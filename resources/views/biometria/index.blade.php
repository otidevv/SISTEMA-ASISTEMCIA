@extends('layouts.app')

@section('title', 'Gestión Biométrica')

@push('css')
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.14.5/dist/sweetalert2.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    
    <style>
        :root {
            --cepre-magenta: #e91e63;
            --cepre-navy: #1a237e;
        }
        .device-selector-card {
            border-left: 5px solid var(--cepre-pink);
            background-color: #f8f9fa;
        }
        .biometric-card {
            border-radius: 1rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            border: none;
            margin-bottom: 1.5rem;
        }
        .device-status-dot {
            height: 10px;
            width: 10px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 5px;
        }
        :root {
            --cepre-pink: #ec008c;
            --cepre-dark-blue: #2b5a6f;
        }

        .dot-online { background-color: #28a745; box-shadow: 0 0 5px #28a745; }
        .dot-offline { background-color: #dc3545; }
        
        .biometric-card {
            border-top: 3px solid var(--cepre-pink);
        }

        .enroll-btn {
            transition: all 0.2s ease;
            font-weight: 600;
            padding: 0.4rem 0.8rem;
        }
        .enroll-btn:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 10px rgba(0,0,0,0.15);
        }
        
        .status-badge {
            font-size: 0.8rem;
            padding: 0.5em 1em;
            font-weight: 600;
        }

        /* Colores Vivos Institucionales */
        .bg-cepre-pink { background-color: var(--cepre-pink) !important; color: white !important; }
        .btn-cepre-pink { background-color: var(--cepre-pink); border-color: var(--cepre-pink); color: white; }
        .btn-cepre-pink:hover { background-color: #d6007e; border-color: #d6007e; color: white; }
        
        .badge-pending {
            background-color: #ffc107;
            color: #000;
            border: 1px solid #d39e00;
        }
        .badge-registered {
            background-color: #28a745;
            color: #fff;
            border: 1px solid #1e7e34;
        }
        
        .enroll-btn i {
            font-size: 1rem;
            vertical-align: middle;
        }
        
        .btn-text {
            font-size: 0.85rem;
            margin-left: 5px;
            vertical-align: middle;
        }
    </style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">Módulo de Gestión Biométrica</h4>
            </div>
        </div>
    </div>

    <!-- Panel de Dispositivos -->
    <div class="row">
        @forelse($devices as $device)
        @php 
            $isOnline = $device->last_seen && $device->last_seen->diffInMinutes(now()) < 5;
        @endphp
        <div class="col-md-4">
            <div class="card biometric-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title mb-0">{{ $device->nombre }}</h5>
                        <span class="badge {{ $isOnline ? 'bg-soft-success text-success' : 'bg-soft-danger text-danger' }}">
                            <span class="device-status-dot {{ $isOnline ? 'dot-online' : 'dot-offline' }}"></span>
                            {{ $isOnline ? 'Online' : 'Offline' }}
                        </span>
                    </div>
                    <p class="text-muted mb-1"><i class="uil uil-processor me-1"></i> SN: <strong>{{ $device->sn }}</strong></p>
                    <p class="text-muted mb-1"><i class="uil uil-rss me-1"></i> IP: {{ $device->ip ?? 'Desconocida' }}</p>
                    <p class="text-muted mb-2"><i class="uil uil-clock me-1"></i> Visto: {{ $device->last_seen ? $device->last_seen->diffForHumans() : 'Nunca' }}</p>
                    
                    @if(Auth::user()->hasPermission('biometria.enroll'))
                    <button class="btn btn-sm btn-outline-primary w-100 sync-device-btn" data-sn="{{ $device->sn }}">
                        <i class="mdi mdi-sync me-1"></i> Sincronizar Datos
                    </button>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="alert alert-warning">No hay dispositivos registrados. El sistema los registrará automáticamente cuando se conecten.</div>
        </div>
        @endforelse
    </div>

    <!-- Panel de Usuarios para Enrolamiento -->
    <div class="row">
        <div class="col-12">
            <div class="card biometric-card shadow-sm">
                <div class="card-header bg-white py-3 border-bottom">
                    <div class="row align-items-center">
                        <div class="col-md-3">
                            <h5 class="card-title mb-0 text-dark fw-bold">Enrolamiento de Usuarios</h5>
                        </div>
                        <div class="col-md-9">
                            <div class="row g-2 justify-content-end">
                                <div class="col-md-2">
                                    <label class="form-label small mb-1 fw-bold">Ciclo:</label>
                                    <select id="ciclo_filter" class="form-select form-select-sm filter-select">
                                        <option value="">-- Ciclo --</option>
                                        @foreach($ciclos as $ciclo)
                                            <option value="{{ $ciclo->id }}" {{ isset($activeCiclo) && $activeCiclo->id == $ciclo->id ? 'selected' : '' }}>{{ $ciclo->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small mb-1 fw-bold">Carrera:</label>
                                    <select id="carrera_filter" class="form-select form-select-sm filter-select">
                                        <option value="">-- Carrera --</option>
                                        @foreach($carreras as $carrera)
                                            <option value="{{ $carrera->id }}">{{ $carrera->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small mb-1 fw-bold">Rol:</label>
                                    <select id="role_filter" class="form-select form-select-sm filter-select">
                                        <option value="">-- Rol --</option>
                                        @foreach($roles as $role)
                                            <option value="{{ $role->id }}">{{ ucfirst($role->nombre) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small mb-1 fw-bold">Estado:</label>
                                    <select id="status_filter" class="form-select form-select-sm filter-select">
                                        <option value="">-- Biometría --</option>
                                        <option value="fingerprint_pending">Sin Huella</option>
                                        <option value="face_pending">Sin Rostro</option>
                                        <option value="both_pending">Sin Ninguno</option>
                                        <option value="fingerprint_ok">Con Huella</option>
                                        <option value="face_ok">Con Rostro</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <table id="users-table" class="table table-hover dt-responsive nowrap w-100">
                        <thead>
                            <tr>
                                <th>DNI (ID)</th>
                                <th>Nombre Completo</th>
                                <th>Rol</th>
                                <th>Huella</th>
                                <th>Rostro</th>
                                <th>Acciones de Registro</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Procesando Registro -->
<div class="modal fade" id="modalProcessing" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center p-4">
                <div class="spinner-border text-primary mb-3" role="status"></div>
                <h5>Esperando acción en el equipo...</h5>
                <p id="processing-text">Por favor, coloque el dedo o rostro en el dispositivo ZKTeco ahora.</p>
                <div id="command-result" class="mt-2" style="display:none;"></div>
                <button type="button" class="btn btn-secondary mt-3" data-bs-dismiss="modal">Cancelar</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.14.5/dist/sweetalert2.all.min.js"></script>

    <script>
        $(document).ready(function() {
            // Lista de dispositivos para JS
            const devices = @json($devices->map(function($d) {
                return [
                    'sn' => $d->sn,
                    'nombre' => $d->nombre,
                    'isOnline' => $d->last_seen && $d->last_seen->diffInMinutes(now()) < 5
                ];
            }));

            const table = $('#users-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('biometria.users') }}",
                    data: function(d) {
                        d.ciclo_id = $('#ciclo_filter').val();
                        d.carrera_id = $('#carrera_filter').val();
                        d.role_id = $('#role_filter').val();
                        d.biometric_status = $('#status_filter').val();
                    }
                },
                columns: [
                    { data: 'numero_documento', name: 'numero_documento' },
                    { data: 'nombre_completo', name: 'nombre_completo' },
                    { data: 'rol', name: 'rol' },
                    { 
                        data: 'has_fingerprint', 
                        render: function(hasFp) {
                            if (hasFp.status) {
                                return `
                                    <div class="position-relative">
                                        <span class="badge badge-registered status-badge w-100"><i class="mdi mdi-check-decagram-outline me-1"></i>Registrado</span>
                                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-info" style="z-index: 1;">
                                            ${hasFp.count}
                                        </span>
                                    </div>`;
                            }
                            return '<span class="badge badge-pending status-badge w-100"><i class="mdi mdi-alert-circle-outline me-1"></i>Pendiente</span>';
                        }
                    },
                    { 
                        data: 'has_face', 
                        render: function(hasFace) {
                            if (hasFace.status) {
                                return `
                                    <div class="position-relative">
                                        <span class="badge badge-registered status-badge w-100"><i class="mdi mdi-check-decagram-outline me-1"></i>Registrado</span>
                                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-info" style="z-index: 1;">
                                            ${hasFace.count}
                                        </span>
                                    </div>`;
                            }
                            return '<span class="badge badge-pending status-badge w-100"><i class="mdi mdi-alert-circle-outline me-1"></i>Pendiente</span>';
                        }
                    },
                    { 
                        data: null, 
                        orderable: false,
                        render: function(data) {
                            let buttons = '<div class="btn-group w-100 shadow-sm">';
                            @if(Auth::user()->hasPermission('biometria.enroll'))
                                buttons += `
                                    <button class="btn btn-cepre-pink enroll-btn d-flex align-items-center justify-content-center" data-type="FP" data-id="${data.id}" style="border-right: 1px solid rgba(255,255,255,0.2);">
                                        <i class="mdi mdi-fingerprint"></i> <span class="btn-text">Huella</span>
                                    </button>
                                    <button class="btn btn-info enroll-btn d-flex align-items-center justify-content-center text-white" data-type="FACE" data-id="${data.id}">
                                        <i class="mdi mdi-face-recognition"></i> <span class="btn-text">Rostro</span>
                                    </button>
                                `;
                            @else
                                buttons += '<span class="text-muted small p-2">Sin Permiso</span>';
                            @endif
                            buttons += '</div>';
                            return buttons;
                        }
                    }
                ],
                language: {
                    "sProcessing":     "Procesando...",
                    "sLengthMenu":     "Mostrar _MENU_ registros",
                    "sZeroRecords":    "No se encontraron resultados",
                    "sEmptyTable":     "Ningún dato disponible en esta tabla",
                    "sInfo":           "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
                    "sInfoEmpty":      "Mostrando registros del 0 al 0 de un total de 0 registros",
                    "sInfoFiltered":   "(filtrado de un total de _MAX_ registros)",
                    "sInfoPostFix":    "",
                    "sSearch":         "Buscar:",
                    "sUrl":            "",
                    "sInfoThousands":  ",",
                    "sLoadingRecords": "Cargando...",
                    "oPaginate": {
                        "sFirst":    "Primero",
                        "sLast":     "Último",
                        "sNext":     "Siguiente",
                        "sPrevious": "Anterior"
                    },
                    "oAria": {
                        "sSortAscending":  ": Activar para ordenar la columna de manera ascendente",
                        "sSortDescending": ": Activar para ordenar la columna de manera descendente"
                    }
                }
            });

            $('#ciclo_filter, #carrera_filter, #role_filter, #status_filter').change(function() {
                table.draw();
            });

            $(document).on('click', '.enroll-btn', function() {
                const userId = $(this).data('id');
                const type = $(this).data('type');
                const typeLabel = type === 'fingerprint' ? 'Huella' : 'Rostro';
                
                if (devices.length === 0) {
                    Swal.fire('Error', 'No hay biométricos registrados en el sistema.', 'error');
                    return;
                }

                // Generar opciones para el selector
                let optionsHtml = '';
                devices.forEach(d => {
                    const statusText = d.isOnline ? '<span class="text-success small">(En Línea)</span>' : '<span class="text-danger small">(Desconectado)</span>';
                    optionsHtml += `<option value="${d.sn}">${d.nombre} ${d.isOnline ? '🟢' : '🔴'}</option>`;
                });

                Swal.fire({
                    title: `Registro de ${typeLabel}`,
                    text: 'Selecciona el equipo que entrará en modo registro:',
                    icon: 'question',
                    html: `
                        <div class="mt-3">
                            <label class="form-label small fw-bold">Elija el Biométrico ZKTeco:</label>
                            <select id="swal_device_selector" class="form-select">
                                ${optionsHtml}
                            </select>
                            <div class="mt-2 small text-muted">
                                El alumno debe estar frente al equipo seleccionado.
                            </div>
                        </div>
                    `,
                    showCancelButton: true,
                    confirmButtonText: 'Iniciar Registro',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#ec008c',
                    preConfirm: () => {
                        return document.getElementById('swal_device_selector').value;
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        startEnrollment(userId, result.value, type);
                    }
                });
            });

            $(document).on('click', '.sync-device-btn', function() {
                const sn = $(this).data('sn');
                
                Swal.fire({
                    title: '¿Sincronizar equipo?',
                    text: 'Se solicitarán todos los usuarios, huellas y rostros registrados en el equipo. Esto actualizará el estado en el sistema.',
                    icon: 'info',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, sincronizar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Sincronizando...',
                            text: 'Enviando comandos al equipo. Por favor, espere unos segundos.',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        $.post("{{ route('biometria.sync') }}", {
                            _token: "{{ csrf_token() }}",
                            sn: sn
                        }, function(response) {
                            Swal.close();
                            if (response.success) {
                                Swal.fire('Comandos Enviados', response.message, 'success');
                                // Recargamos la tabla después de unos segundos para ver cambios
                                setTimeout(() => table.ajax.reload(), 5000);
                            } else {
                                Swal.fire('Error', response.message, 'error');
                            }
                        }).fail(function() {
                            Swal.close();
                            Swal.fire('Error', 'No se pudo comunicar con el servidor.', 'error');
                        });
                    }
                });
            });

            function startEnrollment(userId, deviceSn, type) {
                $('#modalProcessing').modal('show');
                $('#command-result').hide();

                $.post("{{ route('biometria.enroll') }}", {
                    _token: "{{ csrf_token() }}",
                    user_id: userId,
                    device_sn: deviceSn,
                    type: type
                }, function(response) {
                    if (response.success) {
                        pollStatus(response.command_id);
                    } else {
                        $('#modalProcessing').modal('hide');
                        Swal.fire('Error', response.message, 'error');
                    }
                });
            }

            function pollStatus(commandId) {
                const interval = setInterval(function() {
                    $.get(`/biometria/status/${commandId}`, function(data) {
                        if (data.status === 'completed') {
                            clearInterval(interval);
                            $('#modalProcessing').modal('hide');
                            Swal.fire('¡Éxito!', 'Biometría registrada correctamente en el dispositivo.', 'success');
                        } else if (data.status === 'error') {
                            clearInterval(interval);
                            $('#modalProcessing').modal('hide');
                            Swal.fire('Error', 'El equipo reportó un error durante el proceso.', 'error');
                        }
                    });
                }, 2000);

                // Timeout de 1 minuto
                setTimeout(() => {
                    clearInterval(interval);
                    if ($('#modalProcessing').hasClass('show')) {
                        $('#modalProcessing').modal('hide');
                        Swal.fire('Tiempo agotado', 'El equipo no respondió a tiempo.', 'warning');
                    }
                }, 60000);
            }
        });
    </script>
@endpush
