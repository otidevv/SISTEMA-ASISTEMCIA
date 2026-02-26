<div class="left-side-menu">
    <div class="h-100" data-simplebar>

        <!-- User box -->
        <div class="user-box text-center" style="display: block !important;">
            @if (Auth::user()->foto_perfil)
                <img src="{{ asset('storage/' . Auth::user()->foto_perfil) }}" alt="user-image"
                    title="{{ Auth::user()->nombre }}" class="rounded-circle avatar-md shadow-sm">
            @else
                <img src="{{ asset('assets/images/users/default-avatar.jpg') }}" alt="user-image"
                    class="rounded-circle avatar-md shadow-sm">
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
                            <i data-feather="log-out" class="icon-dual icon-xs me-1"></i><span>Cerrar
                                Sesión</span>
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

        <div id="sidebar-menu">
            <ul class="side-menu">
                <li class="menu-title">Navegación</li>
                <!-- Dashboard -->
                <li>
                    <a href="{{ route('dashboard') }}">
                        <i data-feather="home"></i>
                        <span> Dashboard </span>
                    </a>
                </li>

                <li class="menu-title mt-2">Personal</li>

                <!-- Horarios -->
                @can('horarios-docentes.view')
                <li>
                    <a href="{{ route('horarios-docentes.index') }}">
                        <i data-feather="clock"></i>
                        <span> Mis Horarios </span>
                    </a>
                </li>
                @endcan
                
                <!-- Carga Horaria -->
                @can('carga-horaria.mi-horario')
                <li>
                    <a href="{{ route('mi-horario') }}">
                        <i data-feather="calendar"></i>
                        <span> Mi Carga Horaria </span>
                    </a>
                </li>
                @endcan
                
                <!-- Reportes -->
                @can('asistencia-docente.reports')
                <li>
                    <a href="{{ route('asistencia-docente.mis-reportes') }}">
                        <i data-feather="file-text"></i>
                        <span> Mis Reportes </span>
                    </a>
                </li>
                @endcan

                <!-- Asistencia -->
                @can('asistencia-docente.view')
                <li>
                    <a href="#sidebarAsistencia" data-bs-toggle="collapse">
                        <i data-feather="check-square"></i>
                        <span> Asistencia </span>
                        <span class="menu-arrow"></span>
                    </a>
                    <div class="collapse" id="sidebarAsistencia">
                        <ul class="nav-second-level">
                            <li>
                                <a href="{{ route('asistencia-docente.index') }}">Ver Registros</a>
                            </li>
                            @can('asistencia-docente.create')
                            <li>
                                <a href="{{ route('asistencia-docente.create') }}">Registrar</a>
                            </li>
                            @endcan
                            @can('asistencia-docente.edit')
                            <li>
                                <a href="{{ route('asistencia-docente.index') }}">Editar</a>
                            </li>
                            @endcan
                            @can('asistencia-docente.monitor')
                            <li>
                                <a href="{{ route('asistencia-docente.monitor') }}">Monitor en Tiempo Real</a>
                            </li>
                            @endcan
                            @can('asistencia-docente.export')
                            <li>
                                <a href="{{ route('asistencia-docente.exportar') }}">Exportar</a>
                            </li>
                            @endcan
                            @can('asistencia-docente.reports')
                            <li>
                                <a href="{{ route('asistencia-docente.reports') }}">Reportes</a>
                            </li>
                            @endcan
                        </ul>
                    </div>
                </li>
                @endcan

                <li class="menu-title mt-2">Académico</li>

                <!-- Cursos -->
                @can('cursos.view')
                <li>
                    <a href="{{ route('cursos.index') }}">
                        <i data-feather="book"></i>
                        <span> Mis Cursos </span>
                    </a>
                </li>
                @endcan

                <!-- Materiales Académicos -->
                @can('material-academico.ver')
                    <li>
                        <a href="#sidebarMaterial" data-bs-toggle="collapse">
                            <i data-feather="layers"></i>
                            <span> Materiales Académicos </span>
                            <span class="menu-arrow"></span>
                        </a>
                        <div class="collapse" id="sidebarMaterial">
                            <ul class="nav-second-level">
                                <li>
                                    <a href="{{ route('materiales-academicos.index') }}">Ver Materiales</a>
                                </li>
                                @can('material-academico.crear')
                                <li>
                                    <a href="{{ route('materiales-academicos.crear') }}">Subir Material</a>
                                </li>
                                @endcan
                            </ul>
                        </div>
                    </li>
                @endcan

                <!-- Módulo Boletines -->
                @can('boletines.view')
                <li>
                    <a href="{{ route('boletines.index') }}">
                        <i data-feather="book-open"></i>
                        <span> Boletines Académicos </span>
                    </a>
                </li>
                @endcan

                <!-- Pagos -->
                @can('pagos-docentes.view')
                <li>
                    <a href="{{ route('pagos-docentes.index') }}">
                        <i data-feather="dollar-sign"></i>
                        <span> Mis Pagos </span>
                    </a>
                </li>
                @endcan

                <li class="menu-title mt-2">Sistema</li>

                <!-- Perfil -->
                <li>
                    <a href="{{ route('perfil.index') }}">
                        <i data-feather="user"></i>
                        <span> Mi Perfil </span>
                    </a>
                </li>

                <!-- Configuración -->
                <li>
                    <a href="{{ route('perfil.configuracion') }}">
                        <i data-feather="settings"></i>
                        <span> Configuración </span>
                    </a>
                </li>

                <!-- Ayuda -->
                <li>
                    <a href="javascript:void(0);" onclick="showHelp()">
                        <i data-feather="help-circle"></i>
                        <span> Ayuda </span>
                    </a>
                </li>
            </ul>
        </div>
        <div class="clearfix"></div>
    </div>
</div>

@push('js')
<script>
function showHelp() {
    // Aquí puedes implementar la lógica para mostrar la ayuda
    // Por ejemplo, abrir un modal con información de ayuda
    alert('Sección de ayuda en construcción');
}
</script>
@endpush