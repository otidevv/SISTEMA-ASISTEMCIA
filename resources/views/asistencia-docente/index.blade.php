@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4>Asistencia de Docentes</h4>
            <div>
                @can('asistencia-docente.create')
                    <a href="{{ route('asistencia-docente.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Registrar Asistencia
                    </a>
                @endcan
                @can('asistencia-docente.monitor')
                    <a href="{{ route('asistencia-docente.monitor') }}" class="btn btn-info">
                        <i class="fas fa-eye"></i> Monitor en Tiempo Real
                    </a>
                @endcan
                @can('asistencia-docente.report')
                    <a href="{{ route('asistencia-docente.report') }}" class="btn btn-secondary">
                        <i class="fas fa-file-alt"></i> Reportes
                    </a>
                @endcan
            </div>
        </div>

        {{-- Filtros --}}
        <form method="GET" class="row g-3 mb-4">
            <div class="col-md-4">
                <label for="fecha" class="form-label">Fecha</label>
                <input type="date" class="form-control" name="fecha" value="{{ $fecha }}">
            </div>
            <div class="col-md-4">
                <label for="documento" class="form-label">Documento del Docente</label>
                <input type="text" class="form-control" name="documento" placeholder="DNI o código"
                    value="{{ $documento }}">
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">
                    <i class="fas fa-search"></i> Filtrar
                </button>
                <a href="{{ route('asistencia-docente.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Limpiar
                </a>
            </div>
        </form>

        {{-- Estadísticas rápidas --}}
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">Total Hoy</h6>
                                <h4>{{ $asistencias->where('fecha_registro', '>=', now()->startOfDay())->count() }}</h4>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-calendar-day fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">Entradas</h6>
                                <h4>{{ $asistencias->where('tipo_asistencia', 'entrada')->count() }}</h4>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-sign-in-alt fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">Salidas</h6>
                                <h4>{{ $asistencias->where('tipo_asistencia', 'salida')->count() }}</h4>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-sign-out-alt fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <h6 class="card-title">Docentes Activos</h6>
                                <h4>{{ $asistencias->pluck('nro_documento')->unique()->count() }}</h4>
                            </div>
                            <div class="align-self-center">
                                <i class="fas fa-users fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tabla --}}
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Registros de Asistencia - {{ \Carbon\Carbon::parse($fecha)->format('d/m/Y') }}</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Docente</th>
                                <th>Documento</th>
                                <th>Curso</th>
                                <th>Fecha y Hora</th>
                                <th>Tipo</th>
                                <th>Verificación</th>
                                <th>Terminal</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($asistencias as $asistencia)
                                <tr @if(isset($asistencia->horario) && $asistencia->horario && $asistencia->horario->curso && $asistencia->horario->curso->codigo === 'A-1' && $asistencia->horario->turno === 'mañana') style="box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);" @endif>
                                    <td>{{ $loop->iteration + ($asistencias->currentPage() - 1) * $asistencias->perPage() }}
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if ($asistencia->usuario && $asistencia->usuario->foto_perfil)
                                                <img src="{{ asset('storage/' . $asistencia->usuario->foto_perfil) }}"
                                                    class="rounded-circle me-2" width="32" height="32"
                                                    alt="Foto">
                                            @else
                                                <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center me-2"
                                                    style="width: 32px; height: 32px; color: white; font-size: 12px;">
                                                    {{ $asistencia->usuario ? strtoupper(substr($asistencia->usuario->nombre, 0, 1)) : 'N/A' }}
                                                </div>
                                            @endif
                                            <div>
                                                <div class="fw-bold">
                                                    {{ $asistencia->usuario ? $asistencia->usuario->nombre . ' ' . $asistencia->usuario->apellido_paterno : 'N/A' }}
                                                </div>
                                                @if ($asistencia->usuario && $asistencia->usuario->codigo_trabajo)
                                                    <small class="text-muted">Código:
                                                        {{ $asistencia->usuario->codigo_trabajo }}</small>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $asistencia->nro_documento }}</td>
                                    <td>
                                        @if (isset($asistencia->horario) && $asistencia->horario && $asistencia->horario->curso)
                                            {{ $asistencia->horario->curso->nombre }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($asistencia->fecha_registro)->format('d/m/Y H:i:s') }}</td>
                                    <td>
                                        <span
                                            class="badge bg-{{ isset($asistencia->tipo_asistencia) && $asistencia->tipo_asistencia === 'entrada' ? 'success' : 'secondary' }}">
                                            <i
                                                class="fas fa-{{ isset($asistencia->tipo_asistencia) && $asistencia->tipo_asistencia === 'entrada' ? 'sign-in-alt' : 'sign-out-alt' }}"></i>
                                            {{ isset($asistencia->tipo_asistencia) ? ucfirst($asistencia->tipo_asistencia) : 'N/A' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span
                                            class="badge bg-{{ $asistencia->tipo_verificacion == 4 ? 'warning' : 'info' }}">
                                            {{ $asistencia->tipo_verificacion_texto }}
                                        </span>
                                    </td>
                                    <td>{{ $asistencia->terminal_id ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge bg-{{ $asistencia->estado ? 'success' : 'danger' }}">
                                            {{ $asistencia->estado ? 'Activo' : 'Inactivo' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            @can('asistencia-docente.show')
                                                <button type="button" class="btn btn-sm btn-outline-info"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#modal-detalle-{{ $asistencia->id }}" title="Ver detalles">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            @endcan
                                            @can('asistencia-docente.edit')
                                                <a href="{{ route('asistencia-docente.edit', $asistencia->id) }}"
                                                    class="btn btn-sm btn-outline-primary" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            @endcan
                                            @can('asistencia-docente.delete')
                                                <form action="{{ route('asistencia-docente.destroy', $asistencia->id) }}"
                                                    method="POST" style="display:inline-block;">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="btn btn-sm btn-outline-danger"
                                                        onclick="return confirm('¿Está seguro de eliminar este registro?')"
                                                        title="Eliminar">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>

                                {{-- Modal de detalles --}}
                                <div class="modal fade" id="modal-detalle-{{ $asistencia->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Detalles de Asistencia</h5>
                                                <button type="button" class="btn-close"
                                                    data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <dl class="row">
                                                    <dt class="col-sm-4">Docente:</dt>
                                                    <dd class="col-sm-8">
                                                        {{ $asistencia->usuario ? $asistencia->usuario->nombre . ' ' . $asistencia->usuario->apellido_paterno . ' ' . $asistencia->usuario->apellido_materno : 'N/A' }}
                                                    </dd>

                                                    <dt class="col-sm-4">Documento:</dt>
                                                    <dd class="col-sm-8">{{ $asistencia->nro_documento }}</dd>

                                                    <dt class="col-sm-4">Código Trabajo:</dt>
                                                    <dd class="col-sm-8">{{ $asistencia->codigo_trabajo ?? 'N/A' }}</dd>

                                                    <dt class="col-sm-4">Fecha y Hora:</dt>
                                                    <dd class="col-sm-8">
                                                        {{ \Carbon\Carbon::parse($asistencia->fecha_registro)->format('d/m/Y H:i:s') }}
                                                    </dd>

                                                    <dt class="col-sm-4">Tipo Verificación:</dt>
                                                    <dd class="col-sm-8">{{ $asistencia->tipo_verificacion_texto }}</dd>

                                                    <dt class="col-sm-4">Terminal:</dt>
                                                    <dd class="col-sm-8">{{ $asistencia->terminal_id ?? 'N/A' }}</dd>

                                                    <dt class="col-sm-4">SN Dispositivo:</dt>
                                                    <dd class="col-sm-8">{{ $asistencia->sn_dispositivo ?? 'N/A' }}</dd>

                                                    <dt class="col-sm-4">Fecha Registro:</dt>
                                                    <dd class="col-sm-8">
                                                        {{ \Carbon\Carbon::parse($asistencia->fecha_registro)->format('d/m/Y H:i:s') }}
                                                    </dd>

                                                    @if (isset($asistencia->horario) && $asistencia->horario)
                                                        <dt class="col-sm-4">Curso:</dt>
                                                        <dd class="col-sm-8">
                                                            {{ $asistencia->horario->curso->nombre ?? 'N/A' }}</dd>

                                                        <dt class="col-sm-4">Horario:</dt>
                                                        <dd class="col-sm-8">
                                                            {{ \Carbon\Carbon::parse($asistencia->horario->hora_inicio)->format('H:i') }}
                                                            -
                                                            {{ \Carbon\Carbon::parse($asistencia->horario->hora_fin)->format('H:i') }}
                                                        </dd>
                                                    @endif
                                                </dl>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary"
                                                    data-bs-dismiss="modal">Cerrar</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center py-4">
                                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">No se encontraron registros de asistencia de docentes para la
                                            fecha seleccionada.</p>
                                        @can('asistencia-docente.create')
                                            <a href="{{ route('asistencia-docente.create') }}" class="btn btn-primary">
                                                <i class="fas fa-plus"></i> Registrar Primera Asistencia
                                            </a>
                                        @endcan
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Paginación --}}
        @if ($asistencias->hasPages())
            <div class="mt-3">
                {{ $asistencias->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
@endsection

@push('scripts')
    <script>
        // Auto-refresh cada 30 segundos si estamos viendo el día actual
        @if ($fecha === now()->format('Y-m-d'))
            setInterval(function() {
                location.reload();
            }, 30000);
        @endif
    </script>
@endpush
