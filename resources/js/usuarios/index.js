// public/js/usuarios/index.js
console.log('index.js cargado correctamente');
console.log('default_server está definida:', window.default_server);
// Configuración CSRF para AJAX
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

// Función para cargar roles desde la API (fuera del document.ready)
// Función para cargar roles desde la API
function loadRoles(selectElement, callback) {
    $.ajax({
        url: default_server + "/json/roles",
        type: 'GET',
        success: function(response) {
            if (response.success) {
                $(selectElement).empty();

                $.each(response.data, function(i, role) {
                    $(selectElement).append(new Option(role.nombre, role.id));
                });

                // Si está usando Select2, inicializar o actualizar
                if ($.fn.select2) {
                    $(selectElement).select2({
                        dropdownParent: $(selectElement).closest('.modal')
                    });
                }

                // Llamar al callback si existe - esto es crucial
                if (typeof callback === 'function') {
                    callback();
                }
            }
        }
    });
}

$(document).ready(function() {
    console.log('Document ready ejecutado');
    // Funcionalidad para mostrar/ocultar contraseña
    $(document).on('click', '.toggle-password', function() {
        const button = $(this);
        const icon = button.find('i');
        const input = $('#' + button.data('target'));

        // Cambiar tipo de input
        if (input.attr('type') === 'password') {
            input.attr('type', 'text');
            icon.removeClass('uil-eye').addClass('uil-eye-slash');
        } else {
            input.attr('type', 'password');
            icon.removeClass('uil-eye-slash').addClass('uil-eye');
        }
    });

    // Inicializar DataTables con AJAX
    var table = $('#usuarios-datatable').DataTable({
        processing: true,
        serverSide: false,
        ajax: {
            url: default_server + "/json/usuarios",
            type: 'GET',
            dataSrc: function(json) {
                return json.data;
            }
        },
        columns: [
            { data: 'id' },
            { data: 'username' },
            { data: 'full_name' },
            { data: 'email' },
            {
                data: 'roles',
                render: function(data) {
                    let badges = '';
                    data.forEach(function(role) {
                        badges += '<span class="badge bg-info me-1">' + role + '</span>';
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
            { data: 'numero_documento' },
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

    // Cargar roles al abrir los modales
    $('#newUserModal').on('show.bs.modal', function() {
        loadRoles('#roles');
    });

    $('#editUserModal').on('show.bs.modal', function() {
        loadRoles('#edit_roles');
    });

    // Limpiar formulario cuando se cierran los modales
    $('#newUserModal').on('hidden.bs.modal', function() {
        // Limpiar toastr
        toastr.clear();

        // Resetear el formulario
        $('#newUserForm')[0].reset();

        // Limpiar Select2 si existe
        if ($.fn.select2) {
            $('#roles').val(null).trigger('change');
        }

        // Eliminar clases y mensajes de validación
        $('#newUserForm .is-invalid').removeClass('is-invalid');
        $('#newUserForm .invalid-feedback').remove();
    });

    $('#editUserModal').on('hidden.bs.modal', function() {
        // Limpiar toastr
        toastr.clear();

        // Resetear el formulario
        $('#editUserForm')[0].reset();

        // Limpiar Select2 si existe
        if ($.fn.select2) {
            $('#edit_roles').val(null).trigger('change');
        }

        // Eliminar clases y mensajes de validación
        $('#editUserForm .is-invalid').removeClass('is-invalid');
        $('#editUserForm .invalid-feedback').remove();
    });

    // Crear nuevo usuario
    $('#saveNewUser').on('click', function() {
        var formData = $('#newUserForm').serialize();

        $.ajax({
            url: default_server + "/json/usuarios",
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    // Cerrar modal
                    $('#newUserModal').modal('hide');

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

                        // Personalizar mensajes de unique
                        if (message.includes('validation.unique')) {
                            if (field === 'username') {
                                message = 'Este nombre de usuario ya está en uso.';
                            } else if (field === 'email') {
                                message = 'Este correo electrónico ya está registrado.';
                            } else if (field === 'numero_documento') {
                                message = 'Este número de documento ya existe en el sistema.';
                            } else {
                                message = 'Este valor ya está en uso.';
                            }
                        }

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
                    toastr.error('Error al crear el usuario: ' + xhr.statusText);
                }
            }
        });
    });

    // Cargar datos para editar usuario
    // Actualizar la función que carga datos del usuario para editar
    $('#usuarios-datatable').on('click', '.edit-user', function() {
        var id = $(this).data('id');

        // Obtener datos del usuario
        $.ajax({
            url: default_server + "/json/usuarios/" + id,
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    var user = response.data;
                    console.log("Datos del usuario:", user); // Para depuración

                    // Llenar el formulario con los datos
                    $('#edit_user_id').val(user.id);
                    $('#edit_username').val(user.username);
                    $('#edit_email').val(user.email);
                    $('#edit_nombre').val(user.nombre);
                    $('#edit_apellido_paterno').val(user.apellido_paterno);
                    $('#edit_apellido_materno').val(user.apellido_materno);
                    $('#edit_tipo_documento').val(user.tipo_documento);
                    $('#edit_numero_documento').val(user.numero_documento);
                    $('#edit_telefono').val(user.telefono);
                    $('#edit_estado').val(user.estado ? '1' : '0');
                    $('#edit_fecha_nacimiento').val(user.fecha_nacimiento);
                    // Por esta:
                    if (user.fecha_nacimiento) {
                        // Asegurarse de que tenga formato YYYY-MM-DD
                        let fecha = user.fecha_nacimiento;
                        // Si viene como objeto Date o string en otro formato, convertirla
                        if (typeof fecha === 'string' && !fecha.match(/^\d{4}-\d{2}-\d{2}$/)) {
                            fecha = new Date(fecha).toISOString().split('T')[0];
                        }
                        $('#edit_fecha_nacimiento').val(fecha);
                        console.log("Fecha asignada:", fecha); // Para depuración
                    } else {
                        $('#edit_fecha_nacimiento').val('');
                    }
                    $('#edit_genero').val(user.genero);
                    $('#edit_direccion').val(user.direccion);

                    // Primero, abrir el modal para que esté disponible para Select2
                    $('#editUserModal').modal('show');

                    // Cargar y seleccionar los roles DESPUÉS de que el modal esté visible
                    loadRoles('#edit_roles', function() {
                        console.log("Role IDs:", user.role_ids); // Para depuración
                        // Seleccionar los roles del usuario
                        if (user.role_ids && user.role_ids.length > 0) {
                            $('#edit_roles').val(user.role_ids).trigger('change');
                        }
                    });
                } else {
                    toastr.error('No se pudo cargar la información del usuario');
                }
            },
            error: function() {
                toastr.error('Error al obtener los datos del usuario');
            }
        });
    });

    // Actualizar usuario
    $('#updateUser').on('click', function() {
        var id = $('#edit_user_id').val();
        var formData = $('#editUserForm').serialize();

        $.ajax({
            url: default_server + "/json/usuarios/" + id,
            type: 'PUT',
            data: formData,
            success: function(response) {
                if (response.success) {
                    // Cerrar modal
                    $('#editUserModal').modal('hide');
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

                        // Personalizar mensajes de unique
                        if (message.includes('validation.unique')) {
                            if (field === 'username') {
                                message = 'Este nombre de usuario ya está en uso.';
                            } else if (field === 'email') {
                                message = 'Este correo electrónico ya está registrado.';
                            } else if (field === 'numero_documento') {
                                message = 'Este número de documento ya existe en el sistema.';
                            } else {
                                message = 'Este valor ya está en uso.';
                            }
                        }

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
                    toastr.error('Error al actualizar el usuario');
                }
            }
        });
    });

    // Cambiar estado de usuario
    $('#usuarios-datatable').on('click', '.change-status', function() {
        var id = $(this).data('id');

        $.ajax({
            url: default_server + "/json/usuarios/" + id + "/status",
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
                toastr.error('Error al cambiar el estado del usuario');
            }
        });
    });

    // Eliminar usuario
    $('#usuarios-datatable').on('click', '.delete-user', function() {
        var id = $(this).data('id');

        if (confirm('¿Estás seguro de eliminar este usuario?')) {
            $.ajax({
                url: default_server + "/json/usuarios/" + id,
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
                    toastr.error('Error al eliminar el usuario');
                }
            });
        }
    });

    // Funcionalidad para consultar DNI
    $('#btn-buscar-dni').on('click', function() {
        const numeroDNI = $('#numero_documento').val().trim();

        if (numeroDNI.length !== 8) {
            toastr.error('El DNI debe tener 8 dígitos', 'Error');
            return;
        }

        consultarDNI(numeroDNI);
    });

    // Permitir consultar al presionar Enter en el campo
    $('#numero_documento').on('keypress', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            $('#btn-buscar-dni').click();
        }
    });
});


// Función para consultar la API y llenar el formulario
function consultarDNI(numeroDNI) {
    // Mostrar indicador de carga
    $('#numero_documento').addClass('is-loading');
    $('#btn-buscar-dni').prop('disabled', true).html('<i class="uil uil-spinner-alt spin"></i>');

    // Realizar la consulta a la API
    $.ajax({
        url: `/api/consulta/${numeroDNI}`,
        type: 'GET',
        dataType: 'json',
        success: function(data) {
            console.log("Datos recibidos:", data);

            // Llenar los campos del formulario con los datos obtenidos
            $('#nombre').val(data.NOMBRES);
            $('#apellido_paterno').val(data.AP_PAT);
            $('#apellido_materno').val(data.AP_MAT);

            // Generar username a partir del nombre
            const primerNombre = data.NOMBRES.split(' ')[0].toLowerCase();
            const username = primerNombre + '.' + data.AP_PAT.toLowerCase();
            $('#username').val('est_' + numeroDNI);

            // Email sugerido
            $('#email').val(numeroDNI + '@cepre.unamad.edu.pe');

            // Convertir formato de fecha
            if (data.FECHA_NAC) {
                $('#fecha_nacimiento').val(data.FECHA_NAC);
            }

            // Convertir código de género a texto
            if (data.SEXO === "1") {
                $('#genero').val('Masculino');
            } else if (data.SEXO === "2") {
                $('#genero').val('Femenino');
            }

            // Dirección
            if (data.DIRECCION) {
                $('#direccion').val(data.DIRECCION);
            }

            // Mostrar notificación de éxito
            toastr.success('Se han completado los campos automáticamente', '¡Datos encontrados!');
        },
        error: function(xhr, status, error) {
            console.error("Error en la consulta:", error);
            toastr.error('No se pudieron obtener los datos. Verifique el número de documento.', 'Error');
        },
        complete: function() {
            // Quitar indicador de carga
            $('#numero_documento').removeClass('is-loading');
            $('#btn-buscar-dni').prop('disabled', false).html('<i class="uil uil-search"></i>');
        }
    });
}
