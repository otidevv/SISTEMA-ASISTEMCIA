@extends('layouts.app')

@section('title', 'Nuevo Curso')

@section('content')
<div class="container">
    <h4 class="mb-3">Registrar Curso</h4>

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Errores:</strong>
            <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <form action="{{ route('cursos.store') }}" method="POST">
        @csrf

        <div class="form-group">
            <label for="nombre">Nombre del Curso</label>
            <input type="text" name="nombre" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="codigo">Código</label>
            <input type="text" name="codigo" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="descripcion">Descripción</label>
            <textarea name="descripcion" class="form-control" rows="3"></textarea>
        </div>

        <button type="submit" class="btn btn-success">Guardar</button>
        <a href="{{ route('cursos.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
@endsection
