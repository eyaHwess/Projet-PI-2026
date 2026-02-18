# Rapport des Problèmes Non Résolus - Projet PI_dev

Date: 2026-02-18

## ✅ Pages Modernisées (Design Moderne Appliqué)

### 1. Goals (Objectifs)
- ✅ `templates/goal/index.html.twig` - Liste des objectifs
- ✅ `templates/goal/new.html.twig` - Création d'objectif
- ✅ `templates/goal/edit.html.twig` - Édition d'objectif
- ✅ `templates/goal/_form.html.twig` - Formulaire partiel
- ❌ `templates/goal/show.html.twig` - Détails d'un objectif (ANCIEN DESIGN)

### 2. Favorites
- ✅ `templates/favorite/index.html.twig` - Page des favoris

### 3. Calendar
- ✅ `templates/calendar/index.html.twig` - Calendrier de planification

### 4. Base Templates
- ✅ `templates/base_modern.html.twig` - Template de base moderne
- ✅ `public/styles/modern-design-system.css` - Système de design

## ❌ Pages NON Modernisées (Utilisent encore base.html.twig)

### 1. Routines (PRIORITÉ HAUTE)
- ❌ `templates/routine/index.html.twig` - Liste des routines
- ❌ `templates/routine/show.html.twig` - Détails d'une routine
- ❌ `templates/routine/new.html.twig` - Création de routine
- ❌ `templates/routine/_form.html.twig` - Formulaire de routine

### 2. Activities (PRIORITÉ HAUTE)
- ❌ `templates/activity/_form.html.twig` - Formulaire d'activité

### 3. Consistency & Analytics (PRIORITÉ HAUTE)
- ❌ `templates/consistency/heatmap.html.twig` - Heatmap de consistance
- ❌ `templates/time_investment/analytics.html.twig` - Analyse du temps
- ❌ `templates/time_investment/goal_details.html.twig` - Détails temps par objectif

### 4. Homepage (PRIORITÉ MOYENNE)
- ❌ `templates/homepage/index.html.twig` - Page d'accueil
- ⚠️ ERREUR: Utilise asset mapper qui n'existe pas

### 5. Security (PRIORITÉ MOYENNE)
- ❌ `templates/security/login.html.twig` - Page de connexion
- ❌ `templates/security/register.html.twig` - Page d'inscription

### 6. User Dashboard (PRIORITÉ MOYENNE)
- ❌ `templates/user_dashboard/index.html.twig` - Tableau de bord utilisateur
- ❌ `templates/user_dashboard/charts.html.twig` - Graphiques utilisateur
- ❌ `templates/user/dashuser.html.twig` - Dashboard utilisateur
- ❌ `templates/user/index.html.twig` - Index utilisateur

### 7. Sessions (PRIORITÉ BASSE)
- ❌ `templates/session/index.html.twig` - Liste des sessions
- ❌ `templates/session/show.html.twig` - Détails session
- ❌ `templates/session/edit.html.twig` - Édition session
- ❌ `templates/session/schedule.html.twig` - Planification session
- ❌ `templates/session_crud/index.html.twig` - CRUD sessions
- ❌ `templates/session_crud/new.html.twig` - Nouvelle session
- ❌ `templates/session_crud/edit.html.twig` - Édition session

### 8. Coach (PRIORITÉ BASSE)
- ❌ `templates/coach/index.html.twig` - Liste des coachs
- ❌ `templates/coach/index_enhanced.html.twig` - Liste améliorée
- ❌ `templates/coach/index_static.html.twig` - Liste statique
- ❌ `templates/coach/schedule.html.twig` - Planification coach
- ❌ `templates/coach/request_modern.html.twig` - Demande moderne
- ❌ `templates/coach/search_enhanced.html.twig` - Recherche améliorée

### 9. Coaching Requests (PRIORITÉ BASSE)
- ❌ `templates/coaching_request/index.html.twig` - Demandes de coaching

### 10. Admin (PRIORITÉ BASSE)
- ❌ `templates/admin/index.html.twig` - Dashboard admin
- ❌ `templates/admin/coaches_list.html.twig` - Liste coachs admin
- ❌ `templates/admin/manage_accounts.html.twig` - Gestion comptes
- ❌ `templates/admin/user_detail.html.twig` - Détails utilisateur
- ❌ `templates/admin/user_list.html.twig` - Liste utilisateurs
- ❌ `templates/admin_coach/index.html.twig` - Gestion coachs
- ❌ `templates/admin_coach/new.html.twig` - Nouveau coach
- ❌ `templates/admin_coach/edit.html.twig` - Édition coach
- ❌ `templates/admin_coach/show.html.twig` - Détails coach

### 11. Posts (PRIORITÉ BASSE)
- ❌ `templates/Post/postList.html.twig` - Liste des posts
- ❌ `templates/Post/create.html.twig` - Création de post

### 12. Notifications (PRIORITÉ BASSE)
- ❌ `templates/notification/index.html.twig` - Notifications

### 13. Landing (PRIORITÉ BASSE)
- ❌ `templates/landing/index.html.twig` - Page landing

## 🔧 Problèmes Techniques Identifiés

### 1. Asset Mapper Error (CRITIQUE)
**Fichier**: `templates/homepage/index.html.twig`
**Erreur**: "The asset mapper directory 'assets/*' does not exist"
**Cause**: La homepage utilise `{{ asset('images/manifest.json') }}` avec l'ancien système
**Solution**: Moderniser la homepage ou corriger les chemins d'assets

### 2. Formulaire Goal (_form.html.twig)
**Problème**: Le fichier `goal/_form.html.twig` backup étend encore `base.html.twig`
**Impact**: Peut causer des confusions
**Solution**: Déjà corrigé dans la version actuelle

### 3. Contrôleurs
**Fichiers à vérifier**:
- `src/Controller/GoalController.php` - ✅ Corrigé (utilise new.html.twig et edit.html.twig)
- `src/Controller/RoutineController.php` - ❌ À vérifier
- `src/Controller/ActivityController.php` - ❌ À vérifier
- `src/Controller/ConsistencyController.php` - ❌ À vérifier
- `src/Controller/TimeInvestmentController.php` - ❌ À vérifier

### 4. Cache Symfony
**Status**: ✅ Vidé récemment
**Commande**: `php bin/console cache:clear`

## 📊 Statistiques

- **Total de templates**: ~80 fichiers
- **Templates modernisés**: 7 fichiers (9%)
- **Templates à moderniser**: ~73 fichiers (91%)

### Par Priorité:
- **PRIORITÉ HAUTE** (Fonctionnalités principales): 10 fichiers
  - Routines: 4 fichiers
  - Activities: 1 fichier
  - Consistency/Analytics: 3 fichiers
  - Goal show: 1 fichier
  - Homepage: 1 fichier

- **PRIORITÉ MOYENNE** (Authentification & Dashboard): 7 fichiers
  - Security: 2 fichiers
  - User Dashboard: 5 fichiers

- **PRIORITÉ BASSE** (Fonctionnalités secondaires): ~56 fichiers
  - Sessions: 7 fichiers
  - Coach: 6 fichiers
  - Admin: 14 fichiers
  - Autres: 29 fichiers

## 🎯 Plan d'Action Recommandé

### Phase 1: Fonctionnalités Principales (URGENT)
1. ✅ Goals - FAIT
2. ✅ Favorites - FAIT
3. ✅ Calendar - FAIT
4. ❌ Goal show page
5. ❌ Routines (index, show, new, _form)
6. ❌ Activities (_form)
7. ❌ Consistency heatmap
8. ❌ Time investment analytics
9. ❌ Homepage (corriger l'erreur asset mapper)

### Phase 2: Authentification & Dashboard (IMPORTANT)
10. ❌ Login/Register pages
11. ❌ User dashboard

### Phase 3: Fonctionnalités Secondaires (OPTIONNEL)
12. ❌ Sessions
13. ❌ Coach
14. ❌ Admin
15. ❌ Posts
16. ❌ Notifications

## 🚀 Prochaines Étapes Immédiates

1. **Moderniser Goal show page** - Détails d'un objectif
2. **Moderniser Routine pages** - index, show, new, _form
3. **Moderniser Activity form** - _form
4. **Moderniser Consistency heatmap**
5. **Moderniser Time investment analytics**
6. **Corriger Homepage** - Erreur asset mapper

## 📝 Notes

- Le système de design moderne est en place (`modern-design-system.css`)
- Le template de base moderne existe (`base_modern.html.twig`)
- Les contrôleurs Goals sont à jour
- Le cache a été vidé

## ⚠️ Avertissements

- Ne pas supprimer `base.html.twig` tant que toutes les pages ne sont pas migrées
- Tester chaque page après migration
- Vérifier les contrôleurs associés
- S'assurer que les formulaires fonctionnent correctement
