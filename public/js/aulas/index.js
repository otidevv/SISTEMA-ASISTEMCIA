// public/js/aulas/index.js

// Configuración CSRF para AJAX
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

$(document).ready(function() {
    // Inicializar DataTables
    var table = $('#aulas-datatable').DataTable({
        processing: true,
        serverSide: false,
        ajax: {
            url: default_server + "/json/aulas",
            type: 'GET',
            dataSrc: function(json) {
                return json.data;
            }
        },
        columns: [
            { data: 'id' },
            { data: 'codigo' },
            { data: 'nombre' },
            {
                data: 'tipo_display',
                render: function(data, type, row) {
                    let badgeClass = '';
                    let icon = '';

                    switch(row.tipo) {
                        case 'aula':
                            badgeClass = 'bg-primary';
                            icon = '<i class="uil uil-book-reader"></i>';
                            break;
                        case 'laboratorio':
                            badgeClass = 'bg-info';
                            icon = '<i class="uil uil-flask"></i>';
                            break;
                        case 'taller':
                            badgeClass = 'bg-warning';
                            icon = '<i class="uil uil-wrench"></i>';
                            break;
                        case 'auditorio':
                            badgeClass = 'bg-success';
                            icon = '<i class="uil uil-users-alt"></i>';
                            break;
                    }

                    return `<span class="badge ${badgeClass}">${icon} ${data}</span>`;
                }
            },
            {
                data: 'capacidad',
                render: function(data) {
                    return `<span class="badge bg-secondary">${data} personas</span>`;
                }
            },
            {
                data: null,
                render: function(data) {
                    let ubicacion = '';
                    if (data.edificio) {
                        ubicacion += data.edificio;
                        if (data.piso) {
                            ubicacion += ' - Piso ' + data.piso;
                        }
                    } else if (data.piso) {
                        ubicacion = 'Piso ' + data.piso;
                    } else {
                        ubicacion = '<span class="text-muted">No especificada</span>';
                    }
                    return ubicacion;
                }
            },
            {
                data: 'caracteristicas',
                render: function(data) {
                    if (!data || data.length === 0) {
                        return '<span class="text-muted">Ninguna</span>';
                    }

                    let badges = '';
                    data.forEach(function(caracteristica) {
                        let icon = '';
                        let color = 'bg-info';

                        if (caracteristica === 'Proyector') {
                            icon = '<i class="uil uil-presentation"></i>';
                        } else if (caracteristica === 'Aire Acondicionado') {
                            icon = '<i class="uil uil-snowflake"></i>';
                            color = 'bg-primary';
                        } else if (caracteristica === 'Accesible') {
                            icon = '<i class="uil uil-wheelchair"></i>';
                            color = 'bg-success';
                        }

                        badges += `<span class="badge ${color} me-1">${icon} ${caracteristica}</span>`;
                    });

                    return badges;
                }
            },
            {
                data: 'estado',
                render: function(data) {
                    return data ?
                        '<span class="badge bg-success">Activo</span>' :
                        '<span class="badge bg-danger">Inactivo</span>';
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

    // Limpiar formulario cuando se cierra el modal
    $('#newAulaModal').on('hidden.bs.modal', function() {
        $('#newAulaForm')[0].reset();
        $('#newAulaForm .is-invalid').removeClass('is-invalid');
        $('#newAulaForm .invalid-feedback').remove();
        $('#saveNewAula .spinner-border').addClass('d-none');
        // Restablecer checkbox de accesible por defecto
        $('#accesible').prop('checked', true);
        toastr.clear();
    });

    $('#editAulaModal').on('hidden.bs.modal', function() {
        $('#editAulaForm')[0].reset();
        $('#editAulaForm .is-invalid').removeClass('is-invalid');
        $('#editAulaForm .invalid-feedback').remove();
        $('#updateAula .spinner-border').addClass('d-none');
        toastr.clear();
    });

    // Crear nueva aula
    $('#saveNewAula').on('click', function() {
        var btn = $(this);
        var formData = $('#newAulaForm').serialize();

        // Mostrar spinner
        btn.find('.spinner-border').removeClass('d-none');
        btn.prop('disabled', true);

        $.ajax({
            url: default_server + "/json/aulas",
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    $('#newAulaModal').modal('hide');
                    table.ajax.reload();
                    toastr.success(response.message);
                }
            },
            error: function(xhr) {
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
                    toastr.error('Error al crear el aula');
                }
            },
            complete: function() {
                btn.find('.spinner-border').addClass('d-none');
                btn.prop('disabled', false);
            }
        });
    });

    // Cargar datos para editar
    $('#aulas-datatable').on('click', '.edit-aula', function() {
        var id = $(this).data('id');

        $.ajax({
            url: default_server + "/json/aulas/" + id,
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    var aula = response.data;

                    $('#edit_aula_id').val(aula.id);
                    $('#edit_codigo').val(aula.codigo);
                    $('#edit_nombre').val(aula.nombre);
                    $('#edit_tipo').val(aula.tipo);
                    $('#edit_capacidad').val(aula.capacidad);
                    $('#edit_edificio').val(aula.edificio || '');
                    $('#edit_piso').val(aula.piso || '');
                    $('#edit_descripcion').val(aula.descripcion || '');
                    $('#edit_equipamiento').val(aula.equipamiento || '');
                    $('#edit_estado').val(aula.estado ? '1' : '0');

                    // Manejar checkboxes
                    $('#edit_tiene_proyector').prop('checked', aula.tiene_proyector);
                    $('#edit_tiene_aire_acondicionado').prop('checked', aula.tiene_aire_acondicionado);
                    $('#edit_accesible').prop('checked', aula.accesible);

                    $('#editAulaModal').modal('show');
                }
            },
            error: function() {
                toastr.error('Error al obtener los datos del aula');
            }
        });
    });

    // Actualizar aula
    $('#updateAula').on('click', function() {
        var btn = $(this);
        var id = $('#edit_aula_id').val();
        var formData = $('#editAulaForm').serialize();

        // Mostrar spinner
        btn.find('.spinner-border').removeClass('d-none');
        btn.prop('disabled', true);

        $.ajax({
            url: default_server + "/json/aulas/" + id,
            type: 'PUT',
            data: formData,
            success: function(response) {
                if (response.success) {
                    $('#editAulaModal').modal('hide');
                    table.ajax.reload();
                    toastr.success(response.message);
                }
            },
            error: function(xhr) {
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
                    toastr.error('Error al actualizar el aula');
                }
            },
            complete: function() {
                btn.find('.spinner-border').addClass('d-none');
                btn.prop('disabled', false);
            }
        });
    });

    // Cambiar estado de aula
    $('#aulas-datatable').on('click', '.change-status', function() {
        var id = $(this).data('id');
        var btn = $(this);

        // Desactivar botón temporalmente
        btn.prop('disabled', true);

        $.ajax({
            url: default_server + "/json/aulas/" + id + "/status",
            type: 'PATCH',
            success: function(response) {
                if (response.success) {
                    table.ajax.reload();
                    toastr.success(response.message);
                }
            },
            error: function() {
                toastr.error('Error al cambiar el estado del aula');
            },
            complete: function() {
                btn.prop('disabled', false);
            }
        });
    });

    // Eliminar aula
    $('#aulas-datatable').on('click', '.delete-aula', function() {
        var id = $(this).data('id');
        var btn = $(this);

        if (confirm('¿Está seguro de eliminar esta aula? Esta acción no se puede deshacer.')) {
            btn.prop('disabled', true);

            $.ajax({
                url: default_server + "/json/aulas/" + id,
                type: 'DELETE',
                success: function(response) {
                    if (response.success) {
                        table.ajax.reload();
                        toastr.success(response.message);
                    }
                },
                error: function(xhr) {
                    toastr.error('Error al eliminar el aula');
                },
                complete: function() {
                    btn.prop('disabled', false);
                }
            });
        }
    });

    // Validaciones adicionales
    $('#codigo, #edit_codigo').on('input', function() {
        // Convertir a mayúsculas
        $(this).val($(this).val().toUpperCase());
    });

    // Validar capacidad
    $('#capacidad, #edit_capacidad').on('input', function() {
        var val = parseInt($(this).val());
        if (val < 1) $(this).val(1);
        if (val > 1000) $(this).val(1000);
    });

    // Cambiar opciones según el tipo de aula
    $('#tipo, #edit_tipo').on('change', function() {
        var tipo = $(this).val();
        var capacidadInput = $(this).closest('form').find('input[name="capacidad"]');

        // Sugerir capacidades típicas según el tipo
        switch(tipo) {
            case 'aula':
                capacidadInput.attr('placeholder', 'Ej: 30-40');
                break;
            case 'laboratorio':
                capacidadInput.attr('placeholder', 'Ej: 20-30');
                break;
            case 'taller':
                capacidadInput.attr('placeholder', 'Ej: 15-25');
                break;
            case 'auditorio':
                capacidadInput.attr('placeholder', 'Ej: 100-500');
                break;
        }
    });

    // Tooltip para equipamiento
    $('[data-bs-toggle="tooltip"]').tooltip();
});
