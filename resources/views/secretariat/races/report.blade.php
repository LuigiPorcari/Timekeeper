<x-layout documentTitle="Report Gara - Segreteria">
    <main class="container mt-5 pt-5">
        <div class="row justify-content-center">
            <div class="col-12 col-xl-11">

                <h1 class="mb-4">
                    Report Gara (Segreteria): {{ $race->name }} <br>
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
                    $rowsCollection = collect($rows);

                    $secretariatOpenEntriesCount = $rowsCollection
                        ->filter(function ($row) {
                            $entry = $row['entry'] ?? null;

                            return !$entry || !($entry->exists ?? false) || !($entry->secretariat_confirmed ?? false);
                        })
                        ->count();

                    $raceClosedBySecretariat = $rowsCollection->count() > 0 && $secretariatOpenEntriesCount === 0;

                    $missedMealsDetailForDisplay = $dscRace->missed_meals_detail ?? [];

                    if (is_string($missedMealsDetailForDisplay)) {
                        $decodedMealsForDisplay = json_decode($missedMealsDetailForDisplay, true);
                        $missedMealsDetailForDisplay =
                            json_last_error() === JSON_ERROR_NONE && is_array($decodedMealsForDisplay)
                                ? $decodedMealsForDisplay
                                : [];
                    }

                    if (!is_array($missedMealsDetailForDisplay)) {
                        $missedMealsDetailForDisplay = [];
                    }

                    $getMissedMealsData = function ($userId) use ($missedMealsDetailForDisplay) {
                        $mealData =
                            $missedMealsDetailForDisplay[$userId] ??
                            ($missedMealsDetailForDisplay[(string) $userId] ?? []);

                        if (is_string($mealData)) {
                            $decodedMealData = json_decode($mealData, true);
                            $mealData =
                                json_last_error() === JSON_ERROR_NONE && is_array($decodedMealData)
                                    ? $decodedMealData
                                    : [];
                        }

                        if (!is_array($mealData)) {
                            $mealData = [];
                        }

                        $pranzo = !empty($mealData['pranzo']) || !empty($mealData['lunch']);
                        $cena = !empty($mealData['cena']) || !empty($mealData['dinner']);
                        $count = ($pranzo ? 1 : 0) + ($cena ? 1 : 0);

                        if ($pranzo && $cena) {
                            $label = 'Pranzo + Cena';
                        } elseif ($pranzo) {
                            $label = 'Pranzo';
                        } elseif ($cena) {
                            $label = 'Cena';
                        } else {
                            $label = '—';
                        }

                        return [
                            'count' => $count,
                            'amount' => $count * 15,
                            'label' => $label,
                        ];
                    };

                    $formatMissedMealsDetail = function ($userId) use ($getMissedMealsData) {
                        return $getMissedMealsData($userId)['label'];
                    };

                    $countMissedMealsForUser = function ($userId) use ($getMissedMealsData) {
                        return $getMissedMealsData($userId)['count'];
                    };

                    $amountMissedMealsForUser = function ($userId) use ($getMissedMealsData) {
                        return $getMissedMealsData($userId)['amount'];
                    };
                @endphp

                <div class="card tk-card mb-4">
                    <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-3">
                        <div>
                            <div class="fw-semibold">Stato chiusura segreteria</div>
                            @if ($raceClosedBySecretariat)
                                <span class="badge bg-success">
                                    <i class="fas fa-lock me-1"></i> Tutti i report sono confermati dalla segreteria
                                </span>
                            @else
                                <span class="badge bg-warning text-dark">
                                    <i class="fas fa-unlock me-1"></i>
                                    Report ancora modificabili dalla segreteria: {{ $secretariatOpenEntriesCount }}
                                </span>
                            @endif
                        </div>

                        @if (!$raceClosedBySecretariat && $rowsCollection->count() > 0)
                            <form method="POST" action="{{ route('secretariat.races.records.confirmAll', $race) }}"
                                onsubmit="return confirm('Confermare definitivamente tutti i report di questa gara? Dopo non saranno più modificabili da nessuno.');">
                                @csrf
                                <input type="hidden" name="day" value="{{ $selectedDay }}">
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-check-double me-1"></i> Conferma tutti i report
                                </button>
                            </form>
                        @endif
                    </div>
                </div>

                {{-- UNICO selettore giorno: serve per orari DSC + ore segreteria --}}
                @if (!empty($days))
                    <form method="GET" class="mb-3">
                        <label for="day" class="form-label">Seleziona giornata</label>
                        <select id="day" name="day" class="form-select" onchange="this.form.submit()">
                            @foreach ($days as $day)
                                <option value="{{ $day }}" {{ $selectedDay === $day ? 'selected' : '' }}>
                                    {{ ucwords(\Carbon\Carbon::parse($day)->translatedFormat('l d F')) }}
                                </option>
                            @endforeach
                        </select>
                    </form>
                @endif

                {{-- BOX DSC GARA --}}
                <div class="card tk-card mb-4">
                    <div class="card-header tk-card-header">
                        <i class="fas fa-user-shield me-2"></i> Dati DSC (gara, validi per tutti)
                    </div>
                    <div class="card-body">
                        @if ($dscRace)
                            <div class="row g-3">
                                <div class="col-12 col-md-2">
                                    <div class="fw-bold">Furgone</div>
                                    <div>{{ $dscRace->van_needed ? 'Sì' : 'No' }}</div>
                                </div>
                                <div class="col-12 col-md-2">
                                    <div class="fw-bold">Mancati pasti</div>
                                    <div>{{ (int) ($dscRace->missed_meals ?? 0) }}</div>
                                </div>
                                <div class="col-12 col-md-4">
                                    <div class="fw-bold">Dettaglio pasti</div>
                                    <div class="small">
                                        @php
                                            $mealDetailsForCard = $rowsCollection
                                                ->map(function ($row) use ($formatMissedMealsDetail) {
                                                    $u = $row['user'] ?? null;
                                                    if (!$u) {
                                                        return null;
                                                    }

                                                    $label = $formatMissedMealsDetail($u->id);

                                                    if ($label === '—') {
                                                        return null;
                                                    }

                                                    return [
                                                        'name' => trim(($u->surname ?? '') . ' ' . ($u->name ?? '')),
                                                        'label' => $label,
                                                    ];
                                                })
                                                ->filter()
                                                ->values();
                                        @endphp

                                        @if ($mealDetailsForCard->isNotEmpty())
                                            <div class="d-flex flex-column gap-1">
                                                @foreach ($mealDetailsForCard as $mealDetailForCard)
                                                    <div>
                                                        <span
                                                            class="fw-semibold">{{ $mealDetailForCard['name'] }}</span>
                                                        <span class="text-muted">—</span>
                                                        <span>{{ $mealDetailForCard['label'] }}</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-12 col-md-4">
                                    <div class="fw-bold">Apparecchiature</div>
                                    <div>
                                        @php $apps = $dscRace->apparecchiature ?? []; @endphp
                                        @if (!empty($apps))
                                            {{ implode(', ', $apps) }}
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="mt-3">
                                @if ($dscRace->confirmed)
                                    <span class="badge bg-success">
                                        <i class="fas fa-check-circle me-1"></i> Confermato
                                    </span>
                                @else
                                    <span class="badge bg-secondary">Non confermato</span>
                                @endif
                            </div>
                        @else
                            <div class="text-muted">Nessun dato DSC (gara) inserito.</div>
                        @endif
                    </div>
                </div>

                {{-- BOX ORARI DSC GIORNO --}}
                <div class="card tk-card mb-4">
                    <div class="card-header tk-card-header">
                        <i class="fas fa-clock me-2"></i> Orari DSC (giornata: {{ $selectedDay ?? '—' }})
                    </div>
                    <div class="card-body">
                        @if ($selectedDay && $dscDayHours)
                            <div class="row g-3">
                                <div class="col-6 col-md-3">
                                    <div class="fw-bold">Inizio mattina</div>
                                    <div>{{ $dscDayHours->morning_start ?? '—' }}</div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="fw-bold">Fine mattina</div>
                                    <div>{{ $dscDayHours->morning_end ?? '—' }}</div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="fw-bold">Inizio pomeriggio</div>
                                    <div>{{ $dscDayHours->afternoon_start ?? '—' }}</div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="fw-bold">Fine pomeriggio</div>
                                    <div>{{ $dscDayHours->afternoon_end ?? '—' }}</div>
                                </div>
                            </div>

                            <div class="mt-3">
                                @if ($dscDayHours->confirmed)
                                    <span class="badge bg-success">
                                        <i class="fas fa-check-circle me-1"></i> Confermato
                                    </span>
                                @else
                                    <span class="badge bg-secondary">Non confermato</span>
                                @endif
                            </div>
                        @else
                            <div class="text-muted">Nessun orario DSC inserito per questa giornata.</div>
                        @endif
                    </div>
                </div>

                {{-- SEGRETERIA: DATI GARA --}}
                <div class="card tk-card mb-4">
                    <div class="card-header tk-card-header d-flex justify-content-between align-items-center">
                        <h2 class="h5 mb-0">Segreteria — Dati Gara (una volta per gara)</h2>

                        @if ($settings)
                            <span class="badge bg-info text-dark">
                                Ultimo aggiornamento: {{ optional($settings->updated_at)->format('d/m/Y H:i') }}
                            </span>
                        @endif
                    </div>

                    <div class="card-body">
                        <form method="POST" action="{{ route('secretariat.races.adminSettings.save', $race) }}">
                            @csrf

                            {{-- per tornare sullo stesso giorno dopo il salvataggio --}}
                            <input type="hidden" name="day" value="{{ $selectedDay }}">

                            <div class="row g-3">
                                <div class="col-12 col-md-3">
                                    <label class="form-label">Importo furgone (gara)</label>
                                    <input type="number" step="0.01" min="0" name="van_cost"
                                        class="form-control" value="{{ old('van_cost', $settings->van_cost ?? '') }}"
                                        {{ $raceClosedBySecretariat ? 'disabled' : '' }}>
                                </div>

                                <div class="col-12 col-md-3">
                                    <label class="form-label">Coefficiente Kilometrico</label>
                                    <input type="number" step="0.0001" min="0" name="coeff_km"
                                        class="form-control"
                                        value="{{ old('coeff_km', $settings->coeff_km ?? 0.36) }}"
                                        {{ $raceClosedBySecretariat ? 'disabled' : '' }}>
                                </div>

                                <div class="col-12 col-md-3">
                                    <label class="form-label">Contributo organizzativo (gara)</label>
                                    <input type="number" step="0.01" min="0"
                                        name="contributo_organizzativo" class="form-control"
                                        value="{{ old('contributo_organizzativo', $settings->contributo_organizzativo ?? '') }}"
                                        {{ $raceClosedBySecretariat ? 'disabled' : '' }}>
                                </div>

                                <div class="col-12 col-md-3">
                                    <label class="form-label">Spese varie (gara)</label>
                                    <input type="number" step="0.01" min="0" name="spese_varie_gara"
                                        class="form-control"
                                        value="{{ old('spese_varie_gara', $settings->spese_varie_gara ?? '') }}"
                                        {{ $raceClosedBySecretariat ? 'disabled' : '' }}>
                                </div>

                            </div>

                            <button class="btn btn-primary mt-3" type="submit"
                                {{ $raceClosedBySecretariat ? 'disabled' : '' }}>
                                <i class="fas fa-save me-1"></i>
                                {{ $settings ? 'Modifica' : 'Salva' }} dati Segreteria (gara)
                            </button>
                            @if ($raceClosedBySecretariat)
                                <div class="form-text text-success">
                                    Report chiuso dalla segreteria: i dati non sono più modificabili.
                                </div>
                            @endif
                        </form>
                    </div>
                </div>

                {{-- SEGRETERIA: ORE PER GIORNATA --}}
                <div class="card tk-card mb-4">
                    <div class="card-header tk-card-header">
                        <h2 class="h5 mb-0">Segreteria — Ore per giornata (per ogni crono)</h2>
                    </div>

                    <div class="card-body">
                        @if ($selectedDay)
                            <form method="POST" action="{{ route('secretariat.races.dayAdmin.save', $race) }}">
                                @csrf
                                <input type="hidden" name="day" value="{{ $selectedDay }}">

                                <div class="table-responsive">
                                    <table class="table table-striped align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Crono</th>
                                                <th style="width:220px;">Ore servizio ordinario</th>
                                                <th style="width:220px;">Ore servizio specialistico</th>
                                                <th style="width:220px;">Ultimo aggiornamento</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($rowsForDayAdmin as $r)
                                                @php
                                                    $uid = $r['user']->id;
                                                    $dayRow = $adminDay->get($uid);
                                                @endphp
                                                <tr>
                                                    <td>{{ $r['user']->surname }} {{ $r['user']->name }}</td>

                                                    <td>
                                                        <input type="number" step="0.25" min="0"
                                                            max="24" class="form-control"
                                                            name="hours_ordinary_service[{{ $uid }}]"
                                                            value="{{ old('hours_ordinary_service.' . $uid, $dayRow->hours_ordinary_service ?? '') }}"
                                                            {{ $raceClosedBySecretariat ? 'disabled' : '' }}>
                                                    </td>

                                                    <td>
                                                        <input type="number" step="0.25" min="0"
                                                            max="24" class="form-control"
                                                            name="hours_special_service[{{ $uid }}]"
                                                            value="{{ old('hours_special_service.' . $uid, $dayRow->hours_special_service ?? '') }}"
                                                            {{ $raceClosedBySecretariat ? 'disabled' : '' }}>
                                                    </td>

                                                    <td class="text-muted">
                                                        {{ $dayRow?->updated_at ? $dayRow->updated_at->format('d/m/Y H:i') : '—' }}
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="4" class="text-center text-muted p-4">
                                                        Nessun cronometrista risulta assegnato dal DSC per questa
                                                        giornata.
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>

                                    </table>
                                </div>

                                <button class="btn btn-primary mt-3" type="submit"
                                    {{ $raceClosedBySecretariat ? 'disabled' : '' }}>
                                    <i class="fas fa-save me-1"></i> Salva / Modifica ore (giornata)
                                </button>

                                <div class="mt-2 small text-muted">
                                    Regole: Ordinario = prime 4 ore 30€, poi +6€/h.
                                    Specialistico = prime 4 ore 40€, poi +10€/h.
                                </div>
                            </form>
                        @else
                            <div class="text-muted">Nessuna giornata disponibile.</div>
                        @endif
                    </div>
                </div>

                {{-- SEGRETERIA: MODIFICA COMPLETA DIVISA PER CRONOMETRISTA --}}
                <div class="card tk-card mb-4">
                    <div class="card-header tk-card-header">
                        <h2 class="h5 mb-0">
                            <i class="fas fa-users-gear me-2"></i> Modifica completa per cronometrista
                        </h2>
                    </div>

                    <div class="card-body">
                        <div class="accordion" id="accordionReportCrono">
                            @forelse ($rows as $row)
                                @php
                                    $tk = $row['user'];
                                    $entry = $row['entry'];
                                    $entryExists = $entry && ($entry->exists ?? false);
                                    $entryClosed = $entryExists && ($entry->secretariat_confirmed ?? false);
                                    $formDisabled = $raceClosedBySecretariat || $entryClosed;
                                    $collapseId = 'collapse_crono_' . $tk->id;
                                    $headingId = 'heading_crono_' . $tk->id;
                                @endphp

                                <div class="accordion-item">
                                    <h3 class="accordion-header" id="{{ $headingId }}">
                                        <button class="accordion-button collapsed" type="button"
                                            data-bs-toggle="collapse" data-bs-target="#{{ $collapseId }}"
                                            aria-expanded="false" aria-controls="{{ $collapseId }}">
                                            <span class="fw-semibold me-2">
                                                {{ $tk->surname }} {{ $tk->name }}
                                            </span>

                                            @if ($entryClosed)
                                                <span class="badge bg-success ms-2">
                                                    <i class="fas fa-lock me-1"></i> Chiuso segreteria
                                                </span>
                                            @elseif ($entryExists && ($entry->confirmed ?? false))
                                                <span class="badge bg-primary ms-2">Confermato DSC</span>
                                            @elseif ($entryExists)
                                                <span class="badge bg-warning text-dark ms-2">Salvato, non confermato
                                                    DSC</span>
                                            @else
                                                <span class="badge bg-secondary ms-2">Report non ancora salvato</span>
                                            @endif
                                        </button>
                                    </h3>

                                    <div id="{{ $collapseId }}" class="accordion-collapse collapse"
                                        aria-labelledby="{{ $headingId }}" data-bs-parent="#accordionReportCrono">
                                        <div class="accordion-body">
                                            <form method="POST"
                                                action="{{ route('secretariat.races.timekeeperReport.update', ['race' => $race->id, 'user' => $tk->id]) }}">
                                                @csrf
                                                @method('PUT')
                                                <input type="hidden" name="selected_day"
                                                    value="{{ $selectedDay }}">

                                                <div class="row g-3">
                                                    <div class="col-12">
                                                        <h4 class="h6 mb-0">Dati spese report crono</h4>
                                                    </div>

                                                    <div class="col-12 col-md-3">
                                                        <label class="form-label">Km</label>
                                                        <input type="number" step="0.01" min="0"
                                                            name="km" class="form-control"
                                                            value="{{ old('km', $entry->km ?? null) }}"
                                                            {{ $formDisabled ? 'disabled' : '' }}>
                                                    </div>

                                                    <div class="col-12 col-md-3">
                                                        <label class="form-label">Pedaggi / Trasporto</label>
                                                        <input type="number" step="0.01" min="0"
                                                            name="pedaggi" class="form-control"
                                                            value="{{ old('pedaggi', $entry->pedaggi ?? null) }}"
                                                            {{ $formDisabled ? 'disabled' : '' }}>
                                                    </div>

                                                    @php
                                                        $vittoTipoSegreteria = old('vitto_tipo');

                                                        if ($vittoTipoSegreteria === null) {
                                                            if (!$entry || $entry->vitto === null) {
                                                                $vittoTipoSegreteria = '';
                                                            } elseif ((float) $entry->vitto === 15.0) {
                                                                $vittoTipoSegreteria = 'forfettario';
                                                            } elseif ((float) $entry->vitto === 0.0) {
                                                                $vittoTipoSegreteria = 'offerto';
                                                            } else {
                                                                $vittoTipoSegreteria = 'documentato';
                                                            }
                                                        }

                                                        $vittoDocumentatoSegreteria = old('vitto_documentato');

                                                        if (
                                                            $vittoDocumentatoSegreteria === null &&
                                                            $vittoTipoSegreteria === 'documentato'
                                                        ) {
                                                            $vittoDocumentatoSegreteria = $entry->vitto ?? null;
                                                        }
                                                    @endphp

                                                    <div class="col-12 col-md-3">
                                                        <label class="form-label">Vitto</label>
                                                        <select name="vitto_tipo" class="form-select js-vitto-tipo"
                                                            {{ $formDisabled ? 'disabled' : '' }}>
                                                            <option value="">-- Seleziona --</option>
                                                            <option value="forfettario"
                                                                {{ $vittoTipoSegreteria === 'forfettario' ? 'selected' : '' }}>
                                                                Forfettario - 15€
                                                            </option>
                                                            <option value="offerto"
                                                                {{ $vittoTipoSegreteria === 'offerto' ? 'selected' : '' }}>
                                                                Offerto - 0€
                                                            </option>
                                                            <option value="documentato"
                                                                {{ $vittoTipoSegreteria === 'documentato' ? 'selected' : '' }}>
                                                                Documentato
                                                            </option>
                                                        </select>
                                                    </div>

                                                    <div class="col-12 col-md-3 js-vitto-documentato-wrap"
                                                        style="display:none;">
                                                        <label class="form-label">Importo vitto documentato</label>
                                                        <input type="number" step="0.01" min="0"
                                                            name="vitto_documentato"
                                                            class="form-control js-vitto-documentato"
                                                            value="{{ $vittoDocumentatoSegreteria }}"
                                                            {{ $formDisabled ? 'disabled' : '' }}>
                                                    </div>

                                                    <div class="col-12 col-md-3">
                                                        <label class="form-label">Spese varie</label>
                                                        <input type="number" step="0.01" min="0"
                                                            name="spese_varie" class="form-control"
                                                            value="{{ old('spese_varie', $entry->spese_varie ?? null) }}"
                                                            {{ $formDisabled ? 'disabled' : '' }}>
                                                    </div>

                                                    <div class="col-12 col-md-6">
                                                        <label class="form-label">Note spese varie</label>
                                                        <input type="text" name="spese_varie_note"
                                                            class="form-control"
                                                            value="{{ old('spese_varie_note', $entry->spese_varie_note ?? null) }}"
                                                            placeholder="Es. parcheggio, taxi, materiale..."
                                                            {{ $formDisabled ? 'disabled' : '' }}>
                                                    </div>

                                                    <div class="col-12 col-md-6">
                                                        <label class="form-label">Note report crono</label>
                                                        <textarea name="note" class="form-control" rows="2" {{ $formDisabled ? 'disabled' : '' }}>{{ old('note', $entry->note ?? '') }}</textarea>
                                                    </div>

                                                    <div class="col-12">
                                                        <hr>
                                                        <h4 class="h6 mb-2">Ore segreteria per giornata</h4>

                                                        <div class="table-responsive">
                                                            <table class="table table-sm align-middle mb-0">
                                                                <thead class="table-light">
                                                                    <tr>
                                                                        <th>Giornata</th>
                                                                        <th style="width: 220px;">Ore servizio
                                                                            ordinario</th>
                                                                        <th style="width: 220px;">Ore servizio
                                                                            specialistico</th>
                                                                        <th>Stato giornata</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    @foreach ($days as $day)
                                                                        @php
                                                                            $key = $tk->id . '|' . $day;
                                                                            $dayRow = $adminDaysByUserDay->get($key);
                                                                            $isInvolved = $dscDaysByUserDay->has($key);
                                                                        @endphp

                                                                        <tr>
                                                                            <td>
                                                                                {{ ucwords(\Carbon\Carbon::parse($day)->translatedFormat('l d F')) }}
                                                                            </td>

                                                                            <td>
                                                                                <input type="number" step="0.25"
                                                                                    min="0" max="24"
                                                                                    class="form-control"
                                                                                    name="day_admin[{{ $day }}][hours_ordinary_service]"
                                                                                    value="{{ old('day_admin.' . $day . '.hours_ordinary_service', $dayRow->hours_ordinary_service ?? '') }}"
                                                                                    {{ $formDisabled || !$isInvolved ? 'disabled' : '' }}>
                                                                            </td>

                                                                            <td>
                                                                                <input type="number" step="0.25"
                                                                                    min="0" max="24"
                                                                                    class="form-control"
                                                                                    name="day_admin[{{ $day }}][hours_special_service]"
                                                                                    value="{{ old('day_admin.' . $day . '.hours_special_service', $dayRow->hours_special_service ?? '') }}"
                                                                                    {{ $formDisabled || !$isInvolved ? 'disabled' : '' }}>
                                                                            </td>

                                                                            <td>
                                                                                @if ($isInvolved)
                                                                                    <span
                                                                                        class="badge bg-info text-dark">
                                                                                        Inserito dal DSC
                                                                                    </span>
                                                                                @else
                                                                                    <span class="badge bg-secondary">
                                                                                        Non presente quel giorno
                                                                                    </span>
                                                                                @endif
                                                                            </td>
                                                                        </tr>
                                                                    @endforeach
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="d-flex flex-wrap gap-2 mt-3">
                                                    <button type="submit" class="btn btn-primary"
                                                        {{ $formDisabled ? 'disabled' : '' }}>
                                                        <i class="fas fa-save me-1"></i> Salva dati di questo crono
                                                    </button>

                                                    @if ($entryClosed)
                                                        <span class="badge bg-success align-self-center">
                                                            <i class="fas fa-lock me-1"></i> Report chiuso dalla
                                                            segreteria
                                                        </span>
                                                    @elseif ($raceClosedBySecretariat)
                                                        <span class="badge bg-success align-self-center">
                                                            <i class="fas fa-lock me-1"></i> Gara chiusa dalla
                                                            segreteria
                                                        </span>
                                                    @endif
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-muted">Nessun cronometrista assegnato alla gara.</div>
                            @endforelse
                        </div>
                    </div>
                </div>

                {{-- TABELLA RIEPILOGO --}}
                <div class="card tk-card p-3">
                    <div class="card-header tk-card-header">
                        <i class="fas fa-list-ul me-2"></i> Riepilogo Report (gara)
                    </div>

                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped align-middle mb-0 table-vertical-separators">
                                <thead class="table-light">
                                    <tr>
                                        <th>Crono</th>
                                        <th>Km</th>
                                        <th>Coeff Km</th>
                                        <th>Importo Km (sistema)</th>
                                        <th>Pedaggi</th>
                                        <th>Vitto</th>
                                        <th>Spese varie</th>
                                        <th>Note spese</th>
                                        <th>Mancati pasti</th>
                                        <th>Dettaglio pasti</th>
                                        <th>Imp. mancati</th>
                                        <th>Furgone (sistema)</th>
                                        <th>Totale (sistema)</th>
                                        <th>Note</th>
                                        <th>Allegati</th>
                                        <th>Stato DSC</th>
                                        <th>Stato segreteria</th>
                                        <th>Azioni</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @forelse ($rows as $row)
                                        @php
                                            $entry = $row['entry'];
                                            $sys = $row['sys'];
                                        @endphp

                                        <tr>
                                            <td>{{ $row['user']->surname }} {{ $row['user']->name }}</td>

                                            <td>{{ number_format((float) ($entry->km ?? 0), 2) }}</td>
                                            <td>{{ number_format((float) ($sys['coeffKm'] ?? 0), 4) }}</td>
                                            <td>{{ number_format((float) ($sys['kmAmount'] ?? 0), 2) }}</td>

                                            <td>{{ number_format((float) ($entry->pedaggi ?? 0), 2) }}</td>
                                            <td>{{ number_format((float) ($entry->vitto ?? 0), 2) }}</td>
                                            <td>{{ number_format((float) ($entry->spese_varie ?? 0), 2) }}</td>
                                            <td>{{ $entry->spese_varie_note ?? '—' }}</td>

                                            <td>{{ $countMissedMealsForUser($row['user']->id) }}</td>
                                            <td>{{ $formatMissedMealsDetail($row['user']->id) }}</td>
                                            <td>{{ number_format($amountMissedMealsForUser($row['user']->id), 2) }}
                                            </td>

                                            <td>{{ number_format((float) ($sys['vanCostApplied'] ?? 0), 2) }}</td>

                                            @php
                                                $rowSystemTotal =
                                                    (float) ($sys['total'] ?? ($sys['totalRacePart'] ?? 0));
                                            @endphp
                                            <td><strong>{{ number_format($rowSystemTotal, 2) }}</strong></td>

                                            <td>{{ $entry->note ?? '—' }}</td>

                                            <td>
                                                @if ($entry->attachments && $entry->attachments->count())
                                                    <ul class="list-unstyled mb-0">
                                                        @foreach ($entry->attachments as $a)
                                                            <li>
                                                                <a href="{{ asset('storage/' . $a->file_path) }}"
                                                                    target="_blank" rel="noopener"
                                                                    download="{{ $a->original_name ?: basename($a->file_path) }}">
                                                                    <i class="fas fa-paperclip me-1"></i>
                                                                    {{ $a->original_name ?: basename($a->file_path) }}
                                                                </a>
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>

                                            <td class="text-nowrap">
                                                @if ($entry->confirmed)
                                                    <span class="badge bg-success">
                                                        <i class="fas fa-check-circle me-1"></i> Confermato DSC
                                                    </span>
                                                @else
                                                    <span class="badge bg-secondary">Non confermato DSC</span>
                                                @endif
                                            </td>

                                            <td class="text-nowrap">
                                                @if ($entry->secretariat_confirmed)
                                                    <span class="badge bg-success">
                                                        <i class="fas fa-lock me-1"></i> Chiuso
                                                    </span>
                                                @else
                                                    <span class="badge bg-warning text-dark">
                                                        Modificabile
                                                    </span>
                                                @endif
                                            </td>

                                            <td class="text-nowrap">
                                                @if ($entry->exists ?? false)
                                                    <button type="button" class="btn btn-sm btn-primary"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#editReportEntryModal_{{ $entry->id }}"
                                                        {{ $entry->secretariat_confirmed ? 'disabled' : '' }}>
                                                        Modifica
                                                    </button>

                                                    <form method="POST"
                                                        action="{{ route('secretariat.records.confirm', $entry) }}"
                                                        class="d-inline"
                                                        onsubmit="return confirm('Confermare definitivamente questo report? Dopo non sarà più modificabile da nessuno.');">
                                                        @csrf
                                                        <input type="hidden" name="day"
                                                            value="{{ $selectedDay }}">
                                                        <button type="submit" class="btn btn-sm btn-success"
                                                            {{ $entry->secretariat_confirmed ? 'disabled' : '' }}>
                                                            Conferma
                                                        </button>
                                                    </form>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                        </tr>

                                    @empty
                                        <tr>
                                            <td colspan="18" class="text-center text-muted p-4">
                                                Nessun dato disponibile.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>

                            </table>
                        </div>
                    </div>
                </div>

                {{-- MODALI MODIFICA REPORT
                    Tenute fuori dalla tabella per evitare HTML non valido dentro <tbody>
                    e sfarfallii/aperture instabili di Bootstrap.
                --}}
                @foreach ($rows as $modalRow)
                    @php
                        $row = $modalRow;
                        $entry = $row['entry'];
                    @endphp

                    @if ($entry->exists ?? false)
                        <div class="modal fade" id="editReportEntryModal_{{ $entry->id }}" tabindex="-1"
                            aria-hidden="true" aria-labelledby="editReportEntryModal_{{ $entry->id }}Label">
                            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 id="editReportEntryModal_{{ $entry->id }}Label" class="modal-title">
                                            Modifica report — {{ $row['user']->surname }}
                                            {{ $row['user']->name }}
                                        </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Chiudi"></button>
                                    </div>

                                    <form method="POST" action="{{ route('secretariat.records.update', $entry) }}">
                                        @csrf
                                        @method('PUT')
                                        <input type="hidden" name="day" value="{{ $selectedDay }}">

                                        <div class="modal-body">
                                            @if ($entry->secretariat_confirmed)
                                                <div class="alert alert-success mb-0">
                                                    Report già confermato dalla segreteria: non è
                                                    più modificabile.
                                                </div>
                                            @else
                                                <div class="row g-3">
                                                    <div class="col-12 col-md-4">
                                                        <label class="form-label">Km</label>
                                                        <input type="number" step="0.01" min="0"
                                                            name="km" class="form-control"
                                                            value="{{ $entry->km }}">
                                                    </div>

                                                    <div class="col-12 col-md-4">
                                                        <label class="form-label">Pedaggi /
                                                            Trasporto</label>
                                                        <input type="number" step="0.01" min="0"
                                                            name="pedaggi" class="form-control"
                                                            value="{{ $entry->pedaggi }}">
                                                    </div>

                                                    @php
                                                        $vittoTipoModal = old('vitto_tipo');

                                                        if ($vittoTipoModal === null) {
                                                            if ($entry->vitto === null) {
                                                                $vittoTipoModal = '';
                                                            } elseif ((float) $entry->vitto === 15.0) {
                                                                $vittoTipoModal = 'forfettario';
                                                            } elseif ((float) $entry->vitto === 0.0) {
                                                                $vittoTipoModal = 'offerto';
                                                            } else {
                                                                $vittoTipoModal = 'documentato';
                                                            }
                                                        }

                                                        $vittoDocumentatoModal = old('vitto_documentato');

                                                        if (
                                                            $vittoDocumentatoModal === null &&
                                                            $vittoTipoModal === 'documentato'
                                                        ) {
                                                            $vittoDocumentatoModal = $entry->vitto;
                                                        }
                                                    @endphp

                                                    <div class="col-12 col-md-4">
                                                        <label class="form-label">Vitto</label>
                                                        <select name="vitto_tipo" class="form-select js-vitto-tipo">
                                                            <option value="">-- Seleziona --
                                                            </option>
                                                            <option value="forfettario"
                                                                {{ $vittoTipoModal === 'forfettario' ? 'selected' : '' }}>
                                                                Forfettario - 15€
                                                            </option>
                                                            <option value="offerto"
                                                                {{ $vittoTipoModal === 'offerto' ? 'selected' : '' }}>
                                                                Offerto - 0€
                                                            </option>
                                                            <option value="documentato"
                                                                {{ $vittoTipoModal === 'documentato' ? 'selected' : '' }}>
                                                                Documentato
                                                            </option>
                                                        </select>
                                                    </div>

                                                    <div class="col-12 col-md-4 js-vitto-documentato-wrap"
                                                        style="display:none;">
                                                        <label class="form-label">Importo vitto
                                                            documentato</label>
                                                        <input type="number" step="0.01" min="0"
                                                            name="vitto_documentato"
                                                            class="form-control js-vitto-documentato"
                                                            value="{{ $vittoDocumentatoModal }}">
                                                    </div>

                                                    <div class="col-12 col-md-4">
                                                        <label class="form-label">Spese
                                                            varie</label>
                                                        <input type="number" step="0.01" min="0"
                                                            name="spese_varie" class="form-control"
                                                            value="{{ $entry->spese_varie }}">
                                                    </div>

                                                    <div class="col-12 col-md-8">
                                                        <label class="form-label">Note spese
                                                            varie</label>
                                                        <input type="text" name="spese_varie_note"
                                                            class="form-control"
                                                            value="{{ $entry->spese_varie_note }}"
                                                            placeholder="Es. parcheggio, taxi, materiale...">
                                                    </div>

                                                    <div class="col-12">
                                                        <label class="form-label">Note</label>
                                                        <textarea name="note" class="form-control" rows="3">{{ $entry->note }}</textarea>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>

                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary"
                                                data-bs-dismiss="modal">Annulla</button>
                                            <button type="submit" class="btn btn-primary"
                                                {{ $entry->secretariat_confirmed ? 'disabled' : '' }}>
                                                Salva modifiche
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach


                <a href="{{ route('secretariat.races.reportFull', $race) }}" class="btn btn-outline-primary">
                    <i class="fas fa-file-alt me-1"></i> Apri Report Completo
                </a>


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
