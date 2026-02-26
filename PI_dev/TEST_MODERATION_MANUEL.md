# Guide de Test Manuel - Modération Intelligente

## Prérequis
1. Serveur Symfony lancé: `symfony server:start` ou `php -S localhost:8000 -t public`
2. Base de données à jour avec la migration
3. Utilisateur connecté avec accès à un chatroom

## Tests à Effectuer

### 🟢 Test 1: Message Normal (Doit Passer)
**Action:** Envoyez ce message dans le chatroom:
```
Bonjour tout le monde! Comment allez-vous aujourd'hui?
```

**Résultat Attendu:**
- ✅ Message publié normalement
- ✅ Visible par tous les utilisateurs
- ✅ Pas de badge de modération
- ✅ Status: `approved`

---

### 🔴 Test 2: Message Toxique - Insultes (Doit Être Bloqué)
**Action:** Envoyez ce message:
```
Tu es un idiot et un con
```

**Résultat Attendu:**
- ❌ Message bloqué (non enregistré)
- ❌ Flash message rouge: "Ce message viole les règles de la communauté"
- ❌ Redirection vers le chatroom
- ❌ Message non visible dans la liste

---

### 🔴 Test 3: Message Toxique - Anglais (Doit Être Bloqué)
**Action:** Envoyez ce message:
```
You are a fucking asshole
```

**Résultat Attendu:**
- ❌ Message bloqué
- ❌ Flash message d'erreur
- ❌ Non visible

---

### 🔴 Test 4: Message Toxique - Arabe (Doit Être Bloqué)
**Action:** Envoyez ce message:
```
أنت كلب وحمار
```

**Résultat Attendu:**
- ❌ Message bloqué
- ❌ Flash message d'erreur

---

### 🔴 Test 5: CRIER en Majuscules (Doit Être Bloqué)
**Action:** Envoyez ce message:
```
ARRÊTE DE FAIRE ÇA MAINTENANT!!!!
```

**Résultat Attendu:**
- ❌ Message bloqué (score toxicité élevé)
- ❌ Flash message d'erreur

---

### 🟠 Test 6: Message Spam - URL (Doit Être Masqué)
**Action:** Envoyez ce message:
```
Visitez https://www.spam-site.com pour gagner de l'argent!
```

**Résultat Attendu:**
- ⚠️ Message enregistré mais masqué
- ⚠️ Flash message orange: "Votre message a été marqué comme spam..."
- ⚠️ Badge orange visible: "🚫 Ce message est considéré comme spam"
- ⚠️ Visible uniquement pour vous (l'auteur)
- ⚠️ Status: `hidden`

---

### 🟠 Test 7: Message Spam - WWW (Doit Être Masqué)
**Action:** Envoyez ce message:
```
Allez sur www.publicite.com maintenant
```

**Résultat Attendu:**
- ⚠️ Message masqué
- ⚠️ Badge spam orange
- ⚠️ Visible uniquement pour l'auteur

---

### 🟠 Test 8: Caractères Répétés (Doit Être Masqué)
**Action:** Envoyez ce message:
```
aaaaaaaaaa
```

**Résultat Attendu:**
- ⚠️ Message masqué
- ⚠️ Badge spam

---

### 🟠 Test 9: Tout en Majuscules (Doit Être Masqué)
**Action:** Envoyez ce message:
```
ACHETEZ MAINTENANT PROMOTION LIMITÉE
```

**Résultat Attendu:**
- ⚠️ Message masqué
- ⚠️ Badge spam

---

### 🟠 Test 10: Mots-clés Spam (Doit Être Masqué)
**Action:** Envoyez ce message:
```
Click here to win the lottery prize!
```

**Résultat Attendu:**
- ⚠️ Message masqé
- ⚠️ Badge spam

---

### 🟠 Test 11: Trop de Liens (Doit Être Masqué)
**Action:** Envoyez ce message:
```
Visitez https://site1.com et https://site2.com et https://site3.com
```

**Résultat Attendu:**
- ⚠️ Message masqué
- ⚠️ Badge spam avec mention "TROP_DE_LIENS"

---

### 🟢 Test 12: Message Limite (Doit Passer)
**Action:** Envoyez ce message:
```
C'est vraiment nul ce que tu fais
```

**Résultat Attendu:**
- ✅ Message publié (score < 0.7)
- ✅ Visible par tous
- ✅ Pas de badge

---

### 🟢 Test 13: Message avec Émojis (Doit Passer)
**Action:** Envoyez ce message:
```
Bonjour 😊 Comment allez-vous? 👋
```

**Résultat Attendu:**
- ✅ Message publié normalement
- ✅ Émojis affichés correctement

---

## Vérification en Base de Données

Après avoir envoyé plusieurs messages de test, vérifiez dans la base de données:

```sql
-- Voir tous les messages avec leur statut de modération
SELECT 
    id,
    content,
    is_toxic,
    is_spam,
    moderation_status,
    toxicity_score,
    spam_score,
    moderation_reason
FROM message
ORDER BY created_at DESC
LIMIT 20;
```

**Résultats Attendus:**
- Messages normaux: `moderation_status = 'approved'`, scores faibles
- Messages toxiques: `is_toxic = 1`, `moderation_status = 'blocked'`, `toxicity_score >= 0.7`
- Messages spam: `is_spam = 1`, `moderation_status = 'hidden'`, `spam_score >= 0.6`

---

## Test de Visibilité (Multi-Utilisateurs)

### Scénario:
1. **Utilisateur A** envoie un message spam
2. **Utilisateur B** (autre utilisateur) consulte le chatroom
3. **Modérateur** consulte le chatroom

**Résultats Attendus:**
- **Utilisateur A** voit son message avec badge orange et avertissement
- **Utilisateur B** ne voit PAS le message spam
- **Modérateur** voit le message avec badge orange

---

## Vérification des Logs

Consultez les logs Symfony pour voir les décisions de modération:

```bash
# Windows PowerShell
Get-Content var/log/dev.log -Tail 50 | Select-String "moderation"

# Ou ouvrir directement le fichier
notepad var/log/dev.log
```

**Recherchez:**
- Warnings pour spam détecté
- Erreurs pour messages toxiques
- Informations sur les scores calculés

---

## Tests Avancés

### Test 14: Messages Répétitifs (Spam Utilisateur)
**Action:** Envoyez rapidement 3 fois le même message:
```
Bonjour
Bonjour
Bonjour
```

**Note:** Cette fonctionnalité nécessite une implémentation supplémentaire dans le controller pour tracker les messages récents de l'utilisateur.

---

### Test 15: Modification des Seuils

Dans `src/Service/ModerationService.php`, modifiez temporairement:

```php
private const TOXICITY_THRESHOLD = 0.5;  // Plus strict
private const SPAM_THRESHOLD = 0.4;      // Plus strict
```

Puis testez à nouveau les messages limites pour voir la différence.

---

## Checklist de Validation

- [ ] Messages normaux passent sans problème
- [ ] Messages toxiques sont bloqués avec flash message
- [ ] Messages spam sont masqués avec avertissement
- [ ] Badges de modération s'affichent correctement
- [ ] Couleurs des badges (rouge pour toxique, orange pour spam)
- [ ] Visibilité correcte selon le rôle (auteur/modérateur/utilisateur)
- [ ] Scores enregistrés en base de données
- [ ] Raisons de modération enregistrées
- [ ] Logs générés correctement
- [ ] Interface responsive et claire

---

## Problèmes Courants

### Le message toxique n'est pas bloqué
**Solution:** Vérifiez que le mot est dans la liste `TOXIC_WORDS` du service

### Le message spam n'est pas masqué
**Solution:** Vérifiez les patterns regex dans `SPAM_PATTERNS`

### Badge ne s'affiche pas
**Solution:** Videz le cache: `php bin/console cache:clear`

### Erreur 500
**Solution:** Vérifiez les logs dans `var/log/dev.log`

---

## Commandes Utiles

```bash
# Vider le cache
php bin/console cache:clear

# Voir les logs en temps réel
tail -f var/log/dev.log

# Réinitialiser la base de données (ATTENTION: efface les données)
php bin/console doctrine:schema:drop --force
php bin/console doctrine:schema:create
php bin/console doctrine:migrations:migrate

# Lancer les tests unitaires
php bin/phpunit tests/Service/ModerationServiceTest.php
```

---

## Rapport de Test

Après avoir effectué tous les tests, remplissez ce rapport:

| Test | Message | Résultat Attendu | Résultat Obtenu | ✅/❌ |
|------|---------|------------------|-----------------|-------|
| 1 | Message normal | Approuvé | | |
| 2 | Insultes FR | Bloqué | | |
| 3 | Insultes EN | Bloqué | | |
| 4 | Insultes AR | Bloqué | | |
| 5 | Majuscules | Bloqué | | |
| 6 | URL spam | Masqué | | |
| 7 | WWW spam | Masqué | | |
| 8 | Caractères répétés | Masqué | | |
| 9 | Tout majuscules | Masqué | | |
| 10 | Mots-clés spam | Masqué | | |
| 11 | Trop de liens | Masqué | | |
| 12 | Message limite | Approuvé | | |
| 13 | Avec émojis | Approuvé | | |

---

**Date du test:** _______________  
**Testeur:** _______________  
**Version:** 1.0  
**Statut global:** ⬜ Réussi / ⬜ Échec partiel / ⬜ Échec
