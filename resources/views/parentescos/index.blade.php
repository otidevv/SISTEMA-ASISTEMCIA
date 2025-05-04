@extends('layouts.app')

@section('title', 'Gestión de Parentescos')

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
                            <li class="breadcrumb-item active">Parentescos</li>
                        </ol>
                    </div>
                    <h4 class="page-title">Gestión de Parentescos</h4>
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
                                <h4 class="card-title">Lista de Parentescos</h4>
                            </div>
                            <div class="col-sm-8">
                                <div class="text-sm-end">
                                    @if (Auth::user()->hasPermission('parentescos.create'))
                                        <a href="{{ route('parentescos.create') }}" class="btn btn-primary mb-2">
                                            <i class="mdi mdi-plus-circle me-1"></i> Nuevo Parentesco
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

                        @if (session('error'))
                            <div class="alert alert-danger">
                                {{ session('error') }}
                            </div>
                        @endif

                        <div class="table-responsive">
                            <table class="table table-centered table-nowrap table-striped" id="parentescos-datatable">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Estudiante</th>
                                        <th>Padre/Madre/Tutor</th>
                                        <th>Tipo</th>
                                        <th>Acceso Portal</th>
                                        <th>Recibe Notificaciones</th>
                                        <th>Contacto Emergencia</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($parentescos as $parentesco)
                                        <tr>
                                            <td>{{ $parentesco->id }}</td>
                                            <td>
                                                {{ $parentesco->estudiante->nombre }}
                                                {{ $parentesco->estudiante->apellido_paterno }}
                                                {{ $parentesco->estudiante->apellido_materno }}
                                            </td>
                                            <td>
                                                {{ $parentesco->padre->nombre }}
                                                {{ $parentesco->padre->apellido_paterno }}
                                                {{ $parentesco->padre->apellido_materno }}
                                            </td>
                                            <td>{{ ucfirst($parentesco->tipo_parentesco) }}</td>
                                            <td>
                                                @if ($parentesco->acceso_portal)
                                                    <span class="badge bg-success">Sí</span>
                                                @else
                                                    <span class="badge bg-danger">No</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($parentesco->recibe_notificaciones)
                                                    <span class="badge bg-success">Sí</span>
                                                @else
                                                    <span class="badge bg-danger">No</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($parentesco->contacto_emergencia)
                                                    <span class="badge bg-success">Sí</span>
                                                @else
                                                    <span class="badge bg-light text-dark">No</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($parentesco->estado)
                                                    <span class="badge bg-success">Activo</span>
                                                @else
                                                    <span class="badge bg-danger">Inactivo</span>
                                                @endif
                                            </td>
                                            <td class="table-action">
                                                @if (Auth::user()->hasPermission('parentescos.edit'))
                                                    <a href="{{ route('parentescos.edit', $parentesco->id) }}"
                                                        class="action-icon">
                                                        <i class="uil uil-edit"></i>
                                                    </a>
                                                @endif

                                                @if (Auth::user()->hasPermission('parentescos.delete'))
                                                    <form action="{{ route('parentescos.destroy', $parentesco->id) }}"
                                                        method="POST" class="d-inline delete-form">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="action-icon btn btn-link p-0"
                                                            onclick="return confirm('¿Está seguro de eliminar este parentesco?')">
                                                            <i class="uil uil-trash-alt"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="pagination justify-content-end mt-3">
                            {{ $parentescos->links() }}
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
            $('#parentescos-datatable').DataTable({
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
