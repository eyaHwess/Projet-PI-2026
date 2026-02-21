# Configuration d'accès public - Documentation

## Changements effectués

### 1. Modification de security.yaml

Les routes suivantes sont maintenant accessibles sans authentification:

```yaml
- { path: ^/goals, roles: PUBLIC_ACCESS }
- { path: ^/goal, roles: PUBLIC_ACCESS }
- { path: ^/routines, roles: PUBLIC_ACCESS }
- { path: ^/routine, roles: PUBLIC_ACCESS }
- { path: ^/activity, roles: PUBLIC_ACCESS }
- { path: ^/activities, roles: PUBLIC_ACCESS }
- { path: ^/favorites, roles: PUBLIC_ACCESS }
- { path: ^/calendar, roles: PUBLIC_ACCESS }
- { path: ^/consistency, roles: PUBLIC_ACCESS }
- { path: ^/time-investment, roles: PUBLIC_ACCESS }
```

### 2. Utilisateur statique

L'application utilise un utilisateur statique pour toutes les opérations:
- **Email**: `static@example.com`
- **Mot de passe**: `password123`
- **Prénom**: Static
- **Nom**: User
- **Statut**: active

Cet utilisateur est créé automatiquement s'il n'existe pas.

## Routes accessibles publiquement

| Route | URL | Description |
|-------|-----|-------------|
| Homepage | `/` | Page d'accueil |
| Goals | `/goals` | Liste des objectifs |
| Goal Details | `/goals/{id}` | Détails d'un objectif |
| Routines | `/goals/{goalId}/routines` | Liste des routines |
| Routine Details | `/goals/{goalId}/routines/{id}` | Détails d'une routine |
| Activities | `/routines/{routineId}/activities` | Liste des activités |
| Calendar | `/calendar` | Calendrier des deadlines |
| Favorites | `/favorites` | Liste des favoris |
| Consistency | `/consistency/heatmap` | Heatmap de consistance |
| Time Analytics | `/time-investment/analytics` | Analyse d'investissement temps |

## Routes protégées

| Route | URL | Rôle requis | Description |
|-------|-----|-------------|-------------|
| Admin Dashboard | `/admin` | ROLE_ADMIN | Tableau de bord admin |
| Admin Users | `/admin/users` | ROLE_ADMIN | Gestion des utilisateurs |
| Login | `/login` | PUBLIC_ACCESS | Page de connexion |
| Logout | `/logout` | Authentifié | Déconnexion |

## Contrôleurs utilisant l'utilisateur statique

1. **GoalController** - Gestion des objectifs
2. **RoutineController** - Gestion des routines (hérite de Goal)
3. **ActivityController** - Gestion des activités (hérite de Routine)
4. **CalendarController** - Affichage du calendrier
5. **FavoriteController** - Gestion des favoris
6. **ConsistencyController** - Heatmap de consistance
7. **TimeInvestmentController** - Analyse du temps

## Fonctionnement

### Sans authentification
1. L'utilisateur accède directement à `/goals`
2. Le système utilise automatiquement `static@example.com`
3. Toutes les données sont associées à cet utilisateur
4. Aucune connexion n'est requise

### Avec authentification (optionnel)
1. L'utilisateur peut toujours se connecter via `/login`
2. Les routes admin nécessitent `ROLE_ADMIN`
3. Les autres routes restent accessibles publiquement

## Avantages

✅ **Accès immédiat** - Pas besoin de créer un compte
✅ **Simplicité** - Pas de gestion de session complexe
✅ **Démonstration** - Idéal pour tester l'application
✅ **Développement** - Facilite le développement et les tests

## Limitations

⚠️ **Données partagées** - Tous les utilisateurs anonymes partagent les mêmes données
⚠️ **Pas de personnalisation** - Impossible de séparer les données par utilisateur
⚠️ **Sécurité** - Toutes les données sont publiques

## Migration vers multi-utilisateurs

Si vous souhaitez activer l'authentification multi-utilisateurs plus tard:

1. **Créer une route d'inscription**:
```php
#[Route('/register', name: 'app_register')]
public function register(): Response
{
    // Formulaire d'inscription
}
```

2. **Modifier security.yaml**:
```yaml
- { path: ^/goals, roles: ROLE_USER }
- { path: ^/goal, roles: ROLE_USER }
# etc...
```

3. **Modifier les contrôleurs**:
```php
// Remplacer getStaticUser() par:
private function getCurrentUser(): User
{
    return $this->getUser();
}
```

4. **Ajouter un système de registration**:
```bash
php bin/console make:registration-form
```

## Commandes utiles

```bash
# Vérifier les routes
php bin/console debug:router

# Vérifier la configuration de sécurité
php bin/console debug:firewall

# Créer l'utilisateur statique manuellement
php bin/console doctrine:query:sql "INSERT INTO \"user\" (email, first_name, last_name, status, password) VALUES ('static@example.com', 'Static', 'User', 'active', 'hashed_password')"

# Vérifier si l'utilisateur existe
php bin/console doctrine:query:sql "SELECT * FROM \"user\" WHERE email = 'static@example.com'"

# Nettoyer le cache
php bin/console cache:clear
```

## Test de l'accès public

1. **Ouvrir un navigateur en mode incognito**
2. **Naviguer vers** `http://localhost:8000/goals`
3. **Vérifier** que la page se charge sans redirection vers `/login`
4. **Tester** la création, modification, suppression d'objectifs
5. **Vérifier** que toutes les fonctionnalités sont accessibles

## Sécurité

### Configuration actuelle
- ✅ Accès public aux fonctionnalités principales
- ✅ Admin protégé par ROLE_ADMIN
- ✅ CSRF protection activée
- ✅ Mots de passe hashés

### Recommandations
- 🔒 En production, considérez l'authentification obligatoire
- 🔒 Limitez l'accès public aux pages de démonstration uniquement
- 🔒 Implémentez un système de rate limiting
- 🔒 Ajoutez des logs pour surveiller l'utilisation

## Troubleshooting

### Problème: Redirection vers /login
**Solution**: 
```bash
php bin/console cache:clear
```

### Problème: Erreur "User not found"
**Solution**: L'utilisateur statique sera créé automatiquement au premier accès

### Problème: Erreur 403 Forbidden
**Solution**: Vérifier que `PUBLIC_ACCESS` est bien configuré dans security.yaml

### Problème: Les données ne s'affichent pas
**Solution**: Vérifier que l'utilisateur statique a des données associées

## Conclusion

L'application est maintenant accessible publiquement sans nécessiter de connexion. Toutes les fonctionnalités principales (Goals, Routines, Activities, Calendar, Consistency, Time Analytics) sont disponibles pour tous les visiteurs en utilisant un utilisateur statique partagé.

Cette configuration est idéale pour:
- Démonstrations
- Développement
- Tests
- Prototypes

Pour une application en production avec plusieurs utilisateurs, il est recommandé d'implémenter un système d'authentification complet.
