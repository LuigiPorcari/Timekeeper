<x-layout documentTitle="Student Register">
    <div class="container my-5 pt-5">
        <div class="row justify-content-center mt-4">
            <div class="col-12 col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header fs-4">Registrati come Cronometrista</div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('timekeeper.register') }}">
                            @csrf

                            <!-- Nome -->
                            <div class="mb-3">
                                <label for="name" class="form-label">Nome</label>
                                <input id="name" type="text" name="name"
                                    class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}"
                                    required autofocus>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Cognome -->
                            <div class="mb-3">
                                <label for="surname" class="form-label">Cognome</label>
                                <input id="surname" type="text" name="surname"
                                    class="form-control @error('surname') is-invalid @enderror"
                                    value="{{ old('surname') }}" required>
                                @error('surname')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Email -->
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input id="email" type="email" name="email"
                                    class="form-control @error('email') is-invalid @enderror"
                                    value="{{ old('email') }}" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Data di nascita -->
                            <div class="mb-3">
                                <label for="date_of_birth" class="form-label">Data di nascita</label>
                                <input type="date" id="date_of_birth" name="date_of_birth" class="form-control"
                                    required>
                            </div>

                            <!-- Residenza -->
                            <div class="mb-3">
                                <label for="residence" class="form-label">Residenza</label>
                                <input id="residence" type="text" name="residence"
                                    class="form-control @error('residence') is-invalid @enderror"
                                    value="{{ old('residence') }}">
                                @error('residence')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Domicilio -->
                            <div class="mb-3">
                                <label for="domicile" class="form-label">Domicilio</label>
                                <input id="domicile" type="text" name="domicile"
                                    class="form-control @error('domicile') is-invalid @enderror"
                                    value="{{ old('domicile') }}" required>
                                @error('domicile')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Transferta -->
                            <div class="mb-3">
                                <label for="transfer" class="form-label">Transferta</label>
                                <select class="form-select" id="transfer" name="transfer" required>
                                    <option value="no">No</option>
                                    <option value="1">1 notte</option>
                                    <option value="2/5">tra 2 e 5 notti</option>
                                    <option value=">5">più di 5 notti</option>
                                </select>
                            </div>

                            <!-- Automunito -->
                            <div class="mb-3">
                                <label for="auto" class="form-label">Automunito</label>
                                <select class="form-select" id="auto" name="auto" required>
                                    <option value="1">Sì</option>
                                    <option value="0">No</option>
                                </select>
                            </div>

                            <!-- Password -->
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input id="password" type="password" name="password"
                                    class="form-control @error('password') is-invalid @enderror" required>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Conferma Password -->
                            <div class="mb-4">
                                <label for="password-confirm" class="form-label">Conferma Password</label>
                                <input id="password-confirm" type="password" name="password_confirmation"
                                    class="form-control" required>
                            </div>

                            <!-- Submit -->
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Registrati</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layout>
