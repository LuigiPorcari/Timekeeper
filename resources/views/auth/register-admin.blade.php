<x-layout documentTitle="Admin Register">
    <main class="container mt-5 pt-5" id="main-content" aria-labelledby="register-title">
        <div class="row justify-content-center mt-4">
            <div class="col-12 col-lg-8">
                <div class="card auth-card border-0 shadow-sm rounded-4 overflow-hidden">
                    {{-- Header gradiente --}}
                    <div class="auth-header px-4 py-4">
                        <h1 id="register-title" class="h4 text-white mb-1">Registrati come Admin</h1>
                        <p class="text-white-50 mb-0">Crea l’account amministratore del portale</p>
                    </div>

                    <div class="card-body p-4 p-md-5">
                        <form method="POST" action="{{ route('admin.register') }}" aria-describedby="form-desc"
                            novalidate>
                            @csrf
                            <p id="form-desc" class="visually-hidden">
                                Compila i campi per creare un account amministratore.
                            </p>

                            {{-- Dati anagrafici --}}
                            <h2 class="form-section h6 text-uppercase text-muted mb-3">Dati anagrafici</h2>
                            <div class="row g-3">
                                {{-- Nome --}}
                                <div class="col-md-6">
                                    <label for="name" class="form-label fw-semibold">Nome</label>
                                    <div class="input-group input-icon">
                                        <span class="input-group-text" id="icon-name">
                                            <i class="fa-regular fa-user"></i>
                                        </span>
                                        <input id="name" type="text" name="name"
                                            class="form-control @error('name') is-invalid @enderror"
                                            value="{{ old('name') }}" required
                                            aria-describedby="icon-name @error('name') name-error @enderror">
                                        @error('name')
                                            <div class="invalid-feedback" id="name-error" role="alert">{{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Cognome --}}
                                <div class="col-md-6">
                                    <label for="surname" class="form-label fw-semibold">Cognome</label>
                                    <div class="input-group input-icon">
                                        <span class="input-group-text" id="icon-surname">
                                            <i class="fa-regular fa-user"></i>
                                        </span>
                                        <input id="surname" type="text" name="surname"
                                            class="form-control @error('surname') is-invalid @enderror"
                                            value="{{ old('surname') }}" required
                                            aria-describedby="icon-surname @error('surname') surname-error @enderror">
                                        @error('surname')
                                            <div class="invalid-feedback" id="surname-error" role="alert">
                                                {{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Email --}}
                                <div class="col-md-6">
                                    <label for="email" class="form-label fw-semibold">Email</label>
                                    <div class="input-group input-icon">
                                        <span class="input-group-text" id="icon-email">
                                            <i class="fa-regular fa-envelope"></i>
                                        </span>
                                        <input id="email" type="email" name="email"
                                            class="form-control @error('email') is-invalid @enderror"
                                            value="{{ old('email') }}" required
                                            aria-describedby="icon-email @error('email') email-error @enderror">
                                        @error('email')
                                            <div class="invalid-feedback" id="email-error" role="alert">
                                                {{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                {{-- Data di nascita --}}
                                <div class="col-md-6">
                                    <label for="date_of_birth" class="form-label fw-semibold">Data di nascita</label>
                                    <div class="input-group input-icon">
                                        <span class="input-group-text" id="icon-dob">
                                            <i class="fa-regular fa-calendar"></i>
                                        </span>
                                        <input type="date" id="date_of_birth" name="date_of_birth"
                                            class="form-control @error('date_of_birth') is-invalid @enderror" required
                                            aria-describedby="icon-dob @error('date_of_birth') dob-error @enderror">
                                        @error('date_of_birth')
                                            <div class="invalid-feedback" id="dob-error" role="alert">{{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            {{-- Residenza & Domicilio --}}
                            <h2 class="form-section h6 text-uppercase text-muted mt-4 mb-3">Indirizzo</h2>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="residence" class="form-label fw-semibold">Residenza</label>
                                    <div class="input-group input-icon">
                                        <span class="input-group-text" id="icon-res">
                                            <i class="fa-solid fa-house"></i>
                                        </span>
                                        <input id="residence" type="text" name="residence"
                                            class="form-control @error('residence') is-invalid @enderror"
                                            value="{{ old('residence') }}"
                                            aria-describedby="icon-res @error('residence') res-error @enderror">
                                        @error('residence')
                                            <div class="invalid-feedback" id="res-error" role="alert">{{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label for="domicile" class="form-label fw-semibold">Domicilio</label>
                                    <div class="input-group input-icon">
                                        <span class="input-group-text" id="icon-dom">
                                            <i class="fa-solid fa-location-dot"></i>
                                        </span>
                                        <input id="domicile" type="text" name="domicile"
                                            class="form-control @error('domicile') is-invalid @enderror"
                                            value="{{ old('domicile') }}" required
                                            aria-describedby="icon-dom @error('domicile') dom-error @enderror">
                                        @error('domicile')
                                            <div class="invalid-feedback" id="dom-error" role="alert">
                                                {{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            {{-- Logistica --}}
                            <h2 class="form-section h6 text-uppercase text-muted mt-4 mb-3">Logistica</h2>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="transfer" class="form-label fw-semibold">Transferta</label>
                                    <select class="form-select" id="transfer" name="transfer" required>
                                        <option value="no" {{ old('transfer') === 'no' ? 'selected' : '' }}>No
                                        </option>
                                        <option value="1" {{ old('transfer') === '1' ? 'selected' : '' }}>1 notte
                                        </option>
                                        <option value="2/5" {{ old('transfer') === '2/5' ? 'selected' : '' }}>tra 2 e
                                            5 notti</option>
                                        <option value=">5" {{ old('transfer') === '>5' ? 'selected' : '' }}>più di 5
                                            notti</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="auto" class="form-label fw-semibold">Automunito</label>
                                    <select class="form-select" id="auto" name="auto" required>
                                        <option value="1" {{ old('auto') === '1' ? 'selected' : '' }}>Sì</option>
                                        <option value="0" {{ old('auto') === '0' ? 'selected' : '' }}>No</option>
                                    </select>
                                </div>
                            </div>

                            {{-- Credenziali --}}
                            <h2 class="form-section h6 text-uppercase text-muted mt-4 mb-3">Credenziali</h2>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="password" class="form-label fw-semibold">Password</label>
                                    <div class="input-group input-icon">
                                        <span class="input-group-text" id="icon-pass">
                                            <i class="fa-solid fa-lock"></i>
                                        </span>
                                        <input id="password" type="password" name="password"
                                            class="form-control @error('password') is-invalid @enderror" required
                                            aria-describedby="icon-pass @error('password') pass-error @enderror">
                                        <button type="button" class="btn btn-outline-secondary" id="togglePassword"
                                            aria-label="Mostra o nascondi password">
                                            <i class="fa-regular fa-eye"></i>
                                        </button>
                                        @error('password')
                                            <div class="invalid-feedback d-block" id="pass-error" role="alert">
                                                {{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label for="password-confirm" class="form-label fw-semibold">Conferma
                                        Password</label>
                                    <div class="input-group input-icon">
                                        <span class="input-group-text" id="icon-pass2">
                                            <i class="fa-solid fa-lock"></i>
                                        </span>
                                        <input id="password-confirm" type="password" name="password_confirmation"
                                            class="form-control" required aria-describedby="icon-pass2">
                                        <button type="button" class="btn btn-outline-secondary" id="togglePassword2"
                                            aria-label="Mostra o nascondi conferma password">
                                            <i class="fa-regular fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            {{-- Submit --}}
                            <div class="d-grid mt-4">
                                <button type="submit" class="btn btn-ficr"
                                    aria-label="Conferma registrazione admin">
                                    <i class="fa-solid fa-user-shield me-2"></i> Registrati
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <p class="text-center text-muted small mt-3">
                    Sei già registrato? <a href="{{ route('login') }}" class="text-decoration-none">Accedi</a>
                </p>
            </div>
        </div>
    </main>

    {{-- Toggle password --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            function wireToggle(btnId, inputId) {
                const btn = document.getElementById(btnId);
                const input = document.getElementById(inputId);
                if (!btn || !input) return;
                btn.addEventListener('click', function() {
                    const isPwd = input.type === 'password';
                    input.type = isPwd ? 'text' : 'password';
                    const icon = this.querySelector('i');
                    if (icon) {
                        icon.classList.toggle('fa-eye');
                        icon.classList.toggle('fa-eye-slash');
                    }
                });
            }
            wireToggle('togglePassword', 'password');
            wireToggle('togglePassword2', 'password-confirm');
        });
    </script>
</x-layout>
