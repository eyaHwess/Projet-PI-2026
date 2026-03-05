# Doctrine Doctor – Étapes pas à pas

Guide pour mesurer les requêtes Doctrine et faire les captures pour le rapport.

---

## Étape 1 : Lancer l’application en mode dev

1. Ouvre un **terminal** dans le dossier du projet (ex. `PI_dev`).
2. Lance le serveur :
   - **Avec Symfony CLI** :  
     `symfony server:start`
   - **Sans Symfony CLI** :  
     `php -S localhost:8000 -t public`
3. Vérifie que tu es en **dev** : dans ton `.env` tu dois avoir `APP_ENV=dev` (et pas `prod`). La barre de debug n’apparaît qu’en dev.
4. Ouvre ton navigateur et va sur : **http://localhost:8000** (ou l’URL affichée dans le terminal, ex. http://127.0.0.1:8000).

---

## Étape 2 : Te connecter (si nécessaire)

- Si l’app demande une connexion pour accéder à `/goals` ou `/posts`, **connecte-toi** avec un compte utilisateur.
- Sans compte, crée-en un ou utilise des identifiants de test du projet.

---

## Étape 3 : Ouvrir la page à analyser

Dans la barre d’adresse du navigateur, va sur **une** de ces URLs (une page = une mesure) :

| Page à tester | URL à ouvrir |
|---------------|--------------|
| Liste des goals | **http://localhost:8000/goals** |
| Communauté (goals) | **http://localhost:8000/goals/community** |
| Liste des posts | **http://localhost:8000/posts** |

Pour l’instant, choisis **une** page (par ex. `/goals`), on fera les autres après.

---

## Étape 4 : Trouver la barre de debug Symfony

1. Une fois la page chargée, **tout en bas** de la fenêtre du navigateur tu dois voir une **barre noire** (ou colorée) avec des icônes et des chiffres.
2. C’est la **Web Debug Toolbar** de Symfony.  
   - Si tu ne la vois pas : vérifie que `APP_ENV=dev` et que le Profiler est activé (`config/packages/web_profiler.yaml` avec `toolbar: true` en dev).
3. Dans cette barre, repère l’icône / le bloc **« Doctrine »** (souvent avec un nombre à côté, ex. « 12 » = 12 requêtes).  
   - Parfois c’est écrit « Doctrine » ou affiché comme une base de données.

---

## Étape 5 : Ouvrir le panneau Doctrine

1. **Clique** sur la partie **Doctrine** de la barre de debug (le nombre de requêtes ou le libellé « Doctrine »).
2. Une **nouvelle page** s’ouvre (ou un panneau dans la même page) : c’est le **Profiler** pour cette requête HTTP.
3. Tu vois alors :
   - en haut ou en résumé : le **nombre total de requêtes** (ex. « 12 queries »),
   - une **liste des requêtes SQL** (SELECT, etc.) exécutées pour afficher la page.

C’est ce panneau que tu utiliseras pour **noter le nombre de requêtes** et pour **faire ta capture d’écran**.

---

## Étape 6 : Noter le nombre de requêtes

1. Dans le panneau Doctrine, note **le nombre total de requêtes** (affiché en général en haut ou à côté de « Doctrine »).  
   Exemple : **12 requêtes**.
2. Regarde la **liste des requêtes** :
   - Si tu vois **1 requête** qui charge une liste (ex. les goals) puis **plusieurs requêtes presque identiques** (ex. une par goal pour charger les participations), c’est un **problème N+1**.
   - Exemple : 1 requête « SELECT * FROM goal … » puis 5 requêtes « SELECT * FROM goal_participation WHERE goal_id = ? » → tu comptes **1 problème N+1** (liste des goals + chargement des participations).
3. Note sur une feuille ou dans un fichier :
   - **Page** : ex. `/goals`
   - **Nombre de requêtes** : ex. 12
   - **Nombre de problèmes N+1** : ex. 1 (liste goals + participations)

---

## Étape 7 : Faire la capture du panneau Doctrine

1. Reste sur la **page du Profiler** où tu vois la liste des requêtes Doctrine.
2. Fais une **capture d’écran** de tout le panneau (ou au moins la partie qui montre le nombre de requêtes et le début de la liste des requêtes).
   - **Windows** : `Win + Shift + S` (outil capture) ou « Outil de capture ».
   - **Mac** : `Cmd + Shift + 4` (sélection) ou `Cmd + Shift + 5`.
3. Enregistre la capture avec un nom clair, ex. :  
   `doctrine-avant-goals.png`  
   Tu l’utiliseras comme **preuve « Avant optimisation »** dans ton tableau.

---

## Étape 8 : Répéter pour les autres pages (optionnel)

Pour avoir plusieurs mesures « avant » :

1. Retourne sur **http://localhost:8000** (barre d’adresse).
2. Ouvre **http://localhost:8000/goals/community** → refais les **étapes 4 à 7** (barre debug → Doctrine → noter le nombre de requêtes → capture). Nomme la capture ex. `doctrine-avant-community.png`.
3. Ouvre **http://localhost:8000/posts** → refais les **étapes 4 à 7**. Nomme la capture ex. `doctrine-avant-posts.png`.

Tu auras ainsi **avant optimisation** : nombre de requêtes + nombre de problèmes N+1 + captures pour chaque page.

---

## Étape 9 : Remplir le tableau du rapport

Dans ton tableau « 3- Problèmes détectés (DoctrineDoctor) » :

- **Avant optimisation**  
  - Colonne « Nombre de problèmes N+1 » : le nombre que tu as noté (ex. 1 ou 2).  
  - Colonne « Preuves » : insère la (ou les) capture(s) du panneau Doctrine (ex. `doctrine-avant-goals.png`).
- **Les problèmes**  
  - Décris brièvement : ex. « Liste /goals : 1 requête pour les goals + N requêtes pour les participations (N+1). »

Après avoir appliqué les optimisations (jointures, etc.), tu refais les **mêmes étapes** sur les **mêmes URLs** et tu remplis les colonnes **« Après optimisation »** et tu ajoutes les nouvelles captures.

---

## Résumé rapide

| # | Action |
|---|--------|
| 1 | Terminal → `symfony server:start` ou `php -S localhost:8000 -t public` |
| 2 | Navigateur → http://localhost:8000 (se connecter si besoin) |
| 3 | Aller sur http://localhost:8000/goals (ou /goals/community ou /posts) |
| 4 | En bas de page : repérer la **barre de debug** (Doctrine) |
| 5 | **Cliquer** sur Doctrine → s’ouvre le **panneau des requêtes** |
| 6 | Noter le **nombre de requêtes** et compter les **N+1** (1 + N requêtes similaires) |
| 7 | **Capture d’écran** du panneau Doctrine → sauvegarder (ex. doctrine-avant-goals.png) |
| 8 | Remplir le tableau du rapport avec ces chiffres et cette capture |

Si une étape ne marche pas (pas de barre, pas de Doctrine), dis-moi à quelle étape tu bloques et ce que tu vois à l’écran.
