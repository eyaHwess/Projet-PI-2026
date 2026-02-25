# 🔔 Guide des Notifications Live - Mercure

## ✅ Ce qui a été créé

### Fichiers Backend
1. ✅ `src/Service/NotificationService.php` - Service de gestion des notifications
2. ✅ `src/Controller/NotificationController.php` - Contrôleur des notifications
3. ✅ `src/Entity/Notification.php` - Entité (déjà existante)

### Fichiers Frontend
1. ✅ `templates/notification/list.html.twig` - Page liste des notifications
2. ✅ `templates/notification/_notification_item.html.twig` - Template partiel
3. ✅ `public/notifications_live.js` - Gestionnaire JavaScript

---

## 🚀 Fonctionnalités Implémentées

### Mode 1: Polling (ACTIF par défaut)
- ✅ Vérification toutes les 10 secondes
- ✅ Fonctionne sans configuration
- ✅ Compatible tous navigateurs

### Mode 2: Mercure (OPTIONNEL)
- 🚀 Notifications instantanées via WebSocket
- 🚀 Latence < 100ms
- 🚀 Économie de ressources

### Fonctionnalités Communes
- ✅ Badge de compteur en temps réel
- ✅ Dropdown de notifications
- ✅ Notifications navigateur (si autorisées)
- ✅ Son de notification (optionnel)
- ✅ Marquer comme lu
- ✅ Marquer tout comme lu
- ✅ Supprimer une notification

---

## 📝 Étape 1: Ajouter le Script dans base.html.twig

Dans `templates/base.html.twig`, ajouter **AVANT** `</body>`:

```twig
{# Notifications Live #}
{% if app.user %}
    {# Stocker l'ID utilisateur pour JavaScript #}
    <div data-user-id="{{ app.user.id }}" style="display: none;"></div>
    
    {# Script de notifications live #}
    <script src="{{ asset('notifications_live.js') }}"></script>
    
    {# Configuration Mercure (optionnel) #}
    <script>
        // URL publique de Mercure (si activé)
        window.MERCURE_PUBLIC_URL = '{{ mercure_public_url|default('http://localhost:3000/.well-known/mercure') }}';
    </script>
{% endif %}
```

---

## 📝 Étape 2: Utiliser le Service dans vos Contrôleurs

### Exemple: Envoyer une notification lors d'une demande de coaching

Dans `src/Controller/CoachingRequestController.php`:

```php
use App\Service\NotificationService;

public function __construct(
    private NotificationService $notificationService
) {}

public function create(Request $request): Response
{
    // ... code de création de la demande ...
    
    // Envoyer une notification au coach
    $this->notificationService->createAndPublish(
        $coach,
        'coaching_request',
        "Nouvelle demande de coaching de {$user->getFirstName()} {$user->getLastName()}",
        $coachingRequest
    );
    
    return $this->redirectToRoute('coaching_request_success');
}
```

### Exemple: Notification d'acceptation de demande

```php
public function accept(CoachingRequest $request): Response
{
    // ... code d'acceptation ...
    
    // Notifier l'utilisateur
    $this->notificationService->createAndPublish(
        $request->getUser(),
        'coaching_accepted',
        "Votre demande de coaching a été acceptée par {$coach->getFirstName()}",
        $request
    );
    
    return $this->redirectToRoute('coaching_request_list');
}
```

### Exemple: Notification de nouveau message

Dans `MessageController.php`:

```php
public function send(Request $request): Response
{
    // ... code d'envoi du message ...
    
    // Notifier tous les participants du chatroom
    foreach ($chatroom->getParticipants() as $participant) {
        if ($participant->getId() !== $author->getId()) {
            $this->notificationService->createAndPublish(
                $participant,
                'new_message',
                "Nouveau message de {$author->getFirstName()} dans {$chatroom->getName()}",
                $message
            );
        }
    }
    
    return new JsonResponse(['success' => true]);
}
```

---

## 📝 Étape 3: Activer Mercure (Optionnel)

### 3.1 Lancer Mercure Hub

```bash
docker run -d \
  --name mercure \
  -p 3000:80 \
  -e MERCURE_PUBLISHER_JWT_KEY='!ChangeThisMercureJWTKey!' \
  -e MERCURE_SUBSCRIBER_JWT_KEY='!ChangeThisMercureJWTKey!' \
  dunglas/mercure
```

### 3.2 Configurer .env

```env
MERCURE_URL=http://localhost:3000/.well-known/mercure
MERCURE_PUBLIC_URL=http://localhost:3000/.well-known/mercure
MERCURE_JWT_SECRET=!ChangeThisMercureJWTKey!
```

### 3.3 Vider le cache

```bash
php bin/console cache:clear
```

---

## 🧪 Tests

### Test 1: Polling (Actuel)
1. Ouvrir l'application dans 2 onglets
2. Se connecter avec 2 utilisateurs différents
3. Créer une action qui génère une notification (ex: demande de coaching)
4. Observer la notification apparaître après ~10 secondes dans l'autre onglet

### Test 2: Mercure (Si activé)
1. Vérifier que Docker tourne: `docker ps`
2. Ouvrir l'application dans 2 onglets
3. Créer une action qui génère une notification
4. Observer la notification apparaître **INSTANTANÉMENT** dans l'autre onglet

### Test 3: Notifications Navigateur
1. Autoriser les notifications dans le navigateur
2. Créer une notification
3. Observer la notification système apparaître

---

## 🎨 Personnalisation

### Changer le délai de polling

Dans `public/notifications_live.js`, ligne 67:

```javascript
// Poll toutes les 10 secondes (10000ms)
this.pollingInterval = setInterval(() => {
    // ...
}, 10000); // ← Changer cette valeur
```

### Ajouter un son personnalisé

1. Placer votre fichier audio dans `public/sounds/notification.mp3`
2. Le son sera joué automatiquement

### Personnaliser les icônes de notification

Dans `templates/notification/_notification_item.html.twig`:

```twig
{% if notification.type == 'mon_type' %}
    <i class="fas fa-mon-icone"></i>
{% endif %}
```

---

## 📊 Types de Notifications Disponibles

| Type | Description | Icône |
|------|-------------|-------|
| `coaching_request` | Nouvelle demande de coaching | 👤 fa-user-plus |
| `coaching_accepted` | Demande acceptée | ✅ fa-check-circle |
| `coaching_rejected` | Demande rejetée | ❌ fa-times-circle |
| `new_message` | Nouveau message | 💬 fa-comment |
| `goal_invitation` | Invitation à un goal | 🎯 fa-bullseye |

### Ajouter un nouveau type

```php
$this->notificationService->createAndPublish(
    $user,
    'mon_nouveau_type',
    'Message de la notification',
    $entityRelated // optionnel
);
```

---

## 🔧 API du NotificationService

### createAndPublish()
```php
$notification = $notificationService->createAndPublish(
    User $user,              // Utilisateur destinataire
    string $type,            // Type de notification
    string $message,         // Message à afficher
    $relatedEntity = null    // Entité liée (optionnel)
);
```

### markAsRead()
```php
$notificationService->markAsRead($notification);
```

### markAllAsRead()
```php
$notificationService->markAllAsRead($user);
```

### getUnreadCount()
```php
$count = $notificationService->getUnreadCount($user);
```

### getRecentNotifications()
```php
$notifications = $notificationService->getRecentNotifications($user, 10);
```

---

## 🔄 Architecture

```
┌─────────────────────────────────────────────────────────┐
│                    CLIENT (Browser)                      │
│                                                          │
│  ┌────────────────────────────────────────────────┐    │
│  │      NotificationManager (JavaScript)           │    │
│  │                                                 │    │
│  │  ┌──────────┐         ┌──────────┐            │    │
│  │  │ Mercure  │         │ Polling  │            │    │
│  │  │(Optional)│         │ (Active) │            │    │
│  │  └────┬─────┘         └────┬─────┘            │    │
│  │       │                    │                   │    │
│  │       └────────┬───────────┘                   │    │
│  │                ▼                               │    │
│  │       Badge + Dropdown                         │    │
│  └────────────────────────────────────────────────┘    │
└─────────────────────────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────┐
│                 Symfony Server                           │
│                                                          │
│  NotificationService                                     │
│  ├─ createAndPublish()                                  │
│  ├─ publishNotification() → Mercure Hub                │
│  └─ markAsRead()                                        │
│                                                          │
│  NotificationController                                  │
│  ├─ /notification/fetch (AJAX)                          │
│  ├─ /notification/{id}/mark-read                        │
│  └─ /notification/mark-all-read                         │
└─────────────────────────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────┐
│              Mercure Hub (Optional)                      │
│                                                          │
│  Topic: notification/user/{userId}                       │
│  WebSocket Broadcast                                     │
└─────────────────────────────────────────────────────────┘
```

---

## 🎯 Résumé

Vous avez maintenant:
- ✅ Un système de notifications fonctionnel (polling)
- ✅ Badge de compteur en temps réel
- ✅ Dropdown de notifications
- ✅ Page de liste complète
- ✅ Notifications navigateur
- 🚀 Mercure prêt à activer (optionnel)

**Le système fonctionne immédiatement avec le polling!**

---

## 📚 Prochaines Étapes

1. Intégrer les notifications dans vos contrôleurs
2. (Optionnel) Activer Mercure pour du temps réel
3. Personnaliser les types de notifications
4. Ajouter des sons personnalisés
5. Créer des filtres de notifications

---

**Statut**: ✅ **FONCTIONNEL**
**Mercure**: 🚀 **PRÊT** (optionnel)
