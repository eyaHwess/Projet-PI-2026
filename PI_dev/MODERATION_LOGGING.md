# Content Moderation Logging System

## Overview
This system logs all content moderation violations to a dedicated log file using Monolog. Clean content is never logged, keeping the log file focused on violations only.

## Configuration

### 1. Monolog Channel Setup
**File:** `config/packages/monolog.yaml`

```yaml
monolog:
    channels:
        - moderation # Custom channel for content moderation violations

when@dev:
    monolog:
        handlers:
            moderation:
                type: stream
                path: "%kernel.logs_dir%/moderation.log"
                level: warning
                channels: [moderation]
                formatter: monolog.formatter.json

when@prod:
    monolog:
        handlers:
            moderation:
                type: stream
                path: "%kernel.logs_dir%/moderation.log"
                level: warning
                channels: [moderation]
                formatter: monolog.formatter.json
```

### 2. Service Configuration
**File:** `config/services.yaml`

```yaml
App\Service\Moderation\ModerationService:
    arguments:
        $apiKey: '%env(PERSPECTIVE_API_KEY)%'
        $toxicityThreshold: '%env(float:PERSPECTIVE_TOXICITY_THRESHOLD)%'
        $moderationLogger: '@monolog.logger.moderation'
```

## Log File Location
- **Development:** `var/log/moderation.log`
- **Production:** `var/log/moderation.log`

## Log Entry Structure

### Post Violations (Merged)
When a post (title + content) is flagged, a single log entry is created:

```json
{
  "message": "Content moderation violation detected",
  "context": {
    "user_id": 123,
    "user_email": "user@example.com",
    "entity_type": "post",
    "content_preview": "Title: Bad title here... | Content: Bad content here...",
    "title_length": 15,
    "content_length": 450,
    "scores": {
      "TOXICITY": 0.925,
      "INSULT": 0.923,
      "PROFANITY": 0.895,
      "THREAT": 0.045
    },
    "flagged_attributes": ["TOXICITY", "INSULT", "PROFANITY"],
    "highest_score": 0.925,
    "threshold": 0.7,
    "timestamp": "2026-02-24 15:30:45"
  },
  "level": 300,
  "level_name": "WARNING",
  "channel": "moderation",
  "datetime": "2026-02-24T15:30:45.123456+00:00",
  "extra": []
}
```

### Comment Violations
Comments are logged individually:

```json
{
  "message": "Content moderation violation detected",
  "context": {
    "user_id": 123,
    "user_email": "user@example.com",
    "entity_type": "comment",
    "content_preview": "First 200 characters of the flagged comment...",
    "content_length": 85,
    "scores": {
      "TOXICITY": 0.856,
      "PROFANITY": 0.712
    },
    "flagged_attributes": ["TOXICITY", "PROFANITY"],
    "highest_score": 0.856,
    "threshold": 0.7,
    "timestamp": "2026-02-24 15:30:45"
  }
}
```

## Entity Types

The system tracks different types of content:

| Entity Type | Description |
|------------|-------------|
| `post` | New post (title + content merged) |
| `post_edit` | Edited post (title + content merged) |
| `comment` | New comment |
| `comment_edit` | Edited comment |

**Note:** Post violations are logged as a single entry with merged scores from both title and content analysis.

## Usage in Controllers

### Example: Creating a Post (Merged Analysis)
```php
#[Route('/posts/create', name: 'post_create_ajax', methods: ['POST'])]
public function createFromList(
    Request $request, 
    PostService $postService,
    ModerationService $moderationService
): Response {
    $user = $this->getUser();
    $title = $request->request->get('title');
    $content = $request->request->get('content');

    // Analyze post (title + content) as a single entity
    // This creates only ONE log entry if flagged
    $moderationResult = $moderationService->analyzePost($title, $content, $user, 'post');

    if (!$moderationResult->isClean()) {
        // Single violation logged with merged scores
        return $this->json([
            'success' => false,
            'error' => $moderationResult->getMessage(),
            'moderation' => $moderationResult->toArray()
        ], 400);
    }

    // ... continue with post creation
}
```

### Example: Creating a Comment (Single Analysis)
```php
#[Route('/posts/{id}/comment', name: 'post_comment', methods: ['POST'])]
public function addComment(
    int $id, 
    Request $request, 
    CommentService $commentService,
    ModerationService $moderationService
): Response {
    $user = $this->getUser();
    $content = $request->request->get('content');

    // Analyze comment content
    $moderationResult = $moderationService->analyzeContent($content, $user, 'comment');

    if (!$moderationResult->isClean()) {
        return $this->json([
            'error' => $moderationResult->getMessage(),
            'moderation' => $moderationResult->toArray()
        ], 400);
    }

    // ... continue with comment creation
}
```

## Merging Logic

### How Post Moderation Works

1. **Separate Analysis:** Title and content are analyzed separately by the Perspective API
2. **Score Merging:** For each attribute (TOXICITY, INSULT, etc.), the highest score is kept
3. **Attribute Merging:** Flagged attributes from both analyses are combined (unique values)
4. **Single Log Entry:** Only one log entry is created with merged results
5. **Combined Preview:** Log includes both title and content preview

### Example Merge Scenario

```php
// Title analysis results:
// TOXICITY: 0.85, PROFANITY: 0.75
// Flagged: [TOXICITY, PROFANITY]

// Content analysis results:
// TOXICITY: 0.72, INSULT: 0.90
// Flagged: [TOXICITY, INSULT]

// Merged result:
// TOXICITY: 0.85 (highest)
// PROFANITY: 0.75
// INSULT: 0.90
// Flagged: [TOXICITY, PROFANITY, INSULT] (unique)
// highest_score: 0.90
```

## Best Practices

### 1. Use analyzePost() for Posts
```php
// ✅ GOOD - Single log entry for posts
$result = $moderationService->analyzePost($title, $content, $user, 'post');

// ❌ BAD - Creates duplicate log entries
$titleResult = $moderationService->analyzeContent($title, $user, 'post_title');
$contentResult = $moderationService->analyzeContent($content, $user, 'post_content');
```

### 2. Use analyzeContent() for Comments
```php
// ✅ GOOD - Single field to analyze
$result = $moderationService->analyzeContent($content, $user, 'comment');
```

### 3. Always Pass User Context
```php
// ✅ GOOD - Logs user information
$result = $moderationService->analyzePost($title, $content, $user, 'post');

// ❌ BAD - No user context logged
$result = $moderationService->analyzePost($title, $content);
```

### 4. Use Descriptive Entity Types
```php
// ✅ GOOD - Clear entity type
$moderationService->analyzePost($title, $content, $user, 'post_edit');

// ❌ BAD - Generic entity type
$moderationService->analyzePost($title, $content, $user, 'edit');
```
### 5. Content Preview Format
- **Posts:** Combined format `"Title: ... | Content: ..."`
- **Comments:** First 200 characters
- Automatically truncated to keep logs manageable

### 6. Log Rotation
Configure log rotation in production to prevent the log file from growing indefinitely:

```yaml
# config/packages/monolog.yaml (production)
when@prod:
    monolog:
        handlers:
            moderation:
                type: rotating_file
                path: "%kernel.logs_dir%/moderation.log"
                level: warning
                channels: [moderation]
                formatter: monolog.formatter.json
                max_files: 30  # Keep 30 days of logs
```

## Analyzing Logs

### View Recent Violations
```bash
tail -f var/log/moderation.log | jq '.'
```

### Count Violations by User
```bash
cat var/log/moderation.log | jq -r '.context.user_email' | sort | uniq -c | sort -rn
```

### Find High-Toxicity Content
```bash
cat var/log/moderation.log | jq 'select(.context.scores.TOXICITY > 0.9)'
```

### Violations by Entity Type
```bash
cat var/log/moderation.log | jq -r '.context.entity_type' | sort | uniq -c
```

### Export to CSV for Analysis
```bash
cat var/log/moderation.log | jq -r '[.context.user_id, .context.user_email, .context.entity_type, .context.flagged_attributes | join(";"), .context.timestamp] | @csv' > violations.csv
```

## Security Considerations

1. **Log File Permissions:** Ensure `var/log/moderation.log` is not publicly accessible
2. **PII Protection:** Logs contain user emails - handle according to GDPR/privacy laws
3. **Content Preview:** Only first 200 chars are logged to balance context vs. privacy
4. **Access Control:** Restrict access to moderation logs to authorized personnel only

## Monitoring & Alerts

### Set Up Alerts for Repeat Offenders
Monitor the log file for users with multiple violations:

```bash
# Find users with 5+ violations in the last 24 hours
cat var/log/moderation.log | \
  jq -r 'select(.datetime > (now - 86400 | strftime("%Y-%m-%dT%H:%M:%S"))) | .context.user_email' | \
  sort | uniq -c | awk '$1 >= 5'
```

### Dashboard Integration
Parse the JSON logs and integrate with monitoring tools like:
- Grafana + Loki
- ELK Stack (Elasticsearch, Logstash, Kibana)
- Datadog
- New Relic

## Troubleshooting

### Logs Not Appearing
1. Check file permissions: `ls -la var/log/moderation.log`
2. Verify Monolog configuration: `php bin/console debug:config monolog`
3. Clear cache: `php bin/console cache:clear`
4. Check that violations are actually occurring (test with known bad words)

### Log File Too Large
1. Implement log rotation (see Best Practices above)
2. Archive old logs: `gzip var/log/moderation.log.1`
3. Set up automated cleanup scripts

## Testing

Test the logging system:

```bash
# Create a test post with inappropriate content
# Check the log file
tail -1 var/log/moderation.log | jq '.'
```

Expected output:
```json
{
  "message": "Content moderation violation detected",
  "context": {
    "user_id": 1,
    "user_email": "test@example.com",
    "entity_type": "post_content",
    "content_preview": "Test content with bad words...",
    "scores": {...},
    "flagged_attributes": ["PROFANITY", "TOXICITY"]
  }
}
```

## Compliance & Retention

- **GDPR:** Logs contain personal data (user emails) - document retention policy
- **Retention Period:** Recommended 90 days for moderation logs
- **Right to Erasure:** Implement process to remove user data from logs upon request
- **Data Minimization:** Only essential data is logged (no full content, only preview)
