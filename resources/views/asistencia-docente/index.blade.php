@extends('layouts.app')

@section('content')
<div class="container">
    <h4 class="mb-4">Asistencia de Docentes</h4>

    {{-- Filtros --}}
    <form method="GET" class="row g-3 mb-4">
        <div class="col-md-3">
            <label for="fecha_inicio" class="form-label">Desde</label>
            <input type="date" class="form-control" name="fecha_inicio" value="{{ request('fecha_inicio') }}">
        </div>
        <div class="col-md-3">
            <label for="fecha_fin" class="form-label">Hasta</label>
            <input type="date" class="form-control" name="fecha_fin" value="{{ request('fecha_fin') }}">
        </div>
        <div class="col-md-4">
            <label for="documento" class="form-label">Documento del Docente</label>
            <input type="text" class="form-control" name="documento" placeholder="DNI o código" value="{{ request('documento') }}">
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <button type="submit" class="btn btn-primary w-100">Filtrar</button>
        </div>
    </form>

    {{-- Tabla --}}
    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Docente</th>
                    <th>Curso</th>
                    <th>Fecha y Hora</th>
                    <th>Estado</th>
                    <th>Tema</th>
                    <th>Terminal</th>
                    <th>Código</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($asistencias as $index => $asistencia)
                    <tr>
                        <td>{{ $loop->iteration + ($asistencias->currentPage() - 1) * $asistencias->perPage() }}</td>
                        <td>{{ $asistencia->docente->nombre_completo ?? '---' }}</td>
                        <td>{{ $asistencia->horario->curso->nombre ?? '-' }}</td>
                        <td>{{ \Carbon\Carbon::parse($asistencia->fecha_hora)->format('d/m/Y H:i') }}</td>
                        <td><span class="badge bg-{{ $asistencia->estado === 'entrada' ? 'success' : 'secondary' }}">{{ ucfirst($asistencia->estado) }}</span></td>
                        <td>{{ $asistencia->tema_desarrollado ?? '-' }}</td>
                        <td>{{ $asistencia->terminal_id ?? '-' }}</td>
                        <td>{{ $asistencia->codigo_trabajo ?? '-' }}</td>
                        <td>
                            @can('asistencia-docente.edit')
                                <a href="#" class="btn btn-sm btn-info disabled">Editar</a>
                            @endcan
                            @can('asistencia-docente.delete')
                                <form action="{{ route('asistencia-docente.eliminar', $asistencia->id) }}" method="POST" style="display:inline-block;">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar este registro?')">Eliminar</button>
                                </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center">No se encontraron registros.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Paginación --}}
    <div class="mt-3">
        {{ $asistencias->appends(request()->query())->links() }}
    </div>
</div>
@endsection
