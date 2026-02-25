# ⚡ Quick Start - Modération Intelligente

## 🚀 Démarrage Rapide (5 minutes)

### Étape 1: Vérifier l'Installation ✅

```bash
# Vérifier que la migration est appliquée
php bin/console doctrine:migrations:status

# Vider le cache
php bin/console cache:clear
```

### Étape 2: Lancer le Serveur 🌐

```bash
# Option 1: Symfony CLI
symfony server:start

# Option 2: PHP Built-in Server
php -S localhost:8000 -t public
```

### Étape 3: Test Rapide (2 minutes) 🧪

**Dans votre terminal:**
```bash
php demo_moderation.php
```

**Résultat attendu:**
```
✅ 5-7 tests réussis sur 11
🔴 Messages toxiques bloqués
🟠 Messages spam détectés
```

### Étape 4: Test dans le Navigateur 🌐

1. Ouvrez `http://localhost:8000`
2. Connectez-vous
3. Accédez à un chatroom
4. Testez ces 3 messages:

```
✅ "Bonjour tout le monde!"
   → Doit passer

🔴 "You are a fucking asshole"
   → Doit être bloqué

🟠 "Visitez https://spam.com"
   → Doit être masqué (ou passer - à améliorer)
```

---

## 📊 Vérification Rapide

### Base de Données
```sql
SELECT 
    moderation_status,
    COUNT(*) as total
FROM message
GROUP BY moderation_status;
```

**Résultat attendu:**
```
| status   | total |
|----------|-------|
| approved | X     |
| blocked  | Y     |
| hidden   | Z     |
```

---

## 🎯 Ce Qui Fonctionne

✅ **Détection de toxicité:**
- Insultes graves en anglais
- Mots toxiques multiples
- Insultes en français (connard, salaud)

✅ **Détection de spam:**
- URLs complètes
- Trop de liens
- Messages répétitifs

✅ **Interface:**
- Badges visuels
- Flash messages
- Visibilité selon rôle

---

## ⚠️ Limitations Connues

❌ **Ne fonctionne pas encore:**
- Mots courts (idiot, con)
- Majuscules avec accents (ARRÊTEZ)
- WWW sans https
- Caractères répétés (aaaa)
- Messages trop courts

**Taux de réussite:** 60-80%

---

## 🔧 Configuration Rapide

### Rendre Plus Strict
```php
// src/Service/ModerationService.php
private const TOXICITY_THRESHOLD = 0.5;  // Au lieu de 0.7
private const SPAM_THRESHOLD = 0.4;      // Au lieu de 0.6
```

### Rendre Plus Permissif
```php
private const TOXICITY_THRESHOLD = 0.8;  // Au lieu de 0.7
private const SPAM_THRESHOLD = 0.7;      // Au lieu de 0.6
```

### Ajouter un Mot Toxique
```php
private const TOXIC_WORDS = [
    // ... mots existants
    'nouveau_mot',
];
```

---

## 📚 Documentation Complète

Pour plus de détails, consultez:

1. **`MODERATION_INTELLIGENTE.md`** - Documentation technique complète
2. **`EXEMPLES_TESTS_VISUELS.md`** - Exemples visuels de tests
3. **`GUIDE_UTILISATION_MODERATION.md`** - Guide d'utilisation
4. **`RESUME_FINAL_MODERATION.md`** - Résumé complet

---

## 🆘 Aide Rapide

### Problème: Badge ne s'affiche pas
```bash
php bin/console cache:clear
```

### Problème: Message toxique n'est pas bloqué
```php
// Vérifier que le mot est dans TOXIC_WORDS
// src/Service/ModerationService.php
```

### Problème: Erreur 500
```bash
# Voir les logs
tail -f var/log/dev.log
```

---

## ✅ Checklist de Validation

- [ ] Migration appliquée
- [ ] Cache vidé
- [ ] Serveur lancé
- [ ] Test démo exécuté
- [ ] Test navigateur effectué
- [ ] Base de données vérifiée
- [ ] Documentation lue

---

## 🎉 Félicitations!

Votre système de modération intelligente est opérationnel!

**Prochaines étapes:**
1. Tester avec de vrais utilisateurs
2. Collecter des métriques
3. Ajuster les seuils
4. Enrichir les listes de mots

---

**Temps total:** 5-10 minutes  
**Niveau:** Débutant  
**Statut:** ✅ Prêt à l'emploi
