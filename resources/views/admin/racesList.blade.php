<x-layout documentTitle="Admin Races List">
    <div class="container mt-5 pt-5">
        <h1 class="mb-4">Lista delle gare</h1>

        @if (session('success'))
            <div class="alert alert-dismissible alert-success">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="table-responsive mb-4">
            <table class="table table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th scope="col">Data</th>
                        <th scope="col">Luogo</th>
                        <th scope="col">Disciplina</th>
                        <th scope="col">Cronometristi</th>
                        <th scope="col">Inserisci cronometristi</th>
                        <th scope="col">Report</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($races as $race)
                        <tr>
                            <th scope="row">{{ $race->date_of_race }}</th>
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
                            <td>
                                <a href="{{ route('race.timekeepers.select', $race) }}"
                                    class="btn btn-outline-secondary btn-sm">
                                    Gestisci
                                </a>
                            </td>
                            <td>
                                <span class="text-muted">Report</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">Non ci sono gare registrate</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="text-end">
            <a href="{{ route('admin.createRace.form') }}" class="btn btn-success">Inserisci nuova gara</a>
        </div>
    </div>
</x-layout>
