# ⚡ Démarrage Rapide - Fonctionnalités de Présence

## 🚀 En 5 Minutes

### Étape 1: Vérifier l'Installation (30 secondes)

```bash
# Windows PowerShell
.\test_presence_setup.ps1

# Linux/Mac
chmod +x test_presence_setup.sh
./test_presence_setup.sh
```

**Résultat attendu:**
```
✅ Tous les tests sont passés!
```

---

### Étape 2: Intégrer dans le Template (2 minutes)

Ouvrir `templates/chatroom/chatroom.html.twig` et ajouter:

#### A. Avant `</body>` ou dans `{% block javascripts %}`:

```twig
{# Données pour le gestionnaire de présence #}
<div data-chatroom-id="{{ chatroom.id }}" style="display: none;"></div>
<div data-user-id="{{ app.user.id }}" style="display: none;"></div>

{# Script de gestion de présence #}
<script src="{{ asset('presence_manager.js') }}"></script>
```

#### B. Ajouter l'ID au champ de message:

Chercher le champ de saisie et ajouter `id="messageInput"`:

```twig
<textarea id="messageInput" name="message[content]" ...></textarea>
```

#### C. Ajouter l'indicateur de frappe (avant le formulaire):

```twig
<div id="typingIndicator" style="display: none; padding: 12px 28px; background: #f9fafb; border-top: 1px solid #e8ecf1;">
    <div style="display: flex; align-items: center; gap: 10px;">
        <div class="typing-dots">
            <span></span>
            <span></span>
            <span></span>
        </div>
        <span class="typing-text" style="font-size: 13px; color: #6b7280;"></span>
    </div>
</div>
```

#### D. Ajouter le CSS (dans `<style>`):

```css
.typing-dots {
    display: flex;
    gap: 4px;
}

.typing-dots span {
    width: 8px;
    height: 8px;
    background: #8b9dc3;
    border-radius: 50%;
    animation: typingBounce 1.4s infinite ease-in-out;
}

.typing-dots span:nth-child(1) {
    animation-delay: -0.32s;
}

.typing-dots span:nth-child(2) {
    animation-delay: -0.16s;
}

@keyframes typingBounce {
    0%, 80%, 100% {
        transform: scale(0);
        opacity: 0.5;
    }
    40% {
        transform: scale(1);
        opacity: 1;
    }
}
```

---

### Étape 3: Tester (2 minutes)

#### Test Rapide:

1. **Ouvrir 2 navigateurs** (normal + incognito)
2. **Se connecter** avec 2 utilisateurs différents
3. **Ouvrir le même chatroom** dans les 2 navigateurs
4. **Navigateur 1**: Commencer à taper
5. **Navigateur 2**: Observer "X est en train d'écrire..."

#### Vérifier la Console:

Ouvrir la console (F12) et chercher:
```
🟢 PresenceManager initialized for chatroom: 1
```

---

## ✅ Checklist Rapide

- [ ] Script de test exécuté avec succès
- [ ] Template modifié avec les 4 ajouts (A, B, C, D)
- [ ] Cache Symfony vidé: `php bin/console cache:clear`
- [ ] Serveur redémarré
- [ ] Test avec 2 navigateurs effectué
- [ ] Indicateur de frappe visible

---

## 🎯 Fonctionnalités Actives

Une fois intégré, vous aurez automatiquement:

✅ **Statut en ligne** - Indicateurs verts sur les avatars  
✅ **Indicateur de frappe** - "X est en train d'écrire..."  
✅ **Messages lus** - Double check (✓✓) quand lu  
✅ **Compteur de lectures** - "Lu par X personnes"  
✅ **Présence groupe** - "X en ligne sur Y membres"

---

## 🐛 Problème?

### Le script ne se charge pas
```bash
# Vérifier que le fichier existe
ls public/presence_manager.js

# Vider le cache
php bin/console cache:clear

# Vider le cache du navigateur
Ctrl + Shift + R
```

### Erreur 404 sur les routes
```bash
# Vérifier les routes
php bin/console debug:router | grep presence

# Vider le cache
php bin/console cache:clear
```

### Rien ne s'affiche
1. Ouvrir la console (F12)
2. Chercher les erreurs en rouge
3. Vérifier que les divs `data-chatroom-id` et `data-user-id` existent

---

## 📚 Documentation Complète

Pour plus de détails, consultez:
- `GUIDE_TEST_PRESENCE_FEATURES.md` - Guide de test complet
- `CHAT_PRESENCE_FEATURES_COMPLETE.md` - Documentation technique

---

## 🎉 C'est Tout!

Votre système de présence est maintenant actif!

**Prochaines étapes optionnelles:**
- Personnaliser les couleurs et animations
- Ajouter des sons de notification
- Intégrer Mercure pour du temps réel instantané

---

**Temps total:** ~5 minutes  
**Difficulté:** ⭐⭐☆☆☆ (Facile)  
**Statut:** ✅ Prêt à l'emploi
