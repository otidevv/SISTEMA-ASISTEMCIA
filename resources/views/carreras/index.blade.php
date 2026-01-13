{{-- resources/views/carreras/index.blade.php --}}

@extends('layouts.app')

@section('title', 'Gestión de Carreras')

@push('css')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">
@endpush

@push('js')
    <script>
        window.default_server = "{{ url('/') }}";
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="{{ asset('js/carreras/index.js') }}"></script>
@endpush

@section('content')
    <!-- start page title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">Gestión de Carreras</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Carreras</li>
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
                            <h4 class="header-title mt-0 mb-1">Lista de Carreras</h4>
                        </div>
                        <div class="col-sm-8">
                            <div class="text-sm-end">
                                @if (Auth::user()->hasPermission('carreras.create'))
                                    <button type="button" class="btn btn-primary mb-2" data-bs-toggle="modal"
                                        data-bs-target="#newCarreraModal">
                                        <i class="mdi mdi-plus-circle me-1"></i> Nueva Carrera
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

                    <table id="carreras-datatable" class="table dt-responsive nowrap w-100">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Código</th>
                                <th>Nombre</th>
                                <th>Grupo</th>
                                <th>Descripción</th>
                                <th>Estudiantes</th>
                                <th>Estado</th>
                                <th>Fecha Creación</th>
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
    <!-- Modal para Nueva Carrera -->
    <div class="modal fade" id="newCarreraModal" tabindex="-1" role="dialog" aria-labelledby="newCarreraModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="newCarreraModalLabel">Nueva Carrera</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="newCarreraForm">
                        <div class="mb-3">
                            <label for="codigo" class="form-label">Código</label>
                            <input type="text" class="form-control" id="codigo" name="codigo" required
                                placeholder="Ej: ING-SIS" maxlength="20">
                            <small class="text-muted">Código único de la carrera (máximo 20 caracteres)</small>
                        </div>
                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre de la Carrera</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" required
                                placeholder="Ej: Ingeniería de Sistemas">
                        </div>
                        <div class="mb-3">
                            <label for="grupo" class="form-label">Grupo <span class="text-danger">*</span></label>
                            <select class="form-select" id="grupo" name="grupo" required>
                                <option value="">Seleccione un grupo</option>
                                <option value="A">Grupo A - Ingenierías</option>
                                <option value="B">Grupo B - Ciencias de la Salud</option>
                                <option value="C">Grupo C - Ciencias Sociales y Educación</option>
                            </select>
                            <small class="text-muted">El grupo determina a qué aula se asignará el estudiante</small>
                        </div>
                        <div class="mb-3">
                            <label for="descripcion" class="form-label">Descripción</label>
                            <textarea class="form-control" id="descripcion" name="descripcion" rows="3"
                                placeholder="Descripción de la carrera (opcional)..."></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="estado" class="form-label">Estado</label>
                            <select class="form-select" id="estado" name="estado">
                                <option value="1">Activo</option>
                                <option value="0">Inactivo</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="saveNewCarrera">
                        <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                        Guardar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Editar Carrera -->
    <div class="modal fade" id="editCarreraModal" tabindex="-1" role="dialog" aria-labelledby="editCarreraModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editCarreraModalLabel">Editar Carrera</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editCarreraForm">
                        <input type="hidden" id="edit_carrera_id" name="id">
                        <div class="mb-3">
                            <label for="edit_codigo" class="form-label">Código</label>
                            <input type="text" class="form-control" id="edit_codigo" name="codigo" required
                                placeholder="Ej: ING-SIS" maxlength="20">
                            <small class="text-muted">Código único de la carrera (máximo 20 caracteres)</small>
                        </div>
                        <div class="mb-3">
                            <label for="edit_nombre" class="form-label">Nombre de la Carrera</label>
                            <input type="text" class="form-control" id="edit_nombre" name="nombre" required
                                placeholder="Ej: Ingeniería de Sistemas">
                        </div>
                        <div class="mb-3">
                            <label for="edit_grupo" class="form-label">Grupo <span class="text-danger">*</span></label>
                            <select class="form-select" id="edit_grupo" name="grupo" required>
                                <option value="">Seleccione un grupo</option>
                                <option value="A">Grupo A - Ingenierías</option>
                                <option value="B">Grupo B - Ciencias de la Salud</option>
                                <option value="C">Grupo C - Ciencias Sociales y Educación</option>
                            </select>
                            <small class="text-muted">El grupo determina a qué aula se asignará el estudiante</small>
                        </div>
                        <div class="mb-3">
                            <label for="edit_descripcion" class="form-label">Descripción</label>
                            <textarea class="form-control" id="edit_descripcion" name="descripcion" rows="3"
                                placeholder="Descripción de la carrera (opcional)..."></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="edit_estado" class="form-label">Estado</label>
                            <select class="form-select" id="edit_estado" name="estado">
                                <option value="1">Activo</option>
                                <option value="0">Inactivo</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="updateCarrera">
                        <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                        Actualizar
                    </button>
                </div>
            </div>
        </div>
    </div>
@endpush
