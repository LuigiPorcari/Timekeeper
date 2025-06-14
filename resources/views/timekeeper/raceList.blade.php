<x-layout documentTitle="Timekeeper Races List">
    <div class="container mt-5 pt-5">
        <h1 class="mb-4">Lista delle gare</h1>

        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead class="table-light">
                    <tr>
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
                                {{ ucwords(\Carbon\Carbon::parse($race->date_of_race)->translatedFormat('l d F')) }}
                            </th>
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
                            <td><span class="text-muted"> <a href="{{ route('records.manage', ['race' => $race->id]) }}"
                                        class="btn btn-sm btn-primary">
                                        Report
                                    </a></span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted">
                                Non sei assegnato/a a nessuna gara
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-layout>
