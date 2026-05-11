@extends('layouts.app')

@section('title', 'Reportes de Asistencia')

@push('css')
    <style>
        :root {
            --corporate-blue: #1e3a8a;
            --corporate-blue-dark: #1e40af;
            --corporate-gray: #f9fafb;
            --corporate-border: #d1d5db;
            --corporate-text: #1f2937;
        }

        .tilebox-one {
            border: 1px solid var(--corporate-border);
            border-radius: 4px;
            transition: all 0.15s ease;
            overflow: hidden;
            background: #ffffff;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        .tilebox-one:hover {
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .tilebox-one .card-body {
            position: relative;
            z-index: 2;
            padding: 1.25rem;
            border-left: 4px solid var(--corporate-blue);
        }
        .tilebox-one .mdi {
            font-size: 2rem;
            opacity: 0.15;
            color: var(--corporate-blue);
        }
        .tilebox-one h2, .tilebox-one h6 {
            color: var(--corporate-text);
            margin: 0;
        }
        .tilebox-one h6 {
            opacity: 0.7;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
        }
        .tilebox-one h2 {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--corporate-blue);
        }
        .chart-container {
            position: relative;
            height: 350px;
            width: 100%;
        }
        
        body[data-layout-mode="dark"] .tilebox-one {
            background: #364053;
            border-color: #4a5468;
        }
        body[data-layout-mode="dark"] .tilebox-one h2,
        body[data-layout-mode="dark"] .tilebox-one h6 {
            color: #eef2f7;
        }
        body[data-layout-mode="dark"] .card {
            background: #364053;
            border-color: #4a5468;
        }
        body[data-layout-mode="dark"] .header-title {
            color: #eef2f7;
        }
    </style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('asistencia.index') }}">Asistencia</a></li>
                        <li class="breadcrumb-item active">Reportes</li>
                    </ol>
                </div>
                <h4 class="page-title"><i class="mdi mdi-chart-bar me-1"></i> Reportes y Estadísticas de Asistencia</h4>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card tilebox-one">
                <div class="card-body">
                    <i class="mdi mdi-calendar-today float-end"></i>
                    <h6>Asistencias Hoy</h6>
                    <h2>{{ number_format($totalHoy) }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card tilebox-one">
                <div class="card-body">
                    <i class="mdi mdi-calendar-week float-end"></i>
                    <h6>Asistencias esta Semana</h6>
                    <h2>{{ number_format($totalSemana) }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card tilebox-one">
                <div class="card-body">
                    <i class="mdi mdi-calendar-month float-end"></i>
                    <h6>Asistencias este Mes</h6>
                    <h2>{{ number_format($totalMes) }}</h2>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="header-title mb-4">Tendencia de Asistencia (Semana Actual)</h4>
                    <div class="chart-container">
                        <canvas id="asistenciaChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('asistenciaChart').getContext('2d');
    const datos = @json($asistenciaSemana);
    
    // Preparar labels y data
    const labels = [];
    const data = [];
    
    for (const [fecha, conteo] of Object.entries(datos)) {
        // Formatear la fecha para que sea más legible (ej. "Lun 11")
        // Como la fecha viene en Y-m-d (UTC origin), la parseamos asegurando que no cambie el día
        const dateParts = fecha.split('-');
        const dateObj = new Date(dateParts[0], dateParts[1] - 1, dateParts[2]);
        const options = { weekday: 'short', day: 'numeric' };
        labels.push(dateObj.toLocaleDateString('es-ES', options).toUpperCase());
        data.push(conteo);
    }
    
    // Verificar modo oscuro
    const isDarkMode = document.body.getAttribute('data-layout-mode') === 'dark';
    const gridColor = isDarkMode ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)';
    const textColor = isDarkMode ? '#eef2f7' : '#1f2937';
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Número de Asistencias',
                data: data,
                backgroundColor: 'rgba(30, 58, 138, 0.8)', // corporate-blue con opacidad
                borderColor: '#1e3a8a',
                borderWidth: 1,
                borderRadius: 4,
                hoverBackgroundColor: '#1e40af'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0,
                        color: textColor
                    },
                    grid: {
                        color: gridColor,
                        drawBorder: false
                    }
                },
                x: {
                    ticks: {
                        color: textColor
                    },
                    grid: {
                        display: false,
                        drawBorder: false
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: isDarkMode ? '#4a5468' : '#1f2937',
                    titleFont: { size: 13, family: 'Nunito, sans-serif' },
                    bodyFont: { size: 14, family: 'Nunito, sans-serif' },
                    padding: 12,
                    cornerRadius: 4,
                    displayColors: false
                }
            }
        }
    });
});
</script>
@endpush
