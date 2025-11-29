<!-- Estilos base CSS para el modal y wizard, adaptados a los colores del logo -->
<style>
    /* VARIABLES GLOBALES (Colores Institucionales de CEPRE UNAMAD: Verde Lima, Magenta/Rosa, Azul) */
    :root {
        --color-principal: #8bc34a; /* Verde Lima (Dominante en Logo) */
        --color-secundario: #e91e63; /* Magenta/Rosa (Libros en Logo) */
        --color-acento: #03a9f4;    /* Azul Cían (Acento) */
        --color-texto-oscuro: #1f2937; /* Gris Oscuro para legibilidad */
        --color-fondo-claro: #f8f8f8;
    }

    .modal {
        display: none; /* Asegura que esté oculto por defecto */
        position: fixed;
        z-index: 1050; 
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0, 0, 0, 0.6);
    }
    .modal-content {
        background-color: #fefefe;
        margin: 3% auto; 
        padding: 30px;
        border: none;
        border-radius: 15px; /* Bordes más suaves */
        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.3);
    }

    /* WIZARD STYLES */
    .wizard-progress {
        display: flex;
        justify-content: space-between;
        margin-bottom: 35px;
        position: relative;
    }
    .progress-line {
        position: absolute;
        top: 18px;
        left: 0;
        width: 100%;
        height: 4px;
        background: #e5e7eb;
        z-index: 1;
        border-radius: 2px;
    }
    .step-item {
        position: relative;
        z-index: 2;
        text-align: center;
        flex: 1;
        min-width: 0;
    }
    .step-circle {
        width: 38px;
        height: 38px;
        background: #e0f2f1; /* Fondo suave para inactivo */
        color: var(--color-principal);
        font-weight: 700;
        border: 2px solid var(--color-principal);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 8px;
        transition: all 0.3s ease;
    }
    .step-item.active .step-circle {
        background: var(--color-principal);
        color: white;
        border-color: var(--color-principal);
        box-shadow: 0 0 0 5px rgba(139, 195, 74, 0.3); /* Efecto de brillo */
        transform: scale(1.05);
    }
    .step-item.active span {
        font-weight: bold;
        color: var(--color-texto-oscuro);
    }
    .step-item span {
        font-size: 11px;
        display: block;
        color: #6b7280;
    }
    /* Líneas separadoras y títulos */
    .form-section-title {
        color: var(--color-secundario); /* Magenta */
        border-bottom: 2px solid var(--color-principal); /* Verde Lima */
        padding-bottom: 10px;
        margin-bottom: 25px;
        font-weight: 700;
        font-size: 1.3rem;
    }

    /* Estilo de Botones */
    .btn-next-prev {
        padding: 10px 25px;
        border-radius: 8px;
        font-weight: 600;
        transition: background-color 0.2s, transform 0.1s;
    }
    #nextBtn {
        background-color: var(--color-principal);
        color: white;
        border-color: var(--color-principal);
    }
    #prevBtn {
        background-color: var(--color-secundario);
        color: white;
        border-color: var(--color-secundario);
    }
    #nextBtn:hover {
        background-color: #689f38; /* Verde más oscuro */
        border-color: #689f38;
        transform: translateY(-1px);
    }
    #prevBtn:hover {
        background-color: #ad1457; /* Magenta más oscuro */
        border-color: #ad1457;
        transform: translateY(-1px);
    }

    /* Estilos de inputs y selects limpios */
    .form-control, .form-select {
        border-radius: 8px;
        padding: 10px 12px;
        border: 1px solid #d1d5db;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }
    .form-control:focus, .form-select:focus {
        border-color: var(--color-principal);
        box-shadow: 0 0 0 0.25rem rgba(139, 195, 74, 0.25);
    }
</style>

<!-- Modal de Postulación -->
<div id="postulacionModal" class="modal">
    <div class="modal-content" style="max-width: 900px; width: 95%;">
        <span class="close-button" onclick="closeModal('postulacionModal')" style="position: absolute; top: 15px; right: 25px; font-size: 24px; color: #6b7280; cursor: pointer;">&times;</span>
        <h3 style="color: var(--color-secundario); margin-bottom: 30px; text-align: center; font-weight: 800;">
            <!-- RUTA ACTUALIZADA CON asset() Y onerror -->
            <img src="{{ asset('assets_cepre/img/logo/logocepre1.svg') }}" onerror="this.onerror=null; this.src='https://placehold.co/150x60/8bc34a/ffffff?text=CEPRE+UNAMAD';" alt="CEPRE UNAMAD" style="height: 40px; margin-right: 10px; vertical-align: middle;">
            Postulación CEPRE UNAMAD
        </h3>

        <!-- Wizard Steps -->
        <div class="wizard-progress">
            <div class="progress-line" style="background: #e5e7eb;"></div>
            
            <div class="step-item active" data-step="1">
                <div class="step-circle">1</div>
                <span>Personal</span>
            </div>
            <div class="step-item" data-step="2">
                <div class="step-circle">2</div>
                <span>Padre</span>
            </div>
            <div class="step-item" data-step="3">
                <div class="step-circle">3</div>
                <span>Madre</span>
            </div>
            <div class="step-item" data-step="4">
                <div class="step-circle">4</div>
                <span>Académico</span>
            </div>
            <div class="step-item" data-step="5">
                <div class="step-circle">5</div>
                <span>Docs/Pago</span>
            </div>
            <div class="step-item" data-step="6">
                <div class="step-circle">6</div>
                <span>Confirmar</span>
            </div>
        </div>

        <form id="formPostulacionPublica" enctype="multipart/form-data" novalidate>
            @csrf
            
            <!-- Paso 1: Datos Personales (Incluye validación DNI inicial) -->
            <div class="step-content active" data-step="1">
                <h5 class="form-section-title">Datos Personales</h5>
                
                <!-- Validación DNI Inicial -->
                <div class="mb-4 p-3 bg-light rounded-lg border border-gray-200">
                    <label for="check_dni" class="form-label" style="font-weight: bold;">Verificar DNI</label>
                    <div class="d-flex gap-3">
                        <input type="text" class="form-control" id="check_dni" name="check_dni" maxlength="8" pattern="[0-9]{8}" placeholder="Ingrese DNI para verificar" required>
                        <button type="button" class="btn btn-next-prev shadow-sm" id="btn-verificar-dni" style="background-color: var(--color-principal); color: white;">
                            <i class="fas fa-search me-1"></i> Verificar
                        </button>
                    </div>
                    <div id="dni_feedback" class="mt-2 small text-muted">Asegúrese de ingresar un DNI válido de 8 dígitos.</div>
                </div>

                <div id="personal_fields" style="display: none;">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">DNI</label>
                            <input type="hidden" name="estudiante_tipo_documento" value="1">
                            <input type="text" class="form-control" id="estudiante_dni" name="estudiante_dni" readonly>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Nombres</label>
                            <input type="text" class="form-control" id="estudiante_nombre" name="estudiante_nombre" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Apellido Paterno</label>
                            <input type="text" class="form-control" id="estudiante_apellido_paterno" name="estudiante_apellido_paterno" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Apellido Materno</label>
                            <input type="text" class="form-control" id="estudiante_apellido_materno" name="estudiante_apellido_materno" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Fecha Nacimiento</label>
                            <input type="date" class="form-control" id="estudiante_fecha_nacimiento" name="estudiante_fecha_nacimiento" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Género</label>
                            <select class="form-select" id="estudiante_genero" name="estudiante_genero" required>
                                <option value="">Seleccione</option>
                                <option value="M">Masculino</option>
                                <option value="F">Femenino</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Teléfono</label>
                            <input type="tel" class="form-control" id="estudiante_telefono" name="estudiante_telefono" pattern="[0-9]{9}" maxlength="9" required>
                        </div>
                        <div class="col-md-8 mb-3">
                            <label class="form-label">Dirección</label>
                            <input type="text" class="form-control" id="estudiante_direccion" name="estudiante_direccion" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Email (Usuario)</label>
                            <input type="email" class="form-control" id="estudiante_email" name="estudiante_email" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Contraseña</label>
                            <input type="password" class="form-control" id="estudiante_password" name="estudiante_password" required minlength="8">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Confirmar Pass</label>
                            <input type="password" class="form-control" id="estudiante_password_confirmation" name="estudiante_password_confirmation" required minlength="8">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Paso 2: Datos del Padre -->
            <div class="step-content" data-step="2" style="display: none;">
                <h5 class="form-section-title">Datos del Padre</h5>
                <div class="alert alert-info py-2 small"><i class="fas fa-info-circle me-1"></i> Opcional si es mayor de edad, pero recomendado.</div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">DNI Padre</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="padre_dni" name="padre_dni" maxlength="8" pattern="[0-9]{8}">
                            <button class="btn btn-outline-secondary" type="button" onclick="consultarDNIPadre('padre')">RENIEC</button>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nombres</label>
                        <input type="text" class="form-control" id="padre_nombre" name="padre_nombre">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Apellidos</label>
                        <input type="text" class="form-control" id="padre_apellidos" name="padre_apellidos">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Teléfono</label>
                        <input type="tel" class="form-control" id="padre_telefono" name="padre_telefono" pattern="[0-9]{9}" maxlength="9">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Ocupación</label>
                        <input type="text" class="form-control" id="padre_ocupacion" name="padre_ocupacion">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" id="padre_email" name="padre_email">
                    </div>
                </div>
            </div>

            <!-- Paso 3: Datos de la Madre -->
            <div class="step-content" data-step="3" style="display: none;">
                <h5 class="form-section-title">Datos de la Madre</h5>
                <div class="alert alert-info py-2 small"><i class="fas fa-info-circle me-1"></i> Opcional si es mayor de edad, pero recomendado.</div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">DNI Madre</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="madre_dni" name="madre_dni" maxlength="8" pattern="[0-9]{8}">
                            <button class="btn btn-outline-secondary" type="button" onclick="consultarDNIPadre('madre')">RENIEC</button>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Nombres</label>
                        <input type="text" class="form-control" id="madre_nombre" name="madre_nombre">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Apellidos</label>
                        <input type="text" class="form-control" id="madre_apellidos" name="madre_apellidos">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Teléfono</label>
                        <input type="tel" class="form-control" id="madre_telefono" name="madre_telefono" pattern="[0-9]{9}" maxlength="9">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Ocupación</label>
                        <input type="text" class="form-control" id="madre_ocupacion" name="madre_ocupacion">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" id="madre_email" name="madre_email">
                    </div>
                </div>
            </div>

            <!-- Paso 4: Datos Académicos -->
            <div class="step-content" data-step="4" style="display: none;">
                <h5 class="form-section-title">Datos Académicos</h5>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Carrera a Postular</label>
                        <!-- Usando form-select limpio -->
                        <select class="form-select" id="carrera_id" name="carrera_id" required>
                            <option value="">Seleccione</option>
                            <!-- NOTE: Asegúrate de que tu backend (Laravel Blade) renderice correctamente el foreach -->
                            @foreach(\App\Models\Carrera::where('estado', 1)->get() as $carrera)
                                <option value="{{ $carrera->id }}">{{ $carrera->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Turno</label>
                        <!-- Usando form-select limpio -->
                        <select class="form-select" id="turno_id" name="turno_id" required>
                            <option value="">Seleccione</option>
                            <!-- NOTE: Asegúrate de que tu backend (Laravel Blade) renderice correctamente el foreach -->
                            @foreach(\App\Models\Turno::where('estado', 1)->get() as $turno)
                                <option value="{{ $turno->id }}">{{ $turno->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Tipo de Inscripción</label>
                        <select class="form-select" id="tipo_inscripcion" name="tipo_inscripcion" required>
                            <option value="Regular">Regular</option>
                            <option value="Exonerado">Exonerado</option>
                            <option value="Beca">Beca</option>
                        </select>
                    </div>
                    <div class="col-md-12 my-3">
                        <h6 class="form-section-title" style="border-bottom: 1px solid #d1d5db;">Ubicación del Colegio</h6>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Departamento</label>
                        <select class="form-select" id="departamento" name="departamento" required>
                             <!-- Las opciones se cargan por JS -->
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Provincia</label>
                        <select class="form-select" id="provincia" name="provincia" required disabled>
                             <option value="">Seleccione departamento primero</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Distrito</label>
                        <select class="form-select" id="distrito" name="distrito" required disabled>
                            <option value="">Seleccione provincia primero</option>
                        </select>
                    </div>
                    <div class="col-md-8 mb-3">
                        <label class="form-label">Colegio de Procedencia</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="buscar_colegio" placeholder="Buscar colegio..." disabled>
                            <button class="btn btn-outline-secondary" type="button" id="btnBuscarColegio" disabled>
                                <i class="fas fa-search"></i> Buscar
                            </button>
                        </div>
                        <div id="sugerencias-colegios" class="list-group mt-2 shadow-sm" style="max-height: 200px; overflow-y: auto;"></div>
                        <div id="colegio-seleccionado" class="alert alert-success mt-2 p-2 small" style="display: none;">
                            <strong>Colegio seleccionado:</strong> <span id="nombre-colegio-seleccionado"></span>
                        </div>
                        <input type="hidden" id="centro_educativo_id" name="centro_educativo_id" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Año de Egreso</label>
                        <input type="number" class="form-control" id="anio_egreso" name="anio_egreso" min="1990" max="{{ date('Y') }}" required>
                    </div>
                </div>
            </div>

            <!-- Paso 5: Documentos y Pago -->
            <div class="step-content" data-step="5" style="display: none;">
                <h5 class="form-section-title">Documentos y Pago</h5>
                
                <!-- Sección de Pago -->
                <div class="card mb-4 shadow-sm border-0">
                    <div class="card-header fw-bold text-white p-3" style="background-color: var(--color-secundario);">
                        <i class="fas fa-money-check-alt me-1"></i> Validación de Pago Automática
                    </div>
                    <div class="card-body">
                        <div class="alert alert-warning py-2 small" style="font-size: 14px;">
                            <i class="fas fa-exclamation-triangle me-1"></i> 
                            <strong>Nota:</strong> Los pagos se están buscando automáticamente con su DNI. Por favor espere o use la búsqueda manual si el DNI es diferente.
                        </div>
                        
                        <div class="mb-3 d-flex align-items-center justify-content-between">
                            <label for="voucher_secuencia" class="form-label mb-0 fw-bold">DNI de Búsqueda:</label>
                            <div class="d-flex gap-2 align-items-center">
                                <span class="badge bg-secondary py-2 px-3 fs-6" id="dni_display" style="min-width: 100px;">Cargando...</span>
                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="habilitarBusquedaManual()" title="Usar otro DNI">
                                    <i class="fas fa-edit"></i> Editar
                                </button>
                            </div>
                            <input type="hidden" class="form-control" id="voucher_secuencia" name="voucher_secuencia" readonly>
                        </div>
                        
                        <div id="pago_feedback" class="mt-2" style="min-height: 20px;"></div>
                        
                        <!-- CAMPOS OCULTOS REQUERIDOS POR LA BD -->
                        <input type="hidden" id="monto_matricula" name="monto_matricula">
                        <input type="hidden" id="monto_ensenanza" name="monto_ensenanza">
                        <input type="hidden" id="monto_total_pagado" name="monto_total_pagado"> <!-- Campo agregado -->

                        <div class="mt-2" style="display: none;" id="fecha_emision_container">
                            <label>Fecha de Emisión (Voucher)</label>
                            <input type="date" class="form-control" id="fecha_emision_voucher" name="fecha_emision_voucher">
                        </div>
                    </div>
                    
                    <!-- Aquí se inyecta la lista de vouchers (voucher_details) -->
                    <div id="voucher_details" style="display: none; margin-top: 10px; padding: 0 15px 15px;"></div>
                </div>

                <!-- Carga de Archivos -->
                <h5 class="form-section-title">Carga de Documentos (PDF, JPG/PNG)</h5>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Foto del Estudiante (JPG/PNG)</label>
                        <input type="file" class="form-control" id="foto" name="foto" accept="image/*" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">DNI Escaneado (PDF)</label>
                        <input type="file" class="form-control" id="dni_pdf" name="dni_pdf" accept="application/pdf" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Certificado de Estudios (PDF)</label>
                        <input type="file" class="form-control" id="certificado_estudios" name="certificado_estudios" accept="application/pdf" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Voucher Escaneado (PDF/Img)</label>
                        <input type="file" class="form-control" id="voucher_pago" name="voucher_pago" accept="application/pdf,image/*" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Carta de Compromiso (PDF)</label>
                        <input type="file" class="form-control" id="carta_compromiso" name="carta_compromiso" accept="application/pdf">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Constancia de Estudios (PDF)</label>
                        <input type="file" class="form-control" id="constancia_estudios" name="constancia_estudios" accept="application/pdf">
                    </div>
                </div>
            </div>

            <!-- Paso 6: Confirmación -->
            <div class="step-content" data-step="6" style="display: none; text-align: center;">
                <i class="fas fa-check-circle" style="font-size: 50px; color: var(--color-principal); margin-bottom: 20px;"></i>
                <h4>¡Todo listo!</h4>
                <p>Por favor revise sus datos antes de enviar.</p>
                <div id="resumen_final" style="text-align: left; background: var(--color-fondo-claro); padding: 15px; margin: 20px 0; border-radius: 8px; max-height: 300px; overflow-y: auto;"></div>
                
                <div class="form-check mb-3" style="text-align: left;">
                    <input class="form-check-input" type="checkbox" id="confirmarDatos" required>
                    <label class="form-check-label" for="confirmarDatos">
                        Declaro que toda la información es verídica.
                    </label>
                </div>

                <button type="submit" class="btn btn-success btn-next-prev" style="background-color: var(--color-principal); border-color: var(--color-principal); padding: 12px 30px; font-size: 16px;">
                    ENVIAR POSTULACIÓN
                </button>
            </div>

            <!-- Navegación -->
            <div class="wizard-buttons" style="margin-top: 30px; display: flex; justify-content: space-between;">
                <button type="button" class="btn btn-secondary btn-next-prev" id="prevBtn" onclick="nextPrev(-1)" style="display: none;">Anterior</button>
                <button type="button" class="btn btn-primary btn-next-prev" id="nextBtn" onclick="nextPrev(1)">Siguiente</button>
            </div>
        </form>
    </div>
</div>