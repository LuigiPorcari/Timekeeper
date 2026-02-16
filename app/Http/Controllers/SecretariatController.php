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
use App\Services\RaceReportFullBuilder;
use App\Services\ReportPrimaNotaExcelExporter;


use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

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

        // Crono assegnati alla gara
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

        // Giorni gara + selezione giorno
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

        // ✅ CRONO COINVOLTI IN QUESTA GIORNATA (scelti dal DSC)
        $involvedUserIds = [];
        if ($selectedDay) {
            $involvedUserIds = ReportDayDsc::where('race_id', $race->id)
                ->where('work_date', $selectedDay)
                ->whereNotNull('user_id')          // importante se in tabella hai anche righe "globali"
                ->pluck('user_id')
                ->map(fn($id) => (int) $id)
                ->unique()
                ->values()
                ->all();
        }

        // ✅ Orari DSC del giorno:
        // nel tuo progetto "dscDayHours" è una riga unica per race+day (quella globale)
        // se ora la tabella è diventata per-user, allora per mostrare gli orari globali devi decidere:
        // - o hai una riga "globale" (user_id null), oppure
        // - prendi la prima riga di quel giorno.
        $dscDayHours = null;
        if ($selectedDay) {
            $dscDayHours = ReportDayDsc::where('race_id', $race->id)
                ->where('work_date', $selectedDay)
                ->whereNull('user_id') // ✅ se hai deciso di salvare la riga globale con user_id NULL
                ->first();

            // fallback: se non esiste globale, prendi la prima riga del giorno
            if (!$dscDayHours) {
                $dscDayHours = ReportDayDsc::where('race_id', $race->id)
                    ->where('work_date', $selectedDay)
                    ->first();
            }
        }

        // ✅ Ore segreteria (solo crono coinvolti)
        $adminDay = collect();
        if ($selectedDay && !empty($involvedUserIds)) {
            $adminDay = ReportDayAdmin::where('race_id', $race->id)
                ->where('work_date', $selectedDay)
                ->whereIn('user_id', $involvedUserIds)
                ->get()
                ->keyBy('user_id');
        }

        // ✅ Righe riepilogo “gara” (questa parte può restare su TUTTI i crono, perché è riepilogo gara)
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

            $sys = $calc->computeRowForDay($race, $entry, $dscRace, null, $settings);

            return [
                'user' => $tk,
                'entry' => $entry,
                'sys' => $sys,
            ];
        });

        // ✅ RIGHE PER TABELLA SEGRETERIA "Ore per giornata":
        // solo crono coinvolti nel giorno
        $rowsForDayAdmin = $rows->filter(function ($r) use ($involvedUserIds) {
            return in_array((int) $r['user']->id, $involvedUserIds, true);
        })->values();

        return view('secretariat.races.report', [
            'race' => $race,

            'days' => $days,
            'selectedDay' => $selectedDay,

            'dscRace' => $dscRace,
            'dscDayHours' => $dscDayHours,

            'settings' => $settings,
            'adminDay' => $adminDay,

            'rows' => $rows,                       // riepilogo gara (tutti)
            'rowsForDayAdmin' => $rowsForDayAdmin, // ✅ segreteria ore (solo coinvolti)
            'involvedUserIds' => $involvedUserIds, // opzionale (debug / UI)
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

        // ✅ userId coinvolti nel giorno (scelti DSC)
        $involvedUserIds = ReportDayDsc::where('race_id', $race->id)
            ->where('work_date', $workDate)
            ->whereNotNull('user_id')
            ->pluck('user_id')
            ->map(fn($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        if (empty($involvedUserIds)) {
            return redirect()
                ->route('secretariat.races.report', ['race' => $race->id, 'day' => $workDate])
                ->with('error', 'Nessun cronometrista risulta assegnato dal DSC per questa giornata.');
        }

        $specArr = $validated['hours_special_service'] ?? [];
        $ordArr = $validated['hours_ordinary_service'] ?? [];

        $userIdsFromForm = array_unique(array_merge(array_keys($specArr), array_keys($ordArr)));
        $userIdsFromForm = array_map('intval', $userIdsFromForm);

        // ✅ filtro: accetto SOLO quelli coinvolti
        $userIds = array_values(array_intersect($userIdsFromForm, $involvedUserIds));

        foreach ($userIds as $uid) {
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

        // ✅ opzionale ma consigliato:
        // se un crono NON è più coinvolto (DSC lo ha tolto), elimino eventuali ore segreteria vecchie
        ReportDayAdmin::where('race_id', $race->id)
            ->where('work_date', $workDate)
            ->whereNotIn('user_id', $involvedUserIds)
            ->delete();

        return redirect()
            ->route('secretariat.races.report', ['race' => $race->id, 'day' => $workDate])
            ->with('success', 'Ore Segreteria salvate/modificate per la giornata selezionata.');
    }


    public function raceReportFull(Race $race, ReportCalculator $calc, RaceReportFullBuilder $builder)
    {
        $data = $builder->build($race, $calc);

        // link di ritorno per segreteria
        $data['backUrl'] = route('secretariat.races.report', ['race' => $race->id]);

        return view('secretariat.races.report_full', $data);
    }

    public function exportReportFullExcel(
        Race $race,
        RaceReportFullBuilder $builder,
        ReportCalculator $calc,
        ReportPrimaNotaExcelExporter $exporter
    ) {
        $data = $builder->build($race, $calc);

        $templatePath = storage_path('app/templates/FOGLIO_PRIMA_NOTA.xlsx');
        if (!file_exists($templatePath)) {
            abort(500, 'Template Excel non trovato: ' . $templatePath);
        }

        $spreadsheet = $exporter->buildFromTemplate($templatePath, $race, $data);

        $filename = 'prima_nota_gara_' . $race->id . '.xlsx';
        $tmpDir = storage_path('app/tmp');
        if (!is_dir($tmpDir)) {
            mkdir($tmpDir, 0775, true);
        }

        $tmpPath = $tmpDir . DIRECTORY_SEPARATOR . $filename;

        $writer = new Xlsx($spreadsheet);
        $writer->save($tmpPath);

        return response()->download($tmpPath, $filename)->deleteFileAfterSend(true);
    }

}
