// public/js/postulaciones/unificado.js

// Configuración CSRF para AJAX
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

// Variables globales
let currentStep = 1;
const totalSteps = 5;
let departamentos = [];
let provincias = [];
let distritos = [];
let colegioSeleccionado = null;

$(document).ready(function() {
    console.log('Postulación Unificada JS cargado');
    
    // Inicializar componentes
    initializeComponents();
    
    // Configurar eventos
    setupEventHandlers();
    
    // Cargar datos iniciales
    loadInitialData();
});

function initializeComponents() {
    
    
    // Configurar validación en tiempo real
    setupRealTimeValidation();
}

function setupEventHandlers() {
    // Navegación por pasos
    $('#nextBtn').on('click', function() {
        changeStep(1);
    });
    
    $('#prevBtn').on('click', function() {
        changeStep(-1);
    });
    
    // Envío del formulario
    $('#postulacionUnificadaForm').on('submit', function(e) {
        e.preventDefault();
        showConfirmationModal();
    });
    
    // Confirmación final
    $('#confirmSubmit').on('click', function() {
        submitForm();
    });
    
    // Cálculo automático del total
    $('#monto_matricula, #monto_ensenanza').on('input', function() {
        calculateTotal();
    });
    
    // Manejo de documentos
    setupDocumentHandlers();
    
    // Validación de DNI en tiempo real
    $('input[name$="_dni"]').on('input', function() {
        validateDNI($(this));
    });
    
    // Validación de email en tiempo real
    $('input[type="email"]').on('blur', function() {
        validateEmail($(this));
    });
    
    // Navegación directa por pasos (click en wizard)
    $('.step-wizard-list').on('click', function() {
        const targetStep = parseInt($(this).data('step'));
        if (targetStep < currentStep || validateCurrentStep()) {
            goToStep(targetStep);
        }
    });

    // Eventos para selectores de ubicación
    $('#departamento').on('change', function() {
        const departamentoId = $(this).val();
        if (departamentoId) {
            loadProvincias(departamentoId);
            $('#provincia').prop('disabled', false);
            resetSelect('#distrito', 'Seleccione provincia primero');
            // Clear and disable school related fields
            $('#buscar_colegio').prop('disabled', true).val('');
            $('#btnBuscarColegio').prop('disabled', true);
            $('#sugerencias-colegios').empty();
            ocultarColegioSeleccionado();
        } else {
            resetSelect('#provincia', 'Seleccione departamento primero');
            resetSelect('#distrito', 'Seleccione provincia primero');
            $('#buscar_colegio').prop('disabled', true).val('');
            $('#btnBuscarColegio').prop('disabled', true);
            $('#sugerencias-colegios').empty();
            ocultarColegioSeleccionado();
        }
    });

    $('#provincia').on('change', function() {
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
            resetSelect('#distrito', 'Seleccione provincia primero');
            $('#buscar_colegio').prop('disabled', true).val('');
            $('#btnBuscarColegio').prop('disabled', true);
            $('#sugerencias-colegios').empty();
            ocultarColegioSeleccionado();
        }
    });

    $('#distrito').on('change', function() {
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

    // Búsqueda de colegio
    $('#btnBuscarColegio').on('click', buscarColegios);
    // Evento keyup para la búsqueda en tiempo real
    $('#buscar_colegio').on('keyup', function() {
        const searchTerm = $(this).val();
        if (searchTerm.length >= 2 || searchTerm.length === 0) { // Search if 2+ chars or empty (to clear results)
            buscarColegios();
        } else {
            $('#sugerencias-colegios').empty(); // Limpiar sugerencias si no hay suficientes caracteres
        }
    });
    $('#buscar_colegio').on('keypress', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            buscarColegios();
        }
    });
}

function setupDocumentHandlers() {
    // Click en área de upload
    $('.document-upload').on('click', function() {
        $(this).find('input[type="file"]').click();
    });
    
    // Cambio de archivo
    $('.document-upload input[type="file"]').on('change', function() {
        handleFileSelect(this);
    });
    
    // Drag and drop
    $('.document-upload').on('dragover', function(e) {
        e.preventDefault();
        $(this).addClass('dragover');
    });
    
    $('.document-upload').on('dragleave', function(e) {
        e.preventDefault();
        $(this).removeClass('dragover');
    });
    
    $('.document-upload').on('drop', function(e) {
        e.preventDefault();
        $(this).removeClass('dragover');
        
        const files = e.originalEvent.dataTransfer.files;
        if (files.length > 0) {
            const input = $(this).find('input[type="file"]')[0];
            input.files = files;
            handleFileSelect(input);
        }
    });
}

function handleFileSelect(input) {
    const file = input.files[0];
    const target = $(input).closest('.document-upload').data('target');
    const previewId = 'preview-' + target;
    
    if (file) {
        // Validar tamaño
        const maxSize = target === 'foto_carnet' ? 2 * 1024 * 1024 : 5 * 1024 * 1024; // 2MB para foto, 5MB para otros
        if (file.size > maxSize) {
            toastr.error('El archivo es demasiado grande. Tamaño máximo: ' + (maxSize / 1024 / 1024) + 'MB');
            input.value = '';
            return;
        }
        
        // Validar tipo
        const allowedTypes = target === 'foto_carnet' ? 
            ['image/jpeg', 'image/jpg', 'image/png'] : 
            ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'];
        
        if (!allowedTypes.includes(file.type)) {
            toastr.error('Tipo de archivo no permitido');
            input.value = '';
            return;
        }
        
        // Mostrar preview
        showFilePreview(file, previewId);
        
        // Marcar como completado
        $(input).closest('.document-upload').addClass('completed');
    }
}

function showFilePreview(file, previewId) {
    const preview = $('#' + previewId);
    let html = '<div class="d-flex align-items-center">';
    html += '<i class="uil uil-file-check-alt text-success me-2"></i>';
    html += '<span class="me-auto">' + file.name + '</span>';
    html += '<small class="text-muted">(' + formatFileSize(file.size) + ')</small>';
    html += '</div>';
    
    preview.html(html).addClass('show');
}

function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

function changeStep(direction) {
    const newStep = currentStep + direction;
    
    if (direction > 0) {
        // Avanzar: validar paso actual
        if (!validateCurrentStep()) {
            return;
        }
    }
    
    if (newStep >= 1 && newStep <= totalSteps) {
        goToStep(newStep);
    }
}

function goToStep(step) {
    console.log('Cambiando del paso', currentStep, 'al paso', step);
    
    // Ocultar paso actual
    $('#step-' + currentStep).removeClass('active').hide();
    $('.step-wizard-list[data-step="' + currentStep + '"]').removeClass('active');
    
    // Marcar pasos completados
    if (step > currentStep) {
        $('.step-wizard-list[data-step="' + currentStep + '"]').addClass('completed');
    }
    
    // Mostrar nuevo paso
    currentStep = step;
    $('#step-' + currentStep).addClass('active').show();
    $('.step-wizard-list[data-step="' + currentStep + '"]').addClass('active');
    
    // Actualizar botones de navegación
    updateNavigationButtons();
    
    // Scroll al top del modal
    $('.modal-body').animate({ scrollTop: 0 }, 300);
    
    console.log('Paso cambiado a:', currentStep);
}

function updateNavigationButtons() {
    // Botón anterior
    if (currentStep === 1) {
        $('#prevBtn').hide();
    } else {
        $('#prevBtn').show();
    }
    
    // Botón siguiente/enviar
    if (currentStep === totalSteps) {
        $('#nextBtn').hide();
        $('#submitBtn').show();
    } else {
        $('#nextBtn').show();
        $('#submitBtn').hide();
    }
}

function validateCurrentStep() {
    let isValid = true;
    const currentStepElement = $('#step-' + currentStep);
    
    // Limpiar errores previos
    currentStepElement.find('.is-invalid').removeClass('is-invalid');
    currentStepElement.find('.invalid-feedback').remove();
    
    // Validar campos requeridos
    currentStepElement.find('input[required], select[required]').each(function() {
        if (!$(this).val()) {
            showFieldError($(this), 'Este campo es obligatorio');
            isValid = false;
        }
    });
    
    // Validaciones específicas por paso
    switch (currentStep) {
        case 1:
            isValid = validateStep1() && isValid;
            break;
        case 2:
            isValid = validateStep2() && isValid;
            break;
        case 3:
            isValid = validateStep3() && isValid;
            break;
        case 4:
            isValid = validateStep4() && isValid;
            break;
        case 5:
            isValid = validateStep5() && isValid;
            break;
    }
    
    if (!isValid) {
        toastr.error('Por favor complete todos los campos requeridos correctamente');
    }
    
    return isValid;
}

function validateStep1() {
    let isValid = true;
    
    // Validar DNI del estudiante
    const dni = $('#estudiante_dni').val();
    if (dni && !validateDNIFormat(dni)) {
        showFieldError($('#estudiante_dni'), 'DNI debe tener 8 dígitos');
        isValid = false;
    }
    
    // Validar email
    const email = $('#estudiante_email').val();
    if (email && !validateEmailFormat(email)) {
        showFieldError($('#estudiante_email'), 'Email no válido');
        isValid = false;
    }
    
    // Validar fecha de nacimiento
    const fechaNac = $('#estudiante_fecha_nacimiento').val();
    if (fechaNac) {
        const edad = calculateAge(fechaNac);
        if (edad < 15 || edad > 25) {
            showFieldError($('#estudiante_fecha_nacimiento'), 'Edad debe estar entre 15 y 25 años');
            isValid = false;
        }
    }
    
    return isValid;
}

function validateStep2() {
    let isValid = true;
    
    // Validar DNI del padre
    const dni = $('#padre_dni').val();
    if (dni && !validateDNIFormat(dni)) {
        showFieldError($('#padre_dni'), 'DNI debe tener 8 dígitos');
        isValid = false;
    }
    
    // Validar que no sea igual al DNI del estudiante
    if (dni === $('#estudiante_dni').val()) {
        showFieldError($('#padre_dni'), 'DNI del padre no puede ser igual al del estudiante');
        isValid = false;
    }
    
    return isValid;
}

function validateStep3() {
    let isValid = true;
    
    // Validar DNI de la madre
    const dni = $('#madre_dni').val();
    if (dni && !validateDNIFormat(dni)) {
        showFieldError($('#madre_dni'), 'DNI debe tener 8 dígitos');
        isValid = false;
    }
    
    // Validar que no sea igual al DNI del estudiante o padre
    if (dni === $('#estudiante_dni').val()) {
        showFieldError($('#madre_dni'), 'DNI de la madre no puede ser igual al del estudiante');
        isValid = false;
    }
    
    if (dni === $('#padre_dni').val()) {
        showFieldError($('#madre_dni'), 'DNI de la madre no puede ser igual al del padre');
        isValid = false;
    }
    
    return isValid;
}

function validateStep4() {
    let isValid = true;
    
    // Validar montos
    const matricula = parseFloat($('#monto_matricula').val()) || 0;
    const ensenanza = parseFloat($('#monto_ensenanza').val()) || 0;
    
    if (matricula <= 0) {
        showFieldError($('#monto_matricula'), 'Monto debe ser mayor a 0');
        isValid = false;
    }
    
    if (ensenanza <= 0) {
        showFieldError($('#monto_ensenanza'), 'Monto debe ser mayor a 0');
        isValid = false;
    }
    
    return isValid;
}

function validateStep5() {
    let isValid = true;
    
    // Validar que todos los documentos estén subidos
    const requiredDocs = ['dni_documento', 'certificado_estudios', 'foto_carnet', 'voucher_pago', 'carta_compromiso', 'constancia_estudios'];
    
    requiredDocs.forEach(function(docName) {
        const input = $('input[name="' + docName + '"]');
        if (!input[0].files || input[0].files.length === 0) {
            const uploadArea = input.closest('.document-upload');
            uploadArea.addClass('border-danger');
            isValid = false;
        }
    });
    
    if (!isValid) {
        toastr.error('Debe subir todos los documentos requeridos');
    }
    
    return isValid;
}

function showFieldError(field, message) {
    field.addClass('is-invalid');
    field.after('<div class="invalid-feedback">' + message + '</div>');
}

function validateDNI(input) {
    const dni = input.val();
    if (dni && !validateDNIFormat(dni)) {
        showFieldError(input, 'DNI debe tener 8 dígitos');
    } else {
        input.removeClass('is-invalid');
        input.next('.invalid-feedback').remove();
    }
}

function validateEmail(input) {
    const email = input.val();
    if (email && !validateEmailFormat(email)) {
        showFieldError(input, 'Email no válido');
    } else {
        input.removeClass('is-invalid');
        input.next('.invalid-feedback').remove();
    }
}

function validateDNIFormat(dni) {
    return /^\d{8}$/.test(dni);
}

function validateEmailFormat(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

function calculateAge(birthDate) {
    const today = new Date();
    const birth = new Date(birthDate);
    let age = today.getFullYear() - birth.getFullYear();
    const monthDiff = today.getMonth() - birth.getMonth();
    
    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birth.getDate())) {
        age--;
    }
    
    return age;
}

function calculateTotal() {
    const matricula = parseFloat($('#monto_matricula').val()) || 0;
    const ensenanza = parseFloat($('#monto_ensenanza').val()) || 0;
    const total = matricula + ensenanza;
    
    $('#monto_total').val('S/. ' + total.toFixed(2));
}

function setupRealTimeValidation() {
    // Validación de DNI mientras se escribe
    $('input[name$="_dni"]').on('input', function() {
        const value = $(this).val();
        // Solo permitir números
        $(this).val(value.replace(/[^0-9]/g, ''));
    });
    
    // Validación de teléfono
    $('input[type="tel"]').on('input', function() {
        const value = $(this).val();
        // Solo permitir números, espacios y guiones
        $(this).val(value.replace(/[^0-9\s\-]/g, ''));
    });
}

function loadInitialData() {
    loadDepartamentos();
    resetSelect('#provincia', 'Seleccione departamento primero');
    resetSelect('#distrito', 'Seleccione provincia primero');
}

function loadDepartamentos() {
    const select = $('#departamento');
    select.prop('disabled', true).html('<option value="">Cargando...</option>');

    $.ajax({
        url: '/api/postulacion-unificada/departamentos',
        type: 'GET',
        success: function(response) {
            if (response.success) {
                let options = '<option value="">-- Seleccione --</option>';
                response.departamentos.forEach(function(item) {
                    options += `<option value="${item}">${item}</option>`;
                });
                select.html(options).prop('disabled', false);
            } else {
                select.html('<option value="">Error al cargar</option>');
            }
        },
        error: function() {
            select.html('<option value="">Error al cargar</option>');
        }
    });
}

function loadProvincias(departamentoId) {
    const select = $('#provincia');
    select.prop('disabled', true).html('<option value="">Cargando...</option>');
    resetSelect('#distrito', 'Seleccione provincia primero');

    $.ajax({
        url: `/api/postulacion-unificada/provincias/${departamentoId}`,
        type: 'GET',
        success: function(response) {
            if (response.success) {
                let options = '<option value="">-- Seleccione --</option>';
                response.provincias.forEach(function(item) {
                    options += `<option value="${item.id}">${item.name}</option>`;
                });
                select.html(options).prop('disabled', false);
            } else {
                select.html('<option value="">Error al cargar</option>');
            }
        },
        error: function() {
            select.html('<option value="">Error al cargar</option>');
        }
    });
}

function loadDistritos(departamentoId, provinciaId) {
    const select = $('#distrito');
    select.prop('disabled', true).html('<option value="">Cargando...</option>');

    $.ajax({
        url: `/api/postulacion-unificada/distritos/${departamentoId}/${provinciaId}`,
        type: 'GET',
        success: function(response) {
            if (response.success) {
                let options = '<option value="">-- Seleccione --</option>';
                response.distritos.forEach(function(item) {
                    options += `<option value="${item.id}">${item.name}</option>`;
                });
                select.html(options).prop('disabled', false);
            } else {
                select.html('<option value="">Error al cargar</option>');
            }
        },
        error: function() {
            select.html('<option value="">Error al cargar</option>');
        }
    });
}

function resetSelect(selector, placeholder) {
    const select = $(selector);
    select.prop('disabled', true).html(`<option value="">${placeholder}</option>`);
}

function buscarColegios() {
    const termino = $('#buscar_colegio').val();
    // Solo buscar si el término tiene al menos 2 caracteres o si el campo está vacío al seleccionar un distrito
    if (termino.length < 2 && termino.length !== 0) {
        $('#sugerencias-colegios').empty(); // Limpiar sugerencias si no hay suficientes caracteres
        return;
    }

    $.ajax({
        url: '/api/postulacion-unificada/buscar-colegios', // Using the unificado.js endpoint
        type: 'POST',
        data: {
            departamento: $('#departamento').val(),
            provincia: $('#provincia').val(),
            distrito: $('#distrito').val(),
            termino: termino,
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                mostrarSugerenciasColegios(response.colegios);
            }
        }
    });
}

function mostrarSugerenciasColegios(colegios) {
    let html = '';
    if (colegios.length === 0) {
        html = '<div class="list-group-item">No se encontraron colegios</div>';
    } else {
        colegios.forEach(colegio => {
            html += `
                <a href="#" class="list-group-item list-group-item-action seleccionar-colegio"
                   data-id="${colegio.id}" data-nombre="${colegio.nombre}">
                    <strong>${colegio.nombre}</strong>
                    ${colegio.nivel ? `<br><small>Nivel: ${colegio.nivel}</small>` : ''}
                    ${colegio.direccion ? `<br><small>${colegio.direccion}</small>` : ''}
                </a>
            `;
        });
    }
    $('#sugerencias-colegios').html(html);

    // Evento para seleccionar colegio
    $('.seleccionar-colegio').on('click', function(e) {
        e.preventDefault();
        colegioSeleccionado = {
            id: $(this).data('id'),
            nombre: $(this).data('nombre')
        };
        mostrarColegioSeleccionado();
        $('#sugerencias-colegios').empty();
    });
}

function mostrarColegioSeleccionado() {
    $('#nombre-colegio-seleccionado').text(colegioSeleccionado.nombre);
    $('#colegio-seleccionado').show();
    $('#buscar_colegio').val(colegioSeleccionado.nombre);
}

function ocultarColegioSeleccionado() {
    colegioSeleccionado = null;
    $('#colegio-seleccionado').hide();
}

function showConfirmationModal() {
    // Generar resumen
    const resumen = generateSummary();
    $('#resumenPostulacion').html(resumen);
    $('#confirmModal').modal('show');
}

function generateSummary() {
    let html = '<div class="row">';
    
    // Datos del estudiante
    html += '<div class="col-md-6">';
    html += '<h6>Estudiante:</h6>';
    html += '<p><strong>' + $('#estudiante_nombre').val() + ' ' + $('#estudiante_apellido_paterno').val() + ' ' + $('#estudiante_apellido_materno').val() + '</strong></p>';
    html += '<p>DNI: ' + $('#estudiante_dni').val() + '</p>';
    html += '</div>';
    
    // Datos académicos
    html += '<div class="col-md-6">';
    html += '<h6>Datos Académicos:</h6>';
    html += '<p>Carrera: ' + $('#carrera_id option:selected').text() + '</p>';
    html += '<p>Turno: ' + $('#turno_id option:selected').text() + '</p>';
    html += '<p>Tipo: ' + $('#tipo_inscripcion option:selected').text() + '</p>';
    html += '</div>';
    
    html += '</div>';
    
    return html;
}

function submitForm() {
    // Mostrar loading
    $('#loadingOverlay').show();
    $('#confirmModal').modal('hide');
    
    // Preparar FormData
    const formData = new FormData($('#postulacionUnificadaForm')[0]);
    
    $.ajax({
        url: default_server + '/postulacion-unificada',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            $('#loadingOverlay').hide();
            
            if (response.success) {
                toastr.success(response.message);
                
                // Mostrar información de la postulación
                if (response.postulacion) {
                    setTimeout(function() {
                        toastr.info('Código de postulación: ' + response.postulacion.codigo_postulante, 'Información', {
                            timeOut: 8000
                        });
                    }, 1000);
                }
                
                // Enviar mensaje al padre (modal) para cerrar
                if (window.parent !== window) {
                    window.parent.postMessage({
                        type: 'postulacion-completada',
                        message: 'Postulación creada exitosamente',
                        data: response.postulacion
                    }, '*');
                } else {
                    // Si no está en iframe, redirigir normalmente
                    if (response.redirect) {
                        setTimeout(function() {
                            window.location.href = response.redirect;
                        }, 2000);
                    }
                }
            } else {
                toastr.error(response.message || 'Error al enviar la postulación');
            }
        },
        error: function(xhr) {
            $('#loadingOverlay').hide();
            
            if (xhr.status === 422) {
                // Errores de validación
                const errors = xhr.responseJSON.errors;
                for (let field in errors) {
                    toastr.error(errors[field][0]);
                }
            } else {
                const response = xhr.responseJSON;
                toastr.error(response?.message || 'Error al enviar la postulación');
            }
        }
    });
}

// Función global para consultar DNI
window.consultarDNI = function(tipo) {
    console.log('Función consultarDNI llamada para tipo:', tipo);
    
    const dniField = $('#' + tipo + '_dni');
    if (dniField.length === 0) {
        console.error('No se encontró el campo DNI para tipo:', tipo);
        toastr.error('Error: No se encontró el campo DNI');
        return;
    }
    
    const dni = dniField.val().trim();
    console.log('DNI a consultar:', dni);
    
    if (!/^\d{8}$/.test(dni)) {
        const mensaje = 'Ingrese un DNI válido de 8 dígitos';
        console.log('DNI inválido:', dni);
        toastr.error(mensaje);
        return;
    }
    
    // Deshabilitar el botón mientras consulta
    const button = dniField.parent('.input-group').find('button');
    if (button.length === 0) {
        console.error('No se encontró el botón RENIEC');
        toastr.error('Error: No se encontró el botón RENIEC');
        return;
    }
    
    const originalText = button.html();
    button.prop('disabled', true);
    button.html('<i class="mdi mdi-loading mdi-spin"></i> Consultando...');
    
    console.log('Iniciando consulta RENIEC...');
    toastr.info('Consultando RENIEC...', 'Procesando');
    
    // Verificar que tenemos el servidor configurado
    if (!window.default_server) {
        console.error('window.default_server no está definido');
        toastr.error('Error de configuración del servidor');
        button.prop('disabled', false);
        button.html(originalText);
        return;
    }
    
    const url = window.default_server + '/api/reniec/consultar';
    console.log('URL de consulta:', url);
    
    // Consultar la API real de RENIEC
    $.ajax({
        url: url,
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            'Accept': 'application/json'
        },
        data: JSON.stringify({ dni: dni }),
        success: function(data) {
            console.log('Datos recibidos de RENIEC:', data);
            
            if (data.success && data.data) {
                const datos = data.data;
                console.log('Datos a llenar:', datos);

                const fieldMapping = {
                    'nombres': `#${tipo}_nombre`,
                    'apellido_paterno': `#${tipo}_apellido_paterno`,
                    'apellido_materno': `#${tipo}_apellido_materno`,
                    'fecha_nacimiento': `#${tipo}_fecha_nacimiento`,
                    'genero': `#${tipo}_genero`,
                    'direccion': `#${tipo}_direccion`
                };

                let filledFields = [];
                for (const key in fieldMapping) {
                    if (datos[key] && $(fieldMapping[key]).length) {
                        $(fieldMapping[key]).val(datos[key]);
                        console.log(`Llenado ${key} para ${tipo}:`, datos[key]);
                        filledFields.push(fieldMapping[key]);
                    }
                }

                // Asignar tipo de documento
                const tipoDocField = $(`#${tipo}_tipo_documento`);
                if (tipoDocField.length) {
                    tipoDocField.val('DNI');
                    filledFields.push(`#${tipo}_tipo_documento`);
                }

                // Agregar clase de autocompletado para efecto visual
                filledFields.forEach(fieldId => {
                    const field = $(fieldId);
                    if (field.length && field.val()) {
                        field.addClass('auto-filled');
                        setTimeout(() => field.removeClass('auto-filled'), 2000);
                    }
                });
                
                const mensajeExito = 'Datos encontrados y completados automáticamente';
                toastr.success(mensajeExito, 'RENIEC');
            } else {
                const mensajeError = data.message || 'No se encontraron datos para este DNI';
                console.log('No se encontraron datos:', mensajeError);
                toastr.warning(mensajeError, 'RENIEC');
            }
        },
        error: function(xhr, status, error) {
            console.error('Error consultando RENIEC:', error);
            const mensajeError = 'Error al consultar RENIEC: ' + error;
            toastr.error(mensajeError, 'Error');
        },
        complete: function() {
            // Rehabilitar el botón
            console.log('Rehabilitando botón...');
            button.prop('disabled', false);
            button.html(originalText);
        }
    });
};

// Funciones globales para navegación
window.changeStep = changeStep;
window.goToStep = goToStep;
