# Goal System Enhancements - Design Document

## Architecture Overview

### New Entities

#### 1. MessageReaction
```php
class MessageReaction {
    id: int
    message: Message (ManyToOne)
    user: User (ManyToOne)
    reactionType: string (like, clap, fire, heart)
    createdAt: DateTime
}
```

#### 2. Badge
```php
class Badge {
    id: int
    user: User (ManyToOne)
    badgeType: string (motivated, leader, faithful)
    earnedAt: DateTime
}
```

#### 3. Notification
```php
class Notification {
    id: int
    user: User (ManyToOne)
    type: string (new_participant, new_message, goal_completed)
    content: string
    relatedGoalId: int (nullable)
    isRead: boolean
    createdAt: DateTime
}
```

### Modified Entities

#### Goal Entity Updates
```php
class Goal {
    // Existing fields...
    progress: int (0-100) // NEW
    completedAt: DateTime (nullable) // NEW
}
```

#### User Entity Updates
```php
class User {
    // Existing fields...
    points: int (default: 0) // NEW
    badges: Collection<Badge> (OneToMany) // NEW
    notifications: Collection<Notification> (OneToMany) // NEW
}
```

#### GoalParticipation Entity Updates
```php
class GoalParticipation {
    // Existing fields...
    role: string (creator, co_leader, participant) // NEW
}
```

#### Message Entity Updates
```php
class Message {
    // Existing fields...
    isPinned: boolean (default: false) // NEW
    reactions: Collection<MessageReaction> (OneToMany) // NEW
}
```

---

## Feature Designs

### 1. Système de Progression

#### Database Schema
- Add `progress` column to `goal` table (INTEGER, default 0)
- Add `completed_at` column to `goal` table (DATETIME, nullable)

#### UI Components
**Progress Bar Component:**
```html
<div class="progress-container">
    <div class="progress-bar" style="width: {{ goal.progress }}%">
        <span>{{ goal.progress }}%</span>
    </div>
</div>
```

**Color Logic:**
- 0-29%: Red (#f44336)
- 30-69%: Yellow (#ffc107)
- 70-99%: Blue (#2196F3)
- 100%: Green (#4caf50)

#### Controller Actions
```php
#[Route('/goal/{id}/update-progress', name: 'goal_update_progress')]
public function updateProgress(Goal $goal, Request $request): Response
{
    // Check if user is creator
    // Update progress
    // If progress = 100, set completedAt
    // Send notifications to all participants
}
```

---

### 2. Gamification

#### Points System
**Point Awards:**
- Create goal: +50 points
- Join goal: +10 points
- Send message: +5 points
- Complete goal: +100 points (creator only)
- Receive reaction: +2 points

**Implementation:**
```php
// In GoalController::new()
$user->setPoints($user->getPoints() + 50);

// In GoalController::join()
$user->setPoints($user->getPoints() + 10);

// In GoalController::messages() after message save
$user->setPoints($user->getPoints() + 5);
```

#### Badge System
**Badge Types:**
1. **Motivé** (Motivated): 10+ messages sent
2. **Leader**: Created 3+ goals
3. **Fidèle** (Faithful): Joined 5+ goals
4. **Champion**: Completed 5+ goals
5. **Social**: Received 50+ reactions

**Badge Check Service:**
```php
class BadgeService {
    public function checkAndAwardBadges(User $user): void
    {
        // Check each badge criteria
        // Award badge if not already earned
        // Create notification
    }
}
```

#### Leaderboard
**Route:** `/leaderboard`

**Query:**
```php
$users = $userRepository->createQueryBuilder('u')
    ->orderBy('u.points', 'DESC')
    ->setMaxResults(10)
    ->getQuery()
    ->getResult();
```

**UI Design:**
- Top 3 users: Podium style (gold, silver, bronze)
- Ranks 4-10: List style
- Show: rank, avatar, name, points, badges
- Filter tabs: All Time, This Month, This Week

---

### 3. Rôles dans le Goal

#### Role Enum
```php
enum GoalRole: string {
    case CREATOR = 'creator';
    case CO_LEADER = 'co_leader';
    case PARTICIPANT = 'participant';
}
```

#### Permission Matrix
| Action | Creator | Co-Leader | Participant |
|--------|---------|-----------|-------------|
| Delete goal | ✅ | ❌ | ❌ |
| Update progress | ✅ | ✅ | ❌ |
| Pin message | ✅ | ✅ | ❌ |
| Remove participant | ✅ | ✅ | ❌ |
| Promote to Co-Leader | ✅ | ❌ | ❌ |
| Send message | ✅ | ✅ | ✅ |
| Leave goal | ❌ | ✅ | ✅ |

#### Permission Service
```php
class GoalPermissionService {
    public function canUpdateProgress(User $user, Goal $goal): bool
    {
        $participation = $goal->getUserParticipation($user);
        return $participation && in_array($participation->getRole(), ['creator', 'co_leader']);
    }
    
    public function canDeleteGoal(User $user, Goal $goal): bool
    {
        $participation = $goal->getUserParticipation($user);
        return $participation && $participation->getRole() === 'creator';
    }
    
    // ... other permission methods
}
```

#### UI Changes
- Role badges in participant list
- Conditional rendering of action buttons
- "Promote to Co-Leader" button for creator

---

### 4. Réactions aux Messages

#### Database Schema
```sql
CREATE TABLE message_reaction (
    id INT AUTO_INCREMENT PRIMARY KEY,
    message_id INT NOT NULL,
    user_id INT NOT NULL,
    reaction_type VARCHAR(20) NOT NULL,
    created_at DATETIME NOT NULL,
    FOREIGN KEY (message_id) REFERENCES message(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE,
    UNIQUE KEY unique_reaction (message_id, user_id, reaction_type)
);
```

#### Reaction Types
- `like` 👍
- `clap` 👏
- `fire` 🔥
- `heart` ❤️

#### Controller Action
```php
#[Route('/message/{id}/react/{type}', name: 'message_react')]
public function reactToMessage(Message $message, string $type, EntityManagerInterface $em): Response
{
    $user = $this->getUser();
    
    // Check if reaction already exists
    $existingReaction = $em->getRepository(MessageReaction::class)
        ->findOneBy(['message' => $message, 'user' => $user, 'reactionType' => $type]);
    
    if ($existingReaction) {
        // Remove reaction (toggle off)
        $em->remove($existingReaction);
    } else {
        // Add reaction
        $reaction = new MessageReaction();
        $reaction->setMessage($message);
        $reaction->setUser($user);
        $reaction->setReactionType($type);
        $reaction->setCreatedAt(new \DateTime());
        $em->persist($reaction);
        
        // Award points to message author
        $message->getAuthor()->setPoints($message->getAuthor()->getPoints() + 2);
    }
    
    $em->flush();
    
    return $this->redirectToRoute('goal_messages', ['id' => $message->getChatroom()->getGoal()->getId()]);
}
```

#### UI Component
```html
<div class="message-reactions">
    <button class="reaction-btn" data-type="like">
        👍 <span class="count">{{ message.getReactionCount('like') }}</span>
    </button>
    <button class="reaction-btn" data-type="clap">
        👏 <span class="count">{{ message.getReactionCount('clap') }}</span>
    </button>
    <button class="reaction-btn" data-type="fire">
        🔥 <span class="count">{{ message.getReactionCount('fire') }}</span>
    </button>
    <button class="reaction-btn" data-type="heart">
        ❤️ <span class="count">{{ message.getReactionCount('heart') }}</span>
    </button>
</div>
```

---

### 5. Message Épinglé

#### Business Logic
- Only one message can be pinned per chatroom
- Only creator/co-leader can pin messages
- Pinned message displayed at top of chat
- Unpin button available

#### Controller Actions
```php
#[Route('/message/{id}/pin', name: 'message_pin')]
public function pinMessage(Message $message, EntityManagerInterface $em): Response
{
    // Check permissions
    // Unpin any existing pinned message in this chatroom
    // Pin this message
}

#[Route('/message/{id}/unpin', name: 'message_unpin')]
public function unpinMessage(Message $message, EntityManagerInterface $em): Response
{
    // Check permissions
    // Unpin message
}
```

#### UI Design
- Pinned message box at top of chatroom (yellow background)
- Pin icon 📌 next to message
- "Unpin" button for creator/co-leader

---

### 6. Notifications

#### Notification Types
1. **new_participant**: "X joined your goal Y"
2. **new_message**: "New message in goal Y"
3. **goal_completed**: "Goal Y has been completed!"
4. **promoted**: "You've been promoted to Co-Leader in goal Y"
5. **badge_earned**: "You earned the X badge!"

#### Notification Service
```php
class NotificationService {
    public function notifyGoalParticipants(Goal $goal, string $type, string $content): void
    {
        foreach ($goal->getGoalParticipations() as $participation) {
            $notification = new Notification();
            $notification->setUser($participation->getUser());
            $notification->setType($type);
            $notification->setContent($content);
            $notification->setRelatedGoalId($goal->getId());
            $notification->setIsRead(false);
            $notification->setCreatedAt(new \DateTime());
            
            $this->em->persist($notification);
        }
        $this->em->flush();
    }
}
```

#### UI Component
**Navbar Badge:**
```html
<div class="notification-icon">
    <i class="fas fa-bell"></i>
    {% if unreadCount > 0 %}
        <span class="badge">{{ unreadCount }}</span>
    {% endif %}
</div>
```

**Dropdown:**
```html
<div class="notification-dropdown">
    {% for notification in notifications %}
        <div class="notification-item {% if not notification.isRead %}unread{% endif %}">
            <div class="notification-content">{{ notification.content }}</div>
            <div class="notification-time">{{ notification.createdAt|date('g:i A') }}</div>
        </div>
    {% endfor %}
</div>
```

---

### 7. Filtre des Goals

#### Filter Types
- **Tous**: All goals
- **Mes Goals**: Goals created by current user
- **En cours**: Goals with progress < 100%
- **Terminés**: Goals with progress = 100%

#### Implementation
**JavaScript Filter:**
```javascript
function filterGoals(filterType) {
    const goals = document.querySelectorAll('.goal-card');
    
    goals.forEach(goal => {
        const progress = parseInt(goal.dataset.progress);
        const creatorId = parseInt(goal.dataset.creatorId);
        const currentUserId = parseInt(document.body.dataset.userId);
        
        let show = false;
        
        switch(filterType) {
            case 'all':
                show = true;
                break;
            case 'mine':
                show = creatorId === currentUserId;
                break;
            case 'in_progress':
                show = progress < 100;
                break;
            case 'completed':
                show = progress === 100;
                break;
        }
        
        goal.style.display = show ? 'block' : 'none';
    });
}
```

---

### 8. Statistiques Personnelles

#### Dashboard Route
`/profile/stats`

#### Statistics to Display
1. **Goals Created**: Count of goals where user is creator
2. **Goals Joined**: Count of goal participations
3. **Completion Rate**: (Completed goals / Total goals) * 100
4. **Total Points**: User's point total
5. **Messages Sent**: Count of messages by user
6. **Reactions Received**: Count of reactions on user's messages
7. **Badges Earned**: List of badges with dates

#### Chart Library
Use **Chart.js** for visualizations:
- Bar chart: Goals per month
- Pie chart: Goal status distribution
- Line chart: Points over time

---

### 9. Dark Mode

#### Implementation
**CSS Variables:**
```css
:root {
    --bg-primary: #ffffff;
    --bg-secondary: #f8f9fa;
    --text-primary: #333333;
    --text-secondary: #666666;
}

[data-theme="dark"] {
    --bg-primary: #1a1a1a;
    --bg-secondary: #2d2d2d;
    --text-primary: #ffffff;
    --text-secondary: #cccccc;
}
```

**Toggle Button:**
```html
<button id="darkModeToggle">
    <i class="fas fa-moon"></i>
</button>
```

**JavaScript:**
```javascript
const darkModeToggle = document.getElementById('darkModeToggle');
const currentTheme = localStorage.getItem('theme') || 'light';

document.documentElement.setAttribute('data-theme', currentTheme);

darkModeToggle.addEventListener('click', () => {
    const theme = document.documentElement.getAttribute('data-theme');
    const newTheme = theme === 'light' ? 'dark' : 'light';
    
    document.documentElement.setAttribute('data-theme', newTheme);
    localStorage.setItem('theme', newTheme);
});
```

---

## Database Migrations

### Migration 1: Add Progress to Goal
```php
$this->addSql('ALTER TABLE goal ADD progress INT DEFAULT 0 NOT NULL');
$this->addSql('ALTER TABLE goal ADD completed_at DATETIME DEFAULT NULL');
```

### Migration 2: Add Points to User
```php
$this->addSql('ALTER TABLE user ADD points INT DEFAULT 0 NOT NULL');
```

### Migration 3: Add Role to GoalParticipation
```php
$this->addSql('ALTER TABLE goal_participation ADD role VARCHAR(20) DEFAULT "participant" NOT NULL');
```

### Migration 4: Add isPinned to Message
```php
$this->addSql('ALTER TABLE message ADD is_pinned TINYINT(1) DEFAULT 0 NOT NULL');
```

### Migration 5: Create MessageReaction Table
```php
$this->addSql('CREATE TABLE message_reaction (
    id INT AUTO_INCREMENT NOT NULL,
    message_id INT NOT NULL,
    user_id INT NOT NULL,
    reaction_type VARCHAR(20) NOT NULL,
    created_at DATETIME NOT NULL,
    PRIMARY KEY(id),
    INDEX IDX_message (message_id),
    INDEX IDX_user (user_id),
    UNIQUE INDEX unique_reaction (message_id, user_id, reaction_type)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
```

### Migration 6: Create Badge Table
```php
$this->addSql('CREATE TABLE badge (
    id INT AUTO_INCREMENT NOT NULL,
    user_id INT NOT NULL,
    badge_type VARCHAR(50) NOT NULL,
    earned_at DATETIME NOT NULL,
    PRIMARY KEY(id),
    INDEX IDX_user (user_id)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
```

### Migration 7: Create Notification Table
```php
$this->addSql('CREATE TABLE notification (
    id INT AUTO_INCREMENT NOT NULL,
    user_id INT NOT NULL,
    type VARCHAR(50) NOT NULL,
    content TEXT NOT NULL,
    related_goal_id INT DEFAULT NULL,
    is_read TINYINT(1) DEFAULT 0 NOT NULL,
    created_at DATETIME NOT NULL,
    PRIMARY KEY(id),
    INDEX IDX_user (user_id),
    INDEX IDX_is_read (is_read)
) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
```

---

## Security Considerations

### Role-Based Access Control (RBAC)
- All permission checks done server-side
- Use GoalPermissionService for centralized logic
- Never trust client-side role information

### CSRF Protection
- All forms include CSRF tokens
- Validate tokens on POST requests

### Input Validation
- Sanitize all user input
- Validate progress values (0-100)
- Validate reaction types (whitelist)

### SQL Injection Prevention
- Use Doctrine ORM (parameterized queries)
- Never concatenate user input in queries

---

## Performance Optimizations

### Database Indexes
- Index on `goal_participation.role`
- Index on `notification.is_read`
- Index on `message_reaction.message_id`
- Composite index on `message_reaction(message_id, user_id, reaction_type)`

### Caching
- Cache leaderboard data (5 minute TTL)
- Cache badge counts
- Cache notification counts

### Query Optimization
- Use JOIN FETCH for eager loading
- Paginate message lists
- Limit notification queries to last 50

---

## Testing Strategy

### Unit Tests
- BadgeService::checkAndAwardBadges()
- GoalPermissionService methods
- NotificationService methods

### Integration Tests
- Goal creation with automatic role assignment
- Message reaction toggle
- Progress update with completion notification

### UI Tests
- Filter goals by status
- Dark mode toggle persistence
- Reaction button interactions

---

## Deployment Checklist

- [ ] Run all migrations
- [ ] Clear Symfony cache
- [ ] Update existing goals with default progress (0)
- [ ] Update existing participations with default role (participant)
- [ ] Set creators to 'creator' role
- [ ] Test all permission checks
- [ ] Verify notification delivery
- [ ] Test dark mode on all pages

---

## Future Enhancements (Out of Scope)

- Real-time notifications with WebSockets
- Email notifications
- Mobile app
- Goal templates
- AI-powered goal suggestions
- Export to PDF
- Calendar view
- Private messaging between users
