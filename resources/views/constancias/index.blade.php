@extends('layouts.app')

@section('content')
<div class="container-fluid">
                    <div class="row">
                        <div class="col-12">
                            <div class="page-title-box">
                                <div class="page-title-right">
                                    <ol class="breadcrumb m-0">
                                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                                        <li class="breadcrumb-item active">Constancias</li>
                                    </ol>
                                </div>
                                <h4 class="page-title">Mis Constancias</h4>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row mb-3">
                                        <div class="col-md-6">
                                            <h4 class="header-title">Constancias Generadas</h4>
                                            <p class="text-muted">Lista de todas las constancias que has generado o que te pertenecen.</p>
                                        </div>
                                        <div class="col-md-6 text-end">
                                            @if(Auth::user()->hasPermission('constancias.generar-estudios') || Auth::user()->hasPermission('constancias.generar-vacante'))
                                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#generarConstanciaModal">
                                                    <i class="mdi mdi-plus"></i> Generar Nueva Constancia
                                                </button>
                                            @endif
                                        </div>
                                    </div>

                                    <table id="constancias-table" class="table table-striped dt-responsive nowrap w-100">
                                        <thead>
                                            <tr>
                                                <th>Tipo</th>
                                                <th>Número</th>
                                                <th>Estudiante</th>
                                                <th>Ciclo</th>
                                                <th>Carrera</th>
                                                <th>Fecha Generación</th>
                                                <th>Estado</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($constancias as $constancia)
                                            <tr>
                                                <td>
                                                    <span class="badge bg-{{ $constancia->tipo == 'estudios' ? 'primary' : 'success' }}">
                                                        {{ ucfirst($constancia->tipo) }}
                                                    </span>
                                                </td>
                                                <td>{{ $constancia->numero_constancia }}</td>
                                                <td>{{ $constancia->estudiante->nombre }} {{ $constancia->estudiante->apellido_paterno }}</td>
                                                <td>{{ $constancia->inscripcion->ciclo->nombre }}</td>
                                                <td>{{ $constancia->inscripcion->carrera->nombre }}</td>
                                                <td>{{ \Carbon\Carbon::parse($constancia->created_at)->format('d/m/Y H:i') }}</td>
                                                <td>
                                                    <span class="badge bg-success">Válida</span>
                                                </td>
                                                <td>
                                                    <div class="btn-group">
                                                        @if(Auth::user()->hasPermission('constancias.view'))
                                                        <a href="{{ route('constancias.estudios.ver', $constancia->id) }}"
                                                           class="btn btn-sm btn-outline-info" target="_blank">
                                                            <i class="mdi mdi-eye"></i> Ver
                                                        </a>
                                                        @endif
                                                        <a href="{{ route('constancias.validar', $constancia->codigo_verificacion) }}"
                                                           class="btn btn-sm btn-outline-primary" target="_blank">
                                                            <i class="mdi mdi-qrcode"></i> Verificar
                                                        </a>
                                                        @if($constancia->constancia_firmada_path)
                                                        <a href="{{ Storage::url($constancia->constancia_firmada_path) }}"
                                                           class="btn btn-sm btn-outline-success" target="_blank">
                                                            <i class="mdi mdi-download"></i> Descargar Firmada
                                                        </a>
                                                        @else
                                                        @if(Auth::user()->hasRole('admin') || Auth::user()->id == $constancia->estudiante_id)
                                                        <button type="button" class="btn btn-sm btn-outline-warning"
                                                                onclick="abrirModalSubir({{ $constancia->id }}, '{{ $constancia->tipo }}')">
                                                            <i class="mdi mdi-upload"></i> Subir Firmada
                                                        </button>
                                                        @endif
                                                        @endif
                                                        @if(Auth::user()->hasRole('admin') || Auth::user()->hasPermission('constancias.eliminar'))
                                                        <button type="button" class="btn btn-sm btn-outline-danger"
                                                                onclick="eliminarConstancia({{ $constancia->id }})">
                                                            <i class="mdi mdi-delete"></i> Eliminar
                                                        </button>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
@endsection

@push('modals')
<!-- Modal para generar nueva constancia -->
<div class="modal fade" id="generarConstanciaModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Generar Nueva Constancia</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card text-center">
                            <div class="card-body">
                                <i class="mdi mdi-school mdi-48px text-primary"></i>
                                <h5 class="mt-3">Constancia de Estudios</h5>
                                <p class="text-muted">Genera una constancia de estudios para estudiantes inscritos</p>
                                <a href="#" class="btn btn-primary" onclick="seleccionarTipo('estudios')">Seleccionar</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card text-center">
                            <div class="card-body">
                                <i class="mdi mdi-trophy mdi-48px text-success"></i>
                                <h5 class="mt-3">Constancia de Vacante</h5>
                                <p class="text-muted">Genera una constancia de vacante para estudiantes inscritos</p>
                                <a href="#" class="btn btn-success" onclick="seleccionarTipo('vacante')">Seleccionar</a>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="seleccion-inscripcion" style="display: none;">
                    <hr>
                    <h6>Seleccionar Inscripción</h6>

                    <!-- Seleccionar Ciclo Académico -->
                    <div class="mb-3">
                        <label for="ciclo-select" class="form-label">Seleccionar Ciclo Académico:</label>
                        <select id="ciclo-select" class="form-select">
                            <option value="">Todos los ciclos...</option>
                        </select>
                    </div>

                    <!-- Campo de búsqueda por DNI -->
                    <div class="mb-3">
                        <label for="dni-search" class="form-label">Buscar por DNI del estudiante:</label>
                        <div class="input-group">
                            <input type="text" id="dni-search" class="form-control" placeholder="Ingresa DNI..." maxlength="8">
                            <button type="button" class="btn btn-outline-secondary" onclick="buscarPorDNI()">
                                <i class="mdi mdi-magnify"></i> Buscar
                            </button>
                        </div>
                    </div>

                    <select id="inscripcion-select" class="form-select">
                        <option value="">Selecciona una inscripción...</option>
                    </select>
                    <div class="mt-3">
                        <button type="button" class="btn btn-secondary" onclick="volverSeleccion()">Volver</button>
                        <button type="button" class="btn btn-primary" id="generar-btn" onclick="generarConstancia()">Generar Constancia</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Modal para subir constancia firmada -->
<div class="modal fade" id="subirConstanciaModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Subir Constancia Firmada</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="subirConstanciaForm" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" id="constancia-id" name="constancia_id">
                    <input type="hidden" id="constancia-tipo" name="tipo">

                    <div class="mb-3">
                        <label for="constancia_firmada" class="form-label">Seleccionar archivo PDF firmado:</label>
                        <input type="file" class="form-control" id="constancia_firmada" name="constancia_firmada"
                               accept=".pdf" required>
                        <div class="form-text">Solo se permiten archivos PDF con un tamaño máximo de 5MB.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Subir Constancia</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endpush

@push('js')
<script>
    $(document).ready(function() {
        $('#constancias-table').DataTable({
            responsive: true,
            language: {
                url: '//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json'
            }
        });
    });

    let tipoSeleccionado = '';

    function seleccionarTipo(tipo) {
        tipoSeleccionado = tipo;
        $('.card').hide();
        $('#seleccion-inscripcion').show();

        // Cargar ciclos disponibles
        cargarCiclos();

        // Cargar inscripciones disponibles
        cargarInscripciones(tipo);
    }

    function volverSeleccion() {
        $('#seleccion-inscripcion').hide();
        $('.card').show();
        tipoSeleccionado = '';
    }

    function cargarCiclos() {
        // Mostrar indicador de carga
        $('#ciclo-select').html('<option value="">Cargando ciclos...</option>');

        // Hacer una llamada AJAX para cargar los ciclos disponibles
        $.ajax({
            url: '{{ route("json.ciclos-disponibles") }}',
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            success: function(response) {
                let options = '<option value="">Todos los ciclos...</option>';
                if (response.length > 0) {
                    response.forEach(function(ciclo) {
                        const cicloInfo = `${ciclo.nombre} (${ciclo.fecha_inicio} - ${ciclo.fecha_fin})`;
                        options += `<option value="${ciclo.id}">${cicloInfo}</option>`;
                    });
                }
                $('#ciclo-select').html(options);
            },
            error: function(xhr, status, error) {
                console.error('Error al cargar ciclos:', xhr.responseText);
                $('#ciclo-select').html('<option value="">Error al cargar ciclos</option>');
            }
        });
    }

    function cargarInscripciones(tipo, dni = null, cicloId = null) {
        // Mostrar indicador de carga
        $('#inscripcion-select').html('<option value="">Cargando...</option>');

        // Hacer una llamada AJAX para cargar las inscripciones disponibles
        $.ajax({
            url: '{{ route("json.inscripciones") }}',
            method: 'GET',
            data: {
                tipo: tipo,
                dni: dni,
                ciclo_id: cicloId
            },
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            success: function(response) {
                let options = '<option value="">Selecciona una inscripción...</option>';
                if (response.length > 0) {
                    response.forEach(function(inscripcion) {
                        const cicloInfo = `${inscripcion.ciclo.nombre} (${inscripcion.ciclo.fecha_inicio} - ${inscripcion.ciclo.fecha_fin})`;
                        options += `<option value="${inscripcion.id}">
                            ${inscripcion.estudiante.nombre} ${inscripcion.estudiante.apellido_paterno} ${inscripcion.estudiante.apellido_materno} -
                            ${inscripcion.carrera.nombre} (${cicloInfo})
                        </option>`;
                    });
                } else {
                    options = '<option value="">No se encontraron inscripciones</option>';
                }
                $('#inscripcion-select').html(options);
            },
            error: function(xhr, status, error) {
                console.error('Error al cargar inscripciones:', xhr.responseText);
                let errorMsg = 'Error al cargar inscripciones';
                if (xhr.status === 419) {
                    errorMsg = 'Sesión expirada. Recarga la página.';
                } else if (xhr.status === 403) {
                    errorMsg = 'No tienes permisos para ver estas inscripciones.';
                }
                $('#inscripcion-select').html(`<option value="">${errorMsg}</option>`);
            }
        });
    }

    function buscarPorDNI() {
        const dni = $('#dni-search').val().trim();
        if (dni.length === 8) {
            cargarInscripciones(tipoSeleccionado, dni);
        } else {
            alert('Por favor ingresa un DNI válido (8 dígitos)');
        }
    }

    // Filtrar por ciclo cuando se selecciona uno
    $('#ciclo-select').change(function() {
        const cicloId = $(this).val();
        cargarInscripciones(tipoSeleccionado, null, cicloId);
    });

    // Permitir búsqueda al presionar Enter
    $('#dni-search').keypress(function(e) {
        if (e.which === 13) { // Enter key
            buscarPorDNI();
        }
    });

        function generarConstancia() {
            const inscripcionId = $('#inscripcion-select').val();
            console.log('ID de inscripción seleccionado:', inscripcionId);
            console.log('Tipo seleccionado:', tipoSeleccionado);

            if (!inscripcionId) {
                alert('Por favor selecciona una inscripción');
                return;
            }

            let route = '';
            if (tipoSeleccionado === 'estudios') {
                route = '{{ route("constancias.estudios.generar", ":id") }}'.replace(':id', inscripcionId);
            } else {
                route = '{{ route("constancias.vacante.generar", ":id") }}'.replace(':id', inscripcionId);
            }

            console.log('URL generada:', route);
            window.open(route, '_blank');
            $('#generarConstanciaModal').modal('hide');
        }

        function eliminarConstancia(constanciaId) {
            if (confirm('¿Estás seguro de que deseas eliminar esta constancia? Esta acción no se puede deshacer.')) {
                $.ajax({
                    url: '{{ route("constancias.eliminar", ":id") }}'.replace(':id', constanciaId),
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        alert('Constancia eliminada correctamente');
                        location.reload();
                    },
                    error: function(xhr, status, error) {
                        console.error('Error al eliminar constancia:', xhr.responseText);
                        let errorMsg = 'Error al eliminar la constancia';
                        if (xhr.status === 403) {
                            errorMsg = 'No tienes permisos para eliminar esta constancia';
                        } else if (xhr.status === 404) {
                            errorMsg = 'Constancia no encontrada';
                        }
                        alert(errorMsg);
                    }
                });
            }
        }

        function abrirModalSubir(constanciaId, tipo) {
            $('#constancia-id').val(constanciaId);
            $('#constancia-tipo').val(tipo);
            $('#constancia_firmada').val(''); // Limpiar el input file
            $('#subirConstanciaModal').modal('show');
        }

        // Manejar el envío del formulario de subir constancia
        $('#subirConstanciaForm').on('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const constanciaId = $('#constancia-id').val();
            const tipo = $('#constancia-tipo').val();

            // Determinar la ruta según el tipo
            let route = '';
            if (tipo === 'estudios') {
                route = '{{ route("constancias.estudios.subir-firmada", ":id") }}'.replace(':id', constanciaId);
            } else {
                route = '{{ route("constancias.vacante.subir-firmada", ":id") }}'.replace(':id', constanciaId);
            }

            $.ajax({
                url: route,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                success: function(response) {
                    alert('Constancia firmada subida correctamente');
                    $('#subirConstanciaModal').modal('hide');
                    location.reload();
                },
                error: function(xhr, status, error) {
                    console.error('Error al subir constancia:', xhr.responseText);
                    let errorMsg = 'Error al subir la constancia';
                    if (xhr.status === 422) {
                        const errors = xhr.responseJSON.errors;
                        if (errors.constancia_firmada) {
                            errorMsg = errors.constancia_firmada[0];
                        }
                    } else if (xhr.status === 403) {
                        errorMsg = 'No tienes permisos para subir esta constancia';
                    } else if (xhr.status === 413) {
                        errorMsg = 'El archivo es demasiado grande';
                    }
                    alert(errorMsg);
                }
            });
        });
</script>
@endpush
