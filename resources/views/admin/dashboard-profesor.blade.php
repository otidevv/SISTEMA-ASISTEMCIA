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

    .action-button:disabled {
        background-color: #9ca3af !important;
        color: #ffffff !important;
        cursor: not-allowed !important;
        border-color: #9ca3af !important;
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

    .session-card.active {
        border-left-color: #06b6d4;
        background: #f0f9ff;
    }

    .session-card.no-access {
        border-left-color: #ef4444;
        background: #fef2f2;
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

    .status-badge.danger {
        background-color: #ef4444;
        color: white;
    }

    .status-badge.active {
        background-color: #06b6d4;
        color: white;
    }

    /* Modal mejorado */
    .modal-content {
        border: none;
        border-radius: 15px;
        box-shadow: 0 20px 60px rgba(0,0,0,0.2);
    }

    .modal-header {
        background: linear-gradient(135deg, #3b82f6, #2563eb);
        color: white;
        border-radius: 15px 15px 0 0;
        border: none;
        padding: 1.5rem 2rem;
    }

    .modal-title {
        font-weight: 700;
        font-size: 1.25rem;
    }

    .btn-close {
        filter: invert(1);
        opacity: 0.8;
    }

    .btn-close:hover {
        opacity: 1;
    }

    /* Alertas */
    .alert {
        border: none;
        border-radius: 10px;
        padding: 1rem 1.5rem;
        margin-bottom: 1rem;
    }

    .alert-success {
        background-color: #dcfce7;
        color: #166534;
        border-left: 4px solid #22c55e;
    }

    .alert-danger {
        background-color: #fef2f2;
        color: #dc2626;
        border-left: 4px solid #ef4444;
    }

    .alert-warning {
        background-color: #fef3c7;
        color: #92400e;
        border-left: 4px solid #f59e0b;
    }

    .alert-info {
        background-color: #dbeafe;
        color: #1e40af;
        border-left: 4px solid #3b82f6;
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
    }

    /* Spinner de carga */
    .spinner-border-sm {
        width: 1rem;
        height: 1rem;
        border-width: 0.15em;
    }

    /* Formulario */
    .form-control:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 0.2rem rgba(59, 130, 246, 0.25);
    }

    .form-label {
        font-weight: 600;
        color: #374151;
    }

    .form-text {
        font-size: 0.875rem;
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
                        <button class="action-button outline btn-sm me-2" onclick="location.reload()">
                            <i class="mdi mdi-refresh"></i>
                            Actualizar
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
                                $puedeRegistrarTema = $item['puede_registrar_tema'];
                                $dentroHorario = $item['dentro_horario'];
                                $claseTerminada = $item['clase_terminada'];
                                $tieneRegistros = $item['tiene_registros'];
                                
                                $horaInicio = \Carbon\Carbon::parse($horario->hora_inicio);
                                $horaFin = \Carbon\Carbon::parse($horario->hora_fin);
                                $ahora = \Carbon\Carbon::now();

                                // Determinar estado y estilo
                                if ($asistencia) {
                                    $estado = 'completed';
                                    $estadoTexto = 'COMPLETADA';
                                    $estadoColor = 'success';
                                    $estadoIcon = 'mdi-check-circle';
                                    $cardClass = 'completed';
                                    $mensaje = 'Tema desarrollado registrado';
                                } elseif ($dentroHorario) {
                                    $estado = 'active';
                                    $estadoTexto = 'EN CURSO';
                                    $estadoColor = 'active';
                                    $estadoIcon = 'mdi-play-circle';
                                    $cardClass = 'active';
                                    $mensaje = $horaEntradaRegistrada ? 'Puedes registrar el tema ahora' : 'Marca tu entrada para registrar tema';
                                } elseif ($claseTerminada && $tieneRegistros) {
                                    $estado = 'pending';
                                    $estadoTexto = 'PENDIENTE TEMA';
                                    $estadoColor = 'warning';
                                    $estadoIcon = 'mdi-clock-alert';
                                    $cardClass = 'pending';
                                    $mensaje = 'Registra el tema desarrollado';
                                } elseif ($claseTerminada && !$tieneRegistros) {
                                    $estado = 'no-access';
                                    $estadoTexto = 'SIN REGISTROS';
                                    $estadoColor = 'danger';
                                    $estadoIcon = 'mdi-alert-circle';
                                    $cardClass = 'no-access';
                                    $mensaje = 'No se puede registrar tema sin marcaciones';
                                } elseif ($ahora->lessThan($horaInicio)) {
                                    $estado = 'programmed';
                                    $estadoTexto = 'PROGRAMADA';
                                    $estadoColor = 'info';
                                    $estadoIcon = 'mdi-clock-outline';
                                    $cardClass = 'programmed';
                                    $mensaje = 'Clase programada para las ' . $horaInicio->format('H:i');
                                } else {
                                    $estado = 'programmed';
                                    $estadoTexto = 'PROGRAMADA';
                                    $estadoColor = 'info';
                                    $estadoIcon = 'mdi-clock-outline';
                                    $cardClass = 'programmed';
                                    $mensaje = 'Esperando inicio de clase';
                                }
                            @endphp

                            <div class="session-card {{ $cardClass }}" id="session-{{ $horario->id }}">
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
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-2">
                                                        <i class="mdi mdi-login text-success"></i>
                                                        <strong>Entrada:</strong>
                                                        <span class="{{ $horaEntradaRegistrada ? 'text-success' : 'text-muted' }}">
                                                            {{ $horaEntradaRegistrada ?? 'No registrada' }}
                                                        </span>
                                                    </div>
                                                    <div class="mb-2">
                                                        <i class="mdi mdi-logout text-danger"></i>
                                                        <strong>Salida:</strong>
                                                        <span class="{{ $horaSalidaRegistrada ? 'text-success' : 'text-muted' }}">
                                                            {{ $horaSalidaRegistrada ?? 'No registrada' }}
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-2">
                                                        <i class="mdi mdi-map-marker text-info"></i>
                                                        <strong>Aula:</strong>
                                                        {{ $horario->aula->nombre ?? 'Sin aula' }}
                                                    </div>
                                                    <div class="mb-2">
                                                        <small class="text-muted">{{ $mensaje }}</small>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            @if($asistencia && $asistencia->tema_desarrollado)
                                                <div class="mt-2 p-2 bg-light rounded">
                                                    <small class="text-primary d-block">
                                                        <i class="mdi mdi-notebook me-1"></i>
                                                        <strong>Tema:</strong> {{ $asistencia->tema_desarrollado }}
                                                    </small>
                                                </div>
                                            @endif
                                        </div>

                                        <div class="d-flex align-items-center gap-3 flex-wrap justify-content-between w-100">
                                            <div class="d-flex align-items-center gap-3 flex-wrap">
                                                <div class="status-badge {{ $estadoColor }}">
                                                    <i class="mdi {{ $estadoIcon }}"></i>
                                                    {{ $estadoTexto }}
                                                </div>
                                                <div class="text-muted d-none d-md-block">
                                                    <i class="mdi mdi-clock-outline me-1"></i>
                                                    {{ $horaFin->diffInHours($horaInicio) }} hora{{ $horaFin->diffInHours($horaInicio) > 1 ? 's' : '' }}
                                                </div>
                                            </div>
                                            <div class="d-flex gap-2 flex-wrap">
                                                @if($puedeRegistrarTema)
                                                    <button class="action-button btn-primary btn-sm" 
                                                            onclick="abrirModalTema({{ $horario->id }}, '{{ $asistencia ? addslashes($asistencia->tema_desarrollado) : '' }}')">
                                                        <i class="mdi mdi-{{ $asistencia ? 'pencil' : 'plus' }}"></i>
                                                        {{ $asistencia ? 'Editar Tema' : 'Registrar Tema' }}
                                                    </button>
                                                @else
                                                    <button class="action-button outline btn-sm" disabled>
                                                        <i class="mdi mdi-lock"></i>
                                                        No disponible
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
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

<!-- Modal para registrar tema desarrollado -->
<div class="modal fade" id="modalTemaDesarrollado" tabindex="-1" aria-labelledby="modalTemaDesarrolladoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTemaDesarrolladoLabel">
                    <i class="mdi mdi-clipboard-text me-2"></i>
                    Registrar Tema Desarrollado
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form id="formTemaDesarrollado">
                @csrf
                <div class="modal-body">
                    <input type="hidden" id="horario_id" name="horario_id">
                    
                    <div id="alertContainer"></div>
                    
                    <div class="alert alert-info">
                        <i class="mdi mdi-information me-2"></i>
                        <strong>Importante:</strong> Solo puedes registrar el tema durante la clase o después de que termine, siempre que tengas los registros biométricos correspondientes.
                    </div>
                    
                    <div class="mb-3">
                        <label for="tema_desarrollado" class="form-label">
                            <i class="mdi mdi-notebook me-2"></i>
                            Tema Desarrollado *
                        </label>
                        <textarea class="form-control" id="tema_desarrollado" name="tema_desarrollado" 
                                  rows="5" required 
                                  placeholder="Describe detalladamente el tema desarrollado en esta sesión..."></textarea>
                        <div class="form-text">Mínimo 10 caracteres, máximo 1000 caracteres. <span id="contador">0/1000</span></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="mdi mdi-close me-1"></i>
                        Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary" id="btnGuardarTema">
                        <i class="mdi mdi-content-save me-1"></i>
                        Guardar Tema
                    </button>
                </div>
            </form>
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

    // Función para abrir modal de tema desarrollado
    function abrirModalTema(horarioId, temaExistente = '') {
        const modal = new bootstrap.Modal(document.getElementById('modalTemaDesarrollado'));
        
        // Configurar formulario
        document.getElementById('horario_id').value = horarioId;
        document.getElementById('tema_desarrollado').value = temaExistente;
        
        // Actualizar título del modal
        const titulo = document.getElementById('modalTemaDesarrolladoLabel');
        if (temaExistente) {
            titulo.innerHTML = '<i class="mdi mdi-pencil me-2"></i>Editar Tema Desarrollado';
        } else {
            titulo.innerHTML = '<i class="mdi mdi-clipboard-text me-2"></i>Registrar Tema Desarrollado';
        }
        
        // Limpiar alertas y actualizar contador
        document.getElementById('alertContainer').innerHTML = '';
        actualizarContador();
        
        modal.show();
    }

    // Contador de caracteres
    document.getElementById('tema_desarrollado').addEventListener('input', actualizarContador);

    function actualizarContador() {
        const textarea = document.getElementById('tema_desarrollado');
        const contador = document.getElementById('contador');
        const actual = textarea.value.length;
        
        contador.textContent = `${actual}/1000`;
        
        if (actual < 10) {
            contador.style.color = '#dc2626';
        } else if (actual > 900) {
            contador.style.color = '#f59e0b';
        } else {
            contador.style.color = '#22c55e';
        }
    }

    // Manejar envío del formulario
    document.getElementById('formTemaDesarrollado').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const btnGuardar = document.getElementById('btnGuardarTema');
        const originalText = btnGuardar.innerHTML;
        
        // Validación básica
        const tema = document.getElementById('tema_desarrollado').value.trim();
        if (tema.length < 10) {
            mostrarAlertEnModal('danger', 'El tema debe tener al menos 10 caracteres');
            return;
        }
        
        // Deshabilitar botón y mostrar loading
        btnGuardar.disabled = true;
        btnGuardar.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Guardando...';
        
        // Limpiar alertas previas
        document.getElementById('alertContainer').innerHTML = '';
        
        const formData = new FormData(this);
        
        fetch('{{ route("dashboard") }}/docente/tema-desarrollado', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                mostrarAlertEnModal('success', data.message);
                
                // Cerrar modal y recargar después de 2 segundos
                setTimeout(() => {
                    bootstrap.Modal.getInstance(document.getElementById('modalTemaDesarrollado')).hide();
                    location.reload();
                }, 2000);
            } else {
                let mensaje = data.message;
                if (data.errors) {
                    mensaje += '<ul class="mt-2 mb-0">';
                    Object.values(data.errors).forEach(error => {
                        if (Array.isArray(error)) {
                            error.forEach(err => mensaje += `<li>${err}</li>`);
                        } else {
                            mensaje += `<li>${error}</li>`;
                        }
                    });
                    mensaje += '</ul>';
                }
                mostrarAlertEnModal('danger', mensaje);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarAlertEnModal('danger', 'Error de conexión. Por favor, inténtalo de nuevo.');
        })
        .finally(() => {
            // Restaurar botón
            btnGuardar.disabled = false;
            btnGuardar.innerHTML = originalText;
        });
    });

    // Función para mostrar alertas en el modal
    function mostrarAlertEnModal(tipo, mensaje) {
        const alertContainer = document.getElementById('alertContainer');
        const alertClass = tipo === 'success' ? 'alert-success' : 'alert-danger';
        const icon = tipo === 'success' ? 'mdi-check-circle' : 'mdi-alert-circle';
        
        alertContainer.innerHTML = `
            <div class="alert ${alertClass}" role="alert">
                <i class="mdi ${icon} me-2"></i>
                ${mensaje}
            </div>
        `;
        
        // Scroll hacia arriba del modal
        document.querySelector('.modal-body').scrollTop = 0;
    }

    // Cerrar alertas automáticamente después de 5 segundos
    setTimeout(() => {
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            if (alert.classList.contains('alert-success')) {
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 300);
            }
        });
    }, 5000);
</script>
@endpush