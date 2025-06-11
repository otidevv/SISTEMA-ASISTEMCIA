@extends('layouts.app') {{-- o tu layout base --}}

@section('title', 'Horarios de Docentes')

@section('content')
<div class="container">
    <h4 class="mb-3">Horarios de Docentes</h4>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <a href="{{ route('horarios-docentes.create') }}" class="btn btn-primary mb-3">
        <i class="uil uil-plus"></i> Asignar nuevo horario
    </a>

    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead class="thead-dark">
                <tr>
                    <th>Docente</th>
                    <th>Día</th>
                    <th>Inicio</th>
                    <th>Fin</th>
                    <th>Aula</th>
                    <th>Curso</th>
                    <th>Ciclo</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($horarios as $horario)
                    <tr>
                        <td>{{ $horario->docente->nombre_completo ?? '---' }}</td>
                        <td>{{ $horario->dia_semana }}</td>
                        <td>{{ \Carbon\Carbon::parse($horario->hora_inicio)->format('H:i') }}</td>
                        <td>{{ \Carbon\Carbon::parse($horario->hora_fin)->format('H:i') }}</td>
                        <td>{{ $horario->aula->nombre ?? '---' }}</td>
                        <td>{{ $horario->curso->nombre ?? '---' }}</td>
                        <td>{{ $horario->ciclo->nombre ?? '---' }}</td>
                        <td>
                            <a href="{{ route('horarios-docentes.edit', $horario->id) }}" class="btn btn-sm btn-warning">Editar</a>
                            <form action="{{ route('horarios-docentes.destroy', $horario->id) }}" method="POST" style="display:inline-block">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar este horario?')">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center">No hay horarios registrados.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $horarios->links() }}
</div>
@endsection
