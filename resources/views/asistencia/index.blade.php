@extends('layouts.app')

@section('title', 'Registros de Asistencia')

@push('css')
<style>
    .search-container {
        position: relative;
    }
    .search-input {
        padding-right: 40px;
    }
    .search-icon {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #6c757d;
        pointer-events: none;
    }
    .suggestions-dropdown {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 1px solid #dee2e6;
        border-top: none;
        border-radius: 0 0 0.25rem 0.25rem;
        max-height: 300px;
        overflow-y: auto;
        display: none;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        z-index: 1050;
    }
    .suggestion-item {
        padding: 10px 15px;
        cursor: pointer;
        transition: background-color 0.2s ease;
        border-bottom: 1px solid #f1f3f4;
    }
    .suggestion-item:last-child {
        border-bottom: none;
    }
    .suggestion-item:hover,
    .suggestion-item.active {
        background-color: #f8f9fa;
    }
    .pagination {
        display: flex;
        list-style: none;
        padding-left: 0;
    }
    .pagination .page-item .page-link {
        border-radius: 0.375rem;
        margin: 0 2px;
        color: #6c757d;
        padding: 6px 12px;
        font-weight: 500;
    }
    .pagination .page-item.active .page-link {
        background-color: #0d6efd;
        border-color: #0d6efd;
        color: #fff;
    }
    .pagination .page-item.disabled .page-link {
        pointer-events: none;
        background-color: #fff;
        color: #6c757d;
        border-color: #dee2e6;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex justify-content-between align-items-center">
                <h4 class="page-title">Registros de Asistencia</h4>
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Registros de Asistencia</li>
                </ol>
            </div>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-body">

            <div class="d-flex justify-content-between mb-3">
                <h4 class="header-title">Listado de Registros</h4>
                <div>
                    @if (Auth::user()->hasPermission('attendance.register'))
                        <a href="{{ route('asistencia.registrar') }}" class="btn btn-primary">
                            <i class="mdi mdi-plus-circle me-1"></i> Registrar
                        </a>
                    @endif
                    @if (Auth::user()->hasPermission('attendance.export'))
                        <a href="{{ route('asistencia.exportar') }}" class="btn btn-info ms-2">
                            <i class="mdi mdi-export me-1"></i> Exportar
                        </a>
                    @endif
                </div>
            </div>

            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <form action="{{ route('asistencia.index') }}" method="GET" class="row gy-2 gx-3 align-items-center mb-4">
                <div class="col-md-4">
                    <label for="fecha" class="form-label">Fecha</label>
                    <input type="date" class="form-control" id="fecha" name="fecha" value="{{ $fecha ?? date('Y-m-d') }}">
                </div>
                <div class="col-md-4">
                    <label for="documento" class="form-label">Documento</label>
                    <div class="search-container">
                        <input type="text" class="form-control search-input" id="documento" name="documento"
                            value="{{ $documento ?? '' }}" placeholder="Ingrese número de documento o nombre" autocomplete="off">
                        <i class="fas fa-search search-icon"></i>
                        <div class="suggestions-dropdown" id="suggestions"></div>
                    </div>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">Filtrar</button>
                    <a href="{{ route('asistencia.index') }}" class="btn btn-secondary">Limpiar</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-hover table-striped align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Documento</th>
                            <th>Estudiante</th>
                            <th>Fecha y Hora</th>
                            <th>Tipo</th>
                            <th>Dispositivo</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($registros as $registro)
                            <tr>
                                <td>{{ $registro->id }}</td>
                                <td>{{ $registro->nro_documento }}</td>
                                <td>
                                    @if ($registro->usuario)
                                        {{ $registro->usuario->nombre }} {{ $registro->usuario->apellido_paterno }}
                                    @else
                                        <span class="text-muted">No encontrado</span>
                                    @endif
                                </td>
                                <td>
                                    @if($registro->tipo_verificacion == 4)
                                        {{ \Carbon\Carbon::parse($registro->fecha_hora)->format('d/m/Y H:i:s') }}
                                    @else
                                        {{ $registro->fecha_registro->format('d/m/Y H:i:s') }}
                                    @endif
                                </td>
                                <td>{{ $registro->tipo_verificacion_texto }}</td>
                                <td>{{ $registro->sn_dispositivo ?: 'N/A' }}</td>
                                <td>
                                    <span class="badge {{ $registro->estado ? 'bg-success' : 'bg-danger' }}">
                                        {{ $registro->estado ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </td>
                                <td>
                                    @if (Auth::user()->hasPermission('attendance.edit'))
                                        <a href="{{ route('asistencia.editar.form', $registro->id) }}" class="text-primary">
                                            <i class="uil uil-edit"></i>
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end mt-3">
                <nav>
                    {{ $registros->appends(request()->query())->onEachSide(1)->links('pagination::bootstrap-5') }}
                </nav>
            </div>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
    const estudiantes = @json($usuarios);

    const searchInput = document.getElementById('documento');
    const suggestionsContainer = document.getElementById('suggestions');

    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const filtered = estudiantes.filter(est => {
            const fullName = `${est.nombre} ${est.apellido_paterno}`.toLowerCase();
            return fullName.includes(searchTerm) || est.numero_documento.includes(searchTerm);
        });

        if (filtered.length === 0 || searchTerm.length === 0) {
            suggestionsContainer.style.display = 'none';
            return;
        }

        let html = '';
        filtered.slice(0, 10).forEach(est => {
            html += `<div class="suggestion-item">${est.nombre} ${est.apellido_paterno} - ${est.numero_documento}</div>`;
        });

        suggestionsContainer.innerHTML = html;
        suggestionsContainer.style.display = 'block';

        document.querySelectorAll('.suggestion-item').forEach(item => {
            item.addEventListener('click', function() {
                searchInput.value = this.textContent.split(' - ')[1];
                suggestionsContainer.style.display = 'none';
            });
        });
    });

    document.addEventListener('click', function(e) {
        if (!e.target.closest('.search-container')) {
            suggestionsContainer.style.display = 'none';
        }
    });
</script>
@endpush
