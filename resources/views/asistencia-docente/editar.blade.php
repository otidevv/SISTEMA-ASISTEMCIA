@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-edit me-2"></i> Editar Asistencia Docente
                    </h5>
                    <small>
                        {{-- CORRECCIÓN: Header con nombres completos --}}
                        {{ $asistencia->usuario ? 
                            $asistencia->usuario->nombre . ' ' . 
                            $asistencia->usuario->apellido_paterno . 
                            ($asistencia->usuario->apellido_materno ? ' ' . $asistencia->usuario->apellido_materno : '') 
                            : 'Doc: ' . $asistencia->nro_documento }}
                        | {{ \Carbon\Carbon::parse($asistencia->fecha_registro)->format('d/m/Y H:i:s') }}
                    </small>
                </div>
                <div class="card-body">
                    <form action="{{ route('asistencia-docente.update', $asistencia->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="docente_id" class="form-label">Docente <span class="text-danger">*</span></label>
                                    <select class="form-select @error('docente_id') is-invalid @enderror" 
                                            id="docente_id" name="docente_id" required>
                                        <option value="">Seleccionar docente...</option>
                                        @foreach($docentes as $docente)
                                            {{-- CORRECCIÓN: Nombre completo con apellido materno --}}
                                            @php
                                                $nombreCompleto = trim($docente->apellido_paterno . ' ' . 
                                                                     ($docente->apellido_materno ? $docente->apellido_materno . ' ' : '') . 
                                                                     $docente->nombre);
                                            @endphp
                                            <option value="{{ $docente->id }}" 
                                                    {{ old('docente_id', $asistencia->usuario_id) == $docente->id ? 'selected' : '' }}
                                                    data-documento="{{ $docente->numero_documento }}"
                                                    data-nombre-completo="{{ $nombreCompleto }}">
                                                {{ $nombreCompleto }} - {{ $docente->numero_documento }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('docente_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted" id="docente_nro_documento_display">Documento: {{ $asistencia->nro_documento }}</small>
                                    <input type="hidden" name="nro_documento" id="nro_documento_hidden" value="{{ old('nro_documento', $asistencia->nro_documento) }}">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="fecha_registro" class="form-label">Fecha y Hora <span class="text-danger">*</span></label>
                                    <input type="datetime-local" 
                                           class="form-control @error('fecha_registro') is-invalid @enderror" 
                                           id="fecha_registro" 
                                           name="fecha_registro" 
                                           value="{{ old('fecha_registro', \Carbon\Carbon::parse($asistencia->fecha_registro)->format('Y-m-d\TH:i')) }}" 
                                           required>
                                    @error('fecha_registro')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="estado" class="form-label">Estado <span class="text-danger">*</span></label>
                                    <select class="form-select @error('estado') is-invalid @enderror"
                                            id="estado" name="estado" required>
                                        <option value="">Seleccionar estado...</option>
                                        <option value="1" {{ (old('estado', $asistencia->estado) == 1 || old('estado', $asistencia->estado) == 'entrada') ? 'selected' : '' }}>
                                            Entrada
                                        </option>
                                        <option value="0" {{ (old('estado', $asistencia->estado) == 0 || old('estado', $asistencia->estado) == 'salida') ? 'selected' : '' }}>
                                            Salida
                                        </option>
                                    </select>
                                    @error('estado')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="tipo_verificacion" class="form-label">Tipo de Verificación</label>
                                    <select class="form-select @error('tipo_verificacion') is-invalid @enderror" 
                                            id="tipo_verificacion" name="tipo_verificacion">
                                        <option value="4" {{ (old('tipo_verificacion', $asistencia->tipo_verificacion) == 4 || old('tipo_verificacion', $asistencia->tipo_verificacion) == 'manual') ? 'selected' : '' }}>Manual</option>
                                        <option value="0" {{ (old('tipo_verificacion', $asistencia->tipo_verificacion) == 0 || old('tipo_verificacion', $asistencia->tipo_verificacion) == 'biometrico') ? 'selected' : '' }}>Biométrico</option>
                                        <option value="1" {{ (old('tipo_verificacion', $asistencia->tipo_verificacion) == 1 || old('tipo_verificacion', $asistencia->tipo_verificacion) == 'tarjeta') ? 'selected' : '' }}>Tarjeta</option>
                                        <option value="3" {{ (old('tipo_verificacion', $asistencia->tipo_verificacion) == 3 || old('tipo_verificacion', $asistencia->tipo_verificacion) == 'codigo') ? 'selected' : '' }}>Código</option>
                                        <option value="2" {{ (old('tipo_verificacion', $asistencia->tipo_verificacion) == 2 || old('tipo_verificacion', $asistencia->tipo_verificacion) == 'facial') ? 'selected' : '' }}>Facial</option>
                                    </select>
                                    @error('tipo_verificacion')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Actual: {{ $asistencia->tipo_verificacion }} ({{ $tipoActual }})</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="terminal_id" class="form-label">Terminal ID</label>
                                    <input type="text" 
                                           class="form-control @error('terminal_id') is-invalid @enderror" 
                                           id="terminal_id" 
                                           name="terminal_id" 
                                           value="{{ old('terminal_id', $asistencia->terminal_id) }}" 
                                           placeholder="ID del terminal">
                                    @error('terminal_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="codigo_trabajo" class="form-label">Código de Trabajo</label>
                                    <input type="text" 
                                           class="form-control @error('codigo_trabajo') is-invalid @enderror" 
                                           id="codigo_trabajo" 
                                           name="codigo_trabajo" 
                                           value="{{ old('codigo_trabajo', $asistencia->codigo_trabajo) }}" 
                                           placeholder="Código de trabajo">
                                    @error('codigo_trabajo')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <h6><i class="fas fa-info-circle"></i> Información del Registro</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>ID:</strong> {{ $asistencia->id }}</p>
                                    <p class="mb-1"><strong>Documento:</strong> {{ $asistencia->nro_documento }}</p>
                                    <p class="mb-0"><strong>Fecha Original:</strong> {{ \Carbon\Carbon::parse($asistencia->fecha_registro)->format('d/m/Y H:i:s') }}</p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-1"><strong>Terminal:</strong> {{ $asistencia->terminal_id ?? 'N/A' }}</p>
                                    <p class="mb-1"><strong>Usuario ID:</strong> {{ $asistencia->usuario_id ?? 'N/A' }}</p>
                                    <p class="mb-0"><strong>Dispositivo:</strong> {{ $asistencia->sn_dispositivo ?? 'N/A' }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center">
                            <a href="{{ route('asistencia-docente.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Volver
                            </a>
                            <div>
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="fas fa-save"></i> Actualizar
                                </button>
                                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#confirmDeleteModal">
                                    <i class="fas fa-trash"></i> Eliminar
                                </button>
                            </div>
                        </div>
                    </form>

                    <!-- Modal de Confirmación de Eliminación -->
                    <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="confirmDeleteModalLabel">Confirmar Eliminación</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    ¿Estás seguro de que deseas eliminar esta asistencia? Esta acción no se puede deshacer.
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                    <form action="{{ route('asistencia-docente.destroy', $asistencia->id) }}" method="POST" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger">Eliminar</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 INICIANDO AUTO-SELECCIÓN DE DOCENTE (VERSIÓN MEJORADA)...');
    
    const selectDocente = document.getElementById('docente_id');
    const nroDocumentoHidden = document.getElementById('nro_documento_hidden');
    const docenteNroDocumentoDisplay = document.getElementById('docente_nro_documento_display');
    const usuarioId = {{ $asistencia->usuario_id ?? 'null' }};
    const documento = '{{ $asistencia->nro_documento }}';
    
    console.log('📋 DATOS DEL REGISTRO:');
    console.log('   👤 Usuario ID:', usuarioId, '(Tipo:', typeof usuarioId, ')');
    console.log('   📄 Documento:', documento);
    console.log('   🎯 Valor actual select:', selectDocente.value);
    console.log('   📊 Total opciones disponibles:', selectDocente.options.length);
    
    // Verificar si el usuario_id es válido
    const usuarioIdValido = usuarioId && usuarioId !== null && usuarioId !== 'null' && usuarioId !== '';
    console.log('   ✅ Usuario ID válido:', usuarioIdValido);

    // Function to update hidden nro_documento and display
    function updateNroDocumento() {
        const selectedOption = selectDocente.options[selectDocente.selectedIndex];
        const selectedDocumento = selectedOption ? selectedOption.getAttribute('data-documento') : '';
        nroDocumentoHidden.value = selectedDocumento;
        docenteNroDocumentoDisplay.textContent = `Documento: ${selectedDocumento}`;
    }
    
    // Función principal de auto-selección mejorada
    function autoSeleccionarDocente() {
        let encontrado = false;
        let metodoExitoso = '';
        
        console.log('🔍 INICIANDO BÚSQUEDA SISTEMÁTICA...');
        
        // MÉTODO 1: Por usuario_id (solo si es válido)
        if (usuarioIdValido) {
            console.log('🔍 Método 1: Buscando por usuario_id:', usuarioId);
            const optionById = selectDocente.querySelector(`option[value="${usuarioId}"]`);
            if (optionById) {
                optionById.selected = true;
                selectDocente.value = usuarioId;
                encontrado = true;
                metodoExitoso = 'usuario_id';
                console.log('✅ ENCONTRADO por usuario_id!');
                console.log('   📝 Docente:', optionById.textContent.trim());
                marcarExito(selectDocente, '#d4edda', '#28a745', 'Usuario ID');
                updateNroDocumento(); // Update nro_documento after selection
                return { encontrado: true, metodo: metodoExitoso };
            } else {
                console.log('❌ No encontrado por usuario_id');
            }
        } else {
            console.log('⚠️ Saltando búsqueda por usuario_id (no válido o N/A)');
        }
        
        // MÉTODO 2: Por documento (MÉTODO PRINCIPAL para registros sin usuario_id)
        if (!encontrado && documento && documento.trim() !== '') {
            console.log('🔍 Método 2: Buscando por documento:', documento);
            const options = selectDocente.querySelectorAll('option[data-documento]');
            console.log('   📊 Opciones con data-documento:', options.length);
            
            for (let i = 0; i < options.length; i++) {
                const option = options[i];
                const optionDoc = option.getAttribute('data-documento');
                console.log(`   🔍 Opción ${i+1}: documento="${optionDoc}", valor="${option.value}", texto="${option.textContent.trim()}"`);
                
                if (optionDoc === documento) {
                    option.selected = true;
                    selectDocente.value = option.value;
                    encontrado = true;
                    metodoExitoso = 'documento';
                    console.log('✅ ¡ENCONTRADO por documento exacto!');
                    console.log('   📝 Docente:', option.textContent.trim());
                    console.log('   🆔 ID del docente:', option.value);
                    marcarExito(selectDocente, '#fff3cd', '#ffc107', 'Documento');
                    
                    // Disparar evento change
                    const changeEvent = new Event('change', { bubbles: true });
                    selectDocente.dispatchEvent(changeEvent);
                    updateNroDocumento(); // Update nro_documento after selection
                    
                    return { encontrado: true, metodo: metodoExitoso };
                }
            }
            
            console.log('❌ No encontrado por documento exacto');
        }
        
        if (!encontrado) {
            console.error('❌ NO SE PUDO ENCONTRAR EL DOCENTE');
            mostrarInformacionDebug();
            marcarError(selectDocente);
        }
        
        return { encontrado, metodo: metodoExitoso };
    }
    
    // Función para mostrar información de debug detallada
    function mostrarInformacionDebug() {
        console.log('📊 INFORMACIÓN DE DEBUG DETALLADA:');
        console.log('   🎯 Documento buscado:', documento);
        console.log('   👤 Usuario ID buscado:', usuarioId);
        console.log('   👥 TODAS LAS OPCIONES DISPONIBLES:');
        
        for (let i = 0; i < selectDocente.options.length; i++) {
            const option = selectDocente.options[i];
            const id = option.value;
            const doc = option.getAttribute('data-documento');
            const texto = option.textContent.trim();
            
            if (id) { // Solo mostrar opciones válidas (no la opción vacía)
                console.log(`      ${i}. ID="${id}", DOC="${doc}", TEXTO="${texto}"`);
                
                // Verificar coincidencias
                if (doc === documento) {
                    console.log(`         ⭐ COINCIDENCIA EXACTA DE DOCUMENTO!`);
                }
                if (id == usuarioId) {
                    console.log(`         ⭐ COINCIDENCIA EXACTA DE USUARIO_ID!`);
                }
            }
        }
        
        // Sugerencias de solución
        console.log('   💡 SUGERENCIAS:');
        console.log('      1. Verificar que el documento existe en la base de datos');
        console.log('      2. Verificar que el docente tiene rol "profesor"');
        console.log('      3. Verificar la consulta que trae los docentes al controlador');
        console.log('      4. Verificar que se incluye apellido_materno en la consulta');
    }
    
    // Función para marcar éxito visual
    function marcarExito(element, bgColor, borderColor, metodo) {
        element.style.backgroundColor = bgColor;
        element.style.borderColor = borderColor;
        element.style.transition = 'all 0.3s ease';
        element.style.boxShadow = `0 0 0 0.2rem ${borderColor}25`;
        
        // Mostrar mensaje de éxito
        mostrarMensajeTemporal(`✅ Docente auto-seleccionado por ${metodo}`, 'success');
        
        setTimeout(() => {
            element.style.backgroundColor = '';
            element.style.borderColor = '';
            element.style.boxShadow = '';
        }, 4000);
    }
    
    // Función para marcar error visual
    function marcarError(element) {
        element.style.backgroundColor = '#f8d7da';
        element.style.borderColor = '#dc3545';
        element.style.transition = 'all 0.3s ease';
        element.style.boxShadow = '0 0 0 0.2rem #dc354525';
        
        mostrarMensajeTemporal('🚨 No se pudo auto-seleccionar. Ver consola para detalles.', 'error');
        
        console.log('🚨 Campo marcado en rojo - necesita selección manual');
    }
    
    // Función para mostrar mensajes temporales
    function mostrarMensajeTemporal(mensaje, tipo) {
        // Remover mensajes anteriores
        const existingAlerts = document.querySelectorAll('.auto-select-alert');
        existingAlerts.forEach(alert => alert.remove());
        
        const alertClass = tipo === 'success' ? 'alert-success' : 'alert-danger';
        const iconos = tipo === 'success' ? '✅' : '🚨';
        
        const alert = document.createElement('div');
        alert.className = `alert ${alertClass} alert-dismissible fade show position-fixed auto-select-alert`;
        alert.style.cssText = `
            top: 20px; 
            right: 20px; 
            z-index: 9999; 
            min-width: 350px;
            max-width: 500px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        `;
        alert.innerHTML = `
            <div style="display: flex; align-items: center;">
                <span style="margin-right: 8px; font-size: 16px;">${iconos}</span>
                <div>
                    <strong>Auto-selección:</strong><br>
                    <small>${mensaje}</small>
                </div>
                <button type="button" class="btn-close ms-auto" onclick="this.parentElement.parentElement.remove()"></button>
            </div>
        `;
        
        document.body.appendChild(alert);
        
        // Auto-remover después de unos segundos
        setTimeout(() => {
            if (alert.parentNode) {
                alert.remove();
            }
        }, tipo === 'success' ? 5000 : 8000);
    }
    
    // EJECUTAR AUTO-SELECCIÓN PRINCIPAL
    const valorActual = selectDocente.value;
    
    if (!valorActual || valorActual === '') {
        console.log('⚡ Campo vacío, ejecutando auto-selección...');
        const resultado = autoSeleccionarDocente();
        
        // VERIFICACIÓN FINAL DETALLADA
        setTimeout(() => {
            const valorFinal = selectDocente.value;
            const opcionSeleccionada = selectDocente.options[selectDocente.selectedIndex];
            const textoFinal = opcionSeleccionada?.textContent || 'Ninguno';
            const documentoFinal = opcionSeleccionada?.getAttribute('data-documento') || 'N/A';
            
            console.log('🏁 RESULTADO FINAL:');
            console.log('   🎯 Valor seleccionado:', valorFinal);
            console.log('   📝 Docente seleccionado:', textoFinal);
            console.log('   📄 Documento del docente:', documentoFinal);
            console.log('   🔧 Método usado:', resultado.metodo || 'ninguno');
            console.log('   ✅ Éxito:', resultado.encontrado);
            
            if (resultado.encontrado) {
                console.log('🎉 ¡AUTO-SELECCIÓN EXITOSA!');
                
                // Verificar que la selección es correcta
                if (documentoFinal === documento) {
                    console.log('✅ VERIFICACIÓN: Documento coincide perfectamente');
                } else {
                    console.log('⚠️ ADVERTENCIA: Documento no coincide exactamente');
                    console.log('   📄 Esperado:', documento);
                    console.log('   📄 Obtenido:', documentoFinal);
                }
            } else {
                console.log('🚨 AUTO-SELECCIÓN FALLÓ');
                console.log('💡 ACCIONES RECOMENDADAS:');
                console.log('   1. Verificar que el docente existe en la base de datos');
                console.log('   2. Verificar que tiene rol "profesor"');
                console.log('   3. Verificar la consulta que trae los docentes al controlador');
                console.log('   4. Seleccionar manualmente el docente correcto');
            }
        }, 200);
        
    } else {
        console.log('ℹ️ Ya hay un valor seleccionado:', valorActual);
        
        // Verificar si la selección actual es correcta
        const opcionActual = selectDocente.querySelector(`option[value="${valorActual}"]`);
        const docActual = opcionActual?.getAttribute('data-documento');
        
        console.log('🔍 Verificando selección actual...');
        console.log('   📄 Documento en opción actual:', docActual);
        console.log('   📄 Documento esperado:', documento);
        
        if (docActual !== documento) {
            console.log('⚠️ La selección actual no coincide con el documento, corrigiendo...');
            autoSeleccionarDocente();
        } else {
            console.log('✅ La selección actual es correcta');
            mostrarMensajeTemporal('✅ Docente ya seleccionado correctamente', 'success');
        }
    }
    
    // Initial update of nro_documento_hidden and display
    updateNroDocumento();

    // Event listener para limpiar estilos cuando usuario cambie manualmente
    selectDocente.addEventListener('change', function() {
        this.style.backgroundColor = '';
        this.style.borderColor = '';
        this.style.boxShadow = '';
        
        const opcionSeleccionada = this.options[this.selectedIndex];
        const nombreDocente = opcionSeleccionada?.textContent?.trim() || 'Desconocido';
        const documentoDocente = opcionSeleccionada?.getAttribute('data-documento') || 'N/A';
        
        console.log('👤 Usuario cambió selección manualmente:');
        console.log('   🆔 ID:', this.value);
        console.log('   📝 Nombre:', nombreDocente);
        console.log('   📄 Documento:', documentoDocente);
        
        // Remover alertas
        const existingAlerts = document.querySelectorAll('.auto-select-alert');
        existingAlerts.forEach(alert => alert.remove());

        updateNroDocumento(); // Update nro_documento when docente changes manually
    });
    
    console.log('🏁 Script de auto-selección inicializado completamente');
});
</script>
@endpush