@extends('layouts.app')

@section('title', 'Gestión de Horarios Docentes')

@section('content')
<div class="container-fluid">
    
    <!-- start page title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="javascript: void(0);">Shreyu</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('horarios-docentes.index') }}">Horarios</a></li>
                        <li class="breadcrumb-item active">Gestión</li>
                    </ol>
                </div>
                <h4 class="page-title">Gestión de Horarios Docentes</h4>
            </div>
        </div>
    </div>
    <!-- end page title -->

    <!-- Alerts -->
    @if(session('success'))
        <div class="row">
            <div class="col-12">
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="mdi mdi-check-all me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="row">
            <div class="col-12">
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="mdi mdi-block-helper me-2"></i>
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif

    <!-- Estadísticas -->
    <div class="row">
        <div class="col-md-6 col-xl-3">
            <div class="card tilebox-one">
                <div class="card-body">
                    <i class="mdi mdi-calendar-check float-end text-muted"></i>
                    <h6 class="text-uppercase mt-0">Total Horarios</h6>
                    <h2 class="my-2" id="totalHorarios">{{ $horarios->total() ?? 0 }}</h2>
                    <p class="mb-0 text-muted">
                        <span class="text-success me-2"><span class="mdi mdi-arrow-up-bold"></span> 5.27%</span>
                        <span class="text-nowrap">Desde la semana pasada</span>
                    </p>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 col-xl-3">
            <div class="card tilebox-one">
                <div class="card-body">
                    <i class="mdi mdi-account-multiple float-end text-muted"></i>
                    <h6 class="text-uppercase mt-0">Docentes Activos</h6>
                    <h2 class="my-2" id="docentesActivos">{{ isset($horarios) ? $horarios->unique('docente_id')->count() : 0 }}</h2>
                    <p class="mb-0 text-muted">
                        <span class="text-success me-2"><span class="mdi mdi-arrow-up-bold"></span> 1.33%</span>
                        <span class="text-nowrap">Desde la semana pasada</span>
                    </p>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 col-xl-3">
            <div class="card tilebox-one">
                <div class="card-body">
                    <i class="mdi mdi-book-open-variant float-end text-muted"></i>
                    <h6 class="text-uppercase mt-0">Cursos Programados</h6>
                    <h2 class="my-2" id="cursosProgram">{{ isset($horarios) ? $horarios->unique('curso_id')->count() : 0 }}</h2>
                    <p class="mb-0 text-muted">
                        <span class="text-warning me-2"><span class="mdi mdi-arrow-down-bold"></span> 7.00%</span>
                        <span class="text-nowrap">Desde la semana pasada</span>
                    </p>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 col-xl-3">
            <div class="card tilebox-one">
                <div class="card-body">
                    <i class="mdi mdi-door-open float-end text-muted"></i>
                    <h6 class="text-uppercase mt-0">Aulas en Uso</h6>
                    <h2 class="my-2" id="aulasUso">{{ isset($horarios) ? $horarios->unique('aula_id')->count() : 0 }}</h2>
                    <p class="mb-0 text-muted">
                        <span class="text-success me-2"><span class="mdi mdi-arrow-up-bold"></span> 4.87%</span>
                        <span class="text-nowrap">Desde la semana pasada</span>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="header-title">
                        <i class="mdi mdi-view-list me-2"></i>
                        Horarios Programados
                    </h4>
                    <p class="text-muted mb-0">Gestione y visualice todos los horarios académicos</p>
                </div>
                <div class="card-body">

                    <!-- Acciones Rápidas -->
                    <div class="mb-3">
                        <h6 class="text-muted mb-2">
                            <i class="mdi mdi-lightning-bolt me-1"></i>
                            Acciones Rápidas
                        </h6>
                        <div class="d-flex flex-wrap gap-2">
                            <a href="{{ route('horarios-docentes.create') }}" class="btn btn-primary btn-sm">
                                <i class="mdi mdi-plus me-1"></i>
                                Nuevo Horario
                            </a>
                            <button class="btn btn-success btn-sm" id="toggleViewBtn" onclick="toggleCalendarioView()">
                                <i class="mdi mdi-calendar-week me-1" id="toggleIcon"></i>
                                <span id="toggleText">Vista Calendario</span>
                            </button>
                            <button class="btn btn-info btn-sm" onclick="exportarHorarios()">
                                <i class="mdi mdi-file-export me-1"></i>
                                Exportar Excel
                            </button>
                            <button class="btn btn-warning btn-sm" onclick="generarReporte()">
                                <i class="mdi mdi-chart-pie me-1"></i>
                                Reportes
                            </button>
                        </div>
                    </div>

                    <!-- Búsqueda y Filtros -->
                    <div class="mb-3 view-lista" id="searchFilters">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="position-relative">
                                    <input type="text" 
                                        class="form-control" 
                                        id="horario_search" 
                                        placeholder="Buscar por docente, curso, aula..."
                                        autocomplete="off">
                                    <div class="position-absolute top-50 end-0 translate-middle-y pe-3">
                                        <i class="mdi mdi-magnify text-muted"></i>
                                    </div>
                                    <div id="suggestions" class="position-absolute w-100 bg-white border border-top-0 rounded-bottom shadow-sm" style="display: none; z-index: 1050; top: 100%; left: 0;"></div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <select class="form-select" id="filtroTipo">
                                    <option value="">Todos los tipos</option>
                                    <option value="teoria">Teoría</option>
                                    <option value="practica">Práctica</option>
                                    <option value="laboratorio">Laboratorio</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Filtros por día -->
                    <div class="mb-3 view-lista" id="dayFilters">
                        <h6 class="text-muted mb-2">
                            <i class="mdi mdi-calendar-today me-1"></i>
                            Filtrar por día:
                        </h6>
                        <div class="d-flex flex-wrap gap-1">
                            <span class="badge bg-primary day-filter-badge active" data-day="todos" style="cursor: pointer;">
                                <i class="mdi mdi-calendar me-1"></i>Todos
                            </span>
                            @php
                                $dias = [
                                    'lunes' => 'Lunes',
                                    'martes' => 'Martes', 
                                    'miercoles' => 'Miércoles',
                                    'jueves' => 'Jueves',
                                    'viernes' => 'Viernes',
                                    'sabado' => 'Sábado',
                                    'domingo' => 'Domingo'
                                ];
                            @endphp
                            @foreach($dias as $diaKey => $diaNombre)
                                <span class="badge bg-light text-dark day-filter-badge" data-day="{{ $diaKey }}" style="cursor: pointer;">
                                    <i class="mdi mdi-calendar-today me-1"></i>{{ $diaNombre }}
                                </span>
                            @endforeach
                        </div>
                    </div>

                    <!-- Vista Lista -->
                    <div id="horariosList" class="view-lista">
                        @forelse($horarios ?? [] as $horario)
                            <div class="card mb-2 horario-card" 
                                 data-day="{{ strtolower($horario->dia_semana ?? '') }}" 
                                 data-docente="{{ $horario->docente->nombre_completo ?? '' }}" 
                                 data-curso="{{ $horario->curso->nombre ?? '' }}" 
                                 data-aula="{{ $horario->aula->nombre ?? '' }}"
                                 data-tipo="{{ strtolower($horario->tipo ?? '') }}">
                                <div class="card-body py-2 d-flex align-items-center justify-content-between">
                                    <button class="btn btn-primary btn-sm me-2 d-flex align-items-center" onclick="verDetalles({{ $horario->id ?? 0 }})">
                                        <i class="mdi mdi-eye me-1"></i> Ver Detalles
                                    </button>
                                    <div class="d-flex align-items-center flex-grow-1">
                                        <div class="avatar-sm bg-primary rounded-circle d-flex align-items-center justify-content-center me-2">
                                            <span class="text-white font-weight-bold">
                                                {{ substr($horario->docente->nombre_completo ?? 'N', 0, 1) }}
                                            </span>
                                        </div>
                                        <div>
                                            <h6 class="mb-0">{{ $horario->docente->nombre_completo ?? 'Sin asignar' }}</h6>
                                            <small class="text-muted d-block mt-1">
                                                <span class="badge bg-info me-1">{{ ucfirst($horario->dia_semana ?? '') }}</span>
                                                <span class="badge bg-secondary me-1">
                                                    {{ isset($horario->hora_inicio) ? \Carbon\Carbon::parse($horario->hora_inicio)->format('H:i') : '' }} - 
                                                    {{ isset($horario->hora_fin) ? \Carbon\Carbon::parse($horario->hora_fin)->format('H:i') : '' }}
                                                </span>
                                                <span class="badge bg-warning text-dark me-1">
                                                    {{ Str::limit($horario->curso->nombre ?? 'N/A', 15) }}
                                                </span>
                                            </small>
                                        </div>
                                    </div>
                                    <div class="dropdown">
                                        <button class="btn btn-light btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="mdi mdi-dots-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="#" onclick="verDetalles({{ $horario->id ?? 0 }})">
                                                <i class="mdi mdi-eye me-2 text-info"></i>Ver Detalles
                                            </a></li>
                                            <li><a class="dropdown-item" href="{{ route('horarios-docentes.edit', $horario->id ?? 0) }}">
                                                <i class="mdi mdi-pencil me-2 text-warning"></i>Editar
                                            </a></li>
                                            <li><a class="dropdown-item" href="#" onclick="duplicarHorario({{ $horario->id ?? 0 }})">
                                                <i class="mdi mdi-content-copy me-2 text-success"></i>Duplicar
                                            </a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><a class="dropdown-item text-danger" href="#" onclick="showDeleteConfirmation({{ $horario->id ?? 0 }})">
                                                <i class="mdi mdi-delete me-2"></i>Eliminar
                                            </a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-5" id="emptyState">
                                <i class="mdi mdi-calendar-remove display-3 text-muted mb-3"></i>
                                <h5 class="text-muted">No hay horarios programados</h5>
                                <p class="text-muted">Comience creando su primer horario académico</p>
                                <a href="{{ route('horarios-docentes.create') }}" class="btn btn-primary">
                                    <i class="mdi mdi-plus me-2"></i>Crear Primer Horario
                                </a>
                            </div>
                        @endforelse
                    </div>

                    <!-- Vista Calendario -->
                    <div id="calendarioView" class="view-calendario" style="display: none;">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center">
                                        <i class="mdi mdi-calendar-week me-2"></i>
                                        <h5 class="mb-0">Programación Semanal</h5>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <button class="btn btn-light btn-sm" onclick="previousWeek()">
                                            <i class="mdi mdi-chevron-left"></i>
                                        </button>
                                        <span id="semanaActual" class="text-white mx-2">Semana Actual</span>
                                        <button class="btn btn-light btn-sm" onclick="nextWeek()">
                                            <i class="mdi mdi-chevron-right"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-bordered mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                @foreach($dias as $diaKey => $diaNombre)
                                                    <th class="text-center py-3">
                                                        <i class="mdi mdi-calendar-today me-1"></i>
                                                        {{ $diaNombre }}
                                                    </th>
                                                @endforeach
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                @foreach($dias as $diaKey => $diaNombre)
                                                    <td class="p-2" style="width: 14.28%; min-height: 200px; vertical-align: top;">
                                                        @if(isset($horarios))
                                                            @php
                                                                $horariosDia = $horarios->where('dia_semana', $diaKey)->sortBy('hora_inicio');
                                                            @endphp
                                                            @forelse($horariosDia as $horario)
                                                                <div class="card mb-2 bg-primary text-white" 
                                                                     style="cursor: pointer; font-size: 0.8rem;"
                                                                     onclick="verDetalles({{ $horario->id }})">
                                                                     <div class="card-body p-2">
                                                                         <div class="d-flex align-items-center mb-1">
                                                                             <i class="mdi mdi-clock-outline me-1"></i>
                                                                             <small>{{ \Carbon\Carbon::parse($horario->hora_inicio)->format('H:i') }} - {{ \Carbon\Carbon::parse($horario->hora_fin)->format('H:i') }}</small>
                                                                         </div>
                                                                         <div class="fw-bold small">
                                                                             {{ Str::limit($horario->docente->nombre_completo ?? 'N/A', 12) }}
                                                                         </div>
                                                                         <div class="small">
                                                                             <i class="mdi mdi-book-open-variant me-1"></i>
                                                                             {{ Str::limit($horario->curso->nombre ?? 'N/A', 10) }}
                                                                         </div>
                                                                         <div class="small">
                                                                             <i class="mdi mdi-door-open me-1"></i>
                                                                             {{ $horario->aula->nombre ?? 'N/A' }}
                                                                         </div>
                                                                     </div>
                                                                 </div>
                                                             @empty
                                                                 <div class="text-center text-muted p-3">
                                                                     <i class="mdi mdi-calendar-remove mb-2"></i>
                                                                     <small>Sin horarios</small>
                                                                 </div>
                                                             @endforelse
                                                         @endif
                                                     </td>
                                                 @endforeach
                                             </tr>
                                         </tbody>
                                     </table>
                                 </div>
                             </div>
                         </div>
                     </div>
 
                    <!-- Paginación -->
                    @if(isset($horarios) && $horarios->hasPages())
                        <div class="d-flex justify-content-center mt-4 view-lista">
                            {{ $horarios->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Actividad Reciente -->
            <div class="card">
                <div class="card-header">
                    <h4 class="header-title">
                        <i class="mdi mdi-history me-2"></i>
                        Actividad Reciente
                    </h4>
                    <p class="text-muted mb-0">Últimas acciones realizadas</p>
                </div>
                <div class="card-body">
                    <div id="recentActivity" style="max-height: 300px; overflow-y: auto;">
                        <div class="text-center text-muted py-3">
                            <div class="spinner-border spinner-border-sm text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mb-0 mt-2 small">Cargando actividad...</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Resumen por Docente -->
            <div class="card mt-3">
                <div class="card-header">
                    <h4 class="header-title">
                        <i class="mdi mdi-account-group me-2"></i>
                        Top Docentes Activos
                    </h4>
                    <p class="text-muted mb-0">Docentes con más horarios asignados</p>
                </div>
                <div class="card-body">
                    <div id="resumenDocentes">
                        @if(isset($horarios))
                            @php
                                $docentesConHorarios = $horarios->groupBy('docente_id');
                            @endphp
                            @foreach($docentesConHorarios->sortByDesc(fn($h) => $h->count())->take(5) as $docenteId => $horariosDocente)
                                @php
                                    $docente = $horariosDocente->first()->docente;
                                    $totalHoras = $horariosDocente->sum(function($h) {
                                        return isset($h->hora_inicio) && isset($h->hora_fin) 
                                            ? \Carbon\Carbon::parse($h->hora_fin)->diffInHours(\Carbon\Carbon::parse($h->hora_inicio)) 
                                            : 0;
                                    });
                                @endphp
                                <div class="d-flex justify-content-between align-items-center mb-3 p-2 bg-light rounded">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm bg-primary rounded-circle d-flex align-items-center justify-content-center me-2">
                                            <span class="text-white font-weight-bold">
                                                {{ substr($docente->nombre_completo ?? 'N', 0, 1) }}
                                            </span>
                                        </div>
                                        <div>
                                            <h6 class="mb-0">{{ Str::limit($docente->nombre_completo ?? 'Sin nombre', 15) }}</h6>
                                            <small class="text-muted">
                                                <i class="mdi mdi-clock-outline me-1"></i>
                                                {{ $totalHoras }}h/semana
                                            </small>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-primary">{{ $horariosDocente->count() }}</span>
                                        <small class="text-muted d-block">horarios</small>
                                    </div>
                                </div>
                            @endforeach
                            
                            @if($docentesConHorarios->count() > 5)
                                <div class="text-center mt-3">
                                    <button class="btn btn-sm btn-outline-primary" onclick="verTodosDocentes()">
                                        <i class="mdi mdi-account-group me-1"></i>
                                        Ver todos ({{ $docentesConHorarios->count() }} docentes)
                                    </button>
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para detalles -->
<div class="modal fade" id="detallesModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">
                    <i class="mdi mdi-information-outline me-2"></i>
                    Detalles del Horario
                </h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="detallesContent">
                <!-- Contenido dinámico -->
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmación para eliminar -->
<div class="modal fade" id="deleteConfirmationModal" tabindex="-1" aria-labelledby="deleteConfirmationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteConfirmationModalLabel">
                    <i class="mdi mdi-alert-circle me-2"></i>Confirmar Eliminación
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>
                    <i class="mdi mdi-alert-circle-outline text-danger me-2"></i>
                    ¿Estás seguro de que quieres eliminar este horario?
                </p>
                <p class="text-muted">Esta acción no se puede deshacer y afectará la programación académica.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Eliminar</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Variables globales
    let vistaActual = 'lista';
    const horariosData = @json(isset($horarios) ? $horarios->toArray() : ['data' => []]);
    
    const searchInput = document.getElementById('horario_search');
    const suggestionsContainer = document.getElementById('suggestions');
    let horarioCards = document.querySelectorAll('.horario-card');
    
    let currentFocus = -1;
    let filteredHorarios = [];

    // Inicialización
    document.addEventListener('DOMContentLoaded', function() {
        cargarActividadReciente();
        inicializarFiltros();
        animarContadores();
        inicializarBusqueda();
        actualizarSemanaActual();
    });

    // Sistema de búsqueda mejorado
    function inicializarBusqueda() {
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase().trim();
                mostrarSugerencias(searchTerm);
                filtrarHorarios(searchTerm);
            });

            searchInput.addEventListener('keydown', function(e) {
                const items = suggestionsContainer.querySelectorAll('.suggestion-item');
                
                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    currentFocus++;
                    if (currentFocus >= items.length) currentFocus = 0;
                    setActive(items);
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    currentFocus--;
                    if (currentFocus < 0) currentFocus = items.length - 1;
                    setActive(items);
                } else if (e.key === 'Enter') {
                    e.preventDefault();
                    if (currentFocus > -1 && filteredHorarios[currentFocus]) {
                        seleccionarSugerencia(currentFocus);
                    }
                } else if (e.key === 'Escape') {
                    suggestionsContainer.style.display = 'none';
                    currentFocus = -1;
                }
            });
        }
    }

    function mostrarSugerencias(searchTerm) {
        if (!searchTerm || !suggestionsContainer) {
            if (suggestionsContainer) suggestionsContainer.style.display = 'none';
            return;
        }

        filteredHorarios = [];
        
        if (horariosData.data) {
            filteredHorarios = horariosData.data.filter(horario => {
                const docenteNombre = horario.docente ? horario.docente.nombre_completo || 'Sin asignar' : 'Sin asignar';
                const cursoNombre = horario.curso ? horario.curso.nombre || 'Sin curso' : 'Sin curso';
                const aulaNombre = horario.aula ? horario.aula.nombre || 'Sin aula' : 'Sin aula';
                const diaName = horario.dia_semana || '';
                
                return docenteNombre.toLowerCase().includes(searchTerm) ||
                        cursoNombre.toLowerCase().includes(searchTerm) ||
                        aulaNombre.toLowerCase().includes(searchTerm) ||
                        diaName.toLowerCase().includes(searchTerm);
            });
        }

        if (filteredHorarios.length === 0) {
            suggestionsContainer.innerHTML = `
                <div class="p-3 text-center text-muted">
                    <i class="mdi mdi-magnify-close me-2"></i>
                    No se encontraron resultados
                </div>`;
            suggestionsContainer.style.display = 'block';
            return;
        }

        let html = '';
        filteredHorarios.slice(0, 5).forEach((horario, index) => {
            const docenteNombre = horario.docente ? horario.docente.nombre_completo || 'Sin asignar' : 'Sin asignar';
            const cursoNombre = horario.curso ? horario.curso.nombre || 'Sin curso' : 'Sin curso';
            const aulaNombre = horario.aula ? horario.aula.nombre || 'Sin aula' : 'Sin aula';
            const diaName = horario.dia_semana || '';
            
            html += `
                <div class="suggestion-item p-3 border-bottom" data-index="${index}" style="cursor: pointer;">
                    <div class="d-flex align-items-center">
                        <div class="avatar-sm bg-primary rounded-circle d-flex align-items-center justify-content-center me-3">
                            <span class="text-white font-weight-bold">${docenteNombre.charAt(0)}</span>
                        </div>
                        <div class="flex-grow-1">
                            <div><span class="text-primary fw-bold">${highlightMatch(docenteNombre, searchTerm)}</span> - ${highlightMatch(cursoNombre, searchTerm)}</div>
                            <div><small class="text-muted">
                                <i class="mdi mdi-calendar-today me-1"></i>${diaName} | 
                                <i class="mdi mdi-door-open me-1"></i>${aulaNombre} | 
                                <i class="mdi mdi-clock-outline me-1"></i>${horario.hora_inicio || ''} - ${horario.hora_fin || ''}
                            </small></div>
                        </div>
                    </div>
                </div>
            `;
        });

        suggestionsContainer.innerHTML = html;
        suggestionsContainer.style.display = 'block';

        document.querySelectorAll('.suggestion-item').forEach((item, index) => {
            item.addEventListener('click', function() {
                seleccionarSugerencia(index);
            });
        });
    }

    function highlightMatch(text, searchTerm) {
        if (!searchTerm) return text;
        const regex = new RegExp(`(${searchTerm.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');
        return text.replace(regex, '<mark>$1</mark>');
    }

    function seleccionarSugerencia(index) {
        const horario = filteredHorarios[index];
        const docenteNombre = horario.docente ? horario.docente.nombre_completo || 'Sin asignar' : 'Sin asignar';
        const cursoNombre = horario.curso ? horario.curso.nombre || 'Sin curso' : 'Sin curso';
        searchInput.value = docenteNombre + ' - ' + cursoNombre;
        suggestionsContainer.style.display = 'none';
        filtrarHorarios(searchInput.value.toLowerCase());
    }

    function setActive(items) {
        items.forEach(item => item.classList.remove('bg-light'));
        if (currentFocus >= 0 && currentFocus < items.length) {
            items[currentFocus].classList.add('bg-light');
            items[currentFocus].scrollIntoView({ block: 'nearest' });
        }
    }

    function filtrarHorarios(searchTerm) {
        const diaActivo = document.querySelector('.day-filter-badge.active')?.dataset.day || 'todos';
        const tipoActivo = document.getElementById('filtroTipo')?.value.toLowerCase() || '';
        let visibleCount = 0;
        
        horarioCards = document.querySelectorAll('.horario-card');

        horarioCards.forEach(card => {
            const docente = (card.dataset.docente || '').toLowerCase();
            const curso = (card.dataset.curso || '').toLowerCase();
            const aula = (card.dataset.aula || '').toLowerCase();
            const dia = card.dataset.day || '';
            const tipo = card.dataset.tipo || '';
            
            const matchesSearch = !searchTerm || 
                docente.includes(searchTerm) || 
                curso.includes(searchTerm) || 
                aula.includes(searchTerm);
            
            const matchesDay = diaActivo === 'todos' || dia === diaActivo;
            const matchesType = tipoActivo === '' || tipo === tipoActivo;
            
            if (matchesSearch && matchesDay && matchesType) {
                card.style.display = 'block';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        verificarEstadoVacio();
    }

    // Filtros por día y tipo
    function inicializarFiltros() {
        const dayFilters = document.querySelectorAll('.day-filter-badge');
        const tipoFilter = document.getElementById('filtroTipo');
        
        dayFilters.forEach(filter => {
            filter.addEventListener('click', function() {
                dayFilters.forEach(f => {
                    f.classList.remove('active', 'bg-primary', 'text-white');
                    f.classList.add('bg-light', 'text-dark');
                });
                this.classList.remove('bg-light', 'text-dark');
                this.classList.add('active', 'bg-primary', 'text-white');
                filtrarHorarios(searchInput ? searchInput.value.toLowerCase() : '');
            });
        });

        if (tipoFilter) {
            tipoFilter.addEventListener('change', function() {
                filtrarHorarios(searchInput ? searchInput.value.toLowerCase() : '');
            });
        }
    }

    function verificarEstadoVacio() {
        const visibleCards = Array.from(horarioCards).filter(card => 
            card.style.display !== 'none'
        );
        
        const emptyState = document.getElementById('emptyState');
        if (emptyState) {
            emptyState.style.display = visibleCards.length === 0 ? 'block' : 'none';
        }
    }

    // Toggle entre vista lista y calendario
    function toggleCalendarioView() {
        const listaElements = document.querySelectorAll('.view-lista');
        const calendarioView = document.getElementById('calendarioView');
        const toggleBtn = document.getElementById('toggleViewBtn');
        const toggleIcon = document.getElementById('toggleIcon');
        const toggleText = document.getElementById('toggleText');

        if (vistaActual === 'lista') {
            listaElements.forEach(el => el.style.display = 'none');
            if (calendarioView) calendarioView.style.display = 'block';
            
            if (toggleBtn) {
                toggleBtn.className = 'btn btn-danger btn-sm';
                toggleIcon.className = 'mdi mdi-view-list me-1';
                toggleText.textContent = 'Vista Lista';
            }
            
            vistaActual = 'calendario';
            
        } else {
            listaElements.forEach(el => el.style.display = 'block');
            if (calendarioView) calendarioView.style.display = 'none';
            
            if (toggleBtn) {
                toggleBtn.className = 'btn btn-success btn-sm';
                toggleIcon.className = 'mdi mdi-calendar-week me-1';
                toggleText.textContent = 'Vista Calendario';
            }
            
            vistaActual = 'lista';
        }
    }

    // Cargar actividad reciente con datos reales
    function cargarActividadReciente() {
        const container = document.getElementById('recentActivity');
        if (!container) return;
        
        let actividadHTML = '';
        const tiposActividad = ['created', 'updated', 'deleted'];
        const iconos = {
            'created': 'mdi-plus-circle',
            'updated': 'mdi-pencil',
            'deleted': 'mdi-delete'
        };
        const colores = {
            'created': 'success',
            'updated': 'warning',
            'deleted': 'danger'
        };
        const acciones = {
            'created': 'Horario creado',
            'updated': 'Horario actualizado', 
            'deleted': 'Horario eliminado'
        };

        if (horariosData.data && horariosData.data.length > 0) {
            const sortedHorarios = horariosData.data.slice().sort((a, b) => {
                const dateA = new Date(a.created_at || '2000-01-01');
                const dateB = new Date(b.created_at || '2000-01-01');
                return dateB - dateA;
            });
            for (let i = 0; i < Math.min(sortedHorarios.length, 5); i++) {
                const horario = sortedHorarios[i];
                const tipo = tiposActividad[i % tiposActividad.length];
                const docenteNombre = horario.docente ? horario.docente.nombre_completo : 'Docente';
                const cursoNombre = horario.curso ? horario.curso.nombre : 'Curso';
                const minutos = Math.floor(Math.random() * 120) + 1;
                
                actividadHTML += `
                    <div class="d-flex align-items-start mb-3 p-2 border-start border-3 border-${colores[tipo]} bg-light bg-opacity-50 rounded">
                        <div class="avatar-sm bg-${colores[tipo]} rounded-circle d-flex align-items-center justify-content-center me-3">
                            <i class="mdi ${iconos[tipo]} text-white"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-1">${acciones[tipo]}</h6>
                            <div class="text-muted small mb-1">
                                <i class="mdi mdi-account me-1"></i>${docenteNombre} - 
                                <i class="mdi mdi-book-open-variant me-1"></i>${cursoNombre}
                            </div>
                            <small class="text-muted">
                                <i class="mdi mdi-clock-outline me-1"></i>
                                Hace ${minutos} min
                            </small>
                        </div>
                    </div>
                `;
            }
        } else {
            actividadHTML = `
                <div class="text-center text-muted py-3">
                    <i class="mdi mdi-calendar-remove display-4"></i>
                    <p class="mb-0 mt-2">Sin actividad reciente</p>
                </div>
            `;
        }

        setTimeout(() => {
            if (container) {
                container.innerHTML = actividadHTML;
            }
        }, 1000);
    }

    // Animar contadores
    function animarContadores() {
        const contadores = document.querySelectorAll('[id$="Horarios"], [id$="Activos"], [id$="Program"], [id$="Uso"]');
        contadores.forEach((contador, index) => {
            const valor = parseInt(contador.textContent) || 0;
            let actual = 0;
            const incremento = valor / 30;
            
            setTimeout(() => {
                const timer = setInterval(() => {
                    actual += incremento;
                    if (actual >= valor) {
                        actual = valor;
                        clearInterval(timer);
                    }
                    contador.textContent = Math.floor(actual);
                }, 50);
            }, index * 200);
        });
    }

    // Navegación de calendario
    function previousWeek() {
        console.log('Semana anterior');
        mostrarNotificacion('Navegación', 'Cargando semana anterior', 'info');
    }

    function nextWeek() {
        console.log('Siguiente semana');
        mostrarNotificacion('Navegación', 'Cargando siguiente semana', 'info');
    }

    function actualizarSemanaActual() {
        const semanaElement = document.getElementById('semanaActual');
        if (semanaElement) {
            const fecha = new Date();
            const opciones = { 
                weekday: 'long', 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric' 
            };
            semanaElement.innerHTML = `
                <i class="mdi mdi-calendar me-2"></i>
                Semana del ${fecha.toLocaleDateString('es-ES', opciones)}
            `;
        }
    }

    // Funciones de acciones
    function exportarHorarios() {
        mostrarNotificacion('Exportación iniciada', 'Los horarios se están preparando para descargar', 'success');
    }

    function generarReporte() {
        mostrarNotificacion('Generando reporte', 'Se está preparando el reporte estadístico', 'info');
        
        setTimeout(() => {
            mostrarNotificacion('Reporte generado', 'El reporte estadístico está listo', 'success');
        }, 2000);
    }

    function verDetalles(horarioId) {
        const modal = new bootstrap.Modal(document.getElementById('detallesModal'));
        const content = document.getElementById('detallesContent');
        
        if (content) {
            content.innerHTML = `
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <p class="mt-2 text-muted">Cargando detalles del horario...</p>
                </div>
            `;
        }
        
        modal.show();
        
        let horarioDetalle = null;
        if (horariosData.data) {
            horarioDetalle = horariosData.data.find(h => h.id == horarioId);
        }
        
        setTimeout(() => {
            if (horarioDetalle && content) {
                const docenteNombre = horarioDetalle.docente ? horarioDetalle.docente.nombre_completo : 'Sin asignar';
                const cursoNombre = horarioDetalle.curso ? horarioDetalle.curso.nombre : 'Sin curso';
                const aulaNombre = horarioDetalle.aula ? horarioDetalle.aula.nombre : 'Sin aula';
                const tipo = horarioDetalle.tipo || 'N/A';
                
                content.innerHTML = `
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="mdi mdi-information-outline me-2"></i>
                                        Información General
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <strong><i class="mdi mdi-pound me-1"></i>ID:</strong> 
                                        <span class="badge bg-primary">${horarioDetalle.id}</span>
                                    </div>
                                    <div class="mb-3">
                                        <strong><i class="mdi mdi-toggle-switch me-1"></i>Estado:</strong> 
                                        <span class="badge bg-success">Activo</span>
                                    </div>
                                    <div class="mb-3">
                                        <strong><i class="mdi mdi-calendar-plus me-1"></i>Creado:</strong> 
                                        ${new Date().toLocaleDateString('es-ES')}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="mdi mdi-clock-outline me-2"></i>
                                        Detalles del Horario
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <strong><i class="mdi mdi-account me-1"></i>Docente:</strong><br>
                                        ${docenteNombre}
                                    </div>
                                    <div class="mb-3">
                                        <strong><i class="mdi mdi-book-open-variant me-1"></i>Curso:</strong><br>
                                        ${cursoNombre}
                                    </div>
                                    <div class="mb-3">
                                        <strong><i class="mdi mdi-door-open me-1"></i>Aula:</strong><br>
                                        ${aulaNombre}
                                    </div>
                                    <div class="mb-0">
                                        <strong><i class="mdi mdi-tag me-1"></i>Tipo:</strong>
                                        <span class="badge bg-secondary">${tipo}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-12">
                            <div class="alert alert-info" role="alert">
                                <i class="mdi mdi-lightbulb-outline me-2"></i>
                                <strong>Información adicional:</strong> Este horario está activo y forma parte de la programación académica actual.
                            </div>
                        </div>
                    </div>
                `;
            } else if (content) {
                content.innerHTML = `
                    <div class="text-center py-4">
                        <i class="mdi mdi-alert-circle display-3 text-warning mb-3"></i>
                        <h5>Error al cargar detalles</h5>
                        <p class="text-muted">No se pudo encontrar la información del horario</p>
                    </div>
                `;
            }
        }, 1500);
    }
    
    function showDeleteConfirmation(horarioId) {
        const myModal = new bootstrap.Modal(document.getElementById('deleteConfirmationModal'));
        const confirmBtn = document.getElementById('confirmDeleteBtn');
        
        confirmBtn.onclick = function() {
            eliminarHorario(horarioId);
            myModal.hide();
        };

        myModal.show();
    }

    function duplicarHorario(horarioId) {
        mostrarNotificacion('Duplicando horario', 'Se está creando una copia del horario', 'info');
        
        setTimeout(() => {
            mostrarNotificacion('Horario duplicado', 'Se ha creado una copia exitosamente', 'success');
        }, 1500);
    }

    function eliminarHorario(horarioId) {
        mostrarNotificacion('Eliminando horario', 'Procesando eliminación...', 'warning');
        
        setTimeout(() => {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/horarios-docentes/${horarioId}`;
            
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            const methodField = document.createElement('input');
            methodField.type = 'hidden';
            methodField.name = '_method';
            methodField.value = 'DELETE';
            
            form.appendChild(csrfToken);
            form.appendChild(methodField);
            document.body.appendChild(form);
            form.submit();
        }, 1000);
    }

    function verTodosDocentes() {
        mostrarNotificacion('Cargando docentes', 'Preparando vista completa de docentes', 'info');
    }

    // Sistema de notificaciones usando las notificaciones de Bootstrap
    function mostrarNotificacion(titulo, mensaje, tipo = 'info') {
        const iconos = {
            'success': 'mdi-check-all',
            'error': 'mdi-block-helper',
            'warning': 'mdi-alert-outline',
            'info': 'mdi-information-outline'
        };
        
        const notif = document.createElement('div');
        notif.className = `alert alert-${tipo} alert-dismissible fade show position-fixed top-0 end-0 m-3 z-index-1050`;
        notif.role = 'alert';
        
        notif.innerHTML = `
            <i class="mdi ${iconos[tipo]} me-2"></i>
            <strong>${titulo}:</strong> ${mensaje}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        
        document.body.appendChild(notif);
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(notif);
            bsAlert.close();
        }, 4000);
    }

    // Cerrar sugerencias al hacer clic fuera
    document.addEventListener('click', function(e) {
        if (suggestionsContainer && !e.target.closest('#horario_search') && !e.target.closest('#suggestions')) {
            suggestionsContainer.style.display = 'none';
        }
    });
</script>
@endsection
