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
    .payments-header {
        background: var(--primary-gradient);
        color: white;
        padding: 3rem 0;
        position: relative;
        overflow: hidden;
    }

    .payments-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse"><path d="M 10 0 L 0 0 0 10" fill="none" stroke="rgba(255,255,255,0.05)" stroke-width="1"/></pattern></defs><rect width="100" height="100" fill="url(%23grid)"/></svg>');
        opacity: 0.3;
    }

    .payments-header .container-fluid {
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
        font-size: 2.5rem;
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

    .header-actions .btn-primary-custom {
        background: rgba(255, 255, 255, 0.15);
        border: 1px solid rgba(255, 255, 255, 0.3);
        color: white;
        padding: 0.875rem 2rem;
        border-radius: 0.75rem;
        font-weight: 600;
        font-size: 0.95rem;
        transition: all 0.3s ease;
        backdrop-filter: blur(10px);
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .header-actions .btn-primary-custom:hover {
        background: rgba(255, 255, 255, 0.25);
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        color: white;
    }

    /* Métricas */
    .metrics-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1.5rem;
        margin: -2rem 0 3rem 0;
        position: relative;
        z-index: 10;
    }

    .metric-card {
        background: var(--bg-white);
        border-radius: 1rem;
        padding: 2rem;
        box-shadow: var(--shadow-lg);
        border: 1px solid var(--border-color);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .metric-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 4px;
        background: var(--accent-color);
        transition: all 0.3s ease;
    }

    .metric-card:hover {
        transform: translateY(-8px);
        box-shadow: var(--shadow-xl);
    }

    .metric-card:nth-child(1)::before { background: var(--accent-color); }
    .metric-card:nth-child(2)::before { background: var(--success-color); }
    .metric-card:nth-child(3)::before { background: var(--warning-color); }
    .metric-card:nth-child(4)::before { background: #8b5cf6; }

    .metric-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 1.5rem;
    }

    .metric-icon {
        width: 3.5rem;
        height: 3.5rem;
        border-radius: 0.75rem;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        background: linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, rgba(59, 130, 246, 0.05) 100%);
        color: var(--accent-color);
    }

    .metric-icon.success {
        background: linear-gradient(135deg, rgba(16, 185, 129, 0.1) 0%, rgba(16, 185, 129, 0.05) 100%);
        color: var(--success-color);
    }

    .metric-icon.warning {
        background: linear-gradient(135deg, rgba(245, 158, 11, 0.1) 0%, rgba(245, 158, 11, 0.05) 100%);
        color: var(--warning-color);
    }

    .metric-icon.purple {
        background: linear-gradient(135deg, rgba(139, 92, 246, 0.1) 0%, rgba(139, 92, 246, 0.05) 100%);
        color: #8b5cf6;
    }

    .metric-value {
        font-size: 2.5rem;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 0.25rem;
        line-height: 1;
    }

    .metric-label {
        color: var(--text-secondary);
        font-size: 0.875rem;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .metric-trend {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
        border-radius: 0.375rem;
        font-weight: 600;
        background: rgba(16, 185, 129, 0.1);
        color: var(--success-color);
    }

    /* Card Principal */
    .main-panel {
        background: var(--bg-white);
        border-radius: 1.25rem;
        box-shadow: var(--shadow-lg);
        border: 1px solid var(--border-color);
        overflow: hidden;
        margin-bottom: 2rem;
    }

    .panel-header {
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        padding: 2rem;
        border-bottom: 1px solid var(--border-color);
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 1.5rem;
    }

    .panel-title {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .panel-title h2 {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--text-primary);
        margin: 0;
    }

    .panel-title .icon {
        width: 2.5rem;
        height: 2.5rem;
        border-radius: 0.5rem;
        background: var(--accent-color);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.125rem;
    }

    /* Alertas */
    .alert-modern {
        border: none;
        border-radius: 0.75rem;
        padding: 1.25rem 1.5rem;
        margin-bottom: 2rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        box-shadow: var(--shadow-md);
    }

    .alert-success {
        background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
        color: #166534;
        border-left: 4px solid var(--success-color);
    }

    /* Tabla */
    .data-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }

    .data-table thead th {
        background: var(--primary-gradient);
        color: white;
        padding: 1.25rem 1.5rem;
        font-weight: 600;
        font-size: 0.875rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        border: none;
        position: sticky;
        top: 0;
        z-index: 5;
    }

    .data-table thead th:first-child {
        border-top-left-radius: 0.75rem;
    }

    .data-table thead th:last-child {
        border-top-right-radius: 0.75rem;
    }

    .data-table tbody td {
        padding: 1.5rem;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
        transition: all 0.3s ease;
        font-weight: 500;
        color: var(--text-primary);
    }

    .data-table tbody tr {
        transition: all 0.3s ease;
    }

    .data-table tbody tr:hover {
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        transform: scale(1.01);
        box-shadow: var(--shadow-md);
    }

    /* Componentes específicos */
    .teacher-profile {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .teacher-avatar {
        width: 2.75rem;
        height: 2.75rem;
        border-radius: 0.75rem;
        background: var(--secondary-gradient);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 1.125rem;
        box-shadow: var(--shadow-md);
    }

    .teacher-info h4 {
        font-size: 0.95rem;
        font-weight: 600;
        color: var(--text-primary);
        margin: 0 0 0.25rem 0;
    }

    .teacher-info .teacher-id {
        font-size: 0.8rem;
        color: var(--text-secondary);
        font-weight: 500;
    }

    .amount-badge {
        background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
        color: #166534;
        padding: 0.5rem 1rem;
        border-radius: 2rem;
        font-size: 0.9rem;
        font-weight: 700;
        text-align: center;
        min-width: 100px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        border: 1px solid #bbf7d0;
    }

    .date-badge {
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        color: #92400e;
        padding: 0.4rem 0.8rem;
        border-radius: 0.5rem;
        font-size: 0.875rem;
        font-weight: 500;
        border: 1px solid #fbbf24;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .status-active {
        background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
        color: #166534;
        padding: 0.4rem 0.8rem;
        border-radius: 2rem;
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        border: 1px solid #bbf7d0;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    /* Botones de acción */
    .action-group {
        display: flex;
        gap: 0.5rem;
        justify-content: center;
    }

    .action-btn {
        width: 2.5rem;
        height: 2.5rem;
        border-radius: 0.5rem;
        border: none;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.875rem;
        transition: all 0.3s ease;
        position: relative;
        cursor: pointer;
        text-decoration: none;
    }

    .action-btn:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-lg);
    }

    .action-btn.edit {
        background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
        color: #92400e;
    }

    .action-btn.edit:hover {
        background: var(--warning-color);
        color: white;
    }

    .action-btn.delete {
        background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
        color: #991b1b;
    }

    .action-btn.delete:hover {
        background: var(--danger-color);
        color: white;
    }

    /* Estado vacío */
    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        color: var(--text-secondary);
    }

    .empty-icon {
        font-size: 5rem;
        margin-bottom: 2rem;
        opacity: 0.3;
        background: var(--secondary-gradient);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .empty-title {
        font-size: 1.75rem;
        font-weight: 700;
        margin-bottom: 1rem;
        color: var(--text-primary);
    }

    .empty-message {
        font-size: 1.125rem;
        margin-bottom: 2.5rem;
        max-width: 500px;
        margin-left: auto;
        margin-right: auto;
        line-height: 1.6;
    }

    .empty-action {
        background: var(--secondary-gradient);
        color: white;
        padding: 1rem 2rem;
        border-radius: 0.75rem;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.3s ease;
        box-shadow: var(--shadow-md);
    }

    .empty-action:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow-lg);
        color: white;
    }

    /* Paginación */
    .pagination-container {
        padding: 2rem;
        border-top: 1px solid var(--border-color);
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
    }

    .pagination-modern {
        display: flex;
        justify-content: center;
        gap: 0.5rem;
    }

    .pagination-modern .page-item .page-link {
        border: none;
        color: var(--text-secondary);
        padding: 0.75rem 1rem;
        border-radius: 0.5rem;
        font-weight: 500;
        transition: all 0.3s ease;
        background: transparent;
    }

    .pagination-modern .page-item .page-link:hover {
        background: var(--accent-color);
        color: white;
        transform: translateY(-1px);
    }

    .pagination-modern .page-item.active .page-link {
        background: var(--accent-color);
        color: white;
        box-shadow: var(--shadow-md);
    }

    /* Responsive */
    @media (max-width: 1024px) {
        .header-content {
            flex-direction: column;
            text-align: center;
        }

        .metrics-grid {
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            margin-top: -1rem;
        }

        .panel-header {
            flex-direction: column;
            align-items: stretch;
        }
    }

    @media (max-width: 768px) {
        .payments-header {
            padding: 2rem 0;
        }

        .header-info h1 {
            font-size: 2rem;
        }

        .metrics-grid {
            grid-template-columns: 1fr;
        }

        .metric-card {
            padding: 1.5rem;
        }

        .data-table {
            font-size: 0.875rem;
        }

        .data-table thead th,
        .data-table tbody td {
            padding: 1rem;
        }

        .action-group {
            flex-direction: column;
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
</style>
@endpush

@section('content')
<div class="container-fluid">
    <!-- Header Principal -->
    <div class="payments-header">
        <div class="container-fluid">
            <div class="header-content">
                <div class="header-info">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb breadcrumb-custom mb-3">
                            <li class="breadcrumb-item">
                                <i class="uil uil-estate me-1"></i>Centro Preuniversitario
                            </li>
                            <li class="breadcrumb-item">Gestión Financiera</li>
                            <li class="breadcrumb-item active">Pagos a Docentes</li>
                        </ol>
                    </nav>
                    <h1>
                        <i class="uil uil-money-bill"></i>
                        Gestión de Pagos a Docentes
                    </h1>
                    <p class="subtitle">
                        Sistema integral de administración y control de pagos al cuerpo docente
                    </p>
                </div>
                <div class="header-actions">
                    <a href="{{ route('pagos-docentes.create') }}" class="btn-primary-custom">
                        <i class="uil uil-plus"></i>
                        Nuevo Pago
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <!-- Panel de Métricas -->
        <div class="metrics-grid">
            <div class="metric-card fade-in-up">
                <div class="metric-header">
                    <div class="metric-icon">
                        <i class="uil uil-money-bill"></i>
                    </div>
                    <div class="metric-trend">Total</div>
                </div>
                <div class="metric-value">{{ $pagos->total() }}</div>
                <div class="metric-label">
                    <i class="uil uil-chart-line me-1"></i>
                    Pagos Registrados
                </div>
            </div>

            <div class="metric-card fade-in-up">
                <div class="metric-header">
                    <div class="metric-icon success">
                        <i class="uil uil-users-alt"></i>
                    </div>
                    <div class="metric-trend">Activos</div>
                </div>
                <div class="metric-value">{{ $pagos->where('fecha_fin', null)->count() }}</div>
                <div class="metric-label">
                    <i class="uil uil-user-check me-1"></i>
                    Pagos Activos
                </div>
            </div>

            <div class="metric-card fade-in-up">
                <div class="metric-header">
                    <div class="metric-icon warning">
                        <i class="uil uil-calculator"></i>
                    </div>
                    <div class="metric-trend">Promedio</div>
                </div>
                <div class="metric-value">S/ {{ number_format($pagos->avg('tarifa_por_hora'), 0) }}</div>
                <div class="metric-label">
                    <i class="uil uil-money-withdrawal me-1"></i>
                    Tarifa Promedio
                </div>
            </div>

            <div class="metric-card fade-in-up">
                <div class="metric-header">
                    <div class="metric-icon purple">
                        <i class="uil uil-briefcase-alt"></i>
                    </div>
                    <div class="metric-trend">Total</div>
                </div>
                <div class="metric-value">{{ $pagos->unique('docente_id')->count() }}</div>
                <div class="metric-label">
                    <i class="uil uil-graduation-cap me-1"></i>
                    Docentes con Pago
                </div>
            </div>
        </div>

        <!-- Alerta de Éxito -->
        @if(session('success'))
            <div class="alert alert-modern alert-success">
                <i class="uil uil-check-circle" style="font-size: 1.25rem;"></i>
                <div>
                    <strong>¡Operación exitosa!</strong>
                    {{ session('success') }}
                </div>
            </div>
        @endif

        <!-- Panel Principal -->
        <div class="main-panel fade-in-up">
            <!-- Header del Panel -->
            <div class="panel-header">
                <div class="panel-title">
                    <div class="icon">
                        <i class="uil uil-list-ul"></i>
                    </div>
                    <div>
                        <h2>Registro de Pagos a Docentes</h2>
                        <p class="text-muted mb-0">Gestión completa de la remuneración docente</p>
                    </div>
                </div>
            </div>

            <!-- Contenido de la Tabla -->
            <div class="panel-body p-0">
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>
                                    <i class="uil uil-user me-2"></i>
                                    Docente
                                </th>
                                <th>
                                    <i class="uil uil-money-bill me-2"></i>
                                    Tarifa por Hora
                                </th>
                                <th>
                                    <i class="uil uil-calendar-alt me-2"></i>
                                    Fecha Inicio
                                </th>
                                <th>
                                    <i class="uil uil-calender me-2"></i>
                                    Fecha Fin
                                </th>
                                <th class="text-center">
                                    <i class="uil uil-setting me-2"></i>
                                    Acciones
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pagos as $pago)
                                <tr class="payment-row">
                                    <td>
                                        <div class="teacher-profile">
                                            <div class="teacher-avatar">
                                                {{ substr($pago->docente->nombre ?? 'N', 0, 1) }}
                                            </div>
                                            <div class="teacher-info">
                                                <h4>
                                                    @if($pago->docente)
                                                        {{ $pago->docente->nombre }} {{ $pago->docente->apellido_paterno }}
                                                    @else
                                                        Docente no encontrado
                                                    @endif
                                                </h4>
                                                <div class="teacher-id">ID: {{ $pago->docente->id ?? '---' }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="amount-badge">
                                            <i class="uil uil-money-bill"></i>
                                            S/ {{ number_format($pago->tarifa_por_hora, 2) }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="date-badge">
                                            <i class="uil uil-calendar-alt"></i>
                                            {{ \Carbon\Carbon::parse($pago->fecha_inicio)->format('d/m/Y') }}
                                        </div>
                                    </td>
                                    <td>
                                        @if($pago->fecha_fin)
                                            <div class="date-badge">
                                                <i class="uil uil-calender"></i>
                                                {{ \Carbon\Carbon::parse($pago->fecha_fin)->format('d/m/Y') }}
                                            </div>
                                        @else
                                            <div class="status-active">
                                                <i class="uil uil-check-circle"></i>
                                                Activo
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="action-group">
                                            <a href="{{ route('pagos-docentes.edit', $pago->id) }}" 
                                               class="action-btn edit" 
                                               data-bs-toggle="tooltip" 
                                               title="Editar pago">
                                                <i class="uil uil-edit"></i>
                                            </a>
                                            <form action="{{ route('pagos-docentes.destroy', $pago->id) }}" 
                                                  method="POST" 
                                                  class="d-inline-block" 
                                                  onsubmit="return confirm('¿Está seguro de eliminar este pago?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="action-btn delete" 
                                                        data-bs-toggle="tooltip" 
                                                        title="Eliminar pago">
                                                    <i class="uil uil-trash-alt"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5">
                                        <div class="empty-state">
                                            <div class="empty-icon">
                                                <i class="uil uil-money-withdrawal"></i>
                                            </div>
                                            <div class="empty-title">
                                                No hay pagos registrados
                                            </div>
                                            <div class="empty-message">
                                                El sistema de pagos está listo para comenzar. Registra el primer pago 
                                                para establecer la estructura de remuneración docente.
                                            </div>
                                            <a href="{{ route('pagos-docentes.create') }}" class="empty-action">
                                                <i class="uil uil-plus me-2"></i>
                                                Registrar Primer Pago
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                @if($pagos->hasPages())
                    <div class="pagination-container">
                        <nav class="pagination-modern">
                            {{ $pagos->links('pagination::bootstrap-5') }}
                        </nav>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl, {
            delay: { show: 500, hide: 100 },
            animation: true
        });
    });

    // Animaciones de entrada escalonadas
    const metricCards = document.querySelectorAll('.metric-card');
    metricCards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(30px)';
        
        setTimeout(() => {
            card.style.transition = 'all 0.6s cubic-bezier(0.4, 0, 0.2, 1)';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 150);
    });

    // Efectos hover para filas
    const paymentRows = document.querySelectorAll('.payment-row');
    paymentRows.forEach(row => {
        row.addEventListener('mouseenter', function() {
            this.style.transition = 'all 0.3s ease';
            this.style.boxShadow = '0 8px 25px rgba(0, 0, 0, 0.1)';
            this.style.borderRadius = '0.75rem';
        });
        
        row.addEventListener('mouseleave', function() {
            this.style.boxShadow = '';
            this.style.borderRadius = '';
        });
    });

    // Contador animado para métricas
    const animateCounter = (element, target) => {
        let current = 0;
        const increment = target / 50;
        const timer = setInterval(() => {
            current += increment;
            if (current >= target) {
                current = target;
                clearInterval(timer);
            }
            element.textContent = Math.floor(current);
        }, 30);
    };

    // Iniciar contadores cuando sean visibles
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const valueElement = entry.target.querySelector('.metric-value');
                if (valueElement && !valueElement.textContent.includes('S/')) {
                    const targetValue = parseInt(valueElement.textContent);
                    animateCounter(valueElement, targetValue);
                }
                observer.unobserve(entry.target);
            }
        });
    });

    metricCards.forEach(card => observer.observe(card));
});
</script>
@endpush
@endsection