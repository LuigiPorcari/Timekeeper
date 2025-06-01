<x-layout documentTitle="Reset Password">
    <div class="container mt-5 pt-5">
        <div class="row justify-content-center mt-4">
            <div class="col-12 col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3>Reset Password</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('password.update') }}">
                            @csrf
                            <input type="hidden" name="token" value="{{ $request->route('token') }}">
                            <div class="form-group mb-3">
                                <label for="email">Email</label>
                                <input type="email" class="@error('email') is-invalid @enderror" id="email"
                                    name="email" placeholder="Inserisci la tua email"
                                    value="{{ old('email', $request->email) }}" required autofocus>
                                @error('email')
                                    <span role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <div class="form-group mb-3">
                                <label for="password">Nuova Password</label>
                                <input type="password" class="@error('password') is-invalid @enderror" id="password"
                                    name="password" placeholder="Inserisci la nuova password" required>
                                @error('password')
                                    <span role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>

                            <div class="form-group mb-3">
                                <label for="password_confirmation">Conferma Password</label>
                                <input type="password" id="password_confirmation" name="password_confirmation"
                                    placeholder="Conferma la nuova password" required>
                            </div>
                            <button type="submit" class="btn">Reset Password</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layout>
