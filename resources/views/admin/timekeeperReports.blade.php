<x-layout documentTitle="Report Record Cronometrista">
    <main class="container mt-5 pt-5" id="main-content" aria-labelledby="report-title">
        <h1 id="report-title" class="mb-4">
            Report di {{ $user->name }} {{ $user->surname }}
        </h1>

        @forelse ($races as $race)
            <section class="mb-4" role="region" aria-labelledby="gara-{{ $race->id }}-title">
                <div class="card shadow-sm rounded-3 p-3">
                    <div class="card-header bg-white">
                        <h2 id="gara-{{ $race->id }}-title" class="h5 mb-0 text-primary fw-bold">
                            Gara {{ $race->name }}
                            <small class="text-muted d-block">
                                {{ ucwords(\Carbon\Carbon::parse($race->date_of_race)->translatedFormat('l d F')) }}
                                @if ($race->date_end)
                                    / {{ ucwords(\Carbon\Carbon::parse($race->date_end)->translatedFormat('l d F')) }}
                                @endif
                                @if ($race->place)
                                    — {{ $race->place }}
                                @endif
                            </small>
                        </h2>
                    </div>

                    @if ($race->records->isEmpty())
                        <div class="card-body">
                            <p class="text-muted mb-0" role="status">Nessun record inserito per questa gara.</p>
                        </div>
                    @else
                        <div class="card-body p-0">
                            <div class="table-responsive" style="overflow-x: visible;">
                                <table class="table table-bordered table-striped table-hover align-middle table-dark-borders mb-0">
                                    <thead class="table-light">
                                        <tr>
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
                                        @foreach ($race->records as $record)
                                            @php
                                                $ratePerKm = $record->euroKM !== null ? (float) $record->euroKM : 0.36;
                                                $km = (float) ($record->km_documented ?? 0);
                                                $amount = $km > 0 ? round($km * $ratePerKm, 2) : 0.0;
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
                                                <td>{{ $record->type ?? '—' }}</td>
                                                <td>{{ number_format($ratePerKm, 2, ',', '.') }}</td>
                                                <td>{{ $record->daily_service }}</td>
                                                <td>{{ $record->special_service }}</td>
                                                <td>{{ $record->rate_documented }}</td>
                                                <td>{{ $record->km_documented }}</td>
                                                <td>{{ number_format($amount, 2, ',', '.') }}</td>
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
                                                <td colspan="15">
                                                    <div class="py-2">
                                                        <div class="mb-1">
                                                            <strong>Descrizione:</strong>
                                                            <span class="text-break">{{ $record->description ?: '—' }}</span>
                                                        </div>
                                                        <div>
                                                            <strong>Allegati:</strong>
                                                            @if ($record->attachments && $record->attachments->count())
                                                                <ul class="list-unstyled d-inline mb-0">
                                                                    @foreach ($record->attachments as $attachment)
                                                                        <li class="d-inline me-2">
                                                                            <a href="{{ route('attachments.show', $attachment) }}" target="_blank" rel="noopener">
                                                                                {{ $attachment->original_name }}
                                                                            </a>
                                                                        </li>
                                                                    @endforeach
                                                                </ul>
                                                            @else
                                                                <em>Nessuno</em>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div> {{-- /.table-responsive --}}
                        </div> {{-- /.card-body --}}
                    @endif
                </div>
            </section>
        @empty
            <p class="text-muted" role="status">
                Il cronometrista non è assegnato a nessuna gara.
            </p>
        @endforelse
    </main>
</x-layout>
