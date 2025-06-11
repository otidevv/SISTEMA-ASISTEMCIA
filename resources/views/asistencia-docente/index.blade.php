@extends('layouts.app')

@section('title', 'Asistencia de Docentes')

@section('content')
<div class="container">
    <h4 class="mb-3">Asistencia de Docentes</h4>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead class="thead-dark">
                <tr>
                    <th>Docente</th>
                    <th>Curso</th>
                    <th>Ciclo</th>
                    <th>Día</th>
                    <th>Fecha</th>
                    <th>Hora</th>
                    <th>Tipo Verificación</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                @forelse($asistencias as $asistencia)
                    <tr>
                        <td>{{ $asistencia->docente->nombre_completo ?? '---' }}</td>
                        <td>{{ $asistencia->horario->curso->nombre ?? '---' }}</td>
                        <td>{{ $asistencia->horario->ciclo->nombre ?? '---' }}</td>
                        <td>{{ $asistencia->horario->dia_semana ?? '---' }}</td>
                        <td>{{ \Carbon\Carbon::parse($asistencia->fecha)->format('d/m/Y') }}</td>
                        <td>{{ $asistencia->hora ?? '---' }}</td>
                        <td>{{ $asistencia->tipo_verificacion ?? '---' }}</td>
                        <td>
                            @if($asistencia->estado === 'activo')
                                <span class="badge bg-success">Activo</span>
                            @else
                                <span class="badge bg-secondary">Inactivo</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center">No se han registrado asistencias.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $asistencias->links() }}
</div>
@endsection
