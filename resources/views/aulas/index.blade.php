{{-- resources/views/aulas/index.blade.php --}}

@extends('layouts.app')

@section('title', 'Gestión de Aulas')

@push('css')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">
@endpush

@push('js')
    <script>
        window.default_server = "{{ url('/') }}";
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="{{ asset('js/aulas/index.js') }}"></script>
@endpush

@section('content')
    <!-- start page title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">Gestión de Aulas</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Aulas</li>
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
                            <h4 class="header-title mt-0 mb-1">Lista de Aulas</h4>
                        </div>
                        <div class="col-sm-8">
                            <div class="text-sm-end">
                                @if (Auth::user()->hasPermission('aulas.create'))
                                    <button type="button" class="btn btn-primary mb-2" data-bs-toggle="modal"
                                        data-bs-target="#newAulaModal">
                                        <i class="mdi mdi-plus-circle me-1"></i> Nueva Aula
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>

                    <table id="aulas-datatable" class="table dt-responsive nowrap w-100">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Código</th>
                                <th>Nombre</th>
                                <th>Tipo</th>
                                <th>Capacidad</th>
                                <th>Ubicación</th>
                                <th>Características</th>
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
    <!-- Modal para Nueva Aula -->
    <div class="modal fade" id="newAulaModal" tabindex="-1" role="dialog" aria-labelledby="newAulaModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="newAulaModalLabel">Nueva Aula</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="newAulaForm">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="codigo" class="form-label">Código</label>
                                <input type="text" class="form-control" id="codigo" name="codigo" required
                                    placeholder="Ej: A-101, LAB-01" maxlength="20">
                                <small class="text-muted">Código único del aula</small>
                            </div>
                            <div class="col-md-8 mb-3">
                                <label for="nombre" class="form-label">Nombre del Aula</label>
                                <input type="text" class="form-control" id="nombre" name="nombre" required
                                    placeholder="Ej: Aula 101, Laboratorio de Cómputo 1">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="tipo" class="form-label">Tipo</label>
                                <select class="form-select" id="tipo" name="tipo" required>
                                    <option value="">Seleccione...</option>
                                    <option value="aula">Aula</option>
                                    <option value="laboratorio">Laboratorio</option>
                                    <option value="taller">Taller</option>
                                    <option value="auditorio">Auditorio</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="capacidad" class="form-label">Capacidad</label>
                                <input type="number" class="form-control" id="capacidad" name="capacidad" required
                                    min="1" max="1000" placeholder="Ej: 30">
                                <small class="text-muted">Número de personas</small>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="estado" class="form-label">Estado</label>
                                <select class="form-select" id="estado" name="estado">
                                    <option value="1">Activo</option>
                                    <option value="0">Inactivo</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edificio" class="form-label">Edificio</label>
                                <input type="text" class="form-control" id="edificio" name="edificio"
                                    placeholder="Ej: Edificio A, Pabellón Central">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="piso" class="form-label">Piso</label>
                                <input type="text" class="form-control" id="piso" name="piso"
                                    placeholder="Ej: 1, 2, Planta Baja">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="descripcion" class="form-label">Descripción</label>
                            <textarea class="form-control" id="descripcion" name="descripcion" rows="2"
                                placeholder="Descripción del aula (opcional)..."></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="equipamiento" class="form-label">Equipamiento</label>
                            <textarea class="form-control" id="equipamiento" name="equipamiento" rows="2"
                                placeholder="Describa el equipamiento disponible..."></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Características</label>
                                <div class="d-flex flex-wrap gap-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="tiene_proyector"
                                            name="tiene_proyector" value="1">
                                        <label class="form-check-label" for="tiene_proyector">
                                            <i class="uil uil-presentation"></i> Proyector
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="tiene_aire_acondicionado"
                                            name="tiene_aire_acondicionado" value="1">
                                        <label class="form-check-label" for="tiene_aire_acondicionado">
                                            <i class="uil uil-snowflake"></i> Aire Acondicionado
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="accesible" name="accesible"
                                            value="1" checked>
                                        <label class="form-check-label" for="accesible">
                                            <i class="uil uil-wheelchair"></i> Accesible
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="saveNewAula">
                        <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                        Guardar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Editar Aula -->
    <div class="modal fade" id="editAulaModal" tabindex="-1" role="dialog" aria-labelledby="editAulaModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editAulaModalLabel">Editar Aula</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editAulaForm">
                        <input type="hidden" id="edit_aula_id" name="id">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="edit_codigo" class="form-label">Código</label>
                                <input type="text" class="form-control" id="edit_codigo" name="codigo" required
                                    placeholder="Ej: A-101, LAB-01" maxlength="20">
                                <small class="text-muted">Código único del aula</small>
                            </div>
                            <div class="col-md-8 mb-3">
                                <label for="edit_nombre" class="form-label">Nombre del Aula</label>
                                <input type="text" class="form-control" id="edit_nombre" name="nombre" required
                                    placeholder="Ej: Aula 101, Laboratorio de Cómputo 1">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="edit_tipo" class="form-label">Tipo</label>
                                <select class="form-select" id="edit_tipo" name="tipo" required>
                                    <option value="">Seleccione...</option>
                                    <option value="aula">Aula</option>
                                    <option value="laboratorio">Laboratorio</option>
                                    <option value="taller">Taller</option>
                                    <option value="auditorio">Auditorio</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="edit_capacidad" class="form-label">Capacidad</label>
                                <input type="number" class="form-control" id="edit_capacidad" name="capacidad" required
                                    min="1" max="1000" placeholder="Ej: 30">
                                <small class="text-muted">Número de personas</small>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="edit_estado" class="form-label">Estado</label>
                                <select class="form-select" id="edit_estado" name="estado">
                                    <option value="1">Activo</option>
                                    <option value="0">Inactivo</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="edit_edificio" class="form-label">Edificio</label>
                                <input type="text" class="form-control" id="edit_edificio" name="edificio"
                                    placeholder="Ej: Edificio A, Pabellón Central">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="edit_piso" class="form-label">Piso</label>
                                <input type="text" class="form-control" id="edit_piso" name="piso"
                                    placeholder="Ej: 1, 2, Planta Baja">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="edit_descripcion" class="form-label">Descripción</label>
                            <textarea class="form-control" id="edit_descripcion" name="descripcion" rows="2"
                                placeholder="Descripción del aula (opcional)..."></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="edit_equipamiento" class="form-label">Equipamiento</label>
                            <textarea class="form-control" id="edit_equipamiento" name="equipamiento" rows="2"
                                placeholder="Describa el equipamiento disponible..."></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Características</label>
                                <div class="d-flex flex-wrap gap-3">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="edit_tiene_proyector"
                                            name="tiene_proyector" value="1">
                                        <label class="form-check-label" for="edit_tiene_proyector">
                                            <i class="uil uil-presentation"></i> Proyector
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input"
                                            id="edit_tiene_aire_acondicionado" name="tiene_aire_acondicionado"
                                            value="1">
                                        <label class="form-check-label" for="edit_tiene_aire_acondicionado">
                                            <i class="uil uil-snowflake"></i> Aire Acondicionado
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="edit_accesible"
                                            name="accesible" value="1">
                                        <label class="form-check-label" for="edit_accesible">
                                            <i class="uil uil-wheelchair"></i> Accesible
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="updateAula">
                        <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                        Actualizar
                    </button>
                </div>
            </div>
        </div>
    </div>
@endpush
