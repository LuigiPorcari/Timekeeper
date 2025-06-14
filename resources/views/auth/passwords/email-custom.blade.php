<x-layout documentTitle="Reset Password Email">
    <div class="container mt-5 pt-5">
        <div class="row justify-content-center mt-4">
            <div class="col-12 col-md-6">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h3 class="mb-0">Invia Link per il Reset della Password</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('password.email') }}">
                            @csrf
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" id="email" name="email" class="form-control"
                                    placeholder="Inserisci la tua email" required>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Invia Link</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layout>
