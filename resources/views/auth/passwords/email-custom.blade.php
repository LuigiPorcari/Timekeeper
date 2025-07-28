<x-layout documentTitle="Reset Password Email">
    <main class="container mt-5 pt-5" id="main-content" aria-labelledby="reset-password-title">
        <div class="row justify-content-center mt-4">
            <div class="col-12 col-md-6">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h1 id="reset-password-title" class="fs-4 mb-0">
                            Invia Link per il Reset della Password
                        </h1>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('password.email') }}" aria-describedby="reset-desc">
                            @csrf
                            <p id="reset-desc" class="visually-hidden">
                                Inserisci il tuo indirizzo email per ricevere il link di reimpostazione della password.
                            </p>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" id="email" name="email" class="form-control"
                                    placeholder="Inserisci la tua email" required aria-describedby="emailHelp">
                                <div id="emailHelp" class="form-text visually-hidden">
                                    Inserisci un indirizzo email valido associato al tuo account.
                                </div>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary"
                                    aria-label="Invia link di reset password">
                                    Invia Link
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>
</x-layout>
