# Test d'Accès aux Fichiers Uploadés

## ✅ Fichier de Test Créé

Un fichier de test a été créé: `public/uploads/messages/test.txt`

## 🧪 Comment Tester

### 1. Démarrer le Serveur

```bash
symfony server:start
```

### 2. Tester l'Accès au Fichier

Ouvrir dans le navigateur:

✅ **URL CORRECTE:**
```
http://127.0.0.1:8000/uploads/messages/test.txt
```

**Résultat attendu:** Le fichier s'affiche avec le texte "Test file"

❌ **URL INCORRECTE (NE PAS UTILISER):**
```
http://127.0.0.1:8000/public/uploads/messages/test.txt
```

**Résultat:** Erreur 404 Not Found

## 📝 Règle Importante

### ⚠️ JAMAIS de `/public/` dans les URLs!

Le dossier `public/` est la racine web. Donc:

- **Fichier physique:** `public/uploads/messages/test.txt`
- **URL d'accès:** `http://127.0.0.1:8000/uploads/messages/test.txt`

## 🎯 Test avec une Vraie Image

### Étape 1: Copier une Image de Test

```bash
# Remplacer par le chemin de votre image
copy "C:\chemin\vers\votre\image.jpg" "public\uploads\messages\test-image.jpg"
```

### Étape 2: Accéder à l'Image

```
http://127.0.0.1:8000/uploads/messages/test-image.jpg
```

**Résultat attendu:** L'image s'affiche dans le navigateur

## 🔍 Vérification Complète

### Commandes de Vérification

```bash
# 1. Vérifier que le fichier existe
dir public\uploads\messages\test.txt

# 2. Vérifier le contenu
type public\uploads\messages\test.txt

# 3. Démarrer le serveur
symfony server:start

# 4. Tester dans le navigateur
# http://127.0.0.1:8000/uploads/messages/test.txt
```

## ✅ Si Tout Fonctionne

Vous devriez voir:
- Le fichier test.txt s'affiche dans le navigateur
- Pas d'erreur 404
- Le contenu "Test file" est visible

## 🎉 Prêt pour les Uploads!

Si le test fonctionne, alors:
- ✅ La configuration est correcte
- ✅ Les fichiers uploadés seront accessibles
- ✅ VichUploader fonctionnera correctement

## 🚀 Prochaine Étape

Tester l'upload d'une vraie image via le chatroom:

1. Aller sur `http://127.0.0.1:8000/goals`
2. Cliquer sur "Chatroom"
3. Uploader une image
4. Vérifier qu'elle s'affiche correctement

---

**Note:** Si vous voyez une erreur 404, vérifiez que vous n'avez PAS `/public/` dans l'URL!
