@extends('layouts.app')

@section('title', 'Dashboard de Gestión de Boletines')

@push('css')
    {{-- Dependencias de CSS --}}
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/boxicons@latest/css/boxicons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        /* ============================================
           VARIABLES CSS MEJORADAS
           ============================================ */
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --success-gradient: linear-gradient(135deg, #10b981 0%, #34d399 100%);
            --warning-gradient: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%);
            --info-gradient: linear-gradient(135deg, #3b82f6 0%, #60a5fa 100%);
            --danger-gradient: linear-gradient(135deg, #ef4444 0%, #f87171 100%);
            
            --shadow-sm: 0 2px 4px rgba(0,0,0,0.04);
            --shadow-md: 0 4px 12px rgba(0,0,0,0.08);
            --shadow-lg: 0 10px 30px rgba(0,0,0,0.12);
            --shadow-xl: 0 20px 40px rgba(0,0,0,0.15);
            
            --radius-sm: 0.5rem;
            --radius-md: 0.75rem;
            --radius-lg: 1rem;
            --radius-xl: 1.25rem;
            
            --transition-fast: all 0.2s ease;
            --transition-base: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* ============================================
           HEADER DEL DASHBOARD CON GLASSMORPHISM
           ============================================ */
        .dashboard-header {
            background: linear-gradient(135deg, rgba(255,255,255,0.95) 0%, rgba(255,255,255,0.9) 100%);
            backdrop-filter: blur(10px);
            padding: 1.5rem;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
            border: 1px solid rgba(255,255,255,0.3);
            margin-bottom: 2rem;
        }

        .dashboard-header h4 {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        /* ============================================
           TARJETAS DE ESTADÍSTICAS MEJORADAS
           ============================================ */
        .content-page .metric-card {
            border: none;
            border-radius: var(--radius-xl);
            transition: var(--transition-base);
            overflow: hidden;
            position: relative;
            box-shadow: var(--shadow-md);
            color: white;
            cursor: pointer;
        }

        /* Borde superior animado */
        .content-page .metric-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: rgba(255,255,255,0.6);
            transform: scaleX(0);
            transform-origin: left;
            transition: transform 0.3s ease;
            z-index: 3;
        }

        .content-page .metric-card:hover::before {
            transform: scaleX(1);
        }

        /* Efecto de brillo en hover */
        .content-page .metric-card::after {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            bottom: -50%;
            left: -50%;
            background: linear-gradient(
                to bottom,
                rgba(255, 255, 255, 0) 0%,
                rgba(255, 255, 255, 0.05) 50%,
                rgba(255, 255, 255, 0) 100%
            );
            transform: rotate(45deg) translateY(100%);
            transition: transform 0.6s;
        }

        .content-page .metric-card:hover::after {
            transform: rotate(45deg) translateY(-100%);
        }

        .content-page .metric-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: var(--shadow-xl);
        }

        .content-page .metric-card .card-body {
            position: relative;
            z-index: 2;
            padding: 1.75rem !important;
        }

        /* Iconos mejorados */
        .content-page .metric-card .stat-icon {
            font-size: 3.5rem;
            transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            position: absolute;
            right: 1.5rem;
            top: 50%;
            transform: translateY(-50%);
            opacity: 0.25;
            color: rgba(255,255,255,0.9);
        }

        .content-page .metric-card:hover .stat-icon {
            transform: translateY(-50%) scale(1.15) rotate(-5deg);
            opacity: 0.35;
        }

        /* Números con animación de contador */
        .content-page .metric-card h3 {
            font-size: 2.25rem;
            font-weight: 800;
            color: white !important;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
            letter-spacing: -0.5px;
        }

        .content-page .metric-card h5 {
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: rgba(255,255,255,0.95) !important;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.15);
        }

        /* Colores de las tarjetas */
        .content-page .metric-card.info { background-image: var(--info-gradient); }
        .content-page .metric-card.success { background-image: var(--success-gradient); }
        .content-page .metric-card.warning { background-image: var(--warning-gradient); }
        .content-page .metric-card.primary { background-image: var(--primary-gradient); }

        /* ============================================
           CARD DE FILTROS MEJORADO
           ============================================ */
        .filter-card {
            border: none;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
            overflow: hidden;
            margin-bottom: 2rem;
        }

        .filter-card .card-header {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-bottom: 2px solid #dee2e6;
            padding: 1.25rem 1.5rem;
        }

        .filter-card .card-header h5 {
            color: #495057;
            font-weight: 700;
            font-size: 1.1rem;
        }

        .filter-card .card-body {
            padding: 1.5rem;
            background: white;
        }

        /* Inputs mejorados */
        .filter-card .form-label {
            font-size: 0.875rem;
            color: #495057;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .filter-card .form-select {
            border: 2px solid #e9ecef;
            border-radius: var(--radius-sm);
            padding: 0.65rem 1rem;
            transition: var(--transition-fast);
            font-size: 0.95rem;
        }

        .filter-card .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.25rem rgba(102, 126, 234, 0.15);
        }

        .filter-card .form-select:hover {
            border-color: #cbd5e1;
        }

        /* Botón de filtrar mejorado */
        .filter-card .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 0.65rem 1.5rem;
            font-weight: 600;
            border-radius: var(--radius-sm);
            transition: var(--transition-base);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }

        .filter-card .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
        }

        .filter-card .btn-primary:active {
            transform: translateY(0);
        }

        /* ============================================
           TABLA PROFESIONAL MEJORADA
           ============================================ */
        .professional-table-wrapper {
            background: white;
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            box-shadow: var(--shadow-md);
        }

        /* Card header de la tabla */
        .table-card-header {
            border: none;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-bottom: 2px solid #dee2e6;
            padding: 1.25rem 1.5rem;
            border-radius: var(--radius-lg) var(--radius-lg) 0 0 !important;
        }

        .table-card-header h5 {
            color: #495057;
            font-weight: 700;
            font-size: 1.1rem;
        }

        /* Botones de acción mejorados */
        .action-buttons .btn {
            border-radius: var(--radius-sm);
            font-weight: 600;
            padding: 0.5rem 1.25rem;
            transition: var(--transition-base);
            font-size: 0.9rem;
        }

        .action-buttons .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }

        .action-buttons .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
        }

        .action-buttons .btn-success {
            background: linear-gradient(135deg, #10b981 0%, #34d399 100%);
            border: none;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }

        .action-buttons .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(16, 185, 129, 0.4);
        }

        /* Badge de cambios pendientes */
        .action-buttons .badge {
            font-size: 0.75rem;
            padding: 0.35rem 0.6rem;
            border-radius: 10px;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }

        /* ============================================
           ESTILOS DE TABLA MEJORADOS
           ============================================ */
        .professional-table-wrapper #boletines-table {
            border-collapse: separate;
            border-spacing: 0;
            width: 100%;
        }

        .professional-table-wrapper #boletines-table thead th {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            color: #495057;
            border-bottom: 3px solid #dee2e6;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 0.75rem;
            text-shadow: none;
            vertical-align: middle;
            text-align: center;
            padding: 1.25rem 0.75rem;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .professional-table-wrapper #boletines-table thead th.text-start {
             text-align: left !important;
        }

        /* Hover en filas mejorado */
        .professional-table-wrapper #boletines-table tbody tr {
            transition: var(--transition-fast);
        }

        .professional-table-wrapper #boletines-table tbody tr:hover {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.05) 0%, rgba(118, 75, 162, 0.05) 100%) !important;
            transform: scale(1.005);
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        
        .professional-table-wrapper #boletines-table tbody td {
            vertical-align: middle;
            color: #343a40;
            font-weight: 500;
            border-top: 1px solid #f1f3f5;
            padding: 1rem 0.75rem;
            transition: var(--transition-fast);
        }

        /* Zebra striping sutil */
        .professional-table-wrapper #boletines-table.table-striped tbody tr:nth-of-type(odd) {
            background-color: rgba(248, 249, 250, 0.5);
        }

        /* Headers de curso verticales */
        .curso-header {
            writing-mode: vertical-rl;
            transform: rotate(180deg);
            white-space: nowrap;
            padding-bottom: 10px !important;
            font-size: 0.8rem;
        }

        /* ============================================
           CHECKBOXES MODERNOS MEJORADOS
           ============================================ */
        .checkbox-lg {
            width: 1.5rem;
            height: 1.5rem;
            cursor: pointer;
            border: 2px solid #cbd5e1;
            border-radius: 0.375rem;
            transition: var(--transition-fast);
            position: relative;
            /* Asegurar que el checkbox base del DTR no tenga padding raro */
            margin: 0;
        }

        .checkbox-lg:hover {
            border-color: #667eea;
            transform: scale(1.1);
        }

        .checkbox-lg:checked {
            background-color: #667eea;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.2);
        }

        .checkbox-lg:focus {
            box-shadow: 0 0 0 0.25rem rgba(102, 126, 234, 0.25);
            outline: none;
        }

        /* Animación de cambio */
        .cell-changed {
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.15) 0%, rgba(52, 211, 153, 0.15) 100%) !important;
            animation: cellFlash 0.6s ease-out;
        }

        @keyframes cellFlash {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        /* ============================================
           PLACEHOLDER MEJORADO
           ============================================ */
        #table-placeholder {
            border: 3px dashed #e2e8f0;
            border-radius: var(--radius-lg);
            padding: 4rem 2rem;
            background: linear-gradient(135deg, #fafbfc 0%, #f8f9fa 100%);
            transition: var(--transition-base);
        }

        #table-placeholder:hover {
            border-color: #cbd5e1;
            background: linear-gradient(135deg, #f8f9fa 0%, #f1f3f5 100%);
        }

        #table-placeholder i {
            transition: var(--transition-base);
        }

        #table-placeholder:hover i {
            transform: scale(1.1) rotate(5deg);
        }

        /* ============================================
           RESPONSIVE DATATABLES MEJORADO (MOBILE FIXES)
           ============================================ */
        
        /* Control de expansión para móvil */
        table.dataTable.dtr-inline.collapsed > tbody > tr > td.control:before,
        table.dataTable.dtr-inline.collapsed > tbody > tr > th.control:before {
            top: 50%;
            left: 10px;
            transform: translateY(-50%);
            height: 24px;
            width: 24px;
            line-height: 24px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            font-weight: bold;
            box-shadow: 0 4px 8px rgba(102, 126, 234, 0.3);
            content: '+';
            color: white;
            display: inline-block;
            text-align: center;
            font-size: 1.1rem;
            transition: var(--transition-base);
            cursor: pointer;
        }

        table.dataTable.dtr-inline.collapsed > tbody > tr > td.control:hover:before {
            transform: translateY(-50%) scale(1.1);
            box-shadow: 0 6px 12px rgba(102, 126, 234, 0.4);
        }
        
        table.dataTable.dtr-inline.collapsed > tbody > tr.parent > td.control:before,
        table.dataTable.dtr-inline.collapsed > tbody > tr.parent > th.control:before {
            content: "-";
            background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
            box-shadow: 0 4px 8px rgba(108, 117, 125, 0.3);
        }
        
        /* CRÍTICO: Forzar que la columna del nombre SIEMPRE esté visible */
        table.dataTable.dtr-inline.collapsed tbody td:nth-child(2),
        table.dataTable.dtr-inline.collapsed thead th:nth-child(2),
        table.dataTable tbody td:nth-child(2),
        table.dataTable thead th:nth-child(2) {
            display: table-cell !important;
        }
        
        /* Ajustar padding cuando hay control */
        table.dataTable.dtr-inline.collapsed > tbody > tr > td.control {
            position: relative;
            padding-left: 45px !important;
            width: 50px;
            min-width: 50px;
        }
        
        tr.parent {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.08) 0%, rgba(118, 75, 162, 0.08) 100%) !important;
        }

        tr.child {
            background-color: #f8f9fa;
        }
        
        /* Detalles expandidos (Vista de Tarjeta en Móvil) */
        tr.child ul.dtr-details {
            list-style-type: none;
            padding: 1rem;
            margin: 0;
            background: white;
            border-radius: var(--radius-sm);
        }

        tr.child ul.dtr-details li {
            display: flex;
            flex-direction: row; /* Asegura el layout horizontal por defecto */
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem;
            border-bottom: 1px solid #f1f3f5;
            transition: var(--transition-fast);
        }
        
        tr.child ul.dtr-details li:hover {
            background-color: #f8f9fa;
        }
        
        tr.child ul.dtr-details li:last-child {
            border-bottom: none;
        }

        tr.child span.dtr-title {
            font-weight: 700;
            color: #495057;
            font-size: 0.9rem;
            flex-basis: 60%; /* Título a la izquierda */
        }

        tr.child span.dtr-data {
            text-align: right;
            flex-basis: 40%; /* Dato a la derecha */
        }

        /* ============================================
           DATATABLES SEARCH Y PAGINATION MEJORADOS
           ============================================ */
        .dataTables_wrapper .dataTables_filter input {
            border: 2px solid #e9ecef;
            border-radius: var(--radius-sm);
            padding: 0.5rem 1rem;
            transition: var(--transition-fast);
        }

        .dataTables_wrapper .dataTables_filter input:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.25rem rgba(102, 126, 234, 0.15);
            outline: none;
        }

        .dataTables_wrapper .dataTables_length select {
            border: 2px solid #e9ecef;
            border-radius: var(--radius-sm);
            padding: 0.375rem 2rem 0.375rem 0.75rem;
            transition: var(--transition-fast);
        }

        .dataTables_wrapper .dataTables_length select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.25rem rgba(102, 126, 234, 0.15);
            outline: none;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
            border: none !important;
            color: white !important;
            border-radius: var(--radius-sm);
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%) !important;
            border: none !important;
            color: #495057 !important;
        }

        /* ============================================
           UTILIDADES RESPONSIVE
           ============================================ */
        @media (max-width: 768px) {
            .dashboard-header {
                padding: 1rem;
            }

            .content-page .metric-card .card-body {
                padding: 1.25rem !important;
            }

            .content-page .metric-card h3 {
                font-size: 1.75rem;
            }

            .content-page .metric-card .stat-icon {
                font-size: 2.5rem;
                right: 1rem;
            }

            .filter-card .card-body {
                padding: 1rem;
            }

            .professional-table-wrapper {
                padding: 0.75rem;
                overflow-x: auto; /* Mantener por si acaso */
            }

            /* Los botones de acción ahora son 100% en móvil */
            .action-buttons .btn {
                width: 100%;
                margin-bottom: 0.5rem;
            }
            
            /* SÚPER IMPORTANTE: Forzar visibilidad del nombre en móvil */
            table.dataTable.dtr-inline.collapsed tbody td:nth-child(2),
            table.dataTable.dtr-inline.collapsed thead th:nth-child(2) {
                display: table-cell !important;
                min-width: 150px !important;
                max-width: none !important;
                font-weight: 600 !important;
                font-size: 0.9rem !important;
                padding: 0.75rem 0.5rem !important;
            }
            
            /* Ajustar el ancho de la columna de control en móvil */
            table.dataTable.dtr-inline.collapsed tbody td.control,
            table.dataTable.dtr-inline.collapsed thead th.control {
                width: 50px !important;
                min-width: 50px !important;
                max-width: 50px !important;
            }
            
            /* Hacer el checkbox de selección de fila más visible en móvil */
            .checkbox-lg {
                width: 1.3rem;
                height: 1.3rem;
            }
            
            /* Mejorar la visualización de las filas expandidas (Vista de tarjeta más compacta) */
            tr.child ul.dtr-details {
                padding: 0.75rem;
            }
            
            tr.child ul.dtr-details li {
                padding: 0.6rem;
                font-size: 0.85rem;
                flex-wrap: wrap; /* Permitir que el contenido se envuelva si es muy largo */
            }
            
            tr.child span.dtr-title {
                font-size: 0.85rem;
                /* No forzar margin-bottom para mantener una sola línea si es posible */
            }
            
            /* Asegurar que la tabla sea responsive */
            .table-responsive {
                overflow-x: visible !important; /* DTR maneja el colapso */
            }
            
            #boletines-table {
                width: 100% !important;
                max-width: 100% !important;
            }
        }
        
        /* Para pantallas muy pequeñas */
        @media (max-width: 480px) {
            table.dataTable.dtr-inline.collapsed tbody td:nth-child(2) {
                font-size: 0.85rem !important;
                min-width: 120px !important;
            }
            
            .professional-table-wrapper {
                padding: 0.5rem;
            }
            
            .dataTables_wrapper .dataTables_info,
            .dataTables_wrapper .dataTables_paginate {
                text-align: center;
            }
        }

        /* ============================================
           ANIMACIONES ADICIONALES
           ============================================ */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .card {
            animation: fadeIn 0.5s ease-out;
        }

        .metric-card {
            animation: fadeIn 0.5s ease-out;
        }

        .metric-card:nth-child(1) { animation-delay: 0.1s; }
        .metric-card:nth-child(2) { animation-delay: 0.2s; }
        .metric-card:nth-child(3) { animation-delay: 0.3s; }
        .metric-card:nth-child(4) { animation-delay: 0.4s; }

        /* ============================================
           SCROLLBAR PERSONALIZADO
           ============================================ */
        .professional-table-wrapper::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        .professional-table-wrapper::-webkit-scrollbar-track {
            background: #f1f3f5;
            border-radius: 10px;
        }

        .professional-table-wrapper::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 10px;
        }

        .professional-table-wrapper::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(135deg, #5a67d8 0%, #6b4694 100%);
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid pt-4">
        <!-- Header Mejorado -->
        <div class="dashboard-header d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="mb-1 font-size-20">Dashboard de Boletines</h4>
                <p class="text-muted mb-0 font-size-14">Gestión centralizada de la entrega de boletines académicos.</p>
            </div>
        </div>

        <!-- Tarjetas de Estadísticas (KPIs) Mejoradas -->
        <div class="row">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card metric-card info">
                    <div class="card-body">
                        <h5 class="text-uppercase mb-3">Total Estudiantes</h5>
                        <h3 class="mb-0" id="stat-total-students">-</h3>
                        <i class='fas fa-users stat-icon'></i>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card metric-card success">
                    <div class="card-body">
                        <h5 class="text-uppercase mb-3">Entregados</h5>
                        <h3 class="mb-0" id="stat-entregados">-</h3>
                        <i class='fas fa-check-circle stat-icon'></i>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card metric-card warning">
                    <div class="card-body">
                        <h5 class="text-uppercase mb-3">Pendientes</h5>
                        <h3 class="mb-0" id="stat-pendientes">-</h3>
                         <i class='fas fa-clock stat-icon'></i>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card metric-card primary">
                    <div class="card-body">
                        <h5 class="text-uppercase mb-3">Progreso</h5>
                        <h3 class="mb-0" id="stat-progreso">- %</h3>
                        <i class='fas fa-chart-line stat-icon'></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtros Mejorados -->
        <div class="card filter-card">
            <div class="card-header">
                <h5 class="card-title mb-0 d-flex align-items-center">
                    <i class="bx bx-filter-alt me-2 font-size-20"></i> Filtros de Búsqueda
                </h5>
            </div>
            <div class="card-body">
                <form id="filter-form" class="row g-3 align-items-end">
                    <div class="col-md-3 col-sm-6">
                        <label for="ciclo_id" class="form-label">Ciclo Académico <span class="text-danger">*</span></label>
                        <select id="ciclo_id" name="ciclo_id" class="form-select">
                            <option value="">Seleccione un ciclo</option>
                            @foreach($ciclos as $ciclo)
                                <option value="{{ $ciclo->id }}">{{ $ciclo->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <label for="aula_id" class="form-label">Aula <span class="text-danger">*</span></label>
                        <select id="aula_id" name="aula_id" class="form-select">
                            <option value="">Seleccione un aula</option>
                             @foreach($aulas as $aula)
                                <option value="{{ $aula->id }}">{{ $aula->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <label for="tipo_examen" class="form-label">Tipo de Examen <span class="text-danger">*</span></label>
                        <select id="tipo_examen" name="tipo_examen" class="form-select">
                            <option value="">Seleccione un examen</option>
                            <option value="PRIMER EXAMEN">PRIMER EXAMEN</option>
                            <option value="SEGUNDO EXAMEN">SEGUNDO EXAMEN</option>
                            <option value="TERCER EXAMEN">TERCER EXAMEN</option>
                        </select>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <button type="submit" class="btn btn-primary w-100 d-flex align-items-center justify-content-center">
                            <i class="bx bx-search-alt me-1 font-size-18"></i> Filtrar Datos
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabla de Datos Mejorada -->
        <div class="card">
            <div class="card-header table-card-header">
                <div class="d-flex flex-wrap justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Resultados de Estudiantes</h5>
                    <div class="action-buttons d-flex gap-2 mt-2 mt-md-0 w-100 w-md-auto">
                        <button type="button" id="save-changes-button" class="btn btn-primary" style="display: none;">
                            <i class="fas fa-save me-1"></i> Guardar Cambios <span id="changes-count" class="badge bg-danger ms-1"></span>
                        </button>
                        <button type="button" id="export-button" class="btn btn-success" style="display: none;">
                            <i class="fas fa-file-excel me-1"></i> Exportar a Excel
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body professional-table-wrapper">
                 <div id="table-container">
                    <div id="table-placeholder" class="text-center py-5">
                        <i class="bx bx-table display-4 text-muted mb-3" style="font-size: 5rem !important;"></i>
                        <h5 class="mt-2">Bienvenido al Dashboard</h5>
                        <p class="text-muted">Utilice los filtros superiores para cargar los datos de los estudiantes.</p>
                    </div>
                    <div class="table-responsive">
                        <table id="boletines-table" class="table table-striped table-hover dt-responsive nowrap" style="width:100%; display:none;">
                            <thead></thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    {{-- Dependencias de JS --}}
    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
    $(document).ready(function() {
        // ==========================================
        // CONFIGURACIÓN Y ESTADO GLOBAL
        // ==========================================
        const DashboardState = {
            dataTable: null,
            changes: {},
            isLoading: false,
            currentFilters: {}
        };

        const DOMElements = {
            $table: $('#boletines-table'),
            $placeholder: $('#table-placeholder'),
            $exportButton: $('#export-button'),
            $saveButton: $('#save-changes-button'),
            $changesCount: $('#changes-count'),
            $filterForm: $('#filter-form'),
            $statElements: {
                total: $('#stat-total-students'),
                entregados: $('#stat-entregados'),
                pendientes: $('#stat-pendientes'),
                progreso: $('#stat-progreso')
            }
        };

        // Configuración AJAX global
        $.ajaxSetup({ 
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
            timeout: 30000
        });

        // Toast mejorado
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 2500,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer);
                toast.addEventListener('mouseleave', Swal.resumeTimer);
            }
        });

        // ==========================================
        // MÓDULO DE ESTADÍSTICAS
        // ==========================================
        const StatsModule = {
            reset() {
                Object.values(DOMElements.$statElements).forEach($el => $el.text('-'));
            },

            update() {
                if (!DashboardState.dataTable) return;

                // Usamos rows({ page: 'all' }) para obtener todos los datos, incluyendo los no visibles
                // Usamos rows({ search: 'applied' }) para obtener solo los datos filtrados por el buscador
                const rows = DashboardState.dataTable.rows({ search: 'applied' });
                const $checkboxes = rows.nodes().to$().find('.entrega-checkbox');
                
                const stats = {
                    total: rows.count(),
                    totalCheckboxes: $checkboxes.length,
                    checked: $checkboxes.filter(':checked').length
                };

                stats.pendientes = stats.totalCheckboxes - stats.checked;
                stats.progreso = stats.totalCheckboxes > 0 
                    ? ((stats.checked / stats.totalCheckboxes) * 100).toFixed(1) 
                    : 0;

                this.animateUpdate(DOMElements.$statElements.total, stats.total);
                this.animateUpdate(DOMElements.$statElements.entregados, stats.checked);
                this.animateUpdate(DOMElements.$statElements.pendientes, stats.pendientes);
                this.animateUpdate(DOMElements.$statElements.progreso, `${stats.progreso} %`);
            },

            animateUpdate($element, newValue) {
                const currentValue = $element.text();
                if (currentValue !== String(newValue)) {
                    $element.fadeOut(200, function() {
                        $(this).text(newValue).fadeIn(200);
                    });
                }
            }
        };

        // ==========================================
        // MÓDULO DE UI
        // ==========================================
        const UIModule = {
            showPlaceholder(icon, title, message) {
                DOMElements.$placeholder.html(`
                    <div class="text-center py-5">
                        <i class="${icon} display-4 text-muted mb-3" style="font-size: 5rem !important;"></i>
                        <h5 class="mt-2">${title}</h5>
                        <p class="text-muted">${message}</p>
                    </div>
                `).fadeIn(300);
                
                DOMElements.$table.hide();
                DOMElements.$exportButton.hide();
                this.hideChangeButton();
                
                if (DashboardState.dataTable) {
                    DashboardState.dataTable.destroy();
                    DashboardState.dataTable = null;
                    $('#boletines-table thead, #boletines-table tbody').empty();
                }
                
                DashboardState.changes = {};
                StatsModule.reset();
            },

            flashCell($cell) {
                $cell.addClass('cell-changed');
                setTimeout(() => $cell.removeClass('cell-changed'), 600);
            },

            showChangeButton(count) {
                DOMElements.$changesCount.text(count);
                DOMElements.$saveButton.fadeIn(300);
            },

            hideChangeButton() {
                DOMElements.$saveButton.fadeOut(300);
                DOMElements.$changesCount.text('');
            },

            showLoading(title = 'Cargando...', text = 'Por favor espere.') {
                return Swal.fire({
                    title,
                    text,
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => Swal.showLoading()
                });
            }
        };

        // ==========================================
        // MÓDULO DE GESTIÓN DE CAMBIOS
        // ==========================================
        const ChangesModule = {
            add(key, data) {
                DashboardState.changes[key] = data;
                this.updateUI();
            },

            remove(key) {
                delete DashboardState.changes[key];
                this.updateUI();
            },

            clear() {
                DashboardState.changes = {};
                this.updateUI();
            },

            getCount() {
                return Object.keys(DashboardState.changes).length;
            },

            hasChanges() {
                return this.getCount() > 0;
            },

            updateUI() {
                const count = this.getCount();
                if (count > 0) {
                    UIModule.showChangeButton(count);
                } else {
                    UIModule.hideChangeButton();
                }
            },

            async save() {
                if (!this.hasChanges()) return;

                const dataToSend = {
                    entregas: Object.values(DashboardState.changes),
                    tipo_examen: $('#tipo_examen').val()
                };

                try {
                    UIModule.showLoading(
                        'Guardando Cambios...', 
                        `Se guardarán ${dataToSend.entregas.length} registro(s).`
                    );

                    const response = await $.ajax({
                        url: '{{ route("boletines.marcar") }}',
                        type: 'POST',
                        data: dataToSend
                    });

                    if (response.success) {
                        // Actualizar el estado 'defaultChecked' de los checkboxes guardados
                        Object.keys(DashboardState.changes).forEach(key => {
                            const $cb = $(`.entrega-checkbox[data-key="${key}"]`);
                            if ($cb.length) {
                                $cb[0].defaultChecked = $cb.is(':checked');
                            }
                        });

                        this.clear();
                        Swal.fire({
                            icon: 'success',
                            title: '¡Éxito!',
                            text: response.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                        StatsModule.update();
                    } else {
                        throw new Error(response.message || 'Error al guardar');
                    }
                } catch (error) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error al Guardar',
                        text: error.message || 'Ocurrió un problema de comunicación.',
                        confirmButtonText: 'Entendido'
                    });
                }
            }
        };

        // ==========================================
        // MÓDULO DE TABLA
        // ==========================================
        const TableModule = {
            destroy() {
                if (DashboardState.dataTable) {
                    DashboardState.dataTable.destroy();
                    DashboardState.dataTable = null;
                    $('#boletines-table thead, #boletines-table tbody').empty();
                }
            },

            buildHeader(cursos) {
                let html = `
                    <tr>
                        <th class="text-center">
                            <input type="checkbox" 
                                   id="select-all-master" 
                                   class="form-check-input checkbox-lg"
                                   aria-label="Seleccionar todos">
                        </th>
                        <th class="text-start">
                            <i class="fas fa-user-graduate me-2"></i>Estudiante
                        </th>
                `;

                cursos.forEach(curso => {
                    html += `
                        <th class="curso-header">
                            <input type="checkbox" 
                                   class="form-check-input select-all-col checkbox-lg" 
                                   data-curso-id="${curso.id}"
                                   aria-label="Seleccionar columna ${this.escapeHtml(curso.nombre)}">
                            <br>${this.escapeHtml(curso.nombre)}
                        </th>
                    `;
                });

                html += '</tr>';
                return html;
            },

            buildBody(data) {
                return data.map(row => {
                    let html = `
                        <tr>
                            <td class="text-center">
                                <input type="checkbox" 
                                               class="form-check-input select-all-row checkbox-lg" 
                                               data-inscripcion-id="${row.inscripcion_id}"
                                               aria-label="Seleccionar fila">
                            </td>
                            <td class="text-start">
                                <strong>${this.escapeHtml(row.student)}</strong>
                            </td>
                    `;

                    row.courses.forEach(course => {
                        const checked = course.entregado ? 'checked' : '';
                        const key = `${row.inscripcion_id}-${course.id}`;
                        html += `
                            <td class="text-center">
                                <input type="checkbox" 
                                               class="form-check-input entrega-checkbox checkbox-lg" 
                                               data-inscripcion-id="${row.inscripcion_id}" 
                                               data-curso-id="${course.id}" 
                                               data-key="${key}" 
                                               ${checked}
                                               aria-label="Marcar entrega">
                            </td>
                        `;
                    });

                    html += '</tr>';
                    return html;
                }).join('');
            },

            escapeHtml(text) {
                const map = {
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#039;'
                };
                return String(text).replace(/[&<>"']/g, m => map[m]);
            },

            initialize() {
                // Inicializar defaultChecked para rastrear cambios
                $('.entrega-checkbox').each(function() {
                    this.defaultChecked = this.checked;
                });

                DashboardState.dataTable = DOMElements.$table.DataTable({
                    responsive: {
                        details: {
                            type: 'column',
                            target: 0
                        }
                    },
                    language: { 
                        url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json',
                        search: "_INPUT_",
                        searchPlaceholder: "Buscar estudiante..."
                    },
                    destroy: true,
                    pageLength: 25,
                    lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Todos"]],
                    order: [[1, 'asc']],
                    columnDefs: [
                        { orderable: false, targets: '_all' },
                        { 
                            className: 'control',
                            orderable: false,
                            targets: 0,
                            responsivePriority: 2 // El control siempre visible
                        },
                        { 
                            responsivePriority: 1, // El nombre del estudiante es la prioridad más alta
                            targets: 1
                        },
                        {
                            responsivePriority: 10001, // Forzar a las columnas de cursos a colapsar
                            targets: '_all' 
                        }
                    ],
                    drawCallback: function() {
                        StatsModule.update();
                    }
                });

                StatsModule.update();
            }
        };

        // ==========================================
        // MÓDULO DE DATOS (API)
        // ==========================================
        const DataModule = {
            async load(cicloId, aulaId, tipoExamen) {
                if (DashboardState.isLoading) return;
                
                DashboardState.isLoading = true;
                DashboardState.currentFilters = { cicloId, aulaId, tipoExamen };

                try {
                    UIModule.showLoading('Cargando Datos...', 'Por favor espere. Puede tardar un momento si la lista es grande.');

                    const response = await $.ajax({
                        url: '{{ route("boletines.data") }}',
                        type: 'GET',
                        data: { 
                            ciclo_id: cicloId, 
                            aula_id: aulaId, 
                            tipo_examen: tipoExamen 
                        }
                    });

                    Swal.close();
                    this.handleResponse(response);

                } catch (error) {
                    Swal.close();
                    UIModule.showPlaceholder(
                        'bx bx-error-circle', 
                        'Error de Carga', 
                        'Ocurrió un error al comunicarse con el servidor. Intente de nuevo.'
                    );
                    console.error('Error loading data:', error);
                } finally {
                    DashboardState.isLoading = false;
                }
            },

            handleResponse(response) {
                if (!response.data || response.data.length === 0) {
                    UIModule.showPlaceholder(
                        'bx bx-search-alt', 
                        'No se encontraron estudiantes', 
                        'No hay datos que coincidan con los filtros seleccionados.'
                    );
                    return;
                }

                TableModule.destroy();
                ChangesModule.clear();

                DOMElements.$placeholder.hide();
                DOMElements.$table.show();
                DOMElements.$exportButton.show();

                $('#boletines-table thead').html(TableModule.buildHeader(response.cursos));
                $('#boletines-table tbody').html(TableModule.buildBody(response.data));

                TableModule.initialize();

                Toast.fire({
                    icon: 'success',
                    title: `${response.data.length} estudiante(s) encontrado(s)`
                });
            }
        };

        // ==========================================
        // EVENTOS DEL FORMULARIO
        // ==========================================
        DOMElements.$filterForm.on('submit', function(e) {
            e.preventDefault();
            
            const cicloId = $('#ciclo_id').val();
            const aulaId = $('#aula_id').val();
            const tipoExamen = $('#tipo_examen').val();

            if (!cicloId || !aulaId || !tipoExamen) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Atención',
                    text: 'Por favor, seleccione todos los filtros requeridos.',
                    confirmButtonText: 'Entendido'
                });
                return;
            }

            if (ChangesModule.hasChanges()) {
                Swal.fire({
                    title: 'Cambios sin guardar',
                    text: "Tiene cambios pendientes. ¿Desea descartarlos y continuar?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#667eea',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Sí, descartar',
                    cancelButtonText: 'No, cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        DataModule.load(cicloId, aulaId, tipoExamen);
                    }
                });
            } else {
                DataModule.load(cicloId, aulaId, tipoExamen);
            }
        });

        // ==========================================
        // EVENTOS DE CHECKBOXES
        // ==========================================
        DOMElements.$table.on('change', '.entrega-checkbox', function(e) {
            const $cb = $(this);
            // Comprobar si es un evento de cambio "manual" o por selección masiva
            const isBulkChange = !e.originalEvent; 

            // Lógica para guardar directamente si NO es una acción masiva
            // NOTA: Para una mejor UX/rendimiento, la acción masiva ahora usa el botón "Guardar Cambios"
            if (!isBulkChange) {
                UIModule.flashCell($cb.parent());
                const dataToSend = {
                    entregas: [{
                        inscripcion_id: $cb.data('inscripcion-id'),
                        curso_id: $cb.data('curso-id'),
                        entregado: $cb.is(':checked') ? 1 : 0
                    }],
                    tipo_examen: $('#tipo_examen').val()
                };

                $cb.prop('disabled', true);
                $.ajax({
                    url: '{{ route("boletines.marcar") }}',
                    type: 'POST',
                    data: dataToSend,
                    success: function(response) {
                        if (response.success) {
                            $cb[0].defaultChecked = $cb.is(':checked');
                            Toast.fire({ icon: 'success', title: response.message });
                        } else {
                            Swal.fire('Error', response.message || 'No se pudo guardar.', 'error');
                            // Revertir el estado del checkbox
                            $cb.prop('checked', !$cb.is(':checked'));
                        }
                    },
                    error: () => {
                        Swal.fire('Error', 'Problema de comunicación con el servidor. Intente de nuevo.', 'error');
                        $cb.prop('checked', !$cb.is(':checked'));
                    },
                    complete: () => {
                        $cb.prop('disabled', false);
                        StatsModule.update();
                    }
                });
            } else {
                // Lógica de seguimiento de cambios para acciones masivas
                const key = $cb.data('key');
                const originalState = $cb[0].defaultChecked;
                const currentState = $cb.is(':checked');

                if (currentState !== originalState) {
                    ChangesModule.add(key, {
                        inscripcion_id: $cb.data('inscripcion-id'),
                        curso_id: $cb.data('curso-id'),
                        entregado: currentState ? 1 : 0
                    });
                } else {
                    ChangesModule.remove(key);
                }
                StatsModule.update();
            }
        });

        // Seleccionar todos (master)
        DOMElements.$table.on('change', '#select-all-master', function() {
            const isChecked = $(this).is(':checked');
            const rows = DashboardState.dataTable.rows({ search: 'applied' }).nodes();
            
            // Las columnas de cursos se marcarán
            const dataCheckboxes = $(rows).find('.entrega-checkbox');
            dataCheckboxes.prop('checked', isChecked);
            dataCheckboxes.trigger('change', [true]); // Pasar un argumento para indicar que es masivo

            // Las cabeceras de columna también se marcan/desmarcan
            $('.select-all-col').prop('checked', isChecked);
        });

        // Seleccionar columna
        DOMElements.$table.on('change', '.select-all-col', function() {
            const cursoId = $(this).data('curso-id');
            const isChecked = $(this).is(':checked');
            
            const cbs = DashboardState.dataTable.rows({ search: 'applied' }).nodes().to$()
                .find(`.entrega-checkbox[data-curso-id="${cursoId}"]`);
                
            cbs.prop('checked', isChecked).trigger('change', [true]); // Pasar un argumento para indicar que es masivo
        });

        // Seleccionar fila
        DOMElements.$table.on('change', '.select-all-row', function() {
            const isChecked = $(this).is(':checked');
            const cbs = $(this).closest('tr').find('.entrega-checkbox');
            cbs.prop('checked', isChecked).trigger('change', [true]); // Pasar un argumento para indicar que es masivo
        });

        // ==========================================
        // BOTONES DE ACCIÓN
        // ==========================================
        DOMElements.$saveButton.on('click', function() {
            ChangesModule.save();
        });

        DOMElements.$exportButton.on('click', function() {
            const { cicloId, aulaId, tipoExamen } = DashboardState.currentFilters;
            
            if (!cicloId || !aulaId || !tipoExamen) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Atención',
                    text: 'Seleccione filtros válidos para exportar.',
                    confirmButtonText: 'Entendido'
                });
                return;
            }

            // Realiza la descarga, no se necesita AJAX/Loading aquí
            window.location.href = `{{ route("boletines.exportar") }}?ciclo_id=${cicloId}&aula_id=${aulaId}&tipo_examen=${tipoExamen}`;
        });

        // ==========================================
        // INICIALIZACIÓN
        // ==========================================
        UIModule.showPlaceholder(
            'bx bx-table', 
            'Bienvenido al Dashboard', 
            'Utilice los filtros superiores para cargar los datos de los estudiantes.'
        );
    });
    </script>
@endpush
