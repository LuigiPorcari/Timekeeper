<?php

namespace App\Http\Controllers;

use App\Models\Race;
use App\Models\User;
use App\Models\Record;
use App\Models\Availability;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TimekeeperController extends Controller
{

    public function dashboard()
    {
        return view('timekeeper.dashboard');
    }
    public function showForUser()
    {
        $availabilities = Availability::orderBy('date_of_availability')->get();
        $selected = auth()->user()->availabilities()->pluck('availability_id')->toArray();

        return view('timekeeper.availabilitiesList', compact('availabilities', 'selected'));
    }
    public function storeForUser(Request $request)
    {
        $validated = $request->validate([
            'dates' => 'nullable|array',
            'dates.*' => 'integer|exists:availabilities,id',
        ]);

        auth()->user()->availabilities()->sync($validated['dates'] ?? []);

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
        $records = $race->records()->where('user_id', Auth::id())->get();
        return view('timekeeper.records.manage', compact('race', 'records'));
    }
    public function store(Request $request, Race $race)
    {
        $request->validate(['description' => 'required|string']);

        Record::create([
            'description' => $request->description,
            'user_id' => auth()->id(),
            'race_id' => $race->id,
        ]);

        return redirect()->route('records.manage', $race)->with('success', 'Record aggiunto con successo.');
    }

    public function update(Request $request, Record $record)
    {
        if ($record->user_id !== auth()->id()) {
            return back()->with('error', 'Non hai i permessi per modificare questo record.');
        }

        $record->update($request->validate(['description' => 'required|string']));
        return back()->with('success', 'Record aggiornato con successo.');
    }

    public function destroy(Record $record)
    {
        if ($record->user_id !== auth()->id()) {
            return back()->with('error', 'Non hai i permessi per eliminare questo record.');
        }

        $record->delete();
        return back()->with('success', 'Record eliminato con successo.');
    }

}
