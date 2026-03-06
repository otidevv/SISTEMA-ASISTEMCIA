{{--
|--------------------------------------------------------------------------
| Animaciones Estacionales - CEPRE UNAMAD
|--------------------------------------------------------------------------
| Agrega aquí los efectos visuales de temporada.
| Cada efecto debe verificar la fecha y auto-desactivarse.
|
| Efectos actuales:
|   - Día de la Mujer: flores/corazones púrpuras (Mar 06 - Mar 09)
|   - Otoño / Académico: hojas cayendo (Resto de Marzo)
|--}}

@php
    $ahora = now();
    
    // Configuración de temporadas
    $esDiaMujer = $ahora->between(
        \Carbon\Carbon::parse('2026-03-05 00:00:00'),
        \Carbon\Carbon::parse('2026-03-09 23:59:59')
    );
    
    $esOtono = $ahora->month === 3 && !$esDiaMujer; // El resto de marzo si no es día de la mujer
@endphp

@if($esDiaMujer || $esOtono)
<canvas id="seasonal-canvas" 
    style="position:fixed;top:0;left:0;width:100%;height:100%;pointer-events:none;z-index:9999;"></canvas>

<script>
(function () {
    const canvas = document.getElementById('seasonal-canvas');
    const ctx    = canvas.getContext('2d');
    
    // Configuración según la temporada
    const isWomensDay = @json($esDiaMujer);
    
    // Estado del texto y explosión
    let textState = {
        phrases: [
            "Mujer: Fuerza, Coraje e Inspiración del Mundo",
            "¡Feliz Día de la Mujer!"
        ],
        currentIdx: 0,
        opacity: 0,
        scale: 0.8,
        active: isWomensDay,
        startTime: Date.now() + 500,
        phraseDuration: 4000, // Duración total por frase
        burstTriggered: false
    };
    
    const COLORS = isWomensDay 
        ? ['#F472B6', '#EC4899', '#D946EF', '#A855F7', '#FDF2F8'] 
        : ['#D97706', '#B45309', '#92400E', '#78350F', '#F59E0B'];
        
    const SHAPES = isWomensDay ? ['heart', 'petal'] : ['leaf', 'circle'];
    const NUM = isWomensDay ? (window.innerWidth < 600 ? 60 : 100) : 40;
    
    let pieces = [], W, H, DPR;

    function resize() {
        DPR = window.devicePixelRatio || 1;
        W = window.innerWidth;
        H = window.innerHeight;
        canvas.width = W * DPR;
        canvas.height = H * DPR;
        canvas.style.width = W + 'px';
        canvas.style.height = H + 'px';
        ctx.scale(DPR, DPR);
    }

    function rnd(a, b) { return a + Math.random() * (b - a); }

    function newPiece(fromBurst = false) {
        let x, y, speed, drift, opacity;
        
        if (fromBurst && isWomensDay) {
            x = W / 2 + rnd(-60, 60);
            y = H / 2 + rnd(-25, 25);
            speed = rnd(1.5, 4.5);
            drift = rnd(-2.5, 2.5);
            opacity = rnd(0.8, 1);
        } else {
            x = rnd(0, W);
            y = rnd(-H, 0);
            speed = rnd(0.7, 2.2);
            drift = rnd(-0.3, 0.3);
            opacity = rnd(0.4, 0.7);
        }

        return {
            x: x, y: y,
            w: rnd(8, 20), h: rnd(8, 20),
            color: COLORS[Math.floor(Math.random() * COLORS.length)],
            shape: SHAPES[Math.floor(Math.random() * SHAPES.length)],
            speed: speed,
            angle: rnd(0, Math.PI * 2),
            spin:  rnd(-0.03, 0.03),
            drift: drift,
            opacity: opacity,
            oscillation: rnd(0.01, 0.02),
            amplitude: rnd(0.5, 1.2),
            isBurst: fromBurst
        };
    }

    function drawShape(ctx, shape, w, h) {
        if (shape === 'heart') {
            ctx.beginPath();
            ctx.moveTo(0, h/4);
            ctx.bezierCurveTo(0, 0, -w/2, 0, -w/2, h/4);
            ctx.bezierCurveTo(-w/2, h/2, 0, h*0.7, 0, h);
            ctx.bezierCurveTo(0, h*0.7, w/2, h/2, w/2, h/4);
            ctx.bezierCurveTo(w/2, 0, 0, 0, 0, h/4);
            ctx.fill();
        } else if (shape === 'petal' || shape === 'leaf') {
            ctx.beginPath();
            ctx.moveTo(0, -h/2);
            ctx.quadraticCurveTo(w/2, 0, 0, h/2);
            ctx.quadraticCurveTo(-w/2, 0, 0, -h/2);
            ctx.fill();
        } else {
            ctx.beginPath();
            ctx.arc(0, 0, w / 2, 0, Math.PI * 2);
            ctx.fill();
        }
    }

    function wrapText(context, text, x, y, maxWidth, lineHeight) {
        const words = text.split(' ');
        let line = '';
        let lines = [];

        for (let n = 0; n < words.length; n++) {
            let testLine = line + words[n] + ' ';
            let metrics = context.measureText(testLine);
            let testWidth = metrics.width;
            if (testWidth > maxWidth && n > 0) {
                lines.push(line);
                line = words[n] + ' ';
            } else {
                line = testLine;
            }
        }
        lines.push(line);

        const totalHeight = lines.length * lineHeight;
        let startY = y - (totalHeight / 2) + (lineHeight / 2);

        for (let k = 0; k < lines.length; k++) {
            const currentLine = lines[k].trim();
            
            // Dibujamos el contorno (stroke) para legibilidad
            context.lineWidth = 6;
            context.strokeStyle = 'rgba(88, 28, 135, 0.8)'; // Púrpura oscuro
            context.strokeText(currentLine, x, startY);
            
            // Dibujamos el texto principal
            context.fillText(currentLine, x, startY);
            startY += lineHeight;
        }
    }

    function drawText() {
        if (!textState.active) return;
        
        const now = Date.now();
        const elapsedSinceStart = now - textState.startTime;
        const currentIdx = Math.floor(elapsedSinceStart / textState.phraseDuration);
        const elapsedInPhrase = elapsedSinceStart % textState.phraseDuration;
        
        if (elapsedSinceStart < 0) return;
        
        if (currentIdx >= textState.phrases.length) {
            textState.active = false;
            return;
        }

        if (textState.currentIdx !== currentIdx) {
            textState.currentIdx = currentIdx;
            textState.burstTriggered = false;
        }

        let opacity = 0;
        let scale = 1;

        if (elapsedInPhrase < 1000) {
            opacity = elapsedInPhrase / 1000;
            scale = 0.8 + (opacity * 0.2);
        } else if (elapsedInPhrase < 3000) {
            opacity = 1;
            scale = 1;
            if (!textState.burstTriggered) {
                const burstCount = W < 600 ? 25 : 45;
                for(let i=0; i<burstCount; i++) pieces.push(newPiece(true));
                textState.burstTriggered = true;
            }
        } else {
            opacity = 1 - (elapsedInPhrase - 3000) / 1000;
            scale = 1 + (1 - opacity) * 0.1;
        }

        ctx.save();
        ctx.globalAlpha = opacity;
        
        const content = textState.phrases[currentIdx];
        let fontSize = W < 600 ? 28 : (W < 1000 ? 45 : 65);
        ctx.font = `bold ${fontSize}px "Outfit", sans-serif`;
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        
        // Brillo exterior suave
        ctx.shadowColor = 'rgba(236, 72, 153, 0.5)';
        ctx.shadowBlur = 10;
        
        ctx.fillStyle = '#fff0f6';
        
        ctx.translate(W / 2, H / 2);
        ctx.scale(scale, scale);
        
        const maxWidth = W * 0.85;
        const lineHeight = fontSize * 1.2;
        wrapText(ctx, content, 0, 0, maxWidth, lineHeight);
        
        ctx.restore();
    }

    function draw() {
        ctx.clearRect(0, 0, W, H);
        drawText();
        
        pieces.forEach((p, i) => {
            ctx.save();
            ctx.globalAlpha = p.opacity;
            ctx.translate(p.x, p.y);
            ctx.rotate(p.angle);
            ctx.fillStyle = p.color;
            drawShape(ctx, p.shape, p.w, p.h);
            ctx.restore();

            p.y += p.speed;
            if (p.isBurst) {
                p.x += p.drift;
                p.speed *= 0.98;
                p.opacity *= 0.992;
            } else {
                p.x += p.drift + Math.sin(p.y * p.oscillation) * p.amplitude;
            }
            p.angle += p.spin;

            if (p.y > H + 20 || p.opacity < 0.01) {
                if (p.isBurst) {
                    pieces.splice(i, 1);
                } else {
                    Object.assign(p, newPiece(false));
                }
            }
        });
        requestAnimationFrame(draw);
    }

    window.addEventListener('resize', resize);
    resize();
    for (let i = 0; i < 40; i++) pieces.push(newPiece(false));
    draw();
})();
</script>
@endif
