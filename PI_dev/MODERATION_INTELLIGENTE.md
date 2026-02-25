# Modération Intelligente - Documentation

## Vue d'ensemble

Le système de modération intelligente analyse automatiquement le contenu des messages avant leur publication pour assurer un environnement sain, sécurisé et professionnel dans les discussions.

## Fonctionnalités

### 1. Détection de Messages Toxiques

L'IA analyse et détecte:
- ✅ Insultes
- ✅ Harcèlement
- ✅ Discours haineux
- ✅ Menaces
- ✅ Vulgarité excessive
- ✅ Majuscules excessives (CRIER)
- ✅ Points d'exclamation excessifs

**Comportement:**
- Message bloqué automatiquement
- Affichage: "⚠️ Ce message viole les règles de la communauté"
- Badge rouge visible sur le message
- Contenu masqué pour tous sauf l'auteur et les modérateurs

### 2. Détection de Spam

L'IA détecte:
- ✅ Messages répétitifs
- ✅ Liens suspects
- ✅ Publicité
- ✅ Copié-collé massif
- ✅ Messages trop courts envoyés en boucle
- ✅ Trop de liens dans un message
- ✅ Messages entièrement en majuscules

**Comportement:**
- Message marqué comme spam
- Affichage: "🚫 Ce message est considéré comme spam"
- Badge orange visible
- Message masqué pour les autres utilisateurs
- Visible uniquement pour l'auteur et les modérateurs

## Architecture Technique

### Entité Message - Nouveaux Champs

```php
// Champs de modération
private bool $isToxic = false;
private bool $isSpam = false;
private string $moderationStatus = 'approved'; // approved, blocked, hidden, pending
private ?float $toxicityScore = null;
private ?float $spamScore = null;
private ?string $moderationReason = null;
```

### Statuts de Modération

1. **approved** (par défaut) - Message approuvé, visible par tous
2. **blocked** - Message bloqué (toxique), non enregistré
3. **hidden** - Message masqué (spam), visible uniquement par l'auteur et modérateurs
4. **pending** - En attente de modération manuelle (futur)

### Service de Modération

**Fichier:** `src/Service/ModerationService.php`

**Méthodes principales:**

```php
// Analyse complète d'un message
public function analyzeMessage(string $content): array

// Détecte la toxicité
private function detectToxicity(string $content): array

// Détecte le spam
private function detectSpam(string $content): array

// Vérifie le spam utilisateur (messages répétitifs)
public function checkUserSpamming(array $recentMessages, string $newMessage): bool
```

### Seuils de Détection

```php
private const TOXICITY_THRESHOLD = 0.7;  // 70% de confiance
private const SPAM_THRESHOLD = 0.6;      // 60% de confiance
```

## Flux de Fonctionnement

```
1. Utilisateur envoie un message
   ↓
2. ChatroomController reçoit le message
   ↓
3. ModerationService analyse le contenu
   ↓
4. Calcul des scores (toxicité, spam)
   ↓
5. Décision automatique:
   - Score toxicité ≥ 0.7 → BLOCKED
   - Score spam ≥ 0.6 → HIDDEN
   - Sinon → APPROVED
   ↓
6. Application du statut au message
   ↓
7. Enregistrement en base de données
   ↓
8. Affichage avec badge approprié
```

## Interface Utilisateur

### Badges de Modération

**Message Toxique (Bloqué):**
```html
<div class="moderation-badge toxic">
    <i class="fas fa-exclamation-triangle"></i> 
    Ce message viole les règles de la communauté
</div>
```
- Fond: Dégradé rouge (#ff4444 → #cc0000)
- Bordure: Rouge #ff0000
- Ombre: Rouge avec opacité

**Message Spam (Masqué):**
```html
<div class="moderation-badge spam">
    <i class="fas fa-ban"></i> 
    Ce message est considéré comme spam
</div>
```
- Fond: Dégradé orange (#ff9800 → #f57c00)
- Bordure: Orange #ff6f00
- Ombre: Orange avec opacité

### Visibilité des Messages Modérés

- **Auteur:** Voit son message avec le badge et un avertissement
- **Modérateurs:** Voient tous les messages avec les badges
- **Autres utilisateurs:** Ne voient pas les messages bloqués/masqués

## Mots Toxiques Détectés

Le système détecte une liste de mots toxiques en plusieurs langues:
- Français: insulte, idiot, con, connard, salaud, merde, etc.
- Anglais: fuck, shit, bitch, asshole, damn, bastard, etc.
- Arabe: كلب, حمار, غبي, أحمق

**Note:** Cette liste peut être enrichie dans `ModerationService.php`

## Patterns de Spam Détectés

```php
- URLs: /https?:\/\/[^\s]+/i
- WWW: /www\.[^\s]+/i
- Mots-clés: /\b(viagra|casino|lottery|winner|prize|click here|buy now)\b/i
- Caractères répétés: /(.)\1{4,}/
- Mots répétés: /\b(\w+)\s+\1\b/i
```

## Configuration

### Ajuster les Seuils

Dans `src/Service/ModerationService.php`:

```php
// Augmenter pour être plus strict
private const TOXICITY_THRESHOLD = 0.8;  // 80%
private const SPAM_THRESHOLD = 0.7;      // 70%

// Diminuer pour être plus permissif
private const TOXICITY_THRESHOLD = 0.5;  // 50%
private const SPAM_THRESHOLD = 0.4;      // 40%
```

### Ajouter des Mots Toxiques

```php
private const TOXIC_WORDS = [
    // Ajouter vos mots ici
    'nouveau_mot_toxique',
    'autre_insulte',
];
```

### Ajouter des Patterns de Spam

```php
private const SPAM_PATTERNS = [
    // Ajouter vos patterns regex ici
    '/nouveau_pattern/i',
];
```

## Améliorations Futures

### 1. Intégration API IA Externe

Remplacer l'analyse locale par une API comme:
- **Perspective API** (Google) - Détection de toxicité avancée
- **Azure Content Moderator** (Microsoft)
- **AWS Comprehend** (Amazon)

```php
// Exemple d'intégration future
public function analyzeWithPerspectiveAPI(string $content): array
{
    $response = $this->httpClient->request('POST', 'https://commentanalyzer.googleapis.com/v1alpha1/comments:analyze', [
        'json' => [
            'comment' => ['text' => $content],
            'languages' => ['fr', 'en', 'ar'],
            'requestedAttributes' => [
                'TOXICITY' => [],
                'SEVERE_TOXICITY' => [],
                'INSULT' => [],
                'PROFANITY' => [],
                'THREAT' => []
            ]
        ],
        'headers' => [
            'Authorization' => 'Bearer ' . $this->apiKey
        ]
    ]);
    
    return $response->toArray();
}
```

### 2. Modération Manuelle

- Interface d'administration pour réviser les messages modérés
- Statut "pending" pour révision manuelle
- Historique des décisions de modération

### 3. Apprentissage Automatique

- Enregistrer les faux positifs/négatifs
- Améliorer les seuils automatiquement
- Adapter le modèle au contexte de l'application

### 4. Notifications

- Notifier les modérateurs des messages suspects
- Alerter l'utilisateur en cas de comportement répété
- Système de points/avertissements

### 5. Analyse Contextuelle

- Détecter le sarcasme
- Comprendre le contexte de la conversation
- Analyser les images et fichiers joints

## Tests

### Tester la Détection de Toxicité

Essayez d'envoyer ces messages:
```
"Tu es un idiot"
"ARRÊTE DE CRIER!!!!"
"Connard de merde"
```

### Tester la Détection de Spam

Essayez d'envoyer ces messages:
```
"Visitez www.spam.com pour gagner!!!"
"aaaaaaaaaa"
"ACHETEZ MAINTENANT!!!"
"Cliquez ici: https://suspicious-link.com"
```

## Logs

Les décisions de modération sont enregistrées dans les logs Symfony:

```bash
# Voir les logs de modération
tail -f var/log/dev.log | grep moderation
```

## Base de Données

### Migration

La migration `Version20260224203946` ajoute les colonnes:
- `is_toxic` (TINYINT)
- `is_spam` (TINYINT)
- `moderation_status` (VARCHAR 20)
- `toxicity_score` (DOUBLE)
- `spam_score` (DOUBLE)
- `moderation_reason` (TEXT)

### Requêtes Utiles

```sql
-- Messages toxiques
SELECT * FROM message WHERE is_toxic = 1;

-- Messages spam
SELECT * FROM message WHERE is_spam = 1;

-- Messages modérés
SELECT * FROM message WHERE moderation_status != 'approved';

-- Statistiques de modération
SELECT 
    moderation_status, 
    COUNT(*) as count,
    AVG(toxicity_score) as avg_toxicity,
    AVG(spam_score) as avg_spam
FROM message 
GROUP BY moderation_status;
```

## Support

Pour toute question ou amélioration, consultez:
- Documentation Symfony: https://symfony.com/doc
- Perspective API: https://perspectiveapi.com
- Azure Content Moderator: https://azure.microsoft.com/services/cognitive-services/content-moderator/

---

**Version:** 1.0  
**Date:** 24 février 2026  
**Auteur:** Système de Modération Intelligente
