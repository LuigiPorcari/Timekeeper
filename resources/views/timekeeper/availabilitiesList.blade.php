<x-layout documentTitle="Timekeeper Availabilities List">
    <main class="container mt-5 pt-5" role="main">
        <h1 class="mb-4">Seleziona le tue disponibilità</h1>

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
                    $month = \Carbon\Carbon::parse($availability->date_of_availability)->translatedFormat('F Y');
                    $groupedAvailabilities[$month][] = $availability;
                }
            @endphp

            @foreach ($groupedAvailabilities as $month => $dates)
                <section class="card my-3 shadow-sm" aria-labelledby="heading-{{ Str::slug($month) }}">
                    <div class="card-header" id="heading-{{ Str::slug($month) }}">
                        <h2 class="h5 m-0"><strong>{{ ucfirst($month) }}</strong></h2>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            @foreach ($dates as $availability)
                                <div class="col-6 col-md-4 col-lg-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="dates[]"
                                            id="date-{{ $availability->id }}" value="{{ $availability->id }}"
                                            {{ in_array($availability->id, $selected ?? []) ? 'checked' : '' }}
                                            aria-labelledby="label-date-{{ $availability->id }}">
                                        <label class="form-check-label" id="label-date-{{ $availability->id }}"
                                            for="date-{{ $availability->id }}">
                                            {{ ucwords(\Carbon\Carbon::parse($availability->date_of_availability)->translatedFormat('l d')) }}
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </section>
            @endforeach

            <div class="mt-4 d-grid d-md-block">
                <button type="submit" class="btn btn-primary">Salva Disponibilità</button>
            </div>
        </form>
    </main>
</x-layout>
