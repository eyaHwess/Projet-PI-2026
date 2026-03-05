# Projet PI 2026 — Application de coaching et objectifs

## Overview

Ce projet a été réalisé dans le cadre du **PIDEV** (Projet d’Ingénierie) — 3ᵉ année du cycle ingénieur à l’**Esprit School of Engineering** (année universitaire 2025-2026).

Il s’agit d’une application web full-stack permettant de gérer des objectifs personnels, des routines, des sessions de coaching, un chat en temps réel, des réclamations et des notifications, avec suivi de présence et traduction des messages.

## Features

- **Objectifs et routines** — Création et suivi d’objectifs, routines et activités
- **Coaching** — Demandes de coaching, sessions, créneaux, avis
- **Chat en temps réel** — Messagerie par objectif (polling / Mercure), réactions, pièces jointes, messages épinglés
- **Présence** — Statut en ligne, « en train d’écrire », messages lus
- **Traduction** — Traduction des messages (multi-langues)
- **Réclamations** — Dépôt et suivi des réclamations avec réponses
- **Notifications** — Notifications pour utilisateurs et coaches (NotificationBundle)
- **Posts et commentaires** — Fil d’actualité, likes, partages

## Tech Stack

### Frontend

- Twig (templates)
- JavaScript
- Stimulus, UX Turbo, UX Chart.js
- Tailwind CSS (via Asset Mapper)
- Mercure (optionnel, temps réel)

### Backend

- PHP 8.2+
- Symfony 7.4
- Doctrine ORM
- PostgreSQL (recommandé) / SQLite
- OAuth2 Google (connexion)
- Stripe (paiements)
- Symfony Mercure (temps réel)

## Architecture

- **Racine du dépôt** — Contient le dossier `PI_dev` (application Symfony).
- **PI_dev** — Application Symfony : contrôleurs, entités, services, templates, assets.
- **Modules métier** — User, Goals (objectifs / routines), Post, Chatroom, Coaching-Session, Reclamation.
- **Bundles internes** — GoalHistoryBundle, NotificationBundle. Pour l’API et l’utilisation des notifications : `PI_dev/src/NotificationBundle/README.md`.

## Contributors

(À compléter par l’équipe projet.)

## Academic Context

- **Établissement** — Esprit School of Engineering — Tunisie  
- **Parcours** — PIDEV — 3A | 2025-2026  

## Getting Started

1. **Cloner le dépôt et entrer dans l’application**

   ```bash
   cd PI_dev
   ```

2. **Installer les dépendances**

   ```bash
   composer install
   npm install
   ```

3. **Configurer l’environnement**

   - Copier `.env` vers `.env.local` et adapter (base de données, clés API, etc.).

4. **Base de données**

   ```bash
   php bin/console doctrine:database:create
   php bin/console doctrine:migrations:migrate
   ```

5. **Lancer l’application**

   ```bash
   symfony serve
   ```
   ou  
   ```bash
   php -S localhost:8000 -t public
   ```

6. **Accès**

   - Application : `http://localhost:8000`  
   - Connexion Google possible si OAuth2 est configuré.

## Tests

Exécuter depuis le dossier **PI_dev** :

- **Tests unitaires (entités)** — 2 entités par module (User, Goals, Post, Chatroom, Coaching-Session, Reclamation) :  
  `php bin/phpunit tests/Entity/`
- **Tests statiques (PHPStan)** — Par module : `composer phpstan:user`, `composer phpstan:goals`, `composer phpstan:post`, `composer phpstan:chatroom`, `composer phpstan:coaching-session`, `composer phpstan:reclamation` — ou tous : `composer phpstan:all` ; analyse globale : `composer phpstan`.

## Acknowledgments

- Esprit School of Engineering  
- Équipe pédagogique PIDEV  
- Documentation Symfony, Doctrine, Mercure  
