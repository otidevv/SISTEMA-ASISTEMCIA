<div class="left-side-menu">
    <div class="h-100" data-simplebar>
        <div id="sidebar-menu">
            <ul id="side-menu">
                <!-- Dashboard -->
                <li>
                    <a href="{{ route('dashboard') }}">
                        <i class="mdi mdi-view-dashboard-outline"></i>
                        <span> Dashboard </span>
                    </a>
                </li>

                <!-- Horarios -->
                @can('horarios-docentes.view')
                <li>
                    <a href="{{ route('horarios-docentes.index') }}">
                        <i class="mdi mdi-calendar-clock"></i>
                        <span> Mis Horarios </span>
                    </a>
                </li>
                @endcan
                
                <!-- Carga Horaria -->
                @can('carga-horaria.mi-horario')
                <li>
                    <a href="{{ route('mi-horario') }}">
                        <i class="mdi mdi-clock-check-outline"></i>
                        <span> Mi Carga Horaria </span>
                    </a>
                </li>
                @endcan
                
                <!-- Reportes -->
                @can('asistencia-docente.view')
                <li>
                    <a href="{{ route('asistencia-docente.mis-reportes') }}">
                        <i class="mdi mdi-file-document-multiple-outline"></i>
                        <span> Mis Reportes </span>
                    </a>
                </li>
                @endcan

                <!-- Asistencia -->
                @can('asistencia-docente.view')
                <li>
                    <a href="#sidebarAsistencia" data-bs-toggle="collapse">
                        <i class="mdi mdi-clipboard-check-outline"></i>
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

                <!-- Cursos -->
                @can('cursos.view')
                <li>
                    <a href="{{ route('cursos.index') }}">
                        <i class="mdi mdi-book-open-variant"></i>
                        <span> Mis Cursos </span>
                    </a>
                </li>
                @endcan

                <!-- Materiales Académicos - RUTAS CORREGIDAS CON EL NOMBRE CORRECTO -->
                @can('material-academico.ver')
                    <li>
                        <a href="#sidebarMaterial" data-bs-toggle="collapse">
                            <i class="mdi mdi-book-multiple-outline"></i>
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
                        <i class="mdi mdi-notebook-check-outline"></i>
                        <span> Boletines Académicos </span>
                    </a>
                </li>
                @endcan

                <!-- Pagos -->
                @can('pagos-docentes.view')
                <li>
                    <a href="{{ route('pagos-docentes.index') }}">
                        <i class="mdi mdi-cash-multiple"></i>
                        <span> Mis Pagos </span>
                    </a>
                </li>
                @endcan

                <!-- Perfil -->
                <li>
                    <a href="{{ route('perfil.index') }}">
                        <i class="mdi mdi-account-circle-outline"></i>
                        <span> Mi Perfil </span>
                    </a>
                </li>

                <!-- Configuración -->
                <li>
                    <a href="{{ route('perfil.configuracion') }}">
                        <i class="mdi mdi-cog-outline"></i>
                        <span> Configuración </span>
                    </a>
                </li>

                <!-- Ayuda -->
                <li>
                    <a href="javascript:void(0);" onclick="showHelp()">
                        <i class="mdi mdi-help-circle-outline"></i>
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