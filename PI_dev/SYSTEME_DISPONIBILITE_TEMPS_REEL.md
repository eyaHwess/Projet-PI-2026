# 📅 Système de Disponibilité en Temps Réel

## ✅ Fonctionnalités Implémentées

### 1. Entité TimeSlot (Créneau Horaire)

Structure complète pour gérer les créneaux :
```php
- coach: User (le coach propriétaire)
- startTime: DateTimeImmutable (début du créneau)
- endTime: DateTimeImmutable (fin du créneau)
- isAvailable: boolean (disponible/réservé)
- bookedBy: User (utilisateur ayant réservé)
- coachingRequest: CoachingRequest (demande liée)
- createdAt: DateTimeImmutable
```

Méthodes utiles :
- `getDuration()` : Durée en secondes
- `getDurationInMinutes()` : Durée en minutes
- `book(User, CoachingRequest)` : Réserver le créneau
- `cancel()` : Annuler la réservation

### 2. TimeSlotRepository

Méthodes de requête optimisées :

**findAvailableForCoach(User $coach, DateTimeImmutable $start, DateTimeImmutable $end)**
- Récupère les créneaux disponibles d'un coach pour une période
- Filtre par `isAvailable = true`
- Tri par date croissante

**hasAvailableToday(User $coach): bool**
- Vérifie si le coach a des créneaux disponibles aujourd'hui
- Utilisé pour le badge "Disponible aujourd'hui"

**countAvailableForCoach(User $coach): int**
- Compte le nombre total de créneaux disponibles futurs
- Utilisé pour afficher "X créneaux disponibles"

**findBookedByUser(User $user)**
- Récupère les créneaux réservés par un utilisateur
- Utilisé pour l'historique des réservations

### 3. Calendrier Interactif (FullCalendar)

#### Bibliothèque
- FullCalendar v6.1.10
- CDN : `https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/`
- Locale française intégrée

#### Vues Disponibles
- **timeGridWeek** : Vue semaine avec heures (par défaut)
- **timeGridDay** : Vue journée détaillée
- Navigation : Précédent, Suivant, Aujourd'hui

#### Configuration
```javascript
{
    initialView: 'timeGridWeek',
    locale: 'fr',
    slotMinTime: '08:00:00',
    slotMaxTime: '21:00:00',
    allDaySlot: false,
    height: 'auto'
}
```

#### Événements
- **Clic sur un créneau** : Sélectionne le créneau
- **Clic sur une date** : Affiche les créneaux du jour
- **Couleurs** :
  - Vert (#10B981) : Disponible
  - Rouge (#EF4444) : Réservé

### 4. Modal de Sélection de Créneaux

#### Structure
- **Gauche (8 colonnes)** : Calendrier FullCalendar
- **Droite (4 colonnes)** : Liste des créneaux + Confirmation

#### Liste des Créneaux
- Affichage par date sélectionnée
- Format : "HH:MM - HH:MM (durée en minutes)"
- Carte cliquable avec effet hover
- Icône check-circle quand sélectionné
- Scroll si nombreux créneaux

#### Confirmation
- Affichage du créneau sélectionné
- Date complète en français
- Heure de début et fin
- Durée en minutes
- Bouton "Confirmer et envoyer la demande"

### 5. Badges sur les Cartes Coach

#### Badge "Disponible aujourd'hui"
- Gradient vert (#10B981 → #34D399)
- Animation pulse
- Icône calendar-check
- Affiché si `hasAvailableToday() === true`

#### Badge "Complet"
- Fond gris (#F3F4F6)
- Texte gris (#6B7280)
- Icône calendar-x
- Affiché si aucun créneau disponible

### 6. Boutons d'Action

#### "Voir disponibilités"
- Bouton principal (btn-outline-primary)
- Ouvre le modal calendrier
- Charge les créneaux du coach

#### "Demande sans créneau"
- Bouton secondaire (btn-outline-secondary btn-sm)
- Permet une demande classique sans réservation
- Scroll vers le formulaire

### 7. API REST

#### GET /api/timeslots/coach/{id}
Récupère les créneaux d'un coach

**Paramètres optionnels** :
- `start` : Date de début (format ISO)
- `end` : Date de fin (format ISO)
- Par défaut : 14 prochains jours

**Réponse** :
```json
{
  "success": true,
  "timeSlots": [
    {
      "id": 1,
      "start": "2026-02-16T09:00:00",
      "end": "2026-02-16T10:00:00",
      "title": "Disponible",
      "available": true,
      "duration": 60,
      "backgroundColor": "#10B981",
      "borderColor": "#059669"
    }
  ],
  "hasAvailableToday": true,
  "totalAvailable": 42
}
```

#### GET /api/timeslots/{id}
Récupère les détails d'un créneau spécifique

**Réponse** :
```json
{
  "success": true,
  "timeSlot": {
    "id": 1,
    "coachName": "Marie Dupont",
    "startTime": "2026-02-16 09:00",
    "endTime": "2026-02-16 10:00",
    "duration": 60,
    "available": true,
    "date": "16/02/2026",
    "time": "09:00 - 10:00"
  }
}
```

### 8. Commande de Génération

**php bin/console app:populate-timeslots**

Génère automatiquement des créneaux pour tous les coaches :
- Période : 14 prochains jours
- Créneaux matin : 9h-12h
- Créneaux après-midi : 14h-18h
- Créneaux soir : 18h-20h (33% de chance)
- Durée : 1 heure par créneau
- Disponibilité : 70% de chance d'être disponible

### 9. Intégration avec CoachingRequest

#### Champ timeSlot
Ajouté à l'entité CoachingRequest :
```php
#[ORM\ManyToOne(targetEntity: TimeSlot::class)]
#[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
private ?TimeSlot $timeSlot = null;
```

#### Workflow de Réservation
1. Utilisateur sélectionne un créneau dans le calendrier
2. Clic sur "Confirmer"
3. Modal se ferme
4. Formulaire pré-rempli avec le coach
5. Champ caché `timeSlotId` ajouté
6. Soumission du formulaire
7. Créneau réservé automatiquement
8. `isAvailable` passe à `false`
9. `bookedBy` et `coachingRequest` renseignés

## 🎨 Design

### Calendrier
- Largeur : 900px (modal-xl)
- Hauteur : Auto-ajustée
- Bordure : 2px solid bleu pastel
- Coins arrondis : 1.5rem

### Cartes de Créneaux
```css
.timeslot-card {
    border: 2px solid #D4EEF7;
    border-radius: 0.75rem;
    padding: 1rem;
    cursor: pointer;
    transition: all 0.3s;
}

.timeslot-card:hover {
    border-color: var(--pastel-primary);
    box-shadow: 0 4px 12px rgba(168, 216, 234, 0.3);
    transform: translateY(-2px);
}

.timeslot-card.selected {
    border-color: #10B981;
    background: #D1FAE5;
}
```

### Badge Disponible Aujourd'hui
```css
.available-today-badge {
    background: linear-gradient(135deg, #10B981, #34D399);
    color: white;
    animation: pulse 2s infinite;
    box-shadow: 0 2px 8px rgba(16, 185, 129, 0.3);
}
```

## 🔄 Workflow Utilisateur

### Scénario 1 : Réservation avec Créneau

1. **Navigation** : Utilisateur parcourt les coaches
2. **Badge** : Voit "Disponible aujourd'hui" sur un coach
3. **Clic** : Clique sur "Voir disponibilités"
4. **Modal** : Calendrier s'ouvre avec créneaux verts
5. **Sélection date** : Clique sur une date
6. **Liste** : Créneaux du jour s'affichent à droite
7. **Choix** : Clique sur un créneau (09:00-10:00)
8. **Confirmation** : Créneau sélectionné affiché en vert
9. **Validation** : Clique sur "Confirmer et envoyer"
10. **Formulaire** : Scroll vers formulaire pré-rempli
11. **Message** : Complète le message
12. **Envoi** : Soumet la demande
13. **Réservation** : Créneau réservé automatiquement
14. **Notification** : Coach notifié avec créneau

### Scénario 2 : Demande sans Créneau

1. **Navigation** : Utilisateur parcourt les coaches
2. **Clic** : Clique sur "Demande sans créneau"
3. **Scroll** : Formulaire affiché
4. **Remplissage** : Complète normalement
5. **Envoi** : Demande envoyée sans créneau spécifique

## 📊 Statistiques Affichées

### Sur les Cartes Coach
- Badge "Disponible aujourd'hui" (si créneaux aujourd'hui)
- Badge "Complet" (si aucun créneau disponible)

### Dans le Modal
- Nombre de créneaux disponibles par jour
- Total de créneaux disponibles
- Calendrier visuel avec couleurs

## 🚀 Performance

### Optimisations
- Chargement des créneaux à la demande (modal)
- Cache côté client (variable `allTimeSlots`)
- Requêtes filtrées par période (14 jours)
- Index sur `coach_id`, `start_time`, `is_available`

### Indexation Recommandée
```sql
CREATE INDEX idx_timeslot_coach_available 
ON time_slots(coach_id, is_available, start_time);

CREATE INDEX idx_timeslot_dates 
ON time_slots(start_time, end_time);
```

## 💡 Avantages

### Pour l'Utilisateur
1. **Visibilité** : Voit immédiatement les disponibilités
2. **Simplicité** : Réservation en quelques clics
3. **Clarté** : Calendrier visuel intuitif
4. **Flexibilité** : Peut choisir ou non un créneau
5. **Confirmation** : Sait exactement quand aura lieu la session

### Pour le Coach
1. **Organisation** : Gestion centralisée des créneaux
2. **Automatisation** : Réservations automatiques
3. **Visibilité** : Sait quand il est réservé
4. **Flexibilité** : Peut gérer ses disponibilités
5. **Efficacité** : Moins d'allers-retours

## 🔒 Sécurité

### Vérifications
- Créneau disponible avant réservation
- Appartenance du créneau au coach sélectionné
- Pas de double réservation
- Validation des dates

### Gestion des Conflits
- Vérification `isAvailable` avant réservation
- Transaction atomique (persist + flush)
- Rollback en cas d'erreur

## 📱 Responsive

### Desktop
- Modal large (900px)
- Calendrier et liste côte à côte
- Toutes les fonctionnalités

### Tablet
- Modal adapté
- Calendrier réduit
- Liste scrollable

### Mobile
- Modal plein écran
- Calendrier empilé au-dessus
- Liste en dessous
- Touch-friendly

## 🔮 Améliorations Futures

1. **Gestion Coach** : Interface pour créer/modifier créneaux
2. **Récurrence** : Créneaux récurrents (tous les lundis 9h)
3. **Durées variables** : 30min, 1h, 1h30, 2h
4. **Pause déjeuner** : Bloquer automatiquement 12h-14h
5. **Synchronisation** : Google Calendar, Outlook
6. **Rappels** : Email/SMS avant la session
7. **Annulation** : Permettre annuler jusqu'à 24h avant
8. **Liste d'attente** : Si créneau complet
9. **Tarifs variables** : Prix différent selon l'heure
10. **Statistiques** : Taux de remplissage, créneaux populaires

## 📝 Notes Techniques

- **Symfony 6+** compatible
- **Doctrine ORM** pour la persistance
- **FullCalendar 6.1.10** pour le calendrier
- **Bootstrap 5** pour le modal
- **JavaScript Vanilla** pour la logique
- **API REST** pour les données

## 🚀 Pour Tester

1. **Générer créneaux** : `php bin/console app:populate-timeslots`
2. **Accéder** : `/coaches`
3. **Cliquer** : "Voir disponibilités" sur un coach
4. **Explorer** : Calendrier et créneaux
5. **Sélectionner** : Un créneau disponible (vert)
6. **Confirmer** : Bouton "Confirmer et envoyer"
7. **Compléter** : Formulaire pré-rempli
8. **Envoyer** : Demande avec créneau réservé

## 🎯 Métriques de Succès

- **Taux de réservation** : % de demandes avec créneau
- **Taux de remplissage** : % de créneaux réservés
- **Créneaux populaires** : Heures les plus demandées
- **Temps de réservation** : Délai moyen entre vue et réservation
- **Satisfaction** : Feedback utilisateurs sur le système
