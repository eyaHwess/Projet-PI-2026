# Design Moderne Appliqué - Guide de Test

## ✅ Changements effectués

### 1. Fichiers créés
- ✅ `public/styles/modern-design-system.css` - Système de design complet
- ✅ `templates/base_modern.html.twig` - Template de base moderne
- ✅ `templates/goal/index.html.twig` - Page Goals modernisée (remplacée)
- ✅ `templates/homepage/index.html.twig` - Homepage modernisée (remplacée)

### 2. Backups créés
- ✅ `templates/goal/index_backup.html.twig` - Backup de l'ancienne page Goals
- ✅ `templates/homepage/index_backup.html.twig` - Backup de l'ancienne homepage

### 3. Cache nettoyé
- ✅ Cache Symfony vidé

## 🚀 Comment tester

### Option 1: Serveur PHP intégré
```bash
cd PI_dev
php -S localhost:8000 -t public
```

Puis ouvrir dans le navigateur:
- Homepage: `http://localhost:8000/`
- Goals: `http://localhost:8000/goals`

### Option 2: Symfony CLI
```bash
cd PI_dev
symfony server:start
```

Puis ouvrir:
- Homepage: `http://127.0.0.1:8000/`
- Goals: `http://127.0.0.1:8000/goals`

## 🔧 Résolution de problèmes

### Problème: CSS ne se charge pas

**Solution 1: Vérifier que le fichier existe**
```bash
dir PI_dev\public\styles\modern-design-system.css
```

**Solution 2: Vider le cache du navigateur**
- Chrome/Edge: Ctrl + Shift + Delete
- Firefox: Ctrl + Shift + Delete
- Ou ouvrir en mode navigation privée

**Solution 3: Vérifier les permissions**
```bash
# Le fichier doit être accessible en lecture
```

**Solution 4: Utiliser le chemin absolu temporairement**
Dans `base_modern.html.twig`, ligne 17:
```twig
<link rel="stylesheet" href="http://localhost:8000/styles/modern-design-system.css">
```

### Problème: Page blanche

**Solution: Vérifier les logs**
```bash
cd PI_dev
tail -f var/log/dev.log
```

### Problème: Erreur 500

**Solution: Vider le cache**
```bash
cd PI_dev
php bin/console cache:clear
rm -rf var/cache/*
```

## 📋 Checklist de vérification

- [ ] Le serveur PHP est démarré
- [ ] Le fichier `public/styles/modern-design-system.css` existe
- [ ] Le cache Symfony est vidé
- [ ] Le cache du navigateur est vidé
- [ ] La page se charge sans erreur 500
- [ ] Les polices Google Fonts se chargent
- [ ] Les icônes Bootstrap Icons s'affichent
- [ ] Le CSS moderne est appliqué

## 🎨 Ce qui devrait être visible

### Homepage (`/`)
- ✅ Navbar moderne avec logo et navigation
- ✅ Hero section avec titre et boutons arrondis
- ✅ 3 cartes de statistiques avec bordure colorée à gauche
- ✅ 6 cartes de fonctionnalités avec icônes
- ✅ Section FAQ avec 4 questions
- ✅ Footer minimaliste

### Goals (`/goals`)
- ✅ Navbar moderne
- ✅ Breadcrumb (Accueil / Mes Objectifs)
- ✅ Header avec titre et boutons d'action
- ✅ Barre de recherche et filtres
- ✅ Cards d'objectifs avec design épuré
- ✅ Badges colorés pour les statuts
- ✅ Barre de progression
- ✅ Boutons d'action arrondis

## 🎨 Palette de couleurs

```css
/* Primaires */
--primary-color: #4A9B9F;        /* Turquoise */
--primary-light: #A8D5D8;
--primary-lighter: #D4EBEC;
--primary-lightest: #E8F5F6;

/* Accents */
--accent-teal: #26A69A;
--accent-green: #4CAF50;
--accent-yellow: #FFC107;
--accent-orange: #FFB74D;
```

## 📝 Prochaines étapes

### Pages à migrer (optionnel)
1. `templates/goal/show.html.twig` - Détails d'un objectif
2. `templates/goal/_form.html.twig` - Formulaire d'objectif
3. `templates/routine/index.html.twig` - Liste des routines
4. `templates/routine/show.html.twig` - Détails d'une routine
5. `templates/activity/index.html.twig` - Liste des activités
6. `templates/calendar/index.html.twig` - Calendrier
7. `templates/favorites/index.html.twig` - Favoris
8. `templates/consistency/heatmap.html.twig` - Heatmap
9. `templates/time_investment/analytics.html.twig` - Analytics

### Pour migrer une page
1. Copier le template actuel en backup
2. Changer `{% extends 'base.html.twig' %}` en `{% extends 'base_modern.html.twig' %}`
3. Remplacer les classes Bootstrap par les classes modernes
4. Tester la page
5. Ajuster si nécessaire

## 🔄 Revenir à l'ancien design

Si vous souhaitez revenir à l'ancien design:

```bash
# Goals
copy PI_dev\templates\goal\index_backup.html.twig PI_dev\templates\goal\index.html.twig

# Homepage
copy PI_dev\templates\homepage\index_backup.html.twig PI_dev\templates\homepage\index.html.twig

# Vider le cache
cd PI_dev
php bin/console cache:clear
```

## 📞 Support

### Vérifier la configuration
```bash
cd PI_dev
php bin/console debug:router
php bin/console debug:config framework
```

### Vérifier les assets
```bash
cd PI_dev/public/styles
dir
# Devrait afficher modern-design-system.css
```

### Tester le CSS directement
Ouvrir dans le navigateur:
```
http://localhost:8000/styles/modern-design-system.css
```

Si le fichier s'affiche, le problème vient d'ailleurs.
Si erreur 404, vérifier le chemin du fichier.

## ✨ Fonctionnalités préservées

Toutes les fonctionnalités existantes sont préservées:
- ✅ Création/modification/suppression d'objectifs
- ✅ Gestion des routines et activités
- ✅ Système de favoris
- ✅ Calendrier
- ✅ Heatmap de consistance
- ✅ Analyse du temps
- ✅ Filtres et recherche
- ✅ Tri des objectifs
- ✅ Duplication d'objectifs
- ✅ Toasts de notification

Seul le design visuel a changé, pas la logique!
