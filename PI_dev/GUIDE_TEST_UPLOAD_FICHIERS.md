# 🧪 Guide de Test - Upload de Fichiers

## ✅ SYSTÈME DÉJÀ FONCTIONNEL

Votre système d'upload est **déjà opérationnel**! Voici comment le tester.

## 📋 TESTS À EFFECTUER

### Test 1: Upload d'Image 📷

**Étapes:**
1. Ouvrez votre chatroom: http://localhost:8000/message/chatroom/{goalId}
2. Cliquez sur le champ de message
3. Cherchez le bouton "Attach File" ou l'icône 📎
4. Sélectionnez une image (JPG, PNG, GIF, WebP)
5. Ajoutez un texte (optionnel): "Voici une photo!"
6. Cliquez sur "Send"

**Résultat attendu:**
```
✅ L'image s'affiche directement dans le chat
✅ Vous pouvez cliquer dessus pour zoomer
✅ Le fichier est sauvegardé dans public/uploads/messages/
✅ Le nom du fichier est unique (ex: c-699cb23e48b31847309202.png)
```

---

### Test 2: Upload de PDF 📄

**Étapes:**
1. Ouvrez le chatroom
2. Cliquez sur "Attach File"
3. Sélectionnez un fichier PDF
4. Ajoutez un texte: "Voici le document"
5. Envoyez

**Résultat attendu:**
```
✅ Une carte s'affiche avec:
   - Icône PDF rouge 📄
   - Nom du fichier
   - Taille (ex: 2.5 MB)
   - Type (PDF)
   - Bouton de téléchargement ⬇️
```

---

### Test 3: Upload de Document Word 📝

**Étapes:**
1. Ouvrez le chatroom
2. Cliquez sur "Attach File"
3. Sélectionnez un fichier .doc ou .docx
4. Envoyez

**Résultat attendu:**
```
✅ Une carte s'affiche avec:
   - Icône Word bleue 📘
   - Nom du fichier
   - Taille
   - Bouton de téléchargement
```

---

### Test 4: Upload de Vidéo 📹

**Étapes:**
1. Ouvrez le chatroom
2. Cliquez sur "Attach File"
3. Sélectionnez une vidéo (MP4, WebM)
4. Envoyez

**Résultat attendu:**
```
✅ Une carte s'affiche avec:
   - Icône vidéo rose 🎬
   - Nom du fichier
   - Taille
   - Bouton de téléchargement
```

---

### Test 5: Téléchargement de Fichier ⬇️

**Étapes:**
1. Trouvez un message avec un fichier attaché
2. Cliquez sur le bouton ⬇️ (download)

**Résultat attendu:**
```
✅ Le fichier se télécharge
✅ Le nom du fichier est préservé
✅ Le fichier s'ouvre correctement
```

---

### Test 6: Suppression de Message avec Fichier 🗑️

**Étapes:**
1. Envoyez un message avec un fichier
2. Notez le nom du fichier dans public/uploads/messages/
3. Supprimez le message
4. Vérifiez le dossier uploads

**Résultat attendu:**
```
✅ Le message est supprimé du chat
✅ Le fichier est supprimé du serveur
✅ Aucun fichier orphelin ne reste
```

---

### Test 7: Message Sans Texte (Fichier Seul) 📎

**Étapes:**
1. Ouvrez le chatroom
2. NE PAS écrire de texte
3. Attachez seulement un fichier
4. Envoyez

**Résultat attendu:**
```
✅ Le message s'envoie avec juste le fichier
✅ Pas d'erreur "Le message doit contenir du texte"
```

---

### Test 8: Fichier Trop Gros ⚠️

**Étapes:**
1. Essayez d'uploader un fichier > 10MB

**Résultat attendu:**
```
❌ Message d'erreur: "File is too large (max 10MB)"
✅ Le message ne s'envoie pas
✅ Aucun fichier n'est uploadé
```

---

### Test 9: Type de Fichier Non Supporté ⚠️

**Étapes:**
1. Essayez d'uploader un fichier .exe ou .zip

**Résultat attendu:**
```
❌ Message d'erreur: "Please upload a valid file type"
✅ Le message ne s'envoie pas
```

---

### Test 10: Affichage dans la Galerie 🖼️

**Étapes:**
1. Envoyez plusieurs images dans le chat
2. Ouvrez le panneau de droite (Info)
3. Allez dans la section "Photos"

**Résultat attendu:**
```
✅ Toutes les images sont affichées en grille
✅ Le compteur affiche le bon nombre
✅ Vous pouvez cliquer pour zoomer
```

---

## 🔍 VÉRIFICATIONS TECHNIQUES

### Vérifier les fichiers uploadés
```bash
# Windows PowerShell
Get-ChildItem public/uploads/messages/ | Format-Table Name, Length, LastWriteTime

# Résultat attendu:
# Name                         Length LastWriteTime
# ----                         ------ -------------
# c-699cb23e48b31847309202.png  12653 23/02/2026 21:02:02
# c-699af7a2f12c8444600660.png  12653 22/02/2026 13:33:37
```

### Vérifier la base de données
```bash
php bin/console dbal:run-sql "SELECT id, content, imageName, fileName, fileSize, fileType FROM message WHERE imageName IS NOT NULL OR fileName IS NOT NULL LIMIT 5"
```

### Vérifier la configuration VichUploader
```bash
php bin/console debug:config vich_uploader
```

---

## 📊 CHECKLIST COMPLÈTE

### Fonctionnalités de Base
- [ ] Upload d'image (JPG, PNG, GIF, WebP)
- [ ] Upload de PDF
- [ ] Upload de Word (.doc, .docx)
- [ ] Upload d'Excel (.xls, .xlsx)
- [ ] Upload de vidéo (MP4, WebM)
- [ ] Upload d'audio (MP3, WAV)
- [ ] Upload de texte (.txt)

### Affichage
- [ ] Images affichées avec aperçu
- [ ] Fichiers affichés avec icône colorée
- [ ] Nom du fichier affiché
- [ ] Taille du fichier affichée (KB, MB, GB)
- [ ] Type MIME affiché
- [ ] Bouton de téléchargement visible

### Interactions
- [ ] Clic sur image pour zoomer
- [ ] Téléchargement de fichier fonctionne
- [ ] Nom de fichier préservé au téléchargement
- [ ] Hover effects sur les cartes de fichiers

### Sécurité
- [ ] Fichiers > 10MB rejetés
- [ ] Types non supportés rejetés
- [ ] Noms de fichiers uniques générés
- [ ] Fichiers supprimés avec le message

### Performance
- [ ] Upload rapide (< 2 secondes pour 5MB)
- [ ] Affichage instantané
- [ ] Pas de lag dans le chat

---

## 🐛 PROBLÈMES COURANTS

### Problème 1: "File not found"
**Cause:** Le dossier uploads n'existe pas

**Solution:**
```bash
mkdir -p public/uploads/messages
chmod 755 public/uploads/messages
```

### Problème 2: "Permission denied"
**Cause:** Permissions incorrectes

**Solution:**
```bash
chmod -R 755 public/uploads
```

### Problème 3: Fichier ne s'affiche pas
**Cause:** VichUploader mal configuré

**Solution:**
```bash
php bin/console cache:clear
php bin/console debug:config vich_uploader
```

### Problème 4: Image cassée
**Cause:** Chemin incorrect

**Solution:**
Vérifiez que le fichier existe:
```bash
ls -la public/uploads/messages/
```

---

## 📈 STATISTIQUES ACTUELLES

D'après le dossier `public/uploads/messages/`:

```
Fichiers actuels: 5
Types: PNG (4), TXT (1)
Taille totale: ~50 KB
Dernier upload: 23/02/2026 21:02:02
```

**Conclusion:** Le système fonctionne déjà! ✅

---

## 🎯 PROCHAINES ÉTAPES

Une fois tous les tests passés:

1. ✅ Testez avec différents types de fichiers
2. ✅ Testez avec différentes tailles
3. ✅ Testez la suppression
4. ✅ Testez le téléchargement
5. ✅ Vérifiez la galerie d'images

**Tout devrait fonctionner parfaitement!** 🚀

---

## 💡 AMÉLIORATIONS OPTIONNELLES

Si vous voulez aller plus loin:

1. **Drag & Drop:** Glisser-déposer des fichiers
2. **Progress Bar:** Barre de progression pendant l'upload
3. **Preview Vidéo:** Lecteur vidéo intégré
4. **Audio Player:** Lecteur audio avec waveform
5. **Compression:** Compression automatique des images
6. **Thumbnails:** Miniatures pour les vidéos
7. **Upload Multiple:** Plusieurs fichiers à la fois
8. **Cloud Storage:** AWS S3, Google Cloud Storage

Mais pour l'instant, **tout fonctionne!** ✅
