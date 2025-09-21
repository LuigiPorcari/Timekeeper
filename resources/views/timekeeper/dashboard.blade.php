<x-layout documentTitle="Timekeeper Dashboard">
    <main class="container mt-5 pt-5" role="main" aria-labelledby="tk-home-title">
        <h1 id="tk-home-title" class="visually-hidden">Dashboard Cronometrista</h1>

        {{-- Hero introduttivo --}}
        <section class="row justify-content-center mb-4">
            <div class="col-12 col-lg-10">
                <div class="card p-4 p-md-5 bg-white border-0 shadow-sm rounded-4">
                    <div class="row align-items-center g-4">
                        <div class="col-md">
                            <h2 class="h3 mb-2 text-dark">
                                Ciao {{ auth()->user()->name }}!
                            </h2>
                            <p class="mb-0 text-secondary">
                                Gestisci le tue disponibilità e consulta le gare a te assegnate in pochi click.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- Card azione in stile homepage (CTA tiles) --}}
        <section class="row g-4 justify-content-center" aria-label="Azioni rapide">
            <div class="col-12 col-md-6 col-lg-5">
                <a class="cta-card text-decoration-none d-block h-100" href="{{ route('availability.show') }}"
                    aria-label="Gestisci disponibilità">
                    <div class="cta-card__icon">
                        <i class="fa-solid fa-calendar-check"></i>
                    </div>
                    <h3 class="h5 m-0 text-dark">Gestisci disponibilità</h3>
                    <p class="text-secondary mb-0">
                        Seleziona i giorni in cui puoi operare.
                    </p>
                </a>
            </div>

            <div class="col-12 col-md-6 col-lg-5">
                <a class="cta-card text-decoration-none d-block h-100" href="{{ route('timekeeper.racesList') }}"
                    aria-label="Vai alle gare assegnate">
                    <div class="cta-card__icon">
                        <i class="fa-solid fa-flag-checkered"></i>
                    </div>
                    <h3 class="h5 m-0 text-dark">Le tue gare</h3>
                    <p class="text-secondary mb-0">
                        Visualizza elenco, dettagli e report delle gare.
                    </p>
                </a>
            </div>
        </section>
    </main>
</x-layout>
