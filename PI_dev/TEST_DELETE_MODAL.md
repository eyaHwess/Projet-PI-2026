# 🧪 Test - Modal de Suppression de Message

## 🚀 Comment Tester

### Préparation
1. Ouvrez le chatroom
2. Assurez-vous d'avoir au moins un message que vous avez envoyé

### Test 1: Ouvrir la Modal ✅
**Actions**:
1. Passez la souris sur un de vos messages
2. Cliquez sur le bouton 🗑️ (trash) qui apparaît

**Résultat attendu**:
- ✅ Une modal s'ouvre au centre de l'écran
- ✅ Titre: "Pour qui voulez-vous retirer ce message ?"
- ✅ Deux options sont visibles
- ✅ "Retirer pour tout le monde" est sélectionné (radio button bleu)
- ✅ Overlay sombre derrière la modal

### Test 2: Changer d'Option ✅
**Actions**:
1. Cliquez sur "Retirer pour vous"

**Résultat attendu**:
- ✅ Le radio button de "Retirer pour vous" devient bleu
- ✅ Le radio button de "Retirer pour tout le monde" devient gris
- ✅ L'option sélectionnée a un fond bleu clair
- ✅ Transition fluide

### Test 3: Supprimer pour Tout le Monde ✅
**Actions**:
1. Sélectionnez "Retirer pour tout le monde"
2. Cliquez sur le bouton "Supprimer" (rouge)

**Résultat attendu**:
- ✅ Le message disparaît avec une animation (fade out + slide left)
- ✅ La modal se ferme
- ✅ Le message n'est plus visible dans le chat
- ✅ Le message est supprimé de la base de données

### Test 4: Supprimer pour Vous ✅
**Actions**:
1. Cliquez sur 🗑️ sur un autre message
2. Sélectionnez "Retirer pour vous"
3. Cliquez sur "Supprimer"

**Résultat attendu**:
- ✅ Le message disparaît avec animation
- ✅ La modal se ferme
- ✅ Le message n'est plus visible pour vous
- ✅ Les autres utilisateurs peuvent toujours le voir

### Test 5: Annuler ✅
**Actions**:
1. Cliquez sur 🗑️
2. Cliquez sur le bouton "Annuler" (gris)

**Résultat attendu**:
- ✅ La modal se ferme
- ✅ Le message reste visible
- ✅ Aucune suppression n'a lieu

### Test 6: Fermer avec X ✅
**Actions**:
1. Cliquez sur 🗑️
2. Cliquez sur le X en haut à droite de la modal

**Résultat attendu**:
- ✅ La modal se ferme
- ✅ Le message reste visible

### Test 7: Fermer avec Escape ✅
**Actions**:
1. Cliquez sur 🗑️
2. Appuyez sur la touche **Escape**

**Résultat attendu**:
- ✅ La modal se ferme
- ✅ Le message reste visible

### Test 8: Fermer en Cliquant à l'Extérieur ✅
**Actions**:
1. Cliquez sur 🗑️
2. Cliquez sur l'overlay sombre (en dehors de la modal)

**Résultat attendu**:
- ✅ La modal se ferme
- ✅ Le message reste visible

## 🎨 Apparence Visuelle

### Modal Fermée
```
Message avec bouton trash visible au hover
```

### Modal Ouverte
```
┌─────────────────────────────────────────────────┐
│  Pour qui voulez-vous retirer ce message ?  ❌  │
├─────────────────────────────────────────────────┤
│                                                 │
│  ⦿ Retirer pour tout le monde                  │
│    Ce message sera retiré pour tous les        │
│    participants à la discussion. Il est        │
│    possible que certains l'aient déjà vu...    │
│                                                 │
│  ○ Retirer pour vous                           │
│    Cette action supprimera le message de       │
│    vos appareils. Les autres membres...        │
│                                                 │
├─────────────────────────────────────────────────┤
│                      [Annuler]  [Supprimer]    │
└─────────────────────────────────────────────────┘
```

### Option Sélectionnée
```
⦿ = Radio button bleu rempli
○ = Radio button gris vide
Fond bleu clair sur l'option sélectionnée
```

## 🔍 Vérifications Console

Ouvrez la console (F12) et vérifiez:

### Lors de l'ouverture
```javascript
// Aucune erreur ne devrait apparaître
```

### Lors de la suppression
```javascript
// Requête AJAX vers /message/{id}/delete ou /message/{id}/delete-for-me
// Réponse: {success: true, message: "..."}
```

### En cas d'erreur
```javascript
// Message d'erreur clair dans la console
// Alert avec le message d'erreur
```

## ❌ Problèmes Possibles

### La modal ne s'ouvre pas
**Causes**:
- Erreur JavaScript
- Bouton trash non cliquable

**Solution**:
1. Vérifier la console pour erreurs
2. Rafraîchir la page (Ctrl+F5)

### Le message ne se supprime pas
**Causes**:
- Erreur serveur
- Problème de permissions

**Solution**:
1. Vérifier la console Network
2. Vérifier que vous êtes l'auteur du message
3. Vérifier les logs Symfony

### La modal ne se ferme pas
**Causes**:
- Erreur JavaScript

**Solution**:
1. Rafraîchir la page
2. Vérifier la console pour erreurs

## 📊 Checklist de Test

| Test | Description | Status |
|------|-------------|--------|
| 1 | Ouvrir la modal | ⬜ |
| 2 | Changer d'option | ⬜ |
| 3 | Supprimer pour tout le monde | ⬜ |
| 4 | Supprimer pour vous | ⬜ |
| 5 | Annuler | ⬜ |
| 6 | Fermer avec X | ⬜ |
| 7 | Fermer avec Escape | ⬜ |
| 8 | Fermer en cliquant dehors | ⬜ |

## 💡 Conseils

1. **Testez d'abord "Retirer pour vous"** - C'est moins destructif
2. **Vérifiez les animations** - Elles doivent être fluides
3. **Testez sur différents navigateurs** - Chrome, Firefox, Edge
4. **Testez la responsivité** - Sur mobile et tablette

## 🎯 Résultat Final

Si tous les tests passent:
- ✅ La modal fonctionne parfaitement
- ✅ Les deux options de suppression fonctionnent
- ✅ Les animations sont fluides
- ✅ L'UX est intuitive
- ✅ Aucune erreur dans la console

---

**Prêt à tester?** Ouvrez le chatroom et essayez! 🚀
