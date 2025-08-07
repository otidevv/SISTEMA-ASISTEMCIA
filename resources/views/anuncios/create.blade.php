@extends('layouts.app')

@section('title', 'Crear Anuncio')

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb mejorado -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        @can('announcements_view')
                            <li class="breadcrumb-item"><a href="{{ route('anuncios.index') }}">Anuncios</a></li>
                        @endcan
                        <li class="breadcrumb-item active">Crear Nuevo</li>
                    </ol>
                </div>
                <h4 class="page-title">
                    <i class="bi bi-plus-circle me-1"></i>
                    Crear Nuevo Anuncio
                </h4>
            </div>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <strong>¡Hay errores en el formulario!</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form action="{{ route('anuncios.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        
        <div class="row">
            <!-- Contenido Principal -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-file-text me-2"></i>Contenido del Anuncio
                        </h5>
                    </div>
                    <div class="card-body">
                        <!-- Título -->
                        <div class="form-group mb-4">
                            <label for="titulo" class="form-label fw-bold">
                                <i class="bi bi-type me-1"></i>Título <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control form-control-lg @error('titulo') is-invalid @enderror" 
                                   id="titulo" name="titulo" value="{{ old('titulo') }}" 
                                   required maxlength="255" 
                                   placeholder="Escriba un título claro y descriptivo">
                            @error('titulo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                <span id="titulo-count">0</span>/255 caracteres
                            </div>
                        </div>

                        <!-- Descripción Breve -->
                        <div class="form-group mb-4">
                            <label for="descripcion" class="form-label fw-bold">
                                <i class="bi bi-card-text me-1"></i>Descripción Breve
                            </label>
                            <textarea class="form-control @error('descripcion') is-invalid @enderror" 
                                      id="descripcion" name="descripcion" rows="3" maxlength="500"
                                      placeholder="Resumen del anuncio (se usa en notificaciones y vista previa)">{{ old('descripcion') }}</textarea>
                            @error('descripcion')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                <span id="descripcion-count">0</span>/500 caracteres • Opcional pero recomendado
                            </div>
                        </div>

                        <!-- Contenido Principal -->
                        <div class="form-group mb-4">
                            <label for="contenido" class="form-label fw-bold">
                                <i class="bi bi-file-richtext me-1"></i>Contenido Completo <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control @error('contenido') is-invalid @enderror" 
                                      id="contenido" name="contenido" rows="10" required
                                      placeholder="Escriba aquí el contenido completo del anuncio...">{{ old('contenido') }}</textarea>
                            @error('contenido')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                <i class="bi bi-info-circle me-1"></i>Este será el contenido principal que verán los usuarios
                            </div>
                        </div>

                        <!-- Vista Previa -->
                        <div class="card bg-light">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="bi bi-eye me-1"></i>Vista Previa
                                </h6>
                            </div>
                            <div class="card-body">
                                <div id="preview-container">
                                    <div class="text-muted text-center py-3">
                                        <i class="bi bi-eye-slash"></i>
                                        <p class="mb-0">Escriba el título y contenido para ver la vista previa</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Imagen -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-image me-2"></i>Imagen del Anuncio
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="form-group mb-3">
                            <label for="imagen" class="form-label">Seleccionar Imagen</label>
                            <input type="file" class="form-control @error('imagen') is-invalid @enderror" 
                                   id="imagen" name="imagen" accept="image/*">
                            @error('imagen')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                <i class="bi bi-info-circle me-1"></i>Formatos permitidos: JPG, PNG, GIF • Tamaño máximo: 2MB
                            </div>
                        </div>

                        <!-- Preview de imagen -->
                        <div id="image-preview" style="display: none;">
                            <div class="mt-3">
                                <label class="form-label">Vista previa:</label>
                                <div>
                                    <img id="preview-img" src="" class="img-thumbnail" style="max-height: 200px;">
                                    <button type="button" class="btn btn-sm btn-outline-danger ms-2" id="remove-image">
                                        <i class="bi bi-x-circle"></i> Quitar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Configuración -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-gear me-2"></i>Configuración
                        </h5>
                    </div>
                    <div class="card-body">
                        <!-- Estado Activo -->
                        <div class="form-group mb-4">
                            <div class="form-check form-switch">
                                <input type="checkbox" class="form-check-input" id="es_activo" name="es_activo" 
                                       value="1" {{ old('es_activo', true) ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold" for="es_activo">
                                    <i class="bi bi-toggle-on me-1"></i>Anuncio Activo
                                </label>
                            </div>
                            <small class="form-text text-muted">Si está desactivado, el anuncio no será visible para los usuarios</small>
                        </div>

                        <!-- Tipo de Anuncio -->
                        <div class="form-group mb-4">
                            <label for="tipo" class="form-label fw-bold">
                                <i class="bi bi-tag me-1"></i>Tipo de Anuncio <span class="text-danger">*</span>
                            </label>
                            <select class="form-select @error('tipo') is-invalid @enderror" id="tipo" name="tipo" required>
                                <option value="informativo" {{ old('tipo', 'informativo') == 'informativo' ? 'selected' : '' }}>
                                    📄 Informativo
                                </option>
                                <option value="importante" {{ old('tipo') == 'importante' ? 'selected' : '' }}>
                                    ⚠️ Importante
                                </option>
                                <option value="urgente" {{ old('tipo') == 'urgente' ? 'selected' : '' }}>
                                    🚨 Urgente
                                </option>
                                <option value="mantenimiento" {{ old('tipo') == 'mantenimiento' ? 'selected' : '' }}>
                                    🔧 Mantenimiento
                                </option>
                                <option value="evento" {{ old('tipo') == 'evento' ? 'selected' : '' }}>
                                    📅 Evento
                                </option>
                            </select>
                            @error('tipo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Prioridad -->
                        <div class="form-group mb-4">
                            <label for="prioridad" class="form-label fw-bold">
                                <i class="bi bi-exclamation-triangle me-1"></i>Prioridad <span class="text-danger">*</span>
                            </label>
                            <select class="form-select @error('prioridad') is-invalid @enderror" id="prioridad" name="prioridad" required>
                                <option value="1" {{ old('prioridad', 1) == 1 ? 'selected' : '' }}>
                                    🔵 Baja (Información general)
                                </option>
                                <option value="2" {{ old('prioridad') == 2 ? 'selected' : '' }}>
                                    🟢 Media (Información relevante)
                                </option>
                                <option value="3" {{ old('prioridad') == 3 ? 'selected' : '' }}>
                                    🟡 Alta (Requiere atención)
                                </option>
                                <option value="4" {{ old('prioridad') == 4 ? 'selected' : '' }}>
                                    🔴 Crítica (Acción inmediata)
                                </option>
                            </select>
                            @error('prioridad')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Dirigido A -->
                        <div class="form-group mb-4">
                            <label for="dirigido_a" class="form-label fw-bold">
                                <i class="bi bi-people me-1"></i>Dirigido A <span class="text-danger">*</span>
                            </label>
                            <select class="form-select @error('dirigido_a') is-invalid @enderror" id="dirigido_a" name="dirigido_a" required>
                                <option value="todos" {{ old('dirigido_a', 'todos') == 'todos' ? 'selected' : '' }}>
                                    👥 Todos los usuarios
                                </option>
                                <option value="estudiantes" {{ old('dirigido_a') == 'estudiantes' ? 'selected' : '' }}>
                                    🎓 Solo Estudiantes
                                </option>
                                <option value="docentes" {{ old('dirigido_a') == 'docentes' ? 'selected' : '' }}>
                                    👨‍🏫 Solo Docentes
                                </option>
                                <option value="administrativos" {{ old('dirigido_a') == 'administrativos' ? 'selected' : '' }}>
                                    💼 Solo Administrativos
                                </option>
                                <option value="padres" {{ old('dirigido_a') == 'padres' ? 'selected' : '' }}>
                                    👨‍👩‍👧‍👦 Solo Padres de Familia
                                </option>
                            </select>
                            @error('dirigido_a')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Programación -->
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-calendar me-2"></i>Programación
                        </h5>
                    </div>
                    <div class="card-body">
                        <!-- Fecha de Publicación -->
                        <div class="form-group mb-3">
                            <label for="fecha_publicacion" class="form-label fw-bold">
                                <i class="bi bi-calendar-plus me-1"></i>Fecha de Publicación
                            </label>
                            <input type="datetime-local" class="form-control @error('fecha_publicacion') is-invalid @enderror" 
                                   id="fecha_publicacion" name="fecha_publicacion" 
                                   value="{{ old('fecha_publicacion', now()->format('Y-m-d\TH:i')) }}">
                            @error('fecha_publicacion')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Dejar vacío para publicar inmediatamente</small>
                        </div>

                        <div class="form-group mb-4">
                            <label for="fecha_expiracion" class="form-label fw-bold">
                                <i class="bi bi-calendar-x me-1"></i>Fecha de Expiración
                            </label>
                            <input type="datetime-local" class="form-control @error('fecha_expiracion') is-invalid @enderror" 
                                   id="fecha_expiracion" name="fecha_expiracion" 
                                   value="{{ old('fecha_expiracion') }}">
                            @error('fecha_expiracion')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Dejar vacío si no tiene fecha límite</small>
                        </div>

                        <!-- Enviar Notificación -->
                        <div class="form-group mb-3">
                            <div class="form-check form-switch">
                                <input type="checkbox" class="form-check-input" id="enviar_notificacion" name="enviar_notificacion" value="1">
                                <label class="form-check-label fw-bold" for="enviar_notificacion">
                                    <i class="bi bi-bell me-1"></i>Enviar Notificación
                                </label>
                            </div>
                            <small class="form-text text-muted">Los usuarios recibirán una notificación sobre este anuncio</small>
                        </div>
                    </div>
                </div>

                <!-- Botones de Acción -->
                <div class="card">
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="bi bi-check-circle me-2"></i>Crear Anuncio
                            </button>
                            
                            <button type="button" class="btn btn-outline-primary" id="btn-borrador">
                                <i class="bi bi-save me-2"></i>Guardar como Borrador
                            </button>
                            
                            @can('announcements_view')
                                <a href="{{ route('anuncios.index') }}" class="btn btn-secondary">
                                    <i class="bi bi-arrow-left me-2"></i>Volver a Lista
                                </a>
                            @else
                                <button type="button" class="btn btn-secondary" onclick="window.history.back()">
                                    <i class="bi bi-x-circle me-2"></i>Cancelar
                                </button>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('styles')
<!-- Cargar Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

<style>
.form-control-lg {
    font-size: 1.1rem;
    font-weight: 500;
}

.form-label.fw-bold {
    color: #495057;
    margin-bottom: 0.75rem;
}

.card-header.bg-primary,
.card-header.bg-success,
.card-header.bg-info {
    border: none;
}

.form-check-input:checked {
    background-color: #198754;
    border-color: #198754;
}

.form-select option {
    padding: 0.5rem;
}

#preview-container {
    min-height: 100px;
    max-height: 300px;
    overflow-y: auto;
}

.preview-title {
    font-size: 1.25rem;
    font-weight: bold;
    color: #212529;
    margin-bottom: 0.5rem;
}

.preview-content {
    color: #6c757d;
    line-height: 1.5;
}

.btn-lg {
    font-weight: 600;
}

.page-title-box {
    background: #fff;
    padding: 20px 0;
    margin-bottom: 20px;
}

.breadcrumb-item a {
    text-decoration: none;
    color: #6c757d;
}

.breadcrumb-item a:hover {
    color: #0d6efd;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Contadores de caracteres
    const tituloInput = document.getElementById('titulo');
    const descripcionInput = document.getElementById('descripcion');
    const contenidoInput = document.getElementById('contenido');
    
    // Contador para título
    tituloInput.addEventListener('input', function() {
        document.getElementById('titulo-count').textContent = this.value.length;
        updatePreview();
    });
    
    // Contador para descripción
    descripcionInput.addEventListener('input', function() {
        document.getElementById('descripcion-count').textContent = this.value.length;
        updatePreview();
    });
    
    // Vista previa
    contenidoInput.addEventListener('input', updatePreview);
    
    function updatePreview() {
        const titulo = tituloInput.value.trim();
        const contenido = contenidoInput.value.trim();
        const previewContainer = document.getElementById('preview-container');
        
        if (titulo || contenido) {
            let html = '';
            if (titulo) {
                html += `<div class="preview-title">${titulo}</div>`;
            }
            if (contenido) {
                html += `<div class="preview-content">${contenido.replace(/\n/g, '<br>')}</div>`;
            }
            previewContainer.innerHTML = html;
        } else {
            previewContainer.innerHTML = `
                <div class="text-muted text-center py-3">
                    <i class="bi bi-eye-slash"></i>
                    <p class="mb-0">Escriba el título y contenido para ver la vista previa</p>
                </div>
            `;
        }
    }
    
    // Preview de imagen
    const imagenInput = document.getElementById('imagen');
    const imagePreview = document.getElementById('image-preview');
    const previewImg = document.getElementById('preview-img');
    const removeButton = document.getElementById('remove-image');
    
    imagenInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                imagePreview.style.display = 'block';
            };
            reader.readAsDataURL(file);
        }
    });
    
    removeButton.addEventListener('click', function() {
        imagenInput.value = '';
        imagePreview.style.display = 'none';
        previewImg.src = '';
    });
    
    // Validación de fechas
    const fechaPublicacion = document.getElementById('fecha_publicacion');
    const fechaExpiracion = document.getElementById('fecha_expiracion');
    
    fechaPublicacion.addEventListener('change', function() {
        if (fechaExpiracion.value && fechaExpiracion.value < this.value) {
            fechaExpiracion.value = '';
            alert('⚠️ La fecha de expiración debe ser posterior a la fecha de publicación');
        }
    });
    
    fechaExpiracion.addEventListener('change', function() {
        if (fechaPublicacion.value && this.value < fechaPublicacion.value) {
            this.value = '';
            alert('⚠️ La fecha de expiración debe ser posterior a la fecha de publicación');
        }
    });
    
    // Guardar como borrador
    document.getElementById('btn-borrador').addEventListener('click', function() {
        // Desactivar el anuncio para guardarlo como borrador
        document.getElementById('es_activo').checked = false;
        
        // Cambiar el texto del botón principal
        const submitBtn = document.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="bi bi-save me-2"></i>Guardando como Borrador...';
        submitBtn.disabled = true;
        
        // Enviar el formulario
        document.querySelector('form').submit();
    });
    
    // Confirmación al salir si hay cambios
    let hasChanges = false;
    const inputs = document.querySelectorAll('input, textarea, select');
    inputs.forEach(input => {
        input.addEventListener('change', () => hasChanges = true);
    });
    
    window.addEventListener('beforeunload', function(e) {
        if (hasChanges) {
            e.preventDefault();
            e.returnValue = '¿Está seguro de salir? Los cambios no guardados se perderán.';
        }
    });
    
    // No mostrar confirmación al enviar el formulario
    document.querySelector('form').addEventListener('submit', () => hasChanges = false);
});
</script>
@endpush
@endsection