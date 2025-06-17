@extends('layouts.app')

@section('title', 'Dashboard Docente')

@push('css')
<style>
    .dashboard-card {
        border-radius: 12px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        transition: transform 0.2s ease;
    }
    .dashboard-card:hover {
        transform: translateY(-2px);
    }
    .stat-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 20px;
    }
    .stat-card.warning {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    }
    .stat-card.success {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    }
    .stat-card.info {
        background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
    }
    .session-card {
        border-left: 4px solid #007bff;
        transition: all 0.3s ease;
    }
    .session-card.completed {
        border-left-color: #28a745;
        background-color: #f8fff9;
    }
    .session-card.pending {
        border-left-color: #ffc107;
        background-color: #fffdf5;
    }
    .session-card.programmed {
        border-left-color: #6c757d;
        background-color: #f8f9fa;
    }
    .welcome-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 12px;
        padding: 30px;
        margin-bottom: 30px;
    }
    .recordatorio {
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 15px;
        border-left: 4px solid;
    }
    .recordatorio.warning {
        background-color: #fff3cd;
        border-left-color: #ffc107;
        color: #856404;
    }
    .recordatorio.info {
        background-color: #d1ecf1;
        border-left-color: #17a2b8;
        color: #0c5460;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header de Bienvenida -->
    <div class="welcome-header">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2 class="mb-1">¡Bienvenida, {{ $user->nombre }} {{ $user->apellido_paterno }}!</h2>
                <p class="mb-0">
                    <i class="mdi mdi-calendar-today me-2"></i>
                    Hoy es {{ \Carbon\Carbon::now()->locale('es')->isoFormat('dddd, D [de] MMMM [de] YYYY') }}
                </p>
            </div>
            <div class="col-md-4 text-end">
                <div class="h4 mb-0">
                    <i class="mdi mdi-clock-outline me-2"></i>
                    <span id="current-time">{{ \Carbon\Carbon::now()->format('H:i A') }}</span>
                </div>
                <small>Última actualización</small>
            </div>
        </div>
    </div>

    <!-- Tarjetas de Estadísticas -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="stat-card">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h3 class="mb-0">{{ $sesionesHoy }}</h3>
                        <p class="mb-0">Sesiones Hoy</p>
                    </div>
                    <div class="flex-shrink-0">
                        <i class="mdi mdi-calendar-today" style="font-size: 2.5rem; opacity: 0.7;"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6">
            <div class="stat-card warning">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h3 class="mb-0">{{ $sesionesPendientes }}</h3>
                        <p class="mb-0">Pendientes</p>
                    </div>
                    <div class="flex-shrink-0">
                        <i class="mdi mdi-alert-circle-outline" style="font-size: 2.5rem; opacity: 0.7;"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6">
            <div class="stat-card success">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h3 class="mb-0">{{ $horasHoy }}</h3>
                        <p class="mb-0">Horas Hoy</p>
                    </div>
                    <div class="flex-shrink-0">
                        <i class="mdi mdi-clock-outline" style="font-size: 2.5rem; opacity: 0.7;"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-3 col-md-6">
            <div class="stat-card info">
                <div class="d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h3 class="mb-0">S/. {{ number_format($pagoEstimadoHoy, 2) }}</h3>
                        <p class="mb-0">Pago Estimado</p>
                    </div>
                    <div class="flex-shrink-0">
                        <i class="mdi mdi-currency-usd" style="font-size: 2.5rem; opacity: 0.7;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Mis Sesiones de Hoy -->
        <div class="col-lg-8">
            <div class="card dashboard-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="mdi mdi-calendar-today me-2"></i>
                        Mis Sesiones de Hoy
                    </h5>
                    <div>
                        <button class="btn btn-outline-primary btn-sm me-2">
                            <i class="mdi mdi-filter-variant"></i> Filtros
                        </button>
                        <button class="btn btn-primary btn-sm">
                            <i class="mdi mdi-plus"></i> Registro Manual
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    @if($horariosHoy->count() > 0)
                        @foreach($horariosHoy as $horario)
                            @php
                                $asistencia = $asistenciasHoy->where('horario_id', $horario->id)->first();
                                $horaInicio = \Carbon\Carbon::parse($horario->hora_inicio);
                                $horaFin = \Carbon\Carbon::parse($horario->hora_fin);
                                $ahora = \Carbon\Carbon::now();
                                
                                if ($asistencia) {
                                    $estado = 'completed';
                                    $estadoTexto = 'COMPLETADA';
                                    $estadoColor = 'success';
                                } elseif ($ahora->greaterThan($horaInicio)) {
                                    $estado = 'pending';
                                    $estadoTexto = 'PENDIENTE';
                                    $estadoColor = 'warning';
                                } else {
                                    $estado = 'programmed';
                                    $estadoTexto = 'PROGRAMADA';
                                    $estadoColor = 'secondary';
                                }
                            @endphp
                            
                            <div class="session-card {{ $estado }} card mb-3">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col-md-2">
                                            <div class="text-center">
                                                <div class="h5 mb-0 text-primary">
                                                    <i class="mdi mdi-clock-outline"></i>
                                                    {{ $horaInicio->format('H:i') }} AM
                                                </div>
                                                <small class="text-muted">
                                                    <i class="mdi mdi-arrow-right"></i>
                                                    {{ $horaFin->format('H:i') }} AM
                                                </small>
                                                @if($asistencia)
                                                    <div class="mt-2">
                                                        <small class="text-muted">Registros biométricos</small>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-4">
                                            <div class="d-flex align-items-center mb-2">
                                                <i class="mdi mdi-clock-outline me-2 text-primary"></i>
                                                <span class="badge bg-primary">{{ $horaFin->diffInHours($horaInicio) }}.0 hrs</span>
                                            </div>
                                            @if($asistencia)
                                                <div class="small text-success">
                                                    S/. {{ number_format($asistencia->monto_total ?? 0, 2) }}
                                                </div>
                                            @else
                                                <div class="small text-muted">
                                                    S/. {{ number_format(80.00, 2) }}
                                                </div>
                                            @endif
                                        </div>
                                        
                                        <div class="col-md-4">
                                            <h6 class="mb-1">{{ $horario->curso->nombre ?? 'Sin curso' }} - {{ $horario->aula->nombre ?? 'Sin aula' }}</h6>
                                            @if($horario->aula)
                                                <small class="text-muted">
                                                    <i class="mdi mdi-map-marker"></i> {{ $horario->aula->nombre }}
                                                </small>
                                            @endif
                                            @if($asistencia && $asistencia->tema_desarrollado)
                                                <div class="mt-1">
                                                    <small class="text-primary">
                                                        <i class="mdi mdi-book-open-variant"></i> {{ $asistencia->tema_desarrollado }}
                                                    </small>
                                                </div>
                                            @endif
                                            <div class="mt-1">
                                                <small class="text-muted">
                                                    @if($asistencia)
                                                        Sesión teórica completada
                                                    @else
                                                        Turno {{ $horario->turno ?? 'No definido' }}
                                                    @endif
                                                </small>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-2 text-end">
                                            <span class="badge bg-{{ $estadoColor }} mb-2">
                                                <i class="mdi mdi-check-circle"></i> {{ $estadoTexto }}
                                            </span>
                                            <div class="btn-group-vertical d-grid gap-1">
                                                @if($asistencia)
                                                    <button class="btn btn-outline-success btn-sm">
                                                        <i class="mdi mdi-eye"></i> Detalles
                                                    </button>
                                                    <button class="btn btn-outline-primary btn-sm">
                                                        <i class="mdi mdi-file-document"></i> Reporte
                                                    </button>
                                                @else
                                                    @if($estado == 'pending')
                                                        <button class="btn btn-success btn-sm">
                                                            <i class="mdi mdi-check"></i> Completar
                                                        </button>
                                                    @else
                                                        <button class="btn btn-outline-secondary btn-sm" disabled>
                                                            <i class="mdi mdi-clock"></i> Esperando
                                                        </button>
                                                    @endif
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center py-4">
                            <i class="mdi mdi-calendar-remove" style="font-size: 3rem; color: #dee2e6;"></i>
                            <h5 class="mt-3 text-muted">No tienes sesiones programadas para hoy</h5>
                            <p class="text-muted">Disfruta tu día libre o revisa tu horario semanal</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar Derecho -->
        <div class="col-lg-4">
            <!-- Recordatorios -->
            @if(count($recordatorios) > 0)
                <div class="card dashboard-card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="mdi mdi-bell-outline me-2"></i>
                            Recordatorios
                        </h6>
                    </div>
                    <div class="card-body">
                        @foreach($recordatorios as $recordatorio)
                            <div class="recordatorio {{ $recordatorio['tipo'] }}">
                                <i class="mdi mdi-alert-circle me-2"></i>
                                {{ $recordatorio['mensaje'] }}
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Próxima Clase -->
            @if($proximaClase)
                <div class="card dashboard-card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="mdi mdi-clock-outline me-2"></i>
                            Próxima Clase
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <h6 class="mb-1">{{ $proximaClase->curso->nombre ?? 'Sin curso' }}</h6>
                                <p class="mb-1 text-muted">
                                    <i class="mdi mdi-map-marker me-1"></i>
                                    {{ $proximaClase->aula->nombre ?? 'Sin aula' }}
                                </p>
                                <small class="text-primary">
                                    {{ ucfirst($proximaClase->dia_semana) }} - 
                                    {{ \Carbon\Carbon::parse($proximaClase->hora_inicio)->format('H:i') }}
                                </small>
                            </div>
                            <div class="flex-shrink-0">
                                <span class="badge bg-info">
                                    En {{ \Carbon\Carbon::now()->diffInHours(\Carbon\Carbon::parse($proximaClase->hora_inicio)) }} horas
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Resumen Semanal -->
            <div class="card dashboard-card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="mdi mdi-chart-line me-2"></i>
                        Resumen Semanal ({{ \Carbon\Carbon::now()->subDays(6)->format('d/m') }} - {{ \Carbon\Carbon::now()->format('d/m') }})
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-6 mb-3">
                            <div class="h4 text-primary mb-0">{{ $resumenSemanal['sesiones'] }}</div>
                            <small class="text-muted">Sesiones</small>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="h4 text-success mb-0">{{ $resumenSemanal['horas'] }}</div>
                            <small class="text-muted">Horas</small>
                        </div>
                        <div class="col-6">
                            <div class="h4 text-info mb-0">S/. {{ number_format($resumenSemanal['ingresos'], 0) }}</div>
                            <small class="text-muted">Ingresos</small>
                        </div>
                        <div class="col-6">
                            <div class="h4 text-warning mb-0">{{ $resumenSemanal['asistencia'] }}%</div>
                            <small class="text-muted">Asistencia</small>
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

    // Efectos de hover para las tarjetas de sesión
    document.querySelectorAll('.session-card').forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateX(5px)';
            this.style.boxShadow = '0 4px 15px rgba(0,0,0,0.1)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateX(0)';
            this.style.boxShadow = '';
        });
    });
</script>
@endpush
