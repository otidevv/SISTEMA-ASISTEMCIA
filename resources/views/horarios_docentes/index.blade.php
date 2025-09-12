@extends('layouts.app')

@section('title', 'Gestión de Horarios Docentes')

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
    .tilebox-one .mdi { font-size: 3rem; transition: all 0.3s ease; }
    .tilebox-one:hover .mdi { transform: scale(1.2) rotate(-10deg); }
    .tilebox-one[style*="--"] { color: white; }
    .tilebox-one[style*="--"] .mdi { opacity: 0.3 !important; }
    .tilebox-one[style*="--"] h2, .tilebox-one[style*="--"] h6, .tilebox-one[style*="--"] p { color: white; }
    .tilebox-one[style*="--"] h6, .tilebox-one[style*="--"] p { opacity: 0.8; }
    .tilebox-one .text-muted { color: rgba(255, 255, 255, 0.8) !important; }

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
    .horario-row-item:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.08); background-color: #f8f9fe; }

    /* --- FILTROS Y SIDEBAR --- */
    .day-filter-badge { cursor: pointer; transition: all 0.2s ease; }
    .day-filter-badge:hover { transform: translateY(-1px); }
    .card-sidebar .card-header { background: linear-gradient(135deg, #f8f9fe 0%, #f1f3f9 100%); border-bottom: 1px solid #eef2f7; }
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
                        <li class="breadcrumb-item"><a href="{{ route('horarios-docentes.index') }}">Horarios</a></li>
                        <li class="breadcrumb-item active">Gestión</li>
                    </ol>
                </div>
                <h4 class="page-title">
                    <i class="mdi mdi-calendar-clock me-1"></i>
                    Gestión de Horarios
                    @if ($cicloSeleccionado)
                        <span class="badge bg-primary fs-6 ms-2">{{ $cicloSeleccionado->nombre }}</span>
                    @else
                        <span class="badge bg-warning fs-6 ms-2">Sin Ciclo Seleccionado</span>
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
                    <i class="mdi mdi-calendar-check float-end"></i>
                    <h6 class="text-uppercase mt-0">Total Horarios</h6>
                    <h2 class="my-2" id="totalHorarios">{{ $horarios->total() ?? 0 }}</h2>
                    <p class="mb-0">Registrados en este ciclo</p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card tilebox-one" style="background: var(--success-gradient);">
                <div class="card-body">
                    <i class="mdi mdi-account-multiple float-end"></i>
                    <h6 class="text-uppercase mt-0">Docentes Activos</h6>
                    <h2 class="my-2" id="docentesActivos">{{ isset($horarios) ? $horarios->unique('docente_id')->count() : 0 }}</h2>
                    <p class="mb-0">Impartiendo clases</p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card tilebox-one" style="background: var(--warning-gradient);">
                <div class="card-body">
                    <i class="mdi mdi-book-open-variant float-end"></i>
                    <h6 class="text-uppercase mt-0">Cursos Programados</h6>
                    <h2 class="my-2" id="cursosProgram">{{ isset($horarios) ? $horarios->unique('curso_id')->count() : 0 }}</h2>
                    <p class="mb-0">Cursos únicos con horario</p>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card tilebox-one" style="background: var(--primary-gradient);">
                <div class="card-body">
                    <i class="mdi mdi-door-open float-end"></i>
                    <h6 class="text-uppercase mt-0">Aulas en Uso</h6>
                    <h2 class="my-2" id="aulasUso">{{ isset($horarios) ? $horarios->unique('aula_id')->count() : 0 }}</h2>
                    <p class="mb-0">Espacios físicos asignados</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <!-- Filtro por Ciclo y Acciones -->
                    <div class="row align-items-center mb-3 bg-light p-2 rounded">
                        <div class="col-md-4">
                            <a href="{{ route('horarios-docentes.create') }}" class="btn btn-primary-gradient btn-sm">
                                <i class="mdi mdi-plus me-1"></i> Nuevo Horario
                            </a>
                        </div>
                        <div class="col-md-8">
                            <div class="d-flex flex-wrap gap-2 justify-content-md-end">
                                <form action="{{ route('horarios-docentes.index') }}" method="GET" class="d-flex align-items-center gap-2">
                                    <label for="ciclo_id" class="form-label mb-0 fw-bold text-primary flex-shrink-0">
                                        <i class="mdi mdi-calendar-sync me-1"></i>Ciclo:
                                    </label>
                                    <select name="ciclo_id" id="ciclo_id" class="form-select form-select-sm" style="width: auto;" onchange="this.form.submit()">
                                        @foreach ($ciclos as $ciclo)
                                            <option value="{{ $ciclo->id }}" {{ $cicloSeleccionado && $cicloSeleccionado->id == $ciclo->id ? 'selected' : '' }}>
                                                {{ $ciclo->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                </form>
                                <a href="{{ route('horarios.calendario') }}" class="btn btn-success btn-sm">
                                    <i class="mdi mdi-calendar-week me-1"></i>
                                    <span>Vista Calendario</span>
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Búsqueda y Filtros de la Tabla -->
                    <div class="row mb-3">
                         <div class="col-md-4">
                            <input type="text" class="form-control form-control-sm" id="horario_search" placeholder="Buscar por docente, curso, aula...">
                        </div>
                        <div class="col-md-4">
                            <select class="form-select form-select-sm" id="filtroTipo">
                                <option value="">Filtrar por tipo</option>
                                <option value="teoria">Teoría</option>
                                <option value="practica">Práctica</option>
                                <option value="laboratorio">Laboratorio</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <select class="form-select form-select-sm" id="filtroDia">
                                <option value="">Filtrar por día</option>
                                @php
                                    $dias = ['lunes' => 'Lunes', 'martes' => 'Martes', 'miercoles' => 'Miércoles', 'jueves' => 'Jueves', 'viernes' => 'Viernes', 'sabado' => 'Sábado', 'domingo' => 'Domingo'];
                                @endphp
                                @foreach($dias as $diaKey => $diaNombre)
                                    <option value="{{ $diaKey }}">{{ $diaNombre }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <!-- Vista Tabla -->
                    <div class="table-responsive">
                        <table class="table table-hover table-centered mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="sortable" data-sort="docente"><i class="mdi mdi-account-tie-outline me-1"></i>Docente<span class="sort-indicator"></span></th>
                                    <th class="sortable" data-sort="curso"><i class="mdi mdi-book-open-outline me-1"></i>Curso<span class="sort-indicator"></span></th>
                                    <th><i class="mdi mdi-clock-outline me-1"></i>Horario</th>
                                    <th class="sortable" data-sort="aula"><i class="mdi mdi-school-outline me-1"></i>Aula<span class="sort-indicator"></span></th>
                                    <th class="text-center"><i class="mdi mdi-cogs me-1"></i>Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="horariosTableBody">
                                @forelse($horarios ?? [] as $horario)
                                <tr class="horario-row-item"
                                    data-docente="{{ $horario->docente->nombre_completo ?? '' }}"
                                    data-curso="{{ $horario->curso->nombre ?? '' }}"
                                    data-aula="{{ $horario->aula->nombre ?? '' }}"
                                    data-tipo="{{ strtolower($horario->tipo ?? '') }}"
                                    data-dia="{{ strtolower($horario->dia_semana ?? '') }}">
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm rounded-circle d-flex align-items-center justify-content-center me-2" data-bg-color>
                                                <span class="text-white fw-bold">{{ substr($horario->docente->nombre_completo ?? 'N', 0, 1) }}</span>
                                            </div>
                                            <div>
                                                <h6 class="mb-0 fs-14">{{ $horario->docente->nombre_completo ?? 'Sin asignar' }}</h6>
                                                <small class="text-muted">{{ $horario->docente->especialidad ?? '' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <h6 class="mb-0 fs-14">{{ $horario->curso->nombre ?? 'Sin curso' }}</h6>
                                        <span class="badge bg-primary-lighten text-primary">{{ ucfirst($horario->tipo ?? 'N/A') }}</span>
                                    </td>
                                    <td>
                                        <span class="fw-bold">{{ ucfirst($horario->dia_semana ?? '') }}</span><br>
                                        <small class="text-muted">{{ \Carbon\Carbon::parse($horario->hora_inicio)->format('h:i A') }} - {{ \Carbon\Carbon::parse($horario->hora_fin)->format('h:i A') }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-info-lighten text-info fs-12"><i class="mdi mdi-door-open me-1"></i>{{ $horario->aula->nombre ?? 'N/A' }}</span>
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('horarios-docentes.edit', $horario->id) }}" class="btn btn-warning btn-sm" title="Editar"><i class="mdi mdi-pencil"></i></a>
                                        <form action="{{ route('horarios-docentes.delete', $horario->id) }}" method="POST" class="d-inline form-delete">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm" title="Eliminar"><i class="mdi mdi-delete"></i></button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5"><i class="mdi mdi-calendar-remove fs-2 text-muted"></i><h5 class="mt-2">No hay horarios en este ciclo</h5></td>
                                </tr>
                                @endforelse
                                <tr id="no-results-row" style="display: none;">
                                    <td colspan="5" class="text-center py-4"><i class="mdi mdi-magnify-close fs-2 text-muted"></i><h5 class="mt-2">No se encontraron resultados</h5></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // --- INICIALIZACIÓN ---
    const searchInput = document.getElementById('horario_search');
    const tipoFilter = document.getElementById('filtroTipo');
    const diaFilter = document.getElementById('filtroDia');
    const tableBody = document.getElementById('horariosTableBody');
    const allRows = Array.from(tableBody.querySelectorAll('tr.horario-row-item'));
    const noResultsRow = document.getElementById('no-results-row');

    // --- ANIMACIÓN DE CONTADORES ---
    function animateCounter(element) {
        const target = parseInt(element.textContent.replace(/S\/|\s|,/g, '')) || 0;
        let current = 0;
        const duration = 1500;
        const stepTime = 20;
        const steps = duration / stepTime;
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
    document.querySelectorAll('#totalHorarios, #docentesActivos, #cursosProgram, #aulasUso').forEach(animateCounter);

    // --- LÓGICA DE FILTRADO Y BÚSQUEDA ---
    function applyFilters() {
        const searchTerm = searchInput.value.toLowerCase();
        const tipoTerm = tipoFilter.value.toLowerCase();
        const diaTerm = diaFilter.value.toLowerCase();
        let visibleCount = 0;

        allRows.forEach(row => {
            const docente = (row.dataset.docente || '').toLowerCase();
            const curso = (row.dataset.curso || '').toLowerCase();
            const aula = (row.dataset.aula || '').toLowerCase();
            const tipo = (row.dataset.tipo || '').toLowerCase();
            const dia = (row.dataset.dia || '').toLowerCase();
            
            const matchesSearch = !searchTerm || docente.includes(searchTerm) || curso.includes(searchTerm) || aula.includes(searchTerm);
            const matchesTipo = !tipoTerm || tipo === tipoTerm;
            const matchesDia = !diaTerm || dia === diaTerm;

            if (matchesSearch && matchesTipo && matchesDia) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });
        
        noResultsRow.style.display = visibleCount === 0 ? '' : 'none';
    }

    searchInput.addEventListener('keyup', applyFilters);
    tipoFilter.addEventListener('change', applyFilters);
    diaFilter.addEventListener('change', applyFilters);

    // --- LÓGICA DE ORDENAMIENTO DE TABLA ---
    document.querySelectorAll('.table-light th.sortable').forEach(headerCell => {
        headerCell.addEventListener('click', () => {
            const currentIsAsc = headerCell.getAttribute('data-sort-direction') === 'asc';
            const newDirection = currentIsAsc ? 'desc' : 'asc';

            document.querySelectorAll('.table-light th.sortable').forEach(th => th.removeAttribute('data-sort-direction'));
            headerCell.setAttribute('data-sort-direction', newDirection);

            const sortProperty = headerCell.dataset.sort;

            allRows.sort((a, b) => {
                const valA = (a.dataset[sortProperty] || '').toLowerCase();
                const valB = (b.dataset[sortProperty] || '').toLowerCase();
                if (valA < valB) return newDirection === 'asc' ? -1 : 1;
                if (valA > valB) return newDirection === 'asc' ? 1 : -1;
                return 0;
            }).forEach(row => tableBody.appendChild(row));
        });
    });

    // --- MANEJO DE CONFIRMACIÓN DE ELIMINACIÓN CON SWEETALERT ---
    document.querySelectorAll('.form-delete').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault(); // Prevenir el envío inmediato

            Swal.fire({
                title: '¿Estás seguro?',
                text: "¡No podrás revertir esta acción!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, ¡eliminar!',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.submit(); // Enviar el formulario si se confirma
                }
            });
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