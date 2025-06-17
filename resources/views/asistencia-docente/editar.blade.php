@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4><i class="fas fa-edit"></i> Editar Registros de Asistencia Docente</h4>
        <a href="{{ route('asistencia-docente.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>

    {{-- Formulario de búsqueda --}}
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="fas fa-search"></i> Buscar Registros para Editar</h5>
        </div>
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label for="fecha_desde" class="form-label">Fecha Desde</label>
                    <input type="date" class="form-control" name="fecha_desde" 
                           value="{{ request('fecha_desde') }}" id="fecha_desde">
                </div>
                <div class="col-md-4">
                    <label for="fecha_hasta" class="form-label">Fecha Hasta</label>
                    <input type="date" class="form-control" name="fecha_hasta" 
                           value="{{ request('fecha_hasta') }}" id="fecha_hasta">
                </div>
                <div class="col-md-4">
                    <label for="documento" class="form-label">Documento del Docente</label>
                    <input type="text" class="form-control" name="documento" 
                           placeholder="DNI o código" value="{{ request('documento') }}">
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Buscar Registros
                    </button>
                    <a href="{{ route('asistencia-docente.editar') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Limpiar Filtros
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Resultados de búsqueda --}}
    @if(isset($asistencias))
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-list"></i> Registros Encontrados 
                    <span class="badge bg-primary">{{ $asistencias->total() }}</span>
                </h5>
            </div>
            <div class="card-body">
                @if($asistencias->count() > 0)
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>Instrucciones:</strong> Haga clic en el botón "Editar" de cualquier registro para modificar sus datos.
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Docente</th>
                                    <th>Documento</th>
                                    <th>Fecha y Hora</th>
                                    <th>Estado</th>
                                    <th>Curso</th>
                                    <th>Tipo</th>
                                    <th>Terminal</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($asistencias as $asistencia)
                                    <tr>
                                        <td>{{ $loop->iteration + ($asistencias->currentPage() - 1) * $asistencias->perPage() }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @if($asistencia->docente && $asistencia->docente->foto_perfil)
                                                    <img src="{{ asset('storage/' . $asistencia->docente->foto_perfil) }}" 
                                                         class="rounded-circle me-2" width="32" height="32" alt="Foto">
                                                @else
                                                    <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center me-2" 
                                                         style="width: 32px; height: 32px; color: white; font-size: 12px;">
                                                        {{ $asistencia->docente ? strtoupper(substr($asistencia->docente->nombre, 0, 1)) : 'N/A' }}
                                                    </div>
                                                @endif
                                                <div>
                                                    <div class="fw-bold">
                                                        {{ $asistencia->docente ? $asistencia->docente->nombre . ' ' . $asistencia->docente->apellido_paterno : 'N/A' }}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $asistencia->docente->numero_documento ?? 'N/A' }}</td>
                                        <td>{{ $asistencia->fecha_hora->format('d/m/Y H:i:s') }}</td>
                                        <td>
                                            <span class="badge bg-{{ $asistencia->estado === 'entrada' ? 'success' : 'secondary' }}">
                                                <i class="fas fa-{{ $asistencia->estado === 'entrada' ? 'sign-in-alt' : 'sign-out-alt' }}"></i>
                                                {{ ucfirst($asistencia->estado) }}
                                            </span>
                                        </td>
                                        <td>{{ $asistencia->horario && $asistencia->horario->curso ? $asistencia->horario->curso->nombre : '-' }}</td>
                                        <td>
                                            <span class="badge bg-{{ $asistencia->tipo_verificacion === 'manual' ? 'warning' : 'info' }}">
                                                {{ ucfirst($asistencia->tipo_verificacion ?? 'manual') }}
                                            </span>
                                        </td>
                                        <td>{{ $asistencia->terminal_id ?? 'MANUAL' }}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                @can('asistencia-docente.edit')
                                                    <a href="{{ route('asistencia-docente.edit', $asistencia->id) }}" 
                                                       class="btn btn-sm btn-primary" title="Editar">
                                                        <i class="fas fa-edit"></i> Editar
                                                    </a>
                                                @endcan
                                                @can('asistencia-docente.delete')
                                                    <form action="{{ route('asistencia-docente.destroy', $asistencia->id) }}" 
                                                          method="POST" style="display:inline-block;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button class="btn btn-sm btn-danger" 
                                                                onclick="return confirm('¿Está seguro de eliminar este registro?')"
                                                                title="Eliminar">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Paginación --}}
                    @if($asistencias->hasPages())
                        <div class="mt-3">
                            {{ $asistencias->appends(request()->query())->links() }}
                        </div>
                    @endif
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-search fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No se encontraron registros</h5>
                        <p class="text-muted">No hay registros de asistencia docente que coincidan con los criterios de búsqueda.</p>
                        <div class="mt-3">
                            <a href="{{ route('asistencia-docente.editar') }}" class="btn btn-secondary">
                                <i class="fas fa-redo"></i> Intentar Nueva Búsqueda
                            </a>
                            @can('asistencia-docente.create')
                                <a href="{{ route('asistencia-docente.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Crear Nuevo Registro
                                </a>
                            @endcan
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @else
        {{-- Mensaje inicial --}}
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fas fa-edit fa-4x text-muted mb-4"></i>
                <h5 class="text-muted">Editar Registros de Asistencia Docente</h5>
                <p class="text-muted mb-4">
                    Utilice el formulario de búsqueda para encontrar los registros de asistencia docente que desea editar.
                    Puede filtrar por rango de fechas y documento del docente.
                </p>
                <div class="alert alert-info">
                    <h6><i class="fas fa-lightbulb"></i> Consejos de búsqueda:</h6>
                    <ul class="list-unstyled mb-0">
                        <li><i class="fas fa-check text-success"></i> Use un rango de fechas específico para resultados más precisos</li>
                        <li><i class="fas fa-check text-success"></i> Ingrese el documento completo o parcial del docente</li>
                        <li><i class="fas fa-check text-success"></i> Deje los campos vacíos para ver todos los registros</li>
                    </ul>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    // Establecer fechas por defecto (últimos 7 días)
    document.addEventListener('DOMContentLoaded', function() {
        const fechaDesde = document.getElementById('fecha_desde');
        const fechaHasta = document.getElementById('fecha_hasta');
        
        // Si no hay valores, establecer rango de la última semana
        if (!fechaDesde.value && !fechaHasta.value) {
            const hoy = new Date();
            const hace7Dias = new Date();
            hace7Dias.setDate(hoy.getDate() - 7);
            
            fechaDesde.value = hace7Dias.toISOString().split('T')[0];
            fechaHasta.value = hoy.toISOString().split('T')[0];
        }
    });
</script>
@endpush
