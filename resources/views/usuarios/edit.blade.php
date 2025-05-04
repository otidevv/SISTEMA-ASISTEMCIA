@extends('layouts.app')

@section('title', 'Editar Usuario')

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
                            <li class="breadcrumb-item"><a href="{{ route('usuarios.index') }}">Usuarios</a></li>
                            <li class="breadcrumb-item active">Editar Usuario</li>
                        </ol>
                    </div>
                    <h4 class="page-title">Editar Usuario: {{ $usuario->username }}</h4>
                </div>
            </div>
        </div>
        <!-- end page title -->

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <form action="{{ route('usuarios.update', $usuario->id) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="row">
                                <div class="col-md-6">
                                    <h5 class="mb-3">Información de Cuenta</h5>
                                    <div class="mb-3">
                                        <label for="username" class="form-label">Nombre de Usuario <span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('username') is-invalid @enderror"
                                            id="username" name="username" value="{{ old('username', $usuario->username) }}"
                                            required>
                                        @error('username')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label for="email" class="form-label">Correo Electrónico <span
                                                class="text-danger">*</span></label>
                                        <input type="email" class="form-control @error('email') is-invalid @enderror"
                                            id="email" name="email" value="{{ old('email', $usuario->email) }}"
                                            required>
                                        @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label for="password" class="form-label">Nueva Contraseña</label>
                                        <small class="text-muted d-block mb-1">Dejar en blanco si no desea cambiarla</small>
                                        <input type="password" class="form-control @error('password') is-invalid @enderror"
                                            id="password" name="password">
                                        @error('password')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label for="password_confirmation" class="form-label">Confirmar Nueva
                                            Contraseña</label>
                                        <input type="password" class="form-control" id="password_confirmation"
                                            name="password_confirmation">
                                    </div>

                                    <div class="mb-3">
                                        <label for="roles" class="form-label">Roles <span
                                                class="text-danger">*</span></label>
                                        <select class="form-control select2 @error('roles') is-invalid @enderror"
                                            id="roles" name="roles[]" multiple required>
                                            @foreach ($roles as $role)
                                                <option value="{{ $role->id }}"
                                                    {{ in_array($role->id, old('roles', $usuarioRoles)) ? 'selected' : '' }}>
                                                    {{ $role->nombre }} - {{ $role->descripcion }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('roles')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="form-check mb-3">
                                        <input type="checkbox" class="form-check-input" id="estado" name="estado"
                                            value="1" {{ old('estado', $usuario->estado) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="estado">Usuario Activo</label>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <h5 class="mb-3">Información Personal</h5>
                                    <div class="mb-3">
                                        <label for="nombre" class="form-label">Nombre <span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('nombre') is-invalid @enderror"
                                            id="nombre" name="nombre" value="{{ old('nombre', $usuario->nombre) }}"
                                            required>
                                        @error('nombre')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label for="apellido_paterno" class="form-label">Apellido Paterno <span
                                                class="text-danger">*</span></label>
                                        <input type="text"
                                            class="form-control @error('apellido_paterno') is-invalid @enderror"
                                            id="apellido_paterno" name="apellido_paterno"
                                            value="{{ old('apellido_paterno', $usuario->apellido_paterno) }}" required>
                                        @error('apellido_paterno')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label for="apellido_materno" class="form-label">Apellido Materno</label>
                                        <input type="text"
                                            class="form-control @error('apellido_materno') is-invalid @enderror"
                                            id="apellido_materno" name="apellido_materno"
                                            value="{{ old('apellido_materno', $usuario->apellido_materno) }}">
                                        @error('apellido_materno')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="tipo_documento" class="form-label">Tipo Documento <span
                                                        class="text-danger">*</span></label>
                                                <select class="form-control @error('tipo_documento') is-invalid @enderror"
                                                    id="tipo_documento" name="tipo_documento" required>
                                                    <option value="">Seleccione</option>
                                                    <option value="DNI"
                                                        {{ old('tipo_documento', $usuario->tipo_documento) == 'DNI' ? 'selected' : '' }}>
                                                        DNI</option>
                                                    <option value="Pasaporte"
                                                        {{ old('tipo_documento', $usuario->tipo_documento) == 'Pasaporte' ? 'selected' : '' }}>
                                                        Pasaporte</option>
                                                    <option value="CE"
                                                        {{ old('tipo_documento', $usuario->tipo_documento) == 'CE' ? 'selected' : '' }}>
                                                        Carné de Extranjería</option>
                                                </select>
                                                @error('tipo_documento')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="numero_documento" class="form-label">Número Documento <span
                                                        class="text-danger">*</span></label>
                                                <input type="text"
                                                    class="form-control @error('numero_documento') is-invalid @enderror"
                                                    id="numero_documento" name="numero_documento"
                                                    value="{{ old('numero_documento', $usuario->numero_documento) }}"
                                                    required>
                                                @error('numero_documento')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="telefono" class="form-label">Teléfono</label>
                                                <input type="text"
                                                    class="form-control @error('telefono') is-invalid @enderror"
                                                    id="telefono" name="telefono"
                                                    value="{{ old('telefono', $usuario->telefono) }}">
                                                @error('telefono')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="fecha_nacimiento" class="form-label">Fecha de
                                                    Nacimiento</label>
                                                <input type="date"
                                                    class="form-control @error('fecha_nacimiento') is-invalid @enderror"
                                                    id="fecha_nacimiento" name="fecha_nacimiento"
                                                    value="{{ old('fecha_nacimiento', $usuario->fecha_nacimiento ? $usuario->fecha_nacimiento->format('Y-m-d') : '') }}">
                                                @error('fecha_nacimiento')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="genero" class="form-label">Género</label>
                                        <select class="form-control @error('genero') is-invalid @enderror" id="genero"
                                            name="genero">
                                            <option value="">Seleccione</option>
                                            <option value="Masculino"
                                                {{ old('genero', $usuario->genero) == 'Masculino' ? 'selected' : '' }}>
                                                Masculino</option>
                                            <option value="Femenino"
                                                {{ old('genero', $usuario->genero) == 'Femenino' ? 'selected' : '' }}>
                                                Femenino</option>
                                            <option value="Otro"
                                                {{ old('genero', $usuario->genero) == 'Otro' ? 'selected' : '' }}>Otro
                                            </option>
                                            <option value="Prefiero no decir"
                                                {{ old('genero', $usuario->genero) == 'Prefiero no decir' ? 'selected' : '' }}>
                                                Prefiero no decir</option>
                                        </select>
                                        @error('genero')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label for="direccion" class="form-label">Dirección</label>
                                        <textarea class="form-control @error('direccion') is-invalid @enderror" id="direccion" name="direccion"
                                            rows="2">{{ old('direccion', $usuario->direccion) }}</textarea>
                                        @error('direccion')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="text-end">
                                <a href="{{ route('usuarios.index') }}" class="btn btn-secondary me-2">Cancelar</a>
                                <button type="submit" class="btn btn-primary">Actualizar Usuario</button>
                            </div>
                        </form>
                    </div> <!-- end card-body-->
                </div> <!-- end card-->
            </div> <!-- end col -->
        </div>
        <!-- end row -->
    </div> <!-- container -->
@endsection

@push('js')
    <script>
        $(document).ready(function() {
            // Inicializar Select2
            $('.select2').select2({
                placeholder: "Seleccione roles",
                allowClear: true
            });
        });
    </script>
@endpush
