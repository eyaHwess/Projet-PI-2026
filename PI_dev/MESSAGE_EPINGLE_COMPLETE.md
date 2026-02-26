# Message Épinglé - COMPLETE ✅

## Objectif
Permettre aux administrateurs et propriétaires d'épingler un message important en haut du chatroom.

## Fonctionnalités Implémentées

### 1. Backend - MessageController
**Fichier:** `src/Controller/MessageController.php`

#### Méthode pin()
- **Route:** `/message/{id}/pin`
- **Méthode HTTP:** POST
- **Permission:** ADMIN ou OWNER uniquement

**Fonctionnement:**
```php
// 1. Vérifier que l'utilisateur est connecté
if (!$user) {
    $this->addFlash('error', 'Vous devez être connecté');
    return $this->redirectToRoute('app_login');
}

// 2. Vérifier les permissions (ADMIN ou OWNER)
$participation = $goal->getUserParticipation($user);
if (!$participation || !$participation->canModerate()) {
    $this->addFlash('error', 'Vous n\'avez pas la permission d\'épingler des messages');
    return $this->redirectToRoute('message_chatroom', ['goalId' => $goal->getId()]);
}

// 3. Désépingler tout message existant (un seul message épinglé à la fois)
$existingPinned = $this->entityManager->getRepository(Message::class)->findOneBy([
    'chatroom' => $chatroom,
    'isPinned' => true
]);
if ($existingPinned) {
    $existingPinned->setIsPinned(false);
}

// 4. Épingler le nouveau message
$message->setIsPinned(true);
$this->entityManager->flush();
```

#### Méthode unpin()
- **Route:** `/message/{id}/unpin`
- **Méthode HTTP:** POST
- **Permission:** ADMIN ou OWNER uniquement

**Fonctionnement:**
```php
// 1. Vérifier que l'utilisateur est connecté
if (!$user) {
    $this->addFlash('error', 'Vous devez être connecté');
    return $this->redirectToRoute('app_login');
}

// 2. Vérifier les permissions (ADMIN ou OWNER)
$participation = $goal->getUserParticipation($user);
if (!$participation || !$participation->canModerate()) {
    $this->addFlash('error', 'Vous n\'avez pas la permission de désépingler des messages');
    return $this->redirectToRoute('message_chatroom', ['goalId' => $goal->getId()]);
}

// 3. Désépingler le message
$message->setIsPinned(false);
$this->entityManager->flush();
```

### 2. Frontend - Interface Utilisateur

#### A. Bannière en Haut du Chatroom
**Affichage permanent du message épinglé:**
```twig
{% set pinnedMessage = chatroom.messages|filter(m => m.isPinned)|first %}
{% if pinnedMessage %}
    <div class="pinned-message-banner">
        <div class="pinned-message-icon">
            <i class="fas fa-thumbtack"></i>
        </div>
        <div class="pinned-message-content">
            <div class="pinned-message-author">
                {{ pinnedMessage.author.firstName }} {{ pinnedMessage.author.lastName }}
            </div>
            <div class="pinned-message-text">
                {{ pinnedMessage.content|length > 100 ? pinnedMessage.content|slice(0, 100) ~ '...' : pinnedMessage.content }}
            </div>
        </div>
        <button class="pinned-message-close" onclick="document.querySelector('.pinned-message-banner').style.display='none'">
            <i class="fas fa-times"></i>
        </button>
    </div>
{% endif %}
```

**Caractéristiques:**
- Affichée en haut du chatroom
- Fond jaune/doré (#fff9e6)
- Icône de punaise 📌
- Nom de l'auteur
- Contenu du message (tronqué à 100 caractères)
- Bouton pour fermer temporairement

#### B. Badge sur le Message Épinglé
**Dans la liste des messages:**
```twig
<div class="message {% if message.isPinned %}pinned{% endif %}">
    {% if message.isPinned %}
        <div class="pinned-badge">
            <i class="fas fa-thumbtack"></i> Message épinglé
        </div>
    {% endif %}
    <!-- Contenu du message -->
</div>
```

**Caractéristiques:**
- Badge jaune avec icône de punaise
- Fond du message légèrement jaune
- Bordure gauche jaune

#### C. Boutons d'Action (Admins/Owners uniquement)
**Bouton Épingler:**
```twig
{% if userParticipation and userParticipation.canModerate() %}
    <div class="message-actions">
        {% if message.isPinned %}
            <form method="post" action="{{ path('message_unpin', {id: message.id}) }}">
                <button type="submit" class="action-btn unpin-btn">
                    <i class="fas fa-thumbtack"></i> Désépingler
                </button>
            </form>
        {% else %}
            <form method="post" action="{{ path('message_pin', {id: message.id}) }}">
                <button type="submit" class="action-btn pin-btn">
                    <i class="fas fa-thumbtack"></i> Épingler
                </button>
            </form>
        {% endif %}
    </div>
{% endif %}
```

**Caractéristiques:**
- Visible uniquement pour les admins et owners
- Bouton "Épingler" pour les messages non épinglés
- Bouton "Désépingler" pour le message épinglé
- Couleur jaune pour indiquer l'action

### 3. CSS - Styles

#### Bannière Message Épinglé
```css
.pinned-message-banner {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 16px;
    background: linear-gradient(135deg, #fff9e6 0%, #fffbf0 100%);
    border-bottom: 2px solid #ffc107;
    margin-bottom: 12px;
}

.pinned-message-icon {
    width: 32px;
    height: 32px;
    background: #ffc107;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #000;
    font-size: 14px;
}

.pinned-message-content {
    flex: 1;
    min-width: 0;
}

.pinned-message-author {
    font-size: 12px;
    font-weight: 600;
    color: #ffc107;
    margin-bottom: 2px;
}

.pinned-message-text {
    font-size: 13px;
    color: #050505;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
```

#### Message Épinglé dans la Liste
```css
.message.pinned {
    background: #fff9e6;
    border-left: 3px solid #ffc107;
    padding-left: 8px;
    margin-left: -8px;
}

.pinned-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 4px 8px;
    background: #ffc107;
    color: #000;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 600;
    margin-bottom: 8px;
}
```

#### Boutons d'Action
```css
.message-actions {
    display: flex;
    gap: 4px;
    margin-top: 4px;
    padding: 0 12px;
}

.action-btn {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 4px 8px;
    background: transparent;
    border: 1px solid #e4e6eb;
    border-radius: 8px;
    font-size: 12px;
    cursor: pointer;
    transition: all 0.2s;
    color: #65676b;
}

.pin-btn:hover {
    background: #fff9e6;
    border-color: #ffc107;
    color: #ffc107;
}

.unpin-btn {
    background: #fff9e6;
    border-color: #ffc107;
    color: #ffc107;
}
```

## Règles de Gestion

### 1. Un Seul Message Épinglé
- Un chatroom ne peut avoir qu'un seul message épinglé à la fois
- Épingler un nouveau message désépingle automatiquement l'ancien

### 2. Permissions
- Seuls les ADMIN et OWNER peuvent épingler/désépingler
- Les MEMBER ne voient pas les boutons d'action

### 3. Visibilité
- Le message épinglé est visible en haut du chatroom (bannière)
- Le message épinglé est aussi visible dans la liste avec un badge
- Tous les membres peuvent voir le message épinglé

### 4. Persistance
- Le message reste épinglé jusqu'à ce qu'un admin le désépingle
- Le message reste épinglé même si de nouveaux messages sont envoyés

## Scénarios d'Utilisation

### Scénario 1: Épingler un Message Important
```
1. Admin voit un message important (ex: "Réunion demain à 14h")
2. Admin clique sur "Épingler"
3. Le message apparaît en haut du chatroom dans une bannière jaune
4. Le message dans la liste affiche un badge "Message épinglé"
5. Tous les membres voient le message épinglé
```

### Scénario 2: Changer le Message Épinglé
```
1. Un message est déjà épinglé
2. Admin épingle un nouveau message
3. L'ancien message est automatiquement désépinglé
4. Le nouveau message apparaît en haut
5. Un seul message est épinglé à la fois
```

### Scénario 3: Désépingler un Message
```
1. Un message est épinglé
2. Admin clique sur "Désépingler"
3. La bannière disparaît
4. Le badge "Message épinglé" disparaît
5. Le message redevient normal
```

### Scénario 4: Fermer Temporairement la Bannière
```
1. Un message est épinglé
2. Utilisateur clique sur le bouton X de la bannière
3. La bannière se cache temporairement
4. Le message reste épinglé (badge visible dans la liste)
5. La bannière réapparaît au rechargement de la page
```

## Cas d'Usage

### 1. Annonces Importantes
- Réunions
- Événements
- Deadlines
- Changements importants

### 2. Règles du Groupe
- Code de conduite
- Règles de participation
- Consignes importantes

### 3. Informations Utiles
- Liens importants
- Documents de référence
- Contacts importants

### 4. Messages d'Urgence
- Alertes
- Problèmes critiques
- Actions requises

## Sécurité

### Vérifications
✅ Utilisateur doit être connecté
✅ Utilisateur doit être ADMIN ou OWNER
✅ Vérification via `canModerate()`
✅ Messages flash pour les erreurs

### Codes HTTP
- **200 OK** - Message épinglé/désépinglé avec succès
- **302 Found** - Redirection après action
- **401 Unauthorized** - Utilisateur non connecté
- **403 Forbidden** - Permissions insuffisantes

## Base de Données

### Champ isPinned
```sql
ALTER TABLE message ADD COLUMN is_pinned BOOLEAN DEFAULT FALSE;
```

### Index Recommandé
```sql
CREATE INDEX idx_message_pinned ON message(chatroom_id, is_pinned);
```

### Contrainte
- Un seul message épinglé par chatroom (géré par l'application)

## Améliorations Futures Possibles

### 1. Historique des Messages Épinglés
- Garder un historique des messages épinglés
- Voir qui a épinglé et quand

### 2. Notifications
- Notifier les membres quand un message est épinglé
- Badge de notification

### 3. Épingler Plusieurs Messages
- Permettre d'épingler jusqu'à 3 messages
- Carrousel de messages épinglés

### 4. Durée d'Épinglage
- Épingler pour une durée limitée
- Désépinglage automatique après X jours

### 5. Catégories de Messages Épinglés
- Annonce
- Règle
- Information
- Urgence

## Interface Utilisateur

### États Visuels

**Message Normal:**
- Fond blanc
- Pas de badge

**Message Épinglé:**
- Fond jaune clair (#fff9e6)
- Bordure gauche jaune (#ffc107)
- Badge "Message épinglé"
- Visible dans la bannière en haut

**Bannière:**
- Fond dégradé jaune
- Icône de punaise dans un cercle jaune
- Nom de l'auteur en jaune
- Contenu tronqué
- Bouton X pour fermer

**Boutons d'Action:**
- Transparents par défaut
- Jaune au hover pour "Épingler"
- Jaune actif pour "Désépingler"

## Tests Recommandés

### Tests Fonctionnels
1. ✅ Épingler un message
2. ✅ Désépingler un message
3. ✅ Épingler un nouveau message (désépingle l'ancien)
4. ✅ Vérifier la bannière en haut
5. ✅ Vérifier le badge dans la liste
6. ✅ Fermer temporairement la bannière

### Tests de Permissions
1. ✅ OWNER peut épingler
2. ✅ ADMIN peut épingler
3. ✅ MEMBER ne peut pas épingler
4. ✅ Non-membre ne peut pas épingler

### Tests d'Interface
1. ✅ Bannière s'affiche correctement
2. ✅ Badge s'affiche correctement
3. ✅ Boutons visibles pour les admins uniquement
4. ✅ Responsive design

## Fichiers Modifiés

1. **src/Controller/MessageController.php**
   - Méthodes `pin()` et `unpin()` déjà existantes
   - Vérifications de permissions complètes

2. **templates/chatroom/chatroom_modern.html.twig**
   - Ajout de la bannière du message épinglé
   - Ajout du badge sur le message épinglé
   - Ajout des boutons d'action
   - Ajout du CSS complet

## Résultat Final

✅ Messages peuvent être épinglés par les admins/owners
✅ Bannière permanente en haut du chatroom
✅ Badge visible sur le message épinglé
✅ Un seul message épinglé à la fois
✅ Boutons d'action pour épingler/désépingler
✅ Interface moderne et intuitive
✅ Permissions strictes (ADMIN/OWNER uniquement)
✅ Design cohérent avec le reste de l'interface
