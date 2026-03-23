<!-- Scripts Generic API CEPRE -->
<script>
    // --- Referencias DOM ---
    const menuToggle = document.querySelector('.menu-toggle');
    const navMenu = document.querySelector('.nav-menu');
    const header = document.querySelector('.main-header');

    // ====================================
    // 0. Ocultar Preloader
    // ====================================
    window.addEventListener('load', () => {
        const preloader = document.getElementById('preloader');
        if (preloader) {
            preloader.style.opacity = '0';
            preloader.style.transition = 'opacity 0.5s ease';
            setTimeout(() => {
                preloader.style.display = 'none';
            }, 500);
        }
    });

    // ====================================
    // 1. Funcionalidad del Menú Móvil
    // ====================================
    function toggleMobileMenu() {
        if (!menuToggle || !navMenu) return;
        menuToggle.classList.toggle('active');
        navMenu.classList.toggle('active');
    }
    
    if (menuToggle) menuToggle.addEventListener('click', toggleMobileMenu);

    // Cierra el menú móvil al hacer clic en un enlace de navegación
    document.querySelectorAll('.nav-menu a').forEach(anchor => {
        anchor.addEventListener('click', function () {
            if (window.innerWidth <= 1024 && navMenu && navMenu.classList.contains('active')) {
                toggleMobileMenu();
            }
        });
    });

    window.addEventListener('resize', () => {
         if (window.innerWidth > 1024 && navMenu) {
            navMenu.classList.remove('active');
            menuToggle.classList.remove('active');
         }
    });
    
    // ====================================
    // 2. Animación de Contadores
    // ====================================
    const counters = document.querySelectorAll('.counter');
    const speed = 250; 

    function startCounterAnimation(counter) {
        const updateCount = () => {
            const target = +counter.getAttribute('data-target');
            const countString = counter.innerText.replace('+', '');
            const count = +countString;
            const inc = target / speed;

            if (count < target) {
                counter.innerText = Math.ceil(count + inc);
                setTimeout(updateCount, 1);
            } else {
                counter.innerText = target + '+';
            }
        };
        updateCount();
    }

    const counterObserver = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                startCounterAnimation(entry.target);
                counterObserver.unobserve(entry.target);
            }
        });
    }, { threshold: 0.5 }); 

    counters.forEach(counter => {
        counterObserver.observe(counter);
    });

    // ====================================
    // 3. Animación al Scroll (Fade In)
    // ====================================
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const scrollObserver = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animated');
            }
        });
    }, observerOptions);

    document.querySelectorAll('.animate-on-scroll').forEach(el => {
        scrollObserver.observe(el);
    });

    document.querySelectorAll('.footer-column').forEach(el => {
        scrollObserver.observe(el);
    });

    // ====================================
    // 4. Botón Scroll to Top
    // ====================================
    const scrollTopBtn = document.getElementById('scrollTop');
    window.addEventListener('scroll', function() {
        if (!scrollTopBtn) return;
        if (window.pageYOffset > 300) {
            scrollTopBtn.style.display = 'flex';
        } else {
            scrollTopBtn.style.display = 'none';
        }
    });

    if (scrollTopBtn) {
        scrollTopBtn.addEventListener('click', function() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }
    
    // ====================================
    // 5. Control de Modals
    // ====================================
    function showModal(type, title = '', body = '', icon = 'fa-info-circle') {
        const modal = document.getElementById('infoModal');
        const titleEl = document.getElementById('modalTitle');
        const bodyEl = document.getElementById('modalBody');
        
        if (!modal) return;
        
        // Actualizar icono si existe el contenedor
        const iconContainer = document.getElementById('modalIconContainer');
        if (iconContainer) {
            iconContainer.innerHTML = `<i class="fas ${icon}"></i>`;
        }

        if (type === 'videoModal') {
            if (titleEl) titleEl.textContent = '¡Video Promocional!';
            if (bodyEl) bodyEl.innerHTML = 'Aquí iría el video de presentación del CEPRE UNAMAD.';
        } else if (type === 'courseInfo') {
            if (titleEl) titleEl.textContent = title;
            if (bodyEl) bodyEl.innerHTML = body;
        } else if (type === 'assistantModal') {
            if (titleEl) titleEl.textContent = '💡 Asistente Virtual CEPRE';
            if (bodyEl) bodyEl.innerHTML = '<p>¡Hola! Soy el asistente virtual de la CEPRE UNAMAD. Estoy aquí para guiarte en tu camino al éxito.</p>';
        }
        modal.style.display = 'flex';
    }

    function closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'none';

            // Restaurar elementos flotantes si es el modal de postulación
            if (modalId === 'postulacionModal') {
                const bubble = document.getElementById('countdown-bubble');
                const chatbot = document.getElementById('chatbot-launcher');
                const helpBtn = document.querySelector('.btn-ayuda-tour');
                
                if (bubble) bubble.style.display = 'flex';
                if (chatbot) chatbot.style.display = 'flex';
                if (helpBtn) helpBtn.style.display = 'flex';
            }
        }
    }
    
    // Cerrar modal al hacer clic fuera
    window.addEventListener('click', function(event) {
         if (event.target === infoModal) {
             closeModal('infoModal');
         }
    });

    // ====================================
    // 6. Confetti y Animaciones Pop-up
    // ====================================
    function createConfetti() {
        const academicIcons = ['fa-graduation-cap', 'fa-book', 'fa-pencil-alt', 'fa-award', 'fa-star'];
        const colors = ['#8bc34a', '#e91e63', '#03a9f4', '#ffc107', '#ff5722'];
        const confettiCount = 20;
        
        for (let i = 0; i < confettiCount; i++) {
            setTimeout(() => {
                const confetti = document.createElement('i');
                confetti.className = 'fas ' + academicIcons[Math.floor(Math.random() * academicIcons.length)] + ' confetti-icon';
                confetti.style.left = Math.random() * 100 + '%';
                confetti.style.color = colors[Math.floor(Math.random() * colors.length)];
                confetti.style.fontSize = (Math.random() * 12 + 18) + 'px';
                confetti.style.animation = `confettiFall ${(Math.random() * 2 + 2.5)}s linear forwards`;
                document.body.appendChild(confetti);
                setTimeout(() => confetti.remove(), 5000);
            }, i * 30);
        }
    }

    // Exportar funciones globales si es necesario
    window.showModal = showModal;
    window.closeModal = closeModal;
    window.createConfetti = createConfetti;
</script>

<!-- Driver.js para el Tutorial / Onboarding -->
<script src="https://cdn.jsdelivr.net/npm/driver.js@1.3.1/dist/driver.js.iife.js"></script>
