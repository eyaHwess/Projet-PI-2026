# 🔗 Firebase Notifications - Guide d'Intégration

## 🎯 Objectif

Intégrer les notifications Firebase dans les contrôleurs pour envoyer des notifications automatiques.

---

## 📋 Intégrations à Faire

### 1. Nouveau Message (MessageController)
### 2. Nouveau Membre (GoalController)
### 3. Mentions @user (MessageController)

---

## 🔧 Intégration 1: Nouveau Message

### Fichier: `src/Controller/MessageController.php`

**Ajouter** l'injection du service dans le constructeur ou la méthode:

```php
use App\Service\FirebaseNotificationService;
use App\Service\MentionDetector;

// Dans la méthode send() ou create()
public function send(
    Request $request,
    EntityManagerInterface $em,
    FirebaseNotificationService $firebaseService,  // ← AJOUTER
    MentionDetector $mentionDetector                // ← AJOUTER
): Response {
    // ... code existant ...
    
    // Après la création et la sauvegarde du message
    $em->persist($message);
    $em->flush();
    
    // ✨ AJOUTER: Envoyer notification nouveau message
    try {
        $firebaseService->notifyNewMessage($message);
    } catch (\Exception $e) {
        // Logger l'erreur mais ne pas bloquer l'envoi du message
        $this->logger->error('Erreur notification Firebase', [
            'error' => $e->getMessage()
        ]);
    }
    
    // ✨ AJOUTER: Détecter et notifier les mentions
    if ($message->getContent()) {
        try {
            $mentions = $mentionDetector->detectMentions($message->getContent());
            foreach ($mentions as $mentionedUser) {
                $firebaseService->notifyMention($message, $mentionedUser);
            }
        } catch (\Exception $e) {
            $this->logger->error('Erreur détection mentions', [
                'error' => $e->getMessage()
            ]);
        }
    }
    
    // ... reste du code ...
}
```

---

## 🔧 Intégration 2: Nouveau Membre

### Fichier: `src/Controller/GoalController.php`

**Trouver** la méthode qui ajoute un membre (ex: `addMember`, `join`, etc.)

```php
use App\Service\FirebaseNotificationService;

public function addMember(
    Goal $goal,
    User $newMember,
    EntityManagerInterface $em,
    FirebaseNotificationService $firebaseService  // ← AJOUTER
): Response {
    // ... code existant pour ajouter le membre ...
    
    $em->persist($participation);
    $em->flush();
    
    // ✨ AJOUTER: Notifier les membres existants
    try {
        $firebaseService->notifyNewMember($goal, $newMember);
    } catch (\Exception $e) {
        $this->logger->error('Erreur notification nouveau membre', [
            'error' => $e->getMessage()
        ]);
    }
    
    // ... reste du code ...
}
```

---

## 🎨 Amélioration: Afficher les Mentions

### Fichier: `templates/chatroom/chatroom.html.twig`

**Dans la boucle des messages**, remplacer l'affichage du contenu:

```twig
{# Avant #}
<div class="message-text">
    {{ message.content }}
</div>

{# Après #}
<div class="message-text">
    {{ message.content|mention_links|raw }}
</div>
```

**Créer** un Twig Extension pour `mention_links`:

```php
// src/Twig/MentionExtension.php
namespace App\Twig;

use App\Service\MentionDetector;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class MentionExtension extends AbstractExtension
{
    public function __construct(
        private MentionDetector $mentionDetector
    ) {
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('mention_links', [$this, 'replaceMentions'], ['is_safe' => ['html']]),
        ];
    }

    public function replaceMentions(string $content): string
    {
        return $this->mentionDetector->replaceMentionsWithLinks($content);
    }
}
```

---

## 🎨 CSS pour les Mentions

**Ajouter** dans `templates/chatroom/chatroom.html.twig`:

```css
/* Mentions */
.mention {
    background: rgba(139, 157, 195, 0.15);
    color: #8b9dc3;
    padding: 2px 6px;
    border-radius: 4px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.mention:hover {
    background: rgba(139, 157, 195, 0.25);
    text-decoration: underline;
}

/* Alerte de mention */
.mention-alert {
    position: fixed;
    top: 80px;
    right: 20px;
    z-index: 10000;
    animation: slideInRight 0.3s ease-out;
}

.mention-alert-content {
    background: linear-gradient(135deg, #8b9dc3 0%, #a8b5d1 100%);
    color: white;
    padding: 16px 20px;
    border-radius: 12px;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
    display: flex;
    align-items: center;
    gap: 12px;
    cursor: pointer;
    transition: all 0.2s;
}

.mention-alert-content:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 32px rgba(0, 0, 0, 0.3);
}

.mention-alert-content i {
    font-size: 24px;
}

.mention-alert-close {
    background: rgba(255, 255, 255, 0.2);
    border: none;
    color: white;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-left: auto;
}

@keyframes slideInRight {
    from {
        opacity: 0;
        transform: translateX(100px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

/* Prompt de permission */
.notification-permission-prompt {
    position: fixed;
    bottom: 20px;
    right: 20px;
    z-index: 10000;
    animation: slideInUp 0.3s ease-out;
}

.notification-prompt-content {
    background: white;
    padding: 20px;
    border-radius: 16px;
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15);
    max-width: 400px;
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.notification-prompt-icon {
    font-size: 48px;
    text-align: center;
}

.notification-prompt-text h3 {
    margin: 0 0 8px 0;
    font-size: 18px;
    color: #1f2937;
}

.notification-prompt-text p {
    margin: 0;
    font-size: 14px;
    color: #6b7280;
}

.notification-prompt-actions {
    display: flex;
    gap: 12px;
}

.btn-enable-notifications,
.btn-dismiss-notifications {
    flex: 1;
    padding: 10px 20px;
    border-radius: 10px;
    border: none;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-enable-notifications {
    background: linear-gradient(135deg, #8b9dc3 0%, #a8b5d1 100%);
    color: white;
}

.btn-enable-notifications:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(139, 157, 195, 0.3);
}

.btn-dismiss-notifications {
    background: #f3f4f6;
    color: #6b7280;
}

.btn-dismiss-notifications:hover {
    background: #e5e7eb;
}

/* Toast notifications */
.toast {
    position: fixed;
    bottom: 20px;
    left: 50%;
    transform: translateX(-50%) translateY(100px);
    background: white;
    padding: 16px 24px;
    border-radius: 12px;
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
    opacity: 0;
    transition: all 0.3s;
    z-index: 10000;
}

.toast.show {
    opacity: 1;
    transform: translateX(-50%) translateY(0);
}

.toast-success {
    border-left: 4px solid #10b981;
}

.toast-error {
    border-left: 4px solid #ef4444;
}
```

---

## 🧪 Tests

### Test 1: Nouveau Message
1. Ouvrir 2 navigateurs (ou 2 onglets en navigation privée)
2. Se connecter avec 2 utilisateurs différents
3. Rejoindre le même chatroom
4. Envoyer un message depuis le premier
5. Vérifier la notification sur le second

### Test 2: Mention
1. Envoyer un message avec "@marie"
2. Vérifier que Marie reçoit une notification spéciale
3. Cliquer sur la notification
4. Vérifier la redirection vers le message

### Test 3: Nouveau Membre
1. Ajouter un nouveau membre à un goal
2. Vérifier que les membres existants reçoivent une notification

---

## 📊 Résultat Attendu

✅ Notifications push en temps réel  
✅ Détection automatique des @mentions  
✅ Notifications pour nouveaux messages  
✅ Notifications pour nouveaux membres  
✅ Interface utilisateur intuitive  

---

**Prêt à tester!** 🚀
