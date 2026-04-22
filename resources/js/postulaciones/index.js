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
        pageLength: 50,
        order: [[6, 'desc']],
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
                d.solo_hoy = $('#filter-hoy').is(':checked') ? 1 : 0;
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
            
            // Re-aplicar resaltado de nuevos registros si la función existe
            if (typeof window.applyPersistentHighlights === 'function') {
                window.applyPersistentHighlights();
            }
        }
    });
}

function setupEventHandlers() {
    // Filtrar
    $('#btn-filtrar').on('click', function () {
        // Al filtrar manualmente, quitamos el filtro de "Hoy" si estuviera activo
        $('#filter-hoy').prop('checked', false);
        $('#card-filter-hoy').removeClass('active');
        table.ajax.reload();
        loadStatistics();
    });

    // Filtro especial para Registrados Hoy
    $('#card-filter-hoy').on('click', function() {
        const checkbox = $('#filter-hoy');
        const isActive = !checkbox.is(':checked');
        checkbox.prop('checked', isActive);
        
        if (isActive) {
            $(this).addClass('active');
        } else {
            $(this).removeClass('active');
        }
        
        table.ajax.reload();
        loadStatistics();
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

    // Sincronizar pago desde API
    $(document).on('click', '.sync-payment', function () {
        const id = $(this).data('id');
        const btn = $(this);

        if (!id) {
            Toast.fire({ icon: 'error', title: 'No se pudo obtener el ID de la postulación' });
            return;
        }

        Swal.fire({
            title: '¿Sincronizar con UNAMAD?',
            text: "Se buscarán los pagos del alumno en la API oficial y se actualizarán los montos automáticamente.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: 'var(--cepre-magenta)',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, sincronizar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                btn.prop('disabled', true);
                btn.html('<i class="spinner-border spinner-border-sm me-1"></i> ESPERE...');

                $.ajax({
                    url: "/json/postulaciones/" + id + "/sync-payment",
                    type: 'POST',
                    success: function (response) {
                        if (response.success) {
                            Toast.fire({ icon: 'success', title: response.message });
                            
                            // Si el modal de edición aprobada está abierto, recargar sus datos
                            if ($('#editApprovedModal').hasClass('show')) {
                                loadApprovedPostulationForEdit(id);
                            }
                            
                            // Si el modal de detalle está abierto, recargar sus datos
                            if ($('#viewModal').hasClass('show')) {
                                viewPostulacion(id);
                            }
                            
                            // Recargar la tabla
                            table.ajax.reload();
                        } else {
                            Toast.fire({ icon: 'error', title: response.message || 'Error al sincronizar pago' });
                            btn.prop('disabled', false);
                            btn.html('<i class="bi bi-arrow-repeat me-1"></i> SINCRONIZAR');
                        }
                    },
                    error: function (xhr) {
                        const errorMsg = xhr.responseJSON?.message || 'Error al sincronizar pago';
                        Toast.fire({ icon: 'error', title: errorMsg });
                        btn.prop('disabled', false);
                        btn.html('<i class="bi bi-arrow-repeat me-1"></i> SINCRONIZAR');
                    }
                });
            }
        });
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
        $('#btn-sync-edit').data('id', id); // Asignar el ID al botón de sincronizar del modal
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
    // Mostrar spinner de carga inicial con estilo premium
    $('#viewModalBody').html(`
        <div class="p-5 text-center fade-in-premium">
            <div class="spinner-grow text-magenta" role="status" style="width: 3rem; height: 3rem;"></div>
            <p class="mt-3 fw-bold text-navy" style="letter-spacing: 1px;">AUTENTICANDO EXPEDIENTE...</p>
        </div>
    `);
    $('#viewModal').modal('show');

    $.ajax({
        url: "/json/postulaciones/" + id,
        type: 'GET',
        success: function (response) {
            if (response.success) {
                const data = response.data;
                const post = data.postulacion;
                const docs = data.documentos;
                const insc = data.inscripcion;
                const est = post.estudiante;

                // Configuración Institucional de Estados
                const statusConfig = {
                    'pendiente': { class: 'bg-premium-pending', icon: 'bi-hourglass-split', label: 'En Verificación', color: 'var(--cepre-navy)' },
                    'aprobado': { class: 'bg-premium-approved', icon: 'bi-patch-check-fill', label: 'Postulación Aprobada', color: 'var(--cepre-green)' },
                    'rechazado': { class: 'bg-premium-rejected', icon: 'bi-x-octagon-fill', label: 'Postulación Rechazada', color: '#d32f2f' },
                    'observado': { class: 'bg-premium-observed', icon: 'bi-exclamation-diamond-fill', label: 'Con Observaciones', color: 'var(--cepre-magenta)' }
                };

                const config = statusConfig[post.estado] || { class: 'bg-premium-default', icon: 'bi-info-circle', label: post.estado.toUpperCase(), color: 'var(--cepre-navy)' };

                let html = '<div class="fade-in-premium">';
                
                // 1. CABECERA INSTITUCIONAL
                html += `<div class="premium-header ${config.class}">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="premium-title-container">
                            <div class="premium-logo-box">
                                <img src="/assets_cepre/img/logo/logo2_0.svg" class="premium-logo" alt="CEPRE LOGO">
                            </div>
                            <div class="ms-1">
                                <h2 class="premium-title">${est.nombre} ${est.apellido_paterno} ${est.apellido_materno}</h2>
                                <div class="premium-subtitle-badges">
                                    <span class="premium-badge-id"><i class="bi bi-fingerprint me-1"></i> EXPEDIENTE: ${post.codigo_postulante}</span>
                                    <span class="premium-badge-status" style="color: ${config.color}"><i class="bi ${config.icon} me-1"></i> ${config.label}</span>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn-close-premium" data-bs-dismiss="modal" aria-label="Close">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                </div>`;

                html += '<div class="modal-body p-0">';
                html += '<div class="row g-0">';
                
                // 2. SIDEBAR - CREDENCIAL DEL POSTULANTE
                html += '<div class="col-lg-3 p-4 pr-lg-0">';
                html += '<div class="premium-sidebar h-100">';
                
                const fotoUrl = post.foto_path ? '/storage/' + post.foto_path : (data.foto_perfil_url || null);
                html += '<div class="premium-photo-container shadow-sm">';
                if (fotoUrl) {
                    html += `<img src="${fotoUrl}" class="premium-photo" alt="Foto de Perfil">`;
                } else {
                    html += '<div class="w-100 h-100 d-flex flex-column align-items-center justify-content-center text-muted"><i class="bi bi-person-bounding-box" style="font-size: 3rem;"></i><span class="small mt-2">SIN FOTO</span></div>';
                }
                html += '</div>';

                html += '<div class="premium-barcode-section">';
                html += '<div class="text-center mb-2"><span class="badge postulante-badge border px-3 py-2 w-100" style="font-size: 10px;">POSTULANTE OFICIAL</span></div>';
                html += `<img src="https://barcode.tec-it.com/barcode.ashx?data=${post.codigo_postulante}&code=Code128&translate-esc=true" style="width: 100%; height: 55px; mix-blend-mode: multiply;" alt="Barcode">`;
                html += `<div class="mt-2 fw-bold text-center text-navy" style="font-size: 13px; letter-spacing: 2px;">${post.codigo_postulante}</div>`;
                html += '</div>';

                html += `<div class="sidebar-info-item">
                    <div class="d-flex align-items-center gap-2 mb-1 text-cyan"><i class="bi bi-calendar-event"></i> <span class="small fw-800">REGISTRO</span></div>
                    <span class="text-navy small fw-bold d-block">${new Date(post.fecha_postulacion).toLocaleDateString()}</span>
                </div>`;
                html += `<div class="sidebar-info-item">
                    <div class="d-flex align-items-center gap-2 mb-1 text-cyan"><i class="bi bi-person-vcard"></i> <span class="small fw-800">DNI</span></div>
                    <span class="text-navy small fw-bold d-block">${est.numero_documento}</span>
                </div>`;
                
                // Moviendo contacto al sidebar para llenar espacio
                html += `<div class="sidebar-info-item mt-3">
                    <div class="d-flex align-items-center gap-2 mb-1 text-cyan"><i class="bi bi-envelope-at"></i> <span class="small fw-800">EMAIL</span></div>
                    <span class="text-navy small fw-bold d-block" style="word-break: break-all;">${est.email || 'N/A'}</span>
                </div>`;
                html += `<div class="sidebar-info-item">
                    <div class="d-flex align-items-center gap-2 mb-1 text-cyan"><i class="bi bi-phone"></i> <span class="small fw-800">CELULAR</span></div>
                    <span class="text-navy small fw-bold d-block">${est.telefono || 'N/A'}</span>
                </div>`;
                html += `<div class="sidebar-info-item">
                    <div class="d-flex align-items-center gap-2 mb-1 text-cyan"><i class="bi bi-geo-alt"></i> <span class="small fw-800">DIRECCIÓN</span></div>
                    <span class="text-navy small fw-bold d-block" style="font-size: 11px;">${est.direccion || 'N/A'}</span>
                </div>`;

                html += '</div></div>';

                // 3. CUERPO PRINCIPAL
                html += '<div class="col-lg-9 p-4 pl-lg-0">';
                
                // 3.1 GESTIÓN DE PAGOS (KPIs + TRANSACCIONES)
                html += '<div class="payments-container shadow-sm mb-4">';
                html += `<div class="payments-header">
                    <div class="d-flex align-items-center gap-2">
                        <div class="spec-header-icon" style="color: var(--cepre-navy); background: #e8eaf6;"><i class="bi bi-cash-coin"></i></div>
                        <span class="spec-header-title" style="color: var(--cepre-navy);">Control de Pagos y Aranceles</span>
                    </div>
                    <button class="btn btn-cepre-magenta btn-premium-action text-white rounded-pill px-4 fw-bold sync-payment shadow-sm" data-id="${post.id}" style="font-size: 12px;">
                        <i class="bi bi-arrow-repeat me-1"></i> SINCRONIZAR PAGOS
                    </button>
                </div>`;
                html += '<div class="kpi-row mb-3">';
                html += renderKPICard('bi-wallet2', 'Matrícula', `S/. ${post.monto_matricula || '0.00'}`, 'kpi-card-navy', 'text-navy');
                html += renderKPICard('bi-bank', 'Enseñanza', `S/. ${post.monto_ensenanza || '0.00'}`, 'kpi-card-cyan', 'text-cyan');
                html += renderKPICard('bi-check2-circle', 'Recaudado', `S/. ${post.monto_total_pagado || '0.00'}`, 'kpi-card-green', 'text-green');
                html += renderKPICard('bi-award', 'Modalidad', post.tipo_inscripcion.toUpperCase(), 'kpi-card-magenta', 'text-magenta');
                html += '</div>';

                // TABLA DE TRANSACCIONES DETALLADA
                html += `<div class="payment-detail-container">
                    <table class="payment-detail-table">
                        <thead>
                            <tr>
                                <th>N° RECIBO / OPERACIÓN</th>
                                <th>CONCEPTO DE PAGO</th>
                                <th>FECHA DE PAGO</th>
                                <th>ESTADO</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="payment-row">
                                <td class="text-navy fw-800">${post.numero_recibo || 'POR REGISTRAR'}</td>
                                <td><span class="payment-concept-badge bg-navy text-white border">INSCRIPCIÓN ORDINARIA</span></td>
                                <td>${post.fecha_emision_voucher ? new Date(post.fecha_emision_voucher).toLocaleDateString() : 'PENDIENTE'}</td>
                                <td><span class="badge rounded-pill bg-success px-3">VERIFICADO</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>`;
                html += '</div>';


                // 3.2 INFORMACIÓN ACADÉMICA (GRID ORGANIZADA)
                html += '<div class="row g-3 mb-4 d-flex align-items-stretch">';
                html += '<div class="col-lg-8">';
                html += '<div class="spec-container h-100 shadow-sm">';
                html += `<div class="spec-header">
                    <div class="spec-header-icon"><i class="bi bi-mortarboard"></i></div>
                    <span class="spec-header-title">Detalles de la Postulación</span>
                </div>`;
                html += '<div class="spec-grid">';
                html += renderSpecItem('bi-briefcase', 'Carrera Elegida', post.carrera?.nombre || 'No asignada', 'text-navy fw-800');
                html += renderSpecItem('bi-layers', 'Ciclo Académico', post.ciclo?.nombre || 'No asignado');
                html += renderSpecItem('bi-clock-history', 'Turno / Horario', (typeof post.turno === 'object' ? post.turno?.nombre : post.turno) || 'No asignado');
                
                if (insc && insc.aula) {
                    html += renderSpecItem('bi-door-closed-fill', 'Aula Asignada', insc.aula.nombre, 'text-success fw-800');
                } else {
                    html += renderSpecItem('bi-door-closed', 'Aula Asignada', 'Pendiente de asignación', 'text-muted italic');
                }
                
                html += renderSpecItem('bi-building-check', 'Centro Educativo', post.centro_educativo?.nombre || 'No registrado');
                html += renderSpecItem('bi-calendar-check', 'Año de Egreso', post.anio_egreso || '---');
                html += '</div></div></div>';

                // 3.3 PADRES / APODERADOS
                html += '<div class="col-lg-4">';
                html += '<div class="spec-container h-100 shadow-sm">';
                html += `<div class="spec-header">
                    <div class="spec-header-icon" style="color: var(--cepre-magenta); background: #fce4ec;"><i class="bi bi-people"></i></div>
                    <span class="spec-header-title" style="color: var(--cepre-magenta);">Padres / Apoderados</span>
                </div>`;
                html += '<div class="d-flex flex-column gap-3">';
                if (data.padre) {
                    html += `<div><label class="spec-item-label">PADRE</label><div class="spec-item-value small text-navy">${data.padre.nombre} ${data.padre.apellido_paterno} <br><i class="bi bi-telephone text-muted"></i> ${data.padre.telefono || '---'}</div></div>`;
                }
                if (data.madre) {
                    html += `<div><label class="spec-item-label">MADRE</label><div class="spec-item-value small text-navy">${data.madre.nombre} ${data.madre.apellido_paterno} <br><i class="bi bi-telephone text-muted"></i> ${data.madre.telefono || '---'}</div></div>`;
                }
                if (!data.padre && !data.madre) {
                    html += '<div class="text-center py-2"><i class="bi bi-exclamation-circle text-muted fs-4"></i><p class="text-muted small mt-1">Sin datos de apoderados</p></div>';
                }
                html += '</div></div></div></div>';

                // 3.4 DOCUMENTOS CARGADOS
                html += '<div class="spec-container mt-3">';
                html += `<div class="spec-header d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2">
                        <div class="spec-header-icon" style="color: #64748b; background: #f1f5f9;"><i class="bi bi-file-earmark-zip"></i></div>
                        <span class="spec-header-title" style="color: #64748b;">Expediente Digital</span>
                    </div>
                    <button class="btn btn-premium-action btn-navy edit-documents" data-id="${post.id}" style="font-size: 11px; padding: 0.5rem 1.2rem;">
                        <i class="bi bi-pencil-square me-1"></i> GESTIONAR ARCHIVOS
                    </button>
                </div>`;
                
                html += '<div class="row g-3">';
                for (let key in docs) {
                    const doc = docs[key];
                    if (doc.existe) {
                        const docColor = key === 'dni' ? '#e1f5fe' : (key === 'voucher' ? '#e8f5e9' : '#fce4ec');
                        const iconColor = key === 'dni' ? '#0288d1' : (key === 'voucher' ? '#2e7d32' : '#d81b60');
                        
                        html += `<div class="col-md-6">
                            <div class="doc-tile shadow-sm">
                                <div class="doc-info">
                                    <div class="doc-icon-box" style="background: ${docColor}; color: ${iconColor};">
                                        <i class="bi ${getFileIcon(key)}"></i>
                                    </div>
                                    <span class="doc-name text-navy">${doc.nombre}</span>
                                </div>
                                <a href="${doc.url}" target="_blank" class="btn btn-navy btn-sm rounded-pill px-3 fw-bold" style="font-size: 10px;"><i class="bi bi-eye-fill me-1"></i> VER</a>
                            </div>
                        </div>`;
                    }
                }
                html += '</div></div>';

                // 3.5 ALERTAS DE ESTADO
                if (post.observaciones || post.motivo_rechazo) {
                    const tint = post.motivo_rechazo ? 'danger' : 'warning';
                    const icon = post.motivo_rechazo ? 'bi-shield-x' : 'bi-shield-exclamation';
                    html += `<div class="alert alert-${tint} d-flex align-items-center gap-3 mt-3 border-0 shadow-sm p-3" style="border-radius: 15px;">
                        <i class="bi ${icon} fs-2"></i>
                        <div>
                            <div class="fw-800 text-uppercase small" style="letter-spacing: 1px;">Nota Administrativa:</div>
                            <div class="fw-bold">${post.observaciones || post.motivo_rechazo}</div>
                        </div>
                    </div>`;
                }

                html += '</div></div></div>'; // Cierre de row, col-md-9, fade-in
                html += '</div>'; // Cierre de modal-body

                // FOOTER DE ACCIONES RÁPIDAS
                html += `<div class="modal-footer">
                    <div class="footer-actions-left">
                        ${post.estado === 'pendiente' ? `
                            ${!post.documentos_verificados ? `<button class="btn btn-premium-action btn-outline-info verify-documents" data-id="${post.id}"><i class="bi bi-file-earmark-check me-1"></i> VERIFICAR DOCS</button>` : ''}
                            ${!post.pago_verificado ? `<button class="btn btn-premium-action btn-outline-success verify-payment" data-id="${post.id}"><i class="bi bi-cash-stack me-1"></i> VERIFICAR PAGO</button>` : ''}
                            
                            <button class="btn btn-premium-action btn-outline-warning observe-postulacion" data-id="${post.id}"><i class="bi bi-chat-quote me-1"></i> OBSERVAR</button>
                            <button class="btn btn-premium-action btn-outline-danger reject-postulacion" data-id="${post.id}"><i class="bi bi-slash-circle me-1"></i> RECHAZAR</button>
                            
                            ${post.documentos_verificados && post.pago_verificado ? 
                                `<button class="btn btn-premium-action btn-success approve-postulacion shadow-sm" data-id="${post.id}"><i class="bi bi-check-lg me-1"></i> APROBAR AHORA</button>` : ''
                            }
                        ` : ''}
                    </div>
                    <div class="footer-actions-right">
                        <button type="button" class="btn btn-premium-action btn-light shadow-sm" data-bs-dismiss="modal">SALIR DEL EXPEDIENTE</button>
                    </div>
                </div>`;

                $('#viewModalBody').html(html);
            }
        },
        error: function (xhr) {
            Toast.fire({ icon: 'error', title: 'Error crítico al recuperar expediente' });
        }
    });
}



function getFileIcon(type) {
    const icons = {
        'dni': 'bi-card-heading',
        'certificado_estudios': 'bi-journal-check',
        'foto': 'bi-image',
        'voucher': 'bi-receipt',
        'carta_compromiso': 'bi-file-earmark-ruled',
        'constancia_estudios': 'bi-mortarboard',
        'constancia_firmada': 'bi-pen-fill'
    };
    return icons[type] || 'bi-file-earmark-text';
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
        solo_hoy: $('#filter-hoy').is(':checked') ? 1 : 0,
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
    $('#stat-hoy').text(stats.hoy || 0);
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
                    
                    // Asegurar que el tipo de inscripción coincida con las opciones del select (minúsculas)
                    if (data.postulacion.tipo_inscripcion) {
                        $('#edit-approved-tipo').val(data.postulacion.tipo_inscripcion.toLowerCase());
                    }
                    
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

// --- FUNCIONES HELPER PARA RENDERIZADO PREMIUM ---

function renderKPICard(icon, label, value, cardClass, textClass) {
    return `<div class="kpi-card ${cardClass}">
        <div class="kpi-icon"><i class="bi ${icon}"></i></div>
        <div class="kpi-content">
            <div class="kpi-label">${label}</div>
            <div class="kpi-value ${textClass}">${value}</div>
        </div>
    </div>`;
}

function renderSpecItem(icon, label, value, extraClass = '') {
    return `<div class="spec-item">
        <div class="d-flex align-items-center gap-2 mb-1">
            <i class="bi ${icon} text-cyan" style="font-size: 14px;"></i>
            <span class="spec-item-label mb-0">${label}</span>
        </div>
        <span class="spec-item-value ${extraClass}">${value}</span>
    </div>`;
}
