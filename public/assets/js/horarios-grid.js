/**
 * Grilla Visual de Horarios - JavaScript
 * Maneja drag & drop, creación, edición y eliminación de horarios
 */

// Estado global
let horariosActuales = [];
let cambiosPendientes = [];
let horariosEliminados = [];
let gridCellIndex = new Map();
let horarioEnEdicionId = null;
let horarioEnDragId = null;

// Colores por curso (se generarán dinámicamente)
const cursosColores = {};

// Inicializar cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function () {
    aplicarFiltrosDesdeUrl();
    inicializarColoresCursos();
    indexarCeldasGrilla();
    cargarHorarios();
    inicializarEventos();
});

/**
 * Inicializar colores de cursos
 */
function inicializarColoresCursos() {
    if (window.scheduleData && window.scheduleData.cursos) {
        window.scheduleData.cursos.forEach(curso => {
            cursosColores[curso.id] = curso.color || generarColorAleatorio();
        });
    }
}

/**
 * Generar color aleatorio vibrante
 */
function generarColorAleatorio() {
    const colores = [
        '#7367f0', '#28c76f', '#ff9f43', '#ea5455', '#00cfe8',
        '#8e44ad', '#e74c3c', '#3498db', '#2ecc71', '#f39c12',
        '#9b59b6', '#1abc9c', '#e67e22', '#c0392b', '#16a085'
    ];
    return colores[Math.floor(Math.random() * colores.length)];
}

/**
 * Normalizar dÃ­a de la semana para comparaciÃ³n de celdas
 */
function normalizarDiaKey(dia) {
    if (dia === null || dia === undefined) return '';

    // Si viene como nÃºmero (0-6), mapear a nombre de dÃ­a
    if (typeof dia === 'number' || /^\d+$/.test(String(dia))) {
        const num = Number(dia);
        const dias = ['domingo', 'lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado'];
        return dias[num] || String(dia);
    }

    // Normalizar texto (sin tildes, minÃºsculas, trim)
    return String(dia)
        .trim()
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '');
}

/**
 * Normalizar hora (HH:mm) para comparaciÃ³n de celdas
 */
function normalizarHoraKey(hora) {
    if (!hora) return '';
    const partes = String(hora).trim().split(':');
    if (partes.length >= 2) {
        const h = String(partes[0]).padStart(2, '0');
        const m = String(partes[1]).padStart(2, '0');
        return `${h}:${m}`;
    }
    return String(hora).trim();
}

/**
 * Indexar celdas de la grilla por dÃ­a y hora (normalizados)
 */
function indexarCeldasGrilla() {
    gridCellIndex = new Map();
    document.querySelectorAll('.grid-cell[data-dia][data-hora]').forEach(cell => {
        const diaKey = normalizarDiaKey(cell.dataset.dia);
        const horaKey = normalizarHoraKey(cell.dataset.hora);
        const key = `${diaKey}|${horaKey}`;
        gridCellIndex.set(key, cell);
    });
}

/**
 * Cargar horarios desde el servidor
 */
async function cargarHorarios(mostrarAlerta = true) {
    const cicloId = document.getElementById('filtro-ciclo').value;
    const aulaId = document.getElementById('filtro-aula').value;
    const turno = document.getElementById('filtro-turno').value;

    actualizarUrlConFiltros(cicloId, aulaId, turno);

    if (!cicloId || !aulaId || !turno) {
        Swal.fire('Atención', 'Por favor seleccione ciclo, aula y turno', 'warning');
        return;
    }

    mostrarCargando(true);

    try {
        const response = await fetch(`${window.scheduleData.routes.getSchedules}?ciclo_id=${cicloId}&aula_id=${aulaId}&turno=${turno}`);
        const data = await response.json();

        horariosActuales = data;
        renderizarHorarios();
        actualizarEstadisticas();

        if (mostrarAlerta) {
            Swal.fire({
                icon: 'success',
                title: 'Horarios cargados',
                text: `Se cargaron ${data.length} horarios`,
                timer: 2000,
                showConfirmButton: false
            });
        }
    } catch (error) {
        Swal.fire('Error', 'No se pudieron cargar los horarios', 'error');
    } finally {
        mostrarCargando(false);
    }
}

/**
 * Renderizar horarios en la grilla
 */
function renderizarHorarios() {
    // Limpiar grilla
    document.querySelectorAll('.grid-cell:not(.header):not(.time-slot)').forEach(cell => {
        cell.innerHTML = '';
        cell.style.position = 'relative';
    });

    // Renderizar cada horario
    horariosActuales.forEach(horario => {
        // Verificar si el horario atraviesa un receso (10:00-10:30 o 18:00-18:30)
        const [hInicio, mInicio] = horario.hora_inicio.split(':').map(Number);
        const [hFin, mFin] = horario.hora_fin.split(':').map(Number);
        const minInicio = hInicio * 60 + mInicio;
        const minFin = hFin * 60 + mFin;

        // Horas de receso en minutos
        const receso1Inicio = 10 * 60; // 10:00
        const receso1Fin = 10 * 60 + 30; // 10:30
        const receso2Inicio = 18 * 60; // 18:00
        const receso2Fin = 18 * 60 + 30; // 18:30

        // Verificar si atraviesa el receso de las 10:00
        if (minInicio < receso1Inicio && minFin > receso1Fin) {
            // Dividir en dos bloques
            renderizarBloqueSimple({
                ...horario,
                hora_fin: '10:00'
            }, true);
            renderizarBloqueSimple({
                ...horario,
                hora_inicio: '10:30'
            }, true);
        }
        // Verificar si atraviesa el receso de las 18:00
        else if (minInicio < receso2Inicio && minFin > receso2Fin) {
            // Dividir en dos bloques
            renderizarBloqueSimple({
                ...horario,
                hora_fin: '18:00'
            }, true);
            renderizarBloqueSimple({
                ...horario,
                hora_inicio: '18:30'
            }, true);
        }
        else {
            // Renderizar normalmente
            renderizarBloqueSimple(horario, true);
        }
    });
}

/**
 * Renderizar un bloque simple (sin dividir)
 */
function renderizarBloqueSimple(horario, mostrarInfo) {
    const cellInicio = encontrarCelda(horario.dia_semana, horario.hora_inicio);
    if (cellInicio) {
        // Calcular cuántas celdas debe abarcar
        const duracionHoras = calcularDuracionHoras(horario.hora_inicio, horario.hora_fin);
        const celdasACubrir = Math.ceil(duracionHoras);

        // Obtener la altura real de la celda ANTES de agregar el bloque
        const alturaCelda = cellInicio.offsetHeight;
        const gap = 1; // Gap entre celdas

        // Calcular altura total en píxeles
        const alturaTotal = (alturaCelda * celdasACubrir) + (gap * (celdasACubrir - 1));

        const block = crearBloqueHorario(horario, mostrarInfo);
        cellInicio.appendChild(block);

        if (celdasACubrir >= 1) {
            // Hacer que el bloque abarque múltiples filas
            block.style.position = 'absolute';
            block.style.top = '0';
            block.style.left = '0';
            block.style.right = '0';
            block.style.bottom = 'auto';
            block.style.height = `${alturaTotal}px`;
            block.style.maxHeight = `${alturaTotal}px`;
            block.style.overflow = 'hidden';
            block.style.zIndex = '10';
        }
    }
}

/**
 * Calcular duración en horas
 */
function calcularDuracionHoras(horaInicio, horaFin) {
    const [hInicio, mInicio] = horaInicio.split(':').map(Number);
    const [hFin, mFin] = horaFin.split(':').map(Number);

    const minutosInicio = hInicio * 60 + mInicio;
    const minutosFin = hFin * 60 + mFin;

    return (minutosFin - minutosInicio) / 60;
}

/**
 * Crear bloque visual de horario
 */
function crearBloqueHorario(horario, mostrarInfo = true) {
    const block = document.createElement('div');
    block.className = 'schedule-block';
    if (horarioEnEdicionId && String(horario.id) === String(horarioEnEdicionId)) {
        block.classList.add('editing');
    }
    block.draggable = true;
    block.dataset.horarioId = horario.id || 'temp-' + Date.now();
    block.dataset.cursoId = horario.curso_id;
    block.dataset.docenteId = horario.docente_id;
    block.dataset.horaInicio = horario.hora_inicio;
    block.dataset.horaFin = horario.hora_fin;

    // Detectar si es un receso o curso eliminado
    const esReceso = !horario.curso_id ||
        (horario.curso_nombre && (
            horario.curso_nombre.toLowerCase().includes('receso') ||
            horario.curso_nombre === 'RECESO' ||
            horario.curso_nombre === 'Sin curso'
        ));

    // Obtener color: priorizar curso_color de la BD, luego cursosColores, luego default
    let color = '#7367f0'; // Default
    if (esReceso) {
        color = '#10b981';
    } else if (horario.curso_color) {
        color = horario.curso_color;
    } else if (horario.curso_id && cursosColores[horario.curso_id]) {
        color = cursosColores[horario.curso_id];
    }

    block.style.background = esReceso
        ? 'linear-gradient(135deg, #10b981 0%, #059669 100%)'
        : `linear-gradient(135deg, ${color} 0%, ${ajustarBrillo(color, -20)} 100%)`;

    const duracion = calcularDuracionHoras(horario.hora_inicio, horario.hora_fin);
    const fontSize = duracion >= 2 ? '0.95rem' : (duracion >= 1 ? '0.9rem' : '0.8rem');

    block.innerHTML = `
        <button class="delete-btn" onclick="eliminarHorario('${horario.id || block.dataset.horarioId}')">
            <i class="mdi mdi-close"></i>
        </button>
        <div class="course-name" style="font-size: ${fontSize};">${horario.curso_nombre === 'Sin curso' ? 'RECESO' : horario.curso_nombre}</div>
        ${!esReceso && mostrarInfo ? `
        <div class="teacher-name">
            <i class="mdi mdi-account"></i>
            ${horario.docente_nombre}
        </div>` : ''}
        <div class="time-range">
            <i class="mdi mdi-clock-outline"></i>
            ${horario.hora_inicio} - ${horario.hora_fin}
        </div>
    `;

    // Evento click para editar
    block.addEventListener('click', function (e) {
        // Evitar que se active si se hace click en el botón de eliminar
        if (e.target.closest('.delete-btn')) return;

        abrirModalEdicion(horario);
    });

    // Eventos drag
    block.addEventListener('dragstart', function (e) {
        e.dataTransfer.setData('horarioId', this.dataset.horarioId);
        this.style.opacity = '0.5';
        horarioEnDragId = this.dataset.horarioId;
    });

    block.addEventListener('dragend', function (e) {
        this.style.opacity = '1';
        horarioEnDragId = null;
    });

    return block;
}

/**
 * Encontrar celda por día y hora
 */
function encontrarCelda(dia, hora) {
    const diaKey = normalizarDiaKey(dia);
    const horaKey = normalizarHoraKey(hora);
    const key = `${diaKey}|${horaKey}`;
    return gridCellIndex.get(key) || null;
}

/**
 * Ajustar brillo de color
 */
function ajustarBrillo(hex, percent) {
    const num = parseInt(hex.replace('#', ''), 16);
    const amt = Math.round(2.55 * percent);
    const R = (num >> 16) + amt;
    const G = (num >> 8 & 0x00FF) + amt;
    const B = (num & 0x0000FF) + amt;
    return '#' + (0x1000000 + (R < 255 ? R < 1 ? 0 : R : 255) * 0x10000 +
        (G < 255 ? G < 1 ? 0 : G : 255) * 0x100 +
        (B < 255 ? B < 1 ? 0 : B : 255))
        .toString(16).slice(1);
}

/**
 * Utilidades de horarios
 */
function esRecesoHorario(horario) {
    return !horario.curso_id ||
        (horario.curso_nombre && (
            horario.curso_nombre.toLowerCase().includes('receso') ||
            horario.curso_nombre === 'RECESO' ||
            horario.curso_nombre === 'Sin curso'
        ));
}

function parseMinutos(hhmm) {
    const [h, m] = String(hhmm).split(':').map(Number);
    return (h * 60) + (m || 0);
}

function haySolapamiento(h1Inicio, h1Fin, h2Inicio, h2Fin) {
    return h1Inicio < h2Fin && h1Fin > h2Inicio;
}

function obtenerConflictoLocal(nuevo, excluirId = null) {
    if (esRecesoHorario(nuevo)) return null;
    const nuevoInicio = parseMinutos(nuevo.hora_inicio);
    const nuevoFin = parseMinutos(nuevo.hora_fin);

    for (const existente of horariosActuales) {
        if (excluirId && String(existente.id) === String(excluirId)) continue;
        if (esRecesoHorario(existente)) continue;

        const mismoDia = normalizarDiaKey(existente.dia_semana) === normalizarDiaKey(nuevo.dia_semana);
        const mismoTurno = String(existente.turno) === String(nuevo.turno);
        const mismoCiclo = String(existente.ciclo_id) === String(nuevo.ciclo_id);
        const mismoAula = String(existente.aula_id) === String(nuevo.aula_id);
        const mismoDocente = String(existente.docente_id) === String(nuevo.docente_id);

        if (!mismoDia || !mismoTurno || !mismoCiclo) continue;

        const exInicio = parseMinutos(existente.hora_inicio);
        const exFin = parseMinutos(existente.hora_fin);

        if (haySolapamiento(nuevoInicio, nuevoFin, exInicio, exFin) && (mismoAula || mismoDocente)) {
            return existente;
        }
    }

    return null;
}

function hayConflictoLocal(nuevo, excluirId = null) {
    return !!obtenerConflictoLocal(nuevo, excluirId);
}

function construirHtmlConflictos(errores) {
    if (!Array.isArray(errores)) return '';
    const items = [];
    errores.forEach((item, idx) => {
        const detalle = item?.errores || {};
        Object.keys(detalle).forEach((campo) => {
            const mensajes = Array.isArray(detalle[campo]) ? detalle[campo] : [detalle[campo]];
            mensajes.forEach(msg => {
                items.push(`<li>Horario #${(idx + 1)} (${campo}): ${msg}</li>`);
            });
        });
    });
    if (items.length === 0) return '';
    return `<ul style="text-align:left; padding-left: 18px; margin: 0;">${items.join('')}</ul>`;
}

function normalizarHorarioKey(h) {
    return [
        h.docente_id || '',
        h.curso_id || '',
        h.aula_id || '',
        h.ciclo_id || '',
        normalizarDiaKey(h.dia_semana || ''),
        h.hora_inicio || '',
        h.hora_fin || '',
        h.turno || '',
        h.grupo || ''
    ].join('|');
}

async function actualizarHorarioServidor(horario) {
    const payload = {
        docente_id: horario.docente_id,
        curso_id: horario.curso_id,
        aula_id: horario.aula_id,
        ciclo_id: horario.ciclo_id,
        dia_semana: horario.dia_semana,
        hora_inicio: horario.hora_inicio,
        hora_fin: horario.hora_fin,
        turno: horario.turno,
        grupo: horario.grupo || null
    };

    const response = await fetch(`${window.location.origin}/json/horarios-docentes/${horario.id}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify(payload)
    });

    if (!response.ok) {
        const contentType = response.headers.get('content-type') || '';
        if (response.status === 422 && contentType.includes('application/json')) {
            const data = await response.json();
            const html = construirHtmlConflictos(data.errores || []);
            const message = data.message || 'Se encontraron conflictos en algunos horarios';
            Swal.fire({ icon: 'error', title: 'Conflicto', html: html || message });
            throw new Error(message);
        }

        throw new Error(`Error al actualizar horario (${response.status})`);
    }

    return response;
}

/**
 * Drag & Drop - Permitir drop
 */
function allowDrop(ev) {
    ev.preventDefault();
    const cell = ev.currentTarget;
    let isInvalid = false;

    if (horarioEnDragId && cell?.dataset?.dia && cell?.dataset?.hora) {
        const horario = horariosActuales.find(h => h.id == horarioEnDragId || `temp-${h.id}` == horarioEnDragId);
        if (horario && !esRecesoHorario(horario)) {
            const candidato = {
                ...horario,
                dia_semana: cell.dataset.dia,
                hora_inicio: cell.dataset.hora
            };
            isInvalid = hayConflictoLocal(candidato, horario.id);
        }
    }

    if (isInvalid) {
        cell.classList.add('invalid-drop');
        cell.style.background = '#ffe6e6';
        cell.dataset.invalidDrop = '1';
    } else {
        cell.classList.remove('invalid-drop');
        cell.style.background = '#f0f7ff';
        cell.dataset.invalidDrop = '0';
    }
}

/**
 * Drag & Drop - Iniciar arrastre desde sidebar
 */
function drag(ev) {
    const item = (ev.currentTarget && ev.currentTarget.classList.contains('course-item'))
        ? ev.currentTarget
        : ev.target.closest('.course-item');
    if (!item) return;

    const cursoId = item.dataset.cursoId;
    const cursoNombre = item.dataset.cursoNombre;
    ev.dataTransfer.setData('cursoId', cursoId);
    ev.dataTransfer.setData('cursoNombre', cursoNombre);
    ev.dataTransfer.setData('fromSidebar', 'true');
}

/**
 * Drag & Drop - Soltar
 */
function drop(ev) {
    ev.preventDefault();
    ev.currentTarget.style.background = '';
    ev.currentTarget.classList.remove('invalid-drop');

    const fromSidebar = ev.dataTransfer.getData('fromSidebar');

    if (fromSidebar === 'true') {
        // Arrastrado desde sidebar - abrir modal
        const dia = ev.currentTarget.dataset.dia;
        const hora = ev.currentTarget.dataset.hora;
        const cursoId = ev.dataTransfer.getData('cursoId');
        const cursoNombre = ev.dataTransfer.getData('cursoNombre');

        abrirModalCreacion(dia, hora, cursoId);
    } else {
        // Mover horario existente
        if (ev.currentTarget.dataset.invalidDrop === '1') {
            Swal.fire('Error', 'Conflicto detectado: el horario no se puede mover a esa celda.', 'error');
            ev.currentTarget.dataset.invalidDrop = '0';
            return;
        }
        const horarioId = ev.dataTransfer.getData('horarioId');
        moverHorario(horarioId, ev.currentTarget);
    }
}

/**
 * Abrir modal de creación rápida
 */
function abrirModalCreacion(dia, hora, cursoId = null) {
    horarioEnEdicionId = null;
    document.getElementById('modal-dia').value = dia;
    document.getElementById('modal-hora-inicio').value = hora;

    if (cursoId) {
        document.getElementById('modal-curso').value = cursoId;
    }

    // Calcular hora fin sugerida (1 hora después)
    const horaInicio = hora.split(':');
    const horaFin = `${String(parseInt(horaInicio[0]) + 1).padStart(2, '0')}:${horaInicio[1]}`;
    document.getElementById('modal-hora-fin').value = horaFin;

    const modal = new bootstrap.Modal(document.getElementById('quickCreateModal'));
    modal.show();
}

/**
 * Abrir modal de edición
 */
function abrirModalEdicion(horario) {
    horarioEnEdicionId = horario.id || null;
    // Pre-llenar el modal con los datos del horario
    document.getElementById('modal-dia').value = horario.dia_semana;
    document.getElementById('modal-hora-inicio').value = horario.hora_inicio;
    document.getElementById('modal-hora-fin').value = horario.hora_fin;
    document.getElementById('modal-grupo').value = horario.grupo || '';

    // Si es receso
    const esReceso = horario.curso_nombre && (horario.curso_nombre.toLowerCase().includes('receso') || horario.curso_nombre === 'RECESO');

    if (esReceso) {
        document.getElementById('modal-es-receso').checked = true;
        document.getElementById('curso-field').style.display = 'none';
        document.getElementById('modal-curso').removeAttribute('required');
        document.getElementById('docente-field').style.display = 'none';
        document.getElementById('modal-docente').removeAttribute('required');
        document.getElementById('dias-receso-field').style.display = 'block';

        // Marcar el día actual
        document.querySelectorAll('.dia-checkbox').forEach(cb => {
            cb.checked = cb.value === horario.dia_semana;
        });
    } else {
        document.getElementById('modal-es-receso').checked = false;
        document.getElementById('modal-curso').value = horario.curso_id;
        document.getElementById('modal-docente').value = horario.docente_id;
        document.getElementById('curso-field').style.display = 'block';
        document.getElementById('modal-curso').setAttribute('required', 'required');
        document.getElementById('docente-field').style.display = 'block';
        document.getElementById('modal-docente').setAttribute('required', 'required');
        document.getElementById('dias-receso-field').style.display = 'none';
    }

    // Mostrar modal
    const modal = new bootstrap.Modal(document.getElementById('quickCreateModal'));
    modal.show();
}

/**
 * Mover horario existente
 */
function moverHorario(horarioId, nuevaCelda) {
    const horario = horariosActuales.find(h => h.id == horarioId || `temp-${h.id}` == horarioId);
    if (!horario) return;

    const nuevoDia = nuevaCelda.dataset.dia;
    const nuevaHora = nuevaCelda.dataset.hora;
    const prevDia = horario.dia_semana;
    const prevHora = horario.hora_inicio;

    const candidato = {
        ...horario,
        dia_semana: nuevoDia,
        hora_inicio: nuevaHora
    };

    if (!esRecesoHorario(horario) && hayConflictoLocal(candidato, horario.id)) {
        Swal.fire('Error', 'Conflicto detectado: el docente o el aula ya tienen horario en ese rango.', 'error');
        return;
    }

    // Actualizar datos
    horario.dia_semana = nuevoDia;
    horario.hora_inicio = nuevaHora;

    // Si es un horario existente, actualizar en servidor
    if (horario.id && !String(horario.id).startsWith('temp-') && !esRecesoHorario(horario)) {
        actualizarHorarioServidor(horario)
            .then(() => {
                renderizarHorarios();
            })
            .catch((error) => {
                horario.dia_semana = prevDia;
                horario.hora_inicio = prevHora;
                renderizarHorarios();
                Swal.fire('Error', error.message || 'No se pudo actualizar el horario', 'error');
            });
        return;
    }

    // Registrar cambio para horarios temporales
    registrarCambio({
        ...horario,
        accion: 'mover'
    });

    renderizarHorarios();

    // Auto-guardar movimiento
    guardarCambios();
}

/**
 * Eliminar horario
 */
function eliminarHorario(horarioId) {
    event.stopPropagation();

    Swal.fire({
        title: '¿Eliminar horario?',
        text: 'Esta acción no se puede deshacer',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            // Remover de la lista actual
            horariosActuales = horariosActuales.filter(h => h.id != horarioId && `temp-${h.id}` != horarioId);

            // Convertir a string para verificar
            const idString = String(horarioId);

            // Si es un horario existente (no temporal), agregarlo a la lista de eliminados
            if (!idString.startsWith('temp-')) {
                horariosEliminados.push(horarioId);
            } else {
                // Si es temporal, solo remover de cambios pendientes
                cambiosPendientes = cambiosPendientes.filter(c => c.id != horarioId);
            }

            renderizarHorarios();
            actualizarEstadisticas();

            // Si es de BD, auto-guardar la eliminación
            if (!idString.startsWith('temp-')) {
                guardarSoloEliminaciones();
            } else {
                // Si es temporal, mostrar mensaje
                Swal.fire({
                    icon: 'success',
                    title: 'Eliminado',
                    text: 'El horario ha sido eliminado',
                    timer: 1500,
                    showConfirmButton: false
                });
            }
        }
    });
}

/**
 * Registrar cambio pendiente
 */
function registrarCambio(horario) {
    const index = cambiosPendientes.findIndex(c => c.id === horario.id);
    if (index >= 0) {
        cambiosPendientes[index] = horario;
    } else {
        cambiosPendientes.push(horario);
    }
    actualizarContadorCambios();
}

/**
 * Actualizar contador de cambios
 */
function actualizarContadorCambios() {
    const total = cambiosPendientes.length + horariosEliminados.length;
    const btnGuardar = document.getElementById('btn-guardar-cambios');
    const contadorSpan = document.getElementById('cambios-count');

    if (contadorSpan) contadorSpan.textContent = total;
    if (btnGuardar) btnGuardar.disabled = total === 0;
}

/**
 * Actualizar estadísticas
 */
function actualizarEstadisticas() {
    // Filtrar recesos para no contarlos en estadísticas
    const horariosReales = horariosActuales.filter(h => {
        const esReceso = !h.curso_id ||
            (h.curso_nombre && (
                h.curso_nombre.toLowerCase().includes('receso') ||
                h.curso_nombre === 'RECESO' ||
                h.curso_nombre === 'Sin curso'
            ));
        return !esReceso;
    });

    const total = horariosReales.length;
    const cursosUnicos = new Set(horariosReales.map(h => h.curso_id)).size;
    const docentesUnicos = new Set(horariosReales.map(h => h.docente_id)).size;

    // Calcular horas totales
    let horasTotales = 0;
    horariosReales.forEach(h => {
        const inicio = h.hora_inicio.split(':');
        const fin = h.hora_fin.split(':');
        const minutos = (parseInt(fin[0]) * 60 + parseInt(fin[1])) - (parseInt(inicio[0]) * 60 + parseInt(inicio[1]));
        horasTotales += minutos / 60;
    });

    animarContador('stat-total', total);
    animarContador('stat-cursos', cursosUnicos);
    animarContador('stat-docentes', docentesUnicos);
    animarContador('stat-horas', Math.round(horasTotales));
}

/**
 * Animar contador
 */
function animarContador(elementId, valorFinal) {
    const elemento = document.getElementById(elementId);
    const valorActual = parseInt(elemento.textContent) || 0;
    const duracion = 500;
    const pasos = 20;
    const incremento = (valorFinal - valorActual) / pasos;
    let paso = 0;

    const intervalo = setInterval(() => {
        paso++;
        const nuevoValor = Math.round(valorActual + (incremento * paso));
        elemento.textContent = nuevoValor;

        if (paso >= pasos) {
            clearInterval(intervalo);
            elemento.textContent = valorFinal;
        }
    }, duracion / pasos);
}

/**
 * Aplicar filtros desde la URL si existen
 */
function aplicarFiltrosDesdeUrl() {
    const params = new URLSearchParams(window.location.search);
    const cicloId = params.get('ciclo_id');
    const aulaId = params.get('aula_id');
    const turno = params.get('turno');

    if (cicloId) {
        const el = document.getElementById('filtro-ciclo');
        if (el && el.value !== cicloId) el.value = cicloId;
    }
    if (aulaId) {
        const el = document.getElementById('filtro-aula');
        if (el && el.value !== aulaId) el.value = aulaId;
    }
    if (turno) {
        const el = document.getElementById('filtro-turno');
        if (el && el.value !== turno) el.value = turno;
    }
}

/**
 * Actualizar la URL con los filtros actuales (sin recargar)
 */
function actualizarUrlConFiltros(cicloId, aulaId, turno) {
    const params = new URLSearchParams(window.location.search);
    if (cicloId) params.set('ciclo_id', cicloId); else params.delete('ciclo_id');
    if (aulaId) params.set('aula_id', aulaId); else params.delete('aula_id');
    if (turno) params.set('turno', turno); else params.delete('turno');

    const newUrl = `${window.location.pathname}?${params.toString()}`;
    window.history.replaceState({}, '', newUrl);
}

/**
 * Inicializar eventos
 */
function inicializarEventos() {
    const filtroCiclo = document.getElementById('filtro-ciclo');
    const filtroAula = document.getElementById('filtro-aula');
    const filtroTurno = document.getElementById('filtro-turno');

    const actualizarUrl = () => {
        actualizarUrlConFiltros(
            filtroCiclo?.value,
            filtroAula?.value,
            filtroTurno?.value
        );
    };

    if (filtroCiclo) filtroCiclo.addEventListener('change', actualizarUrl);
    if (filtroAula) filtroAula.addEventListener('change', actualizarUrl);
    if (filtroTurno) filtroTurno.addEventListener('change', actualizarUrl);
    // Botón cargar horarios
    document.getElementById('btn-cargar-horarios').addEventListener('click', cargarHorarios);

    // Botón guardar cambios
    // Botón guardar cambios
    const btnGuardar = document.getElementById('btn-guardar-cambios');
    if (btnGuardar) {
        btnGuardar.addEventListener('click', guardarCambios);
    }

    // Botón exportar PDF
    document.getElementById('btn-exportar-pdf').addEventListener('click', function () {
        const cicloId = document.getElementById('filtro-ciclo').value;
        const aulaId = document.getElementById('filtro-aula').value;
        const turno = document.getElementById('filtro-turno').value;

        if (!cicloId || !aulaId || !turno) {
            Swal.fire('Atención', 'Por favor seleccione ciclo, aula y turno antes de exportar', 'warning');
            return;
        }

        // Abrir en nueva ventana
        const url = `${window.location.origin}/json/horarios-docentes/export-pdf?ciclo_id=${cicloId}&aula_id=${aulaId}&turno=${turno}`;
        window.open(url, '_blank');
    });

    // Modal - Guardar
    document.getElementById('btn-modal-guardar').addEventListener('click', guardarDesdeModal);

    // Limpiar modo ediciÃ³n al cerrar el modal
    const quickCreateModalEl = document.getElementById('quickCreateModal');
    if (quickCreateModalEl) {
        quickCreateModalEl.addEventListener('hidden.bs.modal', function () {
            horarioEnEdicionId = null;
        });
    }

    // Checkbox de receso - mostrar/ocultar campos
    document.getElementById('modal-es-receso').addEventListener('change', function (e) {
        const cursoField = document.getElementById('curso-field');
        const cursoSelect = document.getElementById('modal-curso');
        const docenteField = document.getElementById('docente-field');
        const docenteSelect = document.getElementById('modal-docente');
        const diasRecesoField = document.getElementById('dias-receso-field');

        if (e.target.checked) {
            // Ocultar curso y docente
            cursoField.style.display = 'none';
            cursoSelect.removeAttribute('required');
            docenteField.style.display = 'none';
            docenteSelect.removeAttribute('required');
            // Mostrar selección de días
            diasRecesoField.style.display = 'block';
            // Marcar todos los días por defecto
            document.querySelectorAll('.dia-checkbox').forEach(cb => cb.checked = true);
        } else {
            // Mostrar curso y docente
            cursoField.style.display = 'block';
            cursoSelect.setAttribute('required', 'required');
            docenteField.style.display = 'block';
            docenteSelect.setAttribute('required', 'required');
            // Ocultar selección de días
            diasRecesoField.style.display = 'none';
        }
    });

    // Botones de selección de días
    document.getElementById('btn-seleccionar-todos-dias').addEventListener('click', function () {
        document.querySelectorAll('.dia-checkbox').forEach(cb => cb.checked = true);
    });

    document.getElementById('btn-deseleccionar-todos-dias').addEventListener('click', function () {
        document.querySelectorAll('.dia-checkbox').forEach(cb => cb.checked = false);
    });

    // Búsqueda de cursos
    document.getElementById('search-curso').addEventListener('input', function (e) {
        const busqueda = e.target.value.toLowerCase();
        document.querySelectorAll('.course-item').forEach(item => {
            const nombre = item.dataset.cursoNombre.toLowerCase();
            item.style.display = nombre.includes(busqueda) ? 'block' : 'none';
        });
    });

    // Click en celdas vacías
    document.querySelectorAll('.grid-cell:not(.header):not(.time-slot)').forEach(cell => {
        cell.addEventListener('click', function (e) {
            if (e.target === this && this.children.length === 0) {
                abrirModalCreacion(this.dataset.dia, this.dataset.hora);
            }
        });

        cell.addEventListener('dragleave', function (e) {
            this.style.background = '';
            this.classList.remove('invalid-drop');
        });
    });
}

/**
 * Guardar desde modal
 */
async function guardarDesdeModal() {
    const dia = document.getElementById('modal-dia').value;
    const horaInicio = document.getElementById('modal-hora-inicio').value;
    const horaFin = document.getElementById('modal-hora-fin').value;
    const cursoId = document.getElementById('modal-curso').value;
    const docenteId = document.getElementById('modal-docente').value;
    const grupo = document.getElementById('modal-grupo').value;
    const esReceso = document.getElementById('modal-es-receso').checked;

    // Validaciones básicas
    if (!horaFin) {
        Swal.fire('Error', 'Por favor ingrese la hora de fin', 'error');
        return;
    }

    // Si no es receso, validar curso y docente
    if (!esReceso) {
        if (!cursoId) {
            Swal.fire('Error', 'Por favor seleccione un curso', 'error');
            return;
        }
        if (!docenteId) {
            Swal.fire('Error', 'Por favor seleccione un docente', 'error');
            return;
        }
    }

    // Validar que hora fin sea mayor que hora inicio
    if (horaFin <= horaInicio) {
        Swal.fire('Error', 'La hora de fin debe ser posterior a la hora de inicio', 'error');
        return;
    }

    // Si estÃ¡ editando un horario real, no permitir convertir a receso
    if (horarioEnEdicionId && esReceso) {
        Swal.fire('Error', 'No puedes convertir un horario existente a receso desde ediciÃ³n. Elimina y crea un receso nuevo.', 'error');
        return;
    }

    // Determinar días a crear
    let dias;
    if (esReceso) {
        // Obtener días seleccionados
        dias = Array.from(document.querySelectorAll('.dia-checkbox:checked')).map(cb => cb.value);
        if (dias.length === 0) {
            Swal.fire('Error', 'Por favor seleccione al menos un día para el receso', 'error');
            return;
        }
    } else {
        dias = [dia];
    }

    // Obtener datos del curso y docente (si aplica)
    const curso = esReceso ? null : window.scheduleData.cursos.find(c => c.id == cursoId);
    const docente = esReceso ? null : window.scheduleData.docentes.find(d => d.id == docenteId);

    // Validar integridad de datos
    if (!esReceso) {
        if (!curso) {
            console.error('Curso no encontrado en data:', cursoId);
            Swal.fire('Error', 'No se encontraron los datos del curso seleccionado. Por favor recargue la página.', 'error');
            return;
        }
        if (!docente) {
            console.error('Docente no encontrado en data:', docenteId);
            Swal.fire('Error', 'No se encontraron los datos del docente seleccionado. Por favor recargue la página.', 'error');
            return;
        }
    }

    // Nombre del curso/receso
    let cursoNombre;
    if (esReceso) {
        cursoNombre = 'RECESO';
    } else {
        cursoNombre = curso.nombre;
    }

    if (!esReceso) {
        const candidato = {
            id: horarioEnEdicionId,
            docente_id: docenteId,
            curso_id: cursoId,
            aula_id: document.getElementById('filtro-aula').value,
            ciclo_id: document.getElementById('filtro-ciclo').value,
            dia_semana: dia,
            hora_inicio: horaInicio,
            hora_fin: horaFin,
            turno: document.getElementById('filtro-turno').value
        };
        const conflicto = obtenerConflictoLocal(candidato, horarioEnEdicionId);
        if (conflicto) {
            const detalle = `${conflicto.docente_nombre || 'Docente'} - ${conflicto.curso_nombre || 'Curso'} (${conflicto.hora_inicio} a ${conflicto.hora_fin})`;
            Swal.fire('Error', `Conflicto local con: ${detalle}`, 'error');
            return;
        }
    }

    // Crear horarios para cada día
    if (horarioEnEdicionId && !esReceso) {
        const payload = {
            docente_id: docenteId,
            curso_id: cursoId,
            aula_id: document.getElementById('filtro-aula').value,
            ciclo_id: document.getElementById('filtro-ciclo').value,
            dia_semana: dia,
            hora_inicio: horaInicio,
            hora_fin: horaFin,
            turno: document.getElementById('filtro-turno').value,
            grupo: grupo || null
        };

        try {
            const response = await fetch(`${window.location.origin}/json/horarios-docentes/${horarioEnEdicionId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            });

            if (!response.ok) {
                let message = 'Error al actualizar horario';
                const contentType = response.headers.get('content-type');
                if (contentType && contentType.includes('application/json')) {
                    const data = await response.json();
                    if (response.status === 422 && Array.isArray(data.errores)) {
                        const html = construirHtmlConflictos(data.errores);
                        Swal.fire({ icon: 'error', title: 'Conflictos detectados', html: html || data.message || message });
                        throw new Error(data.message || message);
                    }
                    message = data.message || message;
                }
                throw new Error(message + ` (${response.status})`);
            }

            const index = horariosActuales.findIndex(h => h.id == horarioEnEdicionId);
            if (index !== -1) {
                horariosActuales[index] = {
                    ...horariosActuales[index],
                    ...payload,
                    curso_nombre: curso?.nombre || horariosActuales[index].curso_nombre,
                    curso_color: curso?.color || horariosActuales[index].curso_color,
                    docente_nombre: docente?.nombre_completo || horariosActuales[index].docente_nombre
                };
            }

            horarioEnEdicionId = null;

            renderizarHorarios();
            actualizarEstadisticas();

            bootstrap.Modal.getInstance(document.getElementById('quickCreateModal')).hide();
            document.getElementById('quick-create-form').reset();
            document.getElementById('modal-es-receso').checked = false;
            document.getElementById('curso-field').style.display = 'block';
            document.getElementById('modal-curso').setAttribute('required', 'required');
            document.getElementById('docente-field').style.display = 'block';
            document.getElementById('modal-docente').setAttribute('required', 'required');
            document.getElementById('dias-receso-field').style.display = 'none';

            Swal.fire({
                icon: 'success',
                title: 'Horario actualizado',
                text: 'Cambios guardados correctamente',
                timer: 1500,
                showConfirmButton: false
            });

            return;
        } catch (error) {
            Swal.fire('Error', error.message || 'OcurriÃ³ un error al actualizar', 'error');
            return;
        }
    }

    dias.forEach(diaActual => {
        const nuevoHorario = {
            id: 'temp-' + Date.now() + '-' + diaActual,
            docente_id: esReceso ? null : docenteId,
            docente_nombre: esReceso ? 'N/A' : docente.nombre_completo,
            curso_id: esReceso ? null : cursoId,
            curso_nombre: cursoNombre,
            curso_color: esReceso ? '#10b981' : (curso.color || cursosColores[cursoId]),
            dia_semana: diaActual,
            hora_inicio: horaInicio,
            hora_fin: horaFin,
            grupo: esReceso ? null : grupo,
            aula_id: document.getElementById('filtro-aula').value,
            ciclo_id: document.getElementById('filtro-ciclo').value,
            turno: document.getElementById('filtro-turno').value
        };

        horariosActuales.push(nuevoHorario);
        registrarCambio(nuevoHorario);
    });

    renderizarHorarios();
    actualizarEstadisticas();

    // Cerrar modal y resetear
    bootstrap.Modal.getInstance(document.getElementById('quickCreateModal')).hide();
    document.getElementById('quick-create-form').reset();
    document.getElementById('modal-es-receso').checked = false;
    document.getElementById('curso-field').style.display = 'block';
    document.getElementById('modal-curso').setAttribute('required', 'required');
    document.getElementById('docente-field').style.display = 'block';
    document.getElementById('modal-docente').setAttribute('required', 'required');
    document.getElementById('dias-receso-field').style.display = 'none';

    // Mostrar mensaje de éxito
    const mensaje = dias.length > 1
        ? `Se agregaron ${dias.length} recesos (${dias.join(', ')})`
        : esReceso
            ? 'Receso agregado'
            : 'Horario agregado';

    Swal.fire({
        icon: 'success',
        title: esReceso ? 'Receso(s) agregado(s)' : 'Horario agregado',
        text: mensaje,
        timer: 1500,
        showConfirmButton: false
    });

    // Auto-guardar inmediatamente (silencioso porque ya mostramos mensaje)
    guardarCambios(false);
}

/**
 * Guardar solo eliminaciones (auto-save inmediato)
 */
async function guardarSoloEliminaciones() {
    if (horariosEliminados.length === 0) {
        return;
    }

    mostrarCargando(true);

    try {
        // Eliminar horarios marcados
        for (const horarioId of horariosEliminados) {
            try {
                const deleteResponse = await ejecutarDeleteHorario(horarioId);

                if (!deleteResponse.ok) {
                    throw new Error(`Error al eliminar horario (${deleteResponse.status})`);
                }
            } catch (deleteError) {
                throw new Error('Error al eliminar horario: ' + deleteError.message);
            }
        }

        // Limpiar lista de eliminados
        horariosEliminados = [];

        // Mostrar mensaje de éxito
        Swal.fire({
            icon: 'success',
            title: 'Eliminado',
            text: 'El horario ha sido eliminado correctamente',
            timer: 1500,
            showConfirmButton: false
        });

        // Recargar horarios (silencioso)
        await cargarHorarios(false);
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: error.message || 'Ocurrió un error al eliminar el horario',
            confirmButtonText: 'Entendido'
        });
    } finally {
        mostrarCargando(false);
    }
}

/**
 * Guardar todos los cambios
 */
async function guardarCambios(mostrarAlerta = true) {
    if (cambiosPendientes.length === 0 && horariosEliminados.length === 0) {
        return; // Silenciosamente no hacer nada si no hay cambios
    }

    mostrarCargando(true);
    let guardadosCount = 0;
    let eliminadosCount = horariosEliminados.length;

    try {
        // Guardar nuevos horarios si hay (EXCLUYENDO RECESOS)
        if (cambiosPendientes.length > 0) {
            // Filtrar recesos - solo guardar horarios reales
            const horariosParaGuardar = cambiosPendientes.filter(h => {
                const esReceso = !h.curso_id ||
                    (h.curso_nombre && (
                        h.curso_nombre.toLowerCase().includes('receso') ||
                        h.curso_nombre === 'RECESO' ||
                        h.curso_nombre === 'Sin curso'
                    ));
                return !esReceso;
            });

            // Solo hacer la petición si hay horarios reales para guardar
            if (horariosParaGuardar.length > 0) {
                const dedup = new Map();
                horariosParaGuardar.forEach(h => {
                    dedup.set(normalizarHorarioKey(h), h);
                });
                const horariosUnicos = Array.from(dedup.values());

                const horarios = horariosUnicos.map(h => ({
                    docente_id: h.docente_id,
                    curso_id: h.curso_id,
                    aula_id: h.aula_id,
                    ciclo_id: h.ciclo_id,
                    dia_semana: h.dia_semana,
                    hora_inicio: h.hora_inicio,
                    hora_fin: h.hora_fin,
                    turno: h.turno,
                    grupo: h.grupo || null
                }));

                const response = await fetch(window.scheduleData.routes.bulkStore, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ horarios })
                });

                const contentType = response.headers.get('content-type') || '';
                if (!response.ok) {
                    let message = `Error al guardar horarios (${response.status})`;
                    if (contentType.includes('application/json')) {
                        const result = await response.json();
                        if (response.status === 422 && Array.isArray(result.errores)) {
                            const html = construirHtmlConflictos(result.errores);
                            Swal.fire({ icon: 'error', title: 'Conflictos detectados', html: html || result.message || message });
                            throw new Error(result.message || message);
                        }
                        message = result.message || message;
                    } else {
                        // Respuesta HTML o vacÃ­a (posible sesiÃ³n expirada/redirect)
                        message = `${message}. Recarga la pÃ¡gina e intenta de nuevo.`;
                    }
                    throw new Error(message);
                }

                if (contentType.includes('application/json')) {
                    const result = await response.json();
                    if (result && result.success === false) {
                        throw new Error(result.message || 'Error al guardar horarios');
                    }
                    if (Array.isArray(result?.horarios_creados)) {
                        guardadosCount += result.horarios_creados.length;
                    } else {
                        guardadosCount += horariosUnicos.length;
                    }
                } else {
                    guardadosCount += horariosUnicos.length;
                }
            }
        }

        // Eliminar horarios marcados si hay
        if (horariosEliminados.length > 0) {
            for (const horarioId of horariosEliminados) {
                try {
                    const deleteResponse = await ejecutarDeleteHorario(horarioId);

                    // Verificar si la respuesta es exitosa (200-299)
                    if (deleteResponse.ok) {
                        // Intentar parsear JSON si hay contenido
                        const contentType = deleteResponse.headers.get('content-type');
                        if (contentType && contentType.includes('application/json')) {
                            const deleteResult = await deleteResponse.json();
                            if (deleteResult.success === false) {
                                throw new Error(deleteResult.message || 'Error al eliminar horario');
                            }
                        }
                        // Si no hay JSON o success es true, continuar
                    } else {
                        // Si el código HTTP no es exitoso
                        throw new Error(`Error al eliminar horario (${deleteResponse.status})`);
                    }
                } catch (deleteError) {
                    console.error('Error eliminando horario:', deleteError);
                    throw new Error('Error al eliminar horario: ' + deleteError.message);
                }
            }
        }

        // Limpiar cambios pendientes
        cambiosPendientes = [];
        horariosEliminados = [];
        actualizarContadorCambios();

        // Mostrar mensaje de éxito
        let mensaje = 'Cambios guardados correctamente';
        if (guardadosCount > 0 && eliminadosCount > 0) {
            mensaje = `Guardados: ${guardadosCount}, Eliminados: ${eliminadosCount}`;
        } else if (guardadosCount > 0) {
            mensaje = `Guardados: ${guardadosCount}`;
        } else if (eliminadosCount > 0) {
            mensaje = `Eliminados: ${eliminadosCount}`;
        }

        if (mostrarAlerta) {
            Swal.fire({
                icon: 'success',
                title: '¡Éxito!',
                text: mensaje,
                timer: 2000,
                showConfirmButton: false
            });
        }

        // Recargar horarios (silencioso)
        await cargarHorarios(false);
    } catch (error) {
        console.error('Error al guardar:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: error.message || 'Ocurrió un error al guardar los cambios',
            confirmButtonText: 'Entendido'
        });
    } finally {
        mostrarCargando(false);
    }
}

/**
 * Mostrar/ocultar overlay de carga
 */
function mostrarCargando(mostrar) {
    const overlay = document.getElementById('loadingOverlay');
    if (mostrar) {
        overlay.classList.add('active');
    } else {
        overlay.classList.remove('active');
    }
}

/**
 * Construir URL de eliminaciÃ³n y aplicar fallback sin /json si es necesario
 */
function construirUrlDelete(horarioId) {
    const base = window.scheduleData?.routes?.deleteBase || '/json/horarios-docentes';
    const normalizado = base.replace(/\/$/, '');
    return new URL(`${normalizado}/${horarioId}`, window.location.origin).toString();
}

/**
 * Ejecutar DELETE con fallback a ruta sin /json si devuelve 404
 */
async function ejecutarDeleteHorario(horarioId) {
    const headers = {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        'Accept': 'application/json'
    };

    const url = construirUrlDelete(horarioId);
    let response = await fetch(url, { method: 'DELETE', headers });
    const location = response.headers.get('location');

    // Fallback: algunos servidores no aceptan DELETE directo
    if (response.status === 404 || response.status === 405) {
        const body = new URLSearchParams({ _method: 'DELETE' }).toString();
        response = await fetch(url, {
            method: 'POST',
            headers: {
                ...headers,
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'
            },
            body
        });
        const fallbackLocation = response.headers.get('location');
    }

    return response;
}
