<x-layout documentTitle="Password Update">
    <main class="container mt-5 pt-5" id="main-content" aria-labelledby="password-update-title">
        <div class="row justify-content-center mt-4">
            <div class="col-12 col-md-8">
                <div class="card auth-card border-0 shadow-sm rounded-4 overflow-hidden" role="form"
                    aria-describedby="password-update-description">

                    {{-- Header gradiente --}}
                    <div class="auth-header px-4 py-4">
                        <h1 id="password-update-title" class="h4 text-white mb-1">Cambia Password</h1>
                        <p class="text-white-50 mb-0">Imposta una nuova password sicura per il tuo account</p>
                    </div>

                    <div class="card-body p-4 p-md-5">
                        <p id="password-update-description" class="visually-hidden">
                            Inserisci la tua vecchia password e imposta una nuova password sicura.
                        </p>

                        <form method="POST" action="{{ route('password.change') }}" novalidate>
                            @csrf

                            {{-- Vecchia Password --}}
                            <div class="mb-3">
                                <label for="old_password" class="form-label fw-semibold">Vecchia Password</label>
                                <div class="input-group input-icon">
                                    <span class="input-group-text" id="icon-old-pass">
                                        <i class="fa-solid fa-lock"></i>
                                    </span>
                                    <input id="old_password" type="password"
                                        class="form-control @error('old_password') is-invalid @enderror"
                                        name="old_password" required
                                        aria-describedby="icon-old-pass @error('old_password') old-password-error @enderror">
                                    <button type="button" class="btn btn-outline-secondary" id="toggleOldPassword"
                                        aria-label="Mostra o nascondi password attuale">
                                        <i class="fa-regular fa-eye"></i>
                                    </button>
                                    @error('old_password')
                                        <div class="invalid-feedback d-block" id="old-password-error" role="alert">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>

                            {{-- Nuova Password --}}
                            <div class="mb-3">
                                <label for="password" class="form-label fw-semibold">Nuova Password</label>
                                <div class="input-group input-icon">
                                    <span class="input-group-text" id="icon-new-pass">
                                        <i class="fa-solid fa-shield-halved"></i>
                                    </span>
                                    <input id="password" type="password"
                                        class="form-control @error('password') is-invalid @enderror" name="password"
                                        required
                                        aria-describedby="icon-new-pass @error('password') new-password-error @enderror">
                                    <button type="button" class="btn btn-outline-secondary" id="toggleNewPassword"
                                        aria-label="Mostra o nascondi nuova password">
                                        <i class="fa-regular fa-eye"></i>
                                    </button>
                                    @error('password')
                                        <div class="invalid-feedback d-block" id="new-password-error" role="alert">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                                <small class="text-muted">
                                    Usa almeno 8 caratteri, includi lettere e numeri.
                                </small>
                            </div>

                            {{-- Conferma Nuova Password --}}
                            <div class="mb-4">
                                <label for="password-confirm" class="form-label fw-semibold">Conferma Nuova
                                    Password</label>
                                <div class="input-group input-icon">
                                    <span class="input-group-text" id="icon-new-pass2">
                                        <i class="fa-solid fa-shield"></i>
                                    </span>
                                    <input id="password-confirm" type="password" class="form-control"
                                        name="password_confirmation" required aria-describedby="icon-new-pass2">
                                    <button type="button" class="btn btn-outline-secondary" id="toggleConfirmPassword"
                                        aria-label="Mostra o nascondi conferma password">
                                        <i class="fa-regular fa-eye"></i>
                                    </button>
                                </div>
                            </div>

                            {{-- Submit --}}
                            <div class="d-grid">
                                <button type="submit" class="btn btn-ficr" aria-label="Conferma cambio password">
                                    <i class="fa-solid fa-key me-2"></i> Cambia Password
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <p class="text-center text-muted small mt-3">
                    Hai problemi ad accedere? <a href="{{ route('password.request') }}"
                        class="text-decoration-none">Recupera password</a>
                </p>
            </div>
        </div>
    </main>

    {{-- JS toggle mostra/nascondi --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            function wireToggle(btnId, inputId) {
                const btn = document.getElementById(btnId);
                const input = document.getElementById(inputId);
                if (!btn || !input) return;
                btn.addEventListener('click', function() {
                    input.type = input.type === 'password' ? 'text' : 'password';
                    const icon = this.querySelector('i');
                    if (icon) {
                        icon.classList.toggle('fa-eye');
                        icon.classList.toggle('fa-eye-slash');
                    }
                });
            }
            wireToggle('toggleOldPassword', 'old_password');
            wireToggle('toggleNewPassword', 'password');
            wireToggle('toggleConfirmPassword', 'password-confirm');
        });
    </script>
</x-layout>
