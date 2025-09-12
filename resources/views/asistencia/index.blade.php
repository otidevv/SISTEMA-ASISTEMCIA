@extends('layouts.app')

@section('title', 'Registros de Asistencia')

@push('css')
    <!-- Font Awesome (for original search icon) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
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
            border: none; border-radius: 0.75rem; transition: all 0.3s ease; overflow: hidden; color: white;
        }
        .tilebox-one:hover {
            transform: translateY(-5px); box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        .tilebox-one .card-body { position: relative; z-index: 2; padding: 1.25rem; }
        .tilebox-one .mdi { font-size: 2.5rem; transition: all 0.3s ease; opacity: 0.3; }
        .tilebox-one:hover .mdi { transform: scale(1.2) rotate(-10deg); }
        .tilebox-one h2, .tilebox-one h6 { color: white; margin: 0; }
        .tilebox-one h6 { opacity: 0.8; font-size: 0.9rem; }
        .tilebox-one h2 { font-size: 1.75rem; font-weight: 700; }
        
        /* --- BOTONES Y UI --- */
        .btn-primary-gradient {
            background-image: var(--primary-gradient); border: none; color: white !important; transition: all 0.3s ease;
            box-shadow: 0 4px 10px rgba(115, 103, 240, 0.5);
        }
        .btn-primary-gradient:hover {
            transform: translateY(-2px); box-shadow: var(--primary-glow); color: white !important;
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
        .avatar-sm { width: 36px; height: 36px; font-size: 0.9rem; }
        .avatar-sm[data-bg-color] { color: white; }

        /* --- BÚSQUEDA CON SUGERENCIAS --- */
        .search-container { position: relative; }
        .suggestions-dropdown {
            position: absolute; top: 100%; left: 0; right: 0; background: white;
            border: 1px solid #dee2e6; border-top: none; border-radius: 0 0 0.375rem 0.375rem;
            max-height: 300px; overflow-y: auto; display: none;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1); z-index: 1050;
        }
        .suggestion-item { padding: 10px 15px; cursor: pointer; transition: background-color 0.2s ease; border-bottom: 1px solid #f1f3f4; }
        .suggestion-item:last-child { border-bottom: none; }
        .suggestion-item:hover, .suggestion-item.active { background-color: #f8f9fa; }

        /* ========= MEJORA DE PAGINACIÓN ========= */
        .pagination {
            --bs-pagination-padding-y: 0.3rem;      /* Reduce el padding vertical */
            --bs-pagination-padding-x: 0.6rem;      /* Reduce el padding horizontal */
            --bs-pagination-font-size: 0.875rem;    /* Reduce el tamaño de la fuente */
        }
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
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Registros de Asistencia</li>
                    </ol>
                </div>
                <h4 class="page-title">
                    <i class="mdi mdi-fingerprint me-1"></i>
                    Registros de Asistencia
                    @if ($fecha)
                        <span class="badge bg-info fs-6 ms-2">{{ \Carbon\Carbon::parse($fecha)->format('d/m/Y') }}</span>
                    @endif
                </h4>
            </div>
        </div>
    </div>

    <!-- Estadísticas -->
    <div class="row">
        <div class="col-md-3"><div class="card tilebox-one" style="background: var(--info-gradient);"><div class="card-body"><i class="mdi mdi-format-list-numbered float-end"></i><h6>Total Registros</h6><h2 id="stat-total">{{ $registros->total() }}</h2></div></div></div>
        <div class="col-md-3"><div class="card tilebox-one" style="background: var(--success-gradient);"><div class="card-body"><i class="mdi mdi-login-variant float-end"></i><h6>Entradas</h6><h2 id="stat-entradas">{{ $registros->where('tipo_asistencia', 'entrada')->count() }}</h2></div></div></div>
        <div class="col-md-3"><div class="card tilebox-one" style="background: var(--warning-gradient);"><div class="card-body"><i class="mdi mdi-logout-variant float-end"></i><h6>Salidas</h6><h2 id="stat-salidas">{{ $registros->where('tipo_asistencia', 'salida')->count() }}</h2></div></div></div>
        <div class="col-md-3"><div class="card tilebox-one" style="background: var(--primary-gradient);"><div class="card-body"><i class="mdi mdi-account-group-outline float-end"></i><h6>Estudiantes Únicos</h6><h2 id="stat-estudiantes">{{ $registros->pluck('nro_documento')->unique()->count() }}</h2></div></div></div>
    </div>

    <div class="card">
        <div class="card-body">
            <!-- Filtros y Acciones -->
            <form action="{{ route('asistencia.index') }}" method="GET" class="row g-3 mb-4 align-items-end bg-light p-3 rounded">
                <div class="col-md-4">
                    <label for="fecha" class="form-label fw-bold">Fecha</label>
                    <input type="date" class="form-control form-control-sm" id="fecha" name="fecha" value="{{ $fecha ?? date('Y-m-d') }}">
                </div>
                <div class="col-md-5">
                    <label for="documento" class="form-label fw-bold">Estudiante (DNI o Nombre)</label>
                    <div class="search-container">
                        <input type="text" class="form-control form-control-sm" id="documento" name="documento" value="{{ $documento ?? '' }}" placeholder="Buscar..." autocomplete="off">
                        <div class="suggestions-dropdown" id="suggestions"></div>
                    </div>
                </div>
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary btn-sm w-100"><i class="mdi mdi-filter-variant"></i> Filtrar</button>
                    <a href="{{ route('asistencia.index') }}" class="btn btn-secondary btn-sm w-100"><i class="mdi mdi-reload"></i> Limpiar</a>
                </div>
            </form>

            <div class="d-flex justify-content-end mb-3">
                @if (Auth::user()->hasPermission('attendance.register'))<a href="{{ route('asistencia.registrar') }}" class="btn btn-primary-gradient btn-sm me-2"><i class="mdi mdi-plus"></i> Registrar</a>@endif
                @if (Auth::user()->hasPermission('attendance.export'))<a href="{{ route('asistencia.exportar') }}" class="btn btn-info btn-sm text-white"><i class="mdi mdi-export"></i> Exportar</a>@endif
            </div>
            
            <div class="table-responsive">
                <table class="table table-hover table-centered mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="sortable" data-sort="estudiante"><i class="mdi mdi-account-circle-outline me-1"></i>Estudiante<span class="sort-indicator"></span></th>
                            <th class="sortable" data-sort="fecha"><i class="mdi mdi-calendar-clock-outline me-1"></i>Fecha y Hora<span class="sort-indicator"></span></th>
                            <th><i class="mdi mdi-swap-horizontal-bold me-1"></i>Tipo</th>
                            <th><i class="mdi mdi-check-decagram-outline me-1"></i>Dispositivo</th>
                            <th><i class="mdi mdi-list-status me-1"></i>Estado</th>
                            <th class="text-center"><i class="mdi mdi-cogs me-1"></i>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($registros as $registro)
                            <tr class="asistencia-row-item" data-estudiante="{{ optional($registro->usuario)->nombre_completo ?? '' }}" data-fecha="{{ \Carbon\Carbon::parse($registro->fecha_registro)->timestamp }}">
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm rounded-circle d-flex align-items-center justify-content-center me-2" data-bg-color>
                                            <span class="text-white fw-bold">{{ substr(optional($registro->usuario)->nombre ?? 'N', 0, 1) }}</span>
                                        </div>
                                        <div>
                                            <h6 class="mb-0 fs-14">{{ optional($registro->usuario)->nombre_completo ?? 'No encontrado' }}</h6>
                                            <small class="text-muted">DNI: {{ $registro->nro_documento }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ \Carbon\Carbon::parse($registro->fecha_registro)->format('d/m/Y h:i:s A') }}</td>
                                <td>{{ $registro->tipo_verificacion_texto }}</td>
                                <td>{{ $registro->sn_dispositivo ?: 'N/A' }}</td>
                                <td>
                                    <span class="badge {{ $registro->estado ? 'bg-success-lighten text-success' : 'bg-danger-lighten text-danger' }}">
                                        {{ $registro->estado ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    @if (Auth::user()->hasPermission('attendance.edit'))
                                        <a href="{{ route('asistencia.editar.form', $registro->id) }}" class="btn btn-warning btn-sm" title="Editar"><i class="mdi mdi-pencil"></i></a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center py-4"><i class="mdi mdi-archive-alert-outline fs-2 text-muted"></i><h5 class="mt-2">No se encontraron registros</h5></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($registros->hasPages())
                <div class="d-flex justify-content-end mt-3">{{ $registros->appends(request()->query())->onEachSide(1)->links('pagination::bootstrap-5') }}</div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // --- LÓGICA DE SUGERENCIAS DE BÚSQUEDA (ORIGINAL DEL USUARIO) ---
    const estudiantes = @json($usuarios);
    const searchInput = document.getElementById('documento');
    const suggestionsContainer = document.getElementById('suggestions');

    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        if (searchTerm.length < 2) {
            suggestionsContainer.style.display = 'none';
            return;
        }
        const filtered = estudiantes.filter(est => {
            const fullName = `${est.nombre} ${est.apellido_paterno}`.toLowerCase();
            return fullName.includes(searchTerm) || est.numero_documento.includes(searchTerm);
        });

        if (filtered.length === 0) {
            suggestionsContainer.style.display = 'none';
            return;
        }

        let html = '';
        filtered.slice(0, 10).forEach(est => {
            html += `<div class="suggestion-item" data-dni="${est.numero_documento}">${est.nombre} ${est.apellido_paterno} - ${est.numero_documento}</div>`;
        });
        suggestionsContainer.innerHTML = html;
        suggestionsContainer.style.display = 'block';

        document.querySelectorAll('.suggestion-item').forEach(item => {
            item.addEventListener('click', function() {
                searchInput.value = this.textContent.split(' - ')[1].trim(); // Get DNI part
                suggestionsContainer.style.display = 'none';
            });
        });
    });

    document.addEventListener('click', e => {
        if (!e.target.closest('.search-container')) {
            suggestionsContainer.style.display = 'none';
        }
    });
    
    // --- ANIMACIÓN DE CONTADORES ---
    ['#stat-total', '#stat-entradas', '#stat-salidas', '#stat-estudiantes'].forEach(id => {
        const el = document.querySelector(id);
        if(el) {
            const target = parseInt(el.textContent) || 0;
            let current = 0;
            const duration = 1500, stepTime = 20, steps = duration / stepTime, increment = target / steps;
            const timer = setInterval(() => {
                current += increment;
                if (current >= target) { current = target; clearInterval(timer); }
                el.textContent = Math.floor(current).toLocaleString('es-PE');
            }, stepTime);
        }
    });

    // --- LÓGICA DE ORDENAMIENTO DE TABLA ---
    document.querySelectorAll('.table-light th.sortable').forEach(headerCell => {
        headerCell.addEventListener('click', () => {
            const currentIsAsc = headerCell.getAttribute('data-sort-direction') === 'asc';
            const newDirection = currentIsAsc ? 'desc' : 'asc';
            document.querySelectorAll('.table-light th.sortable').forEach(th => th.removeAttribute('data-sort-direction'));
            headerCell.setAttribute('data-sort-direction', newDirection);
            
            const sortProperty = headerCell.dataset.sort;
            const tableBody = document.querySelector('tbody');
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

    // Auto-refresh (código original preservado)
    @if ($fecha === now()->format('Y-m-d'))
        setInterval(() => location.reload(), 30000);
    @endif
});
</script>
@endpush

