{{-- resources/views/turnos/index.blade.php --}}

@extends('layouts.app')

@section('title', 'Gestión de Turnos')

@push('css')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">
@endpush

@push('js')
    <script>
        window.default_server = "{{ url('/') }}";
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="{{ asset('js/turnos/index.js') }}"></script>
@endpush

@section('content')
    <!-- start page title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">Gestión de Turnos</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Turnos</li>
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
                            <h4 class="header-title mt-0 mb-1">Lista de Turnos</h4>
                        </div>
                        <div class="col-sm-8">
                            <div class="text-sm-end">
                                @if (Auth::user()->hasPermission('turnos.create'))
                                    <button type="button" class="btn btn-primary mb-2" data-bs-toggle="modal"
                                        data-bs-target="#newTurnoModal">
                                        <i class="mdi mdi-plus-circle me-1"></i> Nuevo Turno
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>

                    <table id="turnos-datatable" class="table dt-responsive nowrap w-100">
                        <thead>
                            <tr>
                                <th>Orden</th>
                                <th>Código</th>
                                <th>Nombre</th>
                                <th>Horario</th>
                                <th>Duración</th>
                                <th>Días</th>
                                <th>Estudiantes</th>
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
    <!-- Modal para Nuevo Turno -->
    <div class="modal fade" id="newTurnoModal" tabindex="-1" role="dialog" aria-labelledby="newTurnoModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="newTurnoModalLabel">Nuevo Turno</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="newTurnoForm">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="codigo" class="form-label">Código</label>
                                <input type="text" class="form-control" id="codigo" name="codigo" required
                                    placeholder="Ej: M, T, N" maxlength="20">
                                <small class="text-muted">Código único del turno</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="nombre" class="form-label">Nombre del Turno</label>
                                <input type="text" class="form-control" id="nombre" name="nombre" required
                                    placeholder="Ej: Mañana, Tarde, Noche">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="hora_inicio" class="form-label">Hora de Inicio (General)</label>
                                <input type="time" class="form-control" id="hora_inicio" name="hora_inicio" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="hora_fin" class="form-label">Hora de Fin (General)</label>
                                <input type="time" class="form-control" id="hora_fin" name="hora_fin" required>
                            </div>
                        </div>

                        <h5 class="text-uppercase bg-light p-2 mt-0 mb-3">Configuración de Asistencia</h5>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="hora_entrada_inicio" class="form-label text-success">Entrada Inicio</label>
                                <input type="time" class="form-control" id="hora_entrada_inicio" name="hora_entrada_inicio">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="hora_entrada_fin" class="form-label text-success">Entrada Fin</label>
                                <input type="time" class="form-control" id="hora_entrada_fin" name="hora_entrada_fin">
                                <small class="text-muted d-block mt-1">Límite para entrada normal</small>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="hora_tarde_inicio" class="form-label text-warning">Tarde Inicio</label>
                                <input type="time" class="form-control" id="hora_tarde_inicio" name="hora_tarde_inicio">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="hora_tarde_fin" class="form-label text-warning">Tarde Fin</label>
                                <input type="time" class="form-control" id="hora_tarde_fin" name="hora_tarde_fin">
                                <small class="text-muted d-block mt-1">Límite para considerar tardanza</small>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="hora_salida_inicio" class="form-label text-danger">Salida Inicio</label>
                                <input type="time" class="form-control" id="hora_salida_inicio" name="hora_salida_inicio">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="hora_salida_fin" class="form-label text-danger">Salida Fin</label>
                                <input type="time" class="form-control" id="hora_salida_fin" name="hora_salida_fin">
                                <small class="text-muted d-block mt-1">Límite para marcar salida</small>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="dias_semana" class="form-label">Días de la Semana</label>
                            <input type="text" class="form-control" id="dias_semana" name="dias_semana"
                                placeholder="Ej: L-V, L-S">
                            <small class="text-muted">Indique los días de funcionamiento</small>
                        </div>
                        <div class="mb-3">
                            <label for="descripcion" class="form-label">Descripción</label>
                            <textarea class="form-control" id="descripcion" name="descripcion" rows="2"
                                placeholder="Descripción del turno (opcional)..."></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="orden" class="form-label">Orden de Visualización</label>
                                <input type="number" class="form-control" id="orden" name="orden" min="0"
                                    value="0">
                                <small class="text-muted">Orden en que aparecerá en las listas</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="estado" class="form-label">Estado</label>
                                <select class="form-select" id="estado" name="estado">
                                    <option value="1">Activo</option>
                                    <option value="0">Inactivo</option>
                                </select>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="saveNewTurno">
                        <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                        Guardar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Editar Turno -->
    <div class="modal fade" id="editTurnoModal" tabindex="-1" role="dialog" aria-labelledby="editTurnoModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editTurnoModalLabel">Editar Turno</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editTurnoForm">
                        <input type="hidden" id="edit_turno_id" name="id">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_codigo" class="form-label">Código</label>
                                <input type="text" class="form-control" id="edit_codigo" name="codigo" required
                                    placeholder="Ej: M, T, N" maxlength="20">
                                <small class="text-muted">Código único del turno</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_nombre" class="form-label">Nombre del Turno</label>
                                <input type="text" class="form-control" id="edit_nombre" name="nombre" required
                                    placeholder="Ej: Mañana, Tarde, Noche">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_hora_inicio" class="form-label">Hora de Inicio (General)</label>
                                <input type="time" class="form-control" id="edit_hora_inicio" name="hora_inicio"
                                    required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_hora_fin" class="form-label">Hora de Fin (General)</label>
                                <input type="time" class="form-control" id="edit_hora_fin" name="hora_fin" required>
                            </div>
                        </div>

                        <h5 class="text-uppercase bg-light p-2 mt-0 mb-3">Configuración de Asistencia</h5>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_hora_entrada_inicio" class="form-label text-success">Entrada Inicio</label>
                                <input type="time" class="form-control" id="edit_hora_entrada_inicio" name="hora_entrada_inicio">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_hora_entrada_fin" class="form-label text-success">Entrada Fin</label>
                                <input type="time" class="form-control" id="edit_hora_entrada_fin" name="hora_entrada_fin">
                                <small class="text-muted d-block mt-1">Límite para entrada normal</small>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_hora_tarde_inicio" class="form-label text-warning">Tarde Inicio</label>
                                <input type="time" class="form-control" id="edit_hora_tarde_inicio" name="hora_tarde_inicio">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_hora_tarde_fin" class="form-label text-warning">Tarde Fin</label>
                                <input type="time" class="form-control" id="edit_hora_tarde_fin" name="hora_tarde_fin">
                                <small class="text-muted d-block mt-1">Límite para considerar tardanza</small>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_hora_salida_inicio" class="form-label text-danger">Salida Inicio</label>
                                <input type="time" class="form-control" id="edit_hora_salida_inicio" name="hora_salida_inicio">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_hora_salida_fin" class="form-label text-danger">Salida Fin</label>
                                <input type="time" class="form-control" id="edit_hora_salida_fin" name="hora_salida_fin">
                                <small class="text-muted d-block mt-1">Límite para marcar salida</small>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="edit_dias_semana" class="form-label">Días de la Semana</label>
                            <input type="text" class="form-control" id="edit_dias_semana" name="dias_semana"
                                placeholder="Ej: L-V, L-S">
                            <small class="text-muted">Indique los días de funcionamiento</small>
                        </div>
                        <div class="mb-3">
                            <label for="edit_descripcion" class="form-label">Descripción</label>
                            <textarea class="form-control" id="edit_descripcion" name="descripcion" rows="2"
                                placeholder="Descripción del turno (opcional)..."></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_orden" class="form-label">Orden de Visualización</label>
                                <input type="number" class="form-control" id="edit_orden" name="orden"
                                    min="0">
                                <small class="text-muted">Orden en que aparecerá en las listas</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_estado" class="form-label">Estado</label>
                                <select class="form-select" id="edit_estado" name="estado">
                                    <option value="1">Activo</option>
                                    <option value="0">Inactivo</option>
                                </select>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="updateTurno">
                        <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                        Actualizar
                    </button>
                </div>
            </div>
        </div>
    </div>
@endpush
