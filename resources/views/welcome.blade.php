<x-layout documentTitle="Homepage">
    <main class="container mt-5 pt-5" role="main">
        <h1 class="mb-4">Benvenuto</h1>

        @guest
            <section class="row justify-content-center g-4" aria-label="Scelte disponibili per utenti non autenticati">
                <div class="col-12 col-md-4 d-grid">
                    <a class="btn btn-primary" href="{{ route('login') }}" aria-label="Accedi al tuo account">Accedi</a>
                </div>
                <div class="col-12 col-md-4 d-grid">
                    <a class="btn btn-primary" href="{{ route('timekeeper.register.form') }}"
                        aria-label="Registrati come cronometrista">Registrati</a>
                </div>
            </section>
            <section class="row justify-content-center g-4 mt-3" aria-label="Scelte disponibili per utenti non autenticati">
                <div class="col-12 col-md-4 d-grid">
                    <a class="btn btn-primary" href="{{ route('guest.createRaceTemp.form') }}"
                        aria-label="Registrati come cronometrista">Inserisci richiesta gara</a>
                </div>
            </section>
        @endguest
    </main>
</x-layout>
