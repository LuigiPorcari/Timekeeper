<x-layout documentTitle="Homepage">
    <div class="container mt-5 pt-5">
        <h1 class="mb-4">Welcome</h1>

        @guest
            <div class="row justify-content-center g-4">
                <div class="col-4 d-grid">
                    <a class="btn btn-primary" href="{{ route('login') }}">Accedi</a>
                </div>
                <div class="col-4 d-grid">
                    <a class="btn btn-primary" href="{{ route('timekeeper.register.form') }}">Registrati</a>
                </div>
                <div class="col-4 d-grid">
                    <a class="btn btn-primary" href="{{ route('admin.register.form') }}">Registrati Admin</a>
                </div>
            </div>
        @endguest
    </div>
</x-layout>
