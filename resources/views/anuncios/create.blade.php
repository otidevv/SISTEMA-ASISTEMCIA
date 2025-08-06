@extends('layouts.app')

@section('title', 'Crear Anuncio')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Crear Nuevo Anuncio</h4>
                    {{-- ✅ CAMBIO: Usar hasPermission granular --}}
                    @if (Auth::user()->hasPermission('Ver Anuncios'))
                        <a href="{{ route('anuncios.index') }}" class="btn btn-secondary">
                            <i data-feather="arrow-left"></i> Volver
                        </a>
                    @endif
                </div>
                
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('anuncios.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-8">
                                <!-- Título -->
                                <div class="form-group mb-3">
                                    <label for="titulo" class="form-label">Título <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('titulo') is-invalid @enderror" 
                                           id="titulo" name="titulo" value="{{ old('titulo') }}" required maxlength="255">
                                    @error('titulo')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Contenido -->
                                <div class="form-group mb-3">
                                    <label for="contenido" class="form-label">Contenido <span class="text-danger">*</span></label>
                                    <textarea class="form-control @error('contenido') is-invalid @enderror" 
                                              id="contenido" name="contenido" rows="8" required>{{ old('contenido') }}</textarea>
                                    @error('contenido')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Descripción Breve -->
                                <div class="form-group mb-3">
                                    <label for="descripcion" class="form-label">Descripción Breve</label>
                                    <textarea class="form-control @error('descripcion') is-invalid @enderror" 
                                              id="descripcion" name="descripcion" rows="3" maxlength="500"
                                              placeholder="Resumen del anuncio (se usa en notificaciones)">{{ old('descripcion') }}</textarea>
                                    <small class="form-text text-muted">Máximo 500 caracteres</small>
                                    @error('descripcion')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <!-- Estado -->
                                <div class="form-group mb-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="es_activo" name="es_activo" 
                                               value="1" {{ old('es_activo', true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="es_activo">
                                            Anuncio Activo
                                        </label>
                                    </div>
                                </div>

                                <!-- Prioridad -->
                                <div class="form-group mb-3">
                                    <label for="prioridad" class="form-label">Prioridad <span class="text-danger">*</span></label>
                                    <select class="form-control @error('prioridad') is-invalid @enderror" id="prioridad" name="prioridad" required>
                                        <option value="1" {{ old('prioridad', 1) == 1 ? 'selected' : '' }}>Baja</option>
                                        <option value="2" {{ old('prioridad') == 2 ? 'selected' : '' }}>Media</option>
                                        <option value="3" {{ old('prioridad') == 3 ? 'selected' : '' }}>Alta</option>
                                        <option value="4" {{ old('prioridad') == 4 ? 'selected' : '' }}>Crítica</option>
                                    </select>
                                    @error('prioridad')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Tipo -->
                                <div class="form-group mb-3">
                                    <label for="tipo" class="form-label">Tipo <span class="text-danger">*</span></label>
                                    <select class="form-control @error('tipo') is-invalid @enderror" id="tipo" name="tipo" required>
                                        <option value="informativo" {{ old('tipo', 'informativo') == 'informativo' ? 'selected' : '' }}>Informativo</option>
                                        <option value="importante" {{ old('tipo') == 'importante' ? 'selected' : '' }}>Importante</option>
                                        <option value="urgente" {{ old('tipo') == 'urgente' ? 'selected' : '' }}>Urgente</option>
                                        <option value="mantenimiento" {{ old('tipo') == 'mantenimiento' ? 'selected' : '' }}>Mantenimiento</option>
                                        <option value="evento" {{ old('tipo') == 'evento' ? 'selected' : '' }}>Evento</option>
                                    </select>
                                    @error('tipo')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Dirigido A -->
                                <div class="form-group mb-3">
                                    <label for="dirigido_a" class="form-label">Dirigido A <span class="text-danger">*</span></label>
                                    <select class="form-control @error('dirigido_a') is-invalid @enderror" id="dirigido_a" name="dirigido_a" required>
                                        <option value="todos" {{ old('dirigido_a', 'todos') == 'todos' ? 'selected' : '' }}>Todos</option>
                                        <option value="estudiantes" {{ old('dirigido_a') == 'estudiantes' ? 'selected' : '' }}>Estudiantes</option>
                                        <option value="docentes" {{ old('dirigido_a') == 'docentes' ? 'selected' : '' }}>Docentes</option>
                                        <option value="administrativos" {{ old('dirigido_a') == 'administrativos' ? 'selected' : '' }}>Administrativos</option>
                                        <option value="padres" {{ old('dirigido_a') == 'padres' ? 'selected' : '' }}>Padres</option>
                                    </select>
                                    @error('dirigido_a')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Fechas de Vigencia -->
                                <div class="form-group mb-3">
                                    <label for="fecha_publicacion" class="form-label">Fecha de Publicación</label>
                                    <input type="datetime-local" class="form-control @error('fecha_publicacion') is-invalid @enderror" 
                                           id="fecha_publicacion" name="fecha_publicacion" value="{{ old('fecha_publicacion', now()->format('Y-m-d\TH:i')) }}">
                                    <small class="form-text text-muted">Si no se especifica, se publica inmediatamente</small>
                                    @error('fecha_publicacion')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group mb-3">
                                    <label for="fecha_expiracion" class="form-label">Fecha de Expiración</label>
                                    <input type="datetime-local" class="form-control @error('fecha_expiracion') is-invalid @enderror" 
                                           id="fecha_expiracion" name="fecha_expiracion" value="{{ old('fecha_expiracion') }}">
                                    <small class="form-text text-muted">Si no se especifica, no expira</small>
                                    @error('fecha_expiracion')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Imagen -->
                                <div class="form-group mb-3">
                                    <label for="imagen" class="form-label">Imagen</label>
                                    <input type="file" class="form-control @error('imagen') is-invalid @enderror" 
                                           id="imagen" name="imagen" accept="image/*">
                                    <small class="form-text text-muted">Formatos: JPG, PNG, GIF (máx. 2MB)</small>
                                    @error('imagen')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Enviar Notificación -->
                                <div class="form-group mb-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="enviar_notificacion" name="enviar_notificacion" value="1">
                                        <label class="form-check-label" for="enviar_notificacion">
                                            Enviar notificación a los usuarios
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="d-flex justify-content-between">
                                    <button type="button" class="btn btn-secondary" onclick="window.history.back()">
                                        <i data-feather="x"></i> Cancelar
                                    </button>
                                    <button type="submit" class="btn btn-primary">
                                        <i data-feather="save"></i> Crear Anuncio
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Validar fechas
document.addEventListener('DOMContentLoaded', function() {
    const fechaPublicacion = document.getElementById('fecha_publicacion');
    const fechaExpiracion = document.getElementById('fecha_expiracion');
    
    fechaPublicacion.addEventListener('change', function() {
        if (fechaExpiracion.value && fechaExpiracion.value < this.value) {
            fechaExpiracion.value = '';
            alert('La fecha de expiración debe ser posterior a la fecha de publicación');
        }
    });
    
    fechaExpiracion.addEventListener('change', function() {
        if (fechaPublicacion.value && this.value < fechaPublicacion.value) {
            this.value = '';
            alert('La fecha de expiración debe ser posterior a la fecha de publicación');
        }
    });
});

// Activar feather icons
feather.replace();
</script>
@endpush
@endsection