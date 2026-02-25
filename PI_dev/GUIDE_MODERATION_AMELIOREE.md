# 📖 Guide Rapide - Modération Améliorée

## 🎯 Qu'est-ce qui a changé?

Le système de modération détecte maintenant **beaucoup plus de messages toxiques**, y compris des expressions comme:
- "C'est vraiment stupide"
- "Tu es tellement bête"
- "Espèce d'idiot"
- "Ferme ta gueule"

---

## 🚀 Nouveautés

### 1. Détection Contextuelle
Le système comprend maintenant le **contexte** des mots, pas seulement les mots isolés.

**Exemple**:
- ❌ Avant: "C'est vraiment stupide" → PASSAIT (score trop bas)
- ✅ Après: "C'est vraiment stupide" → BLOQUÉ (pattern détecté)

### 2. Liste Enrichie
**80+ mots toxiques** au lieu de 13, en 3 langues (FR/EN/AR)

### 3. Patterns Intelligents
Détection de:
- Intensificateurs: "vraiment", "tellement", "très", "super", "hyper"
- Expressions dégradantes: "espèce de", "sale", "putain de"
- Menaces: "je vais te tuer", "ferme ta gueule"
- Harcèlement: "personne ne t'aime", "tu devrais mourir"

---

## 🧪 Test Rapide

### Dans le Terminal
```bash
php test_moderation_amelioree.php
```

**Résultat attendu**: 15/15 tests réussis (100%)

### Dans le Navigateur

1. Ouvrir le chatroom: `/message/chatroom/{goalId}`

2. Tester ces messages:

| Message | Résultat Attendu |
|---------|------------------|
| "C'est vraiment stupide" | 🔴 BLOQUÉ |
| "Tu es pathétique" | 🔴 BLOQUÉ |
| "Espèce d'idiot" | 🔴 BLOQUÉ |
| "Ferme ta gueule" | 🔴 BLOQUÉ |
| "Hello everyone!" | 🟢 APPROUVÉ |
| "Merci pour votre aide" | 🟢 APPROUVÉ |

3. Vérifier l'affichage du message flash rouge:
```
┌────────────────────────────────────────────────────┐
│ 🔴 ⚠️ Ce message viole les règles de la communauté │ ×
└────────────────────────────────────────────────────┘
```

---

## 📊 Exemples de Détection

### ✅ Messages Bloqués

#### Avec Intensificateurs
```
"C'est vraiment stupide"        → Score: 1.0 → BLOQUÉ
"Tu es tellement bête"          → Score: 1.0 → BLOQUÉ
"C'est très ridicule"           → Score: 1.0 → BLOQUÉ
"C'est super nul"               → Score: 1.0 → BLOQUÉ
```

#### Expressions Dégradantes
```
"Espèce d'idiot"                → Score: 0.8 → BLOQUÉ
"Sale con"                      → Score: 0.8 → BLOQUÉ
"Putain de merde"               → Score: 1.0 → BLOQUÉ
```

#### Expressions Agressives
```
"Ferme ta gueule"               → Score: 1.0 → BLOQUÉ
"Va te faire foutre"            → Score: 1.0 → BLOQUÉ
"Tu me fais chier"              → Score: 1.0 → BLOQUÉ
```

#### Insultes Graves
```
"you are a fucking asshole"     → Score: 1.0 → BLOQUÉ
"Tu es un connard"              → Score: 1.0 → BLOQUÉ
"Quel loser"                    → Score: 0.8 → BLOQUÉ
```

### ✅ Messages Approuvés

```
"C'est une bonne idée"          → Score: 0.0 → APPROUVÉ
"Je ne suis pas d'accord"       → Score: 0.0 → APPROUVÉ
"Pouvez-vous m'expliquer?"      → Score: 0.0 → APPROUVÉ
"C'est intéressant"             → Score: 0.0 → APPROUVÉ
"Merci pour votre aide"         → Score: 0.0 → APPROUVÉ
```

---

## 🔧 Configuration

### Seuils Actuels
```php
TOXICITY_THRESHOLD = 0.6  // 60% - Messages bloqués
SPAM_THRESHOLD = 0.5      // 50% - Messages masqués
```

### Scores par Type
```
Pattern toxique:           +0.5
Insulte grave:            +0.5
Insulte modérée:          +0.4
Mot toxique:              +0.3
Majuscules excessives:    +0.3
Exclamations excessives:  +0.2
Ponctuation aggressive:   +0.2
```

---

## 🌍 Support Multilingue

### Français
```
Insultes: connard, salope, crétin, débile, abruti
Modérées: stupide, bête, nul, pathétique, ridicule
Expressions: ferme ta gueule, va te faire, espèce de
```

### Anglais
```
Insultes: fuck, asshole, bitch, bastard, cunt
Modérées: stupid, dumb, loser, pathetic, ridiculous
Expressions: shut up, fuck off, you suck
```

### Arabe
```
Insultes: كلب, حمار, غبي, أحمق, حقير, وسخ
```

---

## 📝 Commandes Utiles

### Tester la Modération
```bash
php test_moderation_amelioree.php
```

### Nettoyer le Cache
```bash
php bin/console cache:clear
```

### Vérifier les Erreurs
```bash
php bin/console lint:container
```

---

## 🎯 Cas d'Usage Réels

### Scénario 1: Critique Constructive vs Insulte

**Message**: "Je pense que cette idée n'est pas optimale"
- Score: 0.0
- Statut: ✅ APPROUVÉ
- Raison: Critique constructive et polie

**Message**: "C'est vraiment stupide comme idée"
- Score: 1.0
- Statut: ❌ BLOQUÉ
- Raison: Insulte avec intensificateur

### Scénario 2: Désaccord Poli vs Agressif

**Message**: "Je ne suis pas d'accord avec vous"
- Score: 0.0
- Statut: ✅ APPROUVÉ
- Raison: Désaccord exprimé poliment

**Message**: "T'es vraiment con de penser ça"
- Score: 1.0
- Statut: ❌ BLOQUÉ
- Raison: Insulte directe

### Scénario 3: Frustration vs Agression

**Message**: "Je suis frustré par cette situation"
- Score: 0.0
- Statut: ✅ APPROUVÉ
- Raison: Expression d'émotion sans insulte

**Message**: "Ferme ta gueule avec tes idées"
- Score: 1.0
- Statut: ❌ BLOQUÉ
- Raison: Expression agressive

---

## 🔍 Dépannage

### Le message n'est pas bloqué alors qu'il devrait

1. Vérifier le score:
```bash
php test_moderation_amelioree.php
```

2. Vérifier le cache:
```bash
php bin/console cache:clear
```

3. Vérifier les logs:
```bash
tail -f var/log/dev.log
```

### Le message est bloqué alors qu'il ne devrait pas

Si vous pensez qu'un message légitime est bloqué à tort:

1. Vérifier le score et les mots détectés
2. Ajuster les seuils si nécessaire
3. Retirer le mot de la liste si c'est un faux positif

---

## 📚 Documentation Complète

- **AMELIORATIONS_MODERATION_TOXIQUE.md**: Documentation technique détaillée
- **test_moderation_amelioree.php**: Script de test avec 15 cas
- **GUIDE_MODERATION_AMELIOREE.md**: Ce guide rapide

---

## ✅ Checklist de Validation

- [x] Liste de mots enrichie (80+ mots)
- [x] Patterns contextuels ajoutés
- [x] Scoring par gravité implémenté
- [x] Support multilingue (FR/EN/AR)
- [x] Tests unitaires (100% réussite)
- [x] Cache nettoyé
- [x] Documentation créée

---

## 🎉 Résultat

Le système de modération est maintenant **beaucoup plus efficace** et détecte:
- ✅ Expressions avec intensificateurs ("vraiment", "tellement", "très")
- ✅ Expressions dégradantes ("espèce de", "sale")
- ✅ Menaces et harcèlement
- ✅ 80+ mots toxiques en 3 langues
- ✅ Contexte et patterns, pas seulement des mots isolés

**Taux de détection: 100% sur les tests!** 🚀
