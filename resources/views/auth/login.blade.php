<x-layout documentTitle="Login">
    <main class="container mt-5 pt-5" id="main-content" aria-labelledby="login-title">
        <div class="row justify-content-center mt-4">
            <div class="col-12 col-md-8 col-lg-6">
                <div class="card auth-card border-0 shadow-sm rounded-4 overflow-hidden">
                    <div class="auth-header px-4 py-4">
                        <h1 id="login-title" class="h4 text-white mb-1">Accedi</h1>
                        <p class="text-white-50 mb-0">Entra nel portale dei Cronometristi</p>
                    </div>

                    <div class="card-body p-4 p-md-5">
                        <form method="POST" action="{{ route('login') }}" aria-describedby="login-description"
                            novalidate>
                            <p id="login-description" class="visually-hidden">
                                Inserisci le credenziali per accedere alla piattaforma.
                            </p>
                            @csrf

                            {{-- Email --}}
                            <div class="mb-3">
                                <label for="email" class="form-label fw-semibold">Email</label>
                                <div class="input-group input-icon">
                                    <span class="input-group-text" id="icon-email">
                                        <i class="fa-regular fa-envelope"></i>
                                    </span>
                                    <input id="email" type="email"
                                        class="form-control @error('email') is-invalid @enderror" name="email"
                                        value="{{ old('email') }}" required autofocus
                                        aria-describedby="icon-email @error('email') email-error @enderror">
                                    @error('email')
                                        <div class="invalid-feedback" id="email-error" role="alert">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>

                            {{-- Password --}}
                            <div class="mb-3">
                                <label for="password" class="form-label fw-semibold">Password</label>
                                <div class="input-group input-icon">
                                    <span class="input-group-text" id="icon-password">
                                        <i class="fa-solid fa-lock"></i>
                                    </span>
                                    <input id="password" type="password"
                                        class="form-control @error('password') is-invalid @enderror" name="password"
                                        required
                                        aria-describedby="icon-password @error('password') password-error @enderror">
                                    <button type="button" class="btn btn-outline-secondary" id="togglePassword"
                                        aria-label="Mostra o nascondi password">
                                        <i class="fa-regular fa-eye"></i>
                                    </button>
                                    @error('password')
                                        <div class="invalid-feedback d-block" id="password-error" role="alert">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>

                            {{-- Ricordami / Password dimenticata --}}
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="form-check form-switch">
                                    <input type="checkbox" class="form-check-input" id="remember" name="remember">
                                    <label class="form-check-label" for="remember">Ricordami</label>
                                </div>
                                <a href="{{ route('password.request') }}" class="link-muted"
                                    aria-label="Recupera la password dimenticata">
                                    Password dimenticata?
                                </a>
                            </div>

                            {{-- Bottone login --}}
                            <div class="d-grid">
                                <button type="submit" class="btn btn-ficr" aria-label="Accedi al tuo account">
                                    <i class="fa-solid fa-right-to-bracket me-2"></i> Login
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Suggerimento registrazione (opzionale) --}}
                <p class="text-center text-muted small mt-3">
                    Non hai un account? <a href="{{ route('timekeeper.register.form') }}"
                        class="text-decoration-none">Registrati come cronometrista</a>
                </p>
            </div>
        </div>
    </main>

    {{-- Toggle password --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const btn = document.getElementById('togglePassword');
            const input = document.getElementById('password');
            btn?.addEventListener('click', function() {
                const isPwd = input.type === 'password';
                input.type = isPwd ? 'text' : 'password';
                this.querySelector('i')?.classList.toggle('fa-eye');
                this.querySelector('i')?.classList.toggle('fa-eye-slash');
            });
        });
    </script>
</x-layout>
