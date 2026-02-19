# Checklist de Test - Système de Coaching Amélioré

## 📋 Tests Préliminaires

### Configuration
- [ ] Serveur Symfony démarré (`symfony server:start`)
- [ ] Base de données à jour (`php bin/console doctrine:migrations:migrate`)
- [ ] Coaches de test créés (`php bin/console app:populate-coaches`)
- [ ] Utilisateur de test connecté

### Accès à l'Interface
- [ ] URL accessible : `/coaches/enhanced`
- [ ] Page se charge sans erreur
- [ ] Tous les assets CSS/JS chargés
- [ ] Aucune erreur dans la console du navigateur

## 🔍 Tests de Recherche

### Recherche Basique
- [ ] Taper "yoga" dans la barre de recherche
  - Résultat attendu : Affiche les coaches de yoga
- [ ] Taper "marie" dans la barre de recherche
  - Résultat attendu : Affiche les coaches nommés Marie
- [ ] Taper "nutrition" dans la barre de recherche
  - Résultat attendu : Affiche les coaches en nutrition

### Recherche Avancée
- [ ] Taper un mot qui n'existe pas (ex: "zzzzz")
  - Résultat attendu : Message "Aucun coach trouvé"
- [ ] Taper puis effacer rapidement
  - Résultat attendu : Debounce fonctionne (pas de requête à chaque lettre)
- [ ] Cliquer sur le bouton X
  - Résultat attendu : Recherche effacée, tous les coaches affichés

### Compteur de Résultats
- [ ] Vérifier que le compteur affiche le bon nombre
  - Format : "X coach(es) trouvé(s)"
- [ ] Vérifier que le compteur se met à jour après recherche
- [ ] Vérifier que le compteur se met à jour après filtrage

## 🎛️ Tests de Filtres

### Filtre Spécialité
- [ ] Sélectionner "Yoga"
  - Résultat attendu : Affiche uniquement les coaches de yoga
- [ ] Sélectionner "Musculation"
  - Résultat attendu : Affiche uniquement les coaches de musculation
- [ ] Revenir à "Toutes les spécialités"
  - Résultat attendu : Affiche tous les coaches

### Filtre Prix
- [ ] Entrer prix min : 30
  - Résultat attendu : Affiche coaches avec prix >= 30€
- [ ] Entrer prix max : 60
  - Résultat attendu : Affiche coaches avec prix <= 60€
- [ ] Entrer min : 30, max : 60
  - Résultat attendu : Affiche coaches entre 30€ et 60€
- [ ] Effacer les prix
  - Résultat attendu : Affiche tous les coaches

### Filtre Note
- [ ] Sélectionner "4.5+ ⭐"
  - Résultat attendu : Affiche uniquement coaches avec note >= 4.5
- [ ] Sélectionner "4+ ⭐"
  - Résultat attendu : Affiche uniquement coaches avec note >= 4
- [ ] Sélectionner "3+ ⭐"
  - Résultat attendu : Affiche uniquement coaches avec note >= 3

### Filtre Disponibilité
- [ ] Sélectionner "Disponible"
  - Résultat attendu : Affiche uniquement coaches disponibles
- [ ] Sélectionner "Limité"
  - Résultat attendu : Affiche uniquement coaches avec disponibilité limitée

### Filtre Type de Coaching
- [ ] Sélectionner "En ligne"
  - Résultat attendu : Affiche uniquement coaches en ligne
- [ ] Sélectionner "En présentiel"
  - Résultat attendu : Affiche uniquement coaches en présentiel
- [ ] Sélectionner "Hybride"
  - Résultat attendu : Affiche uniquement coaches hybrides

### Combinaison de Filtres
- [ ] Spécialité "Yoga" + Prix max 50€
  - Résultat attendu : Coaches de yoga à max 50€
- [ ] Note 4+ + Disponible
  - Résultat attendu : Coaches bien notés et disponibles
- [ ] Tous les filtres en même temps
  - Résultat attendu : Résultats correspondant à tous les critères

### Réinitialisation
- [ ] Appliquer plusieurs filtres
- [ ] Cliquer sur "Réinitialiser"
  - Résultat attendu : Tous les filtres effacés
  - Résultat attendu : Tous les coaches affichés
  - Résultat attendu : Tous les champs de filtre vides

## 🔄 Tests de Tri

### Tri par Note
- [ ] Cliquer sur "Mieux notés"
  - Résultat attendu : Bouton surligné en orange
  - Résultat attendu : Coaches triés par note décroissante
  - Résultat attendu : Premier coach a la meilleure note

### Tri par Prix
- [ ] Cliquer sur "Prix croissant"
  - Résultat attendu : Bouton surligné
  - Résultat attendu : Coaches triés du moins cher au plus cher
- [ ] Cliquer sur "Prix décroissant"
  - Résultat attendu : Bouton surligné
  - Résultat attendu : Coaches triés du plus cher au moins cher

### Tri par Popularité
- [ ] Cliquer sur "Popularité"
  - Résultat attendu : Bouton surligné
  - Résultat attendu : Coaches triés par nombre de séances décroissant
  - Résultat attendu : Premier coach a le plus de séances

### Changement de Tri
- [ ] Cliquer sur "Mieux notés" puis "Prix croissant"
  - Résultat attendu : Seul "Prix croissant" est surligné
  - Résultat attendu : Ordre change instantanément
- [ ] Vérifier que le tri persiste après filtrage
  - Résultat attendu : L'ordre de tri reste actif

## 📝 Tests du Formulaire de Demande

### Ouverture du Modal
- [ ] Cliquer sur "Demande rapide" sur une carte
  - Résultat attendu : Modal s'ouvre
  - Résultat attendu : Informations du coach affichées
  - Résultat attendu : Formulaire vide et prêt

### Validation Champ "Objectif"
- [ ] Laisser vide et cliquer ailleurs
  - Résultat attendu : Pas de validation (optionnel au début)
- [ ] Sélectionner "Perte de poids"
  - Résultat attendu : Bordure verte
  - Résultat attendu : Icône ✓ visible
- [ ] Revenir à "Sélectionnez votre objectif"
  - Résultat attendu : Bordure rouge si tentative d'envoi

### Validation Champ "Niveau"
- [ ] Laisser vide
  - Résultat attendu : Pas de validation initiale
- [ ] Sélectionner "Débutant"
  - Résultat attendu : Bordure verte
  - Résultat attendu : Icône ✓ visible
- [ ] Sélectionner "Intermédiaire"
  - Résultat attendu : Reste valide
- [ ] Sélectionner "Avancé"
  - Résultat attendu : Reste valide

### Validation Champ "Fréquence"
- [ ] Laisser vide
  - Résultat attendu : Pas de validation initiale
- [ ] Sélectionner "2 fois/semaine"
  - Résultat attendu : Bordure verte
  - Résultat attendu : Icône ✓ visible

### Validation Champ "Budget"
- [ ] Laisser vide
  - Résultat attendu : Pas d'erreur (optionnel)
- [ ] Entrer "50"
  - Résultat attendu : Bordure verte
- [ ] Entrer "-10"
  - Résultat attendu : Bordure rouge
  - Résultat attendu : Message "Le budget doit être un nombre positif"
- [ ] Entrer "abc"
  - Résultat attendu : Champ refuse la saisie (type number)

### Validation Champ "Message"

#### Test Longueur Minimale
- [ ] Taper "Test" (4 caractères)
  - Résultat attendu : Bordure rouge
  - Résultat attendu : Message d'erreur visible
  - Résultat attendu : Compteur : "4 / 1000 caractères"
- [ ] Taper "Test message" (12 caractères)
  - Résultat attendu : Bordure verte
  - Résultat attendu : Message d'erreur disparu
  - Résultat attendu : Compteur : "12 / 1000 caractères"

#### Test Compteur de Caractères
- [ ] Taper 800 caractères
  - Résultat attendu : Compteur noir
  - Résultat attendu : Bordure verte
- [ ] Taper 850 caractères
  - Résultat attendu : Compteur orange (warning)
  - Résultat attendu : Bordure verte
- [ ] Taper 950 caractères
  - Résultat attendu : Compteur orange foncé
  - Résultat attendu : Bordure verte
- [ ] Taper 1001 caractères
  - Résultat attendu : Compteur rouge
  - Résultat attendu : Bordure rouge
  - Résultat attendu : Message d'erreur visible

#### Test Temps Réel
- [ ] Taper caractère par caractère
  - Résultat attendu : Compteur se met à jour instantanément
  - Résultat attendu : Validation change en temps réel
- [ ] Effacer caractère par caractère
  - Résultat attendu : Compteur décrémente
  - Résultat attendu : Validation s'ajuste

### Test d'Envoi du Formulaire

#### Envoi avec Erreurs
- [ ] Laisser tous les champs vides
- [ ] Cliquer sur "Envoyer la demande"
  - Résultat attendu : Formulaire ne s'envoie pas
  - Résultat attendu : Tous les champs obligatoires en rouge
  - Résultat attendu : Messages d'erreur visibles

#### Envoi Partiel
- [ ] Remplir uniquement "Objectif" et "Niveau"
- [ ] Cliquer sur "Envoyer la demande"
  - Résultat attendu : Formulaire ne s'envoie pas
  - Résultat attendu : "Fréquence" et "Message" en rouge

#### Envoi Valide
- [ ] Remplir tous les champs obligatoires correctement
  - Objectif : "Perte de poids"
  - Niveau : "Débutant"
  - Fréquence : "2 fois/semaine"
  - Budget : "50" (optionnel)
  - Message : "Je souhaite perdre du poids et retrouver la forme"
- [ ] Cliquer sur "Envoyer la demande"
  - Résultat attendu : Bouton affiche spinner
  - Résultat attendu : Texte change en "Envoi..."
  - Résultat attendu : Bouton désactivé
  - Résultat attendu : Requête AJAX envoyée

#### Confirmation d'Envoi
- [ ] Après envoi réussi
  - Résultat attendu : Formulaire disparaît
  - Résultat attendu : Animation de succès (checkmark) s'affiche
  - Résultat attendu : Message "Demande envoyée !"
  - Résultat attendu : Modal se ferme après 3 secondes
  - Résultat attendu : Formulaire réinitialisé pour prochaine utilisation

#### Gestion des Erreurs
- [ ] Simuler une erreur réseau (déconnecter internet)
- [ ] Essayer d'envoyer
  - Résultat attendu : Message d'erreur "Erreur réseau"
  - Résultat attendu : Bouton redevient actif
  - Résultat attendu : Formulaire reste rempli

## 🎨 Tests Visuels

### Cartes de Coach
- [ ] Vérifier que toutes les informations s'affichent
  - Photo ou avatar
  - Nom et prénom
  - Spécialité
  - Note et nombre d'avis
  - Prix par séance
  - Disponibilité
  - Biographie (tronquée à 100 caractères)
  - Badges
  - Nombre de séances

### Badges
- [ ] Vérifier les badges "Top coach" (or)
- [ ] Vérifier les badges "Répond rapidement" (vert)
- [ ] Vérifier les badges "Nouveau" (bleu)

### Animations
- [ ] Hover sur une carte de coach
  - Résultat attendu : Élévation de la carte
  - Résultat attendu : Bordure orange
  - Résultat attendu : Transition fluide
- [ ] Hover sur un bouton
  - Résultat attendu : Changement de couleur
  - Résultat attendu : Élévation légère
- [ ] Apparition des cartes
  - Résultat attendu : Fade-in progressif
  - Résultat attendu : Délai entre chaque carte

### Loading States
- [ ] Vérifier le spinner pendant le chargement initial
- [ ] Vérifier le spinner pendant la recherche
- [ ] Vérifier le spinner pendant l'envoi du formulaire

## 📱 Tests Responsive

### Mobile (< 768px)
- [ ] Ouvrir sur mobile ou réduire la fenêtre
  - Résultat attendu : 1 colonne de cartes
  - Résultat attendu : Filtres accessibles
  - Résultat attendu : Boutons de tri empilés
  - Résultat attendu : Modal adapté à l'écran

### Tablette (768px - 1024px)
- [ ] Ouvrir sur tablette ou fenêtre moyenne
  - Résultat attendu : 2 colonnes de cartes
  - Résultat attendu : Sidebar visible
  - Résultat attendu : Boutons de tri sur une ligne

### Desktop (> 1024px)
- [ ] Ouvrir sur desktop
  - Résultat attendu : 3 colonnes de cartes
  - Résultat attendu : Sidebar sticky
  - Résultat attendu : Tous les éléments bien espacés

## 🔒 Tests de Sécurité

### Authentification
- [ ] Se déconnecter
- [ ] Essayer d'accéder à `/coaches/enhanced`
  - Résultat attendu : Redirection vers login ou message d'erreur
- [ ] Essayer d'envoyer une demande sans être connecté
  - Résultat attendu : Erreur 401 "Vous devez être connecté"

### Validation Serveur
- [ ] Envoyer une demande avec un coach inexistant (via console)
  - Résultat attendu : Erreur 404 "Coach introuvable"
- [ ] Envoyer une demande avec un message trop court
  - Résultat attendu : Erreur 400 avec message explicite
- [ ] Envoyer une demande avec un message trop long
  - Résultat attendu : Erreur 400 avec message explicite

### Protection CSRF
- [ ] Vérifier que le token CSRF est présent (si implémenté)
- [ ] Essayer d'envoyer sans token (via console)
  - Résultat attendu : Erreur 403

## ⚡ Tests de Performance

### Temps de Réponse
- [ ] Mesurer le temps de recherche
  - Objectif : < 300ms
- [ ] Mesurer le temps de filtrage
  - Objectif : < 200ms
- [ ] Mesurer le temps de tri
  - Objectif : < 100ms
- [ ] Mesurer le temps de validation
  - Objectif : Instantané

### Optimisations
- [ ] Vérifier que le debounce fonctionne (pas de requête à chaque lettre)
- [ ] Vérifier que les animations sont fluides (60fps)
- [ ] Vérifier qu'il n'y a pas de memory leaks (console)

## 🐛 Tests de Régression

### Fonctionnalités Existantes
- [ ] Vérifier que l'ancienne page `/coaches` fonctionne toujours
- [ ] Vérifier que les demandes s'enregistrent en BDD
- [ ] Vérifier que les coaches reçoivent les demandes
- [ ] Vérifier que les notifications fonctionnent (si implémentées)

## ✅ Validation Finale

### Checklist Globale
- [ ] Tous les tests passent
- [ ] Aucune erreur dans la console
- [ ] Aucune erreur dans les logs Symfony
- [ ] Performance acceptable
- [ ] Design cohérent
- [ ] Responsive fonctionnel
- [ ] Sécurité validée

### Documentation
- [ ] README mis à jour
- [ ] Guide utilisateur créé
- [ ] Guide technique créé
- [ ] Changelog mis à jour

---

## 📊 Résultats des Tests

**Date** : _______________  
**Testeur** : _______________  
**Environnement** : _______________

**Tests Réussis** : _____ / _____  
**Tests Échoués** : _____ / _____  
**Bugs Trouvés** : _____

**Commentaires** :
_______________________________________
_______________________________________
_______________________________________

**Statut Final** : ☐ Validé  ☐ À corriger  ☐ Bloquant
