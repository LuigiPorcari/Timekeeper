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
        if ($race->records()->where('confirmed', true)->exists()) {
            return back()->with('error', 'Non è possibile aggiungere nuovi record: sono già stati confermati.');
        }

        $validated = $request->validate([
            'daily_service' => 'nullable|integer',
            'special_service' => 'nullable|integer',
            'rate_documented' => 'nullable|string',
            'km_documented' => 'nullable|numeric',
            'travel_ticket_documented' => 'nullable|numeric',
            'food_documented' => 'nullable|numeric',
            'accommodation_documented' => 'nullable|numeric',
            'various_documented' => 'nullable|numeric',
            'food_not_documented' => 'nullable|numeric',
            'daily_allowances_not_documented' => 'nullable|numeric',
            'special_daily_allowances_not_documented' => 'nullable|numeric',
            'description' => 'nullable|string',
        ]);

        $amountDocumented = round(($request->km_documented ?? 0) * 0.36, 2);

        $total = $amountDocumented
            + ($request->travel_ticket_documented ?? 0)
            + ($request->food_documented ?? 0)
            + ($request->accommodation_documented ?? 0)
            + ($request->various_documented ?? 0)
            + ($request->food_not_documented ?? 0)
            + ($request->daily_allowances_not_documented ?? 0)
            + ($request->special_daily_allowances_not_documented ?? 0);

        $record = Record::create([
            'daily_service' => $request->daily_service,
            'special_service' => $request->special_service,
            'rate_documented' => $request->rate_documented,
            'km_documented' => $request->km_documented,
            'amount_documented' => $amountDocumented,
            'travel_ticket_documented' => $request->travel_ticket_documented,
            'food_documented' => $request->food_documented,
            'accommodation_documented' => $request->accommodation_documented,
            'various_documented' => $request->various_documented,
            'food_not_documented' => $request->food_not_documented,
            'daily_allowances_not_documented' => $request->daily_allowances_not_documented,
            'special_daily_allowances_not_documented' => $request->special_daily_allowances_not_documented,
            'total' => round($total, 2),
            'description' => $request->description,
            'user_id' => auth()->id(),
            'race_id' => $race->id,
        ]);

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('attachments', 'public');

                $record->attachments()->create([
                    'file_path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                ]);
            }
        }

        return redirect()->route('records.manage', $race)->with('success', 'Record aggiunto con successo.');
    }



    public function update(Request $request, Record $record)
    {
        if ($record->confirmed) {
            return back()->with('error', 'Questo record è stato confermato e non può essere modificato.');
        }

        if ($record->user_id !== auth()->id()) {
            return back()->with('error', 'Non hai i permessi per modificare questo record.');
        }

        $validated = $request->validate([
            'daily_service' => 'nullable|integer',
            'special_service' => 'nullable|integer',
            'rate_documented' => 'nullable|string',
            'km_documented' => 'nullable|numeric',
            'travel_ticket_documented' => 'nullable|numeric',
            'food_documented' => 'nullable|numeric',
            'accommodation_documented' => 'nullable|numeric',
            'various_documented' => 'nullable|numeric',
            'food_not_documented' => 'nullable|numeric',
            'daily_allowances_not_documented' => 'nullable|numeric',
            'special_daily_allowances_not_documented' => 'nullable|numeric',
            'description' => 'required|string',
        ]);

        $amountDocumented = round(($request->km_documented ?? 0) * 0.36, 2);

        $total = $amountDocumented
            + ($request->travel_ticket_documented ?? 0)
            + ($request->food_documented ?? 0)
            + ($request->accommodation_documented ?? 0)
            + ($request->various_documented ?? 0)
            + ($request->food_not_documented ?? 0)
            + ($request->daily_allowances_not_documented ?? 0)
            + ($request->special_daily_allowances_not_documented ?? 0);

        $record->update([
            'daily_service' => $request->daily_service,
            'special_service' => $request->special_service,
            'rate_documented' => $request->rate_documented,
            'km_documented' => $request->km_documented,
            'amount_documented' => $amountDocumented,
            'travel_ticket_documented' => $request->travel_ticket_documented,
            'food_documented' => $request->food_documented,
            'accommodation_documented' => $request->accommodation_documented,
            'various_documented' => $request->various_documented,
            'food_not_documented' => $request->food_not_documented,
            'daily_allowances_not_documented' => $request->daily_allowances_not_documented,
            'special_daily_allowances_not_documented' => $request->special_daily_allowances_not_documented,
            'total' => round($total, 2),
            'description' => $request->description,
        ]);

        return back()->with('success', 'Record aggiornato con successo.');
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
            return back()->with('error', 'Non hai i permessi per confermare questo record.');
        }

        $record->update(['confirmed' => true]);

        return back()->with('success', 'Record confermato con successo.');
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
