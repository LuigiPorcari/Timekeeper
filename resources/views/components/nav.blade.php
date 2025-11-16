<nav class="navbar navbar-expand-lg navbar-dark ficr-navbar fixed-top shadow-sm" role="navigation"
    aria-label="Menu principale">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('homepage') }}">
            <i class="fa-solid fa-stopwatch"></i>
            <span>TimeKeeper</span>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
            aria-controls="navbarNav" aria-expanded="false" aria-label="Mostra/Nascondi menu">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            {{-- Sinistra: voci principali --}}
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('homepage') ? 'active' : '' }}"
                        href="{{ route('homepage') }}">Home</a>
                </li>

                @auth
                    @if (auth()->user()->is_segretarian)
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('secretariat.*') ? 'active' : '' }}"
                                href="{{ route('secretariat.dashboard') }}">Segreteria</a>
                        </li>
                    @endif
                @endauth
            </ul>

            {{-- Destra: registrazioni / profilo --}}
            <ul class="navbar-nav align-items-lg-center ms-auto gap-lg-2">
                {{-- @auth
                    @if (Auth::user()->is_admin) --}}
                        {{-- Versione mobile: link semplici --}}
                        <li class="nav-item d-lg-none">
                            <a class="nav-link" href="{{ route('secretariat.register.form') }}">Registrati Segreteria</a>
                        </li>
                        <li class="nav-item d-lg-none">
                            <a class="nav-link" href="{{ route('admin.register.form') }}">Registrati Admin</a>
                        </li>

                        {{-- Versione desktop: bottoni outline chiari --}}
                        <li class="nav-item d-none d-lg-block">
                            <a class="btn btn-sm btn-outline-light" href="{{ route('secretariat.register.form') }}">
                                Registrati Segreteria
                            </a>
                        </li>
                        <li class="nav-item d-none d-lg-block">
                            <a class="btn btn-sm btn-outline-light" href="{{ route('admin.register.form') }}">
                                Registrati Admin
                            </a>
                        </li>
                    {{-- @endif
                @endauth --}}
                @guest
                    <li class="nav-item ms-lg-2 mt-2 mt-lg-0">
                        <a class="btn btn-sm btn-light" href="{{ route('login') }}">Accedi</a>
                    </li>
                @endguest

                @auth
                    <li class="nav-item dropdown ms-lg-2">
                        <a class="nav-link dropdown-toggle fw-semibold" href="#" id="navbarDropdown" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fa-regular fa-user"></i>
                            <span class="ms-1">Ciao {{ Auth::user()->name }} {{ Auth::user()->surname }}</span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="navbarDropdown">
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
        </div>
    </div>
</nav>
