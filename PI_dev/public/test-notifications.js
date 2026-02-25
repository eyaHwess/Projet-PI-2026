/**
 * Test Notifications System
 * Système de test pour les notifications sans Firebase
 * Utilise les notifications natives du navigateur
 */

class TestNotifications {
    constructor() {
        this.isSupported = false;
        this.init();
    }

    async init() {
        console.log('🧪 Initialisation Test Notifications...');

        // Vérifier le support des notifications
        if (!('Notification' in window)) {
            console.warn('❌ Les notifications ne sont pas supportées');
            this.showError('Votre navigateur ne supporte pas les notifications');
            return;
        }

        this.isSupported = true;
        console.log('✅ Notifications supportées');

        // Vérifier la permission actuelle
        await this.checkPermission();

        // Ajouter les boutons de test
        this.addTestButtons();

        console.log('✅ Test Notifications prêt');
    }

    async checkPermission() {
        const permission = Notification.permission;
        console.log('📋 Permission actuelle:', permission);

        if (permission === 'default') {
            this.showPermissionPrompt();
        } else if (permission === 'granted') {
            this.showSuccess('Notifications activées! Utilisez les boutons de test.');
        } else {
            this.showError('Notifications refusées. Activez-les dans les paramètres du navigateur.');
        }
    }

    showPermissionPrompt() {
        const prompt = document.createElement('div');
        prompt.className = 'test-notification-prompt';
        prompt.innerHTML = `
            <div class="test-prompt-content">
                <div class="test-prompt-icon">🧪</div>
                <div class="test-prompt-text">
                    <h3>Tester les Notifications</h3>
                    <p>Activez les notifications pour tester le système</p>
                </div>
                <div class="test-prompt-actions">
                    <button class="btn-test-enable">Activer</button>
                    <button class="btn-test-dismiss">Plus tard</button>
                </div>
            </div>
        `;

        document.body.appendChild(prompt);

        prompt.querySelector('.btn-test-enable').addEventListener('click', async () => {
            prompt.remove();
            await this.requestPermission();
        });

        prompt.querySelector('.btn-test-dismiss').addEventListener('click', () => {
            prompt.remove();
        });
    }

    async requestPermission() {
        try {
            const permission = await Notification.requestPermission();
            console.log('📋 Nouvelle permission:', permission);

            if (permission === 'granted') {
                this.showSuccess('Notifications activées! 🎉');
                this.addTestButtons();
            } else {
                this.showError('Notifications refusées');
            }
        } catch (error) {
            console.error('❌ Erreur demande permission:', error);
            this.showError('Erreur lors de l\'activation');
        }
    }

    addTestButtons() {
        // Vérifier si les boutons existent déjà
        if (document.getElementById('test-notifications-panel')) {
            return;
        }

        const panel = document.createElement('div');
        panel.id = 'test-notifications-panel';
        panel.className = 'test-notifications-panel';
        panel.innerHTML = `
            <div class="test-panel-header">
                <h3>🧪 Test Notifications</h3>
                <button class="test-panel-close" onclick="this.parentElement.parentElement.remove()">×</button>
            </div>
            <div class="test-panel-body">
                <button class="test-btn" onclick="testNotifications.testNewMessage()">
                    💬 Nouveau Message
                </button>
                <button class="test-btn" onclick="testNotifications.testMention()">
                    📢 Mention @user
                </button>
                <button class="test-btn" onclick="testNotifications.testNewMember()">
                    👤 Nouveau Membre
                </button>
                <button class="test-btn" onclick="testNotifications.testMultiple()">
                    🔔 Plusieurs Notifications
                </button>
                <button class="test-btn test-btn-danger" onclick="testNotifications.clearAll()">
                    🗑️ Tout Effacer
                </button>
            </div>
            <div class="test-panel-footer">
                <small>Permission: <span id="permission-status">${Notification.permission}</span></small>
            </div>
        `;

        document.body.appendChild(panel);
    }

    testNewMessage() {
        console.log('🧪 Test: Nouveau Message');

        const notification = new Notification('Nouveau message de Marie', {
            body: 'Super idée pour le projet! 🎉',
            icon: '/images/logo.png',
            badge: '/images/badge.png',
            tag: 'test-message',
            requireInteraction: false,
            data: {
                type: 'new_message',
                url: window.location.href
            }
        });

        notification.onclick = () => {
            console.log('Clic sur notification');
            window.focus();
            notification.close();
        };

        this.playSound();
        this.showSuccess('Notification "Nouveau Message" envoyée!');
    }

    testMention() {
        console.log('🧪 Test: Mention');

        const notification = new Notification('Marie vous a mentionné', {
            body: '@islem qu\'en penses-tu?',
            icon: '/images/logo.png',
            badge: '/images/badge.png',
            tag: 'test-mention',
            requireInteraction: true,
            data: {
                type: 'mention',
                url: window.location.href
            }
        });

        notification.onclick = () => {
            console.log('Clic sur notification mention');
            window.focus();
            this.showMentionAlert();
            notification.close();
        };

        this.playSound();
        this.showSuccess('Notification "Mention" envoyée!');
    }

    testNewMember() {
        console.log('🧪 Test: Nouveau Membre');

        const notification = new Notification('Nouveau membre dans "Mon Goal"', {
            body: 'Ahmed a rejoint le goal',
            icon: '/images/logo.png',
            badge: '/images/badge.png',
            tag: 'test-member',
            data: {
                type: 'new_member',
                url: window.location.href
            }
        });

        notification.onclick = () => {
            console.log('Clic sur notification membre');
            window.focus();
            notification.close();
        };

        this.playSound();
        this.showSuccess('Notification "Nouveau Membre" envoyée!');
    }

    testMultiple() {
        console.log('🧪 Test: Plusieurs Notifications');

        setTimeout(() => this.testNewMessage(), 0);
        setTimeout(() => this.testMention(), 2000);
        setTimeout(() => this.testNewMember(), 4000);

        this.showSuccess('3 notifications seront envoyées (0s, 2s, 4s)');
    }

    showMentionAlert() {
        const alert = document.createElement('div');
        alert.className = 'test-mention-alert';
        alert.innerHTML = `
            <div class="test-mention-content">
                <i class="fas fa-at"></i>
                <span>Marie vous a mentionné dans le chatroom</span>
                <button class="test-mention-close">×</button>
            </div>
        `;

        document.body.appendChild(alert);

        alert.querySelector('.test-mention-close').addEventListener('click', () => {
            alert.remove();
        });

        setTimeout(() => {
            alert.remove();
        }, 5000);
    }

    clearAll() {
        console.log('🧪 Effacer toutes les notifications');
        
        // Note: Il n'y a pas d'API standard pour fermer toutes les notifications
        // On peut seulement conseiller à l'utilisateur
        this.showSuccess('Fermez les notifications manuellement ou attendez qu\'elles disparaissent');
    }

    playSound() {
        try {
            // Créer un son simple avec Web Audio API
            const audioContext = new (window.AudioContext || window.webkitAudioContext)();
            const oscillator = audioContext.createOscillator();
            const gainNode = audioContext.createGain();

            oscillator.connect(gainNode);
            gainNode.connect(audioContext.destination);

            oscillator.frequency.value = 800;
            oscillator.type = 'sine';

            gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
            gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.5);

            oscillator.start(audioContext.currentTime);
            oscillator.stop(audioContext.currentTime + 0.5);
        } catch (error) {
            console.log('Son désactivé:', error);
        }
    }

    showSuccess(message) {
        this.showToast(message, 'success');
    }

    showError(message) {
        this.showToast(message, 'error');
    }

    showToast(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = `test-toast test-toast-${type}`;
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
}

// Styles CSS
const styles = document.createElement('style');
styles.textContent = `
/* Test Notifications Panel */
.test-notifications-panel {
    position: fixed;
    bottom: 20px;
    right: 20px;
    background: white;
    border-radius: 16px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
    z-index: 10000;
    min-width: 300px;
    animation: slideInUp 0.3s ease-out;
}

.test-panel-header {
    padding: 16px 20px;
    border-bottom: 1px solid #e8ecf1;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.test-panel-header h3 {
    margin: 0;
    font-size: 16px;
    color: #1f2937;
}

.test-panel-close {
    background: none;
    border: none;
    font-size: 24px;
    color: #9ca3af;
    cursor: pointer;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 6px;
    transition: all 0.2s;
}

.test-panel-close:hover {
    background: #f3f4f6;
    color: #6b7280;
}

.test-panel-body {
    padding: 16px;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.test-btn {
    padding: 12px 16px;
    border-radius: 10px;
    border: none;
    background: linear-gradient(135deg, #8b9dc3 0%, #a8b5d1 100%);
    color: white;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    text-align: left;
    font-size: 14px;
}

.test-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(139, 157, 195, 0.3);
}

.test-btn:active {
    transform: translateY(0);
}

.test-btn-danger {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
}

.test-btn-danger:hover {
    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
}

.test-panel-footer {
    padding: 12px 20px;
    border-top: 1px solid #e8ecf1;
    background: #f9fafb;
    border-radius: 0 0 16px 16px;
}

.test-panel-footer small {
    color: #6b7280;
    font-size: 12px;
}

#permission-status {
    font-weight: 600;
    color: #8b9dc3;
}

/* Test Notification Prompt */
.test-notification-prompt {
    position: fixed;
    bottom: 20px;
    right: 20px;
    z-index: 10000;
    animation: slideInUp 0.3s ease-out;
}

.test-prompt-content {
    background: white;
    padding: 20px;
    border-radius: 16px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
    max-width: 400px;
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.test-prompt-icon {
    font-size: 48px;
    text-align: center;
}

.test-prompt-text h3 {
    margin: 0 0 8px 0;
    font-size: 18px;
    color: #1f2937;
}

.test-prompt-text p {
    margin: 0;
    font-size: 14px;
    color: #6b7280;
}

.test-prompt-actions {
    display: flex;
    gap: 12px;
}

.btn-test-enable,
.btn-test-dismiss {
    flex: 1;
    padding: 10px 20px;
    border-radius: 10px;
    border: none;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-test-enable {
    background: linear-gradient(135deg, #8b9dc3 0%, #a8b5d1 100%);
    color: white;
}

.btn-test-enable:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(139, 157, 195, 0.3);
}

.btn-test-dismiss {
    background: #f3f4f6;
    color: #6b7280;
}

.btn-test-dismiss:hover {
    background: #e5e7eb;
}

/* Test Mention Alert */
.test-mention-alert {
    position: fixed;
    top: 80px;
    right: 20px;
    z-index: 10000;
    animation: slideInRight 0.3s ease-out;
}

.test-mention-content {
    background: linear-gradient(135deg, #8b9dc3 0%, #a8b5d1 100%);
    color: white;
    padding: 16px 20px;
    border-radius: 12px;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
    display: flex;
    align-items: center;
    gap: 12px;
    cursor: pointer;
}

.test-mention-content i {
    font-size: 24px;
}

.test-mention-close {
    background: rgba(255, 255, 255, 0.2);
    border: none;
    color: white;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-left: auto;
}

/* Test Toast */
.test-toast {
    position: fixed;
    bottom: 20px;
    left: 50%;
    transform: translateX(-50%) translateY(100px);
    background: white;
    padding: 16px 24px;
    border-radius: 12px;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
    opacity: 0;
    transition: all 0.3s;
    z-index: 10000;
}

.test-toast.show {
    opacity: 1;
    transform: translateX(-50%) translateY(0);
}

.test-toast-success {
    border-left: 4px solid #10b981;
}

.test-toast-error {
    border-left: 4px solid #ef4444;
}

@keyframes slideInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes slideInRight {
    from {
        opacity: 0;
        transform: translateX(100px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}
`;
document.head.appendChild(styles);

// Initialisation automatique
let testNotifications;

document.addEventListener('DOMContentLoaded', () => {
    testNotifications = new TestNotifications();
    window.testNotifications = testNotifications;
    console.log('✅ Test Notifications prêt');
});

// Export
if (typeof module !== 'undefined' && module.exports) {
    module.exports = TestNotifications;
}
