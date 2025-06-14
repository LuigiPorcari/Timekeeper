<x-layout documentTitle="Timekeeper Dashboard">
    <div class="container mt-5 pt-5">
        <h1 class="mb-4">Welcome</h1>

        <div class="row mt-5 pt-5 justify-content-center g-4">
            <div class="col-4 d-grid">
                <a class="btn btn-primary" href="{{ route('availability.show') }}">Disponibilità</a>
            </div>
            <div class="col-4 d-grid">
                <a class="btn btn-primary" href="{{ route('timekeeper.racesList') }}">Gare</a>
            </div>
        </div>
    </div>
</x-layout>
