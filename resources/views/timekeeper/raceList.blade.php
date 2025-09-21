<x-layout documentTitle="Timekeeper Races List">
    <main class="container mt-5 pt-5" role="main" aria-labelledby="races-title">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h1 id="races-title" class="h3 mb-0">Lista delle gare</h1>
        </div>

        <section class="card border-0 shadow-lg rounded-4 p-3">
            <div class="card-header bg-white">
                <div class="d-flex align-items-center">
                    <i class="fa-solid fa-flag-checkered me-2 text-primary"></i>
                    <span class="fw-semibold">Gare assegnate</span>
                </div>
            </div>

            <div class="card-body p-0">
                {{-- NIENTE .table-responsive -> niente scroll orizzontale interno --}}
                <table
                    class="table table-hover table-striped table-sm align-middle mb-0 table-no-scroll table-bordered align-middle table-border-black"
                    aria-describedby="descrizione-tabella-gare">
                    <caption id="descrizione-tabella-gare" class="visually-hidden">
                        Elenco delle gare a cui sei stato assegnato, con tipo, periodo, luogo, ente di fatturazione,
                        allegato, discipline, cronometristi e link al report.
                    </caption>

                    <thead class="table-light">
                        <tr>
                            <th scope="col" class="w-name">Nome</th>
                            <th scope="col" class="w-type">Tipo</th>
                            <th scope="col" class="w-period">Periodo</th>
                            <th scope="col" class="w-place">Luogo</th>
                            <th scope="col" class="w-ente d-none d-md-table-cell">Ente fatturazione</th>
                            <th scope="col" class="w-allegato text-center">Allegato</th>
                            <th scope="col" class="w-spec d-none d-lg-table-cell">Discipline</th>
                            <th scope="col" class="w-crono d-none d-lg-table-cell">Cronometristi</th>
                            <th scope="col" class="text-center w-report">Report</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($timekeeperRaces as $race)
                            @php
                                $startDate = $race->date_of_race
                                    ? \Illuminate\Support\Carbon::parse($race->date_of_race)->format('d/m/Y')
                                    : null;
                                $endDate = $race->date_end
                                    ? \Illuminate\Support\Carbon::parse($race->date_end)->format('d/m/Y')
                                    : null;
                                // Periodo su due righe come nell'altra vista
$periodo = $startDate ? ($endDate ? $startDate . '<br>' . $endDate : $startDate) : '—';

// Normalizza specializzazioni (array/JSON/string)
$specs = $race->specialization_of_race;
if (is_string($specs)) {
    $decoded = json_decode($specs, true);
    $specs =
        json_last_error() === JSON_ERROR_NONE ? $decoded : ($specs ? [$specs] : []);
}
$specs = is_array($specs) ? $specs : [];

// Allegato pubblico
$allegatoUrl = null;
if (
    !empty($race->programma_allegato) &&
    \Illuminate\Support\Facades\Storage::disk('public')->exists(
        $race->programma_allegato,
    )
) {
    $allegatoUrl = \Illuminate\Support\Facades\Storage::url($race->programma_allegato);
}

// Helper badge come nell'altra pagina
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
                                            {{ ucwords(str_replace('_', ' ', $specialization)) }}
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

                                <td class="text-center">
                                    <a href="{{ route('records.manage', ['race' => $race->id]) }}"
                                        class="btn btn-sm btn-ficr"
                                        aria-label="Visualizza o modifica il report per la gara del {{ \Illuminate\Support\Carbon::parse($race->date_of_race)->format('d/m/Y') }} a {{ $race->place }}">
                                        Report
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                {{-- 9 colonne in questa tabella --}}
                                <td colspan="9" class="text-center text-muted py-4">
                                    Non sei assegnato/a a nessuna gara
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</x-layout>
