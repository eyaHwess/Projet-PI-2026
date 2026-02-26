# 🧪 Guide de Test - Fonctionnalités de Présence

## 📋 Prérequis

Avant de commencer les tests, assurez-vous que:
- ✅ Les migrations ont été exécutées: `php bin/console doctrine:migrations:migrate`
- ✅ Le cache a été vidé: `php bin/console cache:clear`
- ✅ Le serveur Symfony est lancé: `symfony server:start` ou `php -S localhost:8000 -t public`
- ✅ Vous avez au moins 2 comptes utilisateurs de test
- ✅ Vous avez un goal avec un chatroom actif

---

## 🚀 ÉTAPE 1: Intégration du Script dans le Template

### 1.1 Ouvrir le Template du Chatroom

Ouvrir le fichier: `templates/chatroom/chatroom.html.twig` (ou `chatroom_modern.html.twig`)

### 1.2 Ajouter les Données et le Script

Chercher la balise `{% block javascripts %}` ou ajouter avant `</body>`:

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

### 1.3 Ajouter l'Indicateur de Frappe

Dans la zone des messages, juste avant le formulaire d'envoi, ajouter:

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

### 1.4 Ajouter le CSS pour l'Animation

Dans le `<style>` du template:

```css
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

### 1.5 Ajouter l'ID au Champ de Message

Trouver le champ de saisie de message et ajouter `id="messageInput"`:

```twig
<textarea id="messageInput" name="message[content]" ...></textarea>
```

---

## 🧪 ÉTAPE 2: Tests Fonctionnels

### TEST 1: Vérifier que le Script se Charge

#### Actions:
1. Ouvrir le chatroom dans le navigateur
2. Ouvrir la console développeur (F12)
3. Chercher le message: `🟢 PresenceManager initialized for chatroom: X`

#### Résultat Attendu:
```
🟢 PresenceManager initialized for chatroom: 1
```

#### Si ça ne fonctionne pas:
- Vérifier que `presence_manager.js` existe dans `public/`
- Vérifier que les divs `data-chatroom-id` et `data-user-id` sont présents
- Vider le cache du navigateur (Ctrl+Shift+R)

---

### TEST 2: Heartbeat (Statut En Ligne)

#### Actions:
1. Ouvrir le chatroom
2. Ouvrir l'onglet Network dans la console (F12 → Network)
3. Filtrer par "heartbeat"
4. Attendre 30 secondes

#### Résultat Attendu:
- Une requête POST vers `/presence/heartbeat` toutes les 30 secondes
- Statut 200 OK
- Réponse: `{"success":true}`

#### Capture d'écran:
```
POST /presence/heartbeat
Status: 200 OK
Response: {"success":true}
```

#### Si ça ne fonctionne pas:
- Vérifier que la route existe: `php bin/console debug:router | grep heartbeat`
- Vérifier les logs Symfony: `tail -f var/log/dev.log`

---

### TEST 3: Indicateur de Frappe (Typing Indicator)

#### Préparation:
- Ouvrir 2 navigateurs différents (ou 1 normal + 1 incognito)
- Se connecter avec 2 utilisateurs différents
- Ouvrir le même chatroom dans les 2 navigateurs

#### Actions:
1. **Navigateur 1**: Commencer à taper dans le champ de message
2. **Navigateur 2**: Observer la zone au-dessus du formulaire

#### Résultat Attendu:
- **Navigateur 2** affiche: "Prénom est en train d'écrire..."
- Animation de 3 points qui rebondissent
- L'indicateur disparaît après 3 secondes d'inactivité

#### Capture Visuelle:
```
┌─────────────────────────────────────┐
│ ● ● ●  Marie est en train d'écrire...│
└─────────────────────────────────────┘
```

#### Console (Navigateur 2):
```
GET /presence/typing/1/users
Response: {"typingUsers":[{"id":2,"firstName":"Marie","lastName":"Dupont"}],"count":1}
```

#### Si ça ne fonctionne pas:
- Vérifier que l'élément `#typingIndicator` existe dans le HTML
- Vérifier que le champ a bien `id="messageInput"`
- Vérifier la console pour les erreurs JavaScript

---

### TEST 4: Messages Lus (Read Receipts)

#### Préparation:
- 2 navigateurs avec 2 utilisateurs différents
- Même chatroom ouvert

#### Actions:
1. **Navigateur 1 (User A)**: Envoyer un message "Test de lecture"
2. Observer l'icône sous le message
3. **Navigateur 2 (User B)**: Ouvrir le chatroom et voir le message
4. **Navigateur 1**: Observer le changement d'icône

#### Résultat Attendu:

**Avant lecture (Navigateur 1):**
```
Test de lecture
10:30 AM ✓
```

**Après lecture (Navigateur 1):**
```
Test de lecture
10:30 AM ✓✓ 1
```

#### Console (Navigateur 2):
```
POST /message/123/mark-read
Response: {"success":true,"readCount":1}
```

#### Si ça ne fonctionne pas:
- Vérifier que la table `message_read_receipt` existe
- Vérifier que la route `/message/{id}/mark-read` fonctionne
- Vérifier que `readReceiptRepo` est injecté dans le template

---

### TEST 5: Statut En Ligne (Online Status)

#### Préparation:
- 2 navigateurs avec 2 utilisateurs différents
- Même chatroom ouvert

#### Actions:
1. **Navigateur 1 & 2**: Ouvrir le chatroom
2. Observer les avatars dans la sidebar
3. **Navigateur 2**: Fermer l'onglet
4. **Navigateur 1**: Attendre 5-6 minutes
5. Observer le changement de statut

#### Résultat Attendu:

**Immédiatement:**
```
👤 Marie Dupont
   🟢 En ligne
```

**Après 5 minutes:**
```
👤 Marie Dupont
   🟡 Il y a 5 minutes
```

**Après 1 heure:**
```
👤 Marie Dupont
   ⚫ Il y a 1 heure
```

#### Console:
```
GET /presence/online/1
Response: {
  "online": [...],
  "away": [...],
  "offline": [...],
  "counts": {"online":1,"away":0,"offline":1,"total":2}
}
```

---

### TEST 6: Compteur de Présence Groupe

#### Actions:
1. Ouvrir le chatroom avec 3 utilisateurs différents
2. Observer le header du chatroom

#### Résultat Attendu:
```
┌────────────────────────────────────┐
│ 💬 Chatroom - Mon Goal             │
│ 🟢 3 en ligne sur 5 membres        │
└────────────────────────────────────┘
```

#### Si ça ne fonctionne pas:
- Ajouter l'élément dans le template:
```twig
<div class="chat-header-subtitle">
    <span id="onlineCount">0</span> en ligne sur 
    <span id="totalParticipants">{{ goal.goalParticipations|length }}</span> membres
</div>
```

---

## 🔍 ÉTAPE 3: Vérification de la Base de Données

### Vérifier les Tables

```bash
php bin/console doctrine:schema:validate
```

**Résultat attendu:**
```
[OK] The database schema is in sync with the mapping files.
```

### Vérifier les Données

```sql
-- Vérifier les accusés de lecture
SELECT * FROM message_read_receipt;

-- Vérifier les présences
SELECT * FROM user_presence;
```

---

## 🐛 ÉTAPE 4: Débogage

### Activer les Logs JavaScript

Ajouter dans `presence_manager.js` (déjà présent):

```javascript
console.log('🟢 PresenceManager initialized');
console.log('📊 Chargement du compteur...');
console.log('✅ Compteur reçu:', data.unreadCount);
```

### Vérifier les Routes

```bash
php bin/console debug:router | grep presence
```

**Résultat attendu:**
```
presence_heartbeat          POST   /presence/heartbeat
presence_typing             POST   /presence/typing/{chatroomId}
presence_typing_users       GET    /presence/typing/{chatroomId}/users
presence_online_users       GET    /presence/online/{chatroomId}
presence_user_status        GET    /presence/status/{userId}
```

### Vérifier les Logs Symfony

```bash
tail -f var/log/dev.log
```

---

## ✅ ÉTAPE 5: Checklist Finale

Cocher chaque fonctionnalité testée:

- [ ] Script `presence_manager.js` se charge correctement
- [ ] Heartbeat fonctionne (requête toutes les 30s)
- [ ] Indicateur de frappe s'affiche quand on tape
- [ ] Indicateur de frappe disparaît après 3s d'inactivité
- [ ] Messages marqués comme lus automatiquement
- [ ] Icône ✓ devient ✓✓ quand le message est lu
- [ ] Compteur de lectures s'affiche sous les messages
- [ ] Statut en ligne (🟢) s'affiche sur les avatars
- [ ] Statut passe à "away" (🟡) après 5 minutes
- [ ] Statut passe à "offline" (⚫) après 1 heure
- [ ] Compteur "X en ligne sur Y membres" fonctionne
- [ ] Liste des participants triée par statut

---

## 🎯 ÉTAPE 6: Tests Avancés

### Test de Charge

1. Ouvrir 5 onglets avec 5 utilisateurs différents
2. Tous tapent en même temps
3. Observer: "5 personnes sont en train d'écrire..."

### Test de Reconnexion

1. Ouvrir le chatroom
2. Couper la connexion internet
3. Attendre 1 minute
4. Rétablir la connexion
5. Observer: Le heartbeat reprend automatiquement

### Test de Performance

1. Ouvrir la console Performance (F12 → Performance)
2. Enregistrer pendant 1 minute
3. Vérifier que les requêtes ne bloquent pas l'interface

---

## 📊 Métriques de Succès

### Performance
- ✅ Heartbeat: < 100ms
- ✅ Typing check: < 50ms
- ✅ Mark as read: < 100ms
- ✅ Online users: < 200ms

### Fiabilité
- ✅ Aucune erreur dans la console
- ✅ Aucune erreur 500 dans les requêtes
- ✅ Pas de fuite mémoire après 10 minutes

---

## 🆘 Problèmes Courants

### Problème 1: Script ne se charge pas
**Solution:**
```bash
# Vérifier que le fichier existe
ls -la public/presence_manager.js

# Vider le cache
php bin/console cache:clear
```

### Problème 2: Routes 404
**Solution:**
```bash
# Vérifier les routes
php bin/console debug:router | grep presence

# Vider le cache des routes
php bin/console cache:clear
```

### Problème 3: Erreur 500 sur heartbeat
**Solution:**
```bash
# Vérifier les logs
tail -f var/log/dev.log

# Vérifier la base de données
php bin/console doctrine:schema:validate
```

### Problème 4: Indicateur de frappe ne s'affiche pas
**Solution:**
1. Vérifier que `#typingIndicator` existe dans le HTML
2. Vérifier que `#messageInput` a bien cet ID
3. Vérifier la console pour les erreurs JavaScript

### Problème 5: Messages non marqués comme lus
**Solution:**
1. Vérifier que la table `message_read_receipt` existe
2. Vérifier que `readReceiptRepo` est injecté dans le contrôleur
3. Vérifier les permissions de l'utilisateur

---

## 📸 Captures d'Écran Attendues

### 1. Console au Chargement
```
🟢 PresenceManager initialized for chatroom: 1
```

### 2. Network Tab - Heartbeat
```
POST /presence/heartbeat
Status: 200 OK
Time: 45ms
```

### 3. Indicateur de Frappe
```
● ● ● Marie est en train d'écrire...
```

### 4. Message Lu
```
Test de lecture
10:30 AM ✓✓ 2
```

### 5. Statut En Ligne
```
👤 Marie Dupont
   🟢 En ligne
```

---

## 🎓 Conclusion

Si tous les tests passent, les fonctionnalités sont correctement implémentées!

**Prochaines étapes:**
1. Personnaliser les styles CSS selon votre design
2. Ajouter des sons de notification (optionnel)
3. Intégrer Mercure pour du temps réel instantané (optionnel)

---

**Date**: 22 février 2026
**Version**: 1.0
**Statut**: ✅ GUIDE COMPLET
