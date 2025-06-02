<x-layout documentTitle="Timekeeper Availabilities List">
    <div class="mt-5">
        <h1 class="mt-5 pt-5">Seleziona le tue disponibilità</h1>
    </div>

    @if (session('success'))
        <div class="alert alert-dismissible alert-success">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="container my-5">
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
                <div class="card my-3">
                    <div class="card-header">
                        <strong>{{ ucfirst($month) }}</strong>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach ($dates as $availability)
                                <div class="col-2">
                                    <input type="checkbox" name="dates[]" value="{{ $availability->id }}"
                                        id="date-{{ $availability->id }}"
                                        {{ in_array($availability->id, $selected ?? []) ? 'checked' : '' }}>
                                    <label for="date-{{ $availability->id }}">
                                        {{ \Carbon\Carbon::parse($availability->date_of_availability)->format('d/m/Y') }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach

            <button type="submit" class="btn btn-primary mt-3">Salva Disponibilità</button>
        </form>
    </div>
</x-layout>
