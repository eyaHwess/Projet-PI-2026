# 🔍 Diagnostic du Problème de Notifications

## Problème Rapporté
"Lorsque je clique sur le bouton notification, il ne fonctionne pas"

## Corrections Appliquées

### 1. Ajout de `type="button"` sur les boutons
**Problème** : Sans `type="button"`, les boutons peuvent se comporter comme des boutons de soumission
**Solution** : Ajout explicite de `type="button"`

```html
<!-- AVANT -->
<button id="notificationBtn" class="...">

<!-- APRÈS -->
<button type="button" id="notificationBtn" class="...">
```

### 2. Utilisation de `DOMContentLoaded`
**Problème** : Le JavaScript peut s'exécuter avant que le DOM soit prêt
**Solution** : Envelopper tout le code dans `DOMContentLoaded`

```javascript
// AVANT
(function() {
    const notificationBtn = document.getElementById('notificationBtn');
    // ...
})();

// APRÈS
document.addEventListener('DOMContentLoaded', function() {
    const notificationBtn = document.getElementById('notificationBtn');
    // ...
});
```

### 3. Logs de débogage améliorés
Ajout de logs détaillés pour identifier où le problème se situe

## Tests à Effectuer

### Test 1 : Fichier HTML Standalone
1. Ouvrir dans le navigateur : `http://localhost:8000/test-notification.html`
2. Ouvrir la console (F12)
3. Cliquer sur le bouton 🔔
4. Vérifier les logs :
   ```
   🔔 Initialisation du test
   ✅ Éléments trouvés
   ✅ Event listeners ajoutés
   🖱️ Clic détecté
   📂 Ouverture
   ```

**Si ce test fonctionne** : Le problème est dans l'intégration Symfony
**Si ce test ne fonctionne pas** : Problème de navigateur ou de configuration

### Test 2 : Page Symfony avec Console
1. Se connecter à l'application
2. Ouvrir la console (F12)
3. Vérifier que vous voyez :
   ```
   🔔 Initialisation du système de notifications
   ✅ Éléments DOM trouvés
   🚀 Chargement initial du compteur
   ```

**Si vous ne voyez PAS ces logs** :
- Le JavaScript ne se charge pas
- Vérifier que `{% if app.user %}` est vrai
- Vérifier qu'il n'y a pas d'erreur JavaScript avant

**Si vous voyez ces logs** :
- Cliquer sur le bouton 🔔
- Vérifier si vous voyez : `🖱️ Clic sur le bouton notification`

### Test 3 : Inspection des Éléments
1. Ouvrir les DevTools (F12)
2. Onglet "Elements" ou "Inspecteur"
3. Chercher `id="notificationBtn"`
4. Vérifier que l'élément existe
5. Cliquer avec le bouton droit → "Scroll into view"
6. Essayer de cliquer dessus

### Test 4 : Vérification des Event Listeners
1. Dans la console, taper :
   ```javascript
   const btn = document.getElementById('notificationBtn');
   console.log('Bouton:', btn);
   console.log('Event listeners:', getEventListeners(btn));
   ```
2. Vérifier qu'il y a bien un listener de type "click"

### Test 5 : Test Manuel dans la Console
1. Dans la console, taper :
   ```javascript
   const dropdown = document.getElementById('notificationDropdown');
   dropdown.classList.add('show');
   ```
2. Le dropdown devrait s'afficher
3. Pour le fermer :
   ```javascript
   dropdown.classList.remove('show');
   ```

## Problèmes Possibles et Solutions

### Problème 1 : JavaScript ne se charge pas
**Symptômes** : Aucun log dans la console
**Causes possibles** :
- Erreur JavaScript avant le code des notifications
- Bloc `{% if app.user %}` est faux
- Cache du navigateur

**Solutions** :
1. Vider le cache du navigateur (Ctrl+Shift+Delete)
2. Vérifier qu'il n'y a pas d'erreur dans la console
3. Vérifier que vous êtes bien connecté

### Problème 2 : Éléments DOM non trouvés
**Symptômes** : Log "❌ Éléments de notification non trouvés"
**Causes possibles** :
- IDs mal orthographiés
- Éléments dans un template enfant qui écrase le parent
- JavaScript s'exécute trop tôt

**Solutions** :
1. Vérifier l'orthographe des IDs
2. Utiliser `DOMContentLoaded` (déjà fait)
3. Inspecter le HTML généré

### Problème 3 : Clic ne déclenche rien
**Symptômes** : Pas de log "🖱️ Clic sur le bouton notification"
**Causes possibles** :
- Event listener non attaché
- Autre élément capture le clic (z-index)
- Bouton désactivé

**Solutions** :
1. Vérifier avec `getEventListeners()`
2. Vérifier le z-index du bouton
3. Vérifier que le bouton n'a pas `disabled`

### Problème 4 : Dropdown ne s'affiche pas
**Symptômes** : Log de clic OK mais dropdown invisible
**Causes possibles** :
- CSS `display: none` trop fort
- Classe `.show` non appliquée
- z-index trop bas

**Solutions** :
1. Inspecter l'élément dans DevTools
2. Vérifier que la classe `show` est bien ajoutée
3. Forcer `display: block !important` dans DevTools pour tester

### Problème 5 : Erreur CORS ou 404 sur les API
**Symptômes** : Erreur réseau dans la console
**Causes possibles** :
- Routes mal configurées
- Serveur non démarré
- Problème d'authentification

**Solutions** :
1. Vérifier les routes : `php bin/console debug:router | findstr notification`
2. Tester l'API directement : `/notifications/unread-count`
3. Vérifier les logs serveur

## Commandes de Diagnostic

### Vérifier les routes
```bash
php bin/console debug:router | findstr notification
```

### Vider le cache
```bash
php bin/console cache:clear
```

### Vérifier les logs
```bash
type var\log\dev.log | Select-Object -Last 50
```

### Tester l'API directement
Ouvrir dans le navigateur :
- `http://localhost:8000/notifications/unread-count`
- `http://localhost:8000/notifications/unread`

## Checklist de Vérification

- [ ] Le serveur Symfony est démarré
- [ ] Je suis connecté avec un compte utilisateur
- [ ] La console ne montre aucune erreur JavaScript
- [ ] Je vois le log "🔔 Initialisation du système de notifications"
- [ ] Je vois le log "✅ Éléments DOM trouvés"
- [ ] Le bouton 🔔 est visible dans la navbar
- [ ] Le bouton a bien `id="notificationBtn"`
- [ ] Le dropdown a bien `id="notificationDropdown"`
- [ ] Le test standalone fonctionne (`test-notification.html`)
- [ ] Les routes de notification existent
- [ ] L'API `/notifications/unread-count` retourne un JSON

## Informations à Fournir si le Problème Persiste

1. **Logs de la console** (copier-coller tout)
2. **Erreurs JavaScript** (s'il y en a)
3. **HTML généré** (clic droit sur le bouton → Inspecter → copier l'HTML)
4. **Résultat du test standalone** (fonctionne ou non ?)
5. **Version du navigateur** (Chrome, Firefox, Edge, etc.)
6. **Résultat de** : `php bin/console debug:router | findstr notification`

## Prochaines Étapes

1. **Tester le fichier standalone** : `/test-notification.html`
2. **Ouvrir la console** sur la page Symfony
3. **Copier tous les logs** et me les envoyer
4. **Tester les commandes** de diagnostic ci-dessus

---

**Date** : 17 février 2026
**Status** : En diagnostic
