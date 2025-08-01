<?php

namespace App\Http\Controllers;

use App\Models\Race;
use App\Models\RaceTemp;
use App\Models\User;
use App\Models\Availability;
use Illuminate\Http\Request;

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
        // Validazione
        $validated = $request->validate([
            'specialization' => 'nullable|array',
            'specialization.*' => 'string',
        ]);
        // Salva l'array delle specializzazioni (anche vuoto se nessuna checkbox selezionata)
        $timekeeper->specialization = $request->input('specialization', []);
        // Salva nel database
        $timekeeper->save();
        // Redirect con messaggio di successo
        return redirect()->back()->with('success', 'Specializzazioni aggiornate con successo!');
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
        // Validazione inclusiva del campo name
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'date_of_race' => 'required|date',
            'place' => 'required|string|max:255',
            'specialization_of_race' => 'nullable|array',
            'specialization_of_race.*' => 'string',
        ]);

        // Creazione della nuova gara
        $race = new Race();
        $race->name = $request->input('name');
        $race->date_of_race = $request->input('date_of_race');
        $race->place = $request->input('place');
        $race->specialization_of_race = $request->input('specialization_of_race', []); // salva come array
        $race->save();

        // Redirect con messaggio di successo
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

        // Filtra i cronometristi con disponibilità e specializzazione in comune
        $timekeepers = User::where('is_timekeeper', 1)
            ->whereHas('availabilities', function ($query) use ($raceDate) {
                $query->where('date_of_availability', $raceDate);
            })
            ->get()
            ->filter(function ($user) use ($raceSpecializations) {
                return count(array_intersect($user->specialization ?? [], $raceSpecializations)) > 0;
            });

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
        $request->validate([
            'name' => 'required|string|max:255',
            'date_of_race' => 'required|date',
            'place' => 'required|string|max:255',
            'specialization_of_race' => 'nullable|array',
        ]);

        $race->update($request->only(['name', 'date_of_race', 'place', 'specialization_of_race']));

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
