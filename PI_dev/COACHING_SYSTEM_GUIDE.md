# Guide du Système de Demande de Coaching

## Résumé des modifications

Ce document décrit les modifications apportées au système de demande de coaching pour permettre aux utilisateurs d'envoyer des messages personnalisés et de filtrer les coaches par spécialité.

## Fonctionnalités ajoutées

### 1. Message dans les demandes de coaching
- Les utilisateurs peuvent maintenant écrire un message personnalisé (10-1000 caractères) lors de leur demande
- Le message est affiché au coach pour mieux comprendre les besoins de l'utilisateur
- Validation automatique de la longueur du message

### 2. Filtre par spécialité
- Les utilisateurs peuvent filtrer les coaches par spécialité
- Affichage de toutes les spécialités disponibles
- Navigation facile entre les différentes spécialités

### 3. Formulaire de demande amélioré
- Formulaire centralisé avec sélection du coach et message
- Validation côté serveur et client
- Interface utilisateur moderne et intuitive

### 4. Gestion des statuts
- **PENDING (En attente)**: Demande envoyée, en attente de réponse du coach
- **ACCEPTED (Acceptée)**: Le coach a accepté la demande
- **DECLINED (Refusée)**: Le coach a refusé la demande

## Fichiers modifiés

### Entités
- `src/Entity/CoachingRequest.php`: Ajout du champ `message`
- `src/Entity/User.php`: Ajout de la méthode `isCoach()`

### Formulaires
- `src/Form/CoachingRequestType.php`: Nouveau formulaire pour les demandes

### Contrôleurs
- `src/Controller/CoachController.php`: Gestion du formulaire et filtres
- `src/Controller/CoachingRequestController.php`: Affichage des messages

### Repositories
- `src/Repository/UserRepository.php`: Méthodes pour filtrer par spécialité

### Templates
- `templates/coach/index.html.twig`: Nouveau design avec formulaire et filtres
- `templates/coaching_request/index.html.twig`: Affichage des messages

### Migrations
- `migrations/Version20260211205026.php`: Ajout du champ message

## Instructions d'installation

### 1. Exécuter la migration
```bash
cd PI_dev
php bin/console doctrine:migrations:migrate
```

### 2. Vider le cache
```bash
php bin/console cache:clear
```

### 3. Tester le système

#### Pour les utilisateurs:
1. Aller sur `/coaches`
2. Remplir le formulaire avec:
   - Sélection d'un coach
   - Message personnalisé (minimum 10 caractères)
3. Envoyer la demande
4. Voir le statut dans "Mes demandes de coaching"

#### Pour les coaches:
1. Aller sur `/coach/requests` (accessible via le menu coach)
2. Voir les demandes en attente avec les messages
3. Accepter ou refuser les demandes
4. Les demandes acceptées créent automatiquement une session

## Utilisation

### Créer une demande de coaching (Utilisateur)
```php
$coachingRequest = new CoachingRequest();
$coachingRequest->setUser($currentUser);
$coachingRequest->setCoach($selectedCoach);
$coachingRequest->setMessage("Je souhaite améliorer ma condition physique...");
// Status est automatiquement "pending"
```

### Gérer une demande (Coach)
```php
// Accepter
$coachingRequest->setStatus(CoachingRequest::STATUS_ACCEPTED);

// Refuser
$coachingRequest->setStatus(CoachingRequest::STATUS_DECLINED);

// Mettre en attente (status par défaut)
$coachingRequest->setStatus(CoachingRequest::STATUS_PENDING);
```

### Filtrer les coaches par spécialité
```php
// Dans le contrôleur
$coaches = $this->userRepository->findCoachesBySpeciality('Fitness');

// Ou tous les coaches
$coaches = $this->userRepository->findCoaches();
```

## Structure de la base de données

### Table: coaching_request
```sql
- id (INT, PRIMARY KEY)
- user_id (INT, FOREIGN KEY -> user.id)
- coach_id (INT, FOREIGN KEY -> user.id)
- message (TEXT, NOT NULL)
- status (VARCHAR(20), DEFAULT 'pending')
- created_at (DATETIME)
- responded_at (DATETIME, NULLABLE)
```

## Validation

### Message
- Obligatoire
- Minimum: 10 caractères
- Maximum: 1000 caractères

### Coach
- Doit avoir le rôle ROLE_COACH
- Ne peut pas être l'utilisateur lui-même
- Pas de demande en attente existante

## Interface utilisateur

### Page des coaches (`/coaches`)
- Filtres par spécialité en haut
- Formulaire de demande centralisé
- Liste des coaches avec leurs spécialités
- Badges de statut pour les demandes existantes
- Section "Mes demandes" en bas

### Page des demandes coach (`/coach/requests`)
- Section "En attente" avec les nouvelles demandes
- Affichage du message de l'utilisateur
- Boutons Accepter/Refuser
- Section "Toutes les demandes" avec historique
- Badges de statut colorés

## Prochaines améliorations possibles

1. Notifications par email
2. Système de réponse du coach au message
3. Historique des conversations
4. Filtres avancés (rating, disponibilité)
5. Recherche par nom de coach
6. Pagination des demandes
7. Export des demandes en PDF

## Support

Pour toute question ou problème, vérifiez:
1. Les logs Symfony: `var/log/dev.log`
2. La console du navigateur pour les erreurs JavaScript
3. Les messages flash pour les erreurs de validation
