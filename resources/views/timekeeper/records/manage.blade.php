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

                @php
                    $specs = is_array($race->specialization_of_race) ? $race->specialization_of_race : [];

                    $myEntry = $entries->get(auth()->id());
                    $hasEntry = $myEntry && ($myEntry->exists ?? false);

                    $dsc = $dscRace;

                    $hasUnconfirmed = false;
                    foreach ($rows as $r) {
                        if (!($r['entry']->confirmed ?? false) && ($r['entry']->exists ?? false)) {
                            $hasUnconfirmed = true;
                            break;
                        }
                    }

                    // Per DSC: nel riepilogo voglio SOLO gli altri crono
                    $rowsForSummary = $rows;
                    if ($isLeader) {
                        $rowsForSummary = collect($rows)->reject(function ($r) {
                            return (int) ($r['user']->id ?? 0) === (int) auth()->id();
                        });
                    }

                    // Dati FULL (solo se passati dal controller)
                    // (vedi modifica controller sotto)
                    $hasFullData = isset($fullRows, $fullDays) && is_iterable($fullRows) && is_array($fullDays);
                @endphp

                {{-- ============================================================
                    CARD: CRONO (sempre visibile) - 1 volta per gara
                ============================================================ --}}
                <div class="card tk-card mb-4">
                    @php
                        $lockedEntry =
                            $myEntry && (($myEntry->confirmed ?? false) || ($myEntry->secretariat_confirmed ?? false));
                    @endphp

                    <div class="card-header tk-card-header d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fas fa-pen me-2"></i> Report Crono (una volta per gara)
                        </div>

                        <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse"
                            data-bs-target="#collapseCrono" aria-expanded="true" aria-controls="collapseCrono">
                            Mostra / Nascondi
                        </button>
                    </div>

                    <div id="collapseCrono" class="collapse show">
                        <div class="card-body">
                            <form method="POST" action="{{ route('records.entry.save', $race) }}"
                                enctype="multipart/form-data">
                                @csrf

                                <div class="row g-3">
                                    <div class="col-12 col-md-2">
                                        <label class="form-label">Km</label>
                                        <input type="number" step="0.01" name="km"
                                            class="form-control @error('km') is-invalid @enderror"
                                            value="{{ old('km', $myEntry->km ?? null) }}"
                                            {{ $lockedEntry ? 'disabled' : '' }}>
                                        @error('km')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-12 col-md-2">
                                        <label class="form-label">Pedaggi / Trasporto</label>
                                        <input type="number" step="0.01" name="pedaggi"
                                            class="form-control @error('pedaggi') is-invalid @enderror"
                                            value="{{ old('pedaggi', $myEntry->pedaggi ?? null) }}"
                                            {{ $lockedEntry ? 'disabled' : '' }}>
                                        @error('pedaggi')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    @php
                                        $vittoTipo = old('vitto_tipo');

                                        if ($vittoTipo === null) {
                                            if (!$myEntry || $myEntry->vitto === null) {
                                                $vittoTipo = '';
                                            } elseif ((float) $myEntry->vitto === 15.0) {
                                                $vittoTipo = 'forfettario';
                                            } elseif ((float) $myEntry->vitto === 0.0) {
                                                $vittoTipo = 'offerto';
                                            } else {
                                                $vittoTipo = 'documentato';
                                            }
                                        }

                                        $vittoDocumentato = old('vitto_documentato');

                                        if ($vittoDocumentato === null && $vittoTipo === 'documentato') {
                                            $vittoDocumentato = $myEntry->vitto ?? null;
                                        }
                                    @endphp

                                    <div class="col-12 col-md-2">
                                        <label class="form-label">Vitto</label>
                                        <select name="vitto_tipo"
                                            class="form-select js-vitto-tipo @error('vitto_tipo') is-invalid @enderror"
                                            {{ $lockedEntry ? 'disabled' : '' }}>
                                            <option value="">-- Seleziona --</option>
                                            <option value="forfettario"
                                                {{ $vittoTipo === 'forfettario' ? 'selected' : '' }}>
                                                Forfettario - 15€
                                            </option>
                                            <option value="offerto" {{ $vittoTipo === 'offerto' ? 'selected' : '' }}>
                                                Offerto - 0€
                                            </option>
                                            <option value="documentato"
                                                {{ $vittoTipo === 'documentato' ? 'selected' : '' }}>
                                                Documentato
                                            </option>
                                        </select>
                                        @error('vitto_tipo')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-12 col-md-2 js-vitto-documentato-wrap" style="display: none;">
                                        <label class="form-label">Importo vitto</label>
                                        <input type="number" step="0.01" min="0" name="vitto_documentato"
                                            class="form-control js-vitto-documentato @error('vitto_documentato') is-invalid @enderror"
                                            value="{{ $vittoDocumentato }}" {{ $lockedEntry ? 'disabled' : '' }}>
                                        @error('vitto_documentato')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-12 col-md-2">
                                        <label class="form-label">Spese varie</label>
                                        <input type="number" step="0.01" name="spese_varie"
                                            class="form-control @error('spese_varie') is-invalid @enderror"
                                            value="{{ old('spese_varie', $myEntry->spese_varie ?? null) }}"
                                            {{ $lockedEntry ? 'disabled' : '' }}>
                                        @error('spese_varie')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-12 col-md-4">
                                        <label class="form-label">Note spese varie</label>
                                        <input type="text" name="spese_varie_note"
                                            class="form-control @error('spese_varie_note') is-invalid @enderror"
                                            value="{{ old('spese_varie_note', $myEntry->spese_varie_note ?? null) }}"
                                            placeholder="Es. parcheggio, taxi, materiale..."
                                            {{ $lockedEntry ? 'disabled' : '' }}>
                                        @error('spese_varie_note')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="col-12 col-md-2">
                                        <label class="form-label">Allegati</label>
                                        <input type="file" name="attachments[]" class="form-control" multiple
                                            {{ $lockedEntry ? 'disabled' : '' }}>
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label">Note</label>
                                        <textarea name="note" class="form-control" rows="2" {{ $lockedEntry ? 'disabled' : '' }}>{{ old('note', $myEntry->note ?? '') }}</textarea>
                                    </div>
                                </div>

                                <div class="d-flex gap-2 mt-3">
                                    @if (!$lockedEntry)
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-1"></i> {{ $hasEntry ? 'Modifica' : 'Salva' }}
                                            Report Crono
                                        </button>
                                    @else
                                        @if ($myEntry && ($myEntry->secretariat_confirmed ?? false))
                                            <span class="badge bg-success align-self-center">
                                                <i class="fas fa-lock me-1"></i> Report chiuso dalla segreteria
                                            </span>
                                        @else
                                            <span class="badge bg-success align-self-center">
                                                <i class="fas fa-check-circle me-1"></i> Report Crono confermato
                                            </span>
                                        @endif
                                    @endif
                                    {{-- @if (!$isLeader && $myEntry && ($myEntry->exists ?? false) && !($myEntry->confirmed ?? false))
                                        <form method="POST" action="{{ route('records.entry.delete', $race) }}"
                                            onsubmit="return confirm('Eliminare il tuo report per questa gara?');">
                                            @csrf
                                            <input type="hidden" name="day"
                                                value="{{ $selectedDay ?? request('day') }}">

                                            <button type="submit" class="btn btn-danger">
                                                <i class="fas fa-trash me-1"></i> Elimina il mio report
                                            </button>
                                        </form>
                                    @endif --}}
                                </div>
                            </form>

                            @if ($isLeader)
                                <div class="mt-3">
                                    <form method="POST" action="{{ route('records.entry.confirm', $race) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-success"
                                            onclick="return confirm('Confermare il Report Crono? Dopo non potrai più modificarlo.');"
                                            {{ $myEntry && ($myEntry->exists ?? false) && !$lockedEntry ? '' : 'disabled' }}>
                                            <i class="fas fa-check me-1"></i> Conferma Report Crono
                                        </button>
                                    </form>
                                </div>
                            @endif

                            @if ($myEntry && $myEntry->attachments && $myEntry->attachments->count())
                                <hr>
                                <h5 class="mb-2">Allegati già caricati</h5>
                                <ul class="mb-0">
                                    @foreach ($myEntry->attachments as $a)
                                        <li>
                                            <a href="{{ asset('storage/' . $a->file_path) }}" target="_blank"
                                                rel="noopener">
                                                {{ $a->original_name }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- ============================================================
                    DSC (solo leader): una volta per gara + orari giornalieri
                ============================================================ --}}
                @if ($isLeader)
                    @php
                        $lockedDsc = $dsc && ($dsc->confirmed ?? false);

                        $missedMealsDetail = old('missed_meals_detail');

                        if ($missedMealsDetail === null) {
                            $missedMealsDetail = $dsc->missed_meals_detail ?? [];
                        }

                        if (is_string($missedMealsDetail)) {
                            $decodedMeals = json_decode($missedMealsDetail, true);
                            $missedMealsDetail =
                                json_last_error() === JSON_ERROR_NONE && is_array($decodedMeals) ? $decodedMeals : [];
                        }

                        $mealIsChecked = function ($userId, string $meal) use ($missedMealsDetail) {
                            return !empty($missedMealsDetail[$userId][$meal]) ||
                                !empty($missedMealsDetail[(string) $userId][$meal]);
                        };
                    @endphp

                    {{-- DSC GARA --}}
                    <div class="card tk-card mb-4">
                        <div class="card-header tk-card-header">
                            <i class="fas fa-user-shield me-2"></i> Dati DSC (una volta per gara, validi per tutti)
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('records.dscRace.save', $race) }}">
                                @csrf

                                <div class="row g-3">
                                    <div class="col-12 col-md-3">
                                        <label class="form-label">Furgone</label>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="van_needed"
                                                value="1" id="van_needed"
                                                {{ old('van_needed', $dsc->van_needed ?? false) ? 'checked' : '' }}
                                                {{ $lockedDsc ? 'disabled' : '' }}>
                                            <label class="form-check-label" for="van_needed">Serve furgone</label>
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <label class="form-label">Mancati pasti</label>

                                        <div class="table-responsive">
                                            <table class="table table-sm align-middle mb-0">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Cronometrista</th>
                                                        <th class="text-center">Pranzo</th>
                                                        <th class="text-center">Cena</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse ($timekeepers as $timekeeper)
                                                        <tr>
                                                            <td>
                                                                {{ $timekeeper->name }} {{ $timekeeper->surname }}
                                                            </td>
                                                            <td class="text-center">
                                                                <input class="form-check-input" type="checkbox"
                                                                    name="missed_meals_detail[{{ $timekeeper->id }}][pranzo]"
                                                                    id="missed_meal_{{ $timekeeper->id }}_pranzo"
                                                                    value="1"
                                                                    {{ $mealIsChecked($timekeeper->id, 'pranzo') ? 'checked' : '' }}
                                                                    {{ $lockedDsc ? 'disabled' : '' }}>
                                                                <label class="visually-hidden"
                                                                    for="missed_meal_{{ $timekeeper->id }}_pranzo">
                                                                    Pranzo mancato per {{ $timekeeper->name }}
                                                                    {{ $timekeeper->surname }}
                                                                </label>
                                                            </td>
                                                            <td class="text-center">
                                                                <input class="form-check-input" type="checkbox"
                                                                    name="missed_meals_detail[{{ $timekeeper->id }}][cena]"
                                                                    id="missed_meal_{{ $timekeeper->id }}_cena"
                                                                    value="1"
                                                                    {{ $mealIsChecked($timekeeper->id, 'cena') ? 'checked' : '' }}
                                                                    {{ $lockedDsc ? 'disabled' : '' }}>
                                                                <label class="visually-hidden"
                                                                    for="missed_meal_{{ $timekeeper->id }}_cena">
                                                                    Cena mancata per {{ $timekeeper->name }}
                                                                    {{ $timekeeper->surname }}
                                                                </label>
                                                            </td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="3" class="text-muted">
                                                                Nessun cronometrista assegnato alla gara.
                                                            </td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>

                                        <div class="form-text">
                                            Ogni spunta vale un pasto mancato. Puoi selezionare sia pranzo che cena per
                                            lo stesso cronometrista.
                                        </div>
                                    </div>

                                    @if (!empty($specs))
                                        <div class="col-12">
                                            <label class="form-label">Apparecchiature (gara)</label>
                                            <div class="row g-2">
                                                @foreach ($specs as $spec)
                                                    <div class="col-12 col-sm-6 col-md-4">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox"
                                                                name="apparecchiature[]" id="app_{{ $spec }}"
                                                                value="{{ $spec }}"
                                                                {{ in_array($spec, old('apparecchiature', $dsc->apparecchiature ?? []), true) ? 'checked' : '' }}
                                                                {{ $lockedDsc ? 'disabled' : '' }}>
                                                            <label class="form-check-label"
                                                                for="app_{{ $spec }}">
                                                                {{ $spec }}
                                                            </label>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                <div class="d-flex gap-2 mt-3">
                                    @if (!$lockedDsc)
                                        <button type="submit" class="btn btn-warning">
                                            <i class="fas fa-save me-1"></i> {{ $dsc ? 'Modifica' : 'Salva' }} Dati
                                            DSC
                                        </button>
                                    @else
                                        <span class="badge bg-success align-self-center">
                                            <i class="fas fa-check-circle me-1"></i> DSC confermato per la gara
                                        </span>
                                    @endif
                                </div>
                            </form>

                            <div class="mt-3">
                                <form method="POST" action="{{ route('records.dscRace.confirm', $race) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-success"
                                        onclick="return confirm('Confermare i dati DSC per l\'intera gara?');"
                                        {{ $dsc && !$dsc->confirmed ? '' : 'disabled' }}>
                                        <i class="fas fa-check me-1"></i> Conferma DSC (gara)
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    {{-- DSC ORARI GIORNALIERI --}}
                    <div class="card tk-card mb-4">
                        <div class="card-header tk-card-header">
                            <i class="fas fa-clock me-2"></i> Orari DSC (per giornata) + Selezione cronometristi
                        </div>

                        <div class="card-body">
                            <form method="GET" class="mb-3">
                                <label for="day" class="form-label">Seleziona giornata</label>
                                <select id="day" name="day" class="form-select"
                                    onchange="this.form.submit()">
                                    @foreach ($days as $day)
                                        <option value="{{ $day }}"
                                            {{ $selectedDay === $day ? 'selected' : '' }}>
                                            {{ ucwords(\Carbon\Carbon::parse($day)->translatedFormat('l d F')) }}
                                        </option>
                                    @endforeach
                                </select>
                            </form>

                            @php
                                // $dscDayHours può essere null se non ci sono righe per nessun crono in quel giorno
                                // $selectedDayTimekeepers è un array di user_id selezionati per quel giorno
                                $lockedHours = $lockedHours ?? false;
                            @endphp

                            <form method="POST" action="{{ route('records.dscDayHours.save', $race) }}">
                                @csrf
                                <input type="hidden" name="day" value="{{ $selectedDay }}">

                                <div class="row g-3">

                                    {{-- Selezione crono per la giornata --}}
                                    <div class="col-12">
                                        <label class="form-label">Cronometristi che lavorano in questa giornata</label>

                                        <div class="row g-2">
                                            @foreach ($timekeepers as $tk)
                                                <div class="col-12 col-sm-6 col-lg-4">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox"
                                                            name="timekeepers[]" id="tk_{{ $tk->id }}"
                                                            value="{{ $tk->id }}"
                                                            {{ in_array($tk->id, old('timekeepers', $selectedDayTimekeepers ?? [])) ? 'checked' : '' }}
                                                            {{ $lockedHours ? 'disabled' : '' }}>
                                                        <label class="form-check-label" for="tk_{{ $tk->id }}">
                                                            {{ $tk->surname }} {{ $tk->name }}
                                                        </label>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>

                                        @error('timekeepers')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror
                                        @error('timekeepers.*')
                                            <div class="text-danger small mt-1">{{ $message }}</div>
                                        @enderror

                                        <div class="form-text">
                                            Se non selezioni nessuno, nessun crono avrà ore per questa giornata (ore=0).
                                        </div>
                                    </div>

                                    {{-- Orari giornata --}}
                                    <div class="col-6 col-md-3">
                                        <label class="form-label">Ora inizio mattina</label>
                                        <input type="time" name="morning_start" class="form-control"
                                            value="{{ old('morning_start', $dscDayHours->morning_start ?? '') }}"
                                            {{ $lockedHours ? 'disabled' : '' }}>
                                    </div>

                                    <div class="col-6 col-md-3">
                                        <label class="form-label">Ora fine mattina</label>
                                        <input type="time" name="morning_end" class="form-control"
                                            value="{{ old('morning_end', $dscDayHours->morning_end ?? '') }}"
                                            {{ $lockedHours ? 'disabled' : '' }}>
                                    </div>

                                    <div class="col-6 col-md-3">
                                        <label class="form-label">Ora inizio pomeriggio</label>
                                        <input type="time" name="afternoon_start" class="form-control"
                                            value="{{ old('afternoon_start', $dscDayHours->afternoon_start ?? '') }}"
                                            {{ $lockedHours ? 'disabled' : '' }}>
                                    </div>

                                    <div class="col-6 col-md-3">
                                        <label class="form-label">Ora fine pomeriggio</label>
                                        <input type="time" name="afternoon_end" class="form-control"
                                            value="{{ old('afternoon_end', $dscDayHours->afternoon_end ?? '') }}"
                                            {{ $lockedHours ? 'disabled' : '' }}>
                                    </div>
                                </div>

                                <div class="d-flex gap-2 mt-3">
                                    @if (!$lockedHours)
                                        <button type="submit" class="btn btn-warning">
                                            <i class="fas fa-save me-1"></i>
                                            Salva Orari + Assegnazioni (giornata)
                                        </button>
                                    @else
                                        <span class="badge bg-success align-self-center">
                                            <i class="fas fa-check-circle me-1"></i> Orari/assegnazioni confermati per
                                            questa giornata
                                        </span>
                                    @endif
                                </div>
                            </form>

                            <div class="mt-3">
                                <form method="POST" action="{{ route('records.dscDayHours.confirm', $race) }}">
                                    @csrf
                                    <input type="hidden" name="day" value="{{ $selectedDay }}">

                                    <button type="submit" class="btn btn-success"
                                        onclick="return confirm('Confermare gli orari/assegnazioni DSC per questa giornata? Dopo non potrai più modificarli.');"
                                        {{ ($hasAnyDayRows ?? false) && !$lockedHours ? '' : 'disabled' }}>
                                        <i class="fas fa-check me-1"></i> Conferma Orari/Assegnazioni (giornata)
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>


                @endif

                {{-- ============================================================
                    TABELLA SOTTO:
                    - DSC: riepilogo snello (solo altri crono)
                    - NON DSC: tabella FULL (stile report_full)
                ============================================================ --}}
                @if ($isLeader)

                    {{-- ================== DSC: RIEPILOGO SNELLO ================== --}}
                    <div class="card tk-card p-3">
                        <div class="card-header tk-card-header">
                            <i class="fas fa-list-ul me-2"></i> Report — riepilogo gara (solo altri cronometristi)
                        </div>

                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped align-middle mb-0 table-vertical-separators">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Crono</th>
                                            <th>Km</th>
                                            <th>Pedaggi</th>
                                            <th>Vitto</th>
                                            <th>Spese varie</th>
                                            <th>Note spese</th>
                                            <th>Azioni</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @forelse ($rowsForSummary as $row)
                                            @php
                                                $entry = $row['entry'];
                                            @endphp

                                            <tr>
                                                <td>{{ $row['user']->name }} {{ $row['user']->surname }}</td>

                                                <td>{{ number_format((float) ($entry->km ?? 0), 2) }}</td>
                                                <td>{{ number_format((float) ($entry->pedaggi ?? 0), 2) }}</td>
                                                <td>{{ number_format((float) ($entry->vitto ?? 0), 2) }}</td>
                                                <td>{{ number_format((float) ($entry->spese_varie ?? 0), 2) }}</td>
                                                <td>{{ $entry->spese_varie_note ?? '—' }}</td>

                                                <td class="text-nowrap">
                                                    @php
                                                        $canConfirm =
                                                            $isLeader &&
                                                            !$entry->confirmed &&
                                                            !($entry->secretariat_confirmed ?? false);
                                                    @endphp

                                                    <div class="d-flex flex-wrap gap-2 justify-content-start">
                                                        @if ($canConfirm && ($entry->exists ?? false))
                                                            <form method="POST"
                                                                action="{{ route('records.confirm', $entry) }}">
                                                                @csrf
                                                                <button type="submit" class="btn btn-warning btn-sm"
                                                                    onclick="return confirm('Confermare questo report?');">
                                                                    <i class="fas fa-check me-1"></i> Conferma
                                                                </button>
                                                            </form>
                                                        @endif

                                                        @if ($entry->secretariat_confirmed ?? false)
                                                            <span class="badge bg-success align-self-center">
                                                                <i class="fas fa-lock me-1"></i> Chiuso segreteria
                                                            </span>
                                                        @elseif ($entry->confirmed)
                                                            <span class="badge bg-success align-self-center">
                                                                <i class="fas fa-check-circle me-1"></i> Confermato
                                                            </span>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center text-muted p-4">
                                                    Nessun dato disponibile.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>

                            </div>
                        </div>
                    </div>

                    @if ($hasUnconfirmed)
                        <div class="d-flex mt-3 flex-wrap gap-2">
                            <form method="POST" action="{{ route('records.confirm.all', $race) }}">
                                @csrf
                                <button type="submit" class="btn btn-warning"
                                    onclick="return confirm('Confermare TUTTI i report della gara?');">
                                    <i class="fas fa-check-double me-1"></i> Conferma Tutti i Report della Gara
                                </button>
                            </form>

                            <a href="{{ route('secretariat.races.reportFull', $race) }}"
                                class="btn btn-outline-primary">
                                <i class="fas fa-file-alt me-1"></i> Apri Report Completo
                            </a>
                        </div>
                    @endif
                @else
                    {{-- ================== CRONO NON DSC: TABELLA FULL ================== --}}
                    @if (!$hasFullData)
                        <div class="alert alert-warning">
                            Dati “full” non disponibili. Ti manca la modifica al controller (vedi sotto).
                        </div>
                    @else
                        @php
                            // variabili comode (arrivano dal controller)
                            $startDate = \Carbon\Carbon::parse($race->date_of_race);
                            $endDate = $race->date_end ? \Carbon\Carbon::parse($race->date_end) : $startDate->copy();

                            $appsDsc = $dscRace->apparecchiature ?? [];
                            $missedMeals = (int) ($dscRace->missed_meals ?? 0);
                            $missedMealsAmount = $missedMeals * 15;

                            $vanNeeded = (bool) ($dscRace->van_needed ?? false);

                            $vanCostRace =
                                $settings && $settings->van_cost !== null ? (float) $settings->van_cost : 0.0;
                            $coeffKm = $settings && $settings->coeff_km !== null ? (float) $settings->coeff_km : 0.36;

                            $contributoOrganizzativo =
                                $settings && $settings->contributo_organizzativo !== null
                                    ? (float) $settings->contributo_organizzativo
                                    : 0.0;

                            $speseVarieGara =
                                $settings && $settings->spese_varie_gara !== null
                                    ? (float) $settings->spese_varie_gara
                                    : 0.0;

                            $daysCount = is_array($fullDays) ? count($fullDays) : 0;
                        @endphp

                        <style>
                            .table-wrap {
                                position: relative;
                                max-height: 70vh;
                                overflow: auto;
                                max-width: 100%;
                                border-top: 1px solid rgba(0, 0, 0, .08);
                            }

                            :root {
                                --meta-h: 0px;
                                --group-h: 0px;
                            }

                            .big-report-table {
                                border-collapse: separate;
                                border-spacing: 0;
                            }

                            .big-report-table thead tr.group-row th {
                                position: sticky;
                                top: var(--meta-h);
                                z-index: 30;
                                background: var(--bs-table-bg, #fff);
                            }

                            .big-report-table thead tr.header-row th {
                                position: sticky;
                                top: calc(var(--meta-h) + var(--group-h));
                                z-index: 29;
                                background: var(--bs-table-bg, #fff);
                            }

                            .big-report-table th,
                            .big-report-table td {
                                vertical-align: middle;
                            }

                            .num {
                                text-align: end;
                                font-variant-numeric: tabular-nums;
                            }

                            .nowrap {
                                white-space: nowrap;
                            }

                            .cell-muted {
                                color: #6c757d;
                            }

                            .big-report-table thead th {
                                background: var(--bs-table-bg, #fff);
                            }

                            .sticky-col-1 {
                                position: sticky;
                                left: 0;
                                z-index: 40;
                                background: var(--bs-body-bg, #fff);
                                box-shadow: 1px 0 0 rgba(0, 0, 0, .08);
                            }

                            .sticky-col-2 {
                                position: sticky;
                                left: 140px;
                                z-index: 40;
                                background: var(--bs-body-bg, #fff);
                                box-shadow: 1px 0 0 rgba(0, 0, 0, .08);
                            }

                            .w-col-crono {
                                width: 140px;
                                min-width: 140px;
                                max-width: 140px;
                            }

                            .w-col-dom {
                                width: 170px;
                                min-width: 170px;
                                max-width: 170px;
                            }

                            .clip {
                                overflow: hidden;
                                text-overflow: ellipsis;
                                white-space: nowrap;
                            }

                            .sep-right {
                                border-right: 2px solid rgba(0, 0, 0, .08) !important;
                            }

                            .controls-bar {
                                background: #fff;
                                border: 1px solid rgba(0, 0, 0, .08);
                                border-radius: .75rem;
                                padding: .75rem;
                            }

                            .report-meta-bar {
                                position: sticky;
                                top: 0;
                                z-index: 60;
                                background: #fff;
                                border-bottom: 1px solid rgba(0, 0, 0, .08);
                                padding: .75rem;
                            }

                            @media print {
                                @page {
                                    size: landscape;
                                    margin: 10mm;
                                }

                                .btn,
                                .controls-bar,
                                .alert {
                                    display: none !important;
                                }

                                .table-wrap {
                                    overflow: visible !important;
                                    border: none !important;
                                    max-height: none !important;
                                }

                                .big-report-table {
                                    font-size: 9pt;
                                    min-width: 0 !important;
                                }

                                .big-report-table thead th {
                                    position: static !important;
                                }

                                .sticky-col-1,
                                .sticky-col-2 {
                                    position: static !important;
                                    box-shadow: none !important;
                                }

                                .d-none.force-hide {
                                    display: table-cell !important;
                                }
                            }
                        </style>

                        <div class="card tk-card">
                            <div
                                class="card-header tk-card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                                <div>
                                    <i class="fas fa-table me-2"></i> Report Completo (Crono × Giorno)
                                </div>

                                <div class="d-flex gap-2">
                                    {{-- <button class="btn btn-outline-secondary btn-sm" type="button"
                                        onclick="window.print()">
                                        <i class="fas fa-print me-1"></i> Stampa
                                    </button> --}}

                                    <a href="{{ route('timekeeper.racesList') }}"
                                        class="btn btn-outline-primary btn-sm">
                                        <i class="fas fa-arrow-left me-1"></i> Torna alle gare
                                    </a>
                                </div>
                            </div>

                            <div class="card-body">
                                <div class="controls-bar mb-3">
                                    <div class="row g-2 align-items-end">
                                        <div class="col-12 col-lg-5">
                                            <label for="reportSearch" class="form-label mb-1">Ricerca nella
                                                tabella</label>
                                            <input id="reportSearch" type="search" class="form-control"
                                                placeholder="Es. Rossi, domicilio, note, luogo, ecc.">
                                            <div class="form-text">Filtra le righe mentre scrivi (non modifica i dati).
                                            </div>
                                        </div>

                                        <div class="col-12 col-lg-7">
                                            <div class="d-flex flex-wrap gap-3">
                                                <div>
                                                    <div class="fw-semibold mb-1">Mostra/Nascondi gruppi</div>

                                                    <div class="d-flex flex-wrap gap-3">
                                                        <div class="form-check">
                                                            <input class="form-check-input col-toggle" type="checkbox"
                                                                id="tgOrari" data-group="g-orari" checked>
                                                            <label class="form-check-label"
                                                                for="tgOrari">Orari</label>
                                                        </div>

                                                        <div class="form-check">
                                                            <input class="form-check-input col-toggle" type="checkbox"
                                                                id="tgOrd" data-group="g-ord" checked>
                                                            <label class="form-check-label"
                                                                for="tgOrd">Ordinario</label>
                                                        </div>

                                                        <div class="form-check">
                                                            <input class="form-check-input col-toggle" type="checkbox"
                                                                id="tgSpec" data-group="g-spec" checked>
                                                            <label class="form-check-label"
                                                                for="tgSpec">Specialistico</label>
                                                        </div>

                                                        <div class="form-check">
                                                            <input class="form-check-input col-toggle" type="checkbox"
                                                                id="tgDsc" data-group="g-dsc" checked>
                                                            <label class="form-check-label" for="tgDsc">DSC</label>
                                                        </div>

                                                        <div class="form-check">
                                                            <input class="form-check-input col-toggle" type="checkbox"
                                                                id="tgSegr" data-group="g-segr" checked>
                                                            <label class="form-check-label"
                                                                for="tgSegr">Segreteria</label>
                                                        </div>

                                                        <div class="form-check">
                                                            <input class="form-check-input col-toggle" type="checkbox"
                                                                id="tgSpese" data-group="g-spese" checked>
                                                            <label class="form-check-label"
                                                                for="tgSpese">Spese</label>
                                                        </div>

                                                        <div class="form-check">
                                                            <input class="form-check-input col-toggle" type="checkbox"
                                                                id="tgTot" data-group="g-totali" checked>
                                                            <label class="form-check-label"
                                                                for="tgTot">Totali</label>
                                                        </div>

                                                        <div class="form-check">
                                                            <input class="form-check-input col-toggle" type="checkbox"
                                                                id="tgNote" data-group="g-note" checked>
                                                            <label class="form-check-label"
                                                                for="tgNote">Note/Allegati/Stato</label>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="ms-auto d-flex gap-2">
                                                    <button class="btn btn-outline-secondary btn-sm" type="button"
                                                        id="btnAllOn">Tutto ON</button>
                                                    <button class="btn btn-outline-secondary btn-sm" type="button"
                                                        id="btnEssentials">Solo essenziali</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- META BAR --}}
                                <div class="report-meta-bar">
                                    <div class="d-flex flex-wrap gap-3">
                                        <div>
                                            <div class="fw-bold">Organizzatore / Ente:</div>
                                            <div>{{ $race->ente_fatturazione ?? '—' }}</div>
                                            @if (!empty($race->organizer_email))
                                                <div class="text-muted small">{{ $race->organizer_email }}</div>
                                            @endif
                                        </div>

                                        <div style="min-width:250px;">
                                            <div class="fw-bold">Descrizione gara / Note:</div>
                                            <div>{{ $race->note ?? '—' }}</div>
                                        </div>

                                        <div>
                                            <div class="fw-bold">Luogo:</div>
                                            <div>{{ $race->place ?? '—' }}</div>
                                        </div>

                                        <div>
                                            <div class="fw-bold">Data inizio:</div>
                                            <div>{{ ucwords($startDate->translatedFormat('l d F Y')) }}</div>
                                        </div>

                                        <div>
                                            <div class="fw-bold">Data fine:</div>
                                            <div>{{ ucwords($endDate->translatedFormat('l d F Y')) }}</div>
                                        </div>

                                        <div>
                                            <div class="fw-bold">Tipologia gara:</div>
                                            <div>{{ $race->type ?? '—' }}</div>
                                        </div>

                                        <div>
                                            <div class="fw-bold">Giorni gara:</div>
                                            <div>{{ (int) ($raceDaysCount ?? 1) }}</div>
                                        </div>
                                    </div>
                                </div>

                            </div>

                            <div class="card-body p-0">
                                <div class="table-wrap" role="region" aria-label="Tabella report completo"
                                    tabindex="0">
                                    <table
                                        class="table table-striped table-hover table-sm align-middle mb-0 big-report-table">
                                        <thead class="table-light">
                                            <tr class="group-row">
                                                <th colspan="2" class="sep-right sticky-col-1 z-3"></th>

                                                <th colspan="6" class="z-0 g-orari sep-right text-center">Orari
                                                </th>
                                                <th colspan="3" class="z-0 g-ord sep-right text-center">Ordinario
                                                </th>
                                                <th colspan="3" class="z-0 g-spec sep-right text-center">
                                                    Specialistico</th>
                                                <th colspan="1" class="z-0 g-orari sep-right text-center">Tot.
                                                    servizio</th>

                                                <th colspan="4" class="z-0 g-dsc sep-right text-center">DSC</th>
                                                <th colspan="4" class="z-0 g-segr sep-right text-center">Segreteria
                                                    (gara)</th>
                                                <th colspan="6" class="z-0 g-spese sep-right text-center">Spese
                                                    (crono)</th>

                                                <th colspan="3" class="z-0 g-totali sep-right text-center">Totali
                                                </th>
                                                <th colspan="3" class="z-0 g-note text-center">Note / Allegati /
                                                    Stato</th>
                                            </tr>

                                            <tr class="header-row">
                                                <th class="z-3 sticky-col-1 w-col-crono">Crono</th>
                                                <th class="z-3 sticky-col-2 w-col-dom sep-right">Domicilio</th>

                                                <th class="z-0 g-orari nowrap">Giorno</th>
                                                <th class="z-0 g-orari nowrap">Inizio matt.</th>
                                                <th class="z-0 g-orari nowrap">Fine matt.</th>
                                                <th class="z-0 g-orari nowrap">Inizio pom.</th>
                                                <th class="z-0 g-orari nowrap">Fine pom.</th>
                                                <th class="z-0 g-orari nowrap sep-right">Ore da orari</th>

                                                <th class="z-0 g-ord nowrap">Ore ord.</th>
                                                <th class="z-0 g-ord nowrap">Tar. ord.</th>
                                                <th class="z-0 g-ord nowrap sep-right">Imp. ord.</th>

                                                <th class="z-0 g-spec nowrap">Ore spec.</th>
                                                <th class="z-0 g-spec nowrap">Tar. spec.</th>
                                                <th class="z-0 g-spec nowrap sep-right">Imp. spec.</th>

                                                <th class="z-0 g-orari nowrap sep-right">Tot. serv.(gg)</th>

                                                <th class="z-0 g-dsc nowrap">Furgone</th>
                                                <th class="z-0 g-dsc nowrap">Mancati pasti</th>
                                                <th class="z-0 g-dsc nowrap">Imp. mancati</th>
                                                <th class="z-0 g-dsc nowrap sep-right">Apparecchiature</th>

                                                <th class="z-0 g-segr nowrap">Coeff Km</th>
                                                <th class="z-0 g-segr nowrap">Van cost</th>
                                                <th class="z-0 g-segr nowrap">Contributo org.</th>
                                                <th class="z-0 g-segr nowrap sep-right">Spese varie</th>

                                                <th class="z-0 g-spese nowrap">Km</th>
                                                <th class="z-0 g-spese nowrap">Imp. Km</th>
                                                <th class="z-0 g-spese nowrap">Pedaggi</th>
                                                <th class="z-0 g-spese nowrap">Vitto</th>
                                                <th class="z-0 g-spese nowrap">Spese varie</th>
                                                <th class="z-0 g-spese nowrap sep-right">Note spese</th>

                                                <th class="z-0 g-totali nowrap">Tot. parte gara</th>
                                                <th class="z-0 g-totali nowrap">TotaleCrono</th>
                                                <th class="z-0 g-totali nowrap sep-right">Grand total</th>

                                                <th class="z-0 g-note nowrap">Note crono</th>
                                                <th class="z-0 g-note nowrap">Allegati</th>
                                                <th class="z-0 g-note nowrap">Stato</th>
                                            </tr>
                                        </thead>

                                        <tbody id="reportTbody">
                                            @forelse ($fullRows as $row)
                                                @php
                                                    $u = $row['user'];
                                                    $entry = $row['entry'];

                                                    $sysRace = $row['sysRace'] ?? [];

                                                    $totalRacePart = (float) ($sysRace['totalRacePart'] ?? 0);
                                                    $totaleCrono = (float) ($row['totaleCrono'] ?? 0);
                                                    $grandTotal = (float) ($row['grandTotal'] ?? 0);

                                                    $kmAmount = (float) ($sysRace['kmAmount'] ?? 0);

                                                    $rowspan = max(1, $daysCount);
                                                @endphp

                                                @foreach ($fullDays as $i => $day)
                                                    @php
                                                        $drow = $row['perDay'][$day] ?? null;

                                                        $dscDay = $drow['dscDay'] ?? null;
                                                        $workedHours = (float) ($drow['workedHours'] ?? 0);

                                                        $service = $drow['service'] ?? [];
                                                        $ordHours = (float) ($service['ordHours'] ?? 0);
                                                        $ordRate = (float) ($service['ordRate'] ?? 0);
                                                        $ordAmount = (float) ($service['ordAmount'] ?? 0);

                                                        $specHours = (float) ($service['specHours'] ?? 0);
                                                        $specRate = (float) ($service['specRate'] ?? 0);
                                                        $specAmount = (float) ($service['specAmount'] ?? 0);

                                                        $totalServiceDay = (float) ($service['totalService'] ?? 0);

                                                        $dayLabel = ucwords(
                                                            \Carbon\Carbon::parse($day)->translatedFormat('l d F'),
                                                        );
                                                    @endphp

                                                    <tr class="report-row">
                                                        @if ($i === 0)
                                                            <td rowspan="{{ $rowspan }}"
                                                                class="fw-bold sticky-col-1 w-col-crono clip"
                                                                title="{{ $u->surname }} {{ $u->name }}">
                                                                {{ $u->surname }} {{ $u->name }}
                                                            </td>

                                                            <td rowspan="{{ $rowspan }}"
                                                                class="sticky-col-2 w-col-dom clip sep-right"
                                                                title="{{ $u->domicile ?? '' }}">
                                                                {{ $u->domicile ?? '—' }}
                                                            </td>
                                                        @endif

                                                        <td class="g-orari nowrap">{{ $dayLabel }}</td>
                                                        <td class="g-orari nowrap">
                                                            {{ $dscDay?->morning_start ?? '—' }}</td>
                                                        <td class="g-orari nowrap">{{ $dscDay?->morning_end ?? '—' }}
                                                        </td>
                                                        <td class="g-orari nowrap">
                                                            {{ $dscDay?->afternoon_start ?? '—' }}</td>
                                                        <td class="g-orari nowrap">
                                                            {{ $dscDay?->afternoon_end ?? '—' }}</td>
                                                        <td class="g-orari fw-semibold num sep-right">
                                                            {{ number_format($workedHours, 2) }}</td>

                                                        <td class="g-ord num">{{ number_format($ordHours, 2) }}</td>
                                                        <td class="g-ord num">{{ number_format($ordRate, 2) }}</td>
                                                        <td class="g-ord num sep-right">
                                                            {{ number_format($ordAmount, 2) }}</td>

                                                        <td class="g-spec num">{{ number_format($specHours, 2) }}
                                                        </td>
                                                        <td class="g-spec num">{{ number_format($specRate, 2) }}</td>
                                                        <td class="g-spec num sep-right">
                                                            {{ number_format($specAmount, 2) }}</td>

                                                        <td class="g-orari fw-semibold num sep-right">
                                                            {{ number_format($totalServiceDay, 2) }}</td>

                                                        @if ($i === 0)
                                                            <td rowspan="{{ $rowspan }}" class="g-dsc nowrap">
                                                                {{ $vanNeeded ? 'Sì' : 'No' }}</td>
                                                            <td rowspan="{{ $rowspan }}" class="g-dsc num">
                                                                {{ $missedMeals }}</td>
                                                            <td rowspan="{{ $rowspan }}" class="g-dsc num">
                                                                {{ number_format($missedMealsAmount, 2) }}</td>
                                                            <td rowspan="{{ $rowspan }}"
                                                                class="g-dsc sep-right">
                                                                @if (!empty($appsDsc))
                                                                    {{ implode(', ', $appsDsc) }}
                                                                @else
                                                                    <span class="cell-muted">—</span>
                                                                @endif
                                                            </td>

                                                            <td rowspan="{{ $rowspan }}" class="g-segr num">
                                                                {{ number_format($coeffKm, 4) }}</td>
                                                            <td rowspan="{{ $rowspan }}" class="g-segr num">
                                                                {{ number_format($vanCostRace, 2) }}</td>
                                                            <td rowspan="{{ $rowspan }}" class="g-segr num">
                                                                {{ number_format($contributoOrganizzativo, 2) }}</td>
                                                            <td rowspan="{{ $rowspan }}"
                                                                class="g-segr num sep-right">
                                                                {{ number_format($speseVarieGara, 2) }}</td>

                                                            <td rowspan="{{ $rowspan }}" class="g-spese num">
                                                                {{ number_format((float) ($entry->km ?? 0), 2) }}</td>
                                                            <td rowspan="{{ $rowspan }}" class="g-spese num">
                                                                {{ number_format($kmAmount, 2) }}</td>
                                                            <td rowspan="{{ $rowspan }}" class="g-spese num">
                                                                {{ number_format((float) ($entry->pedaggi ?? 0), 2) }}
                                                            </td>
                                                            <td rowspan="{{ $rowspan }}" class="g-spese num">
                                                                {{ number_format((float) ($entry->vitto ?? 0), 2) }}
                                                            </td>
                                                            <td rowspan="{{ $rowspan }}" class="g-spese num">
                                                                {{ number_format((float) ($entry->spese_varie ?? 0), 2) }}
                                                            </td>
                                                            <td rowspan="{{ $rowspan }}"
                                                                class="g-spese sep-right">
                                                                {{ $entry->spese_varie_note ?? '—' }}
                                                            </td>

                                                            <td rowspan="{{ $rowspan }}"
                                                                class="g-totali fw-semibold num">
                                                                {{ number_format($totalRacePart, 2) }}</td>
                                                            <td rowspan="{{ $rowspan }}"
                                                                class="g-totali fw-semibold num">
                                                                {{ number_format($totaleCrono, 2) }}</td>
                                                            <td rowspan="{{ $rowspan }}"
                                                                class="g-totali fw-bold num sep-right">
                                                                {{ number_format($grandTotal, 2) }}</td>

                                                            <td rowspan="{{ $rowspan }}" class="g-note">
                                                                {{ $entry->note ?? '—' }}</td>

                                                            <td rowspan="{{ $rowspan }}" class="g-note">
                                                                @if ($entry->attachments && $entry->attachments->count())
                                                                    <ul class="list-unstyled mb-0">
                                                                        @foreach ($entry->attachments as $a)
                                                                            <li class="mb-1">
                                                                                <a href="{{ asset('storage/' . $a->file_path) }}"
                                                                                    target="_blank" rel="noopener">
                                                                                    {{ $a->original_name }}
                                                                                </a>
                                                                            </li>
                                                                        @endforeach
                                                                    </ul>
                                                                @else
                                                                    <span class="text-muted">—</span>
                                                                @endif
                                                            </td>

                                                            <td rowspan="{{ $rowspan }}" class="g-note nowrap">
                                                                @if ($entry->secretariat_confirmed ?? false)
                                                                    <span class="badge bg-success">
                                                                        <i class="fas fa-lock me-1"></i>
                                                                        Chiuso segreteria
                                                                    </span>
                                                                @elseif ($entry->confirmed)
                                                                    <span class="badge bg-success">
                                                                        <i class="fas fa-check-circle me-1"></i>
                                                                        Confermato
                                                                    </span>
                                                                @else
                                                                    <span class="badge bg-secondary">Non
                                                                        confermato</span>
                                                                @endif
                                                            </td>
                                                        @endif
                                                    </tr>
                                                @endforeach
                                            @empty
                                                <tr>
                                                    <td colspan="35" class="text-center text-muted p-4">
                                                        Nessun dato disponibile.
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>

                                <div class="p-3 small text-muted">
                                    Regole: Ordinario = prime 4 ore 30€, poi +6€/h. Specialistico = prime 4 ore 40€, poi
                                    +10€/h.
                                </div>
                            </div>
                        </div>

                        <script>
                            (function() {
                                const search = document.getElementById('reportSearch');
                                const tbody = document.getElementById('reportTbody');

                                function normalize(s) {
                                    return (s || '').toString().toLowerCase().trim();
                                }

                                if (search && tbody) {
                                    search.addEventListener('input', function() {
                                        const q = normalize(search.value);
                                        const rows = tbody.querySelectorAll('tr.report-row');
                                        rows.forEach(tr => {
                                            const text = normalize(tr.innerText);
                                            tr.style.display = (!q || text.includes(q)) ? '' : 'none';
                                        });
                                    });
                                }

                                const toggles = document.querySelectorAll('.col-toggle');

                                function setGroupVisible(groupClass, visible) {
                                    const cells = document.querySelectorAll('.' + groupClass);
                                    cells.forEach(el => {
                                        if (!visible) {
                                            el.classList.add('d-none', 'force-hide');
                                        } else {
                                            el.classList.remove('d-none', 'force-hide');
                                        }
                                    });
                                }

                                toggles.forEach(tg => {
                                    tg.addEventListener('change', () => {
                                        const group = tg.getAttribute('data-group');
                                        setGroupVisible(group, tg.checked);
                                    });
                                });

                                const btnAllOn = document.getElementById('btnAllOn');
                                const btnEssentials = document.getElementById('btnEssentials');

                                if (btnAllOn) {
                                    btnAllOn.addEventListener('click', () => {
                                        toggles.forEach(tg => {
                                            tg.checked = true;
                                            setGroupVisible(tg.getAttribute('data-group'), true);
                                        });
                                    });
                                }

                                if (btnEssentials) {
                                    btnEssentials.addEventListener('click', () => {
                                        toggles.forEach(tg => {
                                            const group = tg.getAttribute('data-group');
                                            const keep = (group === 'g-orari' || group === 'g-totali' || group ===
                                                'g-note');
                                            tg.checked = keep;
                                            setGroupVisible(group, keep);
                                        });
                                    });
                                }
                            })();
                        </script>
                    @endif

                @endif

            </div>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const selects = document.querySelectorAll('.js-vitto-tipo');

            selects.forEach(function(select) {
                const form = select.closest('form');
                const wrapper = form ? form.querySelector('.js-vitto-documentato-wrap') : null;
                const input = form ? form.querySelector('.js-vitto-documentato') : null;

                function toggleVittoDocumentato() {
                    if (!wrapper || !input) {
                        return;
                    }

                    const isDocumentato = select.value === 'documentato';

                    wrapper.style.display = isDocumentato ? 'block' : 'none';
                    input.required = isDocumentato;

                    if (!isDocumentato) {
                        input.value = '';
                    }
                }

                select.addEventListener('change', toggleVittoDocumentato);
                toggleVittoDocumentato();
            });
        });
    </script>
</x-layout>
