<x-layout documentTitle="Reset Password">
    <main class="container mt-5 pt-5" id="main-content" aria-labelledby="reset-password-title">
        <div class="row justify-content-center mt-4">
            <div class="col-12 col-md-6">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h1 id="reset-password-title" class="fs-4 mb-0">Reset Password</h1>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('password.update') }}"
                            aria-describedby="form-reset-password-desc">
                            <p id="form-reset-password-desc" class="visually-hidden">
                                Inserisci la tua email, una nuova password e conferma per completare il reset.
                            </p>
                            @csrf
                            <input type="hidden" name="token" value="{{ $request->route('token') }}">

                            <!-- Email -->
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror"
                                    id="email" name="email" placeholder="Inserisci la tua email"
                                    value="{{ old('email', $request->email) }}" required autofocus
                                    aria-describedby="emailHelp @error('email') emailError @enderror">
                                <div id="emailHelp" class="form-text visually-hidden">
                                    Inserisci l'indirizzo email associato al tuo account.
                                </div>
                                @error('email')
                                    <div class="invalid-feedback" id="emailError" role="alert">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <!-- Nuova password -->
                            <div class="mb-3">
                                <label for="password" class="form-label">Nuova Password</label>
                                <input type="password" class="form-control @error('password') is-invalid @enderror"
                                    id="password" name="password" placeholder="Inserisci la nuova password" required
                                    aria-describedby="@error('password') passwordError @enderror">
                                @error('password')
                                    <div class="invalid-feedback" id="passwordError" role="alert">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <!-- Conferma nuova password -->
                            <div class="mb-4">
                                <label for="password_confirmation" class="form-label">Conferma Password</label>
                                <input type="password" class="form-control" id="password_confirmation"
                                    name="password_confirmation" placeholder="Conferma la nuova password" required>
                            </div>

                            <!-- Bottone di submit -->
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary"
                                    aria-label="Conferma reset della password">
                                    Reset Password
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>
</x-layout>
