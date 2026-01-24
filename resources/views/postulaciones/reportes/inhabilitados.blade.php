@extends('layouts.app')

@section('title', 'Reporte de Estudiantes Inhabilitados')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card modern-card">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <h4 class="mb-1 text-primary"><i class="mdi mdi-account-off-outline"></i> Reporte de Estudiantes Inhabilitados</h4>
                                <p class="text-muted mb-0">Genera reportes detallados de estudiantes que superan el límite de inasistencias.</p>
                            </div>
                        </div>

                        <form action="{{ route('postulaciones.reportes.inhabilitados.pdf') }}" method="POST">
                            @csrf
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label for="ciclo_id" class="form-label font-weight-bold text-dark">Ciclo Académico</label>
                                        <select class="form-control" id="ciclo_id" name="ciclo_id" required>
                                            @foreach($ciclos as $ciclo)
                                                <option value="{{ $ciclo->id }}" {{ $ciclo->es_activo ? 'selected' : '' }}>
                                                    {{ $ciclo->nombre }} {{ $ciclo->es_activo ? '(Actual)' : '' }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="periodo_examen" class="form-label font-weight-bold text-dark">Calcular hasta:</label>
                                        <select class="form-control" id="periodo_examen" name="periodo_examen">
                                            <option value="hoy">Fecha Actual (Proyectado)</option>
                                            <option value="1">Hasta Primer Examen</option>
                                            <option value="2">Hasta Segundo Examen</option>
                                            <option value="3">Hasta Tercer Examen</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-5 d-flex align-items-end mb-3">
                                    <button type="submit" class="btn btn-danger px-4 shadow-sm font-weight-bold">
                                        <i class="mdi mdi-file-pdf-box me-1"></i> GENERAR REPORTE PDF
                                    </button>
                                </div>
                            </div>
                        </form>
                        
                        <div class="alert alert-info mt-3 border-0 shadow-sm">
                            <h5 class="alert-heading"><i class="mdi mdi-information-outline"></i> Información sobre el cálculo</h5>
                            <p class="mb-2">
                                El sistema maneja los periodos de examen de forma independiente:
                            </p>
                            <ul class="mb-0">
                                <li><strong>Hoy / 1er Examen</strong>: Cálculo acumulado desde el inicio del alumno hasta la fecha indicada.</li>
                                <li><strong>2do / 3er Examen</strong>: Cálculo exclusivo de inasistencias dentro del periodo (entre exámenes).</li>
                                <li>La inhabilitación se basa en el porcentaje configurado para el ciclo ({{ $cicloActivo->porcentaje_inhabilitacion ?? '30' }}%).</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
