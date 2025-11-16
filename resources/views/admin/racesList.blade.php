<x-layout documentTitle="Races List">
    <main class="container mt-5 pt-5" id="main-content" aria-labelledby="races-list-title">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h1 id="races-list-title" class="h3 mb-0">Lista delle gare</h1>

            @if (Auth::user()->is_admin)
                <a href="{{ route('admin.createRace.form') }}" class="btn btn-ficr">
                    <i class="fa-solid fa-plus me-1"></i> Inserisci nuova gara
                </a>
            @endif
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Chiudi notifica"></button>
            </div>
        @endif

        <div class="card border-0 shadow-lg rounded-4 p-3">
            <div class="card-header bg-white">
                <div class="d-flex align-items-center">
                    <i class="fa-solid fa-flag-checkered me-2 text-primary"></i>
                    <span class="fw-semibold">Gare registrate</span>
                </div>
            </div>

            <div class="card-body p-0">
                {{-- NIENTE .table-responsive -> niente scroll orizzontale --}}
                <table
                    class="table table-hover table-striped table-sm align-middle mb-0 table-no-scroll table-bordered align-middle table-border-black">
                    <caption class="visually-hidden">
                        Tabella con elenco delle gare, tipo, periodo, luogo, ente di fatturazione, allegato,
                        discipline, cronometristi e azioni disponibili.
                    </caption>
                    <thead class="table-light">
                        <tr>
                            <th scope="col" class="w-name">Nome</th>
                            <th scope="col" class="w-type">Tipologia gara</th>
                            <th scope="col" class="w-period">Periodo</th>
                            <th scope="col" class="w-place">Luogo</th>
                            <th scope="col" class="w-ente d-none d-md-table-cell">Ente fatturazione</th>
                            <th scope="col" class="w-allegato text-center">Allegato</th>
                            <th scope="col" class="w-spec d-none d-lg-table-cell">Apparecchiature</th>
                            <th scope="col" class="w-crono d-none d-lg-table-cell">Cronometristi</th>
                            @if (Auth::user()->is_admin)
                                <th scope="col" class="text-center d-none d-md-table-cell">Inserisci cronometristi
                                </th>
                            @endif
                            <th scope="col" class="text-center w-report">Report</th>
                            @if (Auth::user()->is_admin)
                                <th scope="col" class="text-center d-none d-md-table-cell w-actions">Azioni</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($races as $race)
                            @php
                                $startDate = $race->date_of_race
                                    ? \Illuminate\Support\Carbon::parse($race->date_of_race)->format('d/m/Y')
                                    : null;
                                $endDate = $race->date_end
                                    ? \Illuminate\Support\Carbon::parse($race->date_end)->format('d/m/Y')
                                    : null;
                                $periodo = $startDate ? ($endDate ? $startDate . '<br>' . $endDate : $startDate) : '—';

                                // Normalizza specialization_of_race in array
                                $specs = $race->specialization_of_race;
                                if (is_string($specs)) {
                                    $decoded = json_decode($specs, true);
                                    $specs =
                                        json_last_error() === JSON_ERROR_NONE ? $decoded : ($specs ? [$specs] : []);
                                }
                                $specs = is_array($specs) ? $specs : [];

                                // URL allegato (se presente)
                                $allegatoUrl = null;
                                if (
                                    !empty($race->programma_allegato) &&
                                    \Illuminate\Support\Facades\Storage::disk('public')->exists(
                                        $race->programma_allegato,
                                    )
                                ) {
                                    $allegatoUrl = \Illuminate\Support\Facades\Storage::url($race->programma_allegato);
                                }

                                // Badge helper
                                $badge = fn($text, $class = 'primary') => '<span class="badge rounded-pill text-bg-' .
                                    $class .
                                    ' bg-opacity-10 border border-' .
                                    $class .
                                    ' text-' .
                                    $class .
                                    '">' .
                                    e($text) .
                                    '</span>';

                                // Formatter per apparecchiature: rimuove il prefisso del tipo (tipo__etichetta),
                                // e trasforma lo slug in label leggibile.
                                $formatSpec = function ($raw) {
                                    if (!is_string($raw) || $raw === '') {
                                        return '—';
                                    }
                                    $slug = $raw;
                                    if (str_contains($raw, '__')) {
                                        [$typeSlug, $slug] = explode('__', $raw, 2);
                                    }
                                    // sostituisco trattini/underscore con spazi, compatto gli spazi e metto in forma "Title Case"
                                    $label = preg_replace('/\s+/', ' ', str_replace(['-', '_'], ' ', $slug));
                                    $label = trim($label);
                                    // mantieni acronimi semplici (es. REI, PS) se già in maiuscolo nello slug
                                    return ucwords($label);
                                };
                            @endphp

                            <tr>
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

                                <td>{!! $periodo !!}</td>
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

                                <td class="d-none d-lg-table-cell">
                                    @forelse ($specs as $specialization)
                                        <span
                                            class="badge rounded-pill text-bg-secondary bg-opacity-10 border border-secondary text-secondary me-1 mb-1">
                                            {{ $formatSpec($specialization) }}
                                        </span>
                                    @empty
                                        <em class="text-muted">Nessuna</em>
                                    @endforelse
                                </td>

                                <td class="d-none d-lg-table-cell">
                                    @forelse ($race->users as $user)
                                        <span class="badge text-bg-light border me-1 mb-1">
                                            {{ $user->name }} {{ $user->surname }}
                                        </span><br>
                                    @empty
                                        <em class="text-muted">Nessuno</em>
                                    @endforelse
                                </td>

                                @if (Auth::user()->is_admin)
                                    <td class="text-center d-none d-md-table-cell">
                                        <a href="{{ route('race.timekeepers.select', $race) }}"
                                            class="btn btn-outline-secondary btn-sm"
                                            aria-label="Gestisci cronometristi per la gara del {{ $race->date_of_race }}">
                                            Gestisci
                                        </a>
                                    </td>
                                @endif

                                <td class="text-center">
                                    <a href="{{ route('admin.raceReport', $race) }}" class="btn btn-sm btn-ficr"
                                        aria-label="Visualizza report della gara del {{ $race->date_of_race }}">
                                        Report
                                    </a>
                                </td>

                                @if (Auth::user()->is_admin)
                                    <td class="text-center d-none d-md-table-cell">
                                        <div class="d-inline-flex gap-2 flex-wrap table-actions">
                                            <a href="{{ route('admin.race.edit', $race) }}"
                                                class="btn btn-warning btn-sm"
                                                aria-label="Modifica gara del {{ $race->date_of_race }}">
                                                Modifica
                                            </a>
                                            <form method="POST" action="{{ route('admin.race.destroy', $race) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm"
                                                    aria-label="Elimina gara del {{ $race->date_of_race }}">
                                                    Elimina
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            @php
                                $colspan = 9;
                                if (Auth::user()->is_admin) {
                                    $colspan = 11;
                                }
                            @endphp
                            <tr>
                                <td colspan="{{ $colspan }}" class="text-center text-muted py-4">
                                    Non ci sono gare registrate
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</x-layout>
