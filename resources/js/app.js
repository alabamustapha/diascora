function urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
    const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
    const rawData = atob(base64);
    return Uint8Array.from([...rawData].map((char) => char.charCodeAt(0)));
}

async function registerPushSubscription() {
    if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
        return;
    }

    if (!document.querySelector('[data-push-enabled]')) {
        return;
    }

    try {
        const registration = await navigator.serviceWorker.register('/sw.js');
        await navigator.serviceWorker.ready;

        const permission = await Notification.requestPermission();
        if (permission !== 'granted') {
            return;
        }

        const vapidMeta = document.querySelector('meta[name="vapid-public-key"]');
        if (!vapidMeta) {
            return;
        }

        const applicationServerKey = urlBase64ToUint8Array(vapidMeta.content);

        let subscription = await registration.pushManager.getSubscription();

        if (!subscription) {
            subscription = await registration.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey,
            });
        }

        const subscriptionJson = subscription.toJSON();

        await fetch('/push/subscribe', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '',
            },
            body: JSON.stringify({
                endpoint: subscriptionJson.endpoint,
                keys: subscriptionJson.keys,
            }),
        });
    } catch {
        // Push subscription failed silently — non-critical feature
    }
}

registerPushSubscription();
