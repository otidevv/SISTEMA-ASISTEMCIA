/**
 * JavaScript para el Módulo de Carga Horaria Docente
 */

document.addEventListener('DOMContentLoaded', function () {
    // Referencias a elementos
    const selectCiclo = document.getElementById('ciclo_id');
    const selectDocente = document.getElementById('docente_id');
    const btnConsultar = document.getElementById('btn-consultar');
    const sectionResultados = document.getElementById('section-resultados');
    const sectionVacio = document.getElementById('section-vacio');
    const loadingOverlay = document.getElementById('loading-overlay');

    // Elementos de resumen
    const resumenNombre = document.getElementById('resumen-nombre');
    const resumenDocumento = document.getElementById('resumen-documento');
    const resumenAvatar = document.querySelector('#docente-avatar span');
    const resumenTarifa = document.getElementById('resumen-tarifa');
    const resumenPagoSemanal = document.getElementById('resumen-pago-semanal');
    const resumenPagoMensual = document.getElementById('resumen-pago-mensual');
    const resumenPagoTotal = document.getElementById('resumen-pago-total');

    // Botones de acción
    const btnPdfVisual = document.getElementById('btn-pdf-visual');
    const btnPdfDetallado = document.getElementById('btn-pdf-detallado');
    const btnWhatsapp = document.getElementById('btn-whatsapp');

    // Cuerpo de tablas
    const tableBody = document.getElementById('lista-horarios-body');
    const gridBody = document.getElementById('grid-horario-body');
    const labelPagoTotal = document.getElementById('label-total-semanas');

    let currentData = null;

    // Inicializar Select2 si está disponible
    if (typeof jQuery !== 'undefined' && jQuery.fn.select2) {
        $(selectDocente).select2({
            placeholder: "Busque un docente...",
            allowClear: true,
            width: '100%'
        });
    }

    // Evento Consultar
    btnConsultar.addEventListener('click', function () {
        const docenteId = selectDocente.value;
        const cicloId = selectCiclo.value;

        if (!docenteId) {
            Swal.fire({
                icon: 'warning',
                title: 'Atención',
                text: 'Por favor, seleccione un docente para continuar.'
            });
            return;
        }

        consultarCarga(docenteId, cicloId);
    });

    async function consultarCarga(docenteId, cicloId) {
        loadingOverlay.style.display = 'flex';
        sectionResultados.style.display = 'block';
        sectionVacio.style.display = 'none';

        try {
            const response = await fetch(`/json/carga-horaria/docente/${docenteId}/ciclo/${cicloId}`);
            const result = await response.json();

            if (result.success) {
                currentData = result.data;
                actualizarUI(currentData);
            } else {
                throw new Error(result.message || 'Error desconocido');
            }
        } catch (error) {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'No se pudo obtener la carga horaria: ' + error.message
            });
            sectionResultados.style.display = 'none';
            sectionVacio.style.display = 'block';
        } finally {
            loadingOverlay.style.display = 'none';
        }
    }

    function actualizarUI(data) {
        // Actualizar Resumen
        const docente = data.docente;
        resumenNombre.textContent = docente.nombre + ' ' + (docente.apellido_paterno || '') + ' ' + (docente.apellido_materno || '');
        resumenDocumento.textContent = `DNI: ${docente.numero_documento || 'No registrado'}`;
        resumenAvatar.textContent = docente.nombre.charAt(0);

        const horasBaseFormateado = data.horas_base_formateado || '0h 0m';
        const horasCicloFormateado = data.horas_totales_ciclo_formateado || '0h 0m';

        document.getElementById('resumen-horas-base').textContent = horasBaseFormateado;
        document.getElementById('resumen-horas-ciclo').textContent = horasCicloFormateado;

        // Calcular promedio de sábados si existe diferencia
        const horasPromedioSabados = (data.total_horas_semana - data.horas_base_semanal);
        const rowPromedio = document.getElementById('row-promedio-sabados');
        if (horasPromedioSabados > 0.05) { // Un margen pequeño por redondeo
            rowPromedio.classList.remove('d-none');
            document.getElementById('resumen-promedio-horas').textContent = `+${formatearHorasHumanas(horasPromedioSabados)} (promedio)`;
        } else {
            rowPromedio.classList.add('d-none');
        }

        resumenTarifa.textContent = formatearMoneda(data.tarifa_por_hora);
        resumenPagoSemanal.textContent = formatearMoneda(data.pago_semanal);
        resumenPagoMensual.textContent = formatearMoneda(data.pago_mensual);
        resumenPagoTotal.textContent = formatearMoneda(data.pago_total_ciclo);

        // Actualizar etiqueta del total ciclo
        if (labelPagoTotal) {
            labelPagoTotal.innerHTML = `<i class="mdi mdi-calculator me-2 text-danger"></i>Total Ciclo (${data.semanas_ciclo} sem)`;
        }

        // Actualizar Links PDF
        btnPdfVisual.href = `/carga-horaria/pdf-visual/${docente.id}/${data.ciclo.id}`;
        btnPdfDetallado.href = `/carga-horaria/pdf-detallado/${docente.id}/${data.ciclo.id}`;

        // Actualizar Tabla de Horarios
        tableBody.innerHTML = '';
        if (data.horarios && data.horarios.length > 0) {
            data.horarios.forEach(h => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>
                        <div class="fw-bold">${h.es_receso ? 'RECESO' : (h.curso ? h.curso.nombre : 'Sin curso')}</div>
                        <small class="text-muted">${!h.es_receso && h.curso ? h.curso.codigo : ''}</small>
                    </td>
                    <td>${h.es_receso ? '---' : (h.aula ? h.aula.nombre : 'Sin aula')}</td>
                    <td>${getNombreDia(h.dia_semana)}</td>
                    <td>${h.hora_inicio.substring(0, 5)} - ${h.hora_fin.substring(0, 5)}</td>
                    <td><span class="badge bg-soft-info text-info border border-info">${h.turno}</span></td>
                    <td class="text-end fw-bold">
                        ${h.es_receso ? '---' : `
                            ${h.horas_formateado}
                            ${h.minutos_receso_sustraidos > 0 ? `<div class="small fw-normal text-muted" style="font-size: 0.75rem;">(-${h.minutos_receso_sustraidos}m receso)</div>` : ''}
                        `}
                    </td>
                `;
                tableBody.appendChild(tr);
            });

            // Llenar Grid Horario
            actualizarGridHorario(data.horarios);
        } else {
            tableBody.innerHTML = '<tr><td colspan="6" class="text-center py-4">No se encontraron horarios registrados en este ciclo.</td></tr>';
            gridBody.innerHTML = '<tr><td colspan="7" class="text-center py-4">No hay horarios para mostrar.</td></tr>';
        }

        // Lógica WhatsApp
        btnWhatsapp.onclick = function () {
            enviarWhatsApp(data);
        };
    }

    function enviarWhatsApp(data) {
        if (!data.docente.celular && !data.docente.telefono) {
            Swal.fire({
                icon: 'warning',
                title: 'Teléfono ausente',
                text: 'El docente no tiene un número de celular registrado para enviar el mensaje.'
            });
            return;
        }

        const telefono = data.docente.celular || data.docente.telefono;
        const nombreDocente = data.docente.nombre;
        const nombreCiclo = data.ciclo.nombre;
        const totalHoras = data.total_horas_semana;
        const pagoTotal = data.pago_total_ciclo;

        // Link relativo al PDF visual
        const pdfUrl = `${window.location.origin}/carga-horaria/pdf-visual/${data.docente.id}/${data.ciclo.id}`;

        const mensaje = `Hola *${nombreDocente}* 👋\n\n` +
            `Te envío tu carga horaria para el ciclo *${nombreCiclo}*:\n\n` +
            `📚 *Total:* ${totalHoras.toFixed(1)} horas semanales\n` +
            `💰 *Pago estimado total:* ${formatearMoneda(pagoTotal)}\n\n` +
            `📄 Descarga tu horario completo aquí:\n` +
            `${pdfUrl}\n\n` +
            `¡Cualquier duda, estamos a tu disposición!`;

        const whatsappUrl = `https://wa.me/51${telefono.replace(/\s+/g, '')}?text=${encodeURIComponent(mensaje)}`;
        window.open(whatsappUrl, '_blank');
    }

    function formatearMoneda(monto) {
        return 'S/ ' + parseFloat(monto).toLocaleString('es-PE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function formatearHorasHumanas(horasDecimal) {
        if (!horasDecimal) return "0h 0m";
        const horas = Math.floor(horasDecimal);
        const minutos = Math.round((horasDecimal - horas) * 60);

        if (horas === 0 && minutos === 0) return "0h 0m";
        if (horas === 0) return `${minutos}m`;
        if (minutos === 0) return `${horas}h`;

        return `${horas}h ${minutos}m`;
    }

    function getNombreDia(dia) {
        const dias = {
            '1': 'Lunes', '2': 'Martes', '3': 'Miércoles', '4': 'Jueves', '5': 'Viernes', '6': 'Sábado', '0': 'Domingo',
            'lunes': 'Lunes', 'martes': 'Martes', 'miércoles': 'Miércoles', 'jueves': 'Jueves', 'viernes': 'Viernes', 'sábado': 'Sábado', 'domingo': 'Domingo',
            'miercoles': 'Miércoles', 'sabado': 'Sábado'
        };
        return dias[dia.toString().toLowerCase()] || dia;
    }

    function actualizarGridHorario(horarios) {
        gridBody.innerHTML = '';

        // Determinar rango de horas
        let hMin = 8, hMax = 20;
        if (horarios.length > 0) {
            horarios.forEach(h => {
                const start = parseInt(h.hora_inicio.substring(0, 2));
                const end = parseInt(h.hora_fin.substring(0, 2)) + (parseInt(h.hora_fin.substring(3, 5)) > 0 ? 1 : 0);
                if (start < hMin) hMin = start;
                if (end > hMax) hMax = end;
            });
        }

        const diasSemana = ['1', '2', '3', '4', '5', '6']; // Lunes a Sábado

        for (let h = hMin; h < hMax; h++) {
            const tr = document.createElement('tr');
            const horaStr = `${h.toString().padStart(2, '0')}:00 - ${(h + 1).toString().padStart(2, '0')}:00`;
            tr.innerHTML = `<td class="bg-light fw-bold">${horaStr}</td>`;

            diasSemana.forEach(dia => {
                const clases = horarios.filter(item => {
                    const d = item.dia_semana.toString().toLowerCase();
                    const start = parseInt(item.hora_inicio.substring(0, 2));
                    const end = parseInt(item.hora_fin.substring(0, 2)) + (parseInt(item.hora_fin.substring(3, 5)) > 0 ? 1 : 0);

                    const diaCorrespondiente = (d == dia) ||
                        (dia == '1' && d == 'lunes') ||
                        (dia == '2' && d == 'martes') ||
                        (dia == '3' && (d == 'miércoles' || d == 'miercoles')) ||
                        (dia == '4' && d == 'jueves') ||
                        (dia == '5' && d == 'viernes') ||
                        (dia == '6' && (d == 'sábado' || d == 'sabado'));

                    return diaCorrespondiente && (h >= start && h < end);
                });

                if (clases.length > 0) {
                    const c = clases[0];
                    tr.innerHTML += `
                        <td class="p-1">
                            <div class="bg-soft-primary rounded p-1" style="font-size: 0.75rem; border-left: 2px solid #7367f0;">
                                <div class="fw-bold text-truncate">${c.curso ? c.curso.nombre : '...'}</div>
                                <div class="small text-muted">${c.aula ? c.aula.nombre : 'N/A'}</div>
                            </div>
                        </td>
                    `;
                } else {
                    tr.innerHTML += `<td></td>`;
                }
            });
            gridBody.appendChild(tr);
        }
    }
});
