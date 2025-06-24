@extends('layouts.app')

@section('title', 'Horario Académico - Centro Preuniversitario')

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
    }

    * {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    }


    .main-container {
        background: var(--bg-white);
        max-width: 1200px;
        margin: 0 auto;
        border-radius: 1.5rem;
        box-shadow: var(--shadow-xl);
        border: 1px solid var(--border-color);
        overflow: hidden;
        position: relative;
    }

    /* Header Universitario Mejorado */
    .header-universidad {
        background: var(--primary-gradient);
        color: white;
        padding: 3rem 2rem;
        position: relative;
        overflow: hidden;
        text-align: center;
    }

    .header-universidad::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse"><path d="M 10 0 L 0 0 0 10" fill="none" stroke="rgba(255,255,255,0.05)" stroke-width="1"/></pattern></defs><rect width="100" height="100" fill="url(%23grid)"/></svg>');
        opacity: 0.3;
    }

    .header-content {
        position: relative;
        z-index: 2;
    }

    .ciclo-badge {
        position: absolute;
        top: 2rem;
        right: 2rem;
        background: rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(10px);
        padding: 0.875rem 1.5rem;
        border-radius: 2rem;
        font-weight: 700;
        font-size: 0.875rem;
        border: 1px solid rgba(255, 255, 255, 0.3);
        text-transform: uppercase;
        letter-spacing: 0.05em;
        box-shadow: var(--shadow-md);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .universidad-logo {
        width: 4rem;
        height: 4rem;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 1rem;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.5rem;
        font-size: 2rem;
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.3);
    }

    .header-universidad h1 {
        font-size: 2.25rem;
        font-weight: 800;
        margin: 0 0 1rem 0;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        line-height: 1.2;
    }

    .header-universidad h2 {
        font-size: 1rem;
        font-weight: 400;
        margin: 0.5rem 0;
        opacity: 0.9;
        line-height: 1.5;
        font-style: italic;
    }

    .header-universidad h3 {
        font-size: 1.5rem;
        font-weight: 700;
        margin: 2rem 0 1rem 0;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        color: #fbbf24;
        text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
    }

    /* Sección de Información Modernizada */
    .info-section {
        padding: 2.5rem;
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        border-bottom: 1px solid var(--border-color);
    }

    .btn-actions {
        display: flex;
        gap: 1rem;
        justify-content: center;
        margin-bottom: 2rem;
        flex-wrap: wrap;
    }

    .btn-modern {
        padding: 0.875rem 1.75rem;
        border-radius: 0.75rem;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.3s ease;
        border: none;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.875rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        box-shadow: var(--shadow-md);
        position: relative;
        overflow: hidden;
    }

    .btn-modern::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
        transition: left 0.5s;
    }

    .btn-modern:hover::before {
        left: 100%;
    }

    .btn-modern:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-lg);
    }

    .btn-primary-modern {
        background: var(--primary-gradient);
        color: white;
    }

    .btn-secondary-modern {
        background: var(--bg-white);
        color: var(--text-secondary);
        border: 2px solid var(--border-color);
    }

    .btn-success-modern {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
    }

    .info-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
    }

    .info-card {
        background: var(--bg-white);
        padding: 1.75rem;
        border-radius: 1rem;
        box-shadow: var(--shadow-lg);
        border: 1px solid var(--border-color);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .info-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 4px;
        background: var(--accent-color);
    }

    .info-card:nth-child(2)::before {
        background: var(--success-color);
    }

    .info-card:nth-child(3)::before {
        background: var(--warning-color);
    }

    .info-card:hover {
        transform: translateY(-4px);
        box-shadow: var(--shadow-xl);
    }

    .info-card h4 {
        margin: 0 0 0.75rem 0;
        font-size: 0.875rem;
        text-transform: uppercase;
        color: var(--text-secondary);
        font-weight: 600;
        letter-spacing: 0.05em;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .info-card p {
        margin: 0;
        font-size: 1.375rem;
        font-weight: 700;
        color: var(--text-primary);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    /* Contenedor de Horarios */
    .schedule-container {
        padding: 2.5rem;
    }

    .schedule-header {
        text-align: center;
        margin-bottom: 2rem;
        padding: 1.5rem;
        background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
        border-radius: 1rem;
        border: 1px solid var(--border-color);
    }

    .schedule-header h4 {
        margin: 0;
        font-size: 1.375rem;
        font-weight: 700;
        text-transform: uppercase;
        color: var(--text-primary);
        letter-spacing: 0.05em;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 1rem;
    }

    /* Tabla de Horarios Modernizada */
    .tabla-horario {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        background: var(--bg-white);
        border-radius: 1rem;
        overflow: hidden;
        box-shadow: var(--shadow-xl);
        margin-bottom: 3rem;
        border: 1px solid var(--border-color);
    }

    .tabla-horario th {
        background: var(--primary-gradient);
        color: white;
        padding: 1.5rem 1rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        font-size: 0.875rem;
        border: none;
        position: relative;
        text-align: center;
    }

    .tabla-horario th:first-child {
        border-top-left-radius: 1rem;
    }

    .tabla-horario th:last-child {
        border-top-right-radius: 1rem;
    }

    .tabla-horario th::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: linear-gradient(90deg, #fbbf24, #f59e0b, #d97706);
    }

    .tabla-horario td {
        padding: 0;
        border: 1px solid #f1f5f9;
        vertical-align: middle;
        height: 90px;
        position: relative;
        background: var(--bg-white);
    }

    .tabla-horario .hora-col {
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        font-weight: 700;
        width: 120px;
        color: var(--text-primary);
        font-size: 0.875rem;
        text-align: center;
        border-right: 2px solid var(--border-color);
        position: relative;
    }

    .hora-col::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 4px;
        background: var(--accent-color);
    }

    /* Celdas de Materias Mejoradas */
    .celda-materia {
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        padding: 0.75rem;
        text-align: center;
        border-radius: 0.5rem;
        margin: 0.25rem;
        transition: all 0.3s ease;
        cursor: pointer;
        position: relative;
        overflow: hidden;
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    .celda-materia::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
        transition: left 0.5s;
    }

    .celda-materia:hover::before {
        left: 100%;
    }

    .celda-materia:hover {
        transform: scale(1.03);
        box-shadow: var(--shadow-xl);
        z-index: 10;
    }

    .nombre-materia {
        font-size: 0.75rem;
        font-weight: 700;
        margin-bottom: 0.25rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        line-height: 1.2;
    }

    .nombre-docente {
        font-size: 0.625rem;
        font-weight: 500;
        opacity: 0.9;
        line-height: 1.1;
    }

    /* Colores Modernos para Materias */
    .razonamiento-matematico {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
        box-shadow: 0 4px 15px rgba(16, 185, 129, 0.4);
    }

    .razonamiento-verbal {
        background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
        color: white;
        box-shadow: 0 4px 15px rgba(59, 130, 246, 0.4);
    }

    .algebra {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: white;
        box-shadow: 0 4px 15px rgba(245, 158, 11, 0.4);
    }

    .geometria {
        background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
        color: white;
        box-shadow: 0 4px 15px rgba(139, 92, 246, 0.4);
    }

    .aritmetica {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: white;
        box-shadow: 0 4px 15px rgba(239, 68, 68, 0.4);
    }

    .economia {
        background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
        color: white;
        box-shadow: 0 4px 15px rgba(6, 182, 212, 0.4);
    }

    .fisica {
        background: linear-gradient(135deg, #84cc16 0%, #65a30d 100%);
        color: white;
        box-shadow: 0 4px 15px rgba(132, 204, 22, 0.4);
    }

    .educacion-civica {
        background: linear-gradient(135deg, #ec4899 0%, #db2777 100%);
        color: white;
        box-shadow: 0 4px 15px rgba(236, 72, 153, 0.4);
    }

    .trigonometria {
        background: linear-gradient(135deg, #14b8a6 0%, #0d9488 100%);
        color: white;
        box-shadow: 0 4px 15px rgba(20, 184, 166, 0.4);
    }

    .historia {
        background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
        color: white;
        box-shadow: 0 4px 15px rgba(249, 115, 22, 0.4);
    }

    .biologia {
        background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
        color: white;
        box-shadow: 0 4px 15px rgba(34, 197, 94, 0.4);
    }

    .comprension-lectora {
        background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
        color: white;
        box-shadow: 0 4px 15px rgba(99, 102, 241, 0.4);
    }

    .quimica {
        background: linear-gradient(135deg, #eab308 0%, #ca8a04 100%);
        color: white;
        box-shadow: 0 4px 15px rgba(234, 179, 8, 0.4);
    }

    .lenguaje {
        background: linear-gradient(135deg, #a855f7 0%, #9333ea 100%);
        color: white;
        box-shadow: 0 4px 15px rgba(168, 85, 247, 0.4);
    }

    .otros {
        background: linear-gradient(135deg, #64748b 0%, #475569 100%);
        color: white;
        box-shadow: 0 4px 15px rgba(100, 116, 139, 0.4);
    }

    /* Receso Especial */
    .receso {
        background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
        color: white;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        box-shadow: 0 4px 15px rgba(255, 107, 107, 0.5);
        border: 2px solid rgba(255, 255, 255, 0.3);
    }

    .receso-row {
        background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
    }

    .receso-row td {
        padding: 1.25rem;
        font-size: 1.125rem;
        font-weight: 800;
        color: white;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        text-align: center;
        border: none;
        position: relative;
    }

    .receso-row td::before {
        content: '';
        position: absolute;
        top: 50%;
        left: 1rem;
        right: 1rem;
        height: 2px;
        background: rgba(255, 255, 255, 0.3);
        transform: translateY(-50%);
    }

    /* Footer Coordinación */
    .coordinacion-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 2rem 2.5rem;
        background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
        color: white;
        font-weight: 700;
        border-top: 3px solid #fbbf24;
        position: relative;
    }

    .coordinacion-footer::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grid2" width="10" height="10" patternUnits="userSpaceOnUse"><path d="M 10 0 L 0 0 0 10" fill="none" stroke="rgba(255,255,255,0.03)" stroke-width="1"/></pattern></defs><rect width="100" height="100" fill="url(%23grid2)"/></svg>');
        opacity: 0.3;
    }

    .coordinacion-footer div {
        font-size: 0.875rem;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        position: relative;
        z-index: 2;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    /* Leyenda de Colores Modernizada */
    .legend {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 1rem;
        margin: 2rem 0;
        padding: 1.5rem;
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        border-radius: 1rem;
        border: 1px solid var(--border-color);
    }

    .legend-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.75rem 1rem;
        background: var(--bg-white);
        border-radius: 0.5rem;
        box-shadow: var(--shadow-sm);
        font-size: 0.8rem;
        font-weight: 600;
        transition: all 0.3s ease;
        border: 1px solid var(--border-color);
    }

    .legend-item:hover {
        transform: translateY(-1px);
        box-shadow: var(--shadow-md);
    }

    .legend-color {
        width: 1.25rem;
        height: 1.25rem;
        border-radius: 0.25rem;
        flex-shrink: 0;
        box-shadow: var(--shadow-sm);
    }

    /* Responsive Design */
    @media print {
        body {
            background: white;
            padding: 0;
        }
        
        .btn-actions, .legend {
            display: none !important;
        }
        
        .main-container {
            box-shadow: none;
            border-radius: 0;
            border: none;
        }
        
        .header-universidad {
            background: #1a365d !important;
            -webkit-print-color-adjust: exact;
        }
        
        .celda-materia {
            -webkit-print-color-adjust: exact;
        }
    }

    @media (max-width: 1024px) {
        .main-container {
            margin: 0 1rem;
            border-radius: 1rem;
        }
        
        .info-cards {
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        }
    }

    @media (max-width: 768px) {
        body {
            padding: 10px 0;
        }
        
        .main-container {
            margin: 0 0.5rem;
            border-radius: 1rem;
        }
        
        .header-universidad, .info-section, .schedule-container {
            padding: 1.5rem;
        }
        
        .header-universidad h1 {
            font-size: 1.5rem;
        }
        
        .ciclo-badge {
            position: static;
            margin: 1rem 0 0;
            display: inline-flex;
        }
        
        .tabla-horario {
            font-size: 0.75rem;
        }
        
        .tabla-horario td {
            height: 70px;
        }
        
        .nome-materia {
            font-size: 0.625rem;
        }
        
        .nombre-docente {
            font-size: 0.5rem;
        }
        
        .info-cards {
            grid-template-columns: 1fr;
            gap: 1rem;
        }
        
        .btn-actions {
            flex-direction: column;
            align-items: center;
        }
        
        .btn-modern {
            width: 100%;
            max-width: 300px;
            justify-content: center;
        }
        
        .coordinacion-footer {
            flex-direction: column;
            gap: 1rem;
            text-align: center;
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

    @keyframes pulse {
        0%, 100% {
            transform: scale(1);
        }
        50% {
            transform: scale(1.05);
        }
    }

    .pulse {
        animation: pulse 2s infinite;
    }
</style>
@endpush

@section('content')
<div class="main-container fade-in-up">
    <!-- Header de la Universidad -->
    <div class="header-universidad">
        <div class="ciclo-badge">
            <i class="uil uil-calendar-alt"></i>
            Ciclo Ordinario 2025-1
        </div>
        <div class="header-content">
            <div class="universidad-logo">
                <i class="uil uil-university"></i>
            </div>
            <h1>Universidad Nacional Amazónica de Madre de Dios</h1>
            <h2>"Año del Bicentenario, de la consolidación de nuestra Independencia, y de la conmemoración de las heroicas batallas de Junín y Ayacucho"</h2>
            <h2>"Madre de Dios, Capital de la Biodiversidad del Perú"</h2>
            <h3>
                <i class="uil uil-graduation-cap me-2"></i>
                Centro Pre Universitario
            </h3>
        </div>
    </div>

    <!-- Información del horario -->
    <div class="info-section">
        <!-- Botones de acción -->
        <div class="btn-actions">
            <a href="{{ route('horarios-docentes.index') }}" class="btn-modern btn-secondary-modern">
                <i class="uil uil-arrow-left"></i> 
                Volver a Lista
            </a>
            <button onclick="window.print()" class="btn-modern btn-primary-modern">
                <i class="uil uil-print"></i> 
                Imprimir Horario
            </button>
            <a href="{{ route('horarios-docentes.create') }}" class="btn-modern btn-success-modern">
                <i class="uil uil-plus"></i> 
                Nuevo Horario
            </a>
        </div>

        <div class="info-cards">
            <div class="info-card slide-in-right">
                <h4>
                    <i class="uil uil-sun"></i>
                    Turno Académico
                </h4>
                <p>
                    <i class="uil uil-clock"></i>
                    {{ $turnoSeleccionado ?? 'MAÑANA' }}
                </p>
            </div>
            <div class="info-card slide-in-right" style="animation-delay: 0.1s;">
                <h4>
                    <i class="uil uil-calendar-alt"></i>
                    Semana Lectiva
                </h4>
                <p>
                    <i class="uil uil-schedule"></i>
                    N° {{ $semana ?? '01' }}
                </p>
            </div>
            <div class="info-card slide-in-right" style="animation-delay: 0.2s;">
                <h4>
                    <i class="uil uil-building"></i>
                    Aula Asignada
                </h4>
                <p>
                    <i class="uil uil-map-marker"></i>
                    {{ $aulaSeleccionada->codigo ?? $aulaSeleccionada->nombre ?? 'GRUPO - A1' }}
                </p>
            </div>
        </div>
    </div>

    <!-- Contenido de horarios -->
    <div class="schedule-container">
        @foreach($calendarios as $index => $cal)
            <div class="schedule-header" style="animation-delay: {{ $index * 0.1 }}s;">
                <h4>
                    <i class="uil uil-building"></i>
                    Aula: {{ $cal['aula']->codigo ?? $cal['aula']->nombre ?? 'SIN AULA' }}
                    <span style="color: var(--text-secondary); margin: 0 1rem;">|</span>
                    <i class="uil uil-sun"></i>
                    Turno: {{ strtoupper($cal['turno'] ?? 'NO DEFINIDO') }}
                </h4>
            </div>

            <table class="tabla-horario">
                <thead>
                    <tr>
                        <th class="hora-col">
                            <i class="uil uil-clock me-1"></i>
                            HORA
                        </th>
                        @foreach($cal['horariosSemana']['dias'] as $dia)
                            <th>
                                <i class="uil uil-calendar-alt me-1"></i>
                                {{ strtoupper($dia) }}
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($cal['horariosSemana']['bloques'] as $bloque)
                        @if($bloque == '10:00-10:30')
                            <tr class="receso-row">
                                <td class="hora-col">
                                    <i class="uil uil-coffee"></i>
                                    {{ $bloque }}
                                </td>
                                <td colspan="6">
                                    <i class="uil uil-coffee me-2"></i>
                                    RECESO 30 MINUTOS
                                    <i class="uil uil-coffee ms-2"></i>
                                </td>
                            </tr>
                        @else
                            <tr>
                                <td class="hora-col">
                                    <i class="uil uil-clock-three"></i>
                                    {{ $bloque }}
                                </td>
                                @foreach($cal['horariosSemana']['dias'] as $dia)
                                    <td>
                                        @foreach($cal['horariosSemana']['horarios'][$bloque][$dia] ?? [] as $horario)
                                            @php
                                                $nombreCurso = $horario->curso->nombre ?? 'Sin curso';
                                                $nombreDocente = $horario->docente->nombre_completo ?? 'Sin docente';
                                                $claseCurso = '';
                                                $nombreLower = strtolower($nombreCurso);
                                                
                                                if (str_contains($nombreLower, 'razonamiento matemático') || str_contains($nombreLower, 'razonamiento matematico')) 
                                                    $claseCurso = 'razonamiento-matematico';
                                                elseif (str_contains($nombreLower, 'razonamiento verbal')) 
                                                    $claseCurso = 'razonamiento-verbal';
                                                elseif (str_contains($nombreLower, 'álgebra') || str_contains($nombreLower, 'algebra')) 
                                                    $claseCurso = 'algebra';
                                                elseif (str_contains($nombreLower, 'geometría') || str_contains($nombreLower, 'geometria')) 
                                                    $claseCurso = 'geometria';
                                                elseif (str_contains($nombreLower, 'aritmética') || str_contains($nombreLower, 'aritmetica')) 
                                                    $claseCurso = 'aritmetica';
                                                elseif (str_contains($nombreLower, 'economía') || str_contains($nombreLower, 'economia')) 
                                                    $claseCurso = 'economia';
                                                elseif (str_contains($nombreLower, 'física') || str_contains($nombreLower, 'fisica')) 
                                                    $claseCurso = 'fisica';
                                                elseif (str_contains($nombreLower, 'educación cívica') || str_contains($nombreLower, 'educacion civica')) 
                                                    $claseCurso = 'educacion-civica';
                                                elseif (str_contains($nombreLower, 'trigonometría') || str_contains($nombreLower, 'trigonometria')) 
                                                    $claseCurso = 'trigonometria';
                                                elseif (str_contains($nombreLower, 'historia')) 
                                                    $claseCurso = 'historia';
                                                elseif (str_contains($nombreLower, 'biología') || str_contains($nombreLower, 'biologia')) 
                                                    $claseCurso = 'biologia';
                                                elseif (str_contains($nombreLower, 'comprensión') || str_contains($nombreLower, 'comprension')) 
                                                    $claseCurso = 'comprension-lectora';
                                                elseif (str_contains($nombreLower, 'química') || str_contains($nombreLower, 'quimica')) 
                                                    $claseCurso = 'quimica';
                                                elseif (str_contains($nombreLower, 'lenguaje')) 
                                                    $claseCurso = 'lenguaje';
                                                else 
                                                    $claseCurso = 'otros';
                                            @endphp

                                            <div class="celda-materia {{ $claseCurso }}">
                                                <div class="nombre-materia">{{ $nombreCurso }}</div>
                                                <div class="nombre-docente">
                                                    <i class="uil uil-user me-1"></i>
                                                    {{ $nombreDocente }}
                                                </div>
                                            </div>
                                        @endforeach
                                    </td>
                                @endforeach
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        @endforeach

        <!-- Leyenda de Colores -->
        <div class="legend">
            <div class="legend-item">
                <div class="legend-color razonamiento-matematico"></div>
                <span>Razonamiento Matemático</span>
            </div>
            <div class="legend-item">
                <div class="legend-color razonamiento-verbal"></div>
                <span>Razonamiento Verbal</span>
            </div>
            <div class="legend-item">
                <div class="legend-color algebra"></div>
                <span>Álgebra</span>
            </div>
            <div class="legend-item">
                <div class="legend-color geometria"></div>
                <span>Geometría</span>
            </div>
            <div class="legend-item">
                <div class="legend-color aritmetica"></div>
                <span>Aritmética</span>
            </div>
            <div class="legend-item">
                <div class="legend-color economia"></div>
                <span>Economía</span>
            </div>
            <div class="legend-item">
                <div class="legend-color fisica"></div>
                <span>Física</span>
            </div>
            <div class="legend-item">
                <div class="legend-color educacion-civica"></div>
                <span>Educación Cívica</span>
            </div>
            <div class="legend-item">
                <div class="legend-color trigonometria"></div>
                <span>Trigonometría</span>
            </div>
            <div class="legend-item">
                <div class="legend-color historia"></div>
                <span>Historia</span>
            </div>
            <div class="legend-item">
                <div class="legend-color biologia"></div>
                <span>Biología</span>
            </div>
            <div class="legend-item">
                <div class="legend-color comprension-lectora"></div>
                <span>Comprensión Lectora</span>
            </div>
            <div class="legend-item">
                <div class="legend-color quimica"></div>
                <span>Química</span>
            </div>
            <div class="legend-item">
                <div class="legend-color lenguaje"></div>
                <span>Lenguaje</span>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="coordinacion-footer">
        <div>
            <i class="uil uil-calendar-alt me-1"></i>
            Mayo - Agosto 2025
        </div>
        <div>
            <i class="uil uil-graduation-cap me-1"></i>
            Coordinación Académica CEPRE
        </div>
    </div>
</div>

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Animaciones de entrada escalonadas para las tablas
    const tables = document.querySelectorAll('.tabla-horario');
    tables.forEach((table, index) => {
        table.style.opacity = '0';
        table.style.transform = 'translateY(30px)';
        
        setTimeout(() => {
            table.style.transition = 'all 0.6s cubic-bezier(0.4, 0, 0.2, 1)';
            table.style.opacity = '1';
            table.style.transform = 'translateY(0)';
        }, index * 200);
    });

    // Efectos adicionales para celdas de materias
    const materias = document.querySelectorAll('.celda-materia');
    materias.forEach(materia => {
        materia.addEventListener('mouseenter', function() {
            this.style.transition = 'all 0.3s ease';
            this.style.filter = 'brightness(1.1)';
        });
        
        materia.addEventListener('mouseleave', function() {
            this.style.filter = 'brightness(1)';
        });
    });

    // Mejorar experiencia de impresión
    window.addEventListener('beforeprint', function() {
        document.body.style.background = 'white';
    });

    window.addEventListener('afterprint', function() {
        document.body.style.background = 'linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%)';
    });

    // Tooltip informativo para materias (opcional)
    materias.forEach(materia => {
        materia.setAttribute('title', 
            materia.querySelector('.nombre-materia').textContent + ' - ' + 
            materia.querySelector('.nombre-docente').textContent
        );
    });
});
</script>
@endpush
@endsection