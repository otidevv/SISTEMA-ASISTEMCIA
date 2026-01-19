@extends('layouts.app')

@section('title', 'Mi Horario y Carga Horaria')

@push('css')
<style>
    .card-docente {
        border-radius: 15px;
        border: none;
        overflow: hidden;
    }
    .welcome-banner {
        background: linear-gradient(135deg, #4f32c2 0%, #7367f0 100%);
        color: white;
        padding: 30px;
        border-radius: 15px;
        margin-bottom: 25px;
        box-shadow: 0 4px 15px rgba(115, 103, 240, 0.3);
    }
    .stat-box {
        background: white;
        padding: 20px;
        border-radius: 12px;
        text-align: center;
        height: 100%;
        transition: transform 0.3s ease;
        border: 1px solid #eef2f7;
    }
    .stat-box:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    }
    .stat-icon {
        width: 50px;
        height: 50px;
        background: #f1f4ff;
        color: #7367f0;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 15px;
        font-size: 24px;
    }
    .table-horario {
        border-collapse: separate;
        border-spacing: 0 8px;
    }
    .table-horario tr {
        background-color: white;
        box-shadow: 0 2px 5px rgba(0,0,0,0.02);
    }
    .table-horario td {
        padding: 15px;
        border: none;
    }
    .table-horario td:first-child { border-radius: 10px 0 0 10px; }
    .table-horario td:last-child { border-radius: 0 10px 10px 0; }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Banner de Bienvenida -->
    <div class="welcome-banner d-flex align-items-center justify-content-between">
        <div>
            <h2 class="text-white mb-1">¡Hola, {{ $docente->nombre }}! 👋</h2>
            <p class="text-white-50 mb-0">Aquí puedes consultar tu carga horaria y pagos estimados para el ciclo {{ $cicloActivo->nombre }}.</p>
        </div>
        <div class="d-none d-md-block">
            <i class="mdi mdi-calendar-clock display-3 text-white-50"></i>
        </div>
    </div>

    <!-- Estadísticas Rápidas -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stat-box">
                <div class="stat-icon"><i class="mdi mdi-clock-check"></i></div>
                <h3 class="mb-1">{{ $data['horas_base_formateado'] }}</h3>
                <p class="text-muted mb-0">Horas Base (L-V)</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-box">
                <div class="stat-icon"><i class="mdi mdi-calendar-check"></i></div>
                <h3 class="mb-1">{{ $data['horas_totales_ciclo_formateado'] }}</h3>
                <p class="text-muted mb-0">Total Horas Ciclo</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-box">
                <div class="stat-icon"><i class="mdi mdi-cash-multiple"></i></div>
                <h3 class="mb-1">S/ {{ number_format($data['pago_semanal'], 2) }}</h3>
                <p class="text-muted mb-0">Pago Semanal Prom.</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-box">
                <div class="stat-icon"><i class="mdi mdi-download"></i></div>
                <div class="d-grid gap-2 mt-2">
                    <a href="{{ route('carga-horaria.pdf-visual', [$docente->id, $cicloActivo->id]) }}" class="btn btn-primary btn-sm">
                        <i class="mdi mdi-file-table me-1"></i> Mi Horario (Visual)
                    </a>
                    <a href="{{ route('carga-horaria.pdf-detallado', [$docente->id, $cicloActivo->id]) }}" class="btn btn-outline-primary btn-sm">
                        <i class="mdi mdi-file-document-outline me-1"></i> Reporte Detallado
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Detalle de Horario -->
        <div class="col-lg-8">
            <div class="card card-docente shadow-sm">
                <div class="card-header bg-white py-3">
                    <h4 class="header-title mb-0">Mi Horario de Clases</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-horario">
                            <thead>
                                <tr class="bg-light">
                                    <th>Asignatura</th>
                                    <th>Día</th>
                                    <th>Horario</th>
                                    <th>Aula</th>
                                    <th>Turno</th>
                                    <th class="text-end">Horas</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($data['horarios'] as $h)
                                <tr>
                                    <td>
                                        <span class="fw-bold d-block text-primary">{{ $h->es_receso ? 'RECESO' : $h->curso->nombre }}</span>
                                        <small class="text-muted">{{ !$h->es_receso ? $h->curso->codigo : '' }}</small>
                                    </td>
                                    <td><span class="badge bg-soft-primary text-primary px-2 py-1">{{ $h->dia_semana }}</span></td>
                                    <td><i class="mdi mdi-clock-outline me-1"></i> {{ substr($h->hora_inicio, 0, 5) }} - {{ substr($h->hora_fin, 0, 5) }}</td>
                                    <td><i class="mdi mdi-map-marker-outline me-1"></i> {{ $h->es_receso ? '---' : $h->aula->nombre }}</td>
                                    <td class="text-info fw-bold">{{ $h->turno }}</td>
                                    <td class="text-end fw-bold">
                                        @if($h->es_receso)
                                            ---
                                        @else
                                            {{ $h->horas_formateado }}
                                            @if($h->minutos_receso_sustraidos > 0)
                                                <div class="small fw-normal text-muted" style="font-size: 0.75rem;">(-{{ $h->minutos_receso_sustraidos }}m receso)</div>
                                            @endif
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5">
                                        <i class="mdi mdi-calendar-blank display-4 text-muted"></i>
                                        <p class="text-muted mt-2">No tienes horarios registrados para este ciclo.</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Información de Pago y Ciclo -->
        <div class="col-lg-4">
            <div class="card card-docente shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h4 class="header-title mb-0">Detalles del Ciclo</h4>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="text-muted small text-uppercase fw-bold">Ciclo Actual</label>
                        <p class="mb-0 fw-bold text-primary">{{ $cicloActivo->nombre }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small text-uppercase fw-bold">Periodo y Duración</label>
                        <p class="mb-0">{{ \Carbon\Carbon::parse($cicloActivo->fecha_inicio)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($cicloActivo->fecha_fin)->format('d/m/Y') }}</p>
                        <small class="text-muted">Total: {{ $data['semanas_ciclo'] }} semanas</small>
                    </div>
                    <div class="mb-3">
                        <label class="text-muted small text-uppercase fw-bold">Mi Tarifa por Hora</label>
                        <p class="mb-0 h4 text-success">S/ {{ number_format($data['tarifa_por_hora'], 2) }}</p>
                    </div>
                    <hr>
                    <div class="alert alert-info border-0 mb-0">
                        <i class="mdi mdi-information-outline me-1"></i>
                        Recuerda que los pagos se calculan mensualmente según la asistencia registrada.
                    </div>
                </div>
            </div>

            <!-- Botón de Soporte/Ayuda -->
            <div class="card card-docente bg-soft-warning border-warning shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar-sm bg-warning rounded-circle d-flex align-items-center justify-content-center me-3">
                            <i class="mdi mdi-help-circle text-white fs-20"></i>
                        </div>
                        <div>
                            <h5 class="mb-1 text-warning">¿Dudas con tu pago?</h5>
                            <p class="mb-0 small">Contacta con el área administrativa para cualquier consulta sobre tu carga horaria.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
