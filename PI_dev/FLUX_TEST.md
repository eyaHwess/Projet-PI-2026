# Test du Flux Complet - Goals & Chatroom

## ✅ Étapes Complétées

### 1. Relations Doctrine Corrigées
- ✅ Goal ↔ GoalParticipation (mappedBy: 'goal')
- ✅ User ↔ GoalParticipation (mappedBy: 'user')
- ✅ Goal ↔ Chatroom (OneToOne bidirectionnel)
- ✅ Chatroom ↔ Message (OneToMany)
- ✅ Message → User (ManyToOne author)

### 2. Base de Données Synchronisée
- ✅ Migration créée et exécutée
- ✅ Tables: goal, chatroom, message, goal_participation

### 3. Logique Métier Implémentée
- ✅ GoalRepository: findGoalsWithParticipants(), findByUser(), findActiveGoals()
- ✅ MessageRepository: findByChatroomOrderedByDate(), findRecentMessages()
- ✅ Goal::isUserParticipating() - vérification de participation
- ✅ Création automatique du chatroom lors de la création d'un goal
- ✅ Participation automatique du créateur au goal

### 4. Sécurité Implémentée
- ✅ Vérification ROLE_USER sur toutes les routes
- ✅ Vérification de participation avant accès au chatroom
- ✅ Empêcher de rejoindre deux fois le même goal
- ✅ Messages flash pour feedback utilisateur

### 5. Vues Twig Créées/Améliorées
- ✅ goal/list.html.twig - Liste avec participants et boutons intelligents
- ✅ goal/show.html.twig - Détails du goal avec liste des participants
- ✅ chatroom/chatroom.html.twig - Chat avec infos du goal et participants

## 🧪 Flux de Test à Exécuter

### Scénario 1: Créer un Goal
1. Se connecter en tant qu'utilisateur
2. Aller sur `/goals`
3. Cliquer sur "Créer un Goal"
4. Remplir le formulaire:
   - Title: "Apprendre Symfony"
   - Description: "Maîtriser Symfony en 30 jours"
   - Start Date: aujourd'hui
   - End Date: +30 jours
   - Status: "active"
5. Soumettre
6. ✅ Vérifier: Goal créé, Chatroom créé, Participation créée automatiquement

### Scénario 2: Rejoindre un Goal
1. Se connecter avec un autre utilisateur
2. Aller sur `/goals`
3. Voir le goal "Apprendre Symfony"
4. Cliquer sur "Rejoindre"
5. ✅ Vérifier: Message de succès, bouton devient "Quitter", bouton "Chatroom" apparaît

### Scénario 3: Accéder au Chatroom
1. Cliquer sur "Chatroom"
2. ✅ Vérifier: 
   - Accès autorisé (car participant)
   - Infos du goal affichées
   - Liste des participants visible
   - Aucun message pour l'instant

### Scénario 4: Envoyer des Messages
1. Dans le chatroom, taper "Bonjour tout le monde!"
2. Envoyer
3. ✅ Vérifier: Message apparaît à droite (envoyé)
4. Se connecter avec l'autre utilisateur
5. Accéder au même chatroom
6. ✅ Vérifier: Message apparaît à gauche (reçu) avec nom de l'auteur
7. Répondre "Salut! Prêt à apprendre?"
8. ✅ Vérifier: Les deux messages s'affichent correctement

### Scénario 5: Sécurité - Accès Refusé
1. Se connecter avec un utilisateur qui ne participe PAS au goal
2. Essayer d'accéder directement à `/chatroom/{id}`
3. ✅ Vérifier: Redirection vers `/goals` avec message d'erreur

### Scénario 6: Quitter un Goal
1. Aller sur `/goals`
2. Cliquer sur "Quitter" pour un goal
3. ✅ Vérifier: 
   - Message de succès
   - Bouton redevient "Rejoindre"
   - Bouton "Chatroom" disparaît
   - Accès au chatroom refusé

## 🚀 Commandes Utiles

```bash
# Vérifier la base de données
php bin/console doctrine:schema:validate

# Voir les routes
php bin/console debug:router | grep goal
php bin/console debug:router | grep chatroom

# Lancer le serveur
symfony server:start
# ou
php -S localhost:8000 -t public
```

## 📊 Routes Disponibles

- GET  `/goals` - Liste des goals
- GET  `/goal/new` - Formulaire création goal
- POST `/goal/new` - Créer un goal
- GET  `/goal/{id}` - Détails d'un goal
- GET  `/goal/{id}/join` - Rejoindre un goal
- GET  `/goal/{id}/leave` - Quitter un goal
- GET  `/chatroom/{id}` - Accéder au chatroom
- POST `/chatroom/{id}` - Envoyer un message

## ✨ Fonctionnalités Implémentées

1. ✅ Créer un goal → Chatroom créé automatiquement
2. ✅ Rejoindre un goal → Participation enregistrée
3. ✅ Ouvrir la chatroom → Vérification de participation
4. ✅ Envoyer un message → Sauvegardé avec auteur et date
5. ✅ Voir les messages → Affichés en temps réel (refresh manuel)

## 🎯 Prochaines Améliorations Possibles

- [ ] WebSocket pour messages en temps réel (Mercure/Pusher)
- [ ] Notifications de nouveaux messages
- [ ] Upload d'images dans le chat
- [ ] Recherche de goals
- [ ] Filtres (actifs, terminés, mes goals)
- [ ] Statistiques de participation
- [ ] Système de modération
