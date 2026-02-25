# ✅ Checklist de Test - Upload de Fichiers

## Préparation

- [ ] Ouvrir le navigateur (Chrome, Firefox, ou Edge recommandé)
- [ ] Appuyer sur F12 pour ouvrir la console développeur
- [ ] Aller dans l'onglet "Console"
- [ ] Se connecter avec: `mariemayari@gmail.com` / `mariem`
- [ ] Ouvrir un chatroom (Goal)

## Test 1: Bouton Trombone (📎)

### Actions
- [ ] Cliquer sur le bouton trombone dans la barre de message

### Résultats Attendus
- [ ] La console affiche: `Attach file button clicked`
- [ ] La console affiche: `Found file input by selector...`
- [ ] La console affiche: `Triggering file input click`
- [ ] Une fenêtre de sélection de fichier s'ouvre

### Si ça ne marche pas
- Vérifier les messages d'erreur dans la console
- Rafraîchir la page (Ctrl+F5)
- Vérifier que JavaScript est activé

## Test 2: Sélection d'Image PNG

### Actions
- [ ] Cliquer sur le bouton trombone
- [ ] Sélectionner une image PNG (ex: capture d'écran)

### Résultats Attendus
- [ ] La console affiche: `handleFileSelect called`
- [ ] La console affiche: `File selected: [nom].png Size: [taille] Type: image/png`
- [ ] La console affiche: `File preview displayed`
- [ ] Un badge bleu apparaît avec le nom du fichier et une icône image
- [ ] Le badge a un bouton X pour supprimer

### Si ça ne marche pas
- Vérifier que le fichier fait moins de 10MB
- Vérifier les messages d'erreur dans la console
- Essayer avec une autre image

## Test 3: Envoi du Message avec Image

### Actions
- [ ] Avec l'image sélectionnée, taper un message (optionnel)
- [ ] Cliquer sur le bouton d'envoi (✈️)

### Résultats Attendus
- [ ] La console affiche: `=== Form submit started ===`
- [ ] La console affiche: `Form data entries:`
- [ ] La console affiche: `message[attachment]: File([nom].png, [taille] bytes, image/png)`
- [ ] La console affiche: `Validation passed, sending request...`
- [ ] La console affiche: `Response status: 200`
- [ ] La console affiche: `✓ Message sent successfully!`
- [ ] Le badge disparaît
- [ ] Le champ de texte est vidé
- [ ] L'image apparaît dans le message après quelques secondes

### Si ça ne marche pas
- Copier tous les logs de la console
- Vérifier `var/log/dev.log` pour les erreurs Symfony
- Vérifier que le dossier `public/uploads/messages/` existe

## Test 4: Sélection de PDF

### Actions
- [ ] Cliquer sur le bouton trombone
- [ ] Sélectionner un fichier PDF

### Résultats Attendus
- [ ] Badge avec icône PDF (📄)
- [ ] Nom du fichier affiché
- [ ] Envoi réussi
- [ ] Fichier apparaît comme carte téléchargeable

## Test 5: Message Vocal

### Actions
- [ ] Cliquer sur le bouton microphone (🎤)
- [ ] Autoriser l'accès au microphone si demandé
- [ ] Parler pendant 3-5 secondes
- [ ] Cliquer sur "Envoyer"

### Résultats Attendus
- [ ] Interface d'enregistrement apparaît avec animation
- [ ] Timer s'affiche (0:00, 0:01, 0:02...)
- [ ] La console affiche: `startVoiceRecording called`
- [ ] La console affiche: `Microphone access granted`
- [ ] La console affiche: `MediaRecorder started`
- [ ] Après envoi: `Voice message sent successfully!`
- [ ] Message vocal apparaît avec lecteur audio

### Si ça ne marche pas
- Vérifier que le microphone est autorisé dans le navigateur
- Vérifier les logs de la console
- Essayer dans un autre navigateur

## Test 6: Suppression de Fichier Avant Envoi

### Actions
- [ ] Sélectionner un fichier
- [ ] Cliquer sur le X dans le badge

### Résultats Attendus
- [ ] Le badge disparaît
- [ ] Le fichier est désélectionné
- [ ] Peut sélectionner un nouveau fichier

## Test 7: Envoi Sans Fichier ni Texte

### Actions
- [ ] Ne rien taper
- [ ] Ne pas sélectionner de fichier
- [ ] Cliquer sur envoyer

### Résultats Attendus
- [ ] La console affiche: `Validation failed: no content and no attachment`
- [ ] Alert: "Veuillez entrer un message ou joindre un fichier"
- [ ] Le message n'est pas envoyé

## Test 8: Fichier Trop Gros

### Actions
- [ ] Essayer de sélectionner un fichier > 10MB

### Résultats Attendus
- [ ] Message d'erreur de validation
- [ ] Le fichier n'est pas accepté

## Test 9: Type de Fichier Non Supporté

### Actions
- [ ] Essayer de sélectionner un fichier .exe ou .zip

### Résultats Attendus
- [ ] Message d'erreur: "Please upload a valid file..."
- [ ] Le fichier n'est pas accepté

## Test 10: Affichage dans Recent Images

### Actions
- [ ] Envoyer plusieurs images
- [ ] Ouvrir le panneau "Group Info" (bouton ℹ️)
- [ ] Regarder la section "Recent Images"

### Résultats Attendus
- [ ] Les 6 dernières images apparaissent en grille 3x3
- [ ] Hover sur une image montre une icône de zoom
- [ ] Clic ouvre l'image en grand

## Résumé des Tests

| Test | Status | Notes |
|------|--------|-------|
| 1. Bouton Trombone | ⬜ | |
| 2. Sélection PNG | ⬜ | |
| 3. Envoi avec Image | ⬜ | |
| 4. Sélection PDF | ⬜ | |
| 5. Message Vocal | ⬜ | |
| 6. Suppression Fichier | ⬜ | |
| 7. Validation Vide | ⬜ | |
| 8. Fichier Trop Gros | ⬜ | |
| 9. Type Non Supporté | ⬜ | |
| 10. Recent Images | ⬜ | |

## Logs à Fournir en Cas de Problème

### Console JavaScript
```
[Copier tous les logs de la console ici]
```

### Logs Symfony
```bash
# Commande pour voir les logs
tail -f var/log/dev.log
```

### Informations Système
- Navigateur: [Chrome/Firefox/Edge] Version: [X.X]
- Système: [Windows/Mac/Linux]
- Type de fichier testé: [PNG/PDF/etc.]
- Taille du fichier: [XXX KB/MB]

## Notes Importantes

⚠️ **Permissions**: Les dossiers `public/uploads/messages/` et `public/uploads/voice/` doivent être accessibles en écriture

⚠️ **Microphone**: Pour les messages vocaux, le navigateur doit avoir l'autorisation d'accéder au microphone

⚠️ **HTTPS**: Certaines fonctionnalités (microphone) nécessitent HTTPS en production

✅ **Logs**: Toujours garder la console ouverte pour voir les logs détaillés

✅ **Rafraîchir**: En cas de problème, essayer Ctrl+F5 pour rafraîchir complètement

## Commandes Utiles

### Vérifier les fichiers uploadés
```bash
dir public\uploads\messages
dir public\uploads\voice
```

### Voir les logs Symfony en temps réel
```bash
tail -f var/log/dev.log
```

### Vider le cache Symfony
```bash
php bin/console cache:clear
```

### Vérifier la syntaxe
```bash
php bin/console lint:twig templates/chatroom/chatroom.html.twig
php bin/console lint:container
```

---

**Date**: 17 février 2026
**Version**: 1.0
**Status**: ✅ Prêt pour les tests
