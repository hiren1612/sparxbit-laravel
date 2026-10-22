// Browser (Web Push) notifications client.
// Exposes window.SparxPush used by the Notifications settings screen.

function meta(name) {
    const el = document.querySelector(`meta[name="${name}"]`);
    return el ? el.getAttribute('content') : '';
}

function urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
    const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
    const raw = atob(base64);
    const out = new Uint8Array(raw.length);
    for (let i = 0; i < raw.length; i++) out[i] = raw.charCodeAt(i);
    return out;
}

async function post(url, body) {
    return fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': meta('csrf-token'),
            Accept: 'application/json',
        },
        body: JSON.stringify(body || {}),
    });
}

const SparxPush = {
    supported() {
        return 'serviceWorker' in navigator && 'PushManager' in window && 'Notification' in window;
    },

    configured() {
        return !!meta('vapid-key');
    },

    async registration() {
        return navigator.serviceWorker.register('/sw.js');
    },

    // 'unsupported' | 'unconfigured' | 'denied' | 'subscribed' | 'off'
    async status() {
        if (!this.supported()) return 'unsupported';
        if (!this.configured()) return 'unconfigured';
        if (Notification.permission === 'denied') return 'denied';
        try {
            const reg = await navigator.serviceWorker.getRegistration();
            if (!reg) return 'off';
            const sub = await reg.pushManager.getSubscription();
            return sub ? 'subscribed' : 'off';
        } catch (e) {
            return 'off';
        }
    },

    async enable() {
        if (!this.supported()) throw new Error('This browser does not support notifications.');
        if (!this.configured()) throw new Error('Server push keys are not configured yet.');

        const permission = await Notification.requestPermission();
        if (permission !== 'granted') throw new Error('Notifications were blocked. Allow them in your browser settings.');

        const reg = await this.registration();
        await navigator.serviceWorker.ready;

        let sub = await reg.pushManager.getSubscription();
        if (!sub) {
            sub = await reg.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: urlBase64ToUint8Array(meta('vapid-key')),
            });
        }

        const res = await post('/web-push/subscribe', sub.toJSON());
        if (!res.ok) throw new Error('Could not save the subscription on the server.');
        return true;
    },

    async disable() {
        try {
            const reg = await navigator.serviceWorker.getRegistration();
            if (reg) {
                const sub = await reg.pushManager.getSubscription();
                if (sub) {
                    await post('/web-push/unsubscribe', { endpoint: sub.endpoint });
                    await sub.unsubscribe();
                }
            }
        } catch (e) {
            /* ignore */
        }
        return true;
    },

    async test() {
        const res = await post('/web-push/test', {});
        return res.json();
    },
};

window.SparxPush = SparxPush;

// Alpine component powering the "Browser notifications" card on the
// Notifications settings screen.
document.addEventListener('alpine:init', () => {
    window.Alpine.data('sparxBrowserPush', () => ({
        state: 'loading', // loading | unsupported | unconfigured | denied | subscribed | off
        busy: false,
        message: '',

        async init() {
            this.state = await SparxPush.status();
        },
        get isOn() {
            return this.state === 'subscribed';
        },
        async toggle() {
            if (this.busy) return;
            this.busy = true;
            this.message = '';
            try {
                if (this.isOn) {
                    await SparxPush.disable();
                    this.message = 'Browser notifications turned off.';
                } else {
                    await SparxPush.enable();
                    this.message = 'Done! You\'ll get reminders in this browser.';
                }
            } catch (e) {
                this.message = e.message || 'Something went wrong.';
            }
            this.state = await SparxPush.status();
            this.busy = false;
        },
        async sendTest() {
            if (this.busy) return;
            this.busy = true;
            this.message = 'Sending…';
            try {
                const r = await SparxPush.test();
                this.message = r.ok ? 'Test sent — check for the popup!' : 'No active subscription to send to.';
            } catch (e) {
                this.message = 'Could not send the test.';
            }
            this.busy = false;
        },
    }));
});

export default SparxPush;
