<x-layout documentTitle="Guest Create Race Temp">
    <main class="container pt-5 mt-5" id="main-content">
        <h1 class="mb-4">Crea nuova gara temporanea</h1>

        <form action="{{ route('raceTemp.store') }}" method="POST" aria-describedby="race-form-description">
            <p id="race-form-description" class="visually-hidden">
                Inserisci la data, il luogo e le specializzazioni tecniche relative alla gara temporanea da creare.
            </p>

            @csrf
            <div class="row g-4">
                <div class="col-md-4">
                    <section aria-labelledby="gara-info">
                        <h2 id="gara-info" class="h5 mb-3">Informazioni generali sulla gara temporanea</h2>

                        {{-- Inserimento mail --}}
                        <div class="form-group">
                            <label for="email" class="form-label">Inserisci la mail a cui vuoi essere contattato</label>
                            <input id="email" type="email" name="email"
                                class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}"
                                required>
                            @error('email')
                                <div class="invalid-feedback" role="alert">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Nome della gara --}}
                        <div class="form-group">
                            <label for="name" class="form-label">Nome Gara</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                        </div>

                        {{-- Data della gara --}}
                        <div class="mb-3">
                            <label for="date_of_race" class="form-label">Data della gara</label>
                            <input type="date" id="date_of_race" name="date_of_race" class="form-control" required>
                        </div>

                        {{-- Luogo della gara --}}
                        <div class="mb-3">
                            <label for="place" class="form-label">Luogo della gara</label>
                            <input id="place" type="text"
                                class="form-control @error('place') is-invalid @enderror" name="place"
                                value="{{ old('place') }}" aria-describedby="placeHelp">
                            @error('place')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Crea gara</button>
                    </section>
                </div>

                {{-- Specializzazione della gara --}}
                <div class="col-md-8">
                    <section aria-labelledby="specializzazioni-gara">
                        <h2 id="specializzazioni-gara" class="h5 mb-3">Specializzazioni tecniche</h2>

                        <div class="row g-3">
                            <div class="col-md-4">
                                <h3 class="h6">Lynx fotofinish</h3>
                                @foreach (['per_atletica', 'per_ciclismo', 'per_pattinaggio'] as $spec)
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" name="specialization_of_race[]"
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
                                        <input type="checkbox" class="form-check-input" name="specialization_of_race[]"
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
                                        <input type="checkbox" class="form-check-input" name="specialization_of_race[]"
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
                                        <input type="checkbox" class="form-check-input" name="specialization_of_race[]"
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
                                        <input type="checkbox" class="form-check-input" name="specialization_of_race[]"
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
                                        <input type="checkbox" class="form-check-input"
                                            name="specialization_of_race[]" value="{{ $spec }}"
                                            id="{{ $spec }}"
                                            {{ in_array($spec, $timekeeper->specialization ?? []) ? 'checked' : '' }}>
                                        <label class="form-check-label fw-bold fs-5" for="{{ $spec }}">
                                            {{ ucwords(str_replace('_', ' ', $spec)) }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </form>
    </main>
</x-layout>
