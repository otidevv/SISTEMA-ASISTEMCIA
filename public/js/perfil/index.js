// public/js/perfil/index.js
$(document).ready(function() {
    // Configuración CSRF para AJAX
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Cargar información de perfil
    function cargarPerfil() {
        $.ajax({
            url: default_server + "/json/perfil",
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    const user = response.data;
                    // Actualizar campos con la información del usuario
                    $('#email').val(user.email);
                    $('#telefono').val(user.telefono);
                    // Actualizar otros campos...
                }
            },
            error: function() {
                toastr.error('Error al cargar la información del perfil');
            }
        });
    }

    // Actualizar información personal
    $('#formInformacion').on('submit', function(e) {
        e.preventDefault();
        const formData = $(this).serialize();

        $.ajax({
            url: default_server + "/json/perfil/update",
            type: 'PUT',
            data: formData,
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    // Actualizar la información mostrada
                    cargarPerfil();
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    // Limpiar errores previos
                    $('.is-invalid').removeClass('is-invalid');
                    $('.invalid-feedback').remove();

                    // Mostrar errores de validación
                    const errors = xhr.responseJSON.errors;
                    for (const field in errors) {
                        const message = errors[field][0];
                        $('#' + field).addClass('is-invalid');
                        $('#' + field).after('<div class="invalid-feedback">' + message + '</div>');
                    }
                } else {
                    toastr.error('Error al actualizar la información');
                }
            }
        });
    });

    // Cambiar contraseña
    $('#formPassword').on('submit', function(e) {
        e.preventDefault();
        const formData = $(this).serialize();

        $.ajax({
            url: default_server + "/json/perfil/password",
            type: 'PUT',
            data: formData,
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    // Limpiar formulario
                    $('#formPassword')[0].reset();
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    // Mostrar errores de validación
                    const errors = xhr.responseJSON.errors;
                    if (errors) {
                        for (const field in errors) {
                            const message = errors[field][0];
                            $('#' + field).addClass('is-invalid');
                            $('#' + field).after('<div class="invalid-feedback">' + message + '</div>');
                        }
                    } else {
                        toastr.error(xhr.responseJSON.message);
                    }
                } else {
                    toastr.error('Error al cambiar la contraseña');
                }
            }
        });
    });

    // Subir foto de perfil
    $('#formFoto').on('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);

        $.ajax({
            url: default_server + "/json/perfil/foto",
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    // Actualizar la imagen mostrada
                    if (response.data.foto_url) {
                        $('.avatar-xl').attr('src', response.data.foto_url);
                    }
                }
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    // Mostrar errores de validación
                    const errors = xhr.responseJSON.errors;
                    for (const field in errors) {
                        const message = errors[field][0];
                        $('#' + field).addClass('is-invalid');
                        $('#' + field).after('<div class="invalid-feedback">' + message + '</div>');
                    }
                } else {
                    toastr.error('Error al subir la foto de perfil');
                }
            }
        });
    });

    // Eliminar foto de perfil
    $('#btnEliminarFoto').on('click', function(e) {
        e.preventDefault();

        if (confirm('¿Está seguro de eliminar su foto de perfil?')) {
            $.ajax({
                url: default_server + "/json/perfil/foto",
                type: 'DELETE',
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                        // Actualizar la imagen a la imagen por defecto o iniciales
                        $('.avatar-xl').replaceWith('<div class="avatar-xl rounded-circle bg-primary text-white mx-auto p-4 font-24"></div>');
                    }
                },
                error: function() {
                    toastr.error('Error al eliminar la foto de perfil');
                }
            });
        }
    });

    // Actualizar preferencias
    $('#formPreferencias').on('submit', function(e) {
        e.preventDefault();
        const formData = $(this).serialize();

        $.ajax({
            url: default_server + "/json/perfil/preferencias",
            type: 'PUT',
            data: formData,
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                }
            },
            error: function() {
                toastr.error('Error al actualizar las preferencias');
            }
        });
    });

    // Inicialización
    cargarPerfil();
});
