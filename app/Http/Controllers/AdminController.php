<?php

namespace App\Http\Controllers;

use App\Models\Race;
use App\Models\User;
use App\Models\RaceTemp;
use App\Models\Availability;
use Illuminate\Http\Request;
use App\Services\BrevoMailer;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    public function dashboard()
    {
        return view('admin.dashboard');
    }
    public function timekeeperListShow()
    {
        $timekeepers = User::where('is_timekeeper', 1)->get();
        return view('admin.timekeeperList', compact('timekeepers'));
    }
    public function timekeeperDetailsShow(User $timekeeper)
    {
        return view('admin.timekeeperDetails', compact('timekeeper'));
    }
    public function updateTimekeeper(Request $request, User $timekeeper)
    {
        $allowed = [
            'co',
            'elaborazione_dati',
            'vasca',
            'partenza',
            'arrivo',
            'elaborazione_dati_completa',
            'elaborazione_dati_parziale_live',
            'fotofinish',
            'manuale',
            'centro_classifica',
            'tracking',
            'start_ps',
            'fine_ps',
            'controllo_orari_co',
            'riordini',
            'assistenza_partenza_arrivo',
            'palco_premiazioni',
            'transponder_pc',
            'solo_cronometraggio_start',
            'solo_cronometraggio_fine',
            'co_con_pc',
            'co_solo_tablet',
            'partenza_prova',
            'fine_prova',
            'pressostati',
            'tablet',
            'arrivo_bandelle',
            'partenza_orologio_tablet',
            'prog_spec_concorso_ippico',
            'utilizzo_spec_programma',
        ];

        $data = $request->validate([
            'specialization' => 'nullable|array',
            'specialization.*' => ['string', Rule::in($allowed)],
        ]);

        $timekeeper->specialization = $data['specialization'] ?? [];
        $timekeeper->save();

        return back()->with('success', 'Specializzazioni aggiornate!');
    }
    public function racesListShow()
    {
        $races = Race::all();
        return view('admin.racesList', compact('races'));
    }
    public function racesTempListShow()
    {
        $racesTemp = RaceTemp::all();
        return view('admin.racesTempList', compact('racesTemp'));
    }
    public function createRaceShow()
    {
        return view('admin.createRace');
    }
    public function storeRace(Request $request)
    {
        // elenco dei type ammessi (devono corrispondere alla select della view)
        $allowedTypes = [
            'NUOTO -NUOTO SALVAMENTO',
            'SCI ALPINO – SCI NORDICO',
            'ATLETICA LEGGERA',
            'MOTORALLY',
            'RALLY',
            'ENDURO MOTO',
            'ENDURO MTB',
            'MOTOCROSS',
            'CANOA',
            'CANOTTAGGIO',
            'CICLISMO SU STRADA',
            'CICLISMO PISTA',
            'DOWHINILL',
            'AUTO REGOLARITA’',
            'AUTO STORICHE',
            'AUTOMOBILSMO CIRCUITO',
            'CONCORSO IPPICO',
            'TROTTO',
        ];

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'date_of_race' => 'required|date',
            'date_end' => 'nullable|date|after_or_equal:date_of_race',
            'place' => 'required|string|max:255',

            'type' => ['required', 'string', Rule::in($allowedTypes)],

            'ente_fatturazione' => 'nullable|string|max:255',
            'programma_allegato' => 'nullable|file|mimes:pdf,doc,docx,odt,zip|max:10240',
            'note' => 'nullable|string|max:5000',
        ]);

        // Mappatura TYPE -> specializzazioni normalizzate (slug coerenti riusati ovunque)
        $typeToSpecs = [
            'NUOTO -NUOTO SALVAMENTO' => ['elaborazione_dati', 'vasca'],
            'SCI ALPINO – SCI NORDICO' => ['partenza', 'arrivo', 'elaborazione_dati_completa', 'elaborazione_dati_parziale_live'],
            'ATLETICA LEGGERA' => ['fotofinish', 'manuale'],
            'MOTORALLY' => ['centro_classifica', 'tracking'],
            'RALLY' => ['centro_classifica', 'start_ps', 'fine_ps', 'controllo_orari_co', 'riordini', 'assistenza_partenza_arrivo', 'palco_premiazioni'],
            'ENDURO MOTO' => ['transponder_pc', 'solo_cronometraggio_start', 'solo_cronometraggio_fine', 'co_con_pc', 'co_solo_tablet'],
            'ENDURO MTB' => ['elaborazione_dati', 'partenza_prova', 'fine_prova'],
            'MOTOCROSS' => ['elaborazione_dati', 'arrivo'],
            'CANOA' => ['elaborazione_dati', 'arrivo', 'partenza_orologio_tablet', 'fotofinish'],
            'CANOTTAGGIO' => ['arrivo', 'partenza_orologio_tablet'],
            'CICLISMO SU STRADA' => ['arrivo', 'fotofinish'],
            'CICLISMO PISTA' => ['arrivo_bandelle', 'fotofinish'],
            'DOWHINILL' => ['partenza', 'arrivo', 'elaborazione_dati'], // "come sci": riuso partenza/arrivo
            'AUTO REGOLARITA’' => ['pressostati', 'tablet'],
            'AUTO STORICHE' => ['arrivo', 'start'],
            'AUTOMOBILSMO CIRCUITO' => ['elaborazione_dati', 'contagiri', 'transponder'],
            'CONCORSO IPPICO' => ['prog_spec_concorso_ippico'],
            'TROTTO' => ['utilizzo_spec_programma'],
        ];

        // Upload allegato (se presente)
        $allegatoPath = null;
        if ($request->hasFile('programma_allegato')) {
            $allegatoPath = $request->file('programma_allegato')->store('programmi_gara', 'public');
        }

        // Calcola le specializzazioni dalla mappa
        $computedSpecs = $typeToSpecs[$validated['type']] ?? [];

        // Creazione della nuova gara
        $race = new Race();
        $race->name = $validated['name'];
        $race->date_of_race = $validated['date_of_race'];
        $race->date_end = $validated['date_end'] ?? null;
        $race->place = $validated['place'];
        $race->type = $validated['type'];                 // << salva il type
        $race->ente_fatturazione = $validated['ente_fatturazione'] ?? null;
        $race->programma_allegato = $allegatoPath;
        $race->note = $validated['note'] ?? null;
        $race->specialization_of_race = $computedSpecs;                     // << set automatico
        $race->save();

        $emailsTimekeepers = User::where('is_timekeeper', 1)
            ->whereNotNull('email')
            ->select('email')
            ->distinct()
            ->pluck('email')
            ->all();

        foreach ($emailsTimekeepers as $email) {
            $brevo = new BrevoMailer();
            $brevo->sendEmail(
                $email,
                'Inserimento nuova gara',
                'emails.timekeeper.newRace',
                ['raceName' => $race->name, 'raceStart' => $race->date_of_race, 'raceEnd' => $race->date_end ?? $race->date_of_race]
            );
        }

        return redirect()->route('admin.racesList')->with('success', 'Gara creata con successo!');
    }


    public function storeAvailabilityForm()
    {
        $selectedDates = Availability::pluck('date_of_availability')->toArray();
        return view('admin.availability', compact('selectedDates'));
    }
    public function storeAvailability(Request $request)
    {
        // Recupera le date selezionate dal form
        $selectedDates = $request->input('dates', []);
        // Recupera tutte le date già salvate nel database
        $existingDates = Availability::pluck('date_of_availability')->toArray();
        // Calcola le date da aggiungere
        $datesToAdd = array_diff($selectedDates, $existingDates);
        // Calcola le date da rimuovere
        $datesToRemove = array_diff($existingDates, $selectedDates);
        // Aggiunge le nuove date
        foreach ($datesToAdd as $date) {
            Availability::create([
                'date_of_availability' => $date,
            ]);
        }
        // Rimuove le date deselezionate
        Availability::whereIn('date_of_availability', $datesToRemove)->delete();
        return redirect()->back()->with('success', 'Disponibilità aggiornata con successo!');
    }

    public function selectTimekeepers(Race $race)
    {
        $raceDate = $race->date_of_race;
        $raceSpecializations = $race->specialization_of_race ?? [];

        if ($race->date_end == null) {
            $timekeepers = User::where('is_timekeeper', 1)
                ->whereHas('availabilities', function ($query) use ($raceDate) {
                    $query->where('date_of_availability', \Illuminate\Support\Carbon::parse($raceDate)->toDateString());
                })
                ->get()
                ->filter(function ($user) use ($raceSpecializations) {
                    $userSpecs = $user->specialization ?? [];
                    // ✅ jolly: se l'utente ha "co", passa sempre
                    if (in_array('co', $userSpecs, true))
                        return true;
                    // se la gara non richiede specializzazioni, non filtrare
                    if (empty($raceSpecializations))
                        return true;
                    return count(array_intersect($userSpecs, $raceSpecializations)) > 0;
                });
        } else {
            $period = [
                \Illuminate\Support\Carbon::parse($race->date_of_race)->toDateString(),
                \Illuminate\Support\Carbon::parse($race->date_end)->toDateString(),
            ];
            if ($period[1] < $period[0])
                $period = [$period[1], $period[0]];

            $timekeepers = User::where('is_timekeeper', 1)
                ->whereHas('availabilities', function ($query) use ($period) {
                    $query->whereBetween('date_of_availability', $period);
                })
                ->get()
                ->filter(function ($user) use ($raceSpecializations) {
                    $userSpecs = $user->specialization ?? [];
                    if (in_array('co', $userSpecs, true))
                        return true;      // ✅ jolly
                    if (empty($raceSpecializations))
                        return true;           // no richieste → tutti ok
                    return count(array_intersect($userSpecs, $raceSpecializations)) > 0;
                });
        }

        return view('admin.selectTimekeepers', compact('race', 'timekeepers'));
    }

    public function assignTimekeepers(Request $request, Race $race)
    {
        $request->validate([
            'timekeepers' => 'nullable|array',
            'timekeepers.*' => 'exists:users,id',
            'leader' => 'nullable|exists:users,id',
        ]);

        $assigned = collect($request->input('timekeepers', []));
        $leaderId = $request->input('leader');

        // Mappa cronometristi assegnati con flag is_leader (true solo se corrisponde)
        $syncData = $assigned->mapWithKeys(function ($id) use ($leaderId) {
            return [$id => ['is_leader' => ($id == $leaderId)]];
        })->toArray();

        // Sincronizza cronometristi per la gara
        $race->users()->sync($syncData);

        $race->load(['users' => fn($q) => $q->select('users.id', 'users.email')]);

        $emailsSelected = $race->users->pluck('email')->filter()->values()->all();

        $leaderEmail = optional($race->users->firstWhere('pivot.is_leader', true))->email;

        foreach ($emailsSelected as $email) {
            if ($email != $leaderEmail) {
                $brevo = new BrevoMailer();
                $brevo->sendEmail(
                    $email,
                    'Convocazione gara',
                    'emails.timekeeper.raceConvocation',
                    ['raceName' => $race->name, 'raceStart' => $race->date_of_race, 'raceEnd' => $race->date_end ?? $race->date_of_race]
                );
            } else {
                $brevo = new BrevoMailer();
                $brevo->sendEmail(
                    $email,
                    'Convocazione gara DSC',
                    'emails.timekeeper.raceConvocationDsc',
                    ['raceName' => $race->name, 'raceStart' => $race->date_of_race, 'raceEnd' => $race->date_end ?? $race->date_of_race]
                );
            }
        }
        return redirect()->route('admin.racesList')->with('success', 'Cronometristi aggiornati con successo.');
    }


    public function timekeeperReport(User $user)
    {
        $races = $user->races()->with([
            'records' => function ($query) use ($user) {
                $query->where('user_id', $user->id);
            }
        ])->get();

        return view('admin.timekeeperReports', compact('user', 'races'));
    }
    public function raceReport(Race $race)
    {
        $records = $race->records()->with('user')->get();
        $cms = $race->users()->wherePivot('is_leader', true)->first();
        $cmsRecord = $cms ? $race->records()->where('user_id', $cms->id)->first() : null;

        $totalSum = 0;

        foreach ($records as $record) {
            $useCmsValues = $cmsRecord && $record->user_id !== $cmsRecord->user_id;
            $amount = $useCmsValues ? $cmsRecord->amount_documented : $record->amount_documented;

            $total = $amount
                + ($record->travel_ticket_documented ?? 0)
                + ($record->food_documented ?? 0)
                + ($record->accommodation_documented ?? 0)
                + ($record->various_documented ?? 0)
                + ($record->food_not_documented ?? 0)
                + ($record->daily_allowances_not_documented ?? 0)
                + ($record->special_daily_allowances_not_documented ?? 0);

            $totalSum += $total;
        }

        return view('admin.racesReports', compact('race', 'records', 'cmsRecord', 'totalSum'));
    }
    public function editRace(Race $race)
    {
        return view('admin.racesEdit', compact('race'));
    }
    public function updateRace(Request $request, Race $race)
    {
        $allowedTypes = [
            'NUOTO -NUOTO SALVAMENTO',
            'SCI ALPINO – SCI NORDICO',
            'ATLETICA LEGGERA',
            'MOTORALLY',
            'RALLY',
            'ENDURO MOTO',
            'ENDURO MTB',
            'MOTOCROSS',
            'CANOA',
            'CANOTTAGGIO',
            'CICLISMO SU STRADA',
            'CICLISMO PISTA',
            'DOWHINILL',
            'AUTO REGOLARITA’',
            'AUTO STORICHE',
            'AUTOMOBILSMO CIRCUITO',
            'CONCORSO IPPICO',
            'TROTTO',
        ];

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'date_of_race' => 'required|date',
            'date_end' => 'nullable|date|after_or_equal:date_of_race',
            'place' => 'required|string|max:255',
            'type' => ['required', 'string', Rule::in($allowedTypes)],

            'ente_fatturazione' => 'nullable|string|max:255',
            'programma_allegato' => 'nullable|file|mimes:pdf,doc,docx,odt,zip|max:10240',
            'note' => 'nullable|string|max:5000',
        ]);

        // Mappa Type → specializzazioni (stessi slug usati nello store)
        $typeToSpecs = [
            'NUOTO -NUOTO SALVAMENTO' => ['elaborazione_dati', 'vasca'],
            'SCI ALPINO – SCI NORDICO' => ['partenza', 'arrivo', 'elaborazione_dati_completa', 'elaborazione_dati_parziale_live'],
            'ATLETICA LEGGERA' => ['fotofinish', 'manuale'],
            'MOTORALLY' => ['centro_classifica', 'tracking'],
            'RALLY' => ['centro_classifica', 'start_ps', 'fine_ps', 'controllo_orari_co', 'riordini', 'assistenza_partenza_arrivo', 'palco_premiazioni'],
            'ENDURO MOTO' => ['transponder_pc', 'solo_cronometraggio_start', 'solo_cronometraggio_fine', 'co_con_pc', 'co_solo_tablet'],
            'ENDURO MTB' => ['elaborazione_dati', 'partenza_prova', 'fine_prova'],
            'MOTOCROSS' => ['elaborazione_dati', 'arrivo'],
            'CANOA' => ['elaborazione_dati', 'arrivo', 'partenza_orologio_tablet', 'fotofinish'],
            'CANOTTAGGIO' => ['arrivo', 'partenza_orologio_tablet'],
            'CICLISMO SU STRADA' => ['arrivo', 'fotofinish'],
            'CICLISMO PISTA' => ['arrivo_bandelle', 'fotofinish'],
            'DOWHINILL' => ['partenza', 'arrivo', 'elaborazione_dati'],
            'AUTO REGOLARITA’' => ['pressostati', 'tablet'],
            'AUTO STORICHE' => ['arrivo', 'start'],
            'AUTOMOBILSMO CIRCUITO' => ['elaborazione_dati', 'contagiri', 'transponder'],
            'CONCORSO IPPICO' => ['prog_spec_concorso_ippico'],
            'TROTTO' => ['utilizzo_spec_programma'],
        ];

        // Upload allegato (sostituzione)
        if ($request->hasFile('programma_allegato')) {
            $newPath = $request->file('programma_allegato')->store('programmi_gara', 'public');
            if (!empty($race->programma_allegato) && Storage::disk('public')->exists($race->programma_allegato)) {
                Storage::disk('public')->delete($race->programma_allegato);
            }
            $validated['programma_allegato'] = $newPath;
        } else {
            unset($validated['programma_allegato']);
        }

        // Calcola specializzazioni dal type scelto
        $computedSpecs = $typeToSpecs[$validated['type']] ?? [];

        // Aggiorna i campi
        $race->update([
            'name' => $validated['name'],
            'date_of_race' => $validated['date_of_race'],
            'date_end' => $validated['date_end'] ?? null,
            'place' => $validated['place'],
            'type' => $validated['type'],
            'ente_fatturazione' => $validated['ente_fatturazione'] ?? null,
            'programma_allegato' => $validated['programma_allegato'] ?? $race->programma_allegato,
            'note' => $validated['note'] ?? null,
            'specialization_of_race' => $computedSpecs, // ricalcolato
        ]);

        return redirect()->route('admin.racesList')->with('success', 'Gara aggiornata con successo.');
    }

    public function destroyRace(Race $race)
    {
        // Elimina i record associati
        $race->records()->delete();
        // Elimina i collegamenti con i cronometristi
        $race->users()->detach();
        // Ora puoi eliminare la gara
        $race->delete();
        return redirect()->route('admin.racesList')->with('success', 'Gara eliminata con successo.');
    }
}
