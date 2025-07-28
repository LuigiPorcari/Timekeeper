<x-layout documentTitle="Temp Races List">
    <main class="container mt-5 pt-5" id="main-content" aria-labelledby="races-list-title">
        <h1 id="races-list-title" class="mb-4">Lista delle gare temporanee</h1>

        @if (session('success'))
            <div class="alert alert-dismissible alert-success" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Chiudi notifica"></button>
            </div>
        @endif

        <div class="table-responsive mb-4" role="region" aria-label="Elenco delle gare registrate">
            <table class="table table-bordered align-middle">
                <caption class="visually-hidden">Tabella con elenco delle gare temporanee, luoghi, discipline e azioni
                    disponibili
                </caption>
                <thead class="table-light">
                    <tr>
                        <th scope="col">Nome</th>
                        <th scope="col">Data</th>
                        <th scope="col">Luogo</th>
                        <th scope="col">Disciplina</th>
                        <th scope="col">Azioni</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($racesTemp as $race)
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
                            <td class="d-flex gap-2">
                                <form method="POST" action="{{ route('race-temp.accept', $race->id) }}">
                                    @csrf
                                    <button type="submit" class="btn btn-warning btn-sm">
                                        Accetta
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('race-temp.reject', $race->id) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm"
                                        aria-label="Elimina gara del {{ $race->date_of_race }}">
                                        Rifiuta
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted">
                                Non ci sono gare temporanee registrate
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </main>
</x-layout>
