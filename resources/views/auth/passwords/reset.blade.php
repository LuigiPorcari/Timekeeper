<x-layout documentTitle="Reset Password">
    <main class="container mt-5 pt-5" id="main-content" aria-labelledby="reset-password-title">
        <div class="row justify-content-center mt-4">
            <div class="col-12 col-md-6">
                <div class="card auth-card border-0 shadow-sm rounded-4 overflow-hidden">

                    {{-- Header gradiente --}}
                    <div class="auth-header px-4 py-4">
                        <h1 id="reset-password-title" class="h4 text-white mb-1">
                            Imposta nuova password
                        </h1>
                        <p class="text-white-50 mb-0">
                            Scegli una password sicura e confermala
                        </p>
                    </div>

                    <div class="card-body p-4 p-md-5">
                        <form method="POST" action="{{ route('password.update') }}"
                            aria-describedby="form-reset-password-desc" novalidate>
                            @csrf
                            <p id="form-reset-password-desc" class="visually-hidden">
                                Inserisci la tua email, una nuova password e conferma per completare il reset.
                            </p>
                            <input type="hidden" name="token" value="{{ $request->route('token') }}">

                            {{-- Email --}}
                            <div class="mb-4">
                                <label for="email" class="form-label fw-semibold">Email</label>
                                <div class="input-group input-icon">
                                    <span class="input-group-text" id="icon-email">
                                        <i class="fa-regular fa-envelope"></i>
                                    </span>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror"
                                        id="email" name="email" placeholder="nome@esempio.it"
                                        value="{{ old('email', $request->email) }}" required autofocus
                                        aria-describedby="icon-email @error('email') emailError @enderror"
                                        autocomplete="email">
                                </div>
                                @error('email')
                                    <div class="invalid-feedback d-block" id="emailError" role="alert">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            {{-- Nuova password --}}
                            <div class="mb-4">
                                <label for="password" class="form-label fw-semibold">Nuova Password</label>
                                <div class="input-group input-icon">
                                    <span class="input-group-text" id="icon-password">
                                        <i class="fa-solid fa-lock"></i>
                                    </span>
                                    <input type="password" class="form-control @error('password') is-invalid @enderror"
                                        id="password" name="password" placeholder="Inserisci la nuova password"
                                        required
                                        aria-describedby="icon-password @error('password') passwordError @enderror"
                                        autocomplete="new-password">
                                </div>
                                @error('password')
                                    <div class="invalid-feedback d-block" id="passwordError" role="alert">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            {{-- Conferma nuova password --}}
                            <div class="mb-4">
                                <label for="password_confirmation" class="form-label fw-semibold">Conferma
                                    Password</label>
                                <div class="input-group input-icon">
                                    <span class="input-group-text" id="icon-password-confirm">
                                        <i class="fa-solid fa-check"></i>
                                    </span>
                                    <input type="password" class="form-control" id="password_confirmation"
                                        name="password_confirmation" placeholder="Conferma la nuova password" required
                                        aria-describedby="icon-password-confirm" autocomplete="new-password">
                                </div>
                            </div>

                            {{-- Submit --}}
                            <div class="d-grid">
                                <button type="submit" class="btn btn-ficr" aria-label="Conferma reset della password">
                                    <i class="fa-solid fa-rotate-left me-2"></i> Reset Password
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <p class="text-center text-muted small mt-3">
                    Hai già aggiornato la password? <a href="{{ route('login') }}" class="text-decoration-none">Torna al
                        login</a>
                </p>
            </div>
        </div>
    </main>
</x-layout>
