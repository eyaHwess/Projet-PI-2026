/**
 * Système de Notifications Live avec Mercure
 * Gère les notifications en temps réel via WebSocket (Mercure) ou Polling (fallback)
 */

class NotificationManager {
    constructor() {
        this.userId = null;
        this.mercureSource = null;
        this.pollingInterval = null;
        this.lastNotificationId = 0;
        this.unreadCount = 0;
        
        this.init();
    }

    /**
     * Initialiser le gestionnaire de notifications
     */
    init() {
        // Récupérer l'ID utilisateur depuis le DOM
        const userIdElement = document.querySelector('[data-user-id]');
        if (userIdElement) {
            this.userId = userIdElement.dataset.userId;
        }

        if (!this.userId) {
            console.log('No user ID found, notifications disabled');
            return;
        }

        // Charger le compteur initial
        this.loadUnreadCount();

        // Essayer de se connecter à Mercure
        this.connectMercure();

        // Démarrer le polling comme fallback
        this.startPolling();

        // Écouter les clics sur les notifications
        this.setupEventListeners();
    }

    /**
     * Se connecter à Mercure pour les notifications en temps réel
     */
    connectMercure() {
        try {
            const mercureUrl = window.MERCURE_PUBLIC_URL || 'http://localhost:3000/.well-known/mercure';
            const topic = `notification/user/${this.userId}`;
            
            this.mercureSource = new EventSource(`${mercureUrl}?topic=${encodeURIComponent(topic)}`);

            this.mercureSource.onmessage = (event) => {
                console.log('Mercure notification received');
                const data = JSON.parse(event.data);
                this.handleNewNotification(data);
            };

            this.mercureSource.onerror = (error) => {
                console.log('Mercure connection error, using polling fallback');
                this.mercureSource.close();
                this.mercureSource = null;
            };

            console.log('Mercure notifications connected');
        } catch (error) {
            console.log('Mercure not available, using polling:', error.message);
        }
    }

    /**
     * Démarrer le polling comme fallback
     */
    startPolling() {
        // Poll toutes les 10 secondes
        this.pollingInterval = setInterval(() => {
            if (!this.mercureSource) {
                // Seulement si Mercure n'est pas actif
                this.fetchNotifications();
            }
        }, 10000);
    }

    /**
     * Récupérer les notifications via AJAX
     */
    async fetchNotifications() {
        try {
            const response = await fetch('/notification/fetch', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const data = await response.json();

            if (data.notifications && data.notifications.length > 0) {
                // Vérifier s'il y a de nouvelles notifications
                const newNotifications = data.notifications.filter(n => n.id > this.lastNotificationId);
                
                if (newNotifications.length > 0) {
                    newNotifications.forEach(notification => {
                        this.handleNewNotification(notification);
                    });
                }
            }

            // Mettre à jour le compteur
            this.updateUnreadCount(data.unreadCount);
        } catch (error) {
            console.error('Error fetching notifications:', error);
        }
    }

    /**
     * Gérer une nouvelle notification
     */
    handleNewNotification(notification) {
        console.log('New notification:', notification);

        // Mettre à jour le dernier ID
        if (notification.id > this.lastNotificationId) {
            this.lastNotificationId = notification.id;
        }

        // Afficher une notification navigateur si autorisé
        this.showBrowserNotification(notification);

        // Jouer un son (optionnel)
        this.playNotificationSound();

        // Ajouter la notification au dropdown
        this.addNotificationToDropdown(notification);

        // Incrémenter le compteur
        this.unreadCount++;
        this.updateUnreadCount(this.unreadCount);
    }

    /**
     * Afficher une notification navigateur
     */
    showBrowserNotification(notification) {
        if ('Notification' in window && Notification.permission === 'granted') {
            const notif = new Notification('Nouvelle notification', {
                body: notification.message,
                icon: '/images/logo.svg',
                badge: '/images/logo.svg',
                tag: `notification-${notification.id}`
            });

            notif.onclick = () => {
                window.focus();
                this.markAsRead(notification.id);
                notif.close();
            };
        }
    }

    /**
     * Jouer un son de notification
     */
    playNotificationSound() {
        try {
            const audio = new Audio('/sounds/notification.mp3');
            audio.volume = 0.3;
            audio.play().catch(() => {
                // Ignorer les erreurs de lecture audio
            });
        } catch (error) {
            // Son non disponible
        }
    }

    /**
     * Ajouter une notification au dropdown
     */
    addNotificationToDropdown(notification) {
        const dropdown = document.getElementById('notificationDropdown');
        if (!dropdown) return;

        const notificationsList = dropdown.querySelector('.notifications-list') || dropdown;
        
        // Créer l'élément de notification
        const notificationElement = document.createElement('div');
        notificationElement.className = 'notification-item unread';
        notificationElement.dataset.notificationId = notification.id;
        
        if (notification.html) {
            notificationElement.innerHTML = notification.html;
        } else {
            notificationElement.innerHTML = `
                <div class="notification-icon">
                    <i class="fas fa-bell"></i>
                </div>
                <div class="notification-content">
                    <div class="notification-message">${notification.message}</div>
                    <div class="notification-time">À l'instant</div>
                </div>
                <div class="notification-badge"></div>
            `;
        }

        // Ajouter en haut de la liste
        notificationsList.insertBefore(notificationElement, notificationsList.firstChild);

        // Supprimer le message "Aucune notification" si présent
        const emptyState = dropdown.querySelector('.empty-state');
        if (emptyState) {
            emptyState.remove();
        }

        // Limiter à 10 notifications dans le dropdown
        const items = notificationsList.querySelectorAll('.notification-item');
        if (items.length > 10) {
            items[items.length - 1].remove();
        }
    }

    /**
     * Charger le compteur de notifications non lues
     */
    async loadUnreadCount() {
        try {
            const response = await fetch('/notification/fetch', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const data = await response.json();
            this.unreadCount = data.unreadCount || 0;
            this.updateUnreadCount(this.unreadCount);

            // Mettre à jour le dernier ID
            if (data.notifications && data.notifications.length > 0) {
                this.lastNotificationId = Math.max(...data.notifications.map(n => n.id));
            }
        } catch (error) {
            console.error('Error loading unread count:', error);
        }
    }

    /**
     * Mettre à jour le compteur de notifications non lues
     */
    updateUnreadCount(count) {
        this.unreadCount = count;
        const badge = document.getElementById('notificationCount');
        
        if (badge) {
            if (count > 0) {
                badge.textContent = count > 99 ? '99+' : count;
                badge.classList.remove('hidden');
            } else {
                badge.classList.add('hidden');
            }
        }
    }

    /**
     * Marquer une notification comme lue
     */
    async markAsRead(notificationId) {
        try {
            const response = await fetch(`/notification/${notificationId}/mark-read`, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const data = await response.json();

            if (data.success) {
                // Mettre à jour l'UI
                const notificationElement = document.querySelector(`[data-notification-id="${notificationId}"]`);
                if (notificationElement) {
                    notificationElement.classList.remove('unread');
                    const badge = notificationElement.querySelector('.notification-badge');
                    if (badge) {
                        badge.remove();
                    }
                }

                // Mettre à jour le compteur
                this.updateUnreadCount(data.unreadCount);
            }
        } catch (error) {
            console.error('Error marking notification as read:', error);
        }
    }

    /**
     * Configurer les écouteurs d'événements
     */
    setupEventListeners() {
        // Clic sur une notification
        document.addEventListener('click', (e) => {
            const notificationItem = e.target.closest('.notification-item');
            if (notificationItem && notificationItem.classList.contains('unread')) {
                const notificationId = notificationItem.dataset.notificationId;
                if (notificationId) {
                    this.markAsRead(notificationId);
                }
            }
        });

        // Demander la permission pour les notifications navigateur
        if ('Notification' in window && Notification.permission === 'default') {
            Notification.requestPermission();
        }
    }

    /**
     * Nettoyer les ressources
     */
    destroy() {
        if (this.mercureSource) {
            this.mercureSource.close();
        }
        if (this.pollingInterval) {
            clearInterval(this.pollingInterval);
        }
    }
}

// Initialiser le gestionnaire de notifications au chargement de la page
let notificationManager = null;

document.addEventListener('DOMContentLoaded', () => {
    notificationManager = new NotificationManager();
});

// Nettoyer avant de quitter la page
window.addEventListener('beforeunload', () => {
    if (notificationManager) {
        notificationManager.destroy();
    }
});

// Fonction globale pour basculer le dropdown de notifications
window.toggleNotifications = function(event) {
    event.stopPropagation();
    const dropdown = document.getElementById('notificationDropdown');
    if (dropdown) {
        dropdown.classList.toggle('show');
    }
};

// Fermer le dropdown en cliquant à l'extérieur
document.addEventListener('click', (e) => {
    const dropdown = document.getElementById('notificationDropdown');
    const notificationBtn = document.getElementById('notificationBtn');
    
    if (dropdown && !dropdown.contains(e.target) && !notificationBtn.contains(e.target)) {
        dropdown.classList.remove('show');
    }
});

