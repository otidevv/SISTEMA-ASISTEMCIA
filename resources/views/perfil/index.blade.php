@extends('layouts.app')

@section('title', 'Mi Perfil')

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
                            <li class="breadcrumb-item active">Mi Perfil</li>
                        </ol>
                    </div>
                    <h4 class="page-title">Mi Perfil</h4>
                </div>
            </div>
        </div>
        <!-- end page title -->

        <div class="row">
            <div class="col-xl-4 col-lg-5">
                <div class="card">
                    <div class="card-body">
                        <div class="text-center">
                            @if (Auth::user()->foto_perfil)
                                <img src="{{ asset('storage/' . Auth::user()->foto_perfil) }}"
                                    class="rounded-circle avatar-xl img-thumbnail" alt="foto de perfil">
                            @else
                                <div class="avatar-xl rounded-circle bg-primary text-white mx-auto p-4 font-24">
                                    {{ strtoupper(substr(Auth::user()->nombre, 0, 1) . substr(Auth::user()->apellido_paterno, 0, 1)) }}
                                </div>
                            @endif
                            <h4 class="mt-3 mb-0">{{ Auth::user()->nombre }} {{ Auth::user()->apellido_paterno }}</h4>
                            <p class="text-muted">
                                @foreach (Auth::user()->roles as $rol)
                                    <span class="badge bg-primary">{{ ucfirst($rol->nombre) }}</span>
                                @endforeach
                            </p>

                            <div class="text-start mt-3">
                                <p class="text-muted mb-2"><strong>Nombre Completo:</strong> {{ Auth::user()->nombre }}
                                    {{ Auth::user()->apellido_paterno }} {{ Auth::user()->apellido_materno }}</p>
                                <p class="text-muted mb-2"><strong>Documento:</strong> {{ Auth::user()->tipo_documento }}
                                    {{ Auth::user()->numero_documento }}</p>
                                <p class="text-muted mb-2"><strong>Teléfono:</strong> {{ Auth::user()->telefono }}</p>
                                <p class="text-muted mb-2"><strong>Email:</strong> {{ Auth::user()->email }}</p>
                                <p class="text-muted mb-2"><strong>Fecha de Nacimiento:</strong>
                                    {{ \Carbon\Carbon::parse(Auth::user()->fecha_nacimiento)->format('d/m/Y') }}</p>
                                <p class="text-muted mb-2"><strong>Género:</strong> {{ Auth::user()->genero }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-8 col-lg-7">
                <div class="card">
                    <div class="card-body">
                        <ul class="nav nav-tabs nav-bordered">
                            <li class="nav-item">
                                <a href="#informacion-tab" data-bs-toggle="tab" class="nav-link active">
                                    <i class="mdi mdi-account-outline me-1"></i> Información Personal
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="#foto-tab" data-bs-toggle="tab" class="nav-link">
                                    <i class="mdi mdi-camera me-1"></i> Foto de Perfil
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="#seguridad-tab" data-bs-toggle="tab" class="nav-link">
                                    <i class="mdi mdi-lock-outline me-1"></i> Seguridad
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="#preferencias-tab" data-bs-toggle="tab" class="nav-link">
                                    <i class="mdi mdi-cog-outline me-1"></i> Preferencias
                                </a>
                            </li>
                        </ul>

                        <div class="tab-content">
                            <!-- Información Personal -->
                            <div class="tab-pane show active" id="informacion-tab">
                                <h5 class="mb-4 mt-3">Información Personal</h5>

                                @if (session('info_success'))
                                    <div class="alert alert-success">
                                        {{ session('info_success') }}
                                    </div>
                                @endif

                                <form action="{{ route('perfil.update') }}" method="POST">
                                    @csrf
                                    @method('PUT')

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="telefono" class="form-label">Teléfono</label>
                                                <input type="text"
                                                    class="form-control @error('telefono') is-invalid @enderror"
                                                    id="telefono" name="telefono"
                                                    value="{{ old('telefono', Auth::user()->telefono) }}">
                                                @error('telefono')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="email" class="form-label">Email</label>
                                                <input type="email"
                                                    class="form-control @error('email') is-invalid @enderror" id="email"
                                                    name="email" value="{{ old('email', Auth::user()->email) }}">
                                                @error('email')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="text-end mt-3">
                                        <button type="submit" class="btn btn-success">Guardar Cambios</button>
                                    </div>
                                </form>
                            </div>

                            <!-- Foto de Perfil -->
                            <div class="tab-pane" id="foto-tab">
                                <h5 class="mb-4 mt-3">Actualizar Foto de Perfil</h5>

                                @if (session('foto_success'))
                                    <div class="alert alert-success">
                                        {{ session('foto_success') }}
                                    </div>
                                @endif

                                <form action="{{ route('perfil.update.foto') }}" method="POST"
                                    enctype="multipart/form-data">
                                    @csrf
                                    @method('PUT')

                                    <div class="mb-3 text-center">
                                        <div class="mt-3">
                                            <input type="file"
                                                class="form-control @error('foto_perfil') is-invalid @enderror"
                                                id="foto_perfil" name="foto_perfil" accept="image/*">
                                            <small class="form-text text-muted">Formato recomendado: JPG, PNG. Tamaño
                                                máximo: 2MB</small>
                                            @error('foto_perfil')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="text-center mt-4">
                                        <button type="submit" class="btn btn-success">Subir Foto</button>

                                        @if (Auth::user()->foto_perfil)
                                            <a href="{{ route('perfil.eliminar.foto') }}" class="btn btn-danger ms-2"
                                                onclick="return confirm('¿Está seguro de eliminar su foto de perfil?')">
                                                Eliminar Foto
                                            </a>
                                        @endif
                                    </div>
                                </form>
                            </div>

                            <!-- Seguridad -->
                            <div class="tab-pane" id="seguridad-tab">
                                <h5 class="mb-4 mt-3">Cambiar Contraseña</h5>

                                @if (session('password_success'))
                                    <div class="alert alert-success">
                                        {{ session('password_success') }}
                                    </div>
                                @endif

                                @if (session('password_error'))
                                    <div class="alert alert-danger">
                                        {{ session('password_error') }}
                                    </div>
                                @endif

                                <form action="{{ route('perfil.password') }}" method="POST">
                                    @csrf
                                    @method('PUT')

                                    <div class="mb-3">
                                        <label for="current_password" class="form-label">Contraseña Actual</label>
                                        <input type="password"
                                            class="form-control @error('current_password') is-invalid @enderror"
                                            id="current_password" name="current_password" required>
                                        @error('current_password')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label for="password" class="form-label">Nueva Contraseña</label>
                                        <input type="password"
                                            class="form-control @error('password') is-invalid @enderror" id="password"
                                            name="password" required>
                                        <small class="form-text text-muted">
                                            La contraseña debe tener al menos 8 caracteres e incluir letras y números.
                                        </small>
                                        @error('password')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label for="password_confirmation" class="form-label">Confirmar Nueva
                                            Contraseña</label>
                                        <input type="password" class="form-control" id="password_confirmation"
                                            name="password_confirmation" required>
                                    </div>

                                    <div class="text-end mt-3">
                                        <button type="submit" class="btn btn-success">Actualizar Contraseña</button>
                                    </div>
                                </form>
                            </div>

                            <!-- Preferencias -->
                            <div class="tab-pane" id="preferencias-tab">
                                <h5 class="mb-4 mt-3">Preferencias de Notificación</h5>

                                @if (session('pref_success'))
                                    <div class="alert alert-success">
                                        {{ session('pref_success') }}
                                    </div>
                                @endif

                                <form action="{{ route('perfil.preferencias') }}" method="POST">
                                    @csrf
                                    @method('PUT')

                                    <div class="mb-3">
                                        <div class="form-check form-switch">
                                            <input type="checkbox" class="form-check-input" id="notif_email"
                                                name="notif_email" value="1"
                                                {{ Auth::user()->notif_email ? 'checked' : '' }}>
                                            <label class="form-check-label" for="notif_email">
                                                Recibir notificaciones por email
                                            </label>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <div class="form-check form-switch">
                                            <input type="checkbox" class="form-check-input" id="notif_sistema"
                                                name="notif_sistema" value="1"
                                                {{ Auth::user()->notif_sistema ? 'checked' : '' }}>
                                            <label class="form-check-label" for="notif_sistema">
                                                Recibir notificaciones en el sistema
                                            </label>
                                        </div>
                                    </div>

                                    <div class="text-end mt-3">
                                        <button type="submit" class="btn btn-success">Guardar Preferencias</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script>
        $(document).ready(function() {
            // Previsualizar imagen antes de subir
            $('#foto_perfil').change(function() {
                const file = this.files[0];
                if (file) {
                    let reader = new FileReader();
                    reader.onload = function(event) {
                        $('.avatar-xl').attr('src', event.target.result);
                    }
                    reader.readAsDataURL(file);
                }
            });
        });
    </script>
@endpush
