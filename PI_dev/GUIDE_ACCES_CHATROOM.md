# Guide d'Accès au Chatroom 💬

## Routes Disponibles

### 1. Liste des Goals
**URL:** `http://127.0.0.1:8000/goals`
**Route:** `goal_list`

C'est la page principale où vous voyez tous les goals disponibles.

### 2. Créer un Goal
**URL:** `http://127.0.0.1:8000/goal/new`
**Route:** `goal_new`

Créez un nouveau goal. Un chatroom sera automatiquement créé avec le goal.

### 3. Accéder au Chatroom d'un Goal
**URL:** `http://127.0.0.1:8000/goal/{id}/messages`
**Route:** `goal_messages`

Remplacez `{id}` par l'ID du goal.

**Exemples:**
- `http://127.0.0.1:8000/goal/1/messages` - Chatroom du goal #1
- `http://127.0.0.1:8000/goal/2/messages` - Chatroom du goal #2
- `http://127.0.0.1:8000/goal/3/messages` - Chatroom du goal #3

## Étapes pour Accéder au Chatroom

### Option 1: Via la Liste des Goals
1. Allez sur `http://127.0.0.1:8000/goals`
2. Cliquez sur un goal dans la liste
3. Vous serez redirigé vers le chatroom de ce goal

### Option 2: Créer un Nouveau Goal
1. Allez sur `http://127.0.0.1:8000/goal/new`
2. Remplissez le formulaire:
   - Titre du goal
   - Description
   - Date de début
   - Date de fin
   - Statut
3. Soumettez le formulaire
4. Un chatroom sera automatiquement créé
5. Vous serez automatiquement membre du goal
6. Accédez au chatroom via `http://127.0.0.1:8000/goal/{id}/messages`

### Option 3: Rejoindre un Goal Existant
1. Allez sur `http://127.0.0.1:8000/goals`
2. Cliquez sur "Join" pour un goal
3. Attendez l'approbation d'un administrateur
4. Une fois approuvé, accédez au chatroom

## Système de Permissions

### Statuts de Participation
- **PENDING** 🕐 - Demande en attente d'approbation
- **APPROVED** ✅ - Accès autorisé au chatroom
- **REJECTED** ❌ - Demande refusée

### Rôles
- **OWNER** 👑 - Créateur du goal (tous les droits)
- **ADMIN** 🛡️ - Administrateur (peut modérer)
- **MEMBER** 👤 - Membre simple

### Accès au Chatroom
- ✅ Membres APPROVED peuvent voir et envoyer des messages
- ⏳ Membres PENDING peuvent voir mais pas envoyer
- ❌ Non-membres ne peuvent pas accéder

## Vérifier les Goals Existants

Pour voir quels goals existent dans votre base de données:

```bash
php bin/console doctrine:query:sql "SELECT id, title FROM goal"
```

## Créer un Goal de Test

Si vous n'avez pas de goals, créez-en un:

1. Connectez-vous avec: `mariemayari@gmail.com` / `mariem`
2. Allez sur `http://127.0.0.1:8000/goal/new`
3. Créez un goal de test
4. Accédez au chatroom via `http://127.0.0.1:8000/goal/1/messages`

## Routes Complètes du Chatroom

```
goal_list              /goals                           - Liste des goals
goal_new               /goal/new                        - Créer un goal
goal_show              /goal/{id}                       - Détails du goal
goal_messages          /goal/{id}/messages              - Chatroom (page principale)
goal_messages_fetch    /goal/{id}/messages/fetch        - Récupérer nouveaux messages (AJAX)
goal_send_voice        /goal/{id}/send-voice            - Envoyer message vocal
goal_join              /goal/{id}/join                  - Rejoindre le goal
goal_leave             /goal/{id}/leave                 - Quitter le goal
goal_approve_request   /goal/{goalId}/approve-request/{userId}  - Approuver demande
goal_reject_request    /goal/{goalId}/reject-request/{userId}   - Refuser demande
```

## Routes des Messages

```
message_delete         /message/{id}/delete             - Supprimer message
message_delete_for_me  /message/{id}/delete-for-me      - Supprimer pour moi
message_edit           /message/{id}/edit               - Modifier message
message_react          /message/{id}/react/{type}       - Réagir au message
message_pin            /message/{id}/pin                - Épingler message
message_unpin          /message/{id}/unpin              - Désépingler message
```

## Erreurs Courantes

### "Not Found" sur /chatroom
❌ La route `/chatroom` n'existe pas
✅ Utilisez `/goal/{id}/messages` à la place

### "Access Denied"
- Vérifiez que vous êtes connecté
- Vérifiez que vous êtes membre APPROVED du goal

### "Chatroom introuvable"
- Le goal n'a pas de chatroom
- Créez un nouveau goal (chatroom créé automatiquement)

## Exemple Complet

1. **Démarrer le serveur:**
   ```bash
   symfony server:start
   ```

2. **Accéder à l'application:**
   ```
   http://127.0.0.1:8000
   ```

3. **Se connecter:**
   - Email: `mariemayari@gmail.com`
   - Password: `mariem`

4. **Aller à la liste des goals:**
   ```
   http://127.0.0.1:8000/goals
   ```

5. **Créer ou rejoindre un goal**

6. **Accéder au chatroom:**
   ```
   http://127.0.0.1:8000/goal/1/messages
   ```

Voilà! Vous êtes maintenant dans le chatroom! 🎉
