# Guide d'Accès aux Sous-Groupes Privés 📱

## Comment Accéder aux Sous-Groupes Privés

### Méthode 1: Depuis le Chatroom Principal

#### Étape 1: Accéder au Chatroom d'un Goal
```
URL: http://127.0.0.1:8000/message/chatroom/{goalId}
Exemple: http://127.0.0.1:8000/message/chatroom/1
```

#### Étape 2: Cliquer sur le Bouton "Créer un Sous-Groupe"
- Regardez en haut à droite du chatroom
- Cliquez sur l'icône <i class="fas fa-user-plus"></i> (utilisateur avec +)
- Tooltip: "Créer un sous-groupe privé"

#### Étape 3: Remplir le Formulaire
1. **Nom du sous-groupe** - Ex: "Équipe Marketing"
2. **Sélectionner les membres** - Cochez les membres à inclure
3. Cliquer sur "Créer le sous-groupe"

#### Étape 4: Accès Automatique
- Vous serez automatiquement redirigé vers le sous-groupe créé
- Vous pouvez commencer à envoyer des messages

### Méthode 2: URL Directe

#### Créer un Sous-Groupe
```
URL: http://127.0.0.1:8000/message/private-chatroom/create/{goalId}
Exemple: http://127.0.0.1:8000/message/private-chatroom/create/1
```

#### Voir un Sous-Groupe Spécifique
```
URL: http://127.0.0.1:8000/message/private-chatroom/{id}
Exemple: http://127.0.0.1:8000/message/private-chatroom/5
```

#### Liste des Sous-Groupes d'un Goal
```
URL: http://127.0.0.1:8000/message/private-chatrooms/{goalId}
Exemple: http://127.0.0.1:8000/message/private-chatrooms/1
```

## Routes Disponibles

### 1. Créer un Sous-Groupe
```
Route: message_private_chatroom_create
URL: /message/private-chatroom/create/{goalId}
Méthodes: GET, POST
```

**Accès:**
- Utilisateur connecté
- Membre approuvé du goal

**Fonctionnalité:**
- Affiche le formulaire de création
- Permet de sélectionner les membres
- Crée le sous-groupe

### 2. Afficher un Sous-Groupe
```
Route: message_private_chatroom_show
URL: /message/private-chatroom/{id}
Méthodes: GET, POST
```

**Accès:**
- Utilisateur connecté
- Membre du sous-groupe

**Fonctionnalité:**
- Affiche les messages du sous-groupe
- Permet d'envoyer des messages
- Liste des membres

### 3. Liste des Sous-Groupes
```
Route: message_private_chatrooms_list
URL: /message/private-chatrooms/{goalId}
Méthode: GET
```

**Accès:**
- Utilisateur connecté
- Membre approuvé du goal

**Fonctionnalité:**
- Liste tous les sous-groupes de l'utilisateur
- Affiche le nombre de membres
- Lien vers chaque sous-groupe

## Interface Utilisateur

### Bouton dans le Chatroom Principal

**Emplacement:**
- En haut à droite du chatroom
- À côté des boutons Search, Call, More

**Apparence:**
- Icône: <i class="fas fa-user-plus"></i>
- Forme: Cercle
- Couleur: Gris clair (#f0f2f5)
- Hover: Gris foncé (#e4e6eb)

**Code:**
```html
<a href="{{ path('message_private_chatroom_create', {goalId: goal.id}) }}" 
   class="header-btn" 
   title="Créer un sous-groupe privé">
    <i class="fas fa-user-plus"></i>
</a>
```

### Formulaire de Création

**Champs:**
1. **Nom du sous-groupe** (requis)
   - Type: Texte
   - Min: 3 caractères
   - Max: 255 caractères
   - Placeholder: "Ex: Équipe Marketing"

2. **Membres** (requis)
   - Type: Checkboxes multiples
   - Affiche tous les membres approuvés du goal
   - Exclut le créateur (ajouté automatiquement)

**Boutons:**
- "Annuler" - Retour au chatroom principal
- "Créer le sous-groupe" - Soumet le formulaire

## Permissions et Sécurité

### Qui Peut Créer un Sous-Groupe?
✅ Tous les membres approuvés du goal
❌ Membres en attente (PENDING)
❌ Non-membres
❌ Utilisateurs non connectés

### Qui Peut Accéder à un Sous-Groupe?
✅ Le créateur du sous-groupe
✅ Les membres sélectionnés
❌ Autres membres du goal
❌ Non-membres

### Qui Peut Envoyer des Messages?
✅ Le créateur du sous-groupe
✅ Les membres du sous-groupe
❌ Tous les autres

## Exemples d'Utilisation

### Exemple 1: Créer un Sous-Groupe "Équipe Marketing"

**Étape 1:** Accéder au chatroom du goal
```
http://127.0.0.1:8000/message/chatroom/1
```

**Étape 2:** Cliquer sur l'icône <i class="fas fa-user-plus"></i>

**Étape 3:** Remplir le formulaire
- Nom: "Équipe Marketing"
- Membres: Cocher Alice, Bob, Charlie

**Étape 4:** Cliquer sur "Créer le sous-groupe"

**Résultat:**
- Sous-groupe créé avec 4 membres (vous + 3 sélectionnés)
- Redirection vers le sous-groupe
- Vous pouvez envoyer des messages

### Exemple 2: Accéder à un Sous-Groupe Existant

**Option A: Via la liste**
```
http://127.0.0.1:8000/message/private-chatrooms/1
```
- Voir tous vos sous-groupes
- Cliquer sur celui souhaité

**Option B: URL directe**
```
http://127.0.0.1:8000/message/private-chatroom/5
```
- Accès direct si vous connaissez l'ID

### Exemple 3: Envoyer un Message dans un Sous-Groupe

**Étape 1:** Accéder au sous-groupe
```
http://127.0.0.1:8000/message/private-chatroom/5
```

**Étape 2:** Utiliser le formulaire en bas
- Taper votre message
- Cliquer sur "Envoyer"

**Résultat:**
- Message visible uniquement par les membres du sous-groupe
- Notification aux membres (si implémenté)

## Scénarios d'Erreur

### Erreur 1: "Vous devez être connecté"
**Cause:** Utilisateur non connecté
**Solution:** Se connecter via `/login`

### Erreur 2: "Vous devez être membre de ce goal"
**Cause:** Utilisateur pas membre ou pas approuvé
**Solution:** Rejoindre le goal et attendre l'approbation

### Erreur 3: "Vous n'avez pas accès à ce sous-groupe"
**Cause:** Utilisateur pas membre du sous-groupe
**Solution:** Demander au créateur de créer un nouveau sous-groupe avec vous

### Erreur 4: "Private chatroom not found"
**Cause:** ID de sous-groupe invalide ou supprimé
**Solution:** Vérifier l'ID ou accéder via la liste

## Navigation

### Depuis le Chatroom Principal
```
Chatroom Principal
    ↓ (clic sur icône user-plus)
Formulaire de Création
    ↓ (soumettre)
Sous-Groupe Créé
```

### Depuis la Liste des Goals
```
Liste des Goals
    ↓ (clic sur "Chatroom")
Chatroom Principal
    ↓ (clic sur icône user-plus)
Formulaire de Création
```

### Navigation Complète
```
/goals
    ↓
/message/chatroom/{goalId}
    ↓
/message/private-chatroom/create/{goalId}
    ↓
/message/private-chatroom/{id}
```

## Raccourcis Clavier (À Implémenter)

### Suggestions
- `Ctrl + N` - Nouveau sous-groupe
- `Ctrl + L` - Liste des sous-groupes
- `Esc` - Retour au chatroom principal

## Intégration Future

### Menu Latéral (À Créer)
```
┌─────────────────────┐
│ Chatroom Principal  │
├─────────────────────┤
│ Sous-Groupes:       │
│ • Équipe Marketing  │
│ • Équipe Technique  │
│ • Management        │
├─────────────────────┤
│ + Créer un groupe   │
└─────────────────────┘
```

### Notifications (À Implémenter)
- Badge avec nombre de messages non lus
- Notification de création de sous-groupe
- Notification d'ajout à un sous-groupe

## Commandes Utiles

### Vérifier les Routes
```bash
php bin/console debug:router | findstr /i "private"
```

### Vérifier les Sous-Groupes en Base
```bash
php bin/console dbal:run-sql "SELECT * FROM private_chatroom"
```

### Vérifier les Membres
```bash
php bin/console dbal:run-sql "SELECT * FROM private_chatroom_members"
```

## Résumé des URLs

| Action | URL | Méthode |
|--------|-----|---------|
| Créer un sous-groupe | `/message/private-chatroom/create/{goalId}` | GET, POST |
| Voir un sous-groupe | `/message/private-chatroom/{id}` | GET, POST |
| Liste des sous-groupes | `/message/private-chatrooms/{goalId}` | GET |
| Chatroom principal | `/message/chatroom/{goalId}` | GET, POST |

## Prochaines Étapes

1. ✅ Bouton ajouté dans le chatroom principal
2. ⏳ Créer le template `private_chatroom_show.html.twig`
3. ⏳ Créer le template `private_chatrooms_list.html.twig`
4. ⏳ Ajouter un menu latéral avec la liste des sous-groupes
5. ⏳ Ajouter les notifications
6. ⏳ Permettre la modification des membres
7. ⏳ Ajouter la suppression de sous-groupes

## Support

### En Cas de Problème
1. Vérifier que vous êtes connecté
2. Vérifier que vous êtes membre approuvé du goal
3. Vérifier l'URL
4. Nettoyer le cache: `php bin/console cache:clear`
5. Vérifier les logs Symfony

### Logs à Consulter
```bash
# Logs de l'application
tail -f var/log/dev.log

# Logs du serveur
# (selon votre configuration)
```
