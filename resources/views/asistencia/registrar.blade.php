@extends('layouts.app')

@section('title', 'Registrar Asistencia')

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
                                        <label for="nro_documento" class="form-label">Estudiante <span
                                                class="text-danger">*</span></label>
                                        <select class="form-select" id="nro_documento" name="nro_documento" required>
                                            <option value="">Seleccione un estudiante</option>
                                            @foreach ($estudiantes as $estudiante)
                                                <option value="{{ $estudiante->numero_documento }}"
                                                    {{ old('nro_documento') == $estudiante->numero_documento ? 'selected' : '' }}>
                                                    {{ $estudiante->nombre }} {{ $estudiante->apellido_paterno }}
                                                    {{ $estudiante->apellido_materno }}
                                                    ({{ $estudiante->numero_documento }})
                                                </option>
                                            @endforeach
                                        </select>
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
        $(document).ready(function() {
            // Si lo necesitas, aquí puedes inicializar select2 u otros componentes JS
            // $('#nro_documento').select2();
        });
    </script>
@endpush
