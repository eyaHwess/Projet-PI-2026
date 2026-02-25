# Guide de Dépannage Final - Emojis et Images

## 🧪 Page de Test Créée

J'ai créé une page de test standalone pour vérifier que les fonctions JavaScript fonctionnent:

**Accéder à:** `http://localhost:8000/test-chatroom.html`

Cette page teste:
1. ✅ Sélecteur d'emojis
2. ✅ Insertion d'emojis
3. ✅ Upload d'images
4. ✅ Prévisualisation
5. ✅ Diagnostic automatique

## 🔍 Diagnostic Rapide

### Étape 1: Tester la Page Standalone

1. Ouvrir: `http://localhost:8000/test-chatroom.html`
2. Cliquer sur "Lancer le Diagnostic"
3. Vérifier que tous les tests passent ✅

**Si la page de test fonctionne:**
→ Le problème est dans le template Twig du chatroom

**Si la page de test ne fonctionne pas:**
→ Le problème est dans le navigateur ou JavaScript désactivé

### Étape 2: Vérifier le Chatroom Réel

1. Aller sur `/goals`
2. Cliquer sur un goal
3. Ouvrir la console (F12)
4. Taper:
```javascript
console.log('messageInput:', document.getElementById('messageInput'));
console.log('emojiPicker:', document.getElementById('emojiPicker'));
console.log('toggleEmojiPicker:', typeof toggleEmojiPicker);
console.log('insertEmoji:', typeof insertEmoji);
console.log('handleFileSelect:', typeof handleFileSelect);
```

**Résultats attendus:**
- Tous les éléments doivent exister (pas `null`)
- Toutes les fonctions doivent être `function`

### Étape 3: Tester Manuellement

**Test Emojis:**
```javascript
// Dans la console
toggleEmojiPicker(); // Devrait ouvrir/fermer le sélecteur
insertEmoji('😀'); // Devrait insérer l'emoji
```

**Test Images:**
```javascript
// Cliquer sur le bouton 📎
// Sélectionner une image
// Vérifier dans la console:
console.log(document.getElementById('filePreviewArea').style.display);
// Devrait afficher 'block' si prévisualisation active
```

## ✅ Solutions par Problème

### Problème 1: "toggleEmojiPicker is not defined"

**Cause:** La fonction n'est pas chargée ou mal définie

**Solution:**
1. Vérifier que le script est dans le template
2. Vérifier qu'il n'y a pas d'erreur JavaScript avant
3. Vider le cache: Ctrl+F5

**Code à vérifier:**
```javascript
function toggleEmojiPicker() {
    const picker = document.getElementById('emojiPicker');
    const emojiBtn = document.getElementById('emojiBtn');
    
    if (!picker || !emojiBtn) {
        console.error('Elements not found');
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

### Problème 2: "Cannot read property 'value' of null"

**Cause:** L'élément `messageInput` n'existe pas

**Solution:**
Vérifier que le champ de texte a l'ID:
```twig
{{ form_widget(form.content, {
    'attr': {
        'id': 'messageInput',  ← IMPORTANT
        'class': 'chat-input'
    }
}) }}
```

### Problème 3: Le sélecteur d'emojis ne s'affiche pas

**Cause:** CSS `display: none` ou élément manquant

**Solution:**
1. Vérifier que l'élément existe:
```html
<div class="emoji-picker" id="emojiPicker" style="display: none;">
```

2. Vérifier le CSS:
```css
.emoji-picker {
    position: absolute;
    /* ... autres styles ... */
}
```

3. Tester manuellement:
```javascript
document.getElementById('emojiPicker').style.display = 'block';
```

### Problème 4: Les emojis ne s'insèrent pas

**Cause:** Fonction `insertEmoji` mal définie ou input manquant

**Solution:**
```javascript
function insertEmoji(emoji) {
    const input = document.getElementById('messageInput');
    
    if (!input) {
        console.error('Input not found');
        return;
    }
    
    const cursorPos = input.selectionStart || 0;
    const textBefore = input.value.substring(0, cursorPos);
    const textAfter = input.value.substring(cursorPos);
    
    input.value = textBefore + emoji + textAfter;
    
    const newCursorPos = cursorPos + emoji.length;
    input.setSelectionRange(newCursorPos, newCursorPos);
    input.focus();
}
```

### Problème 5: Le bouton 📎 ne fait rien

**Cause:** Input file manquant ou fonction non définie

**Solution:**
1. Vérifier l'input:
```html
<input type="file" 
       id="fileAttachment" 
       onchange="handleFileSelect(this)"
       style="display: none;">
```

2. Vérifier le label:
```html
<label for="fileAttachment" class="input-btn">
    <i class="fas fa-paperclip"></i>
</label>
```

### Problème 6: Pas de prévisualisation d'image

**Cause:** Éléments de prévisualisation manquants

**Solution:**
Vérifier que ces éléments existent:
```html
<div id="filePreviewArea" style="display: none;">
    <div id="filePreviewIcon"></div>
    <div id="filePreviewName"></div>
    <div id="filePreviewSize"></div>
</div>
```

### Problème 7: L'image ne s'envoie pas

**Cause:** Formulaire sans `enctype` ou permissions

**Solution:**
1. Vérifier le formulaire:
```twig
{{ form_start(form, {'attr': {'enctype': 'multipart/form-data'}}) }}
```

2. Vérifier les permissions:
```bash
chmod 777 public/uploads/messages
```

3. Vérifier les logs:
```bash
tail -f var/log/dev.log
```

## 🔧 Commandes de Dépannage

```bash
# 1. Vider le cache Symfony
php bin/console cache:clear

# 2. Vérifier les dossiers
ls -la public/uploads/messages/

# 3. Voir les logs
tail -f var/log/dev.log

# 4. Tester les permissions
touch public/uploads/messages/test.txt
rm public/uploads/messages/test.txt

# 5. Vérifier PHP
php -v
php -m | grep -i fileinfo
```

## 📋 Checklist Complète

### HTML/Twig
- [ ] `<textarea id="messageInput">` existe
- [ ] `<div id="emojiPicker">` existe
- [ ] `<button id="emojiBtn" onclick="toggleEmojiPicker()">` existe
- [ ] `<input id="fileAttachment" onchange="handleFileSelect(this)">` existe
- [ ] `<div id="filePreviewArea">` existe
- [ ] Formulaire a `enctype="multipart/form-data"`

### JavaScript
- [ ] `function toggleEmojiPicker()` définie
- [ ] `function insertEmoji(emoji)` définie
- [ ] `function handleFileSelect(input)` définie
- [ ] `function formatFileSize(bytes)` définie
- [ ] Pas d'erreur dans la console (F12)
- [ ] Fonctions définies AVANT utilisation

### CSS
- [ ] `.emoji-picker` défini
- [ ] `.file-preview-area` défini
- [ ] `.input-btn` défini
- [ ] Animations définies

### Backend
- [ ] Dossier `public/uploads/messages/` existe
- [ ] Permissions 777 ou 755
- [ ] Contrôleur gère l'upload
- [ ] Cache vidé

## 🎯 Test Final

Après avoir vérifié tout:

1. **Test Emojis:**
   - Cliquer sur 😊
   - Sélecteur s'ouvre
   - Cliquer sur un emoji
   - Il s'insère dans le texte
   - ✅ Fonctionne

2. **Test Images:**
   - Cliquer sur 📎
   - Sélectionner une image
   - Prévisualisation s'affiche
   - Envoyer
   - Image apparaît dans le chat
   - ✅ Fonctionne

## 🆘 Si Rien ne Fonctionne

1. **Tester la page standalone:**
   `http://localhost:8000/test-chatroom.html`

2. **Copier le code qui fonctionne:**
   Si la page de test fonctionne, copier le code JavaScript dans le template

3. **Vérifier le navigateur:**
   - Tester dans Chrome (meilleur support)
   - Désactiver les extensions
   - Mode navigation privée

4. **Vérifier les erreurs:**
   - Console JavaScript (F12)
   - Logs Symfony (`var/log/dev.log`)
   - Logs PHP (`php -i | grep error_log`)

## ✅ Confirmation

Quand tout fonctionne, vous devriez voir:
- ✅ Bouton 😊 ouvre le sélecteur
- ✅ Emojis s'insèrent au curseur
- ✅ Bouton 📎 ouvre le sélecteur de fichiers
- ✅ Prévisualisation s'affiche
- ✅ Image s'envoie et apparaît dans le chat
- ✅ Aucune erreur dans la console

**Système opérationnel!** 🎉
