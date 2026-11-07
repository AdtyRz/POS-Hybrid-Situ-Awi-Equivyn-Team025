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

const REALTIME_EVENTS = ['pesanan.baru', 'status.berubah', 'panggilan.pelayan', 'menu.diperbarui', 'sesi.berubah', 'pengaturan.diperbarui', 'admin.diperbarui'];

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
            const channel = echo.private(channelName);
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


let liveRefreshTimer = null;
let liveRefreshInFlight = false;
let liveRefreshQueued = false;

const hasLiveRegions = () => document.querySelector('[data-live]') !== null;

const regionContainsFocus = (region) => {
    const active = document.activeElement;

    if (!active || active === document.body) return false;

    const isTyping = ['INPUT', 'TEXTAREA', 'SELECT'].includes(active.tagName)
        || active.isContentEditable;

    return isTyping && region.contains(active);
};

const applyLiveDocument = (doc) => {
    const regions = [];

    document.querySelectorAll('[data-live]').forEach((target) => {
        const name = target.dataset.live;

        if (!name || regionContainsFocus(target)) return;

        const fresh = doc.querySelector('[data-live="' + name + '"]');

        if (!fresh) return;

        target.innerHTML = fresh.innerHTML;
        regions.push(target);

        if (window.Alpine && typeof window.Alpine.initTree === 'function') {
            window.Alpine.initTree(target);
        }
    });

    window.dispatchEvent(new CustomEvent('live:updated', {
        detail: { document: doc, regions },
    }));

    return regions.length > 0;
};

const fetchLiveDocument = async () => {
    if (liveRefreshInFlight) {
        liveRefreshQueued = true;
        return false;
    }

    if (document.hidden) return false;

    liveRefreshInFlight = true;

    try {
        const response = await fetch(window.location.href, {
            headers: {
                'Accept': 'text/html',
                'X-Requested-With': 'XMLHttpRequest',
            },
            cache: 'no-store',
            credentials: 'same-origin',
        });

        if (!response.ok) return false;

        const contentType = response.headers.get('content-type') || '';

        if (!contentType.includes('text/html')) return false;

        const doc = new DOMParser().parseFromString(await response.text(), 'text/html');

        return applyLiveDocument(doc);
    } catch (error) {

        return false;
    } finally {
        liveRefreshInFlight = false;

        if (liveRefreshQueued) {
            liveRefreshQueued = false;
            window.refreshLiveRegions({ delay: 250 });
        }
    }
};

window.refreshLiveRegions = ({ delay = 350, immediate = false } = {}) => {
    if (!hasLiveRegions()) return Promise.resolve(false);

    if (liveRefreshTimer) {
        clearTimeout(liveRefreshTimer);
        liveRefreshTimer = null;
    }

    if (immediate) return fetchLiveDocument();

    return new Promise((resolve) => {
        liveRefreshTimer = setTimeout(() => {
            liveRefreshTimer = null;
            fetchLiveDocument().then(resolve);
        }, delay);
    });
};

window.addEventListener('realtime', () => {
    if (hasLiveRegions()) {
        window.refreshLiveRegions();
    }
});


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
