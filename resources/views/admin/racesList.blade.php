<x-layout documentTitle="Admin Races List">
    <div class="pt-5">
        <h1 class="mt-4">Lista delle gare</h1>
    </div>
    @if (session('success'))
        <div class="alert alert-dismissible alert-success">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    <div>
        <table class="table table-bordered">
            <thead>
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
                        <td><a href="">Inserisci/modifica cronometristi</a></td>
                        <td>Report</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">
                            <h2 class="custom-subtitle text-black">Non ci sono gare registrate</h2>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div>
        <a href="{{ route('admin.createRace.form') }}">Inserisci nuova gara</a>
    </div>
</x-layout>
