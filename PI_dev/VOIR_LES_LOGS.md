# 📊 Comment Voir les Logs de Débogage

## 🎯 J'ai Ajouté des Logs Détaillés

Le controller va maintenant logger chaque étape du processus d'upload.

## 📝 Comment Voir les Logs

### Méthode 1: Logs PHP (error_log)

Les logs sont écrits dans le fichier d'erreur PHP.

**Windows avec Symfony CLI**:
```bash
# Les logs apparaissent dans la console où vous avez lancé le serveur
# Regardez la fenêtre du terminal
```

**Ou dans le fichier de log**:
```bash
# Voir les logs en temps réel
tail -f var/log/dev.log

# Ou voir les dernières lignes
Get-Content var/log/dev.log -Tail 50
```

### Méthode 2: Profiler Symfony

1. Après l'erreur 500, regardez en bas de la page
2. Vous devriez voir une barre de debug Symfony
3. Cliquez sur l'icône rouge (erreur)
4. Vous verrez l'exception complète

## 🔍 Ce Que Vous Devriez Voir

Quand vous uploadez un fichier, les logs devraient afficher:

```
=== START FILE UPLOAD DEBUG ===
Attachment file: YES
File original name: Capture.PNG
File size: 123456
File MIME type: image/png
Extension: png
New filename: capture-abc123.png
Upload dir: C:/Users/.../public/uploads/messages
Moving file...
File moved successfully!
Determining file type from MIME: image/png
File type set to: image
Content value: mai
Persisting message...
Message persisted successfully!
=== END FILE UPLOAD DEBUG ===
```

## ❌ Si Erreur

Si une erreur se produit, vous verrez:

```
=== START FILE UPLOAD DEBUG ===
Attachment file: YES
File original name: Capture.PNG
...
ERROR during file upload: [message d'erreur]
Stack trace: [détails de l'erreur]
```

Ou:

```
FATAL ERROR: [message d'erreur]
Stack trace: [détails de l'erreur]
```

## 🚀 Étapes pour Tester

### 1. Ouvrir le Terminal du Serveur

Si vous utilisez Symfony CLI:
```bash
symfony server:start
```

Gardez ce terminal ouvert et visible.

### 2. Tester l'Upload

1. Allez dans le chatroom
2. Sélectionnez un fichier
3. Cliquez sur Envoyer

### 3. Regarder les Logs

**Dans le terminal du serveur**, vous devriez voir apparaître:
```
=== START FILE UPLOAD DEBUG ===
...
```

### 4. Copier les Logs

Si vous voyez une erreur, copiez TOUT depuis `=== START` jusqu'à la fin de l'erreur.

## 📋 Exemple de Logs à Copier

```
=== START FILE UPLOAD DEBUG ===
Attachment file: YES
File original name: Capture.PNG
File size: 123456
File MIME type: image/png
Extension: png
New filename: capture-abc123.png
Upload dir: C:/Users/Mariem&Islem/Documents/GitHub/Projet-PI-2026/PI_dev/public/uploads/messages
Moving file...
ERROR during file upload: Permission denied
Stack trace: #0 /path/to/Controller.php(123): move()
#1 ...
```

## 🎯 Actions Immédiates

1. **Redémarrez le serveur** pour que les nouveaux logs soient actifs:
   ```bash
   # Arrêter (Ctrl+C)
   # Redémarrer
   symfony server:start
   ```

2. **Testez l'upload** d'un fichier

3. **Regardez le terminal** où le serveur tourne

4. **Copiez les logs** qui apparaissent

5. **Partagez-les** avec moi pour diagnostic

## 💡 Astuce

Si vous ne voyez pas les logs dans le terminal, essayez:

```bash
# Voir les logs Symfony
Get-Content var/log/dev.log -Tail 100 -Wait
```

Cette commande affichera les logs en temps réel.

---

**MAINTENANT**: Redémarrez le serveur et testez!
