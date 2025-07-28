<x-layout documentTitle="Races List">
    <main class="container mt-5 pt-5" id="main-content" aria-labelledby="races-list-title">
        <h1 id="races-list-title" class="mb-4">Lista delle gare</h1>

        @if (session('success'))
            <div class="alert alert-dismissible alert-success" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Chiudi notifica"></button>
            </div>
        @endif

        <div class="table-responsive mb-4" role="region" aria-label="Elenco delle gare registrate">
            <table class="table table-bordered align-middle">
                <caption class="visually-hidden">Tabella con elenco delle gare, luoghi, discipline e azioni disponibili
                </caption>
                <thead class="table-light">
                    <tr>
                        <th scope="col">Nome</th>
                        <th scope="col">Data</th>
                        <th scope="col">Luogo</th>
                        <th scope="col">Disciplina</th>
                        <th scope="col">Cronometristi</th>
                        @if (Auth::user()->is_admin)
                            <th scope="col">Inserisci cronometristi</th>
                        @endif
                        <th scope="col">Report</th>
                        @if (Auth::user()->is_admin)
                            <th scope="col">Azioni</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse ($races as $race)
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
                                    <em>Nessuna specializzazione</em>
                                @endforelse
                            </td>
                            <td>
                                @forelse ($race->users as $user)
                                    {{ $user->name }} {{ $user->surname }}<br>
                                @empty
                                    <em>Nessun cronometrista assegnato</em>
                                @endforelse
                            </td>
                            @if (Auth::user()->is_admin)
                                <td>
                                    <a href="{{ route('race.timekeepers.select', $race) }}"
                                        class="btn btn-outline-secondary btn-sm"
                                        aria-label="Gestisci cronometristi per la gara del {{ $race->date_of_race }}">
                                        Gestisci
                                    </a>
                                </td>
                            @endif
                            <td>
                                <a href="{{ route('admin.raceReport', $race) }}" class="btn btn-sm btn-primary"
                                    aria-label="Visualizza report della gara del {{ $race->date_of_race }}">
                                    Report
                                </a>
                            </td>
                            @if (Auth::user()->is_admin)
                                <td class="d-flex gap-2">
                                    <a href="{{ route('admin.race.edit', $race) }}" class="btn btn-warning btn-sm"
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
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted">
                                Non ci sono gare registrate
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if (Auth::user()->is_admin)
            <div class="text-end">
                <a href="{{ route('admin.createRace.form') }}" class="btn btn-success"
                    aria-label="Inserisci una nuova gara">
                    Inserisci nuova gara
                </a>
            </div>
        @endif
    </main>
</x-layout>
