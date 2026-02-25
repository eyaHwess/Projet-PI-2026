# 🎭 Mock Mode - Testing Without OpenAI API

## Overview

Mock mode allows you to test the AI integration without making real API calls to OpenAI. This is perfect for:
- Development and testing
- Avoiding rate limits
- Working offline
- Demonstrating functionality

---

## How to Enable/Disable Mock Mode

### Enable Mock Mode (Current Setting)

In `.env` file:
```env
OPENAI_USE_MOCK=1
```

### Disable Mock Mode (Use Real OpenAI API)

In `.env` file:
```env
OPENAI_USE_MOCK=0
```

After changing, always clear cache:
```bash
php bin/console cache:clear
```

---

## What Mock Mode Does

### Mock Data Generated

When mock mode is enabled, the system generates 5 realistic goal suggestions based on user statistics:

1. **Complete Overdue Goal First** (High Priority, 7 days)
   - Addresses overdue goals directly
   
2. **Daily 15-Minute Goal Review** (High Priority, 30 days)
   - Builds consistency habit
   
3. **Break Large Goals into Milestones** (Medium Priority, 14 days)
   - Improves completion rate
   
4. **Weekly Progress Check-in** (Medium Priority, 90 days)
   - Long-term tracking
   
5. **Celebrate Small Wins** (Low Priority, 60 days)
   - Motivation and momentum

### Dynamic Content

The mock suggestions adapt to user statistics:
- References actual overdue goal count
- Mentions current completion rate
- Adjusts descriptions based on data

---

## Testing

### Console Command
```bash
php bin/console app:test-ai
```

### Web Page
Visit: `http://localhost:8000/goals`

The AI coaching card will display the mock suggestions.

---

## Switching to Real API

When you're ready to use the real OpenAI API:

### Step 1: Add Credits to OpenAI
1. Go to: https://platform.openai.com/account/billing
2. Add minimum $5 credit
3. This upgrades you to Tier 1 (3,500 requests/minute)

### Step 2: Disable Mock Mode
In `.env`:
```env
OPENAI_USE_MOCK=0
```

### Step 3: Clear Cache
```bash
php bin/console cache:clear
```

### Step 4: Test
```bash
php bin/console app:test-ai
```

---

## Summary

✅ **Mock mode is now enabled**  
✅ **No API calls will be made**  
✅ **No rate limits**  
✅ **Perfect for testing**

To test:
```bash
# Console
php bin/console app:test-ai

# Or visit
http://localhost:8000/goals
```

When ready for production, just change `OPENAI_USE_MOCK=0` and add OpenAI credits!
