@extends('layouts.app')

@section('title', 'Registrar Asistencia Docente')

@push('css')
<style>
    .search-container {
        position: relative;
    }

    .search-input {
        padding-right: 40px;
    }

    .search-icon {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #6c757d;
        pointer-events: none;
    }

    .suggestions-dropdown {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 1px solid #dee2e6;
        border-top: none;
        border-radius: 0 0 0.25rem 0.25rem;
        max-height: 300px;
        overflow-y: auto;
        display: none;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        z-index: 1050;
    }

    .suggestion-item {
        padding: 10px 15px;
        cursor: pointer;
        transition: background-color 0.2s ease;
        border-bottom: 1px solid #f1f3f4;
    }

    .suggestion-item:last-child {
        border-bottom: none;
    }

    .suggestion-item:hover,
    .suggestion-item.active {
        background-color: #f8f9fa;
    }

    .suggestion-item .text-primary {
        font-weight: 600;
    }

    .suggestion-item .dni {
        color: #6c757d;
        font-size: 0.875rem;
    }

    .selected-docente {
        padding: 10px;
        background-color: #e7f3ff;
        border: 1px solid #b3d9ff;
        border-radius: 0.25rem;
        margin-bottom: 10px;
        display: none;
    }

    .selected-docente .remove-btn {
        float: right;
        color: #dc3545;
        cursor: pointer;
        font-weight: bold;
    }

    .no-results {
        padding: 15px;
        text-align: center;
        color: #6c757d;
    }

    .quick-actions {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .quick-action-btn {
        padding: 1rem;
        border: 2px dashed #d1d5db;
        border-radius: 0.5rem;
        background: white;
        cursor: pointer;
        transition: all 0.3s ease;
        text-align: center;
        text-decoration: none;
        color: inherit;
    }

    .quick-action-btn:hover {
        border-color: #4f46e5;
        background: #f8fafc;
        color: inherit;
        text-decoration: none;
    }

    .quick-action-btn.active {
        border-color: #4f46e5;
        background: #eef2ff;
        color: #4f46e5;
    }

    .estado-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .estado-entrada {
        background-color: #dcfce7;
        color: #166534;
        border: 1px solid #22c55e;
    }

    .estado-salida {
        background-color: #fef2f2;
        color: #991b1b;
        border: 1px solid #ef4444;
    }

    .docente-info {
        background: #f8f9fa;
        padding: 1rem;
        border-radius: 0.5rem;
        margin: 1rem 0;
    }

    .recent-activity {
        max-height: 300px;
        overflow-y: auto;
        background: #f8f9fa;
        border-radius: 0.5rem;
        padding: 1rem;
    }

    .activity-item {
        padding: 0.75rem;
        background: white;
        border-radius: 0.375rem;
        margin-bottom: 0.5rem;
        border-left: 4px solid #4f46e5;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }

    .activity-item:last-child {
        margin-bottom: 0;
    }
</style>
@endpush

@section('content')
<!-- Start Content-->
<div class="container-fluid">

    <!-- start page title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('asistencia-docente.index') }}">Asistencia Docente</a></li>
                        <li class="breadcrumb-item active">Registrar</li>
                    </ol>
                </div>
                <h4 class="page-title">
                    <i class="fas fa-user-clock me-2"></i>
                    Registrar Asistencia Docente
                </h4>
            </div>
        </div>
    </div>
    <!-- end page title -->

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-plus-circle me-2"></i>
                        Nuevo Registro de Asistencia
                    </h5>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <h6><i class="fas fa-exclamation-triangle me-1"></i> Errores encontrados:</h6>
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <!-- Acciones Rápidas -->
                    <div class="mb-4">
                        <h6 class="text-muted mb-3">
                            <i class="fas fa-bolt me-1"></i>
                            Acciones Rápidas
                        </h6>
                        <div class="quick-actions">
                            <div class="quick-action-btn" onclick="configurarRegistroRapido('entrada')">
                                <i class="fas fa-sign-in-alt text-success fs-3"></i>
                                <div class="fw-bold mt-2">Registrar Entrada</div>
                                <small class="text-muted">Hora actual</small>
                            </div>
                            <div class="quick-action-btn" onclick="configurarRegistroRapido('salida')">
                                <i class="fas fa-sign-out-alt text-danger fs-3"></i>
                                <div class="fw-bold mt-2">Registrar Salida</div>
                                <small class="text-muted">Hora actual</small>
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('asistencia-docente.store') }}" method="POST" id="formAsistencia">
                        @csrf

                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="docente_search" class="form-label">
                                        <i class="fas fa-chalkboard-teacher me-1"></i>
                                        Docente <span class="text-danger">*</span>
                                    </label>
                                    
                                    <!-- Contenedor del docente seleccionado -->
                                    <div class="selected-docente" id="selectedDocente">
                                        <span class="remove-btn" onclick="removeDocente()">×</span>
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <strong id="selectedName"></strong><br>
                                                <small>DNI: <span id="selectedDNI"></span></small><br>
                                                <small class="text-muted">Email: <span id="selectedEmail"></span></small>
                                            </div>
                                            <span class="badge bg-primary" id="selectedRole">Docente</span>
                                        </div>
                                    </div>

                                    <!-- Campo de búsqueda con autocompletado -->
                                    <div class="search-container">
                                        <input type="text" 
                                               class="form-control search-input" 
                                               id="docente_search" 
                                               placeholder="Buscar docente por nombre o DNI..."
                                               autocomplete="off">
                                        <i class="fas fa-search search-icon"></i>
                                        <div class="suggestions-dropdown" id="suggestions"></div>
                                    </div>

                                    <!-- Campo oculto para enviar el ID del docente -->
                                    <input type="hidden" id="docente_id" name="docente_id" 
                                           value="{{ old('docente_id') }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="fecha_hora" class="form-label">
                                        <i class="fas fa-calendar-clock me-1"></i>
                                        Fecha y Hora <span class="text-danger">*</span>
                                    </label>
                                    <input type="datetime-local" 
                                           class="form-control" 
                                           id="fecha_hora" 
                                           name="fecha_hora"
                                           value="{{ old('fecha_hora') }}" 
                                           required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="estado" class="form-label">
                                        <i class="fas fa-exchange-alt me-1"></i>
                                        Estado <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select" id="estado" name="estado" required>
                                        <option value="">Seleccionar estado...</option>
                                        <option value="entrada" {{ old('estado') == 'entrada' ? 'selected' : '' }}>
                                            <i class="fas fa-sign-in-alt"></i> Entrada
                                        </option>
                                        <option value="salida" {{ old('estado') == 'salida' ? 'selected' : '' }}>
                                            <i class="fas fa-sign-out-alt"></i> Salida
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="tipo_verificacion" class="form-label">
                                        <i class="fas fa-shield-alt me-1"></i>
                                        Tipo de Verificación
                                    </label>
                                    <select class="form-select" id="tipo_verificacion" name="tipo_verificacion">
                                        <option value="manual" {{ old('tipo_verificacion', 'manual') == 'manual' ? 'selected' : '' }}>Manual</option>
                                        <option value="biometrico" {{ old('tipo_verificacion') == 'biometrico' ? 'selected' : '' }}>Biométrico</option>
                                        <option value="tarjeta" {{ old('tipo_verificacion') == 'tarjeta' ? 'selected' : '' }}>Tarjeta</option>
                                        <option value="codigo" {{ old('tipo_verificacion') == 'codigo' ? 'selected' : '' }}>Código</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="terminal_id" class="form-label">
                                        <i class="fas fa-desktop me-1"></i>
                                        Terminal ID
                                    </label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="terminal_id" 
                                           name="terminal_id" 
                                           value="{{ old('terminal_id', 'MANUAL') }}" 
                                           placeholder="ID del terminal o dispositivo">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="codigo_trabajo" class="form-label">
                                        <i class="fas fa-briefcase me-1"></i>
                                        Código de Trabajo
                                    </label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="codigo_trabajo" 
                                           name="codigo_trabajo" 
                                           value="{{ old('codigo_trabajo') }}" 
                                           placeholder="Código de trabajo o proyecto (opcional)">
                                </div>
                            </div>
                        </div>

                        <!-- Información del Sistema -->
                        <div class="alert alert-info">
                            <h6><i class="fas fa-info-circle me-1"></i> Información</h6>
                            <p class="mb-0">
                                El sistema automáticamente asociará este registro con el horario correspondiente del docente 
                                si existe uno programado para la fecha y hora seleccionadas.
                            </p>
                        </div>

                        <!-- Información del Docente Seleccionado -->
                        <div class="docente-info" id="docenteInfo" style="display: none;">
                            <h6><i class="fas fa-user-info me-1"></i> Información del Docente</h6>
                            <div class="row" id="docenteDetalles"></div>
                        </div>

                        <div class="d-flex justify-content-between pt-3">
                            <a href="{{ route('asistencia-docente.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Volver
                            </a>
                            <button type="submit" class="btn btn-primary" id="btnGuardar">
                                <i class="fas fa-save me-1"></i> 
                                <span>Registrar Asistencia</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar con información adicional -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-history me-1"></i>
                        Registros Recientes
                    </h6>
                </div>
                <div class="card-body">
                    <div class="recent-activity" id="recentActivity">
                        <div class="text-center text-muted py-3">
                            <i class="fas fa-clock fs-3"></i>
                            <p class="mb-0 mt-2">Cargando registros recientes...</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-info-circle me-1"></i>
                        Información
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="estado-badge estado-entrada me-2">
                            <i class="fas fa-sign-in-alt me-1"></i>
                            Entrada
                        </div>
                        <small class="text-muted">Registra el inicio de actividades</small>
                    </div>
                    <div class="d-flex align-items-center mb-3">
                        <div class="estado-badge estado-salida me-2">
                            <i class="fas fa-sign-out-alt me-1"></i>
                            Salida
                        </div>
                        <small class="text-muted">Registra el fin de actividades</small>
                    </div>
                    <hr>
                    <p class="text-muted mb-0 small">
                        <i class="fas fa-lightbulb me-1"></i>
                        <strong>Tip:</strong> Usa las acciones rápidas para registrar con la hora actual automáticamente.
                    </p>
                </div>
            </div>
        </div>
    </div>

</div> <!-- container -->
@endsection

@push('js')
<script>
    // Datos reales de docentes desde la base de datos
    const docentes = @json($docentes);
    
    // Procesar datos para el formato esperado
    const docentesProcessed = docentes.map(function(docente) {
        return {
            id: docente.id,
            nombre: docente.nombre,
            apellido_paterno: docente.apellido_paterno,
            apellido_materno: docente.apellido_materno || '',
            numero_documento: docente.numero_documento,
            email: docente.email || 'No registrado',
            telefono: docente.telefono || 'No registrado'
        };
    });
    
    const searchInput = document.getElementById('docente_search');
    const suggestionsContainer = document.getElementById('suggestions');
    const docenteIdInput = document.getElementById('docente_id');
    const selectedDocenteDiv = document.getElementById('selectedDocente');
    const selectedNameSpan = document.getElementById('selectedName');
    const selectedDNISpan = document.getElementById('selectedDNI');
    const selectedEmailSpan = document.getElementById('selectedEmail');
    const docenteInfoDiv = document.getElementById('docenteInfo');
    const docenteDetallesDiv = document.getElementById('docenteDetalles');
    
    let currentFocus = -1;
    let filteredDocentes = [];

    // Inicializar formulario
    document.addEventListener('DOMContentLoaded', function() {
        establecerFechaHoraActual();
        cargarRegistrosRecientes();
    });

    function establecerFechaHoraActual() {
        const fechaHoraInput = document.getElementById('fecha_hora');
        if (!fechaHoraInput.value) {
            const now = new Date();
            const year = now.getFullYear();
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const day = String(now.getDate()).padStart(2, '0');
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            
            fechaHoraInput.value = `${year}-${month}-${day}T${hours}:${minutes}`;
        }
    }

    function configurarRegistroRapido(estado) {
        // Remover clase active de todos los botones
        document.querySelectorAll('.quick-action-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        
        // Activar el botón seleccionado
        event.currentTarget.classList.add('active');
        
        // Configurar el formulario
        document.getElementById('estado').value = estado;
        establecerFechaHoraActual();
        document.getElementById('tipo_verificacion').value = 'manual';
        
        // Scroll al formulario si es necesario
        document.getElementById('formAsistencia').scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }

    // Función para resaltar coincidencias
    function highlightMatch(text, searchTerm) {
        if (!searchTerm) return text;
        const regex = new RegExp(`(${searchTerm.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');
        return text.replace(regex, '<span class="text-primary">$1</span>');
    }

    // Función para buscar docentes
    function searchDocentes(searchTerm) {
        if (!searchTerm) return [];
        
        const term = searchTerm.toLowerCase();
        return docentesProcessed.filter(docente => {
            const nombreCompleto = `${docente.nombre} ${docente.apellido_paterno} ${docente.apellido_materno}`.toLowerCase();
            return nombreCompleto.includes(term) || docente.numero_documento.includes(term);
        });
    }

    // Función para mostrar sugerencias
    function showSuggestions(searchTerm) {
        filteredDocentes = searchDocentes(searchTerm);
        
        if (searchTerm.length === 0 || filteredDocentes.length === 0) {
            suggestionsContainer.style.display = 'none';
            if (searchTerm.length > 0 && filteredDocentes.length === 0) {
                suggestionsContainer.innerHTML = '<div class="no-results">No se encontraron docentes</div>';
                suggestionsContainer.style.display = 'block';
            }
            return;
        }

        let html = '';
        filteredDocentes.slice(0, 10).forEach((docente, index) => {
            const nombreCompleto = `${docente.nombre} ${docente.apellido_paterno} ${docente.apellido_materno}`;
            const highlightedNombre = highlightMatch(nombreCompleto, searchTerm);
            const highlightedDNI = highlightMatch(docente.numero_documento, searchTerm);
            
            html += `
                <div class="suggestion-item" data-index="${index}">
                    <div>${highlightedNombre}</div>
                    <div class="dni">DNI: ${highlightedDNI} | Email: ${docente.email}</div>
                </div>
            `;
        });

        suggestionsContainer.innerHTML = html;
        suggestionsContainer.style.display = 'block';
        currentFocus = -1;

        // Agregar eventos a las sugerencias
        document.querySelectorAll('.suggestion-item').forEach((item, index) => {
            item.addEventListener('click', function() {
                selectDocente(filteredDocentes[index]);
            });
        });
    }

    // Función para seleccionar un docente
    function selectDocente(docente) {
        const nombreCompleto = `${docente.nombre} ${docente.apellido_paterno} ${docente.apellido_materno}`;
        
        // Mostrar el docente seleccionado
        selectedNameSpan.textContent = nombreCompleto;
        selectedDNISpan.textContent = docente.numero_documento;
        selectedEmailSpan.textContent = docente.email;
        selectedDocenteDiv.style.display = 'block';
        
        // Mostrar información adicional
        docenteDetallesDiv.innerHTML = `
            <div class="col-md-6">
                <small class="text-muted">Teléfono:</small><br>
                <strong>${docente.telefono || 'No registrado'}</strong>
            </div>
            <div class="col-md-6">
                <small class="text-muted">ID del Sistema:</small><br>
                <strong>#${docente.id}</strong>
            </div>
        `;
        docenteInfoDiv.style.display = 'block';
        
        // Establecer el valor del campo oculto
        docenteIdInput.value = docente.id;
        
        // Limpiar y ocultar el campo de búsqueda
        searchInput.value = '';
        searchInput.style.display = 'none';
        suggestionsContainer.style.display = 'none';
    }

    // Función para remover el docente seleccionado
    function removeDocente() {
        selectedDocenteDiv.style.display = 'none';
        docenteInfoDiv.style.display = 'none';
        searchInput.style.display = 'block';
        docenteIdInput.value = '';
        searchInput.focus();
        
        // Limpiar acciones rápidas
        document.querySelectorAll('.quick-action-btn').forEach(btn => {
            btn.classList.remove('active');
        });
    }

    // Eventos del input
    searchInput.addEventListener('input', function() {
        showSuggestions(this.value);
    });

    // Navegación con teclado
    searchInput.addEventListener('keydown', function(e) {
        const items = suggestionsContainer.querySelectorAll('.suggestion-item');
        
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            currentFocus++;
            if (currentFocus >= items.length) currentFocus = 0;
            setActive(items);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            currentFocus--;
            if (currentFocus < 0) currentFocus = items.length - 1;
            setActive(items);
        } else if (e.key === 'Enter') {
            e.preventDefault();
            if (currentFocus > -1 && filteredDocentes[currentFocus]) {
                selectDocente(filteredDocentes[currentFocus]);
            }
        } else if (e.key === 'Escape') {
            suggestionsContainer.style.display = 'none';
            currentFocus = -1;
        }
    });

    // Función para marcar elemento activo
    function setActive(items) {
        items.forEach(item => item.classList.remove('active'));
        if (currentFocus >= 0 && currentFocus < items.length) {
            items[currentFocus].classList.add('active');
            items[currentFocus].scrollIntoView({ block: 'nearest' });
        }
    }

    // Cerrar sugerencias al hacer clic fuera
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.search-container') && e.target !== searchInput) {
            suggestionsContainer.style.display = 'none';
        }
    });

    function cargarRegistrosRecientes() {
        // Cargar registros recientes reales desde la base de datos
        fetch('{{ route("asistencia-docente.ultimas-procesadas") }}')
            .then(response => response.json())
            .then(data => {
                const container = document.getElementById('recentActivity');
                container.innerHTML = '';

                if (!data.success || data.registros.length === 0) {
                    container.innerHTML = `
                        <div class="text-center text-muted py-3">
                            <i class="fas fa-clipboard-list fs-3"></i>
                            <p class="mb-0 mt-2">No hay registros recientes</p>
                        </div>
                    `;
                    return;
                }

                data.registros.forEach(registro => {
                    const fecha = new Date(registro.fecha_hora);
                    const fechaFormateada = fecha.toLocaleDateString('es-PE') + ' ' + fecha.toLocaleTimeString('es-PE', {hour: '2-digit', minute: '2-digit'});
                    
                    const item = document.createElement('div');
                    item.className = 'activity-item';
                    item.innerHTML = `
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <strong class="d-block">${registro.docente_nombre}</strong>
                                <div class="d-flex align-items-center mt-1">
                                    <span class="estado-badge ${registro.estado === 'entrada' ? 'estado-entrada' : 'estado-salida'} me-2">
                                        <i class="fas fa-${registro.estado === 'entrada' ? 'sign-in-alt' : 'sign-out-alt'} me-1"></i>
                                        ${registro.estado.toUpperCase()}
                                    </span>
                                    <small class="text-muted">${registro.tipo_verificacion || 'manual'}</small>
                                </div>
                            </div>
                            <div class="text-end">
                                <small class="text-muted d-block">${fechaFormateada}</small>
                                <small class="text-muted">${registro.terminal_id || 'MANUAL'}</small>
                            </div>
                        </div>
                    `;
                    container.appendChild(item);
                });
            })
            .catch(error => {
                console.error('Error al cargar registros recientes:', error);
                const container = document.getElementById('recentActivity');
                container.innerHTML = `
                    <div class="text-center text-muted py-3">
                        <i class="fas fa-exclamation-triangle fs-3 text-warning"></i>
                        <p class="mb-0 mt-2">Error al cargar registros</p>
                    </div>
                `;
            });
    }

    // Manejar envío del formulario
    document.getElementById('formAsistencia').addEventListener('submit', function(e) {
        const btnGuardar = document.getElementById('btnGuardar');
        const btnText = btnGuardar.querySelector('span');
        
        // Mostrar estado de carga
        btnGuardar.disabled = true;
        btnText.textContent = 'Guardando...';
        btnGuardar.insertAdjacentHTML('afterbegin', '<span class="spinner-border spinner-border-sm me-2"></span>');
    });

    // Si hay un valor old() de Laravel, pre-seleccionar el docente
    @if(old('docente_id'))
        const oldDocenteId = "{{ old('docente_id') }}";
        const oldDocente = docentes.find(d => d.id == oldDocenteId);
        if (oldDocente) {
            selectDocente(oldDocente);
        }
    @endif

    // Hacer las funciones globales
    window.removeDocente = removeDocente;
    window.configurarRegistroRapido = configurarRegistroRapido;
</script>
@endpush