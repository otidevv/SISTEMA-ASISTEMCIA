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
    <!-- Toastr CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css">
    @stack('css')

    <link href="https://cdn.jsdelivr.net/npm/@mdi/font@7.4.47/css/materialdesignicons.min.css" rel="stylesheet">

    <!-- Config js -->
    <script src="{{ asset('assets/js/config.js') }}"></script>
    <script>
        window.default_server = "{{ url('/') }}";
        // Configuración de sesión para JavaScript
        window.sessionConfig = {
            lifetime: {{ config('session.lifetime') }}, // minutos desde config
            warningTime: 2 // mostrar contador desde el inicio
        };
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
    <script src="{{ asset('assets/libs/datatables.net-buttons/js/buttons.print.js') }}"></script>
    <script src="{{ asset('assets/libs/datatables.net-keytable/js/dataTables.keyTable.min.js') }}"></script>
    <script src="{{ asset('assets/libs/datatables.net-select/js/dataTables.select.min.js') }}"></script>

    <!-- Datatables init -->
    <script src="{{ asset('assets/js/pages/datatables.init.js') }}"></script>

    <!-- App js -->
    <script src="{{ asset('assets/js/app.js') }}"></script>
    
    <!-- Toastr JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.js"></script>
    
    <!-- CSRF Token Handler - DEBE cargarse después de jQuery y SweetAlert2 -->
    <script src="{{ asset('js/csrf-handler.js') }}"></script>
    
    <script>
        // Configuración global de toastr
        toastr.options = {
            "closeButton": true,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "timeOut": "3000",
            "extendedTimeOut": "1000"
        };

        @if(Session::has('success'))
            toastr.success("{{ Session::get('success') }}");
        @endif

        @if(Session::has('error'))
            toastr.error("{{ Session::get('error') }}");
        @endif

        @if(Session::has('info'))
            toastr.info("{{ Session::get('info') }}");
        @endif

        @if(Session::has('warning'))
            toastr.warning("{{ Session::get('warning') }}");
        @endif

        @if ($errors->any())
            @foreach ($errors->all() as $error)
                toastr.error('{{ $error }}');
            @endforeach
        @endif
    </script>
    
    @stack('js')
    @stack('scripts')

    <!-- ============================================ -->
    <!-- SISTEMA DE GESTIÓN DE SESIÓN CON CONTADOR -->
    <!-- ============================================ -->
    <script>
        class SessionManager {
            constructor() {
                this.config = window.sessionConfig || { lifetime: 120, warningTime: 1 };
                this.lastActivity = Date.now();
                this.warningShown = false;
                this.checkInterval = null;
                this.isAlertActive = false;
                this.countdownInterval = null;
                
                console.log('SessionManager iniciado:', {
                    lifetime: this.config.lifetime + ' minutos',
                    warningTime: this.config.warningTime + ' minuto antes'
                });
                
                this.init();
            }
            
            init() {
                // Rastrear actividad del usuario
                this.trackUserActivity();
                
                // Verificar estado cada 10 segundos
                this.startSessionCheck();
            }
            
            trackUserActivity() {
                const events = ['mousedown', 'mousemove', 'keypress', 'scroll', 'click', 'touchstart'];
                const updateActivity = () => {
                    this.lastActivity = Date.now();
                    this.warningShown = false;
                };
                
                events.forEach(event => {
                    document.addEventListener(event, updateActivity, true);
                });
            }
            
            startSessionCheck() {
                this.checkInterval = setInterval(() => {
                    this.checkSessionStatus();
                }, 10000); // Verificar cada 10 segundos
            }
            
            checkSessionStatus() {
                if (this.isAlertActive) return; // No verificar si ya hay una alerta activa
                
                const now = Date.now();
                const timeSinceActivity = now - this.lastActivity;
                const maxInactivity = this.config.lifetime * 60 * 1000; // convertir a ms
                const warningTime = this.config.warningTime * 60 * 1000; // convertir a ms
                
                // Si queda 1 minuto o menos, mostrar contador regresivo
                if (timeSinceActivity >= (maxInactivity - warningTime)) {
                    const timeRemaining = Math.max(0, maxInactivity - timeSinceActivity);
                    this.showCountdown(timeRemaining);
                }
            }
            
            showCountdown(timeRemaining) {
                if (this.isAlertActive) return;
                
                this.isAlertActive = true;
                this.warningShown = true;
                
                let secondsLeft = Math.floor(timeRemaining / 1000);
                let userChooseToContinue = false; // Bandera para controlar si usuario eligió continuar
                
                console.log('Mostrando contador de sesión:', secondsLeft + ' segundos restantes');
                
                Swal.fire({
                    title: 'Contador de Sesión',
                    html: `
                        <div style="text-align: center;">
                            <p><strong>Tiempo de inactividad detectado</strong></p>
                            <div style="margin: 20px 0;">
                                <div id="countdown-circle" style="
                                    width: 120px; 
                                    height: 120px; 
                                    border: 8px solid #e3e3e3; 
                                    border-top: 8px solid #28a745; 
                                    border-radius: 50%; 
                                    margin: 0 auto 15px auto;
                                    display: flex;
                                    align-items: center;
                                    justify-content: center;
                                    font-size: 2rem;
                                    font-weight: bold;
                                    color: #28a745;
                                ">
                                    <span id="countdown-number">${secondsLeft}</span>
                                </div>
                                <p style="color: #6c757d;">segundos hasta el cierre de sesión</p>
                            </div>
                            <p style="font-size: 0.9rem; color: #666;">
                                Haz cualquier actividad para reiniciar el contador
                            </p>
                        </div>
                    `,
                    icon: 'info',
                    showCancelButton: true,
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Continuar Trabajando',
                    cancelButtonText: 'Cerrar Sesión',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => {
                        this.countdownInterval = setInterval(() => {
                            secondsLeft--;
                            
                            const numberElement = document.getElementById('countdown-number');
                            const circleElement = document.getElementById('countdown-circle');
                            
                            if (numberElement) {
                                numberElement.textContent = secondsLeft;
                                
                                // Cambiar colores según tiempo restante
                                if (secondsLeft <= 10) {
                                    circleElement.style.borderTopColor = '#dc3545';
                                    numberElement.style.color = '#dc3545';
                                    circleElement.style.animation = 'pulse 1s infinite';
                                } else if (secondsLeft <= 30) {
                                    circleElement.style.borderTopColor = '#fd7e14';
                                    numberElement.style.color = '#fd7e14';
                                }
                            }
                            
                            if (secondsLeft <= 0) {
                                clearInterval(this.countdownInterval);
                                this.countdownInterval = null;
                                
                                // SOLO mostrar sesión expirada si el usuario NO eligió continuar
                                if (!userChooseToContinue) {
                                    Swal.close();
                                    // Dar un momento para que se cierre la alerta, luego mostrar alerta final
                                    setTimeout(() => {
                                        this.showSessionExpired();
                                    }, 300);
                                }
                            }
                        }, 1000);
                    },
                    willClose: () => {
                        if (this.countdownInterval) {
                            clearInterval(this.countdownInterval);
                        }
                    }
                }).then((result) => {
                    this.isAlertActive = false;
                    
                    // Limpiar intervalo ANTES de hacer cualquier cosa
                    if (this.countdownInterval) {
                        clearInterval(this.countdownInterval);
                        this.countdownInterval = null;
                    }
                    
                    if (result.isConfirmed) {
                        // Usuario quiere continuar - recargar página para reiniciar todo
                        console.log('Usuario eligió continuar - recargando página');
                        window.location.reload();
                        
                    } else if (result.isDismissed && result.dismiss === Swal.DismissReason.cancel) {
                        // Usuario eligió cerrar sesión
                        console.log('Usuario eligió cerrar sesión manualmente');
                        window.location.href = '{{ route("login") }}';
                    }
                    // Si result.isDismissed por timer, no hacer nada aquí - se maneja en didOpen
                });
            }
            
            showSessionExpired() {
                if (this.isAlertActive) return;
                
                this.isAlertActive = true;
                let finalCountdown = 10;
                
                console.log('Sesión expirada - mostrando alerta final');
                
                // Función para redirigir (backup)
                const redirectToLogin = () => {
                    console.log('Redirigiendo al login...');
                    window.location.href = '{{ route("login") }}';
                };
                
                // Timeout de seguridad - redirigir después de 11 segundos sin importar qué
                const backupRedirect = setTimeout(redirectToLogin, 11000);
                
                Swal.fire({
                    title: 'Sesión Expirada',
                    html: `
                        <div style="text-align: center;">
                            <p><strong>Tu sesión ha expirado por inactividad</strong></p>
                            <p>Serás redirigido al login en:</p>
                            <div style="font-size: 3rem; color: #dc3545; font-weight: bold; margin: 20px 0;">
                                <span id="final-countdown">${finalCountdown}</span>
                            </div>
                            <p style="color: #6c757d; font-size: 0.9rem;">
                                Inicia sesión nuevamente para continuar
                            </p>
                        </div>
                    `,
                    icon: 'error',
                    confirmButtonColor: '#dc3545',
                    confirmButtonText: 'Ir al Login Ahora',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    timer: finalCountdown * 1000,
                    timerProgressBar: true,
                    didOpen: () => {
                        const interval = setInterval(() => {
                            finalCountdown--;
                            const element = document.getElementById('final-countdown');
                            if (element) {
                                element.textContent = finalCountdown;
                                element.style.animation = 'pulse 0.5s infinite';
                            }
                            if (finalCountdown <= 0) {
                                clearInterval(interval);
                                clearTimeout(backupRedirect);
                                redirectToLogin(); // Redirigir inmediatamente
                            }
                        }, 1000);
                    }
                }).then(() => {
                    clearTimeout(backupRedirect);
                    redirectToLogin();
                }).catch(() => {
                    // Si hay cualquier error, igual redirigir
                    clearTimeout(backupRedirect);
                    redirectToLogin();
                });
            }
            
            destroy() {
                if (this.checkInterval) clearInterval(this.checkInterval);
                if (this.countdownInterval) clearInterval(this.countdownInterval);
            }
        }
        
        // Inicializar cuando el DOM esté listo
        let sessionManager;
        document.addEventListener('DOMContentLoaded', function() {
            sessionManager = new SessionManager();
            
            // Interceptar errores 401/419 de AJAX
            $(document).ajaxError(function(event, xhr, settings) {
                if ((xhr.status === 401 || xhr.status === 419) && !sessionManager.isAlertActive) {
                    console.log('Error ' + xhr.status + ' detectado - sesión expirada');
                    sessionManager.showSessionExpired();
                }
            });
        });
        
        // Limpiar al salir
        window.addEventListener('beforeunload', function() {
            if (sessionManager) {
                sessionManager.destroy();
            }
        });
        
        // CSS para animaciones
        const style = document.createElement('style');
        style.textContent = `
            @keyframes pulse {
                0% { transform: scale(1); opacity: 1; }
                50% { transform: scale(1.05); opacity: 0.8; }
                100% { transform: scale(1); opacity: 1; }
            }
        `;
        document.head.appendChild(style);
    </script>


</body>


<!-- Mirrored from coderthemes.com/shreyu/index.html by HTTrack Website Copier/3.x [XR&CO'2014], Fri, 02 May 2025 17:40:21 GMT -->

</html>