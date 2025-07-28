<x-layout documentTitle="Report Gara">
    <main class="container mt-5 pt-5" id="main-content" aria-labelledby="report-title">
        <h1 id="report-title" class="mb-4">
            Report per la Gara {{ $race->name }} di
            {{ ucwords(\Carbon\Carbon::parse($race->date_of_race)->translatedFormat('l d F')) }}
            a {{ $race->place }}
        </h1>

        @if ($records->isEmpty())
            <p class="text-muted" role="status">Nessun record disponibile per questa gara.</p>
        @else
            <table class="table table-bordered table-striped mt-3">
                <thead class="table-light">
                    <tr>
                        <th rowspan="2">Operatore</th>
                        <th rowspan="2">Servizio Giornaliero</th>
                        <th rowspan="2">Servizio Speciale</th>
                        <th rowspan="2">Tariffa</th>
                        <th rowspan="2">Km</th>
                        <th rowspan="2">€ Km (0.36)</th>
                        <th colspan="4" class="text-center">Spesa Documentata</th>
                        <th colspan="3" class="text-center">Spesa NON Documentata</th>
                        <th rowspan="2">Totale</th>
                        <th rowspan="2">Descrizione</th>
                        <th rowspan="2">Allegati</th>
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
                            $useCmsValues = $cmsRecord && $record->user_id !== $cmsRecord->user_id;
                            $km = $useCmsValues ? $cmsRecord->km_documented : $record->km_documented;
                            $amount = $useCmsValues ? $cmsRecord->amount_documented : $record->amount_documented;

                            $total =
                                $amount +
                                ($record->travel_ticket_documented ?? 0) +
                                ($record->food_documented ?? 0) +
                                ($record->accommodation_documented ?? 0) +
                                ($record->various_documented ?? 0) +
                                ($record->food_not_documented ?? 0) +
                                ($record->daily_allowances_not_documented ?? 0) +
                                ($record->special_daily_allowances_not_documented ?? 0);
                        @endphp
                        <tr>
                            <td>{{ $record->user->name }} {{ $record->user->surname }}</td>
                            <td>{{ $record->daily_service }}</td>
                            <td>{{ $record->special_service }}</td>
                            <td>{{ $record->rate_documented }}</td>
                            <td>{{ $km }}</td>
                            <td>{{ number_format($amount, 2) }}</td>
                            <td>{{ $record->travel_ticket_documented }}</td>
                            <td>{{ $record->food_documented }}</td>
                            <td>{{ $record->accommodation_documented }}</td>
                            <td>{{ $record->various_documented }}</td>
                            <td>{{ $record->food_not_documented }}</td>
                            <td>{{ $record->daily_allowances_not_documented }}</td>
                            <td>{{ $record->special_daily_allowances_not_documented }}</td>
                            <td><strong>{{ number_format($total, 2) }}</strong></td>
                            <td>{{ $record->description }}</td>
                            <td>
                                @if ($record->attachments && $record->attachments->count())
                                    <ul class="list-unstyled mb-0">
                                        @foreach ($record->attachments as $attachment)
                                            <li>
                                                <a href="{{ route('attachments.show', $attachment) }}" target="_blank">
                                                    {{ $attachment->original_name }}
                                                </a>
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <em>Nessuno</em>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    <tr class="table-secondary fw-bold">
                        <td colspan="13" class="text-end">Totale Generale</td>
                        <td>{{ number_format($totalSum, 2) }}</td>
                        <td colspan="2"></td>
                    </tr>
                </tbody>
            </table>
        @endif
    </main>
</x-layout>
