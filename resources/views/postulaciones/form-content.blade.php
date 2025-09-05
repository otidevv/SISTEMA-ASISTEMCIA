<!-- Meta CSRF Token -->
<meta name="csrf-token" content="{{ csrf_token() }}">

<!-- Wizard de Pasos -->
<div class="step-wizard mb-4">
    <div class="d-flex justify-content-between">
        <div class="step-wizard-item active" data-step="1">
            <div class="step-number">1</div>
            <div class="step-text">Datos del Estudiante</div>
        </div>
        <div class="step-wizard-item" data-step="2">
            <div class="step-number">2</div>
            <div class="step-text">Datos del Padre</div>
        </div>
        <div class="step-wizard-item" data-step="3">
            <div class="step-number">3</div>
            <div class="step-text">Datos de la Madre</div>
        </div>
        <div class="step-wizard-item" data-step="4">
            <div class="step-number">4</div>
            <div class="step-text">Datos Académicos</div>
        </div>
        <div class="step-wizard-item" data-step="5">
            <div class="step-number">5</div>
            <div class="step-text">Documentos</div>
        </div>
    </div>
</div>

<!-- Formulario -->
<form id="postulacionUnificadaForm" enctype="multipart/form-data">
    @csrf
    
    <!-- Paso 1: Datos del Estudiante -->
    <div id="step-1" class="step-content active">
        <h5 class="mb-4">
            <i class="mdi mdi-account me-2 text-primary"></i>
            Datos Personales del Estudiante
        </h5>
        
        <div class="row">
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="estudiante_nombre" class="form-label">Nombres <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="estudiante_nombre" name="estudiante_nombre" 
                           value="{{ old('estudiante_nombre') }}" required>
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="estudiante_apellido_paterno" class="form-label">Apellido Paterno <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="estudiante_apellido_paterno" name="estudiante_apellido_paterno" 
                           value="{{ old('estudiante_apellido_paterno') }}" required>
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="estudiante_apellido_materno" class="form-label">Apellido Materno <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="estudiante_apellido_materno" name="estudiante_apellido_materno" 
                           value="{{ old('estudiante_apellido_materno') }}" required>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3">
                <div class="mb-3">
                    <label for="estudiante_dni" class="form-label">DNI <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="estudiante_dni" name="estudiante_dni" 
                               value="{{ old('estudiante_dni') }}" maxlength="8" required>
                        <button class="btn btn-outline-primary" type="button" onclick="consultarDNI('estudiante')" title="Consultar RENIEC">
                            <i class="mdi mdi-magnify"></i> RENIEC
                        </button>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="mb-3">
                    <label for="estudiante_fecha_nacimiento" class="form-label">Fecha de Nacimiento <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="estudiante_fecha_nacimiento" name="estudiante_fecha_nacimiento" 
                           value="{{ old('estudiante_fecha_nacimiento') }}" required>
                </div>
            </div>
            <div class="col-md-3">
                <div class="mb-3">
                    <label for="estudiante_genero" class="form-label">Género <span class="text-danger">*</span></label>
                    <select class="form-select" id="estudiante_genero" name="estudiante_genero" required>
                        <option value="">Seleccione</option>
                        <option value="M" {{ old('estudiante_genero') == 'M' ? 'selected' : '' }}>Masculino</option>
                        <option value="F" {{ old('estudiante_genero') == 'F' ? 'selected' : '' }}>Femenino</option>
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="mb-3">
                    <label for="estudiante_telefono" class="form-label">Teléfono <span class="text-danger">*</span></label>
                    <input type="tel" class="form-control" id="estudiante_telefono" name="estudiante_telefono" 
                           value="{{ old('estudiante_telefono') }}" required>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="mb-3">
                    <label for="estudiante_direccion" class="form-label">Dirección <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="estudiante_direccion" name="estudiante_direccion" 
                           value="{{ old('estudiante_direccion') }}" required>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3">
                <div class="mb-3">
                    <label for="estudiante_tipo_documento" class="form-label">Tipo Documento <span class="text-danger">*</span></label>
                    <select class="form-select" id="estudiante_tipo_documento" name="estudiante_tipo_documento" required>
                        <option value="">Seleccione...</option>
                        <option value="DNI" {{ old('estudiante_tipo_documento') == 'DNI' ? 'selected' : '' }}>DNI</option>
                        <option value="CE" {{ old('estudiante_tipo_documento') == 'CE' ? 'selected' : '' }}>Carnet de Extranjería</option>
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="mb-3">
                    <label for="estudiante_email" class="form-label">Email <span class="text-danger">*</span></label>
                    <input type="email" class="form-control" id="estudiante_email" name="estudiante_email" 
                           value="{{ old('estudiante_email') }}" required>
                </div>
            </div>
            <div class="col-md-3">
                <div class="mb-3">
                    <label for="estudiante_password" class="form-label">Contraseña <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="estudiante_password" name="estudiante_password" 
                               minlength="8" required>
                        <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('estudiante_password')">
                            <i class="mdi mdi-eye"></i>
                        </button>
                    </div>
                    <small class="text-muted">Mínimo 8 caracteres</small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="mb-3">
                    <label for="estudiante_password_confirmation" class="form-label">Confirmar Contraseña <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="estudiante_password_confirmation" name="estudiante_password_confirmation" 
                               minlength="8" required>
                        <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('estudiante_password_confirmation')">
                            <i class="mdi mdi-eye"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Paso 2: Datos del Padre -->
    <div id="step-2" class="step-content" style="display: none;">
        <h5 class="mb-4">
            <i class="mdi mdi-account-tie me-2 text-primary"></i>
            Datos del Padre
        </h5>
        
        <div class="row">
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="padre_nombre" class="form-label">Nombres <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="padre_nombre" name="padre_nombre" required>
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="padre_apellido_paterno" class="form-label">Apellido Paterno <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="padre_apellido_paterno" name="padre_apellido_paterno" required>
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
            <div class="col-md-3">
                <div class="mb-3">
                    <label for="padre_dni" class="form-label">DNI <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="padre_dni" name="padre_dni" maxlength="8" required>
                        <button class="btn btn-outline-primary" type="button" onclick="consultarDNI('padre')" title="Consultar RENIEC">
                            <i class="mdi mdi-magnify"></i> RENIEC
                        </button>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="mb-3">
                    <label for="padre_telefono" class="form-label">Teléfono <span class="text-danger">*</span></label>
                    <input type="tel" class="form-control" id="padre_telefono" name="padre_telefono" required>
                </div>
            </div>
            <div class="col-md-3">
                <div class="mb-3">
                    <label for="padre_email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="padre_email" name="padre_email">
                </div>
            </div>
            <div class="col-md-3">
                <div class="mb-3">
                    <label for="padre_ocupacion" class="form-label">Ocupación</label>
                    <input type="text" class="form-control" id="padre_ocupacion" name="padre_ocupacion">
                </div>
            </div>
        </div>
    </div>

    <!-- Paso 3: Datos de la Madre -->
    <div id="step-3" class="step-content" style="display: none;">
        <h5 class="mb-4">
            <i class="mdi mdi-account-heart me-2 text-primary"></i>
            Datos de la Madre
        </h5>
        
        <div class="row">
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="madre_nombre" class="form-label">Nombres <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="madre_nombre" name="madre_nombre" required>
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="madre_apellido_paterno" class="form-label">Apellido Paterno <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="madre_apellido_paterno" name="madre_apellido_paterno" required>
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
            <div class="col-md-3">
                <div class="mb-3">
                    <label for="madre_dni" class="form-label">DNI <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="madre_dni" name="madre_dni" maxlength="8" required>
                        <button class="btn btn-outline-primary" type="button" onclick="consultarDNI('madre')" title="Consultar RENIEC">
                            <i class="mdi mdi-magnify"></i> RENIEC
                        </button>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="mb-3">
                    <label for="madre_telefono" class="form-label">Teléfono <span class="text-danger">*</span></label>
                    <input type="tel" class="form-control" id="madre_telefono" name="madre_telefono" required>
                </div>
            </div>
            <div class="col-md-3">
                <div class="mb-3">
                    <label for="madre_email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="madre_email" name="madre_email">
                </div>
            </div>
            <div class="col-md-3">
                <div class="mb-3">
                    <label for="madre_ocupacion" class="form-label">Ocupación</label>
                    <input type="text" class="form-control" id="madre_ocupacion" name="madre_ocupacion">
                </div>
            </div>
        </div>
    </div>

    <!-- Paso 4: Datos Académicos -->
    <div id="step-4" class="step-content" style="display: none;">
        <h5 class="mb-4">
            <i class="mdi mdi-school me-2 text-primary"></i>
            Datos Académicos y de Pago
        </h5>
        
        <div class="row">
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="tipo_inscripcion" class="form-label">Tipo de Inscripción <span class="text-danger">*</span></label>
                    <select class="form-select" id="tipo_inscripcion" name="tipo_inscripcion" required>
                        <option value="">Seleccione</option>
                        <option value="postulante">Postulante</option>
                        <option value="reforzamiento">Reforzamiento</option>
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="carrera_id" class="form-label">Carrera <span class="text-danger">*</span></label>
                    <select class="form-select" id="carrera_id" name="carrera_id" required>
                        <option value="">Seleccione una carrera</option>
                        @foreach($carreras as $carrera)
                            <option value="{{ $carrera['id'] }}">{{ $carrera['nombre'] }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="turno_id" class="form-label">Turno <span class="text-danger">*</span></label>
                    <select class="form-select" id="turno_id" name="turno_id" required>
                        <option value="">Seleccione un turno</option>
                        @foreach($turnos as $turno)
                            <option value="{{ $turno->id }}">{{ $turno->nombre }} ({{ $turno->hora_inicio }} - {{ $turno->hora_fin }})</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="departamento" class="form-label">Departamento <span class="text-danger">*</span></label>
                    <select class="form-select" id="departamento" name="departamento" required>
                        <option value="">Cargando...</option>
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="provincia" class="form-label">Provincia <span class="text-danger">*</span></label>
                    <select class="form-select" id="provincia" name="provincia" required disabled>
                        <option value="">Seleccione departamento primero</option>
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="distrito" class="form-label">Distrito <span class="text-danger">*</span></label>
                    <select class="form-select" id="distrito" name="distrito" required disabled>
                        <option value="">Seleccione provincia primero</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="mb-3">
                    <label for="centro_educativo_id" class="form-label">Centro Educativo <span class="text-danger">*</span></label>
                    <select class="form-select" id="centro_educativo_id" name="centro_educativo_id" required>
                        <option value="">Seleccione un distrito para buscar</option>
                    </select>
                    <small class="text-muted">Escriba al menos 2 caracteres para buscar</small>
                </div>
            </div>
        </div>

        <h6 class="mt-4 mb-3">Información de Pago</h6>
        <div class="row">
            <div class="col-md-3">
                <div class="mb-3">
                    <label for="numero_recibo" class="form-label">N° Recibo <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="numero_recibo" name="numero_recibo" required>
                </div>
            </div>
            <div class="col-md-3">
                <div class="mb-3">
                    <label for="fecha_emision_voucher" class="form-label">Fecha de Emisión <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="fecha_emision_voucher" name="fecha_emision_voucher" required>
                </div>
            </div>
            <div class="col-md-2">
                <div class="mb-3">
                    <label for="monto_matricula" class="form-label">Matrícula (S/.) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" class="form-control" id="monto_matricula" name="monto_matricula" required>
                </div>
            </div>
            <div class="col-md-2">
                <div class="mb-3">
                    <label for="monto_ensenanza" class="form-label">Enseñanza (S/.) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" class="form-control" id="monto_ensenanza" name="monto_ensenanza" required>
                </div>
            </div>
            <div class="col-md-2">
                <div class="mb-3">
                    <label for="monto_total" class="form-label">Total (S/.)</label>
                    <input type="text" class="form-control" id="monto_total" readonly>
                </div>
            </div>
        </div>
    </div>

    <!-- Paso 5: Documentos -->
    <div id="step-5" class="step-content" style="display: none;">
        <h5 class="mb-4">
            <i class="mdi mdi-file-document me-2 text-primary"></i>
            Documentos Requeridos
        </h5>
        
        <div class="row">
            <div class="col-md-6">
                <div class="mb-4">
                    <label class="form-label">DNI del Postulante <span class="text-danger">*</span></label>
                    <div class="document-upload border-2 border-dashed rounded p-3 text-center" data-target="dni_documento" style="cursor: pointer; border-color: #dee2e6 !important;">
                        <i class="mdi mdi-cloud-upload mdi-48px text-muted"></i>
                        <p class="mb-2">Haga clic o arrastre el archivo aquí</p>
                        <small class="text-muted">PDF, JPG, PNG (máx. 5MB)</small>
                        <input type="file" name="dni_documento" accept=".pdf,.jpg,.jpeg,.png" style="display: none;">
                    </div>
                    <div id="preview-dni_documento" class="document-preview mt-2" style="display: none;"></div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="mb-4">
                    <label class="form-label">Certificado de Estudios <span class="text-danger">*</span></label>
                    <div class="document-upload border-2 border-dashed rounded p-3 text-center" data-target="certificado_estudios" style="cursor: pointer; border-color: #dee2e6 !important;">
                        <i class="mdi mdi-cloud-upload mdi-48px text-muted"></i>
                        <p class="mb-2">Haga clic o arrastre el archivo aquí</p>
                        <small class="text-muted">PDF, JPG, PNG (máx. 5MB)</small>
                        <input type="file" name="certificado_estudios" accept=".pdf,.jpg,.jpeg,.png" style="display: none;">
                    </div>
                    <div id="preview-certificado_estudios" class="document-preview mt-2" style="display: none;"></div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="mb-4">
                    <label class="form-label">Fotografía <span class="text-danger">*</span></label>
                    <div class="document-upload border-2 border-dashed rounded p-3 text-center" data-target="foto_carnet" style="cursor: pointer; border-color: #dee2e6 !important;">
                        <i class="mdi mdi-cloud-upload mdi-48px text-muted"></i>
                        <p class="mb-2">Haga clic o arrastre el archivo aquí</p>
                        <small class="text-muted">JPG, PNG (máx. 2MB)</small>
                        <input type="file" name="foto_carnet" accept=".jpg,.jpeg,.png" style="display: none;">
                    </div>
                    <div id="preview-foto_carnet" class="document-preview mt-2" style="display: none;"></div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="mb-4">
                    <label class="form-label">Voucher de Pago <span class="text-danger">*</span></label>
                    <div class="document-upload border-2 border-dashed rounded p-3 text-center" data-target="voucher_pago" style="cursor: pointer; border-color: #dee2e6 !important;">
                        <i class="mdi mdi-cloud-upload mdi-48px text-muted"></i>
                        <p class="mb-2">Haga clic o arrastre el archivo aquí</p>
                        <small class="text-muted">PDF, JPG, PNG (máx. 5MB)</small>
                        <input type="file" name="voucher_pago" accept=".pdf,.jpg,.jpeg,.png" style="display: none;">
                    </div>
                    <div id="preview-voucher_pago" class="document-preview mt-2" style="display: none;"></div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="mb-4">
                    <label class="form-label">Carta de Compromiso <span class="text-danger">*</span></label>
                    <div class="document-upload border-2 border-dashed rounded p-3 text-center" data-target="carta_compromiso" style="cursor: pointer; border-color: #dee2e6 !important;">
                        <i class="mdi mdi-cloud-upload mdi-48px text-muted"></i>
                        <p class="mb-2">Haga clic o arrastre el archivo aquí</p>
                        <small class="text-muted">PDF, JPG, PNG (máx. 5MB)</small>
                        <input type="file" name="carta_compromiso" accept=".pdf,.jpg,.jpeg,.png" style="display: none;">
                    </div>
                    <div id="preview-carta_compromiso" class="document-preview mt-2" style="display: none;"></div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="mb-4">
                    <label class="form-label">Constancia de Estudios <span class="text-danger">*</span></label>
                    <div class="document-upload border-2 border-dashed rounded p-3 text-center" data-target="constancia_estudios" style="cursor: pointer; border-color: #dee2e6 !important;">
                        <i class="mdi mdi-cloud-upload mdi-48px text-muted"></i>
                        <p class="mb-2">Haga clic o arrastre el archivo aquí</p>
                        <small class="text-muted">PDF, JPG, PNG (máx. 5MB)</small>
                        <input type="file" name="constancia_estudios" accept=".pdf,.jpg,.jpeg,.png" style="display: none;">
                    </div>
                    <div id="preview-constancia_estudios" class="document-preview mt-2" style="display: none;"></div>
                </div>
            </div>
        </div>
    </div>
</form>

<!-- Botones de Navegación -->
<div class="d-flex justify-content-between mt-4 pt-3 border-top">
    <button type="button" id="prevBtn" class="btn btn-secondary" style="display: none;">
        <i class="mdi mdi-arrow-left me-1"></i> Anterior
    </button>
    <div class="ms-auto">
        <button type="button" id="nextBtn" class="btn btn-success">
            Siguiente <i class="mdi mdi-arrow-right ms-1"></i>
        </button>
        <button type="submit" id="submitBtn" class="btn btn-primary" style="display: none;" form="postulacionUnificadaForm">
            <i class="mdi mdi-send me-1"></i> Enviar Postulación
        </button>
    </div>
</div>

<!-- Estilos CSS para el wizard -->
<style>
.step-wizard {
    margin-bottom: 2rem;
}

.step-wizard .d-flex {
    position: relative;
}

.step-wizard .d-flex::before {
    content: '';
    position: absolute;
    top: 20px;
    left: 0;
    right: 0;
    height: 2px;
    background: #dee2e6;
    z-index: 1;
}

.step-wizard-item {
    flex: 1;
    text-align: center;
    position: relative;
    z-index: 2;
}

.step-number {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #f8f9fa;
    border: 2px solid #dee2e6;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 8px;
    font-weight: bold;
    color: #6c757d;
    transition: all 0.3s ease;
}

.step-wizard-item.active .step-number {
    background: #28a745;
    border-color: #28a745;
    color: white;
}

.step-wizard-item.completed .step-number {
    background: #20c997;
    border-color: #20c997;
    color: white;
}

.step-text {
    font-size: 12px;
    font-weight: 500;
    color: #6c757d;
}

.step-wizard-item.active .step-text {
    color: #28a745;
    font-weight: 600;
}

.step-content {
    min-height: 400px;
}

.document-upload:hover {
    border-color: #28a745 !important;
    background-color: #f0fff4;
}

.document-upload.completed {
    border-color: #28a745 !important;
    background-color: #d4edda;
}

.document-preview {
    padding: 10px;
    background: #e8f5e8;
    border-radius: 6px;
}

.document-preview.show {
    display: block !important;
}

/* Efecto visual para campos autocompletados */
.auto-filled {
    background-color: #d4edda !important;
    border-color: #28a745 !important;
    transition: all 0.3s ease;
}

.auto-filled:focus {
    box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
}
</style>

<!-- JavaScript para el wizard -->
<script>
console.log('Iniciando script del wizard...');

document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM cargado, inicializando wizard...');
    
    let currentStep = 1;
    const totalSteps = 5;
    
    // Configurar CSRF para AJAX
    window.default_server = "{{ url('/') }}";
    
    // Verificar que los elementos existen
    const nextBtn = document.getElementById('nextBtn');
    const prevBtn = document.getElementById('prevBtn');
    const form = document.getElementById('postulacionUnificadaForm');
    
    console.log('Elementos encontrados:', {
        nextBtn: !!nextBtn,
        prevBtn: !!prevBtn,
        form: !!form
    });
    
    if (!nextBtn || !prevBtn || !form) {
        console.error('No se encontraron elementos necesarios del formulario');
        return;
    }
    
    // Configurar eventos de navegación
    nextBtn.addEventListener('click', function() {
        console.log('Click en siguiente, paso actual:', currentStep);
        if (validateCurrentStep()) {
            if (currentStep < totalSteps) {
                goToStep(currentStep + 1);
            }
        }
    });
    
    prevBtn.addEventListener('click', function() {
        console.log('Click en anterior, paso actual:', currentStep);
        if (currentStep > 1) {
            goToStep(currentStep - 1);
        }
    });
    
    // Configurar envío del formulario
    form.addEventListener('submit', function(e) {
        console.log('Enviando formulario...');
        e.preventDefault();
        if (validateCurrentStep()) {
            submitForm();
        }
    });
    
    // Configurar subida de documentos
    setupDocumentHandlers();
    
    // Configurar cálculo de montos
    const montoMatricula = document.getElementById('monto_matricula');
    const montoEnsenanza = document.getElementById('monto_ensenanza');
    
    if (montoMatricula) montoMatricula.addEventListener('input', calculateTotal);
    if (montoEnsenanza) montoEnsenanza.addEventListener('input', calculateTotal);
    
    // Configurar Select2 para centro educativo
    if (typeof $ !== 'undefined' && $.fn.select2) {
        $('#centro_educativo_id').select2({
            theme: 'bootstrap-5',
            placeholder: 'Buscar colegio...',
            allowClear: true,
            minimumInputLength: 2,
            ajax: {
                url: window.default_server + '/api/postulacion-unificada/buscar-colegios',
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        termino: params.term
                    };
                },
                processResults: function (data) {
                    if (data.success) {
                        return {
                            results: data.colegios.map(function(colegio) {
                                return {
                                    id: colegio.id,
                                    text: colegio.nombre + ' - ' + colegio.nivel
                                };
                            })
                        };
                    }
                    return { results: [] };
                }
            }
        });
    }
    
    console.log('Wizard inicializado correctamente');
    
    function goToStep(step) {
        // Ocultar paso actual
        document.getElementById('step-' + currentStep).style.display = 'none';
        document.querySelector('.step-wizard-item[data-step="' + currentStep + '"]').classList.remove('active');
        
        // Marcar como completado si avanzamos
        if (step > currentStep) {
            document.querySelector('.step-wizard-item[data-step="' + currentStep + '"]').classList.add('completed');
        }
        
        // Mostrar nuevo paso
        currentStep = step;
        document.getElementById('step-' + currentStep).style.display = 'block';
        document.querySelector('.step-wizard-item[data-step="' + currentStep + '"]').classList.add('active');
        
        // Actualizar botones
        updateNavigationButtons();
    }
    
    function updateNavigationButtons() {
        const prevBtn = document.getElementById('prevBtn');
        const nextBtn = document.getElementById('nextBtn');
        const submitBtn = document.getElementById('submitBtn');
        
        prevBtn.style.display = currentStep === 1 ? 'none' : 'block';
        
        if (currentStep === totalSteps) {
            nextBtn.style.display = 'none';
            submitBtn.style.display = 'block';
        } else {
            nextBtn.style.display = 'block';
            submitBtn.style.display = 'none';
        }
    }
    
    function validateCurrentStep() {
        const currentStepElement = document.getElementById('step-' + currentStep);
        let isValid = true;
        let errors = [];
        
        // Validar según el paso actual
        switch(currentStep) {
            case 1:
                isValid = validateStep1(errors);
                break;
            case 2:
                isValid = validateStep2(errors);
                break;
            case 3:
                isValid = validateStep3(errors);
                break;
            case 4:
                isValid = validateStep4(errors);
                break;
            case 5:
                isValid = validateStep5(errors);
                break;
        }
        
        if (!isValid) {
            if (typeof toastr !== 'undefined') {
                errors.forEach(error => toastr.error(error));
            } else {
                alert(errors.join('\n'));
            }
        }
        
        return isValid;
    }
    
    function validateStep1(errors) {
        let isValid = true;
        
        // Validar campos requeridos del estudiante
        const requiredFields = ['estudiante_nombre', 'estudiante_apellido_paterno', 'estudiante_apellido_materno', 
                               'estudiante_dni', 'estudiante_fecha_nacimiento', 'estudiante_genero', 
                               'estudiante_telefono', 'estudiante_email', 'estudiante_direccion'];
        
        requiredFields.forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (!field.value.trim()) {
                field.classList.add('is-invalid');
                errors.push(`El campo ${field.previousElementSibling.textContent.replace('*', '').trim()} es requerido`);
                isValid = false;
            } else {
                field.classList.remove('is-invalid');
            }
        });
        
        // Validar DNI
        const dni = document.getElementById('estudiante_dni').value;
        if (dni && !/^\d{8}$/.test(dni)) {
            document.getElementById('estudiante_dni').classList.add('is-invalid');
            errors.push('El DNI debe tener exactamente 8 dígitos');
            isValid = false;
        }
        
        // Validar email
        const email = document.getElementById('estudiante_email').value;
        if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            document.getElementById('estudiante_email').classList.add('is-invalid');
            errors.push('El email no tiene un formato válido');
            isValid = false;
        } else {
            document.getElementById('estudiante_email').classList.remove('is-invalid');
        }

        // Validar contraseñas
        const password = document.getElementById('estudiante_password').value;
        const passwordConfirmation = document.getElementById('estudiante_password_confirmation').value;

        if (password !== passwordConfirmation) {
            document.getElementById('estudiante_password').classList.add('is-invalid');
            document.getElementById('estudiante_password_confirmation').classList.add('is-invalid');
            errors.push('Las contraseñas no coinciden');
            isValid = false;
        } else {
            document.getElementById('estudiante_password').classList.remove('is-invalid');
            document.getElementById('estudiante_password_confirmation').classList.remove('is-invalid');
        }
        
        return isValid;
    }
    
    function validateStep2(errors) {
        return validateParentData('padre', errors);
    }
    
    function validateStep3(errors) {
        return validateParentData('madre', errors);
    }
    
    function validateParentData(tipo, errors) {
        let isValid = true;
        
        const requiredFields = [`${tipo}_nombre`, `${tipo}_apellido_paterno`, `${tipo}_dni`, `${tipo}_telefono`];
        
        requiredFields.forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (!field.value.trim()) {
                field.classList.add('is-invalid');
                errors.push(`El campo ${field.previousElementSibling.textContent.replace('*', '').trim()} del ${tipo} es requerido`);
                isValid = false;
            } else {
                field.classList.remove('is-invalid');
            }
        });
        
        // Validar DNI
        const dni = document.getElementById(`${tipo}_dni`).value;
        if (dni && !/^\d{8}$/.test(dni)) {
            document.getElementById(`${tipo}_dni`).classList.add('is-invalid');
            errors.push(`El DNI del ${tipo} debe tener exactamente 8 dígitos`);
            isValid = false;
        }
        
        return isValid;
    }
    
    function validateStep4(errors) {
        let isValid = true;
        
        const requiredFields = ['tipo_inscripcion', 'carrera_id', 'turno_id', 'centro_educativo_id', 
                               'numero_recibo', 'fecha_emision_voucher', 'monto_matricula', 'monto_ensenanza'];
        
        requiredFields.forEach(fieldId => {
            const field = document.getElementById(fieldId);
            if (!field.value.trim()) {
                field.classList.add('is-invalid');
                errors.push(`El campo ${field.previousElementSibling.textContent.replace('*', '').trim()} es requerido`);
                isValid = false;
            } else {
                field.classList.remove('is-invalid');
            }
        });
        
        return isValid;
    }
    
    function validateStep5(errors) {
        let isValid = true;
        const requiredDocs = ['dni_documento', 'certificado_estudios', 'foto_carnet', 'voucher_pago', 'carta_compromiso', 'constancia_estudios'];
        
        requiredDocs.forEach(function(docName) {
            const input = document.querySelector(`input[name="${docName}"]`);
            if (!input.files || input.files.length === 0) {
                const uploadArea = input.closest('.document-upload');
                uploadArea.classList.add('border-danger');
                errors.push(`El documento ${getDocumentLabel(docName)} es requerido`);
                isValid = false;
            } else {
                const uploadArea = input.closest('.document-upload');
                uploadArea.classList.remove('border-danger');
            }
        });
        
        return isValid;
    }
    
    function getDocumentLabel(docName) {
        const labels = {
            'dni_documento': 'DNI del Postulante',
            'certificado_estudios': 'Certificado de Estudios',
            'foto_carnet': 'Fotografía',
            'voucher_pago': 'Voucher de Pago',
            'carta_compromiso': 'Carta de Compromiso',
            'constancia_estudios': 'Constancia de Estudios'
        };
        return labels[docName] || docName;
    }
    
    function setupDocumentHandlers() {
        document.querySelectorAll('.document-upload').forEach(upload => {
            upload.addEventListener('click', function() {
                this.querySelector('input[type="file"]').click();
            });
            
            const input = upload.querySelector('input[type="file"]');
            input.addEventListener('change', function() {
                handleFileSelect(this);
            });
        });
    }
    
    function handleFileSelect(input) {
        const file = input.files[0];
        const target = input.closest('.document-upload').dataset.target;
        const previewId = 'preview-' + target;
        
        if (file) {
            // Validar tamaño
            const maxSize = target === 'foto_carnet' ? 2 * 1024 * 1024 : 5 * 1024 * 1024;
            if (file.size > maxSize) {
                alert('El archivo es demasiado grande. Tamaño máximo: ' + (maxSize / 1024 / 1024) + 'MB');
                input.value = '';
                return;
            }
            
            // Validar tipo
            const allowedTypes = target === 'foto_carnet' ? 
                ['image/jpeg', 'image/jpg', 'image/png'] : 
                ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'];
            
            if (!allowedTypes.includes(file.type)) {
                alert('Tipo de archivo no permitido');
                input.value = '';
                return;
            }
            
            // Mostrar preview
            showFilePreview(file, previewId);
            
            // Marcar como completado
            input.closest('.document-upload').classList.add('completed');
        }
    }
    
    function showFilePreview(file, previewId) {
        const preview = document.getElementById(previewId);
        let html = '<div class="d-flex align-items-center">';
        html += '<i class="mdi mdi-file-check-alt text-success me-2"></i>';
        html += '<span class="me-auto">' + file.name + '</span>';
        html += '<small class="text-muted">(' + formatFileSize(file.size) + ')</small>';
        html += '</div>';
        
        preview.innerHTML = html;
        preview.classList.add('show');
        preview.style.display = 'block';
    }
    
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
    
    function calculateTotal() {
        const matricula = parseFloat(document.getElementById('monto_matricula').value) || 0;
        const ensenanza = parseFloat(document.getElementById('monto_ensenanza').value) || 0;
        const total = matricula + ensenanza;
        
        document.getElementById('monto_total').value = 'S/. ' + total.toFixed(2);
    }
    
    function submitForm() {
        const formData = new FormData(document.getElementById('postulacionUnificadaForm'));
        
        // Mostrar loading
        if (typeof toastr !== 'undefined') {
            toastr.info('Enviando postulación...', 'Procesando');
        }
        
        fetch(window.default_server + '/postulacion-unificada', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (typeof toastr !== 'undefined') {
                    toastr.success(data.message, 'Éxito');
                } else {
                    alert(data.message);
                }
                
                // Cerrar modal y actualizar lista
                setTimeout(() => {
                    // Enviar evento al padre para cerrar modal
                    if (window.parent && window.parent !== window) {
                        window.parent.postMessage({
                            type: 'postulacion-completada',
                            message: 'Postulación creada exitosamente',
                            data: data.postulacion
                        }, '*');
                    } else {
                        // Si no está en modal, cerrar modal Bootstrap
                        const modal = bootstrap.Modal.getInstance(document.getElementById('nuevaPostulacionModal'));
                        if (modal) {
                            modal.hide();
                        }
                        
                        // Actualizar lista
                        if (typeof refreshPostulacionesList === 'function') {
                            refreshPostulacionesList();
                        }
                    }
                }, 1500);
            } else {
                if (typeof toastr !== 'undefined') {
                    toastr.error(data.message || 'Error al enviar la postulación', 'Error');
                } else {
                    alert(data.message || 'Error al enviar la postulación');
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            if (typeof toastr !== 'undefined') {
                toastr.error('Error al enviar la postulación', 'Error');
            } else {
                alert('Error al enviar la postulación');
            }
        });
    }
});

// Función global para consultar DNI
window.consultarDNI = function(tipo) {
    console.log('Función consultarDNI llamada para tipo:', tipo);
    
    const dniField = document.getElementById(tipo + '_dni') || document.getElementById('estudiante_dni');
    if (!dniField) {
        console.error('No se encontró el campo DNI para tipo:', tipo);
        alert('Error: No se encontró el campo DNI');
        return;
    }
    
    const dni = dniField.value.trim();
    console.log('DNI a consultar:', dni);
    
    if (!/^\d{8}$/.test(dni)) {
        const mensaje = 'Ingrese un DNI válido de 8 dígitos';
        console.log('DNI inválido:', dni);
        if (typeof toastr !== 'undefined') {
            toastr.error(mensaje);
        } else {
            alert(mensaje);
        }
        return;
    }
    
    // Deshabilitar el botón mientras consulta
    const button = dniField.nextElementSibling;
    if (!button) {
        console.error('No se encontró el botón RENIEC');
        alert('Error: No se encontró el botón RENIEC');
        return;
    }
    
    const originalText = button.innerHTML;
    button.disabled = true;
    button.innerHTML = '<i class="mdi mdi-loading mdi-spin"></i> Consultando...';
    
    console.log('Iniciando consulta RENIEC...');
    
    // Mostrar mensaje de inicio
    if (typeof toastr !== 'undefined') {
        toastr.info('Consultando RENIEC...', 'Procesando');
    } else {
        console.log('Consultando RENIEC...');
    }
    
    // Verificar que tenemos el servidor configurado
    if (!window.default_server) {
        console.error('window.default_server no está definido');
        alert('Error de configuración del servidor');
        button.disabled = false;
        button.innerHTML = originalText;
        return;
    }
    
    const url = window.default_server + '/api/reniec/consultar';
    console.log('URL de consulta:', url);
    
    // Consultar la API real de RENIEC
    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        },
        body: JSON.stringify({ dni: dni })
    })
    .then(response => {
        console.log('Respuesta recibida:', response.status, response.statusText);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('Datos recibidos de RENIEC:', data);
        
        if (data.success && data.data) {
            // Llenar los campos con los datos de RENIEC
            const datos = data.data;
            console.log('Datos a llenar:', datos);
            
            if (tipo === 'estudiante') {
                if (datos.nombres) {
                    document.getElementById('estudiante_nombre').value = datos.nombres;
                    console.log('Llenado nombres estudiante:', datos.nombres);
                }
                if (datos.apellido_paterno) {
                    document.getElementById('estudiante_apellido_paterno').value = datos.apellido_paterno;
                    console.log('Llenado apellido paterno estudiante:', datos.apellido_paterno);
                }
                if (datos.apellido_materno) {
                    document.getElementById('estudiante_apellido_materno').value = datos.apellido_materno;
                    console.log('Llenado apellido materno estudiante:', datos.apellido_materno);
                }
                
                // Agregar clase de autocompletado para efecto visual
                ['estudiante_nombre', 'estudiante_apellido_paterno', 'estudiante_apellido_materno'].forEach(fieldId => {
                    const field = document.getElementById(fieldId);
                    if (field && field.value) {
                        field.classList.add('auto-filled');
                        setTimeout(() => field.classList.remove('auto-filled'), 2000);
                    }
                });
            } else if (tipo === 'padre') {
                if (datos.nombres) document.getElementById('padre_nombre').value = datos.nombres;
                if (datos.apellido_paterno) document.getElementById('padre_apellido_paterno').value = datos.apellido_paterno;
                if (datos.apellido_materno) document.getElementById('padre_apellido_materno').value = datos.apellido_materno;
                
                // Agregar clase de autocompletado para efecto visual
                ['padre_nombre', 'padre_apellido_paterno', 'padre_apellido_materno'].forEach(fieldId => {
                    const field = document.getElementById(fieldId);
                    if (field && field.value) {
                        field.classList.add('auto-filled');
                        setTimeout(() => field.classList.remove('auto-filled'), 2000);
                    }
                });
            } else if (tipo === 'madre') {
                if (datos.nombres) document.getElementById('madre_nombre').value = datos.nombres;
                if (datos.apellido_paterno) document.getElementById('madre_apellido_paterno').value = datos.apellido_paterno;
                if (datos.apellido_materno) document.getElementById('madre_apellido_materno').value = datos.apellido_materno;
                
                // Agregar clase de autocompletado para efecto visual
                ['madre_nombre', 'madre_apellido_paterno', 'madre_apellido_materno'].forEach(fieldId => {
                    const field = document.getElementById(fieldId);
                    if (field && field.value) {
                        field.classList.add('auto-filled');
                        setTimeout(() => field.classList.remove('auto-filled'), 2000);
                    }
                });
            }
            
            const mensajeExito = 'Datos encontrados y completados automáticamente';
            if (typeof toastr !== 'undefined') {
                toastr.success(mensajeExito, 'RENIEC');
            } else {
                alert(mensajeExito);
            }
        } else {
            const mensajeError = data.message || 'No se encontraron datos para este DNI';
            console.log('No se encontraron datos:', mensajeError);
            if (typeof toastr !== 'undefined') {
                toastr.warning(mensajeError, 'RENIEC');
            } else {
                alert(mensajeError);
            }
        }
    })
    .catch(error => {
        console.error('Error consultando RENIEC:', error);
        const mensajeError = 'Error al consultar RENIEC: ' + error.message;
        if (typeof toastr !== 'undefined') {
            toastr.error(mensajeError, 'Error');
        } else {
            alert(mensajeError);
        }
    })
    .finally(() => {
        // Rehabilitar el botón
        console.log('Rehabilitando botón...');
        button.disabled = false;
        button.innerHTML = originalText;
    });
};

// Función global para mostrar/ocultar contraseña
window.togglePassword = function(fieldId) {
    const field = document.getElementById(fieldId);
    const button = field.nextElementSibling;
    const icon = button.querySelector('i');
    
    if (field.type === 'password') {
        field.type = 'text';
        icon.className = 'mdi mdi-eye-off';
    } else {
        field.type = 'password';
        icon.className = 'mdi mdi-eye';
    }
};
</script>