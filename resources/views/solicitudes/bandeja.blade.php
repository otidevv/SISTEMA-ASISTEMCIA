@extends('layouts.app')

@section('title', 'Pendientes de Visto Bueno')

@section('content')
<div class="container-fluid">
    <h4 class="mb-3"><i class="mdi mdi-check-decagram-outline me-2"></i>Pendientes de Visto Bueno (Director)</h4>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

    <div class="card"><div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover table-centered mb-0">
                <thead class="table-light">
                    <tr><th>Código</th><th>Trámite</th><th>Solicitante</th><th>Fecha</th><th class="text-center">Acción</th></tr>
                </thead>
                <tbody>
                    @forelse($solicitudes as $s)
                        <tr>
                            <td class="fw-bold">{{ $s->codigo }}</td>
                            <td>{{ $s->tipo->nombre ?? '—' }}</td>
                            <td>{{ $s->estudiante->nombre ?? '' }} {{ $s->estudiante->apellido_paterno ?? '' }}<br><small class="text-muted">{{ $s->numero_documento }}</small></td>
                            <td>{{ $s->created_at?->format('d/m/Y H:i') }}</td>
                            <td class="text-center">
                                <a href="{{ route('solicitudes.show', $s->id) }}" class="btn btn-success btn-sm"><i class="mdi mdi-eye-check"></i> Revisar y dar V°B°</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center py-4 text-muted"><i class="mdi mdi-check-all fs-2"></i><div>No hay trámites pendientes de V°B°.</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($solicitudes->hasPages())<div class="mt-3">{{ $solicitudes->links() }}</div>@endif
    </div></div>
</div>
@endsection
