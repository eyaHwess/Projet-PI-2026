# Guide de Vérification - VichUploaderBundle

## 🎯 Objectif

Vérifier que VichUploaderBundle est correctement installé et fonctionne pour les uploads de fichiers dans les messages.

## ✅ Checklist d'Installation

### 1. Vérifier l'Installation du Bundle

```bash
composer show vich/uploader-bundle
```

**Résultat attendu:**
```
name     : vich/uploader-bundle
descrip. : A simple Symfony bundle to ease file uploads with ORM entities and ODM documents
versions : * 2.9.x
```

---

### 2. Vérifier l'Enregistrement du Bundle

**Fichier:** `config/bundles.php`

```bash
cat config/bundles.php | findstr Vich
```

**Résultat attendu:**
```php
Vich\UploaderBundle\VichUploaderBundle::class => ['all' => true],
```

---

### 3. Vérifier la Configuration

**Fichier:** `config/packages/vich_uploader.yaml`

```bash
type config\packages\vich_uploader.yaml
```

**Résultat attendu:**
```yaml
vich_uploader:
    db_driver: orm
    mappings:
        message_images:
            uri_prefix: /uploads/messages
            upload_destination: '%kernel.project_dir%/public/uploads/messages'
            namer: Vich\UploaderBundle\Naming\SmartUniqueNamer
            inject_on_load: false
            delete_on_update: true
            delete_on_remove: true
```

---

### 4. Vérifier la Migration

```bash
php bin/console doctrine:migrations:status
```

**Résultat attendu:**
- Migration `Version20260218214432` dans la liste "Executed"

**Vérifier les colonnes en base:**
```bash
php bin/console doctrine:schema:validate
```

---

### 5. Vérifier l'Entité Message

**Fichier:** `src/Entity/Message.php`

```bash
php bin/console debug:container --parameters | findstr message
```

**Vérifier manuellement:**
- [ ] Annotation `#[Vich\Uploadable]` sur la classe
- [ ] Propriété `$imageFile` avec `#[Vich\UploadableField]`
- [ ] Propriétés `$imageName`, `$imageSize`, `$updatedAt`
- [ ] Méthodes getter/setter pour ces propriétés

---

### 6. Vérifier le Formulaire

**Fichier:** `src/Form/MessageType.php`

```bash
findstr /C:"VichImageType" src\Form\MessageType.php
```

**Résultat attendu:**
```
use Vich\UploaderBundle\Form\Type\VichImageType;
->add('imageFile', VichImageType::class, [
```

---

### 7. Vérifier le Dossier d'Upload

```bash
dir public\uploads\messages
```

**Si le dossier n'existe pas, le créer:**
```bash
mkdir public\uploads\messages
```

---

## 🧪 Tests Fonctionnels

### Test 1: Vérifier que le Serveur Démarre

```bash
symfony server:start
```

**Résultat attendu:**
- Aucune erreur de configuration
- Serveur démarre sur http://127.0.0.1:8000

---

### Test 2: Vérifier le Formulaire dans le Chatroom

1. Ouvrir le navigateur: `http://127.0.0.1:8000/goals`
2. Cliquer sur "Chatroom" d'un goal
3. Inspecter le formulaire (F12)

**Vérifier dans le HTML:**
```html
<input type="file" id="message_imageFile" name="message[imageFile]" accept="image/*">
```

**Screenshot recommandé:** Formulaire avec champ image

---

### Test 3: Upload d'une Image Simple

**Étapes:**
1. Aller dans un chatroom
2. Sélectionner une image (JPG, PNG, GIF)
3. Cliquer "Envoyer"

**Vérifications:**

#### A. Vérifier en Base de Données
```sql
SELECT id, content, image_name, image_size, updated_at 
FROM message 
ORDER BY id DESC 
LIMIT 5;
```

**Résultat attendu:**
```
id | content | image_name              | image_size | updated_at
---+---------+-------------------------+------------+-------------------
15 | NULL    | image-abc123def456.jpg  | 245678     | 2026-02-18 21:45:00
```

#### B. Vérifier le Fichier sur le Disque
```bash
dir public\uploads\messages
```

**Résultat attendu:**
- Fichier avec nom unique (ex: `image-abc123def456.jpg`)
- Taille correspondant à `image_size` en BDD

#### C. Vérifier l'Affichage
- [ ] Image visible dans le chatroom
- [ ] Nom du fichier affiché
- [ ] Taille du fichier affichée (si implémenté)

---

### Test 4: Vérifier la Suppression Automatique

**Étapes:**
1. Noter le nom du fichier uploadé (ex: `image-abc123.jpg`)
2. Supprimer le message contenant l'image
3. Vérifier que le fichier est supprimé

**Commandes:**
```bash
# Avant suppression
dir public\uploads\messages\image-abc123.jpg

# Supprimer le message via l'interface

# Après suppression
dir public\uploads\messages\image-abc123.jpg
```

**Résultat attendu:**
```
Le fichier spécifié est introuvable.
```

---

### Test 5: Vérifier la Mise à Jour

**Étapes:**
1. Envoyer un message avec image A
2. Noter le nom du fichier (ex: `image-old.jpg`)
3. Modifier le message et uploader image B
4. Vérifier que `image-old.jpg` est supprimé
5. Vérifier que le nouveau fichier existe

---

### Test 6: Vérifier les Validations

#### A. Fichier Trop Grand
1. Essayer d'uploader un fichier > 10MB
2. **Résultat attendu:** Message d'erreur

#### B. Type de Fichier Invalide
1. Essayer d'uploader un fichier .exe ou .zip
2. **Résultat attendu:** Message d'erreur

#### C. Image Valide
1. Uploader JPG, PNG, GIF, WebP
2. **Résultat attendu:** Upload réussi

---

## 🔍 Vérifications Avancées

### Vérifier les Logs

```bash
tail -f var/log/dev.log
```

**Rechercher:**
- Erreurs VichUploader
- Erreurs d'upload
- Erreurs de permissions

---

### Vérifier les Permissions

**Windows:**
```bash
icacls public\uploads\messages
```

**Résultat attendu:**
- Permissions d'écriture pour l'utilisateur web

---

### Vérifier la Configuration Doctrine

```bash
php bin/console debug:config vich_uploader
```

**Résultat attendu:**
```yaml
vich_uploader:
    db_driver: orm
    mappings:
        message_images:
            uri_prefix: /uploads/messages
            upload_destination: 'C:\...\public\uploads\messages'
            namer: Vich\UploaderBundle\Naming\SmartUniqueNamer
            inject_on_load: false
            delete_on_update: true
            delete_on_remove: true
```

---

### Vérifier les Services

```bash
php bin/console debug:container vich
```

**Résultat attendu:**
- Liste des services VichUploader disponibles
- `vich_uploader.upload_handler`
- `vich_uploader.storage.file_system`

---

## 🐛 Dépannage

### Problème 1: "No extension able to load configuration"

**Solution:**
```bash
# Vérifier que le bundle est enregistré
cat config/bundles.php | findstr Vich

# Si absent, ajouter:
# Vich\UploaderBundle\VichUploaderBundle::class => ['all' => true],
```

---

### Problème 2: Fichier Non Uploadé

**Vérifications:**
1. Dossier existe: `public/uploads/messages`
2. Permissions d'écriture
3. Taille du fichier < 10MB
4. Type MIME valide

**Logs à vérifier:**
```bash
tail -f var/log/dev.log
```

---

### Problème 3: Fichier Non Supprimé

**Vérifications:**
1. Configuration `delete_on_remove: true`
2. Permissions de suppression sur le dossier
3. Vérifier les logs

---

### Problème 4: Image Non Affichée

**Vérifications:**
1. Chemin correct: `/uploads/messages/filename.jpg`
2. Fichier existe physiquement
3. Permissions de lecture
4. Vérifier le HTML généré (F12)

---

## 📊 Checklist Finale

### Installation
- [ ] Bundle installé (`composer show vich/uploader-bundle`)
- [ ] Bundle enregistré (`config/bundles.php`)
- [ ] Configuration créée (`config/packages/vich_uploader.yaml`)
- [ ] Migration exécutée
- [ ] Colonnes en BDD (`image_name`, `image_size`, `updated_at`)

### Code
- [ ] Entité Message avec annotations Vich
- [ ] Propriétés `imageFile`, `imageName`, `imageSize`, `updatedAt`
- [ ] Getters/Setters implémentés
- [ ] Formulaire avec `VichImageType`

### Fonctionnel
- [ ] Upload d'image fonctionne
- [ ] Fichier créé dans `public/uploads/messages`
- [ ] Nom unique généré
- [ ] Taille enregistrée en BDD
- [ ] Image affichée dans le chatroom
- [ ] Suppression automatique fonctionne
- [ ] Mise à jour fonctionne
- [ ] Validations fonctionnent

### Performance
- [ ] Pas d'erreurs dans les logs
- [ ] Upload rapide (< 2 secondes)
- [ ] Pas de fichiers orphelins

---

## 📸 Screenshots Recommandés

1. **Formulaire** - Champ imageFile visible
2. **Upload réussi** - Image affichée dans le chat
3. **Base de données** - Colonnes remplies
4. **Dossier uploads** - Fichiers avec noms uniques
5. **Suppression** - Fichier disparu après suppression

---

## ✅ Validation Finale

**Commande de validation complète:**
```bash
# 1. Vérifier l'installation
composer show vich/uploader-bundle

# 2. Vérifier la configuration
php bin/console debug:config vich_uploader

# 3. Vérifier le schéma
php bin/console doctrine:schema:validate

# 4. Vérifier les services
php bin/console debug:container vich_uploader.upload_handler

# 5. Lister les fichiers uploadés
dir public\uploads\messages

# 6. Vérifier les derniers messages en BDD
php bin/console doctrine:query:sql "SELECT id, image_name, image_size FROM message WHERE image_name IS NOT NULL ORDER BY id DESC LIMIT 5"
```

---

## 🎉 Succès!

Si tous les tests passent:
- ✅ VichUploaderBundle est correctement installé
- ✅ Les uploads fonctionnent
- ✅ La suppression automatique fonctionne
- ✅ Le système est prêt pour la production

**Temps de vérification estimé:** 15-20 minutes

---

**Bon test! 🚀**
