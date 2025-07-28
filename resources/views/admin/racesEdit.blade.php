<x-layout documentTitle="Modifica Gara">
    <main class="container pt-5 mt-5" id="main-content" aria-labelledby="modifica-gara-title">
        <h1 id="modifica-gara-title" class="mb-4">Modifica Gara</h1>

        <form action="{{ route('admin.race.update', $race) }}" method="POST" aria-describedby="form-descrizione-gara">
            <p id="form-descrizione-gara" class="visually-hidden">
                Modifica i dettagli della gara e seleziona o aggiorna le specializzazioni tecniche richieste.
            </p>

            @csrf
            @method('PUT')

            <div class="row g-4">
                <div class="col-md-4">
                    <section aria-labelledby="info-gara">
                        <h2 id="info-gara" class="h5">Informazioni sulla gara</h2>

                        {{-- Nome della gara --}}
                        <div class="mb-3">
                            <label for="name" class="form-label">Nome della gara</label>
                            <input id="name" type="text"
                                class="form-control @error('name') is-invalid @enderror" name="name"
                                value="{{ old('name', $race->name) }}" aria-describedby="nameHelp">
                            @error('name')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        {{-- Data della gara --}}
                        <div class="mb-3">
                            <label for="date_of_race" class="form-label">Data della gara</label>
                            <input type="date" id="date_of_race" name="date_of_race" class="form-control" required
                                value="{{ old('date_of_race', \Carbon\Carbon::parse($race->date_of_race)->format('Y-m-d')) }}">
                        </div>

                        {{-- Luogo della gara --}}
                        <div class="mb-3">
                            <label for="place" class="form-label">Luogo della gara</label>
                            <input id="place" type="text"
                                class="form-control @error('place') is-invalid @enderror" name="place"
                                value="{{ old('place', $race->place) }}" aria-describedby="placeHelp">
                            @error('place')
                                <div class="invalid-feedback">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Salva Modifiche</button>
                    </section>
                </div>

                <div class="col-md-8">
                    <section aria-labelledby="specializzazioni-gara">
                        <h2 id="specializzazioni-gara" class="h5">Specializzazioni tecniche</h2>

                        <div class="row g-3">
                            @php
                                $specs = $race->specialization_of_race ?? [];
                            @endphp

                            <div class="col-md-4">
                                <h3 class="h6">Lynx fotofinish</h3>
                                @foreach (['per_atletica', 'per_ciclismo', 'per_pattinaggio'] as $spec)
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" name="specialization_of_race[]"
                                            value="{{ $spec }}" id="{{ $spec }}"
                                            {{ in_array($spec, $specs) ? 'checked' : '' }}>
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
                                            {{ in_array($spec, $specs) ? 'checked' : '' }}>
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
                                            {{ in_array($spec, $specs) ? 'checked' : '' }}>
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
                                            {{ in_array($spec, $specs) ? 'checked' : '' }}>
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
                                            {{ in_array($spec, $specs) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="{{ $spec }}">
                                            {{ ucwords(str_replace('_', ' ', $spec)) }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>

                            <div class="col-md-4">
                                @foreach (['apparecchiature_di_arrivo', 'tabellone', 'tablet/smartphone'] as $spec)
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" name="specialization_of_race[]"
                                            value="{{ $spec }}" id="{{ $spec }}"
                                            {{ in_array($spec, $specs) ? 'checked' : '' }}>
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
