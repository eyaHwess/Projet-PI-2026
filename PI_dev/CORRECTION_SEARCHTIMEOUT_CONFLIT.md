# Correction de l'Erreur "searchTimeout has already been declared"

## 🐛 Problème Identifié

**Erreur**: `Uncaught SyntaxError: Identifier 'searchTimeout' has already been declared`

**Ligne**: 2:6939:9

**Cause**: La variable `searchTimeout` était déclarée deux fois dans le même scope :
1. Ligne ~4732 : Pour la recherche dans la sidebar des conversations
2. Ligne ~5842 : Pour la recherche d'utilisateurs à ajouter au chatroom

En JavaScript, on ne peut pas déclarer deux fois la même variable avec `let` dans le même scope.

## ✅ Solution Appliquée

### Renommage de la Variable

**AVANT** (Conflit - ❌):
```javascript
// Ligne 4732
let searchTimeout = null;  // Pour recherche conversations

// Ligne 5842
let searchTimeout;  // ❌ ERREUR: Déjà déclaré !
```

**APRÈS** (Pas de conflit - ✅):
```javascript
// Ligne 4732
let searchTimeout = null;  // Pour recherche conversations

// Ligne 5842
let addMemberSearchTimeout;  // ✅ Nom unique
```

### Bonus: Suppression du onclick inline

J'ai aussi corrigé le bouton "Ajouter" qui utilisait encore `onclick` inline :

**AVANT**:
```javascript
<button onclick="addUserToChatroom(${user.id}, '${user.name}', this)">
```

**APRÈS**:
```javascript
<button class="add-user-btn" data-user-id="${user.id}" data-user-name="${user.name}">
```

Avec event listener attaché dynamiquement :
```javascript
document.querySelectorAll('.add-user-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const userId = this.getAttribute('data-user-id');
        const userName = this.getAttribute('data-user-name');
        addUserToChatroom(userId, userName, this);
    });
});
```

## 📝 Changements Effectués

### Fichier: `templates/chatroom/chatroom_modern.html.twig`

1. **Ligne ~5842**: Renommage de la variable
   ```javascript
   // AVANT
   let searchTimeout;
   
   // APRÈS
   let addMemberSearchTimeout;
   ```

2. **Ligne ~5856**: Utilisation de la nouvelle variable
   ```javascript
   // AVANT
   searchTimeout = setTimeout(() => {
   
   // APRÈS
   addMemberSearchTimeout = setTimeout(() => {
   ```

3. **Ligne ~5845**: Utilisation de la nouvelle variable
   ```javascript
   // AVANT
   clearTimeout(searchTimeout);
   
   // APRÈS
   clearTimeout(addMemberSearchTimeout);
   ```

4. **Ligne ~5867**: Remplacement onclick par data-attributes
   ```javascript
   // AVANT
   <button onclick="addUserToChatroom(...)">
   
   // APRÈS
   <button data-user-id="${user.id}" data-user-name="${user.name}">
   ```

5. **Ligne ~5873**: Ajout d'event listeners dynamiques
   ```javascript
   // Attacher les event listeners aux boutons "Ajouter"
   document.querySelectorAll('.add-user-btn').forEach(btn => {
       btn.addEventListener('click', function() {
           const userId = this.getAttribute('data-user-id');
           const userName = this.getAttribute('data-user-name');
           addUserToChatroom(userId, userName, this);
       });
   });
   ```

## 🧪 Test Maintenant

1. **Videz le cache du navigateur** (Ctrl+Shift+R ou Ctrl+F5)
2. **Allez sur un chatroom**
3. **Ouvrez la console** (F12)
4. **Vérifiez qu'il n'y a plus d'erreur rouge**
5. **Cliquez sur le bouton 👤+** pour ouvrir le modal
6. **Tapez au moins 2 caractères** dans le champ de recherche
7. **Vérifiez que la recherche fonctionne**
8. **Cliquez sur "Ajouter"** à côté d'un utilisateur

## ✅ Vérifications

### Syntaxe Twig
```bash
php bin/console lint:twig templates/chatroom/chatroom_modern.html.twig
```
✅ Validé (0 erreurs)

### Cache Symfony
```bash
php bin/console cache:clear
```
✅ Vidé

## 🎯 Résultat Attendu

Maintenant :
1. ✅ Aucune erreur `searchTimeout has already been declared`
2. ✅ Le modal s'ouvre correctement
3. ✅ La recherche d'utilisateurs fonctionne
4. ✅ Les boutons "Ajouter" fonctionnent
5. ✅ Pas de conflit entre les deux fonctionnalités de recherche

## 📚 Explication Technique

### Pourquoi cette erreur ?

En JavaScript moderne (ES6+), `let` et `const` créent des variables dans un **block scope**. Si vous déclarez deux fois la même variable avec `let` dans le même scope, vous obtenez une erreur.

**Exemple du problème**:
```javascript
let x = 1;
let x = 2;  // ❌ SyntaxError: Identifier 'x' has already been declared
```

**Solution**:
```javascript
let x = 1;
let y = 2;  // ✅ OK: Noms différents
```

### Pourquoi deux searchTimeout ?

Le template a deux fonctionnalités de recherche distinctes :
1. **Recherche de conversations** dans la sidebar (ligne ~4732)
2. **Recherche d'utilisateurs** pour ajouter au chatroom (ligne ~5842)

Chacune a besoin de son propre timeout pour le debounce, donc on a maintenant :
- `searchTimeout` → Pour les conversations
- `addMemberSearchTimeout` → Pour ajouter des membres

## 🎉 Conclusion

L'erreur est corrigée ! Le modal devrait maintenant fonctionner parfaitement sans aucune erreur JavaScript.

## ⚠️ Note sur Tailwind CSS

L'avertissement Tailwind CSS (`cdn.tailwindcss.com should not be used in production`) n'est qu'un warning, pas une erreur. Il n'empêche pas le fonctionnement de l'application. Pour le corriger en production, il faudrait installer Tailwind CSS via npm/PostCSS, mais ce n'est pas urgent pour le développement.
