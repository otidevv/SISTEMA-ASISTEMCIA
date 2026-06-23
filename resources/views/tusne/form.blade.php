@extends('layouts.app')

@section('title', $concepto->exists ? 'Editar Concepto TUSNE' : 'Nuevo Concepto TUSNE')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0"><i class="mdi mdi-tag-outline me-2"></i>{{ $concepto->exists ? 'Editar' : 'Nuevo' }} Concepto TUSNE</h4>
        <a href="{{ route('tusne.index') }}" class="btn btn-secondary btn-sm"><i class="mdi mdi-arrow-left"></i> Volver</a>
    </div>

    @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif

    <div class="card"><div class="card-body">
        <form method="POST" action="{{ $concepto->exists ? route('tusne.update', $concepto->id) : route('tusne.store') }}">
            @csrf
            @if($concepto->exists) @method('PUT') @endif
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label fw-bold">Código <span class="text-danger">*</span></label>
                    <input type="text" name="codigo" class="form-control" value="{{ old('codigo', $concepto->codigo) }}" required>
                    <small class="text-muted">Debe coincidir con el código de la API de pagos (ej. 372).</small>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Nombre <span class="text-danger">*</span></label>
                    <input type="text" name="nombre" class="form-control" value="{{ old('nombre', $concepto->nombre) }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Costo (S/) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" min="0" name="costo" class="form-control" value="{{ old('costo', $concepto->costo ?? 0) }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Categoría <span class="text-danger">*</span></label>
                    <select name="categoria" class="form-select">
                        @foreach(['tramite' => 'Trámite', 'matricula' => 'Matrícula', 'constancia' => 'Constancia', 'justificacion' => 'Justificación', 'otro' => 'Otro'] as $k => $v)
                            <option value="{{ $k }}" {{ old('categoria', $concepto->categoria) === $k ? 'selected' : '' }}>{{ $v }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold">Año / vigencia</label>
                    <input type="text" name="anio" class="form-control" value="{{ old('anio', $concepto->anio ?? '2026') }}">
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <div class="form-check me-3">
                        <input type="checkbox" class="form-check-input" id="requiere_pago" name="requiere_pago" value="1" {{ old('requiere_pago', $concepto->requiere_pago ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="requiere_pago">Requiere pago</label>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="activo" name="activo" value="1" {{ old('activo', $concepto->activo ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="activo">Activo</label>
                    </div>
                </div>
                <div class="col-12">
                    <label class="form-label fw-bold">Descripción</label>
                    <textarea name="descripcion" class="form-control" rows="2">{{ old('descripcion', $concepto->descripcion) }}</textarea>
                </div>
            </div>
            <div class="mt-4">
                <button class="btn btn-primary"><i class="mdi mdi-content-save"></i> Guardar</button>
            </div>
        </form>
    </div></div>
</div>
@endsection
