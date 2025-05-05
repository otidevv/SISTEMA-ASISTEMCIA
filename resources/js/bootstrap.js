import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

// Esta parte es CRÍTICA - configura Echo antes de exportarlo
window.Echo = new Echo({
    broadcaster: 'reverb',
    key: 'iv9wx1kfwnwactpwfzwn',
    wsHost: 'localhost',
    wsPort: 8080,
    wssPort: 8080,
    forceTLS: false,
    enabledTransports: ['ws', 'wss'],
});

// Agrega esto para depuración
console.log('Echo configurado en bootstrap:', window.Echo);
