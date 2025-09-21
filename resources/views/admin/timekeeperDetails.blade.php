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
            {{-- Colonna profilo --}}
            <div class="col-md-4">
                <section aria-labelledby="dati-personali">
                    <div class="card shadow-sm rounded-3">
                        <div class="card-header bg-white">
                            <h2 id="dati-personali" class="h5 mb-0">
                                {{ $timekeeper->name }} {{ $timekeeper->surname }}
                            </h2>
                            <small class="text-muted">Cronometrista</small>
                        </div>

                        <div class="card-body">
                            <dl class="row mb-0">
                                <dt class="col-5 text-muted small">Email</dt>
                                <dd class="col-7">{{ $timekeeper->email }}</dd>

                                <dt class="col-5 text-muted small">Data di nascita</dt>
                                <dd class="col-7">
                                    {{ \Carbon\Carbon::parse($timekeeper->date_of_birth)->format('d-m-Y') }}</dd>

                                @if ($timekeeper->residence)
                                    <dt class="col-5 text-muted small">Residenza</dt>
                                    <dd class="col-7">{{ $timekeeper->residence }}</dd>
                                @endif

                                <dt class="col-5 text-muted small">Domicilio</dt>
                                <dd class="col-7">{{ $timekeeper->domicile }}</dd>

                                <dt class="col-5 text-muted small">Transferta</dt>
                                <dd class="col-7">
                                    {{ match ($timekeeper->transfer) {
                                        'no' => 'NO',
                                        '1' => '1 notte',
                                        '2/5' => 'tra 2 e 5 notti',
                                        '>5' => 'più di 5 notti',
                                        default => '—',
                                    } }}
                                </dd>

                                <dt class="col-5 text-muted small">Automunito</dt>
                                <dd class="col-7">{{ $timekeeper->auto ? 'SI' : 'NO' }}</dd>
                            </dl>

                            <hr class="my-3">

                            {{-- Specializzazioni già assegnate (solo display) --}}
                            <div class="mb-3">
                                <div class="fw-semibold mb-2">Specializzazioni attuali</div>
                                @if (empty($timekeeper->specialization))
                                    <span class="text-muted">Cronometrista generico</span>
                                @else
                                    <div class="d-flex flex-wrap gap-2">
                                        @foreach ($timekeeper->specialization as $specialization)
                                            <span class="badge badge-soft border">
                                                {{ str_replace('_', ' ', $specialization) }}
                                            </span>
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

            {{-- Colonna form specializzazioni --}}
            <div class="col-md-8">
                <section aria-labelledby="modifica-specializzazione">
                    <div class="card shadow-sm rounded-3">
                        <div class="card-header bg-white">
                            <h2 id="modifica-specializzazione" class="h5 mb-1">Seleziona disciplina</h2>
                            <small class="text-muted">Spunta tutte le specializzazioni pertinenti.</small>
                        </div>

                        <div class="card-body">
                            <form action="{{ route('update.timekeeper', $timekeeper) }}" method="POST"
                                aria-describedby="form-specializzazione-descrizione">
                                <p id="form-specializzazione-descrizione" class="visually-hidden">
                                    Seleziona o modifica le specializzazioni per il cronometrista.
                                </p>
                                @csrf

                                <fieldset>
                                    <legend class="visually-hidden">Categorie di specializzazione disponibili</legend>

                                    @php
                                        $typeToSpecs = [
                                            'NUOTO -NUOTO SALVAMENTO' => ['elaborazione_dati', 'vasca'],
                                            'SCI ALPINO – SCI NORDICO' => [
                                                'partenza',
                                                'arrivo',
                                                'elaborazione_dati_completa',
                                                'elaborazione_dati_parziale_live',
                                            ],
                                            'ATLETICA LEGGERA' => ['fotofinish', 'manuale'],
                                            'MOTORALLY' => ['centro_classifica', 'tracking'],
                                            'RALLY' => [
                                                'centro_classifica',
                                                'start_ps',
                                                'fine_ps',
                                                'controllo_orari_co',
                                                'riordini',
                                                'assistenza_partenza_arrivo',
                                                'palco_premiazioni',
                                            ],
                                            'ENDURO MOTO' => [
                                                'transponder_pc',
                                                'solo_cronometraggio_start',
                                                'solo_cronometraggio_fine',
                                                'co_con_pc',
                                                'co_solo_tablet',
                                            ],
                                            'ENDURO MTB' => ['elaborazione_dati', 'partenza_prova', 'fine_prova'],
                                            'MOTOCROSS' => ['elaborazione_dati', 'arrivo'],
                                            'CANOA' => [
                                                'elaborazione_dati',
                                                'arrivo',
                                                'partenza_orologio_tablet',
                                                'fotofinish',
                                            ],
                                            'CANOTTAGGIO' => ['arrivo', 'partenza_orologio_tablet'],
                                            'CICLISMO SU STRADA' => ['arrivo', 'fotofinish'],
                                            'CICLISMO PISTA' => ['arrivo_bandelle', 'fotofinish'],
                                            'DOWHINILL' => ['partenza', 'arrivo', 'elaborazione_dati'],
                                            'AUTO REGOLARITA’' => ['pressostati', 'tablet'],
                                            'AUTO STORICHE' => ['arrivo', 'start'],
                                            'AUTOMOBILSMO CIRCUITO' => [
                                                'elaborazione_dati',
                                                'contagiri',
                                                'transponder',
                                            ],
                                            'CONCORSO IPPICO' => ['prog_spec_concorso_ippico'],
                                            'TROTTO' => ['utilizzo_spec_programma'],
                                        ];

                                        $counts = [];
                                        foreach ($typeToSpecs as $specs) {
                                            foreach ($specs as $s) {
                                                $counts[$s] = ($counts[$s] ?? 0) + 1;
                                            }
                                        }
                                        $commons = array_keys(array_filter($counts, fn($c) => $c > 1));

                                        $sections = [];
                                        $sections['Generali'] = array_unique(array_merge(['co'], $commons));
                                        foreach ($typeToSpecs as $type => $specs) {
                                            $specific = array_values(array_diff($specs, $commons));
                                            if (!empty($specific)) {
                                                $sections[$type] = $specific;
                                            }
                                        }

                                        $labels = [
                                            'elaborazione_dati' => 'Elaborazione dati',
                                            'elaborazione_dati_completa' => 'Elaborazione dati completa',
                                            'elaborazione_dati_parziale_live' => 'Elaborazione dati parziale (live)',
                                            'partenza_orologio_tablet' => 'Partenza con orologio/tablet',
                                            'arrivo_bandelle' => 'Arrivo – Bandelle',
                                            'start_ps' => 'Start PS',
                                            'fine_ps' => 'Fine PS',
                                            'controllo_orari_co' => 'Controllo orari (CO)',
                                            'assistenza_partenza_arrivo' => 'Assistenza/Partenza/Arrivo',
                                            'transponder_pc' => 'Transponder – PC',
                                            'solo_cronometraggio_start' => 'Solo cronometraggio: start',
                                            'solo_cronometraggio_fine' => 'Solo cronometraggio: fine',
                                            'co_con_pc' => 'CO con PC',
                                            'co_solo_tablet' => 'CO solo tablet',
                                            'prog_spec_concorso_ippico' => 'Programma specifico concorso ippico',
                                            'utilizzo_spec_programma' => 'Utilizzo specifico programma',
                                            'centro_classifica' => 'Centro classifica',
                                            'fotofinish' => 'Fotofinish',
                                            'pressostati' => 'Pressostati',
                                        ];

                                        $selected = $timekeeper->specialization ?? [];
                                        $nice = fn(string $slug) => $labels[$slug] ??
                                            ucwords(str_replace(['_', '-'], ' ', $slug));
                                    @endphp

                                    <div class="row g-4">
                                        @foreach ($sections as $title => $specList)
                                            <div class="col-md-6 col-lg-4">
                                                <h3 class="h6 mb-2 border-bottom pb-1">{{ $title }}</h3>
                                                @foreach ($specList as $spec)
                                                    <div class="form-check mb-1">
                                                        <input class="form-check-input" type="checkbox"
                                                            name="specialization[]" value="{{ $spec }}"
                                                            id="spec_{{ $spec }}"
                                                            {{ in_array($spec, $selected, true) ? 'checked' : '' }}>
                                                        <label class="form-check-label" for="spec_{{ $spec }}">
                                                            {{ $nice($spec) }}
                                                        </label>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endforeach
                                    </div>
                                </fieldset>

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
