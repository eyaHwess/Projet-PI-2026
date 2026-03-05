# 3– Problèmes détectés (Doctrine / optimisation N+1)

Ce guide explique comment mesurer et documenter les problèmes N+1 avec Doctrine pour remplir le tableau de rapport (avant / après optimisation, preuves).

## Outils utilisés

- **Environnement** : Symfony en `APP_ENV=dev` (toolbar et profiler activés).
- **Doctrine** : le **Profiler Symfony** (panneau Doctrine) affiche pour chaque requête HTTP le **nombre de requêtes SQL** et leur détail. C’est l’outil de référence pour « Doctrine Doctor » (détection des N+1).

## Remplir le tableau

### Indicateur 1 : Nombre de problèmes N+1 détectés

| Indicateur | Avant optimisation (par défaut) | Après optimisation | Preuves (captures) |
|------------|---------------------------------|--------------------|---------------------|
| **Nombre de problèmes N+1 détectés (Doctrine)** | _À remplir_ | _À remplir_ | Capture du panneau Doctrine du Profiler (avant) | Capture du panneau Doctrine du Profiler (après) |

**Comment mesurer :**

1. **Avant optimisation**
   - Démarrer l’app : `symfony server:start` ou `php -S localhost:8000 -t public`.
   - Ouvrir une page qui charge des listes (ex. `/goals`, `/posts`, page communauté).
   - En bas de la page, cliquer sur la **barre de debug** (icône Symfony).
   - Onglet **Doctrine** : noter le **nombre de requêtes**.
   - Si vous voyez **1 requête pour la liste + N requêtes similaires** (une par élément), c’est un **N+1**. Noter combien de « problèmes » (ex. 1 problème = liste goals sans jointure → N+1 sur les participations).
   - Faire une **capture d’écran** du panneau Doctrine (liste des requêtes) → **Preuve « Avant »**.

2. **Après optimisation**
   - Appliquer les optimisations (jointures, `JOIN` + `addSelect`, ou requêtes dédiées avec `findGoalsWithParticipants()` etc.).
   - Recharger la même page.
   - Onglet **Doctrine** : noter le **nouveau nombre de requêtes** (souvent 1 ou 2 au lieu de 1+N).
   - Faire une **capture d’écran** du panneau Doctrine → **Preuve « Après »**.

### Indicateur 2 : Les problèmes

| Indicateur | Avant optimisation (par défaut) | Après optimisation | Preuves (captures) |
|------------|---------------------------------|--------------------|---------------------|
| **Les problèmes** | Ex. : « Liste des goals : 1 requête pour les goals + N requêtes pour charger les participations (getGoalParticipations) dans la vue. » | Ex. : « Utilisation de `findGoalsWithParticipants()` avec `leftJoin` + `addSelect` : 1 seule requête pour goals + participations. » | Même captures que ci‑dessus (liste des requêtes Doctrine) |

**Exemples de problèmes N+1 dans ce projet :**

- **Goals (liste)** : chargement des goals puis accès à `$goal->getGoalParticipations()` ou `$goal->getChatroom()` dans le template → N+1. **Solution** : utiliser un `QueryBuilder` avec `leftJoin('g.goalParticipations', 'gp')->addSelect('gp')` (ou la méthode `findGoalsWithParticipants()` sur la page communauté).
- **Posts / commentaires** : liste de posts puis `$post->getComments()` ou `$post->getCreatedBy()` pour chaque post → N+1. **Solution** : jointures ou requête avec `addSelect` sur les relations nécessaires.
- **Chatroom / messages** : liste de messages puis accès à l’auteur ou aux réactions → N+1. **Solution** : jointures dans la requête qui charge les messages.

## Commande fournie

Pour afficher un rappel de cette procédure en ligne de commande :

```bash
php bin/console app:doctrine:report-n-plus-one
```

## Résumé pour le rapport

- **Avant** : noter le nombre de requêtes (et le nombre de « problèmes » N+1 identifiés) + coller la capture du Profiler Doctrine.
- **Après** : noter le nouveau nombre de requêtes (et que les N+1 listés sont corrigés) + coller la capture du Profiler Doctrine.
- **Les problèmes** : décrire brièvement chaque N+1 (quelle entité, quelle relation) et la correction appliquée (quelle jointure / quelle méthode de repository).
