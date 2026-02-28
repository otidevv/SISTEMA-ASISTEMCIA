@extends('layouts.app')

@section('title', 'Auditoría del Sistema')

@push('css')
    <style>
        .details-row { border-left: 4px solid #727cf5 !important; background-color: #f8f9fa; border-radius: 4px; border: 1px solid #eef2f7; }
        td.dt-control { text-align: center; cursor: pointer; color: #727cf5; font-size: 1.2rem; transition: color 0.2s; }
        td.dt-control:hover { color: #2b3a4a; }
        table.dataTable tbody tr.shown td.dt-control { color: #fa5c7c; }
    </style>
@endpush

@section('content')
    <!-- start page title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">Historial de Auditoría</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Auditoría</li>
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
                    <h4 class="header-title mt-0 mb-3"><i class="uil uil-history text-primary"></i> Registro Inmutable de Actividades</h4>
                    <p class="text-muted font-14">
                        Visualiza todas las creaciones, actualizaciones y eliminaciones de registros críticos en el sistema. Utiliza el buscador para filtrar rápidamente. <b>Haz clic en el ícono <i class="uil uil-plus-circle text-primary"></i> para ver los campos exactos que cambiaron (Anterior vs Nuevo).</b>
                    </p>

                    <div class="table-responsive">
                        <table id="auditoria-datatable" class="table table-hover table-centered mb-0 align-middle w-100">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 5%"></th>
                                    <th>Fecha y Hora</th>
                                    <th>Responsable</th>
                                    <th>Acción</th>
                                    <th>Módulo/Registro</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Cargado por AJAX -->
                            </tbody>
                        </table>
                    </div>
                </div> <!-- end card body-->
            </div> <!-- end card -->
        </div><!-- end col-->
    </div>
@endsection

@push('js')
    <script>
        $(document).ready(function() {
            var table = $('#auditoria-datatable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('auditoria.index') }}",
                    type: 'GET'
                },
                order: [[1, 'desc']], // Fecha descdendente predeterminada
                columns: [
                    {
                        className: 'dt-control',
                        orderable: false,
                        data: null,
                        defaultContent: '<i class="uil uil-plus-circle"></i>',
                        searchable: false
                    },
                    { data: 'fecha', name: 'created_at', width: '15%' },
                    { data: 'responsable', name: 'causer.nombre', orderable: false, searchable: false }, 
                    { data: 'accion_badge', name: 'event', orderable: false, searchable: false },
                    { data: 'modulo_legible', name: 'subject_type', searchable: true } 
                ],
                "language": {
                    "lengthMenu": "Mostrar _MENU_ registros por página",
                    "zeroRecords": "No se encontraron resultados en el historial histórico",
                    "info": "Mostrando _START_ a _END_ de _TOTAL_ registros",
                    "infoEmpty": "Mostrando 0 a 0 de 0 registros",
                    "infoFiltered": "(filtrado de _MAX_ registros totales)",
                    "search": "Buscar:",
                    "paginate": {
                        "first": "Primero",
                        "last": "Último",
                        "previous": "<i class='uil uil-angle-left'>",
                        "next": "<i class='uil uil-angle-right'>"
                    },
                    "processing": "Procesando millones de logs... 😎"
                },
                "drawCallback": function () {
                    $('.dataTables_paginate > .pagination').addClass('pagination-rounded');
                }
            });

            // Lógica para Expandir/Contraer la fila y ver los detalles HTML (Valores Nuevos vs Viejos)
            $('#auditoria-datatable tbody').on('click', 'td.dt-control', function () {
                var tr = $(this).closest('tr');
                var icon = $(this).find('i');
                var row = table.row(tr);

                if (row.child.isShown()) {
                    // Si ya está abierto, lo ocultamos
                    row.child.hide();
                    tr.removeClass('shown');
                    icon.removeClass('uil-minus-circle text-danger').addClass('uil-plus-circle text-primary');
                } else {
                    // Si está cerrado, obtenemos datos y mostramos el row hijo (detalles_html pre-renderizado desde Controlador)
                    var data = row.data();
                    var content = '<div class="p-3 details-row m-2">' + 
                                  '<h5 class="mb-2 mt-0 font-14"><i class="uil uil-list-ui-alt"></i> <b>Exploración de Cambios: ' + data.event + '</b></h5>' + 
                                  data.detalles_html + 
                                  '</div>';
                    row.child(content).show();
                    tr.addClass('shown');
                    icon.removeClass('uil-plus-circle text-primary').addClass('uil-minus-circle text-danger');
                }
            });
        });
    </script>
@endpush
