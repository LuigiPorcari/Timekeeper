<x-layout documentTitle="Gestione Report Gara">
    <main class="container mt-5 pt-5">
        <div class="row justify-content-center">
            <div class="col-12 col-xl-11">

                <h1 class="mb-4">
                    Gestione Report per la Gara {{ $race->name }} <br>
                    {{ ucwords(\Carbon\Carbon::parse($race->date_of_race)->translatedFormat('l d F')) }}
                    @if ($race->date_end)
                        / {{ ucwords(\Carbon\Carbon::parse($race->date_end)->translatedFormat('l d F')) }}
                    @endif
                    a {{ $race->place }}
                </h1>

                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Chiudi"></button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Chiudi"></button>
                    </div>
                @endif

                @if ($records->where('confirmed', false)->isNotEmpty() && auth()->user()->isLeaderOf($race))
                    <form method="POST" action="{{ route('records.confirm.all', $race) }}" class="mb-4">
                        @csrf
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-check-double me-1"></i> Conferma Tutti i Record
                        </button>
                    </form>
                @endif

                {{-- CARD: Aggiungi record --}}
                @if ($records->isEmpty() || $records->where('confirmed', false)->isNotEmpty())
                    <div class="card tk-card mb-4">
                        <div class="card-header tk-card-header">
                            <i class="fas fa-plus-circle me-2"></i> Aggiungi record
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('records.store', $race) }}"
                                enctype="multipart/form-data" class="mb-0">
                                @csrf
                                <input type="hidden" name="user_id" value="{{ auth()->id() }}">
                                <input type="hidden" name="race_id" value="{{ $race->id }}">

                                {{-- Tipo + €/Km (€/Km solo DSC) --}}
                                <div class="row g-3 mb-3">
                                    <div class="col-12 col-md-3">
                                        <label for="type" class="form-label">Tipo *</label>
                                        <select id="type" name="type"
                                            class="form-select @error('type') is-invalid @enderror" required>
                                            <option value="" disabled {{ old('type') ? '' : 'selected' }}>
                                                Seleziona…
                                            </option>
                                            <option value="FC" {{ old('type') === 'FC' ? 'selected' : '' }}>FC —
                                                Fuori città</option>
                                            <option value="CM" {{ old('type') === 'CM' ? 'selected' : '' }}>CM —
                                                Fisso</option>
                                            <option value="CP" {{ old('type') === 'CP' ? 'selected' : '' }}>CP —
                                                Fisso</option>
                                        </select>
                                        @error('type')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    @if (auth()->user()->isLeaderOf($race))
                                        <div class="col-12 col-md-3">
                                            <label for="euroKM" class="form-label">€/Km</label>
                                            <input type="text" id="euroKM" name="euroKM" inputmode="decimal"
                                                pattern="^\d{1,6}([,.]\d{1,2})?$"
                                                class="form-control @error('euroKM') is-invalid @enderror"
                                                value="{{ old('euroKM') }}" placeholder="es. 0,36">
                                            <small class="text-muted">Puoi usare virgola o punto (max 2
                                                decimali).</small>
                                            @error('euroKM')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    @endif

                                    {{-- Trasporto: solo DSC --}}
                                    @if (auth()->user()->isLeaderOf($race))
                                        <div class="col-12 col-md-6">
                                            <label class="form-label d-block">Trasporto *</label>
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="transport_mode"
                                                        id="tm_trasp" value="trasportato"
                                                        {{ old('transport_mode', 'km') === 'trasportato' ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="tm_trasp">Trasportato</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="transport_mode"
                                                        id="tm_km" value="km"
                                                        {{ old('transport_mode', 'km') === 'km' ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="tm_km">Numero km</label>
                                                </div>

                                                <div class="ms-3" id="kmBox">
                                                    <label for="km_documented" class="form-label mb-0 me-2">Km</label>
                                                    <input type="number" step="any" name="km_documented"
                                                        id="km_documented" class="form-control d-inline-block"
                                                        style="max-width: 140px" value="{{ old('km_documented') }}">
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped table-vertical-separators mb-3">
                                        <thead class="table-light">
                                            <tr>
                                                <th rowspan="2">Servizio Giornaliero</th>
                                                <th rowspan="2">Servizio Speciale</th>
                                                <th rowspan="2">Tariffa</th>
                                                <th colspan="4" class="text-center">Spese Documentate</th>
                                                @if (auth()->user()->isLeaderOf($race))
                                                    <th colspan="3" class="text-center">Spese NON Documentate</th>
                                                @endif
                                            </tr>
                                            <tr>
                                                <th>Biglietto</th>
                                                <th>Vitto</th>
                                                <th>Alloggio</th>
                                                <th>Varie</th>
                                                @if (auth()->user()->isLeaderOf($race))
                                                    <th>Vitto</th>
                                                    <th>Diaria</th>
                                                    <th>Diaria Speciale</th>
                                                @endif
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                {{-- SG / SS / Tariffa --}}
                                                <td><input type="number" step="1" name="daily_service"
                                                        class="form-control" value="{{ old('daily_service') }}"></td>
                                                <td><input type="number" step="1" name="special_service"
                                                        class="form-control" value="{{ old('special_service') }}">
                                                </td>
                                                <td><input type="text" name="rate_documented" class="form-control"
                                                        value="{{ old('rate_documented') }}"></td>

                                                {{-- Documentate --}}
                                                <td><input type="number" step="any"
                                                        name="travel_ticket_documented" class="form-control"
                                                        value="{{ old('travel_ticket_documented') }}"></td>
                                                <td><input type="number" step="any" name="food_documented"
                                                        class="form-control" value="{{ old('food_documented') }}">
                                                </td>
                                                <td><input type="number" step="any"
                                                        name="accommodation_documented" class="form-control"
                                                        value="{{ old('accommodation_documented') }}"></td>
                                                <td><input type="number" step="any" name="various_documented"
                                                        class="form-control" value="{{ old('various_documented') }}">
                                                </td>

                                                {{-- NON Documentate (solo DSC) --}}
                                                @if (auth()->user()->isLeaderOf($race))
                                                    <td><input type="number" step="any"
                                                            name="food_not_documented" class="form-control"
                                                            value="{{ old('food_not_documented') }}"></td>
                                                    <td><input type="number" step="any"
                                                            name="daily_allowances_not_documented"
                                                            class="form-control"
                                                            value="{{ old('daily_allowances_not_documented') }}"></td>
                                                    <td><input type="number" step="any"
                                                            name="special_daily_allowances_not_documented"
                                                            class="form-control"
                                                            value="{{ old('special_daily_allowances_not_documented') }}">
                                                    </td>
                                                @endif
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                @php
                                    // Mappa slug namespacizzato "tipo__spec" → label umano della specializzazione
                                    $typesMap = config('races.types', []);
                                    $nsToLabel = [];

                                    foreach ($typesMap as $typeLabel => $equipList) {
                                        $typeSlug = \Illuminate\Support\Str::slug($typeLabel);
                                        foreach ($equipList as $lab) {
                                            if (!filled($lab)) {
                                                continue;
                                            }
                                            $equipSlug = \Illuminate\Support\Str::slug($lab);
                                            $ns = $typeSlug . '__' . $equipSlug;
                                            $nsToLabel[$ns] = $lab;
                                        }
                                    }

                                    // Helper per mostrare SOLO il nome della specializzazione (senza tipo)
                                    $prettyApp = function ($ns) use ($nsToLabel) {
                                        if (isset($nsToLabel[$ns])) {
                                            return $nsToLabel[$ns];
                                        }
                                        // fallback: prendo la parte dopo "__" e tolgo slug
                                        if (is_string($ns) && str_contains($ns, '__')) {
                                            $parts = explode('__', $ns, 2);
                                            $ns = $parts[1];
                                        }
                                        $ns = str_replace(['_', '-'], ' ', (string) $ns);
                                        return ucwords($ns);
                                    };

                                    $specs = is_array($race->specialization_of_race)
                                        ? $race->specialization_of_race
                                        : [];
                                @endphp

                                {{-- Apparecchiature (solo DSC) --}}
                                @if (auth()->user()->isLeaderOf($race) && !empty($specs))
                                    <div class="mb-3">
                                        <label class="form-label">Apparecchiature usate in gara</label>
                                        <div class="row g-2">
                                            @foreach ($specs as $spec)
                                                <div class="col-12 col-sm-6 col-md-4">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox"
                                                            name="apparecchiature[]" id="app_{{ $spec }}"
                                                            value="{{ $spec }}"
                                                            {{ in_array($spec, old('apparecchiature', []), true) ? 'checked' : '' }}>
                                                        <label class="form-check-label"
                                                            for="app_{{ $spec }}">
                                                            {{ $prettyApp($spec) }}
                                                        </label>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                <div class="mb-3">
                                    <label for="description" class="form-label">Note</label>
                                    <textarea name="description" id="description" class="form-control" rows="3">{{ old('description') }}</textarea>
                                </div>

                                <div class="mb-3">
                                    <label for="attachments" class="form-label">Allegati (facoltativi)</label>
                                    <input type="file" name="attachments[]" id="attachments" class="form-control"
                                        multiple>
                                </div>

                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i> Aggiungi Record
                                </button>
                            </form>
                        </div>
                    </div>
                @endif

                {{-- CARD: tabella record --}}
                <div class="card tk-card p-3">
                    <div class="card-header tk-card-header">
                        <i class="fas fa-list-ul me-2"></i> Record inseriti
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped align-middle mb-0 table-vertical-separators">
                                <thead class="table-light">
                                    <tr>
                                        <th rowspan="2">Crono</th>
                                        <th rowspan="2">Tipo</th>
                                        <th rowspan="2">Trasporto</th>
                                        <th rowspan="2">€/Km</th>
                                        <th rowspan="2">Km (eff.)</th>
                                        <th rowspan="2">Imp. Km</th>
                                        <th rowspan="2">Servizio Giornaliero</th>
                                        <th rowspan="2">Servizio Speciale</th>
                                        <th colspan="4" class="text-center">Spese Documentate</th>
                                        <th colspan="3" class="text-center">Spese NON Documentate</th>
                                        <th rowspan="2">Apparecchiature</th>
                                        <th rowspan="2">Totale</th>
                                        <th rowspan="2">Note</th>
                                        <th rowspan="2">Allegati</th>
                                    </tr>
                                    <tr>
                                        <th>Bigl.</th>
                                        <th>Vitto</th>
                                        <th>Alloggio</th>
                                        <th>Varie</th>
                                        <th>Vitto</th>
                                        <th>Diaria</th>
                                        <th>Diaria Spec.</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($records as $record)
                                        @php
                                            $user = auth()->user();
                                            $isLeader = $user->isLeaderOf($race);
                                            $isOwner = $user->id === $record->user_id;
                                            $canSee = $isOwner || $isLeader;

                                            $ratePerKm = $record->euroKM !== null ? (float) $record->euroKM : 0.36;
                                            $kmEff = (float) ($record->km_documented ?? 0);
                                            $amount = round($kmEff * $ratePerKm, 2);

                                            // usa lo stesso helper definito sopra ($prettyApp)
                                            $apps = array_map($prettyApp, $record->apparecchiature ?? []);
                                            $appsLabel = $apps ? implode(', ', $apps) : '—';
                                        @endphp

                                        @if ($canSee)
                                            <tr>
                                                <td>{{ $record->user->name }} {{ $record->user->surname }}</td>
                                                <td>{{ $record->type ?? '—' }}</td>
                                                <td>{{ $record->transport_mode === 'trasportato' ? 'Trasportato' : 'Km' }}
                                                </td>
                                                <td>{{ number_format($ratePerKm, 2) }}</td>
                                                <td>{{ $kmEff }}</td>
                                                <td>{{ number_format($amount, 2) }}</td>

                                                {{-- Servizi --}}
                                                <td>{{ $record->daily_service }}</td>
                                                <td>{{ $record->special_service }}</td>

                                                {{-- Spese Documentate --}}
                                                <td>{{ $record->travel_ticket_documented }}</td>
                                                <td>{{ $record->food_documented }}</td>
                                                <td>{{ $record->accommodation_documented }}</td>
                                                <td>{{ $record->various_documented }}</td>

                                                {{-- Spese NON Documentate --}}
                                                <td>{{ $record->food_not_documented }}</td>
                                                <td>{{ $record->daily_allowances_not_documented }}</td>
                                                <td>{{ $record->special_daily_allowances_not_documented }}</td>

                                                <td>{{ $appsLabel }}</td>
                                                <td><strong>{{ number_format($record->total, 2) }}</strong></td>
                                                <td>{{ $record->description }}</td>
                                                <td>
                                                    @if ($record->attachments && $record->attachments->count())
                                                        <ul class="list-unstyled mb-0">
                                                            @foreach ($record->attachments as $attachment)
                                                                <li>
                                                                    <a href="{{ route('attachments.show', $attachment) }}"
                                                                        target="_blank">
                                                                        {{ $attachment->original_name }}
                                                                    </a>
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    @else
                                                        <span class="text-muted">—</span>
                                                    @endif
                                                </td>
                                            </tr>

                                            {{-- Riga azioni --}}
                                            <tr class="bg-light">
                                                <td colspan="19">
                                                    <div class="d-flex flex-wrap justify-content-end gap-2">
                                                        @if (auth()->user()->isLeaderOf($race) && !$record->confirmed)
                                                            <form method="POST"
                                                                action="{{ route('records.confirm', $record) }}"
                                                                class="d-inline">
                                                                @csrf
                                                                <button type="submit" class="btn btn-warning btn-sm"
                                                                    onclick="return confirm('Confermare questo record?');">
                                                                    <i class="fas fa-check me-1"></i> Conferma
                                                                </button>
                                                            </form>
                                                        @endif

                                                        @php
                                                            $canEdit =
                                                                auth()->id() === $record->user_id &&
                                                                !$record->confirmed;
                                                        @endphp

                                                        @if ($canEdit)
                                                            <a href="{{ route('records.edit', $record) }}"
                                                                class="btn btn-secondary btn-sm">
                                                                <i class="fas fa-pen me-1"></i> Modifica
                                                            </a>
                                                            <form action="{{ route('records.destroy', $record) }}"
                                                                method="POST" class="d-inline">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-danger btn-sm">
                                                                    <i class="fas fa-trash me-1"></i> Elimina
                                                                </button>
                                                            </form>
                                                        @elseif ($record->confirmed)
                                                            <span class="badge bg-success align-self-center">
                                                                <i class="fas fa-check-circle me-1"></i> Confermato
                                                            </span>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </main>

    {{-- Toggle km box in base al transport_mode (solo DSC) --}}
    @if (auth()->user()->isLeaderOf($race))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const tmKm = document.getElementById('tm_km');
                const tmTr = document.getElementById('tm_trasp');
                const box = document.getElementById('kmBox');

                function toggle() {
                    box.style.display = (tmKm && tmKm.checked) ? 'block' : 'none';
                }
                if (tmKm) tmKm.addEventListener('change', toggle);
                if (tmTr) tmTr.addEventListener('change', toggle);
                toggle();
            });
        </script>
    @endif
</x-layout>
