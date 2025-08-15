@extends('layouts.app')

@section('title', 'Gestión de Postulaciones')

@push('css')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css"
        rel="stylesheet" />
    <style>
        .badge-estado-pendiente { background-color: #ffc107; color: #000; }
        .badge-estado-aprobado { background-color: #28a745; color: #fff; }
        .badge-estado-rechazado { background-color: #dc3545; color: #fff; }
        .badge-estado-observado { background-color: #17a2b8; color: #fff; }
        .document-list { list-style: none; padding: 0; }
        .document-list li { padding: 5px 0; }
        .document-list .text-success { color: #28a745; }
        .document-list .text-danger { color: #dc3545; }
        .voucher-details {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-top: 10px;
        }
        .edit-documents {
            transition: all 0.3s ease;
        }
        .edit-documents:hover {
            transform: scale(1.05);
        }
        #editDocumentsModal .card {
            border: 1px solid #dee2e6;
            transition: box-shadow 0.3s ease;
        }
        #editDocumentsModal .card:hover {
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        #editDocumentsModal .doc-file-input {
            margin-top: 10px;
        }
        #documents-container .card-title {
            color: #495057;
            font-weight: 600;
            margin-bottom: 15px;
        }
    </style>
@endpush

@push('js')
    <script>
        window.default_server = "{{ url('/') }}";
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="{{ asset('js/postulaciones/index.js') }}"></script>
@endpush

@section('content')
    <!-- start page title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">Gestión de Postulaciones</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Postulaciones</li>
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
                    <!-- Filtros -->
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label for="filter-ciclo">Ciclo:</label>
                            <select id="filter-ciclo" class="form-select">
                                <option value="">Todos los ciclos</option>
                                @foreach($ciclos as $ciclo)
                                    <option value="{{ $ciclo->id }}" {{ $ciclo->es_activo ? 'selected' : '' }}>
                                        {{ $ciclo->nombre }} {{ $ciclo->es_activo ? '(Activo)' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="filter-estado">Estado:</label>
                            <select id="filter-estado" class="form-select">
                                <option value="">Todos</option>
                                <option value="pendiente">Pendiente</option>
                                <option value="aprobado">Aprobado</option>
                                <option value="rechazado">Rechazado</option>
                                <option value="observado">Observado</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="filter-carrera">Carrera:</label>
                            <select id="filter-carrera" class="form-select">
                                <option value="">Todas las carreras</option>
                                @foreach($carreras as $carrera)
                                    <option value="{{ $carrera->id }}">{{ $carrera->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label>&nbsp;</label>
                            <button type="button" class="btn btn-primary d-block w-100" id="btn-filtrar">
                                <i class="mdi mdi-filter me-1"></i> Filtrar
                            </button>
                        </div>
                    </div>

                    <!-- Estadísticas rápidas -->
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body p-3">
                                    <h5 class="mb-1">Pendientes</h5>
                                    <h3 id="stat-pendientes">0</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-success text-white">
                                <div class="card-body p-3">
                                    <h5 class="mb-1">Aprobadas</h5>
                                    <h3 id="stat-aprobadas">0</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-danger text-white">
                                <div class="card-body p-3">
                                    <h5 class="mb-1">Rechazadas</h5>
                                    <h3 id="stat-rechazadas">0</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body p-3">
                                    <h5 class="mb-1">Observadas</h5>
                                    <h3 id="stat-observadas">0</h3>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabla de postulaciones -->
                    <div class="row">
                        <div class="col-12">
                            <h4 class="header-title mt-0 mb-3">Lista de Postulaciones</h4>
                            
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

                            <table id="postulaciones-datatable" class="table dt-responsive nowrap w-100">
                                <thead>
                                    <tr>
                                        <th>Código</th>
                                        <th>Estudiante</th>
                                        <th>DNI</th>
                                        <th>Carrera</th>
                                        <th>Turno</th>
                                        <th>Tipo</th>
                                        <th>Fecha</th>
                                        <th>Estado</th>
                                        <th>Verificación</th>
                                        <th>Constancia</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Los datos se cargarán vía AJAX -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('modals')
    <!-- Modal para Ver Detalle -->
    <div class="modal fade" id="viewModal" tabindex="-1" role="dialog" aria-labelledby="viewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewModalLabel">Detalle de Postulación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="viewModalBody">
                    <!-- El contenido se cargará dinámicamente -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Rechazar -->
    <div class="modal fade" id="rejectModal" tabindex="-1" role="dialog" aria-labelledby="rejectModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="rejectModalLabel">Rechazar Postulación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="rejectForm">
                        <input type="hidden" id="reject-id" name="id">
                        <div class="mb-3">
                            <label for="reject-motivo" class="form-label">Motivo del Rechazo <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="reject-motivo" name="motivo" rows="4" required 
                                placeholder="Ingrese el motivo del rechazo (mínimo 10 caracteres)"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger" id="confirmReject">
                        <i class="mdi mdi-close-circle me-1"></i> Rechazar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Observar -->
    <div class="modal fade" id="observeModal" tabindex="-1" role="dialog" aria-labelledby="observeModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title" id="observeModalLabel">Observar Postulación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="observeForm">
                        <input type="hidden" id="observe-id" name="id">
                        <div class="mb-3">
                            <label for="observe-observaciones" class="form-label">Observaciones <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="observe-observaciones" name="observaciones" rows="4" required 
                                placeholder="Ingrese las observaciones (mínimo 10 caracteres)"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-warning" id="confirmObserve">
                        <i class="mdi mdi-comment-alert me-1"></i> Marcar con Observaciones
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Confirmación para Eliminar -->
    <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deleteModalLabel">Confirmar Eliminación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>¿Está seguro de que desea eliminar esta postulación?</p>
                    <p class="text-danger">Esta acción no se puede deshacer y eliminará todos los documentos asociados.</p>
                    <input type="hidden" id="delete-id">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger" id="confirmDelete">
                        <i class="mdi mdi-delete me-1"></i> Eliminar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Editar Documentos -->
    <div class="modal fade" id="editDocumentsModal" tabindex="-1" role="dialog" aria-labelledby="editDocumentsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="editDocumentsModalLabel">Editar Documentos del Postulante</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editDocumentsForm" enctype="multipart/form-data">
                        <input type="hidden" id="edit-docs-postulacion-id">
                        
                        <div class="alert alert-info">
                            <i class="uil uil-info-circle"></i> 
                            Puede reemplazar los documentos subidos por el postulante. Solo suba los documentos que desea cambiar.
                        </div>

                        <div class="row" id="documents-container">
                            <!-- Los documentos se cargarán dinámicamente aquí -->
                        </div>

                        <div class="mt-3">
                            <div class="form-group">
                                <label for="edit-docs-observacion">Observación del cambio:</label>
                                <textarea class="form-control" id="edit-docs-observacion" rows="3" 
                                    placeholder="Explique brevemente por qué se están modificando los documentos"></textarea>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="saveDocumentChanges">
                        <i class="uil uil-save me-1"></i> Guardar Cambios
                    </button>
                </div>
            </div>
        </div>
    </div>
@endpush