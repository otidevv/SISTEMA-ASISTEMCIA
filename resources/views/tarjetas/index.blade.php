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
        @media print {
            .no-print { display: none !important; }
            
            .tarjeta-container {
                column-count: unset;
                column-gap: unset;
                display: flex;
                flex-wrap: wrap;
                justify-content: flex-start; 
                padding: 0;
            }

            .tarjeta {
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
                        <div class="no-print mb-4 d-flex" style="gap: 12px">
                            <button
                                id="load-btn"
                                class="btn btn-primary"
                                disabled
                            >
                                <i class="fas fa-circle-notch fa-spin mr-2"></i> Cargando...
                            </button>
                            <button
                                id="print-btn"
                                class="btn btn-success"
                                disabled
                            >
                                <i class="fas fa-print mr-2"></i> Imprimir Etiquetas
                            </button>
                            <button
                                id="pdf-btn"
                                class="btn btn-danger"
                                disabled
                            >
                                <i class="fas fa-file-pdf mr-2"></i> Exportar PDF
                            </button>
                        </div>
                        
                        <!-- Contenedor donde se renderizarán las tarjetas con Vanilla JS -->
                        <div id="tarjetas-container" class="tarjeta-container">
                             <p class="text-gray-500 text-center w-100 mt-4">Cargando datos...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <!-- Carga Axios para peticiones HTTP -->
    <script src="https://unpkg.com/axios/dist/axios.min.js"></script>

    <!-- Inyectar las URLs de Laravel de forma segura -->
    <script>
        const CARGAR_DATOS_URL = '{{ url('api/tarjetas-preuni') }}';
        const EXPORTAR_PDF_URL = '{{ route("tarjetas.exportar-pdf") }}'; // Asegúrate que esta ruta esté definida
    </script>

    <script>
        // Variables globales para manejar el estado (reemplazo de useState de React)
        let postulantesData = [];
        let isLoading = false;

        // Función auxiliar para obtener la clase de tema
        function getClaseTema(tema) {
            switch(tema) {
                case 'P': return 'tarjeta-p';
                case 'Q': return 'tarjeta-q';
                case 'R': return 'tarjeta-r';
                default: return 'tarjeta-r';
            }
        }

        // Función que genera el HTML de una sola tarjeta (reemplazo del componente Tarjeta)
        function crearTarjetaHTML(postulante) {
            // Desestructuración de datos con fallbacks
            const { grupo, tema, codigo, aula, carrera, nombres, foto } = postulante;
            const claseTema = getClaseTema(tema);

            return `
                <div class="tarjeta ${claseTema} relative">
                    <!-- Franja de Color Vertical (Tema) -->
                    <div class="franja-tema"></div>

                    <!-- Contenido Principal -->
                    <div class="contenido-principal">
                        
                        <!-- 1. HEADER INSTITUCIONAL -->
                        <div class="header-institucional" style="background-color: var(--color-unama-blue);">
                            <div style="display: flex; align-items: center; gap: 4px;">
                                <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/e/e6/UNAMAD_LOGO.png/200px-UNAMAD_LOGO.png" alt="UNAMAD Logo" style="height: 16px; width: 16px; object-fit: contain; background-color: white; border-radius: 50%; padding: 2px; box-shadow: 0 1px 2px rgba(0,0,0,0.2);"/>
                                <div style="font-size: 7px; line-height: 1; font-weight: 800; text-transform: uppercase; text-align: left;">
                                    UNAMAD / CENTRO PRE
                                </div>
                            </div>
                            <div style="font-size: 7px; font-weight: 700;">CICLO 2024-II</div>
                        </div>

                        <!-- 2. UBICACIÓN CLAVE (Grande y visible) -->
                        <div class="ubicacion-clave">
                            
                            <!-- AULA -->
                            <div class="aula-code">
                                <span>AULA / ROOM</span>
                                <div class="aula">${aula || '---'}</div>
                            </div>
                            
                            <!-- CÓDIGO -->
                            <div class="aula-code separator">
                                <span>CÓDIGO / CODE</span>
                                <div class="codigo">${codigo || '---'}</div>
                            </div>
                        </div>

                        <!-- 3. IDENTIFICACIÓN FOTOGRÁFICA Y DETALLES -->
                        <div class="identificacion-detalle">
                            <div class="identificacion-fila" style="align-items: flex-start;">
                                <!-- Foto -->
                                <div class="foto-container">
                                    <img
                                        src="${foto || 'https://cdn.pixabay.com/photo/2015/10/05/22/37/blank-profile-picture-973460_960_720.png'}"
                                        alt="Foto del estudiante"
                                        onerror="this.onerror=null;this.src='https://cdn.pixabay.com/photo/2015/10/05/22/37/blank-profile-picture-973460_960_720.png';"
                                    />
                                </div>
                                <!-- Nombre y Carrera -->
                                <div class="datos-postulante">
                                    <span>Postulante</span>
                                    
                                    <!-- SOLUCIÓN: Nombre en dos líneas -->
                                    <div class="nombre-postulante">${nombres || 'SIN NOMBRE'}</div>
                                    
                                    <!-- SOLUCIÓN: Carrera truncada con puntos suspensivos -->
                                    <div class="carrera-postulante">${carrera || 'SIN CARRERA'}</div>
                                    
                                </div>
                            </div>
                            
                            <!-- Grupo y Tema (Texto) -->
                            <div class="footer-grupo-tema">
                                <strong>GRUPO:</strong> ${grupo || '---'} | <strong>TEMA ASIGNADO:</strong> ${tema || '---'}
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }
        
        // Función principal para actualizar el DOM con los datos
        function updateUI(postulantes) {
            const container = document.getElementById('tarjetas-container');
            const loadBtn = document.getElementById('load-btn');
            const printBtn = document.getElementById('print-btn');
            const pdfBtn = document.getElementById('pdf-btn');

            if (postulantes.length === 0) {
                container.innerHTML = '<p class="text-gray-500 text-center w-100 mt-4">No se encontraron datos de postulantes.</p>';
                printBtn.disabled = true;
                pdfBtn.disabled = true;
            } else {
                const htmlContent = postulantes.map(crearTarjetaHTML).join('');
                container.innerHTML = htmlContent;
                printBtn.disabled = false;
                pdfBtn.disabled = false;
            }

            // Actualizar estado del botón
            isLoading = false;
            loadBtn.innerHTML = '<i class="fas fa-sync-alt mr-2"></i> Recargar Datos';
            loadBtn.classList.remove('btn-secondary', 'btn-danger');
            loadBtn.classList.add('btn-primary');
            loadBtn.disabled = false;
        }


        // Función para manejar la carga de datos (reemplazo de cargarDatos de React)
        async function cargarDatos() {
            if (isLoading) return;
            isLoading = true;

            const container = document.getElementById('tarjetas-container');
            const loadBtn = document.getElementById('load-btn');
            
            // Actualizar UI de carga
            loadBtn.innerHTML = '<i class="fas fa-circle-notch fa-spin mr-2"></i> Cargando...';
            loadBtn.classList.remove('btn-primary', 'btn-danger');
            loadBtn.classList.add('btn-secondary');
            loadBtn.disabled = true;
            container.innerHTML = '<p class="text-gray-500 text-center w-100 mt-4"><i class="fas fa-spinner fa-spin mr-2"></i> Obteniendo datos de la API...</p>';


            try {
                const response = await axios.get(CARGAR_DATOS_URL);
                
                // Manejo flexible de la respuesta (array o paginación)
                const data = Array.isArray(response.data) ? response.data : 
                             (Array.isArray(response.data.data) ? response.data.data : []);

                postulantesData = data; // Guardar datos globalmente
                updateUI(postulantesData);

            } catch (error) {
                console.error('Error al cargar datos:', error);
                const errorMessage = error.response && error.response.data && error.response.data.message
                    ? `Error de la API: ${error.response.data.message}`
                    : 'Error de red. Revise la consola.';
                
                container.innerHTML = `<div class="alert alert-danger w-100 mt-4" role="alert">${errorMessage}</div>`;
                
                // Restablecer estado en caso de error
                isLoading = false;
                loadBtn.innerHTML = '<i class="fas fa-times-circle mr-2"></i> Error al Cargar';
                loadBtn.classList.remove('btn-secondary');
                loadBtn.classList.add('btn-danger');
                loadBtn.disabled = false;
            }
        }

        // Función para imprimir
        function imprimir() {
            window.print();
        }

        // Función para exportar PDF
        async function exportarPDF() {
            if (postulantesData.length === 0) {
                alert('No hay datos para exportar.');
                return;
            }

            const pdfBtn = document.getElementById('pdf-btn');
            pdfBtn.disabled = true;
            pdfBtn.innerHTML = '<i class="fas fa-circle-notch fa-spin mr-2"></i> Exportando...';

            try {
                const response = await axios.post(EXPORTAR_PDF_URL, {
                    postulantes: postulantesData
                }, {
                    responseType: 'blob' // Importante para manejar archivos binarios
                });

                const url = window.URL.createObjectURL(new Blob([response.data]));
                const link = document.createElement('a');
                link.href = url;
                link.setAttribute('download', 'etiquetas_examen_preuni_' + new Date().toISOString().split('T')[0] + '.pdf');
                document.body.appendChild(link);
                link.click();
                link.remove();
                
            } catch (error) {
                console.error('Error al exportar PDF:', error);
                alert('Error al exportar PDF. Asegúrese de que la ruta y la lógica del controlador de Laravel sean correctas.');
            } finally {
                pdfBtn.disabled = false;
                pdfBtn.innerHTML = '<i class="fas fa-file-pdf mr-2"></i> Exportar PDF';
            }
        }
        
        // Asignar funciones a los botones y cargar datos al inicio
        window.onload = function() {
            document.getElementById('load-btn').addEventListener('click', cargarDatos);
            document.getElementById('print-btn').addEventListener('click', imprimir);
            document.getElementById('pdf-btn').addEventListener('click', exportarPDF);
            cargarDatos();
        };

    </script>
@endpush