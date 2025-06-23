@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Nuevo Pago Docente</h1>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('pagos-docentes.store') }}" method="POST">
        @csrf

        <div class="form-group">
            <label for="docente_id">Docente</label>
            <select name="docente_id" id="docente_id" class="form-control" required>
                <option value="">Seleccione un docente</option>
                @foreach($docentes as $docente)
                    <option value="{{ $docente->id }}" {{ old('docente_id') == $docente->id ? 'selected' : '' }}>
                        {{ $docente->nombre }} {{ $docente->apellido_paterno }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="ciclo_id">Ciclo Académico</label>
            <select name="ciclo_id" id="ciclo_id" class="form-control" required>
                <option value="">Seleccione un ciclo</option>
                @foreach($ciclos as $ciclo)
                    <option value="{{ $ciclo->id }}" {{ old('ciclo_id') == $ciclo->id ? 'selected' : '' }}>
                        {{ $ciclo->nombre }} ({{ $ciclo->fecha_inicio }} - {{ $ciclo->fecha_fin }})
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label for="tarifa_por_hora">Tarifa por Hora (S/.)</label>
            <input type="number" step="0.01" name="tarifa_por_hora" id="tarifa_por_hora" class="form-control" value="{{ old('tarifa_por_hora') }}" required>
        </div>

        <button type="submit" class="btn btn-primary">Guardar</button>
        <a href="{{ route('pagos-docentes.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
</div>

<script>
    function fetchTarifa() {
        const cicloId = document.getElementById('ciclo_id').value;
        const docenteId = document.getElementById('docente_id').value;

        if (cicloId && docenteId) {
            fetch(`/pagos-docentes/tarifa/${docenteId}/${cicloId}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('tarifa_por_hora').value = data.tarifa_por_hora || '';
                });
        } else {
            document.getElementById('tarifa_por_hora').value = '';
        }
    }

    document.getElementById('ciclo_id').addEventListener('change', fetchTarifa);
    document.getElementById('docente_id').addEventListener('change', fetchTarifa);
</script>
@endsection
