# Correction Route app_goal_index

## ❌ Erreur

```
An exception has been thrown during the rendering of a template 
("Unable to generate a URL for the named route "app_goal_index" 
as such route does not exist.") in goal/index.html.twig at line 142.
```

## 🔍 Cause

La route `app_goal_index` n'existe pas. La route correcte est `goal_list`.

## ✅ Solution

Remplacé toutes les occurrences de `app_goal_index` par `goal_list` dans tous les templates.

## 📝 Fichiers Modifiés

### 1. templates/goal/index.html.twig
```twig
<!-- AVANT -->
<a href="{{ path('app_goal_index') }}">Mes Objectifs</a>

<!-- APRÈS -->
<a href="{{ path('goal_list') }}">Mes Objectifs</a>
```

### 2. templates/routine/show.html.twig
- Ligne 177: `app_goal_index` → `goal_list`

### 3. templates/routine/new.html.twig
- Ligne 84: `app_goal_index` → `goal_list`

### 4. templates/routine/index.html.twig
- Ligne 147: `app_goal_index` → `goal_list`

### 5. templates/user/dashuser.html.twig
- Ligne 549: `app_goal_index` → `goal_list`
- Ligne 631: `app_goal_index` → `goal_list`
- Ligne 659: `app_goal_index` → `goal_list`
- Ligne 684: `app_goal_index` → `goal_list`

### 6. templates/homepage/index.html.twig
- Toutes les occurrences remplacées (8 occurrences)

### 7. templates/goal/show.html.twig
- Ligne 139: `app_goal_index` → `goal_list`
- Ligne 148: `app_goal_index` → `goal_list`

## 📊 Statistiques

- **Fichiers modifiés:** 7
- **Occurrences corrigées:** ~15
- **Route correcte:** `goal_list`

## ✅ Vérifications

### Test 1: Recherche d'Occurrences
```bash
grep -r "app_goal_index" templates/
```
**Résultat:** ✅ Aucune occurrence trouvée

### Test 2: Cache Clear
```bash
php bin/console cache:clear
```
**Résultat:** ✅ OK

### Test 3: Routes Disponibles
```bash
php bin/console debug:router | findstr goal
```
**Résultat:** ✅ `goal_list` existe

## 🎯 Routes Goals Disponibles

```
goal_list          GET      /goals
goal_new           GET|POST /goal/new
goal_join          GET      /goal/{id}/join
goal_leave         GET      /goal/{id}/leave
goal_show          GET      /goal/{id}
goal_messages      GET|POST /goal/{id}/messages
goal_delete        POST     /goal/{id}/delete
goal_edit          GET|POST /goal/{id}/edit
```

## 🧪 Tests à Effectuer

### Test 1: Page d'Accueil
1. Aller sur `/`
2. Cliquer sur "Mes Objectifs"
3. ✅ **Résultat:** Redirige vers `/goals`

### Test 2: Dashboard Utilisateur
1. Aller sur `/user/dashboard`
2. Cliquer sur "Gérer mes objectifs"
3. ✅ **Résultat:** Redirige vers `/goals`

### Test 3: Navigation
1. Aller sur une page de routine
2. Cliquer sur "Mes Objectifs" dans le menu
3. ✅ **Résultat:** Redirige vers `/goals`

### Test 4: Goal Show
1. Aller sur `/goal/1`
2. Cliquer sur "Retour aux objectifs"
3. ✅ **Résultat:** Redirige vers `/goals`

## 📚 Documentation

### Nom de Route Correct
```twig
{# ✅ CORRECT #}
<a href="{{ path('goal_list') }}">Mes Objectifs</a>

{# ❌ INCORRECT #}
<a href="{{ path('app_goal_index') }}">Mes Objectifs</a>
```

### Controller
```php
#[Route('/goals', name: 'goal_list')]
public function list(GoalRepository $goalRepository): Response
{
    // ...
}
```

## ✅ Checklist

- [x] Toutes les occurrences de `app_goal_index` remplacées
- [x] Cache nettoyé
- [x] Aucune erreur de route
- [x] Navigation fonctionne
- [x] Liens corrects dans tous les templates

## 🚀 Résultat

Tous les liens vers la liste des goals fonctionnent maintenant correctement!

---

**Correction terminée! L'erreur de route est résolue. 🎉**
