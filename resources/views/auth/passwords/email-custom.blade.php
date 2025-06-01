<x-layout documentTitle="Reset Password Email">
    <div class="container mt-5 pt-5">
        <div class="row justify-content-center mt-4">
            <div class="col-12 col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3>Invia Link per il Reset della Password</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('password.email') }}">
                            @csrf
                            <div class="form-group mb-3">
                                <label for="email">Email</label>
                                <input type="email" id="email" name="email" placeholder="Inserisci la tua email"
                                    required>
                            </div>
                            <button type="submit" class="btn">Invia Link</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layout>
