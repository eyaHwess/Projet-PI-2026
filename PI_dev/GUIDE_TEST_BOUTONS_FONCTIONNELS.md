# Guide de Test - Boutons Fonctionnels

## 🎯 Objectif
Vérifier que tous les boutons fonctionnent correctement pour envoyer des messages.

## ✅ Tests à Effectuer

### Test 1: Envoyer un Message Texte Simple

**Étapes:**
1. Ouvrir un chatroom (accéder via /goals puis cliquer sur un goal)
2. Taper un message dans la zone de texte
3. Observer que le bouton ✈️ devient plus visible (opacité 1.0)
4. Cliquer sur le bouton ✈️ Envoyer
5. Le message doit apparaître dans le chat

**Résultat attendu:**
- ✅ Message envoyé et visible dans le chat
- ✅ Zone de texte se vide après envoi
- ✅ Pas d'erreur dans la console

---

### Test 2: Envoyer une Image

**Étapes:**
1. Cliquer sur le bouton 📎 (bleu)
2. Sélectionner une image (JPG, PNG, GIF, WEBP)
3. Observer la prévisualisation de l'image (miniature 48×48px)
4. Le bouton 📎 devient actif (fond bleu clair)
5. (Optionnel) Taper un message d'accompagnement
6. Cliquer sur le bouton ✈️ Envoyer
7. L'image doit apparaître dans le chat

**Résultat attendu:**
- ✅ Image uploadée et visible dans le chat
- ✅ Prévisualisation disparaît après envoi
- ✅ Bouton 📎 redevient normal
- ✅ Image cliquable pour agrandissement

**Si ça ne fonctionne pas:**
- Vérifier que le dossier `public/uploads/messages/` existe
- Vérifier les permissions du dossier
- Regarder les logs: `var/log/dev.log`

---

### Test 3: Envoyer un Fichier (PDF, Word, etc.)

**Étapes:**
1. Cliquer sur le bouton 📎 (bleu)
2. Sélectionner un fichier (PDF, DOC, DOCX, XLS, XLSX, TXT)
3. Observer l'icône appropriée dans la prévisualisation
4. Le bouton 📎 devient actif (fond bleu clair)
5. (Optionnel) Taper un message d'accompagnement
6. Cliquer sur le bouton ✈️ Envoyer
7. Le fichier doit apparaître avec un lien de téléchargement

**Résultat attendu:**
- ✅ Fichier uploadé
- ✅ Icône appropriée affichée (PDF rouge, Word bleu, etc.)
- ✅ Lien de téléchargement fonctionnel
- ✅ Nom du fichier visible

---

### Test 4: Envoyer un Message Vocal

**Étapes:**
1. Cliquer sur le bouton 🎤 (rouge)
2. Le modal d'enregistrement s'ouvre
3. Le bouton 🎤 devient actif (fond rouge clair)
4. Cliquer sur "Enregistrer" (bouton violet)
5. Autoriser l'accès au microphone si demandé
6. Parler pendant quelques secondes
7. Observer le timer qui avance (00:01, 00:02, etc.)
8. Observer les barres d'animation qui bougent
9. Cliquer sur "Arrêter" (bouton rouge)
10. Cliquer sur "Envoyer" (bouton vert)
11. Le message vocal doit apparaître dans le chat

**Résultat attendu:**
- ✅ Modal s'ouvre correctement
- ✅ Enregistrement fonctionne
- ✅ Timer avance
- ✅ Animations visibles
- ✅ Message vocal envoyé
- ✅ Lecteur audio fonctionnel dans le chat
- ✅ Modal se ferme après envoi
- ✅ Bouton 🎤 redevient normal

**Si ça ne fonctionne pas:**
- Vérifier que le navigateur a accès au microphone
- Vérifier que le dossier `public/uploads/voice/` existe
- Regarder les logs pour les erreurs

---

### Test 5: Utiliser les Emojis

**Étapes:**
1. Cliquer sur le bouton 😊 (jaune)
2. Le sélecteur d'emojis s'ouvre
3. Le bouton 😊 devient actif (fond jaune clair)
4. Cliquer sur plusieurs emojis
5. Observer qu'ils s'insèrent dans la zone de texte
6. Cliquer à l'extérieur du sélecteur
7. Le sélecteur se ferme
8. Le bouton 😊 redevient normal
9. Cliquer sur ✈️ Envoyer
10. Le message avec emojis apparaît dans le chat

**Résultat attendu:**
- ✅ Sélecteur s'ouvre/ferme correctement
- ✅ Emojis s'insèrent au curseur
- ✅ Sélection multiple fonctionne
- ✅ Fermeture automatique en cliquant à l'extérieur
- ✅ Message avec emojis envoyé correctement

---

### Test 6: Combiner Texte + Image

**Étapes:**
1. Taper un message
2. Cliquer sur 📎 et sélectionner une image
3. Observer les deux: texte + prévisualisation image
4. Cliquer sur ✈️ Envoyer
5. Le message doit contenir le texte ET l'image

**Résultat attendu:**
- ✅ Texte et image envoyés ensemble
- ✅ Affichage correct dans le chat

---

### Test 7: Combiner Texte + Emojis

**Étapes:**
1. Taper "Bonjour"
2. Cliquer sur 😊
3. Ajouter des emojis: 👋 😊 🎉
4. Continuer à taper: "Comment ça va?"
5. Résultat: "Bonjour 👋 😊 🎉 Comment ça va?"
6. Cliquer sur ✈️ Envoyer

**Résultat attendu:**
- ✅ Emojis insérés au bon endroit
- ✅ Message complet envoyé

---

### Test 8: Auto-resize de la Zone de Texte

**Étapes:**
1. Taper un long message sur plusieurs lignes
2. Observer que la zone de texte s'agrandit automatiquement
3. Maximum 120px de hauteur
4. Au-delà, un scroll apparaît

**Résultat attendu:**
- ✅ Zone s'agrandit jusqu'à 120px
- ✅ Scroll apparaît si dépassement
- ✅ Pas de coupure du texte

---

### Test 9: Bouton Envoyer - États

**Test A: Sans contenu**
- Zone de texte vide
- Pas de fichier
- Bouton ✈️ légèrement transparent (opacité 0.7)
- Mais toujours cliquable

**Test B: Avec texte**
- Taper du texte
- Bouton ✈️ devient pleinement visible (opacité 1.0)

**Test C: Avec fichier**
- Ajouter un fichier
- Même sans texte, bouton ✈️ visible

**Résultat attendu:**
- ✅ Feedback visuel clair
- ✅ Bouton toujours fonctionnel

---

### Test 10: Annuler un Enregistrement Vocal

**Étapes:**
1. Cliquer sur 🎤
2. Cliquer sur "Enregistrer"
3. Parler quelques secondes
4. Cliquer sur "Annuler" (au lieu d'Arrêter)
5. Modal se ferme
6. Aucun message envoyé

**Résultat attendu:**
- ✅ Enregistrement annulé
- ✅ Pas de message dans le chat
- ✅ Modal fermé proprement

---

## 🐛 Dépannage

### Problème: Les fichiers ne s'envoient pas

**Solutions:**
1. Vérifier les dossiers:
```bash
ls -la public/uploads/messages/
ls -la public/uploads/voice/
```

2. Vérifier les permissions:
```bash
chmod 777 public/uploads/messages/
chmod 777 public/uploads/voice/
```

3. Vérifier les logs:
```bash
tail -f var/log/dev.log
```

4. Vider le cache:
```bash
php bin/console cache:clear
```

### Problème: Le message vocal ne s'enregistre pas

**Solutions:**
1. Vérifier l'accès au microphone dans le navigateur
2. Tester dans un autre navigateur (Chrome recommandé)
3. Vérifier que le site est en HTTPS (requis pour getUserMedia)
4. Regarder la console JavaScript (F12)

### Problème: Les emojis ne s'affichent pas

**Solutions:**
1. Vider le cache du navigateur (Ctrl+F5)
2. Vérifier que le sélecteur s'ouvre (cliquer sur 😊)
3. Regarder la console pour les erreurs JavaScript

### Problème: Le bouton envoyer ne réagit pas

**Solutions:**
1. Vérifier que le formulaire a l'attribut `enctype="multipart/form-data"`
2. Regarder la console pour les erreurs JavaScript
3. Vérifier que l'ID `sendBtn` existe
4. Tester en désactivant les extensions du navigateur

---

## 📊 Checklist Complète

### Boutons
- [ ] 📎 Fichier - Ouvre le sélecteur
- [ ] 📎 Fichier - Prévisualisation fonctionne
- [ ] 📎 Fichier - État actif visible
- [ ] 🎤 Vocal - Ouvre le modal
- [ ] 🎤 Vocal - Enregistrement fonctionne
- [ ] 🎤 Vocal - État actif visible
- [ ] 😊 Emoji - Ouvre le sélecteur
- [ ] 😊 Emoji - Insertion au curseur
- [ ] 😊 Emoji - État actif visible
- [ ] ✈️ Envoyer - Feedback visuel
- [ ] ✈️ Envoyer - Toujours cliquable

### Fonctionnalités
- [ ] Envoyer message texte
- [ ] Envoyer image
- [ ] Envoyer fichier (PDF, Word, etc.)
- [ ] Envoyer message vocal
- [ ] Utiliser emojis
- [ ] Combiner texte + image
- [ ] Combiner texte + emojis
- [ ] Auto-resize zone de texte
- [ ] Annuler enregistrement vocal
- [ ] Supprimer fichier attaché

### Interface
- [ ] Animations fluides
- [ ] États actifs visibles
- [ ] Couleurs distinctives
- [ ] Hover effects
- [ ] Responsive design

---

## ✅ Résultat Final Attendu

Après tous les tests, vous devriez pouvoir:
1. ✅ Envoyer des messages texte
2. ✅ Envoyer des images
3. ✅ Envoyer des fichiers
4. ✅ Envoyer des messages vocaux
5. ✅ Utiliser des emojis
6. ✅ Combiner texte + fichiers + emojis
7. ✅ Avoir un feedback visuel clair sur chaque action
8. ✅ Utiliser une interface moderne et intuitive

---

## 🚀 Commandes Utiles

**Vider le cache:**
```bash
php bin/console cache:clear
```

**Voir les logs en temps réel:**
```bash
tail -f var/log/dev.log
```

**Vérifier les permissions:**
```bash
ls -la public/uploads/
```

**Créer les dossiers si manquants:**
```bash
mkdir -p public/uploads/messages
mkdir -p public/uploads/voice
chmod 777 public/uploads/messages
chmod 777 public/uploads/voice
```

---

## 📝 Notes

- Tous les boutons sont maintenant fonctionnels
- Le bouton envoyer est toujours actif (pas de blocage)
- Les états actifs donnent un feedback visuel clair
- Les animations rendent l'interface plus agréable
- Le code est optimisé et sans conflits
