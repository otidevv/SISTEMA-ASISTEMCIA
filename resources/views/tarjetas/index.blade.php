@extends('layouts.app')

@section('title', 'Etiquetas de Examen Pre Universitario UNAMAD')

@push('css')
    <!-- Carga Bootstrap 4 (Para el layout general de tu app) -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Carga Font Awesome (ICONOS) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    
    <style>
        /* Variables y configuración de color principal */
        :root {
            --color-unama-blue: #0A3C59; /* Azul Oscuro Institucional */
            --color-tema-p: #0d6efd; /* Azul claro */
            --color-tema-q: #198754; /* Verde */
            --color-tema-r: #ffc107; /* Amarillo */
        }

        /* ------------------------------------------------ */
        /* Estilos del CONTENEDOR (RESPETA CÓDIGO ORIGINAL) */
        /* ------------------------------------------------ */

        .tarjeta-container {
            /* CÓDIGO ORIGINAL DEL USUARIO PARA EL LAYOUT DE COLUMNAS EN PANTALLA */
            column-count: 2;
            column-gap: 20px;
            padding: 10px;
        }

        /* ------------------------------------------------ */
        /* Estilos de la TARJETA (Flexible en Web, Fija en Print) */
        /* ------------------------------------------------ */

        .tarjeta {
            /* ESSENCIAL para column-count y resolución de desbordamiento */
            display: inline-block; 
            width: 100%; /* Ocupa el 100% de la columna, NO causa desbordamiento */
            max-width: 8.5cm; /* Máximo tamaño en pantalla */
            height: 5.5cm;
            margin: 0 0 10px 0; /* Margen solo inferior para separación */
            padding: 0; 
            
            /* Estilos de Diseño */
            background-color: #ffffff; 
            position: relative;
            font-size: 10px;
            border-radius: 0.65rem;
            overflow: hidden;
            box-shadow: 0 4px 10px -2px rgba(0, 0, 0, 0.15);
            border: 1px solid #ddd;
            
            /* Usamos Flexbox internamente para el layout del contenido */
            display: flex;
        }

        /* Colores de las franjas laterales */
        .franja-tema {
            width: 15px;
            height: 100%;
            z-index: 10;
            flex-shrink: 0;
        }
        .tarjeta-p .franja-tema { background-color: var(--color-tema-p); }
        .tarjeta-q .franja-tema { background-color: var(--color-tema-q); }
        .tarjeta-r .franja-tema { background-color: var(--color-tema-r); }

        /* Contenido Principal Interno */
        .contenido-principal {
            display: flex;
            flex-direction: column;
            width: 100%;
            height: 100%;
            position: relative;
            z-index: 10;
        }

        /* Cabecera */
        .header-institucional {
            padding: 3px 8px; /* Reducción a 3px */
            display: flex;
            align-items: center;
            justify-content: space-between;
            color: white;
            flex-shrink: 0;
        }

        /* Ubicación Clave */
        .ubicacion-clave {
            flex-grow: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px; /* Reducción de gap */
            padding: 2px 8px; /* Reducción de padding */
        }
        .ubicacion-clave .aula-code {
            text-align: center;
            flex: 1;
        }
        .ubicacion-clave .aula-code span {
            color: #9ca3af;
            font-weight: 800;
            font-size: 8px;
            display: block;
            line-height: 1;
        }
        .ubicacion-clave .aula-code .aula {
            font-weight: 900;
            font-size: 32px;
            line-height: 1; /* Ajuste crítico: Reducido a 1 */
            color: #dc2626;
        }
        .ubicacion-clave .aula-code .codigo {
            font-weight: 900;
            font-size: 20px;
            line-height: 1; /* Ajuste crítico: Reducido a 1 */
            color: #1f2937;
        }
        .ubicacion-clave .separator {
            border-left: 1px solid #d1d5db;
            padding-left: 10px; /* Reducción de padding-left */
        }


        /* Identificación (Foto, Nombre, Carrera) */
        .identificacion-detalle {
            width: 100%;
            background-color: #f3f4f6;
            padding: 4px 8px 2px; /* Reducción de padding inferior */
            color: #4b5563;
            flex-shrink: 0;
        }
        .identificacion-fila {
            display: flex;
            align-items: flex-start;
            gap: 6px;
            margin-bottom: 2px;
        }

        /* Foto */
        .identificacion-fila .foto-container {
            flex-shrink: 0;
        }
        .identificacion-fila .foto-container img {
            width: 75px; /* TAMAÑO FINAL DE FOTO */
            height: 75px; /* TAMAÑO FINAL DE FOTO */
            object-fit: cover;
            border-radius: 0.25rem;
            border: 2px solid #60a5fa;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
        }
        
        /* Nombre y Carrera */
        .identificacion-fila .datos-postulante {
            flex-grow: 1;
            text-align: left;
            line-height: 1.1;
            padding-top: 2px; /* Ajuste para alinear con la foto */
        }
        .identificacion-fila .datos-postulante span {
            color: #9ca3af;
            font-size: 8px;
            display: block;
            text-transform: uppercase;
        }
        
        /* Solución de Recorte de Texto */
        .nombre-postulante {
            font-weight: 800;
            font-size: 0.7rem; /* 11.2px */
            line-height: 1.1; 
            height: 1.3rem;
            overflow: hidden;
            color: #1f2937;
            margin-bottom: 0px;
        }
        
        .carrera-postulante {
            font-weight: 600; 
            font-size: 0.65rem; /* 10.4px */
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            color: #1d4ed8; 
            margin-bottom: 0;
        }


        /* Footer */
        .footer-grupo-tema {
            font-size: 8px;
            font-weight: 600;
            padding-top: 2px;
            border-top: 1px solid #d1d5db;
            text-align: center;
        }
        .footer-grupo-tema strong {
            color: #1f2937;
        }

        /* ------------------------------------------------ */
        /* Media Query para impresión (Fuerza el tamaño físico) */
        /* ------------------------------------------------ */
        /* ------------------------------------------------ */
        /* Media Query para impresión (Fuerza el tamaño físico) */
        /* ------------------------------------------------ */
        @media print {
            .no-print { display: none !important; }
            
            /* IMPRESIÓN DE TARJETAS */
            body.print-cards .tarjeta-container {
                column-count: unset;
                column-gap: unset;
                display: flex;
                flex-wrap: wrap;
                justify-content: flex-start; 
                padding: 0;
            }

            body.print-cards .tarjeta {
                /* FUERZA EL TAMAÑO FÍSICO Y EXACTO PARA LA IMPRESIÓN */
                width: 8.5cm !important;
                height: 5.5cm !important;
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

            /* IMPRESIÓN DE LISTA DE DISTRIBUCIÓN */
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

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-white">
                        <h4 class="card-title text-gray-900 font-bold">Etiquetas de Examen Pre Universitario - UNAMAD</h4>
                    </div>
                    <div class="card-body">
                        <!-- BLOQUE DE BOTONES DE ACCIÓN (Añadido) -->
                        <div class="no-print mb-4 d-flex justify-content-between align-items-center flex-wrap">
                            <div class="d-flex" style="gap: 12px">
                                <button id="load-btn" class="btn btn-primary" disabled>
                                    <i class="fas fa-circle-notch fa-spin mr-2"></i> Cargando...
                                </button>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn btn-outline-secondary active" id="view-cards-btn">
                                        <i class="fas fa-id-card mr-2"></i> Tarjetas
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary" id="view-list-btn">
                                        <i class="fas fa-list mr-2"></i> Distribución
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary" id="view-building-btn">
                                        <i class="fas fa-building mr-2"></i> Simulación Edificio
                                    </button>
                                </div>
                            </div>
                            
                            <div class="d-flex" style="gap: 12px">
                                <button id="print-btn" class="btn btn-success" disabled>
                                    <i class="fas fa-print mr-2"></i> Imprimir
                                </button>
                                <!-- PDF Button removed as requested by user flow simplification, or keep if needed -->
                            </div>
                        </div>
                        
                        <!-- Contenedor de Tarjetas -->
                        <div id="tarjetas-container" class="tarjeta-container">
                             <p class="text-gray-500 text-center w-100 mt-4">Cargando datos...</p>
                        </div>

                        <!-- Contenedor de Lista de Distribución (Oculto por defecto) -->
                        <div id="distribucion-container" class="d-none">
                            <div class="table-responsive">
                                <div class="text-center mb-4">
                                    <h5 class="font-weight-bold">SEGUNDO EXAMEN DEL CEPRE-UNAMAD CICLO ORDINARIO 2025-1</h5>
                                    <h6 class="font-weight-bold">DISTRIBUCIÓN DEL NUMERO CARTILLA DE PREGUNTAS POR AULA, TEMA Y GRUPO:</h6>
                                </div>
                                <table class="table table-bordered table-sm text-center" id="tabla-distribucion">
                                    <thead class="thead-light">
                                        <tr>
                                            <th style="width: 10%">AULA</th>
                                            <th style="width: 10%">TEMA</th>
                                            <th style="width: 10%">GRUPO</th>
                                            <th style="width: 10%">CANTIDAD</th>
                                            <th style="width: 60%">DOCENTE</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Se llena con JS -->
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Contenedor de Simulación de Edificio (Oculto por defecto) -->
                        <div id="edificio-container" class="d-none">
                            <div class="alert alert-info d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="fas fa-info-circle mr-2"></i>
                                    Visualiza la ocupación, asigna docentes y gestiona las aulas.
                                </div>
                                <div>
                                    <button class="btn btn-sm btn-primary mr-2" onclick="abrirModalGestionAulas()">
                                        <i class="fas fa-cog mr-2"></i> Gestionar Aulas
                                    </button>
                                    <button class="btn btn-sm btn-warning" id="btn-distribuir">
                                        <i class="fas fa-random mr-2"></i> Generar Distribución Aleatoria
                                    </button>
                                </div>
                            </div>
                            <div id="edificio-grid">
                                <!-- Se llena con JS -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL DETALLE AULA (SIMULACIÓN CARPETAS) -->
    <div class="modal fade" id="modalDetalleAula" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalDetalleTitulo">Aula X</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body bg-light">
                    <div class="d-flex justify-content-between mb-3">
                        <div class="pizarra bg-dark text-white text-center py-2 rounded w-100 mx-5">
                            PIZARRA / DOCENTE
                        </div>
                    </div>
                    <div id="grid-carpetas" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap: 15px; padding: 20px;">
                        <!-- Se llena con JS -->
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
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <ul class="nav nav-tabs mb-3" id="gestionTab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="individual-tab" data-toggle="tab" href="#individual" role="tab">Individual</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="masivo-tab" data-toggle="tab" href="#masivo" role="tab">Agregar Piso Completo</a>
                        </li>
                    </ul>

                    <div class="tab-content" id="gestionTabContent">
                        <!-- TAB INDIVIDUAL -->
                        <div class="tab-pane fade show active" id="individual" role="tabpanel">
                            <form id="form-aula" class="mb-4 border p-3 rounded bg-light">
                                <h6 class="font-weight-bold mb-3">Agregar / Editar Aula</h6>
                                <div class="form-row">
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
                                        <button type="submit" class="btn btn-success btn-block">Guardar</button>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <!-- TAB MASIVO (PISO) -->
                        <div class="tab-pane fade" id="masivo" role="tabpanel">
                            <form id="form-piso" class="mb-4 border p-3 rounded bg-light">
                                <h6 class="font-weight-bold mb-3">Generar Piso Completo</h6>
                                <div class="alert alert-info small">
                                    Esto creará múltiples aulas automáticamente. Ej: Piso 3, Inicio 1, Cantidad 5 => Creará 301, 302, 303, 304, 305.
                                </div>
                                <div class="form-row">
                                    <div class="col-md-3 mb-2">
                                        <label class="small">N° Piso</label>
                                        <input type="number" class="form-control" id="piso-numero" placeholder="Ej. 3" required>
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <label class="small">Iniciar en (01)</label>
                                        <input type="number" class="form-control" id="piso-inicio" value="1" required>
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <label class="small">Cant. Aulas</label>
                                        <input type="number" class="form-control" id="piso-cantidad" value="5" required>
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <label class="small">Capacidad Default</label>
                                        <input type="number" class="form-control" id="piso-capacidad" value="40" required>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary btn-block">Generar Piso</button>
                            </form>
                        </div>
                    </div>
                    
                    <hr>
                    <h6 class="font-weight-bold">Aulas Existentes</h6>
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

@endsection

@push('js')
    <script src="https://unpkg.com/axios/dist/axios.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // Configurar Axios para CSRF
        const token = document.querySelector('meta[name="csrf-token"]');
        if (token) {
            axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
        } else {
            console.error('CSRF token not found: Asegúrate de que el meta tag csrf-token esté presente en el layout.');
        }

        const API_URLS = {
            CARGAR_DATOS: '{{ url('api/tarjetas-preuni') }}',
            EXPORTAR_PDF: '{{ route("tarjetas.exportar-pdf") }}',
            EDIFICIO_DATA: '{{ route("api.tarjetas.edificio") }}',
            DISTRIBUIR: '{{ route("api.tarjetas.distribuir") }}',
            GUARDAR_DOCENTE: '{{ route("api.tarjetas.guardar-docente") }}',
            AULA_DETALLE: '{{ url("api/tarjetas/aula") }}', // + /{id}
            GUARDAR_AULA: '{{ route("api.tarjetas.guardar-aula") }}',
            GUARDAR_PISO: '{{ route("api.tarjetas.guardar-piso") }}',
            ELIMINAR_AULA: '{{ url("api/tarjetas/aula") }}' // + /{id}
        };

        // Estado Global
        let state = {
            postulantes: [],
            edificio: {}, 
            isLoading: false,
            currentView: 'cards'
        };

        // ==========================================
        // FUNCIONES DE RENDERIZADO (TARJETAS)
        // ==========================================
        function getClaseTema(tema) {
            switch(tema) {
                case 'P': return 'tarjeta-p';
                case 'Q': return 'tarjeta-q';
                case 'R': return 'tarjeta-r';
                default: return 'tarjeta-r';
            }
        }

        function crearTarjetaHTML(postulante) {
            const { grupo, tema, codigo, aula, carrera, nombres, foto } = postulante;
            const claseTema = getClaseTema(tema);

            return `
                <div class="tarjeta ${claseTema} relative">
                    <div class="franja-tema"></div>
                    <div class="contenido-principal">
                        <div class="header-institucional" style="background-color: var(--color-unama-blue);">
                            <div style="display: flex; align-items: center; gap: 4px;">
                                <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/e/e6/UNAMAD_LOGO.png/200px-UNAMAD_LOGO.png" alt="UNAMAD Logo" style="height: 16px; width: 16px; object-fit: contain; background-color: white; border-radius: 50%; padding: 2px; box-shadow: 0 1px 2px rgba(0,0,0,0.2);"/>
                                <div style="font-size: 7px; line-height: 1; font-weight: 800; text-transform: uppercase; text-align: left;">
                                    UNAMAD / CENTRO PRE
                                </div>
                            </div>
                            <div style="font-size: 7px; font-weight: 700;">CICLO 2024-II</div>
                        </div>
                        <div class="ubicacion-clave">
                            <div class="aula-code">
                                <span>AULA / ROOM</span>
                                <div class="aula">${aula || '---'}</div>
                            </div>
                            <div class="aula-code separator">
                                <span>CÓDIGO / CODE</span>
                                <div class="codigo">${codigo || '---'}</div>
                            </div>
                        </div>
                        <div class="identificacion-detalle">
                            <div class="identificacion-fila" style="align-items: flex-start;">
                                <div class="foto-container">
                                    <img src="${foto || 'https://cdn.pixabay.com/photo/2015/10/05/22/37/blank-profile-picture-973460_960_720.png'}" alt="Foto" onerror="this.onerror=null;this.src='https://cdn.pixabay.com/photo/2015/10/05/22/37/blank-profile-picture-973460_960_720.png';"/>
                                </div>
                                <div class="datos-postulante">
                                    <span>Postulante</span>
                                    <div class="nombre-postulante">${nombres || 'SIN NOMBRE'}</div>
                                    <div class="carrera-postulante">${carrera || 'SIN CARRERA'}</div>
                                </div>
                            </div>
                            <div class="footer-grupo-tema">
                                <strong>GRUPO:</strong> ${grupo || '---'} | <strong>TEMA ASIGNADO:</strong> ${tema || '---'}
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        // ==========================================
        // FUNCIONES DE RENDERIZADO (EDIFICIO)
        // ==========================================
        function renderEdificio() {
            const container = document.getElementById('edificio-grid');
            if (!state.edificio.pisos) return;

            let html = '';
            
            Object.entries(state.edificio.pisos).forEach(([piso, aulas]) => {
                html += `
                    <div class="piso-row mb-4">
                        <div class="piso-label bg-dark text-white p-2 rounded mb-2 font-weight-bold">
                            <i class="fas fa-layer-group mr-2"></i> PISO ${piso}
                        </div>
                        <div class="d-flex flex-wrap" style="gap: 15px;">
                `;

                aulas.forEach(aula => {
                    const ocupacionPorc = aula.capacidad > 0 ? Math.round((aula.ocupacion_real / aula.capacidad) * 100) : 0;
                    const colorClass = ocupacionPorc >= 100 ? 'bg-danger text-white' : (ocupacionPorc > 50 ? 'bg-warning' : 'bg-light');
                    const borderClass = ocupacionPorc >= 100 ? 'border-danger' : 'border-secondary';

                    // Input de Docente (Datalist para sugerencias pero permite texto libre)
                    const docenteValue = aula.docente_invitado || aula.docente_nombre || '';
                    const datalistId = `list-docentes-${aula.id}`;

                    html += `
                        <div class="card aula-card shadow-sm ${borderClass}" style="width: 300px;">
                            <div class="card-header ${colorClass} py-1 px-2 d-flex justify-content-between align-items-center" 
                                 style="cursor: pointer;" onclick="verDetalleAula(${aula.id})">
                                <span class="font-weight-bold"><i class="fas fa-door-open mr-1"></i> Aula ${aula.nombre}</span>
                                <span class="badge badge-light" title="Ocupación">${aula.ocupacion_real}/${aula.capacidad}</span>
                            </div>
                            <div class="card-body p-2">
                                <div class="mb-2 text-center" onclick="verDetalleAula(${aula.id})" style="cursor: pointer;">
                                    <div class="progress" style="height: 10px;">
                                        <div class="progress-bar" role="progressbar" style="width: ${ocupacionPorc}%;" aria-valuenow="${ocupacionPorc}" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                    <small class="text-muted">Clic para ver distribución de carpetas</small>
                                </div>
                                
                                <div class="form-group mb-1">
                                    <label class="small font-weight-bold mb-0">Docente (Buscar o Escribir):</label>
                                    <input type="text" class="form-control form-control-sm" list="${datalistId}" 
                                           value="${docenteValue}" 
                                           placeholder="Nombre del docente..."
                                           onchange="asignarDocente(${aula.id}, this.value)">
                                    <datalist id="${datalistId}">
                                        ${state.edificio.docentes.map(d => `<option value="${d.id}">${d.nombre} ${d.apellido_paterno}</option>`).join('')}
                                    </datalist>
                                </div>

                                <div class="d-flex justify-content-between mt-2 small">
                                    <span><strong>Tema:</strong> ${aula.tema || '-'}</span>
                                    <span><strong>Grupo:</strong> ${aula.grupo || '-'}</span>
                                </div>
                            </div>
                        </div>
                    `;
                });

                html += `</div></div><hr>`;
            });

            container.innerHTML = html;
        }

        // ==========================================
        // LÓGICA DE NEGOCIO
        // ==========================================

        async function cargarDatos() {
            if (state.isLoading) return;
            state.isLoading = true;
            updateLoadingUI(true);

            try {
                const resTarjetas = await axios.get(API_URLS.CARGAR_DATOS);
                state.postulantes = Array.isArray(resTarjetas.data) ? resTarjetas.data : [];

                const resEdificio = await axios.get(API_URLS.EDIFICIO_DATA);
                state.edificio = resEdificio.data;

                // ACTUALIZAR TÍTULO DINÁMICO
                if (state.edificio.titulo_examen) {
                    const tituloEl = document.querySelector('#distribucion-container h5');
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
            const result = await Swal.fire({
                title: '¿Generar Distribución Aleatoria?',
                text: "Se reasignarán estudiantes a TODAS las aulas activas con capacidad > 0.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, distribuir'
            });

            if (result.isConfirmed) {
                state.isLoading = true;
                updateLoadingUI(true);
                try {
                    const response = await axios.post(API_URLS.DISTRIBUIR);
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

        async function asignarDocente(aulaId, valor) {
            try {
                await axios.post(API_URLS.GUARDAR_DOCENTE, {
                    aula_id: aulaId,
                    docente_valor: valor 
                });
                const Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 2000 });
                Toast.fire({ icon: 'success', title: 'Docente asignado' });
                // Actualizar localmente para reflejar en lista sin recargar todo
                // (Opcional, pero mejor recargar si queremos que la lista de distribución se actualice al instante)
                // await cargarDatos(); // Descomentar si se quiere consistencia inmediata en la otra pestaña
            } catch (error) {
                console.error(error);
                const msg = error.response?.data?.error || error.message || 'Error al asignar docente';
                Swal.fire('Error', msg, 'error');
            }
        }

        // --- GESTIÓN DE AULAS ---
        function abrirModalGestionAulas() {
            renderTablaGestionAulas();
            $('#modalGestionAulas').modal('show');
        }

        function renderTablaGestionAulas() {
            const tbody = document.getElementById('tbody-gestion-aulas');
            let html = '';
            // Aplanar aulas desde state.edificio.pisos
            const todasAulas = [];
            if (state.edificio.pisos) {
                Object.values(state.edificio.pisos).flat().forEach(a => todasAulas.push(a));
            }
            // Ordenar por nombre
            todasAulas.sort((a,b) => a.nombre.localeCompare(b.nombre, undefined, {numeric: true}));

            todasAulas.forEach(aula => {
                html += `
                    <tr>
                        <td>${aula.nombre}</td>
                        <td>${aula.piso}</td>
                        <td>${aula.capacidad}</td>
                        <td>
                            <button class="btn btn-sm btn-danger" onclick="eliminarAula(${aula.id})"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>
                `;
            });
            tbody.innerHTML = html;
        }

        document.getElementById('form-aula').addEventListener('submit', async function(e) {
            e.preventDefault();
            const nombre = document.getElementById('aula-nombre').value;
            const piso = document.getElementById('aula-piso').value;
            const capacidad = document.getElementById('aula-capacidad').value;

            try {
                await axios.post(API_URLS.GUARDAR_AULA, { nombre, piso, capacidad });
                // Recargar datos globales para actualizar edificio y tabla
                await cargarDatos(); 
                renderTablaGestionAulas(); // Actualizar tabla del modal
                document.getElementById('form-aula').reset();
                Swal.fire({ icon: 'success', title: 'Aula guardada', toast: true, position: 'top-end', timer: 1500, showConfirmButton: false });
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
                Swal.fire({ icon: 'success', title: res.data.message, toast: true, position: 'top-end', timer: 2000, showConfirmButton: false });
            } catch (error) {
                console.error(error);
                const msg = error.response?.data?.error || error.message || 'Error al generar piso';
                Swal.fire('Error', msg, 'error');
            }
        });

        async function eliminarAula(id) {
            if (!confirm('¿Desactivar esta aula?')) return;
            try {
                await axios.delete(`${API_URLS.ELIMINAR_AULA}/${id}`);
                await cargarDatos();
                renderTablaGestionAulas();
            } catch (error) {
                console.error(error);
                const msg = error.response?.data?.error || error.message || 'Error al eliminar';
                Swal.fire('Error', msg, 'error');
            }
        }

        // --- DETALLE AULA (SIMULACIÓN) ---
        async function verDetalleAula(id) {
            try {
                const res = await axios.get(`${API_URLS.AULA_DETALLE}/${id}`);
                const { aula, estudiantes } = res.data;

                document.getElementById('modalDetalleTitulo').innerText = `Aula ${aula.nombre} - Piso ${aula.piso}`;
                const grid = document.getElementById('grid-carpetas');
                grid.innerHTML = '';

                // Generar carpetas según capacidad (o estudiantes si excede)
                const totalSlots = Math.max(aula.capacidad, estudiantes.length);
                
                for (let i = 0; i < totalSlots; i++) {
                    const est = estudiantes[i];
                    let content = '';
                    
                    if (est) {
                        content = `
                            <div class="card h-100 border-primary shadow-sm">
                                <img src="${est.foto || 'https://cdn.pixabay.com/photo/2015/10/05/22/37/blank-profile-picture-973460_960_720.png'}" class="card-img-top" style="height: 80px; object-fit: cover;">
                                <div class="card-body p-1 text-center">
                                    <small class="font-weight-bold d-block text-truncate" style="font-size: 0.7rem;">${est.nombre_completo}</small>
                                    <span class="badge badge-info" style="font-size: 0.6rem;">${est.codigo}</span>
                                </div>
                            </div>
                        `;
                    } else {
                        content = `
                            <div class="card h-100 border-secondary bg-light" style="opacity: 0.5;">
                                <div class="card-body p-1 d-flex align-items-center justify-content-center">
                                    <small class="text-muted">Vacío</small>
                                </div>
                            </div>
                        `;
                    }
                    
                    const div = document.createElement('div');
                    div.innerHTML = content;
                    grid.appendChild(div);
                }

                $('#modalDetalleAula').modal('show');

            } catch (error) {
                console.error(error);
                const msg = error.response?.data?.error || error.message || 'Error al cargar detalle';
                Swal.fire('Error', msg, 'error');
            }
        }

        // ==========================================
        // UI HELPERS
        // ==========================================

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

        function updateUI() {
            // Renderizar Tarjetas
            const tarjetasContainer = document.getElementById('tarjetas-container');
            if (state.postulantes.length === 0) {
                tarjetasContainer.innerHTML = '<p class="text-center mt-4">No hay datos.</p>';
            } else {
                tarjetasContainer.innerHTML = state.postulantes.map(crearTarjetaHTML).join('');
            }

            // Renderizar Tabla Distribución
            renderDistribucionTabla(state.postulantes);

            // Renderizar Edificio
            renderEdificio();
        }

        function renderDistribucionTabla(postulantes) {
            const tbody = document.querySelector('#tabla-distribucion tbody');
            const grupos = {};
            postulantes.forEach(p => {
                const aula = p.aula || 'SIN AULA';
                if (!grupos[aula]) {
                    grupos[aula] = { aula: aula, tema: p.tema, grupo: p.grupo, cantidad: 0 };
                }
                grupos[aula].cantidad++;
            });
            const data = Object.values(grupos).sort((a, b) => a.aula.localeCompare(b.aula, undefined, { numeric: true }));

            if (data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5">Sin datos</td></tr>';
                return;
            }

            tbody.innerHTML = data.map(row => {
                // Buscar docente en state.edificio
                let docenteNombre = '';
                if (state.edificio.pisos) {
                    Object.values(state.edificio.pisos).flat().forEach(a => {
                        if (a.nombre == row.aula) {
                            // Prioridad: Invitado > Registrado
                            docenteNombre = a.docente_invitado || a.docente_nombre || '';
                        }
                    });
                }

                return `
                    <tr>
                        <td class="font-weight-bold text-xl">${row.aula}</td>
                        <td class="font-weight-bold text-xl">${row.tema}</td>
                        <td class="font-weight-bold text-xl">${row.grupo}</td>
                        <td class="font-weight-bold text-xl">${row.cantidad}</td>
                        <td class="text-left pl-4 text-lg">${docenteNombre}</td>
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

        function imprimir() {
            document.body.classList.remove('print-cards', 'print-list');
            if (state.currentView === 'cards') document.body.classList.add('print-cards');
            else document.body.classList.add('print-list'); 
            window.print();
        }

        window.onload = function() {
            document.getElementById('load-btn').addEventListener('click', cargarDatos);
            document.getElementById('print-btn').addEventListener('click', imprimir);
            
            document.getElementById('view-cards-btn').addEventListener('click', () => switchView('cards'));
            document.getElementById('view-list-btn').addEventListener('click', () => switchView('list'));
            document.getElementById('view-building-btn').addEventListener('click', () => switchView('building'));
            
            document.getElementById('btn-distribuir').addEventListener('click', generarDistribucion);

            cargarDatos();
        };
    </script>
@endpush
