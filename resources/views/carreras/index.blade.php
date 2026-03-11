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
    @vite('resources/js/carreras/index.js')
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
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="newCarreraModalLabel">Nueva Carrera</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="newCarreraForm" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="codigo" class="form-label">Código</label>
                                <input type="text" class="form-control" id="codigo" name="codigo" required
                                    placeholder="Ej: ING-SIS" maxlength="20">
                                <small class="text-muted">Código único de la carrera</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="nombre" class="form-label">Nombre de la Carrera</label>
                                <input type="text" class="form-control" id="nombre" name="nombre" required
                                    placeholder="Ej: Ingeniería de Sistemas">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="grupo" class="form-label">Grupo <span class="text-danger">*</span></label>
                                <select class="form-select" id="grupo" name="grupo" required>
                                    <option value="">Seleccione un grupo</option>
                                    <option value="A">Grupo A - Ingenierías</option>
                                    <option value="B">Grupo B - Ciencias de la Salud</option>
                                    <option value="C">Grupo C - Ciencias Sociales y Educación</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="estado" class="form-label">Estado</label>
                                <select class="form-select" id="estado" name="estado">
                                    <option value="1">Activo</option>
                                    <option value="0">Inactivo</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="grado" class="form-label">Grado Académico</label>
                                <input type="text" class="form-control" id="grado" name="grado" placeholder="Ej: Bachiller en...">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="titulo" class="form-label">Título Profesional</label>
                                <input type="text" class="form-control" id="titulo" name="titulo" placeholder="Ej: Licenciado en...">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="duracion" class="form-label">Duración</label>
                                <input type="text" class="form-control" id="duracion" name="duracion" placeholder="Ej: 10 Semestres">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="descripcion" class="form-label">Descripción General</label>
                            <textarea class="form-control" id="descripcion" name="descripcion" rows="2"
                                placeholder="Breve descripción..."></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_mision" class="form-label">Misión</label>
                                <textarea class="form-control" id="edit_mision" name="mision" rows="3"></textarea>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_vision" class="form-label">Visión</label>
                                <textarea class="form-control" id="edit_vision" name="vision" rows="3"></textarea>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="edit_objetivos" class="form-label">Objetivos (Un objetivo por línea)</label>
                            <textarea class="form-control" id="edit_objetivos" name="objetivos" rows="3" placeholder="Ingresa los objetivos separados por saltos de línea..."></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="campo_laboral" class="form-label">Campo Laboral (Una ocupación por línea)</label>
                            <textarea class="form-control" id="campo_laboral" name="campo_laboral" rows="3" placeholder="Ingresa el campo laboral separado por saltos de línea..."></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="edit_perfil" class="form-label">Perfil del Egresado</label>
                            <textarea class="form-control" id="edit_perfil" name="perfil" rows="3"></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="malla_file" class="form-label">Malla Curricular (PDF)</label>
                                <input type="file" class="form-control" id="malla_file" name="malla_file" accept=".pdf">
                                <small class="text-muted">Si se sube, reemplazará la URL actual.</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="imagen_file" class="form-label">Imagen/Logo</label>
                                <input type="file" class="form-control" id="imagen_file" name="imagen_file" accept="image/*">
                                <small class="text-muted">Si se sube, reemplazará la imagen actual.</small>
                            </div>
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
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editCarreraModalLabel">Editar Carrera</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editCarreraForm" enctype="multipart/form-data">
                        <input type="hidden" id="edit_carrera_id" name="id">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_codigo" class="form-label">Código</label>
                                <input type="text" class="form-control" id="edit_codigo" name="codigo" required
                                    placeholder="Ej: ING-SIS" maxlength="20">
                                <small class="text-muted">Código único de la carrera</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_nombre" class="form-label">Nombre de la Carrera</label>
                                <input type="text" class="form-control" id="edit_nombre" name="nombre" required
                                    placeholder="Ej: Ingeniería de Sistemas">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_grupo" class="form-label">Grupo <span class="text-danger">*</span></label>
                                <select class="form-select" id="edit_grupo" name="grupo" required>
                                    <option value="">Seleccione un grupo</option>
                                    <option value="A">Grupo A - Ingenierías</option>
                                    <option value="B">Grupo B - Ciencias de la Salud</option>
                                    <option value="C">Grupo C - Ciencias Sociales y Educación</option>
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
                                <label for="edit_grado" class="form-label">Grado Académico</label>
                                <input type="text" class="form-control" id="edit_grado" name="grado">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="edit_titulo" class="form-label">Título Profesional</label>
                                <input type="text" class="form-control" id="edit_titulo" name="titulo">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="edit_duracion" class="form-label">Duración</label>
                                <input type="text" class="form-control" id="edit_duracion" name="duracion">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="edit_descripcion" class="form-label">Descripción</label>
                            <textarea class="form-control" id="edit_descripcion" name="descripcion" rows="2"></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_mision" class="form-label">Misión</label>
                                <textarea class="form-control" id="edit_mision" name="mision" rows="3"></textarea>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_vision" class="form-label">Visión</label>
                                <textarea class="form-control" id="edit_vision" name="vision" rows="3"></textarea>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="edit_objetivos" class="form-label">Objetivos (Un objetivo por línea)</label>
                            <textarea class="form-control" id="edit_objetivos" name="objetivos" rows="3"></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="edit_campo_laboral" class="form-label">Campo Laboral (Una ocupación por línea)</label>
                            <textarea class="form-control" id="edit_campo_laboral" name="campo_laboral" rows="3" placeholder="Ingresa las ocupaciones separadas por saltos de línea..."></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="edit_perfil" class="form-label">Perfil del Egresado</label>
                            <textarea class="form-control" id="edit_perfil" name="perfil" rows="3"></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_malla_file" class="form-label">Nueva Malla Curricular (PDF)</label>
                                <input type="file" class="form-control" id="edit_malla_file" name="malla_file" accept=".pdf">
                                <small class="text-muted" id="current_malla_url_display"></small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_imagen_file" class="form-label">Nueva Imagen/Logo</label>
                                <input type="file" class="form-control" id="edit_imagen_file" name="imagen_file" accept="image/*">
                                <small class="text-muted" id="current_imagen_url_display"></small>
                            </div>
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
