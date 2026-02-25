/**
 * Emoji Picker pour Chatroom
 * Sélecteur d'emojis moderne et léger
 */

class EmojiPicker {
    constructor(inputElement, buttonElement) {
        this.input = inputElement;
        this.button = buttonElement;
        this.picker = null;
        this.isOpen = false;
        
        this.emojis = {
            smileys: [
                '😀', '😃', '😄', '😁', '😆', '😅', '🤣', '😂',
                '🙂', '🙃', '😉', '😊', '😇', '🥰', '😍', '🤩',
                '😘', '😗', '😚', '😙', '😋', '😛', '😜', '🤪',
                '😝', '🤑', '🤗', '🤭', '🤫', '🤔', '🤐', '🤨',
                '😐', '😑', '😶', '😏', '😒', '🙄', '😬', '🤥',
                '😌', '😔', '😪', '🤤', '😴', '😷', '🤒', '🤕',
                '🤢', '🤮', '🤧', '🥵', '🥶', '😵', '🤯', '🤠',
                '🥳', '😎', '🤓', '🧐', '😕', '😟', '🙁', '☹️',
                '😮', '😯', '😲', '😳', '🥺', '😦', '😧', '😨',
                '😰', '😥', '😢', '😭', '😱', '😖', '😣', '😞'
            ],
            gestures: [
                '👍', '👎', '👌', '✌️', '🤞', '🤟', '🤘', '🤙',
                '👈', '👉', '👆', '👇', '☝️', '👋', '🤚', '🖐',
                '✋', '🖖', '👏', '🙌', '👐', '🤲', '🤝', '🙏',
                '✍️', '💪', '🦾', '🦿', '🦵', '🦶', '👂', '🦻',
                '👃', '🧠', '🦷', '🦴', '👀', '👁', '👅', '👄'
            ],
            hearts: [
                '❤️', '🧡', '💛', '💚', '💙', '💜', '🖤', '🤍',
                '🤎', '💔', '❣️', '💕', '💞', '💓', '💗', '💖',
                '💘', '💝', '💟', '♥️', '💌', '💋', '💏', '💑'
            ],
            animals: [
                '🐶', '🐱', '🐭', '🐹', '🐰', '🦊', '🐻', '🐼',
                '🐨', '🐯', '🦁', '🐮', '🐷', '🐸', '🐵', '🐔',
                '🐧', '🐦', '🐤', '🦆', '🦅', '🦉', '🦇', '🐺',
                '🐗', '🐴', '🦄', '🐝', '🐛', '🦋', '🐌', '🐞'
            ],
            food: [
                '🍎', '🍊', '🍋', '🍌', '🍉', '🍇', '🍓', '🍈',
                '🍒', '🍑', '🥭', '🍍', '🥥', '🥝', '🍅', '🍆',
                '🥑', '🥦', '🥬', '🥒', '🌶', '🌽', '🥕', '🧄',
                '🧅', '🥔', '🍠', '🥐', '🥯', '🍞', '🥖', '🥨',
                '🧀', '🥚', '🍳', '🧈', '🥞', '🧇', '🥓', '🥩',
                '🍗', '🍖', '🦴', '🌭', '🍔', '🍟', '🍕', '🥪'
            ],
            activities: [
                '⚽', '🏀', '🏈', '⚾', '🥎', '🎾', '🏐', '🏉',
                '🥏', '🎱', '🪀', '🏓', '🏸', '🏒', '🏑', '🥍',
                '🏏', '🥅', '⛳', '🪁', '🏹', '🎣', '🤿', '🥊',
                '🥋', '🎽', '🛹', '🛼', '🛷', '⛸', '🥌', '🎿'
            ],
            objects: [
                '⌚', '📱', '💻', '⌨️', '🖥', '🖨', '🖱', '🖲',
                '🕹', '🗜', '💾', '💿', '📀', '📼', '📷', '📸',
                '📹', '🎥', '📽', '🎞', '📞', '☎️', '📟', '📠',
                '📺', '📻', '🎙', '🎚', '🎛', '🧭', '⏱', '⏲'
            ],
            symbols: [
                '❤️', '💛', '💚', '💙', '💜', '🖤', '🤍', '🤎',
                '💔', '❣️', '💕', '💞', '💓', '💗', '💖', '💘',
                '💝', '💟', '☮️', '✝️', '☪️', '🕉', '☸️', '✡️',
                '🔯', '🕎', '☯️', '☦️', '🛐', '⛎', '♈', '♉'
            ],
            flags: [
                '🏁', '🚩', '🎌', '🏴', '🏳️', '🏳️‍🌈', '🏳️‍⚧️', '🏴‍☠️',
                '🇦🇨', '🇦🇩', '🇦🇪', '🇦🇫', '🇦🇬', '🇦🇮', '🇦🇱', '🇦🇲',
                '🇦🇴', '🇦🇶', '🇦🇷', '🇦🇸', '🇦🇹', '🇦🇺', '🇦🇼', '🇦🇽',
                '🇦🇿', '🇧🇦', '🇧🇧', '🇧🇩', '🇧🇪', '🇧🇫', '🇧🇬', '🇧🇭'
            ]
        };
        
        this.init();
    }

    init() {
        this.createPicker();
        this.attachEvents();
    }

    createPicker() {
        this.picker = document.createElement('div');
        this.picker.className = 'emoji-picker';
        this.picker.style.display = 'none';
        
        // Header avec onglets
        const header = document.createElement('div');
        header.className = 'emoji-picker-header';
        
        const categories = [
            { name: 'smileys', icon: '😀', label: 'Smileys' },
            { name: 'gestures', icon: '👍', label: 'Gestes' },
            { name: 'hearts', icon: '❤️', label: 'Cœurs' },
            { name: 'animals', icon: '🐶', label: 'Animaux' },
            { name: 'food', icon: '🍎', label: 'Nourriture' },
            { name: 'activities', icon: '⚽', label: 'Activités' },
            { name: 'objects', icon: '💻', label: 'Objets' },
            { name: 'symbols', icon: '❤️', label: 'Symboles' },
            { name: 'flags', icon: '🏁', label: 'Drapeaux' }
        ];
        
        categories.forEach(cat => {
            const tab = document.createElement('button');
            tab.className = 'emoji-tab';
            tab.dataset.category = cat.name;
            tab.innerHTML = cat.icon;
            tab.title = cat.label;
            tab.onclick = () => this.showCategory(cat.name);
            header.appendChild(tab);
        });
        
        this.picker.appendChild(header);
        
        // Barre de recherche
        const searchBar = document.createElement('div');
        searchBar.className = 'emoji-search';
        searchBar.innerHTML = `
            <input type="text" placeholder="Rechercher un emoji..." class="emoji-search-input">
        `;
        this.picker.appendChild(searchBar);
        
        // Conteneur des emojis
        const content = document.createElement('div');
        content.className = 'emoji-picker-content';
        this.picker.appendChild(content);
        
        // Ajouter au DOM
        this.button.parentElement.style.position = 'relative';
        this.button.parentElement.appendChild(this.picker);
        
        // Afficher la première catégorie
        this.showCategory('smileys');
        
        // Recherche
        const searchInput = this.picker.querySelector('.emoji-search-input');
        searchInput.addEventListener('input', (e) => this.search(e.target.value));
    }

    showCategory(categoryName) {
        const content = this.picker.querySelector('.emoji-picker-content');
        content.innerHTML = '';
        
        // Mettre à jour les onglets actifs
        this.picker.querySelectorAll('.emoji-tab').forEach(tab => {
            tab.classList.remove('active');
            if (tab.dataset.category === categoryName) {
                tab.classList.add('active');
            }
        });
        
        const emojis = this.emojis[categoryName] || [];
        
        emojis.forEach(emoji => {
            const btn = document.createElement('button');
            btn.className = 'emoji-item';
            btn.textContent = emoji;
            btn.onclick = () => this.insertEmoji(emoji);
            content.appendChild(btn);
        });
    }

    search(query) {
        if (!query.trim()) {
            this.showCategory('smileys');
            return;
        }
        
        const content = this.picker.querySelector('.emoji-picker-content');
        content.innerHTML = '';
        
        let found = false;
        Object.values(this.emojis).forEach(category => {
            category.forEach(emoji => {
                const btn = document.createElement('button');
                btn.className = 'emoji-item';
                btn.textContent = emoji;
                btn.onclick = () => this.insertEmoji(emoji);
                content.appendChild(btn);
                found = true;
            });
        });
        
        if (!found) {
            content.innerHTML = '<div class="emoji-no-results">Aucun emoji trouvé</div>';
        }
    }

    insertEmoji(emoji) {
        const start = this.input.selectionStart;
        const end = this.input.selectionEnd;
        const text = this.input.value;
        
        this.input.value = text.substring(0, start) + emoji + text.substring(end);
        this.input.selectionStart = this.input.selectionEnd = start + emoji.length;
        
        // Focus sur l'input
        this.input.focus();
        
        // Déclencher l'événement input pour les listeners
        this.input.dispatchEvent(new Event('input', { bubbles: true }));
    }

    attachEvents() {
        // Toggle picker
        this.button.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            this.toggle();
        });
        
        // Fermer en cliquant à l'extérieur
        document.addEventListener('click', (e) => {
            if (this.isOpen && !this.picker.contains(e.target) && e.target !== this.button) {
                this.close();
            }
        });
    }

    toggle() {
        if (this.isOpen) {
            this.close();
        } else {
            this.open();
        }
    }

    open() {
        this.picker.style.display = 'block';
        this.isOpen = true;
        this.button.classList.add('active');
    }

    close() {
        this.picker.style.display = 'none';
        this.isOpen = false;
        this.button.classList.remove('active');
    }
}

// Initialisation automatique
document.addEventListener('DOMContentLoaded', () => {
    const messageInput = document.getElementById('messageInput');
    const emojiButton = document.getElementById('emojiButton');
    
    if (messageInput && emojiButton) {
        new EmojiPicker(messageInput, emojiButton);
        console.log('✅ Emoji Picker initialisé');
    }
});
