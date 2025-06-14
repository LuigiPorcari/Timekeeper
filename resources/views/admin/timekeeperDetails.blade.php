<x-layout documentTitle="Admin Timekeeper Details">
    <div class="container mt-5 pt-5">
        <h1 class="mb-4">Dettagli {{ $timekeeper->name }} {{ $timekeeper->surname }}</h1>

        @if (session('success'))
            <div class="alert alert-dismissible alert-success">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row g-4">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">{{ $timekeeper->name }} {{ $timekeeper->surname }}</h5>
                        <p><strong>Email:</strong> {{ $timekeeper->email }}</p>
                        <p><strong>Data di nascita:</strong> {{ $timekeeper->date_of_birth }}</p>
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
                                {{ $a->date_of_availability }}<br>
                            @empty
                                Nessuna Disponibilità
                            @endforelse
                        </p>
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <h4 class="mb-3">Seleziona disciplina</h4>
                <form action="{{ route('update.timekeeper', $timekeeper) }}" method="POST">
                    @csrf
                    <div class="row g-4">
                        <div class="col-md-4">
                            <h5>Lynx fotofinish</h5>
                            @foreach (['per_atletica', 'per_ciclismo', 'per_pattinaggio'] as $spec)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="specialization[]"
                                        value="{{ $spec }}" id="{{ $spec }}"
                                        {{ in_array($spec, $timekeeper->specialization ?? []) ? 'checked' : '' }}>
                                    <label class="form-check-label"
                                        for="{{ $spec }}">{{ ucwords(str_replace('_', ' ', $spec)) }}</label>
                                </div>
                            @endforeach

                            <h5 class="mt-3">Elaborazione dati</h5>
                            @foreach (['sciplus', 'enduroplus', 'rallyplus', 'canoaplus', 'wicklax', 'cuitiplus', 'orologio_regolarità'] as $spec)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="specialization[]"
                                        value="{{ $spec }}" id="{{ $spec }}"
                                        {{ in_array($spec, $timekeeper->specialization ?? []) ? 'checked' : '' }}>
                                    <label class="form-check-label"
                                        for="{{ $spec }}">{{ ucwords(str_replace('_', ' ', $spec)) }}</label>
                                </div>
                            @endforeach
                        </div>

                        <div class="col-md-4">
                            @foreach (['piastre', 'trasponder', 'centro_classifica'] as $spec)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="specialization[]"
                                        value="{{ $spec }}" id="{{ $spec }}"
                                        {{ in_array($spec, $timekeeper->specialization ?? []) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold fs-5"
                                        for="{{ $spec }}">{{ ucwords(str_replace('_', ' ', $spec)) }}</label>
                                </div>
                            @endforeach

                            <h5 class="mt-3">Cronometri</h5>
                            @foreach (['master', 'rei_pro'] as $spec)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="specialization[]"
                                        value="{{ $spec }}" id="{{ $spec }}"
                                        {{ in_array($spec, $timekeeper->specialization ?? []) ? 'checked' : '' }}>
                                    <label class="form-check-label"
                                        for="{{ $spec }}">{{ ucwords(str_replace('_', ' ', $spec)) }}</label>
                                </div>
                            @endforeach

                            <h5 class="mt-3">Apparecchiature di partenza</h5>
                            @foreach (['susanna', 'rally', 'cancelletto_sci'] as $spec)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="specialization[]"
                                        value="{{ $spec }}" id="{{ $spec }}"
                                        {{ in_array($spec, $timekeeper->specialization ?? []) ? 'checked' : '' }}>
                                    <label class="form-check-label"
                                        for="{{ $spec }}">{{ ucwords(str_replace('_', ' ', $spec)) }}</label>
                                </div>
                            @endforeach
                        </div>

                        <div class="col-md-4">
                            @foreach (['apparecchiature_di_arrivo', 'tabellone', 'tablet/smartphone'] as $spec)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="specialization[]"
                                        value="{{ $spec }}" id="{{ $spec }}"
                                        {{ in_array($spec, $timekeeper->specialization ?? []) ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold fs-5"
                                        for="{{ $spec }}">{{ ucwords(str_replace('_', ' ', $spec)) }}</label>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-primary">Salva</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layout>
