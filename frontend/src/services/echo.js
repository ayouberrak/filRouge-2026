import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

/**
 * Configuration de Laravel Echo pour Reverb (WebSocket natif Laravel 11)
 */
const echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY || 'avub6fjkxzhpujq6ntws',
    wsHost: import.meta.env.VITE_REVERB_HOST || 'localhost',
    wsPort: import.meta.env.VITE_REVERB_PORT || 8080,
    wssPort: import.meta.env.VITE_REVERB_PORT || 8080,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME || 'http') === 'https',
    enabledTransports: ['ws', 'wss'],
    // On passe le token pour les canaux privés (Sanctum)
    authEndpoint: 'http://localhost:8000/api/broadcasting/auth',
    auth: {
        headers: {
            get Authorization() {
                const token = localStorage.getItem('auth_token');
                return token ? `Bearer ${token}` : '';
            },
            Accept: 'application/json',
        },
    },
});

export default echo;
