@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-plus"></i> Registrar Asistencia Docente
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('asistencia-docente.store') }}" method="POST">
                        @csrf

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="docente_id" class="form-label">Docente <span class="text-danger">*</span></label>
                                    <select class="form-select @error('docente_id') is-invalid @enderror" 
                                            id="docente_id" name="docente_id" required>
                                        <option value="">Seleccionar docente...</option>
                                        @foreach($docentes as $docente)
                                            <option value="{{ $docente->id }}" {{ old('docente_id') == $docente->id ? 'selected' : '' }}>
                                                {{ $docente->nombre }} {{ $docente->apellido_paterno }} - {{ $docente->numero_documento }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('docente_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="fecha_hora" class="form-label">Fecha y Hora <span class="text-danger">*</span></label>
                                    <input type="datetime-local" 
                                           class="form-control @error('fecha_hora') is-invalid @enderror" 
                                           id="fecha_hora" 
                                           name="fecha_hora" 
                                           value="{{ old('fecha_hora') }}" 
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
                                        <option value="entrada" {{ old('estado') == 'entrada' ? 'selected' : '' }}>
                                            <i class="fas fa-sign-in-alt"></i> Entrada
                                        </option>
                                        <option value="salida" {{ old('estado') == 'salida' ? 'selected' : '' }}>
                                            <i class="fas fa-sign-out-alt"></i> Salida
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
                                        <option value="manual" {{ old('tipo_verificacion', 'manual') == 'manual' ? 'selected' : '' }}>Manual</option>
                                        <option value="biometrico" {{ old('tipo_verificacion') == 'biometrico' ? 'selected' : '' }}>Biométrico</option>
                                        <option value="tarjeta" {{ old('tipo_verificacion') == 'tarjeta' ? 'selected' : '' }}>Tarjeta</option>
                                        <option value="codigo" {{ old('tipo_verificacion') == 'codigo' ? 'selected' : '' }}>Código</option>
                                    </select>
                                    @error('tipo_verificacion')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
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
                                           value="{{ old('terminal_id', 'MANUAL') }}" 
                                           placeholder="ID del terminal o dispositivo">
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
                                           value="{{ old('codigo_trabajo') }}" 
                                           placeholder="Código de trabajo o proyecto">
                                    @error('codigo_trabajo')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <h6><i class="fas fa-info-circle"></i> Información</h6>
                            <p class="mb-0">El sistema automáticamente asociará este registro con el horario correspondiente del docente si existe uno programado para la fecha y hora seleccionadas.</p>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('asistencia-docente.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Volver
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Registrar Asistencia
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
<script>
    // Establecer fecha y hora actual por defecto
    document.addEventListener('DOMContentLoaded', function() {
        const fechaHoraInput = document.getElementById('fecha_hora');
        
        // Si no hay valor, establecer la fecha y hora actual
        if (!fechaHoraInput.value) {
            const now = new Date();
            const year = now.getFullYear();
            const month = String(now.getMonth() + 1).padStart(2, '0');
            const day = String(now.getDate()).padStart(2, '0');
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            
            fechaHoraInput.value = `${year}-${month}-${day}T${hours}:${minutes}`;
        }
    });
</script>
@endpush
