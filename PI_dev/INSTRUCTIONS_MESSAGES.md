# Instructions pour tester les messages

## Étapes pour configurer et tester

### 1. Créer les comptes utilisateurs
Visitez: `http://localhost:8000/demo/setup`

Cela créera automatiquement 3 comptes:
- **mariemayari@gmail.com** / password: **mariem**
- alice@test.com / password: password123
- bob@test.com / password: password123

### 2. Se connecter
Visitez: `http://localhost:8000/login`

Connectez-vous avec:
- Email: **mariemayari@gmail.com**
- Password: **mariem**

### 3. Créer un Goal
Visitez: `http://localhost:8000/goal/new`

Remplissez le formulaire multi-étapes pour créer un goal.

### 4. Accéder à la chatroom
Depuis la liste des goals (`http://localhost:8000/goals`), cliquez sur le bouton "Chatroom" du goal que vous avez créé.

### 5. Envoyer des messages
- Tapez votre message dans le champ de texte en bas
- Cliquez sur le bouton d'envoi (avion en papier bleu)
- Le message apparaîtra immédiatement dans la conversation

### 6. Tester avec plusieurs utilisateurs
Pour voir les messages en temps réel:
1. Ouvrez un autre navigateur (ou mode incognito)
2. Connectez-vous avec alice@test.com / password123
3. Rejoignez le même goal
4. Accédez à la chatroom
5. Les messages se rafraîchissent automatiquement toutes les 3 secondes

## Fonctionnalités implémentées

✅ Création de compte automatique
✅ Login avec email/password
✅ Envoi de messages en temps réel
✅ Auto-refresh des messages (toutes les 3 secondes)
✅ Design moderne avec gradient violet
✅ Messages alignés (envoyés à droite, reçus à gauche)
✅ Avatars avec initiales
✅ Timestamps sur chaque message
✅ Liste des participants dans la sidebar

## Problèmes résolus

1. ✅ Hachage correct des mots de passe avec Symfony PasswordHasher
2. ✅ Gestion des tokens CSRF pour les formulaires
3. ✅ Auto-refresh AJAX sans rechargement de page
4. ✅ Redirection vers login si non connecté lors de l'envoi
5. ✅ Messages persistés correctement en base de données
