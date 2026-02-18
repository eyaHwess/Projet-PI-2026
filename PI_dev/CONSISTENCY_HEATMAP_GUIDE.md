# Consistency Heatmap - Guide d'utilisation

## Vue d'ensemble

Le **Consistency Heatmap** est une fonctionnalité avancée inspirée du graphique de contributions GitHub qui permet de visualiser votre productivité quotidienne sur une année complète.

## Fonctionnalités principales

### 1. Heatmap annuel (GitHub-style)
- Visualisation de 52 semaines × 7 jours
- Chaque cellule représente un jour de l'année
- Codage couleur basé sur le taux de complétion :
  - **Gris** (#f3f4f6) : Aucune activité
  - **Rouge** (#ef4444) : 0% de complétion
  - **Vert clair** (#86efac) : 1-49% de complétion
  - **Vert moyen** (#22c55e) : 50-79% de complétion
  - **Vert foncé** (#15803d) : 80-100% de complétion

### 2. Statistiques clés

#### Consistency Score
- Moyenne du taux de complétion sur les 30 derniers jours
- Indicateur de régularité dans vos efforts

#### Longest Streak
- Plus longue série de jours consécutifs avec au moins une activité complétée
- Mesure votre constance

#### Most Productive Day
- Jour de la semaine où vous êtes le plus productif
- Calculé sur les 90 derniers jours

#### Trend
- **Improving** : Progression > 10% entre les 2 dernières semaines
- **Stable** : Variation entre -10% et +10%
- **Decreasing** : Régression > 10%

### 3. Comparaison mensuelle
- Graphique en barres montrant la moyenne de complétion par mois
- Permet d'identifier les mois les plus productifs
- Visualisation rapide des tendances annuelles

### 4. Détails quotidiens
- Cliquez sur n'importe quelle cellule du heatmap pour voir les détails du jour
- Modal affichant :
  - Taux de complétion
  - Nombre d'activités complétées / total
  - Nombre de routines complétées / total

### 5. Tooltips interactifs
- Survolez une cellule pour voir un aperçu rapide
- Affiche la date, le pourcentage et les statistiques

## Architecture technique

### Entités
- **DailyActivityLog** : Stocke les données quotidiennes par utilisateur
  - `log_date` : Date du log
  - `total_activities` : Nombre total d'activités
  - `completed_activities` : Nombre d'activités complétées
  - `total_routines` : Nombre total de routines
  - `completed_routines` : Nombre de routines complétées
  - `completion_percentage` : Pourcentage calculé automatiquement

### Services
- **ConsistencyTracker** : Service principal pour gérer les logs
  - `updateDailyLog()` : Met à jour le log pour une date donnée
  - `generateYearlyHeatmap()` : Génère les données du heatmap
  - `getConsistencyStats()` : Calcule les statistiques
  - `getLogsBetweenDates()` : Récupère les logs entre deux dates

### Repository
- **DailyActivityLogRepository** : Requêtes avancées
  - `calculateConsistencyScore()` : Score de consistance
  - `findLongestStreak()` : Plus longue série
  - `findMostProductiveWeekday()` : Jour le plus productif
  - `calculateTrend()` : Tendance

## Utilisation

### Accès
1. Depuis la page "Mes Objectifs"
2. Cliquez sur le bouton "Consistency" dans le header
3. Ou accédez directement à `/consistency/heatmap`

### Navigation
- Utilisez les boutons de navigation pour changer d'année
- Cliquez sur une cellule pour voir les détails du jour
- Survolez les barres mensuelles pour voir les moyennes

### Mise à jour des données
Les données sont mises à jour automatiquement lorsque :
- Une activité est complétée
- Une routine est terminée
- Un goal est mis à jour

Pour peupler des données de test :
```bash
php bin/console app:populate-consistency-data
```

## Logique métier

### Calcul du pourcentage de complétion
```
completion_percentage = (completed_activities / total_activities) × 100
```

Si `total_activities = 0`, alors `completion_percentage = 0`

### Détermination de la couleur
```php
if ($percentage == 0 && $total > 0) return '#ef4444'; // Red
if ($percentage < 50) return '#86efac'; // Light Green
if ($percentage < 80) return '#22c55e'; // Medium Green
return '#15803d'; // Dark Green
```

### Calcul du Consistency Score
```
score = moyenne(completion_percentage des 30 derniers jours)
```

### Calcul du Longest Streak
- Parcourt tous les logs avec `completion_percentage > 0`
- Compte les jours consécutifs
- Retourne la plus longue série

### Calcul du Most Productive Day
- Analyse les 90 derniers jours
- Calcule la moyenne par jour de la semaine
- Retourne le jour avec la moyenne la plus élevée

### Calcul du Trend
- Compare les moyennes des 2 dernières semaines
- Si différence > 10% : Improving
- Si différence < -10% : Decreasing
- Sinon : Stable

## Améliorations futures possibles

1. **Filtres avancés**
   - Par type d'activité
   - Par goal spécifique
   - Par routine

2. **Comparaison multi-années**
   - Comparer 2024 vs 2025
   - Voir l'évolution sur plusieurs années

3. **Objectifs de consistance**
   - Définir un objectif de streak
   - Notifications de rappel

4. **Export de données**
   - Export CSV des statistiques
   - Génération de rapports PDF

5. **Intégration sociale**
   - Partager ses statistiques
   - Comparer avec d'autres utilisateurs

6. **Gamification**
   - Badges pour les streaks
   - Récompenses pour la consistance
   - Défis mensuels

## Notes techniques

- La génération du heatmap utilise une grille de 52 semaines × 7 jours
- Le premier jour affiché est toujours un lundi
- Les données sont indexées par `user_id` et `log_date` pour des performances optimales
- Les calculs sont effectués côté serveur pour garantir la précision
- Le frontend utilise Bootstrap 5.3.2 et Bootstrap Icons 1.11.2

## Dépendances

- Symfony 6.x
- Doctrine ORM
- Bootstrap 5.3.2
- Bootstrap Icons 1.11.2
- PostgreSQL (ou autre base de données compatible)

## Commandes utiles

```bash
# Peupler des données de test
php bin/console app:populate-consistency-data

# Mettre à jour le schéma de base de données
php bin/console doctrine:schema:update --force

# Créer une migration
php bin/console make:migration

# Exécuter les migrations
php bin/console doctrine:migrations:migrate
```
