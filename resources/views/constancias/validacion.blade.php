<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validación de Constancia - CEPRE UNAMAD</title>
    <!-- Carga del CDN de Tailwind CSS (Necesario para que funcionen las clases) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Configuración para usar la fuente Inter y un color principal personalizado -->
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap');
        
        /* Tema de Alto Contraste y Profesional: Diseño Split */
        body {
            font-family: 'Inter', sans-serif;
            background-color: #e2e8f0; /* Fondo azul/gris claro (slate-200) */
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        
        /* Sombra más profunda para el contenedor principal */
        .shadow-2xl-custom {
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
        
        /* Estilos para el botón principal */
        .btn-primary {
            background-color: #4f46e5; /* indigo-600 */
            transition: all 0.2s ease-in-out;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -2px rgba(0, 0, 0, 0.1);
        }
        .btn-primary:hover {
            background-color: #4338ca; /* indigo-700 */
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -4px rgba(0, 0, 0, 0.1);
        }

        /* Estilos específicos para la sección de datos */
        .data-card {
            border-left: 5px solid #a5b4fc; /* indigo-300 */
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4 md:p-10">

    <!-- Contenedor Principal con Cabecera Institucional (GRID para layout de dos columnas en Desktop) -->
    <div class="w-full max-w-7xl shadow-2xl-custom overflow-hidden rounded-xl bg-white">
        
        <!-- CABECERA DE LA INSTITUCIÓN: CEPRE UNAMAD -->
        <header class="bg-white border-b-2 border-indigo-100 p-4 sm:p-6 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <!-- Logo Placeholder (Reemplazar con el logo oficial) -->
                <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#4f46e5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-graduation-cap flex-shrink-0">
                    <path d="M21.43 11v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-6"/><path d="M22 10a1 1 0 0 0-1-1H3a1 1 0 0 0-1 1v1h20v-1z"/><path d="M12 2l10 8-10 8-10-8 10-8z"/>
                </svg>
                <div>
                    <h1 class="text-xl sm:text-2xl font-extrabold text-indigo-700 leading-tight">CENTRO PRE UNIVERSITARIO UNAMAD</h1>
                    <p class="text-xs text-gray-500 font-medium tracking-wider uppercase hidden sm:block">Sistema Oficial de Verificación de Constancias</p>
                </div>
            </div>
            <!-- Enlace a la UNAMAD (Ejemplo) -->
            <a href="#" class="text-sm font-semibold text-indigo-500 hover:text-indigo-700 transition duration-150">Sitio Web UNAMAD</a>
        </header>

        <!-- CONTENIDO PRINCIPAL DIVIDIDO -->
        <div class="grid grid-cols-1 md:grid-cols-4 lg:grid-cols-5">
            
            <!-- ============================== -->
            <!-- COLUMNA IZQUIERDA: ESTADO (1/4 o 1/5 del ancho) -->
            <!-- Fondo de color primario para autoridad -->
            <!-- ============================== -->
            <div class="md:col-span-1 lg:col-span-2 p-8 md:p-10 text-white flex flex-col justify-center min-h-[300px] md:min-h-full
                        @if($valida) bg-indigo-700 @else bg-red-700 @endif
                        ">

                @if($valida)
                    <!-- ESTADO VÁLIDO -->
                    <div class="flex flex-col items-center">
                        <!-- Icono de Check Circle (Inline SVG) -->
                        <svg xmlns="http://www.w3.org/2000/svg" width="96" height="96" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-shield-check text-green-300 mb-4">
                            <path d="M20 13V6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v7c0 4.7 6 7 6 7s6-2.3 6-7ZM9 12l2 2 4-4"/>
                        </svg>
                        <h2 class="text-4xl font-extrabold mb-1 tracking-tight text-white">VALIDADO</h2>
                        <p class="text-lg font-medium text-indigo-200">Datos Auténticos Certificados.</p>
                    </div>
                @else
                    <!-- ESTADO INVÁLIDO -->
                    <div class="flex flex-col items-center">
                        <!-- Icono de X Circle (Inline SVG) -->
                        <svg xmlns="http://www.w3.org/2000/svg" width="96" height="96" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-shield-x text-red-300 mb-4">
                            <path d="M20 13V6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v7c0 4.7 6 7 6 7s6-2.3 6-7ZM10 12l4 4M14 12l-4 4"/>
                        </svg>
                        <h2 class="text-4xl font-extrabold mb-1 tracking-tight text-white">INVÁLIDO</h2>
                        <p class="text-lg font-medium text-red-200">Certificación Inexistente.</p>
                    </div>
                @endif
            </div>
            
            <!-- ============================== -->
            <!-- COLUMNA DERECHA: DETALLES Y ARCHIVO (3/4 o 4/5 del ancho) -->
            <!-- Fondo blanco para contenido de lectura -->
            <!-- ============================== -->
            <div class="md:col-span-3 lg:col-span-3 p-8 md:p-12 bg-white text-left">

                @if($valida)
                    <!-- Contenido Válido -->
                    @php
                        // 1. Intentamos obtener la ruta del PDF. Asumimos 'constancia_firmada_path'
                        $pdfPath = $datos['constancia_firmada_path'] ?? null;
                        
                        // 2. Generamos la URL pública segura SOLO si la ruta existe.
                        $pdfUrl = $pdfPath ? Storage::url($pdfPath) : null; 
                    @endphp

                    <h3 class="text-3xl font-bold text-gray-800 mb-6 border-b pb-2 border-gray-200">Detalles de la Constancia Verificada</h3>

                    <!-- Detalles de la Constancia (Diseño de datos limpios) -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-6 text-gray-700 text-sm mb-10">
                        
                        <!-- Estudiante -->
                        <div class="data-card bg-white p-3 rounded-lg shadow-sm">
                            <p class="text-gray-600 font-medium uppercase text-xs">Estudiante</p> 
                            <p class="font-bold text-indigo-700 text-lg leading-tight">{{ $datos['estudiante']['nombre'] }} {{ $datos['estudiante']['apellido_paterno'] }} {{ $datos['estudiante']['apellido_materno'] }}</p>
                            <p class="text-xs text-gray-500 mt-1">DNI: {{ $datos['estudiante']['numero_documento'] }}</p>
                        </div>
                        
                        <!-- Información Académica -->
                        <div class="data-card bg-white p-3 rounded-lg shadow-sm">
                            <p class="text-gray-600 font-medium uppercase text-xs">Carrera y Ciclo</p> 
                            <p class="font-bold text-gray-800 text-lg leading-tight">{{ $datos['carrera']['nombre'] }}</p>
                            <p class="text-xs text-gray-500 mt-1">Ciclo: {{ $datos['ciclo']['nombre'] }}</p>
                        </div>

                        <!-- ID de Certificación -->
                        <div class="data-card bg-white p-3 rounded-lg shadow-sm">
                            <p class="text-gray-600 font-medium uppercase text-xs">ID de Certificación</p> 
                            <p class="font-extrabold text-indigo-800 text-xl tracking-wider">{{ $datos['numero_constancia'] }}</p>
                            <p class="text-xs text-gray-500 mt-1">Generada: {{ \Carbon\Carbon::parse($fecha_generacion)->format('d/m/Y H:i') }}</p>
                        </div>
                        
                        <!-- Modalidad de Estudios -->
                        @if(isset($datos['turno']))
                            <div class="data-card bg-white p-3 rounded-lg shadow-sm">
                                <p class="text-gray-600 font-medium uppercase text-xs">Modalidad de Estudios</p>
                                <p class="font-bold text-gray-800 text-lg leading-tight">{{ ucfirst($tipo) }} (Turno: {{ $datos['turno']['nombre'] }})</p>
                            </div>
                        @endif
                    </div>

                    <!-- **SECCIÓN DE VISUALIZACIÓN DEL DOCUMENTO PDF** -->
                    <div class="mb-8 p-4 bg-gray-50 rounded-xl border border-gray-200 shadow-inner">
                        <h5 class="text-xl font-bold text-gray-700 mb-3 border-b pb-2 border-gray-300 flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-file-text mr-2 text-indigo-500"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><path d="M14 2v4a2 2 0 0 0 2 2h4"/><path d="M10 9H8"/><path d="M16 13H8"/><path d="M16 17H8"/></svg>
                            Vista Previa Digital
                        </h5>
                        
                        <div class="border border-gray-300 rounded-lg overflow-hidden w-full mx-auto shadow-md" style="min-height: 400px; max-height: 70vh;">
                            
                            @if($pdfUrl)
                                <!-- Muestra el PDF si la ruta es válida y existe -->
                                <iframe 
                                    src="{{ $pdfUrl }}" 
                                    class="w-full h-full" 
                                    frameborder="0"
                                    title="Constancia Oficial Verificada">
                                </iframe>
                            @else
                                <!-- Mensaje si el PDF no está cargado -->
                                <div class="w-full h-full flex flex-col items-center justify-center bg-gray-100 text-gray-500 p-8">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-file-warning text-red-500 mb-3"><path d="M14.5 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7.5L14.5 2z"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>
                                    <p class="text-md font-semibold text-gray-700 mb-1">Archivo PDF No Cargado</p>
                                    <p class="text-center max-w-sm text-xs">La verificación es correcta, pero el archivo PDF firmado no está disponible para visualización.</p>
                                </div>
                            @endif

                        </div>

                        <p class="text-xs text-gray-400 mt-3 text-center italic">La verificación digital asegura la integridad. El documento físico es la única versión con validez legal completa.</p>
                    </div>
                    <!-- Fin de Vista Previa -->

                @else
                    <!-- Contenido Inválido -->
                    <h3 class="text-3xl font-bold text-red-700 mb-6 border-b pb-2 border-red-200">Motivo del Error</h3>
                    <p class="text-lg text-gray-700 mb-8">
                        {{ $mensaje ?? 'El código de verificación proporcionado no coincide con ninguna constancia registrada en el sistema. Por favor, asegúrese de haber ingresado el código correctamente.' }}
                    </p>
                    
                    <!-- Sección de Ayuda/Contacto para constancias inválidas -->
                    <div class="mt-8 p-6 bg-red-50 rounded-lg border border-red-300 shadow-sm">
                        <p class="text-lg font-bold text-red-800 flex items-center justify-center mb-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-help-circle mr-2"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.8 1c0 2-3 3-3 3"/><path d="M12 17h.01"/></svg>
                            ¿Necesita Asistencia?
                        </p>
                        <p class="text-base text-red-700 mt-2">
                            Si considera que este mensaje es un error, contacte con la oficina de secretaría de la institución para soporte inmediato y verificación manual.
                        </p>
                    </div>
                @endif

                <!-- Botón de Acción Común -->
                <div class="mt-12 text-center md:text-left">
                    <a href="{{ url('/') }}" class="btn-primary inline-flex items-center justify-center px-8 py-3 text-lg font-bold rounded-lg text-white focus:outline-none focus:ring-4 focus:ring-indigo-500 focus:ring-opacity-50">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-home mr-2">
                            <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><path d="M9 22V12h6v10"/>
                        </svg>
                        Volver al Inicio
                    </a>
                </div>

            </div>
            <!-- FIN DE COLUMNA DERECHA -->

        </div>
        <!-- FIN DE CONTENIDO PRINCIPAL DIVIDIDO -->

    </div>

</body>
</html>
