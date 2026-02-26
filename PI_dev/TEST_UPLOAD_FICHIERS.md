# Test d'Upload de Fichiers et Images

## 🧪 Procédure de Test

### Étape 1: Vérifier les Prérequis

**Vérifier les dossiers:**
```bash
ls -la public/uploads/messages/
ls -la public/uploads/voice/
```

**Résultat attendu:**
- ✅ Les dossiers existent
- ✅ Permissions en écriture (777 ou 755)

**Si les dossiers n'existent pas:**
```bash
mkdir -p public/uploads/messages
mkdir -p public/uploads/voice
chmod 777 public/uploads/messages
chmod 777 public/uploads/voice
```

### Étape 2: Vérifier la Configuration PHP

**Créer un fichier `public/phpinfo.php`:**
```php
<?php
phpinfo();
?>
```

**Accéder à:** `http://localhost/phpinfo.php`

**Vérifier:**
- `file_uploads` = On
- `upload_max_filesize` = 10M (ou plus)
- `post_max_size` = 10M (ou plus)
- `max_file_uploads` = 20 (ou plus)

**Supprimer le fichier après:**
```bash
rm public/phpinfo.php
```

### Étape 3: Test d'Upload d'Image

**Actions:**
1. Aller sur `/goals`
2. Cliquer sur un goal pour accéder au chatroom
3. Cliquer sur le bouton 📎 (bleu)
4. Sélectionner une image (JPG, PNG, GIF, WEBP)
5. Observer la prévisualisation (miniature 48×48px)
6. (Optionnel) Taper un message
7. Cliquer sur ✈️ Envoyer

**Résultat attendu:**
- ✅ Prévisualisation s'affiche
- ✅ Bouton 📎 devient actif (fond bleu clair)
- ✅ Après envoi: image apparaît dans le chat
- ✅ Image cliquable pour agrandissement
- ✅ Fichier présent dans `public/uploads/messages/`

**Si ça ne fonctionne pas:**
- Regarder les logs: `var/log/dev.log`
- Regarder la console du navigateur (F12)
- Vérifier les permissions du dossier

### Étape 4: Test d'Upload de PDF

**Actions:**
1. Cliquer sur 📎
2. Sélectionner un fichier PDF
3. Observer l'icône PDF rouge dans la prévisualisation
4. Cliquer sur ✈️ Envoyer

**Résultat attendu:**
- ✅ Icône PDF rouge s'affiche
- ✅ Après envoi: fichier apparaît avec lien de téléchargement
- ✅ Cliquer sur le lien télécharge le PDF
- ✅ Fichier présent dans `public/uploads/messages/`

### Étape 5: Test d'Upload de Document Word

**Actions:**
1. Cliquer sur 📎
2. Sélectionner un fichier .doc ou .docx
3. Observer l'icône Word bleue
4. Cliquer sur ✈️ Envoyer

**Résultat attendu:**
- ✅ Icône Word bleue s'affiche
- ✅ Fichier apparaît avec lien de téléchargement
- ✅ Nom du fichier visible

### Étape 6: Test d'Upload de Fichier Excel

**Actions:**
1. Cliquer sur 📎
2. Sélectionner un fichier .xls ou .xlsx
3. Observer l'icône Excel verte
4. Cliquer sur ✈️ Envoyer

**Résultat attendu:**
- ✅ Icône Excel verte s'affiche
- ✅ Fichier apparaît avec lien de téléchargement

### Étape 7: Test Combiné (Texte + Image)

**Actions:**
1. Taper un message: "Voici une photo"
2. Cliquer sur 📎 et sélectionner une image
3. Observer: texte + prévisualisation
4. Cliquer sur ✈️ Envoyer

**Résultat attendu:**
- ✅ Message contient le texte ET l'image
- ✅ Affichage correct dans le chat

## 🔍 Diagnostic des Problèmes

### Problème 1: Le bouton 📎 ne fait rien

**Causes possibles:**
- JavaScript désactivé
- Erreur JavaScript dans la console

**Solutions:**
1. Ouvrir la console (F12)
2. Regarder les erreurs
3. Vider le cache (Ctrl+F5)
4. Tester dans un autre navigateur

### Problème 2: Prévisualisation ne s'affiche pas

**Causes possibles:**
- Fonction `handleFileSelect()` ne fonctionne pas
- Élément `filePreviewArea` manquant

**Solutions:**
1. Vérifier la console JavaScript
2. Vérifier que l'élément existe:
```javascript
console.log(document.getElementById('filePreviewArea'));
```

### Problème 3: Fichier ne s'envoie pas

**Causes possibles:**
- Formulaire sans `enctype="multipart/form-data"`
- Taille du fichier trop grande
- Permissions du dossier

**Solutions:**
1. Vérifier le formulaire:
```twig
{{ form_start(form, {'attr': {'enctype': 'multipart/form-data'}}) }}
```

2. Vérifier la taille du fichier (max 10MB)

3. Vérifier les permissions:
```bash
chmod 777 public/uploads/messages
```

4. Regarder les logs:
```bash
tail -f var/log/dev.log
```

### Problème 4: Fichier uploadé mais pas affiché

**Causes possibles:**
- Chemin incorrect dans la base de données
- Type MIME non reconnu

**Solutions:**
1. Vérifier dans la base de données:
```sql
SELECT id, content, attachment_path, attachment_type FROM message ORDER BY id DESC LIMIT 5;
```

2. Vérifier que `attachmentPath` commence par `/uploads/messages/`

3. Vérifier que `attachmentType` est correct (image, pdf, document, etc.)

### Problème 5: Erreur 500 lors de l'upload

**Causes possibles:**
- Erreur PHP
- Dossier non accessible
- Extension PHP manquante

**Solutions:**
1. Regarder les logs PHP:
```bash
tail -f var/log/dev.log
```

2. Vérifier les extensions PHP:
```bash
php -m | grep -i fileinfo
php -m | grep -i gd
```

3. Vérifier les permissions:
```bash
ls -la public/uploads/
```

## 📊 Checklist de Vérification

### Configuration
- [ ] Dossier `public/uploads/messages/` existe
- [ ] Permissions en écriture (777 ou 755)
- [ ] `file_uploads = On` dans php.ini
- [ ] `upload_max_filesize >= 10M`
- [ ] `post_max_size >= 10M`

### Formulaire
- [ ] Attribut `enctype="multipart/form-data"`
- [ ] Champ `attachment` de type `FileType`
- [ ] Attribut `accept` correct
- [ ] Fonction `handleFileSelect()` définie

### Contrôleur
- [ ] Méthode `chatroom()` gère l'upload
- [ ] Fichier déplacé vers `public/uploads/messages/`
- [ ] `attachmentPath` enregistré en base
- [ ] `attachmentType` déterminé correctement

### Affichage
- [ ] Images affichées avec `<img>`
- [ ] Fichiers affichés avec lien de téléchargement
- [ ] Icônes appropriées pour chaque type
- [ ] Prévisualisation fonctionne

## 🛠️ Commandes Utiles

**Vider le cache:**
```bash
php bin/console cache:clear
```

**Voir les logs en temps réel:**
```bash
tail -f var/log/dev.log
```

**Vérifier les fichiers uploadés:**
```bash
ls -lah public/uploads/messages/
```

**Compter les fichiers:**
```bash
ls public/uploads/messages/ | wc -l
```

**Voir les derniers fichiers:**
```bash
ls -lt public/uploads/messages/ | head -10
```

**Vérifier les permissions:**
```bash
stat public/uploads/messages/
```

**Créer un fichier de test:**
```bash
echo "Test file" > public/uploads/messages/test.txt
```

## 📝 Logs à Vérifier

Quand vous uploadez un fichier, vous devriez voir dans les logs:
```
Form submitted. Has attachment: YES
File name: example.jpg
File size: 123456
File type: image/jpeg
```

Si vous voyez:
```
Form submitted. Has attachment: NO
```
Alors le fichier n'est pas reçu par le serveur.

## ✅ Test Réussi

Si tout fonctionne, vous devriez pouvoir:
1. ✅ Sélectionner un fichier
2. ✅ Voir la prévisualisation
3. ✅ Envoyer le message
4. ✅ Voir le fichier dans le chat
5. ✅ Cliquer pour agrandir (images)
6. ✅ Télécharger (documents)
7. ✅ Fichier présent dans `public/uploads/messages/`

## 🚀 Si Tout Fonctionne Déjà

D'après les fichiers présents dans `public/uploads/messages/`, l'upload fonctionne déjà!

Fichiers trouvés:
- `c-699aea619a269999065640.png` (12.6 KB)
- `c-699aee3c25266538898169.png` (12.6 KB)

Cela signifie que:
- ✅ L'upload fonctionne
- ✅ Les fichiers sont enregistrés
- ✅ Le système est opérationnel

Si vous ne voyez pas les images dans le chat, le problème est dans l'affichage, pas dans l'upload.

## 🔧 Correction de l'Affichage

Si l'upload fonctionne mais l'affichage ne marche pas:

1. **Vérifier la base de données:**
```sql
SELECT id, content, attachment_path, attachment_type 
FROM message 
WHERE attachment_path IS NOT NULL 
ORDER BY id DESC 
LIMIT 5;
```

2. **Vérifier le template:**
- Le code `{% if message.attachmentType == 'image' %}` doit être présent
- Le chemin `{{ message.attachmentPath }}` doit être correct

3. **Vérifier l'entité Message:**
- Propriété `attachmentPath` existe
- Propriété `attachmentType` existe
- Getters fonctionnent

## 📞 Support

Si le problème persiste:
1. Copier les logs de `var/log/dev.log`
2. Copier les erreurs de la console JavaScript (F12)
3. Vérifier la requête réseau dans l'onglet Network (F12)
4. Prendre une capture d'écran du problème
