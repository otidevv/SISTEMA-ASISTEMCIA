// public/js/postulaciones/index.js

// Configuración CSRF para AJAX
// Configuración Global de Toasts con SweetAlert2
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

$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

let table;
let currentPostulacionId = null;

$(document).ready(function () {
    console.log('Postulaciones JS cargado');

    // Inicializar Select2 para filtros
    initFilterSelects();

    // Inicializar DataTables
    initDataTable();

    // Cargar estadísticas iniciales
    loadStatistics();

    // Configurar eventos
    setupEventHandlers();
});

function initFilterSelects() {
    // No Select2 initialization needed - using plain selects like Estado filter
}

function initDataTable() {
    table = $('#postulaciones-datatable').DataTable({
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Todos"]],
        processing: true,
        serverSide: true, // Habilitar server-side processing
        destroy: true, // Permite reinicializar la tabla y cancelar solicitudes Ajax pendientes
        ajax: {
            url: "/json/postulaciones",
            type: 'GET',
            data: function (d) {
                d.ciclo_id = $('#filter-ciclo').val();
                d.estado = $('#filter-estado').val();
                d.carrera_id = $('#filter-carrera').val();
            },
            error: function (xhr, error, code) {
                // Ignorar errores de solicitudes abortadas (ocurre cuando se actualiza rápidamente)
                if (xhr.statusText === 'abort') {
                    console.log('Solicitud Ajax anterior cancelada (normal al actualizar rápido)');
                    return;
                }
                console.error('Error Ajax en DataTables:', error, code);
                console.error('Respuesta del servidor:', xhr.responseText);

                // Solo mostrar error si no es un abort
                if (error !== 'abort') {
                    Toast.fire({ icon: 'error', title: 'Error al cargar las postulaciones. Por favor, intente nuevamente.' });
                }
            }
        },
        columns: [
            { data: 'codigo_postulante', name: 'codigo_postulante' },
            { data: 'estudiante_nombre', name: 'estudiante.nombre' },
            { data: 'dni', name: 'estudiante.numero_documento' },
            { data: 'carrera_nombre', name: 'carrera.nombre' },
            { data: 'turno_nombre', name: 'turno.nombre' },
            {
                data: 'tipo_inscripcion',
                name: 'tipo_inscripcion',
                render: function (data) {
                    if (!data) return '<span class="badge bg-secondary">N/A</span>';
                    const tipo = data.toString().toLowerCase().trim();
                    return tipo === 'postulante' ?
                        '<span class="badge bg-primary">Postulante</span>' :
                        '<span class="badge bg-info">Reforzamiento</span>';
                }
            },
            { data: 'fecha_postulacion', name: 'fecha_postulacion' },
            {
                data: 'estado',
                name: 'estado',
                render: function (data) {
                    const estadoLower = data ? data.toLowerCase() : 'pendiente';
                    let badgeClass = 'badge-estado-' + estadoLower;
                    return '<span class="badge ' + badgeClass + '">' + data.toUpperCase() + '</span>';
                }
            },
            {
                data: null,
                name: 'verificacion',
                orderable: false,
                searchable: false,
                render: function (data) {
                    let html = '<div class="d-flex gap-1">';
                    let docIcon = data.documentos_verificados ?
                        '<i class="uil uil-check-circle text-success"></i>' :
                        '<i class="uil uil-times-circle text-danger"></i>';
                    html += '<span title="Documentos">' + docIcon + '</span>';
                    let payIcon = data.pago_verificado ?
                        '<i class="uil uil-money-bill text-success"></i>' :
                        '<i class="uil uil-money-bill-slash text-danger"></i>';
                    html += '<span title="Pago">' + payIcon + '</span>';
                    html += '</div>';
                    return html;
                }
            },
            {
                data: 'constancia_estado_html',
                name: 'constancia',
                orderable: false,
                searchable: false
            },
            {
                data: 'actions',
                name: 'actions',
                orderable: false,
                searchable: false
            }
        ],
        language: {
            lengthMenu: "Mostrar _MENU_ registros por página",
            zeroRecords: "No se encontraron postulaciones",
            info: "Mostrando _START_ a _END_ de _TOTAL_ postulaciones",
            infoEmpty: "Mostrando 0 a 0 de 0 postulaciones",
            infoFiltered: "(filtrado de _MAX_ postulaciones totales)",
            search: "Buscar:",
            paginate: {
                first: "Primero",
                last: "Último",
                previous: "<i class='uil uil-angle-left'>",
                next: "<i class='uil uil-angle-right'>"
            },
            processing: "Procesando...",
            emptyTable: "No hay postulaciones disponibles",
            loadingRecords: "Cargando..."
        },
        rowCallback: function (row, data) {
            // Aplicar clase según el estado de la postulación
            const estado = data.estado ? data.estado.toLowerCase() : '';

            // Remover clases previas de estado
            $(row).removeClass('estado-pendiente estado-aprobado estado-rechazado estado-observado');

            // Agregar clase según el estado actual
            if (estado === 'pendiente') {
                $(row).addClass('estado-pendiente');
            } else if (estado === 'aprobado') {
                $(row).addClass('estado-aprobado');
            } else if (estado === 'rechazado') {
                $(row).addClass('estado-rechazado');
            } else if (estado === 'observado') {
                $(row).addClass('estado-observado');
            }
        },
        drawCallback: function () {
            $('.dataTables_paginate > .pagination').addClass('pagination-rounded');
            loadStatistics();
        }
    });
}

function setupEventHandlers() {
    // Filtrar
    $('#btn-filtrar').on('click', function () {
        table.ajax.reload();
    });

    // Ver detalle
    $(document).on('click', '.view-postulacion', function () {
        const id = $(this).data('id');
        viewPostulacion(id);
    });

    // Verificar documentos
    $(document).on('click', '.verify-docs', function () {
        const id = $(this).data('id');
        const verified = $(this).data('verified') == 1 ? false : true;
        verifyDocuments(id, verified);
    });

    // Verificar pago
    $(document).on('click', '.verify-payment', function () {
        const id = $(this).data('id');
        const verified = $(this).data('verified') == 1 ? false : true;
        verifyPayment(id, verified);
    });

    // Observar
    $(document).on('click', '.observe-postulacion', function () {
        const id = $(this).data('id');
        currentPostulacionId = id;
        $('#observe-id').val(id);
        $('#observeModal').modal('show');
    });

    // Rechazar
    $(document).on('click', '.reject-postulacion', function () {
        const id = $(this).data('id');
        currentPostulacionId = id;
        $('#reject-id').val(id);
        $('#rejectModal').modal('show');
    });

    // Eliminar
    $(document).on('click', '.delete-postulacion', function () {
        const id = $(this).data('id');
        currentPostulacionId = id;
        $('#delete-id').val(id);
        $('#deleteModal').modal('show');
    });

    // Aprobar postulación
    $(document).on('click', '.approve-postulacion', function () {
        const id = $(this).data('id');
        const btn = $(this);

        // Confirmar la acción
        if (!confirm('¿Está seguro de aprobar esta postulación? Se creará una inscripción y se asignará un aula automáticamente.')) {
            return;
        }

        // Deshabilitar el botón mientras se procesa
        btn.prop('disabled', true);
        btn.html('<i class="spinner-border spinner-border-sm"></i> Procesando...');

        $.ajax({
            url: "/json/postulaciones/" + id + "/aprobar",
            type: 'POST',
            success: function (response) {
                if (response.success) {
                    Toast.fire({ icon: 'success', title: response.message });

                    // Si hay información adicional, mostrarla
                    if (response.data) {
                        Toast.fire({
                            icon: 'info',
                            title: 'Detalles de la inscripción',
                            html: 'Código de inscripción: ' + response.data.codigo_inscripcion +
                                '<br>Aula asignada: ' + response.data.aula +
                                '<br>Capacidad disponible restante: ' + response.data.aula_capacidad_disponible,
                            timer: 8000
                        });
                    }

                    // Recargar la tabla
                    table.ajax.reload();
                } else {
                    Toast.fire({ icon: 'error', title: response.message || 'Error al aprobar la postulación' });
                    // Restaurar el botón
                    btn.prop('disabled', false);
                    btn.html('<i class="uil uil-check-circle"></i>');
                }
            },
            error: function (xhr) {
                let errorMsg = 'Error al aprobar la postulación';

                if (xhr.status === 400 || xhr.status === 403) {
                    const response = xhr.responseJSON;
                    errorMsg = response.message || response.error || errorMsg;
                } else if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    for (let key in errors) {
                        Toast.fire({ icon: 'error', title: errors[key][0] });
                    }
                    // Restaurar el botón
                    btn.prop('disabled', false);
                    btn.html('<i class="uil uil-check-circle"></i>');
                    return;
                }

                Toast.fire({ icon: 'error', title: errorMsg });
                // Restaurar el botón
                btn.prop('disabled', false);
                btn.html('<i class="uil uil-check-circle"></i>');
            }
        });
    });

    // Generar constancia
    $(document).on('click', '.generate-constancia', function () {
        const id = $(this).data('id');
        const btn = $(this);

        const newWindow = window.open('/postulacion/constancia/generar/' + id, '_blank');
        if (!newWindow || newWindow.closed || typeof newWindow.closed == 'undefined') {
            Toast.fire({ icon: 'warning', title: 'Aviso de Pop-up', text: 'El navegador bloqueó la ventana emergente. Por favor, permita las ventanas emergentes para este sitio.' });
            return;
        }

        // Para evitar que el usuario pierda la fila, actualizamos la UI manualmente.
        // La recarga completa se hará cuando suba el archivo firmado.
        Toast.fire({ icon: 'success', title: 'Constancia generada. La fila se actualizará para permitir la subida del documento firmado.' });

        // Actualizar la data de la fila en DataTables
        const row = table.row(btn.closest('tr'));
        const rowData = row.data();
        rowData.constancia_generada = true;

        // Invalidar la fila para que DataTables la redibuje con los nuevos datos
        row.invalidate().draw(false);
    });

    // Ver constancia firmada
    $(document).on('click', '.view-constancia-firmada', function () {
        const id = $(this).data('id');
        window.open('/postulacion/constancia/ver/' + id, '_blank');
    });

    // Confirmar rechazo
    $('#confirmReject').on('click', function () {
        const motivo = $('#reject-motivo').val();
        if (motivo.length < 10) {
            Toast.fire({ icon: 'error', title: 'El motivo debe tener al menos 10 caracteres' });
            return;
        }
        rejectPostulacion(currentPostulacionId, motivo);
    });

    // Confirmar observación
    $('#confirmObserve').on('click', function () {
        const observaciones = $('#observe-observaciones').val();
        if (observaciones.length < 10) {
            Toast.fire({ icon: 'error', title: 'Las observaciones deben tener al menos 10 caracteres' });
            return;
        }
        observePostulacion(currentPostulacionId, observaciones);
    });

    // Confirmar eliminación
    $('#confirmDelete').on('click', function () {
        deletePostulacion(currentPostulacionId);
    });

    // Editar documentos
    $(document).on('click', '.edit-documents', function () {
        const id = $(this).data('id');
        currentPostulacionId = id;
        $('#edit-docs-postulacion-id').val(id);
        loadDocumentsForEdit(id);
        $('#editDocumentsModal').modal('show');
    });

    // Guardar cambios de documentos
    $('#saveDocumentChanges').on('click', function () {
        saveDocumentChanges();
    });

    // Editar postulación aprobada
    $(document).on('click', '.edit-approved', function () {
        const id = $(this).data('id');
        currentPostulacionId = id;
        $('#edit-approved-id').val(id);
        loadApprovedPostulationForEdit(id);
        $('#editApprovedModal').modal('show');
    });

    // Guardar cambios de postulación aprobada
    $('#saveApprovedChanges').on('click', function () {
        saveApprovedPostulationChanges();
    });

    // Evento de cambio de carrera para cargar turnos
    $('#edit-approved-carrera').on('change', function () {
        const carreraId = $(this).val();
        const cicloId = $('#edit-approved-ciclo').val();

        if (carreraId && cicloId) {
            // IMPORTANTE: Capturar valores actuales ANTES de limpiar
            const currentTurnoId = $('#edit-approved-turno').val();
            const currentAulaId = $('#edit-approved-aula').val();

            // Cargar turnos preservando la selección actual si es compatible
            loadTurnosForCarrera(carreraId, cicloId, currentTurnoId, false);

            // Si había un turno seleccionado, intentar cargar las aulas preservando la selección
            if (currentTurnoId) {
                setTimeout(function () {
                    loadAulasForTurno(currentTurnoId, carreraId, cicloId, currentAulaId);
                }, 500);
            } else {
                // Si no había turno seleccionado, limpiar aulas
                $('#edit-approved-aula').html('<option value="">Primero seleccione un turno</option>');
            }
        }
    });

    // Evento de cambio de turno para cargar aulas
    $('#edit-approved-turno').on('change', function () {
        const turnoId = $(this).val();
        const carreraId = $('#edit-approved-carrera').val();
        const cicloId = $('#edit-approved-ciclo').val();
        if (turnoId && carreraId && cicloId) {
            // Al cambiar turno, cargar aulas disponibles
            loadAulasForTurno(turnoId, carreraId, cicloId);
        } else {
            $('#edit-approved-aula').html('<option value="">Primero seleccione un turno</option>');
        }
    });

    // Limpiar formularios al cerrar modales
    $('.modal').on('hidden.bs.modal', function () {
        $(this).find('form')[0]?.reset();
        currentPostulacionId = null;
    });

    // Subir constancia (admin)
    $(document).on('click', '.upload-constancia-admin', function () {
        const id = $(this).data('id');
        currentPostulacionId = id;
        $('#postulacion-id-admin-upload').val(id);
        $('#uploadConstanciaAdminModal').modal('show');
    });

    // Confirmar subida de constancia (admin)
    $('#confirmUploadConstanciaAdmin').on('click', function () {
        const form = $('#uploadConstanciaAdminForm')[0];
        const formData = new FormData(form);
        const postulacionId = $('#postulacion-id-admin-upload').val();

        if (!formData.get('documento_constancia_admin').name) {
            Toast.fire({ icon: 'error', title: 'Debe seleccionar un archivo' });
            return;
        }

        $.ajax({
            url: "/postulacion/constancia/subir-admin/" + postulacionId,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                if (response.success) {
                    Toast.fire({ icon: 'success', title: response.message });
                    $('#uploadConstanciaAdminModal').modal('hide');
                    table.ajax.reload();
                } else {
                    Toast.fire({ icon: 'error', title: response.message || 'Error al subir la constancia' });
                }
            },
            error: function (xhr) {
                const errorMsg = xhr.responseJSON?.message || 'Error al subir la constancia';
                Toast.fire({ icon: 'error', title: errorMsg });
            }
        });
    });
}

function viewPostulacion(id) {
    $.ajax({
        url: "/json/postulaciones/" + id,
        type: 'GET',
        success: function (response) {
            if (response.success) {
                const data = response.data;
                const postulacion = data.postulacion;
                const documentos = data.documentos;
                const inscripcion = data.inscripcion;

                let html = '<div class="postulacion-detail-container px-1">';

                // 1. INFORMACIÓN DEL ESTUDIANTE + FOTO (MÁS COMPACTO)
                html += '<div class="detail-section shadow-sm">';
                html += '<h4 class="detail-section-title"><i class="bi bi-person-badge me-2"></i> Estudiante</h4>';
                html += '<div class="row g-2 align-items-center">';
                html += '<div class="col-md-2 text-center">';
                const fotoPerfilUrl = postulacion.foto_path ? '/storage/' + postulacion.foto_path : null;
                if (fotoPerfilUrl) {
                    html += '<img src="' + fotoPerfilUrl + '" class="rounded-lg shadow-sm border border-2 border-primary" style="width: 100px; height: 100px; object-fit: cover;" alt="Foto">';
                } else {
                    html += '<div class="bg-light rounded-lg d-flex align-items-center justify-content-center mx-auto" style="width: 100px; height: 100px; border: 1px dashed #ccc;"><i class="bi bi-person text-muted" style="font-size: 2rem;"></i> </div>';
                }
                html += '</div>';
                html += '<div class="col-md-10">';
                html += '<div class="detail-grid">';
                html += '<div class="detail-item"><strong>Nombre Completo</strong><span>' + postulacion.estudiante.nombre + ' ' + postulacion.estudiante.apellido_paterno + ' ' + postulacion.estudiante.apellido_materno + '</span></div>';
                html += '<div class="detail-item"><strong>DNI / Documento</strong><span>' + (postulacion.estudiante.numero_documento || 'N/A') + '</span></div>';
                html += '<div class="detail-item"><strong>Email</strong><span class="small">' + (postulacion.estudiante.email || 'N/A') + '</span></div>';
                html += '<div class="detail-item"><strong>Teléfono</strong><span>' + (postulacion.estudiante.telefono || 'N/A') + '</span></div>';
                html += '<div class="detail-item"><strong>Nacimiento</strong><span>' + (postulacion.estudiante.fecha_nacimiento ? new Date(postulacion.estudiante.fecha_nacimiento).toLocaleDateString() : 'N/A') + '</span></div>';
                html += '<div class="detail-item"><strong>Género</strong><span>' + (postulacion.estudiante.genero ? (postulacion.estudiante.genero === 'M' ? 'Masculino' : 'Femenino') : 'N/A') + '</span></div>';
                html += '</div></div></div></div>';

                // 2. FILA DE PADRES Y POSTULACION (DOS COLUMNAS)
                html += '<div class="row g-2">';
                html += '<div class="col-md-6">';
                html += '<div class="detail-section h-100 shadow-sm">';
                html += '<h4 class="detail-section-title"><i class="bi bi-people me-2"></i> Padres</h4>';
                if (data.padre || data.madre) {
                    html += '<div class="detail-item mb-1"><strong>Padre</strong><span>' + (data.padre ? data.padre.nombre + ' ' + data.padre.apellido_paterno + ' (' + (data.padre.telefono || 'Sin telf') + ')' : 'No registrado') + '</span></div>';
                    html += '<div class="detail-item"><strong>Madre</strong><span>' + (data.madre ? data.madre.nombre + ' ' + data.madre.apellido_paterno + ' (' + (data.madre.telefono || 'Sin telf') + ')' : 'No registrado') + '</span></div>';
                } else {
                    html += '<p class="text-muted extra-small py-2">Sin info de padres registrados.</p>';
                }
                html += '</div></div>';

                html += '<div class="col-md-6">';
                html += '<div class="detail-section h-100 shadow-sm">';
                html += '<h4 class="detail-section-title"><i class="bi bi-mortarboard me-2"></i> Postulación</h4>';
                html += '<div class="detail-grid" style="grid-template-columns: repeat(2, 1fr);">';
                html += '<div class="detail-item"><strong>Código</strong><span class="text-magenta font-weight-bold">' + postulacion.codigo_postulante + '</span></div>';
                html += '<div class="detail-item"><strong>Estado</strong><span class="badge badge-estado-' + postulacion.estado + ' extra-small">' + postulacion.estado.toUpperCase() + '</span></div>';
                html += '<div class="detail-item col-span-2"><strong>Carrera / Ciclo</strong><span>' + postulacion.carrera.nombre + ' (' + postulacion.ciclo.nombre + ')</span></div>';
                html += '<div class="detail-item"><strong>Turno</strong><span>' + postulacion.turno.nombre + '</span></div>';
                if (inscripcion && inscripcion.aula) {
                    html += '<div class="detail-item"><strong>Aula</strong><span class="text-success font-weight-bold">' + inscripcion.aula.nombre + '</span></div>';
                }
                html += '</div></div></div></div>';

                // 3. DOCUMENTOS Y PAGO (MÁS COMPACTO)
                html += '<div class="row g-2 mt-2">';
                html += '<div class="col-md-6">';
                html += '<div class="detail-section h-100 shadow-sm">';
                html += '<div class="d-flex justify-content-between align-items-center mb-2 border-bottom pb-1">';
                html += '<h4 class="detail-section-title mb-0 border-0"><i class="bi bi-file-earmark-text me-2"></i> Documentos</h4>';
                html += '<button class="btn btn-xs btn-outline-primary edit-documents py-0" data-id="' + postulacion.id + '">Editar</button>';
                html += '</div>';
                html += '<div class="list-group list-group-flush bg-transparent">';
                for (let key in documentos) {
                    const doc = documentos[key];
                    if (doc.existe) {
                        html += '<div class="list-group-item bg-transparent d-flex justify-content-between align-items-center py-1 px-0 border-0">';
                        html += '<div><i class="bi bi-check-circle-fill text-success me-1 small"></i><span class="extra-small">' + doc.nombre + '</span></div>';
                        html += '<a href="' + doc.url + '" target="_blank" class="btn btn-xs btn-magenta py-0 px-2" style="font-size: 9px;">VER</a>';
                        html += '</div>';
                    }
                }
                html += '</div></div></div>';

                html += '<div class="col-md-6">';
                html += '<div class="detail-section h-100 shadow-sm">';
                html += '<h4 class="detail-section-title"><i class="bi bi-cash me-2"></i> Pagos</h4>';
                if (postulacion.numero_recibo) {
                    html += '<div class="detail-grid" style="grid-template-columns: repeat(2, 1fr);">';
                    html += '<div class="detail-item"><strong>Recibo / Fecha</strong><span>' + postulacion.numero_recibo + ' (' + (postulacion.fecha_emision_voucher ? new Date(postulacion.fecha_emision_voucher).toLocaleDateString() : 'N/A') + ')</span></div>';
                    html += '<div class="detail-item"><strong>Matrícula</strong><span>S/. ' + postulacion.monto_matricula + '</span></div>';
                    html += '<div class="detail-item col-md-12"><div class="alert alert-info py-1 mb-0 mt-1 extra-small text-center"><strong>Total:</strong> S/. ' + postulacion.monto_total_pagado + '</div></div>';
                    html += '</div>';
                } else {
                    html += '<p class="text-muted extra-small">Sin pagos registrados.</p>';
                }
                html += '</div></div></div>';

                // 4. OBSERVACIONES (SI HAY)
                if (postulacion.observaciones || postulacion.motivo_rechazo) {
                    const tint = postulacion.observaciones ? 'warning' : 'danger';
                    const text = postulacion.observaciones || postulacion.motivo_rechazo;
                    html += '<div class="alert alert-' + tint + ' p-2 mt-2 extra-small border-0 shadow-sm">';
                    html += '<strong><i class="bi bi-exclamation-triangle"></i> NOTA:</strong> ' + text;
                    html += '</div>';
                }

                html += '</div>';

                $('#viewModalBody').html(html);
                $('#viewModal').modal('show');
            }
        },
        error: function (xhr) {
            Toast.fire({ icon: 'error', title: 'Error al cargar el detalle de la postulación' });
        }
    });
}

function verifyDocuments(id, verified) {
    $.ajax({
        url: "/json/postulaciones/" + id + "/verificar-documentos",
        type: 'POST',
        data: { verificado: verified },
        success: function (response) {
            if (response.success) {
                Toast.fire({ icon: 'success', title: response.message });
                table.ajax.reload();
            }
        },
        error: function (xhr) {
            Toast.fire({ icon: 'error', title: 'Error al verificar documentos' });
        }
    });
}

function verifyPayment(id, verified) {
    $.ajax({
        url: "/json/postulaciones/" + id + "/verificar-pago",
        type: 'POST',
        data: { verificado: verified },
        success: function (response) {
            if (response.success) {
                Toast.fire({ icon: 'success', title: response.message });
                table.ajax.reload();
            }
        },
        error: function (xhr) {
            Toast.fire({ icon: 'error', title: 'Error al verificar pago' });
        }
    });
}

function rejectPostulacion(id, motivo) {
    $.ajax({
        url: "/json/postulaciones/" + id + "/rechazar",
        type: 'POST',
        data: { motivo: motivo },
        success: function (response) {
            if (response.success) {
                Toast.fire({ icon: 'success', title: response.message });
                $('#rejectModal').modal('hide');
                table.ajax.reload();
            }
        },
        error: function (xhr) {
            if (xhr.status === 422) {
                const errors = xhr.responseJSON.errors;
                for (let key in errors) {
                    Toast.fire({ icon: 'error', title: errors[key][0] });
                }
            } else {
                Toast.fire({ icon: 'error', title: 'Error al rechazar la postulación' });
            }
        }
    });
}

function observePostulacion(id, observaciones) {
    $.ajax({
        url: "/json/postulaciones/" + id + "/observar",
        type: 'POST',
        data: { observaciones: observaciones },
        success: function (response) {
            if (response.success) {
                Toast.fire({ icon: 'success', title: response.message });
                $('#observeModal').modal('hide');
                table.ajax.reload();
            }
        },
        error: function (xhr) {
            if (xhr.status === 422) {
                const errors = xhr.responseJSON.errors;
                for (let key in errors) {
                    Toast.fire({ icon: 'error', title: errors[key][0] });
                }
            } else {
                Toast.fire({ icon: 'error', title: 'Error al observar la postulación' });
            }
        }
    });
}

function deletePostulacion(id) {
    $.ajax({
        url: "/json/postulaciones/" + id,
        type: 'DELETE',
        success: function (response) {
            if (response.success) {
                Toast.fire({ icon: 'success', title: response.message });
                $('#deleteModal').modal('hide');
                table.ajax.reload();
            }
        },
        error: function (xhr) {
            Toast.fire({ icon: 'error', title: 'Error al eliminar la postulación' });
        }
    });
}

function loadStatistics() {
    const params = {
        ciclo_id: $('#filter-ciclo').val(),
        carrera_id: $('#filter-carrera').val(),
        estado: $('#filter-estado').val(),
    };

    $.ajax({
        url: '/json/postulaciones/stats',
        type: 'GET',
        data: params,
        success: function (response) {
            updateStatistics(response);
        },
        error: function (xhr) {
            console.error('Error al cargar estadísticas:', xhr.responseText);
            Toast.fire({ icon: 'error', title: 'No se pudieron cargar las estadísticas.' });
        }
    });
}

function updateStatistics(stats) {
    $('#stat-pendientes').text(stats.pendiente || 0);
    $('#stat-aprobadas').text(stats.aprobado || 0);
    $('#stat-rechazadas').text(stats.rechazado || 0);
    $('#stat-observadas').text(stats.observado || 0);
}

function loadDocumentsForEdit(id) {
    $.ajax({
        url: "/json/postulaciones/" + id,
        type: 'GET',
        success: function (response) {
            if (response.success) {
                const documentos = response.data.documentos;
                let html = '';

                // Mapeo de tipos de documento a sus nombres descriptivos
                const docTypes = {
                    'dni': 'DNI del Postulante',
                    'certificado_estudios': 'Certificado de Estudios',
                    'foto': 'Fotografía',
                    'voucher': 'Voucher de Pago',
                    'carta_compromiso': 'Carta de Compromiso',
                    'constancia_estudios': 'Constancia de Estudios',
                    'constancia_firmada': 'Constancia Firmada'
                };

                for (let key in documentos) {
                    const doc = documentos[key];
                    const docName = docTypes[key] || doc.nombre;

                    html += '<div class="col-md-6 mb-3">';
                    html += '<div class="card">';
                    html += '<div class="card-body">';
                    html += '<h6 class="card-title">' + docName + '</h6>';

                    if (doc.existe) {
                        html += '<p class="text-success"><i class="uil uil-check-circle"></i> Documento actual subido</p>';
                        html += '<a href="' + doc.url + '" target="_blank" class="btn btn-sm btn-info mb-2">Ver documento actual</a>';
                        html += '<div class="form-group">';
                        html += '<label>Reemplazar con nuevo archivo:</label>';
                        html += '<input type="file" class="form-control doc-file-input" data-doc-type="' + key + '" ';

                        // Establecer el tipo de archivo aceptado según el documento
                        if (key === 'foto') {
                            html += 'accept="image/*"';
                        } else {
                            html += 'accept=".pdf,.jpg,.jpeg,.png"';
                        }

                        html += '>';
                        html += '<small class="text-muted">Deje vacío si no desea cambiar este documento</small>';
                        html += '</div>';
                    } else {
                        html += '<p class="text-warning"><i class="uil uil-exclamation-triangle"></i> Documento no subido</p>';
                        html += '<div class="form-group">';
                        html += '<label>Subir archivo:</label>';
                        html += '<input type="file" class="form-control doc-file-input" data-doc-type="' + key + '" ';

                        if (key === 'foto') {
                            html += 'accept="image/*"';
                        } else {
                            html += 'accept=".pdf,.jpg,.jpeg,.png"';
                        }

                        html += '>';
                        html += '</div>';
                    }

                    html += '</div>';
                    html += '</div>';
                    html += '</div>';
                }

                $('#documents-container').html(html);
            }
        },
        error: function (xhr) {
            Toast.fire({ icon: 'error', title: 'Error al cargar los documentos' });
        }
    });
}

function saveDocumentChanges() {
    const formData = new FormData();
    const postulacionId = $('#edit-docs-postulacion-id').val();
    const observacion = $('#edit-docs-observacion').val();

    // Agregar observación si existe
    if (observacion) {
        formData.append('observacion', observacion);
    }

    // Agregar archivos seleccionados
    let hasFiles = false;
    $('.doc-file-input').each(function () {
        const file = this.files[0];
        if (file) {
            const docType = $(this).data('doc-type');
            formData.append(docType, file);
            hasFiles = true;
        }
    });

    if (!hasFiles) {
        Toast.fire({ icon: 'warning', title: 'No ha seleccionado ningún archivo para actualizar' });
        return;
    }

    // Deshabilitar botón mientras se procesa
    const btn = $('#saveDocumentChanges');
    btn.prop('disabled', true);
    btn.html('<i class="spinner-border spinner-border-sm"></i> Guardando...');

    $.ajax({
        url: "/json/postulaciones/" + postulacionId + "/actualizar-documentos",
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function (response) {
            if (response.success) {
                Toast.fire({ icon: 'success', title: 'Documentos actualizados correctamente' });
                $('#editDocumentsModal').modal('hide');

                // Recargar el modal de detalle si está abierto
                if ($('#viewModal').hasClass('show')) {
                    viewPostulacion(postulacionId);
                }

                // Recargar la tabla
                table.ajax.reload();
            } else {
                Toast.fire({ icon: 'error', title: response.message || 'Error al actualizar los documentos' });
            }

            // Restaurar botón
            btn.prop('disabled', false);
            btn.html('<i class="uil uil-save me-1"></i> Guardar Cambios');
        },
        error: function (xhr) {
            let errorMsg = 'Error al actualizar los documentos';

            if (xhr.status === 422) {
                const errors = xhr.responseJSON.errors;
                for (let key in errors) {
                    Toast.fire({ icon: 'error', title: errors[key][0] });
                }
            } else if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMsg = xhr.responseJSON.message;
                Toast.fire({ icon: 'error', title: errorMsg });
            } else {
                Toast.fire({ icon: 'error', title: errorMsg });
            }

            // Restaurar botón
            btn.prop('disabled', false);
            btn.html('<i class="uil uil-save me-1"></i> Guardar Cambios');
        }
    });
}

// Función para cargar datos de postulación aprobada para editar
function loadApprovedPostulationForEdit(id) {
    $.when(loadCiclosForEdit(), loadCarrerasForEdit()).done(function () {
        $.ajax({
            url: "/json/postulaciones/" + id + "/editar-aprobada",
            type: 'GET',
            success: function (response) {
                if (response.success) {
                    const data = response.data;

                    // Guardar el aula actual si existe
                    const aulaActual = data.inscripcion ? data.inscripcion.aula_id : null;

                    // Llenar datos del estudiante
                    $('#edit-approved-dni').val(data.estudiante.numero_documento);
                    $('#edit-approved-nombre').val(data.estudiante.nombre);
                    $('#edit-approved-apellido-paterno').val(data.estudiante.apellido_paterno);
                    $('#edit-approved-apellido-materno').val(data.estudiante.apellido_materno);
                    $('#edit-approved-telefono').val(data.estudiante.telefono);
                    $('#edit-approved-email').val(data.estudiante.email);

                    // Llenar datos del padre
                    if (data.padre) {
                        $('#edit-approved-padre-nombre').val(data.padre.nombre);
                        $('#edit-approved-padre-apellido-paterno').val(data.padre.apellido_paterno);
                        $('#edit-approved-padre-apellido-materno').val(data.padre.apellido_materno);
                        $('#edit-approved-padre-dni').val(data.padre.numero_documento);
                        $('#edit-approved-padre-telefono').val(data.padre.telefono);
                    }

                    // Llenar datos de la madre
                    if (data.madre) {
                        $('#edit-approved-madre-nombre').val(data.madre.nombre);
                        $('#edit-approved-madre-apellido-paterno').val(data.madre.apellido_paterno);
                        $('#edit-approved-madre-apellido-materno').val(data.madre.apellido_materno);
                        $('#edit-approved-madre-dni').val(data.madre.numero_documento);
                        $('#edit-approved-madre-telefono').val(data.madre.telefono);
                    }

                    // Llenar datos académicos
                    $('#edit-approved-ciclo').val(data.postulacion.ciclo_id);
                    $('#edit-approved-carrera').val(data.postulacion.carrera_id);
                    $('#edit-approved-tipo').val(data.postulacion.tipo_inscripcion);
                    $('#edit-approved-codigo').val(data.postulacion.codigo_postulante);

                    // Cargar turnos disponibles y seleccionar el actual
                    // Pasamos false como cuarto parámetro para evitar el trigger del evento change
                    loadTurnosForCarrera(data.postulacion.carrera_id, data.postulacion.ciclo_id, data.postulacion.turno_id, false);

                    // Cargar aulas directamente con el turno y aula seleccionados
                    setTimeout(function () {
                        loadAulasForTurno(data.postulacion.turno_id, data.postulacion.carrera_id, data.postulacion.ciclo_id, aulaActual);
                    }, 500);

                    // Llenar datos de pago
                    $('#edit-approved-recibo').val(data.postulacion.numero_recibo);
                    $('#edit-approved-matricula').val(data.postulacion.monto_matricula);
                    $('#edit-approved-ensenanza').val(data.postulacion.monto_ensenanza);
                }
            },
            error: function (xhr) {
                Toast.fire({ icon: 'error', title: 'Error al cargar los datos de la postulación' });
                $('#editApprovedModal').modal('hide');
            }
        });
    });
}

// Función para cargar turnos disponibles
function loadTurnosForCarrera(carreraId, cicloId, selectedTurnoId = null, triggerChange = true) {
    $.ajax({
        url: "/json/turnos/por-carrera",
        type: 'GET',
        data: {
            carrera_id: carreraId,
            ciclo_id: cicloId
        },
        success: function (response) {
            let html = '<option value="">Seleccione un turno</option>';
            if (response.data && response.data.length > 0) {
                response.data.forEach(function (turno) {
                    const selected = selectedTurnoId == turno.id ? 'selected' : '';
                    html += '<option value="' + turno.id + '" ' + selected + '>' + turno.nombre + '</option>';
                });
            }
            $('#edit-approved-turno').html(html);

            // Solo trigger change si se indica explícitamente
            if (selectedTurnoId && triggerChange) {
                $('#edit-approved-turno').trigger('change');
            }
        }
    });
}

// Función para cargar aulas disponibles
function loadAulasForTurno(turnoId, carreraId, cicloId, selectedAulaId = null) {
    if (!turnoId) {
        $('#edit-approved-aula').html('<option value="">Primero seleccione un turno</option>');
        return;
    }

    $.ajax({
        url: "/json/aulas/disponibles",
        type: 'GET',
        data: {
            turno_id: turnoId,
            carrera_id: carreraId,
            ciclo_id: cicloId
        },
        success: function (response) {
            let html = '<option value="">Seleccione un aula</option>';
            let aulaEncontrada = false;

            if (response.data && response.data.length > 0) {
                response.data.forEach(function (aula) {
                    const selected = selectedAulaId && selectedAulaId == aula.id ? 'selected' : '';
                    if (selected) aulaEncontrada = true;
                    const capacidadInfo = ' (Capacidad: ' + aula.capacidad_disponible + '/' + aula.capacidad + ')';
                    html += '<option value="' + aula.id + '" ' + selected + '>' + aula.nombre + capacidadInfo + '</option>';
                });
            }

            // Si el aula seleccionada no está en la lista (porque está llena), agregarla como opción deshabilitada
            if (selectedAulaId && !aulaEncontrada) {
                // Hacer una petición adicional para obtener el nombre del aula actual
                $.ajax({
                    url: "/json/aulas/" + selectedAulaId,
                    type: 'GET',
                    success: function (aulaResponse) {
                        if (aulaResponse.success && aulaResponse.data) {
                            html = '<option value="' + selectedAulaId + '" selected>' +
                                aulaResponse.data.nombre + ' (Aula actual - Sin capacidad disponible)</option>' + html;
                            $('#edit-approved-aula').html(html);
                        }
                    }
                });
            } else {
                $('#edit-approved-aula').html(html);
            }

            // Si hay un aula seleccionada, mantenerla
            if (selectedAulaId) {
                $('#edit-approved-aula').val(selectedAulaId);
            }
        },
        error: function () {
            $('#edit-approved-aula').html('<option value="">Error al cargar aulas</option>');
        }
    });
}

// Función para cargar ciclos disponibles para editar
function loadCiclosForEdit() {
    return $.ajax({
        url: "/json/ciclos",
        type: 'GET',
        success: function (response) {
            if (response.success) {
                let html = '<option value="">Seleccione un ciclo</option>';
                response.data.forEach(function (ciclo) {
                    html += '<option value="' + ciclo.id + '">' + ciclo.nombre + '</option>';
                });
                $('#edit-approved-ciclo').html(html);
            }
        },
        error: function () {
            $('#edit-approved-ciclo').html('<option value="">Error al cargar ciclos</option>');
        }
    });
}

// Función para cargar carreras disponibles para editar
function loadCarrerasForEdit() {
    return $.ajax({
        url: "/json/carreras",
        type: 'GET',
        success: function (response) {
            if (response.success) {
                let html = '<option value="">Seleccione una carrera</option>';
                response.data.forEach(function (carrera) {
                    html += '<option value="' + carrera.id + '">' + carrera.nombre + '</option>';
                });
                $('#edit-approved-carrera').html(html);
            }
        },
        error: function () {
            $('#edit-approved-carrera').html('<option value="">Error al cargar carreras</option>');
        }
    });
}

// Función para guardar cambios de postulación aprobada
function saveApprovedPostulationChanges() {
    const formData = {
        nombre: $('#edit-approved-nombre').val(),
        apellido_paterno: $('#edit-approved-apellido-paterno').val(),
        apellido_materno: $('#edit-approved-apellido-materno').val(),
        telefono: $('#edit-approved-telefono').val(),
        email: $('#edit-approved-email').val(),
        ciclo_id: $('#edit-approved-ciclo').val(),
        carrera_id: $('#edit-approved-carrera').val(),
        turno_id: $('#edit-approved-turno').val(),
        aula_id: $('#edit-approved-aula').val(),
        tipo_inscripcion: $('#edit-approved-tipo').val(),
        codigo_postulante: $('#edit-approved-codigo').val(),
        numero_recibo: $('#edit-approved-recibo').val(),
        monto_matricula: $('#edit-approved-matricula').val(),
        monto_ensenanza: $('#edit-approved-ensenanza').val(),
        observacion_cambio: $('#edit-approved-observacion').val(),
        padre_nombre: $('#edit-approved-padre-nombre').val(),
        padre_apellido_paterno: $('#edit-approved-padre-apellido-paterno').val(),
        padre_apellido_materno: $('#edit-approved-padre-apellido-materno').val(),
        padre_dni: $('#edit-approved-padre-dni').val(),
        padre_telefono: $('#edit-approved-padre-telefono').val(),
        madre_nombre: $('#edit-approved-madre-nombre').val(),
        madre_apellido_paterno: $('#edit-approved-madre-apellido-paterno').val(),
        madre_apellido_materno: $('#edit-approved-madre-apellido-materno').val(),
        madre_dni: $('#edit-approved-madre-dni').val(),
        madre_telefono: $('#edit-approved-madre-telefono').val(),
    };

    // Validar observación
    if (!formData.observacion_cambio || formData.observacion_cambio.length < 10) {
        Toast.fire({ icon: 'error', title: 'Debe explicar el motivo de la modificación (mínimo 10 caracteres)' });
        return;
    }

    // Deshabilitar botón mientras se procesa
    const btn = $('#saveApprovedChanges');
    btn.prop('disabled', true);
    btn.html('<i class="spinner-border spinner-border-sm"></i> Guardando...');

    $.ajax({
        url: "/json/postulaciones/" + currentPostulacionId + "/actualizar-aprobada",
        type: 'PUT',
        data: formData,
        success: function (response) {
            if (response.success) {
                Toast.fire({ icon: 'success', title: 'Postulación actualizada correctamente' });

                if (response.message) {
                    Toast.fire({ icon: 'info', title: response.message });
                }

                $('#editApprovedModal').modal('hide');
                table.ajax.reload();
            } else {
                Toast.fire({ icon: 'error', title: response.message || 'Error al actualizar la postulación' });
            }

            // Restaurar botón
            btn.prop('disabled', false);
            btn.html('<i class="uil uil-save me-1"></i> Guardar Cambios');
        },
        error: function (xhr) {
            if (xhr.status === 422) {
                const errors = xhr.responseJSON.errors;
                for (let key in errors) {
                    Toast.fire({ icon: 'error', title: errors[key][0] });
                }
            } else {
                Toast.fire({ icon: 'error', title: 'Error al actualizar la postulación' });
            }

            // Restaurar botón
            btn.prop('disabled', false);
            btn.html('<i class="uil uil-save me-1"></i> Guardar Cambios');
        }
    });
}
