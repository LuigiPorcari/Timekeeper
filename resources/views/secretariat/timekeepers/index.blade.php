<x-layout documentTitle="Segreteria — Report Cronometristi">
    <main class="container mt-5 pt-5" aria-labelledby="tk-title">
        <h1 id="tk-title" class="mb-4">Report Cronometristi</h1>

        {{-- Card filtri --}}
        <div class="card shadow-sm mb-4 tk-card" aria-label="Filtri di ricerca">
            <div class="card-header tk-card-header">
                <h2 class="h5 mb-0">Filtri</h2>
            </div>
            <div class="card-body">
                <form class="row g-2" method="GET" action="{{ route('secretariat.timekeepers.index') }}">
                    <div class="col-12 col-md-4">
                        <label class="form-label" for="q">Cerca cronometrista</label>
                        <input type="text" id="q" name="q" class="form-control"
                            placeholder="Nome o cognome" value="{{ request('q') }}">
                    </div>
                    <div class="col-12 col-md-2 d-flex align-items-end ms-auto">
                        <button class="btn btn-primary w-100">Filtra</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Card tabella risultati --}}
        <div class="card shadow-sm tk-card p-3">
            <div class="card-header tk-card-header">
                <h2 class="h5 mb-0">Elenco cronometristi</h2>
            </div>
            <div class="card-body p-0">
                <table class="table table-bordered table-striped align-middle mb-0 tk-table-separated">
                    <thead class="table-light">
                        <tr>
                            <th>Cronometrista</th>
                            <th>N. record</th>
                            <th>Azioni</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rows as $row)
                            <tr>
                                <td>{{ $row['user']->surname }} {{ $row['user']->name }}</td>
                                <td>{{ $row['count'] }}</td>
                                <td>
                                    <a href="{{ route('secretariat.timekeepers.show', $row['user']) }}"
                                        class="btn btn-sm btn-outline-primary">
                                        Apri report
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted">
                                    Nessun dato per il periodo selezionato.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer d-flex justify-content-end mt-3">
            <a href="{{ route('secretariat.dashboard') }}" class="btn btn-secondary">
                Torna alla dashboard
            </a>
        </div>
    </main>
</x-layout>
