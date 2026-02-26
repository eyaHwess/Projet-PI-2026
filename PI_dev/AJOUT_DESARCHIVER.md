# ✅ Ajout de la Fonction "Désarchiver"

## 🎯 Objectif
Permettre aux admins/modérateurs de désarchiver un chatroom archivé pour le rendre à nouveau actif.

## ✅ Modifications Effectuées

### 1. Workflow Configuration (`config/packages/workflow.yaml`)

Ajout de la transition `unarchive`:

```yaml
transitions:
    lock:
        from: active
        to: locked
    unlock:
        from: locked
        to: active
    archive:
        from: [active, locked]
        to: archived
    unarchive:              # ← NOUVEAU
        from: archived
        to: active
    delete:
        from: [active, locked, archived]
        to: deleted
    restore:
        from: deleted
        to: active
```

### 2. Contrôleur (`src/Controller/ChatroomStateController.php`)

Ajout de la méthode `unarchive()`:

```php
#[Route('/{id}/unarchive', name: 'chatroom_unarchive', methods: ['POST'])]
public function unarchive(Chatroom $chatroom, WorkflowInterface $chatroomStateMachine): Response
{
    $user = $this->getUser();
    
    if (!$user) {
        $this->addFlash('error', 'Vous devez être connecté');
        return $this->redirectToRoute('app_login');
    }

    $goal = $chatroom->getGoal();
    $participation = $goal->getUserParticipation($user);

    if (!$participation || !$participation->canModerate()) {
        $this->addFlash('error', 'Vous n\'avez pas la permission de désarchiver ce chatroom');
        return $this->redirectToRoute('message_chatroom', ['goalId' => $goal->getId()]);
    }

    if (!$chatroomStateMachine->can($chatroom, 'unarchive')) {
        $this->addFlash('error', 'Impossible de désarchiver ce chatroom dans son état actuel');
        return $this->redirectToRoute('message_chatroom', ['goalId' => $goal->getId()]);
    }

    $chatroomStateMachine->apply($chatroom, 'unarchive');
    $this->entityManager->flush();

    $this->addFlash('success', '🟢 Chatroom désarchivé. Le chatroom est à nouveau actif.');
    return $this->redirectToRoute('message_chatroom', ['goalId' => $goal->getId()]);
}
```

### 3. Template (`templates/chatroom/chatroom_modern.html.twig`)

Ajout du bouton "Désarchiver" dans l'interface:

```twig
{% elseif chatroom.state == 'archived' %}
    <form method="post" action="{{ path('chatroom_unarchive', {id: chatroom.id}) }}" style="display: inline;">
        <button type="submit" class="workflow-btn workflow-btn-unlock" title="Désarchiver le chatroom">
            <i class="fas fa-box-open"></i> Désarchiver
        </button>
    </form>
{% endif %}
```

## 🎨 Interface Utilisateur

### État Archivé
Quand un chatroom est archivé, l'interface affiche:
- 📦 Badge gris "Archivé"
- 🔓 Bouton "Désarchiver" (admins/modérateurs)
- 🗑️ Bouton "Supprimer" (propriétaire uniquement)
- Bannière: "Ce chatroom est archivé. Lecture seule."
- Zone de saisie désactivée

### Après Désarchivage
- Badge "Archivé" disparaît
- Chatroom redevient actif
- Zone de saisie réactivée
- Tous les membres peuvent à nouveau envoyer des messages
- Message de succès: "🟢 Chatroom désarchivé. Le chatroom est à nouveau actif."

## 🔄 Diagramme des Transitions

```
┌─────────┐
│ ACTIVE  │ ◄──────────────────┐
└────┬────┘                    │
     │                         │
     │ lock              unarchive
     ▼                         │
┌─────────┐                    │
│ LOCKED  │                    │
└────┬────┘                    │
     │                         │
     │ archive                 │
     ▼                         │
┌─────────┐                    │
│ARCHIVED │────────────────────┘
└────┬────┘
     │
     │ delete
     ▼
┌─────────┐
│ DELETED │
└─────────┘
```

## 🔐 Permissions

| Action | Admin | Modérateur | Propriétaire | Membre |
|--------|-------|------------|--------------|--------|
| Archiver | ✅ | ✅ | ✅ | ❌ |
| Désarchiver | ✅ | ✅ | ✅ | ❌ |
| Supprimer | ❌ | ❌ | ✅ | ❌ |

## 🧪 Test

### 1. Archiver un Chatroom
1. Ouvrir un chatroom actif
2. Cliquer sur "Archiver"
3. Confirmer l'action
4. Vérifier:
   - Badge "Archivé" apparaît
   - Zone de saisie désactivée
   - Bouton "Désarchiver" visible

### 2. Désarchiver un Chatroom
1. Ouvrir un chatroom archivé
2. Cliquer sur "Désarchiver"
3. Vérifier:
   - Badge "Archivé" disparaît
   - Zone de saisie réactivée
   - Message de succès affiché
   - Chatroom redevient actif

### 3. Vérifier les Permissions
1. Se connecter en tant que membre simple
2. Ouvrir un chatroom archivé
3. Vérifier que le bouton "Désarchiver" n'est PAS visible

## 📁 Fichiers Modifiés

1. `config/packages/workflow.yaml` - Ajout transition `unarchive`
2. `src/Controller/ChatroomStateController.php` - Ajout méthode `unarchive()`
3. `templates/chatroom/chatroom_modern.html.twig` - Ajout bouton "Désarchiver"

## 🎉 Résultat Final

### Routes Disponibles
```
✅ chatroom_lock        POST  /chatroom/{id}/lock
✅ chatroom_unlock      POST  /chatroom/{id}/unlock
✅ chatroom_archive     POST  /chatroom/{id}/archive
✅ chatroom_unarchive   POST  /chatroom/{id}/unarchive    ← NOUVEAU
✅ chatroom_delete      POST  /chatroom/{id}/delete
✅ chatroom_restore     POST  /chatroom/{id}/restore
```

### Transitions Workflow
```
✅ lock       : active → locked
✅ unlock     : locked → active
✅ archive    : active/locked → archived
✅ unarchive  : archived → active                          ← NOUVEAU
✅ delete     : active/locked/archived → deleted
✅ restore    : deleted → active
```

### Interface
- ✅ Bouton "Désarchiver" visible quand chatroom archivé
- ✅ Icône box-open (📦 ouvert)
- ✅ Style cohérent avec les autres boutons
- ✅ Permissions vérifiées
- ✅ Messages de confirmation

## 💡 Cas d'Usage

### Scénario 1: Challenge Temporaire
1. Un challenge se termine → Archiver le chatroom
2. Le challenge est relancé → Désarchiver le chatroom
3. Les participants peuvent à nouveau discuter

### Scénario 2: Maintenance
1. Maintenance du chatroom → Archiver temporairement
2. Maintenance terminée → Désarchiver
3. Activité reprend normalement

### Scénario 3: Modération
1. Problème de modération → Archiver pour calmer la situation
2. Situation résolue → Désarchiver
3. Discussion reprend dans de bonnes conditions

## 🚀 Avantages

- ✅ Flexibilité: Possibilité de réactiver un chatroom archivé
- ✅ Réversibilité: L'archivage n'est plus définitif
- ✅ Contrôle: Les admins gardent le contrôle total
- ✅ Historique: Toutes les données sont conservées
- ✅ UX: Interface claire et intuitive

**La fonction "Désarchiver" est maintenant opérationnelle!** 🚀
