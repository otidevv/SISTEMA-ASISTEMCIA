/**
 * CSRF Token Handler - Manejo Global de Errores 419
 * Optimizado para dispositivos móviles
 * 
 * Este script complementa el middleware del servidor para prevenir errores 419.
 * Se enfoca en PREVENCIÓN más que en recuperación (el servidor maneja la recuperación).
 * 
 * Implementa:
 * - Detección automática de errores 419 en AJAX
 * - Refresco del token CSRF cuando ocurre el error
 * - Reintento automático de peticiones AJAX fallidas
 * - Detección de visibilidad de página (móviles)
 * - Persistencia de token en localStorage
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
        MAX_RETRIES: 1,

        // Clave para localStorage
        STORAGE_KEY: 'csrf_token_data'
    };

    // ========================================
    // DETECCIÓN DE DISPOSITIVO MÓVIL
    // ========================================
    const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);

    if (isMobile) {
        console.log('📱 Dispositivo móvil detectado - Activando protección CSRF mejorada');
    }

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
     * Guarda el token y su timestamp en localStorage
     */
    function saveTokenToStorage(token) {
        try {
            const data = {
                token: token,
                timestamp: Date.now()
            };
            localStorage.setItem(CONFIG.STORAGE_KEY, JSON.stringify(data));
        } catch (e) {
            console.warn('No se pudo guardar token en localStorage:', e);
        }
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

        // Guardar en localStorage
        saveTokenToStorage(newToken);

        console.log('✓ Token CSRF actualizado correctamente');
    }

    /**
     * Refresca el token CSRF desde el servidor
     */
    function refreshCsrfToken() {
        console.log('🔄 Refrescando token CSRF...');

        return fetch(CONFIG.REFRESH_TOKEN_URL, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
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
                console.error('❌ Error al refrescar token CSRF:', error);
                return null;
            });
    }

    // ========================================
    // MANEJO DE ERRORES 419 EN AJAX
    // ========================================

    /**
     * Maneja el error 419 en peticiones AJAX y reintenta
     */
    function handle419Error(xhr, ajaxSettings) {
        console.warn('⚠️ Error 419 detectado en AJAX - Token CSRF expirado');

        // Refrescar token y reintentar
        return refreshCsrfToken().then(newToken => {
            if (!newToken) {
                console.error('❌ No se pudo refrescar el token');
                return null;
            }

            // Actualizar el token en la petición original
            if (ajaxSettings.data) {
                if (typeof ajaxSettings.data === 'string') {
                    ajaxSettings.data = ajaxSettings.data.replace(
                        /_token=[^&]*/,
                        '_token=' + encodeURIComponent(newToken)
                    );
                } else if (typeof ajaxSettings.data === 'object') {
                    ajaxSettings.data._token = newToken;
                }
            }

            // Actualizar headers si existen
            if (ajaxSettings.headers) {
                ajaxSettings.headers['X-CSRF-TOKEN'] = newToken;
            }

            // Reintentar la petición
            console.log('↻ Reintentando petición AJAX con nuevo token...');
            return $.ajax(ajaxSettings);
        });
    }

    // ========================================
    // DETECCIÓN DE VISIBILIDAD DE PÁGINA
    // ========================================

    /**
     * Refresca el token cuando el usuario vuelve a la pestaña
     * Especialmente útil en móviles donde las pestañas se suspenden
     */
    function setupVisibilityHandler() {
        if (typeof document.hidden !== 'undefined') {
            let wasHidden = false;
            let hiddenTime = null;

            document.addEventListener('visibilitychange', function () {
                if (document.hidden) {
                    // La página se ocultó
                    wasHidden = true;
                    hiddenTime = Date.now();
                    console.log('👁️ Página oculta - Guardando estado');
                } else if (wasHidden) {
                    // La página volvió a ser visible
                    const hiddenDuration = Date.now() - hiddenTime;
                    console.log(`👁️ Página visible nuevamente (oculta por ${Math.round(hiddenDuration / 1000)}s)`);

                    // Si estuvo oculta más de 5 minutos, refrescar token
                    if (hiddenDuration > 5 * 60 * 1000) {
                        console.log('🔄 Refrescando token después de inactividad');
                        refreshCsrfToken();
                    }

                    wasHidden = false;
                }
            });

            console.log('✓ Detección de visibilidad de página activada');
        }
    }

    // ========================================
    // CONFIGURACIÓN DE JQUERY AJAX
    // ========================================

    if (typeof $ !== 'undefined' && $.ajaxSetup) {
        // Contador de reintentos por petición
        const retryCount = new WeakMap();

        // Configurar manejo global de errores AJAX
        $(document).ajaxError(function (event, xhr, settings, thrownError) {
            // Solo manejar errores 419 en AJAX
            if (xhr.status === 419) {
                event.preventDefault();

                const retries = retryCount.get(settings) || 0;

                if (retries < CONFIG.MAX_RETRIES) {
                    retryCount.set(settings, retries + 1);

                    // Manejar el error 419 y reintentar
                    handle419Error(xhr, settings)
                        .then(result => {
                            if (result && settings.success) {
                                settings.success(result);
                            }
                        })
                        .catch(error => {
                            console.error('Error en reintento AJAX:', error);
                            if (settings.error) {
                                settings.error(xhr, 'error', thrownError);
                            }
                        });
                } else {
                    console.error('❌ Máximo de reintentos alcanzado para petición AJAX 419');
                }
            }
        });

        // Agregar token CSRF a todas las peticiones AJAX
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': getCurrentToken()
            },
            beforeSend: function (xhr, settings) {
                const currentToken = getCurrentToken();
                if (currentToken) {
                    xhr.setRequestHeader('X-CSRF-TOKEN', currentToken);
                }
            }
        });

        console.log('✓ Manejo global de errores AJAX 419 configurado');
    }

    // ========================================
    // INICIALIZACIÓN
    // ========================================

    // Guardar token inicial en localStorage
    const initialToken = getCurrentToken();
    if (initialToken) {
        saveTokenToStorage(initialToken);
    }

    // Configurar detección de visibilidad
    setupVisibilityHandler();

    // Exponer funciones útiles globalmente
    window.csrfHandler = {
        refreshToken: refreshCsrfToken,
        getCurrentToken: getCurrentToken,
        updateToken: updateCsrfToken,
        isMobile: isMobile
    };

    console.log('✅ CSRF Handler inicializado - Protección móvil activada');

})();
