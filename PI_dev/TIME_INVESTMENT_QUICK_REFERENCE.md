# Time Investment Analysis - Quick Reference

## Quick Start

### 1. Update Database
```bash
php bin/console doctrine:schema:update --force
```

### 2. Populate Test Data
```bash
php bin/console app:populate-time-data
```

### 3. Clear Cache
```bash
php bin/console cache:clear
```

### 4. Access Feature
Navigate to: `/time-investment/analytics`

## Key Endpoints

| Route | URL | Description |
|-------|-----|-------------|
| Analytics Dashboard | `/time-investment/analytics` | Main analytics page |
| Goal Details | `/time-investment/goal/{id}` | Goal-specific time details |

## Service Methods

### TimeInvestmentAnalyzer

```php
// Get total time for a goal
$totalMinutes = $analyzer->calculateGoalTotalTime($goal);

// Get weekly time
$weeklyMinutes = $analyzer->calculateWeeklyTime($goal);

// Get monthly time
$monthlyMinutes = $analyzer->calculateMonthlyTime($goal, $month, $year);

// Get time distribution
$distribution = $analyzer->getTimeDistribution($user);

// Check for overload
$hasOverload = $analyzer->hasWeeklyOverload($user);

// Detect imbalance
$imbalance = $analyzer->detectTimeImbalance($user);

// Get comprehensive analytics
$analytics = $analyzer->getComprehensiveAnalytics($user);

// Format duration
$formatted = $analyzer->formatDuration(125); // "2h 5min"
```

## Activity Entity - New Fields

```php
// Set when activity is completed
$activity->setCompletedAt(new \DateTime());

// Set actual time spent (in minutes)
$activity->setActualDurationMinutes(90);

// Set planned time (in minutes)
$activity->setPlannedDurationMinutes(60);

// Get efficiency
$efficiency = $activity->getTimeEfficiency(); // 150%

// Check if efficient
$isEfficient = $activity->isCompletedEfficiently(); // false (>110%)
```

## Thresholds

```php
WEEKLY_THRESHOLD_HOURS = 40;      // Overload warning
IMBALANCE_THRESHOLD_PERCENT = 60; // Imbalance warning
```

## Efficiency Levels

| Efficiency | Level | Color | Meaning |
|-----------|-------|-------|---------|
| ≤100% | Excellent | Green | On time or faster |
| 101-120% | Good | Blue | Slightly longer |
| >120% | Poor | Red | Significantly longer |

## Database Schema

```sql
-- New columns in activity table
completed_at              TIMESTAMP NULL
actual_duration_minutes   INT NULL
planned_duration_minutes  INT NULL
```

## Common Queries

### Get all completed activities with time data
```php
$qb = $em->createQueryBuilder();
$activities = $qb->select('a')
    ->from('App\Entity\Activity', 'a')
    ->where('a.status = :status')
    ->andWhere('a.actualDurationMinutes IS NOT NULL')
    ->setParameter('status', 'completed')
    ->getQuery()
    ->getResult();
```

### Calculate total time for user
```php
$qb = $em->createQueryBuilder();
$result = $qb->select('SUM(a.actualDurationMinutes) as total')
    ->from('App\Entity\Activity', 'a')
    ->join('a.routine', 'r')
    ->join('r.goal', 'g')
    ->where('g.user = :user')
    ->andWhere('a.status = :status')
    ->setParameter('user', $user)
    ->setParameter('status', 'completed')
    ->getQuery()
    ->getSingleScalarResult();
```

## Twig Templates

### Display formatted duration
```twig
{{ analyzer.formatDuration(minutes) }}
```

### Display efficiency badge
```twig
{% if efficiency <= 100 %}
    <span class="efficiency-badge efficiency-excellent">{{ efficiency }}%</span>
{% elseif efficiency <= 120 %}
    <span class="efficiency-badge efficiency-good">{{ efficiency }}%</span>
{% else %}
    <span class="efficiency-badge efficiency-poor">{{ efficiency }}%</span>
{% endif %}
```

### Display time distribution
```twig
{% for item in distribution %}
    <div>{{ item.goal.title }}: {{ item.percentage }}%</div>
{% endfor %}
```

## Console Commands

```bash
# Populate time data
php bin/console app:populate-time-data

# Check routes
php bin/console debug:router | findstr time

# Clear cache
php bin/console cache:clear

# Update schema
php bin/console doctrine:schema:update --force
```

## Troubleshooting

### No data showing
```bash
# 1. Check if activities exist
php bin/console doctrine:query:sql "SELECT COUNT(*) FROM activity WHERE status = 'completed'"

# 2. Check if time data exists
php bin/console doctrine:query:sql "SELECT COUNT(*) FROM activity WHERE actual_duration_minutes IS NOT NULL"

# 3. Populate test data
php bin/console app:populate-time-data

# 4. Clear cache
php bin/console cache:clear
```

### Calculations seem wrong
```bash
# 1. Verify completed_at is set
php bin/console doctrine:query:sql "SELECT id, completed_at FROM activity WHERE status = 'completed' LIMIT 5"

# 2. Verify actual_duration_minutes
php bin/console doctrine:query:sql "SELECT id, actual_duration_minutes FROM activity WHERE status = 'completed' LIMIT 5"

# 3. Clear cache
php bin/console cache:clear
```

## Integration Example

### When completing an activity

```php
// In your ActivityController or service
public function completeActivity(Activity $activity, int $actualMinutes): void
{
    // Set status
    $activity->setStatus('completed');
    
    // Set completion timestamp
    $activity->setCompletedAt(new \DateTime());
    
    // Set actual duration
    $activity->setActualDurationMinutes($actualMinutes);
    
    // Set planned duration (if not already set)
    if (!$activity->getPlannedDurationMinutes()) {
        $activity->setPlannedDurationMinutes($activity->getDurationInMinutes());
    }
    
    // Save
    $this->entityManager->persist($activity);
    $this->entityManager->flush();
}
```

## CSS Classes

```css
/* Stats cards */
.stats-card
.stats-value
.stats-label

/* Efficiency badges */
.efficiency-badge
.efficiency-excellent  /* Green */
.efficiency-good       /* Blue */
.efficiency-poor       /* Red */

/* Alerts */
.alert-overload    /* Red alert */
.alert-imbalance   /* Yellow alert */

/* Charts */
.weekly-chart
.day-bar
.time-bar
```

## Color Palette

```css
--primary-color: #9333ea;     /* Purple */
--primary-hover: #7e22ce;     /* Dark purple */

/* Efficiency colors */
Excellent: #d1fae5 to #a7f3d0  /* Green gradient */
Good:      #dbeafe to #bfdbfe  /* Blue gradient */
Poor:      #fee2e2 to #fecaca  /* Red gradient */

/* Alert colors */
Overload:  #fee2e2 to #fecaca  /* Red gradient */
Imbalance: #fef3c7 to #fde68a  /* Yellow gradient */
```

## Icons (Bootstrap Icons)

```html
<i class="bi bi-clock-history"></i>    <!-- Time Analytics -->
<i class="bi bi-clock-fill"></i>       <!-- Weekly time -->
<i class="bi bi-calendar-week"></i>    <!-- Total time -->
<i class="bi bi-bullseye"></i>         <!-- Active goals -->
<i class="bi bi-trophy"></i>           <!-- Priority goal -->
<i class="bi bi-bar-chart-line"></i>   <!-- Weekly breakdown -->
<i class="bi bi-pie-chart-fill"></i>   <!-- Distribution -->
<i class="bi bi-speedometer2"></i>     <!-- Efficiency -->
<i class="bi bi-star-fill"></i>        <!-- Focus index -->
```

## Testing Checklist

- [ ] Database schema updated
- [ ] Test data populated
- [ ] Analytics page loads
- [ ] Goal details page loads
- [ ] Weekly chart displays
- [ ] Distribution shows correctly
- [ ] Efficiency badges work
- [ ] Alerts trigger properly
- [ ] Navigation works
- [ ] Responsive on mobile

## Performance Tips

```sql
-- Add indexes for better performance
CREATE INDEX idx_activity_completed_at ON activity(completed_at);
CREATE INDEX idx_activity_status_completed ON activity(status, completed_at);
```

## Common Patterns

### Get analytics for current user
```php
$user = $this->getUser();
$analytics = $this->analyzer->getComprehensiveAnalytics($user);
```

### Check for warnings
```php
if ($analytics['weeklyTime']['hasOverload']) {
    // Show overload warning
}

if ($analytics['imbalance']) {
    // Show imbalance warning
}
```

### Display time in hours
```php
$hours = round($minutes / 60, 2);
```

## Documentation Links

- Full Guide: `TIME_INVESTMENT_GUIDE.md`
- Implementation Summary: `TIME_INVESTMENT_SUMMARY.md`
- Quick Reference: `TIME_INVESTMENT_QUICK_REFERENCE.md` (this file)
