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
    @endif

{{-- ============================================ --}}
{{-- MODAL DE ANUNCIOS - INTEGRADO AL DASHBOARD --}}
{{-- ============================================ --}}
@if(isset($anuncios) && $anuncios->count() > 0)
<div class="modal fade" id="anunciosModal" tabindex="-1" aria-labelledby="anunciosModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            {{-- Header del Modal --}}
            <div class="modal-header bg-primary text-white text-center border-0" style="background: linear-gradient(135deg, #28a745, #20c997) !important;">
                <div class="w-100">
                    <div class="d-flex align-items-center justify-content-center mb-2">
                        <img src="{{ asset('assets/images/logo cepre.png') }}" alt="Logo CEPRE" height="40" class="me-2">
                        <div>
                            <h6 class="mb-0 fw-bold">UNIVERSIDAD NACIONAL AMAZÓNICA DE MADRE DE DIOS</h6>
                            <small>CENTRO PREUNIVERSITARIO - UNAMAD</small>
                        </div>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white position-absolute" data-bs-dismiss="modal" aria-label="Close" style="top: 15px; right: 15px;"></button>
            </div>

            {{-- Body del Modal --}}
            <div class="modal-body p-0">
                {{-- Carrusel de Anuncios --}}
                <div id="anunciosCarousel" class="carousel slide" data-bs-ride="carousel">
                    <div class="carousel-inner">
                        @foreach($anuncios as $index => $anuncio)
                        <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                            <div class="p-4 text-center" style="background: linear-gradient(135deg, #f8f9fa, #e9ecef); min-height: 400px;">
                                {{-- Icono según tipo de anuncio --}}
                                <div class="mb-3">
                                    @switch($anuncio->tipo)
                                        @case('importante')
                                            <i class="fas fa-exclamation-circle text-warning" style="font-size: 3rem;"></i>
                                            @break
                                        @case('urgente')
                                            <i class="fas fa-exclamation-triangle text-danger" style="font-size: 3rem;"></i>
                                            @break
                                        @case('evento')
                                            <i class="fas fa-calendar-alt text-info" style="font-size: 3rem;"></i>
                                            @break
                                        @case('mantenimiento')
                                            <i class="fas fa-tools text-secondary" style="font-size: 3rem;"></i>
                                            @break
                                        @default
                                            <i class="fas fa-info-circle text-primary" style="font-size: 3rem;"></i>
                                    @endswitch
                                </div>

                                {{-- Título del Anuncio --}}
                                <h2 class="fw-bold text-uppercase mb-3" style="color: #28a745; font-size: 2.5rem;">
                                    {{ $anuncio->titulo }}
                                </h2>

                                {{-- Descripción --}}
                                @if($anuncio->descripcion)
                                <p class="lead mb-3" style="color: #495057;">
                                    {{ $anuncio->descripcion }}
                                </p>
                                @endif

                                {{-- Contenido Principal --}}
                                <div class="bg-white rounded-3 p-4 mx-auto shadow-sm" style="max-width: 500px;">
                                    <div style="color: #212529; font-size: 1.1rem; line-height: 1.6;">
                                        {!! nl2br(e($anuncio->contenido)) !!}
                                    </div>
                                </div>

                                {{-- Badge de tipo de anuncio --}}
                                <div class="mt-3">
                                    <span class="badge fs-6 px-3 py-2 
                                        @switch($anuncio->tipo)
                                            @case('importante') bg-warning text-dark @break
                                            @case('urgente') bg-danger @break
                                            @case('evento') bg-info @break
                                            @case('mantenimiento') bg-secondary @break
                                            @default bg-primary @break
                                        @endswitch
                                    ">
                                        {{ ucfirst($anuncio->tipo) }}
                                    </span>
                                </div>

                                {{-- Fecha de publicación --}}
                                <p class="text-muted mt-3 mb-0">
                                    <small>
                                        <i class="fas fa-calendar-check me-1"></i>
                                        Publicado: {{ $anuncio->fecha_publicacion ? \Carbon\Carbon::parse($anuncio->fecha_publicacion)->format('d/m/Y') : $anuncio->created_at->format('d/m/Y') }}
                                    </small>
                                </p>

                                {{-- Imagen si existe --}}
                                @if($anuncio->imagen)
                                <div class="mt-3">
                                    <img src="{{ asset('storage/' . $anuncio->imagen) }}" 
                                         alt="{{ $anuncio->titulo }}" 
                                         class="img-fluid rounded shadow-sm" 
                                         style="max-height: 200px;">
                                </div>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>

                    {{-- Controles del carrusel (solo si hay más de 1 anuncio) --}}
                    @if($anuncios->count() > 1)
                    <button class="carousel-control-prev" type="button" data-bs-target="#anunciosCarousel" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon bg-dark rounded-circle p-2" aria-hidden="true"></span>
                        <span class="visually-hidden">Anterior</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#anunciosCarousel" data-bs-slide="next">
                        <span class="carousel-control-next-icon bg-dark rounded-circle p-2" aria-hidden="true"></span>
                        <span class="visually-hidden">Siguiente</span>
                    </button>

                    {{-- Indicadores --}}
                    <div class="carousel-indicators">
                        @foreach($anuncios as $index => $anuncio)
                        <button type="button" data-bs-target="#anunciosCarousel" data-bs-slide-to="{{ $index }}" 
                                class="{{ $index === 0 ? 'active' : '' }}" aria-label="Anuncio {{ $index + 1 }}"></button>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>

            {{-- Footer del Modal --}}
            <div class="modal-footer bg-light border-0 justify-content-between">
                <div class="text-muted">
                    <small>
                        <i class="fas fa-bullhorn me-1"></i>
                        Anuncios del Centro Preuniversitario
                    </small>
                </div>
                <div>
                    @if($anuncios->count() > 1)
                    <small class="text-muted me-3">
                        <i class="fas fa-layer-group me-1"></i>
                        {{ $anuncios->count() }} anuncios
                    </small>
                    @endif
                    <button type="button" class="btn btn-primary btn-sm" data-bs-dismiss="modal">
                        <i class="fas fa-check me-1"></i>Entendido
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- JavaScript para mostrar automáticamente el modal --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Verificar si el usuario ya vio los anuncios hoy
    const today = new Date().toDateString();
    const lastShown = localStorage.getItem('anunciosModalLastShown');
    
    // Solo mostrar si no se ha mostrado hoy o si hay anuncios urgentes
    const hasUrgentAnnouncements = @json($anuncios->whereIn('tipo', ['urgente', 'importante'])->count() > 0);
    
    if (lastShown !== today || hasUrgentAnnouncements) {
        // Pequeño delay para que cargue completamente la página
        setTimeout(function() {
            const modal = new bootstrap.Modal(document.getElementById('anunciosModal'));
            modal.show();
            
            // Guardar que se mostró hoy (solo para anuncios no urgentes)
            if (!hasUrgentAnnouncements) {
                localStorage.setItem('anunciosModalLastShown', today);
            }
        }, 1000);
    }
    
    // Auto-avanzar el carrusel cada 8 segundos si hay múltiples anuncios
    @if($anuncios->count() > 1)
    setInterval(function() {
        const carousel = document.getElementById('anunciosCarousel');
        if (carousel && document.getElementById('anunciosModal').classList.contains('show')) {
            const bsCarousel = bootstrap.Carousel.getInstance(carousel) || new bootstrap.Carousel(carousel);
            bsCarousel.next();
        }
    }, 8000);
    @endif
});
</script>
@endif
@endsection