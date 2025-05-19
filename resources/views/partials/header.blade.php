<!-- Topbar Start -->
<div class="navbar-custom">
    <div class="container-fluid">
        <ul class="list-unstyled topnav-menu float-end mb-0">

            <li class="d-none d-lg-block">
                <form class="app-search">
                    <div class="app-search-box dropdown">

                        <div class="input-group">
                            <input type="search" class="form-control" placeholder="Search..." id="top-search">
                            <button class="btn input-group-text" type="submit">
                                <i class="uil uil-search"></i>
                            </button>
                        </div>

                        <div class="dropdown-menu dropdown-lg" id="search-dropdown">
                            <!-- item-->
                            <div class="dropdown-header noti-title">
                                <h5 class="text-overflow mb-2">Found 05 results</h5>
                            </div>

                            <!-- item-->
                            <a href="javascript:void(0);" class="dropdown-item notify-item">
                                <i class="uil uil-sliders-v-alt me-1"></i>
                                <span>User profile settings</span>
                            </a>

                            <!-- item-->
                            <a href="javascript:void(0);" class="dropdown-item notify-item">
                                <i class="uil uil-home-alt me-1"></i>
                                <span>Analytics Report</span>
                            </a>

                            <!-- item-->
                            <a href="javascript:void(0);" class="dropdown-item notify-item">
                                <i class="uil uil-life-ring me-1"></i>
                                <span>How can I help you?</span>
                            </a>

                            <!-- item-->
                            <div class="dropdown-header noti-title">
                                <h6 class="text-overflow mb-2 text-uppercase">Users</h6>
                            </div>

                            <div class="notification-list">
                                <!-- item-->
                                <a href="javascript:void(0);" class="dropdown-item notify-item">
                                    <div class="d-flex text-align-start">
                                        <img class="me-2 rounded-circle"
                                            src="{{ asset('assets/images/users/avatar-1.jpg') }}"
                                            alt="Generic placeholder image" height="32">
                                        <div class="flex-grow-1">
                                            <h5 class="m-0 fs-14">Shirley Miller</h5>
                                            <span class="fs-12 mb-0">UI Designer</span>
                                        </div>
                                    </div>
                                </a>

                                <!-- item-->
                                <a href="javascript:void(0);" class="dropdown-item notify-item">
                                    <div class="d-flex text-align-start">
                                        <img class="me-2 rounded-circle"
                                            src="{{ asset('assets/images/users/avatar-2.jpg') }}"
                                            alt="Generic placeholder image" height="32">
                                        <div class="flex-grow-1">
                                            <h5 class="m-0 fs-14">Timothy Moreno</h5>
                                            <span class="fs-12 mb-0">Frontend Developer</span>
                                        </div>
                                    </div>
                                </a>
                            </div>

                        </div>
                    </div>
                </form>
            </li>

            <li class="dropdown d-inline-block d-lg-none">
                <a class="nav-link dropdown-toggle arrow-none" data-bs-toggle="dropdown" href="#" role="button"
                    aria-haspopup="false" aria-expanded="false">
                    <i data-feather="search"></i>
                </a>
                <div class="dropdown-menu dropdown-lg dropdown-menu-end p-0">
                    <form class="p-3">
                        <input type="text" class="form-control" placeholder="Search ..." aria-label="search here">
                    </form>
                </div>
            </li>

            <li class="d-none d-lg-inline-block">
                <a class="nav-link" id="light-dark-mode" href="#">
                    <i data-feather="sun" class="light-mode"></i>
                    <i data-feather="moon" class="dark-mode"></i>
                </a>
            </li>

            <li class="dropdown d-none d-lg-inline-block">
                <a class="nav-link dropdown-toggle arrow-none" data-toggle="fullscreen" href="#">
                    <i data-feather="maximize"></i>
                </a>
            </li>

            <li class="dropdown d-none d-lg-inline-block topbar-dropdown">
                <a class="nav-link dropdown-toggle arrow-none" data-bs-toggle="dropdown" href="#" role="button"
                    aria-haspopup="false" aria-expanded="false">
                    <i data-feather="grid"></i>
                </a>
                <div class="dropdown-menu dropdown-lg dropdown-menu-end p-0">

                    <div class="p-1">
                        <div class="row g-0">
                            <div class="col">
                                <a class="dropdown-icon-item" href="#">
                                    <img src="{{ asset('assets/images/brands/slack.png') }}" alt="slack">
                                    <span>Slack</span>
                                </a>
                            </div>
                            <div class="col">
                                <a class="dropdown-icon-item" href="#">
                                    <img src="{{ asset('assets/images/brands/github.png') }}" alt="Github">
                                    <span>GitHub</span>
                                </a>
                            </div>
                            <div class="col">
                                <a class="dropdown-icon-item" href="#">
                                    <img src="{{ asset('assets/images/brands/dribbble.png') }}" alt="dribbble">
                                    <span>Dribbble</span>
                                </a>
                            </div>
                        </div>
                    </div>

                </div>
            </li>

            <li class="dropdown d-none d-lg-inline-block topbar-dropdown">
                <a class="nav-link dropdown-toggle arrow-none" data-bs-toggle="dropdown" href="#"
                    role="button" aria-haspopup="false" aria-expanded="false">
                    <i data-feather="globe"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-end">

                    <!-- item-->
                    <a href="javascript:void(0);" class="dropdown-item">
                        <img src="{{ asset('assets/images/flags/us.jpg') }}" alt="user-image" class="me-1"
                            height="12">
                        <span class="align-middle">English</span>
                    </a>

                    <!-- item-->
                    <a href="javascript:void(0);" class="dropdown-item">
                        <img src="{{ asset('assets/images/flags/germany.jpg') }}" alt="user-image" class="me-1"
                            height="12">
                        <span class="align-middle">German</span>
                    </a>

                    <!-- item-->
                    <a href="javascript:void(0);" class="dropdown-item">
                        <img src="{{ asset('assets/images/flags/italy.jpg') }}" alt="user-image" class="me-1"
                            height="12">
                        <span class="align-middle">Italian</span>
                    </a>

                    <!-- item-->
                    <a href="javascript:void(0);" class="dropdown-item">
                        <img src="{{ asset('assets/images/flags/spain.jpg') }}" alt="user-image" class="me-1"
                            height="12">
                        <span class="align-middle">Spanish</span>
                    </a>

                    <!-- item-->
                    <a href="javascript:void(0);" class="dropdown-item">
                        <img src="{{ asset('assets/images/flags/russia.jpg') }}" alt="user-image" class="me-1"
                            height="12">
                        <span class="align-middle">Russian</span>
                    </a>

                </div>
            </li>
            <li class="dropdown notification-list topbar-dropdown">
                <a class="nav-link dropdown-toggle position-relative" data-bs-toggle="dropdown" href="#"
                    role="button" aria-haspopup="false" aria-expanded="false">
                    <i data-feather="bell"></i>
                    <span class="badge bg-danger rounded-circle noti-icon-badge">6</span>
                </a>
                <div class="dropdown-menu dropdown-menu-end dropdown-lg">

                    <!-- item-->
                    <div class="dropdown-item noti-title">
                        <h5 class="m-0">
                            <span class="float-end">
                                <a href="#" class="text-dark"><small>Clear All</small></a>
                            </span>Notification
                        </h5>
                    </div>

                    <div class="noti-scroll" data-simplebar>

                        <!-- item-->
                        <a href="javascript:void(0);" class="dropdown-item notify-item border-bottom">
                            <div class="notify-icon bg-primary"><i class="uil uil-user-plus"></i></div>
                            <p class="notify-details">New user registered.<small class="text-muted">5 hours
                                    ago</small>
                            </p>
                        </a>

                        <!-- item-->
                        <a href="javascript:void(0);" class="dropdown-item notify-item border-bottom">
                            <div class="notify-icon">
                                <img src="{{ asset('assets/images/users/avatar-1.jpg') }}"
                                    class="img-fluid rounded-circle" alt="" />
                            </div>
                            <p class="notify-details">Karen Robinson</p>
                            <p class="text-muted mb-0 user-msg">
                                <small>Wow ! this admin looks good and awesome design</small>
                            </p>
                        </a>

                        <!-- item-->
                        <a href="javascript:void(0);" class="dropdown-item notify-item border-bottom">
                            <div class="notify-icon">
                                <img src="{{ asset('assets/images/users/avatar-2.jpg') }}"
                                    class="img-fluid rounded-circle" alt="" />
                            </div>
                            <p class="notify-details">Cristina Pride</p>
                            <p class="text-muted mb-0 user-msg">
                                <small>Hi, How are you? What about our next meeting</small>
                            </p>
                        </a>

                        <!-- item-->
                        <a href="javascript:void(0);" class="dropdown-item notify-item border-bottom active">
                            <div class="notify-icon bg-success"><i class="uil uil-comment-message"></i> </div>
                            <p class="notify-details">
                                Jaclyn Brunswick commented on Dashboard<small class="text-muted">1 min
                                    ago</small>
                            </p>
                        </a>

                        <!-- item-->
                        <a href="javascript:void(0);" class="dropdown-item notify-item border-bottom">
                            <div class="notify-icon bg-danger"><i class="uil uil-comment-message"></i></div>
                            <p class="notify-details">
                                Caleb Flakelar commented on Admin<small class="text-muted">4 days ago</small>
                            </p>
                        </a>

                        <!-- item-->
                        <a href="javascript:void(0);" class="dropdown-item notify-item">
                            <div class="notify-icon bg-primary">
                                <i class="uil uil-heart"></i>
                            </div>
                            <p class="notify-details">
                                Carlos Crouch liked <b>Admin</b> <small class="text-muted">13 days ago</small>
                            </p>
                        </a>
                    </div>

                    <!-- All-->
                    <a href="javascript:void(0);"
                        class="dropdown-item text-center text-primary notify-item notify-all">
                        View all <i class="fe-arrow-right"></i>
                    </a>

                </div>
            </li>

            <li class="dropdown notification-list topbar-dropdown">
                <a class="nav-link dropdown-toggle nav-user me-0" data-bs-toggle="dropdown" href="#"
                    role="button" aria-haspopup="false" aria-expanded="false">
                    @if (Auth::user()->foto_perfil)
                        <img src="{{ asset('storage/' . Auth::user()->foto_perfil) }}" alt="user-image"
                            class="rounded-circle">
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

                    <a href="{{ route('perfil.index') }}" class="dropdown-item notify-item">
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

            <li class="dropdown notification-list">
                <button class="nav-link" data-bs-toggle="offcanvas" data-bs-target="#theme-settings-offcanvas"
                    type="button">
                    <i class="mdi mdi-cog-outline font-22"></i>
                    <i data-feather="settings"></i>
                </button>
            </li>

        </ul>

        <!-- LOGO -->
        <div class="logo-box">
            <a href="{{ url('/dashboard') }}" class="logo logo-dark">
                <span class="logo-sm">
                    <img src="{{ asset('assets/images/logo-sm.png') }}" alt="" height="30">
                    <!-- <span class="logo-lg-text-light">Shreyu</span> -->
                </span>
                <span class="logo-lg">
                    <img src="{{ asset('assets/images/logocepre1.svg') }}" alt="" height="45">
                    <!-- <span class="logo-lg-text-light">S</span> -->
                </span>
            </a>

            <a href="{{ url('/') }}" class="logo logo-light">
                <span class="logo-sm">
                    <img src="{{ asset('assets/images/logo-sm.png') }}" alt="" height="30">
                </span>
                <span class="logo-lg">
                    <img src="{{ asset('assets/images/logocepre1.psg') }}" alt="" height="45">
                </span>
            </a>
        </div>

        <ul class="list-unstyled topnav-menu topnav-menu-left m-0">
            <li>
                <button class="button-menu-mobile">
                    <i data-feather="menu"></i>
                </button>
            </li>

            <li>
                <!-- Mobile menu toggle (Horizontal Layout)-->
                <a class="navbar-toggle nav-link" data-bs-toggle="collapse" data-bs-target="#topnav-menu-content">
                    <div class="lines">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                </a>
                <!-- End mobile menu toggle-->
            </li>

            <li class="dropdown d-none d-xl-block">
                <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#" role="button"
                    aria-haspopup="false" aria-expanded="false">
                    Create New
                    <i class="uil uil-angle-down"></i>
                </a>
                <div class="dropdown-menu">
                    <!-- item-->
                    <a href="javascript:void(0);" class="dropdown-item">
                        <i class="uil uil-bag me-1"></i><span>New Projects</span>
                    </a>

                    <!-- item-->
                    <a href="javascript:void(0);" class="dropdown-item">
                        <i class="uil uil-user-plus me-1"></i><span>Create Users</span>
                    </a>

                    <!-- item-->
                    <a href="javascript:void(0);" class="dropdown-item">
                        <i class="uil uil-chart-pie me-1"></i><span>Revenue Report</span>
                    </a>

                    <!-- item-->
                    <a href="javascript:void(0);" class="dropdown-item">
                        <i class="uil uil-cog me-1"></i><span>Settings</span>
                    </a>

                    <div class="dropdown-divider"></div>

                    <!-- item-->
                    <a href="javascript:void(0);" class="dropdown-item">
                        <i class="uil uil-question-circle me-1"></i><span>Help & Support</span>
                    </a>

                </div>
            </li>

        </ul>

        <div class="clearfix"></div>
    </div>
</div>
<!-- end Topbar -->

<!-- ========== Left Sidebar Start ========== -->
<div class="left-side-menu">

    <div class="h-100" data-simplebar>

        <!-- User box -->
        <div class="user-box text-center">
            @if (Auth::user()->foto_perfil)
                <img src="{{ asset(Auth::user()->foto_perfil) }}" alt="foto de perfil"
                    title="{{ Auth::user()->nombre }}" class="rounded-circle avatar-md">
            @else
                <img src="{{ asset('assets/images/users/default-avatar.jpg') }}" alt="foto de perfil"
                    class="rounded-circle avatar-md">
            @endif
            <div class="dropdown">
                <a href="javascript: void(0);" class="dropdown-toggle h5 mt-2 mb-1 d-block"
                    data-bs-toggle="dropdown">{{ Auth::user()->nombre }} {{ Auth::user()->apellido_paterno }}</a>
                <div class="dropdown-menu user-pro-dropdown">

                    <a href="{{ route('perfil.index') }}" class="dropdown-item notify-item">
                        <i data-feather="user" class="icon-dual icon-xs me-1"></i><span>Mi Perfil</span>
                    </a>
                    <a href="{{ route('perfil.configuracion') }}" class="dropdown-item notify-item">
                        <i data-feather="settings" class="icon-dual icon-xs me-1"></i><span>Configuración</span>
                    </a>
                    <a href="javascript:void(0);" class="dropdown-item notify-item">
                        <i data-feather="help-circle" class="icon-dual icon-xs me-1"></i><span>Ayuda</span>
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
            </div>
            <p>
                @foreach (Auth::user()->roles as $role)
                    {{ $role->nombre }}{{ !$loop->last ? ', ' : '' }}
                @endforeach
            </p>
        </div>

        <!--- Sidemenu -->
        <div id="sidebar-menu">

            <ul class="side-menu">

                <!-- <li class="menu-title">Navegación</li> -->

                <li>
                    <a href="{{ route('dashboard') }}">
                        <i data-feather="home"></i>
                        <span> Dashboard </span>
                    </a>
                </li>

                <li class="menu-title mt-2">Módulos</li>

                <!-- Módulo Usuarios - Solo visible si tiene permiso -->
                @if (Auth::user()->hasPermission('users.view'))
                    <li>
                        <a href="#sidebarUsuarios" data-bs-toggle="collapse">
                            <i data-feather="users"></i>
                            <span> Usuarios </span>
                            <span class="menu-arrow"></span>
                        </a>
                        <div class="collapse" id="sidebarUsuarios">
                            <ul class="nav-second-level">
                                <li><a href="{{ route('usuarios.index') }}">Listar Usuarios</a></li>
                                @if (Auth::user()->hasPermission('users.create'))
                                    <li><a href="{{ route('usuarios.create') }}">Crear Usuario</a></li>
                                @endif
                            </ul>
                        </div>
                    </li>
                @endif

                <!-- Módulo Roles - Solo visible si tiene permiso -->
                @if (Auth::user()->hasPermission('roles.view'))
                    <li>
                        <a href="#sidebarRoles" data-bs-toggle="collapse">
                            <i data-feather="shield"></i>
                            <span> Roles y Permisos </span>
                            <span class="menu-arrow"></span>
                        </a>
                        <div class="collapse" id="sidebarRoles">
                            <ul class="nav-second-level">
                                <li><a href="{{ route('roles.index') }}">Listar Roles</a></li>
                                @if (Auth::user()->hasPermission('roles.create'))
                                    <li><a href="{{ route('roles.create') }}">Crear Rol</a></li>
                                @endif
                                @if (Auth::user()->hasPermission('roles.assign_permissions'))
                                    <li><a href="{{ route('roles.permisos') }}">Asignar Permisos</a></li>
                                @endif
                            </ul>
                        </div>
                    </li>
                @endif

                <!-- Módulo Asistencia - Accesible para todos con sus permisos específicos -->
                @if (Auth::user()->hasPermission('attendance.view') ||
                        Auth::user()->hasPermission('attendance.register') ||
                        Auth::user()->hasPermission('attendance.edit') ||
                        Auth::user()->hasPermission('attendance.reports') ||
                        Auth::user()->hasPermission('attendance.realtime'))
                    <li>
                        <a href="#sidebarAsistencia" data-bs-toggle="collapse">
                            <i data-feather="calendar"></i>
                            <span> Asistencia </span>
                            <span class="menu-arrow"></span>
                        </a>
                        <div class="collapse" id="sidebarAsistencia">
                            <ul class="nav-second-level">
                                @if (Auth::user()->hasPermission('attendance.view'))
                                    <li><a href="{{ route('asistencia.index') }}">Ver Registros</a></li>
                                @endif

                                @if (Auth::user()->hasPermission('attendance.register'))
                                    <li><a href="{{ route('asistencia.registrar') }}">Registrar Asistencia</a></li>
                                @endif

                                @if (Auth::user()->hasPermission('attendance.edit'))
                                    <li><a href="{{ route('asistencia.editar') }}">Editar Registros</a></li>
                                @endif

                                @if (Auth::user()->hasPermission('attendance.export'))
                                    <li><a href="{{ route('asistencia.exportar') }}">Exportar Registros</a></li>
                                @endif

                                @if (Auth::user()->hasPermission('attendance.reports'))
                                    <li><a href="{{ route('asistencia.reportes') }}">Reportes y Estadísticas</a></li>
                                @endif

                                <!-- Nueva opción para monitoreo en tiempo real -->
                                @if (Auth::user()->hasPermission('attendance.realtime'))
                                    <li><a href="{{ route('asistencia.tiempo-real') }}">Monitoreo en Tiempo Real</a>
                                    </li>
                                @endif
                            </ul>
                        </div>
                    </li>
                @endif


                <!-- Módulo Parentescos - Solo visible si tiene permiso -->
                @if (Auth::user()->hasPermission('parentescos.view'))
                    <li>
                        <a href="#sidebarParentescos" data-bs-toggle="collapse">
                            <i data-feather="home"></i>
                            <span> Parentescos </span>
                            <span class="menu-arrow"></span>
                        </a>
                        <div class="collapse" id="sidebarParentescos">
                            <ul class="nav-second-level">
                                <li><a href="{{ route('parentescos.index') }}">Listar Parentescos</a></li>
                                @if (Auth::user()->hasPermission('parentescos.create'))
                                    <li><a href="{{ route('parentescos.create') }}">Crear Parentesco</a></li>
                                @endif
                                <!-- Eliminada la opción problemática de Editar Parentescos -->
                            </ul>
                        </div>
                    </li>
                @endif

                <li class="menu-title mt-2">Configuración</li>

                <!-- Ajustes de perfil - Visible para todos -->
                <li>
                    <a href="{{ route('perfil.index') }}">
                        <i data-feather="user"></i>
                        <span> Mi Perfil </span>
                    </a>
                </li>

                <li>
                    <a href="{{ route('perfil.index') }}">
                        <i data-feather="settings"></i>
                        <span> Ajustes </span>
                    </a>
                </li>

                <li>
                    <a href="javascript:void(0);">
                        <i data-feather="help-circle"></i>
                        <span> Ayuda </span>
                    </a>
                </li>

            </ul>

        </div>
        <!-- End Sidebar -->

        <div class="clearfix"></div>

    </div>
    <!-- Sidebar -left -->

</div>
<!-- Left Sidebar End -->
