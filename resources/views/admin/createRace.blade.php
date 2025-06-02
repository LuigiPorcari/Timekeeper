<x-layout documentTitle="Admin Create Race">
    <div class="pt-5">
        <h1 class="mt-4">Crea nuova gara</h1>
    </div>
    <div>
        <form action="{{ route('race.store') }}" method="POST">
            @csrf
            <div class="container">
                <div class="row">
                    <div class="col-4">
                        {{-- Data della gara --}}
                        <div class="mb-3">
                            <label for="date_of_race">Data della gara</label>
                            <input type="date" id="date_of_race" name="date_of_race" required>
                        </div>
                        {{-- Luogo della gara --}}
                        <div class="form-group mb-3">
                            <label for="place">Luogo della gara</label>
                            <input id="place" type="text" class="@error('place') is-invalid @enderror"
                                name="place" value="{{ old('place') }}">
                            @error('place')
                                <span role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div>
                            <button type="submit" class="btn">
                                Crea gara
                            </button>
                        </div>
                    </div>
                    {{-- Specializzazione della gara --}}
                    <div class="col-8">
                        <div class="container-fluid">
                            <div class="row">
                                <div class="col-4">
                                    <div>
                                        <h5>Lynx fotofinish</h5>
                                        <div>
                                            <input type="checkbox" name="specialization_of_race[]" value="per_atletica"
                                                {{ in_array('per_atletica', $timekeeper->specialization ?? []) ? 'checked' : '' }}>
                                            <label>Per atletica</label>
                                        </div>
                                        <div>
                                            <input type="checkbox" name="specialization_of_race[]" value="per_ciclismo"
                                                {{ in_array('per_ciclismo', $timekeeper->specialization ?? []) ? 'checked' : '' }}>
                                            <label>Per ciclismo</label>
                                        </div>
                                        <div>
                                            <input type="checkbox" name="specialization_of_race[]"
                                                value="per_pattinaggio"
                                                {{ in_array('per_pattinaggio', $timekeeper->specialization ?? []) ? 'checked' : '' }}>
                                            <label>Per pattinaggio</label>
                                        </div>
                                    </div>
                                    <div>
                                        <h5>Elaborazione dati</h5>
                                        <div>
                                            <input type="checkbox" name="specialization_of_race[]" value="sciplus"
                                                {{ in_array('sciplus', $timekeeper->specialization ?? []) ? 'checked' : '' }}>
                                            <label>Sciplus</label>
                                        </div>
                                        <div>
                                            <input type="checkbox" name="specialization_of_race[]" value="enduroplus"
                                                {{ in_array('enduroplus', $timekeeper->specialization ?? []) ? 'checked' : '' }}>
                                            <label>Enduroplus</label>
                                        </div>
                                        <div>
                                            <input type="checkbox" name="specialization_of_race[]" value="rallyplus"
                                                {{ in_array('rallyplus', $timekeeper->specialization ?? []) ? 'checked' : '' }}>
                                            <label>Rallyplus</label>
                                        </div>
                                        <div>
                                            <input type="checkbox" name="specialization_of_race[]" value="canoaplus"
                                                {{ in_array('canoaplus', $timekeeper->specialization ?? []) ? 'checked' : '' }}>
                                            <label>Canoaplus</label>
                                        </div>
                                        <div>
                                            <input type="checkbox" name="specialization_of_race[]" value="wicklax"
                                                {{ in_array('wicklax', $timekeeper->specialization ?? []) ? 'checked' : '' }}>
                                            <label>Wicklax</label>
                                        </div>
                                        <div>
                                            <input type="checkbox" name="specialization_of_race[]" value="cuitiplus"
                                                {{ in_array('cuitiplus', $timekeeper->specialization ?? []) ? 'checked' : '' }}>
                                            <label>Cuitiplus</label>
                                        </div>
                                        <div>
                                            <input type="checkbox" name="specialization_of_race[]"
                                                value="orologio_regolarità"
                                                {{ in_array('orologio_regolarità', $timekeeper->specialization ?? []) ? 'checked' : '' }}>
                                            <label>Orologio regolarità</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div>
                                        <input type="checkbox" name="specialization_of_race[]" value="piastre"
                                            {{ in_array('piastre', $timekeeper->specialization ?? []) ? 'checked' : '' }}>
                                        <label class="fw-bold fs-5">Piastre</label>
                                    </div>
                                    <div>
                                        <input type="checkbox" name="specialization_of_race[]" value="trasponder"
                                            {{ in_array('trasponder', $timekeeper->specialization ?? []) ? 'checked' : '' }}>
                                        <label class="fw-bold fs-5">Trasponder</label>
                                    </div>
                                    <div>
                                        <input type="checkbox" name="specialization_of_race[]" value="centro_classifica"
                                            {{ in_array('centro_classifica', $timekeeper->specialization ?? []) ? 'checked' : '' }}>
                                        <label class="fw-bold fs-5">Centro classifica</label>
                                    </div>
                                    <div>
                                        <h5>Cronometri</h5>
                                        <div>
                                            <input type="checkbox" name="specialization_of_race[]" value="master"
                                                {{ in_array('master', $timekeeper->specialization ?? []) ? 'checked' : '' }}>
                                            <label>Master</label>
                                        </div>
                                        <div>
                                            <input type="checkbox" name="specialization_of_race[]" value="rei_pro"
                                                {{ in_array('rei_pro', $timekeeper->specialization ?? []) ? 'checked' : '' }}>
                                            <label>Rei pro</label>
                                        </div>
                                    </div>
                                    <div>
                                        <h5>Apparecchiature si partenza</h5>
                                        <div>
                                            <input type="checkbox" name="specialization_of_race[]" value="susanna"
                                                {{ in_array('susanna', $timekeeper->specialization ?? []) ? 'checked' : '' }}>
                                            <label>Susanna</label>
                                        </div>
                                        <div>
                                            <input type="checkbox" name="specialization_of_race[]" value="rally"
                                                {{ in_array('rally', $timekeeper->specialization ?? []) ? 'checked' : '' }}>
                                            <label>Rally</label>
                                        </div>
                                        <div>
                                            <input type="checkbox" name="specialization_of_race[]"
                                                value="cancelletto_sci"
                                                {{ in_array('cancelletto_sci', $timekeeper->specialization ?? []) ? 'checked' : '' }}>
                                            <label>Cancelletto sci</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div>
                                        <input type="checkbox" name="specialization_of_race[]"
                                            value="apparecchiature_di_arrivo"
                                            {{ in_array('apparecchiature_di_arrivo', $timekeeper->specialization ?? []) ? 'checked' : '' }}>
                                        <label class="fw-bold fs-5">Apparecchiature di arrivo</label>
                                    </div>
                                    <div>
                                        <input type="checkbox" name="specialization_of_race[]" value="tabellone"
                                            {{ in_array('tabellone', $timekeeper->specialization ?? []) ? 'checked' : '' }}>
                                        <label class="fw-bold fs-5">Tabellone</label>
                                    </div>
                                    <div>
                                        <input type="checkbox" name="specialization_of_race[]" value="tablet/smartphone"
                                            {{ in_array('tablet/smartphone', $timekeeper->specialization ?? []) ? 'checked' : '' }}>
                                        <label class="fw-bold fs-5">Tablet/smartphone</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</x-layout>
