@extends('layouts.app')

@section('title', 'Asignar Horario a Docente')

@section('content')
<div class="container">
    <h4 class="mb-3">Asignar Horario</h4>

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>¡Errores en el formulario!</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('horarios-docentes.store') }}" method="POST">
        @csrf

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="docente_id">Docente</label>
                <select name="docente_id" class="form-control" required>
                    <option value="">-- Seleccione --</option>
                    @foreach ($docentes as $docente)
                        <option value="{{ $docente->id }}">{{ $docente->nombre_completo }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6 mb-3">
                <label for="curso_id">Curso</label>
                <select name="curso_id" class="form-control" required>
                    <option value="">-- Seleccione --</option>
                    @foreach ($cursos as $curso)
                        <option value="{{ $curso->id }}">{{ $curso->nombre }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6 mb-3">
                <label for="ciclo_id">Ciclo Académico</label>
                <select name="ciclo_id" class="form-control" required>
                    <option value="">-- Seleccione --</option>
                    @foreach ($ciclos as $ciclo)
                        <option value="{{ $ciclo->id }}">{{ $ciclo->nombre }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6 mb-3">
                <label for="aula_id">Aula</label>
                <select name="aula_id" class="form-control" required>
                    <option value="">-- Seleccione --</option>
                    @foreach ($aulas as $aula)
                        <option value="{{ $aula->id }}">{{ $aula->nombre }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6 mb-3">
                <label for="dia_semana">Día</label>
                <select name="dia_semana" class="form-control" required>
                    <option value="">-- Seleccione --</option>
                    @foreach (['Lunes','Martes','Miércoles','Jueves','Viernes','Sábado','Domingo'] as $dia)
                        <option value="{{ $dia }}">{{ $dia }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3 mb-3">
                <label for="hora_inicio">Hora Inicio</label>
                <input type="time" name="hora_inicio" class="form-control" required>
            </div>

            <div class="col-md-3 mb-3">
                <label for="hora_fin">Hora Fin</label>
                <input type="time" name="hora_fin" class="form-control" required>
            </div>

            <div class="col-md-3 mb-3">
                <label for="turno">Turno</label>
                <select name="turno" class="form-control">
                    <option value="Mañana">Mañana</option>
                    <option value="Tarde">Tarde</option>
                </select>
            </div>

            <div class="col-md-3 mb-3">
                <label for="grupo">Grupo</label>
                <input type="text" name="grupo" class="form-control" placeholder="Ej: A-1, B-1">
            </div>
        </div>

        <button type="submit" class="btn btn-success">Guardar Horario</button>
        <a href="{{ route('horarios-docentes.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
@endsection
