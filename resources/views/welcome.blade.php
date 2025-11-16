<x-layout documentTitle="Homepage">
    <main class="container mt-5 pt-5" role="main" aria-labelledby="homepage-title">
        <h1 id="homepage-title" class="visually-hidden">Benvenuto</h1>

        @guest
            {{-- Intro hero-card --}}
            <section class="row justify-content-center mb-4">
                <div class="col-12 col-lg-10">
                    <div class="card p-4 p-md-5 bg-white border-0 shadow-sm rounded-4">
                        <div class="row align-items-center g-4">
                            <div class="col-md">
                                <h2 class="h3 mb-2 text-dark">Benvenuto nel portale dei Cronometristi</h2>
                                <p class="mb-0 text-secondary">
                                    Gestisci gare, disponibilità e report in un’unica piattaforma semplice e veloce.
                                </p>
                            </div>
                            <div class="col-md-auto">
                                <a class="btn btn-ficr btn-lg px-4" href="{{ route('login') }}"
                                    aria-label="Accedi al tuo account">
                                    <i class="fa-solid fa-right-to-bracket me-2"></i> Accedi
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            {{-- CTA tiles: prima riga --}}
            <section class="row justify-content-center g-4" aria-label="Azioni disponibili">
                <div class="col-12 col-md-6 col-lg-4">
                    <a class="cta-card text-decoration-none d-block h-100" href="{{ route('timekeeper.register.form') }}"
                        aria-label="Registrati come cronometrista">
                        <div class="cta-card__icon">
                            <i class="fa-solid fa-stopwatch"></i>
                        </div>
                        <h3 class="h5 m-0 text-dark">Registrati Cronometrista</h3>
                        <p class="text-secondary mb-0">Crea il tuo profilo e indica le tue disponibilità.</p>
                    </a>
                </div>

                <div class="col-12 col-md-6 col-lg-4">
                    <a class="cta-card text-decoration-none d-block h-100" href="{{ route('guest.createRaceTemp.form') }}"
                        aria-label="Inserisci richiesta gara">
                        <div class="cta-card__icon">
                            <i class="fa-solid fa-flag-checkered"></i>
                        </div>
                        <h3 class="h5 m-0 text-dark">Inserisci richiesta gara</h3>
                        <p class="text-secondary mb-0">Invia una gara da confermare senza registrazione.</p>
                    </a>
                </div>
            </section>

            {{-- CTA: seconda riga solo con Risultati Live --}}
            <section class="row justify-content-center mt-3">
                <div class="col-12 col-md-6 col-lg-4">
                    <a class="cta-card text-decoration-none d-block h-100 text-center" href="http://www.cronotorino.it/"
                        target="_blank" rel="noopener" aria-label="Vai ai risultati live su Cronotorino">
                        <div class="cta-card__icon mx-auto">
                            <i class="fa-solid fa-trophy"></i>
                        </div>
                        <h3 class="h5 m-0 text-dark">Risultati Live</h3>
                        <p class="text-secondary mb-0">http://www.cronotorino.it/</p>
                    </a>
                </div>
            </section>


        @endguest
    </main>
</x-layout>
