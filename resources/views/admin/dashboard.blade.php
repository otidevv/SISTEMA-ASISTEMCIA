@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <!-- start page title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">Dashboard</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item active">Dashboard</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <!-- end page title -->

    @if (isset($esEstudiante) && $esEstudiante)
        {{-- Dashboard para Estudiantes --}}
        @if (isset($inscripcionActiva))
            {{-- Información del Ciclo Actual --}}
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title mb-3">Mi Inscripción Actual</h5>
                            <div class="row">
                                <div class="col-md-3">
                                    <p class="mb-1"><strong>Ciclo:</strong></p>
                                    <p class="text-primary">{{ $inscripcionActiva->ciclo->nombre }}</p>
                                </div>
                                <div class="col-md-3">
                                    <p class="mb-1"><strong>Carrera:</strong></p>
                                    <p class="text-primary">{{ $inscripcionActiva->carrera->nombre }}</p>
                                </div>
                                <div class="col-md-3">
                                    <p class="mb-1"><strong>Turno:</strong></p>
                                    <p class="text-primary">{{ $inscripcionActiva->turno->nombre }}</p>
                                </div>
                                <div class="col-md-3">
                                    <p class="mb-1"><strong>Aula:</strong></p>
                                    <p class="text-primary">{{ $inscripcionActiva->aula->codigo }} -
                                        {{ $inscripcionActiva->aula->nombre }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if (isset($infoAsistencia) && !empty($infoAsistencia))
                {{-- Resumen General de Asistencia --}}
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title mb-3">Resumen de Asistencia del Ciclo</h5>
                                @if (isset($infoAsistencia['total_ciclo']))
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="text-center">
                                                <h2
                                                    class="mb-2 @if ($infoAsistencia['total_ciclo']['estado'] == 'regular') text-success @elseif($infoAsistencia['total_ciclo']['estado'] == 'amonestado') text-warning @else text-danger @endif">
                                                    {{ $infoAsistencia['total_ciclo']['porcentaje_asistencia_actual'] ?? $infoAsistencia['total_ciclo']['porcentaje_asistencia'] }}%
                                                </h2>
                                                <p class="text-muted mb-0">Asistencia Total</p>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="text-center">
                                                <h3 class="mb-2">{{ $infoAsistencia['total_ciclo']['dias_asistidos'] }}
                                                </h3>
                                                <p class="text-muted mb-0">Días Asistidos</p>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="text-center">
                                                <h3 class="mb-2">{{ $infoAsistencia['total_ciclo']['dias_falta'] }}</h3>
                                                <p class="text-muted mb-0">Días de Falta</p>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="text-center">
                                                <h3 class="mb-2">
                                                    {{ $infoAsistencia['total_ciclo']['dias_habiles_transcurridos'] ?? $infoAsistencia['total_ciclo']['dias_habiles'] }}
                                                </h3>
                                                <p class="text-muted mb-0">Días Transcurridos</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mt-3">
                                        <div class="progress" style="height: 25px;">
                                            <div class="progress-bar @if ($infoAsistencia['total_ciclo']['estado'] == 'regular') bg-success @elseif($infoAsistencia['total_ciclo']['estado'] == 'amonestado') bg-warning @else bg-danger @endif"
                                                role="progressbar"
                                                style="width: {{ $infoAsistencia['total_ciclo']['porcentaje_asistencia_actual'] ?? $infoAsistencia['total_ciclo']['porcentaje_asistencia'] }}%;"
                                                aria-valuenow="{{ $infoAsistencia['total_ciclo']['porcentaje_asistencia_actual'] ?? $infoAsistencia['total_ciclo']['porcentaje_asistencia'] }}"
                                                aria-valuemin="0" aria-valuemax="100">
                                                {{ $infoAsistencia['total_ciclo']['porcentaje_asistencia_actual'] ?? $infoAsistencia['total_ciclo']['porcentaje_asistencia'] }}%
                                            </div>
                                        </div>
                                        @if (isset($infoAsistencia['total_ciclo']['es_proyeccion']) && $infoAsistencia['total_ciclo']['es_proyeccion'])
                                            <small class="text-muted mt-1 d-block">Datos hasta hoy. El ciclo termina el
                                                {{ \Carbon\Carbon::parse($inscripcionActiva->ciclo->fecha_fin)->format('d/m/Y') }}</small>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Información por Examen --}}
                <div class="row">
                    {{-- Primer Examen --}}
                    @if (isset($infoAsistencia['primer_examen']))
                        <div class="col-md-4">
                            <div
                                class="card border @if ($infoAsistencia['primer_examen']['estado'] == 'inhabilitado') border-danger @elseif($infoAsistencia['primer_examen']['estado'] == 'amonestado') border-warning @else border-success @endif">
                                <div
                                    class="card-header @if ($infoAsistencia['primer_examen']['estado'] == 'inhabilitado') bg-danger @elseif($infoAsistencia['primer_examen']['estado'] == 'amonestado') bg-warning @else bg-success @endif text-white">
                                    <h5 class="card-title mb-0">Primer Examen</h5>
                                    <small>{{ \Carbon\Carbon::parse($inscripcionActiva->ciclo->fecha_primer_examen)->format('d/m/Y') }}</small>
                                    @if ($infoAsistencia['primer_examen']['es_proyeccion'])
                                        <span class="badge bg-light text-dark ms-2">Proyección</span>
                                    @endif
                                </div>
                                <div class="card-body">
                                    <div class="text-center mb-3">
                                        <h3
                                            class="@if ($infoAsistencia['primer_examen']['estado'] == 'inhabilitado') text-danger @elseif($infoAsistencia['primer_examen']['estado'] == 'amonestado') text-warning @else text-success @endif">
                                            {{ $infoAsistencia['primer_examen']['porcentaje_asistencia_actual'] ?? $infoAsistencia['primer_examen']['porcentaje_asistencia'] }}%
                                        </h3>
                                        <p class="text-muted mb-0">Asistencia Actual</p>
                                        @if ($infoAsistencia['primer_examen']['es_proyeccion'])
                                            <small
                                                class="text-muted">({{ $infoAsistencia['primer_examen']['dias_habiles_transcurridos'] }}
                                                de {{ $infoAsistencia['primer_examen']['dias_habiles'] }} días)</small>
                                        @endif
                                    </div>

                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between">
                                            <span>Días asistidos:</span>
                                            <strong>{{ $infoAsistencia['primer_examen']['dias_asistidos'] }}</strong>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <span>Faltas actuales:</span>
                                            <strong>{{ $infoAsistencia['primer_examen']['dias_falta'] }}</strong>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <span>Límite amonestación:</span>
                                            <strong>{{ $infoAsistencia['primer_examen']['limite_amonestacion'] }}
                                                faltas</strong>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <span>Límite inhabilitación:</span>
                                            <strong>{{ $infoAsistencia['primer_examen']['limite_inhabilitacion'] }}
                                                faltas</strong>
                                        </div>
                                    </div>

                                    @if ($infoAsistencia['primer_examen']['estado'] == 'inhabilitado')
                                        <div class="alert alert-danger mb-0">
                                            <i class="dripicons-wrong me-2"></i>
                                            {{ $infoAsistencia['primer_examen']['mensaje'] }}
                                        </div>
                                    @elseif($infoAsistencia['primer_examen']['estado'] == 'amonestado')
                                        <div class="alert alert-warning mb-0">
                                            <i class="dripicons-warning me-2"></i>
                                            {{ $infoAsistencia['primer_examen']['mensaje'] }}
                                        </div>
                                    @else
                                        <div class="alert alert-success mb-0">
                                            <i class="dripicons-checkmark me-2"></i>
                                            {{ $infoAsistencia['primer_examen']['mensaje'] }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Segundo Examen --}}
                    @if (isset($infoAsistencia['segundo_examen']))
                        <div class="col-md-4">
                            <div
                                class="card border @if ($infoAsistencia['segundo_examen']['estado'] == 'pendiente') border-secondary @elseif($infoAsistencia['segundo_examen']['estado'] == 'inhabilitado') border-danger @elseif($infoAsistencia['segundo_examen']['estado'] == 'amonestado') border-warning @else border-success @endif">
                                <div
                                    class="card-header @if ($infoAsistencia['segundo_examen']['estado'] == 'pendiente') bg-secondary @elseif($infoAsistencia['segundo_examen']['estado'] == 'inhabilitado') bg-danger @elseif($infoAsistencia['segundo_examen']['estado'] == 'amonestado') bg-warning @else bg-success @endif text-white">
                                    <h5 class="card-title mb-0">Segundo Examen</h5>
                                    <small>{{ \Carbon\Carbon::parse($inscripcionActiva->ciclo->fecha_segundo_examen)->format('d/m/Y') }}</small>
                                    @if ($infoAsistencia['segundo_examen']['estado'] != 'pendiente' && $infoAsistencia['segundo_examen']['es_proyeccion'])
                                        <span class="badge bg-light text-dark ms-2">Proyección</span>
                                    @endif
                                </div>
                                <div class="card-body">
                                    @if ($infoAsistencia['segundo_examen']['estado'] == 'pendiente')
                                        <div class="text-center">
                                            <i class="dripicons-clock h1 text-muted"></i>
                                            <p class="text-muted">{{ $infoAsistencia['segundo_examen']['mensaje'] }}</p>
                                            <small class="text-muted">Comenzará después del primer examen</small>
                                        </div>
                                    @else
                                        <div class="text-center mb-3">
                                            <h3
                                                class="@if ($infoAsistencia['segundo_examen']['estado'] == 'inhabilitado') text-danger @elseif($infoAsistencia['segundo_examen']['estado'] == 'amonestado') text-warning @else text-success @endif">
                                                {{ $infoAsistencia['segundo_examen']['porcentaje_asistencia_actual'] ?? $infoAsistencia['segundo_examen']['porcentaje_asistencia'] }}%
                                            </h3>
                                            <p class="text-muted mb-0">Asistencia Actual</p>
                                            @if ($infoAsistencia['segundo_examen']['es_proyeccion'])
                                                <small
                                                    class="text-muted">({{ $infoAsistencia['segundo_examen']['dias_habiles_transcurridos'] }}
                                                    de {{ $infoAsistencia['segundo_examen']['dias_habiles'] }}
                                                    días)</small>
                                            @endif
                                        </div>

                                        <div class="mb-3">
                                            <div class="d-flex justify-content-between">
                                                <span>Días asistidos:</span>
                                                <strong>{{ $infoAsistencia['segundo_examen']['dias_asistidos'] }}</strong>
                                            </div>
                                            <div class="d-flex justify-content-between">
                                                <span>Faltas actuales:</span>
                                                <strong>{{ $infoAsistencia['segundo_examen']['dias_falta'] }}</strong>
                                            </div>
                                            <div class="d-flex justify-content-between">
                                                <span>Límite amonestación:</span>
                                                <strong>{{ $infoAsistencia['segundo_examen']['limite_amonestacion'] }}
                                                    faltas</strong>
                                            </div>
                                            <div class="d-flex justify-content-between">
                                                <span>Límite inhabilitación:</span>
                                                <strong>{{ $infoAsistencia['segundo_examen']['limite_inhabilitacion'] }}
                                                    faltas</strong>
                                            </div>
                                        </div>

                                        @if ($infoAsistencia['segundo_examen']['estado'] == 'inhabilitado')
                                            <div class="alert alert-danger mb-0">
                                                <i class="dripicons-wrong me-2"></i>
                                                {{ $infoAsistencia['segundo_examen']['mensaje'] }}
                                            </div>
                                        @elseif($infoAsistencia['segundo_examen']['estado'] == 'amonestado')
                                            <div class="alert alert-warning mb-0">
                                                <i class="dripicons-warning me-2"></i>
                                                {{ $infoAsistencia['segundo_examen']['mensaje'] }}
                                            </div>
                                        @else
                                            <div class="alert alert-success mb-0">
                                                <i class="dripicons-checkmark me-2"></i>
                                                {{ $infoAsistencia['segundo_examen']['mensaje'] }}
                                            </div>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Tercer Examen --}}
                    @if (isset($infoAsistencia['tercer_examen']))
                        <div class="col-md-4">
                            <div
                                class="card border @if ($infoAsistencia['tercer_examen']['estado'] == 'pendiente') border-secondary @elseif($infoAsistencia['tercer_examen']['estado'] == 'inhabilitado') border-danger @elseif($infoAsistencia['tercer_examen']['estado'] == 'amonestado') border-warning @else border-success @endif">
                                <div
                                    class="card-header @if ($infoAsistencia['tercer_examen']['estado'] == 'pendiente') bg-secondary @elseif($infoAsistencia['tercer_examen']['estado'] == 'inhabilitado') bg-danger @elseif($infoAsistencia['tercer_examen']['estado'] == 'amonestado') bg-warning @else bg-success @endif text-white">
                                    <h5 class="card-title mb-0">Tercer Examen</h5>
                                    <small>{{ \Carbon\Carbon::parse($inscripcionActiva->ciclo->fecha_tercer_examen)->format('d/m/Y') }}</small>
                                    @if ($infoAsistencia['tercer_examen']['estado'] != 'pendiente' && $infoAsistencia['tercer_examen']['es_proyeccion'])
                                        <span class="badge bg-light text-dark ms-2">Proyección</span>
                                    @endif
                                </div>
                                <div class="card-body">
                                    @if ($infoAsistencia['tercer_examen']['estado'] == 'pendiente')
                                        <div class="text-center">
                                            <i class="dripicons-clock h1 text-muted"></i>
                                            <p class="text-muted">{{ $infoAsistencia['tercer_examen']['mensaje'] }}</p>
                                            <small class="text-muted">Comenzará después del segundo examen</small>
                                        </div>
                                    @else
                                        <div class="text-center mb-3">
                                            <h3
                                                class="@if ($infoAsistencia['tercer_examen']['estado'] == 'inhabilitado') text-danger @elseif($infoAsistencia['tercer_examen']['estado'] == 'amonestado') text-warning @else text-success @endif">
                                                {{ $infoAsistencia['tercer_examen']['porcentaje_asistencia_actual'] ?? $infoAsistencia['tercer_examen']['porcentaje_asistencia'] }}%
                                            </h3>
                                            <p class="text-muted mb-0">Asistencia Actual</p>
                                            @if ($infoAsistencia['tercer_examen']['es_proyeccion'])
                                                <small
                                                    class="text-muted">({{ $infoAsistencia['tercer_examen']['dias_habiles_transcurridos'] }}
                                                    de {{ $infoAsistencia['tercer_examen']['dias_habiles'] }} días)</small>
                                            @endif
                                        </div>

                                        <div class="mb-3">
                                            <div class="d-flex justify-content-between">
                                                <span>Días asistidos:</span>
                                                <strong>{{ $infoAsistencia['tercer_examen']['dias_asistidos'] }}</strong>
                                            </div>
                                            <div class="d-flex justify-content-between">
                                                <span>Faltas actuales:</span>
                                                <strong>{{ $infoAsistencia['tercer_examen']['dias_falta'] }}</strong>
                                            </div>
                                            <div class="d-flex justify-content-between">
                                                <span>Límite amonestación:</span>
                                                <strong>{{ $infoAsistencia['tercer_examen']['limite_amonestacion'] }}
                                                    faltas</strong>
                                            </div>
                                            <div class="d-flex justify-content-between">
                                                <span>Límite inhabilitación:</span>
                                                <strong>{{ $infoAsistencia['tercer_examen']['limite_inhabilitacion'] }}
                                                    faltas</strong>
                                            </div>
                                        </div>

                                        @if ($infoAsistencia['tercer_examen']['estado'] == 'inhabilitado')
                                            <div class="alert alert-danger mb-0">
                                                <i class="dripicons-wrong me-2"></i>
                                                {{ $infoAsistencia['tercer_examen']['mensaje'] }}
                                            </div>
                                        @elseif($infoAsistencia['tercer_examen']['estado'] == 'amonestado')
                                            <div class="alert alert-warning mb-0">
                                                <i class="dripicons-warning me-2"></i>
                                                {{ $infoAsistencia['tercer_examen']['mensaje'] }}
                                            </div>
                                        @else
                                            <div class="alert alert-success mb-0">
                                                <i class="dripicons-checkmark me-2"></i>
                                                {{ $infoAsistencia['tercer_examen']['mensaje'] }}
                                            </div>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Nota informativa --}}
                <div class="row">
                    <div class="col-12">
                        <div class="alert alert-info">
                            <h5 class="alert-heading">Información Importante:</h5>
                            <ul class="mb-0">
                                <li>Las clases se imparten de <strong>Lunes a Viernes</strong>.</li>
                                <li>Tu asistencia se cuenta desde tu primer registro:
                                    <strong>{{ $primerRegistro ? \Carbon\Carbon::parse($primerRegistro->fecha_registro)->format('d/m/Y') : 'Sin registro' }}</strong>
                                </li>
                                <li>Si superas el <strong>20%</strong> de inasistencias recibirás una amonestación.</li>
                                <li>Si superas el <strong>30%</strong> de inasistencias no podrás rendir el examen
                                    correspondiente.</li>
                                <li>Para el segundo y tercer examen, la asistencia se cuenta desde el día hábil siguiente al
                                    examen anterior.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            @else
                {{-- Si no hay registros de asistencia --}}
                <div class="row">
                    <div class="col-12">
                        <div class="alert alert-warning">
                            <h4 class="alert-heading">Sin registros de asistencia</h4>
                            <p>Aún no tienes registros de asistencia en este ciclo. Tu asistencia comenzará a contarse desde
                                tu primer registro.</p>
                        </div>
                    </div>
                </div>
            @endif
        @else
            {{-- Si no tiene inscripción activa --}}
            <div class="row">
                <div class="col-12">
                    <div class="alert alert-info">
                        <h4 class="alert-heading">Sin inscripción activa</h4>
                        <p>No tienes una inscripción activa en el ciclo actual. Por favor, contacta con la administración.
                        </p>
                    </div>
                </div>
            </div>
        @endif
    @elseif(isset($esPadre) && $esPadre)
        {{-- Dashboard para Padres --}}
        <div class="row">
            <div class="col-xl-3 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="mb-0">{{ $hijosCount ?? 0 }}</h4>
                                <p class="text-muted mb-0">Hijos Registrados</p>
                            </div>
                            <div class="avatar-sm">
                                <span class="avatar-title bg-primary rounded">
                                    <i class="mdi mdi-account-child font-22"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Accesos Rápidos</h5>
                        <div class="row">
                            <div class="col-md-6">
                                <a href="{{ route('parentescos.index') }}" class="btn btn-primary btn-block mb-2">
                                    <i class="mdi mdi-account-multiple me-2"></i> Ver Mis Hijos
                                </a>
                            </div>
                            <div class="col-md-6">
                                <a href="{{ route('perfil.index') }}" class="btn btn-info btn-block mb-2">
                                    <i class="mdi mdi-account-edit me-2"></i> Mi Perfil
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @else
        {{-- Dashboard para Administradores y otros roles --}}
        <div class="row">
            <div class="col-xl-3 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="mb-0">{{ $totalUsuarios }}</h4>
                                <p class="text-muted mb-0">Total Usuarios</p>
                            </div>
                            <div class="avatar-sm">
                                <span class="avatar-title bg-primary rounded">
                                    <i class="mdi mdi-account-multiple font-22"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="mb-0">{{ $totalEstudiantes }}</h4>
                                <p class="text-muted mb-0">Estudiantes</p>
                            </div>
                            <div class="avatar-sm">
                                <span class="avatar-title bg-success rounded">
                                    <i class="mdi mdi-school font-22"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="mb-0">{{ $totalCarreras ?? 0 }}</h4>
                                <p class="text-muted mb-0">Carreras Activas</p>
                            </div>
                            <div class="avatar-sm">
                                <span class="avatar-title bg-info rounded">
                                    <i class="mdi mdi-book-education font-22"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h4 class="mb-0">{{ $totalAulas ?? 0 }}</h4>
                                <p class="text-muted mb-0">Aulas Disponibles</p>
                            </div>
                            <div class="avatar-sm">
                                <span class="avatar-title bg-warning rounded">
                                    <i class="mdi mdi-door font-22"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if (isset($cicloActivo))
            {{-- Información del Ciclo Activo --}}
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title mb-3">Ciclo Activo: {{ $cicloActivo->nombre }}</h5>
                            <div class="row">
                                <div class="col-md-4">
                                    <p><strong>Inscripciones Activas:</strong> {{ $totalInscripciones ?? 0 }}</p>
                                    <p><strong>Inicio:</strong> {{ $cicloActivo->fecha_inicio->format('d/m/Y') }}</p>
                                    <p><strong>Fin:</strong> {{ $cicloActivo->fecha_fin->format('d/m/Y') }}</p>
                                </div>
                                <div class="col-md-4">
                                    <p><strong>Progreso del Ciclo:</strong></p>
                                    <div class="progress mb-2" style="height: 20px;">
                                        <div class="progress-bar" role="progressbar"
                                            style="width: {{ $cicloActivo->calcularPorcentajeAvance() }}%;"
                                            aria-valuenow="{{ $cicloActivo->calcularPorcentajeAvance() }}"
                                            aria-valuemin="0" aria-valuemax="100">
                                            {{ $cicloActivo->calcularPorcentajeAvance() }}%
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    @php
                                        $proximoExamen = $cicloActivo->getProximoExamen();
                                    @endphp
                                    @if ($proximoExamen)
                                        <p><strong>Próximo Examen:</strong></p>
                                        <p>{{ $proximoExamen['nombre'] }} - {{ $proximoExamen['fecha']->format('d/m/Y') }}
                                        </p>
                                        <p class="text-muted">En {{ $proximoExamen['fecha']->diffInDays() }} días</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Estadísticas de Asistencia General --}}
            @if (isset($estadisticasAsistencia))
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title mb-3">Estadísticas de Asistencia General</h5>
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="text-center">
                                            <h4 class="text-success">{{ $estadisticasAsistencia['regulares'] }}</h4>
                                            <p class="text-muted">Estudiantes Regulares</p>
                                            <span
                                                class="badge bg-success">{{ $estadisticasAsistencia['porcentaje_regulares'] }}%</span>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="text-center">
                                            <h4 class="text-warning">{{ $estadisticasAsistencia['amonestados'] }}</h4>
                                            <p class="text-muted">Estudiantes Amonestados</p>
                                            <span
                                                class="badge bg-warning">{{ $estadisticasAsistencia['porcentaje_amonestados'] }}%</span>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="text-center">
                                            <h4 class="text-danger">{{ $estadisticasAsistencia['inhabilitados'] }}</h4>
                                            <p class="text-muted">Estudiantes Inhabilitados</p>
                                            <span
                                                class="badge bg-danger">{{ $estadisticasAsistencia['porcentaje_inhabilitados'] }}%</span>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="text-center">
                                            <h4 class="text-primary">{{ $estadisticasAsistencia['total_estudiantes'] }}
                                            </h4>
                                            <p class="text-muted">Total Estudiantes</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @endif

        {{-- Accesos Rápidos para Administradores --}}
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Accesos Rápidos</h5>
                        <div class="row">
                            @if (Auth::user()->hasPermission('users.view'))
                                <div class="col-md-3 mb-2">
                                    <a href="{{ route('usuarios.index') }}" class="btn btn-primary btn-block">
                                        <i class="mdi mdi-account-multiple me-2"></i> Usuarios
                                    </a>
                                </div>
                            @endif

                            @if (Auth::user()->hasPermission('inscripciones.view'))
                                <div class="col-md-3 mb-2">
                                    <a href="{{ route('inscripciones.index') }}" class="btn btn-success btn-block">
                                        <i class="mdi mdi-clipboard-list me-2"></i> Inscripciones
                                    </a>
                                </div>
                            @endif

                            @if (Auth::user()->hasPermission('ciclos.view'))
                                <div class="col-md-3 mb-2">
                                    <a href="{{ route('ciclos.index') }}" class="btn btn-info btn-block">
                                        <i class="mdi mdi-calendar me-2"></i> Ciclos
                                    </a>
                                </div>
                            @endif

                            @if (Auth::user()->hasPermission('carreras.view'))
                                <div class="col-md-3 mb-2">
                                    <a href="{{ route('carreras.index') }}" class="btn btn-warning btn-block">
                                        <i class="mdi mdi-book-education me-2"></i> Carreras
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection
