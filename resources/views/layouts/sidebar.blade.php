<!-- ========== Left Sidebar Start ========== -->
<div class="left-side-menu">

    <div class="h-100" data-simplebar>

        <!-- User box -->
        <div class="user-box text-center">
            <!-- IMAGEN SIMPLIFICADA SIN ERRORES -->
            <img src="{{ asset('assets/images/users/default-avatar.jpg') }}" alt="foto de perfil"
                class="rounded-circle avatar-md">

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
                <!-- Dashboard - Visible para todos -->
                <li>
                    <a href="{{ route('dashboard') }}">
                        <i data-feather="home"></i>
                        <span> Dashboard </span>
                    </a>
                </li>



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
                        Auth::user()->hasPermission('attendance.reports'))
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
                            </ul>
                        </div>
                    </li>
                @endif

                <!-- Módulo Postulaciones - Solo visible si tiene permiso -->
                @if (Auth::user()->hasPermission('postulaciones.view'))
                    <li>
                        <a href="#sidebarPostulaciones" data-bs-toggle="collapse">
                            <i data-feather="file-text"></i>
                            <span> Postulaciones </span>
                            <span class="menu-arrow"></span>
                        </a>
                        <div class="collapse" id="sidebarPostulaciones">
                            <ul class="nav-second-level">
                                <li><a href="{{ route('postulaciones.index') }}">Ver Postulaciones</a></li>
                                @if (Auth::user()->hasPermission('postulaciones.reports'))
                                    <li><a href="#">Reportes</a></li>
                                @endif
                                @if (Auth::user()->hasPermission('postulaciones.statistics'))
                                    <li><a href="#">Estadísticas</a></li>
                                @endif
                            </ul>
                        </div>
                    </li>
                @endif

                <!-- Ajustes de perfil - Visible para todos -->
                <li>
                    <a href="{{ route('perfil.index') }}">
                        <i data-feather="user"></i>
                        <span> Mi Perfil </span>
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
