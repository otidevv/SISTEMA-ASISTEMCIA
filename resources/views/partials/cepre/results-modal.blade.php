<!-- Results Modal -->
<div id="resultsModal" class="results-modal">
    <div class="results-modal-overlay" onclick="closeResultsModal()"></div>
    <div class="results-modal-content">
        <button class="results-modal-close" onclick="closeResultsModal()">
            <i class="fas fa-times"></i>
        </button>
        
        <!-- Carousel Navigation -->
        <button class="carousel-nav carousel-prev" onclick="previousAnnouncement()" style="display: none;">
            <i class="fas fa-chevron-left"></i>
        </button>
        <button class="carousel-nav carousel-next" onclick="nextAnnouncement()" style="display: none;">
            <i class="fas fa-chevron-right"></i>
        </button>
        
        <div class="results-modal-image" onclick="window.location.href='{{ route('resultados-examenes.public') }}'">
            <img id="modal-announcement-image" src="" alt="Anuncio">
        </div>
        <div class="results-modal-footer">
            <h3 id="modal-announcement-title">¡Resultados Publicados!</h3>
            <p id="modal-announcement-description">Consulta los resultados de los exámenes</p>
            
            <!-- Carousel Counter -->
            <div class="carousel-counter" style="display: none;">
                <span id="current-announcement">1</span> / <span id="total-announcements">1</span>
            </div>
            
            <a href="{{ route('resultados-examenes.public') }}" class="btn-view-results">
                <i class="fas fa-eye"></i>
                Ver Resultados Completos
            </a>
        </div>
    </div>
</div>

<!-- Floating Results Button -->
<button id="floating-results-btn" onclick="openResultsModal()" title="Ver Anuncios" style="display: none;">
    <i class="fas fa-bullhorn btn-icon"></i>
    <span class="btn-text">Anuncios</span>
    <span class="results-badge">Nuevo</span>
</button>

<script>
    let allAnnouncements = [];
    let currentAnnouncementIndex = 0;

    async function fetchActiveAnnouncements() {
        try {
            const response = await fetch('/api/anuncios/activos');
            const data = await response.json();
            return data.length > 0 ? data : null;
        } catch (error) {
            console.error('Error fetching announcements:', error);
            return null;
        }
    }

    function openResultsModal() {
        const modal = document.getElementById('resultsModal');
        if (!modal) return;
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
        sessionStorage.setItem('announcementViewed', 'true');
    }

    function closeResultsModal() {
        const modal = document.getElementById('resultsModal');
        if (!modal) return;
        modal.classList.remove('active');
        document.body.style.overflow = 'auto';
    }

    function nextAnnouncement() {
        if (currentAnnouncementIndex < allAnnouncements.length - 1) {
            currentAnnouncementIndex++;
            displayCurrentAnnouncement();
        }
    }

    function previousAnnouncement() {
        if (currentAnnouncementIndex > 0) {
            currentAnnouncementIndex--;
            displayCurrentAnnouncement();
        }
    }

    function displayCurrentAnnouncement() {
        const announcement = allAnnouncements[currentAnnouncementIndex];
        if (!announcement) return;
        
        const image = document.getElementById('modal-announcement-image');
        const title = document.getElementById('modal-announcement-title');
        const description = document.getElementById('modal-announcement-description');
        const currentSpan = document.getElementById('current-announcement');
        
        if (image) {
            image.src = announcement.imagen ? `/storage/${announcement.imagen}` : 'https://placehold.co/600x400/2C5F7C/ffffff?text=Resultados+Disponibles';
        }
        
        if (title) title.textContent = announcement.titulo;
        if (description) description.textContent = announcement.descripcion || 'Consulta los resultados de los exámenes';
        if (currentSpan) currentSpan.textContent = currentAnnouncementIndex + 1;
        
        updateNavigationButtons();
    }

    function updateNavigationButtons() {
        const prevBtn = document.querySelector('.carousel-prev');
        const nextBtn = document.querySelector('.carousel-next');
        
        if (!prevBtn || !nextBtn) return;

        if (allAnnouncements.length > 1) {
            prevBtn.style.display = currentAnnouncementIndex > 0 ? 'flex' : 'none';
            nextBtn.style.display = currentAnnouncementIndex < allAnnouncements.length - 1 ? 'flex' : 'none';
        } else {
            prevBtn.style.display = 'none';
            nextBtn.style.display = 'none';
        }
    }

    function loadAllAnnouncements(announcements) {
        allAnnouncements = announcements;
        currentAnnouncementIndex = 0;
        
        const floatingBtn = document.getElementById('floating-results-btn');
        const totalSpan = document.getElementById('total-announcements');
        const counter = document.querySelector('.carousel-counter');
        
        if (totalSpan) totalSpan.textContent = announcements.length;
        if (counter) counter.style.display = announcements.length > 1 ? 'block' : 'none';
        
        displayCurrentAnnouncement();
        
        if (floatingBtn) {
            floatingBtn.style.display = 'flex';
            const badge = floatingBtn.querySelector('.results-badge');
            if (badge) badge.textContent = announcements.length > 1 ? `${announcements.length} Nuevos` : 'Nuevo';
        }
    }

    function initResultsModal() {
        fetchActiveAnnouncements().then(announcements => {
            if (announcements) {
                loadAllAnnouncements(announcements);
                
                // Show modal automatically if not viewed in this session
                if (!sessionStorage.getItem('announcementViewed')) {
                    setTimeout(openResultsModal, 2000);
                }
            }
        });
    }

    window.addEventListener('DOMContentLoaded', initResultsModal);
</script>
