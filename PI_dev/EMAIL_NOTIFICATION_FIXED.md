# ✅ Email Notifications - FIXED

## What Was the Problem?

Emails were being **queued** in the database (`messenger_messages` table) but never sent to MailHog because:
- Symfony Messenger was configured to send emails asynchronously
- No worker was running to consume the queued messages

## What Was Fixed?

Changed `config/packages/messenger.yaml` to send emails **synchronously** (immediately):
```yaml
# Before (queued):
Symfony\Component\Mailer\Messenger\SendEmailMessage: async

# After (immediate):
# Symfony\Component\Mailer\Messenger\SendEmailMessage: async  (commented out)
```

## How to Test

### 1. Make sure MailHog is running
- Download from: https://github.com/mailhog/MailHog/releases/latest
- Run `MailHog.exe` (double-click)
- Open http://localhost:8025 in your browser

### 2. Test the notifications

**Test 1: User creates a reclamation**
1. Login as a user
2. Click the floating bubble (bottom-right)
3. Fill out and submit a reclamation
4. Check MailHog - you should see a confirmation email

**Test 2: Admin responds to reclamation**
1. Login as admin
2. Go to "Réclamations" in sidebar
3. Click "Répondre" on a reclamation
4. Write a response and submit
5. Check MailHog - the user should receive a response email

## Email Types

### 1. Confirmation Email (User)
- **Sent when**: User creates a new reclamation
- **To**: User's email address
- **Subject**: "Confirmation de votre réclamation"
- **Contains**: Reclamation details, type, date, content

### 2. Response Email (User)
- **Sent when**: Admin responds to a reclamation
- **To**: User's email address
- **Subject**: "Réponse à votre réclamation"
- **Contains**: Admin's response, original reclamation

### 3. Admin Notification
- **Sent when**: New reclamation is created
- **To**: Admin email (configured in notifier.yaml)
- **Type**: Notification (not full email)

## Configuration Files

- **MAILER_DSN**: `PI_dev/.env.local` → `smtp://localhost:1025`
- **Sender Email**: `PI_dev/config/services.yaml` → `noreply@buildify.com`
- **Messenger Config**: `PI_dev/config/packages/messenger.yaml`

## Troubleshooting

### Emails still not appearing in MailHog?

1. **Check MailHog is running**:
   - Open http://localhost:8025
   - Should see MailHog interface

2. **Check Symfony logs**:
   ```bash
   php bin/console messenger:failed:show
   ```

3. **Clear cache**:
   ```bash
   php bin/console cache:clear
   ```

4. **Check database for queued messages**:
   ```sql
   SELECT * FROM messenger_messages;
   ```
   - Should be empty now (emails sent immediately)

### Want to use async emails again?

Uncomment the line in `messenger.yaml` and run:
```bash
php bin/console messenger:consume async -vv
```

This will process queued emails in the background.
