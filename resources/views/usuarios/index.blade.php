@extends('layouts.app')

@section('title', 'Gestión de Usuarios')

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
                            <li class="breadcrumb-item active">Usuarios</li>
                        </ol>
                    </div>
                    <h4 class="page-title">Gestión de Usuarios</h4>
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
                                <h4 class="card-title">Lista de Usuarios</h4>
                            </div>
                            <div class="col-sm-8">
                                <div class="text-sm-end">
                                    @if (Auth::user()->hasPermission('users.create'))
                                        <a href="{{ route('usuarios.create') }}" class="btn btn-primary mb-2">
                                            <i class="mdi mdi-plus-circle me-1"></i> Nuevo Usuario
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
                            <table class="table table-centered table-nowrap table-striped" id="usuarios-datatable">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Usuario</th>
                                        <th>Nombre Completo</th>
                                        <th>Correo</th>
                                        <th>Roles</th>
                                        <th>Estado</th>
                                        <th>Último Acceso</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($usuarios as $usuario)
                                        <tr>
                                            <td>{{ $usuario->id }}</td>
                                            <td>{{ $usuario->username }}</td>
                                            <td>{{ $usuario->full_name }}</td>
                                            <td>{{ $usuario->email }}</td>
                                            <td>
                                                @foreach ($usuario->roles as $role)
                                                    <span class="badge bg-info">{{ $role->nombre }}</span>
                                                @endforeach
                                            </td>
                                            <td>
                                                @if ($usuario->estado)
                                                    <span class="badge bg-success">Activo</span>
                                                @else
                                                    <span class="badge bg-danger">Inactivo</span>
                                                @endif
                                            </td>
                                            <td>{{ $usuario->ultimo_acceso ? $usuario->ultimo_acceso->format('d/m/Y H:i') : 'Nunca' }}
                                            </td>
                                            <td class="table-action">
                                                @if (Auth::user()->hasPermission('users.edit'))
                                                    <a href="{{ route('usuarios.edit', $usuario->id) }}"
                                                        class="action-icon">
                                                        <i class="uil uil-edit"></i>
                                                    </a>
                                                @endif

                                                @if (Auth::user()->hasPermission('users.delete') && $usuario->id != Auth::id())
                                                    <form action="{{ route('usuarios.destroy', $usuario->id) }}"
                                                        method="POST" class="d-inline delete-form">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="action-icon btn btn-link p-0"
                                                            onclick="return confirm('¿Está seguro de desactivar este usuario?')">
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
                            {{ $usuarios->links() }}
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
            $('#usuarios-datatable').DataTable({
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
