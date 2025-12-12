@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Page Title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('resultados-examenes.index') }}">Resultados de Exámenes</a></li>
                        <li class="breadcrumb-item active">Crear Resultado</li>
                    </ol>
                </div>
                <h4 class="page-title">Crear Nuevo Resultado de Examen</h4>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('resultados-examenes.store') }}" method="POST" enctype="multipart/form-data" id="resultadoForm">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="ciclo_id" class="form-label">Ciclo Académico <span class="text-danger">*</span></label>
                                    <select class="form-select" id="ciclo_id" name="ciclo_id" required>
                                        <option value="">Seleccione un ciclo</option>
                                        @foreach($ciclos as $ciclo)
                                            <option value="{{ $ciclo->id }}" {{ old('ciclo_id') == $ciclo->id ? 'selected' : '' }}>
                                                {{ $ciclo->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('ciclo_id')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="fecha_examen" class="form-label">Fecha del Examen <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="fecha_examen" name="fecha_examen" 
                                           value="{{ old('fecha_examen') }}" required>
                                    @error('fecha_examen')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="nombre_examen" class="form-label">Nombre del Examen <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nombre_examen" name="nombre_examen" 
                                   placeholder="Ej: Examen Final - Ciclo 2024-I" value="{{ old('nombre_examen') }}" required>
                            @error('nombre_examen')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="descripcion" class="form-label">Descripción</label>
                            <textarea class="form-control" id="descripcion" name="descripcion" rows="3" 
                                      placeholder="Descripción adicional del examen (opcional)">{{ old('descripcion') }}</textarea>
                            @error('descripcion')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Tipo de Resultado <span class="text-danger">*</span></label>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="tipo_resultado" 
                                               id="tipo_pdf" value="pdf" {{ old('tipo_resultado', 'pdf') == 'pdf' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="tipo_pdf">
                                            <i class="mdi mdi-file-pdf text-danger"></i> Solo PDF
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="tipo_resultado" 
                                               id="tipo_link" value="link" {{ old('tipo_resultado') == 'link' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="tipo_link">
                                            <i class="mdi mdi-link text-primary"></i> Solo Link
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="tipo_resultado" 
                                               id="tipo_ambos" value="ambos" {{ old('tipo_resultado') == 'ambos' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="tipo_ambos">
                                            <i class="mdi mdi-file-link text-success"></i> PDF y Link
                                        </label>
                                    </div>
                                </div>
                            </div>
                            @error('tipo_resultado')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3" id="pdf-container">
                                    <label for="archivo_pdf" class="form-label">
                                        Archivo PDF <span class="text-danger pdf-required">*</span>
                                    </label>
                                    <input type="file" class="form-control" id="archivo_pdf" name="archivo_pdf" accept=".pdf">
                                    <small class="text-muted">Tamaño máximo: 10MB</small>
                                    @error('archivo_pdf')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                    <div id="pdf-preview" class="mt-2"></div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3" id="link-container">
                                    <label for="link_externo" class="form-label">
                                        Link Externo <span class="text-danger link-required" style="display:none;">*</span>
                                    </label>
                                    <input type="url" class="form-control" id="link_externo" name="link_externo" 
                                           placeholder="https://ejemplo.com/resultados" value="{{ old('link_externo') }}">
                                    @error('link_externo')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="orden" class="form-label">Orden de Visualización</label>
                                    <input type="number" class="form-control" id="orden" name="orden" 
                                           value="{{ old('orden', 0) }}" min="0">
                                    <small class="text-muted">Menor número aparece primero</small>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="form-check mt-4">
                                        <input class="form-check-input" type="checkbox" id="visible" name="visible" 
                                               value="1" {{ old('visible') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="visible">
                                            <strong>Publicar inmediatamente</strong>
                                            <br><small class="text-muted">Los estudiantes podrán ver este resultado</small>
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
                                <i class="mdi mdi-content-save"></i> Guardar Resultado
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
    // Handle tipo_resultado change
    $('input[name="tipo_resultado"]').change(function() {
        const tipo = $(this).val();
        updateRequiredFields(tipo);
    });

    // Initialize on page load
    updateRequiredFields($('input[name="tipo_resultado"]:checked').val());

    function updateRequiredFields(tipo) {
        const pdfInput = $('#archivo_pdf');
        const linkInput = $('#link_externo');
        const pdfRequired = $('.pdf-required');
        const linkRequired = $('.link-required');

        // Reset
        pdfInput.prop('required', false);
        linkInput.prop('required', false);
        pdfRequired.hide();
        linkRequired.hide();
        $('#pdf-container').removeClass('d-none');
        $('#link-container').removeClass('d-none');

        if (tipo === 'pdf') {
            pdfInput.prop('required', true);
            pdfRequired.show();
            $('#link-container').addClass('d-none');
        } else if (tipo === 'link') {
            linkInput.prop('required', true);
            linkRequired.show();
            $('#pdf-container').addClass('d-none');
        } else if (tipo === 'ambos') {
            pdfInput.prop('required', true);
            linkInput.prop('required', true);
            pdfRequired.show();
            linkRequired.show();
        }
    }

    // PDF file preview
    $('#archivo_pdf').change(function() {
        const file = this.files[0];
        const preview = $('#pdf-preview');
        
        if (file) {
            if (file.size > 10 * 1024 * 1024) {
                toastr.error('El archivo excede el tamaño máximo de 10MB');
                $(this).val('');
                preview.html('');
                return;
            }

            const fileSize = (file.size / 1024 / 1024).toFixed(2);
            preview.html(`
                <div class="alert alert-info">
                    <i class="mdi mdi-file-pdf"></i> ${file.name} (${fileSize} MB)
                </div>
            `);
        } else {
            preview.html('');
        }
    });

    // Form validation
    $('#resultadoForm').submit(function(e) {
        const tipo = $('input[name="tipo_resultado"]:checked').val();
        const hasPdf = $('#archivo_pdf')[0].files.length > 0;
        const hasLink = $('#link_externo').val().trim() !== '';

        if (tipo === 'pdf' && !hasPdf) {
            e.preventDefault();
            toastr.error('Debe subir un archivo PDF');
            return false;
        }

        if (tipo === 'link' && !hasLink) {
            e.preventDefault();
            toastr.error('Debe proporcionar un enlace');
            return false;
        }

        if (tipo === 'ambos' && (!hasPdf || !hasLink)) {
            e.preventDefault();
            toastr.error('Debe proporcionar tanto el PDF como el enlace');
            return false;
        }
    });
});
</script>
@endpush
