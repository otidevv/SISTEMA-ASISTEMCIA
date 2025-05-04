@extends('layouts.app')

@section('title', 'Editar Rol')

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
                            <li class="breadcrumb-item active">Editar Rol</li>
                        </ol>
                    </div>
                    <h4 class="page-title">Editar Rol: {{ $role->nombre }}</h4>
                </div>
            </div>
        </div>
        <!-- end page title -->

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="header-title">Información del Rol</h4>
                        <p class="text-muted font-14">Actualice la información del rol según sea necesario.</p>

                        <form action="{{ route('roles.update', $role->id) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="nombre" class="form-label">Nombre del Rol <span
                                                class="text-danger">*</span></label>
                                        <input type="text" id="nombre" name="nombre"
                                            value="{{ old('nombre', $role->nombre) }}"
                                            class="form-control @error('nombre') is-invalid @enderror" required>
                                        @error('nombre')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label for="descripcion" class="form-label">Descripción</label>
                                        <textarea id="descripcion" name="descripcion" class="form-control @error('descripcion') is-invalid @enderror"
                                            rows="4">{{ old('descripcion', $role->descripcion) }}</textarea>
                                        @error('descripcion')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="is_default"
                                                name="is_default"
                                                {{ old('is_default', $role->is_default) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="is_default">
                                                Rol por defecto para nuevos usuarios
                                            </label>
                                        </div>
                                        <small class="text-muted">Si está marcado, este rol se asignará automáticamente a
                                            los nuevos usuarios.</small>
                                    </div>
                                </div>
                            </div>

                            <div class="text-end">
                                <a href="{{ route('roles.index') }}" class="btn btn-secondary me-1">Cancelar</a>
                                <button type="submit" class="btn btn-primary">Actualizar Rol</button>
                            </div>
                        </form>
                    </div> <!-- end card-body-->
                </div> <!-- end card-->

                @if (Auth::user()->hasPermission('roles.assign_permissions'))
                    <div class="card mt-3">
                        <div class="card-body">
                            <h4 class="header-title">Permisos Asignados</h4>
                            <p class="text-muted font-14">
                                Este rol tiene {{ $role->permissions->count() }} permisos asignados.
                                Para gestionar los permisos de todos los roles, utilice la
                                <a href="{{ route('roles.permisos') }}">página de asignación de permisos</a>.
                            </p>

                            <div class="row">
                                @foreach ($role->permissions->groupBy('modulo') as $modulo => $permisos)
                                    <div class="col-md-4 mb-3">
                                        <div class="card border">
                                            <div class="card-header bg-light">
                                                <h5 class="card-title mb-0 text-capitalize">{{ $modulo }}</h5>
                                            </div>
                                            <div class="card-body">
                                                <ul class="list-group list-group-flush">
                                                    @foreach ($permisos as $permiso)
                                                        <li class="list-group-item d-flex align-items-center">
                                                            <i class="mdi mdi-check-circle text-success me-2"></i>
                                                            {{ $permiso->nombre }}
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div> <!-- end card-body-->
                    </div> <!-- end card-->
                @endif
            </div> <!-- end col -->
        </div>
        <!-- end row -->
    </div> <!-- container -->
@endsection
