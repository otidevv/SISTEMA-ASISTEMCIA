@extends('layouts.app')

@section('title', 'Gestión de Anuncios')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">
                        <i data-feather="megaphone"></i> Gestión de Anuncios
                    </h4>
                    {{-- ✅ USAR NOMBRES DESCRIPTIVOS QUE FUNCIONAN --}}
                    @if (Auth::user()->hasPermission('Crear Anuncio'))
                        <a href="{{ route('anuncios.create') }}" class="btn btn-primary">
                            <i data-feather="plus"></i> Crear Nuevo Anuncio
                        </a>
                    @endif
                </div>
                
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Título</th>
                                    <th>Tipo</th>
                                    <th>Dirigido A</th>
                                    <th>Estado</th>
                                    <th>Prioridad</th>
                                    <th>Fecha Creación</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($anuncios as $anuncio)
                                    <tr>
                                        <td>{{ $anuncio->id }}</td>
                                        <td>
                                            <strong>{{ $anuncio->titulo }}</strong>
                                            @if($anuncio->descripcion)
                                                <br>
                                                <small class="text-muted">{{ Str::limit($anuncio->descripcion, 50) }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            @switch($anuncio->tipo)
                                                @case('urgente')
                                                    <span class="badge bg-danger">Urgente</span>
                                                    @break
                                                @case('importante')
                                                    <span class="badge bg-warning">Importante</span>
                                                    @break
                                                @case('mantenimiento')
                                                    <span class="badge bg-secondary">Mantenimiento</span>
                                                    @break
                                                @case('evento')
                                                    <span class="badge bg-success">Evento</span>
                                                    @break
                                                @default
                                                    <span class="badge bg-info">Informativo</span>
                                            @endswitch
                                        </td>
                                        <td>
                                            <span class="badge bg-primary">{{ ucfirst($anuncio->dirigido_a) }}</span>
                                        </td>
                                        <td>
                                            @if($anuncio->es_activo)
                                                @if(method_exists($anuncio, 'estaVigente') && $anuncio->estaVigente())
                                                    <span class="badge bg-success">Activo</span>
                                                @else
                                                    <span class="badge bg-warning">Programado/Expirado</span>
                                                @endif
                                            @else
                                                <span class="badge bg-secondary">Inactivo</span>
                                            @endif
                                        </td>
                                        <td>
                                            @switch($anuncio->prioridad)
                                                @case(4)
                                                    <span class="badge bg-danger">Crítica</span>
                                                    @break
                                                @case(3)
                                                    <span class="badge bg-warning">Alta</span>
                                                    @break
                                                @case(2)
                                                    <span class="badge bg-info">Media</span>
                                                    @break
                                                @default
                                                    <span class="badge bg-secondary">Baja</span>
                                            @endswitch
                                        </td>
                                        <td>{{ $anuncio->created_at->format('d/m/Y H:i') }}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                {{-- ✅ USAR NOMBRES DESCRIPTIVOS QUE FUNCIONAN --}}
                                                @if (Auth::user()->hasPermission('Ver Anuncios'))
                                                    <a href="{{ route('anuncios.show', $anuncio) }}" class="btn btn-sm btn-info" title="Ver">
                                                        <i data-feather="eye"></i>
                                                    </a>
                                                @endif

                                                @if (Auth::user()->hasPermission('Editar Anuncio'))
                                                    <a href="{{ route('anuncios.edit', $anuncio) }}" class="btn btn-sm btn-warning" title="Editar">
                                                        <i data-feather="edit"></i>
                                                    </a>
                                                @endif

                                                @if (Auth::user()->hasPermission('Eliminar Anuncio'))
                                                    <form action="{{ route('anuncios.destroy', $anuncio) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger" 
                                                                onclick="return confirm('¿Estás seguro de eliminar este anuncio?')" 
                                                                title="Eliminar">
                                                            <i data-feather="trash"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4">
                                            <div class="text-muted">
                                                <i data-feather="megaphone" style="width: 48px; height: 48px;" class="mb-3"></i>
                                                <p>No hay anuncios creados aún.</p>
                                                {{-- ✅ USAR NOMBRES DESCRIPTIVOS QUE FUNCIONAN --}}
                                                @if (Auth::user()->hasPermission('Crear Anuncio'))
                                                    <a href="{{ route('anuncios.create') }}" class="btn btn-primary">
                                                        <i data-feather="plus"></i> Crear Primer Anuncio
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if(method_exists($anuncios, 'links'))
                        <div class="d-flex justify-content-center">
                            {{ $anuncios->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>feather.replace();</script>
@endpush
@endsection