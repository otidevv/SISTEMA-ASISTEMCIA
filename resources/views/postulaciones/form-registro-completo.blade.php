<!-- Meta CSRF Token -->
<meta name="csrf-token" content="{{ csrf_token() }}">

<!-- Wizard de Pasos Extendido para Registro + Postulación -->
<div class="wizard-step-indicator mb-4">
    <div class="wizard-step active" data-step="1">
        <div class="wizard-step-number">1</div>
        <div class="wizard-step-title">Datos Personales</div>
    </div>
    <div class="wizard-step" data-step="2">
        <div class="wizard-step-number">2</div>
        <div class="wizard-step-title">Datos del Padre</div>
    </div>
    <div class="wizard-step" data-step="3">
        <div class="wizard-step-number">3</div>
        <div class="wizard-step-title">Datos de la Madre</div>
    </div>
    <div class="wizard-step" data-step="4">
        <div class="wizard-step-number">4</div>
        <div class="wizard-step-title">Datos Académicos</div>
    </div>
    <div class="wizard-step" data-step="5">
        <div class="wizard-step-number">5</div>
        <div class="wizard-step-title">Documentos</div>
    </div>
    <div class="wizard-step" data-step="6">
        <div class="wizard-step-number">6</div>
        <div class="wizard-step-title">Confirmación</div>
    </div>
</div>

<!-- Barra de Progreso -->
<div class="progress mb-4" style="height: 6px;">
    <div class="progress-bar bg-success" role="progressbar" style="width: 16.66%;" id="wizardProgress"></div>
</div>

<!-- Formulario de Registro Completo -->
<form id="formRegistroCompleto" enctype="multipart/form-data">
    @csrf
    
    <!-- Paso 1: Datos Personales del Estudiante -->
    <div class="wizard-content active" data-step="1">
        <h5 class="mb-4">
            <i class="mdi mdi-account me-2 text-primary"></i>
            Datos Personales del Estudiante
        </h5>
        
        <div class="row">
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="estudiante_nombre" class="form-label">Nombres <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="estudiante_nombre" name="estudiante_nombre" required>
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="estudiante_apellido_paterno" class="form-label">Apellido Paterno <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="estudiante_apellido_paterno" name="estudiante_apellido_paterno" required>
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="estudiante_apellido_materno" class="form-label">Apellido Materno <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="estudiante_apellido_materno" name="estudiante_apellido_materno" required>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-3">
                <div class="mb-3">
                    <label for="estudiante_dni" class="form-label">DNI <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="estudiante_dni" name="estudiante_dni" 
                               maxlength="8" pattern="[0-9]{8}" required>
                        <button class="btn btn-outline-primary" type="button" onclick="consultarDNI('estudiante')" title="Consultar RENIEC">
                            <i class="mdi mdi-magnify"></i> RENIEC
                        </button>
                    </div>
                    <div class="invalid-feedback">Ingrese un DNI válido de 8 dígitos</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="mb-3">
                    <label for="estudiante_fecha_nacimiento" class="form-label">Fecha de Nacimiento <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="estudiante_fecha_nacimiento" name="estudiante_fecha_nacimiento" required>
                </div>
            </div>
            <div class="col-md-3">
                <div class="mb-3">
                    <label for="estudiante_genero" class="form-label">Género <span class="text-danger">*</span></label>
                    <select class="form-select" id="estudiante_genero" name="estudiante_genero" required>
                        <option value="">Seleccione</option>
                        <option value="M">Masculino</option>
                        <option value="F">Femenino</option>
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="mb-3">
                    <label for="estudiante_telefono" class="form-label">Teléfono <span class="text-danger">*</span></label>
                    <input type="tel" class="form-control" id="estudiante_telefono" name="estudiante_telefono" 
                           pattern="[0-9]{9}" maxlength="9" required>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-12">
                <div class="mb-3">
                    <label for="estudiante_direccion" class="form-label">Dirección <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="estudiante_direccion" name="estudiante_direccion" required>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="estudiante_email" class="form-label">Correo Electrónico <span class="text-danger">*</span></label>
                    <input type="email" class="form-control" id="estudiante_email" name="estudiante_email" required>
                    <small class="text-muted">Este será su usuario para ingresar al sistema</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="mb-3">
                    <label for="estudiante_password" class="form-label">Contraseña <span class="text-danger">*</span></label>
                    <input type="password" class="form-control" id="estudiante_password" name="estudiante_password" 
                           minlength="8" required>
                    <small class="text-muted">Mínimo 8 caracteres</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="mb-3">
                    <label for="estudiante_password_confirmation" class="form-label">Confirmar Contraseña <span class="text-danger">*</span></label>
                    <input type="password" class="form-control" id="estudiante_password_confirmation" 
                           name="estudiante_password_confirmation" minlength="8" required>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Paso 2: Datos del Padre -->
    <div class="wizard-content" data-step="2" style="display: none;">
        <h5 class="mb-4">
            <i class="mdi mdi-account-tie me-2 text-info"></i>
            Datos del Padre o Tutor
        </h5>
        
        <div class="alert alert-info">
            <i class="mdi mdi-information-outline"></i>
            Complete esta sección si el estudiante es menor de edad o si desea registrar información del padre.
        </div>
        
        <div class="row">
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="padre_nombre" class="form-label">Nombres</label>
                    <input type="text" class="form-control" id="padre_nombre" name="padre_nombre">
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="padre_apellido_paterno" class="form-label">Apellido Paterno</label>
                    <input type="text" class="form-control" id="padre_apellido_paterno" name="padre_apellido_paterno">
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="padre_apellido_materno" class="form-label">Apellido Materno</label>
                    <input type="text" class="form-control" id="padre_apellido_materno" name="padre_apellido_materno">
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="padre_dni" class="form-label">DNI</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="padre_dni" name="padre_dni" 
                               maxlength="8" pattern="[0-9]{8}">
                        <button class="btn btn-outline-primary" type="button" onclick="consultarDNI('padre')" title="Consultar RENIEC">
                            <i class="mdi mdi-magnify"></i> RENIEC
                        </button>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="padre_telefono" class="form-label">Teléfono</label>
                    <input type="tel" class="form-control" id="padre_telefono" name="padre_telefono" 
                           pattern="[0-9]{9}" maxlength="9">
                </div>
            </div>
        </div>
    </div>
    
    <!-- Paso 3: Datos de la Madre -->
    <div class="wizard-content" data-step="3" style="display: none;">
        <h5 class="mb-4">
            <i class="mdi mdi-account-heart me-2 text-danger"></i>
            Datos de la Madre o Tutora
        </h5>
        
        <div class="alert alert-info">
            <i class="mdi mdi-information-outline"></i>
            Complete esta sección si el estudiante es menor de edad o si desea registrar información de la madre.
        </div>
        
        <div class="row">
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="madre_nombre" class="form-label">Nombres</label>
                    <input type="text" class="form-control" id="madre_nombre" name="madre_nombre">
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="madre_apellido_paterno" class="form-label">Apellido Paterno</label>
                    <input type="text" class="form-control" id="madre_apellido_paterno" name="madre_apellido_paterno">
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="madre_apellido_materno" class="form-label">Apellido Materno</label>
                    <input type="text" class="form-control" id="madre_apellido_materno" name="madre_apellido_materno">
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="madre_dni" class="form-label">DNI</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="madre_dni" name="madre_dni" 
                               maxlength="8" pattern="[0-9]{8}">
                        <button class="btn btn-outline-primary" type="button" onclick="consultarDNI('madre')" title="Consultar RENIEC">
                            <i class="mdi mdi-magnify"></i> RENIEC
                        </button>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="madre_telefono" class="form-label">Teléfono</label>
                    <input type="tel" class="form-control" id="madre_telefono" name="madre_telefono" 
                           pattern="[0-9]{9}" maxlength="9">
                </div>
            </div>
        </div>
    </div>
    
    <!-- Paso 4: Datos Académicos -->
    <div class="wizard-content" data-step="4" style="display: none;">
        <h5 class="mb-4">
            <i class="mdi mdi-school me-2 text-warning"></i>
            Datos Académicos y de Postulación
        </h5>
        
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="carrera_id" class="form-label">Carrera a Postular <span class="text-danger">*</span></label>
                    <select class="form-select" id="carrera_id" name="carrera_id" required>
                        <option value="">Seleccione una carrera</option>
                        @foreach($carreras as $carrera)
                            <option value="{{ $carrera['id'] }}">
                                {{ $carrera['nombre'] }} 
                                @if($carrera['vacantes_disponibles'] != 'Sin límite')
                                    ({{ $carrera['vacantes_disponibles'] }} vacantes)
                                @endif
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="turno_id" class="form-label">Turno <span class="text-danger">*</span></label>
                    <select class="form-select" id="turno_id" name="turno_id" required>
                        <option value="">Seleccione un turno</option>
                        @foreach($turnos as $turno)
                            <option value="{{ $turno->id }}">
                                {{ $turno->nombre }} ({{ $turno->hora_inicio }} - {{ $turno->hora_fin }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-8">
                <div class="mb-3">
                    <label for="colegio_procedencia" class="form-label">Colegio de Procedencia <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="colegio_procedencia" name="colegio_procedencia" required>
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="año_egreso" class="form-label">Año de Egreso <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" id="año_egreso" name="año_egreso" 
                           min="1990" max="{{ date('Y') }}" required>
                </div>
            </div>
        </div>
        
        <div class="alert alert-warning">
            <i class="mdi mdi-alert"></i>
            Ciclo Activo: <strong>{{ $cicloActivo->nombre }}</strong>
        </div>
    </div>
    
    <!-- Paso 5: Documentos -->
    <div class="wizard-content" data-step="5" style="display: none;">
        <h5 class="mb-4">
            <i class="mdi mdi-file-document-multiple me-2 text-success"></i>
            Carga de Documentos
        </h5>
        
        <div class="alert alert-info">
            <i class="mdi mdi-information-outline"></i>
            Todos los documentos son obligatorios. Los archivos PDF no deben superar los 5MB y las imágenes 2MB.
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="foto" class="form-label">Foto del Estudiante <span class="text-danger">*</span></label>
                    <input type="file" class="form-control" id="foto" name="foto" 
                           accept="image/jpeg,image/png,image/jpg" required>
                    <small class="text-muted">Formatos: JPG, PNG. Máximo: 2MB</small>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="dni_pdf" class="form-label">DNI Escaneado <span class="text-danger">*</span></label>
                    <input type="file" class="form-control" id="dni_pdf" name="dni_pdf" 
                           accept="application/pdf" required>
                    <small class="text-muted">Formato: PDF. Máximo: 5MB</small>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="certificado_estudios" class="form-label">Certificado de Estudios <span class="text-danger">*</span></label>
                    <input type="file" class="form-control" id="certificado_estudios" name="certificado_estudios" 
                           accept="application/pdf" required>
                    <small class="text-muted">Formato: PDF. Máximo: 5MB</small>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="voucher_pago" class="form-label">Voucher de Pago <span class="text-danger">*</span></label>
                    <input type="file" class="form-control" id="voucher_pago" name="voucher_pago" 
                           accept="application/pdf" required>
                    <small class="text-muted">Formato: PDF. Máximo: 5MB</small>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Paso 6: Confirmación -->
    <div class="wizard-content" data-step="6" style="display: none;">
        <h5 class="mb-4">
            <i class="mdi mdi-check-circle me-2 text-success"></i>
            Confirmación de Datos
        </h5>
        
        <div class="alert alert-warning">
            <i class="mdi mdi-alert"></i>
            Por favor, revise cuidadosamente la información antes de enviar la postulación.
        </div>
        
        <div id="resumenDatos">
            <!-- El resumen se generará dinámicamente -->
        </div>
        
        <div class="form-check mt-4">
            <input class="form-check-input" type="checkbox" id="confirmarDatos" required>
            <label class="form-check-label" for="confirmarDatos">
                Declaro que toda la información proporcionada es verídica y acepto los términos y condiciones.
            </label>
        </div>
    </div>
    
    <!-- Botones de Navegación -->
    <div class="wizard-navigation mt-4">
        <button type="button" class="btn btn-secondary" id="btnAnterior" style="display: none;">
            <i class="mdi mdi-arrow-left me-1"></i> Anterior
        </button>
        <button type="button" class="btn btn-primary float-end" id="btnSiguiente">
            Siguiente <i class="mdi mdi-arrow-right ms-1"></i>
        </button>
        <button type="submit" class="btn btn-success float-end" id="btnEnviar" style="display: none;">
            <i class="mdi mdi-send me-1"></i> Enviar Postulación
        </button>
    </div>
</form>

<script>
// Script de manejo del wizard será cargado desde wizard-completo.js
</script>