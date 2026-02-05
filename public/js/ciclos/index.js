// public/js/ciclos/index.js

// Configuración CSRF para AJAX
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

$(document).ready(function () {
    // Inicializar DataTables
    var table = $('#ciclos-datatable').DataTable({
        processing: true,
        serverSide: false,
        ajax: {
            url: default_server + "/json/ciclos",
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
                data: 'fecha_inicio',
                render: function (data) {
                    return formatDate(data);
                }
            },
            {
                data: 'fecha_fin',
                render: function (data) {
                    return formatDate(data);
                }
            },
            {
                data: 'proximo_examen',
                render: function (data) {
                    if (data) {
                        return `<div class="text-center">
                            <span class="badge bg-info">${data.nombre}</span><br>
                            <small>${formatDate(data.fecha)}</small>
                        </div>`;
                    }
                    return '<span class="text-muted">Sin exámenes próximos</span>';
                }
            },
            {
                data: null,
                render: function (data) {
                    return `<div class="text-center">
                        <small>
                            Amonestación: <span class="badge bg-warning">${data.porcentaje_amonestacion}%</span>
                            (${data.limite_faltas_amonestacion} días)<br>
                            Inhabilitación: <span class="badge bg-danger">${data.porcentaje_inhabilitacion}%</span>
                            (${data.limite_faltas_inhabilitacion} días)
                        </small>
                    </div>`;
                }
            },
            {
                data: 'porcentaje_avance',
                render: function (data) {
                    let color = 'bg-info';
                    if (data >= 75) color = 'bg-warning';
                    if (data == 100) color = 'bg-success';

                    return `<div class="progress" style="height: 20px;">
                        <div class="progress-bar ${color}" role="progressbar"
                            style="width: ${data}%" aria-valuenow="${data}"
                            aria-valuemin="0" aria-valuemax="100">${data}%</div>
                    </div>`;
                }
            },
            {
                data: 'estado',
                render: function (data) {
                    let badgeClass = '';
                    let text = '';

                    switch (data) {
                        case 'planificado':
                            badgeClass = 'bg-secondary';
                            text = 'Planificado';
                            break;
                        case 'en_curso':
                            badgeClass = 'bg-primary';
                            text = 'En Curso';
                            break;
                        case 'finalizado':
                            badgeClass = 'bg-success';
                            text = 'Finalizado';
                            break;
                        case 'cancelado':
                            badgeClass = 'bg-danger';
                            text = 'Cancelado';
                            break;
                    }

                    return `<span class="badge ${badgeClass}">${text}</span>`;
                }
            },
            {
                data: 'es_activo',
                render: function (data) {
                    return data ?
                        '<span class="badge bg-success">Sí</span>' :
                        '<span class="badge bg-secondary">No</span>';
                }
            },
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

    // Función para formatear fechas
    function formatDate(dateString) {
        if (!dateString) return '';

        // Método 1: Dividir la fecha para evitar problemas de timezone
        if (dateString.includes('-')) {
            const [year, month, day] = dateString.split('-');
            // Crear la fecha usando los componentes locales
            const date = new Date(year, month - 1, day);
            const options = { year: 'numeric', month: '2-digit', day: '2-digit' };
            return date.toLocaleDateString('es-ES', options);
        }

        // Método 2: Si viene en otro formato
        const date = new Date(dateString + 'T00:00:00');
        const options = { year: 'numeric', month: '2-digit', day: '2-digit' };
        return date.toLocaleDateString('es-ES', options);
    }

    // Función alternativa para formatear fechas (más simple)
    function formatDateSimple(dateString) {
        if (!dateString) return '';
        const [year, month, day] = dateString.split('-');
        return `${day}/${month}/${year}`;
    }

    // Limpiar formulario cuando se cierra el modal
    $('#newCicloModal').on('hidden.bs.modal', function () {
        $('#newCicloForm')[0].reset();
        $('#newCicloForm .is-invalid').removeClass('is-invalid');
        $('#newCicloForm .invalid-feedback').remove();
        toastr.clear();
    });

    $('#editCicloModal').on('hidden.bs.modal', function () {
        $('#editCicloForm')[0].reset();
        $('#editCicloForm .is-invalid').removeClass('is-invalid');
        $('#editCicloForm .invalid-feedback').remove();
        toastr.clear();
    });

    // Validar fechas en tiempo real
    $('#fecha_inicio, #fecha_fin').on('change', function () {
        validateDates();
    });

    $('#edit_fecha_inicio, #edit_fecha_fin').on('change', function () {
        validateEditDates();
    });

    // Validar fechas de exámenes
    $('#fecha_primer_examen, #fecha_segundo_examen, #fecha_tercer_examen').on('change', function () {
        validateExamDates();
    });

    $('#edit_fecha_primer_examen, #edit_fecha_segundo_examen, #edit_fecha_tercer_examen').on('change', function () {
        validateEditExamDates();
    });

    function validateDates() {
        const fechaInicio = $('#fecha_inicio').val();
        const fechaFin = $('#fecha_fin').val();

        if (fechaInicio && fechaFin) {
            if (new Date(fechaInicio) >= new Date(fechaFin)) {
                $('#fecha_fin').addClass('is-invalid');
                if (!$('#fecha_fin').next('.invalid-feedback').length) {
                    $('#fecha_fin').after('<div class="invalid-feedback">La fecha de fin debe ser posterior a la fecha de inicio</div>');
                }
                return false;
            } else {
                $('#fecha_fin').removeClass('is-invalid').next('.invalid-feedback').remove();
            }
        }
        return true;
    }

    function validateEditDates() {
        const fechaInicio = $('#edit_fecha_inicio').val();
        const fechaFin = $('#edit_fecha_fin').val();

        if (fechaInicio && fechaFin) {
            if (new Date(fechaInicio) >= new Date(fechaFin)) {
                $('#edit_fecha_fin').addClass('is-invalid');
                if (!$('#edit_fecha_fin').next('.invalid-feedback').length) {
                    $('#edit_fecha_fin').after('<div class="invalid-feedback">La fecha de fin debe ser posterior a la fecha de inicio</div>');
                }
                return false;
            } else {
                $('#edit_fecha_fin').removeClass('is-invalid').next('.invalid-feedback').remove();
            }
        }
        return true;
    }

    function validateExamDates() {
        const fechaInicio = $('#fecha_inicio').val();
        const fechaPrimer = $('#fecha_primer_examen').val();
        const fechaSegundo = $('#fecha_segundo_examen').val();
        const fechaTercer = $('#fecha_tercer_examen').val();

        let valid = true;

        // Validar primer examen
        if (fechaPrimer && fechaInicio) {
            if (new Date(fechaPrimer) < new Date(fechaInicio)) {
                $('#fecha_primer_examen').addClass('is-invalid');
                if (!$('#fecha_primer_examen').next('.invalid-feedback').length) {
                    $('#fecha_primer_examen').after('<div class="invalid-feedback">Debe ser posterior al inicio del ciclo</div>');
                }
                valid = false;
            } else {
                $('#fecha_primer_examen').removeClass('is-invalid').next('.invalid-feedback').remove();
            }
        }

        // Validar segundo examen
        if (fechaSegundo && fechaPrimer) {
            if (new Date(fechaSegundo) <= new Date(fechaPrimer)) {
                $('#fecha_segundo_examen').addClass('is-invalid');
                if (!$('#fecha_segundo_examen').next('.invalid-feedback').length) {
                    $('#fecha_segundo_examen').after('<div class="invalid-feedback">Debe ser posterior al primer examen</div>');
                }
                valid = false;
            } else {
                $('#fecha_segundo_examen').removeClass('is-invalid').next('.invalid-feedback').remove();
            }
        }

        // Validar tercer examen
        if (fechaTercer && fechaSegundo) {
            if (new Date(fechaTercer) <= new Date(fechaSegundo)) {
                $('#fecha_tercer_examen').addClass('is-invalid');
                if (!$('#fecha_tercer_examen').next('.invalid-feedback').length) {
                    $('#fecha_tercer_examen').after('<div class="invalid-feedback">Debe ser posterior al segundo examen</div>');
                }
                valid = false;
            } else {
                $('#fecha_tercer_examen').removeClass('is-invalid').next('.invalid-feedback').remove();
            }
        }

        return valid;
    }

    function validateEditExamDates() {
        const fechaInicio = $('#edit_fecha_inicio').val();
        const fechaPrimer = $('#edit_fecha_primer_examen').val();
        const fechaSegundo = $('#edit_fecha_segundo_examen').val();
        const fechaTercer = $('#edit_fecha_tercer_examen').val();

        let valid = true;

        // Validar primer examen
        if (fechaPrimer && fechaInicio) {
            if (new Date(fechaPrimer) < new Date(fechaInicio)) {
                $('#edit_fecha_primer_examen').addClass('is-invalid');
                if (!$('#edit_fecha_primer_examen').next('.invalid-feedback').length) {
                    $('#edit_fecha_primer_examen').after('<div class="invalid-feedback">Debe ser posterior al inicio del ciclo</div>');
                }
                valid = false;
            } else {
                $('#edit_fecha_primer_examen').removeClass('is-invalid').next('.invalid-feedback').remove();
            }
        }

        // Validar segundo examen
        if (fechaSegundo && fechaPrimer) {
            if (new Date(fechaSegundo) <= new Date(fechaPrimer)) {
                $('#edit_fecha_segundo_examen').addClass('is-invalid');
                if (!$('#edit_fecha_segundo_examen').next('.invalid-feedback').length) {
                    $('#edit_fecha_segundo_examen').after('<div class="invalid-feedback">Debe ser posterior al primer examen</div>');
                }
                valid = false;
            } else {
                $('#edit_fecha_segundo_examen').removeClass('is-invalid').next('.invalid-feedback').remove();
            }
        }

        // Validar tercer examen
        if (fechaTercer && fechaSegundo) {
            if (new Date(fechaTercer) <= new Date(fechaSegundo)) {
                $('#edit_fecha_tercer_examen').addClass('is-invalid');
                if (!$('#edit_fecha_tercer_examen').next('.invalid-feedback').length) {
                    $('#edit_fecha_tercer_examen').after('<div class="invalid-feedback">Debe ser posterior al segundo examen</div>');
                }
                valid = false;
            } else {
                $('#edit_fecha_tercer_examen').removeClass('is-invalid').next('.invalid-feedback').remove();
            }
        }

        return valid;
    }

    // Crear nuevo ciclo
    $('#saveNewCiclo').on('click', function () {
        if (!validateDates()) {
            toastr.error('Por favor, corrija los errores en las fechas del ciclo');
            return;
        }

        if (!validateExamDates()) {
            toastr.error('Por favor, corrija los errores en las fechas de exámenes');
            return;
        }

        var formData = $('#newCicloForm').serialize();

        // Agregar explícitamente el valor del checkbox incluye_sabados
        var incluye_sabados = $('#incluye_sabados').is(':checked') ? 1 : 0;
        formData += '&incluye_sabados=' + incluye_sabados;

        $.ajax({
            url: default_server + "/json/ciclos",
            type: 'POST',
            data: formData,
            success: function (response) {
                if (response.success) {
                    $('#newCicloModal').modal('hide');
                    table.ajax.reload();
                    toastr.success(response.message);
                }
            },
            error: function (xhr) {
                if (xhr.status === 422) {
                    $('.is-invalid').removeClass('is-invalid');
                    $('.invalid-feedback').remove();

                    var errors = xhr.responseJSON.errors;
                    var errorSummary = '<ul>';

                    for (var field in errors) {
                        var message = errors[field][0];
                        $('#' + field).addClass('is-invalid');
                        $('#' + field).after('<div class="invalid-feedback">' + message + '</div>');
                        errorSummary += '<li>' + message + '</li>';
                    }

                    errorSummary += '</ul>';
                    toastr.error(errorSummary, 'Error de validación', {
                        closeButton: true,
                        timeOut: 0,
                        extendedTimeOut: 0,
                        enableHtml: true
                    });
                } else {
                    toastr.error('Error al crear el ciclo académico');
                }
            }
        });
    });

    // Cargar datos para editar
    $('#ciclos-datatable').on('click', '.edit-ciclo', function () {
        var id = $(this).data('id');

        $.ajax({
            url: default_server + "/json/ciclos/" + id,
            type: 'GET',
            success: function (response) {
                if (response.success) {
                    var ciclo = response.data;

                    console.log('Datos del ciclo:', ciclo); // Para debug

                    $('#edit_ciclo_id').val(ciclo.id);
                    $('#edit_codigo').val(ciclo.codigo);
                    $('#edit_nombre').val(ciclo.nombre);
                    $('#edit_descripcion').val(ciclo.descripcion || '');
                    $('#edit_fecha_inicio').val(ciclo.fecha_inicio);
                    $('#edit_fecha_fin').val(ciclo.fecha_fin);
                    $('#edit_porcentaje_amonestacion').val(ciclo.porcentaje_amonestacion || 20);
                    $('#edit_porcentaje_inhabilitacion').val(ciclo.porcentaje_inhabilitacion || 30);
                    $('#edit_fecha_primer_examen').val(ciclo.fecha_primer_examen || '');
                    $('#edit_fecha_segundo_examen').val(ciclo.fecha_segundo_examen || '');
                    $('#edit_fecha_tercer_examen').val(ciclo.fecha_tercer_examen || '');
                    $('#edit_estado').val(ciclo.estado);
                    $('#edit_correlativo_inicial').val(ciclo.correlativo_inicial || 1);
                    $('#edit_porcentaje').val(ciclo.porcentaje_avance || 0);

                    // Manejar checkbox de rotación de sábados
                    $('#edit_incluye_sabados').prop('checked', ciclo.incluye_sabados || false);

                    $('#editCicloModal').modal('show');
                } else {
                    toastr.error('No se pudo cargar la información del ciclo');
                }
            },
            error: function () {
                toastr.error('Error al obtener los datos del ciclo');
            }
        });
    });

    // Actualizar ciclo
    $('#updateCiclo').on('click', function () {
        if (!validateEditDates()) {
            toastr.error('Por favor, corrija los errores en las fechas del ciclo');
            return;
        }

        if (!validateEditExamDates()) {
            toastr.error('Por favor, corrija los errores en las fechas de exámenes');
            return;
        }

        var id = $('#edit_ciclo_id').val();
        var formData = $('#editCicloForm').serialize();

        // Agregar explícitamente el valor del checkbox incluye_sabados
        var incluye_sabados = $('#edit_incluye_sabados').is(':checked') ? 1 : 0;
        formData += '&incluye_sabados=' + incluye_sabados;

        $.ajax({
            url: default_server + "/json/ciclos/" + id,
            type: 'PUT',
            data: formData,
            success: function (response) {
                if (response.success) {
                    $('#editCicloModal').modal('hide');
                    table.ajax.reload();
                    toastr.success(response.message);
                }
            },
            error: function (xhr) {
                if (xhr.status === 422) {
                    $('.is-invalid').removeClass('is-invalid');
                    $('.invalid-feedback').remove();

                    var errors = xhr.responseJSON.errors;
                    var errorSummary = '<ul>';

                    for (var field in errors) {
                        var message = errors[field][0];
                        var inputField = 'edit_' + field;
                        $('#' + inputField).addClass('is-invalid');
                        $('#' + inputField).after('<div class="invalid-feedback">' + message + '</div>');
                        errorSummary += '<li>' + message + '</li>';
                    }

                    errorSummary += '</ul>';
                    toastr.error(errorSummary, 'Error de validación', {
                        closeButton: true,
                        timeOut: 0,
                        extendedTimeOut: 0,
                        enableHtml: true
                    });
                } else {
                    toastr.error('Error al actualizar el ciclo académico');
                }
            }
        });
    });

    // Activar ciclo
    $('#ciclos-datatable').on('click', '.activate-ciclo', function () {
        var id = $(this).data('id');

        if (confirm('¿Está seguro de activar este ciclo?')) {
            $.ajax({
                url: default_server + "/json/ciclos/" + id + "/activar",
                type: 'POST',
                success: function (response) {
                    if (response.success) {
                        table.ajax.reload();
                        toastr.success(response.message);
                    }
                },
                error: function () {
                    toastr.error('Error al activar el ciclo académico');
                }
            });
        }
    });

    // Desactivar ciclo
    $('#ciclos-datatable').on('click', '.deactivate-ciclo', function () {
        var id = $(this).data('id');

        if (confirm('¿Está seguro de desactivar este ciclo?')) {
            $.ajax({
                url: default_server + "/json/ciclos/" + id + "/desactivar",
                type: 'POST',
                success: function (response) {
                    if (response.success) {
                        table.ajax.reload();
                        toastr.success(response.message);
                    }
                },
                error: function () {
                    toastr.error('Error al desactivar el ciclo académico');
                }
            });
        }
    });

    // Eliminar ciclo
    $('#ciclos-datatable').on('click', '.delete-ciclo', function () {
        var id = $(this).data('id');

        if (confirm('¿Está seguro de eliminar este ciclo académico?')) {
            $.ajax({
                url: default_server + "/json/ciclos/" + id,
                type: 'DELETE',
                success: function (response) {
                    if (response.success) {
                        table.ajax.reload();
                        toastr.success(response.message);
                    }
                },
                error: function (xhr) {
                    if (xhr.status === 400) {
                        toastr.error(xhr.responseJSON.message);
                    } else {
                        toastr.error('Error al eliminar el ciclo académico');
                    }
                }
            });
        }
    });

    // ============= GESTIÓN DE VACANTES =============
    let cicloActualId = null;
    let vacantesData = [];
    let vacantesModificadas = [];

    // Guardar y Configurar Vacantes (nuevo ciclo)
    $('#saveAndConfigVacantes').on('click', function () {
        if (!validateDates() || !validateExamDates()) {
            toastr.error('Por favor, corrija los errores antes de continuar');
            return;
        }

        var formData = $('#newCicloForm').serialize();

        $.ajax({
            url: default_server + "/json/ciclos",
            type: 'POST',
            data: formData,
            success: function (response) {
                if (response.success) {
                    $('#newCicloModal').modal('hide');
                    table.ajax.reload();
                    toastr.success('Ciclo creado exitosamente');

                    // Abrir modal de vacantes
                    cicloActualId = response.data.id;
                    $('#vacantes-ciclo-nombre').text(response.data.nombre);
                    cargarVacantes(cicloActualId);
                    $('#vacantesModal').modal('show');
                }
            },
            error: function (xhr) {
                handleFormErrors(xhr);
            }
        });
    });

    // Configurar Vacantes desde edición
    $('#configVacantesBtn').on('click', function () {
        cicloActualId = $('#edit_ciclo_id').val();
        const nombreCiclo = $('#edit_nombre').val();

        $('#vacantes-ciclo-nombre').text(nombreCiclo);
        $('#editCicloModal').modal('hide');
        cargarVacantes(cicloActualId);
        $('#vacantesModal').modal('show');
    });

    // Cargar vacantes del ciclo
    function cargarVacantes(cicloId) {
        $.ajax({
            url: default_server + `/json/ciclos/${cicloId}/vacantes`,
            type: 'GET',
            success: function (response) {
                if (response.success) {
                    vacantesData = response.vacantes;
                    vacantesModificadas = [];

                    // Actualizar resumen
                    $('#total-carreras').text(response.resumen.total_carreras);
                    $('#total-vacantes').text(response.resumen.total_vacantes);
                    $('#vacantes-ocupadas').text(response.resumen.vacantes_ocupadas);
                    $('#vacantes-disponibles').text(response.resumen.vacantes_disponibles);

                    // Renderizar tabla con todas las carreras
                    renderizarTablaVacantes();
                }
            },
            error: function () {
                toastr.error('Error al cargar las vacantes');
            }
        });
    }

    // Renderizar tabla de vacantes
    function renderizarTablaVacantes() {
        const tbody = $('#vacantesTableBody');
        tbody.empty();

        if (vacantesData.length === 0) {
            $('#tablaVacantes').hide();
            $('#noVacantesMessage').show();
        } else {
            $('#tablaVacantes').show();
            $('#noVacantesMessage').hide();

            vacantesData.forEach(function (vacante) {
                const estadoClass = getEstadoClass(vacante.estado_vacantes);
                const estadoBadge = vacante.estado ?
                    '<span class="badge bg-success">Activo</span>' :
                    '<span class="badge bg-secondary">Inactivo</span>';

                // Determinar si mostrar porcentaje
                let porcentajeHtml = '';
                if (vacante.vacantes_total > 0) {
                    porcentajeHtml = `
                        <div class="progress" style="height: 20px;">
                            <div class="progress-bar ${getProgressClass(vacante.porcentaje_ocupacion)}" 
                                 role="progressbar" style="width: ${vacante.porcentaje_ocupacion}%">
                                ${vacante.porcentaje_ocupacion}%
                            </div>
                        </div>
                    `;
                } else {
                    porcentajeHtml = '<span class="text-muted">Sin límite</span>';
                }

                const row = `
                    <tr data-id="${vacante.id || ''}" data-carrera-id="${vacante.carrera_id}">
                        <td>${vacante.carrera_nombre}</td>
                        <td><span class="badge bg-secondary">${vacante.carrera_codigo}</span></td>
                        <td class="text-center">
                            <input type="number" class="form-control form-control-sm text-center vacante-total" 
                                   value="${vacante.vacantes_total}" min="0" style="width: 80px;">
                        </td>
                        <td class="text-center">${vacante.vacantes_ocupadas}</td>
                        <td class="text-center">
                            ${vacante.vacantes_total > 0 ?
                        `<span class="${estadoClass}">${vacante.vacantes_disponibles}</span>` :
                        '<span class="text-muted">-</span>'}
                        </td>
                        <td class="text-center">
                            ${porcentajeHtml}
                        </td>
                        <td>
                            <input type="text" class="form-control form-control-sm vacante-obs" 
                                   value="${vacante.observaciones || ''}" placeholder="Observaciones...">
                        </td>
                        <td class="text-center">${estadoBadge}</td>
                    </tr>
                `;
                tbody.append(row);
            });
        }
    }

    // Obtener clase de estado
    function getEstadoClass(estado) {
        switch (estado) {
            case 'agotado': return 'badge bg-danger';
            case 'pocas': return 'badge bg-warning';
            case 'limitadas': return 'badge bg-info';
            case 'sin-configurar': return 'badge bg-secondary';
            case 'sin-limite': return 'badge bg-light text-dark';
            default: return 'badge bg-success';
        }
    }

    // Obtener clase de progreso
    function getProgressClass(porcentaje) {
        if (porcentaje >= 90) return 'bg-danger';
        if (porcentaje >= 70) return 'bg-warning';
        if (porcentaje >= 50) return 'bg-info';
        return 'bg-success';
    }


    // Detectar cambios en la tabla
    $(document).on('change', '.vacante-total, .vacante-obs', function () {
        const row = $(this).closest('tr');
        const carreraId = row.data('carrera-id');

        if (!vacantesModificadas.includes(carreraId)) {
            vacantesModificadas.push(carreraId);
            row.addClass('table-warning');
        }
    });

    // Guardar todos los cambios
    $('#guardarTodosVacantes').on('click', function () {
        const vacantesActualizar = [];

        // Recopilar datos de todas las filas
        $('#vacantesTableBody tr').each(function () {
            const row = $(this);
            const carreraId = row.data('carrera-id');
            const vacantesTotal = parseInt(row.find('.vacante-total').val()) || 0;
            const observaciones = row.find('.vacante-obs').val() || '';

            vacantesActualizar.push({
                carrera_id: carreraId,
                vacantes_total: vacantesTotal,
                observaciones: observaciones
            });
        });

        if (vacantesActualizar.length === 0) {
            toastr.warning('No hay carreras para configurar');
            return;
        }

        $.ajax({
            url: default_server + `/json/ciclos/${cicloActualId}/vacantes`,
            type: 'POST',
            data: { vacantes: vacantesActualizar },
            success: function (response) {
                if (response.success) {
                    toastr.success('Vacantes guardadas exitosamente');
                    vacantesModificadas = [];
                    cargarVacantes(cicloActualId);
                }
            },
            error: function () {
                toastr.error('Error al guardar las vacantes');
            }
        });
    });


    // Función auxiliar para manejar errores de formulario
    function handleFormErrors(xhr) {
        if (xhr.status === 422) {
            $('.is-invalid').removeClass('is-invalid');
            $('.invalid-feedback').remove();

            var errors = xhr.responseJSON.errors;
            var errorSummary = '<ul>';

            for (var field in errors) {
                var message = errors[field][0];
                $('#' + field).addClass('is-invalid');
                $('#' + field).after('<div class="invalid-feedback">' + message + '</div>');
                errorSummary += '<li>' + message + '</li>';
            }

            errorSummary += '</ul>';
            toastr.error(errorSummary, 'Error de validación', {
                closeButton: true,
                timeOut: 0,
                extendedTimeOut: 0,
                enableHtml: true
            });
        } else {
            toastr.error('Error al procesar la solicitud');
        }
    }
});
