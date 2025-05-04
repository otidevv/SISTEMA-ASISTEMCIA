@extends('layouts.app')

@section('title', 'Asignar Permisos')

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
                            <li class="breadcrumb-item"><a href="{{ route('roles.index') }}">Roles</a></li>
                            <li class="breadcrumb-item active">Asignar Permisos</li>
                        </ol>
                    </div>
                    <h4 class="page-title">Asignación de Permisos por Rol</h4>
                </div>
            </div>
        </div>
        <!-- end page title -->

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="header-title">Matriz de Permisos</h4>
                        <p class="text-muted font-14">
                            Asigne permisos a los diferentes roles del sistema.
                            Marque las casillas para otorgar permisos específicos a cada rol.
                        </p>

                        <form action="{{ route('roles.permisos.update') }}" method="POST">
                            @csrf

                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th style="width: 20%;">Módulo / Permiso</th>
                                            @foreach ($roles as $role)
                                                <th class="text-center">
                                                    {{ $role->nombre }}
                                                </th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($modulos as $modulo => $permisos)
                                            <tr class="table-light">
                                                <th colspan="{{ count($roles) + 1 }}" class="text-capitalize">
                                                    {{ $modulo }}
                                                </th>
                                            </tr>

                                            @foreach ($permisos as $permiso)
                                                <tr>
                                                    <td>{{ $permiso->nombre }}</td>

                                                    @foreach ($roles as $role)
                                                        <td class="text-center">
                                                            <div class="form-check d-inline-block">
                                                                <input type="checkbox" class="form-check-input"
                                                                    id="permiso_{{ $role->id }}_{{ $permiso->id }}"
                                                                    name="permisos[{{ $role->id }}][]"
                                                                    value="{{ $permiso->id }}"
                                                                    {{ in_array($permiso->id, $rolesPermissions[$role->id] ?? []) ? 'checked' : '' }}>
                                                                <label class="form-check-label"
                                                                    for="permiso_{{ $role->id }}_{{ $permiso->id }}"></label>
                                                            </div>
                                                        </td>
                                                    @endforeach
                                                </tr>
                                            @endforeach
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="text-end mt-3">
                                <a href="{{ route('roles.index') }}" class="btn btn-secondary me-1">Cancelar</a>
                                <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                            </div>
                        </form>
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
            // Función para seleccionar todos los permisos de un módulo para un rol específico
            $('.select-all-module').on('click', function() {
                const roleId = $(this).data('role-id');
                const moduleId = $(this).data('module-id');
                const isChecked = $(this).prop('checked');

                $(`.permission-checkbox[data-role-id="${roleId}"][data-module-id="${moduleId}"]`)
                    .prop('checked', isChecked);
            });

            // Función para seleccionar todos los permisos para un rol
            $('.select-all-role').on('click', function() {
                const roleId = $(this).data('role-id');
                const isChecked = $(this).prop('checked');

                $(`.permission-checkbox[data-role-id="${roleId}"]`)
                    .prop('checked', isChecked);
            });
        });
    </script>
@endpush
