@extends('layouts.app')

@push('css')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endpush


@push('js')
    <script>
        window.default_server = "{{ url('/') }}";
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="{{ asset('js/reportes/index.js') }}"></script>
@endpush
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    <h4 class="page-title">Reportes de Asistencia</h4>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="header-title mb-4">Generar Reporte de Asistencia por Día</h4>

                        <form id="formReporteAsistencia">
                            <div class="row">
                                <!-- Filtro de Ciclo -->
                                <div class="col-md-3 mb-3">
                                    <label for="ciclo_id" class="form-label">Ciclo <span
                                            class="text-danger">*</span></label>
                                    <select class="form-select" id="ciclo_id" name="ciclo_id" required>
                                        <option value="">Seleccione un ciclo</option>
                                        @foreach ($ciclos as $ciclo)
                                            <option value="{{ $ciclo->id }}" data-inicio="{{ $ciclo->fecha_inicio }}"
                                                data-fin="{{ $ciclo->fecha_fin }}"
                                                data-examen1="{{ $ciclo->fecha_primer_examen }}"
                                                data-examen2="{{ $ciclo->fecha_segundo_examen }}"
                                                data-examen3="{{ $ciclo->fecha_tercer_examen }}">
                                                {{ $ciclo->nombre }} ({{ $ciclo->codigo }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Filtro de Carrera -->
                                <div class="col-md-3 mb-3">
                                    <label for="carrera_id" class="form-label">Carrera</label>
                                    <select class="form-select" id="carrera_id" name="carrera_id">
                                        <option value="">Todas las carreras</option>
                                        @foreach ($carreras as $carrera)
                                            <option value="{{ $carrera->id }}">{{ $carrera->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Filtro de Turno -->
                                <div class="col-md-3 mb-3">
                                    <label for="turno_id" class="form-label">Turno</label>
                                    <select class="form-select" id="turno_id" name="turno_id">
                                        <option value="">Todos los turnos</option>
                                        @foreach ($turnos as $turno)
                                            <option value="{{ $turno->id }}">{{ $turno->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Filtro de Aula -->
                                <div class="col-md-3 mb-3">
                                    <label for="aula_id" class="form-label">Aula</label>
                                    <select class="form-select" id="aula_id" name="aula_id">
                                        <option value="">Todas las aulas</option>
                                        @foreach ($aulas as $aula)
                                            <option value="{{ $aula->id }}">{{ $aula->codigo }} - {{ $aula->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <!-- Selector de Fecha -->
                                <div class="col-md-4 mb-3">
                                    <label for="fecha_reporte" class="form-label">Fecha del Reporte <span
                                            class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="fecha_reporte" name="fecha_reporte"
                                        required>
                                    <div class="form-text" id="infoFecha"></div>
                                </div>

                                <!-- Tipo de Reporte -->
                                <div class="col-md-4 mb-3">
                                    <label for="tipo_reporte" class="form-label">Tipo de Reporte <span
                                            class="text-danger">*</span></label>
                                    <select class="form-select" id="tipo_reporte" name="tipo_reporte" required>
                                        <option value="asistencia_dia">Asistencia del Día</option>
                                        <option value="faltas_dia">Solo Faltas del Día</option>
                                        <option value="resumen_examen">Resumen para Examen</option>
                                    </select>
                                </div>

                                <!-- Formato de Descarga -->
                                <div class="col-md-4 mb-3">
                                    <label for="formato" class="form-label">Formato <span
                                            class="text-danger">*</span></label>
                                    <select class="form-select" id="formato" name="formato" required>
                                        <option value="pdf">PDF</option>
                                        <option value="xlsx">Excel</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Información de Exámenes -->
                            <div class="row" id="infoExamenes" style="display: none;">
                                <div class="col-12 mb-3">
                                    <div class="alert alert-info">
                                        <h5 class="alert-heading">Fechas de Exámenes del Ciclo</h5>
                                        <ul class="mb-0">
                                            <li id="examen1Info">Primer Examen: <span></span></li>
                                            <li id="examen2Info">Segundo Examen: <span></span></li>
                                            <li id="examen3Info">Tercer Examen: <span></span></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12">
                                    <button type="button" class="btn btn-primary" id="btnGenerarReporte">
                                        <i class="uil uil-file-download me-2"></i>Generar Reporte
                                    </button>
                                    <button type="button" class="btn btn-info ms-2" id="btnVistaPrevia">
                                        <i class="uil uil-eye me-2"></i>Vista Previa
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Vista Previa -->
        <div class="row" id="vistaPrevia" style="display: none;">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="header-title mb-4">Vista Previa del Reporte</h4>
                        <div id="contenidoVistaPrevia"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Carga -->
    <div class="modal fade" id="loadingModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <p class="mt-3 mb-0">Generando reporte...</p>
                </div>
            </div>
        </div>
    </div>
@endsection
