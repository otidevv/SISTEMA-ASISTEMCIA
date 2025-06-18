@extends('layouts.app')

@section('title', 'Horarios de Docentes')

@push('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
    }

    .btn-rounded {
        border-radius: 50px;
    }

    .table thead th {
        background-color: #4e73df;
        color: #fff;
        text-transform: uppercase;
        font-size: 0.85rem;
        letter-spacing: 0.5px;
    }

    .table td {
        vertical-align: middle;
    }

    .badge-day {
        font-size: 0.85rem;
        font-weight: 500;
        padding: 0.4rem 0.8rem;
        border-radius: 50px;
        background-color: #e0e7ff;
        color: #3730a3;
    }

    .action-buttons .btn {
        margin-right: 0.3rem;
    }

    .alert-success {
        background: #e6f4ea;
        color: #276749;
        border: 1px solid #c6f6d5;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">

    <div class="page-header">
        <h3 class="mb-0">
            <i class="fas fa-calendar-alt me-2 text-primary"></i>
            Horarios de Docentes
        </h3>
        <a href="{{ route('horarios-docentes.create') }}" class="btn btn-primary btn-rounded shadow-sm">
            <i class="fas fa-plus me-1"></i> Nuevo Horario
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success shadow-sm rounded mb-4">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
        </div>
    @endif

    <div class="card shadow-sm rounded">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table align-middle table-hover">
                    <thead>
                        <tr>
                            <th>Docente</th>
                            <th>Día</th>
                            <th>Inicio</th>
                            <th>Fin</th>
                            <th>Aula</th>
                            <th>Curso</th>
                            <th>Ciclo</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($horarios as $horario)
                            <tr>
                                <td>{{ $horario->docente->nombre_completo ?? '---' }}</td>
                                <td><span class="badge-day">{{ ucfirst($horario->dia_semana) }}</span></td>
                                <td>{{ \Carbon\Carbon::parse($horario->hora_inicio)->format('H:i') }}</td>
                                <td>{{ \Carbon\Carbon::parse($horario->hora_fin)->format('H:i') }}</td>
                                <td>{{ $horario->aula->nombre ?? '---' }}</td>
                                <td>{{ $horario->curso->nombre ?? '---' }}</td>
                                <td>{{ $horario->ciclo->nombre ?? '---' }}</td>
                                <td class="text-center action-buttons">
                                    <a href="{{ route('horarios-docentes.edit', $horario->id) }}" class="btn btn-warning btn-sm" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('horarios-docentes.destroy', $horario->id) }}" method="POST" class="d-inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Deseas eliminar este horario?')" title="Eliminar">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    <i class="fas fa-calendar-times fa-2x mb-2"></i><br>
                                    No hay horarios registrados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <div class="mt-3">
                {{ $horarios->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>
@endsection
