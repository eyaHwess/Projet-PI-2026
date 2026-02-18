# 🚨 Système de Priorité des Demandes de Coaching

## ✅ Fonctionnalités Implémentées

### 1. Deux Niveaux de Priorité

#### 🟢 Standard (par défaut)
- Réponse attendue sous 48 heures
- Traitement normal
- Badge vert menthe
- Icône check-circle

#### 🔴 Urgente
- Réponse attendue sous 24 heures
- Traitement prioritaire
- Badge rouge avec gradient
- Icône exclamation-circle
- Animation pulse
- Ombre portée rouge

### 2. Modification de l'Entité CoachingRequest

Ajout du champ `priority` :
```php
#[ORM\Column(length: 20)]
#[Assert\Choice(
    choices: [self::PRIORITY_STANDARD, self::PRIORITY_URGENT],
    message: "La priorité de la demande est invalide."
)]
private string $priority = self::PRIORITY_STANDARD;
```

Constantes :
- `PRIORITY_STANDARD = 'standard'`
- `PRIORITY_URGENT = 'urgent'`

Méthode helper :
```php
public function isUrgent(): bool
{
    return $this->priority === self::PRIORITY_URGENT;
}
```

### 3. Formulaire avec Boutons Radio Stylisés

Le champ `priority` dans le formulaire :
- Type : `ChoiceType` avec `expanded: true`
- Affichage : Boutons radio personnalisés
- Options :
  - 🟢 Standard (réponse sous 48h)
  - 🔴 Urgente (réponse sous 24h)
- Valeur par défaut : Standard

### 4. Design des Boutons Radio

#### État Normal
- Bordure bleu pastel (3px)
- Fond blanc
- Padding généreux (1rem 1.5rem)
- Coins arrondis (1rem)
- Texte centré et en gras

#### État Sélectionné
- Bordure bleu pastel plus foncée
- Fond bleu très clair
- Scale(1.05) pour effet de zoom
- Ombre portée bleue
- Transition fluide (0.3s)

#### État Hover
- Bordure bleu clair
- TranslateY(-2px) pour effet d'élévation

### 5. Tri Automatique des Demandes

Les demandes sont triées dans cet ordre :
1. **Priorité** : Urgentes en premier (DESC)
2. **Date** : Plus récentes en premier (DESC)

Méthodes modifiées dans `CoachingRequestRepository` :
- `findPendingForCoach()` : Demandes en attente pour un coach
- `findAllForCoach()` : Toutes les demandes pour un coach

```php
->orderBy('cr.priority', 'DESC') // urgent avant standard
->addOrderBy('cr.createdAt', 'DESC')
```

### 6. Affichage des Badges de Priorité

#### Badge Urgent
- Gradient rouge (#FF6B6B → #FF8E8E)
- Texte blanc
- Animation pulse (2s infinite)
- Ombre portée rouge
- Icône exclamation-circle-fill
- Texte "URGENT" en majuscules

#### Badge Standard
- Fond vert menthe pastel
- Texte vert foncé (#4A7C59)
- Icône check-circle
- Texte "Standard"

### 7. Affichage dans "Mes Demandes"

Chaque demande affiche maintenant :
- Nom du coach
- Statut (En attente / Acceptée / Refusée)
- **Badge de priorité** (Urgent ou Standard)
- Date de création
- Message

Les badges sont alignés horizontalement avec flexbox et flex-wrap pour le responsive.

## 🎨 Design

### Boutons Radio de Priorité
```css
.priority-radio-group {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}

.priority-radio-group .form-check-input:checked + .form-check-label {
    border-color: var(--pastel-primary);
    background: var(--pastel-light);
    transform: scale(1.05);
    box-shadow: 0 4px 12px rgba(168, 216, 234, 0.3);
}
```

### Badge Urgent
```css
.urgent-badge {
    background: linear-gradient(135deg, #FF6B6B, #FF8E8E);
    color: white;
    animation: pulse 2s infinite;
    box-shadow: 0 2px 8px rgba(255, 107, 107, 0.3);
}
```

### Badge Standard
```css
.standard-badge {
    background: var(--pastel-success);
    color: #4A7C59;
}
```

## 🔄 Workflow Utilisateur

### Création d'une Demande

1. **Remplissage du formulaire** : L'utilisateur remplit les champs
2. **Choix de la priorité** : Sélectionne Standard ou Urgente
3. **Indication visuelle** : Le bouton sélectionné s'agrandit et change de couleur
4. **Information** : Texte d'aide explique la différence
5. **Envoi** : La demande est créée avec la priorité choisie

### Côté Coach

1. **Réception** : Le coach reçoit les demandes
2. **Tri automatique** : Les demandes urgentes apparaissent en premier
3. **Badge visible** : Badge rouge "URGENT" attire l'attention
4. **Traitement prioritaire** : Le coach traite les urgentes en premier

### Côté Utilisateur (Mes Demandes)

1. **Consultation** : L'utilisateur voit ses demandes
2. **Badge de priorité** : Chaque demande affiche son badge
3. **Statut** : Badge de statut (En attente / Acceptée / Refusée)
4. **Suivi** : L'utilisateur peut suivre l'évolution

## 📊 Exemples d'Utilisation

### Cas d'Usage : Demande Standard
**Situation** : Utilisateur veut commencer un programme dans 2 semaines
- Sélectionne "🟢 Standard"
- Réponse attendue sous 48h
- Pas de pression sur le coach

### Cas d'Usage : Demande Urgente
**Situation** : Utilisateur a un événement important dans 3 jours
- Sélectionne "🔴 Urgente"
- Réponse attendue sous 24h
- Badge rouge attire l'attention du coach
- Demande apparaît en haut de la liste

## 🔧 Migration Base de Données

La migration ajoute le champ `priority` avec valeur par défaut :

```sql
ALTER TABLE coaching_request 
ADD priority VARCHAR(20) DEFAULT 'standard' NOT NULL;

UPDATE coaching_request 
SET priority = 'standard' 
WHERE priority IS NULL;
```

Toutes les demandes existantes sont automatiquement définies comme "Standard".

## 📱 Responsive

### Desktop
- Boutons radio côte à côte
- Badges alignés horizontalement
- Espacement généreux

### Tablet
- Boutons radio peuvent passer sur 2 lignes
- Badges restent alignés
- Flex-wrap activé

### Mobile
- Boutons radio empilés verticalement
- Badges empilés si nécessaire
- Min-width: 200px par bouton

## 🚀 Performance

### Optimisations
- Index sur le champ `priority` pour tri rapide
- Requêtes optimisées avec ORDER BY
- Pas de requêtes supplémentaires

### Indexation Recommandée
```sql
CREATE INDEX idx_priority_created 
ON coaching_request(priority DESC, created_at DESC);
```

## 💡 Avantages

1. **Meilleure réactivité** : Les coaches voient les urgences en premier
2. **Satisfaction client** : Réponse rapide pour les cas urgents
3. **Organisation** : Tri automatique des demandes
4. **Visibilité** : Badge rouge attire l'attention
5. **Flexibilité** : L'utilisateur choisit selon ses besoins

## 🎯 Métriques de Succès

- **Taux de demandes urgentes** : % de demandes marquées urgentes
- **Temps de réponse urgent** : Moyenne pour les demandes urgentes
- **Temps de réponse standard** : Moyenne pour les demandes standard
- **Satisfaction** : Note des utilisateurs ayant utilisé "Urgent"

## 🔮 Améliorations Futures Possibles

1. **Notification Push** : Alerte instantanée pour demandes urgentes
2. **Tarification différenciée** : Supplément pour demandes urgentes
3. **Statistiques coach** : Taux de réponse aux urgences
4. **Badge "Réactif"** : Pour coaches répondant vite aux urgences
5. **Filtres** : Filtrer par priorité dans l'interface coach
6. **Historique** : Graphique des demandes urgentes vs standard
7. **Auto-escalade** : Demande standard devient urgente après 48h
8. **Quota** : Limite de demandes urgentes par utilisateur/mois

## 📝 Notes Techniques

- **Symfony 6+** compatible
- **Doctrine ORM** pour la persistance
- **Validation** avec Assert\Choice
- **CSS personnalisé** pour les boutons radio
- **Animation CSS** pour le badge urgent
- **Tri SQL** optimisé

## 🔒 Validation

Le champ `priority` est validé avec :
```php
#[Assert\Choice(
    choices: [self::PRIORITY_STANDARD, self::PRIORITY_URGENT],
    message: "La priorité de la demande est invalide."
)]
```

Seules les valeurs 'standard' et 'urgent' sont acceptées.

## 🎨 Palette de Couleurs

- **Standard** : Vert menthe (#B5EAD7) / Texte vert foncé (#4A7C59)
- **Urgent** : Rouge gradient (#FF6B6B → #FF8E8E) / Texte blanc
- **Sélection** : Bleu pastel (var(--pastel-primary))

## 🚀 Pour Tester

1. Accédez à `/coaches`
2. Remplissez le formulaire de demande
3. Sélectionnez "🔴 Urgente"
4. Envoyez la demande
5. Consultez "Mes demandes de coaching"
6. Vérifiez le badge rouge "URGENT"
7. Côté coach : Les demandes urgentes apparaissent en premier
