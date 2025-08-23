@extends('layouts.app')

@section('title', 'Gestión de Carnets')

@push('css')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <style>
        .badge-estado-activo { background-color: #28a745; color: #fff; }
        .badge-estado-inactivo { background-color: #6c757d; color: #fff; }
        .badge-estado-vencido { background-color: #ffc107; color: #000; }
        .badge-estado-anulado { background-color: #dc3545; color: #fff; }
        .carnet-preview {
            border: 1px solid #dee2e6;
            border-radius: 10px;
            padding: 15px;
            background: #f8f9fa;
        }
        .stat-card {
            transition: transform 0.3s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
    </style>
@endpush

@push('js')
    <script>
        window.default_server = "{{ url('/') }}";
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="{{ asset('js/carnets/index.js') }}"></script>
@endpush

@section('content')
    <!-- start page title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">Gestión de Carnets</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Carnets</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <!-- end page title -->

    <!-- Estadísticas rápidas -->
    <div class="row mb-3">
        <div class="col-md-3">
            <div class="card stat-card bg-primary text-white">
                <div class="card-body p-3">
                    <h5 class="mb-1">Total Carnets</h5>
                    <h3 id="stat-total">0</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card bg-success text-white">
                <div class="card-body p-3">
                    <h5 class="mb-1">Activos</h5>
                    <h3 id="stat-activos">0</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card bg-warning text-white">
                <div class="card-body p-3">
                    <h5 class="mb-1">Pendientes Impresión</h5>
                    <h3 id="stat-pendientes">0</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card bg-info text-white">
                <div class="card-body p-3">
                    <h5 class="mb-1">Impresos</h5>
                    <h3 id="stat-impresos">0</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <!-- Botones de acción -->
                    <div class="row mb-3">
                        <div class="col-md-12">
                            @if(Auth::user()->hasPermission('carnets.generate'))
                            <button type="button" class="btn btn-success" id="btn-generar-masivo">
                                <i class="uil uil-plus-circle me-1"></i> Generar Carnets Masivos
                            </button>
                            @endif
                            
                            @if(Auth::user()->hasPermission('carnets.create'))
                            <button type="button" class="btn btn-primary" id="btn-generar-individual">
                                <i class="uil uil-id-card me-1"></i> Generar Individual
                            </button>
                            @endif
                            
                            @if(Auth::user()->hasPermission('carnets.export'))
                            <button type="button" class="btn btn-info" id="btn-exportar-pdf" disabled>
                                <i class="uil uil-file-pdf me-1"></i> Exportar Seleccionados
                            </button>
                            @endif
                            
                            @if(Auth::user()->hasPermission('carnets.mark_printed'))
                            <button type="button" class="btn btn-warning" id="btn-marcar-impresos" disabled>
                                <i class="uil uil-print me-1"></i> Marcar como Impresos
                            </button>
                            @endif
                        </div>
                    </div>

                    <!-- Filtros -->
                    <div class="row mb-3">
                        <div class="col-md-2">
                            <label for="filter-ciclo">Ciclo:</label>
                            <select id="filter-ciclo" class="form-select">
                                <option value="">Todos</option>
                                @foreach($ciclos as $ciclo)
                                    <option value="{{ $ciclo->id }}" {{ $ciclo->es_activo ? 'selected' : '' }}>
                                        {{ $ciclo->nombre }} {{ $ciclo->es_activo ? '(Activo)' : '' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="filter-carrera">Carrera:</label>
                            <select id="filter-carrera" class="form-select">
                                <option value="">Todas</option>
                                @foreach($carreras as $carrera)
                                    <option value="{{ $carrera->id }}">{{ $carrera->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="filter-turno">Turno:</label>
                            <select id="filter-turno" class="form-select">
                                <option value="">Todos</option>
                                @foreach($turnos as $turno)
                                    <option value="{{ $turno->id }}">{{ $turno->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="filter-aula">Aula:</label>
                            <select id="filter-aula" class="form-select">
                                <option value="">Todas</option>
                                @foreach($aulas as $aula)
                                    <option value="{{ $aula->id }}">{{ $aula->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="filter-estado">Estado:</label>
                            <select id="filter-estado" class="form-select">
                                <option value="">Todos</option>
                                <option value="activo">Activo</option>
                                <option value="inactivo">Inactivo</option>
                                <option value="vencido">Vencido</option>
                                <option value="anulado">Anulado</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="filter-impreso">Impresión:</label>
                            <select id="filter-impreso" class="form-select">
                                <option value="">Todos</option>
                                <option value="1">Impresos</option>
                                <option value="0">No impresos</option>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-2">
                        <div class="col-md-12">
                            <button type="button" class="btn btn-primary" id="btn-filtrar">
                                <i class="mdi mdi-filter me-1"></i> Filtrar
                            </button>
                            <button type="button" class="btn btn-secondary" id="btn-limpiar-filtros">
                                <i class="mdi mdi-filter-remove me-1"></i> Limpiar Filtros
                            </button>
                        </div>
                    </div>

                    <!-- Tabla de carnets -->
                    <div class="row">
                        <div class="col-12">
                            <h4 class="header-title mt-3 mb-3">Lista de Carnets</h4>
                            
                            <table id="carnets-datatable" class="table dt-responsive nowrap w-100">
                                <thead>
                                    <tr>
                                        <th>
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="select-all">
                                                <label class="form-check-label" for="select-all"></label>
                                            </div>
                                        </th>
                                        <th>Código</th>
                                        <th>Estudiante</th>
                                        <th>DNI</th>
                                        <th>Ciclo</th>
                                        <th>Carrera</th>
                                        <th>Turno</th>
                                        <th>Aula</th>
                                        <th>Emisión</th>
                                        <th>Vencimiento</th>
                                        <th>Estado</th>
                                        <th>Impreso</th>
                                        <th>Acciones</th>
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
@endsection

@push('modals')
    <!-- Modal para generar carnets masivos -->
    <div class="modal fade" id="generarMasivoModal" tabindex="-1" role="dialog" aria-labelledby="generarMasivoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="generarMasivoModalLabel">Generar Carnets Masivos</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="generarMasivoForm">
                        <div class="alert alert-info">
                            <i class="uil uil-info-circle"></i> 
                            Se generarán carnets para todos los estudiantes inscritos que coincidan con los filtros seleccionados.
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="masivo-ciclo" class="form-label">Ciclo <span class="text-danger">*</span></label>
                                    <select class="form-select" id="masivo-ciclo" name="ciclo_id" required>
                                        <option value="">Seleccione un ciclo</option>
                                        @foreach($ciclos as $ciclo)
                                            <option value="{{ $ciclo->id }}" {{ $ciclo->es_activo ? 'selected' : '' }}>
                                                {{ $ciclo->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="masivo-fecha-vencimiento" class="form-label">Fecha Vencimiento <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="masivo-fecha-vencimiento" name="fecha_vencimiento" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="masivo-carrera" class="form-label">Carrera (Opcional)</label>
                                    <select class="form-select" id="masivo-carrera" name="carrera_id">
                                        <option value="">Todas las carreras</option>
                                        @foreach($carreras as $carrera)
                                            <option value="{{ $carrera->id }}">{{ $carrera->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="masivo-turno" class="form-label">Turno (Opcional)</label>
                                    <select class="form-select" id="masivo-turno" name="turno_id">
                                        <option value="">Todos los turnos</option>
                                        @foreach($turnos as $turno)
                                            <option value="{{ $turno->id }}">{{ $turno->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="masivo-aula" class="form-label">Aula (Opcional)</label>
                                    <select class="form-select" id="masivo-aula" name="aula_id">
                                        <option value="">Todas las aulas</option>
                                        @foreach($aulas as $aula)
                                            <option value="{{ $aula->id }}">{{ $aula->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="alert alert-warning">
                            <i class="uil uil-exclamation-triangle"></i> 
                            Los estudiantes que ya tengan carnet para este ciclo serán omitidos.
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-success" id="confirmGenerarMasivo">
                        <i class="uil uil-check me-1"></i> Generar Carnets
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para ver detalle del carnet -->
    <div class="modal fade" id="viewCarnetModal" tabindex="-1" role="dialog" aria-labelledby="viewCarnetModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewCarnetModalLabel">Detalle del Carnet</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="viewCarnetBody">
                    <!-- El contenido se cargará dinámicamente -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    @if(Auth::user()->hasPermission('carnets.print'))
                    <button type="button" class="btn btn-primary" id="printSingleCarnet">
                        <i class="uil uil-print me-1"></i> Imprimir
                    </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para cambiar estado -->
    <div class="modal fade" id="changeStatusModal" tabindex="-1" role="dialog" aria-labelledby="changeStatusModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title" id="changeStatusModalLabel">Cambiar Estado del Carnet</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="changeStatusForm">
                        <input type="hidden" id="status-carnet-id">
                        <div class="mb-3">
                            <label for="nuevo-estado" class="form-label">Nuevo Estado <span class="text-danger">*</span></label>
                            <select class="form-select" id="nuevo-estado" name="estado" required>
                                <option value="">Seleccione un estado</option>
                                <option value="activo">Activo</option>
                                <option value="inactivo">Inactivo</option>
                                <option value="vencido">Vencido</option>
                                <option value="anulado">Anulado</option>
                            </select>
                        </div>
                        <div class="mb-3" id="motivo-anulacion-group" style="display: none;">
                            <label for="motivo-anulacion" class="form-label">Motivo de Anulación <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="motivo-anulacion" name="motivo" rows="3" 
                                placeholder="Ingrese el motivo de la anulación"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-warning" id="confirmChangeStatus">
                        <i class="uil uil-sync me-1"></i> Cambiar Estado
                    </button>
                </div>
            </div>
        </div>
    </div>
@endpush