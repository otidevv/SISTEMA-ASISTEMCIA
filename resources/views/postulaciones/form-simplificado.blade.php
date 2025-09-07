<!-- Meta CSRF Token -->
<meta name="csrf-token" content="{{ csrf_token() }}">

<!-- Wizard Simplificado para Postulantes Existentes -->
<div class="wizard-step-indicator mb-4">
    <div class="wizard-step active" data-step="1">
        <div class="wizard-step-number">1</div>
        <div class="wizard-step-title">Confirmar Datos</div>
    </div>
    <div class="wizard-step" data-step="2">
        <div class="wizard-step-number">2</div>
        <div class="wizard-step-title">Datos Académicos</div>
    </div>
    <div class="wizard-step" data-step="3">
        <div class="wizard-step-number">3</div>
        <div class="wizard-step-title">Documentos</div>
    </div>
    <div class="wizard-step" data-step="4">
        <div class="wizard-step-number">4</div>
        <div class="wizard-step-title">Confirmación</div>
    </div>
</div>

<!-- Barra de Progreso -->
<div class="progress mb-4" style="height: 6px;">
    <div class="progress-bar bg-success" role="progressbar" style="width: 25%;" id="wizardProgress"></div>
</div>

<!-- Formulario Simplificado -->
<form id="formPostulacionSimplificado" enctype="multipart/form-data">
    @csrf
    <input type="hidden" name="estudiante_id" value="{{ $usuario->id }}">
    
    <!-- Paso 1: Confirmación de Datos Existentes -->
    <div class="wizard-content active" data-step="1">
        <h5 class="mb-4">
            <i class="mdi mdi-account-check me-2 text-success"></i>
            Confirmar Datos del Postulante
        </h5>
        
        <div class="alert alert-info">
            <i class="mdi mdi-information-outline"></i>
            Como ya tienes una cuenta registrada, estos son tus datos actuales. Si necesitas actualizarlos, puedes hacerlo desde tu perfil después de completar la postulación.
        </div>
        
        <!-- Datos del Estudiante (Solo lectura) -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h6 class="card-title mb-0"><i class="mdi mdi-account me-2"></i>Datos Personales</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <strong>Nombre Completo:</strong> 
                        {{ $usuario->nombre }} {{ $usuario->apellido_paterno }} {{ $usuario->apellido_materno }}
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>DNI:</strong> {{ $usuario->numero_documento }}
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Email:</strong> {{ $usuario->email }}
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Teléfono:</strong> {{ $usuario->telefono ?: 'No registrado' }}
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Fecha de Nacimiento:</strong> 
                        {{ $usuario->fecha_nacimiento ? \Carbon\Carbon::parse($usuario->fecha_nacimiento)->format('d/m/Y') : 'No registrada' }}
                    </div>
                    @if($usuario->direccion)
                    <div class="col-md-12 mb-3">
                        <strong>Dirección:</strong> {{ $usuario->direccion }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Datos de Padres si existen -->
        @if($padres->count() > 0)
        <div class="card">
            <div class="card-header bg-light">
                <h6 class="card-title mb-0"><i class="mdi mdi-account-group me-2"></i>Datos de Padres/Tutores Registrados</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach($padres as $parentesco)
                    <div class="col-md-6 mb-3">
                        <div class="border p-3 rounded">
                            <h6 class="text-capitalize">{{ $parentesco['tipo'] }}</h6>
                            <p class="mb-1"><strong>Nombre:</strong> 
                                {{ $parentesco['padre']->nombre }} {{ $parentesco['padre']->apellido_paterno }} {{ $parentesco['padre']->apellido_materno }}
                            </p>
                            <p class="mb-1"><strong>DNI:</strong> {{ $parentesco['padre']->numero_documento }}</p>
                            <p class="mb-0"><strong>Teléfono:</strong> {{ $parentesco['padre']->telefono ?: 'No registrado' }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @else
        <div class="alert alert-warning">
            <i class="mdi mdi-alert"></i>
            No tienes padres o tutores registrados en el sistema. Si eres menor de edad, puedes agregarlos desde tu perfil después de completar la postulación.
        </div>
        @endif
    </div>
    
    <!-- Paso 2: Datos Académicos -->
    <div class="wizard-content" data-step="2" style="display: none;">
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
        
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="tipo_inscripcion" class="form-label">Tipo de Inscripción <span class="text-danger">*</span></label>
                    <select class="form-select" id="tipo_inscripcion" name="tipo_inscripcion" required>
                        <option value="">Seleccione el tipo</option>
                        <option value="postulante">Postulante (Primera vez)</option>
                        <option value="reforzamiento">Reforzamiento (Repitente)</option>
                    </select>
                    <small class="text-muted">Seleccione "Reforzamiento" si ya estudió esta carrera anteriormente</small>
                </div>
            </div>
        </div>
        
        <div class="alert alert-warning">
            <i class="mdi mdi-alert"></i>
            Ciclo Activo: <strong>{{ $cicloActivo->nombre }}</strong>
        </div>
    </div>
    
    <!-- Paso 3: Documentos -->
    <div class="wizard-content" data-step="3" style="display: none;">
        <h5 class="mb-4">
            <i class="mdi mdi-file-document-multiple me-2 text-success"></i>
            Carga de Documentos
        </h5>
        
        <div class="alert alert-info">
            <i class="mdi mdi-information-outline"></i>
            Cargue los documentos requeridos para completar su postulación. Los archivos PDF no deben superar los 5MB y las imágenes 2MB.
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="foto" class="form-label">Foto Actualizada <span class="text-danger">*</span></label>
                    <input type="file" class="form-control" id="foto" name="foto" 
                           accept="image/jpeg,image/png,image/jpg" required>
                    <small class="text-muted">Formatos: JPG, PNG. Máximo: 2MB</small>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="certificado_estudios" class="form-label">Certificado de Estudios <span class="text-danger">*</span></label>
                    <input type="file" class="form-control" id="certificado_estudios" name="certificado_estudios" 
                           accept="application/pdf" required>
                    <small class="text-muted">Formato: PDF. Máximo: 5MB</small>
                </div>
            </div>
        </div>
        
        <div class="row">
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="voucher_pago" class="form-label">Voucher de Pago <span class="text-danger">*</span></label>
                    <input type="file" class="form-control" id="voucher_pago" name="voucher_pago" 
                           accept="application/pdf" required>
                    <small class="text-muted">Formato: PDF. Máximo: 5MB</small>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label for="dni_pdf" class="form-label">DNI Actualizado (Opcional)</label>
                    <input type="file" class="form-control" id="dni_pdf" name="dni_pdf" 
                           accept="application/pdf">
                    <small class="text-muted">Solo si ha renovado su DNI recientemente. Formato: PDF. Máximo: 5MB</small>
                </div>
            </div>
        </div>
        
        <div class="alert alert-warning">
            <i class="mdi mdi-alert-circle"></i>
            <strong>Documentos Opcionales:</strong>
            <ul class="mb-0 mt-2">
                <li>Si ya tienes documentos cargados en postulaciones anteriores, no es necesario subirlos nuevamente</li>
                <li>Solo sube documentos si han cambiado o si es tu primera postulación</li>
            </ul>
        </div>
    </div>
    
    <!-- Paso 4: Confirmación -->
    <div class="wizard-content" data-step="4" style="display: none;">
        <h5 class="mb-4">
            <i class="mdi mdi-check-circle me-2 text-success"></i>
            Confirmación de Postulación
        </h5>
        
        <div class="alert alert-info">
            <i class="mdi mdi-information-outline"></i>
            Revise los datos antes de enviar su postulación.
        </div>
        
        <div id="resumenPostulacion">
            <!-- El resumen se generará dinámicamente -->
        </div>
        
        <div class="form-check mt-4">
            <input class="form-check-input" type="checkbox" id="confirmarPostulacion" required>
            <label class="form-check-label" for="confirmarPostulacion">
                Confirmo que los datos son correctos y acepto los términos y condiciones de la postulación.
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
// El script wizard-simplificado.js manejará la funcionalidad
</script>