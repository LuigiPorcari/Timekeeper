{{-- resources/views/secretariat/races/partials/report_full_table.blade.php --}}

@php
    /**
     * REQUIRED (passati dal controller o da include):
     * - $race
     * - $raceDaysCount
     * - $days (array Y-m-d)
     * - $dscRace
     * - $settings
     * - $rows
     *
     * OPTIONAL:
     * - $backUrl (string)
     * - $uid (string) -> serve per rendere unici gli id DOM (search/tbody/buttons)
     */
    $uid = $uid ?? 'r' . ($race->id ?? '0');

    $startDate = \Carbon\Carbon::parse($race->date_of_race);
    $endDate = $race->date_end ? \Carbon\Carbon::parse($race->date_end) : $startDate->copy();

    $appsDsc = $dscRace->apparecchiature ?? [];
    $missedMeals = (int) ($dscRace->missed_meals ?? 0);
    $missedMealsAmount = $missedMeals * 15;

    $vanNeeded = (bool) ($dscRace->van_needed ?? false);

    $vanCostRace = $settings && $settings->van_cost !== null ? (float) $settings->van_cost : 0.0;
    $coeffKm = $settings && $settings->coeff_km !== null ? (float) $settings->coeff_km : 0.36;

    $contributoOrganizzativo =
        $settings && $settings->contributo_organizzativo !== null ? (float) $settings->contributo_organizzativo : 0.0;

    $speseVarieGara = $settings && $settings->spese_varie_gara !== null ? (float) $settings->spese_varie_gara : 0.0;

    $noteAppGara = $settings->apparecchiature_note ?? '';

    $daysCount = is_array($days) ? count($days) : 0;
@endphp

<style>
    /* ===== UX tabella grande ===== */
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
        /* viene impostato da JS */
    }

    /* IMPORTANT: elimina "spazi bianchi" tra righe sticky in alcuni browser */
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

    /* ===== Sticky prime 2 colonne ===== */
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

    .controls-bar .form-check {
        margin-bottom: .25rem;
    }

    /* Barra meta (fuori dallo scroll orizzontale) */
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
        .alert,
        .report-meta-bar {
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
    <div class="card-header tk-card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <i class="fas fa-table me-2"></i> Riepilogo completo (Crono × Giorno)
        </div>
        <span class="badge bg-light text-dark">
            Giorni gara: <strong>{{ (int) ($raceDaysCount ?? 1) }}</strong>
        </span>
    </div>

    <div class="card-body">
        {{-- CONTROLLI: ricerca + toggle gruppi colonne --}}
        <div class="controls-bar mb-3">
            <div class="row g-2 align-items-end">
                <div class="col-12 col-lg-5">
                    <label for="reportSearch-{{ $uid }}" class="form-label mb-1">Ricerca nella tabella</label>
                    <input id="reportSearch-{{ $uid }}" type="search" class="form-control"
                        placeholder="Es. Rossi, domicilio, note, luogo, ecc.">
                    <div class="form-text">Filtra le righe mentre scrivi (non modifica i dati).</div>
                </div>

                <div class="col-12 col-lg-7">
                    <div class="d-flex flex-wrap gap-3">
                        <div>
                            <div class="fw-semibold mb-1">Mostra/Nascondi gruppi</div>

                            <div class="d-flex flex-wrap gap-3">
                                <div class="form-check">
                                    <input class="form-check-input col-toggle-{{ $uid }}" type="checkbox"
                                        id="tgOrari-{{ $uid }}" data-group="g-orari" checked>
                                    <label class="form-check-label" for="tgOrari-{{ $uid }}">Orari</label>
                                </div>

                                <div class="form-check">
                                    <input class="form-check-input col-toggle-{{ $uid }}" type="checkbox"
                                        id="tgOrd-{{ $uid }}" data-group="g-ord" checked>
                                    <label class="form-check-label" for="tgOrd-{{ $uid }}">Ordinario</label>
                                </div>

                                <div class="form-check">
                                    <input class="form-check-input col-toggle-{{ $uid }}" type="checkbox"
                                        id="tgSpec-{{ $uid }}" data-group="g-spec" checked>
                                    <label class="form-check-label"
                                        for="tgSpec-{{ $uid }}">Specialistico</label>
                                </div>

                                <div class="form-check">
                                    <input class="form-check-input col-toggle-{{ $uid }}" type="checkbox"
                                        id="tgDsc-{{ $uid }}" data-group="g-dsc" checked>
                                    <label class="form-check-label" for="tgDsc-{{ $uid }}">DSC</label>
                                </div>

                                <div class="form-check">
                                    <input class="form-check-input col-toggle-{{ $uid }}" type="checkbox"
                                        id="tgSegr-{{ $uid }}" data-group="g-segr" checked>
                                    <label class="form-check-label" for="tgSegr-{{ $uid }}">Segreteria</label>
                                </div>

                                <div class="form-check">
                                    <input class="form-check-input col-toggle-{{ $uid }}" type="checkbox"
                                        id="tgSpese-{{ $uid }}" data-group="g-spese" checked>
                                    <label class="form-check-label" for="tgSpese-{{ $uid }}">Spese</label>
                                </div>

                                <div class="form-check">
                                    <input class="form-check-input col-toggle-{{ $uid }}" type="checkbox"
                                        id="tgTot-{{ $uid }}" data-group="g-totali" checked>
                                    <label class="form-check-label" for="tgTot-{{ $uid }}">Totali</label>
                                </div>

                                <div class="form-check">
                                    <input class="form-check-input col-toggle-{{ $uid }}" type="checkbox"
                                        id="tgNote-{{ $uid }}" data-group="g-note" checked>
                                    <label class="form-check-label"
                                        for="tgNote-{{ $uid }}">Note/Allegati/Stato</label>
                                </div>
                            </div>
                        </div>

                        <div class="ms-auto d-flex gap-2">
                            <button class="btn btn-outline-secondary btn-sm" type="button"
                                id="btnAllOn-{{ $uid }}">
                                Tutto ON
                            </button>
                            <button class="btn btn-outline-secondary btn-sm" type="button"
                                id="btnEssentials-{{ $uid }}">
                                Solo essenziali
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <div class="card-body p-0">
        {{-- META sopra la tabella --}}
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

        <div class="table-wrap" role="region" aria-label="Tabella report completo" tabindex="0">
            <table class="table table-striped table-hover table-sm align-middle mb-0 big-report-table">
                <thead class="table-light">

                    {{-- RIGA GRUPPI --}}
                    <tr class="group-row">
                        <th colspan="2" class="sep-right sticky-col-1 z-3"></th>

                        <th colspan="6" class="z-0 g-orari sep-right text-center">Orari</th>
                        <th colspan="3" class="z-0 g-ord sep-right text-center">Ordinario</th>
                        <th colspan="3" class="z-0 g-spec sep-right text-center">Specialistico</th>
                        <th colspan="1" class="z-0 g-orari sep-right text-center">Tot. servizio</th>

                        <th colspan="4" class="z-0 g-dsc sep-right text-center">DSC</th>
                        <th colspan="5" class="z-0 g-segr sep-right text-center">Segreteria (gara)</th>
                        <th colspan="6" class="z-0 g-spese sep-right text-center">Spese (crono)</th>

                        <th colspan="3" class="z-0 g-totali sep-right text-center">Totali</th>
                        <th colspan="3" class="z-0 g-note text-center">Note / Allegati / Stato</th>
                    </tr>

                    {{-- HEADER COLONNE --}}
                    <tr class="header-row">
                        <th class="z-3 sticky-col-1 w-col-crono">Crono</th>
                        <th class="z-3 sticky-col-2 w-col-dom sep-right">Domicilio</th>

                        <th class="z-0 g-orari nowrap">Giorno</th>
                        <th class="z-0 g-orari nowrap">Inizio matt.</th>
                        <th class="z-0 g-orari nowrap">Fine matt.</th>
                        <th class="z-0 g-orari nowrap">Inizio pom.</th>
                        <th class="z-0 g-orari nowrap">Fine pom.</th>
                        <th class="z-0 g-orari nowrap sep-right">Ore lav.</th>

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
                        <th class="z-0 g-segr nowrap">Spese varie</th>
                        <th class="z-0 g-segr nowrap sep-right">Note app.</th>

                        <th class="z-0 g-spese nowrap">Km</th>
                        <th class="z-0 g-spese nowrap">Imp. Km</th>
                        <th class="z-0 g-spese nowrap">Pedaggi</th>
                        <th class="z-0 g-spese nowrap">Vitto</th>
                        <th class="z-0 g-spese nowrap">Alloggio</th>
                        <th class="z-0 g-spese nowrap sep-right">Spese varie</th>

                        <th class="z-0 g-totali nowrap">Tot. parte gara</th>
                        <th class="z-0 g-totali nowrap">TotaleCrono</th>
                        <th class="z-0 g-totali nowrap sep-right">Grand total</th>

                        <th class="z-0 g-note nowrap">Note crono</th>
                        <th class="z-0 g-note nowrap">Allegati</th>
                        <th class="z-0 g-note nowrap">Stato</th>
                    </tr>
                </thead>

                <tbody id="reportTbody-{{ $uid }}">
                    @forelse ($rows as $row)
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

                        @foreach ($days as $i => $day)
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

                                $dayLabel = ucwords(\Carbon\Carbon::parse($day)->translatedFormat('l d F'));
                            @endphp

                            <tr class="report-row">
                                @if ($i === 0)
                                    <td rowspan="{{ $rowspan }}" class="fw-bold sticky-col-1 w-col-crono clip"
                                        title="{{ $u->surname }} {{ $u->name }}">
                                        {{ $u->surname }} {{ $u->name }}
                                    </td>

                                    <td rowspan="{{ $rowspan }}" class="sticky-col-2 w-col-dom clip sep-right"
                                        title="{{ $u->domicile ?? '' }}">
                                        {{ $u->domicile ?? '—' }}
                                    </td>
                                @endif

                                <td class="g-orari nowrap">{{ $dayLabel }}</td>
                                <td class="g-orari nowrap">{{ $dscDay?->morning_start ?? '—' }}</td>
                                <td class="g-orari nowrap">{{ $dscDay?->morning_end ?? '—' }}</td>
                                <td class="g-orari nowrap">{{ $dscDay?->afternoon_start ?? '—' }}</td>
                                <td class="g-orari nowrap">{{ $dscDay?->afternoon_end ?? '—' }}</td>
                                <td class="g-orari fw-semibold num sep-right">{{ number_format($workedHours, 2) }}
                                </td>

                                <td class="g-ord num">{{ number_format($ordHours, 2) }}</td>
                                <td class="g-ord num">{{ number_format($ordRate, 2) }}</td>
                                <td class="g-ord num sep-right">{{ number_format($ordAmount, 2) }}</td>

                                <td class="g-spec num">{{ number_format($specHours, 2) }}</td>
                                <td class="g-spec num">{{ number_format($specRate, 2) }}</td>
                                <td class="g-spec num sep-right">{{ number_format($specAmount, 2) }}</td>

                                <td class="g-orari fw-semibold num sep-right">{{ number_format($totalServiceDay, 2) }}
                                </td>

                                @if ($i === 0)
                                    <td rowspan="{{ $rowspan }}" class="g-dsc nowrap">
                                        {{ $vanNeeded ? 'Sì' : 'No' }}</td>
                                    <td rowspan="{{ $rowspan }}" class="g-dsc num">{{ $missedMeals }}</td>
                                    <td rowspan="{{ $rowspan }}" class="g-dsc num">
                                        {{ number_format($missedMealsAmount, 2) }}</td>
                                    <td rowspan="{{ $rowspan }}" class="g-dsc sep-right">
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
                                    <td rowspan="{{ $rowspan }}" class="g-segr num">
                                        {{ number_format($speseVarieGara, 2) }}</td>
                                    <td rowspan="{{ $rowspan }}" class="g-segr sep-right">
                                        {{ $noteAppGara !== '' ? $noteAppGara : '—' }}
                                    </td>

                                    <td rowspan="{{ $rowspan }}" class="g-spese num">
                                        {{ number_format((float) ($entry->km ?? 0), 2) }}</td>
                                    <td rowspan="{{ $rowspan }}" class="g-spese num">
                                        {{ number_format($kmAmount, 2) }}</td>
                                    <td rowspan="{{ $rowspan }}" class="g-spese num">
                                        {{ number_format((float) ($entry->pedaggi ?? 0), 2) }}</td>
                                    <td rowspan="{{ $rowspan }}" class="g-spese num">
                                        {{ number_format((float) ($entry->vitto ?? 0), 2) }}</td>
                                    <td rowspan="{{ $rowspan }}" class="g-spese num">
                                        {{ number_format((float) ($entry->alloggio ?? 0), 2) }}</td>
                                    <td rowspan="{{ $rowspan }}" class="g-spese num sep-right">
                                        {{ number_format((float) ($entry->spese_varie ?? 0), 2) }}</td>

                                    <td rowspan="{{ $rowspan }}" class="g-totali fw-semibold num">
                                        {{ number_format($totalRacePart, 2) }}</td>
                                    <td rowspan="{{ $rowspan }}" class="g-totali fw-semibold num">
                                        {{ number_format($totaleCrono, 2) }}</td>
                                    <td rowspan="{{ $rowspan }}" class="g-totali fw-bold num sep-right">
                                        {{ number_format($grandTotal, 2) }}</td>

                                    <td rowspan="{{ $rowspan }}" class="g-note">{{ $entry->note ?? '—' }}</td>

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
                                        @if ($entry->confirmed)
                                            <span class="badge bg-success">
                                                <i class="fas fa-check-circle me-1"></i> Confermato
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">Non confermato</span>
                                        @endif
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    @empty
                        <tr>
                            <td colspan="36" class="text-center text-muted p-4">
                                Nessun dato disponibile.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-3 small text-muted">
            Regole: Ordinario = prime 4 ore 30€, poi +6€/h. Specialistico = prime 4 ore 40€, poi +10€/h.
        </div>
    </div>
</div>

<script>
    (function() {
        // ===== IDs unici =====
        const uid = @json($uid);

        const search = document.getElementById('reportSearch-' + uid);
        const tbody = document.getElementById('reportTbody-' + uid);

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

        const toggles = document.querySelectorAll('.col-toggle-' + uid);

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

        const btnAllOn = document.getElementById('btnAllOn-' + uid);
        const btnEssentials = document.getElementById('btnEssentials-' + uid);

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

        // ===== misura automatica altezza riga gruppi per sticky header =====
        // (se cambia font/padding, non devi modificare CSS)
        const groupRow = document.querySelector('.big-report-table thead tr.group-row');
        if (groupRow) {
            const h = groupRow.getBoundingClientRect().height;
            document.documentElement.style.setProperty('--group-h', h + 'px');
        }
    })();
</script>
