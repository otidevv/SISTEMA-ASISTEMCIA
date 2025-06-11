@extends('layouts.app')

@section('title', 'Editar Curso')

@section('content')
<div class="container">
    <h4 class="mb-3">Editar Curso</h4>

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Errores:</strong>
            <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <form action="{{ route('cursos.update', $curso->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="nombre">Nombre del Curso</label>
            <input type="text" name="nombre" value="{{ $curso->nombre }}" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="codigo">Código</label>
            <input type="text" name="codigo" value="{{ $curso->codigo }}" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="descripcion">Descripción</label>
            <textarea name="descripcion" class="form-control" rows="3">{{ $curso->descripcion }}</textarea>
        </div>

        <button type="submit" class="btn btn-success">Actualizar</button>
        <a href="{{ route('cursos.index') }}" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
@endsection
