# 🔗 Guide de Redirection des Notifications

## Fonctionnalité Ajoutée

Lorsqu'un utilisateur clique sur une notification, il est maintenant automatiquement redirigé vers la page de détail de la session concernée.

## Avant vs Après

### ❌ Avant
```
Utilisateur clique sur notification
    ↓
Notification marquée comme lue
    ↓
Dropdown se rafraîchit
    ↓
Utilisateur reste sur la même page
```

### ✅ Après
```
Utilisateur clique sur notification
    ↓
Notification marquée comme lue
    ↓
Redirection automatique vers /sessions/{id}
    ↓
Page de détail de la session s'affiche
```

## Exemple Visuel

### Notification dans le Dropdown
```
┌─────────────────────────────────────────┐
│  🔔 Notifications                       │
├─────────────────────────────────────────┤
│  ✅  Bonne nouvelle ! fouta fouta a     │
│      accepté votre demande de coaching. │
│      🕐 Il y a 2h → Voir la session     │ ← Indicateur cliquable
├─────────────────────────────────────────┤
│  ⏰  fouta fouta a mis votre demande    │
│      en attente.                        │
│      🕐 Il y a 5h → Voir la session     │
└─────────────────────────────────────────┘
```

### Après le Clic
```
Clic sur la notification
    ↓
Console: 🖱️ Clic sur notification: 1 Session: 6
    ↓
Console: ✔️ Marquage notification comme lue: 1
    ↓
Console: ✅ Notification marquée: {success: true}
    ↓
Console: 🔗 Redirection vers la session: 6
    ↓
Navigateur: Redirection vers /sessions/6
    ↓
Page de session affichée avec tous les détails
```

## Données Retournées par l'API

### Endpoint: `/notifications/unread`

**Avant** :
```json
{
  "notifications": [
    {
      "id": 1,
      "type": "request_accepted",
      "message": "Bonne nouvelle ! fouta fouta a accepté votre demande de coaching.",
      "createdAt": "2026-02-16 00:09:21",
      "isRead": false
    }
  ]
}
```

**Après** :
```json
{
  "notifications": [
    {
      "id": 1,
      "type": "request_accepted",
      "message": "Bonne nouvelle ! fouta fouta a accepté votre demande de coaching.",
      "createdAt": "2026-02-16 00:09:21",
      "isRead": false,
      "sessionId": 6  ← NOUVEAU CHAMP
    }
  ]
}
```

## Code Modifié

### 1. Contrôleur PHP (NotificationController.php)

```php
// Récupérer l'ID de la session si elle existe
$coachingRequest = $notification->getCoachingRequest();
$sessionId = null;

if ($coachingRequest && $coachingRequest->getSession()) {
    $sessionId = $coachingRequest->getSession()->getId();
}

return [
    'id' => $notification->getId(),
    'type' => $notification->getType(),
    'message' => $notification->getMessage(),
    'createdAt' => $createdAt ? $createdAt->format('Y-m-d H:i:s') : null,
    'isRead' => $notification->isRead(),
    'sessionId' => $sessionId, // ← Nouveau champ
];
```

### 2. JavaScript (base.html.twig)

```javascript
// Affichage de l'indicateur dans le HTML
${notif.sessionId ? '<i class="bi bi-arrow-right ml-2"></i> Voir la session' : ''}

// Fonction de redirection
async function markAsReadAndRedirect(id, sessionId) {
    // Marquer comme lue
    const res = await fetch(`/notifications/${id}/mark-read`, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json'
        }
    });
    
    // Rediriger vers la session
    if (sessionId) {
        console.log('🔗 Redirection vers la session:', sessionId);
        window.location.href = `/sessions/${sessionId}`;
    } else {
        // Pas de session, juste rafraîchir
        loadNotificationCount();
        loadNotifications();
    }
}
```

## Relations Base de Données

```
Notification
    ↓ (ManyToOne)
CoachingRequest
    ↓ (OneToOne)
Session
```

### Requête SQL Équivalente
```sql
SELECT 
    n.id as notification_id,
    n.message,
    s.id as session_id
FROM notifications n
LEFT JOIN coaching_request cr ON n.coaching_request_id = cr.id
LEFT JOIN session s ON cr.id = s.coaching_request_id
WHERE n.user_id = :userId
  AND n.is_read = false
ORDER BY n.created_at DESC
```

## Types de Notifications et Redirections

| Type de Notification | Icône | Couleur | Redirection |
|---------------------|-------|---------|-------------|
| `request_accepted` | ✅ | Vert | → Session |
| `request_declined` | ❌ | Rouge | → Session |
| `request_pending` | ⏰ | Jaune | → Session |
| `session_scheduled` | 📅 | Bleu | → Session |
| `session_confirmed` | ✔️ | Violet | → Session |

## Cas Particuliers

### Notification sans Session
Si une notification n'a pas de session associée (cas rare) :
- L'indicateur "→ Voir la session" n'est pas affiché
- Le clic marque juste la notification comme lue
- Pas de redirection

### Session Supprimée
Si la session a été supprimée après la création de la notification :
- `sessionId` sera `null`
- Comportement identique au cas "sans session"

## Test Manuel

1. **Ouvrir le navigateur** et se connecter
2. **Ouvrir la console** (F12)
3. **Cliquer sur l'icône 🔔**
4. **Observer** :
   - Les notifications s'affichent
   - L'indicateur "→ Voir la session" est visible
5. **Cliquer sur une notification**
6. **Vérifier dans la console** :
   ```
   🖱️ Clic sur notification: 1 Session: 6
   ✔️ Marquage notification comme lue: 1
   ✅ Notification marquée: {success: true}
   🔗 Redirection vers la session: 6
   ```
7. **Vérifier** que la page de session s'affiche

## Avantages

✅ **Expérience utilisateur améliorée** - Un seul clic pour accéder à la session
✅ **Navigation intuitive** - L'utilisateur sait où il va grâce à l'indicateur
✅ **Gain de temps** - Plus besoin de chercher la session manuellement
✅ **Feedback visuel** - Logs de débogage pour suivre le processus
✅ **Robuste** - Gestion des cas où la session n'existe pas

---

**Date** : 17 février 2026
**Version** : 2.0
**Status** : ✅ Fonctionnel
