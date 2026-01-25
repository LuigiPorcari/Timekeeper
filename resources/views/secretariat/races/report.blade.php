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
                                <div class="col-12 col-md-3">
                                    <div class="fw-bold">Furgone</div>
                                    <div>{{ $dscRace->van_needed ? 'Sì' : 'No' }}</div>
                                </div>
                                <div class="col-12 col-md-3">
                                    <div class="fw-bold">Mancati pasti</div>
                                    <div>{{ (int) ($dscRace->missed_meals ?? 0) }}</div>
                                </div>
                                <div class="col-12 col-md-6">
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
                                        class="form-control" value="{{ old('van_cost', $settings->van_cost ?? '') }}">
                                </div>

                                <div class="col-12 col-md-3">
                                    <label class="form-label">Coefficiente Kilometrico</label>
                                    <input type="number" step="0.0001" min="0" name="coeff_km"
                                        class="form-control"
                                        value="{{ old('coeff_km', $settings->coeff_km ?? 0.36) }}">
                                </div>

                                <div class="col-12 col-md-3">
                                    <label class="form-label">Contributo organizzativo (gara)</label>
                                    <input type="number" step="0.01" min="0" name="contributo_organizzativo"
                                        class="form-control"
                                        value="{{ old('contributo_organizzativo', $settings->contributo_organizzativo ?? '') }}">
                                </div>

                                <div class="col-12 col-md-3">
                                    <label class="form-label">Spese varie (gara)</label>
                                    <input type="number" step="0.01" min="0" name="spese_varie_gara"
                                        class="form-control"
                                        value="{{ old('spese_varie_gara', $settings->spese_varie_gara ?? '') }}">
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Note apparecchiature (gara)</label>
                                    <textarea name="apparecchiature_note" class="form-control" rows="2"
                                        placeholder="Indica quali apparecchiature sono state usate...">{{ old('apparecchiature_note', $settings->apparecchiature_note ?? '') }}</textarea>
                                </div>
                            </div>

                            <button class="btn btn-primary mt-3" type="submit">
                                <i class="fas fa-save me-1"></i>
                                {{ $settings ? 'Modifica' : 'Salva' }} dati Segreteria (gara)
                            </button>
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
                                            @foreach ($rows as $r)
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
                                                            value="{{ old("hours_ordinary_service.$uid", $dayRow->hours_ordinary_service ?? '') }}">
                                                    </td>

                                                    <td>
                                                        <input type="number" step="0.25" min="0"
                                                            max="24" class="form-control"
                                                            name="hours_special_service[{{ $uid }}]"
                                                            value="{{ old("hours_special_service.$uid", $dayRow->hours_special_service ?? '') }}">
                                                    </td>

                                                    <td class="text-muted">
                                                        {{ $dayRow?->updated_at ? $dayRow->updated_at->format('d/m/Y H:i') : '—' }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                <button class="btn btn-primary mt-3" type="submit">
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
                                        <th>Alloggio</th>
                                        <th>Spese varie</th>
                                        <th>Mancati pasti (gara)</th>
                                        <th>Imp. mancati (sistema)</th>
                                        <th>Furgone (sistema)</th>
                                        <th>Totale (sistema)</th>
                                        <th>Note</th>
                                        <th>Allegati</th>
                                        <th>Stato</th>
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
                                            <td>{{ number_format((float) ($entry->alloggio ?? 0), 2) }}</td>
                                            <td>{{ number_format((float) ($entry->spese_varie ?? 0), 2) }}</td>

                                            <td>{{ (int) ($sys['missedMeals'] ?? 0) }}</td>
                                            <td>{{ number_format((float) ($sys['missedMealsAmount'] ?? 0), 2) }}</td>

                                            <td>{{ number_format((float) ($sys['vanCostApplied'] ?? 0), 2) }}</td>

                                            <td><strong>{{ number_format((float) ($sys['total'] ?? 0), 2) }}</strong>
                                            </td>

                                            <td>{{ $entry->note ?? '—' }}</td>

                                            <td>
                                                @if ($entry->attachments && $entry->attachments->count())
                                                    <ul class="list-unstyled mb-0">
                                                        @foreach ($entry->attachments as $a)
                                                            <li>
                                                                <a href="{{ asset('storage/' . $a->file_path) }}"
                                                                    target="_blank">
                                                                    {{ $a->original_name }}
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
                                                        <i class="fas fa-check-circle me-1"></i> Confermato
                                                    </span>
                                                @else
                                                    <span class="badge bg-secondary">Non confermato</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="15" class="text-center text-muted p-4">
                                                Nessun dato disponibile.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>

                            </table>
                        </div>
                    </div>
                </div>

                <a href="{{ route('secretariat.races.reportFull', $race) }}" class="btn btn-outline-primary">
                    <i class="fas fa-file-alt me-1"></i> Apri Report Completo
                </a>


            </div>
        </div>
    </main>
</x-layout>
