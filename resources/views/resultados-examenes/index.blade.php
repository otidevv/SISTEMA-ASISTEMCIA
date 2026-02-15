@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Page Title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Resultados de Exámenes</li>
                    </ol>
                </div>
                <h4 class="page-title">Gestión de Resultados de Exámenes</h4>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row mb-2">
                        <div class="col-sm-4">
                            <a href="{{ route('resultados-examenes.create') }}" class="btn btn-primary mb-2">
                                <i class="mdi mdi-plus-circle me-1"></i> Nuevo Resultado
                            </a>
                        </div>
                        <div class="col-sm-8">
                            <div class="text-sm-end">
                                <button type="button" class="btn btn-success mb-2 me-1" id="refreshBtn">
                                    <i class="mdi mdi-refresh"></i> Actualizar
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table id="resultados-table" class="table table-centered table-striped dt-responsive nowrap w-100">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Ciclo</th>
                                    <th>Nombre del Examen</th>
                                    <th>Fecha Examen</th>
                                    <th>Tipo</th>
                                    <th>Estado</th>
                                    <th>Fecha Publicación</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($resultados as $resultado)
                                <tr>
                                    <td>{{ $resultado->id }}</td>
                                    <td>
                                        <span class="badge bg-info">{{ $resultado->ciclo->nombre }}</span>
                                    </td>
                                    <td>
                                        <strong>{{ $resultado->nombre_examen }}</strong>
                                        @if($resultado->descripcion)
                                        <br><small class="text-muted">{{ Str::limit($resultado->descripcion, 50) }}</small>
                                        @endif
                                    </td>
                                    <td>{{ $resultado->fecha_examen->format('d/m/Y') }}</td>
                                    <td>
                                        @if($resultado->tipo_resultado === 'pdf')
                                            <span class="badge bg-danger"><i class="mdi mdi-file-pdf"></i> PDF</span>
                                        @elseif($resultado->tipo_resultado === 'link')
                                            <span class="badge bg-primary"><i class="mdi mdi-link"></i> Link</span>
                                        @else
                                            <span class="badge bg-success"><i class="mdi mdi-file-link"></i> Ambos</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input toggle-visibility" 
                                                   type="checkbox" 
                                                   data-id="{{ $resultado->id }}"
                                                   {{ $resultado->visible ? 'checked' : '' }}>
                                            <label class="form-check-label">
                                                <span class="badge bg-{{ $resultado->visible ? 'success' : 'secondary' }}">
                                                    {{ $resultado->visible ? 'Publicado' : 'Borrador' }}
                                                </span>
                                            </label>
                                        </div>
                                    </td>
                                    <td>
                                        @if($resultado->fecha_publicacion)
                                            {{ $resultado->fecha_publicacion->format('d/m/Y H:i') }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            @if($resultado->tiene_pdf)
                                            <button type="button" 
                                               onclick="openResourceModal('{{ route('resultados-examenes.view', $resultado->id) }}', '{{ addslashes($resultado->nombre_examen) }}', false)"
                                               class="btn btn-sm btn-info" 
                                               title="Ver PDF">
                                                <i class="mdi mdi-eye"></i>
                                            </button>
                                            @endif
                                            
                                            @if($resultado->tiene_link)
                                            <button type="button" 
                                               onclick="openResourceModal('{{ $resultado->link_externo }}', '{{ addslashes($resultado->nombre_examen) }}', true)"
                                               class="btn btn-sm btn-primary" 
                                               title="Ver Link">
                                                <i class="mdi mdi-open-in-new"></i>
                                            </button>
                                            @endif
                                            
                                            @can('resultados-examenes.edit')
                                            <a href="{{ route('resultados-examenes.edit', $resultado->id) }}" 
                                               class="btn btn-sm btn-warning" 
                                               title="Editar">
                                                <i class="mdi mdi-pencil"></i>
                                            </a>
                                            @endcan
                                            
                                            @can('resultados-examenes.delete')
                                            <button type="button" 
                                                    class="btn btn-sm btn-danger delete-btn" 
                                                    data-id="{{ $resultado->id }}"
                                                    data-nombre="{{ $resultado->nombre_examen }}"
                                                    title="Eliminar">
                                                <i class="mdi mdi-delete"></i>
                                            </button>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="mt-3">
                        {{ $resultados->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('css')
<style>
    /* Modal styles (Simplified for Shreyu theme) */
    .resource-modal {
        display: none;
        position: fixed;
        z-index: 1050;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.8);
        backdrop-filter: blur(5px);
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .resource-modal.active {
        display: flex;
        opacity: 1;
    }

    .modal-container {
        width: 90%;
        max-width: 1200px;
        height: 90vh;
        background: #fff;
        border-radius: 8px;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5);
    }

    .modal-top-bar {
        padding: 15px 20px;
        background: #32394e;
        color: #fff;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .modal-frame-body {
        flex: 1;
        background: #f8f9fa;
        padding: 0;
    }

    .modal-frame-body iframe {
        width: 100%;
        height: 100%;
        border: none;
    }

    .btn-modal-close {
        background: transparent;
        border: none;
        color: #fff;
        font-size: 24px;
        cursor: pointer;
    }
</style>
@endpush

@push('modals')
<div id="resourceModal" class="resource-modal">
    <div class="modal-container">
        <div class="modal-top-bar">
            <h4 class="mb-0" id="resourceModalTitle" style="color: white">Visualizar Recurso</h4>
            <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn-modal-close" onclick="closeResourceModal()">
                    <i class="mdi mdi-close"></i>
                </button>
            </div>
        </div>
        <div class="modal-frame-body">
            <iframe id="resourceViewer" src=""></iframe>
        </div>
    </div>
</div>
@endpush

@push('scripts')
<script>
    function openResourceModal(url, title, isLink = false) {
        const modal = document.getElementById('resourceModal');
        const viewer = document.getElementById('resourceViewer');
        const modalTitle = document.getElementById('resourceModalTitle');
        
        let finalUrl = url;
        
        // Optimize Google Drive links for iframe
        if (isLink && url.includes('drive.google.com')) {
            if (url.includes('/view') || url.includes('/edit')) {
                finalUrl = url.replace(/\/view.*|\/edit.*/, '/preview');
            }
        } else if (!isLink) {
            finalUrl += '#toolbar=0';
        }
        
        viewer.src = finalUrl;
        modalTitle.textContent = title;
        
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeResourceModal() {
        const modal = document.getElementById('resourceModal');
        const viewer = document.getElementById('resourceViewer');
        
        modal.classList.remove('active');
        viewer.src = 'about:blank';
        document.body.style.overflow = 'auto';
    }

    // Close on click outside
    document.getElementById('resourceModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeResourceModal();
        }
    });

    // Close on ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeResourceModal();
        }
    });
</script>
@endpush

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize DataTable
    const table = $('#resultados-table').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
        },
        order: [[0, 'desc']],
        pageLength: 15,
        columnDefs: [
            { orderable: false, targets: [7] }
        ]
    });

    // Refresh button
    $('#refreshBtn').click(function() {
        location.reload();
    });

    // Toggle visibility
    $('.toggle-visibility').change(function() {
        const checkbox = $(this);
        const resultadoId = checkbox.data('id');
        const isVisible = checkbox.is(':checked');
        
        $.ajax({
            url: `/admin/resultados-examenes/${resultadoId}/toggle-visibility`,
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    toastr.success(response.message);
                    const badge = checkbox.next('label').find('.badge');
                    if (response.visible) {
                        badge.removeClass('bg-secondary').addClass('bg-success').text('Publicado');
                    } else {
                        badge.removeClass('bg-success').addClass('bg-secondary').text('Borrador');
                    }
                } else {
                    toastr.error(response.message);
                    checkbox.prop('checked', !isVisible);
                }
            },
            error: function(xhr) {
                toastr.error('Error al cambiar la visibilidad');
                checkbox.prop('checked', !isVisible);
            }
        });
    });

    // Delete button
    $('.delete-btn').click(function() {
        const btn = $(this);
        const resultadoId = btn.data('id');
        const nombreExamen = btn.data('nombre');
        
        Swal.fire({
            title: '¿Estás seguro?',
            html: `Se eliminará el resultado: <strong>${nombreExamen}</strong>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                // Create form and submit
                const form = $('<form>', {
                    method: 'POST',
                    action: `/admin/resultados-examenes/${resultadoId}`
                });
                
                form.append($('<input>', {
                    type: 'hidden',
                    name: '_token',
                    value: $('meta[name="csrf-token"]').attr('content')
                }));
                
                form.append($('<input>', {
                    type: 'hidden',
                    name: '_method',
                    value: 'DELETE'
                }));
                
                $('body').append(form);
                form.submit();
            }
        });
    });
});
</script>
@endpush
