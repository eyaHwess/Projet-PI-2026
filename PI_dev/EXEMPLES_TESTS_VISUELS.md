# 🎯 Exemples de Tests Visuels - Modération Intelligente

## 📋 Prérequis

1. ✅ Serveur Symfony lancé
2. ✅ Connecté avec un compte utilisateur
3. ✅ Accès à un chatroom actif

---

## 🟢 TEST 1: Message Normal (Doit Passer)

### Action
Dans le chatroom, tapez et envoyez:
```
Bonjour tout le monde! Comment allez-vous aujourd'hui?
```

### Résultat Attendu
```
┌─────────────────────────────────────────┐
│ 👤 Votre Nom                            │
│                                         │
│ ┌─────────────────────────────────────┐ │
│ │ Bonjour tout le monde! Comment      │ │
│ │ allez-vous aujourd'hui?             │ │
│ └─────────────────────────────────────┘ │
│                                         │
│ 🕐 Il y a quelques secondes             │
└─────────────────────────────────────────┘
```

✅ **Validation:**
- Message visible par tous
- Pas de badge rouge ou orange
- Pas de message d'erreur
- Message enregistré en base avec `moderation_status = 'approved'`

---

## 🔴 TEST 2: Message Toxique - Insultes Graves (Doit Être Bloqué)

### Action
Tapez et envoyez:
```
You are a fucking asshole
```

### Résultat Attendu

**1. Flash Message (en haut de la page):**
```
┌──────────────────────────────────────────────────────┐
│ ⚠️ Ce message viole les règles de la communauté     │
└──────────────────────────────────────────────────────┘
```

**2. Le message N'APPARAÎT PAS dans le chatroom**

**3. Redirection vers le chatroom**

✅ **Validation:**
- Message flash rouge affiché
- Message NON visible dans la liste
- Message NON enregistré en base (ou avec `moderation_status = 'blocked'`)
- Vous restez sur la page du chatroom

---

## 🟠 TEST 3: Message Spam - URL (Doit Être Masqué)

### Action
Tapez et envoyez:
```
Visitez https://www.spam-site.com pour gagner de l'argent!
```

### Résultat Attendu

**1. Flash Message (orange):**
```
┌──────────────────────────────────────────────────────────────┐
│ ⚠️ Votre message a été marqué comme spam et sera masqué     │
│    pour les autres utilisateurs.                            │
└──────────────────────────────────────────────────────────────┘
```

**2. Votre Vue (Auteur):**
```
┌─────────────────────────────────────────────────────────┐
│ 👤 Votre Nom                                            │
│                                                         │
│ ┌─────────────────────────────────────────────────────┐ │
│ │ 🚫 Ce message est considéré comme spam             │ │
│ │ ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ │ │
│ │                                                     │ │
│ │ 👁️ Votre message est masqué pour les autres       │ │
│ │    utilisateurs                                     │ │
│ │                                                     │ │
│ │ ┌─────────────────────────────────────────────────┐ │ │
│ │ │ Visitez https://www.spam-site.com pour gagner  │ │ │
│ │ │ de l'argent!                                    │ │ │
│ │ └─────────────────────────────────────────────────┘ │ │
│ └─────────────────────────────────────────────────────┘ │
│                                                         │
│ 🕐 Il y a quelques secondes                             │
└─────────────────────────────────────────────────────────┘
```

**3. Vue d'un Autre Utilisateur:**
```
(Le message n'apparaît PAS du tout)
```

**4. Vue d'un Modérateur:**
```
┌─────────────────────────────────────────────────────────┐
│ 👤 Nom de l'Auteur                                      │
│                                                         │
│ ┌─────────────────────────────────────────────────────┐ │
│ │ 🚫 Ce message est considéré comme spam             │ │
│ │ ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ │ │
│ │                                                     │ │
│ │ 👁️ Message masqué (visible uniquement par les     │ │
│ │    modérateurs)                                     │ │
│ │                                                     │ │
│ │ ┌─────────────────────────────────────────────────┐ │ │
│ │ │ Visitez https://www.spam-site.com pour gagner  │ │ │
│ │ │ de l'argent!                                    │ │ │
│ │ └─────────────────────────────────────────────────┘ │ │
│ └─────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────┘
```

✅ **Validation:**
- Badge orange visible
- Message visible pour vous (auteur)
- Message INVISIBLE pour les autres utilisateurs
- Message visible pour les modérateurs
- Enregistré avec `moderation_status = 'hidden'`

---

## 🔴 TEST 4: Message Toxique - Plusieurs Insultes

### Action
Tapez et envoyez:
```
Espèce de connard, imbécile et crétin
```

### Résultat Attendu
```
┌──────────────────────────────────────────────────────┐
│ ⚠️ Ce message viole les règles de la communauté     │
└──────────────────────────────────────────────────────┘
```

✅ **Validation:**
- Message bloqué
- Flash message rouge
- Score de toxicité élevé (> 0.7)

---

## 🟠 TEST 5: Message Spam - Trop de Liens

### Action
Tapez et envoyez:
```
Visitez https://site1.com et https://site2.com et https://site3.com
```

### Résultat Attendu
```
┌─────────────────────────────────────────────────────────┐
│ 👤 Votre Nom                                            │
│                                                         │
│ ┌─────────────────────────────────────────────────────┐ │
│ │ 🚫 Ce message est considéré comme spam             │ │
│ │ ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━ │ │
│ │                                                     │ │
│ │ ┌─────────────────────────────────────────────────┐ │ │
│ │ │ Visitez https://site1.com et                   │ │ │
│ │ │ https://site2.com et https://site3.com         │ │ │
│ │ └─────────────────────────────────────────────────┘ │ │
│ └─────────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────────┘
```

✅ **Validation:**
- Badge orange "spam"
- Masqué pour les autres

---

## 🟢 TEST 6: Message avec Émojis (Doit Passer)

### Action
Tapez et envoyez:
```
Bonjour 😊 Comment allez-vous? 👋 Bonne journée! 🌞
```

### Résultat Attendu
```
┌─────────────────────────────────────────┐
│ 👤 Votre Nom                            │
│                                         │
│ ┌─────────────────────────────────────┐ │
│ │ Bonjour 😊 Comment allez-vous? 👋  │ │
│ │ Bonne journée! 🌞                   │ │
│ └─────────────────────────────────────┘ │
│                                         │
│ 🕐 Il y a quelques secondes             │
└─────────────────────────────────────────┘
```

✅ **Validation:**
- Message publié normalement
- Émojis affichés correctement
- Pas de badge

---

## 📸 Captures d'Écran Attendues

### Badge Toxique (Rouge)
```
┌────────────────────────────────────────────────────┐
│ ⚠️ Ce message viole les règles de la communauté   │
└────────────────────────────────────────────────────┘
Couleur: Fond rouge dégradé (#ff4444 → #cc0000)
Bordure: Rouge #ff0000
Icône: ⚠️ Triangle d'avertissement
```

### Badge Spam (Orange)
```
┌────────────────────────────────────────────────────┐
│ 🚫 Ce message est considéré comme spam            │
└────────────────────────────────────────────────────┘
Couleur: Fond orange dégradé (#ff9800 → #f57c00)
Bordure: Orange #ff6f00
Icône: 🚫 Interdit
```

---

## 🗄️ Vérification en Base de Données

Après chaque test, vérifiez dans la base:

```sql
-- Voir les 10 derniers messages
SELECT 
    id,
    SUBSTRING(content, 1, 50) as message,
    is_toxic,
    is_spam,
    moderation_status,
    ROUND(toxicity_score, 2) as tox_score,
    ROUND(spam_score, 2) as spam_score,
    moderation_reason
FROM message
ORDER BY created_at DESC
LIMIT 10;
```

### Résultats Attendus

**Test 1 (Normal):**
```
| id | message                    | is_toxic | is_spam | status   | tox | spam | reason |
|----|----------------------------|----------|---------|----------|-----|------|--------|
| 1  | Bonjour tout le monde!...  | 0        | 0       | approved | 0.0 | 0.0  | NULL   |
```

**Test 2 (Toxique):**
```
| id | message                    | is_toxic | is_spam | status  | tox  | spam | reason                              |
|----|----------------------------|----------|---------|---------|------|------|-------------------------------------|
| 2  | You are a fucking...       | 1        | 0       | blocked | 0.80 | 0.0  | Ce message viole les règles...      |
```

**Test 3 (Spam):**
```
| id | message                    | is_toxic | is_spam | status | tox | spam | reason                              |
|----|----------------------------|----------|---------|--------|-----|------|-------------------------------------|
| 3  | Visitez https://spam...    | 0        | 1       | hidden | 0.0 | 0.80 | Ce message est considéré comme spam |
```

---

## 🎬 Scénario Complet de Test

### Étape 1: Préparation
1. Ouvrez votre navigateur
2. Connectez-vous à votre application
3. Accédez à un chatroom
4. Ouvrez un second onglet avec un autre utilisateur (ou mode incognito)

### Étape 2: Tests Séquentiels

**Minute 0-2:** Test messages normaux
```
✅ "Bonjour!"
✅ "Comment ça va?"
✅ "Merci pour votre aide 😊"
```

**Minute 2-4:** Test messages toxiques
```
❌ "You are a fucking asshole"
❌ "Espèce de connard"
```

**Minute 4-6:** Test messages spam
```
⚠️ "Visitez https://spam.com"
⚠️ "https://site1.com https://site2.com https://site3.com"
```

**Minute 6-8:** Vérification multi-utilisateurs
1. Utilisateur A envoie un message spam
2. Utilisateur B rafraîchit → Ne voit PAS le message
3. Modérateur rafraîchit → Voit le message avec badge

### Étape 3: Vérification Base de Données
```sql
SELECT 
    moderation_status,
    COUNT(*) as total
FROM message
WHERE created_at > NOW() - INTERVAL 10 MINUTE
GROUP BY moderation_status;
```

Résultat attendu:
```
| status   | total |
|----------|-------|
| approved | 3     |
| blocked  | 2     |
| hidden   | 2     |
```

---

## 📊 Checklist de Validation

### Interface Utilisateur
- [ ] Messages normaux s'affichent correctement
- [ ] Flash message rouge pour messages toxiques
- [ ] Flash message orange pour messages spam
- [ ] Badge rouge visible sur messages toxiques
- [ ] Badge orange visible sur messages spam
- [ ] Messages spam masqués pour autres utilisateurs
- [ ] Messages spam visibles pour auteur
- [ ] Messages spam visibles pour modérateurs
- [ ] Émojis affichés correctement

### Base de Données
- [ ] Champ `is_toxic` = 1 pour messages toxiques
- [ ] Champ `is_spam` = 1 pour messages spam
- [ ] `moderation_status` correct (approved/blocked/hidden)
- [ ] `toxicity_score` calculé (0.0 à 1.0)
- [ ] `spam_score` calculé (0.0 à 1.0)
- [ ] `moderation_reason` rempli pour messages modérés

### Comportement
- [ ] Messages toxiques ne sont PAS enregistrés
- [ ] Messages spam sont enregistrés mais masqués
- [ ] Redirection après envoi fonctionne
- [ ] Pas d'erreur 500
- [ ] Logs générés correctement

---

## 🐛 Problèmes Courants et Solutions

### Problème 1: Le badge ne s'affiche pas
**Solution:**
```bash
php bin/console cache:clear
```

### Problème 2: Message toxique n'est pas bloqué
**Vérification:**
```php
// Dans src/Service/ModerationService.php
// Vérifiez que le mot est dans TOXIC_WORDS
private const TOXIC_WORDS = [
    'fuck', 'asshole', // etc.
];
```

### Problème 3: Message spam n'est pas masqué
**Vérification:**
```sql
-- Vérifiez le score en base
SELECT content, spam_score, moderation_status
FROM message
WHERE content LIKE '%https://%';
```

### Problème 4: Erreur 500
**Solution:**
```bash
# Voir les logs
tail -f var/log/dev.log

# Ou sur Windows
Get-Content var/log/dev.log -Tail 50
```

---

## 📹 Vidéo de Démonstration (Script)

**0:00-0:30** - Introduction
- Montrer la page du chatroom
- Expliquer le système de modération

**0:30-1:00** - Test message normal
- Taper "Bonjour tout le monde!"
- Montrer qu'il s'affiche normalement

**1:00-1:30** - Test message toxique
- Taper "You are a fucking asshole"
- Montrer le flash message rouge
- Montrer que le message n'apparaît pas

**1:30-2:00** - Test message spam
- Taper "Visitez https://spam.com"
- Montrer le flash message orange
- Montrer le badge orange

**2:00-2:30** - Test multi-utilisateurs
- Montrer que l'auteur voit son message spam
- Montrer qu'un autre utilisateur ne le voit pas
- Montrer qu'un modérateur le voit

**2:30-3:00** - Vérification base de données
- Montrer les requêtes SQL
- Montrer les scores calculés

---

## 🎯 Objectif Final

À la fin de ces tests, vous devriez avoir:

✅ 3 messages normaux publiés  
✅ 2 messages toxiques bloqués  
✅ 2 messages spam masqués  
✅ Badges visuels fonctionnels  
✅ Visibilité correcte selon les rôles  
✅ Données correctes en base  

**Taux de réussite attendu:** 80-100%

---

**Durée totale des tests:** 15-20 minutes  
**Niveau de difficulté:** Facile  
**Prérequis techniques:** Aucun (juste un navigateur)
