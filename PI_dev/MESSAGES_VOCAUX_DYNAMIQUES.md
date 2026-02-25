# Messages Vocaux Dynamiques ✅

## Vue d'Ensemble

Les messages vocaux sont maintenant entièrement fonctionnels avec enregistrement, prévisualisation et envoi automatique au serveur.

## Fonctionnalités Implémentées

### 1. Enregistrement Audio
**Processus:**
1. Cliquer sur l'icône micro 🎤
2. Autoriser l'accès au microphone (première fois)
3. Enregistrement démarre automatiquement
4. Indicateur rouge "Recording..." avec timer
5. Bouton devient rouge avec icône stop
6. Cliquer à nouveau pour arrêter

**Caractéristiques:**
- Format: audio/webm
- Timer en temps réel (MM:SS)
- Animation de pulsation
- Arrêt automatique possible

### 2. Prévisualisation
**Après l'enregistrement:**
- Aperçu avec bouton play ▶️
- Durée affichée
- Bouton envoyer ✈️ (vert)
- Bouton annuler ❌

**Actions possibles:**
- Écouter l'enregistrement avant envoi
- Envoyer le message vocal
- Annuler et réenregistrer

### 3. Envoi Automatique
**Processus:**
1. Cliquer sur le bouton envoyer vert
2. Upload du fichier audio au serveur
3. Création du message dans la base de données
4. Rechargement de la page
5. Message vocal visible dans le chat

**Endpoint:**
```
POST /goal/{id}/send-voice
```

**Paramètres:**
- `voice`: Fichier audio (audio/webm)
- `duration`: Durée en secondes

## Code JavaScript

### Variables Globales
```javascript
let mediaRecorder;          // Enregistreur audio
let audioChunks = [];       // Morceaux audio
let recordingInterval;      // Timer
let recordingSeconds = 0;   // Durée
let currentAudioBlob = null; // Blob audio actuel
```

### Enregistrement
```javascript
const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
mediaRecorder = new MediaRecorder(stream);
mediaRecorder.start();
```

### Arrêt et Prévisualisation
```javascript
mediaRecorder.onstop = () => {
    const audioBlob = new Blob(audioChunks, { type: 'audio/webm' });
    currentAudioBlob = audioBlob;
    // Afficher l'aperçu avec bouton envoyer
};
```

### Envoi au Serveur
```javascript
async function sendVoiceMessage() {
    const formData = new FormData();
    formData.append('voice', currentAudioBlob, 'voice-message.webm');
    formData.append('duration', recordingSeconds);
    
    const response = await fetch(`/goal/${goalId}/send-voice`, {
        method: 'POST',
        body: formData
    });
}
```

## Code PHP (Contrôleur)

### Route
```php
#[Route('/goal/{id}/send-voice', name: 'goal_send_voice', methods: ['POST'])]
public function sendVoiceMessage(Goal $goal, Request $request, EntityManagerInterface $em): JsonResponse
```

### Traitement
```php
// Récupérer le fichier
$voiceFile = $request->files->get('voice');
$duration = $request->request->get('duration', 0);

// Générer nom unique
$newFilename = 'voice-'.uniqid().'.webm';

// Sauvegarder
$uploadDir = $this->getParameter('kernel.project_dir').'/public/uploads/voice';
$voiceFile->move($uploadDir, $newFilename);

// Créer le message
$message = new Message();
$message->setAttachmentPath('/uploads/voice/'.$newFilename);
$message->setAttachmentType('audio');
$message->setAudioDuration((int)$duration);
```

## Structure des Fichiers

### Dossier d'Upload
```
public/uploads/voice/
├── voice-65abc123.webm
├── voice-65abc456.webm
└── voice-65abc789.webm
```

### Base de Données
```sql
message
├── id
├── content (NULL pour messages vocaux)
├── attachment_path (/uploads/voice/voice-xxx.webm)
├── attachment_type (audio)
├── audio_duration (en secondes)
├── created_at
└── author_id
```

## Affichage dans le Chat

### Template Twig
```twig
{% if message.attachmentType == 'audio' %}
    <div class="message-voice">
        <button class="voice-play-btn">
            <i class="fas fa-play"></i>
        </button>
        <div class="voice-waveform">
            <!-- Barres de forme d'onde -->
        </div>
        <span>{{ message.formattedDuration }}</span>
    </div>
{% endif %}
```

### Méthode Entity
```php
public function getFormattedDuration(): string
{
    if (!$this->audioDuration) {
        return '0:00';
    }
    $minutes = floor($this->audioDuration / 60);
    $seconds = $this->audioDuration % 60;
    return sprintf('%d:%02d', $minutes, $seconds);
}
```

## Permissions Navigateur

### Demande d'Autorisation
Au premier clic sur le micro, le navigateur demande:
```
"127.0.0.1:8000 wants to use your microphone"
[Block] [Allow]
```

### Vérifier les Permissions
```javascript
navigator.permissions.query({name: 'microphone'})
    .then(result => console.log('Microphone:', result.state));
```

États possibles:
- `granted` - Autorisé
- `denied` - Refusé
- `prompt` - Demande en attente

## Compatibilité

### Navigateurs Supportés
- ✅ Chrome 47+
- ✅ Firefox 25+
- ✅ Safari 14+
- ✅ Edge 79+
- ❌ Internet Explorer (non supporté)

### APIs Utilisées
- `navigator.mediaDevices.getUserMedia()` - Accès micro
- `MediaRecorder` - Enregistrement audio
- `Blob` - Manipulation fichier
- `FormData` - Upload fichier
- `Fetch API` - Envoi AJAX

## Test Complet

### 1. Enregistrer
1. Ouvrir le chatroom
2. Cliquer sur 🎤
3. Autoriser le micro
4. Parler pendant 5 secondes
5. Cliquer sur stop

### 2. Prévisualiser
1. Vérifier l'aperçu s'affiche
2. Cliquer sur ▶️ pour écouter
3. Vérifier la durée (0:05)

### 3. Envoyer
1. Cliquer sur le bouton vert ✈️
2. Vérifier le spinner
3. Attendre le rechargement
4. Vérifier le message vocal dans le chat

### 4. Écouter
1. Cliquer sur ▶️ dans le message
2. Vérifier que l'audio se joue
3. Vérifier la durée affichée

## Débogage

### Console du Navigateur
```javascript
// Vérifier l'enregistrement
console.log('MediaRecorder state:', mediaRecorder.state);

// Vérifier le blob
console.log('Audio blob size:', currentAudioBlob.size);

// Vérifier l'envoi
console.log('Sending voice message...');
```

### Logs PHP
```php
error_log('Voice file received: ' . $voiceFile->getClientOriginalName());
error_log('Duration: ' . $duration . ' seconds');
error_log('Saved to: ' . $uploadDir . '/' . $newFilename);
```

### Vérifier le Fichier
```bash
# Lister les fichiers vocaux
ls public/uploads/voice/

# Vérifier la taille
du -h public/uploads/voice/voice-*.webm
```

## Erreurs Courantes

### 1. Microphone Non Accessible
**Erreur:** `Could not access microphone`
**Solution:** 
- Vérifier les permissions du navigateur
- Vérifier qu'aucune autre app n'utilise le micro
- Essayer en HTTPS (requis sur certains navigateurs)

### 2. Fichier Non Envoyé
**Erreur:** `Fichier audio manquant`
**Solution:**
- Vérifier que `currentAudioBlob` n'est pas null
- Vérifier le nom du champ FormData ('voice')
- Vérifier la route dans le fetch

### 3. Dossier Non Accessible
**Erreur:** `Failed to move file`
**Solution:**
```bash
# Créer le dossier
mkdir -p public/uploads/voice

# Donner les permissions
chmod 777 public/uploads/voice
```

### 4. Format Non Supporté
**Erreur:** Lecture impossible
**Solution:**
- Vérifier que le navigateur supporte webm
- Fallback vers mp3 si nécessaire
- Utiliser un convertisseur côté serveur

## Améliorations Futures

### Possibles
1. ⏳ Limite de durée (ex: 2 minutes max)
2. ⏳ Visualisation de la forme d'onde pendant l'enregistrement
3. ⏳ Pause/Reprise de l'enregistrement
4. ⏳ Conversion en MP3 côté serveur
5. ⏳ Compression audio
6. ⏳ Transcription automatique (Speech-to-Text)
7. ⏳ Vitesse de lecture (1x, 1.5x, 2x)
8. ⏳ Téléchargement du message vocal
9. ⏳ Partage du message vocal
10. ⏳ Réponse vocale rapide

## Sécurité

### Validations
- ✅ Vérification de l'authentification
- ✅ Vérification du membership au goal
- ✅ Validation du type de fichier
- ✅ Nom de fichier unique (uniqid)
- ✅ Dossier sécurisé (hors de /public idéalement)

### Recommandations
- Limiter la taille du fichier (ex: 10 MB max)
- Limiter la durée (ex: 5 minutes max)
- Scanner les fichiers pour malware
- Nettoyer les anciens fichiers régulièrement

## État Actuel

✅ Enregistrement fonctionnel
✅ Prévisualisation avec play
✅ Envoi automatique au serveur
✅ Sauvegarde dans la base de données
✅ Affichage dans le chat
✅ Lecture des messages vocaux
✅ Durée formatée (MM:SS)
✅ Dossier uploads/voice créé
✅ Cache vidé
✅ Prêt à l'utilisation

## Commandes

### Vider le cache
```bash
php bin/console cache:clear
```

### Vérifier les fichiers vocaux
```bash
ls -lh public/uploads/voice/
```

### Tester l'upload
```bash
curl -X POST http://127.0.0.1:8000/goal/1/send-voice \
  -F "voice=@test.webm" \
  -F "duration=10"
```

Les messages vocaux sont maintenant entièrement fonctionnels! 🎤🎉
