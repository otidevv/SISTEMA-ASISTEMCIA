<!DOCTYPE html>
<html lang="en">


<!-- Mirrored from coderthemes.com/shreyu/index.html by HTTrack Website Copier/3.x [XR&CO'2014], Fri, 02 May 2025 17:40:03 GMT -->

<head>
    <meta charset="utf-8" />
    <title>Portal CEPRE UNAMAD | Centro Preuniversitario Universidad Nacional Amazónica de Madre de Dios</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description"
        content="Portal oficial del Centro Preuniversitario de la Universidad Nacional Amazónica de Madre de Dios (CEPRE UNAMAD). Información sobre admisión, cursos, horarios y más." />
    <meta name="keywords"
        content="CEPRE UNAMAD, portal cepre unamad, preuniversitario, Universidad Nacional Amazónica de Madre de Dios, admisión, Puerto Maldonado" />
    <meta name="author" content="UNAMAD" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />

    <!-- Etiquetas Open Graph para redes sociales -->
    <meta property="og:title" content="Portal CEPRE UNAMAD" />
    <meta property="og:description"
        content="Portal oficial del Centro Preuniversitario de la Universidad Nacional Amazónica de Madre de Dios" />
    <meta property="og:url" content="https://portalcepre.unamad.edu.pe/" />
    <meta property="og:image" content="https://portalcepre.unamad.edu.pe/assets/images/logo-unamad.jpg" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- third party css -->
    <link href="{{ asset('assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css') }}" rel="stylesheet"
        type="text/css" />
    <link href="{{ asset('assets/libs/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css') }}"
        rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/libs/datatables.net-buttons-bs4/css/buttons.bootstrap4.min.css') }}" rel="stylesheet"
        type="text/css" />
    <link href="{{ asset('assets/libs/datatables.net-select-bs4/css/select.bootstrap4.min.css') }}" rel="stylesheet"
        type="text/css" />
    <!-- third party css end -->

    <!-- Icons CSS -->
    <link href="{{ asset('assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
    <!-- App favicon -->
    <link rel="shortcut icon" href="{{ asset('assets/images/favicon.ico') }}">
    <link href="{{ asset('assets/libs/flatpickr/flatpickr.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/app.min.css') }}" rel="stylesheet" type="text/css" id="app-default-stylesheet" />
    @stack('css')

    <!-- Config js -->
    <script src="{{ asset('assets/js/config.js') }}"></script>
    <script>
        window.default_server = "{{ url('/') }}";
    </script>
</head>

<body>

    <!-- Begin page -->
    <div id="wrapper">
        <!-- Header -->
        @include('partials.header')


        <!-- ============================================================== -->
        <!-- Start Page Content here -->
        <!-- ============================================================== -->

        <div class="content-page">
            <div class="content">
                @yield('content')

            </div> <!-- content -->


            <!-- Footer -->
            @include('partials.footer')



        </div>

        <!-- ============================================================== -->
        <!-- End Page content -->
        <!-- ============================================================== -->


    </div>
    <!-- END wrapper -->

    <!-- Theme Settings -->
    <div class="offcanvas offcanvas-end" tabindex="-1" id="theme-settings-offcanvas" style="width: 260px;">
        <div class="px-3 m-0 py-2 text-uppercase bg-light offcanvas-header">
            <h6 class="fw-medium d-block mb-0">Theme Settings</h6>

            <button type="button" class="btn-close fs-14" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>

        <div class="offcanvas-body" data-simplebar style="height: calc(100% - 50px);">

            <div class="alert alert-warning" role="alert">
                <strong>Customize </strong> the overall color scheme, sidebar menu, etc.
            </div>

            <h6 class="fw-medium mt-4 mb-2 pb-1">Color Scheme</h6>
            <div class="form-switch d-flex align-items-center gap-1 mb-1">
                <input type="checkbox" class="form-check-input mt-0" name="color-scheme-mode" value="light"
                    id="light-mode-check" checked />
                <label class="form-check-label" for="light-mode-check">Light Mode</label>
            </div>

            <div class="form-switch d-flex align-items-center gap-1 mb-1">
                <input type="checkbox" class="form-check-input mt-0" name="color-scheme-mode" value="dark"
                    id="dark-mode-check" />
                <label class="form-check-label" for="dark-mode-check">Dark Mode</label>
            </div>

            <!-- Width -->
            <h6 class="fw-medium mt-4 mb-2 pb-1">Width</h6>
            <div class="form-switch d-flex align-items-center gap-1 mb-1">
                <input type="checkbox" class="form-check-input mt-0" name="layout-width" value="fluid" id="fluid-check"
                    checked />
                <label class="form-check-label" for="fluid-check">Fluid</label>
            </div>
            <div class="form-switch d-flex align-items-center gap-1 mb-1">
                <input type="checkbox" class="form-check-input mt-0" name="layout-width" value="boxed"
                    id="boxed-check" />
                <label class="form-check-label" for="boxed-check">Boxed</label>
            </div>

            <!-- Menu positions -->
            <h6 class="fw-medium mt-4 mb-2 pb-1">Menu Position</h6>

            <div class="form-switch d-flex align-items-center gap-1 mb-1">
                <input type="checkbox" class="form-check-input mt-0" name="menu-position" value="fixed"
                    id="fixed-check" checked />
                <label class="form-check-label" for="fixed-check">Fixed</label>
            </div>

            <div class="form-switch d-flex align-items-center gap-1 mb-1">
                <input type="checkbox" class="form-check-input mt-0" name="menu-position" value="scrollable"
                    id="scrollable-check" />
                <label class="form-check-label" for="scrollable-check">Scrollable</label>
            </div>

            <!-- Left Sidebar-->
            <h6 class="fw-medium mt-4 mb-2 pb-1">Menu Color</h6>

            <div class="form-switch d-flex align-items-center gap-1 mb-1">
                <input type="checkbox" class="form-check-input mt-0" name="menu-color" value="light"
                    id="light-check" checked />
                <label class="form-check-label" for="light-check">Light</label>
            </div>

            <div class="form-switch d-flex align-items-center gap-1 mb-1">
                <input type="checkbox" class="form-check-input mt-0" name="menu-color" value="dark"
                    id="dark-check" />
                <label class="form-check-label" for="dark-check">Dark</label>
            </div>

            <!-- <div class="form-switch d-flex align-items-center gap-1 mb-1">
                <input type="checkbox" class="form-check-input mt-0" name="menu-color" value="brand" id="brand-check" />
                <label class="form-check-label" for="brand-check">Brand</label>
            </div> -->

            <!-- size -->
            <div id="sidebarSize">
                <h6 class="fw-medium mt-4 mb-2 pb-1">Left Sidebar Size</h6>

                <div class="form-switch d-flex align-items-center gap-1 mb-1">
                    <input type="checkbox" class="form-check-input mt-0" name="leftsidebar-size" value="default"
                        id="default-size-check" checked />
                    <label class="form-check-label" for="default-size-check">Default</label>
                </div>

                <div class="form-switch d-flex align-items-center gap-1 mb-1">
                    <input type="checkbox" class="form-check-input mt-0" name="leftsidebar-size" value="condensed"
                        id="condensed-check" />
                    <label class="form-check-label" for="condensed-check">Condensed <small>(Extra Small
                            size)</small></label>
                </div>

                <div class="form-switch d-flex align-items-center gap-1 mb-1">
                    <input type="checkbox" class="form-check-input mt-0" name="leftsidebar-size" value="compact"
                        id="compact-check" />
                    <label class="form-check-label" for="compact-check">Compact <small>(Small size)</small></label>
                </div>
            </div>

            <!-- User info -->
            <div id="sidebarUser">
                <h6 class="fw-medium mt-4 mb-2 pb-1">Sidebar User Info</h6>

                <div class="form-switch d-flex align-items-center gap-1 mb-1">
                    <input type="checkbox" class="form-check-input mt-0" name="leftsidebar-user" value="fixed"
                        id="sidebaruser-check" />
                    <label class="form-check-label" for="sidebaruser-check">Enable</label>
                </div>
            </div>


            <!-- Topbar -->
            <h6 class="fw-medium mt-4 mb-2 pb-1">Topbar</h6>

            <div class="form-switch d-flex align-items-center gap-1 mb-1">
                <input type="checkbox" class="form-check-input mt-0" name="topbar-color" value="dark"
                    id="darktopbar-check" checked />
                <label class="form-check-label" for="darktopbar-check">Dark</label>
            </div>

            <div class="form-switch d-flex align-items-center gap-1 mb-1">
                <input type="checkbox" class="form-check-input mt-0" name="topbar-color" value="light"
                    id="lighttopbar-check" />
                <label class="form-check-label" for="lighttopbar-check">Light</label>
            </div>

            <div class="form-switch d-flex align-items-center gap-1 mb-1">
                <input type="checkbox" class="form-check-input mt-0" name="topbar-color" value="brand"
                    id="brandtopbar-check" />
                <label class="form-check-label" for="brandtopbar-check">Brand</label>
            </div>
        </div>


        <div class="d-flex flex-column gap-2 px-3 py-2 offcanvas-header border-top border-dashed">

            <button class="btn btn-primary w-100" id="resetBtn">Reset to Default</button>

            <a href="https://1.envato.market/shreyu_admin" class="btn btn-danger w-100" target="_blank">
                <i class="mdi mdi-basket me-1"></i> Purchase Now
            </a>
        </div>

    </div>
    @stack('modals')


    <script src="{{ asset('assets/js/vendor.min.js') }}"></script>
    <script src="{{ asset('assets/libs/moment/min/moment.min.js') }}"></script>
    <script src="{{ asset('assets/libs/flatpickr/flatpickr.min.js') }}"></script>

    <!-- third party js -->
    <script src="{{ asset('assets/libs/datatables.net/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('assets/libs/datatables.net-responsive-bs4/js/responsive.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('assets/libs/datatables.net-buttons/js/dataTables.buttons.min.js') }}"></script>
    <script src="{{ asset('assets/libs/datatables.net-buttons-bs4/js/buttons.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('assets/libs/datatables.net-buttons/js/buttons.html5.min.js') }}"></script>
    <script src="{{ asset('assets/libs/datatables.net-buttons/js/buttons.flash.min.js') }}"></script>
    <script src="{{ asset('assets/libs/datatables.net-buttons/js/buttons.print.min.js') }}"></script>
    <script src="{{ asset('assets/libs/datatables.net-keytable/js/dataTables.keyTable.min.js') }}"></script>
    <script src="{{ asset('assets/libs/datatables.net-select/js/dataTables.select.min.js') }}"></script>

    <!-- Datatables init -->
    <script src="{{ asset('assets/js/pages/datatables.init.js') }}"></script>

    <!-- App js -->
    <script src="{{ asset('assets/js/app.js') }}"></script>
    @stack('js')
    @stack('scripts')


</body>


<!-- Mirrored from coderthemes.com/shreyu/index.html by HTTrack Website Copier/3.x [XR&CO'2014], Fri, 02 May 2025 17:40:21 GMT -->

</html>
