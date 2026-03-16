<!-- Results Modal -->
<style>
    :root {
        --modal-glass-bg: rgba(255, 255, 255, 0.95);
        --modal-accent: #ec008c;
        --modal-secondary: #00aeef;
        --modal-text: #1a1a1a;
        --modal-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.4);
    }

    .results-modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 9999;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 20px;
        opacity: 0;
        transition: opacity 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .results-modal.active {
        display: flex;
        opacity: 1;
    }

    .results-modal-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(12, 30, 47, 0.9);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
    }

    .results-modal-content {
        position: relative;
        width: 100%;
        max-width: 650px;
        background: var(--modal-glass-bg);
        border-radius: 24px;
        overflow: hidden;
        box-shadow: var(--modal-shadow);
        border: 1px solid rgba(255, 255, 255, 0.3);
        transform: translateY(30px) scale(0.95);
        transition: transform 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
        z-index: 10;
        display: flex;
        flex-direction: column;
    }

    .results-modal.active .results-modal-content {
        transform: translateY(0) scale(1);
    }

    .results-modal-close {
        position: absolute;
        top: 15px;
        right: 15px;
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: rgba(0, 0, 0, 0.5);
        border: 2px solid rgba(255, 255, 255, 0.3);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s ease;
        z-index: 100;
    }

    .results-modal-close:hover {
        background: var(--modal-accent);
        transform: rotate(90deg);
    }

    .results-modal-image-container {
        width: 100%;
        max-height: 65vh;
        position: relative;
        background: #000;
        cursor: pointer;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .results-modal-image-container img {
        max-width: 100%;
        max-height: 65vh;
        width: auto;
        height: auto;
        object-fit: contain;
        transition: transform 0.6s ease;
    }

    .results-modal-body {
        padding: 25px 30px;
        text-align: center;
        background: #fff;
    }

    .results-modal-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 20px;
        background: rgba(236, 0, 140, 0.1);
        color: var(--modal-accent);
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        margin-bottom: 12px;
        letter-spacing: 0.5px;
    }

    #modal-announcement-title {
        font-family: 'Sora', sans-serif;
        font-size: 22px;
        font-weight: 800;
        color: #1a1a1a;
        margin: 0 auto 10px;
        line-height: 1.3;
        max-width: 90%;
    }

    #modal-announcement-description {
        font-size: 14px;
        color: #666;
        margin-bottom: 25px;
        line-height: 1.6;
    }

    .carousel-nav-btn {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        width: 38px;
        height: 38px;
        border-radius: 10px;
        background: rgba(255, 255, 255, 0.95);
        border: none;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        color: var(--modal-text);
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s ease;
        z-index: 20;
    }

    .carousel-nav-btn:hover {
        background: var(--modal-secondary);
        color: #fff;
        transform: translateY(-50%) scale(1.1);
    }

    .carousel-prev-btn { left: 15px; }
    .carousel-next-btn { right: 15px; }

    .carousel-pagination {
        display: flex;
        justify-content: center;
        gap: 6px;
        margin-bottom: 15px;
    }

    .carousel-pagination-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: rgba(0,0,0,0.1);
        transition: all 0.3s ease;
    }

    .carousel-pagination-dot.active {
        width: 18px;
        border-radius: 4px;
        background: var(--modal-accent);
    }

    .btn-modal-action {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 28px;
        background: linear-gradient(135deg, var(--modal-accent), #ff4d97);
        color: #fff;
        border-radius: 12px;
        font-weight: 700;
        text-decoration: none;
        transition: all 0.3s ease;
        font-size: 15px;
        box-shadow: 0 8px 20px -4px rgba(236, 0, 140, 0.4);
    }

    .btn-modal-action:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 24px -4px rgba(236, 0, 140, 0.5);
        color: #fff;
    }

    #floating-results-btn {
        position: fixed;
        bottom: 30px;
        right: 30px;
        width: 55px;
        height: 55px;
        background: linear-gradient(135deg, var(--modal-accent), #ff4d97);
        color: #fff;
        border: none;
        border-radius: 50%;
        box-shadow: 0 10px 25px rgba(236, 0, 140, 0.5);
        cursor: pointer;
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 999;
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }

    #floating-results-btn i { font-size: 22px; }

    #floating-results-btn .results-badge {
        position: absolute;
        top: -2px;
        right: -2px;
        background: #fff;
        color: var(--modal-accent);
        min-width: 20px;
        height: 20px;
        padding: 0 5px;
        border-radius: 10px;
        font-size: 10px;
        font-weight: 800;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        border: 2px solid var(--modal-accent);
    }

    /* FIX MOBILE LAYOUT */
    @media (max-width: 768px) {
        .results-modal {
            padding: 10px;
        }

        .results-modal-content {
            border-radius: 20px;
            height: auto; /* Shrink to content */
            max-height: 92vh;
            width: 100%;
        }
        
        .results-modal-body {
            padding: 20px;
            flex: none; /* Do not stretch */
            overflow-y: auto;
        }

        #modal-announcement-title {
            font-size: 18px;
        }

        .results-modal-image-container {
            max-height: 50vh;
        }

        #floating-results-btn {
            bottom: 20px;
            right: 20px;
            width: 50px;
            height: 50px;
        }
    }
</style>

<div id="resultsModal" class="results-modal">
    <div class="results-modal-overlay" onclick="closeResultsModal()"></div>
    <div class="results-modal-content">
        <button class="results-modal-close" onclick="closeResultsModal()">
            <i class="fas fa-times"></i>
        </button>
        
        <button class="carousel-nav-btn carousel-prev-btn" onclick="previousAnnouncement()" style="display: none;">
            <i class="fas fa-chevron-left"></i>
        </button>
        <button class="carousel-nav-btn carousel-next-btn" onclick="nextAnnouncement()" style="display: none;">
            <i class="fas fa-chevron-right"></i>
        </button>
        
        <div class="results-modal-image-container" onclick="handleAnnouncementClick()">
            <img id="modal-announcement-image" src="" alt="Anuncio">
        </div>

        <div class="results-modal-body">
            <span class="results-modal-badge" id="modal-announcement-type-badge">COMUNICADO</span>
            <div class="carousel-pagination" id="carousel-pagination-dots" style="display: none;"></div>
            
            <h3 id="modal-announcement-title">Cargando...</h3>
            <p id="modal-announcement-description">Mantente informado con las últimas noticias del CEPRE UNAMAD.</p>
            
            <a href="{{ route('resultados-examenes.public') }}" id="modal-action-btn" class="btn-modal-action">
                <i class="fas fa-external-link-alt"></i>
                <span id="modal-btn-text">Ver Detalles</span>
            </a>
        </div>
    </div>
</div>

<!-- Floating Button as BOLITA -->
<button id="floating-results-btn" onclick="openResultsModal()">
    <i class="fas fa-bullhorn"></i>
    <span class="results-badge" id="floating-badge-text">1</span>
</button>

<script>
    let allAnnouncements = [];
    let currentAnnouncementIndex = 0;
    let autoplayInterval = null;
    let touchStartX = 0;
    let touchEndX = 0;
    const AUTOPLAY_TIME = 5000; // 5 segundos

    async function fetchActiveAnnouncements() {
        try {
            const response = await fetch('/api/anuncios/activos');
            if (!response.ok) throw new Error('Network error');
            return await response.json();
        } catch (error) {
            console.error('Error:', error);
            return [];
        }
    }

    function openResultsModal() {
        const modal = document.getElementById('resultsModal');
        if (!modal) return;
        modal.style.display = 'flex';
        setTimeout(() => modal.classList.add('active'), 10);
        document.body.style.overflow = 'hidden';
        startAutoplay();
    }

    function closeResultsModal() {
        const modal = document.getElementById('resultsModal');
        if (!modal) return;
        modal.classList.remove('active');
        setTimeout(() => modal.style.display = 'none', 400);
        document.body.style.overflow = 'auto';
        stopAutoplay();
    }

    function startAutoplay() {
        stopAutoplay();
        if (allAnnouncements.length > 1) {
            autoplayInterval = setInterval(() => {
                nextAnnouncement(true); // true significa que es automático
            }, AUTOPLAY_TIME);
        }
    }

    function stopAutoplay() {
        if (autoplayInterval) {
            clearInterval(autoplayInterval);
            autoplayInterval = null;
        }
    }

    function displayCurrentAnnouncement() {
        const ad = allAnnouncements[currentAnnouncementIndex];
        if (!ad) return;
        
        const img = document.getElementById('modal-announcement-image');
        img.src = ad.imagen ? `/storage/${ad.imagen}` : 'https://placehold.co/600x400/2C5F7C/ffffff?text=CEPRE+UNAMAD';
        
        document.getElementById('modal-announcement-title').textContent = ad.titulo;
        document.getElementById('modal-announcement-description').textContent = ad.descripcion || 'Consulta los detalles de este anuncio en nuestro portal.';
        document.getElementById('modal-announcement-type-badge').textContent = (ad.tipo || 'INFORMATIVO').toUpperCase();
        
        updatePagination();
        updateNavButtons();
    }

    function updatePagination() {
        const dotsContainer = document.getElementById('carousel-pagination-dots');
        if (!dotsContainer || allAnnouncements.length <= 1) {
            if (dotsContainer) dotsContainer.style.display = 'none';
            return;
        }
        
        dotsContainer.style.display = 'flex';
        dotsContainer.innerHTML = '';
        allAnnouncements.forEach((_, i) => {
            const dot = document.createElement('div');
            dot.className = `carousel-pagination-dot ${i === currentAnnouncementIndex ? 'active' : ''}`;
            dotsContainer.appendChild(dot);
        });
    }

    function updateNavButtons() {
        const show = allAnnouncements.length > 1;
        document.querySelector('.carousel-prev-btn').style.display = (show && currentAnnouncementIndex > 0) ? 'flex' : 'none';
        document.querySelector('.carousel-next-btn').style.display = (show && currentAnnouncementIndex < allAnnouncements.length - 1) ? 'flex' : 'none';
    }

    function nextAnnouncement(isAuto = false) {
        if (currentAnnouncementIndex < allAnnouncements.length - 1) {
            currentAnnouncementIndex++;
        } else if (isAuto) {
            currentAnnouncementIndex = 0; // Loop al inicio si es automático
        }

        displayCurrentAnnouncement();
        if (!isAuto) startAutoplay(); // Reset timer si es manual
    }

    function previousAnnouncement() {
        if (currentAnnouncementIndex > 0) {
            currentAnnouncementIndex--;
            displayCurrentAnnouncement();
            startAutoplay(); // Reset timer
        }
    }

    function handleAnnouncementClick() {
        window.location.href = "{{ route('resultados-examenes.public') }}";
    }

    // SOPORTE PARA SWIPE (DEDO) EN CELULARES
    function handleTouchStart(e) {
        touchStartX = e.changedTouches[0].screenX;
        stopAutoplay();
    }

    function handleTouchEnd(e) {
        touchEndX = e.changedTouches[0].screenX;
        handleGesture();
        startAutoplay();
    }

    function handleGesture() {
        const threshold = 50; // Mínimo de píxeles para detectar swipe
        if (touchEndX < touchStartX - threshold) {
            nextAnnouncement(); // Swipe a la izquierda
        }
        if (touchEndX > touchStartX + threshold) {
            previousAnnouncement(); // Swipe a la derecha
        }
    }

    async function initAnnouncements() {
        const ads = await fetchActiveAnnouncements();
        if (ads && ads.length > 0) {
            allAnnouncements = ads;
            
            const floatingBtn = document.getElementById('floating-results-btn');
            floatingBtn.style.display = 'flex';
            document.getElementById('floating-badge-text').textContent = ads.length;
            
            displayCurrentAnnouncement();
            
            const modalContent = document.querySelector('.results-modal-content');
            
            // Pausar al pasar el mouse
            modalContent.addEventListener('mouseenter', stopAutoplay);
            modalContent.addEventListener('mouseleave', startAutoplay);
            
            // Eventos táctiles para el dedo
            modalContent.addEventListener('touchstart', handleTouchStart, {passive: true});
            modalContent.addEventListener('touchend', handleTouchEnd, {passive: true});
            
            // SIEMPRE MOSTRAR AL CARGAR
            setTimeout(openResultsModal, 1500);
        }
    }

    document.addEventListener('DOMContentLoaded', initAnnouncements);
</script>
