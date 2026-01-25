<?php

namespace App\Http\Controllers;


use App\Models\Race;
use App\Models\User;


use App\Models\ReportEntry;
use App\Models\ReportDayDsc;
use Illuminate\Http\Request;
use App\Models\ReportRaceDsc;
use App\Models\ReportDayAdmin;

use Illuminate\Support\Carbon;
use App\Services\ReportCalculator;
use App\Models\ReportAdminRaceSettings;

class SecretariatController extends Controller
{
    /**
     * DASHBOARD (NUOVO SISTEMA)
     */
    public function dashboard()
    {
        $racesCount = Race::count();

        // Adatta se usi un campo diverso per i cronometristi
        $timekeepersCount = User::where('is_timekeeper', true)->count();

        // I “record” ora sono i ReportEntry (una riga per gara+crono)
        $recordsCount = ReportEntry::count();

        return view('secretariat.dashboard', compact('racesCount', 'timekeepersCount', 'recordsCount'));
    }

    /**
     * ELENCO GARE (NUOVO SISTEMA)
     */
    public function racesIndex(Request $request)
    {
        $from = $request->input('from') ? Carbon::parse($request->input('from'))->startOfDay() : null;
        $to = $request->input('to') ? Carbon::parse($request->input('to'))->endOfDay() : null;
        $q = trim((string) $request->input('q'));
        $status = $request->input('status'); // all | open | closed

        $races = Race::query()
            ->when($from, fn($qr) => $qr->whereDate('date_of_race', '>=', $from))
            ->when($to, fn($qr) => $qr->whereDate('date_of_race', '<=', $to))
            ->when($q, fn($qr) => $qr->where(function ($w) use ($q) {
                $w->where('name', 'like', "%$q%")
                    ->orWhere('place', 'like', "%$q%");
            }))
            ->withCount([
                'reportEntries as records_total',
                'reportEntries as records_unconfirmed' => fn($qq) => $qq->where('confirmed', false),
            ])
            ->orderByDesc('date_of_race')
            ->paginate(20)
            ->withQueryString();

        if ($status === 'open' || $status === 'closed') {
            $filtered = $races->getCollection()->filter(function ($race) use ($status) {
                $open = $race->records_unconfirmed > 0;
                $closed = $race->records_total > 0 && $race->records_unconfirmed == 0;
                return $status === 'open' ? $open : $closed;
            });
            $races->setCollection($filtered->values());
        }

        return view('secretariat.races.index', compact('races', 'from', 'to', 'q', 'status'));
    }

    /**
     * REPORT GARA (NUOVO SISTEMA)
     * - include form segreteria (gara)
     * - include form segreteria (giorno per crono)
     */
    public function raceReport(Race $race, ReportCalculator $calc)
    {
        // Settings segreteria (una volta per gara)
        $settings = ReportAdminRaceSettings::where('race_id', $race->id)->first();

        // Crono assegnati
        $timekeepers = $race->users()
            ->select('users.id', 'users.name', 'users.surname', 'users.domicile')
            ->orderBy('surname')
            ->get();

        // ReportEntry con allegati
        $entries = ReportEntry::where('race_id', $race->id)
            ->with(['user', 'attachments'])
            ->get()
            ->keyBy('user_id');

        // DSC gara
        $dscRace = ReportRaceDsc::where('race_id', $race->id)->first();

        // Giorni gara + selezione giorno (serve per: orari DSC + ore segreteria)
        $start = Carbon::parse($race->date_of_race)->startOfDay();
        $end = $race->date_end ? Carbon::parse($race->date_end)->startOfDay() : $start->copy();
        if ($end->lt($start)) {
            [$start, $end] = [$end, $start];
        }

        $days = [];
        for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
            $days[] = $d->format('Y-m-d');
        }
        $selectedDay = request('day') ?? ($days[0] ?? null);

        // Orari DSC per quel giorno (una riga per race + day)
        $dscDayHours = null;
        if ($selectedDay) {
            $dscDayHours = ReportDayDsc::where('race_id', $race->id)
                ->where('work_date', $selectedDay)
                ->first();
        }

        // Ore segreteria per quel giorno (una riga per race+day+user)
        $adminDay = collect();
        if ($selectedDay) {
            $adminDay = ReportDayAdmin::where('race_id', $race->id)
                ->where('work_date', $selectedDay)
                ->get()
                ->keyBy('user_id');
        }

        // Righe riepilogo “gara” (calcolo sistema)
        $rows = $timekeepers->map(function ($tk) use ($race, $entries, $dscRace, $settings, $calc) {
            $entry = $entries->get($tk->id);

            if (!$entry) {
                $entry = new ReportEntry([
                    'race_id' => $race->id,
                    'user_id' => $tk->id,
                    'km' => null,
                    'pedaggi' => null,
                    'vitto' => null,
                    'alloggio' => null,
                    'spese_varie' => null,
                    'note' => null,
                    'confirmed' => false,
                ]);
                $entry->setRelation('user', $tk);
                $entry->setRelation('attachments', collect());
            }

            // ✅ Passiamo null come $admin (non usiamo più van_cost da dayAdmin “a caso”)
            // e usiamo $settings per coeff_km (+ van_cost lo gestiamo dentro il calculator)
            $sys = $calc->computeRowForDay($race, $entry, $dscRace, null, $settings);

            return [
                'user' => $tk,
                'entry' => $entry,
                'sys' => $sys,
            ];
        });

        return view('secretariat.races.report', [
            'race' => $race,

            'days' => $days,
            'selectedDay' => $selectedDay,

            'dscRace' => $dscRace,
            'dscDayHours' => $dscDayHours,

            'settings' => $settings,
            'adminDay' => $adminDay,

            'rows' => $rows,
        ]);
    }

    /**
     * Salva/modifica impostazioni segreteria (una volta per gara)
     */
    public function saveRaceAdminSettings(Request $request, Race $race)
    {
        $validated = $request->validate([
            'van_cost' => 'nullable|numeric|min:0',
            'coeff_km' => 'nullable|numeric|min:0|max:10',
            'contributo_organizzativo' => 'nullable|numeric|min:0',
            'apparecchiature_note' => 'nullable|string',
            'spese_varie_gara' => 'nullable|numeric|min:0',
        ]);

        ReportAdminRaceSettings::updateOrCreate(
            ['race_id' => $race->id],
            [
                'coeff_km' => $validated['coeff_km'] ?? 0.36,
                'van_cost' => $validated['van_cost'] ?? null,
                'contributo_organizzativo' => $validated['contributo_organizzativo'] ?? null,
                'apparecchiature_note' => $validated['apparecchiature_note'] ?? null,
                'spese_varie_gara' => $validated['spese_varie_gara'] ?? null,
            ]
        );

        return redirect()
            ->route('secretariat.races.report', ['race' => $race->id, 'day' => request('day')])
            ->with('success', 'Impostazioni Segreteria (gara) salvate/modificate.');
    }

    /**
     * Salva/modifica ore segreteria per una giornata (per ogni crono)
     */
    public function saveDayAdmin(Request $request, Race $race)
    {
        $validated = $request->validate([
            'day' => 'required|date',
            'hours_special_service' => 'nullable|array',
            'hours_special_service.*' => 'nullable|numeric|min:0|max:24',
            'hours_ordinary_service' => 'nullable|array',
            'hours_ordinary_service.*' => 'nullable|numeric|min:0|max:24',
        ]);

        $workDate = Carbon::parse($validated['day'])->toDateString();

        // range gara
        $start = Carbon::parse($race->date_of_race)->toDateString();
        $end = $race->date_end ? Carbon::parse($race->date_end)->toDateString() : $start;
        if ($end < $start) {
            [$start, $end] = [$end, $start];
        }
        if ($workDate < $start || $workDate > $end) {
            return back()->with('error', 'La giornata selezionata non rientra nel periodo della gara.');
        }

        $specArr = $validated['hours_special_service'] ?? [];
        $ordArr = $validated['hours_ordinary_service'] ?? [];

        $userIds = array_unique(array_merge(array_keys($specArr), array_keys($ordArr)));

        foreach ($userIds as $uid) {
            $uid = (int) $uid;

            // sicurezza: deve essere assegnato alla gara
            $assigned = $race->users()->where('users.id', $uid)->exists();
            if (!$assigned) {
                continue;
            }

            ReportDayAdmin::updateOrCreate(
                [
                    'race_id' => $race->id,
                    'user_id' => $uid,
                    'work_date' => $workDate,
                ],
                [
                    'hours_special_service' => array_key_exists($uid, $specArr) ? (float) $specArr[$uid] : null,
                    'hours_ordinary_service' => array_key_exists($uid, $ordArr) ? (float) $ordArr[$uid] : null,
                ]
            );
        }

        return redirect()
            ->route('secretariat.races.report', ['race' => $race->id, 'day' => $workDate])
            ->with('success', 'Ore Segreteria salvate/modificate per la giornata selezionata.');
    }

    public function raceReportFull(Race $race, ReportCalculator $calc, \App\Services\RaceReportFullBuilder $builder)
    {
        $data = $builder->build($race, $calc);

        // link di ritorno per segreteria
        $data['backUrl'] = route('secretariat.races.report', ['race' => $race->id]);

        return view('secretariat.races.report_full', $data);
    }



}
