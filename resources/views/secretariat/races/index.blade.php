<x-layout documentTitle="Segreteria — Report Gare">
    <main class="container mt-5 pt-5" aria-labelledby="races-title">
        <h1 id="races-title" class="mb-4">Report Gare</h1>

        <div class="card shadow-sm mb-4 tk-card" aria-label="Filtri di ricerca">
            <div class="card-header tk-card-header">
                <h2 class="h5 mb-0">Filtri</h2>
            </div>
            <div class="card-body">
                <form class="row g-3" method="GET" action="{{ route('secretariat.races.index') }}">
                    <div class="col-12 col-md-3">
                        <label class="form-label" for="from">Dal</label>
                        <input type="date" id="from" name="from" class="form-control"
                            value="{{ request('from') }}">
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label" for="to">Al</label>
                        <input type="date" id="to" name="to" class="form-control"
                            value="{{ request('to') }}">
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label" for="q">Ricerca</label>
                        <input type="text" id="q" name="q" class="form-control"
                            placeholder="Nome gara o luogo" value="{{ request('q') }}">
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label" for="status">Stato</label>
                        <select id="status" name="status" class="form-select">
                            <option value="">Tutte</option>
                            <option value="open" {{ request('status') === 'open' ? 'selected' : '' }}>Aperte (da
                                confermare)</option>
                            <option value="closed" {{ request('status') === 'closed' ? 'selected' : '' }}>Chiuse (tutto
                                confermato)</option>
                        </select>
                    </div>
                    <div class="col-12 d-flex justify-content-end gap-2">
                        <button class="btn btn-primary">Filtra</button>
                        <a class="btn btn-outline-secondary" href="{{ route('secretariat.races.index') }}">Reset</a>
                    </div>
                </form>
            </div>
        </div>


        {{-- CARD RISULTATI (Tabella + paginazione) --}}
        <div class="card border-0 shadow-lg rounded-4 p-3">
            <div class="card-header bg-white">
                <div class="d-flex align-items-center">
                    <i class="fa-solid fa-flag-checkered me-2 text-primary"></i>
                    <span class="fw-semibold">Elenco gare</span>
                </div>
            </div>

            <div class="card-body p-0">
                {{-- NIENTE .table-responsive -> niente scroll orizzontale interno --}}
                <table
                    class="table table-hover table-striped table-sm align-middle mb-0 table-no-scroll table-bordered table-border-black"
                    aria-describedby="tbl-desc">
                    <caption id="tbl-desc" class="visually-hidden">
                        Tabella con periodo, nome gara, tipo, luogo, ente fatturazione, allegato, numero record, da
                        confermare e link al dettaglio.
                    </caption>

                    <thead class="table-light">
                        <tr>
                            <th class="w-period">Periodo</th>
                            <th class="w-name">Gara</th>
                            <th class="w-type">Tipo</th>
                            <th class="w-place">Luogo</th>
                            <th class="w-ente d-none d-md-table-cell">Ente fatturazione</th>
                            <th class="w-allegato text-center">Allegato</th>
                            <th class="text-center">Record</th>
                            <th class="text-center">Da confermare</th>
                            <th class="text-center w-report">Report</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($races as $race)
                            @php
                                $start = $race->date_of_race
                                    ? \Illuminate\Support\Carbon::parse($race->date_of_race)->format('d/m/Y')
                                    : null;
                                $end = $race->date_end
                                    ? \Illuminate\Support\Carbon::parse($race->date_end)->format('d/m/Y')
                                    : null;
                                $periodo = $start ? ($end ? $start . '<br>' . $end : $start) : '—';

                                $allegatoUrl = null;
                                if (
                                    !empty($race->programma_allegato) &&
                                    \Illuminate\Support\Facades\Storage::disk('public')->exists(
                                        $race->programma_allegato,
                                    )
                                ) {
                                    $allegatoUrl = \Illuminate\Support\Facades\Storage::url($race->programma_allegato);
                                }

                                // Helper badge come nella "Races List"
                                $badge = fn($text, $class = 'primary') => '<span class="badge rounded-pill text-bg-' .
                                    $class .
                                    ' bg-opacity-10 border border-' .
                                    $class .
                                    ' text-' .
                                    $class .
                                    '">' .
                                    e($text) .
                                    '</span>';
                            @endphp

                            <tr>
                                <td>{!! $periodo !!}</td>

                                <th scope="row" class="fw-semibold">
                                    {{ $race->name }}
                                </th>

                                <td>
                                    @if ($race->type)
                                        {!! $badge($race->type, 'primary') !!}
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>

                                <td>{{ $race->place ?? '—' }}</td>

                                <td class="d-none d-md-table-cell">{{ $race->ente_fatturazione ?? '—' }}</td>

                                <td class="text-center">
                                    @if ($allegatoUrl)
                                        <a href="{{ $allegatoUrl }}" class="btn btn-sm btn-outline-secondary"
                                            target="_blank" rel="noopener">
                                            <i class="fa-regular fa-file-lines me-1"></i> Apri
                                        </a>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>

                                <td class="text-center">{{ $race->records_total }}</td>

                                <td class="text-center">
                                    @if ($race->records_unconfirmed > 0)
                                        <span
                                            class="badge bg-warning text-dark">{{ $race->records_unconfirmed }}</span>
                                    @else
                                        <span class="badge bg-success">0</span>
                                    @endif
                                </td>

                                <td class="text-center">
                                    <a class="btn btn-sm btn-ficr" href="{{ route('secretariat.races.show', $race) }}">
                                        <i class="fa-regular fa-folder-open me-1"></i> Apri
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">Nessuna gara trovata.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="card-footer d-flex justify-content-end">
                {{ $races->links() }}
            </div>
        </div>

        {{-- Pulsante back separato, come prima --}}
        <div class="card-footer d-flex justify-content-end mt-3">
            <a href="{{ route('secretariat.dashboard') }}" class="btn btn-secondary">
                Torna alla dashboard
            </a>
        </div>

    </main>
</x-layout>
