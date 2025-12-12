@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('resultados-examenes.index') }}">Resultados de Exámenes</a></li>
                        <li class="breadcrumb-item active">Editar Resultado</li>
                    </ol>
                </div>
                <h4 class="page-title">Editar Resultado de Examen</h4>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('resultados-examenes.update', $resultado->id) }}" method="POST" enctype="multipart/form-data" id="resultadoForm">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="ciclo_id" class="form-label">Ciclo Académico <span class="text-danger">*</span></label>
                                    <select class="form-select" id="ciclo_id" name="ciclo_id" required>
                                        <option value="">Seleccione un ciclo</option>
                                        @foreach($ciclos as $ciclo)
                                            <option value="{{ $ciclo->id }}" {{ old('ciclo_id', $resultado->ciclo_id) == $ciclo->id ? 'selected' : '' }}>
                                                {{ $ciclo->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="fecha_examen" class="form-label">Fecha del Examen <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="fecha_examen" name="fecha_examen" 
                                           value="{{ old('fecha_examen', $resultado->fecha_examen->format('Y-m-d')) }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="nombre_examen" class="form-label">Nombre del Examen <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nombre_examen" name="nombre_examen" 
                                   value="{{ old('nombre_examen', $resultado->nombre_examen) }}" required>
                        </div>

                        <div class="mb-3">
                            <label for="descripcion" class="form-label">Descripción</label>
                            <textarea class="form-control" id="descripcion" name="descripcion" rows="3">{{ old('descripcion', $resultado->descripcion) }}</textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Tipo de Resultado <span class="text-danger">*</span></label>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="tipo_resultado" 
                                               id="tipo_pdf" value="pdf" {{ old('tipo_resultado', $resultado->tipo_resultado) == 'pdf' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="tipo_pdf">
                                            <i class="mdi mdi-file-pdf text-danger"></i> Solo PDF
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="tipo_resultado" 
                                               id="tipo_link" value="link" {{ old('tipo_resultado', $resultado->tipo_resultado) == 'link' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="tipo_link">
                                            <i class="mdi mdi-link text-primary"></i> Solo Link
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="tipo_resultado" 
                                               id="tipo_ambos" value="ambos" {{ old('tipo_resultado', $resultado->tipo_resultado) == 'ambos' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="tipo_ambos">
                                            <i class="mdi mdi-file-link text-success"></i> PDF y Link
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3" id="pdf-container">
                                    <label for="archivo_pdf" class="form-label">Archivo PDF</label>
                                    @if($resultado->tiene_pdf)
                                        <div class="alert alert-success">
                                            <i class="mdi mdi-file-pdf"></i> Archivo actual: {{ $resultado->nombre_archivo_pdf }}
                                            <br><small>{{ $resultado->tamanio_archivo_pdf }}</small>
                                        </div>
                                    @endif
                                    <input type="file" class="form-control" id="archivo_pdf" name="archivo_pdf" accept=".pdf">
                                    <small class="text-muted">Dejar vacío para mantener el archivo actual. Máximo: 10MB</small>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3" id="link-container">
                                    <label for="link_externo" class="form-label">Link Externo</label>
                                    <input type="url" class="form-control" id="link_externo" name="link_externo" 
                                           value="{{ old('link_externo', $resultado->link_externo) }}">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="orden" class="form-label">Orden de Visualización</label>
                                    <input type="number" class="form-control" id="orden" name="orden" 
                                           value="{{ old('orden', $resultado->orden) }}" min="0">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="form-check mt-4">
                                        <input class="form-check-input" type="checkbox" id="visible" name="visible" 
                                               value="1" {{ old('visible', $resultado->visible) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="visible">
                                            <strong>Publicado</strong>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="text-end">
                            <a href="{{ route('resultados-examenes.index') }}" class="btn btn-secondary">
                                <i class="mdi mdi-arrow-left"></i> Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="mdi mdi-content-save"></i> Actualizar Resultado
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('input[name="tipo_resultado"]').change(function() {
        const tipo = $(this).val();
        if (tipo === 'pdf') {
            $('#link-container').addClass('d-none');
            $('#pdf-container').removeClass('d-none');
        } else if (tipo === 'link') {
            $('#pdf-container').addClass('d-none');
            $('#link-container').removeClass('d-none');
        } else {
            $('#pdf-container').removeClass('d-none');
            $('#link-container').removeClass('d-none');
        }
    });

    // Initialize
    $('input[name="tipo_resultado"]:checked').trigger('change');
});
</script>
@endpush
