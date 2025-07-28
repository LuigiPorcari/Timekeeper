<x-layout documentTitle="Seleziona Cronometristi per la Gara">
    <main class="container mt-5 pt-5" id="main-content" aria-labelledby="select-timekeepers-title">
        <h1 id="select-timekeepers-title" class="mb-4">
            Seleziona i cronometristi per la gara:
            {{ $race->name }} ({{ $race->date_of_race->format('d/m/Y') }})
        </h1>

        @if ($timekeepers->isEmpty())
            <p class="text-muted" role="status">
                Nessun cronometrista disponibile per questa gara.
            </p>
        @else
            <form action="{{ route('race.timekeepers.assign', $race) }}" method="POST"
                aria-describedby="form-description">
                @csrf
                <p id="form-description" class="visually-hidden">
                    Seleziona uno o più cronometristi da assegnare alla gara.
                </p>

                <fieldset>
                    <legend class="visually-hidden">Elenco dei cronometristi disponibili</legend>
                    <div class="row g-3">
                        @foreach ($timekeepers as $timekeeper)
                            <div class="col-12 col-md-6 col-lg-4">
                                <div class="form-check">
                                    <input class="form-check-input timekeeper-checkbox" type="checkbox"
                                        name="timekeepers[]" value="{{ $timekeeper->id }}"
                                        id="timekeeper-{{ $timekeeper->id }}"
                                        {{ $race->users->contains($timekeeper->id) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="timekeeper-{{ $timekeeper->id }}">
                                        {{ $timekeeper->name }} {{ $timekeeper->surname }}
                                    </label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </fieldset>

                <fieldset class="mt-4" id="leader-fieldset" style="display: none;">
                    <legend>Seleziona DMS</legend>
                    <select name="leader" class="form-select" id="leader-select">
                        <option value="">-- Seleziona DMS --</option>
                        @foreach ($timekeepers as $timekeeper)
                            @php
                                $isAssigned = $race->users->contains($timekeeper->id);
                                $isLeader = $race->pivotUsers->firstWhere('id', $timekeeper->id)?->pivot?->is_leader;
                            @endphp
                            <option value="{{ $timekeeper->id }}" {{ $isLeader ? 'selected' : '' }}>
                                {{ $timekeeper->name }} {{ $timekeeper->surname }}
                            </option>
                        @endforeach
                    </select>
                </fieldset>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary" aria-label="Salva cronometristi selezionati">
                        Salva
                    </button>
                </div>
            </form>
        @endif
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const checkboxes = document.querySelectorAll('.timekeeper-checkbox');
            const fieldset = document.getElementById('leader-fieldset');
            const select = document.getElementById('leader-select');

            function toggleLeaderField() {
                const anyChecked = Array.from(checkboxes).some(cb => cb.checked);
                fieldset.style.display = anyChecked ? 'block' : 'none';
            }

            checkboxes.forEach(cb => cb.addEventListener('change', toggleLeaderField));

            toggleLeaderField(); // esegui inizialmente
        });
    </script>
</x-layout>
