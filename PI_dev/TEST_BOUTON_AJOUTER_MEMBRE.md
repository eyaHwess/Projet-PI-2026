# 🧪 Test du Bouton "Ajouter un Membre"

## ✅ Prérequis

1. **Avoir un compte admin/modérateur** du goal
2. **Avoir un chatroom** lié à un goal
3. **Avoir d'autres utilisateurs** dans la base de données

## 📋 Étapes de Test

### Test 1 : Visibilité du Bouton

1. **Connecte-toi** avec un compte admin/owner du goal
2. **Ouvre le chatroom**
3. **Vérifie** que le bouton avec l'icône `👤+` est visible dans le header
4. **Survole** le bouton → tooltip "Ajouter un membre" doit apparaître

**Résultat attendu** : ✅ Bouton visible pour admin/modérateur

---

### Test 2 : Ouverture du Modal

1. **Clique** sur le bouton "Ajouter un membre"
2. **Vérifie** qu'un modal s'ouvre avec :
   - Titre : "Ajouter un membre"
   - Champ de recherche
   - Section "OU"
   - Lien d'invitation

**Résultat attendu** : ✅ Modal s'ouvre avec animation

---

### Test 3 : Recherche d'Utilisateurs

1. **Dans le modal**, tape dans le champ de recherche (ex: "mar")
2. **Attends 300ms** (debounce)
3. **Vérifie** que :
   - Un spinner "Recherche..." apparaît
   - Les résultats s'affichent avec :
     - Avatar avec initiales
     - Nom complet
     - Email
     - Bouton "Ajouter"

**Résultat attendu** : ✅ Résultats de recherche affichés

---

### Test 4 : Ajout d'un Membre

1. **Dans les résultats**, clique sur "Ajouter" pour un utilisateur
2. **Vérifie** que :
   - Le bouton devient "Ajout..." avec spinner
   - Puis devient "Ajouté" en vert
   - Une alerte de succès apparaît
   - La page se recharge après 1 seconde

**Résultat attendu** : ✅ Membre ajouté avec succès

---

### Test 5 : Vérification en Base de Données

```sql
-- Vérifier que le membre a été ajouté
SELECT * FROM goal_participation 
WHERE goal_id = [ID_DU_GOAL] 
AND user_id = [ID_UTILISATEUR_AJOUTE];
```

**Résultat attendu** : 
- ✅ Nouvelle ligne dans `goal_participation`
- ✅ `role` = 'MEMBER'
- ✅ `status` = 'APPROVED'
- ✅ `joined_at` = date actuelle

---

### Test 6 : Vérification dans l'Interface

1. **Recharge la page** du chatroom
2. **Ouvre la sidebar** "Members"
3. **Vérifie** que le nouveau membre apparaît dans la liste

**Résultat attendu** : ✅ Nouveau membre visible dans la liste

---

### Test 7 : Accès du Nouveau Membre

1. **Déconnecte-toi**
2. **Connecte-toi** avec le compte du membre ajouté
3. **Va sur la liste des goals**
4. **Vérifie** que le goal apparaît dans "Mes Goals"
5. **Clique** sur le goal
6. **Vérifie** que tu peux accéder au chatroom
7. **Essaie** d'envoyer un message

**Résultat attendu** : ✅ Le nouveau membre peut accéder et utiliser le chatroom

---

### Test 8 : Lien d'Invitation

1. **Ouvre le modal** "Ajouter un membre"
2. **Clique** sur "Copier" le lien d'invitation
3. **Vérifie** que :
   - Le bouton devient vert "Copié !"
   - Le lien est dans le presse-papiers
4. **Ouvre** le lien dans un nouvel onglet
5. **Vérifie** que la page du goal s'affiche
6. **Clique** sur "Rejoindre le goal"

**Résultat attendu** : ✅ Lien copié et fonctionnel

---

### Test 9 : Permissions (Membre Simple)

1. **Connecte-toi** avec un compte membre simple (pas admin)
2. **Ouvre le chatroom**
3. **Vérifie** que le bouton "Ajouter un membre" n'est PAS visible

**Résultat attendu** : ✅ Bouton caché pour les membres simples

---

### Test 10 : Gestion des Erreurs

#### Test 10.1 : Utilisateur déjà membre
1. **Recherche** un utilisateur déjà membre
2. **Vérifie** qu'il n'apparaît PAS dans les résultats

**Résultat attendu** : ✅ Membres existants filtrés

#### Test 10.2 : Recherche sans résultat
1. **Tape** "zzzzzzzzz" dans la recherche
2. **Vérifie** le message "Aucun utilisateur trouvé"

**Résultat attendu** : ✅ Message approprié affiché

#### Test 10.3 : Recherche trop courte
1. **Tape** "a" (1 caractère)
2. **Vérifie** qu'aucune recherche n'est lancée

**Résultat attendu** : ✅ Minimum 2 caractères requis

---

## 🐛 Dépannage

### Problème : Le bouton n'apparaît pas

**Causes possibles** :
1. Tu n'es pas admin/modérateur du goal
2. La variable `userParticipation` est null
3. La méthode `canModerate()` retourne false

**Solution** :
```sql
-- Vérifier ton rôle
SELECT * FROM goal_participation 
WHERE goal_id = [ID_GOAL] 
AND user_id = [TON_USER_ID];

-- Le rôle doit être 'OWNER' ou 'ADMIN'
```

---

### Problème : Le modal ne s'ouvre pas

**Causes possibles** :
1. Erreur JavaScript dans la console
2. ID du modal incorrect

**Solution** :
1. Ouvre la console du navigateur (F12)
2. Vérifie les erreurs JavaScript
3. Vérifie que `addMemberModal` existe dans le DOM

---

### Problème : La recherche ne fonctionne pas

**Causes possibles** :
1. Route `chatroom_search_users` non trouvée
2. Erreur 403 (pas de permissions)
3. Erreur 500 (problème serveur)

**Solution** :
1. Vérifie la console réseau (F12 → Network)
2. Vérifie la réponse de l'API
3. Vérifie les logs Symfony :
```bash
tail -f var/log/dev.log
```

---

### Problème : L'ajout échoue

**Causes possibles** :
1. Route `chatroom_add_member` non trouvée
2. Utilisateur déjà membre
3. Pas de permissions

**Solution** :
1. Vérifie la console réseau
2. Vérifie le message d'erreur retourné
3. Vérifie les logs Symfony

---

## 📊 Checklist Complète

- [ ] Bouton visible pour admin/modérateur
- [ ] Bouton caché pour membre simple
- [ ] Modal s'ouvre au clic
- [ ] Recherche fonctionne (min 2 caractères)
- [ ] Résultats affichés correctement
- [ ] Membres existants filtrés
- [ ] Bouton "Ajouter" fonctionne
- [ ] Feedback visuel (loading, succès)
- [ ] Entrée créée en base de données
- [ ] Nouveau membre visible dans la liste
- [ ] Nouveau membre peut accéder au chatroom
- [ ] Lien d'invitation fonctionne
- [ ] Gestion des erreurs appropriée

---

## 🎯 Résultat Final

Si tous les tests passent :
- ✅ Le système d'ajout de membres fonctionne parfaitement
- ✅ Les permissions sont correctement gérées
- ✅ La base de données est mise à jour
- ✅ L'interface utilisateur est réactive et claire

**Le bouton "Ajouter un membre" est opérationnel ! 🚀**
