# Plan d'Implémentation - Chatroom Avancé

## 🎯 Objectif
Transformer le chatroom en plateforme de communication professionnelle avec les fonctionnalités les plus impactantes pour la soutenance.

---

## 📋 Phase 1: Fonctionnalités MUST HAVE (6-8h)

### ✅ 1. Messages en Temps Réel (2h) ⭐⭐⭐⭐⭐
**Impact soutenance: MAXIMUM**

**Implémentation:**
- [ ] Améliorer l'AJAX auto-refresh existant (déjà à 3s)
- [ ] Ajouter animation smooth pour nouveaux messages
- [ ] Préserver scroll position
- [ ] Ajouter sound notification (optionnel)

**Fichiers:**
- `templates/chatroom/chatroom.html.twig` (JavaScript)

---

### ✅ 2. Système "Lu / Non Lu" (2-3h) ⭐⭐⭐⭐⭐
**Impact soutenance: TRÈS ÉLEVÉ**

**Implémentation:**
- [ ] Créer entité `MessageReadReceipt`
- [ ] Migration base de données
- [ ] Marquer messages comme lus à l'ouverture du chat
- [ ] Afficher ✔ Envoyé / ✔✔ Lu
- [ ] Badge unread count sur goal list

**Fichiers:**
- `src/Entity/MessageReadReceipt.php` (nouveau)
- `src/Controller/GoalController.php` (markAsRead action)
- `templates/chatroom/chatroom.html.twig` (checkmarks)
- `templates/goal/list.html.twig` (badge)

---

### ✅ 3. Modifier Message (1-2h) ⭐⭐⭐⭐
**Impact soutenance: ÉLEVÉ**

**Implémentation:**
- [ ] Ajouter champs `is_edited`, `edited_at` à Message
- [ ] Migration base de données
- [ ] Bouton "Edit" sur messages
- [ ] Inline editing avec JavaScript
- [ ] Label "Edited" après modification

**Fichiers:**
- `src/Entity/Message.php` (champs)
- `src/Controller/GoalController.php` (editMessage action)
- `templates/chatroom/chatroom.html.twig` (UI)

---

### ✅ 4. Pagination "Load More" (1h) ⭐⭐⭐⭐
**Impact soutenance: ÉLEVÉ**

**Implémentation:**
- [ ] Limiter à 20 messages initialement
- [ ] Bouton "Load older messages" en haut
- [ ] AJAX pour charger plus
- [ ] Préserver scroll position

**Fichiers:**
- `src/Controller/GoalController.php` (pagination)
- `templates/chatroom/chatroom.html.twig` (button + AJAX)

---

## 📋 Phase 2: Fonctionnalités SHOULD HAVE (4-6h)

### ✅ 5. Réponses à un Message (2h) ⭐⭐⭐⭐
**Impact soutenance: ÉLEVÉ**

**Implémentation:**
- [ ] Ajouter champ `parent_message_id` à Message
- [ ] Migration base de données
- [ ] Bouton "Reply" sur messages
- [ ] Afficher message quoté
- [ ] Scroll to original on click

**Fichiers:**
- `src/Entity/Message.php` (parent relation)
- `src/Controller/GoalController.php` (reply logic)
- `templates/chatroom/chatroom.html.twig` (UI)

---

### ✅ 6. Messages avec Fichiers (2-3h) ⭐⭐⭐⭐
**Impact soutenance: TRÈS ÉLEVÉ**

**Implémentation:**
- [ ] Créer entité `MessageFile`
- [ ] Migration base de données
- [ ] Upload button (📎)
- [ ] Validation (type, size)
- [ ] Preview images
- [ ] Download link

**Fichiers:**
- `src/Entity/MessageFile.php` (nouveau)
- `src/Controller/GoalController.php` (upload logic)
- `templates/chatroom/chatroom.html.twig` (UI)
- `/uploads/chatroom/` (directory)

---

### ✅ 7. Messages Système (1h) ⭐⭐⭐
**Impact soutenance: MOYEN**

**Implémentation:**
- [ ] Ajouter champ `is_system_message` à Message
- [ ] Migration base de données
- [ ] Créer messages auto: join, complete, progress
- [ ] Style différent (gris, centré)

**Fichiers:**
- `src/Entity/Message.php` (champ)
- `src/Controller/GoalController.php` (system messages)
- `templates/chatroom/chatroom.html.twig` (style)

---

## 📋 Phase 3: Fonctionnalités NICE TO HAVE (3-5h)

### ✅ 8. Indicateur "en train d'écrire..." (1-2h) ⭐⭐⭐
**Impact soutenance: MOYEN**

**Implémentation:**
- [ ] Détecter typing avec JavaScript
- [ ] AJAX pour broadcast typing status
- [ ] Afficher "X is typing..."
- [ ] Timeout après 3s inactivité

**Fichiers:**
- `src/Controller/GoalController.php` (typing endpoint)
- `templates/chatroom/chatroom.html.twig` (JavaScript)

---

### ✅ 9. Recherche dans Messages (1h) ⭐⭐⭐
**Impact soutenance: MOYEN**

**Implémentation:**
- [ ] Search bar dans header
- [ ] AJAX search endpoint
- [ ] Highlight résultats
- [ ] Jump to message

**Fichiers:**
- `src/Controller/GoalController.php` (search action)
- `templates/chatroom/chatroom.html.twig` (UI)

---

### ✅ 10. Mention Utilisateur @user (1-2h) ⭐⭐⭐
**Impact soutenance: MOYEN**

**Implémentation:**
- [ ] Détecter @ dans input
- [ ] Autocomplete participants
- [ ] Highlight mentions
- [ ] Notification to mentioned user

**Fichiers:**
- `src/Controller/GoalController.php` (mention logic)
- `templates/chatroom/chatroom.html.twig` (autocomplete)

---

## 🚀 Recommandation pour Soutenance

### Implémentez EN PRIORITÉ (8-10h):
1. **Messages en Temps Réel** (2h) - Déjà partiellement fait, améliorer
2. **Système Lu/Non Lu** (3h) - Très impressionnant, comme WhatsApp
3. **Modifier Message** (2h) - Professionnel
4. **Pagination Load More** (1h) - Performance
5. **Réponses à Message** (2h) - Organisation

Ces 5 fonctionnalités donnent un chatroom de niveau professionnel!

### Si vous avez plus de temps (4-6h):
6. **Messages avec Fichiers** (3h) - Très visuel
7. **Messages Système** (1h) - Automatisation
8. **Indicateur Typing** (2h) - UX moderne

---

## 📊 Ordre d'Implémentation Recommandé

```
Jour 1 (4h):
✅ Messages en Temps Réel (amélioration) (1h)
✅ Système Lu/Non Lu (3h)

Jour 2 (4h):
✅ Modifier Message (2h)
✅ Pagination Load More (1h)
✅ Réponses à Message (2h) - commencer

Jour 3 (4h):
✅ Réponses à Message (finir) (1h)
✅ Messages avec Fichiers (3h)

Jour 4 (2h):
✅ Messages Système (1h)
✅ Polish UI/UX (1h)
```

---

## 🎨 Améliorations Visuelles Rapides

### Quick Wins (30min chacun):
- [ ] Animations d'apparition des messages
- [ ] Hover effects sur messages
- [ ] Smooth scroll
- [ ] Loading spinners
- [ ] Toast notifications
- [ ] Sound effects (optionnel)

---

## 📝 Estimation Totale

- **Phase 1 (MUST)**: 6-8 heures
- **Phase 2 (SHOULD)**: 4-6 heures
- **Phase 3 (NICE)**: 3-5 heures

**Total**: 13-19 heures

---

## 🔥 Conseil Final

**Pour votre soutenance, concentrez-vous sur:**

1. ✅ **Système Lu/Non Lu** - Comme WhatsApp, très impressionnant
2. ✅ **Messages en Temps Réel** - Déjà fait, juste améliorer
3. ✅ **Modifier Message** - Professionnel
4. ✅ **Réponses à Message** - Organisation
5. ✅ **Pagination** - Performance

Ces 5 fonctionnalités en 10-12h transforment votre chatroom en plateforme professionnelle!

---

## 🎯 Fonctionnalités Déjà Implémentées ✅

- ✅ Réactions aux messages (👍 👏 🔥 ❤️)
- ✅ Message épinglé (📌)
- ✅ Suppression de message (🗑️)
- ✅ Auto-refresh (toutes les 3s)
- ✅ Design moderne avec gradient
- ✅ Liste participants
- ✅ Avatars avec initiales

Vous avez déjà une excellente base! 🚀
