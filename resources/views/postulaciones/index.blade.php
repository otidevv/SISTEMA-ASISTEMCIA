@extends('layouts.app')

@section('title', 'Gestión de Postulaciones')

@push('css')
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.14.5/dist/sweetalert2.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.30/dist/select2-bootstrap-5-theme.min.css"
        rel="stylesheet" />
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- Fuente Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">

    <style>
        /* Paleta de colores institucional basada en el logo CEPRE UNAMAD */
        :root {
            --cepre-magenta: #e91e63;
            --cepre-cyan: #00bcd4;
            --cepre-green: #8bc34a;
            --cepre-gold: #ffd700;
            --cepre-navy: #1a237e;
            --cepre-dark-blue: #0d47a1;
            --cepre-light-gray: #f8f9fa;
            --cepre-dark-gray: #455a64;
            --cepre-shadow: rgba(26, 35, 126, 0.15);
            /* Colores para Dark Mode (guiados por tu imagen de asistencia) */
            --cepre-dark-bg: #293142;
            --cepre-dark-card: #364053;
            --cepre-dark-text: #eef2f7;
        }
        
        /* Estilos base para usar la fuente Inter y fondo */
        body {
            font-family: 'Inter', sans-serif !important; 
            background-color: #f4f7f9;
        }
        
        /* Contenedores principales de la página (APLICAMOS ESTÉTICA EXTERNA) */
        .cepre-content-card {
            border-radius: 1.5rem !important; 
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            margin-bottom: 1.5rem;
        }
        
        /* ========================================================= */
        /* SOLUCIÓN MODO OSCURO (Dark Mode) */
        /* ========================================================= */
        body[data-layout-mode="dark"] .cepre-content-card {
            background-color: var(--cepre-dark-card) !important;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.5), 0 4px 6px -2px rgba(0, 0, 0, 0.2);
            border: none;
        }
        body[data-layout-mode="dark"] .border-b.border-gray-200 {
            border-color: #4a5468 !important;
        }
        body[data-layout-mode="dark"] .cepre-content-card h4, 
        body[data-layout-mode="dark"] .cepre-content-card h3, 
        body[data-layout-mode="dark"] .cepre-content-card p, 
        body[data-layout-mode="dark"] .cepre-content-card .form-label, 
        body[data-layout-mode="dark"] .cepre-content-card .text-gray-700, 
        body[data-layout-mode="dark"] .cepre-content-card .text-gray-600,
        body[data-layout-mode="dark"] .cepre-content-card .text-muted,
        body[data-layout-mode="dark"] .cepre-content-card .header-title {
            color: var(--cepre-dark-text) !important;
        }
        body[data-layout-mode="dark"] .cepre-stat-card * {
            color: var(--cepre-dark-text) !important;
        }
        body[data-layout-mode="dark"] .form-select, 
        body[data-layout-mode="dark"] .form-control {
            background-color: #4a5468 !important; 
            border-color: #5a6374 !important;
            color: var(--cepre-dark-text) !important;
        }
        body[data-layout-mode="dark"] .form-select option {
            background-color: #364053 !important; 
            color: var(--cepre-dark-text) !important;
        }
        body[data-layout-mode="dark"] #postulaciones-datatable tbody tr td {
            color: var(--cepre-dark-text) !important;
            background-color: transparent !important; 
        }

        /* Estilo para tarjetas de estadísticas (cepre-stat-card) */
        .cepre-stat-card {
            background-color: white;
            border-radius: 0.75rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -2px rgba(0, 0, 0, 0.06);
            transition: transform 0.2s;
            overflow: hidden;
            border-left: 5px solid;
            padding: 1rem;
            color: #333 !important;
        }
        body[data-layout-mode="dark"] .cepre-stat-card {
             background-color: var(--cepre-dark-card) !important;
             box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.3);
        }
        .cepre-stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        
        /* Estilos para encabezados de modal (mantenidos de Bootstrap con colores CEPRE) */
        .modal-header.cepre-bg-gradient {
            background: linear-gradient(135deg, var(--cepre-magenta) 0%, var(--cepre-navy) 100%) !important;
        }
        .modal-header.cepre-bg-navy {
            background-color: var(--cepre-navy) !important;
            color: white;
        }
        .modal-header.cepre-bg-cyan {
            background-color: var(--cepre-cyan) !important;
            color: white;
        }
        
        /* Íconos de las Tarjetas de Estadísticas */
        .stat-icon {
            font-size: 2.5rem; /* 40px */
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .stat-icon-container {
            width: 55px; /* Contenedor para el ícono */
            height: 55px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
            color: white; /* Asegurar que el ícono sea blanco por defecto */
        }
        /* Estilo del spinner institucional */
        .cepre-spinner {
            width: 60px;
            height: 60px;
            border: 4px solid rgba(233, 30, 99, 0.1);
            border-left: 4px solid var(--cepre-magenta);
            border-radius: 50%;
            animation: cepreSpinAnimation 1s linear infinite;
            margin: 0 auto;
        }
        @keyframes cepreSpinAnimation {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* ========================================================= */
        /* WIZARD HORIZONTAL MODERNIZADO (SOLUCIÓN FINAL) */
        /* ========================================================= */
        
        #modalRegistroNuevo .modal-content {
            flex-direction: column; /* Volvemos a columna para ocupar todo el ancho */
            max-width: 1200px;
            margin: auto;
        }
        
        .registration-wizard {
            display: flex;
            flex-direction: column;
            width: 100%;
        }

        /* 1. Barra de progreso HORIZONTAL */
        .registration-wizard .wizard-progress-container {
            width: 100%; /* Ocupar todo el ancho */
            flex-direction: row; /* Horizontal */
            justify-content: space-between;
            padding: 1.5rem 2rem;
            margin-bottom: 0;
            border-bottom: 1px solid #dee2e6; /* Separador debajo del wizard */
            border-right: none;
            background-color: #f8f9fa;
        }
        body[data-layout-mode="dark"] .registration-wizard .wizard-progress-container {
            border-bottom-color: #4a5468 !important;
            background-color: var(--cepre-dark-card) !important;
        }
        .registration-wizard .step-indicator {
            flex-grow: 1;
            padding: 0 0.5rem;
            text-align: center;
            transition: none;
        }
        .registration-wizard .step-indicator:not(:last-child) {
            margin-right: 2rem; /* Espacio entre pasos */
        }
        
        /* Círculo e Ícono (Diseño horizontal limpio) */
        .registration-wizard .step-circle {
            position: relative;
            transform: none;
            left: auto;
            margin: 0 auto 0.5rem; /* Centrado horizontalmente, espacio inferior */
            width: 50px;
            height: 50px;
            border: 2px solid #ccc;
            background-color: white;
            transition: background-color 0.3s, border-color 0.3s;
        }
        .registration-wizard .step-indicator.active .step-circle {
            border-color: var(--cepre-cyan);
            background-color: var(--cepre-cyan);
            color: white;
            transform: none;
            box-shadow: 0 0 10px rgba(0, 188, 212, 0.4);
        }
        .registration-wizard .step-indicator.completed .step-circle {
            background-color: var(--cepre-green);
            border-color: var(--cepre-green);
            color: white;
        }

        /* Etiqueta (Texto) - Debajo del círculo */
        .registration-wizard .step-label {
            margin-left: 0;
            font-size: 0.85rem;
            font-weight: 600;
            color: #6c757d;
            transform: none;
            position: relative;
            top: auto;
            left: auto;
            width: auto;
        }
        .registration-wizard .step-indicator.active .step-label {
            color: var(--cepre-cyan);
        }
        .registration-wizard .step-indicator.completed .step-label {
            color: var(--cepre-navy);
        }
        
        /* Línea de Conexión (Horizontal) */
        .registration-wizard .step-indicator:not(:last-child) {
            /* Contenedor para el paso y la línea */
            position: relative;
            display: flex;
            align-items: center;
        }
        .registration-wizard .step-indicator .progress-line {
            display: block; /* Hacemos visible la línea para conexión horizontal */
            position: absolute;
            top: 25px; 
            left: 50px; /* Inicio de la línea después del círculo */
            right: -2rem; /* Termina antes del siguiente paso */
            height: 4px;
            background: #e0e0e0;
            z-index: 1;
            transform: translateY(-50%);
            width: calc(100% - 70px); /* Ajustar el ancho de la línea */
        }
        .registration-wizard .step-indicator:last-child .progress-line {
            display: none;
        }
        .registration-wizard .step-indicator.completed .progress-line {
            background: var(--cepre-green);
        }

        /* 2. Contenido del Formulario (Alineación y Pasos) */
        #formRegistroNuevo {
            flex-grow: 1; /* Ocupar el espacio restante */
            padding: 2rem; /* Padding interno */
        }

        .wizard-step {
            min-height: 500px;
            padding: 0; /* Quitamos padding duplicado */
        }

        /* REGLA CRÍTICA PARA EL WIZARD: Ocultar pasos inactivos */
        .wizard-step {
            display: none;
            animation: fadeIn 0.5s ease-in-out; 
        }

        .wizard-step.active {
            display: block;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Alineación de inputs */
        .wizard-step .step-content-card .row > div {
             padding-left: 0.75rem !important;
             padding-right: 0.75rem !important;
             margin-bottom: 1rem; /* Espacio más compacto */
        }

        /* ========================================================= */
        /* COLORES DE FILAS SEGÚN ESTADO DE POSTULACIÓN */
        /* ========================================================= */
        
        /* Fila Pendiente - Amarillo suave */
        #postulaciones-datatable tbody tr.estado-pendiente {
            background-color: #fff9e6 !important;
            border-left: 4px solid #ffc107;
        }
        #postulaciones-datatable tbody tr.estado-pendiente:hover {
            background-color: #fff3cd !important;
        }
        
        /* Fila Aprobado - Verde suave */
        #postulaciones-datatable tbody tr.estado-aprobado {
            background-color: #e8f5e9 !important;
            border-left: 4px solid #28a745;
        }
        #postulaciones-datatable tbody tr.estado-aprobado:hover {
            background-color: #d4edda !important;
        }
        
        /* Fila Rechazado - Rojo suave */
        #postulaciones-datatable tbody tr.estado-rechazado {
            background-color: #ffebee !important;
            border-left: 4px solid #dc3545;
        }
        #postulaciones-datatable tbody tr.estado-rechazado:hover {
            background-color: #f8d7da !important;
        }
        
        /* Fila Observado - Naranja suave */
        #postulaciones-datatable tbody tr.estado-observado {
            background-color: #fff3e0 !important;
            border-left: 4px solid #ff9800;
        }
        #postulaciones-datatable tbody tr.estado-observado:hover {
            background-color: #ffe0b2 !important;
        }
        
        /* Modo Oscuro - Ajustes para las filas con color */
        body[data-layout-mode="dark"] #postulaciones-datatable tbody tr.estado-pendiente {
            background-color: rgba(255, 193, 7, 0.15) !important;
            border-left-color: #ffc107;
        }
        body[data-layout-mode="dark"] #postulaciones-datatable tbody tr.estado-pendiente:hover {
            background-color: rgba(255, 193, 7, 0.25) !important;
        }
        
        body[data-layout-mode="dark"] #postulaciones-datatable tbody tr.estado-aprobado {
            background-color: rgba(40, 167, 69, 0.15) !important;
            border-left-color: #28a745;
        }
        body[data-layout-mode="dark"] #postulaciones-datatable tbody tr.estado-aprobado:hover {
            background-color: rgba(40, 167, 69, 0.25) !important;
        }
        
        body[data-layout-mode="dark"] #postulaciones-datatable tbody tr.estado-rechazado {
            background-color: rgba(220, 53, 69, 0.15) !important;
            border-left-color: #dc3545;
        }
        body[data-layout-mode="dark"] #postulaciones-datatable tbody tr.estado-rechazado:hover {
            background-color: rgba(220, 53, 69, 0.25) !important;
        }
        
        body[data-layout-mode="dark"] #postulaciones-datatable tbody tr.estado-observado {
            background-color: rgba(255, 152, 0, 0.15) !important;
            border-left-color: #ff9800;
        }
        body[data-layout-mode="dark"] #postulaciones-datatable tbody tr.estado-observado:hover {
            background-color: rgba(255, 152, 0, 0.25) !important;
        }
        
        /* Transición suave para hover */
        #postulaciones-datatable tbody tr {
            transition: background-color 0.2s ease-in-out;
        }

        /* ========================================================= */
        /* BADGES DE ESTADO EN LA COLUMNA */
        /* ========================================================= */
        
        /* Badge Pendiente */
        .badge-estado-pendiente {
            background-color: #ffc107 !important;
            color: #000 !important;
            font-weight: 600;
            padding: 0.35em 0.65em;
            font-size: 0.75rem;
        }
        
        /* Badge Aprobado */
        .badge-estado-aprobado {
            background-color: #28a745 !important;
            color: #fff !important;
            font-weight: 600;
            padding: 0.35em 0.65em;
            font-size: 0.75rem;
        }
        
        /* Badge Rechazado */
        .badge-estado-rechazado {
            background-color: #dc3545 !important;
            color: #fff !important;
            font-weight: 600;
            padding: 0.35em 0.65em;
            font-size: 0.75rem;
        }
        
        /* Badge Observado */
        .badge-estado-observado {
            background-color: #ff9800 !important;
            color: #fff !important;
            font-weight: 600;
            padding: 0.35em 0.65em;
            font-size: 0.75rem;
        }
        
        /* Modo Oscuro - Badges mantienen sus colores */
        body[data-layout-mode="dark"] .badge-estado-pendiente,
        body[data-layout-mode="dark"] .badge-estado-aprobado,
        body[data-layout-mode="dark"] .badge-estado-rechazado,
        body[data-layout-mode="dark"] .badge-estado-observado {
            opacity: 0.95;
        }

        /* ========================================================= */
        /* DISEÑO FORMAL Y CORPORATIVO PARA DATATABLE */
        /* ========================================================= */
        
        /* Contenedor de la tabla - estilo corporativo */
        .dataTables_wrapper {
            background: #ffffff;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            padding: 0;
            margin-bottom: 2rem;
            overflow: hidden;
        }
        
        body[data-layout-mode="dark"] .dataTables_wrapper {
            background: var(--cepre-dark-card);
            border-color: #4a5468;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
        }
        
        /* Tabla principal - diseño formal */
        #postulaciones-datatable {
            border-collapse: collapse !important;
            width: 100% !important;
            font-size: 0.875rem;
            margin: 0 !important;
        }
        
        /* Encabezado de la tabla - estilo corporativo azul marino */
        #postulaciones-datatable thead th {
            background: #1e3a8a !important;
            color: #ffffff !important;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.8px;
            padding: 1rem 1rem !important;
            border-bottom: 2px solid #1e40af !important;
            border-right: 1px solid rgba(255, 255, 255, 0.1) !important;
            white-space: nowrap;
            text-align: left;
        }
        
        #postulaciones-datatable thead th:last-child {
            border-right: none !important;
        }
        
        /* Iconos de ordenamiento - estilo formal */
        #postulaciones-datatable thead th.sorting:after,
        #postulaciones-datatable thead th.sorting_asc:after,
        #postulaciones-datatable thead th.sorting_desc:after {
            opacity: 0.7;
            font-size: 0.75em;
            color: #ffffff;
        }
        
        /* Cuerpo de la tabla - diseño limpio */
        #postulaciones-datatable tbody td {
            padding: 0.875rem 1rem !important;
            vertical-align: middle !important;
            border-bottom: 1px solid #e5e7eb !important;
            border-right: 1px solid #f3f4f6 !important;
            font-size: 0.875rem;
            color: #1f2937;
            background: #ffffff;
        }
        
        #postulaciones-datatable tbody td:last-child {
            border-right: none !important;
        }
        
        body[data-layout-mode="dark"] #postulaciones-datatable tbody td {
            border-bottom-color: #4a5468 !important;
            border-right-color: #3a4556 !important;
            color: var(--cepre-dark-text);
        }
        
        /* Filas alternas - estilo zebra formal */
        #postulaciones-datatable tbody tr:nth-child(even) td {
            background: #f9fafb;
        }
        
        body[data-layout-mode="dark"] #postulaciones-datatable tbody tr:nth-child(even) td {
            background: rgba(255, 255, 255, 0.02);
        }
        
        /* Efecto hover - sutil y profesional */
        #postulaciones-datatable tbody tr:hover td {
            background: #f3f4f6 !important;
            cursor: pointer;
        }
        
        body[data-layout-mode="dark"] #postulaciones-datatable tbody tr:hover td {
            background: rgba(255, 255, 255, 0.05) !important;
        }
        
        /* Código de postulante - estilo formal */
        #postulaciones-datatable tbody td:first-child {
            font-weight: 700;
            color: #1e3a8a;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            font-size: 0.8125rem;
        }
        
        body[data-layout-mode="dark"] #postulaciones-datatable tbody td:first-child {
            color: #60a5fa;
        }
        
        /* Botones de acción - diseño corporativo */
        #postulaciones-datatable .btn-group {
            display: flex;
            gap: 0.25rem;
            flex-wrap: nowrap;
            justify-content: center;
        }
        
        #postulaciones-datatable .btn-sm {
            padding: 0.375rem 0.5rem;
            border-radius: 3px;
            font-size: 0.75rem;
            transition: all 0.15s ease-in-out;
            border: 1px solid transparent;
        }
        
        #postulaciones-datatable .btn-sm:hover {
            opacity: 0.85;
            border-color: rgba(0, 0, 0, 0.1);
        }
        
        /* Badges - diseño formal y sobrio */
        #postulaciones-datatable .badge {
            padding: 0.35em 0.65em;
            border-radius: 3px;
            font-weight: 600;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border: 1px solid transparent;
        }
        
        /* Controles de DataTables - estilo formal */
        .dataTables_length,
        .dataTables_filter {
            padding: 1rem 1.25rem;
            background: #f9fafb;
            border-bottom: 1px solid #e5e7eb;
        }
        
        body[data-layout-mode="dark"] .dataTables_length,
        body[data-layout-mode="dark"] .dataTables_filter {
            background: rgba(255, 255, 255, 0.02);
            border-bottom-color: #4a5468;
        }
        
        .dataTables_length {
            float: left;
        }
        
        .dataTables_filter {
            float: right;
        }
        
        .dataTables_length label,
        .dataTables_filter label {
            font-weight: 500;
            color: #374151;
            font-size: 0.875rem;
            margin: 0;
        }
        
        body[data-layout-mode="dark"] .dataTables_length label,
        body[data-layout-mode="dark"] .dataTables_filter label {
            color: var(--cepre-dark-text);
        }
        
        .dataTables_length select,
        .dataTables_filter input {
            border: 1px solid #d1d5db;
            border-radius: 3px;
            padding: 0.375rem 0.75rem;
            margin-left: 0.5rem;
            font-size: 0.875rem;
            background: #ffffff;
        }
        
        .dataTables_length select:focus,
        .dataTables_filter input:focus {
            outline: none;
            border-color: #1e3a8a;
            box-shadow: 0 0 0 2px rgba(30, 58, 138, 0.1);
        }
        
        /* Información de paginación - estilo formal */
        .dataTables_info {
            font-size: 0.875rem;
            color: #6b7280;
            font-weight: 500;
            padding: 1rem 1.25rem;
            background: #f9fafb;
            border-top: 1px solid #e5e7eb;
        }
        
        body[data-layout-mode="dark"] .dataTables_info {
            color: #9ca3af;
            background: rgba(255, 255, 255, 0.02);
            border-top-color: #4a5468;
        }
        
        /* Paginación - diseño corporativo formal */
        .dataTables_paginate {
            padding: 1rem 1.25rem;
            background: #f9fafb;
            border-top: 1px solid #e5e7eb;
        }
        
        body[data-layout-mode="dark"] .dataTables_paginate {
            background: rgba(255, 255, 255, 0.02);
            border-top-color: #4a5468;
        }
        
        .pagination {
            margin: 0;
            gap: 0.25rem;
        }
        
        .pagination .page-link {
            border: 1px solid #d1d5db;
            color: #374151;
            padding: 0.5rem 0.875rem;
            border-radius: 3px;
            font-weight: 500;
            transition: all 0.15s ease-in-out;
            margin: 0 2px;
            background: #ffffff;
        }
        
        .pagination .page-link:hover {
            background-color: #1e3a8a;
            border-color: #1e3a8a;
            color: #ffffff;
        }
        
        .pagination .page-item.active .page-link {
            background: #1e3a8a;
            border-color: #1e3a8a;
            color: #ffffff;
            font-weight: 600;
        }
        
        .pagination .page-item.disabled .page-link {
            opacity: 0.5;
            cursor: not-allowed;
            background: #f9fafb;
        }
        
        /* Mensaje de procesamiento - estilo formal */
        .dataTables_processing {
            background: #1e3a8a !important;
            color: white !important;
            border: 1px solid #1e40af !important;
            border-radius: 3px !important;
            padding: 1rem 2rem !important;
            font-weight: 600 !important;
            box-shadow: 0 2px 8px rgba(30, 58, 138, 0.2) !important;
        }
        
        /* ========================================================= */
        /* ESTILOS RESPONSIVE PARA LA TABLA */
        /* ========================================================= */
        
        /* Reducir padding del card para dar más espacio a la tabla */
        .cepre-content-card .card-body {
            padding: 0.75rem !important;
        }
        
        /* Tabla usa todo el ancho disponible */
        .dataTables_wrapper {
            width: 100%;
            overflow-x: auto;
        }
        
        /* Tabla responsive que se ajusta al contenedor */
        #postulaciones-datatable {
            width: 100% !important;
            table-layout: auto;
        }
        
        /* Ajustar tamaño de fuente y padding según pantalla */
        #postulaciones-datatable tbody td,
        #postulaciones-datatable thead th {
            font-size: 0.75rem !important;
            padding: 0.5rem 0.35rem !important;
            white-space: nowrap;
        }
        
        /* Código de postulante más compacto */
        #postulaciones-datatable tbody td:first-child {
            font-size: 0.7rem !important;
        }
        
        /* Badges más compactos */
        #postulaciones-datatable .badge {
            padding: 0.2em 0.4em !important;
            font-size: 0.6rem !important;
            white-space: nowrap;
        }
        
        /* Botones de acción más compactos */
        #postulaciones-datatable .btn-sm {
            padding: 0.2rem 0.3rem !important;
            font-size: 0.65rem !important;
        }
        
        #postulaciones-datatable .btn-sm i {
            font-size: 0.75rem;
        }
        
        /* Grupo de botones más compacto */
        #postulaciones-datatable .btn-group {
            gap: 0.15rem !important;
        }
        
        /* Controles de DataTables más compactos */
        .dataTables_length,
        .dataTables_filter,
        .dataTables_info,
        .dataTables_paginate {
            padding: 0.75rem 1rem !important;
        }
        
        .dataTables_length label,
        .dataTables_filter label {
            font-size: 0.8rem !important;
        }
        
        .dataTables_length select,
        .dataTables_filter input {
            padding: 0.25rem 0.5rem !important;
            font-size: 0.8rem !important;
        }
        
        /* Paginación más compacta */
        .pagination .page-link {
            padding: 0.35rem 0.6rem !important;
            font-size: 0.75rem !important;
        }
        
        /* Para pantallas grandes (desktop) - todo debe caber sin scroll */
        @media (min-width: 1200px) {
            #postulaciones-datatable tbody td,
            #postulaciones-datatable thead th {
                font-size: 0.8rem !important;
                padding: 0.6rem 0.4rem !important;
            }
            
            #postulaciones-datatable .badge {
                font-size: 0.65rem !important;
            }
            
            #postulaciones-datatable .btn-sm {
                font-size: 0.7rem !important;
                padding: 0.25rem 0.35rem !important;
            }
        }
        
        /* Para pantallas medianas (tablets) */
        @media (max-width: 1199px) and (min-width: 768px) {
            #postulaciones-datatable tbody td,
            #postulaciones-datatable thead th {
                font-size: 0.7rem !important;
                padding: 0.5rem 0.3rem !important;
            }
        }
        
        /* Para pantallas pequeñas (móviles) - permitir scroll horizontal */
        @media (max-width: 767px) {
            .dataTables_wrapper {
                overflow-x: auto;
                -webkit-overflow-scrolling: touch;
            }
            
            #postulaciones-datatable {
                min-width: 800px; /* Ancho mínimo solo en móviles */
            }
            
            #postulaciones-datatable tbody td,
            #postulaciones-datatable thead th {
                font-size: 0.7rem !important;
                padding: 0.5rem 0.25rem !important;
            }
            
            .cepre-content-card .card-body {
                padding: 0.5rem !important;
            }
        }

        /* ========================================================= */
        /* DRAG & DROP DOCUMENT UPLOAD STYLES */
        /* ========================================================= */
        .document-upload-card {
            margin-bottom: 1rem;
        }

        .drop-zone {
            position: relative;
            border: 2px dashed #cbd5e0;
            border-radius: 12px;
            padding: 2rem 1rem;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
            background-color: #f8f9fa;
            min-height: 180px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .drop-zone:hover {
            border-color: var(--cepre-cyan);
            background-color: #e3f2fd;
        }

        .drop-zone.drag-over {
            border-color: var(--cepre-magenta);
            background-color: #fce4ec;
            transform: scale(1.02);
        }

        .drop-zone .file-input {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            opacity: 0;
            cursor: pointer;
        }

        .drop-zone-content {
            pointer-events: none;
        }

        .preview-container {
            position: relative;
            width: 100%;
            min-height: 180px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .preview-image {
            max-width: 100%;
            max-height: 200px;
            border-radius: 8px;
            object-fit: contain;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .pdf-preview {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .preview-overlay {
            position: absolute;
            top: 10px;
            right: 10px;
            opacity: 0;
            transition: opacity 0.3s;
        }

        .preview-container:hover .preview-overlay {
            opacity: 1;
        }

        .file-info {
            margin-top: 0.75rem;
            text-align: center;
        }

        .file-name {
            display: block;
            font-size: 0.875rem;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 0.25rem;
            word-break: break-all;
        }

        .file-size {
            display: block;
            font-size: 0.75rem;
            color: #718096;
        }

        .btn-remove {
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }

        /* Dark mode support */
        body[data-layout-mode="dark"] .drop-zone {
            background-color: var(--cepre-dark-card);
            border-color: #5a6374;
        }

        body[data-layout-mode="dark"] .drop-zone:hover {
            background-color: #4a5468;
            border-color: var(--cepre-cyan);
        }

        body[data-layout-mode="dark"] .file-name {
            color: var(--cepre-dark-text);
        }

        body[data-layout-mode="dark"] .file-size {
            color: #a0aec0;
        }
    </style>
@endpush

@push('js')
    <script>
        window.default_server = "{{ url('/') }}";
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="{{ asset('js/postulaciones/index.js') }}"></script>
    
    <script>
        // Variables globales para el flujo de postulación
        let tipoPostulante = null; // 'nuevo' o 'existente'
        let postulanteData = null; // Datos del postulante si es existente
        
        document.addEventListener('DOMContentLoaded', function() {
            const btnNuevaPostulacion = document.getElementById('btn-nueva-postulacion-unificada');
            const modalSeleccionTipo = document.getElementById('modalSeleccionTipo');
            const modalBuscarPostulante = document.getElementById('modalBuscarPostulante');
            const modalNuevaPostulacion = document.getElementById('nuevaPostulacionModal');
            
            // Botón principal - Abre modal de selección de tipo
            if (btnNuevaPostulacion) {
                btnNuevaPostulacion.addEventListener('click', function() {
                    const modal = new bootstrap.Modal(modalSeleccionTipo);
                    modal.show();
                });
            }
            
            // Botón Postulante Nuevo
            document.getElementById('btnPostulanteNuevo').addEventListener('click', function() {
                tipoPostulante = 'nuevo';
                bootstrap.Modal.getInstance(modalSeleccionTipo).hide();
                
                // Mostrar modal de registro para crear cuenta primero
                mostrarModalRegistroNuevo();
            });
            
            // Botón Postulante Existente
            document.getElementById('btnPostulanteExistente').addEventListener('click', function() {
                tipoPostulante = 'existente';
                bootstrap.Modal.getInstance(modalSeleccionTipo).hide();
                
                // Abrir modal de búsqueda
                const modalBuscar = new bootstrap.Modal(modalBuscarPostulante);
                modalBuscar.show();
            });
            
            // Botón Buscar por DNI
            document.getElementById('btnBuscarPorDNI').addEventListener('click', function() {
                const dni = document.getElementById('dniPostulanteExistente').value;
                
                if (dni.length !== 8) {
                    toastr.error('El DNI debe tener 8 dígitos', 'Error');
                    return;
                }
                
                buscarPostulanteExistente(dni);
            });
            
            // Botón Volver a Selección
            document.getElementById('btnVolverSeleccion').addEventListener('click', function() {
                bootstrap.Modal.getInstance(modalBuscarPostulante).hide();
                const modalSeleccion = new bootstrap.Modal(modalSeleccionTipo);
                modalSeleccion.show();
                
                // Limpiar búsqueda
                document.getElementById('dniPostulanteExistente').value = '';
                document.getElementById('resultadoBusqueda').style.display = 'none';
                document.getElementById('btnContinuarPostulacion').style.display = 'none';
            });
            
            // Botón Continuar con Postulación (para existentes)
            document.getElementById('btnContinuarPostulacion').addEventListener('click', function() {
                bootstrap.Modal.getInstance(modalBuscarPostulante).hide();
                loadFormularioCompleto('existente');
            });
            
            // Función para buscar postulante existente
            function buscarPostulanteExistente(dni) {
                const resultadoDiv = document.getElementById('resultadoBusqueda');
                const btnContinuar = document.getElementById('btnContinuarPostulacion');
                
                // Mostrar loading
                resultadoDiv.innerHTML = '<div class="text-center"><div class="spinner-border spinner-border-sm"></div> Buscando...</div>';
                resultadoDiv.style.display = 'block';
                
                // Hacer petición AJAX
                fetch(`{{ url('/api/postulantes/buscar') }}/${dni}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.postulante) {
                        postulanteData = data.postulante;
                        
                        // Mostrar datos del postulante encontrado
                        resultadoDiv.innerHTML = `
                            <div class="alert alert-success">
                                <h6 class="mb-2"><i class="bi bi-check-circle-fill"></i> Postulante encontrado</h6>
                                <p class="mb-1"><strong>Nombre:</strong> ${data.postulante.nombre} ${data.postulante.apellido_paterno} ${data.postulante.apellido_materno}</p>
                                <p class="mb-1"><strong>DNI:</strong> ${data.postulante.numero_documento}</p>
                                <p class="mb-0"><strong>Email:</strong> ${data.postulante.email}</p>
                            </div>
                        `;
                        
                        btnContinuar.style.display = 'block';
                        btnContinuar.disabled = false;
                    } else {
                        resultadoDiv.innerHTML = `
                            <div class="alert alert-warning">
                                <i class="bi bi-exclamation-triangle-fill"></i> No se encontró un postulante con DNI: ${dni}
                                <br><small class="text-muted">Puede crear una nueva cuenta seleccionando "Soy Postulante Nuevo"</small>
                            </div>
                        `;
                        btnContinuar.style.display = 'none';
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    resultadoDiv.innerHTML = '<div class="alert alert-danger">Error al buscar el postulante</div>';
                    btnContinuar.style.display = 'none';
                });
            }
            
            // ============================================
            // Funciones del WIZARD (Mantenidas)
            // ============================================
            
            document.getElementById('btnConsultarReniecNuevo').addEventListener('click', consultarReniecRegistro);
            document.getElementById('btnConsultarReniecPadre').addEventListener('click', consultarReniecPadre);
            document.getElementById('btnConsultarReniecMadre').addEventListener('click', consultarReniecMadre);
            
            const formRegistro = document.getElementById('formRegistroNuevo');
            formRegistro.addEventListener('input', function(e) { actualizarContadorCamposPaso(wizardCurrentStep); });
            formRegistro.addEventListener('change', function(e) { actualizarContadorCamposPaso(wizardCurrentStep); });
            document.getElementById('nuevo_password_confirmation').addEventListener('input', validarPasswordsRegistro);
            formRegistro.addEventListener('submit', function(e) { e.preventDefault(); });
            
            let cicloActivo = null;
            let colegioSeleccionado = null;
            let currentFormData = null;
            
            function loadFormularioCompleto(tipo) {
                const modalPostulacion = new bootstrap.Modal(modalNuevaPostulacion);
                const container = document.getElementById('postulacion-form-container');
                const titulo = document.getElementById('tituloModalPostulacion');
                
                if (tipo === 'nuevo') {
                    titulo.textContent = 'Nueva Postulación - Registro y Datos Completos';
                } else {
                    titulo.textContent = 'Nueva Postulación - ' + (postulanteData ? postulanteData.nombre : 'Postulante Existente');
                }
                
                modalPostulacion.show();
                
                container.innerHTML = `
                    <div class="text-center py-4">
                        <div class="cepre-spinner"></div>
                        <p class="mt-3 text-muted">Preparando formulario de postulación...</p>
                    </div>
                `;
                
                Promise.all([
                    fetch('{{ url("/json/inscripciones-estudiante/ciclo-activo") }}').then(r => r.json()),
                    fetch('{{ url("/json/inscripciones-estudiante/departamentos") }}').then(r => r.json())
                ])
                .then(([cicloData, departamentosData]) => {
                    if (cicloData.success && departamentosData.success) {
                        cicloActivo = cicloData.ciclo;
                        generarFormularioDirecto(tipo, cicloData, departamentosData);
                    } else {
                        throw new Error('Error al cargar datos del formulario');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    container.innerHTML = '<div class="alert alert-danger">Error al cargar el formulario. Por favor, inténtelo nuevamente.</div>';
                });
            }
            
            function generarFormularioDirecto(tipo, cicloData, departamentosData) {
                const container = document.getElementById('postulacion-form-container');
                
                let carrerasOptions = '<option value="">Seleccione una carrera...</option>';
                cicloData.carreras.forEach(carrera => {
                    const vacantesText = carrera.vacantes_disponibles === 'Sin límite' ?
                        'Sin límite' : `${carrera.vacantes_disponibles} vacantes`;
                    carrerasOptions += `<option value="${carrera.id}" ${!carrera.tiene_vacantes ? 'disabled' : ''}>
                        ${carrera.nombre} (${vacantesText})
                    </option>`;
                });

                let turnosOptions = '<option value="">Seleccione un turno...</option>';
                cicloData.turnos.forEach(turno => {
                    turnosOptions += `<option value="${turno.id}">${turno.nombre} (${turno.hora_inicio} - ${turno.hora_fin})</option>`;
                });

                let tiposOptions = '<option value="">Seleccione tipo...</option>';
                cicloData.tipos_inscripcion.forEach(tipo => {
                    tiposOptions += `<option value="${tipo.value}">${tipo.label}</option>`;
                });
                
                let departamentosOptions = '<option value="">Seleccione departamento...</option>';
                departamentosData.departamentos.forEach(depto => {
                    departamentosOptions += `<option value="${depto}">${depto}</option>`;
                });

                // Generar HTML del formulario completo con estilo CEPRE (Usando el código original del usuario)
                container.innerHTML = `
                    <div class="cepre-form-container">
                        <div class="card border-0 shadow-sm">
                            <div class="card-header text-white" style="background: linear-gradient(135deg, var(--cepre-magenta) 0%, var(--cepre-navy) 100%);">
                                <h4 class="mb-0 text-white">
                                    <i class="bi bi-card-list me-2"></i>Formulario de Inscripción y Postulación
                                </h4>
                            </div>
                            <div class="card-body">
                                <form id="formPostulacionIntegrado">
                                    <div class="row">
                                        <!-- Tipo de inscripción -->
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Tipo de Inscripción <span class="text-danger">*</span></label>
                                            <select class="form-select" id="tipo_inscripcion" name="tipo_inscripcion" required>
                                                ${tiposOptions}
                                            </select>
                                        </div>

                                        <!-- Carrera -->
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Carrera Profesional <span class="text-danger">*</span></label>
                                            <select class="form-select" id="carrera_id" name="carrera_id" required>
                                                ${carrerasOptions}
                                            </select>
                                        </div>

                                        <!-- Turno -->
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Turno <span class="text-danger">*</span></label>
                                            <select class="form-select" id="turno_id" name="turno_id" required>
                                                ${turnosOptions}
                                            </select>
                                        </div>

                                        <!-- Centro Educativo -->
                                        <div class="col-12">
                                            <h5 class="mt-3 mb-3" style="color: var(--cepre-navy);">Institución Educativa</h5>
                                        </div>

                                        <!-- Departamento -->
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Departamento <span class="text-danger">*</span></label>
                                            <select class="form-select" id="departamento" required>
                                                ${departamentosOptions}
                                            </select>
                                        </div>

                                        <!-- Provincia -->
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Provincia <span class="text-danger">*</span></label>
                                            <select class="form-select" id="provincia" disabled required>
                                                <option value="">Seleccione departamento primero</option>
                                            </select>
                                        </div>

                                        <!-- Distrito -->
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Distrito <span class="text-danger">*</span></label>
                                            <select class="form-select" id="distrito" disabled required>
                                                <option value="">Seleccione provincia primero</option>
                                            </select>
                                        </div>

                                        <!-- Búsqueda de colegio -->
                                        <div class="col-md-8 mb-3">
                                            <label class="form-label">Nombre del Colegio <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="buscar_colegio"
                                                     placeholder="Escriba el nombre del colegio..." disabled>
                                            <div id="sugerencias-colegios" class="list-group mt-1" style="max-height: 200px; overflow-y: auto;"></div>
                                        </div>

                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">&nbsp;</label>
                                            <button type="button" class="btn btn-secondary w-100" id="btnBuscarColegio" disabled>
                                                <i class="bi bi-search"></i> Buscar
                                            </button>
                                        </div>

                                        <!-- Colegio seleccionado -->
                                        <div class="col-12 mb-3" id="colegio-seleccionado" style="display: none;">
                                            <div class="alert alert-info">
                                                <strong>Colegio seleccionado:</strong>
                                                <span id="nombre-colegio-seleccionado"></span>
                                            </div>
                                        </div>

                                        <!-- Sección de Documentos -->
                                        <div class="col-12">
                                            <h5 class="mt-4 mb-3" style="color: var(--cepre-navy);">Documentos Requeridos</h5>
                                        </div>

                                        <!-- Voucher de Pago -->
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Voucher de Pago <span class="text-danger">*</span></label>
                                            <input type="file" class="form-control" id="voucher_pago" name="voucher_pago"
                                                     accept=".pdf,.jpg,.jpeg,.png" required>
                                            <small class="text-muted">PDF, JPG o PNG (Max: 5MB)</small>
                                        </div>

                                        <!-- Certificado de Estudios -->
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Certificado de Estudios <span class="text-danger">*</span></label>
                                            <input type="file" class="form-control" id="certificado_estudios" name="certificado_estudios"
                                                     accept=".pdf,.jpg,.jpeg,.png" required>
                                            <small class="text-muted">PDF, JPG o PNG (Max: 5MB)</small>
                                        </div>

                                        <!-- Carta de Compromiso -->
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Carta de Compromiso <span class="text-danger">*</span></label>
                                            <input type="file" class="form-control" id="carta_compromiso" name="carta_compromiso"
                                                     accept=".pdf,.jpg,.jpeg,.png" required>
                                            <small class="text-muted">PDF, JPG o PNG (Max: 5MB)</small>
                                        </div>

                                        <!-- Constancia de Estudios -->
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Constancia de Estudios <span class="text-danger">*</span></label>
                                            <input type="file" class="form-control" id="constancia_estudios" name="constancia_estudios"
                                                     accept=".pdf,.jpg,.jpeg,.png" required>
                                            <small class="text-muted">PDF, JPG o PNG (Max: 5MB)</small>
                                        </div>

                                        <!-- DNI -->
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">DNI <span class="text-danger">*</span></label>
                                            <input type="file" class="form-control" id="dni_documento" name="dni_documento"
                                                     accept=".pdf,.jpg,.jpeg,.png" required>
                                            <small class="text-muted">PDF, JPG o PNG (Max: 5MB)</small>
                                        </div>

                                        <!-- Foto Carnet -->
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Foto Carnet <span class="text-danger">*</span></label>
                                            <input type="file" class="form-control" id="foto_carnet" name="foto_carnet"
                                                     accept=".jpg,.jpeg,.png" required>
                                            <small class="text-muted">JPG o PNG (Max: 2MB)</small>
                                        </div>

                                        <!-- Sección de Datos del Voucher -->
                                        <div class="col-12" id="seccion-voucher" style="display: none;">
                                            <h5 class="mt-4 mb-3" style="color: var(--cepre-navy);">Datos del Voucher de Pago</h5>
                                        </div>

                                        <!-- Número de Recibo -->
                                        <div class="col-md-6 mb-3" style="display: none;" id="campo-numero-recibo">
                                            <label class="form-label">Número de Recibo <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="numero_recibo" name="numero_recibo"
                                                     placeholder="Ej: 002-0001234" value="002-000" required>
                                        </div>

                                        <!-- Fecha de Emisión -->
                                        <div class="col-md-6 mb-3" style="display: none;" id="campo-fecha-emision">
                                            <label class="form-label">Fecha de Emisión <span class="text-danger">*</span></label>
                                            <input type="date" class="form-control" id="fecha_emision_voucher" name="fecha_emision_voucher" required>
                                        </div>

                                        <!-- Monto Matrícula -->
                                        <div class="col-md-6 mb-3" style="display: none;" id="campo-monto-matricula">
                                            <label class="form-label">Matrícula de Ciclo de Preparación General (S/.) <span class="text-danger">*</span></label>
                                            <h6 class="mt-2 mb-1">Opción 1 Matrícula Regular:</h6>
                                            <div class="d-flex justify-content-between mb-2">
                                                <button type="button" class="btn btn-outline-secondary btn-sm btn-block btn-matricula flex-grow-1 me-2" data-value="100">S/ 100</button>
                                            </div>
                                            <h6 class="mt-2 mb-1">Opción 2 Descuento 50% (Resolución):</h6>
                                            <div class="d-flex justify-content-between mb-2">
                                                <button type="button" class="btn btn-outline-secondary btn-sm btn-block btn-matricula flex-grow-1" data-value="50">S/ 50</button>
                                            </div>
                                            <input type="number" class="form-control" id="monto_matricula" name="monto_matricula"
                                                     step="0.01" min="0" placeholder="0.00" required>
                                        </div>

                                        <!-- Monto Enseñanza -->
                                        <div class="col-md-6 mb-3" style="display: none;" id="campo-monto-ensenanza">
                                            <label class="form-label">Costo de Enseñanza por Preparación (S/.) <span class="text-danger">*</span></label>
                                            <h6 class="mt-2 mb-1">Opción 1 Enseñanza Regular:</h6>
                                            <div class="d-flex justify-content-between mb-2">
                                                <button type="button" class="btn btn-outline-secondary btn-sm btn-block btn-ensenanza flex-grow-1 me-2" data-value="1050">S/ 1050</button>
                                            </div>
                                            <h6 class="mt-2 mb-1">Opción 2 Descuento 50% (Resolución):</h6>
                                            <div class="d-flex justify-content-between mb-2">
                                                <button type="button" class="btn btn-outline-secondary btn-sm btn-block btn-ensenanza flex-grow-1" data-value="525">S/ 525</button>
                                            </div>
                                            <input type="number" class="form-control" id="monto_ensenanza" name="monto_ensenanza"
                                                     step="0.01" min="0" placeholder="0.00" required>
                                        </div>

                                        <!-- Subtotal -->
                                        <div class="col-12 mb-3" style="display: none;" id="campo-subtotal">
                                            <div class="alert alert-success">
                                                <div class="row align-items-center">
                                                    <div class="col-md-8">
                                                        <strong>SUBTOTAL A PAGAR:</strong>
                                                    </div>
                                                    <div class="col-md-4 text-end">
                                                        <h4 class="mb-0">S/. <span id="monto_subtotal">0.00</span></h4>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="text-center mt-4">
                                        <button type="submit" class="btn btn-cepre-inscribir">
                                            <i class="bi bi-send-check-fill me-2"></i>Inscribirme Ahora
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                `;
                
                // Configurar todos los eventos del formulario
                configurarEventosFormulario();
                
                toastr.success('Formulario cargado correctamente', 'Listo');
            }
            
            // Función para configurar todos los eventos del formulario integrado
            function configurarEventosFormulario() {
                // Cambio de departamento
                $('#departamento').on('change', function() {
                    const depto = $(this).val();
                    if (depto) {
                        cargarProvincias(depto);
                        $('#provincia').prop('disabled', false);
                        $('#distrito').prop('disabled', true).html('<option value="">Seleccione provincia primero</option>');
                        $('#buscar_colegio').prop('disabled', true).val('');
                        $('#btnBuscarColegio').prop('disabled', true);
                        $('#sugerencias-colegios').empty();
                        ocultarColegioSeleccionado();
                    }
                });

                // Cambio de provincia
                $('#provincia').on('change', function() {
                    const depto = $('#departamento').val();
                    const prov = $(this).val();
                    if (prov) {
                        cargarDistritos(depto, prov);
                        $('#distrito').prop('disabled', false);
                        $('#buscar_colegio').prop('disabled', true).val('');
                        $('#btnBuscarColegio').prop('disabled', true);
                        $('#sugerencias-colegios').empty();
                        ocultarColegioSeleccionado();
                    }
                });

                // Cambio de distrito
                $('#distrito').on('change', function() {
                    if ($(this).val()) {
                        $('#buscar_colegio').prop('disabled', false);
                        $('#btnBuscarColegio').prop('disabled', false);
                        $('#sugerencias-colegios').empty();
                        ocultarColegioSeleccionado();
                        
                        // Cargar colegios automáticamente
                        buscarColegios();
                    }
                });

                // Búsqueda de colegio
                $('#btnBuscarColegio').on('click', buscarColegios);
                $('#buscar_colegio').on('keyup', function() {
                    const searchTerm = $(this).val();
                    if (searchTerm.length >= 2) {
                        buscarColegios();
                    } else {
                        $('#sugerencias-colegios').empty();
                    }
                });
                $('#buscar_colegio').on('keypress', function(e) {
                    if (e.which === 13) {
                        e.preventDefault();
                        buscarColegios();
                    }
                });

                // Cambio en el archivo de voucher
                $('#voucher_pago').on('change', function() {
                    if (this.files && this.files[0]) {
                        $('#seccion-voucher').show();
                        $('#campo-numero-recibo').show();
                        $('#campo-fecha-emision').show();
                        $('#campo-monto-matricula').show();
                        $('#campo-monto-ensenanza').show();
                        $('#campo-subtotal').show();
                    } else {
                        $('#seccion-voucher').hide();
                        $('#campo-numero-recibo').hide();
                        $('#campo-fecha-emision').hide();
                        $('#campo-monto-matricula').hide();
                        $('#campo-monto-ensenanza').hide();
                        $('#campo-subtotal').hide();
                    }
                });

                // Calcular subtotal cuando cambien los montos
                $('#monto_matricula, #monto_ensenanza').on('input', function() {
                    const matricula = parseFloat($('#monto_matricula').val()) || 0;
                    const ensenanza = parseFloat($('#monto_ensenanza').val()) || 0;
                    const subtotal = matricula + ensenanza;
                    $('#monto_subtotal').text(subtotal.toFixed(2));
                });

                // Eventos para los botones de matrícula
                $(document).on('click', '.btn-matricula', function() {
                    if ($('#campo-monto-matricula').is(':hidden')) {
                        toastr.info('Por favor, sube primero el voucher de pago.');
                        return;
                    }
                    const value = $(this).data('value');
                    $('#monto_matricula').val(value).trigger('input');
                });

                // Eventos para los botones de enseñanza
                $(document).on('click', '.btn-ensenanza', function() {
                    if ($('#campo-monto-ensenanza').is(':hidden')) {
                        toastr.info('Por favor, sube primero el voucher de pago.');
                        return;
                    }
                    const value = $(this).data('value');
                    $('#monto_ensenanza').val(value).trigger('input');
                });

                // Validación visual para archivos
                $('input[type="file"]').on('change', function() {
                    const fileName = $(this).val().split('\\').pop();
                    const inputId = $(this).attr('id');
                    const labelText = $(`label[for="${inputId}"]`).text().replace(' *', '');

                    if (fileName) {
                        $(this).removeClass('is-invalid').addClass('is-valid');
                        toastr.info(`${labelText}: ${fileName}`, 'Archivo seleccionado', {
                            "closeButton": false,
                            "progressBar": false,
                            "positionClass": "toast-bottom-left",
                            "timeOut": "1500"
                        });
                    } else {
                        $(this).removeClass('is-valid is-invalid');
                    }
                });

                // Envío del formulario
                $('#formPostulacionIntegrado').on('submit', function(e) {
                    e.preventDefault();
                    if (!colegioSeleccionado) {
                        toastr.warning('Por favor seleccione un colegio de la lista');
                        return;
                    }

                    // Validar archivos
                    const archivosRequeridos = [
                        { id: 'voucher_pago', nombre: 'Voucher de Pago' },
                        { id: 'certificado_estudios', nombre: 'Certificado de Estudios' },
                        { id: 'carta_compromiso', nombre: 'Carta de Compromiso' },
                        { id: 'constancia_estudios', nombre: 'Constancia de Estudios' },
                        { id: 'dni_documento', nombre: 'DNI' },
                        { id: 'foto_carnet', nombre: 'Foto Carnet' }
                    ];

                    for (let archivo of archivosRequeridos) {
                        const input = document.getElementById(archivo.id);
                        if (!input.files || !input.files[0]) {
                            toastr.warning(`Por favor seleccione el archivo: ${archivo.nombre}`);
                            return;
                        }
                    }

                    mostrarConfirmacionIntegrada();
                });
            }
            
            // Funciones auxiliares para geografía
            function cargarProvincias(departamento) {
                fetch(`{{ url('/json/inscripciones-estudiante/provincias') }}/${encodeURIComponent(departamento)}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            let options = '<option value="">Seleccione provincia...</option>';
                            data.provincias.forEach(prov => {
                                options += `<option value="${prov}">${prov}</option>`;
                            });
                            $('#provincia').html(options);
                        }
                    });
            }

            function cargarDistritos(departamento, provincia) {
                fetch(`{{ url('/json/inscripciones-estudiante/distritos') }}/${encodeURIComponent(departamento)}/${encodeURIComponent(provincia)}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            let options = '<option value="">Seleccione distrito...</option>';
                            data.distritos.forEach(dist => {
                                options += `<option value="${dist}">${dist}</option>`;
                            });
                            $('#distrito').html(options);
                        }
                    });
            }

            function buscarColegios() {
                const termino = $('#buscar_colegio').val();
                if (termino.length < 2 && termino.length !== 0) {
                    $('#sugerencias-colegios').empty();
                    return;
                }

                const requestData = {
                    departamento: $('#departamento').val(),
                    provincia: $('#provincia').val(),
                    distrito: $('#distrito').val(),
                    termino: termino,
                    _token: $('meta[name="csrf-token"]').attr('content')
                };

                fetch('{{ url("/json/inscripciones-estudiante/buscar-colegios") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    body: JSON.stringify(requestData)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        mostrarSugerenciasColegios(data.colegios);
                    }
                });
            }

            function mostrarSugerenciasColegios(colegios) {
                let html = '';
                if (colegios.length === 0) {
                    html = '<div class="list-group-item">No se encontraron colegios</div>';
                } else {
                    colegios.forEach(colegio => {
                        html += `
                            <a href="#" class="list-group-item list-group-item-action seleccionar-colegio"
                               data-id="${colegio.id}" data-nombre="${colegio.nombre}">
                                <strong>${colegio.nombre}</strong>
                                ${colegio.nivel ? `<br><small>Nivel: ${colegio.nivel}</small>` : ''}
                                ${colegio.direccion ? `<br><small>${colegio.direccion}</small>` : ''}
                            </a>
                        `;
                    });
                }
                $('#sugerencias-colegios').html(html);

                // Evento para seleccionar colegio
                $('.seleccionar-colegio').on('click', function(e) {
                    e.preventDefault();
                    colegioSeleccionado = {
                        id: $(this).data('id'),
                        nombre: $(this).data('nombre')
                    };
                    mostrarColegioSeleccionado();
                    $('#sugerencias-colegios').empty();
                });
            }

            function mostrarColegioSeleccionado() {
                $('#nombre-colegio-seleccionado').text(colegioSeleccionado.nombre);
                $('#colegio-seleccionado').show();
                $('#buscar_colegio').val(colegioSeleccionado.nombre);
            }

            function ocultarColegioSeleccionado() {
                colegioSeleccionado = null;
                $('#colegio-seleccionado').hide();
            }
            
            // Función para mostrar confirmación con SweetAlert2
            function mostrarConfirmacionIntegrada() {
                const tipo = $('#tipo_inscripcion option:selected').text();
                const carrera = $('#carrera_id option:selected').text();
                const turno = $('#turno_id option:selected').text();
                const numeroRecibo = $('#numero_recibo').val();
                const montoMatricula = parseFloat($('#monto_matricula').val()) || 0;
                const montoEnsenanza = parseFloat($('#monto_ensenanza').val()) || 0;
                const montoTotal = montoMatricula + montoEnsenanza;

                const resumenHTML = `
                    <div style="text-align: left;">
                        <ul style="list-style: none; padding: 0;">
                            <li style="padding: 5px 0;"><strong>Tipo:</strong> ${tipo}</li>
                            <li style="padding: 5px 0;"><strong>Carrera:</strong> ${carrera}</li>
                            <li style="padding: 5px 0;"><strong>Turno:</strong> ${turno}</li>
                            <li style="padding: 5px 0;"><strong>Colegio:</strong> ${colegioSeleccionado.nombre}</li>
                            <li style="padding: 5px 0;"><strong>Documentos:</strong> 6 archivos seleccionados</li>
                            ${numeroRecibo ? `<li style="padding: 5px 0;"><strong>N° Recibo:</strong> ${numeroRecibo}</li>` : ''}
                            ${montoMatricula > 0 ? `<li style="padding: 5px 0;"><strong>Matrícula:</strong> S/. ${montoMatricula.toFixed(2)}</li>` : ''}
                            ${montoEnsenanza > 0 ? `<li style="padding: 5px 0;"><strong>Enseñanza:</strong> S/. ${montoEnsenanza.toFixed(2)}</li>` : ''}
                            ${montoTotal > 0 ? `<li style="padding: 5px 0;"><strong><span style="color: #28a745;">TOTAL PAGADO: S/. ${montoTotal.toFixed(2)}</span></strong></li>` : ''}
                        </ul>
                    </div>
                `;

                Swal.fire({
                    title: '¡Confirmar Postulación!',
                    html: `<p style="margin-bottom: 20px;">Revise cuidadosamente los datos antes de enviar:</p>${resumenHTML}`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: '<i class="bi bi-check-lg"></i> Confirmar Postulación',
                    cancelButtonText: '<i class="bi bi-x"></i> Cancelar',
                    reverseButtons: true,
                    customClass: {
                        confirmButton: 'swal2-confirm',
                        cancelButton: 'swal2-cancel'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        enviarFormularioPostulacion();
                    }
                });
            }

            // Función para enviar el formulario de postulación
            function enviarFormularioPostulacion() {
                // Crear FormData para enviar archivos
                const formData = new FormData();

                // Agregar datos básicos
                formData.append('tipo_inscripcion', $('#tipo_inscripcion').val());
                formData.append('carrera_id', $('#carrera_id').val());
                formData.append('turno_id', $('#turno_id').val());
                formData.append('centro_educativo_id', colegioSeleccionado.id);

                // Agregar archivos
                const archivos = [
                    {id: 'voucher_pago', nombre: 'Voucher de pago'},
                    {id: 'certificado_estudios', nombre: 'Certificado de estudios'},
                    {id: 'carta_compromiso', nombre: 'Carta de compromiso'},
                    {id: 'constancia_estudios', nombre: 'Constancia de estudios'},
                    {id: 'dni_documento', nombre: 'DNI'},
                    {id: 'foto_carnet', nombre: 'Foto carnet'}
                ];

                let archivosSubidos = 0;
                archivos.forEach(function(archivo, index) {
                    const input = document.getElementById(archivo.id);
                    if (input.files && input.files[0]) {
                        formData.append(archivo.id, input.files[0]);
                        archivosSubidos++;
                        setTimeout(() => {
                            toastr.info(`${archivo.nombre} cargado correctamente (${archivosSubidos}/6)`, 'Archivo ' + archivosSubidos, {
                                "closeButton": false,
                                "progressBar": true,
                                "positionClass": "toast-bottom-right",
                                "timeOut": "2000"
                            });
                        }, index * 300);
                    }
                });

                // Agregar datos del voucher
                formData.append('numero_recibo', $('#numero_recibo').val());
                formData.append('fecha_emision_voucher', $('#fecha_emision_voucher').val());
                formData.append('monto_matricula', $('#monto_matricula').val() || 0);
                formData.append('monto_ensenanza', $('#monto_ensenanza').val() || 0);

                // Agregar token CSRF
                formData.append('_token', $('meta[name="csrf-token"]').attr('content'));

                // IMPORTANTE: Determinar el endpoint y datos correctos según el tipo
                let urlEndpoint = '{{ url("/json/postulaciones/crear") }}'; // Endpoint unificado para crear postulaciones
                
                if (tipoPostulante === 'existente' && postulanteData) {
                    // Para postulante existente, usar su ID y NO el del admin logueado
                    formData.append('estudiante_id', postulanteData.id); // ID del usuario/postulante encontrado por DNI
                    
                    // Agregar datos del estudiante para evitar confusión
                    formData.append('estudiante_dni', postulanteData.numero_documento);
                    formData.append('estudiante_nombre', postulanteData.nombre);
                    formData.append('estudiante_apellido_paterno', postulanteData.apellido_paterno);
                    formData.append('estudiante_apellido_materno', postulanteData.apellido_materno);
                    
                } else if (tipoPostulante === 'nuevo') {
                    // Para postulante nuevo, el admin está creando tanto el usuario como la postulación
                    formData.append('crear_usuario_nuevo', '1');
                    
                    // Aquí deberían ir los datos del nuevo usuario (nombre, DNI, email, etc.)
                    // Estos vendrían del formulario de registro completo
                }

                // Mostrar loading con SweetAlert2
                Swal.fire({
                    title: 'Procesando Postulación',
                    html: `
                        <div style="text-align: center;">
                            <div class="cepre-spinner" style="margin: 20px auto;"></div>
                            <p>Enviando documentos y datos...</p>
                            <div id="progress-info">Iniciando envío...</div>
                        </div>
                    `,
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false
                });

                // Realizar petición AJAX con el endpoint correcto
                $.ajax({
                    url: urlEndpoint, // Usar el endpoint determinado según el tipo
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    xhr: function() {
                        var xhr = new window.XMLHttpRequest();
                        xhr.upload.addEventListener("progress", function(evt) {
                            if (evt.lengthComputable) {
                                var percentComplete = ((evt.loaded / evt.total) * 100).toFixed(0);
                                $('#progress-info').html(`Subiendo... ${percentComplete}%`);
                            }
                        }, false);
                        return xhr;
                    },
                    success: function(response) {
                        Swal.close();
                        
                        if (response.success) {
                            // Mostrar toast de éxito escalonado
                            setTimeout(() => {
                                toastr.success('¡Todos tus archivos fueron subidos exitosamente!', 'Archivos Completos', {
                                    "closeButton": true,
                                    "progressBar": true,
                                    "positionClass": "toast-top-center",
                                    "timeOut": "3000"
                                });
                            }, archivos.length * 300 + 500);

                            if (response.postulacion) {
                                setTimeout(() => {
                                    Swal.fire({
                                        icon: 'success',
                                        title: '¡Postulación Exitosa!',
                                        text: 'Tu postulación ha sido enviada exitosamente. Te notificaremos cuando sea revisada.',
                                        confirmButtonText: 'Entendido'
                                    }).then(() => {
                                        // Cerrar modal
                                        $('#nuevaPostulacionModal').modal('hide');

                                        // Buscar DNI para filtrar la tabla
                                        let dniToSearch = null;
                                        if (response.data && response.data.dni) {
                                            dniToSearch = response.data.dni;
                                        } else if (tipoPostulante === 'nuevo') {
                                            dniToSearch = $('#nuevo_numero_documento').val();
                                        } else if (tipoPostulante === 'existente' && postulanteData) {
                                            dniToSearch = postulanteData.numero_documento;
                                        }

                                        if (typeof table !== 'undefined' && table && dniToSearch) {
                                            // Limpiar filtros existentes
                                            $('#filter-ciclo').val('').trigger('change');
                                            $('#filter-estado').val('').trigger('change');
                                            $('#filter-carrera').val('').trigger('change');
                                            
                                            // Aplicar búsqueda por DNI y redibujar la tabla
                                            table.search(dniToSearch).draw();
                                            
                                            toastr.info('Mostrando solo la postulación creada. Limpie el filtro de búsqueda para ver todos los registros.', 'Filtro Aplicado', {timeOut: 6000});
                                        } else if (typeof table !== 'undefined' && table) {
                                            // Si no se pudo obtener el DNI, simplemente recargar la tabla
                                            table.ajax.reload();
                                        }
                                    });
                                }, archivos.length * 300 + 2000);
                            } else {
                                setTimeout(() => {
                                    Swal.fire({
                                        icon: 'success',
                                        title: '¡Inscripción Exitosa!',
                                        text: response.message || 'Inscripción realizada correctamente',
                                        confirmButtonText: 'Entendido'
                                    }).then(() => {
                                        $('#nuevaPostulacionModal').modal('hide');
                                        if (typeof table !== 'undefined' && table) {
                                            table.ajax.reload();
                                        }
                                    });
                                }, archivos.length * 300 + 2000);
                            }
                        }
                    },
                    error: function(xhr) {
                        Swal.close();
                        const response = xhr.responseJSON;
                        let errorMsg = 'Error al procesar la postulación';
                        
                        if (response && response.errors) {
                            let errores = '';
                            $.each(response.errors, function(key, value) {
                                errores += value[0] + '\n';
                            });
                            errorMsg = errores;
                        } else if (response && response.message) {
                            errorMsg = response.message;
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Error en el Envío',
                            text: errorMsg,
                            confirmButtonText: 'Entendido'
                        });
                    }
                });
            }
            
            // Función para inicializar wizard de registro completo
            function initWizardRegistroCompleto() {
                // Cargar script de wizard para registro + postulación
                const script = document.createElement('script');
                script.src = "{{ asset('js/postulaciones/wizard-completo.js') }}";
                script.onload = function() {
                    console.log('Wizard completo inicializado');
                };
                document.head.appendChild(script);
            }
            
            // Función para inicializar wizard de postulación simple
            function initWizardPostulacion() {
                // Para usuarios existentes, usar un formulario simplificado
                // que solo pida datos académicos y documentos
                const script = document.createElement('script');
                script.src = "{{ asset('js/postulaciones/wizard-simplificado.js') }}";
                script.onload = function() {
                    console.log('Wizard simplificado inicializado para postulante existente');
                    
                    // Pasar los datos del postulante al wizard
                    if (window.initWizardSimplificado) {
                        window.initWizardSimplificado(postulanteData);
                    }
                };
                document.head.appendChild(script);
            }
            
            // Escuchar mensajes para cerrar el modal cuando se complete la postulación
            window.addEventListener('message', function(event) {
                if (event.data && event.data.type === 'postulacion-completada') {
                    // Cerrar todos los modales
                    const modales = [modalNuevaPostulacion, modalBuscarPostulante, modalSeleccionTipo];
                    modales.forEach(modalEl => {
                        const modal = bootstrap.Modal.getInstance(modalEl);
                        if (modal) modal.hide();
                    });
                    
                    // Limpiar datos
                    tipoPostulante = null;
                    postulanteData = null;
                    
                    // Mostrar mensaje de éxito
                    toastr.success('Postulación creada exitosamente', 'Éxito');
                    
                    // Actualizar la lista
                    if (typeof refreshPostulacionesList === 'function') {
                        refreshPostulacionesList();
                    } else {
                        setTimeout(() => {
                            window.location.reload();
                        }, 2000);
                    }
                }
            });
        });
        
        // ============================================
        // WIZARD DE REGISTRO COMPLETO DE NUEVOS POSTULANTES
        // ============================================
        
        // Variables globales del wizard
        let wizardCurrentStep = 1;
        const wizardTotalSteps = 4;
        let nuevoUsuarioData = null;
        let wizardFormData = {};
        const modalRegistroNuevo = document.getElementById('modalRegistroNuevo');
        
        // Función para mostrar el modal de registro
        function mostrarModalRegistroNuevo() {
            const modal = new bootstrap.Modal(modalRegistroNuevo);
            modal.show();
            
            // Resetear wizard
            wizardCurrentStep = 1;
            wizardFormData = {};
            
            // Limpiar formulario
            document.getElementById('formRegistroNuevo').reset();
            
            // Mostrar solo paso 1
            showWizardStep(1);
            updateWizardDisplay();
            actualizarContadorCamposPaso(1);
        }
        
        // Funciones de navegación del wizard
        function nextStepWizard() {
            if (validateWizardStep(wizardCurrentStep)) {
                saveCurrentStepData();
                
                if (wizardCurrentStep === 3) {
                    // En paso 3, crear la cuenta
                    crearCuentaUsuario();
                } else if (wizardCurrentStep === 4) {
                    // En paso 4, enviar postulación
                    // Esta función ya no se usa, el formulario interno lo hace
                    enviarPostulacionCompleta();
                } else if (wizardCurrentStep < wizardTotalSteps) {
                    // Navegar al siguiente paso
                    wizardCurrentStep++;
                    showWizardStep(wizardCurrentStep);
                    updateWizardDisplay();
                    
                    if (wizardCurrentStep === 3) {
                        generateConfirmationSummaryWizard();
                    } else if (wizardCurrentStep === 4) {
                        cargarFormularioPostulacion();
                    }
                }
            }
        }
        
        function previousStepWizard() {
            if (wizardCurrentStep > 1) {
                wizardCurrentStep--;
                showWizardStep(wizardCurrentStep);
                updateWizardDisplay();
            }
        }
        
        function showWizardStep(step) {
            document.querySelectorAll('.wizard-step').forEach(stepEl => {
                stepEl.classList.remove('active');
            });
            
            const currentStepEl = document.querySelector(`.wizard-step[data-step="${step}"]`);
            if (currentStepEl) {
                currentStepEl.classList.add('active');
            }
        }
        
        function updateWizardDisplay() {
            // Actualizar indicadores de paso
            document.querySelectorAll('.registration-wizard .step-indicator').forEach(indicator => {
                const stepNum = parseInt(indicator.getAttribute('data-step'));
                
                indicator.classList.remove('active', 'completed');
                if (stepNum < wizardCurrentStep) {
                    indicator.classList.add('completed');
                } else if (stepNum === wizardCurrentStep) {
                    indicator.classList.add('active');
                }
            });
            
            // Actualizar botones de navegación
            updateNavigationButtons();
        }
        
        function updateNavigationButtons() {
            const prevBtn = document.getElementById('prevStepBtnWizard');
            const nextBtn = document.getElementById('nextStepBtnWizard');
            const btnText = nextBtn.querySelector('.btn-text');
            const btnIcon = nextBtn.querySelector('i');
            
            // Botón anterior
            prevBtn.style.display = (wizardCurrentStep > 1) ? 'inline-block' : 'none';
            
            // Botón siguiente/finalizar
            if (wizardCurrentStep === 3) {
                btnText.textContent = 'Crear Cuenta';
                btnIcon.className = 'bi bi-check-circle-fill ms-1';
                nextBtn.className = 'btn btn-success';
            } else if (wizardCurrentStep === 4) {
                btnText.textContent = 'Enviar Postulación';
                btnIcon.className = 'bi bi-send-check-fill ms-1';
                nextBtn.className = 'btn btn-primary';
            } else {
                btnText.textContent = 'Siguiente';
                btnIcon.className = 'bi bi-chevron-right ms-1';
                nextBtn.className = 'btn btn-primary';
            }
            // Ocultar botones en el paso 4, ya que el formulario interno tiene su propio submit
            if (wizardCurrentStep === 4) {
                prevBtn.style.display = 'none';
                nextBtn.style.display = 'none';
            } else {
                nextBtn.style.display = 'inline-block';
            }
        }
        
        // Funciones de validación por paso
        function validateWizardStep(step) {
            const stepElement = document.querySelector(`.wizard-step[data-step="${step}"]`);
            if (!stepElement) return false;
            
            if (step === 1) return validateStep1();
            if (step === 2) return validateStep2();
            if (step === 3) return validateStep3();
            if (step === 4) return validateStep4();
            
            return true;
        }
        
        function validateStep1() {
            const requiredFields = [
                'nuevo_tipo_documento', 'nuevo_numero_documento', 'nuevo_nombre',
                'nuevo_apellido_paterno', 'nuevo_apellido_materno', 'nuevo_fecha_nacimiento',
                'nuevo_genero', 'nuevo_telefono', 'nuevo_direccion', 'nuevo_email',
                'nuevo_password', 'nuevo_password_confirmation'
            ];
            
            let isValid = true;
            
            for (let fieldId of requiredFields) {
                const field = document.getElementById(fieldId);
                if (!field || !field.value.trim()) {
                    field?.classList.add('is-invalid');
                    isValid = false;
                } else {
                    field.classList.remove('is-invalid');
                }
            }
            
            if (!validarPasswordsRegistro()) {
                isValid = false;
            }
            
            if (!isValid) {
                toastr.warning('Por favor complete todos los campos requeridos');
            }
            
            return isValid;
        }
        
        function validateStep2() {
            // Se hacen opcionales los campos de padres
            const requiredFields = [
                'padre_tipo_doc', 'padre_numero_doc', 'padre_nombre', 'padre_apellidos', 'padre_telefono',
                'madre_tipo_doc', 'madre_numero_doc', 'madre_nombre', 'madre_apellidos', 'madre_telefono'
            ];
            
            let isValid = true;
            
            for (let fieldId of requiredFields) {
                const field = document.getElementById(fieldId);
                if (!field || !field.value.trim()) {
                    field?.classList.add('is-invalid');
                    isValid = false;
                } else {
                    field.classList.remove('is-invalid');
                }
            }
            
            if (!isValid) {
                toastr.warning('Por favor complete todos los campos de los padres');
            }
            
            return isValid;
        }
        
        function validateStep3() {
            const termsCheckbox = document.getElementById('nuevo_terms');
            if (!termsCheckbox.checked) {
                toastr.warning('Debe aceptar los términos y condiciones');
                return false;
            }
            return true;
        }
        
        function validateStep4() {
            // La validación se maneja en el propio formulario de postulación
            return true;
        }
        
        // Función para guardar datos del paso actual
        function saveCurrentStepData() {
            const stepElement = document.querySelector(`.wizard-step[data-step="${wizardCurrentStep}"]`);
            if (!stepElement) return;
            
            const inputs = stepElement.querySelectorAll('input, select, textarea');
            inputs.forEach(input => {
                if (input.type === 'checkbox') {
                    wizardFormData[input.name] = input.checked;
                } else {
                    wizardFormData[input.name] = input.value;
                }
            });
        }
        
        // Funciones de consulta RENIEC
        function consultarReniecRegistro() {
            consultarReniecPersona('nuevo_numero_documento', 'postulante');
        }
        
        function consultarReniecPadre() {
            consultarReniecPersona('padre_numero_doc', 'padre');
        }
        
        function consultarReniecMadre() {
            consultarReniecPersona('madre_numero_doc', 'madre');
        }
        
        function consultarReniecPersona(dniFieldId, tipo) {
            const dni = document.getElementById(dniFieldId).value;
            const btnConsultar = tipo === 'postulante' ? document.getElementById('btnConsultarReniecNuevo') :
                                 tipo === 'padre' ? document.getElementById('btnConsultarReniecPadre') :
                                 document.getElementById('btnConsultarReniecMadre');
            
            if (!dni || dni.length !== 8) {
                toastr.warning('Ingrese un DNI válido de 8 dígitos');
                return;
            }
            
            // Mostrar loading
            btnConsultar.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span>';
            btnConsultar.disabled = true;
            
            // Realizar consulta RENIEC con POST
            fetch('{{ url('/api/reniec/consultar') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ dni: dni })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    autocompletarDatos(data.data || data, tipo);
                    toastr.success('Datos obtenidos correctamente de RENIEC', 'Consulta Exitosa');
                    actualizarContadorCamposPaso(wizardCurrentStep);
                } else {
                    toastr.error(data.message || 'No se pudo consultar los datos');
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error de Conexión',
                    text: 'No se pudo conectar con el servidor.'
                });
            })
            .finally(() => {
                // Restaurar botón
                btnConsultar.innerHTML = '<i class="bi bi-search"></i>';
                btnConsultar.disabled = false;
            });
        }
        
        function autocompletarDatos(data, tipo) {
            if (tipo === 'postulante') {
                if (data.nombres) document.getElementById('nuevo_nombre').value = data.nombres;
                if (data.apellido_paterno) document.getElementById('nuevo_apellido_paterno').value = data.apellido_paterno;
                if (data.apellido_materno) document.getElementById('nuevo_apellido_materno').value = data.apellido_materno;
                if (data.genero) document.getElementById('nuevo_genero').value = data.genero;
                if (data.fecha_nacimiento) document.getElementById('nuevo_fecha_nacimiento').value = data.fecha_nacimiento;
                if (data.direccion) document.getElementById('nuevo_direccion').value = data.direccion;
            } else if (tipo === 'padre') {
                if (data.nombres) document.getElementById('padre_nombre').value = data.nombres;
                const apellidos = `${data.apellido_paterno || ''} ${data.apellido_materno || ''}`.trim();
                if (apellidos) document.getElementById('padre_apellidos').value = apellidos;
            } else if (tipo === 'madre') {
                if (data.nombres) document.getElementById('madre_nombre').value = data.nombres;
                const apellidos = `${data.apellido_paterno || ''} ${data.apellido_materno || ''}`.trim();
                if (apellidos) document.getElementById('madre_apellidos').value = apellidos;
            }
        }
        
        function actualizarContadorCamposPaso(step) {
            const stepElement = document.querySelector(`.wizard-step[data-step="${step}"]`);
            if (!stepElement) return;
            
            const campos = stepElement.querySelectorAll('input[required], select[required]');
            let camposCompletos = 0;
            
            campos.forEach(campo => {
                if (campo.type === 'checkbox' ? campo.checked : (campo.value && campo.value.trim() !== '')) {
                    camposCompletos++;
                }
            });
        }
        
        function validarPasswordsRegistro() {
            const password = document.getElementById('nuevo_password');
            const confirmation = document.getElementById('nuevo_password_confirmation');
            
            if (password.value !== confirmation.value) {
                confirmation.classList.add('is-invalid');
                return false;
            } else {
                confirmation.classList.remove('is-invalid');
                return true;
            }
        }
        
        function togglePasswordVisibility(inputId, button) {
            const input = document.getElementById(inputId);
            const icon = button.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('bi-eye-fill');
                icon.classList.add('bi-eye-slash-fill');
            } else {
                input.type = 'password';
                icon.classList.remove('bi-eye-slash-fill');
                icon.classList.add('bi-eye-fill');
            }
        }
        
        function generateConfirmationSummaryWizard() {
            const container = document.getElementById('confirmationSummaryWizard');
            const data = {
                postulante: {
                    nombre: `${$('#nuevo_nombre').val()} ${$('#nuevo_apellido_paterno').val()} ${$('#nuevo_apellido_materno').val()}`,
                    documento: `${$('#nuevo_tipo_documento').val()} ${$('#nuevo_numero_documento').val()}`,
                    fecha_nac: $('#nuevo_fecha_nacimiento').val(),
                    genero: $('#nuevo_genero option:selected').text(),
                    telefono: $('#nuevo_telefono').val(),
                    email: $('#nuevo_email').val(),
                    direccion: $('#nuevo_direccion').val()
                },
                padre: {
                    nombre: `${$('#padre_nombre').val()} ${$('#padre_apellidos').val()}`,
                    documento: `${$('#padre_tipo_doc').val()} ${$('#padre_numero_doc').val()}`,
                    telefono: $('#padre_telefono').val(),
                },
                madre: {
                    nombre: `${$('#madre_nombre').val()} ${$('#madre_apellidos').val()}`,
                    documento: `${$('#madre_tipo_doc').val()} ${$('#madre_numero_doc').val()}`,
                    telefono: $('#madre_telefono').val(),
                }
            };
            
            container.innerHTML = `
                <div class="confirmation-summary">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="card border-primary h-100">
                                <div class="card-header bg-primary text-white"><h6 class="mb-0"><i class="bi bi-person-fill me-2"></i>Datos del Postulante</h6></div>
                                <div class="card-body">
                                    <p><strong>Nombre:</strong> ${data.postulante.nombre}</p>
                                    <p><strong>Documento:</strong> ${data.postulante.documento}</p>
                                    <p><strong>Email:</strong> ${data.postulante.email}</p>
                                    <p><strong>Teléfono:</strong> ${data.postulante.telefono}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <div class="card border-info h-100">
                                <div class="card-header bg-info text-white"><h6 class="mb-0"><i class="bi bi-people-fill me-2"></i>Datos de los Padres</h6></div>
                                <div class="card-body">
                                    <p><strong>Padre:</strong> ${data.padre.nombre}</p>
                                    <p><strong>Madre:</strong> ${data.madre.nombre}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }
        
        function crearCuentaUsuario() {
            const formData = new FormData(document.getElementById('formRegistroNuevo'));
            
            Swal.fire({
                title: 'Creando Cuenta',
                html: `<div class="text-center"><div class="cepre-spinner my-3"></div><p>Registrando nuevo postulante...</p></div>`,
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false
            });
            
            fetch('{{ route("api.register.postulante") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                Swal.close();
                if (data.success) {
                    nuevoUsuarioData = data.data.postulante || data.user || data.postulante;
                    postulanteData = nuevoUsuarioData;
                    Swal.fire({
                        icon: 'success',
                        title: '¡Cuenta Creada!',
                        text: 'Ahora puede continuar con su postulación.',
                        timer: 2000,
                        timerProgressBar: true,
                        showConfirmButton: false
                    }).then(() => {
                        wizardCurrentStep = 4;
                        showWizardStep(4);
                        updateWizardDisplay();
                        cargarFormularioPostulacion();
                    });
                } else {
                    const errorMsg = data.errors ? Object.values(data.errors).flat().join('<br>') : data.message;
                    Swal.fire({
                        icon: 'error',
                        title: 'Error en el Registro',
                        html: errorMsg
                    });
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error de Conexión',
                    text: 'No se pudo conectar con el servidor.'
                });
            });
        }
        
        function cargarFormularioPostulacion() {
            const container = document.getElementById('formularioPostulacionContainer');
            container.innerHTML = `
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center py-5">
                        <div class="mb-4"><i class="bi bi-check-circle-fill text-success" style="font-size: 64px;"></i></div>
                        <h4 class="text-success mb-3">¡Cuenta Creada Exitosamente!</h4>
                        <p class="text-muted mb-4">Ahora puede proceder con su postulación académica.</p>
                        <button type="button" class="btn btn-success btn-lg" onclick="procederAFormularioPostulacion()">
                            <i class="bi bi-mortarboard-fill me-2"></i> Proceder a Postulación
                        </button>
                    </div>
                </div>
            `;
        }
        
        function procederAFormularioPostulacion() {
            const modalInstance = bootstrap.Modal.getInstance(modalRegistroNuevo);
            if (modalInstance) {
                modalInstance.hide();
            }
            
            modalRegistroNuevo.addEventListener('hidden.bs.modal', () => {
                const dni = nuevoUsuarioData?.numero_documento || document.getElementById('nuevo_numero_documento')?.value;
                if (dni) {
                    $('#dniPostulanteExistente').val(dni);
                    tipoPostulante = 'existente';
                    const modalBuscarBS = new bootstrap.Modal(document.getElementById('modalBuscarPostulante'));
                    modalBuscarBS.show();
                    setTimeout(() => $('#btnBuscarPorDNI').click(), 500);
                }
            }, { once: true });
        }
        
        function refreshPostulacionesList() {
            if (typeof window.postulacionesDataTable !== 'undefined' && window.postulacionesDataTable) {
                window.postulacionesDataTable.ajax.reload(null, false);
                toastr.info('Lista de postulaciones actualizada', 'Actualizado');
            } else {
                window.location.reload();
            }
        }
    </script>
@endpush

@section('content')
    <!-- start page title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">Gestión de Postulaciones</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Postulaciones</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <!-- end page title -->

    <!-- CONTENEDOR PRINCIPAL: Ahora solo aplicamos estética externa y Dark Mode aquí -->
    <div class="card cepre-content-card">
        <div class="card-body p-4">
            
            <!-- OPCIONES DE FILTRADO -->
            <div class="pb-3 border-b border-gray-200">
                <h4 class="header-title mt-0 mb-3" style="font-size: 1.25rem; font-weight: 700;">Opciones de Filtrado</h4>
                
                <div class="row mb-3 g-3">
                    <!-- Filtros: Usamos la estructura original de Bootstrap -->
                    <div class="col-md-3">
                        <label for="filter-ciclo" class="form-label text-sm font-semibold text-gray-700">Ciclo:</label>
                        <select id="filter-ciclo" class="form-select">
                            <option value="">Todos</option>
                            @foreach($ciclos as $ciclo)
                                <option value="{{ $ciclo->id }}" {{ $cicloActivo && $ciclo->id == $cicloActivo->id ? 'selected' : '' }}>{{ $ciclo->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="filter-estado" class="form-label text-sm font-semibold text-gray-700">Estado:</label>
                        <select id="filter-estado" class="form-select">
                            <option value="" selected>Todos</option>
                            <option value="pendiente">Pendiente</option>
                            <option value="aprobado">Aprobado</option>
                            <option value="rechazado">Rechazado</option>
                            <option value="observado">Observado</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="filter-carrera" class="form-label text-sm font-semibold text-gray-700">Carrera:</label>
                        <select id="filter-carrera" class="form-select">
                            <option value="" selected>Todos</option>
                            @foreach($carreras as $carrera)
                                <option value="{{ $carrera->id }}">{{ $carrera->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="button" class="btn btn-primary d-block w-100" id="btn-filtrar">
                            <i class="bi bi-funnel-fill me-1"></i> Filtrar
                        </button>
                    </div>
                </div>
            </div>

            <!-- Estadísticas rápidas -->
            <div class="row pt-4 g-3">
                <!-- Pendientes -->
                <div class="col-md-3">
                    <div class="cepre-stat-card" style="border-left-color: #ffc107;">
                        <div class="d-flex align-items-center p-2">
                            <div class="stat-icon-container me-3" style="background-color: #ffc107;">
                                <i class="bi bi-timer stat-icon" style="color: black !important;"></i>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-gray-500">Pendientes</p>
                                <h3 class="text-2xl font-extrabold text-gray-900" id="stat-pendientes">0</h3>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Aprobadas -->
                <div class="col-md-3">
                    <div class="cepre-stat-card" style="border-left-color: #28a745;">
                        <div class="d-flex align-items-center p-2">
                            <div class="stat-icon-container me-3" style="background-color: #28a745;">
                                <i class="bi bi-check2-circle stat-icon" style="color: white !important;"></i>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-gray-500">Aprobadas</p>
                                <h3 class="text-2xl font-extrabold text-gray-900" id="stat-aprobadas">263</h3>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Rechazadas -->
                <div class="col-md-3">
                    <div class="cepre-stat-card" style="border-left-color: #dc3545;">
                        <div class="d-flex align-items-center p-2">
                            <div class="stat-icon-container me-3" style="background-color: #dc3545;">
                                <i class="bi bi-x-circle stat-icon" style="color: white !important;"></i>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-gray-500">Rechazadas</p>
                                <h3 class="text-2xl font-extrabold text-gray-900" id="stat-rechazadas">0</h3>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Observadas -->
                <div class="col-md-3">
                    <div class="cepre-stat-card" style="border-left-color: #17a2b8;">
                        <div class="d-flex align-items-center p-2">
                            <div class="stat-icon-container me-3" style="background-color: #17a2b8;">
                                <i class="bi bi-eye-fill stat-icon" style="color: white !important;"></i>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-gray-500">Observadas</p>
                                <h3 class="text-2xl font-extrabold text-gray-900" id="stat-observadas">0</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contenedor de la Tabla -->
            <div class="pt-5 border-t border-gray-200 mt-5">
                <!-- Título y Botón -->
                @if (Auth::user()->hasPermission('postulaciones.create-unified'))
                <div class="d-flex justify-content-between align-items-center pb-3">
                    <h4 class="header-title mt-0 mb-0" style="font-size: 1.25rem; font-weight: 700;">LISTA DE POSTULACIONES</h4>
                    <div class="d-flex gap-2">
                        @if (Auth::user()->hasPermission('postulaciones.create'))
                        <button type="button" class="btn btn-info btn-lg text-white" data-bs-toggle="modal" data-bs-target="#modalImportar">
                            <i class="bi bi-file-earmark-spreadsheet-fill me-2"></i> Importar Excel
                        </button>
                        @endif
                        
                        <button type="button" class="btn btn-success btn-lg" id="btn-nueva-postulacion-unificada">
                            <i class="bi bi-person-plus-fill me-2"></i>
                            Nueva Postulación Completa
                        </button>
                    </div>
                </div>
                @else
                <h4 class="header-title mt-0 mb-3" style="font-size: 1.25rem; font-weight: 700;">LISTA DE POSTULACIONES</h4>
                @endif

                <!-- Mensajes de sesión -->
                @if (session('success'))
                    <div class="alert alert-success mt-3 rounded-xl" role="alert">
                        {{ session('success') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger mt-3 rounded-xl" role="alert">
                        {{ session('error') }}
                    </div>
                @endif

                <!-- Tabla de postulaciones (DataTables) -->
                <div class="row">
                    <div class="col-12">
                        <div class="table-responsive">
                            <table id="postulaciones-datatable" class="table dt-responsive nowrap w-100">
                                <thead class="bg-light border-bottom border-gray-200">
                                    <tr>
                                        <th class="p-3 text-sm font-semibold tracking-wider text-left text-gray-600">Código</th>
                                        <th class="p-3 text-sm font-semibold tracking-wider text-left text-gray-600">Estudiante</th>
                                        <th class="p-3 text-sm font-semibold tracking-wider text-left text-gray-600">DNI</th>
                                        <th class="p-3 text-sm font-semibold tracking-wider text-left text-gray-600">Carrera</th>
                                        <th class="p-3 text-sm font-semibold tracking-wider text-left text-gray-600">Turno</th>
                                        <th class="p-3 text-sm font-semibold tracking-wider text-left text-gray-600">Tipo</th>
                                        <th class="p-3 text-sm font-semibold tracking-wider text-left text-gray-600">Fecha</th>
                                        <th class="p-3 text-sm font-semibold tracking-wider text-left text-gray-600">Estado</th>
                                        <th class="p-3 text-sm font-semibold tracking-wider text-left text-gray-600">Verificación</th>
                                        <th class="p-3 text-sm font-semibold tracking-wider text-left text-gray-600">Constancia</th>
                                        <th class="p-3 text-sm font-semibold tracking-wider text-left text-gray-600">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Los datos se cargarán vía AJAX -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
    <!-- Modal para subir constancia firmada (Admin) - Mantenido en Bootstrap -->
    <div class="modal fade" id="uploadConstanciaAdminModal" tabindex="-1" aria-labelledby="uploadConstanciaAdminModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content rounded-xl shadow-lg">
                <div class="modal-header cepre-bg-navy rounded-t-xl">
                    <h5 class="modal-title" id="uploadConstanciaAdminModalLabel">Subir Constancia Firmada</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="uploadConstanciaAdminForm" enctype="multipart/form-data">
                        <input type="hidden" id="postulacion-id-admin-upload" name="postulacion_id">
                        <div class="mb-3">
                            <label for="documento_constancia_admin" class="form-label font-semibold">Seleccionar archivo PDF o imagen</label>
                            <input class="form-control rounded-lg" type="file" id="documento_constancia_admin" name="documento_constancia_admin" accept=".pdf,.jpg,.jpeg,.png" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary rounded-lg" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary rounded-lg" id="confirmUploadConstanciaAdmin">Subir</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('modals')
    <!-- Modal Importación Masiva -->
    <div class="modal fade" id="modalImportar" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form action="{{ route('postulaciones.importar') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-content">
                    <div class="modal-header cepre-bg-navy">
                        <h5 class="modal-title text-white">Importar Postulantes Masivos</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> Descarga la plantilla para asegurar el formato correcto. Las columnas obligatorias son DNI, Nombres y Carrera.
                            <br>
                            <a href="{{ route('postulaciones.plantilla') }}" class="btn btn-sm btn-outline-primary mt-2">
                                <i class="bi bi-download"></i> Descargar Plantilla Excel
                            </a>
                        </div>
                        
                        <div class="mb-3">
                            <label for="archivo_excel" class="form-label">Archivo Excel (.xlsx, .xls)</label>
                            <input type="file" class="form-control" id="archivo_excel" name="archivo_excel" accept=".xlsx, .xls" required>
                        </div>

                        <div class="form-check mb-3 p-3 bg-light rounded border">
                            <input class="form-check-input" type="checkbox" name="simulacro" id="checkSimulacro" value="1">
                            <label class="form-check-label fw-bold text-dark" for="checkSimulacro">
                                <i class="bi bi-shield-check text-primary me-1"></i> Modo Simulacro (Solo Validar)
                            </label>
                            <div class="form-text text-muted small mt-1">
                                Verifica errores y duplicados sin realizar cambios en la base de datos.
                            </div>
                        </div>

                        @if(session('import_errors'))
                            <div class="alert alert-warning mt-3">
                                <strong>Errores previos:</strong>
                                <ul class="mb-0 small" style="max-height: 150px; overflow-y: auto;">
                                    @foreach(session('import_errors') as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-upload me-1"></i> Procesar Importación
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal de Progreso de Importación -->
    <div class="modal fade" id="modalProgreso" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header cepre-bg-navy">
                    <h5 class="modal-title text-white">
                        <i class="bi bi-hourglass-split me-2"></i>
                        <span id="tituloProgreso">Procesando Importación...</span>
                    </h5>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-3">
                        <div class="spinner-border text-primary" role="status" id="spinnerProgreso">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                        <i class="bi bi-check-circle-fill text-success d-none" style="font-size: 3rem;" id="iconoExito"></i>
                        <i class="bi bi-exclamation-triangle-fill text-warning d-none" style="font-size: 3rem;" id="iconoAdvertencia"></i>
                    </div>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="fw-bold">Progreso:</span>
                            <span class="fw-bold text-primary" id="porcentajeTexto">0%</span>
                        </div>
                        <div class="progress" style="height: 25px;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" 
                                 role="progressbar" 
                                 id="barraProgreso" 
                                 style="width: 0%"
                                 aria-valuenow="0" 
                                 aria-valuemin="0" 
                                 aria-valuemax="100">
                                <span class="fw-bold" id="porcentajeInside">0%</span>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info mb-0" id="mensajeProgreso">
                        <i class="bi bi-info-circle me-2"></i>
                        <span id="textoMensaje">Iniciando importación...</span>
                    </div>

                    <div id="resumenFinal" class="d-none mt-3">
                        <div class="card">
                            <div class="card-body">
                                <h6 class="card-title">Resumen de Importación:</h6>
                                <ul class="list-unstyled mb-0">
                                    <li><i class="bi bi-check-circle text-success me-2"></i>Procesados: <strong id="totalProcesados">0</strong></li>
                                    <li><i class="bi bi-plus-circle text-primary me-2"></i>Creados: <strong id="totalCreados">0</strong></li>
                                    <li class="d-none" id="lineaErrores"><i class="bi bi-exclamation-circle text-warning me-2"></i>Errores: <strong id="totalErrores">0</strong></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary d-none" id="btnCerrarProgreso" data-bs-dismiss="modal">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Ver Detalle (Mantenido en Bootstrap) -->
    <div class="modal fade" id="viewModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="viewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content rounded-xl shadow-lg">
                <div class="modal-header cepre-bg-navy rounded-t-xl">
                    <!-- CORRECCIÓN APLICADA AQUÍ: Aseguramos text-white -->
                    <h5 class="modal-title text-white" id="viewModalLabel">Detalle de Postulación</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="viewModalBody">
                    <!-- 
                        PLANTILLA DE CONTENIDO MODERNO PARA ser inyectado por tu JS (Ejemplo):
                    -->
                    <div id="detalle-content">
                        <!-- 1. Sección de Datos de Postulación -->
                        <div class="detail-section">
                            <h4 class="detail-section-title">
                                <i class="bi bi-calendar-check-fill me-2 text-primary"></i> Información General
                            </h4>
                            <div class="detail-grid">
                                <div class="detail-item">
                                    <strong>Código de Postulación:</strong>
                                    <span id="detalle-codigo">200003</span>
                                </div>
                                <div class="detail-item">
                                    <strong>Ciclo:</strong>
                                    <span id="detalle-ciclo">Ciclo Ordinario 2025-2</span>
                                </div>
                                <div class="detail-item">
                                    <strong>Fecha de Registro:</strong>
                                    <span id="detalle-fecha">09/09/2025</span>
                                </div>
                                <div class="detail-item">
                                    <strong>Estado Actual:</strong>
                                    <span id="detalle-estado" class="badge bg-success">APROBADO</span>
                                </div>
                            </div>
                        </div>

                        <!-- 2. Sección de Datos del Estudiante -->
                        <div class="detail-section">
                            <h4 class="detail-section-title">
                                <i class="bi bi-person-circle me-2 text-info"></i> Datos del Estudiante
                            </h4>
                            <div class="detail-grid">
                                <div class="detail-item">
                                    <strong>Nombre Completo:</strong>
                                    <span id="detalle-nombre">TREYCI ROSARIO MAYTA DURAND</span>
                                </div>
                                <div class="detail-item">
                                    <strong>DNI/Documento:</strong>
                                    <span id="detalle-dni">62531429 (DNI)</span>
                                </div>
                                <div class="detail-item">
                                    <strong>Correo Electrónico:</strong>
                                    <span id="detalle-email">treyci.mayta@example.com</span>
                                </div>
                                <div class="detail-item">
                                    <strong>Teléfono:</strong>
                                    <span id="detalle-telefono">987654321</span>
                                </div>
                                <div class="detail-item col-span-full">
                                    <strong>Dirección:</strong>
                                    <span id="detalle-direccion">Av. Los Girasoles N° 123, Madre de Dios</span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- 3. Sección de Datos Académicos -->
                        <div class="detail-section">
                            <h4 class="detail-section-title">
                                <i class="bi bi-mortarboard-fill me-2 text-warning"></i> Opciones Académicas
                            </h4>
                            <div class="detail-grid">
                                <div class="detail-item">
                                    <strong>Carrera Postulada:</strong>
                                    <span id="detalle-carrera">ENFERMERÍA</span>
                                </div>
                                <div class="detail-item">
                                    <strong>Turno Seleccionado:</strong>
                                    <span id="detalle-turno">Mañana</span>
                                </div>
                                <div class="detail-item">
                                    <strong>Tipo de Inscripción:</strong>
                                    <span id="detalle-tipo">Postulante Regular</span>
                                </div>
                                <div class="detail-item col-span-full">
                                    <strong>Colegio de Procedencia:</strong>
                                    <span id="detalle-colegio">IEP Señor de los Milagros - Puerto Maldonado</span>
                                </div>
                            </div>
                        </div>

                        <!-- 4. Sección de Documentos y Archivos -->
                        <div class="detail-section" style="border-bottom: none;">
                            <h4 class="detail-section-title">
                                <i class="bi bi-file-earmark-check-fill me-2" style="color: var(--cepre-green);"></i> Archivos y Documentos
                            </h4>
                            <p class="text-sm text-muted mb-3">Haga click para previsualizar/descargar documentos.</p>
                            <div class="row g-2">
                                <!-- Elemento de Documento (Ejemplo) -->
                                <div class="col-md-4">
                                    <a href="#" class="btn btn-outline-secondary btn-sm w-100 d-flex align-items-center justify-content-between rounded-lg">
                                        Voucher de Pago <i class="bi bi-download ms-2"></i>
                                    </a>
                                </div>
                                <!-- Elemento de Documento (Ejemplo) -->
                                <div class="col-md-4">
                                    <a href="#" class="btn btn-outline-secondary btn-sm w-100 d-flex align-items-center justify-content-between rounded-lg">
                                        Certificado de Estudios <i class="bi bi-download ms-2"></i>
                                    </a>
                                </div>
                                <!-- Elemento de Documento (Ejemplo) -->
                                <div class="col-md-4">
                                    <a href="#" class="btn btn-outline-secondary btn-sm w-100 d-flex align-items-center justify-content-between rounded-lg">
                                        DNI <i class="bi bi-download ms-2"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary rounded-lg" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Rechazar (Mantenido en Bootstrap) -->
    <div class="modal fade" id="rejectModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="rejectModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content rounded-xl shadow-lg">
                <div class="modal-header bg-danger text-white rounded-t-xl" style="background-color: #dc3545;">
                    <h5 class="modal-title" id="rejectModalLabel">Rechazar Postulación</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="rejectForm">
                        <input type="hidden" id="reject-id" name="id">
                        <div class="mb-3">
                            <label for="reject-motivo" class="form-label font-semibold">Motivo del Rechazo <span class="text-danger">*</span></label>
                            <textarea class="form-control rounded-lg" id="reject-motivo" name="motivo" rows="4" required 
                                placeholder="Ingrese el motivo del rechazo (mínimo 10 caracteres)"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary rounded-lg" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger rounded-lg" id="confirmReject">
                        <i class="bi bi-x-circle-fill me-1"></i> Rechazar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Observar (Mantenido en Bootstrap) -->
    <div class="modal fade" id="observeModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="observeModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content rounded-xl shadow-lg">
                <div class="modal-header bg-warning rounded-t-xl" style="background-color: #ffc107;">
                    <h5 class="modal-title text-gray-900" id="observeModalLabel">Observar Postulación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="observeForm">
                        <input type="hidden" id="observe-id" name="id">
                        <div class="mb-3">
                            <label for="observe-observaciones" class="form-label font-semibold">Observaciones <span class="text-danger">*</span></label>
                            <textarea class="form-control rounded-lg" id="observe-observaciones" name="observaciones" rows="4" required 
                                placeholder="Ingrese las observaciones (mínimo 10 caracteres)"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary rounded-lg" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-warning rounded-lg" id="confirmObserve">
                        <i class="bi bi-eye-fill me-1"></i> Marcar con Observaciones
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Confirmación para Eliminar (Mantenido en Bootstrap) -->
    <div class="modal fade" id="deleteModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content rounded-xl shadow-lg">
                <div class="modal-header bg-danger text-white rounded-t-xl" style="background-color: #dc3545;">
                    <h5 class="modal-title" id="deleteModalLabel">Confirmar Eliminación</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="text-lg">¿Está seguro de que desea eliminar esta postulación?</p>
                    <p class="text-danger font-semibold">Esta acción no se puede deshacer y eliminará todos los documentos asociados.</p>
                    <input type="hidden" id="delete-id">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary rounded-lg" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger rounded-lg" id="confirmDelete">
                        <i class="bi bi-trash-fill me-1"></i> Eliminar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Editar Postulación Aprobada (Mantenido en Bootstrap) -->
    <div class="modal fade" id="editApprovedModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="editApprovedModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content rounded-xl shadow-lg">
                <div class="modal-header cepre-bg-cyan rounded-t-xl">
                    <h5 class="modal-title" id="editApprovedModalLabel">Editar Postulación Aprobada</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editApprovedForm">
                        <input type="hidden" id="edit-approved-id" name="id">
                        
                        <div class="alert alert-warning border-l-4 border-warning rounded-lg mb-4" role="alert">
                            <i class="bi bi-exclamation-triangle-fill text-warning me-2"></i> 
                            <strong class="text-warning-dark">Atención:</strong> Esta postulación ya ha sido aprobada. Los cambios que realice también actualizarán la inscripción asociada.
                        </div>

                        <div class="row g-4">
                            <div class="col-md-6">
                                <h6 class="text-lg font-semibold text-gray-600 mb-3 border-bottom pb-2">Datos del Estudiante</h6>
                                <div class="mb-3">
                                    <label for="edit-approved-dni" class="form-label font-semibold">DNI</label>
                                    <input type="text" class="form-control rounded-lg bg-light" id="edit-approved-dni" name="dni" maxlength="8" readonly>
                                    <small class="text-muted">El DNI no puede ser modificado</small>
                                </div>
                                <div class="mb-3">
                                    <label for="edit-approved-nombre" class="form-label font-semibold">Nombres</label>
                                    <input type="text" class="form-control rounded-lg" id="edit-approved-nombre" name="nombre" required>
                                </div>
                                <div class="mb-3">
                                    <label for="edit-approved-apellido-paterno" class="form-label font-semibold">Apellido Paterno</label>
                                    <input type="text" class="form-control rounded-lg" id="edit-approved-apellido-paterno" name="apellido_paterno" required>
                                </div>
                                <div class="mb-3">
                                    <label for="edit-approved-apellido-materno" class="form-label font-semibold">Apellido Materno</label>
                                    <input type="text" class="form-control rounded-lg" id="edit-approved-apellido-materno" name="apellido_materno" required>
                                </div>
                                <div class="mb-3">
                                    <label for="edit-approved-telefono" class="form-label font-semibold">Teléfono</label>
                                    <input type="text" class="form-control rounded-lg" id="edit-approved-telefono" name="telefono">
                                </div>
                                <div class="mb-3">
                                    <label for="edit-approved-email" class="form-label font-semibold">Email</label>
                                    <input type="email" class="form-control rounded-lg" id="edit-approved-email" name="email" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <h6 class="text-lg font-semibold text-gray-600 mb-3 border-bottom pb-2">Datos Académicos</h6>
                                <div class="mb-3">
                                    <label for="edit-approved-ciclo" class="form-label font-semibold">Ciclo</label>
                                    <select class="form-select rounded-lg" id="edit-approved-ciclo" name="ciclo_id" required>
                                        <!-- Opciones cargadas vía AJAX -->
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="edit-approved-carrera" class="form-label font-semibold">Carrera</label>
                                    <select class="form-select rounded-lg" id="edit-approved-carrera" name="carrera_id" required>
                                        <!-- Opciones cargadas vía AJAX -->
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="edit-approved-turno" class="form-label font-semibold">Turno</label>
                                    <select class="form-select rounded-lg" id="edit-approved-turno" name="turno_id" required>
                                        <!-- Los turnos se cargarán dinámicamente -->
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="edit-approved-aula" class="form-label font-semibold">Aula Asignada</label>
                                    <select class="form-select rounded-lg" id="edit-approved-aula" name="aula_id">
                                        <!-- Las aulas se cargarán dinámicamente -->
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="edit-approved-codigo" class="form-label font-semibold">Código de Postulante</label>
                                    <input type="text" class="form-control rounded-lg" id="edit-approved-codigo" name="codigo_postulante">
                                </div>
                                <div class="mb-3">
                                    <label for="edit-approved-tipo" class="form-label font-semibold">Tipo de Inscripción</label>
                                    <select class="form-select rounded-lg" id="edit-approved-tipo" name="tipo_inscripcion" required>
                                        <option value="postulante">Postulante</option>
                                        <option value="reforzamiento">Reforzamiento</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <h6 class="text-lg font-semibold text-gray-600 mt-6 mb-3 border-bottom pb-2">Datos de Padres (Opcional)</h6>
                        <div class="row g-4">
                            <div class="col-md-6">
                                <h6 class="text-md font-medium text-primary mb-2">Padre</h6>
                                <div class="mb-3"><label for="edit-approved-padre-dni" class="form-label text-sm">DNI del Padre</label><input type="text" class="form-control rounded-lg" id="edit-approved-padre-dni" name="padre_dni" maxlength="8"></div>
                                <div class="mb-3"><label for="edit-approved-padre-nombre" class="form-label text-sm">Nombres del Padre</label><input type="text" class="form-control rounded-lg" id="edit-approved-padre-nombre" name="padre_nombre"></div>
                                <div class="mb-3"><label for="edit-approved-padre-apellido-paterno" class="form-label text-sm">Apellido Paterno del Padre</label><input type="text" class="form-control rounded-lg" id="edit-approved-padre-apellido-paterno" name="padre_apellido_paterno"></div>
                                <div class="mb-3"><label for="edit-approved-padre-apellido-materno" class="form-label text-sm">Apellido Materno del Padre</label><input type="text" class="form-control rounded-lg" id="edit-approved-padre-apellido-materno" name="padre_apellido_materno"></div>
                                <div class="mb-3"><label for="edit-approved-padre-telefono" class="form-label text-sm">Teléfono del Padre</label><input type="text" class="form-control rounded-lg" id="edit-approved-padre-telefono" name="padre_telefono"></div>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-md font-medium text-primary mb-2">Madre</h6>
                                <div class="mb-3"><label for="edit-approved-madre-dni" class="form-label text-sm">DNI de la Madre</label><input type="text" class="form-control rounded-lg" id="edit-approved-madre-dni" name="madre_dni" maxlength="8"></div>
                                <div class="mb-3"><label for="edit-approved-madre-nombre" class="form-label text-sm">Nombres de la Madre</label><input type="text" class="form-control rounded-lg" id="edit-approved-madre-nombre" name="madre_nombre"></div>
                                <div class="mb-3"><label for="edit-approved-madre-apellido-paterno" class="form-label text-sm">Apellido Paterno de la Madre</label><input type="text" class="form-control rounded-lg" id="edit-approved-madre-apellido-paterno" name="madre_apellido_paterno"></div>
                                <div class="mb-3"><label for="edit-approved-madre-apellido-materno" class="form-label text-sm">Apellido Materno de la Madre</label><input type="text" class="form-control rounded-lg" id="edit-approved-madre-apellido-materno" name="madre_apellido_materno"></div>
                                <div class="mb-3"><label for="edit-approved-madre-telefono" class="form-label text-sm">Teléfono de la Madre</label><input type="text" class="form-control rounded-lg" id="edit-approved-madre-telefono" name="madre_telefono"></div>
                            </div>
                        </div>

                        <h6 class="text-lg font-semibold text-gray-600 mt-6 mb-3 border-bottom pb-2">Información de Pago</h6>
                        <div class="row g-4">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="edit-approved-recibo" class="form-label font-semibold">N° Recibo</label>
                                    <input type="text" class="form-control rounded-lg" id="edit-approved-recibo" name="numero_recibo">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="edit-approved-matricula" class="form-label font-semibold">Monto Matrícula (S/.)</label>
                                    <input type="number" step="0.01" class="form-control rounded-lg" id="edit-approved-matricula" name="monto_matricula">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="edit-approved-ensenanza" class="form-label font-semibold">Monto Enseñanza (S/.)</label>
                                    <input type="number" step="0.01" class="form-control rounded-lg" id="edit-approved-ensenanza" name="monto_ensenanza">
                                </div>
                            </div>
                        </div>

                        <div class="mb-3 mt-4">
                            <label for="edit-approved-observacion" class="form-label font-semibold">Observación del cambio <span class="text-danger">*</span></label>
                            <textarea class="form-control rounded-lg" id="edit-approved-observacion" name="observacion_cambio" rows="3" required
                                placeholder="Explique brevemente el motivo de la modificación"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary rounded-lg" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary rounded-lg" id="saveApprovedChanges">
                        <i class="bi bi-save-fill me-1"></i> Guardar Cambios
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Editar Documentos (Mantenido en Bootstrap) -->
    <div class="modal fade" id="editDocumentsModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="editDocumentsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content rounded-xl shadow-lg">
                <div class="modal-header cepre-bg-navy rounded-t-xl">
                    <h5 class="modal-title" id="editDocumentsModalLabel">Editar Documentos del Postulante</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editDocumentsForm" enctype="multipart/form-data">
                        <input type="hidden" id="edit-docs-postulacion-id">
                        
                        <div class="alert alert-info border-l-4 border-info rounded-lg mb-4" role="alert">
                            <i class="bi bi-info-circle-fill text-info me-2"></i> 
                            <strong class="text-info-dark">Puede reemplazar</strong> los documentos subidos por el postulante. Solo suba los documentos que desea cambiar.
                        </div>

                        <div class="row g-4" id="documents-container">
                            <!-- Foto del Postulante -->
                            <div class="col-md-6">
                                <div class="document-upload-card">
                                    <label class="form-label fw-bold">
                                        <i class="bi bi-person-badge text-primary"></i> Foto del Postulante
                                    </label>
                                    <div class="drop-zone" data-doc-type="foto" data-accept="image/*" data-max-size="5">
                                        <input type="file" class="file-input" accept="image/*" id="input-foto">
                                        <div class="drop-zone-content">
                                            <i class="bi bi-cloud-upload-fill text-muted" style="font-size: 48px;"></i>
                                            <p class="mb-1 fw-semibold">Arrastra la foto aquí</p>
                                            <p class="text-muted small">o haz clic para seleccionar</p>
                                            <p class="text-muted small">Máx. 5MB - JPG, PNG</p>
                                        </div>
                                        <div class="preview-container d-none">
                                            <img class="preview-image" alt="Preview">
                                            <div class="preview-overlay">
                                                <button type="button" class="btn btn-sm btn-danger btn-remove">
                                                    <i class="bi bi-trash"></i> Eliminar
                                                </button>
                                            </div>
                                            <div class="file-info">
                                                <span class="file-name"></span>
                                                <span class="file-size"></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- DNI -->
                            <div class="col-md-6">
                                <div class="document-upload-card">
                                    <label class="form-label fw-bold">
                                        <i class="bi bi-card-text text-success"></i> DNI
                                    </label>
                                    <div class="drop-zone" data-doc-type="dni" data-accept="image/*,application/pdf" data-max-size="5">
                                        <input type="file" class="file-input" accept="image/*,application/pdf" id="input-dni">
                                        <div class="drop-zone-content">
                                            <i class="bi bi-cloud-upload-fill text-muted" style="font-size: 48px;"></i>
                                            <p class="mb-1 fw-semibold">Arrastra el DNI aquí</p>
                                            <p class="text-muted small">o haz clic para seleccionar</p>
                                            <p class="text-muted small">Máx. 5MB - JPG, PNG, PDF</p>
                                        </div>
                                        <div class="preview-container d-none">
                                            <img class="preview-image" alt="Preview">
                                            <div class="preview-overlay">
                                                <button type="button" class="btn btn-sm btn-danger btn-remove">
                                                    <i class="bi bi-trash"></i> Eliminar
                                                </button>
                                            </div>
                                            <div class="file-info">
                                                <span class="file-name"></span>
                                                <span class="file-size"></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Voucher de Pago -->
                            <div class="col-md-6">
                                <div class="document-upload-card">
                                    <label class="form-label fw-bold">
                                        <i class="bi bi-receipt text-warning"></i> Voucher de Pago
                                    </label>
                                    <div class="drop-zone" data-doc-type="voucher" data-accept="image/*,application/pdf" data-max-size="5">
                                        <input type="file" class="file-input" accept="image/*,application/pdf" id="input-voucher">
                                        <div class="drop-zone-content">
                                            <i class="bi bi-cloud-upload-fill text-muted" style="font-size: 48px;"></i>
                                            <p class="mb-1 fw-semibold">Arrastra el voucher aquí</p>
                                            <p class="text-muted small">o haz clic para seleccionar</p>
                                            <p class="text-muted small">Máx. 5MB - JPG, PNG, PDF</p>
                                        </div>
                                        <div class="preview-container d-none">
                                            <img class="preview-image" alt="Preview">
                                            <div class="preview-overlay">
                                                <button type="button" class="btn btn-sm btn-danger btn-remove">
                                                    <i class="bi bi-trash"></i> Eliminar
                                                </button>
                                            </div>
                                            <div class="file-info">
                                                <span class="file-name"></span>
                                                <span class="file-size"></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Certificado de Estudios -->
                            <div class="col-md-6">
                                <div class="document-upload-card">
                                    <label class="form-label fw-bold">
                                        <i class="bi bi-file-earmark-text text-info"></i> Certificado de Estudios
                                    </label>
                                    <div class="drop-zone" data-doc-type="certificado_estudios" data-accept="application/pdf" data-max-size="10">
                                        <input type="file" class="file-input" accept="application/pdf" id="input-certificado_estudios">
                                        <div class="drop-zone-content">
                                            <i class="bi bi-cloud-upload-fill text-muted" style="font-size: 48px;"></i>
                                            <p class="mb-1 fw-semibold">Arrastra el certificado aquí</p>
                                            <p class="text-muted small">o haz clic para seleccionar</p>
                                            <p class="text-muted small">Máx. 10MB - PDF</p>
                                        </div>
                                        <div class="preview-container d-none">
                                            <div class="pdf-preview">
                                                <i class="bi bi-file-pdf-fill text-danger" style="font-size: 64px;"></i>
                                            </div>
                                            <div class="preview-overlay">
                                                <button type="button" class="btn btn-sm btn-danger btn-remove">
                                                    <i class="bi bi-trash"></i> Eliminar
                                                </button>
                                            </div>
                                            <div class="file-info">
                                                <span class="file-name"></span>
                                                <span class="file-size"></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Constancia de Estudios -->
                            <div class="col-md-6">
                                <div class="document-upload-card">
                                    <label class="form-label fw-bold">
                                        <i class="bi bi-file-earmark-check text-secondary"></i> Constancia de Estudios
                                    </label>
                                    <div class="drop-zone" data-doc-type="constancia_estudios" data-accept="application/pdf" data-max-size="10">
                                        <input type="file" class="file-input" accept="application/pdf" id="input-constancia_estudios">
                                        <div class="drop-zone-content">
                                            <i class="bi bi-cloud-upload-fill text-muted" style="font-size: 48px;"></i>
                                            <p class="mb-1 fw-semibold">Arrastra la constancia aquí</p>
                                            <p class="text-muted small">o haz clic para seleccionar</p>
                                            <p class="text-muted small">Máx. 10MB - PDF</p>
                                        </div>
                                        <div class="preview-container d-none">
                                            <div class="pdf-preview">
                                                <i class="bi bi-file-pdf-fill text-danger" style="font-size: 64px;"></i>
                                            </div>
                                            <div class="preview-overlay">
                                                <button type="button" class="btn btn-sm btn-danger btn-remove">
                                                    <i class="bi bi-trash"></i> Eliminar
                                                </button>
                                            </div>
                                            <div class="file-info">
                                                <span class="file-name"></span>
                                                <span class="file-size"></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Carta Compromiso -->
                            <div class="col-md-6">
                                <div class="document-upload-card">
                                    <label class="form-label fw-bold">
                                        <i class="bi bi-file-earmark-ruled text-danger"></i> Carta Compromiso
                                    </label>
                                    <div class="drop-zone" data-doc-type="carta_compromiso" data-accept="application/pdf" data-max-size="10">
                                        <input type="file" class="file-input" accept="application/pdf" id="input-carta_compromiso">
                                        <div class="drop-zone-content">
                                            <i class="bi bi-cloud-upload-fill text-muted" style="font-size: 48px;"></i>
                                            <p class="mb-1 fw-semibold">Arrastra la carta aquí</p>
                                            <p class="text-muted small">o haz clic para seleccionar</p>
                                            <p class="text-muted small">Máx. 10MB - PDF</p>
                                        </div>
                                        <div class="preview-container d-none">
                                            <div class="pdf-preview">
                                                <i class="bi bi-file-pdf-fill text-danger" style="font-size: 64px;"></i>
                                            </div>
                                            <div class="preview-overlay">
                                                <button type="button" class="btn btn-sm btn-danger btn-remove">
                                                    <i class="bi bi-trash"></i> Eliminar
                                                </button>
                                            </div>
                                            <div class="file-info">
                                                <span class="file-name"></span>
                                                <span class="file-size"></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <div class="mb-3">
                                <label for="edit-docs-observacion" class="form-label font-semibold">Observación del cambio:</label>
                                <textarea class="form-control rounded-lg" id="edit-docs-observacion" rows="3" 
                                    placeholder="Explique brevemente por qué se están modificando los documentos"></textarea>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary rounded-lg" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary rounded-lg" id="saveDocumentChanges">
                        <i class="bi bi-save-fill me-1"></i> Guardar Cambios
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Selección Tipo de Usuario (Mantenido en Bootstrap) -->
    <div class="modal fade" id="modalSeleccionTipo" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="modalSeleccionTipoLabel" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content rounded-xl shadow-2xl">
                <div class="modal-header cepre-bg-navy rounded-t-xl">
                    <h5 class="modal-title text-white" id="modalSeleccionTipoLabel">
                        <i class="bi bi-person-check-fill me-2"></i>
                        ¿Cómo desea realizar la postulación?
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="card h-100 border border-success hover-card cursor-pointer rounded-xl" id="btnPostulanteNuevo">
                                <div class="card-body text-center p-4">
                                    <div class="mb-3">
                                        <i class="bi bi-person-plus-fill text-success" style="font-size: 48px;"></i>
                                    </div>
                                    <h5 class="card-title font-bold text-lg text-gray-800">Soy Postulante Nuevo</h5>
                                    <p class="card-text text-muted text-sm mt-2">
                                        Primera vez postulando. Necesito crear una cuenta nueva.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card h-100 border border-info hover-card cursor-pointer rounded-xl" id="btnPostulanteExistente">
                                <div class="card-body text-center p-4">
                                    <div class="mb-3">
                                        <i class="bi bi-person-bounding-box text-info" style="font-size: 48px;"></i>
                                    </div>
                                    <h5 class="card-title font-bold text-lg text-gray-800">Ya Tengo Cuenta</h5>
                                    <p class="card-text text-muted text-sm mt-2">
                                        Soy postulante recurrente o ya tengo cuenta creada.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Búsqueda de Postulante Existente (Mantenido en Bootstrap) -->
    <div class="modal fade" id="modalBuscarPostulante" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="modalBuscarPostulanteLabel" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content rounded-xl shadow-2xl">
                <div class="modal-header cepre-bg-cyan rounded-t-xl">
                    <h5 class="modal-title" id="modalBuscarPostulanteLabel">
                        <i class="bi bi-search me-2"></i>
                        Buscar Postulante Existente
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-info border-l-4 border-info rounded-lg mb-4" role="alert">
                        <i class="bi bi-info-circle-fill text-info me-2"></i>
                        Ingrese el DNI del postulante para continuar con la postulación.
                    </div>
                    <form id="formBuscarPostulante">
                        <div class="mb-3">
                            <label for="dniPostulanteExistente" class="form-label font-semibold">DNI del Postulante <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="text" class="form-control rounded-start p-3" id="dniPostulanteExistente" 
                                        maxlength="8" pattern="[0-9]{8}" required 
                                        placeholder="Ingrese el DNI">
                                <button class="btn btn-primary p-3 rounded-end" type="button" id="btnBuscarPorDNI">
                                    <i class="bi bi-search"></i> Buscar
                                </button>
                            </div>
                            <div class="invalid-feedback">Ingrese un DNI válido de 8 dígitos</div>
                        </div>
                        <div id="resultadoBusqueda" class="mt-4" style="display: none;">
                            <!-- Los resultados de búsqueda se mostrarán aquí -->
                        </div>
                    </form>
                </div>
                <div class="modal-footer bg-light rounded-b-xl">
                    <button type="button" class="btn btn-secondary rounded-lg" id="btnVolverSeleccion">
                        <i class="bi bi-arrow-left me-1"></i> Volver
                    </button>
                    <button type="button" class="btn btn-primary rounded-lg" id="btnContinuarPostulacion" style="display: none;">
                        <i class="bi bi-arrow-right me-1"></i> Continuar con Postulación
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Nueva Postulación Unificada (Mantenido en Bootstrap) -->
    <div class="modal fade" id="nuevaPostulacionModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="nuevaPostulacionModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content rounded-xl shadow-2xl">
                <div class="modal-header cepre-bg-navy rounded-t-xl">
                    <h5 class="modal-title" id="nuevaPostulacionModalLabel">
                        <i class="bi bi-person-plus-fill me-2"></i>
                        <span id="tituloModalPostulacion">Nueva Postulación Completa</span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0" style="max-height: 75vh; overflow-y: auto;">
                    <div id="postulacion-form-container">
                        <!-- El formulario se cargará aquí dinámicamente -->
                        <div class="text-center py-8" id="loadingContainer">
                            <div class="cepre-spinner"></div>
                            <p class="mt-3 text-gray-600">Cargando formulario de postulación...</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light rounded-b-xl">
                    <button type="button" class="btn btn-secondary rounded-lg" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg me-1"></i> Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ===== MODAL MEJORADO: Registro Completo - Nuevo Postulante ===== (Estructura Horizontal) -->
    <div class="modal fade" id="modalRegistroNuevo" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" role="dialog" aria-labelledby="modalRegistroNuevoLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content rounded-2xl shadow-2xl">
                <div class="modal-header text-white rounded-t-2xl cepre-bg-gradient">
                    <h5 class="modal-title text-white font-bold text-xl" id="modalRegistroNuevoLabel">
                        <i class="bi bi-person-plus-fill me-2"></i>Registro Completo - Nuevo Postulante
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="registration-wizard">
                        
                        <!-- 1. Barra de progreso HORIZONTAL -->
                        <div class="wizard-progress-container">
                            <div class="step-indicator active" data-step="1">
                                <div class="step-circle"><i class="bi bi-person-vcard"></i></div>
                                <div class="progress-line"></div>
                                <div class="step-label">Postulante</div>
                            </div>
                            <div class="step-indicator" data-step="2">
                                <div class="step-circle"><i class="bi bi-people"></i></div>
                                <div class="progress-line"></div>
                                <div class="step-label">Padres</div>
                            </div>
                            <div class="step-indicator" data-step="3">
                                <div class="step-circle"><i class="bi bi-shield-check"></i></div>
                                <div class="progress-line"></div>
                                <div class="step-label">Confirmación</div>
                            </div>
                            <div class="step-indicator" data-step="4">
                                <div class="step-circle"><i class="bi bi-mortarboard"></i></div>
                                <div class="step-label">Postulación</div>
                            </div>
                        </div>

                        <!-- 2. Contenido del formulario (Scrollable) -->
                        <form id="formRegistroNuevo" class="needs-validation" novalidate style="flex-grow: 1; overflow-y: auto; padding: 2rem;">
                            @csrf
                            
                            <!-- PASO 1: Datos Personales del Postulante -->
                            <div class="wizard-step active" data-step="1">
                                <div class="step-content-card">
                                    <h4 class="mb-4 text-2xl font-bold text-center" style="color: var(--cepre-navy);">Datos Personales del Postulante</h4>
                                    <!-- APLICAMOS G-4 para mejor espaciado -->
                                    <div class="row g-4">
                                        <!-- GRUPO 1: Documentos -->
                                        <div class="col-md-6">
                                            <label for="nuevo_tipo_documento" class="form-label">Tipo Documento <span class="text-danger">*</span></label>
                                            <select class="form-select" id="nuevo_tipo_documento" name="tipo_documento" required>
                                                <option value="DNI" selected>DNI</option>
                                                <option value="CE">Carnet de Extranjería</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="nuevo_numero_documento" class="form-label">Número Documento <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <input type="text" class="form-control" id="nuevo_numero_documento" name="numero_documento" maxlength="8" pattern="[0-9]{8}" required>
                                                <button class="btn btn-outline-primary" type="button" id="btnConsultarReniecNuevo"><i class="bi bi-search"></i></button>
                                            </div>
                                            <small class="form-text text-muted">Consulte RENIEC para autocompletar</small>
                                        </div>
                                    
                                        <!-- GRUPO 2: Nombres y Apellidos (3 Columnas) -->
                                        <div class="col-md-4">
                                            <label for="nuevo_nombre" class="form-label">Nombres <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="nuevo_nombre" name="nombre" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="nuevo_apellido_paterno" class="form-label">Apellido Paterno <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="nuevo_apellido_paterno" name="apellido_paterno" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="nuevo_apellido_materno" class="form-label">Apellido Materno <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="nuevo_apellido_materno" name="apellido_materno" required>
                                        </div>
                                    
                                        <!-- GRUPO 3: Nacimiento, Género, Teléfono (3 Columnas) -->
                                        <div class="col-md-4">
                                            <label for="nuevo_fecha_nacimiento" class="form-label">Fecha Nacimiento <span class="text-danger">*</span></label>
                                            <input type="date" class="form-control" id="nuevo_fecha_nacimiento" name="fecha_nacimiento" max="{{ date('Y-m-d', strtotime('-14 years')) }}" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="nuevo_genero" class="form-label">Género <span class="text-danger">*</span></label>
                                            <select class="form-select" id="nuevo_genero" name="genero" required>
                                                <option value="">Seleccione...</option>
                                                <option value="M">Masculino</option>
                                                <option value="F">Femenino</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="nuevo_telefono" class="form-label">Teléfono/Celular <span class="text-danger">*</span></label>
                                            <input type="tel" class="form-control" id="nuevo_telefono" name="telefono" pattern="[0-9]{9}" maxlength="9" required>
                                        </div>
                                    
                                        <!-- GRUPO 4: Dirección (Full width) -->
                                        <div class="col-12">
                                            <label for="nuevo_direccion" class="form-label">Dirección <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control" id="nuevo_direccion" name="direccion" required>
                                        </div>
                                        
                                        <!-- GRUPO 5: Credenciales (3 Columnas) -->
                                        <div class="col-md-4">
                                            <label for="nuevo_email" class="form-label">Correo Electrónico <span class="text-danger">*</span></label>
                                            <input type="email" class="form-control" id="nuevo_email" name="email" required>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="nuevo_password" class="form-label">Contraseña <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <input type="password" class="form-control" id="nuevo_password" name="password" minlength="8" required>
                                                <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('nuevo_password', this)"><i class="bi bi-eye-fill"></i></button>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="nuevo_password_confirmation" class="form-label">Confirmar Contraseña <span class="text-danger">*</span></label>
                                            <input type="password" class="form-control" id="nuevo_password_confirmation" name="password_confirmation" minlength="8" required>
                                            <div class="invalid-feedback">Las contraseñas no coinciden</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- PASO 2: Datos de Padres/Tutores -->
                            <div class="wizard-step" data-step="2">
                                <div class="step-content-card">
                                    <h4 class="mb-4 text-center" style="color: var(--cepre-navy);">Datos de Padres y/o Tutores</h4>
                                    <!-- APLICAMOS G-4 para mejor espaciado y estructura de dos columnas principales -->
                                    <div class="row g-4">
                                        
                                        <div class="col-lg-6 border-end">
                                            <h5 class="text-primary mb-4">Datos del Padre</h5>
                                            <div class="row g-3">
                                                <!-- Fila Documento Padre -->
                                                <div class="col-md-6"><label for="padre_tipo_doc" class="form-label">Tipo Documento <span class="text-danger">*</span></label><select class="form-select" id="padre_tipo_doc" name="padre_tipo_documento" required><option value="DNI" selected>DNI</option><option value="CE">CE</option></select><div class="invalid-feedback">Seleccione un tipo</div></div>
                                                <div class="col-md-6"><label for="padre_numero_doc" class="form-label">Número Documento <span class="text-danger">*</span></label><div class="input-group"><input type="text" class="form-control" id="padre_numero_doc" name="padre_numero_documento" maxlength="8" pattern="[0-9]{8}" required><button class="btn btn-outline-primary" type="button" id="btnConsultarReniecPadre"><i class="bi bi-search"></i></button></div><div class="invalid-feedback">Ingrese un número</div></div>
                                                <!-- Fila Nombres Padre -->
                                                <div class="col-12"><label for="padre_nombre" class="form-label">Nombres <span class="text-danger">*</span></label><input type="text" class="form-control" id="padre_nombre" name="padre_nombre" required><div class="invalid-feedback">Ingrese los nombres</div></div>
                                                <!-- Fila Apellidos Padre -->
                                                <div class="col-12"><label for="padre_apellidos" class="form-label">Apellidos <span class="text-danger">*</span></label><input type="text" class="form-control" id="padre_apellidos" name="padre_apellidos" required><div class="invalid-feedback">Ingrese los apellidos</div></div>
                                                <!-- Fila Contacto Padre -->
                                                <div class="col-md-6"><label for="padre_telefono" class="form-label">Teléfono <span class="text-danger">*</span></label><input type="tel" class="form-control" id="padre_telefono" name="padre_telefono" pattern="[0-9]{9}" maxlength="9" required><div class="invalid-feedback">Ingrese un teléfono</div></div>
                                                <div class="col-md-6"><label for="padre_email" class="form-label">Correo Electrónico</label><input type="email" class="form-control" id="padre_email" name="padre_email"></div>
                                            </div>
                                        </div>
                                        
                                        <div class="col-lg-6">
                                            <h5 class="text-primary mb-4">Datos de la Madre</h5>
                                            <div class="row g-3">
                                                <!-- Fila Documento Madre -->
                                                <div class="col-md-6"><label for="madre_tipo_doc" class="form-label">Tipo Documento <span class="text-danger">*</span></label><select class="form-select" id="madre_tipo_doc" name="madre_tipo_documento" required><option value="DNI" selected>DNI</option><option value="CE">CE</option></select><div class="invalid-feedback">Seleccione un tipo</div></div>
                                                <div class="col-md-6"><label for="madre_numero_doc" class="form-label">Número Documento <span class="text-danger">*</span></label><div class="input-group"><input type="text" class="form-control" id="madre_numero_doc" name="madre_numero_documento" maxlength="8" pattern="[0-9]{8}" required><button class="btn btn-outline-primary" type="button" id="btnConsultarReniecMadre"><i class="bi bi-search"></i></button></div><div class="invalid-feedback">Ingrese un número</div></div>
                                                <!-- Fila Nombres Madre -->
                                                <div class="col-12"><label for="madre_nombre" class="form-label">Nombres <span class="text-danger">*</span></label><input type="text" class="form-control" id="madre_nombre" name="madre_nombre" required><div class="invalid-feedback">Ingrese los nombres</div></div>
                                                <!-- Fila Apellidos Madre -->
                                                <div class="col-12"><label for="madre_apellidos" class="form-label">Apellidos <span class="text-danger">*</span></label><input type="text" class="form-control" id="madre_apellidos" name="madre_apellidos" required><div class="invalid-feedback">Ingrese los apellidos</div></div>
                                                <!-- Fila Contacto Madre -->
                                                <div class="col-md-6"><label for="madre_telefono" class="form-label">Teléfono <span class="text-danger">*</span></label><input type="tel" class="form-control" id="madre_telefono" name="madre_telefono" pattern="[0-9]{9}" maxlength="9" required><div class="invalid-feedback">Ingrese un teléfono</div></div>
                                                <div class="col-md-6"><label for="madre_email" class="form-label">Correo Electrónico</label><input type="email" class="form-control" id="madre_email" name="madre_email"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- PASO 3: Confirmación -->
                            <div class="wizard-step" data-step="3">
                                <div class="step-content-card">
                                    <h4 class="mb-4 text-2xl font-bold text-center" style="color: var(--cepre-navy);">Confirmación de Datos</h4>
                                    <div id="confirmationSummaryWizard" class="mb-4"></div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="nuevo_terms" name="terms" required>
                                        <label class="form-check-label" for="nuevo_terms">
                                            Acepto los <a href="#" class="text-primary">términos y condiciones</a> y la <a href="#" class="text-primary">política de privacidad</a>.
                                        </label>
                                        <div class="invalid-feedback">Debe aceptar los términos para continuar.</div>
                                    </div>
                                </div>
                            </div>

                            <!-- PASO 4: Formulario de Postulación -->
                            <div class="wizard-step" data-step="4">
                                <div id="formularioPostulacionContainer">
                                    <div class="text-center py-4">
                                        <div class="spinner-border text-success" role="status">
                                            <span class="visually-hidden">Cargando formulario...</span>
                                        </div>
                                        <p class="mt-2 text-muted">Cargando formulario de postulación...</p>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-light" id="prevStepBtnWizard" onclick="previousStepWizard()" style="display: none;">
                        <i class="bi bi-chevron-left me-1"></i> Anterior
                    </button>
                    <button type="button" class="btn btn-primary" id="nextStepBtnWizard" onclick="nextStepWizard()">
                        <span class="btn-text">Siguiente</span>
                        <i class="bi bi-chevron-right ms-1"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
    // Interceptar el formulario de importación para mostrar progreso
    document.addEventListener('DOMContentLoaded', function() {
        const formImportar = document.querySelector('#modalImportar form');
        
        if (formImportar) {
            formImportar.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                const archivo = formData.get('archivo_excel');
                
                if (!archivo || archivo.size === 0) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Por favor seleccione un archivo Excel'
                    });
                    return;
                }
                
                // Cerrar modal de importación y abrir modal de progreso
                const modalImportar = bootstrap.Modal.getInstance(document.getElementById('modalImportar'));
                modalImportar.hide();
                
                const modalProgreso = new bootstrap.Modal(document.getElementById('modalProgreso'));
                modalProgreso.show();
                
                // Resetear UI
                resetearModalProgreso();
                
                // Simular progreso mientras se procesa
                let progreso = 0;
                const intervalo = setInterval(() => {
                    if (progreso < 90) {
                        progreso += Math.random() * 15;
                        if (progreso > 90) progreso = 90;
                        actualizarProgreso(progreso, 'Procesando filas del Excel...');
                    }
                }, 500);
                
                // Enviar formulario via AJAX
                fetch(formImportar.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    clearInterval(intervalo);
                    actualizarProgreso(100, 'Importación completada');
                    
                    setTimeout(() => {
                        mostrarResultado(data);
                    }, 500);
                })
                .catch(error => {
                    clearInterval(intervalo);
                    console.error('Error:', error);
                    mostrarError('Ocurrió un error durante la importación. Por favor, intente nuevamente.');
                });
            });
        }
        
        function resetearModalProgreso() {
            document.getElementById('spinnerProgreso').classList.remove('d-none');
            document.getElementById('iconoExito').classList.add('d-none');
            document.getElementById('iconoAdvertencia').classList.add('d-none');
            document.getElementById('resumenFinal').classList.add('d-none');
            document.getElementById('btnCerrarProgreso').classList.add('d-none');
            document.getElementById('tituloProgreso').textContent = 'Procesando Importación...';
            actualizarProgreso(0, 'Iniciando importación...');
        }
        
        function actualizarProgreso(porcentaje, mensaje) {
            const porcentajeRedondeado = Math.round(porcentaje);
            document.getElementById('barraProgreso').style.width = porcentajeRedondeado + '%';
            document.getElementById('barraProgreso').setAttribute('aria-valuenow', porcentajeRedondeado);
            document.getElementById('porcentajeTexto').textContent = porcentajeRedondeado + '%';
            document.getElementById('porcentajeInside').textContent = porcentajeRedondeado + '%';
            document.getElementById('textoMensaje').textContent = mensaje;
        }
        
        function mostrarResultado(data) {
            document.getElementById('spinnerProgreso').classList.add('d-none');
            document.getElementById('barraProgreso').classList.remove('progress-bar-animated');
            
            const hayErrores = data.errores && data.errores.length > 0;
            const esSimulacro = data.simulacro || false;
            
            if (hayErrores) {
                document.getElementById('iconoAdvertencia').classList.remove('d-none');
                document.getElementById('tituloProgreso').textContent = esSimulacro ? 'Simulacro Completado con Advertencias' : 'Importación Completada con Advertencias';
                document.getElementById('mensajeProgreso').className = 'alert alert-warning mb-0';
                document.getElementById('textoMensaje').innerHTML = '<i class="bi bi-exclamation-triangle me-2"></i>' + data.message;
            } else {
                document.getElementById('iconoExito').classList.remove('d-none');
                document.getElementById('tituloProgreso').textContent = esSimulacro ? 'Simulacro Completado Exitosamente' : 'Importación Completada Exitosamente';
                document.getElementById('mensajeProgreso').className = 'alert alert-success mb-0';
                document.getElementById('textoMensaje').innerHTML = '<i class="bi bi-check-circle me-2"></i>' + data.message;
                document.getElementById('barraProgreso').classList.remove('bg-primary');
                document.getElementById('barraProgreso').classList.add('bg-success');
            }
            
            // Mostrar resumen
            document.getElementById('totalProcesados').textContent = data.procesados || 0;
            document.getElementById('totalCreados').textContent = data.creados || 0;
            
            if (hayErrores) {
                document.getElementById('totalErrores').textContent = data.errores.length;
                document.getElementById('lineaErrores').classList.remove('d-none');
            }
            
            document.getElementById('resumenFinal').classList.remove('d-none');
            document.getElementById('btnCerrarProgreso').classList.remove('d-none');
            
            // Mostrar errores detallados si existen
            if (hayErrores) {
                setTimeout(() => {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Errores Encontrados',
                        html: '<div style="max-height: 400px; overflow-y: auto; text-align: left;"><ul>' + 
                              data.errores.map(e => '<li>' + e + '</li>').join('') + 
                              '</ul></div>',
                        width: '600px',
                        confirmButtonText: 'Entendido'
                    });
                }, 1000);
            }
            
            // Recargar tabla si no es simulacro y fue exitoso
            if (!esSimulacro && !hayErrores) {
                setTimeout(() => {
                    location.reload();
                }, 2000);
            }
        }
        
        function mostrarError(mensaje) {
            document.getElementById('spinnerProgreso').classList.add('d-none');
            document.getElementById('iconoAdvertencia').classList.remove('d-none');
            document.getElementById('tituloProgreso').textContent = 'Error en la Importación';
            document.getElementById('mensajeProgreso').className = 'alert alert-danger mb-0';
            document.getElementById('textoMensaje').innerHTML = '<i class="bi bi-x-circle me-2"></i>' + mensaje;
            document.getElementById('barraProgreso').classList.remove('bg-primary', 'progress-bar-animated');
            document.getElementById('barraProgreso').classList.add('bg-danger');
            document.getElementById('btnCerrarProgreso').classList.remove('d-none');
        }
        
        // Resetear formulario al cerrar modal de progreso
        document.getElementById('modalProgreso').addEventListener('hidden.bs.modal', function() {
            document.querySelector('#modalImportar form').reset();
        });
    });
    </script>

    <!-- Drag & Drop Document Upload Script -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Inicializar drag & drop para todas las zonas
        const dropZones = document.querySelectorAll('.drop-zone');
        
        dropZones.forEach(zone => {
            const fileInput = zone.querySelector('.file-input');
            const dropContent = zone.querySelector('.drop-zone-content');
            const previewContainer = zone.querySelector('.preview-container');
            const previewImage = zone.querySelector('.preview-image');
            const fileName = zone.querySelector('.file-name');
            const fileSize = zone.querySelector('.file-size');
            const btnRemove = zone.querySelector('.btn-remove');
            const docType = zone.dataset.docType;
            const maxSize = parseInt(zone.dataset.maxSize) * 1024 * 1024; // Convertir MB a bytes
            
            // Click en la zona para abrir selector de archivos
            zone.addEventListener('click', (e) => {
                if (!e.target.classList.contains('btn-remove')) {
                    fileInput.click();
                }
            });
            
            // Prevenir comportamiento por defecto del navegador
            ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                zone.addEventListener(eventName, preventDefaults, false);
            });
            
            function preventDefaults(e) {
                e.preventDefault();
                e.stopPropagation();
            }
            
            // Resaltar zona cuando se arrastra archivo sobre ella
            ['dragenter', 'dragover'].forEach(eventName => {
                zone.addEventListener(eventName, () => {
                    zone.classList.add('drag-over');
                });
            });
            
            ['dragleave', 'drop'].forEach(eventName => {
                zone.addEventListener(eventName, () => {
                    zone.classList.remove('drag-over');
                });
            });
            
            // Manejar archivo soltado
            zone.addEventListener('drop', (e) => {
                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    handleFile(files[0]);
                }
            });
            
            // Manejar archivo seleccionado
            fileInput.addEventListener('change', (e) => {
                if (e.target.files.length > 0) {
                    handleFile(e.target.files[0]);
                }
            });
            
            // Procesar archivo
            function handleFile(file) {
                // Validar tamaño
                if (file.size > maxSize) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Archivo muy grande',
                        text: `El archivo no debe superar ${zone.dataset.maxSize}MB`,
                        confirmButtonColor: '#e91e63'
                    });
                    return;
                }
                
                // Validar tipo
                const acceptedTypes = zone.dataset.accept.split(',');
                const fileType = file.type;
                const isValid = acceptedTypes.some(type => {
                    if (type.trim() === 'image/*') {
                        return fileType.startsWith('image/');
                    }
                    return fileType === type.trim();
                });
                
                if (!isValid) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Tipo de archivo no válido',
                        text: 'Por favor selecciona un archivo del tipo correcto',
                        confirmButtonColor: '#e91e63'
                    });
                    return;
                }
                
                // Mostrar preview
                showPreview(file);
            }
            
            // Mostrar preview del archivo
            function showPreview(file) {
                dropContent.classList.add('d-none');
                previewContainer.classList.remove('d-none');
                
                // Actualizar información del archivo
                fileName.textContent = file.name;
                fileSize.textContent = formatFileSize(file.size);
                
                // Si es imagen, mostrar preview
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        previewImage.src = e.target.result;
                        previewImage.classList.remove('d-none');
                        const pdfPreview = zone.querySelector('.pdf-preview');
                        if (pdfPreview) pdfPreview.classList.add('d-none');
                    };
                    reader.readAsDataURL(file);
                } else {
                    // Si es PDF, mostrar icono
                    previewImage.classList.add('d-none');
                    const pdfPreview = zone.querySelector('.pdf-preview');
                    if (pdfPreview) pdfPreview.classList.remove('d-none');
                }
            }
            
            // Botón para eliminar archivo
            if (btnRemove) {
                btnRemove.addEventListener('click', (e) => {
                    e.stopPropagation();
                    fileInput.value = '';
                    dropContent.classList.remove('d-none');
                    previewContainer.classList.add('d-none');
                    previewImage.src = '';
                });
            }
            
            // Formatear tamaño de archivo
            function formatFileSize(bytes) {
                if (bytes === 0) return '0 Bytes';
                const k = 1024;
                const sizes = ['Bytes', 'KB', 'MB', 'GB'];
                const i = Math.floor(Math.log(bytes) / Math.log(k));
                return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
            }
        });
        
        // PROTECCIÓN: Evitar que se reemplace el contenido de las zonas de drag & drop
        const documentsContainer = document.getElementById('documents-container');
        if (documentsContainer) {
            // Guardar el HTML original de las zonas de drag & drop
            const originalHTML = documentsContainer.innerHTML;
            
            // Crear un observador para detectar cambios en el contenedor
            const observer = new MutationObserver((mutations) => {
                mutations.forEach((mutation) => {
                    // Si se eliminaron las zonas de drag & drop, restaurarlas
                    const dropZones = documentsContainer.querySelectorAll('.drop-zone');
                    if (dropZones.length === 0) {
                        console.log('⚠️ Detectado reemplazo de zonas de drag & drop. Restaurando...');
                        documentsContainer.innerHTML = originalHTML;
                        // Reinicializar los event listeners
                        initializeDragDropZones();
                    }
                });
            });
            
            // Configurar el observador para vigilar cambios en los hijos
            observer.observe(documentsContainer, {
                childList: true,
                subtree: true
            });
            
            console.log('✅ Protección de drag & drop activada');
        }
        
        // Función para reinicializar las zonas de drag & drop
        function initializeDragDropZones() {
            const dropZones = document.querySelectorAll('.drop-zone');
            dropZones.forEach(zone => {
                const fileInput = zone.querySelector('.file-input');
                const dropContent = zone.querySelector('.drop-zone-content');
                const previewContainer = zone.querySelector('.preview-container');
                const previewImage = zone.querySelector('.preview-image');
                const fileName = zone.querySelector('.file-name');
                const fileSize = zone.querySelector('.file-size');
                const btnRemove = zone.querySelector('.btn-remove');
                const maxSize = parseInt(zone.dataset.maxSize) * 1024 * 1024;
                
                if (!fileInput) return; // Skip si ya fue inicializado
                
                // Remover listeners anteriores clonando el elemento
                const newFileInput = fileInput.cloneNode(true);
                fileInput.parentNode.replaceChild(newFileInput, fileInput);
                
                // Click en la zona
                zone.addEventListener('click', (e) => {
                    if (!e.target.classList.contains('btn-remove')) {
                        newFileInput.click();
                    }
                });
                
                // Drag & Drop events
                ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
                    zone.addEventListener(eventName, (e) => {
                        e.preventDefault();
                        e.stopPropagation();
                    });
                });
                
                ['dragenter', 'dragover'].forEach(eventName => {
                    zone.addEventListener(eventName, () => zone.classList.add('drag-over'));
                });
                
                ['dragleave', 'drop'].forEach(eventName => {
                    zone.addEventListener(eventName, () => zone.classList.remove('drag-over'));
                });
                
                zone.addEventListener('drop', (e) => {
                    if (e.dataTransfer.files.length > 0) {
                        handleFile(e.dataTransfer.files[0], zone, newFileInput, dropContent, previewContainer, previewImage, fileName, fileSize, maxSize);
                    }
                });
                
                newFileInput.addEventListener('change', (e) => {
                    if (e.target.files.length > 0) {
                        handleFile(e.target.files[0], zone, newFileInput, dropContent, previewContainer, previewImage, fileName, fileSize, maxSize);
                    }
                });
                
                if (btnRemove) {
                    btnRemove.addEventListener('click', (e) => {
                        e.stopPropagation();
                        newFileInput.value = '';
                        dropContent.classList.remove('d-none');
                        previewContainer.classList.add('d-none');
                        previewImage.src = '';
                    });
                }
            });
        }
        
        function handleFile(file, zone, fileInput, dropContent, previewContainer, previewImage, fileName, fileSize, maxSize) {
            if (file.size > maxSize) {
                Swal.fire({
                    icon: 'error',
                    title: 'Archivo muy grande',
                    text: `El archivo no debe superar ${zone.dataset.maxSize}MB`,
                    confirmButtonColor: '#e91e63'
                });
                return;
            }
            
            const acceptedTypes = zone.dataset.accept.split(',');
            const isValid = acceptedTypes.some(type => {
                if (type.trim() === 'image/*') return file.type.startsWith('image/');
                return file.type === type.trim();
            });
            
            if (!isValid) {
                Swal.fire({
                    icon: 'error',
                    title: 'Tipo de archivo no válido',
                    text: 'Por favor selecciona un archivo del tipo correcto',
                    confirmButtonColor: '#e91e63'
                });
                return;
            }
            
            dropContent.classList.add('d-none');
            previewContainer.classList.remove('d-none');
            fileName.textContent = file.name;
            fileSize.textContent = formatFileSize(file.size);
            
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    previewImage.src = e.target.result;
                    previewImage.classList.remove('d-none');
                    const pdfPreview = zone.querySelector('.pdf-preview');
                    if (pdfPreview) pdfPreview.classList.add('d-none');
                };
                reader.readAsDataURL(file);
            } else {
                previewImage.classList.add('d-none');
                const pdfPreview = zone.querySelector('.pdf-preview');
                if (pdfPreview) pdfPreview.classList.remove('d-none');
            }
        }
        
        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
        }
        
        // Handler para el botón "Guardar Cambios"
        document.getElementById('saveDocumentChanges')?.addEventListener('click', function() {
            const formData = new FormData();
            const postulacionId = document.getElementById('edit-docs-postulacion-id').value;
            const observacion = document.getElementById('edit-docs-observacion').value;
            
            // Recopilar archivos de todas las zonas de drag & drop
            let hasFiles = false;
            document.querySelectorAll('.drop-zone .file-input').forEach(input => {
                if (input.files && input.files.length > 0) {
                    const docType = input.closest('.drop-zone').dataset.docType;
                    formData.append(docType, input.files[0]);
                    hasFiles = true;
                }
            });
            
            // Validar que al menos un archivo fue seleccionado
            if (!hasFiles) {
                Swal.fire({
                    icon: 'warning',
                    title: 'No hay archivos',
                    text: 'Por favor selecciona al menos un archivo para actualizar',
                    confirmButtonColor: '#e91e63'
                });
                return;
            }
            
            // Agregar datos adicionales
            formData.append('postulacion_id', postulacionId);
            formData.append('observacion', observacion);
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
            
            // Mostrar loading
            Swal.fire({
                title: 'Guardando documentos...',
                html: '<div class="cepre-spinner my-3"></div>',
                allowOutsideClick: false,
                showConfirmButton: false
            });
            
            // Enviar formulario a la ruta correcta de la API
            fetch(`/json/postulaciones/${postulacionId}/actualizar-documentos`, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                Swal.close();
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Documentos actualizados!',
                        text: data.message || 'Los documentos se actualizaron correctamente',
                        confirmButtonColor: '#00bcd4'
                    }).then(() => {
                        // Cerrar modal actual
                        const modal = bootstrap.Modal.getInstance(document.getElementById('editDocumentsModal'));
                        if (modal) modal.hide();
                        
                        // Recargar el modal de detalle para ver la nueva foto
                        if (typeof viewPostulacion === 'function') {
                            viewPostulacion(postulacionId);
                        }
                        
                        // Recargar tabla si existe
                        if (typeof window.postulacionesDataTable !== 'undefined') {
                            window.postulacionesDataTable.ajax.reload(null, false);
                        }
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message || 'No se pudieron actualizar los documentos',
                        confirmButtonColor: '#e91e63'
                    });
                }
            })
            .catch(error => {
                Swal.close();
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error de conexión',
                    text: 'No se pudo conectar con el servidor',
                    confirmButtonColor: '#e91e63'
                });
            });
        });
    });
    </script>
@endpush