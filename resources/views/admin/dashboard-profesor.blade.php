@extends('layouts.app')

@section('title', 'Dashboard Docente')

@push('css')
<style>
    /* Reset y base */
    * {
        box-sizing: border-box;
    }
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-color: #f4f6f8;
        margin: 0;
        padding: 0;
        color: #333;
    }

    /* Contenedor principal */
    .dashboard-container {
        max-width: 1140px;
        margin: 2rem auto;
        padding: 0 1rem;
    }

    /* Header de bienvenida */
    .welcome-header {
        background: linear-gradient(90deg, #3b82f6, #2563eb);
        color: white;
        border-radius: 12px;
        padding: 1.5rem 2rem;
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
    }

    .welcome-title {
        font-size: 1.8rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        flex: 1 1 60%;
        min-width: 250px;
    }

    .welcome-title i {
        font-size: 2.4rem;
        margin-right: 1rem;
        color: #bfdbfe;
    }

    .welcome-subtitle {
        font-size: 1rem;
        opacity: 0.85;
        margin-top: 0.3rem;
        flex: 1 1 60%;
        min-width: 250px;
        color: #dbeafe;
    }

    .time-display {
        background: rgba(255, 255, 255, 0.25);
        padding: 0.6rem 1.2rem;
        border-radius: 9999px;
        font-size: 1.2rem;
        font-weight: 600;
        color: #1e40af;
        display: flex;
        align-items: center;
        justify-content: center;
        flex: 1 1 30%;
        min-width: 150px;
        box-shadow: 0 2px 8px rgba(37, 99, 235, 0.3);
    }

    .time-display i {
        margin-right: 0.6rem;
        font-size: 1.5rem;
    }

    /* Estadísticas */
    .stats-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        background: linear-gradient(135deg, #2563eb, #3b82f6);
        border-radius: 12px;
        color: white;
        padding: 1.5rem 2rem;
        position: relative;
        overflow: hidden;
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        display: flex;
        flex-direction: column;
        justify-content: center;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .stat-card.warning {
        background: linear-gradient(135deg, #facc15, #eab308);
        box-shadow: 0 4px 12px rgba(234, 179, 8, 0.3);
        color: #78350f;
    }

    .stat-card.success {
        background: linear-gradient(135deg, #22c55e, #16a34a);
        box-shadow: 0 4px 12px rgba(21, 128, 61, 0.3);
        color: white;
    }

    .stat-card.info {
        background: linear-gradient(135deg, #14b8a6, #0d9488);
        box-shadow: 0 4px 12px rgba(13, 148, 136, 0.3);
        color: white;
    }

    .stat-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 10px 30px rgba(37, 99, 235, 0.4);
    }

    .stat-value {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 0.3rem;
    }

    .stat-label {
        font-size: 0.9rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1.2px;
    }

    .stat-icon {
        position: absolute;
        right: 1rem;
        bottom: 1rem;
        font-size: 4.5rem;
        opacity: 0.15;
    }

    /* Sesiones */
    .sessions-container {
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        padding: 1rem 1.5rem 2rem 1.5rem;
        margin-bottom: 2rem;
    }

    .sessions-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        margin-bottom: 1rem;
    }

    .sessions-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: #2563eb;
        display: flex;
        align-items: center;
        flex: 1 1 60%;
        min-width: 200px;
    }

    .sessions-title i {
        font-size: 1.6rem;
        margin-right: 0.8rem;
        color: #3b82f6;
    }

    .sessions-header > div {
        display: flex;
        gap: 0.5rem;
        flex: 1 1 35%;
        justify-content: flex-end;
        min-width: 150px;
    }

    .action-button {
        background-color: #2563eb;
        color: white;
        border: none;
        padding: 0.5rem 1rem;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        transition: background-color 0.3s ease;
    }

    .action-button:hover {
        background-color: #1e40af;
    }

    .action-button.outline {
        background-color: transparent;
        border: 2px solid #2563eb;
        color: #2563eb;
    }

    .action-button.outline:hover {
        background-color: #2563eb;
        color: white;
    }

    /* Tarjetas de sesión */
    .session-card {
        background: #f9fafb;
        border-radius: 12px;
        margin-bottom: 1rem;
        padding: 1rem 1.5rem;
        border-left: 6px solid transparent;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        transition: box-shadow 0.3s ease, transform 0.3s ease;
    }

    .session-card.completed {
        border-left-color: #22c55e;
    }

    .session-card.pending {
        border-left-color: #facc15;
    }

    .session-card.programmed {
        border-left-color: #2563eb;
    }

    .session-card:hover {
        box-shadow: 0 6px 20px rgba(0,0,0,0.1);
        transform: translateX(5px);
    }

    .session-time {
        font-weight: 600;
        color: #2563eb;
        display: flex;
        align-items: center;
        flex: 1 1 100%;
        margin-bottom: 0.5rem;
        font-size: 1.1rem;
    }

    .session-time i {
        margin-right: 0.5rem;
        font-size: 1.3rem;
    }

    .session-info {
        background: white;
        padding: 1rem;
        border-radius: 10px;
        flex: 1 1 100%;
        box-shadow: 0 1px 5px rgba(0,0,0,0.05);
    }

    .status-badge {
        padding: 0.4rem 1rem;
        border-radius: 9999px;
        font-size: 0.8rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: inline-flex;
        align-items: center;
        white-space: nowrap;
    }

    .status-badge i {
        margin-right: 0.5rem;
        font-size: 1rem;
    }

    .status-badge.success {
        background-color: #22c55e;
        color: white;
    }

    .status-badge.warning {
        background-color: #facc15;
        color: #78350f;
    }

    .status-badge.info {
        background-color: #2563eb;
        color: white;
    }

    /* Botones de acción */
    .action-button {
        padding: 0.5rem 1rem;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        white-space: nowrap;
        border: none;
        transition: background-color 0.3s ease;
    }

    .action-button i {
        font-size: 1.2rem;
    }

    .btn-primary {
        background-color: #2563eb;
        color: white;
    }

    .btn-primary:hover {
        background-color: #1e40af;
    }

    .btn-outline-primary {
        background-color: transparent;
        border: 2px solid #2563eb;
        color: #2563eb;
    }

    .btn-outline-primary:hover {
        background-color: #2563eb;
        color: white;
    }

    /* Sidebar derecho */
    .sidebar-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        margin-bottom: 2rem;
        padding: 1rem 1.5rem;
    }

    .sidebar-card-header {
        border-bottom: 1px solid #e5e7eb;
        padding-bottom: 0.5rem;
        margin-bottom: 1rem;
    }

    .sidebar-card-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: #2563eb;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .sidebar-card-title i {
        font-size: 1.4rem;
    }

    /* Recordatorios */
    .recordatorio {
        padding: 1rem;
        border-radius: 10px;
        margin-bottom: 1rem;
        border-left: 6px solid;
        background: #f9fafb;
        box-shadow: 0 1px 5px rgba(0,0,0,0.05);
        display: flex;
        align-items: center;
        gap: 0.75rem;
        font-weight: 600;
    }

    .recordatorio.warning {
        border-color: #facc15;
        color: #78350f;
        background-color: #fef3c7;
    }

    .recordatorio.info {
        border-color: #2563eb;
        color: #1e40af;
        background-color: #dbeafe;
    }

    /* Resumen semanal */
    .stat-summary {
        text-align: center;
        padding: 1rem 0;
    }

    .stat-summary-value {
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 0.25rem;
    }

    .stat-summary-label {
        font-size: 0.85rem;
        font-weight: 600;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* Responsive */
    @media (max-width: 991.98px) {
        .welcome-header {
            flex-direction: column;
            align-items: flex-start;
        }
        .welcome-title, .welcome-subtitle, .time-display {
            flex: 1 1 100%;
            margin-bottom: 0.75rem;
            justify-content: flex-start !important;
        }
        .sessions-header > div {
            flex: 1 1 100%;
            justify-content: flex-start !important;
            margin-bottom: 1rem;
        }
        .session-card {
            flex-direction: column;
            margin: 1rem 0.5rem;
        }
        .session-time, .session-info, .status-badge, .action-button {
            flex: 1 1 100%;
            margin-bottom: 0.5rem;
        }
        .action-button {
            justify-content: flex-start;
        }
    }

    @media (max-width: 575.98px) {
        .stat-card {
            padding: 1rem;
        }
        .stat-value {
            font-size: 1.8rem;
        }
        .stat-label {
            font-size: 0.8rem;
        }
        .sessions-title {
            font-size: 1.1rem;
        }
        .action-button {
            padding: 0.4rem 0.8rem;
            font-size: 0.85rem;
        }
        .session-card {
            padding: 1rem;
            margin: 0.5rem 0;
        }
        .session-time {
            font-size: 1rem;
        }
        .session-info {
            padding: 0.8rem;
        }
    }
</style>
@endpush

@section('content')
<div class="dashboard-container">
    <!-- Header de Bienvenida -->
    <div class="welcome-header">
        <div class="welcome-title">
            <i class="mdi mdi-account-circle"></i>
            <div>
                <div>¡Bienvenido, {{ $user->nombre }} {{ $user->apellido_paterno }}!</div>
                <div class="welcome-subtitle">
                    <i class="mdi mdi-calendar-today"></i>
                    {{ \Carbon\Carbon::now()->locale('es')->isoFormat('dddd, D [de] MMMM [de] YYYY') }}
                </div>
            </div>
        </div>
        <div class="time-display">
            <i class="mdi mdi-clock-outline"></i>
            <span id="current-time">{{ \Carbon\Carbon::now()->format('H:i A') }}</span>
        </div>
    </div>

    <!-- Estadísticas -->
    <div class="stats-container">
        <div class="stat-card primary">
            <div class="stat-value">{{ $sesionesHoy }}</div>
            <div class="stat-label">Sesiones Hoy</div>
            <i class="mdi mdi-calendar-check stat-icon"></i>
        </div>
        <div class="stat-card warning">
            <div class="stat-value">{{ $sesionesPendientes }}</div>
            <div class="stat-label">Pendientes</div>
            <i class="mdi mdi-alert-circle stat-icon"></i>
        </div>
        <div class="stat-card success">
            <div class="stat-value">{{ $horasHoy }}</div>
            <div class="stat-label">Horas Hoy</div>
            <i class="mdi mdi-clock stat-icon"></i>
        </div>
        <div class="stat-card info">
            <div class="stat-value">S/. {{ number_format($pagoEstimadoHoy, 2) }}</div>
            <div class="stat-label">Pago Estimado</div>
            <i class="mdi mdi-currency-usd stat-icon"></i>
        </div>
    </div>

    <div class="row">
        <!-- Sesiones de Hoy -->
        <div class="col-lg-8">
            <div class="sessions-container">
                <div class="sessions-header">
                    <h5 class="sessions-title">
                        <i class="mdi mdi-calendar-clock"></i>
                        Mis Sesiones de Hoy
                    </h5>
                    <div>
                        <button class="action-button outline btn-sm me-2">
                            <i class="mdi mdi-filter-variant"></i>
                            Filtros
                        </button>
                        <button class="action-button btn-primary btn-sm">
                            <i class="mdi mdi-plus"></i>
                            Registro Manual
                        </button>
                    </div>
                </div>

                <div>
                    @if($horariosHoy->count() > 0)
                        @foreach($horariosHoyConHoras as $item)
                            @php
                                $horario = $item['horario'];
                                $asistencia = $item['asistencia'];
                                $horaEntradaRegistrada = $item['hora_entrada_registrada'];
                                $horaSalidaRegistrada = $item['hora_salida_registrada'];
                                $horaInicio = \Carbon\Carbon::parse($horario->hora_inicio);
                                $horaFin = \Carbon\Carbon::parse($horario->hora_fin);
                                $ahora = \Carbon\Carbon::now();

                                if ($asistencia) {
                                    $estado = 'completed';
                                    $estadoTexto = 'COMPLETADA';
                                    $estadoColor = 'success';
                                    $estadoIcon = 'mdi-check-circle';
                                } elseif ($ahora->greaterThan($horaInicio)) {
                                    $estado = 'pending';
                                    $estadoTexto = 'PENDIENTE';
                                    $estadoColor = 'warning';
                                    $estadoIcon = 'mdi-clock-alert';
                                } else {
                                    $estado = 'programmed';
                                    $estadoTexto = 'PROGRAMADA';
                                    $estadoColor = 'info';
                                    $estadoIcon = 'mdi-clock-outline';
                                }
                            @endphp

                            <div class="session-card {{ $estado }}">
                                <div class="d-flex flex-column gap-2">
                                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                        <h5 class="mb-0 d-flex align-items-center gap-2">
                                            <i class="mdi mdi-book-open-variant text-primary"></i>
                                            {{ $horario->curso->nombre ?? 'Sin curso' }}
                                        </h5>
                                        <div class="session-time text-primary fw-semibold">
                                            <i class="mdi mdi-clock-outline me-1"></i>
                                            {{ $horaInicio->format('H:i A') }} - {{ $horaFin->format('H:i A') }}
                                        </div>
                                    </div>

                                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
                                    <div class="session-info flex-grow-1 bg-white p-3 rounded shadow-sm">
                                        <div>
                                            <i class="mdi mdi-login text-success"></i>
                                            <strong>Entrada:</strong>
                                            {{ $horaEntradaRegistrada ?? 'Pendiente' }}
                                        </div>
                                        <div>
                                            <i class="mdi mdi-logout text-danger"></i>
                                            <strong>Salida:</strong>
                                            {{ $horaSalidaRegistrada ?? 'Pendiente' }}
                                        </div>
                                        @if($asistencia && $asistencia->tema_desarrollado)
                                            <div class="mt-2">
                                                <small class="text-primary d-block">
                                                    <i class="mdi mdi-notebook me-1"></i>
                                                    {{ $asistencia->tema_desarrollado }}
                                                </small>
                                            </div>
                                        @endif
                                    </div>


                                        <div class="d-flex align-items-center gap-3 flex-wrap justify-content-between w-100">
                                            <div class="d-flex align-items-center gap-3 flex-wrap">
                                                <div class="status-badge bg-{{ $estadoColor }}">
                                                    <i class="mdi {{ $estadoIcon }}"></i>
                                                    {{ $estadoTexto }}
                                                </div>
                                                <div class="text-muted d-none d-md-block shadow-sm px-2 rounded">
                                                    <i class="mdi mdi-map-marker me-1"></i>
                                                    {{ $horario->aula->nombre ?? 'Sin aula' }}
                                                </div>
                                                <div class="text-muted d-none d-md-block">
                                                    <i class="mdi mdi-clock-outline me-1"></i>
                                                    {{ $horaFin->diffInHours($horaInicio) }} horas
                                                </div>
                                            </div>
                                            <div class="d-flex gap-2 flex-wrap">
                                                @if($asistencia)
                                                    <button class="action-button outline btn-sm">
                                                        <i class="mdi mdi-eye"></i>
                                                        Detalles
                                                    </button>
                                                    <button class="action-button outline btn-sm">
                                                        <i class="mdi mdi-file-document"></i>
                                                        Reporte
                                                    </button>
                                                @elseif($estado == 'pending')
                                                    <button class="action-button btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#completarModal{{ $horario->id }}">
                                                        <i class="mdi mdi-check"></i>
                                                        Completar
                                                    </button>
                                                @else
                                                    <button class="action-button outline btn-sm" disabled>
                                                        <i class="mdi mdi-clock"></i>
                                                        Esperando
                                                    </button>
                                                @endif
                                            </div>
                                        </div>


                                    </div>
                                </div>
                            </div>



                            @if($estado == 'pending')
                                <!-- Modal para completar tema -->
                                <div class="modal fade" id="completarModal{{ $horario->id }}" tabindex="-1" aria-labelledby="completarModalLabel{{ $horario->id }}" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form method="POST" action="{{ route('asistencia-docente.store') }}">
                                                @csrf
                                                <input type="hidden" name="asistencia_id" value="{{ $asistencia ? $asistencia->id : '' }}">

                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="completarModalLabel{{ $horario->id }}">
                                                        <i class="mdi mdi-clipboard-text me-2"></i>
                                                        Registrar Tema Desarrollado
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <label for="tema_desarrollado{{ $horario->id }}" class="form-label">
                                                        <i class="mdi mdi-notebook me-2"></i>
                                                        Tema desarrollado
                                                    </label>
                                                    <textarea class="form-control" id="tema_desarrollado{{ $horario->id }}" name="tema_desarrollado" rows="3" required autofocus placeholder="Ingrese el tema desarrollado en la sesión..."></textarea>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                                        <i class="mdi mdi-close me-1"></i>
                                                        Cancelar
                                                    </button>
                                                    <button type="submit" class="btn btn-primary">
                                                        <i class="mdi mdi-content-save me-1"></i>
                                                        Guardar Tema
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                            @endif
                        @endforeach
                    @else
                        <div class="text-center py-5 text-gray-500">
                            <i class="mdi mdi-calendar-remove" style="font-size: 4rem;"></i>
                            <h5 class="mt-3">No tienes sesiones programadas para hoy</h5>
                            <p>Disfruta tu día libre o revisa tu horario semanal</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar Derecho -->
        <div class="col-lg-4">
            @if(count($recordatorios) > 0)
                <div class="sidebar-card">
                    <div class="sidebar-card-header">
                        <h6 class="sidebar-card-title">
                            <i class="mdi mdi-bell-outline"></i>
                            Recordatorios
                        </h6>
                    </div>
                    <div>
                        @foreach($recordatorios as $recordatorio)
                            <div class="recordatorio {{ $recordatorio['tipo'] }}">
                                <i class="mdi mdi-alert-circle"></i>
                                {{ $recordatorio['mensaje'] }}
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if($proximaClase)
                <div class="sidebar-card">
                    <div class="sidebar-card-header">
                        <h6 class="sidebar-card-title">
                            <i class="mdi mdi-clock-outline"></i>
                            Próxima Clase
                        </h6>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <div>
                            <h6 class="mb-1">
                                <i class="mdi mdi-book-open-variant me-2 text-primary"></i>
                                {{ $proximaClase->curso->nombre ?? 'Sin curso' }}
                            </h6>
                            <p class="mb-1 text-muted">
                                <i class="mdi mdi-map-marker me-2"></i>
                                {{ $proximaClase->aula->nombre ?? 'Sin aula' }}
                            </p>
                            <small class="text-primary">
                                <i class="mdi mdi-calendar-clock me-2"></i>
                                {{ ucfirst($proximaClase->dia_semana) }} - 
                                {{ \Carbon\Carbon::parse($proximaClase->hora_inicio)->format('H:i') }}
                            </small>
                        </div>
                        <span class="badge bg-info d-flex align-items-center gap-1">
                            <i class="mdi mdi-clock-outline"></i>
                            En {{ \Carbon\Carbon::now()->diffInHours(\Carbon\Carbon::parse($proximaClase->hora_inicio)) }} horas
                        </span>
                    </div>
                </div>
            @endif

            <div class="sidebar-card">
                <div class="sidebar-card-header">
                    <h6 class="sidebar-card-title">
                        <i class="mdi mdi-chart-line"></i>
                        Resumen Semanal
                    </h6>
                    <small class="text-muted">
                        {{ \Carbon\Carbon::now()->subDays(6)->format('d/m') }} - 
                        {{ \Carbon\Carbon::now()->format('d/m') }}
                    </small>
                </div>
                <div class="row text-center">
                    <div class="col-6 mb-4">
                        <div class="stat-summary">
                            <div class="stat-summary-value text-primary">
                                <i class="mdi mdi-calendar-check mb-2" style="font-size: 2rem;"></i>
                                <div>{{ $resumenSemanal['sesiones'] }}</div>
                            </div>
                            <div class="stat-summary-label">Sesiones</div>
                        </div>
                    </div>
                    <div class="col-6 mb-4">
                        <div class="stat-summary">
                            <div class="stat-summary-value text-success">
                                <i class="mdi mdi-clock mb-2" style="font-size: 2rem;"></i>
                                <div>{{ $resumenSemanal['horas'] }}</div>
                            </div>
                            <div class="stat-summary-label">Horas</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="stat-summary">
                            <div class="stat-summary-value text-info">
                                <i class="mdi mdi-currency-usd mb-2" style="font-size: 2rem;"></i>
                                <div>S/. {{ number_format($resumenSemanal['ingresos'], 0) }}</div>
                            </div>
                            <div class="stat-summary-label">Ingresos</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="stat-summary">
                            <div class="stat-summary-value text-warning">
                                <i class="mdi mdi-chart-arc mb-2" style="font-size: 2rem;"></i>
                                <div>{{ $resumenSemanal['asistencia'] }}%</div>
                            </div>
                            <div class="stat-summary-label">Asistencia</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('js')
<script>
    // Actualizar hora cada minuto
    setInterval(function() {
        const now = new Date();
        const timeString = now.toLocaleTimeString('es-PE', { 
            hour: '2-digit', 
            minute: '2-digit',
            hour12: true 
        });
        document.getElementById('current-time').textContent = timeString;
    }, 60000);
</script>
@endpush
