# Google Perspective API - Content Moderation Setup

## Overview
This project uses Google Perspective API to automatically moderate user-generated content (posts and comments) for toxicity, insults, profanity, and threats.

## Features
- ✅ Automatic content analysis before saving to database
- ✅ Checks for: TOXICITY, INSULT, PROFANITY, THREAT
- ✅ Configurable toxicity threshold
- ✅ User-friendly error messages
- ✅ Works for both Posts and Comments
- ✅ Graceful fallback on API errors
- ✅ Detailed logging

## Setup Instructions

### 1. Get Your API Key

1. Go to [Google Perspective API](https://developers.perspectiveapi.com/s/docs-get-started)
2. Click "Get Started"
3. Enable the API in your Google Cloud Console
4. Create credentials (API Key)
5. Copy your API key

### 2. Configure Environment Variables

Edit your `.env` file:

```env
###> google/perspective-api ###
PERSPECTIVE_API_KEY=YOUR_ACTUAL_API_KEY_HERE
PERSPECTIVE_TOXICITY_THRESHOLD=0.7
###< google/perspective-api ###
```

**Threshold Guide:**
- `0.5` - Moderate filtering (catches more content)
- `0.7` - Balanced (recommended)
- `0.9` - Strict (only catches very toxic content)

### 3. Test the Integration

Create a test post with inappropriate content:
```
Title: "Test Post"
Content: "You are an idiot and I hate you"
```

Expected result: Post should be rejected with message:
> "Your content contains inappropriate language (toxic language and insults) and cannot be published. Please revise your message and try again."

## How It Works

### For Posts

```php
// In PostController::create()
$titleResult = $moderationService->analyzeContent($title);
$contentResult = $moderationService->analyzeContent($content);

if (!$titleResult->isClean() || !$contentResult->isClean()) {
    // Show error message, don't save post
    $this->addFlash('error', $result->getMessage());
    return $this->redirectToRoute('post_new');
}

// Content is clean, proceed with creation
$postService->createPost($title, $content, $user);
```

### For Comments

```php
// In PostController::addComment()
$moderationResult = $moderationService->analyzeContent($content);

if (!$moderationResult->isClean()) {
    return $this->json([
        'error' => $moderationResult->getMessage()
    ], 400);
}

// Content is clean, create comment
$comment = $commentService->createComment(...);
```

## API Response Structure

The `ModerationResult` object contains:

```php
[
    'isClean' => true/false,
    'scores' => [
        'TOXICITY' => 0.85,
        'INSULT' => 0.72,
        'PROFANITY' => 0.15,
        'THREAT' => 0.05
    ],
    'flaggedAttributes' => ['TOXICITY', 'INSULT'],
    'message' => 'Your content contains inappropriate language...',
    'highestScore' => 0.85
]
```

## Customizing Threshold

You can set a custom threshold per request:

```php
$moderationService->setThreshold(0.5); // More strict
$result = $moderationService->analyzeContent($content);
```

## Error Handling

The service handles errors gracefully:

1. **API Timeout**: Content is allowed, error is logged
2. **Invalid API Key**: Content is allowed, error is logged
3. **Network Error**: Content is allowed, error is logged

This ensures your app continues working even if the API is down.

## Monitoring

Check logs for moderation activity:

```bash
tail -f var/log/dev.log | grep "Perspective API"
```

## Testing Different Content

### Clean Content (Should Pass)
```
"This is a great post about technology and innovation."
```

### Toxic Content (Should Fail)
```
"You're an idiot and I hate you."
```

### Profanity (Should Fail)
```
"This is f***ing terrible."
```

### Threats (Should Fail)
```
"I'm going to hurt you."
```

## Troubleshooting

### Issue: "Moderation service temporarily unavailable"
**Solution**: Check your API key in `.env` file

### Issue: All content is being rejected
**Solution**: Lower the threshold in `.env` (try 0.8 or 0.9)

### Issue: Toxic content is passing through
**Solution**: Lower the threshold in `.env` (try 0.5 or 0.6)

### Issue: API quota exceeded
**Solution**: 
- Check your Google Cloud Console for quota limits
- Consider implementing rate limiting
- Cache results for duplicate content

## Advanced Usage

### Custom Attributes

To check additional attributes, edit `ModerationService.php`:

```php
private const ATTRIBUTES = [
    'TOXICITY',
    'INSULT',
    'PROFANITY',
    'THREAT',
    'IDENTITY_ATTACK',  // Add this
    'SEXUALLY_EXPLICIT', // Add this
];
```

### Language Support

The service supports multiple languages. Edit in `ModerationService.php`:

```php
'languages' => ['en', 'fr', 'es', 'de'], // Add your languages
```

## API Limits

Free tier limits:
- 1 QPS (Query Per Second)
- 1,000 queries per day

For production, consider:
- Upgrading to paid tier
- Implementing caching
- Rate limiting user submissions

## Security Notes

- ✅ API key is stored in `.env` (not in code)
- ✅ API key is never exposed to frontend
- ✅ Service runs server-side only
- ✅ Results are logged for audit

## Support

For issues with:
- **This integration**: Check your controller and service configuration
- **Perspective API**: Visit [Google Perspective API Docs](https://developers.perspectiveapi.com/)
- **API Key**: Check Google Cloud Console

## Files Modified

- `src/Service/Moderation/ModerationService.php` - Main service
- `src/Service/Moderation/ModerationResult.php` - Result DTO
- `src/Controller/Post/PostController.php` - Integration
- `config/services.yaml` - Service configuration
- `.env` - API configuration
