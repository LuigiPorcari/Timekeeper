<x-layout documentTitle="Homepage">
    <div class="pt-5">
        <h1 class="mt-4">Welcome</h1>
    </div>
    @if (!Auth::check())
        <div class="container mt-5 pt-1">
            <div class="row mt-5 pt-5 justify-content-center">
                <div class="col-4">
                    <!-- Pulsante Accedi-->
                    <a class="btn btn-primary" href="{{ route('login') }}">Accedi</a>
                </div>
                <div class="col-4">
                    <!-- Pulsante Registrati-->
                    <a class="btn btn-primary" href="{{ route('timekeeper.register.form') }}">Registrati</a>
                </div>
                <div class="col-4">
                    <!-- Pulsante Registrati Admin-->
                    <a class="btn btn-primary" href="{{ route('admin.register.form') }}">Registrati Admin</a>
                </div>
            </div>
        </div>
    @endif
</x-layout>
