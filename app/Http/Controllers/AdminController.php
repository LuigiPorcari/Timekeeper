<?php

namespace App\Http\Controllers;

use App\Models\Race;
use App\Models\User;
use App\Models\RaceTemp;
use Illuminate\Support\Str;
use App\Models\Availability;
use Illuminate\Http\Request;
use App\Services\BrevoMailer;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    public function timekeeperDetailsShow(User $timekeeper)
    {
        // mappa tipi → label attrezzature dal config (con tuo fallback interno)
        $typesMap = $this->getRaceTypesMap();
        return view('admin.timekeeperDetails', compact('timekeeper', 'typesMap'));
    }
    public function updateTimekeeper(Request $request, User $timekeeper)
    {
        // Costruisco l’elenco "consentito" direttamente dal config
        $typesMap = $this->getRaceTypesMap();
        $slug = fn(string $text) => Str::slug($text, '_');

        // Valori consentiti: "co" + tutte le attrezzature MA namespacizzate col tipo
        $allowed = ['co'];
        foreach ($typesMap as $typeLabel => $equipList) {
            $typeSlug = $slug($typeLabel);
            foreach ($equipList as $lab) {
                if (!filled($lab))
                    continue;
                $equipSlug = $slug($lab);
                $allowed[] = "{$typeSlug}__{$equipSlug}";
            }
        }
        $allowed = array_values(array_unique($allowed));

        $data = $request->validate([
            'specialization' => 'nullable|array',
            'specialization.*' => ['string', Rule::in($allowed)],
        ]);

        // Salvo esattamente i valori namespacizzati (più "co" se selezionato)
        $timekeeper->specialization = $data['specialization'] ?? [];
        $timekeeper->save();

        return back()->with('success', 'Specializzazioni aggiornate!');
    }
    public function dashboard()
    {
        return view('admin.dashboard');
    }
    public function timekeeperListShow()
    {
        $timekeepers = User::where('is_timekeeper', 1)
            ->with([
                'availabilities' => function ($q) {
                    $q->withPivot(['morning', 'afternoon', 'trasferta', 'reperibilita'])
                        ->orderBy('date_of_availability');
                },
                'races',
            ])
            ->get();

        return view('admin.timekeeperList', compact('timekeepers'));
    }
    public function selectTimekeepers(Race $race)
    {
        $raceDate = $race->date_of_race;

        // 1) Recupero specializzazioni gara (array o stringa JSON) e le normalizzo
        $raceSpecs = $race->specialization_of_race ?? [];

        if (is_string($raceSpecs)) {
            $decoded = json_decode($raceSpecs, true);
            $raceSpecs = json_last_error() === JSON_ERROR_NONE
                ? $decoded
                : ($raceSpecs ? [$raceSpecs] : []);
        }

        $raceSpecs = is_array($raceSpecs) ? $raceSpecs : [];

        // Le specializzazioni in gara sono salvate come "tipoSlug__equipSlug".
        // Qui estraggo SOLO la parte "equipSlug".
        $raceSpecBase = array_values(array_filter(array_map(function ($ns) {
            if (!is_string($ns) || $ns === '') {
                return null;
            }

            if (str_contains($ns, '__')) {
                [$type, $equip] = explode('__', $ns, 2);
                return $equip; // uso solo la parte attrezzatura
            }

            // fallback: se non è namespacizzato, lo porto a slug
            return Str::slug($ns, '_');
        }, $raceSpecs)));

        // 2) Funzione di filtro cronometristi
        $filterFn = function ($user) use ($raceSpecBase) {
            $userSpecs = $user->specialization ?? [];
            $userSpecs = is_array($userSpecs) ? $userSpecs : [];

            // Jolly: se ha "co", passa sempre
            if (in_array('co', $userSpecs, true)) {
                return true;
            }

            // Se la gara non richiede specializzazioni, non filtriamo
            if (empty($raceSpecBase)) {
                return true;
            }

            // Le specializzazioni del cronometrista sono salvate come "tipoSlug__equipSlug" (o "co").
            // Anche qui estraggo SOLO la parte "equipSlug".
            $userSpecBase = array_values(array_filter(array_map(function ($ns) {
                if (!is_string($ns) || $ns === '') {
                    return null;
                }

                if (str_contains($ns, '__')) {
                    [$type, $equip] = explode('__', $ns, 2);
                    return $equip;
                }

                // fallback per vecchi valori non namespacizzati
                return Str::slug($ns, '_');
            }, $userSpecs)));

            // Match se c'è almeno una apparecchiatura in comune
            return count(array_intersect($userSpecBase, $raceSpecBase)) > 0;
        };

        // 3) Logica disponibilità (come avevi già)
        if ($race->date_end == null) {
            $timekeepers = User::where('is_timekeeper', 1)
                ->whereHas('availabilities', function ($query) use ($raceDate) {
                    $query->where(
                        'date_of_availability',
                        Carbon::parse($raceDate)->toDateString()
                    );
                })
                ->get()
                ->filter($filterFn);
        } else {
            $period = [
                Carbon::parse($race->date_of_race)->toDateString(),
                Carbon::parse($race->date_end)->toDateString(),
            ];

            if ($period[1] < $period[0]) {
                $period = [$period[1], $period[0]];
            }

            $timekeepers = User::where('is_timekeeper', 1)
                ->whereHas('availabilities', function ($query) use ($period) {
                    $query->whereBetween('date_of_availability', $period);
                })
                ->get()
                ->filter($filterFn);
        }

        return view('admin.selectTimekeepers', compact('race', 'timekeepers'));
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
    private function getRaceTypesMap(): array
    {
        // unica fonte di verità
        return config('races.types', []);
    }
    public function createRaceShow()
    {
        // la view leggerà comunque la config, ma avere qui l’helper può tornare utile in futuro
        return view('admin.createRace');
    }
    public function storeRace(Request $request)
    {
        $typeMap = $this->getRaceTypesMap();
        $allowedTypes = array_keys($typeMap);

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

        // Upload allegato (se presente)
        $allegatoPath = null;
        if ($request->hasFile('programma_allegato')) {
            $allegatoPath = $request->file('programma_allegato')->store('programmi_gara', 'public');
        }

        // Apparecchiature "namespacizzate" col tipo
        $type = $validated['type'];
        $baseEquip = $typeMap[$type] ?? [];
        $typeSlug = Str::slug($type, '_');          // <-- underscore
        $specialization = array_values(array_map(function ($label) use ($typeSlug) {
            $labelSlug = Str::slug($label, '_');    // <-- underscore
            return "{$typeSlug}__{$labelSlug}";
        }, array_filter($baseEquip, fn($v) => filled($v))));

        // Crea gara
        $race = new Race();
        $race->name = $validated['name'];
        $race->date_of_race = $validated['date_of_race'];
        $race->date_end = $validated['date_end'] ?? null;
        $race->place = $validated['place'];
        $race->type = $type; // salva il tipo “umano”
        $race->ente_fatturazione = $validated['ente_fatturazione'] ?? null;
        $race->programma_allegato = $allegatoPath;
        $race->note = $validated['note'] ?? null;
        $race->specialization_of_race = $specialization; // slug namespacizzati
        $race->save();

        return redirect()->route('admin.racesList')->with('success', 'Gara creata con successo!');
    }
    public function updateRace(Request $request, Race $race)
    {
        $typeMap = $this->getRaceTypesMap();
        $allowedTypes = array_keys($typeMap);

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

        // Sostituzione allegato
        if ($request->hasFile('programma_allegato')) {
            $newPath = $request->file('programma_allegato')->store('programmi_gara', 'public');
            if (!empty($race->programma_allegato) && Storage::disk('public')->exists($race->programma_allegato)) {
                Storage::disk('public')->delete($race->programma_allegato);
            }
            $validated['programma_allegato'] = $newPath;
        } else {
            unset($validated['programma_allegato']);
        }

        // Ricalcola apparecchiature namespacizzate
        $type = $validated['type'];
        $baseEquip = $typeMap[$type] ?? [];
        $typeSlug = Str::slug($type, '_');          // <-- underscore
        $specialization = array_values(array_map(function ($label) use ($typeSlug) {
            $labelSlug = Str::slug($label, '_');    // <-- underscore
            return "{$typeSlug}__{$labelSlug}";
        }, array_filter($baseEquip, fn($v) => filled($v))));

        // Aggiorna
        $race->update([
            'name' => $validated['name'],
            'date_of_race' => $validated['date_of_race'],
            'date_end' => $validated['date_end'] ?? null,
            'place' => $validated['place'],
            'type' => $type,
            'ente_fatturazione' => $validated['ente_fatturazione'] ?? null,
            'programma_allegato' => $validated['programma_allegato'] ?? $race->programma_allegato,
            'note' => $validated['note'] ?? null,
            'specialization_of_race' => $specialization,
        ]);

        return redirect()->route('admin.racesList')->with('success', 'Gara aggiornata con successo.');
    }
    public function storeAvailabilityForm()
    {
        // mappa: 'YYYY-MM-DD' => 'verde'|'arancione'|'rosso'
        $selectedMap = Availability::pluck('color', 'date_of_availability')->toArray();

        return view('admin.availability', [
            'selectedMap' => $selectedMap,
        ]);
    }
    public function storeAvailability(Request $request)
    {
        // Mappa per-day: color[YYYY-MM-DD] = 'verde'|'arancione'|'rosso'
        $validated = $request->validate([
            'color' => ['nullable', 'array'],
            'color.*' => [Rule::in(['verde', 'arancione', 'rosso'])],
        ]);

        $colorMap = $validated['color'] ?? [];                // es: ['2025-02-03' => 'verde', ...]
        $datesSelected = array_keys($colorMap);               // giorni con un colore scelto

        $existing = Availability::pluck('date_of_availability', 'id')->toArray(); // [id => date]
        $existingDates = array_values($existing);             // ['2025-02-03', ...]

        $datesToAdd = array_diff($datesSelected, $existingDates);
        $datesToUpdate = array_intersect($datesSelected, $existingDates);
        $datesToRemove = array_diff($existingDates, $datesSelected); // giorni non più colorati -> rimuovi

        // Aggiungi nuovi
        foreach ($datesToAdd as $date) {
            Availability::create([
                'date_of_availability' => $date,
                'color' => $colorMap[$date],
            ]);
        }

        // Aggiorna colore ai già esistenti
        if (!empty($datesToUpdate)) {
            Availability::whereIn('date_of_availability', $datesToUpdate)
                ->get()
                ->each(function ($row) use ($colorMap) {
                    $row->color = $colorMap[$row->date_of_availability];
                    $row->save();
                });
        }

        // Rimuovi deselezionati (nessun colore scelto)
        if (!empty($datesToRemove)) {
            Availability::whereIn('date_of_availability', $datesToRemove)->delete();
        }

        return back()->with('success', 'Disponibilità aggiornata con successo!');
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

        // pivot: is_leader = true solo per il DSC
        $syncData = $assigned->mapWithKeys(function ($id) use ($leaderId) {
            return [
                $id => ['is_leader' => ($id == $leaderId)],
            ];
        })->toArray();

        // aggiorna gli assegnati alla gara
        $race->users()->sync($syncData);

        // ricarica cronometristi con pivot
        $race->load([
            'users' => function ($q) {
                $q->select('users.id', 'users.name', 'users.surname', 'users.email');
            },
        ]);

        $leader = $race->users->firstWhere('pivot.is_leader', true);

        // dati comuni alle mail
        $mailBaseData = [
            'raceName' => $race->name,
            'raceStart' => $race->date_of_race,
            'raceEnd' => $race->date_end ?? $race->date_of_race,
            'racePlace' => $race->place,

            // opzionali: se non li hai in config, lasciali pure null,
            // la view userà il testo [inserire ...]
            'contactEmail' => config('mail.from.address') ?? null,
            'contactPhone' => config('app.contact_phone') ?? null,
            // questi per ora li lascio null così usi i placeholder
            'meetInfo' => null,
            'raceStartTime' => null,
            'serviceManager' => null,
            'teamInfo' => null,
            'deadlineConfirm' => null,
            'replyEmail' => null,
            'placeToday' => null,
        ];

        foreach ($race->users as $user) {
            if (empty($user->email)) {
                continue;
            }

            $brevo = new BrevoMailer();

            $isLeader = $leader && $user->id === $leader->id;

            $view = $isLeader
                ? 'emails.timekeeper.raceConvocationDsc'
                : 'emails.timekeeper.raceConvocation';

            $subject = $isLeader
                ? 'Convocazione gara DSC'
                : 'Convocazione gara';

            $brevo->sendEmail(
                $user->email,
                $subject,
                $view,
                $mailBaseData
            );
        }

        return redirect()
            ->route('admin.racesList')
            ->with('success', 'Cronometristi aggiornati e convocazioni inviate con successo.');
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
                + ($record->food_not_documented ?? 0);

            $totalSum += $total;
        }

        return view('admin.racesReports', compact('race', 'records', 'cmsRecord', 'totalSum'));
    }
    public function editRace(Race $race)
    {
        return view('admin.racesEdit', compact('race'));
    }
    // public function updateRace(Request $request, Race $race)
    // {
    //     $allowedTypes = [
    //         'NUOTO -NUOTO SALVAMENTO',
    //         'SCI ALPINO – SCI NORDICO',
    //         'ATLETICA LEGGERA',
    //         'MOTORALLY',
    //         'RALLY',
    //         'ENDURO MOTO',
    //         'ENDURO MTB',
    //         'MOTOCROSS',
    //         'CANOA',
    //         'CANOTTAGGIO',
    //         'CICLISMO SU STRADA',
    //         'CICLISMO PISTA',
    //         'DOWHINILL',
    //         'AUTO REGOLARITA’',
    //         'AUTO STORICHE',
    //         'AUTOMOBILSMO CIRCUITO',
    //         'CONCORSO IPPICO',
    //         'TROTTO',
    //     ];

    //     $validated = $request->validate([
    //         'name' => 'required|string|max:255',
    //         'date_of_race' => 'required|date',
    //         'date_end' => 'nullable|date|after_or_equal:date_of_race',
    //         'place' => 'required|string|max:255',
    //         'type' => ['required', 'string', Rule::in($allowedTypes)],

    //         'ente_fatturazione' => 'nullable|string|max:255',
    //         'programma_allegato' => 'nullable|file|mimes:pdf,doc,docx,odt,zip|max:10240',
    //         'note' => 'nullable|string|max:5000',
    //     ]);

    //     // Mappa Type → specializzazioni (stessi slug usati nello store)
    //     $typeToSpecs = [
    //         'NUOTO -NUOTO SALVAMENTO' => ['elaborazione_dati', 'vasca'],
    //         'SCI ALPINO – SCI NORDICO' => ['partenza', 'arrivo', 'elaborazione_dati_completa', 'elaborazione_dati_parziale_live'],
    //         'ATLETICA LEGGERA' => ['fotofinish', 'manuale'],
    //         'MOTORALLY' => ['centro_classifica', 'tracking'],
    //         'RALLY' => ['centro_classifica', 'start_ps', 'fine_ps', 'controllo_orari_co', 'riordini', 'assistenza_partenza_arrivo', 'palco_premiazioni'],
    //         'ENDURO MOTO' => ['transponder_pc', 'solo_cronometraggio_start', 'solo_cronometraggio_fine', 'co_con_pc', 'co_solo_tablet'],
    //         'ENDURO MTB' => ['elaborazione_dati', 'partenza_prova', 'fine_prova'],
    //         'MOTOCROSS' => ['elaborazione_dati', 'arrivo'],
    //         'CANOA' => ['elaborazione_dati', 'arrivo', 'partenza_orologio_tablet', 'fotofinish'],
    //         'CANOTTAGGIO' => ['arrivo', 'partenza_orologio_tablet'],
    //         'CICLISMO SU STRADA' => ['arrivo', 'fotofinish'],
    //         'CICLISMO PISTA' => ['arrivo_bandelle', 'fotofinish'],
    //         'DOWHINILL' => ['partenza', 'arrivo', 'elaborazione_dati'],
    //         'AUTO REGOLARITA’' => ['pressostati', 'tablet'],
    //         'AUTO STORICHE' => ['arrivo', 'start'],
    //         'AUTOMOBILSMO CIRCUITO' => ['elaborazione_dati', 'contagiri', 'transponder'],
    //         'CONCORSO IPPICO' => ['prog_spec_concorso_ippico'],
    //         'TROTTO' => ['utilizzo_spec_programma'],
    //     ];

    //     // Upload allegato (sostituzione)
    //     if ($request->hasFile('programma_allegato')) {
    //         $newPath = $request->file('programma_allegato')->store('programmi_gara', 'public');
    //         if (!empty($race->programma_allegato) && Storage::disk('public')->exists($race->programma_allegato)) {
    //             Storage::disk('public')->delete($race->programma_allegato);
    //         }
    //         $validated['programma_allegato'] = $newPath;
    //     } else {
    //         unset($validated['programma_allegato']);
    //     }

    //     // Calcola specializzazioni dal type scelto
    //     $computedSpecs = $typeToSpecs[$validated['type']] ?? [];

    //     // Aggiorna i campi
    //     $race->update([
    //         'name' => $validated['name'],
    //         'date_of_race' => $validated['date_of_race'],
    //         'date_end' => $validated['date_end'] ?? null,
    //         'place' => $validated['place'],
    //         'type' => $validated['type'],
    //         'ente_fatturazione' => $validated['ente_fatturazione'] ?? null,
    //         'programma_allegato' => $validated['programma_allegato'] ?? $race->programma_allegato,
    //         'note' => $validated['note'] ?? null,
    //         'specialization_of_race' => $computedSpecs, // ricalcolato
    //     ]);

    //     return redirect()->route('admin.racesList')->with('success', 'Gara aggiornata con successo.');
    // }

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
