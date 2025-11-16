<x-layout documentTitle="Admin Create Race">
    <main class="container pt-5 mt-5" id="main-content">

        {{-- HEADER A GRADIENTE --}}
        <header class="page-header rounded-4 mb-4 px-4 py-4">
            <h1 class="h3 text-white mb-1">Crea nuova gara</h1>
            <p class="text-white-50 mb-0">
                Inserisci periodo, luogo, tipologia ed eventuali dettagli organizzativi.
            </p>
        </header>

        {{-- Errori validazione --}}
        @if ($errors->any())
            <div class="alert alert-danger shadow-sm border-0">
                <ul class="mb-0">
                    @foreach ($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card ficr-card shadow-sm border-0 rounded-4">
            <div class="card-body p-4 p-md-5">
                <form action="{{ route('race.store') }}" method="POST" enctype="multipart/form-data"
                    aria-describedby="race-form-description" novalidate>
                    @csrf

                    <p id="race-form-description" class="visually-hidden">
                        Inserisci la data (inizio/fine), il luogo, i dati per fatturazione, eventuale programma allegato
                        e il tipo di gara da creare.
                    </p>

                    {{-- Sezione: Informazioni generali --}}
                    <h2 class="h5 mb-3">Informazioni generali sulla gara</h2>

                    <div class="row g-3">
                        {{-- Nome --}}
                        <div class="col-12">
                            <label for="name" class="form-label">Nome Gara *</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                id="name" name="name" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Date --}}
                        <div class="col-12 col-md-6">
                            <label for="date_of_race" class="form-label">Data inizio *</label>
                            <input type="date" id="date_of_race" name="date_of_race"
                                class="form-control @error('date_of_race') is-invalid @enderror"
                                value="{{ old('date_of_race') }}" required>
                            @error('date_of_race')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="date_end" class="form-label">Data fine</label>
                            <input type="date" id="date_end" name="date_end"
                                class="form-control @error('date_end') is-invalid @enderror"
                                value="{{ old('date_end') }}">
                            @error('date_end')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Luogo --}}
                        <div class="col-12 col-md-6">
                            <label for="place" class="form-label">Luogo della gara *</label>
                            <input id="place" type="text"
                                class="form-control @error('place') is-invalid @enderror" name="place"
                                value="{{ old('place') }}" required>
                            @error('place')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Tipo gara (config con fallback) --}}
                        <div class="col-12 col-md-6">
                            <label for="type" class="form-label">Tipo gara *</label>
                            @php
                                $types = array_keys(config('races.types', []));
                            @endphp
                            <select id="type" name="type"
                                class="form-select @error('type') is-invalid @enderror" required>
                                <option value="" disabled {{ old('type') ? '' : 'selected' }}>Seleziona…</option>
                                @foreach ($types as $t)
                                    <option value="{{ $t }}" {{ old('type') === $t ? 'selected' : '' }}>
                                        {{ $t }}
                                    </option>
                                @endforeach
                            </select>
                            @error('type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <hr class="my-4">

                    {{-- Sezione: Documenti & Note --}}
                    <h2 class="h6 mb-3 text-uppercase text-muted">Documenti & Note</h2>

                    <div class="row g-3">
                        {{-- Ente fatturazione --}}
                        <div class="col-12">
                            <label for="ente_fatturazione" class="form-label">Ente organizzatore per
                                fatturazione</label>
                            <input id="ente_fatturazione" type="text"
                                class="form-control @error('ente_fatturazione') is-invalid @enderror"
                                name="ente_fatturazione" value="{{ old('ente_fatturazione') }}">
                            @error('ente_fatturazione')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Programma allegato --}}
                        <div class="col-12 col-md-6">
                            <label for="programma_allegato" class="form-label">Programma gara (PDF/DOC/ZIP, max
                                10MB)</label>
                            <input type="file" id="programma_allegato" name="programma_allegato"
                                class="form-control @error('programma_allegato') is-invalid @enderror"
                                accept=".pdf,.doc,.docx,.odt,.zip">
                            @error('programma_allegato')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Formati supportati: PDF, DOC, DOCX, ODT, ZIP.</div>
                        </div>

                        {{-- Note --}}
                        <div class="col-12">
                            <label for="note" class="form-label">Note / Commenti</label>
                            <textarea id="note" name="note" rows="3" class="form-control @error('note') is-invalid @enderror"
                                placeholder="Informazioni utili per la gestione della gara">{{ old('note') }}</textarea>
                            @error('note')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="d-grid mt-4">
                        <button type="submit" class="btn btn-ficr btn-lg">Crea gara</button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</x-layout>
