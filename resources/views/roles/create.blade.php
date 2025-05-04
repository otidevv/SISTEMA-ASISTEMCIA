@extends('layouts.app')

@section('title', 'Crear Rol')

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
                            <li class="breadcrumb-item active">Crear Rol</li>
                        </ol>
                    </div>
                    <h4 class="page-title">Crear Nuevo Rol</h4>
                </div>
            </div>
        </div>
        <!-- end page title -->

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h4 class="header-title">Información del Rol</h4>
                        <p class="text-muted font-14">Complete el formulario para crear un nuevo rol en el sistema.</p>

                        <form action="{{ route('roles.store') }}" method="POST">
                            @csrf

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="nombre" class="form-label">Nombre del Rol <span
                                                class="text-danger">*</span></label>
                                        <input type="text" id="nombre" name="nombre" value="{{ old('nombre') }}"
                                            class="form-control @error('nombre') is-invalid @enderror" required>
                                        @error('nombre')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="text-muted">El nombre debe ser único y describe la función del
                                            rol.</small>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label for="descripcion" class="form-label">Descripción</label>
                                        <textarea id="descripcion" name="descripcion" class="form-control @error('descripcion') is-invalid @enderror"
                                            rows="4">{{ old('descripcion') }}</textarea>
                                        @error('descripcion')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="text-muted">Describa las responsabilidades y alcance de este
                                            rol.</small>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="is_default"
                                                name="is_default" {{ old('is_default') ? 'checked' : '' }}>
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
                                <button type="submit" class="btn btn-primary">Guardar Rol</button>
                            </div>
                        </form>
                    </div> <!-- end card-body-->
                </div> <!-- end card-->
            </div> <!-- end col -->
        </div>
        <!-- end row -->
    </div> <!-- container -->
@endsection
