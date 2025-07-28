<x-layout documentTitle="Login">
    <main class="container mt-5 pt-5" id="main-content" aria-labelledby="login-title">
        <div class="row justify-content-center mt-4">
            <div class="col-12 col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h1 id="login-title" class="fs-4 mb-0">Login</h1>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('login') }}" aria-describedby="login-description">
                            <p id="login-description" class="visually-hidden">
                                Inserisci le credenziali per accedere alla piattaforma.
                            </p>
                            @csrf

                            <!-- Email -->
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input id="email" type="email"
                                    class="form-control @error('email') is-invalid @enderror" name="email"
                                    value="{{ old('email') }}" required autofocus
                                    aria-describedby="@error('email') email-error @enderror">
                                @error('email')
                                    <div class="invalid-feedback" id="email-error" role="alert">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <!-- Password -->
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input id="password" type="password"
                                    class="form-control @error('password') is-invalid @enderror" name="password"
                                    required aria-describedby="@error('password') password-error @enderror">
                                @error('password')
                                    <div class="invalid-feedback" id="password-error" role="alert">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <!-- Ricordami -->
                            <div class="form-check mb-3">
                                <input type="checkbox" class="form-check-input" id="remember" name="remember">
                                <label class="form-check-label" for="remember">Ricordami</label>
                            </div>

                            <!-- Password dimenticata -->
                            <div class="text-center mb-3">
                                <a href="{{ route('password.request') }}" class="text-decoration-none"
                                    aria-label="Recupera la password dimenticata">
                                    Password dimenticata?
                                </a>
                            </div>

                            <!-- Bottone login -->
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary" aria-label="Accedi al tuo account">
                                    Login
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>
</x-layout>
