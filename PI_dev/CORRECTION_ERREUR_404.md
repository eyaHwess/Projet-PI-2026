# 🔧 CORRECTION ERREUR 404 - TRADUCTION

## ❌ PROBLÈME IDENTIFIÉ

**Erreur:** Réponse non-JSON reçue, Status: 404

**Cause:** Le serveur Symfony n'est pas démarré ou la route n'est pas accessible.

---

## ✅ SOLUTIONS

### Solution 1 : Démarrer le Serveur Symfony

```bash
# Option 1 : Avec Symfony CLI (recommandé)
symfony server:start

# Option 2 : Avec PHP built-in server
php -S localhost:8000 -t public

# Option 3 : Avec un port différent
php -S localhost:8080 -t public
```

**Vérifier que le serveur fonctionne :**
- Ouvrir : `http://localhost:8000`
- Doit afficher la page d'accueil de l'application

---

### Solution 2 : Vérifier l'URL de Base

Si vous utilisez un port différent de 8000, modifiez les URLs dans les fichiers de test :

**Fichier : `public/test_traduction_direct.html`**

Remplacer :
```javascript
fetch('/message/${messageId}/translate', ...)
```

Par :
```javascript
fetch('http://localhost:8000/message/${messageId}/translate', ...)
```

---

### Solution 3 : Tester Directement dans le Chatroom

Au lieu d'utiliser la page de test, testez directement dans l'interface :

1. **Démarrer le serveur :**
   ```bash
   symfony server:start
   ```

2. **Se connecter :**
   - Aller sur : `http://localhost:8000/login`
   - Se connecter avec vos identifiants

3. **Aller dans un chatroom :**
   - Aller sur : `http://localhost:8000/goals`
   - Cliquer sur un goal
   - Aller dans le chatroom

4. **Envoyer un message :**
   - Taper : "hello"
   - Envoyer

5. **Traduire le message :**
   - Cliquer sur le bouton "Traduire"
   - Sélectionner "🇫🇷 Français"
   - Vérifier que "bonjour" s'affiche

---

### Solution 4 : Vérifier la Configuration Apache/Nginx

Si vous utilisez Apache ou Nginx au lieu du serveur PHP :

**Pour Apache :**
Vérifier que le fichier `.htaccess` existe dans `public/` :

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^(.*)$ index.php [QSA,L]
</IfModule>
```

**Pour Nginx :**
Vérifier la configuration :

```nginx
location / {
    try_files $uri /index.php$is_args$args;
}
```

---

## 🧪 TEST RAPIDE

### Test 1 : Vérifier que le serveur fonctionne

```bash
# Démarrer le serveur
symfony server:start

# Dans un autre terminal, tester
curl http://localhost:8000
```

**Résultat attendu :** HTML de la page d'accueil

---

### Test 2 : Tester la route de traduction

```bash
# Remplacer 1 par un ID de message existant
curl -X POST http://localhost:8000/message/1/translate \
     -H "Content-Type: application/x-www-form-urlencoded" \
     -H "X-Requested-With: XMLHttpRequest" \
     -d "lang=fr"
```

**Résultat attendu :** JSON avec la traduction

**Si erreur 404 :** Le message avec l'ID 1 n'existe pas
**Si erreur 401 :** Vous devez être connecté

---

### Test 3 : Utiliser la console du navigateur

1. **Ouvrir un chatroom :**
   - `http://localhost:8000/message/chatroom/1`

2. **Ouvrir la console (F12)**

3. **Taper :**
   ```javascript
   // Vérifier que les fonctions sont chargées
   console.log(typeof window.translateMessage);
   // Doit afficher "function"
   
   // Trouver un ID de message
   const messages = document.querySelectorAll('[data-message-id]');
   console.log('Messages trouvés:', messages.length);
   
   // Tester avec le premier message
   if (messages.length > 0) {
       const messageId = messages[0].getAttribute('data-message-id');
       console.log('Test avec message ID:', messageId);
       translateMessage(messageId, 'fr');
   }
   ```

---

## 📋 CHECKLIST DE DÉBOGAGE

### ✅ Étape 1 : Serveur
- [ ] Le serveur Symfony est démarré
- [ ] `http://localhost:8000` affiche la page d'accueil
- [ ] Aucune erreur dans le terminal du serveur

### ✅ Étape 2 : Connexion
- [ ] Je suis connecté à l'application
- [ ] Je peux accéder à `/goals`
- [ ] Je peux voir mes chatrooms

### ✅ Étape 3 : Message
- [ ] J'ai créé un message dans un chatroom
- [ ] Le message contient du texte (ex: "hello")
- [ ] Je connais l'ID du message

### ✅ Étape 4 : Fichiers
- [ ] Le fichier `public/js/translation.js` existe
- [ ] Le fichier est accessible : `http://localhost:8000/js/translation.js`
- [ ] Pas d'erreur 404 dans la console (F12 > Network)

### ✅ Étape 5 : Routes
- [ ] La route `message_translate` existe
- [ ] Commande : `php bin/console debug:router | grep translate`
- [ ] Résultat : `message_translate POST /message/{id}/translate`

---

## 🚀 PROCÉDURE COMPLÈTE

### Étape 1 : Démarrer le Serveur
```bash
symfony server:start
```

**Vérifier :**
```bash
curl http://localhost:8000
```

### Étape 2 : Se Connecter
- Ouvrir : `http://localhost:8000/login`
- Se connecter

### Étape 3 : Créer un Message
- Aller dans un chatroom
- Envoyer "hello"
- Noter l'ID du message (visible dans l'URL ou la console)

### Étape 4 : Tester la Traduction

**Option A : Interface Utilisateur**
- Cliquer sur "Traduire" sous le message
- Sélectionner "🇫🇷 Français"
- Vérifier que "bonjour" s'affiche

**Option B : Console du Navigateur**
```javascript
// F12 > Console
translateMessage(MESSAGE_ID, 'fr');
```

**Option C : Commande Symfony**
```bash
php bin/console app:test-translation hello fr
```

---

## 🔍 DIAGNOSTIC AVANCÉ

### Si l'erreur 404 persiste :

1. **Vérifier les routes :**
   ```bash
   php bin/console debug:router message_translate
   ```

2. **Vérifier le contrôleur :**
   ```bash
   cat src/Controller/MessageController.php | grep -A 10 "translate"
   ```

3. **Nettoyer le cache :**
   ```bash
   php bin/console cache:clear
   rm -rf var/cache/*
   ```

4. **Vérifier les logs :**
   ```bash
   tail -f var/log/dev.log
   ```

---

## 💡 SOLUTION RAPIDE

**Si vous voulez juste tester que la traduction fonctionne :**

```bash
# 1. Démarrer le serveur
symfony server:start

# 2. Tester directement avec la commande
php bin/console app:test-translation hello fr

# Résultat attendu :
# ✅ Traduction réussie!
# Texte original: hello
# Traduction: bonjour
# Langue cible: fr
```

**Si cette commande fonctionne, le service de traduction est OK !**

Le problème vient alors de :
- L'authentification (vous devez être connecté)
- L'ID du message (le message n'existe pas)
- Le serveur web (pas démarré ou mauvais port)

---

## ✅ RÉSUMÉ

**Erreur 404 = Le serveur ne trouve pas la route**

**Causes possibles :**
1. ❌ Serveur pas démarré → `symfony server:start`
2. ❌ Mauvais port → Vérifier `localhost:8000`
3. ❌ Message inexistant → Créer un message d'abord
4. ❌ Pas connecté → Se connecter avant de tester
5. ❌ Cache corrompu → `php bin/console cache:clear`

**Solution la plus simple :**
1. Démarrer le serveur : `symfony server:start`
2. Aller dans l'interface : `http://localhost:8000`
3. Se connecter
4. Aller dans un chatroom
5. Envoyer "hello"
6. Cliquer sur "Traduire" → "🇫🇷 Français"
7. Voir "bonjour" s'afficher

**C'est tout ! 🎉**