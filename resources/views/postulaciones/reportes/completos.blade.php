@extends('layouts.app')

@section('title', 'Reportes Completos de Postulaciones')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="card-title">Reportes Completos de Postulaciones</h4>
                        <p class="card-title-desc">Aquí puedes generar reportes completos de postulaciones en formato Excel.</p>
                        
                        <form action="{{ route('postulaciones.reportes.completos.exportar') }}" method="POST">
                            @csrf
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="ciclo_id" class="form-label">Ciclo</label>
                                        <select class="form-control" id="ciclo_id" name="ciclo_id">
                                            <option value="">Todos los ciclos</option>
                                            @foreach($ciclos as $ciclo)
                                                <option value="{{ $ciclo->id }}">{{ $ciclo->nombre }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="carrera_id" class="form-label">Carrera</label>
                                        <select class="form-control" id="carrera_id" name="carrera_id">
                                            <option value="">Todas las carreras</option>
                                            @foreach($carreras as $carrera)
                                                <option value="{{ $carrera->id }}">{{ $carrera->nombre }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="turno_id" class="form-label">Turno</label>
                                        <select class="form-control" id="turno_id" name="turno_id">
                                            <option value="">Todos los turnos</option>
                                            @foreach($turnos as $turno)
                                                <option value="{{ $turno->id }}">{{ $turno->nombre }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">Generar Reporte</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection