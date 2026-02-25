// Real-time notifications using Mercure
class NotificationManager {
    constructor() {
        this.eventSource = null;
        this.notificationCount = 0;
        this.init();
    }

    init() {
        // Get current user ID from data attribute
        const userIdElement = document.querySelector('[data-user-id]');
        if (!userIdElement) {
            console.log('User not logged in, notifications disabled');
            return;
        }

        const userId = userIdElement.dataset.userId;
        this.connectToMercure(userId);
        this.setupUI();
    }

    connectToMercure(userId) {
        // Connect to Mercure hub
        const mercureUrl = new URL('http://localhost:3000/.well-known/mercure');
        mercureUrl.searchParams.append('topic', `user/${userId}/notifications`);
        mercureUrl.searchParams.append('topic', 'topic/posts');

        this.eventSource = new EventSource(mercureUrl);

        this.eventSource.onmessage = (event) => {
            const notification = JSON.parse(event.data);
            this.handleNotification(notification);
        };

        this.eventSource.onerror = (error) => {
            console.error('Mercure connection error:', error);
            // Attempt to reconnect after 5 seconds
            setTimeout(() => this.connectToMercure(userId), 5000);
        };
    }

    handleNotification(notification) {
        console.log('Received notification:', notification);

        // Update badge count
        this.notificationCount++;
        this.updateBadge();

        // Show toast notification
        this.showToast(notification);

        // Play sound (optional)
        this.playNotificationSound();

        // Add to notification dropdown
        this.addToDropdown(notification);
    }

    showToast(notification) {
        // Create toast element
        const toast = document.createElement('div');
        toast.className = 'notification-toast';
        toast.innerHTML = `
            <div class="notification-toast-content">
                <div class="notification-toast-icon">
                    ${this.getIconForType(notification.type)}
                </div>
                <div class="notification-toast-body">
                    <strong>${this.getTitleForType(notification.type)}</strong>
                    <p>${notification.message}</p>
                </div>
                <button class="notification-toast-close" onclick="this.parentElement.parentElement.remove()">
                    <i class="bi bi-x"></i>
                </button>
            </div>
        `;

        // Add to page
        let container = document.getElementById('notification-toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'notification-toast-container';
            document.body.appendChild(container);
        }

        container.appendChild(toast);

        // Auto remove after 5 seconds
        setTimeout(() => {
            toast.classList.add('fade-out');
            setTimeout(() => toast.remove(), 300);
        }, 5000);

        // Make toast clickable to go to post
        if (notification.postId) {
            toast.style.cursor = 'pointer';
            toast.onclick = () => {
                window.location.href = `/posts/${notification.postId}`;
            };
        }
    }

    updateBadge() {
        const badge = document.getElementById('notification-badge');
        if (badge) {
            badge.textContent = this.notificationCount;
            badge.style.display = this.notificationCount > 0 ? 'inline-block' : 'none';
        }
    }

    addToDropdown(notification) {
        const dropdown = document.getElementById('notification-dropdown');
        if (!dropdown) return;

        const item = document.createElement('div');
        item.className = 'notification-item unread';
        item.innerHTML = `
            <div class="notification-item-icon">
                ${this.getIconForType(notification.type)}
            </div>
            <div class="notification-item-content">
                <p>${notification.message}</p>
                <small>${this.formatTime(notification.timestamp)}</small>
            </div>
        `;

        if (notification.postId) {
            item.onclick = () => {
                window.location.href = `/posts/${notification.postId}`;
            };
        }

        dropdown.insertBefore(item, dropdown.firstChild);
    }

    getIconForType(type) {
        const icons = {
            'post_liked': '<i class="bi bi-heart-fill text-danger"></i>',
            'post_commented': '<i class="bi bi-chat-fill text-primary"></i>',
            'comment_replied': '<i class="bi bi-reply-fill text-info"></i>',
            'new_post': '<i class="bi bi-file-post-fill text-success"></i>',
        };
        return icons[type] || '<i class="bi bi-bell-fill"></i>';
    }

    getTitleForType(type) {
        const titles = {
            'post_liked': 'New Like',
            'post_commented': 'New Comment',
            'comment_replied': 'New Reply',
            'new_post': 'New Post',
        };
        return titles[type] || 'Notification';
    }

    formatTime(timestamp) {
        const date = new Date(timestamp);
        const now = new Date();
        const diff = Math.floor((now - date) / 1000); // seconds

        if (diff < 60) return 'Just now';
        if (diff < 3600) return `${Math.floor(diff / 60)}m ago`;
        if (diff < 86400) return `${Math.floor(diff / 3600)}h ago`;
        return `${Math.floor(diff / 86400)}d ago`;
    }

    playNotificationSound() {
        // Optional: play a subtle notification sound
        // const audio = new Audio('/sounds/notification.mp3');
        // audio.volume = 0.3;
        // audio.play().catch(e => console.log('Could not play sound:', e));
    }

    setupUI() {
        // Add CSS for toasts
        if (!document.getElementById('notification-styles')) {
            const style = document.createElement('style');
            style.id = 'notification-styles';
            style.textContent = `
                #notification-toast-container {
                    position: fixed;
                    top: 20px;
                    right: 20px;
                    z-index: 9999;
                    max-width: 400px;
                }
                
                .notification-toast {
                    background: white;
                    border-radius: 12px;
                    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                    margin-bottom: 10px;
                    animation: slideIn 0.3s ease-out;
                }
                
                .notification-toast-content {
                    display: flex;
                    align-items: start;
                    padding: 16px;
                    gap: 12px;
                }
                
                .notification-toast-icon {
                    font-size: 24px;
                    flex-shrink: 0;
                }
                
                .notification-toast-body {
                    flex-grow: 1;
                }
                
                .notification-toast-body strong {
                    display: block;
                    margin-bottom: 4px;
                    color: #2d3748;
                }
                
                .notification-toast-body p {
                    margin: 0;
                    font-size: 14px;
                    color: #64748b;
                }
                
                .notification-toast-close {
                    background: none;
                    border: none;
                    font-size: 20px;
                    color: #94a3b8;
                    cursor: pointer;
                    padding: 0;
                    flex-shrink: 0;
                }
                
                .notification-toast-close:hover {
                    color: #64748b;
                }
                
                .notification-toast.fade-out {
                    animation: slideOut 0.3s ease-out forwards;
                }
                
                @keyframes slideIn {
                    from {
                        transform: translateX(400px);
                        opacity: 0;
                    }
                    to {
                        transform: translateX(0);
                        opacity: 1;
                    }
                }
                
                @keyframes slideOut {
                    from {
                        transform: translateX(0);
                        opacity: 1;
                    }
                    to {
                        transform: translateX(400px);
                        opacity: 0;
                    }
                }
            `;
            document.head.appendChild(style);
        }
    }

    disconnect() {
        if (this.eventSource) {
            this.eventSource.close();
        }
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.notificationManager = new NotificationManager();
});

// Cleanup on page unload
window.addEventListener('beforeunload', () => {
    if (window.notificationManager) {
        window.notificationManager.disconnect();
    }
});
