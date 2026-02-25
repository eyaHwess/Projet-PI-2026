# 🧪 Test de Correction - Modération

## ✅ Correction Appliquée

Le message **"you are a fucking asshole"** devrait maintenant être **BLOQUÉ**.

---

## 🎯 Test à Effectuer MAINTENANT

### Étape 1: Rafraîchir la Page
Appuyez sur **F5** dans votre navigateur pour recharger le chatroom.

### Étape 2: Taper le Message
Dans la zone de texte, tapez exactement:
```
you are a fucking asshole
```

### Étape 3: Envoyer
Cliquez sur le bouton d'envoi (✈️).

---

## 📋 Résultat Attendu

### ✅ CE QUI DOIT SE PASSER:

**1. Flash Message Rouge (en haut de la page):**
```
┌──────────────────────────────────────────────────────┐
│ ⚠️ Ce message viole les règles de la communauté     │
└──────────────────────────────────────────────────────┘
```

**2. Le Message N'APPARAÎT PAS:**
- Le chatroom ne montre PAS le message
- Vous restez sur la même page
- Le champ de texte est vidé

**3. Aucun Badge:**
- Pas de badge rouge ou orange
- Le message n'est simplement pas publié

---

## ❌ CE QUI NE DOIT PAS SE PASSER:

- ❌ Le message ne doit PAS apparaître dans le chatroom
- ❌ Pas de badge bleu (message normal)
- ❌ Pas de message "envoyé avec succès"

---

## 🔍 Vérification Supplémentaire

### Dans le Terminal:
```bash
php test_quick.php
```

**Résultat attendu:**
```
Message: "you are a fucking asshole"
Score toxicité: 1
Est toxique: OUI
Statut: blocked
Raison: Ce message viole les règles de la communauté
```

### Dans la Base de Données:
```sql
SELECT * FROM message 
WHERE content LIKE '%fucking%'
ORDER BY created_at DESC
LIMIT 5;
```

**Résultat attendu:** Aucune ligne (le message est bloqué)

---

## 📸 Capture d'Écran Attendue

```
┌─────────────────────────────────────────────────────────┐
│                      CHATROOM                           │
├─────────────────────────────────────────────────────────┤
│                                                         │
│  ⚠️ Ce message viole les règles de la communauté       │
│                                                         │
├─────────────────────────────────────────────────────────┤
│                                                         │
│  [Messages précédents du chatroom...]                  │
│                                                         │
│  👤 Autre Utilisateur                                   │
│  ┌─────────────────────────────────────────────────┐   │
│  │ Bonjour tout le monde!                          │   │
│  └─────────────────────────────────────────────────┘   │
│                                                         │
├─────────────────────────────────────────────────────────┤
│  😊 [Tapez votre message...]              ✈️           │
└─────────────────────────────────────────────────────────┘
```

**Note:** Le message toxique n'apparaît PAS dans la liste.

---

## 🎯 Autres Messages à Tester

Après avoir confirmé que le premier message est bloqué, testez ces autres messages:

### Test 2: Message Normal (doit PASSER)
```
Bonjour tout le monde!
```
**Résultat attendu:** ✅ Message publié normalement

### Test 3: Autre Message Toxique (doit être BLOQUÉ)
```
fuck you
```
**Résultat attendu:** 🔴 Flash message rouge + non publié

### Test 4: Message avec Émoji (doit PASSER)
```
Merci beaucoup 😊
```
**Résultat attendu:** ✅ Message publié normalement

---

## ✅ Checklist de Validation

- [ ] Page rafraîchie (F5)
- [ ] Message "you are a fucking asshole" tapé
- [ ] Bouton d'envoi cliqué
- [ ] Flash message rouge affiché
- [ ] Message NON visible dans le chatroom
- [ ] Test terminal exécuté (php test_quick.php)
- [ ] Résultat: "Est toxique: OUI"
- [ ] Base de données vérifiée (aucun message toxique)

---

## 🐛 Si Ça Ne Fonctionne Pas

### Problème 1: Le message passe quand même
**Solution:**
```bash
# Vider le cache à nouveau
php bin/console cache:clear

# Redémarrer le serveur
# Ctrl+C puis relancer
symfony server:start
```

### Problème 2: Erreur 500
**Solution:**
```bash
# Voir les logs
tail -f var/log/dev.log
```

### Problème 3: Flash message ne s'affiche pas
**Vérification:**
- Le message est-il dans la liste du chatroom?
- Si OUI → Le blocage ne fonctionne pas
- Si NON → Le blocage fonctionne mais le flash message ne s'affiche pas

---

## 📞 Commandes Utiles

```bash
# Test rapide
php test_quick.php

# Vider le cache
php bin/console cache:clear

# Voir les logs
tail -f var/log/dev.log

# Tests unitaires
php bin/phpunit tests/Service/ModerationServiceTest.php
```

---

## 🎉 Succès!

Si vous voyez le flash message rouge et que le message n'apparaît pas, **la correction fonctionne!** ✅

Le système de modération bloque maintenant correctement les messages toxiques.

---

**Date:** 24 février 2026  
**Statut:** ✅ Prêt à tester  
**Temps estimé:** 2 minutes
