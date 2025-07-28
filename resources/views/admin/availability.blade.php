<x-layout documentTitle="Admin Create Availability">
    <main class="container pt-5 mt-5" id="main-content">
        <h1 class="mb-4">Inserisci Disponibilità</h1>

        @if (session('success'))
            <div class="alert alert-dismissible alert-success" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Chiudi notifica"></button>
            </div>
        @endif

        <div class="card shadow-sm mb-5" role="region" aria-labelledby="form-title">
            <div class="card-body">
                <h2 class="card-title h5" id="form-title">Seleziona le disponibilità</h2>

                <form action="{{ route('availability.store') }}" method="POST" aria-describedby="form-description">
                    <p id="form-description" class="visually-hidden">
                        Seleziona i giorni in cui sei disponibile per ciascun mese.
                    </p>
                    @csrf

                    @foreach (range(1, 12) as $month)
                        @php
                            $monthName = \Carbon\Carbon::create()->month($month)->locale('it')->monthName;
                        @endphp

                        <div class="accordion mb-3" id="accordion-{{ $month }}">
                            <div class="accordion-item">
                                <h3 class="accordion-header" id="heading-{{ $month }}">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#collapse-{{ $month }}" aria-expanded="false"
                                        aria-controls="collapse-{{ $month }}">
                                        {{ ucfirst($monthName) }}
                                    </button>
                                </h3>
                                <div id="collapse-{{ $month }}" class="accordion-collapse collapse"
                                    aria-labelledby="heading-{{ $month }}"
                                    data-bs-parent="#accordion-{{ $month }}">
                                    <div class="accordion-body">
                                        <div class="row g-3">
                                            @foreach (\Carbon\Carbon::now()->startOfYear()->startOfMonth()->month($month)->daysUntil(\Carbon\Carbon::now()->startOfYear()->startOfMonth()->month($month)->endOfMonth()) as $date)
                                                <div class="col-6 col-md-4 col-lg-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="dates[]"
                                                            value="{{ $date->toDateString() }}"
                                                            id="date-{{ $date->toDateString() }}"
                                                            {{ in_array($date->toDateString(), $selectedDates ?? []) ? 'checked' : '' }}>
                                                        <label class="form-check-label"
                                                            for="date-{{ $date->toDateString() }}">
                                                            {{ ucwords($date->translatedFormat('l d')) }}
                                                        </label>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">Salva Disponibilità</button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</x-layout>
