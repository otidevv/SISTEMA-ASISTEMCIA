@extends('layouts.app')

@section('title', 'Gestión de Anuncios')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">
                        <i class="bi bi-megaphone"></i> Gestión de Anuncios
                    </h4>
                    {{-- ✅ USAR PERMISOS CORRECTOS --}}
                    @can('announcements_create')
                        <a href="{{ route('anuncios.create') }}" class="btn btn-primary">
                            <i class="bi bi-plus-circle"></i> Crear Nuevo Anuncio
                        </a>
                    @endcan
                </div>
                
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    {{-- Filtros rápidos --}}
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="btn-group" role="group" aria-label="Filtros">
                                <input type="radio" class="btn-check" name="filtro" id="todos" value="todos" checked>
                                <label class="btn btn-outline-primary" for="todos">
                                    <i class="bi bi-list"></i> Todos
                                </label>
                                
                                <input type="radio" class="btn-check" name="filtro" id="activos" value="activos">
                                <label class="btn btn-outline-success" for="activos">
                                    <i class="bi bi-check-circle"></i> Activos
                                </label>
                                
                                <input type="radio" class="btn-check" name="filtro" id="inactivos" value="inactivos">
                                <label class="btn btn-outline-secondary" for="inactivos">
                                    <i class="bi bi-pause-circle"></i> Inactivos
                                </label>
                                
                                <input type="radio" class="btn-check" name="filtro" id="urgentes" value="urgentes">
                                <label class="btn btn-outline-danger" for="urgentes">
                                    <i class="bi bi-exclamation-triangle"></i> Urgentes
                                </label>
                            </div>
                        </div>
                    </div>

                    {{-- Estadísticas rápidas --}}
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h6 class="card-title">Total Anuncios</h6>
                                            <h4>{{ $anuncios->total() ?? count($anuncios) }}</h4>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="bi bi-megaphone" style="font-size: 2rem;"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h6 class="card-title">Activos</h6>
                                            <h4>{{ $anuncios->where('es_activo', true)->count() }}</h4>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="bi bi-check-circle" style="font-size: 2rem;"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h6 class="card-title">Urgentes</h6>
                                            <h4>{{ $anuncios->where('tipo', 'urgente')->count() }}</h4>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="bi bi-exclamation-triangle" style="font-size: 2rem;"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <h6 class="card-title">Esta Semana</h6>
                                            <h4>{{ $anuncios->where('created_at', '>=', now()->startOfWeek())->count() }}</h4>
                                        </div>
                                        <div class="align-self-center">
                                            <i class="bi bi-calendar-week" style="font-size: 2rem;"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Anuncio</th>
                                    <th>Tipo</th>
                                    <th>Dirigido A</th>
                                    <th>Estado</th>
                                    <th>Prioridad</th>
                                    <th>Fecha</th>
                                    <th width="180">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($anuncios as $anuncio)
                                    <tr @if($anuncio->tipo === 'urgente') class="table-danger" @elseif($anuncio->tipo === 'importante') class="table-warning" @endif>
                                        <td>{{ $anuncio->id }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @if($anuncio->imagen)
                                                    <img src="{{ asset('storage/' . $anuncio->imagen) }}" 
                                                         class="rounded me-2" width="32" height="32" alt="Imagen">
                                                @else
                                                    <div class="bg-secondary rounded d-flex align-items-center justify-content-center me-2"
                                                         style="width: 32px; height: 32px; color: white; font-size: 12px;">
                                                        <i class="bi bi-megaphone"></i>
                                                    </div>
                                                @endif
                                                <div>
                                                    <div class="fw-bold">{{ Str::limit($anuncio->titulo, 30) }}</div>
                                                    @if($anuncio->descripcion)
                                                        <small class="text-muted">{{ Str::limit($anuncio->descripcion, 40) }}</small>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            @switch($anuncio->tipo)
                                                @case('urgente')
                                                    <span class="badge bg-danger">
                                                        <i class="bi bi-exclamation-triangle"></i> Urgente
                                                    </span>
                                                    @break
                                                @case('importante')
                                                    <span class="badge bg-warning text-dark">
                                                        <i class="bi bi-exclamation-circle"></i> Importante
                                                    </span>
                                                    @break
                                                @case('mantenimiento')
                                                    <span class="badge bg-secondary">
                                                        <i class="bi bi-tools"></i> Mantenimiento
                                                    </span>
                                                    @break
                                                @case('evento')
                                                    <span class="badge bg-success">
                                                        <i class="bi bi-calendar-event"></i> Evento
                                                    </span>
                                                    @break
                                                @default
                                                    <span class="badge bg-info">
                                                        <i class="bi bi-info-circle"></i> Informativo
                                                    </span>
                                            @endswitch
                                        </td>
                                        <td>
                                            <span class="badge bg-primary">
                                                <i class="bi bi-people"></i> {{ ucfirst($anuncio->dirigido_a) }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($anuncio->es_activo)
                                                @if(method_exists($anuncio, 'estaVigente') && $anuncio->estaVigente())
                                                    <span class="badge bg-success">
                                                        <i class="bi bi-check-circle"></i> Activo
                                                    </span>
                                                @else
                                                    <span class="badge bg-warning">
                                                        <i class="bi bi-clock"></i> Programado/Expirado
                                                    </span>
                                                @endif
                                            @else
                                                <span class="badge bg-secondary">
                                                    <i class="bi bi-pause-circle"></i> Inactivo
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            @switch($anuncio->prioridad)
                                                @case(4)
                                                    <span class="badge bg-danger">
                                                        <i class="bi bi-exclamation-triangle-fill"></i> Crítica
                                                    </span>
                                                    @break
                                                @case(3)
                                                    <span class="badge bg-warning">
                                                        <i class="bi bi-exclamation-triangle"></i> Alta
                                                    </span>
                                                    @break
                                                @case(2)
                                                    <span class="badge bg-info">
                                                        <i class="bi bi-info-circle"></i> Media
                                                    </span>
                                                    @break
                                                @default
                                                    <span class="badge bg-secondary">
                                                        <i class="bi bi-dash-circle"></i> Baja
                                                    </span>
                                            @endswitch
                                        </td>
                                        <td>
                                            <div>
                                                <small class="fw-bold">{{ $anuncio->created_at->format('d/m/Y') }}</small>
                                                <br>
                                                <small class="text-muted">{{ $anuncio->created_at->format('H:i') }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            {{-- BOTONES DE ACCIÓN MEJORADOS --}}
                                            <div class="d-flex gap-1">
                                                @can('announcements_view')
                                                    <button type="button" class="btn btn-info btn-sm" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#modal-detalle-{{ $anuncio->id }}" 
                                                            title="Ver detalles">
                                                        <i class="bi bi-eye"></i>
                                                    </button>
                                                @endcan
                                                
                                                @can('announcements_edit')
                                                    <a href="{{ route('anuncios.edit', $anuncio) }}" 
                                                       class="btn btn-warning btn-sm" 
                                                       title="Editar">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                @endcan

                                                @can('announcements_edit')
                                                    {{-- Botón Activar/Desactivar --}}
                                                    <form action="{{ route('anuncios.toggle-status', $anuncio) }}" 
                                                          method="POST" 
                                                          style="display:inline-block;">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" 
                                                                class="btn btn-{{ $anuncio->es_activo ? 'secondary' : 'success' }} btn-sm" 
                                                                title="{{ $anuncio->es_activo ? 'Desactivar' : 'Activar' }}">
                                                            <i class="bi bi-{{ $anuncio->es_activo ? 'pause-circle' : 'play-circle' }}"></i>
                                                        </button>
                                                    </form>
                                                @endcan
                                                
                                                @can('announcements_delete')
                                                    <form action="{{ route('anuncios.destroy', $anuncio) }}" 
                                                          method="POST" 
                                                          style="display:inline-block;"
                                                          onsubmit="return confirm('¿Está seguro de eliminar este anuncio?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger btn-sm" title="Eliminar">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>

                                    {{-- Modal de detalles --}}
                                    <div class="modal fade" id="modal-detalle-{{ $anuncio->id }}" tabindex="-1">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header bg-primary text-white">
                                                    <h5 class="modal-title">
                                                        <i class="bi bi-info-circle me-2"></i>
                                                        Detalles del Anuncio
                                                    </h5>
                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row">
                                                        <div class="col-md-8">
                                                            <h5>{{ $anuncio->titulo }}</h5>
                                                            @if($anuncio->descripcion)
                                                                <p class="text-muted">{{ $anuncio->descripcion }}</p>
                                                            @endif
                                                            <div class="mt-3">
                                                                <strong>Contenido:</strong>
                                                                <div class="mt-2">
                                                                    {!! nl2br(e($anuncio->contenido)) !!}
                                                                </div>
                                                            </div>
                                                            @if($anuncio->imagen)
                                                                <div class="mt-3">
                                                                    <strong>Imagen:</strong><br>
                                                                    <img src="{{ asset('storage/' . $anuncio->imagen) }}" 
                                                                         alt="Imagen del anuncio" 
                                                                         class="img-fluid mt-2" 
                                                                         style="max-height: 200px;">
                                                                </div>
                                                            @endif
                                                        </div>
                                                        <div class="col-md-4">
                                                            <dl class="row">
                                                                <dt class="col-sm-6">Tipo:</dt>
                                                                <dd class="col-sm-6">
                                                                    <span class="badge bg-info">{{ ucfirst($anuncio->tipo) }}</span>
                                                                </dd>

                                                                <dt class="col-sm-6">Dirigido a:</dt>
                                                                <dd class="col-sm-6">
                                                                    <span class="badge bg-primary">{{ ucfirst($anuncio->dirigido_a) }}</span>
                                                                </dd>

                                                                <dt class="col-sm-6">Prioridad:</dt>
                                                                <dd class="col-sm-6">
                                                                    @switch($anuncio->prioridad)
                                                                        @case(4) <span class="badge bg-danger">Crítica</span> @break
                                                                        @case(3) <span class="badge bg-warning">Alta</span> @break
                                                                        @case(2) <span class="badge bg-info">Media</span> @break
                                                                        @default <span class="badge bg-secondary">Baja</span>
                                                                    @endswitch
                                                                </dd>

                                                                <dt class="col-sm-6">Estado:</dt>
                                                                <dd class="col-sm-6">
                                                                    <span class="badge bg-{{ $anuncio->es_activo ? 'success' : 'secondary' }}">
                                                                        {{ $anuncio->es_activo ? 'Activo' : 'Inactivo' }}
                                                                    </span>
                                                                </dd>

                                                                <dt class="col-sm-6">Creado:</dt>
                                                                <dd class="col-sm-6">{{ $anuncio->created_at->format('d/m/Y H:i') }}</dd>

                                                                @if($anuncio->fecha_publicacion)
                                                                    <dt class="col-sm-6">Publicación:</dt>
                                                                    <dd class="col-sm-6">{{ $anuncio->fecha_publicacion->format('d/m/Y H:i') }}</dd>
                                                                @endif

                                                                @if($anuncio->fecha_expiracion)
                                                                    <dt class="col-sm-6">Expiración:</dt>
                                                                    <dd class="col-sm-6">{{ $anuncio->fecha_expiracion->format('d/m/Y H:i') }}</dd>
                                                                @endif
                                                            </dl>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    @can('announcements_edit')
                                                        <a href="{{ route('anuncios.edit', $anuncio) }}" class="btn btn-warning">
                                                            <i class="bi bi-pencil me-1"></i>Editar
                                                        </a>
                                                    @endcan
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                        <i class="bi bi-x-circle me-1"></i>Cerrar
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-5">
                                            <div class="text-muted">
                                                <i class="bi bi-megaphone" style="font-size: 3rem;" class="mb-3"></i>
                                                <h5>No hay anuncios creados aún</h5>
                                                <p>Comienza creando tu primer anuncio para comunicarte con los usuarios.</p>
                                                @can('announcements_create')
                                                    <a href="{{ route('anuncios.create') }}" class="btn btn-primary">
                                                        <i class="bi bi-plus-circle"></i> Crear Primer Anuncio
                                                    </a>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if(method_exists($anuncios, 'links'))
                        <div class="d-flex justify-content-center mt-4">
                            {{ $anuncios->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<!-- Cargar Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

<style>
/* BOTONES DE ACCIÓN PERFECTOS */
.d-flex.gap-1 {
    gap: 0.5rem !important;
}

.btn-sm {
    padding: 0.5rem 0.75rem !important;
    font-size: 1rem !important;
    font-weight: 600 !important;
    line-height: 1 !important;
    border-radius: 0.375rem !important;
    min-width: 42px !important;
    height: 40px !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    text-decoration: none !important;
    border: none !important;
}

.table td {
    vertical-align: middle;
}

/* Bootstrap Icons específicos */
.btn-sm i.bi {
    font-size: 1.1rem !important;
    line-height: 1 !important;
    color: white !important;
    font-weight: normal !important;
}

/* Efectos hover suaves */
.d-flex.gap-1 .btn {
    transition: all 0.2s ease !important;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1) !important;
}

.d-flex.gap-1 .btn:hover {
    transform: translateY(-2px) !important;
    box-shadow: 0 4px 12px rgba(0,0,0,0.25) !important;
}

/* Colores de botones */
.btn-info {
    background-color: #0d6efd !important;
    color: white !important;
}

.btn-warning {
    background-color: #ffc107 !important;
    color: #000 !important;
}

.btn-warning i.bi {
    color: #000 !important;
}

.btn-danger {
    background-color: #dc3545 !important;
    color: white !important;
}

.btn-success {
    background-color: #198754 !important;
    color: white !important;
}

.btn-secondary {
    background-color: #6c757d !important;
    color: white !important;
}

/* Mejorar badges */
.badge {
    font-size: 0.75em;
    font-weight: 600;
}

.badge i {
    margin-right: 0.25rem;
    font-size: 0.9em;
}

/* Card hover effects */
.card {
    transition: box-shadow 0.15s ease-in-out;
}

.card:hover {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

/* Filtros de botones */
.btn-check:checked + .btn {
    background-color: var(--bs-btn-active-bg);
    border-color: var(--bs-btn-active-border-color);
}

/* Filas destacadas */
.table-danger {
    --bs-table-bg: rgba(220, 53, 69, 0.1);
}

.table-warning {
    --bs-table-bg: rgba(255, 193, 7, 0.1);
}

/* Ajuste de columna de acciones */
.table th:last-child,
.table td:last-child {
    text-align: center;
    vertical-align: middle;
}

.d-flex.gap-1 {
    justify-content: center;
    align-items: center;
}
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Filtros de anuncios
    const filtros = document.querySelectorAll('input[name="filtro"]');
    const filas = document.querySelectorAll('tbody tr');
    
    filtros.forEach(filtro => {
        filtro.addEventListener('change', function() {
            const valor = this.value;
            
            filas.forEach(fila => {
                if (fila.querySelector('td') === null) return; // Skip empty rows
                
                const estadoBadge = fila.querySelector('.badge');
                let mostrar = true;
                
                switch(valor) {
                    case 'activos':
                        mostrar = estadoBadge && estadoBadge.textContent.trim().includes('Activo');
                        break;
                    case 'inactivos':
                        mostrar = estadoBadge && estadoBadge.textContent.trim().includes('Inactivo');
                        break;
                    case 'urgentes':
                        mostrar = fila.querySelector('.badge.bg-danger') !== null;
                        break;
                    case 'todos':
                    default:
                        mostrar = true;
                        break;
                }
                
                fila.style.display = mostrar ? '' : 'none';
            });
        });
    });
    
    // Confirmación mejorada para eliminación
    const deleteButtons = document.querySelectorAll('button[title="Eliminar"]');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const form = this.closest('form');
            
            if (confirm('⚠️ ¿Está seguro de eliminar este anuncio?\n\nEsta acción no se puede deshacer.')) {
                form.submit();
            }
        });
    });
});
</script>
@endpush
@endsection