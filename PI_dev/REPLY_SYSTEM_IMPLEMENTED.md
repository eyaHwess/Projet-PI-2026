# 💬 Reply System - Message Replies

## Status: ✅ COMPLETED

Le système de réponses aux messages a été implémenté avec succès, permettant une structure hiérarchique de conversation.

## Fonctionnalités Implémentées

### 1. Bouton Répondre
- **Bouton vert** avec icône de réponse (↩️)
- Apparaît au survol de chaque message
- Disponible pour les messages envoyés et reçus
- Position: à gauche des boutons modifier/supprimer

### 2. Prévisualisation de Réponse
- **Zone de prévisualisation** au-dessus du champ de saisie
- Affiche l'auteur du message original
- Affiche un aperçu du contenu (50 caractères max)
- Bouton X pour annuler la réponse
- Animation de glissement vers le bas

### 3. Référence au Message Original
- **Bloc de référence** dans le message de réponse
- Bordure gauche bleue (#8b9dc3)
- Fond gris clair avec icône de réponse
- Affiche l'auteur et le contenu du message original
- Tronqué à 50 caractères si trop long

### 4. Structure Hiérarchique
- Relation parent-enfant dans la base de données
- Un message peut avoir plusieurs réponses
- Les réponses référencent le message original
- Suppression en cascade gérée (SET NULL)

## Implémentation Technique

### Base de Données

#### Migration: Version20260216202911.php
```sql
ALTER TABLE message ADD reply_to_id INT DEFAULT NULL;
ALTER TABLE message ADD CONSTRAINT FK_B6BD307FFFDF7169 
    FOREIGN KEY (reply_to_id) REFERENCES message (id) 
    ON DELETE SET NULL;
CREATE INDEX IDX_B6BD307FFFDF7169 ON message (reply_to_id);
```

#### Entity: Message.php
```php
#[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'replies')]
#[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
private ?Message $replyTo = null;

#[ORM\OneToMany(targetEntity: self::class, mappedBy: 'replyTo')]
private Collection $replies;

public function isReply(): bool
{
    return $this->replyTo !== null;
}
```

### Backend (Controller)

#### Gestion des Réponses
```php
// Handle reply to another message
$replyToId = $request->request->get('reply_to');
if ($replyToId) {
    $replyToMessage = $em->getRepository(Message::class)->find($replyToId);
    if ($replyToMessage && $replyToMessage->getChatroom()->getId() === $chatroom->getId()) {
        $message->setReplyTo($replyToMessage);
    }
}
```

### Frontend (JavaScript)

#### Fonctions de Réponse
```javascript
function setReplyTo(messageId, authorName, messagePreview) {
    document.getElementById('replyToInput').value = messageId;
    document.getElementById('replyAuthor').textContent = authorName;
    document.getElementById('replyText').textContent = messagePreview;
    document.getElementById('replyPreview').style.display = 'block';
    document.querySelector('.chat-input').focus();
}

function cancelReply() {
    document.getElementById('replyToInput').value = '';
    document.getElementById('replyPreview').style.display = 'none';
}
```

### Frontend (Template)

#### Affichage de la Référence
```twig
{% if message.isReply %}
    <div class="reply-reference">
        <i class="fas fa-reply"></i>
        <div class="reply-info">
            <strong>{{ message.replyTo.author.firstName }} {{ message.replyTo.author.lastName }}</strong>
            <span class="reply-preview">{{ message.replyTo.content|slice(0, 50) }}</span>
        </div>
    </div>
{% endif %}
```

#### Zone de Prévisualisation
```html
<div id="replyPreview" style="display: none;">
    <div class="reply-preview-content">
        <i class="fas fa-reply"></i>
        <div class="reply-preview-info">
            <strong id="replyAuthor"></strong>
            <span id="replyText"></span>
        </div>
        <button type="button" class="reply-cancel-btn" onclick="cancelReply()">
            <i class="fas fa-times"></i>
        </button>
    </div>
</div>
<input type="hidden" id="replyToInput" name="reply_to" value="">
```

## Design Visuel

### Bouton Répondre
- **Couleur**: Vert (#10b981)
- **Forme**: Circulaire (28px)
- **Position**: Absolue, apparaît au survol
- **Animation**: Scale au hover (1.1x)
- **Ombre**: Box-shadow verte

### Référence dans le Message
- **Fond**: Gris clair rgba(139, 157, 195, 0.1)
- **Bordure**: Gauche 3px bleue (#8b9dc3)
- **Padding**: 8px 12px
- **Border-radius**: 8px
- **Icône**: Flèche de réponse bleue

### Prévisualisation dans l'Input
- **Fond**: Gris #f3f4f6
- **Bordure**: Gauche 3px verte (#10b981)
- **Animation**: slideDown 0.2s
- **Bouton X**: Hover gris avec transition

## Flux Utilisateur

### Répondre à un Message
1. Survoler un message
2. Cliquer sur le bouton vert "Répondre"
3. La prévisualisation apparaît au-dessus de l'input
4. Taper la réponse
5. Envoyer le message
6. Le message affiche la référence au message original

### Annuler une Réponse
1. Cliquer sur le X dans la prévisualisation
2. La prévisualisation disparaît
3. Le champ caché est vidé
4. Le message sera envoyé normalement (sans réponse)

## Exemple de Conversation

```
Marie: Bonjour tout le monde! 👋
  └─ Toi: Oui, bonjour Marie 😊
     [Référence: Marie - Bonjour tout le monde! 👋]

Jean: Comment allez-vous?
  └─ Marie: Très bien merci!
     [Référence: Jean - Comment allez-vous?]
```

## Sécurité

### Validations
- ✅ Vérification que le message original existe
- ✅ Vérification que le message appartient au même chatroom
- ✅ Protection contre les injections SQL (Doctrine ORM)
- ✅ Échappement XSS dans les templates (Twig)
- ✅ Suppression en cascade gérée (SET NULL)

### Gestion des Suppressions
- Si le message original est supprimé, `reply_to_id` devient NULL
- Le message de réponse reste visible
- Pas de référence cassée dans l'interface

## Avantages

### Pour l'Utilisateur
1. **Contexte clair**: Voir à quel message on répond
2. **Navigation facile**: Suivre les fils de conversation
3. **Organisation**: Structure hiérarchique des discussions
4. **Intuitivité**: Interface familière (comme WhatsApp, Telegram)

### Pour le Développement
1. **Scalabilité**: Peut supporter des threads complexes
2. **Flexibilité**: Facile d'ajouter des fonctionnalités (scroll to message)
3. **Performance**: Index sur reply_to_id pour requêtes rapides
4. **Maintenabilité**: Code propre et bien structuré

## Améliorations Futures (Optionnelles)

- [ ] Scroll automatique vers le message original au clic
- [ ] Compteur de réponses sur chaque message
- [ ] Vue en thread (afficher toutes les réponses)
- [ ] Réponses imbriquées (multi-niveaux)
- [ ] Notification quand quelqu'un répond à votre message
- [ ] Highlight du message original au survol de la référence

## Fichiers Modifiés

### Backend
- `src/Entity/Message.php` - Ajout relation replyTo/replies
- `src/Controller/GoalController.php` - Gestion des réponses
- `migrations/Version20260216202911.php` - Migration DB

### Frontend
- `templates/chatroom/chatroom.html.twig` - UI complète (boutons, prévisualisation, référence)

## Tests

Pour tester le système de réponses:

1. Se connecter (mariemayari@gmail.com / mariem)
2. Ouvrir un chatroom
3. Survoler un message existant
4. Cliquer sur le bouton vert "Répondre"
5. Vérifier que la prévisualisation apparaît
6. Taper une réponse
7. Envoyer
8. Vérifier que le message affiche la référence

## Présentation pour Soutenance

### Points à Mettre en Avant

1. **Structure hiérarchique** - Relation parent-enfant dans la DB
2. **UX moderne** - Interface inspirée des messageries populaires
3. **Animations fluides** - Transitions CSS professionnelles
4. **Code propre** - Architecture MVC respectée
5. **Sécurité** - Validations et protections en place

### Démonstration Live

1. Montrer un message simple
2. Cliquer sur "Répondre"
3. Montrer la prévisualisation
4. Envoyer la réponse
5. Montrer la référence dans le message
6. Montrer l'annulation d'une réponse

---

**Date d'Implémentation**: 16 Février 2026
**Statut**: Production Ready ✅
**Complexité**: Intermédiaire 🔥
**Impact Visuel**: Élevé 🌟
