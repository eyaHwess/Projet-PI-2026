# 🎯 CRUD des Demandes de Coaching - Interface Coach

## ✅ Fonctionnalités CRUD Complètes

Le coach peut maintenant effectuer toutes les opérations CRUD sur les demandes:

### 📖 READ (Lire)
- ✅ Voir toutes les demandes reçues
- ✅ Lire les messages des utilisateurs
- ✅ Voir les détails (nom, email, date, message)
- ✅ Filtrer par statut (en attente, acceptées, refusées)

### ✏️ UPDATE (Modifier le statut)
Le coach peut changer le statut d'une demande:

#### 1. **ACCEPTER** une demande
- **Route:** `POST /coach/requests/{id}/accept`
- **Bouton:** Vert avec icône ✓
- **Action:** Change le statut à "accepted"
- **Résultat:** Crée automatiquement une session
- **Disponible pour:** Demandes en attente

#### 2. **REFUSER** une demande
- **Route:** `POST /coach/requests/{id}/decline`
- **Bouton:** Rouge avec icône ✗
- **Action:** Change le statut à "declined"
- **Résultat:** L'utilisateur est notifié du refus
- **Disponible pour:** Demandes en attente

#### 3. **METTRE EN ATTENTE** une demande ⭐ NOUVEAU
- **Route:** `POST /coach/requests/{id}/pending`
- **Bouton:** Jaune avec icône ⏰
- **Action:** Remet le statut à "pending"
- **Résultat:** La demande revient dans la liste "En attente"
- **Disponible pour:** Demandes acceptées ou refusées

---

## 🎨 Interface Utilisateur

### Section 1: Demandes en attente (Fond jaune)

```
┌─────────────────────────────────────────────────────────┐
│ 👤 Jean Dupont                                          │
│ 📧 jean.dupont@email.com                                │
│ 📅 11/02/2026 à 09:30                                   │
│                                                          │
│ 💬 Message:                                             │
│ Bonjour, je souhaite perdre 10kg...                     │
│                                                          │
│                    [✅ Accepter]  [❌ Refuser]          │
└─────────────────────────────────────────────────────────┘
```

**Actions disponibles:**
- ✅ Accepter → Passe à "ACCEPTÉE" + Crée une session
- ❌ Refuser → Passe à "REFUSÉE"

---

### Section 2: Toutes les demandes

#### Demande ACCEPTÉE:
```
┌─────────────────────────────────────────────────────────┐
│ 👤 Claire Dubois              [✅ ACCEPTÉE]             │
│ 10/02/2026 à 11:20                                      │
│ Message: Je cherche à améliorer ma souplesse...         │
│                                                          │
│              [📅 Planifier]  [⏰ En attente]            │
└─────────────────────────────────────────────────────────┘
```

**Actions disponibles:**
- 📅 Planifier la session
- ⏰ Mettre en attente → Remet à "EN ATTENTE"

#### Demande REFUSÉE:
```
┌─────────────────────────────────────────────────────────┐
│ 👤 Emma Petit                 [❌ REFUSÉE]              │
│ 08/02/2026 à 10:00                                      │
│ Message: Je cherche un coach pour le crossfit...        │
│                                                          │
│                         [⏰ En attente]                  │
└─────────────────────────────────────────────────────────┘
```

**Actions disponibles:**
- ⏰ Mettre en attente → Remet à "EN ATTENTE"

#### Demande EN ATTENTE:
```
┌─────────────────────────────────────────────────────────┐
│ 👤 Marc Lefebvre              [⏰ EN ATTENTE]           │
│ 11/02/2026 à 16:45                                      │
│ Message: Je veux développer ma masse musculaire...      │
│                                                          │
│                    [✅ Accepter]  [❌ Refuser]          │
└─────────────────────────────────────────────────────────┘
```

**Actions disponibles:**
- ✅ Accepter → Passe à "ACCEPTÉE"
- ❌ Refuser → Passe à "REFUSÉE"

---

## 🔄 Flux des Statuts

```
                    ┌─────────────┐
                    │  PENDING    │
                    │ (En attente)│
                    └──────┬──────┘
                           │
              ┌────────────┼────────────┐
              │                         │
              ▼                         ▼
      ┌──────────────┐          ┌──────────────┐
      │  ACCEPTED    │          │  DECLINED    │
      │  (Acceptée)  │          │  (Refusée)   │
      └──────┬───────┘          └──────┬───────┘
             │                         │
             │    [Mettre en attente]  │
             └────────────┬────────────┘
                          │
                          ▼
                    ┌─────────────┐
                    │  PENDING    │
                    │ (En attente)│
                    └─────────────┘
```

**Transitions possibles:**
- PENDING → ACCEPTED (Accepter)
- PENDING → DECLINED (Refuser)
- ACCEPTED → PENDING (Mettre en attente)
- DECLINED → PENDING (Mettre en attente)

---

## 🛠️ Implémentation Technique

### Routes disponibles:

```php
POST /coach/requests/{id}/accept   // Accepter une demande
POST /coach/requests/{id}/decline  // Refuser une demande
POST /coach/requests/{id}/pending  // Mettre en attente (NOUVEAU)
```

### Contrôleur:

```php
// CoachingRequestController.php

// Accepter
public function accept(CoachingRequest $coachingRequest, Request $request)
{
    $coachingRequest->setStatus(CoachingRequest::STATUS_ACCEPTED);
    // Crée automatiquement une Session
}

// Refuser
public function decline(CoachingRequest $coachingRequest, Request $request)
{
    $coachingRequest->setStatus(CoachingRequest::STATUS_DECLINED);
}

// Mettre en attente (NOUVEAU)
public function setPending(CoachingRequest $coachingRequest, Request $request)
{
    $coachingRequest->setStatus(CoachingRequest::STATUS_PENDING);
    // Réinitialise responded_at à null
}
```

### Template:

```twig
{# Pour les demandes EN ATTENTE #}
<button class="btn btn-success">✅ Accepter</button>
<button class="btn btn-outline-danger">❌ Refuser</button>

{# Pour les demandes ACCEPTÉES ou REFUSÉES #}
<button class="btn btn-warning">⏰ Mettre en attente</button>
```

---

## 🎯 Cas d'utilisation

### Scénario 1: Accepter une demande
```
1. Coach voit une nouvelle demande
2. Lit le message de l'utilisateur
3. Clique sur "Accepter"
4. Statut passe à "ACCEPTÉE"
5. Une session est créée automatiquement
6. Coach peut maintenant planifier la session
```

### Scénario 2: Refuser une demande
```
1. Coach voit une demande
2. Décide qu'il ne peut pas accepter
3. Clique sur "Refuser"
4. Statut passe à "REFUSÉE"
5. L'utilisateur est notifié
```

### Scénario 3: Reconsidérer une décision ⭐ NOUVEAU
```
1. Coach a refusé une demande par erreur
2. Voit la demande dans "Toutes les demandes"
3. Clique sur "Mettre en attente"
4. La demande revient dans "En attente"
5. Coach peut maintenant l'accepter
```

### Scénario 4: Reporter une décision ⭐ NOUVEAU
```
1. Coach a accepté une demande
2. Finalement, il veut y réfléchir encore
3. Clique sur "Mettre en attente"
4. La demande revient en attente
5. La session créée reste disponible
```

---

## 🔒 Sécurité

### Protection CSRF
Chaque action nécessite un token CSRF unique:
```twig
<input type="hidden" name="_token" value="{{ csrf_token('accept-request' ~ req.id) }}">
<input type="hidden" name="_token" value="{{ csrf_token('decline-request' ~ req.id) }}">
<input type="hidden" name="_token" value="{{ csrf_token('pending-request' ~ req.id) }}">
```

### Vérifications
- ✅ Le coach doit être authentifié
- ✅ Le coach doit être le destinataire de la demande
- ✅ Token CSRF valide requis
- ✅ Requêtes AJAX pour une meilleure UX

---

## 🚀 Tester maintenant

### Version DÉMO (sans authentification):
```
http://localhost:8000/demo/coach/requests
```

### Version RÉELLE (avec base de données):
```
http://localhost:8000/coach/requests
```

**Testez les 3 actions:**
1. Accepter une demande en attente
2. Refuser une demande en attente
3. Remettre en attente une demande acceptée/refusée

---

## 📊 Résumé des Boutons

| Statut actuel | Boutons disponibles | Actions |
|--------------|---------------------|---------|
| EN ATTENTE | ✅ Accepter, ❌ Refuser | Change le statut |
| ACCEPTÉE | 📅 Planifier, ⏰ En attente | Planifie ou remet en attente |
| REFUSÉE | ⏰ En attente | Remet en attente |

---

## ✨ Avantages

1. **Flexibilité:** Le coach peut changer d'avis
2. **Gestion d'erreurs:** Possibilité de corriger une erreur
3. **Organisation:** Reporter une décision à plus tard
4. **Traçabilité:** Historique complet des changements de statut
5. **UX améliorée:** Interface claire avec boutons contextuels

Le système CRUD est maintenant complet! 🎉
