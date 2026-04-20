@extends('layouts.app')

@section('title', 'Reporte Resumen de Postulantes')

@push('css')
<style>
    :root {
        --cepre-pink: #ec008c;
        --cepre-green: #8cc63f;
        --cepre-blue: #00aeef;
        --cepre-dark-blue: #2b5a6f;
        --cepre-yellow: #fff200;
        
        --primary-gradient: linear-gradient(135deg, var(--cepre-pink) 0%, #ff4fb1 100%);
        --success-gradient: linear-gradient(135deg, var(--cepre-green) 0%, #a2d164 100%);
        --warning-gradient: linear-gradient(135deg, var(--cepre-yellow) 0%, #ffd04b 100%);
        --info-gradient: linear-gradient(135deg, var(--cepre-blue) 0%, #40c4f3 100%);
        --danger-gradient: linear-gradient(135deg, #ea5455 0%, #ff5252 100%);
    }

    .badge-a { background: rgba(236, 0, 140, 0.1) !important; color: var(--cepre-pink) !important; } /* Rosa */
    .badge-b { background: rgba(140, 198, 63, 0.1) !important; color: var(--cepre-green) !important; } /* Verde */
    .badge-c { background: rgba(0, 174, 239, 0.1) !important; color: var(--cepre-blue) !important; } /* Azul */
    .badge-d { background: rgba(255, 242, 0, 0.2) !important; color: #9c9400 !important; } /* Amarillo */


    .report-card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
    }

    .report-header {
        background: var(--primary-gradient);
        color: white;
        border-radius: 15px 15px 0 0;
        padding: 1.5rem;
    }

    .filter-section {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 1.5rem;
        margin-bottom: 2rem;
        border: 1px solid rgba(0,0,0,0.05);
    }

    .btn-premium {
        border-radius: 10px;
        padding: 10px 20px;
        font-weight: 600;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        border: none;
    }

    .btn-premium-primary { background: var(--primary-gradient); color: white; }
    .btn-premium-success { background: var(--success-gradient); color: white; box-shadow: 0 4px 15px rgba(40, 199, 111, 0.3); }
    .btn-premium-danger { background: var(--danger-gradient); color: white; box-shadow: 0 4px 15px rgba(234, 84, 85, 0.3); }
    .btn-premium-info { background: var(--info-gradient); color: white; box-shadow: 0 4px 15px rgba(0, 207, 232, 0.3); }

    .btn-premium:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        color: white;
    }

    .preview-container {
        display: none;
        margin-top: 2rem;
        animation: fadeIn 0.5s ease;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .table-premium {
        border-collapse: separate;
        border-spacing: 0 8px;
    }

    .table-premium thead th {
        background: transparent;
        border: none;
        color: #6c757d;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.8rem;
        padding: 12px 20px;
    }

    .table-premium tbody tr {
        box-shadow: 0 2px 10px rgba(0,0,0,0.02);
        background: white;
        transition: all 0.2s ease;
    }

    .table-premium tbody tr:hover {
        transform: scale(1.01);
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    }

    .table-premium tbody td {
        background: white;
        border: none;
        padding: 15px 20px;
        vertical-align: middle;
    }

    .table-premium tbody td:first-child { border-radius: 10px 0 0 10px; }
    .table-premium tbody td:last-child { border-radius: 0 10px 10px 0; }

    .group-badge {
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 700;
    }

    .badge-a { background: rgba(79, 50, 194, 0.1); color: #4f32c2; }
    .badge-b { background: rgba(16, 185, 129, 0.1); color: #10b981; }
    .badge-c { background: rgba(255, 159, 67, 0.1); color: #ff9f43; }
    .badge-d { background: rgba(234, 84, 85, 0.1); color: #ea5455; }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0 shadow-sm p-2 rounded bg-white"><i class="mdi mdi-table-eye text-primary"></i> Reporte Resumen de Postulantes</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0 text-muted">
                        <li class="breadcrumb-item"><a href="javascript: void(0);">Reportes</a></li>
                        <li class="breadcrumb-item active">Resumen</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card report-card">
                <div class="report-header">
                    <h5 class="m-0 text-white"><i class="mdi mdi-filter-variant me-1"></i> Filtros de Reporte</h5>
                    <p class="mb-0 text-white-50 small">Configure los parámetros para generar el resumen organizado.</p>
                </div>
                <div class="card-body">
                    <div class="filter-section">
                        <form id="filterForm" method="POST">
                            @csrf
                            <div class="row g-3">
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">Ciclo Académico</label>
                                    <select class="form-select border-0 shadow-sm" id="ciclo_id" name="ciclo_id" required>
                                        @foreach($ciclos as $ciclo)
                                            <option value="{{ $ciclo->id }}" {{ $ciclo->es_activo ? 'selected' : '' }}>{{ $ciclo->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">Carrera Profesional</label>
                                    <select class="form-select border-0 shadow-sm" id="carrera_id" name="carrera_id">
                                        <option value="">Todas las Carreras</option>
                                        @foreach($carreras as $carrera)
                                            <option value="{{ $carrera->id }}">{{ $carrera->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">Turno</label>
                                    <select class="form-select border-0 shadow-sm" id="turno_id" name="turno_id">
                                        <option value="">Todos los Turnos</option>
                                        @foreach($turnos as $turno)
                                            <option value="{{ $turno->id }}">{{ $turno->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-bold">Aula</label>
                                    <select class="form-select border-0 shadow-sm" id="aula_id" name="aula_id">
                                        <option value="">Todas las Aulas</option>
                                        @foreach($aulas as $aula)
                                            <option value="{{ $aula->id }}">{{ $aula->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="d-flex justify-content-center gap-2 mt-4">
                                <button type="button" id="btnPreview" class="btn btn-premium btn-premium-info">
                                    <i class="mdi mdi-eye"></i> Vista Previa
                                </button>
                                <button type="submit" formaction="{{ route('postulaciones.reportes.resumen.exportar') }}" class="btn btn-premium btn-premium-success">
                                    <i class="mdi mdi-file-excel"></i> Exportar Excel
                                </button>
                                <button type="submit" formaction="{{ route('postulaciones.reportes.resumen.exportar_pdf') }}" class="btn btn-premium btn-premium-danger">
                                    <i class="mdi mdi-file-pdf"></i> Exportar PDF
                                </button>
                            </div>
                        </form>
                    </div>

                    <div id="previewContainer" class="preview-container">
                        <div class="row">
                            <div class="col-lg-8">
                                <h5 class="mb-3 text-dark fw-bold"><i class="mdi mdi-format-list-bulleted me-1 text-primary"></i> Tabla de Resultados</h5>
                                <div class="table-responsive">
                                    <table class="table table-premium" id="previewTable">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Grupo</th>
                                                <th>Carrera</th>
                                                <th>Aula</th>
                                                <th class="text-center">Total</th>
                                            </tr>
                                        </thead>
                                        <tbody id="previewBody">
                                            <!-- Data injection via JS -->
                                        </tbody>
                                        <tfoot id="previewFoot">
                                            <!-- Total row -->
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <h5 class="mb-3 text-dark fw-bold"><i class="mdi mdi-chart-pie me-1 text-primary"></i> Resumen por Aula</h5>
                                <div class="table-responsive">
                                    <table class="table table-premium" id="summaryTable">
                                        <thead>
                                            <tr>
                                                <th>Aula</th>
                                                <th class="text-center">Cant.</th>
                                            </tr>
                                        </thead>
                                        <tbody id="summaryBody">
                                            <!-- Data injection via JS -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#btnPreview').on('click', function() {
            const formData = $('#filterForm').serialize();
            
            // Show loading or similar
            $(this).prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Cargando...');

            $.ajax({
                url: "{{ route('postulaciones.reportes.get-resumen-data') }}",
                method: 'POST',
                data: formData,
                success: function(response) {
                    $('#btnPreview').prop('disabled', false).html('<i class="mdi mdi-eye"></i> Vista Previa');
                    
                    if (response.success) {
                        $('#previewContainer').fadeIn();
                        renderTable(response.data);
                    } else {
                        Swal.fire('Error', response.message || 'No se pudo cargar la vista previa', 'error');
                    }
                },
                error: function() {
                    $('#btnPreview').prop('disabled', false).html('<i class="mdi mdi-eye"></i> Vista Previa');
                    Swal.fire('Error', 'Ocurrió un error en el servidor', 'error');
                }
            });
        });

        function renderTable(data) {
            let bodyHtml = '';
            let totalGeneral = 0;

            // Render Main Table
            data.tabla1.forEach((row, index) => {
                if (index < 2) return; // Skip headers
                if (row.length < 5) return;
                
                const isTotalRow = row[2] === 'Total';
                if (isTotalRow) {
                    totalGeneral = row[4];
                    return;
                }

                const groupClass = 'badge-' + (row[1]?.split(' ')[1] || 'default').toLowerCase();
                
                bodyHtml += `
                    <tr>
                        <td class="fw-bold text-muted">${row[0] || ''}</td>
                        <td>${row[1] ? `<span class="group-badge ${groupClass}">${row[1]}</span>` : ''}</td>
                        <td class="fw-bold text-dark">${row[2]}</td>
                        <td><span class="badge bg-light text-dark shadow-sm border">${row[3]}</span></td>
                        <td class="text-center"><span class="fw-bold text-primary">${row[4]}</span></td>
                    </tr>
                `;
            });

            $('#previewBody').html(bodyHtml);
            $('#previewFoot').html(`
                <tr class="table-light">
                    <td colspan="4" class="text-end fw-bold py-3 text-uppercase">Total General:</td>
                    <td class="text-center py-3"><h4 class="m-0 fw-bold text-primary">${totalGeneral}</h4></td>
                </tr>
            `);

            // Render Summary Table
            let summaryHtml = '';
            data.tabla2.forEach((row, index) => {
                if (index < 2) return;
                if (row[0] === 'Total') return;

                summaryHtml += `
                    <tr>
                        <td class="fw-bold">${row[0]}</td>
                        <td class="text-center font-size-15 fw-bold text-info">${row[1]}</td>
                    </tr>
                `;
            });
            $('#summaryBody').html(summaryHtml);
        }
    });
</script>
@endpush