window.initWebPush = function() {
    const metaTag = document.querySelector('meta[name="vapid-public-key"]');
    
    // Silently exit if meta tag is missing (e.g., guest pages, print views, or not authenticated)
    if (!metaTag || !metaTag.content) {
        return;
    }

    console.log('WebPush: Initializing...');
    const vapidPublicKey = metaTag.content;

    if ('serviceWorker' in navigator && 'PushManager' in window) {
        navigator.serviceWorker.register('/sw.js')
            .then(registration => {
                console.log('WebPush: Service Worker registered');
                initPush(registration, vapidPublicKey);
            })
            .catch(error => {
                console.error('WebPush: Service Worker registration failed:', error);
            });
    } else {
        console.warn('WebPush: Push messaging is not supported in this browser.');
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', window.initWebPush);
} else {
    window.initWebPush();
}

function initPush(registration, vapidPublicKey) {
    registration.pushManager.getSubscription()
        .then(subscription => {
            if (subscription) {
                console.log('WebPush: User is already subscribed.');
                return sendSubscriptionToBackend(subscription);
            }

            console.log('WebPush: User is not subscribed. Subscribing...');
            return subscribeUser(registration, vapidPublicKey);
        });
}

function subscribeUser(registration, vapidPublicKey) {
    const applicationServerKey = urlBase64ToUint8Array(vapidPublicKey);
    
    return registration.pushManager.subscribe({
        userVisibleOnly: true,
        applicationServerKey: applicationServerKey
    })
    .then(subscription => {
        console.log('WebPush: User subscribed successfully.');
        return sendSubscriptionToBackend(subscription);
    })
    .catch(err => {
        if (Notification.permission === 'denied') {
            console.warn('WebPush: Permission for notifications was denied');
        } else {
            console.error('WebPush: Failed to subscribe the user: ', err);
        }
    });
}

function sendSubscriptionToBackend(subscription) {
    console.log('WebPush: Syncing subscription with backend...');
    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    
    if (!csrfToken) {
        console.error('WebPush: CSRF token not found.');
        return;
    }

    return fetch('/push-subscription', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken.content
        },
        body: JSON.stringify(subscription)
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Bad status code from server.');
        }
        console.log('WebPush: Subscription synced with backend.');
        return response.json();
    });
}

function urlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - base64String.length % 4) % 4);
    const base64 = (base64String + padding)
        .replace(/\-/g, '+')
        .replace(/_/g, '/');

    const rawData = window.atob(base64);
    const outputArray = new Uint8Array(rawData.length);

    for (let i = 0; i < rawData.length; ++i) {
        outputArray[i] = rawData.charCodeAt(i);
    }
    return outputArray;
}
