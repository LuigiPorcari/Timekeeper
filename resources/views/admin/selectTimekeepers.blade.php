<x-layout documentTitle="Seleziona Cronometristi per la Gara">
    <main class="container mt-5 pt-5" id="main-content" aria-labelledby="select-timekeepers-title">
        <div class="card shadow-sm rounded-3">
            <div class="card-header bg-white">
                <h1 id="select-timekeepers-title" class="h4 mb-0">
                    Seleziona i cronometristi — {{ $race->name }}
                    <small class="text-muted d-block mt-1">
                        {{ $race->date_of_race->format('d/m/Y') }}
                        @if ($race->date_end)
                            <span class="mx-1">→</span>{{ \Carbon\Carbon::parse($race->date_end)->format('d/m/Y') }}
                        @endif
                        @if (!empty($race->place))
                            · {{ $race->place }}
                        @endif
                    </small>
                </h1>
            </div>

            <div class="card-body">
                @if ($timekeepers->isEmpty())
                    <p class="text-muted mb-0" role="status">
                        Nessun cronometrista disponibile per questa gara.
                    </p>
                @else
                    <form action="{{ route('race.timekeepers.assign', $race) }}" method="POST"
                        aria-describedby="form-description">
                        @csrf
                        <p id="form-description" class="visually-hidden">
                            Seleziona uno o più cronometristi da assegnare alla gara e indica il DSC.
                        </p>

                        <fieldset>
                            <legend class="visually-hidden">Elenco dei cronometristi disponibili</legend>

                            {{-- Griglia responsive di "card selezionabili" --}}
                            <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-3 g-3">
                                @foreach ($timekeepers as $timekeeper)
                                    <div class="col">
                                        <div class="card h-100 tk-card hover-lift">
                                            <label class="p-3 d-flex align-items-start gap-2 cursor-pointer"
                                                for="timekeeper-{{ $timekeeper->id }}">
                                                <input class="form-check-input mt-1 timekeeper-checkbox" type="checkbox"
                                                    name="timekeepers[]" value="{{ $timekeeper->id }}"
                                                    id="timekeeper-{{ $timekeeper->id }}"
                                                    {{ $race->users->contains($timekeeper->id) ? 'checked' : '' }}>
                                                <div>
                                                    <div class="fw-semibold">
                                                        {{ $timekeeper->name }} {{ $timekeeper->surname }}
                                                    </div>

                                                    {{-- Badge specializzazioni (se presenti) --}}
                                                    @php
                                                        $specs = $timekeeper->specialization ?? [];
                                                        $pretty = function ($val) {
                                                            // se è "tipo__equip" mostro solo l'equip
    if (is_string($val) && str_contains($val, '__')) {
        [$t, $e] = explode('__', $val, 2);
        return ucwords(str_replace(['_', '-'], ' ', $e));
    }
    return ucwords(str_replace(['_', '-'], ' ', $val));
                                                        };
                                                    @endphp

                                                    @if (!empty($specs))
                                                        <div class="mt-2 d-flex flex-wrap gap-2">
                                                            @foreach ($specs as $s)
                                                                <span
                                                                    class="badge bg-secondary-subtle text-secondary-emphasis border">
                                                                    {{ $pretty($s) }}
                                                                </span>
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </fieldset>

                        {{-- Selezione DSC (compare solo se c’è almeno un selezionato) --}}
                        <fieldset class="mt-4" id="leader-fieldset" style="display:none;">
                            <legend class="h6 mb-2">Seleziona DSC</legend>
                            <select name="leader" class="form-select" id="leader-select" aria-label="Seleziona il DSC">
                                <option value="">-- Seleziona DSC --</option>
                                @foreach ($timekeepers as $timekeeper)
                                    @php
                                        $isLeader = $race->pivotUsers->firstWhere('id', $timekeeper->id)?->pivot
                                            ?->is_leader;
                                    @endphp
                                    <option value="{{ $timekeeper->id }}" {{ $isLeader ? 'selected' : '' }}>
                                        {{ $timekeeper->name }} {{ $timekeeper->surname }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted d-block mt-1">Il DSC potrà confermare i record della gara.</small>
                        </fieldset>

                        <div class="mt-4 d-flex gap-2">
                            <button type="submit" class="btn btn-primary" aria-label="Salva cronometristi selezionati">
                                Salva
                            </button>
                            <a href="{{ route('admin.racesList') }}" class="btn btn-outline-secondary">Annulla</a>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const checkboxes = document.querySelectorAll('.timekeeper-checkbox');
            const fieldset = document.getElementById('leader-fieldset');

            function toggleLeaderField() {
                const anyChecked = Array.from(checkboxes).some(cb => cb.checked);
                fieldset.style.display = anyChecked ? 'block' : 'none';
            }

            checkboxes.forEach(cb => cb.addEventListener('change', toggleLeaderField));
            toggleLeaderField();
        });
    </script>
</x-layout>
