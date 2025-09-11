@extends('layouts.app')

@section('title', 'Gestión de Pagos a Docentes')

{{-- CSS para un look más profesional y colorido --}}
@push('css')
<style>
    /* Paleta de colores y variables */
    :root {
        --primary-gradient: linear-gradient(135deg, #4f32c2 0%, #7367f0 100%);
        --success-gradient: linear-gradient(135deg, #10b981 0%, #28c76f 100%);
        --warning-gradient: linear-gradient(135deg, #ff9f43 0%, #ff8b1b 100%); /* Naranja ajustado para mejor contraste */
        --info-gradient: linear-gradient(135deg, #00cfe8 0%, #1ce1ff 100%);
        --primary-glow: 0 0 20px rgba(115, 103, 240, 0.4);
    }

    /* --- TARJETAS DE ESTADÍSTICAS MEJORADAS --- */
    .tilebox-one {
        border: none;
        border-radius: 0.75rem;
        transition: all 0.3s ease;
        overflow: hidden;
    }
    .tilebox-one:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    }
    .tilebox-one .card-body {
        position: relative;
        z-index: 2;
    }
    .tilebox-one .mdi {
        font-size: 3rem;
        transition: all 0.3s ease;
    }
    .tilebox-one:hover .mdi {
        transform: scale(1.2) rotate(-10deg);
    }
    
    /* Estilos específicos para tarjetas con fondo de color */
    .tilebox-one[style*="--"] {
        color: white;
    }
    .tilebox-one[style*="--"] .mdi {
        opacity: 0.3 !important;
    }
    .tilebox-one[style*="--"] h2, .tilebox-one[style*="--"] h6, .tilebox-one[style*="--"] p {
        color: white;
    }
    .tilebox-one[style*="--"] h6, .tilebox-one[style*="--"] p {
        opacity: 0.8; /* Texto secundario con ligera transparencia */
    }

    /* --- BOTONES Y ELEMENTOS DE UI --- */
    .btn-primary-gradient {
        background-image: var(--primary-gradient);
        border: none;
        color: white;
        transition: all 0.3s ease;
        box-shadow: 0 4px 10px rgba(115, 103, 240, 0.5);
    }
    .btn-primary-gradient:hover {
        transform: translateY(-2px);
        box-shadow: var(--primary-glow);
        color: white;
    }
    
    /* --- TABLA PROFESIONAL --- */
    .table-light thead th {
        background: #2a3042;
        color: #b4b7c1;
        border-bottom: 2px solid var(--bs-primary);
    }
    .table-light th.sortable {
        cursor: pointer;
        position: relative;
    }
    .table-light th.sortable:hover {
        background-color: #323950;
        color: #fff;
    }
    .sort-indicator {
        display: inline-block; width: 16px; height: 16px; margin-left: 5px; opacity: 0.6; vertical-align: middle;
    }
    .sort-indicator::after {
        font-family: 'Material Design Icons'; font-size: 16px; line-height: 1;
    }
    th[data-sort-direction="asc"] .sort-indicator::after { content: "\F005D"; } /* mdi-arrow-up */
    th[data-sort-direction="desc"] .sort-indicator::after { content: "\F0045"; } /* mdi-arrow-down */

    .payment-row-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        background-color: #f8f9fe;
    }
    
    /* --- BADGES Y OTROS --- */
    .badge.bg-success-lighten {
        background-color: rgba(40, 199, 111, 0.15) !important;
        color: #28c76f !important;
        font-weight: 600;
    }
    .badge.bg-secondary-lighten {
        background-color: rgba(130, 134, 139, 0.15) !important;
        color: #82868b !important;
        font-weight: 600;
    }
    .card-sidebar .card-header {
        background: linear-gradient(135deg, #f8f9fe 0%, #f1f3f9 100%);
        border-bottom: 1px solid #eef2f7;
    }
    .avatar-sm[data-bg-color] {
        color: white;
    }
    .highlight { background-color: #f8e479; border-radius: 3px; }
</style>
@endpush

@section('content')
<div class="container-fluid">
    
    <!-- Título de la página y breadcrumbs -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="javascript: void(0);">Centro Pre</a></li>
                        <li class="breadcrumb-item">Gestión Financiera</li>
                        <li class="breadcrumb-item active">Pagos a Docentes</li>
                    </ol>
                </div>
                <h4 class="page-title">
                    <i class="mdi mdi-cash-multiple me-1"></i>
                    Gestión de Pagos
                </h4>
            </div>
        </div>
    </div>
    <!-- fin del título de la página -->

    <!-- Alertas de sesión -->
    @if(session('success'))
        <div class="row">
            <div class="col-12">
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="mdi mdi-check-all me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif

    <!-- Estadísticas -->
    <div class="row">
        <div class="col-md-6 col-xl-3">
            <div class="card tilebox-one" style="background: var(--info-gradient);">
                <div class="card-body">
                    <i class="mdi mdi-cash-register float-end"></i>
                    <h6 class="text-uppercase mt-0">Total Pagos Registrados</h6>
                    <h2 class="my-2" id="totalPagos">{{ $pagos->total() ?? 0 }}</h2>
                    <p class="mb-0">
                        <span class="text-nowrap">Registros en el sistema</span>
                    </p>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 col-xl-3">
            <div class="card tilebox-one" style="background: var(--success-gradient);">
                <div class="card-body">
                    <i class="mdi mdi-briefcase-check float-end"></i>
                    <h6 class="text-uppercase mt-0">Pagos Activos</h6>
                    <h2 class="my-2" id="pagosActivos">{{ $pagos->where('fecha_fin', null)->count() ?? 0 }}</h2>
                    <p class="mb-0">
                        <span class="text-nowrap">Contratos vigentes</span>
                    </p>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 col-xl-3">
            <div class="card tilebox-one" style="background: var(--warning-gradient);">
                <div class="card-body">
                    <i class="mdi mdi-currency-usd float-end"></i>
                    <h6 class="text-uppercase mt-0">Tarifa Promedio</h6>
                    <h2 class="my-2" id="tarifaPromedio">S/ {{ number_format($pagos->avg('tarifa_por_hora'), 2) }}</h2>
                    <p class="mb-0">
                        <span class="text-nowrap">Promedio por hora</span>
                    </p>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 col-xl-3">
            <div class="card tilebox-one" style="background: var(--primary-gradient);">
                <div class="card-body">
                    <i class="mdi mdi-calendar-cash float-end"></i>
                    <h6 class="text-uppercase mt-0">Total Pagado (Mes Actual)</h6>
                    <h2 class="my-2" id="totalMesActual">S/ 0.00</h2>
                     <p class="mb-0">
                        <span class="text-nowrap">Estimado basado en registros</span>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="header-title">
                        <i class="mdi mdi-view-list me-2"></i>
                        Registro de Pagos
                    </h4>
                    <p class="text-muted mb-0">Gestione y visualice todos los pagos al personal docente</p>
                </div>
                <div class="card-body">

                    <!-- Acciones y Filtros -->
                    <div class="row align-items-center mb-3 bg-light p-2 rounded">
                        <div class="col-md-6">
                            <a href="{{ route('pagos-docentes.create') }}" class="btn btn-primary-gradient btn-sm">
                                <i class="mdi mdi-plus me-1"></i>
                                Nuevo Registro de Pago
                            </a>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex flex-wrap gap-2 justify-content-md-end">
                                <div class="position-relative" style="min-width: 220px;">
                                    <input type="text" 
                                           class="form-control form-control-sm" 
                                           id="payment_search" 
                                           placeholder="Buscar por docente..."
                                           autocomplete="off">
                                    <div class="position-absolute top-50 end-0 translate-middle-y pe-2">
                                        <i class="mdi mdi-magnify text-muted"></i>
                                    </div>
                                </div>
                                <select id="status_filter" class="form-select form-select-sm" style="width: auto;">
                                    <option value="">Todos los estados</option>
                                    <option value="activo">Activo</option>
                                    <option value="finalizado">Finalizado</option>
                                </select>
                           </div>
                        </div>
                    </div>

                    <!-- Tabla de Pagos -->
                    <div class="table-responsive">
                        <table class="table table-hover table-centered mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="sortable" data-sort="docente"><i class="mdi mdi-account-circle-outline me-1"></i>Docente<span class="sort-indicator"></span></th>
                                    <th class="sortable" data-sort="tarifa"><i class="mdi mdi-cash me-1"></i>Tarifa por Hora<span class="sort-indicator"></span></th>
                                    <th class="sortable" data-sort="periodo"><i class="mdi mdi-calendar-clock-outline me-1"></i>Periodo<span class="sort-indicator"></span></th>
                                    <th class="text-center sortable" data-sort="estado"><i class="mdi mdi-list-status me-1"></i>Estado<span class="sort-indicator"></span></th>
                                    <th class="text-center"><i class="mdi mdi-cogs me-1"></i>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="paymentsTableBody">
                                @forelse($pagos as $pago)
                                    <tr class="payment-row-item" 
                                        data-docente="{{ $pago->docente->nombre_completo ?? '' }}"
                                        data-status="{{ $pago->fecha_fin ? 'finalizado' : 'activo' }}"
                                        data-tarifa="{{ $pago->tarifa_por_hora }}"
                                        data-periodo="{{ \Carbon\Carbon::parse($pago->fecha_inicio)->timestamp }}">
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm rounded-circle d-flex align-items-center justify-content-center me-2" data-bg-color>
                                                    <span class="text-white fw-bold">
                                                        {{ substr($pago->docente->nombre ?? 'N', 0, 1) }}
                                                    </span>
                                                </div>
                                                <div>
                                                    <h6 class="mb-0 fs-14">{{ $pago->docente->nombre_completo ?? 'Docente no encontrado' }}</h6>
                                                    <small class="text-muted">ID: {{ $pago->docente->id ?? '---' }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="fw-bold text-success fs-15">S/ {{ number_format($pago->tarifa_por_hora, 2) }}</span>
                                        </td>
                                        <td>
                                            <i class="mdi mdi-calendar-start text-info"></i> {{ \Carbon\Carbon::parse($pago->fecha_inicio)->format('d/m/Y') }}<br>
                                            <i class="mdi mdi-calendar-end text-danger"></i> {{ $pago->fecha_fin ? \Carbon\Carbon::parse($pago->fecha_fin)->format('d/m/Y') : 'Presente' }}
                                        </td>
                                        <td class="text-center">
                                            @if($pago->fecha_fin)
                                                <span class="badge bg-secondary-lighten text-secondary">Finalizado</span>
                                            @else
                                                <span class="badge bg-success-lighten text-success">Activo</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <div class="dropdown">
                                                <button class="btn btn-light btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                    Acción
                                                </button>
                                                <ul class="dropdown-menu">
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('pagos-docentes.edit', $pago->id) }}">
                                                            <i class="mdi mdi-pencil me-2 text-warning"></i>Editar
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item text-danger" href="#" onclick="showDeleteConfirmation({{ $pago->id }})">
                                                            <i class="mdi mdi-delete me-2"></i>Eliminar
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5">
                                            <div class="text-center py-5" id="emptyState">
                                                <i class="mdi mdi-cash-remove display-3 text-muted mb-3"></i>
                                                <h5 class="text-muted">No hay pagos registrados</h5>
                                                <p class="text-muted">Comience creando el primer registro de pago</p>
                                                <a href="{{ route('pagos-docentes.create') }}" class="btn btn-primary mt-2">
                                                    <i class="mdi mdi-plus me-2"></i>Crear Primer Registro
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                                <tr id="no-results-row" style="display: none;">
                                    <td colspan="5" class="text-center py-4">
                                        <i class="mdi mdi-magnify-close fs-2 text-muted"></i>
                                        <h5 class="mt-2">No se encontraron resultados</h5>
                                        <p class="text-muted">Intenta ajustar tu búsqueda o filtros.</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Paginación -->
                    @if($pagos->hasPages())
                        <div class="d-flex justify-content-center mt-4">
                            {{ $pagos->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Pagos Recientes -->
            <div class="card card-sidebar">
                <div class="card-header">
                    <h4 class="header-title">
                        <i class="mdi mdi-history me-2"></i>
                        Pagos Recientes
                    </h4>
                    <p class="text-muted mb-0">Últimos registros añadidos</p>
                </div>
                <div class="card-body" style="max-height: 280px; overflow-y: auto;">
                    @forelse ($pagos->take(5) as $pagoReciente)
                    <div class="d-flex align-items-start mb-3 p-2 border-start border-3 border-primary bg-light bg-opacity-50 rounded">
                        <div class="avatar-sm bg-primary rounded-circle d-flex align-items-center justify-content-center me-3">
                            <i class="mdi mdi-cash-plus text-white"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-1 fs-14">
                                {{ Str::limit($pagoReciente->docente->nombre_completo, 20) ?? 'N/A' }}
                            </h6>
                            <div class="text-muted small mb-1">
                                <i class="mdi mdi-currency-usd"></i> S/ {{ number_format($pagoReciente->tarifa_por_hora, 2) }} por hora
                            </div>
                            <small class="text-muted">
                                <i class="mdi mdi-clock-outline me-1"></i>
                                Registrado {{ $pagoReciente->created_at->diffForHumans() }}
                            </small>
                        </div>
                    </div>
                    @empty
                    <div class="text-center text-muted py-3">
                        <i class="mdi mdi-calendar-remove display-4"></i>
                        <p class="mb-0 mt-2">Sin actividad reciente</p>
                    </div>
                    @endforelse
                </div>
            </div>
            
            <!-- Top Docentes por Tarifa -->
            <div class="card mt-3 card-sidebar">
                <div class="card-header">
                    <h4 class="header-title">
                        <i class="mdi mdi-trophy-award me-2"></i>
                        Top 5 Docentes por Tarifa
                    </h4>
                    <p class="text-muted mb-0">Basado en la tarifa por hora más alta</p>
                </div>
                <div class="card-body" style="max-height: 450px; overflow-y: auto;">
                    @php
                        // Filtrar solo pagos activos, ordenarlos por tarifa y obtener docentes únicos
                        $topDocentes = $pagos->sortByDesc('tarifa_por_hora')
                                            ->unique('docente_id')
                                            ->take(5);
                    @endphp
                    @forelse ($topDocentes as $pagoTop)
                    <div class="d-flex align-items-center mb-3">
                        <div class="avatar-sm rounded-circle d-flex align-items-center justify-content-center me-3" data-bg-color>
                            <span class="text-white fw-bold">{{ substr($pagoTop->docente->nombre ?? 'N', 0, 1) }}</span>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-0 fs-14">{{ $pagoTop->docente->nombre_completo ?? 'N/A' }}</h6>
                            <small class="text-muted">
                                <i class="mdi mdi-cash"></i> S/ {{ number_format($pagoTop->tarifa_por_hora, 2) }} / hora
                            </small>
                        </div>
                        <span class="badge bg-warning-lighten text-warning">Top {{ $loop->iteration }}</span>
                    </div>
                    @empty
                    <div class="text-center text-muted py-3">
                        <i class="mdi mdi-information-outline display-4"></i>
                        <p class="mb-0 mt-2">No hay suficientes datos</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmación para eliminar -->
<div class="modal fade" id="deleteConfirmationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="mdi mdi-alert-circle me-2"></i>Confirmar Eliminación
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>
                    <i class="mdi mdi-alert-circle-outline text-danger me-2"></i>
                    ¿Estás seguro de que quieres eliminar este registro de pago?
                </p>
                <p class="text-muted">Esta acción no se puede deshacer.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <form id="deleteForm" method="POST" class="d-inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Eliminar</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // Función para animar contadores de estadísticas
    function animateCounter(element) {
        let targetText = element.textContent.replace(/[S\/,]/g, '').trim();
        const target = parseFloat(targetText) || 0;
        let current = 0;
        const duration = 1500; // 1.5 segundos
        const stepTime = 20;
        const steps = duration / stepTime;
        const increment = target / steps;
        
        const timer = setInterval(() => {
            current += increment;
            if (current >= target) {
                current = target;
                clearInterval(timer);
            }
            if (element.id === 'tarifaPromedio' || element.id === 'totalMesActual') {
                 element.textContent = 'S/ ' + current.toLocaleString('es-PE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            } else {
                 element.textContent = Math.floor(current);
            }
        }, stepTime);
    }
    
    document.querySelectorAll('#totalPagos, #pagosActivos, #tarifaPromedio').forEach(animateCounter);

    // Lógica para el modal de eliminación
    window.showDeleteConfirmation = function(pagoId) {
        const deleteForm = document.getElementById('deleteForm');
        deleteForm.action = `/pagos-docentes/${pagoId}`;
        const myModal = new bootstrap.Modal(document.getElementById('deleteConfirmationModal'));
        myModal.show();
    }

    // Lógica de búsqueda y filtro
    const searchInput = document.getElementById('payment_search');
    const statusFilter = document.getElementById('status_filter');
    const tableBody = document.getElementById('paymentsTableBody');
    const allRows = Array.from(tableBody.querySelectorAll('tr.payment-row-item'));
    const noResultsRow = document.getElementById('no-results-row');

    function applyFilters() {
        const searchTerm = searchInput.value.toLowerCase();
        const statusTerm = statusFilter.value.toLowerCase();
        let visibleCount = 0;

        allRows.forEach(row => {
            const docente = (row.dataset.docente || '').toLowerCase();
            const status = (row.dataset.status || '').toLowerCase();
            
            const matchesSearch = !searchTerm || docente.includes(searchTerm);
            const matchesStatus = !statusTerm || status === statusTerm;

            if (matchesSearch && matchesStatus) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });
        
        noResultsRow.style.display = visibleCount === 0 ? '' : 'none';
        
        // Resaltar texto de búsqueda
        allRows.forEach(row => {
            const cell = row.querySelector('td:first-child h6');
            if(cell){
                const originalText = row.dataset.docente;
                if(searchTerm){
                    const regex = new RegExp(`(${searchTerm})`, 'gi');
                    cell.innerHTML = originalText.replace(regex, `<span class="highlight">$1</span>`);
                } else {
                    cell.innerHTML = originalText;
                }
            }
        });
    }

    searchInput.addEventListener('keyup', applyFilters);
    statusFilter.addEventListener('change', applyFilters);

    // Lógica de ordenamiento de tabla
    document.querySelectorAll('.table-light th.sortable').forEach(headerCell => {
        headerCell.addEventListener('click', () => {
            const tableElement = headerCell.closest('table');
            const currentIsAsc = headerCell.getAttribute('data-sort-direction') === 'asc';
            const newDirection = currentIsAsc ? 'desc' : 'asc';

            document.querySelectorAll('.table-light th.sortable').forEach(th => th.removeAttribute('data-sort-direction'));
            headerCell.setAttribute('data-sort-direction', newDirection);

            const sortProperty = headerCell.dataset.sort;

            allRows.sort((a, b) => {
                let valA, valB;
                switch(sortProperty) {
                    case 'tarifa':
                    case 'periodo':
                        valA = parseFloat(a.dataset[sortProperty]);
                        valB = parseFloat(b.dataset[sortProperty]);
                        break;
                    case 'estado':
                        valA = a.dataset.status === 'activo' ? 1 : 0;
                        valB = b.dataset.status === 'activo' ? 1 : 0;
                        break;
                    default: // docente
                        valA = a.dataset[sortProperty].toLowerCase();
                        valB = b.dataset[sortProperty].toLowerCase();
                }
                
                if (valA < valB) return newDirection === 'asc' ? -1 : 1;
                if (valA > valB) return newDirection === 'asc' ? 1 : -1;
                return 0;
            })
            .forEach(row => tableBody.appendChild(row));
        });
    });

    // Calcular total pagado en el mes actual (simulado)
    const totalMesActualEl = document.getElementById('totalMesActual');
    if (totalMesActualEl) {
        let totalMes = 0;
        allRows.forEach(row => {
            if (row.dataset.status === 'activo') {
                totalMes += parseFloat(row.dataset.tarifa) * 40; // Simulación: 40 horas al mes
            }
        });
        totalMesActualEl.textContent = `S/ ${totalMes.toFixed(2)}`;
        animateCounter(totalMesActualEl);
    }
    
    // Asignar colores dinámicos a los avatares
    const colors = ["#7367f0", "#28c76f", "#ff9f43", "#ea5455", "#00cfe8", "#8e44ad"];
    document.querySelectorAll('[data-bg-color]').forEach((el, index) => {
        el.style.backgroundColor = colors[index % colors.length];
    });
});
</script>
@endpush

