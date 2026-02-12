// public/js/asistencia/editar_index.js
// Sistema de Gestión Masiva de Asistencias

(function () {
    'use strict';

    // ========================================
    // VARIABLES GLOBALES
    // ========================================
    let estudiantesCargados = [];
    let estudiantesFiltrados = [];
    let fechasSeleccionadas = [];
    let flatpickrInstance = null;

    // Estudiantes para autocompletado (regularización)
    // Los datos se pasan desde la vista Blade mediante una variable global
    const estudiantesData = window.estudiantesData || [];

    // ========================================
    // TAB 2: REGISTRO MASIVO
    // ========================================

    // Cargar estudiantes según filtros
    $('#btnCargarEstudiantes').on('click', function () {
        const fecha = $('#masivo_fecha').val();

        if (!fecha) {
            Swal.fire({
                icon: 'warning',
                title: 'Fecha requerida',
                text: 'Por favor seleccione una fecha antes de cargar estudiantes.',
            });
            return;
        }

        const filtros = {
            ciclo_id: $('#filtro_ciclo').val(),
            aula_id: $('#filtro_aula').val(),
            turno_id: $('#filtro_turno').val(),
            carrera_id: $('#filtro_carrera').val()
        };

        // Mostrar loading
        const btn = $(this);
        const originalText = btn.html();
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Cargando...');

        // AJAX para obtener estudiantes
        $.ajax({
            url: window.appRoutes.estudiantesFiltrados,
            method: 'GET',
            data: filtros,
            success: function (response) {
                if (response.success) {
                    estudiantesCargados = response.estudiantes;
                    estudiantesFiltrados = response.estudiantes;
                    renderizarTablaEstudiantes();
                    $('#tablaEstudiantesContainer').slideDown();

                    Swal.fire({
                        icon: 'success',
                        title: 'Estudiantes cargados',
                        text: `Se cargaron ${response.total} estudiantes.`,
                        timer: 2000,
                        showConfirmButton: false
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message || 'No se pudieron cargar los estudiantes.'
                    });
                }
            },
            error: function (xhr) {
                console.error('Error:', xhr);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Ocurrió un error al cargar los estudiantes.'
                });
            },
            complete: function () {
                btn.prop('disabled', false).html(originalText);
            }
        });
    });

    // Renderizar tabla de estudiantes
    function renderizarTablaEstudiantes() {
        const tbody = $('#tbodyEstudiantes');
        tbody.empty();

        if (estudiantesFiltrados.length === 0) {
            tbody.append(`
                <tr>
                    <td colspan="6" class="text-center text-muted">
                        No se encontraron estudiantes con los filtros seleccionados.
                    </td>
                </tr>
            `);
            actualizarContador();
            return;
        }

        estudiantesFiltrados.forEach(function (estudiante) {
            const row = `
                <tr>
                    <td>
                        <input type="checkbox" class="form-check-input check-estudiante" 
                               value="${estudiante.numero_documento}" checked>
                    </td>
                    <td>${estudiante.numero_documento}</td>
                    <td>${estudiante.nombre_completo}</td>
                    <td>${estudiante.aula}</td>
                    <td>${estudiante.turno}</td>
                    <td>${estudiante.carrera}</td>
                </tr>
            `;
            tbody.append(row);
        });

        // Actualizar contador
        actualizarContador();

        // Event listeners para checkboxes individuales
        $('.check-estudiante').on('change', function () {
            actualizarContador();
            actualizarCheckTodos();
        });
    }

    // Checkbox "Seleccionar Todos"
    $('#checkTodos').on('change', function () {
        const isChecked = $(this).is(':checked');
        $('.check-estudiante').prop('checked', isChecked);
        actualizarContador();
    });

    // Actualizar contador de seleccionados
    function actualizarContador() {
        const total = $('.check-estudiante:checked').length;
        $('#contadorSeleccionados').text(`${total} seleccionados`);
    }

    // Actualizar estado del checkbox "Todos"
    function actualizarCheckTodos() {
        const total = $('.check-estudiante').length;
        const checked = $('.check-estudiante:checked').length;
        $('#checkTodos').prop('checked', total > 0 && total === checked);
    }

    // Búsqueda en tiempo real en la tabla
    $('#buscarEstudiante').on('input', debounce(function () {
        const searchTerm = $(this).val().toLowerCase();

        if (!searchTerm) {
            estudiantesFiltrados = estudiantesCargados;
        } else {
            estudiantesFiltrados = estudiantesCargados.filter(function (estudiante) {
                return estudiante.numero_documento.includes(searchTerm) ||
                    estudiante.nombre_completo.toLowerCase().includes(searchTerm);
            });
        }

        renderizarTablaEstudiantes();
    }, 300));

    // Registrar asistencias masivas
    $('#btnRegistrarMasivo').on('click', function () {
        const fecha = $('#masivo_fecha').val();
        const hora = $('#masivo_hora').val();
        const tipo = $('#masivo_tipo').val();
        const seleccionados = [];

        $('.check-estudiante:checked').each(function () {
            seleccionados.push($(this).val());
        });

        if (seleccionados.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Sin selección',
                text: 'Debe seleccionar al menos un estudiante.'
            });
            return;
        }

        // Confirmación
        Swal.fire({
            title: '¿Confirmar registro masivo?',
            html: `Se registrará asistencia para <strong>${seleccionados.length} estudiantes</strong><br>
                   Fecha: <strong>${formatearFecha(fecha)}</strong><br>
                   Hora: <strong>${hora}</strong>`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, registrar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d'
        }).then((result) => {
            if (result.isConfirmed) {
                registrarMasivo(fecha, hora, tipo, seleccionados);
            }
        });
    });

    // Función para registrar masivo (AJAX)
    function registrarMasivo(fecha, hora, tipo, estudiantes) {
        const btn = $('#btnRegistrarMasivo');
        const originalText = btn.html();
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Registrando...');

        $.ajax({
            url: window.appRoutes.registrarMasivo,
            method: 'POST',
            data: {
                _token: window.csrfToken,
                fecha: fecha,
                hora: hora,
                tipo_verificacion: tipo,
                ciclo_id: $('#filtro_ciclo').val(),
                estudiantes: estudiantes
            },
            success: function (response) {
                if (response.success) {
                    let mensajeHtml = `<strong>${response.registrados}</strong> asistencias registradas<br>
                           <strong>${response.omitidos}</strong> omitidas (duplicados)`;

                    if (response.errores && response.errores > 0) {
                        mensajeHtml += `<br><strong>${response.errores}</strong> errores`;
                        console.log('Detalles de errores:', response.detalles_errores);
                    }

                    Swal.fire({
                        icon: 'success',
                        title: '¡Registro completado!',
                        html: mensajeHtml,
                        confirmButtonText: 'Aceptar'
                    }).then(() => {
                        // Limpiar formulario
                        $('#formRegistroMasivo')[0].reset();
                        $('#tablaEstudiantesContainer').slideUp();
                        estudiantesCargados = [];
                        estudiantesFiltrados = [];
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message || 'No se pudo completar el registro.'
                    });
                }
            },
            error: function (xhr) {
                console.error('Error:', xhr);
                let errorMsg = 'Ocurrió un error al registrar las asistencias.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: errorMsg
                });
            },
            complete: function () {
                btn.prop('disabled', false).html(originalText);
            }
        });
    }

    // ========================================
    // TAB 3: REGULARIZACIÓN INDIVIDUAL
    // ========================================

    // Autocompletado de estudiantes
    const searchInputReg = document.getElementById('estudiante_search_reg');
    const suggestionsContainerReg = document.getElementById('suggestionsReg');
    const nroDocumentoInputReg = document.getElementById('nro_documento_reg');
    const selectedStudentDivReg = document.getElementById('selectedStudentReg');
    const selectedNameSpanReg = document.getElementById('selectedNameReg');
    const selectedDNISpanReg = document.getElementById('selectedDNIReg');

    let currentFocusReg = -1;
    let filteredEstudiantesReg = [];

    // Función para resaltar coincidencias
    function highlightMatch(text, searchTerm) {
        if (!searchTerm) return text;
        const regex = new RegExp(`(${searchTerm.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');
        return text.replace(regex, '<span class="text-primary">$1</span>');
    }

    // Función para buscar estudiantes
    function searchEstudiantesReg(searchTerm) {
        if (!searchTerm) return [];

        const term = searchTerm.toLowerCase();
        return estudiantesData.filter(estudiante => {
            const nombreCompleto = `${estudiante.nombre} ${estudiante.apellido_paterno} ${estudiante.apellido_materno}`.toLowerCase();
            return nombreCompleto.includes(term) || estudiante.numero_documento.includes(term);
        });
    }

    // Función para mostrar sugerencias
    function showSuggestionsReg(searchTerm) {
        filteredEstudiantesReg = searchEstudiantesReg(searchTerm);

        if (searchTerm.length === 0 || filteredEstudiantesReg.length === 0) {
            suggestionsContainerReg.style.display = 'none';
            if (searchTerm.length > 0 && filteredEstudiantesReg.length === 0) {
                suggestionsContainerReg.innerHTML = '<div class="no-results">No se encontraron resultados</div>';
                suggestionsContainerReg.style.display = 'block';
            }
            return;
        }

        let html = '';
        filteredEstudiantesReg.slice(0, 10).forEach((estudiante, index) => {
            const nombreCompleto = `${estudiante.nombre} ${estudiante.apellido_paterno} ${estudiante.apellido_materno}`;
            const highlightedNombre = highlightMatch(nombreCompleto, searchTerm);
            const highlightedDNI = highlightMatch(estudiante.numero_documento, searchTerm);

            html += `
                <div class="suggestion-item" data-index="${index}">
                    <div>${highlightedNombre}</div>
                    <div class="dni">DNI: ${highlightedDNI}</div>
                </div>
            `;
        });

        suggestionsContainerReg.innerHTML = html;
        suggestionsContainerReg.style.display = 'block';
        currentFocusReg = -1;

        // Agregar eventos a las sugerencias
        document.querySelectorAll('#suggestionsReg .suggestion-item').forEach((item, index) => {
            item.addEventListener('click', function () {
                selectEstudianteReg(filteredEstudiantesReg[index]);
            });
        });
    }

    // Función para seleccionar un estudiante
    function selectEstudianteReg(estudiante) {
        const nombreCompleto = `${estudiante.nombre} ${estudiante.apellido_paterno} ${estudiante.apellido_materno}`;

        // Mostrar el estudiante seleccionado
        selectedNameSpanReg.textContent = nombreCompleto;
        selectedDNISpanReg.textContent = estudiante.numero_documento;
        selectedStudentDivReg.style.display = 'block';

        // Establecer el valor del campo oculto
        nroDocumentoInputReg.value = estudiante.numero_documento;

        // Limpiar y ocultar el campo de búsqueda
        searchInputReg.value = '';
        searchInputReg.style.display = 'none';
        suggestionsContainerReg.style.display = 'none';
    }

    // Función para remover el estudiante seleccionado
    window.removeStudentReg = function () {
        selectedStudentDivReg.style.display = 'none';
        searchInputReg.style.display = 'block';
        nroDocumentoInputReg.value = '';
        searchInputReg.focus();
    };

    // Eventos del input de búsqueda
    searchInputReg.addEventListener('input', function () {
        showSuggestionsReg(this.value);
    });

    // Navegación con teclado
    searchInputReg.addEventListener('keydown', function (e) {
        const items = suggestionsContainerReg.querySelectorAll('.suggestion-item');

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            currentFocusReg++;
            if (currentFocusReg >= items.length) currentFocusReg = 0;
            setActiveReg(items);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            currentFocusReg--;
            if (currentFocusReg < 0) currentFocusReg = items.length - 1;
            setActiveReg(items);
        } else if (e.key === 'Enter') {
            e.preventDefault();
            if (currentFocusReg > -1 && filteredEstudiantesReg[currentFocusReg]) {
                selectEstudianteReg(filteredEstudiantesReg[currentFocusReg]);
            }
        } else if (e.key === 'Escape') {
            suggestionsContainerReg.style.display = 'none';
            currentFocusReg = -1;
        }
    });

    // Función para marcar elemento activo
    function setActiveReg(items) {
        items.forEach(item => item.classList.remove('active'));
        if (currentFocusReg >= 0 && currentFocusReg < items.length) {
            items[currentFocusReg].classList.add('active');
            items[currentFocusReg].scrollIntoView({ block: 'nearest' });
        }
    }

    // Cerrar sugerencias al hacer clic fuera
    document.addEventListener('click', function (e) {
        if (!e.target.closest('.search-container') && e.target !== searchInputReg) {
            suggestionsContainerReg.style.display = 'none';
        }
    });

    // Flatpickr para selección múltiple de fechas
    flatpickrInstance = flatpickr("#fechas_regularizar", {
        mode: "multiple",
        dateFormat: "Y-m-d",
        locale: "es",
        onChange: function (selectedDates, dateStr, instance) {
            fechasSeleccionadas = selectedDates;
            renderizarFechasSeleccionadas();
        }
    });

    // Renderizar fechas seleccionadas
    function renderizarFechasSeleccionadas() {
        const container = $('#selectedDatesList');
        container.empty();

        if (fechasSeleccionadas.length === 0) {
            container.html('<span class="text-muted">No hay fechas seleccionadas</span>');
            return;
        }

        fechasSeleccionadas.forEach(function (fecha, index) {
            const fechaFormateada = formatearFecha(fecha.toISOString().split('T')[0]);
            const tag = `
                <span class="date-tag">
                    ${fechaFormateada}
                    <span class="remove-date" data-index="${index}">×</span>
                </span>
            `;
            container.append(tag);
        });

        // Event listeners para remover fechas
        $('.remove-date').on('click', function () {
            const index = $(this).data('index');
            fechasSeleccionadas.splice(index, 1);
            flatpickrInstance.setDate(fechasSeleccionadas);
            renderizarFechasSeleccionadas();
        });
    }

    // Submit formulario de regularización
    $('#formRegularizacion').on('submit', function (e) {
        e.preventDefault();

        const dni = $('#nro_documento_reg').val();
        const hora = $('#reg_hora').val();
        const tipo = $('#reg_tipo').val();

        if (!dni) {
            Swal.fire({
                icon: 'warning',
                title: 'Estudiante requerido',
                text: 'Por favor seleccione un estudiante.'
            });
            return;
        }

        if (fechasSeleccionadas.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Fechas requeridas',
                text: 'Por favor seleccione al menos una fecha.'
            });
            return;
        }

        // Convertir fechas a formato string
        const fechasStr = fechasSeleccionadas.map(f => f.toISOString().split('T')[0]);

        // Confirmación
        Swal.fire({
            title: '¿Confirmar regularización?',
            html: `Se registrará asistencia para:<br>
                   <strong>${$('#selectedNameReg').text()}</strong><br>
                   En <strong>${fechasStr.length} fechas</strong> seleccionadas<br>
                   Hora: <strong>${hora}</strong>`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, regularizar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d'
        }).then((result) => {
            if (result.isConfirmed) {
                regularizarEstudiante(dni, fechasStr, hora, tipo);
            }
        });
    });

    // Función para regularizar estudiante (AJAX)
    function regularizarEstudiante(dni, fechas, hora, tipo) {
        const btn = $('#btnRegularizar');
        const originalText = btn.html();
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Regularizando...');

        $.ajax({
            url: window.appRoutes.regularizar,
            method: 'POST',
            data: {
                _token: window.csrfToken,
                nro_documento: dni,
                fechas: fechas,
                hora: hora,
                tipo_verificacion: tipo
            },
            success: function (response) {
                if (response.success) {
                    let detallesHtml = '<div class="text-start mt-3">';
                    response.detalles.forEach(function (detalle) {
                        const icon = detalle.estado === 'registrado' ? '✓' : '✗';
                        const color = detalle.estado === 'registrado' ? 'success' : 'warning';
                        detallesHtml += `<div class="text-${color}"><small>${icon} ${detalle.fecha}: ${detalle.motivo}</small></div>`;
                    });
                    detallesHtml += '</div>';

                    Swal.fire({
                        icon: 'success',
                        title: '¡Regularización completada!',
                        html: `<strong>${response.registrados}</strong> asistencias registradas<br>
                               <strong>${response.omitidos}</strong> omitidas${detallesHtml}`,
                        confirmButtonText: 'Aceptar',
                        width: '600px'
                    }).then(() => {
                        // Limpiar formulario
                        $('#formRegularizacion')[0].reset();
                        removeStudentReg();
                        fechasSeleccionadas = [];
                        flatpickrInstance.clear();
                        renderizarFechasSeleccionadas();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message || 'No se pudo completar la regularización.'
                    });
                }
            },
            error: function (xhr) {
                console.error('Error:', xhr);
                let errorMsg = 'Ocurrió un error al regularizar las asistencias.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: errorMsg
                });
            },
            complete: function () {
                btn.prop('disabled', false).html(originalText);
            }
        });
    }

    // ========================================
    // UTILIDADES
    // ========================================

    // Debounce function
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    // Formatear fecha a dd/mm/yyyy
    function formatearFecha(fecha) {
        const date = new Date(fecha + 'T00:00:00');
        const day = String(date.getDate()).padStart(2, '0');
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const year = date.getFullYear();
        return `${day}/${month}/${year}`;
    }

})();
