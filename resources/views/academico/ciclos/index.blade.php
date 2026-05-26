{{-- resources/views/ciclos/index.blade.php --}}

@extends('layouts.app')

@section('title', 'Gestión de Ciclos Académicos')

@push('css')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">
@endpush

@push('js')
    <script>
        window.default_server = "{{ url('/') }}";
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    @vite('resources/js/ciclos/index.js')
@endpush

@section('content')
    <!-- start page title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">Gestión de Ciclos Académicos</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Ciclos</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <!-- end page title -->

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row mb-2">
                        <div class="col-sm-4">
                            <h4 class="header-title mt-0 mb-1">Lista de Ciclos Académicos</h4>
                        </div>
                        <div class="col-sm-8">
                            <div class="text-sm-end">
                                @if (Auth::user()->hasPermission('ciclos.create'))
                                    <button type="button" class="btn btn-primary mb-2" data-bs-toggle="modal"
                                        data-bs-target="#newCicloModal">
                                        <i class="mdi mdi-plus-circle me-1"></i> Nuevo Ciclo
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif

                    <table id="ciclos-datatable" class="table dt-responsive nowrap w-100">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Código</th>
                                <th>Nombre</th>
                                <th>Fecha Inicio</th>
                                <th>Fecha Fin</th>
                                <th>Próximo Examen</th>
                                <th>Límites de Asistencia</th>
                                <th>Progreso</th>
                                <th>Estado</th>
                                <th>Activo</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>

                </div>
            </div>
        </div>
    </div>
@endsection

@push('modals')
    <!-- Modal para Nuevo Ciclo -->
    <div class="modal fade" id="newCicloModal" tabindex="-1" role="dialog" aria-labelledby="newCicloModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="newCicloModalLabel">Nuevo Ciclo Académico</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="newCicloForm">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="codigo" class="form-label">Código del Ciclo</label>
                                <input type="text" class="form-control" id="codigo" name="codigo" required
                                    placeholder="Ej: 2025-1">
                                <small class="text-muted">Formato: AÑO-PERIODO (Ej: 2025-1, 2025-2)</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="nombre" class="form-label">Nombre del Ciclo</label>
                                <input type="text" class="form-control" id="nombre" name="nombre" required
                                    placeholder="Ej: Ciclo I - 2025">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="programa_id" class="form-label">Programa Académico <span class="text-danger">*</span></label>
                                <select class="form-select" id="programa_id" name="programa_id" required>
                                    <option value="">Seleccione Programa...</option>
                                    @foreach($programas as $programa)
                                        <option value="{{ $programa->id }}">{{ $programa->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="descripcion" class="form-label">Descripción</label>
                                <textarea class="form-control" id="descripcion" name="descripcion" rows="2"
                                    placeholder="Descripción del ciclo académico..."></textarea>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="fecha_inicio" class="form-label">Fecha de Inicio</label>
                                <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="fecha_fin" class="form-label">Fecha de Fin</label>
                                <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="porcentaje_amonestacion" class="form-label">Límite para Amonestación (%)</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="porcentaje_amonestacion"
                                        name="porcentaje_amonestacion" value="20" min="0" max="100"
                                        step="0.01">
                                    <span class="input-group-text">%</span>
                                </div>
                                <small class="text-muted">Porcentaje de faltas para ser amonestado</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="porcentaje_inhabilitacion" class="form-label">Límite para Inhabilitación
                                    (%)</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="porcentaje_inhabilitacion"
                                        name="porcentaje_inhabilitacion" value="30" min="0" max="100"
                                        step="0.01">
                                    <span class="input-group-text">%</span>
                                </div>
                                <small class="text-muted">Porcentaje de faltas para ser inhabilitado</small>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 mb-2">
                                <h6 class="text-muted">Fechas de Exámenes</h6>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="fecha_primer_examen" class="form-label">Primer Examen</label>
                                <input type="date" class="form-control" id="fecha_primer_examen"
                                    name="fecha_primer_examen">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="fecha_segundo_examen" class="form-label">Segundo Examen</label>
                                <input type="date" class="form-control" id="fecha_segundo_examen"
                                    name="fecha_segundo_examen">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="fecha_tercer_examen" class="form-label">Tercer Examen</label>
                                <input type="date" class="form-control" id="fecha_tercer_examen"
                                    name="fecha_tercer_examen">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="estado" class="form-label">Estado</label>
                                <select class="form-select" id="estado" name="estado" required>
                                    <option value="">Seleccione...</option>
                                    <option value="planificado">Planificado</option>
                                    <option value="en_curso">En Curso</option>
                                    <option value="finalizado">Finalizado</option>
                                    <option value="cancelado">Cancelado</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="correlativo_inicial" class="form-label">Correlativo Inicial</label>
                                <input type="number" class="form-control" id="correlativo_inicial" name="correlativo_inicial" 
                                    value="1" min="1" required>
                                <small class="text-muted">Número inicial para el correlativo de inscripciones</small>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="incluye_sabados" name="incluye_sabados" value="1">
                                    <label class="form-check-label" for="incluye_sabados">
                                        <strong>Incluir Clases los Sábados</strong>
                                    </label>
                                    <br>
                                    <small class="text-muted">
                                        <i class="mdi mdi-information-outline"></i> 
                                        Marque esta opción si el ciclo tendrá clases los días sábados. Si no se marca, el ciclo solo tendrá clases de lunes a viernes.
                                    </small>
                                </div>
                            </div>
                        </div>
                        <!-- Periodo de Inscripciones -->
                        <div class="row border-top pt-3 mt-2">
                            <div class="col-md-12 mb-2">
                                <h6 class="text-info"><i class="mdi mdi-calendar-clock me-1"></i>Periodo de Inscripciones</h6>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <div class="form-check form-switch pt-2">
                                    <input class="form-check-input" type="checkbox" id="inscripciones_abiertas" name="inscripciones_abiertas" value="1" checked>
                                    <label class="form-check-label" for="inscripciones_abiertas">
                                        <strong>Inscripciones Habilitadas</strong>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="fecha_inicio_inscripcion" class="form-label">Fecha de Inicio Inscripción</label>
                                <input type="datetime-local" class="form-control" id="fecha_inicio_inscripcion" name="fecha_inicio_inscripcion">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="fecha_fin_inscripcion" class="form-label">Fecha de Fin Inscripción</label>
                                <input type="datetime-local" class="form-control" id="fecha_fin_inscripcion" name="fecha_fin_inscripcion">
                            </div>
                        </div>
                        <!-- NUEVO: Horarios de Receso -->
                        <div class="row">
                            <div class="col-md-12 mb-2">
                                <h6 class="text-muted"><i class="mdi mdi-coffee-outline me-1"></i>Horarios de Receso</h6>
                                <small class="text-muted">Deje vacío si el ciclo no tiene receso en ese turno</small>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label for="receso_manana_inicio" class="form-label">Receso Mañana Inicio</label>
                                <input type="time" class="form-control" id="receso_manana_inicio" name="receso_manana_inicio">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="receso_manana_fin" class="form-label">Receso Mañana Fin</label>
                                <input type="time" class="form-control" id="receso_manana_fin" name="receso_manana_fin">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="receso_tarde_inicio" class="form-label">Receso Tarde Inicio</label>
                                <input type="time" class="form-control" id="receso_tarde_inicio" name="receso_tarde_inicio">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="receso_tarde_fin" class="form-label">Receso Tarde Fin</label>
                                <input type="time" class="form-control" id="receso_tarde_fin" name="receso_tarde_fin">
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-info" id="saveAndConfigVacantes">
                        <i class="mdi mdi-cog me-1"></i> Guardar y Configurar Vacantes
                    </button>
                    <button type="button" class="btn btn-primary" id="saveNewCiclo">Guardar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Editar Ciclo -->
    <div class="modal fade" id="editCicloModal" tabindex="-1" role="dialog" aria-labelledby="editCicloModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editCicloModalLabel">Editar Ciclo Académico</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editCicloForm">
                        <input type="hidden" id="edit_ciclo_id" name="id">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_codigo" class="form-label">Código del Ciclo</label>
                                <input type="text" class="form-control" id="edit_codigo" name="codigo" required
                                    placeholder="Ej: 2025-1">
                                <small class="text-muted">Formato: AÑO-PERIODO (Ej: 2025-1, 2025-2)</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_nombre" class="form-label">Nombre del Ciclo</label>
                                <input type="text" class="form-control" id="edit_nombre" name="nombre" required
                                    placeholder="Ej: Ciclo I - 2025">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="edit_programa_id" class="form-label">Programa Académico <span class="text-danger">*</span></label>
                                <select class="form-select" id="edit_programa_id" name="programa_id" required>
                                    <option value="">Seleccione Programa...</option>
                                    @foreach($programas as $programa)
                                        <option value="{{ $programa->id }}">{{ $programa->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="edit_descripcion" class="form-label">Descripción</label>
                                <textarea class="form-control" id="edit_descripcion" name="descripcion" rows="2"
                                    placeholder="Descripción del ciclo académico..."></textarea>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_fecha_inicio" class="form-label">Fecha de Inicio</label>
                                <input type="date" class="form-control" id="edit_fecha_inicio" name="fecha_inicio"
                                    required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_fecha_fin" class="form-label">Fecha de Fin</label>
                                <input type="date" class="form-control" id="edit_fecha_fin" name="fecha_fin"
                                    required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_porcentaje_amonestacion" class="form-label">Límite para Amonestación
                                    (%)</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="edit_porcentaje_amonestacion"
                                        name="porcentaje_amonestacion" min="0" max="100" step="0.01">
                                    <span class="input-group-text">%</span>
                                </div>
                                <small class="text-muted">Porcentaje de faltas para ser amonestado</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_porcentaje_inhabilitacion" class="form-label">Límite para Inhabilitación
                                    (%)</label>
                                <div class="input-group">
                                    <input type="number" class="form-control" id="edit_porcentaje_inhabilitacion"
                                        name="porcentaje_inhabilitacion" min="0" max="100" step="0.01">
                                    <span class="input-group-text">%</span>
                                </div>
                                <small class="text-muted">Porcentaje de faltas para ser inhabilitado</small>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 mb-2">
                                <h6 class="text-muted">Fechas de Exámenes</h6>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="edit_fecha_primer_examen" class="form-label">Primer Examen</label>
                                <input type="date" class="form-control" id="edit_fecha_primer_examen"
                                    name="fecha_primer_examen">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="edit_fecha_segundo_examen" class="form-label">Segundo Examen</label>
                                <input type="date" class="form-control" id="edit_fecha_segundo_examen"
                                    name="fecha_segundo_examen">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="edit_fecha_tercer_examen" class="form-label">Tercer Examen</label>
                                <input type="date" class="form-control" id="edit_fecha_tercer_examen"
                                    name="fecha_tercer_examen">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_estado" class="form-label">Estado</label>
                                <select class="form-select" id="edit_estado" name="estado" required>
                                    <option value="">Seleccione...</option>
                                    <option value="planificado">Planificado</option>
                                    <option value="en_curso">En Curso</option>
                                    <option value="finalizado">Finalizado</option>
                                    <option value="cancelado">Cancelado</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_correlativo_inicial" class="form-label">Correlativo Inicial</label>
                                <input type="number" class="form-control" id="edit_correlativo_inicial" name="correlativo_inicial" 
                                    min="1" required>
                                <small class="text-muted">Número inicial para el correlativo de inscripciones</small>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="edit_incluye_sabados" name="incluye_sabados" value="1">
                                    <label class="form-check-label" for="edit_incluye_sabados">
                                        <strong>Incluir Clases los Sábados</strong>
                                    </label>
                                    <br>
                                    <small class="text-muted">
                                        <i class="mdi mdi-information-outline"></i> 
                                        Marque esta opción si el ciclo tendrá clases los días sábados. Si no se marca, el ciclo solo tendrá clases de lunes a viernes.
                                    </small>
                                </div>
                            </div>
                        </div>
                        <!-- Periodo de Inscripciones -->
                        <div class="row border-top pt-3 mt-2">
                            <div class="col-md-12 mb-2">
                                <h6 class="text-info"><i class="mdi mdi-calendar-clock me-1"></i>Periodo de Inscripciones</h6>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <div class="form-check form-switch pt-2">
                                    <input class="form-check-input" type="checkbox" id="edit_inscripciones_abiertas" name="inscripciones_abiertas" value="1">
                                    <label class="form-check-label" for="edit_inscripciones_abiertas">
                                        <strong>Inscripciones Habilitadas</strong>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="edit_fecha_inicio_inscripcion" class="form-label">Fecha de Inicio Inscripción</label>
                                <input type="datetime-local" class="form-control" id="edit_fecha_inicio_inscripcion" name="fecha_inicio_inscripcion">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="edit_fecha_fin_inscripcion" class="form-label">Fecha de Fin Inscripción</label>
                                <input type="datetime-local" class="form-control" id="edit_fecha_fin_inscripcion" name="fecha_fin_inscripcion">
                            </div>
                        </div>
                        <!-- NUEVO: Horarios de Receso -->
                        <div class="row">
                            <div class="col-md-12 mb-2">
                                <h6 class="text-muted"><i class="mdi mdi-coffee-outline me-1"></i>Horarios de Receso</h6>
                                <small class="text-muted">Deje vacío si el ciclo no tiene receso en ese turno</small>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label for="edit_receso_manana_inicio" class="form-label">Receso Mañana Inicio</label>
                                <input type="time" class="form-control" id="edit_receso_manana_inicio" name="receso_manana_inicio">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="edit_receso_manana_fin" class="form-label">Receso Mañana Fin</label>
                                <input type="time" class="form-control" id="edit_receso_manana_fin" name="receso_manana_fin">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="edit_receso_tarde_inicio" class="form-label">Receso Tarde Inicio</label>
                                <input type="time" class="form-control" id="edit_receso_tarde_inicio" name="receso_tarde_inicio">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="edit_receso_tarde_fin" class="form-label">Receso Tarde Fin</label>
                                <input type="time" class="form-control" id="edit_receso_tarde_fin" name="receso_tarde_fin">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="edit_porcentaje" class="form-label">Porcentaje de Avance</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" id="edit_porcentaje" readonly>
                                    <span class="input-group-text">%</span>
                                </div>
                                <small class="text-muted">Calculado automáticamente según las fechas</small>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-warning" id="configVacantesBtn">
                        <i class="mdi mdi-account-group me-1"></i> Configurar Vacantes
                    </button>
                    <button type="button" class="btn btn-primary" id="updateCiclo">Actualizar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Gestionar Vacantes -->
    <div class="modal fade" id="vacantesModal" tabindex="-1" role="dialog" aria-labelledby="vacantesModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="vacantesModalLabel">
                        <i class="mdi mdi-account-group me-1"></i> Gestión de Vacantes por Carrera
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-12">
                            <div class="alert alert-info mb-3">
                                <h6 class="alert-heading">
                                    <i class="mdi mdi-information-outline me-1"></i> 
                                    Ciclo: <span id="vacantes-ciclo-nombre"></span>
                                </h6>
                                <div class="row mt-2">
                                    <div class="col-md-3">
                                        <small><strong>Total Carreras:</strong> <span id="total-carreras">0</span></small>
                                    </div>
                                    <div class="col-md-3">
                                        <small><strong>Total Vacantes:</strong> <span id="total-vacantes">0</span></small>
                                    </div>
                                    <div class="col-md-3">
                                        <small><strong>Vacantes Ocupadas:</strong> <span id="vacantes-ocupadas">0</span></small>
                                    </div>
                                    <div class="col-md-3">
                                        <small><strong>Vacantes Disponibles:</strong> <span id="vacantes-disponibles">0</span></small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                    <!-- Tabla de vacantes existentes -->
                    <div class="card">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">
                                <i class="mdi mdi-table me-1"></i> Vacantes Configuradas
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover table-sm" id="tablaVacantes">
                                    <thead>
                                        <tr>
                                            <th>Carrera</th>
                                            <th>Código</th>
                                            <th class="text-center">Total Vacantes</th>
                                            <th class="text-center">Ocupadas</th>
                                            <th class="text-center">Disponibles</th>
                                            <th class="text-center">% Ocupación</th>
                                                            <th>Observaciones</th>
                                            <th class="text-center">Estado</th>
                                            <th class="text-center">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody id="vacantesTableBody">
                                        <!-- Se llenará dinámicamente -->
                                    </tbody>
                                </table>
                                <div id="noVacantesMessage" class="text-center py-4 text-muted" style="display: none;">
                                    <i class="mdi mdi-information-outline fs-1"></i>
                                    <p>No hay vacantes configuradas para este ciclo</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary" id="guardarTodosVacantes">
                        <i class="mdi mdi-content-save me-1"></i> Guardar Cambios
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Gestionar Recuperaciones (Sábados/Otros) -->
    <div class="modal fade" id="recuperacionesModal" tabindex="-1" role="dialog" aria-labelledby="recuperacionesModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="recuperacionesModalLabel">
                        <i class="mdi mdi-calendar-clock me-1"></i> Recuperación de Clases (Sábados/Otros)
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <h6 class="alert-heading">
                            <i class="mdi mdi-information-outline me-1"></i>
                            Ciclo: <span id="recuperaciones-ciclo-nombre" class="fw-bold"></span>
                        </h6>
                        <p class="mb-0 small">
                            Configure fechas específicas (por ejemplo, sábados de recuperación) en las que se impartirán clases con el horario de otro día de la semana. Estas fechas serán consideradas días hábiles y afectarán la carga horaria, planillas y control de asistencia de los docentes.
                        </p>
                    </div>

                    <!-- Formulario para agregar una nueva fecha de recuperación -->
                    <div class="card mb-3 border-light">
                        <div class="card-body bg-light">
                            <h6 class="card-title mb-3">Agregar Fecha de Recuperación</h6>
                            <form id="addRecuperacionForm" class="row g-3 align-items-end">
                                <div class="col-md-5">
                                    <label for="recuperacion_fecha" class="form-label">Fecha a Recuperar</label>
                                    <input type="date" class="form-control" id="recuperacion_fecha" required>
                                </div>
                                <div class="col-md-5">
                                    <label for="recuperacion_dia" class="form-label">Horario del Día a Aplicar</label>
                                    <select class="form-select" id="recuperacion_dia" required>
                                        <option value="">Seleccione día...</option>
                                        <option value="Lunes">Lunes</option>
                                        <option value="Martes">Martes</option>
                                        <option value="Miércoles">Miércoles</option>
                                        <option value="Jueves">Jueves</option>
                                        <option value="Viernes">Viernes</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-info w-100" id="btnAgregarRecuperacion">
                                        <i class="mdi mdi-plus"></i> Agregar
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Tabla de fechas de recuperación -->
                    <div class="card border-light">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">Fechas de Recuperación Programadas</h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover mb-0" id="tablaRecuperaciones">
                                    <thead>
                                        <tr>
                                            <th>Fecha</th>
                                            <th>Día de la Semana Real</th>
                                            <th>Horario del Día que Aplica</th>
                                            <th class="text-center" style="width: 100px;">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody id="recuperacionesTableBody">
                                        <!-- Se llenará dinámicamente -->
                                    </tbody>
                                </table>
                                <div id="noRecuperacionesMessage" class="text-center py-4 text-muted">
                                    <i class="mdi mdi-calendar-blank fs-1"></i>
                                    <p class="mb-0 mt-1">No hay fechas de recuperación programadas para este ciclo</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary" id="btnGuardarRecuperaciones">
                        <i class="mdi mdi-content-save me-1"></i> Guardar Cambios
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Estructura de Examen (Preguntas por Grupo) -->
    <div class="modal fade" id="examenEstructuraModal" tabindex="-1" role="dialog" aria-labelledby="examenEstructuraModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-secondary text-white">
                    <h5 class="modal-title" id="examenEstructuraModalLabel">
                        <i class="mdi mdi-format-list-numbered me-1"></i> Estructura y Distribución de Preguntas del Examen
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <h6 class="alert-heading">
                            <i class="mdi mdi-information-outline me-1"></i>
                            Ciclo: <span id="examen-ciclo-nombre" class="fw-bold"></span>
                        </h6>
                        <p class="mb-0 small">
                            Configure la cantidad de preguntas asignadas a cada curso/asignatura por grupo de carrera, así como el tema de examen correspondiente y los puntajes. Los totales se calcularán de manera automática.
                        </p>
                    </div>

                    <form id="examenEstructuraForm">
                        <!-- Configuraciones generales de grupos -->
                        <div class="row mb-4">
                            <!-- Grupo A -->
                            <div class="col-md-4">
                                <div class="card border border-primary">
                                    <div class="card-header bg-soft-primary text-primary py-2 text-center">
                                        <h6 class="mb-0 fw-bold">GRUPO A (Ingenierías)</h6>
                                    </div>
                                    <div class="card-body p-3">
                                        <div class="mb-2">
                                            <label class="form-label mb-1">Tema por Defecto</label>
                                            <input type="text" class="form-control form-control-sm" name="configs[A][tema]" id="config_A_tema" placeholder="Ej: P">
                                        </div>
                                        <div class="mb-2">
                                            <label class="form-label mb-1">Duración (minutos)</label>
                                            <input type="number" class="form-control form-control-sm" name="configs[A][duracion_minutos]" id="config_A_duracion" required min="1">
                                        </div>
                                        <div class="row">
                                            <div class="col-6 mb-2">
                                                <label class="form-label mb-1">Puntaje Máx.</label>
                                                <input type="number" class="form-control form-control-sm" name="configs[A][puntaje_maximo]" id="config_A_max" required min="0">
                                            </div>
                                            <div class="col-6 mb-2">
                                                <label class="form-label mb-1">Puntaje Mín.</label>
                                                <input type="number" class="form-control form-control-sm" name="configs[A][puntaje_minimo_aprobatorio]" id="config_A_min" required min="0">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Grupo B -->
                            <div class="col-md-4">
                                <div class="card border border-success">
                                    <div class="card-header bg-soft-success text-success py-2 text-center">
                                        <h6 class="mb-0 fw-bold">GRUPO B (Salud)</h6>
                                    </div>
                                    <div class="card-body p-3">
                                        <div class="mb-2">
                                            <label class="form-label mb-1">Tema por Defecto</label>
                                            <input type="text" class="form-control form-control-sm" name="configs[B][tema]" id="config_B_tema" placeholder="Ej: Q">
                                        </div>
                                        <div class="mb-2">
                                            <label class="form-label mb-1">Duración (minutos)</label>
                                            <input type="number" class="form-control form-control-sm" name="configs[B][duracion_minutos]" id="config_B_duracion" required min="1">
                                        </div>
                                        <div class="row">
                                            <div class="col-6 mb-2">
                                                <label class="form-label mb-1">Puntaje Máx.</label>
                                                <input type="number" class="form-control form-control-sm" name="configs[B][puntaje_maximo]" id="config_B_max" required min="0">
                                            </div>
                                            <div class="col-6 mb-2">
                                                <label class="form-label mb-1">Puntaje Mín.</label>
                                                <input type="number" class="form-control form-control-sm" name="configs[B][puntaje_minimo_aprobatorio]" id="config_B_min" required min="0">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Grupo C -->
                            <div class="col-md-4">
                                <div class="card border border-purple" style="border-color: #6f42c1 !important;">
                                    <div class="card-header bg-soft-purple py-2 text-center" style="background-color: rgba(111, 66, 193, 0.1); color: #6f42c1;">
                                        <h6 class="mb-0 fw-bold">GRUPO C (Sociales)</h6>
                                    </div>
                                    <div class="card-body p-3">
                                        <div class="mb-2">
                                            <label class="form-label mb-1">Tema por Defecto</label>
                                            <input type="text" class="form-control form-control-sm" name="configs[C][tema]" id="config_C_tema" placeholder="Ej: R">
                                        </div>
                                        <div class="mb-2">
                                            <label class="form-label mb-1">Duración (minutos)</label>
                                            <input type="number" class="form-control form-control-sm" name="configs[C][duracion_minutos]" id="config_C_duracion" required min="1">
                                        </div>
                                        <div class="row">
                                            <div class="col-6 mb-2">
                                                <label class="form-label mb-1">Puntaje Máx.</label>
                                                <input type="number" class="form-control form-control-sm" name="configs[C][puntaje_maximo]" id="config_C_max" required min="0">
                                            </div>
                                            <div class="col-6 mb-2">
                                                <label class="form-label mb-1">Puntaje Mín.</label>
                                                <input type="number" class="form-control form-control-sm" name="configs[C][puntaje_minimo_aprobatorio]" id="config_C_min" required min="0">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tabla de asignaturas -->
                        <div class="card">
                            <div class="card-header bg-light py-2">
                                <h6 class="mb-0 fw-bold"><i class="mdi mdi-table me-1"></i> Distribución de Preguntas por Curso</h6>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover table-centered mb-0" id="tablaEstructuraExamen">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Asignatura (Curso)</th>
                                                <th>Código</th>
                                                <th class="text-center" style="width: 180px;">Grupo A (Preguntas)</th>
                                                <th class="text-center" style="width: 180px;">Grupo B (Preguntas)</th>
                                                <th class="text-center" style="width: 180px;">Grupo C (Preguntas)</th>
                                            </tr>
                                        </thead>
                                        <tbody id="estructuraExamenTableBody">
                                            <!-- Se llenará dinámicamente -->
                                        </tbody>
                                        <tfoot class="table-light">
                                            <tr style="border-top: 2px solid #dee2e6;">
                                                <td colspan="2" class="text-end fw-bold">TOTAL PREGUNTAS:</td>
                                                <td class="text-center fw-bold text-primary fs-5" id="total_preguntas_A">0</td>
                                                <td class="text-center fw-bold text-success fs-5" id="total_preguntas_B">0</td>
                                                <td class="text-center fw-bold text-purple fs-5" style="color: #6f42c1;" id="total_preguntas_C">0</td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btnGuardarEstructura">
                        <i class="mdi mdi-content-save me-1"></i> Guardar Estructura
                    </button>
                </div>
            </div>
        </div>
    </div>
@endpush

