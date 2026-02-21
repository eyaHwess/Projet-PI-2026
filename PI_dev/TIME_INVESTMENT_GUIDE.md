# Time Investment Analysis - Complete Guide

## Overview

The **Time Investment Analysis** feature provides comprehensive insights into how users invest their time across different goals, helping them optimize productivity and maintain work-life balance.

## Business Logic

### Core Concepts

1. **Planned Duration**: The estimated time to complete an activity (stored in `duration` field)
2. **Actual Duration**: The real time spent completing an activity (stored in `actual_duration_minutes`)
3. **Completed At**: Timestamp when activity was marked as completed
4. **Time Efficiency**: Ratio of actual vs planned duration (100% = on time, >100% = took longer, <100% = faster)

### Calculations

#### Total Goal Time
```
Total Time = SUM(activity.actual_duration_minutes WHERE status = 'completed')
```

#### Weekly Time Investment
```
Weekly Time = SUM(actual_duration_minutes 
                  WHERE completed_at BETWEEN week_start AND week_end 
                  AND status = 'completed')
```

#### Monthly Time Investment
```
Monthly Time = SUM(actual_duration_minutes 
                   WHERE MONTH(completed_at) = current_month 
                   AND status = 'completed')
```

#### Time Distribution
```
Goal Percentage = (Goal Total Time / All Goals Total Time) × 100
```

#### Time Efficiency
```
Efficiency = (Actual Duration / Planned Duration) × 100

- ≤100%: Excellent (completed faster or on time)
- 101-120%: Good (slightly longer than planned)
- >120%: Poor (significantly longer than planned)
```

## Features

### 1. Weekly Time Summary
- Total hours invested this week
- Day-by-day breakdown (bar chart)
- Visual comparison across weekdays
- Workload warning if exceeds 40h threshold

### 2. Time Distribution
- Percentage breakdown per goal
- Visual bars showing relative time investment
- Sorted by most time-consuming first
- Identifies which goals receive most attention

### 3. Monthly Efficiency Analysis
- Time efficiency per goal
- Color-coded badges:
  - Green: Excellent (≤100%)
  - Blue: Good (101-120%)
  - Red: Poor (>120%)
- Monthly hours per goal

### 4. Time Focus Index
- Ranking of goals by time investment
- Focus score (percentage of total time)
- Total hours per goal
- Quick access to detailed view

### 5. Alerts & Warnings

#### Workload Warning
Triggers when weekly time exceeds 40 hours:
```
"Surcharge de travail détectée! 
Vous avez investi Xh cette semaine, 
dépassant le seuil recommandé de 40h."
```

#### Imbalance Detection
Triggers when one goal consumes >60% of total time:
```
"Déséquilibre détecté
Le goal 'X' consomme Y% de votre temps total."
```

### 6. Goal-Specific Details
- Total time invested
- Weekly time
- Monthly time
- Average efficiency
- Goal information and progress

## Database Schema

### Activity Entity - New Fields

```php
#[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
private ?\DateTimeInterface $completedAt = null;

#[ORM\Column(nullable: true)]
private ?int $actualDurationMinutes = null;

#[ORM\Column(nullable: true)]
private ?int $plannedDurationMinutes = null;
```

### Migration SQL
```sql
ALTER TABLE activity ADD completed_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL;
ALTER TABLE activity ADD actual_duration_minutes INT DEFAULT NULL;
ALTER TABLE activity ADD planned_duration_minutes INT DEFAULT NULL;
```

## Architecture

### Service Layer

**TimeInvestmentAnalyzer** (`src/Service/TimeInvestmentAnalyzer.php`)

Key Methods:
- `calculateGoalTotalTime(Goal $goal): int`
- `calculateWeeklyTime(Goal $goal, ?DateTimeInterface $weekStart): int`
- `calculateMonthlyTime(Goal $goal, ?int $month, ?int $year): int`
- `getTimeDistribution(User $user): array`
- `getMostTimeConsumingGoal(User $user): ?array`
- `calculateUserWeeklyTime(User $user, ?DateTimeInterface $weekStart): int`
- `hasWeeklyOverload(User $user, ?DateTimeInterface $weekStart): bool`
- `detectTimeImbalance(User $user): ?array`
- `calculateTimeFocusIndex(User $user): array`
- `calculateGoalTimeEfficiency(Goal $goal): ?float`
- `getComprehensiveAnalytics(User $user): array`
- `getWeeklyBreakdown(User $user, ?DateTimeInterface $weekStart): array`
- `formatDuration(int $minutes): string`

### Controller Layer

**TimeInvestmentController** (`src/Controller/TimeInvestmentController.php`)

Routes:
- `/time-investment/analytics` - Main analytics dashboard
- `/time-investment/goal/{id}` - Goal-specific details

### View Layer

Templates:
- `templates/time_investment/analytics.html.twig` - Main dashboard
- `templates/time_investment/goal_details.html.twig` - Goal details

## Usage

### Accessing the Feature

1. **From Goals Page**:
   - Click "Time Analytics" button in header
   - Located next to Calendrier, Favoris, and Consistency buttons

2. **Direct URL**:
   - Navigate to `/time-investment/analytics`

### Viewing Analytics

The main dashboard displays:
- 4 key statistics cards (weekly time, total time, active goals, priority goal)
- Weekly breakdown bar chart
- Time distribution by goal
- Monthly efficiency analysis
- Time Focus Index table
- Alerts for overload and imbalance

### Viewing Goal Details

1. Click "Détails" button in Focus Index table
2. Or navigate to `/time-investment/goal/{id}`
3. View goal-specific time metrics and efficiency

## Data Population

### Automatic Population

When an activity is marked as completed:
1. Set `completedAt` to current timestamp
2. Set `actualDurationMinutes` to actual time spent
3. Set `plannedDurationMinutes` from `duration` field

### Manual Population (Test Data)

```bash
php bin/console app:populate-time-data
```

This command:
- Finds all completed activities
- Sets planned duration from existing duration field
- Generates realistic actual duration (80-130% of planned)
- Sets completed_at to random time in past 60 days

## Configuration

### Thresholds

Defined in `TimeInvestmentAnalyzer`:

```php
private const WEEKLY_THRESHOLD_HOURS = 40;
private const IMBALANCE_THRESHOLD_PERCENT = 60;
```

To modify:
1. Edit constants in `src/Service/TimeInvestmentAnalyzer.php`
2. Clear cache: `php bin/console cache:clear`

## Integration with Existing System

### Activity Completion Flow

When marking an activity as completed:

```php
$activity->setStatus('completed');
$activity->setCompletedAt(new \DateTime());
$activity->setActualDurationMinutes($actualMinutes);
$activity->setPlannedDurationMinutes($activity->getDurationInMinutes());
```

### Goal Progress Calculation

Time investment data doesn't affect goal progress percentage, which is still calculated based on completed routines.

## Analytics Insights

### Time Focus Index

Helps answer:
- Which goal receives most attention?
- Is time distributed evenly?
- Are priorities aligned with time investment?

### Efficiency Metrics

Helps identify:
- Activities that consistently take longer than planned
- Goals with poor time estimation
- Opportunities for process improvement

### Workload Management

Helps prevent:
- Burnout from overwork
- Imbalanced focus on single goal
- Unrealistic time commitments

## Best Practices

### For Users

1. **Accurate Time Tracking**: Record actual time spent honestly
2. **Regular Review**: Check analytics weekly to adjust priorities
3. **Balance Goals**: Ensure no single goal dominates (>60%)
4. **Respect Limits**: Stay under 40h/week threshold
5. **Improve Estimates**: Use efficiency data to better plan future activities

### For Developers

1. **Data Validation**: Ensure actual_duration_minutes is set when status = 'completed'
2. **Performance**: Use database indexes on completed_at for faster queries
3. **Caching**: Consider caching analytics for frequently accessed data
4. **Extensibility**: Service methods are designed for easy extension

## Advanced Features

### Time Efficiency Tracking

```php
// Get efficiency for an activity
$efficiency = $activity->getTimeEfficiency();

// Check if completed efficiently
$isEfficient = $activity->isCompletedEfficiently(); // ≤110%
```

### Custom Date Ranges

```php
// Weekly time for specific week
$weekStart = new \DateTime('2026-02-10');
$weeklyTime = $analyzer->calculateWeeklyTime($goal, $weekStart);

// Monthly time for specific month
$monthlyTime = $analyzer->calculateMonthlyTime($goal, 2, 2026);
```

### Formatted Duration

```php
$formatted = $analyzer->formatDuration(125); // "2h 5min"
$formatted = $analyzer->formatDuration(45);  // "45 min"
$formatted = $analyzer->formatDuration(120); // "2h"
```

## Troubleshooting

### No Data Showing

**Problem**: Analytics page shows "Aucune donnée disponible"

**Solutions**:
1. Ensure activities are marked as completed
2. Verify `actual_duration_minutes` is set
3. Run: `php bin/console app:populate-time-data`
4. Check user has goals and activities

### Incorrect Calculations

**Problem**: Time totals seem wrong

**Solutions**:
1. Verify `completed_at` is set for completed activities
2. Check `actual_duration_minutes` is not null
3. Ensure status is exactly 'completed'
4. Clear cache: `php bin/console cache:clear`

### Efficiency Shows N/A

**Problem**: Efficiency metrics not displaying

**Solutions**:
1. Ensure both `planned_duration_minutes` and `actual_duration_minutes` are set
2. Verify planned duration is not 0
3. Check activities are completed

## Future Enhancements

### Potential Features

1. **Predictive Analytics**
   - Forecast time needed for future activities
   - Suggest optimal time allocation

2. **Team Comparison**
   - Compare time investment across team members
   - Identify best practices

3. **Time Blocking**
   - Suggest optimal schedule based on historical data
   - Calendar integration

4. **Productivity Score**
   - Combine efficiency, consistency, and completion rate
   - Gamification elements

5. **Export & Reports**
   - PDF reports for time investment
   - CSV export for external analysis

6. **Mobile Notifications**
   - Alert when approaching weekly threshold
   - Remind to log time for completed activities

7. **AI Insights**
   - Identify patterns in time usage
   - Suggest improvements

## API Endpoints (Future)

Potential REST API endpoints:

```
GET  /api/time-investment/analytics
GET  /api/time-investment/goal/{id}
GET  /api/time-investment/weekly-breakdown
GET  /api/time-investment/distribution
POST /api/activity/{id}/complete
```

## Performance Considerations

### Database Queries

- All queries use indexed fields (user_id, completed_at, status)
- Aggregation done at database level (SUM, COUNT)
- Results cached where appropriate

### Optimization Tips

1. Add index on `completed_at`:
```sql
CREATE INDEX idx_activity_completed_at ON activity(completed_at);
```

2. Add composite index:
```sql
CREATE INDEX idx_activity_status_completed ON activity(status, completed_at);
```

3. Consider materialized views for frequently accessed analytics

## Testing

### Unit Tests

Test service methods:
```php
public function testCalculateGoalTotalTime()
public function testDetectTimeImbalance()
public function testHasWeeklyOverload()
```

### Integration Tests

Test controller endpoints:
```php
public function testAnalyticsPageLoads()
public function testGoalDetailsPageLoads()
```

### Manual Testing

1. Create goals with activities
2. Mark activities as completed with time data
3. View analytics dashboard
4. Verify calculations manually
5. Test alerts and warnings

## Conclusion

The Time Investment Analysis feature provides powerful insights into time management, helping users optimize their productivity and maintain healthy work-life balance. The clean architecture ensures easy maintenance and extensibility for future enhancements.
