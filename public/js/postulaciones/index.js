// public/js/postulaciones/index.js

// Configuración CSRF para AJAX
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

let table;
let currentPostulacionId = null;

$(document).ready(function() {
    console.log('Postulaciones JS cargado');
    
    // Inicializar DataTables
    initDataTable();
    
    // Cargar estadísticas iniciales
    loadStatistics();
    
    // Configurar eventos
    setupEventHandlers();
});

function initDataTable() {
    table = $('#postulaciones-datatable').DataTable({
        processing: true,
        serverSide: false,
        ajax: {
            url: default_server + "/json/postulaciones",
            type: 'GET',
            data: function(d) {
                d.ciclo_id = $('#filter-ciclo').val();
                d.estado = $('#filter-estado').val();
                d.carrera_id = $('#filter-carrera').val();
            },
            dataSrc: function(json) {
                // Actualizar estadísticas
                updateStatistics(json.data);
                return json.data;
            }
        },
        columns: [
            { data: 'codigo' },
            { data: 'estudiante' },
            { data: 'dni' },
            { data: 'carrera' },
            { data: 'turno' },
            { 
                data: 'tipo_inscripcion',
                render: function(data) {
                    return data === 'postulante' ? 
                        '<span class="badge bg-primary">Postulante</span>' : 
                        '<span class="badge bg-info">Reforzamiento</span>';
                }
            },
            { data: 'fecha_postulacion' },
            { 
                data: 'estado',
                render: function(data) {
                    let badgeClass = 'badge-estado-' + data;
                    return '<span class="badge ' + badgeClass + '">' + data.toUpperCase() + '</span>';
                }
            },
            {
                data: null,
                render: function(data) {
                    let html = '<div class="d-flex gap-1">';
                    
                    // Documentos
                    let docIcon = data.documentos_verificados ? 
                        '<i class="uil uil-check-circle text-success"></i>' : 
                        '<i class="uil uil-times-circle text-danger"></i>';
                    html += '<span title="Documentos">' + docIcon + '</span>';
                    
                    // Pago
                    let payIcon = data.pago_verificado ? 
                        '<i class="uil uil-money-bill text-success"></i>' : 
                        '<i class="uil uil-money-bill-slash text-danger"></i>';
                    html += '<span title="Pago">' + payIcon + '</span>';
                    
                    html += '</div>';
                    return html;
                }
            },
            {
                data: null,
                render: function(data, type, row) {
                    return row.actions;
                }
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
        drawCallback: function() {
            $('.dataTables_paginate > .pagination').addClass('pagination-rounded');
        }
    });
}

function setupEventHandlers() {
    // Filtrar
    $('#btn-filtrar').on('click', function() {
        table.ajax.reload();
    });
    
    // Ver detalle
    $(document).on('click', '.view-postulacion', function() {
        const id = $(this).data('id');
        viewPostulacion(id);
    });
    
    // Verificar documentos
    $(document).on('click', '.verify-docs', function() {
        const id = $(this).data('id');
        const verified = $(this).data('verified') == 1 ? false : true;
        verifyDocuments(id, verified);
    });
    
    // Verificar pago
    $(document).on('click', '.verify-payment', function() {
        const id = $(this).data('id');
        const verified = $(this).data('verified') == 1 ? false : true;
        verifyPayment(id, verified);
    });
    
    // Observar
    $(document).on('click', '.observe-postulacion', function() {
        const id = $(this).data('id');
        currentPostulacionId = id;
        $('#observe-id').val(id);
        $('#observeModal').modal('show');
    });
    
    // Rechazar
    $(document).on('click', '.reject-postulacion', function() {
        const id = $(this).data('id');
        currentPostulacionId = id;
        $('#reject-id').val(id);
        $('#rejectModal').modal('show');
    });
    
    // Eliminar
    $(document).on('click', '.delete-postulacion', function() {
        const id = $(this).data('id');
        currentPostulacionId = id;
        $('#delete-id').val(id);
        $('#deleteModal').modal('show');
    });
    
    // Aprobar (placeholder)
    $(document).on('click', '.approve-postulacion', function() {
        const id = $(this).data('id');
        toastr.info('La función de aprobación se implementará próximamente');
    });
    
    // Confirmar rechazo
    $('#confirmReject').on('click', function() {
        const motivo = $('#reject-motivo').val();
        if (motivo.length < 10) {
            toastr.error('El motivo debe tener al menos 10 caracteres');
            return;
        }
        rejectPostulacion(currentPostulacionId, motivo);
    });
    
    // Confirmar observación
    $('#confirmObserve').on('click', function() {
        const observaciones = $('#observe-observaciones').val();
        if (observaciones.length < 10) {
            toastr.error('Las observaciones deben tener al menos 10 caracteres');
            return;
        }
        observePostulacion(currentPostulacionId, observaciones);
    });
    
    // Confirmar eliminación
    $('#confirmDelete').on('click', function() {
        deletePostulacion(currentPostulacionId);
    });
    
    // Limpiar formularios al cerrar modales
    $('.modal').on('hidden.bs.modal', function() {
        $(this).find('form')[0]?.reset();
        currentPostulacionId = null;
    });
}

function viewPostulacion(id) {
    $.ajax({
        url: default_server + "/json/postulaciones/" + id,
        type: 'GET',
        success: function(response) {
            if (response.success) {
                const data = response.data;
                const postulacion = data.postulacion;
                const documentos = data.documentos;
                
                let html = '<div class="row">';
                
                // Información del estudiante
                html += '<div class="col-md-6">';
                html += '<h5>Información del Estudiante</h5>';
                html += '<table class="table table-sm">';
                html += '<tr><td><strong>Nombre:</strong></td><td>' + postulacion.estudiante.nombre + ' ' + 
                        postulacion.estudiante.apellido_paterno + ' ' + postulacion.estudiante.apellido_materno + '</td></tr>';
                html += '<tr><td><strong>DNI:</strong></td><td>' + (postulacion.estudiante.numero_documento || 'N/A') + '</td></tr>';
                html += '<tr><td><strong>Email:</strong></td><td>' + postulacion.estudiante.email + '</td></tr>';
                html += '<tr><td><strong>Teléfono:</strong></td><td>' + (postulacion.estudiante.telefono || 'N/A') + '</td></tr>';
                html += '</table>';
                html += '</div>';
                
                // Información de la postulación
                html += '<div class="col-md-6">';
                html += '<h5>Información de la Postulación</h5>';
                html += '<table class="table table-sm">';
                html += '<tr><td><strong>Código:</strong></td><td>' + postulacion.codigo_postulacion + '</td></tr>';
                html += '<tr><td><strong>Ciclo:</strong></td><td>' + postulacion.ciclo.nombre + '</td></tr>';
                html += '<tr><td><strong>Carrera:</strong></td><td>' + postulacion.carrera.nombre + '</td></tr>';
                html += '<tr><td><strong>Turno:</strong></td><td>' + postulacion.turno.nombre + '</td></tr>';
                html += '<tr><td><strong>Tipo:</strong></td><td>' + postulacion.tipo_inscripcion + '</td></tr>';
                html += '<tr><td><strong>Estado:</strong></td><td><span class="badge badge-estado-' + 
                        postulacion.estado + '">' + postulacion.estado.toUpperCase() + '</span></td></tr>';
                html += '</table>';
                html += '</div>';
                
                // Documentos
                html += '<div class="col-md-6">';
                html += '<h5>Documentos Subidos</h5>';
                html += '<ul class="document-list">';
                
                for (let key in documentos) {
                    const doc = documentos[key];
                    if (doc.existe) {
                        html += '<li><i class="uil uil-check-circle text-success"></i> ' + doc.nombre + 
                               ' <a href="' + doc.url + '" target="_blank" class="btn btn-sm btn-primary">Ver</a></li>';
                    } else {
                        html += '<li><i class="uil uil-times-circle text-danger"></i> ' + doc.nombre + ' (No subido)</li>';
                    }
                }
                
                html += '</ul>';
                html += '</div>';
                
                // Información del voucher
                if (postulacion.numero_recibo) {
                    html += '<div class="col-md-6">';
                    html += '<h5>Información del Pago</h5>';
                    html += '<div class="voucher-details">';
                    html += '<p><strong>N° Recibo:</strong> ' + postulacion.numero_recibo + '</p>';
                    // Formatear fecha de emisión
                    let fechaEmision = '';
                    if (postulacion.fecha_emision_voucher) {
                        let fecha = new Date(postulacion.fecha_emision_voucher);
                        let dia = fecha.getDate().toString().padStart(2, '0');
                        let mes = (fecha.getMonth() + 1).toString().padStart(2, '0');
                        let año = fecha.getFullYear();
                        fechaEmision = dia + '/' + mes + '/' + año;
                    }
                    html += '<p><strong>Fecha Emisión:</strong> ' + fechaEmision + '</p>';
                    html += '<p><strong>Matrícula:</strong> S/. ' + postulacion.monto_matricula + '</p>';
                    html += '<p><strong>Enseñanza:</strong> S/. ' + postulacion.monto_ensenanza + '</p>';
                    html += '<p><strong>Total Pagado:</strong> S/. ' + postulacion.monto_total_pagado + '</p>';
                    html += '</div>';
                    html += '</div>';
                }
                
                // Observaciones o motivo de rechazo
                if (postulacion.observaciones) {
                    html += '<div class="col-12 mt-3">';
                    html += '<div class="alert alert-warning">';
                    html += '<h6>Observaciones:</h6>';
                    html += '<p>' + postulacion.observaciones + '</p>';
                    html += '</div>';
                    html += '</div>';
                }
                
                if (postulacion.motivo_rechazo) {
                    html += '<div class="col-12 mt-3">';
                    html += '<div class="alert alert-danger">';
                    html += '<h6>Motivo de Rechazo:</h6>';
                    html += '<p>' + postulacion.motivo_rechazo + '</p>';
                    html += '</div>';
                    html += '</div>';
                }
                
                html += '</div>';
                
                $('#viewModalBody').html(html);
                $('#viewModal').modal('show');
            }
        },
        error: function(xhr) {
            toastr.error('Error al cargar el detalle de la postulación');
        }
    });
}

function verifyDocuments(id, verified) {
    $.ajax({
        url: default_server + "/json/postulaciones/" + id + "/verificar-documentos",
        type: 'POST',
        data: { verificado: verified },
        success: function(response) {
            if (response.success) {
                toastr.success(response.message);
                table.ajax.reload();
            }
        },
        error: function(xhr) {
            toastr.error('Error al verificar documentos');
        }
    });
}

function verifyPayment(id, verified) {
    $.ajax({
        url: default_server + "/json/postulaciones/" + id + "/verificar-pago",
        type: 'POST',
        data: { verificado: verified },
        success: function(response) {
            if (response.success) {
                toastr.success(response.message);
                table.ajax.reload();
            }
        },
        error: function(xhr) {
            toastr.error('Error al verificar pago');
        }
    });
}

function rejectPostulacion(id, motivo) {
    $.ajax({
        url: default_server + "/json/postulaciones/" + id + "/rechazar",
        type: 'POST',
        data: { motivo: motivo },
        success: function(response) {
            if (response.success) {
                toastr.success(response.message);
                $('#rejectModal').modal('hide');
                table.ajax.reload();
            }
        },
        error: function(xhr) {
            if (xhr.status === 422) {
                const errors = xhr.responseJSON.errors;
                for (let key in errors) {
                    toastr.error(errors[key][0]);
                }
            } else {
                toastr.error('Error al rechazar la postulación');
            }
        }
    });
}

function observePostulacion(id, observaciones) {
    $.ajax({
        url: default_server + "/json/postulaciones/" + id + "/observar",
        type: 'POST',
        data: { observaciones: observaciones },
        success: function(response) {
            if (response.success) {
                toastr.success(response.message);
                $('#observeModal').modal('hide');
                table.ajax.reload();
            }
        },
        error: function(xhr) {
            if (xhr.status === 422) {
                const errors = xhr.responseJSON.errors;
                for (let key in errors) {
                    toastr.error(errors[key][0]);
                }
            } else {
                toastr.error('Error al observar la postulación');
            }
        }
    });
}

function deletePostulacion(id) {
    $.ajax({
        url: default_server + "/json/postulaciones/" + id,
        type: 'DELETE',
        success: function(response) {
            if (response.success) {
                toastr.success(response.message);
                $('#deleteModal').modal('hide');
                table.ajax.reload();
            }
        },
        error: function(xhr) {
            toastr.error('Error al eliminar la postulación');
        }
    });
}

function loadStatistics() {
    // Esta función se actualizará cuando se recargue la tabla
}

function updateStatistics(data) {
    let pendientes = 0, aprobadas = 0, rechazadas = 0, observadas = 0;
    
    data.forEach(function(item) {
        switch(item.estado) {
            case 'pendiente': pendientes++; break;
            case 'aprobado': aprobadas++; break;
            case 'rechazado': rechazadas++; break;
            case 'observado': observadas++; break;
        }
    });
    
    $('#stat-pendientes').text(pendientes);
    $('#stat-aprobadas').text(aprobadas);
    $('#stat-rechazadas').text(rechazadas);
    $('#stat-observadas').text(observadas);
}