@extends('layouts.app')

@section('title', 'Mis Reportes y Asistencia')

@push('css')
<style>
    .card-docente {
        border-radius: 15px;
        border: none;
        overflow: hidden;
        transition: all 0.3s ease;
    }
    .welcome-banner {
        background: linear-gradient(135deg, #4f32c2 0%, #7367f0 100%);
        color: white;
        padding: 30px;
        border-radius: 15px;
        margin-bottom: 25px;
        box-shadow: 0 4px 15px rgba(115, 103, 240, 0.3);
    }
    .feature-card {
        border: 1px solid #eef2f7;
        border-radius: 12px;
        padding: 25px;
        height: 100%;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .feature-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.05);
        border-color: #7367f0;
    }
    .feature-icon {
        width: 60px;
        height: 60px;
        background: rgba(115, 103, 240, 0.1);
        color: #7367f0;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
        margin-bottom: 20px;
    }
    .form-label {
        font-weight: 600;
        color: #5e5873;
        font-size: 0.9rem;
    }
    .form-select, .form-control {
        border-radius: 8px;
        padding: 10px 15px;
        border-color: #d8d6de;
    }
    .form-select:focus, .form-control:focus {
        border-color: #7367f0;
        box-shadow: 0 0 0 0.2rem rgba(115, 103, 240, 0.1);
    }
    .btn-download {
        background: linear-gradient(135deg, #7367f0 0%, #9e95f5 100%);
        border: none;
        border-radius: 8px;
        padding: 12px;
        font-weight: 600;
        letter-spacing: 0.5px;
        transition: all 0.3s ease;
    }
    .btn-download:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(115, 103, 240, 0.4);
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Banner de Bienvenida -->
    <div class="welcome-banner d-flex align-items-center justify-content-between">
        <div>
            <h2 class="text-white mb-1">Mis Reportes y Asistencia 📊</h2>
            <p class="text-white-50 mb-0">Genera reportes detallados de tu avance académico y control de asistencia.</p>
        </div>
        <div class="d-none d-md-block">
            <i class="mdi mdi-file-document-multiple-outline display-3 text-white-50"></i>
        </div>
    </div>

    <div class="row justify-content-center">
        <!-- Tarjeta de Generación de Reporte -->
        <div class="col-lg-6 mb-4">
            <div class="card card-docente shadow-sm h-100">
                <div class="card-body p-4">
                    <div class="d-flex align-items-start mb-4">
                        <div class="feature-icon me-3">
                            <i class="mdi mdi-file-pdf-box"></i>
                        </div>
                        <div>
                            <h4 class="mb-1 text-primary">Reporte de Asistencia PDF</h4>
                            <p class="text-muted mb-0 font-14">Descarga un informe oficial con tus horas dictadas, temas avanzados y cálculo de pagos.</p>
                        </div>
                    </div>

                    <form action="{{ route('asistencia-docente.exportar-pdf') }}" method="GET" class="mt-4">
                        <div class="mb-4">
                            <label for="ciclo_id" class="form-label">Seleccionar Ciclo Académico</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="mdi mdi-school text-muted"></i></span>
                                <select name="ciclo_id" id="ciclo_id" class="form-select border-start-0 ps-0">
                                    @foreach($ciclosActivos as $ciclo)
                                        <option value="{{ $ciclo->id }}">{{ $ciclo->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="fecha_inicio" class="form-label">Desde (Opcional)</label>
                                <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="fecha_fin" class="form-label">Hasta (Opcional)</label>
                                <input type="date" class="form-control" id="fecha_fin" name="fecha_fin">
                            </div>
                        </div>

                        <div class="alert alert-soft-primary border-0 d-flex align-items-center mt-2 mb-4">
                            <i class="mdi mdi-information-variant fs-4 me-2"></i>
                            <div class="small">
                                Si no seleccionas fechas, el reporte incluirá <strong>todo el historial</strong> del ciclo seleccionado hasta hoy.
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-download w-100 text-white">
                            <i class="mdi mdi-download-outline fs-5 align-middle me-1"></i> Descargar Reporte Completo
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Tarjeta Informativa / Next Steps -->
        <div class="col-lg-4 mb-4">
            <div class="card card-docente bg-soft-info border-info shadow-none overflow-hidden h-100">
                <div class="card-body position-relative p-4">
                    <div class="position-absolute end-0 top-0 opacity-10 me-n4 mt-n4">
                        <i class="mdi mdi-help-circle-outline display-1"></i>
                    </div>
                    
                    <h4 class="mb-3 text-info">¿Qué incluye este reporte?</h4>
                    <ul class="list-unstyled text-dark mb-0 d-grid gap-2">
                        <li class="d-flex align-items-center">
                            <i class="mdi mdi-check-circle-outline text-success me-2"></i> Registro de entrada y salida
                        </li>
                        <li class="d-flex align-items-center">
                            <i class="mdi mdi-check-circle-outline text-success me-2"></i> Temas desarrollados por sesión
                        </li>
                        <li class="d-flex align-items-center">
                            <i class="mdi mdi-check-circle-outline text-success me-2"></i> Cálculo de minutos efectivos
                        </li>
                        <li class="d-flex align-items-center">
                            <i class="mdi mdi-check-circle-outline text-success me-2"></i> Resumen de pagos estimados
                        </li>
                    </ul>
                    
                    <hr class="border-info opacity-25 my-4">
                    
                    <div class="d-flex align-items-center">
                        <i class="mdi mdi-alert-circle-outline text-info fs-3 me-2"></i>
                        <small class="text-muted">Si encuentras alguna inconsistencia en tu reporte, por favor contacta a Coordinación Académica inmediatamente.</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
