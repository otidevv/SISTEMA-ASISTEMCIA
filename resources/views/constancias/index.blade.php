@extends('layouts.app')

@section('content')

<!-- NOTA: Se eliminan el CDN de Tailwind y los estilos generales para evitar conflictos con el tema Sheroyou. -->

<div class="container-fluid">
    <div class="row">
        <!-- Título y Breadcrumb (Se mantiene la estructura de Sheroyou/Bootstrap) -->
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Constancias</li>
                    </ol>
                </div>
                <h4 class="page-title">Mis Constancias</h4>
            </div>
        </div>
    </div>

    <!-- Contenedor Principal de la Tarjeta -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row mb-3 align-items-end">
                        <!-- Título y Descripción -->
                        <div class="col-md-6">
                            <h4 class="header-title mb-1">Constancias Generadas</h4>
                            <p class="text-muted mb-0">Lista de todas las constancias que has generado o que te pertenecen.</p>
                        </div>
                        <!-- Botón de Acción -->
                        <div class="col-md-6 text-end">
                            @if(Auth::user()->hasPermission('constancias.generar-estudios') || Auth::user()->hasPermission('constancias.generar-vacante'))
                                <button type="button" class="btn btn-primary waves-effect waves-light" data-bs-toggle="modal" data-bs-target="#generarConstanciaModal">
                                    <!-- Icono de Lucide: PlusCircle (Usando un SVG para estilo moderno sin colisionar CSS) -->
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="inline-block mr-1"><circle cx="12" cy="12" r="10"/><path d="M12 8v8"/><path d="M8 12h8"/></svg>
                                    Generar Nueva Constancia
                                </button>
                            @endif
                        </div>
                    </div>

                    <!-- Tabla de Constancias (Usando clases estándar de Bootstrap/Sheroyou) -->
                    <div class="table-responsive">
                        <table id="constancias-table" class="table table-striped table-hover dt-responsive nowrap w-100">
                            <thead class="table-light">
                                <tr>
                                    <th>Tipo</th>
                                    <th>Número</th>
                                    <th>Estudiante</th>
                                    <th>Ciclo</th>
                                    <th>Carrera</th>
                                    <th>Fecha Generación</th>
                                    <th>Estado</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($constancias as $constancia)
                                <tr>
                                    <td class="align-middle">
                                        <span class="badge rounded-pill 
                                            {{ $constancia->tipo == 'estudios' ? 'bg-primary' : 'bg-success' }}">
                                            <!-- ÍCONO CORREGIDO: Se usa 'Scroll' para Constancia de Estudios -->
                                            @if($constancia->tipo == 'estudios')
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="inline-block mr-1"><path d="M20.2 14.8c1.3-1.3 1.9-2.6 1.8-3.5-.1-1.3-1.3-3.8-1.3-3.8-1-2-3-2-3-2V2H7v4S5 6 3 7c0 0-1.2 2.5-1.3 3.8-.1.9.5 2.2 1.8 3.5"/><path d="M14 12c-2.4 2.1-5.6 2.1-8 0v7c2.4 2.1 5.6 2.1 8 0V12Z"/><path d="M17.5 14.5c1.1 1.1 1.5 2.1 1.4 2.8-.1.9-.9 2.1-.9 2.1s-.7 1.4-1.8 1.4c-.6 0-1.1-.3-1.5-.7L14 18l.7-.7c.4-.4.9-.7 1.5-.7 1.1 0 1.9.9 2 2 .1.7-.3 1.7-1.4 2.8"/></svg>
                                            @else
                                            <!-- Ícono original de Award (Vacante) se mantiene por ser coherente con "logro" -->
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="inline-block mr-1"><path d="m15.4 17.5 1.5-2.4 2.8-1.5-2.8-1.5-1.5-2.4-1.5 2.4-2.8 1.5 2.8 1.5 1.5 2.4Z"/><path d="M12 20v2"/><path d="M12 2v2"/></svg>
                                            @endif
                                            {{ ucfirst($constancia->tipo) }}
                                        </span>
                                    </td>
                                    <td class="align-middle"><strong>{{ $constancia->numero_constancia }}</strong></td>
                                    <td class="align-middle">{{ $constancia->estudiante->nombre }} {{ $constancia->estudiante->apellido_paterno }}</td>
                                    <td class="align-middle">{{ $constancia->inscripcion->ciclo->nombre }}</td>
                                    <td class="align-middle"><small>{{ $constancia->inscripcion->carrera->nombre }}</small></td>
                                    <td class="align-middle">{{ \Carbon\Carbon::parse($constancia->created_at)->format('d/m/Y H:i') }}</td>
                                    <td class="align-middle">
                                        <span class="badge bg-success rounded-pill">
                                            <!-- Icono de Lucide: CheckCircle -->
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="inline-block mr-1"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="m9 11 3 3L22 4"/></svg>
                                            Válida
                                        </span>
                                    </td>
                                    <td class="text-center align-middle">
                                        <div class="btn-group" role="group">
                                            <!-- Botón Ver -->
                                            @if(Auth::user()->hasPermission('constancias.view'))
                                            <a href="{{ route('constancias.estudios.ver', $constancia->id) }}"
                                               class="btn btn-sm btn-outline-info waves-effect tooltip-trigger" 
                                               target="_blank"
                                               title="Ver constancia">
                                                <!-- Icono de Lucide: Eye -->
                                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-eye"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                            </a>
                                            @endif
                                            <!-- Botón Verificar -->
                                            <a href="{{ route('constancias.validar', $constancia->codigo_verificacion) }}"
                                               class="btn btn-sm btn-outline-primary waves-effect tooltip-trigger" 
                                               target="_blank"
                                               title="Verificar autenticidad">
                                                <!-- Icono de Lucide: Qrcode -->
                                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-qr-code"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M12 12h.01"/><path d="M16 12h.01"/><path d="M8 12h.01"/><path d="M12 16h.01"/><path d="M16 16h.01"/><path d="M8 16h.01"/><path d="M8 8h.01"/><path d="M16 8h.01"/></svg>
                                            </a>
                                            <!-- Botón Descargar Firmada / Subir Firmada -->
                                            @if($constancia->constancia_firmada_path)
                                            <a href="{{ Storage::url($constancia->constancia_firmada_path) }}"
                                               class="btn btn-sm btn-outline-success waves-effect tooltip-trigger" 
                                               target="_blank"
                                               title="Descargar firmada">
                                                <!-- Icono de Lucide: Download -->
                                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-download"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" x2="12" y1="15" y2="3"/></svg>
                                            </a>
                                            @else
                                                @if(Auth::user()->hasRole('admin') || Auth::user()->id == $constancia->estudiante_id)
                                                <button type="button" 
                                                         class="btn btn-sm btn-outline-warning waves-effect tooltip-trigger"
                                                         onclick="abrirModalSubir({{ $constancia->id }}, '{{ $constancia->tipo }}')"
                                                         title="Subir firmada">
                                                     <!-- Icono de Lucide: Upload -->
                                                     <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-upload"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" x2="12" y1="3" y2="15"/></svg>
                                                 </button>
                                                 @endif
                                            @endif
                                            <!-- Botón Eliminar -->
                                            @if(Auth::user()->hasRole('admin') || Auth::user()->hasPermission('constancias.eliminar'))
                                            <button type="button" 
                                                     class="btn btn-sm btn-outline-danger waves-effect tooltip-trigger"
                                                     onclick="confirmarEliminar({{ $constancia->id }})"
                                                     title="Eliminar">
                                                <!-- Icono de Lucide: Trash2 -->
                                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-trash-2"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" x2="10" y1="11" y2="17"/><line x1="14" x2="14" y1="11" y2="17"/></svg>
                                            </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
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
<!-- Modal para generar nueva constancia (Clases de Bootstrap/Sheroyou con SVG moderno) -->
<div class="modal fade" id="generarConstanciaModal" tabindex="-1" aria-labelledby="generarConstanciaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title flex items-center" id="generarConstanciaModalLabel">
                    <!-- Icono de Lucide: FileText -->
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-file-text mr-2 inline-block"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M10 9h6"/><path d="M10 13h6"/><path d="M10 17h4"/></svg>
                    Generar Nueva Constancia
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row" id="tipo-seleccion">
                    <!-- Tarjeta Constancia de Estudios -->
                    <div class="col-md-6 mb-3 mb-md-0">
                        <div class="card border border-primary shadow-sm h-100 hover-card" style="cursor: pointer;" onclick="seleccionarTipo('estudios')">
                            <div class="card-body text-center p-4">
                                <div class="mb-3">
                                    <!-- Icono de Lucide: School (Se mantiene en el modal como representacion de estudios) -->
                                    <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#007bff" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-school mx-auto"><path d="M22 10v6m0 0l-10-6-10 6m0 0v-6"/><path d="M12 2l10 6-10 6-10-6Z"/></svg>
                                </div>
                                <h5 class="card-title mb-2">Constancia de Estudios</h5>
                                <p class="text-muted mb-3">Genera una constancia de estudios para estudiantes inscritos en el ciclo actual</p>
                                <button class="btn btn-primary waves-effect waves-light" type="button">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="inline-block mr-1"><circle cx="12" cy="12" r="10"/><path d="m12 16 4-4-4-4"/><path d="M8 12h8"/></svg>
                                    Seleccionar
                                </button>
                            </div>
                        </div>
                    </div>
                    <!-- Tarjeta Constancia de Vacante -->
                    <div class="col-md-6">
                        <div class="card border border-success shadow-sm h-100 hover-card" style="cursor: pointer;" onclick="seleccionarTipo('vacante')">
                            <div class="card-body text-center p-4">
                                <div class="mb-3">
                                    <!-- Icono de Lucide: Award (Se mantiene en el modal como representacion de logro/cupo) -->
                                    <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#28a745" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-award mx-auto"><path d="m15.4 17.5 1.5-2.4 2.8-1.5-2.8-1.5-1.5-2.4-1.5 2.4-2.8 1.5 2.8 1.5 1.5 2.4Z"/><path d="M12 20v2"/><path d="M12 2v2"/></svg>
                                </div>
                                <h5 class="card-title mb-2">Constancia de Vacante</h5>
                                <p class="text-muted mb-3">Genera una constancia de vacante para estudiantes que obtuvieron su cupo</p>
                                <button class="btn btn-success waves-effect waves-light" type="button">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="inline-block mr-1"><circle cx="12" cy="12" r="10"/><path d="m12 16 4-4-4-4"/><path d="M8 12h8"/></svg>
                                    Seleccionar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="seleccion-inscripcion" style="display: none;">
                    <div class="alert alert-info border-0 d-flex align-items-center" role="alert">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-info me-2 inline-block"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
                        <div>
                            <strong>Tipo seleccionado:</strong> <span id="tipo-texto" class="font-weight-semibold"></span>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="ciclo-select" class="form-label fw-semibold">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="inline-block me-1"><path d="M21 7.5V6a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h7.5"/><path d="M16 2v4"/><path d="M8 2v4"/><path d="M3 10h5.5M12 20a4 4 0 0 0 4-4"/><path d="M16 12v-4"/></svg>
                                Ciclo Académico
                            </label>
                            <select id="ciclo-select" class="form-select">
                                <option value="">Todos los ciclos...</option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="dni-search" class="form-label fw-semibold">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="inline-block me-1"><rect x="1" y="4" width="22" height="16" rx="2"/><path d="M1 10h22"/><line x1="7" x2="7" y1="15" y2="17"/></svg>
                                Buscar por DNI
                            </label>
                            <div class="input-group">
                                <input type="text" id="dni-search" class="form-control" placeholder="Ingresa 8 dígitos..." maxlength="8" pattern="[0-9]{8}">
                                <button type="button" class="btn btn-secondary waves-effect" onclick="buscarPorDNI()">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-search"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                                </button>
                            </div>
                            <small class="text-muted">Presiona Enter para buscar</small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="inscripcion-select" class="form-label fw-semibold">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="inline-block me-1"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><polyline points="17 11 19 13 23 9"/></svg>
                            Seleccionar Inscripción
                        </label>
                        <select id="inscripcion-select" class="form-select" size="8">
                            <option value="">Selecciona una inscripción...</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light waves-effect" id="btn-volver" style="display: none;" onclick="volverSeleccion()">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-arrow-left me-1"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg>
                    Volver
                </button>
                <button type="button" class="btn btn-secondary waves-effect" data-bs-dismiss="modal">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-x me-1"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                    Cancelar
                </button>
                <button type="button" class="btn btn-primary waves-effect waves-light" id="generar-btn" style="display: none;" onclick="generarConstancia()">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-file-check me-1"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="m9 15 2 2 4-4"/></svg>
                    Generar Constancia
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para subir constancia firmada (Clases de Bootstrap/Sheroyou con SVG moderno) -->
<div class="modal fade" id="subirConstanciaModal" tabindex="-1" aria-labelledby="subirConstanciaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title flex items-center" id="subirConstanciaModalLabel">
                    <!-- Icono de Lucide: UploadCloud -->
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-upload-cloud mr-2 inline-block"><path d="M4 14.899A7 7 0 1 1 15.71 8h1.79a4.5 4.5 0 0 1 2.5 8.242"/><path d="M12 12v6"/><path d="m15 15-3 3-3-3"/></svg>
                    Subir Constancia Firmada
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="subirConstanciaForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" id="constancia-id" name="constancia_id">
                    <input type="hidden" id="constancia-tipo" name="tipo">

                    <div class="alert alert-warning border-0" role="alert">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-alert-triangle inline-block me-2"><path d="m21.73 18-9-16c-.32-.61-.32-.88 0-1.5-.32.61.32.88 0 1.5l-9 16c-.3.53.07 1.05.69 1.05h17.42c.62 0 .99-.52.69-1.05Z"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>
                        <strong>Importante:</strong> Solo archivos PDF firmados digitalmente con tamaño máximo de 5MB.
                    </div>

                    <div class="mb-3">
                        <label for="constancia_firmada" class="form-label fw-semibold">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-file-text inline-block me-1"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/></svg>
                            Seleccionar archivo PDF
                        </label>
                        <input type="file" class="form-control" id="constancia_firmada" name="constancia_firmada"
                                accept=".pdf" required>
                        <div class="mt-2">
                            <small class="text-muted d-block">
                                <!-- Icono de Lucide: Info -->
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-info inline-block me-1"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
                                Formatos permitidos: PDF
                            </small>
                            <small class="text-muted d-block">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-info inline-block me-1"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
                                Tamaño máximo: 5MB
                            </small>
                        </div>
                    </div>

                    <div id="file-info" class="alert alert-secondary d-none" role="alert">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-file-check inline-block me-2"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="m9 15 2 2 4-4"/></svg>
                        <strong>Archivo seleccionado:</strong> <span id="file-name"></span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light waves-effect" data-bs-dismiss="modal">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-x me-1"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                        Cancelar
                    </button>
                    <button type="submit" class="btn btn-warning waves-effect waves-light">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-upload me-1"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" x2="12" y1="3" y2="15"/></svg>
                        Subir Constancia
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal de confirmación para eliminar (Clases de Bootstrap/Sheroyou con SVG moderno) -->
<div class="modal fade" id="eliminarModal" tabindex="-1" aria-labelledby="eliminarModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title flex items-center" id="eliminarModalLabel">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-alert-circle mr-2 inline-block"><circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>
                    Confirmar Eliminación
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#dc3545" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-alert-octagon mx-auto"><polygon points="7.86 2 16.14 2 22 7.86 22 16.14 16.14 22 7.86 22 2 16.14 2 7.86 7.86 2"/><line x1="12" x2="12" y1="8" y2="12"/><line x1="12" x2="12.01" y1="16" y2="16"/></svg>
                </div>
                <p class="text-center mb-0">
                    <strong>¿Estás seguro de que deseas eliminar esta constancia?</strong>
                </p>
                <p class="text-center text-muted mt-2">
                    Esta acción no se puede deshacer y la constancia será eliminada permanentemente.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light waves-effect" data-bs-dismiss="modal">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-x me-1"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                    Cancelar
                </button>
                <button type="button" class="btn btn-danger waves-effect waves-light" id="confirmar-eliminar-btn">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-trash-2 me-1"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" x2="10" y1="11" y2="17"/><line x1="14" x2="14" y1="11" y2="17"/></svg>
                    Sí, Eliminar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal de carga (Clases de Bootstrap/Sheroyou) -->
<div class="modal fade" id="loadingModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center p-4">
                <div class="spinner-border text-primary mb-3" role="status">
                    <span class="visually-hidden">Cargando...</span>
                </div>
                <p class="mb-0">Procesando...</p>
            </div>
        </div>
    </div>
</div>
@endpush

@push('css')
<style>
    /* Se mantienen los estilos de tarjetas hover y el select grande */
    .hover-card {
        transition: all 0.3s ease;
    }
    .hover-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    }
    /* Se elimina el styling de DataTables de Tailwind para usar el del tema base */
    /* Se mantiene el estilo de hover de fila para consistencia */
    .table-hover tbody tr:hover {
        background-color: rgba(0, 0, 0, 0.02);
    }
    #inscripcion-select {
        height: auto !important;
        min-height: 200px;
    }
    #inscripcion-select option {
        padding: 10px;
        border-bottom: 1px solid #f1f1f1;
    }
    .btn-group .btn {
        margin: 0 !important;
    }
    /* Clase de utilidad para alinear iconos con texto en modales */
    .modal-header .modal-title, .modal-body .alert {
        display: flex;
        align-items: center;
    }
</style>
@endpush

@push('js')
<script>
    // Se mantiene la lógica JavaScript sin cambios, ya que ahora usa las clases de Bootstrap/Sheroyou esperadas.
    
    // Tu función mostrarNotificacion (adaptada para usar el Bootstrap Toast si está disponible en Sheroyou)
    function mostrarNotificacion(tipo, mensaje) {
        let bgColor = '';
        let icon = '';
        
        // Mapeo a clases de Bootstrap
        switch(tipo) {
            case 'success':
                bgColor = 'bg-success';
                icon = 'lucide-check-circle';
                break;
            case 'error':
                bgColor = 'bg-danger';
                icon = 'lucide-alert-triangle';
                break;
            case 'warning':
                bgColor = 'bg-warning';
                icon = 'lucide-alert-octagon';
                break;
            case 'info':
                bgColor = 'bg-info';
                icon = 'lucide-info';
                break;
            default:
                bgColor = 'bg-secondary';
                icon = 'lucide-bell';
        }

        const toastHtml = `
            <div class="toast align-items-center text-white ${bgColor} border-0" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="4000">
                <div class="d-flex">
                    <div class="toast-body">
                        <i data-lucide="${icon}" class="me-2"></i>${mensaje}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        `;
        
        let toastContainer = document.getElementById('toast-container');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.id = 'toast-container';
            // Clases de Bootstrap para posicionamiento (asumiendo Bootstrap 5+)
            toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
            toastContainer.style.zIndex = '9999';
            document.body.appendChild(toastContainer);
        }

        toastContainer.insertAdjacentHTML('beforeend', toastHtml);
        const toastElement = toastContainer.lastElementChild;
        // Se asume que tu tema Sheroyou carga la librería 'lucide' o similar, o que se ha inyectado el SVG manualmente.
        // Si no se usa Lucide, tendrás que cambiar `data-lucide` por el tag SVG completo.
        const toast = new bootstrap.Toast(toastElement);
        toast.show();

        toastElement.addEventListener('hidden.bs.toast', function() {
            toastElement.remove();
        });
    }


    $(document).ready(function() {
        // Inicializar DataTable
        $('#constancias-table').DataTable({
            responsive: true,
            language: {
                url: '//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json'
            },
            order: [[5, 'desc']],
            columnDefs: [
                { orderable: false, targets: -1 }
            ]
        });

        // Inicializar tooltips (Clases cambiadas de vuelta a Bootstrap)
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('.tooltip-trigger'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            // Se usa data-bs-toggle="tooltip" y se espera el JS de Bootstrap
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Mostrar información del archivo seleccionado
        $('#constancia_firmada').on('change', function() {
            const file = this.files[0];
            if (file) {
                $('#file-name').text(file.name + ' (' + (file.size / 1024 / 1024).toFixed(2) + ' MB)');
                $('#file-info').removeClass('d-none'); // d-none es la clase de Bootstrap
            } else {
                $('#file-info').addClass('d-none');
            }
        });

        // Limpiar modal al cerrar
        $('#generarConstanciaModal').on('hidden.bs.modal', function () {
            volverSeleccion();
        });

        $('#subirConstanciaModal').on('hidden.bs.modal', function () {
            $('#subirConstanciaForm')[0].reset();
            $('#file-info').addClass('d-none');
        });
    });

    let tipoSeleccionado = '';
    let constanciaIdEliminar = null;

    function seleccionarTipo(tipo) {
        tipoSeleccionado = tipo;
        // Ocultar/Mostrar usando jQuery/Bootstrap JS
        $('#tipo-seleccion').fadeOut(300, function() {
            $('#seleccion-inscripcion').fadeIn(300).css('display', 'block');
            $('#generar-btn, #btn-volver').show();
        });

        const tipoTexto = tipo === 'estudios' ? 
            'Constancia de Estudios' : 
            'Constancia de Vacante';
        $('#tipo-texto').html(tipoTexto); // Usar .html si se incluyen iconos o tags HTML

        cargarCiclos();
        cargarInscripciones(tipo);
    }

    function volverSeleccion() {
        $('#seleccion-inscripcion').fadeOut(300, function() {
            $('#tipo-seleccion').fadeIn(300).css('display', 'flex'); // Usar 'flex' o 'block' según el display original
            $('#generar-btn, #btn-volver').hide();
        });
        tipoSeleccionado = '';
        $('#inscripcion-select').html('<option value="">Selecciona una inscripción...</option>');
        $('#dni-search').val('');
        $('#ciclo-select').val('');
    }

    // --- Lógica AJAX (Se mantiene intacta) ---

    function cargarCiclos() {
        $('#ciclo-select').html('<option value="">Cargando ciclos...</option>').prop('disabled', true);

        $.ajax({
            url: '{{ route("json.ciclos-disponibles") }}',
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            success: function(response) {
                let options = '<option value="">Todos los ciclos...</option>';
                if (response.length > 0) {
                    response.forEach(function(ciclo) {
                        const cicloInfo = `${ciclo.nombre} (${ciclo.fecha_inicio} - ${ciclo.fecha_fin})`;
                        options += `<option value="${ciclo.id}">${cicloInfo}</option>`;
                    });
                }
                $('#ciclo-select').html(options).prop('disabled', false);
            },
            error: function(xhr) {
                console.error('Error al cargar ciclos:', xhr.responseText);
                $('#ciclo-select').html('<option value="">Error al cargar ciclos</option>').prop('disabled', false);
                mostrarNotificacion('error', 'No se pudieron cargar los ciclos');
            }
        });
    }

    function cargarInscripciones(tipo, dni = null, cicloId = null) {
        $('#inscripcion-select').html('<option value="">Cargando inscripciones...</option>').prop('disabled', true);

        $.ajax({
            url: '{{ route("json.inscripciones") }}',
            method: 'GET',
            data: {
                tipo: tipo,
                dni: dni,
                ciclo_id: cicloId
            },
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            success: function(response) {
                let options = '<option value="">Selecciona una inscripción...</option>';
                if (response.length > 0) {
                    response.forEach(function(inscripcion) {
                        const cicloInfo = `${inscripcion.ciclo.nombre}`;
                        options += `<option value="${inscripcion.id}">
                            ${inscripcion.estudiante.nombre} ${inscripcion.estudiante.apellido_paterno} ${inscripcion.estudiante.apellido_materno} | 
                            DNI: ${inscripcion.estudiante.dni} | 
                            ${inscripcion.carrera.nombre} | 
                            Ciclo: ${cicloInfo}
                        </option>`;
                    });
                } else {
                    options = '<option value="">No se encontraron inscripciones</option>';
                }
                $('#inscripcion-select').html(options).prop('disabled', false);
            },
            error: function(xhr) {
                console.error('Error al cargar inscripciones:', xhr.responseText);
                let errorMsg = 'Error al cargar inscripciones';
                if (xhr.status === 419) {
                    errorMsg = 'Sesión expirada. Recarga la página.';
                } else if (xhr.status === 403) {
                    errorMsg = 'No tienes permisos para ver estas inscripciones.';
                }
                $('#inscripcion-select').html(`<option value="">${errorMsg}</option>`).prop('disabled', false);
                mostrarNotificacion('error', errorMsg);
            }
        });
    }

    function buscarPorDNI() {
        const dni = $('#dni-search').val().trim();
        if (dni.length === 8 && /^\d{8}$/.test(dni)) {
            const cicloId = $('#ciclo-select').val();
            cargarInscripciones(tipoSeleccionado, dni, cicloId);
        } else {
            mostrarNotificacion('warning', 'Por favor ingresa un DNI válido (8 dígitos numéricos)');
            $('#dni-search').focus();
        }
    }

    $('#ciclo-select').change(function() {
        const cicloId = $(this).val();
        const dni = $('#dni-search').val().trim();
        cargarInscripciones(tipoSeleccionado, dni || null, cicloId);
    });

    $('#dni-search').keypress(function(e) {
        if (e.which === 13) {
            e.preventDefault();
            buscarPorDNI();
        }
    });

    function generarConstancia() {
        const inscripcionId = $('#inscripcion-select').val();

        if (!inscripcionId) {
            mostrarNotificacion('warning', 'Por favor selecciona una inscripción');
            return;
        }

        let route = '';
        if (tipoSeleccionado === 'estudios') {
            route = '{{ route("constancias.estudios.generar", ":id") }}'.replace(':id', inscripcionId);
        } else {
            route = '{{ route("constancias.vacante.generar", ":id") }}'.replace(':id', inscripcionId);
        }

        $('#loadingModal').modal('show');
        
        setTimeout(function() {
            window.open(route, '_blank');
            $('#loadingModal').modal('hide');
            $('#generarConstanciaModal').modal('hide');
            mostrarNotificacion('success', 'Constancia generada correctamente');
            // En un entorno real, descomentar el siguiente para recargar la lista
            // setTimeout(function() { location.reload(); }, 1000); 
        }, 1000);
    }

    function confirmarEliminar(constanciaId) {
        constanciaIdEliminar = constanciaId;
        $('#eliminarModal').modal('show');
    }

    $('#confirmar-eliminar-btn').on('click', function() {
        if (constanciaIdEliminar) {
            eliminarConstancia(constanciaIdEliminar);
        }
    });

    function eliminarConstancia(constanciaId) {
        $('#eliminarModal').modal('hide');
        $('#loadingModal').modal('show');

        $.ajax({
            url: '{{ route("constancias.eliminar", ":id") }}'.replace(':id', constanciaId),
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            success: function(response) {
                $('#loadingModal').modal('hide');
                mostrarNotificacion('success', 'Constancia eliminada correctamente');
                setTimeout(function() {
                    location.reload();
                }, 1500);
            },
            error: function(xhr) {
                $('#loadingModal').modal('hide');
                console.error('Error al eliminar constancia:', xhr.responseText);
                let errorMsg = 'Error al eliminar la constancia';
                if (xhr.status === 403) {
                    errorMsg = 'No tienes permisos para eliminar esta constancia';
                } else if (xhr.status === 404) {
                    errorMsg = 'Constancia no encontrada';
                }
                mostrarNotificacion('error', errorMsg);
            }
        });
    }

    function abrirModalSubir(constanciaId, tipo) {
        $('#constancia-id').val(constanciaId);
        $('#constancia-tipo').val(tipo);
        $('#constancia_firmada').val('');
        $('#file-info').addClass('d-none');
        $('#subirConstanciaModal').modal('show');
    }

    $('#subirConstanciaForm').on('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        const constanciaId = $('#constancia-id').val();
        const tipo = $('#constancia-tipo').val();

        let route = '';
        if (tipo === 'estudios') {
            route = '{{ route("constancias.estudios.subir-firmada", ":id") }}'.replace(':id', constanciaId);
        } else {
            route = '{{ route("constancias.vacante.subir-firmada", ":id") }}'.replace(':id', constanciaId);
        }

        $('#subirConstanciaModal').modal('hide');
        $('#loadingModal').modal('show');

        $.ajax({
            url: route,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            success: function(response) {
                $('#loadingModal').modal('hide');
                mostrarNotificacion('success', 'Constancia firmada subida correctamente');
                setTimeout(function() {
                    location.reload();
                }, 1500);
            },
            error: function(xhr) {
                $('#loadingModal').modal('hide');
                console.error('Error al subir constancia:', xhr.responseText);
                let errorMsg = 'Error al subir la constancia';
                if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    if (errors.constancia_firmada) {
                        errorMsg = errors.constancia_firmada[0];
                    }
                } else if (xhr.status === 403) {
                    errorMsg = 'No tienes permisos para subir esta constancia';
                } else if (xhr.status === 413) {
                    errorMsg = 'El archivo es demasiado grande (máximo 5MB)';
                }
                mostrarNotificacion('error', errorMsg);
            }
        });
    });
</script>
@endpush
