<nav class="navbar navbar-expand-lg bg-body-tertiary fixed-top" role="navigation" aria-label="Menu principale">
    <div class="container-fluid">
        <a class="navbar-brand" href="{{ route('homepage') }}">TimeKeeper</a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
            aria-controls="navbarNav" aria-expanded="false" aria-label="Mostra/Nascondi menu">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('homepage') ? 'active' : '' }}"
                        href="{{ route('homepage') }}">Home</a>
                </li>

                @auth
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            Ciao {{ Auth::user()->name }} {{ Auth::user()->surname }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                            <li>
                                <a class="dropdown-item" href="{{ route('password.change') }}">Cambia Password</a>
                            </li>
                            <li>
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="dropdown-item">Logout</button>
                                </form>
                            </li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <form method="POST" action="{{ route('user.destroy') }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="dropdown-item text-danger">
                                        Elimina il mio account
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </li>
                @endauth
            </ul>

            @auth
                @if (Auth::user()->is_admin)
                    <div class="ms-auto">
                        <a class="btn btn-outline-primary" href="{{ route('secretariat.register.form') }}">
                            Registrati Segreteria
                        </a>
                        <a class="btn btn-outline-primary" href="{{ route('admin.register.form') }}">
                            Registrati Admin
                        </a>
                    </div>
                @endif
            @endauth
        </div>
    </div>
</nav>
