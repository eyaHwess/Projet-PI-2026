# 🤖 Système de Recommandation Intelligent de Coaches

## ✅ Fonctionnalités Implémentées

### 1. Analyse Automatique du Message
- Analyse en temps réel du message de l'utilisateur (dès 10 caractères)
- Détection des mots-clés liés aux spécialités sportives
- Identification des objectifs (perte de poids, prise de masse, etc.)
- Reconnaissance des préférences de disponibilité

### 2. Algorithme de Scoring Intelligent
Le système calcule un score de compatibilité basé sur plusieurs critères :

#### Spécialité (40 points max)
- Détection de mots-clés par spécialité :
  - **Yoga** : yoga, méditation, relaxation, souplesse, zen, stress, calme
  - **Musculation** : muscle, force, poids, haltère, bodybuilding, masse
  - **Cardio** : cardio, course, running, endurance, vélo, natation, marathon
  - **Nutrition** : nutrition, alimentation, régime, manger, poids, maigrir
  - **CrossFit** : crossfit, hiit, intense, wod, fonctionnel, explosif
  - **Pilates** : pilates, posture, core, gainage, dos, colonne
  - **Boxe** : boxe, combat, frappe, ring, punch
  - **Danse** : danse, chorégraphie, rythme, zumba, mouvement

#### Objectifs (20 points max)
- **Perte de poids** : maigrir, perdre, poids, mincir, affiner
- **Prise de masse** : muscle, masse, grossir, prendre, volume
- **Remise en forme** : forme, santé, bien-être, condition, fitness
- **Performance** : performance, compétition, améliorer, progresser, record
- **Rééducation** : rééducation, blessure, récupération, douleur, kiné

#### Note du Coach (20 points max)
- Score proportionnel à la note : (note/5) × 20
- Exemple : Coach avec 4.5/5 = 18 points

#### Prix Attractif (10 points max)
- ≤ 30€ : 10 points
- ≤ 50€ : 7 points
- ≤ 70€ : 4 points
- > 70€ : 0 point

#### Disponibilité (10 points max)
- Correspondance entre les créneaux mentionnés et la disponibilité du coach
- Mots-clés : matin, soir, midi, week-end, semaine, jours de la semaine

### 3. Top 3 Recommandations
- Affichage des 3 coaches les plus compatibles
- Tri par score décroissant
- Mise en avant du meilleur match avec badge "👑 Meilleur match"

### 4. Section "Recommandé pour vous"
- Apparition automatique après 10 caractères tapés
- Design distinctif avec fond jaune pastel
- Badge "💡 Recommandé pour vous"
- Animation de slide-down élégante

### 5. Affichage Détaillé des Recommandations

Chaque carte de recommandation affiche :
- **Avatar** du coach
- **Nom complet**
- **Spécialité** (badge)
- **Score de compatibilité** (en %)
- **Note** (étoiles)
- **Prix** par session
- **Disponibilité**
- **Raisons du match** (badges)
- **Bouton "Choisir ce coach"**

### 6. Meilleur Match Mis en Avant
Le coach le plus compatible bénéficie de :
- Badge doré "🏆 Meilleur match"
- Bordure dorée (#FFD700)
- Ombre portée dorée
- Icône couronne en filigrane
- Animation pulse

### 7. Sélection Rapide
- Bouton "Choisir ce coach" sur chaque recommandation
- Sélection automatique dans le formulaire
- Scroll automatique vers le formulaire
- Highlight visuel du champ sélectionné (bordure dorée)

## 🎨 Design

### Section Recommandations
- Fond : Gradient jaune pastel (#FFF9F0 → #FFF5E8)
- Bordure : 2px solid var(--pastel-warning)
- Coins arrondis : 1.5rem
- Animation : slideDown (0.5s)

### Cartes Recommandées
- Fond blanc
- Bordure : 3px solid var(--pastel-warning)
- Hover : Élévation avec ombre jaune
- Transition fluide (0.3s)

### Badge "Meilleur Match"
- Gradient doré (#FFD700 → #FFA500)
- Texte blanc
- Icône trophée
- Ombre portée dorée

### Badges de Raisons
- Fond bleu clair (var(--pastel-light))
- Texte bleu foncé (#4A7C9D)
- Icône check-circle
- Bordure subtile

### Score de Compatibilité
- Gradient doré
- Format : "XX% compatible"
- Police bold
- Taille réduite (0.75rem)

## 🔄 Workflow Utilisateur

1. **Début de saisie** : L'utilisateur commence à taper son message
2. **Analyse en temps réel** : Après 10 caractères, le système analyse (délai 1s)
3. **Affichage des recommandations** : Les 3 meilleurs coaches apparaissent
4. **Exploration** : L'utilisateur peut voir les raisons du match
5. **Sélection rapide** : Clic sur "Choisir ce coach"
6. **Auto-remplissage** : Le coach est sélectionné dans le formulaire
7. **Envoi** : L'utilisateur peut envoyer sa demande

## 📊 Exemples de Messages et Résultats

### Exemple 1 : Perte de poids
**Message** : "Je veux perdre du poids et me remettre en forme, disponible le soir"

**Analyse** :
- Objectif détecté : Perte de poids (+20 points)
- Disponibilité : Soir (+10 points si match)
- Spécialités favorisées : Cardio, Nutrition

**Résultat** : Coaches spécialisés en cardio/nutrition avec bonne note

### Exemple 2 : Musculation
**Message** : "Je cherche un coach pour prendre de la masse musculaire, je veux devenir plus fort"

**Analyse** :
- Spécialité détectée : Musculation (+40 points)
- Objectif : Prise de masse (+20 points)
- Spécialités favorisées : Musculation

**Résultat** : Coaches musculation avec meilleure note en premier

### Exemple 3 : Yoga et relaxation
**Message** : "J'ai beaucoup de stress au travail, je cherche du yoga pour me détendre"

**Analyse** :
- Spécialité détectée : Yoga (+40 points)
- Mots-clés : stress, détendre
- Spécialités favorisées : Yoga

**Résultat** : Coaches yoga avec prix attractif

## 🚀 API Endpoint

### POST /coaches/recommendations

**Request Body** :
```json
{
  "message": "Je veux perdre du poids avec du cardio"
}
```

**Response** :
```json
{
  "success": true,
  "recommendations": [
    {
      "id": 5,
      "firstName": "Marie",
      "lastName": "Dupont",
      "email": "marie@example.com",
      "speciality": "Cardio",
      "rating": 4.8,
      "pricePerSession": 45,
      "availability": "Lundi-Vendredi soir",
      "score": 88,
      "reasons": [
        "Spécialiste en Cardio",
        "Excellente note (4.8/5)",
        "Prix attractif (45€)"
      ]
    },
    // ... 2 autres coaches
  ]
}
```

## 💡 Avantages

1. **Gain de temps** : L'utilisateur n'a pas à parcourir tous les coaches
2. **Pertinence** : Recommandations basées sur l'analyse sémantique
3. **Transparence** : Affichage des raisons du match
4. **UX fluide** : Sélection en un clic
5. **Temps réel** : Recommandations mises à jour pendant la saisie

## 🔧 Configuration

Le service `CoachRecommendationService` est automatiquement enregistré grâce à l'autowiring Symfony.

Aucune configuration supplémentaire n'est nécessaire.

## 📱 Responsive

- **Desktop** : 3 cartes visibles, layout horizontal
- **Tablet** : 2 cartes par ligne
- **Mobile** : 1 carte par ligne, layout vertical

## 🎯 Améliorations Futures Possibles

- Machine Learning pour améliorer les recommandations
- Historique des préférences utilisateur
- Filtres personnalisés dans les recommandations
- Notation de la pertinence des recommandations
- Intégration avec un système de matching avancé
- Recommandations basées sur les avis d'autres utilisateurs similaires
