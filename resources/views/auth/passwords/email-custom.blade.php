<x-layout documentTitle="Reset Password Email">
    <main class="container mt-5 pt-5" id="main-content" aria-labelledby="reset-password-title">
        <div class="row justify-content-center mt-4">
            <div class="col-12 col-md-6">
                <div class="card auth-card border-0 shadow-sm rounded-4 overflow-hidden">

                    {{-- Header gradiente --}}
                    <div class="auth-header px-4 py-4">
                        <h1 id="reset-password-title" class="h4 text-white mb-1">
                            Reimposta Password
                        </h1>
                        <p class="text-white-50 mb-0">
                            Riceverai un link per creare una nuova password
                        </p>
                    </div>

                    <div class="card-body p-4 p-md-5">

                        {{-- Messaggio successo invio email (Laravel usa session("status")) --}}
                        @if (session('status'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('status') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Chiudi"></button>
                            </div>
                        @endif

                        <form method="POST" action="{{ route('password.email') }}" aria-describedby="reset-desc"
                            novalidate>
                            @csrf
                            <p id="reset-desc" class="visually-hidden">
                                Inserisci il tuo indirizzo email per ricevere il link di reimpostazione della password.
                            </p>

                            {{-- Email --}}
                            <div class="mb-4">
                                <label for="email" class="form-label fw-semibold">Email</label>
                                <div class="input-group input-icon">
                                    <span class="input-group-text" id="icon-email">
                                        <i class="fa-regular fa-envelope"></i>
                                    </span>
                                    <input type="email" id="email" name="email"
                                        class="form-control @error('email') is-invalid @enderror"
                                        placeholder="nome@esempio.it" value="{{ old('email') }}" required
                                        aria-describedby="icon-email @error('email') email-error @enderror"
                                        autocomplete="email">
                                    @error('email')
                                        <div class="invalid-feedback d-block" id="email-error" role="alert">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                                <small class="text-muted">Usa l’indirizzo associato al tuo account.</small>
                            </div>

                            {{-- Submit --}}
                            <div class="d-grid">
                                <button type="submit" class="btn btn-ficr" aria-label="Invia link di reset password">
                                    <i class="fa-solid fa-paper-plane me-2"></i> Invia Link
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <p class="text-center text-muted small mt-3">
                    Ricordi la password? <a href="{{ route('login') }}" class="text-decoration-none">Accedi</a>
                </p>
            </div>
        </div>
    </main>
</x-layout>
