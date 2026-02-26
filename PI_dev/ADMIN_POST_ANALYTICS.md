# Admin Post Analytics & Filtering System

## Overview
Comprehensive analytics and filtering system for admin post management with CTR tracking, trend analysis, tag filtering, and performance metrics.

## Features Implemented

### 1. Tag Filtering (Admin Post List)
- **Location**: `templates/admin/components/Post/posts.html.twig`
- **Functionality**:
  - Dropdown filter showing all available tags
  - Displays tag usage count next to each tag name
  - Single-select tag filtering
  - Maintains existing filters (author name, email, sort)
  - Pagination-friendly (doesn't break pagination)
  - Shows "All Tags" option to clear filter

### 2. CTR Score Display (Admin List View)
- **Replaced**: Likes and Comments columns
- **New Column**: CTR Score
- **Display**: Clean badges (Low / Medium / High)
- **Calculation**:
  - CTR = (clicks / views) * 100
  - Handles division by zero (returns 0 if views = 0)
- **Score Logic**:
  - 0%-2% → Low (Gray badge)
  - 2%-6% → Medium (Blue badge)
  - 6%+ → High (Green badge)
- **Styling**: Modern SaaS badges with soft colors, 8px radius, no gradients

### 3. Trend Indicator (Admin List View)
- **New Column**: Trend
- **Display**: Status badges with emojis
- **Statuses**:
  - 🔥 Trending (Growth > 25%)
  - ⬆ Growing (Growth 5%-25%)
  - ⬇ Declining (Growth < 0%)
  - Stable (Growth 0%-5%)
- **Filter**: Dropdown to filter by trend status
- **Calculation**: Based on 7-day vs 14-day view comparison

### 4. Post Detail Analytics
**Location**: `templates/admin/components/Post/post_detail.html.twig`

**Analytics Card**:
- CTR % with progress bar
- Engagement Rate % with progress bar
- Interaction Score (0-100) with progress bar

**Trend Analytics Card**:
- Views (7d)
- Growth percentage
- Trend status badge

**Formulas**:
- **CTR**: (clicks / views) * 100
- **Engagement Rate**: ((likes + comments) / views) * 100
- **Interaction Score**: Weighted calculation:
  - Views: max 20 points (views / 10)
  - Clicks: max 25 points (clicks / 5)
  - Likes: max 30 points (likes / 3)
  - Comments: max 25 points (comments / 2)

### 5. Tag Performance Stats (Post Detail)
**Location**: `templates/admin/components/Post/post_detail.html.twig`

**For each tag attached to post**:
- Tag name badge
- Global rank (based on usage_count)
- Popularity index percentage
- Usage count

**Calculation**:
- Rank: Position in sorted list by usage_count (descending)
- Popularity Index: (tag usage / total tag usage) * 100

### 6. Tags Display in Admin Post Detail
- Tags shown under author email in General Information section
- Only displays if post has tags
- Modern SaaS badge styling
- Clean spacing and layout

## Architecture

### Service Layer
**File**: `src/Service/Analytics/PostAnalyticsService.php`

**Methods**:
- `calculateCTR(Post $post): float` - Calculate CTR percentage
- `getCTRScore(Post $post): string` - Get CTR score (Low/Medium/High)
- `calculateEngagementRate(Post $post): float` - Calculate engagement %
- `calculateInteractionScore(Post $post): int` - Calculate 0-100 score
- `getTagPerformance(Tag $tag): array` - Get tag rank and popularity
- `getTrendStatus(Post $post): array` - Get trend data and status
- `getAllTagsWithStats(): array` - Get all tags for filter dropdown

### Controller Updates
**File**: `src/Controller/AdminController.php`

**admin_posts action**:
- Added tag filter parameter
- Added trend filter parameter
- Integrated PostAnalyticsService
- Calculate CTR score and trend data for each post
- Pass all tags to template for filter dropdown

**admin_post_detail action**:
- Calculate all analytics metrics
- Get tag performance for each tag
- Get trend data
- Pass analytics to template

### Repository
**File**: `src/Repository/PostRepository.php`
- No changes needed (uses QueryBuilder in controller)

### Templates

**posts.html.twig**:
- Added tag filter dropdown
- Added trend filter dropdown
- Replaced Likes/Comments columns with CTR Score/Trend
- Updated table structure
- Added badge styling

**post_detail.html.twig**:
- Added Analytics card
- Added Trend Analytics card
- Added Tag Performance card
- Added tags display in General Information
- Progress bars for metrics

### CSS Styles
**File**: `public/post/posts.css`

**New Classes**:
- `.ctr-badge`, `.ctr-high`, `.ctr-medium`, `.ctr-low`
- `.trend-badge`, `.trend-trending`, `.trend-growing`, `.trend-declining`, `.trend-stable`
- `.trend-badge-detail`
- `.admin-post-tags`

**Design**:
- Modern SaaS style
- Soft pastel backgrounds
- 8px border radius
- No gradients
- Clean spacing
- Professional appearance

## Performance Considerations

1. **N+1 Query Prevention**:
   - Uses QueryBuilder with proper JOINs
   - Eager loads relationships (tags, user, likes, comments)

2. **Pagination**:
   - All filters work with KnpPaginator
   - Maintains filter state across pages

3. **Calculations**:
   - All analytics calculated in service layer
   - Cached in controller loop (not recalculated per render)

4. **Tag Statistics**:
   - Single query to fetch all tags with usage counts
   - Rank calculated once per tag

## Future Enhancements

1. **View Tracking**:
   - Implement PostView entity with timestamps
   - Track daily/weekly/monthly views accurately
   - Enable precise trend calculations

2. **Caching**:
   - Cache tag statistics
   - Cache trend calculations
   - Use Redis for high-traffic scenarios

3. **Advanced Analytics**:
   - Bounce rate tracking
   - Time on post
   - Scroll depth
   - Click heatmaps

4. **Export**:
   - CSV export of analytics
   - PDF reports
   - Scheduled email reports

## Testing

### Manual Testing Checklist
- [ ] Tag filter shows all tags with usage counts
- [ ] Tag filter works with pagination
- [ ] CTR badges display correctly (Low/Medium/High)
- [ ] Trend badges display correctly
- [ ] Trend filter works
- [ ] Analytics display in post detail
- [ ] Tag performance shows correct ranks
- [ ] Progress bars render correctly
- [ ] All filters can be combined
- [ ] Clear filters button works

### Edge Cases Handled
- Division by zero (views = 0)
- Posts with no tags
- Posts with no views/clicks
- Empty tag list
- No posts matching filters

## Configuration

No additional configuration required. Service is auto-registered via autowiring.

## Dependencies

- Symfony 6.x+
- Doctrine ORM
- KnpPaginatorBundle
- Twig

## Files Modified

1. `src/Service/Analytics/PostAnalyticsService.php` (NEW)
2. `src/Controller/AdminController.php` (UPDATED)
3. `templates/admin/components/Post/posts.html.twig` (UPDATED)
4. `templates/admin/components/Post/post_detail.html.twig` (UPDATED)
5. `public/post/posts.css` (UPDATED)
6. `ADMIN_POST_ANALYTICS.md` (NEW - this file)

## Summary

The admin post analytics system provides comprehensive insights into post performance with:
- Clean, modern SaaS UI
- CTR and engagement tracking
- Trend analysis
- Tag performance metrics
- Flexible filtering
- Pagination support
- Performance-optimized queries
- Maintainable service layer architecture

All requirements have been implemented following modern Symfony best practices and maintaining the existing design system.
