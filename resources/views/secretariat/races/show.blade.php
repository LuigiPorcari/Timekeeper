<x-layout documentTitle="Segreteria — Dettaglio Gara">
    <main class="container-fluid mt-5 pt-5" aria-labelledby="race-title">
        <h1 id="race-title" class="mb-1">{{ $race->name }}</h1>
        <p class="text-muted mb-4">
            {{ \Illuminate\Support\Carbon::parse($race->date_of_race)->translatedFormat('l d F') }}
            @if ($race->date_end)
                / {{ \Illuminate\Support\Carbon::parse($race->date_end)->translatedFormat('l d F') }}
            @endif
            - {{ $race->place }}
        </p>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Chiudi"></button>
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Chiudi"></button>
            </div>
        @endif

        @php
            // Km del DSC per fallback
            $dscKm = null;
            foreach ($rows as $rowTmp) {
                $recTmp = $rowTmp['record'] ?? null;
                if ($recTmp && $recTmp->user && $recTmp->user->isLeaderOf($race)) {
                    if (!is_null($recTmp->km_documented) && $recTmp->km_documented !== '') {
                        $dscKm = (float) $recTmp->km_documented;
                    }
                    break;
                }
            }

            $computedGrandTotal = 0.0;

            // Mappa per mostrare solo il nome "umano" delle specializzazioni (senza tipo)
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

            $prettySpec = function ($ns) use ($nsToLabel) {
                if (isset($nsToLabel[$ns])) {
                    return $nsToLabel[$ns];
                }
                if (is_string($ns) && str_contains($ns, '__')) {
                    [, $ns] = explode('__', $ns, 2);
                }
                $ns = str_replace(['_', '-'], ' ', (string) $ns);
                return ucwords($ns);
            };
        @endphp

        <div class="card shadow-sm p-3">
            <div class="card-header bg-white">
                <strong>Dettaglio rendiconti</strong>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-sm mb-0 tk-table-separated">
                        <thead class="table-light align-middle">
                            <tr>
                                <th scope="col" rowspan="2">Operatore</th>
                                <th scope="col" rowspan="2">Tipo</th>
                                <th scope="col" rowspan="2">€/Km</th>
                                <th scope="col" rowspan="2">Serv. Giornaliero</th>
                                <th scope="col" rowspan="2">Serv. Speciale</th>
                                <th scope="col" rowspan="2">Tariffa</th>
                                <th scope="col" rowspan="2">Km (usati)</th>
                                <th scope="col" rowspan="2">Importo Km</th>
                                <th scope="colgroup" colspan="4" class="text-center">Spesa documentata</th>
                                <th scope="colgroup" colspan="3" class="text-center">Spesa non documentata</th>
                                <th scope="col" rowspan="2">Totale</th>
                                <th scope="col" rowspan="2">Stato</th>
                                <th scope="col" rowspan="2">Azioni</th>
                            </tr>
                            <tr>
                                {{-- sotto-colonne gruppi --}}
                                <th scope="col">Bigl.</th>
                                <th scope="col">Vitto</th>
                                <th scope="col">Alloggio</th>
                                <th scope="col">Varie</th>
                                <th scope="col">Vitto</th>
                                <th scope="col">Diaria</th>
                                <th scope="col">Diaria spec.</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($rows as $row)
                                @php
                                    /** @var \App\Models\Record $r */
                                    $r = $row['record'];

                                    $ratePerKm = $r->euroKM !== null ? (float) $r->euroKM : 0.36;

                                    $kmRaw = (float) ($r->km_documented ?? 0);
                                    $kmUsed = $kmRaw > 0 ? $kmRaw : ((float) ($dscKm ?? 0));

                                    $kmAmount = $kmUsed > 0 ? round($kmUsed * $ratePerKm, 2) : 0.0;

                                    // TOTALE DI RIGA:
                                    // Importo Km + spese documentate + SOLO vitto non documentato
                                    $rowTotal =
                                        $kmAmount +
                                        (float) ($r->travel_ticket_documented ?? 0) +
                                        (float) ($r->food_documented ?? 0) +
                                        (float) ($r->accommodation_documented ?? 0) +
                                        (float) ($r->various_documented ?? 0) +
                                        (float) ($r->food_not_documented ?? 0);

                                    $computedGrandTotal += $rowTotal;

                                    $modalId = 'editRecordModal_' . $r->id;

                                    // Apparecchiature / specializzazioni usate
                                    $appsRaw = is_array($r->apparecchiature ?? null) ? $r->apparecchiature : [];
                                    $apps = array_filter(array_map($prettySpec, $appsRaw));
                                    $appsLabel = $apps ? implode(', ', $apps) : '—';
                                @endphp

                                {{-- Riga principale --}}
                                <tr>
                                    <td>{{ $r->user->surname }} {{ $r->user->name }}</td>
                                    <td>{{ $r->type ?? '—' }}</td>
                                    <td>{{ number_format($ratePerKm, 2, ',', '.') }}</td>
                                    <td>{{ $r->daily_service }}</td>
                                    <td>{{ $r->special_service }}</td>
                                    <td>{{ $r->rate_documented }}</td>
                                    <td>{{ $kmUsed }}</td>
                                    <td>{{ number_format($kmAmount, 2, ',', '.') }}</td>

                                    {{-- Documentate --}}
                                    <td>{{ $r->travel_ticket_documented }}</td>
                                    <td>{{ $r->food_documented }}</td>
                                    <td>{{ $r->accommodation_documented }}</td>
                                    <td>{{ $r->various_documented }}</td>

                                    {{-- NON Documentate --}}
                                    <td>{{ $r->food_not_documented }}</td>
                                    <td>{{ $r->daily_allowances_not_documented }}</td>
                                    <td>{{ $r->special_daily_allowances_not_documented }}</td>

                                    <td><strong>{{ number_format($rowTotal, 2, ',', '.') }}</strong></td>

                                    <td>
                                        @if ($r->confirmed)
                                            <span class="badge bg-success">Confermato</span>
                                        @else
                                            <span class="badge bg-warning text-dark">Da confermare</span>
                                        @endif
                                    </td>

                                    <td class="text-nowrap">
                                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal"
                                            data-bs-target="#{{ $modalId }}">
                                            Modifica
                                        </button>
                                    </td>
                                </tr>

                                {{-- Riga secondaria: Specializzazioni + Descrizione + Allegati --}}
                                <tr class="bg-light">
                                    <td colspan="18">
                                        <div class="py-2">
                                            <div class="mb-1">
                                                <strong>Specializzazioni / Apparecchiature:</strong>
                                                <span class="text-break">{{ $appsLabel }}</span>
                                            </div>
                                            <div class="mb-1">
                                                <strong>Descrizione:</strong>
                                                <span class="text-break">{{ $r->description ?: '—' }}</span>
                                            </div>
                                            <div>
                                                <strong>Allegati:</strong>
                                                @if ($r->attachments && $r->attachments->count())
                                                    <ul class="mb-0 list-unstyled d-inline">
                                                        @foreach ($r->attachments as $att)
                                                            <li class="d-inline me-2">
                                                                <a href="{{ route('attachments.show', $att) }}"
                                                                    target="_blank" rel="noopener">
                                                                    {{ $att->original_name }}
                                                                </a>
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                @else
                                                    <span class="text-muted">Nessuno</span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                </tr>

                                {{-- MODALE MODIFICA RECORD --}}
                                <div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-hidden="true"
                                    aria-labelledby="{{ $modalId }}Label">
                                    <div class="modal-dialog modal-lg modal-dialog-scrollable">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 id="{{ $modalId }}Label" class="modal-title">
                                                    Modifica record — {{ $r->user->surname }} {{ $r->user->name }}
                                                </h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                    aria-label="Chiudi"></button>
                                            </div>

                                            <form method="POST"
                                                action="{{ route('secretariat.records.update', $r) }}">
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-body">
                                                    <div class="row g-3">
                                                        <div class="col-12 col-md-4">
                                                            <label class="form-label">Tipo *</label>
                                                            <select name="type" class="form-select" required>
                                                                <option value="FC" @selected($r->type === 'FC')>FC
                                                                    — Fuori città</option>
                                                                <option value="CM" @selected($r->type === 'CM')>CM
                                                                    — Comunale</option>
                                                                <option value="CP" @selected($r->type === 'CP')>CP
                                                                    — Provinciale</option>
                                                            </select>
                                                        </div>

                                                        <div class="col-12 col-md-4">
                                                            <label class="form-label">€/Km</label>
                                                            <input type="text" name="euroKM" inputmode="decimal"
                                                                pattern="^\d{1,6}([,.]\d{1,2})?$" class="form-control"
                                                                value="{{ $r->euroKM !== null ? str_replace('.', ',', (string) $r->euroKM) : '' }}"
                                                                placeholder="es. 0,36">
                                                            <small class="text-muted">Virgola o punto, max 2
                                                                decimali.</small>
                                                        </div>

                                                        <div class="col-12 col-md-4">
                                                            <label class="form-label">Km (effettivi)</label>
                                                            <input type="number" step="any" name="km_documented"
                                                                class="form-control" value="{{ $r->km_documented }}">
                                                        </div>

                                                        <div class="col-12 col-md-4">
                                                            <label class="form-label">Servizio giornaliero</label>
                                                            <input type="number" step="1" name="daily_service"
                                                                class="form-control" value="{{ $r->daily_service }}">
                                                        </div>
                                                        <div class="col-12 col-md-4">
                                                            <label class="form-label">Servizio speciale</label>
                                                            <input type="number" step="1"
                                                                name="special_service" class="form-control"
                                                                value="{{ $r->special_service }}">
                                                        </div>
                                                        <div class="col-12 col-md-4">
                                                            <label class="form-label">Tariffa</label>
                                                            <input type="text" name="rate_documented"
                                                                class="form-control"
                                                                value="{{ $r->rate_documented }}">
                                                        </div>

                                                        <div class="col-12">
                                                            <h6 class="mt-2 mb-1">Spese documentate</h6>
                                                        </div>
                                                        <div class="col-12 col-md-3">
                                                            <label class="form-label">Biglietto</label>
                                                            <input type="number" step="any"
                                                                name="travel_ticket_documented" class="form-control"
                                                                value="{{ $r->travel_ticket_documented }}">
                                                        </div>
                                                        <div class="col-12 col-md-3">
                                                            <label class="form-label">Vitto</label>
                                                            <input type="number" step="any"
                                                                name="food_documented" class="form-control"
                                                                value="{{ $r->food_documented }}">
                                                        </div>
                                                        <div class="col-12 col-md-3">
                                                            <label class="form-label">Alloggio</label>
                                                            <input type="number" step="any"
                                                                name="accommodation_documented" class="form-control"
                                                                value="{{ $r->accommodation_documented }}">
                                                        </div>
                                                        <div class="col-12 col-md-3">
                                                            <label class="form-label">Varie</label>
                                                            <input type="number" step="any"
                                                                name="various_documented" class="form-control"
                                                                value="{{ $r->various_documented }}">
                                                        </div>

                                                        <div class="col-12">
                                                            <h6 class="mt-2 mb-1">Spese non documentate</h6>
                                                        </div>
                                                        <div class="col-12 col-md-4">
                                                            <label class="form-label">Vitto (ND)</label>
                                                            <input type="number" step="any"
                                                                name="food_not_documented" class="form-control"
                                                                value="{{ $r->food_not_documented }}">
                                                        </div>
                                                        <div class="col-12 col-md-4">
                                                            <label class="form-label">Diaria (ND)</label>
                                                            <input type="number" step="any"
                                                                name="daily_allowances_not_documented"
                                                                class="form-control"
                                                                value="{{ $r->daily_allowances_not_documented }}">
                                                        </div>
                                                        <div class="col-12 col-md-4">
                                                            <label class="form-label">Diaria speciale (ND)</label>
                                                            <input type="number" step="any"
                                                                name="special_daily_allowances_not_documented"
                                                                class="form-control"
                                                                value="{{ $r->special_daily_allowances_not_documented }}">
                                                        </div>

                                                        <div class="col-12">
                                                            <label class="form-label">Descrizione</label>
                                                            <textarea name="description" rows="3" class="form-control">{{ $r->description }}</textarea>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary"
                                                        data-bs-dismiss="modal">Annulla</button>
                                                    <button type="submit" class="btn btn-primary">Salva
                                                        modifiche</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                {{-- /MODALE --}}
                            @empty
                                <tr>
                                    <td colspan="18" class="text-center text-muted">Nessun record.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr>
                                {{-- totale colonne = 18 --}}
                                <th colspan="15" class="text-end">Totale complessivo</th>
                                <th>{{ number_format($computedGrandTotal, 2, ',', '.') }}</th>
                                <th colspan="2"></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <div class="card-footer d-flex justify-content-end">
                <a href="{{ route('secretariat.races.index') }}" class="btn btn-secondary">Torna all’elenco</a>
            </div>
        </div>
    </main>
</x-layout>
