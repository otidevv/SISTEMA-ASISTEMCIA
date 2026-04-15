<!-- Estilos base CSS para el modal y wizard, adaptados a los colores del logo -->
<style>
    /* VARIABLES GLOBALES (Colores Institucionales de CEPRE UNAMAD: Verde Lima, Magenta/Rosa, Azul) */
    :root {
        --color-principal: #8bc34a;
        /* Verde Lima (Dominante en Logo) */
        --color-secundario: #e91e63;
        /* Magenta/Rosa (Libros en Logo) */
        --color-acento: #03a9f4;
        /* Azul Cían (Acento) */
        --color-texto-oscuro: #1f2937;
        /* Gris Oscuro para legibilidad */
        --color-fondo-claro: #f8f8f8;
    }

    .modal {
        display: none;
        /* Asegura que esté oculto por defecto */
        position: fixed;
        z-index: 1050;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0, 0, 0, 0.6);
        /* Mejoras para móviles */
        -webkit-overflow-scrolling: touch;
        /* Smooth scrolling en iOS */
    }

    .modal-content {
        background-color: #fefefe;
        margin: 3% auto;
        padding: 0;
        /* Padding movido a la columna interna */
        border: none;
        border-radius: 15px;
        /* Bordes más suaves */
        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.3);
        /* Mejoras para móviles */
        max-height: 95vh;
        /* Limitar altura en móviles */
        overflow-y: auto;
        /* Permitir scroll interno */
        -webkit-overflow-scrolling: touch;
        /* Smooth scrolling en iOS */
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

    .progress-line-fill {
        position: absolute;
        top: 0;
        left: 0;
        height: 100%;
        width: 0%;
        background: var(--color-principal);
        transition: width 0.4s ease;
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
        background: #e0f2f1;
        /* Fondo suave para inactivo */
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
        box-shadow: 0 0 0 5px rgba(139, 195, 74, 0.3);
        /* Efecto de brillo */
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
        color: var(--color-secundario);
        /* Magenta */
        border-bottom: 2px solid var(--color-principal);
        /* Verde Lima */
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
        background-color: #689f38;
        /* Verde más oscuro */
        border-color: #689f38;
        transform: translateY(-1px);
    }

    #prevBtn:hover {
        background-color: #ad1457;
        /* Magenta más oscuro */
        border-color: #ad1457;
        transform: translateY(-1px);
    }

    /* Estilos de inputs y selects limpios */
    .form-control,
    .form-select {
        border-radius: 8px;
        padding: 10px 12px;
        border: 1px solid #d1d5db;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: var(--color-principal);
        box-shadow: 0 0 0 0.25rem rgba(139, 195, 74, 0.25);
    }

    /* Estilos responsivos para móviles */
    @media (max-width: 768px) {
        .modal {
            padding: 10px;
        }

        .modal-content {
            margin: 2% auto;
            padding: 0;
            width: 98% !important;
            max-width: 98% !important;
            max-height: 96vh;
            border-radius: 10px;
        }

        .wizard-progress {
            margin-bottom: 20px;
        }

        .step-item span {
            font-size: 9px;
        }

        .step-circle {
            width: 32px;
            height: 32px;
            font-size: 13px;
        }
    }

    /* Resaltar término de búsqueda */
    .highlight-search {
        background-color: rgba(255, 215, 0, 0.3);
        font-weight: 600;
        padding: 0 2px;
        border-radius: 2px;
    }

    /* FORZAR SELECTS NATIVOS - Eliminar cualquier estilo personalizado */
    #postulacionModal select,
    #postulacionModal .form-select {
        -webkit-appearance: menulist !important;
        -moz-appearance: menulist !important;
        appearance: menulist !important;
        background-image: none !important;
        padding-right: 2rem !important;
    }

    /* Ocultar cualquier elemento de select2 o nice-select que se genere */
    #postulacionModal .select2-container,
    #postulacionModal .nice-select {
        display: none !important;
    }

    /* Asegurar que los selects originales sean visibles */
    #postulacionModal select.form-select,
    #postulacionModal select.form-control {
        display: block !important;
        visibility: visible !important;
        opacity: 1 !important;
        position: relative !important;
    }

    /* Estilos para pantallas muy pequeñas */
    @media (max-width: 480px) {
        .modal-content {
            padding: 0;
            max-height: 98vh;
        }

        .step-item span {
            font-size: 8px;
        }

        .step-circle {
            width: 28px;
            height: 28px;
            font-size: 12px;
        }

        .form-section-title {
            font-size: 1rem;
        }

        .col-md-3,
        .col-md-4,
        .col-md-6,
        .col-md-8,
        .col-md-12 {
            padding-left: 8px;
            padding-right: 8px;
        }
    }

    /* FIX: Garantizar que los íconos de validación no se escapen del campo */
    #postulacionModal .mb-3,
    #postulacionModal .mb-4,
    #postulacionModal .col-md-4,
    #postulacionModal .col-md-6,
    #postulacionModal .col-md-8,
    #postulacionModal .col-md-12 {
        position: relative !important;
        display: flex;
        flex-direction: column;
    }

    #postulacionModal .valid-feedback-icon {
        position: absolute !important;
        right: 12px;
        /* Centrado vertical respecto al input */
        top: 32px;
        height: 38px;
        display: flex;
        align-items: center;
        z-index: 5;
        pointer-events: none;
        color: #198754;
        font-size: 14px;
    }

    /* Estilos para carga de documentos premium (Compactos) */
    .file-upload-card {
        border: 2px dashed #d1d5db;
        border-radius: 12px;
        padding: 12px;
        text-align: center;
        transition: all 0.3s ease;
        cursor: pointer;
        background: #fff;
        height: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 6px;
        min-height: 140px;
    }

    .file-upload-card:hover {
        border-color: var(--color-principal);
        background: #f1f8e9;
        transform: translateY(-2px);
    }

    .file-upload-card i {
        font-size: 1.5rem;
        color: #9ca3af;
        transition: color 0.3s ease;
    }

    .file-upload-card .card-title {
        font-size: 0.9rem;
        font-weight: 700;
        margin-bottom: 2px;
        color: var(--color-texto-oscuro);
    }

    .file-upload-card .card-subtitle {
        font-size: 0.75rem;
        color: #6b7280;
    }

    .file-upload-card.has-file {
        border-style: solid;
        border-color: var(--color-principal);
        background: #f1f8e9;
        position: relative;
    }

    .file-upload-card.has-file::after {
        content: '\f058';
        font-family: 'Font Awesome 5 Free';
        font-weight: 900;
        position: absolute;
        top: 8px;
        right: 8px;
        color: var(--color-principal);
        font-size: 1.2rem;
    }

    .file-upload-card.has-file i {
        color: var(--color-principal);
    }

    .file-upload-card .file-name {
        font-size: 0.75rem;
        color: #059669;
        font-weight: 600;
        word-break: break-all;
        background: rgba(139, 195, 74, 0.1);
        padding: 2px 8px;
        border-radius: 4px;
        margin-top: 4px;
    }

    .file-upload-card input[type="file"] {
        display: none;
    }

    /* ESTILOS PARA EL RESUMEN PROFESIONAL */
    .resumen-card {
        background: #fff;
        border-radius: 15px;
        border: 1px solid #e5e7eb;
        overflow: hidden;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }

    .resumen-header {
        background: linear-gradient(135deg, var(--color-principal) 0%, #689f38 100%);
        padding: 15px;
        color: white;
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .resumen-photo-container {
        width: 90px;
        height: 90px;
        border-radius: 12px;
        border: 3px solid white;
        overflow: hidden;
        background: #f3f4f6;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        flex-shrink: 0;
    }

    .resumen-photo-container img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .resumen-student-info h4 {
        margin: 0;
        font-weight: 800;
        font-size: 1.2rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .resumen-student-info p {
        margin: 2px 0 0;
        opacity: 0.9;
        font-size: 0.9rem;
    }

    .resumen-body {
        padding: 20px;
    }

    .resumen-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
    }

    .resumen-item {
        display: flex;
        flex-direction: column;
    }

    .resumen-label {
        font-size: 0.75rem;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 700;
    }

    .resumen-value {
        font-size: 0.95rem;
        color: var(--color-texto-oscuro);
        font-weight: 600;
    }

    .resumen-item i {
        color: var(--color-principal);
        margin-right: 6px;
        width: 16px;
        text-align: center;
    }

    .resumen-payments-box {
        margin-top: 20px;
        background: #f9fafb;
        border-radius: 10px;
        border: 1px solid #e5e7eb;
        padding: 15px;
    }

    @keyframes fadeInSlide {
        from {
            opacity: 0;
            transform: translateY(10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Animación para campos autocompletados (Pintado Permanente Temporal) */
    @keyframes highlight-pulse-intense {
        0% {
            background-color: rgba(139, 195, 74, 1) !important;
            box-shadow: 0 0 0 8px rgba(139, 195, 74, 0.4) !important;
            border: 3px solid var(--color-principal) !important;
            transform: scale(1.02);
            z-index: 99;
        }

        30% {
            background-color: rgba(139, 195, 74, 0.8) !important;
            transform: scale(1.02);
        }

        100% {
            background-color: #fff !important;
            box-shadow: none;
            border: 1px solid #d1d5db !important;
            transform: scale(1);
        }
    }

    .field-highlight {
        animation: highlight-pulse-intense 3.5s cubic-bezier(0.4, 0, 0.2, 1) forwards !important;
        z-index: 100 !important;
        position: relative !important;
    }

    /* Estilos para la barra de progreso del wizard */
    #wizard-progress-fill {
        background-color: var(--color-principal);
    }
</style>

<!-- Modal de Postulación -->
<div id="postulacionModal" class="modal">
    <div class="modal-content" style="max-width: 1200px; width: 95%;">
        <div class="row g-0">
            <!-- Sección de Flyers (Carrusel oculto en pantallas pequeñas) -->
            <div class="col-lg-5 d-none d-lg-block" style="background-color: #0d1e34; position: relative;">
                <div id="flayerCarousel" class="carousel slide carousel-fade h-100" data-bs-ride="carousel"
                    data-bs-interval="4000">
                    <div class="carousel-inner h-100">
                        <div class="carousel-item active h-100"
                            style="background-image: url('{{ asset('assets_cepre/img/flayer_reforzamiento.jpg') }}'); background-size: contain; background-repeat: no-repeat; background-position: center;">
                        </div>
                        <div class="carousel-item h-100"
                            style="background-image: url('{{ asset('assets_cepre/img/flyer_reforzamiento2.jpg') }}'); background-size: contain; background-repeat: no-repeat; background-position: center;">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sección del Formulario -->
            <div class="col-lg-7 p-4 p-md-5 position-relative">
                <span class="close-button" onclick="closeModal('postulacionModal')"
                    style="position: absolute; top: 15px; right: 25px; font-size: 32px; color: #6b7280; cursor: pointer; z-index: 10;">&times;</span>
                <h3 style="color: var(--color-secundario); margin-bottom: 30px; text-align: center; font-weight: 800;">
                    <img src="{{ asset('assets_cepre/img/logo/logocepre1.svg') }}"
                        onerror="this.onerror=null; this.src='https://placehold.co/150x60/8bc34a/ffffff?text=CEPRE+UNAMAD';"
                        alt="CEPRE UNAMAD" style="height: 40px; margin-right: 10px; vertical-align: middle;">
                    Postulación CEPRE UNAMAD
                </h3>

                <!-- Wizard Steps -->
                <div class="wizard-progress">
                    <div class="progress-line">
                        <div class="progress-line-fill" id="wizard-progress-fill"></div>
                    </div>

                    <div class="step-item active" data-step="1">
                        <div class="step-circle">1</div>
                        <span>Personal</span>
                    </div>
                    <div class="step-item" data-step="2">
                        <div class="step-circle">2</div>
                        <span>Padres</span>
                    </div>
                    <div class="step-item" data-step="3">
                        <div class="step-circle">3</div>
                        <span>Académico</span>
                    </div>
                    <div class="step-item" data-step="4">
                        <div class="step-circle">4</div>
                        <span>Docs/Pago</span>
                    </div>
                    <div class="step-item" data-step="5">
                        <div class="step-circle">5</div>
                        <span>Confirmar</span>
                    </div>
                </div>

                <form id="formPostulacionPublica" enctype="multipart/form-data" novalidate>
                    @csrf

                    <!-- Paso 1: Datos Personales (Incluye validación DNI inicial) -->
                    <div class="step-content active" data-step="1">
                        <h5 class="form-section-title">Datos Personales</h5>

                        <!-- Selector de Tipo de Documento -->
                        <div class="mb-4">
                            <label for="estudiante_tipo_documento_select" class="form-label"
                                style="font-weight: bold;">Tipo de Documento</label>
                            <div class="d-flex gap-2">
                                <div class="flex-grow-1">
                                    <select class="form-select" id="estudiante_tipo_documento_select"
                                        name="estudiante_tipo_documento">
                                        <option value="1" selected>DNI (Perú)</option>
                                        <option value="2">Carnet de Extranjería</option>
                                        <option value="3">Pasaporte</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Sección de Verificación (Solo para DNI) -->
                        <div id="section-verificacion" class="mb-4 p-3 bg-light rounded-lg border border-gray-200">
                            <div class="d-flex align-items-end gap-2 mb-2 flex-wrap w-100">
                                <div style="flex: 1; min-width: 120px;">
                                    <label class="form-label mb-1"
                                        style="font-size: 0.8rem; font-weight: 700; color: #4a5568;">DNI</label>
                                    <input type="text" class="form-control" id="check_dni" name="check_dni"
                                        maxlength="8" pattern="[0-9]{8}" placeholder="8 dígitos" required
                                        style="height: 38px;">
                                </div>
                                <div class="pb-2 fw-bold text-muted d-none d-sm-block" style="font-size: 1.2rem;">-
                                </div>
                                <div style="width: 100px; flex-shrink: 0;">
                                    <label class="form-label mb-1 text-center d-block"
                                        style="font-size: 0.8rem; font-weight: 700; color: #4a5568;">DV</label>
                                    <input type="text" class="form-control text-center" id="check_dv" name="check_dv"
                                        maxlength="1" pattern="[0-9]{1}" placeholder="0" required style="height: 38px;">
                                </div>
                                <div class="w-100 d-sm-none"></div> <!-- Salto de línea solo en móvil muy pequeño -->
                                <div class="flex-grow-1 flex-sm-grow-0">
                                    <button type="button" class="btn btn-next-prev shadow-sm w-100"
                                        id="btn-verificar-dni"
                                        style="background-color: var(--color-principal); color: white; white-space: nowrap; height: 38px; padding: 0 25px; font-weight: 700; min-width: 120px;">
                                        <i class="fas fa-search me-2"></i> Verificar
                                    </button>
                                </div>
                            </div>
                            <div id="dni_feedback" class="mt-2 p-3 rounded border" style="background-color: #f8fafc;">
                                <div class="d-flex align-items-start mb-2">
                                    <i class="fas fa-info-circle me-2 text-primary mt-1"></i>
                                    <div>
                                        <span class="fw-bold d-block mb-1" style="font-size: 0.9rem;">¿Dónde encontrar
                                            el dígito verificador?</span>
                                        <p class="text-muted mb-2" style="font-size: 0.85rem; line-height: 1.4;">
                                            Es el número ubicado después del guion en la parte superior derecha (DNI
                                            azul) o al final del número CUI (DNI electrónico).
                                        </p>
                                    </div>
                                </div>
                                <div class="text-center rounded overflow-hidden shadow-sm border bg-white mt-1">
                                    <img src="{{ asset('assets_cepre/img/ejmplo_verificador.jpg') }}"
                                        alt="Guía Dígito Verificador" class="img-fluid"
                                        style="max-height: 250px; width: auto; object-fit: contain;">
                                </div>
                            </div>
                        </div>

                        <div id="personal_fields" style="display: none;">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label" id="label_estudiante_dni">Número de Documento</label>
                                    <input type="text" class="form-control" id="estudiante_dni" name="estudiante_dni">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Nombres</label>
                                    <input type="text" class="form-control" id="estudiante_nombre"
                                        name="estudiante_nombre" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Apellido Paterno</label>
                                    <input type="text" class="form-control" id="estudiante_apellido_paterno"
                                        name="estudiante_apellido_paterno" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Apellido Materno</label>
                                    <input type="text" class="form-control" id="estudiante_apellido_materno"
                                        name="estudiante_apellido_materno" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Fecha Nacimiento</label>
                                    <input type="date" class="form-control" id="estudiante_fecha_nacimiento"
                                        name="estudiante_fecha_nacimiento" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Género</label>
                                    <select class="form-select" id="estudiante_genero" name="estudiante_genero"
                                        required>
                                        <option value="">Seleccione</option>
                                        <option value="M">Masculino</option>
                                        <option value="F">Femenino</option>
                                    </select>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Teléfono</label>
                                    <input type="tel" class="form-control" id="estudiante_telefono"
                                        name="estudiante_telefono" pattern="[0-9]{9}" maxlength="9" required>
                                </div>
                                <div class="col-md-8 mb-3">
                                    <label class="form-label">Dirección</label>
                                    <input type="text" class="form-control" id="estudiante_direccion"
                                        name="estudiante_direccion" required>
                                </div>
                                <div class="col-md-12 mb-3">
                                    <label class="form-label">Email (Usuario)</label>
                                    <input type="email" class="form-control" id="estudiante_email"
                                        name="estudiante_email" required>
                                    <small class="text-muted">
                                        <i class="fas fa-info-circle"></i> Tu contraseña será tu DNI. Podrás cambiarla
                                        después desde tu perfil.
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Paso 2: Datos de Padres (Unificado) -->
                    <div class="step-content" data-step="2" style="display: none;">
                        <h5 class="form-section-title">Datos de los Padres</h5>

                        <div class="alert alert-warning py-2 small mb-4">
                            <i class="fas fa-exclamation-triangle me-1"></i>
                            <strong>Importante:</strong> Debe registrar al menos la información de uno de los padres
                            (padre o madre).
                        </div>

                        <!-- PADRE -->
                        <div class="card mb-4 shadow-sm">
                            <div class="card-header" style="background-color: var(--color-acento); color: white;">
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input" type="checkbox" id="tiene_padre" checked
                                        onchange="togglePadreFields()">
                                    <label class="form-check-label fw-bold" for="tiene_padre">
                                        <i class="fas fa-user-tie me-1"></i> Registrar información del Padre
                                    </label>
                                </div>
                            </div>
                            <div class="card-body" id="padre_fields_container">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">DNI Padre</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control" id="padre_dni" name="padre_dni"
                                                maxlength="8" pattern="[0-9]{8}">
                                            <button class="btn btn-outline-secondary" type="button"
                                                id="btn-consultar-padre"
                                                onclick="consultarDNIPadre('padre', this)">Consultar</button>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Nombres</label>
                                        <input type="text" class="form-control" id="padre_nombre" name="padre_nombre">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Apellidos</label>
                                        <input type="text" class="form-control" id="padre_apellidos"
                                            name="padre_apellidos">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Teléfono</label>
                                        <input type="tel" class="form-control" id="padre_telefono" name="padre_telefono"
                                            pattern="[0-9]{9}" maxlength="9">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Ocupación</label>
                                        <input type="text" class="form-control" id="padre_ocupacion"
                                            name="padre_ocupacion">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Email</label>
                                        <input type="email" class="form-control" id="padre_email" name="padre_email">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- MADRE -->
                        <div class="card mb-3 shadow-sm">
                            <div class="card-header" style="background-color: var(--color-secundario); color: white;">
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input" type="checkbox" id="tiene_madre" checked
                                        onchange="toggleMadreFields()">
                                    <label class="form-check-label fw-bold" for="tiene_madre">
                                        <i class="fas fa-user me-1"></i> Registrar información de la Madre
                                    </label>
                                </div>
                            </div>
                            <div class="card-body" id="madre_fields_container">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">DNI Madre</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control" id="madre_dni" name="madre_dni"
                                                maxlength="8" pattern="[0-9]{8}">
                                            <button class="btn btn-outline-secondary" type="button"
                                                id="btn-consultar-madre"
                                                onclick="consultarDNIPadre('madre', this)">Consultar</button>
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Nombres</label>
                                        <input type="text" class="form-control" id="madre_nombre" name="madre_nombre">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Apellidos</label>
                                        <input type="text" class="form-control" id="madre_apellidos"
                                            name="madre_apellidos">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Teléfono</label>
                                        <input type="tel" class="form-control" id="madre_telefono" name="madre_telefono"
                                            pattern="[0-9]{9}" maxlength="9">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Ocupación</label>
                                        <input type="text" class="form-control" id="madre_ocupacion"
                                            name="madre_ocupacion">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Email</label>
                                        <input type="email" class="form-control" id="madre_email" name="madre_email">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Paso 3: Datos Académicos -->
                    <div class="step-content" data-step="3" style="display: none;">
                        <h5 class="form-section-title">Datos Académicos</h5>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Carrera a Postular</label>
                                <!-- Usando form-select limpio -->
                                    <select class="form-select" id="carrera_id" name="carrera_id" required>
                                        <option value="">Seleccione carrera</option>
                                        @php
                                            $carrerasAgrupadas = \App\Models\Carrera::where('estado', 1)
                                                ->orderBy('nombre', 'asc')
                                                ->get()
                                                ->groupBy('grupo');
                                            
                                            $nombresGrupos = [
                                                'A' => 'GRUPO A - INGENIERÍAS',
                                                'B' => 'GRUPO B - CIENCIAS DE LA SALUD',
                                                'C' => 'GRUPO C - CIENCIAS SOCIALES Y EDUCACIÓN',
                                                'D' => 'GRUPO D - ALTA ESPECIALIZACIÓN / SALUD'
                                            ];

                                            $nuevasCarreras = ['MEDICINA HUMANA', 'BIOLOGÍA', 'ECONOMÍA'];
                                        @endphp

                                        @foreach(['A', 'B', 'C', 'D'] as $grupoKey)
                                            @if(isset($carrerasAgrupadas[$grupoKey]))
                                                <optgroup label="{{ $nombresGrupos[$grupoKey] ?? "GRUPO $grupoKey" }}">
                                                    @php
                                                        // Ordenamos para que las NUEVAS aparezcan primero dentro de su grupo
                                                        $carrerasDelGrupo = $carrerasAgrupadas[$grupoKey]->sortByDesc(function($c) use ($nuevasCarreras) {
                                                            $nombreLimpio = strtoupper(trim($c->nombre));
                                                            return (
                                                                strpos($nombreLimpio, 'MEDICINA HUMANA') !== false || 
                                                                strpos($nombreLimpio, 'BIOLOG') !== false || 
                                                                strpos($nombreLimpio, 'ECONOM') !== false
                                                            ) ? 1 : 0;
                                                        });
                                                    @endphp
                                                    @foreach($carrerasDelGrupo as $carrera)
                                                        @php
                                                            $nombreLimpio = strtoupper(trim($carrera->nombre));
                                                            $esNueva = (
                                                                strpos($nombreLimpio, 'MEDICINA HUMANA') !== false || 
                                                                strpos($nombreLimpio, 'BIOLOG') !== false || 
                                                                strpos($nombreLimpio, 'ECONOM') !== false
                                                            );
                                                        @endphp
                                                        <option value="{{ $carrera->id }}" @if($esNueva) style="background-color: #fff4e6; color: #000000;" @endif>
                                                            @if($esNueva) 🔥 [¡NUEVA!] @endif {{ $carrera->nombre }}
                                                        </option>
                                                    @endforeach
                                                </optgroup>
                                            @endif
                                        @endforeach
                                    </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Turno</label>
                                <!-- Usando form-select limpio -->
                                <select class="form-select" id="turno_id" name="turno_id" required>
                                    <option value="">Seleccione</option>
                                    <!-- NOTE: Asegúrate de que tu backend (Laravel Blade) renderice correctamente el foreach -->
                                    @foreach(\App\Models\Turno::where('estado', 1)->orderBy('orden', 'asc')->get() as $turno)
                                        <option value="{{ $turno->id }}">
                                            @if($turno->nombre == 'Mañana') 🌅 @elseif($turno->nombre == 'Tarde') 🌆 @endif 
                                            {{ $turno->nombre }} ({{ $turno->getHorarioCompleto() }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tipo de Inscripción</label>
                                <select class="form-select" id="tipo_inscripcion" name="tipo_inscripcion" required>
                                    <option value="">Seleccione</option>
                                    <option value="Postulante">Postulante</option>
                                    <option value="Reforzamiento">Reforzamiento</option>
                                </select>
                            </div>
                            <div class="col-md-12 my-3">
                                <h6 class="form-section-title" style="border-bottom: 1px solid #d1d5db;">Ubicación del
                                    Colegio</h6>
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
                                    <input type="text" class="form-control" id="buscar_colegio"
                                        placeholder="Buscar colegio..." disabled>
                                    <button class="btn btn-outline-secondary" type="button" id="btnBuscarColegio"
                                        disabled>
                                        <i class="fas fa-search"></i> Buscar
                                    </button>
                                </div>
                                <div id="sugerencias-colegios" class="list-group mt-2 shadow-sm"
                                    style="max-height: 200px; overflow-y: auto;"></div>
                                <div id="colegio-seleccionado" class="alert alert-success mt-2 p-2 small"
                                    style="display: none;">
                                    <strong>Colegio seleccionado:</strong> <span
                                        id="nombre-colegio-seleccionado"></span>
                                </div>
                                <input type="hidden" id="centro_educativo_id" name="centro_educativo_id" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Año de Egreso</label>
                                <input type="number" class="form-control" id="anio_egreso" name="anio_egreso" min="1990"
                                    max="{{ date('Y') }}" required>
                            </div>
                        </div>
                    </div>

                    <!-- Paso 4: Documentos y Pago -->
                    <div class="step-content" data-step="4" style="display: none;">
                        <h5 class="form-section-title">Documentos y Pago</h5>

                        <!-- Sección de Pago -->
                        <div class="card mb-4 shadow-sm border-0">
                            <div class="card-header fw-bold text-white p-3"
                                style="background-color: var(--color-secundario);">
                                <i class="fas fa-money-check-alt me-1"></i> Validación de Pago Automática
                            </div>
                            <div class="card-body">
                                <div class="alert alert-warning py-2 small" style="font-size: 14px;">
                                    <i class="fas fa-exclamation-triangle me-1"></i>
                                    <strong>Nota:</strong> Los pagos se están buscando automáticamente con su DNI. Por
                                    favor espere o use la búsqueda manual si el DNI es diferente.
                                </div>

                                <div class="mb-3 d-flex align-items-center justify-content-between">
                                    <label for="voucher_secuencia" class="form-label mb-0 fw-bold">DNI de
                                        Búsqueda:</label>
                                    <div class="d-flex gap-2 align-items-center">
                                        <span class="badge bg-secondary py-2 px-3 fs-6" id="dni_display"
                                            style="min-width: 100px;">Cargando...</span>
                                        <button type="button" class="btn btn-outline-secondary btn-sm"
                                            onclick="habilitarBusquedaManual()" title="Usar otro DNI">
                                            <i class="fas fa-edit"></i> Editar
                                        </button>
                                    </div>
                                    <input type="hidden" class="form-control" id="voucher_secuencia"
                                        name="voucher_secuencia" readonly>
                                </div>

                                <div id="pago_feedback" class="mt-2" style="min-height: 20px;"></div>

                                <!-- CAMPOS OCULTOS REQUERIDOS POR LA BD -->
                                <input type="hidden" id="monto_matricula" name="monto_matricula">
                                <input type="hidden" id="monto_ensenanza" name="monto_ensenanza">
                                <input type="hidden" id="monto_total_pagado" name="monto_total_pagado">
                                <!-- Campo agregado -->

                                <div class="mt-2" style="display: none;" id="fecha_emision_container">
                                    <label>Fecha de Emisión (Voucher)</label>
                                    <input type="date" class="form-control" id="fecha_emision_voucher"
                                        name="fecha_emision_voucher">
                                </div>
                            </div>

                            <!-- Aquí se inyecta la lista de vouchers (voucher_details) -->
                            <div id="voucher_details" style="display: none; margin-top: 10px; padding: 0 15px 15px;">
                            </div>

                            <!-- Opción de Ingreso Manual -->
                            <div id="manual_voucher_section"
                                style="display: none; margin-top: 15px; padding: 0 15px 15px;">
                                <div class="alert alert-info py-2 small">
                                    <i class="fas fa-info-circle me-1"></i>
                                    <strong>Ingreso Manual:</strong> Complete los datos de su voucher de pago
                                    manualmente.
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Número de Voucher/Secuencia <span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="manual_voucher_numero"
                                            placeholder="Ej: 001234567">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Fecha de Emisión <span
                                                class="text-danger">*</span></label>
                                        <input type="date" class="form-control" id="manual_voucher_fecha">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Monto Matrícula (S/.) <span
                                                class="text-danger">*</span></label>
                                        <input type="number" class="form-control" id="manual_monto_matricula"
                                            step="0.01" min="0" placeholder="0.00">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Monto Enseñanza (S/.) <span
                                                class="text-danger">*</span></label>
                                        <input type="number" class="form-control" id="manual_monto_ensenanza"
                                            step="0.01" min="0" placeholder="0.00">
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <div class="alert alert-success py-2 small">
                                            <strong>Total a Pagar:</strong> S/. <span
                                                id="manual_total_display">0.00</span>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <button type="button" class="btn btn-success btn-sm"
                                            onclick="confirmarVoucherManual()">
                                            <i class="fas fa-check me-1"></i> Confirmar Datos
                                        </button>
                                        <button type="button" class="btn btn-secondary btn-sm"
                                            onclick="cancelarVoucherManual()">
                                            <i class="fas fa-times me-1"></i> Cancelar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Botón para generar Documento Autorellenado -->
                        <div class="alert alert-info d-flex align-items-center mb-4" style="border: none; border-left: 4px solid var(--color-acento); background: #e3f2fd;">
                            <i class="fas fa-file-pdf fs-3 me-3 text-primary"></i>
                            <div class="flex-grow-1">
                                <h6 class="mb-1" style="font-weight: 800;">¿Aún no tienes la Carta de Compromiso?</h6>
                                <p class="mb-2 small">Descarga tu pack de inscripción <strong>autorellenado</strong> con tus datos, fírmalo y súbelo a continuación.</p>
                                <button type="button" class="btn btn-primary btn-sm rounded-pill px-3" onclick="descargarPackInscripcion()">
                                    <i class="fas fa-download me-1"></i> GENERAR Y DESCARGAR PACK
                                </button>
                            </div>
                        </div>

                        <!-- Carga de Archivos -->
                        <h5 class="form-section-title">Carga de Documentos (PDF, JPG/PNG)</h5>
                        <div class="row g-2 justify-content-center">
                            <div class="col-6 col-md-4">
                                <label class="file-upload-card" for="foto">
                                    <i class="fas fa-camera"></i>
                                    <div class="card-title">Foto del Estudiante</div>
                                    <div class="card-subtitle">JPG/PNG</div>
                                    <div class="file-name">Sin archivo</div>
                                    <input type="file" id="foto" name="foto" accept="image/*" required
                                        onchange="updateFileName(this)">
                                </label>
                            </div>
                            <div class="col-6 col-md-4">
                                <label class="file-upload-card" for="dni_pdf">
                                    <i class="fas fa-id-card"></i>
                                    <div class="card-title">DNI Escaneado</div>
                                    <div class="card-subtitle">PDF</div>
                                    <div class="file-name">Sin archivo</div>
                                    <input type="file" id="dni_pdf" name="dni_pdf" accept="application/pdf" required
                                        onchange="updateFileName(this)">
                                </label>
                            </div>
                            <div class="col-6 col-md-4">
                                <label class="file-upload-card" for="certificado_estudios">
                                    <i class="fas fa-certificate"></i>
                                    <div class="card-title">Certificado de Estudios</div>
                                    <div class="card-subtitle">PDF</div>
                                    <div class="file-name">Sin archivo</div>
                                    <input type="file" id="certificado_estudios" name="certificado_estudios"
                                        accept="application/pdf" required onchange="updateFileName(this)">
                                </label>
                            </div>
                            <div class="col-6 col-md-4">
                                <label class="file-upload-card" for="voucher_pago">
                                    <i class="fas fa-receipt"></i>
                                    <div class="card-title">Voucher Escaneado</div>
                                    <div class="card-subtitle">PDF/Imagen</div>
                                    <div class="file-name">Sin archivo</div>
                                    <input type="file" id="voucher_pago" name="voucher_pago"
                                        accept="image/*,application/pdf" required onchange="updateFileName(this)">
                                </label>
                            </div>
                            <div class="col-6 col-md-4">
                                <label class="file-upload-card" for="carta_compromiso">
                                    <i class="fas fa-file-contract"></i>
                                    <div class="card-title">Carta de Compromiso</div>
                                    <div class="card-subtitle">PDF</div>
                                    <div class="file-name">Sin archivo</div>
                                    <input type="file" id="carta_compromiso" name="carta_compromiso"
                                        accept="application/pdf" onchange="updateFileName(this)">
                                </label>
                            </div>
                            <div class="col-6 col-md-4">
                                <label class="file-upload-card" for="constancia_estudios">
                                    <i class="fas fa-file-invoice"></i>
                                    <div class="card-title">Constancia de Estudios</div>
                                    <div class="card-subtitle">PDF</div>
                                    <div class="file-name">Sin archivo</div>
                                    <input type="file" id="constancia_estudios" name="constancia_estudios"
                                        accept="application/pdf" onchange="updateFileName(this)">
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Paso 5: Confirmación -->
                    <div class="step-content" data-step="5" style="display: none; text-align: center;">
                        <i class="fas fa-check-circle"
                            style="font-size: 50px; color: var(--color-principal); margin-bottom: 20px;"></i>
                        <h4>¡Todo listo!</h4>
                        <p>Por favor revise sus datos antes de enviar.</p>
                        <div id="resumen_final"
                            style="text-align: left; background: var(--color-fondo-claro); padding: 15px; margin: 20px 0; border-radius: 8px; max-height: 300px; overflow-y: auto;">
                        </div>

                        <div class="form-check mb-2" style="text-align: left;">
                            <input class="form-check-input" type="checkbox" id="aceptoTerminos" required>
                            <label class="form-check-label" for="aceptoTerminos">
                                Acepto los <a href="javascript:void(0)"
                                    onclick="Swal.fire('Términos y Condiciones', 'Todas las postulaciones están sujetas a la veracidad de la información proporcionada y al cumplimiento del reglamento interno del CEPRE UNAMAD.', 'info')"
                                    style="color: var(--color-principal); text-decoration: underline;">términos y
                                    condiciones</a> y las políticas de privacidad.
                            </label>
                        </div>

                        <div class="form-check mb-4" style="text-align: left;">
                            <input class="form-check-input" type="checkbox" id="confirmarDatos" required>
                            <label class="form-check-label" for="confirmarDatos">
                                Declaro que toda la información consignada en este formulario es verídica.
                            </label>
                        </div>

                        <button type="submit" class="btn btn-success btn-next-prev"
                            style="background-color: var(--color-principal); border-color: var(--color-principal); padding: 12px 30px; font-size: 16px;">
                            ENVIAR POSTULACIÓN
                        </button>
                    </div>

                    <!-- Navegación -->
                    <div class="wizard-buttons"
                        style="margin-top: 30px; display: flex; justify-content: space-between;">
                        <button type="button" class="btn btn-secondary btn-next-prev" id="prevBtn"
                            onclick="nextPrev(-1)" style="display: none;">Anterior</button>
                        <button type="button" class="btn btn-primary btn-next-prev" id="nextBtn"
                            onclick="nextPrev(1)">Siguiente</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>