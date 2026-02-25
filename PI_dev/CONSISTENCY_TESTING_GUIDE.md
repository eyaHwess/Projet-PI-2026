# Consistency Heatmap - Testing Guide

## Prerequisites

1. Database schema updated
2. Test data populated
3. Cache cleared
4. Development server running

## Setup Commands

```bash
# Navigate to project directory
cd PI_dev

# Update database schema
php bin/console doctrine:schema:update --force

# Populate test data (91 days)
php bin/console app:populate-consistency-data

# Clear cache
php bin/console cache:clear

# Start development server (if not already running)
# Note: Run this in a separate terminal
php -S localhost:8000 -t public
```

## Test Scenarios

### 1. Access the Heatmap

#### Test 1.1: From Goals Page
1. Navigate to `/goal`
2. Look for "Consistency" button in the hero section
3. Click the button
4. Verify redirect to `/consistency/heatmap`

**Expected Result**: Successfully navigates to heatmap page

#### Test 1.2: Direct URL
1. Navigate directly to `/consistency/heatmap`
2. Verify page loads without errors

**Expected Result**: Page loads with heatmap, stats, and monthly chart

#### Test 1.3: From Navbar
1. On heatmap page, click "Mes Objectifs" in navbar
2. Verify navigation works
3. Click "Consistency" in navbar
4. Verify return to heatmap

**Expected Result**: Navigation works bidirectionally

### 2. Statistics Cards

#### Test 2.1: Consistency Score
1. Locate the first stats card
2. Verify it shows a percentage (0-100%)
3. Verify subtitle says "(30 derniers jours)"
4. Hover over card
5. Verify elevation effect

**Expected Result**: 
- Shows calculated percentage
- Hover effect works smoothly
- Gradient text visible

#### Test 2.2: Longest Streak
1. Locate the second stats card
2. Verify it shows a number (days)
3. Verify subtitle says "jours consécutifs"
4. Hover over card

**Expected Result**: 
- Shows calculated streak
- Number is realistic (0-91 for test data)

#### Test 2.3: Most Productive Day
1. Locate the third stats card
2. Verify it shows a calendar icon or day name
3. Verify subtitle shows the day name (e.g., "Monday")
4. Hover over card

**Expected Result**: 
- Shows day name or "N/A" if no data
- Icon displays correctly

#### Test 2.4: Trend
1. Locate the fourth stats card
2. Verify it shows a badge (Improving/Stable/Decreasing)
3. Verify correct icon (↑ / - / ↓)
4. Verify correct color (green/blue/red)
5. Verify subtitle says "(14 derniers jours)"

**Expected Result**: 
- Badge displays with correct color
- Icon matches trend direction

### 3. Monthly Comparison Chart

#### Test 3.1: Chart Display
1. Locate the monthly chart section
2. Verify 12 bars are displayed
3. Verify each bar has a month label (Jan-Dec)
4. Verify bars have different heights

**Expected Result**: 
- All 12 months visible
- Heights vary based on data
- Purple gradient bars

#### Test 3.2: Bar Interaction
1. Hover over each bar
2. Verify hover effect (elevation)
3. Verify percentage value visible on bar

**Expected Result**: 
- Smooth hover animation
- Percentage displays on bar

### 4. Heatmap Grid

#### Test 4.1: Grid Structure
1. Locate the heatmap grid
2. Count rows (should be 7 for days of week)
3. Count columns (should be ~52 for weeks)
4. Verify weekday labels (Mon, Wed, Fri, Sun)

**Expected Result**: 
- 7 rows × ~52 columns
- Weekday labels aligned correctly
- Grid is properly aligned

#### Test 4.2: Cell Colors
1. Examine cells in the grid
2. Verify different colors present:
   - Gray cells (no activity)
   - Red cells (0% completion)
   - Light green cells (1-49%)
   - Medium green cells (50-79%)
   - Dark green cells (80-100%)

**Expected Result**: 
- Multiple colors visible
- Colors match legend
- Cells are 14px × 14px

#### Test 4.3: Cell Hover
1. Hover over various cells
2. Verify tooltip appears
3. Verify tooltip shows:
   - Date
   - Percentage
   - Activities completed/total
   - Routines completed/total
4. Move mouse around
5. Verify tooltip follows cursor

**Expected Result**: 
- Tooltip appears immediately
- Content is accurate
- Tooltip follows mouse smoothly
- Cell scales up on hover

#### Test 4.4: Cell Click
1. Click on a cell with data
2. Verify modal opens
3. Verify modal shows:
   - Formatted date (French)
   - Large percentage
   - 4 statistics cards
4. Click X to close
5. Verify modal closes

**Expected Result**: 
- Modal opens smoothly
- All data displays correctly
- Close button works

#### Test 4.5: Empty Cell Click
1. Click on a gray cell (no activity)
2. Verify modal opens
3. Verify shows 0% and "Aucune activité" message

**Expected Result**: 
- Modal opens
- Shows appropriate message for no data

### 5. Year Navigation

#### Test 5.1: Previous Year
1. Note current year in title
2. Click "◄ 2025" button
3. Verify URL updates with `?year=2025`
4. Verify heatmap updates
5. Verify title shows "2025"

**Expected Result**: 
- URL updates
- Heatmap regenerates for 2025
- Stats recalculate for 2025

#### Test 5.2: Next Year
1. Click "2027 ►" button
2. Verify URL updates with `?year=2027`
3. Verify heatmap updates
4. Verify title shows "2027"

**Expected Result**: 
- URL updates
- Heatmap regenerates for 2027
- Stats recalculate for 2027

#### Test 5.3: Future Year (No Data)
1. Navigate to year 2030
2. Verify heatmap shows all gray cells
3. Verify stats show 0 or N/A

**Expected Result**: 
- No errors
- Empty state displays correctly

### 6. Responsive Design

#### Test 6.1: Desktop (> 1200px)
1. Resize browser to full width
2. Verify 4 stats cards in one row
3. Verify heatmap fully visible
4. Verify monthly chart shows all bars

**Expected Result**: 
- Optimal layout for large screens
- No horizontal scroll

#### Test 6.2: Tablet (768px - 992px)
1. Resize browser to ~800px width
2. Verify stats cards stack (2 per row)
3. Verify heatmap scrollable horizontally
4. Verify monthly chart visible

**Expected Result**: 
- Cards reflow appropriately
- Horizontal scroll available for heatmap

#### Test 6.3: Mobile (< 768px)
1. Resize browser to ~375px width
2. Verify stats cards stack (1 per row)
3. Verify heatmap scrollable
4. Verify monthly chart scrollable
5. Verify buttons are touch-friendly

**Expected Result**: 
- Single column layout
- All content accessible
- Touch targets adequate

### 7. Performance

#### Test 7.1: Page Load Time
1. Open browser DevTools (F12)
2. Go to Network tab
3. Navigate to `/consistency/heatmap`
4. Check total load time

**Expected Result**: 
- Page loads in < 2 seconds
- No failed requests

#### Test 7.2: Interaction Responsiveness
1. Hover over multiple cells rapidly
2. Click cells quickly
3. Navigate between years

**Expected Result**: 
- No lag or stuttering
- Smooth animations
- No console errors

### 8. Data Accuracy

#### Test 8.1: Verify Calculations
1. Pick a specific date from heatmap
2. Note the percentage shown
3. Calculate manually: (completed / total) × 100
4. Verify matches displayed percentage

**Expected Result**: 
- Calculations are accurate

#### Test 8.2: Consistency Score
1. Note the consistency score
2. Check last 30 days of data
3. Calculate average manually
4. Verify matches displayed score

**Expected Result**: 
- Score is accurate average

#### Test 8.3: Longest Streak
1. Note the longest streak
2. Examine heatmap for consecutive colored cells
3. Count manually
4. Verify matches displayed streak

**Expected Result**: 
- Streak count is accurate

### 9. Edge Cases

#### Test 9.1: No Data
1. Create a new user with no activities
2. Navigate to their heatmap
3. Verify graceful handling

**Expected Result**: 
- All gray cells
- Stats show 0 or N/A
- No errors

#### Test 9.2: All Perfect Days
1. Manually create logs with 100% completion
2. View heatmap
3. Verify all cells are dark green

**Expected Result**: 
- Consistent dark green color
- 100% consistency score

#### Test 9.3: All Failed Days
1. Manually create logs with 0% completion
2. View heatmap
3. Verify all cells are red

**Expected Result**: 
- Consistent red color
- 0% consistency score

### 10. Browser Compatibility

#### Test 10.1: Chrome
1. Open in Chrome
2. Run all tests above

**Expected Result**: All features work

#### Test 10.2: Firefox
1. Open in Firefox
2. Run all tests above

**Expected Result**: All features work

#### Test 10.3: Safari
1. Open in Safari
2. Run all tests above

**Expected Result**: All features work

#### Test 10.4: Edge
1. Open in Edge
2. Run all tests above

**Expected Result**: All features work

## Console Errors

Throughout all tests, monitor browser console for:
- JavaScript errors
- Network errors
- Warning messages

**Expected Result**: No errors or warnings

## Database Verification

```bash
# Check if data exists
php bin/console doctrine:query:sql "SELECT COUNT(*) FROM daily_activity_log"

# Check date range
php bin/console doctrine:query:sql "SELECT MIN(log_date), MAX(log_date) FROM daily_activity_log"

# Check completion percentages
php bin/console doctrine:query:sql "SELECT log_date, completion_percentage FROM daily_activity_log ORDER BY log_date DESC LIMIT 10"
```

## Debugging Tips

### If heatmap doesn't show:
1. Check browser console for errors
2. Verify data exists in database
3. Check user exists (static@example.com)
4. Clear cache: `php bin/console cache:clear`

### If colors are wrong:
1. Check `getHeatmapColor()` method in `DailyActivityLog.php`
2. Verify completion percentages in database
3. Check CSS color definitions

### If stats are 0:
1. Verify test data was populated
2. Check date range in queries
3. Verify user_id matches

### If tooltips don't work:
1. Check JavaScript console for errors
2. Verify Bootstrap JS is loaded
3. Check data attributes on cells

### If modal doesn't open:
1. Check Bootstrap modal initialization
2. Verify click event listener
3. Check modal HTML structure

## Success Criteria

✅ All 10 test scenarios pass
✅ No console errors
✅ Responsive on all screen sizes
✅ Data calculations are accurate
✅ Smooth animations and interactions
✅ Works in all major browsers
✅ Performance is acceptable (< 2s load)

## Reporting Issues

If you find any issues:

1. **Document the issue**:
   - What you did
   - What you expected
   - What actually happened
   - Browser and version
   - Screenshot if applicable

2. **Check console**:
   - Copy any error messages
   - Note the file and line number

3. **Verify data**:
   - Check database for relevant data
   - Verify user exists

4. **Create a bug report** with all above information

## Additional Testing

### Load Testing
```bash
# Generate more test data
php bin/console app:populate-consistency-data

# This will add more days if run multiple times
```

### Stress Testing
1. Navigate between years rapidly
2. Click many cells quickly
3. Hover over cells rapidly
4. Verify no memory leaks or slowdowns

### Accessibility Testing
1. Use keyboard only (Tab, Enter, Esc)
2. Use screen reader
3. Check color contrast
4. Verify ARIA labels

## Conclusion

After completing all tests, the Consistency Heatmap should be fully functional, performant, and ready for production use.
