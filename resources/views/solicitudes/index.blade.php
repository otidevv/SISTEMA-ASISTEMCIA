@extends('layouts.app')

@section('title', 'Mis Trámites')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0"><i class="mdi mdi-file-document-multiple-outline me-2"></i>Mis Trámites</h4>
        <a href="{{ route('solicitudes.create') }}" class="btn btn-primary btn-sm">
            <i class="mdi mdi-plus"></i> Nueva solicitud
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-centered mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Código</th>
                            <th>Trámite</th>
                            <th>Estado</th>
                            <th>Actualmente con</th>
                            <th>Fecha</th>
                            <th class="text-center">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($solicitudes as $s)
                            <tr>
                                <td class="fw-bold">{{ $s->codigo }}</td>
                                <td>{{ $s->tipo->nombre ?? '—' }}</td>
                                <td>@include('solicitudes.partials.estado', ['estado' => $s->estado])</td>
                                <td>
                                    @if($s->usuarioActual)
                                        {{ $s->usuarioActual->nombre }} {{ $s->usuarioActual->apellido_paterno }}
                                    @elseif($s->rolActual)
                                        {{ $s->rolActual->nombre }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>{{ $s->created_at?->format('d/m/Y H:i') }}</td>
                                <td class="text-center">
                                    <a href="{{ route('solicitudes.show', $s->id) }}" class="btn btn-info btn-sm" title="Ver expediente">
                                        <i class="mdi mdi-eye"></i> Seguimiento
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center py-4 text-muted">
                                <i class="mdi mdi-inbox-outline fs-2"></i>
                                <div>Aún no tienes trámites. Crea uno con "Nueva solicitud".</div>
                            </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($solicitudes->hasPages())
                <div class="mt-3">{{ $solicitudes->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
