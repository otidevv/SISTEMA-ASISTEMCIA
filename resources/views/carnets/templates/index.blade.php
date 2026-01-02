@extends('layouts.app')

@section('title', 'Plantillas de Carnets')

@push('css')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">
    <style>
        .template-card {
            transition: transform 0.3s, box-shadow 0.3s;
            cursor: pointer;
        }
        .template-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
        }
        .template-preview {
            height: 200px;
            background: #f8f9fa;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        .template-preview img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }
        .badge-active {
            background: #28a745;
            color: white;
        }
        .badge-inactive {
            background: #6c757d;
            color: white;
        }
    </style>
@endpush

@section('content')
    <!-- Page Title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">Plantillas de Carnets</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('carnets.index') }}">Carnets</a></li>
                        <li class="breadcrumb-item active">Plantillas</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="row mb-3">
        <div class="col-12">
            @if(Auth::user()->hasPermission('carnets.templates.create'))
                <a href="{{ route('carnets.templates.create') }}" class="btn btn-primary">
                    <i class="uil uil-plus-circle me-1"></i> Nueva Plantilla
                </a>
            @endif
        </div>
    </div>

    <!-- Templates Grid -->
    <div class="row">
        @forelse($templates as $template)
            <div class="col-md-4 col-lg-3 mb-4">
                <div class="card template-card h-100">
                    <div class="card-body">
                        <!-- Preview -->
                        <div class="template-preview mb-3">
                            @if($template->fondo_path && Storage::disk('public')->exists($template->fondo_path))
                                <img src="{{ asset('storage/' . $template->fondo_path) }}" alt="{{ $template->nombre }}">
                            @else
                                <div class="text-center text-muted">
                                    <i class="uil uil-image-slash" style="font-size: 48px;"></i>
                                    <p class="mb-0">Sin imagen</p>
                                </div>
                            @endif
                        </div>

                        <!-- Template Info -->
                        <h5 class="card-title mb-2">{{ $template->nombre }}</h5>
                        
                        <div class="mb-2">
                            <span class="badge {{ $template->activa ? 'badge-active' : 'badge-inactive' }}">
                                {{ $template->activa ? 'Activa' : 'Inactiva' }}
                            </span>
                            <span class="badge bg-info text-white ms-1">
                                {{ ucfirst($template->tipo) }}
                            </span>
                        </div>

                        @if($template->descripcion)
                            <p class="text-muted small mb-3">{{ Str::limit($template->descripcion, 80) }}</p>
                        @endif

                        <!-- Dimensions -->
                        <p class="text-muted small mb-3">
                            <i class="uil uil-ruler-combined me-1"></i>
                            {{ $template->ancho_mm }} x {{ $template->alto_mm }} mm
                        </p>

                        <!-- Actions -->
                        <div class="btn-group w-100" role="group">
                            @if(Auth::user()->hasPermission('carnets.templates.edit'))
                                <a href="{{ route('carnets.templates.edit', $template->id) }}" 
                                   class="btn btn-sm btn-outline-primary" 
                                   title="Editar">
                                    <i class="uil uil-edit"></i>
                                </a>
                            @endif

                            @if(Auth::user()->hasPermission('carnets.templates.activate') && !$template->activa)
                                <button type="button" 
                                        class="btn btn-sm btn-outline-success btn-activate" 
                                        data-id="{{ $template->id }}"
                                        title="Activar">
                                    <i class="uil uil-check-circle"></i>
                                </button>
                            @endif

                            @if(Auth::user()->hasPermission('carnets.templates.delete') && !$template->activa)
                                <button type="button" 
                                        class="btn btn-sm btn-outline-danger btn-delete" 
                                        data-id="{{ $template->id }}"
                                        title="Eliminar">
                                    <i class="uil uil-trash"></i>
                                </button>
                            @endif
                        </div>

                        <!-- Created By -->
                        @if($template->creador)
                            <p class="text-muted small mt-3 mb-0">
                                <i class="uil uil-user me-1"></i>
                                Creado por {{ $template->creador->nombre }}
                            </p>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="uil uil-file-slash" style="font-size: 64px; color: #ccc;"></i>
                        <h4 class="mt-3">No hay plantillas disponibles</h4>
                        <p class="text-muted">Crea tu primera plantilla para comenzar</p>
                        @if(Auth::user()->hasPermission('carnets.templates.create'))
                            <a href="{{ route('carnets.templates.create') }}" class="btn btn-primary mt-2">
                                <i class="uil uil-plus-circle me-1"></i> Crear Plantilla
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @endforelse
    </div>
@endsection

@push('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script>
        $(document).ready(function() {
            // Activar plantilla
            $('.btn-activate').click(function() {
                const templateId = $(this).data('id');
                
                Swal.fire({
                    title: '¿Activar esta plantilla?',
                    text: 'Se desactivarán las demás plantillas del mismo tipo',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Sí, activar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `/carnets/plantillas/${templateId}/activar`,
                            type: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                if (response.success) {
                                    toastr.success(response.message);
                                    setTimeout(() => location.reload(), 1000);
                                } else {
                                    toastr.error(response.message);
                                }
                            },
                            error: function(xhr) {
                                toastr.error('Error al activar la plantilla');
                            }
                        });
                    }
                });
            });

            // Eliminar plantilla
            $('.btn-delete').click(function() {
                const templateId = $(this).data('id');
                
                Swal.fire({
                    title: '¿Eliminar esta plantilla?',
                    text: 'Esta acción no se puede deshacer',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `/carnets/plantillas/${templateId}`,
                            type: 'DELETE',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                if (response.success) {
                                    toastr.success(response.message);
                                    setTimeout(() => location.reload(), 1000);
                                } else {
                                    toastr.error(response.message);
                                }
                            },
                            error: function(xhr) {
                                toastr.error('Error al eliminar la plantilla');
                            }
                        });
                    }
                });
            });

            // Mostrar mensajes de sesión
            @if(session('success'))
                toastr.success('{{ session('success') }}');
            @endif

            @if(session('error'))
                toastr.error('{{ session('error') }}');
            @endif
        });
    </script>
@endpush
