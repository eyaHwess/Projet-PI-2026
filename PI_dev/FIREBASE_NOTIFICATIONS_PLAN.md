# 🔔 Firebase Notifications - Plan d'Implémentation

## 🎯 Objectif

Intégrer Firebase Cloud Messaging (FCM) pour envoyer des notifications push en temps réel pour:
- ✅ Nouveau message dans le chatroom
- ✅ Nouveau membre ajouté au goal
- ✅ Mention @user dans un message

---

## 📋 Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                    Firebase Cloud Messaging                  │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│                    Symfony Backend                           │
│  - NotificationService (envoie vers FCM)                    │
│  - MessageController (nouveau message)                       │
│  - GoalController (nouveau membre)                           │
│  - MentionDetector (détecte @mentions)                       │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│                    Frontend JavaScript                       │
│  - firebase-messaging.js (reçoit notifications)             │
│  - Service Worker (notifications en arrière-plan)            │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│                    Utilisateur                               │
│  - Notifications push sur desktop                            │
│  - Notifications push sur mobile                             │
└─────────────────────────────────────────────────────────────┘
```

---

## 🔧 Étapes d'Implémentation

### Phase 1: Configuration Firebase (30 min)

#### 1.1 Créer un Projet Firebase
1. Aller sur https://console.firebase.google.com/
2. Créer un nouveau projet "PI-Coaching"
3. Activer Cloud Messaging
4. Générer une clé serveur (Server Key)
5. Télécharger le fichier de configuration

#### 1.2 Installer le SDK Firebase
```bash
composer require kreait/firebase-php
```

#### 1.3 Configuration Symfony
Créer `config/packages/firebase.yaml`:
```yaml
parameters:
    firebase.credentials: '%kernel.project_dir%/config/firebase-credentials.json'
    firebase.server_key: 'YOUR_SERVER_KEY_HERE'
```

---

### Phase 2: Backend Symfony (1h)

#### 2.1 Service Firebase
Créer `src/Service/FirebaseNotificationService.php`:
- Méthode `sendToUser(User $user, array $data)`
- Méthode `sendToMultipleUsers(array $users, array $data)`
- Méthode `sendToTopic(string $topic, array $data)`

#### 2.2 Entité FCM Token
Créer `src/Entity/FcmToken.php`:
- `user` (relation ManyToOne avec User)
- `token` (string, unique)
- `device` (string: web, android, ios)
- `createdAt` (datetime)
- `lastUsedAt` (datetime)

#### 2.3 Controller FCM Token
Créer `src/Controller/FcmTokenController.php`:
- Route POST `/fcm/register` - Enregistrer un token
- Route DELETE `/fcm/unregister` - Supprimer un token

#### 2.4 Détecteur de Mentions
Créer `src/Service/MentionDetector.php`:
- Méthode `detectMentions(string $content): array`
- Regex pour détecter @username
- Retourner liste des utilisateurs mentionnés

---

### Phase 3: Frontend JavaScript (1h)

#### 3.1 Firebase SDK
Ajouter dans `templates/base.html.twig`:
```html
<script src="https://www.gstatic.com/firebasejs/10.7.1/firebase-app-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/10.7.1/firebase-messaging-compat.js"></script>
```

#### 3.2 Fichier de Configuration
Créer `public/firebase-config.js`:
```javascript
const firebaseConfig = {
    apiKey: "YOUR_API_KEY",
    authDomain: "YOUR_AUTH_DOMAIN",
    projectId: "YOUR_PROJECT_ID",
    storageBucket: "YOUR_STORAGE_BUCKET",
    messagingSenderId: "YOUR_SENDER_ID",
    appId: "YOUR_APP_ID"
};
```

#### 3.3 Service Worker
Créer `public/firebase-messaging-sw.js`:
- Écouter les notifications en arrière-plan
- Afficher les notifications
- Gérer les clics sur notifications

#### 3.4 Manager de Notifications
Créer `public/firebase-notifications.js`:
- Initialiser Firebase
- Demander permission notifications
- Récupérer token FCM
- Envoyer token au backend
- Écouter les notifications

---

### Phase 4: Intégration dans l'Application (1h)

#### 4.1 Nouveau Message
Dans `MessageController::send()`:
```php
// Après création du message
$this->firebaseService->notifyNewMessage($message);
```

#### 4.2 Nouveau Membre
Dans `GoalController::addMember()`:
```php
// Après ajout du membre
$this->firebaseService->notifyNewMember($goal, $newMember);
```

#### 4.3 Mentions
Dans `MessageController::send()`:
```php
// Détecter les mentions
$mentions = $this->mentionDetector->detectMentions($message->getContent());
foreach ($mentions as $user) {
    $this->firebaseService->notifyMention($message, $user);
}
```

---

## 📝 Structure des Notifications

### 1. Nouveau Message
```json
{
    "notification": {
        "title": "Nouveau message de Marie",
        "body": "Super idée pour le projet! 🎉",
        "icon": "/images/logo.png",
        "badge": "/images/badge.png",
        "tag": "message-123",
        "requireInteraction": false
    },
    "data": {
        "type": "new_message",
        "messageId": "123",
        "chatroomId": "45",
        "goalId": "12",
        "authorId": "5",
        "authorName": "Marie Dupont",
        "url": "/chatroom/45"
    }
}
```

### 2. Nouveau Membre
```json
{
    "notification": {
        "title": "Nouveau membre dans 'Mon Goal'",
        "body": "Ahmed a rejoint le goal",
        "icon": "/images/logo.png"
    },
    "data": {
        "type": "new_member",
        "goalId": "12",
        "memberId": "8",
        "memberName": "Ahmed",
        "url": "/goal/12"
    }
}
```

### 3. Mention
```json
{
    "notification": {
        "title": "Marie vous a mentionné",
        "body": "@islem qu'en penses-tu?",
        "icon": "/images/logo.png",
        "tag": "mention-123"
    },
    "data": {
        "type": "mention",
        "messageId": "123",
        "chatroomId": "45",
        "authorId": "5",
        "authorName": "Marie",
        "url": "/chatroom/45#message-123"
    }
}
```

---

## 🔐 Sécurité

### Permissions
- Vérifier que l'utilisateur a accès au chatroom
- Vérifier que l'utilisateur est membre du goal
- Ne pas envoyer de notifications à soi-même

### Tokens
- Stocker les tokens de manière sécurisée
- Supprimer les tokens expirés
- Gérer les erreurs (token invalide, etc.)

### Rate Limiting
- Limiter le nombre de notifications par utilisateur
- Éviter le spam de notifications

---

## 🎨 UI/UX

### Demande de Permission
```javascript
// Afficher un message explicatif avant de demander
if (Notification.permission === 'default') {
    showNotificationPrompt();
}
```

### Badge de Compteur
```javascript
// Mettre à jour le badge avec le nombre de notifications
navigator.setAppBadge(unreadCount);
```

### Sons
```javascript
// Jouer un son pour les notifications importantes
const audio = new Audio('/sounds/notification.mp3');
audio.play();
```

---

## 📊 Base de Données

### Table: fcm_token
```sql
CREATE TABLE fcm_token (
    id SERIAL PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(255) NOT NULL UNIQUE,
    device VARCHAR(50) NOT NULL,
    created_at TIMESTAMP NOT NULL,
    last_used_at TIMESTAMP NOT NULL,
    FOREIGN KEY (user_id) REFERENCES "user"(id) ON DELETE CASCADE
);

CREATE INDEX idx_fcm_token_user ON fcm_token(user_id);
CREATE INDEX idx_fcm_token_token ON fcm_token(token);
```

---

## 🧪 Tests

### Test 1: Enregistrement Token
```bash
curl -X POST http://localhost:8000/fcm/register \
  -H "Content-Type: application/json" \
  -d '{"token": "test-token-123", "device": "web"}'
```

### Test 2: Envoi Notification
```php
$this->firebaseService->sendToUser($user, [
    'title' => 'Test',
    'body' => 'Notification de test'
]);
```

### Test 3: Mention
```
Message: "Salut @marie comment vas-tu?"
Résultat: Notification envoyée à Marie
```

---

## 📚 Documentation

### Guides à Créer
1. **FIREBASE_SETUP.md** - Configuration Firebase
2. **FIREBASE_BACKEND.md** - Implémentation backend
3. **FIREBASE_FRONTEND.md** - Implémentation frontend
4. **FIREBASE_TESTING.md** - Guide de test

---

## 🚀 Déploiement

### Variables d'Environnement
```env
FIREBASE_SERVER_KEY=your_server_key
FIREBASE_PROJECT_ID=your_project_id
FIREBASE_API_KEY=your_api_key
```

### Production
- Utiliser HTTPS (obligatoire pour notifications)
- Configurer le domaine dans Firebase Console
- Tester sur différents navigateurs

---

## 💡 Améliorations Futures

### Court Terme
- [ ] Grouper les notifications similaires
- [ ] Personnaliser les sons
- [ ] Ajouter des actions rapides (Répondre, Marquer comme lu)

### Moyen Terme
- [ ] Notifications par email (fallback)
- [ ] Préférences de notifications par utilisateur
- [ ] Résumé quotidien des notifications

### Long Terme
- [ ] Application mobile native
- [ ] Notifications riches (images, boutons)
- [ ] Analytics des notifications

---

## 📊 Métriques

### À Suivre
- Taux d'activation des notifications
- Taux de clic sur notifications
- Taux de désactivation
- Temps de réponse moyen

---

## 🎯 Résultat Attendu

Un système de notifications push complet avec:
- ✅ Notifications en temps réel
- ✅ Support multi-device
- ✅ Détection automatique des mentions
- ✅ Interface utilisateur intuitive
- ✅ Performance optimale

---

**Temps total estimé**: 3-4 heures  
**Difficulté**: ⭐⭐⭐ (Moyenne-Élevée)  
**Prérequis**: Compte Firebase, HTTPS en production

**Prêt à commencer?** 🚀
