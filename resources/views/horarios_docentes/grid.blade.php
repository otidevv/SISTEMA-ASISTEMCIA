@extends('layouts.app')

@section('title', 'Grilla Visual de Horarios')

@push('css')
<style>
    :root {
        --primary-gradient: linear-gradient(135deg, #4f32c2 0%, #7367f0 100%);
        --success-gradient: linear-gradient(135deg, #10b981 0%, #28c76f 100%);
        --warning-gradient: linear-gradient(135deg, #ff9f43 0%, #ff8b1b 100%);
        --info-gradient: linear-gradient(135deg, #00cfe8 0%, #1ce1ff 100%);
    }

    /* Contenedor de la grilla */
    .schedule-grid-container {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        overflow: hidden;
    }

    /* Grilla de horarios */
    .schedule-grid {
        display: grid;
        grid-template-columns: 140px repeat(6, 1fr);
        gap: 1px;
        background: #e5e7eb;
        min-height: 600px;
    }

    /* Celdas de la grilla */
    .grid-cell {
        background: white;
        padding: 8px;
        min-height: 80px;
        position: relative;
        transition: all 0.2s ease;
    }

    .grid-cell:hover {
        background: #f8f9fe;
        cursor: pointer;
    }

    .grid-cell.header {
        background: linear-gradient(135deg, #2a3042 0%, #323950 100%);
        color: white;
        font-weight: 600;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        padding: 12px 8px;
    }

    .grid-cell.time-slot {
        background: #f8f9fe;
        font-weight: 600;
        color: #6c757d;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
        text-align: center;
        padding: 4px;
    }

    /* Bloques de horario */
    .schedule-block {
        background: var(--primary-gradient);
        color: white;
        border-radius: 8px;
        padding: 8px;
        margin: 2px;
        cursor: move;
        transition: all 0.3s ease;
        box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        position: relative;
        overflow: hidden;
    }

    .schedule-block:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    }

    .schedule-block::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: rgba(255,255,255,0.3);
    }

    .schedule-block .course-name {
        font-weight: 700;
        font-size: 0.9rem;
        margin-bottom: 4px;
        line-height: 1.2;
    }

    .schedule-block .teacher-name {
        font-size: 0.75rem;
        opacity: 0.9;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .schedule-block .time-range {
        font-size: 0.7rem;
        opacity: 0.8;
        margin-top: 4px;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    /* Botón de eliminar en el bloque */
    .schedule-block .delete-btn {
        position: absolute;
        top: 4px;
        right: 4px;
        background: rgba(255,255,255,0.2);
        border: none;
        color: white;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        display: none;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s;
    }

    .schedule-block:hover .delete-btn {
        display: flex;
    }

    .schedule-block .delete-btn:hover {
        background: rgba(255,255,255,0.4);
    }

    /* Filtros superiores */
    .filters-bar {
        background: linear-gradient(135deg, #f8f9fe 0%, #f1f3f9 100%);
        padding: 20px;
        border-radius: 12px 12px 0 0;
        border-bottom: 2px solid #e5e7eb;
    }

    /* Panel lateral de cursos */
    .courses-sidebar {
        background: white;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        padding: 20px;
        max-height: 700px;
        overflow-y: auto;
    }

    .course-item {
        padding: 12px;
        margin-bottom: 8px;
        border-radius: 8px;
        cursor: grab;
        transition: all 0.2s ease;
        border-left: 4px solid;
        background: white;
        box-shadow: 0 2px 6px rgba(0,0,0,0.08);
    }

    .course-item:hover {
        transform: translateX(4px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.12);
    }

    .course-item:active {
        cursor: grabbing;
    }

    .course-item .course-title {
        font-weight: 600;
        font-size: 0.9rem;
        margin-bottom: 4px;
    }

    .course-item .course-code {
        font-size: 0.75rem;
        opacity: 0.7;
    }

    /* Botones de acción */
    .btn-save-all {
        background: var(--success-gradient);
        border: none;
        color: white;
        padding: 12px 24px;
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.3s ease;
        box-shadow: 0 4px 10px rgba(16, 185, 129, 0.3);
    }

    .btn-save-all:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(16, 185, 129, 0.4);
        color: white;
    }

    /* Modal de creación rápida */
    .quick-create-modal .modal-content {
        border-radius: 16px;
        border: none;
        box-shadow: 0 10px 40px rgba(0,0,0,0.15);
    }

    .quick-create-modal .modal-header {
        background: var(--primary-gradient);
        color: white;
        border-radius: 16px 16px 0 0;
        padding: 20px 24px;
    }

    /* Indicador de carga */
    .loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.5);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 9999;
    }

    .loading-overlay.active {
        display: flex;
    }

    .loading-spinner {
        background: white;
        padding: 30px;
        border-radius: 12px;
        text-align: center;
    }

    /* Animaciones */
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .schedule-block {
        animation: fadeIn 0.3s ease;
    }

    /* Scrollbar personalizado */
    .courses-sidebar::-webkit-scrollbar {
        width: 6px;
    }

    .courses-sidebar::-webkit-scrollbar-track {
        background: #f1f3f9;
        border-radius: 10px;
    }

    .courses-sidebar::-webkit-scrollbar-thumb {
        background: #7367f0;
        border-radius: 10px;
    }

    /* Estadísticas */
    .stats-card {
        background: white;
        border-radius: 8px;
        padding: 12px 16px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.08);
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .stats-card .icon {
        width: 40px;
        height: 40px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
    }

    .stats-card .value {
        font-size: 1.5rem;
        font-weight: 700;
        line-height: 1;
    }

    .stats-card .label {
        font-size: 0.75rem;
        color: #6c757d;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Título y breadcrumbs -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="javascript: void(0);">Centro Pre</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('horarios-docentes.index') }}">Horarios</a></li>
                        <li class="breadcrumb-item active">Grilla Visual</li>
                    </ol>
                </div>
                <h4 class="page-title">
                    <i class="mdi mdi-calendar-multiselect me-1"></i>
                    Grilla Visual de Horarios
                    @if ($cicloSeleccionado)
                        <span class="badge bg-primary fs-6 ms-2">{{ $cicloSeleccionado->nombre }}</span>
                    @endif
                </h4>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="filters-bar">
                <div class="row align-items-end g-3">
                    <div class="col-md-3">
                        <label class="form-label fw-bold"><i class="mdi mdi-calendar-sync me-1"></i>Ciclo</label>
                        <select id="filtro-ciclo" class="form-select">
                            @foreach ($ciclos as $ciclo)
                                <option value="{{ $ciclo->id }}" {{ $cicloSeleccionado && $cicloSeleccionado->id == $ciclo->id ? 'selected' : '' }}>
                                    {{ $ciclo->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold"><i class="mdi mdi-door-open me-1"></i>Aula</label>
                        <select id="filtro-aula" class="form-select">
                            @foreach ($aulas as $aula)
                                <option value="{{ $aula->id }}" {{ $aulaSeleccionadaId == $aula->id ? 'selected' : '' }}>
                                    {{ $aula->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold"><i class="mdi mdi-weather-sunset me-1"></i>Turno</label>
                        <select id="filtro-turno" class="form-select">
                            @foreach ($turnos as $turno)
                                <option value="{{ $turno }}" {{ $turnoSeleccionado == $turno ? 'selected' : '' }}>
                                    {{ $turno }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button id="btn-cargar-horarios" class="btn btn-primary w-100">
                            <i class="mdi mdi-refresh me-1"></i>Cargar Horarios
                        </button>
                    </div>
                </div>

                <!-- Estadísticas rápidas -->
                <div class="row mt-3 g-2">
                    <div class="col-md-3">
                        <div class="stats-card">
                            <div class="icon" style="background: linear-gradient(135deg, #00cfe8 0%, #1ce1ff 100%);">
                                <i class="mdi mdi-calendar-check text-white"></i>
                            </div>
                            <div>
                                <div class="value" id="stat-total">0</div>
                                <div class="label">Horarios</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card">
                            <div class="icon" style="background: linear-gradient(135deg, #10b981 0%, #28c76f 100%);">
                                <i class="mdi mdi-book-open-variant text-white"></i>
                            </div>
                            <div>
                                <div class="value" id="stat-cursos">0</div>
                                <div class="label">Cursos</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card">
                            <div class="icon" style="background: linear-gradient(135deg, #ff9f43 0%, #ff8b1b 100%);">
                                <i class="mdi mdi-account-multiple text-white"></i>
                            </div>
                            <div>
                                <div class="value" id="stat-docentes">0</div>
                                <div class="label">Docentes</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stats-card">
                            <div class="icon" style="background: linear-gradient(135deg, #7367f0 0%, #9e8cfc 100%);">
                                <i class="mdi mdi-clock-outline text-white"></i>
                            </div>
                            <div>
                                <div class="value" id="stat-horas">0</div>
                                <div class="label">Horas/Semana</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Contenido principal -->
    <div class="row">
        <!-- Grilla de horarios -->
        <div class="col-lg-9">
            <div class="schedule-grid-container">
                <div class="p-3 d-flex justify-content-between align-items-center border-bottom">
                    <h5 class="mb-0"><i class="mdi mdi-grid me-2"></i>Grilla Semanal</h5>
                    <div>
                        <button id="btn-exportar-pdf" class="btn btn-info btn-sm me-2">
                            <i class="mdi mdi-file-pdf-box me-1"></i>Exportar PDF
                        </button>
                        <a href="{{ route('horarios-docentes.index') }}" class="btn btn-light">
                            <i class="mdi mdi-arrow-left me-1"></i>Volver
                        </a>
                    </div>
                </div>
                
                <div class="schedule-grid" id="schedule-grid">
                    <!-- Encabezado -->
                    <div class="grid-cell header">Hora</div>
                    <div class="grid-cell header">Lunes</div>
                    <div class="grid-cell header">Martes</div>
                    <div class="grid-cell header">Miércoles</div>
                    <div class="grid-cell header">Jueves</div>
                    <div class="grid-cell header">Viernes</div>
                    <div class="grid-cell header">Sábado</div>

                    <!-- Filas de horarios (7:00 AM - 9:00 PM) -->
                    @php
                        $horas = ['07:00', '08:00', '09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00', '18:00', '19:00', '20:00', '21:00'];
                        $dias = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
                        
                        // Función para obtener la siguiente hora
                        function siguienteHora($hora) {
                            $partes = explode(':', $hora);
                            $horaNum = (int)$partes[0] + 1;
                            return str_pad($horaNum, 2, '0', STR_PAD_LEFT) . ':00';
                        }
                    @endphp

                    @foreach ($horas as $hora)
                        <div class="grid-cell time-slot">{{ $hora }} - {{ siguienteHora($hora) }}</div>
                        @foreach ($dias as $dia)
                            <div class="grid-cell" 
                                 data-dia="{{ $dia }}" 
                                 data-hora="{{ $hora }}"
                                 ondrop="drop(event)" 
                                 ondragover="allowDrop(event)">
                            </div>
                        @endforeach
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Sidebar de cursos -->
        <div class="col-lg-3">
            <div class="courses-sidebar">
                <h5 class="mb-3"><i class="mdi mdi-book-multiple me-2"></i>Cursos Disponibles</h5>
                <div class="mb-3">
                    <input type="text" id="search-curso" class="form-control form-control-sm" placeholder="Buscar curso...">
                </div>
                <div id="courses-list">
                    @foreach ($cursos as $curso)
                        <div class="course-item" 
                             draggable="true" 
                             ondragstart="drag(event)"
                             data-curso-id="{{ $curso->id }}"
                             data-curso-nombre="{{ $curso->nombre }}"
                             style="border-left-color: {{ $curso->color ?? '#7367f0' }};">
                            <div class="course-title" style="color: {{ $curso->color ?? '#7367f0' }};">
                                {{ $curso->nombre }}
                            </div>
                            <div class="course-code">Código: {{ $curso->codigo ?? 'N/A' }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de creación rápida -->
<div class="modal fade quick-create-modal" id="quickCreateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="mdi mdi-plus-circle me-2"></i>Crear Horario</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="quick-create-form">
                    <input type="hidden" id="modal-dia">
                    <input type="hidden" id="modal-hora-inicio">
                    
                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="modal-es-receso">
                            <label class="form-check-label" for="modal-es-receso">
                                <i class="mdi mdi-coffee me-1"></i>Es un receso/descanso
                            </label>
                        </div>
                        <small class="text-muted">Los recesos se mostrarán en color verde y no requieren curso ni docente</small>
                    </div>

                    <div class="mb-3" id="curso-field">
                        <label class="form-label">Curso</label>
                        <select id="modal-curso" class="form-select" required>
                            <option value="">Seleccione un curso</option>
                            @foreach ($cursos as $curso)
                                <option value="{{ $curso->id }}" data-color="{{ $curso->color ?? '#7367f0' }}">
                                    {{ $curso->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3" id="dias-receso-field" style="display: none;">
                        <label class="form-label fw-bold">Seleccionar días para el receso:</label>
                        <div class="row">
                            <div class="col-6">
                                <div class="form-check">
                                    <input class="form-check-input dia-checkbox" type="checkbox" id="dia-lunes" value="Lunes">
                                    <label class="form-check-label" for="dia-lunes">Lunes</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input dia-checkbox" type="checkbox" id="dia-martes" value="Martes">
                                    <label class="form-check-label" for="dia-martes">Martes</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input dia-checkbox" type="checkbox" id="dia-miercoles" value="Miércoles">
                                    <label class="form-check-label" for="dia-miercoles">Miércoles</label>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-check">
                                    <input class="form-check-input dia-checkbox" type="checkbox" id="dia-jueves" value="Jueves">
                                    <label class="form-check-label" for="dia-jueves">Jueves</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input dia-checkbox" type="checkbox" id="dia-viernes" value="Viernes">
                                    <label class="form-check-label" for="dia-viernes">Viernes</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input dia-checkbox" type="checkbox" id="dia-sabado" value="Sábado">
                                    <label class="form-check-label" for="dia-sabado">Sábado</label>
                                </div>
                            </div>
                        </div>
                        <div class="mt-2">
                            <button type="button" class="btn btn-sm btn-outline-primary" id="btn-seleccionar-todos-dias">
                                <i class="mdi mdi-checkbox-multiple-marked"></i> Todos
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="btn-deseleccionar-todos-dias">
                                <i class="mdi mdi-checkbox-multiple-blank-outline"></i> Ninguno
                            </button>
                        </div>
                    </div>

                    <div class="mb-3" id="docente-field">
                        <label class="form-label">Docente</label>
                        <select id="modal-docente" class="form-select" required>
                            <option value="">Seleccione un docente</option>
                            @foreach ($docentes as $docente)
                                <option value="{{ $docente->id }}">{{ $docente->nombre_completo }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-6">
                            <label class="form-label">Hora Fin</label>
                            <input type="time" id="modal-hora-fin" class="form-control" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Grupo (Opcional)</label>
                            <input type="text" id="modal-grupo" class="form-control" placeholder="Ej: A-1">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btn-modal-guardar">
                    <i class="mdi mdi-check me-1"></i>Agregar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Loading overlay -->
<div class="loading-overlay" id="loadingOverlay">
    <div class="loading-spinner">
        <div class="spinner-border text-primary mb-3" role="status">
            <span class="visually-hidden">Cargando...</span>
        </div>
        <div>Procesando...</div>
    </div>
</div>

<!-- Data para JavaScript -->
<script>
    window.scheduleData = {
        cicloId: {{ $cicloSeleccionado->id ?? 'null' }},
        aulaId: {{ $aulaSeleccionadaId ?? 'null' }},
        turno: '{{ $turnoSeleccionado }}',
        cursos: @json($cursos),
        docentes: @json($docentes),
        routes: {
            getSchedules: '{{ route('horarios-docentes.get-schedules') }}',
            bulkStore: '{{ route('horarios-docentes.bulk-store') }}',
            delete: '{{ route('horarios-docentes.delete', ':id') }}'
        }
    };
</script>
@endsection

@push('js')
<script src="{{ asset('assets/js/horarios-grid.js') }}"></script>
@endpush
