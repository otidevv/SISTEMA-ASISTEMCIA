@extends('layouts.app')

@section('title', 'Carga Horaria Docente')

@push('css')
<style>
    :root {
        --primary-gradient: linear-gradient(135deg, #4f32c2 0%, #7367f0 100%);
        --success-gradient: linear-gradient(135deg, #10b981 0%, #28c76f 100%);
        --info-gradient: linear-gradient(135deg, #00cfe8 0%, #1ce1ff 100%);
    }

    .card-header-gradient {
        background: var(--primary-gradient);
        color: white;
    }

    .stat-card {
        border: none;
        border-radius: 15px;
        transition: transform 0.3s ease;
    }

    .stat-card:hover {
        transform: translateY(-5px);
    }

    .btn-whatsapp {
        background-color: #25D366;
        color: white;
        border: none;
    }

    .btn-whatsapp:hover {
        background-color: #128C7E;
        color: white;
    }

    .table-responsive {
        border-radius: 10px;
    }

    #loading-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.7);
        display: none;
        justify-content: center;
        align-items: center;
        z-index: 10;
        border-radius: 15px;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Título y Breadcrumbs -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="javascript: void(0);">Inicio</a></li>
                        <li class="breadcrumb-item"><a href="javascript: void(0);">Reportes</a></li>
                        <li class="breadcrumb-item active">Carga Horaria</li>
                    </ol>
                </div>
                <h4 class="page-title">
                    <i class="mdi mdi-calendar-text me-1"></i>
                    Reporte de Carga Horaria Docente
                </h4>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card stat-card shadow-sm">
                <div class="card-body">
                    <div class="row align-items-end">
                        <div class="col-md-4">
                            <label for="ciclo_id" class="form-label fw-bold">Ciclo Académico</label>
                            <select id="ciclo_id" class="form-select">
                                @foreach($ciclos as $ciclo)
                                    <option value="{{ $ciclo->id }}" {{ $ciclo->es_activo ? 'selected' : '' }}>
                                        {{ $ciclo->nombre }} {{ $ciclo->es_activo ? '(Activo)' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-5">
                            <label for="docente_id" class="form-label fw-bold">Docente</label>
                            <select id="docente_id" class="form-select select2" data-toggle="select2">
                                <option value="">Seleccione un docente...</option>
                                @foreach($docentes as $docente)
                                    <option value="{{ $docente->id }}">{{ $docente->nombre_completo }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <div class="d-flex flex-column gap-2">
                                <button id="btn-consultar" class="btn btn-primary">
                                    <i class="mdi mdi-magnify me-1"></i> Consultar Carga
                                </button>
                                <a id="btn-excel-resumen" href="#" class="btn btn-success">
                                    <i class="mdi mdi-file-excel me-1"></i> Exportar Resumen Ciclo
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Resultados (Se muestra al consultar) -->
    <div id="section-resultados" style="display: none;">
        <div class="row">
            <!-- Resumen de Carga -->
            <div class="col-lg-4">
                <div class="card stat-card shadow-sm h-100">
                    <div class="card-header card-header-gradient">
                        <h4 class="header-title mb-0 text-white">Resumen de Carga</h4>
                    </div>
                    <div class="card-body position-relative">
                        <div id="loading-overlay">
                            <div class="spinner-border text-primary" role="status"></div>
                        </div>
                        
                        <div class="text-center mb-4">
                            <div id="docente-avatar" class="avatar-lg rounded-circle bg-soft-primary border-primary border mx-auto mb-2 d-flex align-items-center justify-content-center">
                                <span class="h2 text-primary mb-0 font-weight-bold">?</span>
                            </div>
                            <h4 id="resumen-nombre" class="mb-1">Nombre Docente</h4>
                            <p id="resumen-documento" class="text-muted small">DNI: ---------</p>
                        </div>

                        <ul class="list-group list-group-flush">
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <span><i class="mdi mdi-clock-check me-2 text-primary"></i>Horas Semanales Base (L-V)</span>
                                <span id="resumen-horas-base" class="fw-bold h5 mb-0 text-primary">0.0h</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <span><i class="mdi mdi-calendar-check me-2 text-info"></i>Total Horas Ciclo</span>
                                <span id="resumen-horas-ciclo" class="fw-bold text-info">0.0h</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 d-none" id="row-promedio-sabados">
                                <span><i class="mdi mdi-plus-circle-outline me-2 text-muted"></i>Promedio Sábados</span>
                                <span id="resumen-promedio-horas" class="fw-bold text-muted" style="font-size: 0.8rem;">0.0h</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <span><i class="mdi mdi-cash me-2 text-success"></i>Tarifa por Hora</span>
                                <span id="resumen-tarifa" class="fw-bold text-success">S/ 0.00</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <span><i class="mdi mdi-calendar-week me-2 text-success"></i>Pago Semanal Prom.</span>
                                <span id="resumen-pago-semanal" class="fw-bold text-success">S/ 0.00</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <span><i class="mdi mdi-calendar-month me-2 text-warning"></i>Pago Mensual (Est.)</span>
                                <span id="resumen-pago-mensual" class="fw-bold text-warning">S/ 0.00</span>
                            </li>
                             <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <span id="label-total-semanas"><i class="mdi mdi-calculator me-2 text-danger"></i>Total Ciclo ({{ $cicloActivo ? round($cicloActivo->fecha_inicio->diffInDays($cicloActivo->fecha_fin) / 7, 1) : '?' }} sem)</span>
                                <span id="resumen-pago-total" class="fw-bold text-danger">S/ 0.00</span>
                            </li>
                        </ul>

                        <div class="mt-4">
                            <h6 class="fw-bold text-uppercase mb-2">Acciones de Reporte</h6>
                            <div class="d-grid gap-2">
                                <a id="btn-pdf-visual" href="#" target="_blank" class="btn btn-outline-primary btn-sm">
                                    <i class="mdi mdi-calendar-range me-1"></i> Descargar Horario Visual
                                </a>
                                <a id="btn-pdf-detallado" href="#" target="_blank" class="btn btn-outline-info btn-sm">
                                    <i class="mdi mdi-file-document-outline me-1"></i> Descargar Reporte Detallado
                                </a>
                                <button id="btn-whatsapp" class="btn btn-whatsapp btn-sm">
                                    <i class="mdi mdi-whatsapp me-1"></i> Enviar por WhatsApp
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detalle de Cursos y Horario -->
            <div class="col-lg-8">
                <div class="card stat-card shadow-sm h-100">
                    <div class="card-header bg-white">
                        <ul class="nav nav-tabs card-header-tabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="tab-lista" data-bs-toggle="tab" href="#content-lista" role="tab">
                                    <i class="mdi mdi-format-list-bulleted me-1"></i> Lista de Clases
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="tab-visual" data-bs-toggle="tab" href="#content-visual" role="tab">
                                    <i class="mdi mdi-calendar me-1"></i> Vista de Horario
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body">
                        <div class="tab-content">
                            <!-- Lista de Clases -->
                            <div class="tab-pane fade show active" id="content-lista" role="tabpanel">
                                <div class="table-responsive">
                                    <table class="table table-hover table-centered">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Curso</th>
                                                <th>Aula</th>
                                                <th>Día</th>
                                                <th>Horario</th>
                                                <th>Turno</th>
                                                <th class="text-end">Horas</th>
                                            </tr>
                                        </thead>
                                        <tbody id="lista-horarios-body">
                                            <!-- Se llena dinámicamente -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            
                            <!-- Vista de Horario (Calendario Simplificado) -->
                            <div class="tab-pane fade" id="content-visual" role="tabpanel">
                                <div id="horario-visual-container">
                                    <div class="alert alert-info py-2">
                                        <i class="mdi mdi-information-outline me-1"></i>
                                        Esta es una vista previa simplificada. Para una vista completa y profesional, descargue el PDF.
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-bordered text-center table-sm">
                                            <thead class="bg-light">
                                                <tr>
                                                    <th style="width: 100px;">Hora</th>
                                                    <th>Lunes</th>
                                                    <th>Martes</th>
                                                    <th>Miércoles</th>
                                                    <th>Jueves</th>
                                                    <th>Viernes</th>
                                                    <th>Sábado</th>
                                                </tr>
                                            </thead>
                                            <tbody id="grid-horario-body">
                                                <!-- Se llena dinámicamente o se muestra mensaje -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Estado Inicial -->
    <div id="section-vacio" class="row justify-content-center mt-5">
        <div class="col-md-6 text-center py-5">
            <i class="mdi mdi-account-search-outline display-1 text-muted"></i>
            <h3 class="text-muted mt-3">Seleccione un docente para ver su carga horaria</h3>
            <p class="text-muted">Podrá visualizar el resumen de horas, pagos estimados y generar reportes.</p>
        </div>
    </div>
</div>
@endsection

@push('js')
<script src="{{ asset('js/carga-horaria/index.js') }}"></script>
@endpush
