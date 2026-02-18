# Système de Demande de Coaching Amélioré

## 🎯 Vue d'ensemble

Le système de demande de coaching a été considérablement amélioré avec des fonctionnalités avancées pour offrir une expérience utilisateur moderne et fluide.

## ✨ Nouvelles Fonctionnalités

### 1. Recherche Dynamique
- **Barre de recherche en temps réel** : Recherche instantanée par nom, spécialité ou mot-clé
- **Suggestions automatiques** : Résultats mis à jour pendant la saisie
- **Bouton de réinitialisation** : Effacer rapidement la recherche

### 2. Système de Filtrage Avancé
- **Spécialité** : Filtrer par domaine d'expertise (Yoga, Musculation, Nutrition, etc.)
- **Prix** : Plage de prix personnalisable (min/max)
- **Note** : Filtrer par note minimum (3+, 4+, 4.5+)
- **Disponibilité** : Disponible, Limité
- **Type de coaching** : En ligne, En présentiel, Hybride

### 3. Options de Tri
- **Mieux notés** : Coaches avec les meilleures évaluations
- **Prix croissant/décroissant** : Trier par tarif
- **Popularité** : Basé sur le nombre de séances réalisées
- **Disponibilité** : Coaches disponibles en premier

### 4. Cartes de Coach Enrichies
Chaque carte affiche :
- **Photo de profil** (ou avatar par défaut)
- **Note moyenne** avec nombre d'avis
- **Prix par séance**
- **Disponibilité**
- **Biographie courte**
- **Badges** :
  - 🏆 Top coach
  - ⚡ Répond rapidement
  - ✨ Nouveau
- **Nombre de séances réalisées**
- **Bouton "Demande rapide"**

### 5. Formulaire de Demande Amélioré

#### Champs structurés :
- **Objectif principal** : Perte de poids, Prise de masse, Remise en forme, etc.
- **Niveau actuel** : Débutant, Intermédiaire, Avancé
- **Fréquence souhaitée** : 1 à 4+ fois par semaine
- **Budget par séance** : Montant en euros
- **Message personnalisé** : Description détaillée des besoins

#### Fonctionnalités :
- ✅ **Validation en temps réel**
- 📊 **Compteur de caractères** (0/1000)
- 🎨 **Animation d'envoi** avec spinner
- ✅ **Message de confirmation** avec animation de succès
- 🔒 **Protection CSRF**

### 6. UI/UX Moderne

#### Design :
- **Palette de couleurs** : Orange (#f97316) comme couleur principale
- **Typographie** : Inter, système fonts
- **Espacement** : Design aéré et lisible
- **Responsive** : Adapté mobile, tablette et desktop

#### Animations :
- **Fade-in** : Apparition progressive des cartes
- **Hover effects** : Élévation et changement de couleur
- **Micro-animations** : Transitions fluides
- **Loading states** : Spinners et états de chargement
- **Success animation** : Checkmark animé après envoi

#### États visuels :
- **Loading** : Spinner pendant le chargement
- **Empty state** : Message quand aucun coach trouvé
- **Success state** : Confirmation visuelle d'envoi
- **Error handling** : Messages d'erreur clairs

## 🗄️ Structure de la Base de Données

### Nouveaux champs User (Coach)
```php
- reviewCount: int          // Nombre d'avis
- pricePerSession: float    // Prix par séance
- bio: string(500)          // Biographie
- photoUrl: string(255)     // URL de la photo
- badges: json              // Badges (Top coach, etc.)
- respondsQuickly: bool     // Répond rapidement
- totalSessions: int        // Nombre total de séances
```

### Nouveaux champs CoachingRequest
```php
- goal: string(100)         // Objectif principal
- level: string(50)         // Niveau actuel
- frequency: string(50)     // Fréquence souhaitée
- budget: float             // Budget par séance
- coachingType: string(50)  // Type de coaching
```

## 🚀 Installation et Utilisation

### 1. Appliquer les migrations
```bash
php bin/console doctrine:migrations:migrate
```

### 2. Peupler la base avec des coaches de test
```bash
php bin/console app:populate-coaches
```

### 3. Accéder à la nouvelle interface
```
URL: /coaches/enhanced
```

### 4. API Endpoints

#### Recherche de coaches
```
GET /api/coaches/search
Paramètres:
- q: string (recherche)
- speciality: string
- minPrice: float
- maxPrice: float
- minRating: float
- availability: string
- coachingType: string
- sortBy: string (rating|price|popularity|availability)
- sortOrder: string (asc|desc)
```

#### Filtres disponibles
```
GET /api/coaches/filters
Retourne: specialities, priceRange, availabilities, coachingTypes
```

## 📁 Fichiers Créés/Modifiés

### Nouveaux fichiers :
- `src/Controller/CoachSearchController.php` - API de recherche
- `src/Command/PopulateCoachesCommand.php` - Commande de peuplement
- `templates/coach/index_enhanced.html.twig` - Nouvelle interface
- `migrations/Version20260215213355.php` - Migration BDD

### Fichiers modifiés :
- `src/Entity/User.php` - Nouveaux champs coach
- `src/Entity/CoachingRequest.php` - Nouveaux champs demande
- `src/Repository/UserRepository.php` - Méthodes de recherche
- `src/Form/CoachingRequestType.php` - Formulaire enrichi
- `src/Controller/CoachController.php` - Nouvelle route

## 🎨 Personnalisation

### Couleurs
Modifier les variables CSS dans `index_enhanced.html.twig` :
```css
:root {
    --orange-primary: #f97316;
    --orange-hover: #ea580c;
    --orange-light: #fff5f0;
}
```

### Badges
Ajouter des badges personnalisés dans `PopulateCoachesCommand.php` :
```php
'badges' => ['Top coach', 'Répond rapidement', 'Nouveau', 'Certifié']
```

## 🔧 Configuration

### Ajuster les limites
- **Message** : 10-1000 caractères (modifiable dans `CoachingRequest.php`)
- **Prix** : Min 0€ (modifiable dans les filtres)
- **Résultats** : Illimité (ajouter pagination si nécessaire)

## 📱 Responsive Design

- **Mobile** : 1 colonne
- **Tablette** : 2 colonnes
- **Desktop** : 3 colonnes
- **Large** : 3-4 colonnes

## 🔐 Sécurité

- ✅ Protection CSRF sur tous les formulaires
- ✅ Validation côté serveur
- ✅ Sanitization des entrées
- ✅ Authentification requise pour les demandes

## 🚀 Performance

- **Recherche debounced** : 300ms de délai
- **Chargement asynchrone** : API REST
- **Animations optimisées** : GPU-accelerated
- **Images lazy-loaded** : Chargement différé

## 📊 Métriques

Le système track automatiquement :
- Nombre de séances par coach
- Notes moyennes
- Nombre d'avis
- Temps de réponse

## 🎯 Prochaines Améliorations Possibles

1. **Pagination** : Pour gérer un grand nombre de coaches
2. **Favoris** : Sauvegarder des coaches préférés
3. **Comparaison** : Comparer plusieurs coaches
4. **Calendrier** : Voir les disponibilités en temps réel
5. **Chat** : Messagerie instantanée avec les coaches
6. **Avis** : Système de notation et commentaires
7. **Photos multiples** : Galerie de photos pour chaque coach
8. **Vidéos** : Présentation vidéo des coaches
9. **Certifications** : Afficher les diplômes et certifications
10. **Géolocalisation** : Trouver des coaches à proximité

## 📞 Support

Pour toute question ou problème, consultez la documentation Symfony ou contactez l'équipe de développement.

---

**Version** : 1.0.0  
**Date** : 15 février 2026  
**Auteur** : Kiro AI Assistant
