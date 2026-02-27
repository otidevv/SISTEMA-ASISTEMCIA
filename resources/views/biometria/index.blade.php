@extends('layouts.app')

@section('title', 'Gestión Biométrica')

@push('css')
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.14.5/dist/sweetalert2.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">
    <!-- Usar los de la plantilla para evitar conflictos -->
    
    <style>
        :root {
            --cepre-magenta: #e91e63;
            --cepre-navy: #1a237e;
        }
        .device-selector-card {
            border-left: 5px solid var(--cepre-pink);
            background-color: #f8f9fa;
        }
        .biometric-card {
            border-radius: 1rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            border: none;
            margin-bottom: 1.5rem;
        }
        .device-status-dot {
            height: 10px;
            width: 10px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 5px;
        }
        :root {
            --cepre-pink: #ec008c;
            --cepre-dark-blue: #2b5a6f;
            --cepre-cyan: #00bcd4;
        }

        .btn-group-bio {
            display: flex;
            width: 100%;
            border-radius: 0.5rem;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .btn-group-bio .btn {
            border-radius: 0 !important;
            border: none;
            flex-grow: 1;
        }
        .btn-group-bio .btn-dropdown {
            flex-grow: 0;
            width: 45px;
            background-color: #00acc1;
            border-left: 1px solid rgba(255,255,255,0.2) !important;
        }
        .btn-group-bio .btn-dropdown:hover {
            background-color: #0097a7;
        }
        .dropdown-toggle-nocaret::after {
            display: none !important;
        }

        /* Asegurar que el menú se posicione relativo al grupo de botones */
        .btn-group-bio {
            position: relative;
            display: flex;
            width: 100%;
            border-radius: 0.5rem;
            overflow: visible !important; /* Permitir que el dropdown flote fuera */
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .btn-group-bio .dropdown-menu {
            margin-top: 0 !important;
            z-index: 1060 !important;
        }

        /* Ocultar elementos duplicados inyectados por el script global del tema */
        #datatable-buttons_wrapper, 
        .dt-buttons, 
        #datatable-buttons_filter {
            display: none !important;
            visibility: hidden !important;
            height: 0 !important;
            overflow: hidden !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        .dot-online { background-color: #28a745; box-shadow: 0 0 5px #28a745; }
        .dot-offline { background-color: #dc3545; }
        
        .biometric-card {
            border-top: 3px solid var(--cepre-pink);
        }

        .enroll-btn {
            transition: all 0.2s ease;
            font-weight: 600;
            padding: 0.4rem 0.8rem;
        }
        .enroll-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 10px rgba(0,0,0,0.15);
        }
        
        .status-badge {
            font-size: 0.8rem;
            padding: 0.5em 1em;
            font-weight: 600;
        }

        /* Colores Vivos Institucionales */
        .bg-cepre-pink { background-color: var(--cepre-pink) !important; color: white !important; }
        .btn-cepre-pink { background-color: var(--cepre-pink); border-color: var(--cepre-pink); color: white; }
        .btn-cepre-pink:hover { background-color: #d6007e; border-color: #d6007e; color: white; }
        
        .bg-cepre-teal { background-color: #00acc1 !important; color: white !important; }
        .btn-cepre-teal { background-color: #00acc1; border-color: #00acc1; color: white; }
        .btn-cepre-teal:hover { background-color: #0097a7; border-color: #0097a7; color: white; }
        .text-cepre-teal { color: #00acc1 !important; }

        /* Variantes Soft para botones (Estilo Moderno) */
        .btn-soft-info {
            background-color: rgba(0, 172, 193, 0.1);
            color: #00acc1;
            border: none;
        }
        .btn-soft-info:hover {
            background-color: #00acc1;
            color: white;
        }
        .btn-soft-danger {
            background-color: rgba(220, 53, 69, 0.1);
            color: #dc3545;
            border: none;
        }
        .btn-soft-danger:hover {
            background-color: #dc3545;
            color: white;
        }
        .btn-soft-primary {
            background-color: rgba(85, 110, 230, 0.1);
            color: #556ee6;
            border: none;
        }
        .btn-soft-primary:hover {
            background-color: #556ee6;
            color: white;
        }
        
        .badge-pending {
            background-color: #ffc107;
            color: #000;
            border: 1px solid #d39e00;
        }
        .badge-registered {
            background-color: #28a745;
            color: #fff;
            border: 1px solid #1e7e34;
        }
        
        .enroll-btn i {
            font-size: 1rem;
            vertical-align: middle;
        }
        
        .btn-text {
            font-size: 0.85rem;
            margin-left: 5px;
            vertical-align: middle;
        }
    </style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="page-title">Módulo de Gestión Biométrica</h4>
                <div class="page-title-right d-flex gap-2">
                    <button type="button" class="btn btn-cepre-teal shadow-sm d-flex align-items-center" id="btn-open-migrate">
                        <i class="mdi mdi-swap-horizontal me-2 fs-5"></i> Migrar entre Equipos
                    </button>
                    <button type="button" class="btn btn-cepre-pink shadow-sm d-flex align-items-center" id="btn-refresh-table">
                        <i class="mdi mdi-refresh me-2 fs-5"></i> Actualizar Listado
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Panel de Dispositivos -->
    <div class="row">
        @forelse($devices as $device)
        @php 
            $isOnline = $device->last_seen && $device->last_seen->diffInMinutes(now()) < 5;
        @endphp
        <div class="col-md-4">
            <div class="card biometric-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title mb-0">{{ $device->nombre }}</h5>
                        <span class="badge {{ $isOnline ? 'bg-soft-success text-success' : 'bg-soft-danger text-danger' }}">
                            <span class="device-status-dot {{ $isOnline ? 'dot-online' : 'dot-offline' }}"></span>
                            {{ $isOnline ? 'Online' : 'Offline' }}
                        </span>
                    </div>
                    <p class="text-muted mb-1"><i class="uil uil-processor me-1"></i> SN: <strong>{{ $device->sn }}</strong></p>
                    <p class="text-muted mb-1"><i class="uil uil-rss me-1"></i> IP: {{ $device->ip ?? 'Desconocida' }}</p>
                    <p class="text-muted mb-2"><i class="uil uil-clock me-1"></i> Visto: {{ $device->last_seen ? $device->last_seen->diffForHumans() : 'Nunca' }}</p>
                    
                    @if(Auth::user()->hasPermission('biometria.enroll'))
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-outline-primary flex-grow-1 sync-device-btn" data-sn="{{ $device->sn }}" title="Traer datos del equipo al sistema">
                            <i class="mdi mdi-sync me-1"></i> Sincronizar
                        </button>
                        <button class="btn btn-sm btn-dark btn-open-monitor" data-sn="{{ $device->sn }}" data-name="{{ $device->nombre }}">
                            <i class="mdi mdi-console me-1"></i> Monitor
                        </button>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-soft-secondary dropdown-toggle-nocaret px-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="mdi mdi-dots-vertical fs-5"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0">
                                <li>
                                    <button class="dropdown-item provision-device-btn py-2" data-sn="{{ $device->sn }}">
                                        <i class="mdi mdi-cloud-upload-outline me-2 text-cepre-teal"></i>Cargar Todo del Sistema
                                    </button>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <button class="dropdown-item clear-device-btn py-2 text-danger" data-sn="{{ $device->sn }}">
                                        <i class="mdi mdi-trash-can-outline me-2"></i>Limpiar Datos (Reset)
                                    </button>
                                </li>
                            </ul>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="alert alert-warning">No hay dispositivos registrados. El sistema los registrará automáticamente cuando se conecten.</div>
        </div>
        @endforelse
    </div>

    <!-- Panel de Usuarios para Enrolamiento -->
    <div class="row">
        <div class="col-12">
            <div class="card biometric-card shadow-sm">
                <div class="card-header bg-white py-3 border-bottom">
                    <div class="row align-items-center">
                        <div class="col-md-3">
                            <h5 class="card-title mb-0 text-dark fw-bold">Enrolamiento de Usuarios</h5>
                        </div>
                        <div class="col-md-9">
                            <div class="row g-2 justify-content-end">
                                <div class="col-md-2">
                                    <label class="form-label small mb-1 fw-bold">Ciclo:</label>
                                    <select id="ciclo_filter" class="form-select form-select-sm filter-select">
                                        <option value="">-- Ciclo --</option>
                                        @foreach($ciclos as $ciclo)
                                            <option value="{{ $ciclo->id }}" {{ isset($activeCiclo) && $activeCiclo->id == $ciclo->id ? 'selected' : '' }}>{{ $ciclo->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small mb-1 fw-bold">Carrera:</label>
                                    <select id="carrera_filter" class="form-select form-select-sm filter-select">
                                        <option value="">-- Carrera --</option>
                                        @foreach($carreras as $carrera)
                                            <option value="{{ $carrera->id }}">{{ $carrera->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small mb-1 fw-bold">Rol:</label>
                                    <select id="role_filter" class="form-select form-select-sm filter-select">
                                        <option value="">-- Rol --</option>
                                        @foreach($roles as $role)
                                            <option value="{{ $role->id }}">{{ ucfirst($role->nombre) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small mb-1 fw-bold">Estado:</label>
                                    <select id="status_filter" class="form-select form-select-sm filter-select">
                                        <option value="">-- Biometría --</option>
                                        <option value="fingerprint_pending">Sin Huella</option>
                                        <option value="face_pending">Sin Rostro</option>
                                        <option value="both_pending">Sin Ninguno</option>
                                        <option value="fingerprint_ok">Con Huella</option>
                                        <option value="face_ok">Con Rostro</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <table id="users-table" class="table table-hover dt-responsive nowrap w-100">
                        <thead>
                            <tr>
                                <th>DNI (ID)</th>
                                <th>Nombre Completo</th>
                                <th>Rol</th>
                                <th>Huella</th>
                                <th>Rostro</th>
                                <th>Acciones de Registro</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Procesando Registro -->
<div class="modal fade" id="modalProcessing" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center p-4">
                <div class="spinner-border text-primary mb-3" role="status"></div>
                <h5>Esperando acción en el equipo...</h5>
                <p id="processing-text">Por favor, coloque el dedo o rostro en el dispositivo ZKTeco ahora.</p>
                <div id="command-result" class="mt-2" style="display:none;"></div>
                <button type="button" class="btn btn-secondary mt-3" data-bs-dismiss="modal">Cancelar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para el Monitor de Logs (Terminal) -->
<div class="modal fade" id="modalMonitor" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content bg-dark text-light border-0 shadow-lg">
            <div class="modal-header border-bottom border-secondary">
                <h5 class="modal-title text-white"><i class="mdi mdi-console-line me-2"></i>Monitor de Actividad Biométrica (ZKTeco)</h5>
                <div class="ms-auto d-flex align-items-center">
                    <span class="badge bg-success me-3" id="log-status-dot">En Vivo</span>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>
            <div class="modal-body p-0">
                <div id="log-container" style="height: 500px; overflow-y: auto; font-family: 'Courier New', Courier, monospace; font-size: 0.85rem; padding: 15px; background: #0c0c0c; color: #33ff33;">
                    <div class="text-muted italic">Iniciando monitor... Cargando historial...</div>
                </div>
            </div>
            <div class="modal-footer border-top border-secondary py-2 justify-content-between">
                <small class="text-muted">Desarrollado para CEPRE UNAMAD - Sincronización Automática Cada 3s</small>
                <div>
                   <button type="button" class="btn btn-sm btn-outline-secondary" onclick="document.getElementById('log-container').innerHTML = ''">Limpiar Pantalla</button>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Modal para Migración entre Equipos -->
<div class="modal fade" id="modalMigrate" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-cepre-teal py-3">
                <h5 class="modal-title text-white"><i class="mdi mdi-swap-horizontal me-2"></i>Migración de Datos entre Equipos</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="alert alert-soft-info mb-4 border-0 shadow-sm">
                    <div class="d-flex">
                        <div class="flex-shrink-0">
                            <i class="mdi mdi-information-outline fs-3 text-info"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            Este asistente extraerá la biometría del equipo <strong>Origen</strong> y le permitirá cargarla masivamente en el equipo <strong>Destino</strong>.
                        </div>
                    </div>
                </div>
                
                <form id="form-migrate">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark">1. Seleccione Equipo ORIGEN (Fuente de datos)</label>
                        <select class="form-select border-cepre-teal shadow-none" id="migrate-source" required>
                            <option value="">-- Seleccionar Origen --</option>
                            @foreach($devices as $d)
                                <option value="{{ $d->sn }}">{{ $d->nombre }} ({{ $d->sn }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="text-center my-3">
                        <div class="avatar-sm mx-auto">
                            <span class="avatar-title bg-soft-info text-info rounded-circle fs-4 shadow-sm border border-white">
                                <i class="mdi mdi-arrow-down"></i>
                            </span>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold text-dark">2. Seleccione Equipo DESTINO (Donde se clonarán)</label>
                        <select class="form-select border-cepre-pink shadow-none" id="migrate-target" required>
                            <option value="">-- Seleccionar Destino --</option>
                            @foreach($devices as $d)
                                <option value="{{ $d->sn }}">{{ $d->nombre }} ({{ $d->sn }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-cepre-teal btn-lg shadow-lg border-0 py-3">
                            <i class="mdi mdi-transfer me-2 fs-4"></i> Iniciar Proceso de Migración
                        </button>
                    </div>
                </form>
            </div>
            <div class="modal-footer bg-light py-2 border-0">
                <small class="text-muted italic"><i class="mdi mdi-clock-outline me-1"></i>Nota: El proceso puede tardar unos minutos según la cantidad de usuarios.</small>
            </div>
        </div>
    </div>
</div>

<!-- Tabla fantasma estructurada para evitar que datatables.init.js se rompa -->
<table id="datatable-buttons" style="display: none;">
    <thead>
        <tr>
            <th>Dummy</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>Data</td>
        </tr>
    </tbody>
</table>
@endsection

@push('scripts')
    <!-- Usar los de la plantilla para evitar conflictos -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.14.5/dist/sweetalert2.all.min.js"></script>

    <script>
        $(document).ready(function() {
            // Lista de dispositivos para JS
            const devices = @json($devices->map(function($d) {
                return [
                    'sn' => $d->sn,
                    'nombre' => $d->nombre,
                    'isOnline' => $d->last_seen && $d->last_seen->diffInMinutes(now()) < 5
                ];
            }));

            const table = $('#users-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('biometria.users') }}",
                    data: function(d) {
                        d.ciclo_id = $('#ciclo_filter').val();
                        d.carrera_id = $('#carrera_filter').val();
                        d.role_id = $('#role_filter').val();
                        d.biometric_status = $('#status_filter').val();
                    }
                },
                columns: [
                    { data: 'numero_documento', name: 'numero_documento' },
                    { data: 'nombre_completo', name: 'nombre_completo' },
                    { data: 'rol', name: 'rol' },
                    { 
                        data: 'has_fingerprint', 
                        render: function(hasFp) {
                            if (hasFp.status) {
                                return `
                                    <div class="position-relative">
                                        <span class="badge badge-registered status-badge w-100"><i class="mdi mdi-check-decagram-outline me-1"></i>Registrado</span>
                                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-info" style="z-index: 1;">
                                            ${hasFp.count}
                                        </span>
                                    </div>`;
                            }
                            return '<span class="badge badge-pending status-badge w-100"><i class="mdi mdi-alert-circle-outline me-1"></i>Pendiente</span>';
                        }
                    },
                    { 
                        data: 'has_face', 
                        render: function(hasFace) {
                            if (hasFace.status) {
                                return `
                                    <div class="position-relative">
                                        <span class="badge badge-registered status-badge w-100"><i class="mdi mdi-check-decagram-outline me-1"></i>Registrado</span>
                                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-info" style="z-index: 1;">
                                            ${hasFace.count}
                                        </span>
                                    </div>`;
                            }
                            return '<span class="badge badge-pending status-badge w-100"><i class="mdi mdi-alert-circle-outline me-1"></i>Pendiente</span>';
                        }
                    },
                    { 
                        data: null, 
                        orderable: false,
                        render: function(data) {
                            const userDevices = data.devices || [];
                            let buttons = '';
                            @if(Auth::user()->hasPermission('biometria.enroll'))
                                buttons = `
                                    <div class="btn-group-bio">
                                        <button class="btn btn-cepre-pink enroll-btn d-flex align-items-center justify-content-center" 
                                            data-type="FP" 
                                            data-id="${data.id}" 
                                            data-user-devices='${JSON.stringify(userDevices)}'>
                                            <i class="mdi mdi-fingerprint me-1"></i> <span class="btn-text">Huella</span>
                                        </button>
                                        <button class="btn btn-info enroll-btn d-flex align-items-center justify-content-center text-white" 
                                            data-type="FACE" 
                                            data-id="${data.id}"
                                            data-user-devices='${JSON.stringify(userDevices)}'>
                                            <i class="mdi mdi-face-recognition me-1"></i> <span class="btn-text">Rostro</span>
                                        </button>
                                        <button class="btn btn-info btn-dropdown text-white dropdown-toggle-nocaret d-flex align-items-center justify-content-center" 
                                                type="button" data-bs-toggle="dropdown" data-bs-boundary="viewport" aria-expanded="false">
                                            <i class="mdi mdi-dots-vertical fs-4"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 py-2">
                                            <li><h6 class="dropdown-header text-uppercase fw-bold small text-muted">Acciones Avanzadas</h6></li>
                                            <li>
                                                <a class="dropdown-item text-warning delete-biometric-btn" href="#" data-type="FINGER" data-id="${data.id}" data-user-devices='${JSON.stringify(userDevices)}'>
                                                    <i class="mdi mdi-fingerprint-off me-2 fs-5"></i>Limpiar Huellas
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item text-danger delete-biometric-btn" href="#" data-type="ALL" data-id="${data.id}" data-user-devices='${JSON.stringify(userDevices)}'>
                                                    <i class="mdi mdi-account-remove me-2 fs-5"></i>Borrar de este Equipo
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                `;
                            @else
                                buttons = '<span class="text-muted small p-2">Sin Permiso</span>';
                            @endif
                            return buttons;
                        }
                    }
                ],
                language: {
                    "sProcessing":     "Procesando...",
                    "sLengthMenu":     "Mostrar _MENU_ registros",
                    "sZeroRecords":    "No se encontraron resultados",
                    "sEmptyTable":     "Ningún dato disponible en esta tabla",
                    "sInfo":           "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
                    "sInfoEmpty":      "Mostrando registros del 0 al 0 de un total de 0 registros",
                    "sInfoFiltered":   "(filtrado de un total de _MAX_ registros)",
                    "sInfoPostFix":    "",
                    "sSearch":         "Buscar:",
                    "sUrl":            "",
                    "sInfoThousands":  ",",
                    "sLoadingRecords": "Cargando...",
                    "oPaginate": {
                        "sFirst":    "Primero",
                        "sLast":     "Último",
                        "sNext":     "Siguiente",
                        "sPrevious": "Anterior"
                    },
                    "oAria": {
                        "sSortAscending":  ": Activar para ordenar la columna de manera ascendente",
                        "sSortDescending": ": Activar para ordenar la columna de manera descendente"
                    }
                },
                drawCallback: function() {
                    // Solo re-inicializar si no se ha hecho, para evitar parpadeos
                    // Bootstrap 5 maneja esto automáticamente via data attributes si el bundle está cargado,
                    // pero en entornos mixtos a veces requiere este empujón.
                    $('.dataTables_paginate > .pagination').addClass('pagination-rounded');
                }
            });

            $('#ciclo_filter, #carrera_filter, #role_filter, #status_filter').change(function() {
                table.draw();
            });

            $(document).on('click', '.enroll-btn', function() {
                const userId = $(this).data('id');
                const type = $(this).data('type');
                const userDevices = $(this).data('user-devices') || [];
                const typeLabel = type === 'FP' ? 'Huella' : 'Rostro';
                const typeIcon = type === 'FP' ? 'mdi-fingerprint' : 'mdi-face-recognition';
                
                if (devices.length === 0) {
                    Swal.fire('Error', 'No hay biométricos registrados en el sistema.', 'error');
                    return;
                }

                // Generar opciones para el selector
                let optionsHtml = '';
                devices.forEach(d => {
                    const hasData = userDevices.includes(d.sn);
                    const isSelected = hasData ? 'selected' : '';
                    const badge = hasData ? ' (Recomendado ⭐)' : '';
                    const statusText = d.isOnline ? '🟢' : '🔴';
                    
                    optionsHtml += `<option value="${d.sn}" ${isSelected}>${statusText} ${d.nombre}${badge}</option>`;
                });

                Swal.fire({
                    title: `<div class="d-flex align-items-center justify-content-center text-cepre-pink">
                                <i class="mdi ${typeIcon} me-2 fs-1"></i>
                                <span>Registro de ${typeLabel}</span>
                            </div>`,
                    html: `
                        <div class="text-start mt-3">
                            <p class="text-muted mb-4">El equipo seleccionado entrará en modo de captura. Asegúrate de que el alumno esté frente al dispositivo.</p>
                            
                            <label class="form-label fw-bold mb-2">Seleccionar Biométrico ZKTeco:</label>
                            <select id="swal_device_selector" class="form-select form-select-lg border-2" style="border-color: #ec008c;">
                                ${optionsHtml}
                            </select>
                            
                            <div class="mt-3 p-3 bg-light rounded border">
                                <small class="text-muted d-block mb-1"><i class="mdi mdi-information-outline me-1"></i><strong>Consejo:</strong></small>
                                <small class="text-muted">Si el alumno ya tiene biometría en un equipo, hemos pre-seleccionado ese equipo para que lo sobreescribas si es necesario.</small>
                            </div>
                        </div>
                    `,
                    showCancelButton: true,
                    confirmButtonText: 'Iniciar Captura',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#ec008c',
                    cancelButtonColor: '#6c757d',
                    buttonsStyling: true,
                    width: '500px',
                    padding: '2em',
                    customClass: {
                        popup: 'rounded-4 shadow-lg',
                        title: 'border-bottom pb-3',
                        confirmButton: 'btn-lg px-4 fw-bold',
                        cancelButton: 'btn-lg px-4'
                    },
                    preConfirm: () => {
                        const sn = document.getElementById('swal_device_selector').value;
                        const device = devices.find(d => d.sn === sn);
                        if (device && !device.isOnline) {
                            Swal.showValidationMessage('El equipo seleccionado parece estar desconectado.');
                            return false;
                        }
                        return sn;
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        startEnrollment(userId, result.value, type);
                    }
                });
            });

            $('#btn-open-migrate').click(function() {
                $('#modalMigrate').modal('show');
            });

            $('#form-migrate').submit(function(e) {
                e.preventDefault();
                const source = $('#migrate-source').val();
                const target = $('#migrate-target').val();

                if (!source || !target) {
                    Swal.fire('Error', 'Debe seleccionar equipo origen y destino.', 'error');
                    return;
                }

                if (source === target) {
                    Swal.fire('Error', 'El equipo origen y destino no pueden ser el mismo.', 'error');
                    return;
                }

                Swal.fire({
                    title: '¿Iniciar Migración?',
                    html: `Se extraerán datos de <strong>${source}</strong> para clonarlos en <strong>${target}</strong>.<br><br>Primero sincronizaremos el origen.`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, empezar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $('#modalMigrate').modal('hide');
                        
                        Swal.fire({
                            title: 'Extrayendo datos de Origen...',
                            text: 'Esto actualizará el sistema con la biometría más reciente del equipo fuente.',
                            allowOutsideClick: false,
                            didOpen: () => Swal.showLoading()
                        });

                        $.post("{{ route('biometria.migrate') }}", {
                            _token: "{{ csrf_token() }}",
                            source_sn: source,
                            target_sn: target
                        }, function(response) {
                            Swal.close();
                            if (response.success) {
                                Swal.fire({
                                    title: 'Fase 1 completada',
                                    html: `<p>${response.message}</p><p class="mt-2">¿Desea abrir el monitor para ver el progreso del origen?</p>`,
                                    icon: 'success',
                                    showCancelButton: true,
                                    confirmButtonText: 'Ver Monitor',
                                    cancelButtonText: 'Cerrar'
                                }).then((monResult) => {
                                    if (monResult.isConfirmed) {
                                        $('.btn-open-monitor[data-sn="'+source+'"]').click();
                                    }
                                });
                            } else {
                                Swal.fire('Error', response.message, 'error');
                            }
                        });
                    }
                });
            });

            $(document).on('click', '.provision-device-btn', function() {
                const sn = $(this).data('sn');
                
                Swal.fire({
                    title: '¿Cargar todos los datos?',
                    text: 'Se enviarán todos los usuarios y biometrías guardadas en el sistema hacia este equipo. Use esto para configurar equipos nuevos o clonados.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, Cargar Todo',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#00acc1'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Encolando ráfaga...',
                            text: 'Preparando comandos masivos. Esto puede tardar unos segundos.',
                            allowOutsideClick: false,
                            didOpen: () => Swal.showLoading()
                        });

                        $.post("{{ route('biometria.provision') }}", {
                            _token: "{{ csrf_token() }}",
                            sn: sn
                        }, function(response) {
                            Swal.close();
                            if (response.success) {
                                Swal.fire('Éxito', response.message, 'success');
                            } else {
                                Swal.fire('Error', response.message, 'error');
                            }
                        });
                    }
                });
            });

            $(document).on('click', '.clear-device-btn', function() {
                const sn = $(this).data('sn');
                
                Swal.fire({
                    title: '<span class="text-danger">¿LIMPIAR TODO EL EQUIPO?</span>',
                    html: '<p>Esta acción <strong>BORRARÁ TODOS LOS USUARIOS Y LOGS</strong> del dispositivo físico de forma permanente.</p><p class="small text-muted">Use esto solo para Mantenimiento o Reseteo de fábrica.</p>',
                    icon: 'error',
                    showCancelButton: true,
                    confirmButtonText: 'SÍ, BORRAR TODO',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#dc3545'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Enviando órdenes de limpieza...',
                            allowOutsideClick: false,
                            didOpen: () => Swal.showLoading()
                        });

                        $.post("{{ route('biometria.clear') }}", {
                            _token: "{{ csrf_token() }}",
                            sn: sn
                        }, function(response) {
                            Swal.close();
                            if (response.success) {
                                Swal.fire('Equipo Limpiado', response.message, 'success');
                            } else {
                                Swal.fire('Error', response.message, 'error');
                            }
                        });
                    }
                });
            });

            $(document).on('click', '.sync-device-btn', function() {
                const sn = $(this).data('sn');
                
                Swal.fire({
                    title: '¿Sincronizar equipo?',
                    text: 'Se solicitarán todos los usuarios, huellas y rostros registrados en el equipo. Esto actualizará el estado en el sistema.',
                    icon: 'info',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, sincronizar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Sincronizando...',
                            text: 'Enviando comandos al equipo. Por favor, espere unos segundos.',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        $.post("{{ route('biometria.sync') }}", {
                            _token: "{{ csrf_token() }}",
                            sn: sn
                        }, function(response) {
                            Swal.close();
                            if (response.success) {
                                Swal.fire('Comandos Enviados', response.message, 'success');
                                // Recargamos la tabla después de unos segundos para ver cambios
                                setTimeout(() => table.ajax.reload(), 5000);
                            } else {
                                Swal.fire('Error', response.message, 'error');
                            }
                        }).fail(function() {
                            Swal.close();
                            Swal.fire('Error', 'No se pudo comunicar con el servidor.', 'error');
                        });
                    }
                });
            });

            function startEnrollment(userId, deviceSn, type) {
                $('#modalProcessing').modal('show');
                $('#command-result').hide();

                $.post("{{ route('biometria.enroll') }}", {
                    _token: "{{ csrf_token() }}",
                    user_id: userId,
                    device_sn: deviceSn,
                    type: type
                }, function(response) {
                    if (response.success) {
                        pollStatus(response.command_id);
                    } else {
                        $('#modalProcessing').modal('hide');
                        Swal.fire('Error', response.message, 'error');
                    }
                });
            }

            function pollStatus(commandId) {
                const interval = setInterval(function() {
                    $.get(`/biometria/status/${commandId}`, function(data) {
                        if (data.status === 'completed') {
                            clearInterval(interval);
                            $('#modalProcessing').modal('hide');
                            Swal.fire('¡Éxito!', 'Biometría registrada correctamente en el dispositivo.', 'success');
                        } else if (data.status === 'error') {
                            clearInterval(interval);
                            $('#modalProcessing').modal('hide');
                            
                            let titulo = 'Atención';
                            let icon = 'warning';
                            let msg = 'El equipo reportó un error o canceló el proceso.';
                            
                            if (data.response) {
                                if (data.response.includes('Return=2')) {
                                    msg = 'El usuario ya tiene su huella/rostro principal registrado en este equipo.';
                                    icon = 'info';
                                } else if (data.response.includes('Return=-1003')) {
                                    msg = 'Se acabó el tiempo de espera. No se detectó ninguna biometría.';
                                    icon = 'error';
                                    titulo = 'Tiempo Agotado';
                                } else if (data.response.includes('Return=-1002')) {
                                    msg = 'Comando no soportado por la versión de firmware actual del equipo.';
                                    icon = 'error';
                                    titulo = 'Error de Compatibilidad';
                                }
                            }
                            
                            Swal.fire(titulo, msg, icon);
                        }
                    });
                }, 2000);

                // Timeout de 2 minutos
                setTimeout(() => {
                    clearInterval(interval);
                    if ($('#modalProcessing').hasClass('show')) {
                        $('#modalProcessing').modal('hide');
                        Swal.fire('Tiempo agotado', 'El equipo no respondió a tiempo. Intente nuevamente si el proceso no se completó físicamente.', 'warning');
                    }
                }, 120000);
            }

            // --- Lógica del Monitor de Logs ---
            let logInterval = null;
            let currentMonitorSn = null;

            $(document).on('click', '.btn-open-monitor', function() {
                currentMonitorSn = $(this).data('sn');
                const deviceName = $(this).data('name');
                
                $('#modalMonitor').modal('show');
                $('#modalMonitor .modal-title').html(`<i class="mdi mdi-console-line me-2"></i>Monitor: ${deviceName} (${currentMonitorSn})`);
                
                fetchLogs();
                if (logInterval) clearInterval(logInterval);
                logInterval = setInterval(fetchLogs, 3000);
            });

            $('#modalMonitor').on('hidden.bs.modal', function() {
                if (logInterval) clearInterval(logInterval);
                currentMonitorSn = null;
            });

            function fetchLogs() {
                $.get("{{ route('biometria.logs') }}", { sn: currentMonitorSn }, function(response) {
                    const $container = $('#log-container');
                    const isAtBottom = $container.scrollTop() + $container.innerHeight() >= $container[0].scrollHeight - 50;
                    
                    // Formatear logs para que se vean mejor (colores por nivel)
                    let formattedLogs = response.logs.replace(/\[INFO\]/g, '<span style="color: #66ff66;">[INFO]</span>')
                                                   .replace(/\[ERROR\]/g, '<span style="color: #ff3333;">[ERROR]</span>')
                                                   .replace(/\[WARN\]/g, '<span style="color: #ffff00;">[WARN]</span>');
                    
                    $container.html(formattedLogs);
                    
                    if (isAtBottom || $container.find('.text-muted').length > 0) {
                        $container.scrollTop($container[0].scrollHeight);
                    }
                });
            }

            // --- Lógica de Borrado de Biometría ---
            $(document).on('click', '.delete-biometric-btn', function(e) {
                e.preventDefault();
                const userId = $(this).data('id');
                const type = $(this).data('type');
                const userDevices = $(this).data('user-devices') || [];
                const typeLabel = type === 'FINGER' ? 'Huellas dactilares' : 'Usuario completo (con rostro)';
                const btnColor = type === 'FINGER' ? '#ffc107' : '#dc3545';

                if (devices.length === 0) return;

                // Generar opciones para el selector de equipo
                let optionsHtml = '';
                devices.forEach(d => {
                    const hasData = userDevices.includes(d.sn);
                    const isSelected = hasData ? 'selected' : '';
                    optionsHtml += `<option value="${d.sn}" ${isSelected}>${d.isOnline ? '🟢' : '🔴'} ${d.nombre}</option>`;
                });

                Swal.fire({
                    title: '<span class="text-danger">Confirmar Borrado</span>',
                    html: `
                        <div class="text-start">
                            <p>¿Estás seguro que deseas <strong>borrar</strong> las ${typeLabel} de este usuario en el equipo físico?</p>
                            <label class="form-label fw-bold small">Seleccionar Equipo:</label>
                            <select id="swal_delete_device_selector" class="form-select border-danger">
                                ${optionsHtml}
                            </select>
                        </div>
                    `,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, Borrar Permanentemente',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: btnColor,
                    preConfirm: () => {
                        return document.getElementById('swal_delete_device_selector').value;
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        const sn = result.value;
                        
                        Swal.fire({
                            title: 'Enviando orden...',
                            allowOutsideClick: false,
                            didOpen: () => Swal.showLoading()
                        });

                        $.post("{{ route('biometria.delete') }}", {
                            _token: "{{ csrf_token() }}",
                            user_id: userId,
                            device_sn: sn,
                            type: type
                        }, function(response) {
                            Swal.close();
                            if (response.success) {
                                Swal.fire('Orden Enviada', 'Se ha enviado la instrucción de borrado al equipo. Mira el Monitor de Logs para confirmar.', 'success');
                                setTimeout(() => table.ajax.reload(), 3000);
                            } else {
                                Swal.fire('Error', response.message, 'error');
                            }
                        });
                    }
                });
            });

            $('#btn-refresh-table').click(() => table.ajax.reload());
        });
    </script>
@endpush
