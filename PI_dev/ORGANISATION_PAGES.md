# Organisation des Pages - Système de Coaching

## 📋 Vue d'ensemble

Le système est divisé en DEUX parties distinctes:

### 1️⃣ PARTIE UTILISATEUR (Cherche un coach)
### 2️⃣ PARTIE COACH (Reçoit et gère les demandes)

---

## 1️⃣ PARTIE UTILISATEUR

### Page: Liste des Coaches
**Route:** `/coaches`  
**Contrôleur:** `CoachController::index()`  
**Template:** `templates/coach/index.html.twig`  
**Rôle requis:** `ROLE_USER`

### Fonctionnalités:
✅ Voir la liste de tous les coaches  
✅ Filtrer les coaches par spécialité  
✅ Envoyer une demande de coaching avec un message  
✅ Voir le statut de ses propres demandes  

### Sections affichées:
1. **Filtres par spécialité** (Fitness, Yoga, Musculation, etc.)
2. **Formulaire de demande** (Sélection coach + Message)
3. **Liste des coaches disponibles** avec:
   - Nom, email, spécialité, rating
   - Badge de statut si demande existante
4. **Mes demandes envoyées** avec:
   - Coach contacté
   - Message envoyé
   - Statut (EN ATTENTE / ACCEPTÉE / REFUSÉE)

### Exemple d'utilisation:
```
Utilisateur → Accède à /coaches
           → Remplit le formulaire (choisit coach + écrit message)
           → Envoie la demande
           → Voit "Demande en attente" sur la carte du coach
           → Voit sa demande dans "Mes demandes de coaching"
```

---

## 2️⃣ PARTIE COACH

### Page: Demandes Reçues
**Route:** `/coach/requests`  
**Contrôleur:** `CoachingRequestController::index()`  
**Template:** `templates/coaching_request/index.html.twig`  
**Rôle requis:** `ROLE_COACH`

### Fonctionnalités:
✅ Voir toutes les demandes reçues  
✅ Lire les messages des utilisateurs  
✅ Accepter une demande  
✅ Refuser une demande  
✅ Voir l'historique complet  

### Sections affichées:
1. **Demandes en attente** (fond jaune, prioritaire):
   - Nom de l'utilisateur
   - Email de l'utilisateur
   - **MESSAGE de l'utilisateur** ⭐
   - Date de réception
   - Boutons: Accepter / Refuser

2. **Toutes les demandes** (historique):
   - Toutes les demandes (pending, accepted, declined)
   - **MESSAGE de l'utilisateur** ⭐
   - Badge de statut coloré
   - Bouton "Planifier la session" si acceptée

### Exemple d'utilisation:
```
Coach → Accède à /coach/requests
      → Voit les nouvelles demandes en attente
      → Lit le MESSAGE de l'utilisateur
      → Clique sur "Accepter" ou "Refuser"
      → Si accepté: peut planifier une session
```

---

## 🔄 Flux Complet

```
┌─────────────────────────────────────────────────────────────┐
│                    UTILISATEUR                               │
│  1. Va sur /coaches                                          │
│  2. Choisit un coach                                         │
│  3. Écrit un message: "Je veux perdre 10kg..."              │
│  4. Envoie la demande                                        │
│  5. Voit "Demande en attente"                               │
└─────────────────────────────────────────────────────────────┘
                            ↓
                    [Base de données]
                    CoachingRequest créé:
                    - user_id
                    - coach_id
                    - message: "Je veux perdre 10kg..."
                    - status: "pending"
                            ↓
┌─────────────────────────────────────────────────────────────┐
│                       COACH                                  │
│  1. Va sur /coach/requests                                   │
│  2. Voit la nouvelle demande                                 │
│  3. Lit le message: "Je veux perdre 10kg..."                │
│  4. Clique sur "Accepter"                                    │
│  5. Peut maintenant planifier une session                    │
└─────────────────────────────────────────────────────────────┘
                            ↓
                    [Base de données]
                    CoachingRequest mis à jour:
                    - status: "accepted"
                    - responded_at: maintenant
                    Session créée
                            ↓
┌─────────────────────────────────────────────────────────────┐
│                    UTILISATEUR                               │
│  Retourne sur /coaches                                       │
│  Voit "Demande acceptée" sur la carte du coach              │
└─────────────────────────────────────────────────────────────┘
```

---

## 📁 Structure des Fichiers

### Pour les UTILISATEURS:
```
src/Controller/CoachController.php
    ↓ méthode index()
templates/coach/index.html.twig
```

### Pour les COACHES:
```
src/Controller/CoachingRequestController.php
    ↓ méthode index()
templates/coaching_request/index.html.twig
```

---

## 🎯 Points Clés

### ✅ Ce qui est CORRECT:

1. **Page utilisateur** (`/coaches`):
   - Formulaire pour créer une demande ✅
   - Liste des coaches ✅
   - Affichage de MES demandes envoyées ✅
   - Statut de mes demandes ✅

2. **Page coach** (`/coach/requests`):
   - Liste des demandes REÇUES ✅
   - **Affichage des MESSAGES des utilisateurs** ✅
   - Boutons Accepter/Refuser ✅
   - Historique complet ✅

### ⚠️ Important:

- **L'utilisateur** envoie des demandes et voit LEURS statuts
- **Le coach** reçoit des demandes et peut les accepter/refuser
- **Le MESSAGE est visible des deux côtés:**
  - L'utilisateur voit son propre message dans "Mes demandes"
  - Le coach voit le message de l'utilisateur dans "Demandes reçues"

---

## 🚀 Pour tester:

### En tant qu'utilisateur:
1. Aller sur: `http://localhost:8000/coaches`
2. Remplir le formulaire
3. Voir ses demandes en bas de page

### En tant que coach:
1. Aller sur: `http://localhost:8000/coach/requests`
2. Voir les demandes reçues avec les messages
3. Accepter ou refuser

### Version DEMO (sans authentification):
- Utilisateur: `http://localhost:8000/demo/coaches`
- Coach: `http://localhost:8000/demo/coach/requests`
