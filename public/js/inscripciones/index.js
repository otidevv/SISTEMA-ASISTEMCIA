// public/js/inscripciones/index.js

// Configuración CSRF para AJAX
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

$(document).ready(function() {
    let table;

    // Inicializar Select2
    $('#estudiante_id').select2({
        dropdownParent: $('#newInscripcionModal'),
        width: '100%',
        language: {
            noResults: function() {
                return "No se encontraron estudiantes disponibles";
            },
            searching: function() {
                return "Buscando...";
            }
        }
    });

    // Inicializar DataTables
    table = $('#inscripciones-datatable').DataTable({
        processing: true,
        serverSide: false,
        ajax: {
            url: default_server + "/json/inscripciones",
            type: 'GET',
            dataSrc: function(json) {
                return json.data;
            }
        },
        columns: [
            { data: 'codigo_inscripcion' },
            {
                data: 'estudiante',
                render: function(data) {
                    return `<div>
                        <strong>${data.nombre_completo}</strong><br>
                        <small class="text-muted">${data.codigo || 'Sin documento'}</small>
                    </div>`;
                }
            },
            { data: 'carrera.nombre' },
            { data: 'ciclo.nombre' },
            { data: 'turno.nombre' },
            {
                data: 'aula',
                render: function(data) {
                    let badgeColor = 'bg-success';
                    if (data.disponible <= 5) badgeColor = 'bg-warning';
                    if (data.disponible <= 0) badgeColor = 'bg-danger';

                    return `<div>
                        <strong>${data.codigo}</strong><br>
                        <small class="text-muted">${data.nombre}</small><br>
                        <span class="badge ${badgeColor}">${data.disponible}/${data.capacidad} disponibles</span>
                    </div>`;
                }
            },
            {
                data: 'fecha_inscripcion',
                render: function(data) {
                    return formatDate(data);
                }
            },
            {
                data: 'estado_inscripcion',
                render: function(data) {
                    let badgeClass = '';
                    let text = '';

                    switch(data) {
                        case 'activo':
                            badgeClass = 'bg-success';
                            text = 'Activo';
                            break;
                        case 'inactivo':
                            badgeClass = 'bg-secondary';
                            text = 'Inactivo';
                            break;
                        case 'retirado':
                            badgeClass = 'bg-danger';
                            text = 'Retirado';
                            break;
                        case 'egresado':
                            badgeClass = 'bg-primary';
                            text = 'Egresado';
                            break;
                        case 'trasladado':
                            badgeClass = 'bg-warning';
                            text = 'Trasladado';
                            break;
                    }

                    return `<span class="badge ${badgeClass}">${text}</span>`;
                }
            },
            {
                data: null,
                render: function(data, type, row) {
                    return row.actions;
                }
            }
        ],
        "language": {
            "lengthMenu": "Mostrar _MENU_ registros por página",
            "zeroRecords": "No se encontraron resultados",
            "info": "Mostrando _START_ a _END_ de _TOTAL_ registros",
            "infoEmpty": "Mostrando 0 a 0 de 0 registros",
            "infoFiltered": "(filtrado de _MAX_ registros totales)",
            "search": "Buscar:",
            "paginate": {
                "first": "Primero",
                "last": "Último",
                "previous": "<i class='uil uil-angle-left'>",
                "next": "<i class='uil uil-angle-right'>"
            },
            "processing": "Procesando...",
            "emptyTable": "No hay datos disponibles",
            "loadingRecords": "Cargando..."
        },
        "drawCallback": function () {
            $('.dataTables_paginate > .pagination').addClass('pagination-rounded');
        }
    });

    // Cargar datos para los selectores
    cargarEstudiantes();
    cargarCarreras();
    cargarCiclos();
    cargarTurnos();
    cargarAulas();
    cargarFiltros();

    // Función para cargar estudiantes sin inscripción
    function cargarEstudiantes() {
        $.ajax({
            url: default_server + "/json/estudiantes-sin-inscripcion",
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    $('#estudiante_id').empty().append('<option value="">Seleccione un estudiante...</option>');
                    response.data.forEach(function(estudiante) {
                        $('#estudiante_id').append(
                            `<option value="${estudiante.id}">${estudiante.nombre_completo} - ${estudiante.codigo || 'Sin documento'}</option>`
                        );
                    });
                }
            },
            error: function() {
                toastr.error('Error al cargar estudiantes');
            }
        });
    }

    // Función para cargar carreras
    function cargarCarreras() {
        $.ajax({
            url: default_server + "/json/carreras/activas/lista",
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    const options = '<option value="">Seleccione una carrera...</option>' +
                        response.data.map(carrera =>
                            `<option value="${carrera.id}">${carrera.nombre}</option>`
                        ).join('');

                    $('#carrera_id, #edit_carrera_id').html(options);

                    // Para filtros
                    $('#filtro-carrera').html('<option value="">Todas las carreras</option>' +
                        response.data.map(carrera =>
                            `<option value="${carrera.id}">${carrera.nombre}</option>`
                        ).join(''));
                }
            }
        });
    }

    // Función para cargar ciclos
    function cargarCiclos() {
        $.ajax({
            url: default_server + "/json/ciclos",
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    const ciclosActivos = response.data.filter(ciclo =>
                        ciclo.estado === 'en_curso' || ciclo.estado === 'planificado'
                    );

                    const options = '<option value="">Seleccione un ciclo...</option>' +
                        ciclosActivos.map(ciclo =>
                            `<option value="${ciclo.id}" ${ciclo.es_activo ? 'selected' : ''}>${ciclo.nombre}</option>`
                        ).join('');

                    $('#ciclo_id, #edit_ciclo_id').html(options);

                    // Para filtros (todos los ciclos)
                    $('#filtro-ciclo').html('<option value="">Todos los ciclos</option>' +
                        response.data.map(ciclo =>
                            `<option value="${ciclo.id}">${ciclo.nombre}</option>`
                        ).join(''));
                }
            }
        });
    }

    // Función para cargar turnos
    function cargarTurnos() {
        $.ajax({
            url: default_server + "/json/turnos/activos/lista",
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    const options = '<option value="">Seleccione un turno...</option>' +
                        response.data.map(turno =>
                            `<option value="${turno.id}">${turno.nombre}</option>`
                        ).join('');

                    $('#turno_id, #edit_turno_id').html(options);

                    // Para filtros
                    $('#filtro-turno').html('<option value="">Todos los turnos</option>' +
                        response.data.map(turno =>
                            `<option value="${turno.id}">${turno.nombre}</option>`
                        ).join(''));
                }
            }
        });
    }

    // Función para cargar aulas
    function cargarAulas() {
        $.ajax({
            url: default_server + "/json/inscripciones/aulas-disponibles",
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    const aulasOptions = '<option value="">Seleccione un aula...</option>' +
                        response.data.map(aula => {
                            let texto = `${aula.codigo} - ${aula.nombre} (${aula.disponible}/${aula.capacidad} disponibles)`;
                            let disabled = aula.disponible <= 0 ? 'disabled' : '';
                            return `<option value="${aula.id}" ${disabled}>${texto}</option>`;
                        }).join('');

                    $('#aula_id, #edit_aula_id').html(aulasOptions);

                    // Para filtros (todas las aulas)
                    $('#filtro-aula').html('<option value="">Todas las aulas</option>' +
                        response.data.map(aula =>
                            `<option value="${aula.id}">${aula.codigo} - ${aula.nombre}</option>`
                        ).join(''));
                }
            },
            error: function() {
                toastr.error('Error al cargar aulas');
            }
        });
    }

    // Función para cargar filtros adicionales
    function cargarFiltros() {
        // Los filtros de ciclo, carrera, turno y aula ya se cargan en las funciones anteriores
        // El filtro de estado ya está hardcodeado en el HTML
    }

    // Aplicar filtros
    $('#filtro-ciclo, #filtro-carrera, #filtro-turno, #filtro-aula, #filtro-estado').on('change', function() {
        aplicarFiltros();
    });

    function aplicarFiltros() {
        const ciclo = $('#filtro-ciclo').val();
        const carrera = $('#filtro-carrera').val();
        const turno = $('#filtro-turno').val();
        const aula = $('#filtro-aula').val();
        const estado = $('#filtro-estado').val();

        let url = default_server + "/json/inscripciones?";

        if (ciclo) url += `ciclo_id=${ciclo}&`;
        if (carrera) url += `carrera_id=${carrera}&`;
        if (turno) url += `turno_id=${turno}&`;
        if (aula) url += `aula_id=${aula}&`;
        if (estado) url += `estado=${estado}&`;

        table.ajax.url(url).load();
    }

    // Función para formatear fechas
    function formatDate(dateString) {
        if (!dateString) return '';

        if (dateString.includes('-')) {
            const [year, month, day] = dateString.split('-');
            const date = new Date(year, month - 1, day);
            const options = { year: 'numeric', month: '2-digit', day: '2-digit' };
            return date.toLocaleDateString('es-ES', options);
        }

        const date = new Date(dateString + 'T00:00:00');
        const options = { year: 'numeric', month: '2-digit', day: '2-digit' };
        return date.toLocaleDateString('es-ES', options);
    }

    // Limpiar formulario cuando se cierra el modal
    $('#newInscripcionModal').on('hidden.bs.modal', function() {
        $('#newInscripcionForm')[0].reset();
        $('#newInscripcionForm .is-invalid').removeClass('is-invalid');
        $('#newInscripcionForm .invalid-feedback').remove();
        $('#estudiante_id').val(null).trigger('change');
        toastr.clear();
        cargarEstudiantes(); // Recargar estudiantes disponibles
    });

    $('#editInscripcionModal').on('hidden.bs.modal', function() {
        $('#editInscripcionForm')[0].reset();
        $('#editInscripcionForm .is-invalid').removeClass('is-invalid');
        $('#editInscripcionForm .invalid-feedback').remove();
        $('#motivo_retiro_row').hide();
        toastr.clear();
    });

    // Mostrar/ocultar campos según el estado
    $('#edit_estado_inscripcion').on('change', function() {
        const estado = $(this).val();
        if (estado === 'retirado' || estado === 'trasladado') {
            $('#motivo_retiro_row').show();
            $('#edit_fecha_retiro').prop('required', true);
        } else {
            $('#motivo_retiro_row').hide();
            $('#edit_fecha_retiro').prop('required', false);
            $('#edit_fecha_retiro').val('');
            $('#edit_motivo_retiro').val('');
        }
    });

    // Crear nueva inscripción
    $('#saveNewInscripcion').on('click', function() {
        const formData = $('#newInscripcionForm').serialize();

        $.ajax({
            url: default_server + "/json/inscripciones",
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    $('#newInscripcionModal').modal('hide');
                    table.ajax.reload();
                    toastr.success(response.message);
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    $('.is-invalid').removeClass('is-invalid');
                    $('.invalid-feedback').remove();

                    const errors = xhr.responseJSON.errors;
                    let errorSummary = '<ul>';

                    for (const field in errors) {
                        const message = errors[field][0];
                        $('#' + field).addClass('is-invalid');
                        $('#' + field).after('<div class="invalid-feedback">' + message + '</div>');
                        errorSummary += '<li>' + message + '</li>';
                    }

                    errorSummary += '</ul>';
                    toastr.error(errorSummary, 'Error de validación', {
                        closeButton: true,
                        timeOut: 0,
                        extendedTimeOut: 0,
                        enableHtml: true
                    });
                } else {
                    toastr.error('Error al crear la inscripción');
                }
            }
        });
    });

    // Cargar datos para editar
    $('#inscripciones-datatable').on('click', '.edit-inscripcion', function() {
        const id = $(this).data('id');

        $.ajax({
            url: default_server + "/json/inscripciones/" + id,
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    const inscripcion = response.data;

                    $('#edit_inscripcion_id').val(inscripcion.id);
                    $('#edit_codigo_inscripcion').val(inscripcion.codigo_inscripcion);
                    $('#edit_estudiante_nombre').val(inscripcion.estudiante.nombre_completo);
                    $('#edit_carrera_id').val(inscripcion.carrera_id);
                    $('#edit_ciclo_id').val(inscripcion.ciclo_id);
                    $('#edit_turno_id').val(inscripcion.turno_id);
                    $('#edit_aula_id').val(inscripcion.aula_id);
                    $('#edit_fecha_inscripcion').val(inscripcion.fecha_inscripcion);
                    $('#edit_estado_inscripcion').val(inscripcion.estado_inscripcion).trigger('change');
                    $('#edit_fecha_retiro').val(inscripcion.fecha_retiro || '');
                    $('#edit_motivo_retiro').val(inscripcion.motivo_retiro || '');
                    $('#edit_observaciones').val(inscripcion.observaciones || '');

                    $('#editInscripcionModal').modal('show');
                } else {
                    toastr.error('No se pudo cargar la información de la inscripción');
                }
            },
            error: function() {
                toastr.error('Error al obtener los datos de la inscripción');
            }
        });
    });

    // Actualizar inscripción
    $('#updateInscripcion').on('click', function() {
        const id = $('#edit_inscripcion_id').val();
        const formData = $('#editInscripcionForm').serialize();

        $.ajax({
            url: default_server + "/json/inscripciones/" + id,
            type: 'PUT',
            data: formData,
            success: function(response) {
                if (response.success) {
                    $('#editInscripcionModal').modal('hide');
                    table.ajax.reload();
                    toastr.success(response.message);
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    $('.is-invalid').removeClass('is-invalid');
                    $('.invalid-feedback').remove();

                    const errors = xhr.responseJSON.errors;
                    let errorSummary = '<ul>';

                    for (const field in errors) {
                        const message = errors[field][0];
                        const inputField = 'edit_' + field;
                        $('#' + inputField).addClass('is-invalid');
                        $('#' + inputField).after('<div class="invalid-feedback">' + message + '</div>');
                        errorSummary += '<li>' + message + '</li>';
                    }

                    errorSummary += '</ul>';
                    toastr.error(errorSummary, 'Error de validación', {
                        closeButton: true,
                        timeOut: 0,
                        extendedTimeOut: 0,
                        enableHtml: true
                    });
                } else {
                    toastr.error('Error al actualizar la inscripción');
                }
            }
        });
    });

    // Ver detalles de inscripción
    $('#inscripciones-datatable').on('click', '.view-inscripcion', function() {
        const id = $(this).data('id');

        $.ajax({
            url: default_server + "/json/inscripciones/" + id,
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    const inscripcion = response.data;

                    let html = `
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-muted">Información del Estudiante</h6>
                                <p><strong>Nombre:</strong> ${inscripcion.estudiante.nombre_completo}</p>
                                <p><strong>Documento:</strong> ${inscripcion.estudiante.codigo || 'N/A'}</p>
                                <p><strong>Email:</strong> ${inscripcion.estudiante.email}</p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted">Información de la Inscripción</h6>
                                <p><strong>Código:</strong> ${inscripcion.codigo_inscripcion}</p>
                                <p><strong>Fecha:</strong> ${formatDate(inscripcion.fecha_inscripcion)}</p>
                                <p><strong>Estado:</strong> <span class="badge bg-${getEstadoBadgeClass(inscripcion.estado_inscripcion)}">${inscripcion.estado_inscripcion}</span></p>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-muted">Información Académica</h6>
                                <p><strong>Carrera:</strong> ${inscripcion.carrera.nombre}</p>
                                <p><strong>Ciclo:</strong> ${inscripcion.ciclo.nombre}</p>
                                <p><strong>Turno:</strong> ${inscripcion.turno.nombre}</p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted">Información del Aula</h6>
                                <p><strong>Aula:</strong> ${inscripcion.aula.codigo} - ${inscripcion.aula.nombre}</p>
                                <p><strong>Edificio:</strong> ${inscripcion.aula.edificio || 'N/A'}</p>
                                <p><strong>Piso:</strong> ${inscripcion.aula.piso || 'N/A'}</p>
                                <p><strong>Capacidad:</strong> ${inscripcion.aula.capacidad} estudiantes</p>
                            </div>
                        </div>
                    `;

                    if (inscripcion.fecha_retiro) {
                        html += `
                            <hr>
                            <div class="row">
                                <div class="col-md-12">
                                    <h6 class="text-muted">Información de Retiro</h6>
                                    <p><strong>Fecha de Retiro:</strong> ${formatDate(inscripcion.fecha_retiro)}</p>
                                    <p><strong>Motivo:</strong> ${inscripcion.motivo_retiro || 'No especificado'}</p>
                                </div>
                            </div>
                        `;
                    }

                    if (inscripcion.observaciones) {
                        html += `
                            <hr>
                            <div class="row">
                                <div class="col-md-12">
                                    <h6 class="text-muted">Observaciones</h6>
                                    <p>${inscripcion.observaciones}</p>
                                </div>
                            </div>
                        `;
                    }

                    html += `
                        <hr>
                        <div class="row">
                            <div class="col-md-12">
                                <small class="text-muted">
                                    Registrado por: ${inscripcion.registrado_por?.nombre_completo || 'Sistema'} |
                                    Última actualización: ${formatDate(inscripcion.updated_at)}
                                </small>
                            </div>
                        </div>
                    `;

                    $('#inscripcion-details').html(html);
                    $('#viewInscripcionModal').modal('show');
                }
            },
            error: function() {
                toastr.error('Error al cargar los detalles de la inscripción');
            }
        });
    });

    function getEstadoBadgeClass(estado) {
        const clases = {
            'activo': 'success',
            'inactivo': 'secondary',
            'retirado': 'danger',
            'egresado': 'primary',
            'trasladado': 'warning'
        };
        return clases[estado] || 'secondary';
    }

    // Eliminar inscripción
    $('#inscripciones-datatable').on('click', '.delete-inscripcion', function() {
        const id = $(this).data('id');

        if (confirm('¿Está seguro de eliminar esta inscripción? Esta acción no se puede deshacer.')) {
            $.ajax({
                url: default_server + "/json/inscripciones/" + id,
                type: 'DELETE',
                success: function(response) {
                    if (response.success) {
                        table.ajax.reload();
                        toastr.success(response.message);
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 400) {
                        toastr.error(xhr.responseJSON.message);
                    } else {
                        toastr.error('Error al eliminar la inscripción');
                    }
                }
            });
        }
    });
     // Descargar reporte de asistencia
    // Descargar reporte de asistencia
    $('#inscripciones-datatable').on('click', '.download-asistencia', function() {
        const inscripcionId = $(this).data('id');
        const estudianteId = $(this).data('estudiante-id');
        const cicloId = $(this).data('ciclo-id');

        // Mostrar loading
        toastr.info('Generando reporte de asistencia...', 'Por favor espere', {
            timeOut: 2000,
            progressBar: true
        });

        // Crear la URL para descargar el PDF - ACTUALIZADA
        const url = default_server + `/json/inscripciones/pdf/${inscripcionId}/reporte-asistencia`;

        // Descargar el PDF
        window.open(url, '_blank');
    });  // Exportar inscripciones
    $('#exportarInscripciones').on('click', function () {
        const ciclo = $('#filtro-ciclo').val();

        if (!ciclo) {
            toastr.warning('Seleccione un ciclo para exportar asistencias.');
            return;
        }

        // Mostrar notificación de carga
        toastr.info('Generando archivo...', { timeOut: 1500 });

        // Redirigir a la URL que genera el archivo Excel
        window.location.href = `${default_server}/json/inscripciones/exportar/asistencias?ciclo_id=${ciclo}`;
    });


    // Exportar inscripciones

});
