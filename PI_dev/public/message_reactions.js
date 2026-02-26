/**
 * Message Reactions Manager
 * Gère les réactions sur les messages (like, love, wow, heart)
 */

class MessageReactions {
    constructor() {
        this.reactions = {};
        this.reactionEmojis = {
            'like': '👍',
            'love': '❤️',
            'wow': '😮',
            'heart': '💖'
        };
        this.init();
    }

    init() {
        console.log('✅ Message Reactions initialisé');
        this.attachEventListeners();
    }

    attachEventListeners() {
        // Écouter les clics sur les boutons de réaction
        document.addEventListener('click', (e) => {
            const reactionBtn = e.target.closest('.reaction-btn');
            if (reactionBtn) {
                e.preventDefault();
                this.handleReaction(reactionBtn);
            }

            // Afficher la liste des utilisateurs qui ont réagi
            const reactionInfo = e.target.closest('.reaction-info-btn');
            if (reactionInfo) {
                e.preventDefault();
                this.showReactionUsers(reactionInfo);
            }
        });
    }

    async handleReaction(button) {
        const messageId = button.dataset.messageId;
        const reactionType = button.dataset.reactionType;

        if (!messageId || !reactionType) {
            console.error('Message ID ou type de réaction manquant');
            return;
        }

        // Animation de clic
        button.style.transform = 'scale(0.9)';
        setTimeout(() => {
            button.style.transform = '';
        }, 150);

        try {
            const response = await fetch(`/message/${messageId}/react`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ type: reactionType })
            });

            const data = await response.json();

            if (data.success) {
                this.updateReactionUI(messageId, reactionType, data.count, data.hasReacted);
            } else {
                console.error('Erreur:', data.error);
                alert(data.error || 'Une erreur est survenue');
            }
        } catch (error) {
            console.error('Erreur lors de la réaction:', error);
            alert('Une erreur est survenue. Veuillez réessayer.');
        }
    }

    updateReactionUI(messageId, type, count, hasReacted) {
        const button = document.querySelector(
            `[data-message-id="${messageId}"][data-reaction-type="${type}"]`
        );

        if (!button) {
            console.error('Bouton de réaction introuvable');
            return;
        }

        // Mettre à jour le compteur
        const countSpan = button.querySelector('.reaction-count');
        if (countSpan) {
            countSpan.textContent = count;
        }

        // Mettre à jour la classe active
        if (hasReacted) {
            button.classList.add('active');
        } else {
            button.classList.remove('active');
        }

        // Masquer le bouton si count = 0
        if (count === 0) {
            button.style.display = 'none';
        } else {
            button.style.display = 'inline-flex';
        }

        // Animation de succès
        button.classList.add('reaction-success');
        setTimeout(() => {
            button.classList.remove('reaction-success');
        }, 300);
    }

    async showReactionUsers(button) {
        const messageId = button.dataset.messageId;
        const reactionType = button.dataset.reactionType;

        try {
            const response = await fetch(`/message/${messageId}/reaction-users/${reactionType}`);
            const data = await response.json();

            if (data.users && data.users.length > 0) {
                const userNames = data.users.map(u => u.fullName).join(', ');
                const emoji = this.reactionEmojis[reactionType] || '👍';
                alert(`${emoji} ${data.count} personne(s):\n${userNames}`);
            }
        } catch (error) {
            console.error('Erreur lors de la récupération des utilisateurs:', error);
        }
    }

    // Méthode pour ajouter des boutons de réaction à un message
    addReactionButtons(messageElement, messageId) {
        const reactionsContainer = messageElement.querySelector('.message-reactions');
        if (!reactionsContainer) return;

        const reactionTypes = ['like', 'love', 'wow', 'heart'];
        
        reactionTypes.forEach(type => {
            const button = document.createElement('button');
            button.className = 'reaction-btn';
            button.dataset.messageId = messageId;
            button.dataset.reactionType = type;
            button.title = `Réagir avec ${this.reactionEmojis[type]}`;
            
            button.innerHTML = `
                <span class="reaction-emoji">${this.reactionEmojis[type]}</span>
                <span class="reaction-count">0</span>
            `;
            
            reactionsContainer.appendChild(button);
        });
    }

    // Charger les réactions existantes pour un message
    async loadReactions(messageId) {
        try {
            const response = await fetch(`/message/${messageId}/reactions`);
            const data = await response.json();

            if (data.counts) {
                Object.entries(data.counts).forEach(([type, count]) => {
                    const hasReacted = data.userReactions.includes(type);
                    this.updateReactionUI(messageId, type, count, hasReacted);
                });
            }
        } catch (error) {
            console.error('Erreur lors du chargement des réactions:', error);
        }
    }
}

// Initialisation automatique
document.addEventListener('DOMContentLoaded', () => {
    window.messageReactions = new MessageReactions();
    console.log('✅ Message Reactions prêt');
});

// Export pour utilisation dans d'autres scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = MessageReactions;
}
