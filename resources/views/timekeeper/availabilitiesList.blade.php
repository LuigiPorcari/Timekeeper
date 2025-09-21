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

                <form action="{{ route('availability.storeUser') }}" method="POST"
                    aria-label="Selezione disponibilità mensile">
                    @csrf

                    @php
                        $groupedAvailabilities = [];
                        foreach ($availabilities as $availability) {
                            $month = \Carbon\Carbon::parse($availability->date_of_availability)->translatedFormat(
                                'F Y',
                            );
                            $groupedAvailabilities[$month][] = $availability;
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
                                    <button type="button" class="btn btn-sm btn-outline-primary month-select-all"
                                        data-month="{{ $monthSlug }}">
                                        Seleziona tutti
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary month-select-none"
                                        data-month="{{ $monthSlug }}">
                                        Nessuno
                                    </button>
                                </div>
                            </div>

                            <div class="card-body pt-3">
                                <div class="row g-2">
                                    @foreach ($dates as $availability)
                                        @php
                                            $id = 'date-' . $availability->id;
                                            $checked = in_array($availability->id, $selected ?? []);
                                            $label = ucwords(
                                                \Carbon\Carbon::parse(
                                                    $availability->date_of_availability,
                                                )->translatedFormat('l d'),
                                            );
                                        @endphp

                                        <div class="col-6 col-md-3 col-lg-2">
                                            {{-- Toggle button style checkbox --}}
                                            <input class="btn-check month-{{ $monthSlug }}" type="checkbox"
                                                name="dates[]" id="{{ $id }}" value="{{ $availability->id }}"
                                                {{ $checked ? 'checked' : '' }}>
                                            <label class="btn btn-outline-primary w-100" for="{{ $id }}">
                                                {{ $label }}
                                            </label>
                                        </div>
                                    @endforeach
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

    {{-- Seleziona tutti/nessuno per mese (solo UI) --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.month-select-all').forEach(btn => {
                btn.addEventListener('click', () => {
                    const month = btn.dataset.month;
                    document.querySelectorAll('.btn-check.month-' + month).forEach(cb => cb
                        .checked = true);
                });
            });
            document.querySelectorAll('.month-select-none').forEach(btn => {
                btn.addEventListener('click', () => {
                    const month = btn.dataset.month;
                    document.querySelectorAll('.btn-check.month-' + month).forEach(cb => cb
                        .checked = false);
                });
            });
        });
    </script>
</x-layout>
