// public/js/carreras/index.js

// Configuración CSRF para AJAX
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

$(document).ready(function () {
    // Inicializar DataTables
    var table = $('#carreras-datatable').DataTable({
        processing: true,
        serverSide: false,
        ajax: {
            url: default_server + "/json/carreras",
            type: 'GET',
            dataSrc: function (json) {
                return json.data;
            }
        },
        columns: [
            { data: 'id' },
            { data: 'codigo' },
            { data: 'nombre' },
            {
                data: 'grupo',
                render: function (data) {
                    if (!data) return '<span class="badge bg-secondary">Sin grupo</span>';
                    var badgeClass = data === 'A' ? 'bg-primary' : (data === 'B' ? 'bg-success' : (data === 'C' ? 'bg-info' : 'bg-warning'));
                    var grupoNombre = data === 'A' ? 'Grupo A' : (data === 'B' ? 'Grupo B' : (data === 'C' ? 'Grupo C' : 'Grupo D'));
                    return '<span class="badge ' + badgeClass + '">' + grupoNombre + '</span>';
                }
            },
            {
                data: 'descripcion',
                render: function (data) {
                    if (!data) return '<span class="text-muted">Sin descripción</span>';
                    // Limitar a 50 caracteres
                    return data.length > 50 ? data.substr(0, 50) + '...' : data;
                }
            },
            {
                data: 'estudiantes_activos',
                render: function (data) {
                    return '<span class="badge bg-info">' + data + '</span>';
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
            { data: 'fecha_creacion' },
            {
                data: null,
                render: function (data, type, row) {
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
    $('#newCarreraModal').on('hidden.bs.modal', function () {
        $('#newCarreraForm')[0].reset();
        $('#newCarreraForm .is-invalid').removeClass('is-invalid');
        $('#newCarreraForm .invalid-feedback').remove();
        $('#saveNewCarrera .spinner-border').addClass('d-none');
        toastr.clear();
    });

    $('#editCarreraModal').on('hidden.bs.modal', function () {
        $('#editCarreraForm')[0].reset();
        $('#editCarreraForm .is-invalid').removeClass('is-invalid');
        $('#editCarreraForm .invalid-feedback').remove();
        $('#updateCarrera .spinner-border').addClass('d-none');
        toastr.clear();
    });

    // Crear nueva carrera
    $('#saveNewCarrera').on('click', function () {
        var btn = $(this);
        var formData = new FormData($('#newCarreraForm')[0]);

        // Mostrar spinner
        btn.find('.spinner-border').removeClass('d-none');
        btn.prop('disabled', true);

        $.ajax({
            url: default_server + "/json/carreras",
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                if (response.success) {
                    $('#newCarreraModal').modal('hide');
                    table.ajax.reload();
                    Toast.fire({ icon: 'success', title: response.message });
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

                    Toast.fire({ icon: 'error', title: 'Por favor, corrija los errores en el formulario' });
                } else {
                    Toast.fire({ icon: 'error', title: 'Error al crear la carrera' });
                }
            },
            complete: function () {
                btn.find('.spinner-border').addClass('d-none');
                btn.prop('disabled', false);
            }
        });
    });


    // Cargar datos para editar
    $('#carreras-datatable').on('click', '.edit-carrera', function () {
        var id = $(this).data('id');

        $.ajax({
            url: default_server + "/json/carreras/" + id,
            type: 'GET',
            success: function (response) {
                if (response.success) {
                    var carrera = response.data;

                    $('#edit_carrera_id').val(carrera.id);
                    $('#edit_codigo').val(carrera.codigo);
                    $('#edit_nombre').val(carrera.nombre);
                    $('#edit_grupo').val(carrera.grupo || '');
                    $('#edit_descripcion').val(carrera.descripcion || '');
                    $('#edit_estado').val(carrera.estado ? '1' : '0');

                    // Nuevos campos
                    $('#edit_grado').val(carrera.grado || '');
                    $('#edit_titulo').val(carrera.titulo || '');
                    $('#edit_duracion').val(carrera.duracion || '');
                    $('#edit_mision').val(carrera.mision || '');
                    $('#edit_vision').val(carrera.vision || '');
                    $('#edit_perfil').val(carrera.perfil || '');

                    if (carrera.malla_url) {
                        $('#current_malla_url_display').html('<a href="' + default_server + '/' + carrera.malla_url + '" target="_blank">Ver Malla Actual</a>');
                    } else {
                        $('#current_malla_url_display').text('No hay malla subida.');
                    }

                    if (carrera.imagen_url) {
                        $('#current_imagen_url_display').html('<a href="' + default_server + '/' + carrera.imagen_url + '" target="_blank">Ver Imagen Actual</a>');
                    } else {
                        $('#current_imagen_url_display').text('No hay imagen subida.');
                    }

                    // Manejo de objetivos (pueden ser array o string)
                    if (carrera.objetivos) {
                        if (Array.isArray(carrera.objetivos)) {
                            $('#edit_objetivos').val(carrera.objetivos.join('\n'));
                        } else {
                            $('#edit_objetivos').val(carrera.objetivos);
                        }
                    } else {
                        $('#edit_objetivos').val('');
                    }

                    // Manejo de campo laboral (pueden ser array o string)
                    if (carrera.campo_laboral) {
                        if (Array.isArray(carrera.campo_laboral)) {
                            $('#edit_campo_laboral').val(carrera.campo_laboral.join('\n'));
                        } else {
                            $('#edit_campo_laboral').val(carrera.campo_laboral);
                        }
                    } else {
                        $('#edit_campo_laboral').val('');
                    }

                    $('#editCarreraModal').modal('show');
                }
            },
            error: function () {
                Toast.fire({ icon: 'error', title: 'Error al obtener los datos de la carrera' });
            }
        });
    });

    // Actualizar carrera
    $('#updateCarrera').on('click', function () {
        var btn = $(this);
        var id = $('#edit_carrera_id').val();
        var formData = new FormData($('#editCarreraForm')[0]);
        // Laravel needs _method=PUT when using FormData and POST request
        formData.append('_method', 'PUT');

        // Mostrar spinner
        btn.find('.spinner-border').removeClass('d-none');
        btn.prop('disabled', true);

        $.ajax({
            url: default_server + "/json/carreras/" + id,
            type: 'POST', // POST is used with _method=PUT for file uploads in Laravel
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                if (response.success) {
                    $('#editCarreraModal').modal('hide');
                    table.ajax.reload();
                    Toast.fire({ icon: 'success', title: response.message });
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

                    Toast.fire({ icon: 'error', title: 'Por favor, corrija los errores en el formulario' });
                } else {
                    Toast.fire({ icon: 'error', title: 'Error al actualizar la carrera' });
                }
            },
            complete: function () {
                btn.find('.spinner-border').addClass('d-none');
                btn.prop('disabled', false);
            }
        });
    });

    // Cambiar estado de carrera
    $('#carreras-datatable').on('click', '.change-status', function () {
        var id = $(this).data('id');
        var btn = $(this);

        // Desactivar botón temporalmente
        btn.prop('disabled', true);

        $.ajax({
            url: default_server + "/json/carreras/" + id + "/status",
            type: 'PATCH',
            success: function (response) {
                if (response.success) {
                    table.ajax.reload();
                    Toast.fire({ icon: 'success', title: response.message });
                }
            },
            error: function () {
                Toast.fire({ icon: 'error', title: 'Error al cambiar el estado de la carrera' });
            },
            complete: function () {
                btn.prop('disabled', false);
            }
        });
    });

    // Eliminar carrera
    $('#carreras-datatable').on('click', '.delete-carrera', function () {
        var id = $(this).data('id');
        var btn = $(this);

        // Confirmación con SweetAlert si está disponible, sino usar confirm nativo
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: '¿Está seguro?',
                text: "Esta acción no se puede deshacer",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    eliminarCarrera(id, btn);
                }
            });
        } else {
            if (confirm('¿Está seguro de eliminar esta carrera? Esta acción no se puede deshacer.')) {
                eliminarCarrera(id, btn);
            }
        }
    });

    function eliminarCarrera(id, btn) {
        btn.prop('disabled', true);

        $.ajax({
            url: default_server + "/json/carreras/" + id,
            type: 'DELETE',
            success: function (response) {
                if (response.success) {
                    table.ajax.reload();
                    Toast.fire({ icon: 'success', title: response.message });
                }
            },
            error: function (xhr) {
                if (xhr.status === 400) {
                    Toast.fire({ icon: 'error', title: xhr.responseJSON.message });
                } else {
                    Toast.fire({ icon: 'error', title: 'Error al eliminar la carrera' });
                }
            },
            complete: function () {
                btn.prop('disabled', false);
            }
        });
    }

    // Validaciones adicionales
    $('#codigo, #edit_codigo').on('input', function () {
        // Convertir a mayúsculas y permitir solo letras, números y guiones
        $(this).val($(this).val().toUpperCase().replace(/[^A-Z0-9-]/g, ''));
    });

    // Tooltip para descripción completa
    $('#carreras-datatable').on('mouseenter', 'td', function () {
        var $this = $(this);
        if (this.offsetWidth < this.scrollWidth && !$this.attr('title')) {
            $this.attr('title', $this.text());
        }
    });
});

