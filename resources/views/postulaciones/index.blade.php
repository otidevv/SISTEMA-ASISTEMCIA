@extends('layouts.app')

@section('title', 'Gestión de Postulaciones')

@push('css')
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.14.5/dist/sweetalert2.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css"
        rel="stylesheet" />
    <style>
        /* Paleta de colores institucional basada en el logo CEPRE UNAMAD */
        .postulaciones-container {
            --cepre-magenta: #e91e63;
            --cepre-cyan: #00bcd4;
            --cepre-green: #8bc34a;
            --cepre-gold: #ffd700;
            --cepre-navy: #1a237e;
            --cepre-dark-blue: #0d47a1;
            --cepre-light-gray: #f8f9fa;
            --cepre-dark-gray: #455a64;
            --cepre-shadow: rgba(26, 35, 126, 0.15);
        }

        /* Estilos originales mantenidos */
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
        
        /* Estilos para los nuevos modales */
        .hover-card {
            transition: all 0.3s ease;
            border-width: 2px !important;
        }
        .hover-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        #modalSeleccionTipo .card.selected {
            background-color: #f0f8ff;
            border-color: #007bff !important;
            border-width: 3px !important;
        }
        .wizard-step-indicator {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            position: relative;
        }
        .wizard-step-indicator::before {
            content: '';
            position: absolute;
            top: 20px;
            left: 25px;
            right: 25px;
            height: 2px;
            background: #dee2e6;
            z-index: 0;
        }
        .wizard-step {
            text-align: center;
            position: relative;
            z-index: 1;
        }
        .wizard-step-number {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #fff;
            border: 2px solid #dee2e6;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px;
            font-weight: bold;
        }
        .wizard-step.active .wizard-step-number {
            background: #007bff;
            color: #fff;
            border-color: #007bff;
        }
        .wizard-step.completed .wizard-step-number {
            background: #28a745;
            color: #fff;
            border-color: #28a745;
        }
        .wizard-step-title {
            font-size: 12px;
            color: #6c757d;
        }
        .wizard-step.active .wizard-step-title {
            color: #007bff;
            font-weight: 600;
        }
        .wizard-step.completed .wizard-step-title {
            color: #28a745;
        }

        /* Estilos institucionales CEPRE para formularios */
        .cepre-form-container {
            background: linear-gradient(135deg, #f8f9fa 0%, #eceff1 100%);
            border-radius: 20px;
            padding: 2rem;
        }

        .cepre-form-container .form-select,
        .cepre-form-container .form-control {
            border: 2px solid #e1e5e9;
            border-radius: 16px;
            padding: 1rem 1.5rem;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.95);
            font-size: 1rem;
            font-weight: 500;
        }

        .cepre-form-container .form-select:focus,
        .cepre-form-container .form-control:focus {
            border-color: var(--cepre-cyan);
            box-shadow: 0 0 0 0.25rem rgba(0, 188, 212, 0.15);
            background: white;
            transform: translateY(-2px);
        }

        .cepre-form-container .form-label {
            font-weight: 700;
            color: var(--cepre-navy);
            margin-bottom: 1rem;
            font-size: 1.1rem;
            letter-spacing: -0.2px;
        }

        /* Botón de inscripción institucional */
        .btn-cepre-inscribir {
            background: linear-gradient(135deg, #ff1976 0%, #0d47a1 100%);
            border: 2px solid #ff1976;
            color: white !important;
            font-weight: 700;
            padding: 1.5rem 4rem;
            border-radius: 50px;
            font-size: 1.2rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            box-shadow: 0 8px 32px rgba(255, 25, 118, 0.4);
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.4);
        }

        .btn-cepre-inscribir:hover,
        .btn-cepre-inscribir:focus,
        .btn-cepre-inscribir:active {
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 16px 48px rgba(255, 25, 118, 0.6);
            color: white !important;
            background: linear-gradient(135deg, #e91e63 0%, #1a237e 100%);
            border-color: #e91e63;
        }

        .btn-cepre-inscribir::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.8s;
        }

        .btn-cepre-inscribir:hover::before {
            left: 100%;
        }

        /* Loading spinner institucional */
        .cepre-spinner {
            width: 60px;
            height: 60px;
            border: 4px solid rgba(233, 30, 99, 0.1);
            border-left: 4px solid var(--cepre-magenta);
            border-radius: 50%;
            animation: cepreSpinAnimation 1s linear infinite;
        }

        @keyframes cepreSpinAnimation {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* SweetAlert2 personalizado */
        .swal2-popup {
            border-radius: 20px !important;
            font-family: inherit !important;
        }

        .swal2-title {
            color: var(--cepre-navy) !important;
            font-weight: 700 !important;
        }

        .swal2-confirm {
            background: linear-gradient(135deg, var(--cepre-magenta) 0%, var(--cepre-navy) 100%) !important;
            border-radius: 12px !important;
            padding: 0.8rem 2rem !important;
            font-weight: 600 !important;
            color: white !important;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3) !important;
            border: none !important;
        }

        .swal2-cancel {
            background: linear-gradient(135deg, var(--cepre-dark-gray) 0%, #607d8b 100%) !important;
            border-radius: 12px !important;
            padding: 0.8rem 2rem !important;
            font-weight: 600 !important;
            color: white !important;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3) !important;
            border: none !important;
        }
        
        /* Agregar estilos adicionales para asegurar visibilidad del texto */
        .btn-cepre-inscribir i,
        .btn-cepre-inscribir span,
        .btn-cepre-inscribir * {
            color: white !important;
        }

        /* Estilos para el Wizard de Registro */
        .wizard-progress .step-indicator {
            text-align: center;
            flex: 1;
            position: relative;
        }

        .wizard-progress .step-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e9ecef;
            border: 2px solid #dee2e6;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
            position: relative;
            transition: all 0.3s ease;
        }

        .wizard-progress .step-indicator.active .step-circle {
            background: var(--cepre-magenta);
            border-color: var(--cepre-magenta);
            color: white;
            transform: scale(1.1);
        }

        .wizard-progress .step-indicator.completed .step-circle {
            background: var(--cepre-green);
            border-color: var(--cepre-green);
            color: white;
        }

        .wizard-progress .step-label {
            margin-top: 8px;
            font-size: 0.875rem;
            font-weight: 500;
            color: #6c757d;
        }

        .wizard-progress .step-indicator.active .step-label {
            color: var(--cepre-magenta);
            font-weight: 700;
        }

        .wizard-progress .step-indicator.completed .step-label {
            color: var(--cepre-green);
            font-weight: 600;
        }

        .wizard-progress .progress-line {
            height: 2px;
            background: #dee2e6;
            flex: 1;
            margin: 0 10px;
            align-self: center;
            position: relative;
            top: -15px;
        }

        .wizard-progress .step-progress-mini {
            width: 100%;
            height: 3px;
            background: #e9ecef;
            border-radius: 3px;
            margin-top: 5px;
            overflow: hidden;
        }

        .wizard-progress .mini-progress-bar {
            height: 100%;
            background: linear-gradient(135deg, var(--cepre-cyan) 0%, var(--cepre-magenta) 100%);
            border-radius: 3px;
            transition: width 0.3s ease;
        }

        .wizard-step {
            min-height: 400px;
        }

        /* Animaciones para el wizard */
        .wizard-step {
            opacity: 0;
            transform: translateX(20px);
            transition: all 0.3s ease;
        }

        .wizard-step[style*="block"] {
            opacity: 1;
            transform: translateX(0);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .btn-cepre-inscribir {
                padding: 1.2rem 2.5rem;
                font-size: 1rem;
            }
            
            .wizard-progress .step-circle {
                width: 35px;
                height: 35px;
            }
            
            .wizard-progress .step-label {
                font-size: 0.75rem;
            }
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
        // Variables globales para el flujo de postulación
        let tipoPostulante = null; // 'nuevo' o 'existente'
        let postulanteData = null; // Datos del postulante si es existente
        
        // Función para abrir el modal de nueva postulación unificada
        document.addEventListener('DOMContentLoaded', function() {
            const btnNuevaPostulacion = document.getElementById('btn-nueva-postulacion-unificada');
            const modalSeleccionTipo = document.getElementById('modalSeleccionTipo');
            const modalBuscarPostulante = document.getElementById('modalBuscarPostulante');
            const modalNuevaPostulacion = document.getElementById('nuevaPostulacionModal');
            
            // Botón principal - Abre modal de selección de tipo
            if (btnNuevaPostulacion) {
                btnNuevaPostulacion.addEventListener('click', function() {
                    const modal = new bootstrap.Modal(modalSeleccionTipo);
                    modal.show();
                });
            }
            
            // Botón Postulante Nuevo
            document.getElementById('btnPostulanteNuevo').addEventListener('click', function() {
                tipoPostulante = 'nuevo';
                bootstrap.Modal.getInstance(modalSeleccionTipo).hide();
                
                // Mostrar modal de registro para crear cuenta primero
                mostrarModalRegistroNuevo();
            });
            
            // Botón Postulante Existente
            document.getElementById('btnPostulanteExistente').addEventListener('click', function() {
                tipoPostulante = 'existente';
                bootstrap.Modal.getInstance(modalSeleccionTipo).hide();
                
                // Abrir modal de búsqueda
                const modalBuscar = new bootstrap.Modal(modalBuscarPostulante);
                modalBuscar.show();
            });
            
            // Botón Buscar por DNI
            document.getElementById('btnBuscarPorDNI').addEventListener('click', function() {
                const dni = document.getElementById('dniPostulanteExistente').value;
                
                if (dni.length !== 8) {
                    toastr.error('El DNI debe tener 8 dígitos', 'Error');
                    return;
                }
                
                buscarPostulanteExistente(dni);
            });
            
            // Botón Volver a Selección
            document.getElementById('btnVolverSeleccion').addEventListener('click', function() {
                bootstrap.Modal.getInstance(modalBuscarPostulante).hide();
                const modalSeleccion = new bootstrap.Modal(modalSeleccionTipo);
                modalSeleccion.show();
                
                // Limpiar búsqueda
                document.getElementById('dniPostulanteExistente').value = '';
                document.getElementById('resultadoBusqueda').style.display = 'none';
                document.getElementById('btnContinuarPostulacion').style.display = 'none';
            });
            
            // Botón Continuar con Postulación (para existentes)
            document.getElementById('btnContinuarPostulacion').addEventListener('click', function() {
                bootstrap.Modal.getInstance(modalBuscarPostulante).hide();
                loadFormularioCompleto('existente');
            });
            
            // Función para buscar postulante existente
            function buscarPostulanteExistente(dni) {
                const resultadoDiv = document.getElementById('resultadoBusqueda');
                const btnContinuar = document.getElementById('btnContinuarPostulacion');
                
                // Mostrar loading
                resultadoDiv.innerHTML = '<div class="text-center"><div class="spinner-border spinner-border-sm"></div> Buscando...</div>';
                resultadoDiv.style.display = 'block';
                
                // Hacer petición AJAX
                fetch(`{{ url('/api/postulantes/buscar') }}/${dni}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.postulante) {
                        postulanteData = data.postulante;
                        
                        // Mostrar datos del postulante encontrado
                        resultadoDiv.innerHTML = `
                            <div class="alert alert-success">
                                <h6 class="mb-2"><i class="mdi mdi-check-circle"></i> Postulante encontrado</h6>
                                <p class="mb-1"><strong>Nombre:</strong> ${data.postulante.nombre} ${data.postulante.apellido_paterno} ${data.postulante.apellido_materno}</p>
                                <p class="mb-1"><strong>DNI:</strong> ${data.postulante.numero_documento}</p>
                                <p class="mb-0"><strong>Email:</strong> ${data.postulante.email}</p>
                            </div>
                        `;
                        
                        btnContinuar.style.display = 'block';
                        btnContinuar.disabled = false;
                    } else {
                        resultadoDiv.innerHTML = `
                            <div class="alert alert-warning">
                                <i class="mdi mdi-alert"></i> No se encontró un postulante con DNI: ${dni}
                                <br><small class="text-muted">Puede crear una nueva cuenta seleccionando "Soy Postulante Nuevo"</small>
                            </div>
                        `;
                        btnContinuar.style.display = 'none';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    resultadoDiv.innerHTML = '<div class="alert alert-danger">Error al buscar el postulante</div>';
                    btnContinuar.style.display = 'none';
                });
            }
            
            // ============================================
            // EVENT LISTENERS PARA REGISTRO DE NUEVOS POSTULANTES
            // ============================================
            
            // ============================================
            // EVENT LISTENERS PARA WIZARD DE REGISTRO COMPLETO
            // ============================================
            
            // Event listeners para consultar RENIEC
            document.getElementById('btnConsultarReniecNuevo').addEventListener('click', consultarReniecRegistro);
            document.getElementById('btnConsultarReniecPadre').addEventListener('click', consultarReniecPadre);
            document.getElementById('btnConsultarReniecMadre').addEventListener('click', consultarReniecMadre);
            
            // Event listeners para actualizar contadores en tiempo real
            const formRegistro = document.getElementById('formRegistroNuevo');
            formRegistro.addEventListener('input', function(e) {
                // Determinar el paso actual y actualizar su contador
                actualizarContadorCamposPaso(wizardCurrentStep);
            });
            formRegistro.addEventListener('change', function(e) {
                actualizarContadorCamposPaso(wizardCurrentStep);
            });
            
            // Validación de contraseñas en tiempo real
            document.getElementById('nuevo_password_confirmation').addEventListener('input', validarPasswordsRegistro);
            
            // Prevenir submit directo del formulario (se maneja con los botones del wizard)
            formRegistro.addEventListener('submit', function(e) {
                e.preventDefault();
            });
            
            // Variables globales para el formulario integrado
            let cicloActivo = null;
            let colegioSeleccionado = null;
            let currentFormData = null;
            
            // Función para cargar el formulario completo - Integrado con dashboard
            function loadFormularioCompleto(tipo) {
                const modalPostulacion = new bootstrap.Modal(modalNuevaPostulacion);
                const container = document.getElementById('postulacion-form-container');
                const titulo = document.getElementById('tituloModalPostulacion');
                
                // Actualizar título según el tipo
                if (tipo === 'nuevo') {
                    titulo.textContent = 'Nueva Postulación - Registro y Datos Completos';
                } else {
                    titulo.textContent = 'Nueva Postulación - ' + (postulanteData ? postulanteData.nombre : 'Postulante Existente');
                }
                
                // Mostrar modal
                modalPostulacion.show();
                
                // Mostrar loading
                container.innerHTML = `
                    <div class="text-center py-4">
                        <div class="cepre-spinner"></div>
                        <p class="mt-3 text-muted">Preparando formulario de postulación...</p>
                    </div>
                `;
                
                // Cargar datos necesarios para el formulario
                Promise.all([
                    fetch('{{ url("/json/inscripciones-estudiante/ciclo-activo") }}').then(r => r.json()),
                    fetch('{{ url("/json/inscripciones-estudiante/departamentos") }}').then(r => r.json())
                ])
                .then(([cicloData, departamentosData]) => {
                    if (cicloData.success && departamentosData.success) {
                        cicloActivo = cicloData.ciclo;
                        generarFormularioDirecto(tipo, cicloData, departamentosData);
                    } else {
                        throw new Error('Error al cargar datos del formulario');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    container.innerHTML = '<div class="alert alert-danger">Error al cargar el formulario. Por favor, inténtelo nuevamente.</div>';
                });
            }
            
            // Función para generar el formulario completo directamente
            function generarFormularioDirecto(tipo, cicloData, departamentosData) {
                const container = document.getElementById('postulacion-form-container');
                
                // Generar opciones para los selectores
                let carrerasOptions = '<option value="">Seleccione una carrera...</option>';
                cicloData.carreras.forEach(carrera => {
                    const vacantesText = carrera.vacantes_disponibles === 'Sin límite' ?
                        'Sin límite' : `${carrera.vacantes_disponibles} vacantes`;
                    carrerasOptions += `<option value="${carrera.id}" ${!carrera.tiene_vacantes ? 'disabled' : ''}>
                        ${carrera.nombre} (${vacantesText})
                    </option>`;
                });

                let turnosOptions = '<option value="">Seleccione un turno...</option>';
                cicloData.turnos.forEach(turno => {
                    turnosOptions += `<option value="${turno.id}">${turno.nombre} (${turno.hora_inicio} - ${turno.hora_fin})</option>`;
                });

                let tiposOptions = '<option value="">Seleccione tipo...</option>';
                cicloData.tipos_inscripcion.forEach(tipo => {
                    tiposOptions += `<option value="${tipo.value}">${tipo.label}</option>`;
                });
                
                let departamentosOptions = '<option value="">Seleccione departamento...</option>';
                departamentosData.departamentos.forEach(depto => {
                    departamentosOptions += `<option value="${depto}">${depto}</option>`;
                });

                // Generar HTML del formulario completo con estilo CEPRE
                container.innerHTML = `
                    <div class="cepre-form-container">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header text-white" style="background: linear-gradient(135deg, var(--cepre-magenta) 0%, var(--cepre-navy) 100%);">
                                <h4 class="mb-0 text-white">
                                   <i class="mdi mdi-account-plus me-2"></i>Formulario de Inscripción y Postulación
                                </h4>
                            </div>
                            <div class="card-body">
                                <form id="formPostulacionIntegrado">
                                    <div class="row">
                                        <!-- Tipo de inscripción -->
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Tipo de Inscripción <span class="text-danger">*</span></label>
                                            <select class="form-select" id="tipo_inscripcion" name="tipo_inscripcion" required>
                                                ${tiposOptions}
                                            </select>
                                        </div>

                                        <!-- Carrera -->
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Carrera Profesional <span class="text-danger">*</span></label>
                                            <select class="form-select" id="carrera_id" name="carrera_id" required>
                                                ${carrerasOptions}
                                            </select>
                                        </div>

                                        <!-- Turno -->
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Turno <span class="text-danger">*</span></label>
                                            <select class="form-select" id="turno_id" name="turno_id" required>
                                                ${turnosOptions}
                                            </select>
                                        </div>

                                        <!-- Centro Educativo -->
                                        <div class="col-12">
                                            <h5 class="mt-3 mb-3" style="color: var(--cepre-navy);">Institución Educativa</h5>
                                        </div>

                                        <!-- Departamento -->
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Departamento <span class="text-danger">*</span></label>
                                            <select class="form-select" id="departamento" required>
                                                ${departamentosOptions}
                                            </select>
                                        </div>

                                        <!-- Provincia -->
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Provincia <span class="text-danger">*</span></label>
                                            <select class="form-select" id="provincia" disabled required>
                                                <option value="">Seleccione departamento primero</option>
                                            </select>
                                        </div>

                                        <!-- Distrito -->
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Distrito <span class="text-danger">*</span></label>
                                            <select class="form-select" id="distrito" disabled required>
                                                <option value="">Seleccione provincia primero</option>
                                            </select>
                                        </div>

                                        <!-- Búsqueda de colegio -->
                                        <div class="col-md-8 mb-3">
                                            <label class="form-label">Nombre del Colegio <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="buscar_colegio"
                                                     placeholder="Escriba el nombre del colegio..." disabled>
                                            <div id="sugerencias-colegios" class="list-group mt-1" style="max-height: 200px; overflow-y: auto;"></div>
                                        </div>

                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">&nbsp;</label>
                                            <button type="button" class="btn btn-secondary w-100" id="btnBuscarColegio" disabled>
                                                <i class="mdi mdi-magnify"></i> Buscar
                                            </button>
                                        </div>

                                        <!-- Colegio seleccionado -->
                                        <div class="col-12 mb-3" id="colegio-seleccionado" style="display: none;">
                                            <div class="alert alert-info">
                                                <strong>Colegio seleccionado:</strong>
                                                <span id="nombre-colegio-seleccionado"></span>
                                            </div>
                                        </div>

                                        <!-- Sección de Documentos -->
                                        <div class="col-12">
                                            <h5 class="mt-4 mb-3" style="color: var(--cepre-navy);">Documentos Requeridos</h5>
                                        </div>

                                        <!-- Voucher de Pago -->
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Voucher de Pago <span class="text-danger">*</span></label>
                                            <input type="file" class="form-control" id="voucher_pago" name="voucher_pago"
                                                     accept=".pdf,.jpg,.jpeg,.png" required>
                                            <small class="text-muted">PDF, JPG o PNG (Max: 5MB)</small>
                                        </div>

                                        <!-- Certificado de Estudios -->
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Certificado de Estudios <span class="text-danger">*</span></label>
                                            <input type="file" class="form-control" id="certificado_estudios" name="certificado_estudios"
                                                     accept=".pdf,.jpg,.jpeg,.png" required>
                                            <small class="text-muted">PDF, JPG o PNG (Max: 5MB)</small>
                                        </div>

                                        <!-- Carta de Compromiso -->
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Carta de Compromiso <span class="text-danger">*</span></label>
                                            <input type="file" class="form-control" id="carta_compromiso" name="carta_compromiso"
                                                     accept=".pdf,.jpg,.jpeg,.png" required>
                                            <small class="text-muted">PDF, JPG o PNG (Max: 5MB)</small>
                                        </div>

                                        <!-- Constancia de Estudios -->
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Constancia de Estudios <span class="text-danger">*</span></label>
                                            <input type="file" class="form-control" id="constancia_estudios" name="constancia_estudios"
                                                     accept=".pdf,.jpg,.jpeg,.png" required>
                                            <small class="text-muted">PDF, JPG o PNG (Max: 5MB)</small>
                                        </div>

                                        <!-- DNI -->
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">DNI <span class="text-danger">*</span></label>
                                            <input type="file" class="form-control" id="dni_documento" name="dni_documento"
                                                     accept=".pdf,.jpg,.jpeg,.png" required>
                                            <small class="text-muted">PDF, JPG o PNG (Max: 5MB)</small>
                                        </div>

                                        <!-- Foto Carnet -->
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Foto Carnet <span class="text-danger">*</span></label>
                                            <input type="file" class="form-control" id="foto_carnet" name="foto_carnet"
                                                     accept=".jpg,.jpeg,.png" required>
                                            <small class="text-muted">JPG o PNG (Max: 2MB)</small>
                                        </div>

                                        <!-- Sección de Datos del Voucher -->
                                        <div class="col-12" id="seccion-voucher" style="display: none;">
                                            <h5 class="mt-4 mb-3" style="color: var(--cepre-navy);">Datos del Voucher de Pago</h5>
                                        </div>

                                        <!-- Número de Recibo -->
                                        <div class="col-md-6 mb-3" style="display: none;" id="campo-numero-recibo">
                                            <label class="form-label">Número de Recibo <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="numero_recibo" name="numero_recibo"
                                                     placeholder="Ej: 0001-0004535" required>
                                        </div>

                                        <!-- Fecha de Emisión -->
                                        <div class="col-md-6 mb-3" style="display: none;" id="campo-fecha-emision">
                                            <label class="form-label">Fecha de Emisión <span class="text-danger">*</span></label>
                                            <input type="date" class="form-control" id="fecha_emision_voucher" name="fecha_emision_voucher" required>
                                        </div>

                                        <!-- Monto Matrícula -->
                                        <div class="col-md-6 mb-3" style="display: none;" id="campo-monto-matricula">
                                            <label class="form-label">Matrícula de Ciclo de Preparación General (S/.) <span class="text-danger">*</span></label>
                                            <h6 class="mt-2 mb-1">Opción 1 Matrícula Regular:</h6>
                                            <div class="d-flex justify-content-between mb-2">
                                                <button type="button" class="btn btn-outline-secondary btn-sm btn-block btn-matricula flex-grow-1 me-2" data-value="100">S/ 100</button>
                                            </div>
                                            <h6 class="mt-2 mb-1">Opción 2 Descuento 50% (Resolución):</h6>
                                            <div class="d-flex justify-content-between mb-2">
                                                <button type="button" class="btn btn-outline-secondary btn-sm btn-block btn-matricula flex-grow-1" data-value="50">S/ 50</button>
                                            </div>
                                            <input type="number" class="form-control" id="monto_matricula" name="monto_matricula"
                                                     step="0.01" min="0" placeholder="0.00" required>
                                        </div>

                                        <!-- Monto Enseñanza -->
                                        <div class="col-md-6 mb-3" style="display: none;" id="campo-monto-ensenanza">
                                            <label class="form-label">Costo de Enseñanza por Preparación (S/.) <span class="text-danger">*</span></label>
                                            <h6 class="mt-2 mb-1">Opción 1 Enseñanza Regular:</h6>
                                            <div class="d-flex justify-content-between mb-2">
                                                <button type="button" class="btn btn-outline-secondary btn-sm btn-block btn-ensenanza flex-grow-1 me-2" data-value="1050">S/ 1050</button>
                                            </div>
                                            <h6 class="mt-2 mb-1">Opción 2 Descuento 50% (Resolución):</h6>
                                            <div class="d-flex justify-content-between mb-2">
                                                <button type="button" class="btn btn-outline-secondary btn-sm btn-block btn-ensenanza flex-grow-1" data-value="525">S/ 525</button>
                                            </div>
                                            <input type="number" class="form-control" id="monto_ensenanza" name="monto_ensenanza"
                                                     step="0.01" min="0" placeholder="0.00" required>
                                        </div>

                                        <!-- Subtotal -->
                                        <div class="col-12 mb-3" style="display: none;" id="campo-subtotal">
                                            <div class="alert alert-success">
                                                <div class="row align-items-center">
                                                    <div class="col-md-8">
                                                        <strong>SUBTOTAL A PAGAR:</strong>
                                                    </div>
                                                    <div class="col-md-4 text-end">
                                                        <h4 class="mb-0">S/. <span id="monto_subtotal">0.00</span></h4>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="text-center mt-4">
                                        <button type="submit" class="btn btn-cepre-inscribir">
                                            <i class="mdi mdi-send me-2"></i>Inscribirme Ahora
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                `;
                
                // Configurar todos los eventos del formulario
                configurarEventosFormulario();
                
                toastr.success('Formulario cargado correctamente', 'Listo');
            }
            
            // Función para configurar todos los eventos del formulario integrado
            function configurarEventosFormulario() {
                // Cambio de departamento
                $('#departamento').on('change', function() {
                    const depto = $(this).val();
                    if (depto) {
                        cargarProvincias(depto);
                        $('#provincia').prop('disabled', false);
                        $('#distrito').prop('disabled', true).html('<option value="">Seleccione provincia primero</option>');
                        $('#buscar_colegio').prop('disabled', true).val('');
                        $('#btnBuscarColegio').prop('disabled', true);
                        $('#sugerencias-colegios').empty();
                        ocultarColegioSeleccionado();
                    }
                });

                // Cambio de provincia
                $('#provincia').on('change', function() {
                    const depto = $('#departamento').val();
                    const prov = $(this).val();
                    if (prov) {
                        cargarDistritos(depto, prov);
                        $('#distrito').prop('disabled', false);
                        $('#buscar_colegio').prop('disabled', true).val('');
                        $('#btnBuscarColegio').prop('disabled', true);
                        $('#sugerencias-colegios').empty();
                        ocultarColegioSeleccionado();
                    }
                });

                // Cambio de distrito
                $('#distrito').on('change', function() {
                    if ($(this).val()) {
                        $('#buscar_colegio').prop('disabled', false);
                        $('#btnBuscarColegio').prop('disabled', false);
                        $('#sugerencias-colegios').empty();
                        ocultarColegioSeleccionado();
                        
                        // Cargar colegios automáticamente
                        buscarColegios();
                    }
                });

                // Búsqueda de colegio
                $('#btnBuscarColegio').on('click', buscarColegios);
                $('#buscar_colegio').on('keyup', function() {
                    const searchTerm = $(this).val();
                    if (searchTerm.length >= 2) {
                        buscarColegios();
                    } else {
                        $('#sugerencias-colegios').empty();
                    }
                });
                $('#buscar_colegio').on('keypress', function(e) {
                    if (e.which === 13) {
                        e.preventDefault();
                        buscarColegios();
                    }
                });

                // Cambio en el archivo de voucher
                $('#voucher_pago').on('change', function() {
                    if (this.files && this.files[0]) {
                        $('#seccion-voucher').show();
                        $('#campo-numero-recibo').show();
                        $('#campo-fecha-emision').show();
                        $('#campo-monto-matricula').show();
                        $('#campo-monto-ensenanza').show();
                        $('#campo-subtotal').show();
                    } else {
                        $('#seccion-voucher').hide();
                        $('#campo-numero-recibo').hide();
                        $('#campo-fecha-emision').hide();
                        $('#campo-monto-matricula').hide();
                        $('#campo-monto-ensenanza').hide();
                        $('#campo-subtotal').hide();
                    }
                });

                // Calcular subtotal cuando cambien los montos
                $('#monto_matricula, #monto_ensenanza').on('input', function() {
                    const matricula = parseFloat($('#monto_matricula').val()) || 0;
                    const ensenanza = parseFloat($('#monto_ensenanza').val()) || 0;
                    const subtotal = matricula + ensenanza;
                    $('#monto_subtotal').text(subtotal.toFixed(2));
                });

                // Eventos para los botones de matrícula
                $(document).on('click', '.btn-matricula', function() {
                    if ($('#campo-monto-matricula').is(':hidden')) {
                        toastr.info('Por favor, sube primero el voucher de pago.');
                        return;
                    }
                    const value = $(this).data('value');
                    $('#monto_matricula').val(value).trigger('input');
                });

                // Eventos para los botones de enseñanza
                $(document).on('click', '.btn-ensenanza', function() {
                    if ($('#campo-monto-ensenanza').is(':hidden')) {
                        toastr.info('Por favor, sube primero el voucher de pago.');
                        return;
                    }
                    const value = $(this).data('value');
                    $('#monto_ensenanza').val(value).trigger('input');
                });

                // Validación visual para archivos
                $('input[type="file"]').on('change', function() {
                    const fileName = $(this).val().split('\\').pop();
                    const inputId = $(this).attr('id');
                    const labelText = $(`label[for="${inputId}"]`).text().replace(' *', '');

                    if (fileName) {
                        $(this).removeClass('is-invalid').addClass('is-valid');
                        toastr.info(`${labelText}: ${fileName}`, 'Archivo seleccionado', {
                            "closeButton": false,
                            "progressBar": false,
                            "positionClass": "toast-bottom-left",
                            "timeOut": "1500"
                        });
                    } else {
                        $(this).removeClass('is-valid is-invalid');
                    }
                });

                // Envío del formulario
                $('#formPostulacionIntegrado').on('submit', function(e) {
                    e.preventDefault();
                    if (!colegioSeleccionado) {
                        toastr.warning('Por favor seleccione un colegio de la lista');
                        return;
                    }

                    // Validar archivos
                    const archivosRequeridos = [
                        { id: 'voucher_pago', nombre: 'Voucher de Pago' },
                        { id: 'certificado_estudios', nombre: 'Certificado de Estudios' },
                        { id: 'carta_compromiso', nombre: 'Carta de Compromiso' },
                        { id: 'constancia_estudios', nombre: 'Constancia de Estudios' },
                        { id: 'dni_documento', nombre: 'DNI' },
                        { id: 'foto_carnet', nombre: 'Foto Carnet' }
                    ];

                    for (let archivo of archivosRequeridos) {
                        const input = document.getElementById(archivo.id);
                        if (!input.files || !input.files[0]) {
                            toastr.warning(`Por favor seleccione el archivo: ${archivo.nombre}`);
                            return;
                        }
                    }

                    mostrarConfirmacionIntegrada();
                });
            }
            
            // Funciones auxiliares para geografía
            function cargarProvincias(departamento) {
                fetch(`{{ url('/json/inscripciones-estudiante/provincias') }}/${encodeURIComponent(departamento)}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            let options = '<option value="">Seleccione provincia...</option>';
                            data.provincias.forEach(prov => {
                                options += `<option value="${prov}">${prov}</option>`;
                            });
                            $('#provincia').html(options);
                        }
                    });
            }

            function cargarDistritos(departamento, provincia) {
                fetch(`{{ url('/json/inscripciones-estudiante/distritos') }}/${encodeURIComponent(departamento)}/${encodeURIComponent(provincia)}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            let options = '<option value="">Seleccione distrito...</option>';
                            data.distritos.forEach(dist => {
                                options += `<option value="${dist}">${dist}</option>`;
                            });
                            $('#distrito').html(options);
                        }
                    });
            }

            function buscarColegios() {
                const termino = $('#buscar_colegio').val();
                if (termino.length < 2 && termino.length !== 0) {
                    $('#sugerencias-colegios').empty();
                    return;
                }

                const requestData = {
                    departamento: $('#departamento').val(),
                    provincia: $('#provincia').val(),
                    distrito: $('#distrito').val(),
                    termino: termino,
                    _token: $('meta[name="csrf-token"]').attr('content')
                };

                fetch('{{ url("/json/inscripciones-estudiante/buscar-colegios") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    body: JSON.stringify(requestData)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        mostrarSugerenciasColegios(data.colegios);
                    }
                });
            }

            function mostrarSugerenciasColegios(colegios) {
                let html = '';
                if (colegios.length === 0) {
                    html = '<div class="list-group-item">No se encontraron colegios</div>';
                } else {
                    colegios.forEach(colegio => {
                        html += `
                            <a href="#" class="list-group-item list-group-item-action seleccionar-colegio"
                               data-id="${colegio.id}" data-nombre="${colegio.nombre}">
                                <strong>${colegio.nombre}</strong>
                                ${colegio.nivel ? `<br><small>Nivel: ${colegio.nivel}</small>` : ''}
                                ${colegio.direccion ? `<br><small>${colegio.direccion}</small>` : ''}
                            </a>
                        `;
                    });
                }
                $('#sugerencias-colegios').html(html);

                // Evento para seleccionar colegio
                $('.seleccionar-colegio').on('click', function(e) {
                    e.preventDefault();
                    colegioSeleccionado = {
                        id: $(this).data('id'),
                        nombre: $(this).data('nombre')
                    };
                    mostrarColegioSeleccionado();
                    $('#sugerencias-colegios').empty();
                });
            }

            function mostrarColegioSeleccionado() {
                $('#nombre-colegio-seleccionado').text(colegioSeleccionado.nombre);
                $('#colegio-seleccionado').show();
                $('#buscar_colegio').val(colegioSeleccionado.nombre);
            }

            function ocultarColegioSeleccionado() {
                colegioSeleccionado = null;
                $('#colegio-seleccionado').hide();
            }
            
            // Función para mostrar confirmación con SweetAlert2
            function mostrarConfirmacionIntegrada() {
                const tipo = $('#tipo_inscripcion option:selected').text();
                const carrera = $('#carrera_id option:selected').text();
                const turno = $('#turno_id option:selected').text();
                const numeroRecibo = $('#numero_recibo').val();
                const montoMatricula = parseFloat($('#monto_matricula').val()) || 0;
                const montoEnsenanza = parseFloat($('#monto_ensenanza').val()) || 0;
                const montoTotal = montoMatricula + montoEnsenanza;

                const resumenHTML = `
                    <div style="text-align: left;">
                        <ul style="list-style: none; padding: 0;">
                            <li style="padding: 5px 0;"><strong>Tipo:</strong> ${tipo}</li>
                            <li style="padding: 5px 0;"><strong>Carrera:</strong> ${carrera}</li>
                            <li style="padding: 5px 0;"><strong>Turno:</strong> ${turno}</li>
                            <li style="padding: 5px 0;"><strong>Colegio:</strong> ${colegioSeleccionado.nombre}</li>
                            <li style="padding: 5px 0;"><strong>Documentos:</strong> 6 archivos seleccionados</li>
                            ${numeroRecibo ? `<li style="padding: 5px 0;"><strong>N° Recibo:</strong> ${numeroRecibo}</li>` : ''}
                            ${montoMatricula > 0 ? `<li style="padding: 5px 0;"><strong>Matrícula:</strong> S/. ${montoMatricula.toFixed(2)}</li>` : ''}
                            ${montoEnsenanza > 0 ? `<li style="padding: 5px 0;"><strong>Enseñanza:</strong> S/. ${montoEnsenanza.toFixed(2)}</li>` : ''}
                            ${montoTotal > 0 ? `<li style="padding: 5px 0;"><strong><span style="color: #28a745;">TOTAL PAGADO: S/. ${montoTotal.toFixed(2)}</span></strong></li>` : ''}
                        </ul>
                    </div>
                `;

                Swal.fire({
                    title: '¡Confirmar Postulación!',
                    html: `<p style="margin-bottom: 20px;">Revise cuidadosamente los datos antes de enviar:</p>${resumenHTML}`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: '<i class="mdi mdi-check"></i> Confirmar Postulación',
                    cancelButtonText: '<i class="mdi mdi-close"></i> Cancelar',
                    reverseButtons: true,
                    customClass: {
                        confirmButton: 'swal2-confirm',
                        cancelButton: 'swal2-cancel'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        enviarFormularioPostulacion();
                    }
                });
            }

            // Función para enviar el formulario de postulación
            function enviarFormularioPostulacion() {
                // Crear FormData para enviar archivos
                const formData = new FormData();

                // Agregar datos básicos
                formData.append('tipo_inscripcion', $('#tipo_inscripcion').val());
                formData.append('carrera_id', $('#carrera_id').val());
                formData.append('turno_id', $('#turno_id').val());
                formData.append('centro_educativo_id', colegioSeleccionado.id);

                // Agregar archivos
                const archivos = [
                    {id: 'voucher_pago', nombre: 'Voucher de pago'},
                    {id: 'certificado_estudios', nombre: 'Certificado de estudios'},
                    {id: 'carta_compromiso', nombre: 'Carta de compromiso'},
                    {id: 'constancia_estudios', nombre: 'Constancia de estudios'},
                    {id: 'dni_documento', nombre: 'DNI'},
                    {id: 'foto_carnet', nombre: 'Foto carnet'}
                ];

                let archivosSubidos = 0;
                archivos.forEach(function(archivo, index) {
                    const input = document.getElementById(archivo.id);
                    if (input.files && input.files[0]) {
                        formData.append(archivo.id, input.files[0]);
                        archivosSubidos++;
                        setTimeout(() => {
                            toastr.info(`${archivo.nombre} cargado correctamente (${archivosSubidos}/6)`, 'Archivo ' + archivosSubidos, {
                                "closeButton": false,
                                "progressBar": true,
                                "positionClass": "toast-bottom-right",
                                "timeOut": "2000"
                            });
                        }, index * 300);
                    }
                });

                // Agregar datos del voucher
                formData.append('numero_recibo', $('#numero_recibo').val());
                formData.append('fecha_emision_voucher', $('#fecha_emision_voucher').val());
                formData.append('monto_matricula', $('#monto_matricula').val() || 0);
                formData.append('monto_ensenanza', $('#monto_ensenanza').val() || 0);

                // Agregar token CSRF
                formData.append('_token', $('meta[name="csrf-token"]').attr('content'));

                // IMPORTANTE: Determinar el endpoint y datos correctos según el tipo
                let urlEndpoint = '{{ url("/json/postulaciones/crear") }}'; // Endpoint unificado para crear postulaciones
                
                if (tipoPostulante === 'existente' && postulanteData) {
                    // Para postulante existente, usar su ID y NO el del admin logueado
                    formData.append('estudiante_id', postulanteData.id); // ID del usuario/postulante encontrado por DNI
                    
                    // Agregar datos del estudiante para evitar confusión
                    formData.append('estudiante_dni', postulanteData.numero_documento);
                    formData.append('estudiante_nombre', postulanteData.nombre);
                    formData.append('estudiante_apellido_paterno', postulanteData.apellido_paterno);
                    formData.append('estudiante_apellido_materno', postulanteData.apellido_materno);
                    
                } else if (tipoPostulante === 'nuevo') {
                    // Para postulante nuevo, el admin está creando tanto el usuario como la postulación
                    formData.append('crear_usuario_nuevo', '1');
                    
                    // Aquí deberían ir los datos del nuevo usuario (nombre, DNI, email, etc.)
                    // Estos vendrían del formulario de registro completo
                }

                // Mostrar loading con SweetAlert2
                Swal.fire({
                    title: 'Procesando Postulación',
                    html: `
                        <div style="text-align: center;">
                            <div class="cepre-spinner" style="margin: 20px auto;"></div>
                            <p>Enviando documentos y datos...</p>
                            <div id="progress-info">Iniciando envío...</div>
                        </div>
                    `,
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false
                });

                // Realizar petición AJAX con el endpoint correcto
                $.ajax({
                    url: urlEndpoint, // Usar el endpoint determinado según el tipo
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    xhr: function() {
                        var xhr = new window.XMLHttpRequest();
                        xhr.upload.addEventListener("progress", function(evt) {
                            if (evt.lengthComputable) {
                                var percentComplete = ((evt.loaded / evt.total) * 100).toFixed(0);
                                $('#progress-info').html(`Subiendo... ${percentComplete}%`);
                            }
                        }, false);
                        return xhr;
                    },
                    success: function(response) {
                        Swal.close();
                        
                        if (response.success) {
                            // Mostrar toast de éxito escalonado
                            setTimeout(() => {
                                toastr.success('¡Todos tus archivos fueron subidos exitosamente!', 'Archivos Completos', {
                                    "closeButton": true,
                                    "progressBar": true,
                                    "positionClass": "toast-top-center",
                                    "timeOut": "3000"
                                });
                            }, archivos.length * 300 + 500);

                            if (response.postulacion) {
                                setTimeout(() => {
                                    Swal.fire({
                                        icon: 'success',
                                        title: '¡Postulación Exitosa!',
                                        text: 'Tu postulación ha sido enviada exitosamente. Te notificaremos cuando sea revisada.',
                                        confirmButtonText: 'Entendido'
                                    }).then(() => {
                                        // Cerrar modal y actualizar lista
                                        $('#nuevaPostulacionModal').modal('hide');
                                        if (typeof table !== 'undefined' && table) {
                                            table.ajax.reload();
                                        }
                                    });
                                }, archivos.length * 300 + 2000);
                            } else {
                                setTimeout(() => {
                                    Swal.fire({
                                        icon: 'success',
                                        title: '¡Inscripción Exitosa!',
                                        text: response.message || 'Inscripción realizada correctamente',
                                        confirmButtonText: 'Entendido'
                                    }).then(() => {
                                        $('#nuevaPostulacionModal').modal('hide');
                                        if (typeof table !== 'undefined' && table) {
                                            table.ajax.reload();
                                        }
                                    });
                                }, archivos.length * 300 + 2000);
                            }
                        }
                    },
                    error: function(xhr) {
                        Swal.close();
                        const response = xhr.responseJSON;
                        let errorMsg = 'Error al procesar la postulación';
                        
                        if (response && response.errors) {
                            let errores = '';
                            $.each(response.errors, function(key, value) {
                                errores += value[0] + '\n';
                            });
                            errorMsg = errores;
                        } else if (response && response.message) {
                            errorMsg = response.message;
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Error en el Envío',
                            text: errorMsg,
                            confirmButtonText: 'Entendido'
                        });
                    }
                });
            }
            
            // Función para inicializar wizard de registro completo
            function initWizardRegistroCompleto() {
                // Cargar script de wizard para registro + postulación
                const script = document.createElement('script');
                script.src = "{{ asset('js/postulaciones/wizard-completo.js') }}";
                script.onload = function() {
                    console.log('Wizard completo inicializado');
                };
                document.head.appendChild(script);
            }
            
            // Función para inicializar wizard de postulación simple
            function initWizardPostulacion() {
                // Para usuarios existentes, usar un formulario simplificado
                // que solo pida datos académicos y documentos
                const script = document.createElement('script');
                script.src = "{{ asset('js/postulaciones/wizard-simplificado.js') }}";
                script.onload = function() {
                    console.log('Wizard simplificado inicializado para postulante existente');
                    
                    // Pasar los datos del postulante al wizard
                    if (window.initWizardSimplificado) {
                        window.initWizardSimplificado(postulanteData);
                    }
                };
                document.head.appendChild(script);
            }
            
            // Escuchar mensajes para cerrar el modal cuando se complete la postulación
            window.addEventListener('message', function(event) {
                if (event.data && event.data.type === 'postulacion-completada') {
                    // Cerrar todos los modales
                    const modales = [modalNuevaPostulacion, modalBuscarPostulante, modalSeleccionTipo];
                    modales.forEach(modalEl => {
                        const modal = bootstrap.Modal.getInstance(modalEl);
                        if (modal) modal.hide();
                    });
                    
                    // Limpiar datos
                    tipoPostulante = null;
                    postulanteData = null;
                    
                    // Mostrar mensaje de éxito
                    toastr.success('Postulación creada exitosamente', 'Éxito');
                    
                    // Actualizar la lista
                    if (typeof refreshPostulacionesList === 'function') {
                        refreshPostulacionesList();
                    } else {
                        setTimeout(() => {
                            window.location.reload();
                        }, 2000);
                    }
                }
            });
        });
        
        // ============================================
        // WIZARD DE REGISTRO COMPLETO DE NUEVOS POSTULANTES
        // ============================================
        
        // Variables globales del wizard
        let wizardCurrentStep = 1;
        const wizardTotalSteps = 4;
        let nuevoUsuarioData = null;
        let wizardFormData = {};
        const modalRegistroNuevo = document.getElementById('modalRegistroNuevo');
        
        // Función para mostrar el modal de registro
        function mostrarModalRegistroNuevo() {
            const modal = new bootstrap.Modal(modalRegistroNuevo);
            modal.show();
            
            // Resetear wizard
            wizardCurrentStep = 1;
            wizardFormData = {};
            
            // Limpiar formulario
            document.getElementById('formRegistroNuevo').reset();
            
            // Mostrar solo paso 1
            showWizardStep(1);
            updateWizardDisplay();
            actualizarContadorCamposPaso(1);
        }
        
        // Funciones de navegación del wizard
        function nextStepWizard() {
            if (validateWizardStep(wizardCurrentStep)) {
                saveCurrentStepData();
                
                if (wizardCurrentStep === 3) {
                    // En paso 3, crear la cuenta
                    crearCuentaUsuario();
                } else if (wizardCurrentStep === 4) {
                    // En paso 4, enviar postulación
                    enviarPostulacionCompleta();
                } else if (wizardCurrentStep < wizardTotalSteps) {
                    // Navegar al siguiente paso
                    wizardCurrentStep++;
                    showWizardStep(wizardCurrentStep);
                    updateWizardDisplay();
                    
                    if (wizardCurrentStep === 3) {
                        generateConfirmationSummaryWizard();
                    } else if (wizardCurrentStep === 4) {
                        cargarFormularioPostulacion();
                    }
                }
            }
        }
        
        function previousStepWizard() {
            if (wizardCurrentStep > 1) {
                wizardCurrentStep--;
                showWizardStep(wizardCurrentStep);
                updateWizardDisplay();
            }
        }
        
        function showWizardStep(step) {
            // Ocultar todos los pasos
            document.querySelectorAll('.wizard-step').forEach(stepEl => {
                stepEl.style.display = 'none';
            });
            
            // Mostrar paso actual
            const currentStepEl = document.querySelector(`.wizard-step[data-step="${step}"]`);
            if (currentStepEl) {
                currentStepEl.style.display = 'block';
            }
        }
        
        function updateWizardDisplay() {
            // Actualizar indicadores de paso
            document.querySelectorAll('.step-indicator').forEach(indicator => {
                const stepNum = parseInt(indicator.getAttribute('data-step'));
                const circle = indicator.querySelector('.step-circle');
                
                if (stepNum < wizardCurrentStep) {
                    // Pasos completados
                    indicator.classList.remove('active');
                    indicator.classList.add('completed');
                    circle.querySelector('.step-number').style.display = 'none';
                    circle.querySelector('.step-check').classList.remove('d-none');
                } else if (stepNum === wizardCurrentStep) {
                    // Paso actual
                    indicator.classList.add('active');
                    indicator.classList.remove('completed');
                    circle.querySelector('.step-number').style.display = 'block';
                    circle.querySelector('.step-check').classList.add('d-none');
                } else {
                    // Pasos futuros
                    indicator.classList.remove('active', 'completed');
                    circle.querySelector('.step-number').style.display = 'block';
                    circle.querySelector('.step-check').classList.add('d-none');
                }
            });
            
            // Actualizar barra de progreso
            const progressPercent = (wizardCurrentStep / wizardTotalSteps) * 100;
            const progressBar = document.querySelector('.progress .progress-bar');
            if (progressBar) {
                progressBar.style.width = progressPercent + '%';
            }
            
            // Actualizar texto de paso
            const currentStepText = document.getElementById('currentStepWizard');
            if (currentStepText) {
                currentStepText.textContent = wizardCurrentStep;
            }
            
            // Actualizar botones de navegación
            updateNavigationButtons();
        }
        
        function updateNavigationButtons() {
            const prevBtn = document.getElementById('prevStepBtnWizard');
            const nextBtn = document.getElementById('nextStepBtnWizard');
            const btnText = nextBtn.querySelector('.btn-text');
            
            // Botón anterior
            if (wizardCurrentStep > 1) {
                prevBtn.style.display = 'inline-block';
            } else {
                prevBtn.style.display = 'none';
            }
            
            // Botón siguiente/finalizar
            if (wizardCurrentStep === 3) {
                btnText.textContent = 'Crear Cuenta';
                nextBtn.className = 'btn btn-success';
            } else if (wizardCurrentStep === 4) {
                btnText.textContent = 'Enviar Postulación';
                nextBtn.className = 'btn btn-primary';
            } else {
                btnText.textContent = 'Siguiente';
                nextBtn.className = 'btn btn-primary';
            }
        }
        
        // Funciones de validación por paso
        function validateWizardStep(step) {
            const stepElement = document.querySelector(`.wizard-step[data-step="${step}"]`);
            if (!stepElement) return false;
            
            if (step === 1) {
                return validateStep1();
            } else if (step === 2) {
                return validateStep2();
            } else if (step === 3) {
                return validateStep3();
            } else if (step === 4) {
                return validateStep4();
            }
            
            return true;
        }
        
        function validateStep1() {
            const requiredFields = [
                'nuevo_tipo_documento', 'nuevo_numero_documento', 'nuevo_nombre',
                'nuevo_apellido_paterno', 'nuevo_apellido_materno', 'nuevo_fecha_nacimiento',
                'nuevo_genero', 'nuevo_telefono', 'nuevo_direccion', 'nuevo_email',
                'nuevo_password', 'nuevo_password_confirmation'
            ];
            
            let isValid = true;
            
            for (let fieldId of requiredFields) {
                const field = document.getElementById(fieldId);
                if (!field || !field.value.trim()) {
                    field?.classList.add('is-invalid');
                    isValid = false;
                } else {
                    field.classList.remove('is-invalid');
                }
            }
            
            // Validar contraseñas coincidentes
            if (!validarPasswordsRegistro()) {
                isValid = false;
            }
            
            if (!isValid) {
                toastr.warning('Por favor complete todos los campos requeridos');
            }
            
            return isValid;
        }
        
        function validateStep2() {
            const requiredFields = [
                'padre_tipo_doc', 'padre_numero_doc', 'padre_nombre', 'padre_apellidos', 'padre_telefono',
                'madre_tipo_doc', 'madre_numero_doc', 'madre_nombre', 'madre_apellidos', 'madre_telefono'
            ];
            
            let isValid = true;
            
            for (let fieldId of requiredFields) {
                const field = document.getElementById(fieldId);
                if (!field || !field.value.trim()) {
                    field?.classList.add('is-invalid');
                    isValid = false;
                } else {
                    field.classList.remove('is-invalid');
                }
            }
            
            if (!isValid) {
                toastr.warning('Por favor complete todos los campos de los padres');
            }
            
            return isValid;
        }
        
        function validateStep3() {
            const termsCheckbox = document.getElementById('nuevo_terms');
            if (!termsCheckbox.checked) {
                toastr.warning('Debe aceptar los términos y condiciones');
                return false;
            }
            return true;
        }
        
        function validateStep4() {
            // Validación del formulario de postulación se manejará en su propio contexto
            return true;
        }
        
        // Función para guardar datos del paso actual
        function saveCurrentStepData() {
            const stepElement = document.querySelector(`.wizard-step[data-step="${wizardCurrentStep}"]`);
            if (!stepElement) return;
            
            const inputs = stepElement.querySelectorAll('input, select, textarea');
            inputs.forEach(input => {
                if (input.type === 'checkbox') {
                    wizardFormData[input.name] = input.checked;
                } else {
                    wizardFormData[input.name] = input.value;
                }
            });
        }
        
        // Funciones de consulta RENIEC
        function consultarReniecRegistro() {
            consultarReniecPersona('nuevo_numero_documento', 'postulante');
        }
        
        function consultarReniecPadre() {
            consultarReniecPersona('padre_numero_doc', 'padre');
        }
        
        function consultarReniecMadre() {
            consultarReniecPersona('madre_numero_doc', 'madre');
        }
        
        function consultarReniecPersona(dniFieldId, tipo) {
            const dni = document.getElementById(dniFieldId).value;
            const btnConsultar = tipo === 'postulante' ? document.getElementById('btnConsultarReniecNuevo') :
                                tipo === 'padre' ? document.getElementById('btnConsultarReniecPadre') :
                                document.getElementById('btnConsultarReniecMadre');
            
            if (!dni || dni.length !== 8) {
                toastr.warning('Ingrese un DNI válido de 8 dígitos');
                return;
            }
            
            // Mostrar loading
            btnConsultar.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span>';
            btnConsultar.disabled = true;
            
            // Realizar consulta RENIEC con POST
            fetch('{{ url('/api/reniec/consultar') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ dni: dni })
            })
                .then(response => {
                    if (response.status === 419) {
                        throw new Error('Token CSRF expirado. Por favor, recargue la página.');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        // Pasar data.data en lugar de data directamente
                        autocompletarDatos(data.data || data, tipo);
                        toastr.success('Datos obtenidos correctamente de RENIEC', 'Consulta Exitosa');
                        actualizarContadorCamposPaso(wizardCurrentStep);
                    } else {
                        toastr.error(data.message || 'No se pudo consultar los datos');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    toastr.error('Error al consultar RENIEC');
                })
                .finally(() => {
                    // Restaurar botón
                    btnConsultar.innerHTML = '<i class="mdi mdi-magnify"></i>';
                    btnConsultar.disabled = false;
                });
        }
        
        function autocompletarDatos(data, tipo) {
            // Debug: Ver qué datos llegan
            console.log('Datos RENIEC recibidos:', data);
            console.log('Tipo:', tipo);
            
            if (tipo === 'postulante') {
                // Autocompletar datos del postulante
                if (data.nombres) document.getElementById('nuevo_nombre').value = data.nombres;
                if (data.apellido_paterno) document.getElementById('nuevo_apellido_paterno').value = data.apellido_paterno;
                if (data.apellido_materno) document.getElementById('nuevo_apellido_materno').value = data.apellido_materno;
                if (data.genero) document.getElementById('nuevo_genero').value = data.genero;
                if (data.fecha_nacimiento) document.getElementById('nuevo_fecha_nacimiento').value = data.fecha_nacimiento;
                if (data.direccion) document.getElementById('nuevo_direccion').value = data.direccion;
                
                // También llenar automáticamente algunos datos de los padres si están disponibles
                if (data.nombre_padre) {
                    document.getElementById('padre_nombre').value = data.nombre_padre;
                    // Trigger change para actualizar contador
                    document.getElementById('padre_nombre').dispatchEvent(new Event('change'));
                }
                if (data.nombre_madre) {
                    document.getElementById('madre_nombre').value = data.nombre_madre;
                    // Trigger change para actualizar contador
                    document.getElementById('madre_nombre').dispatchEvent(new Event('change'));
                }
                
                // Mostrar información adicional si hay datos de padres
                if (data.nombre_padre || data.nombre_madre) {
                    toastr.info(
                        `Se autocompletaron algunos datos de los padres desde RENIEC.<br>
                         <strong>Padre:</strong> ${data.nombre_padre || 'N/D'}<br>
                         <strong>Madre:</strong> ${data.nombre_madre || 'N/D'}`,
                        'Datos adicionales encontrados',
                        {
                            timeOut: 8000,
                            extendedTimeOut: 4000,
                            closeButton: true,
                            progressBar: true,
                            positionClass: 'toast-top-right'
                        }
                    );
                }
                
            } else if (tipo === 'padre') {
                // Autocompletar datos del padre
                if (data.nombres) document.getElementById('padre_nombre').value = data.nombres;
                if (data.apellido_paterno || data.apellido_materno) {
                    const apellidosCompletos = `${data.apellido_paterno || ''} ${data.apellido_materno || ''}`.trim();
                    document.getElementById('padre_apellidos').value = apellidosCompletos;
                }
                
            } else if (tipo === 'madre') {
                // Autocompletar datos de la madre
                if (data.nombres) document.getElementById('madre_nombre').value = data.nombres;
                if (data.apellido_paterno || data.apellido_materno) {
                    const apellidosCompletos = `${data.apellido_paterno || ''} ${data.apellido_materno || ''}`.trim();
                    document.getElementById('madre_apellidos').value = apellidosCompletos;
                }
            }
            
            // Marcar visualmente los campos autocompletados
            const tipoPrefix = tipo === 'postulante' ? 'nuevo_' : tipo === 'padre' ? 'padre_' : 'madre_';
            const fieldsToHighlight = tipo === 'postulante' ? 
                ['nombre', 'apellido_paterno', 'apellido_materno', 'genero', 'fecha_nacimiento', 'direccion'] :
                ['nombre', 'apellidos'];
                
            fieldsToHighlight.forEach(field => {
                const fieldId = tipo === 'postulante' ? `nuevo_${field}` : `${tipo}_${field === 'apellidos' ? field : field}`;
                const element = document.getElementById(fieldId);
                if (element && element.value) {
                    element.classList.add('is-valid');
                    element.style.background = 'linear-gradient(135deg, #e8f5e8 0%, #f0f9ff 100%)';
                    element.style.transition = 'all 0.3s ease';
                    
                    // Remover el highlight después de 3 segundos
                    setTimeout(() => {
                        element.classList.remove('is-valid');
                        element.style.background = '';
                    }, 3000);
                }
            });
        }
        
        // Función para actualizar contador de campos por paso
        function actualizarContadorCamposPaso(step) {
            const stepElement = document.querySelector(`.wizard-step[data-step="${step}"]`);
            if (!stepElement) return;
            
            const campos = stepElement.querySelectorAll('input[required], select[required]');
            let camposCompletos = 0;
            
            campos.forEach(campo => {
                if (campo.type === 'checkbox') {
                    if (campo.checked) camposCompletos++;
                } else {
                    if (campo.value && campo.value.trim() !== '') camposCompletos++;
                }
            });
            
            const counterId = `step${step}CounterWizard`;
            const counter = document.getElementById(counterId);
            if (counter) {
                counter.textContent = `${camposCompletos} de ${campos.length} campos completados`;
            }
            
            // Actualizar progreso de paso mini
            const miniProgressBar = document.querySelector(`.mini-progress-bar[data-step="${step}"]`);
            if (miniProgressBar) {
                const porcentaje = Math.round((camposCompletos / campos.length) * 100);
                miniProgressBar.style.width = porcentaje + '%';
            }
        }
        
        // Función para validar contraseñas coincidentes
        function validarPasswordsRegistro() {
            const password = document.getElementById('nuevo_password').value;
            const confirmation = document.getElementById('nuevo_password_confirmation').value;
            const confirmationField = document.getElementById('nuevo_password_confirmation');
            
            if (password && confirmation && password !== confirmation) {
                confirmationField.classList.add('is-invalid');
                return false;
            } else {
                confirmationField.classList.remove('is-invalid');
                return true;
            }
        }
        
        // Función para togglear visibilidad de contraseña
        function togglePasswordVisibility(inputId) {
            const input = document.getElementById(inputId);
            const button = input.parentElement.querySelector('button');
            const icon = button.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('mdi-eye');
                icon.classList.add('mdi-eye-off');
            } else {
                input.type = 'password';
                icon.classList.remove('mdi-eye-off');
                icon.classList.add('mdi-eye');
            }
        }
        
        // Función para generar el resumen de confirmación
        function generateConfirmationSummaryWizard() {
            const container = document.getElementById('confirmationSummaryWizard');
            
            // Recopilar datos del formulario
            const postulante = {
                tipo_doc: document.getElementById('nuevo_tipo_documento').value,
                numero_doc: document.getElementById('nuevo_numero_documento').value,
                nombre: document.getElementById('nuevo_nombre').value,
                apellido_paterno: document.getElementById('nuevo_apellido_paterno').value,
                apellido_materno: document.getElementById('nuevo_apellido_materno').value,
                fecha_nacimiento: document.getElementById('nuevo_fecha_nacimiento').value,
                genero: document.getElementById('nuevo_genero').value,
                telefono: document.getElementById('nuevo_telefono').value,
                direccion: document.getElementById('nuevo_direccion').value,
                email: document.getElementById('nuevo_email').value
            };
            
            const padre = {
                tipo_doc: document.getElementById('padre_tipo_doc').value,
                numero_doc: document.getElementById('padre_numero_doc').value,
                nombre: document.getElementById('padre_nombre').value,
                apellidos: document.getElementById('padre_apellidos').value,
                telefono: document.getElementById('padre_telefono').value,
                email: document.getElementById('padre_email').value
            };
            
            const madre = {
                tipo_doc: document.getElementById('madre_tipo_doc').value,
                numero_doc: document.getElementById('madre_numero_doc').value,
                nombre: document.getElementById('madre_nombre').value,
                apellidos: document.getElementById('madre_apellidos').value,
                telefono: document.getElementById('madre_telefono').value,
                email: document.getElementById('madre_email').value
            };
            
            // Generar HTML del resumen
            container.innerHTML = `
                <div class="confirmation-summary">
                    <div class="row">
                        <!-- Datos del Postulante -->
                        <div class="col-md-6 mb-3">
                            <div class="card border-primary">
                                <div class="card-header bg-primary text-white">
                                    <h6 class="mb-0"><i class="mdi mdi-account me-2"></i>Datos del Postulante</h6>
                                </div>
                                <div class="card-body">
                                    <p><strong>Nombre:</strong> ${postulante.nombre} ${postulante.apellido_paterno} ${postulante.apellido_materno}</p>
                                    <p><strong>Documento:</strong> ${postulante.tipo_doc} ${postulante.numero_doc}</p>
                                    <p><strong>Fecha Nac.:</strong> ${postulante.fecha_nacimiento}</p>
                                    <p><strong>Género:</strong> ${postulante.genero === 'M' ? 'Masculino' : 'Femenino'}</p>
                                    <p><strong>Teléfono:</strong> ${postulante.telefono}</p>
                                    <p><strong>Email:</strong> ${postulante.email}</p>
                                    <p><strong>Dirección:</strong> ${postulante.direccion}</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Datos de los Padres -->
                        <div class="col-md-6 mb-3">
                            <div class="card border-info">
                                <div class="card-header bg-info text-white">
                                    <h6 class="mb-0"><i class="mdi mdi-account-group me-2"></i>Datos de los Padres</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <h6 class="text-primary">Padre/Tutor:</h6>
                                        <p><strong>Nombre:</strong> ${padre.nombre} ${padre.apellidos}</p>
                                        <p><strong>Documento:</strong> ${padre.tipo_doc} ${padre.numero_doc}</p>
                                        <p><strong>Teléfono:</strong> ${padre.telefono}</p>
                                        ${padre.email ? `<p><strong>Email:</strong> ${padre.email}</p>` : ''}
                                    </div>
                                    <div class="mb-0">
                                        <h6 class="text-primary">Madre/Tutora:</h6>
                                        <p><strong>Nombre:</strong> ${madre.nombre} ${madre.apellidos}</p>
                                        <p><strong>Documento:</strong> ${madre.tipo_doc} ${madre.numero_doc}</p>
                                        <p><strong>Teléfono:</strong> ${madre.telefono}</p>
                                        ${madre.email ? `<p><strong>Email:</strong> ${madre.email}</p>` : ''}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }
        
        // Función para crear la cuenta del usuario
        function crearCuentaUsuario() {
            // Recopilar todos los datos del formulario
            const formData = new FormData();
            
            // Agregar datos del postulante
            formData.append('tipo_documento', document.getElementById('nuevo_tipo_documento').value);
            formData.append('numero_documento', document.getElementById('nuevo_numero_documento').value);
            formData.append('nombre', document.getElementById('nuevo_nombre').value);
            formData.append('apellido_paterno', document.getElementById('nuevo_apellido_paterno').value);
            formData.append('apellido_materno', document.getElementById('nuevo_apellido_materno').value);
            formData.append('fecha_nacimiento', document.getElementById('nuevo_fecha_nacimiento').value);
            formData.append('genero', document.getElementById('nuevo_genero').value);
            formData.append('telefono', document.getElementById('nuevo_telefono').value);
            formData.append('direccion', document.getElementById('nuevo_direccion').value);
            formData.append('email', document.getElementById('nuevo_email').value);
            formData.append('password', document.getElementById('nuevo_password').value);
            formData.append('password_confirmation', document.getElementById('nuevo_password_confirmation').value);
            
            // Agregar datos del padre
            formData.append('padre_tipo_documento', document.getElementById('padre_tipo_doc').value);
            formData.append('padre_numero_documento', document.getElementById('padre_numero_doc').value);
            formData.append('padre_nombre', document.getElementById('padre_nombre').value);
            formData.append('padre_apellidos', document.getElementById('padre_apellidos').value);
            formData.append('padre_telefono', document.getElementById('padre_telefono').value);
            formData.append('padre_email', document.getElementById('padre_email').value);
            
            // Agregar datos de la madre
            formData.append('madre_tipo_documento', document.getElementById('madre_tipo_doc').value);
            formData.append('madre_numero_documento', document.getElementById('madre_numero_doc').value);
            formData.append('madre_nombre', document.getElementById('madre_nombre').value);
            formData.append('madre_apellidos', document.getElementById('madre_apellidos').value);
            formData.append('madre_telefono', document.getElementById('madre_telefono').value);
            formData.append('madre_email', document.getElementById('madre_email').value);
            
            // Agregar términos aceptados
            formData.append('terms', document.getElementById('nuevo_terms').checked ? '1' : '0');
            
            // Mostrar loading
            Swal.fire({
                title: 'Creando Cuenta',
                html: `
                    <div class="text-center">
                        <div class="cepre-spinner" style="margin: 20px auto;"></div>
                        <p>Registrando nuevo postulante...</p>
                        <small class="text-muted">Esto puede tomar unos momentos</small>
                    </div>
                `,
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false
            });
            
            // Realizar registro
            fetch('{{ route("api.register.postulante") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    // No incluir Content-Type cuando se envía FormData
                },
                credentials: 'same-origin', // Incluir cookies de sesión
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                Swal.close();
                
                if (data.success) {
                    // Guardar datos del nuevo usuario
                    nuevoUsuarioData = data.data.postulante || data.user || data.postulante;
                    postulanteData = nuevoUsuarioData; // Compatibilidad con sistema existente
                    
                    // Mostrar éxito y continuar al paso 4
                    Swal.fire({
                        icon: 'success',
                        title: '¡Cuenta Creada Exitosamente!',
                        text: 'Ahora puede continuar con su postulación',
                        confirmButtonText: 'Continuar',
                        timer: 3000,
                        timerProgressBar: true
                    }).then(() => {
                        // Pasar al paso 4 (postulación)
                        wizardCurrentStep = 4;
                        showWizardStep(4);
                        updateWizardDisplay();
                        cargarFormularioPostulacion();
                    });
                } else {
                    // Manejar errores
                    let errorMsg = 'Error al crear la cuenta';
                    if (data.errors) {
                        const errorList = Object.values(data.errors).flat();
                        errorMsg = errorList.join('<br>');
                    } else if (data.message) {
                        errorMsg = data.message;
                    }
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Error en el Registro',
                        html: errorMsg,
                        confirmButtonText: 'Intentar Nuevamente'
                    });
                }
            })
            .catch(error => {
                Swal.close();
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error de Conexión',
                    text: 'No se pudo conectar con el servidor. Inténtelo nuevamente.',
                    confirmButtonText: 'Entendido'
                });
            });
        }
        
        // Función para cargar el formulario de postulación en el paso 4
        function cargarFormularioPostulacion() {
            const container = document.getElementById('formularioPostulacionContainer');
            
            // Debug: verificar datos del usuario
            console.log('nuevoUsuarioData:', nuevoUsuarioData);
            
            // NO establecer postulanteData aquí - se establecerá cuando la búsqueda automática encuentre al usuario
            // postulanteData = nuevoUsuarioData;
            
            // Crear botón para proceder al formulario de postulación
            container.innerHTML = `
                <div class="card">
                    <div class="card-body text-center py-5">
                        <div class="mb-4">
                            <i class="mdi mdi-check-circle-outline text-success" style="font-size: 64px;"></i>
                        </div>
                        <h4 class="text-success mb-3">¡Cuenta Creada Exitosamente!</h4>
                        <p class="text-muted mb-4">
                            Su cuenta ha sido registrada correctamente.<br>
                            Ahora puede proceder con su postulación académica.
                        </p>
                        <div class="mb-3">
                            <strong>Postulante:</strong> ${(nuevoUsuarioData && nuevoUsuarioData.nombre_completo) || (nuevoUsuarioData && nuevoUsuarioData.nombre + ' ' + (nuevoUsuarioData.apellido_paterno || '')) || 'Postulante registrado'}<br>
                            <strong>DNI:</strong> ${(nuevoUsuarioData && nuevoUsuarioData.numero_documento) || 'Registrado correctamente'}<br>
                            <strong>Email:</strong> ${(nuevoUsuarioData && nuevoUsuarioData.email) || 'Email registrado'}
                        </div>
                        <button type="button" class="btn btn-success btn-lg" onclick="procederAFormularioPostulacion()">
                            <i class="mdi mdi-school me-2"></i>
                            Proceder a Postulación
                        </button>
                    </div>
                </div>
            `;
        }
        
        // Nueva función para proceder al formulario de postulación
        function procederAFormularioPostulacion() {
            // Debug: verificar datos disponibles
            console.log('=== DEBUG DATOS USUARIO ===');
            console.log('nuevoUsuarioData completo:', nuevoUsuarioData);
            console.log('Campo nuevo_numero_documento:', document.getElementById('nuevo_numero_documento')?.value);
            console.log('===============================');
            // Cerrar el modal del wizard - método más directo
            const modalElement = document.getElementById('modalRegistroNuevo');
            if (modalElement) {
                // Intentar obtener la instancia existente
                let modalInstance = bootstrap.Modal.getInstance(modalElement);
                if (modalInstance) {
                    modalInstance.hide();
                } else {
                    // Si no hay instancia, crear una y cerrarla
                    modalInstance = new bootstrap.Modal(modalElement);
                    modalInstance.hide();
                }
                
                // También remover clases manualmente como respaldo
                modalElement.classList.remove('show');
                modalElement.style.display = 'none';
                
                // Remover backdrop si existe
                const backdrop = document.querySelector('.modal-backdrop');
                if (backdrop) {
                    backdrop.remove();
                }
                
                // Restaurar scroll del body
                document.body.classList.remove('modal-open');
                document.body.style.overflow = '';
                document.body.style.paddingRight = '';
            }
            
            // Abrir directamente el modal de búsqueda con el DNI pre-ingresado
            setTimeout(() => {
                // Establecer el DNI en el campo de búsqueda
                const dniField = document.getElementById('dniPostulanteExistente');
                if (dniField) {
                    // Obtener el DNI del formulario (que sí tiene el valor correcto)
                    let dni = document.getElementById('nuevo_numero_documento')?.value;
                    console.log('DNI del formulario nuevo_numero_documento:', dni);
                    
                    // Si no está en el formulario, intentar de nuevoUsuarioData
                    if (!dni && nuevoUsuarioData) {
                        dni = nuevoUsuarioData.numero_documento || nuevoUsuarioData.documento;
                    }
                    
                    // Limpiar el DNI (remover espacios, caracteres especiales)
                    if (dni) {
                        dni = dni.toString().replace(/\D/g, ''); // Solo dígitos
                        console.log('DNI capturado:', dni, 'Longitud:', dni.length);
                        dniField.value = dni;
                    } else {
                        console.log('No se pudo obtener el DNI desde ninguna fuente');
                    }
                }
                
                // Cambiar el tipo a 'existente' ya que ahora trataremos al usuario como postulante existente
                tipoPostulante = 'existente';
                console.log('Cambiado tipoPostulante a:', tipoPostulante);
                
                // Abrir el modal de búsqueda directamente
                const modalBuscar = document.getElementById('modalBuscarPostulante');
                const modalBuscarBS = new bootstrap.Modal(modalBuscar);
                modalBuscarBS.show();
                
                // Automáticamente hacer la búsqueda (solo si el DNI tiene 8 dígitos)
                setTimeout(() => {
                    const btnBuscar = document.getElementById('btnBuscarPorDNI');
                    const dniValue = document.getElementById('dniPostulanteExistente').value;
                    
                    console.log('Intentando búsqueda automática con DNI:', dniValue);
                    
                    if (btnBuscar && dniValue && dniValue.length === 8) {
                        console.log('Ejecutando búsqueda automática');
                        btnBuscar.click();
                    } else {
                        console.log('No se puede ejecutar búsqueda automática. DNI longitud:', dniValue?.length);
                        if (dniValue && dniValue.length !== 8) {
                            toastr.warning('El DNI debe tener exactamente 8 dígitos. Verifique el valor: ' + dniValue);
                        }
                        
                        // Como respaldo, establecer postulanteData manualmente con estructura compatible
                        if (nuevoUsuarioData) {
                            postulanteData = {
                                id: nuevoUsuarioData.id,
                                nombre: nuevoUsuarioData.nombre || '',
                                apellido_paterno: nuevoUsuarioData.apellido_paterno || '',
                                apellido_materno: nuevoUsuarioData.apellido_materno || '',
                                numero_documento: nuevoUsuarioData.numero_documento || dniValue || '',
                                email: nuevoUsuarioData.email
                            };
                            console.log('Establecido postulanteData como respaldo:', postulanteData);
                        }
                    }
                }, 500);
                
            }, 300);
        }
        
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
                                    <label for="edit-approved-codigo" class="form-label">Código de Postulante</label>
                                    <input type="text" class="form-control" id="edit-approved-codigo" name="codigo_postulante">
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

    <!-- Modal de Selección Tipo de Usuario -->
    <div class="modal fade" id="modalSeleccionTipo" tabindex="-1" role="dialog" aria-labelledby="modalSeleccionTipoLabel" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalSeleccionTipoLabel">
                        <i class="mdi mdi-account-question me-2"></i>
                        ¿Cómo desea realizar la postulación?
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="card h-100 border-primary hover-card" id="btnPostulanteNuevo" style="cursor: pointer;">
                                <div class="card-body text-center">
                                    <div class="mb-3">
                                        <i class="mdi mdi-account-plus-outline" style="font-size: 48px; color: #28a745;"></i>
                                    </div>
                                    <h5 class="card-title">Soy Postulante Nuevo</h5>
                                    <p class="card-text text-muted small">
                                        Primera vez postulando. Necesito crear una cuenta nueva.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="card h-100 border-info hover-card" id="btnPostulanteExistente" style="cursor: pointer;">
                                <div class="card-body text-center">
                                    <div class="mb-3">
                                        <i class="mdi mdi-account-check-outline" style="font-size: 48px; color: #17a2b8;"></i>
                                    </div>
                                    <h5 class="card-title">Ya Tengo Cuenta</h5>
                                    <p class="card-text text-muted small">
                                        Soy postulante recurrente o ya tengo cuenta creada.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Búsqueda de Postulante Existente -->
    <div class="modal fade" id="modalBuscarPostulante" tabindex="-1" role="dialog" aria-labelledby="modalBuscarPostulanteLabel" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="modalBuscarPostulanteLabel">
                        <i class="mdi mdi-account-search me-2"></i>
                        Buscar Postulante Existente
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="mdi mdi-information-outline"></i>
                        Ingrese el DNI del postulante para continuar con la postulación.
                    </div>
                    <form id="formBuscarPostulante">
                        <div class="mb-3">
                            <label for="dniPostulanteExistente" class="form-label">DNI del Postulante <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="dniPostulanteExistente" 
                                       maxlength="8" pattern="[0-9]{8}" required 
                                       placeholder="Ingrese el DNI">
                                <button class="btn btn-primary" type="button" id="btnBuscarPorDNI">
                                    <i class="mdi mdi-magnify"></i> Buscar
                                </button>
                            </div>
                            <div class="invalid-feedback">Ingrese un DNI válido de 8 dígitos</div>
                        </div>
                        <div id="resultadoBusqueda" style="display: none;">
                            <!-- Los resultados de búsqueda se mostrarán aquí -->
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" id="btnVolverSeleccion">
                        <i class="mdi mdi-arrow-left me-1"></i> Volver
                    </button>
                    <button type="button" class="btn btn-primary" id="btnContinuarPostulacion" style="display: none;" disabled>
                        <i class="mdi mdi-arrow-right me-1"></i> Continuar con Postulación
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Nueva Postulación Unificada (Se usará para ambos flujos) -->
    <div class="modal fade" id="nuevaPostulacionModal" tabindex="-1" role="dialog" aria-labelledby="nuevaPostulacionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="nuevaPostulacionModalLabel">
                        <i class="mdi mdi-account-plus me-2"></i>
                        <span id="tituloModalPostulacion">Nueva Postulación Completa</span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="max-height: 75vh; overflow-y: auto;">
                    <div id="postulacion-form-container">
                        <!-- El formulario se cargará aquí dinámicamente -->
                        <div class="text-center py-4" id="loadingContainer">
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

    <!-- Modal de Registro para Nuevos Postulantes - Wizard Completo -->
    <div class="modal fade" id="modalRegistroNuevo" tabindex="-1" role="dialog" aria-labelledby="modalRegistroNuevoLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header text-white" style="background: linear-gradient(135deg, var(--cepre-magenta) 0%, var(--cepre-navy) 100%);">
                    <h5 class="modal-title text-white" id="modalRegistroNuevoLabel">
                        <i class="mdi mdi-account-plus me-2"></i>Registro Completo - Nuevo Postulante
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="registration-wizard">
                        <!-- Wizard Progress Bar Completo -->
                        <div class="wizard-progress mb-4">
                            <!-- Progreso General -->
                            <div class="overall-progress-container mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="fw-semibold text-primary">Progreso del Registro</span>
                                    <div class="progress-percentage">
                                        <span id="overallPercentageWizard" class="fw-bold fs-5 text-primary">0%</span>
                                        <small class="text-muted ms-1">completado</small>
                                    </div>
                                </div>
                                <div class="progress overall-progress">
                                    <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                         style="width: 0%; background: linear-gradient(135deg, var(--cepre-magenta) 0%, var(--cepre-cyan) 100%);" 
                                         id="overallProgressBarWizard"></div>
                                </div>
                            </div>

                            <!-- Indicadores de pasos -->
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <!-- Paso 1: Postulante -->
                                <div class="step-indicator active" data-step="1">
                                    <div class="step-circle">
                                        <span class="step-number">1</span>
                                        <svg class="step-check d-none" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                            <polyline points="20,6 9,17 4,12"></polyline>
                                        </svg>
                                    </div>
                                    <span class="step-label">Postulante</span>
                                    <div class="step-progress-mini">
                                        <div class="mini-progress-bar" data-step="1" style="width: 0%;"></div>
                                    </div>
                                </div>
                                <div class="progress-line"></div>
                                
                                <!-- Paso 2: Padres -->
                                <div class="step-indicator" data-step="2">
                                    <div class="step-circle">
                                        <span class="step-number">2</span>
                                        <svg class="step-check d-none" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                            <polyline points="20,6 9,17 4,12"></polyline>
                                        </svg>
                                    </div>
                                    <span class="step-label">Padres</span>
                                    <div class="step-progress-mini">
                                        <div class="mini-progress-bar" data-step="2" style="width: 0%;"></div>
                                    </div>
                                </div>
                                <div class="progress-line"></div>
                                
                                <!-- Paso 3: Confirmación -->
                                <div class="step-indicator" data-step="3">
                                    <div class="step-circle">
                                        <span class="step-number">3</span>
                                        <svg class="step-check d-none" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                            <polyline points="20,6 9,17 4,12"></polyline>
                                        </svg>
                                    </div>
                                    <span class="step-label">Confirmación</span>
                                    <div class="step-progress-mini">
                                        <div class="mini-progress-bar" data-step="3" style="width: 0%;"></div>
                                    </div>
                                </div>
                                <div class="progress-line"></div>
                                
                                <!-- Paso 4: Postulación -->
                                <div class="step-indicator" data-step="4">
                                    <div class="step-circle">
                                        <span class="step-number">4</span>
                                        <svg class="step-check d-none" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                            <polyline points="20,6 9,17 4,12"></polyline>
                                        </svg>
                                    </div>
                                    <span class="step-label">Postulación</span>
                                    <div class="step-progress-mini">
                                        <div class="mini-progress-bar" data-step="4" style="width: 0%;"></div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="progress">
                                <div class="progress-bar" style="width: 25%; background: linear-gradient(135deg, var(--cepre-magenta) 0%, var(--cepre-cyan) 100%);"></div>
                            </div>
                            <div class="text-center mt-2">
                                <small class="text-muted">Paso <span id="currentStepWizard">1</span> de 4</small>
                            </div>
                        </div>

                        <!-- Formulario de Registro -->
                        <form id="formRegistroNuevo" class="needs-validation" novalidate>
                            @csrf
                            
                            <!-- PASO 1: Datos Personales del Postulante -->
                            <div class="wizard-step active" data-step="1">
                                <div class="step-header text-center mb-4">
                                    <div class="step-icon mb-3">
                                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--cepre-magenta);">
                                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                            <circle cx="12" cy="7" r="4"></circle>
                                        </svg>
                                    </div>
                                    <h4 class="step-title" style="color: var(--cepre-navy);">Datos Personales del Postulante</h4>
                                    <p class="step-subtitle text-muted">Complete la información personal del estudiante</p>
                                    <div class="field-counter">
                                        <span id="step1CounterWizard" class="badge bg-secondary">0 de 10 campos completados</span>
                                    </div>
                                </div>

                            <div class="row">
                                <!-- Tipo de Documento -->
                                <div class="col-md-6 mb-3">
                                    <label for="nuevo_tipo_documento" class="form-label">Tipo Documento <span class="text-danger">*</span></label>
                                    <select class="form-select" id="nuevo_tipo_documento" name="tipo_documento" required>
                                        <option value="">Seleccione...</option>
                                        <option value="DNI">DNI</option>
                                        <option value="CE">Carnet de Extranjería</option>
                                    </select>
                                    <div class="invalid-feedback">Seleccione el tipo de documento</div>
                                </div>

                                <!-- Número de Documento -->
                                <div class="col-md-6 mb-3">
                                    <label for="nuevo_numero_documento" class="form-label">Número Documento <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="nuevo_numero_documento"
                                               name="numero_documento" maxlength="8" pattern="[0-9]{8}" required>
                                        <button class="btn btn-outline-primary" type="button" id="btnConsultarReniecNuevo">
                                            <i class="mdi mdi-magnify"></i>
                                        </button>
                                    </div>
                                    <div class="invalid-feedback">Ingrese un número de documento válido</div>
                                    <small class="form-text text-muted">Presione el botón para consultar RENIEC y autocompletar</small>
                                </div>
                            </div>

                            <div class="row">
                                <!-- Nombres -->
                                <div class="col-md-6 mb-3">
                                    <label for="nuevo_nombre" class="form-label">Nombres <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="nuevo_nombre" name="nombre" required>
                                    <div class="invalid-feedback">Ingrese los nombres</div>
                                </div>

                                <!-- Apellido Paterno -->
                                <div class="col-md-6 mb-3">
                                    <label for="nuevo_apellido_paterno" class="form-label">Apellido Paterno <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="nuevo_apellido_paterno" name="apellido_paterno" required>
                                    <div class="invalid-feedback">Ingrese el apellido paterno</div>
                                </div>
                            </div>

                            <div class="row">
                                <!-- Apellido Materno -->
                                <div class="col-md-6 mb-3">
                                    <label for="nuevo_apellido_materno" class="form-label">Apellido Materno <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="nuevo_apellido_materno" name="apellido_materno" required>
                                    <div class="invalid-feedback">Ingrese el apellido materno</div>
                                </div>

                                <!-- Fecha de Nacimiento -->
                                <div class="col-md-6 mb-3">
                                    <label for="nuevo_fecha_nacimiento" class="form-label">Fecha Nacimiento <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="nuevo_fecha_nacimiento"
                                           name="fecha_nacimiento" max="{{ date('Y-m-d', strtotime('-14 years')) }}" required>
                                    <div class="invalid-feedback">Ingrese la fecha de nacimiento</div>
                                </div>
                            </div>

                            <div class="row">
                                <!-- Género -->
                                <div class="col-md-6 mb-3">
                                    <label for="nuevo_genero" class="form-label">Género <span class="text-danger">*</span></label>
                                    <select class="form-select" id="nuevo_genero" name="genero" required>
                                        <option value="">Seleccione...</option>
                                        <option value="M">Masculino</option>
                                        <option value="F">Femenino</option>
                                    </select>
                                    <div class="invalid-feedback">Seleccione el género</div>
                                </div>

                                <!-- Teléfono -->
                                <div class="col-md-6 mb-3">
                                    <label for="nuevo_telefono" class="form-label">Teléfono/Celular <span class="text-danger">*</span></label>
                                    <input type="tel" class="form-control" id="nuevo_telefono"
                                           name="telefono" pattern="[0-9]{9}" maxlength="9" required>
                                    <div class="invalid-feedback">Ingrese un teléfono válido (9 dígitos)</div>
                                </div>
                            </div>

                            <!-- Dirección -->
                            <div class="mb-3">
                                <label for="nuevo_direccion" class="form-label">Dirección <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nuevo_direccion" name="direccion" required>
                                <div class="invalid-feedback">Ingrese la dirección</div>
                            </div>

                            <div class="row">
                                <!-- Email -->
                                <div class="col-md-6 mb-3">
                                    <label for="nuevo_email" class="form-label">Correo Electrónico <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" id="nuevo_email" name="email" required>
                                    <div class="invalid-feedback">Ingrese un correo válido</div>
                                </div>

                                <!-- Contraseña -->
                                <div class="col-md-6 mb-3">
                                    <label for="nuevo_password" class="form-label">Contraseña <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="nuevo_password"
                                               name="password" minlength="8" required>
                                        <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('nuevo_password')">
                                            <i class="mdi mdi-eye"></i>
                                        </button>
                                    </div>
                                    <div class="invalid-feedback">La contraseña debe tener al menos 8 caracteres</div>
                                </div>
                            </div>

                            <!-- Confirmar Contraseña -->
                            <div class="mb-4">
                                <label for="nuevo_password_confirmation" class="form-label">Confirmar Contraseña <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" id="nuevo_password_confirmation"
                                       name="password_confirmation" minlength="8" required>
                                <div class="invalid-feedback">Las contraseñas no coinciden</div>
                            </div>

                            </div>

                            <!-- PASO 2: Datos de Padres/Tutores -->
                            <div class="wizard-step" data-step="2" style="display: none;">
                                <div class="step-header text-center mb-4">
                                    <div class="step-icon mb-3">
                                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--cepre-magenta);">
                                            <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                            <circle cx="8.5" cy="7" r="4"></circle>
                                            <path d="M20 21v-2a4 4 0 0 0-4-4H13"></path>
                                            <circle cx="17.5" cy="7" r="4"></circle>
                                        </svg>
                                    </div>
                                    <h4 class="step-title" style="color: var(--cepre-navy);">Datos de Padres/Tutores</h4>
                                    <p class="step-subtitle text-muted">Información del padre y/o la madre</p>
                                    <div class="field-counter">
                                        <span id="step2CounterWizard" class="badge bg-secondary">0 de 12 campos completados</span>
                                    </div>
                                </div>

                                <!-- Sección Padre/Tutor -->
                                <h5 class="fw-semibold text-dark mb-3">Datos del Padre/Tutor</h5>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="padre_tipo_doc" class="form-label">Tipo Documento <span class="text-danger">*</span></label>
                                        <select class="form-select" id="padre_tipo_doc" name="padre_tipo_documento" required>
                                            <option value="">Seleccione...</option>
                                            <option value="DNI">DNI</option>
                                            <option value="CE">CE</option>
                                        </select>
                                        <div class="invalid-feedback">Seleccione el tipo de documento</div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="padre_numero_doc" class="form-label">Número Documento <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <input type="text" class="form-control" id="padre_numero_doc"
                                                   name="padre_numero_documento" maxlength="8" pattern="[0-9]{8}" required>
                                            <button class="btn btn-outline-primary" type="button" id="btnConsultarReniecPadre">
                                                <i class="mdi mdi-magnify"></i>
                                            </button>
                                        </div>
                                        <div class="invalid-feedback">Ingrese un número válido</div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="padre_nombre" class="form-label">Nombres <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="padre_nombre" name="padre_nombre" required>
                                        <div class="invalid-feedback">Ingrese los nombres del padre</div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="padre_apellidos" class="form-label">Apellidos <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="padre_apellidos" name="padre_apellidos" required>
                                        <div class="invalid-feedback">Ingrese los apellidos del padre</div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="padre_telefono" class="form-label">Teléfono <span class="text-danger">*</span></label>
                                        <input type="tel" class="form-control" id="padre_telefono"
                                               name="padre_telefono" pattern="[0-9]{9}" maxlength="9" required>
                                        <div class="invalid-feedback">Ingrese un teléfono válido</div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="padre_email" class="form-label">Correo del Padre</label>
                                        <input type="email" class="form-control" id="padre_email" name="padre_email">
                                        <div class="invalid-feedback">Ingrese un correo válido</div>
                                    </div>
                                </div>

                                <hr class="my-4">

                                <!-- Sección Madre/Tutora -->
                                <h5 class="fw-semibold text-dark mb-3">Datos de la Madre/Tutora</h5>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="madre_tipo_doc" class="form-label">Tipo Documento <span class="text-danger">*</span></label>
                                        <select class="form-select" id="madre_tipo_doc" name="madre_tipo_documento" required>
                                            <option value="">Seleccione...</option>
                                            <option value="DNI">DNI</option>
                                            <option value="CE">CE</option>
                                        </select>
                                        <div class="invalid-feedback">Seleccione el tipo de documento</div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="madre_numero_doc" class="form-label">Número Documento <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <input type="text" class="form-control" id="madre_numero_doc"
                                                   name="madre_numero_documento" maxlength="8" pattern="[0-9]{8}" required>
                                            <button class="btn btn-outline-primary" type="button" id="btnConsultarReniecMadre">
                                                <i class="mdi mdi-magnify"></i>
                                            </button>
                                        </div>
                                        <div class="invalid-feedback">Ingrese un número válido</div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="madre_nombre" class="form-label">Nombres <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="madre_nombre" name="madre_nombre" required>
                                        <div class="invalid-feedback">Ingrese los nombres de la madre</div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="madre_apellidos" class="form-label">Apellidos <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="madre_apellidos" name="madre_apellidos" required>
                                        <div class="invalid-feedback">Ingrese los apellidos de la madre</div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="madre_telefono" class="form-label">Teléfono <span class="text-danger">*</span></label>
                                        <input type="tel" class="form-control" id="madre_telefono"
                                               name="madre_telefono" pattern="[0-9]{9}" maxlength="9" required>
                                        <div class="invalid-feedback">Ingrese un teléfono válido</div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="madre_email" class="form-label">Correo de la Madre</label>
                                        <input type="email" class="form-control" id="madre_email" name="madre_email">
                                        <div class="invalid-feedback">Ingrese un correo válido</div>
                                    </div>
                                </div>
                            </div>

                            <!-- PASO 3: Confirmación -->
                            <div class="wizard-step" data-step="3" style="display: none;">
                                <div class="step-header text-center mb-4">
                                    <div class="step-icon mb-3">
                                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--cepre-magenta);">
                                            <path d="M9 12l2 2 4-4"></path>
                                            <path d="M21 12c-1 0-3-1-3-3s2-3 3-3 3 1 3 3-2 3-3 3"></path>
                                            <path d="M3 12c1 0 3-1 3-3s-2-3-3-3-3 1-3 3 2 3 3 3"></path>
                                        </svg>
                                    </div>
                                    <h4 class="step-title" style="color: var(--cepre-navy);">Confirmación de Datos</h4>
                                    <p class="step-subtitle text-muted">Revise toda la información antes de crear la cuenta</p>
                                </div>

                                <div id="confirmationSummaryWizard" class="confirmation-container">
                                    <!-- Resumen será generado por JavaScript -->
                                </div>

                                <!-- Términos y Condiciones -->
                                <div class="form-check mb-4">
                                    <input class="form-check-input" type="checkbox" id="nuevo_terms" name="terms" required>
                                    <label class="form-check-label" for="nuevo_terms">
                                        Acepto los <a href="#" class="text-primary">términos y condiciones</a> y la
                                        <a href="#" class="text-primary">política de privacidad</a>
                                    </label>
                                    <div class="invalid-feedback">Debe aceptar los términos y condiciones</div>
                                </div>
                            </div>

                            <!-- PASO 4: Formulario de Postulación -->
                            <div class="wizard-step" data-step="4" style="display: none;">
                                <div class="step-header text-center mb-4">
                                    <div class="step-icon mb-3">
                                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--cepre-magenta);">
                                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                            <polyline points="14,2 14,8 20,8"></polyline>
                                            <line x1="16" y1="13" x2="8" y2="13"></line>
                                            <line x1="16" y1="17" x2="8" y2="17"></line>
                                            <polyline points="10,9 9,9 8,9"></polyline>
                                        </svg>
                                    </div>
                                    <h4 class="step-title" style="color: var(--cepre-navy);">Formulario de Postulación</h4>
                                    <p class="step-subtitle text-muted">Complete los datos para su postulación</p>
                                </div>

                                <div id="formularioPostulacionContainer">
                                    <!-- El formulario de postulación se cargará aquí dinámicamente -->
                                    <div class="text-center py-4">
                                        <div class="cepre-spinner"></div>
                                        <p class="mt-3 text-muted">Preparando formulario de postulación...</p>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="modal-footer">
                    <!-- Botón Cerrar -->
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="mdi mdi-close me-1"></i> Cancelar
                    </button>
                    
                    <!-- Navegación del Wizard -->
                    <div class="wizard-navigation">
                        <!-- Botón Anterior -->
                        <button type="button" class="btn btn-outline-primary me-2" id="prevStepBtnWizard" onclick="previousStepWizard()" style="display: none;">
                            <i class="mdi mdi-chevron-left me-1"></i> Anterior
                        </button>
                        
                        <!-- Botón Siguiente / Finalizar -->
                        <button type="button" class="btn btn-primary" id="nextStepBtnWizard" onclick="nextStepWizard()">
                            <span class="btn-text">Siguiente</span>
                            <i class="mdi mdi-chevron-right ms-1"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endpush