// public/js/turnos/index.js

// Configuración CSRF para AJAX
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

$(document).ready(function () {
    // Inicializar DataTables
    var table = $('#turnos-datatable').DataTable({
        processing: true,
        serverSide: false,
        ajax: {
            url: default_server + "/json/turnos",
            type: 'GET',
            dataSrc: function (json) {
                return json.data;
            }
        },
        columns: [
            {
                data: 'orden',
                render: function (data) {
                    return '<span class="badge bg-secondary">' + data + '</span>';
                }
            },
            { data: 'codigo' },
            { data: 'nombre' },
            {
                data: null,
                render: function (data) {
                    return '<i class="uil uil-clock"></i> ' + data.hora_inicio + ' - ' + data.hora_fin;
                }
            },
            {
                data: 'duracion',
                render: function (data) {
                    return '<span class="badge bg-info">' + data + '</span>';
                }
            },
            {
                data: 'dias_semana',
                render: function (data) {
                    return data || '<span class="text-muted">No especificado</span>';
                }
            },
            {
                data: 'estudiantes_activos',
                render: function (data) {
                    return '<span class="badge bg-primary">' + data + '</span>';
                }
            },
            {
                data: 'estado',
                render: function (data) {
                    return data ?
                        '<span class="badge bg-success">Activo</span>' :
                        '<span class="badge bg-danger">Inactivo</span>';
                }
            },
            {
                data: null,
                render: function (data, type, row) {
                    return row.actions;
                }
            }
        ],
        order: [[0, 'asc']], // Ordenar por columna 'orden'
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

    // Limpiar formulario cuando se cierra el modal
    $('#newTurnoModal').on('hidden.bs.modal', function () {
        $('#newTurnoForm')[0].reset();
        $('#newTurnoForm .is-invalid').removeClass('is-invalid');
        $('#newTurnoForm .invalid-feedback').remove();
        $('#saveNewTurno .spinner-border').addClass('d-none');
        toastr.clear();
    });

    $('#editTurnoModal').on('hidden.bs.modal', function () {
        $('#editTurnoForm')[0].reset();
        $('#editTurnoForm .is-invalid').removeClass('is-invalid');
        $('#editTurnoForm .invalid-feedback').remove();
        $('#updateTurno .spinner-border').addClass('d-none');
        toastr.clear();
    });

    // Validar horas en tiempo real
    $('#hora_inicio, #hora_fin').on('change', function () {
        validateHoras();
    });

    $('#edit_hora_inicio, #edit_hora_fin').on('change', function () {
        validateEditHoras();
    });

    function validateHoras() {
        const horaInicio = $('#hora_inicio').val();
        const horaFin = $('#hora_fin').val();

        if (horaInicio && horaFin) {
            if (horaInicio >= horaFin) {
                $('#hora_fin').addClass('is-invalid');
                if (!$('#hora_fin').next('.invalid-feedback').length) {
                    $('#hora_fin').after('<div class="invalid-feedback">La hora de fin debe ser posterior a la hora de inicio</div>');
                }
                return false;
            } else {
                $('#hora_fin').removeClass('is-invalid').next('.invalid-feedback').remove();
            }
        }
        return true;
    }

    function validateEditHoras() {
        const horaInicio = $('#edit_hora_inicio').val();
        const horaFin = $('#edit_hora_fin').val();

        if (horaInicio && horaFin) {
            if (horaInicio >= horaFin) {
                $('#edit_hora_fin').addClass('is-invalid');
                if (!$('#edit_hora_fin').next('.invalid-feedback').length) {
                    $('#edit_hora_fin').after('<div class="invalid-feedback">La hora de fin debe ser posterior a la hora de inicio</div>');
                }
                return false;
            } else {
                $('#edit_hora_fin').removeClass('is-invalid').next('.invalid-feedback').remove();
            }
        }
        return true;
    }

    // Crear nuevo turno
    $('#saveNewTurno').on('click', function () {
        if (!validateHoras()) {
            toastr.error('Por favor, corrija los errores en el horario');
            return;
        }

        var btn = $(this);
        var formData = $('#newTurnoForm').serialize();

        // Mostrar spinner
        btn.find('.spinner-border').removeClass('d-none');
        btn.prop('disabled', true);

        $.ajax({
            url: default_server + "/json/turnos",
            type: 'POST',
            data: formData,
            success: function (response) {
                if (response.success) {
                    $('#newTurnoModal').modal('hide');
                    table.ajax.reload();
                    toastr.success(response.message);
                }
            },
            error: function (xhr) {
                if (xhr.status === 422) {
                    $('.is-invalid').removeClass('is-invalid');
                    $('.invalid-feedback').remove();

                    var errors = xhr.responseJSON.errors;
                    for (var field in errors) {
                        var message = errors[field][0];
                        $('#' + field).addClass('is-invalid');
                        $('#' + field).after('<div class="invalid-feedback">' + message + '</div>');
                    }

                    toastr.error('Por favor, corrija los errores en el formulario');
                } else {
                    toastr.error('Error al crear el turno');
                }
            },
            complete: function () {
                btn.find('.spinner-border').addClass('d-none');
                btn.prop('disabled', false);
            }
        });
    });

    // Cargar datos para editar
    $('#turnos-datatable').on('click', '.edit-turno', function () {
        var id = $(this).data('id');

        $.ajax({
            url: default_server + "/json/turnos/" + id,
            type: 'GET',
            success: function (response) {
                if (response.success) {
                    var turno = response.data;

                    $('#edit_turno_id').val(turno.id);
                    $('#edit_codigo').val(turno.codigo);
                    $('#edit_nombre').val(turno.nombre);
                    $('#edit_hora_inicio').val(turno.hora_inicio);
                    $('#edit_hora_fin').val(turno.hora_fin);

                    // Nuevos campos de tiempo
                    $('#edit_hora_entrada_inicio').val(turno.hora_entrada_inicio || '');
                    $('#edit_hora_entrada_fin').val(turno.hora_entrada_fin || '');
                    $('#edit_hora_tarde_inicio').val(turno.hora_tarde_inicio || '');
                    $('#edit_hora_tarde_fin').val(turno.hora_tarde_fin || '');
                    $('#edit_hora_salida_inicio').val(turno.hora_salida_inicio || '');
                    $('#edit_hora_salida_fin').val(turno.hora_salida_fin || '');

                    $('#edit_dias_semana').val(turno.dias_semana || '');
                    $('#edit_descripcion').val(turno.descripcion || '');
                    $('#edit_orden').val(turno.orden);
                    $('#edit_estado').val(turno.estado ? '1' : '0');

                    $('#editTurnoModal').modal('show');
                }
            },
            error: function () {
                toastr.error('Error al obtener los datos del turno');
            }
        });
    });

    // Actualizar turno
    $('#updateTurno').on('click', function () {
        if (!validateEditHoras()) {
            toastr.error('Por favor, corrija los errores en el horario');
            return;
        }

        var btn = $(this);
        var id = $('#edit_turno_id').val();
        var formData = $('#editTurnoForm').serialize();

        // Mostrar spinner
        btn.find('.spinner-border').removeClass('d-none');
        btn.prop('disabled', true);

        $.ajax({
            url: default_server + "/json/turnos/" + id,
            type: 'PUT',
            data: formData,
            success: function (response) {
                if (response.success) {
                    $('#editTurnoModal').modal('hide');
                    table.ajax.reload();
                    toastr.success(response.message);
                }
            },
            error: function (xhr) {
                if (xhr.status === 422) {
                    $('.is-invalid').removeClass('is-invalid');
                    $('.invalid-feedback').remove();

                    var errors = xhr.responseJSON.errors;
                    for (var field in errors) {
                        var message = errors[field][0];
                        var inputField = 'edit_' + field;
                        $('#' + inputField).addClass('is-invalid');
                        $('#' + inputField).after('<div class="invalid-feedback">' + message + '</div>');
                    }

                    toastr.error('Por favor, corrija los errores en el formulario');
                } else {
                    toastr.error('Error al actualizar el turno');
                }
            },
            complete: function () {
                btn.find('.spinner-border').addClass('d-none');
                btn.prop('disabled', false);
            }
        });
    });

    // Cambiar estado de turno
    $('#turnos-datatable').on('click', '.change-status', function () {
        var id = $(this).data('id');
        var btn = $(this);

        // Desactivar botón temporalmente
        btn.prop('disabled', true);

        $.ajax({
            url: default_server + "/json/turnos/" + id + "/status",
            type: 'PATCH',
            success: function (response) {
                if (response.success) {
                    table.ajax.reload();
                    toastr.success(response.message);
                }
            },
            error: function () {
                toastr.error('Error al cambiar el estado del turno');
            },
            complete: function () {
                btn.prop('disabled', false);
            }
        });
    });

    // Eliminar turno
    $('#turnos-datatable').on('click', '.delete-turno', function () {
        var id = $(this).data('id');
        var btn = $(this);

        if (confirm('¿Está seguro de eliminar este turno? Esta acción no se puede deshacer.')) {
            btn.prop('disabled', true);

            $.ajax({
                url: default_server + "/json/turnos/" + id,
                type: 'DELETE',
                success: function (response) {
                    if (response.success) {
                        table.ajax.reload();
                        toastr.success(response.message);
                    }
                },
                error: function (xhr) {
                    if (xhr.status === 400) {
                        toastr.error(xhr.responseJSON.message);
                    } else {
                        toastr.error('Error al eliminar el turno');
                    }
                },
                complete: function () {
                    btn.prop('disabled', false);
                }
            });
        }
    });

    // Validaciones adicionales
    $('#codigo, #edit_codigo').on('input', function () {
        // Convertir a mayúsculas
        $(this).val($(this).val().toUpperCase());
    });

    $('#dias_semana, #edit_dias_semana').on('input', function () {
        // Convertir a mayúsculas
        $(this).val($(this).val().toUpperCase());
    });

    // Validar que el orden sea un número positivo
    $('#orden, #edit_orden').on('input', function () {
        var val = parseInt($(this).val());
        if (val < 0 || isNaN(val)) $(this).val(0);
    });
});
