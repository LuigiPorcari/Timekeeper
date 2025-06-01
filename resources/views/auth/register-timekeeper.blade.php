<x-layout documentTitle="Student Register">
    <div class="container mt-5 pt-5">
        <div class="row justify-content-center mt-4">
            <div class="col-12 col-md-8">
                <div class="card">
                    <div class="card-header fs-4">Registrati come Cronometrista</div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('timekeeper.register') }}">
                            @csrf
                            <!-- Nome -->
                            <div class="form-group mb-3">
                                <label for="name">Nome</label>
                                <input id="name" type="text" class="@error('name') is-invalid @enderror"
                                    name="name" value="{{ old('name') }}" required autofocus>
                                @error('name')
                                    <span role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <!-- Cognome -->
                            <div class="form-group mb-3">
                                <label for="surname">Nome</label>
                                <input id="surname" type="text" class="@error('surname') is-invalid @enderror"
                                    name="surname" value="{{ old('surname') }}" required autofocus>
                                @error('surname')
                                    <span role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <!-- Email -->
                            <div class="form-group mb-3">
                                <label for="email">Email</label>
                                <input id="email" type="email" class="@error('email') is-invalid @enderror"
                                    name="email" value="{{ old('email') }}" required>
                                @error('email')
                                    <span role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            {{-- DATA DI NASCITA --}}
                            <div class="mb-3">
                                <label for="date_of_birth">Data di nascita</label>
                                <input type="date" id="date_of_birth" name="date_of_birth" required>
                            </div>
                            <!-- Residenza -->
                            <div class="form-group mb-3">
                                <label for="residence">Residenza</label>
                                <input id="residence" type="text" class="@error('residence') is-invalid @enderror"
                                    name="residence" value="{{ old('residence') }}">
                                @error('residence')
                                    <span role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <!-- Domicilio -->
                            <div class="form-group mb-3">
                                <label for="domicile">Domicilio</label>
                                <input id="domicile" type="text" class="@error('domicile') is-invalid @enderror"
                                    name="domicile" value="{{ old('domicile') }}" required>
                                @error('domicile')
                                    <span role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <!-- Transferta -->
                            <div lass="form-group mb-3">
                                <label for="transfer">Transferta</label>
                                <select class="form-select" id="transfer" name="transfer" required>
                                    <option value="no">No</option>
                                    <option value="1">1 notte</option>
                                    <option value="2/3">tra 2 e 5 notti</option>
                                    <option value=">5">più di 5 notti</option>
                                </select>
                            </div>
                            <!-- Automunito -->
                            <div lass="form-group mb-3">
                                <label for="auto">Automunito</label>
                                <select class="form-select" id="auto" name="auto" required>
                                    <option value="1">Si</option>
                                    <option value="0">No</option>
                                </select>
                            </div>
                            <!-- Password -->
                            <div class="form-group mb-3">
                                <label for="password">Password</label>
                                <input id="password" type="password" class="@error('password') is-invalid @enderror"
                                    name="password" required>
                                @error('password')
                                    <span role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                            </div>
                            <!-- Conferma Password -->
                            <div class="form-group mb-3">
                                <label for="password-confirm">Conferma
                                    Password</label>
                                <input id="password-confirm" type="password" name="password_confirmation" required>
                            </div>
                            <!-- Bottone di submit -->
                            <div class="form-group mb-0">
                                <button type="submit" class="btn">
                                    Registrati
                                </button>
                            </div>
                    </div>
                </div>
                </form>
            </div>
        </div>
    </div>
</x-layout>
