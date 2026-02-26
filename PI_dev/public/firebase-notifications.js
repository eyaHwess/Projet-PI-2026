/**
 * Firebase Notifications Manager
 * Gère les notifications push via Firebase Cloud Messaging
 */

class FirebaseNotifications {
    constructor() {
        this.messaging = null;
        this.currentToken = null;
        this.isSupported = false;
        this.init();
    }

    async init() {
        console.log('🔔 Initialisation Firebase Notifications...');

        // Vérifier le support des notifications
        if (!('Notification' in window)) {
            console.warn('❌ Les notifications ne sont pas supportées par ce navigateur');
            return;
        }

        if (!('serviceWorker' in navigator)) {
            console.warn('❌ Les Service Workers ne sont pas supportés');
            return;
        }

        this.isSupported = true;

        try {
            // Initialiser Firebase
            if (typeof firebase === 'undefined') {
                console.error('❌ Firebase SDK non chargé');
                return;
            }

            firebase.initializeApp(firebaseConfig);
            this.messaging = firebase.messaging();

            // Enregistrer le Service Worker
            await this.registerServiceWorker();

            // Vérifier la permission actuelle
            await this.checkPermission();

            // Écouter les messages en premier plan
            this.listenForMessages();

            console.log('✅ Firebase Notifications initialisé');
        } catch (error) {
            console.error('❌ Erreur initialisation Firebase:', error);
        }
    }

    async registerServiceWorker() {
        try {
            const registration = await navigator.serviceWorker.register('/firebase-messaging-sw.js');
            console.log('✅ Service Worker enregistré:', registration);
            return registration;
        } catch (error) {
            console.error('❌ Erreur enregistrement Service Worker:', error);
            throw error;
        }
    }

    async checkPermission() {
        const permission = Notification.permission;
        console.log('📋 Permission notifications:', permission);

        if (permission === 'granted') {
            await this.getToken();
        } else if (permission === 'default') {
            this.showPermissionPrompt();
        }
    }

    showPermissionPrompt() {
        // Afficher un message explicatif avant de demander la permission
        const promptDiv = document.createElement('div');
        promptDiv.className = 'notification-permission-prompt';
        promptDiv.innerHTML = `
            <div class="notification-prompt-content">
                <div class="notification-prompt-icon">🔔</div>
                <div class="notification-prompt-text">
                    <h3>Activer les notifications</h3>
                    <p>Recevez des notifications pour les nouveaux messages et mentions</p>
                </div>
                <div class="notification-prompt-actions">
                    <button class="btn-enable-notifications">Activer</button>
                    <button class="btn-dismiss-notifications">Plus tard</button>
                </div>
            </div>
        `;

        document.body.appendChild(promptDiv);

        // Gérer les clics
        promptDiv.querySelector('.btn-enable-notifications').addEventListener('click', async () => {
            promptDiv.remove();
            await this.requestPermission();
        });

        promptDiv.querySelector('.btn-dismiss-notifications').addEventListener('click', () => {
            promptDiv.remove();
        });
    }

    async requestPermission() {
        try {
            const permission = await Notification.requestPermission();
            console.log('📋 Nouvelle permission:', permission);

            if (permission === 'granted') {
                await this.getToken();
                this.showSuccessMessage('Notifications activées! 🎉');
            } else {
                this.showErrorMessage('Notifications refusées');
            }
        } catch (error) {
            console.error('❌ Erreur demande permission:', error);
            this.showErrorMessage('Erreur lors de l\'activation des notifications');
        }
    }

    async getToken() {
        try {
            const token = await this.messaging.getToken({
                vapidKey: vapidKey
            });

            if (token) {
                console.log('✅ Token FCM obtenu:', token.substring(0, 20) + '...');
                this.currentToken = token;
                await this.sendTokenToServer(token);
            } else {
                console.warn('⚠️ Aucun token disponible');
            }
        } catch (error) {
            console.error('❌ Erreur obtention token:', error);
        }
    }

    async sendTokenToServer(token) {
        try {
            const response = await fetch('/fcm/register', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    token: token,
                    device: this.getDeviceType()
                })
            });

            const data = await response.json();

            if (data.success) {
                console.log('✅ Token enregistré sur le serveur');
            } else {
                console.error('❌ Erreur enregistrement token:', data.error);
            }
        } catch (error) {
            console.error('❌ Erreur envoi token au serveur:', error);
        }
    }

    listenForMessages() {
        this.messaging.onMessage((payload) => {
            console.log('📨 Message reçu en premier plan:', payload);

            const { notification, data } = payload;

            // Afficher la notification
            this.showNotification(notification, data);

            // Mettre à jour l'UI si nécessaire
            this.handleNotificationData(data);
        });
    }

    showNotification(notification, data) {
        const { title, body, icon } = notification;

        // Créer une notification navigateur
        if (Notification.permission === 'granted') {
            const notif = new Notification(title, {
                body: body,
                icon: icon || '/images/logo.png',
                badge: '/images/badge.png',
                tag: data?.type || 'default',
                requireInteraction: data?.type === 'mention',
                data: data
            });

            notif.onclick = () => {
                window.focus();
                if (data?.url) {
                    window.location.href = data.url;
                }
                notif.close();
            };

            // Jouer un son
            this.playNotificationSound();
        }
    }

    handleNotificationData(data) {
        if (!data) return;

        switch (data.type) {
            case 'new_message':
                this.handleNewMessage(data);
                break;
            case 'new_member':
                this.handleNewMember(data);
                break;
            case 'mention':
                this.handleMention(data);
                break;
        }
    }

    handleNewMessage(data) {
        console.log('💬 Nouveau message:', data);
        
        // Mettre à jour le badge de notifications
        this.updateNotificationBadge();

        // Si on est sur la page du chatroom, recharger les messages
        if (window.location.pathname.includes('/chatroom/' + data.chatroomId)) {
            // Déclencher un événement personnalisé
            window.dispatchEvent(new CustomEvent('newMessage', { detail: data }));
        }
    }

    handleNewMember(data) {
        console.log('👤 Nouveau membre:', data);
        this.updateNotificationBadge();
    }

    handleMention(data) {
        console.log('📢 Mention:', data);
        this.updateNotificationBadge();
        
        // Afficher une alerte visuelle plus importante pour les mentions
        this.showMentionAlert(data);
    }

    showMentionAlert(data) {
        const alert = document.createElement('div');
        alert.className = 'mention-alert';
        alert.innerHTML = `
            <div class="mention-alert-content">
                <i class="fas fa-at"></i>
                <span>${data.authorName} vous a mentionné</span>
                <button class="mention-alert-close">×</button>
            </div>
        `;

        document.body.appendChild(alert);

        alert.querySelector('.mention-alert-close').addEventListener('click', () => {
            alert.remove();
        });

        alert.addEventListener('click', () => {
            if (data.url) {
                window.location.href = data.url;
            }
        });

        // Auto-remove après 10 secondes
        setTimeout(() => {
            alert.remove();
        }, 10000);
    }

    updateNotificationBadge() {
        // Mettre à jour le badge de notifications dans la navbar
        const badge = document.querySelector('.notification-badge');
        if (badge) {
            const currentCount = parseInt(badge.textContent) || 0;
            badge.textContent = currentCount + 1;
            badge.style.display = 'inline-block';
        }
    }

    playNotificationSound() {
        try {
            const audio = new Audio('/sounds/notification.mp3');
            audio.volume = 0.5;
            audio.play().catch(e => console.log('Son désactivé:', e));
        } catch (error) {
            console.log('Erreur lecture son:', error);
        }
    }

    getDeviceType() {
        const ua = navigator.userAgent;
        if (/android/i.test(ua)) return 'android';
        if (/iPad|iPhone|iPod/.test(ua)) return 'ios';
        return 'web';
    }

    showSuccessMessage(message) {
        this.showToast(message, 'success');
    }

    showErrorMessage(message) {
        this.showToast(message, 'error');
    }

    showToast(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.textContent = message;
        document.body.appendChild(toast);

        setTimeout(() => {
            toast.classList.add('show');
        }, 100);

        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

    // Méthode publique pour demander la permission manuellement
    async enableNotifications() {
        if (!this.isSupported) {
            this.showErrorMessage('Les notifications ne sont pas supportées');
            return false;
        }

        if (Notification.permission === 'granted') {
            this.showSuccessMessage('Les notifications sont déjà activées');
            return true;
        }

        return await this.requestPermission();
    }

    // Méthode pour désactiver les notifications
    async disableNotifications() {
        if (this.currentToken) {
            try {
                await fetch('/fcm/unregister', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        token: this.currentToken
                    })
                });

                this.currentToken = null;
                this.showSuccessMessage('Notifications désactivées');
            } catch (error) {
                console.error('Erreur désactivation:', error);
            }
        }
    }
}

// Initialisation automatique
let firebaseNotifications;

document.addEventListener('DOMContentLoaded', () => {
    firebaseNotifications = new FirebaseNotifications();
    window.firebaseNotifications = firebaseNotifications;
    console.log('✅ Firebase Notifications Manager prêt');
});

// Export pour utilisation dans d'autres scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = FirebaseNotifications;
}
