# Guide d'Utilisation - Système de Modération Intelligente

## 🎯 Vue d'Ensemble

Le système de modération intelligente est maintenant **opérationnel** dans votre application. Il analyse automatiquement tous les messages envoyés dans les chatrooms et applique des actions selon le contenu détecté.

## ✅ Ce Qui Fonctionne Actuellement

### Détection de Toxicité (Taux: ~60%)
- ✅ Insultes en anglais (fuck, asshole, bitch, etc.)
- ✅ Insultes multiples dans un message
- ✅ Points d'exclamation excessifs
- ⚠️ Insultes courtes en français (idiot, con) - nécessite amélioration
- ⚠️ Majuscules excessives - nécessite amélioration

### Détection de Spam (Taux: ~50%)
- ✅ URLs complètes (https://...)
- ✅ Trop de liens (3+)
- ✅ Messages répétitifs (spam utilisateur)
- ⚠️ WWW sans https - nécessite amélioration
- ⚠️ Caractères répétés - nécessite amélioration
- ⚠️ Messages trop courts - nécessite amélioration

## 📊 Résultats des Tests

**Tests Unitaires:** 20/25 réussis (80%)  
**Tests Démonstration:** 5/11 réussis (45%)  
**Statut Global:** ⚠️ Fonctionnel avec améliorations nécessaires

## 🚀 Comment Utiliser

### 1. Envoyer un Message Normal
```
Utilisateur: "Bonjour, comment allez-vous?"
Système: ✅ Message publié normalement
```

### 2. Message Toxique Détecté
```
Utilisateur: "You are a fucking asshole"
Système: ❌ Message bloqué
Flash: "⚠️ Ce message viole les règles de la communauté"
```

### 3. Message Spam Détecté
```
Utilisateur: "Visitez https://spam.com pour gagner!"
Système: ⚠️ Message masqué
Flash: "🚫 Votre message a été marqué comme spam..."
Badge: Orange "Ce message est considéré comme spam"
```

## 🎨 Interface Utilisateur

### Badges de Modération

**Message Toxique (Bloqué):**
- Fond: Dégradé rouge
- Icône: ⚠️ Triangle d'avertissement
- Texte: "Ce message viole les règles de la communauté"
- Visible: Uniquement pour l'auteur et modérateurs

**Message Spam (Masqué):**
- Fond: Dégradé orange
- Icône: 🚫 Interdit
- Texte: "Ce message est considéré comme spam"
- Visible: Uniquement pour l'auteur et modérateurs

## 🔧 Configuration

### Ajuster les Seuils

Fichier: `src/Service/ModerationService.php`

```php
// Plus strict (bloque plus de messages)
private const TOXICITY_THRESHOLD = 0.5;  // 50%
private const SPAM_THRESHOLD = 0.4;      // 40%

// Plus permissif (bloque moins de messages)
private const TOXICITY_THRESHOLD = 0.8;  // 80%
private const SPAM_THRESHOLD = 0.7;      // 70%

// Actuel (équilibré)
private const TOXICITY_THRESHOLD = 0.7;  // 70%
private const SPAM_THRESHOLD = 0.6;      // 60%
```

### Ajouter des Mots Toxiques

```php
private const TOXIC_WORDS = [
    // Français
    'insulte', 'idiot', 'con', 'connard', 'salaud',
    
    // Ajoutez vos mots ici
    'nouveau_mot',
    'autre_insulte',
];
```

### Ajouter des Patterns de Spam

```php
private const SPAM_PATTERNS = [
    '/https?:\/\/[^\s]+/i',
    
    // Ajoutez vos patterns ici
    '/\b(nouveau|pattern)\b/i',
];
```

## 📝 Exemples de Messages

### Messages qui PASSENT ✅
```
"Bonjour tout le monde!"
"Comment allez-vous aujourd'hui?"
"Merci pour votre aide 😊"
"C'est vraiment nul ce que tu fais" (score < 0.7)
"Super travail, bravo!"
```

### Messages BLOQUÉS ❌ (Toxiques)
```
"You are a fucking asshole" ✅ Fonctionne
"Espèce de connard et d'imbécile" ✅ Fonctionne
"Tu es un idiot" ⚠️ Ne fonctionne pas encore
"ARRÊTE DE CRIER!!!" ⚠️ Ne fonctionne pas encore
```

### Messages MASQUÉS ⚠️ (Spam)
```
"Visitez https://spam.com" ✅ Fonctionne
"https://site1.com https://site2.com https://site3.com" ✅ Fonctionne
"aaaaaaaaaa" ⚠️ Ne fonctionne pas encore
"ok" ⚠️ Ne fonctionne pas encore
"Click here to win!" ⚠️ Ne fonctionne pas encore
```

## 🗄️ Base de Données

### Vérifier les Messages Modérés

```sql
-- Messages toxiques
SELECT id, content, toxicity_score, moderation_reason
FROM message
WHERE is_toxic = 1
ORDER BY created_at DESC;

-- Messages spam
SELECT id, content, spam_score, moderation_reason
FROM message
WHERE is_spam = 1
ORDER BY created_at DESC;

-- Statistiques
SELECT 
    moderation_status,
    COUNT(*) as total,
    AVG(toxicity_score) as avg_toxicity,
    AVG(spam_score) as avg_spam
FROM message
GROUP BY moderation_status;
```

## 🧪 Tester le Système

### 1. Tests Unitaires
```bash
php bin/phpunit tests/Service/ModerationServiceTest.php
```

### 2. Script de Démonstration
```bash
php demo_moderation.php
```

### 3. Tests Manuels
Consultez `TEST_MODERATION_MANUEL.md` pour une liste complète de tests à effectuer dans le navigateur.

## 📈 Améliorations Nécessaires

### Priorité HAUTE 🔴
1. **Améliorer détection mots courts** (idiot, con, nul)
   - Solution: Utiliser des limites de mots `\b`
   
2. **Fixer détection majuscules avec accents**
   - Solution: Utiliser `mb_string` pour Unicode

3. **Augmenter scores pour patterns critiques**
   - Mots-clés spam: 0.4 → 0.6
   - Caractères répétés: 0.4 → 0.6

### Priorité MOYENNE 🟡
4. **Améliorer pattern WWW**
5. **Détecter messages trop courts répétés**
6. **Ajouter plus de mots toxiques**

### Priorité BASSE 🟢
7. **Intégration API IA externe**
8. **Interface d'administration**
9. **Statistiques avancées**

## 🔍 Debugging

### Voir les Logs
```bash
# Windows PowerShell
Get-Content var/log/dev.log -Tail 50 | Select-String "moderation"

# Ou ouvrir le fichier
notepad var/log/dev.log
```

### Vider le Cache
```bash
php bin/console cache:clear
```

### Réinitialiser la Base
```bash
php bin/console doctrine:schema:drop --force
php bin/console doctrine:schema:create
php bin/console doctrine:migrations:migrate
```

## 💡 Conseils d'Utilisation

1. **Commencez avec des seuils élevés** (0.7-0.8) puis ajustez selon les retours
2. **Surveillez les faux positifs** dans les logs
3. **Enrichissez la liste de mots** selon votre communauté
4. **Testez régulièrement** avec de vrais messages
5. **Collectez des métriques** pour améliorer le système

## 📞 Support

### Fichiers Importants
- `src/Service/ModerationService.php` - Service principal
- `src/Entity/Message.php` - Entité avec champs de modération
- `src/Controller/ChatroomController.php` - Intégration
- `templates/chatroom/chatroom_modern.html.twig` - Interface

### Documentation
- `MODERATION_INTELLIGENTE.md` - Documentation complète
- `TEST_MODERATION_MANUEL.md` - Guide de tests manuels
- `RESULTATS_TESTS_MODERATION.md` - Résultats des tests

### Commandes Utiles
```bash
# Tests
php bin/phpunit tests/Service/ModerationServiceTest.php

# Démonstration
php demo_moderation.php

# Cache
php bin/console cache:clear

# Migrations
php bin/console make:migration
php bin/console doctrine:migrations:migrate
```

## ✨ Conclusion

Le système de modération intelligente est **opérationnel** et fonctionne pour les cas les plus critiques (insultes graves, URLs spam). Des améliorations sont nécessaires pour atteindre une précision optimale, mais le système peut déjà être utilisé en production avec surveillance.

**Recommandation:** Déployer en environnement de test, collecter des données réelles, puis ajuster les seuils et patterns avant le déploiement en production.

---

**Version:** 1.0  
**Date:** 24 février 2026  
**Statut:** ⚠️ Fonctionnel - Améliorations recommandées
