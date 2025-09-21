<x-layout documentTitle="Segreteria — Dettaglio Gara">
    <main class="container mt-5 pt-5" aria-labelledby="race-title">
        <h1 id="race-title" class="mb-1">{{ $race->name }}</h1>
        <p class="text-muted mb-4">
            {{ \Illuminate\Support\Carbon::parse($race->date_of_race)->translatedFormat('l d F') }}
            @if ($race->date_end)
                / {{ \Illuminate\Support\Carbon::parse($race->date_end)->translatedFormat('l d F') }}
            @endif
            -{{ $race->place }}
        </p>

        @php
            // Ricava i km del DSC (se presenti) per usarli come fallback
            $dscKm = null;
            foreach ($rows as $rowTmp) {
                $recTmp = $rowTmp['record'] ?? null;
                if ($recTmp && $recTmp->user && $recTmp->user->isLeaderOf($race)) {
                    if (!is_null($recTmp->km_documented) && $recTmp->km_documented !== '') {
                        $dscKm = (float) $recTmp->km_documented;
                    }
                    break;
                }
            }
            $computedGrandTotal = 0.0; // ricalcoliamo il totale complessivo in vista con la logica di fallback
        @endphp

        <div class="card shadow-sm p-3">
            <div class="card-header">
                <strong>Dettaglio rendiconti</strong>
            </div>
            <div class="card-body p-0">
                <table class="table table-bordered table-striped table-sm mb-0 tk-table-separated">
                    <thead class="table-light">
                        <tr>
                            <th>Operatore</th>
                            <th>Tipo</th> {{-- NUOVO --}}
                            <th>€/Km</th> {{-- NUOVO --}}
                            <th>Serv. Giornaliero</th>
                            <th>Serv. Speciale</th>
                            <th>Tariffa</th>
                            <th>Km (usati)</th>
                            <th>Importo Km</th>
                            <th>Bigl.</th>
                            <th>Vitto (doc.)</th>
                            <th>Alloggio (doc.)</th>
                            <th>Varie (doc.)</th>
                            <th>Vitto (ND)</th>
                            <th>Diaria (ND)</th>
                            <th>Diaria Spec. (ND)</th>
                            <th>Totale</th>
                            <th>Stato</th>
                            <th>Allegati</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rows as $row)
                            @php
                                /** @var \App\Models\Record $r */
                                $r = $row['record'];

                                // €/Km dal record (fallback 0.36)
                                $ratePerKm = $r->euroKM !== null ? (float) $r->euroKM : 0.36;

                                // Km del record (se assenti/zero usa i km del DSC)
                                $kmRaw = (float) ($r->km_documented ?? 0);
                                $kmUsed = $kmRaw > 0 ? $kmRaw : ((float) ($dscKm ?? 0));

                                // Importo km e totale riga calcolati con i km "usati"
                                $kmAmount = $kmUsed > 0 ? round($kmUsed * $ratePerKm, 2) : 0.0;

                                $rowTotal =
                                    $kmAmount +
                                    (float) ($r->travel_ticket_documented ?? 0) +
                                    (float) ($r->food_documented ?? 0) +
                                    (float) ($r->accommodation_documented ?? 0) +
                                    (float) ($r->various_documented ?? 0) +
                                    (float) ($r->food_not_documented ?? 0) +
                                    (float) ($r->daily_allowances_not_documented ?? 0) +
                                    (float) ($r->special_daily_allowances_not_documented ?? 0);

                                $computedGrandTotal += $rowTotal;
                            @endphp
                            <tr>
                                <td>{{ $r->user->surname }} {{ $r->user->name }}</td>
                                <td>{{ $r->type ?? '—' }}</td>
                                <td>{{ number_format($ratePerKm, 2, ',', '.') }}</td>
                                <td>{{ $r->daily_service }}</td>
                                <td>{{ $r->special_service }}</td>
                                <td>{{ $r->rate_documented }}</td>
                                <td>{{ $kmUsed }}</td>
                                <td>{{ number_format($kmAmount, 2, ',', '.') }}</td>
                                <td>{{ $r->travel_ticket_documented }}</td>
                                <td>{{ $r->food_documented }}</td>
                                <td>{{ $r->accommodation_documented }}</td>
                                <td>{{ $r->various_documented }}</td>
                                <td>{{ $r->food_not_documented }}</td>
                                <td>{{ $r->daily_allowances_not_documented }}</td>
                                <td>{{ $r->special_daily_allowances_not_documented }}</td>
                                <td><strong>{{ number_format($rowTotal, 2, ',', '.') }}</strong></td>
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
                                                    <a href="{{ route('attachments.show', $att) }}" target="_blank"
                                                        rel="noopener">
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
                        @empty
                            <tr>
                                <td colspan="18" class="text-center text-muted">Nessun record.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr>
                            {{-- +2 colonne (Tipo, €/Km) rispetto alla versione originaria --}}
                            <th colspan="15" class="text-end">Totale complessivo</th>
                            <th>{{ number_format($computedGrandTotal, 2, ',', '.') }}</th>
                            <th colspan="2"></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        <div class="card-footer d-flex justify-content-end mt-3">
            <a href="{{ route('secretariat.races.index') }}" class="btn btn-secondary">Torna all’elenco</a>
        </div>
    </main>
</x-layout>
