# 🚀 Démarrage Rapide - Système de Coaching

## ✅ Étapes d'installation (DÉJÀ FAITES)

- [x] Migration de la base de données exécutée
- [x] Champ `message` ajouté à la table `coaching_request`
- [x] Routes configurées
- [x] Contrôleurs créés
- [x] Templates créés
- [x] Formulaire de demande créé
- [x] Cache vidé

## 🎯 Comment tester maintenant

### Option 1: Version DEMO (Sans authentification) ⭐ RECOMMANDÉ

#### Pour tester la vue UTILISATEUR:
```
http://localhost:8000/demo/coaches
```
**Ce que vous verrez:**
- Liste de 6 coaches avec spécialités
- Filtres par spécialité (Fitness, Yoga, Musculation, etc.)
- Formulaire pour envoyer une demande
- Exemples de demandes avec différents statuts

#### Pour tester la vue COACH:
```
http://localhost:8000/demo/coach/requests
```
**Ce que vous verrez:**
- 3 demandes en attente (fond jaune)
- Messages complets des utilisateurs
- Boutons Accepter/Refuser
- Historique de toutes les demandes

---

### Option 2: Version RÉELLE (Avec base de données)

#### Étape 1: Créer des utilisateurs de test

Exécutez ces commandes pour créer des utilisateurs:

```bash
cd PI_dev
php bin/console doctrine:fixtures:load
```

OU créez manuellement via l'interface d'inscription.

#### Étape 2: Tester en tant qu'UTILISATEUR

1. Connectez-vous avec un compte utilisateur
2. Allez sur: `http://localhost:8000/coaches`
3. Vous verrez:
   - Liste des coaches disponibles
   - Formulaire de demande
   - Vos demandes envoyées

4. Remplissez le formulaire:
   - Sélectionnez un coach
   - Écrivez un message (min 10 caractères)
   - Cliquez sur "Envoyer la demande"

5. Vérifiez:
   - Badge "Demande en attente" sur la carte du coach
   - Votre demande apparaît dans "Mes demandes de coaching"

#### Étape 3: Tester en tant que COACH

1. Connectez-vous avec un compte coach (ROLE_COACH)
2. Allez sur: `http://localhost:8000/coach/requests`
3. Vous verrez:
   - Demandes en attente (fond jaune)
   - Messages des utilisateurs
   - Boutons Accepter/Refuser

4. Testez les actions:
   - Cliquez sur "Accepter" → La demande passe à "ACCEPTÉE"
   - Cliquez sur "Refuser" → La demande passe à "REFUSÉE"

---

## 🔧 Commandes utiles

### Vider le cache
```bash
php bin/console cache:clear
```

### Voir les routes
```bash
php bin/console debug:router | findstr coach
```

### Voir le statut des migrations
```bash
php bin/console doctrine:migrations:status
```

### Créer un utilisateur coach manuellement

Connectez-vous à votre base de données et exécutez:

```sql
-- Créer un coach
INSERT INTO user (first_name, last_name, email, password, roles, status, created_at)
VALUES (
    'Sarah',
    'Martin',
    'sarah.coach@test.com',
    '$2y$13$hashedpassword', -- Utilisez un vrai hash
    '["ROLE_USER","ROLE_COACH"]',
    'ACTIVE',
    NOW()
);

-- Ajouter une spécialité
UPDATE user SET speciality = 'Fitness' WHERE email = 'sarah.coach@test.com';
```

---

## 📋 Routes disponibles

### Pour les UTILISATEURS:
- `GET /coaches` - Liste des coaches + formulaire de demande
- `GET /coaches/schedule` - Planning des sessions

### Pour les COACHES:
- `GET /coach/requests` - Voir les demandes reçues
- `POST /coach/requests/{id}/accept` - Accepter une demande
- `POST /coach/requests/{id}/decline` - Refuser une demande

### DEMO (sans authentification):
- `GET /demo/coaches` - Vue utilisateur (statique)
- `GET /demo/coach/requests` - Vue coach (statique)

---

## 🐛 Résolution de problèmes

### Erreur: "Access Denied. The user doesn't have ROLE_USER"

**Solution:**
1. Vérifiez que vous êtes connecté
2. OU utilisez les routes `/demo/*` qui ne nécessitent pas d'authentification

### Erreur: "An exception occurred while executing a query: SQLSTATE[42S22]: Column not found: 1054 Unknown column 'message'"

**Solution:**
```bash
php bin/console doctrine:migrations:migrate
```

### Les coaches n'apparaissent pas

**Solution:**
1. Vérifiez qu'il y a des utilisateurs avec `ROLE_COACH` dans la base
2. Vérifiez avec:
```sql
SELECT * FROM user WHERE roles LIKE '%ROLE_COACH%';
```

### Le formulaire ne s'affiche pas

**Solution:**
1. Vérifiez que le formulaire `CoachingRequestType` existe
2. Videz le cache: `php bin/console cache:clear`

---

## 🎨 Personnalisation

### Changer la couleur principale

Dans les templates, modifiez:
```css
:root { --orange-primary: #f97316; }
```

### Ajouter des spécialités

Modifiez la colonne `speciality` dans la table `user`:
```sql
UPDATE user SET speciality = 'Nouvelle Spécialité' WHERE id = X;
```

---

## 📞 Support

Si vous rencontrez des problèmes:
1. Vérifiez les logs: `var/log/dev.log`
2. Vérifiez la console du navigateur (F12)
3. Testez d'abord avec les routes `/demo/*`

---

## ✨ Prochaines étapes

Une fois que tout fonctionne:
1. Créer des fixtures pour les données de test
2. Ajouter des notifications par email
3. Améliorer le système de filtres
4. Ajouter la pagination
5. Créer un tableau de bord pour les coaches
