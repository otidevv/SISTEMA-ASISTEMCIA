@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Materiales Académicos</h1>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Listado de Materiales</h6>
            @can('gestionar_materiales_academicos')
                <a href="{{ route('materiales-academicos.create') }}" class="btn btn-primary btn-sm float-right">Subir Nuevo Material</a>
            @endcan
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Título</th>
                            <th>Curso</th>
                            <th>Ciclo</th>
                            <th>Aula</th>
                            <th>Semana</th>
                            <th>Subido por</th>
                            <th>Fecha</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($materiales as $material)
                            <tr>
                                <td>{{ $material->titulo }}</td>
                                <td>{{ $material->curso->nombre ?? 'N/A' }}</td>
                                <td>{{ $material->ciclo->nombre ?? 'N/A' }}</td>
                                <td>{{ $material->aula->nombre ?? 'N/A' }}</td>
                                <td>{{ $material->semana }}</td>
                                <td>{{ $material->profesor->nombre_completo ?? 'N/A' }}</td>
                                <td>{{ $material->created_at->format('d/m/Y') }}</td>
                                <td>
                                    {{-- Aquí irán los botones de acción (ver, editar, eliminar) --}}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">No hay materiales disponibles.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
