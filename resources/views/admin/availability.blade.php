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
                    <p id="form-description" class="visually-hidden">
                        Seleziona i giorni in cui sei disponibile per ciascun mese.
                    </p>
                    @csrf

                    <div class="accordion ficr-accordion" id="accordion-root">
                        @foreach (range(1, 12) as $month)
                            @php
                                $monthName = \Carbon\Carbon::create()->month($month)->locale('it')->monthName;
                                $start = \Carbon\Carbon::now()->startOfYear()->month($month)->startOfMonth();
                                $end = (clone $start)->endOfMonth();
                                $collapseId = "collapse-{$month}";
                                $headingId = "heading-{$month}";
                            @endphp

                            <div class="accordion-item border-0 rounded-3 mb-3 overflow-hidden">
                                <h3 class="accordion-header" id="{{ $headingId }}">
                                    <button class="accordion-button collapsed ficr-accordion-button" type="button"
                                        data-bs-toggle="collapse" data-bs-target="#{{ $collapseId }}"
                                        aria-expanded="false" aria-controls="{{ $collapseId }}">
                                        <span class="me-3">{{ ucfirst($monthName) }}</span>

                                        {{-- azioni mese --}}
                                        <span class="ms-auto d-flex gap-2">
                                            <button class="btn btn-sm btn-outline-light px-3" type="button"
                                                data-action="select-month" data-target="{{ $collapseId }}"
                                                aria-label="Seleziona tutti i giorni di {{ $monthName }}">
                                                Seleziona tutto
                                            </button>
                                            <button class="btn btn-sm btn-outline-light px-3" type="button"
                                                data-action="clear-month" data-target="{{ $collapseId }}"
                                                aria-label="Deseleziona tutti i giorni di {{ $monthName }}">
                                                Pulisci
                                            </button>
                                        </span>
                                    </button>
                                </h3>

                                <div id="{{ $collapseId }}" class="accordion-collapse collapse"
                                    aria-labelledby="{{ $headingId }}" data-bs-parent="#accordion-root">
                                    <div class="accordion-body bg-light-subtle">
                                        <div class="row g-2 g-md-3">
                                            @foreach ($start->daysUntil($end->addDay()) as $date)
                                                @php
                                                    $id = 'date-' . $date->toDateString();
                                                    $checked = in_array($date->toDateString(), $selectedDates ?? []);
                                                @endphp
                                                <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                                                    <div class="ficr-day form-check">
                                                        <input class="form-check-input" type="checkbox" name="dates[]"
                                                            value="{{ $date->toDateString() }}"
                                                            id="{{ $id }}" {{ $checked ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="{{ $id }}">
                                                            <span
                                                                class="ficr-day-week d-block small text-uppercase">{{ $date->isoFormat('dd') }}</span>
                                                            <span
                                                                class="ficr-day-num fw-semibold">{{ $date->format('d') }}</span>
                                                        </label>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>

                            </div>
                        @endforeach
                    </div>

                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-ficr btn-lg">Salva Disponibilità</button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    {{-- JS per Seleziona tutto / Pulisci per mese --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const onClick = (selector, handler) => {
                document.querySelectorAll(selector).forEach(btn => {
                    btn.addEventListener('click', () => handler(btn.dataset.target));
                });
            };

            const setMonth = (collapseId, checked) => {
                const container = document.getElementById(collapseId);
                if (!container) return;
                container.querySelectorAll('input[type="checkbox"]').forEach(cb => cb.checked = checked);
            };

            onClick('[data-action="select-month"]', id => setMonth(id, true));
            onClick('[data-action="clear-month"]', id => setMonth(id, false));
        });
    </script>
</x-layout>
