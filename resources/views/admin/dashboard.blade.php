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
        {{-- Dashboard para Estudiantes - Sin cambios --}}
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

                                    {{-- AVISO DE RENDIR - PRIMER EXAMEN --}}
                                    @if (isset($infoAsistencia['primer_examen']) && $infoAsistencia['primer_examen']['estado'] != 'pendiente')
                                        @if ($infoAsistencia['primer_examen']['puede_rendir'])
                                            @if ($infoAsistencia['primer_examen']['estado'] == 'regular')
                                                <div class="alert alert-success mt-2 fw-bold text-center">
                                                    ✅ Rendiste este examen sin restricciones.
                                                </div>
                                            @elseif ($infoAsistencia['primer_examen']['estado'] == 'amonestado')
                                                <div class="alert alert-warning mt-2 fw-bold text-center">
                                                    ⚠️ Rendiste este examen estando amonestado por inasistencias.<br>
                                                    <span class="fw-normal d-block mt-2">
                                                        Si tuviste motivos válidos (como salud), aún puedes justificar tus
                                                        faltas ante coordinación académica.
                                                    </span>
                                                </div>
                                            @endif
                                        @else
                                            <div class="alert alert-danger mt-2 fw-bold text-center">
                                                ❌ No pudiste rendir este examen.<br>
                                                <span class="fw-normal d-block mt-2">
                                                    Si tus faltas fueron por salud u otra causa válida, puedes justificar tu
                                                    inasistencia ante coordinación académica.
                                                </span>
                                            </div>
                                        @endif
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

                                        {{-- AVISO DE RENDIR - SEGUNDO EXAMEN --}}
                                        @if (isset($infoAsistencia['segundo_examen']) && $infoAsistencia['segundo_examen']['estado'] != 'pendiente')
                                            @if ($infoAsistencia['segundo_examen']['puede_rendir'])
                                                @if ($infoAsistencia['segundo_examen']['estado'] == 'regular')
                                                    <div class="alert alert-success mt-2 fw-bold text-center">
                                                        ✅ Puedes rendir este examen sin restricciones (por el momento).
                                                    </div>
                                                @elseif ($infoAsistencia['segundo_examen']['estado'] == 'amonestado')
                                                    <div class="alert alert-warning mt-2 fw-bold text-center">
                                                        ⚠️ Puedes rendir este examen, pero estás amonestado por faltas.<br>
                                                        <span class="fw-normal d-block mt-2">
                                                            Si fueron justificadas, presenta tu documentación antes del
                                                            examen a coordinación académica.
                                                        </span>
                                                    </div>
                                                @endif
                                            @else
                                                <div class="alert alert-danger mt-2 fw-bold text-center">
                                                    ❌ Actualmente no puedes rendir este examen.<br>
                                                    <span class="fw-normal d-block mt-2">
                                                        Si tus faltas fueron por causas justificables, aún puedes presentar
                                                        documentación para revisión antes del examen.
                                                    </span>
                                                </div>
                                            @endif
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

                                        {{-- AVISO DE RENDIR - TERCER EXAMEN --}}
                                        @if (isset($infoAsistencia['tercer_examen']) && $infoAsistencia['tercer_examen']['estado'] != 'pendiente')
                                            @if ($infoAsistencia['tercer_examen']['puede_rendir'])
                                                @if ($infoAsistencia['tercer_examen']['estado'] == 'regular')
                                                    <div class="alert alert-success mt-2 fw-bold text-center">
                                                        ✅ Puedes rendir este examen sin restricciones (por el momento).
                                                    </div>
                                                @elseif ($infoAsistencia['tercer_examen']['estado'] == 'amonestado')
                                                    <div class="alert alert-warning mt-2 fw-bold text-center">
                                                        ⚠️ Puedes rendir este examen, pero estás amonestado por
                                                        inasistencias.<br>
                                                        <span class="fw-normal d-block mt-2">
                                                            Si tienes una justificación válida, presenta tu documentación
                                                            con anticipación a coordinación académica.
                                                        </span>
                                                    </div>
                                                @endif
                                            @else
                                                <div class="alert alert-danger mt-2 fw-bold text-center">
                                                    ❌ Actualmente no puedes rendir este examen.<br>
                                                    <span class="fw-normal d-block mt-2">
                                                        Puedes justificar tu inasistencia con documentación médica u otra
                                                        causa válida ante coordinación académica.
                                                    </span>
                                                </div>
                                            @endif
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
        {{-- Dashboard para Padres - Sin cambios --}}
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

        @if (isset($hijosAsistencia) && count($hijosAsistencia) > 0)
            {{-- Información de asistencia de cada hijo --}}
            @foreach ($hijosAsistencia as $hijoData)
                <div class="mb-4">
                    <h4 class="text-primary mb-3">
                        <i class="mdi mdi-account-school me-2"></i>
                        {{ $hijoData['hijo']->nombre }} {{ $hijoData['hijo']->apellido_paterno }}
                        {{ $hijoData['hijo']->apellido_materno }}
                        <small class="text-muted">({{ $hijoData['parentesco']->tipo_parentesco }})</small>
                    </h4>

                    {{-- Información del Ciclo Actual del Hijo --}}
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title mb-3">Inscripción Actual</h5>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <p class="mb-1"><strong>Ciclo:</strong></p>
                                            <p class="text-primary">{{ $hijoData['inscripcionActiva']->ciclo->nombre }}
                                            </p>
                                        </div>
                                        <div class="col-md-3">
                                            <p class="mb-1"><strong>Carrera:</strong></p>
                                            <p class="text-primary">{{ $hijoData['inscripcionActiva']->carrera->nombre }}
                                            </p>
                                        </div>
                                        <div class="col-md-3">
                                            <p class="mb-1"><strong>Turno:</strong></p>
                                            <p class="text-primary">{{ $hijoData['inscripcionActiva']->turno->nombre }}
                                            </p>
                                        </div>
                                        <div class="col-md-3">
                                            <p class="mb-1"><strong>Aula:</strong></p>
                                            <p class="text-primary">{{ $hijoData['inscripcionActiva']->aula->codigo }} -
                                                {{ $hijoData['inscripcionActiva']->aula->nombre }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if (isset($hijoData['infoAsistencia']) && !empty($hijoData['infoAsistencia']))
                        {{-- Resumen General de Asistencia del Hijo --}}
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title mb-3">Resumen de Asistencia del Ciclo</h5>
                                        @if (isset($hijoData['infoAsistencia']['total_ciclo']))
                                            <div class="row">
                                                <div class="col-md-3">
                                                    <div class="text-center">
                                                        <h2
                                                            class="mb-2 @if ($hijoData['infoAsistencia']['total_ciclo']['estado'] == 'regular') text-success @elseif($hijoData['infoAsistencia']['total_ciclo']['estado'] == 'amonestado') text-warning @else text-danger @endif">
                                                            {{ $hijoData['infoAsistencia']['total_ciclo']['porcentaje_asistencia_actual'] ?? $hijoData['infoAsistencia']['total_ciclo']['porcentaje_asistencia'] }}%
                                                        </h2>
                                                        <p class="text-muted mb-0">Asistencia Total</p>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="text-center">
                                                        <h3 class="mb-2">
                                                            {{ $hijoData['infoAsistencia']['total_ciclo']['dias_asistidos'] }}
                                                        </h3>
                                                        <p class="text-muted mb-0">Días Asistidos</p>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="text-center">
                                                        <h3 class="mb-2">
                                                            {{ $hijoData['infoAsistencia']['total_ciclo']['dias_falta'] }}
                                                        </h3>
                                                        <p class="text-muted mb-0">Días de Falta</p>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="text-center">
                                                        <h3 class="mb-2">
                                                            {{ $hijoData['infoAsistencia']['total_ciclo']['dias_habiles_transcurridos'] ?? $hijoData['infoAsistencia']['total_ciclo']['dias_habiles'] }}
                                                        </h3>
                                                        <p class="text-muted mb-0">Días Transcurridos</p>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="mt-3">
                                                <div class="progress" style="height: 25px;">
                                                    <div class="progress-bar @if ($hijoData['infoAsistencia']['total_ciclo']['estado'] == 'regular') bg-success @elseif($hijoData['infoAsistencia']['total_ciclo']['estado'] == 'amonestado') bg-warning @else bg-danger @endif"
                                                        role="progressbar"
                                                        style="width: {{ $hijoData['infoAsistencia']['total_ciclo']['porcentaje_asistencia_actual'] ?? $hijoData['infoAsistencia']['total_ciclo']['porcentaje_asistencia'] }}%;"
                                                        aria-valuenow="{{ $hijoData['infoAsistencia']['total_ciclo']['porcentaje_asistencia_actual'] ?? $hijoData['infoAsistencia']['total_ciclo']['porcentaje_asistencia'] }}"
                                                        aria-valuemin="0" aria-valuemax="100">
                                                        {{ $hijoData['infoAsistencia']['total_ciclo']['porcentaje_asistencia_actual'] ?? $hijoData['infoAsistencia']['total_ciclo']['porcentaje_asistencia'] }}%
                                                    </div>
                                                </div>
                                                @if (isset($hijoData['infoAsistencia']['total_ciclo']['es_proyeccion']) &&
                                                        $hijoData['infoAsistencia']['total_ciclo']['es_proyeccion']
                                                )
                                                    <small class="text-muted mt-1 d-block">Datos hasta hoy. El ciclo
                                                        termina el
                                                        {{ \Carbon\Carbon::parse($hijoData['inscripcionActiva']->ciclo->fecha_fin)->format('d/m/Y') }}</small>
                                                @endif
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Información por Examen del Hijo --}}
                        <div class="row">
                            {{-- Primer Examen --}}
                            @if (isset($hijoData['infoAsistencia']['primer_examen']))
                                <div class="col-md-4">
                                    <div
                                        class="card border @if ($hijoData['infoAsistencia']['primer_examen']['estado'] == 'pendiente') border-secondary @elseif($hijoData['infoAsistencia']['primer_examen']['estado'] == 'inhabilitado') border-danger @elseif($hijoData['infoAsistencia']['primer_examen']['estado'] == 'amonestado') border-warning @else border-success @endif">
                                        <div
                                            class="card-header @if ($hijoData['infoAsistencia']['primer_examen']['estado'] == 'pendiente') bg-secondary @elseif($hijoData['infoAsistencia']['primer_examen']['estado'] == 'inhabilitado') bg-danger @elseif($hijoData['infoAsistencia']['primer_examen']['estado'] == 'amonestado') bg-warning @else bg-success @endif text-white">
                                            <h5 class="card-title mb-0">Primer Examen</h5>
                                            <small>{{ \Carbon\Carbon::parse($hijoData['inscripcionActiva']->ciclo->fecha_primer_examen)->format('d/m/Y') }}</small>
                                            @if ($hijoData['infoAsistencia']['primer_examen']['es_proyeccion'])
                                                <span class="badge bg-light text-dark ms-2">Proyección</span>
                                            @endif
                                        </div>
                                        <div class="card-body">
                                            <div class="text-center mb-3">
                                                <h3
                                                    class="@if ($hijoData['infoAsistencia']['primer_examen']['estado'] == 'inhabilitado') text-danger @elseif($hijoData['infoAsistencia']['primer_examen']['estado'] == 'amonestado') text-warning @else text-success @endif">
                                                    {{ $hijoData['infoAsistencia']['primer_examen']['porcentaje_asistencia_actual'] ?? $hijoData['infoAsistencia']['primer_examen']['porcentaje_asistencia'] }}%
                                                </h3>
                                                <p class="text-muted mb-0">Asistencia Actual</p>
                                                @if ($hijoData['infoAsistencia']['primer_examen']['es_proyeccion'])
                                                    <small
                                                        class="text-muted">({{ $hijoData['infoAsistencia']['primer_examen']['dias_habiles_transcurridos'] }}
                                                        de
                                                        {{ $hijoData['infoAsistencia']['primer_examen']['dias_habiles'] }}
                                                        días)</small>
                                                @endif
                                            </div>

                                            <div class="mb-3">
                                                <div class="d-flex justify-content-between">
                                                    <span>Días asistidos:</span>
                                                    <strong>{{ $hijoData['infoAsistencia']['primer_examen']['dias_asistidos'] }}</strong>
                                                </div>
                                                <div class="d-flex justify-content-between">
                                                    <span>Faltas actuales:</span>
                                                    <strong>{{ $hijoData['infoAsistencia']['primer_examen']['dias_falta'] }}</strong>
                                                </div>
                                                <div class="d-flex justify-content-between">
                                                    <span>Límite amonestación:</span>
                                                    <strong>{{ $hijoData['infoAsistencia']['primer_examen']['limite_amonestacion'] }}
                                                        faltas</strong>
                                                </div>
                                                <div class="d-flex justify-content-between">
                                                    <span>Límite inhabilitación:</span>
                                                    <strong>{{ $hijoData['infoAsistencia']['primer_examen']['limite_inhabilitacion'] }}
                                                        faltas</strong>
                                                </div>
                                            </div>

                                            @if ($hijoData['infoAsistencia']['primer_examen']['estado'] == 'inhabilitado')
                                                <div class="alert alert-danger mb-0">
                                                    <i class="dripicons-wrong me-2"></i>
                                                    {{ $hijoData['infoAsistencia']['primer_examen']['mensaje'] }}
                                                </div>
                                            @elseif($hijoData['infoAsistencia']['primer_examen']['estado'] == 'amonestado')
                                                <div class="alert alert-warning mb-0">
                                                    <i class="dripicons-warning me-2"></i>
                                                    {{ $hijoData['infoAsistencia']['primer_examen']['mensaje'] }}
                                                </div>
                                            @else
                                                <div class="alert alert-success mb-0">
                                                    <i class="dripicons-checkmark me-2"></i>
                                                    {{ $hijoData['infoAsistencia']['primer_examen']['mensaje'] }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endif

                            {{-- Segundo Examen --}}
                            @if (isset($hijoData['infoAsistencia']['segundo_examen']))
                                <div class="col-md-4">
                                    <div
                                        class="card border @if ($hijoData['infoAsistencia']['segundo_examen']['estado'] == 'pendiente') border-secondary @elseif($hijoData['infoAsistencia']['segundo_examen']['estado'] == 'inhabilitado') border-danger @elseif($hijoData['infoAsistencia']['segundo_examen']['estado'] == 'amonestado') border-warning @else border-success @endif">
                                        <div
                                            class="card-header @if ($hijoData['infoAsistencia']['segundo_examen']['estado'] == 'pendiente') bg-secondary @elseif($hijoData['infoAsistencia']['segundo_examen']['estado'] == 'inhabilitado') bg-danger @elseif($hijoData['infoAsistencia']['segundo_examen']['estado'] == 'amonestado') bg-warning @else bg-success @endif text-white">
                                            <h5 class="card-title mb-0">Segundo Examen</h5>
                                            <small>{{ \Carbon\Carbon::parse($hijoData['inscripcionActiva']->ciclo->fecha_segundo_examen)->format('d/m/Y') }}</small>
                                            @if (
                                                $hijoData['infoAsistencia']['segundo_examen']['estado'] != 'pendiente' &&
                                                    $hijoData['infoAsistencia']['segundo_examen']['es_proyeccion']
                                            )
                                                <span class="badge bg-light text-dark ms-2">Proyección</span>
                                            @endif
                                        </div>
                                        <div class="card-body">
                                            @if ($hijoData['infoAsistencia']['segundo_examen']['estado'] == 'pendiente')
                                                <div class="text-center">
                                                    <i class="dripicons-clock h1 text-muted"></i>
                                                    <p class="text-muted">
                                                        {{ $hijoData['infoAsistencia']['segundo_examen']['mensaje'] }}</p>
                                                    <small class="text-muted">Comenzará después del primer examen</small>
                                                </div>
                                            @else
                                                <div class="text-center mb-3">
                                                    <h3
                                                        class="@if ($hijoData['infoAsistencia']['segundo_examen']['estado'] == 'inhabilitado') text-danger @elseif($hijoData['infoAsistencia']['segundo_examen']['estado'] == 'amonestado') text-warning @else text-success @endif">
                                                        {{ $hijoData['infoAsistencia']['segundo_examen']['porcentaje_asistencia_actual'] ?? $hijoData['infoAsistencia']['segundo_examen']['porcentaje_asistencia'] }}%
                                                    </h3>
                                                    <p class="text-muted mb-0">Asistencia Actual</p>
                                                    @if ($hijoData['infoAsistencia']['segundo_examen']['es_proyeccion'])
                                                        <small
                                                            class="text-muted">({{ $hijoData['infoAsistencia']['segundo_examen']['dias_habiles_transcurridos'] }}
                                                            de
                                                            {{ $hijoData['infoAsistencia']['segundo_examen']['dias_habiles'] }}
                                                            días)</small>
                                                    @endif
                                                </div>

                                                <div class="mb-3">
                                                    <div class="d-flex justify-content-between">
                                                        <span>Días asistidos:</span>
                                                        <strong>{{ $hijoData['infoAsistencia']['segundo_examen']['dias_asistidos'] }}</strong>
                                                    </div>
                                                    <div class="d-flex justify-content-between">
                                                        <span>Faltas actuales:</span>
                                                        <strong>{{ $hijoData['infoAsistencia']['segundo_examen']['dias_falta'] }}</strong>
                                                    </div>
                                                    <div class="d-flex justify-content-between">
                                                        <span>Límite amonestación:</span>
                                                        <strong>{{ $hijoData['infoAsistencia']['segundo_examen']['limite_amonestacion'] }}
                                                            faltas</strong>
                                                    </div>
                                                    <div class="d-flex justify-content-between">
                                                        <span>Límite inhabilitación:</span>
                                                        <strong>{{ $hijoData['infoAsistencia']['segundo_examen']['limite_inhabilitacion'] }}
                                                            faltas</strong>
                                                    </div>
                                                </div>

                                                @if ($hijoData['infoAsistencia']['segundo_examen']['estado'] == 'inhabilitado')
                                                    <div class="alert alert-danger mb-0">
                                                        <i class="dripicons-wrong me-2"></i>
                                                        {{ $hijoData['infoAsistencia']['segundo_examen']['mensaje'] }}
                                                    </div>
                                                @elseif($hijoData['infoAsistencia']['segundo_examen']['estado'] == 'amonestado')
                                                    <div class="alert alert-warning mb-0">
                                                        <i class="dripicons-warning me-2"></i>
                                                        {{ $hijoData['infoAsistencia']['segundo_examen']['mensaje'] }}
                                                    </div>
                                                @else
                                                    <div class="alert alert-success mb-0">
                                                        <i class="dripicons-checkmark me-2"></i>
                                                        {{ $hijoData['infoAsistencia']['segundo_examen']['mensaje'] }}
                                                    </div>
                                                @endif
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endif

                            {{-- Tercer Examen --}}
                            @if (isset($hijoData['infoAsistencia']['tercer_examen']))
                                <div class="col-md-4">
                                    <div
                                        class="card border @if ($hijoData['infoAsistencia']['tercer_examen']['estado'] == 'pendiente') border-secondary @elseif($hijoData['infoAsistencia']['tercer_examen']['estado'] == 'inhabilitado') border-danger @elseif($hijoData['infoAsistencia']['tercer_examen']['estado'] == 'amonestado') border-warning @else border-success @endif">
                                        <div
                                            class="card-header @if ($hijoData['infoAsistencia']['tercer_examen']['estado'] == 'pendiente') bg-secondary @elseif($hijoData['infoAsistencia']['tercer_examen']['estado'] == 'inhabilitado') bg-danger @elseif($hijoData['infoAsistencia']['tercer_examen']['estado'] == 'amonestado') bg-warning @else bg-success @endif text-white">
                                            <h5 class="card-title mb-0">Tercer Examen</h5>
                                            <small>{{ \Carbon\Carbon::parse($hijoData['inscripcionActiva']->ciclo->fecha_tercer_examen)->format('d/m/Y') }}</small>
                                            @if (
                                                $hijoData['infoAsistencia']['tercer_examen']['estado'] != 'pendiente' &&
                                                    $hijoData['infoAsistencia']['tercer_examen']['es_proyeccion']
                                            )
                                                <span class="badge bg-light text-dark ms-2">Proyección</span>
                                            @endif
                                        </div>
                                        <div class="card-body">
                                            @if ($hijoData['infoAsistencia']['tercer_examen']['estado'] == 'pendiente')
                                                <div class="text-center">
                                                    <i class="dripicons-clock h1 text-muted"></i>
                                                    <p class="text-muted">
                                                        {{ $hijoData['infoAsistencia']['tercer_examen']['mensaje'] }}</p>
                                                    <small class="text-muted">Comenzará después del segundo examen</small>
                                                </div>
                                            @else
                                                <div class="text-center mb-3">
                                                    <h3
                                                        class="@if ($hijoData['infoAsistencia']['tercer_examen']['estado'] == 'inhabilitado') text-danger @elseif($hijoData['infoAsistencia']['tercer_examen']['estado'] == 'amonestado') text-warning @else text-success @endif">
                                                        {{ $hijoData['infoAsistencia']['tercer_examen']['porcentaje_asistencia_actual'] ?? $hijoData['infoAsistencia']['tercer_examen']['porcentaje_asistencia'] }}%
                                                    </h3>
                                                    <p class="text-muted mb-0">Asistencia Actual</p>
                                                    @if ($hijoData['infoAsistencia']['tercer_examen']['es_proyeccion'])
                                                        <small
                                                            class="text-muted">({{ $hijoData['infoAsistencia']['tercer_examen']['dias_habiles_transcurridos'] }}
                                                            de
                                                            {{ $hijoData['infoAsistencia']['tercer_examen']['dias_habiles'] }}
                                                            días)</small>
                                                    @endif
                                                </div>

                                                <div class="mb-3">
                                                    <div class="d-flex justify-content-between">
                                                        <span>Días asistidos:</span>
                                                        <strong>{{ $hijoData['infoAsistencia']['tercer_examen']['dias_asistidos'] }}</strong>
                                                    </div>
                                                    <div class="d-flex justify-content-between">
                                                        <span>Faltas actuales:</span>
                                                        <strong>{{ $hijoData['infoAsistencia']['tercer_examen']['dias_falta'] }}</strong>
                                                    </div>
                                                    <div class="d-flex justify-content-between">
                                                        <span>Límite amonestación:</span>
                                                        <strong>{{ $hijoData['infoAsistencia']['tercer_examen']['limite_amonestacion'] }}
                                                            faltas</strong>
                                                    </div>
                                                    <div class="d-flex justify-content-between">
                                                        <span>Límite inhabilitación:</span>
                                                        <strong>{{ $hijoData['infoAsistencia']['tercer_examen']['limite_inhabilitacion'] }}
                                                            faltas</strong>
                                                    </div>
                                                </div>

                                                @if ($hijoData['infoAsistencia']['tercer_examen']['estado'] == 'inhabilitado')
                                                    <div class="alert alert-danger mb-0">
                                                        <i class="dripicons-wrong me-2"></i>
                                                        {{ $hijoData['infoAsistencia']['tercer_examen']['mensaje'] }}
                                                    </div>
                                                @elseif($hijoData['infoAsistencia']['tercer_examen']['estado'] == 'amonestado')
                                                    <div class="alert alert-warning mb-0">
                                                        <i class="dripicons-warning me-2"></i>
                                                        {{ $hijoData['infoAsistencia']['tercer_examen']['mensaje'] }}
                                                    </div>
                                                @else
                                                    <div class="alert alert-success mb-0">
                                                        <i class="dripicons-checkmark me-2"></i>
                                                        {{ $hijoData['infoAsistencia']['tercer_examen']['mensaje'] }}
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
                                    <h5 class="alert-heading">Información Importante sobre
                                        {{ $hijoData['hijo']->nombre }}:</h5>
                                    <ul class="mb-0">
                                        <li>Las clases se imparten de <strong>Lunes a Viernes</strong>.</li>
                                        <li>La asistencia se cuenta desde el primer registro:
                                            <strong>{{ $hijoData['primerRegistro'] ? \Carbon\Carbon::parse($hijoData['primerRegistro']->fecha_registro)->format('d/m/Y') : 'Sin registro' }}</strong>
                                        </li>
                                        <li>Si supera el <strong>20%</strong> de inasistencias recibirá una amonestación.
                                        </li>
                                        <li>Si supera el <strong>30%</strong> de inasistencias no podrá rendir el examen
                                            correspondiente.</li>
                                        <li>Para el segundo y tercer examen, la asistencia se cuenta desde el día hábil
                                            siguiente al examen anterior.</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    @else
                        {{-- Si no hay registros de asistencia del hijo --}}
                        <div class="row">
                            <div class="col-12">
                                <div class="alert alert-warning">
                                    <h4 class="alert-heading">Sin registros de asistencia</h4>
                                    <p>{{ $hijoData['hijo']->nombre }} aún no tiene registros de asistencia en este ciclo.
                                        La asistencia comenzará a contarse desde su primer registro.</p>
                                </div>
                            </div>
                        </div>
                    @endif

                    <hr class="my-4">
                </div>
            @endforeach
        @else
            {{-- Si no tiene hijos con inscripción activa --}}
            <div class="row">
                <div class="col-12">
                    <div class="alert alert-info">
                        <h4 class="alert-heading">Sin inscripciones activas</h4>
                        <p>No tiene hijos con inscripciones activas en el ciclo actual.</p>
                    </div>
                </div>
            </div>
        @endif

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
    {{-- Dashboard para Administradores y otros roles - VERSIÓN PROFESIONAL --}}
    @if (Auth::user()->hasRole('admin') || Auth::user()->hasPermission('dashboard.admin'))
        
        <style>
        /* Paleta de colores profesional para centro preuniversitario */
        :root {
            --primary-color: #1e3a8a;      /* Azul académico profundo */
            --secondary-color: #374151;     /* Gris corporativo */
            --success-color: #065f46;       /* Verde institucional */
            --info-color: #0c4a6e;          /* Azul información */
            --warning-color: #92400e;       /* Ámbar profesional */
            --danger-color: #991b1b;        /* Rojo sobrio */
            --light-color: #f8fafc;         /* Blanco hueso */
            --dark-color: #111827;          /* Negro carbón */
            
            /* Colores de fondo sutiles */
            --primary-bg: #eff6ff;
            --secondary-bg: #f9fafb;
            --success-bg: #ecfdf5;
            --info-bg: #f0f9ff;
            --warning-bg: #fffbeb;
            --danger-bg: #fef2f2;
            
            /* Sombras profesionales */
            --shadow-sm: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        
        .card {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            box-shadow: var(--shadow-sm);
            transition: box-shadow 0.2s ease;
        }
        
        .card:hover {
            box-shadow: var(--shadow-md);
        }
        
        .card-header {
            background-color: var(--secondary-bg);
            border-bottom: 1px solid #e5e7eb;
            border-radius: 8px 8px 0 0 !important;
            font-weight: 600;
            color: var(--dark-color);
            padding: 1rem 1.25rem;
        }
        
        .card-header.bg-primary {
            background-color: var(--primary-color) !important;
            color: white;
            border-bottom: none;
        }
        
        .card-header.bg-secondary {
            background-color: var(--secondary-color) !important;
            color: white;
            border-bottom: none;
        }
        
        .card-header.bg-success {
            background-color: var(--success-color) !important;
            color: white;
            border-bottom: none;
        }
        
        .card-header.bg-info {
            background-color: var(--info-color) !important;
            color: white;
            border-bottom: none;
        }
        
        .card-header.bg-warning {
            background-color: var(--warning-color) !important;
            color: white;
            border-bottom: none;
        }
        
        .card-header.bg-danger {
            background-color: var(--danger-color) !important;
            color: white;
            border-bottom: none;
        }
        
        .btn {
            border-radius: 6px;
            font-weight: 500;
            transition: all 0.2s ease;
            border-width: 1px;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: #1e40af;
            border-color: #1e40af;
            transform: translateY(-1px);
        }
        
        .btn-success {
            background-color: var(--success-color);
            border-color: var(--success-color);
        }
        
        .btn-success:hover {
            background-color: #047857;
            border-color: #047857;
            transform: translateY(-1px);
        }
        
        .btn-info {
            background-color: var(--info-color);
            border-color: var(--info-color);
        }
        
        .btn-info:hover {
            background-color: #0369a1;
            border-color: #0369a1;
            transform: translateY(-1px);
        }
        
        .btn-warning {
            background-color: var(--warning-color);
            border-color: var(--warning-color);
        }
        
        .btn-warning:hover {
            background-color: #a16207;
            border-color: #a16207;
            transform: translateY(-1px);
        }
        
        .progress {
            height: 8px;
            border-radius: 4px;
            background-color: #e5e7eb;
        }
        
        .progress-bar {
            border-radius: 4px;
        }
        
        .badge {
            border-radius: 4px;
            font-weight: 500;
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }
        
        /* Estadísticas profesionales */
        .stat-card {
            background: white;
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: var(--shadow-sm);
            border: 1px solid #e5e7eb;
            transition: all 0.2s ease;
        }
        
        .stat-card:hover {
            box-shadow: var(--shadow-md);
            transform: translateY(-2px);
        }
        
        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            line-height: 1;
            margin-bottom: 0.5rem;
        }
        
        .stat-label {
            font-size: 0.875rem;
            color: #6b7280;
            font-weight: 500;
            margin-bottom: 0.25rem;
        }
        
        .stat-desc {
            font-size: 0.75rem;
            color: #9ca3af;
        }
        
        /* Tablas profesionales */
        .table-professional {
            border-collapse: separate;
            border-spacing: 0;
        }
        
        .table-professional thead th {
            background-color: var(--secondary-bg);
            border-bottom: 2px solid #e5e7eb;
            font-weight: 600;
            font-size: 0.875rem;
            color: var(--dark-color);
            padding: 0.75rem 1rem;
        }
        
        .table-professional tbody tr {
            border-bottom: 1px solid #f3f4f6;
        }
        
        .table-professional tbody tr:hover {
            background-color: var(--light-color);
        }
        
        .table-professional tbody td {
            padding: 0.75rem 1rem;
            vertical-align: middle;
        }
        
        /* Colores de texto profesionales */
        .text-primary-custom { color: var(--primary-color) !important; }
        .text-secondary-custom { color: var(--secondary-color) !important; }
        .text-success-custom { color: var(--success-color) !important; }
        .text-info-custom { color: var(--info-color) !important; }
        .text-warning-custom { color: var(--warning-color) !important; }
        .text-danger-custom { color: var(--danger-color) !important; }
        
        /* Fondos sutiles */
        .bg-primary-subtle { background-color: var(--primary-bg) !important; }
        .bg-secondary-subtle { background-color: var(--secondary-bg) !important; }
        .bg-success-subtle { background-color: var(--success-bg) !important; }
        .bg-info-subtle { background-color: var(--info-bg) !important; }
        .bg-warning-subtle { background-color: var(--warning-bg) !important; }
        .bg-danger-subtle { background-color: var(--danger-bg) !important; }
        
        /* Elementos específicos */
        .dashboard-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, #1e40af 100%);
            color: white;
            border-radius: 8px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .metric-card {
            border-left: 4px solid;
            background: white;
        }
        
        .metric-card.primary { border-left-color: var(--primary-color); }
        .metric-card.success { border-left-color: var(--success-color); }
        .metric-card.info { border-left-color: var(--info-color); }
        .metric-card.warning { border-left-color: var(--warning-color); }
        </style>

        {{-- HEADER PRINCIPAL --}}
        <div class="dashboard-header">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h4 class="mb-1 text-white">
                        <i class="bi bi-speedometer2 me-2 text-white"></i>
                        Panel de Control Administrativo
                    </h4>
                    <p class="mb-0 opacity-90">Sistema de Gestión Académica - Centro Preuniversitario</p>
                </div>
                <div class="text-end">
                    <div class="badge bg-light text-dark fs-6">
                        {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}
                    </div>
                </div>
            </div>
        </div>

        {{-- SECCIÓN 1: ESTADÍSTICAS PRINCIPALES --}}
        <div class="row g-4 mb-4">
            <div class="col-lg-3 col-md-6">
                <div class="stat-card">
                    <div class="stat-icon bg-primary-subtle">
                        <i class="bi bi-people text-primary-custom fs-3"></i>
                    </div>
                    <div class="stat-number text-primary-custom">{{ $totalUsuarios }}</div>
                    <div class="stat-label">Total de Usuarios</div>
                    <div class="stat-desc">Registrados en el sistema</div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <div class="stat-card">
                    <div class="stat-icon bg-success-subtle">
                        <i class="bi bi-mortarboard text-success-custom fs-3"></i>
                    </div>
                    <div class="stat-number text-success-custom">{{ $totalEstudiantes }}</div>
                    <div class="stat-label">Estudiantes Activos</div>
                    <div class="stat-desc">Matriculados actualmente</div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <div class="stat-card">
                    <div class="stat-icon bg-info-subtle">
                        <i class="bi bi-person-workspace text-info-custom fs-3"></i>
                    </div>
                    <div class="stat-number text-info-custom">{{ $totalProfesores }}</div>
                    <div class="stat-label">Docentes</div>
                    <div class="stat-desc">Personal académico</div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6">
                <div class="stat-card">
                    <div class="stat-icon bg-warning-subtle">
                        <i class="bi bi-person-hearts text-warning-custom fs-3"></i>
                    </div>
                    <div class="stat-number text-warning-custom">{{ $totalPadres }}</div>
                    <div class="stat-label">Padres de Familia</div>
                    <div class="stat-desc">Cuentas vinculadas</div>
                </div>
            </div>
        </div>

        {{-- SECCIÓN 2: INFRAESTRUCTURA --}}
        <div class="row g-4 mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-secondary">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-building me-2"></i>Infraestructura Académica
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-4">
                            <div class="col-lg-3 col-md-6">
                                <div class="text-center">
                                    <div class="stat-icon bg-primary-subtle mx-auto">
                                        <i class="bi bi-journal-bookmark text-primary-custom fs-4"></i>
                                    </div>
                                    <h4 class="text-primary-custom mb-2">{{ $totalCarreras ?? 0 }}</h4>
                                    <p class="stat-label mb-0">Carreras Profesionales</p>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-6">
                                <div class="text-center">
                                    <div class="stat-icon bg-warning-subtle mx-auto">
                                        <i class="bi bi-door-open text-warning-custom fs-4"></i>
                                    </div>
                                    <h4 class="text-warning-custom mb-2">{{ $totalAulas ?? 0 }}</h4>
                                    <p class="stat-label mb-0">Aulas Disponibles</p>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-6">
                                <div class="text-center">
                                    <div class="stat-icon bg-danger-subtle mx-auto">
                                        <i class="bi bi-calendar3 text-danger-custom fs-4"></i>
                                    </div>
                                    <h4 class="text-danger-custom mb-2">{{ $totalCiclos ?? 0 }}</h4>
                                    <p class="stat-label mb-0">Ciclos Académicos</p>
                                </div>
                            </div>
                            <div class="col-lg-3 col-md-6">
                                <div class="text-center">
                                    <div class="stat-icon bg-success-subtle mx-auto">
                                        <i class="bi bi-clock text-success-custom fs-4"></i>
                                    </div>
                                    <h4 class="text-success-custom mb-2">{{ $totalTurnos ?? 0 }}</h4>
                                    <p class="stat-label mb-0">Turnos Académicos</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- SECCIÓN 3: GESTIÓN ACADÉMICA Y ASISTENCIA --}}
        <div class="row g-4 mb-4">
            <div class="col-lg-8">
                <div class="card h-100">
                    <div class="card-header bg-success">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-clipboard-data me-2"></i>Gestión Académica
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="metric-card primary p-3 rounded">
                                    <div class="d-flex align-items-center">
                                        <div class="stat-icon bg-primary-subtle me-3" style="width: 40px; height: 40px; margin-bottom: 0;">
                                            <i class="bi bi-journal-text text-primary-custom fs-5"></i>
                                        </div>
                                        <div>
                                            <div class="h5 mb-0 text-primary-custom">{{ $totalCursos ?? 0 }}</div>
                                            <small class="text-muted">Cursos Activos</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="metric-card warning p-3 rounded">
                                    <div class="d-flex align-items-center">
                                        <div class="stat-icon bg-warning-subtle me-3" style="width: 40px; height: 40px; margin-bottom: 0;">
                                            <i class="bi bi-megaphone text-warning-custom fs-5"></i>
                                        </div>
                                        <div>
                                            <div class="h5 mb-0 text-warning-custom">{{ $totalAnuncios ?? 0 }}</div>
                                            <small class="text-muted">Anuncios Publicados</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="metric-card info p-3 rounded">
                                    <div class="d-flex align-items-center">
                                        <div class="stat-icon bg-info-subtle me-3" style="width: 40px; height: 40px; margin-bottom: 0;">
                                            <i class="bi bi-person-plus text-info-custom fs-5"></i>
                                        </div>
                                        <div>
                                            <div class="h5 mb-0 text-info-custom">{{ $totalInscripcionesGeneral ?? 0 }}</div>
                                            <small class="text-muted">Inscripciones</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="card h-100">
                    <div class="card-header bg-info">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-calendar-check me-2"></i>Asistencia del Día
                        </h5>
                        <small>{{ \Carbon\Carbon::now()->format('d/m/Y') }}</small>
                    </div>
                    <div class="card-body">
                        <div class="row g-3 text-center">
                            <div class="col-4">
                                <div class="stat-number text-secondary-custom" style="font-size: 1.5rem;">{{ $asistenciaHoy['total_registros'] ?? 0 }}</div>
                                <div class="stat-desc">Total</div>
                            </div>
                            <div class="col-4">
                                <div class="stat-number text-success-custom" style="font-size: 1.5rem;">{{ $asistenciaHoy['presentes'] ?? 0 }}</div>
                                <div class="stat-desc">Presentes</div>
                            </div>
                            <div class="col-4">
                                <div class="stat-number text-danger-custom" style="font-size: 1.5rem;">{{ $asistenciaHoy['ausentes'] ?? 0 }}</div>
                                <div class="stat-desc">Ausentes</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- SECCIÓN 4: GESTIÓN DOCENTE --}}
        <div class="row g-4 mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-warning">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-person-badge me-2"></i>Gestión Docente
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-4">
                            <div class="col-lg-4">
                                <div class="text-center p-3">
                                    <div class="stat-icon bg-warning-subtle mx-auto">
                                        <i class="bi bi-calendar-week text-warning-custom fs-3"></i>
                                    </div>
                                    <h4 class="text-warning-custom mt-3">{{ $totalHorariosDocentes ?? 0 }}</h4>
                                    <p class="stat-label">Horarios Programados</p>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="text-center p-3">
                                    <div class="stat-icon bg-danger-subtle mx-auto">
                                        <i class="bi bi-cash-stack text-danger-custom fs-3"></i>
                                    </div>
                                    <h4 class="text-danger-custom mt-3">{{ $totalPagosDocentes ?? 0 }}</h4>
                                    <p class="stat-label">Pagos Procesados</p>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="text-center p-3">
                                    <div class="stat-icon bg-success-subtle mx-auto">
                                        <i class="bi bi-check2-all text-success-custom fs-3"></i>
                                    </div>
                                    <h4 class="text-success-custom mt-3">{{ $totalAsistenciaDocente ?? 0 }}</h4>
                                    <p class="stat-label">Asistencias Registradas</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- SECCIÓN 5: INFORMACIÓN DEL CICLO ACTIVO --}}
        @if (isset($cicloActivo))
            <div class="row g-4 mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-primary">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-calendar-event me-2"></i>Ciclo Académico Actual: {{ $cicloActivo->nombre }}
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <h6 class="text-primary-custom mb-3">Inscripciones Activas</h6>
                                    <div class="stat-number text-success-custom mb-2">{{ $totalInscripciones ?? 0 }}</div>
                                    <p class="text-muted mb-3">estudiantes matriculados</p>
                                    <div class="small">
                                        <div><strong>Inicio:</strong> {{ $cicloActivo->fecha_inicio->format('d/m/Y') }}</div>
                                        <div><strong>Fin:</strong> {{ $cicloActivo->fecha_fin->format('d/m/Y') }}</div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <h6 class="text-primary-custom mb-3">Progreso del Ciclo</h6>
                                    <div class="progress mb-2" style="height: 12px;">
                                        <div class="progress-bar bg-primary" role="progressbar"
                                            style="width: {{ $cicloActivo->calcularPorcentajeAvance() }}%;">
                                        </div>
                                    </div>
                                    <div class="text-center">
                                        <span class="badge bg-primary-subtle text-primary-custom">
                                            {{ $cicloActivo->calcularPorcentajeAvance() }}% completado
                                        </span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    @php
                                        $proximoExamen = $cicloActivo->getProximoExamen();
                                    @endphp
                                    @if ($proximoExamen)
                                        <h6 class="text-primary-custom mb-3">Próximo Examen</h6>
                                        <div class="h5 mb-1">{{ $proximoExamen['nombre'] }}</div>
                                        <div class="text-info-custom h6 mb-2">{{ $proximoExamen['fecha']->format('d/m/Y') }}</div>
                                        <span class="badge bg-info-subtle text-info-custom">
                                            <i class="bi bi-clock me-1"></i>{{ $proximoExamen['fecha']->diffInDays() }} días restantes
                                        </span>
                                    @else
                                        <h6 class="text-success-custom mb-3">Estado del Ciclo</h6>
                                        <p class="text-muted">Todos los exámenes completados</p>
                                        <span class="badge bg-success-subtle text-success-custom">Ciclo en curso</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ESTADÍSTICAS DE ASISTENCIA --}}
            @if (isset($estadisticasAsistencia))
                <div class="row g-4 mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header bg-info">
                                <h5 class="card-title mb-0">
                                    <i class="bi bi-bar-chart me-2"></i>Estadísticas de Asistencia Estudiantil
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-4 text-center">
                                    <div class="col-lg-3 col-md-6">
                                        <div class="p-3 bg-success-subtle rounded">
                                            <div class="stat-icon bg-success-subtle mx-auto mb-2" style="background: transparent !important;">
                                                <i class="bi bi-check-circle text-success-custom fs-2"></i>
                                            </div>
                                            <div class="stat-number text-success-custom">{{ $estadisticasAsistencia['regulares'] }}</div>
                                            <div class="stat-label">Estudiantes Regulares</div>
                                            <span class="badge bg-success-subtle text-success-custom">{{ $estadisticasAsistencia['porcentaje_regulares'] }}%</span>
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-md-6">
                                        <div class="p-3 bg-warning-subtle rounded">
                                            <div class="stat-icon bg-warning-subtle mx-auto mb-2" style="background: transparent !important;">
                                                <i class="bi bi-exclamation-triangle text-warning-custom fs-2"></i>
                                            </div>
                                            <div class="stat-number text-warning-custom">{{ $estadisticasAsistencia['amonestados'] }}</div>
                                            <div class="stat-label">Estudiantes Amonestados</div>
                                            <span class="badge bg-warning-subtle text-warning-custom">{{ $estadisticasAsistencia['porcentaje_amonestados'] }}%</span>
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-md-6">
                                        <div class="p-3 bg-danger-subtle rounded">
                                            <div class="stat-icon bg-danger-subtle mx-auto mb-2" style="background: transparent !important;">
                                                <i class="bi bi-x-circle text-danger-custom fs-2"></i>
                                            </div>
                                            <div class="stat-number text-danger-custom">{{ $estadisticasAsistencia['inhabilitados'] }}</div>
                                            <div class="stat-label">Estudiantes Inhabilitados</div>
                                            <span class="badge bg-danger-subtle text-danger-custom">{{ $estadisticasAsistencia['porcentaje_inhabilitados'] }}%</span>
                                        </div>
                                    </div>
                                    <div class="col-lg-3 col-md-6">
                                        <div class="p-3 bg-primary-subtle rounded">
                                            <div class="stat-icon bg-primary-subtle mx-auto mb-2" style="background: transparent !important;">
                                                <i class="bi bi-people text-primary-custom fs-2"></i>
                                            </div>
                                            <div class="stat-number text-primary-custom">{{ $estadisticasAsistencia['total_estudiantes'] }}</div>
                                            <div class="stat-label">Total de Estudiantes</div>
                                            <span class="badge bg-primary-subtle text-primary-custom">100%</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @endif

        {{-- ÚLTIMOS REGISTROS DE ASISTENCIA --}}
        <div class="row g-4 mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-secondary d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-clock-history me-2"></i>Registros de Asistencia Recientes
                        </h5>
                        <span class="badge bg-light text-dark">Tiempo Real</span>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-professional">
                                <thead>
                                    <tr>
                                        <th>Hora</th>
                                        <th>Documento</th>
                                        <th>Estudiante</th>
                                        <th>Verificación</th>
                                    </tr>
                                </thead>
                                <tbody id="latest-attendance-table-body">
                                    @forelse ($ultimosRegistrosAsistencia as $registro)
                                        <tr>
                                            <td>
                                                <span class="badge bg-primary-subtle text-primary-custom">
                                                    {{ \Carbon\Carbon::parse($registro->fecha_registro)->format('H:i:s') }}
                                                </span>
                                            </td>
                                            <td class="fw-semibold">{{ $registro->nro_documento }}</td>
                                            <td>{{ $registro->usuario ? $registro->usuario->nombre . ' ' . $registro->usuario->apellido_paterno : 'N/A' }}</td>
                                            <td>
                                                <span class="badge bg-info-subtle text-info-custom">
                                                    {{ $registro->tipo_verificacion_texto }}
                                                </span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-5 text-muted">
                                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                                <p>No hay registros de asistencia recientes</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ACCESOS RÁPIDOS --}}
        <div class="row g-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-primary">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-grid me-2"></i>Accesos Rápidos de Administración
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            @if (Auth::user()->hasPermission('users.view'))
                                <div class="col-md-3">
                                    <a href="{{ route('usuarios.index') }}" class="btn btn-outline-primary w-100 h-100 d-flex align-items-center p-3">
                                        <div class="me-3">
                                            <i class="bi bi-people fs-2"></i>
                                        </div>
                                        <div class="text-start">
                                            <div class="fw-semibold">Usuarios</div>
                                            <small class="text-muted">Gestión de usuarios</small>
                                        </div>
                                    </a>
                                </div>
                            @endif

                            @if (Auth::user()->hasPermission('inscripciones.view'))
                                <div class="col-md-3">
                                    <a href="{{ route('inscripciones.index') }}" class="btn btn-outline-success w-100 h-100 d-flex align-items-center p-3">
                                        <div class="me-3">
                                            <i class="bi bi-clipboard-plus fs-2"></i>
                                        </div>
                                        <div class="text-start">
                                            <div class="fw-semibold">Inscripciones</div>
                                            <small class="text-muted">Control de matrículas</small>
                                        </div>
                                    </a>
                                </div>
                            @endif

                            @if (Auth::user()->hasPermission('ciclos.view'))
                                <div class="col-md-3">
                                    <a href="{{ route('ciclos.index') }}" class="btn btn-outline-info w-100 h-100 d-flex align-items-center p-3">
                                        <div class="me-3">
                                            <i class="bi bi-calendar3 fs-2"></i>
                                        </div>
                                        <div class="text-start">
                                            <div class="fw-semibold">Ciclos</div>
                                            <small class="text-muted">Períodos académicos</small>
                                        </div>
                                    </a>
                                </div>
                            @endif

                            @if (Auth::user()->hasPermission('carreras.view'))
                                <div class="col-md-3">
                                    <a href="{{ route('carreras.index') }}" class="btn btn-outline-warning w-100 h-100 d-flex align-items-center p-3">
                                        <div class="me-3">
                                            <i class="bi bi-book fs-2"></i>
                                        </div>
                                        <div class="text-start">
                                            <div class="fw-semibold">Carreras</div>
                                            <small class="text-muted">Programas académicos</small>
                                        </div>
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @push('scripts')
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    if (typeof Echo !== 'undefined') {
                        Echo.channel('asistencia-channel')
                            .listen('NuevoRegistroAsistencia', (e) => {
                                const tableBody = document.getElementById('latest-attendance-table-body');
                                if (tableBody) {
                                    const newRow = document.createElement('tr');
                                    newRow.innerHTML = `
                                        <td>
                                            <span class="badge bg-primary-subtle text-primary-custom">
                                                ${e.registro.fecha_hora_formateada.split(' ')[1]}
                                            </span>
                                        </td>
                                        <td class="fw-semibold">${e.registro.nro_documento}</td>
                                        <td>${e.registro.nombre_completo || 'N/A'}</td>
                                        <td>
                                            <span class="badge bg-info-subtle text-info-custom">
                                                ${e.registro.tipo_verificacion_texto}
                                            </span>
                                        </td>
                                    `;

                                    tableBody.prepend(newRow);

                                    while (tableBody.children.length > 10) {
                                        tableBody.removeChild(tableBody.lastChild);
                                    }

                                    const emptyRow = tableBody.querySelector('td[colspan="4"]');
                                    if (emptyRow) {
                                        emptyRow.closest('tr').remove();
                                    }
                                }
                            });
                    }
                });
            </script>
        @endpush
    @else
        {{-- Dashboard para usuarios sin permisos de administrador --}}
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center">
                        <i class="mdi mdi-account-circle h1 text-muted"></i>
                        <h4>Bienvenido al Sistema</h4>
                        <p class="text-muted">Tu dashboard específico se está preparando.</p>
                        
                        @if(isset($anuncios) && count($anuncios) > 0)
                            <hr>
                            <h5 class="text-primary">Anuncios Recientes</h5>
                            <div class="row">
                                @foreach($anuncios->take(3) as $anuncio)
                                    <div class="col-md-4">
                                        <div class="card border-left border-primary">
                                            <div class="card-body">
                                                <h6 class="card-title">{{ $anuncio->titulo }}</h6>
                                                <p class="card-text small">{{ Str::limit($anuncio->contenido, 100) }}</p>
                                                <small class="text-muted">{{ $anuncio->fecha_publicacion->format('d/m/Y') }}</small>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
@endif
@endsection