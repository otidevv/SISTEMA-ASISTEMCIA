@extends('layouts.auth')

@section('title', 'Restablecer Contraseña')

@section('content')
    <div class="account-pages my-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-xl-10">
                    <div class="card">
                        <div class="card-body p-0">
                            <div class="row g-0">
                                <div class="col-lg-6 p-4">
                                    <div class="mx-auto">
                                        <a href="{{ route('home') }}">
                                            <img src="{{ asset('assets/images/logocepre1.svg') }}" alt=""
                                                height="24" />
                                        </a>
                                    </div>

                                    <h6 class="h5 mb-0 mt-3">Restablecer contraseña</h6>
                                    <p class="text-muted mt-1 mb-4">
                                        Ingrese su nueva contraseña a continuación.
                                    </p>

                                    @if ($errors->any())
                                        <div class="alert alert-danger">
                                            <ul class="mb-0">
                                                @foreach ($errors->all() as $error)
                                                    <li>{{ $error }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif

                                    <form method="POST" action="{{ route('password.update') }}"
                                        class="authentication-form">
                                        @csrf

                                        <!-- Token oculto -->
                                        <input type="hidden" name="token" value="{{ $token }}">

                                        <div class="mb-3">
                                            <label class="form-label">Correo Electrónico</label>
                                            <div class="input-group">
                                                <span class="input-group-text">
                                                    <i class="icon-dual" data-feather="mail"></i>
                                                </span>
                                                <input type="email"
                                                    class="form-control @error('email') is-invalid @enderror" name="email"
                                                    id="email" value="{{ $email ?? old('email') }}"
                                                    placeholder="correo@ejemplo.com" required readonly>
                                            </div>
                                            @error('email')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Nueva Contraseña</label>
                                            <div class="input-group">
                                                <span class="input-group-text">
                                                    <i class="icon-dual" data-feather="lock"></i>
                                                </span>
                                                <input type="password"
                                                    class="form-control @error('password') is-invalid @enderror"
                                                    name="password" id="password" required>
                                            </div>
                                            @error('password')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Confirmar Contraseña</label>
                                            <div class="input-group">
                                                <span class="input-group-text">
                                                    <i class="icon-dual" data-feather="lock"></i>
                                                </span>
                                                <input type="password" class="form-control" name="password_confirmation"
                                                    id="password_confirmation" required>
                                            </div>
                                        </div>

                                        <div class="mb-3 text-center d-grid">
                                            <button class="btn btn-primary" type="submit">Restablecer Contraseña</button>
                                        </div>
                                    </form>

                                    <div class="text-center mt-4">
                                        <p class="text-muted">Volver a <a href="{{ route('login') }}"
                                                class="text-primary fw-bold ms-1">Iniciar sesión</a></p>
                                    </div>
                                </div>
                                <div class="col-lg-6 d-none d-md-inline-block">
                                    <div class="auth-page-sidebar">
                                        <div class="overlay"></div>
                                        <div class="auth-user-testimonial">
                                            <p class="fs-24 fw-bold text-white mb-1">Sistema de Asistencia</p>
                                            <p class="lead">"Recupere su acceso de forma segura y rápida"</p>
                                            <p>- Admin</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div> <!-- end card-body -->
                    </div>
                    <!-- end card -->
                </div> <!-- end col -->
            </div>
            <!-- end row -->
        </div>
        <!-- end container -->
    </div>
@endsection
