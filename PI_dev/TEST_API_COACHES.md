# Tests de l'API Coaches

## Endpoints disponibles

### 1. Recherche de coaches
```bash
# Tous les coaches
curl http://localhost:8000/api/coaches/search

# Recherche par mot-clé
curl "http://localhost:8000/api/coaches/search?q=yoga"

# Filtrer par spécialité
curl "http://localhost:8000/api/coaches/search?speciality=Yoga"

# Filtrer par prix
curl "http://localhost:8000/api/coaches/search?minPrice=40&maxPrice=60"

# Filtrer par note
curl "http://localhost:8000/api/coaches/search?minRating=4.5"

# Trier par prix croissant
curl "http://localhost:8000/api/coaches/search?sortBy=price&sortOrder=asc"

# Trier par popularité
curl "http://localhost:8000/api/coaches/search?sortBy=popularity&sortOrder=desc"

# Combinaison de filtres
curl "http://localhost:8000/api/coaches/search?q=coach&speciality=Musculation&minPrice=50&minRating=4.5&sortBy=rating&sortOrder=desc"
```

### 2. Récupérer les filtres disponibles
```bash
curl http://localhost:8000/api/coaches/filters
```

## Réponses attendues

### Recherche de coaches
```json
{
  "success": true,
  "coaches": [
    {
      "id": 1,
      "firstName": "Sophie",
      "lastName": "Martin",
      "email": "sophie.martin@coach.com",
      "speciality": "Yoga",
      "rating": 4.8,
      "reviewCount": 127,
      "pricePerSession": 45.0,
      "availability": "Disponible",
      "bio": "Coach certifiée en Yoga...",
      "photoUrl": null,
      "badges": ["Top coach", "Répond rapidement"],
      "respondsQuickly": true,
      "totalSessions": 450
    }
  ],
  "count": 1
}
```

### Filtres disponibles
```json
{
  "success": true,
  "filters": {
    "specialities": ["Yoga", "Musculation", "Nutrition", "Cardio", "Pilates", "CrossFit", "Boxe"],
    "priceRange": {
      "min": 40,
      "max": 65
    },
    "availabilities": ["Disponible", "Limité"],
    "coachingTypes": ["En ligne", "En présentiel", "Hybride"]
  }
}
```

## Tests avec navigateur

1. **Page principale** : http://localhost:8000/coaches/enhanced
2. **API Search** : http://localhost:8000/api/coaches/search
3. **API Filters** : http://localhost:8000/api/coaches/filters

## Scénarios de test

### Test 1 : Recherche simple
1. Ouvrir `/coaches/enhanced`
2. Taper "yoga" dans la barre de recherche
3. Vérifier que seuls les coaches de Yoga apparaissent

### Test 2 : Filtrage par prix
1. Définir prix min: 50€
2. Définir prix max: 60€
3. Vérifier que seuls les coaches dans cette fourchette apparaissent

### Test 3 : Tri
1. Cliquer sur "Prix croissant"
2. Vérifier que les coaches sont triés du moins cher au plus cher

### Test 4 : Demande rapide
1. Cliquer sur "Demande rapide" sur une carte
2. Remplir le formulaire
3. Envoyer
4. Vérifier l'animation de succès

### Test 5 : Réinitialisation
1. Appliquer plusieurs filtres
2. Cliquer sur "Réinitialiser"
3. Vérifier que tous les filtres sont effacés

## Coaches de test disponibles

1. **Sophie Martin** - Yoga - 45€ - 4.8⭐
2. **Thomas Dubois** - Musculation - 60€ - 4.9⭐
3. **Marie Leroy** - Nutrition - 55€ - 4.7⭐
4. **Lucas Bernard** - Cardio - 40€ - 4.6⭐
5. **Emma Petit** - Pilates - 50€ - 4.9⭐
6. **Alexandre Roux** - CrossFit - 65€ - 4.5⭐
7. **Camille Moreau** - Yoga - 48€ - 4.8⭐
8. **Hugo Simon** - Boxe - 55€ - 4.7⭐

## Vérification de la base de données

```sql
-- Vérifier les coaches
SELECT first_name, last_name, speciality, price_per_session, rating, review_count 
FROM "user" 
WHERE roles::text LIKE '%ROLE_COACH%';

-- Vérifier les demandes de coaching
SELECT cr.id, u1.first_name as client, u2.first_name as coach, cr.goal, cr.level, cr.status
FROM coaching_request cr
JOIN "user" u1 ON cr.user_id = u1.id
JOIN "user" u2 ON cr.coach_id = u2.id;
```

## Dépannage

### Problème : API retourne 0 coaches
- Vérifier que les coaches ont été créés : `php bin/console app:populate-coaches`
- Vérifier la base de données

### Problème : Erreur 500
- Vérifier les logs : `tail -f var/log/dev.log`
- Vérifier la configuration de la base de données

### Problème : Filtres ne fonctionnent pas
- Ouvrir la console du navigateur (F12)
- Vérifier les erreurs JavaScript
- Vérifier les requêtes réseau

## Performance

- Temps de réponse API : < 100ms
- Temps de chargement page : < 2s
- Debounce recherche : 300ms
- Animation : 60fps
