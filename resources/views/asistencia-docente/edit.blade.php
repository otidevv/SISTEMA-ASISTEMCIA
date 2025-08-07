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
                                            @php
                                                $nombreCompleto = trim($docente->apellido_paterno . ' ' . 
                                                                     ($docente->apellido_materno ? $docente->apellido_materno . ' ' : '') . 
                                                                     $docente->nombre);
                                                
                                                // ✅ LÓGICA DE AUTO-SELECCIÓN EN PHP
                                                $esSeleccionado = false;
                                                
                                                // Método 1: Si hay usuario_id, usar ese
                                                if ($asistencia->usuario_id && $asistencia->usuario_id == $docente->id) {
                                                    $esSeleccionado = true;
                                                }
                                                // Método 2: Si no hay usuario_id, buscar por documento
                                                elseif (!$asistencia->usuario_id && $docente->numero_documento == $asistencia->nro_documento) {
                                                    $esSeleccionado = true;
                                                }
                                                // Método 3: Respetar old() si hay error de validación
                                                elseif (old('docente_id') == $docente->id) {
                                                    $esSeleccionado = true;
                                                }
                                            @endphp
                                            <option value="{{ $docente->id }}" 
                                                    {{ $esSeleccionado ? 'selected' : '' }}
                                                    data-documento="{{ $docente->numero_documento }}"
                                                    data-nombre-completo="{{ $nombreCompleto }}">
                                                {{ $nombreCompleto }} - {{ $docente->numero_documento }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('docente_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Documento original: {{ $asistencia->nro_documento }}</small>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="fecha_hora" class="form-label">Fecha y Hora <span class="text-danger">*</span></label>
                                    <input type="datetime-local" 
                                           class="form-control @error('fecha_hora') is-invalid @enderror" 
                                           id="fecha_hora" 
                                           name="fecha_hora" 
                                           value="{{ old('fecha_hora', \Carbon\Carbon::parse($asistencia->fecha_registro)->format('Y-m-d\TH:i')) }}" 
                                           required>
                                    @error('fecha_hora')
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
                                        @php
                                            $horaActual = \Carbon\Carbon::parse($asistencia->fecha_registro)->format('H:i');
                                            $estadoSugerido = $horaActual < '12:00' ? 'entrada' : 'salida';
                                        @endphp
                                        <option value="entrada" {{ old('estado', $estadoSugerido) == 'entrada' ? 'selected' : '' }}>
                                            Entrada
                                        </option>
                                        <option value="salida" {{ old('estado', $estadoSugerido) == 'salida' ? 'selected' : '' }}>
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
                                        @php
                                            $tipos = [0 => 'biometrico', 1 => 'tarjeta', 2 => 'facial', 3 => 'codigo', 4 => 'manual'];
                                            $tipoActual = $tipos[$asistencia->tipo_verificacion] ?? 'manual';
                                        @endphp
                                        <option value="manual" {{ old('tipo_verificacion', $tipoActual) == 'manual' ? 'selected' : '' }}>Manual</option>
                                        <option value="biometrico" {{ old('tipo_verificacion', $tipoActual) == 'biometrico' ? 'selected' : '' }}>Biométrico</option>
                                        <option value="tarjeta" {{ old('tipo_verificacion', $tipoActual) == 'tarjeta' ? 'selected' : '' }}>Tarjeta</option>
                                        <option value="codigo" {{ old('tipo_verificacion', $tipoActual) == 'codigo' ? 'selected' : '' }}>Código</option>
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

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('asistencia-docente.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Volver
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Actualizar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
{{-- JavaScript removido - auto-selección ahora se hace en PHP --}}
<script>
// Efecto visual opcional para confirmar la selección
document.addEventListener('DOMContentLoaded', function() {
    var select = document.getElementById('docente_id');
    if (select && select.value && select.value !== '') {
        // Mostrar brevemente que se auto-seleccionó
        select.style.backgroundColor = '#d4edda';
        select.style.borderColor = '#28a745';
        
        setTimeout(function() {
            select.style.backgroundColor = '';
            select.style.borderColor = '';
        }, 2000);
    }
});
</script>
@endpush