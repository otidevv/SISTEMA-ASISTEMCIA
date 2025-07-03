@extends('layouts.app')

@section('title', 'Dashboard Docente')

@push('css')
{{-- Incluir Google Fonts y Material Design Icons --}}
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@mdi/font@7.4.47/css/materialdesignicons.min.css">

<style>
    /* -------------------------------------------------------------------------- */
    /* Variables de Diseño Avanzado                                               */
    /* -------------------------------------------------------------------------- */
    :root {
        --font-family-sans-serif: 'Inter', sans-serif;
        --bg-color: #f4f7fe;
        --card-bg: rgba(255, 255, 255, 0.9);
        --text-color: #1e293b;
        --text-muted: #64748b;
        --primary-color: #4f46e5;
        --primary-hover: #4338ca;
        --primary-light: #eef2ff;
        --primary-text: #312e81;
        --success-color: #22c55e;
        --success-light: #f0fdf4;
        --success-text: #166534;
        --warning-color: #f59e0b;
        --warning-light: #fffbeb;
        --warning-text: #78350f;
        --danger-color: #ef4444;
        --danger-light: #fef2f2;
        --danger-text: #991b1b;
        --info-color: #3b82f6;
        --info-light: #eff6ff;
        --info-text: #1e40af;
        --border-color: #e2e8f0;
        --border-radius: 1rem; /* 16px */
        --shadow-sm: 0 4px 6px -1px rgb(0 0 0 / 0.05), 0 2px 4px -2px rgb(0 0 0 / 0.05);
        --shadow: 0 10px 15px -3px rgb(0 0 0 / 0.07), 0 4px 6px -4px rgb(0 0 0 / 0.07);
        --shadow-lg: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
        --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* -------------------------------------------------------------------------- */
    /* Estilos Base y Fondo                                                       */
    /* -------------------------------------------------------------------------- */
    body {
        font-family: var(--font-family-sans-serif);
        background-color: var(--bg-color);
        color: var(--text-color);
        background-image: radial-gradient(var(--border-color) 1px, transparent 1px);
        background-size: 20px 20px;
    }

    .dashboard-container {
        max-width: 1400px;
        margin: 2rem auto;
        padding: 0 1.5rem;
    }

    /* -------------------------------------------------------------------------- */
    /* Encabezado de Bienvenida (Efecto Glass)                                    */
    /* -------------------------------------------------------------------------- */
    .welcome-header {
        background: rgba(255, 255, 255, 0.6);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: var(--border-radius);
        padding: 1.5rem 2.5rem;
        margin-bottom: 2.5rem;
        box-shadow: var(--shadow);
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        align-items: center;
        gap: 1.5rem;
        position: relative;
        overflow: hidden;
    }
    .welcome-header::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(79, 70, 229, 0.1), transparent 40%);
        animation: rotate 15s linear infinite;
    }
    @keyframes rotate {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }

    .welcome-header .welcome-text .welcome-title {
        font-size: 2rem;
        font-weight: 800;
        color: var(--text-color);
        letter-spacing: -0.5px;
    }

    .welcome-header .welcome-text .welcome-subtitle {
        font-size: 1.1rem;
        color: var(--text-muted);
        font-weight: 500;
    }

    .time-display {
        background: var(--primary-color);
        color: white;
        padding: 0.75rem 1.5rem;
        border-radius: 9999px;
        font-size: 1rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        box-shadow: 0 4px 15px -3px rgb(79 70 229 / 40%);
    }

    /* -------------------------------------------------------------------------- */
    /* Tarjetas de Estadísticas                                                   */
    /* -------------------------------------------------------------------------- */
    .stats-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2.5rem;
    }

    .stat-card {
        background: var(--card-bg);
        border-radius: var(--border-radius);
        border: 1px solid var(--border-color);
        padding: 1.5rem;
        display: flex;
        align-items: flex-start;
        gap: 1rem;
        transition: var(--transition);
        position: relative;
        overflow: hidden;
    }
    .stat-card:hover {
        transform: translateY(-8px);
        box-shadow: var(--shadow-lg);
        border-color: var(--primary-color);
    }

    .stat-card .stat-icon {
        flex-shrink: 0;
        padding: 0.75rem;
        border-radius: 0.75rem;
        font-size: 1.5rem;
        color: white;
        background-image: linear-gradient(135deg, var(--color-from), var(--color-to));
        box-shadow: 0 4px 8px -2px rgba(0,0,0,0.2);
    }
    .stat-card.primary .stat-icon { --color-from: #6366f1; --color-to: #4f46e5; }
    .stat-card.warning .stat-icon { --color-from: #f59e0b; --color-to: #d97706; }
    .stat-card.success .stat-icon { --color-from: #22c55e; --color-to: #16a34a; }
    .stat-card.info .stat-icon { --color-from: #3b82f6; --color-to: #2563eb; }

    .stat-card .stat-info .stat-value {
        font-size: 2.25rem;
        font-weight: 800;
        line-height: 1;
        color: var(--text-color);
    }
    .stat-card .stat-info .stat-label {
        font-size: 0.9rem;
        color: var(--text-muted);
        font-weight: 500;
        margin-top: 0.25rem;
    }

    /* -------------------------------------------------------------------------- */
    /* Área de Contenido Principal                                                */
    /* -------------------------------------------------------------------------- */
    .main-content-card {
        background: var(--card-bg);
        border-radius: var(--border-radius);
        box-shadow: var(--shadow);
        border: 1px solid var(--border-color);
        padding: 2rem;
        margin-bottom: 2rem;
    }

    .main-content-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
        margin-bottom: 2rem;
    }
    .main-content-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--text-color);
    }

    /* -------------------------------------------------------------------------- */
    /* Tarjetas de Sesión (Diseño Timeline + Tarjeta Completa y coloreada)      */
    /* -------------------------------------------------------------------------- */
    .session-timeline {
        position: relative;
    }
    .session-timeline::before {
        content: '';
        position: absolute;
        left: 14px;
        top: 10px;
        bottom: 10px;
        width: 2px;
        background-color: var(--border-color);
        border-radius: 2px;
    }

    .session-card {
        position: relative;
        padding-left: 3rem; /* Espacio para el punto y la línea */
        margin-bottom: 2rem;
    }
    .session-card:last-child {
        margin-bottom: 0;
    }

    .session-card::before { /* El punto en la línea de tiempo */
        content: '';
        position: absolute;
        left: 8px; /* (14px de la línea) - (12px de ancho / 2) */
        top: 2.25rem; /* Alineado con el centro del header de la tarjeta */
        width: 14px;
        height: 14px;
        border-radius: 50%;
        background-color: var(--dot-color);
        border: 3px solid var(--bg-color);
        z-index: 1;
    }

    /* Asignar colores al punto */
    .session-card.programmed { --dot-color: var(--primary-color); }
    .session-card.completed { --dot-color: var(--success-color); }
    .session-card.pending { --dot-color: var(--warning-color); }
    .session-card.active { --dot-color: var(--info-color); }
    .session-card.no-access { --dot-color: var(--danger-color); }

    .session-card-content {
        border-radius: var(--border-radius);
        padding: 1.5rem;
        transition: var(--transition);
        border: 1px solid;
    }

    .session-card:hover .session-card-content {
        transform: translateY(-5px) scale(1.02);
        box-shadow: var(--shadow);
    }

    /* Colores de Tarjeta por Estado */
    .session-card.programmed .session-card-content {
        background-color: var(--card-bg);
        border-color: var(--border-color);
    }
    .session-card.programmed:hover .session-card-content {
        border-color: var(--primary-color);
    }
    .session-card.completed .session-card-content { background-color: var(--success-light); border-color: var(--success-color); }
    .session-card.pending .session-card-content { background-color: var(--warning-light); border-color: var(--warning-color); }
    .session-card.active .session-card-content { background-color: var(--info-light); border-color: var(--info-color); box-shadow: 0 0 20px 0 rgb(59 130 246 / 25%); }
    .session-card.no-access .session-card-content { background-color: var(--danger-light); border-color: var(--danger-color); }
    
    .session-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        flex-wrap: wrap;
        gap: 1rem;
        margin-bottom: 1rem;
    }
    .course-name {
        font-size: 1.2rem;
        font-weight: 700;
        color: var(--text-color);
    }
    .session-time {
        font-weight: 500;
        color: var(--text-muted);
        font-size: 0.9rem;
        flex-shrink: 0;
    }
    .session-details {
        font-size: 0.9rem;
        color: var(--text-muted);
        display: flex;
        flex-wrap: wrap;
        gap: 1rem 1.5rem;
        margin-bottom: 1rem;
    }
    .session-details strong {
        color: var(--text-color);
        font-weight: 600;
    }
    
    .tema-registrado {
        background-color: rgba(255, 255, 255, 0.5);
        border-radius: 0.75rem;
        padding: 1rem;
        margin-top: 1rem;
        font-size: 0.9rem;
    }
    .tema-registrado strong {
        color: var(--primary-text);
    }

    .session-footer {
        margin-top: 1.5rem;
        padding-top: 1.5rem;
        border-top: 1px solid rgba(0,0,0,0.1);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
    }
    .session-card.programmed .session-footer {
        border-top-color: var(--border-color);
    }

    /* -------------------------------------------------------------------------- */
    /* Sidebar                                                                    */
    /* -------------------------------------------------------------------------- */
    .sidebar-card {
        background: var(--card-bg);
        border-radius: var(--border-radius);
        box-shadow: var(--shadow);
        border: 1px solid var(--border-color);
        margin-bottom: 1.5rem;
        padding: 1.5rem;
        transition: var(--transition);
    }
    .sidebar-card:hover {
        box-shadow: var(--shadow-lg);
    }
    .sidebar-card-title {
        font-size: 1.1rem;
        font-weight: 700;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .sidebar-card-title i {
        color: var(--primary-color);
    }

    /* -------------------------------------------------------------------------- */
    /* Componentes UI (Botones, Badges, etc)                                      */
    /* -------------------------------------------------------------------------- */
    .action-button {
        background-image: linear-gradient(to right, var(--primary-color) 0%, #6d28d9 51%, var(--primary-color) 100%);
        background-size: 200% auto;
        color: white;
        border: none;
        padding: 0.6rem 1.2rem;
        border-radius: 0.5rem;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: var(--transition);
        text-decoration: none;
        box-shadow: var(--shadow-sm);
    }
    .action-button:hover {
        background-position: right center;
        transform: scale(1.05);
        box-shadow: var(--shadow);
    }
    .action-button.outline {
        background-image: none;
        background-color: transparent;
        border: 1px solid var(--primary-color);
        color: var(--primary-color);
        box-shadow: none;
    }
    .action-button.outline:hover {
        background-color: var(--primary-color);
        color: white;
        transform: scale(1.05);
        box-shadow: var(--shadow);
    }
    .action-button:disabled {
        background-image: none;
        background-color: #e5e7eb !important;
        color: var(--text-muted) !important;
        cursor: not-allowed !important;
        border-color: #e5e7eb !important;
        transform: none;
        box-shadow: none !important;
    }

    .status-badge {
        padding: 0.4rem 1rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        border: 1px solid;
    }
    .status-badge.success { background-color: var(--success-light); color: var(--success-text); border-color: var(--success-color); }
    .status-badge.warning { background-color: var(--warning-light); color: var(--warning-text); border-color: var(--warning-color); }
    .status-badge.info { background-color: var(--primary-light); color: var(--primary-text); border-color: var(--primary-color); }
    .status-badge.danger { background-color: var(--danger-light); color: var(--danger-text); border-color: var(--danger-color); }
    .status-badge.active { background-color: var(--info-light); color: var(--info-text); border-color: var(--info-color); }

    /* Modal */
    .modal-content {
        border-radius: var(--border-radius);
        border: none;
        box-shadow: var(--shadow-lg);
        background-color: var(--bg-color);
    }
    .modal-header {
        background: var(--primary-color);
        color: white;
        border-bottom: none;
        border-radius: var(--border-radius) var(--border-radius) 0 0;
        padding: 1.5rem;
    }
    .modal-title {
        font-weight: 700;
    }
    .form-control {
        border-radius: 0.5rem;
        border: 1px solid var(--border-color);
        padding: 0.75rem 1rem;
    }
    .form-control:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 3px rgb(79 70 229 / 15%);
    }
    
    /* Estilos para el modal de anuncios */
    #modalAnuncios .modal-body ul {
        list-style: none;
        padding-left: 0;
    }
    #modalAnuncios .modal-body li {
        display: flex;
        align-items: flex-start;
        gap: 1rem;
        margin-bottom: 1rem;
    }
    #modalAnuncios .modal-body .announcement-icon {
        color: var(--primary-color);
        font-size: 1.5rem;
        margin-top: 0.25rem;
    }


    /* -------------------------------------------------------------------------- */
    /* Estilos Responsivos                                                        */
    /* -------------------------------------------------------------------------- */
    @media (max-width: 768px) {
        .welcome-header .welcome-text .welcome-title {
            font-size: 1.5rem;
        }
        .main-content-title {
            font-size: 1.25rem;
        }
    }

    @media (max-width: 576px) {
        .dashboard-container {
            padding: 0 1rem;
            margin-top: 1rem;
        }
        .welcome-header {
            padding: 1.5rem;
            text-align: center;
            justify-content: center;
        }
        .main-content-card, .sidebar-card {
            padding: 1.5rem;
        }
        .session-card-content {
            padding: 1rem;
        }
        .session-timeline {
            padding-left: 0;
        }
        .session-timeline::before, .session-card::before {
            display: none; /* Ocultar línea de tiempo en móviles */
        }
        .session-card {
            padding-left: 0;
        }
    }

</style>
@endpush

@section('content')
<div class="dashboard-container">
    <!-- Header de Bienvenida -->
    <div class="welcome-header">
        <div class="welcome-text">
            <div class="welcome-title">¡Bienvenido de vuelta, {{ $user->nombre }}!</div>
            <div class="welcome-subtitle">
                {{ \Carbon\Carbon::now()->locale('es')->isoFormat('dddd, D [de] MMMM') }}
            </div>
        </div>
        <div class="time-display">
            <i class="mdi mdi-clock-outline"></i>
            <span id="current-time">{{ \Carbon\Carbon::now()->format('H:i:s A') }}</span>
        </div>
    </div>

    <!-- Estadísticas -->
    <div class="stats-container">
        <div class="stat-card primary">
            <div class="stat-icon"><i class="mdi mdi-calendar-multiselect"></i></div>
            <div class="stat-info">
                <div class="stat-value">{{ $sesionesHoy }}</div>
                <div class="stat-label">Sesiones para Hoy</div>
            </div>
        </div>
        <div class="stat-card warning">
            <div class="stat-icon"><i class="mdi mdi-file-document-edit-outline"></i></div>
            <div class="stat-info">
                <div class="stat-value">{{ $sesionesPendientes }}</div>
                <div class="stat-label">Pendientes de Tema</div>
            </div>
        </div>
        <div class="stat-card success">
            <div class="stat-icon"><i class="mdi mdi-clock-check-outline"></i></div>
            <div class="stat-info">
                <div class="stat-value">{{ $horasHoy }}</div>
                <div class="stat-label">Total Horas Hoy</div>
            </div>
        </div>
        <div class="stat-card info">
            <div class="stat-icon"><i class="mdi mdi-cash-multiple"></i></div>
            <div class="stat-info">
                <div class="stat-value">S/. {{ number_format($pagoEstimadoHoy, 2) }}</div>
                <div class="stat-label">Pago Estimado</div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Columna Principal: Sesiones de Hoy -->
        <div class="col-lg-8">
            <div class="main-content-card">
                <div class="main-content-header">
                    <h5 class="main-content-title">
                        Agenda del <span class="text-primary">{{ $fechaSeleccionada->isToday() ? 'Día' : $fechaSeleccionada->locale('es')->isoFormat('D [de] MMMM') }}</span>
                    </h5>
                    {{-- MODIFICADO: Formulario para seleccionar fecha --}}
                    <form method="GET" action="{{ route('dashboard') }}" class="d-flex align-items-center gap-2">
                        <input type="date" name="fecha" id="fecha-agenda" class="form-control form-control-sm" style="width: auto;" value="{{ $fechaSeleccionada->format('Y-m-d') }}">
                        <button type="submit" class="action-button outline btn-sm">
                            <i class="mdi mdi-magnify"></i>
                            <span>Ver</span>
                        </button>
                    </form>
                </div>

                <div class="session-timeline">
                    @forelse($horariosDelDia as $item)
                        @php
                            $horario = $item['horario'];
                            $asistencia = $item['asistencia'];
                            $horaInicio = \Carbon\Carbon::parse($horario->hora_inicio);
                            $horaFin = \Carbon\Carbon::parse($horario->hora_fin);

                            $estadoConfig = ['clase' => 'programmed', 'texto' => 'PROGRAMADA', 'color' => 'info', 'icono' => 'mdi-clock-outline'];
                            if ($asistencia) { $estadoConfig = ['clase' => 'completed', 'texto' => 'COMPLETADA', 'color' => 'success', 'icono' => 'mdi-check-all']; } 
                            elseif ($item['dentro_horario']) { $estadoConfig = ['clase' => 'active', 'texto' => 'EN CURSO', 'color' => 'active', 'icono' => 'mdi-play-circle']; } 
                            elseif ($item['clase_terminada'] && $item['tiene_registros']) { $estadoConfig = ['clase' => 'pending', 'texto' => 'PENDIENTE', 'color' => 'warning', 'icono' => 'mdi-alert-circle-check-outline']; } 
                            elseif ($item['clase_terminada'] && !$item['tiene_registros']) { $estadoConfig = ['clase' => 'no-access', 'texto' => 'SIN REGISTRO', 'color' => 'danger', 'icono' => 'mdi-close-circle-outline']; }
                        @endphp

                        <div class="session-card {{ $estadoConfig['clase'] }}" id="session-{{ $horario->id }}">
                            <div class="session-card-content">
                                <div class="session-header">
                                    <h6 class="course-name">{{ $horario->curso->nombre ?? 'Sin curso' }}</h6>
                                    <div class="session-time">
                                        <i class="mdi mdi-clock-outline"></i>
                                        {{ $horaInicio->format('h:i A') }} - {{ $horaFin->format('h:i A') }}
                                    </div>
                                </div>

                                <div class="session-details">
                                    <div><i class="mdi mdi-login text-success"></i> <strong>Entrada:</strong> {{ $item['hora_entrada_registrada'] ?? '---' }}</div>
                                    <div><i class="mdi mdi-logout text-danger"></i> <strong>Salida:</strong> {{ $item['hora_salida_registrada'] ?? '---' }}</div>
                                    <div><i class="mdi mdi-map-marker-outline text-info"></i> <strong>Aula:</strong> {{ $horario->aula->nombre ?? 'N/A' }}</div>
                                    {{-- Mostrar tardanza si existe --}}
                                    @if(isset($item['minutos_tardanza']) && $item['minutos_tardanza'] > 0)
                                        <div class="text-danger"><i class="mdi mdi-timer-sand-empty"></i> <strong>Tardanza:</strong> {{ $item['minutos_tardanza'] }} min</div>
                                    @endif
                                </div>
                                
                                @if($asistencia && $asistencia->tema_desarrollado)
                                    <div class="tema-registrado">
                                        <p class="mb-0">
                                            <strong class="text-primary"><i class="mdi mdi-notebook-check-outline"></i> Tema:</strong>
                                            {{ Str::limit($asistencia->tema_desarrollado, 100) }}
                                        </p>
                                    </div>
                                @endif

                                <div class="session-footer">
                                    <div class="status-badge {{ $estadoConfig['color'] }}">
                                        <i class="mdi {{ $estadoConfig['icono'] }}"></i>
                                        {{ $estadoConfig['texto'] }}
                                    </div>
                                    <div class="d-flex gap-2">
                                        {{-- El botón de registrar tema solo debe aparecer si la clase ya pasó --}}
                                        @if($item['puede_registrar_tema'])
                                            <button class="action-button btn-sm" onclick="abrirModalTema({{ $horario->id }}, '{{ $asistencia ? addslashes($asistencia->tema_desarrollado) : '' }}')">
                                                <i class="mdi mdi-{{ $asistencia && $asistencia->tema_desarrollado ? 'pencil' : 'plus' }}"></i>
                                                {{ $asistencia && $asistencia->tema_desarrollado ? 'Editar Tema' : 'Registrar Tema' }}
                                            </button>
                                        @else
                                            <button class="action-button outline btn-sm" disabled title="Solo se puede registrar el tema de clases finalizadas y con registro de entrada/salida.">
                                                <i class="mdi mdi-lock-outline"></i>
                                                Registrar Tema
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5 text-muted">
                            <i class="mdi mdi-calendar-remove" style="font-size: 4rem;"></i>
                            <h5 class="mt-3 fw-bold">Sin Sesiones</h5>
                            <p>No se encontraron sesiones programadas para la fecha seleccionada.</p>
                        </div>
                    
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Sidebar Derecho -->
        <div class="col-lg-4">
            @if($proximaClase)
                <div class="sidebar-card">
                    <h6 class="sidebar-card-title"><i class="mdi mdi-skip-next-circle-outline"></i> Próxima Clase</h6>
                    <div>
                        <h6 class="mb-1 fw-bold">{{ $proximaClase->curso->nombre ?? 'Sin curso' }}</h6>
                        <p class="mb-2 text-muted"><i class="mdi mdi-map-marker-outline me-1"></i> {{ $proximaClase->aula->nombre ?? 'Sin aula' }}</p>
                        <div class="p-2 rounded" style="background-color: var(--primary-light); color: var(--primary-text);">
                            <i class="mdi mdi-calendar-clock me-1"></i>
                            <strong>{{ ucfirst($proximaClase->dia_semana) }}</strong> a las <strong>{{ \Carbon\Carbon::parse($proximaClase->hora_inicio)->format('h:i A') }}</strong>
                        </div>
                    </div>
                </div>
            @endif

            @if(count($recordatorios) > 0)
                <div class="sidebar-card">
                    <h6 class="sidebar-card-title"><i class="mdi mdi-bell-ring-outline"></i> Recordatorios</h6>
                    <div>
                        @foreach($recordatorios as $recordatorio)
                            <div class="alert alert-{{ $recordatorio['tipo'] }} d-flex align-items-center p-2" role="alert">
                                <i class="mdi mdi-alert-circle-outline me-2"></i>
                                <small>{{ $recordatorio['mensaje'] }}</small>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
            
            <div class="sidebar-card">
                <h6 class="sidebar-card-title"><i class="mdi mdi-chart-donut"></i> Resumen Semanal</h6>
                <div class="row text-center">
                    <div class="col-6 mb-3"><div class="fs-4 fw-bold text-primary">{{ $resumenSemanal['sesiones'] }}</div><small class="text-muted">Sesiones</small></div>
                    <div class="col-6 mb-3"><div class="fs-4 fw-bold text-success">{{ $resumenSemanal['horas'] }}</div><small class="text-muted">Horas</small></div>
                    <div class="col-6"><div class="fs-4 fw-bold text-info">S/.{{ number_format($resumenSemanal['ingresos'], 0) }}</div><small class="text-muted">Ingresos</small></div>
                    <div class="col-6"><div class="fs-4 fw-bold text-warning">{{ $resumenSemanal['asistencia'] }}%</div><small class="text-muted">Asistencia</small></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para registrar tema desarrollado -->
<div class="modal fade" id="modalTemaDesarrollado" tabindex="-1" aria-labelledby="modalTemaDesarrolladoLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTemaDesarrolladoLabel"><i class="mdi mdi-clipboard-edit-outline me-2"></i>Registrar Tema Desarrollado</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form id="formTemaDesarrollado" class="p-4">
                @csrf
                <div class="modal-body p-0">
                    <input type="hidden" id="horario_id" name="horario_id">
                    {{-- AÑADIDO: Input oculto para la fecha seleccionada del calendario --}}
                    <input type="hidden" id="fecha_seleccionada_input_oculto" name="fecha_seleccionada" value="{{ $fechaSeleccionada->format('Y-m-d') }}">
                    <div id="alertContainer" class="mb-3"></div>
                    <div class="mb-3">
                        <label for="tema_desarrollado" class="form-label fw-bold">Tema y Actividades Realizadas *</label>
                        <textarea class="form-control" id="tema_desarrollado" name="tema_desarrollado" rows="6" required placeholder="Sea específico sobre los temas cubiertos, ejemplos mostrados y actividades realizadas..."></textarea>
                        <div class="form-text d-flex justify-content-between mt-1">
                            <span>Mínimo 10 caracteres.</span>
                            <span id="contador">0/1000</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 p-0 pt-3">
                    <button type="button" class="action-button outline" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="action-button" id="btnGuardarTema">
                        <i class="mdi mdi-content-save me-1"></i>
                        <span>Guardar Tema</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de Anuncios -->
<div class="modal fade" id="modalAnuncios" tabindex="-1" aria-labelledby="modalAnunciosLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalAnunciosLabel"><i class="mdi mdi-bullhorn-variant-outline me-2"></i>Anuncios Importantes</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-4">
                <p class="text-muted">Por favor, tome un momento para revisar las siguientes directrices sobre el registro de asistencia y temas.</p>
                <ul class="mt-3">
                    <li>
                        <i class="mdi mdi-clock-alert-outline announcement-icon"></i>
                        <div>
                            <strong class="d-block">Tolerancia de 5 Minutos</strong>
                            Se permite un máximo de 5 minutos de tolerancia para el registro de su hora de entrada. Pasado este tiempo, se considerará tardanza.
                        </div>
                    </li>
                    <li>
                        <i class="mdi mdi-login-variant announcement-icon"></i>
                        <div>
                            <strong class="d-block">Registro de Entrada y Salida</strong>
                            Es obligatorio registrar tanto su hora de entrada como su hora de salida para que las horas de clase sean contabilizadas correctamente.
                        </div>
                    </li>
                    <li>
                        <i class="mdi mdi-book-edit-outline announcement-icon"></i>
                        <div>
                            <strong class="d-block">Registro del Tema Desarrollado</strong>
                            No olvide registrar el tema desarrollado al finalizar cada sesión. Este paso es crucial para el seguimiento académico y la validación de su trabajo.
                        </div>
                    </li>
                </ul>
            </div>
            <div class="modal-footer border-0 p-4 pt-0">
                <button type="button" class="action-button" data-bs-dismiss="modal">
                    <i class="mdi mdi-check-circle-outline me-1"></i>
                    <span>Entendido</span>
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('js')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Mostrar modal de anuncios una vez por sesión
        if (!sessionStorage.getItem('anunciosVistos')) {
            const modalAnuncios = new bootstrap.Modal(document.getElementById('modalAnuncios'));
            modalAnuncios.show();
            sessionStorage.setItem('anunciosVistos', 'true');
        }

        // --- Lógica existente ---
        const timeElement = document.getElementById('current-time');
        if (timeElement) {
            setInterval(() => {
                const now = new Date();
                timeElement.textContent = now.toLocaleTimeString('es-PE', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true });
            }, 1000);
        }

        const form = document.getElementById('formTemaDesarrollado');
        if (form) form.addEventListener('submit', handleFormSubmit);

        const textarea = document.getElementById('tema_desarrollado');
        if (textarea) {
            textarea.addEventListener('input', actualizarContador);
            actualizarContador();
        }
    });

    function actualizarContador() {
        const textarea = document.getElementById('tema_desarrollado');
        const contador = document.getElementById('contador');
        const actual = textarea.value.length;
        const max = 1000;
        contador.textContent = `${actual}/${max}`;
        if (actual < 10) contador.style.color = 'var(--danger-text)';
        else if (actual > max * 0.9) contador.style.color = 'var(--warning-text)';
        else contador.style.color = 'var(--success-text)';
    }

    function abrirModalTema(horarioId, temaExistente = '') {
        const modalElement = document.getElementById('modalTemaDesarrollado');
        const modal = bootstrap.Modal.getOrCreateInstance(modalElement);
        document.getElementById('horario_id').value = horarioId;
        document.getElementById('tema_desarrollado').value = temaExistente;

        // AÑADIDO: Asegurarse de que el input oculto de la fecha tenga la fecha actual del calendario
        const fechaAgendaInput = document.getElementById('fecha-agenda');
        if (fechaAgendaInput) {
            document.getElementById('fecha_seleccionada_input_oculto').value = fechaAgendaInput.value;
        }

        const titulo = document.getElementById('modalTemaDesarrolladoLabel');
        const btnGuardar = document.getElementById('btnGuardarTema').querySelector('span');
        if (temaExistente) {
            titulo.innerHTML = '<i class="mdi mdi-pencil-circle-outline me-2"></i>Editar Tema Desarrollado';
            btnGuardar.textContent = 'Actualizar Tema';
        } else {
            titulo.innerHTML = '<i class="mdi mdi-clipboard-edit-outline me-2"></i>Registrar Tema Desarrollado';
            btnGuardar.textContent = 'Guardar Tema';
        }
        document.getElementById('alertContainer').innerHTML = '';
        actualizarContador();
        modal.show();
    }

    function handleFormSubmit(e) {
        e.preventDefault();
        const btnGuardar = document.getElementById('btnGuardarTema');
        const btnText = btnGuardar.querySelector('span');
        const originalText = btnText.textContent;
        if (document.getElementById('tema_desarrollado').value.trim().length < 10) {
            mostrarAlertEnModal('danger', 'El tema debe tener al menos 10 caracteres.');
            return;
        }
        btnGuardar.disabled = true;
        btnText.textContent = 'Guardando...';
        btnGuardar.insertAdjacentHTML('afterbegin', '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>');
        document.getElementById('alertContainer').innerHTML = '';
        
        const formData = new FormData(e.target);
        // AÑADIDO: Obtener la fecha seleccionada del input oculto
        const fechaSeleccionada = document.getElementById('fecha_seleccionada_input_oculto').value;
        // AÑADIDO: Agregar la fecha seleccionada al FormData antes de enviarlo
        formData.append('fecha_seleccionada', fechaSeleccionada);

        fetch('{{ route("docente.tema-guardar") }}', {
            method: 'POST',
            body: formData,
            headers: { 
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 
                'Accept': 'application/json' 
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                mostrarAlertEnModal('success', data.message);
                setTimeout(() => {
                    const modalInstance = bootstrap.Modal.getInstance(document.getElementById('modalTemaDesarrollado'));
                    if (modalInstance) {
                        modalInstance.hide();
                    }
                    // CORRECCIÓN CLAVE: Recargar la página con la fecha seleccionada
                    const currentUrl = new URL(window.location.href);
                    currentUrl.searchParams.set('fecha', fechaSeleccionada); // Establece o actualiza el parámetro 'fecha'
                    window.location.href = currentUrl.toString(); // Recarga la página con la URL correcta
                }, 1500);
            } else {
                let mensaje = data.message || 'Ocurrió un error.';
                if (data.errors) {
                    mensaje += '<ul class="mt-2 mb-0 ps-3">';
                    Object.values(data.errors).flat().forEach(error => mensaje += `<li>${error}</li>`);
                    mensaje += '</ul>';
                }
                mostrarAlertEnModal('danger', mensaje);
                resetButton();
            }
        })
        .catch(error => {
            console.error('Error:', error);
            mostrarAlertEnModal('danger', 'Error de conexión. Por favor, inténtalo de nuevo.');
            resetButton();
        });

        function resetButton() {
            btnGuardar.disabled = false;
            btnText.textContent = originalText;
            const spinner = btnGuardar.querySelector('.spinner-border');
            if(spinner) spinner.remove();
        }
    }

    function mostrarAlertEnModal(tipo, mensaje) {
        const alertContainer = document.getElementById('alertContainer');
        const alertClass = `alert-dismissible alert alert-${tipo} d-flex align-items-center`;
        const icon = tipo === 'success' ? 'mdi-check-circle' : 'mdi-alert-circle';
        alertContainer.innerHTML = `<div class="${alertClass}" role="alert"><i class="mdi ${icon} me-2 fs-5"></i><div>${mensaje}</div><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>`;
        document.querySelector('.modal-body').scrollTop = 0;
    }
</script>
@endpush
