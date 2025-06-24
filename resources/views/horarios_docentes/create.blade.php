@extends('layouts.app')

@section('title', 'Asignar Horario a Docente')

@section('content')
<div class="container-fluid">
    
    <!-- start page title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="javascript: void(0);">Shreyu</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('horarios-docentes.index') }}">Horarios</a></li>
                        <li class="breadcrumb-item active">Asignar Horario</li>
                    </ol>
                </div>
                <h4 class="page-title">Asignar Horario a Docente</h4>
            </div>
        </div>
    </div>
    <!-- end page title -->

    <!-- Alerts -->
    @if ($errors->any())
        <div class="row">
            <div class="col-12">
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="mdi mdi-block-helper me-2"></i>
                    <strong>¡Errores en el formulario!</strong>
                    <ul class="mb-0 mt-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif

    @if(session('success'))
        <div class="row">
            <div class="col-12">
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="mdi mdi-check-all me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="row">
            <div class="col-12">
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="mdi mdi-block-helper me-2"></i>
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="header-title">
                        <i class="mdi mdi-calendar-plus me-2"></i>
                        Información del Horario
                    </h4>
                    <p class="text-muted mb-0">Complete los campos requeridos para asignar un horario al docente</p>
                </div>
                <div class="card-body">
                    <form action="{{ route('horarios-docentes.store') }}" method="POST" id="horarioForm">
                        @csrf

                        <div class="row">
                            <!-- Docente -->
                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label for="docente_id" class="form-label">
                                        Docente <span class="text-danger">*</span>
                                    </label>
                                    <select name="docente_id" id="docente_id" class="form-select" data-toggle="select2" required>
                                        <option value="">Seleccione un docente</option>
                                        @foreach ($docentes as $docente)
                                            <option value="{{ $docente->id }}" {{ old('docente_id') == $docente->id ? 'selected' : '' }}>
                                                {{ $docente->nombre_completo }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback">
                                        Por favor seleccione un docente.
                                    </div>
                                </div>
                            </div>

                            <!-- Curso -->
                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label for="curso_id" class="form-label">
                                        Curso <span class="text-danger">*</span>
                                    </label>
                                    <select name="curso_id" id="curso_id" class="form-select" data-toggle="select2" required>
                                        <option value="">Seleccione un curso</option>
                                        @foreach ($cursos as $curso)
                                            <option value="{{ $curso->id }}" {{ old('curso_id') == $curso->id ? 'selected' : '' }}>
                                                {{ $curso->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback">
                                        Por favor seleccione un curso.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Ciclo Académico -->
                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label for="ciclo_id" class="form-label">
                                        Ciclo Académico <span class="text-danger">*</span>
                                    </label>
                                    <select name="ciclo_id" id="ciclo_id" class="form-select" data-toggle="select2" required>
                                        <option value="">Seleccione un ciclo</option>
                                        @foreach ($ciclos as $ciclo)
                                            <option value="{{ $ciclo->id }}" {{ old('ciclo_id') == $ciclo->id ? 'selected' : '' }}>
                                                {{ $ciclo->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback">
                                        Por favor seleccione un ciclo académico.
                                    </div>
                                </div>
                            </div>

                            <!-- Aula -->
                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label for="aula_id" class="form-label">
                                        Aula <span class="text-danger">*</span>
                                    </label>
                                    <select name="aula_id" id="aula_id" class="form-select" data-toggle="select2" required>
                                        <option value="">Seleccione un aula</option>
                                        @foreach ($aulas as $aula)
                                            <option value="{{ $aula->id }}" {{ old('aula_id') == $aula->id ? 'selected' : '' }}>
                                                {{ $aula->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback">
                                        Por favor seleccione un aula.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Día -->
                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label for="dia_semana" class="form-label">
                                        Día de la Semana <span class="text-danger">*</span>
                                    </label>
                                    <select name="dia_semana" id="dia_semana" class="form-select" required>
                                        <option value="">Seleccione un día</option>
                                        @foreach (['Lunes','Martes','Miércoles','Jueves','Viernes','Sábado','Domingo'] as $dia)
                                            <option value="{{ $dia }}" {{ old('dia_semana') == $dia ? 'selected' : '' }}>
                                                {{ $dia }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="invalid-feedback">
                                        Por favor seleccione un día.
                                    </div>
                                </div>
                            </div>

                            <!-- Turno -->
                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label for="turno" class="form-label">Turno</label>
                                    <select name="turno" id="turno" class="form-select" required>
                                        <option value="">Seleccione un turno</option>
                                        <option value="MAÑANA" {{ old('turno') == 'MAÑANA' ? 'selected' : '' }}>
                                            Mañana
                                        </option>
                                        <option value="TARDE" {{ old('turno') == 'TARDE' ? 'selected' : '' }}>
                                            Tarde
                                        </option>
                                        <option value="NOCHE" {{ old('turno') == 'NOCHE' ? 'selected' : '' }}>
                                            Noche
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Hora Inicio -->
                            <div class="col-lg-3">
                                <div class="mb-3">
                                    <label for="hora_inicio" class="form-label">
                                        Hora de Inicio <span class="text-danger">*</span>
                                    </label>
                                    <input type="time" name="hora_inicio" id="hora_inicio" 
                                           class="form-control" value="{{ old('hora_inicio') }}" required>
                                    <div class="invalid-feedback">
                                        Por favor ingrese la hora de inicio.
                                    </div>
                                </div>
                            </div>

                            <!-- Hora Fin -->
                            <div class="col-lg-3">
                                <div class="mb-3">
                                    <label for="hora_fin" class="form-label">
                                        Hora de Fin <span class="text-danger">*</span>
                                    </label>
                                    <input type="time" name="hora_fin" id="hora_fin" 
                                           class="form-control" value="{{ old('hora_fin') }}" required>
                                    <div class="invalid-feedback">
                                        Por favor ingrese la hora de fin.
                                    </div>
                                </div>
                            </div>

                            <!-- Grupo -->
                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label for="grupo" class="form-label">Grupo</label>
                                    <input type="text" name="grupo" id="grupo" class="form-control" 
                                           placeholder="Ej: A-1, B-1" value="{{ old('grupo') }}">
                                    <small class="form-text text-muted">
                                        Opcional: Especifique el grupo o sección
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="text-end">
                            <button type="button" class="btn btn-light me-2" onclick="window.history.back()">
                                <i class="mdi mdi-arrow-left me-1"></i>
                                Cancelar
                            </button>
                            <button type="submit" class="btn btn-primary" id="submitBtn">
                                <i class="mdi mdi-content-save me-1"></i>
                                Guardar Horario
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Información adicional -->
    <div class="row">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="header-title">
                        <i class="mdi mdi-information-outline me-2"></i>
                        Información
                    </h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-info" role="alert">
                        <i class="mdi mdi-lightbulb-outline me-2"></i>
                        <strong>Recordatorio:</strong> Verifique que no existan conflictos de horario antes de guardar.
                    </div>
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <i class="mdi mdi-check-circle text-success me-2"></i>
                            Todos los campos marcados con <span class="text-danger">*</span> son obligatorios
                        </li>
                        <li class="mb-2">
                            <i class="mdi mdi-check-circle text-success me-2"></i>
                            La hora de fin debe ser posterior a la hora de inicio
                        </li>
                        <li class="mb-2">
                            <i class="mdi mdi-check-circle text-success me-2"></i>
                            El turno se calculará automáticamente según la hora de inicio
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h4 class="header-title">
                        <i class="mdi mdi-clock-outline me-2"></i>
                        Horarios Sugeridos
                    </h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-centered mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Turno</th>
                                    <th>Horario</th>
                                    <th>Duración</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <span class="badge bg-success">Mañana</span>
                                    </td>
                                    <td>08:00 - 12:00</td>
                                    <td>4 horas</td>
                                </tr>
                                <tr>
                                    <td>
                                        <span class="badge bg-primary">Tarde</span>
                                    </td>
                                    <td>14:00 - 18:00</td>
                                    <td>4 horas</td>
                                </tr>
                                <tr>
                                    <td>
                                        <span class="badge bg-warning">Noche</span>
                                    </td>
                                    <td>19:00 - 22:00</td>
                                    <td>3 horas</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('script')
<script>
$(document).ready(function() {
    // Inicializar Select2
    $('[data-toggle="select2"]').select2({
        placeholder: "Seleccione una opción",
        allowClear: true
    });

    // Auto-calcular turno basado en hora de inicio
    $('#hora_inicio').on('change', function() {
        const hora = parseInt(this.value.split(':')[0]);
        const turnoSelect = $('#turno');
        
        if (hora < 12) {
            turnoSelect.val('Mañana');
        } else if (hora < 19) {
            turnoSelect.val('Tarde');
        } else {
            turnoSelect.val('Noche');
        }
    });

    // Validación del formulario
    $('#horarioForm').on('submit', function(e) {
        const horaInicio = $('#hora_inicio').val();
        const horaFin = $('#hora_fin').val();
        const submitBtn = $('#submitBtn');
        
        // Validar que la hora de fin sea posterior a la de inicio
        if (horaInicio && horaFin && horaInicio >= horaFin) {
            e.preventDefault();
            
            // Mostrar toast de error
            $.NotificationApp.send(
                "Error de Validación",
                "La hora de inicio debe ser menor que la hora de finalización",
                "top-right",
                "rgba(0,0,0,0.2)",
                "error"
            );
            
            return false;
        }

        // Cambiar estado del botón
        submitBtn.prop('disabled', true);
        submitBtn.html('<i class="mdi mdi-loading mdi-spin me-1"></i> Guardando...');
        
        // Restaurar botón después de 5 segundos (por si hay error)
        setTimeout(function() {
            submitBtn.prop('disabled', false);
            submitBtn.html('<i class="mdi mdi-content-save me-1"></i> Guardar Horario');
        }, 5000);
    });

    // Mostrar mensajes de éxito/error con NotificationApp
    @if(session('success'))
        $.NotificationApp.send(
            "¡Éxito!",
            "{{ session('success') }}",
            "top-right",
            "rgba(0,0,0,0.2)",
            "success"
        );
    @endif

    @if(session('error'))
        $.NotificationApp.send(
            "Error",
            "{{ session('error') }}",
            "top-right",
            "rgba(0,0,0,0.2)",
            "error"
        );
    @endif

    // Validación en tiempo real
    $('.form-control, .form-select').on('blur', function() {
        if ($(this).prop('required') && !$(this).val()) {
            $(this).addClass('is-invalid');
        } else {
            $(this).removeClass('is-invalid');
        }
    });

    // Limpiar validación al escribir
    $('.form-control, .form-select').on('input change', function() {
        $(this).removeClass('is-invalid');
    });
});
</script>
@endsection