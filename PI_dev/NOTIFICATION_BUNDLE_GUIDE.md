# 📦 NotificationBundle - Guide Complet

**Date** : 21 février 2026  
**Statut** : ✅ BUNDLE CRÉÉ ET PRÊT

---

## 🎯 Objectif Atteint

Transformation du système de notifications en un **NotificationBundle** réutilisable et proprement structuré.

---

## 📁 Structure du Bundle

```
src/NotificationBundle/
├── Controller/
│   └── NotificationController.php           # API REST + Interface web
├── DependencyInjection/
│   └── NotificationExtension.php            # Extension Symfony
├── Entity/
│   └── Notification.php                     # Entité Notification
├── Repository/
│   └── NotificationRepository.php           # Repository avec méthodes
├── Resources/
│   └── config/
│       └── services.yaml                    # Configuration services
├── Service/
│   ├── NotificationManager.php              # ⭐ Service principal
│   └── NotificationService.php              # Service legacy
├── NotificationBundle.php                   # Classe du bundle
└── README.md                                # Documentation complète
```

---

## ✅ Ce Qui a Été Fait

### 1. Création du Bundle
- ✅ Structure standard Symfony
- ✅ Namespace : `App\NotificationBundle`
- ✅ Extension DependencyInjection
- ✅ Configuration des services

### 2. Centralisation de la Logique
- ✅ `NotificationManager` - Service principal
- ✅ `NotificationService` - Service legacy (compatibilité)
- ✅ `NotificationRepository` - Méthodes de requêtes
- ✅ `Notification` - Entité

### 3. Fonctionnalités
- ✅ `notifyUser()` - Notifier un utilisateur
- ✅ `notifyCoach()` - Notifier un coach
- ✅ Méthodes spécifiques (acceptation, refus, etc.)
- ✅ Gestion des notifications (marquer comme lu, supprimer)
- ✅ API REST complète
- ✅ Logging intégré

### 4. Prêt pour le Futur
- ✅ Architecture extensible
- ✅ Prêt pour Mercure (temps réel)
- ✅ Documentation complète

---

## 🚀 Utilisation Simple

### Dans vos Controllers

```php
use App\NotificationBundle\Service\NotificationManager;

class CoachingRequestController extends AbstractController
{
    public function __construct(
        private NotificationManager $notificationManager
    ) {
    }

    public function acceptRequest(CoachingRequest $request)
    {
        // Accepter la demande...
        
        // Notifier l'utilisateur
        $this->notificationManager->notifyRequestAccepted($request);
        
        // OU utiliser la méthode générique
        $this->notificationManager->notifyUser(
            $request->getUser(),
            'success',
            'Votre demande a été acceptée !'
        );
    }

    public function createRequest(CoachingRequest $request)
    {
        // Créer la demande...
        
        // Notifier le coach ET l'utilisateur
        $this->notificationManager->notifyCoachNewRequest($request);
        $this->notificationManager->notifyUserRequestSent($request);
    }
}
```

---

## 📖 Méthodes Principales

### NotificationManager

```php
// Méthodes génériques
notifyUser(User $user, string $type, string $message, ?CoachingRequest $request = null)
notifyCoach(User $coach, string $type, string $message, ?CoachingRequest $request = null)

// Méthodes spécifiques
notifyRequestAccepted(CoachingRequest $request)
notifyRequestDeclined(CoachingRequest $request)
notifyRequestPending(CoachingRequest $request)
notifyCoachNewRequest(CoachingRequest $request)
notifyUserRequestSent(CoachingRequest $request)
notifyUpcomingSession(User $user, CoachingRequest $request, \DateTimeInterface $date)
notifySessionCancelled(User $user, CoachingRequest $request)

// Gestion
markAsRead(Notification $notification)
markAllAsReadForUser(User $user)
deleteNotification(Notification $notification)
deleteAllForUser(User $user)
```

---

## 🔄 Migration depuis l'Ancien Système

### Étape 1 : Mettre à jour les imports

**Avant** :
```php
use App\Service\NotificationService;
use App\Entity\Notification;
use App\Repository\NotificationRepository;
```

**Après** :
```php
use App\NotificationBundle\Service\NotificationManager;
use App\NotificationBundle\Entity\Notification;
use App\NotificationBundle\Repository\NotificationRepository;
```

### Étape 2 : Remplacer NotificationService par NotificationManager

**Avant** :
```php
public function __construct(
    private NotificationService $notificationService
) {
}

$this->notificationService->notifyRequestAccepted($request);
```

**Après** :
```php
public function __construct(
    private NotificationManager $notificationManager
) {
}

$this->notificationManager->notifyRequestAccepted($request);
```

### Étape 3 : Utiliser les nouvelles méthodes

**Avant** (logique dans le controller) :
```php
$notification = new Notification();
$notification->setUser($user);
$notification->setType('info');
$notification->setMessage('Message');
$this->entityManager->persist($notification);
$this->entityManager->flush();
```

**Après** (utiliser le manager) :
```php
$this->notificationManager->notifyUser($user, 'info', 'Message');
```

---

## 🔌 API REST

### Endpoints Disponibles

```
GET    /notifications                    # Liste toutes les notifications
GET    /notifications/unread-count       # Compte les non lues
GET    /notifications/unread             # Liste les non lues
POST   /notifications/{id}/mark-read     # Marquer comme lue
POST   /notifications/mark-all-read      # Tout marquer comme lu
```

### Exemple JavaScript

```javascript
// Récupérer le nombre de notifications non lues
async function getUnreadCount() {
    const response = await fetch('/notifications/unread-count');
    const data = await response.json();
    return data.count;
}

// Récupérer les notifications non lues
async function getUnreadNotifications() {
    const response = await fetch('/notifications/unread');
    const data = await response.json();
    return data.notifications;
}

// Marquer comme lue
async function markAsRead(notificationId) {
    const response = await fetch(`/notifications/${notificationId}/mark-read`, {
        method: 'POST'
    });
    const data = await response.json();
    return data.success;
}
```

---

## 🎨 Types de Notifications

| Type | Description | Utilisation |
|------|-------------|-------------|
| `info` | Information générale | Messages informatifs |
| `success` | Action réussie | Confirmations |
| `warning` | Avertissement | Alertes |
| `error` | Erreur | Messages d'erreur |
| `request_accepted` | Demande acceptée | Coach accepte |
| `request_declined` | Demande refusée | Coach refuse |
| `request_pending` | Demande en attente | Coach met en attente |
| `new_request` | Nouvelle demande | Utilisateur envoie |
| `new_request_urgent` | Demande urgente | Demande prioritaire |
| `request_sent` | Demande envoyée | Confirmation envoi |
| `session_reminder` | Rappel session | Rappel automatique |
| `session_cancelled` | Session annulée | Annulation |

---

## 🚀 Évolution Future : Mercure (Temps Réel)

Le bundle est prêt pour Mercure. Pour activer :

### 1. Installer Mercure
```bash
composer require symfony/mercure-bundle
```

### 2. Configurer Mercure
```yaml
# config/packages/mercure.yaml
mercure:
    hubs:
        default:
            url: '%env(MERCURE_URL)%'
            public_url: '%env(MERCURE_PUBLIC_URL)%'
            jwt:
                secret: '%env(MERCURE_JWT_SECRET)%'
```

### 3. Modifier NotificationManager
```php
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;

public function __construct(
    private EntityManagerInterface $entityManager,
    private ?LoggerInterface $logger = null,
    private ?HubInterface $hub = null  // Ajouter Mercure
) {
}

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
                'createdAt' => $notification->getCreatedAt()->format('c'),
            ])
        );
        $this->hub->publish($update);
    }
    
    return $notification;
}
```

### 4. Écouter côté client
```javascript
const eventSource = new EventSource('/.well-known/mercure?topic=notifications/' + userId);
eventSource.onmessage = event => {
    const notification = JSON.parse(event.data);
    console.log('Nouvelle notification:', notification);
    // Afficher la notification en temps réel
};
```

---

## 📊 Avantages du Bundle

### Avant (Système Dispersé)
- ❌ Logique dans les controllers
- ❌ Code dupliqué
- ❌ Difficile à maintenir
- ❌ Pas réutilisable
- ❌ Pas de centralisation

### Après (NotificationBundle)
- ✅ Logique centralisée
- ✅ Code réutilisable
- ✅ Facile à maintenir
- ✅ Architecture propre
- ✅ Extensible (Mercure, etc.)
- ✅ Documentation complète
- ✅ API REST intégrée

---

## 🧪 Tests

### Créer une notification de test
```bash
php bin/console app:create-notification-for-user 1 "Message de test"
```

### Vérifier les notifications
```bash
php bin/console app:debug-notifications
```

### Tester l'API
```bash
# Compter les non lues
curl http://localhost:8000/notifications/unread-count

# Récupérer les non lues
curl http://localhost:8000/notifications/unread

# Marquer comme lue
curl -X POST http://localhost:8000/notifications/1/mark-read
```

---

## ✅ Checklist de Vérification

### Installation
- [ ] Bundle créé dans `src/NotificationBundle/`
- [ ] Structure complète (Controller, Service, Entity, Repository)
- [ ] Configuration des services
- [ ] Cache vidé

### Utilisation
- [ ] Importer `NotificationManager` dans les controllers
- [ ] Remplacer les appels à `NotificationService`
- [ ] Tester `notifyUser()` et `notifyCoach()`
- [ ] Vérifier les notifications en base de données

### API
- [ ] Tester `/notifications/unread-count`
- [ ] Tester `/notifications/unread`
- [ ] Tester le marquage comme lu

### Documentation
- [ ] Lire `src/NotificationBundle/README.md`
- [ ] Consulter les exemples d'utilisation
- [ ] Comprendre les types de notifications

---

## 📝 Exemples Complets

### Exemple 1 : Acceptation de demande

```php
#[Route('/coach/requests/{id}/accept', name: 'app_coach_request_accept')]
public function accept(
    CoachingRequest $request,
    NotificationManager $notificationManager
): Response {
    // Accepter la demande
    $request->setStatus('accepted');
    $this->entityManager->flush();
    
    // Notifier l'utilisateur
    $notificationManager->notifyRequestAccepted($request);
    
    $this->addFlash('success', 'Demande acceptée avec succès');
    return $this->redirectToRoute('app_coach_requests');
}
```

### Exemple 2 : Création de demande

```php
#[Route('/coaching-request/create', name: 'app_coaching_request_create')]
public function create(
    Request $httpRequest,
    NotificationManager $notificationManager
): Response {
    // Créer la demande
    $coachingRequest = new CoachingRequest();
    // ... remplir les données ...
    $this->entityManager->persist($coachingRequest);
    $this->entityManager->flush();
    
    // Notifier le coach ET l'utilisateur
    $notificationManager->notifyCoachNewRequest($coachingRequest);
    $notificationManager->notifyUserRequestSent($coachingRequest);
    
    $this->addFlash('success', 'Demande envoyée avec succès');
    return $this->redirectToRoute('app_home');
}
```

### Exemple 3 : Notification personnalisée

```php
public function someAction(
    User $user,
    NotificationManager $notificationManager
): Response {
    // Notifier avec un message personnalisé
    $notificationManager->notifyUser(
        $user,
        'info',
        'Votre profil a été mis à jour avec succès.'
    );
    
    // Notifier un coach
    $notificationManager->notifyCoach(
        $coach,
        'warning',
        'Vous avez 3 demandes en attente.'
    );
    
    return $this->redirectToRoute('app_home');
}
```

---

## 🆘 Dépannage

### Problème : Services non trouvés
```bash
php bin/console cache:clear
php bin/console debug:container NotificationManager
```

### Problème : Namespace incorrect
Vérifier que tous les imports utilisent `App\NotificationBundle\...`

### Problème : Notifications non enregistrées
Vérifier que l'EntityManager est bien injecté et que `flush()` est appelé

---

## 📚 Documentation

- **README complet** : `src/NotificationBundle/README.md`
- **Ce guide** : `NOTIFICATION_BUNDLE_GUIDE.md`
- **Symfony Bundles** : https://symfony.com/doc/current/bundles.html

---

## 🎉 Conclusion

Le **NotificationBundle** est maintenant prêt à l'emploi !

**Avantages** :
- ✅ Architecture propre et réutilisable
- ✅ API simple : `notifyUser()` et `notifyCoach()`
- ✅ Extensible (Mercure, etc.)
- ✅ Documentation complète
- ✅ Prêt pour la production

**Prochaines étapes** :
1. Migrer les controllers existants
2. Tester les notifications
3. (Optionnel) Intégrer Mercure pour le temps réel

---

**Version** : 1.0.0  
**Date** : 21 février 2026  
**Statut** : ✅ PRÊT À L'EMPLOI
