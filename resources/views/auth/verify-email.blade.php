@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center mt-5">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-body text-center py-5">
                    <div class="mb-4">
                        <svg class="text-warning" width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                            <polyline points="22,6 12,13 2,6"></polyline>
                        </svg>
                    </div>
                    
                    <h3 class="mb-3">Verificación de Correo Electrónico Pendiente</h3>
                    
                    <p class="text-muted mb-4">
                        Hola {{ $user->nombre }}, hemos enviado un enlace de verificación a:
                    </p>
                    
                    <p class="fw-bold text-primary mb-4">
                        {{ $user->email }}
                    </p>
                    
                    <p class="text-muted mb-4">
                        Por favor, revise su bandeja de entrada y haga clic en el enlace para activar su cuenta.
                        Si no encuentra el correo, revise su carpeta de spam.
                    </p>
                    
                    <hr class="my-4">
                    
                    <p class="text-muted mb-3">
                        ¿No recibió el correo?
                    </p>
                    
                    <form method="POST" action="{{ route('verification.resend') }}" class="d-inline">
                        @csrf
                        <input type="hidden" name="email" value="{{ $user->email }}">
                        <button type="submit" class="btn btn-primary">
                            <svg class="me-2" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="23 4 23 10 17 10"></polyline>
                                <polyline points="1 20 1 14 7 14"></polyline>
                                <path d="M3.51 9a9 9 0 0 1 14.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0 0 20.49 15"></path>
                            </svg>
                            Reenviar Correo de Verificación
                        </button>
                    </form>
                    
                    <div class="mt-4">
                        <a href="{{ route('logout') }}" class="btn btn-link text-muted"
                           onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            Cerrar Sesión
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                    </div>
                </div>
            </div>
            
            @if(session('success'))
                <div class="alert alert-success mt-3" role="alert">
                    {{ session('success') }}
                </div>
            @endif
            
            @if($errors->any())
                <div class="alert alert-danger mt-3" role="alert">
                    @foreach($errors->all() as $error)
                        {{ $error }}
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection