<x-layout documentTitle="Modifica Gara">
    <main class="container pt-5 mt-5" id="main-content" aria-labelledby="modifica-gara-title">

        {{-- Header a gradiente --}}
        <header class="page-header rounded-4 mb-4 px-4 py-4">
            <h1 id="modifica-gara-title" class="h3 text-white mb-1">Modifica Gara</h1>
            <p class="text-white-50 mb-0">Aggiorna le informazioni principali e gli allegati della gara.</p>
        </header>

        {{-- Errori validazione --}}
        @if ($errors->any())
            <div class="alert alert-danger shadow-sm rounded-3">
                <ul class="mb-0">
                    @foreach ($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.race.update', $race) }}" method="POST" enctype="multipart/form-data"
            aria-describedby="form-descrizione-gara" class="needs-validation" novalidate>
            <p id="form-descrizione-gara" class="visually-hidden">
                Modifica i dettagli della gara e seleziona il tipo di gara.
            </p>

            @csrf
            @method('PUT')

            <div class="row justify-content-center">
                <div class="col-12">
                    <section aria-labelledby="info-gara">
                        <div class="card ficr-card border-0 shadow-sm rounded-4">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-center mb-3">
                                    <span class="ficr-badge-icon me-2"><i class="fa-solid fa-flag-checkered"></i></span>
                                    <h2 id="info-gara" class="h5 mb-0">Informazioni sulla gara</h2>
                                </div>

                                @php
                                    // tipi gara presi dal config
                                    $types = array_keys(config('races.types', []));
                                    $oldType = old('type', $race->type);
                                @endphp

                                {{-- Nome --}}
                                <div class="mb-3">
                                    <label for="name" class="form-label">Nome della gara</label>
                                    <input id="name" type="text"
                                        class="form-control @error('name') is-invalid @enderror" name="name"
                                        value="{{ old('name', $race->name) }}">
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="row g-3">
                                    {{-- Data inizio --}}
                                    <div class="col-md-6">
                                        <label for="date_of_race" class="form-label">Data inizio</label>
                                        <input type="date" id="date_of_race" name="date_of_race"
                                            class="form-control @error('date_of_race') is-invalid @enderror"
                                            value="{{ old('date_of_race', \Carbon\Carbon::parse($race->date_of_race)->format('Y-m-d')) }}"
                                            required>
                                        @error('date_of_race')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    {{-- Data fine --}}
                                    <div class="col-md-6">
                                        <label for="date_end" class="form-label">Data fine</label>
                                        <input type="date" id="date_end" name="date_end"
                                            class="form-control @error('date_end') is-invalid @enderror"
                                            value="{{ old('date_end', $race->date_end ? \Carbon\Carbon::parse($race->date_end)->format('Y-m-d') : '') }}">
                                        @error('date_end')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Luogo --}}
                                <div class="mt-3 mb-1">
                                    <label for="place" class="form-label">Luogo della gara</label>
                                    <input id="place" type="text"
                                        class="form-control @error('place') is-invalid @enderror" name="place"
                                        value="{{ old('place', $race->place) }}">
                                    @error('place')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Tipo gara (dal config) --}}
                                <div class="mt-3">
                                    <label for="type" class="form-label">Tipo gara</label>
                                    <select id="type" name="type"
                                        class="form-select @error('type') is-invalid @enderror" required>
                                        <option value="" disabled {{ $oldType ? '' : 'selected' }}>Seleziona…
                                        </option>
                                        @foreach ($types as $t)
                                            <option value="{{ $t }}"
                                                {{ $oldType === $t ? 'selected' : '' }}>
                                                {{ $t }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <hr class="my-4">

                                <div class="d-flex align-items-center mb-3">
                                    <span class="ficr-badge-icon me-2"><i class="fa-solid fa-file-invoice"></i></span>
                                    <h3 class="h6 mb-0">Dati amministrativi</h3>
                                </div>

                                {{-- Ente fatturazione --}}
                                <div class="mb-3">
                                    <label for="ente_fatturazione" class="form-label">Ente organizzatore per
                                        fatturazione</label>
                                    <input id="ente_fatturazione" type="text"
                                        class="form-control @error('ente_fatturazione') is-invalid @enderror"
                                        name="ente_fatturazione"
                                        value="{{ old('ente_fatturazione', $race->ente_fatturazione) }}">
                                    @error('ente_fatturazione')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Programma allegato --}}
                                <div class="mb-3">
                                    <label for="programma_allegato" class="form-label">Programma gara (PDF/DOC/ZIP,
                                        max 10MB)</label>
                                    <input type="file" id="programma_allegato" name="programma_allegato"
                                        class="form-control @error('programma_allegato') is-invalid @enderror"
                                        accept=".pdf,.doc,.docx,.odt,.zip">
                                    @error('programma_allegato')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror

                                    @php
                                        $allegatoPath = $race->programma_allegato ?? null;
                                        $allegatoUrl = null;
                                        if (
                                            $allegatoPath &&
                                            \Illuminate\Support\Facades\Storage::disk('public')->exists($allegatoPath)
                                        ) {
                                            $allegatoUrl = \Illuminate\Support\Facades\Storage::url($allegatoPath);
                                        }
                                    @endphp
                                    @if ($allegatoUrl)
                                        <small class="d-block mt-2">
                                            Allegato attuale:
                                            <a href="{{ $allegatoUrl }}" target="_blank" rel="noopener">Visualizza
                                                / scarica</a>
                                        </small>
                                    @endif
                                </div>

                                {{-- Note --}}
                                <div class="mb-4">
                                    <label for="note" class="form-label">Note / Commenti</label>
                                    <textarea id="note" class="form-control @error('note') is-invalid @enderror" name="note" rows="3">{{ old('note', $race->note) }}</textarea>
                                    @error('note')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="d-grid">
                                    <button type="submit" class="btn btn-ficr">Salva Modifiche</button>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </form>
    </main>
</x-layout>
