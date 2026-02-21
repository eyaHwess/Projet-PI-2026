# Guide de Test Complet - Intégration Trello

## Vue d'ensemble
Ce guide fournit un scénario de test end-to-end pour vérifier que l'intégration Trello fonctionne correctement avec l'entité Goal.

---

## Prérequis

### 1. Configuration Trello
- Compte Trello actif
- API Key et Token configurés dans `.env`:
  ```
  TRELLO_API_KEY=votre_api_key
  TRELLO_API_TOKEN=votre_api_token
  ```

### 2. Base de données
- Migration exécutée pour ajouter les champs `progress`, `requiredTasks`, et `trelloBoardId`
- Serveur Symfony en cours d'exécution

---

## Phase 1: Préparation du Board Trello

### Étape 1.1: Créer un Board de Test
1. Connectez-vous à Trello (https://trello.com)
2. Créez un nouveau board nommé "Test Goal Integration"
3. Notez l'ID du board:
   - Ouvrez le board
   - Dans l'URL, copiez l'ID: `https://trello.com/b/BOARD_ID/nom-du-board`
   - Exemple: `655a44217f0176cbc8a0c4f0`

### Étape 1.2: Créer les Listes
Créez les listes suivantes dans cet ordre:
1. **To Do** (liste des tâches à faire)
2. **In Progress** (tâches en cours)
3. **Done** (tâches terminées) ⚠️ Nom exact important (case-insensitive)

### Étape 1.3: Créer des Cartes de Test

#### Scénario A: Test de Progression Partielle
- Créez 5 cartes dans "To Do":
  - "Tâche 1: Configuration initiale"
  - "Tâche 2: Développement API"
  - "Tâche 3: Tests unitaires"
  - "Tâche 4: Documentation"
  - "Tâche 5: Déploiement"

- Déplacez 2 cartes dans "Done":
  - "Tâche 1: Configuration initiale"
  - "Tâche 2: Développement API"

**État attendu**: 2 cartes dans Done, 3 cartes restantes

---

## Phase 2: Préparation de la Base de Données

### Étape 2.1: Créer un Goal de Test

#### Option A: Via SQL Direct
```sql
INSERT INTO goal (
    title, 
    description, 
    start_date, 
    end_date, 
    status, 
    user_id, 
    created_at, 
    progress, 
    required_tasks, 
    trello_board_id
) VALUES (
    'Développement Module Trello',
    'Intégrer Trello avec le système de goals',
    '2024-01-01',
    '2024-12-31',
    'active',
    1,  -- Remplacez par un user_id valide
    NOW(),
    0,
    5,  -- 5 tâches requises
    '655a44217f0176cbc8a0c4f0'  -- Remplacez par votre BOARD_ID
);
```

#### Option B: Via Symfony Console (Recommandé)
Créez un fichier de commande temporaire ou utilisez l'interface existante.

### Étape 2.2: Vérifier le Goal Créé
```sql
SELECT id, title, status, progress, required_tasks, trello_board_id 
FROM goal 
WHERE title = 'Développement Module Trello';
```

**Résultat attendu**:
```
id | title                      | status | progress | required_tasks | trello_board_id
1  | Développement Module Trello| active | 0        | 5              | 655a44217f0176cbc8a0c4f0
```

---

## Phase 3: Tests Fonctionnels

### Test 1: Synchronisation avec Progression Partielle

#### Contexte
- Board Trello: 2 cartes dans "Done", 3 cartes ailleurs
- Goal: requiredTasks = 5, progress = 0, status = 'active'

#### Exécution
```bash
curl -X GET http://localhost:8000/sync-goal/1
```

Ou via navigateur:
```
http://localhost:8000/sync-goal/1
```

#### Résultat Attendu (JSON)
```json
{
    "done_count": 2,
    "goal_status": "active",
    "goal_progress": 40
}
```

#### Vérification Base de Données
```sql
SELECT status, progress FROM goal WHERE id = 1;
```

**Attendu**: `status = 'active'`, `progress = 40`

#### Calcul de Progression
- Formule: `(done_count / required_tasks) * 100`
- Calcul: `(2 / 5) * 100 = 40%`

---

### Test 2: Synchronisation avec Complétion Totale

#### Préparation
1. Dans Trello, déplacez 3 cartes supplémentaires dans "Done"
2. Total dans "Done": 5 cartes

#### Exécution
```bash
curl -X GET http://localhost:8000/sync-goal/1
```

#### Résultat Attendu (JSON)
```json
{
    "done_count": 5,
    "goal_status": "completed",
    "goal_progress": 100
}
```

#### Vérification Base de Données
```sql
SELECT status, progress FROM goal WHERE id = 1;
```

**Attendu**: `status = 'completed'`, `progress = 100`

#### Points de Validation
✅ `done_count >= required_tasks` (5 >= 5)  
✅ Status changé de 'active' à 'completed'  
✅ Progress mis à 100

---

### Test 3: Dépassement du Nombre Requis

#### Préparation
1. Ajoutez 2 cartes supplémentaires dans Trello
2. Déplacez-les dans "Done"
3. Total dans "Done": 7 cartes

#### Exécution
```bash
curl -X GET http://localhost:8000/sync-goal/1
```

#### Résultat Attendu (JSON)
```json
{
    "done_count": 7,
    "goal_status": "completed",
    "goal_progress": 100
}
```

#### Points de Validation
✅ Progress reste à 100 (pas de dépassement)  
✅ Status reste 'completed'  
✅ Système gère correctement le dépassement

---

### Test 4: Goal sans Trello Board ID

#### Préparation
Créez un nouveau goal sans `trello_board_id`:
```sql
INSERT INTO goal (title, start_date, end_date, status, user_id, created_at, progress, required_tasks)
VALUES ('Goal sans Trello', '2024-01-01', '2024-12-31', 'active', 1, NOW(), 0, 5);
```

#### Exécution
```bash
curl -X GET http://localhost:8000/sync-goal/2
```

#### Résultat Attendu (JSON)
```json
{
    "error": "Goal does not have a Trello board ID configured"
}
```

#### Code HTTP Attendu
`400 Bad Request`

---

### Test 5: Goal ID Invalide

#### Exécution
```bash
curl -X GET http://localhost:8000/sync-goal/99999
```

#### Résultat Attendu (JSON)
```json
{
    "error": "Goal not found"
}
```

#### Code HTTP Attendu
`404 Not Found`

---

### Test 6: Board sans Liste "Done"

#### Préparation
1. Dans Trello, renommez la liste "Done" en "Terminé"
2. Le système ne devrait plus trouver la liste

#### Exécution
```bash
curl -X GET http://localhost:8000/sync-goal/1
```

#### Résultat Attendu (JSON)
```json
{
    "error": "Done list not found on the Trello board"
}
```

#### Code HTTP Attendu
`404 Not Found`

#### Restauration
Renommez "Terminé" en "Done" pour les tests suivants

---

### Test 7: Variations de Casse pour "Done"

#### Préparation
Testez différentes variations de la liste Done:
- "done" (minuscules)
- "DONE" (majuscules)
- "Done" (mixte)
- "DoNe" (mixte aléatoire)

#### Exécution
Pour chaque variation, renommez la liste et exécutez:
```bash
curl -X GET http://localhost:8000/sync-goal/1
```

#### Résultat Attendu
✅ Toutes les variations doivent fonctionner (case-insensitive)  
✅ JSON retourne les données correctement

---

### Test 8: Goal sans Required Tasks

#### Préparation
```sql
UPDATE goal SET required_tasks = NULL WHERE id = 1;
```

#### Exécution
```bash
curl -X GET http://localhost:8000/sync-goal/1
```

#### Résultat Attendu (JSON)
```json
{
    "done_count": 5,
    "goal_status": "active",
    "goal_progress": 5
}
```

#### Points de Validation
✅ Progress = done_count (pas de calcul de pourcentage)  
✅ Status ne change pas automatiquement

---

## Phase 4: Tests de Régression

### Test 9: Synchronisation Multiple

#### Scénario
Exécutez la synchronisation 3 fois de suite sans modifier Trello

#### Exécution
```bash
curl -X GET http://localhost:8000/sync-goal/1
curl -X GET http://localhost:8000/sync-goal/1
curl -X GET http://localhost:8000/sync-goal/1
```

#### Résultat Attendu
✅ Les 3 appels retournent les mêmes valeurs  
✅ Pas de duplication de données  
✅ Idempotence respectée

---

### Test 10: Transition de Statut (Régression)

#### Scénario
Tester la transition: active → completed → active

#### Étape 1: Complétion
1. Mettez 5 cartes dans "Done"
2. Synchronisez: `curl -X GET http://localhost:8000/sync-goal/1`
3. Vérifiez: `status = 'completed'`, `progress = 100`

#### Étape 2: Régression
1. Déplacez 2 cartes hors de "Done" (vers "To Do")
2. Synchronisez: `curl -X GET http://localhost:8000/sync-goal/1`
3. Vérifiez: `status = 'active'`, `progress = 60`

#### Résultat Attendu
```json
{
    "done_count": 3,
    "goal_status": "active",
    "goal_progress": 60
}
```

⚠️ **Note**: Vérifiez si votre logique métier autorise cette régression

---

## Phase 5: Tests de Performance

### Test 11: Board avec Nombreuses Cartes

#### Préparation
1. Créez 100 cartes dans le board Trello
2. Placez 50 cartes dans "Done"

#### Exécution
```bash
time curl -X GET http://localhost:8000/sync-goal/1
```

#### Points de Validation
✅ Temps de réponse < 3 secondes  
✅ Calcul correct: `(50 / 100) * 100 = 50%`  
✅ Pas d'erreur de timeout

---

## Phase 6: Checklist de Démonstration

### Préparation Avant Démo (15 minutes)

#### ✅ Environnement
- [ ] Serveur Symfony démarré
- [ ] Base de données accessible
- [ ] Variables d'environnement Trello configurées
- [ ] Board Trello préparé et visible

#### ✅ Données de Test
- [ ] Goal créé avec `required_tasks = 5`
- [ ] Board Trello avec listes: To Do, In Progress, Done
- [ ] 5 cartes créées (2 dans Done, 3 ailleurs)

#### ✅ Outils
- [ ] Postman ou curl prêt
- [ ] Navigateur avec Trello ouvert
- [ ] Client SQL pour vérifications

### Scénario de Démonstration (10 minutes)

#### Partie 1: État Initial (2 min)
1. Montrer le board Trello (2 cartes dans Done)
2. Montrer le goal dans la base de données (`progress = 0`)
3. Expliquer l'objectif: synchroniser automatiquement

#### Partie 2: Première Synchronisation (3 min)
1. Exécuter: `GET /sync-goal/1`
2. Montrer la réponse JSON: `done_count: 2, progress: 40`
3. Vérifier la base de données: `progress = 40`
4. Expliquer le calcul: `(2/5) * 100 = 40%`

#### Partie 3: Progression vers Complétion (3 min)
1. Dans Trello, déplacer 3 cartes vers "Done" (total: 5)
2. Exécuter: `GET /sync-goal/1`
3. Montrer la réponse: `done_count: 5, status: completed, progress: 100`
4. Vérifier la base de données: `status = 'completed'`

#### Partie 4: Gestion d'Erreurs (2 min)
1. Tester avec ID invalide: `GET /sync-goal/99999`
2. Montrer l'erreur: `Goal not found`
3. Expliquer la robustesse du système

---

## Phase 7: Résultats Attendus - Tableau Récapitulatif

| Test | Cartes Done | Required Tasks | Progress Attendu | Status Attendu | Code HTTP |
|------|-------------|----------------|------------------|----------------|-----------|
| 1    | 2           | 5              | 40               | active         | 200       |
| 2    | 5           | 5              | 100              | completed      | 200       |
| 3    | 7           | 5              | 100              | completed      | 200       |
| 4    | N/A         | 5              | N/A              | N/A            | 400       |
| 5    | N/A         | N/A            | N/A              | N/A            | 404       |
| 6    | N/A         | 5              | N/A              | N/A            | 404       |
| 8    | 5           | NULL           | 5                | active         | 200       |
| 10   | 3           | 5              | 60               | active         | 200       |

---

## Phase 8: Validation Finale

### Checklist de Validation Complète

#### ✅ Fonctionnalités Core
- [ ] Synchronisation basique fonctionne
- [ ] Calcul de progression correct
- [ ] Transition de statut (active → completed)
- [ ] Détection case-insensitive de "Done"

#### ✅ Gestion d'Erreurs
- [ ] Goal inexistant retourne 404
- [ ] Goal sans board ID retourne 400
- [ ] Board sans liste "Done" retourne 404

#### ✅ Edge Cases
- [ ] Goal sans required_tasks géré
- [ ] Dépassement du nombre requis géré
- [ ] Synchronisations multiples idempotentes

#### ✅ Performance
- [ ] Temps de réponse acceptable
- [ ] Gestion de nombreuses cartes

---

## Commandes Utiles

### Vérifier les Logs Symfony
```bash
tail -f var/log/dev.log
```

### Réinitialiser un Goal pour Retester
```sql
UPDATE goal 
SET progress = 0, status = 'active' 
WHERE id = 1;
```

### Vérifier la Configuration Trello
```bash
php bin/console debug:container --env-vars | grep TRELLO
```

### Tester la Connectivité Trello API
```bash
curl "https://api.trello.com/1/members/me/boards?key=YOUR_KEY&token=YOUR_TOKEN"
```

---

## Troubleshooting

### Erreur: "Done list not found"
**Solution**: Vérifiez que la liste s'appelle exactement "Done" (case-insensitive)

### Erreur: "Goal does not have a Trello board ID"
**Solution**: Vérifiez que `trello_board_id` est renseigné dans la base de données

### Progress ne se met pas à jour
**Solution**: Vérifiez que `EntityManager->flush()` est bien appelé

### Erreur 401 Unauthorized de Trello
**Solution**: Vérifiez vos credentials Trello dans `.env`

---

## Conclusion

Ce guide couvre tous les aspects de l'intégration Trello:
- ✅ Tests fonctionnels complets
- ✅ Gestion d'erreurs robuste
- ✅ Edge cases identifiés
- ✅ Scénario de démonstration prêt
- ✅ Documentation pour présentation académique

**Temps total estimé pour exécuter tous les tests**: 45-60 minutes
