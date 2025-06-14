<x-layout documentTitle="Admin Create Availability">
    <div class="container pt-5 mt-5">
        <h1 class="mb-4">Inserisci Disponibilità</h1>

        @if (session('success'))
            <div class="alert alert-dismissible alert-success">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card shadow-sm mb-5">
            <div class="card-body">
                <h5 class="card-title">Seleziona le disponibilità</h5>

                <form action="{{ route('availability.store') }}" method="POST">
                    @csrf

                    @foreach (range(1, 12) as $month)
                        @php
                            $monthName = \Carbon\Carbon::create()->month($month)->locale('it')->monthName;
                        @endphp

                        <div class="accordion mb-3" id="accordion-{{ $month }}">
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="heading-{{ $month }}">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                        data-bs-target="#collapse-{{ $month }}" aria-expanded="false"
                                        aria-controls="collapse-{{ $month }}">
                                        {{ ucfirst($monthName) }}
                                    </button>
                                </h2>
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
                                                            {{ $date->format('d/m/Y') }}
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
    </div>
</x-layout>
