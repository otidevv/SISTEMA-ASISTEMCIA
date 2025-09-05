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
    
    <script>
        // Función para abrir el modal de nueva postulación unificada
        document.addEventListener('DOMContentLoaded', function() {
            const btnNuevaPostulacion = document.getElementById('btn-nueva-postulacion-unificada');
            const modalNuevaPostulacion = document.getElementById('nuevaPostulacionModal');
            const iframe = document.getElementById('postulacion-iframe');
            
            if (btnNuevaPostulacion) {
                btnNuevaPostulacion.addEventListener('click', function() {
                    // Mostrar el modal
                    const modal = new bootstrap.Modal(modalNuevaPostulacion);
                    modal.show();
                    
                    // Cargar el formulario vía AJAX
                    loadPostulacionForm();
                });
            }
            
            // Función para cargar el formulario vía AJAX
            function loadPostulacionForm() {
                const container = document.getElementById('postulacion-form-container');
                
                fetch("{{ route('postulacion-unificada.form-content') }}")
                    .then(response => response.text())
                    .then(html => {
                        // Insertar directamente el contenido HTML
                        container.innerHTML = html;
                        
                        // Cargar el script externo después de insertar el HTML
                        const script = document.createElement('script');
                        script.src = "{{ asset('js/postulaciones/unificado.js') }}";
                        script.onload = function() {
                            console.log('Script unificado.js cargado correctamente');
                            
                            // Verificar que jQuery esté disponible
                            if (typeof $ === 'undefined') {
                                console.error('jQuery no está disponible');
                                return;
                            }
                            
                            // Ejecutar la inicialización del script externo
                            setTimeout(() => {
                                // Simular el evento ready de jQuery para el script externo
                                $(document).ready();
                                console.log('Wizard inicializado después de carga AJAX');
                            }, 100);
                        };
                        script.onerror = function() {
                            console.error('Error al cargar unificado.js');
                        };
                        document.head.appendChild(script);
                        
                        if (typeof toastr !== 'undefined') {
                            toastr.success('Formulario cargado correctamente', 'Listo');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        container.innerHTML = '<div class="alert alert-danger">Error al cargar el formulario</div>';
                    });
            }
            
            // Escuchar mensajes del iframe para cerrar el modal cuando se complete la postulación
            window.addEventListener('message', function(event) {
                if (event.data && event.data.type === 'postulacion-completada') {
                    // Cerrar el modal
                    const modal = bootstrap.Modal.getInstance(modalNuevaPostulacion);
                    if (modal) {
                        modal.hide();
                    }
                    
                    // Mostrar mensaje de éxito
                    toastr.success('Postulación creada exitosamente', 'Éxito');
                    
                    // Actualizar la lista de postulaciones
                    if (typeof refreshPostulacionesList === 'function') {
                        refreshPostulacionesList();
                    } else {
                        // Recargar la página si no existe la función
                        setTimeout(() => {
                            window.location.reload();
                        }, 2000);
                    }
                }
            });
        });
        
        // Función para actualizar la lista de postulaciones
        function refreshPostulacionesList() {
            if (typeof window.postulacionesDataTable !== 'undefined' && window.postulacionesDataTable) {
                window.postulacionesDataTable.ajax.reload(null, false);
                toastr.info('Lista de postulaciones actualizada', 'Actualizado');
            } else {
                // Si no hay DataTable, recargar la página
                window.location.reload();
            }
        }
    </script>
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

                    <!-- Botón Nueva Postulación Unificada -->
                    @if (Auth::user()->hasPermission('postulaciones.create-unified'))
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center">
                                <h4 class="header-title mt-0 mb-0">Lista de Postulaciones</h4>
                                <button type="button" class="btn btn-success btn-lg" id="btn-nueva-postulacion-unificada">
                                    <i class="mdi mdi-account-plus me-2"></i>
                                    Nueva Postulación Completa
                                </button>
                            </div>
                        </div>
                    </div>
                    @else
                    <div class="row mb-3">
                        <div class="col-12">
                            <h4 class="header-title mt-0 mb-3">Lista de Postulaciones</h4>
                        </div>
                    </div>
                    @endif

                    <!-- Tabla de postulaciones -->
                    <div class="row">
                        <div class="col-12">
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

    <!-- Modal para Editar Postulación Aprobada -->
    <div class="modal fade" id="editApprovedModal" tabindex="-1" role="dialog" aria-labelledby="editApprovedModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="editApprovedModalLabel">Editar Postulación Aprobada</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editApprovedForm">
                        <input type="hidden" id="edit-approved-id" name="id">
                        
                        <div class="alert alert-warning">
                            <i class="uil uil-exclamation-triangle"></i> 
                            <strong>Atención:</strong> Esta postulación ya ha sido aprobada. Los cambios que realice también actualizarán la inscripción asociada.
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-muted mb-3">Datos del Estudiante</h6>
                                <div class="mb-3">
                                    <label for="edit-approved-dni" class="form-label">DNI</label>
                                    <input type="text" class="form-control" id="edit-approved-dni" name="dni" maxlength="8" readonly>
                                    <small class="text-muted">El DNI no puede ser modificado</small>
                                </div>
                                <div class="mb-3">
                                    <label for="edit-approved-nombre" class="form-label">Nombres</label>
                                    <input type="text" class="form-control" id="edit-approved-nombre" name="nombre" required>
                                </div>
                                <div class="mb-3">
                                    <label for="edit-approved-apellido-paterno" class="form-label">Apellido Paterno</label>
                                    <input type="text" class="form-control" id="edit-approved-apellido-paterno" name="apellido_paterno" required>
                                </div>
                                <div class="mb-3">
                                    <label for="edit-approved-apellido-materno" class="form-label">Apellido Materno</label>
                                    <input type="text" class="form-control" id="edit-approved-apellido-materno" name="apellido_materno" required>
                                </div>
                                <div class="mb-3">
                                    <label for="edit-approved-telefono" class="form-label">Teléfono</label>
                                    <input type="text" class="form-control" id="edit-approved-telefono" name="telefono">
                                </div>
                                <div class="mb-3">
                                    <label for="edit-approved-email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="edit-approved-email" name="email" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <h6 class="text-muted mb-3">Datos Académicos</h6>
                                <div class="mb-3">
                                    <label for="edit-approved-ciclo" class="form-label">Ciclo</label>
                                    <select class="form-select" id="edit-approved-ciclo" name="ciclo_id" required>
                                        @foreach($ciclos as $ciclo)
                                            <option value="{{ $ciclo->id }}">{{ $ciclo->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="edit-approved-carrera" class="form-label">Carrera</label>
                                    <select class="form-select" id="edit-approved-carrera" name="carrera_id" required>
                                        @foreach($carreras as $carrera)
                                            <option value="{{ $carrera->id }}">{{ $carrera->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="edit-approved-turno" class="form-label">Turno</label>
                                    <select class="form-select" id="edit-approved-turno" name="turno_id" required>
                                        <!-- Los turnos se cargarán dinámicamente -->
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="edit-approved-aula" class="form-label">Aula Asignada</label>
                                    <select class="form-select" id="edit-approved-aula" name="aula_id">
                                        <!-- Las aulas se cargarán dinámicamente -->
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="edit-approved-tipo" class="form-label">Tipo de Inscripción</label>
                                    <select class="form-select" id="edit-approved-tipo" name="tipo_inscripcion" required>
                                        <option value="postulante">Postulante</option>
                                        <option value="reforzamiento">Reforzamiento</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <h6 class="text-muted mb-3">Información de Pago</h6>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="edit-approved-recibo" class="form-label">N° Recibo</label>
                                    <input type="text" class="form-control" id="edit-approved-recibo" name="numero_recibo">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="edit-approved-matricula" class="form-label">Monto Matrícula (S/.)</label>
                                    <input type="number" step="0.01" class="form-control" id="edit-approved-matricula" name="monto_matricula">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="edit-approved-ensenanza" class="form-label">Monto Enseñanza (S/.)</label>
                                    <input type="number" step="0.01" class="form-control" id="edit-approved-ensenanza" name="monto_ensenanza">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="edit-approved-observacion" class="form-label">Observación del cambio <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="edit-approved-observacion" name="observacion_cambio" rows="3" required
                                placeholder="Explique brevemente el motivo de la modificación"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="saveApprovedChanges">
                        <i class="uil uil-save me-1"></i> Guardar Cambios
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

    <!-- Modal para Nueva Postulación Unificada -->
    <div class="modal fade" id="nuevaPostulacionModal" tabindex="-1" role="dialog" aria-labelledby="nuevaPostulacionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="nuevaPostulacionModalLabel">
                        <i class="mdi mdi-account-plus me-2"></i>
                        Nueva Postulación Completa - Datos del Estudiante y Familia
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="max-height: 75vh; overflow-y: auto;">
                    <div id="postulacion-form-container">
                        <!-- El formulario se cargará aquí vía AJAX -->
                        <div class="text-center py-4">
                            <div class="spinner-border text-success" role="status">
                                <span class="visually-hidden">Cargando formulario...</span>
                            </div>
                            <p class="mt-2 text-muted">Cargando formulario de postulación...</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="mdi mdi-close me-1"></i> Cerrar
                    </button>
                    <button type="button" class="btn btn-primary" onclick="refreshPostulacionesList()">
                        <i class="mdi mdi-refresh me-1"></i> Actualizar Lista
                    </button>
                </div>
            </div>
        </div>
    </div>
@endpush