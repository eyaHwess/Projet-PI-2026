/**
 * Gestionnaire de Présence Utilisateur
 * Gère le statut en ligne, les indicateurs de frappe, et les accusés de lecture
 */

class PresenceManager {
    constructor(chatroomId, userId) {
        this.chatroomId = chatroomId;
        this.userId = userId;
        this.heartbeatInterval = null;
        this.typingTimeout = null;
        this.isTyping = false;
        this.typingCheckInterval = null;
        this.onlineUsersCheckInterval = null;
        
        this.init();
    }

    /**
     * Initialiser le gestionnaire
     */
    init() {
        console.log('🟢 PresenceManager initialized for chatroom:', this.chatroomId);
        
        // Démarrer le heartbeat (toutes les 30 secondes)
        this.startHeartbeat();
        
        // Écouter les événements de frappe
        this.setupTypingListeners();
        
        // Vérifier les utilisateurs qui tapent (toutes les 2 secondes)
        this.startTypingCheck();
        
        // Vérifier les utilisateurs en ligne (toutes les 30 secondes)
        this.startOnlineUsersCheck();
        
        // Mettre à jour immédiatement
        this.updateOnlineUsers();
    }

    /**
     * Démarrer le heartbeat pour maintenir le statut en ligne
     */
    startHeartbeat() {
        // Envoyer immédiatement
        this.sendHeartbeat();
        
        // Puis toutes les 30 secondes
        this.heartbeatInterval = setInterval(() => {
            this.sendHeartbeat();
        }, 30000);
    }

    /**
     * Envoyer un heartbeat au serveur
     */
    async sendHeartbeat() {
        try {
            await fetch('/presence/heartbeat', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json'
                }
            });
        } catch (error) {
            console.error('❌ Erreur heartbeat:', error);
        }
    }

    /**
     * Configurer les écouteurs de frappe
     */
    setupTypingListeners() {
        const messageInput = document.getElementById('messageInput');
        if (!messageInput) return;

        messageInput.addEventListener('input', () => {
            this.handleTyping();
        });

        messageInput.addEventListener('blur', () => {
            this.stopTyping();
        });
    }

    /**
     * Gérer l'événement de frappe
     */
    handleTyping() {
        if (!this.isTyping) {
            this.isTyping = true;
            this.sendTypingStatus(true);
        }

        // Réinitialiser le timeout
        clearTimeout(this.typingTimeout);
        
        // Arrêter de taper après 3 secondes d'inactivité
        this.typingTimeout = setTimeout(() => {
            this.stopTyping();
        }, 3000);
    }

    /**
     * Arrêter de taper
     */
    stopTyping() {
        if (this.isTyping) {
            this.isTyping = false;
            this.sendTypingStatus(false);
        }
        clearTimeout(this.typingTimeout);
    }

    /**
     * Envoyer le statut de frappe au serveur
     */
    async sendTypingStatus(isTyping) {
        try {
            await fetch(`/presence/typing/${this.chatroomId}`, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `isTyping=${isTyping}`
            });
        } catch (error) {
            console.error('❌ Erreur typing status:', error);
        }
    }

    /**
     * Démarrer la vérification des utilisateurs qui tapent
     */
    startTypingCheck() {
        this.typingCheckInterval = setInterval(() => {
            this.checkTypingUsers();
        }, 2000);
    }

    /**
     * Vérifier les utilisateurs qui tapent
     */
    async checkTypingUsers() {
        try {
            const response = await fetch(`/presence/typing/${this.chatroomId}/users`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const data = await response.json();
            this.updateTypingIndicator(data.typingUsers);
        } catch (error) {
            console.error('❌ Erreur check typing:', error);
        }
    }

    /**
     * Mettre à jour l'indicateur de frappe
     */
    updateTypingIndicator(typingUsers) {
        const indicator = document.getElementById('typingIndicator');
        if (!indicator) return;

        if (typingUsers.length === 0) {
            indicator.style.display = 'none';
            return;
        }

        indicator.style.display = 'flex';
        
        let text = '';
        if (typingUsers.length === 1) {
            text = `${typingUsers[0].firstName} est en train d'écrire...`;
        } else if (typingUsers.length === 2) {
            text = `${typingUsers[0].firstName} et ${typingUsers[1].firstName} sont en train d'écrire...`;
        } else {
            text = `${typingUsers.length} personnes sont en train d'écrire...`;
        }

        const textElement = indicator.querySelector('.typing-text');
        if (textElement) {
            textElement.textContent = text;
        }
    }

    /**
     * Démarrer la vérification des utilisateurs en ligne
     */
    startOnlineUsersCheck() {
        this.onlineUsersCheckInterval = setInterval(() => {
            this.updateOnlineUsers();
        }, 30000);
    }

    /**
     * Mettre à jour les utilisateurs en ligne
     */
    async updateOnlineUsers() {
        try {
            const response = await fetch(`/presence/online/${this.chatroomId}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            const data = await response.json();
            this.updateOnlineUsersList(data);
            this.updateOnlineCount(data.counts);
        } catch (error) {
            console.error('❌ Erreur update online users:', error);
        }
    }

    /**
     * Mettre à jour la liste des utilisateurs en ligne
     */
    updateOnlineUsersList(data) {
        const allUsers = [...data.online, ...data.away, ...data.offline];
        
        allUsers.forEach(user => {
            // Mettre à jour l'avatar dans la sidebar
            const avatar = document.querySelector(`[data-user-id="${user.id}"] .participant-avatar`);
            if (avatar) {
                avatar.classList.remove('online', 'away', 'offline');
                avatar.classList.add(user.status);
            }

            // Mettre à jour le statut dans la liste
            const statusElement = document.querySelector(`[data-user-id="${user.id}"] .participant-status`);
            if (statusElement) {
                statusElement.textContent = user.lastSeen;
                statusElement.className = `participant-status ${user.status}`;
            }
        });
    }

    /**
     * Mettre à jour le compteur d'utilisateurs en ligne
     */
    updateOnlineCount(counts) {
        const onlineCountElement = document.getElementById('onlineCount');
        if (onlineCountElement) {
            onlineCountElement.textContent = counts.online;
        }

        const totalCountElement = document.getElementById('totalParticipants');
        if (totalCountElement) {
            totalCountElement.textContent = counts.total;
        }

        // Mettre à jour le sous-titre du header
        const subtitle = document.querySelector('.chat-header-subtitle');
        if (subtitle) {
            subtitle.textContent = `${counts.online} en ligne sur ${counts.total} membres`;
        }
    }

    /**
     * Marquer un message comme lu
     */
    async markMessageAsRead(messageId) {
        try {
            await fetch(`/message/${messageId}/mark-read`, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json'
                }
            });
        } catch (error) {
            console.error('❌ Erreur mark as read:', error);
        }
    }

    /**
     * Marquer tous les messages visibles comme lus
     */
    markVisibleMessagesAsRead() {
        const messages = document.querySelectorAll('.message-received[data-message-id]');
        messages.forEach(message => {
            const messageId = message.dataset.messageId;
            if (messageId && this.isElementInViewport(message)) {
                this.markMessageAsRead(messageId);
            }
        });
    }

    /**
     * Vérifier si un élément est visible dans le viewport
     */
    isElementInViewport(el) {
        const rect = el.getBoundingClientRect();
        return (
            rect.top >= 0 &&
            rect.left >= 0 &&
            rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
            rect.right <= (window.innerWidth || document.documentElement.clientWidth)
        );
    }

    /**
     * Nettoyer les ressources
     */
    destroy() {
        if (this.heartbeatInterval) {
            clearInterval(this.heartbeatInterval);
        }
        if (this.typingCheckInterval) {
            clearInterval(this.typingCheckInterval);
        }
        if (this.onlineUsersCheckInterval) {
            clearInterval(this.onlineUsersCheckInterval);
        }
        if (this.typingTimeout) {
            clearTimeout(this.typingTimeout);
        }
        
        // Envoyer un dernier signal pour arrêter de taper
        this.stopTyping();
    }
}

// Initialiser le gestionnaire de présence au chargement de la page
let presenceManager = null;

document.addEventListener('DOMContentLoaded', () => {
    const chatroomElement = document.querySelector('[data-chatroom-id]');
    const userElement = document.querySelector('[data-user-id]');
    
    if (chatroomElement && userElement) {
        const chatroomId = chatroomElement.dataset.chatroomId;
        const userId = userElement.dataset.userId;
        
        presenceManager = new PresenceManager(chatroomId, userId);
        
        // Marquer les messages comme lus au scroll
        const messagesContainer = document.querySelector('.chat-messages');
        if (messagesContainer) {
            messagesContainer.addEventListener('scroll', () => {
                presenceManager.markVisibleMessagesAsRead();
            });
        }
        
        // Marquer les messages comme lus au chargement
        setTimeout(() => {
            presenceManager.markVisibleMessagesAsRead();
        }, 1000);
    }
});

// Nettoyer avant de quitter la page
window.addEventListener('beforeunload', () => {
    if (presenceManager) {
        presenceManager.destroy();
    }
});
