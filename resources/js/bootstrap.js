import axios from 'axios';

window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.withCredentials = true;
window.axios.defaults.xsrfCookieName = 'XSRF-TOKEN';
window.axios.defaults.xsrfHeaderName = 'X-XSRF-TOKEN';

const csrfMeta = document.head.querySelector('meta[name="csrf-token"]');
if (csrfMeta?.content) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfMeta.content;
}

/**
 * Keep Laravel CSRF meta + hidden inputs in sync.
 * Prefer a server-returned token when available.
 */
window.syncCsrfToken = function syncCsrfToken(tokenFromServer) {
    const meta = document.head.querySelector('meta[name="csrf-token"]');
    const token = tokenFromServer || meta?.content;
    if (!token) return null;

    if (meta) {
        meta.content = token;
    }

    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token;

    document.querySelectorAll('input[name="_token"]').forEach((input) => {
        input.value = token;
    });

    return token;
};

window.refreshCsrfToken = async function refreshCsrfToken() {
    try {
        const res = await fetch('/refresh-session', {
            method: 'GET',
            credentials: 'same-origin',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                Accept: 'application/json',
            },
        });
        if (!res.ok) return null;
        const data = await res.json();
        if (data?.csrf_token) {
            return window.syncCsrfToken(data.csrf_token);
        }
        return window.syncCsrfToken();
    } catch (e) {
        return null;
    }
};

document.addEventListener('submit', () => {
    window.syncCsrfToken?.();
}, true);

document.addEventListener('visibilitychange', () => {
    if (document.visibilityState === 'visible' && typeof window.refreshCsrfToken === 'function') {
        window.refreshCsrfToken();
    }
});
