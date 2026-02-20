# Correction Erreur 404 - Uploads

## ❌ Erreur Affichée

```
No route found for "GET http://127.0.0.1:8000/public/uploads/messages/"
HTTP 404 Not Found
```

## 🔍 Cause du Problème

Vous essayez d'accéder à l'URL avec `/public/` dedans, mais Symfony ne doit PAS inclure `/public/` dans les URLs.

## ✅ Solution

### URLs Correctes vs Incorrectes

❌ **INCORRECT:**
```
http://127.0.0.1:8000/public/uploads/messages/
http://127.0.0.1:8000/public/uploads/messages/image.jpg
```

✅ **CORRECT:**
```
http://127.0.0.1:8000/uploads/messages/
http://127.0.0.1:8000/uploads/messages/image.jpg
```

## 📁 Structure des Dossiers

```
projet/
├── public/              ← Racine web (document root)
│   ├── index.php       ← Point d'entrée
│   ├── uploads/
│   │   └── messages/   ← Fichiers uploadés ici
│   │       └── image-abc123.jpg
│   └── ...
└── src/
```

## 🌐 Comment Symfony Gère les URLs

1. **Document Root:** Le serveur pointe vers `/public/`
2. **URL demandée:** `http://127.0.0.1:8000/uploads/messages/image.jpg`
3. **Fichier physique:** `public/uploads/messages/image.jpg`
4. **Résultat:** ✅ Fichier trouvé et servi

## 🧪 Tests de Vérification

### Test 1: Vérifier qu'un Fichier Existe

```bash
# Créer un fichier de test
echo "test" > public/uploads/messages/test.txt

# Vérifier qu'il existe
dir public\uploads\messages\test.txt
```

### Test 2: Accéder au Fichier via le Navigateur

✅ **URL correcte:**
```
http://127.0.0.1:8000/uploads/messages/test.txt
```

**Résultat attendu:** Le fichier s'affiche ou se télécharge

### Test 3: Vérifier avec une Image

```bash
# Si vous avez une image de test
copy "C:\chemin\vers\image.jpg" "public\uploads\messages\test-image.jpg"
```

✅ **URL correcte:**
```
http://127.0.0.1:8000/uploads/messages/test-image.jpg
```

**Résultat attendu:** L'image s'affiche dans le navigateur

## 🔧 Vérification de la Configuration

### 1. Vérifier le .htaccess

Le fichier `public/.htaccess` doit contenir:

```apache
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^ %{ENV:BASE}/index.php [L]
```

✅ Cette règle dit: "Si le fichier existe physiquement, le servir directement"

### 2. Vérifier la Configuration VichUploader

```yaml
# config/packages/vich_uploader.yaml
vich_uploader:
    mappings:
        message_images:
            uri_prefix: /uploads/messages  # ← Pas de /public/ !
            upload_destination: '%kernel.project_dir%/public/uploads/messages'
```

✅ `uri_prefix` ne contient PAS `/public/`

## 📝 Dans le Code

### Template Twig (Correct)

```twig
{# ✅ CORRECT #}
<img src="{{ message.attachmentPath }}" alt="Image">

{# Si attachmentPath = "/uploads/messages/image.jpg" #}
{# URL finale = http://127.0.0.1:8000/uploads/messages/image.jpg #}
```

### Entité Message (Correct)

```php
// ✅ CORRECT
$message->setAttachmentPath('/uploads/messages/image-abc123.jpg');

// ❌ INCORRECT
$message->setAttachmentPath('/public/uploads/messages/image-abc123.jpg');
```

## 🚀 Comment Tester Correctement

### Méthode 1: Via le Chatroom

1. Aller sur `http://127.0.0.1:8000/goals`
2. Cliquer sur "Chatroom"
3. Uploader une image
4. L'image devrait s'afficher automatiquement

### Méthode 2: Accès Direct

1. Uploader une image via le chatroom
2. Noter le nom du fichier (ex: `image-abc123.jpg`)
3. Accéder directement:
   ```
   http://127.0.0.1:8000/uploads/messages/image-abc123.jpg
   ```

### Méthode 3: Vérifier en Base de Données

```bash
php bin/console doctrine:query:sql "SELECT id, attachment_path FROM message WHERE attachment_path IS NOT NULL ORDER BY id DESC LIMIT 1"
```

**Résultat attendu:**
```
id | attachment_path
---+--------------------------------
15 | /uploads/messages/image-abc123.jpg
```

✅ Le chemin commence par `/uploads/` (pas `/public/uploads/`)

## 🐛 Dépannage

### Problème: Image ne s'affiche pas

**Vérifications:**

1. **Fichier existe?**
   ```bash
   dir public\uploads\messages\
   ```

2. **Chemin correct en BDD?**
   ```sql
   SELECT attachment_path FROM message WHERE id = X;
   ```
   Doit retourner: `/uploads/messages/...` (pas `/public/...`)

3. **Permissions?**
   ```bash
   icacls public\uploads\messages
   ```

4. **Serveur démarré?**
   ```bash
   symfony server:status
   ```

### Problème: 404 Not Found

**Causes possibles:**

1. ❌ URL contient `/public/`
   - **Solution:** Enlever `/public/` de l'URL

2. ❌ Fichier n'existe pas physiquement
   - **Solution:** Vérifier avec `dir public\uploads\messages\`

3. ❌ Mauvais chemin en BDD
   - **Solution:** Corriger le chemin (doit commencer par `/uploads/`)

## ✅ Checklist Finale

- [ ] URLs n'incluent PAS `/public/`
- [ ] Fichiers existent dans `public/uploads/messages/`
- [ ] Chemins en BDD commencent par `/uploads/messages/`
- [ ] Configuration VichUploader correcte
- [ ] `.htaccess` présent et correct
- [ ] Serveur Symfony démarré
- [ ] Permissions correctes sur le dossier

## 📸 Exemple Complet

### Upload d'une Image

1. **Fichier physique:**
   ```
   public/uploads/messages/image-5f8a9b2c3d1e.jpg
   ```

2. **Chemin en BDD:**
   ```
   /uploads/messages/image-5f8a9b2c3d1e.jpg
   ```

3. **URL dans le navigateur:**
   ```
   http://127.0.0.1:8000/uploads/messages/image-5f8a9b2c3d1e.jpg
   ```

4. **Dans le template:**
   ```twig
   <img src="/uploads/messages/image-5f8a9b2c3d1e.jpg">
   ```

✅ Tout est cohérent, pas de `/public/` dans les URLs!

---

**Résumé:** Ne JAMAIS inclure `/public/` dans les URLs. Le dossier `public/` est la racine web, donc `/uploads/` pointe déjà vers `public/uploads/`.
