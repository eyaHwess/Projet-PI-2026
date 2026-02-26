# Goal System Enhancements - Implementation Tasks

## Phase 1: Database & Entities (Priority: HIGH)

### 1. Update Goal Entity
- [ ] 1.1 Add `progress` property (int, default 0)
- [ ] 1.2 Add `completedAt` property (DateTime, nullable)
- [ ] 1.3 Add getter/setter methods
- [ ] 1.4 Add `isCompleted()` helper method

### 2. Update User Entity
- [ ] 2.1 Add `points` property (int, default 0)
- [ ] 2.2 Add `badges` OneToMany relationship
- [ ] 2.3 Add `notifications` OneToMany relationship
- [ ] 2.4 Add getter/setter methods
- [ ] 2.5 Add `addPoints(int $points)` helper method

### 3. Update GoalParticipation Entity
- [ ] 3.1 Add `role` property (string, default 'participant')
- [ ] 3.2 Add getter/setter methods
- [ ] 3.3 Add `isCreator()`, `isCoLeader()` helper methods

### 4. Update Message Entity
- [ ] 4.1 Add `isPinned` property (bool, default false)
- [ ] 4.2 Add `reactions` OneToMany relationship
- [ ] 4.3 Add getter/setter methods
- [ ] 4.4 Add `getReactionCount(string $type)` helper method
- [ ] 4.5 Add `hasUserReacted(User $user, string $type)` helper method

### 5. Create MessageReaction Entity
- [ ] 5.1 Create entity class
- [ ] 5.2 Add `message` ManyToOne relationship
- [ ] 5.3 Add `user` ManyToOne relationship
- [ ] 5.4 Add `reactionType` property (string)
- [ ] 5.5 Add `createdAt` property (DateTime)
- [ ] 5.6 Create repository class

### 6. Create Badge Entity
- [ ] 6.1 Create entity class
- [ ] 6.2 Add `user` ManyToOne relationship
- [ ] 6.3 Add `badgeType` property (string)
- [ ] 6.4 Add `earnedAt` property (DateTime)
- [ ] 6.5 Create repository class

### 7. Create Notification Entity
- [ ] 7.1 Create entity class
- [ ] 7.2 Add `user` ManyToOne relationship
- [ ] 7.3 Add `type` property (string)
- [ ] 7.4 Add `content` property (text)
- [ ] 7.5 Add `relatedGoalId` property (int, nullable)
- [ ] 7.6 Add `isRead` property (bool, default false)
- [ ] 7.7 Add `createdAt` property (DateTime)
- [ ] 7.8 Create repository class

### 8. Create Database Migrations
- [ ] 8.1 Generate migration for Goal updates
- [ ] 8.2 Generate migration for User updates
- [ ] 8.3 Generate migration for GoalParticipation updates
- [ ] 8.4 Generate migration for Message updates
- [ ] 8.5 Generate migration for MessageReaction table
- [ ] 8.6 Generate migration for Badge table
- [ ] 8.7 Generate migration for Notification table
- [ ] 8.8 Run all migrations
- [ ] 8.9 Verify database schema

---

## Phase 2: Services & Business Logic (Priority: HIGH)

### 9. Create GoalPermissionService
- [ ] 9.1 Create service class
- [ ] 9.2 Implement `canUpdateProgress(User, Goal)` method
- [ ] 9.3 Implement `canDeleteGoal(User, Goal)` method
- [ ] 9.4 Implement `canPinMessage(User, Goal)` method
- [ ] 9.5 Implement `canRemoveParticipant(User, Goal)` method
- [ ] 9.6 Implement `canPromoteToCoLeader(User, Goal)` method
- [ ] 9.7 Register service in services.yaml

### 10. Create BadgeService
- [ ] 10.1 Create service class
- [ ] 10.2 Implement `checkAndAwardBadges(User)` method
- [ ] 10.3 Implement `checkMotivatedBadge(User)` (10+ messages)
- [ ] 10.4 Implement `checkLeaderBadge(User)` (3+ goals created)
- [ ] 10.5 Implement `checkFaithfulBadge(User)` (5+ goals joined)
- [ ] 10.6 Implement `checkChampionBadge(User)` (5+ goals completed)
- [ ] 10.7 Implement `checkSocialBadge(User)` (50+ reactions received)
- [ ] 10.8 Register service in services.yaml

### 11. Create NotificationService
- [ ] 11.1 Create service class
- [ ] 11.2 Implement `notifyGoalParticipants(Goal, type, content)` method
- [ ] 11.3 Implement `notifyUser(User, type, content)` method
- [ ] 11.4 Implement `notifyNewParticipant(Goal, User)` method
- [ ] 11.5 Implement `notifyGoalCompleted(Goal)` method
- [ ] 11.6 Implement `notifyPromotion(User, Goal)` method
- [ ] 11.7 Implement `notifyBadgeEarned(User, Badge)` method
- [ ] 11.8 Register service in services.yaml

### 12. Create PointsService
- [ ] 12.1 Create service class
- [ ] 12.2 Implement `awardPoints(User, int, reason)` method
- [ ] 12.3 Implement constants for point values
- [ ] 12.4 Register service in services.yaml

---

## Phase 3: Système de Progression (Priority: HIGH)

### 13. Update GoalController for Progress
- [ ] 13.1 Create `updateProgress` action
- [ ] 13.2 Add permission check (creator or co-leader)
- [ ] 13.3 Validate progress value (0-100)
- [ ] 13.4 Update goal progress
- [ ] 13.5 If progress = 100, set completedAt
- [ ] 13.6 Award completion points to creator
- [ ] 13.7 Send notifications to participants
- [ ] 13.8 Add flash message
- [ ] 13.9 Redirect to goal detail page

### 14. Create Progress Update Form
- [ ] 14.1 Create ProgressType form class
- [ ] 14.2 Add progress field (IntegerType, 0-100)
- [ ] 14.3 Add validation constraints

### 15. Update Goal List Template
- [ ] 15.1 Add progress bar component
- [ ] 15.2 Add progress percentage text
- [ ] 15.3 Add color logic (red/yellow/blue/green)
- [ ] 15.4 Add completion badge for 100% goals
- [ ] 15.5 Add CSS styles for progress bar

### 16. Update Goal Detail Template
- [ ] 16.1 Add progress bar component
- [ ] 16.2 Add "Update Progress" button (creator/co-leader only)
- [ ] 16.3 Add progress update modal/form
- [ ] 16.4 Add completion badge
- [ ] 16.5 Add completion date display

---

## Phase 4: Rôles dans le Goal (Priority: HIGH)

### 17. Update Goal Creation Logic
- [ ] 17.1 Set creator role automatically on goal creation
- [ ] 17.2 Update GoalController::new() method
- [ ] 17.3 Test role assignment

### 18. Add Role Management Actions
- [ ] 18.1 Create `promoteToCoLeader` action in GoalController
- [ ] 18.2 Add permission check (creator only)
- [ ] 18.3 Update participant role
- [ ] 18.4 Send notification to promoted user
- [ ] 18.5 Add flash message
- [ ] 18.6 Create `demoteFromCoLeader` action
- [ ] 18.7 Add permission check (creator only)
- [ ] 18.8 Update participant role back to participant

### 19. Update Goal Detail Template for Roles
- [ ] 19.1 Add role badges in participant list
- [ ] 19.2 Add "Promote to Co-Leader" button (creator only)
- [ ] 19.3 Add "Demote" button (creator only)
- [ ] 19.4 Style role badges (gold for creator, silver for co-leader)

### 20. Update Chatroom Template for Roles
- [ ] 20.1 Add role badges next to usernames
- [ ] 20.2 Show pin button only to creator/co-leader
- [ ] 20.3 Update participant list with role indicators

### 21. Implement Permission Checks
- [ ] 21.1 Add permission check to deleteGoal action
- [ ] 21.2 Add permission check to updateProgress action
- [ ] 21.3 Add permission check to pinMessage action
- [ ] 21.4 Add permission check to removeParticipant action
- [ ] 21.5 Hide UI elements based on permissions

---

## Phase 5: Réactions aux Messages (Priority: HIGH)

### 22. Create Message Reaction Controller
- [ ] 22.1 Create `reactToMessage` action in GoalController
- [ ] 22.2 Validate reaction type (like, clap, fire, heart)
- [ ] 22.3 Check if reaction already exists
- [ ] 22.4 Toggle reaction (add if not exists, remove if exists)
- [ ] 22.5 Award points to message author (+2)
- [ ] 22.6 Check and award badges
- [ ] 22.7 Return JSON response for AJAX

### 23. Update Chatroom Template for Reactions
- [ ] 23.1 Add reaction buttons under each message
- [ ] 23.2 Display reaction counts
- [ ] 23.3 Highlight user's reactions
- [ ] 23.4 Add hover tooltip showing who reacted
- [ ] 23.5 Add CSS styles for reaction buttons

### 24. Add JavaScript for Reactions
- [ ] 24.1 Create reaction click handler
- [ ] 24.2 Send AJAX request to toggle reaction
- [ ] 24.3 Update UI without page reload
- [ ] 24.4 Update reaction counts
- [ ] 24.5 Add animation on reaction click

---

## Phase 6: Gamification (Priority: MEDIUM)

### 25. Implement Points System
- [ ] 25.1 Award points on goal creation (+50)
- [ ] 25.2 Award points on goal join (+10)
- [ ] 25.3 Award points on message send (+5)
- [ ] 25.4 Award points on goal completion (+100)
- [ ] 25.5 Award points on reaction received (+2)
- [ ] 25.6 Display points in user profile
- [ ] 25.7 Display points in participant list

### 26. Implement Badge System
- [ ] 26.1 Check badges after each point-earning action
- [ ] 26.2 Award "Motivé" badge (10+ messages)
- [ ] 26.3 Award "Leader" badge (3+ goals created)
- [ ] 26.4 Award "Fidèle" badge (5+ goals joined)
- [ ] 26.5 Award "Champion" badge (5+ goals completed)
- [ ] 26.6 Award "Social" badge (50+ reactions)
- [ ] 26.7 Send notification on badge earned
- [ ] 26.8 Display badges in user profile
- [ ] 26.9 Display badges in chatroom

### 27. Create Leaderboard Page
- [ ] 27.1 Create `leaderboard` action in new LeaderboardController
- [ ] 27.2 Query top 10 users by points
- [ ] 27.3 Create leaderboard template
- [ ] 27.4 Add podium design for top 3
- [ ] 27.5 Add list design for ranks 4-10
- [ ] 27.6 Display rank, avatar, name, points, badges
- [ ] 27.7 Add filter tabs (All Time, This Month, This Week)
- [ ] 27.8 Implement filter logic
- [ ] 27.9 Add link to leaderboard in navbar

---

## Phase 7: Message Épinglé (Priority: MEDIUM)

### 28. Implement Pin Message Feature
- [ ] 28.1 Create `pinMessage` action in GoalController
- [ ] 28.2 Add permission check (creator/co-leader)
- [ ] 28.3 Unpin any existing pinned message in chatroom
- [ ] 28.4 Pin selected message
- [ ] 28.5 Add flash message
- [ ] 28.6 Create `unpinMessage` action
- [ ] 28.7 Add permission check
- [ ] 28.8 Unpin message

### 29. Update Chatroom Template for Pinned Messages
- [ ] 29.1 Display pinned message at top of chatroom
- [ ] 29.2 Add special styling (yellow background, pin icon)
- [ ] 29.3 Add "Unpin" button (creator/co-leader only)
- [ ] 29.4 Add "Pin" button on messages (creator/co-leader only)
- [ ] 29.5 Add CSS styles for pinned message box

---

## Phase 8: Notifications (Priority: MEDIUM)

### 30. Create Notification Controller
- [ ] 30.1 Create NotificationController
- [ ] 30.2 Create `index` action (list notifications)
- [ ] 30.3 Create `markAsRead` action
- [ ] 30.4 Create `markAllAsRead` action
- [ ] 30.5 Create `getUnreadCount` action (AJAX)

### 31. Update Base Template for Notifications
- [ ] 31.1 Add notification icon in navbar
- [ ] 31.2 Add unread count badge
- [ ] 31.3 Add notification dropdown
- [ ] 31.4 Display last 10 notifications
- [ ] 31.5 Add "Mark all as read" button
- [ ] 31.6 Add "View all" link
- [ ] 31.7 Add CSS styles for notification dropdown

### 32. Create Notification Template
- [ ] 32.1 Create notifications list page
- [ ] 32.2 Display all notifications
- [ ] 32.3 Highlight unread notifications
- [ ] 32.4 Add mark as read button
- [ ] 32.5 Add filter (All, Unread)
- [ ] 32.6 Add pagination

### 33. Trigger Notifications
- [ ] 33.1 Send notification on new participant
- [ ] 33.2 Send notification on goal completion
- [ ] 33.3 Send notification on promotion to co-leader
- [ ] 33.4 Send notification on badge earned
- [ ] 33.5 Test all notification triggers

---

## Phase 9: Filtre des Goals (Priority: LOW)

### 34. Implement Goal Filters
- [ ] 34.1 Add filter buttons to goal list template
- [ ] 34.2 Add data attributes to goal cards (progress, creator-id)
- [ ] 34.3 Create JavaScript filter function
- [ ] 34.4 Implement "Tous" filter (show all)
- [ ] 34.5 Implement "Mes Goals" filter (creator = current user)
- [ ] 34.6 Implement "En cours" filter (progress < 100)
- [ ] 34.7 Implement "Terminés" filter (progress = 100)
- [ ] 34.8 Add active state styling for selected filter
- [ ] 34.9 Add CSS styles for filter buttons

---

## Phase 10: Statistiques Personnelles (Priority: LOW)

### 35. Create Stats Controller
- [ ] 35.1 Create StatsController
- [ ] 35.2 Create `dashboard` action
- [ ] 35.3 Calculate goals created count
- [ ] 35.4 Calculate goals joined count
- [ ] 35.5 Calculate completion rate
- [ ] 35.6 Calculate total points
- [ ] 35.7 Calculate messages sent count
- [ ] 35.8 Calculate reactions received count
- [ ] 35.9 Get badges earned list

### 36. Create Stats Template
- [ ] 36.1 Create dashboard template
- [ ] 36.2 Display stat cards (goals, points, messages, etc.)
- [ ] 36.3 Add Chart.js library
- [ ] 36.4 Create bar chart (goals per month)
- [ ] 36.5 Create pie chart (goal status distribution)
- [ ] 36.6 Create line chart (points over time)
- [ ] 36.7 Add date range filter
- [ ] 36.8 Add responsive design
- [ ] 36.9 Add link to stats in navbar

---

## Phase 11: Dark Mode (Priority: LOW)

### 37. Implement Dark Mode
- [ ] 37.1 Define CSS variables for light theme
- [ ] 37.2 Define CSS variables for dark theme
- [ ] 37.3 Update all templates to use CSS variables
- [ ] 37.4 Add dark mode toggle button in navbar
- [ ] 37.5 Create JavaScript toggle function
- [ ] 37.6 Save preference in localStorage
- [ ] 37.7 Load preference on page load
- [ ] 37.8 Add smooth transition between themes
- [ ] 37.9 Test dark mode on all pages

---

## Phase 12: Data Migration & Cleanup (Priority: HIGH)

### 38. Migrate Existing Data
- [ ] 38.1 Set default progress (0) for all existing goals
- [ ] 38.2 Set default role (participant) for all existing participations
- [ ] 38.3 Update creators to 'creator' role
- [ ] 38.4 Set default points (0) for all existing users
- [ ] 38.5 Verify data integrity

---

## Phase 13: Testing (Priority: HIGH)

### 39. Unit Tests
- [ ] 39.1 Test BadgeService::checkAndAwardBadges()
- [ ] 39.2 Test GoalPermissionService methods
- [ ] 39.3 Test NotificationService methods
- [ ] 39.4 Test PointsService methods

### 40. Integration Tests
- [ ] 40.1 Test goal creation with role assignment
- [ ] 40.2 Test progress update with completion
- [ ] 40.3 Test message reaction toggle
- [ ] 40.4 Test badge awarding
- [ ] 40.5 Test notification delivery
- [ ] 40.6 Test permission checks

### 41. Manual Testing
- [ ] 41.1 Test all user flows
- [ ] 41.2 Test permission restrictions
- [ ] 41.3 Test UI responsiveness
- [ ] 41.4 Test dark mode
- [ ] 41.5 Test filters
- [ ] 41.6 Test leaderboard
- [ ] 41.7 Test statistics dashboard

---

## Phase 14: Documentation (Priority: MEDIUM)

### 42. Technical Documentation
- [ ] 42.1 Create UML class diagram (updated with new entities)
- [ ] 42.2 Create sequence diagram: Goal creation with roles
- [ ] 42.3 Create sequence diagram: Message with reactions
- [ ] 42.4 Create sequence diagram: Badge awarding
- [ ] 42.5 Document permission matrix
- [ ] 42.6 Document point system
- [ ] 42.7 Document badge criteria

### 43. User Documentation
- [ ] 43.1 Create user guide for progress tracking
- [ ] 43.2 Create user guide for roles
- [ ] 43.3 Create user guide for reactions
- [ ] 43.4 Create user guide for badges
- [ ] 43.5 Create FAQ document

---

## Phase 15: Polish & Optimization (Priority: LOW)

### 44. UI/UX Improvements
- [ ] 44.1 Add loading spinners
- [ ] 44.2 Add smooth animations
- [ ] 44.3 Add hover effects
- [ ] 44.4 Add toast notifications (replace flash messages)
- [ ] 44.5 Improve mobile responsiveness
- [ ] 44.6 Add empty states
- [ ] 44.7 Add error states

### 45. Performance Optimization
- [ ] 45.1 Add database indexes
- [ ] 45.2 Implement caching for leaderboard
- [ ] 45.3 Optimize queries with JOIN FETCH
- [ ] 45.4 Add pagination to message list
- [ ] 45.5 Lazy load notifications
- [ ] 45.6 Minify CSS/JS

---

## Deployment Checklist

- [ ] Run all database migrations
- [ ] Clear Symfony cache
- [ ] Run data migration scripts
- [ ] Test all features in production
- [ ] Monitor error logs
- [ ] Verify performance metrics
- [ ] Backup database before deployment

---

## Estimated Time

- **Phase 1-2 (Database & Services)**: 6-8 hours
- **Phase 3-5 (Core Features)**: 8-10 hours
- **Phase 6-8 (Gamification & Notifications)**: 6-8 hours
- **Phase 9-11 (UX Features)**: 4-6 hours
- **Phase 12-15 (Testing & Polish)**: 4-6 hours

**Total**: 28-38 hours

---

## Priority for Soutenance

**MUST IMPLEMENT (10-12 hours):**
1. Système de Progression ⭐⭐⭐⭐⭐
2. Rôles dans le Goal ⭐⭐⭐⭐⭐
3. Réactions aux Messages ⭐⭐⭐⭐⭐

**SHOULD IMPLEMENT (6-8 hours):**
4. Système de Points ⭐⭐⭐⭐
5. Badges ⭐⭐⭐⭐
6. Classement ⭐⭐⭐⭐

**NICE TO HAVE (4-6 hours):**
7. Message Épinglé ⭐⭐⭐
8. Notifications ⭐⭐⭐
9. Filtre Goals ⭐⭐⭐
