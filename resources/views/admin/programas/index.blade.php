@extends('layouts.app')

@section('title', 'Gestión de Programas Académicos')

@section('content')
<!-- start page title -->
<div class="row">
    <div class="col-12">
        <div class="page-title-box">
            <h4 class="page-title">Configuración: Programas Académicos</h4>
            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Programas</li>
                </ol>
            </div>
        </div>
    </div>
</div>
<!-- end page title -->

<style>
    .card-header-custom {
        background: linear-gradient(135deg, #1A237E 0%, #311B92 100%);
        color: white;
        border-radius: 8px 8px 0 0 !important;
    }
    .table-custom thead th {
        background-color: #f8f9fa;
        color: #1A237E;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 11px;
        letter-spacing: 0.5px;
        border-top: none;
    }
    .badge-status {
        padding: 5px 12px;
        font-weight: 700;
        font-size: 10px;
        border-radius: 4px;
        text-transform: uppercase;
    }
    .btn-toggle {
        width: 35px;
        height: 35px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        transition: all 0.3s ease;
    }
    .btn-toggle:hover {
        transform: scale(1.1);
    }
</style>

<div class="row">
    <!-- Lista de Programas -->
    <div class="col-lg-8">
        <div class="card shadow-sm border-0 rounded-lg">
            <div class="card-header card-header-custom py-3">
                <div class="d-flex align-items-center">
                    <i class="mdi mdi-layers-outline fs-20 me-2 mr-2"></i>
                    <h5 class="mb-0 text-white fw-bold">Programas Registrados en el Sistema</h5>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-centered table-hover mb-0 table-custom">
                        <thead>
                            <tr>
                                <th class="ps-4">ID</th>
                                <th>Nombre del Programa</th>
                                <th class="text-center">Slug / Enlace</th>
                                <th class="text-center">Estado</th>
                                <th class="text-center pe-4">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($programas as $prog)
                            <tr>
                                <td class="ps-4 fw-bold text-muted">#{{ $prog->id }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-xs me-2 mr-2">
                                            <span class="avatar-title bg-soft-primary text-primary rounded-circle">
                                                {{ substr($prog->nombre, 0, 1) }}
                                            </span>
                                        </div>
                                        <span class="fw-bold text-dark">{{ $prog->nombre }}</span>
                                    </div>
                                </td>
                                <td class="text-center text-muted small">{{ $prog->slug }}</td>
                                <td class="text-center">
                                    @if($prog->estado)
                                        <span class="badge bg-soft-success text-success badge-status">
                                            <i class="mdi mdi-check-circle-outline me-1"></i> Activo
                                        </span>
                                    @else
                                        <span class="badge bg-soft-danger text-danger badge-status">
                                            <i class="mdi mdi-close-circle-outline me-1"></i> Inactivo
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center pe-4">
                                    <form action="{{ route('programas.toggle', $prog->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-toggle {{ $prog->estado ? 'btn-soft-danger' : 'btn-soft-success' }}" 
                                                data-bs-toggle="tooltip" title="{{ $prog->estado ? 'Desactivar Programa' : 'Activar Programa' }}">
                                            <i class="mdi {{ $prog->estado ? 'mdi-eye-off' : 'mdi-eye' }} font-18"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Panel Lateral: Nuevo Programa -->
    <div class="col-lg-4">
        <div class="card shadow-sm border-0 rounded-lg">
            <div class="card-header bg-white border-bottom py-3">
                <h5 class="mb-0 fw-bold text-dark"><i class="mdi mdi-plus-circle-outline text-primary me-1"></i> Nuevo Programa</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('programas.store') }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label class="form-label text-muted fw-bold small text-uppercase">Nombre Oficial del Programa</label>
                        <input type="text" name="nombre" class="form-control form-control-lg border-2 shadow-none" 
                               placeholder="Ej: Ciclo Intensivo 2026" required>
                        <div class="mt-2 p-2 bg-light rounded">
                            <small class="text-muted d-block">
                                <i class="mdi mdi-information-outline me-1"></i> 
                                El identificador para URLs se generará automáticamente a partir del nombre.
                            </small>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100 py-2 fw-bold shadow-sm">
                        <i class="mdi mdi-content-save-outline me-1"></i> Guardar Programa Académico
                    </button>
                </form>
            </div>
        </div>

        <!-- Tip Informativo -->
        <div class="card bg-soft-info border-0 mt-3">
            <div class="card-body">
                <div class="d-flex">
                    <i class="mdi mdi-shield-check-outline text-info fs-24 me-2 mr-2"></i>
                    <div>
                        <h6 class="text-info fw-bold mb-1">Control de Integridad</h6>
                        <p class="mb-0 fs-12 text-info-50">Los programas son la base del sistema. Desactivar un programa impedirá nuevas inscripciones pero no afectará los registros históricos.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
    $(document).ready(function() {
        // Inicializar tooltips para los botones
        $('[data-bs-toggle="tooltip"]').tooltip();
        
        // Animación suave de entrada
        $('.card').addClass('animate__animated animate__fadeInUp');
    });
</script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
@endpush
