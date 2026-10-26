import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

const scheme = import.meta.env.VITE_REVERB_SCHEME ?? 'http';
const forceTLS = scheme === 'https';
const port = Number(import.meta.env.VITE_REVERB_PORT ?? (forceTLS ? 443 : 80));

const echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST ?? window.location.hostname,
    wsPort: port,
    wssPort: port,
    forceTLS,
    enabledTransports: ['ws', 'wss'],
    disableStats: true,
});

window.Echo = echo;

const REALTIME_EVENTS = ['pesanan.baru', 'status.berubah', 'panggilan.pelayan', 'menu.diperbarui', 'sesi.berubah'];

const DEFAULT_CHANNELS = ['kds.dapur', 'kds.bar', 'kasir', 'pelayan', 'admin', 'menu'];

const channels = Array.isArray(window.__CHANNELS__) && window.__CHANNELS__.length
    ? window.__CHANNELS__
    : DEFAULT_CHANNELS;

const emit = (name, payload) => {
    window.dispatchEvent(new CustomEvent('realtime:' + name, { detail: payload }));
    window.dispatchEvent(new CustomEvent('realtime', { detail: { name, payload } }));
};

channels.forEach((channelName) => {
    const channel = echo.channel(channelName);
    REALTIME_EVENTS.forEach((eventName) => {
        channel.listen('.' + eventName, (payload) => emit(eventName, payload));
    });
});

window.realtimeReload = (delay = 500) => {
    if (window.realtimeReload._timer) return;
    window.realtimeReload._timer = setTimeout(() => window.location.reload(), delay);
};

export default echo;

