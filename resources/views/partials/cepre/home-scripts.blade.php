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
        startAutoPlay();
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

    function showSlide(index) {
        if (!slidesContainer) return;
        clearInterval(slideInterval);
        startAutoPlay();

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

    window.addEventListener('load', function() {
        initThreeJS();
        initCarousel();
    });
</script>
