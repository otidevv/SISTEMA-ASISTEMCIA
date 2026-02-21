@extends('layouts.auth')

@section('title', 'Acceso al Sistema - CEPRE UNAMAD')

@section('content')
    <!-- Fondo premium institucional -->
    <div class="login-bg">
        <div class="login-bg-img" style="background-image:url('{{ asset('assets/images/login/login.jpg') }}')"></div>
        <div class="login-bg-overlay"></div>
        <canvas id="login-particles"></canvas>
    </div>

    <!-- Wrapper principal -->
    <div class="login-wrapper">
        <!-- Panel izquierdo: branding (solo desktop) -->
        <div class="login-brand-panel" style="background-image: linear-gradient(160deg, rgba(11, 31, 58, 0.85) 0%, rgba(46, 125, 181, 0.7) 100%), url('{{ asset('assets/images/login/edificio_panel.png') }}'); background-size: cover; background-position: center;">
            <div class="brand-content">
                <img src="{{ asset('assets/images/logo cepre.png') }}" alt="CEPRE UNAMAD" class="brand-logo">
                <h1 class="brand-title">CEPRE <span>UNAMAD</span></h1>
                <p class="brand-sub">Centro Pre-Universitario de la<br>Universidad Nacional Madre de Dios</p>
                <div class="brand-stats">
                    <div class="stat">
                        <strong>+500</strong>
                        <span>Ingresantes</span>
                    </div>
                    <div class="stat">
                        <strong>15+</strong>
                        <span>Años</span>
                    </div>
                    <div class="stat">
                        <strong>12</strong>
                        <span>Carreras</span>
                    </div>
                </div>
                <a href="{{ route('home') }}" class="brand-back">
                    <i class="fas fa-arrow-left"></i> Volver al inicio
                </a>
            </div>
        </div>

        <!-- Panel derecho: formulario -->
        <div class="login-form-panel">
            <div class="login-card animated-card">
                        <div class="card-body p-4 p-sm-5">
                            <!-- Link volver (solo móvil) -->
                            <a href="{{ route('home') }}" class="mobile-back">
                                <i class="fas fa-arrow-left"></i> Volver al Inicio
                            </a>
                            <div class="text-center mb-0" style="margin-top: -35px;">
                                <a href="{{ route('home') }}" class="d-inline-block">
                                    <img src="{{ asset('assets/images/logo cepre.png') }}" alt="CEPRE UNAMAD"
                                         height="90" class="img-fluid" style="margin-bottom: -35px;" />
                                </a>

                                <!-- Tabs para Login y Registro -->
                                <!--<ul class="nav nav-pills nav-justified mb-3" id="authTabs" role="tablist">
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
                                </ul>-->
                            </div>

                            <!-- Alertas de Error de Laravel -->
                            @if ($errors->any())
                                <div class="alert alert-danger border-0 rounded-3 mb-3 animate__animated animate__fadeInDown" role="alert">
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
                                    <h2 class="fw-bold text-dark mt-0 mb-1 fs-2 text-center">Bienvenido</h2>
                                    <p class="text-muted mb-3 text-center">Inicia sesión en tu cuenta para continuar</p>

                                    <!-- Formulario de Login -->
                                    <form method="POST" action="{{ route('login') }}" class="needs-validation" novalidate>
                                        @csrf

                                        <!-- Campo Correo Electrónico -->
                                        <div class="mb-3">
                                            <label for="email" class="form-label fw-semibold text-dark">
                                                Correo Electrónico o DNI
                                            </label>
                                            <div class="input-group has-validation">
                                                <span class="input-group-text bg-light border-end-0 rounded-start-pill">
                                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                                        <polyline points="22,6 12,13 2,6"></polyline>
                                                    </svg>
                                                </span>
                                                <input type="text"
                                                       class="form-control border-start-0 ps-0 @error('email') is-invalid @enderror"
                                                       name="email" id="email"
                                                       value="{{ old('email') }}"
                                                       placeholder="Ej: correo@ejemplo.com o DNI"
                                                       required autofocus>
                                            </div>
                                        </div>

                                        <!-- Campo Contraseña -->
                                        <div class="mb-3">
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
                                        <div class="mb-3 form-check">
                                            <input type="checkbox" class="form-check-input" name="remember"
                                                   id="remember" {{ old('remember') ? 'checked' : '' }}>
                                            <label class="form-check-label text-muted" for="remember">
                                                Mantener sesión iniciada
                                            </label>
                                        </div>

                                        <!-- BotÃ³n de EnvÃ­o -->
                                        <div class="d-grid mb-3">
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
                                        <div class="wizard-progress mb-3">
                                            <!-- Progreso General -->
                                            <div class="overall-progress-container mb-3">
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <span class="fw-semibold text-primary">Progreso del Registro</span>
                                                    <div class="progress-percentage">
                                                        <span id="overallPercentage" class="fw-bold fs-5 text-primary">0%</span>
                                                        <small class="text-muted ms-1">completado</small>
                                                    </div>
                                                </div>
                                                <div class="progress overall-progress">
                                                    <div class="progress-bar progress-bar-striped progress-bar-animated bg-gradient"
                                                         style="width: 0%;" id="overallProgressBar"></div>
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
                                                    <span class="step-label">Postulante</span>
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
                                                    <span class="step-label">Padres/Tutores</span>
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
                                                    <span class="step-label">Confirmación</span>
                                                    <div class="mini-progress-bar" data-step="3" style="width: 0%;"></div>
                                                </div>
                                            </div>
                                            <div class="progress">
                                                <div class="progress-bar bg-gradient" style="width: 0%;"></div>
                                            </div>
                                            <div class="text-center mt-2">
                                                <small class="text-muted">Paso <span id="currentStep">1</span> de 3</small>
                                            </div>
                                        </div>

                                        <!-- Formulario de Registro -->
                                        <form id="registrationWizard" method="POST" action="{{ route('register.postulante') }}" class="needs-validation" novalidate>
                                            @csrf

                                            <!-- Step 1: Datos Personales del Postulante -->
                                            <div class="wizard-step active" data-step="1">
                                                <div class="step-header text-center mb-3">
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
                                                    <div class="col-md-6 mb-2">
                                                        <label for="reg_tipo_documento" class="form-label">Tipo Documento <span class="text-danger">*</span></label>
                                                        <div class="enhanced-input-group">
                                                            <select class="form-select form-control-wizard" id="reg_tipo_documento" name="tipo_documento" required>
                                                                <option value="">Seleccione...</option>
                                                                <option value="DNI" {{ old('tipo_documento') == 'DNI' ? 'selected' : '' }}>DNI</option>
                                                                <option value="CE" {{ old('tipo_documento') == 'CE' ? 'selected' : '' }}>Carnet de ExtranjerÃ­a</option>
                                                            </select>
                                                            <div class="input-feedback"></div>
                                                        </div>
                                                        <div class="invalid-feedback">Seleccione el tipo de documento</div>
                                                    </div>
                                                    <div class="col-md-6 mb-2">
                                                        <label for="reg_numero_documento" class="form-label glow-text">Número Documento <span class="text-danger">*</span></label>
                                                        <div class="input-group enhanced-input-group glow-on-focus">
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
                                                        <small class="form-text text-muted d-block mt-2">
                                                            Ingresa tu DNI y haz clic en la lupa para autocompletar tus datos. ðŸš€
                                                        </small>
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="col-md-6 mb-2">
                                                        <label for="reg_nombre" class="form-label">Nombres <span class="text-danger">*</span></label>
                                                        <div class="enhanced-input-group">
                                                            <input type="text" class="form-control form-control-wizard" id="reg_nombre" name="nombre"
                                                                   value="{{ old('nombre') }}" required>
                                                            <div class="input-feedback"></div>
                                                        </div>
                                                        <div class="invalid-feedback">Ingrese los nombres</div>
                                                    </div>
                                                    <div class="col-md-6 mb-2">
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
                                                    <div class="col-md-6 mb-2">
                                                        <label for="reg_apellido_materno" class="form-label">Apellido Materno <span class="text-danger">*</span></label>
                                                        <div class="enhanced-input-group">
                                                            <input type="text" class="form-control form-control-wizard" id="reg_apellido_materno"
                                                                   name="apellido_materno" value="{{ old('apellido_materno') }}" required>
                                                            <div class="input-feedback"></div>
                                                        </div>
                                                        <div class="invalid-feedback">Ingrese el apellido materno</div>
                                                    </div>
                                                    <div class="col-md-6 mb-2">
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
                                                    <div class="col-md-6 mb-2">
                                                        <label for="reg_genero" class="form-label">GÃ©nero <span class="text-danger">*</span></label>
                                                        <div class="enhanced-input-group">
                                                            <select class="form-select form-control-wizard" id="reg_genero" name="genero" required>
                                                                <option value="">Seleccione...</option>
                                                                <option value="M" {{ old('genero') == 'M' ? 'selected' : '' }}>Masculino</option>
                                                                <option value="F" {{ old('genero') == 'F' ? 'selected' : '' }}>Femenino</option>
                                                            </select>
                                                            <div class="input-feedback"></div>
                                                        </div>
                                                        <div class="invalid-feedback">Seleccione el gÃ©nero</div>
                                                    </div>
                                                    <div class="col-md-6 mb-2">
                                                        <label for="reg_telefono" class="form-label">TelÃ©fono/Celular <span class="text-danger">*</span></label>
                                                        <div class="enhanced-input-group">
                                                            <input type="tel" class="form-control form-control-wizard" id="reg_telefono"
                                                                   name="telefono" value="{{ old('telefono') }}"
                                                                   pattern="[0-9]{9}" maxlength="9" required placeholder="">
                                                            <div class="input-feedback"></div>
                                                        </div>
                                                        <div class="invalid-feedback">Ingrese un telÃ©fono válido (9 dÃ­gitos)</div>
                                                    </div>
                                                </div>

                                                <div class="mb-2">
                                                    <label for="reg_direccion" class="form-label">DirecciÃ³n <span class="text-danger">*</span></label>
                                                    <div class="enhanced-input-group">
                                                        <input type="text" class="form-control form-control-wizard" id="reg_direccion"
                                                               name="direccion" value="{{ old('direccion') }}" required>
                                                        <div class="input-feedback"></div>
                                                    </div>
                                                    <div class="invalid-feedback">Ingrese la direcciÃ³n</div>
                                                </div>

                                                <div class="row">
                                                    <div class="col-md-6 mb-2">
                                                        <label for="reg_email" class="form-label">Correo Electrónico <span class="text-danger">*</span></label>
                                                        <div class="enhanced-input-group">
                                                            <input type="email" class="form-control form-control-wizard" id="reg_email"
                                                                   name="email" value="{{ old('email') }}" required>
                                                            <div class="input-feedback"></div>
                                                        </div>
                                                        <div class="invalid-feedback">Ingrese un correo válido</div>
                                                    </div>
                                                    <div class="col-md-6 mb-2">
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

                                                <div class="mb-2">
                                                    <label for="reg_password_confirmation" class="form-label">Confirmar Contraseña <span class="text-danger">*</span></label>
                                                    <div class="enhanced-input-group">
                                                        <input type="password" class="form-control form-control-wizard" id="reg_password_confirmation"
                                                               name="password_confirmation" minlength="8" required>
                                                        <div class="input-feedback"></div>
                                                    </div>
                                                    <div class="invalid-feedback">Las contraseñas no coinciden</div>
                                                </div>
                                            </div>

                                            <!-- Step 2: Datos de Padres/Tutores (Unificado) -->
                                            <div class="wizard-step" data-step="2" style="display: none;">
                                                <div class="step-header text-center mb-3">
                                                    <div class="step-icon">
                                                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                            <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                                            <circle cx="8.5" cy="7" r="4"></circle>
                                                            <path d="M20 21v-2a4 4 0 0 0-4-4H13"></path>
                                                            <circle cx="17.5" cy="7" r="4"></circle>
                                                        </svg>
                                                    </div>
                                                    <h4 class="step-title">Datos de Padres/Tutores</h4>
                                                    <p class="step-subtitle">Información del padre y/o la madre</p>
                                                    <div class="field-counter">
                                                        <span id="step2Counter">0 de 12 campos completados</span>
                                                    </div>
                                                </div>

                                                <!-- SecciÃ³n Padre/Tutor -->
                                                <h5 class="fw-semibold text-dark mb-2">Datos del Padre/Tutor</h5>
                                                <div class="row">
                                                    <div class="col-md-6 mb-2">
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
                                                    <div class="col-md-6 mb-2">
                                                        <label for="padre_numero_doc" class="form-label glow-text">Número Doc. <span class="text-danger">*</span></label>
                                                        <div class="input-group enhanced-input-group glow-on-focus">
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
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-6 mb-2">
                                                        <label for="padre_nombre" class="form-label">Nombres <span class="text-danger">*</span></label>
                                                        <div class="enhanced-input-group">
                                                            <input type="text" class="form-control form-control-wizard" id="padre_nombre"
                                                                   name="padre_nombre" value="{{ old('padre_nombre') }}" required>
                                                            <div class="input-feedback"></div>
                                                        </div>
                                                        <div class="invalid-feedback">Ingrese los nombres del padre</div>
                                                    </div>
                                                    <div class="col-md-6 mb-2">
                                                        <label for="padre_apellidos" class="form-label">Apellidos <span class="text-danger">*</span></label>
                                                        <div class="enhanced-input-group">
                                                            <input type="text" class="form-control form-control-wizard" id="padre_apellidos"
                                                                   name="padre_apellidos" value="{{ old('padre_apellidos') }}" required>
                                                            <div class="input-feedback"></div>
                                                        </div>
                                                        <div class="invalid-feedback">Ingrese los apellidos del padre</div>
                                                    </div>
                                                </div>
                                                <div class="mb-2">
                                                    <label for="padre_telefono" class="form-label">TelÃ©fono <span class="text-danger">*</span></label>
                                                    <div class="enhanced-input-group">
                                                        <input type="tel" class="form-control form-control-wizard" id="padre_telefono"
                                                               name="padre_telefono" value="{{ old('padre_telefono') }}"
                                                               pattern="[0-9]{9}" maxlength="9" required>
                                                        <div class="input-feedback"></div>
                                                    </div>
                                                    <div class="invalid-feedback">Ingrese un telÃ©fono válido</div>
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

                                                <hr class="my-3">

                                                <!-- SecciÃ³n Madre/Tutora -->
                                                <h5 class="fw-semibold text-dark mb-2">Datos de la Madre/Tutora</h5>
                                                <div class="row">
                                                    <div class="col-md-6 mb-2">
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
                                                    <div class="col-md-6 mb-2">
                                                        <label for="madre_numero_doc" class="form-label glow-text">Número Doc. <span class="text-danger">*</span></label>
                                                        <div class="input-group enhanced-input-group glow-on-focus">
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
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-6 mb-2">
                                                        <label for="madre_nombre" class="form-label">Nombres <span class="text-danger">*</span></label>
                                                        <div class="enhanced-input-group">
                                                            <input type="text" class="form-control form-control-wizard" id="madre_nombre"
                                                                   name="madre_nombre" value="{{ old('madre_nombre') }}" required>
                                                            <div class="input-feedback"></div>
                                                        </div>
                                                        <div class="invalid-feedback">Ingrese los nombres de la madre</div>
                                                    </div>
                                                    <div class="col-md-6 mb-2">
                                                        <label for="madre_apellidos" class="form-label">Apellidos <span class="text-danger">*</span></label>
                                                        <div class="enhanced-input-group">
                                                            <input type="text" class="form-control form-control-wizard" id="madre_apellidos"
                                                                   name="madre_apellidos" value="{{ old('madre_apellidos') }}" required>
                                                            <div class="input-feedback"></div>
                                                        </div>
                                                        <div class="invalid-feedback">Ingrese los apellidos de la madre</div>
                                                    </div>
                                                </div>
                                                <div class="mb-2">
                                                    <label for="madre_telefono" class="form-label">TelÃ©fono <span class="text-danger">*</span></label>
                                                    <div class="enhanced-input-group">
                                                        <input type="tel" class="form-control form-control-wizard" id="madre_telefono"
                                                               name="madre_telefono" value="{{ old('madre_telefono') }}"
                                                               pattern="[0-9]{9}" maxlength="9" required>
                                                        <div class="input-feedback"></div>
                                                    </div>
                                                    <div class="invalid-feedback">Ingrese un telÃ©fono válido</div>
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

                                            <!-- Step 3: Confirmación -->
                                            <div class="wizard-step" data-step="3" style="display: none;">
                                                <div class="step-header text-center mb-3">
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
                                                    <!-- Resumen serÃ¡ generado por JavaScript -->
                                                </div>

                                                <div class="form-check mb-3">
                                                    <input class="form-check-input" type="checkbox" id="terms" name="terms" required>
                                                    <label class="form-check-label" for="terms">
                                                        Acepto los <a href="#" class="text-primary">términos y condiciones</a> y la
                                                        <a href="#" class="text-primary">polÃ­tica de privacidad</a>
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
                                                    </div>
                                                </div>
                                                <span id="stepCounter">Paso 1 de 3</span>
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
                            <div class="text-center mt-3">
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
            </div><!-- /.login-card -->
        </div><!-- /.login-form-panel -->
    </div><!-- /.login-wrapper -->

    <!-- SweetAlert2 CDN -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>

    <script>
        // Variables globales
        const csrfToken = '{{ csrf_token() }}';

        // Variables del wizard
        let wizardCurrentStep = 1;
        const wizardTotalSteps = 3; 
        let wizardFormData = {};

        // Validación del Login con SweetAlert2
        document.addEventListener('DOMContentLoaded', function() {
            const loginForm = document.querySelector('form[action="{{ route('login') }}"]');
            if (loginForm) {
                loginForm.addEventListener('submit', function(event) {
                    const email = document.getElementById('email');
                    const password = document.getElementById('password');
                    
                    if (!email.value.trim() || !password.value.trim()) {
                        event.preventDefault();
                        event.stopPropagation();
                        
                        Swal.fire({
                            icon: 'warning',
                            title: 'Campos Vacíos',
                            text: 'Por favor, ingresa tu correo/DNI y contraseña para continuar.',
                            confirmButtonColor: '#00AEEF',
                            background: '#0f1d35',
                            color: '#ffffff'
                        });
                        
                        loginForm.classList.add('was-validated');
                    }
                });
            }
        });

        // Variables para el progreso
        let fieldCounts = {
            1: { total: 10, completed: 0 },
            2: { total: 12, completed: 0 }, // Unificado
            3: { total: 1, completed: 0 }
        };

        // FunciÃ³n para mostrar/ocultar contraseña
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

        // FunciÃ³n para consultar DNI en RENIEC MEJORADA CON SWEETALERT2
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
                    text: 'El DNI debe tener exactamente 8 dÃ­gitos numÃ©ricos',
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

                    if (wizardCurrentStep === 3) { // Actualizado a paso 3
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
            
            // Corrige la visualizaciÃ³n del contador de pasos
            stepCounter.textContent = `Paso ${wizardCurrentStep} de ${wizardTotalSteps}`;

            prevBtn.style.display = wizardCurrentStep === 1 ? 'none' : 'flex';

            document.querySelectorAll('.mini-dots .dot').forEach((dot, index) => {
                dot.classList.toggle('active', index + 1 <= wizardCurrentStep);
            });

            if (wizardCurrentStep === wizardTotalSteps) {
                nextBtn.querySelector('.btn-text').textContent = 'Registrar PostulaciÃ³n';
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
                feedback = 'Contraseña dÃ©bil';
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
                    feedback.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>';
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

            if (step === 2 && !validateParentEmails()) {
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

        // FUNCIÃ“N SAVECURRENTSTEPDATA CORREGIDA
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

            // CORRECCIÃ“N: Obtener datos directamente del formulario, no de wizardFormData
            const form = document.getElementById('registrationWizard');
            const formData = new FormData(form);

            const sections = [
                {
                    title: 'Datos Personales del Postulante',
                    icon: 'ðŸ‘¤',
                    data: {
                        'Nombres': formData.get('nombre'),
                        'Apellido Paterno': formData.get('apellido_paterno'),
                        'Apellido Materno': formData.get('apellido_materno'),
                        'Tipo de Documento': formData.get('tipo_documento'),
                        'Número de Documento': formData.get('numero_documento'),
                        'Fecha de Nacimiento': formData.get('fecha_nacimiento'),
                        'GÃ©nero': formData.get('genero') === 'M' ? 'Masculino' : 'Femenino',
                        'TelÃ©fono': formData.get('telefono'),
                        'DirecciÃ³n': formData.get('direccion'),
                        'Correo Electrónico': formData.get('email')
                    }
                },
                {
                    title: 'Datos de los Padres/Tutores',
                    icon: 'ðŸ‘¨â€ðŸ‘©â€ðŸ‘§',
                    data: {
                        'Nombres del Padre': formData.get('padre_nombre'),
                        'Apellidos del Padre': formData.get('padre_apellidos'),
                        'Documento del Padre': formData.get('padre_tipo_documento') + ': ' + formData.get('padre_numero_documento'),
                        'TelÃ©fono del Padre': formData.get('padre_telefono'),
                        'Correo del Padre': formData.get('padre_email') || 'No proporcionado',
                        'Nombres de la Madre': formData.get('madre_nombre'),
                        'Apellidos de la Madre': formData.get('madre_apellidos'),
                        'Documento de la Madre': formData.get('madre_tipo_documento') + ': ' + formData.get('madre_numero_documento'),
                        'TelÃ©fono de la Madre': formData.get('madre_telefono'),
                        'Correo de la Madre': formData.get('madre_email') || 'No proporcionado'
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

        // FUNCIÃ“N SUBMITWIZARDFORM CORREGIDA
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

            // CORRECCIÃ“N: Obtener datos directamente del formulario completo
            const form = document.getElementById('registrationWizard');
            const formData = new FormData(form);

            // Asegurar que términos estÃ© incluido
            if (termsCheckbox.checked) {
                formData.append('terms', 'on');
            }

            const email = formData.get('email');

            // Mostrar confirmación con SweetAlert2
            const result = await Swal.fire({
                title: 'Confirmar Registro',
                html: `
                    <div class="text-start">
                        <p><strong>Se enviarÃ¡ un correo de confirmación a:</strong></p>
                        <p class="text-primary fs-5">${email}</p>
                        <p><small class="text-muted">Su cuenta se crearÃ¡ con estado PENDIENTE hasta que verifique su correo electrónico.</small></p>
                    </div>
                `,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3F51B5',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'SÃ­, registrar',
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

                // CORRECCIÃ“N: Usar la misma estructura que funciona
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
                                <p><small>Hijos registrados: ${data.data.madre.hijos_registrados}</small></p>
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

        // Eventos de inicializaciÃ³n
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

        // ValidaciÃ³n de formulario con Bootstrap
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

    <!-- FA Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <!-- Estilos CSS PREMIUM -->
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

        /* ====== VARIABLES INSTITUCIONALES ====== */
        :root {
            --azul-oscuro:   #2E7DB5;
            --azul-medio:    #1a4b6d;
            --verde-cepre:   #8DC63F;
            --cyan-acento:   #00AEEF;
            --magenta:       #EC008C;
            --texto-claro:   rgba(255,255,255,0.85);
        }

        body {
            font-family: 'Inter', sans-serif;
            margin: 0;
            overflow-x: hidden;
            background: var(--azul-oscuro);
        }

        /* ====== FONDO ====== */
        .login-bg {
            position: fixed;
            inset: 0;
            z-index: 0;
            overflow: hidden;
        }
        .login-bg-img {
            position: absolute;
            inset: 0;
            background-size: cover;
            background-position: center;
            filter: blur(4px) brightness(0.6);
            transform: scale(1.05);
        }
        .login-bg-overlay {
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at center, rgba(46, 125, 181, 0.4) 0%, rgba(11, 31, 58, 0.85) 100%);
        }
        #login-particles {
            position: absolute;
            inset: 0;
            pointer-events: none;
        }

        /* ====== WRAPPER ====== */
        .login-wrapper {
            position: relative;
            z-index: 1;
            min-height: 100vh;
            display: flex;
            align-items: stretch;
        }

        /* ====== PANEL IZQUIERDO (Branding) ====== */
        .login-brand-panel {
            display: none;
            flex: 0 0 450px;
            border-right: 1px solid rgba(255,255,255,0.1);
            align-items: center;
            justify-content: center;
            padding: 48px 40px;
            position: relative;
            overflow: hidden;
        }
        .login-brand-panel::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 150px;
            background: linear-gradient(to top, rgba(46, 125, 181, 0.9), transparent);
            pointer-events: none;
        }
        @media (min-width: 992px) {
            .login-brand-panel { display: flex; }
        }
        .brand-content { text-align: center; }
        .brand-logo { width: 100px; margin-bottom: 20px; }
        .brand-title {
            font-size: 2rem;
            font-weight: 800;
            color: white;
            margin: 0 0 6px;
            letter-spacing: -0.5px;
        }
        .brand-title span { color: var(--cyan-acento); }
        .brand-sub {
            font-size: 0.88rem;
            color: var(--texto-claro);
            line-height: 1.6;
            margin-bottom: 36px;
        }
        .brand-stats {
            display: flex;
            gap: 24px;
            justify-content: center;
            margin-bottom: 40px;
        }
        .brand-stats .stat {
            text-align: center;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 12px;
            padding: 14px 18px;
        }
        .brand-stats .stat strong {
            display: block;
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--cyan-acento);
        }
        .brand-stats .stat span {
            font-size: 0.78rem;
            color: var(--texto-claro);
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }
        .brand-back {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: rgba(255,255,255,0.5);
            text-decoration: none;
            font-size: 0.85rem;
            transition: color 0.3s;
        }
        .brand-back:hover { color: var(--cyan-acento); }

        /* ====== PANEL DERECHO (Formulario) ====== */
        .login-form-panel {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
            overflow-y: auto;
        }
        .login-card {
            width: 100%;
            max-width: 540px;
            background: rgba(15,29,53,0.75);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: 20px;
            border: 1px solid rgba(255,255,255,0.1);
            border-top: 3px solid var(--verde-cepre);
            box-shadow: 0 25px 60px rgba(0,0,0,0.5);
            padding: 32px 36px;
            max-height: 95vh;
            overflow-y: auto;
        }
        @media (max-width: 576px) {
            .login-card { padding: 24px 18px; border-radius: 16px; }
        }

        /* Mobile: back link shown inside card */
        /* Mobile: back link shown inside card */
        .mobile-back {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--cyan-acento);
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 20px;
            transition: all 0.3s ease;
            padding: 8px 12px;
            background: rgba(0, 174, 239, 0.1);
            border-radius: 8px;
            width: fit-content;
        }
        .mobile-back:hover { 
            background: var(--cyan-acento);
            color: white;
            transform: translateX(-5px);
        }
        @media (min-width: 992px) { .mobile-back { display: none; } }

        /* ====== TIPOGRAFÃA ====== */
        h2.fw-bold { color: white !important; }
        p.text-muted { color: rgba(255,255,255,0.5) !important; }
        .form-label { color: rgba(255,255,255,0.75) !important; font-size: 0.88rem; }
        .form-label.fw-semibold { font-weight: 600 !important; }

        /* ====== LOGO (mobile only) ====== */
        .login-card-logo {
            text-align: center;
            margin-bottom: 10px;
        }
        .login-card-logo img { width: 40px; }
        @media (min-width: 992px) { .login-card-logo { display: none; } }

        /* ====== INPUTS ====== */
        .input-group-text {
            background: rgba(255,255,255,0.06) !important;
            border-color: rgba(255,255,255,0.12) !important;
            color: rgba(255,255,255,0.5) !important;
        }
        .form-control, .form-select {
            background: rgba(255,255,255,0.07) !important;
            border-color: rgba(255,255,255,0.12) !important;
            color: white !important;
            border-radius: 10px;
            transition: all 0.3s ease;
        }
        .form-control::placeholder { color: rgba(255,255,255,0.3) !important; }
        .form-control:focus, .form-select:focus {
            background: rgba(255,255,255,0.12) !important;
            border-color: var(--cyan-acento) !important;
            box-shadow: 0 0 0 3px rgba(0,174,239,0.2) !important;
            color: white !important;
        }
        .form-select option { background: #0b1f3a; color: white; }
        .form-check-input {
            background-color: rgba(255,255,255,0.1) !important;
            border-color: rgba(255,255,255,0.2) !important;
        }
        .form-check-input:checked { background-color: var(--cyan-acento) !important; border-color: var(--cyan-acento) !important; }
        .form-check-label { color: rgba(255,255,255,0.6) !important; }
        a.text-primary, a.text-decoration-none.text-primary {
            color: var(--cyan-acento) !important;
        }
        a.text-primary:hover { color: var(--magenta) !important; }

        /* ====== BOTONES ====== */
        .btn-primary {
            background: linear-gradient(135deg, var(--cyan-acento) 0%, #007fc0 100%) !important;
            border: none !important;
            color: white !important;
            font-weight: 700;
            letter-spacing: 0.5px;
            border-radius: 12px !important;
            padding: 13px 24px !important;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,174,239,0.4) !important;
        }
        .btn-success {
            background: linear-gradient(135deg, var(--verde-cepre) 0%, #1da851 100%) !important;
            border: none !important;
            font-weight: 700;
            border-radius: 12px !important;
        }
        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(46,200,102,0.4) !important;
        }
        .btn-outline-secondary {
            border-color: rgba(255,255,255,0.2) !important;
            color: rgba(255,255,255,0.6) !important;
        }
        .btn-outline-secondary:hover {
            background: rgba(255,255,255,0.1) !important;
            color: white !important;
        }

        /* ====== ERRORES ====== */
        .alert-danger {
            background: rgba(220,53,69,0.15) !important;
            border-color: rgba(220,53,69,0.3) !important;
            color: #ff8a9b !important;
            border-radius: 12px;
        }

        /* ====== ANIMACIONES ====== */
        .animated-card { animation: fadeInUp 0.6s ease-out; }
        @keyframes fadeInUp {
            from { opacity:0; transform:translateY(30px); }
            to   { opacity:1; transform:translateY(0); }
        }
        .animate-on-hover { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        .animate-on-hover:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(0,0,0,0.3); }

        /* ====== WIZARD ====== */
        .registration-wizard { min-height: 500px; }
        .overall-progress-container {
            background: rgba(255,255,255,0.05);
            border-radius: 12px;
            padding: 1rem;
            border: 1px solid rgba(255,255,255,0.08);
        }
        .overall-progress, .progress {
            height: 8px;
            background: rgba(255,255,255,0.1);
            border-radius: 20px;
            overflow: hidden;
        }
        .progress-bar {
            background: linear-gradient(90deg, var(--cyan-acento), var(--verde-cepre));
            border-radius: 20px;
            transition: width 0.5s ease;
        }
        .step-indicator { display:flex; flex-direction:column; align-items:center; position:relative; z-index:2; transition:all 0.3s ease; }
        .step-circle {
            width: 46px; height: 46px; border-radius: 50%;
            background: rgba(255,255,255,0.08);
            color: rgba(255,255,255,0.5);
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; transition: all 0.4s cubic-bezier(0.4,0,0.2,1);
            border: 2px solid rgba(255,255,255,0.12);
        }
        .step-indicator.active .step-circle {
            background: linear-gradient(135deg, var(--cyan-acento), #007fc0);
            color: white; border-color: var(--cyan-acento);
            transform: scale(1.1); box-shadow: 0 6px 20px rgba(0,174,239,0.4);
        }
        .step-indicator.completed .step-circle {
            background: linear-gradient(135deg, var(--verde-cepre), #1da851);
            border-color: var(--verde-cepre); color: white;
            box-shadow: 0 4px 15px rgba(46,200,102,0.35);
        }
        .step-label { margin-top:6px; font-size:0.8rem; color:rgba(255,255,255,0.4); font-weight:500; text-align:center; }
        .step-indicator.active .step-label { color: var(--cyan-acento); font-weight:700; }
        .step-indicator.completed .step-label { color: var(--verde-cepre); }
        .step-progress-mini { width:100%; height:3px; background:rgba(255,255,255,0.08); border-radius:10px; margin-top:4px; overflow:hidden; }
        .mini-progress-bar { height:100%; background:linear-gradient(90deg,var(--cyan-acento),var(--verde-cepre)); border-radius:10px; transition:all 0.5s ease; }
        .progress-line { flex:1; height:2px; background:rgba(255,255,255,0.08); margin: 0 8px; margin-bottom:30px; }
        .field-counter {
            font-size:0.85rem; margin-top:0.4rem; padding:0.4rem 1rem;
            background:rgba(255,255,255,0.05); border-radius:20px;
            border:1px solid rgba(255,255,255,0.08); color:rgba(255,255,255,0.5);
        }
        .field-counter.text-success { color: var(--verde-cepre) !important; }
        .field-counter.text-warning { color: #fbbf24 !important; }
        .step-header { margin-bottom:1rem; }
        .step-icon {
            width:56px; height:56px; border-radius:50%;
            background:linear-gradient(135deg, var(--cyan-acento), #007fc0);
            display:flex; align-items:center; justify-content:center;
            margin:0 auto 0.5rem; color:white;
            box-shadow:0 8px 25px rgba(0,174,239,0.3);
        }
        .step-title { color:white; font-weight:700; margin-bottom:0.25rem; font-size:1.2rem; }
        .step-subtitle { color:rgba(255,255,255,0.45); font-size:0.88rem; }
        .wizard-step { min-height:420px; padding-top:0.5rem; }
        h5.fw-semibold { color:rgba(255,255,255,0.7) !important; }
        .border-top { border-color:rgba(255,255,255,0.08) !important; }
        .text-muted.fw-semibold { color:rgba(255,255,255,0.4) !important; }

        /* Inputs del wizard */
        .form-control-wizard, .enhanced-input-group .form-control, .enhanced-input-group .form-select {
            background: rgba(255,255,255,0.07) !important;
            border: 1.5px solid rgba(255,255,255,0.12) !important;
            color: white !important;
            border-radius: 10px !important;
            padding: 0.65rem 1rem;
            transition: all 0.3s ease;
        }
        .form-control-wizard:focus {
            border-color: var(--cyan-acento) !important;
            box-shadow: 0 0 0 3px rgba(0,174,239,0.2) !important;
            background: rgba(255,255,255,0.11) !important;
        }
        .form-control-wizard.is-valid { border-color: var(--verde-cepre) !important; }
        .form-control-wizard.is-invalid { border-color: #f87171 !important; }
        .enhanced-input-group { position:relative; margin-bottom:0.25rem; }
        .enhanced-input-group.focused { box-shadow:0 0 0 3px rgba(0,174,239,0.15); border-radius:10px; }
        .input-feedback { position:absolute; top:50%; right:10px; transform:translateY(-50%); opacity:0; transition:all 0.3s ease; pointer-events:none; }
        .input-feedback.show { opacity:1; }
        .input-feedback svg path { stroke: var(--verde-cepre); }
        .invalid-feedback { color: #f87171 !important; font-size:0.8rem; }
        .input-group.glow-on-focus:focus-within { box-shadow:0 0 0 3px rgba(0,174,239,0.2); border-radius:10px; }
        .glow-text { color: var(--cyan-acento) !important; font-weight:700 !important; }
        .auto-filled { animation: autoFillPulse 0.5s ease; }
        @keyframes autoFillPulse { 0%,100% { background:rgba(0,174,239,0.1) !important; } 50% { background:rgba(0,174,239,0.2) !important; } }

        /* BotÃ³n RENIEC */
        .btn-reniec {
            border-radius: 0 10px 10px 0 !important;
            border: 1.5px solid rgba(0,174,239,0.4) !important;
            border-left: none !important;
            background: rgba(0,174,239,0.1) !important;
            color: var(--cyan-acento) !important;
        }
        .btn-reniec:hover { background: var(--cyan-acento) !important; color: white !important; }
        .btn-reniec.loading { background: rgba(255,255,255,0.1) !important; color: white !important; }
        .btn-reniec.success-animation { background: var(--verde-cepre) !important; color: white !important; animation: successPulse 1s ease; }
        @keyframes successPulse { 0%,100%{transform:scale(1)} 50%{transform:scale(1.05)} }

        /* Password strength */
        .strength-meter { height:5px; background:rgba(255,255,255,0.1); border-radius:10px; overflow:hidden; margin-bottom:0.25rem; }
        .strength-bar { height:100%; width:0%; border-radius:10px; transition:all 0.3s ease; }
        .strength-bar.weak { background:linear-gradient(90deg,#ef4444,#f97316); }
        .strength-bar.fair { background:linear-gradient(90deg,#fbbf24,#f97316); }
        .strength-bar.good { background:linear-gradient(90deg,var(--verde-cepre),#10b981); }
        .strength-bar.strong { background:linear-gradient(90deg,var(--cyan-acento),#6366f1); }
        .strength-text { font-size:0.78rem; color:rgba(255,255,255,0.4); }

        /* Confirmación */
        .confirmation-container { max-height:380px; overflow-y:auto; padding-right:8px; }
        .confirmation-section {
            background:rgba(255,255,255,0.04); border-radius:12px;
            padding:1rem; margin-bottom:0.75rem;
            border:1px solid rgba(255,255,255,0.07);
            position:relative; overflow:hidden;
        }
        .confirmation-section::before {
            content:''; position:absolute; top:0; left:0; width:3px; height:100%;
            background:linear-gradient(180deg,var(--cyan-acento),var(--verde-cepre));
        }
        .confirmation-title { color:var(--cyan-acento); font-weight:600; margin-bottom:0.5rem; display:flex; align-items:center; gap:0.5rem; font-size:1rem; }
        .confirmation-data { display:grid; gap:0.2rem; }
        .data-row {
            display:grid; grid-template-columns:1fr 1.5fr; gap:0.5rem;
            padding:0.4rem 0.75rem; background:rgba(255,255,255,0.04);
            border-radius:8px; border-left:3px solid rgba(0,174,239,0.3);
            transition:all 0.3s ease;
        }
        .data-row:hover { transform:translateX(5px); background:rgba(255,255,255,0.07); }
        .data-label { font-weight:600; color:rgba(255,255,255,0.5); font-size:0.85rem; }
        .data-value { color:rgba(255,255,255,0.8); font-size:0.85rem; word-break:break-word; }

        /* Navigation wizard */
        .wizard-navigation { border-top:1px solid rgba(255,255,255,0.08) !important; }
        .step-info.text-muted { color:rgba(255,255,255,0.35) !important; }
        .mini-dots { display:flex; gap:0.25rem; }
        .mini-dots .dot { width:7px; height:7px; border-radius:50%; background:rgba(255,255,255,0.15); transition:all 0.3s ease; }
        .mini-dots .dot.active { background:var(--cyan-acento); transform:scale(1.2); }

        .shake { animation: shake 0.5s ease-in-out; }
        @keyframes shake { 0%,100%{transform:translateX(0)} 25%{transform:translateX(-5px)} 75%{transform:translateX(5px)} }
        .celebration { animation: celebrate 0.6s ease; }
        @keyframes celebrate { 0%,100%{transform:scale(1)} 25%{transform:scale(1.1)rotate(5deg)} 75%{transform:scale(1.1)rotate(-5deg)} }

        /* Scrollbars */
        .login-card::-webkit-scrollbar, .confirmation-container::-webkit-scrollbar { width:6px; }
        .login-card::-webkit-scrollbar-track, .confirmation-container::-webkit-scrollbar-track { background:rgba(255,255,255,0.05); border-radius:10px; }
        .login-card::-webkit-scrollbar-thumb, .confirmation-container::-webkit-scrollbar-thumb { background:linear-gradient(180deg,var(--cyan-acento),var(--verde-cepre)); border-radius:10px; }

        /* SweetAlert2 */
        .swal2-popup { border-radius:16px !important; background:#0f1d35 !important; border:1px solid rgba(255,255,255,0.1) !important; }
        .swal2-title { color:white !important; font-weight:700 !important; }
        .swal2-html-container { color:rgba(255,255,255,0.7) !important; }
        .swal2-confirm { background:linear-gradient(135deg,var(--cyan-acento),#007fc0) !important; border-radius:10px !important; font-weight:700 !important; }
        .swal2-cancel { background:rgba(255,255,255,0.1) !important; border-radius:10px !important; font-weight:600 !important; color:white !important; }

        /* ====== TÃ‰RMINOS ====== */
        a.text-primary { color:var(--cyan-acento) !important; }
        .text-success { color:var(--verde-cepre) !important; }

        /* ====== RESPONSIVE ====== */
        @media (max-width: 768px) {
            .login-card { 
                padding:8px 6px; 
                border-radius: 10px; 
                max-width: 300px; 
                margin: 0 auto;
                box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            }
            .mobile-back { margin-bottom: 8px; padding: 4px 8px; font-size: 0.7rem; }
            .login-card-logo img { width: 100px; height: auto; }
            .step-label { font-size:0.55rem; }
            .card-body { padding: 0.5rem !important; }
            h2.fw-bold { font-size: 1.1rem !important; margin-bottom: 2px !important; }
            p.text-muted { font-size: 0.7rem !important; margin-bottom: 8px !important; }
            .form-label { font-size: 0.7rem !important; margin-bottom: 1px !important; }
            .form-control, .form-select { padding: 0.3rem 0.5rem; font-size: 0.75rem; height: 32px; }
            .input-group-text { padding: 0.3rem 0.5rem; }
            .btn-primary { padding: 5px 12px !important; font-size: 0.8rem; margin-top: 5px; }
            .step-circle { width:24px; height:24px; font-size:0.65rem; }
            .mb-3 { margin-bottom: 0.5rem !important; }
            .mb-2 { margin-bottom: 0.4rem !important; }
        }
            .step-icon { width:50px; height:50px; }
            .step-title { font-size:1.1rem; }
            .data-row { grid-template-columns:1fr; }
        }
        @media (max-width: 576px) {
            .wizard-progress .d-flex { flex-wrap:wrap; }
            .step-indicator { flex:1; min-width:70px; }
            .progress-line { display:none; }
        }

        /* ====== PARTÃCULA FIN ====== */
    </style>

    <!-- PartÃ­culas de fondo (puntos flotantes) -->
    <script>
    (function(){
        const canvas = document.getElementById('login-particles');
        const ctx = canvas.getContext('2d');
        let W, H, dots = [];
        const COLORS = ['#00AEEF','#2EC866','#EC008C','#FFD700'];
        function resize(){ W=canvas.width=window.innerWidth; H=canvas.height=window.innerHeight; }
        function rnd(a,b){ return a+Math.random()*(b-a); }
        function createDot(){
            return { x:rnd(0,W), y:rnd(0,H), r:rnd(1,3), color:COLORS[Math.floor(Math.random()*COLORS.length)],
                     vx:rnd(-0.3,0.3), vy:rnd(-0.3,0.3), opacity:rnd(0.2,0.6) };
        }
        function draw(){
            ctx.clearRect(0,0,W,H);
            dots.forEach(d=>{
                ctx.beginPath();
                ctx.arc(d.x,d.y,d.r,0,Math.PI*2);
                ctx.fillStyle = d.color;
                ctx.globalAlpha = d.opacity;
                ctx.fill();
                d.x+=d.vx; d.y+=d.vy;
                if(d.x<0||d.x>W) d.vx*=-1;
                if(d.y<0||d.y>H) d.vy*=-1;
            });
            ctx.globalAlpha=1;
            requestAnimationFrame(draw);
        }
        window.addEventListener('resize',resize);
        resize();
        for(let i=0;i<80;i++) dots.push(createDot());
        draw();
    })();
    </script>
@endsection
