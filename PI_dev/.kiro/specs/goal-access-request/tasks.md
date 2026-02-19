# Goal Access Request System - Tasks

## Phase 1: Database & Entity (30 min)

- [ ] 1.1 Add `status` field to GoalParticipation entity
  - Add STATUS_PENDING, STATUS_APPROVED, STATUS_REJECTED constants
  - Add status property with default STATUS_APPROVED
  - Add validation in setStatus()

- [ ] 1.2 Add helper methods to GoalParticipation
  - isPending(): bool
  - isApproved(): bool
  - isRejected(): bool

- [ ] 1.3 Create database migration
  - Add status column VARCHAR(20) DEFAULT 'APPROVED'
  - Update existing records to APPROVED

- [ ] 1.4 Execute migration
  - Run php bin/console doctrine:migrations:migrate
  - Verify column added successfully

## Phase 2: Goal Entity Methods (15 min)

- [ ] 2.1 Add getPendingRequests() method to Goal entity
  - Filter goalParticipations by status PENDING
  - Return Collection

- [ ] 2.2 Add getPendingRequestsCount() method
  - Count pending requests
  - Return int

- [ ] 2.3 Add hasUserRequestedAccess() method
  - Check if user has pending request
  - Return bool

## Phase 3: Controller Actions (45 min)

- [ ] 3.1 Modify join() action in GoalController
  - Check for existing participation
  - Create with STATUS_PENDING instead of APPROVED
  - Update flash message
  - Handle PENDING state

- [ ] 3.2 Create approveRequest() action
  - Verify user is ADMIN or OWNER
  - Find pending participation
  - Change status to APPROVED
  - Return JSON for AJAX
  - Add flash message

- [ ] 3.3 Create rejectRequest() action
  - Verify user is ADMIN or OWNER
  - Find pending participation
  - Delete participation
  - Return JSON for AJAX
  - Add flash message

- [ ] 3.4 Update messages() action
  - Check if user has PENDING participation
  - Pass isPending flag to template
  - Handle PENDING state differently from non-member

## Phase 4: Templates - Goal List (30 min)

- [ ] 4.1 Update goal/list.html.twig button logic
  - Check participation status
  - Show "En attente" button if PENDING (disabled)
  - Show "Rejoindre" if no participation
  - Show "Quitter" if APPROVED

- [ ] 4.2 Add CSS for pending button
  - Warning color (yellow/orange)
  - Disabled state styling
  - Clock icon

## Phase 5: Templates - Chatroom (60 min)

- [ ] 5.1 Add pending approval notice
  - Create pending-approval-notice div
  - Clock icon with animation
  - Informative text
  - Yellow/orange theme

- [ ] 5.2 Add CSS for pending notice
  - Gradient background
  - Pulse animation
  - Responsive design

- [ ] 5.3 Update input area logic
  - Hide form if PENDING
  - Show pending notice instead
  - Keep messages visible (read-only)

- [ ] 5.4 Add pending requests section in Group Info
  - New collapsible section
  - List pending requests
  - Show user avatar, name, date
  - Approve/Reject buttons

- [ ] 5.5 Add CSS for pending requests section
  - Request item styling
  - Avatar styling
  - Action buttons (green/red)
  - Hover effects

- [ ] 5.6 Add pending requests badge in header
  - Show count if > 0
  - Only visible to ADMIN/OWNER
  - Yellow/orange badge
  - Pulse animation

- [ ] 5.7 Add CSS for header badge
  - Inline badge styling
  - Animation
  - Responsive

## Phase 6: JavaScript Functions (30 min)

- [ ] 6.1 Create approveRequest() function
  - AJAX POST to approve endpoint
  - Confirmation dialog
  - Success/error handling
  - Page reload on success

- [ ] 6.2 Create rejectRequest() function
  - AJAX POST to reject endpoint
  - Confirmation dialog
  - Success/error handling
  - Page reload on success

- [ ] 6.3 Add event listeners
  - Approve button clicks
  - Reject button clicks
  - Error handling

## Phase 7: Testing (45 min)

- [ ] 7.1 Test request creation
  - Click "Rejoindre" button
  - Verify PENDING status in database
  - Verify flash message
  - Verify button changes to "En attente"

- [ ] 7.2 Test chatroom access with PENDING
  - Access chatroom as pending user
  - Verify pending notice displayed
  - Verify form hidden
  - Verify messages visible

- [ ] 7.3 Test admin view
  - Login as ADMIN/OWNER
  - Verify pending requests section visible
  - Verify badge in header
  - Verify count correct

- [ ] 7.4 Test approval
  - Click approve button
  - Verify confirmation dialog
  - Verify status changes to APPROVED
  - Verify user can now participate
  - Verify flash message

- [ ] 7.5 Test rejection
  - Create new pending request
  - Click reject button
  - Verify confirmation dialog
  - Verify participation deleted
  - Verify user can request again
  - Verify flash message

- [ ] 7.6 Test permissions
  - Try to approve as MEMBER (should fail)
  - Try to reject as MEMBER (should fail)
  - Verify error messages

- [ ] 7.7 Test edge cases
  - Multiple pending requests
  - Approve non-existent request
  - Reject non-existent request
  - Request when already PENDING
  - Request when already APPROVED

## Phase 8: Documentation (15 min)

- [ ] 8.1 Update GUIDE_TEST_COMPLET.md
  - Add access request tests
  - Add approval/rejection tests
  - Add screenshots suggestions

- [ ] 8.2 Create ACCESS_REQUEST_SYSTEM.md
  - Document the feature
  - Add usage examples
  - Add troubleshooting

## Estimated Total Time: 4 hours 30 minutes

## Priority Order
1. Phase 1 (Database) - CRITICAL
2. Phase 2 (Entity methods) - CRITICAL
3. Phase 3 (Controller) - HIGH
4. Phase 4 (Goal list UI) - HIGH
5. Phase 5 (Chatroom UI) - HIGH
6. Phase 6 (JavaScript) - MEDIUM
7. Phase 7 (Testing) - HIGH
8. Phase 8 (Documentation) - LOW

## Dependencies
- Phase 2 depends on Phase 1
- Phase 3 depends on Phase 2
- Phase 4 depends on Phase 3
- Phase 5 depends on Phase 3
- Phase 6 depends on Phase 5
- Phase 7 depends on all previous phases

## Notes
- Test thoroughly after each phase
- Commit after each completed phase
- Keep existing functionality working
- Maintain backward compatibility
