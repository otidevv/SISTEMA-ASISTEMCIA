// public/js/parentescos/index.js
console.log('index.js para parentescos cargado correctamente');
console.log('default_server está definida:', window.default_server);

// Configuración CSRF para AJAX
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

// Función para cargar estudiantes desde la API
// Función para cargar estudiantes desde la API
function loadEstudiantes(selectElement, callback) {
    $.ajax({
        url: default_server + "/json/estudiantes", // Ahora usa el endpoint correcto
        type: 'GET',
        success: function(response) {
            if (response.success) {
                $(selectElement).empty();
                $(selectElement).append('<option value="">Seleccione un estudiante...</option>');

                $.each(response.data, function(i, estudiante) {
                    var nombreCompleto = estudiante.nombre + ' ' + estudiante.apellido_paterno + ' ' + estudiante.apellido_materno;
                    $(selectElement).append(new Option(nombreCompleto, estudiante.id));
                });

                // Si está usando Select2, inicializar o actualizar
                if ($.fn.select2) {
                    $(selectElement).select2({
                        dropdownParent: $(selectElement).closest('.modal')
                    });
                }

                if (typeof callback === 'function') {
                    callback();
                }
            }
        }
    });
}

// Función para cargar padres desde la API
function loadPadres(selectElement, callback) {
    $.ajax({
        url: default_server + "/json/padres",
        type: 'GET',
        success: function(response) {
            if (response.success) {
                $(selectElement).empty();
                $(selectElement).append('<option value="">Seleccione un padre/madre/tutor...</option>');

                $.each(response.data, function(i, padre) {
                    var nombreCompleto = padre.nombre + ' ' + padre.apellido_paterno + ' ' + padre.apellido_materno;
                    $(selectElement).append(new Option(nombreCompleto, padre.id));
                });

                // Si está usando Select2, inicializar o actualizar
                if ($.fn.select2) {
                    $(selectElement).select2({
                        dropdownParent: $(selectElement).closest('.modal')
                    });
                }

                if (typeof callback === 'function') {
                    callback();
                }
            }
        }
    });
}

$(document).ready(function() {
    console.log('Document ready ejecutado');

    // Inicializar DataTables
    var table = $('#parentescos-datatable').DataTable({
        processing: true,
        serverSide: false,
        ajax: {
            url: default_server + "/json/parentescos",
            type: 'GET',
            dataSrc: function(json) {
                return json.data;
            }
        },
        columns: [
            { data: 'id' },
            { data: 'estudiante' },
            { data: 'padre' },
            { data: 'tipo_parentesco' },
            {
                data: 'acceso_portal',
                render: function(data) {
                    return data ?
                        '<span class="badge bg-success">Sí</span>' :
                        '<span class="badge bg-danger">No</span>';
                }
            },
            {
                data: 'recibe_notificaciones',
                render: function(data) {
                    return data ?
                        '<span class="badge bg-success">Sí</span>' :
                        '<span class="badge bg-danger">No</span>';
                }
            },
            {
                data: 'contacto_emergencia',
                render: function(data) {
                    return data ?
                        '<span class="badge bg-success">Sí</span>' :
                        '<span class="badge bg-light text-dark">No</span>';
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
        language: {
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

    // Cargar selectores al abrir los modales
    $('#newParentescoModal').on('show.bs.modal', function() {
        loadEstudiantes('#estudiante_id');
        loadPadres('#padre_id');
    });

    $('#editParentescoModal').on('show.bs.modal', function() {
        loadEstudiantes('#edit_estudiante_id');
        loadPadres('#edit_padre_id');
    });

    // Limpiar formularios al cerrar modales
    $('#newParentescoModal').on('hidden.bs.modal', function() {
        // Limpiar toastr
        toastr.clear();

        // Resetear el formulario
        $('#newParentescoForm')[0].reset();

        // Limpiar Select2
        if ($.fn.select2) {
            $('#estudiante_id, #padre_id').val(null).trigger('change');
        }

        // Eliminar clases y mensajes de validación
        $('#newParentescoForm .is-invalid').removeClass('is-invalid');
        $('#newParentescoForm .invalid-feedback').remove();
    });

    $('#editParentescoModal').on('hidden.bs.modal', function() {
        // Limpiar toastr
        toastr.clear();

        // Resetear el formulario
        $('#editParentescoForm')[0].reset();

        // Limpiar Select2
        if ($.fn.select2) {
            $('#edit_estudiante_id, #edit_padre_id').val(null).trigger('change');
        }

        // Eliminar clases y mensajes de validación
        $('#editParentescoForm .is-invalid').removeClass('is-invalid');
        $('#editParentescoForm .invalid-feedback').remove();
    });

    // Crear nuevo parentesco
    $('#saveNewParentesco').on('click', function() {
        var formData = {
            estudiante_id: $('#estudiante_id').val(),
            padre_id: $('#padre_id').val(),
            tipo_parentesco: $('#tipo_parentesco').val(),
            // Incluir explícitamente valores booleanos
            acceso_portal: $('#acceso_portal').is(':checked') ? 1 : 0,
            recibe_notificaciones: $('#recibe_notificaciones').is(':checked') ? 1 : 0,
            contacto_emergencia: $('#contacto_emergencia').is(':checked') ? 1 : 0
        };

        $.ajax({
            url: default_server + "/json/parentescos",
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    // Cerrar modal
                    $('#newParentescoModal').modal('hide');

                    // Recargar la tabla
                    table.ajax.reload();

                    // Mostrar mensaje de éxito
                    toastr.success(response.message);
                } else {
                    // Mostrar errores
                    toastr.error('Hay errores en el formulario');
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    // Limpiar errores previos
                    $('.is-invalid').removeClass('is-invalid');
                    $('.invalid-feedback').remove();

                    var errors = xhr.responseJSON.errors;
                    console.log("Errores de validación:", errors);

                    // Variable para mostrar resumen de errores
                    var errorSummary = '<ul>';

                    // Mostrar cada error en su campo correspondiente
                    for (var field in errors) {
                        var message = errors[field][0];

                        $('#' + field).addClass('is-invalid');
                        $('#' + field).after('<div class="invalid-feedback">' + message + '</div>');

                        // Agregar al resumen
                        errorSummary += '<li><strong>' + field + '</strong>: ' + message + '</li>';
                    }

                    errorSummary += '</ul>';

                    toastr.error(errorSummary, 'Error de validación', {
                        closeButton: true,
                        timeOut: 0,
                        extendedTimeOut: 0,
                        positionClass: "toast-top-center",
                        enableHtml: true
                    });
                } else {
                    toastr.error('Error al crear el parentesco: ' + xhr.statusText);
                }
            }
        });
    });

    // Cargar datos para editar parentesco
    $('#parentescos-datatable').on('click', '.edit-parentesco', function() {
        var id = $(this).data('id');

        // Obtener datos del parentesco
        $.ajax({
            url: default_server + "/json/parentescos/" + id,
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    var parentesco = response.data;
                    console.log("Datos del parentesco:", parentesco);

                    // Llenar el formulario con los datos
                    $('#edit_parentesco_id').val(parentesco.id);

                    // Abrir el modal para que estén disponibles los selectores
                    $('#editParentescoModal').modal('show');

                    // Cargar y seleccionar estudiante y padre
                    loadEstudiantes('#edit_estudiante_id', function() {
                        $('#edit_estudiante_id').val(parentesco.estudiante_id).trigger('change');
                    });

                    loadPadres('#edit_padre_id', function() {
                        $('#edit_padre_id').val(parentesco.padre_id).trigger('change');
                    });

                    // Llenar otros campos
                    $('#edit_tipo_parentesco').val(parentesco.tipo_parentesco);
                    $('#edit_estado').val(parentesco.estado ? '1' : '0');

                    // Checkboxes
                    $('#edit_acceso_portal').prop('checked', parentesco.acceso_portal);
                    $('#edit_recibe_notificaciones').prop('checked', parentesco.recibe_notificaciones);
                    $('#edit_contacto_emergencia').prop('checked', parentesco.contacto_emergencia);
                } else {
                    toastr.error('No se pudo cargar la información del parentesco');
                }
            },
            error: function() {
                toastr.error('Error al obtener los datos del parentesco');
            }
        });
    });

    // Actualizar parentesco
    $('#updateParentesco').on('click', function() {
        var id = $('#edit_parentesco_id').val();
        var formData = {
            estudiante_id: $('#edit_estudiante_id').val(),
            padre_id: $('#edit_padre_id').val(),
            tipo_parentesco: $('#edit_tipo_parentesco').val(),
            estado: $('#edit_estado').val(),
            // Incluir explícitamente valores booleanos
            acceso_portal: $('#edit_acceso_portal').is(':checked') ? 1 : 0,
            recibe_notificaciones: $('#edit_recibe_notificaciones').is(':checked') ? 1 : 0,
            contacto_emergencia: $('#edit_contacto_emergencia').is(':checked') ? 1 : 0
        };

        $.ajax({
            url: default_server + "/json/parentescos/" + id,
            type: 'PUT',
            data: formData,
            success: function(response) {
                if (response.success) {
                    // Cerrar modal
                    $('#editParentescoModal').modal('hide');

                    // Recargar la tabla
                    table.ajax.reload();

                    // Mostrar mensaje de éxito
                    toastr.success(response.message);
                } else {
                    // Mostrar errores
                    toastr.error('Hay errores en el formulario');
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    // Limpiar errores previos
                    $('.is-invalid').removeClass('is-invalid');
                    $('.invalid-feedback').remove();

                    // Mostrar errores de validación
                    var errors = xhr.responseJSON.errors;
                    var errorSummary = '<ul>';

                    for (var field in errors) {
                        var message = errors[field][0];

                        // Adaptar nombres de campo para el formulario de edición
                        var inputField = field;
                        if (field !== 'id' && !field.startsWith('edit_')) {
                            inputField = 'edit_' + field;
                        }

                        $('#' + inputField).addClass('is-invalid');
                        $('#' + inputField).after('<div class="invalid-feedback">' + message + '</div>');

                        errorSummary += '<li><strong>' + field + '</strong>: ' + message + '</li>';
                    }

                    errorSummary += '</ul>';

                    toastr.error(errorSummary, 'Error de validación', {
                        closeButton: true,
                        timeOut: 0,
                        extendedTimeOut: 0,
                        positionClass: "toast-top-center",
                        enableHtml: true
                    });
                } else {
                    toastr.error('Error al actualizar el parentesco');
                }
            }
        });
    });

    // Cambiar estado de parentesco
    $('#parentescos-datatable').on('click', '.change-status', function() {
        var id = $(this).data('id');

        $.ajax({
            url: default_server + "/json/parentescos/" + id + "/status",
            type: 'PATCH',
            success: function(response) {
                if (response.success) {
                    // Recargar la tabla
                    table.ajax.reload();

                    // Mostrar mensaje de éxito
                    toastr.success(response.message);
                }
            },
            error: function() {
                toastr.error('Error al cambiar el estado del parentesco');
            }
        });
    });

    // Eliminar parentesco
    $('#parentescos-datatable').on('click', '.delete-parentesco', function() {
        var id = $(this).data('id');

        if (confirm('¿Estás seguro de eliminar este parentesco?')) {
            $.ajax({
                url: default_server + "/json/parentescos/" + id,
                type: 'DELETE',
                success: function(response) {
                    if (response.success) {
                        // Recargar la tabla
                        table.ajax.reload();

                        // Mostrar mensaje de éxito
                        toastr.success(response.message);
                    }
                },
                error: function() {
                    toastr.error('Error al eliminar el parentesco');
                }
            });
        }
    });
});
