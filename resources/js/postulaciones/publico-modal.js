let currentStep = 1;
const totalSteps = 5;
let pagosSeleccionadosDetalles = []; // Global para el resumen
window.isSubmittingPostulacion = window.isSubmittingPostulacion || false;

// Función para lanzar confetti cuando la postulación es aprobada
function lanzarConfetti() {
    const duration = 3000; // 3 segundos
    const animationEnd = Date.now() + duration;
    const defaults = { startVelocity: 30, spread: 360, ticks: 60, zIndex: 99999 };

    function randomInRange(min, max) {
        return Math.random() * (max - min) + min;
    }

    const interval = setInterval(function () {
        const timeLeft = animationEnd - Date.now();

        if (timeLeft <= 0) {
            return clearInterval(interval);
        }

        const particleCount = 50 * (timeLeft / duration);

        // Lanzar confetti desde dos puntos
        confetti(Object.assign({}, defaults, {
            particleCount,
            origin: { x: randomInRange(0.1, 0.3), y: Math.random() - 0.2 }
        }));
        confetti(Object.assign({}, defaults, {
            particleCount,
            origin: { x: randomInRange(0.7, 0.9), y: Math.random() - 0.2 }
        }));
    }, 250);
}


// Configuración Global de Toasts con SweetAlert2 (Para un look más moderno y premium)
const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
    didOpen: (toast) => {
        toast.addEventListener('mouseenter', Swal.stopTimer);
        toast.addEventListener('mouseleave', Swal.resumeTimer);
    }
});

function getBaseUrl() {
    const origin = window.location.origin;
    const path = window.location.pathname;
    const parts = path.split('/').filter(p => p.length > 0);
    
    // Si estamos en una subcarpeta (común en XAMPP como /SISTEMA-ASISTEMCIA/)
    if (parts.length > 0 && parts[0] !== 'api' && parts[0] !== 'postulacion') {
        return origin + '/' + parts[0];
    }
    return origin;
}

$(document).ready(function () {


    // Inicializar wizard
    showStep(currentStep);

    // Cargar departamentos
    loadDepartamentos();

    // Función helper para resaltar campos autocompletados con un pulso (Verde institucional) - MÁS DURACIÓN
    window.highlightFields = function (selector) {
        const $els = $(selector);
        $els.addClass('field-highlight');
        setTimeout(() => {
            $els.removeClass('field-highlight');
        }, 3500); // 3.5 segundos para que sea imposible no verlo
    };

    // NUEVO: Función helper para generar email por defecto basado en DNI
    window.autoGenerateEmail = function (dni, targetId) {
        const $target = $(targetId);
        // Solo autogenerar si el campo está vacío para no sobrescribir correos reales
        if (dni && dni.length === 8 && $target.val().trim() === '') {
            $target.val(dni + '@cepre.unamad.edu.pe');
            // Resaltar para avisar al usuario que se autogeneró
            highlightFields(targetId);
        }
    };

    // Event listeners para validación en tiempo real
    $('input[required], select[required], textarea[required]').on('blur input change', function () {
        const $el = $(this);
        if ($el.val()) {
            $el.removeClass('is-invalid').addClass('is-valid');
            // Agregar ícono de éxito si no existe (Excluir archivos de las tarjetas premium para no duplicar)
            if (!$el.parent().find('.valid-feedback-icon').length && !$el.is('input[type="file"]')) {
                $el.after('<div class="valid-feedback-icon"><i class="fas fa-check-circle"></i></div>');
            }
        } else {
            $el.removeClass('is-valid').addClass('is-invalid');
            $el.parent().find('.valid-feedback-icon').remove();
        }
    });

    $('input[type="tel"]').on('input', function () {
        this.value = this.value.replace(/[^0-9]/g, '');
    });

    // Capitalización automática para nombres y apellidos
    $('#estudiante_nombre, #estudiante_apellido_paterno, #estudiante_apellido_materno, #padre_nombre, #padre_apellidos, #madre_nombre, #madre_apellidos').on('input', function () {
        this.value = this.value.toUpperCase();
    });

    // Auto-focus DNI -> DV y Verificación Automática (Postulante)
    let verifPostulanteTimeout = null;
    $(document).on('input', '#check_dni', function () {
        const val = this.value;
        if (val.length === 8) {
            $('#check_dv').focus();
        }
    });

    $(document).on('input', '#check_dv', function () {
        const dv = this.value;
        const dni = $('#check_dni').val();

        if (dv.length === 1 && dni.length === 8) {
            if (verifPostulanteTimeout) clearTimeout(verifPostulanteTimeout);
            verifPostulanteTimeout = setTimeout(() => {
                $('#btn-verificar-dni').trigger('click');
                // Autogenerar email del estudiante si está vacío
                autoGenerateEmail(dni, '#estudiante_email');
            }, 300); // 300ms de espera por si el usuario corrige
        }
    });

    // Verificación Automática (Padres)
    let verifPadreTimeout = null;
    let verifMadreTimeout = null;

    $(document).on('input', '#padre_dni', function () {
        const dni = this.value;
        if (dni.length === 8) {
            if (verifPadreTimeout) clearTimeout(verifPadreTimeout);
            verifPadreTimeout = setTimeout(() => {
                consultarDNIPadre('padre', $('#btn-consultar-padre'));
                // Autogenerar email si está vacío
                autoGenerateEmail(dni, '#padre_email');
            }, 300);
        }
    });

    $(document).on('input', '#madre_dni', function () {
        const dni = this.value;
        if (dni.length === 8) {
            if (verifMadreTimeout) clearTimeout(verifMadreTimeout);
            verifMadreTimeout = setTimeout(() => {
                consultarDNIPadre('madre', $('#btn-consultar-madre'));
                // Autogenerar email si está vacío
                autoGenerateEmail(dni, '#madre_email');
            }, 300);
        }
    });

    // Manejo del formulario
    // Manejo del formulario (Uso de .off().on() para evitar duplicados en recargas parciales)
    $(document).off('submit', '#formPostulacionPublica').on('submit', '#formPostulacionPublica', function (e) {
        e.preventDefault();
        submitPostulacion();
    });

    // Eventos de ubicación
    $('#departamento').on('change', function () {
        const departamentoId = $(this).val();
        if (departamentoId) {
            loadProvincias(departamentoId);
            $('#provincia').prop('disabled', false);
            $('#distrito').html('<option value="">Seleccione provincia primero</option>').prop('disabled', true);
            // Clear and disable school related fields
            $('#buscar_colegio').prop('disabled', true).val('');
            $('#btnBuscarColegio').prop('disabled', true);
            $('#sugerencias-colegios').empty();
            ocultarColegioSeleccionado();
        } else {
            $('#provincia').html('<option value="">Seleccione departamento primero</option>').prop('disabled', true);
            $('#distrito').html('<option value="">Seleccione provincia primero</option>').prop('disabled', true);
            $('#buscar_colegio').prop('disabled', true).val('');
            $('#btnBuscarColegio').prop('disabled', true);
            $('#sugerencias-colegios').empty();
            ocultarColegioSeleccionado();
        }
    });

    $('#provincia').on('change', function () {
        const departamentoId = $('#departamento').val();
        const provinciaId = $(this).val();
        if (provinciaId) {
            loadDistritos(departamentoId, provinciaId);
            $('#distrito').prop('disabled', false);
            // Clear and disable school related fields
            $('#buscar_colegio').prop('disabled', true).val('');
            $('#btnBuscarColegio').prop('disabled', true);
            $('#sugerencias-colegios').empty();
            ocultarColegioSeleccionado();
        } else {
            $('#distrito').html('<option value="">Seleccione provincia primero</option>').prop('disabled', true);
            $('#buscar_colegio').prop('disabled', true).val('');
            $('#btnBuscarColegio').prop('disabled', true);
            $('#sugerencias-colegios').empty();
            ocultarColegioSeleccionado();
        }
    });

    $('#distrito').on('change', function () {
        if ($(this).val()) {
            $('#buscar_colegio').prop('disabled', false);
            $('#btnBuscarColegio').prop('disabled', false);
            $('#sugerencias-colegios').empty();
            ocultarColegioSeleccionado();
            buscarColegios(); // Call search immediately
        } else {
            $('#buscar_colegio').prop('disabled', true).val('');
            $('#btnBuscarColegio').prop('disabled', true);
            $('#sugerencias-colegios').empty();
            ocultarColegioSeleccionado();
        }
    });

    // Verificar postulante (Delegación de eventos para mayor robustez)
    // Manejo de Cambio de Tipo de Documento
    $(document).on('change', '#estudiante_tipo_documento_select', function () {
        const val = $(this).val();
        const sectionVerif = $('#section-verificacion');
        const personalFields = $('#personal_fields');
        const labelDNI = $('#label_estudiante_dni');
        const inputDNI = $('#estudiante_dni');
        const checkDni = $('#check_dni');
        const checkDv = $('#check_dv');

        if (val == '1') { // DNI
            sectionVerif.slideDown();
            personalFields.hide();
            labelDNI.text('DNI');
            inputDNI.prop('readonly', true).val('');
            checkDni.val('').focus();
            checkDv.val('');
        } else { // CE o Pasaporte
            sectionVerif.slideUp();
            personalFields.slideDown();
            const typeText = (val == '2') ? 'Carnet de Extranjería' : 'Pasaporte';
            labelDNI.text(typeText);
            inputDNI.prop('readonly', false).val('').focus();

            // Limpiar campos para evitar mezclas con RENIEC previo
            $('#estudiante_nombre').val('');
            $('#estudiante_apellido_paterno').val('');
            $('#estudiante_apellido_materno').val('');
            $('#estudiante_password').prop('required', true).closest('.col-md-3').show();
            $('#estudiante_password_confirmation').prop('required', true).closest('.col-md-3').show();
        }
    });

    $(document).on('click', '#btn-verificar-dni', function () {

        const $btn = $(this);
        const originalHtml = $btn.html();

        // Mostrar spinner
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Verificando...');

        verificarPostulante(this, function (success, response) {
            $btn.prop('disabled', false).html(originalHtml);
            
            // Si tiene éxito pero ya está registrado, el modal detallado ya se mostró en verificarPostulante
            // No mostramos el toast simple para no "ensuciar" la pantalla y confundir al usuario
            if (success && response && response.status !== 'registered') {
                $('#check_dni, #check_dv').addClass('is-valid').css('border-color', '#198754');
                Toast.fire({ icon: 'success', title: 'DNI Verificado correctamente' });
            }
        });
    });

    // Manejar tecla Enter en el campo de DNI para verificación
    $(document).on('keypress', '#check_dni', function (e) {
        if (e.which === 13) { // Enter key
            e.preventDefault(); // Prevenir envío del formulario

            $('#btn-verificar-dni').trigger('click');
        }
    });

    // Prevenir que Enter envíe el formulario en campos de entrada (excepto textarea)
    $(document).on('keypress', '#formPostulacionPublica input:not([type="submit"])', function (e) {
        if (e.which === 13) {
            e.preventDefault();

            return false;
        }
    });

    // Búsqueda de colegio con debounce
    let searchTimeout = null;

    $('#btnBuscarColegio').off('click').on('click', function () {
        buscarColegios();
    });

    // Evento keyup con debounce de 300ms
    $('#buscar_colegio').off('keyup').on('keyup', function () {
        const searchTerm = $(this).val();

        // Limpiar timeout anterior
        if (searchTimeout) {
            clearTimeout(searchTimeout);
        }

        // Si hay menos de 2 caracteres, limpiar resultados
        if (searchTerm.length < 2 && searchTerm.length > 0) {
            $('#sugerencias-colegios').empty();
            return;
        }

        // Aplicar debounce de 300ms
        searchTimeout = setTimeout(function () {
            buscarColegios();
        }, 300);
    });

    $('#buscar_colegio').off('keypress').on('keypress', function (e) {
        if (e.which === 13) {
            e.preventDefault();
            // Limpiar timeout si existe
            if (searchTimeout) {
                clearTimeout(searchTimeout);
            }
            buscarColegios();
        }
    });
});

function buscarColegios() {
    const termino = $('#buscar_colegio').val();
    // Solo buscar si el término tiene al menos 2 caracteres o si el campo está vacío al seleccionar un distrito
    if (termino.length < 2 && termino.length !== 0) {
        $('#sugerencias-colegios').empty();
        return;
    }

    // Mostrar indicador de carga
    $('#sugerencias-colegios').html(`
        <div class="list-group-item text-center">
            <div class="spinner-border spinner-border-sm text-primary me-2" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
            <span>Buscando colegios...</span>
        </div>
    `);

    $.ajax({
        url: '/api/public/buscar-colegios',
        type: 'POST',
        data: {
            departamento: $('#departamento').val(),
            provincia: $('#provincia').val(),
            distrito: $('#distrito').val(),
            termino: termino,
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        success: function (response) {
            if (response.success) {
                mostrarSugerenciasColegios(response.colegios, termino);
            } else {
                $('#sugerencias-colegios').html(`
                    <div class="list-group-item text-center text-muted">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        No se pudieron cargar los colegios
                    </div>
                `);
            }
        },
        error: function (xhr) {
            console.error('Error buscando colegios:', xhr);
            $('#sugerencias-colegios').html(`
                <div class="list-group-item text-center text-danger">
                    <i class="fas fa-times-circle me-2"></i>
                    Error al buscar colegios. Por favor, intente nuevamente.
                </div>
            `);
            Toast.fire({
                icon: 'error',
                title: 'Error al buscar colegios'
            });
        }
    });
}

function mostrarSugerenciasColegios(colegios, termino = '') {
    let html = '';

    if (colegios.length === 0) {
        html = `
            <div class="list-group-item text-center">
                <i class="fas fa-search me-2"></i>
                No se encontraron colegios
                <br>
                <small class="text-muted">Intente con otro término de búsqueda</small>
            </div>
        `;
    } else {
        colegios.forEach(colegio => {
            // Resaltar término de búsqueda en el nombre
            let nombreResaltado = colegio.nombre;
            if (termino && termino.length >= 2) {
                const regex = new RegExp(`(${termino})`, 'gi');
                nombreResaltado = colegio.nombre.replace(regex, '<span style="background-color: rgba(255, 215, 0, 0.3); font-weight: 600; padding: 0 2px; border-radius: 2px;">$1</span>');
            }

            html += `
                <a href="#" class="list-group-item list-group-item-action seleccionar-colegio"
                    data-id="${colegio.id}" data-nombre="${colegio.nombre}"
                    style="transition: all 0.2s ease; border-left: 3px solid transparent;">
                    <strong>${nombreResaltado}</strong>
                    ${colegio.nivel ? `<br><small class="text-muted"><i class="fas fa-graduation-cap me-1"></i>Nivel: ${colegio.nivel}</small>` : ''}
                    ${colegio.direccion ? `<br><small class="text-muted"><i class="fas fa-map-marker-alt me-1"></i>${colegio.direccion}</small>` : ''}
                </a>
            `;
        });
    }

    $('#sugerencias-colegios').html(html);

    // IMPORTANTE: Usar event delegation para evitar duplicados
    // Primero removemos cualquier handler previo
    $(document).off('click', '.seleccionar-colegio');

    // Luego agregamos el nuevo handler usando delegación
    $(document).on('click', '.seleccionar-colegio', function (e) {
        e.preventDefault();

        const colegioSeleccionado = {
            id: $(this).data('id'),
            nombre: $(this).data('nombre')
        };

        mostrarColegioSeleccionado(colegioSeleccionado);
        $('#sugerencias-colegios').empty();

        // Feedback visual
        Toast.fire({
            icon: 'success',
            title: 'Colegio seleccionado correctamente'
        });
    });

    // Agregar hover effects con CSS inline
    $('.seleccionar-colegio').hover(
        function () {
            $(this).css({
                'background-color': '#f0f8ff',
                'border-left-color': '#00bcd4',
                'transform': 'translateX(5px)'
            });
        },
        function () {
            $(this).css({
                'background-color': '',
                'border-left-color': 'transparent',
                'transform': ''
            });
        }
    );
}

function mostrarColegioSeleccionado(colegio) {
    $('#nombre-colegio-seleccionado').html(`
        <i class="fas fa-school me-2 text-success"></i>
        <strong>${colegio.nombre}</strong>
    `);
    $('#colegio-seleccionado').show();
    $('#buscar_colegio').val('').attr('placeholder', 'Colegio seleccionado: ' + colegio.nombre);
    $('#centro_educativo_id').val(colegio.id);

    // Deshabilitar búsqueda hasta que se cambie
    $('#buscar_colegio').prop('disabled', true);
    $('#btnBuscarColegio').prop('disabled', true);
}

function ocultarColegioSeleccionado() {
    $('#colegio-seleccionado').hide();
    $('#centro_educativo_id').val('');
    $('#buscar_colegio').val('').attr('placeholder', 'Buscar colegio...').prop('disabled', false);
    $('#btnBuscarColegio').prop('disabled', false);
    $('#sugerencias-colegios').empty();
}

function loadDepartamentos() {

    const select = $('#departamento');

    if (select.length === 0) {
        console.error('ERROR CRÍTICO: No se encontró el elemento #departamento');
        return;
    }

    // CARGA INICIAL (Profesional): Usar datos inyectados desde el servidor (Hydration)
    // Esto evita peticiones API redundantes y es instantáneo
    const departamentosSource = window.DEPARTAMENTOS_INICIALES || [];

    if (departamentosSource.length > 0) {
        let html = '<option value="">Seleccione departamento</option>';
        departamentosSource.forEach(function (dep) {
            // Manejar tanto array de strings como array de objetos
            const id = (typeof dep === 'object') ? (dep.id || dep.nombre) : dep;
            const nombre = (typeof dep === 'object') ? dep.nombre : dep;
            html += `<option value="${id}">${nombre}</option>`;
        });
        select.html(html).show();

    } else {
        // Fallback: Si por alguna razón la hidratación falló, intentar la API
        console.warn('Hydration vacía, intentando API como fallback...');
        select.html('<option value="">Cargando...</option>').show();

        $.get('/api/public/departamentos', function (data) {
            if (data.success) {
                let html = '<option value="">Seleccione departamento</option>';
                data.departamentos.forEach(function (item) {
                    const id = (typeof item === 'object') ? item.id : item;
                    const nombre = (typeof item === 'object') ? item.nombre : item;
                    html += `<option value="${id}">${nombre}</option>`;
                });
                select.html(html);
            }
        }).fail(function () {
            select.html('<option value="">Error de conexión</option>');
        });
    }
}

function loadProvincias(dep) {
    const select = $('#provincia');
    const distSelect = $('#distrito');

    select.html('<option value="">Seleccione provincia</option>').prop('disabled', true).show();
    distSelect.html('<option value="">Seleccione distrito</option>').prop('disabled', true).show();

    if (!dep) {
        select.html('<option value="">Seleccione departamento primero</option>');
        return;
    }

    $.get('/api/public/provincias/' + dep, function (data) {
        if (data.success) {
            let html = '<option value="">Seleccione provincia</option>';
            data.provincias.forEach(function (item) {
                const id = (typeof item === 'object') ? item.id : item;
                const nombre = (typeof item === 'object') ? item.nombre : item;
                html += `<option value="${id}">${nombre}</option>`;
            });
            select.html(html).prop('disabled', false).show();
        }
    }).fail(function () {
        Toast.fire({ icon: 'error', title: 'Error al cargar provincias' });
    });
}

function loadDistritos(dep, prov) {
    const select = $('#distrito');

    select.html('<option value="">Seleccione distrito</option>').prop('disabled', true).show();

    if (!dep || !prov) {
        select.html('<option value="">Seleccione provincia primero</option>');
        return;
    }

    $.get('/api/public/distritos/' + dep + '/' + prov, function (data) {
        if (data.success) {
            let html = '<option value="">Seleccione distrito</option>';
            data.distritos.forEach(function (item) {
                const id = (typeof item === 'object') ? item.id : item;
                const nombre = (typeof item === 'object') ? item.nombre : item;
                html += `<option value="${id}">${nombre}</option>`;
            });
            select.html(html).prop('disabled', false).show();
        }
    }).fail(function () {
        Toast.fire({
            icon: 'error',
            title: 'Error al cargar distritos'
        });
    });
}

function showStep(n) {
    // Ocultar todos los pasos
    $('.step-content').hide();
    // Mostrar el paso actual
    $('.step-content[data-step="' + n + '"]').show();

    // Actualizar indicadores visuales (Wizard)
    $('.step-item').removeClass('active');
    $('.step-item').each(function (index) {
        if (index + 1 <= n) {
            $(this).addClass('active');
        }
    });

    // Controlar botones de navegación
    if (n == 1) {
        $('#prevBtn').hide();
    } else {
        $('#prevBtn').show();
    }

    if (n == totalSteps) {
        $('#nextBtn').hide();
        // El botón de envío está dentro del paso 6
    } else {
        $('#nextBtn').show();
    }

    currentStep = n;

    // Actualizar barra de progreso animada
    const progressPercent = ((n - 1) / (totalSteps - 1)) * 100;
    $('#wizard-progress-fill').css('width', progressPercent + '%');
}

// Función global para actualizar el nombre del archivo en las tarjetas premium
window.updateFileName = function (input) {
    const $card = $(input).closest('.file-upload-card');
    const $nameLabel = $card.find('.file-name');
    const fileName = input.files.length > 0 ? input.files[0].name : 'Ningún archivo seleccionado';

    $nameLabel.text(fileName);

    if (input.files.length > 0) {
        $card.addClass('has-file');
    } else {
        $card.removeClass('has-file');
    }
};

function nextPrev(n) {
    // Si vamos adelante, validar paso actual
    if (n == 1 && !validateForm()) return false;

    // Validación especial para el paso 2 (Padres)
    if (n == 1 && currentStep == 2) {
        if (!validarPadres()) return false;
    }

    // Si vamos al paso de confirmación (Paso 5 ahora, antes era 6), generar resumen
    if (currentStep + n == 5) {
        generarResumen();
    }

    showStep(currentStep + n);
}

// ======================================================================
// FUNCIONES DE VALIDACIÓN DE PAGO (MOVIDAS AL ALCANCE GLOBAL)
// ======================================================================

function validarVoucher() {
    const dni = $('#estudiante_dni').val();
    const secuencia = $('#voucher_secuencia').val();
    // Se mantiene la referencia al botón de búsqueda por si se presiona manualmente
    const btnBuscar = $('#btn-validar-pago-manual');

    if (!secuencia) {
        Toast.fire({
            icon: 'warning',
            title: 'Atención',
            text: 'Por favor ingrese el código de voucher o DNI'
        });
        return;
    }

    // Estado de carga
    const originalText = btnBuscar.html();
    btnBuscar.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Buscando...');
    $('#pago_feedback').html('<div class="alert alert-info">Buscando pagos...</div>');
    $('#voucher_details').hide().empty();

    $.ajax({
        url: '/postulacion/validate-payment',
        method: 'POST',
        data: {
            dni: dni,
            secuencia: secuencia,
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        success: function (response) {


            if (response.valid && response.payments && response.payments.length > 0) {
                mostrarDetallesPago(response.payments);
                Toast.fire({
                    icon: 'success',
                    title: 'Pagos encontrados y verificados'
                });
                $('#pago_feedback').html('<div class="alert alert-success"><i class="fas fa-check-circle"></i> Pago verificado correctamente</div>');
            } else {
                Toast.fire({
                    icon: 'error',
                    title: response.message || 'No se encontraron pagos válidos'
                });
                $('#pago_feedback').html(`
                    <div class="alert alert-danger">
                        <i class="fas fa-times-circle"></i> ${response.message || 'Pago no encontrado'}
                        <hr>
                        <button type="button" class="btn btn-warning btn-sm mt-2" onclick="mostrarIngresoManual()">
                            <i class="fas fa-keyboard me-1"></i> Ingresar Datos Manualmente
                        </button>
                    </div>
                `);
            }
        },
        error: function (xhr) {
            console.error('Error validando pago:', xhr);
            let msg = 'Error al validar el pago';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                msg = xhr.responseJSON.message;
            }
            Toast.fire({
                icon: 'error',
                title: msg
            });
            $('#pago_feedback').html(`
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i> ${msg}
                    <hr>
                    <button type="button" class="btn btn-warning btn-sm mt-2" onclick="mostrarIngresoManual()">
                        <i class="fas fa-keyboard me-1"></i> Ingresar Datos Manualmente
                    </button>
                </div>
            `);
        },
        complete: function () {
            // Restaurar el botón solo si existe, ya que este botón puede ser para la validación manual
            if (btnBuscar.length) {
                btnBuscar.prop('disabled', false).html(originalText);
            }
        }
    });
}

function mostrarDetallesPago(vouchers) {


    // CSS Inyectado para estilos de tarjeta personalizados (Mejorado)
    const customStyles = `
    <style>
        .payment-card-label {
            transition: all 0.2s ease-in-out;
            border: 1px solid #e2e8f0;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            background-color: white;
        }
        .payment-card-label:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 12px -2px rgba(0, 0, 0, 0.1), 0 3px 6px -2px rgba(0, 0, 0, 0.05);
            border-color: #93c5fd; /* Blue 300 */
        }
        .payment-card-label.selected-card {
            background-color: #f0f9ff; /* Sky Blue 50 */
            border-color: #3b82f6;    /* Blue 500 */
            box-shadow: 0 0 0 2px #3b82f6;
        }
        .payment-card-label .icon-container {
            width: 50px;
            height: 50px;
            min-width: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            background-color: #dbeafe; /* Blue 100 */
            color: #1d4ed8; /* Blue 700 */
        }
        .payment-card-label.selected-card .icon-container {
            background-color: #3b82f6;
            color: white;
        }
        .payment-price {
            font-size: 1.35rem;
            font-weight: 800;
            color: #059669; /* Emerald 600 */
        }
        .payment-concept {
            font-weight: 700;
            color: #1f2937; /* Gray 800 */
            line-height: 1.3;
        }
        .payment-meta {
            font-size: 0.8rem;
            color: #6b7280; /* Gray 500 */
        }
        .payment-checkbox {
            width: 1.5em;
            height: 1.5em;
            min-width: 1.5em;
            cursor: pointer;
            border-radius: 6px;
        }
        .payment-bundle-badge {
            background-color: #fef3c7;
            color: #92400e;
            font-size: 0.7rem;
            padding: 2px 6px;
            border-radius: 4px;
            font-weight: 600;
            display: inline-block;
            margin-top: 4px;
            border: 1px solid #fde68a;
        }
        .payment-item-detail {
            font-size: 0.75rem;
            color: #4b5563;
            margin-top: 2px;
            padding-left: 12px;
            position: relative;
        }
        .payment-item-detail::before {
            content: '•';
            position: absolute;
            left: 0;
            color: #3b82f6;
        }
        .total-applied-card {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            border: none;
            overflow: hidden;
        }
        .total-applied-card .icon-circle {
            background: rgba(255, 255, 255, 0.2);
            width: 42px;
            height: 42px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
        .total-applied-amount {
            font-size: 1.75rem;
            letter-spacing: -1px;
        }
    </style>
    `;

    let html = customStyles;

    // Procesar datos
    let allPayments = [];
    if (Array.isArray(vouchers)) {
        vouchers.forEach(pago => {
            if (pago.serial && pago.monto) {
                const montoLimpio = String(pago.monto).replace(/,/g, '');
                allPayments.push({
                    secuencia: pago.serial,
                    concepto: pago.concepto,
                    monto: parseFloat(montoLimpio),
                    monto_matricula: parseFloat(pago.monto_matricula || 0),
                    monto_ensenanza: parseFloat(pago.monto_ensenanza || 0),
                    fecha: pago.fecha,
                    items: pago.items || [], // Guardar items
                    original: pago
                });
            }
        });
    }

    const totalVouchers = allPayments.length;

    // Header más llamativo con el total
    const headerHtml = `
    <div class="alert alert-primary d-flex align-items-center mb-4 shadow-sm border-0 rounded-lg p-3" role="alert">
        <i class="fas fa-receipt fa-lg me-3"></i>
        <div class="flex-grow-1">
            <strong class="text-lg text-primary">Pagos Encontrados</strong>
            <div class="small mt-1 text-secondary">Se encontraron <span class="fw-bold">${totalVouchers}</span> voucher(s) para este DNI/Código. Seleccione los que desea aplicar.</div>
        </div>
        <span class="badge bg-primary rounded-pill fs-6 py-2 px-3">${totalVouchers}</span>
    </div>`;

    html += headerHtml;

    // Contenedor de la lista con scroll
    html += '<div class="payment-list-container mb-3 px-1" style="max-height: 400px; overflow-y: auto;">';

    if (totalVouchers === 0) {
        html += '<div class="text-center py-5 text-muted bg-light rounded-xl shadow-inner"><i class="fas fa-search mb-2 fa-2x"></i><p>No se encontraron detalles de pagos.</p></div>';
    } else {
        allPayments.forEach((pago, index) => {
            const monto = pago.monto.toFixed(2);
            const fecha = pago.fecha ? pago.fecha.split('T')[0] : '-';
            const isChecked = ''; // SIN SELECCIONAR por defecto

            // Determinar un icono basado en el concepto (simple heurística)
            let iconClass = 'fas fa-money-check-alt';
            if (pago.concepto.toLowerCase().includes('matricula')) {
                iconClass = 'fas fa-user-graduate';
            } else if (pago.concepto.toLowerCase().includes('enseñanza') || pago.concepto.toLowerCase().includes('maraton')) {
                iconClass = 'fas fa-book-open';
            }

            html += `
                <label class="card mb-3 payment-card-label rounded-lg" id="label_pago_${index}">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center">
                            
                            <!-- Icono -->
                            <div class="me-3 d-none d-sm-block">
                                <div class="icon-container shadow-sm">
                                    <i class="${iconClass} fa-lg"></i>
                                </div>
                            </div>
                            
                            <!-- Contenido -->
                            <div class="flex-grow-1 me-3">
                                <div class="payment-concept mb-1 text-wrap">${pago.concepto.split(' (+ ')[0]}</div>
                                
                                ${pago.items && pago.items.length > 1 ? `
                                    <div class="payment-items-breakdown mb-2">
                                        <div class="payment-bundle-badge mb-1">
                                            <i class="fas fa-layer-group me-1"></i> Paquete de ${pago.items.length} conceptos
                                        </div>
                                        ${pago.items.map(item => `
                                            <div class="payment-item-detail">
                                                ${item.descripcion} (S/ ${parseFloat(item.monto).toFixed(2)})
                                            </div>
                                        `).join('')}
                                    </div>
                                ` : ''}

                                <div class="payment-meta d-flex flex-wrap gap-3">
                                    <span title="Fecha"><i class="far fa-calendar-alt me-1"></i>${fecha}</span>
                                    <span title="Código" class="font-monospace bg-light px-1 rounded border"><i class="fas fa-barcode me-1"></i>${pago.secuencia}</span>
                                </div>
                            </div>

                            <!-- Monto & Checkbox -->
                            <div class="ms-auto text-end d-flex align-items-center">
                                <div class="me-3 text-nowrap">
                                    <div class="small text-muted fw-bold">MONTO</div>
                                    <div class="payment-price">S/ ${monto}</div>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input payment-checkbox" type="checkbox" 
                                            value="${pago.secuencia}" 
                                            data-monto="${monto}" 
                                            data-matricula="${pago.monto_matricula}" 
                                            data-ensenanza="${pago.monto_ensenanza}" 
                                            data-fecha="${fecha}"
                                            data-index="${index}"
                                            id="pago_${index}" 
                                            ${isChecked}
                                            onchange="actualizarPagosSeleccionados()">
                                </div>
                            </div>
                        </div>
                    </div>
                </label>
            `;
        });
    }

    html += '</div>';

    // Footer Total (Estilo Tarjeta Resumen) - Rediseñado
    html += `
        <div class="card total-applied-card text-white shadow-lg border-0 rounded-lg mt-4">
            <div class="card-body d-flex justify-content-between align-items-center p-3">
                <div class="d-flex align-items-center">
                    <div class="icon-circle me-3">
                        <i class="fas fa-calculator fw-bold fa-lg"></i>
                    </div>
                    <div>
                        <div class="text-uppercase small text-white-50 fw-bold" style="font-size: 0.65rem; letter-spacing: 0.5px;">TOTAL APLICADO</div>
                        <div class="small text-white-50 d-none d-sm-block" style="font-size: 0.75rem;">Suma de pagos seleccionados</div>
                    </div>
                </div>
                <h2 class="mb-0 fw-bold text-white total-applied-amount" id="total_seleccionado">S/ 0.00</h2>
            </div>
        </div>
    `;

    $('#voucher_details').html(html).slideDown();

    actualizarPagosSeleccionados();
}

function actualizarPagosSeleccionados() {
    let total = 0;
    let totalMatricula = 0;
    let totalEnsenanza = 0;
    let secuencias = [];
    let fechaReciente = null;
    pagosSeleccionadosDetalles = []; // Limpiar para el resumen

    // Resetear estilos visuales primero
    $('.payment-card-label').removeClass('selected-card');

    $('.payment-checkbox:checked').each(function () {
        const monto = parseFloat($(this).data('monto'));
        const matricula = parseFloat($(this).data('matricula')) || 0;
        const ensenanza = parseFloat($(this).data('ensenanza')) || 0;
        const secuencia = $(this).val();
        const fecha = $(this).data('fecha');
        const index = $(this).data('index');
        const card = $('#label_pago_' + index);
        const concepto = card.find('.payment-concept').text() || 'Pago';

        // Añadir clase visual de seleccionado a la tarjeta padre
        card.addClass('selected-card');

        total += monto;
        totalMatricula += matricula;
        totalEnsenanza += ensenanza;
        secuencias.push(secuencia);

        // Guardar detalles para el resumen final
        pagosSeleccionadosDetalles.push({
            secuencia: secuencia,
            monto: monto,
            concepto: concepto
        });

        if (!fechaReciente) {
            fechaReciente = fecha;
        }
    });

    // Actualizar UI del total
    $('#total_seleccionado').text('S/ ' + total.toFixed(2));

    // Actualizar campos ocultos
    $('#monto_matricula').val(totalMatricula.toFixed(2));
    $('#monto_ensenanza').val(totalEnsenanza.toFixed(2));
    $('#monto_total_pagado').val(total.toFixed(2));
    $('#voucher_secuencia').val(secuencias.join(','));

    if (fechaReciente) {
        $('#fecha_emision_voucher').val(fechaReciente);
        $('#fecha_emision_container').show();
    } else {
        $('#fecha_emision_container').hide();
    }
}

// ======================================================================

function validateForm() {
    let valid = true;
    const currentStepDiv = $('.step-content[data-step="' + currentStep + '"]');

    // Validar campos requeridos visibles
    currentStepDiv.find('input[required]:visible, select[required]:visible').each(function () {
        if (!$(this).val()) {
            $(this).addClass('is-invalid');
            valid = false;
        } else {
            $(this).removeClass('is-invalid');
        }
    });

    // Validaciones específicas por paso
    if (currentStep == 1) {
        // Validar contraseñas solo si son visibles
        const pass = $('#estudiante_password').val();
        const confirm = $('#estudiante_password_confirmation').val();

        if ($('#estudiante_password').is(':visible') && pass && confirm && pass !== confirm) {
            Toast.fire({ icon: 'error', title: 'Las contraseñas no coinciden' });
            $('#estudiante_password_confirmation').addClass('is-invalid');
            valid = false;
        }

        // Validar DNI verificado
        if (!$('#estudiante_dni').val()) {
            Toast.fire({ icon: 'error', title: 'Debe verificar su DNI primero' });
            valid = false;
        }
    }

    if (currentStep == 4) {
        // Validar archivos
        const requiredDocs = ['foto', 'dni_pdf', 'certificado_estudios', 'voucher_pago'];
        requiredDocs.forEach(function (doc) {
            const input = $('#' + doc);
            if (input.length && !input.val()) {
                input.closest('.file-upload-card').addClass('border-danger');
                valid = false;
            }
        });
    }

    if (!valid) {
        Toast.fire({ icon: 'error', title: 'Por favor complete todos los campos requeridos correctamente' });
    }

    return valid;
}

function verificarPostulante(btnElement, callback = null) {

    const dni = $('#check_dni').val();
    const digito = $('#check_dv').val();

    if (dni.length !== 8) {
        Swal.fire({
            icon: 'error',
            title: 'DNI Inválido',
            text: 'El DNI debe contener exactamente 8 dígitos.',
            confirmButtonText: 'Aceptar'
        });
        return;
    }

    if (!digito || digito.length !== 1) {
        Swal.fire({
            icon: 'warning',
            title: 'Dígito Requerido',
            text: 'Por favor, ingrese el dígito verificador (el número después del guion en su DNI).',
            confirmButtonText: 'Aceptar'
        });
        return;
    }

    let btn = $(btnElement);
    if (!btn || btn.length === 0) {
        btn = $('#btn-verificar-dni');
    }
    const originalHtml = btn.html();
    // Registramos isSuccess para el callback final en el block always()
    let isSuccess = false;
    let apiResponse = null;
    $.post('/postulacion/check-postulante', {
        dni: dni,
        digito: digito,
        check_dv: digito, // Enviamos ambos por compatibilidad si hay caché
        _token: $('meta[name="csrf-token"]').attr('content')
    }, function (response) {
        try {
            isSuccess = true;
            apiResponse = response;
            let swalIcon = 'success';
            let swalTitle = 'Verificación Exitosa';
            let swalText = response.message || 'Datos listos para continuar.';

            if (response.status === 'registered') {
                // Preparar el HTML con información detallada
                const postulacion = response.postulacion || {};

                // Determinar el color del badge y el ícono según el estado
                let estadoBadgeClass = 'badge bg-warning';
                let estadoIcon = 'fas fa-clock';
                let tituloModal = 'Postulación ya Registrada';
                let iconoModal = 'warning';

                if (postulacion.estado === 'aprobada' || postulacion.estado === 'aprobado') {
                    estadoBadgeClass = 'badge bg-success';
                    estadoIcon = 'fas fa-check-circle';
                    tituloModal = '¡Postulación Aprobada!';
                    iconoModal = 'success';
                } else if (postulacion.estado === 'rechazada' || postulacion.estado === 'rechazado') {
                    estadoBadgeClass = 'badge bg-danger';
                    estadoIcon = 'fas fa-times-circle';
                    tituloModal = 'Postulación Rechazada';
                    iconoModal = 'error';
                } else if (postulacion.estado === 'observada' || postulacion.estado === 'observado') {
                    estadoBadgeClass = 'badge bg-info';
                    estadoIcon = 'fas fa-exclamation-circle';
                    tituloModal = 'Postulación con Observaciones';
                    iconoModal = 'info';
                }

                // Mensajes específicos según el estado
                let mensajeInstrucciones = '';
                if (postulacion.estado === 'pendiente') {
                    mensajeInstrucciones = `
                    <div style="background: #fff3cd; border-left: 4px solid #ffc107; padding: 12px; border-radius: 4px; margin-bottom: 10px;">
                        <p style="margin: 0; font-size: 14px; color: #856404;">
                            <i class="fas fa-hourglass-half" style="margin-right: 5px;"></i>
                            <strong>Tu postulación está en revisión</strong>
                        </p>
                        <p style="margin: 8px 0 0 0; font-size: 13px; color: #856404;">
                            El equipo administrativo está verificando tus documentos y pago. 
                            Recibirás una notificación cuando sea aprobada o si requiere correcciones.
                        </p>
                    </div>
                `;
                } else if (postulacion.estado === 'aprobada' || postulacion.estado === 'aprobado') {
                    mensajeInstrucciones = `
                    <div style="background: #d4edda; border-left: 4px solid #28a745; padding: 12px; border-radius: 4px; margin-bottom: 10px;">
                        <p style="margin: 0; font-size: 14px; color: #155724;">
                            <i class="fas fa-check-circle" style="margin-right: 5px;"></i>
                            <strong>¡Felicitaciones! Tu postulación fue aprobada</strong>
                        </p>
                        <ol style="margin: 10px 0 0 20px; padding: 0; font-size: 13px; color: #155724;">
                            <li style="margin-bottom: 5px;">Ingresa al <strong>Portal del Estudiante</strong></li>
                            <li style="margin-bottom: 5px;">Descarga tu <strong>constancia de inscripción</strong></li>
                            <li style="margin-bottom: 5px;">Revisa tu aula y horario asignados</li>
                            <li>Prepárate para el inicio de clases</li>
                        </ol>
                    </div>
                `;
                } else if (postulacion.estado === 'rechazada' || postulacion.estado === 'rechazado') {
                    mensajeInstrucciones = `
                    <div style="background: #f8d7da; border-left: 4px solid #dc3545; padding: 12px; border-radius: 4px; margin-bottom: 10px;">
                        <p style="margin: 0; font-size: 14px; color: #721c24;">
                            <i class="fas fa-times-circle" style="margin-right: 5px;"></i>
                            <strong>Tu postulación fue rechazada</strong>
                        </p>
                        <p style="margin: 8px 0 0 0; font-size: 13px; color: #721c24;">
                            Ingresa al Portal del Estudiante para ver el motivo del rechazo. 
                            Si deseas volver a postular, contacta con la administración.
                        </p>
                    </div>
                `;
                } else if (postulacion.estado === 'observada' || postulacion.estado === 'observado') {
                    mensajeInstrucciones = `
                    <div style="background: #d1ecf1; border-left: 4px solid #0dcaf0; padding: 12px; border-radius: 4px; margin-bottom: 10px;">
                        <p style="margin: 0; font-size: 14px; color: #055160;">
                            <i class="fas fa-exclamation-circle" style="margin-right: 5px;"></i>
                            <strong>Tu postulación tiene observaciones</strong>
                        </p>
                        <p style="margin: 8px 0 0 0; font-size: 13px; color: #055160;">
                            Ingresa al Portal del Estudiante para ver las observaciones y 
                            corregir los documentos o información solicitada.
                        </p>
                    </div>
                `;
                }

                const htmlContent = `
                <div style="text-align: left; padding: 10px;">
                    <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 15px;">
                        <h6 style="color: #495057; margin-bottom: 10px; font-weight: bold;">
                            <i class="fas fa-info-circle" style="color: #0d6efd;"></i> Información de tu Postulación
                        </h6>
                        <table style="width: 100%; font-size: 14px;">
                            <tr>
                                <td style="padding: 5px 0; color: #6c757d;"><strong>Código:</strong></td>
                                <td style="padding: 5px 0;">${postulacion.codigo || 'N/A'}</td>
                            </tr>
                            <tr>
                                <td style="padding: 5px 0; color: #6c757d;"><strong>Ciclo:</strong></td>
                                <td style="padding: 5px 0;">${postulacion.ciclo || 'N/A'}</td>
                            </tr>
                            <tr>
                                <td style="padding: 5px 0; color: #6c757d;"><strong>Estado:</strong></td>
                                <td style="padding: 5px 0;">
                                    <span class="${estadoBadgeClass}" style="padding: 4px 10px; border-radius: 4px; font-size: 12px;">
                                        <i class="${estadoIcon}"></i> ${postulacion.estado_texto || 'Pendiente'}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding: 5px 0; color: #6c757d;"><strong>Carrera:</strong></td>
                                <td style="padding: 5px 0;">${postulacion.carrera || 'N/A'}</td>
                            </tr>
                            <tr>
                                <td style="padding: 5px 0; color: #6c757d;"><strong>Turno:</strong></td>
                                <td style="padding: 5px 0;">${postulacion.turno || 'N/A'}</td>
                            </tr>
                            <tr>
                                <td style="padding: 5px 0; color: #6c757d;"><strong>Fecha:</strong></td>
                                <td style="padding: 5px 0;">${postulacion.fecha_postulacion || 'N/A'}</td>
                            </tr>
                        </table>
                    </div>
                    
                    ${mensajeInstrucciones}
                    
                    <div style="background: #e7f3ff; border-left: 4px solid #0d6efd; padding: 12px; border-radius: 4px; margin-bottom: 10px;">
                        <p style="margin: 0; font-size: 14px; color: #084298;">
                            <i class="fas fa-sign-in-alt" style="margin-right: 5px;"></i>
                            <strong>Accede al Portal del Estudiante</strong>
                        </p>
                        <p style="margin: 8px 0 0 0; font-size: 13px; color: #495057;">
                            Ingresa con tu <strong>email</strong> y tu <strong>DNI como contraseña</strong> 
                            para ver el estado completo, descargar documentos y gestionar tu información.
                        </p>
                    </div>
                    
                    <p style="font-size: 13px; color: #6c757d; margin: 10px 0 0 0; text-align: center;">
                        <i class="fas fa-info-circle"></i> No puedes crear una nueva postulación mientras tengas una activa
                    </p>
                </div>
            `;

                // Mostrar SweetAlert con HTML personalizado
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: iconoModal,
                        title: tituloModal,
                        html: htmlContent,
                        confirmButtonText: '<i class="fas fa-sign-in-alt"></i> Ir al Portal',
                        showCancelButton: true,
                        cancelButtonText: 'Cerrar',
                        confirmButtonColor: '#0d6efd',
                        cancelButtonColor: '#6c757d',
                        width: '650px',
                        didOpen: () => {
                            if (postulacion.estado === 'aprobada' || postulacion.estado === 'aprobado') {
                                lanzarConfetti();
                            }
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = '/login';
                        }
                    });
                } else {
                    Toast.fire({ icon: 'warning', title: 'Ya tienes una postulación registrada para este ciclo.' });
                }
                return; // Evitar que continúe al resto de la lógica de éxito
            } else {
                // Nuevo o Recurrente
                $('#estudiante_dni').val(dni);
                $('#personal_fields').slideDown();

                if (response.estudiante) {
                    // Llenar campos si el estudiante ya existe
                    $('#estudiante_nombre').val(response.estudiante.nombre);
                    $('#estudiante_apellido_paterno').val(response.estudiante.apellido_paterno);
                    $('#estudiante_apellido_materno').val(response.estudiante.apellido_materno);
                    const fechaNac = response.estudiante.fecha_nacimiento;
                    $('#estudiante_fecha_nacimiento').val(fechaNac ? fechaNac.split('T')[0] : '');
                    $('#estudiante_genero').val(response.estudiante.genero);
                    $('#estudiante_telefono').val(response.estudiante.telefono);
                    $('#estudiante_direccion').val(response.estudiante.direccion);
                    $('#estudiante_email').val(response.estudiante.email);

                    // RESALTAR CAMPOS (Pintar lo que se autocompletó con un delay mayor para asegurar visibilidad tras slideDown)
                    setTimeout(() => {
                        highlightFields('#estudiante_nombre, #estudiante_apellido_paterno, #estudiante_apellido_materno, #estudiante_fecha_nacimiento, #estudiante_genero');
                    }, 600);

                    // Ocultar y quitar required a contraseñas para recurrentes
                    $('#estudiante_password').prop('required', false).closest('.col-md-3').hide();
                    $('#estudiante_password_confirmation').prop('required', false).closest('.col-md-3').hide();

                    // NUEVO: Pre-cargar datos de padres si existen
                    if (response.padres) {
                        precargarDatosPadres(response.padres);
                    }

                    // NUEVO: Pre-cargar datos académicos y archivos si existen
                    if (response.ultima_postulacion) {
                        if (response.ultima_postulacion.datos_academicos) {
                            precargarDatosAcademicos(response.ultima_postulacion.datos_academicos);
                        }
                        if (response.ultima_postulacion.archivos) {
                            mostrarArchivosExistentes(response.ultima_postulacion.archivos);
                        }
                    }

                    swalIcon = 'success';
                    swalTitle = 'Estudiante Encontrado';
                    swalText = 'Sus datos personales, de padres y académicos se han cargado automáticamente.';

                } else {
                    // Estudiante nuevo: asegurar que contraseñas sean requeridas y visibles
                    $('#estudiante_password').prop('required', true).closest('.col-md-3').show();
                    $('#estudiante_password_confirmation').prop('required', true).closest('.col-md-3').show();

                    // Limpiar campos por si acaso
                    $('#estudiante_nombre').val('');
                    $('#estudiante_apellido_paterno').val('');
                    $('#estudiante_apellido_materno').val('');
                    $('#estudiante_fecha_nacimiento').val('');
                    $('#estudiante_genero').val('');
                    $('#estudiante_telefono').val('');
                    $('#estudiante_direccion').val('');
                    $('#estudiante_email').val('');
                    $('#estudiante_password').val('');
                    $('#estudiante_password_confirmation').val('');

                    swalIcon = 'info';
                    swalTitle = 'Postulante Nuevo';
                    swalText = 'Consultando DATOS para autocompletar sus datos...';

                    // NUEVO: Consultar RENIEC automáticamente para estudiantes nuevos
                    consultarReniecEstudiante(dni);
                }

                // Ocultar sección de verificación al tener éxito
                $('#section-verificacion').slideUp(400);

                // Mostrar SweetAlert según el contexto
                if (typeof Swal !== 'undefined') {
                    const isModalVisible = $('#postulacionModal').is(':visible');
                    
                    if (!isModalVisible) {
                        // Si consultó desde afuera y no está registrado, preguntamos si quiere registrarse
                        Swal.fire({
                            icon: 'info',
                            title: 'Sin postulación activa',
                            text: 'No hemos encontrado una postulación activa con sus datos. ¿Desea iniciar su inscripción ahora?',
                            showCancelButton: true,
                            confirmButtonText: '¡Sí, inscribirme!',
                            cancelButtonText: 'Ahora no',
                            confirmButtonColor: '#8bc34a',
                            cancelButtonColor: '#6c757d'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                if (typeof openPostulacionModal === 'function') {
                                    openPostulacionModal();
                                }
                            }
                        });
                    } else {
                        // Si ya está en el modal, flujo normal
                        Swal.fire({
                            icon: swalIcon,
                            title: swalTitle,
                            text: swalText,
                            confirmButtonText: 'Continuar'
                        });
                    }
                }
            }
        } catch (err) {
            console.error('Error interno en success callback:', err);
            Toast.fire({ icon: 'error', title: 'Error al procesar la respuesta del servidor' });
        }
    }).fail(function (xhr) {
        console.error('Error verificar postulante:', xhr);
        let msg = 'Ocurrió un error inesperado al verificar el postulante.';
        if (xhr.responseJSON && xhr.responseJSON.message) {
            msg = xhr.responseJSON.message;
        } else if (xhr.statusText) {
            msg = 'Error de conexión: ' + xhr.statusText;
        }

        // Usar SweetAlert para errores
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Error de Verificación',
                text: msg,
                confirmButtonText: 'Reintentar'
            });
        } else {
            Toast.fire({
                icon: 'error',
                title: msg
            });
        }
    }).always(function () {
        btn.prop('disabled', false).html(originalHtml);
        if (typeof callback === 'function') {
            callback(isSuccess, apiResponse);
        }
    });
}

// Nueva función para el botón de consulta rápida
function consultarEstadoPostulacion() {
    const dni = $('#check_dni').val();
    const dv = $('#check_dv').val();

    if (!dni || dni.length !== 8 || dv === null || dv === '') {
        Swal.fire({
            icon: 'info',
            title: 'Datos Incompletos',
            text: 'Por favor, ingrese su DNI y dígito verificador en el formulario para consultar su estado.',
            confirmButtonColor: '#0d6efd'
        });
        return;
    }

    // Usamos el botón de verificación como referencia para el spinner
    verificarPostulante($('#btn-verificar-dni'));
}

/**
 * NUEVA: Permite consultar el estado directamente desde cualquier parte de la página
 * sin necesidad de abrir el modal primero. Solicita los datos vía SweetAlert2.
 */
function consultarEstadoDirecto() {
    Swal.fire({
        title: 'Consulta tu Estado',
        text: 'Ingresa tu DNI y dígito verificador',
        html: `
            <div class="d-flex flex-column gap-3 mt-3">
                <div class="form-group text-start">
                    <label class="mb-1 fw-bold">Número de DNI</label>
                    <input id="swal-dni" class="form-control" placeholder="8 dígitos" maxlength="8">
                </div>
                <div class="form-group text-start">
                    <label class="mb-1 fw-bold">Dígito Verificador (DV)</label>
                    <input id="swal-dv" class="form-control text-center" placeholder="Dígito después del guion" maxlength="1">
                </div>
                <div class="text-center mt-2">
                    <img src="${getBaseUrl()}/assets_cepre/img/ejemplo_verificador.jpg" style="max-width: 100%; border-radius: 8px; border: 1px solid #ddd;">
                    <small class="text-muted d-block mt-1">Ubicación del dígito verificador</small>
                </div>
            </div>
        `,
        showCancelButton: true,
        confirmButtonText: 'Consultar Estado',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#8bc34a',
        cancelButtonColor: '#e91e63',
        preConfirm: () => {
            const dni = document.getElementById('swal-dni').value;
            const dv = document.getElementById('swal-dv').value;
            if (!dni || dni.length !== 8) {
                Swal.showValidationMessage('Por favor ingrese un DNI de 8 dígitos');
                return false;
            }
            if (!dv || dv.length !== 1) {
                Swal.showValidationMessage('Por favor ingrese el dígito verificador');
                return false;
            }
            return { dni, dv };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // Seteamos los valores en el modal por si acaso el usuario luego decide abrirlo
            $('#check_dni').val(result.value.dni);
            $('#check_dv').val(result.value.dv);
            
            // Mostrar cargando
            Swal.fire({
                title: 'Consultando...',
                text: 'Buscando tu información de postulación',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Llamamos a la verificación principal
            // Pass null as btnElement so it doesn't try to use a button's spinner
            verificarPostulante(null, (success, response) => {
                // El modal de verificarPostulante se encargará de mostrar el resultado
            });
        }
    });
}

function consultarDNIPadre(tipo, btnElement) {
    const dni = $('#' + tipo + '_dni').val();
    if (dni.length !== 8) {
        Toast.fire({ icon: 'error', title: 'DNI debe tener 8 dígitos' });
        return;
    }

    let btn = $(btnElement);
    if (btn.length === 0) {
        btn = $(event.target);
    }
    const originalHtml = btn.html();
    btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>...');

    $.post('/api/reniec/consultar', {
        dni: dni,
        _token: $('meta[name="csrf-token"]').attr('content')
    }, function (response) {
        if (response.success && response.data) {
            const nombreFull = response.data.apellido_paterno + ' ' + response.data.apellido_materno;
            $('#' + tipo + '_nombre').val(response.data.nombres);
            $('#' + tipo + '_apellidos').val(nombreFull);

            // Autogenerar email si no tiene uno (RENIEC no devuelve emails)
            autoGenerateEmail($('#' + tipo + '_dni').val(), '#' + tipo + '_email');

            // Resaltar campos autocompletados
            highlightFields('#' + tipo + '_nombre, #' + tipo + '_apellidos, #' + tipo + '_email');

            Toast.fire({ icon: 'success', title: 'Datos encontrados' });
        } else {
            Toast.fire({ icon: 'warning', title: 'No se encontraron datos' });
        }
    }).fail(function () {
        Toast.fire({ icon: 'error', title: 'Error al consultar RENIEC' });
    }).always(function () {
        btn.prop('disabled', false).html(originalHtml);
    });
} // <--- Cierre CORRECTO de consultarDNIPadre

// Nueva función para consultar RENIEC y autocompletar datos del estudiante
function consultarReniecEstudiante(dni) {


    // Mostrar indicador de carga
    Toast.fire({ icon: 'info', title: 'Consultando DATOS...', text: 'Por favor espere' });

    $.ajax({
        url: '/api/reniec/consultar',
        method: 'POST',
        data: {
            dni: dni,
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        success: function (response) {


            if (response.success && response.data) {
                // Autocompletar campos con datos de RENIEC
                $('#estudiante_nombre').val(response.data.nombres || '');
                $('#estudiante_apellido_paterno').val(response.data.apellido_paterno || '');
                $('#estudiante_apellido_materno').val(response.data.apellido_materno || '');

                // Resaltar campos autocompletados (Damos un pequeño margen para que el usuario procese el éxito)
                setTimeout(() => {
                    highlightFields('#estudiante_nombre, #estudiante_apellido_paterno, #estudiante_apellido_materno, #estudiante_fecha_nacimiento, #estudiante_genero');
                }, 500);

                if (response.data.fecha_nacimiento) {
                    $('#estudiante_fecha_nacimiento').val(response.data.fecha_nacimiento);
                }

                if (response.data.genero) {
                    $('#estudiante_genero').val(response.data.genero);
                }

                if (response.data.direccion) {
                    $('#estudiante_direccion').val(response.data.direccion);
                }

                // Autogenerar email del estudiante si está vacío
                autoGenerateEmail(dni, '#estudiante_email');
                // Mostrar mensaje de éxito
                Toast.fire({ icon: 'success', title: 'Datos cargados desde el sistema' });

                // Mostrar alerta informativa
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Datos Autocompletos',
                        html: 'Se han cargado automáticamente sus datos desde el sistema:<br><br>' +
                            '<strong>' + response.data.nombres + ' ' +
                            response.data.apellido_paterno + ' ' +
                            response.data.apellido_materno + '</strong><br><br>' +
                            'Por favor verifique y complete los datos faltantes.',
                        confirmButtonText: 'Continuar'
                    });
                }
            } else {
                console.warn('No se encontraron datos en el sistema');
                Toast.fire({ icon: 'warning', title: 'No se encontraron datos en el sistema', text: 'Por favor complete manualmente.' });
            }
        },
        error: function (xhr) {
            console.error('Error consultando el sistema:', xhr);
            let errorMsg = 'No se pudo consultar el sistema. Por favor complete los datos manualmente.';

            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMsg = xhr.responseJSON.message;
            }

            Toast.fire({ icon: 'warning', title: errorMsg });
        }
    });
}

// Nueva función para pre-cargar datos de padres
function precargarDatosPadres(padres) {


    let padresCargados = 0;

    // Pre-cargar datos del padre si existen
    if (padres.padre) {

        $('#padre_dni').val(padres.padre.numero_documento || '');
        $('#padre_nombre').val(padres.padre.nombre || '');
        const apellidosPadre = (padres.padre.apellido_paterno || '') + ' ' + (padres.padre.apellido_materno || '');
        $('#padre_apellidos').val(apellidosPadre.trim());
        $('#padre_telefono').val(padres.padre.telefono || '');

        // Si no tiene correo guardado, generar el defaults
        if (padres.padre.email) {
            $('#padre_email').val(padres.padre.email);
        } else {
            autoGenerateEmail(padres.padre.numero_documento, '#padre_email');
        }

        // Nota: ocupación no está en el modelo User, se deja vacío
        padresCargados++;
    } else {

    }

    // Pre-cargar datos de la madre si existen
    if (padres.madre) {

        $('#madre_dni').val(padres.madre.numero_documento || '');
        $('#madre_nombre').val(padres.madre.nombre || '');
        const apellidosMadre = (padres.madre.apellido_paterno || '') + ' ' + (padres.madre.apellido_materno || '');
        $('#madre_apellidos').val(apellidosMadre.trim());
        $('#madre_telefono').val(padres.madre.telefono || '');

        // Si no tiene correo guardado, generar el defaults
        if (padres.madre.email) {
            $('#madre_email').val(padres.madre.email);
        } else {
            autoGenerateEmail(padres.madre.numero_documento, '#madre_email');
        }

        padresCargados++;
    } else {

    }

    // Mostrar mensaje informativo si se cargaron datos
    if (padresCargados > 0) {
        // Resaltar campos de padres
        highlightFields('#padre_dni, #padre_nombre, #padre_apellidos, #padre_telefono, #padre_email, #madre_dni, #madre_nombre, #madre_apellidos, #madre_telefono, #madre_email');

        const mensaje = padresCargados === 2 ? 'Datos de padre y madre cargados' :
            padres.padre ? 'Datos del padre cargados' : 'Datos de la madre cargados';
        Toast.fire({ icon: 'success', title: mensaje + ' de postulación anterior' });
    } else {

    }
}

// Nueva función para pre-cargar datos académicos (colegio, carrera, etc.)
function precargarDatosAcademicos(datosAcademicos) {


    // Pre-cargar año de egreso
    if (datosAcademicos.anio_egreso) {
        $('#anio_egreso').val(datosAcademicos.anio_egreso);
    }

    // Pre-cargar carrera y turno si existen
    if (datosAcademicos.carrera_id) {
        $('#carrera_id').val(datosAcademicos.carrera_id);
    }
    if (datosAcademicos.turno_id) {
        $('#turno_id').val(datosAcademicos.turno_id);
    }
    if (datosAcademicos.tipo_inscripcion) {
        $('#tipo_inscripcion').val(datosAcademicos.tipo_inscripcion);
    }

    // Pre-cargar colegio y ubicación si existe
    if (datosAcademicos.centro_educativo) {
        const colegio = datosAcademicos.centro_educativo;



        // Si tenemos datos de ubicación, pre-cargar los selectores
        if (colegio.departamento) {
            // Pre-seleccionar departamento
            $('#departamento').val(colegio.departamento);

            // Cargar provincias y pre-seleccionar
            if (colegio.provincia) {
                loadProvincias(colegio.departamento);

                // Esperar un momento para que se carguen las provincias
                setTimeout(function () {
                    $('#provincia').val(colegio.provincia);

                    // Cargar distritos y pre-seleccionar
                    if (colegio.distrito) {
                        loadDistritos(colegio.departamento, colegio.provincia);

                        setTimeout(function () {
                            $('#distrito').val(colegio.distrito);

                            // Habilitar búsqueda de colegio
                            $('#buscar_colegio').prop('disabled', false);
                            $('#btnBuscarColegio').prop('disabled', false);

                            // Mostrar el colegio seleccionado
                            $('#centro_educativo_id').val(colegio.id);
                            $('#buscar_colegio').val(colegio.nombre);
                            mostrarColegioSeleccionado({
                                id: colegio.id,
                                nombre: colegio.nombre
                            });
                        }, 500);
                    }
                }, 500);
            }
        } else {
            // Si no hay datos de ubicación, solo mostrar el colegio
            $('#centro_educativo_id').val(colegio.id);
            $('#buscar_colegio').val(colegio.nombre);
            mostrarColegioSeleccionado({
                id: colegio.id,
                nombre: colegio.nombre
            });
        }

        // Resaltar campos académicos
        highlightFields('#anio_egreso, #buscar_colegio, #carrera_id, #turno_id, #tipo_inscripcion');

        Toast.fire({ icon: 'success', title: 'Colegio cargado: ' + colegio.nombre });
    }
}

// Nueva función para mostrar archivos existentes
function mostrarArchivosExistentes(archivos) {


    // Mapeo de campos de archivo a sus IDs en el formulario
    const mapeoArchivos = {
        'foto_path': 'foto',
        'dni_path': 'dni_pdf',
        'certificado_estudios_path': 'certificado_estudios',
        'voucher_path': 'voucher_pago',
        'carta_compromiso_path': 'carta_compromiso',
        'constancia_estudios_path': 'constancia_estudios'
    };

    let archivosEncontrados = 0;

    // Iterar sobre cada archivo
    Object.keys(mapeoArchivos).forEach(function (campo) {
        const inputId = mapeoArchivos[campo];
        const url = archivos[campo];

        if (url) {
            archivosEncontrados++;

            // Encontrar el input de archivo
            const $input = $('#' + inputId);
            const $container = $input.closest('.mb-3');

            // Quitar el required del input (ya tiene archivo)
            $input.prop('required', false);

            // Agregar badge y botón "Ver" si no existe ya
            if ($container.find('.archivo-existente-badge').length === 0) {
                const badge = `
                    <div class="archivo-existente-badge mt-2">
                        <span class="badge bg-success me-2">
                            <i class="fas fa-check-circle"></i> Archivo existente
                        </span>
                        <button type="button" class="btn btn-sm btn-outline-info" onclick="verArchivoModal('${url}', '${inputId}')">
                            <i class="fas fa-eye"></i> Ver archivo
                        </button>
                        <input type="hidden" name="${inputId}_existente" value="${url}">
                    </div>
                `;
                $container.append(badge);
            }
        }
    });

    if (archivosEncontrados > 0) {
        Toast.fire({ icon: 'success', title: `${archivosEncontrados} archivo(s) encontrado(s) de postulación anterior` });
    }
}

// Nueva función para ver archivo en modal
function verArchivoModal(url, tipo) {


    // Determinar si es imagen o PDF
    const esImagen = tipo === 'foto' || url.match(/\.(jpg|jpeg|png|gif)$/i);
    const esPDF = url.match(/\.pdf$/i);

    let contenido = '';
    let titulo = 'Visualizar Archivo';

    // Mapeo de títulos
    const titulos = {
        'foto': 'Foto del Estudiante',
        'dni_pdf': 'DNI Escaneado',
        'certificado_estudios': 'Certificado de Estudios',
        'voucher_pago': 'Voucher de Pago',
        'carta_compromiso': 'Carta de Compromiso',
        'constancia_estudios': 'Constancia de Estudios'
    };

    titulo = titulos[tipo] || titulo;

    if (esImagen) {
        contenido = `<img src="${url}" class="img-fluid" alt="${titulo}" style="max-width: 100%; height: auto;">`;
    } else if (esPDF) {
        contenido = `<iframe src="${url}" style="width: 100%; height: 500px; border: none;"></iframe>`;
    } else {
        contenido = `<p>No se puede previsualizar este tipo de archivo.</p><a href="${url}" target="_blank" class="btn btn-primary">Descargar archivo</a>`;
    }

    // Crear o actualizar modal
    if ($('#modalVisualizarArchivo').length === 0) {
        const modalHTML = `
            <div id="modalVisualizarArchivo" class="modal" style="display: none;">
                <div class="modal-content" style="max-width: 800px; width: 90%;">
                    <span class="close-button" onclick="cerrarModalArchivo()" style="position: absolute; top: 15px; right: 25px; font-size: 24px; color: #6b7280; cursor: pointer;">&times;</span>
                    <h4 id="tituloModalArchivo" style="margin-bottom: 20px;"></h4>
                    <div id="contenedorArchivo"></div>
                </div>
            </div>
        `;
        $('body').append(modalHTML);
    }

    // Actualizar contenido y mostrar
    $('#tituloModalArchivo').text(titulo);
    $('#contenedorArchivo').html(contenido);
    $('#modalVisualizarArchivo').fadeIn();
}

// Función para cerrar modal de archivo
function cerrarModalArchivo() {
    $('#modalVisualizarArchivo').fadeOut();
}

function generarResumen() {
    const fotoInput = $('#foto')[0];
    let fotoUrl = 'https://placehold.co/100x100/e5e7eb/6b7280?text=Sin+Foto';

    if (fotoInput.files && fotoInput.files[0]) {
        fotoUrl = URL.createObjectURL(fotoInput.files[0]);
    } else if ($('input[name="foto_existente"]').val()) {
        fotoUrl = $('input[name="foto_existente"]').val();
    }

    const dni = $('#estudiante_dni').val() || 'No proporcionado';
    const nombre = $('#estudiante_nombre').val() || '';
    const apPaterno = $('#estudiante_apellido_paterno').val() || '';
    const apMaterno = $('#estudiante_apellido_materno').val() || '';
    const nombreCompleto = `${nombre} ${apPaterno} ${apMaterno}`.trim() || 'Estudiante';

    const carrera = $('#carrera_id option:selected').text() || 'No seleccionada';
    const turno = $('#turno_id option:selected').text() || 'No seleccionado';
    // Obtener el nombre del colegio del contenedor visible ya que el input se limpia al seleccionar
    const colegio = $('#nombre-colegio-seleccionado strong').text() || 'No seleccionado';
    const email = $('#estudiante_email').val() || 'No proporcionado';
    const telefono = $('#estudiante_telefono').val() || 'No proporcionado';

    let pagosHtml = '';
    if (pagosSeleccionadosDetalles.length > 0) {
        pagosHtml = `
            <div class="resumen-payments-box">
                <div class="resumen-label mb-2"><i class="fas fa-receipt"></i> Detalle de Pagos</div>
                <div class="d-flex flex-column gap-1">
        `;
        pagosSeleccionadosDetalles.forEach(p => {
            pagosHtml += `
                <div class="d-flex justify-content-between align-items-center bg-white p-2 border rounded-3 small">
                    <div>
                        <div class="fw-bold text-dark">${p.concepto}</div>
                        <div class="text-muted" style="font-size: 0.7rem;">Sec: ${p.secuencia}</div>
                    </div>
                    <div class="fw-bold text-success">S/ ${p.monto.toFixed(2)}</div>
                </div>
            `;
        });
        pagosHtml += `</div></div>`;
    }

    const html = `
        <div class="resumen-card">
            <div class="resumen-header">
                <div class="resumen-photo-container">
                    <img src="${fotoUrl}" alt="Foto Postulante">
                </div>
                <div class="resumen-student-info">
                    <h4>${nombreCompleto}</h4>
                    <p><i class="fas fa-id-card me-1"></i> DNI: ${dni}</p>
                </div>
            </div>
            <div class="resumen-body">
                <div class="resumen-grid">
                    <div class="resumen-item">
                        <span class="resumen-label"><i class="fas fa-graduation-cap"></i> Carrera</span>
                        <span class="resumen-value">${carrera}</span>
                    </div>
                    <div class="resumen-item">
                        <span class="resumen-label"><i class="fas fa-clock"></i> Turno</span>
                        <span class="resumen-value">${turno}</span>
                    </div>
                    <div class="resumen-item">
                        <span class="resumen-label"><i class="fas fa-school"></i> Colegio</span>
                        <span class="resumen-value" style="font-size: 0.85rem;">${colegio}</span>
                    </div>
                    <div class="resumen-item">
                        <span class="resumen-label"><i class="fas fa-envelope"></i> Email</span>
                        <span class="resumen-value" style="font-size: 0.85rem;">${email}</span>
                    </div>
                </div>
                ${pagosHtml}
            </div>
        </div>
    `;

    $('#resumen_final').html(html);
}

// Función helper para resaltar campos autocompletados con un pulso (Verde institucional)
function highlightFields(selector) {

    const $els = $(selector);

    // Remover clase previa si existe para reiniciar la animación
    $els.removeClass('field-highlight');

    // Forzar reflow para reiniciar la animación
    void $els[0]?.offsetWidth;

    $els.addClass('field-highlight');

    // Mantener el resaltado por 3.5 segundos (sincronizado con CSS)
    setTimeout(() => {
        $els.removeClass('field-highlight');
    }, 3500);
}

function submitPostulacion() {
    // Validar checkboxes de confirmación
    if (window.isSubmittingPostulacion) return;

    if (!$('#confirmarDatos').is(':checked') || !$('#aceptoTerminos').is(':checked')) {
        Swal.fire({
            icon: 'warning',
            title: 'Confirmación Requerida',
            text: 'Debe aceptar los términos y declarar que la información es verídica.',
            confirmButtonText: 'Entendido'
        });
        return;
    }

    const formElement = $('#formPostulacionPublica')[0];
    const formData = new FormData(formElement);
    const submitBtn = $('#formPostulacionPublica button[type="submit"]');

    window.isSubmittingPostulacion = true;
    submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Enviando...');

    $.ajax({
        url: '/postulacion/store',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function (data) {
            window.isSubmittingPostulacion = false;
            if (data.success) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Postulación Enviada!',
                        text: data.message || 'Su postulación ha sido enviada con éxito. Esta pasará por un proceso de revisión y validación administrativa.',
                        confirmButtonText: 'Aceptar y Cerrar',
                        confirmButtonColor: 'var(--color-principal)',
                        didOpen: () => {
                            lanzarConfetti();
                        }
                    }).then((result) => {
                        if (typeof closeModal === 'function') {
                            closeModal('postulacionModal');
                        }
                        location.reload();
                    });
                } else {
                    lanzarConfetti();
                    Toast.fire({ icon: 'success', title: data.message || '¡Postulación enviada con éxito!' });
                    setTimeout(function () {
                        location.reload();
                    }, 4000);
                }
            } else {
                Toast.fire({ icon: 'error', title: 'Error: ' + (data.message || 'Error desconocido') });
                submitBtn.prop('disabled', false).html('ENVIAR POSTULACIÓN');
            }
        },
        error: function (xhr) {
            window.isSubmittingPostulacion = false;
            console.error('Error:', xhr);
            let errorMsg = 'Ocurrió un error al enviar la postulación.';
            if (xhr.status === 0) {
                errorMsg = 'Error de conexión o el archivo cambió durante la subida. Por favor, intente de nuevo.';
            } else if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMsg = xhr.responseJSON.message;
            }
            Toast.fire({ icon: 'error', title: errorMsg });
            submitBtn.prop('disabled', false).html('ENVIAR POSTULACIÓN');
        }
    });
}

window.descargarPackInscripcion = async function(event) {
    const btn = event ? (event.currentTarget || event.target) : null;
    if (btn && btn.disabled) return; // Prevenir doble clic

    const form = $('#formPostulacionPublica')[0];
    const formData = new FormData();
    
    // IMPORTANTE: NO enviar archivos en la generación del pack PDF para evitar ERR_UPLOAD_FILE_CHANGED
    // y ahorrar ancho de banda. El PDF solo necesita los datos de texto.
    Array.from(form.elements).forEach(element => {
        if (element.name && element.type !== 'file' && element.type !== 'submit') {
            formData.append(element.name, element.value);
        }
    });
    
    const oldHtml = btn ? btn.innerHTML : '';
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> GENERANDO...';
    }

    // Feedback visual
    Swal.fire({
        title: 'Generando Pack...',
        text: 'Estamos preparando tu expediente pre-relleno con tus datos actuales.',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    try {
        const baseUrl = getBaseUrl();
        const response = await fetch(`${baseUrl}/postulacion/download-pack`, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Accept': 'application/pdf'
            }
        });

        if (!response.ok) throw new Error('Error en la generación del PDF. Verifique los datos ingresados.');

        const blob = await response.blob();
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        const dni = $('#dni').val() || 'documento';
        
        a.style.display = 'none';
        a.href = url;
        a.download = `Pack_Inscripcion_CEPRE_${dni}.pdf`;
        document.body.appendChild(a);
        a.click();
        
        // Pequeño delay antes de limpiar para asegurar que el navegador inicie la descarga
        setTimeout(() => {
            if (document.body.contains(a)) document.body.removeChild(a);
            window.URL.revokeObjectURL(url);
        }, 500);
        
        Swal.fire({
            icon: 'success',
            title: '¡Documento Listo!',
            text: 'Se ha descargado tu pack de inscripción. Firma, pon tu huella y súbelo en el paso 4 para finalizar.',
            confirmButtonColor: 'var(--color-principal)'
        });

    } catch (error) {
        console.error('Error al descargar pack:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error de Generación',
            text: 'No se pudo generar el pack. Asegúrese de haber ingresado sus datos y los de su apoderado correctamente.'
        });
    } finally {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = oldHtml;
        }
    }
}

// Función para buscar pagos automáticamente usando el DNI del estudiante
function buscarPagosAutomatico() {
    const dni = $('#estudiante_dni').val();

    if (!dni || dni.length !== 8) {
        // No es error crítico, solo informativo, ya que esto se llama al entrar al paso

        return;
    }

    // Llenar el campo con el DNI
    $('#voucher_secuencia').val(dni);

    // Llamar a la función de validación
    validarVoucher();
}

// Función para habilitar búsqueda manual (con otro DNI)
function habilitarBusquedaManual() {
    const input = $('#voucher_secuencia');
    input.prop('readonly', false);
    input.val('');
    input.focus();
    input.attr('placeholder', 'Ingrese DNI o código de voucher');
    Toast.fire({ icon: 'info', title: 'Puede ingresar otro DNI o código de voucher manualmente' });
}

// Actualizar el display del DNI cuando se muestra el paso 5
function actualizarDNIDisplay() {
    const dni = $('#estudiante_dni').val();
    if (dni) {
        $('#dni_display').text(dni);
        // Inicializar el campo de secuencia con el DNI (será sobrescrito en buscarPagosAutomatico)
        $('#voucher_secuencia').val(dni);
    }
}

// Prevenir inicialización de Select2 en el campo de búsqueda de colegios
function preventSelect2Conflicts() {
    // Destruir cualquier instancia de Select2 en el campo de búsqueda
    if ($('#buscar_colegio').data('select2')) {
        $('#buscar_colegio').select2('destroy');
    }

    // Asegurar que el campo sea un input de texto normal
    $('#buscar_colegio').off('select2:select select2:unselect');
}

// Modificar la función showStep para actualizar el DNI cuando se llega al paso 4 (Documentos y Pago) y validar pago
const originalShowStep = showStep;

showStep = function (n) {
    originalShowStep(n);

    // Si llegamos al paso 4 (Documentos y Pago) - ANTES ERA 5, AHORA ES 4
    if (n === 4) {
        actualizarDNIDisplay();
        // CAMBIO CRÍTICO: Llamar a buscarPagosAutomatico() automáticamente.
        // Se añade un pequeño delay para asegurar que el DOM se haya cargado.
        setTimeout(function () {
            buscarPagosAutomatico();
        }, 300);
    }

    // Si llegamos al paso 3 (Académico), prevenir conflictos de Select2
    if (n === 3) {
        preventSelect2Conflicts();
    }
};

// ======================================================================
// HACER FUNCIONES GLOBALES (PARA EVENTOS INLINE EN HTML)
// ======================================================================

// Re-asignar showStep modificado
window.showStep = showStep;
// Asegurar que las demás funciones estén en el scope global para HTML
window.nextPrev = nextPrev;
window.verificarPostulante = verificarPostulante;
window.consultarDNIPadre = consultarDNIPadre;
window.validarVoucher = validarVoucher;
window.actualizarPagosSeleccionados = actualizarPagosSeleccionados;
window.generarResumen = generarResumen;
window.submitPostulacion = submitPostulacion;
window.buscarPagosAutomatico = buscarPagosAutomatico;
window.habilitarBusquedaManual = habilitarBusquedaManual;
// Nuevas funciones para pre-carga de datos
window.precargarDatosPadres = precargarDatosPadres;
window.precargarDatosAcademicos = precargarDatosAcademicos;
window.mostrarArchivosExistentes = mostrarArchivosExistentes;
window.verArchivoModal = verArchivoModal;
window.cerrarModalArchivo = cerrarModalArchivo;
// window.descargarPackInscripcion = descargarPackInscripcion; // Ya asignada arriba asíncronamente

// ======================================================================
// FUNCIONES PARA INGRESO MANUAL DE VOUCHER
// ======================================================================

// Mostrar formulario de ingreso manual
function mostrarIngresoManual() {
    document.getElementById('manual_voucher_section').style.display = 'block';
    document.getElementById('voucher_details').style.display = 'none';

    // Calcular total automáticamente cuando cambian los montos
    const matriculaInput = document.getElementById('manual_monto_matricula');
    const ensenanzaInput = document.getElementById('manual_monto_ensenanza');

    if (matriculaInput && ensenanzaInput) {
        matriculaInput.addEventListener('input', calcularTotalManual);
        ensenanzaInput.addEventListener('input', calcularTotalManual);
    }
}

// Calcular total manual
function calcularTotalManual() {
    const matricula = parseFloat(document.getElementById('manual_monto_matricula').value) || 0;
    const ensenanza = parseFloat(document.getElementById('manual_monto_ensenanza').value) || 0;
    const total = matricula + ensenanza;
    document.getElementById('manual_total_display').textContent = total.toFixed(2);
}

// Confirmar voucher manual
function confirmarVoucherManual() {
    const numero = document.getElementById('manual_voucher_numero').value.trim();
    const fecha = document.getElementById('manual_voucher_fecha').value;
    const matricula = parseFloat(document.getElementById('manual_monto_matricula').value) || 0;
    const ensenanza = parseFloat(document.getElementById('manual_monto_ensenanza').value) || 0;

    // Validar campos
    if (!numero) {
        Toast.fire({ icon: 'error', title: 'Por favor ingrese el número de voucher' });
        return;
    }
    if (!fecha) {
        Toast.fire({ icon: 'error', title: 'Por favor ingrese la fecha de emisión' });
        return;
    }
    if (matricula <= 0 && ensenanza <= 0) {
        Toast.fire({ icon: 'error', title: 'Por favor ingrese al menos un monto válido' });
        return;
    }

    // Llenar campos ocultos
    document.getElementById('voucher_secuencia').value = numero;
    document.getElementById('fecha_emision_voucher').value = fecha;
    document.getElementById('monto_matricula').value = matricula.toFixed(2);
    document.getElementById('monto_ensenanza').value = ensenanza.toFixed(2);
    document.getElementById('monto_total_pagado').value = (matricula + ensenanza).toFixed(2);

    // Mostrar confirmación
    const total = matricula + ensenanza;
    const feedbackDiv = document.getElementById('pago_feedback');
    if (feedbackDiv) {
        feedbackDiv.innerHTML = `
            <div class="alert alert-success py-2 small">
                <i class="fas fa-check-circle me-1"></i> 
                <strong>Voucher Ingresado Manualmente:</strong> ${numero} - Total: S/. ${total.toFixed(2)}
            </div>
        `;
    }

    // Ocultar formulario manual
    document.getElementById('manual_voucher_section').style.display = 'none';

    Toast.fire({ icon: 'success', title: 'Datos del voucher confirmados correctamente' });
}

// Cancelar ingreso manual
function cancelarVoucherManual() {
    // Limpiar campos
    document.getElementById('manual_voucher_numero').value = '';
    document.getElementById('manual_voucher_fecha').value = '';
    document.getElementById('manual_monto_matricula').value = '';
    document.getElementById('manual_monto_ensenanza').value = '';
    document.getElementById('manual_total_display').textContent = '0.00';

    // Ocultar formulario
    document.getElementById('manual_voucher_section').style.display = 'none';
}

// Registrar funciones como globales
window.mostrarIngresoManual = mostrarIngresoManual;
window.calcularTotalManual = calcularTotalManual;
window.confirmarVoucherManual = confirmarVoucherManual;
window.cancelarVoucherManual = cancelarVoucherManual;

// ======================================================================
// FUNCIONES PARA SWITCHES DE PADRE Y MADRE
// ======================================================================

// Toggle campos del padre
function togglePadreFields() {
    const tienePadre = document.getElementById('tiene_padre').checked;
    const tieneMadre = document.getElementById('tiene_madre').checked;
    const container = document.getElementById('padre_fields_container');
    const inputs = container.querySelectorAll('input');

    // Validar que al menos uno esté activo
    if (!tienePadre && !tieneMadre) {
        Toast.fire({ icon: 'warning', title: 'Debe registrar al menos la información de uno de los padres' });
        document.getElementById('tiene_padre').checked = true;
        return;
    }

    if (tienePadre) {
        // Mostrar campos
        container.style.display = 'block';
    } else {
        // Ocultar campos y limpiar valores
        container.style.display = 'none';
        inputs.forEach(input => {
            input.value = '';
            input.removeAttribute('required');
        });
    }
}

// Toggle campos de la madre
function toggleMadreFields() {
    const tienePadre = document.getElementById('tiene_padre').checked;
    const tieneMadre = document.getElementById('tiene_madre').checked;
    const container = document.getElementById('madre_fields_container');
    const inputs = container.querySelectorAll('input');

    // Validar que al menos uno esté activo
    if (!tienePadre && !tieneMadre) {
        Toast.fire({ icon: 'warning', title: 'Debe registrar al menos la información de uno de los padres' });
        document.getElementById('tiene_madre').checked = true;
        return;
    }

    if (tieneMadre) {
        // Mostrar campos
        container.style.display = 'block';
    } else {
        // Ocultar campos y limpiar valores
        container.style.display = 'none';
        inputs.forEach(input => {
            input.value = '';
            input.removeAttribute('required');
        });
    }
}

// Validar que al menos un padre esté registrado
function validarPadres() {
    const tienePadre = document.getElementById('tiene_padre').checked;
    const tieneMadre = document.getElementById('tiene_madre').checked;

    if (!tienePadre && !tieneMadre) {
        Toast.fire({ icon: 'error', title: 'Debe registrar al menos la información de uno de los padres (padre o madre)' });
        return false;
    }

    // Si tiene padre activo, validar TODOS los campos obligatorios
    if (tienePadre) {
        const padreDNI = document.getElementById('padre_dni').value.trim();
        const padreNombre = document.getElementById('padre_nombre').value.trim();
        const padreApellidos = document.getElementById('padre_apellidos').value.trim();
        const padreTelefono = document.getElementById('padre_telefono').value.trim();
        const padreOcupacion = document.getElementById('padre_ocupacion').value.trim();
        const padreEmail = document.getElementById('padre_email').value.trim();

        if (!padreDNI) {
            Toast.fire({ icon: 'error', title: 'Por favor ingrese el DNI del padre' });
            document.getElementById('padre_dni').focus();
            return false;
        }
        if (padreDNI.length !== 8) {
            Toast.fire({ icon: 'error', title: 'El DNI del padre debe tener 8 dígitos' });
            document.getElementById('padre_dni').focus();
            return false;
        }
        if (!padreNombre) {
            Toast.fire({ icon: 'error', title: 'Por favor ingrese el nombre del padre' });
            document.getElementById('padre_nombre').focus();
            return false;
        }
        if (!padreApellidos) {
            Toast.fire({ icon: 'error', title: 'Por favor ingrese los apellidos del padre' });
            document.getElementById('padre_apellidos').focus();
            return false;
        }
        if (!padreTelefono) {
            Toast.fire({ icon: 'error', title: 'Por favor ingrese el teléfono del padre' });
            document.getElementById('padre_telefono').focus();
            return false;
        }
        if (padreTelefono.length !== 9) {
            Toast.fire({ icon: 'error', title: 'El teléfono del padre debe tener 9 dígitos' });
            document.getElementById('padre_telefono').focus();
            return false;
        }
        if (!padreOcupacion) {
            Toast.fire({ icon: 'error', title: 'Por favor ingrese la ocupación del padre' });
            document.getElementById('padre_ocupacion').focus();
            return false;
        }
        if (!padreEmail) {
            Toast.fire({ icon: 'error', title: 'Por favor ingrese el email del padre' });
            document.getElementById('padre_email').focus();
            return false;
        }
        // Validar formato de email
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(padreEmail)) {
            Toast.fire({ icon: 'error', title: 'Por favor ingrese un email válido para el padre' });
            document.getElementById('padre_email').focus();
            return false;
        }
    }

    // Si tiene madre activa, validar TODOS los campos obligatorios
    if (tieneMadre) {
        const madreDNI = document.getElementById('madre_dni').value.trim();
        const madreNombre = document.getElementById('madre_nombre').value.trim();
        const madreApellidos = document.getElementById('madre_apellidos').value.trim();
        const madreTelefono = document.getElementById('madre_telefono').value.trim();
        const madreOcupacion = document.getElementById('madre_ocupacion').value.trim();
        const madreEmail = document.getElementById('madre_email').value.trim();

        if (!madreDNI) {
            Toast.fire({ icon: 'error', title: 'Por favor ingrese el DNI de la madre' });
            document.getElementById('madre_dni').focus();
            return false;
        }
        if (madreDNI.length !== 8) {
            Toast.fire({ icon: 'error', title: 'El DNI de la madre debe tener 8 dígitos' });
            document.getElementById('madre_dni').focus();
            return false;
        }
        if (!madreNombre) {
            Toast.fire({ icon: 'error', title: 'Por favor ingrese el nombre de la madre' });
            document.getElementById('madre_nombre').focus();
            return false;
        }
        if (!madreApellidos) {
            Toast.fire({ icon: 'error', title: 'Por favor ingrese los apellidos de la madre' });
            document.getElementById('madre_apellidos').focus();
            return false;
        }
        if (!madreTelefono) {
            Toast.fire({ icon: 'error', title: 'Por favor ingrese el teléfono de la madre' });
            document.getElementById('madre_telefono').focus();
            return false;
        }
        if (madreTelefono.length !== 9) {
            Toast.fire({ icon: 'error', title: 'El teléfono de la madre debe tener 9 dígitos' });
            document.getElementById('madre_telefono').focus();
            return false;
        }
        if (!madreOcupacion) {
            Toast.fire({ icon: 'error', title: 'Por favor ingrese la ocupación de la madre' });
            document.getElementById('madre_ocupacion').focus();
            return false;
        }
        if (!madreEmail) {
            Toast.fire({ icon: 'error', title: 'Por favor ingrese el email de la madre' });
            document.getElementById('madre_email').focus();
            return false;
        }
        // Validar formato de email
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(madreEmail)) {
            Toast.fire({ icon: 'error', title: 'Por favor ingrese un email válido para la madre' });
            document.getElementById('madre_email').focus();
            return false;
        }
    }

    return true;
}

// Registrar funciones como globales
window.togglePadreFields = togglePadreFields;
window.toggleMadreFields = toggleMadreFields;
window.validarPadres = validarPadres;
window.consultarEstadoPostulacion = consultarEstadoPostulacion;
window.consultarEstadoDirecto = consultarEstadoDirecto;

// ======================================================================
// VALIDACIÓN DE ARCHIVOS (TAMAÑO Y TIPO)
// ======================================================================

$(document).on('change', 'input[type="file"]', function () {
    const file = this.files[0];
    if (!file) return;

    const maxSize = 5 * 1024 * 1024; // 5MB
    const allowedExtensions = $(this).attr('accept') ? $(this).attr('accept').split(',').map(ext => ext.trim()) : [];

    // Validar tamaño
    if (file.size > maxSize) {
        Swal.fire({
            icon: 'error',
            title: 'Archivo demasiado pesado',
            text: `El archivo "${file.name}" supera el límite de 5MB. Por favor, suba un archivo más pequeño.`,
            confirmButtonText: 'Aceptar'
        });
        $(this).val(''); // Limpiar el input
        return;
    }

    // Validar extensión (si el input tiene el atributo accept)
    if (allowedExtensions.length > 0) {
        const fileName = file.name.toLowerCase();
        let isValid = false;

        for (const ext of allowedExtensions) {
            if (ext.startsWith('image/')) {
                if (file.type.startsWith('image/')) isValid = true;
            } else if (ext === 'application/pdf') {
                if (file.type === 'application/pdf') isValid = true;
            } else if (fileName.endsWith(ext.replace('*', ''))) {
                isValid = true;
            }
        }

        if (!isValid) {
            Swal.fire({
                icon: 'error',
                title: 'Formato no permitido',
                text: `El formato del archivo "${file.name}" no es válido. Formatos permitidos: ${$(this).attr('accept')}`,
                confirmButtonText: 'Aceptar'
            });
            $(this).val(''); // Limpiar el input
            return;
        }
    }
});
