@extends('layouts.app')

@section('title', 'Mi Informe de Trabajo')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">CEPRE</a></li>
                        <li class="breadcrumb-item"><a href="javascript: void(0);">Inteligencia de Datos</a></li>
                        <li class="breadcrumb-item active">Mi Informe de Trabajo</li>
                    </ol>
                </div>
                <h4 class="page-title fw-bold text-dark"><i class="mdi mdi-account-clock-outline me-1 text-primary"></i> Mi Informe de Trabajo</h4>
            </div>
        </div>
    </div>

    <!-- Estilos Premium de la página -->
    <style>
        .gradient-card-primary {
            background: linear-gradient(135deg, hsl(329, 85%, 45%) 0%, hsl(340, 85%, 55%) 100%);
            color: #fff;
            border: none;
            border-radius: 16px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .gradient-card-success {
            background: linear-gradient(135deg, hsl(142, 60%, 35%) 0%, hsl(150, 60%, 45%) 100%);
            color: #fff;
            border: none;
            border-radius: 16px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .gradient-card-info {
            background: linear-gradient(135deg, hsl(210, 80%, 40%) 0%, hsl(200, 80%, 50%) 100%);
            color: #fff;
            border: none;
            border-radius: 16px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .gradient-card-warning {
            background: linear-gradient(135deg, hsl(35, 90%, 45%) 0%, hsl(45, 90%, 55%) 100%);
            color: #fff;
            border: none;
            border-radius: 16px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .kpi-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 20px rgba(0, 0, 0, 0.15);
        }
        .filter-panel {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            border: 1px solid rgba(229, 231, 235, 0.5);
            backdrop-filter: blur(10px);
        }
        .tab-premium {
            border-radius: 12px !important;
            padding: 10px 20px !important;
            font-weight: 600 !important;
            transition: all 0.3s ease;
        }
        .tab-premium.active {
            background-color: hsl(329, 85%, 45%) !important;
            color: white !important;
            box-shadow: 0 4px 10px rgba(216, 27, 96, 0.3);
        }
        .avatar-table {
            width: 42px;
            height: 42px;
            object-fit: cover;
            border-radius: 50%;
            border: 2px solid #fff;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .btn-export {
            border-radius: 10px;
            font-weight: 600;
            padding: 8px 16px;
            transition: all 0.3s ease;
        }
        .btn-export-pdf {
            background-color: #f03e3e;
            color: white;
            border: none;
        }
        .btn-export-pdf:hover {
            background-color: #d63031;
            box-shadow: 0 4px 12px rgba(240, 62, 62, 0.4);
            color: white;
        }
        .btn-export-excel {
            background-color: #10b981;
            color: white;
            border: none;
        }
        .btn-export-excel:hover {
            background-color: #059669;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
            color: white;
        }
    </style>

    <!-- Panel de Filtros -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card filter-panel border-0">
                <div class="card-body p-4">
                    <form id="formFiltros" class="row align-items-end g-3">
                        @csrf
                        <div class="col-md-3">
                            <label class="form-label fw-semibold text-muted small text-uppercase">Rango de Fecha</label>
                            <select id="rango" name="rango" class="form-select border-2 rounded-3">
                                <option value="today">Hoy</option>
                                <option value="yesterday">Ayer</option>
                                <option value="week">Esta Semana</option>
                                <option value="custom">Personalizado</option>
                            </select>
                        </div>

                        <!-- Filtro Personalizado (Escondido por defecto) -->
                        <div class="col-md-4" id="rangoPersonalizado" style="display: none;">
                            <div class="row">
                                <div class="col-6">
                                    <label class="form-label fw-semibold text-muted small text-uppercase">Inicio</label>
                                    <input type="date" id="fecha_inicio" name="fecha_inicio" class="form-input form-control border-2 rounded-3">
                                </div>
                                <div class="col-6">
                                    <label class="form-label fw-semibold text-muted small text-uppercase">Fin</label>
                                    <input type="date" id="fecha_fin" name="fecha_fin" class="form-input form-control border-2 rounded-3">
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold text-muted small text-uppercase">Ciclo Académico</label>
                            <select id="ciclo_id" name="ciclo_id" class="form-select border-2 rounded-3">
                                <option value="">Todos los Ciclos</option>
                                @foreach($ciclos as $c)
                                    <option value="{{ $c->id }}" {{ $c->es_activo ? 'selected' : '' }}>{{ $c->nombre }}</option>
                                @endforeach
                            </select>
                        </div>

                        @if($esAdminOCoordinador && $operadores)
                            <div class="col-md-3">
                                <label class="form-label fw-semibold text-muted small text-uppercase">Consultar Operador</label>
                                <select id="operador_id" name="operador_id" class="form-select border-2 rounded-3">
                                    <option value="{{ Auth::id() }}">Mí Actividad ({{ Auth::user()->nombre }})</option>
                                    @foreach($operadores as $op)
                                        @if($op->id !== Auth::id())
                                            <option value="{{ $op->id }}">{{ $op->nombre }} {{ $op->apellido_paterno }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <div class="col-md-3 d-flex gap-2 justify-content-end ms-auto">
                            <button type="button" id="btnExportPdf" class="btn btn-export btn-export-pdf d-flex align-items-center gap-1">
                                <i class="mdi mdi-file-pdf-box fs-4"></i> Exportar PDF
                            </button>
                            <button type="button" id="btnExportExcel" class="btn btn-export btn-export-excel d-flex align-items-center gap-1">
                                <i class="mdi mdi-microsoft-excel fs-4"></i> Exportar Excel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- KPIs Estadísticos -->
    <div class="row mb-4" id="kpisSection">
        <div class="col-md-3">
            <div class="card kpi-card gradient-card-primary shadow-sm">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 text-uppercase fw-semibold mt-0">Total Procesados</h6>
                            <h2 class="my-2 text-white fw-bold" id="kpiTotal">0</h2>
                        </div>
                        <i class="mdi mdi-badge-account-horizontal-outline text-white-50" style="font-size: 3rem;"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card kpi-card gradient-card-success shadow-sm">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 text-uppercase fw-semibold mt-0">Postulaciones (CEPRE)</h6>
                            <h2 class="my-2 text-white fw-bold" id="kpiPostulaciones">0</h2>
                        </div>
                        <i class="mdi mdi-school-outline text-white-50" style="font-size: 3rem;"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card kpi-card gradient-card-info shadow-sm">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 text-uppercase fw-semibold mt-0">Reforzamiento Escolar</h6>
                            <h2 class="my-2 text-white fw-bold" id="kpiReforzamientos">0</h2>
                        </div>
                        <i class="mdi mdi-star-circle-outline text-white-50" style="font-size: 3rem;"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card kpi-card gradient-card-warning shadow-sm">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 text-uppercase fw-semibold mt-0">Total Recaudación</h6>
                            <h2 class="my-2 text-white fw-bold" id="kpiRecaudado">S/. 0.00</h2>
                        </div>
                        <i class="mdi mdi-cash-register text-white-50" style="font-size: 3rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pestañas de Detalle -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <ul class="nav nav-pills nav-justified mb-4" id="pills-tab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link tab-premium active" id="pills-postulaciones-tab" data-bs-toggle="pill" data-bs-target="#pills-postulaciones" type="button" role="tab" aria-selected="true">
                                <i class="mdi mdi-school me-1"></i> Postulaciones Ordinarias (CEPRE)
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link tab-premium" id="pills-reforzamiento-tab" data-bs-toggle="pill" data-bs-target="#pills-reforzamiento" type="button" role="tab" aria-selected="false">
                                <i class="mdi mdi-star me-1"></i> Reforzamiento Escolar
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content" id="pills-tabContent">
                        <!-- Pestaña Postulaciones -->
                        <div class="tab-pane fade show active" id="pills-postulaciones" role="tabpanel" aria-labelledby="pills-postulaciones-tab">
                            <div class="table-responsive">
                                <table id="tablaPostulaciones" class="table table-hover dt-responsive nowrap w-100 align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="text-center" style="width: 60px;">Foto</th>
                                            <th>Código</th>
                                            <th>Documento</th>
                                            <th>Estudiante</th>
                                            <th>Carrera/Programa</th>
                                            <th>Turno</th>
                                            <th>Monto</th>
                                            <th>Fecha Aprobación</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Pestaña Reforzamiento -->
                        <div class="tab-pane fade" id="pills-reforzamiento" role="tabpanel" aria-labelledby="pills-reforzamiento-tab">
                            <div class="table-responsive">
                                <table id="tablaReforzamiento" class="table table-hover dt-responsive nowrap w-100 align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="text-center" style="width: 60px;">Foto</th>
                                            <th>Constancia</th>
                                            <th>Documento</th>
                                            <th>Estudiante</th>
                                            <th>Grado</th>
                                            <th>Aula</th>
                                            <th>Monto</th>
                                            <th>Fecha Validación</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Scripts de Carga y DataTables -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Toggle Rango de fecha
        const selectRango = document.getElementById('rango');
        const rangoPersonalizado = document.getElementById('rangoPersonalizado');

        selectRango.addEventListener('change', function () {
            if (this.value === 'custom') {
                rangoPersonalizado.style.display = 'block';
            } else {
                rangoPersonalizado.style.display = 'none';
            }
            cargarData();
        });

        // Eventos de cambios en filtros
        document.getElementById('ciclo_id').addEventListener('change', cargarData);
        if (document.getElementById('operador_id')) {
            document.getElementById('operador_id').addEventListener('change', cargarData);
        }
        document.getElementById('fecha_inicio').addEventListener('change', cargarData);
        document.getElementById('fecha_fin').addEventListener('change', cargarData);

        // Inicializar DataTables
        const tPostulaciones = $('#tablaPostulaciones').DataTable({
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json'
            },
            columns: [
                { data: 'foto', orderable: false, searchable: false, className: 'text-center' },
                { data: 'codigo' },
                { data: 'dni' },
                { data: 'estudiante' },
                { data: 'carrera' },
                { data: 'turno' },
                { data: 'monto' },
                { data: 'fecha' }
            ]
        });

        const tReforzamiento = $('#tablaReforzamiento').DataTable({
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json'
            },
            columns: [
                { data: 'foto', orderable: false, searchable: false, className: 'text-center' },
                { data: 'constancia' },
                { data: 'dni' },
                { data: 'estudiante' },
                { data: 'grado' },
                { data: 'aula' },
                { data: 'monto' },
                { data: 'fecha' }
            ]
        });

        // Función para cargar data desde el servidor
        function cargarData() {
            const formData = new FormData(document.getElementById('formFiltros'));
            const params = new URLSearchParams(formData).toString();

            fetch(`{{ route('reportes.operador-actividad.data') }}?${params}`)
                .then(response => response.json())
                .then(res => {
                    if (res.success) {
                        // Cargar KPIs
                        document.getElementById('kpiTotal').innerText = res.kpis.total_procesados;
                        document.getElementById('kpiPostulaciones').innerText = res.kpis.total_postulaciones;
                        document.getElementById('kpiReforzamientos').innerText = res.kpis.total_reforzamientos;
                        document.getElementById('kpiRecaudado').innerText = 'S/. ' + res.kpis.monto_total;

                        // Mapear fotos y montos para postulaciones
                        const postulacionesMapeadas = res.postulaciones.map(p => {
                            const fotoUrl = p.foto ? p.foto : 'https://ui-avatars.com/api/?name=' + urlencode(p.estudiante) + '&background=e91e63&color=fff';
                            return {
                                ...p,
                                foto: `<img src="${fotoUrl}" class="avatar-table shadow-sm" alt="">`,
                                monto: `S/. ${parseFloat(p.monto).toFixed(2)}`
                            };
                        });

                        // Mapear fotos y montos para reforzamiento
                        const reforzamientosMapeados = res.reforzamientos.map(r => {
                            const fotoUrl = r.foto ? r.foto : 'https://ui-avatars.com/api/?name=' + urlencode(r.estudiante) + '&background=10b981&color=fff';
                            return {
                                ...r,
                                foto: `<img src="${fotoUrl}" class="avatar-table shadow-sm" alt="">`,
                                monto: `S/. ${parseFloat(r.monto).toFixed(2)}`
                            };
                        });

                        // Recargar tablas
                        tPostulaciones.clear().rows.add(postulacionesMapeadas).draw();
                        tReforzamiento.clear().rows.add(reforzamientosMapeados).draw();
                    }
                })
                .catch(err => {
                    console.error('Error al cargar datos de actividad del operador:', err);
                });
        }

        // Cargar data inicial
        cargarData();

        // Utilidad URL Encode para UI Avatars
        function urlencode(str) {
            return encodeURIComponent(str).replace(/[!'()*]/g, function (c) {
                return '%' + c.charCodeAt(0).toString(16);
            });
        }

        // Acción Exportar PDF
        document.getElementById('btnExportPdf').addEventListener('click', function() {
            const form = document.getElementById('formFiltros');
            form.setAttribute('action', "{{ route('reportes.operador-actividad.pdf') }}");
            form.setAttribute('method', 'POST');
            form.setAttribute('target', '_blank');
            form.submit();
        });

        // Acción Exportar Excel
        document.getElementById('btnExportExcel').addEventListener('click', function() {
            const form = document.getElementById('formFiltros');
            form.setAttribute('action', "{{ route('reportes.operador-actividad.excel') }}");
            form.setAttribute('method', 'POST');
            form.setAttribute('target', '_blank');
            form.submit();
        });
    });
</script>
@endsection
