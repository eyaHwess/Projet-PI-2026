# 💾 Système de Cache de Traduction

## 🎯 Objectif

Le système enregistre maintenant toutes les traductions dans la base de données pour :
- ✅ Réduire les appels API (économiser le quota)
- ✅ Améliorer les performances (réponse instantanée)
- ✅ Réduire les coûts (moins d'appels API)
- ✅ Suivre l'utilisation (statistiques)

## 📊 Architecture

### Table `message_translation`

| Colonne | Type | Description |
|---------|------|-------------|
| `id` | INT | Identifiant unique |
| `message_id` | INT | Message original (FK) |
| `source_language` | VARCHAR(10) | Langue source (auto-détectée) |
| `target_language` | VARCHAR(10) | Langue cible (fr, en, ar, etc.) |
| `translated_text` | TEXT | Texte traduit |
| `provider` | VARCHAR(50) | Provider utilisé (deepl, mymemory, etc.) |
| `created_at` | TIMESTAMP | Date de création |
| `last_used_at` | TIMESTAMP | Dernière utilisation |
| `usage_count` | INT | Nombre d'utilisations |

### Index

- `idx_message_lang` : Index composite sur (message_id, target_language) pour recherche rapide
- `idx_created_at` : Index sur created_at pour nettoyage
- FK avec `ON DELETE CASCADE` : Suppression automatique si le message est supprimé

## 🔄 Workflow

```
Utilisateur clique sur 🌐
         ↓
1. Vérifier si traduction existe en BDD
         ↓
    ┌────┴────┐
    │         │
✅ Existe  ❌ N'existe pas
    │         │
    │         ↓
    │    2. Appeler API (DeepL/MyMemory)
    │         ↓
    │    3. Enregistrer en BDD
    │         │
    └─────────┘
         ↓
4. Incrémenter usage_count
         ↓
5. Retourner traduction
```

## 📈 Avantages

### 1. Économie d'API

**Exemple** : Message "hello" traduit 10 fois
- **Sans cache** : 10 appels API
- **Avec cache** : 1 appel API + 9 lectures BDD

**Économie** : 90% d'appels API

### 2. Performance

| Type | Temps de Réponse |
|------|------------------|
| Appel API DeepL | ~800ms |
| Appel API MyMemory | ~2-3s |
| **Lecture BDD** | **~10ms** |

**Gain** : 80-300x plus rapide

### 3. Quota

**DeepL Free** : 500,000 caractères/mois

**Sans cache** :
- 100 messages de 50 caractères = 5,000 chars
- Traduits 10 fois chacun = 50,000 chars
- Limite : 10 cycles/mois

**Avec cache** :
- 100 messages × 50 chars = 5,000 chars (1ère fois)
- 9 fois suivantes = 0 chars (cache)
- Limite : 100 cycles/mois

**Gain** : 10x plus de traductions possibles

## 🛠️ Commandes

### Voir les Statistiques

```bash
php bin/console app:translation:stats
```

**Affiche** :
- Nombre de traductions par provider et langue
- Total d'utilisations
- Taux de réutilisation
- Top 5 des traductions les plus utilisées

**Exemple de sortie** :
```
📊 Statistiques des Traductions
================================

📈 Utilisation par Provider et Langue
--------------------------------------
Provider    Langue    Traductions    Utilisations
deepl       FR        45             120
deepl       EN        38             95
mymemory    FR        12             15
mymemory    EN        8              10

📊 Résumé
---------
• Total de traductions uniques : 103
• Total d'utilisations : 240
• Taux de réutilisation : 2.33x

🔥 Top 5 des Traductions les Plus Utilisées
--------------------------------------------
Texte Original    Traduction         Langue    Utilisations
hello             bonjour            FR        25
good morning      bonjour            FR        18
thank you         merci              FR        15
how are you?      comment vas-tu?    FR        12
bye               au revoir          FR        10
```

### Nettoyer les Anciennes Traductions

```bash
# Supprimer traductions non utilisées depuis 30 jours (défaut)
php bin/console app:translation:cleanup

# Supprimer traductions non utilisées depuis 60 jours
php bin/console app:translation:cleanup 60

# Supprimer traductions non utilisées depuis 7 jours
php bin/console app:translation:cleanup 7
```

**Recommandation** : Exécuter mensuellement via cron

```bash
# Cron : Tous les 1er du mois à 3h du matin
0 3 1 * * cd /path/to/project && php bin/console app:translation:cleanup 30
```

## 📊 Réponse API

### Avec Cache (Traduction Existante)

```json
{
  "translation": "bonjour",
  "targetLanguage": "Français",
  "originalText": "hello",
  "cached": true,
  "provider": "deepl"
}
```

### Sans Cache (Nouvelle Traduction)

```json
{
  "translation": "bonjour",
  "targetLanguage": "Français",
  "originalText": "hello",
  "cached": false,
  "provider": "deepl"
}
```

**Note** : Le champ `cached` indique si la traduction vient du cache ou de l'API.

## 🔍 Requêtes Utiles

### Voir Toutes les Traductions d'un Message

```php
$translations = $translationRepo->findByMessage($message);
```

### Compter les Traductions d'un Message

```php
$count = $translationRepo->countByMessage($message);
```

### Trouver une Traduction Spécifique

```php
$translation = $translationRepo->findExistingTranslation($message, 'fr');
```

## 📈 Monitoring

### Taux de Cache Hit

```sql
SELECT 
    COUNT(*) as total_translations,
    SUM(usage_count) as total_usage,
    ROUND(SUM(usage_count)::numeric / COUNT(*)::numeric, 2) as cache_hit_ratio
FROM message_translation;
```

### Traductions par Provider

```sql
SELECT 
    provider,
    COUNT(*) as count,
    SUM(usage_count) as total_usage
FROM message_translation
GROUP BY provider
ORDER BY total_usage DESC;
```

### Traductions par Langue

```sql
SELECT 
    target_language,
    COUNT(*) as count,
    SUM(usage_count) as total_usage
FROM message_translation
GROUP BY target_language
ORDER BY total_usage DESC;
```

## 🎯 Optimisations Futures

### 1. Cache Redis (Optionnel)

Pour des performances encore meilleures :
- BDD : ~10ms
- Redis : ~1ms

### 2. Pré-traduction

Traduire automatiquement les messages populaires dans toutes les langues.

### 3. Traduction Collaborative

Permettre aux utilisateurs de suggérer des améliorations de traduction.

### 4. Détection de Langue Serveur

Détecter la langue source côté serveur pour de meilleures statistiques.

## 🔒 Sécurité

### Suppression en Cascade

Si un message est supprimé, toutes ses traductions sont automatiquement supprimées (`ON DELETE CASCADE`).

### Nettoyage Automatique

Les traductions anciennes non utilisées sont supprimées pour économiser l'espace disque.

## 📊 Exemple Réel

### Chatroom avec 100 Utilisateurs

**Scénario** : 10 messages/jour, 50% traduits

**Sans cache** :
- 10 messages × 50% × 100 utilisateurs = 500 traductions/jour
- 500 × 50 caractères = 25,000 chars/jour
- 25,000 × 30 jours = 750,000 chars/mois
- **Quota dépassé** ❌

**Avec cache** :
- 10 messages × 50 chars = 500 chars/jour (1ère traduction)
- 500 × 30 jours = 15,000 chars/mois
- **Quota OK** ✅ (97% d'économie)

## ✅ Résumé

Le système de cache de traduction :
- ✅ Réduit les appels API de 90%+
- ✅ Améliore les performances de 80-300x
- ✅ Économise le quota DeepL
- ✅ Fournit des statistiques d'utilisation
- ✅ Nettoie automatiquement les anciennes traductions
- ✅ Supprime en cascade avec les messages

**Résultat** : Système de traduction professionnel, performant et économique.
