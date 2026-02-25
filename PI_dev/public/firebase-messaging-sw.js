/**
 * Firebase Messaging Service Worker
 * Gère les notifications en arrière-plan
 */

// Import Firebase scripts
importScripts('https://www.gstatic.com/firebasejs/10.7.1/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/10.7.1/firebase-messaging-compat.js');

// Configuration Firebase
// IMPORTANT: Remplacez par vos propres valeurs
const firebaseConfig = {
    apiKey: "YOUR_API_KEY_HERE",
    authDomain: "YOUR_PROJECT_ID.firebaseapp.com",
    projectId: "YOUR_PROJECT_ID",
    storageBucket: "YOUR_PROJECT_ID.appspot.com",
    messagingSenderId: "YOUR_SENDER_ID",
    appId: "YOUR_APP_ID"
};

// Initialiser Firebase
firebase.initializeApp(firebaseConfig);

// Récupérer l'instance de messaging
const messaging = firebase.messaging();

// Gérer les messages en arrière-plan
messaging.onBackgroundMessage((payload) => {
    console.log('[Service Worker] Message reçu en arrière-plan:', payload);

    const { notification, data } = payload;

    // Options de notification
    const notificationTitle = notification?.title || 'Nouvelle notification';
    const notificationOptions = {
        body: notification?.body || '',
        icon: notification?.icon || '/images/logo.png',
        badge: notification?.badge || '/images/badge.png',
        tag: notification?.tag || data?.type || 'default',
        requireInteraction: notification?.requireInteraction || false,
        data: data || {},
        actions: getNotificationActions(data?.type),
        vibrate: [200, 100, 200],
        timestamp: Date.now()
    };

    // Afficher la notification
    return self.registration.showNotification(notificationTitle, notificationOptions);
});

// Actions rapides selon le type de notification
function getNotificationActions(type) {
    switch (type) {
        case 'new_message':
            return [
                { action: 'view', title: 'Voir', icon: '/images/icons/view.png' },
                { action: 'dismiss', title: 'Ignorer', icon: '/images/icons/dismiss.png' }
            ];
        case 'mention':
            return [
                { action: 'reply', title: 'Répondre', icon: '/images/icons/reply.png' },
                { action: 'view', title: 'Voir', icon: '/images/icons/view.png' }
            ];
        case 'new_member':
            return [
                { action: 'view', title: 'Voir le profil', icon: '/images/icons/profile.png' }
            ];
        default:
            return [];
    }
}

// Gérer les clics sur les notifications
self.addEventListener('notificationclick', (event) => {
    console.log('[Service Worker] Clic sur notification:', event);

    event.notification.close();

    const data = event.notification.data;
    const action = event.action;

    // URL par défaut
    let url = data?.url || '/';

    // Actions spécifiques
    if (action === 'reply') {
        // Ouvrir la page avec le formulaire de réponse
        url = data?.url || '/';
    } else if (action === 'view') {
        url = data?.url || '/';
    } else if (action === 'dismiss') {
        // Ne rien faire, juste fermer
        return;
    }

    // Ouvrir ou focus la fenêtre
    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true })
            .then((clientList) => {
                // Chercher une fenêtre déjà ouverte
                for (const client of clientList) {
                    if (client.url === url && 'focus' in client) {
                        return client.focus();
                    }
                }

                // Ouvrir une nouvelle fenêtre
                if (clients.openWindow) {
                    return clients.openWindow(url);
                }
            })
    );
});

// Gérer la fermeture des notifications
self.addEventListener('notificationclose', (event) => {
    console.log('[Service Worker] Notification fermée:', event);
});

// Installation du Service Worker
self.addEventListener('install', (event) => {
    console.log('[Service Worker] Installation');
    self.skipWaiting();
});

// Activation du Service Worker
self.addEventListener('activate', (event) => {
    console.log('[Service Worker] Activation');
    event.waitUntil(clients.claim());
});

// Gestion des erreurs
self.addEventListener('error', (event) => {
    console.error('[Service Worker] Erreur:', event.error);
});

// Gestion des rejets de promesses
self.addEventListener('unhandledrejection', (event) => {
    console.error('[Service Worker] Promesse rejetée:', event.reason);
});

console.log('[Service Worker] Firebase Messaging Service Worker chargé');
