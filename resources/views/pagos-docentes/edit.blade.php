@extends('layouts.app')

@push('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.8/css/line.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
    :root {
        --primary-gradient: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
        --secondary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        --accent-color: #3b82f6;
        --success-color: #10b981;
        --warning-color: #f59e0b;
        --danger-color: #ef4444;
        --text-primary: #1f2937;
        --text-secondary: #6b7280;
        --bg-light: #f8fafc;
        --bg-white: #ffffff;
        --border-color: #e5e7eb;
        --shadow-sm: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
        --shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        --shadow-xl: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }

    * {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    }

    body {
        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
        min-height: 100vh;
    }

    /* Header Principal */
    .edit-header {
        background: var(--warning-color);
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: white;
        padding: 3rem 0;
        position: relative;
        overflow: hidden;
    }

    .edit-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse"><path d="M 10 0 L 0 0 0 10" fill="none" stroke="rgba(255,255,255,0.05)" stroke-width="1"/></pattern></defs><rect width="100" height="100" fill="url(%23grid)"/></svg>');
        opacity: 0.3;
    }

    .edit-header .container {
        position: relative;
        z-index: 2;
    }

    .header-content {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 2rem;
    }

    .header-info h1 {
        font-size: 2.25rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
        letter-spacing: -0.025em;
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .header-info .subtitle {
        font-size: 1.125rem;
        opacity: 0.9;
        font-weight: 400;
        margin-bottom: 1rem;
    }

    .breadcrumb-custom {
        background: rgba(255, 255, 255, 0.1);
        padding: 0.5rem 1rem;
        border-radius: 2rem;
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        margin-bottom: 1rem;
    }

    .breadcrumb-custom .breadcrumb-item {
        color: rgba(255, 255, 255, 0.8);
        font-size: 0.875rem;
    }

    .breadcrumb-custom .breadcrumb-item.active {
        color: white;
        font-weight: 500;
    }

    .breadcrumb-custom .breadcrumb-item a {
        color: rgba(255, 255, 255, 0.8);
        text-decoration: none;
        transition: color 0.3s ease;
    }

    .breadcrumb-custom .breadcrumb-item a:hover {
        color: white;
    }

    /* Información del Docente */
    .teacher-info-banner {
        background: rgba(255, 255, 255, 0.15);
        padding: 1.5rem;
        border-radius: 1rem;
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .teacher-avatar-large {
        width: 4rem;
        height: 4rem;
        border-radius: 1rem;
        background: rgba(255, 255, 255, 0.2);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 1.5rem;
        box-shadow: var(--shadow-md);
    }

    .teacher-details h3 {
        font-size: 1.25rem;
        font-weight: 600;
        margin: 0 0 0.25rem 0;
    }

    .teacher-meta {
        opacity: 0.8;
        font-size: 0.9rem;
    }

    /* Card Principal del Formulario */
    .form-card {
        background: var(--bg-white);
        border-radius: 1.25rem;
        box-shadow: var(--shadow-xl);
        border: 1px solid var(--border-color);
        overflow: hidden;
        margin: -3rem auto 3rem;
        position: relative;
        z-index: 10;
        max-width: 800px;
    }

    .form-card-header {
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        padding: 2rem;
        border-bottom: 1px solid var(--border-color);
        text-align: center;
    }

    .form-card-header h2 {
        font-size: 1.75rem;
        font-weight: 700;
        color: #92400e;
        margin: 0 0 0.5rem 0;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.75rem;
    }

    .form-card-header .form-icon {
        width: 3rem;
        height: 3rem;
        border-radius: 0.75rem;
        background: var(--warning-color);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }

    .form-card-header p {
        color: #92400e;
        margin: 0;
        font-size: 1rem;
        font-weight: 500;
    }

    /* Contenido del Formulario */
    .form-card-body {
        padding: 2.5rem;
    }

    /* Grupos de Formulario */
    .form-group {
        margin-bottom: 2rem;
        position: relative;
    }

    .form-label {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-weight: 600;
        color: var(--text-primary);
        margin-bottom: 0.75rem;
        font-size: 0.95rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .form-label .label-icon {
        color: var(--warning-color);
        font-size: 1rem;
    }

    .required-asterisk {
        color: var(--danger-color);
        margin-left: 0.25rem;
    }

    .optional-badge {
        background: rgba(107, 114, 128, 0.1);
        color: var(--text-secondary);
        padding: 0.125rem 0.5rem;
        border-radius: 0.375rem;
        font-size: 0.75rem;
        font-weight: 500;
        text-transform: lowercase;
    }

    /* Inputs Modernos */
    .form-input-container {
        position: relative;
    }

    .form-control-modern {
        width: 100%;
        padding: 1rem 1rem 1rem 3rem;
        border: 2px solid var(--border-color);
        border-radius: 0.75rem;
        font-size: 0.95rem;
        background: var(--bg-white);
        transition: all 0.3s ease;
        box-shadow: var(--shadow-sm);
        color: var(--text-primary);
        font-weight: 500;
    }

    .form-control-modern:focus {
        outline: none;
        border-color: var(--warning-color);
        box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.1);
        transform: translateY(-1px);
    }

    .form-control-modern:hover {
        border-color: #cbd5e1;
    }

    .input-icon {
        position: absolute;
        left: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-secondary);
        font-size: 1.125rem;
        z-index: 2;
        transition: color 0.3s ease;
    }

    .form-control-modern:focus + .input-icon {
        color: var(--warning-color);
    }

    /* Select Personalizado */
    .select-container {
        position: relative;
    }

    .select-container::after {
        content: '\f078';
        font-family: 'Font Awesome 6 Free';
        font-weight: 900;
        position: absolute;
        right: 1rem;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-secondary);
        pointer-events: none;
        z-index: 2;
    }

    select.form-control-modern {
        appearance: none;
        -webkit-appearance: none;
        -moz-appearance: none;
        cursor: pointer;
        padding-right: 3rem;
    }

    /* Input de Número */
    .currency-input {
        position: relative;
    }

    .currency-symbol {
        position: absolute;
        left: 3.5rem;
        top: 50%;
        transform: translateY(-50%);
        color: var(--success-color);
        font-weight: 700;
        font-size: 0.9rem;
        z-index: 2;
    }

    .form-control-modern.currency {
        padding-left: 5rem;
    }

    /* Inputs de Fecha */
    .date-input {
        position: relative;
    }

    .form-control-modern[type="date"] {
        color: var(--text-primary);
        cursor: pointer;
    }

    .form-control-modern[type="date"]::-webkit-calendar-picker-indicator {
        opacity: 0;
        position: absolute;
        right: 1rem;
        cursor: pointer;
    }

    /* Alertas de Error Mejoradas */
    .alert-danger-modern {
        background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
        border: 1px solid #fca5a5;
        border-left: 4px solid var(--danger-color);
        border-radius: 0.75rem;
        padding: 1.25rem 1.5rem;
        margin-bottom: 2rem;
        box-shadow: var(--shadow-md);
    }

    .alert-danger-modern .alert-title {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-weight: 700;
        color: #991b1b;
        margin-bottom: 0.75rem;
        font-size: 1rem;
    }

    .alert-danger-modern ul {
        margin: 0;
        padding-left: 1.25rem;
        list-style: none;
    }

    .alert-danger-modern li {
        color: #991b1b;
        font-weight: 500;
        margin-bottom: 0.5rem;
        position: relative;
    }

    .alert-danger-modern li::before {
        content: '\f06a';
        font-family: 'Font Awesome 6 Free';
        font-weight: 900;
        position: absolute;
        left: -1.25rem;
        color: var(--danger-color);
        font-size: 0.875rem;
    }

    /* Botones Modernos */
    .button-group {
        display: flex;
        gap: 1rem;
        justify-content: center;
        align-items: center;
        margin-top: 3rem;
        flex-wrap: wrap;
    }

    .btn-modern {
        padding: 1rem 2.5rem;
        border-radius: 0.75rem;
        font-weight: 600;
        font-size: 0.95rem;
        border: none;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        min-width: 150px;
        justify-content: center;
        box-shadow: var(--shadow-md);
    }

    .btn-modern:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-lg);
    }

    .btn-warning-modern {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: white;
    }

    .btn-warning-modern:hover {
        background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
        color: white;
    }

    .btn-secondary-modern {
        background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
        color: var(--text-secondary);
        border: 1px solid var(--border-color);
    }

    .btn-secondary-modern:hover {
        background: linear-gradient(135deg, #e2e8f0 0%, #cbd5e1 100%);
        color: var(--text-primary);
    }

    /* Estados de Loading */
    .btn-loading {
        position: relative;
        pointer-events: none;
    }

    .btn-loading::after {
        content: '';
        position: absolute;
        width: 1rem;
        height: 1rem;
        top: 50%;
        left: 50%;
        margin-left: -0.5rem;
        margin-top: -0.5rem;
        border: 2px solid transparent;
        border-top: 2px solid currentColor;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    /* Validación Visual */
    .form-control-modern.is-valid {
        border-color: var(--success-color);
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
    }

    .form-control-modern.is-invalid {
        border-color: var(--danger-color);
        box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
    }

    .valid-feedback {
        color: var(--success-color);
        font-size: 0.875rem;
        font-weight: 500;
        margin-top: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }

    .invalid-feedback {
        color: var(--danger-color);
        font-size: 0.875rem;
        font-weight: 500;
        margin-top: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }

    /* Información de Pago Actual */
    .current-payment-info {
        background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
        border: 1px solid #bfdbfe;
        border-left: 4px solid var(--accent-color);
        border-radius: 0.75rem;
        padding: 1.5rem;
        margin-bottom: 2rem;
    }

    .current-payment-info h4 {
        color: #1e40af;
        font-weight: 700;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .payment-details {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
    }

    .payment-detail-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: #1e40af;
        font-weight: 500;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .edit-header {
            padding: 2rem 0;
        }

        .header-info h1 {
            font-size: 1.875rem;
        }

        .form-card {
            margin: -2rem 1rem 2rem;
        }

        .form-card-body {
            padding: 1.5rem;
        }

        .button-group {
            flex-direction: column;
        }

        .btn-modern {
            width: 100%;
        }

        .teacher-info-banner {
            flex-direction: column;
            text-align: center;
        }

        .payment-details {
            grid-template-columns: 1fr;
        }
    }

    /* Animaciones */
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

    .fade-in-up {
        animation: fadeInUp 0.6s ease-out;
    }

    @keyframes slideInRight {
        from {
            opacity: 0;
            transform: translateX(30px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    .slide-in-right {
        animation: slideInRight 0.6s ease-out;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header del Formulario -->
    <div class="edit-header">
        <div class="container">
            <div class="header-content">
                <div class="header-info">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb breadcrumb-custom mb-3">
                            <li class="breadcrumb-item">
                                <i class="uil uil-estate me-1"></i>Centro Preuniversitario
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('pagos-docentes.index') }}">Pagos a Docentes</a>
                            </li>
                            <li class="breadcrumb-item active">Editar Pago</li>
                        </ol>
                    </nav>
                    <h1>
                        <i class="uil uil-edit-alt"></i>
                        Editar Configuración de Pago
                    </h1>
                    <p class="subtitle">
                        Modifique la estructura de remuneración del docente seleccionado
                    </p>
                </div>
                <div class="teacher-info-banner">
                    <div class="teacher-avatar-large">
                        {{ substr($pago->docente->nombre ?? 'N', 0, 1) }}
                    </div>
                    <div class="teacher-details">
                        <h3>
                            @if($pago->docente)
                                {{ $pago->docente->nombre }} {{ $pago->docente->apellido_paterno }}
                            @else
                                Docente no encontrado
                            @endif
                        </h3>
                        <div class="teacher-meta">
                            <i class="uil uil-user me-1"></i>
                            ID: {{ $pago->docente->id ?? '---' }} • 
                            <i class="uil uil-money-bill me-1"></i>
                            Tarifa actual: S/ {{ number_format($pago->tarifa_por_hora, 2) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Card del Formulario -->
        <div class="form-card fade-in-up">
            <!-- Header del Card -->
            <div class="form-card-header">
                <h2>
                    <div class="form-icon">
                        <i class="uil uil-money-bill"></i>
                    </div>
                    Actualizar Información de Pago
                </h2>
                <p>Modifique los datos de remuneración según sea necesario</p>
            </div>

            <!-- Cuerpo del Formulario -->
            <div class="form-card-body">
                <!-- Información del Pago Actual -->
                <div class="current-payment-info slide-in-right">
                    <h4>
                        <i class="uil uil-info-circle"></i>
                        Información Actual del Pago
                    </h4>
                    <div class="payment-details">
                        <div class="payment-detail-item">
                            <i class="uil uil-money-bill"></i>
                            <span>S/ {{ number_format($pago->tarifa_por_hora, 2) }} por hora</span>
                        </div>
                        <div class="payment-detail-item">
                            <i class="uil uil-calendar-alt"></i>
                            <span>Desde: {{ \Carbon\Carbon::parse($pago->fecha_inicio)->format('d/m/Y') }}</span>
                        </div>
                        <div class="payment-detail-item">
                            <i class="uil uil-calender"></i>
                            <span>
                                @if($pago->fecha_fin)
                                    Hasta: {{ \Carbon\Carbon::parse($pago->fecha_fin)->format('d/m/Y') }}
                                @else
                                    Estado: Activo
                                @endif
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Alertas de Error -->
                @if ($errors->any())
                    <div class="alert-danger-modern slide-in-right">
                        <div class="alert-title">
                            <i class="uil uil-exclamation-triangle"></i>
                            Se encontraron errores en el formulario
                        </div>
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Formulario Principal -->
                <form action="{{ route('pagos-docentes.update', $pago->id) }}" method="POST" id="editPaymentForm" novalidate>
                    @csrf
                    @method('PUT')

                    <!-- Selección de Docente -->
                    <div class="form-group">
                        <label for="docente_id" class="form-label">
                            <i class="uil uil-user label-icon"></i>
                            Docente Asignado
                            <span class="required-asterisk">*</span>
                        </label>
                        <div class="form-input-container">
                            <div class="select-container">
                                <select name="docente_id" id="docente_id" class="form-control-modern" required>
                                    <option value="">Seleccione un docente del centro</option>
                                    @foreach($docentes as $docente)
                                        <option value="{{ $docente->id }}" {{ $pago->docente_id == $docente->id ? 'selected' : '' }}>
                                            {{ $docente->nombre }} {{ $docente->apellido_paterno }}
                                        </option>
                                    @endforeach
                                </select>
                                <i class="uil uil-user input-icon"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Selección de Ciclo -->
                    <div class="form-group">
                        <label for="ciclo_id" class="form-label">
                            <i class="uil uil-calendar-sync label-icon"></i>
                            Ciclo Académico
                            <span class="required-asterisk">*</span>
                        </label>
                        <div class="form-input-container">
                            <div class="select-container">
                                <select name="ciclo_id" id="ciclo_id" class="form-control-modern" required>
                                    <option value="">Seleccione el ciclo correspondiente</option>
                                    @foreach($ciclos as $ciclo)
                                        <option value="{{ $ciclo->id }}" 
                                                data-inicio="{{ $ciclo->fecha_inicio }}" 
                                                data-fin="{{ $ciclo->fecha_fin }}" 
                                                {{ $pago->ciclo_id == $ciclo->id ? 'selected' : '' }}>
                                            {{ $ciclo->nombre }} ({{ \Carbon\Carbon::parse($ciclo->fecha_inicio)->format('d/m/Y') }} - {{ $ciclo->fecha_fin ? \Carbon\Carbon::parse($ciclo->fecha_fin)->format('d/m/Y') : '...' }})
                                        </option>
                                    @endforeach
                                </select>
                                <i class="uil uil-calendar-sync input-icon"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Tarifa por Hora -->
                    <div class="form-group">
                        <label for="tarifa_por_hora" class="form-label">
                            <i class="uil uil-money-bill label-icon"></i>
                            Tarifa por Hora
                            <span class="required-asterisk">*</span>
                        </label>
                        <div class="form-input-container currency-input">
                            <input type="number" 
                                   step="0.01" 
                                   name="tarifa_por_hora" 
                                   id="tarifa_por_hora" 
                                   class="form-control-modern currency" 
                                   value="{{ old('tarifa_por_hora', $pago->tarifa_por_hora) }}" 
                                   placeholder="0.00"
                                   min="0"
                                   max="999.99"
                                   required>
                            <i class="uil uil-money-bill input-icon"></i>
                            <span class="currency-symbol">S/</span>
                        </div>
                        <div class="valid-feedback" id="tarifaFeedback" style="display: none;">
                            <i class="uil uil-check-circle"></i>
                            Tarifa válida establecida
                        </div>
                    </div>

                    <!-- Fecha de Inicio -->
                    <div class="form-group">
                        <label for="fecha_inicio" class="form-label">
                            <i class="uil uil-calendar-alt label-icon"></i>
                            Fecha de Inicio
                            <span class="required-asterisk">*</span>
                        </label>
                        <div class="form-input-container date-input">
                            <input type="date" 
                                   name="fecha_inicio" 
                                   id="fecha_inicio" 
                                   class="form-control-modern" 
                                   value="{{ old('fecha_inicio', $pago->fecha_inicio) }}" 
                                   required>
                            <i class="uil uil-calendar-alt input-icon"></i>
                        </div>
                    </div>

                    <!-- Fecha de Fin -->
                    <div class="form-group">
                        <label for="fecha_fin" class="form-label">
                            <i class="uil uil-calender label-icon"></i>
                            Fecha de Fin
                            <span class="optional-badge">opcional</span>
                        </label>
                        <div class="form-input-container date-input">
                            <input type="date" 
                                   name="fecha_fin" 
                                   id="fecha_fin" 
                                   class="form-control-modern" 
                                   value="{{ old('fecha_fin', $pago->fecha_fin) }}">
                            <i class="uil uil-calender input-icon"></i>
                        </div>
                        <small class="form-text text-muted mt-2">
                            <i class="uil uil-info-circle me-1"></i>
                            Deje vacío si el pago continúa activo indefinidamente
                        </small>
                    </div>

                    <!-- Botones de Acción -->
                    <div class="button-group">
                        <button type="submit" class="btn-modern btn-warning-modern" id="updateBtn">
                            <i class="uil uil-check-circle"></i>
                            Actualizar Pago
                        </button>
                        <a href="{{ route('pagos-docentes.index') }}" class="btn-modern btn-secondary-modern">
                            <i class="uil uil-times-circle"></i>
                            Cancelar Cambios
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('editPaymentForm');
    const updateBtn = document.getElementById('updateBtn');
    const docenteSelect = document.getElementById('docente_id');
    const cicloSelect = document.getElementById('ciclo_id');
    const tarifaInput = document.getElementById('tarifa_por_hora');
    const fechaInicioInput = document.getElementById('fecha_inicio');
    const fechaFinInput = document.getElementById('fecha_fin');
    const tarifaFeedback = document.getElementById('tarifaFeedback');

    // Función para limpiar validación
    function clearValidation(input) {
        input.classList.remove('is-valid', 'is-invalid');
    }

    // Validación en tiempo real
    function validateField(field) {
        if (field.hasAttribute('required')) {
            if (field.value.trim() !== '') {
                field.classList.remove('is-invalid');
                field.classList.add('is-valid');
            } else {
                clearValidation(field);
            }
        } else {
            // Para campos opcionales, siempre válidos si están llenos o vacíos
            if (field.value.trim() !== '') {
                field.classList.remove('is-invalid');
                field.classList.add('is-valid');
            } else {
                clearValidation(field);
            }
        }
    }

    // Validación de tarifa
    function validateTarifa() {
        const value = parseFloat(tarifaInput.value);
        
        if (value && value > 0) {
            tarifaInput.classList.remove('is-invalid');
            tarifaInput.classList.add('is-valid');
            tarifaFeedback.style.display = 'flex';
        } else {
            clearValidation(tarifaInput);
            tarifaFeedback.style.display = 'none';
        }
    }

    // Validación de fechas
    function validateDates() {
        const fechaInicio = new Date(fechaInicioInput.value);
        const fechaFin = fechaFinInput.value ? new Date(fechaFinInput.value) : null;

        // Validar fecha de inicio
        if (fechaInicioInput.value) {
            fechaInicioInput.classList.remove('is-invalid');
            fechaInicioInput.classList.add('is-valid');
        }

        // Validar fecha de fin si se proporciona
        if (fechaFin) {
            if (fechaFin >= fechaInicio) {
                fechaFinInput.classList.remove('is-invalid');
                fechaFinInput.classList.add('is-valid');
            } else {
                fechaFinInput.classList.remove('is-valid');
                fechaFinInput.classList.add('is-invalid');
            }
        } else {
            clearValidation(fechaFinInput);
        }
    }

    // Event listeners
    docenteSelect.addEventListener('change', function() {
        validateField(this);
    });

    cicloSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        if (selectedOption && selectedOption.dataset.inicio) {
            fechaInicioInput.value = selectedOption.dataset.inicio;
            if (selectedOption.dataset.fin && selectedOption.dataset.fin !== 'null') {
                fechaFinInput.value = selectedOption.dataset.fin;
            } else {
                fechaFinInput.value = '';
            }
            validateDates();
        }
        validateField(this);
    });

    tarifaInput.addEventListener('input', function() {
        validateTarifa();
    });

    tarifaInput.addEventListener('blur', function() {
        validateTarifa();
    });

    fechaInicioInput.addEventListener('change', function() {
        validateDates();
    });

    fechaFinInput.addEventListener('change', function() {
        validateDates();
    });

    // Validación del formulario
    form.addEventListener('submit', function(e) {
        let isValid = true;
        const requiredFields = form.querySelectorAll('[required]');

        // Limpiar validaciones previas
        form.querySelectorAll('.form-control-modern').forEach(field => clearValidation(field));

        // Validar campos requeridos
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                field.classList.add('is-invalid');
                isValid = false;
            } else {
                field.classList.add('is-valid');
            }
        });

        // Validar tarifa específicamente
        const tarifaValue = parseFloat(tarifaInput.value);
        if (!tarifaValue || tarifaValue <= 0) {
            tarifaInput.classList.add('is-invalid');
            isValid = false;
        }

        // Validar fechas
        const fechaInicio = new Date(fechaInicioInput.value);
        const fechaFin = fechaFinInput.value ? new Date(fechaFinInput.value) : null;

        if (fechaFin && fechaFin < fechaInicio) {
            fechaFinInput.classList.add('is-invalid');
            isValid = false;
        }

        if (!isValid) {
            e.preventDefault();
            
            // Scroll al primer campo inválido
            const firstInvalid = form.querySelector('.is-invalid');
            if (firstInvalid) {
                firstInvalid.scrollIntoView({ 
                    behavior: 'smooth', 
                    block: 'center' 
                });
                firstInvalid.focus();
            }
            return false;
        }

        // Mostrar loading en botón
        updateBtn.classList.add('btn-loading');
        updateBtn.disabled = true;
        updateBtn.innerHTML = '<span style="opacity: 0;">Actualizando...</span>';
    });

    // Animaciones de entrada
    const formGroups = document.querySelectorAll('.form-group');
    formGroups.forEach((group, index) => {
        group.style.opacity = '0';
        group.style.transform = 'translateY(20px)';
        
        setTimeout(() => {
            group.style.transition = 'all 0.5s cubic-bezier(0.4, 0, 0.2, 1)';
            group.style.opacity = '1';
            group.style.transform = 'translateY(0)';
        }, (index + 1) * 150); // +1 para dar tiempo al banner de información
    });

    // Efectos de focus mejorados
    const inputs = document.querySelectorAll('.form-control-modern');
    inputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.parentElement.style.transform = 'scale(1.02)';
        });
        
        input.addEventListener('blur', function() {
            this.parentElement.style.transform = 'scale(1)';
        });
    });

    // Formateo de tarifa mientras escribe
    tarifaInput.addEventListener('input', function() {
        let value = this.value;
        
        // Permitir solo números y punto decimal
        value = value.replace(/[^0-9.]/g, '');
        
        // Evitar múltiples puntos decimales
        const parts = value.split('.');
        if (parts.length > 2) {
            value = parts[0] + '.' + parts.slice(1).join('');
        }
        
        // Limitar decimales a 2
        if (parts[1] && parts[1].length > 2) {
            value = parts[0] + '.' + parts[1].substring(0, 2);
        }
        
        this.value = value;
    });

    // Validación inicial para campos con valores pre-cargados
    setTimeout(() => {
        validateField(docenteSelect);
        validateTarifa();
        validateDates();
    }, 500);
});
</script>
@endpush
@endsection