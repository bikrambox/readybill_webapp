import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;
Pusher.logToConsole = true; // Enable Pusher debug logs

try {
    if (!import.meta.env.VITE_REVERB_APP_KEY) {
        throw new Error('VITE_REVERB_APP_KEY is not defined in .env');
    }
    if (!import.meta.env.VITE_REVERB_HOST) {
        throw new Error('VITE_REVERB_HOST is not defined in .env');
    }
    if (!import.meta.env.VITE_REVERB_PORT) {
        throw new Error('VITE_REVERB_PORT is not defined in .env');
    }
    if (!import.meta.env.VITE_REVERB_SCHEME) {
        throw new Error('VITE_REVERB_SCHEME is not defined in .env');
    }

    window.Echo = new Echo({
        broadcaster: 'reverb',
        key: import.meta.env.VITE_REVERB_APP_KEY,
        wsHost: import.meta.env.VITE_REVERB_HOST,
        wsPort: import.meta.env.VITE_REVERB_PORT,
        wssPort: import.meta.env.VITE_REVERB_PORT,
        scheme: import.meta.env.VITE_REVERB_SCHEME,
        enabledTransports: ['ws', 'wss'],
        forceTLS: import.meta.env.VITE_REVERB_SCHEME === 'https',
        disableStats: true,
    });

    console.log('Laravel Echo initialized successfully', window.Echo);

} catch (error) {
    console.error('Failed to initialize Laravel Echo:', error);
}
