# 🧪 Test Upload de Fichiers - Guide Rapide

## ✅ Préparation Effectuée

Toutes les modifications ont été appliquées:
- ✅ MessageType.php - Support étendu des types de fichiers
- ✅ GoalController.php - Détection MIME améliorée
- ✅ chatroom.html.twig - Logs détaillés ajoutés
- ✅ Cache Symfony vidé

## 🚀 Comment Tester MAINTENANT

### Étape 1: Ouvrir la Console du Navigateur
1. Ouvrez votre navigateur (Chrome, Firefox, ou Edge)
2. Appuyez sur **F12** pour ouvrir les DevTools
3. Cliquez sur l'onglet **"Console"**
4. Gardez cette console ouverte pendant tous les tests

### Étape 2: Se Connecter
1. Allez sur votre application Symfony
2. Connectez-vous avec: `mariemayari@gmail.com` / `mariem`
3. Ouvrez un Goal qui a un chatroom

### Étape 3: Test Upload d'Image 📷

#### Actions:
1. Dans le chatroom, cliquez sur le bouton **trombone (📎)**
2. Sélectionnez une **image PNG ou JPG** de votre ordinateur
3. Observez la console

#### Ce que vous devriez voir dans la console:
```javascript
Attach file button clicked
Found file input by selector: input[type="file"]...
Triggering file input click
handleFileSelect called
File selected: photo.png Size: 123456 Type: image/png
File preview displayed
```

#### Ce que vous devriez voir à l'écran:
- Un **badge bleu** apparaît avec le nom du fichier
- Une **icône image** (🖼️) à côté du nom
- Un **bouton X** pour supprimer

#### Envoyer le message:
1. Tapez un message (optionnel) ou laissez vide
2. Cliquez sur le bouton **envoyer (✈️)**

#### Ce que vous devriez voir dans la console:
```javascript
=== Form submit started ===
Form data entries:
  message[content]: [votre texte]
  message[attachment]: File(photo.png, 123456 bytes, image/png)
Validation passed, sending request...
Response status: 200
✓ Message sent successfully!
```

#### Résultat attendu:
- ✅ L'image apparaît dans le message
- ✅ Vous pouvez cliquer dessus pour l'agrandir
- ✅ Le badge disparaît après l'envoi

### Étape 4: Test Upload de PDF 📄

#### Actions:
1. Cliquez sur le bouton **trombone (📎)**
2. Sélectionnez un **fichier PDF**

#### Ce que vous devriez voir:
- Badge avec **icône PDF** (📄)
- Nom du fichier affiché

#### Après envoi:
- ✅ Le PDF apparaît comme une **carte téléchargeable**
- ✅ Icône PDF visible
- ✅ Bouton de téléchargement

### Étape 5: Test Upload de Document Word 📝

#### Actions:
1. Cliquez sur le bouton **trombone (📎)**
2. Sélectionnez un **fichier Word (.doc ou .docx)**

#### Résultat attendu:
- ✅ Badge avec icône Word
- ✅ Après envoi, carte téléchargeable
- ✅ Nom du fichier visible

## 🐛 Si Ça Ne Marche Pas

### Problème 1: "File input not found!"
**Solution**:
```bash
1. Rafraîchir la page avec Ctrl+F5
2. Vider le cache du navigateur
3. Réessayer
```

### Problème 2: "Erreur lors de l'envoi du message"
**Vérifications**:
1. Regardez les logs dans la console (copiez tout)
2. Vérifiez la taille du fichier (< 10MB)
3. Vérifiez le type de fichier (doit être dans la liste supportée)

**Commande pour voir les logs Symfony**:
```bash
tail -f var/log/dev.log
```

### Problème 3: Le fichier ne s'affiche pas après envoi
**Vérifications**:
```bash
# Vérifier que le fichier a été uploadé
dir public\uploads\messages

# Vérifier les permissions
# Le dossier doit être accessible en écriture
```

### Problème 4: Le bouton trombone ne fait rien
**Solution**:
1. Vérifiez la console pour les erreurs JavaScript
2. Rafraîchissez la page (Ctrl+F5)
3. Vérifiez que JavaScript est activé dans votre navigateur

## 📊 Checklist de Test

### Test Image
- [ ] Clic sur bouton trombone
- [ ] Sélection d'une image PNG
- [ ] Badge apparaît avec nom et icône
- [ ] Console affiche les logs corrects
- [ ] Envoi du message réussi
- [ ] Image apparaît dans le chat
- [ ] Image cliquable pour agrandir

### Test PDF
- [ ] Sélection d'un PDF
- [ ] Badge avec icône PDF
- [ ] Envoi réussi
- [ ] Carte téléchargeable apparaît
- [ ] Téléchargement fonctionne

### Test Document Word
- [ ] Sélection d'un fichier Word
- [ ] Badge avec icône Word
- [ ] Envoi réussi
- [ ] Carte téléchargeable apparaît

### Test Suppression
- [ ] Sélection d'un fichier
- [ ] Clic sur X dans le badge
- [ ] Badge disparaît
- [ ] Peut sélectionner un nouveau fichier

### Test Validation
- [ ] Essayer d'envoyer sans texte ni fichier
- [ ] Alert: "Veuillez entrer un message ou joindre un fichier"
- [ ] Message non envoyé

## 📝 Logs à Copier en Cas de Problème

Si vous rencontrez un problème, copiez ces informations:

### 1. Logs de la Console JavaScript
```
[Coller tous les logs de la console ici]
```

### 2. Type de Fichier Testé
- Nom: [nom du fichier]
- Type: [PNG/PDF/Word/etc.]
- Taille: [XXX KB/MB]

### 3. Navigateur
- Navigateur: [Chrome/Firefox/Edge]
- Version: [XX.X]

### 4. Message d'Erreur
```
[Coller le message d'erreur exact]
```

## 🎯 Types de Fichiers Supportés

| Type | Extensions | Icône | Taille Max |
|------|-----------|-------|------------|
| Image | .jpg, .png, .gif, .webp | 🖼️ | 10MB |
| PDF | .pdf | 📄 | 10MB |
| Word | .doc, .docx | 📝 | 10MB |
| Excel | .xls, .xlsx | 📊 | 10MB |
| Texte | .txt | 📃 | 10MB |
| Vidéo | .mp4, .webm, .mov | 🎥 | 10MB |
| Audio | .mp3, .webm | 🎵 | 10MB |

## 🔍 Vérifications Finales

### Avant de Commencer
```bash
# Vérifier que les dossiers existent
dir public\uploads\messages
dir public\uploads\voice

# Si ils n'existent pas, les créer
mkdir public\uploads\messages
mkdir public\uploads\voice
```

### Pendant les Tests
- ✅ Console ouverte (F12)
- ✅ Onglet "Console" sélectionné
- ✅ Connecté avec mariemayari@gmail.com
- ✅ Dans un chatroom

### Après les Tests
- ✅ Vérifier que les fichiers sont dans `public/uploads/messages/`
- ✅ Vérifier que les messages apparaissent correctement
- ✅ Vérifier que les téléchargements fonctionnent

## 💡 Conseils

1. **Commencez petit**: Testez d'abord avec une petite image (< 1MB)
2. **Logs**: Gardez toujours la console ouverte
3. **Patience**: Attendez quelques secondes après l'envoi
4. **Rafraîchir**: Si problème, essayez Ctrl+F5
5. **Cache**: Le cache a été vidé, mais vous pouvez le revider si besoin

## 🎉 Succès!

Si tout fonctionne, vous devriez voir:
- ✅ Badge de fichier apparaît
- ✅ Logs corrects dans la console
- ✅ Message envoyé avec succès
- ✅ Fichier visible dans le chat
- ✅ Téléchargement fonctionne

## 📞 Besoin d'Aide?

Si vous rencontrez un problème:
1. Copiez TOUS les logs de la console
2. Notez le type de fichier testé
3. Notez le message d'erreur exact
4. Vérifiez `var/log/dev.log` pour les erreurs Symfony

---

**Status**: ✅ Prêt pour les tests  
**Cache**: ✅ Vidé  
**Documentation**: ✅ Disponible  
**Support**: ✅ Complet

**COMMENCEZ MAINTENANT!** 🚀
