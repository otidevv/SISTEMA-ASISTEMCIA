<!-- asistencia/editar_index.blade.php -->
@extends('layouts.app')

@section('title', 'Gestión de Asistencias')

@push('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    /* Tabs Styling */
    .nav-tabs-custom {
        border-bottom: 2px solid #dee2e6;
        margin-bottom: 20px;
    }

    .nav-tabs-custom .nav-link {
        border: none;
        color: #6c757d;
        padding: 12px 24px;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .nav-tabs-custom .nav-link:hover {
        color: #0d6efd;
        background-color: #f8f9fa;
    }

    .nav-tabs-custom .nav-link.active {
        color: #0d6efd;
        border-bottom: 3px solid #0d6efd;
        background-color: transparent;
    }

    /* Search Container */
    .search-container {
        position: relative;
    }

    .search-input {
        padding-right: 40px;
    }

    .search-icon {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #6c757d;
        pointer-events: none;
    }

    /* Suggestions Dropdown */
    .suggestions-dropdown {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 1px solid #dee2e6;
        border-top: none;
        border-radius: 0 0 0.25rem 0.25rem;
        max-height: 300px;
        overflow-y: auto;
        display: none;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        z-index: 1050;
    }

    .suggestion-item {
        padding: 10px 15px;
        cursor: pointer;
        transition: background-color 0.2s ease;
        border-bottom: 1px solid #f1f3f4;
    }

    .suggestion-item:last-child {
        border-bottom: none;
    }

    .suggestion-item:hover,
    .suggestion-item.active {
        background-color: #f8f9fa;
    }

    .suggestion-item .text-primary {
        font-weight: 600;
    }

    .suggestion-item .dni {
        color: #6c757d;
        font-size: 0.875rem;
    }

    .selected-student {
        padding: 10px;
        background-color: #e7f3ff;
        border: 1px solid #b3d9ff;
        border-radius: 0.25rem;
        margin-bottom: 10px;
        display: none;
    }

    .selected-student .remove-btn {
        float: right;
        color: #dc3545;
        cursor: pointer;
        font-weight: bold;
    }

    .no-results {
        padding: 15px;
        text-align: center;
        color: #6c757d;
    }

    /* Table Styling */
    .table-estudiantes {
        font-size: 0.9rem;
    }

    .table-estudiantes thead {
        background-color: #f8f9fa;
    }

    .table-estudiantes tbody tr:hover {
        background-color: #f8f9fa;
    }

    /* Badge Styling */
    .badge-counter {
        font-size: 1rem;
        padding: 8px 16px;
    }

    /* Loading Spinner */
    .spinner-border-sm {
        width: 1rem;
        height: 1rem;
        border-width: 0.15em;
    }

    /* Flatpickr Custom */
    .flatpickr-day.selected {
        background: #0d6efd !important;
        border-color: #0d6efd !important;
    }

    /* Selected Dates List */
    .selected-dates-list {
        max-height: 200px;
        overflow-y: auto;
        border: 1px solid #dee2e6;
        border-radius: 0.25rem;
        padding: 10px;
    }

    .date-tag {
        display: inline-block;
        background-color: #e7f3ff;
        border: 1px solid #b3d9ff;
        padding: 4px 10px;
        border-radius: 4px;
        margin: 4px;
        font-size: 0.875rem;
    }

    .date-tag .remove-date {
        margin-left: 8px;
        color: #dc3545;
        cursor: pointer;
        font-weight: bold;
    }
</style>
@endpush

@section('content')
    <!-- Start Content-->
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('asistencia.index') }}">Asistencia</a></li>
                            <li class="breadcrumb-item active">Gestión de Asistencias</li>
                        </ol>
                    </div>
                    <h4 class="page-title">Gestión de Asistencias</h4>
                </div>
            </div>
        </div>
        <!-- end page title -->

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <!-- Tabs Navigation -->
                        <ul class="nav nav-tabs nav-tabs-custom" id="asistenciaTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="buscar-tab" data-bs-toggle="tab" data-bs-target="#buscar" type="button" role="tab">
                                    <i class="uil uil-search me-1"></i> Buscar y Editar
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="masivo-tab" data-bs-toggle="tab" data-bs-target="#masivo" type="button" role="tab">
                                    <i class="uil uil-users-alt me-1"></i> Registro Masivo
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="regularizar-tab" data-bs-toggle="tab" data-bs-target="#regularizar" type="button" role="tab">
                                    <i class="uil uil-calendar-alt me-1"></i> Regularización
                                </button>
                            </li>
                        </ul>

                        <!-- Tabs Content -->
                        <div class="tab-content" id="asistenciaTabsContent">
                            
                            <!-- TAB 1: Buscar y Editar -->
                            <div class="tab-pane fade show active" id="buscar" role="tabpanel">
                                <h4 class="header-title mb-3">Criterios de Búsqueda</h4>

                                <form action="{{ route('asistencia.editar') }}" method="GET">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="fecha_desde" class="form-label">Fecha Desde</label>
                                                <input type="date" class="form-control" id="fecha_desde" name="fecha_desde"
                                                    value="{{ request('fecha_desde', date('Y-m-d', strtotime('-7 days'))) }}">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="fecha_hasta" class="form-label">Fecha Hasta</label>
                                                <input type="date" class="form-control" id="fecha_hasta" name="fecha_hasta"
                                                    value="{{ request('fecha_hasta', date('Y-m-d')) }}">
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="documento" class="form-label">Número de Documento</label>
                                                <input type="text" class="form-control" id="documento" name="documento"
                                                    value="{{ request('documento') }}" placeholder="Ingrese número de documento">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="text-end">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="uil uil-search me-1"></i> Buscar
                                        </button>
                                        <a href="{{ route('asistencia.editar') }}" class="btn btn-secondary">
                                            <i class="uil uil-redo me-1"></i> Limpiar
                                        </a>
                                    </div>
                                </form>

                                @if (request()->has('fecha_desde') || request()->has('fecha_hasta') || request()->has('documento'))
                                    <hr>
                                    <h4 class="header-title mt-4 mb-3">Resultados de Búsqueda</h4>

                                    <div class="table-responsive">
                                        <table class="table table-centered table-nowrap table-striped" id="resultados-datatable">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Documento</th>
                                                    <th>Estudiante</th>
                                                    <th>Fecha y Hora</th>
                                                    <th>Tipo</th>
                                                    <th>Dispositivo</th>
                                                    <th>Estado</th>
                                                    <th>Acciones</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @if (isset($registros) && count($registros) > 0)
                                                    @foreach ($registros as $registro)
                                                        <tr>
                                                            <td>{{ $registro->id }}</td>
                                                            <td>{{ $registro->nro_documento }}</td>
                                                            <td>
                                                                @if ($registro->usuario)
                                                                    {{ $registro->usuario->nombre }}
                                                                    {{ $registro->usuario->apellido_paterno }}
                                                                @else
                                                                    <span class="text-muted">No encontrado</span>
                                                                @endif
                                                            </td>
                                                            <td>{{ $registro->fecha_hora->format('d/m/Y H:i:s') }}</td>
                                                            <td>{{ $registro->tipo_verificacion_texto }}</td>
                                                            <td>{{ $registro->sn_dispositivo ?: 'N/A' }}</td>
                                                            <td>
                                                                @if ($registro->estado)
                                                                    <span class="badge bg-success">Activo</span>
                                                                @else
                                                                    <span class="badge bg-danger">Inactivo</span>
                                                                @endif
                                                            </td>
                                                            <td>
                                                                <a href="{{ route('asistencia.editar.form', $registro->id) }}"
                                                                    class="btn btn-sm btn-primary">
                                                                    <i class="uil uil-edit"></i> Editar
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                @else
                                                    <tr>
                                                        <td colspan="8" class="text-center">No se encontraron registros con los
                                                            criterios de búsqueda.</td>
                                                    </tr>
                                                @endif
                                            </tbody>
                                        </table>
                                    </div>

                                    @if (isset($registros) && $registros->hasPages())
                                        <div class="pagination justify-content-end mt-3">
                                            {{ $registros->appends(request()->except('page'))->links() }}
                                        </div>
                                    @endif
                                @endif
                            </div>

                            <!-- TAB 2: Registro Masivo -->
                            <div class="tab-pane fade" id="masivo" role="tabpanel">
                                <h4 class="header-title mb-3">Registro Masivo de Asistencias</h4>
                                <p class="text-muted">Registre asistencia para múltiples estudiantes en una fecha específica.</p>

                                <form id="formRegistroMasivo">
                                    @csrf
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <label for="masivo_fecha" class="form-label">Fecha <span class="text-danger">*</span></label>
                                                <input type="date" class="form-control" id="masivo_fecha" name="fecha" required>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <label for="masivo_hora" class="form-label">Hora <span class="text-danger">*</span></label>
                                                <input type="time" class="form-control" id="masivo_hora" name="hora" value="08:00" required>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <label for="masivo_tipo" class="form-label">Tipo de Verificación <span class="text-danger">*</span></label>
                                                <select class="form-select" id="masivo_tipo" name="tipo_verificacion" required>
                                                    <option value="0">Huella digital</option>
                                                    <option value="1">Tarjeta RFID</option>
                                                    <option value="2">Facial</option>
                                                    <option value="3">Código QR</option>
                                                    <option value="4" selected>Manual</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <label for="filtro_ciclo" class="form-label">Ciclo <span class="text-danger">*</span></label>
                                                <select class="form-select" id="filtro_ciclo" name="ciclo_id" required>
                                                    @foreach($ciclos as $ciclo)
                                                        <option value="{{ $ciclo->id }}" {{ $cicloActivo && $cicloActivo->id == $ciclo->id ? 'selected' : '' }}>
                                                            {{ $ciclo->nombre }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <label for="filtro_aula" class="form-label">Aula (Opcional)</label>
                                                <select class="form-select" id="filtro_aula" name="aula_id">
                                                    <option value="">Todas las aulas</option>
                                                    @foreach($aulas as $aula)
                                                        <option value="{{ $aula->id }}">{{ $aula->nombre }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <label for="filtro_turno" class="form-label">Turno (Opcional)</label>
                                                <select class="form-select" id="filtro_turno" name="turno_id">
                                                    <option value="">Todos los turnos</option>
                                                    @foreach($turnos as $turno)
                                                        <option value="{{ $turno->id }}">{{ $turno->nombre }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <label for="filtro_carrera" class="form-label">Carrera (Opcional)</label>
                                                <select class="form-select" id="filtro_carrera" name="carrera_id">
                                                    <option value="">Todas las carreras</option>
                                                    @foreach($carreras as $carrera)
                                                        <option value="{{ $carrera->id }}">{{ $carrera->nombre }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="text-end mb-3">
                                        <button type="button" class="btn btn-info" id="btnCargarEstudiantes">
                                            <i class="uil uil-download-alt me-1"></i> Cargar Estudiantes
                                        </button>
                                    </div>
                                </form>

                                <!-- Tabla de Estudiantes -->
                                <div id="tablaEstudiantesContainer" style="display: none;">
                                    <hr>
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h5 class="mb-0">Estudiantes Cargados</h5>
                                        <span class="badge bg-primary badge-counter" id="contadorSeleccionados">0 seleccionados</span>
                                    </div>

                                    <div class="mb-3">
                                        <input type="text" class="form-control" id="buscarEstudiante" placeholder="Buscar por nombre o DNI...">
                                    </div>

                                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                                        <table class="table table-hover table-estudiantes">
                                            <thead class="sticky-top">
                                                <tr>
                                                    <th style="width: 50px;">
                                                        <input type="checkbox" class="form-check-input" id="checkTodos">
                                                    </th>
                                                    <th>DNI</th>
                                                    <th>Nombre Completo</th>
                                                    <th>Aula</th>
                                                    <th>Turno</th>
                                                    <th>Carrera</th>
                                                </tr>
                                            </thead>
                                            <tbody id="tbodyEstudiantes">
                                                <!-- Se llenará dinámicamente -->
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="text-end mt-3">
                                        <button type="button" class="btn btn-success" id="btnRegistrarMasivo">
                                            <i class="uil uil-check me-1"></i> Registrar Asistencias
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- TAB 3: Regularización Individual -->
                            <div class="tab-pane fade" id="regularizar" role="tabpanel">
                                <h4 class="header-title mb-3">Regularización de Asistencias</h4>
                                <p class="text-muted">Registre asistencias para un estudiante en múltiples fechas.</p>

                                <form id="formRegularizacion">
                                    @csrf
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="estudiante_search_reg" class="form-label">Estudiante <span class="text-danger">*</span></label>
                                                
                                                <!-- Contenedor del estudiante seleccionado -->
                                                <div class="selected-student" id="selectedStudentReg">
                                                    <span class="remove-btn" onclick="removeStudentReg()">×</span>
                                                    <strong id="selectedNameReg"></strong><br>
                                                    <small>DNI: <span id="selectedDNIReg"></span></small>
                                                </div>

                                                <!-- Campo de búsqueda con autocompletado -->
                                                <div class="search-container">
                                                    <input type="text" 
                                                           class="form-control search-input" 
                                                           id="estudiante_search_reg" 
                                                           placeholder="Buscar por nombre o DNI..."
                                                           autocomplete="off">
                                                    <i class="fas fa-search search-icon"></i>
                                                    <div class="suggestions-dropdown" id="suggestionsReg"></div>
                                                </div>

                                                <!-- Campo oculto para enviar el DNI -->
                                                <input type="hidden" id="nro_documento_reg" name="nro_documento" required>
                                            </div>
                                        </div>

                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <label for="reg_hora" class="form-label">Hora <span class="text-danger">*</span></label>
                                                <input type="time" class="form-control" id="reg_hora" name="hora" value="08:00" required>
                                            </div>
                                        </div>

                                        <div class="col-md-3">
                                            <div class="mb-3">
                                                <label for="reg_tipo" class="form-label">Tipo de Verificación <span class="text-danger">*</span></label>
                                                <select class="form-select" id="reg_tipo" name="tipo_verificacion" required>
                                                    <option value="0">Huella digital</option>
                                                    <option value="1">Tarjeta RFID</option>
                                                    <option value="2">Facial</option>
                                                    <option value="3">Código QR</option>
                                                    <option value="4" selected>Manual</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label for="fechas_regularizar" class="form-label">Seleccionar Fechas <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="fechas_regularizar" placeholder="Haga clic para seleccionar fechas...">
                                                <small class="text-muted">Puede seleccionar múltiples fechas</small>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="mb-3">
                                                <label class="form-label">Fechas Seleccionadas:</label>
                                                <div class="selected-dates-list" id="selectedDatesList">
                                                    <span class="text-muted">No hay fechas seleccionadas</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="text-end">
                                        <button type="submit" class="btn btn-success" id="btnRegularizar">
                                            <i class="uil uil-check me-1"></i> Regularizar Asistencias
                                        </button>
                                    </div>
                                </form>
                            </div>

                        </div> <!-- end tab-content -->

                    </div> <!-- end card-body-->
                </div> <!-- end card-->
            </div> <!-- end col -->
        </div>
        <!-- end row -->
    </div> <!-- container -->
@endsection

@push('js')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Pasar datos de estudiantes a JavaScript
    window.estudiantesData = @json($estudiantes);
    
    // Pasar rutas y token CSRF a JavaScript
    window.appRoutes = {
        estudiantesFiltrados: '{{ route("asistencia.estudiantes.filtrados") }}',
        registrarMasivo: '{{ route("asistencia.registrar.masivo") }}',
        regularizar: '{{ route("asistencia.regularizar") }}'
    };
    window.csrfToken = '{{ csrf_token() }}';

    $(document).ready(function() {
        // DataTable para los resultados de búsqueda
        @if(request()->has('fecha_desde') || request()->has('fecha_hasta') || request()->has('documento'))
        $('#resultados-datatable').DataTable({
            "paging": false,
            "ordering": true,
            "info": false,
            "searching": true,
            "language": {
                "search": "Buscar:",
                "zeroRecords": "No se encontraron resultados",
                "info": "Mostrando _START_ a _END_ de _TOTAL_ registros",
                "infoEmpty": "Mostrando 0 a 0 de 0 registros",
                "infoFiltered": "(filtrado de _MAX_ registros totales)"
            }
        });
        @endif

        // Establecer fecha actual por defecto en registro masivo
        document.getElementById('masivo_fecha').valueAsDate = new Date();
    });
</script>
<script src="{{ asset('js/asistencia/editar_index.js') }}"></script>
@endpush
