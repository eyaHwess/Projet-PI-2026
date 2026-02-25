# NotificationBundle

Bundle Symfony 7 pour la gestion des notifications dans l'application de coaching.

## 📋 Description

Ce bundle fournit un système complet de notifications permettant d'envoyer, stocker et gérer des notifications pour les utilisateurs et les coaches.

## 🎯 Fonctionnalités

- ✅ Envoi de notifications aux utilisateurs et coaches
- ✅ Stockage en base de données
- ✅ Gestion des notifications lues/non lues
- ✅ API REST pour récupérer les notifications
- ✅ Support des notifications liées aux demandes de coaching
- ✅ Prêt pour l'intégration Mercure (temps réel)
- ✅ Logging des notifications
- ✅ Nettoyage automatique des anciennes notifications

## 📁 Structure

```
src/NotificationBundle/
├── Controller/
│   └── NotificationController.php      # Contrôleur API et web
├── DependencyInjection/
│   └── NotificationExtension.php       # Extension du bundle
├── Entity/
│   └── Notification.php                # Entité Notification
├── Repository/
│   └── NotificationRepository.php      # Repository avec méthodes utiles
├── Resources/
│   └── config/
│       └── services.yaml               # Configuration des services
├── Service/
│   ├── NotificationManager.php         # Service principal (recommandé)
│   └── NotificationService.php         # Service legacy (compatibilité)
├── NotificationBundle.php              # Classe principale du bundle
└── README.md                           # Ce fichier
```

## 🚀 Installation

### 1. Le bundle est déjà dans votre projet

Le bundle est situé dans `src/NotificationBundle/`

### 2. Enregistrer le bundle (si nécessaire)

Le bundle est auto-découvert par Symfony 7. Si vous avez besoin de le configurer manuellement, ajoutez dans `config/bundles.php` :

```php
return [
    // ...
    App\NotificationBundle\NotificationBundle::class => ['all' => true],
];
```

### 3. Vider le cache

```bash
php bin/console cache:clear
```

## 📖 Utilisation

### Méthode Recommandée : NotificationManager

```php
use App\NotificationBundle\Service\NotificationManager;

class YourController extends AbstractController
{
    public function __construct(
        private NotificationManager $notificationManager
    ) {
    }

    public function someAction()
    {
        // Notifier un utilisateur
        $this->notificationManager->notifyUser(
            $user,
            'info',
            'Votre profil a été mis à jour avec succès.'
        );

        // Notifier un coach
        $this->notificationManager->notifyCoach(
            $coach,
            'new_request',
            'Vous avez reçu une nouvelle demande de coaching.',
            $coachingRequest
        );

        // Méthodes spécifiques pour les demandes de coaching
        $this->notificationManager->notifyRequestAccepted($request);
        $this->notificationManager->notifyRequestDeclined($request);
        $this->notificationManager->notifyCoachNewRequest($request);
        $this->notificationManager->notifyUserRequestSent($request);
    }
}
```

### Méthodes Disponibles

#### NotificationManager

```php
// Notifier un utilisateur
notifyUser(User $user, string $type, string $message, ?CoachingRequest $request = null): Notification

// Notifier un coach
notifyCoach(User $coach, string $type, string $message, ?CoachingRequest $request = null): Notification

// Notifications spécifiques
notifyRequestAccepted(CoachingRequest $request): Notification
notifyRequestDeclined(CoachingRequest $request): Notification
notifyRequestPending(CoachingRequest $request): Notification
notifyCoachNewRequest(CoachingRequest $request): Notification
notifyUserRequestSent(CoachingRequest $request): Notification
notifyUpcomingSession(User $user, CoachingRequest $request, \DateTimeInterface $date): Notification
notifySessionCancelled(User $user, CoachingRequest $request): Notification

// Gestion des notifications
markAsRead(Notification $notification): void
markAllAsReadForUser(User $user): void
deleteNotification(Notification $notification): void
deleteAllForUser(User $user): void
```

## 🔌 API REST

### Endpoints Disponibles

```
GET    /notifications                    # Liste toutes les notifications
GET    /notifications/unread-count       # Compte les notifications non lues
GET    /notifications/unread             # Liste les notifications non lues
POST   /notifications/{id}/mark-read     # Marque une notification comme lue
POST   /notifications/mark-all-read      # Marque toutes comme lues
```

### Exemple d'utilisation JavaScript

```javascript
// Récupérer le nombre de notifications non lues
fetch('/notifications/unread-count')
    .then(response => response.json())
    .then(data => {
        console.log('Notifications non lues:', data.count);
    });

// Récupérer les notifications non lues
fetch('/notifications/unread')
    .then(response => response.json())
    .then(data => {
        data.notifications.forEach(notif => {
            console.log(notif.message);
        });
    });

// Marquer une notification comme lue
fetch('/notifications/123/mark-read', {
    method: 'POST'
})
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('Notification marquée comme lue');
        }
    });
```

## 🗄️ Base de Données

### Table `notifications`

| Colonne | Type | Description |
|---------|------|-------------|
| id | INT | Identifiant unique |
| user_id | INT | Utilisateur destinataire |
| type | VARCHAR(50) | Type de notification |
| message | TEXT | Message de la notification |
| coaching_request_id | INT | Demande de coaching associée (optionnel) |
| is_read | BOOLEAN | Notification lue ou non |
| created_at | DATETIME | Date de création |

### Types de Notifications

- `info` - Information générale
- `success` - Action réussie
- `warning` - Avertissement
- `error` - Erreur
- `request_accepted` - Demande acceptée
- `request_declined` - Demande refusée
- `request_pending` - Demande en attente
- `new_request` - Nouvelle demande
- `new_request_urgent` - Nouvelle demande urgente
- `request_sent` - Demande envoyée
- `session_reminder` - Rappel de session
- `session_cancelled` - Session annulée

## 🔧 Configuration

### Services

Les services sont automatiquement configurés via `Resources/config/services.yaml`.

Vous pouvez les injecter dans vos contrôleurs ou services :

```php
use App\NotificationBundle\Service\NotificationManager;
use App\NotificationBundle\Repository\NotificationRepository;

class YourService
{
    public function __construct(
        private NotificationManager $notificationManager,
        private NotificationRepository $notificationRepository
    ) {
    }
}
```

## 🚀 Évolutions Futures

### Intégration Mercure (Temps Réel)

Le bundle est prêt pour l'intégration de Mercure. Pour activer les notifications en temps réel :

1. Installer Mercure :
```bash
composer require symfony/mercure-bundle
```

2. Modifier `NotificationManager` pour publier sur Mercure :
```php
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;

private function createNotification(...): Notification
{
    // ... code existant ...
    
    // Publier sur Mercure
    if ($this->hub) {
        $update = new Update(
            'notifications/' . $user->getId(),
            json_encode([
                'id' => $notification->getId(),
                'type' => $notification->getType(),
                'message' => $notification->getMessage(),
            ])
        );
        $this->hub->publish($update);
    }
    
    return $notification;
}
```

## 📝 Exemples d'Utilisation

### Exemple 1 : Notification simple

```php
$this->notificationManager->notifyUser(
    $user,
    'success',
    'Votre profil a été mis à jour avec succès.'
);
```

### Exemple 2 : Notification avec demande de coaching

```php
$this->notificationManager->notifyCoach(
    $coach,
    'new_request',
    'Nouvelle demande de coaching urgente !',
    $coachingRequest
);
```

### Exemple 3 : Utiliser les méthodes spécifiques

```php
// Quand un coach accepte une demande
$this->notificationManager->notifyRequestAccepted($request);

// Quand un utilisateur envoie une demande
$this->notificationManager->notifyCoachNewRequest($request);
$this->notificationManager->notifyUserRequestSent($request);
```

### Exemple 4 : Gérer les notifications

```php
// Marquer comme lue
$this->notificationManager->markAsRead($notification);

// Marquer toutes comme lues pour un utilisateur
$this->notificationManager->markAllAsReadForUser($user);

// Supprimer une notification
$this->notificationManager->deleteNotification($notification);
```

## 🧪 Tests

Pour tester le bundle :

```bash
# Créer une notification de test
php bin/console app:create-notification-for-user 1 "Test message"

# Vérifier les notifications en base
php bin/console app:debug-notifications
```

## 📚 Documentation Complémentaire

- [Symfony Bundles](https://symfony.com/doc/current/bundles.html)
- [Doctrine ORM](https://www.doctrine-project.org/projects/doctrine-orm/en/latest/)
- [Mercure](https://mercure.rocks/)

## ✅ Checklist de Migration

Si vous migrez depuis l'ancien système :

- [ ] Remplacer `App\Service\NotificationService` par `App\NotificationBundle\Service\NotificationManager`
- [ ] Remplacer `App\Entity\Notification` par `App\NotificationBundle\Entity\Notification`
- [ ] Remplacer `App\Repository\NotificationRepository` par `App\NotificationBundle\Repository\NotificationRepository`
- [ ] Mettre à jour les imports dans les contrôleurs
- [ ] Vider le cache : `php bin/console cache:clear`
- [ ] Tester les notifications

## 🆘 Support

Pour toute question ou problème, consultez la documentation ou contactez l'équipe de développement.

---

**Version** : 1.0.0  
**Symfony** : 7.x  
**PHP** : 8.1+
