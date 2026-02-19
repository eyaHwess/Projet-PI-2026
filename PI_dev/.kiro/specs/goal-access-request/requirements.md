# Goal Access Request System - Requirements

## 📋 Overview
Système de demande d'accès pour les goals permettant aux administrateurs de contrôler qui peut rejoindre un goal.

## 🎯 User Stories

### US-1: Demande d'Accès Utilisateur
**En tant qu'** utilisateur non-membre  
**Je veux** demander l'accès à un goal  
**Afin de** pouvoir participer après approbation

**Acceptance Criteria:**
- AC-1.1: Le bouton "Rejoindre" crée une participation avec status PENDING
- AC-1.2: L'utilisateur voit un message "Demande envoyée, en attente d'approbation"
- AC-1.3: L'utilisateur ne peut pas envoyer de messages tant que status = PENDING
- AC-1.4: L'utilisateur peut voir les messages en lecture seule
- AC-1.5: Un badge "En attente" est affiché pour l'utilisateur

### US-2: Notification des Administrateurs
**En tant qu'** administrateur ou propriétaire  
**Je veux** être notifié des nouvelles demandes  
**Afin de** pouvoir les traiter rapidement

**Acceptance Criteria:**
- AC-2.1: Badge de notification visible dans le chatroom
- AC-2.2: Section "Demandes en attente" dans Group Info
- AC-2.3: Nombre de demandes en attente affiché
- AC-2.4: Liste des demandeurs avec leurs informations

### US-3: Approbation de Demande
**En tant qu'** administrateur ou propriétaire  
**Je veux** approuver une demande d'accès  
**Afin de** permettre à l'utilisateur de participer

**Acceptance Criteria:**
- AC-3.1: Bouton "Accepter" visible pour ADMIN et OWNER
- AC-3.2: Clic sur "Accepter" change status de PENDING à APPROVED
- AC-3.3: L'utilisateur peut maintenant envoyer des messages
- AC-3.4: Message de confirmation affiché
- AC-3.5: L'utilisateur reçoit une notification (optionnel)

### US-4: Refus de Demande
**En tant qu'** administrateur ou propriétaire  
**Je veux** refuser une demande d'accès  
**Afin de** contrôler qui peut rejoindre le goal

**Acceptance Criteria:**
- AC-4.1: Bouton "Refuser" visible pour ADMIN et OWNER
- AC-4.2: Clic sur "Refuser" supprime la participation
- AC-4.3: L'utilisateur voit un message "Demande refusée"
- AC-4.4: L'utilisateur peut redemander l'accès plus tard
- AC-4.5: Message de confirmation affiché à l'admin

### US-5: Gestion des Demandes en Attente
**En tant qu'** administrateur ou propriétaire  
**Je veux** voir toutes les demandes en attente  
**Afin de** les gérer efficacement

**Acceptance Criteria:**
- AC-5.1: Section dédiée dans Group Info
- AC-5.2: Liste avec nom, date de demande
- AC-5.3: Actions Accepter/Refuser pour chaque demande
- AC-5.4: Compteur de demandes en attente
- AC-5.5: Tri par date (plus récent en premier)

## 🗄️ Database Schema

### GoalParticipation Entity - Modifications

**Nouveau champ:**
```php
#[ORM\Column(length: 20)]
private string $status = self::STATUS_APPROVED;

// Constantes
public const STATUS_PENDING = 'PENDING';
public const STATUS_APPROVED = 'APPROVED';
public const STATUS_REJECTED = 'REJECTED';
```

**Méthodes helper:**
```php
public function isPending(): bool
public function isApproved(): bool
public function isRejected(): bool
```

## 🎨 UI/UX Design

### 1. Bouton "Rejoindre" (Liste des Goals)
**État Initial:**
```
[Rejoindre] → Clic → [Demande envoyée ⏳]
```

**États:**
- Aucune participation: "Rejoindre"
- PENDING: "En attente d'approbation" (désactivé)
- APPROVED: "Quitter"

### 2. Chatroom - Vue Non-Membre avec Demande Pending
```
┌─────────────────────────────────────┐
│ 🕐 Demande en attente d'approbation │
│                                     │
│ Votre demande a été envoyée aux     │
│ administrateurs du goal.            │
│                                     │
│ Vous pourrez participer une fois    │
│ votre demande approuvée.            │
└─────────────────────────────────────┘
```

### 3. Group Info - Section Demandes en Attente
```
┌─────────────────────────────────────┐
│ 📋 Demandes en attente (3)          │
├─────────────────────────────────────┤
│ 👤 John Doe                         │
│    Il y a 2 heures                  │
│    [✓ Accepter] [✗ Refuser]        │
├─────────────────────────────────────┤
│ 👤 Jane Smith                       │
│    Il y a 5 heures                  │
│    [✓ Accepter] [✗ Refuser]        │
└─────────────────────────────────────┘
```

### 4. Badge de Notification (Header Chatroom)
```
🎯 Mon Goal
5 participants • 2 demandes en attente • OWNER
```

## 🔐 Security & Permissions

### Matrice des Permissions

| Action | Non-Membre | PENDING | MEMBER | ADMIN | OWNER |
|--------|------------|---------|--------|-------|-------|
| Demander accès | ✅ | ❌ | ❌ | ❌ | ❌ |
| Voir messages | ✅ | ✅ | ✅ | ✅ | ✅ |
| Envoyer messages | ❌ | ❌ | ✅ | ✅ | ✅ |
| Voir demandes | ❌ | ❌ | ❌ | ✅ | ✅ |
| Accepter demande | ❌ | ❌ | ❌ | ✅ | ✅ |
| Refuser demande | ❌ | ❌ | ❌ | ✅ | ✅ |

## 📊 Business Rules

### BR-1: Création de Demande
- Un utilisateur ne peut avoir qu'une seule participation par goal
- Si une participation REJECTED existe, elle doit être supprimée avant de redemander
- La demande est créée avec role = MEMBER par défaut

### BR-2: Approbation
- Seuls ADMIN et OWNER peuvent approuver
- L'approbation change status de PENDING à APPROVED
- Le rôle reste MEMBER (peut être changé après)

### BR-3: Refus
- Seuls ADMIN et OWNER peuvent refuser
- Le refus supprime la participation
- L'utilisateur peut redemander immédiatement

### BR-4: Notifications
- Badge visible seulement pour ADMIN et OWNER
- Compteur mis à jour en temps réel
- Disparaît quand aucune demande en attente

## 🚀 Implementation Plan

### Phase 1: Database & Entity
1. Ajouter champ `status` à GoalParticipation
2. Créer migration
3. Ajouter constantes et méthodes helper
4. Mettre à jour les participations existantes (APPROVED)

### Phase 2: Backend Logic
1. Modifier `join()` pour créer avec status PENDING
2. Créer action `approveRequest()`
3. Créer action `rejectRequest()`
4. Ajouter méthode `getPendingRequests()` dans Goal entity

### Phase 3: Frontend - Liste des Goals
1. Modifier bouton "Rejoindre" selon status
2. Afficher badge "En attente" si PENDING
3. Désactiver bouton si PENDING

### Phase 4: Frontend - Chatroom
1. Modifier message non-membre pour PENDING
2. Ajouter section "Demandes en attente" dans Group Info
3. Ajouter badge de notification dans header
4. Implémenter boutons Accepter/Refuser

### Phase 5: Testing
1. Test création demande
2. Test approbation
3. Test refus
4. Test permissions
5. Test UI/UX

## 📝 Technical Notes

### Migration Strategy
```sql
ALTER TABLE goal_participation 
ADD COLUMN status VARCHAR(20) DEFAULT 'APPROVED' NOT NULL;

UPDATE goal_participation 
SET status = 'APPROVED' 
WHERE status IS NULL;
```

### Repository Methods Needed
```php
// GoalParticipationRepository
public function findPendingByGoal(Goal $goal): array
public function countPendingByGoal(Goal $goal): int
public function findByUserAndGoal(User $user, Goal $goal): ?GoalParticipation
```

### Controller Actions Needed
```php
// GoalController
#[Route('/goal/{id}/join', name: 'goal_join')]
public function join() // Modifier pour créer avec PENDING

#[Route('/goal/{goalId}/approve-request/{userId}', name: 'goal_approve_request')]
public function approveRequest()

#[Route('/goal/{goalId}/reject-request/{userId}', name: 'goal_reject_request')]
public function rejectRequest()
```

## 🎓 Success Criteria

### Functional
- ✅ Utilisateur peut demander l'accès
- ✅ Admin voit les demandes en attente
- ✅ Admin peut accepter/refuser
- ✅ Utilisateur approuvé peut participer
- ✅ Utilisateur refusé peut redemander

### Non-Functional
- ✅ Interface intuitive et claire
- ✅ Feedback immédiat sur les actions
- ✅ Pas de bugs ou erreurs
- ✅ Performance acceptable (< 200ms)
- ✅ Sécurité: vérifications côté serveur

## 🔄 Future Enhancements

### V2 Features (Optionnel)
1. **Notifications Email**
   - Email à l'admin lors d'une nouvelle demande
   - Email à l'utilisateur lors d'approbation/refus

2. **Message Personnalisé**
   - Utilisateur peut ajouter un message avec sa demande
   - Admin peut voir le message avant de décider

3. **Historique**
   - Log des demandes acceptées/refusées
   - Statistiques par goal

4. **Auto-Approbation**
   - Option pour approuver automatiquement
   - Basé sur critères (domaine email, etc.)

5. **Expiration**
   - Demandes expirent après X jours
   - Nettoyage automatique

## 📚 References

- Symfony Security: https://symfony.com/doc/current/security.html
- Doctrine Relations: https://www.doctrine-project.org/projects/doctrine-orm/en/latest/reference/association-mapping.html
- UX Best Practices: Material Design Guidelines

---

**Created:** 17 février 2026  
**Status:** Ready for Implementation  
**Priority:** High  
**Estimated Effort:** 4-6 hours
