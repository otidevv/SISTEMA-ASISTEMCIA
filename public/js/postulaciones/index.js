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
                render: function(data) {
                    // Mostrar estado de constancia y botones de acción
                    let html = '<div class="d-flex align-items-center gap-2">';
                    
                    // Estado de constancia
                    html += data.constancia_estado;
                    
                    // Botones de acción según el estado
                    if (data.constancia_firmada) {
                        // Si tiene constancia firmada, mostrar botón para verla
                        html += ' <button class="btn btn-sm btn-success view-constancia-firmada" data-id="' + data.id + '" title="Ver constancia firmada">';
                        html += '<i class="uil uil-file-check-alt"></i></button>';
                    } else if (data.constancia_generada) {
                        // Si tiene constancia generada pero no firmada, mostrar botón para descargarla
                        html += ' <button class="btn btn-sm btn-info download-constancia" data-id="' + data.id + '" title="Descargar constancia">';
                        html += '<i class="uil uil-file-download-alt"></i></button>';
                    }
                    
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
    
    // Aprobar postulación
    $(document).on('click', '.approve-postulacion', function() {
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
            url: default_server + "/json/postulaciones/" + id + "/aprobar",
            type: 'POST',
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    
                    // Si hay información adicional, mostrarla
                    if (response.data) {
                        toastr.info('Código de inscripción: ' + response.data.codigo_inscripcion + 
                                   '<br>Aula asignada: ' + response.data.aula + 
                                   '<br>Capacidad disponible restante: ' + response.data.aula_capacidad_disponible, 
                                   'Detalles de la inscripción', 
                                   {timeOut: 8000, extendedTimeOut: 3000, escapeHtml: false});
                    }
                    
                    // Recargar la tabla
                    table.ajax.reload();
                } else {
                    toastr.error(response.message || 'Error al aprobar la postulación');
                    // Restaurar el botón
                    btn.prop('disabled', false);
                    btn.html('<i class="uil uil-check-circle"></i>');
                }
            },
            error: function(xhr) {
                let errorMsg = 'Error al aprobar la postulación';
                
                if (xhr.status === 400 || xhr.status === 403) {
                    const response = xhr.responseJSON;
                    errorMsg = response.message || response.error || errorMsg;
                } else if (xhr.status === 422) {
                    const errors = xhr.responseJSON.errors;
                    for (let key in errors) {
                        toastr.error(errors[key][0]);
                    }
                    // Restaurar el botón
                    btn.prop('disabled', false);
                    btn.html('<i class="uil uil-check-circle"></i>');
                    return;
                }
                
                toastr.error(errorMsg);
                // Restaurar el botón
                btn.prop('disabled', false);
                btn.html('<i class="uil uil-check-circle"></i>');
            }
        });
    });
    
    // Ver constancia firmada
    $(document).on('click', '.view-constancia-firmada', function() {
        const id = $(this).data('id');
        window.open(default_server + '/postulacion/constancia/ver/' + id, '_blank');
    });
    
    // Descargar constancia generada
    $(document).on('click', '.download-constancia', function() {
        const id = $(this).data('id');
        window.open(default_server + '/postulacion/constancia/generar/' + id, '_blank');
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
    
    // Editar documentos
    $(document).on('click', '.edit-documents', function() {
        const id = $(this).data('id');
        currentPostulacionId = id;
        $('#edit-docs-postulacion-id').val(id);
        loadDocumentsForEdit(id);
        $('#editDocumentsModal').modal('show');
    });
    
    // Guardar cambios de documentos
    $('#saveDocumentChanges').on('click', function() {
        saveDocumentChanges();
    });
    
    // Editar postulación aprobada
    $(document).on('click', '.edit-approved', function() {
        const id = $(this).data('id');
        currentPostulacionId = id;
        $('#edit-approved-id').val(id);
        loadApprovedPostulationForEdit(id);
        $('#editApprovedModal').modal('show');
    });
    
    // Guardar cambios de postulación aprobada
    $('#saveApprovedChanges').on('click', function() {
        saveApprovedPostulationChanges();
    });
    
    // Evento de cambio de carrera para cargar turnos
    $('#edit-approved-carrera').on('change', function() {
        const carreraId = $(this).val();
        const cicloId = $('#edit-approved-ciclo').val();
        if (carreraId && cicloId) {
            // Al cambiar carrera, limpiar turno y aula
            $('#edit-approved-turno').html('<option value="">Seleccione un turno</option>');
            $('#edit-approved-aula').html('<option value="">Seleccione un aula</option>');
            loadTurnosForCarrera(carreraId, cicloId);
        }
    });
    
    // Evento de cambio de turno para cargar aulas
    $('#edit-approved-turno').on('change', function() {
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
                const inscripcion = data.inscripcion; // Definir inscripcion aquí
                
                let html = '<div class="row">';
                
                // Información del estudiante
                html += '<div class="col-md-6">';
                html += '<h5>Información del Estudiante</h5>';
                html += '<div class="text-center mb-3">';
                const fotoPerfilUrl = postulacion.foto_path ? default_server + '/storage/' + postulacion.foto_path : null;
                if (fotoPerfilUrl) {
                    html += '<img src="' + fotoPerfilUrl + '" class="img-thumbnail" style="width: 120px; height: 120px; object-fit: cover;" alt="Foto de Perfil">';
                } else {
                    html += '<img src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" class="img-thumbnail" style="width: 120px; height: 120px; object-fit: cover;" alt="Sin Foto">';
                }
                html += '</div>';
                html += '<table class="table table-sm">';
                html += '<tr><td><strong>Nombre:</strong></td><td>' + postulacion.estudiante.nombre + ' ' + 
                        postulacion.estudiante.apellido_paterno + ' ' + postulacion.estudiante.apellido_materno + '</td></tr>';
                html += '<tr><td><strong>DNI:</strong></td><td>' + (postulacion.estudiante.numero_documento || 'N/A') + '</td></tr>';
                html += '<tr><td><strong>Email:</strong></td><td>' + (postulacion.estudiante.email || 'N/A') + '</td></tr>';
                html += '<tr><td><strong>Teléfono:</strong></td><td>' + (postulacion.estudiante.telefono || 'N/A') + '</td></tr>';
                html += '<tr><td><strong>Fecha Nacimiento:</strong></td><td>' + (postulacion.estudiante.fecha_nacimiento ? new Date(postulacion.estudiante.fecha_nacimiento).toLocaleDateString() : 'N/A') + '</td></tr>';
                html += '<tr><td><strong>Género:</strong></td><td>' + (postulacion.estudiante.genero ? (postulacion.estudiante.genero === 'M' ? 'Masculino' : 'Femenino') : 'N/A') + '</td></tr>';
                html += '<tr><td><strong>Dirección:</strong></td><td>' + (postulacion.estudiante.direccion || 'N/A') + '</td></tr>';
                html += '<tr><td><strong>Centro Educativo:</strong></td><td>' + (postulacion.centro_educativo?.nombre || 'N/A') + '</td></tr>';
                html += '</table>';
                html += '</div>';
                
                // Información de la postulación
                html += '<div class="col-md-6">';
                html += '<h5>Información de la Postulación</h5>';
                html += '<table class="table table-sm">';
                html += '<tr><td><strong>Código:</strong></td><td>' + postulacion.codigo_postulante + '</td></tr>';
                html += '<tr><td><strong>Ciclo:</strong></td><td>' + postulacion.ciclo.nombre + '</td></tr>';
                html += '<tr><td><strong>Carrera:</strong></td><td>' + postulacion.carrera.nombre + '</td></tr>';
                html += '<tr><td><strong>Turno:</strong></td><td>' + postulacion.turno.nombre + '</td></tr>';
                html += '<tr><td><strong>Tipo:</strong></td><td>' + postulacion.tipo_inscripcion + '</td></tr>';
                html += '<tr><td><strong>Estado:</strong></td><td><span class="badge badge-estado-' + 
                        postulacion.estado + '"> ' + postulacion.estado.toUpperCase() + '</span></td></tr>';
                // Mostrar aula si la postulación está aprobada y hay inscripción
                if (inscripcion && inscripcion.aula) {
                    html += '<tr><td><strong>Aula Asignada:</strong></td><td>' + inscripcion.aula.nombre + '</td></tr>';
                }
                html += '</table>';
                html += '</div>';
                
                // Documentos
                html += '<div class="col-md-6">';
                html += '<h5>Documentos Subidos ';
                html += '<button class="btn btn-sm btn-warning ms-2 edit-documents" data-id="' + postulacion.id + '" title="Editar documentos">';
                html += '<i class="uil uil-edit"></i> Editar</button>';
                html += '</h5>';
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
                
                // Información de la constancia
                html += '<div class="col-md-6">';
                html += '<h5>Constancia de Inscripción</h5>';
                html += '<div class="p-3 bg-light rounded">';
                
                if (postulacion.constancia_firmada) {
                    html += '<p class="text-success"><i class="uil uil-check-circle"></i> <strong>Constancia firmada y subida</strong></p>';
                    html += '<p>Fecha subida: ' + postulacion.fecha_constancia_subida + '</p>';
                    html += '<button class="btn btn-success btn-sm view-constancia-firmada" data-id="' + postulacion.id + '">';
                    html += '<i class="uil uil-file-check-alt"></i> Ver Constancia Firmada</button>';
                } else if (postulacion.constancia_generada) {
                    html += '<p class="text-warning"><i class="uil uil-file-download-alt"></i> <strong>Constancia generada, pendiente de firma</strong></p>';
                    html += '<p>Fecha generación: ' + postulacion.fecha_constancia_generada + '</p>';
                    html += '<button class="btn btn-info btn-sm download-constancia" data-id="' + postulacion.id + '">';
                    html += '<i class="uil uil-download-alt"></i> Descargar Constancia</button>';
                } else {
                    html += '<p class="text-secondary"><i class="uil uil-times-circle"></i> <strong>Constancia no generada</strong></p>';
                    html += '<p>El postulante aún no ha generado su constancia de inscripción.</p>';
                }
                
                html += '</div>';
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

function loadDocumentsForEdit(id) {
    $.ajax({
        url: default_server + "/json/postulaciones/" + id,
        type: 'GET',
        success: function(response) {
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
        error: function(xhr) {
            toastr.error('Error al cargar los documentos');
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
    $('.doc-file-input').each(function() {
        const file = this.files[0];
        if (file) {
            const docType = $(this).data('doc-type');
            formData.append(docType, file);
            hasFiles = true;
        }
    });
    
    if (!hasFiles) {
        toastr.warning('No ha seleccionado ningún archivo para actualizar');
        return;
    }
    
    // Deshabilitar botón mientras se procesa
    const btn = $('#saveDocumentChanges');
    btn.prop('disabled', true);
    btn.html('<i class="spinner-border spinner-border-sm"></i> Guardando...');
    
    $.ajax({
        url: default_server + "/json/postulaciones/" + postulacionId + "/actualizar-documentos",
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.success) {
                toastr.success('Documentos actualizados correctamente');
                $('#editDocumentsModal').modal('hide');
                
                // Recargar el modal de detalle si está abierto
                if ($('#viewModal').hasClass('show')) {
                    viewPostulacion(postulacionId);
                }
                
                // Recargar la tabla
                table.ajax.reload();
            } else {
                toastr.error(response.message || 'Error al actualizar los documentos');
            }
            
            // Restaurar botón
            btn.prop('disabled', false);
            btn.html('<i class="uil uil-save me-1"></i> Guardar Cambios');
        },
        error: function(xhr) {
            let errorMsg = 'Error al actualizar los documentos';
            
            if (xhr.status === 422) {
                const errors = xhr.responseJSON.errors;
                for (let key in errors) {
                    toastr.error(errors[key][0]);
                }
            } else if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMsg = xhr.responseJSON.message;
                toastr.error(errorMsg);
            } else {
                toastr.error(errorMsg);
            }
            
            // Restaurar botón
            btn.prop('disabled', false);
            btn.html('<i class="uil uil-save me-1"></i> Guardar Cambios');
        }
    });
}

// Función para cargar datos de postulación aprobada para editar
function loadApprovedPostulationForEdit(id) {
    $.ajax({
        url: default_server + "/json/postulaciones/" + id + "/editar-aprobada",
        type: 'GET',
        success: function(response) {
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
                
                // Llenar datos académicos
                $('#edit-approved-ciclo').val(data.postulacion.ciclo_id);
                $('#edit-approved-carrera').val(data.postulacion.carrera_id);
                $('#edit-approved-tipo').val(data.postulacion.tipo_inscripcion);
                
                // Cargar turnos disponibles y seleccionar el actual
                // Pasamos false como cuarto parámetro para evitar el trigger del evento change
                loadTurnosForCarrera(data.postulacion.carrera_id, data.postulacion.ciclo_id, data.postulacion.turno_id, false);
                
                // Cargar aulas directamente con el turno y aula seleccionados
                setTimeout(function() {
                    loadAulasForTurno(data.postulacion.turno_id, data.postulacion.carrera_id, data.postulacion.ciclo_id, aulaActual);
                }, 500);
                
                // Llenar datos de pago
                $('#edit-approved-recibo').val(data.postulacion.numero_recibo);
                $('#edit-approved-matricula').val(data.postulacion.monto_matricula);
                $('#edit-approved-ensenanza').val(data.postulacion.monto_ensenanza);
            }
        },
        error: function(xhr) {
            toastr.error('Error al cargar los datos de la postulación');
            $('#editApprovedModal').modal('hide');
        }
    });
}

// Función para cargar turnos disponibles
function loadTurnosForCarrera(carreraId, cicloId, selectedTurnoId = null, triggerChange = true) {
    $.ajax({
        url: default_server + "/json/turnos/por-carrera",
        type: 'GET',
        data: {
            carrera_id: carreraId,
            ciclo_id: cicloId
        },
        success: function(response) {
            let html = '<option value="">Seleccione un turno</option>';
            if (response.data && response.data.length > 0) {
                response.data.forEach(function(turno) {
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
        url: default_server + "/json/aulas/disponibles",
        type: 'GET',
        data: {
            turno_id: turnoId,
            carrera_id: carreraId,
            ciclo_id: cicloId
        },
        success: function(response) {
            let html = '<option value="">Seleccione un aula</option>';
            let aulaEncontrada = false;
            
            if (response.data && response.data.length > 0) {
                response.data.forEach(function(aula) {
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
                    url: default_server + "/json/aulas/" + selectedAulaId,
                    type: 'GET',
                    success: function(aulaResponse) {
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
        error: function() {
            $('#edit-approved-aula').html('<option value="">Error al cargar aulas</option>');
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
        numero_recibo: $('#edit-approved-recibo').val(),
        monto_matricula: $('#edit-approved-matricula').val(),
        monto_ensenanza: $('#edit-approved-ensenanza').val(),
        observacion_cambio: $('#edit-approved-observacion').val()
    };
    
    // Validar observación
    if (!formData.observacion_cambio || formData.observacion_cambio.length < 10) {
        toastr.error('Debe explicar el motivo de la modificación (mínimo 10 caracteres)');
        return;
    }
    
    // Deshabilitar botón mientras se procesa
    const btn = $('#saveApprovedChanges');
    btn.prop('disabled', true);
    btn.html('<i class="spinner-border spinner-border-sm"></i> Guardando...');
    
    $.ajax({
        url: default_server + "/json/postulaciones/" + currentPostulacionId + "/actualizar-aprobada",
        type: 'PUT',
        data: formData,
        success: function(response) {
            if (response.success) {
                toastr.success('Postulación actualizada correctamente');
                
                if (response.message) {
                    toastr.info(response.message);
                }
                
                $('#editApprovedModal').modal('hide');
                table.ajax.reload();
            } else {
                toastr.error(response.message || 'Error al actualizar la postulación');
            }
            
            // Restaurar botón
            btn.prop('disabled', false);
            btn.html('<i class="uil uil-save me-1"></i> Guardar Cambios');
        },
        error: function(xhr) {
            if (xhr.status === 422) {
                const errors = xhr.responseJSON.errors;
                for (let key in errors) {
                    toastr.error(errors[key][0]);
                }
            } else {
                toastr.error('Error al actualizar la postulación');
            }
            
            // Restaurar botón
            btn.prop('disabled', false);
            btn.html('<i class="uil uil-save me-1"></i> Guardar Cambios');
        }
    });
}