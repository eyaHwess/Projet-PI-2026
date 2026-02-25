# Organisation des navbars – DayFlow

Le projet utilise **deux navbars** uniquement : une pour la page d’accueil (avant inscription), une globale pour toute l’application (coach et utilisateur), avec des onglets qui s’affichent ou disparaissent selon le rôle.

---

## 1. Navbar Homepage (avant inscription)

| Élément | Détail |
|--------|--------|
| **Fichier** | `templates/homepage/components/navbar.html.twig` |
| **Utilisation** | Page d’accueil (landing) uniquement : `homepage/index.html.twig` (route `app_home`). Cette page **n’étend pas** `base.html.twig`. |
| **Contenu** | Logo **DayFlow**, liens : Accueil, Posts, Fonctionnalités, Motivation. Si **non connecté** : bouton « Commencer » (inscription). Si **connecté** : bouton « Mon espace » (vers tableau de bord). |
| **Rôle** | Présentation du produit pour les visiteurs ; après inscription/connexion, l’utilisateur passe à l’app et voit la navbar globale. |

---

## 2. Navbar globale app (après inscription – coach et user)

| Élément | Détail |
|--------|--------|
| **Fichier** | `templates/base.html.twig` (intégrée dans la base) |
| **Utilisation** | Toutes les pages qui étendent `base.html.twig` : login, register, dashboard, objectifs, calendrier, favoris, coaching, sessions, etc. **Une seule barre** pour toute l’app. |
| **Contenu** | Logo **DayFlow**, puis selon état et rôle : |

### Non connecté (sur une page qui utilise base)
- Accueil, Connexion, Inscription

### Connecté – onglets communs (tous les rôles)
- Accueil, Objectifs, Calendrier, Favoris, Posts

### Connecté – onglets selon le rôle
- **Coach** (`ROLE_COACH`) : Mes demandes, Gérer sessions  
- **Utilisateur** (sans `ROLE_COACH`) : Demander coaching, Mes sessions  

### Connecté – commun à la fin
- Notifications  
- **Admin** (si `ROLE_ADMIN`)  
- Avatar / profil  
- Déconnexion  

Aucun doublon de navbar dans le body des templates : les pages étendent `base.html.twig` et n’ajoutent pas de deuxième `<nav>`.

---

## Règle à suivre

- **Page d’accueil (landing)**  
  → Utiliser `homepage/index.html.twig` **sans** étendre `base.html.twig`, et inclure `{% include 'homepage/components/navbar.html.twig' %}`. Navbar = DayFlow, bouton Commencer ou Mon espace.

- **Toutes les autres pages app (objectifs, calendrier, favoris, coaching, sessions, login, register, dashboard, etc.)**  
  → Étendre **`base.html.twig`** et **ne pas** ajouter de navbar dans `{% block body %}`. La navbar de la base est la seule ; les onglets Coach / User / Admin apparaissent ou disparaissent selon le rôle.

- **Admin**  
  → Les pages admin peuvent conserver leur propre layout (`admin/base_admin.html.twig` avec top bar + sidebar) ; pour l’app « coach + user », seules les deux navbars ci‑dessus comptent.

---

## Résumé

| Navbar | Fichier | Où | Rôle |
|--------|---------|-----|------|
| **Homepage** | `homepage/components/navbar.html.twig` | Page d’accueil (/) | Logo DayFlow, Commencer / Mon espace |
| **App globale** | `base.html.twig` | Toutes les autres pages app | Une barre, onglets selon rôle (coach / user / admin) |

En gardant ces deux navbars, on évite les doublons et on maintient une seule barre globale pour coach et user, avec des onglets qui s’affichent ou disparaissent selon le rôle.
