@extends('layouts.app')

@section('title', 'Cursos')

@section('content')
<div class="container">
    <h4 class="mb-3">Listado de Cursos</h4>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <a href="{{ route('cursos.create') }}" class="btn btn-primary mb-3">
        <i class="uil uil-plus"></i> Nuevo Curso
    </a>

    <div class="table-responsive">
        <table class="table table-bordered table-hover">
            <thead class="thead-dark">
                <tr>
                    <th>Nombre</th>
                    <th>Código</th>
                    <th>Descripción</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($cursos as $curso)
                    <tr>
                        <td>{{ $curso->nombre }}</td>
                        <td>{{ $curso->codigo }}</td>
                        <td>{{ $curso->descripcion ?? '-' }}</td>
                        <td>
                            <span class="badge bg-{{ $curso->estado ? 'success' : 'secondary' }}">
                                {{ $curso->estado ? 'Activo' : 'Inactivo' }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('cursos.edit', $curso->id) }}" class="btn btn-sm btn-warning">Editar</a>

                            <form action="{{ route('cursos.destroy', $curso->id) }}" method="POST" style="display:inline-block">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-danger" onclick="return confirm('¿Eliminar este curso?')">Eliminar</button>
                            </form>

                            <form action="{{ route('cursos.toggle', $curso->id) }}" method="POST" style="display:inline-block">
                                @csrf @method('PUT')
                                <button class="btn btn-sm btn-{{ $curso->estado ? 'secondary' : 'success' }}">
                                    {{ $curso->estado ? 'Desactivar' : 'Activar' }}
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">No hay cursos registrados.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $cursos->links() }}
</div>
@endsection
