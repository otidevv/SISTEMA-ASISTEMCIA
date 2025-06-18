@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    @if (isset($esPadre) && $esPadre)
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
    @else
@endif
@endsection
