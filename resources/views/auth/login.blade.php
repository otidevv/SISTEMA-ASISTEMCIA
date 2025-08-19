@extends('layouts.auth')

@section('title', 'Acceso al Sistema - CEPRE UNAMAD')

@section('content')
    <div class="min-vh-100 position-relative d-flex align-items-center justify-content-center p-3">
        <!-- Imagen de fondo original con overlay oscuro -->
        <div class="position-absolute w-100 h-100 top-0 start-0 overflow-hidden">
            <img src="{{ asset('assets/images/login/login.jpg') }}" alt="CEPRE UNAMAD Background"
                 class="w-100 h-100 object-fit-cover position-absolute top-0 start-0">
            <div class="position-absolute w-100 h-100 top-0 start-0 bg-dark-overlay"></div>
        </div>

        <!-- Contenedor Principal con formulario -->
        <div class="container position-relative z-1">
            <div class="row justify-content-center">
                <div class="col-md-9 col-lg-8 col-xl-7">
                    <div class="card shadow-lg border-0 rounded-4 animated-card" style="background: rgba(255, 255, 255, 0.98);">
                        <div class="card-body p-4 p-sm-5">
                            <div class="text-center mb-4">
                                <a href="{{ route('home') }}" class="d-inline-block mb-4">
                                    <img src="{{ asset('assets/images/logocepre1.svg') }}" alt="CEPRE UNAMAD"
                                         height="90" class="img-fluid" />
                                </a>
                                
                                <!-- Tabs para Login y Registro -->
                                <ul class="nav nav-pills nav-justified mb-4" id="authTabs" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link active" id="login-tab" data-bs-toggle="pill" 
                                                data-bs-target="#login" type="button" role="tab">
                                            <svg class="me-2" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M15 3h6v6"></path>
                                                <path d="M10 14 21 3"></path>
                                            </svg>
                                            Iniciar Sesión
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link" id="register-tab" data-bs-toggle="pill" 
                                                data-bs-target="#register" type="button" role="tab">
                                            <svg class="me-2" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                                <circle cx="8.5" cy="7" r="4"></circle>
                                                <line x1="20" y1="8" x2="20" y2="14"></line>
                                                <line x1="23" y1="11" x2="17" y2="11"></line>
                                            </svg>
                                            Registrarse
                                        </button>
                                    </li>
                                </ul>
                            </div>

                            <!-- Alertas de Error de Laravel -->
                            @if ($errors->any())
                                <div class="alert alert-danger border-0 rounded-3 mb-4 animate__animated animate__fadeInDown" role="alert">
                                    <div class="d-flex align-items-center">
                                        <svg class="me-2 flex-shrink-0" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <circle cx="12" cy="12" r="10"></circle>
                                            <line x1="12" y1="8" x2="12" y2="12"></line>
                                            <line x1="12" y1="16" x2="12.01" y2="16"></line>
                                        </svg>
                                        <div>
                                            <strong>Error de validación</strong>
                                            <ul class="mb-0 mt-1 small">
                                                @foreach ($errors->all() as $error)
                                                    <li>{{ $error }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <!-- Tab Content -->
                            <div class="tab-content" id="authTabsContent">
                                <!-- Tab Login -->
                                <div class="tab-pane fade show active" id="login" role="tabpanel">
                                    <h2 class="fw-bold text-dark mb-2 fs-2 text-center">Bienvenido</h2>
                                    <p class="text-muted mb-4 text-center">Inicia sesión en tu cuenta para continuar</p>
                                    
                                    <!-- Formulario de Login -->
                                    <form method="POST" action="{{ route('login') }}" class="needs-validation" novalidate>
                                        @csrf
                                        
                                        <!-- Campo Correo Electrónico -->
                                        <div class="mb-4">
                                            <label for="email" class="form-label fw-semibold text-dark">
                                                Correo Electrónico
                                            </label>
                                            <div class="input-group has-validation">
                                                <span class="input-group-text bg-light border-end-0 rounded-start-pill">
                                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                                        <polyline points="22,6 12,13 2,6"></polyline>
                                                    </svg>
                                                </span>
                                                <input type="email"
                                                    class="form-control border-start-0 ps-0 @error('email') is-invalid @enderror" 
                                                    name="email" id="email" 
                                                    value="{{ old('email') }}"
                                                    placeholder="ejemplo@unamad.edu.pe" 
                                                    required autofocus>
                                            </div>
                                        </div>

                                        <!-- Campo Contraseña -->
                                        <div class="mb-4">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <label for="password" class="form-label fw-semibold text-dark mb-0">
                                                    Contraseña
                                                </label>
                                                <a href="{{ route('password.request') }}"
                                                    class="text-decoration-none text-primary small">
                                                    ¿Olvidaste tu contraseña?
                                                </a>
                                            </div>
                                            <div class="input-group has-validation">
                                                <span class="input-group-text bg-light border-end-0 rounded-start-pill">
                                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                        <path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"></path>
                                                    </svg>
                                                </span>
                                                <input type="password"
                                                    class="form-control border-start-0 border-end-0 ps-0 @error('password') is-invalid @enderror"
                                                    name="password" id="password" 
                                                    placeholder="Ingresa tu contraseña"
                                                    required>
                                                <span class="input-group-text bg-light border-start-0 cursor-pointer rounded-end-pill" 
                                                    onclick="togglePassword('password')">
                                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                                        <circle cx="12" cy="12" r="3"></circle>
                                                    </svg>
                                                </span>
                                            </div>
                                        </div>

                                        <!-- Recordar Sesión -->
                                        <div class="mb-4 form-check">
                                            <input type="checkbox" class="form-check-input" name="remember"
                                                id="remember" {{ old('remember') ? 'checked' : '' }}>
                                            <label class="form-check-label text-muted" for="remember">
                                                Mantener sesión iniciada
                                            </label>
                                        </div>

                                        <!-- Botón de Envío -->
                                        <div class="d-grid mb-4">
                                            <button class="btn btn-primary btn-lg fw-semibold animate-on-hover" type="submit">
                                                <svg class="me-2" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M15 3h6v6"></path>
                                                    <path d="M10 14 21 3"></path>
                                                </svg>
                                                Acceder al Sistema
                                            </button>
                                        </div>
                                    </form>
                                </div>

                                <!-- Tab Registro con Wizard -->
                                <div class="tab-pane fade" id="register" role="tabpanel">
                                    <div class="registration-wizard">
                                        <!-- Progress Bar -->
                                        <div class="wizard-progress mb-4">
                                            <!-- Progreso General -->
                                            <div class="overall-progress-container mb-3">
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <span class="fw-semibold text-primary">Progreso del Registro</span>
                                                    <div class="progress-percentage">
                                                        <span id="overallPercentage" class="fw-bold fs-5 text-primary">25%</span>
                                                        <small class="text-muted ms-1">completado</small>
                                                    </div>
                                                </div>
                                                <div class="progress overall-progress">
                                                    <div class="progress-bar progress-bar-striped progress-bar-animated bg-gradient" 
                                                         style="width: 25%;" id="overallProgressBar"></div>
                                                </div>
                                            </div>

                                            <!-- Indicadores de pasos -->
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <div class="step-indicator active" data-step="1">
                                                    <div class="step-circle">
                                                        <span class="step-number">1</span>
                                                        <svg class="step-check d-none" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                                            <polyline points="20,6 9,17 4,12"></polyline>
                                                        </svg>
                                                    </div>
                                                    <span class="step-label">Datos Personales</span>
                                                    <div class="step-progress-mini">
                                                        <div class="mini-progress-bar" data-step="1" style="width: 0%;"></div>
                                                    </div>
                                                </div>
                                                <div class="progress-line"></div>
                                                <div class="step-indicator" data-step="2">
                                                    <div class="step-circle">
                                                        <span class="step-number">2</span>
                                                        <svg class="step-check d-none" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                                            <polyline points="20,6 9,17 4,12"></polyline>
                                                        </svg>
                                                    </div>
                                                    <span class="step-label">Padre/Tutor</span>
                                                    <div class="step-progress-mini">
                                                        <div class="mini-progress-bar" data-step="2" style="width: 0%;"></div>
                                                    </div>
                                                </div>
                                                <div class="progress-line"></div>
                                                <div class="step-indicator" data-step="3">
                                                    <div class="step-circle">
                                                        <span class="step-number">3</span>
                                                        <svg class="step-check d-none" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                                            <polyline points="20,6 9,17 4,12"></polyline>
                                                        </svg>
                                                    </div>
                                                    <span class="step-label">Madre/Tutora</span>
                                                    <div class="step-progress-mini">
                                                        <div class="mini-progress-bar" data-step="3" style="width: 0%;"></div>
                                                    </div>
                                                </div>
                                                <div class="progress-line"></div>
                                                <div class="step-indicator" data-step="4">
                                                    <div class="step-circle">
                                                        <span class="step-number">4</span>
                                                        <svg class="step-check d-none" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                                            <polyline points="20,6 9,17 4,12"></polyline>
                                                        </svg>
                                                    </div>
                                                    <span class="step-label">Confirmación</span>
                                                    <div class="step-progress-mini">
                                                        <div class="mini-progress-bar" data-step="4" style="width: 0%;"></div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="progress">
                                                <div class="progress-bar bg-gradient" style="width: 25%;"></div>
                                            </div>
                                            <div class="text-center mt-2">
                                                <small class="text-muted">Paso <span id="currentStep">1</span> de 4</small>
                                            </div>
                                        </div>

                                        <!-- Formulario de Registro -->
                                        <form id="registrationWizard" method="POST" action="{{ route('register.postulante') }}" class="needs-validation" novalidate>
                                            @csrf
                                            
                                            <!-- Step 1: Datos Personales -->
                                            <div class="wizard-step active" data-step="1">
                                                <div class="step-header text-center mb-4">
                                                    <div class="step-icon">
                                                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                                            <circle cx="12" cy="7" r="4"></circle>
                                                        </svg>
                                                    </div>
                                                    <h4 class="step-title">Datos Personales del Postulante</h4>
                                                    <p class="step-subtitle">Complete la información personal del estudiante</p>
                                                    <div class="field-counter">
                                                        <span id="step1Counter">0 de 10 campos completados</span>
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <label for="reg_nombre" class="form-label">Nombres <span class="text-danger">*</span></label>
                                                        <div class="enhanced-input-group">
                                                            <input type="text" class="form-control form-control-wizard" id="reg_nombre" name="nombre" 
                                                                   value="{{ old('nombre') }}" required>
                                                            <div class="input-feedback"></div>
                                                        </div>
                                                        <div class="invalid-feedback">Ingrese los nombres</div>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label for="reg_apellido_paterno" class="form-label">Apellido Paterno <span class="text-danger">*</span></label>
                                                        <div class="enhanced-input-group">
                                                            <input type="text" class="form-control form-control-wizard" id="reg_apellido_paterno" 
                                                                   name="apellido_paterno" value="{{ old('apellido_paterno') }}" required>
                                                            <div class="input-feedback"></div>
                                                        </div>
                                                        <div class="invalid-feedback">Ingrese el apellido paterno</div>
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <label for="reg_apellido_materno" class="form-label">Apellido Materno <span class="text-danger">*</span></label>
                                                        <div class="enhanced-input-group">
                                                            <input type="text" class="form-control form-control-wizard" id="reg_apellido_materno" 
                                                                   name="apellido_materno" value="{{ old('apellido_materno') }}" required>
                                                            <div class="input-feedback"></div>
                                                        </div>
                                                        <div class="invalid-feedback">Ingrese el apellido materno</div>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label for="reg_tipo_documento" class="form-label">Tipo Documento <span class="text-danger">*</span></label>
                                                        <div class="enhanced-input-group">
                                                            <select class="form-select form-control-wizard" id="reg_tipo_documento" name="tipo_documento" required>
                                                                <option value="">Seleccione...</option>
                                                                <option value="DNI" {{ old('tipo_documento') == 'DNI' ? 'selected' : '' }}>DNI</option>
                                                                <option value="CE" {{ old('tipo_documento') == 'CE' ? 'selected' : '' }}>Carnet de Extranjería</option>
                                                            </select>
                                                            <div class="input-feedback"></div>
                                                        </div>
                                                        <div class="invalid-feedback">Seleccione el tipo de documento</div>
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <label for="reg_numero_documento" class="form-label">Número Documento <span class="text-danger">*</span></label>
                                                        <div class="input-group enhanced-input-group">
                                                            <input type="text" class="form-control form-control-wizard" id="reg_numero_documento" 
                                                                   name="numero_documento" value="{{ old('numero_documento') }}" 
                                                                   maxlength="8" pattern="[0-9]{8}" required>
                                                            <button class="btn btn-outline-primary btn-reniec" type="button" 
                                                                    onclick="consultarDNI('postulante')" id="btn_buscar_postulante">
                                                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                                    <circle cx="11" cy="11" r="8"></circle>
                                                                    <path d="m21 21-4.35-4.35"></path>
                                                                </svg>
                                                            </button>
                                                            <div class="input-feedback"></div>
                                                        </div>
                                                        <div class="invalid-feedback">Ingrese un número de documento válido</div>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label for="reg_fecha_nacimiento" class="form-label">Fecha Nacimiento <span class="text-danger">*</span></label>
                                                        <div class="enhanced-input-group">
                                                            <input type="date" class="form-control form-control-wizard" id="reg_fecha_nacimiento" 
                                                                   name="fecha_nacimiento" value="{{ old('fecha_nacimiento') }}" 
                                                                   max="{{ date('Y-m-d', strtotime('-14 years')) }}" required>
                                                            <div class="input-feedback"></div>
                                                        </div>
                                                        <div class="invalid-feedback">Ingrese la fecha de nacimiento</div>
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <label for="reg_genero" class="form-label">Género <span class="text-danger">*</span></label>
                                                        <div class="enhanced-input-group">
                                                            <select class="form-select form-control-wizard" id="reg_genero" name="genero" required>
                                                                <option value="">Seleccione...</option>
                                                                <option value="M" {{ old('genero') == 'M' ? 'selected' : '' }}>Masculino</option>
                                                                <option value="F" {{ old('genero') == 'F' ? 'selected' : '' }}>Femenino</option>
                                                            </select>
                                                            <div class="input-feedback"></div>
                                                        </div>
                                                        <div class="invalid-feedback">Seleccione el género</div>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label for="reg_telefono" class="form-label">Teléfono/Celular <span class="text-danger">*</span></label>
                                                        <div class="enhanced-input-group">
                                                            <input type="tel" class="form-control form-control-wizard" id="reg_telefono" 
                                                                   name="telefono" value="{{ old('telefono') }}" 
                                                                   pattern="[0-9]{9}" maxlength="9" required placeholder="999123456">
                                                            <div class="input-feedback"></div>
                                                        </div>
                                                        <div class="invalid-feedback">Ingrese un teléfono válido (9 dígitos)</div>
                                                    </div>
                                                </div>

                                                <div class="mb-3">
                                                    <label for="reg_direccion" class="form-label">Dirección <span class="text-danger">*</span></label>
                                                    <div class="enhanced-input-group">
                                                        <input type="text" class="form-control form-control-wizard" id="reg_direccion" 
                                                               name="direccion" value="{{ old('direccion') }}" required>
                                                        <div class="input-feedback"></div>
                                                    </div>
                                                    <div class="invalid-feedback">Ingrese la dirección</div>
                                                </div>

                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <label for="reg_email" class="form-label">Correo Electrónico <span class="text-danger">*</span></label>
                                                        <div class="enhanced-input-group">
                                                            <input type="email" class="form-control form-control-wizard" id="reg_email" 
                                                                   name="email" value="{{ old('email') }}" required>
                                                            <div class="input-feedback"></div>
                                                        </div>
                                                        <div class="invalid-feedback">Ingrese un correo válido</div>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label for="reg_password" class="form-label">Contraseña <span class="text-danger">*</span></label>
                                                        <div class="input-group enhanced-input-group">
                                                            <input type="password" class="form-control form-control-wizard" id="reg_password" 
                                                                   name="password" minlength="8" required>
                                                            <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('reg_password')">
                                                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                                                    <circle cx="12" cy="12" r="3"></circle>
                                                                </svg>
                                                            </button>
                                                            <div class="input-feedback"></div>
                                                        </div>
                                                        <div class="password-strength mt-2">
                                                            <div class="strength-meter">
                                                                <div class="strength-bar" id="strengthBar"></div>
                                                            </div>
                                                            <small class="strength-text" id="strengthText">Ingrese una contraseña</small>
                                                        </div>
                                                        <div class="invalid-feedback">La contraseña debe tener al menos 8 caracteres</div>
                                                    </div>
                                                </div>

                                                <div class="mb-3">
                                                    <label for="reg_password_confirmation" class="form-label">Confirmar Contraseña <span class="text-danger">*</span></label>
                                                    <div class="enhanced-input-group">
                                                        <input type="password" class="form-control form-control-wizard" id="reg_password_confirmation" 
                                                               name="password_confirmation" minlength="8" required>
                                                        <div class="input-feedback"></div>
                                                    </div>
                                                    <div class="invalid-feedback">Las contraseñas no coinciden</div>
                                                </div>
                                            </div>

                                            <!-- Step 2: Datos del Padre -->
                                            <div class="wizard-step" data-step="2" style="display: none;">
                                                <div class="step-header text-center mb-4">
                                                    <div class="step-icon">
                                                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                            <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                                            <circle cx="8.5" cy="7" r="4"></circle>
                                                            <path d="M20 21v-2a4 4 0 0 0-4-4H13"></path>
                                                            <circle cx="17.5" cy="7" r="4"></circle>
                                                        </svg>
                                                    </div>
                                                    <h4 class="step-title">Datos del Padre/Tutor</h4>
                                                    <p class="step-subtitle">Información del padre o tutor legal</p>
                                                    <div class="field-counter">
                                                        <span id="step2Counter">0 de 5 campos completados</span>
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <label for="padre_nombre" class="form-label">Nombres del Padre <span class="text-danger">*</span></label>
                                                        <div class="enhanced-input-group">
                                                            <input type="text" class="form-control form-control-wizard" id="padre_nombre" 
                                                                   name="padre_nombre" value="{{ old('padre_nombre') }}" required>
                                                            <div class="input-feedback"></div>
                                                        </div>
                                                        <div class="invalid-feedback">Ingrese los nombres del padre</div>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label for="padre_apellidos" class="form-label">Apellidos del Padre <span class="text-danger">*</span></label>
                                                        <div class="enhanced-input-group">
                                                            <input type="text" class="form-control form-control-wizard" id="padre_apellidos" 
                                                                   name="padre_apellidos" value="{{ old('padre_apellidos') }}" required>
                                                            <div class="input-feedback"></div>
                                                        </div>
                                                        <div class="invalid-feedback">Ingrese los apellidos del padre</div>
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="col-md-4 mb-3">
                                                        <label for="padre_tipo_doc" class="form-label">Tipo Doc. <span class="text-danger">*</span></label>
                                                        <div class="enhanced-input-group">
                                                            <select class="form-select form-control-wizard" id="padre_tipo_doc" name="padre_tipo_documento" required>
                                                                <option value="">Seleccione...</option>
                                                                <option value="DNI" {{ old('padre_tipo_documento') == 'DNI' ? 'selected' : '' }}>DNI</option>
                                                                <option value="CE" {{ old('padre_tipo_documento') == 'CE' ? 'selected' : '' }}>CE</option>
                                                            </select>
                                                            <div class="input-feedback"></div>
                                                        </div>
                                                        <div class="invalid-feedback">Seleccione el tipo de documento</div>
                                                    </div>
                                                    <div class="col-md-4 mb-3">
                                                        <label for="padre_numero_doc" class="form-label">Número Doc. <span class="text-danger">*</span></label>
                                                        <div class="input-group enhanced-input-group">
                                                            <input type="text" class="form-control form-control-wizard" id="padre_numero_doc" 
                                                                   name="padre_numero_documento" value="{{ old('padre_numero_documento') }}" 
                                                                   maxlength="8" pattern="[0-9]{8}" required>
                                                            <button class="btn btn-outline-primary btn-reniec" type="button" 
                                                                    onclick="consultarDNI('padre')" id="btn_buscar_padre">
                                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                                    <circle cx="11" cy="11" r="8"></circle>
                                                                    <path d="m21 21-4.35-4.35"></path>
                                                                </svg>
                                                            </button>
                                                            <div class="input-feedback"></div>
                                                        </div>
                                                        <div class="invalid-feedback">Ingrese un número válido</div>
                                                    </div>
                                                    <div class="col-md-4 mb-3">
                                                        <label for="padre_telefono" class="form-label">Teléfono <span class="text-danger">*</span></label>
                                                        <div class="enhanced-input-group">
                                                            <input type="tel" class="form-control form-control-wizard" id="padre_telefono" 
                                                                   name="padre_telefono" value="{{ old('padre_telefono') }}" 
                                                                   pattern="[0-9]{9}" maxlength="9" required>
                                                            <div class="input-feedback"></div>
                                                        </div>
                                                        <div class="invalid-feedback">Ingrese un teléfono válido</div>
                                                    </div>
                                                </div>

                                                <div class="mb-3">
                                                    <label for="padre_email" class="form-label">Correo del Padre</label>
                                                    <div class="enhanced-input-group">
                                                        <input type="email" class="form-control form-control-wizard" id="padre_email" 
                                                               name="padre_email" value="{{ old('padre_email') }}">
                                                        <div class="input-feedback"></div>
                                                    </div>
                                                    <small class="form-text text-muted">Debe ser diferente al correo de la madre</small>
                                                    <div class="invalid-feedback" id="padre_email_feedback">Ingrese un correo válido</div>
                                                </div>
                                            </div>

                                            <!-- Step 3: Datos de la Madre -->
                                            <div class="wizard-step" data-step="3" style="display: none;">
                                                <div class="step-header text-center mb-4">
                                                    <div class="step-icon">
                                                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                            <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                                            <circle cx="8.5" cy="7" r="4"></circle>
                                                            <path d="M20 21v-2a4 4 0 0 0-4-4H13"></path>
                                                            <circle cx="17.5" cy="7" r="4"></circle>
                                                        </svg>
                                                    </div>
                                                    <h4 class="step-title">Datos de la Madre/Tutora</h4>
                                                    <p class="step-subtitle">Información de la madre o tutora legal</p>
                                                    <div class="field-counter">
                                                        <span id="step3Counter">0 de 5 campos completados</span>
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="col-md-6 mb-3">
                                                        <label for="madre_nombre" class="form-label">Nombres de la Madre <span class="text-danger">*</span></label>
                                                        <div class="enhanced-input-group">
                                                            <input type="text" class="form-control form-control-wizard" id="madre_nombre" 
                                                                   name="madre_nombre" value="{{ old('madre_nombre') }}" required>
                                                            <div class="input-feedback"></div>
                                                        </div>
                                                        <div class="invalid-feedback">Ingrese los nombres de la madre</div>
                                                    </div>
                                                    <div class="col-md-6 mb-3">
                                                        <label for="madre_apellidos" class="form-label">Apellidos de la Madre <span class="text-danger">*</span></label>
                                                        <div class="enhanced-input-group">
                                                            <input type="text" class="form-control form-control-wizard" id="madre_apellidos" 
                                                                   name="madre_apellidos" value="{{ old('madre_apellidos') }}" required>
                                                            <div class="input-feedback"></div>
                                                        </div>
                                                        <div class="invalid-feedback">Ingrese los apellidos de la madre</div>
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="col-md-4 mb-3">
                                                        <label for="madre_tipo_doc" class="form-label">Tipo Doc. <span class="text-danger">*</span></label>
                                                        <div class="enhanced-input-group">
                                                            <select class="form-select form-control-wizard" id="madre_tipo_doc" name="madre_tipo_documento" required>
                                                                <option value="">Seleccione...</option>
                                                                <option value="DNI" {{ old('madre_tipo_documento') == 'DNI' ? 'selected' : '' }}>DNI</option>
                                                                <option value="CE" {{ old('madre_tipo_documento') == 'CE' ? 'selected' : '' }}>CE</option>
                                                            </select>
                                                            <div class="input-feedback"></div>
                                                        </div>
                                                        <div class="invalid-feedback">Seleccione el tipo de documento</div>
                                                    </div>
                                                    <div class="col-md-4 mb-3">
                                                        <label for="madre_numero_doc" class="form-label">Número Doc. <span class="text-danger">*</span></label>
                                                        <div class="input-group enhanced-input-group">
                                                            <input type="text" class="form-control form-control-wizard" id="madre_numero_doc" 
                                                                   name="madre_numero_documento" value="{{ old('madre_numero_documento') }}" 
                                                                   maxlength="8" pattern="[0-9]{8}" required>
                                                            <button class="btn btn-outline-primary btn-reniec" type="button" 
                                                                    onclick="consultarDNI('madre')" id="btn_buscar_madre">
                                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                                    <circle cx="11" cy="11" r="8"></circle>
                                                                    <path d="m21 21-4.35-4.35"></path>
                                                                </svg>
                                                            </button>
                                                            <div class="input-feedback"></div>
                                                        </div>
                                                        <div class="invalid-feedback">Ingrese un número válido</div>
                                                    </div>
                                                    <div class="col-md-4 mb-3">
                                                        <label for="madre_telefono" class="form-label">Teléfono <span class="text-danger">*</span></label>
                                                        <div class="enhanced-input-group">
                                                            <input type="tel" class="form-control form-control-wizard" id="madre_telefono" 
                                                                   name="madre_telefono" value="{{ old('madre_telefono') }}" 
                                                                   pattern="[0-9]{9}" maxlength="9" required>
                                                            <div class="input-feedback"></div>
                                                        </div>
                                                        <div class="invalid-feedback">Ingrese un teléfono válido</div>
                                                    </div>
                                                </div>

                                                <div class="mb-3">
                                                    <label for="madre_email" class="form-label">Correo de la Madre</label>
                                                    <div class="enhanced-input-group">
                                                        <input type="email" class="form-control form-control-wizard" id="madre_email" 
                                                               name="madre_email" value="{{ old('madre_email') }}">
                                                        <div class="input-feedback"></div>
                                                    </div>
                                                    <small class="form-text text-muted">Debe ser diferente al correo del padre</small>
                                                    <div class="invalid-feedback" id="madre_email_feedback">Ingrese un correo válido</div>
                                                </div>
                                            </div>

                                            <!-- Step 4: Confirmación -->
                                            <div class="wizard-step" data-step="4" style="display: none;">
                                                <div class="step-header text-center mb-4">
                                                    <div class="step-icon">
                                                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                            <path d="M9 12l2 2 4-4"></path>
                                                            <path d="M21 12c-1 0-3-1-3-3s2-3 3-3 3 1 3 3-2 3-3 3"></path>
                                                            <path d="M3 12c1 0 3-1 3-3s-2-3-3-3-3 1-3 3 2 3 3 3"></path>
                                                        </svg>
                                                    </div>
                                                    <h4 class="step-title">Confirmación de Datos</h4>
                                                    <p class="step-subtitle">Revise toda la información antes de enviar</p>
                                                </div>

                                                <div id="confirmationSummary" class="confirmation-container">
                                                    <!-- Resumen será generado por JavaScript -->
                                                </div>

                                                <div class="form-check mb-4">
                                                    <input class="form-check-input" type="checkbox" id="terms" name="terms" required>
                                                    <label class="form-check-label" for="terms">
                                                        Acepto los <a href="#" class="text-primary">términos y condiciones</a> y la 
                                                        <a href="#" class="text-primary">política de privacidad</a>
                                                    </label>
                                                    <div class="invalid-feedback">Debe aceptar los términos y condiciones</div>
                                                </div>
                                            </div>
                                        </form>

                                        <!-- Navigation Buttons -->
                                        <div class="wizard-navigation d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                                            <button type="button" class="btn btn-outline-secondary animate-on-hover" id="prevStepBtn" onclick="previousStep()" style="display: none;">
                                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="me-2">
                                                    <path d="M19 12H5"></path>
                                                    <path d="M12 19l-7-7 7-7"></path>
                                                </svg>
                                                Anterior
                                            </button>
                                            <div class="step-info text-muted small d-flex align-items-center">
                                                <div class="mini-progress-indicator me-2">
                                                    <div class="mini-dots">
                                                        <span class="dot active"></span>
                                                        <span class="dot"></span>
                                                        <span class="dot"></span>
                                                        <span class="dot"></span>
                                                    </div>
                                                </div>
                                                <span id="stepCounter">Paso 1 de 4</span>
                                            </div>
                                            <button type="button" class="btn btn-primary animate-on-hover" id="nextStepBtn" onclick="nextStep()">
                                                <span class="btn-text">Siguiente</span>
                                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="ms-2">
                                                    <path d="M5 12h14"></path>
                                                    <path d="M12 5l7 7-7 7"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Información Adicional -->
                            <div class="text-center mt-4">
                                <p class="text-muted small mb-0">
                                    <svg class="me-1" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M9 12l2 2 4-4"></path>
                                        <path d="M21 12c-1 0-3-1-3-3s2-3 3-3 3 1 3 3-2 3-3 3"></path>
                                        <path d="M3 12c1 0 3-1 3-3s-2-3-3-3-3 1-3 3 2 3 3 3"></path>
                                    </svg>
                                    Conexión segura y encriptada
                                </p>
                                <div class="mt-2">
                                    <small class="text-success">Tiempo estimado: 3-5 minutos</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- SweetAlert2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>

    <script>
        // Variables globales
        const csrfToken = '{{ csrf_token() }}';
        
        // Variables del wizard
        let wizardCurrentStep = 1;
        const wizardTotalSteps = 4;
        let wizardFormData = {};
        
        // Variables para el progreso
        let fieldCounts = {
            1: { total: 10, completed: 0 },
            2: { total: 5, completed: 0 },
            3: { total: 5, completed: 0 },
            4: { total: 1, completed: 0 }
        };

        // Función para mostrar/ocultar contraseña
        function togglePassword(fieldId) {
            const passwordInput = document.getElementById(fieldId);
            const icon = passwordInput.nextElementSibling?.querySelector('svg');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                if (icon) {
                    icon.innerHTML = `
                        <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                        <line x1="1" y1="1" x2="23" y2="23"></line>
                    `;
                }
            } else {
                passwordInput.type = 'password';
                if (icon) {
                    icon.innerHTML = `
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                        <circle cx="12" cy="12" r="3"></circle>
                    `;
                }
            }
        }
        
        // Función para consultar DNI en RENIEC MEJORADA CON SWEETALERT2
        async function consultarDNI(tipo) {
            let dniInput, btnBuscar;
            
            if (tipo === 'postulante') {
                dniInput = document.getElementById('reg_numero_documento');
                btnBuscar = document.getElementById('btn_buscar_postulante');
            } else if (tipo === 'padre') {
                dniInput = document.getElementById('padre_numero_doc');
                btnBuscar = document.getElementById('btn_buscar_padre');
            } else if (tipo === 'madre') {
                dniInput = document.getElementById('madre_numero_doc');
                btnBuscar = document.getElementById('btn_buscar_madre');
            }
            
            const dni = dniInput.value.trim();
            
            if (dni.length !== 8 || !/^\d{8}$/.test(dni)) {
                Swal.fire({
                    icon: 'warning',
                    title: 'DNI Inválido',
                    text: 'El DNI debe tener exactamente 8 dígitos numéricos',
                    confirmButtonColor: '#3F51B5'
                });
                dniInput.focus();
                return;
            }
            
            const btnTextoOriginal = btnBuscar.innerHTML;
            btnBuscar.disabled = true;
            btnBuscar.classList.add('loading');
            btnBuscar.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
            
            try {
                const response = await fetch('/api/reniec/consultar', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ dni: dni })
                });
                
                if (!response.ok) {
                    throw new Error(`Error HTTP: ${response.status}`);
                }
                
                const result = await response.json();
                console.log('Respuesta RENIEC:', result);
                
                if (result.success && result.data) {
                    if (tipo === 'postulante') {
                        autocompletarPostulante(result.data);
                    } else if (tipo === 'padre') {
                        autocompletarPadre(result.data);
                    } else if (tipo === 'madre') {
                        autocompletarMadre(result.data);
                    }
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'Datos Encontrados',
                        text: 'Información cargada desde RENIEC correctamente',
                        timer: 2000,
                        timerProgressBar: true,
                        showConfirmButton: false,
                        toast: true,
                        position: 'top-end'
                    });
                    
                    btnBuscar.classList.add('success-animation');
                    setTimeout(() => btnBuscar.classList.remove('success-animation'), 1000);
                } else {
                    Swal.fire({
                        icon: 'info',
                        title: 'Sin Resultados',
                        text: result.message || 'No se encontraron datos para el DNI ingresado',
                        confirmButtonColor: '#3F51B5'
                    });
                }
            } catch (error) {
                console.error('Error al consultar RENIEC:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error de Conexión',
                    text: 'No se pudo consultar el servicio RENIEC. Intente nuevamente.',
                    confirmButtonColor: '#3F51B5'
                });
            } finally {
                btnBuscar.disabled = false;
                btnBuscar.classList.remove('loading');
                btnBuscar.innerHTML = btnTextoOriginal;
                updateFieldProgress();
            }
        }
        
        function autocompletarPostulante(datos) {
            const fields = [
                { id: 'reg_nombre', value: datos.nombres },
                { id: 'reg_apellido_paterno', value: datos.apellido_paterno },
                { id: 'reg_apellido_materno', value: datos.apellido_materno },
                { id: 'reg_fecha_nacimiento', value: datos.fecha_nacimiento },
                { id: 'reg_genero', value: datos.genero },
                { id: 'reg_direccion', value: datos.direccion }
            ];
            
            fields.forEach((field, index) => {
                setTimeout(() => {
                    const element = document.getElementById(field.id);
                    if (element && field.value) {
                        element.value = field.value;
                        element.classList.add('auto-filled');
                        triggerFieldValidation(element);
                        setTimeout(() => element.classList.remove('auto-filled'), 500);
                    }
                }, index * 100);
            });
            
            document.getElementById('reg_tipo_documento').value = 'DNI';
            updateFieldProgress();
        }
        
        function autocompletarPadre(datos) {
            const apellidos = (datos.apellido_paterno || '') + ' ' + (datos.apellido_materno || '');
            setTimeout(() => {
                if (datos.nombres) {
                    const nombreField = document.getElementById('padre_nombre');
                    nombreField.value = datos.nombres;
                    nombreField.classList.add('auto-filled');
                    triggerFieldValidation(nombreField);
                    setTimeout(() => nombreField.classList.remove('auto-filled'), 500);
                }
                if (apellidos.trim()) {
                    const apellidoField = document.getElementById('padre_apellidos');
                    apellidoField.value = apellidos.trim();
                    apellidoField.classList.add('auto-filled');
                    triggerFieldValidation(apellidoField);
                    setTimeout(() => apellidoField.classList.remove('auto-filled'), 500);
                }
                document.getElementById('padre_tipo_doc').value = 'DNI';
                updateFieldProgress();
            }, 200);
        }
        
        function autocompletarMadre(datos) {
            const apellidos = (datos.apellido_paterno || '') + ' ' + (datos.apellido_materno || '');
            setTimeout(() => {
                if (datos.nombres) {
                    const nombreField = document.getElementById('madre_nombre');
                    nombreField.value = datos.nombres;
                    nombreField.classList.add('auto-filled');
                    triggerFieldValidation(nombreField);
                    setTimeout(() => nombreField.classList.remove('auto-filled'), 500);
                }
                if (apellidos.trim()) {
                    const apellidoField = document.getElementById('madre_apellidos');
                    apellidoField.value = apellidos.trim();
                    apellidoField.classList.add('auto-filled');
                    triggerFieldValidation(apellidoField);
                    setTimeout(() => apellidoField.classList.remove('auto-filled'), 500);
                }
                document.getElementById('madre_tipo_doc').value = 'DNI';
                updateFieldProgress();
            }, 200);
        }

        // === FUNCIONES DEL WIZARD ===
        
        function nextStep() {
            if (validateWizardStep(wizardCurrentStep)) {
                saveCurrentStepData();
                
                if (wizardCurrentStep < wizardTotalSteps) {
                    wizardCurrentStep++;
                    updateWizardDisplay();
                    
                    if (wizardCurrentStep === 4) {
                        generateConfirmationSummary();
                    }
                    
                    celebrateStepCompletion();
                } else {
                    submitWizardForm();
                }
            }
        }

        function previousStep() {
            if (wizardCurrentStep > 1) {
                wizardCurrentStep--;
                updateWizardDisplay();
            }
        }

        function updateWizardDisplay() {
            // Actualizar indicadores de paso
            document.querySelectorAll('.step-indicator').forEach((indicator, index) => {
                const stepNum = index + 1;
                indicator.classList.remove('active', 'completed');
                
                if (stepNum < wizardCurrentStep) {
                    indicator.classList.add('completed');
                    indicator.querySelector('.step-number').style.display = 'none';
                    indicator.querySelector('.step-check').classList.remove('d-none');
                } else if (stepNum === wizardCurrentStep) {
                    indicator.classList.add('active');
                    indicator.querySelector('.step-number').style.display = 'block';
                    indicator.querySelector('.step-check').classList.add('d-none');
                } else {
                    indicator.querySelector('.step-number').style.display = 'block';
                    indicator.querySelector('.step-check').classList.add('d-none');
                }
            });

            const progressPercent = (wizardCurrentStep / wizardTotalSteps) * 100;
            document.querySelector('.progress-bar').style.width = progressPercent + '%';
            updateOverallProgress();

            document.querySelectorAll('.wizard-step').forEach((step, index) => {
                if (index + 1 === wizardCurrentStep) {
                    step.style.display = 'block';
                    step.classList.add('active');
                    step.style.opacity = '0';
                    step.style.transform = 'translateX(50px)';
                    setTimeout(() => {
                        step.style.transition = 'all 0.4s cubic-bezier(0.4, 0, 0.2, 1)';
                        step.style.opacity = '1';
                        step.style.transform = 'translateX(0)';
                    }, 50);
                } else {
                    step.style.display = 'none';
                    step.classList.remove('active');
                }
            });

            const prevBtn = document.getElementById('prevStepBtn');
            const nextBtn = document.getElementById('nextStepBtn');
            const stepCounter = document.getElementById('stepCounter');
            const currentStepSpan = document.getElementById('currentStep');

            prevBtn.style.display = wizardCurrentStep === 1 ? 'none' : 'flex';
            stepCounter.textContent = `Paso ${wizardCurrentStep} de ${wizardTotalSteps}`;
            if (currentStepSpan) currentStepSpan.textContent = wizardCurrentStep;

            document.querySelectorAll('.mini-dots .dot').forEach((dot, index) => {
                dot.classList.toggle('active', index + 1 <= wizardCurrentStep);
            });

            if (wizardCurrentStep === wizardTotalSteps) {
                nextBtn.querySelector('.btn-text').textContent = 'Registrar Postulación';
                nextBtn.classList.add('btn-success');
                nextBtn.classList.remove('btn-primary');
            } else {
                nextBtn.querySelector('.btn-text').textContent = 'Siguiente';
                nextBtn.classList.add('btn-primary');
                nextBtn.classList.remove('btn-success');
            }
        }

        function updateFieldProgress() {
            const currentStepElement = document.querySelector(`.wizard-step[data-step="${wizardCurrentStep}"]`);
            if (!currentStepElement) return;

            const requiredFields = currentStepElement.querySelectorAll('[required]');
            const optionalFields = currentStepElement.querySelectorAll('input:not([required]), select:not([required])');
            const allFields = [...requiredFields, ...optionalFields];
            
            let completed = 0;

            allFields.forEach(field => {
                if (field.value.trim()) {
                    completed++;
                }
            });

            fieldCounts[wizardCurrentStep].completed = completed;
            fieldCounts[wizardCurrentStep].total = allFields.length;

            const counter = document.getElementById(`step${wizardCurrentStep}Counter`);
            if (counter) {
                counter.textContent = `${completed} de ${allFields.length} campos completados`;
                
                const progress = completed / allFields.length;
                if (progress === 1) {
                    counter.className = 'field-counter text-success fw-bold';
                } else if (progress >= 0.7) {
                    counter.className = 'field-counter text-warning fw-semibold';
                } else {
                    counter.className = 'field-counter text-muted';
                }
            }

            const miniProgressBar = document.querySelector(`.mini-progress-bar[data-step="${wizardCurrentStep}"]`);
            if (miniProgressBar) {
                const stepProgress = (completed / allFields.length) * 100;
                miniProgressBar.style.width = stepProgress + '%';
            }

            updateOverallProgress();
        }

        function updateOverallProgress() {
            let totalFields = 0;
            let completedFields = 0;

            for (let step = 1; step <= wizardTotalSteps; step++) {
                if (step < wizardCurrentStep) {
                    const stepElement = document.querySelector(`.wizard-step[data-step="${step}"]`);
                    const allFields = stepElement.querySelectorAll('input, select');
                    totalFields += allFields.length;
                    completedFields += allFields.length;
                } else if (step === wizardCurrentStep) {
                    totalFields += fieldCounts[step].total;
                    completedFields += fieldCounts[step].completed;
                } else {
                    totalFields += fieldCounts[step].total;
                }
            }

            const overallProgress = Math.round((completedFields / totalFields) * 100);
            const overallProgressBar = document.getElementById('overallProgressBar');
            const overallPercentage = document.getElementById('overallPercentage');

            if (overallProgressBar && overallPercentage) {
                overallProgressBar.style.width = overallProgress + '%';
                overallPercentage.textContent = overallProgress + '%';
            }
        }

        function updatePasswordStrength(password) {
            const strengthBar = document.getElementById('strengthBar');
            const strengthText = document.getElementById('strengthText');
            
            if (!strengthBar || !strengthText) return;
            
            let strength = 0;
            let feedback = '';

            if (password.length >= 8) strength += 20;
            if (/[a-z]/.test(password)) strength += 20;
            if (/[A-Z]/.test(password)) strength += 20;
            if (/[0-9]/.test(password)) strength += 20;
            if (/[^A-Za-z0-9]/.test(password)) strength += 20;

            if (strength === 0) {
                feedback = 'Ingrese una contraseña';
                strengthBar.className = 'strength-bar';
            } else if (strength <= 40) {
                feedback = 'Contraseña débil';
                strengthBar.className = 'strength-bar weak';
            } else if (strength <= 60) {
                feedback = 'Contraseña regular';
                strengthBar.className = 'strength-bar fair';
            } else if (strength <= 80) {
                feedback = 'Contraseña buena';
                strengthBar.className = 'strength-bar good';
            } else {
                feedback = 'Contraseña excelente';
                strengthBar.className = 'strength-bar strong';
            }

            strengthBar.style.width = Math.min(strength, 100) + '%';
            strengthText.textContent = feedback;
        }

        function celebrateStepCompletion() {
            const currentStepIndicator = document.querySelector(`.step-indicator[data-step="${wizardCurrentStep - 1}"]`);
            if (currentStepIndicator) {
                currentStepIndicator.classList.add('celebration');
                setTimeout(() => currentStepIndicator.classList.remove('celebration'), 600);
            }
        }

        function validateParentEmails() {
            const padreEmail = document.getElementById('padre_email').value.trim();
            const madreEmail = document.getElementById('madre_email').value.trim();
            
            if (padreEmail && madreEmail && padreEmail === madreEmail) {
                document.getElementById('padre_email').classList.add('is-invalid');
                document.getElementById('madre_email').classList.add('is-invalid');
                document.getElementById('padre_email_feedback').textContent = 'No puede ser igual al correo de la madre';
                document.getElementById('madre_email_feedback').textContent = 'No puede ser igual al correo del padre';
                
                Swal.fire({
                    icon: 'warning',
                    title: 'Correos Duplicados',
                    text: 'Los correos del padre y la madre deben ser diferentes',
                    confirmButtonColor: '#3F51B5'
                });
                return false;
            }
            
            if (padreEmail !== madreEmail) {
                document.getElementById('padre_email').classList.remove('is-invalid');
                document.getElementById('madre_email').classList.remove('is-invalid');
                document.getElementById('padre_email_feedback').textContent = 'Ingrese un correo válido';
                document.getElementById('madre_email_feedback').textContent = 'Ingrese un correo válido';
            }
            
            return true;
        }

        function triggerFieldValidation(field) {
            field.classList.remove('is-invalid', 'is-valid');
            
            if (field.hasAttribute('required') && !field.value.trim()) {
                field.classList.add('is-invalid');
                return false;
            }
            
            if (field.type === 'email' && field.value && !isValidEmail(field.value)) {
                field.classList.add('is-invalid');
                return false;
            }
            
            if (field.name === 'password_confirmation') {
                const password = document.getElementById('reg_password').value;
                if (field.value !== password) {
                    field.classList.add('is-invalid');
                    return false;
                }
            }
            
            if (field.pattern && field.value && !new RegExp(field.pattern).test(field.value)) {
                field.classList.add('is-invalid');
                return false;
            }
            
            if (field.value.trim()) {
                field.classList.add('is-valid');
                
                const feedback = field.closest('.enhanced-input-group')?.querySelector('.input-feedback');
                if (feedback) {
                    feedback.innerHTML = '<i class="text-success">✓</i>';
                    feedback.classList.add('show');
                }
            }
            
            return true;
        }

        function validateWizardStep(step) {
            const currentStepElement = document.querySelector(`.wizard-step[data-step="${step}"]`);
            const requiredFields = currentStepElement.querySelectorAll('[required]');
            let isValid = true;

            requiredFields.forEach(field => {
                if (!triggerFieldValidation(field)) {
                    isValid = false;
                }
            });

            if ((step === 2 || step === 3) && !validateParentEmails()) {
                isValid = false;
            }

            if (!isValid) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Campos Requeridos',
                    text: 'Por favor complete todos los campos obligatorios correctamente',
                    confirmButtonColor: '#3F51B5'
                });
                const firstInvalid = currentStepElement.querySelector('.is-invalid');
                if (firstInvalid) {
                    firstInvalid.focus();
                    firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstInvalid.classList.add('shake');
                    setTimeout(() => firstInvalid.classList.remove('shake'), 500);
                }
            }

            return isValid;
        }

        // FUNCIÓN SAVECURRENTSTEPDATA CORREGIDA
        function saveCurrentStepData() {
            // Guardar datos de TODOS los campos del formulario, no solo del paso actual
            const allInputs = document.querySelectorAll('#registrationWizard input, #registrationWizard select');
            
            allInputs.forEach(input => {
                if (input.value.trim()) {
                    wizardFormData[input.name] = input.value;
                }
            });
        }

        function generateConfirmationSummary() {
            const container = document.getElementById('confirmationSummary');
            
            // CORRECCIÓN: Obtener datos directamente del formulario, no de wizardFormData
            const form = document.getElementById('registrationWizard');
            const formData = new FormData(form);
            
            const sections = [
                {
                    title: 'Datos Personales del Postulante',
                    icon: '👤',
                    data: {
                        'Nombres': formData.get('nombre'),
                        'Apellido Paterno': formData.get('apellido_paterno'),
                        'Apellido Materno': formData.get('apellido_materno'),
                        'Tipo de Documento': formData.get('tipo_documento'),
                        'Número de Documento': formData.get('numero_documento'),
                        'Fecha de Nacimiento': formData.get('fecha_nacimiento'),
                        'Género': formData.get('genero') === 'M' ? 'Masculino' : 'Femenino',
                        'Teléfono': formData.get('telefono'),
                        'Dirección': formData.get('direccion'),
                        'Correo Electrónico': formData.get('email')
                    }
                },
                {
                    title: 'Datos del Padre/Tutor',
                    icon: '👨',
                    data: {
                        'Nombres': formData.get('padre_nombre'),
                        'Apellidos': formData.get('padre_apellidos'),
                        'Tipo de Documento': formData.get('padre_tipo_documento'),
                        'Número de Documento': formData.get('padre_numero_documento'),
                        'Teléfono': formData.get('padre_telefono'),
                        'Correo': formData.get('padre_email') || 'No proporcionado'
                    }
                },
                {
                    title: 'Datos de la Madre/Tutora',
                    icon: '👩',
                    data: {
                        'Nombres': formData.get('madre_nombre'),
                        'Apellidos': formData.get('madre_apellidos'),
                        'Tipo de Documento': formData.get('madre_tipo_documento'),
                        'Número de Documento': formData.get('madre_numero_documento'),
                        'Teléfono': formData.get('madre_telefono'),
                        'Correo': formData.get('madre_email') || 'No proporcionado'
                    }
                }
            ];

            let html = '';
            sections.forEach(section => {
                html += `
                    <div class="confirmation-section animate__animated animate__fadeInUp">
                        <h5 class="confirmation-title">
                            <span class="me-2">${section.icon}</span>
                            ${section.title}
                        </h5>
                        <div class="confirmation-data">
                            ${Object.entries(section.data).map(([key, value]) => `
                                <div class="data-row">
                                    <span class="data-label">${key}:</span>
                                    <span class="data-value">${value || 'No especificado'}</span>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                `;
            });

            container.innerHTML = html;
        }

        // FUNCIÓN SUBMITWIZARDFORM CORREGIDA
        async function submitWizardForm() {
            const termsCheckbox = document.getElementById('terms');
            if (!termsCheckbox.checked) {
                termsCheckbox.classList.add('is-invalid');
                Swal.fire({
                    icon: 'warning',
                    title: 'Términos y Condiciones',
                    text: 'Debe aceptar los términos y condiciones para continuar',
                    confirmButtonColor: '#3F51B5'
                });
                return;
            }

            // CORRECCIÓN: Obtener datos directamente del formulario completo
            const form = document.getElementById('registrationWizard');
            const formData = new FormData(form);
            
            // Asegurar que términos esté incluido
            if (termsCheckbox.checked) {
                formData.append('terms', 'on');
            }

            const email = formData.get('email');
            
            // Mostrar confirmación con SweetAlert2
            const result = await Swal.fire({
                title: 'Confirmar Registro',
                html: `
                    <div class="text-start">
                        <p><strong>Se enviará un correo de confirmación a:</strong></p>
                        <p class="text-primary fs-5">${email}</p>
                        <p><small class="text-muted">Su cuenta se creará con estado PENDIENTE hasta que verifique su correo electrónico.</small></p>
                    </div>
                `,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3F51B5',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, registrar',
                cancelButtonText: 'Cancelar',
                reverseButtons: true,
                focusCancel: false
            });
            
            if (!result.isConfirmed) {
                return;
            }

            // Mostrar loading
            Swal.fire({
                title: 'Registrando...',
                html: 'Por favor espere mientras procesamos su información',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                willOpen: () => {
                    Swal.showLoading();
                }
            });

            try {
                console.log('Enviando datos de registro...');

                // CORRECCIÓN: Usar la misma estructura que funciona
                const response = await fetch('{{ route("api.register.postulante") }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'  // HEADER CRUCIAL
                    },
                    body: formData  // DATOS COMPLETOS DEL FORMULARIO
                });
                
                console.log('Response status:', response.status);
                
                const responseText = await response.text();
                console.log('Response text:', responseText);
                
                let data;
                try {
                    data = JSON.parse(responseText);
                } catch (parseError) {
                    console.error('Error parsing JSON:', parseError);
                    console.log('Raw response:', responseText);
                    throw new Error('La respuesta del servidor no es JSON válido');
                }
                
                console.log('Parsed data:', data);
                
                if (data.success) {
                    Swal.close();
                    
                    let mensajeExito = '';
                    let icono = 'success';
                    
                    if (data.email_status === 'sent') {
                        mensajeExito = `
                            <div class="text-center">
                                <h4 class="text-success mb-3">¡Registro Exitoso!</h4>
                                <p>Se ha enviado un correo de verificación a:</p>
                                <p class="fw-bold text-primary">${email}</p>
                                <p class="small text-muted">Revise su bandeja de entrada y haga clic en el enlace para activar su cuenta.</p>
                            </div>
                        `;
                    } else {
                        mensajeExito = `
                            <div class="text-center">
                                <h4 class="text-warning mb-3">Registro Completado</h4>
                                <p>Su cuenta fue creada pero el correo no pudo ser enviado.</p>
                                <p class="fw-bold text-primary">${email}</p>
                                <p class="small text-muted">Contacte al administrador para activar su cuenta.</p>
                            </div>
                        `;
                        icono = 'warning';
                    }
                    
                    await Swal.fire({
                        html: mensajeExito,
                        icon: icono,
                        confirmButtonColor: '#3F51B5',
                        confirmButtonText: 'Ir al Login',
                        allowOutsideClick: false
                    });
                    
                    // Mostrar información adicional si hay padres existentes
                    if (data.data && data.data.padre_existente) {
                        await Swal.fire({
                            title: 'Padre Vinculado',
                            html: `
                                <p><strong>${data.data.padre_existente.nombre}</strong> ya estaba registrado en el sistema.</p>
                                <p><small>DNI: ${data.data.padre_existente.dni}</small></p>
                                <p><small>Hijos registrados: ${data.data.padre_existente.hijos_registrados}</small></p>
                            `,
                            icon: 'info',
                            confirmButtonColor: '#3F51B5'
                        });
                    }
                    
                    if (data.data && data.data.madre_existente) {
                        await Swal.fire({
                            title: 'Madre Vinculada',
                            html: `
                                <p><strong>${data.data.madre_existente.nombre}</strong> ya estaba registrada en el sistema.</p>
                                <p><small>DNI: ${data.data.madre_existente.dni}</small></p>
                                <p><small>Hijos registrados: ${data.data.madre_existente.hijos_registrados}</small></p>
                            `,
                            icon: 'info',
                            confirmButtonColor: '#3F51B5'
                        });
                    }
                    
                    // Limpiar formulario y redirigir
                    document.getElementById('registrationWizard').reset();
                    window.location.href = '{{ route("login") }}';
                    
                } else {
                    Swal.close();
                    
                    let errorMessages = '';
                    if (data.errors) {
                        const errorList = Object.values(data.errors).flat();
                        errorMessages = errorList.join('<br>');
                    } else {
                        errorMessages = data.message || 'Error desconocido en el registro';
                    }
                    
                    await Swal.fire({
                        icon: 'error',
                        title: 'Error en el Registro',
                        html: errorMessages,
                        confirmButtonColor: '#3F51B5'
                    });
                }
                
            } catch (error) {
                console.error('Error completo:', error);
                Swal.close();
                
                await Swal.fire({
                    icon: 'error',
                    title: 'Error de Conexión',
                    text: 'No se pudo conectar con el servidor: ' + error.message,
                    confirmButtonColor: '#3F51B5'
                });
            }
        }

        function isValidEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }

        // Eventos de inicialización
        document.addEventListener('DOMContentLoaded', function() {
            @if(session('register_tab'))
                var registerTab = new bootstrap.Tab(document.getElementById('register-tab'));
                registerTab.show();
            @endif

            updateWizardDisplay();
            updateFieldProgress();

            document.querySelectorAll('.form-control-wizard, .form-select').forEach(field => {
                field.addEventListener('input', function() {
                    triggerFieldValidation(this);
                    updateFieldProgress();
                    
                    if (this.id === 'reg_password') {
                        updatePasswordStrength(this.value);
                    }
                    
                    if (this.id === 'padre_email' || this.id === 'madre_email') {
                        setTimeout(() => validateParentEmails(), 100);
                    }
                });
                
                field.addEventListener('blur', function() {
                    triggerFieldValidation(this);
                    updateFieldProgress();
                    
                    if (this.id === 'padre_email' || this.id === 'madre_email') {
                        validateParentEmails();
                    }
                });

                field.addEventListener('focus', function() {
                    this.closest('.enhanced-input-group')?.classList.add('focused');
                });
                
                field.addEventListener('blur', function() {
                    this.closest('.enhanced-input-group')?.classList.remove('focused');
                });
            });

            // Eventos para buscar RENIEC con Enter
            ['reg_numero_documento', 'padre_numero_doc', 'madre_numero_doc'].forEach(id => {
                const element = document.getElementById(id);
                if (element) {
                    element.addEventListener('keypress', function(e) {
                        if (e.key === 'Enter') {
                            e.preventDefault();
                            const tipo = id.includes('padre') ? 'padre' : id.includes('madre') ? 'madre' : 'postulante';
                            consultarDNI(tipo);
                        }
                    });
                }
            });
        });

        // Validación de formulario con Bootstrap
        (function() {
            'use strict';
            window.addEventListener('load', function() {
                var forms = document.querySelectorAll('.needs-validation');
                Array.prototype.slice.call(forms)
                    .forEach(function(form) {
                        form.addEventListener('submit', function(event) {
                            if (!form.checkValidity()) {
                                event.preventDefault();
                                event.stopPropagation();
                            }
                            form.classList.add('was-validated');
                        }, false);
                    });
            }, false);
        })();
    </script>

    <!-- Estilos CSS COMPLETOS -->
    <style>
        @import url('https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css');
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');

        body {
            font-family: 'Inter', sans-serif;
            margin: 0;
            overflow-x: hidden;
        }

        .min-vh-100 { min-height: 100vh; }
        .cursor-pointer { cursor: pointer; }
        .object-fit-cover { object-fit: cover; }
        .shadow-lg { box-shadow: 0 1rem 3rem rgba(0, 0, 0, 0.175) !important; }

        .bg-dark-overlay {
            background: linear-gradient(135deg, rgba(63, 81, 181, 0.9) 0%, rgba(48, 63, 159, 0.9) 100%);
        }
        
        .nav-pills .nav-link {
            color: #6c757d;
            background-color: #f8f9fa;
            border-radius: 10px;
            padding: 12px 20px;
            transition: all 0.3s ease;
        }
        
        .nav-pills .nav-link.active {
            background-color: #3F51B5;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(63, 81, 181, 0.3);
        }
        
        .nav-pills .nav-link:hover:not(.active) {
            background-color: #e9ecef;
            transform: translateY(-1px);
        }
        
        .form-control:focus, .form-select:focus {
            border-color: #3F51B5;
            box-shadow: 0 0 0 0.2rem rgba(63, 81, 181, 0.25);
            transform: translateY(-1px);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #3F51B5 0%, #303F9F 100%);
            border: none;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #303F9F 0%, #1A237E 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(63, 81, 181, 0.4);
        }

        .btn-success {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border: none;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .btn-success:hover {
            background: linear-gradient(135deg, #1e7e34 0%, #17a2b8 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(40, 167, 69, 0.4);
        }

        .text-primary { color: #3F51B5 !important; }
        
        .card {
            backdrop-filter: blur(20px);
            background: rgba(255, 255, 255, 0.98) !important;
            max-height: 95vh;
            overflow-y: auto;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        /* === ESTILOS DEL WIZARD === */
        
        .registration-wizard { min-height: 600px; }

        .overall-progress-container {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 15px;
            padding: 1rem;
            border: 1px solid #dee2e6;
        }

        .overall-progress {
            height: 10px;
            background: linear-gradient(90deg, #e9ecef 0%, #f8f9fa 100%);
            border-radius: 20px;
            overflow: hidden;
            position: relative;
        }

        .overall-progress .progress-bar {
            background: linear-gradient(90deg, #3F51B5 0%, #28a745 50%, #17a2b8 100%);
            border-radius: 20px;
            box-shadow: 0 2px 10px rgba(63, 81, 181, 0.3);
            position: relative;
            overflow: hidden;
            transition: width 0.5s ease;
        }

        .step-progress-mini {
            width: 100%;
            height: 4px;
            background: #e9ecef;
            border-radius: 10px;
            margin-top: 5px;
            overflow: hidden;
        }

        .mini-progress-bar {
            height: 100%;
            background: linear-gradient(90deg, #3F51B5, #303F9F);
            border-radius: 10px;
            transition: all 0.5s ease;
        }

        .step-indicator {
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            z-index: 2;
            transition: all 0.3s ease;
        }

        .step-circle {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: #e9ecef;
            color: #6c757d;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            border: 3px solid #e9ecef;
        }

        .step-indicator.active .step-circle {
            background: linear-gradient(135deg, #3F51B5 0%, #303F9F 100%);
            color: white;
            border-color: #3F51B5;
            transform: scale(1.1);
            box-shadow: 0 8px 25px rgba(63, 81, 181, 0.4);
        }

        .step-indicator.completed .step-circle {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border-color: #28a745;
            color: white;
            transform: scale(1.05);
            box-shadow: 0 6px 20px rgba(40, 167, 69, 0.3);
        }

        .celebration {
            animation: celebrate 0.6s ease;
        }

        @keyframes celebrate {
            0%, 100% { transform: scale(1); }
            25% { transform: scale(1.1) rotate(5deg); }
            75% { transform: scale(1.1) rotate(-5deg); }
        }

        .step-label {
            margin-top: 8px;
            font-size: 0.85rem;
            color: #6c757d;
            font-weight: 500;
            text-align: center;
            transition: all 0.3s ease;
        }

        .step-indicator.active .step-label {
            color: #3F51B5;
            font-weight: 700;
            transform: scale(1.05);
        }

        .step-indicator.completed .step-label {
            color: #28a745;
            font-weight: 600;
        }

        .field-counter {
            font-size: 0.9rem;
            margin-top: 0.5rem;
            padding: 0.5rem 1rem;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 20px;
            border: 1px solid #dee2e6;
            transition: all 0.3s ease;
        }

        .enhanced-input-group {
            position: relative;
            margin-bottom: 0.5rem;
        }

        .enhanced-input-group.focused {
            transform: translateY(-1px);
        }

        .input-feedback {
            position: absolute;
            top: 50%;
            right: 10px;
            transform: translateY(-50%);
            opacity: 0;
            transition: all 0.3s ease;
            pointer-events: none;
        }

        .input-feedback.show {
            opacity: 1;
        }

        .form-control-wizard, .form-select {
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 0.75rem 1rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            font-size: 0.95rem;
            background: rgba(255, 255, 255, 0.8);
        }

        .form-control-wizard:focus, .form-select:focus {
            border-color: #3F51B5;
            box-shadow: 0 0 0 0.25rem rgba(63, 81, 181, 0.15);
            transform: translateY(-2px);
            background: rgba(255, 255, 255, 1);
        }

        .form-control-wizard.is-valid, .form-select.is-valid {
            border-color: #28a745;
            box-shadow: 0 0 0 0.25rem rgba(40, 167, 69, 0.15);
            background: rgba(40, 167, 69, 0.05);
        }

        .form-control-wizard.is-invalid, .form-select.is-invalid {
            border-color: #dc3545;
            box-shadow: 0 0 0 0.25rem rgba(220, 53, 69, 0.15);
            background: rgba(220, 53, 69, 0.05);
        }

        .auto-filled {
            animation: autoFillPulse 0.5s ease;
            background: rgba(63, 81, 181, 0.1) !important;
        }

        @keyframes autoFillPulse {
            0%, 100% { background: rgba(63, 81, 181, 0.1); }
            50% { background: rgba(63, 81, 181, 0.2); }
        }

        .password-strength {
            margin-top: 0.5rem;
        }

        .strength-meter {
            height: 6px;
            background: #e9ecef;
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 0.25rem;
        }

        .strength-bar {
            height: 100%;
            width: 0%;
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .strength-bar.weak { background: linear-gradient(90deg, #dc3545, #fd7e14); }
        .strength-bar.fair { background: linear-gradient(90deg, #ffc107, #fd7e14); }
        .strength-bar.good { background: linear-gradient(90deg, #28a745, #20c997); }
        .strength-bar.strong { background: linear-gradient(90deg, #17a2b8, #6610f2); }

        .strength-text {
            font-size: 0.8rem;
            color: #6c757d;
            transition: all 0.3s ease;
        }

        .btn {
            border-radius: 12px;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: inline-flex;
            align-items: center;
            position: relative;
            overflow: hidden;
        }

        .animate-on-hover {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .animate-on-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        }

        .btn-reniec {
            border-radius: 0 12px 12px 0;
            border: 2px solid #3F51B5;
            border-left: none;
            padding: 0.75rem 1rem;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        }

        .btn-reniec:hover {
            background: linear-gradient(135deg, #3F51B5 0%, #303F9F 100%);
            color: white;
        }

        .btn-reniec.loading {
            background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
            color: white;
        }

        .btn-reniec.success-animation {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            animation: successPulse 1s ease;
        }

        @keyframes successPulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        .mini-dots {
            display: flex;
            gap: 0.25rem;
        }

        .mini-dots .dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #dee2e6;
            transition: all 0.3s ease;
        }

        .mini-dots .dot.active {
            background: #3F51B5;
            transform: scale(1.2);
        }

        .shake {
            animation: shake 0.5s ease-in-out;
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }

        .wizard-step {
            min-height: 450px;
            padding: 1rem 0;
        }

        .step-header { margin-bottom: 2rem; }

        .step-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, #3F51B5 0%, #303F9F 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            color: white;
            box-shadow: 0 10px 30px rgba(63, 81, 181, 0.3);
        }

        .step-title {
            color: #2c3e50;
            font-weight: 700;
            margin-bottom: 0.5rem;
            font-size: 1.5rem;
        }

        .step-subtitle {
            color: #6c757d;
            font-size: 1rem;
        }

        .confirmation-container {
            max-height: 400px;
            overflow-y: auto;
            padding-right: 10px;
        }

        .confirmation-section {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            border: 1px solid #dee2e6;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            position: relative;
            overflow: hidden;
        }

        .confirmation-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: linear-gradient(135deg, #3F51B5 0%, #28a745 100%);
        }

        .confirmation-title {
            color: #3F51B5;
            font-weight: 600;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1.1rem;
        }

        .confirmation-data { display: grid; gap: 0.75rem; }

        .data-row {
            display: grid;
            grid-template-columns: 1fr 1.5fr;
            gap: 1rem;
            padding: 0.75rem 1rem;
            background: rgba(255, 255, 255, 0.8);
            border-radius: 10px;
            border-left: 4px solid #3F51B5;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            backdrop-filter: blur(5px);
        }

        .data-row:hover {
            transform: translateX(8px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            background: rgba(255, 255, 255, 1);
        }

        .data-label {
            font-weight: 600;
            color: #495057;
            font-size: 0.9rem;
        }

        .data-value {
            color: #6c757d;
            font-size: 0.9rem;
            word-break: break-word;
        }

        .animated-card {
            animation: fadeInUp 0.6s ease-out;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .card::-webkit-scrollbar, .confirmation-container::-webkit-scrollbar {
            width: 8px;
        }
        
        .card::-webkit-scrollbar-track, .confirmation-container::-webkit-scrollbar-track {
            background: linear-gradient(135deg, #f1f1f1 0%, #e9ecef 100%);
            border-radius: 10px;
        }
        
        .card::-webkit-scrollbar-thumb, .confirmation-container::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, #3F51B5, #28a745);
            border-radius: 10px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .card::-webkit-scrollbar-thumb:hover, .confirmation-container::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(135deg, #303F9F, #1e7e34);
        }

        /* Personalización de SweetAlert2 */
        .swal2-popup {
            border-radius: 15px !important;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15) !important;
        }

        .swal2-title {
            color: #2c3e50 !important;
            font-weight: 600 !important;
        }

        .swal2-confirm {
            background-color: #3F51B5 !important;
            border-radius: 8px !important;
            padding: 10px 25px !important;
            font-weight: 600 !important;
        }

        .swal2-cancel {
            background-color: #6c757d !important;
            border-radius: 8px !important;
            padding: 10px 25px !important;
            font-weight: 600 !important;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .card-body { padding: 1.5rem !important; }
            .step-label { font-size: 0.75rem; }

            .step-circle {
                width: 40px;
                height: 40px;
                font-size: 0.9rem;
            }

            .step-icon {
                width: 60px;
                height: 60px;
            }

            .step-title { font-size: 1.2rem; }

            .data-row {
                grid-template-columns: 1fr;
                gap: 0.5rem;
            }

            .overall-progress-container { padding: 0.75rem; }
        }

        @media (max-width: 576px) {
            .wizard-progress .d-flex { flex-wrap: wrap; }
            .step-indicator {
                flex: 1;
                min-width: 80px;
            }
            .progress-line { display: none; }
        }
    </style>
@endsection