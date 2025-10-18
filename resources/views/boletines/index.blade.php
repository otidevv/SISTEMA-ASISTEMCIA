@extends('layouts.app')

@section('title', 'Gestión de Boletines Académicos')

@push('styles')
    <!-- Estilos de DataTables para Skote -->
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css" rel="stylesheet">
    <style>
        .avatar-sm {
            width: 40px;
            height: 40px;
        }
        .entrega-checkbox {
            width: 20px;
            height: 20px;
            cursor: pointer;
        }
        .entrega-checkbox:hover {
            transform: scale(1.1);
            transition: transform 0.2s;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <!-- Header -->
                <div class="page-title-box d-flex align-items-center justify-content-between mb-4">
                    <div class="d-flex align-items-center">
                        <div class="avatar-sm bg-primary rounded me-3 d-flex align-items-center justify-content-center">
                            <i class="bx bx-file font-size-20 text-white"></i>
                        </div>
                        <div>
                            <h4 class="mb-1 font-size-18">Gestión de Boletines Académicos</h4>
                            <p class="text-muted mb-0 font-size-13">Marque la entrega de boletines por curso para cada estudiante</p>
                        </div>
                    </div>
                </div>

                <!-- Sección de Filtros -->
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-3">
                            <i class="bx bx-filter-alt me-1"></i> Filtros de Búsqueda
                        </h5>
                        
                        <form id="filter-form" class="row g-3">
                            <!-- Ciclo Académico -->
                            <div class="col-md-3 col-sm-6">
                                <label for="ciclo_id" class="form-label">Ciclo Académico <span class="text-danger">*</span></label>
                                <select id="ciclo_id" name="ciclo_id" class="form-select">
                                    <option value="">Seleccione un ciclo</option>
                                    @foreach($ciclos as $ciclo)
                                        <option value="{{ $ciclo->id }}">{{ $ciclo->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Aula -->
                            <div class="col-md-3 col-sm-6">
                                <label for="aula_id" class="form-label">Aula <span class="text-danger">*</span></label>
                                <select id="aula_id" name="aula_id" class="form-select">
                                    <option value="">Seleccione un aula</option>
                                    @foreach($aulas as $aula)
                                        <option value="{{ $aula->id }}">{{ $aula->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Tipo de Examen -->
                            <div class="col-md-3 col-sm-6">
                                <label for="tipo_examen" class="form-label">Tipo de Examen <span class="text-danger">*</span></label>
                                <select id="tipo_examen" name="tipo_examen" class="form-select">
                                    <option value="">Seleccione un examen</option>
                                    <option value="PRIMER EXAMEN">PRIMER EXAMEN</option>
                                    <option value="SEGUNDO EXAMEN">SEGUNDO EXAMEN</option>
                                    <option value="TERCER EXAMEN">TERCER EXAMEN</option>
                                </select>
                            </div>

                            <!-- Botón de Filtrar -->
                            <div class="col-md-3 col-sm-6">
                                <label class="form-label d-block">&nbsp;</label>
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bx bx-search-alt me-1"></i> Filtrar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Sección de Tabla de Datos -->
                <div class="card">
                    <div class="card-body">
                        <div id="table-container">
                            <div id="table-placeholder" class="text-center py-5">
                                <!-- Initial placeholder content is set by JS -->
                            </div>
                            <div class="table-responsive">
                                <table id="boletines-table" class="table table-striped table-bordered dt-responsive nowrap w-100" style="display:none;">
                                    <thead>
                                        <!-- Header will be populated by JS -->
                                    </thead>
                                    <tbody>
                                        <!-- Body will be populated by JS -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <!-- DataTables -->
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            let dataTable;
            const $table = $('#boletines-table');
            const $placeholder = $('#table-placeholder');

            function showPlaceholder(icon, title, message) {
                $placeholder.html(`
                    <div class="text-center py-5">
                        <i class="${icon} display-4 text-muted mb-3"></i>
                        <h5 class="mt-2">${title}</h5>
                        <p class="text-muted">${message}</p>
                    </div>
                `).show();
                $table.hide();
                if (dataTable) {
                    dataTable.destroy();
                    $('#boletines-table thead, #boletines-table tbody').empty();
                }
            }

            showPlaceholder(
                'bx bx-table',
                'Lista de Estudiantes',
                'Utilice los filtros para cargar los datos de los estudiantes.'
            );

            $('#filter-form').on('submit', function(e) {
                e.preventDefault();
                const cicloId = $('#ciclo_id').val();
                const aulaId = $('#aula_id').val();
                const tipoExamen = $('#tipo_examen').val();

                if (!cicloId || !aulaId || !tipoExamen) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Atención',
                        text: 'Por favor, seleccione un ciclo, un aula y un tipo de examen.',
                        confirmButtonColor: '#556ee6'
                    });
                    return;
                }
                loadDataTable(cicloId, aulaId, tipoExamen);
            });

            function loadDataTable(cicloId, aulaId, tipoExamen) {
                if (dataTable) {
                    dataTable.destroy();
                    $('#boletines-table thead, #boletines-table tbody').empty();
                }

                $.ajax({
                    url: '{{ route("boletines.data") }}',
                    type: 'GET',
                    data: {
                        ciclo_id: cicloId,
                        aula_id: aulaId,
                        tipo_examen: tipoExamen,
                        _token: '{{ csrf_token() }}'
                    },
                    beforeSend: function() {
                        $placeholder.hide();
                        $table.hide();
                        Swal.fire({
                            title: 'Cargando Datos...',
                            text: 'Por favor espere.',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });
                    },
                    success: function(response) {
                        Swal.close();
                        if (response.data && response.data.length > 0) {
                            $placeholder.hide();
                            $table.show();

                            const cursos = response.cursos;
                            const data = response.data;

                            let headerRow = '<tr><th>Estudiante</th>';
                            cursos.forEach(curso => {
                                headerRow += `<th class="text-center">${curso.nombre}</th>`;
                            });
                            headerRow += '</tr>';
                            $('#boletines-table thead').html(headerRow);

                            let tableBody = '';
                            data.forEach(row => {
                                let tableRow = `<tr><td><strong>${row.student}</strong></td>`;
                                row.courses.forEach(course => {
                                    const checked = course.entregado ? 'checked' : '';
                                    tableRow += `<td class="text-center">
                                                   <input type="checkbox" class="entrega-checkbox form-check-input" data-inscripcion-id="${row.inscripcion_id}" data-curso-id="${course.id}" ${checked}>
                                                 </td>`;
                                });
                                tableRow += '</tr>';
                                tableBody += tableRow;
                            });
                            $('#boletines-table tbody').html(tableBody);

                            dataTable = $table.DataTable({
                                responsive: true,
                                language: { 
                                    url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
                                },
                                destroy: true,
                                pageLength: 25,
                                order: [[0, 'asc']]
                            });

                            // Toast de éxito
                            const Toast = Swal.mixin({
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 2000,
                                timerProgressBar: true,
                            });
                            Toast.fire({
                                icon: 'success',
                                title: `${data.length} estudiante(s) encontrado(s)`
                            });
                        } else {
                            showPlaceholder(
                                'bx bx-search-alt',
                                'No se encontraron estudiantes',
                                'No hay estudiantes que coincidan con los filtros seleccionados.'
                            );
                        }
                    },
                    error: function(xhr) {
                        Swal.close();
                        showPlaceholder(
                            'bx bx-error-circle',
                            'Error de Carga',
                            'Ocurrió un error al comunicarse con el servidor.'
                        );
                    }
                });
            }

            // Evento para marcar entrega
            $('#boletines-table').on('change', '.entrega-checkbox', function() {
                const checkbox = $(this);
                const inscripcionId = checkbox.data('inscripcion-id');
                const cursoId = checkbox.data('curso-id');
                const entregado = checkbox.is(':checked');
                const tipoExamen = $('#tipo_examen').val();

                checkbox.prop('disabled', true);

                $.ajax({
                    url: '{{ route("boletines.marcar") }}',
                    type: 'POST',
                    data: {
                        inscripcion_id: inscripcionId,
                        curso_id: cursoId,
                        tipo_examen: tipoExamen,
                        entregado: entregado,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            const Toast = Swal.mixin({
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 1500,
                                timerProgressBar: true,
                            });
                            Toast.fire({
                                icon: 'success',
                                title: entregado ? 'Entrega registrada' : 'Entrega desmarcada'
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'No se pudo guardar el cambio.',
                                confirmButtonColor: '#556ee6'
                            });
                            checkbox.prop('checked', !entregado);
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Ocurrió un problema de comunicación.',
                            confirmButtonColor: '#556ee6'
                        });
                        checkbox.prop('checked', !entregado);
                    },
                    complete: function() {
                        checkbox.prop('disabled', false);
                    }
                });
            });
        });
    </script>
@endpush