@extends('layouts.app')

@section('title', 'Gestión de Parentescos')

@push('css')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css"
        rel="stylesheet" />
@endpush

@section('content')
    <!-- Start Content-->
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Parentescos</li>
                        </ol>
                    </div>
                    <h4 class="page-title">Gestión de Parentescos</h4>
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
                                <h4 class="card-title">Lista de Parentescos</h4>
                            </div>
                            <div class="col-sm-8">
                                <div class="text-sm-end">
                                    @if (Auth::user()->hasPermission('parentescos.create'))
                                        <button type="button" class="btn btn-primary mb-2" data-bs-toggle="modal"
                                            data-bs-target="#newParentescoModal">
                                            <i class="mdi mdi-plus-circle me-1"></i> Nuevo Parentesco
                                        </button>
                                    @endif
                                </div>
                            </div><!-- end col-->
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

                        <div class="table-responsive">
                            <table class="table table-centered table-nowrap table-striped" id="parentescos-datatable">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Estudiante</th>
                                        <th>Padre/Madre/Tutor</th>
                                        <th>Tipo</th>
                                        <th>Acceso Portal</th>
                                        <th>Recibe Notificaciones</th>
                                        <th>Contacto Emergencia</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Cargado dinámicamente vía AJAX -->
                                </tbody>
                            </table>
                        </div>
                    </div> <!-- end card-body-->
                </div> <!-- end card-->
            </div> <!-- end col -->
        </div>
        <!-- end row -->
    </div> <!-- container -->
@endsection

@push('modals')
    <!-- Modal para Nuevo Parentesco -->
    <div class="modal fade" id="newParentescoModal" tabindex="-1" role="dialog" aria-labelledby="newParentescoModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="newParentescoModalLabel">Nuevo Parentesco</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="newParentescoForm">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="estudiante_id" class="form-label">Estudiante</label>
                                <select class="form-select select2" id="estudiante_id" name="estudiante_id" required>
                                    <option value="">Seleccione un estudiante...</option>
                                    <!-- Los estudiantes se cargarán dinámicamente -->
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="padre_id" class="form-label">Padre/Madre/Tutor</label>
                                <select class="form-select select2" id="padre_id" name="padre_id" required>
                                    <option value="">Seleccione un padre/madre/tutor...</option>
                                    <!-- Los padres se cargarán dinámicamente -->
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="tipo_parentesco" class="form-label">Tipo de Parentesco</label>
                                <select class="form-select" id="tipo_parentesco" name="tipo_parentesco" required>
                                    <option value="">Seleccione...</option>
                                    <option value="padre">Padre</option>
                                    <option value="madre">Madre</option>
                                    <option value="tutor">Tutor Legal</option>
                                    <option value="abuelo">Abuelo/a</option>
                                    <option value="tio">Tío/a</option>
                                    <option value="hermano">Hermano/a</option>
                                    <option value="otro">Otro</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="acceso_portal" name="acceso_portal">
                                    <label class="form-check-label" for="acceso_portal">Acceso al Portal</label>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="recibe_notificaciones"
                                        name="recibe_notificaciones">
                                    <label class="form-check-label" for="recibe_notificaciones">Recibe
                                        Notificaciones</label>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="contacto_emergencia"
                                        name="contacto_emergencia">
                                    <label class="form-check-label" for="contacto_emergencia">Contacto de
                                        Emergencia</label>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="saveNewParentesco">Guardar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Editar Parentesco -->
    <div class="modal fade" id="editParentescoModal" tabindex="-1" role="dialog"
        aria-labelledby="editParentescoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editParentescoModalLabel">Editar Parentesco</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editParentescoForm">
                        <input type="hidden" id="edit_parentesco_id" name="id">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_estudiante_id" class="form-label">Estudiante</label>
                                <select class="form-select select2" id="edit_estudiante_id" name="estudiante_id"
                                    required>
                                    <option value="">Seleccione un estudiante...</option>
                                    <!-- Los estudiantes se cargarán dinámicamente -->
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_padre_id" class="form-label">Padre/Madre/Tutor</label>
                                <select class="form-select select2" id="edit_padre_id" name="padre_id" required>
                                    <option value="">Seleccione un padre/madre/tutor...</option>
                                    <!-- Los padres se cargarán dinámicamente -->
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_tipo_parentesco" class="form-label">Tipo de Parentesco</label>
                                <select class="form-select" id="edit_tipo_parentesco" name="tipo_parentesco" required>
                                    <option value="">Seleccione...</option>
                                    <option value="padre">Padre</option>
                                    <option value="madre">Madre</option>
                                    <option value="tutor">Tutor Legal</option>
                                    <option value="abuelo">Abuelo/a</option>
                                    <option value="tio">Tío/a</option>
                                    <option value="hermano">Hermano/a</option>
                                    <option value="otro">Otro</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_estado" class="form-label">Estado</label>
                                <select class="form-select" id="edit_estado" name="estado">
                                    <option value="1">Activo</option>
                                    <option value="0">Inactivo</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="edit_acceso_portal"
                                        name="acceso_portal">
                                    <label class="form-check-label" for="edit_acceso_portal">Acceso al Portal</label>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="edit_recibe_notificaciones"
                                        name="recibe_notificaciones">
                                    <label class="form-check-label" for="edit_recibe_notificaciones">Recibe
                                        Notificaciones</label>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="edit_contacto_emergencia"
                                        name="contacto_emergencia">
                                    <label class="form-check-label" for="edit_contacto_emergencia">Contacto de
                                        Emergencia</label>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="updateParentesco">Actualizar</button>
                </div>
            </div>
        </div>
    </div>
@endpush

@push('js')
    <script>
        // Define la URL base para las solicitudes AJAX
        window.default_server = "{{ url('/') }}";
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="{{ asset('js/parentescos/index.js') }}"></script>
@endpush
