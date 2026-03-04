# Instructions de Test - Modal d'Ajout de Membre

## ✅ Changements Appliqués

1. ✅ `ChatroomController` utilise maintenant `chatroom_modern.html.twig`
2. ✅ Variable `userParticipation` passée au template
3. ✅ Cache Symfony vidé
4. ✅ Syntaxe validée (0 erreurs)

## 🧪 Test Étape par Étape

### Étape 1: Accéder à un Chatroom
1. Connectez-vous à l'application
2. Allez sur un goal dont vous êtes membre
3. Cliquez sur le chatroom du goal
4. URL devrait être: `http://localhost/chatroom/[ID]`

### Étape 2: Vérifier le Bouton
Dans le header du chat (en haut), vous devriez voir:
- Un bouton rond avec l'icône d'une personne avec un "+" (fa-user-plus)
- Il est à côté des boutons de recherche et menu (...)

### Étape 3: Ouvrir le Modal
1. Cliquez sur le bouton avec l'icône fa-user-plus
2. Un modal devrait s'ouvrir au centre de l'écran

### Étape 4: Tester la Recherche
1. Dans le champ "Rechercher un utilisateur", tapez au moins 2 caractères
2. Attendez 300ms
3. Une liste d'utilisateurs devrait apparaître
4. Chaque utilisateur a un bouton "Ajouter"

### Étape 5: Ajouter un Membre
1. Cliquez sur "Ajouter" à côté d'un utilisateur
2. Le bouton devrait afficher "Ajout..." avec un spinner
3. Un message de succès devrait apparaître
4. La page se recharge automatiquement
5. Le nouveau membre apparaît dans la sidebar "Membres"

### Étape 6: Tester le Lien d'Invitation
1. Rouvrez le modal
2. Scrollez vers le bas jusqu'à "OU"
3. Cliquez sur "Copier" dans la section lien d'invitation
4. Le bouton devrait devenir vert avec "Copié !"
5. Collez le lien dans un nouvel onglet → devrait aller sur la page du goal

## 🐛 Si le Modal Ne S'Ouvre Pas

### Diagnostic Rapide (Console du Navigateur - F12)

**Test 1: Le modal existe-t-il ?**
```javascript
console.log(document.getElementById('addMemberModal'));
```
- Si `null` → Le template n'est pas le bon
- Si retourne un élément → ✅ Modal existe

**Test 2: La fonction existe-t-elle ?**
```javascript
console.log(typeof openAddMemberModal);
```
- Si `"undefined"` → JavaScript non chargé
- Si `"function"` → ✅ Fonction existe

**Test 3: Forcer l'ouverture**
```javascript
openAddMemberModal();
```
- Si le modal s'ouvre → Le problème vient du bouton
- Si rien ne se passe → Problème avec le JavaScript

### Solutions Possibles

**Problème: Template non chargé**
```bash
# Vider le cache à nouveau
php bin/console cache:clear

# Vérifier les routes
php bin/console debug:router chatroom_show
```

**Problème: JavaScript non chargé**
- Vérifiez la console pour des erreurs JavaScript
- Vérifiez que le fichier `public/js/translation.js` existe
- Rechargez la page avec Ctrl+F5 (force refresh)

**Problème: Bouton non visible**
- Le bouton est peut-être masqué par CSS
- Inspectez l'élément pour voir s'il existe dans le DOM
- Vérifiez qu'il n'y a pas de `display: none` sur le bouton

## 📸 Ce Que Vous Devriez Voir

### Header du Chat
```
[Avatar] Nom du Goal                    [🔍] [👤+] [⋮]
        X membres, Y online
```

### Modal Ouvert
```
┌─────────────────────────────────────────┐
│  👤+ Ajouter un membre              [×] │
├─────────────────────────────────────────┤
│                                         │
│  🔍 Rechercher un utilisateur           │
│  [Nom, prénom ou email...        ]     │
│                                         │
│  [Avatar] Jean Dupont                   │
│           jean@example.com    [Ajouter] │
│                                         │
│  ─────────────── OU ───────────────     │
│                                         │
│  Partagez ce lien d'invitation :        │
│  [http://localhost/goal/1    ] [Copier] │
│                                         │
│  ℹ️ Les personnes qui cliquent sur ce   │
│     lien pourront demander à rejoindre  │
│                                         │
└─────────────────────────────────────────┘
```

## ✅ Résultat Attendu

Après avoir ajouté un membre:
1. Message de succès: "✅ [Nom] a été ajouté au chatroom !"
2. Page se recharge
3. Dans la sidebar "Membres", le nouveau membre apparaît avec:
   - Son avatar (ou initiales)
   - Son nom
   - Son rôle: "MEMBER"

## 📞 Si Ça Ne Marche Toujours Pas

Envoyez-moi:
1. L'URL exacte où vous êtes
2. Capture d'écran du header du chat
3. Résultat des 3 tests de diagnostic (console)
4. Capture d'écran de la console avec erreurs éventuelles
