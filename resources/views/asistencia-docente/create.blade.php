@extends('layouts.app')

@section('content')
<div class="container">
    <h4 class="mb-4">Registrar Asistencia Docente</h4>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('asistencia-docente.store') }}" method="POST">
        @csrf

        {{-- Docente --}}
        <div class="mb-3">
            <label for="docente_id" class="form-label">Docente</label>
            <select name="docente_id" class="form-select" required>
                <option value="">Seleccione un docente</option>
                @foreach ($docentes as $docente)
                    <option value="{{ $docente->id }}">{{ $docente->nombre_completo }}</option>
                        {{ $docente->nombres }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Fecha y hora --}}
        <div class="mb-3">
            <label for="fecha_hora" class="form-label">Fecha y Hora</label>
            <input type="datetime-local" name="fecha_hora" class="form-control" value="{{ old('fecha_hora') }}" required>
        </div>

        {{-- Estado --}}
        <div class="mb-3">
            <label for="estado" class="form-label">Estado</label>
            <select name="estado" class="form-select" required>
                <option value="entrada" {{ old('estado') == 'entrada' ? 'selected' : '' }}>Entrada</option>
                <option value="salida" {{ old('estado') == 'salida' ? 'selected' : '' }}>Salida</option>
            </select>
        </div>

        {{-- Tipo de verificación --}}
        <div class="mb-3">
            <label for="tipo_verificacion" class="form-label">Tipo de Verificación</label>
            <input type="text" name="tipo_verificacion" class="form-control" value="{{ old('tipo_verificacion') }}" placeholder="Ej. Manual, Huella, etc.">
        </div>

        {{-- Terminal ID --}}
        <div class="mb-3">
            <label for="terminal_id" class="form-label">Terminal ID</label>
            <input type="text" name="terminal_id" class="form-control" value="{{ old('terminal_id') }}" placeholder="Ej. ZKTECO01">
        </div>

        {{-- Código de trabajo --}}
        <div class="mb-3">
            <label for="codigo_trabajo" class="form-label">Código de Trabajo</label>
            <input type="text" name="codigo_trabajo" class="form-control" value="{{ old('codigo_trabajo') }}">
        </div>

        <button type="submit" class="btn btn-primary">Guardar Asistencia</button>
        <a href="{{ route('asistencia-docente.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
@endsection
