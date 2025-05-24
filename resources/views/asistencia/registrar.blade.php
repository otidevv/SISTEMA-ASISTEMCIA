@extends('layouts.app')

@section('title', 'Registrar Asistencia')

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

    .selected-student {
        padding: 10px;
        background-color: #e7f3ff;
        border: 1px solid #b3d9ff;
        border-radius: 0.25rem;
        margin-bottom: 10px;
        display: none;
    }

    .selected-student .remove-btn {
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
                            <li class="breadcrumb-item"><a href="{{ route('asistencia.index') }}">Asistencia</a></li>
                            <li class="breadcrumb-item active">Registrar Asistencia</li>
                        </ol>
                    </div>
                    <h4 class="page-title">Registrar Asistencia</h4>
                </div>
            </div>
        </div>
        <!-- end page title -->

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="header-title">Nuevo Registro de Asistencia</h4>

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form action="{{ route('asistencia.registrar.store') }}" method="POST">
                            @csrf

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="estudiante_search" class="form-label">Estudiante <span
                                                class="text-danger">*</span></label>
                                        
                                        <!-- Contenedor del estudiante seleccionado -->
                                        <div class="selected-student" id="selectedStudent">
                                            <span class="remove-btn" onclick="removeStudent()">×</span>
                                            <strong id="selectedName"></strong><br>
                                            <small>DNI: <span id="selectedDNI"></span></small>
                                        </div>

                                        <!-- Campo de búsqueda con autocompletado -->
                                        <div class="search-container">
                                            <input type="text" 
                                                   class="form-control search-input" 
                                                   id="estudiante_search" 
                                                   placeholder="Buscar por nombre o DNI..."
                                                   autocomplete="off">
                                            <i class="fas fa-search search-icon"></i>
                                            <div class="suggestions-dropdown" id="suggestions"></div>
                                        </div>

                                        <!-- Campo oculto para enviar el DNI -->
                                        <input type="hidden" id="nro_documento" name="nro_documento" 
                                               value="{{ old('nro_documento') }}" required>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="fecha_hora" class="form-label">Fecha y Hora <span
                                                class="text-danger">*</span></label>
                                        <input type="datetime-local" class="form-control" id="fecha_hora" name="fecha_hora"
                                            value="{{ old('fecha_hora', date('Y-m-d\TH:i')) }}" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="tipo_verificacion" class="form-label">Tipo de Verificación <span
                                                class="text-danger">*</span></label>
                                        <select class="form-select" id="tipo_verificacion" name="tipo_verificacion"
                                            required>
                                            <option value="0" {{ old('tipo_verificacion') == '0' ? 'selected' : '' }}>
                                                Huella digital</option>
                                            <option value="1" {{ old('tipo_verificacion') == '1' ? 'selected' : '' }}>
                                                Tarjeta RFID</option>
                                            <option value="2" {{ old('tipo_verificacion') == '2' ? 'selected' : '' }}>
                                                Facial</option>
                                            <option value="3" {{ old('tipo_verificacion') == '3' ? 'selected' : '' }}>
                                                Código QR</option>
                                            <option value="4"
                                                {{ old('tipo_verificacion', '4') == '4' ? 'selected' : '' }}>Manual
                                            </option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="codigo_trabajo" class="form-label">Código de Trabajo</label>
                                        <input type="text" class="form-control" id="codigo_trabajo" name="codigo_trabajo"
                                            value="{{ old('codigo_trabajo') }}">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="terminal_id" class="form-label">Terminal ID</label>
                                        <input type="text" class="form-control" id="terminal_id" name="terminal_id"
                                            value="{{ old('terminal_id') }}">
                                    </div>
                                </div>
                            </div>

                            <div class="text-end">
                                <button type="submit" class="btn btn-success">Guardar</button>
                                <a href="{{ route('asistencia.index') }}" class="btn btn-secondary">Cancelar</a>
                            </div>
                        </form>
                    </div> <!-- end card-body -->
                </div> <!-- end card -->
            </div> <!-- end col -->
        </div> <!-- end row -->

    </div> <!-- container -->
@endsection

@push('js')
<script>
    // Convertir los datos de PHP a JavaScript
    const estudiantes = @json($estudiantes);
    
    const searchInput = document.getElementById('estudiante_search');
    const suggestionsContainer = document.getElementById('suggestions');
    const nroDocumentoInput = document.getElementById('nro_documento');
    const selectedStudentDiv = document.getElementById('selectedStudent');
    const selectedNameSpan = document.getElementById('selectedName');
    const selectedDNISpan = document.getElementById('selectedDNI');
    
    let currentFocus = -1;
    let filteredEstudiantes = [];

    // Función para resaltar coincidencias
    function highlightMatch(text, searchTerm) {
        if (!searchTerm) return text;
        const regex = new RegExp(`(${searchTerm.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');
        return text.replace(regex, '<span class="text-primary">$1</span>');
    }

    // Función para buscar estudiantes
    function searchEstudiantes(searchTerm) {
        if (!searchTerm) return [];
        
        const term = searchTerm.toLowerCase();
        return estudiantes.filter(estudiante => {
            const nombreCompleto = `${estudiante.nombre} ${estudiante.apellido_paterno} ${estudiante.apellido_materno}`.toLowerCase();
            return nombreCompleto.includes(term) || estudiante.numero_documento.includes(term);
        });
    }

    // Función para mostrar sugerencias
    function showSuggestions(searchTerm) {
        filteredEstudiantes = searchEstudiantes(searchTerm);
        
        if (searchTerm.length === 0 || filteredEstudiantes.length === 0) {
            suggestionsContainer.style.display = 'none';
            if (searchTerm.length > 0 && filteredEstudiantes.length === 0) {
                suggestionsContainer.innerHTML = '<div class="no-results">No se encontraron resultados</div>';
                suggestionsContainer.style.display = 'block';
            }
            return;
        }

        let html = '';
        filteredEstudiantes.slice(0, 10).forEach((estudiante, index) => {
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

        suggestionsContainer.innerHTML = html;
        suggestionsContainer.style.display = 'block';
        currentFocus = -1;

        // Agregar eventos a las sugerencias
        document.querySelectorAll('.suggestion-item').forEach((item, index) => {
            item.addEventListener('click', function() {
                selectEstudiante(filteredEstudiantes[index]);
            });
        });
    }

    // Función para seleccionar un estudiante
    function selectEstudiante(estudiante) {
        const nombreCompleto = `${estudiante.nombre} ${estudiante.apellido_paterno} ${estudiante.apellido_materno}`;
        
        // Mostrar el estudiante seleccionado
        selectedNameSpan.textContent = nombreCompleto;
        selectedDNISpan.textContent = estudiante.numero_documento;
        selectedStudentDiv.style.display = 'block';
        
        // Establecer el valor del campo oculto
        nroDocumentoInput.value = estudiante.numero_documento;
        
        // Limpiar y ocultar el campo de búsqueda
        searchInput.value = '';
        searchInput.style.display = 'none';
        suggestionsContainer.style.display = 'none';
    }

    // Función para remover el estudiante seleccionado
    function removeStudent() {
        selectedStudentDiv.style.display = 'none';
        searchInput.style.display = 'block';
        nroDocumentoInput.value = '';
        searchInput.focus();
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
            if (currentFocus > -1 && filteredEstudiantes[currentFocus]) {
                selectEstudiante(filteredEstudiantes[currentFocus]);
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

    // Si hay un valor old() de Laravel, pre-seleccionar el estudiante
    @if(old('nro_documento'))
        const oldDNI = "{{ old('nro_documento') }}";
        const oldEstudiante = estudiantes.find(e => e.numero_documento === oldDNI);
        if (oldEstudiante) {
            selectEstudiante(oldEstudiante);
        }
    @endif

    // Hacer la función removeStudent global
    window.removeStudent = removeStudent;
</script>
@endpush

