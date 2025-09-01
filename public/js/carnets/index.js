// public/js/carnets/index.js

// Configuración CSRF para AJAX
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

let table;
let selectedCarnets = [];

$(document).ready(function() {
    console.log('Carnets JS cargado');
    
    // Inicializar DataTables
    initDataTable();
    
    // Cargar estadísticas iniciales
    loadStatistics();
    
    // Configurar eventos
    setupEventHandlers();
    
    // Establecer fecha de vencimiento predeterminada (6 meses desde hoy)
    const fechaVencimiento = new Date();
    fechaVencimiento.setMonth(fechaVencimiento.getMonth() + 6);
    $('#masivo-fecha-vencimiento').val(fechaVencimiento.toISOString().split('T')[0]);
});

function initDataTable() {
    table = $('#carnets-datatable').DataTable({
        processing: true,
        serverSide: false,
        ajax: {
            url: default_server + "/json/carnets",
            type: 'GET',
            data: function(d) {
                d.ciclo_id = $('#filter-ciclo').val();
                d.carrera_id = $('#filter-carrera').val();
                d.turno_id = $('#filter-turno').val();
                d.aula_id = $('#filter-aula').val();
                d.estado = $('#filter-estado').val();
                d.impreso = $('#filter-impreso').val();
            },
            dataSrc: function(json) {
                // Actualizar estadísticas
                updateStatistics(json.data);
                return json.data;
            }
        },
        columns: [
            {
                data: null,
                render: function(data) {
                    return '<div class="form-check">' +
                           '<input type="checkbox" class="form-check-input carnet-checkbox" value="' + data.id + '">' +
                           '<label class="form-check-label"></label>' +
                           '</div>';
                },
                orderable: false
            },
            { data: 'codigo' },
            { data: 'estudiante' },
            { data: 'dni' },
            { data: 'ciclo' },
            { data: 'carrera' },
            { data: 'turno' },
            { data: 'aula' },
            { data: 'fecha_emision' },
            { data: 'fecha_vencimiento' },
            { 
                data: 'estado',
                render: function(data) {
                    let badgeClass = 'badge-estado-' + data;
                    return '<span class="badge ' + badgeClass + '">' + data.toUpperCase() + '</span>';
                }
            },
            {
                data: 'impreso',
                render: function(data, type, row) {
                    if (data) {
                        return '<span class="badge bg-success"><i class="uil uil-check"></i> Sí</span>' +
                               (row.fecha_impresion ? '<br><small>' + row.fecha_impresion + '</small>' : '');
                    } else {
                        return '<span class="badge bg-secondary"><i class="uil uil-times"></i> No</span>';
                    }
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
            zeroRecords: "No se encontraron carnets",
            info: "Mostrando _START_ a _END_ de _TOTAL_ carnets",
            infoEmpty: "Mostrando 0 a 0 de 0 carnets",
            infoFiltered: "(filtrado de _MAX_ carnets totales)",
            search: "Buscar:",
            paginate: {
                first: "Primero",
                last: "Último",
                previous: "<i class='uil uil-angle-left'>",
                next: "<i class='uil uil-angle-right'>"
            },
            processing: "Procesando...",
            emptyTable: "No hay carnets disponibles",
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
    
    // Limpiar filtros
    $('#btn-limpiar-filtros').on('click', function() {
        $('#filter-ciclo').val('');
        $('#filter-carrera').val('');
        $('#filter-turno').val('');
        $('#filter-aula').val('');
        $('#filter-estado').val('');
        $('#filter-impreso').val('');
        table.ajax.reload();
    });
    
    // Seleccionar todos
    $('#select-all').on('change', function() {
        $('.carnet-checkbox').prop('checked', this.checked);
        updateSelectedCarnets();
    });
    
    // Checkbox individual
    $(document).on('change', '.carnet-checkbox', function() {
        updateSelectedCarnets();
    });
    
    // Generar masivo
    $('#btn-generar-masivo').on('click', function() {
        $('#generarMasivoModal').modal('show');
    });
    
    // Confirmar generar masivo
    $('#confirmGenerarMasivo').on('click', function() {
        generarCarnetsMasivo();
    });
    
    // Ver detalle
    $(document).on('click', '.view-carnet', function() {
        const id = $(this).data('id');
        viewCarnetDetail(id);
    });
    
    // Imprimir individual
    $(document).on('click', '.print-carnet', function() {
        const id = $(this).data('id');
        printCarnets([id]);
    });
    
    // Cambiar estado
    $(document).on('click', '.change-status', function() {
        const id = $(this).data('id');
        const estadoActual = $(this).data('estado');
        openChangeStatusModal(id, estadoActual);
    });

    // Editar carnet
    $(document).on('click', '.edit-carnet', function() {
        const id = $(this).data('id');
        alert('Función de editar carnet (ID: ' + id + ') aún no implementada.');
        // Aquí iría la lógica para abrir un modal de edición
    });

    // Eliminar carnet
    $(document).on('click', '.delete-carnet', function() {
        const id = $(this).data('id');
        if (confirm('¿Está seguro de que desea anular este carnet? Esta acción no se puede deshacer.')) {
            deleteCarnet(id);
        }
    });
    
    // Mostrar/ocultar motivo anulación
    $('#nuevo-estado').on('change', function() {
        if ($(this).val() === 'anulado') {
            $('#motivo-anulacion-group').show();
            $('#motivo-anulacion').prop('required', true);
        } else {
            $('#motivo-anulacion-group').hide();
            $('#motivo-anulacion').prop('required', false);
        }
    });
    
    // Confirmar cambio de estado
    $('#confirmChangeStatus').on('click', function() {
        changeCarnetStatus();
    });
    
    // Exportar PDF seleccionados
    $('#btn-exportar-pdf').on('click', function() {
        if (selectedCarnets.length === 0) {
            toastr.warning('Debe seleccionar al menos un carnet');
            return;
        }
        exportarCarnets(selectedCarnets);
    });
    
    // Marcar como impresos
    $('#btn-marcar-impresos').on('click', function() {
        if (selectedCarnets.length === 0) {
            toastr.warning('Debe seleccionar al menos un carnet');
            return;
        }
        marcarComoImpresos(selectedCarnets);
    });
    
    // Limpiar formularios al cerrar modales
    $('.modal').on('hidden.bs.modal', function() {
        $(this).find('form')[0]?.reset();
    });
}

function updateSelectedCarnets() {
    selectedCarnets = [];
    $('.carnet-checkbox:checked').each(function() {
        selectedCarnets.push($(this).val());
    });
    
    // Habilitar/deshabilitar botones según selección
    $('#btn-exportar-pdf').prop('disabled', selectedCarnets.length === 0);
    $('#btn-marcar-impresos').prop('disabled', selectedCarnets.length === 0);
}

function generarCarnetsMasivo() {
    const formData = {
        ciclo_id: $('#masivo-ciclo').val(),
        carrera_id: $('#masivo-carrera').val(),
        turno_id: $('#masivo-turno').val(),
        aula_id: $('#masivo-aula').val(),
        fecha_vencimiento: $('#masivo-fecha-vencimiento').val()
    };
    
    // Validación
    if (!formData.ciclo_id || !formData.fecha_vencimiento) {
        toastr.error('Debe seleccionar el ciclo y la fecha de vencimiento');
        return;
    }
    
    // Deshabilitar botón
    const btn = $('#confirmGenerarMasivo');
    btn.prop('disabled', true);
    btn.html('<i class="spinner-border spinner-border-sm"></i> Generando...');
    
    $.ajax({
        url: default_server + "/json/carnets/generar-masivo",
        type: 'POST',
        data: formData,
        success: function(response) {
            if (response.success) {
                toastr.success(response.message);
                $('#generarMasivoModal').modal('hide');
                table.ajax.reload();
            } else {
                toastr.error(response.message || 'Error al generar carnets');
            }
            
            btn.prop('disabled', false);
            btn.html('<i class="uil uil-check me-1"></i> Generar Carnets');
        },
        error: function(xhr) {
            let errorMsg = 'Error al generar carnets';
            
            if (xhr.status === 422) {
                const errors = xhr.responseJSON.errors;
                for (let key in errors) {
                    toastr.error(errors[key][0]);
                }
            } else if (xhr.responseJSON && xhr.responseJSON.message) {
                toastr.error(xhr.responseJSON.message);
            } else {
                toastr.error(errorMsg);
            }
            
            btn.prop('disabled', false);
            btn.html('<i class="uil uil-check me-1"></i> Generar Carnets');
        }
    });
}

function viewCarnetDetail(id) {
    $('#viewCarnetBody').html('<p>Cargando información del carnet...</p>');
    $('#viewCarnetModal').modal('show');

    $.ajax({
        url: default_server + "/json/carnets/" + id,
        type: 'GET',
        success: function(response) {
            if (response.success) {
                const carnet = response.data;
                let content = '<div class="row">';
                content += '<div class="col-md-4 text-center">';
                content += carnet.tiene_foto ? '<img src="' + default_server + '/storage/' + carnet.foto_path + '" class="img-fluid rounded" alt="Foto">' : '<div class="alert alert-warning">Sin foto</div>';
                content += '</div>';
                content += '<div class="col-md-8">';
                content += '<p><strong>Código:</strong> ' + carnet.codigo + '</p>';
                content += '<p><strong>Estudiante:</strong> ' + carnet.estudiante + '</p>';
                content += '<p><strong>DNI:</strong> ' + carnet.dni + '</p>';
                content += '<p><strong>Ciclo:</strong> ' + carnet.ciclo + '</p>';
                content += '<p><strong>Carrera:</strong> ' + carnet.carrera + '</p>';
                content += '<p><strong>Turno:</strong> ' + carnet.turno + '</p>';
                content += '<p><strong>Aula:</strong> ' + carnet.aula + '</p>';
                content += '<p><strong>Emisión:</strong> ' + carnet.fecha_emision + '</p>';
                content += '<p><strong>Vencimiento:</strong> ' + carnet.fecha_vencimiento + '</p>';
                content += '<p><strong>Estado:</strong> <span class="badge badge-estado-' + carnet.estado + '">' + carnet.estado.toUpperCase() + '</span></p>';
                content += '<p><strong>Impreso:</strong> ' + (carnet.impreso ? 'Sí' : 'No') + '</p>';
                content += '</div>';
                content += '</div>';
                $('#viewCarnetBody').html(content);
            } else {
                $('#viewCarnetBody').html('<p class="text-danger">No se pudo cargar la información.</p>');
                toastr.error(response.message || 'Error al cargar el detalle del carnet.');
            }
        },
        error: function() {
            $('#viewCarnetBody').html('<p class="text-danger">Ocurrió un error de red.</p>');
            toastr.error('Error de red al intentar obtener los detalles del carnet.');
        }
    });
}


function openChangeStatusModal(id, estadoActual) {
    $('#status-carnet-id').val(id);
    $('#nuevo-estado').val('');
    $('#motivo-anulacion').val('');
    $('#motivo-anulacion-group').hide();
    $('#changeStatusModal').modal('show');
}

function changeCarnetStatus() {
    const id = $('#status-carnet-id').val();
    const nuevoEstado = $('#nuevo-estado').val();
    const motivo = $('#motivo-anulacion').val();
    
    if (!nuevoEstado) {
        toastr.error('Debe seleccionar un estado');
        return;
    }
    
    if (nuevoEstado === 'anulado' && !motivo) {
        toastr.error('Debe ingresar el motivo de anulación');
        return;
    }
    
    $.ajax({
        url: default_server + "/json/carnets/" + id + "/cambiar-estado",
        type: 'POST',
        data: {
            estado: nuevoEstado,
            motivo: motivo
        },
        success: function(response) {
            if (response.success) {
                toastr.success(response.message);
                $('#changeStatusModal').modal('hide');
                table.ajax.reload();
            } else {
                toastr.error(response.message || 'Error al cambiar estado');
            }
        },
        error: function(xhr) {
            toastr.error('Error al cambiar estado del carnet');
        }
    });
}

function deleteCarnet(id) {
    $.ajax({
        url: default_server + "/json/carnets/" + id,
        type: 'DELETE',
        success: function(response) {
            if (response.success) {
                toastr.success(response.message || 'Carnet anulado exitosamente.');
                table.ajax.reload();
            } else {
                toastr.error(response.message || 'No se pudo anular el carnet.');
            }
        },
        error: function(xhr) {
            let errorMsg = 'Error al anular el carnet.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMsg = xhr.responseJSON.message;
            }
            toastr.error(errorMsg);
        }
    });
}

function exportarCarnets(carnetIds) {
    // Crear un formulario temporal para enviar los IDs
    const form = $('<form>', {
        'method': 'POST',
        'action': default_server + '/carnets/exportar-pdf',
        'target': '_blank'
    });
    
    // Agregar token CSRF
    form.append($('<input>', {
        'type': 'hidden',
        'name': '_token',
        'value': $('meta[name="csrf-token"]').attr('content')
    }));
    
    // Agregar IDs de carnets
    carnetIds.forEach(function(id) {
        form.append($('<input>', {
            'type': 'hidden',
            'name': 'carnets[]',
            'value': id
        }));
    });
    
    // Agregar al body y enviar
    form.appendTo('body').submit().remove();
}

function marcarComoImpresos(carnetIds) {
    if (!confirm('¿Está seguro de marcar estos carnets como impresos?')) {
        return;
    }
    
    $.ajax({
        url: default_server + "/json/carnets/marcar-impresos",
        type: 'POST',
        data: {
            carnets: carnetIds
        },
        success: function(response) {
            if (response.success) {
                toastr.success(response.message);
                table.ajax.reload();
                selectedCarnets = [];
                $('#select-all').prop('checked', false);
                updateSelectedCarnets();
            } else {
                toastr.error(response.message || 'Error al marcar carnets');
            }
        },
        error: function(xhr) {
            toastr.error('Error al marcar carnets como impresos');
        }
    });
}

function loadStatistics() {
    // Esta función se actualizará cuando se recargue la tabla
}

function updateStatistics(data) {
    let total = data.length;
    let activos = 0, pendientes = 0, impresos = 0;
    
    data.forEach(function(item) {
        if (item.estado === 'activo') activos++;
        if (!item.impreso) pendientes++;
        if (item.impreso) impresos++;
    });
    
    $('#stat-total').text(total);
    $('#stat-activos').text(activos);
    $('#stat-pendientes').text(pendientes);
    $('#stat-impresos').text(impresos);
}

function printCarnets(carnetIds) {
    exportarCarnets(carnetIds);
}
