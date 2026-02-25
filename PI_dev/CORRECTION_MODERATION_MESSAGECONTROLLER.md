# 🔧 Correction de la Modération dans MessageController

## ❌ Problème Identifié

Le message toxique "you are a fucking asshole" était publié au lieu d'être bloqué.

### Cause du Problème

Il existe **DEUX contrôleurs différents** qui gèrent les chatrooms:

1. **ChatroomController** (`/chatroom/{id}`)
   - Template: `chatroom/chatroom.html.twig` (ancien)
   - ✅ Modération intégrée

2. **MessageController** (`/chatroom/{goalId}`)
   - Template: `chatroom/chatroom_modern.html.twig` (moderne)
   - ❌ Modération MANQUANTE

L'utilisateur utilisait le template moderne, donc la modération n'était pas appliquée.

---

## ✅ Solution Appliquée

### 1. Injection du Service de Modération

**Fichier**: `src/Controller/MessageController.php`

```php
#[Route('/message')]
final class MessageController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private \App\Service\ModerationService $moderationService  // ✅ AJOUTÉ
    ) {}
}
```

### 2. Intégration de la Modération dans la Méthode `chatroom()`

**Emplacement**: Avant `$em->persist($message)`

```php
// Modération du contenu avant enregistrement
$content = $message->getContent();
if ($content && trim($content) !== '') {
    $moderationResult = $this->moderationService->analyzeMessage($content);
    
    // Appliquer les résultats de modération
    $message->setIsToxic($moderationResult['isToxic']);
    $message->setIsSpam($moderationResult['isSpam']);
    $message->setToxicityScore($moderationResult['toxicityScore']);
    $message->setSpamScore($moderationResult['spamScore']);
    $message->setModerationStatus($moderationResult['moderationStatus']);
    $message->setModerationReason($moderationResult['moderationReason']);

    // Si le message est bloqué, ne pas l'enregistrer
    if ($moderationResult['moderationStatus'] === 'blocked') {
        if ($request->isXmlHttpRequest() || $request->headers->get('X-Requested-With') === 'XMLHttpRequest') {
            return new JsonResponse([
                'success' => false,
                'error' => $moderationResult['moderationReason']
            ], 403);
        }
        $this->addFlash('error', $moderationResult['moderationReason']);
        return $this->redirectToRoute('message_chatroom', ['goalId' => $goalId]);
    }

    // Si le message est spam, afficher un avertissement
    if ($moderationResult['moderationStatus'] === 'hidden') {
        if ($request->isXmlHttpRequest() || $request->headers->get('X-Requested-With') === 'XMLHttpRequest') {
            return new JsonResponse([
                'success' => false,
                'error' => 'Votre message a été marqué comme spam et sera masqué pour les autres utilisateurs.'
            ], 403);
        }
        $this->addFlash('warning', 'Votre message a été marqué comme spam et sera masqué pour les autres utilisateurs.');
    }
}
```

---

## 🧪 Tests de Vérification

### Test Terminal

```bash
php test_moderation_messagecontroller.php
```

**Résultats**:
- ✅ Message toxique "you are a fucking asshole" → BLOQUÉ (score: 1.0)
- ✅ Message normal "Hello, how are you today?" → APPROUVÉ (score: 0.0)
- ✅ Message toxique français "tu es un connard" → BLOQUÉ (score: 0.8)
- ✅ Message spam avec URL → MASQUÉ (score: 0.8)

### Test dans le Navigateur

1. Ouvrir le chatroom moderne: `/message/chatroom/{goalId}`
2. Essayer d'envoyer: "you are a fucking asshole"
3. **Résultat attendu**: 
   - ❌ Message NON publié
   - 🔴 Flash message rouge: "Ce message viole les règles de la communauté"
   - ↩️ Redirection vers le chatroom

---

## 📊 Comportement de la Modération

### Messages Toxiques (score ≥ 0.6)
- **Statut**: `blocked`
- **Action**: Message NON enregistré en base de données
- **Feedback**: Message d'erreur rouge
- **Exemple**: "fucking", "asshole", "connard", etc.

### Messages Spam (score ≥ 0.5)
- **Statut**: `hidden`
- **Action**: Message enregistré mais masqué
- **Feedback**: Message d'avertissement orange
- **Exemple**: URLs multiples, texte répétitif, etc.

### Messages Normaux
- **Statut**: `approved`
- **Action**: Message publié normalement
- **Feedback**: Aucun
- **Exemple**: Conversations normales

---

## 🔍 Différences entre les Deux Contrôleurs

| Aspect | ChatroomController | MessageController |
|--------|-------------------|-------------------|
| Route | `/chatroom/{id}` | `/message/chatroom/{goalId}` |
| Template | `chatroom.html.twig` | `chatroom_modern.html.twig` |
| Modération | ✅ Intégrée depuis le début | ✅ Intégrée maintenant |
| Utilisation | Ancien système | Système actuel |

---

## 📝 Fichiers Modifiés

1. **src/Controller/MessageController.php**
   - Ajout de `ModerationService` dans le constructeur
   - Ajout de la logique de modération dans `chatroom()`

2. **test_moderation_messagecontroller.php** (nouveau)
   - Script de test pour vérifier la modération

3. **CORRECTION_MODERATION_MESSAGECONTROLLER.md** (ce fichier)
   - Documentation de la correction

---

## ✅ Vérification Finale

```bash
# 1. Nettoyer le cache
php bin/console cache:clear

# 2. Tester la modération
php test_moderation_messagecontroller.php

# 3. Vérifier qu'il n'y a pas d'erreurs
# (Aucune erreur de diagnostic trouvée)
```

---

## 🎯 Résultat

La modération fonctionne maintenant correctement dans les **DEUX** contrôleurs:
- ✅ ChatroomController (ancien template)
- ✅ MessageController (template moderne)

Les messages toxiques comme "you are a fucking asshole" sont maintenant **correctement bloqués** et ne sont **plus publiés** dans le chatroom moderne.
