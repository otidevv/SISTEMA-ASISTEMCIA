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
                <div class="col-md-9 col-lg-7 col-xl-6">
                    <div class="card shadow-lg border-0 rounded-4 animated-card" style="background: rgba(255, 255, 255, 0.98);">
                        <div class="card-body p-4 p-sm-5">
                            <div class="text-center mb-5">
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

                            <!-- Alertas de Error de Laravel (si las hay) -->
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
                                                        <circle cx="12" cy="12" r="4"></circle>
                                                        <path d="M16 8v5a3 3 0 0 0 6 0v-5a10 10 0 1 0-20 0v5a3 3 0 0 0 6 0v-5"></path>
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
                                                    onclick="togglePassword('password')" id="togglePasswordBtn">
                                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" id="togglePasswordIcon">
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
                                            <button class="btn btn-primary btn-lg fw-semibold" type="submit"
                                                style="border-radius: 10px; padding: 14px;">
                                                Acceder al Sistema
                                            </button>
                                        </div>
                                    </form>
                                </div>

                                <!-- Tab Registro -->
                                <div class="tab-pane fade" id="register" role="tabpanel">
                                    <h2 class="fw-bold text-dark mb-2 fs-2 text-center">Registro de Postulante</h2>
                                    <p class="text-muted mb-4 text-center">Complete todos los datos para crear su cuenta</p>
                                    
                                    <!-- Formulario de Registro -->
                                    <form id="registrationForm" method="POST" action="{{ route('register.postulante') }}" class="needs-validation" novalidate>
                                        @csrf
                                        
                                        <!-- Sección: Datos del Postulante -->
                                        <h5 class="mb-3 text-primary">
                                            <svg class="me-2" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                                <circle cx="12" cy="7" r="4"></circle>
                                            </svg>
                                            Datos Personales del Postulante
                                        </h5>
                                        
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="reg_nombre" class="form-label">Nombres <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="reg_nombre" name="nombre" 
                                                       value="{{ old('nombre') }}" required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="reg_apellido_paterno" class="form-label">Apellido Paterno <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="reg_apellido_paterno" 
                                                       name="apellido_paterno" value="{{ old('apellido_paterno') }}" required>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="reg_apellido_materno" class="form-label">Apellido Materno <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="reg_apellido_materno" 
                                                       name="apellido_materno" value="{{ old('apellido_materno') }}" required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="reg_tipo_documento" class="form-label">Tipo Documento <span class="text-danger">*</span></label>
                                                <select class="form-select" id="reg_tipo_documento" name="tipo_documento" required>
                                                    <option value="">Seleccione...</option>
                                                    <option value="DNI" {{ old('tipo_documento') == 'DNI' ? 'selected' : '' }}>DNI</option>
                                                    <option value="CE" {{ old('tipo_documento') == 'CE' ? 'selected' : '' }}>Carnet de Extranjería</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="reg_numero_documento" class="form-label">Número Documento <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control" id="reg_numero_documento" 
                                                           name="numero_documento" value="{{ old('numero_documento') }}" 
                                                           maxlength="8" pattern="[0-9]{8}" required>
                                                    <button class="btn btn-outline-primary" type="button" onclick="consultarDNI('postulante')" 
                                                            id="btn_buscar_postulante" title="Buscar en RENIEC">
                                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                            <circle cx="11" cy="11" r="8"></circle>
                                                            <path d="m21 21-4.35-4.35"></path>
                                                        </svg>
                                                        Buscar
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="reg_fecha_nacimiento" class="form-label">Fecha Nacimiento <span class="text-danger">*</span></label>
                                                <input type="date" class="form-control" id="reg_fecha_nacimiento" 
                                                       name="fecha_nacimiento" value="{{ old('fecha_nacimiento') }}" 
                                                       max="{{ date('Y-m-d', strtotime('-14 years')) }}" required>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="reg_genero" class="form-label">Género <span class="text-danger">*</span></label>
                                                <select class="form-select" id="reg_genero" name="genero" required>
                                                    <option value="">Seleccione...</option>
                                                    <option value="M" {{ old('genero') == 'M' ? 'selected' : '' }}>Masculino</option>
                                                    <option value="F" {{ old('genero') == 'F' ? 'selected' : '' }}>Femenino</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="reg_telefono" class="form-label">Teléfono/Celular <span class="text-danger">*</span></label>
                                                <input type="tel" class="form-control" id="reg_telefono" 
                                                       name="telefono" value="{{ old('telefono') }}" 
                                                       pattern="[0-9]{9}" maxlength="9" required>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="reg_direccion" class="form-label">Dirección <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="reg_direccion" 
                                                   name="direccion" value="{{ old('direccion') }}" required>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="reg_email" class="form-label">Correo Electrónico <span class="text-danger">*</span></label>
                                                <input type="email" class="form-control" id="reg_email" 
                                                       name="email" value="{{ old('email') }}" required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="reg_password" class="form-label">Contraseña <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <input type="password" class="form-control" id="reg_password" 
                                                           name="password" minlength="8" required>
                                                    <button class="btn btn-outline-secondary" type="button" 
                                                            onclick="togglePassword('reg_password')">
                                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                                            <circle cx="12" cy="12" r="3"></circle>
                                                        </svg>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mb-4">
                                            <label for="reg_password_confirmation" class="form-label">Confirmar Contraseña <span class="text-danger">*</span></label>
                                            <input type="password" class="form-control" id="reg_password_confirmation" 
                                                   name="password_confirmation" minlength="8" required>
                                        </div>

                                        <!-- Sección: Datos del Padre -->
                                        <h5 class="mb-3 text-primary">
                                            <svg class="me-2" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                                <circle cx="8.5" cy="7" r="4"></circle>
                                                <path d="M20 21v-2a4 4 0 0 0-4-4H13"></path>
                                                <circle cx="17.5" cy="7" r="4"></circle>
                                            </svg>
                                            Datos del Padre/Tutor
                                        </h5>

                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="padre_nombre" class="form-label">Nombres del Padre <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="padre_nombre" 
                                                       name="padre_nombre" value="{{ old('padre_nombre') }}" required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="padre_apellidos" class="form-label">Apellidos del Padre <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="padre_apellidos" 
                                                       name="padre_apellidos" value="{{ old('padre_apellidos') }}" required>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-4 mb-3">
                                                <label for="padre_tipo_doc" class="form-label">Tipo Doc. <span class="text-danger">*</span></label>
                                                <select class="form-select" id="padre_tipo_doc" name="padre_tipo_documento" required>
                                                    <option value="">Seleccione...</option>
                                                    <option value="DNI" {{ old('padre_tipo_documento') == 'DNI' ? 'selected' : '' }}>DNI</option>
                                                    <option value="CE" {{ old('padre_tipo_documento') == 'CE' ? 'selected' : '' }}>CE</option>
                                                </select>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="padre_numero_doc" class="form-label">Número Doc. <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control" id="padre_numero_doc" 
                                                           name="padre_numero_documento" value="{{ old('padre_numero_documento') }}" 
                                                           maxlength="8" pattern="[0-9]{8}" required>
                                                    <button class="btn btn-outline-primary btn-sm" type="button" onclick="consultarDNI('padre')" 
                                                            id="btn_buscar_padre" title="Buscar">
                                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                            <circle cx="11" cy="11" r="8"></circle>
                                                            <path d="m21 21-4.35-4.35"></path>
                                                        </svg>
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="padre_telefono" class="form-label">Teléfono <span class="text-danger">*</span></label>
                                                <input type="tel" class="form-control" id="padre_telefono" 
                                                       name="padre_telefono" value="{{ old('padre_telefono') }}" 
                                                       pattern="[0-9]{9}" maxlength="9" required>
                                            </div>
                                        </div>

                                        <div class="mb-4">
                                            <label for="padre_email" class="form-label">Correo del Padre</label>
                                            <input type="email" class="form-control" id="padre_email" 
                                                   name="padre_email" value="{{ old('padre_email') }}">
                                        </div>

                                        <!-- Sección: Datos de la Madre -->
                                        <h5 class="mb-3 text-primary">
                                            <svg class="me-2" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                                <circle cx="8.5" cy="7" r="4"></circle>
                                                <path d="M20 21v-2a4 4 0 0 0-4-4H13"></path>
                                                <circle cx="17.5" cy="7" r="4"></circle>
                                            </svg>
                                            Datos de la Madre/Tutora
                                        </h5>

                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="madre_nombre" class="form-label">Nombres de la Madre <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="madre_nombre" 
                                                       name="madre_nombre" value="{{ old('madre_nombre') }}" required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="madre_apellidos" class="form-label">Apellidos de la Madre <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="madre_apellidos" 
                                                       name="madre_apellidos" value="{{ old('madre_apellidos') }}" required>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-4 mb-3">
                                                <label for="madre_tipo_doc" class="form-label">Tipo Doc. <span class="text-danger">*</span></label>
                                                <select class="form-select" id="madre_tipo_doc" name="madre_tipo_documento" required>
                                                    <option value="">Seleccione...</option>
                                                    <option value="DNI" {{ old('madre_tipo_documento') == 'DNI' ? 'selected' : '' }}>DNI</option>
                                                    <option value="CE" {{ old('madre_tipo_documento') == 'CE' ? 'selected' : '' }}>CE</option>
                                                </select>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="madre_numero_doc" class="form-label">Número Doc. <span class="text-danger">*</span></label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control" id="madre_numero_doc" 
                                                           name="madre_numero_documento" value="{{ old('madre_numero_documento') }}" 
                                                           maxlength="8" pattern="[0-9]{8}" required>
                                                    <button class="btn btn-outline-primary btn-sm" type="button" onclick="consultarDNI('madre')" 
                                                            id="btn_buscar_madre" title="Buscar">
                                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                            <circle cx="11" cy="11" r="8"></circle>
                                                            <path d="m21 21-4.35-4.35"></path>
                                                        </svg>
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="col-md-4 mb-3">
                                                <label for="madre_telefono" class="form-label">Teléfono <span class="text-danger">*</span></label>
                                                <input type="tel" class="form-control" id="madre_telefono" 
                                                       name="madre_telefono" value="{{ old('madre_telefono') }}" 
                                                       pattern="[0-9]{9}" maxlength="9" required>
                                            </div>
                                        </div>

                                        <div class="mb-4">
                                            <label for="madre_email" class="form-label">Correo de la Madre</label>
                                            <input type="email" class="form-control" id="madre_email" 
                                                   name="madre_email" value="{{ old('madre_email') }}">
                                        </div>

                                        <!-- Términos y Condiciones -->
                                        <div class="form-check mb-4">
                                            <input class="form-check-input" type="checkbox" id="terms" name="terms" required>
                                            <label class="form-check-label" for="terms">
                                                Acepto los <a href="#" class="text-primary">términos y condiciones</a> y la 
                                                <a href="#" class="text-primary">política de privacidad</a>
                                            </label>
                                        </div>

                                        <!-- Botón de Registro -->
                                        <div class="d-grid">
                                            <button class="btn btn-primary btn-lg fw-semibold" type="submit"
                                                style="border-radius: 10px; padding: 14px;">
                                                <svg class="me-2" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                                    <circle cx="8.5" cy="7" r="4"></circle>
                                                    <line x1="20" y1="8" x2="20" y2="14"></line>
                                                    <line x1="23" y1="11" x2="17" y2="11"></line>
                                                </svg>
                                                Registrar Postulación
                                            </button>
                                        </div>
                                    </form>
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
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        // Token CSRF para las peticiones AJAX
        const csrfToken = '{{ csrf_token() }}';
        
        // Función para mostrar/ocultar contraseña
        function togglePassword(fieldId) {
            const passwordInput = document.getElementById(fieldId);
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
            } else {
                passwordInput.type = 'password';
            }
        }
        
        // Función para consultar DNI en RENIEC
        async function consultarDNI(tipo) {
            let dniInput, btnBuscar;
            
            // Determinar qué campos usar según el tipo
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
            
            // Validar que el DNI tenga 8 dígitos
            if (dni.length !== 8) {
                mostrarAlerta('error', 'El DNI debe tener 8 dígitos');
                return;
            }
            
            // Deshabilitar botón y mostrar loading
            const btnTextoOriginal = btnBuscar.innerHTML;
            btnBuscar.disabled = true;
            btnBuscar.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Buscando...';
            
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
                
                const result = await response.json();
                
                if (result.success) {
                    // Autocompletar campos según el tipo
                    if (tipo === 'postulante') {
                        autocompletarPostulante(result.data);
                    } else if (tipo === 'padre') {
                        autocompletarPadre(result.data);
                    } else if (tipo === 'madre') {
                        autocompletarMadre(result.data);
                    }
                    
                    mostrarAlerta('success', 'Datos encontrados y cargados correctamente');
                } else {
                    mostrarAlerta('warning', result.message || 'No se encontraron datos para el DNI ingresado');
                }
            } catch (error) {
                console.error('Error:', error);
                mostrarAlerta('error', 'Error al consultar el servicio. Intente nuevamente.');
            } finally {
                // Restaurar botón
                btnBuscar.disabled = false;
                btnBuscar.innerHTML = btnTextoOriginal;
            }
        }
        
        // Función para autocompletar datos del postulante
        function autocompletarPostulante(datos) {
            // Nombres y apellidos
            if (datos.nombres) document.getElementById('reg_nombre').value = datos.nombres;
            if (datos.apellido_paterno) document.getElementById('reg_apellido_paterno').value = datos.apellido_paterno;
            if (datos.apellido_materno) document.getElementById('reg_apellido_materno').value = datos.apellido_materno;
            
            // Fecha de nacimiento
            if (datos.fecha_nacimiento) document.getElementById('reg_fecha_nacimiento').value = datos.fecha_nacimiento;
            
            // Género
            if (datos.genero) document.getElementById('reg_genero').value = datos.genero;
            
            // Dirección
            if (datos.direccion) document.getElementById('reg_direccion').value = datos.direccion;
            
            // Tipo de documento (siempre DNI cuando viene de RENIEC)
            document.getElementById('reg_tipo_documento').value = 'DNI';
        }
        
        // Función para autocompletar datos del padre
        function autocompletarPadre(datos) {
            // Separar nombres y apellidos
            const apellidos = (datos.apellido_paterno || '') + ' ' + (datos.apellido_materno || '');
            
            if (datos.nombres) document.getElementById('padre_nombre').value = datos.nombres;
            if (apellidos.trim()) document.getElementById('padre_apellidos').value = apellidos.trim();
            
            // Tipo de documento
            document.getElementById('padre_tipo_doc').value = 'DNI';
        }
        
        // Función para autocompletar datos de la madre
        function autocompletarMadre(datos) {
            // Separar nombres y apellidos
            const apellidos = (datos.apellido_paterno || '') + ' ' + (datos.apellido_materno || '');
            
            if (datos.nombres) document.getElementById('madre_nombre').value = datos.nombres;
            if (apellidos.trim()) document.getElementById('madre_apellidos').value = apellidos.trim();
            
            // Tipo de documento
            document.getElementById('madre_tipo_doc').value = 'DNI';
        }
        
        // Función para mostrar alertas temporales
        function mostrarAlerta(tipo, mensaje) {
            // Remover alerta existente si hay
            const alertaExistente = document.querySelector('.alerta-temporal');
            if (alertaExistente) {
                alertaExistente.remove();
            }
            
            // Determinar clase según tipo
            let claseAlerta = 'alert-info';
            let icono = 'ℹ️';
            
            if (tipo === 'success') {
                claseAlerta = 'alert-success';
                icono = '✅';
            } else if (tipo === 'error') {
                claseAlerta = 'alert-danger';
                icono = '❌';
            } else if (tipo === 'warning') {
                claseAlerta = 'alert-warning';
                icono = '⚠️';
            }
            
            // Crear nueva alerta
            const alerta = document.createElement('div');
            alerta.className = `alert ${claseAlerta} alert-dismissible fade show alerta-temporal position-fixed`;
            alerta.style.cssText = 'top: 20px; right: 20px; z-index: 9999; max-width: 400px;';
            alerta.innerHTML = `
                ${icono} ${mensaje}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            document.body.appendChild(alerta);
            
            // Auto-ocultar después de 5 segundos
            setTimeout(() => {
                if (alerta.parentElement) {
                    alerta.remove();
                }
            }, 5000);
        }
        
        // Agregar eventos para buscar al presionar Enter en los campos DNI
        document.addEventListener('DOMContentLoaded', function() {
            // Para postulante
            document.getElementById('reg_numero_documento')?.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    consultarDNI('postulante');
                }
            });
            
            // Para padre
            document.getElementById('padre_numero_doc')?.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    consultarDNI('padre');
                }
            });
            
            // Para madre
            document.getElementById('madre_numero_doc')?.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    consultarDNI('madre');
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

        // Mantener el tab activo después de un error
        document.addEventListener('DOMContentLoaded', function() {
            @if(session('register_tab'))
                var registerTab = new bootstrap.Tab(document.getElementById('register-tab'));
                registerTab.show();
            @endif
            
            // Manejo del formulario de registro con AJAX
            const registrationForm = document.getElementById('registrationForm');
            if (registrationForm) {
                registrationForm.addEventListener('submit', async function(e) {
                    e.preventDefault();
                    
                    // Validar formulario primero
                    if (!this.checkValidity()) {
                        e.stopPropagation();
                        this.classList.add('was-validated');
                        return;
                    }
                    
                    // Obtener el email del formulario
                    const emailInput = this.querySelector('input[name="email"]');
                    const email = emailInput ? emailInput.value : '';
                    
                    // Mostrar confirmación
                    if (!confirm(`IMPORTANTE: Se enviará un correo de confirmación a:\n\n${email}\n\nSu cuenta se creará con estado PENDIENTE hasta que verifique su correo electrónico.\n\n¿Desea continuar con el registro?`)) {
                        return;
                    }
                    
                    // Preparar datos del formulario
                    const formData = new FormData(this);
                    const submitBtn = this.querySelector('button[type="submit"]');
                    const originalBtnText = submitBtn.innerHTML;
                    
                    // Deshabilitar botón y mostrar loading
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Registrando...';
                    
                    try {
                        // Primero verificar el servidor de correo
                        const emailCheck = await fetch('{{ route("api.register.check-email") }}');
                        const emailStatus = await emailCheck.json();
                        
                        if (!emailStatus.success) {
                            console.warn('Servidor de correo no disponible:', emailStatus);
                        }
                        
                        // Enviar registro
                        const response = await fetch('{{ route("api.register.postulante") }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json'
                            },
                            body: formData
                        });
                        
                        const data = await response.json();
                        
                        if (data.success) {
                            // Registro exitoso
                            let userInfo = `Usuario: ${data.data.postulante.username}<br>Email: ${data.data.postulante.email}`;
                            
                            if (data.email_status === 'sent') {
                                // Email enviado exitosamente - Toast verde
                                showToast('success', 
                                    '¡Registro Exitoso!', 
                                    'Se ha enviado un correo de verificación a ' + data.data.postulante.email,
                                    userInfo
                                );
                            } else if (data.email_status === 'failed') {
                                // Registro exitoso pero email falló - Toast amarillo
                                showToast('warning', 
                                    'Registro Completado (Sin Email)', 
                                    'La cuenta fue creada pero el correo no pudo ser enviado.',
                                    'Contacte al administrador para activar su cuenta.<br>' + userInfo
                                );
                            }
                            
                            // Si hay padres existentes, mostrar info adicional
                            if (data.data.padre_existente) {
                                setTimeout(() => {
                                    showToast('info', 
                                        'Padre Vinculado', 
                                        `${data.data.padre_existente.nombre} ya estaba registrado`,
                                        `DNI: ${data.data.padre_existente.dni}<br>Hijos registrados: ${data.data.padre_existente.hijos_registrados}`
                                    );
                                }, 500);
                            }
                            
                            if (data.data.madre_existente) {
                                setTimeout(() => {
                                    showToast('info', 
                                        'Madre Vinculada', 
                                        `${data.data.madre_existente.nombre} ya estaba registrada`,
                                        `DNI: ${data.data.madre_existente.dni}<br>Hijos registrados: ${data.data.madre_existente.hijos_registrados}`
                                    );
                                }, 1000);
                            }
                            
                            // Limpiar formulario si fue exitoso
                            if (data.email_status === 'sent') {
                                this.reset();
                                this.classList.remove('was-validated');
                                
                                // Redirigir después de 5 segundos
                                setTimeout(() => {
                                    window.location.href = '{{ route("login") }}';
                                }, 5000);
                            }
                        } else {
                            // Error en el registro
                            let errorMessages = '';
                            
                            if (data.errors) {
                                const errorList = [];
                                for (const field in data.errors) {
                                    data.errors[field].forEach(error => {
                                        errorList.push(error);
                                    });
                                }
                                errorMessages = errorList.join('<br>');
                            } else {
                                errorMessages = data.message || 'Error desconocido';
                            }
                            
                            showToast('error', 
                                'Error en el Registro', 
                                errorMessages
                            );
                        }
                        
                    } catch (error) {
                        console.error('Error:', error);
                        showToast('error', 
                            'Error de Conexión', 
                            'No se pudo conectar con el servidor.',
                            'Por favor, intente nuevamente'
                        );
                    } finally {
                        // Restaurar botón
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalBtnText;
                    }
                });
            }
            
            // Función para mostrar toast
            function showToast(type, title, message, details = null) {
                // Crear contenedor de toasts si no existe
                let toastContainer = document.getElementById('toast-container');
                if (!toastContainer) {
                    toastContainer = document.createElement('div');
                    toastContainer.id = 'toast-container';
                    toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
                    toastContainer.style.zIndex = '9999';
                    document.body.appendChild(toastContainer);
                }
                
                // Determinar color y icono según el tipo
                let bgClass = 'bg-success';
                let icon = '✓';
                let textClass = 'text-white';
                
                if (type === 'error') {
                    bgClass = 'bg-danger';
                    icon = '✗';
                } else if (type === 'warning') {
                    bgClass = 'bg-warning';
                    icon = '⚠';
                } else if (type === 'info') {
                    bgClass = 'bg-info';
                    icon = 'ℹ';
                }
                
                // Crear el toast
                const toastId = 'toast-' + Date.now();
                const toastHtml = `
                    <div id="${toastId}" class="toast align-items-center ${bgClass} ${textClass} border-0" role="alert" aria-live="assertive" aria-atomic="true">
                        <div class="d-flex">
                            <div class="toast-body">
                                <div class="d-flex align-items-start">
                                    <span class="fs-4 me-2">${icon}</span>
                                    <div class="flex-grow-1">
                                        <strong class="d-block mb-1">${title}</strong>
                                        <div>${message}</div>
                                        ${details ? `<small class="d-block mt-2 opacity-75">${details}</small>` : ''}
                                    </div>
                                </div>
                            </div>
                            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                        </div>
                    </div>
                `;
                
                toastContainer.insertAdjacentHTML('beforeend', toastHtml);
                
                // Inicializar y mostrar el toast
                const toastElement = document.getElementById(toastId);
                const toast = new bootstrap.Toast(toastElement, {
                    autohide: type === 'success',
                    delay: type === 'success' ? 8000 : 15000
                });
                toast.show();
                
                // Eliminar del DOM cuando se oculte
                toastElement.addEventListener('hidden.bs.toast', () => {
                    toastElement.remove();
                });
            }
        });
    </script>

    <!-- Estilos CSS -->
    <style>
        /* Import Animate.css for subtle animations */
        @import url('https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css');

        body {
            font-family: 'Inter', sans-serif;
            margin: 0;
            overflow-x: hidden;
        }

        .min-vh-100 {
            min-height: 100vh;
        }

        .cursor-pointer {
            cursor: pointer;
        }
        
        .object-fit-cover {
            object-fit: cover;
        }
        
        .shadow-lg {
            box-shadow: 0 1rem 3rem rgba(0, 0, 0, 0.175) !important;
        }

        /* Overlay oscuro para la imagen de fondo */
        .bg-dark-overlay {
            background-color: rgba(30, 40, 50, 0.8);
        }
        
        /* Tabs personalizados */
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
        }
        
        .nav-pills .nav-link:hover:not(.active) {
            background-color: #e9ecef;
        }
        
        /* Colores y estilos para elementos interactivos */
        .form-control:focus, .form-select:focus {
            border-color: #3F51B5;
            box-shadow: 0 0 0 0.2rem rgba(63, 81, 181, 0.25);
        }
        
        .btn-primary {
            background: #3F51B5;
            border: none;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            background: #303F9F;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(63, 81, 181, 0.4);
        }

        .text-primary {
            color: #3F51B5 !important;
        }
        
        .input-group-text {
            border-radius: 8px 0 0 8px; 
            background-color: #f8fafc !important;
            border-color: #dee2e6;
        }

        .input-group-text.rounded-start-pill {
            border-top-left-radius: 8px !important;
            border-bottom-left-radius: 8px !important;
        }

        .input-group-text.rounded-end-pill {
            border-top-right-radius: 8px !important;
            border-bottom-right-radius: 8px !important;
        }
        
        .form-control, .form-select {
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        
        .form-control:focus, .form-select:focus {
            transform: translateY(-1px);
        }
        
        .card {
            backdrop-filter: blur(5px);
            background: rgba(255, 255, 255, 0.98) !important;
            max-height: 90vh;
            overflow-y: auto;
        }
        
        .alert {
            backdrop-filter: blur(10px);
            background: rgba(220, 53, 69, 0.9) !important;
            color: white;
        }
        
        .alert svg {
            color: white;
        }

        /* Animaciones */
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

        /* Scrollbar personalizado para el formulario de registro */
        .card::-webkit-scrollbar {
            width: 8px;
        }
        
        .card::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        
        .card::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 10px;
        }
        
        .card::-webkit-scrollbar-thumb:hover {
            background: #555;
        }

        /* Responsive adjustments */
        @media (max-width: 767.98px) {
            .card-body {
                padding: 1.5rem !important;
            }
            .text-center .mb-4 img {
                height: 70px;
            }
            .fs-2 {
                font-size: 1.5rem !important;
            }
            .nav-pills .nav-link {
                padding: 10px 15px;
                font-size: 0.9rem;
            }
        }
        
        /* Estilos para los toasts */
        .toast {
            min-width: 350px;
            backdrop-filter: blur(10px);
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            border-radius: 12px !important;
            animation: slideInRight 0.3s ease-out;
        }
        
        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        .toast.bg-success {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%) !important;
        }
        
        .toast.bg-danger {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%) !important;
        }
        
        .toast.bg-warning {
            background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%) !important;
        }
        
        .toast.bg-info {
            background: linear-gradient(135deg, #17a2b8 0%, #138496 100%) !important;
        }
        
        .toast-body {
            padding: 1rem 1.25rem;
            font-size: 0.95rem;
        }
        
        .btn-close-white {
            filter: brightness(0) invert(1);
        }
        
        @media (max-width: 576px) {
            .toast {
                min-width: 90vw;
                margin: 0 auto;
            }
            .toast-container {
                left: 50% !important;
                right: auto !important;
                transform: translateX(-50%);
            }
        }
    </style>
@endsection