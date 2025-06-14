<x-layout documentTitle="Seleziona Cronometristi per la Gara">
    <div class="container mt-5 pt-5">
        <h1 class="mb-4">Seleziona i cronometristi per la gara: {{ $race->place }}
            ({{ $race->date_of_race->format('d/m/Y') }})</h1>

        @if ($timekeepers->isEmpty())
            <p class="text-muted">Nessun cronometrista disponibile per questa gara.</p>
        @else
            <form action="{{ route('race.timekeepers.assign', $race) }}" method="POST">
                @csrf
                <div class="row g-3">
                    @foreach ($timekeepers as $timekeeper)
                        <div class="col-12 col-md-6 col-lg-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="timekeepers[]"
                                    value="{{ $timekeeper->id }}" id="timekeeper-{{ $timekeeper->id }}"
                                    {{ $race->users->contains($timekeeper->id) ? 'checked' : '' }}>
                                <label class="form-check-label" for="timekeeper-{{ $timekeeper->id }}">
                                    {{ $timekeeper->name }} {{ $timekeeper->surname }}
                                </label>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">Salva</button>
                </div>
            </form>
        @endif
    </div>
</x-layout>
