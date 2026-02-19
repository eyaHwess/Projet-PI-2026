# Vérification Rapide - VichUploaderBundle

## ✅ Commandes de Vérification (5 minutes)

### 1. Vérifier l'Installation
```bash
composer show vich/uploader-bundle
```
✅ **Résultat:** Version v2.9.1 installée

---

### 2. Vérifier la Configuration
```bash
php bin/console debug:config vich_uploader
```
✅ **Résultat:** Configuration chargée correctement
- db_driver: orm
- mapping: message_images
- upload_destination: public/uploads/messages
- delete_on_remove: true

---

### 3. Vérifier le Schéma BDD
```bash
php bin/console doctrine:schema:validate
```
✅ **Résultat:** Mapping et Database OK

---

### 4. Vérifier le Dossier d'Upload
```bash
dir public\uploads\messages
```
✅ **Résultat:** Dossier existe et accessible

---

### 5. Vérifier les Services
```bash
php bin/console debug:container vich_uploader.upload_handler
```

---

## 🧪 Test Rapide dans le Navigateur

### Étape 1: Démarrer le Serveur
```bash
symfony server:start
```

### Étape 2: Ouvrir le Chatroom
1. Aller sur: http://127.0.0.1:8000/goals
2. Cliquer sur "Chatroom" d'un goal
3. Vérifier que le formulaire s'affiche

### Étape 3: Tester l'Upload
1. Sélectionner une image (JPG, PNG)
2. Cliquer "Envoyer"
3. Vérifier que l'image apparaît dans le chat

### Étape 4: Vérifier le Fichier
```bash
dir public\uploads\messages
```
✅ **Résultat:** Nouveau fichier avec nom unique

### Étape 5: Vérifier en BDD
```bash
php bin/console doctrine:query:sql "SELECT id, image_name, image_size FROM message WHERE image_name IS NOT NULL ORDER BY id DESC LIMIT 1"
```

---

## ✅ Statut Actuel

- ✅ Bundle installé (v2.9.1)
- ✅ Configuration OK
- ✅ Schéma BDD synchronisé
- ✅ Dossier uploads créé
- ✅ Services disponibles

## 🎯 Prêt pour les Tests!

Tout est configuré correctement. Vous pouvez maintenant:
1. Uploader des images dans les messages
2. Les fichiers seront automatiquement gérés
3. Suppression automatique lors de la suppression du message

---

**Temps de vérification:** 5 minutes ⏱️
