# Guide de Test - Système de Demande d'Accès

## 🎯 Objectif

Tester le système de demande d'accès aux goals avec approbation par les administrateurs.

## 📋 Prérequis

1. Serveur Symfony démarré: `symfony server:start`
2. Base de données à jour: `php bin/console doctrine:migrations:migrate`
3. Au moins 2 utilisateurs:
   - Un utilisateur normal (pour faire la demande)
   - Un utilisateur ADMIN/OWNER d'un goal (pour approuver)

## 🧪 Scénarios de Test

### Scénario 1: Créer une Demande d'Accès

**Utilisateur:** Normal (non-membre)

1. Aller sur `/goals`
2. Trouver un goal dont vous n'êtes pas membre
3. Cliquer sur le bouton **"Rejoindre"**

**Résultat attendu:**
- ✅ Message flash vert: "Demande d'accès envoyée! En attente d'approbation."
- ✅ Le bouton change en **"En attente d'approbation"** (jaune, désactivé)
- ✅ Icône horloge visible sur le bouton

**Vérification base de données:**
```sql
SELECT * FROM goal_participation WHERE status = 'PENDING';
```

---

### Scénario 2: Vue Chatroom en Attente

**Utilisateur:** Même utilisateur (demande PENDING)

1. Cliquer sur le bouton **"Chatroom"** du goal
2. Observer l'interface

**Résultat attendu:**
- ✅ Notice jaune visible avec:
  - Icône horloge animée (pulse)
  - Titre: "Demande en attente d'approbation"
  - Texte explicatif
- ✅ Formulaire d'envoi de message **caché**
- ✅ Messages existants **visibles** (read-only)
- ✅ Sidebar participants visible

**Screenshot recommandé:** Notice d'approbation en attente

---

### Scénario 3: Vue Administrateur

**Utilisateur:** ADMIN ou OWNER du goal

1. Se connecter avec un compte admin
2. Aller dans le chatroom du goal qui a des demandes en attente

**Résultat attendu dans le header:**
- ✅ Badge jaune visible: "🕐 X demande(s)"
- ✅ Badge animé (pulse)
- ✅ Nombre correct de demandes

**Résultat dans Group Info:**
1. Cliquer sur le bouton "Group Info" (ℹ️)
2. Chercher la section **"Demandes en attente (X)"**

- ✅ Section visible (uniquement pour ADMIN/OWNER)
- ✅ Liste des demandes avec:
  - Avatar de l'utilisateur
  - Nom complet
  - Date et heure de la demande
  - Bouton vert "✓" (Accepter)
  - Bouton rouge "✗" (Refuser)

**Screenshot recommandé:** Section demandes en attente

---

### Scénario 4: Approuver une Demande

**Utilisateur:** ADMIN ou OWNER

1. Dans la section "Demandes en attente"
2. Cliquer sur le bouton **vert (✓)** d'une demande

**Résultat attendu:**
- ✅ Popup de confirmation: "Accepter cette demande d'accès ?"
- ✅ Cliquer "OK"
- ✅ Message d'alerte: "[Nom] a été accepté dans le goal"
- ✅ Page se recharge
- ✅ La demande disparaît de la liste
- ✅ Le badge diminue de 1

**Vérification:**
1. Se reconnecter avec l'utilisateur approuvé
2. Aller dans le chatroom
- ✅ Notice jaune disparue
- ✅ Formulaire d'envoi visible
- ✅ Peut envoyer des messages

**Vérification base de données:**
```sql
SELECT * FROM goal_participation WHERE user_id = X AND goal_id = Y;
-- status devrait être 'APPROVED'
```

---

### Scénario 5: Refuser une Demande

**Utilisateur:** ADMIN ou OWNER

1. Créer une nouvelle demande avec un autre utilisateur
2. Dans la section "Demandes en attente"
3. Cliquer sur le bouton **rouge (✗)**

**Résultat attendu:**
- ✅ Popup de confirmation: "Refuser cette demande d'accès ?"
- ✅ Cliquer "OK"
- ✅ Message d'alerte: "Demande de [Nom] refusée"
- ✅ Page se recharge
- ✅ La demande disparaît de la liste
- ✅ Le badge diminue de 1

**Vérification:**
1. Se reconnecter avec l'utilisateur refusé
2. Aller sur `/goals`
- ✅ Le bouton redevient **"Rejoindre"**
- ✅ L'utilisateur peut redemander l'accès

**Vérification base de données:**
```sql
SELECT * FROM goal_participation WHERE user_id = X AND goal_id = Y;
-- Aucun résultat (participation supprimée)
```

---

### Scénario 6: Test des Permissions

**Utilisateur:** MEMBER simple (pas ADMIN/OWNER)

1. Se connecter avec un membre simple
2. Aller dans le chatroom

**Résultat attendu:**
- ✅ Badge "X demande(s)" **NON visible**
- ✅ Section "Demandes en attente" **NON visible** dans Group Info

**Test d'accès direct:**
1. Essayer d'accéder à l'URL directement:
   ```
   POST /goal/1/approve-request/2
   ```

**Résultat attendu:**
- ✅ Erreur 403 ou message "Permission refusée"
- ✅ Aucune modification en base de données

---

### Scénario 7: Demande Déjà en Attente

**Utilisateur:** Utilisateur avec demande PENDING

1. Aller sur `/goals`
2. Essayer de cliquer à nouveau sur "Rejoindre" (si possible)

**Résultat attendu:**
- ✅ Message flash orange: "Votre demande est déjà en attente d'approbation."
- ✅ Aucune nouvelle participation créée

---

### Scénario 8: Utilisateur Déjà Membre

**Utilisateur:** Utilisateur avec participation APPROVED

1. Aller sur `/goals`
2. Observer le bouton

**Résultat attendu:**
- ✅ Bouton **"Quitter"** (rouge) visible
- ✅ Pas de bouton "Rejoindre"

---

## 🎨 Points Visuels à Vérifier

### Design
- [ ] Couleurs cohérentes (jaune/orange pour pending)
- [ ] Animations fluides (pulse sur icône et badge)
- [ ] Boutons bien dimensionnés (36px minimum)
- [ ] Texte lisible
- [ ] Responsive sur mobile

### UX
- [ ] Messages clairs et informatifs
- [ ] Confirmations avant actions importantes
- [ ] Feedback immédiat après actions
- [ ] Navigation intuitive
- [ ] Pas de bugs visuels

---

## 📊 Checklist Complète

### Fonctionnalités
- [ ] Créer une demande d'accès
- [ ] Voir le statut "En attente"
- [ ] Vue read-only du chatroom pour PENDING
- [ ] Badge visible pour ADMIN/OWNER
- [ ] Section demandes visible pour ADMIN/OWNER
- [ ] Approuver une demande
- [ ] Refuser une demande
- [ ] Permissions correctes
- [ ] Pas de demandes multiples
- [ ] Peut redemander après refus

### Interface
- [ ] Notice d'approbation en attente
- [ ] Badge dans le header
- [ ] Section dans Group Info
- [ ] Boutons Accepter/Refuser
- [ ] Animations
- [ ] Messages flash
- [ ] Popups de confirmation

### Sécurité
- [ ] Authentification requise
- [ ] Permissions vérifiées
- [ ] CSRF protection
- [ ] Validation des données
- [ ] Pas d'accès direct aux URLs

---

## 🐛 Problèmes Potentiels

### Si le bouton reste "Rejoindre"
- Vérifier que la migration a été exécutée
- Vérifier le statut en base de données
- Vider le cache: `php bin/console cache:clear`

### Si le badge n'apparaît pas
- Vérifier que l'utilisateur est ADMIN ou OWNER
- Vérifier qu'il y a des demandes PENDING
- Vérifier la console JavaScript pour erreurs

### Si l'approbation ne fonctionne pas
- Vérifier la console réseau (F12)
- Vérifier les logs Symfony: `tail -f var/log/dev.log`
- Vérifier les permissions de l'utilisateur

---

## 📸 Screenshots Recommandés pour la Soutenance

1. **Liste des goals** - Bouton "En attente d'approbation"
2. **Chatroom PENDING** - Notice jaune d'approbation
3. **Header admin** - Badge "X demande(s)"
4. **Group Info** - Section demandes en attente
5. **Après approbation** - Utilisateur peut participer
6. **Après refus** - Bouton redevient "Rejoindre"

---

## ✅ Validation Finale

Avant la soutenance, vérifier:
- [ ] Tous les scénarios testés
- [ ] Aucune erreur dans les logs
- [ ] Design cohérent et professionnel
- [ ] Animations fluides
- [ ] Messages clairs
- [ ] Permissions correctes
- [ ] Base de données propre

---

**Temps de test estimé:** 30 minutes  
**Nombre de scénarios:** 8  
**Niveau de difficulté:** Moyen

**Bon test! 🚀**
