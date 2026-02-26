# Guide de Débogage - Upload de Fichiers

## Modifications Effectuées

### 1. MessageType.php - Support de Plus de Types de Fichiers
- ✅ Ajout du support pour les vidéos (mp4, webm, quicktime)
- ✅ Ajout du support pour les fichiers audio (webm, mpeg, mp3)
- ✅ Taille maximale: 10MB
- ✅ Message d'erreur mis à jour

### 2. GoalController.php - Détection Améliorée des Types MIME
- ✅ Meilleure détection des fichiers webm (vidéo et audio)
- ✅ Support explicite pour `video/webm` et `audio/webm`
- ✅ Gestion des erreurs améliorée avec try-catch
- ✅ Retour JSON pour les requêtes AJAX

### 3. chatroom.html.twig - Logging Détaillé
- ✅ Logs détaillés dans `handleFileSelect()`
- ✅ Logs détaillés dans `handleFormSubmit()`
- ✅ Affichage des erreurs de validation
- ✅ Support des icônes vidéo dans la prévisualisation

## Comment Tester

### Étape 1: Ouvrir la Console du Navigateur
1. Appuyez sur F12 pour ouvrir les DevTools
2. Allez dans l'onglet "Console"
3. Gardez la console ouverte pendant les tests

### Étape 2: Tester l'Upload de Fichier
1. Cliquez sur le bouton trombone (📎) dans le chatroom
2. Vérifiez dans la console:
   ```
   Attach file button clicked
   Found file input by selector: input[type="file"]...
   Triggering file input click
   ```

3. Sélectionnez un fichier (PNG, JPG, PDF, etc.)
4. Vérifiez dans la console:
   ```
   handleFileSelect called
   File selected: [nom du fichier] Size: [taille] Type: [type MIME]
   File preview displayed
   ```

5. Le fichier devrait apparaître dans une badge à côté du champ de saisie

### Étape 3: Envoyer le Message
1. Tapez un message (optionnel si vous avez un fichier)
2. Cliquez sur le bouton d'envoi (✈️)
3. Vérifiez dans la console:
   ```
   === Form submit started ===
   Form data entries:
     message[content]: [votre texte]
     message[attachment]: File([nom], [taille] bytes, [type])
   Validation passed, sending request...
   Response status: 200
   ✓ Message sent successfully!
   ```

## Types de Fichiers Supportés

### Images
- ✅ JPEG (.jpg, .jpeg)
- ✅ PNG (.png)
- ✅ GIF (.gif)
- ✅ WebP (.webp)

### Vidéos
- ✅ MP4 (.mp4)
- ✅ WebM (.webm)
- ✅ QuickTime (.mov)

### Audio
- ✅ WebM (.webm) - utilisé pour les messages vocaux
- ✅ MP3 (.mp3)
- ✅ MPEG (.mpeg)

### Documents
- ✅ PDF (.pdf)
- ✅ Word (.doc, .docx)
- ✅ Excel (.xls, .xlsx)
- ✅ Texte (.txt)

## Messages d'Erreur Possibles

### "File input not found!"
**Cause**: Le champ de fichier n'a pas été trouvé dans le DOM
**Solution**: Vérifier que le formulaire est bien chargé

### "Preview elements not found!"
**Cause**: Les éléments de prévisualisation ne sont pas dans le DOM
**Solution**: Vérifier que `filePreview`, `file-preview-name`, et `file-preview-icon` existent

### "Validation failed: no content and no attachment"
**Cause**: Aucun texte ni fichier n'a été fourni
**Solution**: Ajouter du texte ou sélectionner un fichier

### "Erreur: La réponse du serveur n'est pas au format JSON"
**Cause**: Le serveur a retourné du HTML au lieu de JSON
**Solution**: Vérifier les logs du serveur Symfony

### "Please upload a valid file..."
**Cause**: Le type de fichier n'est pas supporté
**Solution**: Utiliser un des types de fichiers listés ci-dessus

## Vérifications Côté Serveur

### Logs Symfony
Vérifiez les logs dans `var/log/dev.log` pour voir:
- Les erreurs de validation
- Les erreurs d'upload
- Les erreurs de base de données

### Permissions des Dossiers
Vérifiez que ces dossiers existent et sont accessibles en écriture:
```
public/uploads/messages/
public/uploads/voice/
```

Si les dossiers n'existent pas, créez-les:
```bash
mkdir -p public/uploads/messages
mkdir -p public/uploads/voice
chmod 777 public/uploads/messages
chmod 777 public/uploads/voice
```

## Test Rapide

Pour tester rapidement si tout fonctionne:

1. **Test Image**: Prenez une capture d'écran et uploadez-la
2. **Test PDF**: Créez un fichier PDF simple et uploadez-le
3. **Test Texte**: Créez un fichier .txt et uploadez-le
4. **Test Message Vocal**: Cliquez sur le microphone et enregistrez un message

## Résolution des Problèmes

### Le bouton trombone ne fait rien
1. Vérifiez la console pour les erreurs JavaScript
2. Vérifiez que l'événement click est bien attaché
3. Essayez de rafraîchir la page (Ctrl+F5)

### Le fichier ne s'affiche pas après sélection
1. Vérifiez que `handleFileSelect()` est appelé
2. Vérifiez que les éléments de prévisualisation existent
3. Vérifiez les logs dans la console

### "Erreur lors de l'envoi du message"
1. Vérifiez les logs de la console JavaScript
2. Vérifiez les logs Symfony (`var/log/dev.log`)
3. Vérifiez que le fichier ne dépasse pas 10MB
4. Vérifiez que le type MIME est supporté

### Le message est envoyé mais le fichier n'apparaît pas
1. Vérifiez que le fichier a bien été uploadé dans `public/uploads/messages/`
2. Vérifiez les permissions du dossier
3. Vérifiez que le chemin dans la base de données est correct
4. Rafraîchissez la page pour voir si le fichier apparaît

## Prochaines Étapes

Si le problème persiste après ces vérifications:
1. Partagez les logs de la console JavaScript
2. Partagez les logs Symfony
3. Indiquez le type de fichier que vous essayez d'uploader
4. Indiquez à quelle étape le problème se produit

## Améliorations Apportées

✅ Support complet des vidéos et audio
✅ Logging détaillé pour le débogage
✅ Meilleure gestion des erreurs
✅ Validation améliorée
✅ Messages d'erreur plus clairs
✅ Support des fichiers webm pour les messages vocaux
