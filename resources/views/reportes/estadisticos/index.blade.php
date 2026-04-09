@extends('layouts.app')

@section('title', 'Inteligencia de Datos')

@section('content')
<div class="container-fluid">
    <!-- BARRA DE CONTROL PREMIUM -->
    <div class="row mb-3 align-items-center mt-3 bg-white p-3 rounded-4 shadow-sm mx-0 border-start border-primary border-4">
        <div class="col-md-5">
            <h4 class="fw-bold mb-0 text-dark"><i class="mdi mdi-shield-search text-primary me-2"></i>Cerebro de Inteligencia Total</h4>
            <p class="text-muted small mb-0">Gestión 360: Sincronizada con Lógica Institucional</p>
        </div>
        <div class="col-md-7">
            <form action="{{ route('reportes.estadisticos.index') }}" method="GET" class="row g-2 justify-content-md-end align-items-center">
                <div class="col-auto">
                    <div class="input-group shadow-sm rounded-pill overflow-hidden border">
                        <span class="input-group-text bg-light border-0 px-3"><i class="mdi mdi-calendar-range text-primary"></i></span>
                        <select name="ciclo_id" class="form-select border-0 px-3 fw-semibold" onchange="this.form.submit()" style="min-width: 300px; height: 45px; background-color: #f8f9fa;">
                            <option value="global" {{ $ciclo_id == 'global' ? 'selected' : '' }}>🌎 Métrica Consolidada Global</option>
                            @foreach($ciclos as $c)
                                <option value="{{ $c->id }}" {{ $ciclo_id == $c->id ? 'selected' : '' }}>
                                    {{ $c->es_activo ? '🔥 ACTIVO: ' : '📋 ' }}{{ $c->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- KPI ROW -->
    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-3 mb-4">
        <div class="col">
            <div class="card kpi-card bg-primary text-white h-100 shadow-sm border-0">
                <div class="card-body py-3">
                    <h6 class="text-white-50 small mb-1">Total Estudiantes</h6>
                    <h2 class="mb-0 fw-bold">{{ number_format($inhabilitadosStats['total_activos']) }}</h2>
                    <i class="mdi mdi-account-group position-absolute end-0 bottom-0 mb-3 me-3 fs-1 opacity-25"></i>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card kpi-card bg-info text-white h-100 shadow-sm border-0">
                <div class="card-body py-3">
                    <h6 class="text-white-50 small mb-1">Docentes Activos</h6>
                    <h2 class="mb-0 fw-bold">{{ $docentesStats['docentes'] }}</h2>
                    <i class="mdi mdi-human-male-board position-absolute end-0 bottom-0 mb-3 me-3 fs-1 opacity-25"></i>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card kpi-card bg-success text-white h-100 shadow-sm border-0">
                <div class="card-body py-3">
                    <h6 class="text-white-50 small mb-1">Recaudación Total</h6>
                    <h2 class="mb-0 fw-bold">S/ {{ number_format($finanzasStats['total_recaudado'], 2) }}</h2>
                    <i class="mdi mdi-cash position-absolute end-0 bottom-0 mb-3 me-3 fs-1 opacity-25"></i>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card kpi-card bg-warning text-white h-100 shadow-sm border-0">
                <div class="card-body py-3">
                    <h6 class="text-white-50 small mb-1">Trámites Documentarios</h6>
                    <h2 class="mb-0 fw-bold">{{ $documentosStats['carnets']->sum('total') }}</h2>
                    <i class="mdi mdi-card-account-details position-absolute end-0 bottom-0 mb-3 me-3 fs-1 opacity-25"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- MAIN GRAPHS -->
    <div class="row g-4">
        <div class="col-xl-6">
            <div class="card analytics-card shadow border-0 h-100">
                <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 fw-bold text-dark"><i class="mdi mdi-chart-bar text-primary me-2"></i>Distribución por Carreras</h5>
                    <span class="badge bg-soft-primary text-primary px-3 rounded-pill">Top Popularidad</span>
                </div>
                <div class="card-body">
                    <div style="height: 320px;"><canvas id="popularityChart"></canvas></div>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card analytics-card shadow border-0 h-100">
                <div class="card-header bg-white py-3 border-0">
                    <h5 class="card-title mb-0 fw-bold text-dark"><i class="mdi mdi-door-open text-info me-2"></i>Ocupación de Aulas</h5>
                </div>
                <div class="card-body">
                    <div style="height: 320px;"><canvas id="aulasBarChart"></canvas></div>
                </div>
            </div>
        </div>
    </div>

    <!-- SECOND ROW GRAPHS -->
    <div class="row g-4 mt-1">
        <div class="col-xl-3">
            <div class="card analytics-card shadow border-0 h-100">
                <div class="card-header bg-white py-3 border-0 text-center"><h6 class="fw-bold">Turnos y Horarios</h6></div>
                <div class="card-body"><div style="height: 250px;"><canvas id="turnosBarChart"></canvas></div></div>
            </div>
        </div>
        <div class="col-xl-3">
            <div class="card analytics-card shadow border-0 h-100">
                <div class="card-header bg-white py-3 border-0 text-center"><h6 class="fw-bold">Pirámide de Edades</h6></div>
                <div class="card-body"><div style="height: 250px;"><canvas id="ageBarChart"></canvas></div></div>
            </div>
        </div>
        <div class="col-xl-3">
            <div class="card analytics-card shadow border-0 h-100">
                <div class="card-header bg-white py-3 border-0 text-center"><h6 class="fw-bold">Tipo de Inscripción</h6></div>
                <div class="card-body"><div style="height: 250px;"><canvas id="typePieChart"></canvas></div></div>
            </div>
        </div>
        <div class="col-xl-3">
            <div class="card analytics-card shadow border-0 h-100">
                <div class="card-header bg-white py-3 border-0 text-center text-danger"><h6 class="fw-bold">Situación de Alumnos (Riesgo)</h6></div>
                <div class="card-body">
                    <div class="px-2">
                        <div class="mb-2">
                            <div class="d-flex justify-content-between small mb-1"><span class="text-success fw-bold">Regul.</span><span class="badge bg-light text-success">{{ $inhabilitadosStats['regulares'] }}</span></div>
                            <div class="progress" style="height: 6px;"><div class="progress-bar bg-success" style="width: {{ $inhabilitadosStats['total_activos'] > 0 ? ($inhabilitadosStats['regulares']/$inhabilitadosStats['total_activos'])*100 : 0 }}%"></div></div>
                        </div>
                        <div class="mb-2">
                            <div class="d-flex justify-content-between small mb-1"><span class="text-warning fw-bold">Amonest.</span><span class="badge bg-light text-warning">{{ $inhabilitadosStats['amonestados'] }}</span></div>
                            <div class="progress" style="height: 6px;"><div class="progress-bar bg-warning" style="width: {{ $inhabilitadosStats['total_activos'] > 0 ? ($inhabilitadosStats['amonestados']/$inhabilitadosStats['total_activos'])*100 : 0 }}%"></div></div>
                        </div>
                        <div class="mb-2">
                            <div class="d-flex justify-content-between small mb-1"><span class="text-danger fw-bold">Inhabilit.</span><span class="badge bg-light text-danger">{{ $inhabilitadosStats['inhabilitados'] }}</span></div>
                            <div class="progress" style="height: 6px;"><div class="progress-bar bg-danger" style="width: {{ $inhabilitadosStats['total_activos'] > 0 ? ($inhabilitadosStats['inhabilitados']/$inhabilitadosStats['total_activos'])*100 : 0 }}%"></div></div>
                        </div>
                        <div class="text-center mt-3"><div style="height: 90px;"><canvas id="riskPieChart"></canvas></div></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- BIOMETRIC FLOW -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card analytics-card shadow border-0">
                <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 fw-bold"><i class="mdi mdi-fingerprint text-success me-2"></i>Análisis de Afluencia Biométrica (Ingresos)</h5>
                    <span class="badge bg-light text-dark fw-normal border shadow-sm">{{ $asistenciaStats['rango'] }}</span>
                </div>
                <div class="card-body">
                    <div style="height: 200px;"><canvas id="attendanceBarChart"></canvas></div>
                </div>
            </div>
        </div>
    </div>

    <!-- TABLES SECTION -->
    <div class="row mt-4 mb-5 g-4">
        <div class="col-xl-4 col-lg-6">
            <div class="card shadow border-0 rounded-4 h-100">
                <div class="card-header bg-primary text-white rounded-top-4 py-3"><h6 class="mb-0 fw-bold"><i class="mdi mdi-school me-2"></i>Top 10 Colegios de Origen</h6></div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-sm mb-0">
                            <thead class="bg-light small"><tr><th class="ps-3 py-2">Institución Educativa</th><th class="text-end pe-3 py-2">Total</th></tr></thead>
                            <tbody class="small">
                                @forelse($procedenciaStats['colegios'] as $col)
                                <tr><td class="ps-3 py-2">{{ $col->nombre }}</td><td class="text-end pe-3 py-2 fw-bold text-primary">{{ $col->total }}</td></tr>
                                @empty
                                <tr><td colspan="2" class="text-center py-4 text-muted">No hay datos registrados</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-lg-6">
            <div class="card shadow border-0 rounded-4 h-100">
                <div class="card-header bg-info text-white rounded-top-4 py-3"><h6 class="mb-0 fw-bold"><i class="mdi mdi-map-marker me-2"></i>Alumnos por Provincia</h6></div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-sm mb-0">
                            <thead class="bg-light small"><tr><th class="ps-3 py-2">Provincia</th><th class="text-end pe-3 py-2">Total</th></tr></thead>
                            <tbody class="small">
                                @forelse($procedenciaStats['ciudades'] as $cty)
                                <tr><td class="ps-3 py-2">{{ $cty->ciudad }}</td><td class="text-end pe-3 py-2 fw-bold text-info">{{ $cty->total }}</td></tr>
                                @empty
                                <tr><td colspan="2" class="text-center py-4 text-muted">No hay datos registrados</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-lg-12">
            <div class="card shadow border-0 rounded-4 h-100">
                <div class="card-header bg-success text-white rounded-top-4 py-3"><h6 class="mb-0 fw-bold"><i class="mdi mdi-currency-usd me-2"></i>Ingresos por Programa Académico</h6></div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-sm mb-0">
                            <thead class="bg-light small"><tr><th class="ps-3 py-2">Carrera</th><th class="text-end pe-3 py-2">Recaudado (S/)</th></tr></thead>
                            <tbody class="small">
                                @forelse($finanzasStats['por_carrera'] as $fin)
                                <tr><td class="ps-3 py-2">{{ $fin['nombre'] }}</td><td class="text-end pe-3 py-2 fw-bold text-success">S/ {{ number_format($fin['total'], 2) }}</td></tr>
                                @empty
                                <tr><td colspan="2" class="text-center py-4 text-muted">No hay ingresos registrados</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('css')
<style>
    .kpi-card { border-radius: 15px; overflow: hidden; }
    .analytics-card { border-radius: 15px; }
    .bg-soft-primary { background-color: rgba(79, 70, 229, 0.1); }
    .table-responsive { max-height: 380px; }
</style>
@endpush

@push('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const isDark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
        const cText = isDark ? '#adb5bd' : '#475569';
        const cGrid = isDark ? 'rgba(255,255,255,0.05)' : 'rgba(0,0,0,0.08)';
        Chart.defaults.font.family = "'Inter', sans-serif";
        Chart.defaults.color = cText;

        // POPULARIDAD
        new Chart(document.getElementById('popularityChart'), {
            type: 'bar',
            data: { 
                labels: {!! json_encode($carrerasStats->pluck('nombre')) !!}, 
                datasets: [{ 
                    label: 'Postulantes', 
                    data: {!! json_encode($carrerasStats->pluck('postulaciones_count')) !!}, 
                    backgroundColor: '#4f46e5', 
                    borderRadius: 6 
                }] 
            },
            options: { 
                indexAxis: 'y', 
                maintainAspectRatio: false, 
                plugins: { legend: { display: false } },
                scales: { x: { grid: { color: cGrid } }, y: { grid: { display: false } } }
            }
        });

        // AULAS
        new Chart(document.getElementById('aulasBarChart'), {
            type: 'bar',
            data: { 
                labels: {!! json_encode($aulasStats->pluck('aula')) !!}, 
                datasets: [{ 
                    data: {!! json_encode($aulasStats->pluck('total')) !!}, 
                    backgroundColor: '#0ea5e9', 
                    borderRadius: 4 
                }] 
            },
            options: { 
                maintainAspectRatio: false, 
                plugins: { legend: { display: false } },
                scales: { x: { grid: { display: false } }, y: { grid: { color: cGrid } } }
            }
        });

        // TURNOS
        new Chart(document.getElementById('turnosBarChart'), {
            type: 'bar',
            data: { 
                labels: {!! json_encode($turnosStats->pluck('turno')) !!}, 
                datasets: [{ 
                    data: {!! json_encode($turnosStats->pluck('total')) !!}, 
                    backgroundColor: '#10b981', 
                    borderRadius: 4 
                }] 
            },
            options: { maintainAspectRatio: false, plugins: { legend: { display: false } } }
        });

        // EDADES
        new Chart(document.getElementById('ageBarChart'), {
            type: 'bar',
            data: { 
                labels: {!! json_encode($edades->keys()) !!}, 
                datasets: [{ 
                    data: {!! json_encode($edades->values()) !!}, 
                    backgroundColor: '#818cf8', 
                    borderRadius: 4 
                }] 
            },
            options: { maintainAspectRatio: false, plugins: { legend: { display: false } } }
        });

        // TIPO INSCRIPCIÓN
        new Chart(document.getElementById('typePieChart'), {
            type: 'doughnut',
            data: { 
                labels: {!! json_encode($tipoInscripcionStats->pluck('tipo_inscripcion')) !!}, 
                datasets: [{ 
                    data: {!! json_encode($tipoInscripcionStats->pluck('total')) !!}, 
                    backgroundColor: ['#6366f1', '#f43f5e', '#facc15', '#10b981'] 
                }] 
            },
            options: { maintainAspectRatio: false, cutout: '65%', plugins: { legend: { position: 'bottom' } } }
        });

        // RIESGO PIE (3 ESTADOS)
        new Chart(document.getElementById('riskPieChart'), {
            type: 'pie',
            data: { 
                labels: ['Regular', 'Amonestado', 'Inhabilitado'], 
                datasets: [{ 
                    data: [
                        {{ $inhabilitadosStats['regulares'] }}, 
                        {{ $inhabilitadosStats['amonestados'] }}, 
                        {{ $inhabilitadosStats['inhabilitados'] }}
                    ], 
                    backgroundColor: ['#10b981', '#f59e0b', '#ef4444'] 
                }] 
            },
            options: { maintainAspectRatio: false, plugins: { legend: { display: false } } }
        });

        // ASISTENCIA FLOW
        new Chart(document.getElementById('attendanceBarChart'), {
            type: 'bar',
            data: { 
                labels: {!! json_encode($asistenciaStats['heatmap']->pluck('hora')->map(fn($h)=>$h.':00')) !!}, 
                datasets: [{ 
                    data: {!! json_encode($asistenciaStats['heatmap']->pluck('total')) !!}, 
                    backgroundColor: 'rgba(79, 70, 229, 0.8)',
                    hoverBackgroundColor: '#4f46e5'
                }] 
            },
            options: { 
                maintainAspectRatio: false, 
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, grid: { color: cGrid } }, x: { grid: { display: false } } }
            }
        });
    });
</script>
@endpush
@endsection
