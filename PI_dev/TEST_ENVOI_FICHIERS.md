# Test d'Envoi de Fichiers et Images

## ✅ Corrections Appliquées

### 1. Simplification de l'Interface
- **Avant**: 2 boutons séparés (image + fichier)
- **Après**: 1 seul bouton 📎 pour tous les types de fichiers
- Le bouton accepte: images, vidéos, audio, PDF, Word, Excel, texte

### 2. Suppression du Bouton Image Séparé
Le bouton image séparé causait des conflits. Maintenant:
- Un seul input file qui accepte tous les types
- Prévisualisation automatique pour les images
- Icônes appropriées pour les autres types de fichiers

### 3. JavaScript Simplifié
La fonction `handleFileSelect()` maintenant:
- Affiche une prévisualisation d'image si c'est une image
- Affiche une icône appropriée pour les autres fichiers
- Ne tente plus de copier les fichiers (source du bug)
- Laisse le formulaire Symfony gérer l'upload

### 4. Logs de Débogage Ajoutés
Le contrôleur affiche maintenant dans les logs:
- Si un fichier est attaché
- Le nom du fichier
- La taille du fichier
- Le type MIME

## 🧪 Comment Tester

### Test 1: Envoyer une Image
1. Allez dans un chatroom
2. Cliquez sur le bouton 📎 (bleu)
3. Sélectionnez une image (JPG, PNG, GIF, WEBP)
4. Vous devriez voir une miniature de l'image
5. Tapez un message (optionnel)
6. Cliquez sur Envoyer
7. L'image devrait apparaître dans le chat

### Test 2: Envoyer un PDF
1. Cliquez sur le bouton 📎
2. Sélectionnez un fichier PDF
3. Vous devriez voir l'icône PDF rouge
4. Cliquez sur Envoyer
5. Le PDF devrait apparaître avec un lien de téléchargement

### Test 3: Envoyer un Fichier Word
1. Cliquez sur le bouton 📎
2. Sélectionnez un fichier .doc ou .docx
3. Vous devriez voir l'icône Word bleue
4. Cliquez sur Envoyer
5. Le document devrait apparaître avec un lien

### Test 4: Message Vocal
1. Cliquez sur le bouton 🎤 (rouge)
2. Cliquez sur "Enregistrer"
3. Parlez pendant quelques secondes
4. Cliquez sur "Arrêter"
5. Cliquez sur "Envoyer"
6. Le message vocal devrait apparaître avec un lecteur audio

## 🔍 Vérification des Logs

Pour voir les logs de débogage:
```bash
# Sur Windows
Get-Content var/log/dev.log -Tail 50

# Ou regarder le fichier directement
var/log/dev.log
```

Cherchez les lignes:
```
Form submitted. Has attachment: YES
File name: example.jpg
File size: 123456
File type: image/jpeg
```

## 🐛 Si Ça Ne Fonctionne Toujours Pas

### Vérification 1: Permissions du Répertoire
```bash
# Vérifier que le dossier existe et est accessible
ls -la public/uploads/messages
```

### Vérification 2: Configuration PHP
Vérifiez dans `php.ini`:
```ini
upload_max_filesize = 10M
post_max_size = 10M
file_uploads = On
```

### Vérification 3: Formulaire
Le formulaire doit avoir `enctype="multipart/form-data"`:
```twig
{{ form_start(form, {'attr': {'enctype': 'multipart/form-data'}}) }}
```
✅ Déjà présent dans le template

### Vérification 4: Champ du Formulaire
Le champ `attachment` doit être de type `FileType`:
```php
->add('attachment', FileType::class, [
    'mapped' => false,
    'required' => false,
])
```
✅ Déjà configuré dans MessageType.php

## 📊 Structure des Fichiers

```
public/
  uploads/
    messages/          ← Les fichiers sont stockés ici
      image-abc123.jpg
      document-def456.pdf
      ...
    voice/             ← Les messages vocaux
      voice-ghi789.webm
      ...
```

## 🎯 Résultat Attendu

Après l'envoi:
1. Le fichier est uploadé dans `public/uploads/messages/`
2. Le message apparaît dans le chat avec:
   - Une miniature pour les images
   - Un lecteur pour les vidéos/audio
   - Un lien de téléchargement pour les documents
3. Le formulaire se réinitialise
4. La prévisualisation disparaît

## 💡 Différences Clés

| Avant | Après |
|-------|-------|
| 2 inputs séparés (image + fichier) | 1 seul input pour tout |
| JavaScript copie les fichiers | JavaScript affiche juste la preview |
| Bouton image vert + bouton fichier bleu | Bouton fichier bleu unique |
| Conflits entre les inputs | Pas de conflit |

## 🔧 Code Modifié

### Template (chatroom_modern.html.twig)
- Supprimé: `<input id="imageAttachment">`
- Modifié: Bouton paperclip accepte tous les types
- Simplifié: `handleFileSelect()` ne copie plus les fichiers

### Contrôleur (MessageController.php)
- Ajouté: Logs de débogage
- Inchangé: La logique d'upload (déjà correcte)

### Formulaire (MessageType.php)
- Inchangé: Configuration déjà correcte
