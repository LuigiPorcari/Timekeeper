<?php

namespace App\Http\Controllers;

use App\Models\Race;
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
    public function createRaceShow()
    {
        return view('admin.createRace');
    }
    public function storeRace(Request $request)
    {
        // Validazione base
        $validated = $request->validate([
            'date_of_race' => 'required|date',
            'place' => 'required|string|max:255',
            'specialization_of_race' => 'nullable|array',
            'specialization_of_race.*' => 'string',
        ]);

        // Creazione della nuova gara
        $race = new Race();
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
}
