{{--
|--------------------------------------------------------------------------
| Animaciones Estacionales - CEPRE UNAMAD
|--------------------------------------------------------------------------
| Agrega aquí los efectos visuales de temporada.
| Cada efecto debe verificar la fecha y auto-desactivarse.
|
| Efectos disponibles:
|   - Carnaval: confetti/pica-pica (hasta 01 Mar 2026)
|   - Para Navidad: puedes agregar nieve aquí siguiente temporada
|--}}

@php
    $ahora        = now();
    $carnavalFin  = \Carbon\Carbon::parse('2026-03-01');
    $esCarnaval   = $ahora->lt($carnavalFin);
@endphp

@if($esCarnaval)
{{-- 🎉 CARNAVAL: Pica-Pica / Confetti --}}
<canvas id="carnival-canvas"
    style="position:fixed;top:0;left:0;width:100%;height:100%;pointer-events:none;z-index:9999;"></canvas>
<script>
(function () {
    const canvas = document.getElementById('carnival-canvas');
    const ctx    = canvas.getContext('2d');

    const COLORS = [
        '#EC008C', // Magenta UNAMAD
        '#00AEEF', // Cyan CEPRE
        '#2EC866', // Verde CEPRE
        '#FFD700', // Dorado
        '#FF6B35', // Naranja
        '#A855F7', // Morado
        '#F43F5E', // Rosa
        '#FACC15', // Amarillo
        '#38BDF8', // Celeste
        '#FB923C', // Naranja claro
    ];
    const SHAPES = ['rect', 'circle', 'ribbon'];
    const NUM    = 90;
    let pieces = [], W, H;

    function resize() {
        W = canvas.width  = window.innerWidth;
        H = canvas.height = window.innerHeight;
    }
    function rnd(a, b) { return a + Math.random() * (b - a); }

    function newPiece(startTop = false) {
        return {
            x: rnd(0, W), y: startTop ? rnd(-H, 0) : rnd(-H, H),
            w: rnd(6, 14), h: rnd(4, 10),
            color: COLORS[Math.floor(Math.random() * COLORS.length)],
            shape: SHAPES[Math.floor(Math.random() * SHAPES.length)],
            speed: rnd(1.2, 3.5), angle: rnd(0, Math.PI * 2),
            spin:  rnd(-0.08, 0.08), drift: rnd(-0.6, 0.6),
            opacity: rnd(0.65, 1),
        };
    }

    function draw() {
        ctx.clearRect(0, 0, W, H);
        pieces.forEach(p => {
            ctx.save();
            ctx.globalAlpha = p.opacity;
            ctx.translate(p.x, p.y);
            ctx.rotate(p.angle);
            ctx.fillStyle = p.color;

            if (p.shape === 'rect') {
                ctx.fillRect(-p.w / 2, -p.h / 2, p.w, p.h);
            } else if (p.shape === 'circle') {
                ctx.beginPath();
                ctx.arc(0, 0, p.w / 2, 0, Math.PI * 2);
                ctx.fill();
            } else {
                ctx.beginPath();
                ctx.moveTo(-p.w / 2, -p.h / 4);
                ctx.quadraticCurveTo(0, p.h / 2, p.w / 2, -p.h / 4);
                ctx.quadraticCurveTo(0, -p.h, -p.w / 2, -p.h / 4);
                ctx.fill();
            }
            ctx.restore();

            p.y += p.speed;
            p.x += p.drift + Math.sin(p.y * 0.03) * 0.5;
            p.angle += p.spin;

            if (p.y > H + 20) { Object.assign(p, newPiece(true)); p.y = -20; }
        });
        requestAnimationFrame(draw);
    }

    window.addEventListener('resize', resize);
    resize();
    for (let i = 0; i < NUM; i++) pieces.push(newPiece());
    draw();
})();
</script>
@endif

{{--
| ❄️ NAVIDAD (ejemplo para diciembre):
|
| @if(now()->month === 12)
|   ... código de nieve aquí ...
| @endif
--}}
