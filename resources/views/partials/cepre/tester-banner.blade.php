<!-- Banner Play Store Edition - Color Celeste Principal -->
<div id="play-tester-wrapper" class="play-tester-wrapper animate__animated animate__fadeInRight">
    <!-- Estado Expandido (Tarjeta) -->
    <div id="play-card-expanded" class="play-tester-card" style="display: none;">
        <button type="button" class="play-close-btn" onclick="togglePlayCard()">
            <i class="fa fa-times"></i>
        </button>
        
        <div class="play-card-content">
            <div class="play-logo-container">
                <img src="https://upload.wikimedia.org/wikipedia/commons/d/d0/Google_Play_Arrow_logo.svg" alt="Google Play">
            </div>
            <div class="play-text-content">
                <span class="play-label">PROYECTO BETA</span>
                <h4>App CEPRE UNAMAD</h4>
                <p>¡Optimiza tu preparación! Únete como tester y ayúdanos a mejorar tu camino al <strong>Ingreso Directo</strong>.</p>
            </div>
        </div>
        
        <div class="play-card-actions">
            <a href="https://wa.me/51989043261?text=Hola,%20quiero%20ser%20tester%20de%20la%20App%20CEPRE%20UNAMAD" target="_blank" class="play-btn-main">
                <i class="fa fa-rocket"></i> Unirme ahora
            </a>
        </div>
        
        <div class="play-color-stripe">
            <div class="stripe-magenta"></div>
            <div class="stripe-cyan"></div>
            <div class="stripe-green"></div>
        </div>
    </div>

    <!-- Estado Compacto (Pestaña Lateral Celeste) -->
    <div id="play-card-compact" class="play-side-tab" onclick="togglePlayCard()" style="display: none;">
        <div class="tab-icon">
            <img src="https://upload.wikimedia.org/wikipedia/commons/d/d0/Google_Play_Arrow_logo.svg" alt="Play Store">
        </div>
        <div class="tab-text">
            <span>PROBAR APP</span>
        </div>
        <div class="tab-pulse"></div>
    </div>
</div>

<style>
    .play-tester-wrapper {
        position: fixed;
        top: 50%;
        right: 0;
        transform: translateY(-50%);
        z-index: 1030;
        font-family: 'Inter', sans-serif;
        transition: opacity 0.3s ease;
    }

    /* Pestaña Lateral Celeste */
    .play-side-tab {
        background: #00aeef; /* Principal: Celeste */
        color: white;
        display: flex;
        align-items: center;
        padding: 10px 14px;
        border-radius: 20px 0 0 20px;
        cursor: pointer;
        box-shadow: -5px 5px 15px rgba(0, 174, 239, 0.3);
        transition: all 0.3s ease;
        border: 2px solid white;
        border-right: none;
    }

    .play-side-tab:hover { padding-right: 20px; background: #e2007a; }
    .tab-icon { width: 25px; height: 25px; background: white; border-radius: 6px; display: flex; align-items: center; justify-content: center; padding: 4px; margin-right: 10px; }
    .tab-icon img { width: 100%; height: auto; }
    .tab-text span { font-size: 12px; font-weight: 800; letter-spacing: 0.5px; white-space: nowrap; }

    /* Tarjeta Expandida */
    .play-tester-card {
        width: 280px;
        background: white;
        border-radius: 18px 0 0 18px;
        box-shadow: -10px 10px 35px rgba(0, 0, 0, 0.15);
        border: 2px solid #00aeef; /* Borde Celeste */
        border-right: none;
        overflow: hidden;
        position: relative;
    }

    .play-close-btn {
        position: absolute;
        top: 10px;
        left: 10px;
        background: #f0f0f0;
        border: none;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        color: #00aeef;
        font-size: 10px;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .play-card-content { padding: 30px 18px 12px 18px; text-align: center; }
    .play-logo-container { width: 48px; margin: 0 auto 10px auto; }
    .play-logo-container img { width: 100%; }
    .play-label { font-size: 9px; font-weight: 800; color: #00aeef; background: rgba(0, 174, 239, 0.1); padding: 2px 12px; border-radius: 50px; }
    .play-text-content h4 { font-size: 18px; font-weight: 800; margin: 10px 0 6px 0; color: #1a1a1a; }
    .play-text-content p { font-size: 13px; color: #555; line-height: 1.4; margin-bottom: 0; }
    .play-card-actions { padding: 10px 20px 20px 20px; }

    .play-btn-main {
        background: #e2007a; /* Rosa/Magenta para destacar */
        color: white;
        text-decoration: none !important;
        display: flex !important;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 12px;
        border-radius: 10px;
        font-weight: 700;
        font-size: 14px;
        transition: 0.3s;
        box-shadow: 0 5px 15px rgba(226, 0, 122, 0.2);
    }
    .play-btn-main i { font-size: 14px; }
    .play-btn-main:hover { background: #00aeef; transform: translateY(-2px); box-shadow: 0 8px 20px rgba(0, 174, 239, 0.3); }

    .play-color-stripe { height: 5px; display: flex; }
    .stripe-magenta { background: #e2007a; flex: 1; }
    .stripe-cyan { background: #00aeef; flex: 1; }
    .stripe-green { background: #93c01f; flex: 1; }

    .tab-pulse {
        position: absolute;
        width: 100%;
        height: 100%;
        border-radius: 20px 0 0 20px;
        border: 2px solid #00aeef;
        animation: tab-pulse 2s infinite;
        left: 0;
        pointer-events: none;
    }

    @keyframes tab-pulse {
        0% { transform: scale(1); opacity: 1; }
        100% { transform: scale(1.1, 1.3); opacity: 0; }
    }

    @media (max-width: 991px) {
        .play-tester-wrapper { top: 50%; } 
        .play-tester-card { width: 260px; }
    }
</style>

<script>
    function togglePlayCard() {
        const expanded = document.getElementById('play-card-expanded');
        const compact = document.getElementById('play-card-compact');
        
        if (expanded.style.display === 'none') {
            expanded.style.display = 'block';
            compact.style.display = 'none';
            expanded.classList.remove('animate__fadeOutRight');
            expanded.classList.add('animate__fadeInRight');
        } else {
            expanded.classList.remove('animate__fadeInRight');
            expanded.classList.add('animate__fadeOutRight');
            setTimeout(() => {
                expanded.style.display = 'none';
                compact.style.display = 'flex';
            }, 500);
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const expanded = document.getElementById('play-card-expanded');
        const compact = document.getElementById('play-card-compact');
        const wrapper = document.getElementById('play-tester-wrapper');
        const isMobile = window.innerWidth <= 991;

        if (isMobile) {
            expanded.style.display = 'none';
            compact.style.display = 'flex';
        } else {
            expanded.style.display = 'block';
            compact.style.display = 'none';
        }

        if (typeof jQuery !== 'undefined') {
            jQuery(document).on('show.bs.modal', function() {
                wrapper.style.opacity = '0';
                wrapper.style.pointerEvents = 'none';
            });
            jQuery(document).on('hidden.bs.modal', function() {
                wrapper.style.opacity = '1';
                wrapper.style.pointerEvents = 'auto';
            });
        }

        const observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.attributeName === "class") {
                    const hasModal = document.body.classList.contains('modal-open');
                    wrapper.style.opacity = hasModal ? '0' : '1';
                    wrapper.style.pointerEvents = hasModal ? 'none' : 'auto';
                }
            });
        });
        observer.observe(document.body, { attributes: true });
    });
</script>
