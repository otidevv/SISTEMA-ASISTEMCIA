console.log("entraste");
$(document).ready(function() {
    let cicloActivo = null;
    let inscripcionActual = null;
    let colegioSeleccionado = null;

    // Verificar estado de inscripción y cargar información del ciclo
    verificarInscripcion();

    function verificarInscripcion() {
        $.ajax({
            url: '/json/inscripciones-estudiante/verificar',
            type: 'GET',
                    success: function(response) {
                        if (response.success) {
                            if (response.inscrito) {
                                cicloActivo = response.ciclo; // <-- AGREGADO
                                mostrarInfoCiclo(); // <-- AGREGADO
                                inscripcionActual = response.inscripcion;
                                mostrarInscripcionActual();
                            } else {
                                cargarCicloActivo();
                            }
                        }
                    },            error: function() {
                mostrarError('Error al verificar el estado de inscripción');
            }
        });
    }

    function cargarCicloActivo() {
        $.ajax({
            url: '/json/inscripciones-estudiante/ciclo-activo',
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    cicloActivo = response.ciclo;
                    mostrarInfoCiclo();
                    mostrarFormularioInscripcion(response);
                } else {
                    mostrarSinCicloActivo();
                }
            },
            error: function() {
                mostrarError('Error al cargar información del ciclo');
            }
        });
    }

    function mostrarInfoCiclo() {
        if (cicloActivo) {
            $('#ciclo-info').html(`
                <h5 class="mb-0">${cicloActivo.nombre}</h5>
                <small>Inicio: ${formatDate(cicloActivo.fecha_inicio)}</small>
            `);
        }
    }

    function mostrarInscripcionActual() {
        // Verificar si es una postulación o inscripción
        if (inscripcionActual.es_postulacion) {
            // Es una postulación
            let estadoBadge = '';
            let iconoEstado = '';
            let tituloEstado = '';

            switch(inscripcionActual.estado) {
                case 'pendiente':
                    estadoBadge = 'bg-warning';
                    iconoEstado = 'mdi-clock-outline text-warning';
                    tituloEstado = 'Postulación en Proceso';
                    break;
                case 'aprobado':
                    estadoBadge = 'bg-success';
                    iconoEstado = 'mdi-check-circle text-success';
                    tituloEstado = 'Postulación Aprobada';
                    break;
                case 'rechazado':
                    estadoBadge = 'bg-danger';
                    iconoEstado = 'mdi-close-circle text-danger';
                    tituloEstado = 'Postulación Rechazada';
                    break;
                case 'observado':
                    estadoBadge = 'bg-info';
                    iconoEstado = 'mdi-alert-circle text-info';
                    tituloEstado = 'Postulación con Observaciones';
                    break;
            }

            $('#contenedor-inscripcion').html(`
                <div class="card inscription-card">
                    <div class="card-body text-center py-5">
                        <i class="mdi ${iconoEstado}" style="font-size: 60px;"></i>
                        <h3 class="mt-3">${tituloEstado}</h3>
                        <div class="mt-4">
                            <p><strong>Código de Postulante:</strong> ${inscripcionActual.codigo_postulante || inscripcionActual.id}</p>
                            <p><strong>Tipo:</strong> ${inscripcionActual.tipo_inscripcion}</p>
                            <p><strong>Carrera:</strong> ${inscripcionActual.carrera}</p>
                            <p><strong>Turno:</strong> ${inscripcionActual.turno}</p>
                            <p><strong>Estado:</strong> <span class="badge ${estadoBadge}">${inscripcionActual.estado.toUpperCase()}</span></p>
                            <p><strong>Fecha de postulación:</strong> ${formatDate(inscripcionActual.fecha_inscripcion)}</p>
                            ${inscripcionActual.estado === 'pendiente' ?
                                '<div class="alert alert-warning mt-3">Tu postulación está siendo revisada por el personal administrativo. Te notificaremos cuando sea aprobada.</div>' : ''}
                        </div>
                    </div>
                </div>
            `);
        } else {
            // Es una inscripción confirmada
            $('#contenedor-inscripcion').html(`
                <div class="card inscription-card">
                    <div class="card-body text-center py-5">
                        <i class="mdi mdi-check-circle text-success" style="font-size: 60px;"></i>
                        <h3 class="mt-3">¡Ya estás inscrito!</h3>
                        <div class="mt-4">
                            <p><strong>Tipo de inscripción:</strong> ${inscripcionActual.tipo_inscripcion}</p>
                            <p><strong>Carrera:</strong> ${inscripcionActual.carrera}</p>
                            <p><strong>Turno:</strong> ${inscripcionActual.turno}</p>
                            <p><strong>Estado:</strong> <span class="badge bg-success">${inscripcionActual.estado}</span></p>
                            <p><strong>Fecha de inscripción:</strong> ${formatDate(inscripcionActual.fecha_inscripcion)}</p>
                        </div>
                    </div>
                </div>
            `);
        }
    }

    function mostrarFormularioInscripcion(data) {
        let carrerasOptions = '<option value="">Seleccione una carrera...</option>';
        data.carreras.forEach(carrera => {
            const vacantesText = carrera.vacantes_disponibles === 'Sin límite' ?
                'Sin límite' : `${carrera.vacantes_disponibles} vacantes`;
            carrerasOptions += `<option value="${carrera.id}" ${!carrera.tiene_vacantes ? 'disabled' : ''}>
                ${carrera.nombre} (${vacantesText})
            </option>`;
        });

        let turnosOptions = '<option value="">Seleccione un turno...</option>';
        data.turnos.forEach(turno => {
            turnosOptions += `<option value="${turno.id}">${turno.nombre} (${turno.hora_inicio} - ${turno.hora_fin})</option>`;
        });

        let tiposOptions = '<option value="">Seleccione tipo...</option>';
        data.tipos_inscripcion.forEach(tipo => {
            tiposOptions += `<option value="${tipo.value}">${tipo.label}</option>`;
        });

        $('#contenedor-inscripcion').html(`
            <div class="card inscription-card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0 text-white">
                       <i class="mdi mdi-account-plus me-2"></i>Formulario de Inscripción y Postulación
                    </h4>
                </div>
                <div class="card-body">
                    <form id="formInscripcion">
                        <div class="row">
                            <!-- Tipo de inscripción -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tipo de Inscripción <span class="text-danger">*</span></label>
                                <select class="form-select" id="tipo_inscripcion" name="tipo_inscripcion" required>
                                    ${tiposOptions}
                                </select>
                            </div>

                            <!-- Carrera -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Carrera Profesional <span class="text-danger">*</span></label>
                                <select class="form-select" id="carrera_id" name="carrera_id" required>
                                    ${carrerasOptions}
                                </select>
                            </div>

                            <!-- Turno -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Turno <span class="text-danger">*</span></label>
                                <select class="form-select" id="turno_id" name="turno_id" required>
                                    ${turnosOptions}
                                </select>
                            </div>

                            <!-- Centro Educativo -->
                            <div class="col-12">
                                <h5 class="mt-3 mb-3">Institución Educativa</h5>
                            </div>

                            <!-- Departamento -->
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Departamento <span class="text-danger">*</span></label>
                                <select class="form-select" id="departamento" required>
                                    <option value="">Cargando...</option>
                                </select>
                            </div>

                            <!-- Provincia -->
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Provincia <span class="text-danger">*</span></label>
                                <select class="form-select" id="provincia" disabled required>
                                    <option value="">Seleccione departamento primero</option>
                                </select>
                            </div>

                            <!-- Distrito -->
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Distrito <span class="text-danger">*</span></label>
                                <select class="form-select" id="distrito" disabled required>
                                    <option value="">Seleccione provincia primero</option>
                                </select>
                            </div>

                            <!-- Búsqueda de colegio -->
                            <div class="col-md-8 mb-3">
                                <label class="form-label">Nombre del Colegio <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="buscar_colegio"
                                         placeholder="Escriba el nombre del colegio..." disabled>
                                <div id="sugerencias-colegios" class="list-group mt-1" style="max-height: 200px; overflow-y: auto;"></div>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">&nbsp;</label>
                                <button type="button" class="btn btn-secondary w-100" id="btnBuscarColegio" disabled>
                                    <i class="mdi mdi-magnify"></i> Buscar
                                </button>
                            </div>

                            <!-- Colegio seleccionado -->
                            <div class="col-12 mb-3" id="colegio-seleccionado" style="display: none;">
                                <div class="alert alert-info">
                                    <strong>Colegio seleccionado:</strong>
                                    <span id="nombre-colegio-seleccionado"></span>
                                </div>
                            </div>

                            <!-- Sección de Documentos -->
                            <div class="col-12">
                                <h5 class="mt-4 mb-3">Documentos Requeridos</h5>
                            </div>

                            <!-- Voucher de Pago -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Voucher de Pago <span class="text-danger">*</span></label>
                                <input type="file" class="form-control" id="voucher_pago" name="voucher_pago"
                                         accept=".pdf,.jpg,.jpeg,.png" required>
                                <small class="text-muted">PDF, JPG o PNG (Max: 5MB)</small>
                            </div>

                            <!-- Certificado de Estudios -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Certificado de Estudios <span class="text-danger">*</span></label>
                                <input type="file" class="form-control" id="certificado_estudios" name="certificado_estudios"
                                         accept=".pdf,.jpg,.jpeg,.png" required>
                                <small class="text-muted">PDF, JPG o PNG (Max: 5MB)</small>
                            </div>

                            <!-- Carta de Compromiso -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Carta de Compromiso <span class="text-danger">*</span></label>
                                <input type="file" class="form-control" id="carta_compromiso" name="carta_compromiso"
                                         accept=".pdf,.jpg,.jpeg,.png" required>
                                <small class="text-muted">PDF, JPG o PNG (Max: 5MB)</small>
                            </div>

                            <!-- Constancia de Estudios -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Constancia de Estudios <span class="text-danger">*</span></label>
                                <input type="file" class="form-control" id="constancia_estudios" name="constancia_estudios"
                                         accept=".pdf,.jpg,.jpeg,.png" required>
                                <small class="text-muted">PDF, JPG o PNG (Max: 5MB)</small>
                            </div>

                            <!-- DNI -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">DNI <span class="text-danger">*</span></label>
                                <input type="file" class="form-control" id="dni_documento" name="dni_documento"
                                         accept=".pdf,.jpg,.jpeg,.png" required>
                                <small class="text-muted">PDF, JPG o PNG (Max: 5MB)</small>
                            </div>

                            <!-- Foto Carnet -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Foto Carnet <span class="text-danger">*</span></label>
                                <input type="file" class="form-control" id="foto_carnet" name="foto_carnet"
                                         accept=".jpg,.jpeg,.png" required>
                                <small class="text-muted">JPG o PNG (Max: 2MB)</small>
                            </div>

                            <!-- Sección de Datos del Voucher -->
                            <div class="col-12" id="seccion-voucher" style="display: none;">
                                <h5 class="mt-4 mb-3">Datos del Voucher de Pago</h5>
                            </div>

                            <!-- Número de Recibo -->
                            <div class="col-md-6 mb-3" style="display: none;" id="campo-numero-recibo">
                                <label class="form-label">Número de Recibo <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="numero_recibo" name="numero_recibo"
                                         placeholder="Ej: 0001-0004535" required>
                            </div>

                            <!-- Fecha de Emisión -->
                            <div class="col-md-6 mb-3" style="display: none;" id="campo-fecha-emision">
                                <label class="form-label">Fecha de Emisión <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="fecha_emision_voucher" name="fecha_emision_voucher" required>
                            </div>

                            <!-- Monto Matrícula -->
                            <div class="col-md-6 mb-3" style="display: none;" id="campo-monto-matricula">
                                <label class="form-label">Matrícula de Ciclo de Preparación General (S/.) <span class="text-danger">*</span></label>
                                <!-- Título para la primera opción de matrícula -->
                                <h6 class="mt-2 mb-1">Opción 1 Matrícula Regular:</h6>
                                <!-- Botones con estilo mejorado -->
                                <div class="d-flex justify-content-between mb-2">
                                    <button type="button" class="btn btn-outline-secondary btn-sm btn-block btn-matricula flex-grow-1 me-2" data-value="100">S/ 100</button>
                                </div>
                                <!-- Título para la segunda opción de matrícula -->
                                <h6 class="mt-2 mb-1">Opción 2 Descuento 50% (Resolución):</h6>
                                <div class="d-flex justify-content-between mb-2">
                                    <button type="button" class="btn btn-outline-secondary btn-sm btn-block btn-matricula flex-grow-1" data-value="50">S/ 50</button>
                                </div>
                                <input type="number" class="form-control" id="monto_matricula" name="monto_matricula"
                                         step="0.01" min="0" placeholder="0.00" required>
                            </div>

                            <!-- Monto Enseñanza -->
                            <div class="col-md-6 mb-3" style="display: none;" id="campo-monto-ensenanza">
                                <label class="form-label">Costo de Enseñanza por Preparación (S/.) <span class="text-danger">*</span></label>
                                <!-- Título para la primera opción de enseñanza -->
                                <h6 class="mt-2 mb-1">Opción 1 Enseñanza Regular:</h6>
                                <div class="d-flex justify-content-between mb-2">
                                    <button type="button" class="btn btn-outline-secondary btn-sm btn-block btn-ensenanza flex-grow-1 me-2" data-value="1050">S/ 1050</button>
                                </div>
                                <!-- Título para la segunda opción de enseñanza -->
                                <h6 class="mt-2 mb-1">Opción 2 Descuento 50% (Resolución):</h6>
                                <div class="d-flex justify-content-between mb-2">
                                    <button type="button" class="btn btn-outline-secondary btn-sm btn-block btn-ensenanza flex-grow-1" data-value="525">S/ 525</button>
                                </div>
                                <input type="number" class="form-control" id="monto_ensenanza" name="monto_ensenanza"
                                         step="0.01" min="0" placeholder="0.00" required>
                            </div>

                            <!-- Subtotal -->
                            <div class="col-12 mb-3" style="display: none;" id="campo-subtotal">
                                <div class="alert alert-success">
                                    <div class="row align-items-center">
                                        <div class="col-md-8">
                                            <strong>SUBTOTAL A PAGAR:</strong>
                                        </div>
                                        <div class="col-md-4 text-end">
                                            <h4 class="mb-0">S/. <span id="monto_subtotal">0.00</span></h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="text-center mt-4">
                            <button type="submit" class="btn btn-inscribir btn-lg">
                                <i class="mdi mdi-send me-2"></i>Inscribirme Ahora
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        `);

        // Cargar departamentos
        cargarDepartamentos();

        // Configurar eventos
        configurarEventos();
    }

    function cargarDepartamentos() {
        $.ajax({
            url: '/json/inscripciones-estudiante/departamentos',
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    let options = '<option value="">Seleccione departamento...</option>';
                    response.departamentos.forEach(depto => {
                        options += `<option value="${depto}">${depto}</option>`;
                    });
                    $('#departamento').html(options);
                }
            }
        });
    }

    function configurarEventos() {
        // Cambio de departamento
        $('#departamento').on('change', function() {
            const depto = $(this).val();
            if (depto) {
                cargarProvincias(depto);
                $('#provincia').prop('disabled', false);
                $('#distrito').prop('disabled', true).html('<option value="">Seleccione provincia primero</option>');
                $('#buscar_colegio').prop('disabled', true).val('');
                $('#btnBuscarColegio').prop('disabled', true);
                $('#sugerencias-colegios').empty();
                ocultarColegioSeleccionado();
            }
        });

        // Cambio de provincia
        $('#provincia').on('change', function() {
            const depto = $('#departamento').val();
            const prov = $(this).val();
            if (prov) {
                cargarDistritos(depto, prov);
                $('#distrito').prop('disabled', false);
                $('#buscar_colegio').prop('disabled', true).val('');
                $('#btnBuscarColegio').prop('disabled', true);
                $('#sugerencias-colegios').empty();
                ocultarColegioSeleccionado();
            }
        });

        // Cambio de distrito - ¡AHORA SE CARGA LA LISTA DE COLEGIOS AUTOMÁTICAMENTE!
        $('#distrito').on('change', function() {
            if ($(this).val()) {
                $('#buscar_colegio').prop('disabled', false);
                // No es necesario habilitar el botón de búsqueda si la búsqueda es automática
                $('#btnBuscarColegio').prop('disabled', false); 
                $('#sugerencias-colegios').empty();
                ocultarColegioSeleccionado();
                
                // Llamar a la función de búsqueda de colegios de inmediato
                buscarColegios(); 
            }
        });

        // Búsqueda de colegio
        $('#btnBuscarColegio').on('click', buscarColegios);
        // Evento keyup para la búsqueda en tiempo real
        $('#buscar_colegio').on('keyup', function() {
            const searchTerm = $(this).val();
            if (searchTerm.length >= 2) {
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

        // Cambio en el archivo de voucher
        $('#voucher_pago').on('change', function() {
            if (this.files && this.files[0]) {
                // Mostrar campos de datos del voucher
                $('#seccion-voucher').show();
                $('#campo-numero-recibo').show();
                $('#campo-fecha-emision').show();
                $('#campo-monto-matricula').show();
                $('#campo-monto-ensenanza').show();
                $('#campo-subtotal').show();
            } else {
                // Ocultar campos si se quita el archivo
                $('#seccion-voucher').hide();
                $('#campo-numero-recibo').hide();
                $('#campo-fecha-emision').hide();
                $('#campo-monto-matricula').hide();
                $('#campo-monto-ensenanza').hide();
                $('#campo-subtotal').hide();
            }
        });

        // Calcular subtotal cuando cambien los montos
        $('#monto_matricula, #monto_ensenanza').on('input', function() {
            const matricula = parseFloat($('#monto_matricula').val()) || 0;
            const ensenanza = parseFloat($('#monto_ensenanza').val()) || 0;
            const subtotal = matricula + ensenanza;
            $('#monto_subtotal').text(subtotal.toFixed(2));
        });

        // Envío del formulario
        $('#formInscripcion').on('submit', function(e) {
            e.preventDefault();
            if (!colegioSeleccionado) {
                toastr.warning('Por favor seleccione un colegio de la lista');
                return;
            }

            // Validar archivos
            const archivosRequeridos = [
                { id: 'voucher_pago', nombre: 'Voucher de Pago' },
                { id: 'certificado_estudios', nombre: 'Certificado de Estudios' },
                { id: 'carta_compromiso', nombre: 'Carta de Compromiso' },
                { id: 'constancia_estudios', nombre: 'Constancia de Estudios' },
                { id: 'dni_documento', nombre: 'DNI' },
                { id: 'foto_carnet', nombre: 'Foto Carnet' }
            ];

            for (let archivo of archivosRequeridos) {
                const input = document.getElementById(archivo.id);
                if (!input.files || !input.files[0]) {
                    toastr.warning(`Por favor seleccione el archivo: ${archivo.nombre}`);
                    return;
                }
            }

            mostrarConfirmacion();
        });

        // Evento de click para los botones de matrícula
        $(document).on('click', '.btn-matricula', function() {
            if ($('#campo-monto-matricula').is(':hidden')) {
                toastr.info('Por favor, sube primero el voucher de pago.');
                return;
            }
            const value = $(this).data('value');
            $('#monto_matricula').val(value).trigger('input');
        });

        // Evento de click para los botones de enseñanza
        $(document).on('click', '.btn-ensenanza', function() {
            if ($('#campo-monto-ensenanza').is(':hidden')) {
                toastr.info('Por favor, sube primero el voucher de pago.');
                return;
            }
            const value = $(this).data('value');
            $('#monto_ensenanza').val(value).trigger('input');
        });
    }

    function cargarProvincias(departamento) {
        $.ajax({
            url: `/json/inscripciones-estudiante/provincias/${encodeURIComponent(departamento)}`,
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    let options = '<option value="">Seleccione provincia...</option>';
                    response.provincias.forEach(prov => {
                        options += `<option value="${prov}">${prov}</option>`;
                    });
                    $('#provincia').html(options);
                }
            }
        });
    }

    function cargarDistritos(departamento, provincia) {
        $.ajax({
            url: `/json/inscripciones-estudiante/distritos/${encodeURIComponent(departamento)}/${encodeURIComponent(provincia)}`,
            type: 'GET',
            success: function(response) {
                if (response.success) {
                    let options = '<option value="">Seleccione distrito...</option>';
                    response.distritos.forEach(dist => {
                        options += `<option value="${dist}">${dist}</option>`;
                    });
                    $('#distrito').html(options);
                }
            }
        });
    }

    function buscarColegios() {
        const termino = $('#buscar_colegio').val();
        // Solo buscar si el término tiene al menos 2 caracteres o si el campo está vacío al seleccionar un distrito
        if (termino.length < 2 && termino.length !== 0) {
            $('#sugerencias-colegios').empty(); // Limpiar sugerencias si no hay suficientes caracteres
            return;
        }

        $.ajax({
            url: '/json/inscripciones-estudiante/buscar-colegios',
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

    function mostrarConfirmacion() {
        const tipo = $('#tipo_inscripcion option:selected').text();
        const carrera = $('#carrera_id option:selected').text();
        const turno = $('#turno_id option:selected').text();
        const numeroRecibo = $('#numero_recibo').val();
        const montoMatricula = parseFloat($('#monto_matricula').val()) || 0;
        const montoEnsenanza = parseFloat($('#monto_ensenanza').val()) || 0;
        const montoTotal = montoMatricula + montoEnsenanza;

        $('#resumen-inscripcion').html(`
            <ul>
                <li><strong>Tipo:</strong> ${tipo}</li>
                <li><strong>Carrera:</strong> ${carrera}</li>
                <li><strong>Turno:</strong> ${turno}</li>
                <li><strong>Colegio:</strong> ${colegioSeleccionado.nombre}</li>
                <li><strong>Documentos:</strong> 6 archivos seleccionados</li>
                ${numeroRecibo ? `<li><strong>N° Recibo:</strong> ${numeroRecibo}</li>` : ''}
                ${montoMatricula > 0 ? `<li><strong>Matrícula Ciclo Preparación:</strong> S/. ${montoMatricula.toFixed(2)}</li>` : ''}
                ${montoEnsenanza > 0 ? `<li><strong>Costo Enseñanza:</strong> S/. ${montoEnsenanza.toFixed(2)}</li>` : ''}
                ${montoTotal > 0 ? `<li><strong>TOTAL PAGADO:</strong> <span class="text-success">S/. ${montoTotal.toFixed(2)}</span></li>` : ''}
            </ul>
        `);

        $('#confirmModal').modal('show');
    }

    $('#btnConfirmarInscripcion').on('click', function() {
        // Crear FormData para enviar archivos
        const formData = new FormData();

        // Agregar datos del formulario
        formData.append('tipo_inscripcion', $('#tipo_inscripcion').val());
        formData.append('carrera_id', $('#carrera_id').val());
        formData.append('turno_id', $('#turno_id').val());
        formData.append('centro_educativo_id', colegioSeleccionado.id);

        // Agregar archivos con notificaciones
        const archivos = [
            {id: 'voucher_pago', nombre: 'Voucher de pago'},
            {id: 'certificado_estudios', nombre: 'Certificado de estudios'},
            {id: 'carta_compromiso', nombre: 'Carta de compromiso'},
            {id: 'constancia_estudios', nombre: 'Constancia de estudios'},
            {id: 'dni_documento', nombre: 'DNI'},
            {id: 'foto_carnet', nombre: 'Foto carnet'}
        ];

        let archivosSubidos = 0;
        archivos.forEach(function(archivo, index) {
            const input = document.getElementById(archivo.id);
            if (input.files && input.files[0]) {
                formData.append(archivo.id, input.files[0]);
                archivosSubidos++;
                // Mostrar toast individual para cada archivo
                setTimeout(() => {
                    toastr.info(`${archivo.nombre} cargado correctamente (${archivosSubidos}/6)`, 'Archivo ' + archivosSubidos, {
                        "closeButton": false,
                        "progressBar": true,
                        "positionClass": "toast-bottom-right",
                        "timeOut": "2000"
                    });
                }, index * 300); // Escalonar las notificaciones
            }
        });

        // Agregar datos del voucher
        formData.append('numero_recibo', $('#numero_recibo').val());
        formData.append('fecha_emision_voucher', $('#fecha_emision_voucher').val());
        formData.append('monto_matricula', $('#monto_matricula').val() || 0);
        formData.append('monto_ensenanza', $('#monto_ensenanza').val() || 0);

        // Agregar token CSRF
        formData.append('_token', $('meta[name="csrf-token"]').attr('content'));

        $.ajax({
            url: '/json/inscripciones-estudiante/registrar',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: function() {
                $('#btnConfirmarInscripcion').prop('disabled', true).html('<i class="mdi mdi-loading mdi-spin"></i> Procesando...');
            },
            xhr: function() {
                var xhr = new window.XMLHttpRequest();
                xhr.upload.addEventListener("progress", function(evt) {
                    if (evt.lengthComputable) {
                        var percentComplete = ((evt.loaded / evt.total) * 100).toFixed(0);
                        $('#btnConfirmarInscripcion').html(`<i class="mdi mdi-loading mdi-spin"></i> Subiendo... ${percentComplete}%`);
                    }
                }, false);
                return xhr;
            },
            success: function(response) {
                if (response.success) {
                    $('#confirmModal').modal('hide');

                    // Mostrar toast de confirmación de archivos
                    setTimeout(() => {
                        toastr.success('¡Todos tus archivos fueron subidos exitosamente!', 'Archivos Completos', {
                            "closeButton": true,
                            "progressBar": true,
                            "positionClass": "toast-top-center",
                            "timeOut": "3000"
                        });
                    }, archivos.length * 300 + 500);

                    // Mostrar mensaje diferente para postulación
                    if (response.postulacion) {
                        setTimeout(() => {
                            toastr.success('Postulación enviada exitosamente', '¡Registro Exitoso!', {
                                "closeButton": true,
                                "progressBar": true,
                                "positionClass": "toast-top-right",
                                "timeOut": "4000"
                            });
                        }, archivos.length * 300 + 2000);

                        // Mostrar mensaje adicional
                        setTimeout(() => {
                            toastr.info('Tu postulación está pendiente de aprobación. Te notificaremos cuando sea revisada.', 'Información', {
                                "closeButton": true,
                                "progressBar": true,
                                "positionClass": "toast-top-right",
                                "timeOut": "4000"
                            });
                        }, archivos.length * 300 + 3500);
                    } else {
                        setTimeout(() => {
                            toastr.success(response.message || 'Inscripción realizada correctamente', '¡Éxito!', {
                                "closeButton": true,
                                "progressBar": true,
                                "positionClass": "toast-top-right",
                                "timeOut": "3000"
                            });
                        }, archivos.length * 300 + 2000);
                    }

                    // Recargar página después de mostrar todos los mensajes
                    setTimeout(() => {
                        location.reload();
                    }, archivos.length * 300 + 6000);
                }
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                if (response && response.errors) {
                    let errores = '';
                    $.each(response.errors, function(key, value) {
                        errores += value[0] + '<br>';
                    });
                    toastr.error(errores);
                } else {
                    toastr.error(response.message || 'Error al registrar inscripción');
                }
                $('#btnConfirmarInscripcion').prop('disabled', false).html('Confirmar Inscripción');
            }
        });
    });

    function mostrarSinCicloActivo() {
        $('#contenedor-inscripcion').html(`
            <div class="card inscription-card">
                <div class="card-body text-center py-5">
                    <i class="mdi mdi-calendar-remove text-warning" style="font-size: 60px;"></i>
                    <h3 class="mt-3">No hay ciclo activo</h3>
                    <p class="text-muted">Por el momento no hay un ciclo académico activo para inscripciones.</p>
                    <p>Por favor, vuelve más tarde o contacta con la administración.</p>
                </div>
            </div>
        `);
    }

    function mostrarError(mensaje) {
        $('#contenedor-inscripcion').html(`
            <div class="card inscription-card">
                <div class="card-body text-center py-5">
                    <i class="mdi mdi-alert-circle text-danger" style="font-size: 60px;"></i>
                    <h3 class="mt-3">Error</h3>
                    <p class="text-muted">${mensaje}</p>
                </div>
            </div>
        `);
    }

    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('es-ES', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
    }

    // Funciones para manejo de postulaciones y constancias
    function cargarEstadoPostulacion() {
        $.ajax({
            url: '/json/postulaciones/mi-postulacion-actual',
            type: 'GET',
            success: function(response) {
                if (response.success && response.postulacion) {
                    mostrarEstadoPostulacion(response.postulacion);
                } else {
                    $('#estado-postulacion').html(`
                        <div class="alert alert-info">
                            <i class="mdi mdi-information-outline me-2"></i>
                            No tienes postulaciones activas en este momento.
                        </div>
                    `);
                }
            },
            error: function() {
                $('#estado-postulacion').html(`
                    <div class="alert alert-warning">
                        <i class="mdi mdi-alert-outline me-2"></i>
                        Error al cargar el estado de postulación.
                    </div>
                `);
            }
        });
    }

    function mostrarEstadoPostulacion(postulacion) {
        let htmlConstancia = '';

        // Botones de constancia según el estado
        if (!postulacion.constancia_generada) {
            htmlConstancia = `
                <button class="btn btn-primary btn-sm" onclick="generarConstancia(${postulacion.id})">
                    <i class="mdi mdi-file-pdf-box me-1"></i>Generar Constancia
                </button>
            `;
        } else if (!postulacion.constancia_firmada) {
            htmlConstancia = `
                <button class="btn btn-success btn-sm me-2" onclick="descargarConstancia(${postulacion.id})">
                    <i class="mdi mdi-download me-1"></i>Descargar Constancia
                </button>
                <button class="btn btn-warning btn-sm" onclick="abrirModalSubirConstancia(${postulacion.id})">
                    <i class="mdi mdi-upload me-1"></i>Subir Constancia Firmada
                </button>
            `;
        } else {
            htmlConstancia = `
                <span class="badge bg-success">
                    <i class="mdi mdi-check-circle me-1"></i>Constancia Firmada Subida
                </span>
                <button class="btn btn-info btn-sm ms-2" onclick="verConstanciaFirmada(${postulacion.id})">
                    <i class="mdi mdi-eye me-1"></i>Ver Constancia
                </button>
            `;
        }

        $('#estado-postulacion').html(`
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Código de Postulante:</strong> ${postulacion.codigo_postulante}</p>
                    <p><strong>Estado:</strong>
                        <span class="badge bg-${postulacion.estado === 'aprobado' ? 'success' :
                                               postulacion.estado === 'rechazado' ? 'danger' :
                                               postulacion.estado === 'observado' ? 'warning' : 'info'}">
                            ${postulacion.estado.toUpperCase()}
                        </span>
                    </p>
                </div>
                <div class="col-md-6">
                    <p><strong>Carrera:</strong> ${postulacion.carrera_nombre}</p>
                    <p><strong>Turno:</strong> ${postulacion.turno_nombre}</p>
                    <p><strong>Fecha de Postulación:</strong> ${formatDate(postulacion.fecha_postulacion)}</p>
                </div>
            </div>
            <hr>
            <div class="text-center">
                <h6 class="mb-3">Estado de Constancia de Inscripción</h6>
                ${htmlConstancia}
            </div>
        `);
    }

    // Función global para generar constancia
    window.generarConstancia = function(postulacionId) {
        window.open(`/postulacion/constancia/generar/${postulacionId}`, '_blank');
        setTimeout(() => {
            cargarEstadoPostulacion();
        }, 2000);
    };

    // Función global para descargar constancia
    window.descargarConstancia = function(postulacionId) {
        window.open(`/postulacion/constancia/generar/${postulacionId}`, '_blank');
    };

    // Función global para abrir modal de subir constancia
    window.abrirModalSubirConstancia = function(postulacionId) {
        $('#postulacion_id').val(postulacionId);
        $('#subirConstanciaModal').modal('show');
    };

    // Función global para ver constancia firmada
    window.verConstanciaFirmada = function(postulacionId) {
        window.open(`/postulacion/constancia/ver/${postulacionId}`, '_blank');
    };

    // Manejar preview de archivo
    $('#documento_constancia').on('change', function() {
        const file = this.files[0];
        if (file) {
            const fileType = file.type;
            const fileName = file.name;

            $('#preview-constancia').show();

            if (fileType.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    $('#imagen-preview').attr('src', e.target.result).show();
                    $('#pdf-preview').hide();
                };
                reader.readAsDataURL(file);
            } else if (fileType === 'application/pdf') {
                $('#pdf-nombre').text(fileName);
                $('#pdf-preview').show();
                $('#imagen-preview').hide();
            }
        } else {
            $('#preview-constancia').hide();
        }
    });

    // Subir constancia firmada
    $('#btnSubirConstancia').on('click', function() {
        const formData = new FormData();
        const archivo = $('#documento_constancia')[0].files[0];
        const postulacionId = $('#postulacion_id').val();

        if (!archivo) {
            toastr.error('Por favor seleccione un archivo');
            return;
        }

        formData.append('documento_constancia', archivo);

        $(this).prop('disabled', true).html('<i class="mdi mdi-loading mdi-spin me-1"></i>Subiendo...');

        $.ajax({
            url: `/postulacion/constancia/subir/${postulacionId}`,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    toastr.success('Constancia firmada subida exitosamente');
                    $('#subirConstanciaModal').modal('hide');
                    $('#formSubirConstancia')[0].reset();
                    $('#preview-constancia').hide();
                    cargarEstadoPostulacion();
                    verificarInscripcion();
                } else {
                    toastr.error(response.message || 'Error al subir la constancia');
                }
                $('#btnSubirConstancia').prop('disabled', false).html('<i class="mdi mdi-upload me-1"></i>Subir Constancia');
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                toastr.error(response?.message || 'Error al subir la constancia');
                $('#btnSubirConstancia').prop('disabled', false).html('<i class="mdi mdi-upload me-1"></i>Subir Constancia');
            }
        });
    });

    // Cargar estado de postulación al inicio
    cargarEstadoPostulacion();

    // Agregar validación visual cuando se selecciona un archivo
    $('input[type="file"]').on('change', function() {
        const fileName = $(this).val().split('\\').pop();
        const inputId = $(this).attr('id');
        const labelText = $(`label[for="${inputId}"]`).text().replace(' *', '');

        if (fileName) {
            // Agregar indicador visual de archivo cargado
            $(this).removeClass('is-invalid').addClass('is-valid');

            // Mostrar mini toast de confirmación
            toastr.info(`${labelText}: ${fileName}`, 'Archivo seleccionado', {
                "closeButton": false,
                "progressBar": false,
                "positionClass": "toast-bottom-left",
                "timeOut": "1500",
                "showDuration": "100",
                "hideDuration": "100"
            });
        } else {
            $(this).removeClass('is-valid is-invalid');
        }
    });

    // Validar que todos los archivos estén seleccionados antes de confirmar
    $('#btnInscribirse').on('click', function() {
        let archivosRequeridos = [
            'voucher_pago',
            'certificado_estudios',
            'carta_compromiso',
            'constancia_estudios',
            'dni_documento',
            'foto_carnet'
        ];

        let todosCargados = true;
        let archivosFaltantes = [];

        archivosRequeridos.forEach(function(archivo) {
            const input = document.getElementById(archivo);
            if (!input || !input.files || !input.files[0]) {
                todosCargados = false;
                const label = $(`label[for="${archivo}"]`).text().replace(' *', '');
                archivosFaltantes.push(label);
                $(`#${archivo}`).addClass('is-invalid');
            }
        });

        if (!todosCargados) {
            toastr.warning('Por favor carga todos los documentos requeridos: ' + archivosFaltantes.join(', '), 'Documentos faltantes', {
                "closeButton": true,
                "progressBar": false,
                "positionClass": "toast-top-center",
                "timeOut": "4000"
            });
            return false;
        }
    });
});
