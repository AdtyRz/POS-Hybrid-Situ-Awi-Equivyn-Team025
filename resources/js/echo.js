import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

const scheme = import.meta.env.VITE_REVERB_SCHEME ?? 'http';
const forceTLS = scheme === 'https';
const port = Number(import.meta.env.VITE_REVERB_PORT ?? (forceTLS ? 443 : 80));

let echo = null;

try {
    echo = new Echo({
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
} catch (error) {
    console.warn('WebSocket realtime tidak tersedia, memakai polling fallback.', error);
}

const REALTIME_EVENTS = ['pesanan.baru', 'status.berubah', 'panggilan.pelayan', 'menu.diperbarui', 'sesi.berubah'];

const DEFAULT_CHANNELS = [];

const channels = Array.isArray(window.__CHANNELS__)
    ? window.__CHANNELS__
    : DEFAULT_CHANNELS;

const emit = (name, payload) => {
    window.dispatchEvent(new CustomEvent('realtime:' + name, { detail: payload }));
    window.dispatchEvent(new CustomEvent('realtime', { detail: { name, payload } }));
};

if (echo) {
    try {
        channels.forEach((channelName) => {
            const channel = echo.channel(channelName);
            REALTIME_EVENTS.forEach((eventName) => {
                channel.listen('.' + eventName, (payload) => emit(eventName, payload));
            });
        });
    } catch (error) {
        console.warn('Gagal memasang listener realtime.', error);
    }
}

window.realtimeReload = (delay = 500) => {
    if (window.realtimeReload._timer) return;
    window.realtimeReload._timer = setTimeout(() => window.location.reload(), delay);
};

/*
 * Fallback polling. Ini yang membuat halaman tetap sinkron ketika Reverb
 * tidak berjalan atau WebSocket terputus. Polling hanya membaca satu hash
 * versi data, lalu memicu event realtime bila ada perubahan.
 */
const versionUrl = document.querySelector('meta[name="realtime-version-url"]')?.content;
const versionMeta = document.querySelector('meta[name="realtime-version"]')?.content;
const pollInterval = Number(window.__REALTIME_POLL_MS__ ?? 4000);
let lastVersion = versionMeta || null;
let pollTimer = null;

const pollVersion = async () => {
    if (pollTimer) {
        clearTimeout(pollTimer);
        pollTimer = null;
    }

    if (!versionUrl) return;

    if (document.hidden) {
        pollTimer = setTimeout(pollVersion, pollInterval);
        return;
    }

    try {
        const response = await fetch(versionUrl, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            cache: 'no-store',
            credentials: 'same-origin',
        });

        if (response.ok) {
            const data = await response.json();

            if (lastVersion && data.version && data.version !== lastVersion) {
                emit('fallback', { version: data.version });
            }

            lastVersion = data.version || lastVersion;
        }
    } catch (error) {
        // Jaringan sedang bermasalah; percobaan berikutnya tetap berjalan.
    }

    pollTimer = setTimeout(pollVersion, pollInterval);
};

if (versionUrl) {
    pollTimer = setTimeout(pollVersion, pollInterval);

    document.addEventListener('visibilitychange', () => {
        if (!document.hidden) pollVersion();
    });

    window.realtimePollNow = pollVersion;
}

export default echo;
