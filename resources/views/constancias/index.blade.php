@extends('layouts.app')

@section('content')

<!-- NOTA: Se eliminan el CDN de Tailwind y los estilos generales para evitar conflictos con el tema Sheroyou. -->

<div class="container-fluid" id="constancias-module">
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

                    <!-- Tarjetas de Estadísticas -->
                    <div class="row mb-4">
                        <div class="col-xl-3 col-md-6">
                            <div class="card widget-flat bg-primary text-white">
                                <div class="card-body">
                                    <div class="float-end">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="opacity-75">
                                            <path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/>
                                            <polyline points="14 2 14 8 20 8"/>
                                        </svg>
                                    </div>
                                    <h5 class="text-white-50 fw-normal mt-0" title="Total de Constancias">Total Generadas</h5>
                                    <h3 class="mt-3 mb-1 text-white">{{ $constancias->count() }}</h3>
                                    <p class="mb-0 text-white-50">
                                        <span class="text-nowrap">Todas las constancias</span>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6">
                            <div class="card widget-flat bg-success text-white">
                                <div class="card-body">
                                    <div class="float-end">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="opacity-75">
                                            <path d="M22 10v6M2 10l10-5 10 5-10 5z"/>
                                            <path d="M6 12v5c3 3 9 3 12 0v-5"/>
                                        </svg>
                                    </div>
                                    <h5 class="text-white-50 fw-normal mt-0" title="Constancias de Estudios">Estudios</h5>
                                    <h3 class="mt-3 mb-1 text-white">{{ $constancias->where('tipo', 'estudios')->count() }}</h3>
                                    <p class="mb-0 text-white-50">
                                        <span class="text-nowrap">Certificados académicos</span>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6">
                            <div class="card widget-flat bg-info text-white">
                                <div class="card-body">
                                    <div class="float-end">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="opacity-75">
                                            <rect width="18" height="18" x="3" y="3" rx="2"/>
                                            <path d="M3 9h18"/>
                                            <path d="M9 21V9"/>
                                        </svg>
                                    </div>
                                    <h5 class="text-white-50 fw-normal mt-0" title="Constancias de Vacante">Vacantes</h5>
                                    <h3 class="mt-3 mb-1 text-white">{{ $constancias->where('tipo', 'vacante')->count() }}</h3>
                                    <p class="mb-0 text-white-50">
                                        <span class="text-nowrap">Certificados de vacante</span>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6">
                            <div class="card widget-flat bg-warning text-white">
                                <div class="card-body">
                                    <div class="float-end">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="opacity-75">
                                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"/>
                                            <path d="m9 12 2 2 4-4"/>
                                        </svg>
                                    </div>
                                    <h5 class="text-white-50 fw-normal mt-0" title="Constancias Válidas">Válidas</h5>
                                    <h3 class="mt-3 mb-1 text-white">{{ $constancias->count() }}</h3>
                                    <p class="mb-0 text-white-50">
                                        <span class="text-nowrap">Certificados activos</span>
                                    </p>
                                </div>
                            </div>
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
                                            <button type="button"
                                                    onclick="verConstanciaModal('{{ trim($constancia->tipo) == 'estudios' ? route('constancias.estudios.ver', $constancia->id) : route('constancias.vacante.ver', $constancia->id) }}', '{{ ucfirst($constancia->tipo) }}')"
                                                    class="btn btn-sm btn-outline-info waves-effect tooltip-trigger" 
                                                    title="Ver constancia">
                                                <!-- Icono de Lucide: Eye -->
                                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-eye"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                                            </button>
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
                <h5 class="modal-title text-white d-flex align-items-center" id="generarConstanciaModalLabel">
                    <!-- Icono de Lucide: FileText -->
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-file-text me-2"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M10 9h6"/><path d="M10 13h6"/><path d="M10 17h4"/></svg>
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
                        <div class="col-12 mb-3">
                            <label for="ciclo-select" class="form-label fw-semibold">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="inline-block me-1"><path d="M21 7.5V6a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h7.5"/><path d="M16 2v4"/><path d="M8 2v4"/><path d="M3 10h5.5M12 20a4 4 0 0 0 4-4"/><path d="M16 12v-4"/></svg>
                                Ciclo Académico
                            </label>
                            <select id="ciclo-select" class="form-select">
                                <option value="">Todos los ciclos...</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="inline-block me-1"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><polyline points="17 11 19 13 23 9"/></svg>
                            Buscar y Seleccionar Inscripción
                        </label>
                        
                        <!-- Buscador -->
                        <div class="search-wrapper mb-3">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="search-icon"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                            <input type="text" id="search-inscripcion" class="form-control" placeholder="Buscar por nombre, DNI, carrera o ciclo...">
                        </div>
                        
                        <!-- Lista de inscripciones -->
                        <div id="inscripciones-list" style="max-height: 400px; overflow-y: auto;">
                            <p class="text-muted text-center py-4">Selecciona un ciclo o busca para ver las inscripciones</p>
                        </div>
                        
                        <!-- Hidden input para almacenar el ID seleccionado -->
                        <input type="hidden" id="inscripcion-select" value="">
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
    
    // Función de notificación con SweetAlert2
    function mostrarNotificacion(tipo, mensaje) {
        const iconos = {
            'success': 'success',
            'error': 'error',
            'warning': 'warning',
            'info': 'info'
        };
        
        const titulos = {
            'success': '¡Éxito!',
            'error': 'Error',
            'warning': 'Atención',
            'info': 'Información'
        };
        
        Swal.fire({
            icon: iconos[tipo] || 'info',
            title: titulos[tipo] || 'Notificación',
            text: mensaje,
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });
    }


    $(document).ready(function() {
        // Inicializar DataTable
        $('#constancias-table').DataTable({
            responsive: true,
            language: {
                url: '{{ asset("assets/libs/datatables.net/i18n/Spanish.json") }}'
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
                let cicloActivoId = null;
                
                if (response.length > 0) {
                    response.forEach(function(ciclo) {
                        const cicloInfo = `${ciclo.nombre} (${ciclo.fecha_inicio} - ${ciclo.fecha_fin})`;
                        options += `<option value="${ciclo.id}">${cicloInfo}</option>`;
                        
                        // Detectar el ciclo activo (el que está en curso)
                        const fechaInicio = new Date(ciclo.fecha_inicio);
                        const fechaFin = new Date(ciclo.fecha_fin);
                        const hoy = new Date();
                        
                        if (hoy >= fechaInicio && hoy <= fechaFin) {
                            cicloActivoId = ciclo.id;
                        }
                    });
                    
                    // Si no hay ciclo activo, seleccionar el más reciente
                    if (!cicloActivoId && response.length > 0) {
                        cicloActivoId = response[0].id;
                    }
                }
                
                $('#ciclo-select').html(options).prop('disabled', false);
                
                // Seleccionar automáticamente el ciclo activo
                if (cicloActivoId) {
                    $('#ciclo-select').val(cicloActivoId);
                    // Cargar inscripciones del ciclo activo
                    cargarInscripciones(tipoSeleccionado, null, cicloActivoId);
                }
            },
            error: function(xhr) {
                console.error('Error al cargar ciclos:', xhr.responseText);
                $('#ciclo-select').html('<option value="">Error al cargar ciclos</option>').prop('disabled', false);
                mostrarNotificacion('error', 'No se pudieron cargar los ciclos');
            }
        });
    }

    // Variables globales para inscripciones
    let todasLasInscripciones = [];
    let inscripcionSeleccionada = null;

    function cargarInscripciones(tipo, dni = null, cicloId = null) {
        $('#inscripciones-list').html('<p class="text-muted text-center py-4"><div class="spinner-border spinner-border-sm me-2"></div>Cargando inscripciones...</p>');
        
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
                todasLasInscripciones = response;
                renderizarInscripciones(response);
            },
            error: function(xhr) {
                console.error('Error al cargar inscripciones:', xhr.responseText);
                let errorMsg = 'Error al cargar inscripciones';
                if (xhr.status === 419) {
                    errorMsg = 'Sesión expirada. Recarga la página.';
                } else if (xhr.status === 403) {
                    errorMsg = 'No tienes permisos para ver estas inscripciones.';
                }
                $('#inscripciones-list').html(`<p class="text-danger text-center py-4">${errorMsg}</p>`);
                mostrarNotificacion('error', errorMsg);
            }
        });
    }

    function renderizarInscripciones(inscripciones) {
        if (inscripciones.length === 0) {
            $('#inscripciones-list').html('<p class="text-muted text-center py-4">No se encontraron inscripciones</p>');
            return;
        }

        let html = '';
        inscripciones.forEach(function(inscripcion) {
            const isSelected = inscripcionSeleccionada === inscripcion.id ? 'selected' : '';
            const dni = inscripcion.estudiante.numero_documento || inscripcion.estudiante.dni || 'N/A';
            html += `
                <div class="inscripcion-item ${isSelected}" data-id="${inscripcion.id}" onclick="seleccionarInscripcion(${inscripcion.id})">
                    <div class="student-name">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        ${inscripcion.estudiante.nombre} ${inscripcion.estudiante.apellido_paterno} ${inscripcion.estudiante.apellido_materno}
                    </div>
                    <div class="student-details">
                        <span class="me-3">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1"><rect x="1" y="4" width="22" height="16" rx="2"/><path d="M1 10h22"/></svg>
                            DNI: ${dni}
                        </span>
                        <span class="me-3">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
                            ${inscripcion.carrera.nombre}
                        </span>
                        <span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="me-1"><path d="M21 7.5V6a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h7.5"/><path d="M16 2v4"/><path d="M8 2v4"/><path d="M3 10h5.5"/></svg>
                            ${inscripcion.ciclo.nombre}
                        </span>
                    </div>
                </div>
            `;
        });
        $('#inscripciones-list').html(html);
    }

    function seleccionarInscripcion(id) {
        inscripcionSeleccionada = id;
        $('#inscripcion-select').val(id);
        
        // Actualizar visualización
        $('.inscripcion-item').removeClass('selected');
        $(`.inscripcion-item[data-id="${id}"]`).addClass('selected');
    }

    // Búsqueda en tiempo real
    $('#search-inscripcion').on('input', function() {
        const searchTerm = $(this).val().toLowerCase().trim();
        
        if (searchTerm === '') {
            renderizarInscripciones(todasLasInscripciones);
            return;
        }

        const filtradas = todasLasInscripciones.filter(function(inscripcion) {
            const nombreCompleto = `${inscripcion.estudiante.nombre} ${inscripcion.estudiante.apellido_paterno} ${inscripcion.estudiante.apellido_materno}`.toLowerCase();
            const dni = (inscripcion.estudiante.numero_documento || inscripcion.estudiante.dni || '').toLowerCase();
            const carrera = inscripcion.carrera.nombre.toLowerCase();
            const ciclo = inscripcion.ciclo.nombre.toLowerCase();
            
            return nombreCompleto.includes(searchTerm) || 
                   dni.includes(searchTerm) || 
                   carrera.includes(searchTerm) || 
                   ciclo.includes(searchTerm);
        });

        renderizarInscripciones(filtradas);
    });

    $('#ciclo-select').change(function() {
        const cicloId = $(this).val();
        cargarInscripciones(tipoSeleccionado, null, cicloId);
    });


    async function generarConstancia() {
        const inscripcionId = $('#inscripcion-select').val();

        if (!inscripcionId) {
            Swal.fire({
                icon: 'warning',
                title: 'Atención',
                text: 'Por favor selecciona una inscripción',
                confirmButtonColor: '#3085d6'
            });
            return;
        }

        let route = '';
        let tipoTexto = '';
        if (tipoSeleccionado === 'estudios') {
            route = '{{ route("constancias.estudios.generar", ":id") }}'.replace(':id', inscripcionId);
            tipoTexto = 'Estudios';
        } else {
            route = '{{ route("constancias.vacante.generar", ":id") }}'.replace(':id', inscripcionId);
            tipoTexto = 'Vacante';
        }

        // Mostrar loading
        Swal.fire({
            title: 'Generando constancia...',
            html: 'Por favor espera un momento',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        // Esperar un momento para que se genere
        await new Promise(resolve => setTimeout(resolve, 1000));

        // Abrir PDF en ventana popup
        const pdfWindow = window.open(route, 'Constancia', 'width=900,height=700,scrollbars=yes,resizable=yes');
        
        // Cerrar loading
        Swal.close();

        // Mostrar opciones
        const result = await Swal.fire({
            title: `Constancia de ${tipoTexto} Generada`,
            html: `
                <div class="text-center">
                    <p class="mb-3">Tu constancia ha sido generada exitosamente y se ha abierto en una nueva ventana.</p>
                    <p class="text-muted">¿Qué deseas hacer?</p>
                </div>
            `,
            icon: 'success',
            showCancelButton: true,
            showConfirmButton: true,
            confirmButtonText: '<i class="mdi mdi-download"></i> Descargar',
            cancelButtonText: '<i class="mdi mdi-printer"></i> Imprimir',
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#28a745',
            showDenyButton: true,
            denyButtonText: '<i class="mdi mdi-close"></i> Cerrar',
            denyButtonColor: '#6c757d'
        });

        if (result.isConfirmed) {
            // Descargar PDF
            const link = document.createElement('a');
            link.href = route;
            link.download = `constancia_${tipoSeleccionado}.pdf`;
            link.click();
            
            Swal.fire({
                icon: 'success',
                title: '¡Descargado!',
                text: 'La constancia se ha descargado correctamente',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });
            
            // Recargar tabla después de descargar
            setTimeout(() => location.reload(), 1000);
        } else if (result.dismiss === Swal.DismissReason.cancel) {
            // Imprimir PDF
            if (pdfWindow && !pdfWindow.closed) {
                pdfWindow.print();
            } else {
                window.open(route, '_blank').print();
            }
            
            // Recargar tabla después de imprimir
            setTimeout(() => location.reload(), 1000);
        } else if (result.isDenied) {
            // Solo cerrar, recargar tabla
            if (pdfWindow && !pdfWindow.closed) {
                pdfWindow.close();
            }
            setTimeout(() => location.reload(), 500);
        }

        // Cerrar modal de generación
        $('#generarConstanciaModal').modal('hide');
    }

    // Función para ver constancia existente en ventana popup
    async function verConstanciaModal(url, tipo) {
        // Abrir PDF en ventana popup
        const pdfWindow = window.open(url, 'Constancia', 'width=900,height=700,scrollbars=yes,resizable=yes');
        
        // Mostrar opciones
        const result = await Swal.fire({
            title: `Constancia de ${tipo}`,
            html: `
                <div class="text-center">
                    <p class="mb-3">La constancia se ha abierto en una nueva ventana.</p>
                    <p class="text-muted">¿Qué deseas hacer?</p>
                </div>
            `,
            icon: 'info',
            showCancelButton: true,
            showConfirmButton: true,
            confirmButtonText: '<i class="mdi mdi-download"></i> Descargar',
            cancelButtonText: '<i class="mdi mdi-printer"></i> Imprimir',
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#28a745',
            showDenyButton: true,
            denyButtonText: '<i class="mdi mdi-close"></i> Cerrar',
            denyButtonColor: '#6c757d'
        });

        if (result.isConfirmed) {
            // Descargar PDF
            const link = document.createElement('a');
            link.href = url;
            link.download = `constancia_${tipo.toLowerCase()}.pdf`;
            link.click();
            
            Swal.fire({
                icon: 'success',
                title: '¡Descargado!',
                text: 'La constancia se ha descargado correctamente',
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });
        } else if (result.dismiss === Swal.DismissReason.cancel) {
            // Imprimir PDF
            if (pdfWindow && !pdfWindow.closed) {
                pdfWindow.print();
            } else {
                window.open(url, '_blank').print();
            }
        } else if (result.isDenied) {
            // Cerrar ventana
            if (pdfWindow && !pdfWindow.closed) {
                pdfWindow.close();
            }
        }
    }



    async function confirmarEliminar(constanciaId) {
        const result = await Swal.fire({
            title: '¿Estás seguro?',
            text: 'Esta acción no se puede deshacer. La constancia será eliminada permanentemente.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: '<i class="mdi mdi-delete"></i> Sí, eliminar',
            cancelButtonText: '<i class="mdi mdi-close"></i> Cancelar',
            reverseButtons: true
        });
        
        if (result.isConfirmed) {
            eliminarConstancia(constanciaId);
        }
    }

    function eliminarConstancia(constanciaId) {
        // Mostrar loading
        Swal.fire({
            title: 'Eliminando...',
            html: 'Por favor espera',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        $.ajax({
            url: '{{ route("constancias.eliminar", ":id") }}'.replace(':id', constanciaId),
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            success: function(response) {
                Swal.fire({
                    icon: 'success',
                    title: '¡Eliminada!',
                    text: 'La constancia ha sido eliminada correctamente',
                    confirmButtonColor: '#3085d6'
                }).then(() => {
                    location.reload();
                });
            },
            error: function(xhr) {
                console.error('Error al eliminar constancia:', xhr.responseText);
                let errorMsg = 'Error al eliminar la constancia';
                if (xhr.status === 403) {
                    errorMsg = 'No tienes permisos para eliminar esta constancia';
                } else if (xhr.status === 404) {
                    errorMsg = 'Constancia no encontrada';
                }
                
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: errorMsg,
                    confirmButtonColor: '#3085d6'
                });
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

<style>
    /* ===== Estilos Profesionales SOLO para Módulo de Constancias ===== */
    
    /* Mejoras para las tarjetas de estadísticas */
    #constancias-module .widget-flat {
        border-radius: 0.75rem;
        transition: all 0.3s ease;
        border: none;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    }
    
    #constancias-module .widget-flat:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
    }
    
    #constancias-module .widget-flat .card-body {
        position: relative;
        overflow: hidden;
    }
    
    #constancias-module .widget-flat .card-body::before {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 100px;
        height: 100px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
        transform: translate(30%, -30%);
    }
    
    /* Mejoras para la tabla */
    #constancias-table tbody tr {
        transition: all 0.2s ease;
    }
    
    #constancias-table tbody tr:hover {
        background-color: rgba(79, 70, 229, 0.05);
        transform: scale(1.001);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
    }
    
    #constancias-table thead th {
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
        color: #64748b;
        border-bottom: 2px solid #e2e8f0;
    }
    
    /* Botones de acción mejorados */
    #constancias-table .btn-group .btn {
        transition: all 0.2s ease;
        margin: 0 2px;
    }
    
    #constancias-table .btn-group .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
    }
    
    /* Modal mejorado */
    #generarConstanciaModal .modal-header.bg-primary {
        background: linear-gradient(135deg, #4F46E5 0%, #7C3AED 100%) !important;
        border-bottom: none;
        padding: 1.5rem;
    }
    
    #generarConstanciaModal .modal-content {
        border-radius: 1rem;
        border: none;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }
    
    #generarConstanciaModal .modal-body {
        padding: 2rem;
    }
    
    /* Tarjetas de selección en modal */
    #generarConstanciaModal .hover-card {
        transition: all 0.3s ease;
        cursor: pointer;
        border-radius: 0.75rem;
    }
    
    #generarConstanciaModal .hover-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15);
        border-width: 2px !important;
    }
    
    #generarConstanciaModal .hover-card:hover .btn {
        transform: scale(1.05);
    }
    
    /* Animaciones */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    #constancias-module .col-xl-3:nth-child(1) .widget-flat { animation: fadeInUp 0.5s ease-out 0.1s both; }
    #constancias-module .col-xl-3:nth-child(2) .widget-flat { animation: fadeInUp 0.5s ease-out 0.2s both; }
    #constancias-module .col-xl-3:nth-child(3) .widget-flat { animation: fadeInUp 0.5s ease-out 0.3s both; }
    #constancias-module .col-xl-3:nth-child(4) .widget-flat { animation: fadeInUp 0.5s ease-out 0.4s both; }
    
    /* Mejoras para select en modal */
    #generarConstanciaModal .form-select {
        border-radius: 0.5rem;
        border: 1px solid #e2e8f0;
        padding: 0.625rem 1rem;
        transition: all 0.2s ease;
    }
    
    #generarConstanciaModal .form-select:focus {
        border-color: #4F46E5;
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
    }
    
    /* Buscador de inscripciones */
    #search-inscripcion {
        border-radius: 0.5rem;
        border: 1px solid #e2e8f0;
        padding: 0.75rem 1rem;
        padding-left: 2.5rem;
        transition: all 0.2s ease;
        width: 100%;
    }
    
    #search-inscripcion:focus {
        border-color: #4F46E5;
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        outline: none;
    }
    
    .search-wrapper {
        position: relative;
    }
    
    .search-icon {
        position: absolute;
        left: 0.75rem;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        pointer-events: none;
    }
    
    /* Lista de inscripciones mejorada */
    .inscripcion-item {
        padding: 1rem;
        border: 1px solid #e2e8f0;
        border-radius: 0.5rem;
        margin-bottom: 0.75rem;
        cursor: pointer;
        transition: all 0.2s ease;
        background: white;
    }
    
    .inscripcion-item:hover {
        border-color: #4F46E5;
        background-color: rgba(79, 70, 229, 0.05);
        transform: translateX(4px);
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    }
    
    .inscripcion-item.selected {
        border-color: #4F46E5;
        background-color: rgba(79, 70, 229, 0.1);
        border-width: 2px;
    }
    
    .inscripcion-item .student-name {
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 0.25rem;
    }
    
    .inscripcion-item .student-details {
        font-size: 0.875rem;
        color: #64748b;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        #constancias-module .widget-flat {
            margin-bottom: 1rem;
        }
        
        #constancias-table .btn-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        
        #constancias-table .btn-group .btn {
            width: 100%;
            margin: 0;
        }
    }
</style>

@endpush
