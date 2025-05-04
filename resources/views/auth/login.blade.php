@extends('layouts.auth')

@section('title', 'Iniciar Sesión')

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
                                            <img src="{{ asset('assets/images/logo-dark.png') }}" alt=""
                                                height="24" />
                                        </a>
                                    </div>

                                    <h6 class="h5 mb-0 mt-3">¡Bienvenido de nuevo!</h6>
                                    <p class="text-muted mt-1 mb-4">
                                        Introduzca su dirección de correo electrónico y contraseña para acceder al panel
                                        de administración.
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

                                    <form method="POST" action="{{ route('login') }}" class="authentication-form">
                                        @csrf
                                        <div class="mb-3">
                                            <label class="form-label">Correo Electrónico</label>
                                            <div class="input-group">
                                                <span class="input-group-text">
                                                    <i class="icon-dual" data-feather="mail"></i>
                                                </span>
                                                <input type="email"
                                                    class="form-control @error('email') is-invalid @enderror" name="email"
                                                    id="email" value="{{ old('email') }}"
                                                    placeholder="correo@ejemplo.com" required autofocus>
                                            </div>
                                            @error('email')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label">Contraseña</label>
                                            <a href="{{ route('password.request') }}"
                                                class="float-end text-muted text-unline-dashed ms-1">¿Olvidaste tu
                                                contraseña?</a>
                                            <div class="input-group">
                                                <span class="input-group-text">
                                                    <i class="icon-dual" data-feather="lock"></i>
                                                </span>
                                                <input type="password"
                                                    class="form-control @error('password') is-invalid @enderror"
                                                    name="password" id="password" placeholder="Ingrese su contraseña"
                                                    required>
                                            </div>
                                            @error('password')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" name="remember"
                                                    id="remember" {{ old('remember') ? 'checked' : '' }}>
                                                <label class="form-check-label" for="remember">Recordar</label>
                                            </div>
                                        </div>

                                        <div class="mb-3 text-center d-grid">
                                            <button class="btn btn-primary" type="submit">Iniciar Sesión</button>
                                        </div>
                                    </form>
                                </div>
                                <div class="col-lg-6 d-none d-md-inline-block">
                                    <div class="auth-page-sidebar">
                                        <div class="overlay"></div>
                                        <div class="auth-user-testimonial">
                                            <p class="fs-24 fw-bold text-white mb-1">Sistema de Asistencia</p>
                                            <p class="lead">"Control eficiente de asistencia para profesores y
                                                estudiantes"</p>
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
