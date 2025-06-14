<x-layout documentTitle="Password Update">
    <div class="container mt-5 pt-5">
        <div class="row justify-content-center mt-4">
            <div class="col-12 col-md-8">
                <div class="card shadow-sm">
                    <div class="card-header fs-4">Cambia Password</div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('password.change') }}">
                            @csrf

                            <!-- Vecchia Password -->
                            <div class="mb-3">
                                <label for="old_password" class="form-label">Vecchia Password</label>
                                <input id="old_password" type="password"
                                    class="form-control @error('old_password') is-invalid @enderror" name="old_password"
                                    required>
                                @error('old_password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Nuova Password -->
                            <div class="mb-3">
                                <label for="password" class="form-label">Nuova Password</label>
                                <input id="password" type="password"
                                    class="form-control @error('password') is-invalid @enderror" name="password"
                                    required>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
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
                                <button type="submit" class="btn btn-primary">Cambia Password</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layout>
