let currentStep = 1;
const totalSteps = 6;

$(document).ready(function () {
    console.log('Publico Modal JS Initialized');

    // Inicializar wizard
    showStep(currentStep);

    // Cargar departamentos
    loadDepartamentos();

    // Event listeners para validación en tiempo real
    $('input[type="tel"]').on('input', function () {
        this.value = this.value.replace(/[^0-9]/g, '');
    });

    // Manejo del formulario
    $('#formPostulacionPublica').on('submit', function (e) {
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
    $(document).on('click', '#btn-verificar-dni', function () {
        console.log('Click en verificar DNI detectado via jQuery');
        verificarPostulante(this);
    });

    // Búsqueda de colegio
    $('#btnBuscarColegio').on('click', buscarColegios);
    // Evento keyup para la búsqueda en tiempo real
    $('#buscar_colegio').on('keyup', function () {
        const searchTerm = $(this).val();
        if (searchTerm.length >= 2 || searchTerm.length === 0) { // Search if 2+ chars or empty (to clear results)
            buscarColegios();
        } else {
            $('#sugerencias-colegios').empty(); // Limpiar sugerencias si no hay suficientes caracteres
        }
    });
    $('#buscar_colegio').on('keypress', function (e) {
        if (e.which === 13) {
            e.preventDefault();
            buscarColegios();
        }
    });
});

function buscarColegios() {
    const termino = $('#buscar_colegio').val();
    // Solo buscar si el término tiene al menos 2 caracteres o si el campo está vacío al seleccionar un distrito
    if (termino.length < 2 && termino.length !== 0) {
        $('#sugerencias-colegios').empty(); // Limpiar sugerencias si no hay suficientes caracteres
        return;
    }

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
                mostrarSugerenciasColegios(response.colegios);
            }
        },
        error: function (xhr) {
            console.error('Error buscando colegios:', xhr);
            toastr.error('Error al buscar colegios');
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
    $('.seleccionar-colegio').on('click', function (e) {
        e.preventDefault();
        const colegioSeleccionado = {
            id: $(this).data('id'),
            nombre: $(this).data('nombre')
        };
        mostrarColegioSeleccionado(colegioSeleccionado);
        $('#sugerencias-colegios').empty();
    });
}

function mostrarColegioSeleccionado(colegio) {
    $('#nombre-colegio-seleccionado').text(colegio.nombre);
    $('#colegio-seleccionado').show();
    $('#buscar_colegio').val(colegio.nombre);
    $('#centro_educativo_id').val(colegio.id);
}

function ocultarColegioSeleccionado() {
    $('#colegio-seleccionado').hide();
    $('#centro_educativo_id').val('');
}

function loadDepartamentos() {
    console.log('Iniciando carga de departamentos...');
    const select = $('#departamento');

    if (select.length === 0) {
        console.error('ERROR CRÍTICO: No se encontró el elemento #departamento');
        toastr.error('Error interno: No se encontró el selector de departamentos');
        return;
    }

    select.html('<option value="">Cargando...</option>');

    $.get('/api/public/departamentos', function (data) {
        console.log('Respuesta API Departamentos:', data);

        if (data.success) {
            let html = '<option value="">Seleccione</option>';

            // Verificar si es array u objeto
            const lista = data.departamentos;
            if (Array.isArray(lista)) {
                lista.forEach(function (item) {
                    const id = (typeof item === 'object') ? item.id : item;
                    const nombre = (typeof item === 'object') ? item.nombre : item;
                    html += `<option value="${id}">${nombre}</option>`;
                });
            } else if (typeof lista === 'object') {
                $.each(lista, function (i, item) {
                    const id = (typeof item === 'object') ? item.id : item;
                    const nombre = (typeof item === 'object') ? item.nombre : item;
                    html += `<option value="${id}">${nombre}</option>`;
                });
            }

            select.html(html);
            select.show(); // Asegurar que sea visible

            console.log('Departamentos renderizados (Nativo)');
        } else {
            console.error('API reportó error:', data.message);
            select.html('<option value="">Error al cargar</option>');
            // Eliminando defensivamente cualquier duplicado de UI enhancer
            if (select.next().hasClass('nice-select')) {
                select.niceSelect('destroy');
            }
            select.show();
            toastr.error('Error al cargar departamentos: ' + (data.message || 'Desconocido'));
        }
    }).fail(function (jqXHR, textStatus, errorThrown) {
        console.error('Error AJAX Departamentos:', textStatus, errorThrown);
        console.error('Respuesta:', jqXHR.responseText);
        select.html('<option value="">Error de conexión</option>');
        if (select.next().hasClass('nice-select')) {
            select.niceSelect('destroy');
        }
        select.show();
        toastr.error('Error de conexión al cargar departamentos');

        // Debugging extra solicitado por el usuario
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Error de Conexión',
                text: 'No se pudieron cargar los departamentos. Por favor revise su conexión o contacte al administrador.'
            });
        }
    });
}

function loadProvincias(dep) {
    const select = $('#provincia');
    const distSelect = $('#distrito');

    // Limpieza defensiva de cualquier UI enhancer
    if (select.next().hasClass('nice-select')) { select.niceSelect('destroy'); }
    if (distSelect.next().hasClass('nice-select')) { distSelect.niceSelect('destroy'); }

    select.html('<option value="">Cargando...</option>').prop('disabled', true);
    distSelect.html('<option value="">Seleccione</option>').prop('disabled', true);

    select.show();
    distSelect.show();

    if (!dep) {
        select.html('<option value="">Seleccione</option>');
        return;
    }

    $.get('/api/public/provincias/' + dep, function (data) {
        if (data.success) {
            let html = '<option value="">Seleccione</option>';
            data.provincias.forEach(function (item) {
                const id = (typeof item === 'object') ? item.id : item;
                const nombre = (typeof item === 'object') ? item.nombre : item;
                html += `<option value="${id}">${nombre}</option>`;
            });
            select.html(html).prop('disabled', false);
        }
    }).fail(function () {
        toastr.error('Error al cargar provincias');
    });
}

function loadDistritos(dep, prov) {
    const select = $('#distrito');

    // Limpieza defensiva de cualquier UI enhancer
    if (select.next().hasClass('nice-select')) { select.niceSelect('destroy'); }

    select.html('<option value="">Cargando...</option>').prop('disabled', true);

    select.show();

    if (!dep || !prov) {
        select.html('<option value="">Seleccione</option>');
        return;
    }

    $.get('/api/public/distritos/' + dep + '/' + prov, function (data) {
        if (data.success) {
            let html = '<option value="">Seleccione</option>';
            data.distritos.forEach(function (item) {
                const id = (typeof item === 'object') ? item.id : item;
                const nombre = (typeof item === 'object') ? item.nombre : item;
                html += `<option value="${id}">${nombre}</option>`;
            });
            select.html(html).prop('disabled', false);
        }
    }).fail(function () {
        toastr.error('Error al cargar distritos');
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
}

function nextPrev(n) {
    // Si vamos adelante, validar paso actual
    if (n == 1 && !validateForm()) return false;

    // Si vamos al paso de confirmación (Paso 6), generar resumen
    if (currentStep + n == 6) {
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
        toastr.warning('Por favor ingrese el código de voucher o DNI');
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
            console.log('=== RESPONSE FROM VALIDATE-PAYMENT API ===');
            console.log('Full response:', response);
            console.log('response.valid:', response.valid);
            console.log('response.payments:', response.payments);
            console.log('=========================================');

            if (response.valid && response.payments && response.payments.length > 0) {
                mostrarDetallesPago(response.payments);
                toastr.success('Pagos encontrados y verificados');
                $('#pago_feedback').html('<div class="alert alert-success"><i class="fas fa-check-circle"></i> Pago verificado correctamente</div>');
            } else {
                toastr.error(response.message || 'No se encontraron pagos válidos');
                $('#pago_feedback').html('<div class="alert alert-danger"><i class="fas fa-times-circle"></i> ' + (response.message || 'Pago no encontrado') + '</div>');
            }
        },
        error: function (xhr) {
            console.error('Error validando pago:', xhr);
            let msg = 'Error al validar el pago';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                msg = xhr.responseJSON.message;
            }
            toastr.error(msg);
            $('#pago_feedback').html('<div class="alert alert-danger"><i class="fas fa-exclamation-triangle"></i> ' + msg + '</div>');
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
    console.log('=== MOSTRAR DETALLES PAGO ===');

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
                    fecha: pago.fecha,
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
                                <div class="payment-concept mb-1 text-wrap">${pago.concepto}</div>
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

    // Footer Total (Estilo Tarjeta Resumen)
    html += `
        <div class="card bg-primary text-white shadow-xl border-0 rounded-lg mt-4">
            <div class="card-body d-flex justify-content-between align-items-center p-3">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-white bg-opacity-30 p-2 me-3">
                        <i class="fas fa-calculator fa-fw fa-lg"></i>
                    </div>
                    <div>
                        <div class="text-uppercase small text-white-50 fw-bold">Total Aplicado</div>
                        <div class="small text-white-50">Suma de pagos seleccionados</div>
                    </div>
                </div>
                <h2 class="mb-0 fw-bold text-white" id="total_seleccionado">S/ 0.00</h2>
            </div>
        </div>
    `;

    $('#voucher_details').html(html).slideDown();

    actualizarPagosSeleccionados();
}

function actualizarPagosSeleccionados() {
    let total = 0;
    let secuencias = [];
    let fechaReciente = null;

    // Resetear estilos visuales primero
    $('.payment-card-label').removeClass('selected-card');

    $('.payment-checkbox:checked').each(function () {
        const monto = parseFloat($(this).data('monto'));
        const secuencia = $(this).val();
        const fecha = $(this).data('fecha');
        const index = $(this).data('index');

        // Añadir clase visual de seleccionado a la tarjeta padre
        $('#label_pago_' + index).addClass('selected-card');

        total += monto;
        secuencias.push(secuencia);

        if (!fechaReciente) {
            fechaReciente = fecha;
        }
    });

    console.log('Pagos seleccionados:', secuencias.length, 'Total:', total);

    // Actualizar UI del total
    $('#total_seleccionado').text('S/ ' + total.toFixed(2));

    // Actualizar campos ocultos
    $('#monto_matricula').val(total.toFixed(2));
    $('#monto_ensenanza').val(0);
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
            toastr.error('Las contraseñas no coinciden');
            $('#estudiante_password_confirmation').addClass('is-invalid');
            valid = false;
        }

        // Validar DNI verificado
        if (!$('#estudiante_dni').val()) {
            toastr.error('Debe verificar su DNI primero');
            valid = false;
        }
    }

    if (currentStep == 5) {
        // Validar archivos
        const requiredDocs = ['foto', 'dni_pdf', 'certificado_estudios', 'voucher_pago'];
        requiredDocs.forEach(function (doc) {
            const input = $('#' + doc);
            if (input.length && !input.val()) {
                input.addClass('is-invalid');
                valid = false;
            }
        });
    }

    if (!valid) {
        toastr.error('Por favor complete todos los campos requeridos correctamente');
    }

    return valid;
}

function verificarPostulante(btnElement) {
    console.log('Iniciando verificación de postulante...');
    const dni = $('#check_dni').val();
    if (dni.length !== 8) {
        // Uso de SweetAlert para error de formato de DNI
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'DNI Inválido',
                text: 'El DNI debe contener exactamente 8 dígitos.',
                confirmButtonText: 'Aceptar'
            });
        } else {
            toastr.error('DNI debe tener 8 dígitos');
        }
        return;
    }

    let btn = $(btnElement);
    if (btn.length === 0) {
        btn = $('#btn-verificar-dni');
    }

    const originalText = btn.text();
    btn.prop('disabled', true).text('Verificando...');

    $.post('/postulacion/check-postulante', {
        dni: dni,
        _token: $('meta[name="csrf-token"]').attr('content')
    }, function (response) {
        let swalIcon = 'success';
        let swalTitle = 'Verificación Exitosa';
        let swalText = response.message || 'Datos listos para continuar.';

        if (response.status === 'registered') {
            swalIcon = 'warning';
            swalTitle = 'Postulante ya Registrado';
            // Mensaje de warning para toastr
            toastr.warning(response.message);
        } else {
            // Nuevo o Recurrente
            $('#estudiante_dni').val(dni);
            $('#personal_fields').slideDown();

            if (response.estudiante) {
                // Llenar campos si el estudiante ya existe
                $('#estudiante_nombre').val(response.estudiante.nombre);
                $('#estudiante_apellido_paterno').val(response.estudiante.apellido_paterno);
                $('#estudiante_apellido_materno').val(response.estudiante.apellido_materno);
                $('#estudiante_fecha_nacimiento').val(response.estudiante.fecha_nacimiento);
                $('#estudiante_genero').val(response.estudiante.genero);
                $('#estudiante_telefono').val(response.estudiante.telefono);
                $('#estudiante_direccion').val(response.estudiante.direccion);
                $('#estudiante_email').val(response.estudiante.email);

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
                swalText = 'Consultando RENIEC para autocompletar sus datos...';

                // NUEVO: Consultar RENIEC automáticamente para estudiantes nuevos
                consultarReniecEstudiante(dni);
            }
        }

        // Mostrar SweetAlert (excepto si ya fue manejado como 'registered' arriba)
        if (response.status !== 'registered' && typeof Swal !== 'undefined') {
            Swal.fire({
                icon: swalIcon,
                title: swalTitle,
                text: swalText,
                confirmButtonText: 'Continuar'
            });
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
            toastr.error(msg);
        }
    }).always(function () {
        btn.prop('disabled', false).text(originalText);
    });
}

function consultarDNIPadre(tipo) {
    const dni = $('#' + tipo + '_dni').val();
    if (dni.length !== 8) {
        toastr.error('DNI debe tener 8 dígitos');
        return;
    }

    const btn = event.target;
    const originalText = $(btn).text();
    $(btn).prop('disabled', true).text('Consultando...');

    $.post('/api/reniec/consultar', {
        dni: dni,
        _token: $('meta[name="csrf-token"]').attr('content')
    }, function (response) {
        if (response.success && response.data) {
            $('#' + tipo + '_nombre').val(response.data.nombres);
            $('#' + tipo + '_apellidos').val(response.data.apellido_paterno + ' ' + response.data.apellido_materno);
            toastr.success('Datos encontrados');
        } else {
            toastr.warning('No se encontraron datos');
        }
    }).fail(function () {
        toastr.error('Error al consultar RENIEC');
    }).always(function () {
        $(btn).prop('disabled', false).text(originalText);
    });
} // <--- Cierre CORRECTO de consultarDNIPadre

// Nueva función para consultar RENIEC y autocompletar datos del estudiante
function consultarReniecEstudiante(dni) {
    console.log('Consultando RENIEC para DNI:', dni);

    // Mostrar indicador de carga
    toastr.info('Consultando RENIEC...', 'Por favor espere');

    $.ajax({
        url: '/api/reniec/consultar',
        method: 'POST',
        data: {
            dni: dni,
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        success: function (response) {
            console.log('Respuesta RENIEC:', response);

            if (response.success && response.data) {
                // Autocompletar campos con datos de RENIEC
                $('#estudiante_nombre').val(response.data.nombres || '');
                $('#estudiante_apellido_paterno').val(response.data.apellido_paterno || '');
                $('#estudiante_apellido_materno').val(response.data.apellido_materno || '');

                if (response.data.fecha_nacimiento) {
                    $('#estudiante_fecha_nacimiento').val(response.data.fecha_nacimiento);
                }

                if (response.data.genero) {
                    $('#estudiante_genero').val(response.data.genero);
                }

                if (response.data.direccion) {
                    $('#estudiante_direccion').val(response.data.direccion);
                }

                // Mostrar mensaje de éxito
                toastr.success('Datos cargados desde RENIEC', 'Éxito');

                // Mostrar alerta informativa
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Datos Autocompletos',
                        html: 'Se han cargado automáticamente sus datos desde RENIEC:<br><br>' +
                            '<strong>' + response.data.nombres + ' ' +
                            response.data.apellido_paterno + ' ' +
                            response.data.apellido_materno + '</strong><br><br>' +
                            'Por favor verifique y complete los datos faltantes.',
                        confirmButtonText: 'Continuar'
                    });
                }
            } else {
                console.warn('No se encontraron datos en RENIEC');
                toastr.warning('No se encontraron datos en RENIEC. Por favor complete manualmente.', 'Atención');
            }
        },
        error: function (xhr) {
            console.error('Error consultando RENIEC:', xhr);
            let errorMsg = 'No se pudo consultar RENIEC. Por favor complete los datos manualmente.';

            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMsg = xhr.responseJSON.message;
            }

            toastr.warning(errorMsg, 'Advertencia');
        }
    });
}

// Nueva función para pre-cargar datos de padres
function precargarDatosPadres(padres) {
    console.log('Pre-cargando datos de padres:', padres);

    let padresCargados = 0;

    // Pre-cargar datos del padre si existen
    if (padres.padre) {
        console.log('Datos del padre encontrados:', padres.padre);
        $('#padre_dni').val(padres.padre.numero_documento || '');
        $('#padre_nombre').val(padres.padre.nombre || '');
        const apellidosPadre = (padres.padre.apellido_paterno || '') + ' ' + (padres.padre.apellido_materno || '');
        $('#padre_apellidos').val(apellidosPadre.trim());
        $('#padre_telefono').val(padres.padre.telefono || '');
        $('#padre_email').val(padres.padre.email || '');
        // Nota: ocupación no está en el modelo User, se deja vacío
        padresCargados++;
    } else {
        console.log('No se encontraron datos del padre');
    }

    // Pre-cargar datos de la madre si existen
    if (padres.madre) {
        console.log('Datos de la madre encontrados:', padres.madre);
        $('#madre_dni').val(padres.madre.numero_documento || '');
        $('#madre_nombre').val(padres.madre.nombre || '');
        const apellidosMadre = (padres.madre.apellido_paterno || '') + ' ' + (padres.madre.apellido_materno || '');
        $('#madre_apellidos').val(apellidosMadre.trim());
        $('#madre_telefono').val(padres.madre.telefono || '');
        $('#madre_email').val(padres.madre.email || '');
        padresCargados++;
    } else {
        console.log('No se encontraron datos de la madre');
    }

    // Mostrar mensaje informativo si se cargaron datos
    if (padresCargados > 0) {
        const mensaje = padresCargados === 2 ? 'Datos de padre y madre cargados' :
            padres.padre ? 'Datos del padre cargados' : 'Datos de la madre cargados';
        toastr.success(mensaje + ' de postulación anterior', 'Datos de Padres');
    } else {
        console.log('No se encontraron datos de padres para pre-cargar');
    }
}

// Nueva función para pre-cargar datos académicos (colegio, carrera, etc.)
function precargarDatosAcademicos(datosAcademicos) {
    console.log('Pre-cargando datos académicos:', datosAcademicos);

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

        console.log('Cargando ubicación del colegio:', colegio);

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

        toastr.success('Colegio cargado: ' + colegio.nombre, 'Datos Académicos');
    }
}

// Nueva función para mostrar archivos existentes
function mostrarArchivosExistentes(archivos) {
    console.log('Mostrando archivos existentes:', archivos);

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
        toastr.success(`${archivosEncontrados} archivo(s) encontrado(s) de postulación anterior`, 'Archivos');
    }
}

// Nueva función para ver archivo en modal
function verArchivoModal(url, tipo) {
    console.log('Abriendo modal para ver archivo:', url, tipo);

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

function generarResumen() { // <--- DEFINICIÓN CORRECTA de generarResumen
    const fields = [
        { label: 'DNI', id: 'estudiante_dni' },
        { label: 'Nombre', id: 'estudiante_nombre' },
        { label: 'Apellido Paterno', id: 'estudiante_apellido_paterno' },
        { label: 'Apellido Materno', id: 'estudiante_apellido_materno' },
        { label: 'Carrera', id: 'carrera_id', isSelect: true },
        { label: 'Turno', id: 'turno_id', isSelect: true },
        { label: 'Colegio', id: 'centro_educativo_id', isHidden: true, displayId: 'buscar_colegio' },
        { label: 'Voucher', id: 'voucher_secuencia' }
    ];

    let html = '<ul class="list-group">';
    fields.forEach(field => {
        let el = $('#' + field.id);
        let value = el.val();

        if (field.isSelect && el.length && el[0].selectedIndex >= 0) {
            value = el.find('option:selected').text();
        } else if (field.displayId) {
            value = $('#' + field.displayId).val();
        }

        if (value) {
            html += `<li class="list-group-item"><strong>${field.label}:</strong> ${value}</li>`;
        }
    });
    html += '</ul>';
    $('#resumen_final').html(html);
} // <--- Cierre CORRECTO de generarResumen

function submitPostulacion() {
    const formData = new FormData($('#formPostulacionPublica')[0]);
    const submitBtn = $('#formPostulacionPublica button[type="submit"]');

    submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Enviando...');

    $.ajax({
        url: '/postulacion/store',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function (data) {
            if (data.success) {
                // Usar SweetAlert para éxito final
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Postulación Enviada!',
                        text: 'Su postulación ha sido enviada con éxito. Redireccionando...',
                        showConfirmButton: false
                    });
                } else {
                    toastr.success('¡Postulación enviada con éxito!');
                }
                setTimeout(function () {
                    // Nota: 'closeModal' debe ser una función global disponible en el entorno
                    if (typeof closeModal === 'function') {
                        closeModal('postulacionModal');
                    }
                    location.reload();
                }, 2000);
            } else {
                toastr.error('Error: ' + (data.message || 'Error desconocido'));
                submitBtn.prop('disabled', false).html('ENVIAR POSTULACIÓN');
            }
        },
        error: function (xhr) {
            console.error('Error:', xhr);
            let errorMsg = 'Ocurrió un error al enviar la postulación.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMsg = xhr.responseJSON.message;
            }
            toastr.error(errorMsg);
            submitBtn.prop('disabled', false).html('ENVIAR POSTULACIÓN');
        }
    });
}

// Función para buscar pagos automáticamente usando el DNI del estudiante
function buscarPagosAutomatico() {
    const dni = $('#estudiante_dni').val();

    if (!dni || dni.length !== 8) {
        // No es error crítico, solo informativo, ya que esto se llama al entrar al paso
        console.log('Advertencia: DNI no disponible para validación automática de pago.');
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
    toastr.info('Puede ingresar otro DNI o código de voucher manualmente');
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

// Modificar la función showStep para actualizar el DNI cuando se llega al paso 5 y validar pago
const originalShowStep = showStep;

showStep = function (n) {
    originalShowStep(n);

    // Si llegamos al paso 5 (Documentos y Pago)
    if (n === 5) {
        actualizarDNIDisplay();
        // CAMBIO CRÍTICO: Llamar a buscarPagosAutomatico() automáticamente.
        // Se añade un pequeño delay para asegurar que el DOM se haya cargado.
        setTimeout(function () {
            buscarPagosAutomatico();
        }, 300);
    }

    // Si llegamos al paso 4 (Académico), prevenir conflictos de Select2
    if (n === 4) {
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