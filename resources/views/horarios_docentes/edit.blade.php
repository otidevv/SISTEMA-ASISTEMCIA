@extends('layouts.app')

@section('title', 'Editar Horario Docente')

@section('content')
<div class="container">
    <h4 class="mb-3"><i class="mdi mdi-calendar-edit me-1"></i> Editar Horario</h4>

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

    <form action="{{ route('horarios-docentes.update', $horario->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="docente_id" class="form-label">
                    <i class="mdi mdi-account-outline me-1"></i> Docente
                </label>
                <select name="docente_id" class="form-select" required>
                    <option value="">-- Seleccione --</option>
                    @foreach ($docentes as $docente)
                        <option value="{{ $docente->id }}" {{ $docente->id == $horario->docente_id ? 'selected' : '' }}>
                            {{ $docente->nombre_completo }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6 mb-3">
                <label for="curso_id" class="form-label">
                    <i class="mdi mdi-book-open-variant me-1"></i> Curso
                </label>
                <select name="curso_id" class="form-select" required>
                    <option value="">-- Seleccione --</option>
                    @foreach ($cursos as $curso)
                        <option value="{{ $curso->id }}" {{ $curso->id == $horario->curso_id ? 'selected' : '' }}>
                            {{ $curso->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6 mb-3">
                <label for="ciclo_id" class="form-label">
                    <i class="mdi mdi-calendar-range me-1"></i> Ciclo Académico
                </label>
                <select name="ciclo_id" class="form-select" required>
                    <option value="">-- Seleccione --</option>
                    @foreach ($ciclos as $ciclo)
                        <option value="{{ $ciclo->id }}" {{ $ciclo->id == $horario->ciclo_id ? 'selected' : '' }}>
                            {{ $ciclo->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6 mb-3">
                <label for="aula_id" class="form-label">
                    <i class="mdi mdi-door-open me-1"></i> Aula
                </label>
                <select name="aula_id" class="form-select" required>
                    <option value="">-- Seleccione --</option>
                    @foreach ($aulas as $aula)
                        <option value="{{ $aula->id }}" {{ $aula->id == $horario->aula_id ? 'selected' : '' }}>
                            {{ $aula->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-6 mb-3">
                <label for="dia_semana" class="form-label">
                    <i class="mdi mdi-calendar-today me-1"></i> Día de la Semana
                </label>
                <select name="dia_semana" class="form-select" required>
                    @foreach (['Lunes','Martes','Miércoles','Jueves','Viernes','Sábado','Domingo'] as $dia)
                        <option value="{{ $dia }}" {{ $horario->dia_semana == $dia ? 'selected' : '' }}>
                            {{ $dia }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3 mb-3">
                <label for="hora_inicio" class="form-label">
                    <i class="mdi mdi-clock-start me-1"></i> Hora Inicio
                </label>
                <input type="time" name="hora_inicio" class="form-control"
                    value="{{ old('hora_inicio', substr($horario->hora_inicio, 0, 5)) }}" required>
            </div>

            <div class="col-md-3 mb-3">
                <label for="hora_fin" class="form-label">
                    <i class="mdi mdi-clock-end me-1"></i> Hora Fin
                </label>
                <input type="time" name="hora_fin" class="form-control"
                    value="{{ old('hora_fin', substr($horario->hora_fin, 0, 5)) }}" required>
            </div>

            <div class="col-md-3 mb-3">
                <label for="turno" class="form-label">
                    <i class="mdi mdi-timer-outline me-1"></i> Turno
                </label>
                <select name="turno" class="form-select" required>
                    <option value="MAÑANA" {{ $horario->turno == 'MAÑANA' ? 'selected' : '' }}>Mañana</option>
                    <option value="TARDE" {{ $horario->turno == 'TARDE' ? 'selected' : '' }}>Tarde</option>
                    <option value="NOCHE" {{ $horario->turno == 'NOCHE' ? 'selected' : '' }}>Noche</option>
                </select>
            </div>

            <div class="col-md-3 mb-3">
                <label for="grupo" class="form-label">
                    <i class="mdi mdi-account-group-outline me-1"></i> Grupo
                </label>
                <input type="text" name="grupo" class="form-control"
                    value="{{ old('grupo', $horario->grupo) }}" placeholder="Ej: A-1, B-1">
            </div>
        </div>

        <div class="d-flex justify-content-end mt-4">
            <a href="{{ route('horarios-docentes.index') }}" class="btn btn-secondary me-2">
                <i class="mdi mdi-arrow-left-bold"></i> Cancelar
            </a>
            <button type="submit" class="btn btn-success">
                <i class="mdi mdi-content-save-edit me-1"></i> Actualizar Horario
            </button>
        </div>
    </form>
</div>
@endsection
