<x-layout documentTitle="Timekeeper Races List">
    <div class="pt-5">
        <h1 class="mt-4">Lista delle gare</h1>
    </div>
    <div>
        <table class="table table-bordered">
            <thead>
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
                            {{ $race->date_of_race }}
                        </th>
                        <td>
                            {{ $race->place }}
                        </td>
                        <td>
                            @forelse ($race->specialization_of_race as $specialization)
                                {{ $specialization }}<br>
                            @empty
                                Non è assegnata nessuna specializzazione
                            @endforelse
                        </td>
                        <td>
                            @forelse ($race->users as $user)
                                {{ $user->name }} {{ $user->surname }}<br>
                            @empty
                                Non è assegnato nessun cronometrista
                            @endforelse
                        </td>
                        <td>Report</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">
                            <h2 class="custom-subtitle text-black">Non sei assegnato/a a nessuna gara</h2>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-layout>
