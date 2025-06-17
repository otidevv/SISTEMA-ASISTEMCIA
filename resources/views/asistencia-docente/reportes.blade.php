@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4><i class="fas fa-chart-bar"></i> Reportes de Asistencia Docente</h4>
        <div>
            <a href="{{ route('asistencia-docente.exportar') }}" class="btn btn-success">
                <i class="fas fa-download"></i> Exportar Datos
            </a>
            <a href="{{ route('asistencia-docente.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>

    {{-- Estadísticas generales --}}
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Registros Hoy</h6>
                            <h3>{{ $totalHoy }}</h3>
                            <small>{{ now()->format('d/m/Y') }}</small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-calendar-day fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Esta Semana</h6>
                            <h3>{{ $totalSemana }}</h3>
                            <small>{{ now()->startOfWeek()->format('d/m') }} - {{ now()->endOfWeek()->format('d/m') }}</small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-calendar-week fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Este Mes</h6>
                            <h3>{{ $totalMes }}</h3>
                            <small>{{ now()->format('F Y') }}</small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-calendar-alt fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="card-title">Docentes Activos</h6>
                            <h3>{{ $asistenciaPorDocente->count() }}</h3>
                            <small>Este mes</small>
                        </div>
                        <div class="align-self-center">
                            <i class="fas fa-users fa-2x"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Gráfico de asistencia semanal --}}
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-chart-line"></i> Asistencia por Día - Semana Actual</h5>
                </div>
                <div class="card-body">
                    <canvas id="graficoSemanal" height="100"></canvas>
                </div>
            </div>
        </div>

        {{-- Top docentes --}}
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-trophy"></i> Top Docentes - Este Mes</h5>
                </div>
                <div class="card-body">
                    @forelse($asistenciaPorDocente->sortByDesc('total_asistencias')->take(5) as $index => $docente)
                        <div class="d-flex align-items-center mb-3">
                            <div class="me-3">
                                <span class="badge bg-{{ $index == 0 ? 'warning' : ($index == 1 ? 'secondary' : ($index == 2 ? 'dark' : 'primary')) }} rounded-pill">
                                    {{ $index + 1 }}
                                </span>
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-bold">
                                    {{ $docente->docente ? $docente->docente->nombre . ' ' . $docente->docente->apellido_paterno : 'N/A' }}
                                </div>
                                <small class="text-muted">
                                    {{ $docente->total_asistencias }} registros
                                    @if($docente->total_horas)
                                        • {{ number_format($docente->total_horas, 1) }}h
                                    @endif
                                </small>
                            </div>
                            <div>
                                @if($docente->total_pagos)
                                    <span class="badge bg-success">S/ {{ number_format($docente->total_pagos, 2) }}</span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-3">
                            <i class="fas fa-users fa-2x text-muted mb-2"></i>
                            <p class="text-muted mb-0">No hay datos de docentes para este mes</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Tabla detallada por docente --}}
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-table"></i> Resumen Detallado por Docente - {{ now()->format('F Y') }}</h5>
                </div>
                <div class="card-body">
                    @if($asistenciaPorDocente->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Docente</th>
                                        <th>Documento</th>
                                        <th>Total Registros</th>
                                        <th>Entradas</th>
                                        <th>Salidas</th>
                                        <th>Horas Dictadas</th>
                                        <th>Monto Total</th>
                                        <th>Promedio Diario</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($asistenciaPorDocente->sortByDesc('total_asistencias') as $index => $docente)
                                        @php
                                            $entradas = \App\Models\AsistenciaDocente::where('docente_id', $docente->docente_id)
                                                ->where('estado', 'entrada')
                                                ->whereMonth('fecha_hora', now()->month)
                                                ->count();
                                            $salidas = \App\Models\AsistenciaDocente::where('docente_id', $docente->docente_id)
                                                ->where('estado', 'salida')
                                                ->whereMonth('fecha_hora', now()->month)
                                                ->count();
                                            $diasTrabajados = \App\Models\AsistenciaDocente::where('docente_id', $docente->docente_id)
                                                ->whereMonth('fecha_hora', now()->month)
                                                ->selectRaw('DATE(fecha_hora) as fecha')
                                                ->distinct()
                                                ->count();
                                            $promedioDiario = $diasTrabajados > 0 ? $docente->total_asistencias / $diasTrabajados : 0;
                                        @endphp
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    @if($docente->docente && $docente->docente->foto_perfil)
                                                        <img src="{{ asset('storage/' . $docente->docente->foto_perfil) }}" 
                                                             class="rounded-circle me-2" width="32" height="32" alt="Foto">
                                                    @else
                                                        <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center me-2" 
                                                             style="width: 32px; height: 32px; color: white; font-size: 12px;">
                                                            {{ $docente->docente ? strtoupper(substr($docente->docente->nombre, 0, 1)) : 'N/A' }}
                                                        </div>
                                                    @endif
                                                    <div>
                                                        <div class="fw-bold">
                                                            {{ $docente->docente ? $docente->docente->nombre . ' ' . $docente->docente->apellido_paterno : 'N/A' }}
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>{{ $docente->docente->numero_documento ?? 'N/A' }}</td>
                                            <td>
                                                <span class="badge bg-primary fs-6">{{ $docente->total_asistencias }}</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-success">{{ $entradas }}</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary">{{ $salidas }}</span>
                                            </td>
                                            <td>
                                                @if($docente->total_horas)
                                                    <span class="badge bg-info">{{ number_format($docente->total_horas, 1) }}h</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($docente->total_pagos)
                                                    <span class="badge bg-success">S/ {{ number_format($docente->total_pagos, 2) }}</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                <small class="text-muted">{{ number_format($promedioDiario, 1) }}/día</small>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('asistencia-docente.index', ['documento' => $docente->docente->numero_documento ?? '']) }}" 
                                                       class="btn btn-sm btn-outline-primary" title="Ver registros">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    @can('asistencia-docente.export')
                                                        <a href="{{ route('asistencia-docente.exportar', ['docente_id' => $docente->docente_id]) }}" 
                                                           class="btn btn-sm btn-outline-success" title="Exportar">
                                                            <i class="fas fa-download"></i>
                                                        </a>
                                                    @endcan
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-chart-bar fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No hay datos para mostrar</h5>
                            <p class="text-muted">No se encontraron registros de asistencia docente para este mes.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Gráfico de asistencia semanal
        const ctx = document.getElementById('graficoSemanal').getContext('2d');
        
        const diasSemana = @json(array_keys($asistenciaSemana));
        const valoresSemana = @json(array_values($asistenciaSemana));
        
        // Convertir fechas a nombres de días
        const nombresDias = diasSemana.map(fecha => {
            const date = new Date(fecha + 'T00:00:00');
            return date.toLocaleDateString('es-ES', { weekday: 'short', day: 'numeric' });
        });

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: nombresDias,
                datasets: [{
                    label: 'Registros de Asistencia',
                    data: valoresSemana,
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    tension: 0.1,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            title: function(context) {
                                const index = context[0].dataIndex;
                                return diasSemana[index];
                            },
                            label: function(context) {
                                return `Registros: ${context.parsed.y}`;
                            }
                        }
                    }
                }
            }
        });

        // Auto-refresh cada 5 minutos
        setInterval(function() {
            location.reload();
        }, 300000);
    });
</script>
@endpush
