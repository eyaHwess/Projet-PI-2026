# ✅ Custom Pagination Styling - Complete

## What Was Done

Created custom pagination templates with pastel pink and blue colors matching your design system.

## New Files Created

### 1. User Pagination Template
**File**: `templates/pagination/custom_pagination.html.twig`

**Features**:
- Tailwind CSS styling
- Pastel pink active page (bg-pink-300)
- Blue borders and hover states (border-blue-200, hover:bg-blue-100)
- Rounded corners (rounded-lg)
- Shadow effects
- Chevron icons for Previous/Next
- Page info display (Page X sur Y • Z résultat(s))
- Disabled state styling for first/last pages

**Colors Used**:
- Active page: Pink-300 (#f9a8d4)
- Borders: Blue-200 (#bfdbfe)
- Hover: Blue-100 (#dbeafe)
- Disabled: Gray-100/200

### 2. Admin Pagination Template
**File**: `templates/pagination/admin_pagination.html.twig`

**Features**:
- Bootstrap 5 styling
- Pastel pink active page (#f9a8d4)
- Blue buttons (#bfdbfe)
- Rounded corners (rounded-3)
- Shadow effects
- Text labels "Précédent" and "Suivant"
- Page info display
- Filter parameter preservation

**Colors Used**:
- Active page: #f9a8d4 (pink-300)
- Buttons: #bfdbfe (blue-200)
- Text: #1e40af (blue-800)
- Disabled: #e5e7eb (gray-200)

## Pagination Structure

### User View (Tailwind)
```
[< Previous] [1] [2] [3] ... [10] [Next >]
Page 2 sur 10 • 47 résultat(s)
```

### Admin View (Bootstrap)
```
[< Précédent] [1] [2] [3] ... [10] [Suivant >]
Page 2 sur 10 • 47 réclamation(s)
```

## Features

### Navigation
- ✅ Previous/Next buttons with icons
- ✅ Direct page number links
- ✅ Ellipsis (...) for skipped pages
- ✅ Always show first and last page
- ✅ Current page highlighted in pink
- ✅ Disabled state for unavailable actions

### Visual Design
- ✅ Consistent with pastel pink/blue theme
- ✅ Smooth hover transitions
- ✅ Shadow effects for depth
- ✅ Rounded corners for modern look
- ✅ Clear visual hierarchy

### Functionality
- ✅ Preserves filter parameters (admin)
- ✅ Shows total item count
- ✅ Shows current page / total pages
- ✅ Responsive design
- ✅ Accessible (aria-label, rel attributes)

## Configuration

### Items Per Page
- **User view**: 5 items per page
- **Admin view**: 10 items per page

To change:
```php
// In controller
$pagination = $paginator->paginate(
    $query,
    $request->query->getInt('page', 1),
    10 // Change this number
);
```

### Page Range
Configured in `config/packages/knp_paginator.yaml`:
```yaml
knp_paginator:
    page_range: 5  # Number of page links to show
```

## Templates Updated

1. `templates/reclamation/index.html.twig`
   - Uses: `pagination/custom_pagination.html.twig`

2. `templates/admin_response/index.html.twig`
   - Uses: `pagination/admin_pagination.html.twig`
   - Preserves filters: status, type, search

## Customization Options

### Change Colors

**User Pagination** (Tailwind):
```twig
{# Active page #}
bg-pink-300 border-pink-400  → Change to your color

{# Buttons #}
border-blue-200 hover:bg-blue-100  → Change to your color
```

**Admin Pagination** (Inline styles):
```twig
{# Active page #}
background-color: #f9a8d4;  → Change hex color

{# Buttons #}
background-color: #bfdbfe;  → Change hex color
```

### Change Button Text
```twig
{# User view #}
<i class="bi bi-chevron-left"></i>  → Icon only

{# Admin view #}
Précédent / Suivant  → Change text
```

### Hide Page Info
Remove this section from template:
```twig
<div class="text-center mt-4">
    <p class="text-sm text-gray-600">...</p>
</div>
```

## Testing

1. Create more than 5 reclamations (user) or 10 (admin)
2. Navigate to reclamation list
3. Pagination should appear at bottom
4. Test:
   - Click page numbers
   - Click Previous/Next
   - Check filter preservation (admin)
   - Verify styling matches design

## Benefits

✅ Professional, organized appearance
✅ Consistent with design system
✅ Better user experience
✅ Clear navigation
✅ Accessible and responsive
✅ Easy to customize
