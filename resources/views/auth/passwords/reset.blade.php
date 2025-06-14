<x-layout documentTitle="Reset Password">
    <div class="container mt-5 pt-5">
        <div class="row justify-content-center mt-4">
            <div class="col-12 col-md-6">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h3 class="mb-0">Reset Password</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('password.update') }}">
                            @csrf
                            <input type="hidden" name="token" value="{{ $request->route('token') }}">

                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email"
                                    class="form-control @error('email') is-invalid @enderror"
                                    id="email" name="email" placeholder="Inserisci la tua email"
                                    value="{{ old('email', $request->email) }}" required autofocus>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Nuova Password</label>
                                <input type="password"
                                    class="form-control @error('password') is-invalid @enderror"
                                    id="password" name="password" placeholder="Inserisci la nuova password" required>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label for="password_confirmation" class="form-label">Conferma Password</label>
                                <input type="password" class="form-control"
                                    id="password_confirmation" name="password_confirmation"
                                    placeholder="Conferma la nuova password" required>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Reset Password</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layout>
