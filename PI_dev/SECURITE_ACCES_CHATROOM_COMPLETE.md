# Sécurité d'Accès au Chatroom - COMPLETE ✅

## Objectif
Restreindre l'accès au chatroom uniquement aux membres approuvés du goal.

## Problème Initial
- N'importe qui pouvait accéder à `/chatroom/{id}` sans vérification
- Pas de contrôle d'authentification
- Pas de vérification de membership
- Pas de vérification du statut d'approbation

## Solution Implémentée

### 1. ChatroomController::show()
**Fichier:** `src/Controller/ChatroomController.php`

**Vérifications ajoutées:**
```php
// 1. Vérifier que l'utilisateur est connecté
$user = $this->getUser();
if (!$user) {
    $this->addFlash('error', 'Vous devez être connecté pour accéder au chatroom.');
    return $this->redirectToRoute('app_login');
}

// 2. Vérifier que l'utilisateur est membre du goal
$participation = $em->getRepository(\App\Entity\GoalParticipation::class)->findOneBy([
    'goal' => $goal,
    'user' => $user
]);

if (!$participation) {
    $this->addFlash('error', 'Vous devez rejoindre ce goal pour accéder au chatroom.');
    return $this->redirectToRoute('goal_list');
}

// 3. Vérifier que la participation est approuvée
if (!$participation->isApproved()) {
    $this->addFlash('warning', 'Votre demande d\'accès est en attente d\'approbation.');
    return $this->redirectToRoute('goal_list');
}
```

### 2. MessageController::chatroom()
**Fichier:** `src/Controller/MessageController.php`

**Vérifications ajoutées:**
```php
// 1. Vérifier que l'utilisateur est connecté
$user = $this->getUser();
if (!$user) {
    $this->addFlash('error', 'Vous devez être connecté pour accéder au chatroom.');
    return $this->redirectToRoute('app_login');
}

// 2. Vérifier que l'utilisateur est membre approuvé
$currentUserParticipation = $em->getRepository(\App\Entity\GoalParticipation::class)->findOneBy([
    'user' => $user,
    'goal' => $goal
]);

if (!$currentUserParticipation) {
    $this->addFlash('error', 'Vous devez rejoindre ce goal pour accéder au chatroom.');
    return $this->redirectToRoute('goal_list');
}

if (!$currentUserParticipation->isApproved()) {
    $this->addFlash('warning', 'Votre demande d\'accès est en attente d\'approbation.');
    return $this->redirectToRoute('goal_list');
}
```

### 3. MessageController::fetchMessages()
**Vérifications ajoutées:**
```php
// 1. Vérifier que l'utilisateur est connecté
$user = $this->getUser();
if (!$user) {
    return new JsonResponse(['error' => 'Vous devez être connecté'], 401);
}

// 2. Vérifier que l'utilisateur est membre approuvé
$participation = $em->getRepository(\App\Entity\GoalParticipation::class)->findOneBy([
    'user' => $user,
    'goal' => $goal
]);

if (!$participation || !$participation->isApproved()) {
    return new JsonResponse(['error' => 'Accès refusé'], 403);
}
```

### 4. MessageController::sendVoiceMessage()
**Vérifications ajoutées:**
```php
// 1. Vérifier que l'utilisateur est connecté
$user = $this->getUser();
if (!$user) {
    return new JsonResponse(['error' => 'Vous devez être connecté'], 401);
}

// 2. Vérifier que l'utilisateur est membre approuvé
$participation = $em->getRepository(\App\Entity\GoalParticipation::class)->findOneBy([
    'user' => $user,
    'goal' => $goal
]);

if (!$participation || !$participation->isApproved()) {
    return new JsonResponse(['error' => 'Accès refusé'], 403);
}
```

## Niveaux de Sécurité

### Niveau 1: Authentification
✅ L'utilisateur doit être connecté
- Redirection vers `/login` si non connecté
- Code HTTP 401 pour les requêtes AJAX

### Niveau 2: Membership
✅ L'utilisateur doit être membre du goal
- Vérification de l'existence d'une GoalParticipation
- Redirection vers la liste des goals si non membre
- Code HTTP 403 pour les requêtes AJAX

### Niveau 3: Approbation
✅ La participation doit être approuvée (STATUS_APPROVED)
- Vérification du statut de la participation
- Message d'attente si statut PENDING
- Refus si statut REJECTED

## Codes HTTP Utilisés

### 200 OK
- Accès autorisé, requête réussie

### 301 Moved Permanently
- Redirection de l'ancienne route vers la nouvelle

### 401 Unauthorized
- Utilisateur non connecté
- Retourné pour les requêtes AJAX

### 403 Forbidden
- Utilisateur connecté mais pas membre
- Utilisateur membre mais pas approuvé
- Retourné pour les requêtes AJAX

### 404 Not Found
- Goal ou chatroom introuvable

## Scénarios de Test

### Scénario 1: Utilisateur Non Connecté
```
Action: Accéder à /message/chatroom/1
Résultat: ❌ Redirection vers /login
Message: "Vous devez être connecté pour accéder au chatroom."
```

### Scénario 2: Utilisateur Non Membre
```
Action: Utilisateur connecté accède à /message/chatroom/1 (sans être membre)
Résultat: ❌ Redirection vers /goals
Message: "Vous devez rejoindre ce goal pour accéder au chatroom."
```

### Scénario 3: Utilisateur en Attente d'Approbation
```
Action: Utilisateur membre (STATUS_PENDING) accède à /message/chatroom/1
Résultat: ❌ Redirection vers /goals
Message: "Votre demande d'accès est en attente d'approbation."
```

### Scénario 4: Utilisateur Membre Approuvé
```
Action: Utilisateur membre (STATUS_APPROVED) accède à /message/chatroom/1
Résultat: ✅ Accès au chatroom
Message: Aucun (accès autorisé)
```

### Scénario 5: Requête AJAX Non Autorisée
```
Action: Fetch messages sans être membre
Résultat: ❌ JSON { "error": "Accès refusé" }
Code HTTP: 403
```

## Routes Sécurisées

### Routes Principales
```
✅ /chatroom/{id}                      - ChatroomController::show()
✅ /message/chatroom/{goalId}          - MessageController::chatroom()
✅ /message/chatroom/{goalId}/fetch    - MessageController::fetchMessages()
✅ /message/chatroom/{goalId}/send-voice - MessageController::sendVoiceMessage()
```

### Routes de Messages (déjà sécurisées)
```
✅ /message/{id}/delete                - MessageController::delete()
✅ /message/{id}/edit                  - MessageController::edit()
✅ /message/{id}/pin                   - MessageController::pin()
✅ /message/{id}/unpin                 - MessageController::unpin()
✅ /message/{id}/react/{type}          - MessageController::react()
```

## Avantages

1. **Sécurité renforcée** - Accès strictement contrôlé
2. **Logique métier correcte** - Respect du workflow d'approbation
3. **Messages clairs** - L'utilisateur sait pourquoi l'accès est refusé
4. **Codes HTTP appropriés** - 401, 403, 404 selon le cas
5. **AJAX compatible** - Gestion des requêtes asynchrones
6. **Expérience utilisateur** - Redirections et messages appropriés

## Améliorations Supplémentaires Possibles

### 1. Rate Limiting
- Limiter le nombre de requêtes par utilisateur
- Prévenir les abus et le spam

### 2. Logging
- Logger les tentatives d'accès non autorisées
- Détecter les comportements suspects

### 3. Permissions Granulaires
- Différencier les permissions par rôle (OWNER, ADMIN, MEMBER)
- Certaines actions réservées aux admins

### 4. Bannissement
- Possibilité de bannir un utilisateur d'un goal
- Statut BANNED dans GoalParticipation

## Fichiers Modifiés

1. **src/Controller/ChatroomController.php**
   - Ajout de 3 niveaux de vérification
   - Messages d'erreur appropriés
   - Redirections vers login ou goal_list

2. **src/Controller/MessageController.php**
   - Méthode `chatroom()` - Vérifications complètes
   - Méthode `fetchMessages()` - Vérifications AJAX
   - Méthode `sendVoiceMessage()` - Vérifications AJAX
   - Suppression de la vue en lecture seule

## Commandes de Test

```bash
# Nettoyer le cache
php bin/console cache:clear

# Vérifier les routes
php bin/console debug:router | findstr /i "chatroom"

# Vérifier les diagnostics
php bin/console debug:router message_chatroom
```

## Résultat Final

✅ Accès au chatroom strictement contrôlé
✅ Authentification obligatoire
✅ Membership vérifié
✅ Statut d'approbation vérifié
✅ Messages d'erreur clairs
✅ Codes HTTP appropriés
✅ Compatible AJAX
✅ Sécurité renforcée
