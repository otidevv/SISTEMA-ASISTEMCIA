import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

// Esta parte es CRÍTICA - configura Echo solo si no está ya configurado para evitar duplicados
if (!window.Echo) {
    window.Echo = new Echo({
        broadcaster: 'reverb',
        key: import.meta.env.VITE_REVERB_APP_KEY || 'iv9wx1kfwnwactpwfzwn',
        wsHost: import.meta.env.VITE_REVERB_HOST || '127.0.0.1',
        wsPort: import.meta.env.VITE_REVERB_PORT || 8081,
        wssPort: import.meta.env.VITE_REVERB_PORT || 8081,
        forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
        enabledTransports: ['ws', 'wss'],
    });
}

// Echo configurado en bootstrap.
