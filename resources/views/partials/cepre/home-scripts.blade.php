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
    // Inicialización 3D y Carga
    // ====================================
    let scene, camera, renderer, particles, particleMaterial, particleCount;
    let container = document.getElementById('hero-canvas-container');

    function initThreeJS() {
        if (!container) return;
        
        scene = new THREE.Scene();
        scene.background = null;
        
        camera = new THREE.PerspectiveCamera(75, container.clientWidth / container.clientHeight, 1, 1000);
        camera.position.z = 5;

        renderer = new THREE.WebGLRenderer({ antialias: true, alpha: true });
        renderer.setSize(container.clientWidth, container.clientHeight);
        container.appendChild(renderer.domElement);
        
        particleCount = 750;
        const geometry = new THREE.BufferGeometry();
        const positions = [];
        const colors = [];
        
        const color1 = new THREE.Color(0x00a0e3);
        const color2 = new THREE.Color(0xa4c639);
        
        for (let i = 0; i < particleCount; i++) {
            positions.push(
                (Math.random() - 0.5) * 20,
                (Math.random() - 0.5) * 20,
                (Math.random() - 0.5) * 20
            );
            
            const color = (Math.random() > 0.5) ? color1 : color2;
            colors.push(color.r, color.g, color.b);
        }
        
        geometry.setAttribute('position', new THREE.Float32BufferAttribute(positions, 3));
        geometry.setAttribute('color', new THREE.Float32BufferAttribute(colors, 3));

        particleMaterial = new THREE.PointsMaterial({
            size: 0.1,
            vertexColors: true,
            blending: THREE.AdditiveBlending,
            transparent: true,
            opacity: 0.8
        });

        particles = new THREE.Points(geometry, particleMaterial);
        scene.add(particles);

        window.addEventListener('resize', onWindowResize, false);
        animate();
    }
    
    function onWindowResize() {
        if (!container) return;
        camera.aspect = container.clientWidth / container.clientHeight;
        camera.updateProjectionMatrix();
        renderer.setSize(container.clientWidth, container.clientHeight);
    }

    function animate() {
        requestAnimationFrame(animate);
        if (particles) {
            particles.rotation.x += 0.0005;
            particles.rotation.y += 0.001;
        }
        renderer.render(scene, camera);
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
        
        initThreeJS();
        initCarousel();
        initOnboarding();
    };

    window.addEventListener('cepre_ready', initializeHome);
    window.addEventListener('load', initializeHome);
</script>
