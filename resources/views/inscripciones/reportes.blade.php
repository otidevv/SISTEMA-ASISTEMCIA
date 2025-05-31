{{-- resources/views/inscripciones/index.blade.php --}}

@extends('layouts.app')

@section('title', 'Gestión de Inscripciones')

@push('css')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endpush

@push('js')
    <script>
        window.default_server = "{{ url('/') }}";
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="{{ asset('js/inscripciones/index.js') }}"></script>
@endpush

@section('content')
    <!-- start page title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">Gestión de Inscripciones</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Inscripciones</li>
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
                            <h4 class="header-title mt-0 mb-1">Lista de Inscripciones</h4>
                        </div>
                        <div class="col-sm-8">
                            <div class="text-sm-end">
                                @if (Auth::user()->hasPermission('inscripciones.export'))
                                    <button type="button" class="btn btn-success mb-2" id="exportarInscripciones">
                                        <i class="mdi mdi-file-excel me-1"></i> Exportar
                                    </button>
                                @endif
                                @if (Auth::user()->hasPermission('inscripciones.create'))
                                    <button type="button" class="btn btn-primary mb-2" data-bs-toggle="modal"
                                        data-bs-target="#newInscripcionModal">
                                        <i class="mdi mdi-plus-circle me-1"></i> Nueva Inscripción
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Filtros -->
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <select class="form-select" id="filtro-ciclo">
                                <option value="">Todos los ciclos</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" id="filtro-carrera">
                                <option value="">Todas las carreras</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" id="filtro-turno">
                                <option value="">Todos los turnos</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" id="filtro-estado">
                                <option value="">Todos los estados</option>
                                <option value="activo">Activo</option>
                                <option value="inactivo">Inactivo</option>
                                <option value="retirado">Retirado</option>
                                <option value="egresado">Egresado</option>
                                <option value="trasladado">Trasladado</option>
                            </select>
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

                    <table id="inscripciones-datatable" class="table dt-responsive nowrap w-100">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Estudiante</th>
                                <th>Carrera</th>
                                <th>Ciclo</th>
                                <th>Turno</th>
                                <th>Aula</th>
                                <th>Fecha Inscripción</th>
                                <th>Estado</th>
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
    <!-- Modal para Nueva Inscripción -->
    <div class="modal fade" id="newInscripcionModal" tabindex="-1" role="dialog"
        aria-labelledby="newInscripcionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="newInscripcionModalLabel">Nueva Inscripción</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="newInscripcionForm">
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="estudiante_id" class="form-label">Estudiante</label>
                                <select class="form-select" id="estudiante_id" name="estudiante_id" required>
                                    <option value="">Seleccione un estudiante...</option>
                                </select>
                                <small class="text-muted">Solo se muestran estudiantes sin inscripción activa (identificados
                                    por documento)</small>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="carrera_id" class="form-label">Carrera</label>
                                <select class="form-select" id="carrera_id" name="carrera_id" required>
                                    <option value="">Seleccione una carrera...</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="ciclo_id" class="form-label">Ciclo Académico</label>
                                <select class="form-select" id="ciclo_id" name="ciclo_id" required>
                                    <option value="">Seleccione un ciclo...</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="turno_id" class="form-label">Turno</label>
                                <select class="form-select" id="turno_id" name="turno_id" required>
                                    <option value="">Seleccione un turno...</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="aula_id" class="form-label">Aula</label>
                                <select class="form-select" id="aula_id" name="aula_id" required>
                                    <option value="">Seleccione un aula...</option>
                                </select>
                                <small class="text-muted">Se muestran aulas con capacidad disponible</small>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="fecha_inscripcion" class="form-label">Fecha de Inscripción</label>
                                <input type="date" class="form-control" id="fecha_inscripcion"
                                    name="fecha_inscripcion" value="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="estado_inscripcion" class="form-label">Estado</label>
                                <select class="form-select" id="estado_inscripcion" name="estado_inscripcion" required>
                                    <option value="activo" selected>Activo</option>
                                    <option value="inactivo">Inactivo</option>
                                    <option value="retirado">Retirado</option>
                                    <option value="egresado">Egresado</option>
                                    <option value="trasladado">Trasladado</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="observaciones" class="form-label">Observaciones</label>
                                <textarea class="form-control" id="observaciones" name="observaciones" rows="3"
                                    placeholder="Observaciones adicionales..."></textarea>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="saveNewInscripcion">Guardar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Editar Inscripción -->
    <div class="modal fade" id="editInscripcionModal" tabindex="-1" role="dialog"
        aria-labelledby="editInscripcionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editInscripcionModalLabel">Editar Inscripción</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editInscripcionForm">
                        <input type="hidden" id="edit_inscripcion_id" name="id">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_codigo_inscripcion" class="form-label">Código de Inscripción</label>
                                <input type="text" class="form-control" id="edit_codigo_inscripcion" readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_estudiante_nombre" class="form-label">Estudiante</label>
                                <input type="text" class="form-control" id="edit_estudiante_nombre" readonly>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_carrera_id" class="form-label">Carrera</label>
                                <select class="form-select" id="edit_carrera_id" name="carrera_id" required>
                                    <option value="">Seleccione una carrera...</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_ciclo_id" class="form-label">Ciclo Académico</label>
                                <select class="form-select" id="edit_ciclo_id" name="ciclo_id" required>
                                    <option value="">Seleccione un ciclo...</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_turno_id" class="form-label">Turno</label>
                                <select class="form-select" id="edit_turno_id" name="turno_id" required>
                                    <option value="">Seleccione un turno...</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_aula_id" class="form-label">Aula</label>
                                <select class="form-select" id="edit_aula_id" name="aula_id" required>
                                    <option value="">Seleccione un aula...</option>
                                </select>
                                <small class="text-muted">Se muestran aulas con capacidad disponible</small>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_fecha_inscripcion" class="form-label">Fecha de Inscripción</label>
                                <input type="date" class="form-control" id="edit_fecha_inscripcion"
                                    name="fecha_inscripcion" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_estado_inscripcion" class="form-label">Estado</label>
                                <select class="form-select" id="edit_estado_inscripcion" name="estado_inscripcion"
                                    required>
                                    <option value="activo">Activo</option>
                                    <option value="inactivo">Inactivo</option>
                                    <option value="retirado">Retirado</option>
                                    <option value="egresado">Egresado</option>
                                    <option value="trasladado">Trasladado</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_fecha_retiro" class="form-label">Fecha de Retiro</label>
                                <input type="date" class="form-control" id="edit_fecha_retiro" name="fecha_retiro">
                                <small class="text-muted">Solo si el estado es retirado o trasladado</small>
                            </div>
                        </div>
                        <div class="row" id="motivo_retiro_row" style="display: none;">
                            <div class="col-md-12 mb-3">
                                <label for="edit_motivo_retiro" class="form-label">Motivo de Retiro</label>
                                <textarea class="form-control" id="edit_motivo_retiro" name="motivo_retiro" rows="2"
                                    placeholder="Especifique el motivo del retiro..."></textarea>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="edit_observaciones" class="form-label">Observaciones</label>
                                <textarea class="form-control" id="edit_observaciones" name="observaciones" rows="3"
                                    placeholder="Observaciones adicionales..."></textarea>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="updateInscripcion">Actualizar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Ver Detalles -->
    <div class="modal fade" id="viewInscripcionModal" tabindex="-1" role="dialog"
        aria-labelledby="viewInscripcionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewInscripcionModalLabel">Detalles de Inscripción</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="inscripcion-details">
                        <!-- Los detalles se cargarán dinámicamente -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
@endpush
