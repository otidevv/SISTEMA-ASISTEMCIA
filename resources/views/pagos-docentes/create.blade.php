@extends('layouts.app')

@section('title', 'Nuevo Registro de Pago')

@push('css')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    <style>
        .form-label { font-weight: 600; color: #4b4b4b; }
        .form-control:focus, .form-select:focus {
            border-color: #5369f8;
            box-shadow: 0 0 0 0.15rem rgba(83, 105, 248, 0.15);
        }
        .info-card {
            background-color: #f0f3ff;
            border-left: 4px solid #5369f8;
            border-radius: 0.5rem;
        }
    </style>
@endpush

@push('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.select2').select2({
                theme: 'bootstrap-5'
            });

            // Lógica para autocompletar tarifa si ya existe un pago previo para ese docente
            $('#docente_id').on('change', function() {
                const docenteId = $(this).val();
                if (docenteId) {
                    fetch(`{{ url('api/v1/pagos-docentes/ultima-tarifa') }}/${docenteId}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.tarifa) {
                                $('#tarifa_por_hora').val(data.tarifa).addClass('is-valid');
                                toastr.success('Se cargó automáticamente la última tarifa registrada para este docente.', '¡Optimizado!');
                                setTimeout(() => $('#tarifa_por_hora').removeClass('is-valid'), 3000);
                            }
                        });
                }
            });
        });
    </script>
@endpush

@section('content')
    <!-- start page title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">Configurar Pago a Docente</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('pagos-docentes.index') }}">Pagos</a></li>
                        <li class="breadcrumb-item active">Nuevo</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <!-- end page title -->

    <div class="row justify-content-center">
        <div class="col-md-9">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-bottom py-3">
                    <h4 class="header-title mb-0"><i class="mdi mdi-account-cash-outline me-2 text-primary"></i>Configuración de Pago</h4>
                </div>
                <div class="card-body p-4">
                    <div class="info-card p-3 mb-4">
                        <div class="d-flex align-items-center">
                            <i class="mdi mdi-auto-fix font-size-24 me-3 text-primary"></i>
                            <div>
                                <h6 class="mb-1 fw-bold">Asistente de Registro</h6>
                                <p class="mb-0 text-muted small">Al seleccionar un docente, el sistema buscará su última tarifa histórica para ahorrarle tiempo.</p>
                            </div>
                        </div>
                    </div>

                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show border-0" role="alert">
                            <h6 class="alert-heading fw-bold">Hay errores en el formulario:</h6>
                            <ul class="mb-0 mt-1 small">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form action="{{ route('pagos-docentes.store') }}" method="POST" class="needs-validation">
                        @csrf

                        <div class="row">
                            <div class="col-md-7 mb-4">
                                <label for="docente_id" class="form-label">Docente Responsable</label>
                                <select name="docente_id" id="docente_id" class="form-select select2" required>
                                    <option value="">Buscar nombre o apellido...</option>
                                    @foreach($docentes as $docente)
                                        <option value="{{ $docente->id }}" {{ old('docente_id') == $docente->id ? 'selected' : '' }}>
                                            {{ $docente->nombre }} {{ $docente->apellido_paterno }} {{ $docente->apellido_materno }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback">Por favor seleccione un docente.</div>
                            </div>

                            <div class="col-md-5 mb-4">
                                <label for="ciclo_id" class="form-label">Ciclo Académico</label>
                                <select name="ciclo_id" id="ciclo_id" class="form-select border-primary-subtle" required>
                                    @foreach($ciclos as $ciclo)
                                        <option value="{{ $ciclo->id }}" {{ (old('ciclo_id') == $ciclo->id || (!old('ciclo_id') && isset($cicloActivo) && $cicloActivo->id == $ciclo->id)) ? 'selected' : '' }}>
                                            {{ $ciclo->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="form-text mt-1"><i class="mdi mdi-information-outline me-1"></i>Vincular registro a este periodo.</div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label for="tarifa_por_hora" class="form-label">Tarifa de Pago (S/)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light fw-bold text-primary">S/</span>
                                    <input type="number" step="0.01" name="tarifa_por_hora" id="tarifa_por_hora" 
                                           class="form-control form-control-lg fw-bold" placeholder="0.00" 
                                           value="{{ old('tarifa_por_hora') }}" required>
                                </div>
                                <div class="form-text">Monto bruto por hora académica.</div>
                            </div>
                        </div>

                        <div class="mt-4 pt-3 border-top text-end">
                            <a href="{{ route('pagos-docentes.index') }}" class="btn btn-light px-4 me-2">
                                <i class="mdi mdi-arrow-left me-1"></i> Regresar
                            </a>
                            <button type="submit" class="btn btn-primary px-4 shadow-sm">
                                <i class="mdi mdi-content-save-check me-1"></i> Confirmar Registro
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('css')
@endpush

@push('js')
@endpush
