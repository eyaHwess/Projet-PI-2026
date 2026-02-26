# Diagnostic et Correction - Envoi d'Images et Emojis

## 🔍 Diagnostic

### Problèmes Potentiels Identifiés

#### 1. Emojis
**Symptômes possibles:**
- Le sélecteur ne s'ouvre pas
- Les emojis ne s'insèrent pas
- Erreur JavaScript dans la console

**Causes possibles:**
- Fonction `toggleEmojiPicker()` non définie
- Fonction `insertEmoji()` non définie
- Élément `#emojiPicker` manquant
- Élément `#messageInput` manquant

#### 2. Images
**Symptômes possibles:**
- Le bouton 📎 ne fait rien
- Pas de prévisualisation
- Fichier ne s'envoie pas
- Image ne s'affiche pas dans le chat

**Causes possibles:**
- Fonction `handleFileSelect()` non définie
- Élément `#filePreviewArea` manquant
- Formulaire sans `enctype`
- Permissions du dossier

## ✅ Solutions

### Solution 1: Vérifier les Fonctions JavaScript

Ouvrir la console (F12) et tester:

```javascript
// Test 1: Vérifier que les fonctions existent
console.log('toggleEmojiPicker:', typeof toggleEmojiPicker);
console.log('insertEmoji:', typeof insertEmoji);
console.log('handleFileSelect:', typeof handleFileSelect);

// Test 2: Vérifier que les éléments existent
console.log('emojiPicker:', document.getElementById('emojiPicker'));
console.log('messageInput:', document.getElementById('messageInput'));
console.log('filePreviewArea:', document.getElementById('filePreviewArea'));
console.log('fileAttachment:', document.getElementById('fileAttachment'));

// Test 3: Tester manuellement
toggleEmojiPicker(); // Devrait ouvrir/fermer le sélecteur
```

**Résultats attendus:**
- Toutes les fonctions doivent retourner `function`
- Tous les éléments doivent retourner un objet HTML (pas `null`)

### Solution 2: Vérifier le Template

Le template doit contenir:

**A. Zone de texte avec ID:**
```twig
{{ form_widget(form.content, {
    'attr': {
        'class': 'chat-input',
        'id': 'messageInput',  ← IMPORTANT
        'placeholder': 'Tapez votre message...',
        'rows': 1
    }
}) }}
```

**B. Bouton emoji avec fonction:**
```html
<button type="button" class="input-btn" id="emojiBtn" onclick="toggleEmojiPicker()" title="Emoji">
    <i class="fas fa-smile"></i>
</button>
```

**C. Sélecteur d'emojis:**
```html
<div class="emoji-picker" id="emojiPicker" style="display: none;">
    <!-- Contenu du sélecteur -->
</div>
```

**D. Bouton fichier avec fonction:**
```html
<label for="fileAttachment" class="input-btn" id="fileAttachBtn">
    <i class="fas fa-paperclip"></i>
</label>
{{ form_widget(form.attachment, {
    'attr': {
        'id': 'fileAttachment',
        'onchange': 'handleFileSelect(this)'
    }
}) }}
```

**E. Zone de prévisualisation:**
```html
<div class="file-preview-area" id="filePreviewArea" style="display: none;">
    <div class="file-preview-icon" id="filePreviewIcon"></div>
    <div class="file-preview-name" id="filePreviewName"></div>
    <div class="file-preview-size" id="filePreviewSize"></div>
</div>
```

### Solution 3: Ordre de Chargement JavaScript

Les fonctions doivent être définies AVANT d'être utilisées.

**Ordre correct dans le template:**
```html
<script>
    // 1. Variables globales
    let currentlyPlayingAudio = null;
    
    // 2. Fonctions utilitaires
    function formatFileSize(bytes) { ... }
    
    // 3. Fonctions emoji
    function toggleEmojiPicker() { ... }
    function insertEmoji(emoji) { ... }
    
    // 4. Fonctions fichier
    function handleFileSelect(input) { ... }
    function removeFileAttachment() { ... }
    
    // 5. Event listeners (à la fin)
    document.addEventListener('DOMContentLoaded', function() {
        // Initialisation
    });
</script>
```

### Solution 4: Corriger les Erreurs Communes

**Erreur 1: "toggleEmojiPicker is not defined"**
```javascript
// Vérifier que la fonction est bien définie
function toggleEmojiPicker() {
    const picker = document.getElementById('emojiPicker');
    const emojiBtn = document.getElementById('emojiBtn');
    
    if (!picker || !emojiBtn) {
        console.error('Emoji picker elements not found');
        return;
    }
    
    if (picker.style.display === 'none' || picker.style.display === '') {
        picker.style.display = 'block';
        emojiBtn.classList.add('active');
    } else {
        picker.style.display = 'none';
        emojiBtn.classList.remove('active');
    }
}
```

**Erreur 2: "insertEmoji is not defined"**
```javascript
function insertEmoji(emoji) {
    const input = document.getElementById('messageInput');
    
    if (!input) {
        console.error('Message input not found');
        return;
    }
    
    const cursorPos = input.selectionStart;
    const textBefore = input.value.substring(0, cursorPos);
    const textAfter = input.value.substring(cursorPos);
    
    input.value = textBefore + emoji + textAfter;
    
    const newCursorPos = cursorPos + emoji.length;
    input.setSelectionRange(newCursorPos, newCursorPos);
    input.focus();
}
```

**Erreur 3: "handleFileSelect is not defined"**
```javascript
function handleFileSelect(input) {
    const file = input.files[0];
    if (!file) return;
    
    const previewArea = document.getElementById('filePreviewArea');
    const previewIcon = document.getElementById('filePreviewIcon');
    const previewName = document.getElementById('filePreviewName');
    const previewSize = document.getElementById('filePreviewSize');
    
    if (!previewArea || !previewIcon || !previewName || !previewSize) {
        console.error('Preview elements not found');
        return;
    }
    
    // Afficher prévisualisation
    if (file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = function(e) {
            previewIcon.innerHTML = `<img src="${e.target.result}" style="width: 48px; height: 48px; object-fit: cover; border-radius: 8px;">`;
        };
        reader.readAsDataURL(file);
    } else {
        previewIcon.innerHTML = `<i class="fas fa-file"></i>`;
    }
    
    previewName.textContent = file.name;
    previewSize.textContent = formatFileSize(file.size);
    previewArea.style.display = 'block';
}
```

## 🔧 Script de Correction Automatique

Créer un fichier `public/js/chatroom-fix.js`:

```javascript
// Vérification et correction automatique
document.addEventListener('DOMContentLoaded', function() {
    console.log('=== DIAGNOSTIC CHATROOM ===');
    
    // 1. Vérifier les éléments
    const elements = {
        'messageInput': document.getElementById('messageInput'),
        'emojiPicker': document.getElementById('emojiPicker'),
        'emojiBtn': document.getElementById('emojiBtn'),
        'fileAttachment': document.getElementById('fileAttachment'),
        'filePreviewArea': document.getElementById('filePreviewArea'),
        'sendBtn': document.getElementById('sendBtn')
    };
    
    console.log('Éléments trouvés:');
    for (const [name, element] of Object.entries(elements)) {
        console.log(`  ${name}:`, element ? '✅' : '❌');
    }
    
    // 2. Vérifier les fonctions
    const functions = {
        'toggleEmojiPicker': typeof window.toggleEmojiPicker,
        'insertEmoji': typeof window.insertEmoji,
        'handleFileSelect': typeof window.handleFileSelect,
        'updateSendButton': typeof window.updateSendButton
    };
    
    console.log('Fonctions trouvées:');
    for (const [name, type] of Object.entries(functions)) {
        console.log(`  ${name}:`, type === 'function' ? '✅' : '❌');
    }
    
    // 3. Corriger les problèmes
    if (!elements.messageInput) {
        console.error('❌ messageInput manquant - Ajouter id="messageInput" au champ de texte');
    }
    
    if (!elements.emojiPicker) {
        console.error('❌ emojiPicker manquant - Ajouter le sélecteur d\'emojis');
    }
    
    if (functions.toggleEmojiPicker !== 'function') {
        console.error('❌ toggleEmojiPicker manquant - Définir la fonction');
    }
    
    if (functions.handleFileSelect !== 'function') {
        console.error('❌ handleFileSelect manquant - Définir la fonction');
    }
    
    console.log('=== FIN DIAGNOSTIC ===');
});
```

Puis l'inclure dans le template:
```twig
<script src="{{ asset('js/chatroom-fix.js') }}"></script>
```

## 📋 Checklist de Vérification

### Template HTML
- [ ] Zone de texte a `id="messageInput"`
- [ ] Bouton emoji a `id="emojiBtn"` et `onclick="toggleEmojiPicker()"`
- [ ] Sélecteur emoji a `id="emojiPicker"`
- [ ] Bouton fichier a `id="fileAttachBtn"`
- [ ] Input fichier a `id="fileAttachment"` et `onchange="handleFileSelect(this)"`
- [ ] Zone prévisualisation a `id="filePreviewArea"`
- [ ] Formulaire a `enctype="multipart/form-data"`

### JavaScript
- [ ] Fonction `toggleEmojiPicker()` définie
- [ ] Fonction `insertEmoji(emoji)` définie
- [ ] Fonction `handleFileSelect(input)` définie
- [ ] Fonction `formatFileSize(bytes)` définie
- [ ] Event listener pour fermer emoji picker
- [ ] Fonctions définies AVANT utilisation

### CSS
- [ ] `.emoji-picker` défini
- [ ] `.file-preview-area` défini
- [ ] `.input-btn` défini
- [ ] Animations définies

### Backend
- [ ] Dossier `public/uploads/messages/` existe
- [ ] Permissions en écriture (777 ou 755)
- [ ] Contrôleur gère l'upload
- [ ] Entité Message a les propriétés

## 🎯 Test Final

Après corrections, tester:

1. **Emojis:**
   - Cliquer sur 😊
   - Sélecteur s'ouvre
   - Cliquer sur un emoji
   - Il s'insère dans le texte

2. **Images:**
   - Cliquer sur 📎
   - Sélectionner une image
   - Prévisualisation s'affiche
   - Envoyer
   - Image apparaît dans le chat

Si tout fonctionne: ✅ Système opérationnel!
Si problème persiste: Regarder la console (F12) pour les erreurs.
