<x-layout documentTitle="Timekeeper Races List">
    <main class="container mt-5 pt-5" role="main">
        <h1 class="mb-4">Lista delle gare</h1>

        <section class="table-responsive" aria-label="Tabella delle gare assegnate al cronometrista">
            <table class="table table-bordered align-middle" aria-describedby="descrizione-tabella-gare">
                <caption id="descrizione-tabella-gare" class="visually-hidden">
                    Elenco delle gare a cui sei stato assegnato, con dettagli su data, luogo, disciplina, cronometristi
                    e link al report.
                </caption>
                <thead class="table-light">
                    <tr>
                        <th scope="col">Nome</th>
                        <th scope="col">Data</th>
                        <th scope="col">Luogo</th>
                        <th scope="col">Disciplina</th>
                        <th scope="col">Cronometristi</th>
                        <th scope="col">Report</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($timekeeperRaces as $race)
                        <tr>
                            <th scope="row">
                                {{ $race->name }}
                            </th>
                            <td>{{ ucwords(\Carbon\Carbon::parse($race->date_of_race)->translatedFormat('l d F')) }}
                            </td>
                            <td>{{ $race->place }}</td>
                            <td>
                                @forelse ($race->specialization_of_race as $specialization)
                                    {{ $specialization }}<br>
                                @empty
                                    <em class="text-muted">Nessuna specializzazione</em>
                                @endforelse
                            </td>
                            <td>
                                @forelse ($race->users as $user)
                                    {{ $user->name }} {{ $user->surname }}<br>
                                @empty
                                    <em class="text-muted">Nessun cronometrista</em>
                                @endforelse
                            </td>
                            <td>
                                <a href="{{ route('records.manage', ['race' => $race->id]) }}"
                                    class="btn btn-sm btn-primary"
                                    aria-label="Visualizza o modifica il report per la gara del {{ \Carbon\Carbon::parse($race->date_of_race)->format('d/m/Y') }} a {{ $race->place }}">
                                    Report
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">
                                Non sei assegnato/a a nessuna gara
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </section>
    </main>
</x-layout>
