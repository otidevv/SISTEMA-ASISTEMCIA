@extends('layouts.app')

@section('title', 'Gestión de Reforzamiento')

@push('css')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">
@endpush

@section('content')
    <!-- start page title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">Reforzamiento: Gestión de Inscripciones Standalone</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Reforzamiento</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <!-- end page title -->

    <!-- Secciones de estadísticas rápidas -->
    <div class="row">
        <div class="col-md-6 col-xl-4 text-center">
            <div class="card shadow-sm card-solid-warning">
                <div class="card-body">
                    <div class="d-flex">
                        <div class="flex-grow-1 text-left text-start">
                            <span class="text-white-50 text-uppercase fs-12 fw-bold">Pendientes de Validación</span>
                            <h3 id="count-pendiente" class="mb-0 text-white">0</h3>
                        </div>
                        <div class="align-self-center flex-shrink-0">
                            <span class="avatar-title bg-white-50 text-white rounded-circle" style="width: 48px; height: 48px; display: flex; align-items:center; justify-content:center;">
                                <i class="mdi mdi-clock-outline fs-24"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-4 text-center">
            <div class="card shadow-sm card-solid-success">
                <div class="card-body">
                    <div class="d-flex">
                        <div class="flex-grow-1 text-left text-start">
                            <span class="text-white-50 text-uppercase fs-12 fw-bold">Validados / Matriculados</span>
                            <h3 id="count-aprobado" class="mb-0 text-white">0</h3>
                        </div>
                        <div class="align-self-center flex-shrink-0">
                            <span class="avatar-title bg-white-50 text-white rounded-circle" style="width: 48px; height: 48px; display: flex; align-items:center; justify-content:center;">
                                <i class="mdi mdi-check-circle-outline fs-24"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-4 text-center">
            <div class="card shadow-sm card-solid-info">
                <div class="card-body">
                    <div class="d-flex">
                        <div class="flex-grow-1 text-left text-start">
                            <span class="text-white-50 text-uppercase fs-12 fw-bold">Total Programa</span>
                            <h3 id="count-total" class="mb-0 text-white">0</h3>
                        </div>
                        <div class="align-self-center flex-shrink-0">
                            <span class="avatar-title bg-white-50 text-white rounded-circle" style="width: 48px; height: 48px; display: flex; align-items:center; justify-content:center;">
                                <i class="mdi mdi-account-multiple-outline fs-24"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Restablecer colores de tarjetas de estadísticas con degradados del logo */
        .card-solid-warning { background: linear-gradient(135deg, var(--cepre-magenta) 0%, #b30061 100%) !important; color: white !important; }
        .card-solid-success { background: linear-gradient(135deg, var(--cepre-green) 0%, #769a19 100%) !important; color: white !important; }
        .card-solid-info { background: linear-gradient(135deg, var(--cepre-cyan) 0%, #0088ba 100%) !important; color: white !important; }
        
        /* Estilo Clean para Cabecera de Tabla (Evolución de Navy a Light & Clean) */
        #reforzamientoTable thead th { 
            background-color: #f8f9fc !important; 
            color: var(--cepre-dark-magenta) !important; 
            border-bottom: 2px solid var(--cepre-cyan) !important;
            font-size: 11px; 
            font-weight: 800; 
            text-transform: uppercase; 
            padding: 15px 12px !important; 
        }
        
        .badge-reforzamiento { padding: 6px 14px; font-weight: 800; font-size: 10px; border-radius: 4px; display: inline-flex; align-items: center; }
        .badge-reforzamiento-success { background-color: var(--cepre-green); color: #fff; }
        .badge-reforzamiento-warning { background-color: var(--cepre-magenta); color: #fff; }
        
        .payment-chip { padding: 4px 10px; border-radius: 50px; font-weight: 700; font-size: 10px; }
        .payment-chip-paid { background-color: rgba(147, 192, 31, 0.1); color: var(--cepre-green); }
        .payment-chip-unpaid { background-color: rgba(226, 0, 122, 0.1); color: var(--cepre-magenta); }

        .btn-action-reforzamiento { 
            width: 32px; 
            height: 32px; 
            display: inline-flex; 
            align-items: center; 
            justify-content: center; 
            border-radius: 8px; 
            margin: 0 2px;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            border: none;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .btn-action-reforzamiento i { font-size: 16px; color: white !important; }
        .btn-action-reforzamiento:hover { transform: translateY(-3px); box-shadow: 0 4px 12px rgba(0,0,0,0.1); filter: brightness(1.1); }
        
        .btn-solid-view { background-color: var(--cepre-cyan) !important; }
        .btn-solid-edit { background-color: #31ce8e !important; }
        .btn-solid-print { background-color: #2b323c !important; }
        .btn-solid-wa { background-color: #25d366 !important; }
        .btn-solid-delete { background-color: var(--cepre-magenta) !important; }
        .btn-solid-approve { background-color: var(--cepre-cyan) !important; }

        .bg-white-50 { background-color: rgba(255, 255, 255, 0.2) !important; }

        .doc-box {
            transition: all 0.2s ease;
            border: 1px solid var(--cepre-border-color);
        }
        .doc-box:hover {
            border-color: var(--cepre-cyan);
            background-color: #fff !important;
            box-shadow: 0 4px 8px rgba(0, 174, 239, 0.05);
        }
    </style>


    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="row mb-4 align-items-center">
                        <div class="col-sm-5">
                            <h4 class="mb-1 fw-bold text-dark">Listado de Inscripciones</h4>
                        </div>
                        <div class="col-sm-7 text-sm-end">
                            <div class="d-flex justify-content-end align-items-center">
                                <select id="filtroCiclo" class="form-control w-auto me-3 mr-3 shadow-none fw-medium">
                                    <option value="">TODOS LOS CICLOS</option>
                                    @foreach($ciclos as $ciclo)
                                        <option value="{{ $ciclo->id }}">{{ strtoupper($ciclo->nombre) }}</option>
                                    @endforeach
                                </select>
                                <button class="btn btn-dark" onclick="table.ajax.reload()">Actualizar</button>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table id="reforzamientoTable" class="table table-centered nowrap w-100">
                            <thead>
                                <tr>
                                    <th>Estudiante</th>
                                    <th>DNI</th>
                                    <th>Grado / Turno</th>
                                    <th class="text-center">Estado</th>
                                    <th class="text-center">Pago</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Detalles Ultra-Premium -->
    <div class="modal fade" id="modalExpediente" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header px-4 py-3 align-items-center" style="background: linear-gradient(135deg, #1A237E 0%, #311B92 100%);">
                    <div class="d-flex align-items-center">
                        <i class="mdi mdi-account-card-details text-white fs-24 mr-3 me-2"></i>
                        <h5 class="modal-title font-weight-bold text-white mb-0">EXPEDIENTE DIGITAL DE REFORZAMIENTO</h5>
                    </div>
                    <button type="button" class="btn-close btn-close-white shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body bg-light p-0">
                    <div id="loading-expediente" class="text-center py-5">
                        <div class="spinner-border text-primary"></div>
                        <p class="mt-2 text-muted">Cargando expediente...</p>
                    </div>

                    <div id="content-expediente" style="display:none;">
                        <div class="row g-0">
                            <!-- Columna Izquierda: Perfil -->
                            <div class="col-lg-4 border-end bg-white p-4">
                                <div class="text-center mb-4">
                                    <div class="position-relative d-inline-block mb-3">
                                        <img id="exp-foto" src="" alt="Foto Alumno" class="rounded-circle img-thumbnail shadow-sm" style="width:150px; height:150px; object-fit: cover; display:none;">
                                        <div id="exp-foto-placeholder" class="avatar-xl mx-auto">
                                            <span class="avatar-title bg-soft-primary text-primary rounded-circle font-size-48 fw-bold" id="exp-inicial" style="width:150px;height:150px;margin:0 auto;display:flex;align-items:center;justify-content:center;"></span>
                                        </div>
                                    </div>
                                    <h4 class="mb-1 fw-bold text-dark" id="exp-nombre"></h4>
                                    <div id="exp-dni-badge" class="badge bg-soft-primary text-primary px-3 py-2 rounded-pill mb-3 fs-13"></div>
                                    <div class="d-block">
                                        <div class="badge badge-reforzamiento" id="exp-status-main">PENDIENTE</div>
                                    </div>
                                </div>

                                <div class="mt-4 space-y-3">
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="avatar-xs me-3 mr-2"><div class="avatar-title bg-light text-primary rounded-circle"><i class="mdi mdi-email-outline"></i></div></div>
                                        <div><small class="text-muted d-block">Correo Electrónico</small><span id="exp-correo" class="fw-medium"></span></div>
                                    </div>
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="avatar-xs me-3 mr-2"><div class="avatar-title bg-light text-primary rounded-circle"><i class="mdi mdi-phone-outline"></i></div></div>
                                        <div><small class="text-muted d-block">Teléfono / Celular</small><span id="exp-telefono" class="fw-medium"></span></div>
                                    </div>
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="avatar-xs me-3 mr-2"><div class="avatar-title bg-light text-primary rounded-circle"><i class="mdi mdi-cake-variant-outline"></i></div></div>
                                        <div><small class="text-muted d-block">Fecha Nacimiento</small><span id="exp-nacimiento" class="fw-medium"></span></div>
                                    </div>
                                </div>

                                <div class="mt-5 pt-4 border-top">
                                    <h6 class="text-uppercase fw-bold text-muted fs-11 mb-3" style="letter-spacing: 1px;">Expediente Digital</h6>
                                    <div class="list-group list-group-flush border rounded bg-white p-1">
                                        <a id="link-dni" href="#" target="_blank" class="list-group-item list-group-item-action border-0 px-2 py-2 d-flex align-items-center">
                                            <div class="avatar-sm mr-2 me-2">
                                                <span class="avatar-title rounded-circle bg-soft-primary text-primary">
                                                    <i class="mdi mdi-account-details-outline font-size-18"></i>
                                                </span>
                                            </div>
                                            <div class="flex-grow-1 overflow-hidden">
                                                <h6 class="mb-0 text-dark fs-13">Copia DNI</h6>
                                                <small class="text-muted">Documento Estudiante</small>
                                            </div>
                                            <i class="mdi mdi-open-in-new text-muted"></i>
                                        </a>
                                        <a id="link-dni-apo" href="#" target="_blank" class="list-group-item list-group-item-action border-0 px-2 py-2 d-flex align-items-center mb-1">
                                            <div class="avatar-sm mr-2 me-2">
                                                <span class="avatar-title rounded-circle bg-soft-warning text-warning">
                                                    <i class="mdi mdi-account-group-outline font-size-18"></i>
                                                </span>
                                            </div>
                                            <div class="flex-grow-1 overflow-hidden">
                                                <h6 class="mb-0 text-dark fs-13">DNI Apoderado</h6>
                                                <small class="text-muted">Copia Padre/Madre</small>
                                            </div>
                                            <i class="mdi mdi-open-in-new text-muted"></i>
                                        </a>
                                        <a id="link-cert" href="#" target="_blank" class="list-group-item list-group-item-action border-0 px-2 py-2 d-flex align-items-center mb-1">
                                            <div class="avatar-sm mr-2 me-2">
                                                <span class="avatar-title rounded-circle bg-soft-info text-info">
                                                    <i class="mdi mdi-school-outline font-size-18"></i>
                                                </span>
                                            </div>
                                            <div class="flex-grow-1 overflow-hidden">
                                                <h6 class="mb-0 text-dark fs-13">Certificado</h6>
                                                <small class="text-muted">Estudios/Vacante</small>
                                            </div>
                                            <i class="mdi mdi-open-in-new text-muted"></i>
                                        </a>
                                        <a id="link-voucher" href="#" target="_blank" class="list-group-item list-group-item-action border-0 px-2 py-2 d-flex align-items-center mb-1">
                                            <div class="avatar-sm mr-2 me-2">
                                                <span class="avatar-title rounded-circle bg-soft-success text-success">
                                                    <i class="mdi mdi-receipt-text-outline font-size-18"></i>
                                                </span>
                                            </div>
                                            <div class="flex-grow-1 overflow-hidden">
                                                <h6 class="mb-0 text-dark fs-13">Voucher Pago</h6>
                                                <small class="text-muted">Comprobante UNAMAD</small>
                                            </div>
                                            <i class="mdi mdi-open-in-new text-muted"></i>
                                        </a>
                                        <a id="link-compromiso" href="#" target="_blank" class="list-group-item list-group-item-action border-0 px-2 py-2 d-flex align-items-center mb-1">
                                            <div class="avatar-sm mr-2 me-2">
                                                <span class="avatar-title rounded-circle bg-soft-secondary text-secondary">
                                                    <i class="mdi mdi-draw-pen font-size-18"></i>
                                                </span>
                                            </div>
                                            <div class="flex-grow-1 overflow-hidden">
                                                <h6 class="mb-0 text-dark fs-13">Carta de Compromiso</h6>
                                                <small class="text-muted">Documento Firmado</small>
                                            </div>
                                            <i class="mdi mdi-open-in-new text-muted"></i>
                                        </a>
                                    </div>

                                    <div class="mt-4 pt-4 border-top">
                                        <h6 class="text-uppercase fw-bold text-muted fs-11 mb-3" style="letter-spacing: 1px;">Acciones Administrativas</h6>
                                        <div class="d-grid gap-2" id="panel-acciones">

                                            <button id="btn-validar-modal" type="button" class="btn btn-primary btn-sm px-3 shadow-none fw-bold d-none" onclick="approveInscripcion()">
                                                <i class="mdi mdi-check-circle mr-1"></i> VALIDAR INSCRIPCIÓN
                                            </button>
                                            <button id="btn-constancia-modal" class="btn btn-dark fw-bold btn-sm d-none">
                                                <i class="mdi mdi-file-pdf-box fs-18 mr-2 me-2"></i> IMPRIMIR CONSTANCIA
                                            </button>
                                            <button id="btn-eliminar-modal" class="btn btn-outline-danger btn-sm fw-bold shadow-none" onclick="deleteRecord($('#modalExpediente').data('id'))">
                                                <i class="mdi mdi-delete-outline fs-18 mr-2 me-2"></i> ANULAR REGISTRO
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Columna Derecha: Detalles -->
                            <div class="col-lg-8 p-4 bg-light">
                                <div class="row">
                                    <!-- Datos Escolares -->
                                    <div class="col-12 mb-4">
                                        <div class="card border-0 shadow-none rounded-lg p-3">
                                            <h6 class="text-primary fw-bold text-uppercase fs-12 mb-3">INFORMACIÓN ACADÉMICA</h6>
                                            <div class="row align-items-center">
                                                <div class="col-md-2 text-center border-end">
                                                    <div class="display-6 fw-bold text-dark" id="exp-grado"></div>
                                                    <small class="text-muted text-uppercase">Grado</small>
                                                </div>
                                                <div class="col-md-2 text-center border-end">
                                                    <div class="fs-24 fw-bold text-primary" id="exp-turno"></div>
                                                    <small class="text-muted text-uppercase">Turno</small>
                                                </div>
                                                <div class="col-md-3 text-center border-end">
                                                    <div class="fs-18 fw-bold text-success" id="exp-aula">---</div>
                                                    <small class="text-muted text-uppercase">Aula</small>
                                                </div>
                                                <div class="col-md-5 px-3">
                                                    <small class="text-muted text-uppercase d-block mb-1" style="font-size: 10px; letter-spacing: 0.5px;">Institución de Procedencia</small>
                                                    <div class="fw-bold fs-15 text-dark" id="exp-colegio">Cargando...</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Comprobante de Pago Digital -->
                                    <div class="col-12 mb-4">
                                        <div class="card border-0 shadow-none rounded-lg p-0 overflow-hidden" style="border: 1px solid #e0e0e0 !important;">
                                            <div class="bg-soft-success px-4 py-2 border-bottom d-flex justify-content-between align-items-center">
                                                <h6 class="text-success fw-bold text-uppercase fs-12 mb-0"><i class="mdi mdi-receipt-text mr-1"></i> INFORMACIÓN DEL PAGO (UNAMAD)</h6>
                                                <div id="exp-pago-status" class="payment-chip"></div>
                                            </div>
                                            <div class="p-4">
                                                <div class="row mb-3">
                                                    <div class="col-6">
                                                        <small class="text-muted d-block text-uppercase lh-1 mb-1" style="font-size:10px;">N° Recibo</small>
                                                        <span class="fw-bold fs-16 text-dark" id="pago-operacion">---</span>
                                                    </div>
                                                    <div class="col-6 text-end">
                                                        <small class="text-muted d-block text-uppercase lh-1 mb-1" style="font-size:10px;">Fecha Emisión</small>
                                                        <span class="fw-bold fs-15 text-dark" id="pago-fecha">---</span>
                                                    </div>
                                                </div>
                                                <div class="table-responsive">
                                                    <table class="table table-sm table-borderless mb-0">
                                                        <thead>
                                                            <tr class="border-bottom">
                                                                <th class="text-muted small ps-0">CONCEPTO</th>
                                                                <th class="text-muted small text-end pe-0">IMPORTE</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <tr>
                                                                <td class="ps-0 fs-14 fw-medium">REFORZAMIENTO ESCOLAR (CEPRE UNAMAD)</td>
                                                                <td class="pe-0 text-end fw-bold" id="row-monto-pago">---</td>
                                                            </tr>
                                                        </tbody>
                                                        <tfoot>
                                                            <tr class="border-top">
                                                                <td class="ps-0 pt-2 fw-bold text-dark">TOTAL PAGADO</td>
                                                                <td class="pe-0 pt-2 text-end fw-bold text-success fs-18" id="pago-monto">---</td>
                                                            </tr>
                                                        </tfoot>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Apoderados -->
                                    <div class="col-12">
                                        <h6 class="text-dark fw-bold text-uppercase fs-12 mb-3">RESPONSABLES / APODERADOS</h6>
                                        <div id="container-apoderados" class="row"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>



    <!-- Modal: Edición Integral (Versión Final Definitiva) -->
    <div class="modal fade" id="modalGigaEdicion" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-dark py-3">
                    <h5 class="modal-title font-size-15 text-white fw-bold">
                        <i class="mdi mdi-database-edit-outline mr-2 me-2 text-warning"></i> GESTIÓN INTEGRAL DE EXPEDIENTE
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <form id="form-reforzamiento-giga" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" id="ef-id" name="id">
                        
                        <div class="p-4">
                            <div class="row">
                                <!-- Columna 1: Estudiante -->
                                <div class="col-lg-4 border-end px-3">
                                    <h6 class="text-uppercase fw-bold text-primary fs-11 mb-3" style="letter-spacing: 1px;">1. Datos Estudiante</h6>
                                    <div class="mb-2">
                                        <label class="form-label text-muted fs-10 mb-1">DNI</label>
                                        <input type="text" id="ef-dni" class="form-control bg-light fw-bold" readonly>
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label text-muted fs-10 mb-1">Nombres</label>
                                        <input type="text" name="nombre" id="ef-nombre" class="form-control form-control-sm">
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-6">
                                            <label class="form-label text-muted fs-10 mb-1">Ap. Paterno</label>
                                            <input type="text" name="apellido_paterno" id="ef-paterno" class="form-control form-control-sm">
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label text-muted fs-10 mb-1">Ap. Materno</label>
                                            <input type="text" name="apellido_materno" id="ef-materno" class="form-control form-control-sm">
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-6">
                                            <div class="mb-3">
                                                <label class="form-label text-muted fs-10 mb-1">Celular Estudiante</label>
                                                <input type="text" name="telefono" id="ef-telefono" class="form-control form-control-sm">
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="mb-3">
                                                <label class="form-label text-muted fs-10 mb-1">Correo Electrónico</label>
                                                <input type="email" name="email" id="ef-email" class="form-control form-control-sm">
                                            </div>
                                        </div>
                                    </div>

                                    <h6 class="text-uppercase fw-bold text-primary fs-11 mt-4 mb-3" style="letter-spacing: 1px;">2. Datos del Apoderado</h6>
                                    <div class="mb-2">
                                        <label class="form-label text-muted fs-10 mb-1">Nombre Apoderado</label>
                                        <input type="text" name="apoderado_nombre" id="ef-apo-nombre" class="form-control form-control-sm">
                                    </div>
                                    <div class="row">
                                        <div class="col-6">
                                            <label class="form-label text-muted fs-10 mb-1">DNI Apoderado</label>
                                            <input type="text" name="apoderado_dni" id="ef-apo-dni" class="form-control form-control-sm">
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label text-muted fs-10 mb-1">Celular Apoderado</label>
                                            <input type="text" name="apoderado_telefono" id="ef-apo-telefono" class="form-control form-control-sm">
                                        </div>
                                    </div>
                                </div>

                                <!-- Columna 2: Configuración Académica -->
                                <div class="col-lg-4 border-end px-3">
                                    <h6 class="text-uppercase fw-bold text-success fs-11 mb-3" style="letter-spacing: 1px;">3. Académico y Aula</h6>
                                    <div class="row mb-2">
                                        <div class="col-6">
                                            <label class="form-label text-muted fs-10 mb-1">Grado</label>
                                            <input type="text" name="grado" id="ef-grado" class="form-control form-control-sm fw-bold">
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label text-muted fs-10 mb-1">Turno</label>
                                            <input type="text" name="turno" id="ef-turno" class="form-control form-control-sm fw-bold">
                                        </div>
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label text-muted fs-10 mb-1">Colegio de Procedencia</label>
                                        <input type="text" name="colegio_procedencia" id="ef-colegio" class="form-control form-control-sm">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label text-danger fw-bold fs-10 mb-1">Asignar/Cambiar Aula</label>
                                        <select name="aula_id" id="ef-aula" class="form-select form-select-sm shadow-none">
                                            <option value="">-- SELECCIONE AULA --</option>
                                            @foreach($aulas as $aula)
                                                <option value="{{ $aula->id }}">{{ $aula->nombre }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <h6 class="text-uppercase fw-bold text-info fs-11 mt-4 mb-3" style="letter-spacing: 1px;">4. Información de Pago</h6>
                                    <div class="row mb-2">
                                        <div class="col-6">
                                            <label class="form-label text-muted fs-10 mb-1">N° Operación</label>
                                            <input type="text" name="numero_operacion" id="ef-pago-recibo" class="form-control form-control-sm">
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label text-muted fs-10 mb-1">Monto (S/.)</label>
                                            <input type="number" step="0.01" name="monto" id="ef-pago-monto" class="form-control form-control-sm fw-bold">
                                        </div>
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label text-muted fs-10 mb-1">Mes del Pago</label>
                                        <input type="text" name="mes_pagado" id="ef-pago-mes" class="form-control form-control-sm">
                                    </div>
                                </div>

                                <!-- Columna 3: Expediente Digital -->
                                <div class="col-lg-4 px-3">
                                    <h6 class="text-uppercase fw-bold text-indigo fs-11 mb-3" style="letter-spacing: 1px;">5. Gestión Documental</h6>
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <div class="doc-box p-2 rounded text-center bg-light">
                                                <p class="fs-9 fw-bold mb-1 text-primary">DNI ESTUDIANTE</p>
                                                <input type="file" name="dni_file" class="form-control form-control-xs">
                                                <div id="ef-dni-link" class="mt-1 small"></div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="doc-box p-2 rounded text-center bg-light">
                                                <p class="fs-9 fw-bold mb-1 text-success">VOUCHER PAGO</p>
                                                <input type="file" name="voucher_file" class="form-control form-control-xs">
                                                <div id="ef-voucher-link" class="mt-1 small"></div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="doc-box p-2 rounded text-center bg-light">
                                                <p class="fs-9 fw-bold mb-1 text-warning">CARTA COMP.</p>
                                                <input type="file" name="compromiso_file" class="form-control form-control-xs">
                                                <div id="ef-compromiso-link" class="mt-1 small"></div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="doc-box p-2 rounded text-center bg-light">
                                                <p class="fs-9 fw-bold mb-1 text-info">CERTIFICADO</p>
                                                <input type="file" name="certificado_file" class="form-control form-control-xs">
                                                <div id="ef-certificado-link" class="mt-1 small"></div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="doc-box p-2 rounded text-center bg-light">
                                                <p class="fs-9 fw-bold mb-1 text-danger">DNI APODERADO</p>
                                                <input type="file" name="dni_apoderado_file" class="form-control form-control-xs">
                                                <div id="ef-dni-apoderado-link" class="mt-1 small"></div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="doc-box p-2 rounded text-center bg-dark">
                                                <p class="fs-9 fw-bold mb-1 text-white text-uppercase">Foto Alumno</p>
                                                <input type="file" name="foto_file" class="form-control form-control-xs">
                                                <div id="ef-foto-link" class="mt-1 small"></div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-4">
                                        <label class="form-label fs-10 fw-bold text-danger">Observaciones del Cambio *</label>
                                        <textarea name="observaciones" id="ef-observaciones" class="form-control border-danger form-control-sm" rows="3" placeholder="Sustento administrativo..." required></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer bg-light border-top p-3 px-4">
                            <button type="button" class="btn btn-secondary px-4 fw-bold fs-12 uppercase" data-bs-dismiss="modal">CANCELA</button>
                            <button type="submit" id="btn-save-full" class="btn btn-info px-5 fw-bold shadow-none text-white fs-12">
                                <i class="mdi mdi-content-save-check-outline mr-2 me-2"></i> ACTUALIZAR EXPEDIENTE
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script>
        let table;
        $(document).ready(function() {
            // Configuración Global de Toastr
            toastr.options = {
                "closeButton": true,
                "progressBar": true,
                "positionClass": "toast-top-right",
                "timeOut": "4000",
                "extendedTimeOut": "1000",
                "showEasing": "swing",
                "hideEasing": "linear",
                "showMethod": "fadeIn",
                "hideMethod": "fadeOut"
            };

            table = $('#reforzamientoTable').DataTable({
                processing: true, serverSide: true,
                ajax: {
                    url: "{{ route('admin.reforzamiento.data') }}",
                    data: d => { d.ciclo_id = $('#filtroCiclo').val(); },
                    dataSrc: json => {
                        if(json.counts) {
                            animateCounter('#count-pendiente', json.counts.pendiente);
                            animateCounter('#count-aprobado', json.counts.aprobado);
                            animateCounter('#count-total', json.counts.total);
                        }
                        return json.data;
                    }
                },
                columns: [
                    { data: 'estudiante_nombre' },
                    { data: 'dni' },
                    { data: 'grado_turno' },
                    { data: 'estado', className: 'text-center' },
                    { data: 'semaforo_pagos', className: 'text-center' },
                    { data: 'acciones', className: 'text-center', orderable: false }
                ],
                language: { url: "{{ asset('assets/libs/datatables.net/i18n/Spanish.json') }}" },
                drawCallback: function() {
                    // Animación sutil al redibujar la tabla
                    $('.badge').addClass('animate__animated animate__fadeIn');
                }
            });
            $('#filtroCiclo').on('change', () => table.ajax.reload());

            window.reforzamientoDataTable = table; // Para que el escuchador global en header.blade.php lo detecte

            // Animación al redibujar
            table.on('draw', function() {
                $('.badge').addClass('animate__animated animate__fadeIn');
            });
        });

        function animateCounter(id, target) {
            $({ countNum: $(id).text() }).animate({ countNum: target }, {
                duration: 800,
                easing: 'swing',
                step: function() { $(id).text(Math.ceil(this.countNum)); },
                complete: function() { $(id).text(this.countNum); }
            });
        }

        function viewDetails(id) {
            $('#loading-expediente').show(); $('#content-expediente').hide(); $('#modalExpediente').modal('show');
            $.get("{{ url('admin/reforzamiento') }}/" + id, data => {
                const s = data.estudiante;
                const storageUrl = "{{ asset('storage') }}/";
                
                // Foto
                if (data.foto_path) {
                    $('#exp-foto').attr('src', storageUrl + data.foto_path).show();
                    $('#exp-foto-placeholder').hide();
                } else {
                    $('#exp-foto').hide();
                    $('#exp-foto-placeholder').show();
                    $('#exp-inicial').text(s.nombre.charAt(0));
                }

                // Perfil
                $('#exp-nombre').text(s.nombre + ' ' + s.apellido_paterno + ' ' + (s.apellido_materno || ''));
                $('#exp-dni-badge').text('DNI: ' + s.numero_documento);
                $('#exp-correo').text(s.email || '---');
                $('#exp-telefono').text(s.telefono || '---');
                
                // Formatear fecha nacimiento
                if (s.fecha_nacimiento) {
                    let d = s.fecha_nacimiento.toString().split('T')[0];
                    let p = d.split('-');
                    $('#exp-nacimiento').text(p.length === 3 ? `${p[2]}/${p[1]}/${p[0]}` : d);
                } else { $('#exp-nacimiento').text('---'); }

                // Académico
                $('#exp-grado').text(data.grado || '---');
                $('#exp-turno').text(data.turno ? (data.turno.charAt(0).toUpperCase() + data.turno.slice(1).toLowerCase()) : '---');
                $('#exp-aula').text(data.aula ? data.aula.nombre.toUpperCase() : 'PENDIENTE').removeClass('text-success text-muted').addClass(data.aula ? 'text-success' : 'text-muted');
                
                // Escuela de procedencia
                let colName = data.colegio_procedencia;
                if (!colName || colName.trim() === '' || colName === 'null') {
                    colName = '<span class="text-muted italic">No registrado</span>';
                    $('#exp-colegio').html(colName);
                } else {
                    $('#exp-colegio').text(colName);
                }

                // Multimedia y Documentos
                const cleanPath = (path) => path ? storageUrl + path.replace(/^\/+/, '') : '#';

                // DNI Estudiante
                const dniPath = cleanPath(data.dni_estudiante_path);
                $('#link-dni').attr('href', dniPath).toggle(!!data.dni_estudiante_path);
                
                // DNI Apoderado
                const dniApoPath = cleanPath(data.dni_apoderado_path);
                $('#link-dni-apo').attr('href', dniApoPath).toggle(!!data.dni_apoderado_path);

                // Certificado
                const certPath = cleanPath(data.certificado_path);
                $('#link-cert').attr('href', certPath).toggle(!!data.certificado_path);

                // Carta de Compromiso
                const compPath = cleanPath(data.carta_compromiso_path);
                $('#link-compromiso').attr('href', compPath).toggle(!!data.carta_compromiso_path);

                // Fotografía
                $('#detalle-foto').attr('src', data.foto_path ? cleanPath(data.foto_path) : 'https://via.placeholder.com/150');

                // Información de Pago
                const pago = data.pagos && data.pagos.length > 0 ? data.pagos[0] : null;
                if (pago) {
                    $('#pago-operacion').text(pago.numero_operacion || 'AUTO');
                    const formattedMonto = 'S/. ' + parseFloat(pago.monto || 0).toFixed(2);
                    $('#pago-monto').text(formattedMonto);
                    $('#row-monto-pago').text(formattedMonto);
                    
                    if (pago.fecha_pago) {
                        const datePago = new Date(pago.fecha_pago);
                        $('#pago-fecha').text(datePago.toLocaleDateString('es-PE', { day: '2-digit', month: '2-digit', year: 'numeric' }));
                    } else { $('#pago-fecha').text('---'); }
                    
                    // Manejo del Link de Voucher
                    if (pago.voucher_path) {
                        $('#link-voucher').attr('href', cleanPath(pago.voucher_path)).show();
                        $('#btn-voucher-text').text('Ver Voucher');
                    } else if (pago.verificado_api) {
                        $('#link-voucher').attr('href', 'javascript:void(0)').show();
                        $('#btn-voucher-text').text('Validado por API');
                        $('#link-voucher').off('click').on('click', function() {
                            alert('Pago validado automáticamente vía API (Sin archivo físico).');
                        });
                    } else {
                        $('#link-voucher').hide();
                    }
                } else {
                    $('#pago-operacion').text('---');
                    $('#pago-monto').text('---');
                    $('#pago-fecha').text('---');
                    $('#link-voucher').hide();
                }

                // Configurar Botones de Acción: Gestión Limpia por Estados
                const currentStatus = (data.estado_inscripcion || "PENDIENTE").toString().trim().toUpperCase();
                const isValidated = (currentStatus === 'VALIDADO');
                
                $('#btn-validar-modal').toggleClass('d-none', isValidated).off().on('click', () => approve(data.id));
                $('#btn-constancia-modal').toggleClass('d-none', !isValidated).off().on('click', () => {
                    window.open("{{ url('admin/reforzamiento') }}/" + data.id + "/print", '_blank');
                });

                $('#btn-eliminar-modal').off().on('click', () => deleteRecord(data.id));

                // Estado Inscripción
                const status = (data.estado_inscripcion || "PENDIENTE").toUpperCase();
                $('#exp-status-main').text(status)
                    .removeClass('badge-reforzamiento-success badge-reforzamiento-warning')
                    .addClass(status === 'VALIDADO' ? 'badge-reforzamiento-success' : 'badge-reforzamiento-warning');

                // Apoderados
                let apHTML = '';
                if(data.apoderados && data.apoderados.length > 0) {
                    data.apoderados.forEach(p => {
                        apHTML += `
                            <div class="col-md-6 mb-3">
                                <div class="bg-white p-3 border rounded shadow-none h-100">
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="avatar-xs mr-2 me-2">
                                            <span class="avatar-title bg-soft-info text-info rounded-circle font-size-12">
                                                <i class="mdi mdi-account-child"></i>
                                            </span>
                                        </div>
                                        <span class="text-info font-weight-bold text-uppercase fs-10" style="letter-spacing: 0.5px;">${p.parentesco}</span>
                                    </div>
                                    <h6 class="my-1 fw-bold fs-14 text-dark">${p.nombres}</h6>
                                    <div class="text-muted fs-12 mt-2">
                                        <i class="mdi mdi-card-account-details-outline mr-1"></i> DNI: ${p.numero_documento || '---'}
                                    </div>
                                    <div class="text-muted fs-12">
                                        <i class="mdi mdi-phone-outline mr-1"></i> Celular: ${p.celular}
                                    </div>
                                </div>
                            </div>`;
                    });
                } else { apHTML = '<div class="col-12 text-muted px-3">No hay apoderados registrados para este estudiante.</div>'; }
                $('#container-apoderados').html(apHTML);

                $('#loading-expediente').hide(); $('#content-expediente').fadeIn(400);
            });
        }

        function approve(id) {
            Swal.fire({
                title: 'Asignación de Aula',
                text: "Seleccione el aula para finalizar la validación del estudiante:",
                icon: 'info',
                input: 'select',
                inputOptions: {
                    @foreach($aulas as $aula)
                    '{{ $aula->id }}': '{{ $aula->codigo }} - {{ strtoupper($aula->nombre) }} (Capacidad: {{ $aula->capacidad }})',
                    @endforeach
                },
                inputPlaceholder: '-- Seleccionar Aula --',
                showCancelButton: true,
                confirmButtonColor: '#1b5e20',
                confirmButtonText: 'Validar y Matricular',
                cancelButtonText: 'Cancelar',
                inputValidator: (value) => {
                    return new Promise((resolve) => {
                        if (value !== '') {
                            resolve();
                        } else {
                            resolve('Es obligatorio asignar un aula para validar.');
                        }
                    });
                }
            }).then(r => {
                if (r.isConfirmed) {
                    $.post("{{ url('admin/reforzamiento') }}/" + id + "/status", {
                        _token: '{{ csrf_token() }}',
                        estado: 'validado',
                        aula_id: r.value
                    }).done(function(res) {
                        Swal.fire({
                            title: '¡Sujeto Matriculado!',
                            html: `<div class="text-center">
                                    <i class="mdi mdi-check-circle-outline text-success" style="font-size: 50px;"></i>
                                    <p class="mt-2">La inscripción ha sido validada correctamente.</p>
                                    <h4 class="fw-bold text-primary">CONSTANCIA N° ${res.nro_constancia}</h4>
                                   </div>`,
                            icon: 'success'
                        });
                        
                        // 1. Recargar la tabla sin refrescar la página
                        if (typeof table !== 'undefined') {
                            table.ajax.reload(null, false);
                        }

                        // 2. Actualizar el UI del Modal en tiempo real de forma profesional
                        $('#exp-status-main').text('VALIDADO').removeClass('badge-reforzamiento-warning').addClass('badge-reforzamiento-success');
                        $('#exp-pago-status').text('VALIDADO OK').removeClass('payment-chip-unpaid').addClass('payment-chip-paid');
                        
                        $('#btn-validar-modal').addClass('d-none');
                        $('#btn-constancia-modal').removeClass('d-none').off().on('click', () => {
                            window.open("{{ url('admin/reforzamiento') }}/" + id + "/print", '_blank');
                        });

                    }).fail(function(err) {
                        Swal.fire('Error', 'No se pudo completar la validación.', 'error');
                    });
                }
            });
        }

        function editInscripcion(id) {
            const form = document.getElementById('form-reforzamiento-giga');
            if (form) form.reset();
            
            $.get("{{ url('admin/reforzamiento') }}/" + id, data => {
                const s = data.estudiante;
                const insc = data;
                const p = (insc.pagos && insc.pagos.length > 0) ? insc.pagos[0] : {};

                // I. DATOS DEL ESTUDIANTE
                $('#ef-id').val(insc.id);
                $('#ef-dni').val(s.numero_documento);
                $('#ef-nombre').val(s.nombre);
                $('#ef-paterno').val(s.apellido_paterno);
                $('#ef-materno').val(s.apellido_materno);
                $('#ef-telefono').val(s.telefono);
                $('#ef-email').val(s.email);
                
                // II. DATOS DEL APODERADO (Carga desde tabla ApoderadoReforzamiento)
                const apo = (insc.apoderados && insc.apoderados.length > 0) ? insc.apoderados[0] : {};
                $('#ef-apo-nombre').val(apo.nombres || '');
                $('#ef-apo-dni').val(apo.numero_documento || '');
                $('#ef-apo-telefono').val(apo.celular || '');

                // III. ACADÉMICO Y AULA
                $('#ef-grado').val(insc.grado);
                $('#ef-turno').val(insc.turno);
                $('#ef-colegio').val(insc.colegio_procedencia);
                $('#ef-aula').val(insc.aula_id || "");

                // IV. PAGOS (Con traducción de mes)
                $('#ef-pago-recibo').val(p.numero_operacion);
                $('#ef-pago-monto').val(p.monto);
                
                // Traducción de Mes (Puente de Inglés a Español)
                let mesOriginal = p.mes_pagado || "";
                const mesesMap = {
                    'January': 'Enero', 'February': 'Febrero', 'March': 'Marzo', 'April': 'Abril',
                    'May': 'Mayo', 'June': 'Junio', 'July': 'Julio', 'August': 'Agosto',
                    'September': 'Setiembre', 'October': 'Octubre', 'November': 'Noviembre', 'December': 'Diciembre'
                };
                for (let en in mesesMap) {
                    if (mesOriginal.includes(en)) {
                        mesOriginal = mesOriginal.replace(en, mesesMap[en]);
                    }
                }
                $('#ef-pago-mes').val(mesOriginal);
                
                $('#ef-observaciones').val(insc.observaciones);
                
                // V. LINKS DE DOCUMENTOS EXISTENTES
                const storageUrl = "{{ asset('storage') }}/";
                const setFileLink = (elementId, path) => {
                    const el = $(elementId);
                    if (path) {
                        el.html(`<a href="${storageUrl + path}" target="_blank" class="text-primary fw-bold"><i class="mdi mdi-eye"></i> Ver Actual</a>`);
                    } else {
                        el.html(`<span class="text-muted italic fs-10">Sin archivo</span>`);
                    }
                };

                setFileLink('#ef-dni-link', insc.dni_estudiante_path);
                
                // El voucher está en la relación de pagos
                const voucherPath = (insc.pagos && insc.pagos.length > 0) ? insc.pagos[0].voucher_path : null;
                setFileLink('#ef-voucher-link', voucherPath);
                
                setFileLink('#ef-compromiso-link', insc.carta_compromiso_path);
                setFileLink('#ef-certificado-link', insc.certificado_path);
                setFileLink('#ef-dni-apoderado-link', insc.dni_apoderado_path);
                setFileLink('#ef-foto-link', insc.foto_path);
                
                $('#modalGigaEdicion').modal('show');
            }).fail(() => {
                toastr.error('No se pudieron cargar los datos del expediente.');
            });
        }

        $(document).ready(function() {
            $('#form-reforzamiento-giga').on('submit', function(e) {
                e.preventDefault();
                const id = $('#ef-id').val();
                const fd = new FormData(this);

                Swal.fire({
                    title: '¿Confirmar Cambios?',
                    text: "Se actualizará toda la información del expediente.",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, actualizar todo',
                    cancelButtonText: 'Revisar'
                }).then((r) => {
                    if (r.isConfirmed) {
                        $('#btn-save-full').prop('disabled', true).html('<i class="mdi mdi-loading mdi-spin mr-1"></i> ACTUALIZANDO...');
                        
                        $.ajax({
                            url: "{{ url('admin/reforzamiento') }}/" + id + "/update-data",
                            type: 'POST',
                            data: fd,
                            processData: false,
                            contentType: false
                        }).done(res => {
                            // Detectar si se subieron archivos para un mensaje más específico
                            const hasFiles = fd.get('dni_file').size > 0 || fd.get('voucher_file').size > 0 || 
                                             fd.get('compromiso_file').size > 0 || fd.get('certificado_file').size > 0 || 
                                             fd.get('dni_apoderado_file').size > 0 || fd.get('foto_file').size > 0;
                            
                            const successMsg = hasFiles ? '¡Expediente y Archivos actualizados!' : res.message;
                            
                            toastr.success(successMsg, 'Proceso Completado');
                            $('#modalGigaEdicion').modal('hide');
                            if (typeof table !== 'undefined') table.ajax.reload(null, false);
                        }).fail(err => {
                            let msg = 'No se pudo actualizar el expediente.';
                            if (err.responseJSON && err.responseJSON.message) msg = err.responseJSON.message;
                            Swal.fire('Error', msg, 'error');
                        }).always(() => {
                            $('#btn-save-full').prop('disabled', false).html('<i class="mdi mdi-content-save-check-outline mr-2 me-2"></i> ACTUALIZAR EXPEDIENTE');
                        });
                    }
                });
            });
        });



        function deleteRecord(id) {
            Swal.fire({
                title: '¿Anular Inscripción?',
                text: "Esta acción eliminará todos los datos asociados al programa de Reforzamiento.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#74788d',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ url('admin/reforzamiento') }}/" + id,
                        type: 'DELETE',
                        data: { _token: '{{ csrf_token() }}' }
                    }).done(function(res) {
                        Swal.fire('Eliminado', 'El registro ha sido removido con éxito.', 'success');
                        if (typeof table !== 'undefined') table.ajax.reload(null, false);
                        $('#modalExpediente').modal('hide');
                    }).fail(function(err) {
                        Swal.fire('Error', 'No se pudo eliminar el registro.', 'error');
                    });
                }
            });
        }
    </script>
@endpush
