@extends('layouts.cepre')

@section('title', 'Programas Académicos | Administrador')

@section('content')
<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Programas Académicos</li>
                        </ol>
                    </div>
                    <h4 class="page-title">Gestión de Programas Académicos</h4>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 fw-bold">Lista de Programas</h5>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>ID</th>
                                        <th>Nombre</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($programas as $prog)
                                    <tr>
                                        <td>{{ $prog->id }}</td>
                                        <td><strong>{{ $prog->nombre }}</strong></td>
                                        <td>
                                            @if($prog->estado)
                                                <span class="badge bg-success-lighten text-success rounded-pill px-3">Activo</span>
                                            @else
                                                <span class="badge bg-danger-lighten text-danger rounded-pill px-3">Inactivo</span>
                                            @endif
                                        </td>
                                        <td>
                                            <form action="{{ route('programas.toggle', $prog->id) }}" method="POST" style="display:inline;">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-secondary rounded-circle" data-bs-toggle="tooltip" title="{{ $prog->estado ? 'Desactivar' : 'Activar' }}">
                                                    <i data-feather="{{ $prog->estado ? 'eye-off' : 'eye' }}" style="width:16px; height:16px;"></i>
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

            <div class="col-md-6">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-bold">Nuevo Programa</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('programas.store') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">Nombre del Programa</label>
                                <input type="text" name="nombre" class="form-control" placeholder="Ej: Reforzamiento Escolar 2026" required>
                                <small class="text-muted">El slug se generará automáticamente.</small>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 py-2">
                                <i data-feather="plus-circle" class="me-1" style="width:18px; height:18px;"></i> Guardar Programa
                            </button>
                        </form>
                    </div>
                </div>

                <div class="mt-4">
                    <div class="alert alert-info border-0 shadow-sm" role="alert">
                        <h6 class="alert-heading fw-bold"><i data-feather="info" class="me-1"></i> Información</h6>
                        <p class="mb-0 small">Los programas registrados aquí son los que permiten organizar las inscripciones y los ciclos académicos del sistema.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
    // Inicializar Tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
      return new bootstrap.Tooltip(tooltipTriggerEl)
    })
</script>
@endpush
