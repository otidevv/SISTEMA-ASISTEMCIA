@if(isset($proximoExamen) && $proximoExamen || isset($proximoCiclo) && $proximoCiclo)
<div id="countdown-bubble" class="countdown-premium-wrapper animate__animated animate__fadeInLeft">
    
    <!-- Botón Flotante para re-abrir (Burbuja) -->
    <button id="bubble-reopen" class="bubble-reopen-btn" onclick="toggleCountdownBubble()" style="display:none;">
        <div class="bubble-icon">
            <i class="fa fa-calendar-alt"></i>
        </div>
        <div class="bubble-pulse"></div>
    </button>

    <!-- Panel principal Ultra-Compacto -->
    <div id="bubble-panel" class="premium-countdown-panel">
        <!-- Header Slim (Mínimo espacio) -->
        <div class="premium-header">
            <div class="header-content">
                <div class="header-icon">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="header-text">
                    <span class="header-pre">CENTRO PRE</span>
                    <h4 class="header-main">EVENTOS</h4>
                </div>
            </div>
            <button class="close-panel-btn" onclick="toggleCountdownBubble()">&times;</button>
        </div>

        <!-- Cuerpo Compacto -->
        <div class="premium-body">
            @if(isset($proximoExamen) && $proximoExamen)
            <div class="event-section">
                <div class="section-title cyan-text">
                    <i class="fas fa-edit"></i>
                    <span>{{ $proximoExamen['nombre'] }}</span>
                </div>
                <div id="timer-examen" data-date="{{ $proximoExamen['fecha'] }}" class="timer-grid cyan-border"></div>
            </div>
            @endif

            @if(isset($proximoCiclo) && $proximoCiclo)
            <div class="event-section">
                <div class="section-title magenta-text">
                    <i class="fas fa-graduation-cap"></i>
                    <span>{{ $proximoCiclo['nombre'] }}</span>
                </div>
                <div id="timer-ciclo" data-date="{{ $proximoCiclo['fecha'] }}" class="timer-grid magenta-border"></div>
            </div>

            <a href="javascript:void(0)" onclick="openPostulacionModal(); return false;" class="premium-btn-enroll">
                <i class="fas fa-user-plus"></i> ¡INSCRÍBETE AHORA!
            </a>
            @endif
        </div>

        <div class="premium-color-stripe">
            <div class="stripe-magenta"></div>
            <div class="stripe-cyan"></div>
            <div class="stripe-green"></div>
        </div>
    </div>
</div>

<style>
    :root {
        --unamad-magenta: #e2007a;
        --unamad-cyan: #00aeef;
        --unamad-green: #93c01f;
    }

    .countdown-premium-wrapper {
        position: fixed;
        left: 15px;
        bottom: 20px;
        z-index: 9998;
        font-family: 'Inter', sans-serif;
    }

    .bubble-reopen-btn {
        width: 50px; height: 50px;
        background: var(--unamad-cyan);
        border: 2px solid white;
        border-radius: 50%;
        cursor: pointer;
        display: flex; align-items: center; justify-content: center;
        box-shadow: 0 8px 20px rgba(0, 174, 239, 0.3);
    }

    .bubble-icon { color: white; font-size: 18px; }
    .bubble-pulse {
        position: absolute;
        width: 100%; height: 100%;
        border-radius: 50%;
        border: 2px solid var(--unamad-cyan);
        animation: bubble-pulse 2s infinite;
    }

    .premium-countdown-panel {
        width: 240px;
        background: #ffffff;
        border-radius: 12px;
        overflow: hidden;
        border: 2px solid var(--unamad-cyan);
        box-shadow: 0 12px 35px rgba(0, 0, 0, 0.12);
        display: flex;
        flex-direction: column;
    }

    /* --- Cabezal Slim Extremo --- */
    .premium-header {
        background: var(--unamad-cyan);
        padding: 4px 12px; /* Mínimo espacio arriba y abajo */
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .header-content { display: flex; align-items: center; gap: 8px; }
    
    .header-icon { 
        width: 22px; height: 22px; /* Reducido para no forzar altura */
        background: rgba(255,255,255,0.25); 
        border-radius: 5px; 
        display: flex; align-items: center; justify-content: center; 
        color: white; font-size: 11px; 
    }
    
    .header-text { display: flex; flex-direction: column; line-height: 0.85; }
    .header-pre { font-size: 7.5px; font-weight: 800; color: rgba(255,255,255,0.85); letter-spacing: 0.5px; margin-bottom: -1px; }
    .header-main { margin: 0; color: white; font-size: 13px; font-weight: 900; letter-spacing: 0.3px; }

    .close-panel-btn { 
        background: none; 
        border: none; color: white; font-size: 14px; 
        cursor: pointer; opacity: 0.7;
        width: 18px; height: 18px; 
        display: flex; align-items: center; justify-content: center; 
    }

    .premium-body { padding: 12px; display: flex; flex-direction: column; gap: 10px; }
    .section-title { display: flex; align-items: center; gap: 6px; margin-bottom: 4px; }
    .section-title span { font-size: 9px; font-weight: 800; text-transform: uppercase; }

    .timer-grid { display: flex; gap: 4px; }
    .tbox { 
        flex: 1; background: #f8f9fa; border-radius: 6px; 
        padding: 4px 2px; display: flex; flex-direction: column; align-items: center; border: 1px solid #eee;
    }
    .tbox .n { font-size: 15px; font-weight: 900; color: #333; line-height: 1; }
    .tbox .l { font-size: 7px; color: #555; text-transform: uppercase; font-weight: 800; margin-top: 2px; letter-spacing: 0.2px; }

    .cyan-text, .cyan-border .tbox { color: var(--unamad-cyan); border-bottom: 2px solid var(--unamad-cyan); }
    .magenta-text, .magenta-border .tbox { color: var(--unamad-magenta); border-bottom: 2px solid var(--unamad-magenta); }

    .premium-btn-enroll {
        background: var(--unamad-cyan); /* Volver al Celeste */
        color: white !important;
        text-decoration: none !important;
        display: flex; align-items: center; justify-content: center;
        gap: 8px; padding: 10px;
        border-radius: 10px; font-weight: 800; font-size: 11px;
        box-shadow: 0 5px 15px rgba(0, 174, 239, 0.2);
        transition: 0.3s;
        margin-top: 2px;
    }
    .premium-btn-enroll:hover { background: var(--unamad-magenta); transform: translateY(-1px); box-shadow: 0 8px 20px rgba(226, 0, 122, 0.3); }

    .premium-color-stripe { height: 4px; display: flex; width: 100%; }
    .stripe-magenta { background: var(--unamad-magenta); flex: 1; }
    .stripe-cyan { background: var(--unamad-cyan); flex: 1; }
    .stripe-green { background: var(--unamad-green); flex: 1; }

    @keyframes bubble-pulse { 0% { transform: scale(1); opacity: 0.6; } 100% { transform: scale(1.6); opacity: 0; } }
</style>

<script>
    function toggleCountdownBubble() {
        const panel = document.getElementById('bubble-panel');
        const reopen = document.getElementById('bubble-reopen');
        const isHidden = panel.style.display === 'none';
        if (isHidden) {
            panel.style.display = 'flex'; reopen.style.display = 'none';
        } else {
            panel.style.display = 'none'; reopen.style.display = 'flex';
        }
    }
    document.addEventListener('DOMContentLoaded', function () {
        function initTimer(id, accentColor) {
            const el = document.getElementById(id); if (!el) return;
            const target = new Date(el.getAttribute('data-date')).getTime();
            function tick() {
                const diff = target - Date.now();
                if (diff < 0) { el.innerHTML = `<div style="width:100%; text-align:center; color:var(--unamad-green); font-weight:800; font-size:11px;">EN CURSO</div>`; return; }
                const d = Math.floor(diff / 86400000); const h = Math.floor((diff % 86400000) / 3600000);
                const m = Math.floor((diff % 3600000) / 60000); const s = Math.floor((diff % 60000) / 1000);
                el.innerHTML = `
                    <div class="tbox"><span class="n">${String(d).padStart(2,'0')}</span><span class="l">Días</span></div>
                    <div class="tbox"><span class="n">${String(h).padStart(2,'0')}</span><span class="l">Hrs</span></div>
                    <div class="tbox"><span class="n">${String(m).padStart(2,'0')}</span><span class="l">Min</span></div>
                    <div class="tbox"><span class="n" style="color:${accentColor}">${String(s).padStart(2,'0')}</span><span class="l">Seg</span></div>
                `;
            }
            tick(); setInterval(tick, 1000);
        }
        initTimer('timer-examen', '#00aeef'); initTimer('timer-ciclo', '#e2007a');
        const panel = document.getElementById('bubble-panel'); const reopen = document.getElementById('bubble-reopen');
        if (window.innerWidth <= 768) { panel.style.display = 'none'; reopen.style.display = 'flex'; } 
        else { panel.style.display = 'flex'; reopen.style.display = 'none'; }
    });
</script>
@endif
