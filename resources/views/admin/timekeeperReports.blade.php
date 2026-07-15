<x-layout documentTitle="Report Record Cronometrista">
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

        $missedMealsFormattersByRace = [];

        $getMissedMealsDataFormatterForRace = function ($raceId) use (&$missedMealsFormattersByRace) {
            $raceId = (int) $raceId;

            if (!array_key_exists($raceId, $missedMealsFormattersByRace)) {
                $dscRaceForMeals = \App\Models\ReportRaceDsc::where('race_id', $raceId)->first();
                $detail = $dscRaceForMeals->missed_meals_detail ?? [];

                if (is_string($detail)) {
                    $decodedDetail = json_decode($detail, true);
                    $detail = json_last_error() === JSON_ERROR_NONE && is_array($decodedDetail) ? $decodedDetail : [];
                }

                if (!is_array($detail)) {
                    $detail = [];
                }

                $missedMealsFormattersByRace[$raceId] = function ($userId) use ($detail) {
                    $mealData = $detail[$userId] ?? ($detail[(string) $userId] ?? []);

                    if (is_string($mealData)) {
                        $decodedMealData = json_decode($mealData, true);
                        $mealData =
                            json_last_error() === JSON_ERROR_NONE && is_array($decodedMealData) ? $decodedMealData : [];
                    }

                    if (!is_array($mealData)) {
                        $mealData = [];
                    }

                    $pranzo = !empty($mealData['pranzo']) || !empty($mealData['lunch']);
                    $cena = !empty($mealData['cena']) || !empty($mealData['dinner']);
                    $count = ($pranzo ? 1 : 0) + ($cena ? 1 : 0);

                    if ($pranzo && $cena) {
                        $label = 'Pranzo + Cena';
                    } elseif ($pranzo) {
                        $label = 'Pranzo';
                    } elseif ($cena) {
                        $label = 'Cena';
                    } else {
                        $label = '—';
                    }

                    return [
                        'count' => $count,
                        'amount' => $count * 15,
                        'label' => $label,
                    ];
                };
            }

            return $missedMealsFormattersByRace[$raceId];
        };
    @endphp

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
                        @php
                            $getMissedMealsData = $getMissedMealsDataFormatterForRace($race->id);
                        @endphp

                        <div class="card-body p-0">
                            <div class="table-responsive" style="overflow-x: visible;">
                                <table
                                    class="table table-bordered table-striped table-hover align-middle table-dark-borders mb-0">
                                    <thead class="table-light">
                                        <tr>
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
                                            <th rowspan="2">Pasti mancati</th>
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

                                                // Totale riga SENZA diaria e diaria speciale
                                                $rowTotal =
                                                    $amount +
                                                    (float) ($record->travel_ticket_documented ?? 0) +
                                                    (float) ($record->food_documented ?? 0) +
                                                    (float) ($record->accommodation_documented ?? 0) +
                                                    (float) ($record->various_documented ?? 0) +
                                                    (float) ($record->food_not_documented ?? 0);

                                                // Apparecchiature / specializzazioni
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
                                                <td>{{ $record->type ?? '—' }}</td>
                                                <td>{{ $record->transport_mode === 'trasportato' ? 'Trasportato' : 'Km' }}
                                                </td>
                                                <td>{{ number_format($ratePerKm, 2, ',', '.') }}</td>
                                                <td>{{ $record->daily_service }}</td>
                                                <td>{{ $record->special_service }}</td>
                                                <td><strong>{{ number_format($dscWorkedHours, 2, ',', '.') }}</strong>
                                                </td>
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
                                                @php $mealInfo = $getMissedMealsData($record->user_id); @endphp
                                                <td>
                                                    {{ $mealInfo['count'] }}
                                                    <span class="text-muted">({{ $mealInfo['label'] }})</span>
                                                </td>
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
                                                                                target="_blank" rel="noopener">
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
