# 🔔 Système de Notifications & Statut En Ligne

## ✅ Fonctionnalités Implémentées

### 1. Système de Notifications Complet

#### Types de Notifications
- **new_request** : Nouvelle demande de coaching (pour le coach)
- **new_request_urgent** : Nouvelle demande URGENTE (pour le coach)
- **request_sent** : Confirmation d'envoi de demande (pour l'utilisateur)
- **request_accepted** : Demande acceptée par le coach
- **request_declined** : Demande refusée par le coach
- **request_pending** : Demande mise en attente

#### Entité Notification
```php
- user: User (destinataire)
- type: string (type de notification)
- message: string (contenu)
- coachingRequest: CoachingRequest (lien optionnel)
- isRead: boolean (lu/non lu)
- createdAt: DateTimeImmutable
```

### 2. Badge de Notifications dans la Navbar

#### Affichage
- Icône cloche (bi-bell)
- Badge rouge avec compteur
- Compteur caché si 0 notification
- Format "99+" si plus de 99 notifications

#### Dropdown
- Largeur : 320px
- Max-height : 400px avec scroll
- Affichage des dernières notifications non lues
- Icônes colorées selon le type
- Temps relatif ("Il y a 5 min")
- Bouton "Tout marquer lu"
- Lien "Voir toutes les notifications"

### 3. NotificationService

#### Méthodes Disponibles

**notifyCoachNewRequest(CoachingRequest $request)**
- Notifie le coach d'une nouvelle demande
- Détecte si la demande est urgente
- Message adapté avec emoji 🔴 si urgent

**notifyUserRequestSent(CoachingRequest $request)**
- Confirme à l'utilisateur que sa demande est envoyée
- Indique le nom du coach contacté

**notifyRequestAccepted(CoachingRequest $request)**
- Notifie l'utilisateur que sa demande est acceptée
- Message positif avec le nom du coach

**notifyRequestDeclined(CoachingRequest $request)**
- Notifie l'utilisateur que sa demande est refusée
- Message encourageant à contacter un autre coach

**notifyRequestPending(CoachingRequest $request)**
- Notifie l'utilisateur que sa demande est en attente
- Rassure sur le suivi du coach

**createNotification(User $user, string $type, string $message, ?CoachingRequest $request)**
- Méthode générique pour créer n'importe quelle notification

### 4. NotificationController

#### Routes API

**GET /notifications/unread-count**
- Retourne le nombre de notifications non lues
- Format JSON : `{"count": 5}`
- Utilisé pour le badge

**GET /notifications/unread**
- Retourne les notifications non lues
- Format JSON avec tableau de notifications
- Utilisé pour le dropdown

**POST /notifications/{id}/mark-read**
- Marque une notification comme lue
- Vérifie que la notification appartient à l'utilisateur

**POST /notifications/mark-all-read**
- Marque toutes les notifications comme lues
- Utilisé par le bouton "Tout marquer lu"

**GET /notifications**
- Page complète avec toutes les notifications
- Template dédié

### 5. Système de Statut En Ligne

#### Champ lastActivityAt
Ajouté à l'entité User :
```php
#[ORM\Column(type: 'datetime_immutable', nullable: true)]
private ?\DateTimeImmutable $lastActivityAt = null;
```

#### Méthodes User

**updateLastActivity()**
- Met à jour lastActivityAt avec l'heure actuelle
- Appelée automatiquement à chaque requête

**isOnline(): bool**
- Retourne true si activité < 5 minutes
- Retourne false sinon

**getOnlineStatus(): string**
- Retourne 'online' si activité < 5 minutes
- Retourne 'away' si activité < 1 heure
- Retourne 'offline' sinon

#### UserActivityListener
Event listener qui :
- Écoute chaque requête HTTP (KernelEvents::REQUEST)
- Vérifie si un utilisateur est connecté
- Met à jour automatiquement son lastActivityAt
- Flush la base de données

### 6. Affichage du Statut sur les Cartes Coach

#### Badge de Statut
Chaque carte de coach affiche :
- **En ligne** : Badge vert avec point animé
- **Absent** : Badge jaune avec point animé
- **Hors ligne** : Badge gris avec point animé

#### Design
```css
.online-status.online {
    background: #D1FAE5;
    color: #065F46;
}

.online-status.away {
    background: #FEF3C7;
    color: #92400E;
}

.online-status.offline {
    background: #F3F4F6;
    color: #6B7280;
}
```

#### Animation
Point de statut avec animation pulse :
```css
@keyframes pulse-dot {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}
```

## 🎨 Design

### Badge de Notifications
- Position : Absolute top-right de l'icône
- Taille : 20px × 20px
- Fond : Rouge (#ef4444)
- Bordure : 2px solid white
- Police : 11px, bold

### Dropdown de Notifications
- Ombre : 0 10px 25px rgba(0, 0, 0, 0.1)
- Coins arrondis : 0.5rem
- Bordure : 1px solid #e5e7eb
- Z-index : 1000

### Items de Notification
- Padding : 1rem
- Bordure bottom : 1px solid #f3f4f6
- Hover : Background #f9fafb
- Non lu : Background #eff6ff (bleu clair)

### Icônes de Notification
Cercles colorés selon le type :
- **Acceptée** : Vert (#10B981)
- **Refusée** : Rouge (#EF4444)
- **En attente** : Jaune (#F59E0B)
- **Nouvelle** : Bleu (#3B82F6)

## 🔄 Workflow

### Création d'une Demande

1. **Utilisateur** remplit le formulaire
2. **Soumission** : Demande créée en base
3. **Notification coach** : Le coach reçoit une notification
4. **Notification user** : L'utilisateur reçoit une confirmation
5. **Badge** : Le compteur du coach s'incrémente
6. **Dropdown** : La notification apparaît dans le dropdown

### Réponse du Coach

1. **Coach** accepte/refuse la demande
2. **Notification user** : L'utilisateur reçoit la réponse
3. **Badge** : Le compteur de l'utilisateur s'incrémente
4. **Email** (optionnel) : Email de notification

### Consultation des Notifications

1. **Clic sur la cloche** : Dropdown s'ouvre
2. **Chargement** : Fetch des notifications non lues
3. **Affichage** : Liste avec icônes et temps relatif
4. **Clic sur une notification** : Marquée comme lue
5. **Mise à jour** : Compteur décrémenté

### Statut En Ligne

1. **Connexion** : lastActivityAt initialisé
2. **Navigation** : lastActivityAt mis à jour à chaque page
3. **Calcul** : Statut calculé en temps réel
4. **Affichage** : Badge coloré sur la carte coach
5. **Déconnexion** : Statut devient "Hors ligne" après 5 min

## 📊 Temps de Statut

| Statut | Condition | Couleur | Icône |
|--------|-----------|---------|-------|
| En ligne | < 5 minutes | Vert | Point vert animé |
| Absent | 5 min - 1 heure | Jaune | Point jaune animé |
| Hors ligne | > 1 heure | Gris | Point gris animé |

## 🚀 Performance

### Optimisations
- **Compteur** : Rafraîchi toutes les 30 secondes
- **Dropdown** : Chargé uniquement à l'ouverture
- **Requêtes** : Limitées aux notifications non lues
- **Index** : Sur user_id et isRead pour requêtes rapides

### Indexation Recommandée
```sql
CREATE INDEX idx_notifications_user_read 
ON notifications(user_id, is_read, created_at DESC);

CREATE INDEX idx_user_last_activity 
ON user(last_activity_at);
```

## 💡 Avantages

### Pour l'Utilisateur
1. **Visibilité** : Sait immédiatement quand le coach répond
2. **Réactivité** : Peut voir si le coach est en ligne
3. **Transparence** : Historique complet des notifications
4. **Confort** : Pas besoin de rafraîchir la page

### Pour le Coach
1. **Alertes** : Notifié des nouvelles demandes
2. **Urgences** : Demandes urgentes bien visibles
3. **Organisation** : Toutes les notifications centralisées
4. **Disponibilité** : Statut visible par les clients

## 🔒 Sécurité

### Vérifications
- Authentification requise pour toutes les routes
- Vérification de propriété des notifications
- Protection CSRF sur les actions POST
- Validation des données entrantes

### Privacy
- Statut en ligne basé sur l'activité réelle
- Pas de tracking invasif
- Données de notification privées

## 📱 Responsive

### Desktop
- Dropdown aligné à droite
- Largeur fixe 320px
- Scroll si > 400px

### Tablet
- Dropdown adapté
- Icônes visibles
- Touch-friendly

### Mobile
- Dropdown pleine largeur
- Padding réduit
- Scroll optimisé

## 🎯 Métriques de Succès

- **Taux d'ouverture** : % de notifications ouvertes
- **Temps de réponse** : Délai entre notification et action
- **Engagement** : Nombre de clics sur les notifications
- **Satisfaction** : Feedback utilisateurs sur le système

## 🔮 Améliorations Futures

1. **Push Notifications** : Notifications navigateur
2. **Email Notifications** : Envoi d'emails pour événements importants
3. **SMS** : Pour demandes urgentes
4. **Groupement** : Regrouper notifications similaires
5. **Filtres** : Filtrer par type de notification
6. **Préférences** : Choisir quelles notifications recevoir
7. **Sons** : Son lors de nouvelle notification
8. **Desktop Notifications** : Notifications système
9. **Historique** : Archive des anciennes notifications
10. **Statistiques** : Dashboard des notifications

## 📝 Notes Techniques

- **Symfony 6+** compatible
- **Doctrine ORM** pour la persistance
- **Event Listener** pour l'activité utilisateur
- **JavaScript Vanilla** pour le frontend
- **Fetch API** pour les requêtes AJAX
- **Auto-refresh** toutes les 30 secondes

## 🚀 Pour Tester

1. **Créer une demande** : Allez sur `/coaches` et envoyez une demande
2. **Vérifier le badge** : Le coach voit le compteur s'incrémenter
3. **Ouvrir le dropdown** : Cliquez sur la cloche
4. **Voir la notification** : La nouvelle demande apparaît
5. **Marquer comme lu** : Cliquez sur la notification
6. **Vérifier le statut** : Le badge du coach indique "En ligne"
7. **Attendre 5 min** : Le statut passe à "Absent" puis "Hors ligne"

## 🎨 Palette de Couleurs

- **En ligne** : Vert (#10B981) / Fond (#D1FAE5)
- **Absent** : Jaune (#F59E0B) / Fond (#FEF3C7)
- **Hors ligne** : Gris (#9CA3AF) / Fond (#F3F4F6)
- **Badge notification** : Rouge (#EF4444)
- **Non lu** : Bleu clair (#EFF6FF)
