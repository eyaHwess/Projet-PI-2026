# 🧪 Guide de Test Complet - Système de Goals et Chatroom

## 📋 Préparation des Tests

### 1. Démarrer le Serveur
```bash
symfony server:start
# ou
php -S localhost:8000 -t public
```

### 2. Accéder à l'Application
```
URL: http://localhost:8000
Email: mariemayari@gmail.com
Mot de passe: mariem
```

### 3. Vérifier la Base de Données
```bash
# Voir les rôles actuels
php bin/console dbal:run-sql "SELECT u.email, g.title, gp.role FROM goal_participation gp JOIN user u ON gp.user_id = u.id JOIN goal g ON gp.goal_id = g.id"

# Voir les goals
php bin/console dbal:run-sql "SELECT id, title, status FROM goal"
```

---

## 🎯 PARTIE 1: Système de Rôles dans GoalParticipation

### Test 1.1: Vérifier les Badges de Rôle dans la Liste des Participants

**Étapes:**
1. Se connecter avec mariemayari@gmail.com
2. Accéder à un goal
3. Cliquer sur "Chatroom"
4. Observer la sidebar gauche (liste des participants)

**Résultat Attendu:**
- ✅ Chaque participant a un badge de rôle à côté de son nom
- ✅ Badge OWNER: Jaune/or avec dégradé
- ✅ Badge ADMIN: Bleu (#8b9dc3) avec dégradé
- ✅ Badge MEMBER: Gris

**Capture d'écran:** Sidebar gauche avec badges

---

### Test 1.2: Vérifier les Rôles dans Group Info

**Étapes:**
1. Dans le chatroom, cliquer sur l'icône ℹ️ (Group Info)
2. Ouvrir la section "Members"
3. Observer les rôles affichés sous chaque nom

**Résultat Attendu:**
- ✅ Rôle affiché sous chaque nom (owner/admin/member)
- ✅ Couleurs cohérentes avec les badges

---

### Test 1.3: Tester les Permissions d'Épinglage (ADMIN/OWNER)

**Étapes:**
1. En tant qu'OWNER, survoler un message
2. Cliquer sur l'icône 📌 (épingler)
3. Vérifier que le message apparaît en haut avec fond jaune

**Résultat Attendu:**
- ✅ Message épinglé visible en haut
- ✅ Fond jaune avec icône 📌
- ✅ Bouton "Désépingler" visible

**Test Négatif:**
```bash
# Changer temporairement en MEMBER
php bin/console app:change-role mariemayari@gmail.com 1 MEMBER
```
- ✅ Bouton 📌 n'apparaît plus sur les messages
- ✅ Ne peut pas épingler

**Revenir en OWNER:**
```bash
php bin/console app:change-role mariemayari@gmail.com 1 OWNER
```

---

### Test 1.4: Tester les Permissions de Suppression

**Étapes:**
1. En tant qu'OWNER, survoler un message d'un autre utilisateur
2. Vérifier la présence du bouton 🗑️

**Résultat Attendu:**
- ✅ OWNER voit le bouton 🗑️ sur tous les messages
- ✅ ADMIN voit le bouton 🗑️ sur tous les messages
- ✅ MEMBER voit le bouton 🗑️ seulement sur ses propres messages

**Test:**
```bash
# Tester en tant que MEMBER
php bin/console app:change-role mariemayari@gmail.com 1 MEMBER
# Rafraîchir la page
# Vérifier que le bouton 🗑️ n'apparaît que sur vos messages

# Revenir en OWNER
php bin/console app:change-role mariemayari@gmail.com 1 OWNER
```

---

## 🏢 PARTIE 2: Permissions au Niveau du Goal

### Test 2.1: Boutons Modifier/Supprimer dans la Liste des Goals

**Étapes:**
1. Aller sur la page d'accueil (liste des goals)
2. Observer les boutons sous chaque goal

**Résultat Attendu:**
- ✅ OWNER voit: Chatroom, Détails, **Modifier**, **Supprimer**
- ✅ ADMIN voit: Chatroom, Détails, **Modifier**
- ✅ MEMBER voit: Chatroom, Détails

**Test:**
```bash
# Tester en tant qu'ADMIN
php bin/console app:change-role mariemayari@gmail.com 1 ADMIN
# Rafraîchir la page
# Vérifier: bouton Modifier visible, Supprimer caché

# Tester en tant que MEMBER
php bin/console app:change-role mariemayari@gmail.com 1 MEMBER
# Rafraîchir la page
# Vérifier: boutons Modifier et Supprimer cachés

# Revenir en OWNER
php bin/console app:change-role mariemayari@gmail.com 1 OWNER
```

---

### Test 2.2: Modifier un Goal

**Étapes:**
1. En tant qu'OWNER ou ADMIN
2. Cliquer sur "Modifier" sous un goal
3. Modifier le titre ou la description
4. Cliquer sur "Enregistrer les modifications"

**Résultat Attendu:**
- ✅ Formulaire d'édition s'affiche
- ✅ Champs pré-remplis avec les données actuelles
- ✅ Modifications enregistrées avec succès
- ✅ Message "Goal modifié avec succès!"
- ✅ Redirection vers la liste des goals

---

### Test 2.3: Supprimer un Goal

**Étapes:**
1. En tant qu'OWNER uniquement
2. Cliquer sur "Supprimer" sous un goal
3. Confirmer la suppression dans la popup

**Résultat Attendu:**
- ✅ Popup de confirmation apparaît
- ✅ Message: "Êtes-vous sûr de vouloir supprimer ce goal ? Cette action est irréversible."
- ✅ Après confirmation, goal supprimé
- ✅ Message "Le goal [titre] a été supprimé avec succès"
- ✅ Goal n'apparaît plus dans la liste

---

### Test 2.4: Exclure un Membre

**Étapes:**
1. En tant qu'ADMIN ou OWNER
2. Aller dans le chatroom
3. Ouvrir Group Info (icône ℹ️)
4. Section "Members"
5. Cliquer sur ⋮ à côté d'un membre
6. Choisir "Exclure du goal"
7. Confirmer

**Résultat Attendu:**
- ✅ Menu d'actions s'ouvre
- ✅ Option "Exclure du goal" visible
- ✅ Confirmation demandée
- ✅ Membre exclu avec succès
- ✅ Membre n'apparaît plus dans la liste
- ✅ Message de succès affiché

**Test Négatif:**
- ✅ Bouton ⋮ n'apparaît pas sur soi-même
- ✅ ADMIN ne peut pas exclure OWNER

---

### Test 2.5: Promouvoir un Membre

**Étapes:**
1. En tant qu'OWNER uniquement
2. Aller dans le chatroom
3. Ouvrir Group Info > Members
4. Cliquer sur ⋮ à côté d'un MEMBER
5. Choisir "Promouvoir en Admin"
6. Confirmer

**Résultat Attendu:**
- ✅ Option "Promouvoir en Admin" visible (OWNER uniquement)
- ✅ Confirmation demandée
- ✅ Membre promu avec succès
- ✅ Badge change de MEMBER à ADMIN
- ✅ Message de succès affiché

**Test Inverse:**
```bash
# Créer un deuxième utilisateur pour tester
# Ou utiliser la commande pour changer le rôle d'un participant existant
```

---

## 👥 PARTIE 3: Vérification de Membership

### Test 3.1: Accès Non-Membre au Chatroom

**Étapes:**
1. Se déconnecter
2. Créer un nouveau compte (ou utiliser un autre compte)
3. Accéder directement à un chatroom via URL: `/goal/1/messages`
4. Observer l'interface

**Résultat Attendu:**
- ✅ Messages existants visibles (lecture seule)
- ✅ Liste des participants visible
- ✅ Formulaire d'envoi caché
- ✅ Message affiché: "🔒 Vous n'êtes pas membre de ce goal"
- ✅ Bouton "Rejoindre le goal" visible
- ✅ Pas de badge de rôle dans le header

---

### Test 3.2: Rejoindre un Goal depuis le Chatroom

**Étapes:**
1. En tant que non-membre dans le chatroom
2. Cliquer sur "Rejoindre le goal"
3. Observer les changements

**Résultat Attendu:**
- ✅ Redirection vers la liste des goals ou le chatroom
- ✅ Message "Vous avez rejoint le goal!"
- ✅ Formulaire d'envoi maintenant visible
- ✅ Badge MEMBER apparaît dans le header
- ✅ Peut maintenant envoyer des messages

---

### Test 3.3: Affichage du Rôle dans le Header

**Étapes:**
1. En tant que membre, accéder au chatroom
2. Observer le header (sous le titre du goal)

**Résultat Attendu:**
- ✅ Format: "X participants • status • ROLE"
- ✅ Badge coloré selon le rôle:
  - OWNER: Jaune/or
  - ADMIN: Bleu
  - MEMBER: Gris
- ✅ Badge bien visible et lisible

---

## 💬 PARTIE 4: Fonctionnalités du Chatroom (Déjà Implémentées)

### Test 4.1: Réactions aux Messages

**Étapes:**
1. Survoler un message
2. Cliquer sur une réaction (👍 👏 🔥 ❤️)
3. Vérifier le compteur

**Résultat Attendu:**
- ✅ Réaction ajoutée
- ✅ Compteur incrémenté
- ✅ Bouton devient actif (surligné)
- ✅ Cliquer à nouveau retire la réaction

---

### Test 4.2: Répondre à un Message

**Étapes:**
1. Cliquer sur l'icône 💬 (répondre)
2. Observer la zone de réponse au-dessus de l'input
3. Taper un message
4. Envoyer

**Résultat Attendu:**
- ✅ Aperçu de la réponse affiché
- ✅ Bouton X pour annuler
- ✅ Message envoyé avec référence au message original
- ✅ Référence visible dans le message

---

### Test 4.3: Modifier un Message

**Étapes:**
1. Survoler votre propre message
2. Cliquer sur ✏️ (modifier)
3. Modifier le texte dans le modal
4. Enregistrer

**Résultat Attendu:**
- ✅ Modal d'édition s'ouvre
- ✅ Texte actuel pré-rempli
- ✅ Modifications enregistrées
- ✅ Badge "Edited" apparaît sur le message

---

### Test 4.4: Supprimer un Message

**Étapes:**
1. Cliquer sur 🗑️ sur votre message
2. Choisir "Retirer pour tout le monde" ou "Retirer pour vous"
3. Confirmer

**Résultat Attendu:**
- ✅ Modal avec 2 options s'affiche
- ✅ "Retirer pour tout le monde": Message supprimé de la base
- ✅ "Retirer pour vous": Message caché pour vous uniquement
- ✅ Animation de disparition

---

### Test 4.5: Upload de Fichiers

**Étapes:**
1. Cliquer sur l'icône 📎
2. Sélectionner un fichier (image, PDF, document)
3. Observer l'aperçu
4. Envoyer

**Résultat Attendu:**
- ✅ Aperçu du fichier dans l'input
- ✅ Nom du fichier visible
- ✅ Bouton X pour retirer
- ✅ Fichier envoyé avec succès
- ✅ Image affichée inline
- ✅ Autres fichiers affichés comme carte avec icône

---

### Test 4.6: Message Vocal

**Étapes:**
1. Cliquer sur l'icône 🎤
2. Autoriser l'accès au microphone
3. Parler pendant quelques secondes
4. Cliquer sur "Envoyer"

**Résultat Attendu:**
- ✅ Interface d'enregistrement apparaît
- ✅ Animation des ondes sonores
- ✅ Compteur de temps
- ✅ Boutons Annuler/Envoyer
- ✅ Message vocal envoyé
- ✅ Player audio avec waveform

---

### Test 4.7: Recherche dans les Messages

**Étapes:**
1. Cliquer sur l'icône 🔍 dans le header
2. Taper un mot (minimum 2 caractères)
3. Observer les résultats

**Résultat Attendu:**
- ✅ Barre de recherche apparaît
- ✅ Résultats surlignés en jaune
- ✅ Compteur de résultats affiché
- ✅ Auto-scroll vers le premier résultat
- ✅ Bouton X pour fermer
- ✅ Escape pour fermer

---

### Test 4.8: Emoji Picker

**Étapes:**
1. Cliquer sur l'icône 😊
2. Choisir une catégorie
3. Cliquer sur un emoji
4. Observer l'input

**Résultat Attendu:**
- ✅ Picker s'ouvre avec 4 catégories
- ✅ 420+ emojis disponibles
- ✅ Emoji inséré à la position du curseur
- ✅ Picker se ferme automatiquement

---

### Test 4.9: Messages en Temps Réel

**Étapes:**
1. Ouvrir le chatroom dans 2 onglets différents
2. Envoyer un message dans l'onglet 1
3. Observer l'onglet 2

**Résultat Attendu:**
- ✅ Indicateur "Live" visible dans le header
- ✅ Message apparaît dans l'onglet 2 après 2 secondes max
- ✅ Animation fade-in
- ✅ Pas besoin de rafraîchir

---

### Test 4.10: Group Info Sidebar

**Étapes:**
1. Cliquer sur l'icône ℹ️ dans le header
2. Observer les sections

**Résultat Attendu:**
- ✅ Sidebar s'ouvre à droite
- ✅ Section "Files" avec statistiques
- ✅ Section "Members" avec liste complète
- ✅ Section "Shared Files" avec 10 derniers fichiers
- ✅ Section "Recent Images" avec grille 3x3
- ✅ Toutes les sections dépliables

---

## 🔧 PARTIE 5: Tests de Sécurité

### Test 5.1: Tentative d'Accès Non Autorisé

**Test:**
```bash
# En tant que MEMBER, essayer d'accéder directement à la route de suppression
curl -X POST http://localhost:8000/goal/1/delete \
  -H "Cookie: PHPSESSID=votre_session"
```

**Résultat Attendu:**
- ✅ Erreur 403 ou redirection
- ✅ Message "Seul le propriétaire peut supprimer ce goal"

---

### Test 5.2: Protection CSRF

**Test:**
```bash
# Essayer de supprimer sans token CSRF
curl -X POST http://localhost:8000/goal/1/delete
```

**Résultat Attendu:**
- ✅ Erreur 403 ou 400
- ✅ Token CSRF invalide

---

### Test 5.3: Validation des Rôles

**Test:**
```bash
# Essayer de promouvoir avec un rôle invalide
curl -X POST http://localhost:8000/goal/1/promote-member/2 \
  -d "role=SUPERADMIN"
```

**Résultat Attendu:**
- ✅ Erreur 400
- ✅ Message "Rôle invalide"

---

## 📊 PARTIE 6: Tests de Performance

### Test 6.1: Chargement du Chatroom

**Étapes:**
1. Ouvrir les DevTools (F12)
2. Onglet Network
3. Accéder au chatroom
4. Observer le temps de chargement

**Résultat Attendu:**
- ✅ Page chargée en < 2 secondes
- ✅ Pas d'erreurs dans la console
- ✅ Toutes les ressources chargées

---

### Test 6.2: Polling en Temps Réel

**Étapes:**
1. Ouvrir les DevTools > Network
2. Observer les requêtes AJAX toutes les 2 secondes
3. Vérifier `/goal/{id}/messages/fetch`

**Résultat Attendu:**
- ✅ Requête toutes les 2 secondes
- ✅ Réponse rapide (< 200ms)
- ✅ Pas d'erreurs

---

## 🎨 PARTIE 7: Tests Responsive

### Test 7.1: Mobile (< 768px)

**Étapes:**
1. Ouvrir DevTools (F12)
2. Mode responsive (Ctrl+Shift+M)
3. Sélectionner iPhone ou Android
4. Tester toutes les fonctionnalités

**Résultat Attendu:**
- ✅ Layout adapté
- ✅ Sidebar cachée par défaut
- ✅ Boutons accessibles
- ✅ Texte lisible
- ✅ Formulaire utilisable

---

### Test 7.2: Tablet (768px - 1024px)

**Résultat Attendu:**
- ✅ 2 colonnes (chat + sidebar)
- ✅ Group Info en overlay
- ✅ Tout fonctionnel

---

## 📝 Checklist Finale

### Système de Rôles
- [ ] Badges visibles dans liste participants
- [ ] Badges visibles dans Group Info
- [ ] Rôle affiché dans header du chatroom
- [ ] Permissions épinglage (ADMIN/OWNER)
- [ ] Permissions suppression (ADMIN/OWNER)
- [ ] Permissions modification (auteur uniquement)

### Permissions Goal
- [ ] Bouton Modifier visible (ADMIN/OWNER)
- [ ] Bouton Supprimer visible (OWNER)
- [ ] Modification goal fonctionne
- [ ] Suppression goal fonctionne
- [ ] Exclusion membre fonctionne
- [ ] Promotion membre fonctionne (OWNER)

### Membership
- [ ] Non-membre voit message informatif
- [ ] Non-membre ne voit pas formulaire
- [ ] Non-membre peut voir messages (lecture)
- [ ] Bouton "Rejoindre" fonctionne
- [ ] Après rejoindre, formulaire apparaît

### Chatroom Features
- [ ] Réactions fonctionnent
- [ ] Réponses fonctionnent
- [ ] Modification messages fonctionne
- [ ] Suppression messages fonctionne
- [ ] Upload fichiers fonctionne
- [ ] Messages vocaux fonctionnent
- [ ] Recherche fonctionne
- [ ] Emoji picker fonctionne
- [ ] Temps réel fonctionne
- [ ] Group Info fonctionne

### Sécurité
- [ ] Vérifications côté serveur
- [ ] Protection CSRF
- [ ] Validation des rôles
- [ ] Pas d'accès non autorisé

---

## 🐛 Problèmes Connus et Solutions

### Problème: Badges ne s'affichent pas
**Solution:**
```bash
# Vérifier que la migration est exécutée
php bin/console doctrine:migrations:status

# Vérifier les rôles dans la base
php bin/console dbal:run-sql "SELECT * FROM goal_participation"
```

### Problème: Formulaire caché même pour les membres
**Solution:**
- Vérifier que `isMember` est passé au template
- Vérifier la condition dans le template
- Vider le cache: `php bin/console cache:clear`

### Problème: Boutons d'actions ne fonctionnent pas
**Solution:**
- Vérifier la console JavaScript (F12)
- Vérifier que les routes existent
- Vérifier les tokens CSRF

---

## 📞 Support

Si vous rencontrez des problèmes:
1. Vérifier les logs: `var/log/dev.log`
2. Vérifier la console navigateur (F12)
3. Vérifier la base de données
4. Vider le cache Symfony

---

**Date**: 17 février 2026  
**Version**: 1.0  
**Statut**: Prêt pour la soutenance
