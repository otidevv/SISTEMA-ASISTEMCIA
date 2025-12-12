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
                                            <a href="{{ route('resultados-examenes.download', $resultado->id) }}" 
                                               class="btn btn-sm btn-info" 
                                               title="Descargar PDF">
                                                <i class="mdi mdi-download"></i>
                                            </a>
                                            @endif
                                            
                                            @if($resultado->tiene_link)
                                            <a href="{{ $resultado->link_externo }}" 
                                               target="_blank" 
                                               class="btn btn-sm btn-primary" 
                                               title="Ver Link">
                                                <i class="mdi mdi-open-in-new"></i>
                                            </a>
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
