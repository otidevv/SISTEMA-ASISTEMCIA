@extends('layouts.app')

@section('title', 'Editar Anuncio')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        {{-- ✅ CAMBIO: Usar hasPermission granular --}}
                        @if (Auth::user()->hasPermission('Ver Anuncios'))
                            <li class="breadcrumb-item"><a href="{{ route('anuncios.index') }}">Anuncios</a></li>
                        @endif
                        <li class="breadcrumb-item active">Editar</li>
                    </ol>
                </div>
                <h4 class="page-title">
                    <i data-feather="edit" class="me-1"></i>
                    Editar Anuncio
                </h4>
            </div>
        </div>
    </div>

    <!-- Mostrar errores -->
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('anuncios.update', $anuncio) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        
        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Información del Anuncio</h5>
                    </div>
                    <div class="card-body">
                        <!-- Título -->
                        <div class="mb-3">
                            <label for="titulo" class="form-label">Título <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('titulo') is-invalid @enderror" 
                                   id="titulo" name="titulo" value="{{ old('titulo', $anuncio->titulo) }}" required>
                            @error('titulo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Descripción -->
                        <div class="mb-3">
                            <label for="descripcion" class="form-label">Descripción</label>
                            <textarea class="form-control @error('descripcion') is-invalid @enderror" 
                                      id="descripcion" name="descripcion" rows="2">{{ old('descripcion', $anuncio->descripcion) }}</textarea>
                            @error('descripcion')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Contenido -->
                        <div class="mb-3">
                            <label for="contenido" class="form-label">Contenido <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('contenido') is-invalid @enderror" 
                                      id="contenido" name="contenido" rows="6" required>{{ old('contenido', $anuncio->contenido) }}</textarea>
                            @error('contenido')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Imagen Actual y Nueva -->
                        @if($anuncio->imagen)
                            <div class="mb-3">
                                <label class="form-label">Imagen Actual</label>
                                <div class="mb-2">
                                    <img src="{{ asset('storage/' . $anuncio->imagen) }}" 
                                         alt="Imagen del anuncio" 
                                         class="img-thumbnail" 
                                         style="max-height: 200px;">
                                </div>
                            </div>
                        @endif

                        <!-- Nueva Imagen -->
                        <div class="mb-3">
                            <label for="imagen" class="form-label">
                                {{ $anuncio->imagen ? 'Cambiar Imagen' : 'Agregar Imagen' }}
                            </label>
                            <input type="file" class="form-control @error('imagen') is-invalid @enderror" 
                                   id="imagen" name="imagen" accept="image/*">
                            <small class="form-text text-muted">Formatos: JPG, PNG, GIF (máx. 2MB)</small>
                            @error('imagen')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Configuración</h5>
                    </div>
                    <div class="card-body">
                        <!-- Estado -->
                        <div class="mb-3">
                            <label for="es_activo" class="form-label">Estado</label>
                            <select class="form-select" id="es_activo" name="es_activo">
                                <option value="1" {{ old('es_activo', $anuncio->es_activo) == 1 ? 'selected' : '' }}>Activo</option>
                                <option value="0" {{ old('es_activo', $anuncio->es_activo) == 0 ? 'selected' : '' }}>Inactivo</option>
                            </select>
                        </div>

                        <!-- Tipo -->
                        <div class="mb-3">
                            <label for="tipo" class="form-label">Tipo</label>
                            <select class="form-select" id="tipo" name="tipo" required>
                                <option value="informativo" {{ old('tipo', $anuncio->tipo) == 'informativo' ? 'selected' : '' }}>Informativo</option>
                                <option value="importante" {{ old('tipo', $anuncio->tipo) == 'importante' ? 'selected' : '' }}>Importante</option>
                                <option value="urgente" {{ old('tipo', $anuncio->tipo) == 'urgente' ? 'selected' : '' }}>Urgente</option>
                                <option value="evento" {{ old('tipo', $anuncio->tipo) == 'evento' ? 'selected' : '' }}>Evento</option>
                                <option value="mantenimiento" {{ old('tipo', $anuncio->tipo) == 'mantenimiento' ? 'selected' : '' }}>Mantenimiento</option>
                            </select>
                        </div>

                        <!-- Prioridad -->
                        <div class="mb-3">
                            <label for="prioridad" class="form-label">Prioridad</label>
                            <select class="form-select" id="prioridad" name="prioridad" required>
                                <option value="1" {{ old('prioridad', $anuncio->prioridad) == 1 ? 'selected' : '' }}>Baja</option>
                                <option value="2" {{ old('prioridad', $anuncio->prioridad) == 2 ? 'selected' : '' }}>Normal</option>
                                <option value="3" {{ old('prioridad', $anuncio->prioridad) == 3 ? 'selected' : '' }}>Alta</option>
                                <option value="4" {{ old('prioridad', $anuncio->prioridad) == 4 ? 'selected' : '' }}>Crítica</option>
                            </select>
                        </div>

                        <!-- Dirigido a -->
                        <div class="mb-3">
                            <label for="dirigido_a" class="form-label">Dirigido a</label>
                            <select class="form-select" id="dirigido_a" name="dirigido_a" required>
                                <option value="todos" {{ old('dirigido_a', $anuncio->dirigido_a) == 'todos' ? 'selected' : '' }}>Todos</option>
                                <option value="estudiantes" {{ old('dirigido_a', $anuncio->dirigido_a) == 'estudiantes' ? 'selected' : '' }}>Estudiantes</option>
                                <option value="docentes" {{ old('dirigido_a', $anuncio->dirigido_a) == 'docentes' ? 'selected' : '' }}>Docentes</option>
                                <option value="administrativos" {{ old('dirigido_a', $anuncio->dirigido_a) == 'administrativos' ? 'selected' : '' }}>Administrativos</option>
                                <option value="padres" {{ old('dirigido_a', $anuncio->dirigido_a) == 'padres' ? 'selected' : '' }}>Padres</option>
                            </select>
                        </div>

                        <!-- Fechas de Vigencia -->
                        <div class="mb-3">
                            <label for="fecha_publicacion" class="form-label">Fecha de Publicación</label>
                            <input type="datetime-local" class="form-control @error('fecha_publicacion') is-invalid @enderror" 
                                   id="fecha_publicacion" name="fecha_publicacion" 
                                   value="{{ old('fecha_publicacion', $anuncio->fecha_publicacion ? $anuncio->fecha_publicacion->format('Y-m-d\TH:i') : '') }}">
                            @error('fecha_publicacion')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="fecha_expiracion" class="form-label">Fecha de Expiración</label>
                            <input type="datetime-local" class="form-control @error('fecha_expiracion') is-invalid @enderror" 
                                   id="fecha_expiracion" name="fecha_expiracion" 
                                   value="{{ old('fecha_expiracion', $anuncio->fecha_expiracion ? $anuncio->fecha_expiracion->format('Y-m-d\TH:i') : '') }}">
                            @error('fecha_expiracion')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Botones -->
                <div class="card">
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success">
                                <i data-feather="save" class="me-1"></i>
                                Actualizar Anuncio
                            </button>
                            {{-- ✅ CAMBIO: Usar hasPermission granular --}}
                            @if (Auth::user()->hasPermission('Ver Anuncios'))
                                <a href="{{ route('anuncios.index') }}" class="btn btn-secondary">
                                    <i data-feather="arrow-left" class="me-1"></i>
                                    Volver a Lista
                                </a>
                            @else
                                <button type="button" class="btn btn-secondary" onclick="window.history.back()">
                                    <i data-feather="arrow-left" class="me-1"></i>
                                    Cancelar
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Activar feather icons
    feather.replace();
    
    // Validar fechas
    const fechaPublicacion = document.getElementById('fecha_publicacion');
    const fechaExpiracion = document.getElementById('fecha_expiracion');
    
    if (fechaPublicacion && fechaExpiracion) {
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
    }
});
</script>
@endpush
@endsection