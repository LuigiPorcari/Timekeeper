<x-layout documentTitle="Admin Dashboard">
    <main class="container pt-5 mt-5" id="main-content" aria-labelledby="dashboard-title">
        {{-- Header a gradiente --}}
        <header class="page-header rounded-4 mb-4 px-4 py-4">
            <h1 id="dashboard-title" class="h3 text-white mb-1">Benvenuto nella dashboard amministrativa</h1>
            <p class="text-white-50 mb-0">Gestisci cronometristi, gare, disponibilità e richieste temporanee.</p>
        </header>

        {{-- Navigazione amministrativa --}}
        <section class="row g-4" role="navigation" aria-label="Navigazione amministrativa">
            {{-- Cronometristi --}}
            <div class="col-12 col-md-6 col-lg-3">
                <div class="card ficr-card h-100 border-0 shadow-sm rounded-4">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex align-items-center mb-2">
                            <span class="ficr-badge-icon me-2">
                                <i class="fa-solid fa-users"></i>
                            </span>
                            <h2 class="h6 mb-0">Cronometristi</h2>
                        </div>
                        <p class="text-muted small flex-grow-1 mb-3">
                            Gestisci i profili, le specializzazioni e le disponibilità dei cronometristi.
                        </p>
                        <div class="d-grid">
                            <a class="btn btn-ficr" href="{{ route('admin.timekeeperList') }}" role="button"
                                aria-label="Gestisci Cronometristi">
                                Apri
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Gare --}}
            <div class="col-12 col-md-6 col-lg-3">
                <div class="card ficr-card h-100 border-0 shadow-sm rounded-4">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex align-items-center mb-2">
                            <span class="ficr-badge-icon me-2">
                                <i class="fa-solid fa-flag-checkered"></i>
                            </span>
                            <h2 class="h6 mb-0">Gare</h2>
                        </div>
                        <p class="text-muted small flex-grow-1 mb-3">
                            Crea e modifica le gare, assegna i cronometristi e gestisci i report.
                        </p>
                        <div class="d-grid">
                            <a class="btn btn-ficr" href="{{ route('admin.racesList') }}" role="button"
                                aria-label="Gestisci Gare">
                                Apri
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Disponibilità --}}
            <div class="col-12 col-md-6 col-lg-3">
                <div class="card ficr-card h-100 border-0 shadow-sm rounded-4">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex align-items-center mb-2">
                            <span class="ficr-badge-icon me-2">
                                <i class="fa-solid fa-calendar-check"></i>
                            </span>
                            <h2 class="h6 mb-0">Disponibilità</h2>
                        </div>
                        <p class="text-muted small flex-grow-1 mb-3">
                            Inserisci o aggiorna le disponibilità nel calendario annuale.
                        </p>
                        <div class="d-grid">
                            <a class="btn btn-ficr" href="{{ route('admin.createAvailability.form') }}" role="button"
                                aria-label="Gestisci Disponibilità">
                                Apri
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Gare temporanee --}}
            <div class="col-12 col-md-6 col-lg-3">
                <div class="card ficr-card h-100 border-0 shadow-sm rounded-4">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex align-items-center mb-2">
                            <span class="ficr-badge-icon me-2">
                                <i class="fa-solid fa-hourglass-half"></i>
                            </span>
                            <h2 class="h6 mb-0">Gare temporanee</h2>
                        </div>
                        <p class="text-muted small flex-grow-1 mb-3">
                            Valida o rifiuta le richieste di inserimento gare inviate dagli utenti.
                        </p>
                        <div class="d-grid">
                            <a class="btn btn-ficr" href="{{ route('admin.racesTempList') }}" role="button"
                                aria-label="Gestisci Gare temporanee">
                                Apri
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>
</x-layout>
