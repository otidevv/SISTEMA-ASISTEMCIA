@extends('layouts.app')

@section('title', 'Asistencia Docente')

@section('content')
<div class="container-fluid">
    <div class="page-title-box">
        <h4 class="page-title">Listado de Asistencia Docente</h4>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card">
        <div class="card-body">
            <table class="table table-bordered table-striped align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Docente</th>
                        <th>Curso</th>
                        <th>Fecha</th>
                        <th>Hora Entrada</th>
                        <th>Hora Salida</th>
                        <th>Turno</th>
                        <th>Aula</th>
                        <th>Estado</th>
                        <th>Tema Desarrollado</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($asistencias as $asistencia)
                        <tr>
                            <td>{{ $asistencia->docente->nombre }} {{ $asistencia->docente->apellido_paterno }}</td>
                            <td>{{ $asistencia->horario->curso->nombre ?? '—' }}</td>
                            <td>{{ \Carbon\Carbon::parse($asistencia->fecha_hora)->format('d/m/Y') }}</td>
                            <td>{{ $asistencia->horario->hora_inicio ?? '—' }}</td>
                            <td>{{ $asistencia->horario->hora_fin ?? '—' }}</td>
                            <td>{{ ucfirst($asistencia->horario->turno ?? '—') }}</td>
                            <td>{{ $asistencia->horario->aula->nombre ?? '—' }}</td>
                            <td>
                                <span class="badge {{ $asistencia->estado === 'Presente' ? 'bg-success' : 'bg-danger' }}">
                                    {{ $asistencia->estado }}
                                </span>
                            </td>
                            <td>
                                @if ($asistencia->tema_desarrollado)
                                    {{ $asistencia->tema_desarrollado }}
                                @else
                                    <span class="text-muted">Pendiente</span>
                                @endif
                            </td>
                            <td>
                                @if (!$asistencia->tema_desarrollado)
                                    <a href="{{ route('asistencia-docente.tema.form', $asistencia->id) }}"
                                       class="btn btn-sm btn-primary">Registrar Tema</a>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center text-muted">No hay registros de asistencia.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <div class="d-flex justify-content-end mt-3">
                {{ $asistencias->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>
@endsection
