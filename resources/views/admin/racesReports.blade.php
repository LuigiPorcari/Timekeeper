<x-layout documentTitle="Report Gara">
    @php
        $calculateHoursBetween = function ($startValue, $endValue) {
            if (!$startValue || !$endValue) {
                return 0.0;
            }

            try {
                $startValue = trim((string) $startValue);
                $endValue = trim((string) $endValue);

                if (!preg_match('/\d{2}:\d{2}(?::\d{2})?/', $startValue, $startMatch)) {
                    return 0.0;
                }

                if (!preg_match('/\d{2}:\d{2}(?::\d{2})?/', $endValue, $endMatch)) {
                    return 0.0;
                }

                $startValue = $startMatch[0];
                $endValue = $endMatch[0];

                $formatStart = substr_count($startValue, ':') === 2 ? 'H:i:s' : 'H:i';
                $formatEnd = substr_count($endValue, ':') === 2 ? 'H:i:s' : 'H:i';

                $start = \Carbon\Carbon::createFromFormat($formatStart, $startValue);
                $end = \Carbon\Carbon::createFromFormat($formatEnd, $endValue);

                if ($end->lessThanOrEqualTo($start)) {
                    return 0.0;
                }

                return round($start->diffInMinutes($end) / 60, 2);
            } catch (\Throwable $e) {
                return 0.0;
            }
        };

        $calculateDscWorkedHours = function ($dscDay) use ($calculateHoursBetween) {
            if (!$dscDay) {
                return 0.0;
            }

            return $calculateHoursBetween($dscDay->morning_start ?? null, $dscDay->morning_end ?? null) +
                $calculateHoursBetween($dscDay->afternoon_start ?? null, $dscDay->afternoon_end ?? null);
        };

        $formatDscTime = function ($value) {
            return $value ? substr((string) $value, 0, 5) : '—';
        };

        $formatDscDayDetail = function ($dscDay) use ($formatDscTime) {
            if (!$dscDay) {
                return null;
            }

            $day = $dscDay->work_date ? \Carbon\Carbon::parse($dscDay->work_date)->format('d/m/Y') : 'Giorno';

            return $day .
                ': ' .
                $formatDscTime($dscDay->morning_start ?? null) .
                '-' .
                $formatDscTime($dscDay->morning_end ?? null) .
                ' / ' .
                $formatDscTime($dscDay->afternoon_start ?? null) .
                '-' .
                $formatDscTime($dscDay->afternoon_end ?? null);
        };
    @endphp

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
                                        Dettaglio rendicontazioni per operatore, con tipologia, trasporto, tariffa
                                        chilometrica e spese.
                                    </caption>
                                    <thead class="table-light">
                                        <tr>
                                            <th rowspan="2">Operatore</th>
                                            <th rowspan="2">Tipo</th>
                                            <th rowspan="2">Trasporto</th>
                                            <th rowspan="2">€/Km</th>
                                            <th rowspan="2">Servizio Giornaliero</th>
                                            <th rowspan="2">Servizio Speciale</th>
                                            <th rowspan="2">Ore da orari DSC</th>
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

                                                // Importo km
                                                $amount = $kmDisplay
                                                    ? round(((float) $kmDisplay) * $ratePerKm, 2)
                                                    : 0.0;

                                                // Totale di riga (⚠️ SENZA diaria e diaria speciale)
                                                $rowTotal =
                                                    $amount +
                                                    (float) ($record->travel_ticket_documented ?? 0) +
                                                    (float) ($record->food_documented ?? 0) +
                                                    (float) ($record->accommodation_documented ?? 0) +
                                                    (float) ($record->various_documented ?? 0) +
                                                    (float) ($record->food_not_documented ?? 0);

                                                // Apparecchiature (namespacizzate tipo__equip → mostro solo equip human)
                                                $appsRaw = is_array($record->apparecchiature ?? null)
                                                    ? $record->apparecchiature
                                                    : [];
                                                $prettySpec = function ($val) {
                                                    if (!is_string($val)) {
                                                        return '';
                                                    }
                                                    if (str_contains($val, '__')) {
                                                        [, $val] = explode('__', $val, 2);
                                                    }
                                                    $val = str_replace(['_', '-'], ' ', $val);
                                                    return ucwords($val);
                                                };
                                                $apps = array_filter(array_map($prettySpec, $appsRaw));
                                                $appsLabel = $apps ? implode(', ', $apps) : '—';

                                                $dscRows = \App\Models\ReportDayDsc::where('race_id', $race->id)
                                                    ->where('user_id', $record->user_id)
                                                    ->orderBy('work_date')
                                                    ->get();

                                                $dscWorkedHours = $dscRows->sum(function ($dscDay) use (
                                                    $calculateDscWorkedHours,
                                                ) {
                                                    return $calculateDscWorkedHours($dscDay);
                                                });

                                                $dscHoursDetails = $dscRows
                                                    ->map(function ($dscDay) use ($formatDscDayDetail) {
                                                        return $formatDscDayDetail($dscDay);
                                                    })
                                                    ->filter()
                                                    ->values();
                                            @endphp

                                            {{-- Riga principale dati --}}
                                            <tr>
                                                <td>{{ $record->user->name }} {{ $record->user->surname }}</td>
                                                <td>{{ $record->type ?? '—' }}</td>
                                                <td>{{ $record->transport_mode === 'trasportato' ? 'Trasportato' : 'Km' }}
                                                </td>
                                                <td>{{ number_format($ratePerKm, 2, ',', '.') }}</td>
                                                <td>{{ $record->daily_service }}</td>
                                                <td>{{ $record->special_service }}</td>
                                                <td><strong>{{ number_format($dscWorkedHours, 2, ',', '.') }}</strong>
                                                </td>
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

                                            {{-- Riga secondaria: Apparecchiature + Descrizione + Allegati --}}
                                            <tr class="bg-light">
                                                <td colspan="18">
                                                    <div class="py-2">
                                                        <div class="mb-1">
                                                            <strong>Orari DSC:</strong>
                                                            @if ($dscHoursDetails->isNotEmpty())
                                                                @foreach ($dscHoursDetails as $detail)
                                                                    <span
                                                                        class="badge bg-secondary-subtle text-secondary-emphasis border me-1 mb-1">
                                                                        {{ $detail }}
                                                                    </span>
                                                                @endforeach
                                                            @else
                                                                <span class="text-muted">—</span>
                                                            @endif
                                                        </div>
                                                        <div class="mb-1">
                                                            <strong>Apparecchiature:</strong>
                                                            <span class="text-break">{{ $appsLabel }}</span>
                                                        </div>
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
                                            <td colspan="17" class="text-end">Totale Generale</td>
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
