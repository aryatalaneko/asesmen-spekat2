import Echo from 'laravel-echo';

import Pusher from 'pusher-js';
window.Pusher = Pusher;

const currentScheme = window.location.protocol === 'https:' ? 'https' : 'http';
const reverbScheme = import.meta.env.VITE_REVERB_SCHEME || currentScheme;
const reverbHost = window.location.hostname || import.meta.env.VITE_REVERB_HOST;
const reverbPort = Number(
    import.meta.env.VITE_REVERB_PORT || (reverbScheme === 'https' ? 443 : 8080)
);

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: reverbHost,
    wsPort: reverbPort,
    wssPort: reverbPort,
    forceTLS: reverbScheme === 'https',
    enabledTransports: reverbScheme === 'https' ? ['wss', 'ws'] : ['ws'],
});
