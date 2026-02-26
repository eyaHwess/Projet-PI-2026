# Advanced Chatroom Features - Requirements

## Feature Overview
Fonctionnalités avancées pour transformer le chatroom en une plateforme de communication moderne et professionnelle, comparable à WhatsApp, Discord, et Slack.

## User Stories & Acceptance Criteria

### 1. Système "Message lu / non lu" (Priority: HIGH)

#### 1.1 Statut de lecture des messages
**As a** message sender  
**I want to** see if my messages have been read  
**So that** I know if recipients have seen my messages

**Acceptance Criteria:**
- Messages show status: ✔ Envoyé, ✔✔ Lu
- When user opens chatroom, all messages marked as read
- Only show read status for messages sent by current user
- Read receipts stored in database
- Visual indicator (double checkmark) for read messages

#### 1.2 Badge de messages non lus
**As a** user  
**I want to** see unread message count on goal list  
**So that** I know which goals have new activity

**Acceptance Criteria:**
- Badge with unread count on goal cards
- Badge color: red for unread messages
- Count updates in real-time
- Badge disappears when all messages read
- Unread count per goal, not total

---

### 2. Messages en Temps Réel (Priority: HIGH)

#### 2.1 Auto-refresh sans rechargement
**As a** user  
**I want to** see new messages automatically  
**So that** I don't need to refresh the page

**Acceptance Criteria:**
- New messages appear without page reload
- Use AJAX polling (every 2-3 seconds)
- Smooth scroll to new messages
- No flickering or UI jumps
- Preserve scroll position if user scrolling up

---

### 3. Réponses à un Message (Priority: MEDIUM)

#### 3.1 Reply system
**As a** user  
**I want to** reply to specific messages  
**So that** conversations are organized

**Acceptance Criteria:**
- "Reply" button on each message
- Quoted message shown above reply
- Click quoted message to scroll to original
- Visual connection (line) between reply and original
- Database stores parent_message_id

---

### 4. Modifier / Supprimer Message (Priority: MEDIUM)

#### 4.1 Édition de message
**As a** message author  
**I want to** edit my messages  
**So that** I can correct mistakes

**Acceptance Criteria:**
- "Edit" button on own messages
- Inline editing (no modal)
- "Edited" label after modification
- Edit history stored (optional)
- Time limit: 15 minutes after sending

#### 4.2 Suppression améliorée
**As a** message author  
**I want to** delete my messages  
**So that** I can remove unwanted content

**Acceptance Criteria:**
- Already implemented ✅
- Add "Deleted message" placeholder (optional)
- Cascade delete reactions

---

### 5. Messages avec Fichiers (Priority: MEDIUM)

#### 5.1 Upload de fichiers
**As a** user  
**I want to** share files in chat  
**So that** I can exchange documents

**Acceptance Criteria:**
- Upload button (📎)
- Supported types: images (jpg, png), PDF, documents (docx, xlsx)
- Max file size: 10MB
- Preview for images
- Download link for documents
- Files stored in /uploads/chatroom/

---

### 6. Messages Système Automatiques (Priority: LOW)

#### 6.1 Événements automatiques
**As a** system  
**I want to** post automatic messages for events  
**So that** users are informed of changes

**Acceptance Criteria:**
- Message when user joins goal
- Message when goal completed
- Message when progress updated
- System messages have different style (gray, centered)
- No author, no reactions allowed

---

### 7. Indicateur "en train d'écrire..." (Priority: LOW)

#### 7.1 Typing indicator
**As a** user  
**I want to** see when someone is typing  
**So that** I know they're responding

**Acceptance Criteria:**
- "X is typing..." shown below messages
- Appears when user types in input
- Disappears after 3 seconds of inactivity
- Uses AJAX to broadcast typing status
- Multiple users: "X, Y, and Z are typing..."

---

### 8. Recherche dans les Messages (Priority: LOW)

#### 8.1 Search functionality
**As a** user  
**I want to** search messages by keyword  
**So that** I can find past conversations

**Acceptance Criteria:**
- Search bar in chatroom header
- Search by content, author, date
- Highlight matching text
- Jump to message on click
- Show result count

---

### 9. Pagination Intelligente (Priority: MEDIUM)

#### 9.1 Load more messages
**As a** user  
**I want to** load older messages on demand  
**So that** the page loads faster

**Acceptance Criteria:**
- Load last 20 messages initially
- "Load older messages" button at top
- Smooth loading without scroll jump
- Infinite scroll (optional)
- Show loading spinner

---

### 10. Mention Utilisateur (Priority: LOW)

#### 10.1 @mention system
**As a** user  
**I want to** mention other users  
**So that** they receive notifications

**Acceptance Criteria:**
- Type @ to see participant list
- Autocomplete suggestions
- Mentioned user highlighted in message
- Notification sent to mentioned user
- Click mention to see user profile

---

### 11. Thread / Sous-discussion (Priority: LOW)

#### 11.1 Message threads
**As a** user  
**I want to** create sub-discussions  
**So that** conversations stay organized

**Acceptance Criteria:**
- "Thread" button on messages
- Thread opens in sidebar
- Thread count badge on parent message
- Participants can reply in thread
- Thread notifications separate from main chat

---

### 12. Mode Sombre Chat (Priority: LOW)

#### 12.1 Dark theme
**As a** user  
**I want to** use dark mode  
**So that** I can chat comfortably at night

**Acceptance Criteria:**
- Toggle in chatroom header
- Dark background, light text
- Adjusted colors for messages
- Preference saved per user
- Smooth transition

---

## Technical Requirements

### Database Changes

#### New Tables

**message_read_receipt**
```sql
CREATE TABLE message_read_receipt (
    id INT AUTO_INCREMENT PRIMARY KEY,
    message_id INT NOT NULL,
    user_id INT NOT NULL,
    read_at DATETIME NOT NULL,
    FOREIGN KEY (message_id) REFERENCES message(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE,
    UNIQUE KEY unique_read (message_id, user_id)
);
```

**message_file**
```sql
CREATE TABLE message_file (
    id INT AUTO_INCREMENT PRIMARY KEY,
    message_id INT NOT NULL,
    filename VARCHAR(255) NOT NULL,
    original_filename VARCHAR(255) NOT NULL,
    file_type VARCHAR(50) NOT NULL,
    file_size INT NOT NULL,
    uploaded_at DATETIME NOT NULL,
    FOREIGN KEY (message_id) REFERENCES message(id) ON DELETE CASCADE
);
```

#### Modified Tables

**message**
```sql
ALTER TABLE message ADD parent_message_id INT NULL;
ALTER TABLE message ADD is_edited BOOLEAN DEFAULT FALSE;
ALTER TABLE message ADD edited_at DATETIME NULL;
ALTER TABLE message ADD is_system_message BOOLEAN DEFAULT FALSE;
ALTER TABLE message ADD FOREIGN KEY (parent_message_id) REFERENCES message(id) ON DELETE SET NULL;
```

---

## Success Metrics
- 80%+ of messages are read within 5 minutes
- 50%+ of users use reply feature
- 30%+ of messages include files
- Average response time < 30 seconds with real-time updates

---

## Out of Scope (Future)
- Message vocal
- Video calls
- Screen sharing
- End-to-end encryption
- Message translation
