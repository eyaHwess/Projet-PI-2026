# ⭐ Système d'Avis & Feedback

## ✅ Fonctionnalités Implémentées

### 1. Entité Review
Base de données complète pour stocker les avis :
- **user** : Utilisateur qui laisse l'avis
- **coach** : Coach évalué
- **rating** : Note de 1 à 5 étoiles (float)
- **comment** : Commentaire textuel
- **createdAt** : Date de création
- **updatedAt** : Date de modification
- **isVisible** : Visibilité de l'avis (modération)
- **isVerified** : Badge "Vérifié" pour les avis authentifiés

### 2. Repository avec Méthodes Avancées

#### findVisibleByCoach(User $coach)
Récupère tous les avis visibles pour un coach, triés par date décroissante.

#### getAverageRatingForCoach(User $coach)
Calcule la moyenne des notes (arrondie à 1 décimale).

#### countReviewsForCoach(User $coach)
Compte le nombre total d'avis visibles.

#### hasUserReviewedCoach(User $user, User $coach)
Vérifie si un utilisateur a déjà laissé un avis (évite les doublons).

#### getRatingStatsForCoach(User $coach)
Retourne des statistiques complètes :
```php
[
    'total' => 15,
    'average' => 4.6,
    'distribution' => [
        5 => 10,
        4 => 3,
        3 => 2,
        2 => 0,
        1 => 0
    ]
]
```

### 3. Affichage sur les Cartes de Coach

Chaque carte de coach affiche :
- **Étoiles visuelles** : 5 étoiles avec remplissage doré
- **Note moyenne** : Format "4.6/5"
- **Badge "Voir les avis"** : Cliquable pour ouvrir le modal
- **Compteur d'avis** : Mis à jour dynamiquement

### 4. Modal Détaillé des Avis

#### Section Statistiques (en haut)
- **Note moyenne** : Grande taille, bien visible
- **Étoiles visuelles** : Représentation graphique
- **Nombre total d'avis** : "Basé sur X avis"
- **Distribution des notes** : Barres de progression pour chaque niveau (1-5 étoiles)

#### Section Liste des Avis
Chaque avis affiche :
- **Avatar** : Initiales de l'utilisateur
- **Nom** : Format "Prénom N." (anonymisation du nom)
- **Date** : Format relatif ("il y a 2 mois")
- **Note** : 5 étoiles avec remplissage
- **Badge "Vérifié"** : Si l'avis est authentifié
- **Commentaire** : Texte complet de l'avis

### 5. Distribution des Notes (Barres de Progression)

Visualisation graphique de la répartition :
- Barre pour chaque niveau (5, 4, 3, 2, 1 étoile)
- Pourcentage de remplissage
- Compteur à droite
- Gradient doré pour les barres
- Animation au chargement

### 6. Commande de Peuplement

`php bin/console app:populate-reviews`

Crée automatiquement :
- 3 à 8 avis par coach
- Notes variées (3.5 à 5 étoiles)
- Commentaires réalistes
- Dates aléatoires (6 derniers mois)
- 50% d'avis vérifiés
- Création d'utilisateurs de test si nécessaire

### 7. API Endpoint

**GET /reviews/coach/{id}**

Retourne les avis et statistiques d'un coach :

```json
{
  "success": true,
  "coach": {
    "id": 5,
    "firstName": "Marie",
    "lastName": "Dupont"
  },
  "stats": {
    "total": 15,
    "average": 4.6,
    "distribution": {
      "5": 10,
      "4": 3,
      "3": 2,
      "2": 0,
      "1": 0
    }
  },
  "reviews": [
    {
      "id": 1,
      "rating": 5,
      "comment": "Excellent coach ! Très professionnel...",
      "userName": "Sophie M.",
      "isVerified": true,
      "createdAt": "15/01/2026",
      "createdAtRelative": "il y a 1 mois"
    }
  ]
}
```

## 🎨 Design

### Badge "Voir les avis"
- Fond bleu clair (var(--pastel-light))
- Icône chat
- Hover : Fond bleu pastel + scale(1.05)
- Transition fluide (0.3s)

### Modal
- **Header** : Gradient bleu pastel
- **Bordure** : 2px solid bleu pastel
- **Coins arrondis** : 1.5rem
- **Scrollable** : Max-height 500px pour la liste

### Cartes d'Avis
- Fond bleu très clair
- Bordure bleu pastel
- Hover : Bordure plus foncée + ombre
- Padding généreux
- Transition fluide

### Avatar Utilisateur
- Cercle avec gradient bleu
- Initiales en gras
- Taille : 40px × 40px

### Badge "Vérifié"
- Fond vert menthe pastel
- Icône patch-check
- Texte vert foncé
- Taille réduite (0.625rem)

### Barres de Distribution
- Fond gris clair (#E8E8E8)
- Remplissage : Gradient doré (#FFD700 → #FFA500)
- Hauteur : 8px
- Coins arrondis
- Animation de remplissage (0.5s)

## 🔄 Workflow Utilisateur

1. **Navigation** : L'utilisateur parcourt les coaches
2. **Découverte** : Voit la note moyenne et le badge "Voir les avis"
3. **Clic** : Clique sur "Voir les avis"
4. **Modal** : Le modal s'ouvre avec chargement
5. **Statistiques** : Voit la note moyenne et la distribution
6. **Lecture** : Parcourt les avis détaillés
7. **Fermeture** : Ferme le modal pour continuer

## 📊 Exemples de Commentaires

### 5 étoiles
- "Excellent coach ! Très professionnel et à l'écoute. J'ai atteint mes objectifs en 3 mois."
- "Super expérience ! Le coach est motivant et les séances sont bien structurées."
- "Je recommande vivement ! Résultats visibles rapidement et ambiance agréable."

### 4.5 étoiles
- "Très bon coach, quelques petits ajustements à faire mais globalement satisfait."
- "Bonne expérience, le coach est compétent et sympathique."

### 4 étoiles
- "Bon coach dans l'ensemble, mais les horaires ne sont pas toujours flexibles."
- "Satisfait mais j'aurais aimé plus de suivi entre les séances."

### 3.5 étoiles
- "Correct mais manque un peu de personnalisation dans les programmes."
- "Pas mal mais j'attendais un peu plus de dynamisme."

## 🔒 Sécurité & Modération

### Anonymisation
- Nom de famille réduit à l'initiale ("Sophie M.")
- Protection de la vie privée

### Visibilité
- Flag `isVisible` pour masquer les avis inappropriés
- Modération possible par les admins

### Vérification
- Badge "Vérifié" pour les avis authentifiés
- Augmente la confiance des utilisateurs

### Anti-spam
- Méthode `hasUserReviewedCoach()` pour éviter les doublons
- Un seul avis par utilisateur par coach

## 📱 Responsive

### Desktop
- Modal large (modal-lg)
- Cartes d'avis spacieuses
- Distribution visible

### Tablet
- Modal adapté
- Cartes empilées
- Scrolling fluide

### Mobile
- Modal plein écran
- Cartes compactes
- Avatar plus petit
- Texte adapté

## 🚀 Performance

### Optimisations
- Chargement asynchrone des avis (AJAX)
- Modal chargé à la demande
- Pas de requêtes inutiles
- Cache possible côté serveur

### Indexation Base de Données
- Index sur `coach_id` pour requêtes rapides
- Index sur `isVisible` pour filtrage
- Index composite possible

## 💡 Améliorations Futures Possibles

1. **Formulaire d'ajout d'avis**
   - Permettre aux utilisateurs de laisser des avis
   - Validation et modération

2. **Réponses du coach**
   - Le coach peut répondre aux avis
   - Dialogue visible

3. **Filtres d'avis**
   - Par note (5 étoiles, 4+, etc.)
   - Par date (récents, anciens)
   - Par vérification

4. **Tri des avis**
   - Plus récents
   - Plus utiles
   - Meilleure note

5. **Votes utiles**
   - "Cet avis vous a-t-il été utile ?"
   - Compteur de votes

6. **Photos dans les avis**
   - Upload d'images
   - Galerie visuelle

7. **Signalement d'avis**
   - Bouton "Signaler"
   - Modération renforcée

8. **Statistiques avancées**
   - Évolution de la note dans le temps
   - Comparaison avec d'autres coaches
   - Graphiques interactifs

9. **Badges de qualité**
   - "Top coach" si note > 4.8
   - "Nouveau" si < 5 avis
   - "Populaire" si > 50 avis

10. **Export des avis**
    - PDF pour le coach
    - Partage sur réseaux sociaux

## 🎯 Métriques de Succès

- **Taux de consultation** : % d'utilisateurs qui ouvrent le modal
- **Temps de lecture** : Durée moyenne dans le modal
- **Conversion** : % qui contactent après avoir lu les avis
- **Satisfaction** : Note moyenne globale de la plateforme

## 🔧 Configuration

Aucune configuration supplémentaire nécessaire. Le système est prêt à l'emploi après :
1. Migration de la base de données (automatique)
2. Peuplement des avis de test (optionnel)
3. Accès à `/coaches`

## 📝 Notes Techniques

- **Symfony 6+** compatible
- **Doctrine ORM** pour la persistance
- **Bootstrap 5** pour le modal
- **JavaScript Vanilla** (pas de framework)
- **API REST** pour les avis
- **Format JSON** pour les échanges
