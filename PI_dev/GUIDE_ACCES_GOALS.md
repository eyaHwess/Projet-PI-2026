# Guide d'Accès aux Goals et Chatrooms

## ✅ PROBLÈME RÉSOLU

Le problème SQL avec le paramètre "login" a été corrigé en ajoutant des contraintes de route qui n'acceptent que des valeurs numériques pour les IDs.

## 🎯 Comment Accéder aux Goals

### 1. Page d'Accueil des Goals
```
URL: /goals
Route: goal_list
```
Cette page affiche tous les goals disponibles.

### 2. Créer un Compte (si nécessaire)
```
URL: /register
```
Ou utilisez le compte de démonstration:
- Email: mariemayari@gmail.com
- Mot de passe: mariem

### 3. Se Connecter
```
URL: /login
Route: app_login
```

### 4. Rejoindre un Goal
Sur la page `/goals`, cliquez sur "Rejoindre" pour un goal.
- Votre demande sera en attente d'approbation
- Un administrateur ou propriétaire doit approuver votre demande

### 5. Accéder au Chatroom
Une fois votre demande approuvée:
```
URL: /message/chatroom/{goalId}
Route: message_chatroom
```
Remplacez `{goalId}` par l'ID numérique du goal (ex: /message/chatroom/1)

## 🔒 Sécurité d'Accès

Le système vérifie 3 niveaux de sécurité:

1. **Authentification**: Vous devez être connecté
2. **Membership**: Vous devez être membre du goal
3. **Approbation**: Votre participation doit être STATUS_APPROVED

## 🚫 Erreurs Courantes

### Erreur: "Invalid text representation: integer: login"
**Cause**: Tentative d'accès à une URL avec un texte au lieu d'un ID numérique
**Solution**: Utilisez toujours des IDs numériques (ex: /goal/1, pas /goal/login)

### Erreur: "Vous devez rejoindre ce goal"
**Cause**: Vous n'êtes pas membre du goal
**Solution**: Allez sur /goals et cliquez sur "Rejoindre"

### Erreur: "Votre demande est en attente"
**Cause**: Votre demande n'a pas encore été approuvée
**Solution**: Attendez qu'un administrateur approuve votre demande

## 📋 Routes Principales

| Action | URL | Authentification |
|--------|-----|------------------|
| Liste des goals | `/goals` | Non requise |
| Créer un goal | `/goal/new` | Requise |
| Rejoindre un goal | `/goal/{id}/join` | Requise |
| Quitter un goal | `/goal/{id}/leave` | Requise |
| Chatroom | `/message/chatroom/{goalId}` | Requise + Membre approuvé |
| Recherche messages | `/message/chatroom/{goalId}/search` | Requise + Membre approuvé |

## 🔧 Corrections Appliquées

1. ✅ Ajout de contraintes de route `requirements: ['id' => '\d+']` sur tous les contrôleurs
2. ✅ Correction de la méthode `findByUser()` dans GoalRepository
3. ✅ Cache Symfony vidé pour appliquer les changements

## 🎉 Résultat

Maintenant, les URLs avec des textes (comme /goal/login) retourneront une erreur 404 propre au lieu d'une erreur SQL, et les routes /login, /register, etc. fonctionneront correctement sans conflit.
