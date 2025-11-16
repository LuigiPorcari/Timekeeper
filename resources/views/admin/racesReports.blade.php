<x-layout documentTitle="Report Gara">
    <main class="container-fluid mt-5 pt-5" id="main-content" aria-labelledby="report-title">
        <div class="row justify-content-center">
            <div class="col-12 col-xxl-11">
                <div class="card shadow-sm border-0">
                    <div class="card-header border-0 bg-white">
                        <h1 id="report-title" class="h3 mb-1">
                            Report per la Gara {{ $race->name }}
                        </h1>
                        <p class="text-muted mb-0">
                            {{ ucwords(\Carbon\Carbon::parse($race->date_of_race)->translatedFormat('l d F')) }}
                            @if ($race->date_end)
                                / {{ ucwords(\Carbon\Carbon::parse($race->date_end)->translatedFormat('l d F')) }}
                            @endif
                            · {{ $race->place }}
                        </p>
                    </div>

                    <div class="card-body pt-0">
                        @if ($records->isEmpty())
                            <p class="text-muted" role="status">Nessun record disponibile per questa gara.</p>
                        @else
                            <div class="table-responsive-md">
                                <table
                                    class="table table-striped table-hover align-middle table-bordered table-border-black mt-3">
                                    <caption class="visually-hidden">
                                        Dettaglio rendicontazioni per operatore, con tipologia, tariffa chilometrica e
                                        spese.
                                    </caption>
                                    <thead class="table-light">
                                        <tr>
                                            <th rowspan="2">Operatore</th>
                                            <th rowspan="2">Tipo</th>
                                            <th rowspan="2">€/Km</th>
                                            <th rowspan="2">Servizio Giornaliero</th>
                                            <th rowspan="2">Servizio Speciale</th>
                                            <th rowspan="2">Tariffa</th>
                                            <th rowspan="2">Km</th>
                                            <th rowspan="2">Importo Km</th>
                                            <th colspan="4" class="text-center">Spesa Documentata</th>
                                            <th colspan="3" class="text-center">Spesa NON Documentata</th>
                                            <th rowspan="2">Totale</th>
                                        </tr>
                                        <tr>
                                            <th>Biglietto</th>
                                            <th>Vitto</th>
                                            <th>Alloggio</th>
                                            <th>Varie</th>
                                            <th>Vitto</th>
                                            <th>Diaria</th>
                                            <th>Diaria Spec.</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($records as $record)
                                            @php
                                                // Se esiste un record DSC e questa riga non è del DSC, usa i suoi km per la visualizzazione
                                                $useCmsValues =
                                                    isset($cmsRecord) &&
                                                    $cmsRecord &&
                                                    $record->user_id !== $cmsRecord->user_id;
                                                $kmDisplay = $useCmsValues
                                                    ? $cmsRecord->km_documented
                                                    : $record->km_documented;

                                                // €/Km: usa il valore del record, fallback 0.36
                                                $ratePerKm = $record->euroKM !== null ? (float) $record->euroKM : 0.36;

                                                // Importo km con euroKM effettivo
                                                $amount = $kmDisplay
                                                    ? round(((float) $kmDisplay) * $ratePerKm, 2)
                                                    : 0.0;

                                                // Totale di riga
                                                $rowTotal =
                                                    $amount +
                                                    (float) ($record->travel_ticket_documented ?? 0) +
                                                    (float) ($record->food_documented ?? 0) +
                                                    (float) ($record->accommodation_documented ?? 0) +
                                                    (float) ($record->various_documented ?? 0) +
                                                    (float) ($record->food_not_documented ?? 0) +
                                                    (float) ($record->daily_allowances_not_documented ?? 0) +
                                                    (float) ($record->special_daily_allowances_not_documented ?? 0);
                                            @endphp

                                            {{-- Riga principale dati --}}
                                            <tr>
                                                <td>{{ $record->user->name }} {{ $record->user->surname }}</td>
                                                <td>{{ $record->type ?? '—' }}</td>
                                                <td>{{ number_format($ratePerKm, 2, ',', '.') }}</td>
                                                <td>{{ $record->daily_service }}</td>
                                                <td>{{ $record->special_service }}</td>
                                                <td>{{ $record->rate_documented }}</td>
                                                <td>{{ $kmDisplay }}</td>
                                                <td>{{ number_format((float) $amount, 2, ',', '.') }}</td>
                                                <td>{{ $record->travel_ticket_documented }}</td>
                                                <td>{{ $record->food_documented }}</td>
                                                <td>{{ $record->accommodation_documented }}</td>
                                                <td>{{ $record->various_documented }}</td>
                                                <td>{{ $record->food_not_documented }}</td>
                                                <td>{{ $record->daily_allowances_not_documented }}</td>
                                                <td>{{ $record->special_daily_allowances_not_documented }}</td>
                                                <td><strong>{{ number_format($rowTotal, 2, ',', '.') }}</strong></td>
                                            </tr>

                                            {{-- Riga secondaria: Descrizione + Allegati --}}
                                            <tr class="bg-light">
                                                <td colspan="16">
                                                    <div class="py-2">
                                                        <div class="mb-1">
                                                            <strong>Descrizione:</strong>
                                                            <span
                                                                class="text-break">{{ $record->description ?: '—' }}</span>
                                                        </div>
                                                        <div>
                                                            <strong>Allegati:</strong>
                                                            @if ($record->attachments && $record->attachments->count())
                                                                <ul class="list-unstyled d-inline mb-0">
                                                                    @foreach ($record->attachments as $attachment)
                                                                        <li class="d-inline me-2">
                                                                            <a href="{{ route('attachments.show', $attachment) }}"
                                                                                target="_blank"
                                                                                class="link-primary text-decoration-none">
                                                                                {{ $attachment->original_name }}
                                                                            </a>
                                                                        </li>
                                                                    @endforeach
                                                                </ul>
                                                            @else
                                                                <span class="text-muted">Nessuno</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach

                                        {{-- Totale complessivo --}}
                                        <tr class="table-secondary fw-bold">
                                            <td colspan="15" class="text-end">Totale Generale</td>
                                            <td>{{ number_format($totalSum, 2, ',', '.') }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </main>
</x-layout>
