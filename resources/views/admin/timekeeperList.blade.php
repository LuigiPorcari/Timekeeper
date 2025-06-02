<x-layout documentTitle="Admin Timekeeper List">
    <div class="pt-5">
        <h1 class="mt-4">Lista Cronometristi</h1>
    </div>
    <div class="container mt-5 pt-1">
        <div class="row mt-5 pt-5 justify-content-center">
            <table class="table table-bordered">
                <thead>
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
                                <a href="{{ route('admin.timekeeperDetails', $timekeeper) }}">{{ $timekeeper->name }}
                                    {{ $timekeeper->surname }}</a>
                            </th>
                            <td>
                                @forelse ($timekeeper->availabilities as $availavily)
                                    {{ $availavily->date_of_availability }}
                                @empty
                                    Non ha segnato disponibilità
                                @endforelse
                            </td>
                            <td>
                                @forelse ($timekeeper->races as $race)
                                    {{ $race->date_of_race }}
                                @empty
                                    Non è assegnato a nessuna gara
                                @endforelse
                            </td>
                            <td>Report</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">
                                <h2 class="custom-subtitle text-black">Non ci sono cronometristi registrati</h2>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>


</x-layout>
