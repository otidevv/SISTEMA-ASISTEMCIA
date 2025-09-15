@extends('layouts.app')

@section('title', 'Material Académico')

@push('css')
    <!-- third party css -->
    <link href="{{ asset('assets/libs/datatables.net-bs5/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/libs/datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/libs/datatables.net-buttons-bs5/css/buttons.bootstrap5.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/libs/datatables.net-select-bs5/css/select.bootstrap5.min.css') }}" rel="stylesheet" type="text/css" />
    <!-- third party css end -->
@endpush



@section('content')
    <!-- start page title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">Material Académico</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Material Académico</li>
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
                    <div class="row mb-2">
                        <div class="col-sm-4">
                            <h4 class="header-title mt-0 mb-1">Lista de Materiales</h4>
                        </div>
                        <div class="col-sm-8">
                            <div class="text-sm-end">
                                @can('materiales.manage')
                                    <a href="{{ route('materiales-academicos.create') }}" class="btn btn-primary mb-2">
                                        <i class="mdi mdi-plus-circle me-1"></i> Nuevo Material
                                    </a>
                                @endcan
                            </div>
                        </div>
                    </div>

                    <table id="materiales-datatable" class="table dt-responsive nowrap w-100">
                        <thead>
                            <tr>
                                <th>Título</th>
                                <th>Curso</th>
                                <th>Profesor</th>
                                <th>Semana</th>
                                <th>Tipo</th>
                                <th>Fecha de Subida</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- DataTables will load data via AJAX --}}
                        </tbody>
                    </table>

                </div> <!-- end card body-->
            </div> <!-- end card -->
        </div><!-- end col-->
    </div>

@endsection

@push('js')
    <!-- third party js -->
    <script src="{{ asset('assets/libs/datatables.net/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/libs/datatables.net-bs5/js/dataTables.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('assets/libs/datatables.net-responsive-bs5/js/responsive.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('assets/libs/datatables.net-buttons/js/dataTables.buttons.min.js') }}"></script>
    <script src="{{ asset('assets/libs/datatables.net-buttons-bs5/js/buttons.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('assets/libs/datatables.net-buttons/js/buttons.html5.min.js') }}"></script>
    <script src="{{ asset('assets/libs/datatables.net-buttons/js/buttons.flash.min.js') }}"></script>
    <script src="{{ asset('assets/libs/datatables.net-buttons/js/buttons.print.min.js') }}"></script>
    <script src="{{ asset('assets/libs/datatables.net-keytable/js/dataTables.keyTable.min.js') }}"></script>
    <script src="{{ asset('assets/libs/datatables.net-select/js/dataTables.select.min.js') }}"></script>
    <script src="{{ asset('assets/libs/pdfmake/build/pdfmake.min.js') }}"></script>
    <script src="{{ asset('assets/libs/pdfmake/build/vfs_fonts.js') }}"></script>
    <!-- third party js ends -->

    <script>
        $(document).ready(function() {
            $("#materiales-datatable").DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('materiales-academicos.index') }}",
                columns: [
                    { data: 'titulo', name: 'titulo' },
                    { data: 'curso.nombre', name: 'curso.nombre' },
                    { data: 'profesor.nombre_completo', name: 'profesor.nombre_completo' },
                    { data: 'semana', name: 'semana' },
                    { data: 'tipo', name: 'tipo' },
                    { data: 'created_at', name: 'created_at' },
                    { data: 'acciones', name: 'acciones', orderable: false, searchable: false }
                ],
                language: {
                    paginate: {
                        previous: "<i class='mdi mdi-chevron-left'>",
                        next: "<i class='mdi mdi-chevron-right'>"
                    },
                    info: "Showing materiales _START_ to _END_ of _TOTAL_",
                    lengthMenu: "Display <select class='form-select form-select-sm ms-1 me-1'><option value='10'>10</option><option value='20'>20</option><option value='-1'>All</option></select> materiales"
                },
                pageLength: 10,
                drawCallback: function() {
                    $(".dataTables_paginate > .pagination").addClass("pagination-rounded");
                }
            });
        });
    </script>
@endpush

