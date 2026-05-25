<!-- Scripts Específicos de la Home CEPRE -->
<script>
    // ====================================
    // CARRUSEL (HERO SECTION) LOGIC
    // ====================================
    let currentSlide = 0;
    let slides;
    let totalSlides;
    let slidesContainer;
    let dotsContainer;
    let slideInterval;
    const autoPlayTime = 5000;

    function initCarousel() {
        slides = document.querySelectorAll('.carousel-slide');
        totalSlides = slides.length;
        slidesContainer = document.getElementById('carouselSlides');
        dotsContainer = document.getElementById('carouselDots');
        
        if (!slidesContainer) return;

        createDots();
        showSlide(currentSlide);
    }

    function createDots() {
        if (!dotsContainer) return;
        dotsContainer.innerHTML = '';
        for (let i = 0; i < totalSlides; i++) {
            const dot = document.createElement('span');
            dot.classList.add('dot');
            dot.setAttribute('data-slide-index', i);
            dot.onclick = () => showSlide(i);
            dotsContainer.appendChild(dot);
        }
    }

    function showSlide(index, pause = false) {
        if (!slidesContainer) return;
        clearInterval(slideInterval);
        if (!pause) startAutoPlay();

        if (index >= totalSlides) {
            currentSlide = 0;
        } else if (index < 0) {
            currentSlide = totalSlides - 1;
        } else {
            currentSlide = index;
        }

        const offset = -currentSlide * 100;
        slidesContainer.style.transform = `translateX(${offset}%)`;

        slides.forEach((slide, i) => {
            slide.classList.remove('active');
            if (i === currentSlide) {
                slide.classList.add('active');
            }
        });

        document.querySelectorAll('.dot').forEach((dot, i) => {
            dot.classList.remove('active');
            if (i === currentSlide) {
                dot.classList.add('active');
            }
        });
    }

    function changeSlide(n) {
        showSlide(currentSlide + n);
    }

    function startAutoPlay() {
        clearInterval(slideInterval);
        slideInterval = setInterval(() => {
            showSlide(currentSlide + 1);
        }, autoPlayTime);
    }

    // ====================================
    // Inicialización 3D y Carga (Partículas en el Fondo Maestro)
    // ====================================
    let scene, camera, renderer, particles, particleMaterial, particleCount;
    let container = document.getElementById('notebook-particles-container');
    let animationFrameId;
    let isVisible = false;
    let mouse3D = new THREE.Vector3(0, 0, 0);
    let mouse3DTarget = new THREE.Vector3(0, 0, 0);
    let initialPositions = [];
    let particleSpeeds = [];

    function initThreeJS() {
        container = document.getElementById('notebook-particles-container');
        if (!container || typeof THREE === 'undefined') return;
        
        // Solo inicializar en pantallas de escritorio
        if (window.innerWidth <= 768) return;

        scene = new THREE.Scene();
        scene.background = null;
        
        camera = new THREE.PerspectiveCamera(60, container.clientWidth / container.clientHeight, 1, 1000);
        camera.position.z = 8;

        renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
        renderer.setSize(container.clientWidth, container.clientHeight);
        renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
        container.appendChild(renderer.domElement);
        
        particleCount = 280; // Sutil y ligero para rendimiento
        const geometry = new THREE.BufferGeometry();
        const positions = [];
        const colors = [];
        
        const color1 = new THREE.Color('#ec008c'); // Magenta
        const color2 = new THREE.Color('#00aeef'); // Cyan
        const color3 = new THREE.Color('#93c01f'); // Verde
        
        for (let i = 0; i < particleCount; i++) {
            positions.push(
                (Math.random() - 0.5) * 22,
                (Math.random() - 0.5) * 35,
                (Math.random() - 0.5) * 15
            );
            
            const rand = Math.random();
            const color = rand < 0.4 ? color1 : (rand < 0.8 ? color2 : color3);
            colors.push(color.r, color.g, color.b);
        }
        
        geometry.setAttribute('position', new THREE.Float32BufferAttribute(positions, 3));
        geometry.setAttribute('color', new THREE.Float32BufferAttribute(colors, 3));

        // Registrar posiciones iniciales y velocidades de órbita
        const arr = geometry.attributes.position.array;
        initialPositions = [];
        particleSpeeds = [];
        for (let i = 0; i < particleCount; i++) {
            initialPositions.push({
                x: arr[i * 3],
                y: arr[i * 3 + 1],
                z: arr[i * 3 + 2]
            });
            particleSpeeds.push({
                speed: Math.random() * 0.015 + 0.005,
                angle: Math.random() * Math.PI * 2
            });
        }
        particleMaterial = new THREE.PointsMaterial({
            size: 0.16, // Cuadritos nítidos y vibrantes originales
            vertexColors: true,
            blending: THREE.AdditiveBlending,
            transparent: true,
            opacity: 0.85
        });

        particles = new THREE.Points(geometry, particleMaterial);
        scene.add(particles);

        // Registrar evento de mouse en window para seguimiento fluido
        window.addEventListener('mousemove', onMouseMove, { passive: true });

        window.addEventListener('resize', onWindowResize, false);
        animate();
    }
    
    function onMouseMove(event) {
        if (!container || !camera) return;
        const rect = container.getBoundingClientRect();
        
        // Solo seguir si el cursor está en el rango vertical del contenedor (+ buffer de 150px)
        const buffer = 150;
        const isMouseOverSection = (event.clientY >= rect.top - buffer && event.clientY <= rect.bottom + buffer);
        
        if (isMouseOverSection) {
            const ndcX = ((event.clientX - rect.left) / rect.width) * 2 - 1;
            const ndcY = -((event.clientY - rect.top) / rect.height) * 2 + 1;
            
            // Proyectar coordenadas 2D a 3D basándonos en la fov y la distancia de cámara z=8
            const fovRad = (camera.fov * Math.PI) / 180;
            const planeHeight = 2 * Math.tan(fovRad / 2) * 8;
            const planeWidth = planeHeight * camera.aspect;
            
            mouse3DTarget.x = (ndcX * planeWidth) / 2;
            mouse3DTarget.y = (ndcY * planeHeight) / 2;
        } else {
            // Regresar suavemente al centro si sale de la sección
            mouse3DTarget.x = 0;
            mouse3DTarget.y = 0;
        }
    }

    function onWindowResize() {
        if (!container || !renderer || !camera) return;
        camera.aspect = container.clientWidth / container.clientHeight;
        camera.updateProjectionMatrix();
        renderer.setSize(container.clientWidth, container.clientHeight);
    }

    function animate() {
        if (!isVisible) return;
        animationFrameId = requestAnimationFrame(animate);

        if (particles && particles.geometry) {
            const positions = particles.geometry.attributes.position.array;
            
            // Suavizar el cursor con inercia elástica (factor 0.02 para deslizamiento ultra fluido y pausado)
            mouse3D.x += (mouse3DTarget.x - mouse3D.x) * 0.02;
            mouse3D.y += (mouse3DTarget.y - mouse3D.y) * 0.02;
            
            for (let i = 0; i < particleCount; i++) {
                const xIdx = i * 3;
                const yIdx = i * 3 + 1;
                
                // Rotación orbital interna constante para dar volumen 3D
                particleSpeeds[i].angle += particleSpeeds[i].speed;
                const orbitX = initialPositions[i].x + Math.sin(particleSpeeds[i].angle) * 0.8;
                const orbitY = initialPositions[i].y + Math.cos(particleSpeeds[i].angle) * 0.8;
                
                // El objetivo es el cursor del mouse sumado a la órbita de cada partícula (evita que colapsen)
                const targetX = mouse3D.x + orbitX * 0.45;
                const targetY = mouse3D.y + orbitY * 0.45;
                
                // Interpolación lineal suave (Lerp más lento y elegante)
                positions[xIdx] += (targetX - positions[xIdx]) * 0.02;
                positions[yIdx] += (targetY - positions[yIdx]) * 0.02;
            }
            particles.geometry.attributes.position.needsUpdate = true;
        }
        renderer.render(scene, camera);
    }

    // Inicialización inteligente con IntersectionObserver
    function startThreeJSWithObserver() {
        container = document.getElementById('notebook-particles-container');
        if (!container) return;

        if (window.innerWidth <= 768) return;

        if ('IntersectionObserver' in window) {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    isVisible = entry.isIntersecting;
                    if (isVisible) {
                        if (!scene) {
                            initThreeJS();
                        } else {
                            animate();
                        }
                    } else {
                        cancelAnimationFrame(animationFrameId);
                    }
                });
            }, { threshold: 0.05 });
            observer.observe(container);
        } else {
            isVisible = true;
            initThreeJS();
        }
    }
    
    // Asignar funciones a window para que sean accesibles desde botones HTML
    window.changeSlide = changeSlide;

    // ====================================
    // ONBOARDING & HIGHLIGHTS (Driver.js)
    // ====================================
    let driverInstance = null; // Variable global para controlar el tour

    function initOnboarding(forced = false) {
        if (!forced && (localStorage.getItem('cepre_tour_completed') || sessionStorage.getItem('cepre_tour_hidden_session'))) return;

        setTimeout(() => {
            if (typeof window.driver !== 'undefined') {
                const driver = window.driver.js.driver;
                const countdownBubble = document.getElementById('countdown-bubble');
                const isMobile = window.innerWidth < 768;
                
                const steps = [];

                // PASO 0: Impresión Inicial / Incentivo (Hero Section)
                // FORZAR SLIDE 1 para que el botón esté visible y pausar carrusel
                showSlide(0, true);

                const heroBtn = document.getElementById('hero-btn-postular');
                if (heroBtn) {
                    steps.push({
                        element: '#hero-btn-postular',
                        popover: {
                            title: '<div style="display:flex; align-items:center; gap:10px;"><i class="fas fa-graduation-cap" style="color:#8bc34a"></i> ¡TU ÉXITO COMIENZA AQUÍ!</div>',
                            description: 'Asegura tu ingreso a la UNAMAD. <strong>¡Las inscripciones ya están abiertas!</strong> Únete a la mejor preparación académica.',
                            side: "bottom",
                            align: 'center'
                        }
                    });
                }

                if (countdownBubble) {
                    steps.push({
                        element: '#countdown-bubble',
                        popover: {
                            title: '<div style="display:flex; align-items:center; gap:10px;"><i class="fas fa-calendar-check" style="color:#00aeef"></i> PRÓXIMOS EVENTOS</div>',
                            description: 'Mantente al tanto de las fechas de exámenes y nuevos ciclos.',
                            side: isMobile ? "bottom" : "right",
                            align: 'start'
                        }
                    });

                    steps.push({
                        element: '#countdown-btn-postular',
                        popover: {
                            title: '<div style="display:flex; align-items:center; gap:10px;"><i class="fas fa-rocket" style="color:#ec008c"></i> ¡POSTULA YA!</div>',
                            description: 'Haz clic aquí para registrarte. ¡Es rápido, fácil y seguro!',
                            side: isMobile ? "top" : "right",
                            align: 'center'
                        }
                    });
                }

                if (steps.length === 0) return;

                // Si es móvil y la burbuja está cerrada, abrirla automáticamente para el tour
                if (isMobile && typeof toggleCountdownBubble === 'function') {
                    const panel = document.getElementById('bubble-panel');
                    if (panel && (panel.style.display === 'none' || panel.style.display === '')) {
                        toggleCountdownBubble();
                    }
                }

                driverInstance = driver({
                    showProgress: true,
                    animate: true,
                    overlayColor: 'rgba(12, 30, 47, 0.85)',
                    popoverClass: 'cepre-premium-popover',
                    progressText: 'Paso @{{current}} de @{{total}}',
                    nextBtnText: 'Siguiente',
                    prevBtnText: 'Anterior',
                    doneBtnText: '¡Entendido, no mostrar más!',
                    onDestroyStarted: () => {
                        // Reanudar carrusel al salir del tour
                        if (typeof startAutoPlay === 'function') startAutoPlay();

                        // Mark as completed permanently ONLY if they finish it
                        localStorage.setItem('cepre_tour_completed', 'true');
                        if (driverInstance) driverInstance.destroy();
                        driverInstance = null;
                    },
                    steps: steps
                });

                // ESPERAR UN MOMENTO PARA QUE EL CARRUSEL SE POSITIONE CORRECTAMENTE
                setTimeout(() => {
                    driverInstance.drive();
                }, 400); 
            }
        }, forced ? 300 : 1500); 
    }

    // Exportar para que el modal pueda cerrar el tour
    window.closeCepreTour = function() {
        if (driverInstance) {
            driverInstance.destroy();
            driverInstance = null;
            
            // Reanudar carrusel si se cerró manualmente (ej. al abrir el modal)
            if (typeof startAutoPlay === 'function') startAutoPlay();

            // Al abrir el modal, marcamos como visto en la sesión para no molestar más,
            // pero NO permanentemente si el usuario quiere volver a ver la ayuda luego.
            sessionStorage.setItem('cepre_tour_hidden_session', 'true');
        }
    };

    // Función para marcar como completado cuando abren el modal (llamada desde publico-modal.js)
    window.markTourAsCompleted = function() {
        localStorage.setItem('cepre_tour_completed', 'true');
    };

    // Sincronización profesional con el preloader
    const initializeHome = () => {
        if (window.cepre_home_initialized) return;
        window.cepre_home_initialized = true;
        
        startThreeJSWithObserver();
        initCarousel();
        initOnboarding();
    };

    window.addEventListener('cepre_ready', initializeHome);
    window.addEventListener('load', initializeHome);

    // Manejar apertura automática del modal si viene con el parámetro ?postula=1
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('postula')) {
            // Esperar un momento a que todo cargue correctamente
            setTimeout(() => {
                if (typeof openPostulacionModal === 'function') {
                    openPostulacionModal();
                    
                    // Limpiar la URL sin recargar la página (opcional, para estética)
                    const newUrl = window.location.pathname;
                    window.history.replaceState({}, document.title, newUrl);
                }
            }, 1200);
        }
    });
</script>

