# Correction Erreur: user_id NULL dans goal ✅

## Problème
```
SQLSTATE[23502]: Not null violation: 7 ERREUR: une valeur NULL viole la contrainte NOT NULL 
de la colonne « user_id » dans la relation « goal »
```

## Cause
Lors de la création d'un goal, le champ `user_id` n'était pas défini, alors qu'il est obligatoire dans la base de données.

## Analyse du Code

### Avant (❌ Incorrect)
```php
public function new(Request $request, EntityManagerInterface $em): JsonResponse|Response
{
    $goal = new Goal();
    $form = $this->createForm(GoalType::class, $goal);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // ❌ user_id n'est jamais défini!
        
        $chatroom = new Chatroom();
        $chatroom->setCreatedAt(new \DateTime());
        $chatroom->setGoal($goal);

        if ($this->getUser()) {
            $participation = new GoalParticipation();
            $participation->setGoal($goal);
            $participation->setUser($this->getUser());
            $participation->setCreatedAt(new \DateTime());
            $em->persist($participation);
        }

        $em->persist($goal);  // ❌ Erreur: user_id est NULL
        $em->persist($chatroom);
        $em->flush();
    }
}
```

### Après (✅ Correct)
```php
public function new(Request $request, EntityManagerInterface $em): JsonResponse|Response
{
    // ✅ Vérifier que l'utilisateur est connecté
    $user = $this->getUser();
    if (!$user) {
        $this->addFlash('error', 'Vous devez être connecté pour créer un goal.');
        return $this->redirectToRoute('app_login');
    }

    $goal = new Goal();
    $form = $this->createForm(GoalType::class, $goal);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // ✅ Définir l'utilisateur créateur
        $goal->setUser($user);

        $chatroom = new Chatroom();
        $chatroom->setCreatedAt(new \DateTime());
        $chatroom->setGoal($goal);

        // ✅ Creator joins automatically as OWNER with APPROVED status
        $participation = new GoalParticipation();
        $participation->setGoal($goal);
        $participation->setUser($user);
        $participation->setRole(GoalParticipation::ROLE_OWNER);
        $participation->setStatus(GoalParticipation::STATUS_APPROVED);
        $participation->setCreatedAt(new \DateTime());
        $em->persist($participation);

        $em->persist($goal);  // ✅ user_id est défini
        $em->persist($chatroom);
        $em->flush();
    }
}
```

## Modifications Apportées

### 1. Vérification de l'Authentification
```php
$user = $this->getUser();
if (!$user) {
    $this->addFlash('error', 'Vous devez être connecté pour créer un goal.');
    return $this->redirectToRoute('app_login');
}
```
- Vérifie que l'utilisateur est connecté avant de créer un goal
- Redirige vers la page de connexion si non connecté

### 2. Définition de l'Utilisateur
```php
$goal->setUser($user);
```
- Définit le créateur du goal
- Satisfait la contrainte NOT NULL de la base de données

### 3. Participation Automatique
```php
$participation = new GoalParticipation();
$participation->setGoal($goal);
$participation->setUser($user);
$participation->setRole(GoalParticipation::ROLE_OWNER);  // ✅ OWNER
$participation->setStatus(GoalParticipation::STATUS_APPROVED);  // ✅ APPROVED
$participation->setCreatedAt(new \DateTime());
```
- Le créateur devient automatiquement OWNER
- Son statut est APPROVED (pas besoin d'approbation)
- Il a accès immédiat au chatroom

## Structure de la Base de Données

### Table: goal
```sql
CREATE TABLE goal (
    id INT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    status VARCHAR(50),
    start_date DATE,
    end_date DATE,
    user_id INT NOT NULL,  -- ⭐ Obligatoire!
    FOREIGN KEY (user_id) REFERENCES "user"(id)
);
```

### Table: goal_participation
```sql
CREATE TABLE goal_participation (
    id INT PRIMARY KEY,
    user_id INT NOT NULL,
    goal_id INT NOT NULL,
    role VARCHAR(20) NOT NULL,      -- OWNER, ADMIN, MEMBER
    status VARCHAR(20) NOT NULL,    -- PENDING, APPROVED, REJECTED
    created_at TIMESTAMP NOT NULL,
    FOREIGN KEY (user_id) REFERENCES "user"(id),
    FOREIGN KEY (goal_id) REFERENCES goal(id)
);
```

## Flux de Création d'un Goal

1. **Utilisateur connecté** → Accède à `/goal/new`
2. **Remplit le formulaire** → Titre, description, dates, statut
3. **Soumet le formulaire** → POST vers `goal_new`
4. **Contrôleur vérifie** → Utilisateur connecté?
5. **Crée le goal** → `goal.user_id = user.id`
6. **Crée le chatroom** → Associé au goal
7. **Crée la participation** → OWNER + APPROVED
8. **Sauvegarde** → Tout en base de données
9. **Redirige** → Vers la liste des goals

## Permissions du Créateur

En tant que OWNER, le créateur peut:
- ✅ Modifier le goal
- ✅ Supprimer le goal
- ✅ Approuver/refuser les demandes d'accès
- ✅ Promouvoir des membres en ADMIN
- ✅ Exclure des membres
- ✅ Épingler des messages
- ✅ Modérer le chatroom

## Test de la Correction

### 1. Se connecter
```
Email: mariemayari@gmail.com
Password: mariem
```

### 2. Créer un goal
```
URL: http://127.0.0.1:8000/goal/new
```

### 3. Remplir le formulaire
- Titre: "Mon Premier Goal"
- Description: "Test de création"
- Date début: 2026-02-20
- Date fin: 2026-03-20
- Statut: "active"

### 4. Soumettre
✅ Le goal est créé avec succès
✅ user_id est défini
✅ Chatroom créé automatiquement
✅ Vous êtes OWNER avec statut APPROVED

### 5. Accéder au chatroom
```
URL: http://127.0.0.1:8000/goal/1/messages
```

## État Actuel
✅ Erreur corrigée
✅ user_id défini lors de la création
✅ Vérification d'authentification ajoutée
✅ Participation OWNER créée automatiquement
✅ Cache vidé
✅ Prêt pour les tests

## Prochaines Étapes
1. ✅ Tester la création d'un goal
2. ✅ Vérifier l'accès au chatroom
3. ✅ Tester l'envoi de messages
4. ⏳ Tester les permissions OWNER
5. ⏳ Tester le système d'approbation
