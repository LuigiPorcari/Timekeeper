<?php

namespace App\Http\Controllers;

use App\Models\Race;
use App\Models\User;
use App\Models\Record;
use App\Models\Availability;
use Illuminate\Http\Request;
use App\Services\BrevoMailer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\ReportEntry;
use App\Models\ReportDayDsc;
use App\Models\ReportDayAdmin;
use App\Services\ReportCalculator;
use App\Models\ReportAdminRaceSettings;
use Carbon\Carbon;
use App\Models\ReportRaceDsc;
use App\Services\RaceReportFullBuilder;




class TimekeeperController extends Controller
{
    public function dashboard()
    {
        return view('timekeeper.dashboard');
    }

    /**
     * Mostra la lista disponibilità per l'utente loggato,
     * con mappa delle scelte già effettuate:
     *   $userSelections[availability_id] = [
     *     'morning' => bool,
     *     'afternoon' => bool,
     *     'trasferta' => bool,
     *     'reperibilita' => bool,
     *   ]
     */
    public function showForUser()
    {
        // Disponibilità globali ordinate (contengono anche 'color' deciso dall'admin)
        $availabilities = Availability::orderBy('date_of_availability')->get();

        // Prelievo scelte dell'utente dalla pivot (più robusto che dipendere da withPivot sul Model)
        $rows = DB::table('availability_user')
            ->where('user_id', Auth::id())
            ->get(['availability_id', 'morning', 'afternoon', 'trasferta', 'reperibilita']);

        $userSelections = [];
        foreach ($rows as $r) {
            $userSelections[$r->availability_id] = [
                'morning' => (bool) $r->morning,
                'afternoon' => (bool) $r->afternoon,
                'trasferta' => (bool) $r->trasferta,
                'reperibilita' => (bool) $r->reperibilita,
            ];
        }

        // (Compatibilità col vecchio codice: array di id selezionati "generici", non più usato dalla nuova view)
        $selected = array_keys($userSelections);

        return view('timekeeper.availabilitiesList', compact('availabilities', 'userSelections', 'selected'));
    }

    /**
     * Salva le scelte dell'utente:
     * input atteso:
     *   availability[<availability_id>][morning|afternoon|trasferta|reperibilita] = "1"
     */
    public function storeForUser(Request $request)
    {
        // Validazione di base della struttura
        $validated = $request->validate([
            'availability' => 'nullable|array',
            'availability.*' => 'nullable|array',
            'availability.*.morning' => 'nullable|boolean',
            'availability.*.afternoon' => 'nullable|boolean',
            'availability.*.trasferta' => 'nullable|boolean',
            'availability.*.reperibilita' => 'nullable|boolean',
        ]);

        $payload = $validated['availability'] ?? []; // es: [ 5 => ['morning'=>1, 'trasferta'=>1], 7 => [...] ]

        // Controllo ids esistenti per sicurezza
        $ids = array_map('intval', array_keys($payload));
        if (!empty($ids)) {
            $count = Availability::whereIn('id', $ids)->count();
            if ($count !== count($ids)) {
                return back()->with('error', 'Alcune date non sono valide.')->withInput();
            }
        }

        // Costruisci i dati per sync: [availability_id => ['morning'=>bool, ...]]
        $syncData = [];
        foreach ($payload as $availabilityId => $choices) {
            // Se tutte le opzioni sono vuote/non spuntate, NON sincronizziamo quel giorno (come "nessuna scelta")
            $m = !empty($choices['morning']);
            $p = !empty($choices['afternoon']);
            $t = !empty($choices['trasferta']);
            $r = !empty($choices['reperibilita']);

            if ($m || $p || $t || $r) {
                $syncData[$availabilityId] = [
                    'morning' => $m,
                    'afternoon' => $p,
                    'trasferta' => $t,
                    'reperibilita' => $r,
                ];
            }
        }

        // sync: attacca/aggiorna solo i giorni presenti in $syncData, gli altri vengono sganciati
        Auth::user()->availabilities()->sync($syncData);

        // Notifica agli admin (come in precedenza)
        $adminEmails = User::where('is_admin', 1)
            ->whereNotNull('email')
            ->select('email')
            ->distinct()
            ->pluck('email')
            ->all();

        foreach ($adminEmails as $email) {
            try {
                $brevo = new BrevoMailer();
                $brevo->sendEmail(
                    $email,
                    'Inserimento nuova disponibilità',
                    'emails.admin.newAvailabilities',
                    ['timekeeperName' => Auth::user()->name]
                );
            } catch (\Throwable $e) {
                report($e); // logga, ma non interrompere il flusso
            }
        }

        return redirect()->back()->with('success', 'Disponibilità aggiornata!');
    }

    public function racesListShow()
    {
        $user = auth()->user();
        $timekeeperRaces = $user->races()->orderBy('date_of_race', 'asc')->get();
        return view('timekeeper.raceList', compact('timekeeperRaces'));
    }


    public function manage(Race $race, ReportCalculator $calc, RaceReportFullBuilder $builder)
    {
        $user = auth()->user();
        $isLeader = $user->isLeaderOf($race);

        // Cronometristi assegnati alla gara
        $timekeepers = $race->users()
            ->select('users.id', 'users.name', 'users.surname', 'users.domicile')
            ->orderBy('surname')
            ->get();

        // Entry CRONO: 1 per gara per crono
        $entriesQuery = ReportEntry::query()
            ->where('race_id', $race->id)
            ->with(['user', 'attachments']);

        // Se NON è DSC, mi serve solo la sua entry per il form
        if (!$isLeader) {
            $entriesQuery->where('user_id', $user->id);
        }

        $entries = $entriesQuery->get()->keyBy('user_id');

        // DSC gara (una volta per gara)
        $dscRace = ReportRaceDsc::where('race_id', $race->id)->first();

        // Giorni gara
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

        // ==========================================================
        // DSC ORARI GIORNALIERI (NUOVO): righe per race+day+user_id
        // ==========================================================
        $dscDayHours = null;               // riga campione per precompilare gli orari
        $selectedDayTimekeepers = [];      // lista user_id selezionati per quel giorno
        $lockedHours = false;              // true se anche solo una riga del giorno è confermata
        $hasAnyDayRows = false;            // true se esistono righe per quel giorno

        if ($selectedDay) {
            $dayRows = ReportDayDsc::where('race_id', $race->id)
                ->where('work_date', $selectedDay)
                ->get();

            $hasAnyDayRows = $dayRows->isNotEmpty();

            // crono selezionati per la giornata
            $selectedDayTimekeepers = $dayRows->pluck('user_id')
                ->filter()
                ->values()
                ->all();

            // prendo una riga “campione” per precompilare gli orari nella form
            $dscDayHours = $dayRows->first();

            // se una qualsiasi riga è confermata, blocco modifiche (più semplice e coerente)
            $lockedHours = $dayRows->contains(function ($r) {
                return (bool) ($r->confirmed ?? false);
            });
        }

        // Settings gara
        $settings = ReportAdminRaceSettings::where('race_id', $race->id)->first();

        // Admin day (se serve van_cost): prendo la prima disponibile
        $adminAny = ReportDayAdmin::where('race_id', $race->id)
            ->orderBy('work_date')
            ->first();

        // Righe tabella "snella" (quella che usi nel riepilogo DSC)
        $targetUsers = $isLeader ? $timekeepers : $timekeepers->where('id', $user->id);

        $rows = $targetUsers->map(function ($tk) use ($race, $entries, $dscRace, $adminAny, $settings, $calc) {
            $entry = $entries->get($tk->id);

            // Se non esiste, creo un oggetto "vuoto" (non salvato) per evitare errori in view
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

            // Calcolo sistema: usa DSC gara (per tutti)
            $sys = $calc->computeRowForDay($race, $entry, $dscRace, $adminAny, $settings);

            return [
                'user' => $tk,
                'entry' => $entry,
                'sys' => $sys,
            ];
        });

        // ==========================================================
        // DATI FULL: SOLO per crono NON DSC
        // ==========================================================
        $fullRows = null;
        $fullDays = null;
        $raceDaysCount = null;

        if (!$isLeader) {
            // buildForTimekeeper produce la struttura "full" (rows, days, ecc.)
            $full = $builder->buildForTimekeeper($race, $user->id, $calc);

            $fullRows = $full['rows'] ?? [];
            $fullDays = $full['days'] ?? [];
            $raceDaysCount = $full['raceDaysCount'] ?? (is_array($fullDays) ? count($fullDays) : 1);

            // se il builder produce versioni più "complete" di dsc/settings, le uso
            if (array_key_exists('dscRace', $full) && $full['dscRace']) {
                $dscRace = $full['dscRace'];
            }
            if (array_key_exists('settings', $full) && $full['settings']) {
                $settings = $full['settings'];
            }
        }

        return view('timekeeper.records.manage', [
            'race' => $race,
            'isLeader' => $isLeader,

            // crono
            'timekeepers' => $timekeepers,
            'entries' => $entries,
            'rows' => $rows,

            // DSC
            'dscRace' => $dscRace,

            // DSC ore giornaliere + assegnazioni giornata
            'days' => $days,
            'selectedDay' => $selectedDay,
            'dscDayHours' => $dscDayHours,
            'selectedDayTimekeepers' => $selectedDayTimekeepers,
            'lockedHours' => $lockedHours,
            'hasAnyDayRows' => $hasAnyDayRows,

            // settings
            'settings' => $settings,

            // FULL (solo se NON leader; la view gestisce anche null)
            'fullRows' => $fullRows,
            'fullDays' => $fullDays,
            'raceDaysCount' => $raceDaysCount,
        ]);
    }






    public function saveDscDay(Request $request, Race $race)
    {
        // dd('saveDscDay HIT', $request->all());
        $user = auth()->user();
        if (!$user->isLeaderOf($race)) {
            return back()->with('error', 'Non hai i permessi per modificare i dati DSC.');
        }

        $selectedDay = $request->input('selected_day') ?? $request->input('day');
        if (!$selectedDay) {
            return back()->with('error', 'Seleziona prima una giornata.');
        }

        $start = Carbon::parse($race->date_of_race)->toDateString();
        $end = $race->date_end ? Carbon::parse($race->date_end)->toDateString() : $start;
        if ($end < $start) {
            [$start, $end] = [$end, $start];
        }

        $validated = $request->validate([
            'target_user_id' => 'required|exists:users,id',

            'morning_start' => 'nullable|date_format:H:i',
            'morning_end' => 'nullable|date_format:H:i',
            'afternoon_start' => 'nullable|date_format:H:i',
            'afternoon_end' => 'nullable|date_format:H:i',

            'van_needed' => 'nullable|boolean',
            'missed_meals' => 'nullable|integer|min:0|max:50',
            'apparecchiature' => 'nullable|array',
            'apparecchiature.*' => 'string',
        ]);

        $workDate = Carbon::parse($selectedDay)->toDateString();
        if ($workDate < $start || $workDate > $end) {
            return back()->with('error', 'La giornata selezionata non rientra nel periodo della gara.');
        }

        $targetId = (int) $validated['target_user_id'];

        $isAssigned = $race->users()->where('users.id', $targetId)->exists();
        if (!$isAssigned) {
            return back()->with('error', 'Il cronometrista selezionato non è assegnato a questa gara.');
        }

        $existing = ReportDayDsc::where('race_id', $race->id)
            ->where('user_id', $targetId)
            ->where('work_date', $workDate)
            ->first();

        if ($existing && $existing->confirmed) {
            return back()->with('error', 'I dati DSC di questo giorno sono confermati e non possono essere modificati.');
        }

        ReportDayDsc::updateOrCreate(
            [
                'race_id' => $race->id,
                'user_id' => $targetId,
                'work_date' => $workDate,
            ],
            [
                'morning_start' => $validated['morning_start'] ?? null,
                'morning_end' => $validated['morning_end'] ?? null,
                'afternoon_start' => $validated['afternoon_start'] ?? null,
                'afternoon_end' => $validated['afternoon_end'] ?? null,
                'van_needed' => (bool) ($validated['van_needed'] ?? false),
                'missed_meals' => (int) ($validated['missed_meals'] ?? 0),
                'apparecchiature' => $validated['apparecchiature'] ?? [],
            ]
        );

        // ✅ DEBUG: verifica cosa c’è davvero in DB dopo il salvataggio
        $check = ReportDayDsc::where('race_id', $race->id)
            ->where('user_id', $targetId)
            ->where('work_date', $workDate)
            ->first();

        logger()->info('DSC SAVED', [
            'race_id' => $race->id,
            'user_id' => $targetId,
            'work_date' => $workDate,
            'row' => $check?->toArray(),
        ]);

        return redirect()
            ->route('records.manage', [
                'race' => $race->id,
                'day' => $workDate,
                'dsc_user' => $targetId,
            ])
            ->with('success', 'Dati DSC salvati/modificati per la giornata selezionata.');
    }





    public function store(Request $request, Race $race)
    {
        $user = auth()->user();

        // blocco inserimenti se confermato
        $existing = ReportEntry::where('race_id', $race->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existing && $existing->confirmed) {
            return back()->with('error', 'Il tuo report per questa gara è già confermato e non può essere modificato.');
        }

        $validated = $request->validate([
            'km' => 'nullable|numeric|min:0',
            'pedaggi' => 'nullable|numeric|min:0',
            'vitto' => 'nullable|numeric|min:0',
            'alloggio' => 'nullable|numeric|min:0',
            'spese_varie' => 'nullable|numeric|min:0',
            'note' => 'nullable|string',
            'attachments.*' => 'nullable|file|max:10240',
        ]);

        $entry = ReportEntry::updateOrCreate(
            ['race_id' => $race->id, 'user_id' => $user->id],
            [
                'km' => $validated['km'] ?? null,
                'pedaggi' => $validated['pedaggi'] ?? null,
                'vitto' => $validated['vitto'] ?? null,
                'alloggio' => $validated['alloggio'] ?? null,
                'spese_varie' => $validated['spese_varie'] ?? null,
                'note' => $validated['note'] ?? null,
            ]
        );

        // Allegati (link diretto a storage)
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('report_attachments', 'public');

                $entry->attachments()->create([
                    'file_path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                ]);
            }
        }

        return redirect()
            ->route('records.manage', ['race' => $race->id, 'day' => $request->input('day') ?? request('day')])
            ->with('success', 'Report crono salvato con successo.');

    }

    public function edit(ReportEntry $entry)
    {
        $user = auth()->user();

        if ($entry->user_id !== $user->id) {
            return redirect()
                ->route('records.manage', ['race' => $entry->race_id, 'day' => request('day')])
                ->with('error', 'Non hai i permessi per modificare questo report.');
        }

        if ($entry->confirmed) {
            return redirect()
                ->route('records.manage', ['race' => $entry->race_id, 'day' => request('day')])
                ->with('error', 'Questo report è stato confermato e non può essere modificato.');
        }

        return view('timekeeper.records.edit', [
            'entry' => $entry->load('race', 'attachments'),
            'race' => $entry->race,
            'day' => request('day'),
        ]);
    }

    public function update(Request $request, ReportEntry $entry)
    {
        $user = auth()->user();

        if ($entry->confirmed) {
            return back()->with('error', 'Questo report è stato confermato e non può essere modificato.');
        }

        if ($entry->user_id !== $user->id) {
            return back()->with('error', 'Non hai i permessi per modificare questo report.');
        }

        $validated = $request->validate([
            'km' => 'nullable|numeric|min:0',
            'pedaggi' => 'nullable|numeric|min:0',
            'vitto' => 'nullable|numeric|min:0',
            'alloggio' => 'nullable|numeric|min:0',
            'spese_varie' => 'nullable|numeric|min:0',
            'note' => 'nullable|string',
            'attachments.*' => 'nullable|file|max:10240',
        ]);

        $entry->update([
            'km' => $validated['km'] ?? null,
            'pedaggi' => $validated['pedaggi'] ?? null,
            'vitto' => $validated['vitto'] ?? null,
            'alloggio' => $validated['alloggio'] ?? null,
            'spese_varie' => $validated['spese_varie'] ?? null,
            'note' => $validated['note'] ?? null,
        ]);

        // Allegati aggiuntivi
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('report_attachments', 'public');

                $entry->attachments()->create([
                    'file_path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                ]);
            }
        }

        return redirect()
            ->route('records.manage', ['race' => $entry->race_id, 'day' => $request->input('day')])
            ->with('success', 'Report aggiornato con successo.');
    }
    public function destroy(ReportEntry $entry)
    {
        $user = auth()->user();

        if ($entry->confirmed) {
            return back()->with('error', 'Questo report è stato confermato e non può essere eliminato.');
        }

        if ($entry->user_id !== $user->id) {
            return back()->with('error', 'Non hai i permessi per eliminare questo report.');
        }

        $raceId = $entry->race_id;
        $day = request('day');

        $entry->delete();

        return redirect()
            ->route('records.manage', ['race' => $raceId, 'day' => $day])
            ->with('success', 'Report eliminato con successo.');
    }
    public function confirm(ReportEntry $entry)
    {
        $user = auth()->user();

        if (!$user->isLeaderOf($entry->race)) {
            return redirect()->route('records.manage', ['race' => $entry->race_id, 'day' => request('day')])
                ->with('error', 'Non hai i permessi per confermare questo report.');
        }

        $entry->update(['confirmed' => true]);

        return redirect()->route('records.manage', [
            'race' => $entry->race_id,
            'day' => request('day'),
            'dsc_user' => request('dsc_user'),
        ])->with('success', 'Report confermato con successo.');
    }


    public function confirmAll(Race $race)
    {
        $user = auth()->user();

        if (!$user->isLeaderOf($race)) {
            return back()->with('error', 'Non hai i permessi per confermare i report di questa gara.');
        }

        ReportEntry::where('race_id', $race->id)
            ->where('confirmed', false)
            ->update(['confirmed' => true]);

        return redirect()->route('records.manage', [
            'race' => $race->id,
            'day' => request('day'),
            'dsc_user' => request('dsc_user'),
        ])->with('success', 'Tutti i report (crono) sono stati confermati con successo.');
    }


    public function saveEntry(Request $request, Race $race)
    {
        $user = auth()->user();

        $entry = ReportEntry::where('race_id', $race->id)
            ->where('user_id', $user->id)
            ->first();

        if ($entry && $entry->confirmed) {
            return back()->with('error', 'Il tuo report per questa gara è confermato e non può essere modificato.');
        }

        $validated = $request->validate([
            'km' => 'nullable|numeric|min:0',
            'pedaggi' => 'nullable|numeric|min:0',
            'vitto' => 'nullable|numeric|min:0',
            'alloggio' => 'nullable|numeric|min:0',
            'spese_varie' => 'nullable|numeric|min:0',
            'note' => 'nullable|string',
            'attachments.*' => 'nullable|file|max:10240',
            'day' => 'nullable|date',
        ]);

        $entry = ReportEntry::updateOrCreate(
            ['race_id' => $race->id, 'user_id' => $user->id],
            [
                'km' => $validated['km'] ?? null,
                'pedaggi' => $validated['pedaggi'] ?? null,
                'vitto' => $validated['vitto'] ?? null,
                'alloggio' => $validated['alloggio'] ?? null,
                'spese_varie' => $validated['spese_varie'] ?? null,
                'note' => $validated['note'] ?? null,
            ]
        );

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('report_attachments', 'public');

                $entry->attachments()->create([
                    'file_path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                ]);
            }
        }

        $day = $validated['day'] ?? request('day');

        return redirect()
            ->route('records.manage', ['race' => $race->id, 'day' => $day])
            ->with('success', 'Report crono salvato/modificato con successo.');
    }

    public function confirmDscDay(Request $request, Race $race)
    {
        $user = auth()->user();
        if (!$user->isLeaderOf($race)) {
            return back()->with('error', 'Non hai i permessi per confermare i dati DSC.');
        }

        $validated = $request->validate([
            'selected_day' => 'required|date',
            'target_user_id' => 'required|exists:users,id',
        ]);

        $workDate = Carbon::parse($validated['selected_day'])->toDateString();
        $targetId = (int) $validated['target_user_id'];

        $row = ReportDayDsc::where('race_id', $race->id)
            ->where('user_id', $targetId)
            ->where('work_date', $workDate)
            ->first();

        if (!$row) {
            return back()->with('error', 'Non ci sono dati DSC da confermare per quel crono in quella giornata.');
        }

        $row->update(['confirmed' => true]);

        return redirect()->route('records.manage', [
            'race' => $race->id,
            'day' => $workDate,
            'dsc_user' => $targetId,
        ])->with('success', 'Dati DSC confermati per la giornata selezionata.');
    }

    public function saveDscRace(Request $request, Race $race)
    {
        $user = auth()->user();
        if (!$user->isLeaderOf($race)) {
            return back()->with('error', 'Non hai i permessi per modificare i dati DSC.');
        }

        $validated = $request->validate([
            'van_needed' => 'nullable|boolean',
            'missed_meals' => 'nullable|integer|min:0|max:200',
            'apparecchiature' => 'nullable|array',
            'apparecchiature.*' => 'string',
        ]);

        // se già confermato, non modificabile
        $existing = ReportRaceDsc::where('race_id', $race->id)->first();
        if ($existing && $existing->confirmed) {
            return back()->with('error', 'I dati DSC della gara sono confermati e non possono essere modificati.');
        }

        ReportRaceDsc::updateOrCreate(
            ['race_id' => $race->id],
            [
                'user_id' => $user->id,
                'van_needed' => (bool) ($validated['van_needed'] ?? false),
                'missed_meals' => (int) ($validated['missed_meals'] ?? 0),
                'apparecchiature' => $validated['apparecchiature'] ?? [],
            ]
        );

        return redirect()
            ->route('records.manage', ['race' => $race->id])
            ->with('success', 'Dati DSC (gara) salvati/modificati con successo.');
    }

    public function confirmDscRace(Request $request, Race $race)
    {
        $user = auth()->user();
        if (!$user->isLeaderOf($race)) {
            return back()->with('error', 'Non hai i permessi per confermare i dati DSC.');
        }

        $row = ReportRaceDsc::where('race_id', $race->id)->first();
        if (!$row) {
            return back()->with('error', 'Non ci sono dati DSC da confermare per questa gara.');
        }

        $row->update(['confirmed' => true]);

        return redirect()
            ->route('records.manage', ['race' => $race->id])
            ->with('success', 'Dati DSC (gara) confermati con successo.');
    }


    public function saveDscDayHours(Request $request, Race $race)
    {
        $user = auth()->user();
        if (!$user->isLeaderOf($race)) {
            return back()->with('error', 'Non hai i permessi per modificare i dati DSC.');
        }

        $validated = $request->validate([
            'day' => 'required|date',
            'timekeepers' => 'nullable|array',
            'timekeepers.*' => 'integer|exists:users,id',

            'morning_start' => 'nullable|date_format:H:i',
            'morning_end' => 'nullable|date_format:H:i',
            'afternoon_start' => 'nullable|date_format:H:i',
            'afternoon_end' => 'nullable|date_format:H:i',
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

        // lista selezionata (può essere vuota)
        $selectedIds = array_values(array_unique(array_map('intval', $validated['timekeepers'] ?? [])));

        // sicurezza: devono essere assegnati alla gara
        if (!empty($selectedIds)) {
            $assignedCount = $race->users()->whereIn('users.id', $selectedIds)->count();
            if ($assignedCount !== count($selectedIds)) {
                return back()->with('error', 'Hai selezionato uno o più crono non assegnati a questa gara.');
            }
        }

        // righe esistenti per quel giorno
        $existingRows = ReportDayDsc::where('race_id', $race->id)
            ->where('work_date', $workDate)
            ->get();

        // se QUALSIASI riga di quel giorno è confermata, non permetto modifiche (scelta semplice e sicura)
        if ($existingRows->contains(fn($r) => (bool) ($r->confirmed ?? false))) {
            return redirect()
                ->route('records.manage', ['race' => $race->id, 'day' => $workDate])
                ->with('error', 'Gli orari/assegnazioni DSC per questa giornata sono confermati e non possono essere modificati.');
        }

        DB::transaction(function () use ($race, $workDate, $selectedIds, $validated, $user) {

            // 1) cancello tutte le righe di quel giorno (visto che non sono confermate)
            ReportDayDsc::where('race_id', $race->id)
                ->where('work_date', $workDate)
                ->delete();

            // 2) ricreo una riga per ogni crono selezionato
            foreach ($selectedIds as $tkId) {
                ReportDayDsc::create([
                    'race_id' => $race->id,
                    'work_date' => $workDate,
                    'user_id' => $tkId, // IMPORTANTISSIMO: assegnazione giornata

                    // opzionale: memorizzo chi ha inserito (leader), se ti serve tienilo, altrimenti toglilo
                    // 'created_by' => $user->id,  // solo se hai la colonna

                    'morning_start' => $validated['morning_start'] ?? null,
                    'morning_end' => $validated['morning_end'] ?? null,
                    'afternoon_start' => $validated['afternoon_start'] ?? null,
                    'afternoon_end' => $validated['afternoon_end'] ?? null,

                    'confirmed' => false,
                ]);
            }
        });

        return redirect()
            ->route('records.manage', ['race' => $race->id, 'day' => $workDate])
            ->with('success', 'Orari/assegnazioni DSC salvati per la giornata selezionata.');
    }

    public function confirmDscDayHours(Request $request, Race $race)
    {
        $user = auth()->user();
        if (!$user->isLeaderOf($race)) {
            return back()->with('error', 'Non hai i permessi per confermare i dati DSC.');
        }

        $validated = $request->validate([
            'day' => 'required|date',
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

        $q = ReportDayDsc::where('race_id', $race->id)
            ->where('work_date', $workDate);

        if (!$q->exists()) {
            return back()->with('error', 'Non ci sono orari/assegnazioni DSC da confermare per questa giornata.');
        }

        // conferma in massa tutte le righe del giorno
        $q->update(['confirmed' => true]);

        return redirect()
            ->route('records.manage', ['race' => $race->id, 'day' => $workDate])
            ->with('success', 'Orari/assegnazioni DSC confermati per la giornata selezionata.');
    }

    public function confirmMyEntry(Request $request, Race $race)
    {
        $user = auth()->user();

        $entry = ReportEntry::where('race_id', $race->id)
            ->where('user_id', $user->id)
            ->first();

        if (!$entry) {
            return back()->with('error', 'Non hai ancora salvato il report crono da confermare.');
        }

        if ($entry->confirmed) {
            return back()->with('success', 'Il report crono è già confermato.');
        }

        $entry->update(['confirmed' => true]);

        return redirect()
            ->route('records.manage', ['race' => $race->id, 'day' => request('day')])
            ->with('success', 'Report crono confermato. Non potrai più modificarlo.');
    }

    public function deleteMyEntry(Request $request, Race $race)
    {
        $user = auth()->user();

        $entry = ReportEntry::where('race_id', $race->id)
            ->where('user_id', $user->id)
            ->with('attachments')
            ->first();

        if (!$entry) {
            return back()->with('error', 'Non c’è nessun report da eliminare.');
        }

        if ($entry->confirmed) {
            return back()->with('error', 'Questo report è confermato e non può essere eliminato.');
        }

        // Se vuoi eliminare anche i file fisici dallo storage, dimmelo e te lo aggiungo.
        // Qui elimino solo i record allegati e il report.
        $entry->attachments()->delete();
        $entry->delete();

        $day = $request->input('day') ?? request('day');

        return redirect()
            ->route('records.manage', ['race' => $race->id, 'day' => $day])
            ->with('success', 'Report eliminato con successo.');
    }

}
