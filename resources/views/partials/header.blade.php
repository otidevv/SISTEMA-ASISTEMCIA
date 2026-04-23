<!-- Topbar Start -->
<div class="navbar-custom">
    <div class="container-fluid">
        <ul class="list-unstyled topnav-menu float-end mb-0">

            <li class="d-none d-lg-block">
                <form class="app-search">
                    <div class="app-search-box dropdown">

                        <div class="input-group">
                            <input type="search" class="form-control" placeholder="Search..." id="top-search">
                            <button class="btn input-group-text" type="submit">
                                <i class="uil uil-search"></i>
                            </button>
                        </div>

                        <div class="dropdown-menu dropdown-lg" id="search-dropdown">
                            <!-- item-->
                            <div class="dropdown-header noti-title">
                                <h5 class="text-overflow mb-2">Found 05 results</h5>
                            </div>

                            <!-- item-->
                            <a href="javascript:void(0);" class="dropdown-item notify-item">
                                <i class="uil uil-sliders-v-alt me-1"></i>
                                <span>User profile settings</span>
                            </a>

                            <!-- item-->
                            <a href="javascript:void(0);" class="dropdown-item notify-item">
                                <i class="uil uil-home-alt me-1"></i>
                                <span>Analytics Report</span>
                            </a>

                            <!-- item-->
                            <a href="javascript:void(0);" class="dropdown-item notify-item">
                                <i class="uil uil-life-ring me-1"></i>
                                <span>How can I help you?</span>
                            </a>

                            <!-- item-->
                            <div class="dropdown-header noti-title">
                                <h6 class="text-overflow mb-2 text-uppercase">Users</h6>
                            </div>

                            <div class="notification-list">
                                <!-- item-->
                                <a href="javascript:void(0);" class="dropdown-item notify-item">
                                    <div class="d-flex text-align-start">
                                        <img class="me-2 rounded-circle"
                                            src="{{ asset('assets/images/users/avatar-1.jpg') }}"
                                            alt="Generic placeholder image" height="32">
                                        <div class="flex-grow-1">
                                            <h5 class="m-0 fs-14">Shirley Miller</h5>
                                            <span class="fs-12 mb-0">UI Designer</span>
                                        </div>
                                    </div>
                                </a>

                                <!-- item-->
                                <a href="javascript:void(0);" class="dropdown-item notify-item">
                                    <div class="d-flex text-align-start">
                                        <img class="me-2 rounded-circle"
                                            src="{{ asset('assets/images/users/avatar-2.jpg') }}"
                                            alt="Generic placeholder image" height="32">
                                        <div class="flex-grow-1">
                                            <h5 class="m-0 fs-14">Timothy Moreno</h5>
                                            <span class="fs-12 mb-0">Frontend Developer</span>
                                        </div>
                                    </div>
                                </a>
                            </div>

                        </div>
                    </div>
                </form>
            </li>

            <li class="dropdown d-inline-block d-lg-none">
                <a class="nav-link dropdown-toggle arrow-none" data-bs-toggle="dropdown" href="#" role="button"
                    aria-haspopup="false" aria-expanded="false">
                    <i data-feather="search"></i>
                </a>
                <div class="dropdown-menu dropdown-lg dropdown-menu-end p-0">
                    <form class="p-3">
                        <input type="text" class="form-control" placeholder="Search ..." aria-label="search here">
                    </form>
                </div>
            </li>

            <li class="d-none d-lg-inline-block">
                <a class="nav-link" id="light-dark-mode" href="#">
                    <i data-feather="sun" class="light-mode"></i>
                    <i data-feather="moon" class="dark-mode"></i>
                </a>
            </li>

            <li class="dropdown d-none d-lg-inline-block">
                <a class="nav-link dropdown-toggle arrow-none" data-toggle="fullscreen" href="#">
                    <i data-feather="maximize"></i>
                </a>
            </li>

            <li class="dropdown d-none d-lg-inline-block topbar-dropdown">
                <a class="nav-link dropdown-toggle arrow-none" data-bs-toggle="dropdown" href="#" role="button"
                    aria-haspopup="false" aria-expanded="false">
                    <i data-feather="grid"></i>
                </a>
                <div class="dropdown-menu dropdown-lg dropdown-menu-end p-0">

                    <div class="p-1">
                        <div class="row g-0">
                            <div class="col">
                                <a class="dropdown-icon-item" href="#">
                                    <img src="{{ asset('assets/images/brands/slack.png') }}" alt="slack">
                                    <span>Slack</span>
                                </a>
                            </div>
                            <div class="col">
                                <a class="dropdown-icon-item" href="#">
                                    <img src="{{ asset('assets/images/brands/github.png') }}" alt="Github">
                                    <span>GitHub</span>
                                </a>
                            </div>
                            <div class="col">
                                <a class="dropdown-icon-item" href="#">
                                    <img src="{{ asset('assets/images/brands/dribbble.png') }}" alt="dribbble">
                                    <span>Dribbble</span>
                                </a>
                            </div>
                        </div>
                    </div>

                </div>
            </li>

            <li class="dropdown d-none d-lg-inline-block topbar-dropdown">
                <a class="nav-link dropdown-toggle arrow-none" data-bs-toggle="dropdown" href="#"
                    role="button" aria-haspopup="false" aria-expanded="false">
                    <i data-feather="globe"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-end">

                    <!-- item-->
                    <a href="javascript:void(0);" class="dropdown-item">
                        <img src="{{ asset('assets/images/flags/us.jpg') }}" alt="user-image" class="me-1"
                            height="12">
                        <span class="align-middle">English</span>
                    </a>

                    <!-- item-->
                    <a href="javascript:void(0);" class="dropdown-item">
                        <img src="{{ asset('assets/images/flags/germany.jpg') }}" alt="user-image" class="me-1"
                            height="12">
                        <span class="align-middle">German</span>
                    </a>

                    <!-- item-->
                    <a href="javascript:void(0);" class="dropdown-item">
                        <img src="{{ asset('assets/images/flags/italy.jpg') }}" alt="user-image" class="me-1"
                            height="12">
                        <span class="align-middle">Italian</span>
                    </a>

                    <!-- item-->
                    <a href="javascript:void(0);" class="dropdown-item">
                        <img src="{{ asset('assets/images/flags/spain.jpg') }}" alt="user-image" class="me-1"
                            height="12">
                        <span class="align-middle">Spanish</span>
                    </a>

                    <!-- item-->
                    <a href="javascript:void(0);" class="dropdown-item">
                        <img src="{{ asset('assets/images/flags/russia.jpg') }}" alt="user-image" class="me-1"
                            height="12">
                        <span class="align-middle">Russian</span>
                    </a>

                </div>
            </li>
            <li class="dropdown notification-list topbar-dropdown">
                <a class="nav-link dropdown-toggle position-relative" data-bs-toggle="dropdown" href="#"
                    role="button" aria-haspopup="false" aria-expanded="false" id="notification-bell">
                    <i data-feather="bell"></i>
                    <span class="badge bg-danger rounded-circle noti-icon-badge d-none" id="notification-count">0</span>
                </a>
                <div class="dropdown-menu dropdown-menu-end dropdown-lg">

                    <!-- item-->
                    <div class="dropdown-item noti-title">
                        <h5 class="m-0">
                            <span class="float-end">
                                <a href="#" class="text-dark"><small>Limpiar todo</small></a>
                            </span>Notificaciones
                        </h5>
                    </div>

                    <div class="noti-scroll" data-simplebar id="notification-items-container" style="max-height: 300px; overflow-y: auto;">
                        <div class="text-center p-3">
                            <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                        </div>
                    </div>

                    <!-- All-->
                    <a href="{{ route('notifications.index') }}"
                        class="dropdown-item text-center text-primary notify-item notify-all">
                        Ver todas <i class="fe-arrow-right"></i>
                    </a>

                </div>
            </li>



            <li class="dropdown notification-list topbar-dropdown">
                @php
                    $userAvatar = Auth::user()->foto_perfil 
                        ? asset('storage/' . Auth::user()->foto_perfil) 
                        : 'https://ui-avatars.com/api/?name=' . urlencode(Auth::user()->nombre . ' ' . Auth::user()->apellido_paterno) . '&background=e91e63&color=fff&size=100';
                @endphp
                <a class="nav-link dropdown-toggle nav-user me-0" data-bs-toggle="dropdown" href="#"
                    role="button" aria-haspopup="false" aria-expanded="false">
                    <img src="{{ $userAvatar }}" alt="user-image"
                        class="rounded-circle border border-2 border-white shadow-sm">
                    <span class="pro-user-name d-sm-inline d-none ms-1">
                        {{ Auth::user()->nombre }} <i class="uil uil-angle-down"></i>
                    </span>
                </a>

                <div class="dropdown-menu dropdown-menu-end profile-dropdown ">
                    <!-- item-->
                    <div class="dropdown-header noti-title">
                        <h6 class="text-overflow m-0">¡Bienvenido!</h6>
                    </div>

                    <a href="{{ route('perfil.index') }}" class="dropdown-item notify-item">
                        <i data-feather="user" class="icon-dual icon-xs me-1"></i><span>Mi Perfil</span>
                    </a>

                    <a href="{{ route('perfil.index') }}" class="dropdown-item notify-item">
                        <i data-feather="settings" class="icon-dual icon-xs me-1"></i><span>Configuración</span>
                    </a>

                    <div class="dropdown-divider"></div>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <a href="{{ route('logout') }}" class="dropdown-item notify-item"
                            onclick="event.preventDefault(); this.closest('form').submit();">
                            <i data-feather="log-out" class="icon-dual icon-xs me-1"></i><span>Cerrar Sesión</span>
                        </a>
                    </form>
                </div>
            </li>

            <li class="dropdown notification-list">
                <button class="nav-link" data-bs-toggle="offcanvas" data-bs-target="#theme-settings-offcanvas"
                    type="button">
                    <i class="mdi mdi-cog-outline font-22"></i>
                    <i data-feather="settings"></i>
                </button>
            </li>

        </ul>

        {{-- Audio para notificaciones --}}
        <audio id="notification-sound" preload="auto">
            <source src="{{ asset('assets/audio/sonido_notificacion.mp3') }}" type="audio/mpeg">
        </audio>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const bell = document.getElementById('notification-bell');
                const countBadge = document.getElementById('notification-count');
                const itemsContainer = document.getElementById('notification-items-container');
                const sound = document.getElementById('notification-sound');

                // --- GESTIÓN DE NOTIFICACIONES Y SONIDO MULTICANAL (Web Audio API) ---
                let beepAudioBuffer = null;
                let webAudioContext = null;

                try {
                    webAudioContext = new (window.AudioContext || window.webkitAudioContext)();
                    fetch('{{ asset("assets/audio/sonido_notificacion.mp3") }}')
                        .then(response => response.arrayBuffer())
                        .then(buffer => webAudioContext.decodeAudioData(buffer))
                        .then(decoded => { beepAudioBuffer = decoded; })
                        .catch(err => console.log('Error decoding audio:', err));
                } catch(e) {}
                
                function playNotificationSound() {
                    if (webAudioContext && beepAudioBuffer) {
                        try {
                            if (webAudioContext.state === 'suspended') webAudioContext.resume();
                            const source = webAudioContext.createBufferSource();
                            source.buffer = beepAudioBuffer;
                            const gainNode = webAudioContext.createGain();
                            gainNode.gain.value = 1.0; 
                            source.connect(gainNode);
                            gainNode.connect(webAudioContext.destination);
                            source.start(0);
                        } catch(e) {}
                    } else {
                        const originalSound = document.getElementById('notification-sound');
                        if (originalSound) {
                            originalSound.currentTime = 0;
                            originalSound.volume = 1.0;
                            originalSound.play().catch(e => {});
                        }
                    }
                }

                // --- SISTEMA DE RESALTADO PERSISTENTE ---
                const tablesToCheck = [
                    { id: '#postulaciones-datatable', globalVar: 'window.postulacionesDataTable' },
                    { id: '#reforzamientoTable', globalVar: 'window.reforzamientoDataTable' }
                ];

                window.applyPersistentHighlights = function() {
                    const unseen = JSON.parse(localStorage.getItem('newRecordsUnseen') || '[]');
                    if (unseen.length === 0) return;

                    tablesToCheck.forEach(tableInfo => {
                        if(typeof $ !== 'undefined') {
                            $(`${tableInfo.id} tbody tr`).each(function() {
                                const fila = $(this);
                                const filaTexto = fila.text();
                                const identifierFound = unseen.find(ident => ident && filaTexto.includes(ident));
                                
                                if (identifierFound) {
                                    fila.addClass('fila-reciente-neon').removeClass('fila-reciente-neon-removida');
                                    if (fila.find('.badge-nuevo-registro').length === 0) {
                                        const nameCell = fila.find('td').eq(1).length > 0 ? fila.find('td').eq(1) : fila.find('td:first');
                                        nameCell.append(' <span class="badge bg-danger badge-nuevo-registro ms-2 animate__animated animate__pulse animate__infinite shadow-sm">NUEVO</span>');
                                    }
                                }
                            });
                        }
                    });
                }

                // Delegación de eventos para marcar como visto (Funciona para todas las tablas siempre)
                if(typeof $ !== 'undefined') {
                    $('body').on('click.visto', 'table tbody a, table tbody button, table tbody .btn', function() {
                        let fila = $(this).closest('tr');
                        
                        // Si está en modo responsive (child row), su padre "real" es la fila anterior
                        if (fila.hasClass('child')) fila = fila.prev('tr');

                        if (fila.hasClass('fila-reciente-neon')) {
                            const filaTexto = fila.text();
                            let unseen = JSON.parse(localStorage.getItem('newRecordsUnseen') || '[]');
                            const identifierFound = unseen.find(ident => ident && filaTexto.includes(ident));
                            
                            if (identifierFound) {
                                unseen = unseen.filter(ident => ident !== identifierFound);
                                localStorage.setItem('newRecordsUnseen', JSON.stringify(unseen));
                                fila.removeClass('fila-reciente-neon').addClass('fila-reciente-neon-removida');
                                fila.find('.badge-nuevo-registro').fadeOut(300, function() { $(this).remove(); });
                            }
                        }
                    });
                }

                // Inyectar CSS Dinámico
                if (typeof document !== 'undefined' && !document.getElementById('style-fila-nueva')) {
                    const style = document.createElement('style');
                    style.id = 'style-fila-nueva';
                    style.innerHTML = `
                        tr.fila-reciente-neon, tr.fila-reciente-neon:nth-of-type(odd), tr.fila-reciente-neon:nth-of-type(even) { 
                            --bs-table-accent-bg: #fff8c6 !important; 
                            --bs-table-bg: #fff8c6 !important; 
                        }
                        tr.fila-reciente-neon > td { 
                            background-color: #fff8c6 !important; 
                            transition: background-color 0.8s ease; 
                        }
                        tr.fila-reciente-neon-removida > td { 
                            background-color: transparent !important; 
                            transition: background-color 0.8s ease; 
                        }
                    `;
                    document.head.appendChild(style);
                }

                // Contenedor Toasts
                let customNotifyContainer = document.getElementById('custom-notify-container');
                if (!customNotifyContainer) {
                    customNotifyContainer = document.createElement('div');
                    customNotifyContainer.id = 'custom-notify-container';
                    customNotifyContainer.style.cssText = 'position: fixed; top: 85px; right: 20px; z-index: 99999; display: flex; flex-direction: column; gap: 12px; width: 340px; max-height: calc(100vh - 100px); overflow-y: auto; overflow-x: hidden; pointer-events: none; padding-right: 5px;';
                    document.body.appendChild(customNotifyContainer);
                    const style = document.createElement('style');
                    style.innerHTML = '#custom-notify-container::-webkit-scrollbar { width: 0px; background: transparent; }';
                    document.body.appendChild(style);
                }

                function showSingleToast(e) {
                    const isRef = (e.tipo === 'reforzamiento' || (e.carrera && e.carrera.toUpperCase().includes('REFORZAMIENTO')));
                    const colorPrimary = isRef ? '#f39c12' : '#28a745';
                    const titulo = isRef ? '¡NUEVA INSCRIPCIÓN ESCOLAR!' : '¡NUEVA POSTULACIÓN UNIVERSITARIA!';
                    let fotoUrl = e.foto ? (e.foto.startsWith('http') ? e.foto : '{{ asset("storage") }}/' + e.foto) : 'https://ui-avatars.com/api/?name='+encodeURIComponent(e.nombre||'PS')+'&background='+colorPrimary.replace('#','')+'&color=fff';
                    const labelExtra = isRef ? 'Grado' : 'Carrera';
                    const valorExtra = isRef ? (e.grado || 'Por asignar') : (e.carrera || 'N/A');

                    const toast = document.createElement('div');
                    toast.style.cssText = `background:#fff; pointer-events:auto; border-radius:8px; box-shadow:0 8px 25px rgba(0,0,0,0.15); border-left:5px solid ${colorPrimary}; padding:15px; opacity:0; transform:translateX(100px); transition:all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); position:relative; overflow:hidden; flex-shrink:0;`;
                    toast.innerHTML = `
                        <button type="button" class="btn-close position-absolute top-0 end-0 m-2" style="font-size:10px;" onclick="this.parentElement.remove()"></button>
                        <div style="font-size:11px; font-weight:800; color:#555; margin-bottom:10px;">${titulo}</div>
                        <div class="d-flex align-items-center mb-1">
                            <div class="me-3 position-relative" style="width:55px; height:55px; flex-shrink:0;">
                                <img src="${fotoUrl}" class="rounded-circle shadow-sm" style="width:100%; height:100%; object-fit:cover; border:2px solid ${colorPrimary};">
                                <span class="position-absolute bottom-0 end-0 badge rounded-pill" style="background:${colorPrimary}; font-size:9px; border:1.5px solid white;">${isRef?'ESC':'UNI'}</span>
                            </div>
                            <div class="text-start" style="flex-grow:1;">
                                <h6 class="mb-1 fw-bold text-dark" style="font-size:14px;">${e.nombre || 'Nuevo Ingreso'}</h6>
                                <div class="text-muted" style="font-size:11.5px; line-height:1.4;">
                                    <div><i class="fas fa-id-card"></i> DNI: ${e.dni || '---'}</div>
                                    <div class="mt-1 text-dark"><i class="fas ${isRef?'fa-graduation-cap':'fa-university'}"></i> <b>${labelExtra}:</b> ${valorExtra}</div>
                                </div>
                            </div>
                        </div>
                        <div class="progress-bar-toast" style="position:absolute; bottom:0; left:0; height:4px; background:${colorPrimary}; width:100%; transition:width 8s linear;"></div>
                    `;
                    customNotifyContainer.prepend(toast);
                    void toast.offsetWidth;
                    toast.style.opacity = '1';
                    toast.style.transform = 'translateX(0)';
                    setTimeout(() => {
                        const pb = toast.querySelector('.progress-bar-toast');
                        if(pb) { void pb.offsetWidth; pb.style.width = '0%'; }
                    }, 50);
                    setTimeout(() => {
                        toast.style.opacity = '0';
                        toast.style.transform = 'translateX(50px)';
                        setTimeout(() => toast.remove(), 400);
                    }, 8000);
                }

                function updateNotifications() {
                    fetch('{{ route('notifications.fetch') }}').then(r => r.json()).then(d => renderNotifications(d.notifications, d.unread_count));
                }

                function renderNotifications(notifications, unreadCount) {
                    if (unreadCount > 0) {
                        countBadge.innerText = unreadCount > 9 ? '+9' : unreadCount;
                        countBadge.classList.remove('d-none');
                    } else { countBadge.classList.add('d-none'); }
                    if (notifications.length === 0) {
                        itemsContainer.innerHTML = '<div class="text-center p-3 text-muted">Vacio</div>';
                        return;
                    }
                    let html = '';
                    notifications.forEach(noti => {
                        html += `<a href="/notificaciones/${noti.id}/read" class="dropdown-item notify-item border-bottom">
                            <div class="notify-icon bg-${noti.data.color || 'primary'}"><i class="uil ${noti.data.icon || 'uil-bell'}"></i></div>
                            <p class="notify-details">${noti.data.title}</p>
                            <p class="text-muted mb-0 user-msg"><small>${noti.data.message}</small></p>
                        </a>`;
                    });
                    itemsContainer.innerHTML = html;
                }

                updateNotifications();
                // Ejecutar resaltado inicial al cargar la página
                setTimeout(applyPersistentHighlights, 1000); 

                if (typeof window.Echo !== 'undefined' && typeof window.Echo.private === 'function') {
                    const userId = {{ Auth::id() ?? 'null' }};
                    if (userId) {
                        window.Echo.private(`App.Models.User.${userId}`).notification(() => {
                            playNotificationSound();
                            updateNotifications();
                        });
                    }

                    window.Echo.channel('postulaciones').listen('.NuevaPostulacionCreada', (e) => {
                        playNotificationSound();
                        showSingleToast(e);
                        updateNotifications();
                        
                        // Guardar en memoria persistente
                        let unseen = JSON.parse(localStorage.getItem('newRecordsUnseen') || '[]');
                        unseen.push(e.dni || e.nombre);
                        localStorage.setItem('newRecordsUnseen', JSON.stringify([...new Set(unseen)]));

                        // Recargar tablas y aplicar resaltado
                        tablesToCheck.forEach(tableInfo => {
                            let dt = null;
                            if (typeof window !== 'undefined' && tableInfo.globalVar && typeof window[tableInfo.globalVar.replace('window.','')] !== 'undefined') {
                                dt = window[tableInfo.globalVar.replace('window.','')];
                            } else if (typeof $ !== 'undefined' && $.fn.DataTable.isDataTable(tableInfo.id)) {
                                dt = $(tableInfo.id).DataTable();
                            }
                            if (dt) dt.ajax.reload(applyPersistentHighlights, false);
                        });
                    });
                } else {
                    console.warn('⚠️ Laravel Echo no está inicializado o no tiene el método private.');
                }
            });
        </script>

        <!-- LOGO -->
        <div class="logo-box">
            <a href="{{ url('/dashboard') }}" class="logo logo-dark">
                <span class="logo-sm">
                    <img src="{{ asset('assets_cepre/img/logo/logo2_0.png') }}" alt="" height="35">
                </span>
                <span class="logo-lg">
                    <img src="{{ asset('assets_cepre/img/logo/logo2_0.png') }}" alt="" height="50">
                </span>
            </a>

            <a href="{{ url('/') }}" class="logo logo-light">
                <span class="logo-sm">
                    <img src="{{ asset('assets_cepre/img/logo/logo2_0.png') }}" alt="" height="30">
                </span>
                <span class="logo-lg">
                    <img src="{{ asset('assets_cepre/img/logo/logo2_0.png') }}" alt="" height="50">
                </span>
            </a>
        </div>

        <ul class="list-unstyled topnav-menu topnav-menu-left m-0">
            <li>
                <button class="button-menu-mobile">
                    <i data-feather="menu"></i>
                </button>
            </li>

            <li>
                <!-- Mobile menu toggle (Horizontal Layout)-->
                <a class="navbar-toggle nav-link" data-bs-toggle="collapse" data-bs-target="#topnav-menu-content">
                    <div class="lines">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                </a>
                <!-- End mobile menu toggle-->
            </li>

            <li class="dropdown d-none d-xl-block">
                <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#" role="button"
                    aria-haspopup="false" aria-expanded="false">
                    Crear Nuevo
                    <i class="uil uil-angle-down"></i>
                </a>
                <div class="dropdown-menu">
                    <!-- item-->
                    <a href="javascript:void(0);" class="dropdown-item">
                        <i class="uil uil-bag me-1"></i><span>Ciclos Académicos</span>
                    </a>

                    <!-- item-->
                    @if (Auth::user()->hasPermission('users.view'))
                    <a href="{{ route('programas.index') }}" class="dropdown-item">
                        <i class="uil uil-box me-1"></i><span>Programas Académicos</span>
                    </a>
                    @endif

                    <!-- item-->
                    <a href="javascript:void(0);" class="dropdown-item">
                        <i class="uil uil-user-plus me-1"></i><span>Crear Usuarios</span>
                    </a>

                    <!-- item-->
                    <a href="javascript:void(0);" class="dropdown-item">
                        <i class="uil uil-chart-pie me-1"></i><span>Generar Reportes</span>
                    </a>

                    <!-- item-->
                    @can('boletines.view')
                    <a href="{{ route('boletines.index') }}" class="dropdown-item">
                        <i class="uil uil-archive-alt me-1"></i><span>Boletines Académicos</span>
                    </a>
                    @endcan
                    
                    <!-- item-->
                    @if (Auth::user()->hasPermission('biometria.view'))
                    <a href="{{ route('biometria.index') }}" class="dropdown-item">
                        <i class="uil uil-fingerprint me-1"></i><span>Enrolamiento Biométrico</span>
                    </a>
                    @endif

                    <!-- item-->
                    <a href="javascript:void(0);" class="dropdown-item">
                        <i class="uil uil-cog me-1"></i><span>Configuración</span>
                    </a>

                    <div class="dropdown-divider"></div>

                    <!-- item-->
                    <a href="javascript:void(0);" class="dropdown-item">
                        <i class="uil uil-question-circle me-1"></i><span>Soporte Code_BonFer</span>
                    </a>

                </div>
            </li>

        </ul>

        <div class="clearfix"></div>
    </div>
</div>
<!-- end Topbar -->

<!-- ========== Left Sidebar Start ========== -->
@if (Auth::user()->hasRole('profesor'))
    @include('layouts.sidebar-profesor')
@else
    <div class="left-side-menu">

        <div class="h-100" data-simplebar>

            <!-- User box -->
            <div class="user-box text-center shadow-none border-0">
                @php
                    $userAvatar = Auth::user()->foto_perfil 
                        ? asset('storage/' . Auth::user()->foto_perfil) 
                        : 'https://ui-avatars.com/api/?name=' . urlencode(Auth::user()->nombre . ' ' . Auth::user()->apellido_paterno) . '&background=e91e63&color=fff&size=128';
                @endphp
                <img src="{{ $userAvatar }}" alt="user-image"
                    title="{{ Auth::user()->nombre }}" class="rounded-circle avatar-md shadow-lg border border-2 border-white">
                <div class="dropdown">
                    <a href="javascript: void(0);" class="dropdown-toggle h5 mt-2 mb-1 d-block"
                        data-bs-toggle="dropdown">{{ Auth::user()->nombre }} {{ Auth::user()->apellido_paterno }}</a>
                    <div class="dropdown-menu user-pro-dropdown">

                        <a href="{{ route('perfil.index') }}" class="dropdown-item notify-item">
                            <i data-feather="user" class="icon-dual icon-xs me-1"></i><span>Mi Perfil</span>
                        </a>
                        <a href="{{ route('perfil.configuracion') }}" class="dropdown-item notify-item">
                            <i data-feather="settings" class="icon-dual icon-xs me-1"></i><span>Configuración</span>
                        </a>
                        <a href="javascript:void(0);" class="dropdown-item notify-item">
                            <i data-feather="help-circle" class="icon-dual icon-xs me-1"></i><span>Ayuda</span>
                        </a>
                        <div class="dropdown-divider"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <a href="{{ route('logout') }}" class="dropdown-item notify-item"
                                onclick="event.preventDefault(); this.closest('form').submit();">
                                <i data-feather="log-out" class="icon-dual icon-xs me-1"></i><span>Cerrar
                                    Sesión</span>
                            </a>
                        </form>
                    </div>
                </div>
                <p>
                    @foreach (Auth::user()->roles as $role)
                        {{ $role->nombre }}{{ !$loop->last ? ', ' : '' }}
                    @endforeach
                </p>
            </div>

            <!--- Sidemenu -->
            <div id="sidebar-menu">

                <ul class="side-menu">

                    <!-- <li class="menu-title">Navegación</li> -->

                    <li>
                        <a href="{{ route('dashboard') }}">
                            <i data-feather="home"></i>
                            <span> Dashboard </span>
                        </a>
                    </li>

                    <li class="menu-title mt-2">Módulos</li>

                    <!-- Módulo Usuarios - Solo visible si tiene permiso -->
                    @if (Auth::user()->hasPermission('users.view'))
                        <li>
                            <a href="#sidebarUsuarios" data-bs-toggle="collapse">
                                <i data-feather="users"></i>
                                <span> Usuarios </span>
                                <span class="menu-arrow"></span>
                            </a>
                            <div class="collapse" id="sidebarUsuarios">
                                <ul class="nav-second-level">
                                    <li><a href="{{ route('usuarios.index') }}">Listar Usuarios</a></li>
                                    @if (Auth::user()->hasPermission('users.create'))
                                        <li><a href="{{ route('usuarios.create') }}">Crear Usuario</a></li>
                                    @endif
                                </ul>
                            </div>
                        </li>
                    @endif


                    <!-- Módulo Postulaciones - Solo visible si tiene permiso -->
                    @if (Auth::user()->hasPermission('postulaciones.view'))
                        <li>
                            <a href="#sidebarPostulaciones" data-bs-toggle="collapse">
                                <i data-feather="file-text"></i>
                                <span> Postulaciones </span>
                                <span class="menu-arrow"></span>
                            </a>
                            <div class="collapse" id="sidebarPostulaciones">
                                <ul class="nav-second-level">
                                    <li><a href="{{ route('postulaciones.index') }}">Ver Postulaciones</a></li>
                                </ul>
                            </div>
                        </li>
                    @endif


                    <!-- Módulo Reforzamiento - Gestión Especializada -->
                    @if (Auth::user()->hasPermission('reforzamiento.view'))
                        <li>
                            <a href="#sidebarReforzamiento" data-bs-toggle="collapse">
                                <i data-feather="book-open"></i>
                                <span> Reforzamiento </span>
                                <span class="menu-arrow"></span>
                            </a>
                            <div class="collapse" id="sidebarReforzamiento">
                                <ul class="nav-second-level">
                                    <li><a href="{{ route('admin.reforzamiento.index') }}">Gestión de Alumnos</a></li>
                                </ul>
                            </div>
                        </li>
                    @endif

                    <!-- Módulo Resultados de Exámenes -->
                    @if (Auth::user()->hasPermission('resultados-examenes.view'))
                        <li>
                            <a href="#sidebarResultados" data-bs-toggle="collapse">
                                <i data-feather="file-text"></i>
                                <span> Resultados de Exámenes </span>
                                <span class="menu-arrow"></span>
                            </a>
                            <div class="collapse" id="sidebarResultados">
                                <ul class="nav-second-level">
                                    <li><a href="{{ route('resultados-examenes.index') }}">Listar Resultados</a></li>
                                    @if (Auth::user()->hasPermission('resultados-examenes.create'))
                                        <li><a href="{{ route('resultados-examenes.create') }}">Nuevo Resultado</a></li>
                                    @endif
                                </ul>
                            </div>
                        </li>
                    @endif

                    <!-- Módulo Roles - Solo visible si tiene permiso -->
                    @if (Auth::user()->hasPermission('roles.view'))
                        <li>
                            <a href="#sidebarRoles" data-bs-toggle="collapse">
                                <i data-feather="shield"></i>
                                <span> Roles y Permisos </span>
                                <span class="menu-arrow"></span>
                            </a>
                            <div class="collapse" id="sidebarRoles">
                                <ul class="nav-second-level">
                                    <li><a href="{{ route('roles.index') }}">Listar Roles</a></li>
                                    @if (Auth::user()->hasPermission('roles.create'))
                                        <li><a href="{{ route('roles.create') }}">Crear Rol</a></li>
                                    @endif
                                    @if (Auth::user()->hasPermission('roles.assign_permissions'))
                                        <li><a href="{{ route('roles.permisos') }}">Asignar Permisos</a></li>
                                    @endif
                                </ul>
                            </div>
                        </li>
                    @endif


                    <!-- Módulo Asistencia - Accesible para todos con sus permisos específicos -->
                    @if (Auth::user()->hasPermission('attendance.view') ||
                            Auth::user()->hasPermission('attendance.register') ||
                            Auth::user()->hasPermission('attendance.edit') ||
                            Auth::user()->hasPermission('attendance.reports') ||
                            Auth::user()->hasPermission('attendance.realtime'))
                        <li>
                            <a href="#sidebarAsistencia" data-bs-toggle="collapse">
                                <i data-feather="calendar"></i>
                                <span> Asistencia </span>
                                <span class="menu-arrow"></span>
                            </a>
                            <div class="collapse" id="sidebarAsistencia">
                                <ul class="nav-second-level">
                                    @if (Auth::user()->hasPermission('attendance.view'))
                                        <li><a href="{{ route('asistencia.index') }}">Ver Registros</a></li>
                                    @endif

                                    @if (Auth::user()->hasPermission('attendance.register'))
                                        <li><a href="{{ route('asistencia.registrar') }}">Registrar Asistencia</a>
                                        </li>
                                    @endif

                                    @if (Auth::user()->hasPermission('attendance.edit'))
                                        <li><a href="{{ route('asistencia.editar') }}">Editar Registros</a></li>
                                    @endif

                                    @if (Auth::user()->hasPermission('attendance.export'))
                                        <li><a href="{{ route('asistencia.exportar') }}">Exportar Registros</a></li>
                                    @endif

                                    @if (Auth::user()->hasPermission('attendance.reports'))
                                        <li><a href="{{ route('asistencia.reportes') }}">Reportes y Estadísticas</a>
                                        </li>
                                    @endif

                                    <!-- Nueva opción para monitoreo en tiempo real -->
                                    @if (Auth::user()->hasPermission('attendance.realtime'))
                                        <li><a href="{{ route('asistencia.tiempo-real') }}">Monitoreo en Tiempo
                                                Real</a>
                                        </li>
                                    @endif
                                </ul>
                            </div>
                        </li>
                    @endif

                    <!-- Módulo Biométrico -->
                    @if (Auth::user()->hasPermission('biometria.view'))
                        <li>
                            <a href="{{ route('biometria.index') }}">
                                <i data-feather="cpu"></i>
                                <span> Gestión Biométrica </span>
                            </a>
                        </li>
                    @endif


                    <!-- Módulo Parentescos - Solo visible si tiene permiso -->
                    @if (Auth::user()->hasPermission('parentescos.view'))
                        <li>
                            <a href="#sidebarParentescos" data-bs-toggle="collapse">
                                <i data-feather="home"></i>
                                <span> Parentescos </span>
                                <span class="menu-arrow"></span>
                            </a>
                            <div class="collapse" id="sidebarParentescos">
                                <ul class="nav-second-level">
                                    <li><a href="{{ route('parentescos.index') }}">Listar Parentescos</a></li>
                                    @if (Auth::user()->hasPermission('parentescos.create'))
                                        <li><a href="{{ route('parentescos.create') }}">Crear Parentesco</a></li>
                                    @endif
                                    <!-- Eliminada la opción problemática de Editar Parentescos -->
                                </ul>
                            </div>
                        </li>
                    @endif



                    <!-- Módulo Ciclos Académicos -->
                    @if (Auth::user()->hasPermission('ciclos.view'))
                        <li>
                            <a href="#sidebarCiclos" data-bs-toggle="collapse">
                                <i data-feather="calendar"></i>
                                <span> Ciclos Académicos </span>
                                <span class="menu-arrow"></span>
                            </a>
                            <div class="collapse" id="sidebarCiclos">
                                <ul class="nav-second-level">
                                    <li><a href="{{ route('ciclos.index') }}">Listar Ciclos</a></li>
                                    @if (Auth::user()->hasPermission('ciclos.create'))
                                        <li><a href="{{ route('ciclos.create') }}">Crear Ciclo</a></li>
                                    @endif
                                </ul>
                            </div>
                        </li>
                    @endif

                    <!-- Módulo Carreras -->
                    @if (Auth::user()->hasPermission('carreras.view'))
                        <li>
                            <a href="#sidebarCarreras" data-bs-toggle="collapse">
                                <i data-feather="award"></i>
                                <span> Carreras </span>
                                <span class="menu-arrow"></span>
                            </a>
                            <div class="collapse" id="sidebarCarreras">
                                <ul class="nav-second-level">
                                    <li><a href="{{ route('carreras.index') }}">Listar Carreras</a></li>
                                    @if (Auth::user()->hasPermission('carreras.create'))
                                        <li><a href="{{ route('carreras.create') }}">Crear Carrera</a></li>
                                    @endif
                                </ul>
                            </div>
                        </li>
                    @endif


                    <!-- Módulo Turnos -->
                    @if (Auth::user()->hasPermission('turnos.view'))
                        <li>
                            <a href="#sidebarTurnos" data-bs-toggle="collapse">
                                <i data-feather="clock"></i>
                                <span> Turnos </span>
                                <span class="menu-arrow"></span>
                            </a>
                            <div class="collapse" id="sidebarTurnos">
                                <ul class="nav-second-level">
                                    <li><a href="{{ route('turnos.index') }}">Listar Turnos</a></li>
                                    @if (Auth::user()->hasPermission('turnos.create'))
                                        <li><a href="{{ route('turnos.create') }}">Crear Turno</a></li>
                                    @endif
                                </ul>
                            </div>
                        </li>
                    @endif

                    <!-- Módulo Aulas -->
                    @if (Auth::user()->hasPermission('aulas.view'))
                        <li>
                            <a href="#sidebarAulas" data-bs-toggle="collapse">
                                <i data-feather="home"></i>
                                <span> Aulas </span>
                                <span class="menu-arrow"></span>
                            </a>
                            <div class="collapse" id="sidebarAulas">
                                <ul class="nav-second-level">
                                    <li><a href="{{ route('aulas.index') }}">Listar Aulas</a></li>
                                    @if (Auth::user()->hasPermission('aulas.create'))
                                        <li><a href="{{ route('aulas.create') }}">Crear Aula</a></li>
                                    @endif
                                    @if (Auth::user()->hasPermission('aulas.availability'))
                                        <li><a href="{{ route('aulas.disponibilidad') }}">Disponibilidad</a></li>
                                    @endif
                                </ul>
                            </div>
                        </li>
                    @endif

                    <!-- Módulo Inscripciones -->
                    @if (Auth::user()->hasPermission('inscripciones.view'))
                        <li>
                            <a href="#sidebarInscripciones" data-bs-toggle="collapse">
                                <i data-feather="file-text"></i>
                                <span> Inscripciones </span>
                                <span class="menu-arrow"></span>
                            </a>
                            <div class="collapse" id="sidebarInscripciones">
                                <ul class="nav-second-level">
                                    <li><a href="{{ route('inscripciones.index') }}">Listar Inscripciones</a></li>
                                    @if (Auth::user()->hasPermission('inscripciones.create'))
                                        <li><a href="{{ route('inscripciones.create') }}">Nueva Inscripción</a></li>
                                    @endif
                                    @if (Auth::user()->hasPermission('inscripciones.reports'))
                                        <li><a href="{{ route('inscripciones.reportes') }}">Reportes</a></li>
                                    @endif
                                </ul>
                            </div>
                        </li>
                    @endif

                    <!-- Módulo Carnets -->
                    @if (Auth::user()->hasPermission('carnets.view'))
                        <li>
                            <a href="#sidebarCarnets" data-bs-toggle="collapse">
                                <i data-feather="credit-card"></i>
                                <span> Carnets </span>
                                <span class="menu-arrow"></span>
                            </a>
                            <div class="collapse" id="sidebarCarnets">
                                <ul class="nav-second-level">
                                    <li><a href="{{ route('carnets.index') }}">Ver Carnets</a></li>
                                    @if (Auth::user()->hasPermission('carnets.generate'))
                                        <li><a href="{{ route('carnets.index') }}#generar">Generar Carnets</a></li>
                                    @endif
                                    @if (Auth::user()->hasPermission('carnets.print'))
                                        <li><a href="{{ route('carnets.index') }}#imprimir">Imprimir Carnets</a></li>
                                    @endif
                                    @if (Auth::user()->hasPermission('carnets.templates.view'))
                                        <li><a href="{{ route('carnets.templates.index') }}">Plantillas de Carnets</a></li>
                                    @endif
                                    @if (Auth::user()->hasPermission('carnets.reports'))
                                        <li><a href="{{ route('carnets.index') }}#reportes">Reportes</a></li>
                                    @endif
                                </ul>
                            </div>
                        </li>
                    @endif

                    <!-- Módulo Constancias -->
                    @if (Auth::user()->hasPermission('constancias.generar-estudios') ||
                            Auth::user()->hasPermission('constancias.generar-vacante'))
                        <li>
                            <a href="#sidebarConstancias" data-bs-toggle="collapse">
                                <i data-feather="file-text"></i>
                                <span> Constancias </span>
                                <span class="menu-arrow"></span>
                            </a>
                            <div class="collapse" id="sidebarConstancias">
                                <ul class="nav-second-level">
                                    <li><a href="{{ route('constancias.index') }}">Ver Mis Constancias</a></li>
                                    @if (Auth::user()->hasPermission('constancias.generar-estudios'))
                                        <li><a href="{{ route('constancias.estudios.generar', ['inscripcion' => 1]) }}">Constancia de Estudios</a></li>
                                    @endif
                                    @if (Auth::user()->hasPermission('constancias.generar-vacante'))
                                        <li><a href="{{ route('constancias.vacante.generar', ['inscripcion' => 1]) }}">Constancia de Vacante</a></li>
                                    @endif
                                </ul>
                            </div>
                        </li>
                    @endif

                    <!-- ✅ MÓDULO ANUNCIOS - CÓDIGOS CORRECTOS CON GUIONES BAJOS -->
                    @if (Auth::user()->hasPermission('announcements_view') ||
                            Auth::user()->hasPermission('announcements_create') ||
                            Auth::user()->hasPermission('announcements_edit') ||
                            Auth::user()->hasPermission('announcements_delete'))
                        <li>
                            <a href="#sidebarAnuncios" data-bs-toggle="collapse">
                                <i data-feather="calendar"></i>
                                <span> Anuncios </span>
                                <span class="menu-arrow"></span>
                            </a>
                            <div class="collapse" id="sidebarAnuncios">
                                <ul class="nav-second-level">
                                    @if (Auth::user()->hasPermission('announcements_view'))
                                        <li><a href="{{ route('anuncios.index') }}">Ver Anuncios</a></li>
                                    @endif

                                    @if (Auth::user()->hasPermission('announcements_create'))
                                        <li><a href="{{ route('anuncios.create') }}">Crear Anuncio</a></li>
                                    @endif
                                </ul>
                            </div>
                        </li>
                    @endif
                    <li class="menu-title mt-2">Centro de Analítica y Reportes</li>
                    <li>
                        <a href="#sidebarReportesPower" data-bs-toggle="collapse">
                            <i data-feather="bar-chart-2"></i>
                            <span class="badge bg-soft-success text-success float-end">PRO</span>
                            <span> Inteligencia de Datos </span>
                            <span class="menu-arrow"></span>
                        </a>
                        <div class="collapse" id="sidebarReportesPower">
                            <ul class="nav-second-level">
                                @if (Auth::user()->hasPermission('reportes.estadisticos.ver'))
                                    <li>
                                        <a href="{{ route('reportes.estadisticos.index') }}">
                                            <i class="mdi mdi-view-dashboard-outline me-1"></i> Dashboard Estadístico
                                        </a>
                                    </li>
                                @endif
                                
                                @if (Auth::user()->hasPermission('reportes.financieros.ver'))
                                    <li>
                                        <a href="{{ route('reportes.financieros.index') }}">
                                            <i class="mdi mdi-cash-multiple me-1"></i> Análisis Financiero
                                        </a>
                                    </li>
                                @endif

                                @if (Auth::user()->hasPermission('postulaciones.reports'))
                                    <li>
                                        <a href="{{ route('postulaciones.reportes.completos') }}">
                                            <i class="mdi mdi-account-details me-1"></i> Reporte Postulantes Completo
                                        </a>
                                    </li>
                                    <li>
                                        <a href="{{ route('postulaciones.reportes.resumen') }}">
                                            <i class="mdi mdi-table-eye me-1"></i> Reporte Postulantes Resumido
                                        </a>
                                    </li>
                                @endif

                                @if (Auth::user()->hasPermission('postulaciones.reportes.inhabilitados'))
                                    <li>
                                        <a href="{{ route('postulaciones.reportes.inhabilitados') }}">
                                            <i class="mdi mdi-account-off-outline me-1"></i> Alumnos Inhabilitados
                                        </a>
                                    </li>
                                @endif
                                
                                @if (Auth::user()->hasPermission('attendance.reports'))
                                    <li>
                                        <a href="{{ route('asistencia.reportes') }}">
                                            <i class="mdi mdi-calendar-check me-1"></i> Reportes de Asistencia
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        </div>
                    </li>

                    <li class="menu-title mt-2">Modulos Docentes Cepre</li>

                    {{-- ============================== --}}
                    {{-- Módulo: Carga Horaria Docente --}}
                    {{-- ============================== --}}
                    @if (Auth::user()->hasPermission('carga-horaria.view'))
                        <li>
                            <a href="{{ route('carga-horaria.index') }}">
                                <i data-feather="clock"></i>
                                <span> Carga Horaria </span>
                            </a>
                        </li>
                    @endif
                    {{-- ============================== --}}
                    {{-- Módulo: Horarios Docentes --}}
                    {{-- ============================== --}}
                    @if (Auth::user()->hasPermission('horarios-docentes.view'))
                        <li>
                            <a href="#sidebarHorariosDocentes" data-bs-toggle="collapse">
                                <i data-feather="calendar"></i>
                                <span> Horarios Docentes </span>
                                <span class="menu-arrow"></span>
                            </a>
                            <div class="collapse" id="sidebarHorariosDocentes">
                                <ul class="nav-second-level">
                                    <li><a href="{{ route('horarios-docentes.index') }}">Listar Horarios</a></li>
                                    @if (Auth::user()->hasPermission('horarios-docentes.create'))
                                        <li><a href="{{ route('horarios-docentes.create') }}">Asignar Horario</a></li>
                                    @endif
                                </ul>
                            </div>
                        </li>
                    @endif

                    {{-- ============================== --}}
                    {{-- Módulo: Pagos Docentes --}}
                    {{-- ============================== --}}
                    @if (Auth::user()->hasPermission('pagos-docentes.view'))
                        <li>
                            <a href="#sidebarPagosDocentes" data-bs-toggle="collapse">
                                <i data-feather="dollar-sign"></i>
                                <span> Pagos Docentes </span>
                                <span class="menu-arrow"></span>
                            </a>
                            <div class="collapse" id="sidebarPagosDocentes">
                                <ul class="nav-second-level">
                                    <li><a href="{{ route('pagos-docentes.index') }}">Ver Pagos</a></li>
                                    @if (Auth::user()->hasPermission('pagos-docentes.create'))
                                        <li><a href="{{ route('pagos-docentes.create') }}">Registrar Pago</a></li>
                                    @endif
                                </ul>
                            </div>
                        </li>
                    @endif

                    {{-- ============================== --}}
                    {{-- Módulo: Asistencia Docente --}}
                    {{-- ============================== --}}
                    @if (Auth::user()->hasPermission('asistencia-docente.view'))
                        <li>
                            <a href="#sidebarAsistenciaDocente" data-bs-toggle="collapse">
                                <i data-feather="check-square"></i>
                                <span> Asistencia Docente </span>
                                <span class="menu-arrow"></span>
                            </a>
                            <div class="collapse" id="sidebarAsistenciaDocente">
                                <ul class="nav-second-level">
                                    <li>
                                        <a href="{{ route('asistencia-docente.index') }}">Ver Asistencia Docente</a>
                                    </li>

                                    @if (Auth::user()->hasPermission('asistencia-docente.create'))
                                        <li>
                                            <a href="{{ route('asistencia-docente.create') }}">Registrar Asistencia
                                                Docente</a>
                                        </li>
                                    @endif

                                    @if (Auth::user()->hasPermission('asistencia-docente.edit'))
                                        <li>
                                            <a href="{{ route('asistencia-docente.index') }}">Editar Asistencia
                                                Docente</a>
                                        </li>
                                    @endif

                                    @if (Auth::user()->hasPermission('asistencia-docente.export'))
                                        <li>
                                            <a href="{{ route('asistencia-docente.exportar') }}">Exportar Asistencia
                                                Docente</a>
                                        </li>
                                    @endif

                                    @if (Auth::user()->hasPermission('asistencia-docente.reports'))
                                        <li>
                                            <a href="{{ route('asistencia-docente.reports') }}">Reportes de Asistencia
                                                Docente</a>
                                        </li>
                                    @endif

                                    @if (Auth::user()->hasPermission('asistencia-docente.monitor'))
                                        <li>
                                            <a href="{{ route('asistencia-docente.monitor') }}">Monitorear Asistencia
                                                en Tiempo Real (Docente)</a>
                                        </li>
                                    @endif
                                </ul>
                            </div>
                        </li>
                    @endif
                    
                    {{-- ============================== --}}
                    {{-- Módulo: Boletines Académicos --}}
                    {{-- ============================== --}}
                    @can('boletines.view')
                        <li>
                            <a href="{{ route('boletines.index') }}">
                                <i data-feather="archive"></i>
                                <span> Boletines Académicos </span>
                            </a>
                        </li>
                    @endcan

                    {{-- ============================== --}}
                    {{-- Módulo: Material Académico --}}
                    {{-- ============================== --}}
                    @if (Auth::user()->hasPermission('material-academico.ver'))
                        <li>
                            <a href="#sidebarMaterialAcademico" data-bs-toggle="collapse">
                                <i data-feather="book"></i>
                                <span> Material Académico </span>
                                <span class="menu-arrow"></span>
                            </a>
                            <div class="collapse" id="sidebarMaterialAcademico">
                                <ul class="nav-second-level">
                                    <li><a href="{{ route('materiales-academicos.index') }}">Ver Material</a></li>
                                    @if (Auth::user()->hasPermission('material-academico.crear'))
                                        <li><a href="{{ route('materiales-academicos.crear') }}">Subir Material</a></li>
                                    @endif
                                </ul>
                            </div>
                        </li>
                    @endif

                    {{-- ============================== --}}
                    {{-- Módulo: Cursos --}}
                    {{-- ============================== --}}
                    @if (Auth::user()->hasPermission('cursos.view'))
                        <li>
                            <a href="#sidebarCursos" data-bs-toggle="collapse">
                                <i data-feather="book-open"></i>
                                <span> Cursos </span>
                                <span class="menu-arrow"></span>
                            </a>
                            <div class="collapse" id="sidebarCursos">
                                <ul class="nav-second-level">
                                    <li><a href="{{ route('cursos.index') }}">Listado de Cursos</a></li>
                                    @if (Auth::user()->hasPermission('cursos.create'))
                                        <li><a href="{{ route('cursos.create') }}">Registrar Curso</a></li>
                                    @endif
                                </ul>
                            </div>
                        </li>
                    @endif

                    {{-- ============================== --}}
                    {{-- Módulo: Tarjetas Pre Universitario --}}
                    {{-- ============================== --}}
                    @if (Auth::user()->hasPermission('tarjetas-preuni.view'))
                        <li>
                            <a href="#sidebarTarjetasPreuni" data-bs-toggle="collapse">
                                <i data-feather="credit-card"></i>
                                <span> Tarjetas Pre Universitario </span>
                                <span class="menu-arrow"></span>
                            </a>
                            <div class="collapse" id="sidebarTarjetasPreuni">
                                <ul class="nav-second-level">
                                    <li><a href="{{ route('tarjetas-preuni.index') }}">Ver Tarjetas</a></li>
                                    @if (Auth::user()->hasPermission('tarjetas-preuni.generate'))
                                        <li><a href="{{ route('tarjetas-preuni.index') }}#generar">Generar Tarjetas</a></li>
                                    @endif
                                    @if (Auth::user()->hasPermission('tarjetas-preuni.print'))
                                        <li><a href="{{ route('tarjetas-preuni.index') }}#imprimir">Imprimir Tarjetas</a></li>
                                    @endif
                                </ul>
                            </div>
                        </li>
                    @endif



                    <li class="menu-title mt-2">Configuración</li>

                    <!-- Módulo Programas Académicos -->
                    @if (Auth::user()->hasPermission('users.view'))
                        <li>
                            <a href="{{ route('programas.index') }}">
                                <i data-feather="box"></i>
                                <span> Programas Académicos </span>
                            </a>
                        </li>
                    @endif

                    <!-- Módulo Auditoría - Reubicado para ser más administrativo -->
                    @if (Auth::user()->hasPermission('auditoria.view') || Auth::user()->hasPermission('users.view'))
                        <li>
                            <a href="{{ route('auditoria.index') }}">
                                <i data-feather="activity"></i>
                                <span> Auditoría del Sistema </span>
                            </a>
                        </li>
                    @endif

                    <!-- Ajustes de perfil - Visible para todos -->
                    <li>
                        <a href="{{ route('perfil.index') }}">
                            <i data-feather="user"></i>
                            <span> Mi Perfil </span>
                        </a>
                    </li>

                    <li>
                        <a href="{{ route('perfil.index') }}">
                            <i data-feather="settings"></i>
                            <span> Ajustes </span>
                        </a>
                    </li>

                    <li>
                        <a href="javascript:void(0);">
                            <i data-feather="help-circle"></i>
                            <span> Ayuda </span>
                        </a>
                    </li>

                </ul>

            </div>
            <!-- End Sidebar -->

            <div class="clearfix"></div>

        </div>
        <!-- Sidebar -left -->

    </div>
    <!-- Left Sidebar End -->
@endif
