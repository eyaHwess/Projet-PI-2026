# Goal Access Request System - Design Document

## 🏗️ Architecture Overview

### System Components

```
┌─────────────────────────────────────────────────────────────┐
│                     User Interface Layer                     │
├─────────────────────────────────────────────────────────────┤
│  Goal List    │   Chatroom    │   Group Info   │  Modals   │
└─────────────────────────────────────────────────────────────┘
                              ↓
┌─────────────────────────────────────────────────────────────┐
│                    Controller Layer                          │
├─────────────────────────────────────────────────────────────┤
│  join()  │  approveRequest()  │  rejectRequest()           │
└─────────────────────────────────────────────────────────────┘
                              ↓
┌─────────────────────────────────────────────────────────────┐
│                     Entity Layer                             │
├─────────────────────────────────────────────────────────────┤
│  GoalParticipation (with status field)                      │
└─────────────────────────────────────────────────────────────┘
                              ↓
┌─────────────────────────────────────────────────────────────┐
│                     Database Layer                           │
├─────────────────────────────────────────────────────────────┤
│  goal_participation table (with status column)              │
└─────────────────────────────────────────────────────────────┘
```

## 📊 Data Model

### GoalParticipation Entity

```php
<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GoalParticipationRepository::class)]
class GoalParticipation
{
    // Existing constants
    public const ROLE_MEMBER = 'MEMBER';
    public const ROLE_ADMIN = 'ADMIN';
    public const ROLE_OWNER = 'OWNER';
    
    // New constants for status
    public const STATUS_PENDING = 'PENDING';
    public const STATUS_APPROVED = 'APPROVED';
    public const STATUS_REJECTED = 'REJECTED';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'goalParticipations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'goalParticipations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Goal $goal = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(length: 20)]
    private string $role = self::ROLE_MEMBER;

    // NEW FIELD
    #[ORM\Column(length: 20)]
    private string $status = self::STATUS_APPROVED;

    // Existing methods...

    // New methods
    public function getStatus(): string 
    { 
        return $this->status; 
    }
    
    public function setStatus(string $status): static 
    { 
        if (!in_array($status, [self::STATUS_PENDING, self::STATUS_APPROVED, self::STATUS_REJECTED])) {
            throw new \InvalidArgumentException('Invalid status');
        }
        $this->status = $status; 
        return $this; 
    }

    public function isPending(): bool 
    { 
        return $this->status === self::STATUS_PENDING; 
    }
    
    public function isApproved(): bool 
    { 
        return $this->status === self::STATUS_APPROVED; 
    }
    
    public function isRejected(): bool 
    { 
        return $this->status === self::STATUS_REJECTED; 
    }
}
```

### Goal Entity - New Methods

```php
public function getPendingRequests(): Collection
{
    return $this->goalParticipations->filter(function($participation) {
        return $participation->isPending();
    });
}

public function getPendingRequestsCount(): int
{
    return $this->getPendingRequests()->count();
}

public function hasUserRequestedAccess(User $user): bool
{
    foreach ($this->goalParticipations as $participation) {
        if ($participation->getUser() === $user && $participation->isPending()) {
            return true;
        }
    }
    return false;
}
```

## 🎯 Controller Actions

### 1. Join Action (Modified)

```php
#[Route('/goal/{id}/join', name: 'goal_join')]
public function join(Goal $goal, EntityManagerInterface $em): Response
{
    $this->denyAccessUnlessGranted('ROLE_USER');
    
    $user = $this->getUser();

    // Check if already has a participation
    $existingParticipation = $em->getRepository(GoalParticipation::class)
        ->findOneBy(['goal' => $goal, 'user' => $user]);
    
    if ($existingParticipation) {
        if ($existingParticipation->isPending()) {
            $this->addFlash('warning', 'Votre demande est déjà en attente d\'approbation.');
        } elseif ($existingParticipation->isApproved()) {
            $this->addFlash('warning', 'Vous participez déjà à ce goal.');
        }
        return $this->redirectToRoute('goal_list');
    }

    // Create new participation with PENDING status
    $participation = new GoalParticipation();
    $participation->setGoal($goal);
    $participation->setUser($user);
    $participation->setCreatedAt(new \DateTime());
    $participation->setRole(GoalParticipation::ROLE_MEMBER);
    $participation->setStatus(GoalParticipation::STATUS_PENDING);

    $em->persist($participation);
    $em->flush();

    $this->addFlash('success', 'Demande d\'accès envoyée! En attente d\'approbation.');
    return $this->redirectToRoute('goal_list');
}
```

### 2. Approve Request Action (New)

```php
#[Route('/goal/{goalId}/approve-request/{userId}', name: 'goal_approve_request', methods: ['POST'])]
public function approveRequest(
    int $goalId, 
    int $userId, 
    EntityManagerInterface $em, 
    Request $request
): Response {
    $user = $this->getUser();
    
    if (!$user) {
        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(['success' => false, 'error' => 'Vous devez être connecté'], 401);
        }
        $this->addFlash('error', 'Vous devez être connecté');
        return $this->redirectToRoute('app_login');
    }

    $goal = $em->getRepository(Goal::class)->find($goalId);
    if (!$goal) {
        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(['success' => false, 'error' => 'Goal introuvable'], 404);
        }
        $this->addFlash('error', 'Goal introuvable');
        return $this->redirectToRoute('goal_list');
    }

    // Check permission (ADMIN or OWNER)
    if (!$goal->canUserRemoveMembers($user)) {
        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(['success' => false, 'error' => 'Permission refusée'], 403);
        }
        $this->addFlash('error', 'Vous n\'avez pas la permission d\'approuver des demandes');
        return $this->redirectToRoute('goal_messages', ['id' => $goalId]);
    }

    $requestUser = $em->getRepository(User::class)->find($userId);
    if (!$requestUser) {
        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(['success' => false, 'error' => 'Utilisateur introuvable'], 404);
        }
        $this->addFlash('error', 'Utilisateur introuvable');
        return $this->redirectToRoute('goal_messages', ['id' => $goalId]);
    }

    $participation = $em->getRepository(GoalParticipation::class)->findOneBy([
        'goal' => $goal,
        'user' => $requestUser
    ]);

    if (!$participation || !$participation->isPending()) {
        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(['success' => false, 'error' => 'Aucune demande en attente'], 404);
        }
        $this->addFlash('error', 'Aucune demande en attente pour cet utilisateur');
        return $this->redirectToRoute('goal_messages', ['id' => $goalId]);
    }

    // Approve the request
    $participation->setStatus(GoalParticipation::STATUS_APPROVED);
    $em->flush();

    $userName = $requestUser->getFirstName() . ' ' . $requestUser->getLastName();
    
    if ($request->isXmlHttpRequest()) {
        return new JsonResponse([
            'success' => true,
            'message' => "$userName a été accepté dans le goal"
        ]);
    }

    $this->addFlash('success', "$userName a été accepté dans le goal");
    return $this->redirectToRoute('goal_messages', ['id' => $goalId]);
}
```

### 3. Reject Request Action (New)

```php
#[Route('/goal/{goalId}/reject-request/{userId}', name: 'goal_reject_request', methods: ['POST'])]
public function rejectRequest(
    int $goalId, 
    int $userId, 
    EntityManagerInterface $em, 
    Request $request
): Response {
    $user = $this->getUser();
    
    if (!$user) {
        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(['success' => false, 'error' => 'Vous devez être connecté'], 401);
        }
        $this->addFlash('error', 'Vous devez être connecté');
        return $this->redirectToRoute('app_login');
    }

    $goal = $em->getRepository(Goal::class)->find($goalId);
    if (!$goal) {
        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(['success' => false, 'error' => 'Goal introuvable'], 404);
        }
        $this->addFlash('error', 'Goal introuvable');
        return $this->redirectToRoute('goal_list');
    }

    // Check permission (ADMIN or OWNER)
    if (!$goal->canUserRemoveMembers($user)) {
        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(['success' => false, 'error' => 'Permission refusée'], 403);
        }
        $this->addFlash('error', 'Vous n\'avez pas la permission de refuser des demandes');
        return $this->redirectToRoute('goal_messages', ['id' => $goalId]);
    }

    $requestUser = $em->getRepository(User::class)->find($userId);
    if (!$requestUser) {
        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(['success' => false, 'error' => 'Utilisateur introuvable'], 404);
        }
        $this->addFlash('error', 'Utilisateur introuvable');
        return $this->redirectToRoute('goal_messages', ['id' => $goalId]);
    }

    $participation = $em->getRepository(GoalParticipation::class)->findOneBy([
        'goal' => $goal,
        'user' => $requestUser
    ]);

    if (!$participation || !$participation->isPending()) {
        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(['success' => false, 'error' => 'Aucune demande en attente'], 404);
        }
        $this->addFlash('error', 'Aucune demande en attente pour cet utilisateur');
        return $this->redirectToRoute('goal_messages', ['id' => $goalId]);
    }

    // Reject the request (delete participation)
    $userName = $requestUser->getFirstName() . ' ' . $requestUser->getLastName();
    $em->remove($participation);
    $em->flush();
    
    if ($request->isXmlHttpRequest()) {
        return new JsonResponse([
            'success' => true,
            'message' => "Demande de $userName refusée"
        ]);
    }

    $this->addFlash('success', "Demande de $userName refusée");
    return $this->redirectToRoute('goal_messages', ['id' => $goalId]);
}
```

## 🎨 UI Components

### 1. Goal List - Button States

```twig
{# templates/goal/list.html.twig #}

{% set userParticipation = null %}
{% if app.user %}
    {% for participation in goal.goalParticipations %}
        {% if participation.user.id == app.user.id %}
            {% set userParticipation = participation %}
        {% endif %}
    {% endfor %}
{% endif %}

{% if app.user %}
    {% if userParticipation %}
        {% if userParticipation.isPending() %}
            <button class="btn btn-warning" disabled>
                <i class="fas fa-clock"></i> En attente d'approbation
            </button>
        {% elseif userParticipation.isApproved() %}
            <a href="{{ path('goal_leave', {id: goal.id}) }}" class="btn btn-danger">
                <i class="fas fa-sign-out-alt"></i> Quitter
            </a>
        {% endif %}
    {% else %}
        <a href="{{ path('goal_join', {id: goal.id}) }}" class="btn btn-success">
            <i class="fas fa-user-plus"></i> Rejoindre
        </a>
    {% endif %}
{% endif %}
```

### 2. Chatroom - Pending Status Message

```twig
{# templates/chatroom/chatroom.html.twig #}

{% if currentUserParticipation and currentUserParticipation.isPending() %}
    <!-- Pending approval message -->
    <div class="pending-approval-notice">
        <div class="pending-icon">
            <i class="fas fa-clock"></i>
        </div>
        <div class="pending-content">
            <h3 class="pending-title">Demande en attente d'approbation</h3>
            <p class="pending-text">
                Votre demande a été envoyée aux administrateurs du goal.
                Vous pourrez participer une fois votre demande approuvée.
            </p>
        </div>
    </div>
{% elseif isMember is defined and not isMember %}
    <!-- Non-member message (existing) -->
{% endif %}
```

### 3. Group Info - Pending Requests Section

```twig
{# templates/chatroom/chatroom.html.twig - Group Info Sidebar #}

{% if currentUserParticipation and currentUserParticipation.canModerate() %}
    {% set pendingRequests = goal.getPendingRequests() %}
    {% if pendingRequests|length > 0 %}
        <!-- Pending Requests Section -->
        <div class="group-info-section">
            <div class="group-info-section-title" onclick="toggleSection('pendingRequests')">
                <span>
                    <i class="fas fa-user-clock"></i> 
                    Demandes en attente ({{ pendingRequests|length }})
                </span>
                <i class="fas fa-chevron-down"></i>
            </div>
            <div id="pendingRequestsSection" class="group-info-section-content">
                {% for participation in pendingRequests %}
                    <div class="pending-request-item">
                        <div class="pending-request-avatar">
                            {{ participation.user.firstName|first }}{{ participation.user.lastName|first }}
                        </div>
                        <div class="pending-request-info">
                            <div class="pending-request-name">
                                {{ participation.user.firstName }} {{ participation.user.lastName }}
                            </div>
                            <div class="pending-request-time">
                                {{ participation.createdAt|date('d/m/Y H:i') }}
                            </div>
                        </div>
                        <div class="pending-request-actions">
                            <button class="btn-approve" onclick="approveRequest({{ participation.user.id }})" title="Accepter">
                                <i class="fas fa-check"></i>
                            </button>
                            <button class="btn-reject" onclick="rejectRequest({{ participation.user.id }})" title="Refuser">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                {% endfor %}
            </div>
        </div>
    {% endif %}
{% endif %}
```

### 4. Header - Pending Requests Badge

```twig
{# templates/chatroom/chatroom.html.twig - Chat Header #}

<div class="chat-header-subtitle">
    {{ goal.goalParticipations|length }} participants • {{ goal.status }}
    {% if currentUserParticipation %}
        • <span class="user-role-badge {{ currentUserParticipation.role|lower }}">
            {{ currentUserParticipation.role }}
          </span>
    {% endif %}
    {% if currentUserParticipation and currentUserParticipation.canModerate() %}
        {% set pendingCount = goal.getPendingRequestsCount() %}
        {% if pendingCount > 0 %}
            • <span class="pending-requests-badge">
                <i class="fas fa-user-clock"></i> {{ pendingCount }} demande(s)
              </span>
        {% endif %}
    {% endif %}
</div>
```

## 🎨 CSS Styles

```css
/* Pending approval notice */
.pending-approval-notice {
    display: flex;
    align-items: center;
    gap: 20px;
    padding: 24px;
    background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
    border-radius: 16px;
    border: 2px solid #ffc107;
    margin-bottom: 20px;
}

.pending-icon {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 24px;
    flex-shrink: 0;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.05); }
}

.pending-content {
    flex: 1;
}

.pending-title {
    font-size: 18px;
    font-weight: 700;
    color: #856404;
    margin: 0 0 8px 0;
}

.pending-text {
    font-size: 14px;
    color: #856404;
    margin: 0;
}

/* Pending requests section */
.pending-request-item {
    display: flex;
    align-items: center;
    padding: 12px;
    border-radius: 8px;
    margin-bottom: 8px;
    background: #fff3cd;
    border: 1px solid #ffc107;
}

.pending-request-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 14px;
    font-weight: 600;
    margin-right: 12px;
    flex-shrink: 0;
}

.pending-request-info {
    flex: 1;
}

.pending-request-name {
    font-size: 14px;
    font-weight: 600;
    color: #856404;
}

.pending-request-time {
    font-size: 12px;
    color: #856404;
    opacity: 0.8;
}

.pending-request-actions {
    display: flex;
    gap: 8px;
}

.btn-approve, .btn-reject {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
}

.btn-approve {
    background: #28a745;
    color: white;
}

.btn-approve:hover {
    background: #218838;
    transform: scale(1.1);
}

.btn-reject {
    background: #dc3545;
    color: white;
}

.btn-reject:hover {
    background: #c82333;
    transform: scale(1.1);
}

/* Pending requests badge in header */
.pending-requests-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 3px 10px;
    border-radius: 12px;
    background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
    color: #856404;
    font-size: 11px;
    font-weight: 700;
    animation: pulse 2s infinite;
}
```

## 📱 JavaScript Functions

```javascript
// Approve request
async function approveRequest(userId) {
    if (!confirm('Accepter cette demande d\'accès ?')) {
        return;
    }
    
    const goalId = {{ goal.id }};
    
    try {
        const response = await fetch(`/goal/${goalId}/approve-request/${userId}`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert(result.message);
            location.reload();
        } else {
            alert('Erreur: ' + result.error);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Une erreur est survenue');
    }
}

// Reject request
async function rejectRequest(userId) {
    if (!confirm('Refuser cette demande d\'accès ?')) {
        return;
    }
    
    const goalId = {{ goal.id }};
    
    try {
        const response = await fetch(`/goal/${goalId}/reject-request/${userId}`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert(result.message);
            location.reload();
        } else {
            alert('Erreur: ' + result.error);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Une erreur est survenue');
    }
}
```

## 🔄 State Diagram

```
┌─────────────┐
│  No Request │
└──────┬──────┘
       │ Click "Rejoindre"
       ↓
┌─────────────┐
│   PENDING   │ ← User sees "En attente"
└──────┬──────┘   Admin sees in pending list
       │
       ├─→ Admin clicks "Accepter"
       │   ↓
       │   ┌─────────────┐
       │   │  APPROVED   │ ← User can now participate
       │   └─────────────┘
       │
       └─→ Admin clicks "Refuser"
           ↓
           ┌─────────────┐
           │   DELETED   │ ← Participation removed
           └─────────────┘   User can request again
```

## ✅ Validation Rules

### Server-Side
1. User must be authenticated to request access
2. User cannot have multiple participations for same goal
3. Only ADMIN/OWNER can approve/reject
4. Cannot approve/reject non-pending requests
5. Status must be valid (PENDING/APPROVED/REJECTED)

### Client-Side
1. Button disabled if request pending
2. Confirmation before approve/reject
3. Visual feedback on actions
4. Error messages displayed clearly

---

**Created:** 17 février 2026  
**Status:** Ready for Implementation  
**Next Step:** Create tasks.md
