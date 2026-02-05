@extends('layouts.app')

@section('title', 'Gestión de Pagos a Docentes')

@push('css')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css"
        rel="stylesheet" />
    <style>
        .table-responsive { border-radius: 0.5rem; }
        .stat-card {
            border: none;
            border-bottom: 3px solid transparent;
            transition: all 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .stat-card.primary { border-bottom-color: #5369f8; }
        .stat-card.success { border-bottom-color: #43d39e; }
        .stat-card.info { border-bottom-color: #25c2e3; }
        
        .avatar-title.bg-soft-primary { background-color: rgba(83, 105, 248, 0.1); color: #5369f8; }
        .filter-section {
            background-color: #f9f9f9;
            border: 1px solid #eef2f7;
        }
        .table-centered td { vertical-align: middle !important; }
        .row-hover:hover { background-color: #f8f9fa; }

        /* Custom Swal Styles for Professionalism */
        .swal2-popup.swal-premium {
            border-radius: 12px !important;
            padding: 2rem !important;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1) !important;
        }
        .swal2-title {
            font-size: 1.5rem !important;
            font-weight: 700 !important;
            margin-bottom: 1.5rem !important;
        }
        .select2-container--bootstrap-5 .select2-dropdown {
            z-index: 10000 !important;
            border-radius: 8px !important;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1) !important;
        }
        .swal2-html-container {
            z-index: 1 !important;
            overflow: visible !important;
            padding: 0 !important;
            margin: 0 !important;
            text-align: left !important;
        }
        .swal2-popup.swal-premium {
            overflow: visible !important;
            border-radius: 20px !important;
            padding: 2.5rem !important;
            box-shadow: 0 15px 50px rgba(0,0,0,0.2) !important;
        }
        
        /* Scoped Select2 for Swal to avoid displacement */
        .swal2-container .select2-container {
            z-index: 10005 !important;
            width: 100% !important;
        }
        .swal2-container .select2-container--bootstrap-5 .select2-dropdown {
            border-color: #5369f8 !important;
            box-shadow: 0 10px 25px rgba(83, 105, 248, 0.2) !important;
            z-index: 10006 !important;
        }
        .swal2-container .select2-selection {
            border-radius: 8px !important;
            height: calc(1.5em + 1rem + 2px) !important;
            padding: 0.5rem 0.75rem !important;
            border: 1px solid #dee2e6 !important;
            transition: all 0.2s ease-in-out !important;
        }
        .swal2-container .select2-selection--single .select2-selection__rendered {
            line-height: inherit !important;
            padding-left: 0 !important;
        }
        .swal2-container .select2-selection:focus, .swal2-container .select2-container--open .select2-selection {
            border-color: #5369f8 !important;
            box-shadow: 0 0 0 0.2rem rgba(83, 105, 248, 0.1) !important;
        }

        .swal-form-group {
            position: relative;
            margin-bottom: 1.8rem;
        }
        .swal-form-group label {
            display: block;
            font-weight: 700;
            color: #3d4d5d;
            margin-bottom: 0.7rem;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .swal-footer {
            margin-top: 2.5rem;
            padding-top: 2rem;
            border-top: 1px solid #f0f4f8;
            display: flex;
            justify-content: flex-end;
            gap: 15px;
        }
        .swal-header-icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, #f0f3ff 0%, #e8edff 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: -0.5rem auto 1.5rem;
            box-shadow: 0 5px 15px rgba(83,105,248,0.1);
        }
        .swal-header-icon i {
            font-size: 32px;
            color: #5369f8;
        }
        .swal-info-box {
            background: #f8fbff;
            border: 1px solid #e0e9f5;
            padding: 1rem 1.25rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
        }

        /* Modern Pagination Styling */
        .pagination {
            display: flex;
            padding-left: 0;
            list-style: none;
            justify-content: center;
            gap: 5px;
            margin-bottom: 0 !important;
        }
        .page-item .page-link {
            border: none !important;
            padding: 0.6rem 0.9rem;
            border-radius: 8px !important;
            color: #5d6d7e !important;
            font-weight: 600;
            background: #f1f4f7 !important;
            transition: all 0.3s ease;
            font-size: 0.75rem;
            min-width: 38px;
            text-align: center;
        }
        .page-item.active .page-link {
            background: #5369f8 !important;
            color: #fff !important;
            box-shadow: 0 5px 15px rgba(83, 105, 248, 0.3);
        }
        .page-item:not(.active):hover .page-link {
            background: #e8edff !important;
            color: #5369f8 !important;
        }
        .page-item.disabled .page-link {
            background: #f8f9fa !important;
            opacity: 0.5;
            color: #aab4bc !important;
        }
    </style>
@endpush

@push('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.select2').select2({
                theme: 'bootstrap-5'
            });

            // Filtros Dinámicos (Servidor)
            $('#cycleFilter').on('change', function() {
                $('#filterForm').submit();
            });

            // Live Search con Debounce
            let searchTimer;
            $('#teacherSearch').on('input', function() {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(() => {
                    $('#filterForm').submit();
                }, 600);
            });

            // Prevenir submit al presionar Enter (ya se maneja por input)
            $('#teacherSearch').on('keypress', function(e) {
                if (e.which == 13) e.preventDefault();
            });

            // Filtros de Cliente (Estado)
            $('input[name="statusFilter"]').on('change', filterTable);

            function filterTable() {
                const statusTerm = $('input[name="statusFilter"]:checked').val();

                $('tbody tr').each(function() {
                    const row = $(this);
                    if (row.find('td').length < 2) return;

                    const statusBadge = row.find('.badge').text().trim().toLowerCase();
                    let show = true;

                    if (statusTerm === 'active' && !statusBadge.includes('activo')) show = false;
                    if (statusTerm === 'finalized' && !statusBadge.includes('finalizado')) show = false;

                    row.toggle(show);
                });
            }

            // --- Lógica SweetAlert2 para Registro ---
            window.openCreateModal = function() {
                const html = document.getElementById('createTemplate').innerHTML;
                Swal.fire({
                    html: html,
                    showConfirmButton: false,
                    showCancelButton: false,
                    customClass: {
                        popup: 'swal-premium'
                    },
                    buttonsStyling: false,
                    width: '650px',
                    grow: false,
                    position: 'center',
                    backdrop: `rgba(45, 55, 72, 0.45)`,
                    showClass: {
                        popup: 'animate__animated animate__fadeInDown animate__faster'
                    },
                    hideClass: {
                        popup: 'animate__animated animate__fadeOutUp animate__faster'
                    },
                    didOpen: () => {
                        const content = Swal.getHtmlContainer();
                        
                        // Initialize Select2 after a safe delay
                        setTimeout(() => {
                            $(content).find('.select2-trigger').select2({
                                dropdownParent: $(content),
                                theme: 'bootstrap-5',
                                width: '100%'
                            });
                        }, 250);
                        // Close on cancel click
                        $(content).find('.swal-cancel').on('click', () => Swal.close());

                        // Autocomplete tarifa logic
                        $(content).find('#docente_id').on('change', function() {
                            const docenteId = $(this).val();
                            if (docenteId) {
                                fetch(`{{ url('api/v1/pagos-docentes/ultima-tarifa') }}/${docenteId}`)
                                    .then(response => response.json())
                                    .then(data => {
                                        if (data.tarifa) {
                                            $(content).find('#tarifa_por_hora').val(data.tarifa).addClass('is-valid');
                                            toastr.success('Tarifa cargada.', 'Optimizado');
                                        }
                                    });
                            }
                        });
                    }
                });
            };

            // --- Lógica SweetAlert2 para Edición ---
            $('.edit-pago-btn').on('click', function() {
                const id = $(this).data('id');
                const docenteId = $(this).data('docente-id');
                const docente = $(this).data('docente');
                const cicloId = $(this).data('ciclo');
                const tarifa = $(this).data('tarifa');
                const inicio = $(this).data('inicio');
                const fin = $(this).data('fin');

                let html = document.getElementById('editTemplate').innerHTML;
                
                Swal.fire({
                    html: html,
                    showConfirmButton: false,
                    showCancelButton: false,
                    customClass: {
                        popup: 'swal-premium'
                    },
                    buttonsStyling: false,
                    width: '650px',
                    grow: false,
                    position: 'center',
                    backdrop: `rgba(45, 55, 72, 0.45)`,
                    showClass: {
                        popup: 'animate__animated animate__fadeInDown animate__faster'
                    },
                    hideClass: {
                        popup: 'animate__animated animate__fadeOutUp animate__faster'
                    },
                    didOpen: () => {
                        const content = Swal.getHtmlContainer();
                        
                        // Populate values first
                        $(content).find('#edit_form_swal').attr('action', `{{ url('pagos-docentes') }}/${id}`);
                        $(content).find('#edit_docente_id').val(docenteId);
                        $(content).find('#edit_docente_name').text(docente);
                        $(content).find('#edit_ciclo_id').val(cicloId);
                        $(content).find('#edit_tarifa_por_hora').val(tarifa);
                        $(content).find('#edit_fecha_inicio').val(inicio);
                        $(content).find('#edit_fecha_fin').val(fin || '');

                        // Initialize Select2 after delay
                        setTimeout(() => {
                            $(content).find('.select2-trigger').select2({
                                dropdownParent: $(content),
                                theme: 'bootstrap-5',
                                width: '100%'
                            });
                        }, 250);

                         // Close on cancel click
                         $(content).find('.swal-cancel').on('click', () => Swal.close());
                    }
                });
            });
        });

        function showDeleteConfirmation(id) {
            if (confirm('¿Está seguro de que desea eliminar este registro de pago?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `{{ url('pagos-docentes') }}/${id}`;
                form.innerHTML = `
                    @csrf
                    @method('DELETE')
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
@endpush

@section('content')
    <!-- start page title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">Gestión de Pagos</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Pagos Docentes</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <!-- end page title -->

    <!-- Quick Stats -->
    <div class="row">
        <div class="col-md-6 col-xl-4">
            <div class="card stat-card primary">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-6">
                            <h5 class="text-muted fw-normal mt-0 text-truncate" title="Total Docentes">Total Personal</h5>
                            <h3 class="my-2 py-1">{{ $stats['total'] }}</h3>
                            <p class="mb-0 text-muted">
                                <span class="text-primary me-2"><i class="mdi mdi-account-group"></i></span>
                                <span class="text-nowrap">Registros históricos</span>
                            </p>
                        </div>
                        <div class="col-6">
                            <div class="text-end">
                                <i class="mdi mdi-account-card-details-outline display-4 text-primary opacity-25"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-4">
            <div class="card stat-card success">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-6">
                            <h5 class="text-muted fw-normal mt-0 text-truncate" title="Pagos Activos">Pagos Vigentes</h5>
                            <h3 class="my-2 py-1 text-success">{{ $stats['activos'] }}</h3>
                            <p class="mb-0 text-muted">
                                <span class="text-success me-2"><i class="mdi mdi-check-decagram"></i></span>
                                <span class="text-nowrap">En curso este ciclo</span>
                            </p>
                        </div>
                        <div class="col-6">
                            <div class="text-end">
                                <i class="mdi mdi-cash-check display-4 text-success opacity-25"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-4">
            <div class="card stat-card info">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-6">
                            <h5 class="text-muted fw-normal mt-0 text-truncate" title="Tarifa Media">Inversión Media</h5>
                            <h3 class="my-2 py-1 text-info">S/ {{ number_format($stats['promedio'], 2) }}</h3>
                            <p class="mb-0 text-muted">
                                <span class="text-info me-2"><i class="mdi mdi-trending-up"></i></span>
                                <span class="text-nowrap">Promedio por hora</span>
                            </p>
                        </div>
                        <div class="col-6">
                            <div class="text-end">
                                <i class="mdi mdi-currency-usd display-4 text-info opacity-25"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row mb-2">
                        <div class="col-sm-4">
                            <h4 class="header-title mt-0 mb-1">Listado de Registros</h4>
                        </div>
                        <div class="col-sm-8">
                            <div class="text-sm-end">
                                <button type="button" class="btn btn-primary mb-2 shadow-sm" onclick="openCreateModal()">
                                    <i class="mdi mdi-plus-circle me-1"></i> Registrar Contrato
                                </button>
                            </div>
                        </div>
                    </div>

                    @if (session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <!-- Seccion de Filtros -->
                    <form action="{{ route('pagos-docentes.index') }}" method="GET" id="filterForm">
                        <div class="filter-section p-3 rounded-3 mb-4">
                            <div class="row align-items-end">
                                <div class="col-lg-4 mb-lg-0 mb-3">
                                    <label class="form-label fw-bold text-muted small text-uppercase">Búsqueda Inteligente</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white"><i class="mdi mdi-magnify text-primary"></i></span>
                                        <input type="text" name="search" id="teacherSearch" class="form-control" placeholder="Nombre completo o DNI..." value="{{ request('search') }}">
                                    </div>
                                </div>
                                <div class="col-lg-3 mb-lg-0 mb-3">
                                    <label class="form-label fw-bold text-muted small text-uppercase">Ciclo Académico</label>
                                    <select name="ciclo_id" id="cycleFilter" class="form-select select2">
                                        <option value="all" {{ ($cicloSeleccionado === null) ? 'selected' : '' }}>Todos los Periodos</option>
                                        <option value="none" {{ ($cicloSeleccionado === 'none') ? 'selected' : '' }}>Sin Asignación</option>
                                        @foreach($ciclos as $ciclo)
                                            <option value="{{ $ciclo->id }}" {{ (isset($cicloSeleccionado->id) && $cicloSeleccionado->id == $ciclo->id) ? 'selected' : '' }}>
                                                {{ $ciclo->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-lg-5 text-lg-end">
                                    <label class="form-label fw-bold text-muted small text-uppercase d-block text-lg-start ps-lg-2">Estado de Registro</label>
                                    <div class="btn-group w-100" role="group">
                                        <input type="radio" class="btn-check" name="statusFilter" id="statusAll" value="all" checked>
                                        <label class="btn btn-outline-primary" for="statusAll">Todos</label>
    
                                        <input type="radio" class="btn-check" name="statusFilter" id="statusActive" value="active">
                                        <label class="btn btn-outline-primary" for="statusActive">En Curso</label>
    
                                        <input type="radio" class="btn-check" name="statusFilter" id="statusFinalized" value="finalized">
                                        <label class="btn btn-outline-primary" for="statusFinalized">Finalizados</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table class="table table-centered table-nowrap mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Docente</th>
                                    <th>Tarifa/Hora</th>
                                    <th>Ciclo</th>
                                    <th>Inicio</th>
                                    <th>Fin</th>
                                    <th>Estado</th>
                                    <th style="width: 125px;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pagos as $pago)
                                    <tr class="row-hover" data-teacher="{{ ($pago->docente->nombre_completo ?? '') . ' ' . ($pago->docente->numero_documento ?? '') }}">
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm me-3">
                                                    <span class="avatar-title bg-soft-primary text-primary rounded-circle fw-bold">
                                                        {{ substr($pago->docente->nombre ?? 'N', 0, 1) }}
                                                    </span>
                                                </div>
                                                <div>
                                                    <h5 class="font-size-15 my-0 fw-bold">{{ $pago->docente->nombre_completo ?? 'N/A' }}</h5>
                                                    <p class="text-muted mb-0 font-size-12"><i class="mdi mdi-account-outline me-1"></i>{{ $pago->docente->numero_documento ?? '---' }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="fw-bold text-dark">S/ {{ number_format($pago->tarifa_por_hora, 2) }}</td>
                                        <td>
                                            <span class="badge bg-light text-dark border">{{ $pago->ciclo->nombre ?? 'Sin Ciclo' }}</span>
                                        </td>
                                        <td class="text-muted">{{ \Carbon\Carbon::parse($pago->fecha_inicio)->format('d/m/Y') }}</td>
                                        <td class="text-muted">{{ $pago->fecha_fin ? \Carbon\Carbon::parse($pago->fecha_fin)->format('d/m/Y') : 'Vigente' }}</td>
                                        <td>
                                            @if($pago->fecha_fin)
                                                <span class="badge bg-soft-secondary text-secondary px-2">Finalizado</span>
                                            @else
                                                <span class="badge bg-soft-success text-success px-2">Activo</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="javascript:void(0);" 
                                               class="action-icon text-primary me-2 edit-pago-btn"
                                               data-id="{{ $pago->id }}"
                                               data-docente-id="{{ $pago->docente_id }}"
                                               data-docente="{{ $pago->docente->nombre_completo }}"
                                               data-ciclo="{{ $pago->ciclo_id }}"
                                               data-tarifa="{{ $pago->tarifa_por_hora }}"
                                               data-inicio="{{ \Carbon\Carbon::parse($pago->fecha_inicio)->format('Y-m-d') }}"
                                               data-fin="{{ $pago->fecha_fin ? \Carbon\Carbon::parse($pago->fecha_fin)->format('Y-m-d') : '' }}"> 
                                                <i class="mdi mdi-pencil font-size-18"></i>
                                            </a>
                                            <a href="javascript:void(0);" onclick="showDeleteConfirmation({{ $pago->id }})" class="action-icon text-danger"> 
                                                <i class="mdi mdi-delete font-size-18"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5">
                                            <div class="text-muted">
                                                <i class="mdi mdi-database-off-outline display-3 opacity-25"></i>
                                                <p class="mt-3 font-size-16">No se encontraron registros de pago asociados.</p>
                                                <a href="{{ route('pagos-docentes.create') }}" class="btn btn-sm btn-outline-primary mt-2">
                                                    Click aquí para registrar el primero
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-4 bg-light p-3 rounded-3 border-0">
                        <div class="text-muted small fw-bold">
                            <i class="mdi mdi-information-outline me-1"></i>
                            Mostrando {{ $pagos->firstItem() ?? 0 }} a {{ $pagos->lastItem() ?? 0 }} de {{ $pagos->total() }} registros
                        </div>
                        <div class="pagination-container">
                            {{ $pagos->links('pagination::bootstrap-5') }}
                        </div>
                    </div>

                </div> <!-- end card body-->
            </div> <!-- end card -->
        </div><!-- end col-->
    </div>
    </div>

    <!-- TEMPLATES: SWEETALERT2 FORMS -->
    <template id="createTemplate">
        <div class="swal-header-icon mb-4">
            <i class="mdi mdi-plus-circle-outline"></i>
        </div>
        <h3 class="text-center mb-1 fw-bold text-dark">Nuevo Contrato</h3>
        <p class="text-center text-muted small mb-4 px-4">Defina los parámetros financieros para el docente seleccionado.</p>
        
        <form action="{{ route('pagos-docentes.store') }}" method="POST" id="create_form_swal" class="text-start px-3">
            @csrf
            <div class="swal-form-group">
                <label>Docente Responsable</label>
                <select name="docente_id" id="docente_id" class="form-select select2-trigger" required>
                    <option value="">Buscar en la base de datos...</option>
                    @foreach($docentes as $docente)
                        <option value="{{ $docente->id }}">{{ $docente->nombre }} {{ $docente->apellido_paterno }}</option>
                    @endforeach
                </select>
            </div>
            <div class="row">
                <div class="col-7">
                    <div class="swal-form-group">
                        <label>Ciclo Académico</label>
                        <select name="ciclo_id" class="form-select select2-trigger" required>
                            @foreach($ciclos as $ciclo)
                                <option value="{{ $ciclo->id }}" {{ (isset($cicloActivo) && $cicloActivo->id == $ciclo->id) ? 'selected' : '' }}>
                                    {{ $ciclo->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-5">
                    <div class="swal-form-group">
                        <label>Tarifa Hora</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 fw-bold text-primary">S/</span>
                            <input type="number" step="0.01" name="tarifa_por_hora" id="tarifa_por_hora" class="form-control border-start-0" placeholder="0.00" required>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="swal-info-box d-flex align-items-center">
                <i class="mdi mdi-auto-fix font-size-22 me-3 text-primary animate__animated animate__pulse animate__infinite"></i>
                <div class="small">
                    <strong class="text-primary d-block">Optimización Activa</strong>
                    <span class="text-muted">La última tarifa del docente se cargará automáticamente.</span>
                </div>
            </div>

            <div class="swal-footer">
                <button type="button" class="btn btn-light px-4 swal-cancel fw-bold py-2">Descartar</button>
                <button type="submit" class="btn btn-primary px-4 shadow-sm fw-bold py-2">Crear Registro</button>
            </div>
        </form>
    </template>

    <template id="editTemplate">
        <div class="swal-header-icon mb-4" style="background: linear-gradient(135deg, #fffcf0 0%, #fff8e1 100%);">
            <i class="mdi mdi-pencil-outline" style="color: #ffb300;"></i>
        </div>
        <h3 class="text-center mb-1 fw-bold text-dark">Editar Parámetros</h3>
        <p class="text-center text-muted small mb-4 px-4">Actualice la configuración financiera y fechas vigentes.</p>

        <form method="POST" id="edit_form_swal" class="text-start px-3">
            @csrf
            @method('PUT')
            
            <input type="hidden" name="docente_id" id="edit_docente_id">

            <div class="alert alert-soft-info border-0 mb-4 py-3 d-flex align-items-center rounded-3 bg-light">
                <div class="avatar-sm me-3 border border-info rounded-circle d-flex align-items-center justify-content-center bg-white">
                    <i class="mdi mdi-account text-info font-size-18"></i>
                </div>
                <div class="flex-grow-1">
                    <span class="small text-muted d-block text-uppercase fw-bold" style="font-size: 10px; letter-spacing: 0.5px;">Gestionando ficha de</span>
                    <strong id="edit_docente_name" class="text-dark font-size-16"></strong>
                </div>
            </div>

            <div class="row">
                <div class="col-7">
                    <div class="swal-form-group">
                        <label>Periodo Académico</label>
                        <select name="ciclo_id" id="edit_ciclo_id" class="form-select select2-trigger" required>
                            @foreach($ciclos as $ciclo)
                                <option value="{{ $ciclo->id }}">{{ $ciclo->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-5">
                    <div class="swal-form-group">
                        <label>Tarifa Hora</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 fw-bold text-warning">S/</span>
                            <input type="number" step="0.01" name="tarifa_por_hora" id="edit_tarifa_por_hora" class="form-control border-start-0" required>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-6">
                    <div class="swal-form-group">
                        <label>Fecha de Inicio</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="mdi mdi-calendar"></i></span>
                            <input type="date" name="fecha_inicio" id="edit_fecha_inicio" class="form-control border-start-0" required>
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="swal-form-group">
                        <label>Fecha de Término</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="mdi mdi-calendar-check"></i></span>
                            <input type="date" name="fecha_fin" id="edit_fecha_fin" class="form-control border-start-0">
                        </div>
                    </div>
                </div>
            </div>

            <div class="swal-footer">
                <button type="button" class="btn btn-light px-4 swal-cancel fw-bold py-2">Cancelar</button>
                <button type="submit" class="btn btn-warning px-4 shadow-sm fw-bold py-2">Guardar Cambios</button>
            </div>
        </form>
    </template>
@endsection
