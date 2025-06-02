<x-layout documentTitle="TImekeeper Dashboard">
    <div class="pt-5">
        <h1 class="mt-4">Welcome</h1>
    </div>
        <div class="container mt-5 pt-1">
            <div class="row mt-5 pt-5 justify-content-center">
                <div class="col-4">
                    <!-- Pulsante inserimento disponibilità-->
                    <a class="btn btn-primary" href="{{route('availability.show')}}">Disponibilità</a>
                </div>
                <div class="col-4">
                    <!-- Pulsante gestione gare-->
                    <a class="btn btn-primary" href="{{route('timekeeper.racesList')}}">Gare</a>
                </div>
            </div>
        </div>
</x-layout>
