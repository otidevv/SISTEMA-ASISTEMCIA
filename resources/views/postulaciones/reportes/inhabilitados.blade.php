@extends('layouts.app')

@section('title', 'Reporte de Estudiantes Inhabilitados')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card modern-card">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <h4 class="mb-1 text-primary"><i class="mdi mdi-account-off-outline"></i> Reporte de Estudiantes Inhabilitados</h4>
                                <p class="text-muted mb-0">Gestiona y genera reportes de estudiantes que superan el límite de inasistencias.</p>
                            </div>
                        </div>

                        <form id="filterForm" action="{{ route('postulaciones.reportes.inhabilitados.pdf') }}" method="POST">
                            @csrf
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label for="ciclo_id" class="form-label font-weight-bold text-dark">Ciclo Académico</label>
                                    <select class="form-select select2" id="ciclo_id" name="ciclo_id" required>
                                        @foreach($ciclos as $ciclo)
                                            <option value="{{ $ciclo->id }}" {{ $ciclo->es_activo ? 'selected' : '' }}>
                                                {{ $ciclo->nombre }} {{ $ciclo->es_activo ? '(Actual)' : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="periodo_examen" class="form-label font-weight-bold text-dark">Calcular hasta:</label>
                                    <select class="form-select" id="periodo_examen" name="periodo_examen">
                                        <option value="hoy">Fecha Actual (Proyectado)</option>
                                        <option value="1">Hasta Primer Examen</option>
                                        <option value="2">Hasta Segundo Examen</option>
                                        <option value="3">Hasta Tercer Examen</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="tipo_inscripcion" class="form-label font-weight-bold text-dark">Modalidad</label>
                                    <select class="form-select" id="tipo_inscripcion" name="tipo_inscripcion">
                                        <option value="">Todas las modalidades</option>
                                        <option value="postulante">Postulante</option>
                                        <option value="reforzamiento">Reforzamiento</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="carrera_id" class="form-label font-weight-bold text-dark">Carrera</label>
                                    <select class="form-select select2" id="carrera_id" name="carrera_id">
                                        <option value="">Todas las carreras</option>
                                        @foreach($carreras as $carrera)
                                            <option value="{{ $carrera->id }}">{{ $carrera->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-12 d-flex flex-wrap gap-2 mt-3">
                                    <button type="button" id="btnPreview" class="btn btn-primary px-4 shadow-sm font-weight-bold">
                                        <i class="mdi mdi-eye me-1"></i> CARGAR RESULTADOS
                                    </button>
                                    <button type="button" id="btnExportPdf" class="btn btn-danger px-4 shadow-sm font-weight-bold">
                                        <i class="mdi mdi-file-pdf-box me-1"></i> DESCARGAR PDF
                                    </button>
                                    <button type="button" id="btnExportExcel" class="btn btn-success px-4 shadow-sm font-weight-bold">
                                        <i class="mdi mdi-file-excel me-1"></i> EXPORTAR EXCEL
                                    </button>
                                </div>
                            </div>
                        </form>

                        <!-- Stats Section -->
                        <div id="statsSection" class="row mt-4" style="display: none;">
                            <div class="col-md-3">
                                <div class="card bg-light border-0 shadow-sm text-center p-3 mb-3">
                                    <small class="text-muted text-uppercase mb-1">Total Analizados</small>
                                    <h3 id="statTotal" class="mb-0 text-dark">0</h3>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-success-light border-0 shadow-sm text-center p-3 mb-3">
                                    <small class="text-muted text-uppercase mb-1">Regulares</small>
                                    <h3 id="statRegulares" class="mb-0 text-success">0</h3>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-warning-light border-0 shadow-sm text-center p-3 mb-3">
                                    <small class="text-muted text-uppercase mb-1">Amonestados</small>
                                    <h3 id="statAmonestados" class="mb-0 text-warning">0</h3>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card bg-danger-light border-0 shadow-sm text-center p-3 mb-3">
                                    <small class="text-muted text-uppercase mb-1">Inhabilitados</small>
                                    <h3 id="statInhabilitados" class="mb-0 text-danger">0</h3>
                                </div>
                            </div>
                        </div>

                        <!-- Results Table Section -->
                        <div id="resultsSection" class="mt-4" style="display: none;">
                            <hr>
                            <h5 class="mb-3 text-primary"><i class="mdi mdi-format-list-bulleted me-2"></i> Lista de Estudiantes Inhabilitados</h5>
                            <div class="table-responsive">
                                <table class="table table-hover table-striped border" id="resultsTable">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>Estudiante</th>
                                            <th class="text-center">DNI</th>
                                            <th>Carrera</th>
                                            <th class="text-center">Faltas</th>
                                            <th class="text-center">% Inasist.</th>
                                            <th class="text-center">Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody id="resultsBody">
                                        <!-- Data populated via AJAX -->
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div id="noResults" class="alert alert-warning mt-4 text-center" style="display: none;">
                            <i class="mdi mdi-alert-circle-outline me-2"></i> No se encontraron estudiantes inhabilitados con los filtros seleccionados.
                        </div>

                        <div class="alert alert-info mt-4 border-0 shadow-sm">
                            <h5 class="alert-heading fs-6"><i class="mdi mdi-information-outline"></i> Nota:</h5>
                            <ul class="mb-0 small">
                                <li><strong>Inhabilitado</strong>: Supera el {{ $cicloActivo->porcentaje_inhabilitacion ?? '30' }}% de inasistencias.</li>
                                <li><strong>Amonestado</strong>: Supera el {{ $cicloActivo->porcentaje_amonestacion ?? '20' }}% de inasistencias.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
    .bg-success-light { background-color: rgba(40, 167, 69, 0.1) !important; }
    .bg-warning-light { background-color: rgba(255, 193, 7, 0.1) !important; }
    .bg-danger-light { background-color: rgba(220, 53, 69, 0.1) !important; }
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize Select2 if exists
    if ($.fn.select2) {
        $('.select2').select2({
            theme: 'bootstrap-4',
            width: '100%'
        });
    }

    function loadResults() {
        const formData = $('#filterForm').serialize();
        
        Swal.fire({
            title: 'Procesando...',
            text: 'Calculando inasistencias, por favor espere.',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        $.ajax({
            url: "{{ route('postulaciones.reportes.inhabilitados.data') }}",
            type: "GET",
            data: formData,
            success: function(response) {
                Swal.close();
                if (response.success) {
                    const data = response.data;
                    
                    // Update Stats
                    $('#statTotal').text(data.total_general);
                    $('#statRegulares').text(data.total_regulares);
                    $('#statAmonestados').text(data.total_amonestados);
                    $('#statInhabilitados').text(data.total_inhabilitados);
                    $('#statsSection').fadeIn();

                    // Update Table
                    const body = $('#resultsBody');
                    body.empty();

                    if (data.inhabilitados.length > 0) {
                        data.inhabilitados.forEach(item => {
                            const inasistPercentage = (100 - item.porcentaje).toFixed(1);
                            body.append(`
                                <tr>
                                    <td>
                                        <div class="font-weight-bold text-dark">${item.nombres}</div>
                                        <small class="text-muted">Aula: ${item.aula} | Turno: ${item.turno}</small>
                                    </td>
                                    <td class="text-center">${item.dni}</td>
                                    <td>${item.carrera}</td>
                                    <td class="text-center text-danger font-weight-bold">${item.faltas}</td>
                                    <td class="text-center">${inasistPercentage}%</td>
                                    <td class="text-center">
                                        <span class="badge bg-danger">Inhabilitado</span>
                                    </td>
                                </tr>
                            `);
                        });
                        $('#resultsSection').fadeIn();
                        $('#noResults').hide();
                    } else {
                        $('#resultsSection').hide();
                        $('#noResults').fadeIn();
                    }
                }
            },
            error: function() {
                Swal.fire('Error', 'No se pudo cargar la vista previa.', 'error');
            }
        });
    }

    $('#btnPreview').click(function() {
        loadResults();
    });

    $('#btnExportPdf').click(function() {
        $('#filterForm').attr('action', "{{ route('postulaciones.reportes.inhabilitados.pdf') }}").submit();
    });

    $('#btnExportExcel').click(function() {
        $('#filterForm').attr('action', "{{ route('postulaciones.reportes.inhabilitados.excel') }}").submit();
    });

    // Cargar automáticamente al inicio si el usuario lo desea
    // loadResults(); 
});
</script>
@endpush
