@extends('layouts.app')

@section('title', 'Editar Registro de Pago')

@push('css')
    <style>
        .form-label { font-weight: 600; color: #4b4b4b; }
        .form-control:focus, .form-select:focus {
            border-color: #f8cc53;
            box-shadow: 0 0 0 0.15rem rgba(248, 204, 83, 0.15);
        }
        .edit-info-banner {
            background-color: #fffaf0;
            border-left: 4px solid #f8cc53;
            border-radius: 0.5rem;
        }
    </style>
@endpush

@section('content')
    <!-- start page title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">Modificar Pago a Docente</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('pagos-docentes.index') }}">Pagos</a></li>
                        <li class="breadcrumb-item active">Editar</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <!-- end page title -->

    <div class="row justify-content-center">
        <div class="col-md-9">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-bottom py-3">
                    <h4 class="header-title mb-0"><i class="mdi mdi-account-edit-outline me-2 text-warning"></i>Modificar Registro de Pago</h4>
                </div>
                <div class="card-body p-4">
                    <div class="edit-info-banner p-3 mb-4">
                        <div class="d-flex align-items-center">
                            <i class="mdi mdi-information-variant font-size-24 me-3 text-warning"></i>
                            <div>
                                <h6 class="mb-1 fw-bold">Modo Edición</h6>
                                <p class="mb-0 text-muted small">Actualizando datos financieros para: <strong class="text-dark">{{ $pago->docente->nombre_completo }}</strong></p>
                            </div>
                        </div>
                    </div>

                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show border-0" role="alert">
                            <h6 class="alert-heading fw-bold">Hay errores en el formulario:</h6>
                            <ul class="mb-0 mt-1 small">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form action="{{ route('pagos-docentes.update', $pago->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label for="ciclo_id" class="form-label">Ciclo Académico</label>
                                <select name="ciclo_id" id="ciclo_id" class="form-select border-warning-subtle" required>
                                    @foreach($ciclos as $ciclo)
                                        <option value="{{ $ciclo->id }}" {{ (old('ciclo_id', $pago->ciclo_id) == $ciclo->id) ? 'selected' : '' }}>
                                            {{ $ciclo->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-4">
                                <label for="tarifa_por_hora" class="form-label">Tarifa de Pago (S/)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light fw-bold text-warning">S/</span>
                                    <input type="number" step="0.01" name="tarifa_por_hora" id="tarifa_por_hora" 
                                           class="form-control form-control-lg fw-bold" placeholder="0.00" 
                                           value="{{ old('tarifa_por_hora', $pago->tarifa_por_hora) }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label for="fecha_inicio" class="form-label text-muted small text-uppercase">Fecha de Inicio Vínculo</label>
                                <input type="date" name="fecha_inicio" id="fecha_inicio" 
                                       class="form-control" 
                                       value="{{ old('fecha_inicio', \Carbon\Carbon::parse($pago->fecha_inicio)->format('Y-m-d')) }}" required>
                            </div>
                            <div class="col-md-6 mb-4">
                                <label for="fecha_fin" class="form-label text-muted small text-uppercase">Fecha de Cierre (Opcional)</label>
                                <input type="date" name="fecha_fin" id="fecha_fin" 
                                       class="form-control" 
                                       value="{{ old('fecha_fin', $pago->fecha_fin ? \Carbon\Carbon::parse($pago->fecha_fin)->format('Y-m-d') : '') }}">
                                <div class="form-text mt-1">Deje vacío si el pago sigue vigente.</div>
                            </div>
                        </div>

                        <div class="mt-4 pt-3 border-top text-end">
                            <a href="{{ route('pagos-docentes.index') }}" class="btn btn-light px-4 me-2">
                                <i class="mdi mdi-arrow-left me-1"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-warning px-4 shadow-sm fw-bold">
                                <i class="mdi mdi-content-save-edit me-1"></i> Actualizar Registro
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection