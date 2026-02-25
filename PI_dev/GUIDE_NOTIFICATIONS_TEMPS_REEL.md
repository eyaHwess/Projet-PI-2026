# 🔔 Guide : Notifications en Temps Réel

## ✅ CE QUI EXISTE DÉJÀ

Le système de notifications est déjà fonctionnel :
- ✅ `NotificationService` et `NotificationManager` créés
- ✅ Notifications créées quand le coach accepte/refuse une demande
- ✅ Entité `Notification` en base de données
- ✅ Contrôleur `NotificationController` pour afficher les notifications

## 🎯 CE QU'IL FAUT AJOUTER

Pour avoir les notifications en temps réel, il faut :
1. Créer une route API pour compter les notifications non lues
2. Créer un script JavaScript qui vérifie les nouvelles notifications
3. Ajouter un badge dans la navbar
4. Afficher une alerte quand une nouvelle notification arrive

---

## 📋 ÉTAPE 1 : Ajouter la Route API Count

**Fichier** : `src/Controller/NotificationController.php`

**Action** : Ajouter une méthode pour retourner le nombre de notifications non lues

**Code à ajouter** :
```php
#[Route('/api/notifications/unread/count', name: 'app_notification_unread_count', methods: ['GET'])]
public function getUnreadCount(): JsonResponse
{
    $user = $this->getUser();
    
    if (!$user) {
        return new JsonResponse(['count' => 0]);
    }
    
    $count = $this->notificationRepository->countUnreadForUser($user);
    
    return new JsonResponse(['count' => $count]);
}
```

---

## 📋 ÉTAPE 2 : Créer le Script JavaScript

**Fichier** : `public/js/notifications-realtime.js`

**Créer ce fichier avec le contenu suivant** :

```javascript
// Système de notifications en temps réel
(function() {
    'use strict';
    
    // Configuration
    const POLL_INTERVAL = 5000; // Vérifier toutes les 5 secondes
    const API_URL = '/api/notifications/unread/count';
    
    let lastCount = 0;
    let isFirstCheck = true;
    
    // Fonction pour récupérer le nombre de notifications
    function fetchNotificationCount() {
        fetch(API_URL)
            .then(response => response.json())
            .then(data => {
                const count = data.count || 0;
                updateBadge(count);
                
                // Si le nombre a augmenté, afficher une alerte
                if (!isFirstCheck && count > lastCount) {
                    showNotificationAlert(count - lastCount);
                }
                
                lastCount = count;
                isFirstCheck = false;
            })
            .catch(error => {
                console.error('Erreur lors de la récupération des notifications:', error);
            });
    }
    
    // Fonction pour mettre à jour le badge
    function updateBadge(count) {
        const badge = document.getElementById('notification-badge');
        if (badge) {
            if (count > 0) {
                badge.textContent = count > 99 ? '99+' : count;
                badge.style.display = 'inline-block';
            } else {
                badge.style.display = 'none';
            }
        }
    }
    
    // Fonction pour afficher une alerte
    function showNotificationAlert(newCount) {
        // Créer une notification toast
        const toast = document.createElement('div');
        toast.className = 'notification-toast';
        toast.innerHTML = `
            <div class="notification-toast-content">
                <i class="bi bi-bell-fill"></i>
                <span>Vous avez ${newCount} nouvelle(s) notification(s)</span>
            </div>
        `;
        
        document.body.appendChild(toast);
        
        // Afficher avec animation
        setTimeout(() => toast.classList.add('show'), 100);
        
        // Masquer après 5 secondes
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, 5000);
        
        // Jouer un son (optionnel)
        playNotificationSound();
    }
    
    // Fonction pour jouer un son
    function playNotificationSound() {
        // Créer un son simple avec Web Audio API
        try {
            const audioContext = new (window.AudioContext || window.webkitAudioContext)();
            const oscillator = audioContext.createOscillator();
            const gainNode = audioContext.createGain();
            
            oscillator.connect(gainNode);
            gainNode.connect(audioContext.destination);
            
            oscillator.frequency.value = 800;
            oscillator.type = 'sine';
            
            gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
            gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.5);
            
            oscillator.start(audioContext.currentTime);
            oscillator.stop(audioContext.currentTime + 0.5);
        } catch (e) {
            // Ignorer les erreurs de son
        }
    }
    
    // Démarrer le polling
    function startPolling() {
        // Première vérification immédiate
        fetchNotificationCount();
        
        // Puis vérifier régulièrement
        setInterval(fetchNotificationCount, POLL_INTERVAL);
    }
    
    // Démarrer quand la page est chargée
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', startPolling);
    } else {
        startPolling();
    }
})();
```

---

## 📋 ÉTAPE 3 : Créer le CSS pour les Notifications

**Fichier** : `public/styles/notifications-realtime.css`

**Créer ce fichier avec le contenu suivant** :

```css
/* Badge de notification */
#notification-badge {
    position: absolute;
    top: -8px;
    right: -8px;
    background: #dc3545;
    color: white;
    border-radius: 50%;
    padding: 2px 6px;
    font-size: 11px;
    font-weight: bold;
    min-width: 18px;
    text-align: center;
    display: none;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% {
        transform: scale(1);
    }
    50% {
        transform: scale(1.1);
    }
}

/* Toast de notification */
.notification-toast {
    position: fixed;
    top: 80px;
    right: 20px;
    background: white;
    border-left: 4px solid #28a745;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    border-radius: 8px;
    padding: 15px 20px;
    z-index: 9999;
    opacity: 0;
    transform: translateX(400px);
    transition: all 0.3s ease;
}

.notification-toast.show {
    opacity: 1;
    transform: translateX(0);
}

.notification-toast-content {
    display: flex;
    align-items: center;
    gap: 12px;
}

.notification-toast-content i {
    font-size: 24px;
    color: #28a745;
}

.notification-toast-content span {
    font-size: 14px;
    color: #333;
    font-weight: 500;
}
```

---

## 📋 ÉTAPE 4 : Ajouter le Badge dans la Navbar

**Fichier** : `templates/base.html.twig`

**Action** : Ajouter un lien vers les notifications avec un badge

**Code à ajouter dans la navbar** :

```twig
<li class="nav-item" style="position: relative;">
    <a class="nav-link" href="{{ path('app_notification_index') }}">
        <i class="bi bi-bell"></i>
        <span id="notification-badge">0</span>
    </a>
</li>
```

---

## 📋 ÉTAPE 5 : Inclure les Scripts dans base.html.twig

**Fichier** : `templates/base.html.twig`

**Action** : Ajouter les scripts et CSS avant la fermeture de `</body>`

**Code à ajouter** :

```twig
{# CSS des notifications #}
<link rel="stylesheet" href="{{ asset('styles/notifications-realtime.css') }}">

{# JavaScript des notifications #}
<script src="{{ asset('js/notifications-realtime.js') }}"></script>
```

---

## 📋 ÉTAPE 6 : Vérifier la Route dans NotificationRepository

**Fichier** : `src/NotificationBundle/Repository/NotificationRepository.php`

**Action** : S'assurer qu'il existe une méthode `countUnreadForUser`

**Code à ajouter si elle n'existe pas** :

```php
public function countUnreadForUser(User $user): int
{
    return $this->createQueryBuilder('n')
        ->select('COUNT(n.id)')
        ->where('n.user = :user')
        ->andWhere('n.isRead = false')
        ->setParameter('user', $user)
        ->getQuery()
        ->getSingleScalarResult();
}
```

---

## 🧪 ÉTAPE 7 : TESTER LE SYSTÈME

### Test 1 : Vérifier l'API
1. Se connecter en tant qu'utilisateur
2. Ouvrir : `http://127.0.0.1:8000/api/notifications/unread/count`
3. Tu dois voir : `{"count": X}` (X = nombre de notifications non lues)

### Test 2 : Vérifier le Badge
1. Se connecter en tant qu'utilisateur
2. Regarder la navbar
3. Tu dois voir l'icône de cloche avec un badge rouge si tu as des notifications

### Test 3 : Tester en Temps Réel
1. Ouvrir deux onglets
2. Onglet 1 : Se connecter en tant qu'utilisateur (laisser ouvert)
3. Onglet 2 : Se connecter en tant que coach
4. Dans l'onglet 2, accepter une demande de l'utilisateur
5. Retourner sur l'onglet 1
6. Attendre 5 secondes maximum
7. Tu dois voir :
   - Le badge se mettre à jour
   - Une notification toast apparaître en haut à droite
   - Un son jouer (optionnel)

---

## 🎯 RÉSULTAT ATTENDU

Quand le coach accepte une demande :
1. ✅ Notification créée en base de données
2. ✅ L'utilisateur voit le badge se mettre à jour (dans les 5 secondes)
3. ✅ Une alerte toast apparaît en haut à droite
4. ✅ Un son est joué
5. ✅ L'utilisateur peut cliquer sur la cloche pour voir les détails

---

## 🔧 PERSONNALISATION

### Changer l'intervalle de vérification
Dans `notifications-realtime.js`, modifier :
```javascript
const POLL_INTERVAL = 5000; // 5 secondes
```

### Désactiver le son
Dans `notifications-realtime.js`, commenter :
```javascript
// playNotificationSound();
```

### Changer la couleur du badge
Dans `notifications-realtime.css`, modifier :
```css
#notification-badge {
    background: #dc3545; /* Rouge par défaut */
}
```

---

## 🆘 PROBLÈMES COURANTS

### Le badge ne se met pas à jour
1. Vérifier que le script est bien chargé (F12 > Console)
2. Vérifier que l'API retourne bien le count
3. Vérifier que l'ID du badge est bien `notification-badge`

### L'API retourne une erreur
1. Vérifier que la route existe : `php bin/console debug:router | grep notification`
2. Vérifier que la méthode `countUnreadForUser` existe dans le repository

### Le toast n'apparaît pas
1. Vérifier que le CSS est bien chargé
2. Ouvrir F12 > Console pour voir les erreurs JavaScript

---

## ✅ CHECKLIST FINALE

- [ ] Route API `/api/notifications/unread/count` créée
- [ ] Méthode `countUnreadForUser` dans le repository
- [ ] Fichier `notifications-realtime.js` créé
- [ ] Fichier `notifications-realtime.css` créé
- [ ] Badge ajouté dans la navbar
- [ ] Scripts inclus dans `base.html.twig`
- [ ] Test API fonctionne
- [ ] Test badge fonctionne
- [ ] Test temps réel fonctionne

---

**Durée d'implémentation** : 15-20 minutes  
**Difficulté** : Facile  
**Résultat** : Notifications en temps réel fonctionnelles ! 🎉
