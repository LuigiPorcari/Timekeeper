<x-layout documentTitle="Admin Create Availability">
    <div class="pt-5">
        <h1 class="mt-4">Inserisci Disponibilità</h1>
    </div>

    @if (session('success'))
        <div class="alert alert-dismissible alert-success">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="container my-5">
        <h1>Seleziona le disponibilità</h1>
        <form action="{{ route('availability.store') }}" method="POST">
            @csrf

            @foreach (range(1, 12) as $month)
                @php
                    $monthName = \Carbon\Carbon::create()->month($month)->locale('it')->monthName;
                @endphp

                <div class="card my-3">
                    <div class="card-header">
                        <button class="btn btn-link text-decoration-none" type="button" data-bs-toggle="collapse"
                            data-bs-target="#collapse-{{ $month }}" aria-expanded="false"
                            aria-controls="collapse-{{ $month }}">
                            {{ ucfirst($monthName) }}
                        </button>
                    </div>
                    <div class="collapse" id="collapse-{{ $month }}">
                        <div class="card-body">
                            <div class="row">
                                @foreach (\Carbon\Carbon::now()->startOfYear()->startOfMonth()->month($month)->daysUntil(\Carbon\Carbon::now()->startOfYear()->startOfMonth()->month($month)->endOfMonth()) as $date)
                                    <div class="col-2">
                                        <input type="checkbox" name="dates[]" value="{{ $date->toDateString() }}"
                                            id="date-{{ $date->toDateString() }}"
                                            {{ in_array($date->toDateString(), $selectedDates ?? []) ? 'checked' : '' }}>
                                        <label
                                            for="date-{{ $date->toDateString() }}">{{ $date->format('d/m/Y') }}</label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach

            <button type="submit" class="btn btn-primary mt-3">Salva Disponibilità</button>
        </form>
    </div>
</x-layout>
