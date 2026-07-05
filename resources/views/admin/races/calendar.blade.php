<x-layout documentTitle="Calendario Gare">
    <main class="container pt-5 mt-5" id="main-content" aria-labelledby="calendar-title">
        <header class="page-header rounded-4 mb-4 px-4 py-4">
            <h1 id="calendar-title" class="h3 text-white mb-1">Calendario gare</h1>
            <p class="text-white-50 mb-0">
                Visualizza le gare del mese e naviga avanti o indietro nel calendario.
            </p>
        </header>

        <section class="card ficr-card border-0 shadow-sm rounded-4 mb-4" aria-label="Calendario mensile gare">
            <div class="card-header bg-white border-0 pt-4 px-4">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div>
                        <h2 class="h4 mb-1">
                            {{ ucwords($currentMonth->translatedFormat('F Y')) }}
                        </h2>
                        <p class="text-muted mb-0 small">
                            Ogni pallino indica una gara presente in quel giorno.
                        </p>
                    </div>

                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('admin.racesCalendar', ['month' => $previousMonth]) }}"
                            class="btn btn-outline-primary">
                            <i class="fa-solid fa-chevron-left me-1"></i> Mese precedente
                        </a>

                        <a href="{{ route('admin.racesCalendar') }}" class="btn btn-outline-secondary">
                            Mese corrente
                        </a>

                        <a href="{{ route('admin.racesCalendar', ['month' => $nextMonth]) }}"
                            class="btn btn-outline-primary">
                            Mese successivo <i class="fa-solid fa-chevron-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>

            <div class="card-body p-4">
                <div class="calendar-grid calendar-weekdays mb-2" aria-hidden="true">
                    <div>Lun</div>
                    <div>Mar</div>
                    <div>Mer</div>
                    <div>Gio</div>
                    <div>Ven</div>
                    <div>Sab</div>
                    <div>Dom</div>
                </div>

                <div class="calendar-grid" role="grid" aria-label="Calendario gare">
                    @foreach ($calendarDays as $day)
                        @php
                            $dayKey = $day->toDateString();
                            $dayRaces = $racesByDay[$dayKey] ?? collect();
                            $isCurrentMonth =
                                $day->month === $currentMonth->month && $day->year === $currentMonth->year;
                            $isToday = $day->isToday();
                        @endphp

                        <div class="calendar-day {{ $isCurrentMonth ? '' : 'calendar-day-muted' }} {{ $isToday ? 'calendar-day-today' : '' }}"
                            role="gridcell" aria-label="{{ $day->translatedFormat('l d F Y') }}">
                            <div class="d-flex justify-content-between align-items-start gap-2">
                                <span class="calendar-day-number">
                                    {{ $day->day }}
                                </span>

                                @if ($dayRaces->count() > 0)
                                    <span class="badge rounded-pill text-bg-primary">
                                        {{ $dayRaces->count() }}
                                    </span>
                                @endif
                            </div>

                            <div class="race-dots mt-3" aria-label="{{ $dayRaces->count() }} gare">
                                @foreach ($dayRaces as $race)
                                    <span class="race-dot" title="{{ $race->name }}"></span>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="card ficr-card border-0 shadow-sm rounded-4 mb-4" aria-labelledby="month-races-title">
            <div class="card-header bg-white border-0 pt-4 px-4">
                <h2 id="month-races-title" class="h5 mb-0">Gare del mese</h2>
            </div>

            <div class="card-body px-4 pb-4">
                @if ($monthRaces->isEmpty())
                    <p class="text-muted mb-0">Nessuna gara presente in questo mese.</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <caption class="visually-hidden">
                                Elenco delle gare presenti nel mese selezionato.
                            </caption>
                            <thead class="table-light">
                                <tr>
                                    <th>Periodo</th>
                                    <th>Gara</th>
                                    <th>Luogo</th>
                                    <th>Tipologia</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($monthRaces as $race)
                                    @php
                                        $raceStart = \Illuminate\Support\Carbon::parse($race->date_of_race);
                                        $raceEnd = $race->date_end
                                            ? \Illuminate\Support\Carbon::parse($race->date_end)
                                            : null;
                                    @endphp
                                    <tr>
                                        <td class="text-nowrap">
                                            {{ $raceStart->format('d/m/Y') }}
                                            @if ($raceEnd)
                                                <span class="mx-1">→</span>{{ $raceEnd->format('d/m/Y') }}
                                            @endif
                                        </td>
                                        <td class="fw-semibold">{{ $race->name }}</td>
                                        <td>{{ $race->place ?? '—' }}</td>
                                        <td>{{ $race->type ?? '—' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </section>

        <div class="d-flex justify-content-end">
            <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">
                Torna alla dashboard
            </a>
        </div>
    </main>

    <style>
        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, minmax(0, 1fr));
            gap: .75rem;
        }

        .calendar-weekdays>div {
            color: #6c757d;
            font-size: .875rem;
            font-weight: 700;
            text-align: center;
        }

        .calendar-day {
            min-height: 112px;
            border: 1px solid rgba(0, 0, 0, .08);
            border-radius: .75rem;
            background: #fff;
            padding: .75rem;
        }

        .calendar-day-muted {
            background: #f8f9fa;
            color: #6c757d;
        }

        .calendar-day-today {
            border-color: var(--bs-primary);
            box-shadow: inset 0 0 0 1px var(--bs-primary);
        }

        .calendar-day-number {
            font-weight: 700;
        }

        .race-dots {
            display: flex;
            flex-wrap: wrap;
            gap: .35rem;
            align-items: center;
        }

        .race-dot {
            width: .7rem;
            height: .7rem;
            border-radius: 999px;
            background: var(--bs-primary);
            display: inline-block;
        }

        @media (max-width: 767.98px) {
            .calendar-grid {
                gap: .35rem;
            }

            .calendar-day {
                min-height: 82px;
                padding: .45rem;
                border-radius: .5rem;
            }

            .calendar-weekdays>div {
                font-size: .75rem;
            }

            .race-dot {
                width: .55rem;
                height: .55rem;
            }
        }
    </style>
</x-layout>
