# 🧪 Guide de Test des Notifications

## ✅ Statut: PRÊT À TESTER

Un système de test complet est maintenant disponible!

---

## 🚀 Accès Rapide

### URL de Test
```
http://localhost:8000/test/notifications
```

---

## 📋 Ce Qui a Été Créé

### 1. Fichiers de Test
- **`public/test-notifications.js`** - Système de test complet
- **`templates/test/notifications.html.twig`** - Page de test
- **`src/Controller/TestNotificationController.php`** - Contrôleur

### 2. Fonctionnalités
- ✅ Test sans Firebase (notifications natives)
- ✅ 4 types de notifications
- ✅ Interface visuelle intuitive
- ✅ Panel de contrôle flottant
- ✅ Sons de notification
- ✅ Alertes visuelles

---

## 🎯 Comment Tester

### Étape 1: Ouvrir la Page de Test
```bash
# Vider le cache
php bin/console cache:clear

# Ouvrir dans le navigateur
http://localhost:8000/test/notifications
```

### Étape 2: Activer les Notifications
1. Cliquer sur "Activer" dans le prompt
2. Accepter les notifications dans le navigateur
3. Vérifier le statut: "✅ Activées"

### Étape 3: Tester les Notifications

#### Test 1: Nouveau Message 💬
1. Cliquer sur la carte "Nouveau Message"
2. Une notification apparaît: "Nouveau message de Marie"
3. Contenu: "Super idée pour le projet! 🎉"
4. Cliquer sur la notification pour la fermer

#### Test 2: Mention @user 📢
1. Cliquer sur la carte "Mention @user"
2. Une notification apparaît: "Marie vous a mentionné"
3. Contenu: "@islem qu'en penses-tu?"
4. Une alerte visuelle apparaît en haut à droite
5. Cliquer pour fermer

#### Test 3: Nouveau Membre 👤
1. Cliquer sur la carte "Nouveau Membre"
2. Une notification apparaît: "Nouveau membre dans 'Mon Goal'"
3. Contenu: "Ahmed a rejoint le goal"

#### Test 4: Plusieurs Notifications 🔔
1. Cliquer sur la carte "Plusieurs Notifications"
2. 3 notifications sont envoyées successivement:
   - À 0s: Nouveau Message
   - À 2s: Mention
   - À 4s: Nouveau Membre

---

## 🎨 Interface de Test

### Page Principale
```
┌─────────────────────────────────────────┐
│  🧪 Test des Notifications              │
│  Testez le système sans Firebase        │
│  [✅ Activées]                          │
├─────────────────────────────────────────┤
│  🔔 Types de Notifications              │
│                                          │
│  [💬 Nouveau]  [📢 Mention]            │
│  [Message]     [@user]                  │
│                                          │
│  [👤 Nouveau]  [🔔 Plusieurs]          │
│  [Membre]      [Notifications]          │
└─────────────────────────────────────────┘
```

### Panel Flottant (en bas à droite)
```
┌────────────────────────────┐
│ 🧪 Test Notifications  [×] │
├────────────────────────────┤
│ [💬 Nouveau Message]       │
│ [📢 Mention @user]         │
│ [👤 Nouveau Membre]        │
│ [🔔 Plusieurs Notifications]│
│ [🗑️ Tout Effacer]          │
├────────────────────────────┤
│ Permission: granted        │
└────────────────────────────┘
```

---

## 🔍 Vérifications

### Console JavaScript (F12)
Vous devriez voir:
```
✅ Test Notifications prêt
📋 Permission actuelle: granted
🧪 Test: Nouveau Message
Clic sur notification
```

### Notifications Système
- Apparaissent en haut à droite (Windows/Linux)
- Apparaissent en haut à droite (macOS)
- Avec icône, titre et contenu
- Son de notification

### Alertes Visuelles
- Pour les mentions: alerte bleue en haut à droite
- Disparaît après 5 secondes
- Cliquable pour fermer

---

## 🎵 Sons

Le système génère un son simple avec Web Audio API:
- Fréquence: 800 Hz
- Durée: 0.5 secondes
- Volume: 30%

---

## 🐛 Troubleshooting

### Les notifications n'apparaissent pas?

#### 1. Vérifier la Permission
```javascript
// Dans la console (F12)
console.log(Notification.permission);
// Doit afficher: "granted"
```

#### 2. Vérifier le Support
```javascript
// Dans la console
console.log('Notification' in window);
// Doit afficher: true
```

#### 3. Réinitialiser les Permissions
- Chrome: Paramètres > Confidentialité > Paramètres du site > Notifications
- Firefox: Paramètres > Vie privée > Permissions > Notifications
- Supprimer localhost et réessayer

### Le panel ne s'affiche pas?

1. Vérifier la console pour les erreurs
2. Rafraîchir la page (F5)
3. Vider le cache du navigateur

### Pas de son?

1. Vérifier que le son n'est pas coupé
2. Certains navigateurs bloquent l'audio automatique
3. Interagir avec la page avant de tester

---

## 📊 Différences avec Firebase

### Système de Test (Actuel)
- ✅ Notifications natives du navigateur
- ✅ Fonctionne immédiatement
- ✅ Pas de configuration nécessaire
- ❌ Pas de notifications en arrière-plan
- ❌ Pas de synchronisation multi-device

### Firebase (Production)
- ✅ Notifications en arrière-plan
- ✅ Synchronisation multi-device
- ✅ Statistiques et analytics
- ✅ Notifications même si l'app est fermée
- ⚠️ Nécessite configuration

---

## 🎯 Prochaines Étapes

### Pour Passer à Firebase

1. **Configuration** (45 min)
   - Suivre `FIREBASE_SETUP_GUIDE.md`
   - Créer projet Firebase
   - Copier les clés

2. **Intégration** (30 min)
   - Suivre `FIREBASE_INTEGRATION_GUIDE.md`
   - Ajouter dans MessageController
   - Ajouter dans GoalController

3. **Tests** (15 min)
   - Tester avec 2 navigateurs
   - Vérifier les notifications réelles

---

## 💡 Astuces

### Test Rapide
```javascript
// Dans la console
testNotifications.testNewMessage();
testNotifications.testMention();
testNotifications.testNewMember();
```

### Vérifier le Statut
```javascript
// Dans la console
console.log(Notification.permission);
console.log(testNotifications.isSupported);
```

### Forcer la Demande de Permission
```javascript
// Dans la console
testNotifications.requestPermission();
```

---

## 🎉 Résultat Attendu

Après les tests, vous devriez:
- ✅ Voir les notifications apparaître
- ✅ Entendre les sons
- ✅ Voir les alertes visuelles
- ✅ Pouvoir cliquer sur les notifications
- ✅ Comprendre le fonctionnement du système

**Le système de test fonctionne!** 🚀

---

## 📚 Documentation Complète

- **FIREBASE_NOTIFICATIONS_PLAN.md** - Architecture
- **FIREBASE_SETUP_GUIDE.md** - Configuration Firebase
- **FIREBASE_INTEGRATION_GUIDE.md** - Intégration
- **FIREBASE_COMPLETE.md** - Vue d'ensemble
- **TEST_NOTIFICATIONS_GUIDE.md** - Ce guide

---

**Prêt à tester!** Ouvrez http://localhost:8000/test/notifications 🧪
