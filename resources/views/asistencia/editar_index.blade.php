<!-- asistencia/editar_index.blade.php -->
@extends('layouts.app')

@section('title', 'Buscar Registros para Editar')

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
                            <li class="breadcrumb-item"><a href="{{ route('asistencia.index') }}">Asistencia</a></li>
                            <li class="breadcrumb-item active">Buscar Registros</li>
                        </ol>
                    </div>
                    <h4 class="page-title">Buscar Registros para Editar</h4>
                </div>
            </div>
        </div>
        <!-- end page title -->

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="header-title">Criterios de Búsqueda</h4>

                        @if (session('success'))
                            <div class="alert alert-success">
                                {{ session('success') }}
                            </div>
                        @endif

                        <form action="{{ route('asistencia.editar') }}" method="GET">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="fecha_desde" class="form-label">Fecha Desde</label>
                                        <input type="date" class="form-control" id="fecha_desde" name="fecha_desde"
                                            value="{{ request('fecha_desde', date('Y-m-d', strtotime('-7 days'))) }}">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="fecha_hasta" class="form-label">Fecha Hasta</label>
                                        <input type="date" class="form-control" id="fecha_hasta" name="fecha_hasta"
                                            value="{{ request('fecha_hasta', date('Y-m-d')) }}">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="documento" class="form-label">Número de Documento</label>
                                        <input type="text" class="form-control" id="documento" name="documento"
                                            value="{{ request('documento') }}" placeholder="Ingrese número de documento">
                                    </div>
                                </div>
                            </div>

                            <div class="text-end">
                                <button type="submit" class="btn btn-primary">Buscar</button>
                                <a href="{{ route('asistencia.editar') }}" class="btn btn-secondary">Limpiar</a>
                            </div>
                        </form>

                        @if (request()->has('fecha_desde') || request()->has('fecha_hasta') || request()->has('documento'))
                            <hr>
                            <h4 class="header-title mt-4">Resultados de Búsqueda</h4>

                            <div class="table-responsive">
                                <table class="table table-centered table-nowrap table-striped" id="resultados-datatable">
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
                                        @if (isset($registros) && count($registros) > 0)
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
                                                    <td>
                                                        <a href="{{ route('asistencia.editar.form', $registro->id) }}"
                                                            class="btn btn-sm btn-primary">
                                                            <i class="uil uil-edit"></i> Editar
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr>
                                                <td colspan="8" class="text-center">No se encontraron registros con los
                                                    criterios de búsqueda.</td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>

                            @if (isset($registros) && $registros->hasPages())
                                <div class="pagination justify-content-end mt-3">
                                    {{ $registros->appends(request()->except('page'))->links() }}
                                </div>
                            @endif
                        @endif
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
            // DataTable para los resultados
            $('#resultados-datatable').DataTable({
                "paging": false, // Desactivamos el paginado ya que usamos Laravel Pagination
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
