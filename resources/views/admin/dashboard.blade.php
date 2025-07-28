<x-layout documentTitle="Admin Dashboard">
    <main class="container mt-5 pt-5" id="main-content" aria-labelledby="dashboard-title">
        <h1 id="dashboard-title" class="mb-4">Benvenuto nella dashboard amministrativa</h1>

        <div class="row mt-5 pt-5 justify-content-center g-4" role="navigation" aria-label="Navigazione amministrativa">
            <div class="col-12 col-md-4 d-grid">
                <a class="btn btn-primary" href="{{ route('admin.timekeeperList') }}" role="button"
                    aria-label="Gestisci Cronometristi">
                    Cronometristi
                </a>
            </div>
            <div class="col-12 col-md-4 d-grid">
                <a class="btn btn-primary" href="{{ route('admin.racesList') }}" role="button"
                    aria-label="Gestisci Gare">
                    Gare
                </a>
            </div>
            <div class="col-12 col-md-4 d-grid">
                <a class="btn btn-primary" href="{{ route('admin.createAvailability.form') }}" role="button"
                    aria-label="Gestisci Disponibilità">
                    Disponibilità
                </a>
            </div>
            <div class="col-12 col-md-4 d-grid">
                <a class="btn btn-primary" href="{{ route('admin.racesTempList') }}" role="button"
                    aria-label="Gestisci Disponibilità">
                    Gare temporanee
                </a>
            </div>
        </div>
    </main>
</x-layout>
