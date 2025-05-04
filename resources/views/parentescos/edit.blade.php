@extends('layouts.app')

@section('title', 'Editar Parentesco')

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
                            <li class="breadcrumb-item"><a href="{{ route('parentescos.index') }}">Parentescos</a></li>
                            <li class="breadcrumb-item active">Editar Parentesco</li>
                        </ol>
                    </div>
                    <h4 class="page-title">Editar Parentesco</h4>
                </div>
            </div>
        </div>
        <!-- end page title -->

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="header-title">Información del Parentesco</h4>

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form action="{{ route('parentescos.update', $parentesco->id) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="estudiante_id" class="form-label">Estudiante <span
                                                class="text-danger">*</span></label>
                                        <select class="form-select" id="estudiante_id" name="estudiante_id" required>
                                            <option value="">Seleccione un estudiante</option>
                                            @foreach ($estudiantes as $estudiante)
                                                <option value="{{ $estudiante->id }}"
                                                    {{ old('estudiante_id', $parentesco->estudiante_id) == $estudiante->id ? 'selected' : '' }}>
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
                                        <label for="padre_id" class="form-label">Padre/Madre/Tutor <span
                                                class="text-danger">*</span></label>
                                        <select class="form-select" id="padre_id" name="padre_id" required>
                                            <option value="">Seleccione un padre/madre/tutor</option>
                                            @foreach ($padres as $padre)
                                                <option value="{{ $padre->id }}"
                                                    {{ old('padre_id', $parentesco->padre_id) == $padre->id ? 'selected' : '' }}>
                                                    {{ $padre->nombre }} {{ $padre->apellido_paterno }}
                                                    {{ $padre->apellido_materno }}
                                                    ({{ $padre->numero_documento }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="tipo_parentesco" class="form-label">Tipo de Parentesco <span
                                                class="text-danger">*</span></label>
                                        <select class="form-select" id="tipo_parentesco" name="tipo_parentesco" required>
                                            <option value="">Seleccione un tipo</option>
                                            @foreach ($tiposParentesco as $tipo)
                                                <option value="{{ $tipo }}"
                                                    {{ old('tipo_parentesco', $parentesco->tipo_parentesco) == $tipo ? 'selected' : '' }}>
                                                    {{ ucfirst($tipo) }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <div class="form-check form-switch">
                                            <input type="checkbox" class="form-check-input" id="acceso_portal"
                                                name="acceso_portal" value="1"
                                                {{ old('acceso_portal', $parentesco->acceso_portal) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="acceso_portal">Tiene acceso al
                                                portal</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <div class="form-check form-switch">
                                            <input type="checkbox" class="form-check-input" id="recibe_notificaciones"
                                                name="recibe_notificaciones" value="1"
                                                {{ old('recibe_notificaciones', $parentesco->recibe_notificaciones) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="recibe_notificaciones">Recibe
                                                notificaciones</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <div class="form-check form-switch">
                                            <input type="checkbox" class="form-check-input" id="contacto_emergencia"
                                                name="contacto_emergencia" value="1"
                                                {{ old('contacto_emergencia', $parentesco->contacto_emergencia) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="contacto_emergencia">Es contacto de
                                                emergencia</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <div class="form-check form-switch">
                                            <input type="checkbox" class="form-check-input" id="estado" name="estado"
                                                value="1" {{ old('estado', $parentesco->estado) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="estado">Activo</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="text-end">
                                <button type="submit" class="btn btn-success">Actualizar</button>
                                <a href="{{ route('parentescos.index') }}" class="btn btn-secondary">Cancelar</a>
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
            // $('#estudiante_id').select2();
            // $('#padre_id').select2();
        });
    </script>
@endpush
