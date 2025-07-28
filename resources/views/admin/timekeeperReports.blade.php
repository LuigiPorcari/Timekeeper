<x-layout documentTitle="Report Record Cronometrista">
    <main class="container mt-5 pt-5" id="main-content" aria-labelledby="report-title">
        <h1 id="report-title" class="mb-4">
            Report di {{ $user->name }} {{ $user->surname }}
        </h1>

        @forelse ($races as $race)
            <section class="mb-4" role="region" aria-labelledby="gara-{{ $race->id }}-title">
                <h2 id="gara-{{ $race->id }}-title" class="fw-bold text-primary h5">
                    Gara {{ $race->name }} del
                    {{ ucwords(\Carbon\Carbon::parse($race->date_of_race)->translatedFormat('l d F')) }}
                    a {{ $race->place }}
                </h2>

                @if ($race->records->isEmpty())
                    <p class="text-muted" role="status">Nessun record inserito per questa gara.</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead class="table-light">
                                <tr>
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
                                @foreach ($race->records as $record)
                                    <tr>
                                        <td>{{ $record->daily_service }}</td>
                                        <td>{{ $record->special_service }}</td>
                                        <td>{{ $record->rate_documented }}</td>
                                        <td>{{ $record->km_documented }}</td>
                                        <td>{{ number_format($record->amount_documented, 2) }}</td>
                                        <td>{{ $record->travel_ticket_documented }}</td>
                                        <td>{{ $record->food_documented }}</td>
                                        <td>{{ $record->accommodation_documented }}</td>
                                        <td>{{ $record->various_documented }}</td>
                                        <td>{{ $record->food_not_documented }}</td>
                                        <td>{{ $record->daily_allowances_not_documented }}</td>
                                        <td>{{ $record->special_daily_allowances_not_documented }}</td>
                                        <td><strong>{{ number_format($record->total, 2) }}</strong></td>
                                        <td>{{ $record->description }}</td>
                                        <td>
                                            @if ($record->attachments && $record->attachments->count())
                                                <ul class="list-unstyled mb-0">
                                                    @foreach ($record->attachments as $attachment)
                                                        <li>
                                                            <a href="{{ route('attachments.show', $attachment) }}"
                                                                target="_blank">{{ $attachment->original_name }}</a>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            @else
                                                <em>Nessuno</em>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>
        @empty
            <p class="text-muted" role="status">
                Il cronometrista non è assegnato a nessuna gara.
            </p>
        @endforelse
    </main>
</x-layout>
