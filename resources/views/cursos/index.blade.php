@extends('layouts.app')

@section('title', 'Gestión de Cursos')

@section('content')
<div class="container-fluid px-4">
    <!-- Header Section -->
    <div class="row align-items-center mb-4">
        <div class="col">
            <div class="d-flex align-items-center">
                <div class="icon-circle bg-primary text-white me-3">
                    <i class="uil uil-books"></i>
                </div>
                <div>
                    <h2 class="mb-0 fw-bold text-dark">Gestión de Cursos</h2>
                    <p class="text-muted mb-0">Administra y organiza todos los cursos del sistema</p>
                </div>
            </div>
        </div>
        <div class="col-auto">
            <a href="{{ route('cursos.create') }}" class="btn btn-primary btn-lg shadow-sm">
                <i class="uil uil-plus me-2"></i>
                <span>Nuevo Curso</span>
            </a>
        </div>
    </div>

    <!-- Success Alert -->
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0" role="alert">
            <div class="d-flex align-items-center">
                <i class="uil uil-check-circle me-2 fs-5"></i>
                <div>
                    <strong>¡Éxito!</strong> {{ session('success') }}
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Stats Cards (opcional) -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="icon-circle bg-success text-white me-3">
                            <i class="uil uil-graduation-cap"></i>
                        </div>
                        <div>
                            @php
                                $totalCursos = \App\Models\Curso::count();
                                $cursosActivos = \App\Models\Curso::where('estado', 1)->count();
                            @endphp
                            <h3 class="mb-0 fw-bold">{{ $cursosActivos }}</h3>
                            <small class="text-muted">Cursos Activos</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="icon-circle bg-warning text-white me-3">
                            <i class="uil uil-pause-circle"></i>
                        </div>
                        <div>
                            @php
                                $cursosInactivos = \App\Models\Curso::where('estado', 0)->count();
                            @endphp
                            <h3 class="mb-0 fw-bold">{{ $cursosInactivos }}</h3>
                            <small class="text-muted">Cursos Inactivos</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="icon-circle bg-info text-white me-3">
                            <i class="uil uil-chart-line"></i>
                        </div>
                        <div>
                            <h3 class="mb-0 fw-bold">{{ $totalCursos }}</h3>
                            <small class="text-muted">Total Cursos</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="icon-circle bg-purple text-white me-3">
                            <i class="uil uil-trophy"></i>
                        </div>
                        <div>
                            <h3 class="mb-0 fw-bold">95%</h3>
                            <small class="text-muted">Tasa de Éxito</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Card -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom-0 py-3">
            <div class="row align-items-center">
                <div class="col">
                    <h5 class="mb-0 fw-semibold text-dark">
                        <i class="uil uil-list-ul me-2 text-primary"></i>
                        Lista de Cursos
                    </h5>
                </div>
                <div class="col-auto">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="uil uil-search text-muted"></i>
                        </span>
                        <input type="text" class="form-control border-start-0" placeholder="Buscar cursos...">
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="border-0 fw-semibold text-dark ps-4">
                                <i class="uil uil-book-alt me-2 text-primary"></i>Curso
                            </th>
                            <th class="border-0 fw-semibold text-dark">
                                <i class="uil uil-tag me-2 text-primary"></i>Código
                            </th>
                            <th class="border-0 fw-semibold text-dark">
                                <i class="uil uil-file-alt me-2 text-primary"></i>Descripción
                            </th>
                            <th class="border-0 fw-semibold text-dark">
                                <i class="uil uil-toggle-on me-2 text-primary"></i>Estado
                            </th>
                            <th class="border-0 fw-semibold text-dark text-center">
                                <i class="uil uil-setting me-2 text-primary"></i>Acciones
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($cursos as $curso)
                            <tr class="border-bottom">
                                <td class="ps-4 py-3">
                                    <div class="d-flex align-items-center">
                                        <div class="icon-circle bg-primary bg-opacity-10 text-primary me-3">
                                            @php
                                                $courseIcons = [
                                                    'fisica' => 'uil-atom',
                                                    'quimica' => 'uil-flask',
                                                    'matematica' => 'uil-calculator',
                                                    'psicologia' => 'uil-brain',
                                                    'economia' => 'uil-chart-pie',
                                                    'educacion' => 'uil-graduation-cap',
                                                    'historia' => 'uil-clock',
                                                    'geografia' => 'uil-globe',
                                                    'biologia' => 'uil-dna',
                                                    'informatica' => 'uil-laptop',
                                                    'literatura' => 'uil-book-alt',
                                                    'arte' => 'uil-paint-tool',
                                                ];
                                                
                                                $courseName = strtolower($curso->nombre);
                                                $icon = 'uil-book-alt'; // icono por defecto
                                                
                                                foreach($courseIcons as $key => $iconClass) {
                                                    if(str_contains($courseName, $key)) {
                                                        $icon = $iconClass;
                                                        break;
                                                    }
                                                }
                                            @endphp
                                            <i class="uil {{ $icon }}"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0 fw-semibold">{{ $curso->nombre }}</h6>
                                            <small class="text-muted">Curso académico</small>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3">
                                    <span class="badge bg-light text-dark border fw-normal px-3 py-2">
                                        <i class="uil uil-code-branch me-1"></i>
                                        {{ $curso->codigo }}
                                    </span>
                                </td>
                                <td class="py-3">
                                    <div class="text-truncate" style="max-width: 200px;" title="{{ $curso->descripcion }}">
                                        {{ $curso->descripcion ?? '-' }}
                                    </div>
                                </td>
                                <td class="py-3">
                                    @if($curso->estado)
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success fw-normal px-3 py-2">
                                            <i class="uil uil-check-circle me-1"></i>Activo
                                        </span>
                                    @else
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary fw-normal px-3 py-2">
                                            <i class="uil uil-pause-circle me-1"></i>Inactivo
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3 text-center">
                                    <div class="btn-group" role="group">
                                        <!-- Ver/Editar -->
                                        <a href="{{ route('cursos.edit', $curso->id) }}" 
                                           class="btn btn-sm btn-outline-primary" 
                                           data-bs-toggle="tooltip" 
                                           title="Editar curso">
                                            <i class="uil uil-edit"></i>
                                        </a>

                                        <!-- Toggle Estado -->
                                        <form action="{{ route('cursos.toggle', $curso->id) }}" method="POST" class="d-inline">
                                            @csrf @method('PUT')
                                            <button class="btn btn-sm btn-outline-{{ $curso->estado ? 'warning' : 'success' }}" 
                                                    type="submit"
                                                    data-bs-toggle="tooltip" 
                                                    title="{{ $curso->estado ? 'Desactivar' : 'Activar' }} curso">
                                                <i class="uil uil-{{ $curso->estado ? 'toggle-off' : 'toggle-on' }}"></i>
                                            </button>
                                        </form>

                                        <!-- Eliminar -->
                                        <form action="{{ route('cursos.destroy', $curso->id) }}" method="POST" class="d-inline">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger" 
                                                    type="submit"
                                                    onclick="return confirm('¿Estás seguro de eliminar este curso?')"
                                                    data-bs-toggle="tooltip" 
                                                    title="Eliminar curso">
                                                <i class="uil uil-trash-alt"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <div class="empty-state">
                                        <div class="icon-circle bg-light text-muted mx-auto mb-3" style="width: 80px; height: 80px;">
                                            <i class="uil uil-books" style="font-size: 2rem;"></i>
                                        </div>
                                        <h5 class="text-muted mb-2">No hay cursos registrados</h5>
                                        <p class="text-muted mb-3">Comienza creando tu primer curso</p>
                                        <a href="{{ route('cursos.create') }}" class="btn btn-primary">
                                            <i class="uil uil-plus me-2"></i>Crear Primer Curso
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($cursos->hasPages())
            <div class="card-footer bg-white border-top py-3">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <div class="text-muted small">
                            <i class="uil uil-list-ul me-1"></i>
                            Mostrando {{ $cursos->firstItem() }} - {{ $cursos->lastItem() }} de {{ $cursos->total() }} resultados
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex justify-content-md-end justify-content-center mt-3 mt-md-0">
                            <nav aria-label="Paginación de cursos">
                                <ul class="pagination pagination-sm mb-0">
                                    {{-- Previous Page Link --}}
                                    @if ($cursos->onFirstPage())
                                        <li class="page-item disabled">
                                            <span class="page-link">
                                                <i class="uil uil-angle-left"></i>
                                                <span class="d-none d-sm-inline ms-1">Anterior</span>
                                            </span>
                                        </li>
                                    @else
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $cursos->previousPageUrl() }}">
                                                <i class="uil uil-angle-left"></i>
                                                <span class="d-none d-sm-inline ms-1">Anterior</span>
                                            </a>
                                        </li>
                                    @endif

                                    {{-- Pagination Elements --}}
                                    @foreach ($cursos->getUrlRange(1, $cursos->lastPage()) as $page => $url)
                                        @if ($page == $cursos->currentPage())
                                            <li class="page-item active">
                                                <span class="page-link">{{ $page }}</span>
                                            </li>
                                        @else
                                            <li class="page-item">
                                                <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                                            </li>
                                        @endif
                                    @endforeach

                                    {{-- Next Page Link --}}
                                    @if ($cursos->hasMorePages())
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $cursos->nextPageUrl() }}">
                                                <span class="d-none d-sm-inline me-1">Siguiente</span>
                                                <i class="uil uil-angle-right"></i>
                                            </a>
                                        </li>
                                    @else
                                        <li class="page-item disabled">
                                            <span class="page-link">
                                                <span class="d-none d-sm-inline me-1">Siguiente</span>
                                                <i class="uil uil-angle-right"></i>
                                            </span>
                                        </li>
                                    @endif
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

<style>
.icon-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
}

.bg-purple {
    background-color: #6f42c1 !important;
}

.btn-group .btn {
    border-radius: 0;
}

.btn-group .btn:first-child {
    border-top-left-radius: 0.375rem;
    border-bottom-left-radius: 0.375rem;
}

.btn-group .btn:last-child {
    border-top-right-radius: 0.375rem;
    border-bottom-right-radius: 0.375rem;
}

.empty-state .icon-circle {
    width: 80px;
    height: 80px;
}

.table-responsive {
    border-radius: 0.5rem;
}

.card {
    border-radius: 0.75rem;
}

.badge {
    font-size: 0.75rem;
    letter-spacing: 0.025em;
}

/* Estilos para paginación personalizada */
.pagination {
    --bs-pagination-border-radius: 0.5rem;
    --bs-pagination-border-color: #e9ecef;
    --bs-pagination-hover-color: #0d6efd;
    --bs-pagination-hover-bg: #f8f9fa;
    --bs-pagination-active-bg: #0d6efd;
    --bs-pagination-active-border-color: #0d6efd;
}

.pagination .page-link {
    border: 1px solid var(--bs-pagination-border-color);
    padding: 0.5rem 0.75rem;
    margin: 0 2px;
    border-radius: 0.375rem;
    color: #6c757d;
    transition: all 0.15s ease-in-out;
}

.pagination .page-link:hover {
    background-color: var(--bs-pagination-hover-bg);
    color: var(--bs-pagination-hover-color);
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.pagination .page-item.active .page-link {
    background-color: var(--bs-pagination-active-bg);
    border-color: var(--bs-pagination-active-border-color);
    color: white;
    box-shadow: 0 2px 4px rgba(13, 110, 253, 0.25);
}

.pagination .page-item.disabled .page-link {
    color: #adb5bd;
    background-color: transparent;
    border-color: #e9ecef;
}

@media (max-width: 768px) {
    .btn-group {
        flex-direction: column;
    }
    
    .btn-group .btn {
        border-radius: 0.375rem !important;
        margin-bottom: 2px;
    }
    
    .icon-circle {
        width: 35px;
        height: 35px;
        font-size: 1rem;
    }
    
    .pagination .page-link {
        padding: 0.375rem 0.5rem;
        font-size: 0.875rem;
    }
}
</style>

@push('scripts')
<script>
// Inicializar tooltips
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
@endpush
@endsection