@if(isset($proximoExamen) && $proximoExamen || isset($proximoCiclo) && $proximoCiclo)
<div id="countdown-bubble" style="position:fixed; right:16px; bottom:80px; z-index:9998; display:flex; flex-direction:column; align-items:flex-end; gap:6px;">

    <!-- Botón para re-abrir (solo visible cuando panel está cerrado) -->
    <button id="bubble-reopen" onclick="toggleCountdownBubble()"
        style="display:none;border:none;background:none;cursor:pointer;padding:0;animation:bubble-pulse 2.5s infinite;border-radius:50%;width:65px;height:65px;">
        <img src="{{ asset('assets_cepre/img/cronometro.png') }}" alt="Próximos Eventos"
             style="width:100%;height:100%;object-fit:contain;display:block;"
             onerror="this.parentElement.style.background='var(--magenta-unamad)';">
    </button>

    <!-- Panel principal -->
    <div id="bubble-panel"
        style="background:linear-gradient(160deg,var(--azul-oscuro) 0%,#0b2a4a 100%);border-radius:16px;overflow:hidden;width:240px;box-shadow:0 10px 30px rgba(0,0,0,0.4);border:1px solid rgba(255,255,255,0.08);display:flex;flex-direction:column;">

        <!-- Header: crono2.png como fondo CSS para no distorsionar -->
        <div class="bubble-header"
             style="position:relative;width:100%;height:58px;background:url('{{ asset('assets_cepre/img/crono2.png') }}') center/cover no-repeat;overflow:visible;">
            <!-- Texto sobre la zona izquierda -->
            <div style="position:absolute;top:0;left:0;right:65px;height:100%;display:flex;align-items:center;padding-left:14px;">
                <p style="margin:0;color:white;font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:0.8px;text-shadow:0 1px 4px rgba(0,0,0,0.5);line-height:1.3;">
                    📅 Próximos<br>Eventos
                </p>
            </div>
            <!-- X para cerrar -->
            <button onclick="toggleCountdownBubble()"
                style="position:absolute;top:5px;right:5px;background:rgba(0,0,0,0.25);border:none;color:white;font-size:13px;cursor:pointer;border-radius:50%;width:22px;height:22px;line-height:22px;text-align:center;padding:0;z-index:10;">&times;</button>
        </div>

        <!-- Contenido -->
        <div style="padding:12px 14px;display:flex;flex-direction:column;gap:12px;">

            @if(isset($proximoExamen) && $proximoExamen)
            <div>
                <div style="display:flex;align-items:center;gap:6px;margin-bottom:8px;">
                    <div style="width:20px;height:20px;border-radius:6px;background:rgba(0,174,239,0.15);display:flex;align-items:center;justify-content:center;">
                        <i class="fas fa-edit" style="color:var(--cyan-acento);font-size:9px;"></i>
                    </div>
                    <span style="color:var(--cyan-acento);font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.8px;">{{ $proximoExamen['nombre'] }}</span>
                </div>
                <div id="timer-examen" data-date="{{ $proximoExamen['fecha'] }}" style="display:flex;gap:5px;"></div>
            </div>
            @endif

            @if(isset($proximoCiclo) && $proximoCiclo)
            <div style="border-top:1px solid rgba(255,255,255,0.06);padding-top:10px;">
                <div style="display:flex;align-items:center;gap:6px;margin-bottom:8px;">
                    <div style="width:20px;height:20px;border-radius:6px;background:rgba(236,0,140,0.15);display:flex;align-items:center;justify-content:center;">
                        <i class="fas fa-door-open" style="color:var(--magenta-unamad);font-size:9px;"></i>
                    </div>
                    <span style="color:var(--magenta-unamad);font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:0.8px;">{{ $proximoCiclo['nombre'] }}</span>
                </div>
                <div id="timer-ciclo" data-date="{{ $proximoCiclo['fecha'] }}" style="display:flex;gap:5px;"></div>
            </div>
            <a href="{{ route('register') }}"
                style="display:block;text-align:center;background:var(--magenta-unamad);color:white;border-radius:50px;padding:7px 12px;font-weight:800;font-size:11px;text-decoration:none;letter-spacing:1px;box-shadow:0 3px 10px rgba(236,0,140,0.35);transition:all 0.3s;margin-top:2px;">
                ¡INSCRÍBETE AHORA!
            </a>
            @endif
        </div>
    </div>
</div>

<style>
    @keyframes bubble-pulse {
        0%   { box-shadow: 0 0 0 0 rgba(236, 0, 140, 0.45); }
        70%  { box-shadow: 0 0 0 10px rgba(236, 0, 140, 0); }
        100% { box-shadow: 0 0 0 0 rgba(236, 0, 140, 0); }
    }
    #bubble-reopen:hover { transform: scale(1.05); }
    .tbox {
        flex:1; background:rgba(255,255,255,0.04); border:1px solid rgba(255,255,255,0.07);
        border-radius:8px; padding:5px 3px; display:flex; flex-direction:column; align-items:center; min-width:0;
    }
    .tbox .n { font-size:17px; font-weight:900; color:white; line-height:1; }
    .tbox .l { font-size:8px; color:#64748b; text-transform:uppercase; letter-spacing:0.5px; margin-top:3px; }
    @media (max-width: 768px) {
        #countdown-bubble { right:10px; bottom:72px; }
        #bubble-panel { width:200px; }
        .bubble-header { height:46px !important; }
        .bubble-avatar { display:none !important; }
        .tbox .n { font-size:15px; }
    }
</style>

<script>
    function toggleCountdownBubble() {
        const panel  = document.getElementById('bubble-panel');
        const reopen = document.getElementById('bubble-reopen');
        const open   = panel.style.display !== 'none';
        panel.style.display  = open ? 'none' : 'flex';
        panel.style.flexDirection = 'column';
        reopen.style.display = open ? 'flex' : 'none';
    }

    document.addEventListener('DOMContentLoaded', function () {
        function initTimer(id, accentColor) {
            const el = document.getElementById(id);
            if (!el) return;
            const target = new Date(el.getAttribute('data-date')).getTime();
            function tick() {
                const diff = target - Date.now();
                if (diff < 0) {
                    el.innerHTML = '<span style="color:var(--verde-cepre);font-size:11px;font-weight:800;"><i class="fas fa-check-circle"></i> ¡En curso!</span>';
                    return;
                }
                const d = Math.floor(diff / 86400000);
                const h = Math.floor((diff % 86400000) / 3600000);
                const m = Math.floor((diff % 3600000) / 60000);
                const s = Math.floor((diff % 60000) / 1000);
                el.innerHTML = `
                    <div class="tbox"><span class="n">${String(d).padStart(2,'0')}</span><span class="l">Días</span></div>
                    <div class="tbox"><span class="n">${String(h).padStart(2,'0')}</span><span class="l">Hrs</span></div>
                    <div class="tbox"><span class="n">${String(m).padStart(2,'0')}</span><span class="l">Min</span></div>
                    <div class="tbox" style="border-color:rgba(236,0,140,0.2)"><span class="n" style="color:${accentColor}">${String(s).padStart(2,'0')}</span><span class="l">Seg</span></div>
                `;
            }
            tick();
            setInterval(tick, 1000);
        }
        initTimer('timer-examen', 'var(--cyan-acento)');
        initTimer('timer-ciclo', 'var(--magenta-unamad)');
    });
</script>
@endif
