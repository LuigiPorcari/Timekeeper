<x-layout documentTitle="Admin Timekeeper Details">
    <main class="container mt-5 pt-5" id="main-content" aria-labelledby="timekeeper-details-title">
        <h1 id="timekeeper-details-title" class="mb-4">
            Dettagli {{ $timekeeper->name }} {{ $timekeeper->surname }}
        </h1>

        @if (session('success'))
            <div class="alert alert-dismissible alert-success" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Chiudi notifica"></button>
            </div>
        @endif

        <div class="row g-4">
            <div class="col-md-4">
                <section aria-labelledby="dati-personali">
                    <div class="card">
                        <div class="card-body">
                            <h2 id="dati-personali" class="card-title h5">
                                {{ $timekeeper->name }} {{ $timekeeper->surname }}
                            </h2>
                            <p><strong>Email:</strong> {{ $timekeeper->email }}</p>
                            <p><strong>Data di nascita:</strong>
                                {{ \Carbon\Carbon::parse($timekeeper->date_of_birth)->format('d-m-Y') }}
                            </p>
                            @if ($timekeeper->residence)
                                <p><strong>Residenza:</strong> {{ $timekeeper->residence }}</p>
                            @endif
                            <p><strong>Domicilio:</strong> {{ $timekeeper->domicile }}</p>
                            <p><strong>Transferta:</strong>
                                {{ match ($timekeeper->transfer) {
                                    'no' => 'NO',
                                    '1' => '1 notte',
                                    '2/5' => 'tra 2 e 5 notti',
                                    '>5' => 'più di 5 notti',
                                    default => '—',
                                } }}
                            </p>
                            <p><strong>Automunito:</strong> {{ $timekeeper->auto ? 'SI' : 'NO' }}</p>
                            <p><strong>Specializzazione:</strong><br>
                                @if (empty($timekeeper->specialization))
                                    Cronometrista generico
                                @else
                                    @foreach ($timekeeper->specialization as $specialization)
                                        {{ $specialization }}<br>
                                    @endforeach
                                @endif
                            </p>
                            <p><strong>Disponibilità:</strong><br>
                                @forelse ($timekeeper->availabilities as $a)
                                    {{ ucwords(\Carbon\Carbon::parse($a->date_of_availability)->translatedFormat('l d F')) }}<br>
                                @empty
                                    Nessuna Disponibilità
                                @endforelse
                            </p>
                        </div>
                    </div>
                </section>
                <a class="btn btn-primary mt-2" href="{{ route('admin.timekeeperList') }}"
                    aria-label="Torna alla lista dei cronometristi">
                    Indietro
                </a>
            </div>

            <div class="col-md-8">
                <section aria-labelledby="modifica-specializzazione">
                    <h2 id="modifica-specializzazione" class="h4 mb-3">Seleziona disciplina</h2>

                    <form action="{{ route('update.timekeeper', $timekeeper) }}" method="POST"
                        aria-describedby="form-specializzazione-descrizione">
                        <p id="form-specializzazione-descrizione" class="visually-hidden">
                            Seleziona o modifica le specializzazioni per il cronometrista.
                        </p>

                        @csrf
                        <fieldset>
                            <legend class="visually-hidden">Categorie di specializzazione disponibili</legend>

                            <div class="row g-4">
                                <div class="col-md-4">
                                    <h3 class="h6">Lynx fotofinish</h3>
                                    @foreach (['per_atletica', 'per_ciclismo', 'per_pattinaggio'] as $spec)
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="specialization[]"
                                                value="{{ $spec }}" id="{{ $spec }}"
                                                {{ in_array($spec, $timekeeper->specialization ?? []) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="{{ $spec }}">
                                                {{ ucwords(str_replace('_', ' ', $spec)) }}
                                            </label>
                                        </div>
                                    @endforeach

                                    <h3 class="h6 mt-3">Elaborazione dati</h3>
                                    @foreach (['sciplus', 'enduroplus', 'rallyplus', 'canoaplus', 'wicklax', 'cuitiplus', 'orologio_regolarità'] as $spec)
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="specialization[]"
                                                value="{{ $spec }}" id="{{ $spec }}"
                                                {{ in_array($spec, $timekeeper->specialization ?? []) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="{{ $spec }}">
                                                {{ ucwords(str_replace('_', ' ', $spec)) }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="col-md-4">
                                    @foreach (['piastre', 'trasponder', 'centro_classifica'] as $spec)
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="specialization[]"
                                                value="{{ $spec }}" id="{{ $spec }}"
                                                {{ in_array($spec, $timekeeper->specialization ?? []) ? 'checked' : '' }}>
                                            <label class="form-check-label fw-bold fs-5" for="{{ $spec }}">
                                                {{ ucwords(str_replace('_', ' ', $spec)) }}
                                            </label>
                                        </div>
                                    @endforeach

                                    <h3 class="h6 mt-3">Cronometri</h3>
                                    @foreach (['master', 'rei_pro'] as $spec)
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="specialization[]"
                                                value="{{ $spec }}" id="{{ $spec }}"
                                                {{ in_array($spec, $timekeeper->specialization ?? []) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="{{ $spec }}">
                                                {{ ucwords(str_replace('_', ' ', $spec)) }}
                                            </label>
                                        </div>
                                    @endforeach

                                    <h3 class="h6 mt-3">Apparecchiature di partenza</h3>
                                    @foreach (['susanna', 'rally', 'cancelletto_sci'] as $spec)
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="specialization[]"
                                                value="{{ $spec }}" id="{{ $spec }}"
                                                {{ in_array($spec, $timekeeper->specialization ?? []) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="{{ $spec }}">
                                                {{ ucwords(str_replace('_', ' ', $spec)) }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="col-md-4">
                                    @foreach (['apparecchiature_di_arrivo', 'tabellone', 'tablet/smartphone'] as $spec)
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="specialization[]"
                                                value="{{ $spec }}" id="{{ $spec }}"
                                                {{ in_array($spec, $timekeeper->specialization ?? []) ? 'checked' : '' }}>
                                            <label class="form-check-label fw-bold fs-5" for="{{ $spec }}">
                                                {{ ucwords(str_replace('_', ' ', $spec)) }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </fieldset>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary"
                                aria-label="Salva modifiche alle specializzazioni">
                                Salva
                            </button>
                        </div>
                    </form>
                </section>
            </div>
        </div>
    </main>
</x-layout>
