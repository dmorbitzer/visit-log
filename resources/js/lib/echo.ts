import EchoClass from 'laravel-echo';
import Pusher from 'pusher-js';

let _echo: any = null;

export function getEcho(): any {
    if (typeof window === 'undefined') {
        return null;
    }

    if (_echo) {
        return _echo;
    }

    window.Pusher = Pusher;

    _echo = new EchoClass({
        broadcaster: 'reverb',
        key: import.meta.env.VITE_REVERB_APP_KEY,
        wsHost: import.meta.env.VITE_REVERB_HOST,
        wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
        wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
        forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
        enabledTransports: ['ws', 'wss'],
    });

    return _echo;
}
