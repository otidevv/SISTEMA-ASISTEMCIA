@extends('layouts.app')

@section('title', 'Trámites por Atender')

@section('content')
<div class="container-fluid">
    <h4 class="mb-3"><i class="mdi mdi-clipboard-text-clock-outline me-2"></i>Trámites por Atender</h4>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif

    <div class="card"><div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover table-centered mb-0">
                <thead class="table-light">
                    <tr><th>Código</th><th>Trámite</th><th>Solicitante</th><th>Derivado</th><th class="text-center">Acción</th></tr>
                </thead>
                <tbody>
                    @forelse($solicitudes as $s)
                        <tr>
                            <td class="fw-bold">{{ $s->codigo }}</td>
                            <td>{{ $s->tipo->nombre ?? '—' }}</td>
                            <td>{{ $s->estudiante->nombre ?? '' }} {{ $s->estudiante->apellido_paterno ?? '' }}<br><small class="text-muted">{{ $s->numero_documento }}</small></td>
                            <td>{{ $s->updated_at?->format('d/m/Y H:i') }}</td>
                            <td class="text-center">
                                <a href="{{ route('solicitudes.show', $s->id) }}" class="btn btn-primary btn-sm"><i class="mdi mdi-clipboard-check"></i> Atender</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center py-4 text-muted"><i class="mdi mdi-inbox-outline fs-2"></i><div>No tienes trámites por atender.</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($solicitudes->hasPages())<div class="mt-3">{{ $solicitudes->links() }}</div>@endif
    </div></div>
</div>
@endsection
