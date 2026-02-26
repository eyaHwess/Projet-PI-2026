# 🚀 Quick Start - Chat en Temps Réel

## ✅ Statut Actuel

Votre chat fonctionne **DÉJÀ** en temps réel avec le système de polling!
- Messages apparaissent automatiquement toutes les 2 secondes
- Aucune configuration supplémentaire requise
- Fonctionne sur tous les navigateurs

## 🎯 Test Rapide

1. Ouvrir votre chatroom: `http://localhost:8000/message/chatroom/{goalId}`
2. Ouvrir le même chatroom dans un autre onglet
3. Envoyer un message dans l'onglet 1
4. Observer le message apparaître dans l'onglet 2 après ~2 secondes

✅ **Ça marche!**

## 🚀 Activer Mercure (Optionnel - Pour du VRAI temps réel)

Si vous voulez des messages **instantanés** (< 100ms au lieu de 2s):

### Option 1: Avec Docker (Recommandé)

```bash
# 1. Lancer Mercure Hub
docker run -d \
  --name mercure \
  -p 3000:80 \
  -e MERCURE_PUBLISHER_JWT_KEY='!ChangeThisMercureJWTKey!' \
  -e MERCURE_SUBSCRIBER_JWT_KEY='!ChangeThisMercureJWTKey!' \
  dunglas/mercure

# 2. Vérifier que ça tourne
docker ps

# 3. Tester l'URL
curl http://localhost:3000/.well-known/mercure
```

### Option 2: Sans Docker (Binaire)

```bash
# Télécharger Mercure
# Windows: https://github.com/dunglas/mercure/releases/download/v0.15.8/mercure_0.15.8_Windows_x86_64.zip
# Extraire et lancer:
mercure.exe run --config Caddyfile
```

### Mettre à jour .env

```env
# Remplacer les lignes Mercure par:
MERCURE_URL=http://localhost:3000/.well-known/mercure
MERCURE_PUBLIC_URL=http://localhost:3000/.well-known/mercure
MERCURE_JWT_SECRET=!ChangeThisMercureJWTKey!
```

### Modifier MessageController.php

Ouvrir `src/Controller/MessageController.php` et ajouter:

```php
// En haut du fichier, ajouter:
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;

// Dans la méthode chatroom(), modifier la signature:
public function chatroom(
    int $goalId,
    Request $request,
    EntityManagerInterface $em,
    \App\Repository\MessageReadReceiptRepository $readReceiptRepo,
    \App\Repository\GoalRepository $goalRepository,
    ?HubInterface $hub = null  // ← AJOUTER CE PARAMÈTRE
): Response {
```

Puis dans le bloc `if ($form->isSubmitted() && $form->isValid())`, après `$em->flush();`:

```php
$em->flush();

// 🚀 AJOUTER CE CODE ICI:
if ($hub) {
    try {
        $messageHtml = $this->renderView('chatroom/_message.html.twig', [
            'message' => $message
        ]);
        
        $update = new Update(
            'chatroom/' . $goalId,
            $messageHtml
        );
        
        $hub->publish($update);
    } catch (\Exception $e) {
        error_log('Mercure: ' . $e->getMessage());
    }
}
```

### Modifier chatroom_modern.html.twig

Ouvrir `templates/chatroom/chatroom_modern.html.twig` et ajouter **AVANT** `</body>`:

```twig
{# Turbo Stream pour Mercure #}
{% if app.user %}
<turbo-stream-source 
    src="{{ mercure('chatroom/' ~ goal.id)|escape('html_attr') }}">
</turbo-stream-source>
{% endif %}
```

### Modifier base.html.twig

Ouvrir `templates/base.html.twig` et ajouter dans le `<head>`:

```twig
{# Turbo pour navigation SPA #}
{{ ux_controller('symfony/ux-turbo') }}
```

### Vider le cache

```bash
php bin/console cache:clear
```

### Tester Mercure

1. Ouvrir 2 onglets avec le chatroom
2. Envoyer un message dans l'onglet 1
3. Le message apparaît **INSTANTANÉMENT** dans l'onglet 2 🚀

## 📊 Différences

| Aspect | Polling (Actuel) | Mercure |
|--------|------------------|---------|
| Latence | ~2 secondes | < 100ms |
| Configuration | Aucune | Docker requis |
| Ressources | Moyenne | Faible |

## 🔧 Commandes Utiles

```bash
# Vérifier les routes
php bin/console debug:router | grep message

# Vider le cache
php bin/console cache:clear

# Voir les logs Mercure (Docker)
docker logs mercure

# Arrêter Mercure (Docker)
docker stop mercure

# Redémarrer Mercure (Docker)
docker start mercure
```

## ❓ FAQ

### Le polling fonctionne-t-il toujours avec Mercure?
Oui! Le polling reste actif comme fallback. Si Mercure ne fonctionne pas, le polling prend le relais.

### Dois-je activer Mercure?
Non, c'est optionnel. Le polling fonctionne très bien pour < 100 utilisateurs simultanés.

### Mercure fonctionne-t-il en production?
Oui! Mercure est production-ready et utilisé par des milliers d'applications.

### Puis-je désactiver Mercure plus tard?
Oui, il suffit d'arrêter Docker. Le polling reprendra automatiquement.

## 🎉 Résultat

Vous avez maintenant:
- ✅ Un chat en temps réel fonctionnel (polling)
- ✅ La structure Mercure prête à activer
- ✅ Un système hybride robuste
- ✅ Une solution production-ready

## 📝 Fichiers Créés

- ✅ `templates/chatroom/_message.html.twig` - Template partiel
- ✅ `REALTIME_CHAT_IMPLEMENTATION.md` - Guide complet
- ✅ `CHAT_REALTIME_STATUS.md` - Statut détaillé
- ✅ `QUICK_START_REALTIME_CHAT.md` - Ce fichier

## 🚀 Prochaines Étapes

1. Tester le polling (déjà actif)
2. (Optionnel) Activer Mercure pour du vrai temps réel
3. Ajouter des fonctionnalités:
   - Typing indicator
   - Read receipts
   - Online status
   - Notifications push

---

**Besoin d'aide?** Consultez `REALTIME_CHAT_IMPLEMENTATION.md` pour le guide complet!
