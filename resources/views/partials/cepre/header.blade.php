<!-- Top Bar -->
<div class="top-bar">
    <div class="top-bar-content">
        <div class="help-desk">
            <i class="fas fa-phone-alt"></i>
            <span><strong>HELP DESK:</strong> +51 974 122 813</span>
        </div>
        <div class="info-links">
            <a href="#"><i class="fas fa-graduation-cap"></i> Estudiantes</a>
            <a href="#"><i class="fas fa-chalkboard-teacher"></i> Docentes</a>
            <a href="#"><i class="fas fa-user-graduate"></i> Alumni</a>
            <a href="#"><i class="fas fa-flask"></i> Investigación</a>
            <a href="#"><i class="fas fa-users"></i> Comunidad</a>
        </div>
    </div>
</div>

<!-- Header -->
<header class="main-header">
    <div class="header-content">
        <a href="{{ route('home') }}">
            <img src="{{ asset('assets_cepre/img/logo/logocepre1.svg') }}" onerror="this.onerror=null; this.src='https://placehold.co/150x60/ffffff/2C5F7C?text=CEPRE';" alt="CEPRE UNAMAD" class="logo">
        </a>

            <style>
                .nav-menu li { position: relative; }
                .badge-new {
                    background: #ff0055; /* Color vibrante para el 'NEW' */
                    color: white;
                    font-size: 8px;
                    font-weight: 900;
                    padding: 2px 4px;
                    border-radius: 4px;
                    line-height: 1;
                    /* Posicionamiento superscript */
                    position: absolute;
                    top: -5px;
                    right: -28px;
                    box-shadow: 0 2px 5px rgba(255, 0, 85, 0.2);
                    text-transform: uppercase;
                    animation: pulse-new 2s infinite;
                    pointer-events: none;
                }
                @keyframes pulse-new {
                    0% { opacity: 1; transform: scale(1); }
                    50% { opacity: 0.8; transform: scale(1.05); }
                    100% { opacity: 1; transform: scale(1); }
                }
            </style>
            <ul class="nav-menu" id="navMenu">
                <li><a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}">Inicio</a></li>
                <li><a href="{{ route('public.cursos') }}" class="{{ request()->routeIs('public.cursos') ? 'active' : '' }}">Cursos <span class="badge-new">NEW</span></a></li>
                <li><a href="{{ route('public.carreras') }}" class="{{ request()->routeIs('public.carreras') ? 'active' : '' }}">Carreras <span class="badge-new">NEW</span></a></li>
                <li><a href="{{ route('public.vacantes') }}" class="{{ request()->routeIs('public.vacantes') ? 'active' : '' }}">Vacantes <span class="badge-new">NEW</span></a></li>
                <li><a href="{{ route('resultados-examenes.public') }}" class="{{ request()->routeIs('resultados-examenes.public') ? 'active' : '' }}">Resultados</a></li>
                <li><a href="{{ route('home') }}#contacto">Contacto</a></li>
            </ul>
        </nav>

        <div class="header-buttons">
            <i class="search-icon fas fa-search"></i>
            <a href="{{ route('login') }}" class="btn btn-primary">
                <i class="far fa-user"></i>
                <span>Acceso</span>
            </a>
            <div class="menu-toggle">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
    </div>
</header>
