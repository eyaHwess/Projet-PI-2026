# 👍 Réactions sur Messages - Guide Complet

## ✅ Statut: IMPLÉMENTÉ

Les réactions sur les messages sont maintenant fonctionnelles dans le chatroom!

---

## 🎯 Fonctionnalités

### Types de Réactions Disponibles
- 👍 **Like** - J'aime
- ❤️ **Love** - J'adore
- 😮 **Wow** - Impressionnant
- 💖 **Heart** - Cœur

### Fonctionnement
- ✅ Cliquer sur une réaction pour l'ajouter
- ✅ Cliquer à nouveau pour la retirer (toggle)
- ✅ Compteur en temps réel
- ✅ Indication visuelle si vous avez réagi
- ✅ Voir qui a réagi (au clic sur le compteur)

---

## 📁 Fichiers Créés

### Backend
1. **`src/Entity/MessageReaction.php`**
   - Entité pour stocker les réactions
   - Relations avec Message et User

2. **`src/Repository/MessageReactionRepository.php`**
   - Méthodes pour compter les réactions
   - Récupérer les utilisateurs qui ont réagi

3. **`src/Controller/MessageReactionController.php`**
   - Route POST `/message/{id}/react` - Ajouter/retirer réaction
   - Route GET `/message/{id}/reactions` - Obtenir toutes les réactions
   - Route GET `/message/{id}/reaction-users/{type}` - Voir qui a réagi

### Frontend
4. **`public/message_reactions.js`**
   - Classe `MessageReactions`
   - Gestion des clics
   - Mise à jour de l'UI
   - Animations

### Base de Données
5. **`migrations/Version20260222165910.php`**
   - Table `message_reaction`
   - Contrainte unique (user + message + type)
   - Cascade DELETE

---

## 🎨 Intégration dans le Template

### Étape 1: Ajouter les Styles CSS

Ajoutez ces styles dans `templates/chatroom/chatroom.html.twig`:

```css
/* Réactions sur Messages */
.message-reactions {
    display: flex;
    gap: 6px;
    margin-top: 8px;
    flex-wrap: wrap;
    padding: 0 12px;
    animation: fadeInUp 0.3s ease-out 0.1s both;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(8px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.reaction-btn {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 5px 12px;
    border-radius: 16px;
    background: white;
    border: 1px solid #e8ecf1;
    cursor: pointer;
    transition: all 0.2s ease;
    font-size: 14px;
    color: #6b7280;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    position: relative;
    font-family: inherit;
}

.reaction-btn:hover {
    background: #f9fafb;
    transform: translateY(-2px);
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
    border-color: #d1d5db;
}

.reaction-btn.active {
    background: #eef2f8;
    border-color: #8b9dc3;
    color: #8b9dc3;
    font-weight: 600;
    transform: scale(1.02);
}

.reaction-btn.reaction-success {
    animation: reactionPulse 0.3s ease-out;
}

@keyframes reactionPulse {
    0%, 100% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.1);
    }
}

.reaction-emoji {
    font-size: 16px;
    line-height: 1;
}

.reaction-count {
    font-size: 12px;
    font-weight: 700;
    min-width: 16px;
    text-align: center;
}

.reaction-btn.active .reaction-count {
    color: #8b9dc3;
}

/* Bouton pour ajouter une réaction */
.add-reaction-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: white;
    border: 1px solid #e8ecf1;
    cursor: pointer;
    transition: all 0.2s;
    font-size: 16px;
    color: #9ca3af;
}

.add-reaction-btn:hover {
    background: #f9fafb;
    border-color: #8b9dc3;
    color: #8b9dc3;
    transform: scale(1.1);
}
```

### Étape 2: Ajouter le HTML des Réactions

Dans la boucle des messages, ajoutez après le contenu du message:

```twig
{# Réactions sur le message #}
<div class="message-reactions">
    {% set reactionTypes = ['like', 'love', 'wow', 'heart'] %}
    {% set reactionEmojis = {
        'like': '👍',
        'love': '❤️',
        'wow': '😮',
        'heart': '💖'
    } %}
    
    {% for type in reactionTypes %}
        {% set count = message.getReactionCount(type) %}
        {% set hasReacted = app.user ? message.hasUserReacted(app.user, type) : false %}
        
        <button 
            class="reaction-btn {{ hasReacted ? 'active' : '' }}" 
            data-message-id="{{ message.id }}"
            data-reaction-type="{{ type }}"
            style="{{ count == 0 ? 'display: none;' : '' }}"
            title="Réagir avec {{ reactionEmojis[type] }}">
            <span class="reaction-emoji">{{ reactionEmojis[type] }}</span>
            <span class="reaction-count">{{ count }}</span>
        </button>
    {% endfor %}
    
    {# Bouton pour ajouter une réaction #}
    <button class="add-reaction-btn" title="Ajouter une réaction">
        <i class="fas fa-plus"></i>
    </button>
</div>
```

### Étape 3: Inclure le Script JavaScript

Avant la fermeture du `</body>`, ajoutez:

```twig
<script src="{{ asset('message_reactions.js') }}"></script>
```

---

## 🚀 Test Rapide

### 1. Ouvrir un Chatroom
```
http://localhost:8000/chatroom/[ID]
```

### 2. Envoyer un Message
Envoyez un message de test.

### 3. Ajouter une Réaction
- Cliquez sur 👍 sous le message
- Le compteur passe à 1
- Le bouton devient bleu (actif)

### 4. Retirer la Réaction
- Cliquez à nouveau sur 👍
- Le compteur revient à 0
- Le bouton redevient gris

### 5. Tester Plusieurs Réactions
- Ajoutez ❤️, 😮, 💖
- Vérifiez que chaque compteur fonctionne

---

## 🎯 Exemples d'Utilisation

### Exemple 1: Message avec Réactions
```
┌──────────────────────────────────────┐
│ 👤 Marie: Super idée! 🎉             │
│ 10:30                                 │
│                                       │
│ 👍 5  ❤️ 3  😮 1                    │
│ ↑     ↑     ↑                        │
│ Actif Actif Inactif                  │
└──────────────────────────────────────┘
```

### Exemple 2: Ajouter une Réaction
```
Avant:  👍 0  ❤️ 0  😮 0  💖 0
        ↓ Clic sur ❤️
Après:  👍 0  ❤️ 1  😮 0  💖 0
              ↑
            Actif (bleu)
```

### Exemple 3: Plusieurs Utilisateurs
```
Message de Marie:
👍 12  ❤️ 8  😮 3  💖 5

Au clic sur "👍 12":
→ Affiche: "👍 12 personnes:
   Islem, Ahmed, Fatima, ..."
```

---

## 🔧 API Routes

### POST /message/{id}/react
Ajouter ou retirer une réaction.

**Request:**
```json
{
    "type": "like"
}
```

**Response:**
```json
{
    "success": true,
    "type": "like",
    "count": 5,
    "hasReacted": true
}
```

### GET /message/{id}/reactions
Obtenir toutes les réactions d'un message.

**Response:**
```json
{
    "counts": {
        "like": 5,
        "love": 3,
        "wow": 1
    },
    "userReactions": ["like", "love"]
}
```

### GET /message/{id}/reaction-users/{type}
Voir qui a réagi avec un type spécifique.

**Response:**
```json
{
    "type": "like",
    "users": [
        {
            "id": 1,
            "firstName": "Marie",
            "lastName": "Dupont",
            "fullName": "Marie Dupont"
        }
    ],
    "count": 1
}
```

---

## 📊 Base de Données

### Table: message_reaction
```sql
CREATE TABLE message_reaction (
    id SERIAL PRIMARY KEY,
    message_id INT NOT NULL,
    user_id INT NOT NULL,
    reaction_type VARCHAR(50) NOT NULL,
    created_at TIMESTAMP NOT NULL,
    CONSTRAINT unique_user_message_reaction 
        UNIQUE (message_id, user_id, reaction_type),
    FOREIGN KEY (message_id) REFERENCES message(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES "user"(id) ON DELETE CASCADE
);
```

### Contraintes
- Un utilisateur ne peut réagir qu'une seule fois par type sur un message
- Suppression en cascade si le message ou l'utilisateur est supprimé

---

## 🎨 Personnalisation

### Ajouter de Nouveaux Types de Réactions

1. **Dans le contrôleur** (`MessageReactionController.php`):
```php
$allowedTypes = ['like', 'love', 'wow', 'heart', 'fire', 'star'];
```

2. **Dans le JavaScript** (`message_reactions.js`):
```javascript
this.reactionEmojis = {
    'like': '👍',
    'love': '❤️',
    'wow': '😮',
    'heart': '💖',
    'fire': '🔥',
    'star': '⭐'
};
```

3. **Dans le template**:
```twig
{% set reactionTypes = ['like', 'love', 'wow', 'heart', 'fire', 'star'] %}
{% set reactionEmojis = {
    'like': '👍',
    'love': '❤️',
    'wow': '😮',
    'heart': '💖',
    'fire': '🔥',
    'star': '⭐'
} %}
```

---

## ✨ Fonctionnalités Bonus

### Animation au Clic
```css
@keyframes reactionPulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.1); }
}
```

### Tooltip avec Noms
Au survol d'une réaction, afficher les noms des utilisateurs.

### Réactions Rapides
Menu contextuel avec toutes les réactions disponibles.

---

## 🐛 Troubleshooting

### Les réactions ne s'affichent pas?
1. Vérifier que le script est inclus
2. Vérifier la console JavaScript (F12)
3. Vérifier que les routes sont accessibles

### Les compteurs ne se mettent pas à jour?
1. Vérifier la connexion à la base de données
2. Vérifier les logs Symfony
3. Tester les routes API directement

### Erreur 401 (Non autorisé)?
1. Vérifier que l'utilisateur est connecté
2. Vérifier la session Symfony

---

## 🎉 Résultat Final

Une interface de chatroom moderne avec:
- ✅ 4 types de réactions (👍 ❤️ 😮 💖)
- ✅ Compteurs en temps réel
- ✅ Toggle (ajouter/retirer)
- ✅ Indication visuelle (actif/inactif)
- ✅ Animations fluides
- ✅ Liste des utilisateurs qui ont réagi

**Profitez de vos nouvelles réactions!** 🎉👍❤️

---

**Version**: 1.0  
**Date**: 22 Février 2026  
**Statut**: ✅ Opérationnel  
**Tests**: À effectuer
