<?php

namespace App\Http\Controllers;

use App\Models\Race;
use App\Models\Record;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class SecretariatController extends Controller
{

    public function recordUpdate(Request $request, Record $record)
    {
        // Qui presumo che i permessi della Segreteria siano già gestiti via middleware/policy/ruolo.
        // Validazione
        $validated = $request->validate([
            'type' => 'required|string|in:FC,CM,CP',
            'euroKM' => ['nullable', 'regex:/^\d{1,6}([,.]\d{1,2})?$/'],

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

        // Normalizza €/Km (virgola -> punto)
        $euroKM = $request->filled('euroKM')
            ? (float) str_replace(',', '.', $request->input('euroKM'))
            : null;

        $ratePerKm = $euroKM !== null ? $euroKM : 0.36;

        // Km effettivi (in segreteria settiamo direttamente il valore sul record)
        $km = (float) ($request->km_documented ?? 0);
        $amountDocumented = round($km * $ratePerKm, 2);

        // Totale riga
        $total = $amountDocumented
            + (float) ($request->travel_ticket_documented ?? 0)
            + (float) ($request->food_documented ?? 0)
            + (float) ($request->accommodation_documented ?? 0)
            + (float) ($request->various_documented ?? 0)
            + (float) ($request->food_not_documented ?? 0);

        $record->update([
            'type' => $validated['type'],
            'euroKM' => $euroKM,

            'daily_service' => $request->daily_service,
            'special_service' => $request->special_service,
            'rate_documented' => $request->rate_documented,

            'km_documented' => $km,
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
    public function timekeepersShow(User $user, Request $request)
    {
        // Periodo opzionale come nel filtro principale
        $from = $request->input('from') ? Carbon::parse($request->input('from'))->startOfDay() : null;
        $to = $request->input('to') ? Carbon::parse($request->input('to'))->endOfDay() : null;

        $records = Record::query()
            ->where('user_id', $user->id)
            ->when($from, fn($q) => $q->whereHas('race', fn($r) => $r->whereDate('date_of_race', '>=', $from)))
            ->when($to, fn($q) => $q->whereHas('race', fn($r) => $r->whereDate('date_of_race', '<=', $to)))
            ->with(['race', 'attachments'])
            ->orderByDesc('id')
            ->get()
            ->groupBy('race_id'); // 👈 raggruppo per gara

        return view('secretariat.timekeepers.show', compact('user', 'records', 'from', 'to'));
    }

    public function dashboard()
    {
        // Nessun controllo: gestisci tu il redirect post-login
        $racesCount = Race::count();
        // Adatta questo filtro se usi ruoli diversi:
        $timekeepersCount = User::where('is_timekeeper', true)->count();
        $recordsCount = Record::count();

        return view('secretariat.dashboard', compact('racesCount', 'timekeepersCount', 'recordsCount'));
    }

    // Elenco gare con filtri (solo lettura)
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
            ->withCount(['records as records_total' => fn($q) => $q])
            ->withCount(['records as records_unconfirmed' => fn($q) => $q->where('confirmed', false)])
            ->orderByDesc('date_of_race')
            ->paginate(20)
            ->withQueryString();

        // Filtro stato lato collection
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

    // Dettaglio gara (readonly)
    public function racesShow(Race $race)
    {
        $records = $race->records()
            ->with(['user', 'attachments'])
            ->orderBy('user_id')
            ->get();

        $rows = $records->map(function (Record $r) {
            $kmAmount = $r->km_documented ? round($r->km_documented * 0.36, 2) : 0;
            $total = $kmAmount
                + ($r->travel_ticket_documented ?? 0)
                + ($r->food_documented ?? 0)
                + ($r->accommodation_documented ?? 0)
                + ($r->various_documented ?? 0)
                + ($r->food_not_documented ?? 0);

            return [
                'record' => $r,
                'kmAmount' => $kmAmount,
                'total' => $total,
            ];
        });

        $grandTotal = $rows->sum('total');

        return view('secretariat.races.show', compact('race', 'rows', 'grandTotal'));
    }

    // Report cronometristi per periodo (readonly)
    public function timekeepersIndex(Request $request)
    {
        $from = $request->input('from') ? Carbon::parse($request->input('from'))->startOfDay() : Carbon::now()->startOfMonth();
        $to = $request->input('to') ? Carbon::parse($request->input('to'))->endOfDay() : Carbon::now()->endOfMonth();
        $q = trim((string) $request->input('q'));

        $records = Record::query()
            ->whereHas('race', fn($qr) => $qr->whereBetween('date_of_race', [$from, $to]))
            ->with(['user', 'race'])
            ->when($q, fn($qr) => $qr->whereHas('user', function ($w) use ($q) {
                $w->where('name', 'like', "%$q%")
                    ->orWhere('surname', 'like', "%$q%");
            }))
            ->orderBy('user_id')
            ->orderBy('id')
            ->get();

        $byUser = $records->groupBy('user_id')->map(function ($items) {
            $u = $items->first()->user;
            $totKm = 0;
            $tot = 0;

            foreach ($items as $r) {
                $kmAmount = $r->km_documented ? round($r->km_documented * 0.36, 2) : 0;
                $rowTotal = $kmAmount
                    + ($r->travel_ticket_documented ?? 0)
                    + ($r->food_documented ?? 0)
                    + ($r->accommodation_documented ?? 0)
                    + ($r->various_documented ?? 0)
                    + ($r->food_not_documented ?? 0);

                $totKm += $kmAmount;
                $tot += $rowTotal;
            }

            return [
                'user' => $u,
                'count' => $items->count(),
                'kmAmount' => round($totKm, 2),
                'total' => round($tot, 2),
            ];
        })->sortBy(fn($r) => $r['user']->surname);

        return view('secretariat.timekeepers.index', [
            'from' => $from,
            'to' => $to,
            'q' => $q,
            'rows' => $byUser,
        ]);
    }
}
