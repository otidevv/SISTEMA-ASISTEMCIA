@extends('layouts.app')

@section('title', 'Gestión de Asistencia Docente')

@push('css')
<style>
    /* Paleta de colores y variables */
    :root {
        --primary-gradient: linear-gradient(135deg, #4f32c2 0%, #7367f0 100%);
        --success-gradient: linear-gradient(135deg, #10b981 0%, #28c76f 100%);
        --warning-gradient: linear-gradient(135deg, #ff9f43 0%, #ff8b1b 100%);
        --info-gradient: linear-gradient(135deg, #00cfe8 0%, #1ce1ff 100%);
        --primary-glow: 0 0 20px rgba(115, 103, 240, 0.4);
    }

    /* --- TARJETAS DE ESTADÍSTICAS --- */
    .tilebox-one {
        border: none; border-radius: 0.75rem; transition: all 0.3s ease; overflow: hidden;
    }
    .tilebox-one:hover {
        transform: translateY(-5px); box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    }
    .tilebox-one .card-body { position: relative; z-index: 2; }
    .tilebox-one .mdi { font-size: 3rem; transition: all 0.3s ease; opacity: 0.3; }
    .tilebox-one:hover .mdi { transform: scale(1.2) rotate(-10deg); }
    .tilebox-one[style*="--"] { color: white; }
    .tilebox-one[style*="--"] h2, .tilebox-one[style*="--"] h6, .tilebox-one[style*="--"] p { color: white; }
    .tilebox-one[style*="--"] h6, .tilebox-one[style*="--"] p { opacity: 0.8; }

    /* --- BOTONES Y UI --- */
    .btn-primary-gradient {
        background-image: var(--primary-gradient); border: none; color: white; transition: all 0.3s ease;
        box-shadow: 0 4px 10px rgba(115, 103, 240, 0.5);
    }
    .btn-primary-gradient:hover {
        transform: translateY(-2px); box-shadow: var(--primary-glow); color: white;
    }

    /* --- TABLA PROFESIONAL --- */
    .table-light thead th {
        background: #2a3042; color: #b4b7c1; border-bottom: 2px solid var(--bs-primary);
    }
    .table-light th.sortable { cursor: pointer; position: relative; }
    .table-light th.sortable:hover { background-color: #323950; color: #fff; }
    .sort-indicator { display: inline-block; width: 16px; height: 16px; margin-left: 5px; opacity: 0.6; vertical-align: middle; }
    .sort-indicator::after { font-family: 'Material Design Icons'; font-size: 16px; line-height: 1; }
    th[data-sort-direction="asc"] .sort-indicator::after { content: "\F005D"; } /* mdi-arrow-up */
    th[data-sort-direction="desc"] .sort-indicator::after { content: "\F0045"; } /* mdi-arrow-down */
    .asistencia-row-item:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.08); background-color: #f8f9fe; }

    /* --- AVATARES Y BADGES --- */
    .avatar-sm[data-bg-color] { color: white; }
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
                        <li class="breadcrumb-item">Académico</li>
                        <li class="breadcrumb-item active">Asistencias</li>
                    </ol>
                </div>
                <h4 class="page-title">
                    <i class="mdi mdi-fingerprint me-1"></i>
                    Asistencia de Docentes
                    @if ($cicloSeleccionado)
                        <span class="badge bg-primary fs-6 ms-2">{{ $cicloSeleccionado->nombre }}</span>
                    @elseif($fecha)
                        <span class="badge bg-info fs-6 ms-2">{{ \Carbon\Carbon::parse($fecha)->format('d/m/Y') }}</span>
                    @endif
                </h4>
            </div>
        </div>
    </div>
    <!-- fin del título de la página -->

    <!-- Estadísticas -->
    <div class="row">
        <div class="col-md-6 col-xl-3">
            <div class="card tilebox-one" style="background: var(--info-gradient);">
                <div class="card-body">
                    <i class="mdi mdi-format-list-numbered float-end"></i>
                    <h6 class="text-uppercase mt-0">Total Registros</h6>
                    <h2 class="my-2" id="totalRegistros">{{ $asistencias->total() }}</h2>
                    <p class="mb-0">En el periodo filtrado</p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card tilebox-one" style="background: var(--success-gradient);">
                <div class="card-body">
                    <i class="mdi mdi-login-variant float-end"></i>
                    <h6 class="text-uppercase mt-0">Entradas</h6>
                    <h2 class="my-2" id="totalEntradas">{{ $asistencias->where('tipo_asistencia', 'entrada')->count() }}</h2>
                    <p class="mb-0">Registros de ingreso</p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card tilebox-one" style="background: var(--warning-gradient);">
                <div class="card-body">
                    <i class="mdi mdi-logout-variant float-end"></i>
                    <h6 class="text-uppercase mt-0">Salidas</h6>
                    <h2 class="my-2" id="totalSalidas">{{ $asistencias->where('tipo_asistencia', 'salida')->count() }}</h2>
                    <p class="mb-0">Registros de salida</p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card tilebox-one" style="background: var(--primary-gradient);">
                <div class="card-body">
                    <i class="mdi mdi-account-group-outline float-end"></i>
                    <h6 class="text-uppercase mt-0">Docentes Únicos</h6>
                    <h2 class="my-2" id="docentesUnicos">{{ $asistencias->pluck('nro_documento')->unique()->count() }}</h2>
                    <p class="mb-0">Con asistencia hoy</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <!-- Filtros y Acciones -->
            <form method="GET" action="{{ route('asistencia-docente.index') }}" class="row g-3 mb-4 align-items-end bg-light p-3 rounded">
                <div class="col-md-3">
                    <label for="ciclo_id" class="form-label fw-bold">Ciclo Académico</label>
                    <select name="ciclo_id" id="ciclo_id" class="form-select form-select-sm">
                        <option value="">Todos los ciclos</option>
                        @foreach ($ciclos as $ciclo)
                            <option value="{{ $ciclo->id }}" {{ $cicloSeleccionado && $cicloSeleccionado->id == $ciclo->id ? 'selected' : '' }}>
                                {{ $ciclo->nombre }} @if($ciclo->es_activo) (Activo) @endif
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="fecha" class="form-label fw-bold">Fecha Específica</label>
                    <input type="date" class="form-control form-control-sm" name="fecha" value="{{ $fecha ?? '' }}">
                </div>
                <div class="col-md-3">
                    <label for="documento" class="form-label fw-bold">Docente (DNI o Nombre)</label>
                    <input type="text" class="form-control form-control-sm" name="documento" placeholder="Buscar..." value="{{ request('documento') ?? '' }}">
                </div>
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary btn-sm w-100"><i class="mdi mdi-filter-variant"></i> Filtrar</button>
                    <a href="{{ route('asistencia-docente.index') }}" class="btn btn-secondary btn-sm w-100"><i class="mdi mdi-reload"></i> Limpiar</a>
                </div>
            </form>
            
            <div class="d-flex justify-content-end mb-3">
                @can('asistencia-docente.create')
                    <a href="{{ route('asistencia-docente.create') }}" class="btn btn-primary-gradient btn-sm me-2"><i class="mdi mdi-plus"></i> Registrar Asistencia</a>
                @endcan
                @can('asistencia-docente.monitor')
                    <a href="{{ route('asistencia-docente.monitor') }}" class="btn btn-info btn-sm me-2"><i class="mdi mdi-monitor-dashboard"></i> Monitor en Vivo</a>
                @endcan
                @can('asistencia-docente.reports')
                    <a href="{{ route('asistencia-docente.reports') }}" class="btn btn-light btn-sm"><i class="mdi mdi-file-chart-outline"></i> Reportes</a>
                @endcan
            </div>
            
            <!-- Tabla de Asistencias -->
            <div class="table-responsive">
                <table class="table table-hover table-centered mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="sortable" data-sort="docente"><i class="mdi mdi-account-tie-outline me-1"></i>Docente<span class="sort-indicator"></span></th>
                            <th class="sortable" data-sort="curso"><i class="mdi mdi-book-open-outline me-1"></i>Curso<span class="sort-indicator"></span></th>
                            <th class="sortable" data-sort="fecha"><i class="mdi mdi-calendar-clock-outline me-1"></i>Fecha y Hora<span class="sort-indicator"></span></th>
                            <th><i class="mdi mdi-swap-horizontal-bold me-1"></i>Tipo</th>
                            <th><i class="mdi mdi-check-decagram-outline me-1"></i>Verificación</th>
                            <th class="text-center"><i class="mdi mdi-cogs me-1"></i>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="asistenciasTableBody">
                        @forelse($asistencias as $asistencia)
                            <tr class="asistencia-row-item" data-docente="{{ $asistencia->usuario->nombre_completo ?? '' }}" data-curso="{{ $asistencia->horario->curso->nombre ?? '' }}" data-fecha="{{ \Carbon\Carbon::parse($asistencia->fecha_registro)->timestamp }}">
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm rounded-circle d-flex align-items-center justify-content-center me-2" data-bg-color>
                                            <span class="text-white fw-bold">{{ substr($asistencia->usuario->nombre ?? 'N', 0, 1) }}</span>
                                        </div>
                                        <div>
                                            <h6 class="mb-0 fs-14">{{ $asistencia->usuario->nombre_completo ?? 'N/A' }}</h6>
                                            <small class="text-muted">DNI: {{ $asistencia->nro_documento }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $asistencia->horario->curso->nombre ?? 'N/A' }}</td>
                                <td>{{ \Carbon\Carbon::parse($asistencia->fecha_registro)->format('d/m/Y h:i:s A') }}</td>
                                <td>
                                    <span class="badge bg-{{ $asistencia->tipo_asistencia === 'entrada' ? 'success-lighten text-success' : 'secondary-lighten text-secondary' }}">
                                        <i class="mdi mdi-{{ $asistencia->tipo_asistencia === 'entrada' ? 'login' : 'logout' }} me-1"></i>
                                        {{ ucfirst($asistencia->tipo_asistencia) }}
                                    </span>
                                </td>
                                <td>
                                    @php
                                        $verificacion = ['0' => ['Biométrico', 'info'], '1' => ['Tarjeta', 'info'], '2' => ['Facial', 'info'], '3' => ['Código', 'info'], '4' => ['Manual', 'warning']];
                                        $tipo = $asistencia->tipo_verificacion;
                                    @endphp
                                    <span class="badge bg-{{ $verificacion[$tipo][1] ?? 'light' }}-lighten text-{{ $verificacion[$tipo][1] ?? 'dark' }}">
                                        {{ $verificacion[$tipo][0] ?? 'Desconocido' }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-info btn-sm" title="Ver Detalles"><i class="mdi mdi-eye"></i></button>
                                    <a href="{{ route('asistencia-docente.edit', $asistencia->id) }}" class="btn btn-warning btn-sm" title="Editar"><i class="mdi mdi-pencil"></i></a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center py-4"><i class="mdi mdi-archive-alert-outline fs-2 text-muted"></i><h5 class="mt-2">No se encontraron registros</h5></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
             @if ($asistencias->hasPages())
                <div class="mt-3">{{ $asistencias->appends(request()->query())->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // --- ANIMACIÓN DE CONTADORES ---
    function animateCounter(element) {
        const target = parseInt(element.textContent.replace(/S\/|\s|,/g, '')) || 0;
        let current = 0;
        const duration = 1500, stepTime = 20, steps = duration / stepTime;
        const increment = target / steps;
        
        const timer = setInterval(() => {
            current += increment;
            if (current >= target) {
                current = target;
                clearInterval(timer);
            }
            element.textContent = Math.floor(current).toLocaleString('es-PE');
        }, stepTime);
    }
    document.querySelectorAll('#totalRegistros, #totalEntradas, #totalSalidas, #docentesUnicos').forEach(animateCounter);

    // --- LÓGICA DE ORDENAMIENTO DE TABLA ---
    document.querySelectorAll('.table-light th.sortable').forEach(headerCell => {
        headerCell.addEventListener('click', () => {
            const currentIsAsc = headerCell.getAttribute('data-sort-direction') === 'asc';
            const newDirection = currentIsAsc ? 'desc' : 'asc';
            document.querySelectorAll('.table-light th.sortable').forEach(th => th.removeAttribute('data-sort-direction'));
            headerCell.setAttribute('data-sort-direction', newDirection);
            
            const sortProperty = headerCell.dataset.sort;
            const tableBody = document.getElementById('asistenciasTableBody');
            const allRows = Array.from(tableBody.querySelectorAll('tr.asistencia-row-item'));

            allRows.sort((a, b) => {
                let valA = (a.dataset[sortProperty] || '').toLowerCase();
                let valB = (b.dataset[sortProperty] || '').toLowerCase();
                if (sortProperty === 'fecha') {
                    valA = parseInt(a.dataset.fecha);
                    valB = parseInt(b.dataset.fecha);
                }
                if (valA < valB) return newDirection === 'asc' ? -1 : 1;
                if (valA > valB) return newDirection === 'asc' ? 1 : -1;
                return 0;
            }).forEach(row => tableBody.appendChild(row));
        });
    });
    
    // --- ASIGNAR COLORES DINÁMICOS A AVATARES ---
    const colors = ["#7367f0", "#28c76f", "#ff9f43", "#ea5455", "#00cfe8", "#8e44ad"];
    document.querySelectorAll('[data-bg-color]').forEach((el, index) => {
        el.style.backgroundColor = colors[index % colors.length];
    });
});
</script>
@endpush
