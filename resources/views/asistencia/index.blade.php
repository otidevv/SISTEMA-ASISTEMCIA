@extends('layouts.app')

@section('title', 'Registros de Asistencia')

@section('content')
    <!-- Start Content-->
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Registros de Asistencia</li>
                        </ol>
                    </div>
                    <h4 class="page-title">Registros de Asistencia</h4>
                </div>
            </div>
        </div>
        <!-- end page title -->

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row mb-2">
                            <div class="col-sm-4">
                                <h4 class="card-title">Listado de Registros</h4>
                            </div>
                            <div class="col-sm-8">
                                <div class="text-sm-end">
                                    @if (Auth::user()->hasPermission('attendance.register'))
                                        <a href="{{ route('asistencia.registrar') }}" class="btn btn-primary mb-2">
                                            <i class="mdi mdi-plus-circle me-1"></i> Registrar Asistencia
                                        </a>
                                    @endif
                                    @if (Auth::user()->hasPermission('attendance.export'))
                                        <a href="{{ route('asistencia.exportar') }}" class="btn btn-info mb-2 ms-1">
                                            <i class="mdi mdi-export me-1"></i> Exportar
                                        </a>
                                    @endif
                                </div>
                            </div><!-- end col-->
                        </div>

                        @if (session('success'))
                            <div class="alert alert-success">
                                {{ session('success') }}
                            </div>
                        @endif

                        <!-- Filtros -->
                        <div class="row mb-3">
                            <div class="col-12">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <form action="{{ route('asistencia.index') }}" method="GET"
                                            class="row g-3 align-items-center">
                                            <div class="col-md-4">
                                                <label for="fecha" class="form-label">Fecha</label>
                                                <input type="date" class="form-control" id="fecha" name="fecha"
                                                    value="{{ $fecha ?? date('Y-m-d') }}">
                                            </div>
                                            <div class="col-md-4">
                                                <label for="documento" class="form-label">Documento</label>
                                                <input type="text" class="form-control" id="documento" name="documento"
                                                    value="{{ $documento ?? '' }}"
                                                    placeholder="Ingrese número de documento">
                                            </div>
                                            <div class="col-md-4 d-flex align-items-end">
                                                <button type="submit" class="btn btn-primary me-2">Filtrar</button>
                                                <a href="{{ route('asistencia.index') }}"
                                                    class="btn btn-secondary">Limpiar</a>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-centered table-nowrap table-striped" id="asistencia-datatable">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Documento</th>
                                        <th>Estudiante</th>
                                        <th>Fecha y Hora</th>
                                        <th>Tipo</th>
                                        <th>Dispositivo</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($registros as $registro)
                                        <tr>
                                            <td>{{ $registro->id }}</td>
                                            <td>{{ $registro->nro_documento }}</td>
                                            <td>
                                                @if ($registro->usuario)
                                                    {{ $registro->usuario->nombre }}
                                                    {{ $registro->usuario->apellido_paterno }}
                                                @else
                                                    <span class="text-muted">No encontrado</span>
                                                @endif
                                            </td>
                                            <td>{{ $registro->fecha_hora->format('d/m/Y H:i:s') }}</td>
                                            <td>{{ $registro->tipo_verificacion_texto }}</td>
                                            <td>{{ $registro->sn_dispositivo ?: 'N/A' }}</td>
                                            <td>
                                                @if ($registro->estado)
                                                    <span class="badge bg-success">Activo</span>
                                                @else
                                                    <span class="badge bg-danger">Inactivo</span>
                                                @endif
                                            </td>
                                            <td class="table-action">
                                                @if (Auth::user()->hasPermission('attendance.edit'))
                                                    <a href="{{ route('asistencia.editar.form', $registro->id) }}"
                                                        class="action-icon">
                                                        <i class="uil uil-edit"></i>
                                                    </a>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="pagination justify-content-end mt-3">
                            {{ $registros->appends(request()->query())->links() }}
                        </div>
                    </div> <!-- end card-body-->
                </div> <!-- end card-->
            </div> <!-- end col -->
        </div>
        <!-- end row -->
    </div> <!-- container -->
@endsection

@push('js')
    <script>
        $(document).ready(function() {
            // Inicializar DataTable
            $('#asistencia-datatable').DataTable({
                "paging": false, // Desactivamos el paginado de DataTable ya que usamos Laravel Pagination
                "ordering": true,
                "info": false,
                "searching": true,
                "language": {
                    "search": "Buscar:",
                    "zeroRecords": "No se encontraron resultados",
                    "info": "Mostrando _START_ a _END_ de _TOTAL_ registros",
                    "infoEmpty": "Mostrando 0 a 0 de 0 registros",
                    "infoFiltered": "(filtrado de _MAX_ registros totales)"
                }
            });
        });
    </script>
@endpush
