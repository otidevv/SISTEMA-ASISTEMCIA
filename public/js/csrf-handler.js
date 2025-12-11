/**
 * CSRF Token Handler - Manejo Global de Errores 419
 * 
 * Este script previene que la aplicación se "pegue" cuando el token CSRF expira.
 * Solo actúa cuando ocurre un error 419, respetando el tiempo natural de expiración.
 * 
 * Implementa:
 * - Detección automática de errores 419
 * - Refresco del token CSRF cuando ocurre el error
 * - Reintento automático de peticiones fallidas
 * - Notificaciones amigables al usuario
 */

(function () {
    'use strict';

    // ========================================
    // CONFIGURACIÓN
    // ========================================
    const CONFIG = {
        // Endpoint para obtener nuevo token
        REFRESH_TOKEN_URL: '/refresh-csrf',

        // Máximo de reintentos para una petición
        MAX_RETRIES: 1
    };

    // ========================================
    // GESTIÓN DE TOKEN CSRF
    // ========================================

    /**
     * Obtiene el token CSRF actual de la página
     */
    function getCurrentToken() {
        const metaTag = document.querySelector('meta[name="csrf-token"]');
        return metaTag ? metaTag.getAttribute('content') : null;
    }

    /**
     * Actualiza el token CSRF en toda la página
     */
    function updateCsrfToken(newToken) {
        // Actualizar meta tag
        const metaTag = document.querySelector('meta[name="csrf-token"]');
        if (metaTag) {
            metaTag.setAttribute('content', newToken);
        }

        // Actualizar todos los campos _token en formularios
        document.querySelectorAll('input[name="_token"]').forEach(input => {
            input.value = newToken;
        });

        // Actualizar variable global si existe
        if (window.csrfToken !== undefined) {
            window.csrfToken = newToken;
        }

        console.log('✓ Token CSRF actualizado correctamente');
    }

    /**
     * Refresca el token CSRF desde el servidor
     */
    function refreshCsrfToken() {
        return fetch(CONFIG.REFRESH_TOKEN_URL, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error('No se pudo refrescar el token');
                }
                return response.json();
            })
            .then(data => {
                if (data.token) {
                    updateCsrfToken(data.token);
                    return data.token;
                } else {
                    throw new Error('Token no recibido del servidor');
                }
            })
            .catch(error => {
                console.error('Error al refrescar token CSRF:', error);
                return null;
            });
    }

    // ========================================
    // MANEJO DE ERRORES 419
    // ========================================

    /**
     * Maneja el error 419 y reintenta la petición
     */
    function handle419Error(xhr, ajaxSettings) {
        console.warn('⚠ Error 419 detectado - Token CSRF expirado');

        // Mostrar notificación al usuario
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Sesión actualizada',
                text: 'Reintentando la operación...',
                icon: 'info',
                timer: 2000,
                showConfirmButton: false,
                allowOutsideClick: false
            });
        }

        // Refrescar token y reintentar
        return refreshCsrfToken().then(newToken => {
            if (!newToken) {
                // Si no se pudo refrescar, redirigir a login
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Sesión expirada',
                        text: 'Por favor, inicie sesión nuevamente.',
                        icon: 'warning',
                        confirmButtonText: 'Ir a Login'
                    }).then(() => {
                        window.location.href = '/login';
                    });
                } else {
                    alert('Su sesión ha expirado. Por favor, inicie sesión nuevamente.');
                    window.location.href = '/login';
                }
                return null;
            }

            // Actualizar el token en la petición original
            if (ajaxSettings.data) {
                if (typeof ajaxSettings.data === 'string') {
                    // Si es string, reemplazar el token
                    ajaxSettings.data = ajaxSettings.data.replace(
                        /_token=[^&]*/,
                        '_token=' + encodeURIComponent(newToken)
                    );
                } else if (typeof ajaxSettings.data === 'object') {
                    // Si es objeto, actualizar la propiedad
                    ajaxSettings.data._token = newToken;
                }
            }

            // Actualizar headers si existen
            if (ajaxSettings.headers) {
                ajaxSettings.headers['X-CSRF-TOKEN'] = newToken;
            }

            // Reintentar la petición
            console.log('↻ Reintentando petición con nuevo token...');
            return $.ajax(ajaxSettings);
        });
    }

    // ========================================
    // CONFIGURACIÓN DE JQUERY AJAX
    // ========================================

    if (typeof $ !== 'undefined' && $.ajaxSetup) {
        // Contador de reintentos por petición
        const retryCount = new WeakMap();

        // Configurar manejo global de errores AJAX
        $(document).ajaxError(function (event, xhr, settings, thrownError) {
            // Solo manejar errores 419
            if (xhr.status === 419) {
                // Prevenir el manejo de error por defecto
                event.preventDefault();

                // Verificar número de reintentos
                const retries = retryCount.get(settings) || 0;

                if (retries < CONFIG.MAX_RETRIES) {
                    retryCount.set(settings, retries + 1);

                    // Manejar el error 419 y reintentar
                    handle419Error(xhr, settings)
                        .then(result => {
                            if (result && settings.success) {
                                // Si el reintento fue exitoso, llamar al callback de éxito original
                                settings.success(result);
                            }
                        })
                        .catch(error => {
                            console.error('Error en reintento:', error);
                            if (settings.error) {
                                settings.error(xhr, 'error', thrownError);
                            }
                        });
                } else {
                    console.error('Máximo de reintentos alcanzado para petición 419');

                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: 'Error de sesión',
                            text: 'No se pudo completar la operación. Por favor, recargue la página.',
                            icon: 'error',
                            confirmButtonText: 'Recargar página'
                        }).then(() => {
                            window.location.reload();
                        });
                    } else {
                        alert('Error de sesión. Por favor, recargue la página.');
                        window.location.reload();
                    }
                }
            }
        });

        // Agregar token CSRF a todas las peticiones AJAX
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': getCurrentToken()
            },
            beforeSend: function (xhr, settings) {
                // Actualizar el token antes de cada petición
                const currentToken = getCurrentToken();
                if (currentToken) {
                    xhr.setRequestHeader('X-CSRF-TOKEN', currentToken);
                }
            }
        });

        console.log('✓ Manejo global de errores 419 configurado (solo reactivo)');
    }

    // ========================================
    // INICIALIZACIÓN
    // ========================================

    // Exponer funciones útiles globalmente
    window.csrfHandler = {
        refreshToken: refreshCsrfToken,
        getCurrentToken: getCurrentToken,
        updateToken: updateCsrfToken
    };

    console.log('✓ CSRF Handler inicializado - Solo actúa cuando ocurre error 419');

})();
