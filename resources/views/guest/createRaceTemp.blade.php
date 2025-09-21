{{-- resources/views/guest/createRaceTemp.blade.php --}}
<x-layout documentTitle="Guest Create Race Temp">
    <main class="container pt-5 mt-5" id="main-content" aria-labelledby="page-title">
        <h1 id="page-title" class="mb-4">Crea nuova gara temporanea</h1>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="row justify-content-center">
            <div class="col-12 col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-white auth-header px-4 py-4">
                        <h2 class="h5 mb-0 text-white">Informazioni generali</h2>
                        <small class="text-white">Compila i campi obbligatori contrassegnati con *</small>
                    </div>

                    <div class="card-body">
                        <form action="{{ route('raceTemp.store') }}" method="POST" enctype="multipart/form-data"
                            aria-describedby="race-form-description">
                            @csrf
                            <p id="race-form-description" class="visually-hidden">
                                Inserisci il periodo, il luogo, l’ente per fatturazione, eventuale programma e il tipo
                                di gara.
                            </p>

                            {{-- Email di contatto --}}
                            <div class="mb-3">
                                <label for="email" class="form-label">Email contatto *</label>
                                <input id="email" type="email" name="email"
                                    class="form-control @error('email') is-invalid @enderror"
                                    value="{{ old('email') }}" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Nome gara --}}
                            <div class="mb-3">
                                <label for="name" class="form-label">Nome Gara *</label>
                                <input id="name" type="text" name="name"
                                    class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}"
                                    required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Tipo gara --}}
                            <div class="mb-3">
                                <label for="type" class="form-label">Tipo gara *</label>
                                <select id="type" name="type"
                                    class="form-select @error('type') is-invalid @enderror" required>
                                    <option value="" disabled {{ old('type') ? '' : 'selected' }}>Seleziona…
                                    </option>
                                    @foreach (['NUOTO -NUOTO SALVAMENTO', 'SCI ALPINO – SCI NORDICO', 'ATLETICA LEGGERA', 'MOTORALLY', 'RALLY', 'ENDURO MOTO', 'ENDURO MTB', 'MOTOCROSS', 'CANOA', 'CANOTTAGGIO', 'CICLISMO SU STRADA', 'CICLISMO PISTA', 'DOWHINILL', 'AUTO REGOLARITA’', 'AUTO STORICHE', 'AUTOMOBILSMO CIRCUITO', 'CONCORSO IPPICO', 'TROTTO'] as $t)
                                        <option value="{{ $t }}" {{ old('type') === $t ? 'selected' : '' }}>
                                            {{ $t }}</option>
                                    @endforeach
                                </select>
                                @error('type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Periodo --}}
                            <div class="row">
                                <div class="col-12 col-md-6 mb-3">
                                    <label for="date_of_race" class="form-label">Data inizio *</label>
                                    <input type="date" id="date_of_race" name="date_of_race"
                                        class="form-control @error('date_of_race') is-invalid @enderror"
                                        value="{{ old('date_of_race') }}" required>
                                    @error('date_of_race')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-12 col-md-6 mb-3">
                                    <label for="date_end" class="form-label">Data fine</label>
                                    <input type="date" id="date_end" name="date_end"
                                        class="form-control @error('date_end') is-invalid @enderror"
                                        value="{{ old('date_end') }}">
                                    @error('date_end')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- Luogo --}}
                            <div class="mb-3">
                                <label for="place" class="form-label">Luogo *</label>
                                <input id="place" type="text" name="place"
                                    class="form-control @error('place') is-invalid @enderror"
                                    value="{{ old('place') }}" required>
                                @error('place')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Ente fatturazione --}}
                            <div class="mb-3">
                                <label for="ente_fatturazione" class="form-label">Ente per fatturazione</label>
                                <input id="ente_fatturazione" type="text" name="ente_fatturazione"
                                    class="form-control @error('ente_fatturazione') is-invalid @enderror"
                                    value="{{ old('ente_fatturazione') }}">
                                @error('ente_fatturazione')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Programma --}}
                            <div class="mb-3">
                                <label for="programma_allegato" class="form-label">Programma (PDF/DOC/ZIP, max
                                    10MB)</label>
                                <input type="file" id="programma_allegato" name="programma_allegato"
                                    class="form-control @error('programma_allegato') is-invalid @enderror"
                                    accept=".pdf,.doc,.docx,.odt,.zip">
                                @error('programma_allegato')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Note --}}
                            <div class="mb-4">
                                <label for="note" class="form-label">Note / Commenti</label>
                                <textarea id="note" name="note" rows="3" class="form-control @error('note') is-invalid @enderror">{{ old('note') }}</textarea>
                                @error('note')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-ficr">Invia richiesta</button>
                            </div>
                        </form>
                    </div> {{-- /.card-body --}}
                </div> {{-- /.card --}}
            </div>
        </div>
    </main>
</x-layout>
