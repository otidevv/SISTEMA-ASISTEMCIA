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
{{-- MODAL DE ANUNCIOS CORREGIDO - REEMPLAZA TU SECCIÓN ACTUAL --}}
@if(isset($anuncios) && $anuncios->count() > 0)
<div class="modal fade" id="anunciosModal" tabindex="-1" aria-labelledby="anunciosModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-fullscreen-lg-down modal-xl modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg overflow-hidden">
            
            {{-- Header con mejor posicionamiento --}}
            <div class="modal-header-corregido">
                <div class="header-content-corregido">
                    <div class="d-flex align-items-center">
                        <img src="{{ asset('assets/images/logo cepre.png') }}" alt="Logo CEPRE" height="28" class="me-2">
                        <div class="texto-header-corregido">
                            <h6 class="mb-0 fw-bold">UNAMAD - CENTRO PREUNIVERSITARIO</h6>
                            <small class="opacity-90">Anuncios Importantes</small>
                        </div>
                    </div>
                </div>
                
                {{-- BOTÓN CERRAR CORREGIDO Y SÚPER VISIBLE --}}
                <button type="button" class="btn-close-super-visible" data-bs-dismiss="modal" aria-label="Cerrar">
                    <span class="icono-cerrar">✕</span>
                </button>
            </div>

            {{-- Badge mejorado que no tapa --}}
            <div class="badge-mejorado-container">
                @foreach($anuncios as $index => $anuncio)
                <span class="badge-mejorado badge-{{ $anuncio->tipo }} {{ $index === 0 ? 'active' : '' }}" data-slide="{{ $index }}">
                    <i class="fas fa-
                    @switch($anuncio->tipo)
                        @case('importante') exclamation-circle @break
                        @case('urgente') exclamation-triangle @break
                        @case('evento') calendar-alt @break
                        @case('mantenimiento') tools @break
                        @default info-circle @break
                    @endswitch
                    me-1"></i>
                    {{ strtoupper($anuncio->tipo) }}
                </span>
                @endforeach
            </div>

            {{-- Cuerpo del modal sin conflictos --}}
            <div class="modal-body-sin-conflictos">
                <div id="anunciosCarousel" class="carousel slide h-100" data-bs-ride="carousel">
                    <div class="carousel-inner h-100">
                        @foreach($anuncios as $index => $anuncio)
                        <div class="carousel-item {{ $index === 0 ? 'active' : '' }} h-100">
                            
                            {{-- Contenedor de imagen sin espacios --}}
                            <div class="contenedor-imagen-perfecto">
                                @if($anuncio->imagen)
                                    <img src="{{ asset('storage/' . $anuncio->imagen) }}" 
                                         alt="{{ $anuncio->titulo }}" 
                                         class="imagen-sin-espacios">
                                @else
                                    {{-- Fallback mejorado --}}
                                    <div class="fallback-mejorado">
                                        <i class="fas fa-
                                        @switch($anuncio->tipo)
                                            @case('importante') exclamation-circle @break
                                            @case('urgente') exclamation-triangle @break
                                            @case('evento') calendar-alt @break
                                            @case('mantenimiento') tools @break
                                            @default info-circle @break
                                        @endswitch
                                        icono-fallback"></i>
                                    </div>
                                @endif
                                
                                {{-- Overlay de información mejorado --}}
                                <div class="overlay-informacion-mejorado">
                                    <div class="contenido-overlay">
                                        <h1 class="titulo-overlay">{{ $anuncio->titulo }}</h1>
                                        @if($anuncio->descripcion)
                                        <p class="descripcion-overlay">{{ $anuncio->descripcion }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    {{-- Controles mejorados --}}
                    @if($anuncios->count() > 1)
                    <button class="carousel-control-prev control-mejorado" type="button" data-bs-target="#anunciosCarousel" data-bs-slide="prev">
                        <i class="fas fa-chevron-left"></i>
                    </button>
                    <button class="carousel-control-next control-mejorado" type="button" data-bs-target="#anunciosCarousel" data-bs-slide="next">
                        <i class="fas fa-chevron-right"></i>
                    </button>

                    {{-- Indicadores mejorados --}}
                    <div class="carousel-indicators indicadores-mejorados">
                        @foreach($anuncios as $index => $anuncio)
                        <button type="button" data-bs-target="#anunciosCarousel" data-bs-slide-to="{{ $index }}" 
                                class="{{ $index === 0 ? 'active' : '' }}" aria-label="Anuncio {{ $index + 1 }}"></button>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>

            {{-- Footer corregido --}}
            <div class="modal-footer-corregido">
                <div class="contenido-footer-mejorado">
                    @foreach($anuncios as $index => $anuncio)
                    <div class="info-slide {{ $index === 0 ? 'active' : '' }}" data-slide="{{ $index }}">
                        <div class="texto-info">
                            {{ Str::limit(strip_tags($anuncio->contenido), 100) }}
                            @if(strlen(strip_tags($anuncio->contenido)) > 100)
                                <button class="btn-ver-mas" onclick="mostrarContenidoCompleto({{ $index }})">Ver más</button>
                            @endif
                        </div>
                        
                        {{-- Modal interno para contenido completo --}}
                        @if(strlen(strip_tags($anuncio->contenido)) > 100)
                        <div id="contenido-completo-{{ $index }}" class="modal-contenido-completo d-none">
                            <div class="contenido-completo-inner">
                                <div class="header-contenido-completo">
                                    <h4>{{ $anuncio->titulo }}</h4>
                                    <button class="btn-cerrar-contenido" onclick="cerrarContenidoCompleto({{ $index }})">×</button>
                                </div>
                                <div class="texto-completo">
                                    {!! nl2br(e($anuncio->contenido)) !!}
                                </div>
                                <div class="fecha-contenido">
                                    <small><i class="fas fa-calendar-alt me-1"></i>{{ $anuncio->fecha_publicacion ? \Carbon\Carbon::parse($anuncio->fecha_publicacion)->format('d/m/Y') : $anuncio->created_at->format('d/m/Y') }}</small>
                                </div>
                            </div>
                        </div>
                        @endif
                        
                        <div class="fecha-info">
                            <small><i class="fas fa-calendar-alt me-1"></i>{{ $anuncio->fecha_publicacion ? \Carbon\Carbon::parse($anuncio->fecha_publicacion)->format('d/m/Y') : $anuncio->created_at->format('d/m/Y') }}</small>
                        </div>
                    </div>
                    @endforeach
                </div>
                
                <div class="acciones-footer">
                    <small class="texto-institucion">Centro Preuniversitario UNAMAD</small>
                    <button type="button" class="btn btn-success btn-sm" data-bs-dismiss="modal">
                        <i class="fas fa-check me-1"></i>Entendido
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ESTILOS COMPLETAMENTE CORREGIDOS --}}
<style>
/* === RESET Y BASE === */
.modal-content {
    border-radius: 12px;
    overflow: hidden;
    height: 95vh;
    position: relative;
}

/* === HEADER CORREGIDO === */
.modal-header-corregido {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    background: linear-gradient(180deg, rgba(0,0,0,0.7) 0%, rgba(0,0,0,0.3) 70%, transparent 100%);
    color: white;
    padding: 12px 20px;
    z-index: 15;
    backdrop-filter: blur(8px);
    border: none;
    display: flex;
    justify-content: space-between;
    align-items: center;
    height: auto;
    min-height: 60px;
}

.header-content-corregido .texto-header-corregido h6,
.header-content-corregido .texto-header-corregido small {
    text-shadow: 2px 2px 4px rgba(0,0,0,0.8);
    line-height: 1.2;
}

/* === BOTÓN CERRAR SÚPER VISIBLE CORREGIDO === */
.btn-close-super-visible {
    background: #dc3545;
    color: white;
    border: 3px solid white;
    border-radius: 50%;
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    font-weight: 900;
    transition: all 0.3s ease;
    cursor: pointer;
    box-shadow: 
        0 0 0 2px rgba(220, 53, 69, 1),
        0 4px 15px rgba(0,0,0,0.5);
    position: relative;
    z-index: 20;
    flex-shrink: 0;
}

.btn-close-super-visible:hover,
.btn-close-super-visible:focus {
    background: #c82333;
    color: white;
    transform: scale(1.1);
    box-shadow: 
        0 0 0 3px white,
        0 0 0 6px rgba(220, 53, 69, 1),
        0 6px 20px rgba(0,0,0,0.6);
    border: 3px solid white;
}

.icono-cerrar {
    font-size: 1.8rem;
    line-height: 1;
    display: block;
}

/* === BADGE MEJORADO === */
.badge-mejorado-container {
    position: absolute;
    top: 70px;
    left: 20px;
    z-index: 10;
}

.badge-mejorado {
    display: none;
    padding: 8px 16px;
    border-radius: 25px;
    font-size: 0.85rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    backdrop-filter: blur(10px);
    border: 2px solid rgba(255,255,255,0.3);
    box-shadow: 0 4px 12px rgba(0,0,0,0.4);
    transition: all 0.3s ease;
}

.badge-mejorado.active {
    display: inline-block;
    animation: badge-entrada 0.5s ease-out;
}

.badge-importante {
    background: rgba(255, 193, 7, 0.9);
    color: #000;
    border-color: rgba(255, 193, 7, 0.6);
}

.badge-urgente {
    background: rgba(220, 53, 69, 0.9);
    color: white;
    border-color: rgba(220, 53, 69, 0.6);
}

.badge-evento {
    background: rgba(23, 162, 184, 0.9);
    color: white;
    border-color: rgba(23, 162, 184, 0.6);
}

.badge-mantenimiento {
    background: rgba(108, 117, 125, 0.9);
    color: white;
    border-color: rgba(108, 117, 125, 0.6);
}

.badge-general {
    background: rgba(0, 123, 255, 0.9);
    color: white;
    border-color: rgba(0, 123, 255, 0.6);
}

@keyframes badge-entrada {
    0% { opacity: 0; transform: translateY(-10px) scale(0.9); }
    100% { opacity: 1; transform: translateY(0) scale(1); }
}

/* === CUERPO SIN CONFLICTOS === */
.modal-body-sin-conflictos {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    padding: 0;
    margin: 0;
}

.contenedor-imagen-perfecto {
    width: 100%;
    height: 100%;
    position: relative;
    overflow: hidden;
}

/* === IMAGEN SIN ESPACIOS BLANCOS CORREGIDA PARA MÓVILES === */
.imagen-sin-espacios {
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center;
    display: block;
}

/* Corrección específica para móviles */
@media (max-width: 768px) {
    .imagen-sin-espacios {
        object-fit: contain;
        object-position: center;
        background: #000;
        user-select: none;
        -webkit-user-select: none;
        -webkit-touch-callout: none;
    }
}

/* Para pantallas muy pequeñas */
@media (max-width: 480px) {
    .imagen-sin-espacios {
        object-fit: contain;
        object-position: center top;
        background: #000;
        max-height: 100vh;
        user-select: none;
        -webkit-user-select: none;
        -webkit-touch-callout: none;
    }
}

/* Prevenir zoom en iOS Safari */
@media screen and (-webkit-min-device-pixel-ratio: 0) {
    .modal-content {
        -webkit-text-size-adjust: 100%;
        -webkit-transform: translateZ(0);
    }
    
    .imagen-sin-espacios {
        -webkit-transform: translateZ(0);
        transform: translateZ(0);
    }
}

/* === FALLBACK MEJORADO === */
.fallback-mejorado {
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #28a745, #20c997);
    display: flex;
    align-items: center;
    justify-content: center;
}

.icono-fallback {
    font-size: 8rem;
    color: rgba(255,255,255,0.9);
    text-shadow: 0 4px 20px rgba(0,0,0,0.3);
}

/* === OVERLAY DE INFORMACIÓN MENOS INTRUSIVO === */
.overlay-informacion-mejorado {
    position: absolute;
    bottom: 55px;
    left: 15px;
    right: 15px;
    background: rgba(0,0,0,0.75);
    color: white;
    padding: 15px;
    text-align: center;
    border-radius: 12px;
    backdrop-filter: blur(12px);
    border: 1px solid rgba(255,255,255,0.15);
    box-shadow: 0 6px 25px rgba(0,0,0,0.6);
}

.titulo-overlay {
    font-size: 2.2rem;
    font-weight: 900;
    text-transform: uppercase;
    margin-bottom: 8px;
    color: #ffffff;
    text-shadow: 
        0 0 8px rgba(255,255,255,0.6),
        2px 2px 0px rgba(0,0,0,1),
        3px 3px 0px rgba(0,0,0,0.7);
    line-height: 1.1;
    letter-spacing: 1px;
}

.descripcion-overlay {
    font-size: 1rem;
    margin-bottom: 0;
    color: rgba(255,255,255,0.95);
    text-shadow: 1px 1px 2px rgba(0,0,0,0.8);
    font-weight: 400;
    line-height: 1.3;
}

/* === INDICADORES REPOSICIONADOS === */
.indicadores-mejorados {
    bottom: 110px;
    margin-bottom: 0;
    z-index: 11;
}

.indicadores-mejorados button {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: rgba(255,255,255,0.6);
    border: 2px solid rgba(255,255,255,0.8);
    margin: 0 4px;
    transition: all 0.3s ease;
}

.indicadores-mejorados button.active {
    background: white;
    transform: scale(1.3);
    box-shadow: 0 2px 10px rgba(0,0,0,0.4);
}

/* === FOOTER MEJORADO Y MENOS INTRUSIVO === */
.modal-footer-corregido {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: linear-gradient(transparent 0%, rgba(0,0,0,0.3) 30%, rgba(0,0,0,0.85) 100%);
    backdrop-filter: blur(8px);
    border: none;
    padding: 0;
    z-index: 8;
    height: auto;
    min-height: 45px;
}

.contenido-footer-mejorado {
    padding: 8px 15px 4px 15px;
}

.info-slide {
    display: none;
}

.info-slide.active {
    display: block;
}

.texto-info {
    font-size: 0.85rem;
    color: rgba(255,255,255,0.95);
    line-height: 1.3;
    margin-bottom: 4px;
    text-shadow: 1px 1px 3px rgba(0,0,0,0.9);
    max-height: none;
    overflow: visible;
}

.btn-ver-mas {
    background: rgba(79, 195, 247, 0.95);
    color: white;
    border: none;
    font-size: 0.8rem;
    padding: 4px 12px;
    margin: 4px 0 0 0;
    cursor: pointer;
    text-decoration: none;
    border-radius: 15px;
    backdrop-filter: blur(5px);
    transition: all 0.3s ease;
    font-weight: 600;
    box-shadow: 0 2px 8px rgba(0,0,0,0.4);
    display: inline-block;
    vertical-align: top;
}

.btn-ver-mas:hover {
    background: rgba(79, 195, 247, 1);
    transform: scale(1.05);
    box-shadow: 0 3px 10px rgba(0,0,0,0.5);
}

.fecha-info {
    display: none;
}

.acciones-footer {
    background: rgba(0,0,0,0.9);
    padding: 6px 15px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    backdrop-filter: blur(12px);
    min-height: 36px;
}

.texto-institucion {
    color: rgba(255,255,255,0.9);
    font-weight: 500;
    text-shadow: 1px 1px 2px rgba(0,0,0,0.8);
    font-size: 0.8rem;
}

/* === MODAL PARA CONTENIDO COMPLETO MENOS INVASIVO === */
.modal-contenido-completo {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.95);
    z-index: 30;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    backdrop-filter: blur(15px);
    overflow-y: auto;
}

.contenido-completo-inner {
    background: white;
    color: #333;
    border-radius: 15px;
    max-width: 90%;
    max-height: 90vh;
    overflow: hidden;
    box-shadow: 0 10px 40px rgba(0,0,0,0.5);
    display: flex;
    flex-direction: column;
    margin: auto;
}

.header-contenido-completo {
    background: #f8f9fa;
    padding: 20px;
    border-bottom: 1px solid #dee2e6;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-shrink: 0;
}

.header-contenido-completo h4 {
    margin: 0;
    color: #333;
    font-weight: 600;
    flex: 1;
    margin-right: 15px;
}

.btn-cerrar-contenido {
    background: #dc3545;
    color: white;
    border: none;
    border-radius: 50%;
    width: 35px;
    height: 35px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    cursor: pointer;
    transition: all 0.3s ease;
    flex-shrink: 0;
}

.btn-cerrar-contenido:hover {
    background: #c82333;
    transform: scale(1.1);
}

.texto-completo {
    padding: 25px;
    font-size: 1.1rem;
    line-height: 1.6;
    overflow-y: auto;
    flex: 1;
    -webkit-overflow-scrolling: touch;
}

.fecha-contenido {
    padding: 15px 25px;
    background: #f8f9fa;
    border-top: 1px solid #dee2e6;
    color: #6c757d;
    flex-shrink: 0;
}

/* === VERSIÓN MÓVIL MENOS INVASIVA === */
@media (max-width: 768px) {
    .modal-contenido-completo {
        padding: 15px;
        align-items: flex-end;
        justify-content: center;
    }
    
    .contenido-completo-inner {
        max-width: 95%;
        max-height: 75vh;
        width: 100%;
        margin-bottom: 0;
        border-radius: 15px 15px 0 0;
        animation: slide-up-mobile 0.3s ease-out;
    }
    
    .header-contenido-completo {
        padding: 15px;
        background: linear-gradient(135deg, #f8f9fa, #e9ecef);
    }
    
    .header-contenido-completo h4 {
        font-size: 1.1rem;
    }
    
    .btn-cerrar-contenido {
        width: 32px;
        height: 32px;
        font-size: 1.3rem;
    }
    
    .texto-completo {
        padding: 20px;
        font-size: 1rem;
        max-height: 50vh;
    }
    
    .fecha-contenido {
        padding: 12px 20px;
    }
}

@media (max-width: 480px) {
    .modal-contenido-completo {
        padding: 10px;
    }
    
    .contenido-completo-inner {
        max-width: 98%;
        max-height: 70vh;
        border-radius: 12px 12px 0 0;
    }
    
    .header-contenido-completo {
        padding: 12px 15px;
    }
    
    .header-contenido-completo h4 {
        font-size: 1rem;
        margin-right: 10px;
    }
    
    .btn-cerrar-contenido {
        width: 28px;
        height: 28px;
        font-size: 1.2rem;
    }
    
    .texto-completo {
        padding: 15px;
        font-size: 0.95rem;
        max-height: 45vh;
    }
    
    .fecha-contenido {
        padding: 10px 15px;
        font-size: 0.85rem;
    }
}

/* Animación de entrada para móviles */
@keyframes slide-up-mobile {
    0% {
        transform: translateY(100%);
        opacity: 0.8;
    }
    100% {
        transform: translateY(0);
        opacity: 1;
    }
}

/* === RESPONSIVE DESIGN COMPLETO === */

/* Tablets y pantallas medianas (768px a 1199px) */
@media (min-width: 768px) and (max-width: 1199px) {
    .modal-content { height: 100vh; border-radius: 0; }
    .titulo-overlay { font-size: 1.9rem; }
    .descripcion-overlay { font-size: 0.95rem; }
    .overlay-informacion-mejorado { 
        bottom: 50px; 
        left: 12px; 
        right: 12px; 
        padding: 14px; 
    }
    .control-mejorado { width: 45px; height: 45px; font-size: 1.2rem; }
    .badge-mejorado-container { top: 60px; left: 15px; }
    .modal-header-corregido { padding: 10px 15px; min-height: 55px; }
    .btn-close-super-visible { width: 45px; height: 45px; }
    .contenido-completo-inner { max-width: 95%; }
    .indicadores-mejorados { bottom: 95px; }
    .acciones-footer { padding: 4px 12px; }
    .contenido-footer-mejorado { padding: 4px 12px 2px 12px; }
}

/* Móviles (576px a 767px) */
@media (min-width: 576px) and (max-width: 767px) {
    .modal-content { height: 100vh; border-radius: 0; }
    .titulo-overlay { 
        font-size: 1.6rem; 
        letter-spacing: 0.5px; 
        line-height: 1;
    }
    .descripcion-overlay { font-size: 0.9rem; }
    .modal-header-corregido { 
        padding: 8px 12px; 
        min-height: 50px;
    }
    .header-content-corregido .texto-header-corregido h6 { font-size: 0.9rem; }
    .header-content-corregido .texto-header-corregido small { font-size: 0.75rem; }
    .btn-close-super-visible { width: 42px; height: 42px; font-size: 1.1rem; }
    .icono-cerrar { font-size: 1.6rem; }
    .control-mejorado { 
        width: 40px; 
        height: 40px; 
        left: 12px; 
        font-size: 1.1rem;
    }
    .carousel-control-next.control-mejorado { right: 12px; }
    .indicadores-mejorados { bottom: 90px; }
    .overlay-informacion-mejorado { 
        bottom: 55px; 
        left: 10px; 
        right: 10px; 
        padding: 12px;
    }
    .badge-mejorado-container { top: 52px; left: 12px; }
    .badge-mejorado { 
        font-size: 0.75rem; 
        padding: 6px 12px; 
    }
    .contenido-completo-inner { 
        max-width: 95%;
        max-height: 95vh;
    }
    .texto-completo { 
        padding: 20px; 
        font-size: 1rem;
    }
    .acciones-footer { padding: 5px 10px; }
    .contenido-footer-mejorado { padding: 8px 10px 4px 10px; }
    .texto-info { font-size: 0.8rem; }
    .btn-ver-mas { 
        font-size: 0.75rem; 
        padding: 4px 10px;
        margin: 4px 0 0 0;
    }
    .modal-footer-corregido { min-height: 50px; }
}

/* Móviles pequeños (menos de 576px) */
@media (max-width: 575px) {
    .modal-content { height: 100vh; border-radius: 0; }
    .titulo-overlay { 
        font-size: 1.4rem; 
        letter-spacing: 0px; 
        line-height: 1.1;
        margin-bottom: 6px;
    }
    .descripcion-overlay { font-size: 0.85rem; }
    .modal-header-corregido { 
        padding: 6px 10px; 
        min-height: 45px;
    }
    .header-content-corregido .texto-header-corregido h6 { font-size: 0.85rem; }
    .header-content-corregido .texto-header-corregido small { font-size: 0.7rem; }
    .header-content-corregido img { height: 24px; }
    .btn-close-super-visible { 
        width: 38px; 
        height: 38px; 
        font-size: 1rem; 
        border-width: 2px;
    }
    .icono-cerrar { font-size: 1.4rem; }
    .control-mejorado { 
        width: 35px; 
        height: 35px; 
        left: 10px; 
        font-size: 1rem;
    }
    .carousel-control-next.control-mejorado { right: 10px; }
    .indicadores-mejorados { bottom: 80px; }
    .indicadores-mejorados button {
        width: 8px;
        height: 8px;
        margin: 0 3px;
    }
    .overlay-informacion-mejorado { 
        bottom: 50px; 
        left: 8px; 
        right: 8px; 
        padding: 10px;
    }
    .badge-mejorado-container { top: 48px; left: 10px; }
    .badge-mejorado { 
        font-size: 0.7rem; 
        padding: 5px 10px; 
    }
    .contenido-footer-mejorado { padding: 8px 8px 4px 8px; }
    .texto-info { 
        font-size: 0.8rem; 
        line-height: 1.3;
        margin-bottom: 6px;
    }
    .acciones-footer { padding: 5px 8px; }
    .texto-institucion { font-size: 0.7rem; }
    .btn.btn-success.btn-sm { padding: 4px 8px; font-size: 0.7rem; }
    .btn-ver-mas { 
        font-size: 0.75rem; 
        padding: 4px 10px;
        margin: 6px 0 0 0;
        display: block;
        width: fit-content;
    }
    .modal-footer-corregido { min-height: 55px; }
    .contenido-completo-inner { 
        max-width: 98%;
        max-height: 95vh;
    }
    .texto-completo { 
        padding: 15px; 
        font-size: 0.95rem;
    }
    .header-contenido-completo {
        padding: 15px;
    }
    .btn-cerrar-contenido {
        width: 30px;
        height: 30px;
        font-size: 1.3rem;
    }
}

/* Móviles muy pequeños (menos de 400px) */
@media (max-width: 399px) {
    .titulo-overlay { 
        font-size: 1.2rem; 
    }
    .descripcion-overlay { font-size: 0.8rem; }
    .modal-header-corregido { 
        padding: 5px 8px; 
        min-height: 40px;
    }
    .header-content-corregido .texto-header-corregido h6 { font-size: 0.8rem; }
    .header-content-corregido .texto-header-corregido small { font-size: 0.65rem; }
    .header-content-corregido img { height: 20px; }
    .btn-close-super-visible { 
        width: 35px; 
        height: 35px; 
        font-size: 0.9rem; 
    }
    .icono-cerrar { font-size: 1.2rem; }
    .control-mejorado { 
        width: 32px; 
        height: 32px; 
        font-size: 0.9rem;
    }
    .overlay-informacion-mejorado { 
        bottom: 45px; 
        left: 6px; 
        right: 6px; 
        padding: 8px;
    }
    .badge-mejorado { 
        font-size: 0.65rem; 
        padding: 4px 8px; 
    }
    .indicadores-mejorados button {
        width: 6px;
        height: 6px;
        margin: 0 2px;
    }
    .indicadores-mejorados { bottom: 70px; }
    .contenido-completo-inner { 
        max-width: 99%;
    }
    .contenido-footer-mejorado { padding: 8px 6px 4px 6px; }
    .acciones-footer { padding: 4px 6px; min-height: 32px; }
    .texto-info { font-size: 0.75rem; }
    .btn-ver-mas { 
        font-size: 0.7rem; 
        padding: 4px 8px; 
        margin: 6px 0 0 0;
        display: block;
    }
    .texto-institucion { font-size: 0.65rem; }
    .btn.btn-success.btn-sm { padding: 3px 6px; font-size: 0.65rem; }
    .modal-footer-corregido { min-height: 60px; }
}

/* === ANIMACIONES === */
.carousel-item { transition: transform 0.8s ease-in-out; }
.titulo-overlay { 
    animation: titulo-resplandor 4s ease-in-out infinite alternate; 
}

@keyframes titulo-resplandor {
    0% { text-shadow: 2px 2px 0px rgba(0,0,0,0.9), 0 0 20px rgba(255,255,255,0.3); }
    100% { text-shadow: 2px 2px 0px rgba(0,0,0,0.9), 0 0 30px rgba(255,255,255,0.5); }
}

/* === UTILIDADES === */
.d-none { display: none !important; }
.d-block { display: block !important; }
</style>

{{-- JAVASCRIPT CORREGIDO --}}
<script>
function mostrarContenidoCompleto(index) {
    const contenido = document.getElementById(`contenido-completo-${index}`);
    if (contenido) {
        contenido.classList.remove('d-none');
        // Mejorar scroll en móviles
        document.body.style.overflow = 'hidden';
        document.body.style.position = 'fixed';
        document.body.style.width = '100%';
        
        // Enfocar en el contenido para activar el scroll
        setTimeout(() => {
            const textoCompleto = contenido.querySelector('.texto-completo');
            if (textoCompleto) {
                textoCompleto.scrollTop = 0;
            }
        }, 100);
    }
}

function cerrarContenidoCompleto(index) {
    const contenido = document.getElementById(`contenido-completo-${index}`);
    if (contenido) {
        contenido.classList.add('d-none');
        // Restaurar scroll normal
        document.body.style.overflow = '';
        document.body.style.position = '';
        document.body.style.width = '';
    }
}

// Cerrar con ESC
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const contenidos = document.querySelectorAll('.modal-contenido-completo:not(.d-none)');
        contenidos.forEach(contenido => {
            contenido.classList.add('d-none');
            // Restaurar scroll
            document.body.style.overflow = '';
            document.body.style.position = '';
            document.body.style.width = '';
        });
    }
});

// Cerrar al hacer click fuera
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('modal-contenido-completo')) {
        e.target.classList.add('d-none');
        // Restaurar scroll
        document.body.style.overflow = '';
        document.body.style.position = '';
        document.body.style.width = '';
    }
});

document.addEventListener('DOMContentLoaded', function() {
    // Lógica de mostrar modal
    const today = new Date().toDateString();
    const lastShown = localStorage.getItem('anunciosModalLastShown');
    const hasUrgentAnnouncements = @json($anuncios->whereIn('tipo', ['urgente', 'importante'])->count() > 0);
    
    if (lastShown !== today || hasUrgentAnnouncements) {
        setTimeout(function() {
            const modal = new bootstrap.Modal(document.getElementById('anunciosModal'));
            modal.show();
            
            if (!hasUrgentAnnouncements) {
                localStorage.setItem('anunciosModalLastShown', today);
            }
        }, 1000);
    }
    
    // Sincronización de elementos
    const carousel = document.getElementById('anunciosCarousel');
    if (carousel) {
        carousel.addEventListener('slide.bs.carousel', function (e) {
            const nextIndex = e.to;
            
            // Cerrar cualquier contenido expandido
            const contenidos = document.querySelectorAll('.modal-contenido-completo:not(.d-none)');
            contenidos.forEach(contenido => {
                contenido.classList.add('d-none');
                // Restaurar scroll
                document.body.style.overflow = '';
                document.body.style.position = '';
                document.body.style.width = '';
            });
            
            // Cambiar badge
            document.querySelectorAll('.badge-mejorado').forEach(badge => {
                badge.classList.remove('active');
            });
            const nextBadge = document.querySelector(`.badge-mejorado[data-slide="${nextIndex}"]`);
            if (nextBadge) nextBadge.classList.add('active');
            
            // Cambiar información
            document.querySelectorAll('.info-slide').forEach(info => {
                info.classList.remove('active');
            });
            const nextInfo = document.querySelector(`.info-slide[data-slide="${nextIndex}"]`);
            if (nextInfo) nextInfo.classList.add('active');
        });
    }
    
    // Auto-avance
    @if($anuncios->count() > 1)
    let autoAdvance = setInterval(function() {
        if (carousel && document.getElementById('anunciosModal').classList.contains('show')) {
            const hasOpenContent = document.querySelectorAll('.modal-contenido-completo:not(.d-none)').length > 0;
            if (!hasOpenContent) {
                const bsCarousel = bootstrap.Carousel.getInstance(carousel) || new bootstrap.Carousel(carousel);
                bsCarousel.next();
            }
        }
    }, 18000);
    @endif
    
    // Pausar en hover
    if (carousel) {
        carousel.addEventListener('mouseenter', function() {
            const bsCarousel = bootstrap.Carousel.getInstance(carousel);
            if (bsCarousel) bsCarousel.pause();
        });
        
        carousel.addEventListener('mouseleave', function() {
            const bsCarousel = bootstrap.Carousel.getInstance(carousel);
            if (bsCarousel) bsCarousel.cycle();
        });
    }
    
    // Limpiar al cerrar modal
    document.getElementById('anunciosModal').addEventListener('hidden.bs.modal', function() {
        // Restaurar scroll completamente
        document.body.style.overflow = '';
        document.body.style.position = '';
        document.body.style.width = '';
        
        // Cerrar cualquier contenido completo abierto
        const contenidos = document.querySelectorAll('.modal-contenido-completo:not(.d-none)');
        contenidos.forEach(contenido => {
            contenido.classList.add('d-none');
        });
    });
});
</script>
@endif
@endsection