<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sesión Expirada - CEPRE UNAMAD</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 50%, #7e22ce 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
            position: relative;
            overflow: hidden;
        }
        
        /* Animated background circles */
        body::before,
        body::after {
            content: '';
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.05);
            animation: float 20s infinite ease-in-out;
        }
        
        body::before {
            width: 300px;
            height: 300px;
            top: -100px;
            left: -100px;
        }
        
        body::after {
            width: 400px;
            height: 400px;
            bottom: -150px;
            right: -150px;
            animation-delay: -10s;
        }
        
        @keyframes float {
            0%, 100% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(50px, 50px) scale(1.1); }
        }
        
        .container {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
            padding: 50px 40px;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            text-align: center;
            max-width: 500px;
            width: 100%;
            animation: slideUp 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            position: relative;
            z-index: 1;
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(50px) scale(0.9);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }
        
        .icon-container {
            margin: 0 auto 30px;
            width: 100px;
            height: 100px;
            position: relative;
        }
        
        .lock-icon {
            width: 100%;
            height: 100%;
            animation: lockShake 0.8s ease-in-out;
        }
        
        @keyframes lockShake {
            0%, 100% { transform: rotate(0deg); }
            25% { transform: rotate(-10deg); }
            75% { transform: rotate(10deg); }
        }
        
        h1 {
            color: #1e3c72;
            margin: 0 0 15px 0;
            font-size: 2.2rem;
            font-weight: 700;
            letter-spacing: -0.5px;
        }
        
        .subtitle {
            color: #7e22ce;
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 25px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        p {
            color: #555;
            margin: 0 0 25px 0;
            line-height: 1.7;
            font-size: 1.05rem;
        }
        
        .countdown-container {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 15px;
            padding: 25px;
            margin: 30px 0;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
        }
        
        .countdown-label {
            color: rgba(255, 255, 255, 0.9);
            font-size: 0.9rem;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .countdown {
            font-size: 4rem;
            font-weight: 800;
            color: white;
            margin: 0;
            text-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            animation: pulse 1s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        
        .info-text {
            font-size: 0.95rem;
            color: #888;
            margin: 20px 0;
        }
        
        .btn {
            display: inline-block;
            padding: 15px 40px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1.05rem;
            transition: all 0.3s ease;
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
            border: none;
            cursor: pointer;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 30px rgba(102, 126, 234, 0.6);
        }
        
        .btn:active {
            transform: translateY(0);
        }
        
        .footer-text {
            margin-top: 30px;
            font-size: 0.85rem;
            color: #999;
        }
        
        /* Responsive */
        @media (max-width: 600px) {
            .container {
                padding: 40px 25px;
            }
            
            h1 {
                font-size: 1.8rem;
            }
            
            .countdown {
                font-size: 3rem;
            }
            
            .btn {
                padding: 12px 30px;
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon-container">
            <!-- SVG Lock Icon -->
            <svg class="lock-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <linearGradient id="lockGradient" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" style="stop-color:#667eea;stop-opacity:1" />
                        <stop offset="100%" style="stop-color:#764ba2;stop-opacity:1" />
                    </linearGradient>
                </defs>
                <path d="M19 11H5C3.89543 11 3 11.8954 3 13V20C3 21.1046 3.89543 22 5 22H19C20.1046 22 21 21.1046 21 20V13C21 11.8954 20.1046 11 19 11Z" 
                      stroke="url(#lockGradient)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" fill="rgba(102, 126, 234, 0.1)"/>
                <path d="M7 11V7C7 5.67392 7.52678 4.40215 8.46447 3.46447C9.40215 2.52678 10.6739 2 12 2C13.3261 2 14.5979 2.52678 15.5355 3.46447C16.4732 4.40215 17 5.67392 17 7V11" 
                      stroke="url(#lockGradient)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <circle cx="12" cy="16" r="1.5" fill="url(#lockGradient)"/>
                <path d="M12 17.5V19" stroke="url(#lockGradient)" stroke-width="2" stroke-linecap="round"/>
            </svg>
        </div>
        
        <h1>Sesión Expirada</h1>
        <p class="subtitle">CEPRE UNAMAD</p>
        <p>Tu sesión ha expirado por seguridad. No te preocupes, serás redirigido automáticamente.</p>
        
        <div class="countdown-container">
            <div class="countdown-label">Redirigiendo en</div>
            <div class="countdown" id="countdown">3</div>
        </div>
        
        <p class="info-text">¿No quieres esperar?</p>
        <a href="{{ $redirectUrl ?? url()->previous() ?? route('login') }}" class="btn">Continuar Ahora</a>
        
        <p class="footer-text">Centro Preuniversitario UNAMAD</p>
    </div>

    <script>
        // Redirigir automáticamente después de 3 segundos
        let seconds = 3;
        const countdownEl = document.getElementById('countdown');
        const redirectUrl = '{{ $redirectUrl ?? url()->previous() ?? route("login") }}';

        const interval = setInterval(() => {
            seconds--;
            countdownEl.textContent = seconds;
            
            if (seconds <= 0) {
                clearInterval(interval);
                window.location.replace(redirectUrl);
            }
        }, 1000);

        // Redirección manual
        document.querySelector('.btn').addEventListener('click', function(e) {
            e.preventDefault();
            clearInterval(interval);
            window.location.replace(redirectUrl);
        });
    </script>
</body>
</html>
