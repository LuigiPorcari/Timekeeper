<x-layout documentTitle="Timekeeper Dashboard">
    <main class="container mt-5 pt-5" role="main">
        <h1 class="mb-4">Benvenuto</h1>

        <section class="row mt-5 pt-5 justify-content-center g-4" aria-label="Azioni disponibili per il cronometrista">
            <div class="col-12 col-md-4 d-grid">
                <a class="btn btn-primary" href="{{ route('availability.show') }}"
                    aria-label="Vai alla pagina delle disponibilità">
                    Disponibilità
                </a>
            </div>
            <div class="col-12 col-md-4 d-grid">
                <a class="btn btn-primary" href="{{ route('timekeeper.racesList') }}"
                    aria-label="Vai alla pagina delle gare">
                    Gare
                </a>
            </div>
        </section>
    </main>
</x-layout>
