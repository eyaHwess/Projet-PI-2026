# DayFlow — Personal Development & Coaching Platform

## Overview

DayFlow is a web application that helps users set goals, build routines, track progress, and engage with the community through posts and chatrooms. It also includes a coaching workflow (requests, sessions) and a reclamation system.

This project was developed as part of the **PIDEV** program at **Esprit School of Engineering** (Academic Year **2025–2026**).

## Features

- **User management**: registration/login, profiles, role-based access (User/Coach/Admin)
- **Goals & routines**: create goals, attach routines, progress tracking, community goals
- **Chatrooms**: goal-based chatrooms and private chatrooms
- **Posts module**: create/publish posts, rich-text editor, tags, likes, comments, scheduled posts
- **Content moderation**: toxicity analysis for posts/comments (Perspective API workflow)
- **Real-time updates**: Mercure-based notifications (social interactions + moderation alerts)
- **Coaching**: coaching requests, coach session management
- **Reclamations**: submit and manage reclamations, admin responses
- **Admin dashboard**: platform statistics and administrative management screens

## Tech Stack

### Frontend

- **Twig** templates
- **Bootstrap 5** + custom CSS
- **JavaScript (ES6)** / **Fetch API**
- **Symfony UX (Stimulus)** and **Importmap / Asset Mapper**
- **CKEditor 5** (rich text editor)
- **FullCalendar** (calendar UI)
- **ApexCharts** (dashboard charts)

### Backend

- **PHP** (Symfony framework)
- **Symfony** (Controllers, Security, Services, Console commands)
- **Doctrine ORM** + **Doctrine Migrations**
- **PostgreSQL** database
- **Mercure** (real-time events)
- **Monolog** (logging)
- **HTTP Client** (external API calls such as Perspective API)

## Architecture

The application follows a modular Symfony architecture (Controller → Service → Repository/Doctrine), with feature-based modules:

- **User**: authentication, profiles, roles
- **Coaching**: coaching requests, sessions, notifications
- **Posts**: posts, comments, likes, tagging, moderation checks
- **Goals/Routines**: goal lifecycle, routines, community features
- **Chatroom**: goal chatrooms and private chatrooms with messaging
- **Reclamation**: reclamation submission and admin processing
- **Admin**: dashboards, moderation logs, and administration tools

## Contributors

- Eya Hwess
- Eya Abdellaoui
- Roua Taboubi
- Shaima Barouni
- Ranym Zaghbib
- Mariem Ayari

## Academic Context

This project was developed at **Esprit School of Engineering – Tunisia** as part of the **PIDEV – 3rd Year Engineering Program (Academic Year 2025–2026)**.

## Getting Started

### Prerequisites

- **PHP** (8.1+ recommended)
- **Composer**
- **PostgreSQL**
- (Optional) **Symfony CLI** (`symfony`) for running the local server

### Installation

1) Clone the repository and enter the Symfony app directory:

```bash
git clone <repo-url>
cd PI2026/PI_dev
```

2) Install PHP dependencies:

```bash
composer install
```

3) Configure environment variables:

- Create a local environment file:

```bash
copy .env .env.local
```

- Update at least:
  - `DATABASE_URL` (PostgreSQL connection)
  - `PERSPECTIVE_API_KEY` (for moderation)
  - `MERCURE_URL` and `MERCURE_PUBLIC_URL` (for real-time notifications)

4) Create and migrate the database:

```bash
php -d memory_limit=1G bin/console doctrine:database:create
php -d memory_limit=1G bin/console doctrine:migrations:migrate --no-interaction
```

5) Install frontend vendor assets (Importmap):

```bash
php bin/console importmap:install
```

### Running the application

Run the Symfony development server:

```bash
symfony server:start
```

Or using PHP’s built-in server:

```bash
php -S 127.0.0.1:8000 -t public
```

Then open the app at `http://127.0.0.1:8000`.

### (Optional) Create an admin account

```bash
php bin/console app:create-admin
```

## Acknowledgments

- **Esprit School of Engineering (Tunisia)**
- The supervising professors and academic staff who guided the **PIDEV** program and project evaluation.

"# Projet-PI-2026" 
