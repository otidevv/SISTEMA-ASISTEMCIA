@extends('layouts.app')

@section('title', 'Editar Material Académico')

@section('content')
    <!-- start page title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">Editar Material Académico</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('materiales-academicos.index') }}">Material Académico</a></li>
                        <li class="breadcrumb-item active">Editar</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <!-- end page title -->

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="header-title">Formulario de Edición</h4>
                    <p class="text-muted font-14">
                        Modifique los campos necesarios para actualizar el material académico.
                    </p>

                    <form action="{{ route('materiales-academicos.update', $materialAcademico) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="titulo" class="form-label">Título</label>
                                <input type="text" class="form-control" id="titulo" name="titulo" value="{{ $materialAcademico->titulo }}" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="curso_id" class="form-label">Curso</label>
                                <select class="form-select" id="curso_id" name="curso_id" required>
                                    <option value="">Seleccione un curso</option>
                                    @foreach ($cursos as $curso)
                                        <option value="{{ $curso->id }}" @if($materialAcademico->curso_id == $curso->id) selected @endif>{{ $curso->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="descripcion" class="form-label">Descripción</label>
                            <textarea class="form-control" id="descripcion" name="descripcion" rows="3">{{ $materialAcademico->descripcion }}</textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="ciclo_id" class="form-label">Ciclo</label>
                                <select class="form-select" id="ciclo_id" name="ciclo_id" required>
                                    <option value="">Seleccione un ciclo</option>
                                    @foreach ($ciclos as $ciclo)
                                        <option value="{{ $ciclo->id }}" @if($materialAcademico->ciclo_id == $ciclo->id) selected @endif>{{ $ciclo->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="aula_id" class="form-label">Aula</label>
                                <select class="form-select" id="aula_id" name="aula_id" required>
                                    <option value="">Seleccione un aula</option>
                                    @foreach ($aulas as $aula)
                                        <option value="{{ $aula->id }}" @if($materialAcademico->aula_id == $aula->id) selected @endif>{{ $aula->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="semana" class="form-label">Semana</label>
                                <input type="number" class="form-control" id="semana" name="semana" min="1" max="20" value="{{ $materialAcademico->semana }}" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="tipo" class="form-label">Tipo de Material</label>
                                <select class="form-select" id="tipo" name="tipo" required>
                                    <option value="pdf" @if($materialAcademico->tipo == 'pdf') selected @endif>PDF</option>
                                    <option value="word" @if($materialAcademico->tipo == 'word') selected @endif>Documento de Word</option>
                                    <option value="ppt" @if($materialAcademico->tipo == 'ppt') selected @endif>Presentación PPT</option>
                                    <option value="link" @if($materialAcademico->tipo == 'link') selected @endif>Enlace Web</option>
                                    <option value="otro" @if($materialAcademico->tipo == 'otro') selected @endif>Otro</option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="archivo" class="form-label">Archivo o Enlace (Opcional, dejar en blanco para no cambiar)</label>
                                <input type="file" class="form-control @if($materialAcademico->tipo === 'link') d-none @endif" id="archivo-input" name="archivo">
                                <input type="text" class="form-control @if($materialAcademico->tipo !== 'link') d-none @endif" id="link-input" name="link" placeholder="https://ejemplo.com" value="{{ $materialAcademico->tipo === 'link' ? $materialAcademico->archivo : '' }}">
                                @if($materialAcademico->tipo !== 'link' && $materialAcademico->archivo)
                                    <small class="form-text text-muted">Archivo actual: <a href="{{ asset('storage/' . $materialAcademico->archivo) }}" target="_blank">ver archivo</a></small>
                                @endif
                            </div>
                        </div>

                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary">Actualizar Material</button>
                            <a href="{{ route('materiales-academicos.index') }}" class="btn btn-secondary">Cancelar</a>
                        </div>
                    </form>
                </div> <!-- end card-body -->
            </div> <!-- end card -->
        </div><!-- end col -->
    </div>
@endsection

@push('js')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const tipoSelect = document.getElementById('tipo');
        const archivoInput = document.getElementById('archivo-input');
        const linkInput = document.getElementById('link-input');

        function toggleInputs() {
            const tipo = tipoSelect.value;
            if (tipo === 'link') {
                archivoInput.classList.add('d-none');
                linkInput.classList.remove('d-none');
                archivoInput.name = '';
                linkInput.name = 'archivo';
            } else {
                archivoInput.classList.remove('d-none');
                linkInput.classList.add('d-none');
                archivoInput.name = 'archivo';
                linkInput.name = '';
            }
        }

        toggleInputs(); // Set initial state

        tipoSelect.addEventListener('change', toggleInputs);
    });
</script>
@endpush
