<x-layout documentTitle="Modifica Gara">
    <div class="container pt-5 mt-5">
        <h1 class="mb-4">Modifica Gara</h1>

        <form action="{{ route('admin.race.update', $race) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="row g-4">
                <div class="col-md-4">
                    {{-- Data della gara --}}
                    <div class="mb-3">
                        <label for="date_of_race" class="form-label">Data della gara</label>
                        <input type="date" id="date_of_race" name="date_of_race" class="form-control" required
                            value="{{ old('date_of_race', \Carbon\Carbon::parse($race->date_of_race)->format('Y-m-d')) }}">
                    </div>

                    {{-- Luogo della gara --}}
                    <div class="mb-3">
                        <label for="place" class="form-label">Luogo della gara</label>
                        <input id="place" type="text" class="form-control @error('place') is-invalid @enderror"
                            name="place" value="{{ old('place', $race->place) }}">
                        @error('place')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Salva Modifiche</button>
                </div>

                {{-- Specializzazione della gara --}}
                <div class="col-md-8">
                    <div class="row g-3">
                        @php
                            $specs = $race->specialization_of_race ?? [];
                        @endphp

                        <div class="col-md-4">
                            <h5>Lynx fotofinish</h5>
                            @foreach (['per_atletica', 'per_ciclismo', 'per_pattinaggio'] as $spec)
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" name="specialization_of_race[]"
                                        value="{{ $spec }}" id="{{ $spec }}"
                                        {{ in_array($spec, $specs) ? 'checked' : '' }}>
                                    <label class="form-check-label"
                                        for="{{ $spec }}">{{ ucwords(str_replace('_', ' ', $spec)) }}</label>
                                </div>
                            @endforeach

                            <h5 class="mt-3">Elaborazione dati</h5>
                            @foreach (['sciplus', 'enduroplus', 'rallyplus', 'canoaplus', 'wicklax', 'cuitiplus', 'orologio_regolarità'] as $spec)
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" name="specialization_of_race[]"
                                        value="{{ $spec }}" id="{{ $spec }}"
                                        {{ in_array($spec, $specs) ? 'checked' : '' }}>
                                    <label class="form-check-label"
                                        for="{{ $spec }}">{{ ucwords(str_replace('_', ' ', $spec)) }}</label>
                                </div>
                            @endforeach
                        </div>

                        <div class="col-md-4">
                            @foreach (['piastre', 'trasponder', 'centro_classifica'] as $spec)
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" name="specialization_of_race[]"
                                        value="{{ $spec }}" id="{{ $spec }}"
                                        {{ in_array($spec, $specs) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold fs-5"
                                        for="{{ $spec }}">{{ ucwords(str_replace('_', ' ', $spec)) }}</label>
                                </div>
                            @endforeach

                            <h5 class="mt-3">Cronometri</h5>
                            @foreach (['master', 'rei_pro'] as $spec)
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" name="specialization_of_race[]"
                                        value="{{ $spec }}" id="{{ $spec }}"
                                        {{ in_array($spec, $specs) ? 'checked' : '' }}>
                                    <label class="form-check-label"
                                        for="{{ $spec }}">{{ ucwords(str_replace('_', ' ', $spec)) }}</label>
                                </div>
                            @endforeach

                            <h5 class="mt-3">Apparecchiature di partenza</h5>
                            @foreach (['susanna', 'rally', 'cancelletto_sci'] as $spec)
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" name="specialization_of_race[]"
                                        value="{{ $spec }}" id="{{ $spec }}"
                                        {{ in_array($spec, $specs) ? 'checked' : '' }}>
                                    <label class="form-check-label"
                                        for="{{ $spec }}">{{ ucwords(str_replace('_', ' ', $spec)) }}</label>
                                </div>
                            @endforeach
                        </div>

                        <div class="col-md-4">
                            @foreach (['apparecchiature_di_arrivo', 'tabellone', 'tablet/smartphone'] as $spec)
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" name="specialization_of_race[]"
                                        value="{{ $spec }}" id="{{ $spec }}"
                                        {{ in_array($spec, $specs) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold fs-5"
                                        for="{{ $spec }}">{{ ucwords(str_replace('_', ' ', $spec)) }}</label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</x-layout>
