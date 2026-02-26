# Implementation Plan: Advanced Chatroom Features

## Overview

This implementation plan transforms the existing Symfony chatroom into a modern communication platform with 12 advanced features. The plan follows an incremental approach, building core infrastructure first, then adding features in priority order (HIGH → MEDIUM → LOW). Each task includes property-based testing to validate correctness properties defined in the design document.

## Tasks

- [ ] 1. Database schema updates and entity enhancements
  - [ ] 1.1 Create migration for Message entity enhancements
    - Add `is_system_message`, `system_event_type`, `is_thread_parent`, `thread_reply_count` columns
    - Add foreign key for `reply_to` relationship if not exists
    - _Requirements: 4.5, 8.1, 13.3_
  
  - [ ] 1.2 Create TypingStatus entity and migration
    - Create entity with chatroom, user, lastTypingAt fields
    - Add unique constraint on (chatroom_id, user_id)
    - Generate migration file
    - _Requirements: 9.1_
  
  - [ ] 1.3 Add dark mode preference to User entity
    - Add `dark_mode_enabled` boolean column with default false
    - Generate migration file
    - _Requirements: 14.4_
  
  - [ ] 1.4 Update Message entity class with new fields
    - Add properties for system messages, threads, reply relationships
    - Add getter/setter methods
    - Update constructor defaults
    - _Requirements: 4.5, 8.1, 13.3_

- [ ] 2. Core service layer - ReadReceiptService
  - [ ] 2.1 Implement ReadReceiptService with read tracking methods
    - Create service class with dependency injection
    - Implement `markAsRead()`, `markAllAsRead()`, `getReadCount()`, `hasUserRead()`
    - Implement `getReadStatus()` returning 'sent' or 'read'
    - Implement `getUnreadCount()` for chatroom/user combination
    - _Requirements: 1.1, 1.2, 1.3, 1.4, 2.1_
  
  - [ ]* 2.2 Write property test for read receipt creation
    - **Property 2: Automatic Read Receipt Creation**
    - **Validates: Requirements 1.2**
  
  - [ ]* 2.3 Write property test for read status visibility
    - **Property 3: Read Status Visibility Restriction**
    - **Validates: Requirements 1.3**
  
  - [ ]* 2.4 Write property test for read receipt persistence
    - **Property 4: Read Receipt Persistence**
    - **Validates: Requirements 1.4**

- [ ] 3. Core service layer - MessageService enhancements
  - [ ] 3.1 Enhance MessageService with reply and system message support
    - Add `createMessage()` with optional replyTo parameter
    - Add `createSystemMessage()` for event-triggered messages
    - Add `canEdit()` and `canDelete()` permission checks
    - Implement 15-minute edit time limit validation
    - _Requirements: 4.5, 5.1, 5.5, 8.1_
  
  - [ ]* 3.2 Write property test for edit time limit enforcement
    - **Property 14: Edit Time Limit Enforcement**
    - **Validates: Requirements 5.5**
  
  - [ ]* 3.3 Write property test for reply relationship persistence
    - **Property 11: Reply Relationship Persistence**
    - **Validates: Requirements 4.5**
  
  - [ ]* 3.4 Write property test for cascade delete reactions
    - **Property 15: Cascade Delete Reactions**
    - **Validates: Requirements 6.3**

- [ ] 4. File upload enhancements with validation
  - [ ] 4.1 Implement file type and size validation in MessageService
    - Add validation for supported types (jpg, png, pdf, docx, xlsx)
    - Add 10MB size limit check
    - Return appropriate error messages for validation failures
    - _Requirements: 7.2, 7.3_
  
  - [ ]* 4.2 Write property test for file type validation
    - **Property 16: File Type Validation**
    - **Validates: Requirements 7.2**
  
  - [ ]* 4.3 Write property test for file size validation
    - **Property 17: File Size Validation**
    - **Validates: Requirements 7.3**
  
  - [ ]* 4.4 Write property test for file storage location
    - **Property 19: File Storage Location**
    - **Validates: Requirements 7.6**

- [ ] 5. Checkpoint - Core services complete
  - Ensure all tests pass, ask the user if questions arise.

- [ ] 6. Real-time messaging - AJAX polling endpoints
  - [ ] 6.1 Create fetch endpoint for incremental message updates
    - Add `/chatroom/{goalId}/fetch` route in ChatroomController
    - Accept `lastMessageId` parameter
    - Return JSON with messages after lastMessageId
    - Include read receipt data in response
    - _Requirements: 3.1, 3.2_
  
  - [ ] 6.2 Implement automatic read receipt creation on fetch
    - Call ReadReceiptService.markAllAsRead() when messages fetched
    - Only create receipts for messages user didn't author
    - _Requirements: 1.2_
  
  - [ ]* 6.3 Write property test for real-time message fetching
    - **Property 7: Real-Time Message Fetching**
    - **Validates: Requirements 3.1**

- [ ] 7. Real-time messaging - Frontend JavaScript
  - [ ] 7.1 Implement AJAX polling in chatroom template
    - Create JavaScript polling function (2-3 second interval)
    - Track lastMessageId in client state
    - Append new messages to DOM without page reload
    - Implement smooth scroll to new messages
    - _Requirements: 3.1, 3.2, 3.3_
  
  - [ ] 7.2 Implement scroll position preservation
    - Detect if user scrolled up from bottom
    - Preserve scroll position when new messages arrive
    - Only auto-scroll if user at bottom
    - _Requirements: 3.5_
  
  - [ ]* 7.3 Write property test for scroll position preservation
    - **Property 8: Scroll Position Preservation**
    - **Validates: Requirements 3.5**

- [ ] 8. Read receipt UI - Visual indicators
  - [ ] 8.1 Update message template with read status indicators
    - Add ✔ (single checkmark) for sent messages
    - Add ✔✔ (double checkmark) for read messages
    - Only show indicators for current user's messages
    - Use ReadReceiptService.getReadStatus() to determine state
    - _Requirements: 1.1, 1.3, 1.5_
  
  - [ ]* 8.2 Write property test for read receipt visual indicators
    - **Property 1: Read Receipt Visual Indicators**
    - **Validates: Requirements 1.1, 1.5**

- [ ] 9. Unread badge system
  - [ ] 9.1 Add unread count calculation to goal list
    - Update goal list controller to fetch unread counts
    - Use ReadReceiptService.getUnreadCount() per goal
    - Pass unread counts to template
    - _Requirements: 2.1, 2.5_
  
  - [ ] 9.2 Implement unread badge UI in goal cards
    - Add red badge with count to goal card template
    - Hide badge when count is zero
    - Update badge via AJAX polling on goal list page
    - _Requirements: 2.2, 2.3, 2.4_
  
  - [ ]* 9.3 Write property test for unread badge display
    - **Property 5: Unread Badge Display**
    - **Validates: Requirements 2.1, 2.2, 2.3, 2.4**
  
  - [ ]* 9.4 Write property test for unread count isolation
    - **Property 6: Unread Count Isolation**
    - **Validates: Requirements 2.5**

- [ ] 10. Reply system implementation
  - [ ] 10.1 Add reply button and UI to messages
    - Add "Reply" button to each message
    - Create reply form with quoted parent message preview
    - Implement click handler to populate reply form
    - _Requirements: 4.1, 4.2_
  
  - [ ] 10.2 Implement reply message rendering
    - Show quoted parent message above reply
    - Add visual connection line between reply and parent
    - Make quoted message clickable to scroll to original
    - _Requirements: 4.2, 4.3, 4.4_
  
  - [ ] 10.3 Wire reply creation to MessageService
    - Pass replyTo parameter when creating message
    - Store parent_message_id in database
    - _Requirements: 4.5_
  
  - [ ]* 10.4 Write property test for reply message rendering
    - **Property 9: Reply Message Rendering**
    - **Validates: Requirements 4.1, 4.2, 4.4**
  
  - [ ]* 10.5 Write property test for reply click navigation
    - **Property 10: Reply Click Navigation**
    - **Validates: Requirements 4.3**

- [ ] 11. Message editing enhancements
  - [ ] 11.1 Update edit functionality with time limit
    - Add 15-minute time limit check in MessageController
    - Return 403 error if time limit exceeded
    - Update isEdited flag and editedAt timestamp
    - _Requirements: 5.5_
  
  - [ ] 11.2 Add "Edited" label to modified messages
    - Update message template to show "Edited" indicator
    - Display editedAt timestamp on hover
    - _Requirements: 5.3_
  
  - [ ]* 11.3 Write property test for edit button visibility
    - **Property 12: Edit Button Visibility**
    - **Validates: Requirements 5.1**
  
  - [ ]* 11.4 Write property test for edited label display
    - **Property 13: Edited Label Display**
    - **Validates: Requirements 5.3**

- [ ] 12. Checkpoint - High priority features complete
  - Ensure all tests pass, ask the user if questions arise.

- [ ] 13. System message automation
  - [ ] 13.1 Create SystemMessageService for event handling
    - Create service with methods for each event type
    - Implement message templates for join, complete, progress events
    - Use MessageService.createSystemMessage() for creation
    - _Requirements: 8.1, 8.2, 8.3_
  
  - [ ] 13.2 Integrate system messages into goal events
    - Add system message creation to user join flow
    - Add system message creation to goal completion flow
    - Add system message creation to progress update flow
    - _Requirements: 8.1, 8.2, 8.3_
  
  - [ ] 13.3 Implement system message rendering
    - Create distinct template styling (gray, centered)
    - Hide author information
    - Disable reactions and reply buttons
    - _Requirements: 8.4, 8.5_
  
  - [ ]* 13.4 Write property test for system message creation
    - **Property 20: System Message Creation for Events**
    - **Validates: Requirements 8.1, 8.2, 8.3**
  
  - [ ]* 13.5 Write property test for system message rendering
    - **Property 21: System Message Rendering**
    - **Validates: Requirements 8.4, 8.5**

- [ ] 14. File upload UI improvements
  - [ ] 14.1 Enhance file attachment rendering
    - Add image preview for jpg/png attachments
    - Add download link for pdf/docx/xlsx attachments
    - Display file size and type information
    - _Requirements: 7.4, 7.5_
  
  - [ ]* 14.2 Write property test for attachment rendering by type
    - **Property 18: Attachment Rendering by Type**
    - **Validates: Requirements 7.4, 7.5**

- [ ] 15. Typing indicator system
  - [ ] 15.1 Create TypingIndicatorService
    - Implement setTyping(), getTypingUsers(), clearTyping() methods
    - Use TypingStatus entity for temporary storage
    - Implement 3-second timeout logic
    - _Requirements: 9.1, 9.3_
  
  - [ ] 15.2 Create typing status AJAX endpoint
    - Add `/chatroom/{goalId}/typing` route in ChatroomController
    - Accept typing status updates from client
    - Return list of currently typing users
    - _Requirements: 9.4_
  
  - [ ] 15.3 Implement typing indicator UI
    - Add typing indicator display below messages
    - Show "X is typing..." for single user
    - Show "X, Y, and Z are typing..." for multiple users
    - Update via AJAX polling
    - _Requirements: 9.1, 9.2, 9.5_
  
  - [ ]* 15.4 Write property test for typing indicator display
    - **Property 22: Typing Indicator Display**
    - **Validates: Requirements 9.1, 9.2, 9.5**
  
  - [ ]* 15.5 Write property test for typing indicator timeout
    - **Property 23: Typing Indicator Timeout**
    - **Validates: Requirements 9.3**

- [ ] 16. Message search functionality
  - [ ] 16.1 Create SearchService with filtering logic
    - Implement searchMessages() with content, author, date filters
    - Implement highlightMatches() for result highlighting
    - Use Doctrine QueryBuilder for efficient queries
    - _Requirements: 10.2, 10.3_
  
  - [ ] 16.2 Create search endpoint and UI
    - Add `/chatroom/{goalId}/search` route in ChatroomController
    - Add search bar to chatroom header
    - Display search results with highlighting
    - Show result count
    - _Requirements: 10.1, 10.3, 10.5_
  
  - [ ] 16.3 Implement search result navigation
    - Make search results clickable
    - Scroll to message on click
    - Highlight target message temporarily
    - _Requirements: 10.4_
  
  - [ ]* 16.4 Write property test for message search filtering
    - **Property 24: Message Search Filtering**
    - **Validates: Requirements 10.2, 10.3, 10.5**
  
  - [ ]* 16.5 Write property test for search result navigation
    - **Property 25: Search Result Navigation**
    - **Validates: Requirements 10.4**

- [ ] 17. Smart pagination implementation
  - [ ] 17.1 Implement initial message load limit
    - Modify chatroom controller to load last 20 messages
    - Use Doctrine query with LIMIT and ORDER BY
    - _Requirements: 11.1_
  
  - [ ] 17.2 Create load-more endpoint
    - Add `/chatroom/{goalId}/load-more` route
    - Accept beforeMessageId parameter
    - Return 20 older messages
    - Include loading spinner in UI
    - _Requirements: 11.2, 11.4_
  
  - [ ] 17.3 Implement scroll position preservation on load
    - Calculate scroll offset before loading
    - Restore scroll position after DOM update
    - Ensure no visual jump
    - _Requirements: 11.3_
  
  - [ ]* 17.4 Write property test for initial message load count
    - **Property 26: Initial Message Load Count**
    - **Validates: Requirements 11.1**
  
  - [ ]* 17.5 Write property test for pagination scroll preservation
    - **Property 27: Pagination Scroll Preservation**
    - **Validates: Requirements 11.3**

- [ ] 18. Checkpoint - Medium priority features complete
  - Ensure all tests pass, ask the user if questions arise.

- [ ] 19. User mention system
  - [ ] 19.1 Create NotificationService for mentions
    - Implement extractMentions() to parse @username patterns
    - Implement notifyMentionedUsers() to send notifications
    - Implement notifyReply() for reply notifications
    - _Requirements: 12.3, 12.4_
  
  - [ ] 19.2 Implement mention autocomplete UI
    - Detect @ character in message input
    - Show participant list dropdown
    - Filter suggestions based on partial input
    - Insert selected username on click
    - _Requirements: 12.1, 12.2_
  
  - [ ] 19.3 Implement mention highlighting and navigation
    - Highlight @username in rendered messages
    - Make mentions clickable to user profile
    - Apply distinct styling to mentions
    - _Requirements: 12.3, 12.5_
  
  - [ ] 19.4 Wire mention notifications to message creation
    - Extract mentions from message content
    - Send notifications to mentioned users
    - _Requirements: 12.4_
  
  - [ ]* 19.5 Write property test for mention autocomplete triggering
    - **Property 28: Mention Autocomplete Triggering**
    - **Validates: Requirements 12.1**
  
  - [ ]* 19.6 Write property test for mention autocomplete filtering
    - **Property 29: Mention Autocomplete Filtering**
    - **Validates: Requirements 12.2**
  
  - [ ]* 19.7 Write property test for mention highlighting
    - **Property 30: Mention Highlighting**
    - **Validates: Requirements 12.3**
  
  - [ ]* 19.8 Write property test for mention notification
    - **Property 31: Mention Notification**
    - **Validates: Requirements 12.4**
  
  - [ ]* 19.9 Write property test for mention click navigation
    - **Property 32: Mention Click Navigation**
    - **Validates: Requirements 12.5**

- [ ] 20. Thread system implementation
  - [ ] 20.1 Add thread button and sidebar UI
    - Add "Thread" button to messages
    - Create sidebar template for thread view
    - Implement open/close sidebar functionality
    - _Requirements: 13.1, 13.2_
  
  - [ ] 20.2 Implement thread reply functionality
    - Create thread reply form in sidebar
    - Use MessageService with replyTo parameter
    - Update thread_reply_count on parent message
    - _Requirements: 13.4_
  
  - [ ] 20.3 Add thread count badge to parent messages
    - Display reply count badge on messages with threads
    - Update count in real-time
    - _Requirements: 13.3_
  
  - [ ] 20.4 Implement thread-specific notifications
    - Create separate notification type for thread replies
    - Use NotificationService for thread notifications
    - _Requirements: 13.5_
  
  - [ ]* 20.5 Write property test for thread sidebar display
    - **Property 33: Thread Sidebar Display**
    - **Validates: Requirements 13.2**
  
  - [ ]* 20.6 Write property test for thread reply count badge
    - **Property 34: Thread Reply Count Badge**
    - **Validates: Requirements 13.3**
  
  - [ ]* 20.7 Write property test for thread reply functionality
    - **Property 35: Thread Reply Functionality**
    - **Validates: Requirements 13.4**
  
  - [ ]* 20.8 Write property test for thread notification separation
    - **Property 36: Thread Notification Separation**
    - **Validates: Requirements 13.5**

- [ ] 21. Dark mode implementation
  - [ ] 21.1 Create dark mode CSS stylesheet
    - Define dark mode color variables
    - Create dark theme styles for chatroom
    - Adjust message colors for dark background
    - Ensure proper contrast ratios
    - _Requirements: 14.2, 14.3_
  
  - [ ] 21.2 Implement dark mode toggle
    - Add toggle button to chatroom header
    - Create endpoint to save preference
    - Apply dark mode class to body/container
    - Implement smooth transition animation
    - _Requirements: 14.1, 14.4_
  
  - [ ] 21.3 Load user's dark mode preference on page load
    - Check user's darkModeEnabled property
    - Apply dark mode class if enabled
    - _Requirements: 14.4_
  
  - [ ]* 21.4 Write property test for dark mode styling
    - **Property 37: Dark Mode Styling**
    - **Validates: Requirements 14.2, 14.3**
  
  - [ ]* 21.5 Write property test for dark mode preference persistence
    - **Property 38: Dark Mode Preference Persistence**
    - **Validates: Requirements 14.4**

- [ ] 22. Error handling and validation
  - [ ] 22.1 Implement comprehensive error handling
    - Add validation error messages for empty messages
    - Add file upload error handling (type, size, upload failure)
    - Add permission error messages (403 responses)
    - Add AJAX error handling with retry logic
    - _Requirements: All features_
  
  - [ ] 22.2 Add user-friendly error displays
    - Create error notification UI component
    - Display validation errors inline
    - Show network error messages
    - Implement retry mechanisms for transient failures
    - _Requirements: All features_

- [ ] 23. Final integration and polish
  - [ ] 23.1 Wire all components together
    - Ensure all AJAX endpoints are connected
    - Verify all event listeners are registered
    - Test cross-feature interactions
    - _Requirements: All features_
  
  - [ ] 23.2 Performance optimization
    - Add database indexes for frequently queried fields
    - Optimize AJAX polling intervals
    - Implement query result caching where appropriate
    - _Requirements: All features_
  
  - [ ] 23.3 Accessibility improvements
    - Add ARIA labels to interactive elements
    - Ensure keyboard navigation works
    - Test screen reader compatibility
    - Add focus indicators
    - _Requirements: All features_

- [ ] 24. Final checkpoint - All features complete
  - Ensure all tests pass, ask the user if questions arise.

## Notes

- Tasks marked with `*` are optional property-based tests and can be skipped for faster MVP
- Each task references specific requirements for traceability
- Property tests validate universal correctness properties from the design document
- Implementation uses PHP 8.2+ with Symfony 7.4 framework
- PHPUnit with Eris library is used for property-based testing
- All property tests should run minimum 100 iterations
- Checkpoints ensure incremental validation at major milestones
- Features are implemented in priority order: HIGH → MEDIUM → LOW
