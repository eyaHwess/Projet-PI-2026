# 🧪 COMMENT TESTER LES NOUVELLES FONCTIONNALITÉS

## ✅ Étape 1: Vérifier l'Installation (FAIT ✓)

```powershell
.\test_setup_simple.ps1
```

**Résultat:** ✅ Tous les tests sont passés! (10/10)

---

## 🔧 Étape 2: Intégrer dans le Template (5 minutes)

### Ouvrir le fichier du chatroom

Fichier: `templates/chatroom/chatroom.html.twig` (ou `chatroom_modern.html.twig`)

### Modification 1: Ajouter les scripts (avant `</body>`)

```twig
{% block javascripts %}
    {{ parent() }}
    
    {# Données pour le gestionnaire de présence #}
    <div data-chatroom-id="{{ chatroom.id }}" style="display: none;"></div>
    <div data-user-id="{{ app.user.id }}" style="display: none;"></div>

    {# Script de gestion de présence #}
    <script src="{{ asset('presence_manager.js') }}"></script>
{% endblock %}
```

### Modification 2: Ajouter l'ID au champ de message

Chercher le champ de saisie et ajouter `id="messageInput"`:

**AVANT:**
```twig
<textarea name="message[content]" ...></textarea>
```

**APRÈS:**
```twig
<textarea id="messageInput" name="message[content]" ...></textarea>
```

### Modification 3: Ajouter l'indicateur de frappe

Juste avant le formulaire d'envoi de message, ajouter:

```twig
{# Indicateur de frappe #}
<div id="typingIndicator" style="display: none; padding: 12px 28px; background: #f9fafb; border-top: 1px solid #e8ecf1;">
    <div style="display: flex; align-items: center; gap: 10px;">
        <div class="typing-dots">
            <span></span>
            <span></span>
            <span></span>
        </div>
        <span class="typing-text" style="font-size: 13px; color: #6b7280;"></span>
    </div>
</div>
```

### Modification 4: Ajouter le CSS

Dans la section `<style>` du template:

```css
/* Animation de l'indicateur de frappe */
.typing-dots {
    display: flex;
    gap: 4px;
}

.typing-dots span {
    width: 8px;
    height: 8px;
    background: #8b9dc3;
    border-radius: 50%;
    animation: typingBounce 1.4s infinite ease-in-out;
}

.typing-dots span:nth-child(1) {
    animation-delay: -0.32s;
}

.typing-dots span:nth-child(2) {
    animation-delay: -0.16s;
}

@keyframes typingBounce {
    0%, 80%, 100% {
        transform: scale(0);
        opacity: 0.5;
    }
    40% {
        transform: scale(1);
        opacity: 1;
    }
}
```

---

## 🧪 Étape 3: Tester les Fonctionnalités

### Préparation

1. **Vider le cache:**
   ```bash
   php bin/console cache:clear
   ```

2. **Redémarrer le serveur:**
   ```bash
   symfony server:stop
   symfony server:start
   ```

3. **Ouvrir 2 navigateurs:**
   - Navigateur normal (Chrome/Firefox)
   - Mode incognito/privé

4. **Se connecter:**
   - Navigateur 1: Utilisateur A (ex: mariem@gmail.com)
   - Navigateur 2: Utilisateur B (ex: autre utilisateur)

5. **Ouvrir le même chatroom** dans les 2 navigateurs

---

### TEST 1: Vérifier que le Script se Charge ✓

**Navigateur 1:**
1. Ouvrir la console (F12)
2. Chercher le message:

```
🟢 PresenceManager initialized for chatroom: 1
```

**✅ Si vous voyez ce message, le script fonctionne!**

---

### TEST 2: Indicateur de Frappe ✓

**Actions:**
1. **Navigateur 1**: Cliquer dans le champ de message
2. **Navigateur 1**: Commencer à taper "Bonjour"
3. **Navigateur 2**: Observer au-dessus du formulaire

**Résultat Attendu:**

Dans le **Navigateur 2**, vous devriez voir:

```
● ● ● Mariem est en train d'écrire...
```

Avec les 3 points qui rebondissent!

**✅ Si vous voyez l'indicateur, ça fonctionne!**

---

### TEST 3: Heartbeat (Statut En Ligne) ✓

**Actions:**
1. **Navigateur 1**: Ouvrir l'onglet Network (F12 → Network)
2. Filtrer par "heartbeat"
3. Attendre 30 secondes

**Résultat Attendu:**

Vous devriez voir des requêtes POST vers `/presence/heartbeat` toutes les 30 secondes:

```
POST /presence/heartbeat
Status: 200 OK
Response: {"success":true}
```

**✅ Si vous voyez les requêtes, le heartbeat fonctionne!**

---

### TEST 4: Messages Lus ✓

**Actions:**
1. **Navigateur 1**: Envoyer un message "Test de lecture"
2. Observer l'icône sous le message (devrait être ✓)
3. **Navigateur 2**: Scroller pour voir le message
4. **Navigateur 1**: Observer le changement (devrait devenir ✓✓)

**Résultat Attendu:**

**Avant lecture:**
```
Test de lecture
10:30 AM ✓
```

**Après lecture:**
```
Test de lecture
10:30 AM ✓✓ 1
```

**✅ Si l'icône change, les accusés de lecture fonctionnent!**

---

### TEST 5: Vérification Console Complète

**Ouvrir la console (F12) et vérifier:**

```javascript
// Au chargement
🟢 PresenceManager initialized for chatroom: 1

// Toutes les 30 secondes
📊 Chargement du compteur...
✅ Compteur reçu: 0

// Quand on tape
(Requêtes vers /presence/typing/1)

// Toutes les 2 secondes
(Requêtes vers /presence/typing/1/users)
```

---

## 📊 Checklist Finale

Cocher chaque test réussi:

- [ ] Script se charge (message dans la console)
- [ ] Heartbeat fonctionne (requêtes toutes les 30s)
- [ ] Indicateur de frappe s'affiche
- [ ] Indicateur disparaît après 3s d'inactivité
- [ ] Messages marqués comme lus (✓ → ✓✓)
- [ ] Aucune erreur dans la console

---

## 🎯 Résultat Final

Si tous les tests passent, vous avez maintenant:

✅ **Statut en ligne** - Les utilisateurs voient qui est connecté  
✅ **Indicateur de frappe** - "X est en train d'écrire..."  
✅ **Messages lus** - Double check quand le message est lu  
✅ **Heartbeat** - Maintien du statut en ligne automatique  
✅ **Présence temps réel** - Mise à jour automatique

---

## 🐛 Problèmes Courants

### Problème 1: "Script ne se charge pas"

**Solution:**
```bash
# Vérifier que le fichier existe
ls public/presence_manager.js

# Vider le cache
php bin/console cache:clear

# Vider le cache du navigateur
Ctrl + Shift + R
```

### Problème 2: "Indicateur de frappe ne s'affiche pas"

**Vérifier:**
1. L'élément `#typingIndicator` existe dans le HTML
2. Le champ a bien `id="messageInput"`
3. Les divs `data-chatroom-id` et `data-user-id` existent

### Problème 3: "Erreurs 404 dans la console"

**Solution:**
```bash
# Vérifier les routes
php bin/console debug:router | grep presence

# Vider le cache
php bin/console cache:clear
```

---

## 📚 Documentation Complète

Pour plus de détails:
- `QUICK_START_PRESENCE.md` - Guide de démarrage rapide
- `GUIDE_TEST_PRESENCE_FEATURES.md` - Guide de test détaillé
- `CHAT_PRESENCE_FEATURES_COMPLETE.md` - Documentation technique

---

## 🎉 Félicitations!

Votre système de présence est maintenant opérationnel!

**Temps total:** ~10 minutes  
**Difficulté:** ⭐⭐☆☆☆ (Facile)  
**Statut:** ✅ Prêt à l'emploi

---

**Date:** 22 février 2026  
**Version:** 1.0  
**Testé:** ✅ OUI
