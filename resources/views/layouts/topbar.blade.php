<!-- Topbar Start -->
<div class="navbar-custom">
    <div class="container-fluid">
        <ul class="list-unstyled topnav-menu float-end mb-0">
            <li class="d-none d-lg-inline-block">
                <a class="nav-link" id="light-dark-mode" href="#">
                    <i data-feather="sun" class="light-mode"></i>
                    <i data-feather="moon" class="dark-mode"></i>
                </a>
            </li>

            <li class="dropdown d-none d-lg-inline-block">
                <a class="nav-link dropdown-toggle arrow-none" data-toggle="fullscreen" href="#"
                    onclick="document.documentElement.requestFullscreen()">
                    <i data-feather="maximize"></i>
                </a>
            </li>

            <li class="dropdown notification-list topbar-dropdown">
                <a class="nav-link dropdown-toggle nav-user me-0" data-bs-toggle="dropdown" href="#"
                    role="button" aria-haspopup="false" aria-expanded="false">
                    @if (Auth::user()->foto_perfil)
                        <img src="{{ asset(Auth::user()->foto_perfil) }}" alt="user-image" class="rounded-circle">
                    @else
                        <img src="{{ asset('assets/images/users/default-avatar.jpg') }}" alt="user-image"
                            class="rounded-circle">
                    @endif
                    <span class="pro-user-name d-sm-inline d-none ms-1">
                        {{ Auth::user()->nombre }} <i class="uil uil-angle-down"></i>
                    </span>
                </a>

                <div class="dropdown-menu dropdown-menu-end profile-dropdown ">
                    <!-- item-->
                    <div class="dropdown-header noti-title">
                        <h6 class="text-overflow m-0">¡Bienvenido!</h6>
                    </div>

                    <a href="{{ route('perfil.index') }}" class="dropdown-item notify-item">
                        <i data-feather="user" class="icon-dual icon-xs me-1"></i><span>Mi Perfil</span>
                    </a>

                    <a href="{{ route('perfil.configuracion') }}" class="dropdown-item notify-item">
                        <i data-feather="settings" class="icon-dual icon-xs me-1"></i><span>Configuración</span>
                    </a>

                    <div class="dropdown-divider"></div>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <a href="{{ route('logout') }}" class="dropdown-item notify-item"
                            onclick="event.preventDefault(); this.closest('form').submit();">
                            <i data-feather="log-out" class="icon-dual icon-xs me-1"></i><span>Cerrar Sesión</span>
                        </a>
                    </form>
                </div>
            </li>
        </ul>

        <!-- LOGO -->
        <div class="logo-box">
            <a href="{{ route('dashboard') }}" class="logo logo-dark">
                <span class="logo-sm">
                    <img src="{{ asset('assets/images/logo cepre black.png') }}" alt="" height="40">
                </span>
                <span class="logo-lg">
                    <img src="{{ asset('assets/images/logocepre1.svg') }}" alt="" height="40">
                </span>
            </a>

            <a href="{{ route('dashboard') }}" class="logo logo-light">
                <span class="logo-sm">
                    <img src="{{ asset('assets/images/logo cepre black.png') }}" alt="" height="40">
                </span>
                <span class="logo-lg">
                    <img src="{{ asset('assets/images/logocepre1.svg') }}" alt="" height="40">
                </span>
            </a>
        </div>

        <ul class="list-unstyled topnav-menu topnav-menu-left m-0">
            <li>
                <button class="button-menu-mobile">
                    <i data-feather="menu"></i>
                </button>
            </li>
        </ul>

        <div class="clearfix"></div>
    </div>
</div>
<!-- end Topbar -->
