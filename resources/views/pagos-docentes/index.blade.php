@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="page-title">📄 Pagos a Docentes</h4>
        <a href="{{ route('pagos-docentes.create') }}" class="btn btn-success">
            <i class="uil uil-plus-circle me-1"></i> Nuevo Pago
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="uil uil-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-centered table-striped">
                    <thead class="table-light">
                        <tr>
                            <th>👨‍🏫 Docente</th>
                            <th>💲 Tarifa por Hora</th>
                            <th>🗓️ Fecha Inicio</th>
                            <th>📆 Fecha Fin</th>
                            <th>⚙️ Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pagos as $pago)
                            <tr>
                                <td>
                                    @if($pago->docente)
                                        {{ $pago->docente->nombre }} {{ $pago->docente->apellido_paterno }}
                                    @else
                                        <span class="text-muted">Docente no encontrado</span>
                                    @endif
                                </td>
                                <td>S/ {{ number_format($pago->tarifa_por_hora, 2) }}</td>
                                <td>{{ \Carbon\Carbon::parse($pago->fecha_inicio)->format('d/m/Y') }}</td>
                                <td>{{ $pago->fecha_fin ? \Carbon\Carbon::parse($pago->fecha_fin)->format('d/m/Y') : '-' }}</td>
                                <td>
                                    <a href="{{ route('pagos-docentes.edit', $pago->id) }}" class="btn btn-sm btn-outline-warning">
                                        <i class="uil uil-edit"></i>
                                    </a>
                                    <form action="{{ route('pagos-docentes.destroy', $pago->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('¿Está seguro de eliminar este pago?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="uil uil-trash-alt"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach

                        @if($pagos->isEmpty())
                            <tr>
                                <td colspan="5" class="text-center text-muted">No hay pagos registrados.</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $pagos->links() }}
            </div>
        </div>
    </div>
</div>
@endsection