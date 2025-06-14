<x-layout documentTitle="Timekeeper Availabilities List">
    <div class="container mt-5 pt-5">
        <h1 class="mb-4">Seleziona le tue disponibilità</h1>

        @if (session('success'))
            <div class="alert alert-dismissible alert-success">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <form action="{{ route('availability.storeUser') }}" method="POST">
            @csrf

            @php
                $groupedAvailabilities = [];
                foreach ($availabilities as $availability) {
                    $month = \Carbon\Carbon::parse($availability->date_of_availability)->translatedFormat('F Y');
                    $groupedAvailabilities[$month][] = $availability;
                }
            @endphp

            @foreach ($groupedAvailabilities as $month => $dates)
                <div class="card my-3 shadow-sm">
                    <div class="card-header">
                        <strong>{{ ucfirst($month) }}</strong>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            @foreach ($dates as $availability)
                                <div class="col-6 col-md-4 col-lg-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="dates[]"
                                            value="{{ $availability->id }}" id="date-{{ $availability->id }}"
                                            {{ in_array($availability->id, $selected ?? []) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="date-{{ $availability->id }}">
                                            {{ ucwords(\Carbon\Carbon::parse($availability->date_of_availability)->translatedFormat('l d')) }}
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach

            <div class="mt-4 d-grid d-md-block">
                <button type="submit" class="btn btn-primary">Salva Disponibilità</button>
            </div>
        </form>
    </div>
</x-layout>
