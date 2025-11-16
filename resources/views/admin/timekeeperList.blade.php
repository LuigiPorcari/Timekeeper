<x-layout documentTitle="Admin Timekeeper List">
    <main class="container mt-5 pt-5" id="main-content" aria-labelledby="timekeeper-list-title">
        <h1 id="timekeeper-list-title" class="mb-4">Lista Cronometristi</h1>

        {{-- Legenda colori --}}
        <div class="mb-3">
            <div class="d-flex align-items-center gap-3 flex-wrap">
                <span class="badge rounded-pill text-bg-success">Verde</span>
                <span class="badge rounded-pill text-bg-warning">Arancione</span>
                <span class="badge rounded-pill text-bg-danger">Rosso</span>
            </div>
        </div>

        <div class="card shadow-sm rounded-3 p-3">
            <div class="card-header bg-white d-flex align-items-center">
                <h2 class="h5 mb-0">Cronometristi registrati</h2>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive" style="overflow-x: visible;">
                    <table class="table table-bordered table-hover align-middle table-dark-borders mb-0">
                        <caption class="visually-hidden">
                            Elenco dei cronometristi, con le relative disponibilità (giorno, colore, scelta), gare
                            assegnate e link al report
                        </caption>
                        <thead class="table-light">
                            <tr>
                                <th scope="col" style="width: 22%">Nome e Cognome</th>
                                <th scope="col" style="width: 48%">Disponibilità</th>
                                <th scope="col" style="width: 20%">Gare</th>
                                <th scope="col" class="text-center" style="width: 10%">Report</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($timekeepers as $timekeeper)
                                <tr>
                                    <th scope="row" class="fw-semibold">
                                        <a href="{{ route('admin.timekeeperDetails', $timekeeper) }}"
                                            class="text-decoration-none"
                                            aria-label="Visualizza dettagli di {{ $timekeeper->name }} {{ $timekeeper->surname }}">
                                            {{ $timekeeper->name }} {{ $timekeeper->surname }}
                                        </a>
                                    </th>

                                    {{-- Disponibilità: tabellina interna con giorno/colore/scelta --}}
                                    <td>
                                        @php
                                            $avs = $timekeeper->availabilities ?? collect();
                                        @endphp

                                        @if ($avs->isEmpty())
                                            <em class="text-muted">Non ha segnato disponibilità</em>
                                        @else
                                            <div class="table-responsive">
                                                <table class="table table-sm align-middle mb-0">
                                                    <thead>
                                                        <tr class="text-muted">
                                                            <th style="width: 30%">Giorno</th>
                                                            <th style="width: 20%">Colore</th>
                                                            <th style="width: 50%">Scelta</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($avs as $a)
                                                            @php
                                                                $d = \Carbon\Carbon::parse(
                                                                    $a->date_of_availability,
                                                                )->locale('it');
                                                                // es: "mer 01 gen"
                                                                $giorno = $d->isoFormat('ddd DD MMM');

                                                                $color = $a->color ?? null;
                                                                $badgeClass = match ($color) {
                                                                    'verde' => 'text-bg-success',
                                                                    'arancione' => 'text-bg-warning',
                                                                    'rosso' => 'text-bg-danger',
                                                                    default => 'text-bg-secondary',
                                                                };

                                                                $p = $a->pivot ?? null;
                                                                $scelte = [];
                                                                if ($p && $p->morning) {
                                                                    $scelte[] = 'Mattina';
                                                                }
                                                                if ($p && $p->afternoon) {
                                                                    $scelte[] = 'Pomeriggio';
                                                                }
                                                                if ($p && $p->trasferta) {
                                                                    $scelte[] = 'Trasferta';
                                                                }
                                                                if ($p && $p->reperibilita) {
                                                                    $scelte[] = 'Reperibilità';
                                                                }
                                                                $scelteLabel = $scelte ? implode(', ', $scelte) : '—';
                                                            @endphp

                                                            <tr>
                                                                <td class="text-nowrap">{{ $giorno }}</td>
                                                                <td>
                                                                    <span class="badge {{ $badgeClass }}">
                                                                        {{ $color ? ucfirst($color) : '—' }}
                                                                    </span>
                                                                </td>
                                                                <td>{{ $scelteLabel }}</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        @endif
                                    </td>

                                    {{-- Gare assegnate --}}
                                    <td>
                                        @forelse ($timekeeper->races as $race)
                                            <div>{{ $race->name }}</div>
                                        @empty
                                            <em class="text-muted">Non è assegnato a nessuna gara</em>
                                        @endforelse
                                    </td>

                                    {{-- Report --}}
                                    <td class="text-center">
                                        <a href="{{ route('admin.timekeeperReport', $timekeeper) }}"
                                            class="btn btn-sm btn-ficr"
                                            aria-label="Visualizza report di {{ $timekeeper->name }} {{ $timekeeper->surname }}">
                                            Report
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">
                                        Non ci sono cronometristi registrati
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</x-layout>
