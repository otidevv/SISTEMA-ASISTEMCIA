$(document).ready(function() {
    // Configurar fecha mínima y máxima según el ciclo seleccionado
    $('#ciclo_id').on('change', function() {
        const selectedOption = $(this).find('option:selected');
        const fechaInicio = selectedOption.data('inicio');
        const fechaFin = selectedOption.data('fin');
        const examen1 = selectedOption.data('examen1');
        const examen2 = selectedOption.data('examen2');
        const examen3 = selectedOption.data('examen3');

        if (fechaInicio && fechaFin) {
            $('#fecha_reporte').attr('min', fechaInicio);
            $('#fecha_reporte').attr('max', fechaFin);
            $('#infoFecha').text(`Rango permitido: ${fechaInicio} al ${fechaFin}`);

            // Mostrar información de exámenes
            $('#examen1Info span').text(examen1 || 'No programado');
            $('#examen2Info span').text(examen2 || 'No programado');
            $('#examen3Info span').text(examen3 || 'No programado');
            $('#infoExamenes').show();
        } else {
            $('#fecha_reporte').removeAttr('min').removeAttr('max');
            $('#infoFecha').text('');
            $('#infoExamenes').hide();
        }
    });

    // Verificar si la fecha seleccionada es un examen
    $('#fecha_reporte').on('change', function() {
        const fecha = $(this).val();
        const selectedOption = $('#ciclo_id option:selected');
        const examen1 = selectedOption.data('examen1');
        const examen2 = selectedOption.data('examen2');
        const examen3 = selectedOption.data('examen3');

        if (fecha === examen1 || fecha === examen2 || fecha === examen3) {
            $('#tipo_reporte').val('resumen_examen');
            toastr.info('Fecha de examen detectada. Tipo de reporte cambiado a "Resumen para Examen"');
        }
    });

    // Generar Reporte
    $('#btnGenerarReporte').on('click', function() {
        if (!$('#formReporteAsistencia')[0].checkValidity()) {
            $('#formReporteAsistencia')[0].reportValidity();
            return;
        }

        const formData = {
            ciclo_id: $('#ciclo_id').val(),
            carrera_id: $('#carrera_id').val(),
            turno_id: $('#turno_id').val(),
            aula_id: $('#aula_id').val(),
            fecha_reporte: $('#fecha_reporte').val(),
            tipo_reporte: $('#tipo_reporte').val(),
            formato: $('#formato').val(),
            _token: $('meta[name="csrf-token"]').attr('content') // Agregar esta línea

        };

        $('#loadingModal').modal('show');
        console.log('URL:', '/json/reportes/asistenciadia');

        $.ajax({
            url: '/json/reportes/asistencia-dia',
            method: 'POST',
            data: formData,
            xhrFields: {
                responseType: 'blob'
            },
            success: function(data, status, xhr) {
                $('#loadingModal').modal('hide');

                // Crear enlace de descarga
                const blob = new Blob([data], {
                    type: formData.formato === 'pdf' ? 'application/pdf' : 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                });
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `reporte_asistencia_${formData.fecha_reporte}.${formData.formato}`;
                document.body.appendChild(a);
                a.click();
                window.URL.revokeObjectURL(url);
                document.body.removeChild(a);

                toastr.success('Reporte generado exitosamente');
            },
            error: function(xhr) {
                $('#loadingModal').modal('hide');
                toastr.error('Error al generar el reporte');
            }
        });
    });

    // Vista Previa
    $('#btnVistaPrevia').on('click', function() {
        if (!$('#formReporteAsistencia')[0].checkValidity()) {
            $('#formReporteAsistencia')[0].reportValidity();
            return;
        }

        const formData = {
            ciclo_id: $('#ciclo_id').val(),
            carrera_id: $('#carrera_id').val(),
            turno_id: $('#turno_id').val(),
            aula_id: $('#aula_id').val(),
            fecha_reporte: $('#fecha_reporte').val(),
            tipo_reporte: $('#tipo_reporte').val()
        };

        $('#loadingModal').modal('show');

        $.ajax({
            url: '/json/reportes/asistencia-dia/preview',
            method: 'POST',
            data: formData,
            success: function(response) {
                $('#loadingModal').modal('hide');
                $('#contenidoVistaPrevia').html(response.html);
                $('#vistaPrevia').show();

                // Scroll a la vista previa
                $('html, body').animate({
                    scrollTop: $('#vistaPrevia').offset().top - 100
                }, 500);
            },
            error: function(xhr) {
                $('#loadingModal').modal('hide');
                toastr.error('Error al generar la vista previa');
            }
        });
    });
});
