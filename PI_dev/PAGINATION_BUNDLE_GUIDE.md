# ✅ KnpPaginatorBundle - Installation Complete

## What Was Done?

Successfully installed and configured **KnpPaginatorBundle** for paginating reclamation lists.

## Installation Steps

1. ✅ Added bundle to `composer.json` (v6.10)
2. ✅ Registered bundle in `config/bundles.php`
3. ✅ Created configuration file `config/packages/knp_paginator.yaml`
4. ✅ Updated controllers to use pagination
5. ✅ Added pagination controls to templates

## Configuration

### File: `config/packages/knp_paginator.yaml`
```yaml
knp_paginator:
    page_range: 5                       # Number of page links to show
    default_options:
        page_name: page                 # Query parameter: ?page=2
        distinct: true                  # Ensure distinct results
    template:
        pagination: '@KnpPaginator/Pagination/twitter_bootstrap_v4_pagination.html.twig'
```

## Where Pagination Was Added

### 1. Admin Reclamations List
- **Controller**: `AdminResponseController::list()`
- **Template**: `templates/admin_response/index.html.twig`
- **Items per page**: 10
- **Features**: 
  - Pagination works with filters (status, type, search)
  - Filter parameters preserved across pages
  - Bootstrap 4 styled pagination

### 2. User Reclamations List
- **Controller**: `ReclamationController::index()`
- **Template**: `templates/reclamation/index.html.twig`
- **Items per page**: 5
- **Features**:
  - Shows only user's own reclamations
  - Pastel-styled pagination matching the design

## How It Works

### In Controllers
```php
use Knp\Component\Pager\PaginatorInterface;

public function list(Request $request, ReclamationRepository $repository, PaginatorInterface $paginator): Response
{
    $queryBuilder = $repository->createQueryBuilder('r')
        ->orderBy('r.createdAt', 'DESC');
    
    // Paginate the query
    $pagination = $paginator->paginate(
        $queryBuilder->getQuery(),
        $request->query->getInt('page', 1), // Current page
        10 // Items per page
    );
    
    return $this->render('template.html.twig', [
        'reclamations' => $pagination
    ]);
}
```

### In Templates
```twig
{# Display paginated items #}
{% for reclamation in reclamations %}
    {# Your content #}
{% endfor %}

{# Show pagination controls #}
{% if reclamations.pageCount > 1 %}
    {{ knp_pagination_render(reclamations) }}
{% endif %}
```

## Pagination Features

- **Automatic page links**: Shows page numbers with prev/next buttons
- **Query preservation**: Keeps filter parameters when changing pages
- **Responsive design**: Works on mobile and desktop
- **Customizable**: Can change items per page, page range, templates
- **Performance**: Only loads items for current page (not all records)

## Testing

1. **Create test data**: Add more than 10 reclamations
2. **Admin side**: Go to `/admin/reclamation` - should see pagination at bottom
3. **User side**: Go to `/reclamation` - should see pagination if you have more than 5 reclamations
4. **Test filters**: Apply filters and navigate pages - filters should persist

## Customization Options

### Change items per page
```php
$pagination = $paginator->paginate(
    $query,
    $request->query->getInt('page', 1),
    20 // Change this number
);
```

### Change page range (number of page links shown)
Edit `config/packages/knp_paginator.yaml`:
```yaml
knp_paginator:
    page_range: 10  # Show more page links
```

### Use different pagination template
```twig
{{ knp_pagination_render(reclamations, '@KnpPaginator/Pagination/sliding.html.twig') }}
```

Available templates:
- `twitter_bootstrap_v4_pagination.html.twig` (current)
- `sliding.html.twig`
- `foundation_v6_pagination.html.twig`
- Or create your own custom template

## Benefits

✅ **Easy to use**: Just 2 lines of code in controller
✅ **Performance**: Loads only needed records from database
✅ **User-friendly**: Clean navigation for large datasets
✅ **Flexible**: Works with any Doctrine query
✅ **Maintains state**: Preserves filters and sorting across pages

## Next Steps (Optional)

- Add sortable columns (click column header to sort)
- Customize pagination template to match your exact design
- Add "items per page" selector (10, 25, 50, 100)
- Add "Go to page" input field
