# Sous-Groupes Privés - IMPLÉMENTATION ✅

## Objectif
Permettre aux membres d'un goal de créer des sous-groupes privés pour des conversations restreintes.

## Architecture

### 1. Nouvelle Entité: PrivateChatroom
**Fichier:** `src/Entity/PrivateChatroom.php`

**Champs:**
- `id` - Identifiant unique
- `name` - Nom du sous-groupe
- `createdAt` - Date de création
- `parentGoal` - Goal parent (ManyToOne)
- `creator` - Créateur du sous-groupe (ManyToOne User)
- `members` - Membres du sous-groupe (ManyToMany User)
- `messages` - Messages du sous-groupe (OneToMany Message)
- `isActive` - Statut actif/inactif

**Méthodes:**
- `isMember(User $user)` - Vérifie si un utilisateur est membre
- `addMember(User $member)` - Ajoute un membre
- `removeMember(User $member)` - Retire un membre

### 2. Repository: PrivateChatroomRepository
**Fichier:** `src/Repository/PrivateChatroomRepository.php`

**Méthodes:**
- `findByUserAndGoal(User $user, Goal $goal)` - Trouve tous les sous-groupes d'un utilisateur dans un goal
- `findByCreator(User $user)` - Trouve tous les sous-groupes créés par un utilisateur

### 3. Modification de l'Entité Message
**Fichier:** `src/Entity/Message.php`

**Ajout:**
```php
#[ORM\ManyToOne(inversedBy: 'messages', targetEntity: PrivateChatroom::class)]
#[ORM\JoinColumn(nullable: true)]
private ?PrivateChatroom $privateChatroom = null;
```

**Changement:**
- `chatroom` devient nullable (pour supporter les messages privés)
- Un message appartient soit à un Chatroom, soit à un PrivateChatroom

### 4. Formulaire: PrivateChatroomType
**Fichier:** `src/Form/PrivateChatroomType.php`

**Champs:**
- `name` - TextType (nom du sous-groupe)
- `members` - EntityType (sélection multiple des membres)

**Validation:**
- Nom requis (3-255 caractères)
- Au moins un membre sélectionné

## Routes Ajoutées

### 1. Liste des Sous-Groupes
```
Route: /message/private-chatrooms/{goalId}
Name: message_private_chatrooms_list
Method: GET
```

**Fonctionnalité:**
- Affiche tous les sous-groupes privés d'un utilisateur dans un goal
- Accessible uniquement aux membres approuvés

### 2. Créer un Sous-Groupe
```
Route: /message/private-chatroom/create/{goalId}
Name: message_private_chatroom_create
Methods: GET, POST
```

**Fonctionnalité:**
- Formulaire de création de sous-groupe
- Sélection des membres parmi les membres approuvés du goal
- Le créateur est automatiquement membre

### 3. Afficher un Sous-Groupe
```
Route: /message/private-chatroom/{id}
Name: message_private_chatroom_show
Methods: GET, POST
```

**Fonctionnalité:**
- Affiche le chatroom privé
- Permet d'envoyer des messages
- Accessible uniquement aux membres du sous-groupe

## Contrôleur: MessageController

### Méthode listPrivateChatrooms()
**Vérifications:**
1. Utilisateur connecté
2. Goal existe
3. Utilisateur est membre approuvé du goal

**Retour:**
- Liste des sous-groupes privés de l'utilisateur

### Méthode createPrivateChatroom()
**Vérifications:**
1. Utilisateur connecté
2. Goal existe
3. Utilisateur est membre approuvé du goal

**Processus:**
1. Récupère les membres disponibles (membres approuvés sauf créateur)
2. Affiche le formulaire
3. Crée le sous-groupe avec les membres sélectionnés
4. Redirige vers le chatroom privé

### Méthode showPrivateChatroom()
**Vérifications:**
1. Utilisateur connecté
2. Sous-groupe existe
3. Utilisateur est membre du sous-groupe

**Fonctionnalité:**
- Affiche les messages du sous-groupe
- Permet d'envoyer des messages
- Support AJAX

## Sécurité

### Niveaux de Vérification

#### Niveau 1: Authentification
✅ Utilisateur doit être connecté

#### Niveau 2: Membership du Goal
✅ Utilisateur doit être membre approuvé du goal parent

#### Niveau 3: Membership du Sous-Groupe
✅ Utilisateur doit être membre du sous-groupe privé

### Permissions

**Créer un Sous-Groupe:**
- Tous les membres approuvés du goal peuvent créer un sous-groupe

**Accéder à un Sous-Groupe:**
- Uniquement les membres du sous-groupe
- Le créateur a toujours accès

**Envoyer des Messages:**
- Uniquement les membres du sous-groupe

## Base de Données

### Table: private_chatroom
```sql
CREATE TABLE private_chatroom (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    created_at DATETIME NOT NULL,
    parent_goal_id INT NOT NULL,
    creator_id INT NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (parent_goal_id) REFERENCES goal(id),
    FOREIGN KEY (creator_id) REFERENCES user(id)
);
```

### Table: private_chatroom_members
```sql
CREATE TABLE private_chatroom_members (
    private_chatroom_id INT NOT NULL,
    user_id INT NOT NULL,
    PRIMARY KEY (private_chatroom_id, user_id),
    FOREIGN KEY (private_chatroom_id) REFERENCES private_chatroom(id),
    FOREIGN KEY (user_id) REFERENCES user(id)
);
```

### Modification: message
```sql
ALTER TABLE message 
    MODIFY COLUMN chatroom_id INT NULL,
    ADD COLUMN private_chatroom_id INT NULL,
    ADD FOREIGN KEY (private_chatroom_id) REFERENCES private_chatroom(id);
```

## Migration Doctrine

### Commandes à Exécuter
```bash
# Créer la migration
php bin/console make:migration

# Vérifier la migration générée
# Éditer si nécessaire

# Exécuter la migration
php bin/console doctrine:migrations:migrate

# Vérifier le schéma
php bin/console doctrine:schema:validate
```

## Templates

### 1. private_chatroom_create.html.twig
**Fonctionnalités:**
- Formulaire de création
- Sélection des membres avec checkboxes
- Design moderne et responsive
- Validation côté client

### 2. private_chatroom_show.html.twig (À créer)
**Fonctionnalités:**
- Affichage des messages
- Formulaire d'envoi de message
- Liste des membres
- Bouton pour quitter le sous-groupe

### 3. private_chatrooms_list.html.twig (À créer)
**Fonctionnalités:**
- Liste des sous-groupes
- Bouton "Créer un sous-groupe"
- Nombre de membres par sous-groupe
- Dernier message

## Interface Utilisateur

### Bouton dans le Chatroom Principal
**Emplacement:** En haut du chatroom principal

```html
<a href="{{ path('message_private_chatroom_create', {goalId: goal.id}) }}" class="btn btn-create-subgroup">
    <i class="fas fa-users"></i> Créer un sous-groupe privé
</a>
```

### Menu Latéral
**Afficher:**
- Liste des sous-groupes privés
- Nombre de messages non lus
- Indicateur d'activité

## Cas d'Usage

### 1. Équipes de Travail
- Sous-groupe "Équipe Marketing"
- Sous-groupe "Équipe Technique"
- Sous-groupe "Management"

### 2. Projets Spécifiques
- Sous-groupe "Projet A"
- Sous-groupe "Projet B"
- Communication ciblée

### 3. Discussions Sensibles
- Informations confidentielles
- Décisions stratégiques
- Feedback privé

### 4. Groupes d'Intérêt
- Sous-groupe "Sport"
- Sous-groupe "Culture"
- Discussions thématiques

## Fonctionnalités Futures

### 1. Gestion des Membres
- Ajouter des membres après création
- Retirer des membres
- Transférer la propriété

### 2. Permissions Avancées
- Admin du sous-groupe
- Permissions de modération
- Permissions d'invitation

### 3. Notifications
- Notification de création
- Notification d'ajout au sous-groupe
- Notification de nouveau message

### 4. Statistiques
- Nombre de messages
- Membres actifs
- Activité récente

### 5. Archivage
- Archiver un sous-groupe
- Historique des sous-groupes
- Restauration

## Avantages

1. **Confidentialité** - Conversations privées entre membres sélectionnés
2. **Organisation** - Meilleure structuration des discussions
3. **Efficacité** - Communication ciblée
4. **Flexibilité** - Création libre par tous les membres
5. **Sécurité** - Accès strictement contrôlé

## Limitations Actuelles

1. **Pas de modification des membres** - Après création, les membres sont fixes
2. **Pas de suppression** - Les sous-groupes ne peuvent pas être supprimés
3. **Pas de notifications** - Pas de notification de création ou de nouveau message
4. **Pas d'historique** - Pas de suivi des modifications

## Prochaines Étapes

### Étape 1: Migration de la Base de Données
```bash
php bin/console make:migration
php bin/console doctrine:migrations:migrate
```

### Étape 2: Créer les Templates Manquants
- `private_chatroom_show.html.twig`
- `private_chatrooms_list.html.twig`

### Étape 3: Ajouter le Bouton dans le Chatroom Principal
- Modifier `chatroom_modern.html.twig`
- Ajouter le bouton "Créer un sous-groupe"

### Étape 4: Tests
- Créer un sous-groupe
- Envoyer des messages
- Vérifier les permissions
- Tester avec plusieurs utilisateurs

### Étape 5: Améliorations
- Ajouter les notifications
- Permettre la modification des membres
- Ajouter la suppression de sous-groupes

## Fichiers Créés

1. ✅ `src/Entity/PrivateChatroom.php`
2. ✅ `src/Repository/PrivateChatroomRepository.php`
3. ✅ `src/Form/PrivateChatroomType.php`
4. ✅ `templates/message/private_chatroom_create.html.twig`

## Fichiers Modifiés

1. ✅ `src/Entity/Message.php` - Ajout de la relation privateChatroom
2. ✅ `src/Controller/MessageController.php` - Ajout de 3 nouvelles méthodes

## Fichiers À Créer

1. ⏳ `templates/message/private_chatroom_show.html.twig`
2. ⏳ `templates/message/private_chatrooms_list.html.twig`

## Résultat Final

✅ Architecture complète pour les sous-groupes privés
✅ Entités et relations créées
✅ Contrôleur avec 3 méthodes
✅ Formulaire de création
✅ Template de création
✅ Sécurité et permissions
✅ Documentation complète

⏳ Migration à exécuter
⏳ Templates à compléter
⏳ Intégration dans l'interface principale
