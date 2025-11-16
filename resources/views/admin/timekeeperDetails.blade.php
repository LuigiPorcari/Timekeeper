<x-layout documentTitle="Admin Timekeeper Details">
    <main class="container mt-5 pt-5" id="main-content" aria-labelledby="timekeeper-details-title">
        <h1 id="timekeeper-details-title" class="mb-4">Dettagli {{ $timekeeper->name }} {{ $timekeeper->surname }}</h1>

        @if (session('success'))
            <div class="alert alert-dismissible alert-success" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Chiudi notifica"></button>
            </div>
        @endif

        <div class="row g-4">
            {{-- COLONNA PROFILO --}}
            <div class="col-md-4">
                <section aria-labelledby="dati-personali">
                    <div class="card shadow-sm rounded-3 overflow-hidden">
                        <div class="card-header page-header d-flex align-items-center">
                            <i class="fa-solid fa-user-clock me-2"></i>
                            <div>
                                <h2 id="dati-personali" class="h5 mb-0 text-white">
                                    {{ $timekeeper->name }} {{ $timekeeper->surname }}
                                </h2>
                                <small class="text-white-50">Cronometrista</small>
                            </div>
                        </div>

                        <div class="card-body">
                            <dl class="row mb-0">
                                <dt class="col-5 text-muted small">Email</dt>
                                <dd class="col-7">{{ $timekeeper->email }}</dd>

                                <dt class="col-5 text-muted small">Data di nascita</dt>
                                <dd class="col-7">
                                    {{ \Carbon\Carbon::parse($timekeeper->date_of_birth)->format('d-m-Y') }}
                                </dd>

                                @if ($timekeeper->residence)
                                    <dt class="col-5 text-muted small">Residenza</dt>
                                    <dd class="col-7">{{ $timekeeper->residence }}</dd>
                                @endif

                                <dt class="col-5 text-muted small">Domicilio</dt>
                                <dd class="col-7">{{ $timekeeper->domicile }}</dd>

                                <dt class="col-5 text-muted small">Disponibilità trasferta</dt>
                                <dd class="col-7">{{ $timekeeper->transfer ? 'SI' : 'NO' }}</dd>

                                <dt class="col-5 text-muted small">Automunito</dt>
                                <dd class="col-7">{{ $timekeeper->auto ? 'SI' : 'NO' }}</dd>
                            </dl>

                            <hr class="my-3">

                            {{-- Specializzazioni già assegnate (raggruppate per tipo) --}}
                            <div class="mb-3">
                                <div class="fw-semibold mb-2">Specializzazioni attuali</div>

                                @php
                                    $current = $timekeeper->specialization ?? [];
                                    $typesMap = $typesMap ?? []; // passato dal controller
                                    $slug = fn(string $t) => \Illuminate\Support\Str::slug($t, '_');

                                    // mappa: slugTipo => labelTipo
                                    $typeSlugToLabel = [];
                                    foreach ($typesMap as $typeLabel => $equipList) {
                                        $typeSlugToLabel[$slug($typeLabel)] = $typeLabel;
                                    }

                                    // helper per trovare il label umano dell'attrezzatura partendo dallo slug
$equipHuman = function (string $typeLabel, string $equipSlug) use (
    $typesMap,
    $slug,
) {
    $list = $typesMap[$typeLabel] ?? [];
    foreach ($list as $human) {
        if ($slug($human) === $equipSlug) {
            return $human; // esattamente quello del config
        }
    }
    // fallback decoroso
    return ucwords(str_replace(['_', '-'], ' ', $equipSlug));
};

// Raggruppo:
// - Generali: contiene solo 'co'
// - Per tipo: ogni chiave è "labelTipo", valori = array di label attrezzature
$groups = [];
$generali = [];

foreach ($current as $val) {
    if ($val === 'co') {
        $generali[] = 'Co';
        continue;
    }

    if (is_string($val) && str_contains($val, '__')) {
        [$typeSlug, $equipSlug] = explode('__', $val, 2);
        $typeLabel =
            $typeSlugToLabel[$typeSlug] ??
            ucwords(str_replace('_', ' ', $typeSlug));
        $equipLabel = $equipHuman($typeLabel, $equipSlug);

        if (!isset($groups[$typeLabel])) {
            $groups[$typeLabel] = [];
        }
        $groups[$typeLabel][] = $equipLabel;
    } else {
        // Valore non namespacizzato: lo metto in "Altre"
        if (!isset($groups['Altre'])) {
            $groups['Altre'] = [];
        }
        $groups['Altre'][] = ucwords(str_replace(['_', '-'], ' ', (string) $val));
                                        }
                                    }
                                @endphp

                                @if (empty($current))
                                    <span class="text-muted">Cronometrista generico</span>
                                @else
                                    <div class="d-flex flex-column gap-2">
                                        {{-- Generali --}}
                                        @if (!empty($generali))
                                            <div>
                                                <div class="small text-muted mb-1">Generali</div>
                                                <div class="d-flex flex-wrap gap-2">
                                                    @foreach ($generali as $g)
                                                        <span
                                                            class="badge badge-soft border">{{ $g }}</span>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endif

                                        {{-- Per tipo di gara (ordinate per nome tipo) --}}
                                        @foreach (collect($groups)->sortKeys() as $typeLabel => $equipList)
                                            <div>
                                                <div class="small text-muted mb-1">{{ $typeLabel }}</div>
                                                <div class="d-flex flex-wrap gap-2">
                                                    @foreach (array_unique($equipList) as $equipLabel)
                                                        <span
                                                            class="badge badge-soft border">{{ $equipLabel }}</span>
                                                    @endforeach
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            {{-- Disponibilità --}}
                            <div>
                                <div class="fw-semibold mb-2">Disponibilità</div>
                                <div class="avail-box small">
                                    @forelse ($timekeeper->availabilities as $a)
                                        <div>
                                            {{ ucwords(\Carbon\Carbon::parse($a->date_of_availability)->translatedFormat('l d F')) }}
                                        </div>
                                    @empty
                                        <span class="text-muted">Nessuna Disponibilità</span>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <a class="btn btn-outline-secondary mt-3 w-100" href="{{ route('admin.timekeeperList') }}"
                    aria-label="Torna alla lista dei cronometristi">
                    Indietro
                </a>
            </div>

            {{-- COLONNA SELEZIONE SPECIALIZZAZIONI --}}
            <div class="col-md-8">
                <section aria-labelledby="modifica-specializzazione">
                    <div class="card shadow-sm rounded-3 overflow-hidden">
                        <div class="card-header page-header d-flex align-items-center">
                            <i class="fa-solid fa-layer-group me-2"></i>
                            <div>
                                <h2 id="modifica-specializzazione" class="h5 mb-1 text-white">Seleziona disciplina</h2>
                                <small class="text-white-50">Spunta tutte le specializzazioni pertinenti.</small>
                            </div>
                        </div>

                        <div class="card-body">
                            <form action="{{ route('update.timekeeper', $timekeeper) }}" method="POST"
                                aria-describedby="form-specializzazione-descrizione">
                                <p id="form-specializzazione-descrizione" class="visually-hidden">
                                    Seleziona o modifica le specializzazioni per il cronometrista.
                                </p>
                                @csrf

                                @php
                                    $typesMap = $typesMap ?? [];
                                    $slug = fn(string $text) => \Illuminate\Support\Str::slug($text, '_');
                                    $selected = $timekeeper->specialization ?? [];

                                    // Costruisco le sezioni: "Generali" (co) + tutti i tipi (namespaced)
                                    $sections = [];
                                    $sections['Generali'] = ['co'];

                                    foreach ($typesMap as $typeLabel => $equipList) {
                                        $typeSlug = $slug($typeLabel);
                                        $rows = [];
                                        foreach ($equipList as $lab) {
                                            if (!filled($lab)) {
                                                continue;
                                            }
                                            $rows[] = [
                                                'id' => $typeSlug . '__' . $slug($lab),
                                                'label' => $lab,
                                            ];
                                        }
                                        if (!empty($rows)) {
                                            $sections[$typeLabel] = $rows;
                                        }
                                    }
                                @endphp

                                <div class="row g-4">
                                    {{-- Generali (solo co) --}}
                                    <div class="col-md-6 col-lg-4">
                                        <h3 class="h6 mb-2 border-bottom pb-1">Generali</h3>
                                        <div class="form-check mb-1">
                                            <input class="form-check-input" type="checkbox" name="specialization[]"
                                                id="spec_co" value="co"
                                                {{ in_array('co', $selected, true) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="spec_co">Co</label>
                                        </div>
                                    </div>

                                    {{-- Tutti i tipi con le proprie attrezzature --}}
                                    @foreach ($sections as $title => $rows)
                                        @if ($title === 'Generali')
                                            @continue
                                        @endif
                                        <div class="col-md-6 col-lg-4">
                                            <h3 class="h6 mb-2 border-bottom pb-1">{{ $title }}</h3>
                                            @foreach ($rows as $row)
                                                <div class="form-check mb-1">
                                                    <input class="form-check-input" type="checkbox"
                                                        name="specialization[]" id="spec_{{ $row['id'] }}"
                                                        value="{{ $row['id'] }}"
                                                        {{ in_array($row['id'], $selected, true) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="spec_{{ $row['id'] }}">
                                                        {{ $row['label'] }}
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endforeach
                                </div>

                                <div class="mt-4 text-end">
                                    <button type="submit" class="btn btn-primary"
                                        aria-label="Salva modifiche alle specializzazioni">
                                        Salva
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </main>
</x-layout>
