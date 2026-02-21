# OpenAI Rate Limit Error - Fix Guide

## Error Details

**Error Code**: HTTP 429 - Too Many Requests  
**URL**: `https://api.openai.com/v1/chat/completions`  
**Meaning**: You've exceeded OpenAI's rate limits

---

## Why This Happens

### 1. **Free Tier Limits** (Most Common)
OpenAI free tier has very strict limits:
- **3 requests per minute** (RPM)
- **200 requests per day** (RPD)
- **40,000 tokens per minute** (TPM)

### 2. **Rapid Testing**
- Refreshing the page multiple times
- Running test endpoint repeatedly
- Multiple users accessing simultaneously

### 3. **Account Issues**
- No credits in OpenAI account
- Expired API key
- Account suspended

---

## Immediate Solutions

### Solution 1: Wait and Retry
The simplest solution - wait 60 seconds and try again.

```bash
# Wait 1 minute, then refresh the page
```

### Solution 2: Check Your OpenAI Account

1. Go to: https://platform.openai.com/account/usage
2. Check your current usage
3. Verify you have credits
4. Check rate limits for your tier

### Solution 3: Upgrade Your OpenAI Plan

**Free Tier** → **Pay-as-you-go**
- Increases limits to 3,500 RPM
- Costs ~$0.0001 per suggestion
- Add $5-10 credit to start

---

## What We Fixed in the Code

### 1. **Added Error Handling**

The `AiAssistanceService` now catches rate limit errors:

```php
try {
    $response = $this->client->request('POST', ...);
    return $data['choices'][0]['message']['content'];
} catch (ClientException $e) {
    if ($statusCode === 429) {
        return "🤖 Coaching Temporairement Indisponible...";
    }
}
```

### 2. **Graceful Degradation**

If OpenAI fails, users see a friendly message instead of an error:

```
🤖 Coaching Temporairement Indisponible

Nous avons atteint la limite de requêtes API. 
Votre suggestion personnalisée sera générée dans quelques instants.

💡 En attendant: Continuez votre excellent travail ! 
Vous avez X objectifs complétés sur Y (Z%).

✨ Revenez dans quelques minutes pour votre coaching personnalisé.
```

### 3. **Fallback Message**

If AI generation completely fails, a motivational fallback is shown:

```php
$aiText = "🎯 Continuez votre excellent travail !
Vous avez {$completedGoals} objectifs complétés sur {$totalGoals}.
💪 Restez concentré sur vos priorités !";
```

### 4. **24-Hour Caching**

Suggestions are cached for 24 hours, reducing API calls:
- Only 1 API call per user per day
- Subsequent page loads use cached suggestion
- Dramatically reduces rate limit issues

---

## Testing Without Hitting Rate Limits

### Option 1: Use Cached Suggestions
Once a suggestion is generated, it's cached for 24 hours. Just refresh the page - no new API call!

### Option 2: Test with Mock Data
Temporarily disable AI calls for testing:

```php
// In GoalController.php, comment out AI generation:
/*
if ($shouldGenerateNew) {
    $aiText = $this->aiAssistantService->generateSuggestion($userData);
    // ... save to DB
}
*/

// Use mock data instead:
$aiText = "🎯 **Test Suggestion**\n\nThis is a test message without calling OpenAI API.";
```

### Option 3: Delete Old Suggestions
Force new generation by deleting old suggestions:

```sql
DELETE FROM suggestion WHERE user_id = 1;
```

Then refresh the page once.

---

## Long-Term Solutions

### 1. **Implement Request Throttling**

Add a cooldown period between requests:

```php
// Check last generation time
$lastGeneration = $latestSuggestion?->getCreatedAt();
$now = new \DateTime();
$hoursSinceLastGen = $lastGeneration 
    ? $now->diff($lastGeneration)->h 
    : 24;

// Only generate if at least 12 hours passed
if ($hoursSinceLastGen >= 12) {
    // Generate new suggestion
}
```

### 2. **Queue System**

For high-traffic apps, use a queue:

```php
// Instead of generating immediately:
$this->messageBus->dispatch(new GenerateAiSuggestion($user->getId()));

// Process in background worker
```

### 3. **Batch Processing**

Generate suggestions for all users at once (e.g., daily cron job):

```bash
# Cron job at 6 AM daily
0 6 * * * php bin/console app:generate-ai-suggestions
```

### 4. **Alternative AI Providers**

Consider alternatives with higher free tiers:
- **Anthropic Claude**: Higher rate limits
- **Google Gemini**: Free tier with good limits
- **Local LLMs**: Ollama, LM Studio (no API costs)

---

## Monitoring Rate Limits

### Check Current Usage

```bash
# OpenAI Dashboard
https://platform.openai.com/account/usage

# Check via API
curl https://api.openai.com/v1/usage \
  -H "Authorization: Bearer $OPENAI_API_KEY"
```

### Log API Calls

Add logging to track usage:

```php
// In AiAssistanceService.php
use Psr\Log\LoggerInterface;

public function generateSuggestion(array $userData): string
{
    $this->logger->info('OpenAI API call initiated', [
        'user_data' => $userData,
        'timestamp' => new \DateTime()
    ]);
    
    // ... make API call
    
    $this->logger->info('OpenAI API call completed');
}
```

---

## Error Messages Explained

### 429 - Too Many Requests
**Cause**: Exceeded rate limits  
**Solution**: Wait 60 seconds, upgrade plan, or implement caching

### 401 - Unauthorized
**Cause**: Invalid API key  
**Solution**: Check `.env` file, verify key at platform.openai.com

### 500 - Internal Server Error
**Cause**: OpenAI service issue  
**Solution**: Check status.openai.com, retry later

### Timeout
**Cause**: Request took too long  
**Solution**: Increase timeout, check network

---

## Best Practices

### ✅ DO:
- Cache AI responses for 24+ hours
- Implement error handling
- Show friendly fallback messages
- Monitor API usage regularly
- Use background jobs for non-critical AI calls

### ❌ DON'T:
- Call API on every page load
- Test repeatedly without delays
- Ignore rate limit errors
- Store API keys in code
- Generate suggestions for every user action

---

## Quick Checklist

- [ ] Verified OpenAI account has credits
- [ ] Checked current usage at platform.openai.com
- [ ] Confirmed API key is correct in `.env`
- [ ] Waited 60 seconds before retrying
- [ ] Cleared Symfony cache: `php bin/console cache:clear`
- [ ] Checked database for cached suggestions
- [ ] Reviewed error logs: `var/log/dev.log`
- [ ] Considered upgrading OpenAI plan

---

## Support Resources

- **OpenAI Status**: https://status.openai.com
- **Rate Limits Docs**: https://platform.openai.com/docs/guides/rate-limits
- **Pricing**: https://openai.com/pricing
- **Community**: https://community.openai.com

---

## Summary

The 429 error is now handled gracefully. Users will see a friendly message instead of an error page. The 24-hour caching ensures you rarely hit rate limits in normal usage.

**For Development**: Consider using mock data or upgrading to a paid OpenAI plan.  
**For Production**: The current caching strategy should work fine for most use cases.
