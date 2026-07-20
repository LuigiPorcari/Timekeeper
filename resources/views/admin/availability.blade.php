{{-- resources/views/admin/availability.blade.php --}}
<x-layout documentTitle="Admin Create Availability">
    <main class="container pt-5 mt-5" id="main-content">

        {{-- HEADER A GRADIENTE --}}
        <header class="page-header rounded-4 mb-4 px-4 py-4">
            <h1 class="h3 text-white mb-1">Inserisci Disponibilità</h1>
        </header>

        @if (session('success'))
            <div class="alert alert-dismissible alert-success shadow-sm" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Chiudi notifica"></button>
            </div>
        @endif

        <div class="card ficr-card shadow-sm mb-5 border-0 rounded-4" role="region" aria-labelledby="form-title">
            <div class="card-body p-4 p-md-5">

                <h2 class="card-title h5 mb-4" id="form-title">Seleziona le disponibilità</h2>

                <form action="{{ route('availability.store') }}" method="POST" aria-describedby="form-description"
                    novalidate>
                    @csrf
                    <p id="form-description" class="visually-hidden">
                        Seleziona i giorni in cui sei disponibile per ciascun mese e scegli un colore (verde, arancione
                        o rosso).
                    </p>

                    {{-- Legenda colori --}}
                    <div class="mb-3">
                        <div class="d-flex align-items-center gap-3 flex-wrap">
                            <span class="badge rounded-pill text-bg-success">Verde</span>
                            <span class="badge rounded-pill text-bg-warning">Arancione</span>
                            <span class="badge rounded-pill text-bg-danger">Rosso</span>
                        </div>
                    </div>

                    @php
                        $today = \Carbon\Carbon::now();
                        $currentYear = (int) $today->year;
                        $currentMonth = (int) $today->month;

                        // Mesi da mostrare subito: mese corrente -> dicembre
                        $activeMonths = range($currentMonth, 12);

                        // Archivio: gennaio -> mese precedente
                        $archiveMonths = $currentMonth > 1 ? range(1, $currentMonth - 1) : [];
                    @endphp

                    {{-- MESI DA COMPILARE: dal mese corrente a dicembre --}}
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                            <h3 class="h6 mb-0 text-uppercase text-muted">Mesi disponibili</h3>
                            <span class="badge bg-primary-subtle text-primary-emphasis">
                                Da {{ ucfirst($today->copy()->locale('it')->monthName) }} a dicembre
                            </span>
                        </div>

                        <div class="accordion ficr-accordion" id="accordion-active-months">
                            @foreach ($activeMonths as $month)
                                @php
                                    $monthDate = \Carbon\Carbon::create($currentYear, $month, 1)->locale('it');
                                    $monthName = $monthDate->monthName;
                                    $start = $monthDate->copy()->startOfMonth();
                                    $end = $monthDate->copy()->endOfMonth();
                                    $collapseId = "active-collapse-{$month}";
                                    $headingId = "active-heading-{$month}";
                                @endphp

                                <div class="accordion-item border-0 rounded-3 mb-3 overflow-hidden">
                                    <h3 class="accordion-header" id="{{ $headingId }}">
                                        <button class="accordion-button collapsed ficr-accordion-button" type="button"
                                            data-bs-toggle="collapse" data-bs-target="#{{ $collapseId }}"
                                            aria-expanded="false" aria-controls="{{ $collapseId }}">
                                            <span>{{ ucfirst($monthName) }}</span>
                                        </button>
                                    </h3>

                                    <div id="{{ $collapseId }}" class="accordion-collapse collapse"
                                        aria-labelledby="{{ $headingId }}" data-bs-parent="#accordion-active-months">
                                        <div class="accordion-body bg-light-subtle">
                                            {{-- Azioni mese --}}
                                            <div class="d-flex justify-content-end gap-2 flex-wrap mb-3">
                                                <button class="btn btn-sm btn-outline-primary px-3" type="button"
                                                    data-action="select-month" data-target="{{ $collapseId }}"
                                                    aria-label="Seleziona in verde tutti i giorni di {{ $monthName }}">
                                                    Seleziona tutto
                                                </button>
                                                <button class="btn btn-sm btn-outline-secondary px-3" type="button"
                                                    data-action="clear-month" data-target="{{ $collapseId }}"
                                                    aria-label="Deseleziona tutti i giorni di {{ $monthName }}">
                                                    Pulisci
                                                </button>
                                            </div>

                                            <div class="row g-2 g-md-3">
                                                @for ($date = $start->copy(); $date->lte($end); $date->addDay())
                                                    @php
                                                        $iso = $date->toDateString(); // YYYY-MM-DD
                                                        $id = 'date-' . $iso;
                                                        $savedColor = $selectedMap[$iso] ?? null; // 'verde'|'arancione'|'rosso'|null
                                                    @endphp
                                                    <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                                                        <div
                                                            class="ficr-day border rounded p-2 bg-white h-100 d-flex flex-column">
                                                            {{-- Intestazione giorno (solo giorno e numero) --}}
                                                            <div class="mb-2">
                                                                <span
                                                                    class="ficr-day-week d-block small text-uppercase">{{ $date->isoFormat('dd') }}</span>
                                                                <span
                                                                    class="ficr-day-num fw-semibold">{{ $date->format('d') }}</span>
                                                            </div>

                                                            {{-- Radio colori in colonna --}}
                                                            <div class="d-flex flex-column gap-1">
                                                                @foreach (['verde', 'arancione', 'rosso'] as $color)
                                                                    @php
                                                                        $rid = $id . '-' . $color;
                                                                        $checked = $savedColor === $color;
                                                                        $badgeClass =
                                                                            $color === 'verde'
                                                                                ? 'text-bg-success'
                                                                                : ($color === 'arancione'
                                                                                    ? 'text-bg-warning'
                                                                                    : 'text-bg-danger');
                                                                    @endphp
                                                                    <div
                                                                        class="form-check d-flex align-items-center gap-2">
                                                                        <input class="form-check-input" type="radio"
                                                                            name="color[{{ $iso }}]"
                                                                            id="{{ $rid }}"
                                                                            value="{{ $color }}"
                                                                            {{ $checked ? 'checked' : '' }}>
                                                                        <label class="form-check-label"
                                                                            for="{{ $rid }}">
                                                                            <span
                                                                                class="badge {{ $badgeClass }}">{{ ucfirst($color) }}</span>
                                                                        </label>
                                                                    </div>
                                                                @endforeach
                                                            </div>

                                                            {{-- Pulsante Pulisci centrato --}}
                                                            <div class="text-center mt-2">
                                                                <button type="button"
                                                                    class="btn btn-sm btn-outline-secondary"
                                                                    data-clear-day="{{ $id }}"
                                                                    aria-label="Pulisci selezione per il giorno {{ $date->format('d/m') }}">
                                                                    Pulisci
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endfor
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- ARCHIVIO: mesi precedenti chiusi di default --}}
                    @if (!empty($archiveMonths))
                        <div class="accordion ficr-accordion mt-4" id="accordion-archive-root">
                            <div class="accordion-item border-0 rounded-3 overflow-hidden">
                                <h3 class="accordion-header" id="archive-heading">
                                    <button class="accordion-button collapsed ficr-accordion-button" type="button"
                                        data-bs-toggle="collapse" data-bs-target="#archive-collapse"
                                        aria-expanded="false" aria-controls="archive-collapse">
                                        <span class="me-3">Archivio mesi precedenti</span>
                                        <span class="badge bg-light text-dark ms-auto">
                                            {{ count($archiveMonths) }}
                                            {{ count($archiveMonths) === 1 ? 'mese' : 'mesi' }}
                                        </span>
                                    </button>
                                </h3>

                                <div id="archive-collapse" class="accordion-collapse collapse"
                                    aria-labelledby="archive-heading" data-bs-parent="#accordion-archive-root">
                                    <div class="accordion-body bg-light-subtle">
                                        <p class="text-muted small mb-3">
                                            Qui trovi i mesi precedenti dell’anno corrente. La sezione resta chiusa di
                                            default per dare priorità al mese corrente e ai mesi successivi.
                                        </p>

                                        <div class="accordion ficr-accordion" id="accordion-archive-months">
                                            @foreach ($archiveMonths as $month)
                                                @php
                                                    $monthDate = \Carbon\Carbon::create(
                                                        $currentYear,
                                                        $month,
                                                        1,
                                                    )->locale('it');
                                                    $monthName = $monthDate->monthName;
                                                    $start = $monthDate->copy()->startOfMonth();
                                                    $end = $monthDate->copy()->endOfMonth();
                                                    $collapseId = "archive-collapse-{$month}";
                                                    $headingId = "archive-heading-{$month}";
                                                @endphp

                                                <div class="accordion-item border-0 rounded-3 mb-3 overflow-hidden">
                                                    <h3 class="accordion-header" id="{{ $headingId }}">
                                                        <button
                                                            class="accordion-button collapsed ficr-accordion-button"
                                                            type="button" data-bs-toggle="collapse"
                                                            data-bs-target="#{{ $collapseId }}"
                                                            aria-expanded="false"
                                                            aria-controls="{{ $collapseId }}">
                                                            <span>{{ ucfirst($monthName) }}</span>
                                                        </button>
                                                    </h3>

                                                    <div id="{{ $collapseId }}" class="accordion-collapse collapse"
                                                        aria-labelledby="{{ $headingId }}"
                                                        data-bs-parent="#accordion-archive-months">
                                                        <div class="accordion-body bg-light-subtle">
                                                            {{-- Azioni mese --}}
                                                            <div
                                                                class="d-flex justify-content-end gap-2 flex-wrap mb-3">
                                                                <button class="btn btn-sm btn-outline-primary px-3"
                                                                    type="button" data-action="select-month"
                                                                    data-target="{{ $collapseId }}"
                                                                    aria-label="Seleziona in verde tutti i giorni di {{ $monthName }}">
                                                                    Seleziona tutto
                                                                </button>
                                                                <button class="btn btn-sm btn-outline-secondary px-3"
                                                                    type="button" data-action="clear-month"
                                                                    data-target="{{ $collapseId }}"
                                                                    aria-label="Deseleziona tutti i giorni di {{ $monthName }}">
                                                                    Pulisci
                                                                </button>
                                                            </div>

                                                            <div class="row g-2 g-md-3">
                                                                @for ($date = $start->copy(); $date->lte($end); $date->addDay())
                                                                    @php
                                                                        $iso = $date->toDateString(); // YYYY-MM-DD
                                                                        $id = 'date-' . $iso;
                                                                        $savedColor = $selectedMap[$iso] ?? null; // 'verde'|'arancione'|'rosso'|null
                                                                    @endphp
                                                                    <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                                                                        <div
                                                                            class="ficr-day border rounded p-2 bg-white h-100 d-flex flex-column">
                                                                            {{-- Intestazione giorno (solo giorno e numero) --}}
                                                                            <div class="mb-2">
                                                                                <span
                                                                                    class="ficr-day-week d-block small text-uppercase">{{ $date->isoFormat('dd') }}</span>
                                                                                <span
                                                                                    class="ficr-day-num fw-semibold">{{ $date->format('d') }}</span>
                                                                            </div>

                                                                            {{-- Radio colori in colonna --}}
                                                                            <div class="d-flex flex-column gap-1">
                                                                                @foreach (['verde', 'arancione', 'rosso'] as $color)
                                                                                    @php
                                                                                        $rid = $id . '-' . $color;
                                                                                        $checked =
                                                                                            $savedColor === $color;
                                                                                        $badgeClass =
                                                                                            $color === 'verde'
                                                                                                ? 'text-bg-success'
                                                                                                : ($color ===
                                                                                                'arancione'
                                                                                                    ? 'text-bg-warning'
                                                                                                    : 'text-bg-danger');
                                                                                    @endphp
                                                                                    <div
                                                                                        class="form-check d-flex align-items-center gap-2">
                                                                                        <input class="form-check-input"
                                                                                            type="radio"
                                                                                            name="color[{{ $iso }}]"
                                                                                            id="{{ $rid }}"
                                                                                            value="{{ $color }}"
                                                                                            {{ $checked ? 'checked' : '' }}>
                                                                                        <label class="form-check-label"
                                                                                            for="{{ $rid }}">
                                                                                            <span
                                                                                                class="badge {{ $badgeClass }}">{{ ucfirst($color) }}</span>
                                                                                        </label>
                                                                                    </div>
                                                                                @endforeach
                                                                            </div>

                                                                            {{-- Pulsante Pulisci centrato --}}
                                                                            <div class="text-center mt-2">
                                                                                <button type="button"
                                                                                    class="btn btn-sm btn-outline-secondary"
                                                                                    data-clear-day="{{ $id }}"
                                                                                    aria-label="Pulisci selezione per il giorno {{ $date->format('d/m') }}">
                                                                                    Pulisci
                                                                                </button>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                @endfor
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-ficr btn-lg">Salva Disponibilità</button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    {{-- JS per Seleziona tutto / Pulisci per mese e pulizia singolo giorno --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Seleziona tutto: imposta "verde" per ogni giorno del mese
            document.querySelectorAll('[data-action="select-month"]').forEach(btn => {
                btn.addEventListener('click', () => {
                    const collapseId = btn.dataset.target;
                    const container = document.getElementById(collapseId);
                    if (!container) return;

                    const radios = container.querySelectorAll('input[type="radio"]');
                    const groups = {};
                    radios.forEach(r => {
                        groups[r.name] = groups[r.name] || [];
                        groups[r.name].push(r);
                    });

                    Object.values(groups).forEach(group => {
                        const verde = group.find(r => r.value === 'verde');
                        if (verde) verde.checked = true;
                    });
                });
            });

            // Pulisci mese: deseleziona tutti i radio nel mese
            document.querySelectorAll('[data-action="clear-month"]').forEach(btn => {
                btn.addEventListener('click', () => {
                    const collapseId = btn.dataset.target;
                    const container = document.getElementById(collapseId);
                    if (!container) return;
                    container.querySelectorAll('input[type="radio"]').forEach(r => r.checked =
                        false);
                });
            });

            // Pulisci singolo giorno
            document.querySelectorAll('[data-clear-day]').forEach(btn => {
                btn.addEventListener('click', () => {
                    const dayBox = btn.closest('.ficr-day');
                    if (!dayBox) return;
                    dayBox.querySelectorAll('input[type="radio"]').forEach(r => r.checked = false);
                });
            });
        });
    </script>
</x-layout>
