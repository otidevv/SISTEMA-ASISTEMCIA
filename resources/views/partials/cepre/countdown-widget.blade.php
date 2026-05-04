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
            <div class="event-section cyan-border">
                <div class="section-title cyan-text">
                    <i class="fas fa-edit"></i>
                    <span>{{ $proximoExamen['nombre'] }}</span>
                </div>
                <div id="timer-examen" data-date="{{ $proximoExamen['fecha'] }}" class="timer-grid"></div>
            </div>
            @endif

            @if(isset($proximoCiclo) && $proximoCiclo)
            <div class="event-section magenta-border">
                <div class="section-title magenta-text">
                    <i class="fas fa-graduation-cap"></i>
                    <span>{{ $proximoCiclo['nombre'] }}</span>
                </div>
                <div id="timer-ciclo" data-date="{{ $proximoCiclo['fecha'] }}" class="timer-grid"></div>
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
        width: 260px;
        background: #ffffff;
        border-radius: 18px;
        overflow: hidden;
        border: 1px solid rgba(0, 174, 239, 0.15);
        box-shadow: 0 15px 40px rgba(0, 0, 0, 0.1);
        display: flex;
        flex-direction: column;
        position: relative;
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }

    /* --- Cabezera --- */
    .premium-header {
        background: var(--unamad-cyan);
        position: relative;
        padding: 6px 12px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-bottom: 2px solid var(--unamad-magenta);
        overflow: hidden;
        min-height: 40px;
    }

    .premium-header::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        background-image: url('/assets_cepre/img/kene_bold_white.png');
        background-size: 150px;
        background-position: center;
        opacity: 0.4;
        mix-blend-mode: screen;
        z-index: 1;
        pointer-events: none;
        transition: transform 0.8s ease;
    }
    .premium-header:hover::before { transform: scale(1.1); }

    .header-content { display: flex; align-items: center; gap: 10px; z-index: 2; position: relative; }
    
    .header-icon { 
        width: 24px; height: 24px;
        background: rgba(0, 0, 0, 0.15); 
        border-radius: 6px; 
        display: flex; align-items: center; justify-content: center; 
        color: white; font-size: 11px; 
        transition: transform 0.3s ease;
    }
    .premium-header:hover .header-icon { transform: rotate(15deg) scale(1.1); }
    
    .header-text { 
        display: flex; 
        flex-direction: column; 
        line-height: 1;
        text-shadow: 0 1px 3px rgba(0,0,0,0.4);
    }
    .header-pre { font-size: 8px; font-weight: 800; color: #fff; letter-spacing: 1.5px; text-transform: uppercase; }
    .header-main { margin: 0; color: white; font-size: 16px; font-weight: 900; letter-spacing: 0.5px; }

    .close-panel-btn { 
        background: rgba(0,0,0,0.1); 
        border: none; color: white; font-size: 12px; 
        cursor: pointer;
        width: 20px; height: 20px; 
        display: flex; align-items: center; justify-content: center; 
        border-radius: 50%;
        transition: all 0.3s ease;
        z-index: 2;
        position: relative;
    }
    .close-panel-btn:hover { background: var(--unamad-magenta); transform: rotate(90deg) scale(1.1); }

    .premium-body { 
        padding: 15px; 
        display: flex; 
        flex-direction: column; 
        gap: 12px; 
        background: #fff;
    }

    .event-section { 
        background: #f8fafc; 
        padding: 10px; 
        border-radius: 12px; 
        border-left: 3px solid transparent;
        box-shadow: 0 2px 6px rgba(0,0,0,0.02);
        transition: all 0.3s ease;
    }
    .event-section:hover { 
        transform: translateX(5px); 
        background: #fff;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    }
    
    .event-section.cyan-border { border-left-color: var(--unamad-cyan); }
    .event-section.magenta-border { border-left-color: var(--unamad-magenta); }

    .section-title { display: flex; align-items: center; gap: 8px; margin-bottom: 8px; transition: 0.3s; }
    .event-section:hover .section-title { transform: scale(1.02); }
    .section-title i { font-size: 12px; }
    .section-title span { font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; }

    .timer-grid { display: flex; gap: 6px; }
    .tbox { 
        flex: 1; 
        background: white; 
        border-radius: 8px; 
        padding: 6px 1px; 
        display: flex; 
        flex-direction: column; 
        align-items: center; 
        border: 1px solid #eef2f6;
        box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }
    .tbox:hover { transform: translateY(-3px); border-color: var(--unamad-cyan); box-shadow: 0 5px 10px rgba(0,0,0,0.05); }
    .tbox .n { font-size: 18px; font-weight: 900; line-height: 1; color: #1e293b; }
    .tbox .l { font-size: 7px; color: #94a3b8; text-transform: uppercase; font-weight: 800; margin-top: 3px; }

    .premium-btn-enroll {
        background: linear-gradient(135deg, var(--unamad-cyan) 0%, #0081b2 100%);
        color: white !important;
        text-decoration: none !important;
        display: flex; align-items: center; justify-content: center;
        gap: 10px; padding: 12px;
        border-radius: 12px; font-weight: 900; font-size: 12px;
        box-shadow: 0 8px 20px rgba(0, 174, 239, 0.2);
        transition: all 0.4s ease;
        text-transform: uppercase;
        letter-spacing: 0.8px;
    }
    .premium-btn-enroll:hover { 
        background: linear-gradient(135deg, var(--unamad-magenta) 0%, #a30058 100%); 
        transform: scale(1.02) translateY(-2px);
        box-shadow: 0 10px 25px rgba(226, 0, 122, 0.3);
    }

    .premium-color-stripe { height: 5px; display: flex; width: 100%; }
    .stripe-magenta { background: var(--unamad-magenta); flex: 1; }
    .stripe-cyan { background: var(--unamad-cyan); flex: 1; }
    .stripe-green { background: var(--unamad-green); flex: 1; }

    @keyframes bubble-pulse { 0% { transform: scale(1); opacity: 0.8; } 100% { transform: scale(2); opacity: 0; } }
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
