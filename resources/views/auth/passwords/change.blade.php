<x-layout documentTitle="Password Update">
    <main class="container mt-5 pt-5" id="main-content" aria-labelledby="password-update-title">
        <div class="row justify-content-center mt-4">
            <div class="col-12 col-md-8">
                <div class="card shadow-sm" role="form" aria-describedby="password-update-description">
                    <div class="card-header fs-4" id="password-update-title">Cambia Password</div>
                    <div class="card-body">
                        <p id="password-update-description" class="visually-hidden">
                            Inserisci la tua vecchia password e imposta una nuova password sicura.
                        </p>

                        <form method="POST" action="{{ route('password.change') }}">
                            @csrf

                            <!-- Vecchia Password -->
                            <div class="mb-3">
                                <label for="old_password" class="form-label">Vecchia Password</label>
                                <input id="old_password" type="password"
                                    class="form-control @error('old_password') is-invalid @enderror" name="old_password"
                                    required aria-describedby="old-password-error">
                                @error('old_password')
                                    <div class="invalid-feedback" role="alert" id="old-password-error">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <!-- Nuova Password -->
                            <div class="mb-3">
                                <label for="password" class="form-label">Nuova Password</label>
                                <input id="password" type="password"
                                    class="form-control @error('password') is-invalid @enderror" name="password"
                                    required aria-describedby="new-password-error">
                                @error('password')
                                    <div class="invalid-feedback" role="alert" id="new-password-error">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <!-- Conferma Nuova Password -->
                            <div class="mb-4">
                                <label for="password-confirm" class="form-label">Conferma Nuova Password</label>
                                <input id="password-confirm" type="password" class="form-control"
                                    name="password_confirmation" required>
                            </div>

                            <!-- Bottone di submit -->
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary" aria-label="Conferma cambio password">
                                    Cambia Password
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>
</x-layout>
