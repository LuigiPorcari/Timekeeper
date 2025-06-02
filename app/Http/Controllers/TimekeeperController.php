<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Availability;
use Illuminate\Http\Request;

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


}
