@extends('layouts.app')

@section('title', 'Dashboard Estudiante')

@push('css')
    <style>
        .inscription-card {
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s ease;
        }

        .inscription-card:hover {
            transform: translateY(-5px);
        }

        .form-select:focus,
        .form-control:focus {
            border-color: #4e73df;
            box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
        }

        .btn-inscribir {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            font-weight: 600;
            padding: 12px 30px;
            border-radius: 25px;
            transition: all 0.3s ease;
        }

        .btn-inscribir:hover {
            transform: scale(1.05);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .cycle-info {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 20px;
        }

        .vacancy-badge {
            background: #28a745;
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.85rem;
        }

        .no-vacancy-badge {
            background: #dc3545;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid">
        <!-- Título y breadcrumb -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    <h4 class="page-title">Dashboard Estudiante</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Estudiante</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <!-- Información del estudiante -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card cycle-info">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h3 class="mb-1">¡Bienvenido, {{ Auth::user()->nombres }}!</h3>
                                <p class="mb-0">
                                    @if (Auth::user()->hasRole('postulante'))
                                        Eres un postulante. Inscríbete en el ciclo actual para comenzar tu preparación.
                                    @else
                                        Continúa tu preparación inscribiéndote en el nuevo ciclo disponible.
                                    @endif
                                </p>
                            </div>
                            <div class="col-md-4 text-end">
                                <div id="ciclo-info">
                                    <div class="spinner-border text-light" role="status">
                                        <span class="visually-hidden">Cargando...</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Formulario de inscripción o información de inscripción actual -->
        <div class="row">
            <div class="col-12">
                <div id="contenedor-inscripcion">
                    <!-- Se llenará dinámicamente -->
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de confirmación -->
    <div class="modal fade" id="confirmModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmar Inscripción</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>¿Estás seguro de que deseas inscribirte con los siguientes datos?</p>
                    <div id="resumen-inscripcion"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btnConfirmarInscripcion">Confirmar
                        Inscripción</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js')
    <script src="{{ asset('js/dashboard-estudiante/index.js') }}"></script>
@endpush
