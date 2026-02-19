# 🔍 Diagnostic Erreur 500 - Upload de Fichiers

## ❌ Erreur Observée

**Message**: "Erreur lors de l'envoi du message (Status: 500)"

**Type**: Erreur serveur (Internal Server Error)

## 🔎 Comment Diagnostiquer

### Étape 1: Ouvrir la Console du Navigateur (IMPORTANT!)

1. Appuyez sur **F12**
2. Cliquez sur l'onglet **"Console"**
3. Cliquez sur l'onglet **"Network"** (Réseau)
4. Cochez "Preserve log" pour garder les logs

### Étape 2: Reproduire l'Erreur

1. Sélectionnez un fichier (Capture.PNG)
2. Cliquez sur Envoyer
3. L'erreur 500 apparaît

### Étape 3: Vérifier les Logs JavaScript

Dans la console, vous devriez voir:
```javascript
=== Form submit started ===
Form data entries:
  message[content]: mai
  message[attachment]: File(Capture.PNG, XXXXX bytes, image/png)
Validation passed, sending request...
Sending request to: http://127.0.0.1:8000/goal/1/messages
Response status: 500
✗ Response not OK. Status: 500
Error response: [HTML de l'erreur]
```

### Étape 4: Voir la Réponse Complète

Dans l'onglet "Network":
1. Trouvez la requête "messages" (en rouge)
2. Cliquez dessus
3. Allez dans l'onglet "Response"
4. **COPIEZ TOUT LE CONTENU** - c'est là qu'est l'erreur exacte!

## 🐛 Causes Possibles

### 1. Problème de Validation du Fichier

**Symptôme**: Le fichier ne passe pas la validation Symfony

**Causes**:
- Type MIME non supporté
- Fichier trop gros (> 10MB)
- Extension non reconnue

**Solution**:
```php
// Dans MessageType.php, vérifier que le type MIME est bien dans la liste
'mimeTypes' => [
    'image/jpeg',
    'image/png',  // ← Vérifier que c'est bien là
    'image/gif',
    'image/webp',
    // ...
]
```

### 2. Problème de Permissions

**Symptôme**: Impossible d'écrire dans le dossier uploads

**Causes**:
- Dossier n'existe pas
- Pas de permissions d'écriture

**Solution**:
```bash
# Vérifier que le dossier existe
dir public\uploads\messages

# Si n'existe pas, créer
mkdir public\uploads\messages

# Donner les permissions (Windows)
icacls public\uploads\messages /grant Everyone:F
```

### 3. Problème de Taille de Fichier PHP

**Symptôme**: PHP refuse les fichiers > 2MB

**Causes**:
- `upload_max_filesize` trop petit dans php.ini
- `post_max_size` trop petit dans php.ini

**Solution**:
```ini
; Dans php.ini
upload_max_filesize = 10M
post_max_size = 10M
```

### 4. Erreur dans le Controller

**Symptôme**: Exception PHP dans GoalController

**Causes**:
- Erreur dans le code de traitement du fichier
- Problème avec guessExtension()
- Problème avec move()

**Solution**: Voir les logs Symfony

## 📋 Checklist de Diagnostic

### Vérifications Immédiates

- [ ] Console du navigateur ouverte (F12)
- [ ] Onglet "Network" ouvert
- [ ] "Preserve log" coché
- [ ] Reproduire l'erreur
- [ ] Copier la réponse complète de l'onglet "Response"

### Vérifications Système

- [ ] Dossier `public/uploads/messages` existe
- [ ] Permissions d'écriture sur le dossier
- [ ] Fichier < 10MB
- [ ] Type de fichier supporté (PNG, JPG, etc.)

### Vérifications PHP

- [ ] `upload_max_filesize` >= 10M
- [ ] `post_max_size` >= 10M
- [ ] Extension `fileinfo` activée

### Vérifications Symfony

- [ ] Cache vidé: `php bin/console cache:clear`
- [ ] Logs vérifiés: `tail -f var/log/dev.log`
- [ ] Pas d'erreur de syntaxe

## 🔧 Actions à Faire MAINTENANT

### 1. Copier la Réponse Complète

Dans Network → Response, copiez TOUT le HTML de l'erreur.
Cela contient le message d'erreur exact et la stack trace.

### 2. Vérifier php.ini

```bash
# Trouver php.ini
php --ini

# Vérifier les valeurs
php -i | findstr upload_max_filesize
php -i | findstr post_max_size
```

### 3. Vérifier les Logs Symfony en Temps Réel

```bash
# Dans un terminal séparé
tail -f var/log/dev.log
```

Puis reproduire l'erreur et voir ce qui s'affiche.

## 📊 Informations à Fournir

Pour diagnostiquer l'erreur, j'ai besoin de:

### 1. Réponse Complète (PRIORITAIRE!)
```
[Coller ici le contenu complet de Network → Response]
```

### 2. Logs de la Console JavaScript
```javascript
[Coller ici tous les logs de la console]
```

### 3. Logs Symfony
```
[Coller ici les dernières lignes de var/log/dev.log]
```

### 4. Informations sur le Fichier
- Nom: Capture.PNG
- Taille: [XXX KB/MB]
- Type MIME: image/png

### 5. Configuration PHP
```bash
php -i | findstr upload_max_filesize
php -i | findstr post_max_size
```

## 🎯 Solution Rapide Probable

Basé sur l'erreur 500, voici les solutions les plus probables:

### Solution 1: Augmenter les Limites PHP

Éditez `php.ini`:
```ini
upload_max_filesize = 20M
post_max_size = 20M
max_execution_time = 300
```

Puis redémarrez le serveur:
```bash
# Arrêter le serveur (Ctrl+C)
# Redémarrer
symfony server:start
```

### Solution 2: Vérifier les Permissions

```bash
# Windows
icacls public\uploads\messages /grant Everyone:F

# Vérifier
dir public\uploads\messages
```

### Solution 3: Simplifier la Validation

Temporairement, dans `MessageType.php`, commentez les contraintes:
```php
->add('attachment', FileType::class, [
    'label' => 'Attachment',
    'mapped' => false,
    'required' => false,
    // 'constraints' => [
    //     new File([...])
    // ],
])
```

Puis testez. Si ça marche, le problème vient de la validation.

## 🚨 URGENT: Première Action

**MAINTENANT**, faites ceci:

1. F12 → Network
2. Reproduire l'erreur
3. Cliquer sur la requête "messages" (en rouge)
4. Onglet "Response"
5. **COPIER TOUT** et me l'envoyer

C'est là qu'est l'erreur exacte!

---

**Status**: En attente de la réponse complète de l'erreur
**Priorité**: HAUTE
**Action requise**: Copier le contenu de Network → Response
