@extends('layouts.app')

@section('title', 'Gestión de Roles')

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
                            <li class="breadcrumb-item active">Roles</li>
                        </ol>
                    </div>
                    <h4 class="page-title">Gestión de Roles</h4>
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
                                <h4 class="card-title">Lista de Roles</h4>
                            </div>
                            <div class="col-sm-8">
                                <div class="text-sm-end">
                                    @if (Auth::user()->hasPermission('roles.create'))
                                        <a href="{{ route('roles.create') }}" class="btn btn-primary mb-2">
                                            <i class="mdi mdi-plus-circle me-1"></i> Nuevo Rol
                                        </a>
                                    @endif
                                    @if (Auth::user()->hasPermission('roles.assign_permissions'))
                                        <a href="{{ route('roles.permisos') }}" class="btn btn-info mb-2 ms-1">
                                            <i class="mdi mdi-key-variant me-1"></i> Asignar Permisos
                                        </a>
                                    @endif
                                </div>
                            </div><!-- end col-->
                        </div>

                        <div class="table-responsive">
                            <table class="table table-centered table-nowrap table-striped" id="roles-datatable">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Nombre</th>
                                        <th>Descripción</th>
                                        <th>Permisos</th>
                                        <th>Por defecto</th>
                                        <th>Fecha de Creación</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($roles as $role)
                                        <tr>
                                            <td>{{ $role->id }}</td>
                                            <td>{{ $role->nombre }}</td>
                                            <td>{{ $role->descripcion }}</td>
                                            <td>
                                                <span class="badge bg-info">{{ $role->permissions_count }} permisos</span>
                                            </td>
                                            <td>
                                                @if ($role->is_default)
                                                    <span class="badge bg-success">Sí</span>
                                                @else
                                                    <span class="badge bg-light text-dark">No</span>
                                                @endif
                                            </td>
                                            <td>{{ $role->fecha_creacion }}</td>
                                            <td class="table-action">
                                                @if (Auth::user()->hasPermission('roles.edit'))
                                                    <a href="{{ route('roles.edit', $role->id) }}" class="action-icon">
                                                        <i class="uil uil-edit"></i>
                                                    </a>
                                                @endif

                                                @if (Auth::user()->hasPermission('roles.delete'))
                                                    <form action="{{ route('roles.destroy', $role->id) }}" method="POST"
                                                        class="d-inline delete-form">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="action-icon btn btn-link p-0"
                                                            onclick="return confirm('¿Está seguro de eliminar este rol?')">
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
                            {{ $roles->links() }}
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
            $('#roles-datatable').DataTable({
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
