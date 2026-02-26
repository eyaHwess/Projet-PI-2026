# 🔔 Firebase Notifications - Guide de Configuration

## ✅ Statut: IMPLÉMENTÉ

Le système de notifications Firebase est maintenant prêt à être configuré!

---

## 📋 Fichiers Créés

### Backend
1. `src/Entity/FcmToken.php` - Entité pour stocker les tokens
2. `src/Repository/FcmTokenRepository.php` - Repository
3. `src/Controller/FcmTokenController.php` - API tokens
4. `src/Service/FirebaseNotificationService.php` - Service notifications
5. `src/Service/MentionDetector.php` - Détecteur de mentions
6. `migrations/Version20260222210340.php` - Migration BDD

### Frontend
7. `public/firebase-config.js` - Configuration Firebase
8. `public/firebase-notifications.js` - Manager notifications
9. `public/firebase-messaging-sw.js` - Service Worker

### Configuration
10. `config/services.yaml` - Services Symfony
11. `.env` - Variables d'environnement

---

## 🚀 Étapes de Configuration

### Étape 1: Créer un Projet Firebase (10 min)

1. **Aller sur** https://console.firebase.google.com/
2. **Cliquer** sur "Ajouter un projet"
3. **Nom du projet**: "PI-Coaching" (ou votre choix)
4. **Activer** Google Analytics (optionnel)
5. **Créer** le projet

### Étape 2: Activer Cloud Messaging (5 min)

1. Dans votre projet Firebase
2. **Aller** dans "Project Settings" (⚙️)
3. **Onglet** "Cloud Messaging"
4. **Copier** la "Server key" (Legacy)
5. **Générer** une paire de clés Web Push (VAPID)

### Étape 3: Ajouter une Application Web (5 min)

1. Dans "Project Settings"
2. **Cliquer** sur l'icône Web (</>)
3. **Nom de l'app**: "PI-Coaching Web"
4. **Copier** la configuration Firebase

Vous obtiendrez quelque chose comme:
```javascript
const firebaseConfig = {
    apiKey: "AIzaSyXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX",
    authDomain: "pi-coaching.firebaseapp.com",
    projectId: "pi-coaching",
    storageBucket: "pi-coaching.appspot.com",
    messagingSenderId: "123456789012",
    appId: "1:123456789012:web:xxxxxxxxxxxxx"
};
```

### Étape 4: Configurer le Backend (5 min)

1. **Ouvrir** `.env`
2. **Remplacer** `YOUR_FIREBASE_SERVER_KEY_HERE` par votre Server Key
3. **Sauvegarder**

```env
FIREBASE_SERVER_KEY=AAAA...votre_clé_ici
```

### Étape 5: Configurer le Frontend (10 min)

#### 5.1 Fichier firebase-config.js
1. **Ouvrir** `public/firebase-config.js`
2. **Remplacer** les valeurs par votre configuration
3. **Ajouter** votre VAPID key

#### 5.2 Fichier firebase-messaging-sw.js
1. **Ouvrir** `public/firebase-messaging-sw.js`
2. **Remplacer** la configuration Firebase (lignes 10-17)

### Étape 6: Intégrer dans le Template (10 min)

**Ouvrir** `templates/base.html.twig`

**Ajouter** avant `</head>`:
```twig
{# Firebase SDK #}
<script src="https://www.gstatic.com/firebasejs/10.7.1/firebase-app-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/10.7.1/firebase-messaging-compat.js"></script>

{# Firebase Configuration #}
<script src="{{ asset('firebase-config.js') }}"></script>
```

**Ajouter** avant `</body>`:
```twig
{# Firebase Notifications #}
<script src="{{ asset('firebase-notifications.js') }}"></script>
```

---

## 🧪 Test Rapide

### 1. Vérifier l'Installation
```bash
# Vider le cache
php bin/console cache:clear

# Vérifier les routes
php bin/console debug:router | grep fcm
```

### 2. Tester dans le Navigateur
1. Ouvrir l'application
2. Ouvrir la console (F12)
3. Chercher: "🔔 Initialisation Firebase Notifications..."
4. Accepter les notifications quand demandé

### 3. Vérifier le Token
```javascript
// Dans la console
console.log(window.firebaseNotifications.currentToken);
```

---

## 📝 Prochaines Étapes

Voir `FIREBASE_INTEGRATION_GUIDE.md` pour:
- Intégrer dans MessageController
- Intégrer dans GoalController
- Tester les notifications

---

**Temps total**: ~45 minutes  
**Difficulté**: ⭐⭐ (Moyenne)
