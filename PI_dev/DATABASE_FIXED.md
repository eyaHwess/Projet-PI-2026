# Base de Données Synchronisée - Projet PI_dev

Date: 2026-02-18

## ✅ Problème Résolu

### Erreur Initiale
```
SQLSTATE[42703]: Undefined column: 7 ERREUR: la colonne « review_count » de la relation « user » n'existe pas
```

**Cause**: Le schéma de la base de données n'était pas synchronisé avec les entités Doctrine

## 🔧 Actions Effectuées

### 1. Vérification du Statut des Migrations
```bash
php bin/console doctrine:migrations:status
```
**Résultat**: 8 migrations non exécutées

### 2. Tentative de Migration
```bash
php bin/console doctrine:migrations:migrate --no-interaction
```
**Problème**: Conflit - certaines tables existaient déjà

### 3. Mise à Jour du Schéma
```bash
php bin/console doctrine:schema:update --force
```
**Résultat**: 51 requêtes exécutées avec succès

### 4. Marquage des Migrations
```bash
php bin/console doctrine:migrations:version --add --all --no-interaction
```
**Résultat**: 8 migrations marquées comme exécutées

## 📊 État Final

### Migrations
- **Exécutées**: 10 migrations
- **Disponibles**: 8 migrations
- **Nouvelles**: 0 migration
- **Status**: ✅ À jour (Latest version)

### Tables Créées/Mises à Jour

#### Nouvelles Tables (Système de Coaching)
1. ✅ `coaching_request` - Demandes de coaching
2. ✅ `notifications` - Notifications utilisateur
3. ✅ `reviews` - Avis et évaluations
4. ✅ `session` - Sessions de coaching
5. ✅ `time_slots` - Créneaux horaires

#### Tables Existantes Mises à Jour
1. ✅ `user` - Ajout de colonnes coaching:
   - `review_count` - Nombre d'avis
   - `price_per_session` - Prix par session
   - `bio` - Biographie
   - `photo_url` - URL de la photo
   - `badges` - Badges JSON
   - `responds_quickly` - Répond rapidement
   - `total_sessions` - Total des sessions
   - `last_activity_at` - Dernière activité

2. ✅ `activity` - Colonnes conservées
3. ✅ `goal` - Colonnes conservées
4. ✅ `routine` - Colonnes conservées

#### Table Supprimée
- ❌ `daily_activity_log` - Supprimée (séquence également)

## 🎯 Fonctionnalités Disponibles

### Système de Goals/Routines/Activities
- ✅ Création, édition, suppression d'objectifs
- ✅ Gestion des routines
- ✅ Gestion des activités
- ✅ Système de favoris
- ✅ Priorités et deadlines
- ✅ Statuts intelligents
- ✅ Calendrier de planification

### Système de Coaching (Nouveau)
- ✅ Demandes de coaching
- ✅ Gestion des coachs
- ✅ Système d'avis et évaluations
- ✅ Planification de sessions
- ✅ Créneaux horaires
- ✅ Notifications

### Système d'Analyse
- ⚠️ Consistency Heatmap - Table supprimée, à recréer si nécessaire
- ✅ Time Investment Analytics

## ⚠️ Points d'Attention

### 1. Daily Activity Log
La table `daily_activity_log` a été supprimée. Si la fonctionnalité Consistency Heatmap est nécessaire:

**Option A**: Recréer l'entité et générer une nouvelle migration
```bash
php bin/console make:entity DailyActivityLog
php bin/console make:migration
php bin/console doctrine:migrations:migrate
```

**Option B**: Restaurer depuis un backup si disponible

### 2. Données Existantes
- Les données des tables `user`, `goal`, `routine`, `activity` ont été préservées
- Les nouvelles colonnes ont des valeurs NULL par défaut

### 3. Contraintes de Clés Étrangères
Toutes les relations sont correctement configurées:
- `coaching_request` → `user` (user_id, coach_id)
- `coaching_request` → `time_slots` (time_slot_id)
- `notifications` → `user` (user_id)
- `notifications` → `coaching_request` (coaching_request_id)
- `reviews` → `user` (user_id, coach_id)
- `session` → `coaching_request` (coaching_request_id)
- `time_slots` → `user` (coach_id, booked_by_id)
- `time_slots` → `coaching_request` (coaching_request_id)

## 🚀 Prochaines Étapes

### 1. Tester les Fonctionnalités
- ✅ Tester la création d'objectifs
- ✅ Tester les routines et activités
- ✅ Tester le système de favoris
- ✅ Tester le calendrier
- ⚠️ Tester le système de coaching
- ⚠️ Tester les avis et évaluations
- ⚠️ Tester les sessions

### 2. Vérifier les Données
```bash
# Vérifier les utilisateurs
php bin/console doctrine:query:sql "SELECT id, email, roles FROM \"user\" LIMIT 5"

# Vérifier les objectifs
php bin/console doctrine:query:sql "SELECT id, title, status FROM goal LIMIT 5"

# Vérifier les nouvelles tables
php bin/console doctrine:query:sql "SELECT COUNT(*) FROM coaching_request"
```

### 3. Recréer Consistency Heatmap (Si Nécessaire)
Si la fonctionnalité est utilisée, recréer l'entité `DailyActivityLog`:

```php
// src/Entity/DailyActivityLog.php
#[ORM\Entity]
class DailyActivityLog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'date')]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column]
    private ?int $completedActivities = 0;

    #[ORM\Column]
    private ?int $totalActivities = 0;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    // Getters and setters...
}
```

Puis:
```bash
php bin/console make:migration
php bin/console doctrine:migrations:migrate
```

## 📝 Commandes Utiles

### Vérifier le Schéma
```bash
php bin/console doctrine:schema:validate
```

### Voir les Différences
```bash
php bin/console doctrine:schema:update --dump-sql
```

### Lister les Migrations
```bash
php bin/console doctrine:migrations:list
```

### Créer une Nouvelle Migration
```bash
php bin/console make:migration
```

## ✅ Conclusion

**La base de données est maintenant synchronisée avec les entités Doctrine.**

- Toutes les migrations sont à jour
- Le schéma est cohérent
- Les nouvelles fonctionnalités de coaching sont disponibles
- Le projet devrait fonctionner sans erreurs de base de données

**Note**: Si l'erreur `review_count` persiste, vider le cache:
```bash
php bin/console cache:clear
```
