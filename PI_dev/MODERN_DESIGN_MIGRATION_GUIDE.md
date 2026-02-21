# Guide de Migration vers le Design Moderne

## Vue d'ensemble

Un nouveau système de design moderne a été créé, inspiré par les interfaces de coaching professionnelles. Ce design utilise:
- **Palette de couleurs**: Bleu/turquoise doux (#4A9B9F, #A8D5D8, #D4EBEC)
- **Typographie**: Inter (Google Fonts)
- **Style**: Minimaliste, épuré, avec beaucoup d'espace blanc
- **Composants**: Cards arrondies, boutons pill-shaped, badges colorés

## Fichiers créés

### 1. Design System CSS
**Fichier**: `public/styles/modern-design-system.css`
- Variables CSS pour couleurs, espacements, typographie
- Composants réutilisables (boutons, cards, badges, formulaires)
- Système de grille responsive
- Animations et transitions

### 2. Template de base moderne
**Fichier**: `templates/base_modern.html.twig`
- Navbar moderne avec navigation claire
- Footer minimaliste
- Système de toast notifications
- Breadcrumb navigation
- Structure de page optimisée

### 3. Exemple: Page Goals modernisée
**Fichier**: `templates/goal/index_modern.html.twig`
- Design épuré avec cards spacieuses
- Filtres et recherche intégrés
- Badges colorés pour les statuts
- Actions groupées et visibles
- Layout responsive

## Migration progressive

### Étape 1: Tester le nouveau design

1. **Accéder à la version moderne**:
   - Modifier `GoalController.php` pour utiliser `index_modern.html.twig`
   - Ou créer une route `/goals/modern` pour tester

2. **Comparer les deux versions**:
   - Ancienne: `/goals`
   - Nouvelle: `/goals/modern` (si route créée)

### Étape 2: Migrer page par page

Pour chaque template, suivre ce processus:

#### A. Goals (Objectifs)
```
✅ index_modern.html.twig - Créé
⏳ show.html.twig - À migrer
⏳ _form.html.twig - À migrer
```

#### B. Routines
```
⏳ index.html.twig - À migrer
⏳ show.html.twig - À migrer
⏳ _form.html.twig - À migrer
```

#### C. Activities
```
⏳ index.html.twig - À migrer
⏳ _form.html.twig - À migrer
```

#### D. Pages spéciales
```
⏳ calendar/index.html.twig - À migrer
⏳ favorites/index.html.twig - À migrer
⏳ consistency/heatmap.html.twig - À migrer
⏳ time_investment/analytics.html.twig - À migrer
```

### Étape 3: Appliquer le design system

#### Remplacer les classes Bootstrap par les classes modernes

**Avant (Bootstrap)**:
```html
<div class="card">
    <div class="card-body">
        <h5 class="card-title">Titre</h5>
        <p class="card-text">Contenu</p>
        <button class="btn btn-primary">Action</button>
    </div>
</div>
```

**Après (Design moderne)**:
```html
<div class="card-modern">
    <h3>Titre</h3>
    <p>Contenu</p>
    <button class="btn-modern btn-modern-primary">Action</button>
</div>
```

#### Tableau de correspondance des classes

| Bootstrap | Design Moderne | Notes |
|-----------|----------------|-------|
| `.btn .btn-primary` | `.btn-modern .btn-modern-primary` | Bouton principal |
| `.btn .btn-secondary` | `.btn-modern .btn-modern-secondary` | Bouton secondaire |
| `.btn .btn-outline-primary` | `.btn-modern .btn-modern-outline` | Bouton outline |
| `.card` | `.card-modern` | Card de base |
| `.badge .bg-primary` | `.badge-modern .badge-modern-primary` | Badge |
| `.form-control` | `.form-control-modern` | Input de formulaire |
| `.form-select` | `.form-control-modern .form-select-modern` | Select |
| `.alert .alert-info` | `.alert-modern .alert-modern-info` | Alerte |
| `.table` | `.table-modern` | Tableau |
| `.modal` | `.modal-modern` | Modal |
| `.navbar` | `.navbar-modern` | Barre de navigation |

## Composants du Design System

### Couleurs

```css
/* Primaires */
--primary-color: #4A9B9F;        /* Turquoise principal */
--primary-light: #A8D5D8;        /* Turquoise clair */
--primary-lighter: #D4EBEC;      /* Turquoise très clair */
--primary-lightest: #E8F5F6;     /* Turquoise ultra clair */

/* Accents */
--accent-teal: #26A69A;          /* Vert turquoise */
--accent-green: #4CAF50;         /* Vert */
--accent-yellow: #FFC107;        /* Jaune */
--accent-orange: #FFB74D;        /* Orange */

/* Status */
--success: #26A69A;
--warning: #FFB74D;
--danger: #EF5350;
--info: #4A9B9F;
```

### Typographie

```css
/* Tailles */
--font-size-xs: 0.75rem;    /* 12px */
--font-size-sm: 0.875rem;   /* 14px */
--font-size-base: 1rem;     /* 16px */
--font-size-lg: 1.125rem;   /* 18px */
--font-size-xl: 1.25rem;    /* 20px */
--font-size-2xl: 1.5rem;    /* 24px */
--font-size-3xl: 1.875rem;  /* 30px */
--font-size-4xl: 2.25rem;   /* 36px */

/* Police */
font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
```

### Espacements

```css
--space-xs: 0.5rem;    /* 8px */
--space-sm: 0.75rem;   /* 12px */
--space-md: 1rem;      /* 16px */
--space-lg: 1.5rem;    /* 24px */
--space-xl: 2rem;      /* 32px */
--space-2xl: 3rem;     /* 48px */
```

### Border Radius

```css
--radius-sm: 8px;
--radius-md: 12px;
--radius-lg: 16px;
--radius-xl: 20px;
--radius-full: 9999px;  /* Complètement arrondi */
```

## Exemples de composants

### Boutons

```html
<!-- Bouton principal -->
<button class="btn-modern btn-modern-primary">
    <i class="bi bi-plus-circle"></i> Ajouter
</button>

<!-- Bouton secondaire -->
<button class="btn-modern btn-modern-secondary">
    <i class="bi bi-pencil"></i> Modifier
</button>

<!-- Bouton outline -->
<button class="btn-modern btn-modern-outline">
    <i class="bi bi-files"></i> Dupliquer
</button>

<!-- Bouton succès -->
<button class="btn-modern btn-modern-success">
    <i class="bi bi-check"></i> Valider
</button>

<!-- Tailles -->
<button class="btn-modern btn-modern-primary btn-modern-sm">Petit</button>
<button class="btn-modern btn-modern-primary">Normal</button>
<button class="btn-modern btn-modern-primary btn-modern-lg">Grand</button>
```

### Cards

```html
<!-- Card simple -->
<div class="card-modern">
    <h3>Titre de la card</h3>
    <p>Contenu de la card</p>
</div>

<!-- Card avec header -->
<div class="card-modern">
    <div class="card-modern-header">
        <h3>Titre avec fond coloré</h3>
    </div>
    <p>Contenu de la card</p>
</div>

<!-- Card hover -->
<div class="card-modern" style="cursor: pointer;">
    <!-- Effet hover automatique -->
</div>
```

### Badges

```html
<!-- Badges de statut -->
<span class="badge-modern badge-modern-primary">Actif</span>
<span class="badge-modern badge-modern-success">Complété</span>
<span class="badge-modern badge-modern-warning">En attente</span>
<span class="badge-modern badge-modern-danger">Échoué</span>
<span class="badge-modern badge-modern-info">Info</span>

<!-- Badge avec icône -->
<span class="badge-modern badge-modern-success">
    <i class="bi bi-check-circle"></i> Validé
</span>
```

### Formulaires

```html
<!-- Input simple -->
<div class="form-group-modern">
    <label class="form-label-modern">Nom</label>
    <input type="text" class="form-control-modern" placeholder="Entrez votre nom">
</div>

<!-- Select -->
<div class="form-group-modern">
    <label class="form-label-modern">Catégorie</label>
    <select class="form-control-modern form-select-modern">
        <option>Option 1</option>
        <option>Option 2</option>
    </select>
</div>

<!-- Recherche -->
<div class="search-input-modern">
    <input type="text" class="form-control-modern" placeholder="Rechercher...">
</div>
```

### Grille

```html
<!-- 2 colonnes -->
<div class="grid-modern grid-modern-2">
    <div class="card-modern">Item 1</div>
    <div class="card-modern">Item 2</div>
</div>

<!-- 3 colonnes -->
<div class="grid-modern grid-modern-3">
    <div class="card-modern">Item 1</div>
    <div class="card-modern">Item 2</div>
    <div class="card-modern">Item 3</div>
</div>

<!-- 4 colonnes -->
<div class="grid-modern grid-modern-4">
    <div class="card-modern">Item 1</div>
    <div class="card-modern">Item 2</div>
    <div class="card-modern">Item 3</div>
    <div class="card-modern">Item 4</div>
</div>
```

### Alertes

```html
<!-- Alerte info -->
<div class="alert-modern alert-modern-info">
    <i class="bi bi-info-circle"></i>
    <div>Message d'information</div>
</div>

<!-- Alerte succès -->
<div class="alert-modern alert-modern-success">
    <i class="bi bi-check-circle"></i>
    <div>Opération réussie!</div>
</div>

<!-- Alerte warning -->
<div class="alert-modern alert-modern-warning">
    <i class="bi bi-exclamation-triangle"></i>
    <div>Attention!</div>
</div>

<!-- Alerte danger -->
<div class="alert-modern alert-modern-danger">
    <i class="bi bi-x-circle"></i>
    <div>Erreur!</div>
</div>
```

### Progress Bar

```html
<div class="progress-modern">
    <div class="progress-modern-bar" style="width: 75%;"></div>
</div>
```

### Avatar

```html
<!-- Avatar avec initiales -->
<div class="avatar-modern">JD</div>

<!-- Avatar avec icône -->
<div class="avatar-modern">
    <i class="bi bi-person"></i>
</div>

<!-- Tailles -->
<div class="avatar-modern avatar-modern-sm">S</div>
<div class="avatar-modern">M</div>
<div class="avatar-modern avatar-modern-lg">L</div>
```

## Système de Toast

```javascript
// Afficher un toast
showToast('Message de succès', 'success');
showToast('Message d\'erreur', 'error');
showToast('Message d\'avertissement', 'warning');
showToast('Message d\'information', 'info');
```

## Migration d'un template - Checklist

- [ ] Changer `{% extends 'base.html.twig' %}` en `{% extends 'base_modern.html.twig' %}`
- [ ] Ajouter le block `breadcrumb` si nécessaire
- [ ] Remplacer les classes Bootstrap par les classes modernes
- [ ] Utiliser les couleurs du design system
- [ ] Appliquer les espacements cohérents
- [ ] Utiliser les border-radius définis
- [ ] Tester la responsivité
- [ ] Vérifier les interactions (hover, focus)
- [ ] Tester les fonctionnalités (aucun changement)

## Avantages du nouveau design

✅ **Cohérence visuelle** - Design system unifié
✅ **Modernité** - Interface contemporaine et professionnelle
✅ **Lisibilité** - Typographie claire et hiérarchie visuelle
✅ **Accessibilité** - Contrastes respectés, focus visible
✅ **Performance** - CSS optimisé, pas de framework lourd
✅ **Maintenabilité** - Variables CSS, composants réutilisables
✅ **Responsive** - Mobile-first, adaptatif
✅ **UX améliorée** - Interactions fluides, feedback visuel

## Prochaines étapes

1. **Tester la page Goals moderne** (`index_modern.html.twig`)
2. **Valider le design** avec les utilisateurs
3. **Migrer les autres pages** une par une
4. **Documenter les composants** spécifiques au projet
5. **Optimiser les performances** (lazy loading, etc.)
6. **Ajouter des animations** subtiles si nécessaire

## Support

Pour toute question sur la migration:
- Consulter `modern-design-system.css` pour les composants disponibles
- Voir `base_modern.html.twig` pour la structure de base
- Référencer `index_modern.html.twig` comme exemple complet

## Notes importantes

⚠️ **Aucune fonctionnalité n'est modifiée** - Seul le design change
⚠️ **Migration progressive** - Pas besoin de tout changer d'un coup
⚠️ **Compatibilité** - L'ancien design continue de fonctionner
⚠️ **Tests** - Tester chaque page migrée avant de passer à la suivante
