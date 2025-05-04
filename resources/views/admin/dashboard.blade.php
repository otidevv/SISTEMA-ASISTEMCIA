@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <!-- Start Content-->
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    <h4 class="page-title">Dashboard</h4>
                    <div class="page-title-right">
                        <form class="float-sm-end mt-3 mt-sm-0">
                            <div class="row g-2">
                                <div class="col-md-auto">
                                    <div class="mb-1 mb-sm-0">
                                        <input type="text" class="form-control" id="dash-daterange"
                                            style="min-width: 210px;" />
                                    </div>
                                </div>
                                <div class="col-md-auto">
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-primary dropdown-toggle"
                                            data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <i class='uil uil-file-alt me-1'></i>Download
                                            <i class="icon"><span data-feather="chevron-down"></span></i></button>
                                        <div class="dropdown-menu dropdown-menu-end">
                                            <a href="#" class="dropdown-item notify-item">
                                                <i data-feather="mail" class="icon-dual icon-xs me-2"></i>
                                                <span>Email</span>
                                            </a>
                                            <a href="#" class="dropdown-item notify-item">
                                                <i data-feather="printer" class="icon-dual icon-xs me-2"></i>
                                                <span>Print</span>
                                            </a>
                                            <div class="dropdown-divider"></div>
                                            <a href="#" class="dropdown-item notify-item">
                                                <i data-feather="file" class="icon-dual icon-xs me-2"></i>
                                                <span>Re-Generate</span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!-- end page title -->

        <!-- Welcome card -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="header-title">¡Bienvenido, {{ $user->nombre }}!</h4>
                        <p class="text-muted">
                            Accede a tus módulos disponibles o utiliza el menú lateral para navegar por el sistema.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats cards -->
        <div class="row">
            <div class="col-md-6 col-xl-3">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex">
                            <div class="flex-grow-1">
                                <span class="text-muted text-uppercase fs-12 fw-bold">Permisos Activos</span>
                                <h3 class="mb-0">{{ $user->permissions->count() }}</h3>
                            </div>
                            <div class="align-self-center flex-shrink-0">
                                <div id="permissions-chart" class="apex-charts"></div>
                                <span class="text-success fw-bold fs-13">
                                    <i class='uil uil-check-circle'></i> Activo
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if ($user->hasRole('admin'))
                <div class="col-md-6 col-xl-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex">
                                <div class="flex-grow-1">
                                    <span class="text-muted text-uppercase fs-12 fw-bold">Usuarios Registrados</span>
                                    <h3 class="mb-0">{{ $data['totalUsuarios'] }}</h3>
                                </div>
                                <div class="align-self-center flex-shrink-0">
                                    <div id="users-chart" class="apex-charts"></div>
                                    <span class="text-success fw-bold fs-13">
                                        <i class='uil uil-arrow-up'></i> Activos
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-xl-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex">
                                <div class="flex-grow-1">
                                    <span class="text-muted text-uppercase fs-12 fw-bold">Roles Configurados</span>
                                    <h3 class="mb-0">{{ $data['totalRoles'] }}</h3>
                                </div>
                                <div class="align-self-center flex-shrink-0">
                                    <div id="roles-chart" class="apex-charts"></div>
                                    <span class="text-success fw-bold fs-13">
                                        <i class='uil uil-shield'></i> Configurados
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-xl-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex">
                                <div class="flex-grow-1">
                                    <span class="text-muted text-uppercase fs-12 fw-bold">Permisos Disponibles</span>
                                    <h3 class="mb-0">{{ $data['totalPermisos'] }}</h3>
                                </div>
                                <div class="align-self-center flex-shrink-0">
                                    <div id="permisos-chart" class="apex-charts"></div>
                                    <span class="text-info fw-bold fs-13">
                                        <i class='uil uil-key'></i> Disponibles
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @elseif($user->hasRole('profesor'))
                <div class="col-md-6 col-xl-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex">
                                <div class="flex-grow-1">
                                    <span class="text-muted text-uppercase fs-12 fw-bold">Mis Cursos</span>
                                    <h3 class="mb-0">{{ $cursos_count ?? 0 }}</h3>
                                </div>
                                <div class="align-self-center flex-shrink-0">
                                    <div id="cursos-chart" class="apex-charts"></div>
                                    <span class="text-success fw-bold fs-13">
                                        <i class='uil uil-book-open'></i> Activos
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-xl-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex">
                                <div class="flex-grow-1">
                                    <span class="text-muted text-uppercase fs-12 fw-bold">Asistencias Hoy</span>
                                    <h3 class="mb-0">{{ $asistencias_hoy ?? 0 }}</h3>
                                </div>
                                <div class="align-self-center flex-shrink-0">
                                    <div id="asistencias-chart" class="apex-charts"></div>
                                    <span class="text-info fw-bold fs-13">
                                        <i class='uil uil-user-check'></i> Registradas
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @elseif($user->hasRole('estudiante'))
                <div class="col-md-6 col-xl-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex">
                                <div class="flex-grow-1">
                                    <span class="text-muted text-uppercase fs-12 fw-bold">Mis Asistencias</span>
                                    <h3 class="mb-0">{{ $asistencias_count ?? 0 }}</h3>
                                </div>
                                <div class="align-self-center flex-shrink-0">
                                    <div id="asistencias-chart" class="apex-charts"></div>
                                    <span class="text-success fw-bold fs-13">
                                        <i class='uil uil-check-circle'></i> Registradas
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 col-xl-3">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex">
                                <div class="flex-grow-1">
                                    <span class="text-muted text-uppercase fs-12 fw-bold">Asistencia Mensual</span>
                                    <h3 class="mb-0">{{ $porcentaje_asistencia ?? '0%' }}</h3>
                                </div>
                                <div class="align-self-center flex-shrink-0">
                                    <div id="porcentaje-chart" class="apex-charts"></div>
                                    <span class="text-info fw-bold fs-13">
                                        <i class='uil uil-chart-line'></i> Este mes
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Módulos accesibles -->
        <div class="row">
            <div class="col-xl-3">
                <div class="card">
                    <div class="card-body p-0">
                        <div class="p-3">
                            <div class="dropdown float-end">
                                <a href="#" class="dropdown-toggle arrow-none text-muted" data-bs-toggle="dropdown"
                                    aria-expanded="false">
                                    <i class="uil uil-ellipsis-v"></i>
                                </a>
                                <div class="dropdown-menu dropdown-menu-end">
                                    <!-- item-->
                                    <a href="javascript:void(0);" class="dropdown-item">
                                        <i class="uil uil-refresh me-2"></i>Actualizar
                                    </a>
                                </div>
                            </div>

                            <h5 class="card-title header-title mb-0">Tus Módulos</h5>
                        </div>

                        @foreach ($modules as $module)
                            <!-- módulo -->
                            <div class="d-flex p-3 border-bottom">
                                <div class="flex-grow-1">
                                    <h4 class="mt-0 mb-1 fs-16">{{ $module['name'] }}</h4>
                                    <span class="text-muted">
                                        @php
                                            $permissionCount = collect($module['permissions'])->filter()->count();
                                        @endphp
                                        {{ $permissionCount }} permisos activos
                                    </span>
                                </div>
                                <a href="{{ route($module['route']) }}" class="btn btn-sm btn-primary align-self-center">
                                    Acceder
                                </a>
                            </div>
                        @endforeach

                        <a href="#" class="p-2 d-block text-end">Ver Todos <i class="uil-arrow-right"></i></a>
                    </div>
                </div>
            </div>

            <div class="col-xl-6">
                <div class="card">
                    <div class="card-body">
                        <div class="dropdown float-end">
                            <a href="#" class="dropdown-toggle arrow-none text-muted" data-bs-toggle="dropdown"
                                aria-expanded="false">
                                <i class="uil uil-ellipsis-v"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end">
                                <!-- item-->
                                <a href="javascript:void(0);" class="dropdown-item">
                                    Hoy
                                </a>
                                <!-- item-->
                                <a href="javascript:void(0);" class="dropdown-item">
                                    7 Días
                                </a>
                                <!-- item-->
                                <a href="javascript:void(0);" class="dropdown-item">
                                    15 Días
                                </a>
                                <div class="dropdown-divider"></div>
                                <!-- item-->
                                <a href="javascript:void(0);" class="dropdown-item">
                                    1 Mes
                                </a>
                            </div>
                        </div>
                        <h5 class="card-title mb-0 header-title">Estadísticas</h5>

                        @if ($user->hasRole('admin'))
                            <div id="usuarios-chart" class="apex-charts mt-3" dir="ltr"></div>
                        @elseif($user->hasRole('profesor'))
                            <div id="asistencias-linea-chart" class="apex-charts mt-3" dir="ltr"></div>
                        @elseif($user->hasRole('estudiante'))
                            <div id="asistencia-estudiante-chart" class="apex-charts mt-3" dir="ltr"></div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-xl-3">
                <div class="card">
                    <div class="card-body pb-0">
                        <div class="dropdown float-end">
                            <a href="#" class="dropdown-toggle arrow-none text-muted" data-bs-toggle="dropdown"
                                aria-expanded="false">
                                <i class="uil uil-ellipsis-v"></i>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end">
                                <!-- item-->
                                <a href="javascript:void(0);" class="dropdown-item">
                                    <i class="uil uil-refresh me-2"></i>Actualizar
                                </a>
                            </div>
                        </div>

                        @if ($user->hasRole('admin'))
                            <h5 class="card-title header-title">Distribución de Roles</h5>
                        @elseif($user->hasRole('profesor'))
                            <h5 class="card-title header-title">Distribución de Asistencias</h5>
                        @elseif($user->hasRole('estudiante'))
                            <h5 class="card-title header-title">Mi Asistencia</h5>
                        @endif

                        <div id="distribucion-chart" class="apex-charts mt-3" dir="ltr"></div>
                    </div>
                </div>
            </div>
        </div>
        <!-- row -->

        <!-- Acciones rápidas -->
        @if ($user->hasRole('profesor'))
            <div class="row">
                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title mt-0 mb-0 header-title">Acciones Rápidas</h5>
                            <div class="row mt-4">
                                <div class="col-md-6">
                                    <a href="{{ route('asistencia.registrar') }}"
                                        class="btn btn-primary btn-lg w-100 mb-2">
                                        <i data-feather="user-check" class="icon-md me-2"></i>
                                        Registrar Asistencia
                                    </a>
                                </div>
                                <div class="col-md-6">
                                    <a href="{{ route('asistencia.reportes') }}" class="btn btn-info btn-lg w-100 mb-2">
                                        <i data-feather="file-text" class="icon-md me-2"></i>
                                        Ver Reportes
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if ($user->hasRole('estudiante'))
            <div class="row">
                <div class="col-xl-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title mt-0 mb-0 header-title">Mi Asistencia</h5>
                            <div class="row mt-4">
                                <div class="col-md-12">
                                    <a href="{{ route('asistencia.index') }}" class="btn btn-primary btn-lg w-100 mb-2">
                                        <i data-feather="calendar" class="icon-md me-2"></i>
                                        Ver mi Registro de Asistencia
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if ($user->hasRole('admin'))
            <!-- AdminData -->
            <div class="row">
                <div class="col-xl-5">
                    <div class="card">
                        <div class="card-body">
                            <div class="dropdown float-end">
                                <a href="#" class="dropdown-toggle arrow-none text-muted" data-bs-toggle="dropdown"
                                    aria-expanded="false">
                                    <i class="uil uil-ellipsis-v"></i>
                                </a>
                                <div class="dropdown-menu dropdown-menu-end">
                                    <!-- item-->
                                    <a href="javascript:void(0);" class="dropdown-item">
                                        <i class="uil uil-refresh me-2"></i>Actualizar
                                    </a>
                                </div>
                            </div>
                            <h5 class="card-title mt-0 mb-0 header-title">Distribución de Usuarios</h5>
                            <div id="users-by-role-chart" class="apex-charts mb-0 mt-4" dir="ltr"></div>
                        </div> <!-- end card-body-->
                    </div> <!-- end card-->
                </div> <!-- end col-->
                <div class="col-xl-7">
                    <div class="card">
                        <div class="card-body">
                            <a href="#" class="btn btn-primary btn-sm float-end">
                                <i class='uil uil-export me-1'></i> Exportar
                            </a>
                            <h5 class="card-title mt-0 mb-0 header-title">Usuarios Recientes</h5>

                            <div class="table-responsive mt-4">
                                <table class="table table-hover table-nowrap mb-0">
                                    <thead>
                                        <tr>
                                            <th scope="col">#</th>
                                            <th scope="col">Nombre</th>
                                            <th scope="col">Email</th>
                                            <th scope="col">Rol</th>
                                            <th scope="col">Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($recentUsers ?? [] as $user)
                                            <tr>
                                                <td>{{ $user->id }}</td>
                                                <td>{{ $user->nombre }}</td>
                                                <td>{{ $user->email }}</td>
                                                <td>{{ $user->roles->first()->name ?? 'Sin rol' }}</td>
                                                <td><span
                                                        class="badge badge-soft-{{ $user->activo ? 'success' : 'danger' }} py-1">{{ $user->activo ? 'Activo' : 'Inactivo' }}</span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div> <!-- end table-responsive-->
                        </div> <!-- end card-body-->
                    </div> <!-- end card-->
                </div> <!-- end col-->
            </div>
            <!-- end row -->
        @endif

        @if ($user->hasRole('profesor'))
            <!-- ProfesorData -->
            <div class="row">
                <div class="col-xl-5">
                    <div class="card">
                        <div class="card-body">
                            <div class="dropdown float-end">
                                <a href="#" class="dropdown-toggle arrow-none text-muted" data-bs-toggle="dropdown"
                                    aria-expanded="false">
                                    <i class="uil uil-ellipsis-v"></i>
                                </a>
                                <div class="dropdown-menu dropdown-menu-end">
                                    <!-- item-->
                                    <a href="javascript:void(0);" class="dropdown-item">
                                        <i class="uil uil-refresh me-2"></i>Actualizar
                                    </a>
                                </div>
                            </div>
                            <h5 class="card-title mt-0 mb-0 header-title">Asistencia por Curso</h5>
                            <div id="asistencia-por-curso-chart" class="apex-charts mb-0 mt-4" dir="ltr"></div>
                        </div> <!-- end card-body-->
                    </div> <!-- end card-->
                </div> <!-- end col-->
                <div class="col-xl-7">
                    <div class="card">
                        <div class="card-body">
                            <a href="#" class="btn btn-primary btn-sm float-end">
                                <i class='uil uil-export me-1'></i> Exportar
                            </a>
                            <h5 class="card-title mt-0 mb-0 header-title">Asistencias Recientes</h5>

                            <div class="table-responsive mt-4">
                                <table class="table table-hover table-nowrap mb-0">
                                    <thead>
                                        <tr>
                                            <th scope="col">#</th>
                                            <th scope="col">Estudiante</th>
                                            <th scope="col">Curso</th>
                                            <th scope="col">Fecha</th>
                                            <th scope="col">Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($recentAttendances ?? [] as $attendance)
                                            <tr>
                                                <td>{{ $attendance->id }}</td>
                                                <td>{{ $attendance->estudiante->nombre }}</td>
                                                <td>{{ $attendance->curso->nombre }}</td>
                                                <td>{{ $attendance->fecha_asistencia }}</td>
                                                <td><span
                                                        class="badge badge-soft-{{ $attendance->presente ? 'success' : 'danger' }} py-1">{{ $attendance->presente ? 'Presente' : 'Ausente' }}</span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div> <!-- end table-responsive-->
                        </div> <!-- end card-body-->
                    </div> <!-- end card-->
                </div> <!-- end col-->
            </div>
            <!-- end row -->
        @endif

        @if ($user->hasRole('estudiante'))
            <!-- EstudianteData -->
            <div class="row">
                <div class="col-xl-5">
                    <div class="card">
                        <div class="card-body">
                            <div class="dropdown float-end">
                                <a href="#" class="dropdown-toggle arrow-none text-muted" data-bs-toggle="dropdown"
                                    aria-expanded="false">
                                    <i class="uil uil-ellipsis-v"></i>
                                </a>
                                <div class="dropdown-menu dropdown-menu-end">
                                    <!-- item-->
                                    <a href="javascript:void(0);" class="dropdown-item">
                                        <i class="uil uil-refresh me-2"></i>Actualizar
                                    </a>
                                </div>
                            </div>
                            <h5 class="card-title mt-0 mb-0 header-title">Mi Asistencia por Curso</h5>
                            <div id="mi-asistencia-por-curso-chart" class="apex-charts mb-0 mt-4" dir="ltr"></div>
                        </div> <!-- end card-body-->
                    </div> <!-- end card-->
                </div> <!-- end col-->
                <div class="col-xl-7">
                    <div class="card">
                        <div class="card-body">
                            <a href="#" class="btn btn-primary btn-sm float-end">
                                <i class='uil uil-export me-1'></i> Exportar
                            </a>
                            <h5 class="card-title mt-0 mb-0 header-title">Mis Asistencias Recientes</h5>

                            <div class="table-responsive mt-4">
                                <table class="table table-hover table-nowrap mb-0">
                                    <thead>
                                        <tr>
                                            <th scope="col">#</th>
                                            <th scope="col">Curso</th>
                                            <th scope="col">Profesor</th>
                                            <th scope="col">Fecha</th>
                                            <th scope="col">Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($myAttendances ?? [] as $attendance)
                                            <tr>
                                                <td>{{ $attendance->id }}</td>
                                                <td>{{ $attendance->curso->nombre }}</td>
                                                <td>{{ $attendance->curso->profesor->nombre }}</td>
                                                <td>{{ $attendance->fecha_asistencia }}</td>
                                                <td><span
                                                        class="badge badge-soft-{{ $attendance->presente ? 'success' : 'danger' }} py-1">{{ $attendance->presente ? 'Presente' : 'Ausente' }}</span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div> <!-- end table-responsive-->
                        </div> <!-- end card-body-->
                    </div> <!-- end card-->
                </div> <!-- end col-->
            </div>
            <!-- end row -->
        @endif

    </div> <!-- container -->
@endsection

@push('js')
    <script>
        // Inicialización de gráficos basados en el rol
        $(document).ready(function() {
            // Gráficos comunes para todos los roles
            if ($('#permissions-chart').length) {
                var options = {
                    chart: {
                        type: 'radialBar',
                        width: 60,
                        height: 60,
                        sparkline: {
                            enabled: true
                        }
                    },
                    colors: ['#3bafda'],
                    plotOptions: {
                        radialBar: {
                            hollow: {
                                margin: 0,
                                size: '50%'
                            },
                            track: {
                                margin: 0
                            },
                            dataLabels: {
                                show: false
                            }
                        }
                    },
                    series: [85],
                }
                new ApexCharts(document.querySelector("#permissions-chart"), options).render();
            }

            @if ($user->hasRole('admin'))
                // Gráficos específicos para admin
                // ... código específico de gráficos para admin
            @elseif ($user->hasRole('profesor'))
                // Gráficos específicos para profesor
                // ... código específico de gráficos para profesor
            @elseif ($user->hasRole('estudiante'))
                // Gráficos específicos para estudiante
                // ... código específico de gráficos para estudiante
            @endif
        });
    </script>
@endpush

@push('css')
    <style>
        .card {
            margin-bottom: 20px;
        }

        .icon-lg {
            width: 24px;
            height: 24px;
        }

        .icon-md {
            width: 18px;
            height: 18px;
        }
    </style>
@endpush
