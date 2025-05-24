@extends('layouts.app')

@section('title', 'Mi Perfil')

@push('css')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">
@endpush

@push('js')
    <script>
        // Define la URL base para las solicitudes AJAX
        window.default_server = "{{ url('/') }}";
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="{{ asset('js/perfil/index.js') }}"></script>
@endpush

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
                                    class="rounded-circle avatar-xl img-thumbnail" alt="foto de perfil" id="profile-image">
                            @else
                                <div class="avatar-xl rounded-circle bg-primary text-white mx-auto p-4 font-24"
                                    id="profile-initials">
                                    {{ strtoupper(substr(Auth::user()->nombre, 0, 1) . substr(Auth::user()->apellido_paterno, 0, 1)) }}
                                </div>
                            @endif
                            <h4 class="mt-3 mb-0" id="profile-name">{{ Auth::user()->nombre }}
                                {{ Auth::user()->apellido_paterno }}</h4>
                            <p class="text-muted" id="profile-roles">
                                @foreach (Auth::user()->roles as $rol)
                                    <span class="badge bg-primary">{{ ucfirst($rol->nombre) }}</span>
                                @endforeach
                            </p>

                            <div class="text-start mt-3">
                                <p class="text-muted mb-2"><strong>Nombre Completo:</strong> <span
                                        id="profile-full-name">{{ Auth::user()->nombre }}
                                        {{ Auth::user()->apellido_paterno }} {{ Auth::user()->apellido_materno }}</span>
                                </p>
                                <p class="text-muted mb-2"><strong>Documento:</strong> <span
                                        id="profile-document">{{ Auth::user()->tipo_documento }}
                                        {{ Auth::user()->numero_documento }}</span></p>
                                <p class="text-muted mb-2"><strong>Teléfono:</strong> <span
                                        id="profile-phone">{{ Auth::user()->telefono }}</span></p>
                                <p class="text-muted mb-2"><strong>Email:</strong> <span
                                        id="profile-email">{{ Auth::user()->email }}</span></p>
                                <p class="text-muted mb-2"><strong>Fecha de Nacimiento:</strong>
                                    <span
                                        id="profile-birthdate">{{ \Carbon\Carbon::parse(Auth::user()->fecha_nacimiento)->format('d/m/Y') }}</span>
                                </p>
                                <p class="text-muted mb-2"><strong>Género:</strong> <span
                                        id="profile-gender">{{ Auth::user()->genero }}</span></p>
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
                            {{-- <li class="nav-item">
                                <a href="#preferencias-tab" data-bs-toggle="tab" class="nav-link">
                                    <i class="mdi mdi-cog-outline me-1"></i> Preferencias
                                </a>
                            </li> --}}
                        </ul>

                        <div class="tab-content">
                            <!-- Información Personal -->
                            <div class="tab-pane show active" id="informacion-tab">
                                <h5 class="mb-4 mt-3">Información Personal</h5>

                                <form id="formInformacion">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="telefono" class="form-label">Teléfono</label>
                                                <input type="text" class="form-control" id="telefono" name="telefono"
                                                    value="{{ Auth::user()->telefono }}">
                                                <div class="invalid-feedback" id="telefono-error"></div>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="email" class="form-label">Email</label>
                                                <input type="email" class="form-control" id="email" name="email"
                                                    value="{{ Auth::user()->email }}">
                                                <div class="invalid-feedback" id="email-error"></div>
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

                                <form id="formFoto" enctype="multipart/form-data">
                                    <div class="mb-3 text-center">
                                        <div class="mt-3">
                                            <input type="file" class="form-control" id="foto_perfil" name="foto_perfil"
                                                accept="image/*">
                                            <small class="form-text text-muted">Formato recomendado: JPG, PNG. Tamaño
                                                máximo: 2MB</small>
                                            <div class="invalid-feedback" id="foto_perfil-error"></div>
                                        </div>
                                    </div>

                                    <div class="text-center mt-4">
                                        <button type="submit" class="btn btn-success">Subir Foto</button>

                                        @if (Auth::user()->foto_perfil)
                                            <button type="button" id="btnEliminarFoto" class="btn btn-danger ms-2">
                                                Eliminar Foto
                                            </button>
                                        @endif
                                    </div>
                                </form>
                            </div>

                            <!-- Seguridad -->
                            <div class="tab-pane" id="seguridad-tab">
                                <h5 class="mb-4 mt-3">Cambiar Contraseña</h5>

                                <form id="formPassword">
                                    <div class="mb-3">
                                        <label for="current_password" class="form-label">Contraseña Actual</label>
                                        <input type="password" class="form-control" id="current_password"
                                            name="current_password" required>
                                        <div class="invalid-feedback" id="current_password-error"></div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="password" class="form-label">Nueva Contraseña</label>
                                        <input type="password" class="form-control" id="password" name="password"
                                            required>
                                        <small class="form-text text-muted">
                                            La contraseña debe tener al menos 8 caracteres e incluir letras y números.
                                        </small>
                                        <div class="invalid-feedback" id="password-error"></div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="password_confirmation" class="form-label">Confirmar Nueva
                                            Contraseña</label>
                                        <input type="password" class="form-control" id="password_confirmation"
                                            name="password_confirmation" required>
                                        <div class="invalid-feedback" id="password_confirmation-error"></div>
                                    </div>

                                    <div class="text-end mt-3">
                                        <button type="submit" class="btn btn-success">Actualizar Contraseña</button>
                                    </div>
                                </form>
                            </div>

                            <!-- Preferencias -->
                            {{-- <div class="tab-pane" id="preferencias-tab">
                                <h5 class="mb-4 mt-3">Preferencias de Notificación</h5>

                                <form id="formPreferencias">
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
                            </div> --}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
