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

    public function manage(Race $race)
    {
        $user = auth()->user();

        if ($user->isLeaderOf($race)) {
            $records = $race->records()->with('user', 'attachments')->get(); // tutti i record
        } else {
            $records = $race->records()->where('user_id', $user->id)->with('attachments')->get(); // solo i propri
        }

        return view('timekeeper.records.manage', compact('race', 'records'));
    }

    public function store(Request $request, Race $race)
    {
        // Se ci sono record confermati, blocca l'inserimento
        if ($race->records()->where('confirmed', true)->exists()) {
            return back()->with('error', 'Non è possibile aggiungere nuovi record: sono già stati confermati.');
        }

        $isLeader = auth()->user()->isLeaderOf($race);
        $allowedSpecs = is_array($race->specialization_of_race) ? $race->specialization_of_race : [];

        // Regole base
        $rules = [
            'type' => 'required|string|in:FC,CM,CP',
            // €/Km è inseribile SOLO dal DSC: per i non-DSC NON validiamo questo campo
            'daily_service' => 'nullable|integer',
            'special_service' => 'nullable|integer',
            'rate_documented' => 'nullable|string',

            'km_documented' => 'nullable|numeric',
            'travel_ticket_documented' => 'nullable|numeric',
            'food_documented' => 'nullable|numeric',
            'accommodation_documented' => 'nullable|numeric',
            'various_documented' => 'nullable|numeric',

            'description' => 'nullable|string',
        ];

        if ($isLeader) {
            $rules['euroKM'] = ['nullable', 'regex:/^\d{1,6}([,.]\d{1,2})?$/'];
            $rules['food_not_documented'] = 'nullable|numeric';
            $rules['daily_allowances_not_documented'] = 'nullable|numeric';
            $rules['special_daily_allowances_not_documented'] = 'nullable|numeric';
            $rules['transport_mode'] = 'required|in:trasportato,km';
            $rules['apparecchiature'] = 'nullable|array';
            $rules['apparecchiature.*'] = ['string', \Illuminate\Validation\Rule::in($allowedSpecs)];
        }

        $validated = $request->validate($rules);

        // €/Km: SOLO DSC può impostarlo; per altri rimane null (si usa default 0.36)
        $euroKM = ($isLeader && $request->filled('euroKM'))
            ? (float) str_replace(',', '.', $request->input('euroKM'))
            : null;

        $ratePerKm = $euroKM !== null ? $euroKM : 0.36;

        // Trasporto & km effettivi
        $transportMode = $isLeader ? ($request->input('transport_mode', 'km')) : 'km';
        $kmEffective = ($isLeader && $transportMode === 'km')
            ? (float) ($request->km_documented ?? 0)
            : 0;

        $amountDocumented = round($kmEffective * $ratePerKm, 2);

        // Totale
        $total = $amountDocumented
            + (float) ($request->travel_ticket_documented ?? 0)
            + (float) ($request->food_documented ?? 0)
            + (float) ($request->accommodation_documented ?? 0)
            + (float) ($request->various_documented ?? 0)
            + (float) ($request->food_not_documented ?? 0);

        $data = [
            'type' => $validated['type'],
            'euroKM' => $euroKM,

            'daily_service' => $request->daily_service,
            'special_service' => $request->special_service,
            'rate_documented' => $request->rate_documented,

            'km_documented' => $kmEffective,
            'amount_documented' => $amountDocumented,

            'travel_ticket_documented' => $request->travel_ticket_documented,
            'food_documented' => $request->food_documented,
            'accommodation_documented' => $request->accommodation_documented,
            'various_documented' => $request->various_documented,

            'total' => round($total, 2),
            'description' => $request->description,

            'user_id' => auth()->id(),
            'race_id' => $race->id,

            'transport_mode' => $transportMode,
        ];

        if ($isLeader) {
            $data['food_not_documented'] = $request->food_not_documented;
            $data['daily_allowances_not_documented'] = $request->daily_allowances_not_documented;
            $data['special_daily_allowances_not_documented'] = $request->special_daily_allowances_not_documented;
            $data['apparecchiature'] = $request->input('apparecchiature', []);
        }

        $record = Record::create($data);

        // Allegati multipli (se presenti)
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('attachments', 'public');

                $record->attachments()->create([
                    'file_path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                ]);
            }
        }

        return redirect()->route('records.manage', $race)
            ->with('success', 'Record aggiunto con successo.');
    }

    public function edit(Record $record)
    {
        $user = auth()->user();

        if ($user->id !== $record->user_id || $record->confirmed) {
            return redirect()
                ->route('records.manage', $record->race)
                ->with('error', 'Non hai i permessi per modificare questo record.');
        }

        return view('timekeeper.records.edit', compact('record'));
    }

    public function update(Request $request, Record $record)
    {
        if ($record->confirmed) {
            return back()->with('error', 'Questo record è stato confermato e non può essere modificato.');
        }

        if ($record->user_id !== auth()->id()) {
            return back()->with('error', 'Non hai i permessi per modificare questo record.');
        }

        $race = $record->race;
        $isLeader = auth()->user()->isLeaderOf($race);
        $allowedSpecs = is_array($race->specialization_of_race) ? $race->specialization_of_race : [];

        // Regole base
        $rules = [
            'type' => 'required|string|in:FC,CM,CP',
            // €/Km solo DSC
            'daily_service' => 'nullable|integer',
            'special_service' => 'nullable|integer',
            'rate_documented' => 'nullable|string',

            'km_documented' => 'nullable|numeric',
            'travel_ticket_documented' => 'nullable|numeric',
            'food_documented' => 'nullable|numeric',
            'accommodation_documented' => 'nullable|numeric',
            'various_documented' => 'nullable|numeric',

            'description' => 'nullable|string',
        ];

        if ($isLeader) {
            $rules['euroKM'] = ['nullable', 'regex:/^\d{1,6}([,.]\d{1,2})?$/'];
            $rules['food_not_documented'] = 'nullable|numeric';
            $rules['daily_allowances_not_documented'] = 'nullable|numeric';
            $rules['special_daily_allowances_not_documented'] = 'nullable|numeric';
            $rules['transport_mode'] = 'required|in:trasportato,km';
            $rules['apparecchiature'] = 'nullable|array';
            $rules['apparecchiature.*'] = ['string', \Illuminate\Validation\Rule::in($allowedSpecs)];
        }

        $validated = $request->validate($rules);

        // €/Km: solo DSC può modificarlo; altrimenti manteniamo quello già salvato
        $euroKM = ($isLeader && $request->filled('euroKM'))
            ? (float) str_replace(',', '.', $request->input('euroKM'))
            : $record->euroKM;

        $ratePerKm = $euroKM !== null ? $euroKM : 0.36;

        // Trasporto & km effettivi
        $transportMode = $isLeader
            ? ($request->input('transport_mode', $record->transport_mode ?? 'km'))
            : ($record->transport_mode ?? 'km');

        $kmEffective = ($isLeader && $transportMode === 'km')
            ? (float) ($request->km_documented ?? 0)
            : ($transportMode === 'km' ? ($record->km_documented ?? 0) : 0);

        $amountDocumented = round($kmEffective * $ratePerKm, 2);

        $update = [
            'type' => $validated['type'],
            'euroKM' => $euroKM,

            'daily_service' => $request->daily_service,
            'special_service' => $request->special_service,
            'rate_documented' => $request->rate_documented,

            'km_documented' => $kmEffective,
            'amount_documented' => $amountDocumented,

            'travel_ticket_documented' => $request->travel_ticket_documented,
            'food_documented' => $request->food_documented,
            'accommodation_documented' => $request->accommodation_documented,
            'various_documented' => $request->various_documented,

            'description' => $request->description,
            'transport_mode' => $transportMode,
        ];

        if ($isLeader) {
            $update['food_not_documented'] = $request->food_not_documented;
            $update['daily_allowances_not_documented'] = $request->daily_allowances_not_documented;
            $update['special_daily_allowances_not_documented'] = $request->special_daily_allowances_not_documented;
            $update['apparecchiature'] = $request->input('apparecchiature', []);
        }

        $record->update($update);

        return redirect()->route('records.manage', $record->race)->with('success', 'Record aggiornato con successo.');
    }

    public function destroy(Record $record)
    {
        if ($record->confirmed) {
            return back()->with('error', 'Questo record è stato confermato e non può essere eliminato.');
        }

        if ($record->user_id !== auth()->id()) {
            return back()->with('error', 'Non hai i permessi per eliminare questo record.');
        }

        $record->delete();
        return back()->with('success', 'Record eliminato con successo.');
    }

    public function confirm(Record $record)
    {
        $user = auth()->user();

        if (!$user->isLeaderOf($record->race)) {
            return redirect()
                ->route('records.manage', $record->race)
                ->with('error', 'Non hai i permessi per confermare questo record.');
        }

        $record->update(['confirmed' => true]);

        return redirect()
            ->route('records.manage', $record->race)
            ->with('success', 'Record confermato con successo.');
    }

    public function confirmAll(Race $race)
    {
        $user = auth()->user();

        if (!$user->isLeaderOf($race)) {
            return back()->with('error', 'Non hai i permessi per confermare i record di questa gara.');
        }

        $race->records()->where('confirmed', false)->update(['confirmed' => true]);

        return back()->with('success', 'Tutti i record sono stati confermati con successo.');
    }
}
