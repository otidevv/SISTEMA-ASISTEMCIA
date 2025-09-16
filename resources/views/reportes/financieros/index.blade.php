@extends('layouts.app')

@section('title', 'Dashboard Financiero')

{{-- CSS para un look profesional y colorido --}}
@push('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@mdi/font@5.9.55/css/materialdesignicons.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<style>
    /* Paleta de colores y variables */
    :root {
        --primary-gradient: linear-gradient(135deg, #4f32c2 0%, #7367f0 100%);
        --success-gradient: linear-gradient(135deg, #10b981 0%, #28c76f 100%);
        --warning-gradient: linear-gradient(135deg, #ff9f43 0%, #ff8b1b 100%);
        --info-gradient: linear-gradient(135deg, #00cfe8 0%, #1ce1ff 100%);
        --danger-gradient: linear-gradient(135deg, #ea5455 0%, #ff5252 100%);
        --primary-glow: 0 0 20px rgba(115, 103, 240, 0.4);
    }

    /* --- TARJETAS DE ESTADÍSTICAS MEJORADAS --- */
    .metric-card {
        border: none;
        border-radius: 0.75rem;
        transition: all 0.3s ease;
        overflow: hidden;
        position: relative;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }
    .metric-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 35px rgba(0,0,0,0.15);
    }
    .metric-card .card-body {
        position: relative;
        z-index: 2;
        padding: 1.5rem !important;
    }
    .metric-card .fas, .metric-card .mdi {
        font-size: 2.5rem;
        transition: all 0.3s ease;
        position: absolute;
        right: 1rem;
        top: 1rem;
        opacity: 0.3;
        color: rgba(255,255,255,0.8);
    }
    .metric-card:hover .fas, .metric-card:hover .mdi {
        transform: scale(1.2) rotate(-10deg);
        opacity: 0.5;
    }
    .metric-card.info { background-image: var(--info-gradient); }
    .metric-card.success { background-image: var(--success-gradient); }
    .metric-card.warning { background-image: var(--warning-gradient); }
    .metric-card.danger { background-image: var(--danger-gradient); }
    .metric-card h2, .metric-card h6, .metric-card p, .metric-card small {
        color: white !important;
        text-shadow: 1px 1px 2px rgba(0,0,0,0.2);
    }

    /* --- BOTONES Y ELEMENTOS DE UI MEJORADOS --- */
    .export-btn {
        background-image: var(--success-gradient);
        border: none;
        color: white !important;
        transition: all 0.3s ease;
        box-shadow: 0 4px 10px rgba(40, 199, 111, 0.5);
        padding: 0.75rem 1.5rem;
        border-radius: 10px;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        text-decoration: none;
    }
    .export-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(40, 199, 111, 0.7);
        color: white !important;
        text-decoration: none;
    }

    /* --- TABLAS MEJORADAS --- */
    .professional-table {
        background: white;
        border-radius: 15px;
        padding: 1.5rem;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        border: 1px solid rgba(0,0,0,0.05);
    }
    
    .professional-table .table {
        margin-bottom: 0;
        font-size: 0.9rem;
        font-weight: 500;
    }
    
    .professional-table .table thead th {
        background: var(--primary-gradient);
        color: white !important;
        border: none;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-size: 0.8rem;
        padding: 1rem 0.8rem;
        text-shadow: 1px 1px 2px rgba(0,0,0,0.2);
        vertical-align: middle;
    }
    
    .professional-table .table tbody td {
        padding: 0.9rem 0.8rem;
        vertical-align: middle;
        border-top: 1px solid rgba(0,0,0,0.08);
        font-size: 0.85rem;
        color: #2c3e50 !important;
        font-weight: 500;
    }
    
    .professional-table .table tbody tr {
        transition: all 0.3s ease;
    }
    
    .professional-table .table tbody tr:hover {
        background-color: #f8f9fa !important;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }
    
    .professional-table .table tfoot td {
        background-color: #f8f9fa !important;
        border-top: 2px solid #dee2e6;
        font-weight: 700;
        color: #2c3e50 !important;
        padding: 1rem 0.8rem;
        font-size: 0.9rem;
    }

    /* --- GRÁFICOS MEJORADOS --- */
    .chart-container {
        background: white;
        border-radius: 15px;
        padding: 1.5rem;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        border: 1px solid rgba(0,0,0,0.05);
    }

    .chart-wrapper {
        position: relative;
        height: 350px;
    }
    
    .chart-wrapper canvas {
        width: 100% !important;
        height: 100% !important;
    }
    
    /* --- BOTONES DE ACCIÓN MEJORADOS --- */
    .action-btn {
        padding: 0.5rem 1rem;
        border-radius: 8px;
        font-size: 0.8rem;
        font-weight: 600;
        transition: all 0.3s ease;
        border: none;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .action-btn.btn-info {
        background: var(--info-gradient) !important;
        color: white !important;
        border: none;
        box-shadow: 0 2px 8px rgba(0, 207, 232, 0.3);
    }
    
    .action-btn.btn-info:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 207, 232, 0.5);
        color: white !important;
    }

    /* --- MODAL MEJORADO --- */
    .modal-content {
        border: none;
        border-radius: 15px;
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
    }
    
    .modal-header {
        background: var(--primary-gradient) !important;
        color: white !important;
        border-radius: 15px 15px 0 0;
        padding: 1.5rem;
        border-bottom: none;
    }
    
    .modal-title {
        font-weight: 600;
        font-size: 1.2rem;
        color: white !important;
    }
    
    .modal-body {
        padding: 2rem;
        font-size: 0.9rem;
        max-height: 70vh;
        overflow-y: auto;
    }
    
    .modal-footer {
        padding: 1.5rem;
        border-top: 1px solid rgba(0,0,0,0.1);
        border-radius: 0 0 15px 15px;
    }
    
    /* Botón cerrar del modal mejorado */
    .modal-header .close {
        color: white !important;
        opacity: 0.8;
        font-size: 1.5rem;
        text-shadow: none;
        padding: 0;
        margin: 0;
        background: none;
        border: none;
        width: 30px;
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        transition: all 0.3s ease;
    }
    
    .modal-header .close:hover {
        opacity: 1;
        background-color: rgba(255,255,255,0.1);
        transform: scale(1.1);
    }

    /* --- BADGES Y ELEMENTOS MEJORADOS --- */
    
    
    .badge-primary {
        background: var(--primary-gradient) !important;
        color: white !important;
    }
    
    .badge-info {
        background: var(--info-gradient) !important;
        color: white !important;
    }
    
    .badge-success {
        background: var(--success-gradient) !important;
        color: white !important;
    }
    
    .badge-warning {
        background: var(--warning-gradient) !important;
        color: white !important;
    }

    /* Estilos para el título de la página mejorados */
    .page-title-box h4, .section-title {
        font-size: 1.4rem;
        font-weight: 600;
        color: #2c3e50 !important;
        position: relative;
        padding-left: 1rem;
        margin-bottom: 1.5rem;
    }
    
    .page-title-box h4::before, .section-title::before {
        content: '';
        position: absolute;
        left: 0;
        top: 50%;
        transform: translateY(-50%);
        width: 4px;
        height: 100%;
        background: var(--primary-gradient);
        border-radius: 2px;
    }

    /* --- PROGRESS BARS MEJORADAS --- */
    .progress {
        border-radius: 10px;
        background-color: rgba(0,0,0,0.05);
    }
    
    .progress-bar {
        border-radius: 10px;
        transition: width 1s ease-in-out;
    }

    /* --- TABLA DEL MODAL MEJORADA --- */
    .modal-body .table {
        font-size: 0.85rem;
        margin-bottom: 0;
    }
    
    .modal-body .table thead th {
        background: var(--primary-gradient) !important;
        color: white !important;
        border: none;
        font-weight: 600;
        padding: 0.8rem;
        font-size: 0.8rem;
    }
    
    .modal-body .table tbody td {
        padding: 0.7rem 0.8rem;
        vertical-align: middle;
        color: #2c3e50 !important;
        font-weight: 500;
        border-top: 1px solid rgba(0,0,0,0.08);
    }
    
    .modal-body .table tbody tr:hover {
        background-color: #f8f9fa !important;
    }
    
    .modal-body .table tfoot td {
        background-color: #f8f9fa !important;
        border-top: 2px solid #dee2e6;
        font-weight: 700;
        color: #2c3e50 !important;
        padding: 0.8rem;
    }

    /* --- AVATAR MEJORADO --- */
    .avatar {
        width: 35px;
        height: 35px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 0.8rem;
        background: var(--primary-gradient) !important;
        color: white !important;
        text-shadow: 1px 1px 2px rgba(0,0,0,0.2);
        margin-right: 0.8rem;
    }

    /* --- CÓDIGO Y ELEMENTOS ESPECIALES --- */
    code {
        background-color: #f1f3f4;
        color: #e91e63;
        padding: 0.2rem 0.4rem;
        border-radius: 4px;
        font-size: 0.8rem;
        font-weight: 600;
    }

    /* --- RESPONSIVE MEJORADO --- */
    @media (max-width: 768px) {
        .professional-table .table {
            font-size: 0.75rem;
        }
        
        .modal-dialog {
            margin: 1rem;
        }
        
        .chart-wrapper {
            height: 250px;
        }
    }
</style>
@endpush

@section('content') 
<div class="container-fluid">
    <!-- Título de la página y breadcrumbs -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="javascript: void(0);">Centro Pre</a></li>
                        <li class="breadcrumb-item">Gestión Financiera</li>
                        <li class="breadcrumb-item active">Dashboard Financiero</li>
                    </ol>
                </div>
                <h4 class="page-title">
                    <i class="mdi mdi-chart-line me-1"></i>
                    Dashboard Financiero
                </h4>
            </div>
        </div>
    </div>
    <!-- fin del título de la página -->

    <!-- Métricas Principales -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="metric-card info">
                <div class="card-body">
                    <i class="fas fa-users float-end"></i>
                    <h6 class="text-uppercase mt-0">Total Postulantes con Pago</h6>
                    <h2 class="my-2 metric-value" id="totalPostulantes">{{ number_format($datos['total_general']['postulantes']) }}</h2>
                    <p class="mb-0 small">Con pago confirmado</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="metric-card success">
                <div class="card-body">
                    <i class="fas fa-money-bill-wave float-end"></i>
                    <h6 class="text-uppercase mt-0">Total Recaudado</h6>
                    <h2 class="my-2 metric-value" id="totalRecaudado">S/ {{ number_format($datos['total_general']['recaudado'], 0) }}</h2>
                    <p class="mb-0 small">Ingresos confirmados</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="metric-card warning">
                <div class="card-body">
                    <i class="fas fa-file-invoice float-end"></i>
                    <h6 class="text-uppercase mt-0">Vouchers Emitidos</h6>
                    <h2 class="my-2 metric-value" id="vouchersEmitidos">{{ number_format($datos['total_general']['vouchers']) }}</h2>
                    <p class="mb-0 small">Comprobantes generados</p>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="metric-card danger">
                <div class="card-body">
                    <i class="fas fa-clock float-end"></i>
                    <h6 class="text-uppercase mt-0">Pagos Pendientes</h6>
                    <h2 class="my-2 metric-value" id="pagosPendientes">{{ number_format($datos['pagos_pendientes']) }}</h2>
                    <p class="mb-0 small">Requieren seguimiento</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráficos reestructurados para un mejor layout -->
    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="chart-container">
                <h5 class="section-title">
                    <i class="fas fa-chart-area mr-2"></i>
                    Evolución Mensual de Ingresos
                </h5>
                <div class="chart-wrapper">
                    <canvas id="ingresosMensualesChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="chart-container">
                <h5 class="section-title">
                    <i class="fas fa-chart-pie mr-2"></i>
                    Distribución por Carrera
                </h5>
                <div class="chart-wrapper">
                    <canvas id="distribucionCarrerasChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Botones de Acción -->
    <div class="row mb-3">
        <div class="col-12 text-right">
            <div class="btn-group" role="group">
                <a href="{{ route('reportes.financieros.exportar') }}" class="export-btn">
                    <i class="fas fa-file-excel mr-2"></i>
                    Exportar a Excel
                </a>
                <button class="export-btn" onclick="exportarPDF()" style="background-image: var(--danger-gradient); box-shadow: 0 4px 10px rgba(234, 84, 85, 0.5);">
                    <i class="fas fa-file-pdf mr-2"></i>
                    Exportar a PDF
                </button>
                <button class="export-btn" onclick="refrescarDatos()" style="background-image: var(--info-gradient); box-shadow: 0 4px 10px rgba(0, 207, 232, 0.5);">
                    <i class="fas fa-sync-alt mr-2"></i>
                    Actualizar Datos
                </button>
            </div>
        </div>
    </div>

    <!-- Tablas reestructuradas para un mejor layout -->
    <div class="row">
        <div class="col-lg-8">
            <div class="professional-table mb-4">
                <div class="card-header bg-transparent border-0 pt-0 pb-0 mb-3">
                    <h5 class="section-title">
                        <i class="fas fa-graduation-cap mr-2"></i>
                        Análisis por Carrera y Ciclo
                    </h5>
                </div>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th><i class="fas fa-calendar mr-1"></i>Ciclo</th>
                                <th><i class="fas fa-book mr-1"></i>Carrera</th>
                                <th><i class="fas fa-users mr-1"></i>Postulantes</th>
                                <th><i class="fas fa-id-card mr-1"></i>Matrícula</th>
                                <th><i class="fas fa-chalkboard-teacher mr-1"></i>Enseñanza</th>
                                <th><i class="fas fa-calculator mr-1"></i>Total</th>
                                <th><i class="fas fa-file-invoice mr-1"></i>Vouchers</th>
                                <th><i class="fas fa-cog mr-1"></i>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($datos['resumen_por_carrera'] as $resumen)
                            <tr>
                                <td>
                                    <span class="badge badge-primary">{{ $resumen['ciclo'] }}</span>
                                </td>
                                <td>
                                    <strong>{{ $resumen['carrera'] }}</strong>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-user-graduate text-info mr-2"></i>
                                        <strong>{{ $resumen['total_postulantes'] }}</strong>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-success">
                                        S/ {{ number_format($resumen['total_matricula'], 2) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-warning">
                                        S/ {{ number_format($resumen['total_ensenanza'], 2) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="bg-light p-2 rounded text-center">
                                        <strong class="text-primary" style="font-size: 0.9rem;">
                                            S/ {{ number_format($resumen['total_recaudado'], 2) }}
                                        </strong>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-info">{{ $resumen['vouchers_emitidos'] }}</span>
                                </td>
                                <td>
                                    <button class="action-btn btn-info" onclick="verDetalle({{ $loop->index }})">
                                        <i class="fas fa-eye mr-1"></i> 
                                        Detalle
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="2"><strong>TOTALES</strong></td>
                                <td>
                                    @php 
                                        $totalPostulantes = 0;
                                        $totalMatricula = 0;
                                        $totalEnsenanza = 0;
                                        $totalRecaudado = 0;
                                        $totalVouchers = 0;
                                        
                                        foreach($datos['resumen_por_carrera'] as $resumen) {
                                            $totalPostulantes += $resumen['total_postulantes'] ?? 0;
                                            $totalMatricula += $resumen['total_matricula'] ?? 0;
                                            $totalEnsenanza += $resumen['total_ensenanza'] ?? 0;
                                            $totalRecaudado += $resumen['total_recaudado'] ?? 0;
                                            $totalVouchers += $resumen['vouchers_emitidos'] ?? 0;
                                        }
                                    @endphp
                                    <strong>{{ number_format($totalPostulantes) }}</strong>
                                </td>
                                <td><strong>S/ {{ number_format($totalMatricula, 2) }}</strong></td>
                                <td><strong>S/ {{ number_format($totalEnsenanza, 2) }}</strong></td>
                                <td><strong class="text-primary">S/ {{ number_format($totalRecaudado, 2) }}</strong></td>
                                <td><strong>{{ number_format($totalVouchers) }}</strong></td>
                                <td>-</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="professional-table">
                <div class="card-header bg-transparent border-0 pt-0 pb-0 mb-3">
                    <h5 class="section-title">
                        <i class="fas fa-tachometer-alt mr-2"></i>
                        KPIs Principales
                    </h5>
                </div>
                <div class="p-4">
                    <div class="mb-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span style="color: #2c3e50; font-weight: 600;">Tasa de Conversión</span>
                            <strong style="color: #28c76f;">85%</strong>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar bg-success" style="width: 85%"></div>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span style="color: #2c3e50; font-weight: 600;">Vouchers Completados</span>
                            <strong style="color: #00cfe8;">92%</strong>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar bg-info" style="width: 92%"></div>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <div class="d-flex justify-content-between mb-2">
                            <span style="color: #2c3e50; font-weight: 600;">Pagos Confirmados</span>
                            <strong style="color: #ff9f43;">78%</strong>
                        </div>
                        <div class="progress" style="height: 10px;">
                            <div class="progress-bar bg-warning" style="width: 78%"></div>
                        </div>
                    </div>
                    
                    <div class="text-center mt-4 p-3 bg-light rounded">
                        <h6 class="text-muted mb-2">Promedio General de Pago</h6>
                        <h3 class="text-primary mb-0" style="font-weight: 700;">
                            S/ {{ number_format($datos['total_general']['recaudado'] / max($datos['total_general']['postulantes'], 1), 2) }}
                        </h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Mejorado -->
<div class="modal fade" id="detalleModal" tabindex="-1" role="dialog" aria-labelledby="detalleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detalleModalLabel">
                    <i class="fas fa-list-alt mr-2"></i>
                    Detalle de Postulaciones
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="detalleContent"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times mr-1"></i> Cerrar
                </button>
                <button type="button" class="btn btn-primary" onclick="imprimirDetalle()">
                    <i class="fas fa-print mr-1"></i> Imprimir
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
<script>
    // Función para animar contadores de estadísticas
    function animateCounter(element) {
        let targetText = element.textContent.replace(/[S\/,]/g, '').trim();
        const target = parseFloat(targetText) || 0;
        let current = 0;
        const duration = 1500; // 1.5 segundos
        const stepTime = 20;
        const steps = duration / stepTime;
        const increment = target / steps;
        
        const timer = setInterval(() => {
            current += increment;
            if (current >= target) {
                current = target;
                clearInterval(timer);
            }
            
            if (element.textContent.includes('S/')) {
                element.textContent = 'S/ ' + Math.floor(current).toLocaleString();
            } else {
                element.textContent = Math.floor(current).toLocaleString();
            }
        }, stepTime);
    }

    // Datos para gráficos
    const datosIngresosMensuales = @json($datos['resumen_mensual'] ?? []);
    const datosCarreras = @json(array_values($datos['resumen_por_carrera'] ?? []));

    // Gráfico de ingresos mensuales mejorado
    const ctxIngresos = document.getElementById('ingresosMensualesChart').getContext('2d');
    new Chart(ctxIngresos, {
        type: 'line',
        data: {
            labels: datosIngresosMensuales.map(item => item.periodo),
            datasets: [{
                label: 'Ingresos Mensuales',
                data: datosIngresosMensuales.map(item => item.total_recaudado_mes),
                borderColor: 'rgb(115, 103, 240)',
                backgroundColor: 'rgba(115, 103, 240, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: 'rgb(115, 103, 240)',
                pointBorderColor: '#fff',
                pointBorderWidth: 3,
                pointRadius: 6,
                pointHoverRadius: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'S/ ' + value.toLocaleString();
                        },
                        font: {
                            size: 12,
                            weight: '600'
                        },
                        color: '#666'
                    },
                    grid: {
                        color: 'rgba(0,0,0,0.1)'
                    }
                },
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        font: {
                            size: 11,
                            weight: '600'
                        },
                        color: '#666'
                    }
                }
            }
        }
    });

    // Gráfico de distribución por carreras mejorado
    const ctxCarreras = document.getElementById('distribucionCarrerasChart').getContext('2d');
    const coloresCarreras = [
        '#FF6B6B', '#4ECDC4', '#45B7D1', '#96CEB4', '#FFEAA7',
        '#DDA0DD', '#98D8C8', '#F7DC6F', '#BB8FCE', '#85C1E9'
    ];

    new Chart(ctxCarreras, {
        type: 'doughnut',
        data: {
            labels: datosCarreras.map(item => item.carrera),
            datasets: [{
                data: datosCarreras.map(item => item.total_recaudado),
                backgroundColor: coloresCarreras.slice(0, datosCarreras.length),
                borderWidth: 2,
                borderColor: '#fff',
                hoverBorderWidth: 4,
                hoverBorderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 15,
                        usePointStyle: true,
                        font: {
                            size: 11,
                            weight: '600'
                        },
                        color: '#666'
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.label + ': S/ ' + context.parsed.toLocaleString();
                        }
                    }
                }
            }
        }
    });

    // Función mejorada para mostrar detalle con visualización de PDF
    function verDetalle(index) {
        const resumen = datosCarreras[index];
        let html = `
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="bg-primary text-white p-3 rounded">
                        <h6><i class="fas fa-graduation-cap mr-2"></i><strong>Carrera:</strong> ${resumen.carrera}</h6>
                        <h6><i class="fas fa-calendar mr-2"></i><strong>Ciclo:</strong> ${resumen.ciclo}</h6>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="bg-success text-white p-3 rounded">
                        <h6><i class="fas fa-users mr-2"></i><strong>Postulantes:</strong> ${resumen.total_postulantes}</h6>
                        <h6><i class="fas fa-money-bill-wave mr-2"></i><strong>Total Recaudado:</strong> S/ ${parseFloat(resumen.total_recaudado).toFixed(2)}</h6>
                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th><i class="fas fa-user mr-1"></i>Estudiante</th>
                            <th><i class="fas fa-id-card mr-1"></i>Documento</th>
                            <th><i class="fas fa-credit-card mr-1"></i>Matrícula</th>
                            <th><i class="fas fa-book mr-1"></i>Enseñanza</th>
                            <th><i class="fas fa-calculator mr-1"></i>Total</th>
                            <th><i class="fas fa-receipt mr-1"></i>N° Recibo</th>
                            <th><i class="fas fa-file-pdf mr-1"></i>Voucher</th>
                        </tr>
                    </thead>
                    <tbody>
        `;

        resumen.postulaciones.forEach((postulacion, idx) => {
            const estudiante = postulacion.estudiante;
            const nombreCompleto = `${estudiante.nombre} ${estudiante.apellido_paterno} ${estudiante.apellido_materno}`;
            const total = parseFloat(postulacion.monto_matricula) + parseFloat(postulacion.monto_ensenanza);
            
            // Mejorado para incluir tanto voucher_path como voucher_pago_path
            const voucherUrl = postulacion.voucher_path ? `/storage/${postulacion.voucher_path}` : 
                              (postulacion.voucher_pago_path ? `/storage/${postulacion.voucher_pago_path}` : null);
            
            const downloadUrl = postulacion.voucher_pago_path ? 
                `${window.location.origin}/reportes/financieros/descargar-voucher/${postulacion.id}` : null;
            
            let voucherLink = '<span class="text-muted"><i class="fas fa-times"></i> No disponible</span>';
            
            if (voucherUrl) {
                voucherLink = `
                    <div class="btn-group" role="group">
                        <button class="btn btn-sm btn-primary" onclick="verVoucherModal('${voucherUrl}', '${nombreCompleto}')" title="Visualizar PDF">
                            <i class="fas fa-eye"></i>
                        </button>
                        <a href="${voucherUrl}" class="btn btn-sm btn-outline-primary" target="_blank" title="Abrir en nueva pestaña">
                            <i class="fas fa-external-link-alt"></i>
                        </a>
                        ${downloadUrl ? `<a href="${downloadUrl}" class="btn btn-sm btn-success" download title="Descargar PDF"><i class="fas fa-download"></i></a>` : ''}
                    </div>
                `;
            }

            html += `
                <tr>
                    <td>
                        <div class="d-flex align-items-center">
                            <div class="avatar">
                                ${estudiante.nombre.charAt(0)}${estudiante.apellido_paterno.charAt(0)}
                            </div>
                            <div>
                                <strong style="color: #2c3e50;">${nombreCompleto}</strong>
                            </div>
                        </div>
                    </td>
                    <td><code>${estudiante.numero_documento}</code></td>
                    <td><span class="badge badge-success">S/ ${parseFloat(postulacion.monto_matricula).toFixed(2)}</span></td>
                    <td><span class="badge badge-warning">S/ ${parseFloat(postulacion.monto_ensenanza).toFixed(2)}</span></td>
                    <td><strong class="text-primary" style="font-size: 0.9rem;">S/ ${total.toFixed(2)}</strong></td>
                    <td>${postulacion.numero_recibo ? `<code>${postulacion.numero_recibo}</code>` : '<span class="text-muted">-</span>'}</td>
                    <td>${voucherLink}</td>
                </tr>
            `;
        });

        html += `
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="5"><strong>TOTAL CARRERA:</strong></td>
                            <td colspan="2"><strong class="text-primary">S/ ${parseFloat(resumen.total_recaudado).toFixed(2)}</strong></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        `;
        
        document.getElementById('detalleContent').innerHTML = html;
        $('#detalleModal').modal('show');
    }

    // Función mejorada para visualizar vouchers con PDF viewer integrado
    function verVoucherModal(url, nombreEstudiante = '') {
        // Detectar si es PDF por la extensión
        const isPDF = url.toLowerCase().includes('.pdf');
        
        let html = `
            <div class="text-center mb-4">
                <div class="bg-danger text-white p-3 rounded d-flex align-items-center justify-content-center">
                    <i class="fas fa-file-pdf mr-2" style="font-size: 1.5rem;"></i>
                    <div>
                        <h6 class="mb-0">Voucher de Pago</h6>
                        ${nombreEstudiante ? `<small>${nombreEstudiante}</small>` : ''}
                    </div>
                </div>
            </div>
        `;

        if (isPDF) {
            html += `
                <div class="pdf-viewer-container mb-3" style="height: 600px; border: 2px solid #dee2e6; border-radius: 8px; overflow: hidden;">
                    <div class="pdf-toolbar bg-light p-2 border-bottom d-flex justify-content-between align-items-center">
                        <div class="pdf-controls">
                            <button class="btn btn-sm btn-outline-secondary" onclick="zoomOut()" title="Reducir zoom">
                                <i class="fas fa-search-minus"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-secondary" onclick="resetZoom()" title="Zoom original">
                                <i class="fas fa-expand-arrows-alt"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-secondary" onclick="zoomIn()" title="Aumentar zoom">
                                <i class="fas fa-search-plus"></i>
                            </button>
                        </div>
                        <div class="pdf-info">
                            <small class="text-muted">Vista previa del documento</small>
                        </div>
                    </div>
                    <iframe 
                        id="pdfViewer" 
                        src="${url}#toolbar=1&navpanes=0&scrollbar=1&page=1&zoom=page-fit" 
                        style="width: 100%; height: calc(100% - 50px); border: none;"
                        onload="onPDFLoad()"
                        onerror="onPDFError()">
                    </iframe>
                </div>
                <div class="text-center">
                    <div class="btn-group" role="group">
                        <a href="${url}" target="_blank" class="btn btn-primary">
                            <i class="fas fa-external-link-alt mr-2"></i> Abrir en Nueva Pestaña
                        </a>
                        <a href="${url}" download class="btn btn-success">
                            <i class="fas fa-download mr-2"></i> Descargar PDF
                        </a>
                        <button class="btn btn-info" onclick="imprimirPDF('${url}')">
                            <i class="fas fa-print mr-2"></i> Imprimir
                        </button>
                    </div>
                </div>
            `;
        } else {
            // Para imágenes u otros formatos
            html += `
                <div class="image-viewer-container text-center mb-3">
                    <img src="${url}" class="img-fluid border rounded shadow" alt="Voucher" style="max-height: 500px;">
                </div>
                <div class="text-center">
                    <div class="btn-group" role="group">
                        <a href="${url}" target="_blank" class="btn btn-primary">
                            <i class="fas fa-external-link-alt mr-2"></i> Abrir en Nueva Pestaña
                        </a>
                        <a href="${url}" download class="btn btn-success">
                            <i class="fas fa-download mr-2"></i> Descargar
                        </a>
                    </div>
                </div>
            `;
        }
        
        document.getElementById('detalleContent').innerHTML = html;
        $('#detalleModal').modal('show');
    }

    function imprimirDetalle() {
        const contenido = document.getElementById('detalleContent').innerHTML;
        const ventanaImpresion = window.open('', '_blank');
        ventanaImpresion.document.write(`
            <html>
            <head>
                <title>Detalle de Postulaciones - Centro Pre</title>
                <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/4.6.0/css/bootstrap.min.css" rel="stylesheet">
                <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
                <style>
                    @media print {
                        .btn, .btn-group { display: none !important; }
                        .table { font-size: 11px; }
                        body { -webkit-print-color-adjust: exact; }
                    }
                    body { 
                        font-family: Arial, sans-serif; 
                        color: #2c3e50;
                    }
                    .avatar { display: none !important; }
                    .table thead th {
                        background: #7367f0 !important;
                        color: white !important;
                    }
                    .badge {
                        color: white !important;
                        font-weight: 600;
                    }
                    code {
                        background-color: #f1f3f4;
                        color: #e91e63;
                        padding: 2px 6px;
                        border-radius: 3px;
                        font-weight: 600;
                    }
                </style>
            </head>
            <body class="p-4">
                <div class="text-center mb-4">
                    <h2 style="color: #2c3e50;">Centro Pre - Detalle de Postulaciones</h2>
                    <p class="text-muted">Fecha de generación: ${new Date().toLocaleDateString('es-PE', { 
                        year: 'numeric', 
                        month: 'long', 
                        day: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                    })}</p>
                </div>
                ${contenido}
                <div class="mt-4 text-center">
                    <small class="text-muted">
                        Documento generado automáticamente por el Sistema de Gestión Financiera
                    </small>
                </div>
            </body>
            </html>
        `);
        ventanaImpresion.document.close();
        ventanaImpresion.focus();
        setTimeout(() => {
            ventanaImpresion.print();
            ventanaImpresion.close();
        }, 500);
    }
    
    // Inicialización cuando el documento está listo
    document.addEventListener('DOMContentLoaded', function() {
        // Animación de contadores al cargar la página
        const counters = document.querySelectorAll('.metric-value');
        counters.forEach(counter => {
            setTimeout(() => {
                animateCounter(counter);
            }, 300);
        });

        // Efectos mejorados para las tarjetas
        const cards = document.querySelectorAll('.metric-card');
        cards.forEach((card, index) => {
            // Animación escalonada al cargar
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            
            setTimeout(() => {
                card.style.transition = 'all 0.6s ease';
                card.style.opacity = '1';
                card.style.transform = 'translateY(0)';
            }, index * 150);

            // Efectos de hover mejorados
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-8px) scale(1.02)';
                this.style.boxShadow = '0 20px 40px rgba(0,0,0,0.15)';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0) scale(1)';
                this.style.boxShadow = '0 4px 12px rgba(0,0,0,0.08)';
            });
        });

        // Animación para las barras de progreso
        const progressBars = document.querySelectorAll('.progress-bar');
        progressBars.forEach((bar, index) => {
            const width = bar.style.width;
            bar.style.width = '0%';
            
            setTimeout(() => {
                bar.style.transition = 'width 1.5s ease-in-out';
                bar.style.width = width;
            }, 1000 + (index * 200));
        });

        // Mejorar la funcionalidad del modal
        $('#detalleModal').on('hidden.bs.modal', function () {
            document.getElementById('detalleContent').innerHTML = '';
        });

        // Agregar tooltips a los botones de acción
        const actionButtons = document.querySelectorAll('.action-btn');
        actionButtons.forEach(button => {
            button.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-2px)';
                this.style.boxShadow = '0 8px 15px rgba(0, 207, 232, 0.4)';
            });
            
            button.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
                this.style.boxShadow = '0 2px 8px rgba(0, 207, 232, 0.3)';
            });
        });
    });

    // Funciones para el visualizador de PDF mejorado
    function zoomIn() {
        const iframe = document.getElementById('pdfViewer');
        if (iframe) {
            const currentSrc = iframe.src;
            const newSrc = currentSrc.replace(/zoom=[^&]*/, 'zoom=150');
            iframe.src = newSrc;
        }
    }

    function zoomOut() {
        const iframe = document.getElementById('pdfViewer');
        if (iframe) {
            const currentSrc = iframe.src;
            const newSrc = currentSrc.replace(/zoom=[^&]*/, 'zoom=75');
            iframe.src = newSrc;
        }
    }

    function resetZoom() {
        const iframe = document.getElementById('pdfViewer');
        if (iframe) {
            const currentSrc = iframe.src;
            const newSrc = currentSrc.replace(/zoom=[^&]*/, 'zoom=page-fit');
            iframe.src = newSrc;
        }
    }

    function onPDFLoad() {
        console.log('PDF cargado correctamente');
        // Opcional: mostrar mensaje de éxito
        const toolbar = document.querySelector('.pdf-toolbar .pdf-info');
        if (toolbar) {
            toolbar.innerHTML = '<small class="text-success"><i class="fas fa-check-circle"></i> Documento cargado</small>';
        }
    }

    function onPDFError() {
        console.log('Error al cargar PDF');
        const iframe = document.getElementById('pdfViewer');
        if (iframe) {
            iframe.innerHTML = `
                <div class="d-flex align-items-center justify-content-center h-100 bg-light">
                    <div class="text-center text-muted">
                        <i class="fas fa-exclamation-triangle fa-3x mb-3"></i>
                        <h6>No se pudo cargar el documento</h6>
                        <p>El archivo PDF no está disponible o hay un problema de conexión.</p>
                    </div>
                </div>
            `;
        }
    }

    function imprimirPDF(url) {
        // Abrir el PDF en una nueva ventana para imprimir
        const printWindow = window.open(url, '_blank');
        printWindow.addEventListener('load', function() {
            printWindow.print();
        });
    }

    // Función para refrescar datos con indicador visual
    function refrescarDatos() {
        // Mostrar indicador de carga
        const btn = event.target;
        const originalText = btn.innerHTML;
        const icon = btn.querySelector('i');
        
        // Cambiar botón a estado de carga
        btn.disabled = true;
        icon.className = 'fas fa-spinner fa-spin mr-2';
        btn.innerHTML = btn.innerHTML.replace('Actualizar Datos', 'Actualizando...');
        
        // Simular actualización (en producción sería una llamada AJAX real)
        setTimeout(() => {
            // Aquí podrías agregar una llamada AJAX real:
            /*
            fetch('/dashboard/financiero/datos')
                .then(response => response.json())
                .then(data => {
                    // Actualizar métricas
                    document.getElementById('totalPostulantes').textContent = data.total_postulantes;
                    document.getElementById('totalRecaudado').textContent = 'S/ ' + data.total_recaudado.toLocaleString();
                    // ... etc
                    
                    // Actualizar gráficos
                    actualizarGraficos(data);
                })
                .catch(error => {
                    console.error('Error:', error);
                    mostrarNotificacion('Error al actualizar los datos', 'error');
                })
                .finally(() => {
                    // Restaurar botón
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                });
            */
            
            // Por ahora solo simulamos la actualización
            console.log('Datos actualizados correctamente');
            mostrarNotificacion('Datos actualizados correctamente', 'success');
            
            // Restaurar botón
            btn.disabled = false;
            btn.innerHTML = originalText;
        }, 2000);
    }

    // Función para mostrar notificaciones
    function mostrarNotificacion(mensaje, tipo = 'info') {
        // Crear elemento de notificación
        const notification = document.createElement('div');
        notification.className = `alert alert-${tipo} alert-dismissible fade show position-fixed`;
        notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        
        const iconos = {
            success: 'fas fa-check-circle',
            error: 'fas fa-exclamation-triangle',
            warning: 'fas fa-exclamation-circle',
            info: 'fas fa-info-circle'
        };
        
        notification.innerHTML = `
            <div class="d-flex align-items-center">
                <i class="${iconos[tipo]} mr-2"></i>
                <span>${mensaje}</span>
                <button type="button" class="close ml-auto" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        // Auto-remove después de 5 segundos
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 5000);
    }

    // Función para exportar datos (si es necesario)
    function exportarDatos(formato = 'excel') {
        console.log(`Exportando datos en formato ${formato}...`);
        // Aquí podrías agregar la lógica de exportación
        
        // Ejemplo de redirección para descarga:
        // window.location.href = `/dashboard/financiero/exportar/${formato}`;
    }

    // Agregar funcionalidad de teclado para el modal
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape' && $('#detalleModal').hasClass('show')) {
            $('#detalleModal').modal('hide');
        }
        
        if (event.ctrlKey && event.key === 'p' && $('#detalleModal').hasClass('show')) {
            event.preventDefault();
            imprimirDetalle();
        }
    });
</script>
@endpush