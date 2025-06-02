<x-layout documentTitle="Admin Timekeeper Details">
    <div class="mt-5">
        <h1 class="mt-5 pt-5">Dettagli {{ $timekeeper->name }} {{ $timekeeper->surname }}</h1>
    </div>
    @if (session('success'))
        <div class="alert alert-dismissible alert-success">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    <div class="container">
        <div class="row">
            <div class="col-4">
                <div class="card" style="width: 18rem;">
                    <div class="card-body">
                        <h5 class="card-title">{{ $timekeeper->name }} {{ $timekeeper->surname }}</h5>
                        <p class="card-text"><strong>Email: </strong> {{ $timekeeper->email }}</p>
                        <p class="card-text"><strong>Data di nascita: </strong> {{ $timekeeper->date_of_birth }}</p>
                        @if ($timekeeper->residence != null)
                            <p class="card-text"><strong>Residenza: </strong> {{ $timekeeper->residence }}</p>
                        @endif
                        <p class="card-text"><strong>Domicilio: </strong> {{ $timekeeper->domicile }}</p>
                        <p class="card-text"><strong>Transferta: </strong>
                            @if ($timekeeper->transfer == 'no')
                                NO
                            @elseif($timekeeper->transfer == '1')
                                1 notte
                            @elseif($timekeeper->transfer == '2/5')
                                tra 2 e 5 notti
                            @elseif($timekeeper->transfer == '>5')
                                più di 5 notti
                            @endif
                        </p>
                        <p class="card-text"><strong>Automunito: </strong>
                            @if ($timekeeper->auto)
                                SI
                            @else
                                NO
                            @endif
                        </p>
                        <p class="card-text"><strong>Specializzazione: </strong>
                            <br>
                            @if ($timekeeper->specialization == null)
                                Cronometrista generico
                            @else
                                @foreach ($timekeeper->specialization as $specialization)
                                    {{ $specialization }}<br>
                                @endforeach
                            @endif
                        </p>
                        <p class="card-text"><strong>Disponibilità: </strong>
                            @forelse ($timekeeper->availabilities as $availabily)
                                {{ $availabily->date_of_availability }}
                            @empty
                                Nessuna Disponibilità
                            @endforelse
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-8">
                <h4>Seleziona disciplina</h4>
                <form action="{{ route('update.timekeeper', $timekeeper) }}" method="POST">
                    @csrf
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-4">
                                <div>
                                    <h5>Lynx fotofinish</h5>
                                    <div>
                                        <input type="checkbox" name="specialization[]" value="per_atletica"
                                            {{ in_array('per_atletica', $timekeeper->specialization ?? []) ? 'checked' : '' }}>
                                        <label>Per atletica</label>
                                    </div>
                                    <div>
                                        <input type="checkbox" name="specialization[]" value="per_ciclismo"
                                            {{ in_array('per_ciclismo', $timekeeper->specialization ?? []) ? 'checked' : '' }}>
                                        <label>Per ciclismo</label>
                                    </div>
                                    <div>
                                        <input type="checkbox" name="specialization[]" value="per_pattinaggio"
                                            {{ in_array('per_pattinaggio', $timekeeper->specialization ?? []) ? 'checked' : '' }}>
                                        <label>Per pattinaggio</label>
                                    </div>
                                </div>
                                <div>
                                    <h5>Elaborazione dati</h5>
                                    <div>
                                        <input type="checkbox" name="specialization[]" value="sciplus"
                                            {{ in_array('sciplus', $timekeeper->specialization ?? []) ? 'checked' : '' }}>
                                        <label>Sciplus</label>
                                    </div>
                                    <div>
                                        <input type="checkbox" name="specialization[]" value="enduroplus"
                                            {{ in_array('enduroplus', $timekeeper->specialization ?? []) ? 'checked' : '' }}>
                                        <label>Enduroplus</label>
                                    </div>
                                    <div>
                                        <input type="checkbox" name="specialization[]" value="rallyplus"
                                            {{ in_array('rallyplus', $timekeeper->specialization ?? []) ? 'checked' : '' }}>
                                        <label>Rallyplus</label>
                                    </div>
                                    <div>
                                        <input type="checkbox" name="specialization[]" value="canoaplus"
                                            {{ in_array('canoaplus', $timekeeper->specialization ?? []) ? 'checked' : '' }}>
                                        <label>Canoaplus</label>
                                    </div>
                                    <div>
                                        <input type="checkbox" name="specialization[]" value="wicklax"
                                            {{ in_array('wicklax', $timekeeper->specialization ?? []) ? 'checked' : '' }}>
                                        <label>Wicklax</label>
                                    </div>
                                    <div>
                                        <input type="checkbox" name="specialization[]" value="cuitiplus"
                                            {{ in_array('cuitiplus', $timekeeper->specialization ?? []) ? 'checked' : '' }}>
                                        <label>Cuitiplus</label>
                                    </div>
                                    <div>
                                        <input type="checkbox" name="specialization[]" value="orologio_regolarità"
                                            {{ in_array('orologio_regolarità', $timekeeper->specialization ?? []) ? 'checked' : '' }}>
                                        <label>Orologio regolarità</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div>
                                    <input type="checkbox" name="specialization[]" value="piastre"
                                        {{ in_array('piastre', $timekeeper->specialization ?? []) ? 'checked' : '' }}>
                                    <label class="fw-bold fs-5">Piastre</label>
                                </div>
                                <div>
                                    <input type="checkbox" name="specialization[]" value="trasponder"
                                        {{ in_array('trasponder', $timekeeper->specialization ?? []) ? 'checked' : '' }}>
                                    <label class="fw-bold fs-5">Trasponder</label>
                                </div>
                                <div>
                                    <input type="checkbox" name="specialization[]" value="centro_classifica"
                                        {{ in_array('centro_classifica', $timekeeper->specialization ?? []) ? 'checked' : '' }}>
                                    <label class="fw-bold fs-5">Centro classifica</label>
                                </div>
                                <div>
                                    <h5>Cronometri</h5>
                                    <div>
                                        <input type="checkbox" name="specialization[]" value="master"
                                            {{ in_array('master', $timekeeper->specialization ?? []) ? 'checked' : '' }}>
                                        <label>Master</label>
                                    </div>
                                    <div>
                                        <input type="checkbox" name="specialization[]" value="rei_pro"
                                            {{ in_array('rei_pro', $timekeeper->specialization ?? []) ? 'checked' : '' }}>
                                        <label>Rei pro</label>
                                    </div>
                                </div>
                                <div>
                                    <h5>Apparecchiature si partenza</h5>
                                    <div>
                                        <input type="checkbox" name="specialization[]" value="susanna"
                                            {{ in_array('susanna', $timekeeper->specialization ?? []) ? 'checked' : '' }}>
                                        <label>Susanna</label>
                                    </div>
                                    <div>
                                        <input type="checkbox" name="specialization[]" value="rally"
                                            {{ in_array('rally', $timekeeper->specialization ?? []) ? 'checked' : '' }}>
                                        <label>Rally</label>
                                    </div>
                                    <div>
                                        <input type="checkbox" name="specialization[]" value="cancelletto_sci"
                                            {{ in_array('cancelletto_sci', $timekeeper->specialization ?? []) ? 'checked' : '' }}>
                                        <label>Cancelletto sci</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div>
                                    <input type="checkbox" name="specialization[]" value="apparecchiature_di_arrivo"
                                        {{ in_array('apparecchiature_di_arrivo', $timekeeper->specialization ?? []) ? 'checked' : '' }}>
                                    <label class="fw-bold fs-5">Apparecchiature di arrivo</label>
                                </div>
                                <div>
                                    <input type="checkbox" name="specialization[]" value="tabellone"
                                        {{ in_array('tabellone', $timekeeper->specialization ?? []) ? 'checked' : '' }}>
                                    <label class="fw-bold fs-5">Tabellone</label>
                                </div>
                                <div>
                                    <input type="checkbox" name="specialization[]" value="tablet/smartphone"
                                        {{ in_array('tablet/smartphone', $timekeeper->specialization ?? []) ? 'checked' : '' }}>
                                    <label class="fw-bold fs-5">Tablet/smartphone</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <button type="submit">Salva</button>
                </form>
            </div>
        </div>
    </div>
</x-layout>
