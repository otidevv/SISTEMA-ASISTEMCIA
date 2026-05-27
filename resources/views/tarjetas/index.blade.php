@extends('layouts.app')

@section('title', 'Etiquetas de Examen Pre Universitario UNAMAD')

@section('content')
    <!-- start page title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">Distribución y Etiquetas de Examen</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Etiquetas</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <!-- end page title -->

    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <!-- CABECERA DE LA TARJETA MODIFICADA CON EL SELECTOR DE EXAMEN -->
                    <div class="card-header bg-light d-flex justify-content-between align-items-center flex-wrap py-3" style="border-bottom: 1px solid rgba(0,0,0,0.05);">
                        <div class="d-flex align-items-center" style="gap: 8px">
                            <span class="badge bg-primary text-white font-12 py-1 px-2.5 rounded-pill shadow-xs"><i class="fas fa-shield-alt mr-1"></i> CEPRE</span>
                            <span class="badge bg-success text-white font-12 py-1 px-2.5 rounded-pill shadow-xs" title="Ciclo Activo usado para la distribución"><i class="fas fa-calendar-alt mr-1"></i> {{ App\Models\Ciclo::where('es_activo', true)->first()->nombre ?? 'Ciclo Actual' }}</span>
                            <h4 class="card-title text-dark font-weight-bold mb-0" style="font-size: 17px; letter-spacing: -0.2px; margin-left: 8px;">Etiquetas y Distribución de Examen</h4>
                        </div>
                        <div class="d-flex align-items-center" style="gap: 8px">
                            <label for="select-examen" class="mb-0 font-weight-bold text-gray-700" style="font-size: 13px;"><i class="fas fa-clipboard-list mr-1"></i> Examen Seleccionado:</label>
                            <select id="select-examen" class="form-control form-control-sm shadow-xs" style="width: 180px; font-weight: 700; border-radius: 6px; border-color: #cbd5e1;">
                                <option value="1">Primer Examen</option>
                                <option value="2">Segundo Examen</option>
                                <option value="3">Tercer Examen</option>
                            </select>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- BLOQUE DE BOTONES DE ACCIÓN -->
                        <div class="no-print mb-4 d-flex justify-content-between align-items-center flex-wrap" style="gap: 15px; background: rgba(0,0,0,0.02); padding: 12px 18px; border-radius: 12px; border: 1px solid rgba(0,0,0,0.04);">
                            <div class="d-flex flex-wrap align-items-center" style="gap: 12px">
                                <button id="load-btn" class="btn btn-primary btn-action-premium" disabled>
                                    <i class="fas fa-circle-notch fa-spin mr-1.5"></i> Cargando...
                                </button>
                                <div class="custom-tab-nav shadow-xs">
                                    <button type="button" class="btn-tab active" id="view-cards-btn">
                                        <i class="fas fa-id-card"></i> Tarjetas
                                    </button>
                                    <button type="button" class="btn-tab" id="view-list-btn">
                                        <i class="fas fa-list"></i> Resumen Aula
                                    </button>
                                    <button type="button" class="btn-tab" id="view-building-btn">
                                        <i class="fas fa-building"></i> Simulación Edificio
                                    </button>
                                </div>
                            </div>
                            
                            <div class="d-flex flex-wrap" style="gap: 8px">
                                <button id="pdf-btn" class="btn btn-danger btn-action-premium" disabled>
                                    <i class="fas fa-file-pdf mr-1"></i> Etiquetas Mesa
                                </button>
                                <button id="gate-pdf-btn" class="btn btn-dark btn-action-premium" disabled>
                                    <i class="fas fa-file-pdf mr-1"></i> Lista Puerta
                                </button>
                                <button id="actas-pdf-btn" class="btn btn-info btn-action-premium" disabled>
                                    <i class="fas fa-file-pdf mr-1"></i> Actas Supervisor
                                </button>
                                <button id="resumen-pdf-btn" class="btn btn-warning btn-action-premium" disabled title="Exportar Resumen de Distribución por Aulas">
                                    <i class="fas fa-file-pdf mr-1"></i> Resumen Aulas
                                </button>
                                <button id="print-btn" class="btn btn-success btn-action-premium" disabled>
                                    <i class="fas fa-print mr-1"></i> Imprimir Pantalla
                                </button>
                            </div>
                        </div>
                        
                        <!-- Contenedor de Estadísticas Generales -->
                        <div id="estadisticas-container" class="mb-4"></div>
                        
                        <!-- Contenedor de Tarjetas -->
                        <div id="tarjetas-container" class="tarjeta-container">
                             <p class="text-muted text-center w-100 mt-4">Cargando datos...</p>
                        </div>

                        <!-- Contenedor de Lista de Distribución -->
                        <div id="distribucion-container" class="d-none">
                            <div class="table-responsive">
                                <div class="text-center mb-4">
                                    <h5 class="font-weight-bold" id="distribucion-titulo">EXAMEN DEL CEPRE-UNAMAD</h5>
                                    <h6 class="font-weight-bold text-muted">DISTRIBUCIÓN DEL NÚMERO CARTILLA DE PREGUNTAS POR AULA, TEMA Y GRUPO:</h6>
                                </div>
                                <table class="table table-bordered table-sm text-center" id="tabla-distribucion">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 10%">AULA</th>
                                            <th style="width: 10%">TEMA</th>
                                            <th style="width: 10%">GRUPO</th>
                                            <th style="width: 10%">CANTIDAD</th>
                                            <th style="width: 60%">DOCENTES RESPONSABLES</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- JS -->
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Contenedor de Simulación de Edificio -->
                        <div id="edificio-container" class="d-none">
                            <div class="alert alert-info border-0 shadow-xs d-flex justify-content-between align-items-center flex-wrap py-2.5 px-3.5 mb-4" style="border-radius: 10px; background-color: rgba(59, 130, 246, 0.1); color: #3b82f6;">
                                <div class="d-flex align-items-center mb-2 mb-md-0" style="font-size: 13.5px; font-weight: 600;">
                                    <i class="fas fa-info-circle mr-2.5 opacity-75" style="font-size: 16px;"></i>
                                    <span>Visualiza la ocupación de las aulas y asigna responsables por rangos de carpetas.</span>
                                </div>
                                <div class="d-flex flex-wrap" style="gap: 8px">
                                    <button class="btn btn-sm btn-info text-white font-weight-semibold shadow-xs" onclick="abrirModalReporte()" style="border-radius: 6px; padding: 6px 12px; font-weight: 700;">
                                        <i class="fas fa-chart-bar mr-1.5"></i> Ver Reporte General
                                    </button>
                                    <button class="btn btn-sm btn-primary font-weight-semibold shadow-xs" onclick="abrirModalGestionAulas()" style="border-radius: 6px; padding: 6px 12px; font-weight: 700;">
                                        <i class="fas fa-cog mr-1.5"></i> Gestionar Aulas
                                    </button>
                                    <button class="btn btn-sm btn-warning text-dark font-weight-bold shadow-xs" id="btn-distribuir" style="border-radius: 6px; padding: 6px 12px; font-weight: 700;">
                                        <i class="fas fa-sitemap mr-1.5"></i> Generar Distribución de Estudiantes
                                    </button>
                                </div>
                            </div>
                            <div id="edificio-stats-container" class="mb-4"></div>
                            <div id="edificio-grid">
                                <!-- JS -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL DETALLE AULA (SIMULACIÓN CARPETAS) -->
    <div class="modal fade" id="modalDetalleAula" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header text-white" style="background: linear-gradient(135deg, #0A3C59 0%, #15557a 100%);">
                    <h5 class="modal-title font-weight-bold text-white d-flex align-items-center" id="modalDetalleTitulo">
                        <i class="fas fa-door-open mr-2"></i> Aula X
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body bg-light">
                    <div class="d-flex justify-content-between mb-4">
                        <div class="pizarra text-white text-center py-2.5 rounded w-100 mx-5 font-weight-bold shadow-sm">
                            <i class="fas fa-chalkboard mr-2"></i> PIZARRA / DIRECCIÓN DEL AULA
                        </div>
                    </div>
                    <div id="grid-carpetas" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 15px; padding: 20px;">
                        <!-- JS -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL GESTIÓN AULAS -->
    <div class="modal fade" id="modalGestionAulas" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Gestionar Aulas y Pisos</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <ul class="nav nav-tabs mb-3" id="gestionTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="individual-tab" data-bs-toggle="tab" data-bs-target="#individual" type="button" role="tab">Individual</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="masivo-tab" data-bs-toggle="tab" data-bs-target="#masivo" type="button" role="tab">Agregar Piso Completo</button>
                        </li>
                    </ul>

                    <div class="tab-content" id="gestionTabContent">
                        <div class="tab-pane fade show active" id="individual" role="tabpanel">
                            <form id="form-aula" class="mb-4 border p-3 rounded bg-light">
                                <h6 class="font-weight-bold mb-3 text-dark">Agregar / Editar Aula</h6>
                                <div class="row">
                                    <input type="hidden" id="aula-id" value="">
                                    <div class="col-md-4 mb-2">
                                        <input type="text" class="form-control" id="aula-nombre" placeholder="Nombre (ej. 201)" required>
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <input type="number" class="form-control" id="aula-piso" placeholder="Piso" required>
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <input type="number" class="form-control" id="aula-capacidad" placeholder="Capacidad" required>
                                    </div>
                                    <div class="col-md-2 mb-2">
                                        <button type="submit" class="btn btn-success w-100">Guardar</button>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <div class="tab-pane fade" id="masivo" role="tabpanel">
                            <form id="form-piso" class="mb-4 border p-3 rounded bg-light">
                                <h6 class="font-weight-bold mb-3 text-dark">Generar Piso Completo</h6>
                                <div class="alert alert-info small py-2">
                                    Esto creará múltiples aulas automáticamente. Ej: Piso 3, Inicio 1, Cantidad 5 => Creará 301, 302, 303, 304, 305.
                                </div>
                                <div class="row">
                                    <div class="col-md-3 mb-2">
                                        <label class="small text-dark">N° Piso</label>
                                        <input type="number" class="form-control" id="piso-numero" placeholder="Ej. 3" required>
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <label class="small text-dark">Iniciar en (01)</label>
                                        <input type="number" class="form-control" id="piso-inicio" value="1" required>
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <label class="small text-dark">Cant. Aulas</label>
                                        <input type="number" class="form-control" id="piso-cantidad" value="5" required>
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <label class="small text-dark">Capacidad Default</label>
                                        <input type="number" class="form-control" id="piso-capacidad" value="40" required>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary w-100 mt-2">Generar Piso</button>
                            </form>
                        </div>
                    </div>
                    
                    <hr>
                    <h6 class="font-weight-bold text-dark">Aulas Existentes</h6>
                    <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                        <table class="table table-sm table-striped">
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>Piso</th>
                                    <th>Capacidad</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="tbody-gestion-aulas">
                                <!-- JS -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL REPORTE GENERAL EDIFICIO -->
    <div class="modal fade" id="modalReporteEdificio" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header text-white" style="background: linear-gradient(135deg, #0A3C59 0%, #15557a 100%); border-bottom: 1px solid rgba(255,255,255,0.08);">
                    <h5 class="modal-title font-weight-bold text-white d-flex align-items-center" id="modalReporteTitulo">
                        <i class="fas fa-chart-pie mr-2 text-warning"></i> Reporte de Distribución y Capacidad
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body bg-light">
                    <!-- Detalle de Estudiantes Pendientes -->
                    <div id="modal-edificio-faltantes-container"></div>
                    
                    <!-- Estadísticas Generales en el Modal -->
                    <div id="modal-edificio-stats-cards" class="mb-4"></div>
                    
                    <!-- Control Detallado por Pisos -->
                    <h6 class="font-weight-bold text-dark mb-3"><i class="fas fa-layer-group mr-1.5 text-primary"></i> Resumen de Ocupación por Piso</h6>
                    <div class="table-responsive bg-white p-3 rounded shadow-sm border">
                        <table class="table table-sm table-striped table-hover text-center mb-0">
                            <thead class="table-light text-dark font-weight-bold">
                                <tr>
                                    <th>Piso</th>
                                    <th>Aulas Habilitadas</th>
                                    <th>Capacidad Total</th>
                                    <th>Alumnos Asignados</th>
                                    <th>Asientos Libres</th>
                                    <th>% Ocupación</th>
                                </tr>
                            </thead>
                            <tbody id="tbody-reporte-pisos">
                                <!-- JS -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('css')
    <!-- FontAwesome para iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <style>
        /* Variables y configuración de color principal de Shreyu/CEPRE */
        :root {
            --color-unama-blue: #0A3C59; /* Azul Oscuro Institucional */
            --color-tema-p: #2563eb; /* Azul premium */
            --color-tema-q: #10b981; /* Verde premium */
            --color-tema-r: #f59e0b; /* Amarillo/Naranja premium */
            --cepre-navy: #1a237e;
            --cepre-light-gray: #f8f9fa;
            --tarjeta-bg: #ffffff;
            --tarjeta-text: #1f2937;
            --tarjeta-subtext: #4b5563;
            --tarjeta-border: rgba(0, 0, 0, 0.08);
            --tarjeta-input-bg: #ffffff;
            --tarjeta-input-border: #cbd5e1;
            --tarjeta-header-gradient-1: #0A3C59;
            --tarjeta-header-gradient-2: #15557a;
            --tarjeta-clave-bg: #fafafa;
            --tarjeta-detail-bg: #f3f4f6;
        }

        /* Variables para el modo oscuro compatible con Shreyu */
        html[data-bs-theme="dark"] {
            --tarjeta-bg: #1e293b;
            --tarjeta-text: #f1f5f9;
            --tarjeta-subtext: #94a3b8;
            --tarjeta-border: rgba(255, 255, 255, 0.08);
            --tarjeta-input-bg: #0f172a;
            --tarjeta-input-border: #334155;
            --tarjeta-header-gradient-1: #0f172a;
            --tarjeta-header-gradient-2: #1e293b;
            --tarjeta-clave-bg: #0f172a;
            --tarjeta-detail-bg: #0f172a;
        }

        /* ------------------------------------------------ */
        /* Estilos del CONTENEDOR - Adaptado a Rejilla Grid */
        /* ------------------------------------------------ */

        .tarjeta-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 24px;
            padding: 15px 5px;
        }

        /* ------------------------------------------------ */
        /* Estilos de la TARJETA (Credencial Premium)       */
        /* ------------------------------------------------ */

        .tarjeta {
            background-color: var(--tarjeta-bg); 
            position: relative;
            font-size: 10px;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.025);
            border: 1px solid var(--tarjeta-border);
            display: flex;
            min-height: 5.8cm;
            height: auto;
            width: 100%;
            max-width: 100%;
            transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.3s ease;
        }
        .tarjeta:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 20px -8px rgba(0,0,0,0.15);
        }

        .franja-tema {
            width: 8px;
            height: 100%;
            z-index: 10;
            flex-shrink: 0;
        }
        .tarjeta-p .franja-tema { background: linear-gradient(to bottom, #3b82f6, #1d4ed8); }
        .tarjeta-q .franja-tema { background: linear-gradient(to bottom, #10b981, #047857); }
        .tarjeta-r .franja-tema { background: linear-gradient(to bottom, #f59e0b, #b45309); }

        .contenido-principal {
            display: flex;
            flex-direction: column;
            width: 100%;
            min-height: 100%;
            position: relative;
            z-index: 10;
            background-color: var(--tarjeta-bg);
        }

        .inhabilitado-card {
            border-color: #ef4444 !important;
            box-shadow: 0 0 0 1px #ef4444 !important;
        }

        /* Cabecera */
        .header-institucional {
            padding: 6px 12px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: linear-gradient(135deg, var(--tarjeta-header-gradient-1) 0%, var(--tarjeta-header-gradient-2) 100%);
            color: white;
            flex-shrink: 0;
            border-bottom: 1px solid rgba(255,255,255,0.08);
        }

        /* Ubicación Clave */
        .ubicacion-clave {
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            padding: 8px 12px;
            background-color: var(--tarjeta-clave-bg);
        }
        .ubicacion-clave .aula-code {
            text-align: center;
            flex: 1;
        }
        .ubicacion-clave .aula-code span {
            color: var(--tarjeta-subtext);
            font-weight: 800;
            font-size: 8px;
            display: block;
            line-height: 1;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 2px;
        }
        .ubicacion-clave .aula-code .aula {
            font-weight: 900;
            font-size: 34px;
            line-height: 1;
            color: #dc2626;
            text-shadow: 0 1px 2px rgba(0,0,0,0.05);
        }
        .ubicacion-clave .aula-code .codigo {
            font-weight: 900;
            font-size: 22px;
            line-height: 1;
            color: var(--tarjeta-text);
            letter-spacing: 0.5px;
        }
        .ubicacion-clave .separator {
            border-left: 1px solid var(--tarjeta-border);
            height: 80%;
            padding-left: 12px;
        }

        /* Identificación (Foto, Nombre, Carrera) */
        .identificacion-detalle {
            width: 100%;
            background-color: var(--tarjeta-detail-bg);
            padding: 8px 12px;
            color: var(--tarjeta-subtext);
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            border-top: 1px solid var(--tarjeta-border);
        }
        .identificacion-fila {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            margin-bottom: 6px;
        }

        /* Foto */
        .identificacion-fila .foto-container {
            flex-shrink: 0;
        }
        .identificacion-fila .foto-container img {
            width: 65px;
            height: 65px;
            object-fit: cover;
            border-radius: 8px;
            border: 2px solid var(--tarjeta-bg);
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -1px rgba(0,0,0,0.06);
        }
        
        /* Nombre y Carrera */
        .identificacion-fila .datos-postulante {
            flex-grow: 1;
            text-align: left;
            line-height: 1.2;
            min-width: 0; /* Permite truncar adecuadamente en Flexbox */
        }
        .identificacion-fila .datos-postulante span {
            color: var(--tarjeta-subtext);
            font-size: 7.5px;
            display: block;
            text-transform: uppercase;
            font-weight: 700;
            letter-spacing: 0.3px;
        }
        
        /* Solución de Recorte de Texto */
        .nombre-postulante {
            font-weight: 800;
            font-size: 11px;
            line-height: 1.3; 
            color: var(--tarjeta-text);
            margin-top: 1px;
            margin-bottom: 3px;
            display: -webkit-box;
            -webkit-line-clamp: 3; /* Aumentamos a 3 líneas */
            -webkit-box-orient: vertical;
            overflow: hidden;
            word-break: break-word; /* Romper palabras largas si es necesario */
        }
        
        .carrera-postulante {
            font-weight: 700; 
            font-size: 9.5px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            color: #2563eb; 
            margin-bottom: 0;
        }
        html[data-bs-theme="dark"] .carrera-postulante {
            color: #60a5fa;
        }

        /* Footer */
        .footer-grupo-tema {
            font-size: 8.5px;
            font-weight: 700;
            padding-top: 6px;
            border-top: 1px solid var(--tarjeta-border);
            text-align: center;
            color: var(--tarjeta-subtext);
            margin-top: auto;
        }
        .footer-grupo-tema strong {
            color: var(--tarjeta-text);
        }

        /* Estilo de Tarjeta Inhabilitada */
        .tarjeta.inhabilitado-card {
            border-color: #fca5a5 !important;
        }
        .tarjeta.inhabilitado-card::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(239, 68, 68, 0.03);
            pointer-events: none;
            z-index: 4;
        }

        /* ------------------------------------------------ */
        /* Estilos Premium para Tab Switcher y Botones      */
        /* ------------------------------------------------ */

        .custom-tab-nav {
            background-color: rgba(0,0,0,0.05);
            padding: 4px;
            border-radius: 30px;
            display: inline-flex;
            gap: 4px;
            border: 1px solid rgba(0,0,0,0.05);
        }
        html[data-bs-theme="dark"] .custom-tab-nav {
            background-color: rgba(255,255,255,0.05);
        }
        .custom-tab-nav .btn-tab {
            border: none;
            background: transparent;
            color: var(--tarjeta-subtext);
            font-size: 12.5px;
            font-weight: 700;
            padding: 7px 16px;
            border-radius: 20px;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .custom-tab-nav .btn-tab:hover {
            color: var(--cepre-navy);
        }
        html[data-bs-theme="dark"] .custom-tab-nav .btn-tab:hover {
            color: #ffffff;
        }
        .custom-tab-nav .btn-tab.active {
            background-color: var(--tarjeta-bg);
            color: var(--cepre-navy);
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.08), 0 2px 4px -1px rgba(0,0,0,0.04);
        }
        html[data-bs-theme="dark"] .custom-tab-nav .btn-tab.active {
            color: #60a5fa;
        }

        .btn-action-premium {
            font-weight: 700 !important;
            border-radius: 8px !important;
            padding: 8px 16px !important;
            font-size: 13px !important;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 6px !important;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05) !important;
        }
        .btn-action-premium:not(:disabled):hover {
            transform: translateY(-2px) !important;
            box-shadow: 0 6px 12px rgba(0,0,0,0.1) !important;
        }
        .btn-action-premium:not(:disabled):active {
            transform: translateY(0) !important;
        }

        /* ------------------------------------------------ */
        /* Estilos del Edificio y Aulas (Simulación)        */
        /* ------------------------------------------------ */

        .piso-row {
            background-color: var(--tarjeta-detail-bg);
            border: 1px solid var(--tarjeta-border);
            border-radius: 12px;
            padding: 18px;
            box-shadow: inset 0 2px 4px rgba(0,0,0,0.01);
            margin-bottom: 25px;
        }
        .piso-label {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%) !important;
            border-radius: 8px !important;
            padding: 10px 18px !important;
            font-size: 13.5px;
            font-weight: 800;
            letter-spacing: 0.5px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        .aula-card {
            background-color: var(--tarjeta-bg);
            border-radius: 12px !important;
            overflow: hidden;
            border: 1px solid var(--tarjeta-border) !important;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02) !important;
            transition: all 0.25s ease;
        }
        .aula-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 15px -3px rgba(0,0,0,0.08), 0 4px 6px -2px rgba(0,0,0,0.03) !important;
        }
        
        .progress-premium {
            background-color: var(--tarjeta-detail-bg);
            border-radius: 20px;
            height: 8px !important;
            overflow: hidden;
        }
        
        /* Clases de ocupación */
        .aula-full {
            border-color: #fca5a5 !important;
        }
        .aula-full .card-header {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%) !important;
            color: #b91c1c !important;
            border-bottom: 1px solid #fca5a5 !important;
        }
        .aula-medium {
            border-color: #fcd34d !important;
        }
        .aula-medium .card-header {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%) !important;
            color: #b45309 !important;
            border-bottom: 1px solid #fcd34d !important;
        }
        .aula-empty {
            border-color: var(--tarjeta-border) !important;
        }
        .aula-empty .card-header {
            background: linear-gradient(135deg, var(--tarjeta-clave-bg) 0%, var(--tarjeta-detail-bg) 100%) !important;
            color: var(--tarjeta-text) !important;
            border-bottom: 1px solid var(--tarjeta-border) !important;
        }

        .bg-gradient-p { background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%) !important; color: white !important; }
        .bg-gradient-q { background: linear-gradient(135deg, #10b981 0%, #047857 100%) !important; color: white !important; }
        .bg-gradient-r { background: linear-gradient(135deg, #f59e0b 0%, #b45309 100%) !important; color: white !important; }

        .pizarra {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%) !important;
            color: #e2e8f0 !important;
            font-size: 13px !important;
            letter-spacing: 1px !important;
            border-bottom: 4px solid #cbd5e1 !important;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1) !important;
            border-radius: 6px !important;
            text-transform: uppercase;
        }

        .bg-danger-light {
            background-color: rgba(239, 68, 68, 0.15) !important;
        }

        .btn-xs {
            padding: 2px 6px;
            font-size: 10px;
            line-height: 1.5;
            border-radius: 4px;
        }

        .shadow-xs {
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        }

        /* ------------------------------------------------ */
        /* Arquitectura Visual del Edificio                 */
        /* ------------------------------------------------ */
        .edificio-container-visual {
            max-width: 100%;
            margin: 0 auto;
            position: relative;
        }
        .edificio-roof {
            height: 40px;
            background: linear-gradient(to bottom, #334155, #1e293b);
            clip-path: polygon(5% 0%, 95% 0%, 100% 100%, 0% 100%);
            margin: 0 auto;
            width: 100%;
            border-bottom: 4px solid #0f172a;
            position: relative;
            z-index: 2;
        }
        .edificio-roof::after {
            content: 'SEDE DE EVALUACIÓN';
            position: absolute;
            bottom: 5px;
            left: 0;
            right: 0;
            text-align: center;
            color: rgba(255,255,255,0.7);
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 4px;
        }
        .edificio-wrapper {
            background: #e2e8f0;
            padding: 25px 20px;
            border: 8px solid #94a3b8;
            border-bottom: 15px solid #475569;
            box-shadow: 0 15px 35px rgba(0,0,0,0.15), inset 0 0 20px rgba(0,0,0,0.05);
            position: relative;
        }
        html[data-bs-theme="dark"] .edificio-wrapper {
            background: #1e293b;
            border-color: #334155;
            border-bottom-color: #0f172a;
        }
        
        .piso-row {
            background-color: var(--tarjeta-detail-bg);
            border: 2px solid var(--tarjeta-border);
            border-radius: 0; /* Edificios rectos */
            padding: 20px;
            box-shadow: inset 0 2px 8px rgba(0,0,0,0.03);
            margin-bottom: 15px;
            position: relative;
        }
        .piso-row::before {
            content: '';
            position: absolute;
            left: 0; right: 0; bottom: -15px;
            height: 15px;
            background: linear-gradient(90deg, #94a3b8 0%, #cbd5e1 50%, #94a3b8 100%);
            z-index: 1;
        }
        html[data-bs-theme="dark"] .piso-row::before {
            background: linear-gradient(90deg, #334155 0%, #475569 50%, #334155 100%);
        }

        /* Media Query para impresión */
        @media print {
            .no-print { display: none !important; }
            
            body.print-cards .tarjeta-container {
                column-count: unset;
                column-gap: unset;
                display: flex;
                flex-wrap: wrap;
                justify-content: flex-start; 
                padding: 0;
            }

            body.print-cards .tarjeta {
                width: 8.5cm !important;
                height: 5.5cm !important;
                min-height: 5.5cm !important;
                page-break-inside: avoid;
                margin: 0.15cm; 
                box-shadow: none !important;
                border: 1px solid #aaa !important;
                background-color: #fff !important;
                color: #000 !important;
            }
            
            body.print-cards #distribucion-container {
                display: none !important;
            }

            body.print-list #tarjetas-container {
                display: none !important;
            }
            
            body.print-list #distribucion-container {
                display: block !important;
                width: 100%;
            }
            
            body.print-list .table {
                width: 100%;
                border-collapse: collapse;
            }
            
            body.print-list .table th, 
            body.print-list .table td {
                border: 1px solid #000 !important;
                padding: 8px;
                color: #000;
            }

            .franja-tema {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            body { margin: 0; padding: 0; background-color: #fff; }
        }
    </style>
@endpush

@push('js')
    <script src="https://unpkg.com/axios/dist/axios.min.js"></script>

    <script>
        // Configurar Axios para CSRF
        const token = document.querySelector('meta[name="csrf-token"]');
        if (token) {
            axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
        }

        const API_URLS = {
            CARGAR_DATOS: '{{ url('api/tarjetas-preuni') }}',
            EXPORTAR_PDF: '{{ url("tarjetas/exportar-pdf") }}',
            EXPORTAR_PUERTA: '{{ url("tarjetas/exportar-puerta") }}',
            EXPORTAR_ACTAS: '{{ url("tarjetas/exportar-actas") }}',
            EXPORTAR_RESUMEN: '{{ url("tarjetas/exportar-resumen") }}',
            EDIFICIO_DATA: '{{ url("api/tarjetas/edificio") }}',
            DISTRIBUIR: '{{ url("api/tarjetas/distribuir-aleatorio") }}',
            GUARDAR_DOCENTE: '{{ url("api/tarjetas/guardar-docente") }}',
            DIVIDIR_SUPERVISOR: '{{ url("api/tarjetas/dividir-supervisor") }}',
            ELIMINAR_SUPERVISOR: '{{ url("api/tarjetas/eliminar-supervisor") }}',
            AULA_DETALLE: '{{ url("api/tarjetas/aula") }}', 
            GUARDAR_AULA: '{{ url("api/tarjetas/aula") }}',
            GUARDAR_PISO: '{{ url("api/tarjetas/piso") }}',
            ELIMINAR_AULA: '{{ url("api/tarjetas/aula") }}' 
        };

        // Estado Global
        let state = {
            postulantes: [],
            edificio: {}, 
            isLoading: false,
            currentView: 'cards',
            filtroEstadistico: 'todos'
        };

        // Instancias globales de modales Bootstrap 5
        let modalDetalleAulaInstance = null;
        let modalGestionAulasInstance = null;
        let modalReporteEdificioInstance = null;

        // Custom Toast Configuration to avoid overlay bugs
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 2000,
            didOpen: (toast) => {
                toast.style.zIndex = '999999';
            }
        });

        function getClaseTema(tema) {
            switch(tema) {
                case 'P': return 'tarjeta-p';
                case 'Q': return 'tarjeta-q';
                case 'R': return 'tarjeta-r';
                default: return 'tarjeta-r';
            }
        }
        
        function crearTarjetaHTML(postulante) {
            const { grupo, tema, codigo, aula, carrera, nombres, foto, inhabilitado, asiento, dni } = postulante;
            const claseTema = getClaseTema(tema);
            const inhabilitadoClass = inhabilitado ? 'inhabilitado-card' : '';

            const watermark = inhabilitado ? `
                <div style="position: absolute; top: 1.8cm; left: 1.8cm; width: calc(100% - 2.2cm); z-index: 20; text-align: center; pointer-events: none;">
                    <div style="color: #dc2626; font-size: 20px; font-weight: 900; border: 4px solid #dc2626; padding: 4px 10px; display: inline-block; transform: rotate(-12deg); background-color: white; border-radius: 4px; box-shadow: 0 4px 10px rgba(0,0,0,0.25);">
                        INHABILITADO
                    </div>
                </div>
            ` : '';

            return `
                <div class="tarjeta ${claseTema} ${inhabilitadoClass} relative" style="position: relative; display: flex; min-height: 6.0cm; width: 100%; border: 1.5px solid #64748b; border-radius: 8px; overflow: hidden; background-color: #ffffff; box-shadow: 0 1px 3px rgba(0,0,0,0.05); font-family: sans-serif;">
                    ${watermark}
                    <div class="franja-tema" style="width: 38px; height: 100%; display: flex; flex-direction: column; justify-content: center; align-items: center; text-align: center; color: white;">
                        <span style="font-size: 7px; font-weight: 800; text-transform: uppercase; display: block; letter-spacing: 0.5px; opacity: 0.9;">TEMA</span>
                        <span style="font-size: 40px; font-weight: 900; line-height: 1; display: block; text-shadow: 1px 1px 2px rgba(0,0,0,0.3);">${tema || 'R'}</span>
                    </div>
                    <div class="contenido-principal" style="width: calc(100% - 38px); display: flex; flex-direction: column; height: 100%;">
                        <!-- 1. HEADER INSTITUCIONAL -->
                        <table style="width: 100%; height: 48px; border-collapse: collapse; border: none; border-bottom: 2px solid #0f172a; background-color: #ffffff; margin-bottom: 2px;">
                            <tr style="border: none;">
                                <td style="width: 44px; vertical-align: middle; text-align: left; border: none; padding: 2px 0 2px 8px;">
                                    <img src="{{ asset('assets/images/logo unamad constancia_optimized.png') }}" style="width: 36px; height: 36px; display: block; border-radius: 50%;" alt="Logo UNAMAD"/> 
                                </td>
                                <td style="vertical-align: middle; text-align: center; border: none; padding: 2px 0;">
                                    <span style="font-size: 8.5px; font-weight: 800; display: block; text-transform: uppercase; color: #0f172a; letter-spacing: 0.1px; line-height: 1.15;">UNIVERSIDAD NACIONAL AMAZÓNICA DE MADRE DE DIOS</span>
                                    <span style="font-size: 12px; font-weight: 900; display: block; color: #1e3a8a; line-height: 1.2; text-transform: uppercase; margin-top: 1px;">CENTRO PRE UNIVERSITARIO</span>
                                    <span style="font-size: 8.5px; font-weight: 800; display: block; color: #475569; line-height: 1.1; margin-top: 1px;">{{ App\Models\Ciclo::where('es_activo', true)->first()->nombre ?? 'CICLO ACTUAL' }}</span>
                                </td>
                                <td style="width: 44px; vertical-align: middle; text-align: right; border: none; padding: 2px 8px 2px 0;">
                                    <img src="{{ asset('assets/images/logo cepre costancia_optimized.png') }}" style="width: 36px; height: 36px; display: block; border-radius: 50%;" alt="Logo CEPRE"/> 
                                </td>
                            </tr>
                        </table>

                        <!-- TABLA DE DATOS -->
                        <table style="width: 100%; border-collapse: collapse; border: none; table-layout: fixed; flex-grow: 1;">
                            <!-- Fila 1: Carrera y Código -->
                            <tr style="border-bottom: 1.5px solid #000000; height: 48px;">
                                <td style="width: 63%; padding: 4px 8px; vertical-align: middle; text-align: left; border: none; overflow: hidden;">
                                    <span style="color: #475569; font-weight: 800; font-size: 7.5px; text-transform: uppercase; display: block; margin-bottom: 2px; letter-spacing: 0.3px;">CARRERA PROFESIONAL</span>
                                    <div style="font-weight: 800; font-size: 10.5px; line-height: 1.2; color: #000000; text-transform: uppercase; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="${carrera || 'SIN CARRERA'}">${carrera || 'SIN CARRERA'}</div>
                                </td>
                                <td style="width: 37%; padding: 4px 8px; vertical-align: middle; text-align: center; border-left: 1.5px solid #000000; border-top: none; border-bottom: none; border-right: none;">
                                    <span style="color: #475569; font-weight: 800; font-size: 7.5px; text-transform: uppercase; display: block; margin-bottom: 1px; letter-spacing: 0.3px;">CÓDIGO POSTULANTE</span>
                                    <div style="font-weight: 900; font-size: 18px; color: #000000; letter-spacing: 0.5px;">${codigo || '---'}</div>
                                </td>
                            </tr>
                            
                            <!-- Fila 2: Foto y Datos -->
                            <tr style="border-bottom: 1.5px solid #000000; height: 108px;">
                                <td colspan="2" style="padding: 6px 8px; vertical-align: middle; border: none; background-color: #f8fafc;">
                                    <table style="width: 100%; border-collapse: collapse; border: none;">
                                        <tr style="border: none;">
                                            <td style="width: 72px; vertical-align: middle; text-align: left; border: none; padding: 0;">
                                                <div style="width: 68px; height: 78px; border: 2px solid #000000; border-radius: 4px; overflow: hidden; background-color: #ffffff;">
                                                    <img src="${foto || 'https://cdn.pixabay.com/photo/2015/10/05/22/37/blank-profile-picture-973460_960_720.png'}" onerror="this.onerror=null;this.src='https://cdn.pixabay.com/photo/2015/10/05/22/37/blank-profile-picture-973460_960_720.png';" style="width: 100%; height: 100%; display: block; object-fit: cover; ${inhabilitado ? 'filter: grayscale(100%); opacity: 0.6;' : ''}"/>
                                                </div>
                                            </td>
                                            <td style="padding-left: 10px; vertical-align: middle; text-align: left; border: none;">
                                                <span style="color: #475569; font-size: 8px; text-transform: uppercase; font-weight: 800; display: block; margin-bottom: 2px; letter-spacing: 0.2px;">POSTULANTE</span>
                                                <div style="font-weight: 800; font-size: 12.5px; line-height: 1.25; color: #000000; text-transform: uppercase; margin-bottom: 2px; overflow: hidden; text-overflow: ellipsis; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;" title="${nombres || 'SIN NOMBRE'}">${nombres || 'SIN NOMBRE'}</div>
                                                <div style="font-size: 9.5px; font-weight: 700; color: #475569; margin-bottom: 5px;">DNI: <strong style="color: #000000; font-weight: 800; font-size: 11px;">${dni || '--------'}</strong></div>
                                                
                                                <table style="width: 100%; border-collapse: collapse; border: none;">
                                                    <tr>
                                                        <td style="padding-right: 6px; border: none; width: 50%;">
                                                            <div style="border: 1.5px solid #000000; background-color: #ffffff; padding: 2px 4px; border-radius: 4px; text-align: center;">
                                                                <span style="font-size: 7px; font-weight: 800; display: block; color: #475569; line-height: 1;">AULA</span>
                                                                <span style="font-size: 11px; font-weight: 900; color: #000000; display: block; margin-top: 1px;">${aula || '---'}</span>
                                                            </div>
                                                        </td>
                                                        <td style="border: none; width: 50%;">
                                                            <div style="border: 1.5px solid #000000; background-color: #0f172a; padding: 2px 4px; border-radius: 4px; text-align: center;">
                                                                <span style="font-size: 7px; font-weight: 800; display: block; color: #cbd5e1; line-height: 1;">N° ASIENTO</span>
                                                                <span style="font-size: 11px; font-weight: 900; color: #ffffff; display: block; margin-top: 1px;">${asiento || '---'}</span>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                            
                            <!-- Fila 3: Footer Grupo y Tema -->
                            <tr style="height: 24px; background-color: #ffffff; border: none;">
                                <td colspan="2" style="text-align: center; vertical-align: middle; font-size: 9.5px; font-weight: 700; color: #0f172a; border: none;">
                                    GRUPO: <strong style="font-weight: 900; font-size: 10.5px;">${grupo || '---'}</strong> &nbsp;&nbsp;|&nbsp;&nbsp; TEMA ASIGNADO: <strong style="font-weight: 900; font-size: 10.5px;">${tema || '---'}</strong>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            `;
        }

        function renderEdificio() {
            const container = document.getElementById('edificio-grid');
            if (!state.edificio.pisos) return;

            let html = `
                <div class="edificio-container-visual"><div class="edificio-roof"></div><div class="edificio-wrapper">
            `;
            
            // Ordenar los pisos de mayor a menor para visualización tipo edificio
            const pisosKeys = Object.keys(state.edificio.pisos).sort((a,b) => parseInt(b) - parseInt(a));

            pisosKeys.forEach(pisoKey => {
                const floorData = state.edificio.pisos[pisoKey];
                const aulasInPiso = floorData.aulas;
                
                const pctOcupado = floorData.capacidad_total > 0 ? Math.round((floorData.estudiantes_asignados / floorData.capacidad_total) * 100) : 0;
                
                html += `
                    <div class="piso-row">
                        <div class="piso-label text-white mb-3 font-weight-bold d-inline-flex align-items-center flex-wrap gap-2 px-3 py-1.5" style="border-radius: 8px; background-color: rgba(15, 23, 42, 0.85); box-shadow: 0 4px 6px rgba(0,0,0,0.15);">
                            <span><i class="fas fa-layer-group mr-1 text-warning"></i> PISO ${pisoKey}</span>
                            <span style="font-size: 11px; font-weight: normal; opacity: 0.85; margin-left: 8px; padding-left: 8px; border-left: 1px solid rgba(255,255,255,0.3);">
                                Capacidad: <strong>${floorData.capacidad_total}</strong> | 
                                Asignados: <strong>${floorData.estudiantes_asignados}</strong> | 
                                Libres: <strong class="text-success">${floorData.capacidad_total - floorData.estudiantes_asignados}</strong>
                            </span>
                            <span class="badge bg-primary ml-2" style="font-size: 9.5px; padding: 3px 6px;">${pctOcupado}% Ocupado</span>
                        </div>
                        <div class="d-flex flex-wrap shadow-xs p-3 rounded" style="gap: 20px; justify-content: center; background-color: rgba(255,255,255,0.05); margin-top: 10px;">
                `;

                aulasInPiso.forEach(aula => {
                    const ocupacionPorc = aula.capacidad > 0 ? Math.round((aula.cantidad_estudiantes / aula.capacidad) * 100) : 0;
                    const stateClass = ocupacionPorc >= 100 ? 'aula-full' : (ocupacionPorc > 50 ? 'aula-medium' : 'aula-empty');
                    const badgeClass = ocupacionPorc >= 100 ? 'bg-danger' : (ocupacionPorc > 50 ? 'bg-warning text-dark' : 'bg-secondary');
                    const progressColor = ocupacionPorc >= 100 ? 'bg-danger' : (ocupacionPorc > 50 ? 'bg-warning' : 'bg-success');
                    
                    let supervisorsHtml = '';
                    if (aula.supervisores && aula.supervisores.length > 0) {
                        aula.supervisores.forEach(sup => {
                            const datalistId = `list-docentes-${sup.id}`;
                            const docenteValue = sup.docente_invitado || sup.docente_nombre || '';
                            
                            const canSplit = (sup.rango_fin - sup.rango_inicio) >= 1;
                            const splitBtn = canSplit ? `
                                <button class="btn btn-xs btn-outline-info ml-1" onclick="dividirSupervisor(${sup.id}, ${sup.rango_inicio}, ${sup.rango_fin})" title="Dividir rango">
                                    <i class="fas fa-cut"></i>
                                </button>
                            ` : '';

                            const canDelete = aula.supervisores.length > 1;
                            const deleteBtn = canDelete ? `
                                <button class="btn btn-xs btn-outline-danger ml-1" onclick="eliminarSupervisor(${sup.id})" title="Fusionar/Quitar supervisor">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            ` : '';

                            supervisorsHtml += `
                                <div class="border p-2 mb-2 rounded bg-body text-dark small shadow-xs" style="border-radius: 8px !important; border-color: var(--tarjeta-border) !important;">
                                    <div class="d-flex justify-content-between align-items-center mb-1.5">
                                        <strong style="color: var(--tarjeta-text);"><i class="fas fa-chair mr-1 opacity-50"></i> Carp. ${sup.rango_inicio} - ${sup.rango_fin}</strong>
                                        <span class="badge bg-dark text-white font-weight-bold" style="font-size: 10px;">${sup.cantidad_estudiantes} al.</span>
                                        <div>
                                            ${splitBtn}
                                            ${deleteBtn}
                                        </div>
                                    </div>
                                    <div class="input-group input-group-sm mt-1">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-light text-muted" style="border-color: var(--tarjeta-input-border);"><i class="fas fa-user-shield"></i></span>
                                        </div>
                                        <input type="text" id="input-docente-${sup.id}" class="form-control form-control-sm" list="${datalistId}" 
                                               value="${docenteValue === 'PTE. ASIGNACIÓN' ? '' : docenteValue}" 
                                               placeholder="Asignar responsable..."
                                               style="border-color: var(--tarjeta-input-border); background-color: var(--tarjeta-input-bg); color: var(--tarjeta-text); font-weight: 600;">
                                        <div class="input-group-append">
                                            <button class="btn btn-primary shadow-none" type="button" onclick="asignarDocente(${sup.id}, document.getElementById('input-docente-${sup.id}').value)" title="Guardar responsable" style="border-top-left-radius: 0; border-bottom-left-radius: 0;">
                                                <i class="fas fa-save"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <datalist id="${datalistId}">
                                        ${state.edificio.docentes.map(d => `<option value="${d.id}">${d.nombre} ${d.apellido_paterno}</option>`).join('')}
                                    </datalist>
                                </div>
                            `;
                        });
                    } else {
                        supervisorsHtml = `<p class="text-muted small py-2 text-center"><i class="fas fa-exclamation-triangle mr-1"></i> Sin responsables.</p>`;
                    }

                    html += `
                        <div class="card aula-card shadow-sm ${stateClass}" style="width: 310px; border-radius: 12px; margin-bottom: 5px;">
                            <div class="card-header py-2 px-3 d-flex justify-content-between align-items-center" 
                                 style="cursor: pointer;" onclick="verDetalleAula(${aula.id})">
                                <span class="font-weight-bold" style="font-size: 13.5px;"><i class="fas fa-door-open mr-1.5 opacity-75"></i> Aula ${aula.nombre}</span>
                                <span class="badge ${badgeClass}" title="Haga clic para editar capacidad de esta aula" onclick="editarCapacidadAula(event, ${aula.id}, '${aula.nombre}', ${aula.capacidad})" style="cursor: pointer; padding: 4.5px 7.5px; border-radius: 6px; font-weight: 700; font-size: 10px;">
                                    ${aula.cantidad_estudiantes}/${aula.capacidad} <i class="fas fa-edit ml-1" style="font-size: 8px; opacity: 0.8;"></i>
                                </span>
                            </div>
                            <div class="card-body p-2.5">
                                <div class="mb-3 text-center" onclick="verDetalleAula(${aula.id})" style="cursor: pointer;">
                                    <div class="progress progress-premium mb-1">
                                        <div class="progress-bar ${progressColor}" role="progressbar" style="width: ${Math.min(100, ocupacionPorc)}%;" aria-valuenow="${ocupacionPorc}" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                    <small class="text-muted" style="font-size: 9.5px; font-weight: 500;"><i class="fas fa-th mr-1"></i> Ver distribución de carpetas</small>
                                </div>
                                
                                <div class="supervisores-list">
                                    ${supervisorsHtml}
                                </div>
                            </div>
                        </div>
                    `;
                });

                html += `</div></div>`;
            });

            html += `</div></div>`;
            container.innerHTML = html;
        }

        async function editarCapacidadAula(event, id, nombre, capacidadActual) {
            event.stopPropagation(); // Evitar abrir modal de detalles del aula
            
            const { value: nuevaCapacidad } = await Swal.fire({
                title: `Editar Capacidad - Aula ${nombre}`,
                text: 'Escribe la nueva capacidad de carpetas para esta aula:',
                input: 'number',
                inputAttributes: {
                    min: 1,
                    step: 1
                },
                inputValue: capacidadActual,
                showCancelButton: true,
                confirmButtonText: 'Guardar',
                cancelButtonText: 'Cancelar',
                inputValidator: (value) => {
                    if (!value || isNaN(value) || value <= 0) {
                        return 'Debes ingresar una capacidad válida mayor a 0';
                    }
                }
            });

            if (nuevaCapacidad) {
                try {
                    let piso = 1;
                    if (state.edificio.pisos) {
                        Object.values(state.edificio.pisos).forEach(floorData => {
                            const found = floorData.aulas.find(a => a.id === id);
                            if (found) piso = found.piso;
                        });
                    }
                    
                    await axios.post(API_URLS.GUARDAR_AULA, {
                        id: id,
                        nombre: nombre,
                        piso: piso,
                        capacidad: parseInt(nuevaCapacidad)
                    });
                    
                    Toast.fire({ icon: 'success', title: 'Capacidad de aula actualizada' });
                    await cargarDatos();
                } catch (error) {
                    console.error(error);
                    const msg = error.response?.data?.error || error.message || 'Error al actualizar capacidad';
                    Swal.fire('Error', msg, 'error');
                }
            }
        }

        async function cargarDatos() {
            if (state.isLoading) return;
            state.isLoading = true;
            updateLoadingUI(true);

            try {
                const selectedExamen = document.getElementById('select-examen').value;
                const resTarjetas = await axios.get(`${API_URLS.CARGAR_DATOS}?examen_numero=${selectedExamen}`);
                state.postulantes = Array.isArray(resTarjetas.data) ? resTarjetas.data : [];

                const resEdificio = await axios.get(`${API_URLS.EDIFICIO_DATA}?examen_numero=${selectedExamen}`);
                state.edificio = resEdificio.data;

                if (state.edificio.titulo_examen) {
                    const tituloEl = document.getElementById('distribucion-titulo');
                    if (tituloEl) tituloEl.innerText = state.edificio.titulo_examen;
                }

                updateUI();
            } catch (error) {
                console.error(error);
                const msg = error.response?.data?.error || error.message || 'Error desconocido';
                Swal.fire('Error al Cargar', msg, 'error');
            } finally {
                state.isLoading = false;
                updateLoadingUI(false);
            }
        }

          async function generarDistribucion() {
            const selectedExamen = document.getElementById('select-examen').value;
            
            const { value: formValues } = await Swal.fire({
                title: 'Generar Distribución de Estudiantes',
                html: `
                    <p class="text-muted text-start" style="font-size: 13.5px;">Elige el método de distribución para los estudiantes aptos en las aulas activas:</p>
                    <div class="text-start mb-3 bg-light p-3 rounded" style="border: 1px solid #e2e8f0;">
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="swal-tipo-dist" id="dist-ordenada" value="ordenada" checked>
                            <label class="form-check-label font-weight-bold text-dark mb-0 ml-1" for="dist-ordenada" style="font-size: 13.5px; cursor: pointer;">
                                <i class="fas fa-sort-alpha-down mr-1 text-primary"></i> Ordenada (Recomendado)
                            </label>
                            <small class="text-muted d-block pl-4" style="font-size: 11px;">Distribuye por Grupo, Carrera y orden alfabético.</small>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="swal-tipo-dist" id="dist-aleatoria" value="aleatoria">
                            <label class="form-check-label font-weight-bold text-dark mb-0 ml-1" for="dist-aleatoria" style="font-size: 13.5px; cursor: pointer;">
                                <i class="fas fa-random mr-1 text-warning"></i> Aleatoria
                            </label>
                            <small class="text-muted d-block pl-4" style="font-size: 11px;">Distribuye a los estudiantes completamente al azar.</small>
                        </div>
                    </div>
                    <div class="text-start p-2.5 rounded" style="border: 1px solid rgba(220, 53, 69, 0.2); background-color: rgba(220, 53, 69, 0.04);">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="swal-excluir-inh" value="1" checked>
                            <label class="form-check-label font-weight-bold text-danger mb-0 ml-1" for="swal-excluir-inh" style="font-size: 13px; cursor: pointer; user-select: none;">
                                <i class="fas fa-user-minus mr-1"></i> Descontar/Excluir alumnos inhabilitados
                            </label>
                            <small class="text-muted d-block pl-4" style="font-size: 11px; color: #b02a37 !important;">Si está marcado, los estudiantes inhabilitados no serán distribuidos en las aulas (no ocuparán espacio físico).</small>
                        </div>
                    </div>
                `,
                focusConfirm: false,
                showCancelButton: true,
                confirmButtonText: 'Iniciar Distribución',
                cancelButtonText: 'Cancelar',
                preConfirm: () => {
                    const tipo = document.querySelector('input[name="swal-tipo-dist"]:checked').value;
                    const excluirInhabilitados = document.getElementById('swal-excluir-inh').checked ? 1 : 0;
                    return { tipoDistribucion: tipo, excluirInhabilitados: excluirInhabilitados };
                }
            });

            if (formValues) {
                state.isLoading = true;
                updateLoadingUI(true);
                try {
                    const response = await axios.post(API_URLS.DISTRIBUIR, { 
                        examen_numero: selectedExamen,
                        tipo_distribucion: formValues.tipoDistribucion,
                        excluir_inhabilitados: formValues.excluirInhabilitados
                    });
                    Swal.fire('¡Éxito!', response.data.message, 'success');
                    await cargarDatos(); 
                } catch (error) {
                    console.error(error);
                    const msg = error.response?.data?.error || error.message || 'Error al distribuir';
                    Swal.fire('Error al Distribuir', msg, 'error');
                } finally {
                    state.isLoading = false;
                    updateLoadingUI(false);
                }
            }
        }

        async function asignarDocente(distribucionId, valor) {
            try {
                await axios.post(API_URLS.GUARDAR_DOCENTE, {
                    distribucion_id: distribucionId,
                    docente_valor: valor 
                });
                Toast.fire({ icon: 'success', title: 'Docente asignado' });
            } catch (error) {
                console.error(error);
                const msg = error.response?.data?.error || error.message || 'Error al asignar docente';
                Swal.fire('Error', msg, 'error');
            }
        }

        async function dividirSupervisor(id, inicio, fin) {
            const { value: corte } = await Swal.fire({
                title: 'Dividir Rango de Responsabilidad',
                text: `El rango actual es de la carpeta ${inicio} a la carpeta ${fin}. Escribe el número del asiento final para el primer grupo (debe ser entre ${inicio} y ${fin - 1}):`,
                input: 'number',
                inputAttributes: {
                    min: inicio,
                    max: fin - 1,
                    step: 1
                },
                showCancelButton: true,
                confirmButtonText: 'Dividir',
                cancelButtonText: 'Cancelar',
                inputValidator: (value) => {
                    if (!value || isNaN(value) || value < inicio || value >= fin) {
                        return 'Introduce un asiento de corte válido dentro del rango.';
                    }
                }
            });

            if (corte) {
                state.isLoading = true;
                updateLoadingUI(true);
                try {
                    await axios.post(API_URLS.DIVIDIR_SUPERVISOR, {
                        distribucion_id: id,
                        rango_corte: parseInt(corte)
                    });
                    Toast.fire({ icon: 'success', title: 'Supervisor dividido' });
                    await cargarDatos();
                } catch (error) {
                    console.error(error);
                    const msg = error.response?.data?.error || error.message || 'Error al dividir';
                    Swal.fire('Error', msg, 'error');
                } finally {
                    state.isLoading = false;
                    updateLoadingUI(false);
                }
            }
        }

        async function eliminarSupervisor(id) {
            const result = await Swal.fire({
                title: '¿Quitar responsable y fusionar rango?',
                text: "Se eliminará la asignación de este supervisor y su rango de asientos se fusionará con el supervisor contiguo del aula.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, quitar y fusionar',
                cancelButtonText: 'Cancelar'
            });

            if (result.isConfirmed) {
                state.isLoading = true;
                updateLoadingUI(true);
                try {
                    await axios.post(API_URLS.ELIMINAR_SUPERVISOR, {
                        distribucion_id: id
                    });
                    Toast.fire({ icon: 'success', title: 'Responsable quitado y rango fusionado' });
                    await cargarDatos();
                } catch (error) {
                    console.error(error);
                    const msg = error.response?.data?.error || error.message || 'Error al quitar supervisor';
                    Swal.fire('Error', msg, 'error');
                } finally {
                    state.isLoading = false;
                    updateLoadingUI(false);
                }
            }
        }

        function abrirModalGestionAulas() {
            renderTablaGestionAulas();
            if (modalGestionAulasInstance) {
                modalGestionAulasInstance.show();
            }
        }

        function renderTablaGestionAulas() {
            const tbody = document.getElementById('tbody-gestion-aulas');
            let html = '';
            const todasAulas = [];
            if (state.edificio.pisos) {
                Object.values(state.edificio.pisos).forEach(floor => {
                    if (floor.aulas) {
                        floor.aulas.forEach(a => todasAulas.push(a));
                    }
                });
            }
            todasAulas.sort((a,b) => (a.nombre || '').localeCompare(b.nombre || '', undefined, {numeric: true}));

             todasAulas.forEach(aula => {
                const aulaEscaped = JSON.stringify(aula).replace(/"/g, '&quot;');
                html += `
                    <tr>
                        <td class="text-dark font-weight-bold">${aula.nombre}</td>
                        <td class="text-dark">${aula.piso}</td>
                        <td class="text-dark">${aula.capacidad}</td>
                        <td>
                            <button class="btn btn-sm btn-warning text-white mr-1.5" onclick="editarAula(${aulaEscaped})"><i class="fas fa-edit"></i></button>
                            <button class="btn btn-sm btn-danger" onclick="eliminarAula(${aula.id})"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>
                `;
            });
            tbody.innerHTML = html;
        }

        function editarAula(aula) {
            const individualTab = document.getElementById('individual-tab');
            if (individualTab) {
                individualTab.click();
            }
            document.getElementById('aula-id').value = aula.id;
            document.getElementById('aula-nombre').value = aula.nombre;
            document.getElementById('aula-piso').value = aula.piso;
            document.getElementById('aula-capacidad').value = aula.capacidad;

            const submitBtn = document.querySelector('#form-aula button[type="submit"]');
            if (submitBtn) {
                submitBtn.innerText = 'Actualizar';
                submitBtn.className = 'btn btn-warning w-100 text-white';
            }
        }

        document.getElementById('form-aula').addEventListener('submit', async function(e) {
            e.preventDefault();
            const id = document.getElementById('aula-id').value;
            const nombre = document.getElementById('aula-nombre').value;
            const piso = document.getElementById('aula-piso').value;
            const capacidad = document.getElementById('aula-capacidad').value;

            try {
                await axios.post(API_URLS.GUARDAR_AULA, { id, nombre, piso, capacidad });
                await cargarDatos(); 
                renderTablaGestionAulas();
                
                document.getElementById('aula-id').value = '';
                document.getElementById('form-aula').reset();
                
                const submitBtn = document.querySelector('#form-aula button[type="submit"]');
                if (submitBtn) {
                    submitBtn.innerText = 'Guardar';
                    submitBtn.className = 'btn btn-success w-100';
                }
                
                Toast.fire({ icon: 'success', title: id ? 'Aula actualizada correctamente' : 'Aula guardada' });
            } catch (error) {
                console.error(error);
                const msg = error.response?.data?.error || error.message || 'Error al guardar aula';
                Swal.fire('Error', msg, 'error');
            }
        });

        document.getElementById('form-piso').addEventListener('submit', async function(e) {
            e.preventDefault();
            const piso = document.getElementById('piso-numero').value;
            const inicio = document.getElementById('piso-inicio').value;
            const cantidad = document.getElementById('piso-cantidad').value;
            const capacidad = document.getElementById('piso-capacidad').value;

            try {
                const res = await axios.post(API_URLS.GUARDAR_PISO, { 
                    piso: piso, 
                    inicio_numeracion: inicio, 
                    cantidad_aulas: cantidad, 
                    capacidad_default: capacidad 
                });
                await cargarDatos(); 
                renderTablaGestionAulas(); 
                document.getElementById('form-piso').reset();
                Toast.fire({ icon: 'success', title: res.data.message });
            } catch (error) {
                console.error(error);
                const msg = error.response?.data?.error || error.message || 'Error al generar piso';
                Swal.fire('Error', msg, 'error');
            }
        });

        async function eliminarAula(id) {
            const result = await Swal.fire({
                title: '¿Eliminar esta aula?',
                text: "Se eliminará el aula de la lista permanentemente.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            });

            if (result.isConfirmed) {
                try {
                    await axios.delete(`${API_URLS.ELIMINAR_AULA}/${id}`);
                    await cargarDatos();
                    renderTablaGestionAulas();
                    Toast.fire({ icon: 'success', title: 'Aula eliminada correctamente' });
                } catch (error) {
                    console.error(error);
                    const msg = error.response?.data?.error || error.message || 'Error al eliminar';
                    Swal.fire('Error', msg, 'error');
                }
            }
        }

        async function verDetalleAula(id) {
            try {
                const selectedExamen = document.getElementById('select-examen').value;
                const res = await axios.get(`${API_URLS.AULA_DETALLE}/${id}?examen_numero=${selectedExamen}`);
                const { aula, estudiantes } = res.data;

                document.getElementById('modalDetalleTitulo').innerHTML = `<i class="fas fa-door-open mr-2"></i> Aula ${aula.nombre} - Piso ${aula.piso}`;
                const grid = document.getElementById('grid-carpetas');
                grid.innerHTML = '';

                const totalSlots = Math.max(aula.capacidad, estudiantes.length);
                
                for (let i = 0; i < totalSlots; i++) {
                    const est = estudiantes[i];
                    let content = '';
                    
                    if (est) {
                        let temaBadge = '';
                        if (est.tema === 'P') temaBadge = '<span class="badge bg-primary px-1.5 py-0.5" style="font-size: 8px;">T: P</span>';
                        else if (est.tema === 'Q') temaBadge = '<span class="badge bg-success px-1.5 py-0.5" style="font-size: 8px;">T: Q</span>';
                        else if (est.tema === 'R') temaBadge = '<span class="badge bg-warning text-dark px-1.5 py-0.5" style="font-size: 8px;">T: R</span>';

                        const borderStyle = est.inhabilitado ? 'border-danger' : 'border-primary';
                        const cardBg = est.inhabilitado ? 'style="background-color: rgba(239, 68, 68, 0.15);"' : '';
                        const badgeHtml = est.inhabilitado 
                            ? `<span class="badge bg-danger" style="font-size: 0.65rem; padding: 2px 6px;"><i class="fas fa-exclamation-triangle mr-1"></i> INHABILITADO</span>`
                            : `<span class="badge bg-primary text-white" style="font-size: 0.6rem; padding: 2px 6px; font-weight: 700;">${est.codigo}</span>`;

                        content = `
                            <div class="card h-100 ${borderStyle} shadow-sm" ${cardBg} style="border-radius: 8px; overflow: hidden; position: relative;">
                                <div class="position-absolute d-flex align-items-center justify-content-center" style="top: 4px; left: 6px; font-weight: 950; font-size: 11px; z-index: 10; background: rgba(255,255,255,0.9); width: 22px; height: 22px; border-radius: 50%; border: 1px solid #cbd5e1; box-shadow: 0 1px 3px rgba(0,0,0,0.1); color: #1e293b;">#${est.asiento}</div>
                                <img src="${est.foto || 'https://cdn.pixabay.com/photo/2015/10/05/22/37/blank-profile-picture-973460_960_720.png'}" class="card-img-top" style="height: 85px; object-fit: cover; ${est.inhabilitado ? 'filter: grayscale(100%); opacity: 0.6;' : ''}">
                                <div class="card-body p-2 text-center d-flex flex-column justify-content-between">
                                    <small class="font-weight-bold d-block text-truncate mb-1" style="font-size: 0.75rem; color: var(--tarjeta-text); ${est.inhabilitado ? 'color: #dc2626 !important; text-decoration: line-through;' : ''}" title="${est.nombre_completo}">${est.nombre_completo}</small>
                                    <div class="mb-1">${badgeHtml}</div>
                                    <div class="d-flex align-items-center justify-content-center gap-1" style="font-size: 9px; font-weight: 700; color: var(--tarjeta-subtext);">
                                        <span class="badge bg-secondary px-1.5 py-0.5" style="font-size: 8px;">G: ${est.grupo}</span>
                                        ${temaBadge}
                                    </div>
                                </div>
                            </div>
                        `;
                    } else {
                        content = `
                            <div class="card h-100 border-dashed bg-light d-flex align-items-center justify-content-center py-4" style="opacity: 0.6; border: 1.5px dashed #cbd5e1; border-radius: 8px; min-height: 145px;">
                                <div class="text-center">
                                    <div style="font-size: 10px; font-weight: 700; color: #94a3b8; margin-bottom: 2px;">#${i + 1}</div>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="opacity-75"><rect x="3" y="3" width="18" height="12" rx="2"/><path d="M9 21v-6h6v6"/></svg>
                                    <div class="text-muted" style="font-size: 9px; font-weight: 500; margin-top: 4px;">Carpeta Libre</div>
                                </div>
                            </div>
                        `;
                    }
                    
                    const div = document.createElement('div');
                    div.innerHTML = content;
                    grid.appendChild(div);
                }

                if (modalDetalleAulaInstance) {
                    modalDetalleAulaInstance.show();
                }

            } catch (error) {
                console.error(error);
                const msg = error.response?.data?.error || error.message || 'Error al cargar detalle';
                Swal.fire('Error', msg, 'error');
            }
        }

        function updateLoadingUI(loading) {
            const btn = document.getElementById('load-btn');
            if (loading) {
                btn.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i>';
                btn.disabled = true;
            } else {
                btn.innerHTML = '<i class="fas fa-sync-alt mr-2"></i> Recargar';
                btn.disabled = false;
            }
        }

        function setFiltroEstadistico(filtro) {
            state.filtroEstadistico = filtro;
            updateUI();
        }

        function renderEdificioStats(resumen) {
            const container = document.getElementById('edificio-stats-container');
            if (!resumen) {
                container.innerHTML = '';
                return;
            }

            const { total_faltantes } = resumen;
            
            let faltantesHtml = '';
            if (total_faltantes > 0) {
                faltantesHtml = `
                    <div class="alert alert-danger border-0 shadow-sm px-3.5 py-2.5 d-flex justify-content-between align-items-center mb-3 flex-wrap animate__animated animate__fadeIn" style="border-radius: 8px; background-color: rgba(239, 68, 68, 0.08); color: #dc2626; border-left: 4px solid #dc2626 !important;">
                        <div class="d-flex align-items-center" style="font-size: 13.5px; font-weight: 700;">
                            <i class="fas fa-exclamation-circle mr-2.5" style="font-size: 16px;"></i>
                            <span>Atención: Hay <strong style="text-decoration: underline;">${total_faltantes}</strong> estudiantes aptos sin aula asignada por falta de capacidad física.</span>
                        </div>
                        <button class="btn btn-sm btn-danger text-white font-weight-bold shadow-xs px-3 py-1.5 mt-2 mt-md-0" onclick="abrirModalReporte()" style="border-radius: 6px; font-size: 12px; font-weight: 800;">
                            <i class="fas fa-eye mr-1"></i> Ver Detalle de Pendientes
                        </button>
                    </div>
                `;
            } else {
                faltantesHtml = `
                    <div class="alert alert-success border-0 shadow-sm p-3 d-flex align-items-center mb-3" style="border-radius: 12px; background-color: rgba(16, 185, 129, 0.1); color: #10b981;">
                        <i class="fas fa-check-circle mr-2.5 opacity-90" style="font-size: 18px;"></i>
                        <span style="font-size: 13px; font-weight: 700;">¡Todos los estudiantes aptos del ciclo fueron asignados con éxito! Capacidad de aulas conforme.</span>
                    </div>
                `;
            }

            container.innerHTML = faltantesHtml;
        }

        function abrirModalReporte() {
            if (!state.edificio.resumen) return;
            
            const resumen = state.edificio.resumen;
            const { total_aulas, capacidad_total, total_aptos, total_asignados, total_faltantes, faltantes_detalle } = resumen;
            const ocupacionAulasPct = capacidad_total > 0 ? Math.round((total_asignados / capacidad_total) * 100) : 0;

            // Render unassigned detailed list in modal
            const faltantesContainer = document.getElementById('modal-edificio-faltantes-container');
            if (total_faltantes > 0) {
                let listItems = '';
                faltantes_detalle.forEach(g => {
                    const carrerasBadges = g.carreras.map(c => `
                        <span class="badge bg-light text-dark border mr-1.5 mb-1.5 px-2.5 py-1.5" style="font-size: 11.5px; border-radius: 6px; font-weight: 600;">
                            ${c.nombre}: <strong class="text-danger ml-1">${c.cantidad}</strong>
                        </span>
                    `).join('');
                    
                    listItems += `
                        <div class="mb-3">
                            <strong style="font-size: 13px; color: #1e293b;"><i class="fas fa-users text-muted mr-1"></i> Grupo ${g.grupo}:</strong> 
                            <div class="d-inline-flex flex-wrap mt-1 w-100">${carrerasBadges}</div>
                        </div>
                    `;
                });

                faltantesContainer.innerHTML = `
                    <div class="alert alert-warning border-0 shadow-sm p-3.5 mb-4" style="border-radius: 12px; background-color: rgba(245, 158, 11, 0.12); border-left: 5px solid #f59e0b !important;">
                        <h6 class="font-weight-bold mb-2 text-warning d-flex align-items-center" style="font-size: 14px;">
                            <i class="fas fa-exclamation-triangle mr-2"></i> ESTUDIANTES PENDIENTES DE ASIGNACIÓN: ${total_faltantes}
                        </h6>
                        <p class="text-muted mb-3" style="font-size: 12.5px; font-weight: 500;">
                            Las aulas actuales no cuentan con suficiente capacidad física para albergar a los siguientes postulantes. Te recomendamos agregar más aulas o aumentar su capacidad.
                        </p>
                        <div class="pl-2 border-left" style="border-color: rgba(245, 158, 11, 0.4) !important;">
                            ${listItems}
                        </div>
                    </div>
                `;
            } else {
                faltantesContainer.innerHTML = `
                    <div class="alert alert-success border-0 shadow-sm p-3 d-flex align-items-center mb-4" style="border-radius: 12px; background-color: rgba(16, 185, 129, 0.1); color: #10b981;">
                        <i class="fas fa-check-circle mr-2.5 opacity-90" style="font-size: 18px;"></i>
                        <span style="font-size: 13.5px; font-weight: 700;">¡Todos los estudiantes aptos del ciclo fueron asignados con éxito! Capacidad de aulas conforme.</span>
                    </div>
                `;
            }

            const cardsContainer = document.getElementById('modal-edificio-stats-cards');
            cardsContainer.innerHTML = `
                <div class="row">
                    <!-- Capacidad Total Aulas -->
                    <div class="col-md-3 mb-3 mb-md-0">
                        <div class="card border shadow-sm h-100" style="border-radius: 12px; background-color: var(--tarjeta-bg);">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="text-muted small font-weight-bold d-block text-uppercase" style="letter-spacing: 0.5px; font-size: 10px;">CAPACIDAD TOTAL</span>
                                        <h4 class="mb-0 font-weight-bold text-dark mt-1" style="font-size: 18px;">${capacidad_total} <small style="font-size: 10px; font-weight: normal; color: #64748b;">asientos</small></h4>
                                    </div>
                                    <div class="p-2 rounded bg-primary text-white" style="font-size: 1.1rem; line-height: 1;"><i class="fas fa-chair"></i></div>
                                </div>
                                <div class="mt-2.5">
                                    <div class="progress" style="height: 5px; border-radius: 3px; background-color: var(--tarjeta-detail-bg);">
                                        <div class="progress-bar bg-primary" role="progressbar" style="width: ${ocupacionAulasPct}%" aria-valuenow="${ocupacionAulasPct}" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                    <div class="d-flex justify-content-between text-muted small mt-1" style="font-size: 9px; font-weight: 500;">
                                        <span>Ocupación:</span>
                                        <strong>${ocupacionAulasPct}%</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Postulantes Aptos -->
                    <div class="col-md-3 mb-3 mb-md-0">
                        <div class="card border shadow-sm h-100" style="border-radius: 12px; background-color: var(--tarjeta-bg);">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="text-muted small font-weight-bold d-block text-uppercase" style="letter-spacing: 0.5px; font-size: 10px;">APTOS DEL CICLO</span>
                                        <h4 class="mb-0 font-weight-bold text-dark mt-1" style="font-size: 18px;">${total_aptos}</h4>
                                    </div>
                                    <div class="p-2 rounded bg-info text-white" style="font-size: 1.1rem; line-height: 1;"><i class="fas fa-user-graduate"></i></div>
                                </div>
                                <div class="mt-2.5 text-muted" style="font-size: 9.5px; font-weight: 500;">
                                    <i class="fas fa-building mr-1 text-primary"></i> En <strong>${total_aulas}</strong> aulas
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Estudiantes Asignados -->
                    <div class="col-md-3 mb-3 mb-md-0">
                        <div class="card border shadow-sm h-100" style="border-radius: 12px; background-color: var(--tarjeta-bg);">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="text-muted small font-weight-bold d-block text-uppercase" style="letter-spacing: 0.5px; font-size: 10px;">ASIGNADOS</span>
                                        <h4 class="mb-0 font-weight-bold text-success mt-1" style="font-size: 18px;">${total_asignados}</h4>
                                    </div>
                                    <div class="p-2 rounded bg-success text-white" style="font-size: 1.1rem; line-height: 1;"><i class="fas fa-user-check"></i></div>
                                </div>
                                <div class="mt-2.5 text-muted" style="font-size: 9.5px; font-weight: 500;">
                                    <i class="fas fa-check mr-1 text-success"></i> Tienen asiento y tema
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Estudiantes Faltantes -->
                    <div class="col-md-3">
                        <div class="card border shadow-sm h-100" style="border-radius: 12px; background-color: var(--tarjeta-bg);">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="text-muted small font-weight-bold d-block text-uppercase" style="letter-spacing: 0.5px; font-size: 10px;">PENDIENTES</span>
                                        <h4 class="mb-0 font-weight-bold ${total_faltantes > 0 ? 'text-danger' : 'text-muted'} mt-1" style="font-size: 18px;">${total_faltantes}</h4>
                                    </div>
                                    <div class="p-2 rounded ${total_faltantes > 0 ? 'bg-danger' : 'bg-secondary'} text-white" style="font-size: 1.1rem; line-height: 1;"><i class="fas fa-user-times"></i></div>
                                </div>
                                <div class="mt-2.5 text-muted" style="font-size: 9.5px; font-weight: 500;">
                                    ${total_faltantes > 0 ? '<span class="text-danger"><i class="fas fa-exclamation-triangle mr-1"></i> Faltan asientos</span>' : '<span><i class="fas fa-smile mr-1 text-success"></i> 0 pendientes</span>'}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            const tbody = document.getElementById('tbody-reporte-pisos');
            tbody.innerHTML = '';
            if (state.edificio.pisos) {
                const pisosKeys = Object.keys(state.edificio.pisos).sort((a,b) => parseInt(a) - parseInt(b));
                pisosKeys.forEach(pisoKey => {
                    const floorData = state.edificio.pisos[pisoKey];
                    const capacity = floorData.capacidad_total;
                    const assigned = floorData.estudiantes_asignados;
                    const free = capacity - assigned;
                    const pct = capacity > 0 ? Math.round((assigned / capacity) * 100) : 0;
                    
                    tbody.innerHTML += `
                        <tr>
                            <td class="font-weight-bold text-dark">Piso ${pisoKey}</td>
                            <td class="text-dark">${floorData.aulas.length}</td>
                            <td class="text-dark">${capacity}</td>
                            <td class="text-dark font-weight-bold text-primary">${assigned}</td>
                            <td class="font-weight-bold ${free > 0 ? 'text-success' : 'text-muted'}">${free}</td>
                            <td>
                                <div class="d-flex align-items-center justify-content-center" style="gap: 8px;">
                                    <div class="progress flex-grow-1" style="height: 6px; width: 60px; border-radius: 3px; background-color: var(--tarjeta-detail-bg);">
                                        <div class="progress-bar ${pct >= 100 ? 'bg-danger' : (pct > 50 ? 'bg-warning' : 'bg-success')}" role="progressbar" style="width: ${pct}%"></div>
                                    </div>
                                    <span class="font-weight-bold text-dark" style="font-size: 11px;">${pct}%</span>
                                </div>
                            </td>
                        </tr>
                    `;
                });
            }

            if (modalReporteEdificioInstance) {
                modalReporteEdificioInstance.show();
            }
        }

        function updateUI() {
            renderEstadisticas();

            const tarjetasContainer = document.getElementById('tarjetas-container');
            if (state.postulantes.length === 0) {
                tarjetasContainer.innerHTML = '<p class="text-center mt-4 text-muted">No hay distribución generada para este examen. Dirígete a la pestaña "Simulación Edificio" para generar una nueva.</p>';
            } else {
                let postulantesFiltrados = state.postulantes;
                if (state.filtroEstadistico === 'inhabilitados') {
                    postulantesFiltrados = state.postulantes.filter(p => p.inhabilitado);
                } else if (state.filtroEstadistico === 'habilitados') {
                    postulantesFiltrados = state.postulantes.filter(p => !p.inhabilitado);
                }
                
                if (postulantesFiltrados.length === 0) {
                    tarjetasContainer.innerHTML = `<p class="text-center mt-4 text-muted">No hay postulantes que coincidan con el filtro seleccionado.</p>`;
                } else {
                    tarjetasContainer.innerHTML = postulantesFiltrados.map(crearTarjetaHTML).join('');
                }
            }

            const hasData = state.postulantes.length > 0;
            const printBtn = document.getElementById('print-btn');
            const pdfBtn = document.getElementById('pdf-btn');
            const gatePdfBtn = document.getElementById('gate-pdf-btn');
            const actasPdfBtn = document.getElementById('actas-pdf-btn');
            const resumenPdfBtn = document.getElementById('resumen-pdf-btn');
            
            if (printBtn) printBtn.disabled = !hasData;
            if (pdfBtn) pdfBtn.disabled = !hasData;
            if (gatePdfBtn) gatePdfBtn.disabled = !hasData;
            if (actasPdfBtn) actasPdfBtn.disabled = !hasData;
            if (resumenPdfBtn) resumenPdfBtn.disabled = !hasData;

            renderDistribucionTabla(state.postulantes);
            renderEdificioStats(state.edificio.resumen);
            renderEdificio();
        }

        function renderDistribucionTabla(postulantes) {
            const tbody = document.querySelector('#tabla-distribucion tbody');
            const grupos = {};
            
            postulantes.forEach(p => {
                const aula = p.aula || 'SIN AULA';
                if (aula === 'POR ASIGNAR') return;
                
                if (!grupos[aula]) {
                    grupos[aula] = { aula: aula, tema: p.tema, grupo: p.grupo, cantidad: 0 };
                }
                grupos[aula].cantidad++;
            });
            
            const data = Object.values(grupos).sort((a, b) => a.aula.localeCompare(b.aula, undefined, { numeric: true }));

            if (data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" class="text-muted">Sin distribución generada</td></tr>';
                return;
            }

            tbody.innerHTML = data.map(row => {
                let docentesNombres = [];
                if (state.edificio.pisos) {
                    Object.values(state.edificio.pisos).flat().forEach(a => {
                        if (a.nombre == row.aula && a.supervisores) {
                            a.supervisores.forEach(sup => {
                                if (sup.docente_nombre && sup.docente_nombre !== 'PTE. ASIGNACIÓN') {
                                    docentesNombres.push(`${sup.docente_nombre} (Carpetas ${sup.rango_inicio}-${sup.rango_fin})`);
                                }
                            });
                        }
                    });
                }
                const docentesTexto = docentesNombres.length > 0 ? docentesNombres.join(', ') : 'PTE. ASIGNACIÓN';

                return `
                    <tr>
                        <td class="font-weight-bold text-dark fs-5">${row.aula}</td>
                        <td class="font-weight-bold text-dark fs-5">${row.tema}</td>
                        <td class="font-weight-bold text-dark fs-5">${row.grupo}</td>
                        <td class="font-weight-bold text-dark fs-5">${row.cantidad}</td>
                        <td class="text-start ps-4 text-dark">${docentesTexto}</td>
                    </tr>
                `;
            }).join('');
        }

        function switchView(view) {
            state.currentView = view;
            
            document.getElementById('tarjetas-container').classList.add('d-none');
            document.getElementById('distribucion-container').classList.add('d-none');
            document.getElementById('edificio-container').classList.add('d-none');
            
            ['cards', 'list', 'building'].forEach(v => {
                const btn = document.getElementById(`view-${v}-btn`);
                if(btn) {
                    if (v === view) btn.classList.add('active');
                    else btn.classList.remove('active');
                }
            });

            if (view === 'cards') document.getElementById('tarjetas-container').classList.remove('d-none');
            if (view === 'list') document.getElementById('distribucion-container').classList.remove('d-none');
            if (view === 'building') document.getElementById('edificio-container').classList.remove('d-none');
        }

        function renderEstadisticas() {
            const container = document.getElementById('estadisticas-container');
            if (!state.postulantes || state.postulantes.length === 0) {
                container.innerHTML = '';
                return;
            }

            const totalPostulantes = state.postulantes.length;
            const totalInhabilitados = state.postulantes.filter(p => p.inhabilitado).length;
            const totalAsignados = totalPostulantes - totalInhabilitados;

            const fTodos = state.filtroEstadistico === 'todos';
            const fHabilitados = state.filtroEstadistico === 'habilitados';
            const fInhabilitados = state.filtroEstadistico === 'inhabilitados';

            container.innerHTML = `
                <div class="row">
                    <div class="col-md-4 mb-2 mb-md-0">
                        <div class="card shadow-sm border-0" 
                             onclick="setFiltroEstadistico('todos')"
                             style="cursor: pointer; border-radius: 12px; background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%); transition: all 0.2s; ${fTodos ? 'transform: translateY(-3px) scale(1.02); box-shadow: 0 8px 16px rgba(13, 110, 253, 0.4) !important; outline: 3px solid rgba(255,255,255,0.9); opacity: 1;' : 'opacity: 0.75;'}"
                             title="Filtrar por todos los estudiantes">
                            <div class="card-body py-3 px-4 d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-0 font-weight-bold" style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: #ffffff !important; opacity: 0.9;">Total Distribuido</h6>
                                    <h3 class="mb-0 font-weight-bold" style="font-size: 24px; color: #ffffff !important; margin-top: 4px;">${totalPostulantes}</h3>
                                </div>
                                <div style="font-size: 2rem; opacity: 0.45; color: #ffffff !important;"><i class="fas fa-users"></i></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4 mb-2 mb-md-0">
                        <div class="card shadow-sm border-0" 
                             onclick="setFiltroEstadistico('habilitados')"
                             style="cursor: pointer; border-radius: 12px; background: linear-gradient(135deg, #198754 0%, #146c43 100%); transition: all 0.2s; ${fHabilitados ? 'transform: translateY(-3px) scale(1.02); box-shadow: 0 8px 16px rgba(25, 135, 84, 0.4) !important; outline: 3px solid rgba(255,255,255,0.9); opacity: 1;' : 'opacity: 0.75;'}"
                             title="Filtrar por habilitados">
                            <div class="card-body py-3 px-4 d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-0 font-weight-bold" style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: #ffffff !important; opacity: 0.9;">Habilitados (Darían)</h6>
                                    <h3 class="mb-0 font-weight-bold" style="font-size: 24px; color: #ffffff !important; margin-top: 4px;">${totalAsignados}</h3>
                                </div>
                                <div style="font-size: 2rem; opacity: 0.45; color: #ffffff !important;"><i class="fas fa-check-circle"></i></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card shadow-sm border-0" 
                             onclick="setFiltroEstadistico('inhabilitados')"
                             style="cursor: pointer; border-radius: 12px; background: linear-gradient(135deg, #dc3545 0%, #b02a37 100%); transition: all 0.2s; ${fInhabilitados ? 'transform: translateY(-3px) scale(1.02); box-shadow: 0 8px 16px rgba(220, 53, 69, 0.4) !important; outline: 3px solid rgba(255,255,255,0.9); opacity: 1;' : 'opacity: 0.75;'}"
                             title="Filtrar por inhabilitados">
                            <div class="card-body py-3 px-4 d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-0 font-weight-bold" style="font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; color: #ffffff !important; opacity: 0.9;">Inhabilitados</h6>
                                    <h3 class="mb-0 font-weight-bold" style="font-size: 24px; color: #ffffff !important; margin-top: 4px;">${totalInhabilitados}</h3>
                                </div>
                                <div style="font-size: 2rem; opacity: 0.45; color: #ffffff !important;"><i class="fas fa-ban"></i></div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        function exportarPDF() {
            if (state.postulantes.length === 0) {
                Swal.fire('Atención', 'No hay datos para exportar', 'warning');
                return;
            }
            const selectedExamen = document.getElementById('select-examen').value;
            window.location.href = `${API_URLS.EXPORTAR_PDF}?examen_numero=${selectedExamen}`;
        }

        function exportarListaPuerta() {
            if (state.postulantes.length === 0) {
                Swal.fire('Atención', 'No hay datos para exportar', 'warning');
                return;
            }
            const selectedExamen = document.getElementById('select-examen').value;
            window.location.href = `${API_URLS.EXPORTAR_PUERTA}?examen_numero=${selectedExamen}`;
        }

        function exportarActasSupervisor() {
            if (state.postulantes.length === 0) {
                Swal.fire('Atención', 'No hay datos para exportar', 'warning');
                return;
            }
            const selectedExamen = document.getElementById('select-examen').value;
            window.location.href = `${API_URLS.EXPORTAR_ACTAS}?examen_numero=${selectedExamen}`;
        }

        function exportarResumenAulas() {
            if (state.postulantes.length === 0) {
                Swal.fire('Atención', 'No hay datos para exportar', 'warning');
                return;
            }
            const selectedExamen = document.getElementById('select-examen').value;
            window.location.href = `${API_URLS.EXPORTAR_RESUMEN}?examen_numero=${selectedExamen}`;
        }

        function imprimir() {
            document.body.classList.remove('print-cards', 'print-list');
            if (state.currentView === 'cards') document.body.classList.add('print-cards');
            else document.body.classList.add('print-list'); 
            window.print();
        }

        window.addEventListener('DOMContentLoaded', () => {
            // Inicializar modales de Bootstrap 5
            modalDetalleAulaInstance = new bootstrap.Modal(document.getElementById('modalDetalleAula'));
            modalGestionAulasInstance = new bootstrap.Modal(document.getElementById('modalGestionAulas'));
            modalReporteEdificioInstance = new bootstrap.Modal(document.getElementById('modalReporteEdificio'));

            document.getElementById('load-btn').addEventListener('click', cargarDatos);
            document.getElementById('print-btn').addEventListener('click', imprimir);
            document.getElementById('pdf-btn').addEventListener('click', exportarPDF);
            document.getElementById('gate-pdf-btn').addEventListener('click', exportarListaPuerta);
            document.getElementById('actas-pdf-btn').addEventListener('click', exportarActasSupervisor);
            document.getElementById('resumen-pdf-btn').addEventListener('click', exportarResumenAulas);
            
            document.getElementById('view-cards-btn').addEventListener('click', () => switchView('cards'));
            document.getElementById('view-list-btn').addEventListener('click', () => switchView('list'));
            document.getElementById('view-building-btn').addEventListener('click', () => switchView('building'));
            
            document.getElementById('btn-distribuir').addEventListener('click', generarDistribucion);
            
            document.getElementById('select-examen').addEventListener('change', cargarDatos);
            
            cargarDatos();
        });
    </script>
@endpush
