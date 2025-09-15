@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-4 text-gray-800">Subir Nuevo Material</h1>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Detalles del Material</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('materiales-academicos.store') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="form-group">
                    <label for="titulo">Título del Material</label>
                    <input type="text" name="titulo" id="titulo" class="form-control" required>
                </div>

                <div class="form-group">
                    <label for="descripcion">Descripción (Opcional)</label>
                    <textarea name="descripcion" id="descripcion" class="form-control" rows="3"></textarea>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="ciclo_id">Ciclo</label>
                            <select name="ciclo_id" id="ciclo_id" class="form-control" required>
                                <option value="">Seleccione un ciclo...</option>
                                {{-- Opciones se cargarán dinámicamente --}}
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="aula_id">Aula</label>
                            <select name="aula_id" id="aula_id" class="form-control" required>
                                <option value="">Seleccione un aula...</option>
                                {{-- Opciones se cargarán dinámicamente --}}
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="curso_id">Curso</label>
                            <select name="curso_id" id="curso_id" class="form-control" required>
                                <option value="">Seleccione un curso...</option>
                                {{-- Opciones se cargarán dinámicamente --}}
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="semana">Semana</label>
                            <input type="number" name="semana" id="semana" class="form-control" min="1" max="20">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="archivo">Archivo</label>
                            <input type="file" name="archivo" id="archivo" class="form-control-file" required>
                        </div>
                    </div>
                </div>

                <hr>

                <button type="submit" class="btn btn-primary">Guardar Material</button>
                <a href="{{ route('materiales-academicos.index') }}" class="btn btn-secondary">Cancelar</a>
            </form>
        </div>
    </div>
</div>
@endsection
