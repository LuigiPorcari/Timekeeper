{{-- resources/views/timekeeper/availabilitiesList.blade.php --}}
<x-layout documentTitle="Timekeeper Availabilities List">
    <main class="container mt-5 pt-5" role="main" aria-labelledby="avail-title">
        <div class="row justify-content-center">
            <div class="col-12 col-xl-10">

                <h1 id="avail-title" class="mb-4">Seleziona le tue disponibilità</h1>

                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Chiudi"></button>
                    </div>
                @endif

                {{-- Legenda colori --}}
                <div class="mb-3">
                    <div class="d-flex align-items-center gap-3 flex-wrap">
                        <span class="badge rounded-pill text-bg-success">Verde</span>
                        <span class="badge rounded-pill text-bg-warning">Arancione</span>
                        <span class="badge rounded-pill text-bg-danger">Rosso</span>
                        <small class="text-muted ms-1">Il significato dei colori è definito dall’organizzazione.</small>
                    </div>
                </div>

                <form action="{{ route('availability.storeUser') }}" method="POST"
                    aria-label="Selezione disponibilità mensile">
                    @csrf

                    @php
                        // Raggruppa disponibilità per mese (es. "Febbraio 2025")
                        $groupedAvailabilities = [];
                        foreach ($availabilities as $availability) {
                            $monthLabel = \Carbon\Carbon::parse($availability->date_of_availability)->translatedFormat(
                                'F Y',
                            );
                            $groupedAvailabilities[$monthLabel][] = $availability;
                        }
                    @endphp

                    @foreach ($groupedAvailabilities as $month => $dates)
                        @php
                            $monthSlug = Str::slug($month);
                        @endphp

                        <section class="card tk-card my-3 shadow-sm" aria-labelledby="heading-{{ $monthSlug }}">
                            <div class="card-header tk-card-header d-flex flex-wrap align-items-center justify-content-between gap-2"
                                id="heading-{{ $monthSlug }}">
                                <h2 class="h5 m-0">
                                    <i class="fas fa-calendar-alt me-2"></i>
                                    <strong>{{ ucfirst($month) }}</strong>
                                </h2>
                                <div class="d-flex gap-2">
                                    {{-- <button type="button" class="btn btn-sm btn-outline-primary month-select-all"
                                        data-month="{{ $monthSlug }}">
                                        Seleziona tutti
                                    </button> --}}
                                    <button type="button" class="btn btn-sm btn-outline-secondary month-select-none"
                                        data-month="{{ $monthSlug }}">
                                        Nessuno
                                    </button>
                                </div>
                            </div>

                            <div class="card-body pt-3">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width: 18%">Giorno</th>
                                                <th style="width: 10%">Colore</th>
                                                <th class="text-center" style="width: 18%">Mattina</th>
                                                <th class="text-center" style="width: 18%">Pomeriggio</th>
                                                <th class="text-center" style="width: 18%">Trasferta</th>
                                                <th class="text-center" style="width: 18%">Reperibilità</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($dates as $availability)
                                                @php
                                                    $aid = $availability->id;
                                                    $dayLabel = ucwords(
                                                        \Carbon\Carbon::parse(
                                                            $availability->date_of_availability,
                                                        )->translatedFormat('l d'),
                                                    );
                                                    $color = $availability->color ?? null; // 'verde'|'arancione'|'rosso'|null
                                                    $badgeClass = match ($color) {
                                                        'verde' => 'text-bg-success',
                                                        'arancione' => 'text-bg-warning',
                                                        'rosso' => 'text-bg-danger',
                                                        default => 'text-bg-secondary',
                                                    };
                                                    $sel = $userSelections[$aid] ?? [];
                                                    $isMorning = !empty($sel['morning']);
                                                    $isAfternoon = !empty($sel['afternoon']);
                                                    $isTrasferta = !empty($sel['trasferta']);
                                                    $isReper = !empty($sel['reperibilita']);
                                                @endphp
                                                <tr class="month-{{ $monthSlug }}">
                                                    <td class="fw-medium">{{ $dayLabel }}</td>
                                                    <td>
                                                        <span class="badge {{ $badgeClass }}">
                                                            {{ $color ? ucfirst($color) : '—' }}
                                                        </span>
                                                    </td>

                                                    {{-- Mattina --}}
                                                    <td class="text-center">
                                                        <div class="form-check d-inline-block">
                                                            <input class="form-check-input" type="checkbox"
                                                                id="morning-{{ $aid }}"
                                                                name="availability[{{ $aid }}][morning]"
                                                                value="1" {{ $isMorning ? 'checked' : '' }}>
                                                            <label class="form-check-label"
                                                                for="morning-{{ $aid }}"></label>
                                                        </div>
                                                    </td>

                                                    {{-- Pomeriggio --}}
                                                    <td class="text-center">
                                                        <div class="form-check d-inline-block">
                                                            <input class="form-check-input" type="checkbox"
                                                                id="afternoon-{{ $aid }}"
                                                                name="availability[{{ $aid }}][afternoon]"
                                                                value="1" {{ $isAfternoon ? 'checked' : '' }}>
                                                            <label class="form-check-label"
                                                                for="afternoon-{{ $aid }}"></label>
                                                        </div>
                                                    </td>

                                                    {{-- Trasferta --}}
                                                    <td class="text-center">
                                                        <div class="form-check d-inline-block">
                                                            <input class="form-check-input" type="checkbox"
                                                                id="trasferta-{{ $aid }}"
                                                                name="availability[{{ $aid }}][trasferta]"
                                                                value="1" {{ $isTrasferta ? 'checked' : '' }}>
                                                            <label class="form-check-label"
                                                                for="trasferta-{{ $aid }}"></label>
                                                        </div>
                                                    </td>

                                                    {{-- Reperibilità --}}
                                                    <td class="text-center">
                                                        <div class="form-check d-inline-block">
                                                            <input class="form-check-input" type="checkbox"
                                                                id="reper-{{ $aid }}"
                                                                name="availability[{{ $aid }}][reperibilita]"
                                                                value="1" {{ $isReper ? 'checked' : '' }}>
                                                            <label class="form-check-label"
                                                                for="reper-{{ $aid }}"></label>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </section>
                    @endforeach

                    <div class="mt-4 d-grid d-md-block">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Salva Disponibilità
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </main>

    {{-- Seleziona tutti/nessuno per mese --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.month-select-all').forEach(btn => {
                btn.addEventListener('click', () => {
                    const month = btn.dataset.month;
                    document.querySelectorAll('.month-' + month + ' .form-check-input').forEach(
                        cb => cb.checked = true);
                });
            });
            document.querySelectorAll('.month-select-none').forEach(btn => {
                btn.addEventListener('click', () => {
                    const month = btn.dataset.month;
                    document.querySelectorAll('.month-' + month + ' .form-check-input').forEach(
                        cb => cb.checked = false);
                });
            });
        });
    </script>
</x-layout>
