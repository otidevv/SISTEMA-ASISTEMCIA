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
    <script src="{{ asset('js/ciclos/index.js') }}"></script>
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
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
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
                    <button type="button" class="btn btn-primary" id="updateCiclo">Actualizar</button>
                </div>
            </div>
        </div>
    </div>
@endpush
