<x-layout documentTitle="Segreteria — Report Cronometrista">
    <main class="container mt-5 pt-5" aria-labelledby="user-title">
        <h1 id="user-title" class="mb-4">
            Report di {{ $user->surname }} {{ $user->name }}
        </h1>

        @forelse ($records as $raceId => $items)
            @php $race = $items->first()->race; @endphp

            <div class="card shadow-sm mb-4 tk-card p-3">
                <div class="card-header tk-card-header">
                    <p class="text-muted mb-4">
                        {{$race->name}}
                        {{ \Illuminate\Support\Carbon::parse($race->date_of_race)->translatedFormat('l d F') }}
                        @if ($race->date_end)
                            / {{ \Illuminate\Support\Carbon::parse($race->date_end)->translatedFormat('l d F') }}
                        @endif
                        -{{ $race->place }}
                    </p>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped mb-0 tk-cols-sep">
                            <thead class="table-light">
                                <tr>
                                    <th>Tipo</th> {{-- NUOVO --}}
                                    <th>€/Km</th> {{-- NUOVO --}}
                                    <th>Servizio Giornaliero</th>
                                    <th>Servizio Speciale</th>
                                    <th>Tariffa</th>
                                    <th>Km</th>
                                    <th>Importo Km</th> {{-- NUOVO (calcolato) --}}
                                    <th>Bigl.</th>
                                    <th>Vitto (doc.)</th>
                                    <th>Alloggio</th>
                                    <th>Varie</th>
                                    <th>Vitto (ND)</th>
                                    <th>Diaria (ND)</th>
                                    <th>Diaria Spec. (ND)</th>
                                    <th>Totale</th> {{-- NUOVO (calcolato) --}}
                                    <th>Note</th>
                                    <th>Conf.</th>
                                    <th>Allegati</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($items as $r)
                                    @php
                                        // €/Km del record (fallback 0.36)
                                        $ratePerKm = $r->euroKM !== null ? (float) $r->euroKM : 0.36;

                                        // Km della riga
                                        $km = (float) ($r->km_documented ?? 0);

                                        // Importo chilometrico calcolato al volo
                                        $amount = $km > 0 ? round($km * $ratePerKm, 2) : 0.0;

                                        // Totale ricalcolato per coerenza
                                        $total =
                                            $amount +
                                            (float) ($r->travel_ticket_documented ?? 0) +
                                            (float) ($r->food_documented ?? 0) +
                                            (float) ($r->accommodation_documented ?? 0) +
                                            (float) ($r->various_documented ?? 0) +
                                            (float) ($r->food_not_documented ?? 0) +
                                            (float) ($r->daily_allowances_not_documented ?? 0) +
                                            (float) ($r->special_daily_allowances_not_documented ?? 0);
                                    @endphp
                                    <tr>
                                        <td>{{ $r->type ?? '—' }}</td>
                                        <td>{{ number_format($ratePerKm, 2) }}</td>
                                        <td>{{ $r->daily_service }}</td>
                                        <td>{{ $r->special_service }}</td>
                                        <td>{{ $r->rate_documented }}</td>
                                        <td>{{ $r->km_documented }}</td>
                                        <td>{{ number_format($amount, 2) }}</td>
                                        <td>{{ $r->travel_ticket_documented }}</td>
                                        <td>{{ $r->food_documented }}</td>
                                        <td>{{ $r->accommodation_documented }}</td>
                                        <td>{{ $r->various_documented }}</td>
                                        <td>{{ $r->food_not_documented }}</td>
                                        <td>{{ $r->daily_allowances_not_documented }}</td>
                                        <td>{{ $r->special_daily_allowances_not_documented }}</td>
                                        <td><strong>{{ number_format($total, 2) }}</strong></td>
                                        <td>{{ $r->description }}</td>
                                        <td>
                                            @if ($r->confirmed)
                                                <span class="badge bg-success">Confermato</span>
                                            @else
                                                <span class="badge bg-warning text-dark">Da confermare</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($r->attachments && $r->attachments->count())
                                                <ul class="mb-0">
                                                    @foreach ($r->attachments as $att)
                                                        <li>
                                                            <a href="{{ route('attachments.show', $att) }}"
                                                                target="_blank" rel="noopener">
                                                                {{ $att->original_name }}
                                                            </a>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div> {{-- /.table-responsive --}}
                </div> {{-- /.card-body --}}
            </div> {{-- /.card --}}
        @empty
            <p class="text-muted">Nessun record disponibile per questo cronometrista.</p>
        @endforelse

        <a href="{{ route('secretariat.timekeepers.index') }}" class="btn btn-secondary">Torna all’elenco</a>
    </main>
</x-layout>
