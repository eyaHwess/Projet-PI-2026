# 🔍 Filtres Avancés - Recherche de Coaches

## ✅ Fonctionnalités Implémentées

### 1. Filtrage par Spécialité
- Liste déroulante avec toutes les spécialités disponibles
- Option "Toutes les spécialités" pour réinitialiser ce filtre
- Icône étoile pour identifier visuellement

### 2. Filtrage par Prix (Min - Max)
- **Prix minimum** : Champ numérique avec pas de 5€
- **Prix maximum** : Champ numérique avec pas de 5€
- Filtre les coaches dont le prix par session est dans la fourchette
- Icône euro pour identifier visuellement

### 3. Filtrage par Note Minimum
- Liste déroulante avec options prédéfinies :
  - 4.5+ ⭐ (Excellent)
  - 4+ ⭐ (Très bien)
  - 3.5+ ⭐ (Bien)
  - 3+ ⭐ (Correct)
- Affiche uniquement les coaches avec une note égale ou supérieure
- Icône étoile dorée pour identifier visuellement

### 4. Filtrage par Disponibilité
- Liste déroulante avec toutes les disponibilités des coaches
- Exemples : "Lundi-Vendredi", "Week-end", "Flexible", etc.
- Icône calendrier pour identifier visuellement

### 5. Bouton "Réinitialiser les Filtres"
- Bouton rose pastel en haut à droite du panneau de filtres
- Icône de flèche circulaire
- Redirige vers `/coaches` sans paramètres
- Efface tous les filtres appliqués

## 🎨 Design

### Panneau de Filtres
- Fond bleu très clair (#F8FCFE)
- Bordure bleu pastel (2px solid #D4EEF7)
- Coins arrondis (border-radius: 1.5rem)
- Padding généreux pour une meilleure lisibilité

### Champs de Formulaire
- Bordure bleu pastel cohérente
- Coins arrondis (0.75rem)
- Labels avec icônes colorées
- Focus avec effet de halo bleu

### Bouton "Appliquer les filtres"
- Style cohérent avec le bouton principal (btn-orange)
- Gradient bleu ciel pastel
- Icône entonnoir
- Effet hover avec élévation

### Bouton "Réinitialiser"
- Couleur rose pastel (--pastel-danger)
- Petit format (btn-sm)
- Coins arrondis (50px)
- Texte en couleur sombre (#8B5F7A)

## 📊 Affichage des Informations sur les Cartes

Chaque carte de coach affiche maintenant :

1. **Avatar** avec initiales
2. **Nom complet** du coach
3. **Email** avec icône
4. **Spécialité** (badge bleu pastel)
5. **Note** avec étoiles dorées (1-5)
6. **Prix par session** (€/session) avec icône euro
7. **Disponibilité** avec icône calendrier vert menthe
8. **Statut de la demande** (si applicable)
9. **Bouton "Contacter ce coach"** (si pas de demande en cours)

## 🔄 Fonctionnement

### Application des Filtres
1. L'utilisateur sélectionne un ou plusieurs filtres
2. Clique sur "Appliquer les filtres"
3. La page se recharge avec les paramètres dans l'URL
4. Le contrôleur filtre les coaches côté serveur
5. Seuls les coaches correspondants sont affichés

### Réinitialisation
1. L'utilisateur clique sur "Réinitialiser"
2. Redirection vers `/coaches` sans paramètres
3. Tous les coaches sont affichés
4. Tous les champs de filtre sont vides

## 🎯 Combinaison avec la Recherche

Les filtres et la recherche fonctionnent ensemble :
- **Filtres** : Appliqués côté serveur (rechargement de page)
- **Recherche** : Appliquée côté client (JavaScript, sans rechargement)

Workflow typique :
1. Appliquer des filtres (ex: spécialité "Yoga", prix max 50€)
2. La page se recharge avec les coaches filtrés
3. Utiliser la barre de recherche pour affiner (ex: chercher "Marie")
4. La recherche filtre dynamiquement les résultats déjà filtrés

## 📱 Responsive

- **Desktop** : Filtres sur une ligne (4 colonnes)
- **Tablet** : Filtres sur 2 lignes (2 colonnes par ligne)
- **Mobile** : Filtres empilés verticalement (1 colonne)

## 🚀 Pour Tester

1. Accédez à `/coaches`
2. Essayez différentes combinaisons de filtres :
   - Spécialité "Yoga" + Prix max 50€
   - Note minimum 4+ + Disponibilité "Week-end"
   - Prix entre 30€ et 80€
3. Cliquez sur "Réinitialiser" pour tout effacer
4. Combinez avec la recherche textuelle

## 💡 Améliorations Futures Possibles

- Filtres en temps réel (sans rechargement)
- Slider pour le prix (au lieu de 2 champs)
- Compteur de résultats par filtre
- Sauvegarde des filtres préférés
- Filtres avancés (certifications, années d'expérience, etc.)
