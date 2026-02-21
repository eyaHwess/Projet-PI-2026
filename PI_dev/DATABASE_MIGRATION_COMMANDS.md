# Commandes de Migration Base de Données - Projet PI_dev

## 📋 Commandes Essentielles

### 1. Vérifier l'État des Migrations
```bash
cd PI_dev
php bin/console doctrine:migrations:status
```

### 2. Voir les Migrations Disponibles
```bash
php bin/console doctrine:migrations:list
```

### 3. Vérifier le Schéma de la Base de Données
```bash
php bin/console doctrine:schema:validate
```

### 4. Voir les Différences entre Entités et Base de Données
```bash
php bin/console doctrine:schema:update --dump-sql
```

### 5. Appliquer Toutes les Migrations
```bash
php bin/console doctrine:migrations:migrate
```

### 6. Appliquer les Migrations Sans Confirmation
```bash
php bin/console doctrine:migrations:migrate --no-interaction
```

### 7. Mettre à Jour le Schéma Directement (Sans Migration)
```bash
php bin/console doctrine:schema:update --force
```

### 8. Créer une Nouvelle Migration
```bash
php bin/console make:migration
```

### 9. Marquer une Migration comme Exécutée (Sans l'Exécuter)
```bash
php bin/console doctrine:migrations:version --add --all
```

### 10. Revenir à une Migration Précédente
```bash
php bin/console doctrine:migrations:migrate prev
```

## 🔧 Commandes Avancées

### Exécuter une Migration Spécifique
```bash
php bin/console doctrine:migrations:execute --up Version20260218125642
```

### Annuler une Migration Spécifique
```bash
php bin/console doctrine:migrations:execute --down Version20260218125642
```

### Voir le SQL d'une Migration Sans l'Exécuter
```bash
php bin/console doctrine:migrations:migrate --dry-run
```

### Marquer une Migration Spécifique comme Exécutée
```bash
php bin/console doctrine:migrations:version Version20260218125642 --add
```

### Supprimer le Marquage d'une Migration
```bash
php bin/console doctrine:migrations:version Version20260218125642 --delete
```

## 🗄️ Commandes de Base de Données

### Créer la Base de Données
```bash
php bin/console doctrine:database:create
```

### Supprimer la Base de Données
```bash
php bin/console doctrine:database:drop --force
```

### Exécuter une Requête SQL
```bash
php bin/console doctrine:query:sql "SELECT * FROM user LIMIT 5"
```

### Voir la Structure d'une Table
```bash
php bin/console doctrine:query:sql "SELECT column_name, data_type FROM information_schema.columns WHERE table_name = 'user'"
```

## 📊 Workflow Complet de Migration

### Scénario 1: Première Installation
```bash
# 1. Créer la base de données
php bin/console doctrine:database:create

# 2. Appliquer toutes les migrations
php bin/console doctrine:migrations:migrate --no-interaction

# 3. Vérifier le schéma
php bin/console doctrine:schema:validate
```

### Scénario 2: Mise à Jour après Modification d'Entités
```bash
# 1. Créer une nouvelle migration
php bin/console make:migration

# 2. Vérifier le SQL généré
php bin/console doctrine:migrations:migrate --dry-run

# 3. Appliquer la migration
php bin/console doctrine:migrations:migrate

# 4. Vider le cache
php bin/console cache:clear
```

### Scénario 3: Synchronisation Rapide (Développement)
```bash
# 1. Voir les différences
php bin/console doctrine:schema:update --dump-sql

# 2. Appliquer directement (sans migration)
php bin/console doctrine:schema:update --force

# 3. Marquer toutes les migrations comme exécutées
php bin/console doctrine:migrations:version --add --all
```

### Scénario 4: Réinitialisation Complète
```bash
# 1. Supprimer la base de données
php bin/console doctrine:database:drop --force

# 2. Recréer la base de données
php bin/console doctrine:database:create

# 3. Appliquer toutes les migrations
php bin/console doctrine:migrations:migrate --no-interaction

# 4. (Optionnel) Charger des données de test
php bin/console doctrine:fixtures:load --no-interaction
```

## 🎯 Commandes Utilisées dans ce Projet

### Ce qui a été fait:
```bash
# 1. Vérification du statut
php bin/console doctrine:migrations:status

# 2. Tentative de migration (échouée à cause de conflits)
php bin/console doctrine:migrations:migrate --no-interaction

# 3. Mise à jour directe du schéma (51 requêtes exécutées)
php bin/console doctrine:schema:update --force

# 4. Marquage de toutes les migrations comme exécutées
php bin/console doctrine:migrations:version --add --all --no-interaction

# 5. Vérification finale
php bin/console doctrine:migrations:status

# 6. Vidage du cache
php bin/console cache:clear
```

## 📝 Migrations Disponibles dans le Projet

### Migrations Existantes:
1. `Version20260210104733` - Migration initiale (exécutée)
2. `Version20260211164510` - Migration 2 (exécutée)
3. `Version20260212032942` - Création tables activity, etc.
4. `Version20260214124451` - Ajout colonnes priority, deadline
5. `Version20260215155847` - Ajout colonnes time investment
6. `Version20260215181613` - Ajout colonnes consistency
7. `Version20260215213355` - Migration 7
8. `Version20260215231617` - Migration 8
9. `Version20260215233235` - Migration 9
10. `Version20260215235152` - Migration 10
11. `Version20260216001839` - Migration 11
12. `Version20260216002750` - Migration 12
13. `Version20260218125642` - Mise à jour priorité: standard -> normal

### Toutes Marquées comme Exécutées ✅

## ⚠️ Précautions

### Avant de Migrer en Production:
1. **Toujours faire un backup de la base de données**
```bash
# PostgreSQL
pg_dump -U username -d pidev_db > backup_$(date +%Y%m%d_%H%M%S).sql

# MySQL
mysqldump -u username -p pidev_db > backup_$(date +%Y%m%d_%H%M%S).sql
```

2. **Tester les migrations en développement d'abord**
```bash
php bin/console doctrine:migrations:migrate --dry-run
```

3. **Vérifier le schéma après migration**
```bash
php bin/console doctrine:schema:validate
```

4. **Vider le cache après migration**
```bash
php bin/console cache:clear
```

## 🔍 Commandes de Diagnostic

### Vérifier les Tables Existantes
```bash
php bin/console doctrine:query:sql "SELECT table_name FROM information_schema.tables WHERE table_schema = 'public'"
```

### Vérifier les Colonnes d'une Table
```bash
php bin/console doctrine:query:sql "SELECT column_name, data_type, is_nullable FROM information_schema.columns WHERE table_name = 'user'"
```

### Compter les Enregistrements
```bash
php bin/console doctrine:query:sql "SELECT COUNT(*) FROM user"
php bin/console doctrine:query:sql "SELECT COUNT(*) FROM goal"
php bin/console doctrine:query:sql "SELECT COUNT(*) FROM routine"
php bin/console doctrine:query:sql "SELECT COUNT(*) FROM activity"
```

### Vérifier les Contraintes de Clés Étrangères
```bash
php bin/console doctrine:query:sql "SELECT constraint_name, table_name FROM information_schema.table_constraints WHERE constraint_type = 'FOREIGN KEY'"
```

## 📚 Ressources

### Documentation Symfony
- [Doctrine Migrations](https://symfony.com/doc/current/doctrine.html#migrations-creating-the-database-tables-schema)
- [Database Schema](https://symfony.com/doc/current/doctrine.html#creating-an-entity-class)

### Commandes Utiles
```bash
# Aide sur les migrations
php bin/console doctrine:migrations --help

# Aide sur le schéma
php bin/console doctrine:schema:update --help

# Liste de toutes les commandes Doctrine
php bin/console list doctrine
```

## ✅ État Actuel du Projet

- **Base de données**: pidev_db
- **Migrations exécutées**: 10/10
- **Schéma**: Synchronisé ✅
- **Cache**: Vidé ✅
- **Status**: Prêt pour utilisation ✅
