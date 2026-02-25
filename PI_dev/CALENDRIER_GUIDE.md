# 📅 Guide du Calendrier de Planification

## Comment accéder au calendrier ?

Le calendrier de planification est accessible de plusieurs façons :

### 1. Depuis la page d'accueil (Homepage)
- Cliquez sur le lien **"📅 Calendrier"** dans la barre de navigation en haut
- Disponible sur desktop et mobile (menu hamburger)

### 2. Depuis la page "Mes Objectifs"
- **Dans la navigation** : Cliquez sur **"📅 Calendrier"** dans la barre de navigation
- **Bouton rapide** : Cliquez sur le bouton **"📅 Calendrier"** à côté de "Nouvel Objectif" dans la section hero

### 3. Depuis la page des Routines
- Cliquez sur le lien **"📅 Calendrier"** dans la barre de navigation en haut

### 4. Depuis la page des Activités
- Cliquez sur le lien **"📅 Calendrier"** dans la barre de navigation en haut

### 5. URL directe
- Accédez directement à : `http://votre-domaine/calendar`

## Fonctionnalités du Calendrier

### Visualisation des événements
- **🟣 Violet** : Deadlines des objectifs
- **🔵 Bleu** : Deadlines des routines
- **🟠 Orange** : Heures de début des activités
- **🔴 Rouge** : Deadlines des activités

### Vues disponibles
- **Vue Mois** : Vue d'ensemble mensuelle (par défaut)
- **Vue Semaine** : Détails hebdomadaires
- **Vue Jour** : Planning détaillé de la journée

### Interactions
- **Cliquer sur un événement** : Affiche une modal avec les détails
  - Titre
  - Type (Objectif/Routine/Activité)
  - Description
  - Date
  - Priorité
  - Statut
  - Bouton "Voir les détails" pour accéder à l'élément

### Navigation
- **Boutons Précédent/Suivant** : Naviguer entre les périodes
- **Bouton "Aujourd'hui"** : Retour à la date actuelle
- **Sélecteur de vue** : Basculer entre Mois/Semaine/Jour

## Légende des couleurs

En haut du calendrier, vous trouverez une légende qui explique les couleurs :
- 🟣 **Objectifs** : Événements violets
- 🔵 **Routines** : Événements bleus
- 🟠 **Activités** : Événements orange/rouge

## Remarques importantes

1. **Seuls les éléments avec une deadline ou une date de début sont affichés**
2. **Le calendrier est en français** (jours, mois, boutons)
3. **Le calendrier est responsive** et fonctionne sur mobile
4. **Les événements sont cliquables** pour voir plus de détails
5. **Le calendrier se met à jour automatiquement** quand vous ajoutez/modifiez des deadlines

## Problèmes courants

### Le lien n'apparaît pas
- Assurez-vous d'être sur une page avec la navigation (pas sur une page de connexion)
- Sur mobile, ouvrez le menu hamburger (☰) pour voir le lien
- Rafraîchissez la page (Ctrl+F5 ou Cmd+Shift+R)

### Le calendrier est vide
- Vérifiez que vous avez créé des objectifs/routines/activités avec des deadlines
- Les éléments sans deadline ne s'affichent pas dans le calendrier

### Les événements ne s'affichent pas
- Vérifiez que les deadlines sont dans le futur (ou aujourd'hui)
- Assurez-vous que les objectifs/routines/activités sont bien enregistrés dans la base de données

## Support

Si vous rencontrez des problèmes, vérifiez :
1. Que vous êtes connecté
2. Que vous avez des données avec des deadlines
3. Que votre navigateur supporte JavaScript
4. Que vous avez une connexion internet (pour charger FullCalendar)
