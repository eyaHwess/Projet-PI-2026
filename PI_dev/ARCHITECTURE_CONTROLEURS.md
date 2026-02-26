# Architecture des Contrôleurs - Chatroom 📋

## Vue d'Ensemble

Le système de chatroom utilise 2 contrôleurs principaux avec des responsabilités bien définies.

## 1. GoalController 🎯

**Fichier:** `src/Controller/GoalController.php`

### Responsabilités Principales

#### A. Gestion des Goals
- ✅ Liste des goals (`/goals`)
- ✅ Création de goal (`/goal/new`)
- ✅ Affichage d'un goal (`/goal/{id}`)
- ✅ Modification de goal (`/goal/{id}/edit`)
- ✅ Suppression de goal (`/goal/{id}/delete`)

#### B. Gestion des Participations
- ✅ Rejoindre un goal (`/goal/{id}/join`)
- ✅ Quitter un goal (`/goal/{id}/leave`)
- ✅ Approuver une demande (`/goal/{goalId}/approve-request/{userId}`)
- ✅ Refuser une demande (`/goal/{goalId}/reject-request/{userId}`)
- ✅ Exclure un membre (`/goal/{goalId}/remove-member/{userId}`)
- ✅ Promouvoir un membre (`/goal/{goalId}/promote-member/{userId}`)

#### C. Chatroom et Messages
- ✅ **Afficher le chatroom** (`/goal/{id}/messages`)
- ✅ **Envoyer un message texte** (via formulaire dans `/goal/{id}/messages`)
- ✅ **Envoyer un message vocal** (`/goal/{id}/send-voice`) ⭐
- ✅ **Récupérer nouveaux messages** (`/goal/{id}/messages/fetch`)

### Routes Importantes

```php
// Chatroom
#[Route('/goal/{id}/messages', name: 'goal_messages')]
public function messages(Goal $goal, Request $request, ...): Response

// Message vocal ⭐
#[Route('/goal/{id}/send-voice', name: 'goal_send_voice', methods: ['POST'])]
public function sendVoiceMessage(Goal $goal, Request $request, ...): JsonResponse

// Fetch messages (AJAX)
#[Route('/goal/{id}/messages/fetch', name: 'goal_messages_fetch', methods: ['GET'])]
public function fetchMessages(Goal $goal, Request $request, ...): JsonResponse
```

### Méthode sendVoiceMessage() ⭐

**C'est ici que les messages vocaux sont traités!**

```php
#[Route('/goal/{id}/send-voice', name: 'goal_send_voice', methods: ['POST'])]
public function sendVoiceMessage(Goal $goal, Request $request, EntityManagerInterface $em): JsonResponse
{
    try {
        // 1. Vérifier l'authentification
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'Vous devez être connecté'], 401);
        }

        // 2. Récupérer le chatroom
        $chatroom = $goal->getChatroom();
        if (!$chatroom) {
            return new JsonResponse(['error' => 'Chatroom introuvable'], 404);
        }

        // 3. Récupérer le fichier audio
        $voiceFile = $request->files->get('voice');
        $duration = $request->request->get('duration', 0);
        
        if (!$voiceFile) {
            return new JsonResponse(['error' => 'Fichier audio manquant'], 400);
        }

        // 4. Générer un nom unique
        $newFilename = 'voice-'.uniqid().'.webm';

        // 5. Créer le dossier si nécessaire
        $uploadDir = $this->getParameter('kernel.project_dir').'/public/uploads/voice';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        // 6. Sauvegarder le fichier
        $voiceFile->move($uploadDir, $newFilename);

        // 7. Créer le message dans la base de données
        $message = new Message();
        $message->setAuthor($user);
        $message->setChatroom($chatroom);
        $message->setCreatedAt(new \DateTime());
        $message->setContent(null);  // Pas de texte pour un message vocal
        $message->setAttachmentPath('/uploads/voice/'.$newFilename);
        $message->setAttachmentType('audio');
        $message->setAttachmentOriginalName($newFilename);
        $message->setAudioDuration((int)$duration);

        // 8. Sauvegarder en base
        $em->persist($message);
        $em->flush();

        // 9. Retourner le succès
        return new JsonResponse([
            'success' => true,
            'message' => 'Message vocal envoyé!',
            'messageId' => $message->getId()
        ]);

    } catch (\Exception $e) {
        return new JsonResponse([
            'success' => false,
            'error' => $e->getMessage()
        ], 500);
    }
}
```

## 2. MessageController 💬

**Fichier:** `src/Controller/MessageController.php`

### Responsabilités Principales

#### Actions sur les Messages Existants
- ✅ **Supprimer un message** (`/message/{id}/delete`)
- ✅ **Supprimer pour moi** (`/message/{id}/delete-for-me`)
- ✅ **Modifier un message** (`/message/{id}/edit`)
- ✅ **Réagir à un message** (`/message/{id}/react/{type}`)
- ✅ **Épingler un message** (`/message/{id}/pin`)
- ✅ **Désépingler un message** (`/message/{id}/unpin`)

### Routes Importantes

```php
// Suppression
#[Route('/message/{id}/delete', name: 'message_delete', methods: ['POST'])]
public function delete(Message $message, Request $request): Response

// Édition
#[Route('/message/{id}/edit', name: 'message_edit', methods: ['POST'])]
public function edit(Message $message, Request $request): Response

// Réactions
#[Route('/message/{id}/react/{type}', name: 'message_react', methods: ['POST'])]
public function react(Message $message, string $type, ...): JsonResponse

// Épingler
#[Route('/message/{id}/pin', name: 'message_pin', methods: ['POST'])]
public function pin(Message $message): Response
```

## Flux de Données

### Envoi d'un Message Vocal

```
1. Utilisateur clique sur 🎤
   ↓
2. JavaScript enregistre l'audio
   ↓
3. JavaScript crée un Blob audio
   ↓
4. Utilisateur clique sur envoyer ✈️
   ↓
5. JavaScript envoie via AJAX:
   POST /goal/{id}/send-voice
   FormData: { voice: blob, duration: seconds }
   ↓
6. GoalController::sendVoiceMessage()
   - Vérifie l'authentification
   - Récupère le fichier
   - Sauvegarde dans /public/uploads/voice/
   - Crée le Message en base
   ↓
7. Retourne JSON: { success: true, messageId: X }
   ↓
8. JavaScript recharge la page
   ↓
9. Message vocal visible dans le chat
```

### Envoi d'un Message Texte

```
1. Utilisateur tape un message
   ↓
2. Utilisateur appuie sur Enter ou clique ✈️
   ↓
3. JavaScript envoie via AJAX:
   POST /goal/{id}/messages
   FormData: { message[content]: "texte" }
   ↓
4. GoalController::messages()
   - Traite le formulaire
   - Crée le Message en base
   ↓
5. Retourne JSON: { success: true }
   ↓
6. JavaScript recharge la page
   ↓
7. Message texte visible dans le chat
```

### Réaction à un Message

```
1. Utilisateur clique sur 👍
   ↓
2. JavaScript envoie via AJAX:
   POST /message/{id}/react/like
   ↓
3. MessageController::react()
   - Toggle la réaction
   - Met à jour les compteurs
   ↓
4. Retourne JSON: { success: true, counts: {...} }
   ↓
5. JavaScript met à jour l'affichage
```

## Pourquoi Cette Architecture?

### Séparation des Responsabilités

#### GoalController
- **Focus:** Contexte du goal et du chatroom
- **Crée:** Nouveaux messages (texte, vocal)
- **Gère:** L'accès au chatroom
- **Raison:** Les messages sont créés dans le contexte d'un goal

#### MessageController
- **Focus:** Actions sur messages existants
- **Modifie:** Messages déjà créés
- **Gère:** Interactions avec les messages
- **Raison:** Actions indépendantes du goal

### Avantages

1. **Clarté du Code**
   - Facile de savoir où chercher
   - Responsabilités bien définies

2. **Maintenance**
   - Modifications isolées
   - Moins de conflits

3. **Évolutivité**
   - Facile d'ajouter de nouvelles fonctionnalités
   - Chaque contrôleur reste gérable

4. **Testabilité**
   - Tests unitaires plus simples
   - Mocking plus facile

## Résumé Visuel

```
┌─────────────────────────────────────────────────────────┐
│                    GoalController                        │
├─────────────────────────────────────────────────────────┤
│ • Liste des goals                                        │
│ • Créer/Modifier/Supprimer goal                         │
│ • Rejoindre/Quitter goal                                │
│ • Gérer les participations                              │
│ • Afficher le chatroom                                  │
│ • ⭐ ENVOYER MESSAGE TEXTE                              │
│ • ⭐ ENVOYER MESSAGE VOCAL                              │
│ • Récupérer nouveaux messages                           │
└─────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────┐
│                  MessageController                       │
├─────────────────────────────────────────────────────────┤
│ • Supprimer un message                                  │
│ • Modifier un message                                   │
│ • Réagir à un message (👍👏🔥❤️)                        │
│ • Épingler/Désépingler                                  │
│ • Supprimer pour moi                                    │
└─────────────────────────────────────────────────────────┘
```

## Fichiers JavaScript

### public/chatroom_dynamic.js

**Responsabilités:**
- Enregistrement audio
- Prévisualisation
- Envoi AJAX des messages vocaux
- Envoi AJAX des messages texte
- Gestion des emojis
- Gestion des fichiers
- Auto-scroll
- Auto-resize textarea

**Appelle:**
- `POST /goal/{id}/send-voice` (GoalController)
- `POST /goal/{id}/messages` (GoalController)

## Routes Complètes

### GoalController
```
GET    /goals                                    - goal_list
POST   /goal/new                                 - goal_new
GET    /goal/{id}                                - goal_show
GET    /goal/{id}/messages                       - goal_messages ⭐
POST   /goal/{id}/messages                       - goal_messages (envoi texte)
POST   /goal/{id}/send-voice                     - goal_send_voice ⭐
GET    /goal/{id}/messages/fetch                 - goal_messages_fetch
POST   /goal/{id}/join                           - goal_join
POST   /goal/{id}/leave                          - goal_leave
POST   /goal/{id}/delete                         - goal_delete
GET    /goal/{id}/edit                           - goal_edit
POST   /goal/{goalId}/approve-request/{userId}   - goal_approve_request
POST   /goal/{goalId}/reject-request/{userId}    - goal_reject_request
POST   /goal/{goalId}/remove-member/{userId}     - goal_remove_member
POST   /goal/{goalId}/promote-member/{userId}    - goal_promote_member
```

### MessageController
```
POST   /message/{id}/delete                      - message_delete
POST   /message/{id}/delete-for-me               - message_delete_for_me
POST   /message/{id}/edit                        - message_edit
POST   /message/{id}/react/{type}                - message_react
POST   /message/{id}/pin                         - message_pin
POST   /message/{id}/unpin                       - message_unpin
```

## Commandes Utiles

### Voir toutes les routes
```bash
php bin/console debug:router
```

### Voir les routes d'un contrôleur
```bash
php bin/console debug:router | findstr goal
php bin/console debug:router | findstr message
```

### Tester une route
```bash
# Message vocal
curl -X POST http://127.0.0.1:8000/goal/1/send-voice \
  -F "voice=@test.webm" \
  -F "duration=10"

# Réaction
curl -X POST http://127.0.0.1:8000/message/1/react/like
```

## Conclusion

**Les messages vocaux fonctionnent dans GoalController** parce que:
1. Ils créent de nouveaux messages
2. Ils sont liés au contexte du goal/chatroom
3. Ils nécessitent l'accès au chatroom

**MessageController gère les actions sur messages existants** parce que:
1. Ils modifient des messages déjà créés
2. Ils sont indépendants du contexte du goal
3. Ils peuvent être appelés depuis n'importe où

Cette architecture est claire, maintenable et évolutive! ✅
