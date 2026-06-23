@extends('layouts.app')

@section('title', 'Catálogo TUSNE')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0"><i class="mdi mdi-tag-multiple-outline me-2"></i>Catálogo TUSNE (Tarifario)</h4>
        <a href="{{ route('tusne.create') }}" class="btn btn-primary btn-sm"><i class="mdi mdi-plus"></i> Nuevo concepto</a>
    </div>

    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif

    {{-- Documento TUSNE oficial (sustento legal) --}}
    <div class="card mb-3 border-primary">
        <div class="card-header"><i class="mdi mdi-file-pdf-box me-1"></i>Documento TUSNE Oficial (sustento)</div>
        <div class="card-body">
            @if($documentos->count())
                <ul class="list-group mb-3">
                    @foreach($documentos as $doc)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <a href="{{ asset('storage/' . $doc->path) }}" target="_blank">
                                <i class="mdi mdi-file-pdf-box text-danger"></i>
                                TUSNE {{ $doc->anio ?? '' }} — {{ $doc->nombre_original ?? $doc->path }}
                            </a>
                            <form method="POST" action="{{ route('tusne.documento.destroy', $doc->id) }}" onsubmit="return confirm('¿Eliminar este documento?');" class="d-inline">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="mdi mdi-delete"></i></button>
                            </form>
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="text-muted">Aún no se ha subido el TUSNE oficial. Súbelo en PDF para dar sustento al tarifario.</p>
            @endif

            <form method="POST" action="{{ route('tusne.documento.store') }}" enctype="multipart/form-data" class="row g-2 align-items-end">
                @csrf
                <div class="col-auto">
                    <label class="form-label">Año</label>
                    <input type="text" name="anio" class="form-control form-control-sm" placeholder="2024" style="width:90px;">
                </div>
                <div class="col-auto flex-fill">
                    <label class="form-label">Archivo PDF del TUSNE</label>
                    <input type="file" name="documento" class="form-control form-control-sm" accept=".pdf" required>
                </div>
                <div class="col-auto">
                    <button class="btn btn-sm btn-primary"><i class="mdi mdi-upload"></i> Subir TUSNE</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card"><div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover table-centered mb-0">
                <thead class="table-light">
                    <tr><th>Código</th><th>Nombre</th><th>Categoría</th><th class="text-end">Costo</th><th>Pago</th><th>Estado</th><th class="text-center">Acción</th></tr>
                </thead>
                <tbody>
                    @forelse($conceptos as $c)
                        <tr>
                            <td class="fw-bold">{{ $c->codigo }}</td>
                            <td>{{ $c->nombre }}</td>
                            <td><span class="badge bg-light text-dark text-capitalize">{{ $c->categoria }}</span></td>
                            <td class="text-end">S/ {{ number_format($c->costo, 2) }}</td>
                            <td>{{ $c->requiere_pago ? 'Sí' : 'No' }}</td>
                            <td>@if($c->activo)<span class="badge bg-success">Activo</span>@else<span class="badge bg-secondary">Inactivo</span>@endif</td>
                            <td class="text-center">
                                <a href="{{ route('tusne.edit', $c->id) }}" class="btn btn-warning btn-sm"><i class="mdi mdi-pencil"></i></a>
                                <form method="POST" action="{{ route('tusne.destroy', $c->id) }}" class="d-inline" onsubmit="return confirm('¿Eliminar el concepto {{ $c->codigo }}?');">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-danger btn-sm"><i class="mdi mdi-delete"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center py-4 text-muted">Sin conceptos. Crea el primero.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($conceptos->hasPages())<div class="mt-3">{{ $conceptos->links() }}</div>@endif
    </div></div>
</div>
@endsection
