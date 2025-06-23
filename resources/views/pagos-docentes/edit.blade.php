@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Editar Pago Docente</h1>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- CORRECCIÓN: Cambiar de $pago a $pago->id --}}
    <form action="{{ route('pagos-docentes.update', $pago->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="form-group mb-3">
            <label for="docente_id">Docente</label>
            <select name="docente_id" id="docente_id" class="form-control" required>
                <option value="">Seleccione un docente</option>
                @foreach($docentes as $docente)
                    <option value="{{ $docente->id }}" {{ $pago->docente_id == $docente->id ? 'selected' : '' }}>
                        {{ $docente->nombre }} {{ $docente->apellido_paterno }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group mb-3">
            <label for="tarifa_por_hora">Tarifa por Hora</label>
            <input type="number" step="0.01" name="tarifa_por_hora" id="tarifa_por_hora" class="form-control" value="{{ old('tarifa_por_hora', $pago->tarifa_por_hora) }}" required>
        </div>

        <div class="form-group mb-3">
            <label for="fecha_inicio">Fecha Inicio</label>
            <input type="date" name="fecha_inicio" id="fecha_inicio" class="form-control" value="{{ old('fecha_inicio', $pago->fecha_inicio) }}" required>
        </div>

        <div class="form-group mb-3">
            <label for="fecha_fin">Fecha Fin (opcional)</label>
            <input type="date" name="fecha_fin" id="fecha_fin" class="form-control" value="{{ old('fecha_fin', $pago->fecha_fin) }}">
        </div>

        <button type="submit" class="btn btn-primary">Actualizar</button>
        <a href="{{ route('pagos-docentes.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
@endsection