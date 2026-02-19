# Plan des fonctionnalités – Profils, annuaire, recherche, dashboard, notifications

Ce document décrit le plan d’implémentation pour les 6 blocs suivants :
1. **Profil coach** – Page et formulaire de mise à jour (bio, spécialité, disponibilité, note, etc.)
2. **Annuaire des coachs** – Liste des coachs avec filtres (spécialité, disponibilité, note)
3. **Recherche globale** – Barre de recherche (coachs, sessions, objectifs, routines)
4. **Profil utilisateur** – Voir et modifier ses infos et préférences
5. **Dashboard** – Amélioration avec statistiques et actions rapides
6. **Notifications** – Système de notifications (demande acceptée/refusée, rappel session, etc.)

---

## 1. Profil coach

### Objectif
Une page réservée aux coachs pour consulter et modifier leur profil public (bio, spécialité, disponibilité, note, etc.).

### Modifications techniques

| Élément | Action |
|--------|--------|
| **Entité `User`** | Ajouter un champ `bio` (TEXT, nullable) pour la description du coach. Optionnel : `hourlyRate`, `experienceYears`, `certifications` selon besoin métier. |
| **Migration** | Créer une migration Doctrine pour le nouveau champ `bio` (et autres si ajoutés). |
| **Formulaire** | Créer `CoachProfileType` avec : bio, speciality, availability, phoneNumber, (rating en lecture seule ou calculé). Ne pas exposer email/mot de passe ici. |
| **Contrôleur** | Créer ou étendre un `ProfileController` (ou `CoachProfileController`) avec : `show` (GET) et `edit` (GET/POST). Route du type `/coach/profile` et `/coach/profile/edit`. |
| **Sécurité** | `#[IsGranted('ROLE_COACH')]` sur les actions profil coach. Vérifier que l’utilisateur connecté est bien le coach dont on édite le profil. |
| **Templates** | `coach_profile/show.html.twig` (affichage), `coach_profile/edit.html.twig` (formulaire). Style cohérent avec le reste (ex. Bootstrap / thème actuel). |
| **Validation** | Contraintes côté entité et formulaire (longueur bio, format téléphone, etc.). |

### Liens dans l’app
- Depuis la page « Mes demandes » (coach) : lien « Mon profil » vers `/coach/profile`.
- Après login coach : option « Mon profil » dans le menu ou le dashboard coach.

---

## 2. Annuaire des coachs

### Objectif
Une page dédiée listant tous les coachs avec filtres (spécialité, disponibilité, note) pour que les utilisateurs trouvent facilement un coach.

### Modifications techniques

| Élément | Action |
|--------|--------|
| **Repository `UserRepository`** | Méthodes existantes à réutiliser : `findCoaches()`, `findCoachesBySpeciality()`, `findAllCoachSpecialities()`. Ajouter si besoin : `findCoachesFiltered(speciality?, availability?, minRating?)` avec QueryBuilder. |
| **Contrôleur** | Utiliser ou adapter `CoachController::index` (route `/coaches`). S’assurer que la liste est bien « annuaire » (tous les coachs) avec paramètres de filtre : `?speciality=...&availability=...&min_rating=...`. |
| **Template** | Une vue « annuaire » dédiée (ex. `coach/directory.html.twig` ou renommer/clarifier `coach/index.html.twig`) : cartes coach (photo/avatar, nom, bio courte, spécialité, disponibilité, note). Filtres en haut (select spécialité, select disponibilité, note min). |
| **Données** | Passer : `coaches`, `specialities`, `selectedSpeciality`, `selectedAvailability`, `selectedMinRating`. Si `availability` est une chaîne libre, proposer des valeurs prédéfinies (ex. « Week-end », « Soir », « Matin ») ou liste issue des données. |

### Comportement
- **Filtre spécialité** : déjà en place sur `/coaches` ; le garder et l’enrichir.
- **Filtre disponibilité** : ajout d’un paramètre et filtre en BDD si le champ est structuré, sinon filtre côté PHP ou normaliser le champ.
- **Filtre note** : `WHERE rating >= :minRating` (et `rating IS NOT NULL`).
- L’annuaire peut être la même page que « Choisir un coach » actuelle, avec une section « Faire une demande » en dessous ou sur une page détail coach.

---

## 3. Recherche globale

### Objectif
Une barre de recherche unique permettant de chercher des coachs, des sessions, des objectifs (goals) et des routines.

### Modifications techniques

| Élément | Action |
|--------|--------|
| **Route** | Une route dédiée, ex. `GET /search?q=...` (et éventuellement `&type=coach|session|goal|routine` pour filtrer le type). |
| **Contrôleur** | Créer `SearchController` avec une action `search(Request $request)` : lire `q` (et `type`). Si `q` vide, rediriger vers la page d’accueil ou afficher une page « Recherche » vide. |
| **Recherche** | Pour chaque type (coach, session, goal, routine) : requêtes dédiées (Repository ou QueryBuilder) avec `LIKE %q%` sur les champs pertinents. Ex. : User (coach) : firstName, lastName, speciality, bio ; Session : id ou lien vers coachingRequest ; Goal : title, description ; Routine : title, description. Limiter le nombre de résultats par type (ex. 5–10). |
| **Sécurité** | Coachs : tout le monde peut voir la liste publique. Sessions / objectifs / routines : ne retourner que ceux liés à l’utilisateur connecté (sessions où il est user ou coach, goals/routines de l’user). |
| **Template** | `search/results.html.twig` : afficher les résultats groupés par type (Coachs, Sessions, Objectifs, Routines) avec liens vers les pages correspondantes. |
| **Barre de recherche** | Inclure un formulaire dans la navbar (base ou layout commun) : champ texte + bouton « Rechercher », action vers `path('app_search')` en GET. Option : recherche AJAX avec affichage en dropdown (phase 2). |

### Champs à interroger (suggestions)
- **Coachs** : `firstName`, `lastName`, `speciality`, `bio`.
- **Sessions** : par `coachingRequest.user` / `coachingRequest.coach` + id session (ou libellé dérivé).
- **Objectifs** : `title`, `description` (et filtrer par `user`).
- **Routines** : `title`, `description` (et filtrer par utilisateur via la relation avec Goal/User).

---

## 4. Profil utilisateur

### Objectif
Une page où l’utilisateur (non coach) peut voir et modifier ses informations personnelles et préférences.

### Modifications techniques

| Élément | Action |
|--------|--------|
| **Entité `User`** | Déjà : firstName, lastName, email, phoneNumber, age. Optionnel : préférences (notifications par email, unité préférée, etc.) en JSON ou champs dédiés. |
| **Formulaire** | Créer `UserProfileType` : firstName, lastName, phoneNumber, age (email en lecture seule ou modification avec vérification). Ne pas inclure le mot de passe (changement de mot de passe séparé). |
| **Contrôleur** | Créer ou étendre `ProfileController` avec routes `/user/profile` (show) et `/user/profile/edit` (GET/POST). `#[IsGranted('ROLE_USER')]` et vérifier que l’utilisateur modifie son propre profil. |
| **Templates** | `user_profile/show.html.twig`, `user_profile/edit.html.twig`. Afficher les infos et un bouton « Modifier le profil ». |
| **Validation** | Contraintes sur les champs (longueurs, email, âge positif, etc.). |

### Liens
- Depuis le dashboard utilisateur : lien « Mon profil ».
- Dans le menu / navbar : « Mon compte » ou « Profil » pour les utilisateurs connectés.

---

## 5. Dashboard amélioré

### Objectif
Enrichir le dashboard (utilisateur et éventuellement coach) avec des statistiques claires et des actions rapides.

### Modifications techniques

| Élément | Action |
|--------|--------|
| **Dashboard utilisateur** (`user_dashboard` / `UserDashboardController`) | En plus des stats existantes (nombre d’objectifs, routines) : nombre de demandes de coaching (en attente, acceptées, refusées), prochaines sessions. Requêtes via `CoachingRequestRepository`, `SessionRepository`. Passer ces variables au template. |
| **Template dashboard user** | `user/dashuser.html.twig` : ajouter des blocs « Résumé demandes », « Prochaines sessions », « Derniers objectifs » déjà présents. Ajouter des boutons/liens rapides : « Créer un objectif », « Voir mes sessions », « Trouver un coach ». |
| **Dashboard coach** | Si pas déjà en place : une vue dédiée (ex. après login coach) avec : nombre de demandes en attente, nombre de sessions à venir, lien « Mes demandes », « Gérer mes sessions », « Mon profil ». Peut réutiliser la page « Mes demandes » comme page d’accueil coach avec un encart stats en haut. |
| **Design** | Cartes (cards) pour chaque bloc de stats, icônes, couleurs cohérentes avec le thème (ex. orange). |

---

## 6. Notifications

### Objectif
Système de notifications pour informer l’utilisateur des événements importants : demande acceptée/refusée, rappel de session, etc.

### Modifications techniques

| Élément | Action |
|--------|--------|
| **Entité `Notification`** | Créer une entité : `id`, `user` (ManyToOne vers User), `title`, `message` (ou `content`), `type` (ex. `coaching_request_accepted`, `coaching_request_declined`, `session_reminder`, `session_scheduled`), `readAt` (datetime nullable), `createdAt`, `link` (nullable, URL ou route pour redirection). |
| **Migration** | Générer et exécuter la migration pour la table `notification`. |
| **Repository** | `NotificationRepository` : `findUnreadByUser(User $user)`, `findRecentByUser(User $user, int $limit)`, `markAsRead(Notification $n)` ou `markAllAsRead(User $user)`. |
| **Création des notifications** | Lors des actions existantes : quand un coach accepte une demande → créer une notification pour le `user` de la demande ; quand il refuse → idem. Optionnel : quand une session est planifiée/confirmée → notification aux deux (user + coach). Rappels : cron ou commande Symfony (ex. « sessions dans les 24 h ») qui crée des notifications. |
| **Contrôleur** | `NotificationController` : `index` (liste des notifications), `markAsRead(Notification $id)` (POST, retour JSON si AJAX), `markAllAsRead` (POST). Routes du type `/notifications`, `/notifications/{id}/read`. |
| **Template** | Liste des notifications (page ou sidebar). Dropdown dans la navbar : icône cloche, nombre de non lues, liste des dernières notifications avec lien. |
| **Layout** | Inclure dans la base ou le layout commun un composant « cloche » avec compteur et dropdown (ou lien vers la page notifications). |
| **AJAX** | Optionnel : marquer comme lu en AJAX sans recharger la page ; chargement des dernières notifications en AJAX pour le dropdown. |

### Types de notifications suggérés
- `coaching_request_accepted` – « Votre demande de coaching a été acceptée par [Coach]. »
- `coaching_request_declined` – « [Coach] a décliné votre demande. »
- `session_scheduled` – « Une session a été planifiée pour le [date]. »
- `session_reminder` – « Rappel : session avec [Coach/User] demain à [heure]. »

---

## Ordre d’implémentation suggéré

1. **Profil utilisateur** (show + edit) – rapide, réutilise User sans nouvelle entité.
2. **Profil coach** – ajout du champ `bio`, formulaire et pages profil coach.
3. **Annuaire des coachs** – filtres et page dédiée (ou amélioration de la page actuelle).
4. **Recherche globale** – SearchController, repository methods, barre dans la navbar, page résultats.
5. **Dashboard amélioré** – stats et raccourcis sur le dashboard existant (et coach si besoin).
6. **Notifications** – entité, création aux bons endroits, contrôleur, affichage (navbar + page).

---

## Fichiers à créer ou modifier (résumé)

| Fichier | Action |
|---------|--------|
| `src/Entity/User.php` | Ajouter `bio` (et optionnellement champs préférences). |
| `src/Entity/Notification.php` | Créer. |
| `src/Repository/NotificationRepository.php` | Créer. |
| `src/Form/CoachProfileType.php` | Créer. |
| `src/Form/UserProfileType.php` | Créer. |
| `src/Controller/ProfileController.php` ou `CoachProfileController` + `UserProfileController` | Créer / étendre. |
| `src/Controller/SearchController.php` | Créer. |
| `src/Controller/NotificationController.php` | Créer. |
| `UserRepository` | Ajouter méthode(s) de recherche filtrée pour l’annuaire. |
| `CoachController` | Adapter pour annuaire + filtres (disponibilité, note). |
| `UserDashboardController` | Enrichir avec stats demandes/sessions et liens. |
| `CoachingRequestController` (accept/decline) | Après accept/decline, créer une `Notification` pour le user. |
| `templates/coach_profile/`, `user_profile/`, `search/`, `notification/` | Créer les vues. |
| `templates/base.html.twig` ou layout commun | Ajouter barre de recherche et icône notifications. |
| Migrations Doctrine | Pour `User.bio` et table `notification`. |

---

## Sécurité et cohérence

- Toutes les routes profil (user et coach) doivent vérifier que l’utilisateur connecté modifie ou consulte son propre profil (ou rôle coach pour le profil coach).
- La recherche ne doit retourner que les données auxquelles l’utilisateur a droit (ses objectifs, routines, sessions ; tous les coachs en lecture seule).
- Les notifications : uniquement celles de l’utilisateur connecté ; marquer comme lu uniquement pour le propriétaire de la notification.

Ce plan peut être suivi étape par étape ; chaque bloc est réalisable indépendamment en respectant les points de sécurité ci-dessus.
