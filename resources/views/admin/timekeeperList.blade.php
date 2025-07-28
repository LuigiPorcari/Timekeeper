<x-layout documentTitle="Admin Timekeeper List">
    <main class="container mt-5 pt-5" id="main-content" aria-labelledby="timekeeper-list-title">
        <h1 id="timekeeper-list-title" class="mb-4">Lista Cronometristi</h1>

        <div class="table-responsive" role="region" aria-label="Tabella dei cronometristi registrati">
            <table class="table table-bordered align-middle">
                <caption class="visually-hidden">
                    Elenco dei cronometristi, con le relative disponibilità, gare assegnate e link al report
                </caption>
                <thead class="table-light">
                    <tr>
                        <th scope="col">Nome e Cognome</th>
                        <th scope="col">Disponibilità</th>
                        <th scope="col">Gare</th>
                        <th scope="col">Report</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($timekeepers as $timekeeper)
                        <tr>
                            <th scope="row">
                                <a href="{{ route('admin.timekeeperDetails', $timekeeper) }}"
                                    aria-label="Visualizza dettagli di {{ $timekeeper->name }} {{ $timekeeper->surname }}">
                                    {{ $timekeeper->name }} {{ $timekeeper->surname }}
                                </a>
                            </th>
                            <td>
                                @forelse ($timekeeper->availabilities as $a)
                                    {{ ucwords(\Carbon\Carbon::parse($a->date_of_availability)->translatedFormat('l d F')) }}<br>
                                @empty
                                    <em class="text-muted">Non ha segnato disponibilità</em>
                                @endforelse
                            </td>
                            <td>
                                @forelse ($timekeeper->races as $race)
                                    {{ $race->name }}<br>
                                @empty
                                    <em class="text-muted">Non è assegnato a nessuna gara</em>
                                @endforelse
                            </td>
                            <td>
                                <a href="{{ route('admin.timekeeperReport', $timekeeper) }}"
                                    class="btn btn-sm btn-primary"
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
    </main>
</x-layout>
