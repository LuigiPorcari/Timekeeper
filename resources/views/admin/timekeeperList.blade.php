<x-layout documentTitle="Admin Timekeeper List">
    <main class="container mt-5 pt-5" id="main-content" aria-labelledby="timekeeper-list-title">
        <h1 id="timekeeper-list-title" class="mb-4">Lista Cronometristi</h1>

        <div class="card shadow-sm rounded-3 p-3">
            <div class="card-header bg-white d-flex align-items-center">
                <h2 class="h5 mb-0">Cronometristi registrati</h2>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive" style="overflow-x: visible;">
                    <table class="table table-bordered table-hover align-middle table-dark-borders mb-0">
                        <caption class="visually-hidden">
                            Elenco dei cronometristi, con le relative disponibilità, gare assegnate e link al report
                        </caption>
                        <thead class="table-light">
                            <tr>
                                <th scope="col">Nome e Cognome</th>
                                <th scope="col">Disponibilità</th>
                                <th scope="col">Gare</th>
                                <th scope="col" class="text-center">Report</th>
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

                                    <td>
                                        @forelse ($timekeeper->availabilities as $a)
                                            <div>
                                                {{ ucwords(\Carbon\Carbon::parse($a->date_of_availability)->translatedFormat('l d F')) }}
                                            </div>
                                        @empty
                                            <em class="text-muted">Non ha segnato disponibilità</em>
                                        @endforelse
                                    </td>

                                    <td>
                                        @forelse ($timekeeper->races as $race)
                                            <div>{{ $race->name }}</div>
                                        @empty
                                            <em class="text-muted">Non è assegnato a nessuna gara</em>
                                        @endforelse
                                    </td>

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
