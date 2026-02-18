# Guide de Démarrage Rapide - Système de Coaching Amélioré

## 🚀 Installation en 3 étapes

### Étape 1 : Appliquer les migrations
```bash
php bin/console doctrine:migrations:migrate --no-interaction
```

### Étape 2 : Peupler avec des coaches de test
```bash
php bin/console app:populate-coaches
```

### Étape 3 : Accéder à l'interface
Ouvrez votre navigateur : **http://localhost:8000/coaches/enhanced**

---

## 📖 Utilisation

### Pour les Utilisateurs (Clients)

#### 1. Rechercher un coach
- Utilisez la barre de recherche en haut
- Tapez un nom, une spécialité ou un mot-clé
- Les résultats s'affichent instantanément

#### 2. Filtrer les coaches
Dans la barre latérale gauche :
- **Spécialité** : Yoga, Musculation, Nutrition, etc.
- **Prix** : Définissez votre budget (min/max)
- **Note** : Choisissez une note minimum
- **Disponibilité** : Disponible ou Limité
- **Type** : En ligne, En présentiel, Hybride

#### 3. Trier les résultats
Cliquez sur les boutons de tri :
- ⭐ **Mieux notés** : Les coaches les mieux évalués
- ⬆️ **Prix croissant** : Du moins cher au plus cher
- ⬇️ **Prix décroissant** : Du plus cher au moins cher
- 🔥 **Popularité** : Les plus demandés

#### 4. Faire une demande rapide
1. Cliquez sur **"Demande rapide"** sur la carte d'un coach
2. Remplissez le formulaire :
   - Objectif principal
   - Niveau actuel
   - Fréquence souhaitée
   - Budget par séance
   - Message personnalisé
3. Cliquez sur **"Envoyer la demande"**
4. Attendez la confirmation ✅

### Pour les Coaches

#### Voir vos demandes
Accédez à : **http://localhost:8000/coach/requests**

#### Gérer une demande
- **Accepter** : Crée une session de coaching
- **Refuser** : Décline la demande
- **Remettre en attente** : Repasse en statut "pending"

---

## 🎨 Fonctionnalités Clés

### Cartes de Coach
Chaque carte affiche :
- 📸 Photo de profil
- ⭐ Note moyenne + nombre d'avis
- 💰 Prix par séance
- 📅 Disponibilité
- 📝 Biographie courte
- 🏆 Badges (Top coach, Répond rapidement, Nouveau)
- 👥 Nombre de séances réalisées

### Formulaire Intelligent
- ✅ Validation en temps réel
- 📊 Compteur de caractères (0/1000)
- 🎨 Animations fluides
- ✅ Confirmation visuelle
- 🔒 Sécurisé (CSRF protection)

### Interface Moderne
- 📱 **Responsive** : Fonctionne sur mobile, tablette et desktop
- 🎭 **Animations** : Transitions fluides et micro-animations
- 🎨 **Design** : Interface claire et professionnelle
- ⚡ **Rapide** : Recherche instantanée

---

## 🔧 Configuration

### Modifier les coaches de test
Éditez `src/Command/PopulateCoachesCommand.php` et relancez :
```bash
php bin/console app:populate-coaches
```

### Personnaliser les couleurs
Modifiez les variables CSS dans `templates/coach/index_enhanced.html.twig` :
```css
:root {
    --orange-primary: #f97316;  /* Couleur principale */
    --orange-hover: #ea580c;    /* Couleur au survol */
    --orange-light: #fff5f0;    /* Couleur de fond */
}
```

### Ajouter des spécialités
1. Créez des coaches avec de nouvelles spécialités
2. Elles apparaîtront automatiquement dans les filtres

---

## 📊 Données de Test

### 8 Coaches Disponibles

| Coach | Spécialité | Prix | Note | Séances |
|-------|-----------|------|------|---------|
| Sophie Martin | Yoga | 45€ | 4.8⭐ | 450 |
| Thomas Dubois | Musculation | 60€ | 4.9⭐ | 680 |
| Marie Leroy | Nutrition | 55€ | 4.7⭐ | 320 |
| Lucas Bernard | Cardio | 40€ | 4.6⭐ | 280 |
| Emma Petit | Pilates | 50€ | 4.9⭐ | 520 |
| Alexandre Roux | CrossFit | 65€ | 4.5⭐ | 210 |
| Camille Moreau | Yoga | 48€ | 4.8⭐ | 390 |
| Hugo Simon | Boxe | 55€ | 4.7⭐ | 340 |

---

## 🐛 Dépannage

### Problème : Page blanche
```bash
# Vérifier les logs
tail -f var/log/dev.log

# Vider le cache
php bin/console cache:clear
```

### Problème : Aucun coach affiché
```bash
# Re-peupler la base
php bin/console app:populate-coaches
```

### Problème : Erreur de base de données
```bash
# Mettre à jour le schéma
php bin/console doctrine:schema:update --force
```

### Problème : JavaScript ne fonctionne pas
1. Ouvrez la console du navigateur (F12)
2. Vérifiez les erreurs
3. Rechargez la page (Ctrl+F5)

---

## 📱 Compatibilité

### Navigateurs supportés
- ✅ Chrome 90+
- ✅ Firefox 88+
- ✅ Safari 14+
- ✅ Edge 90+

### Résolutions
- 📱 Mobile : 320px+
- 📱 Tablette : 768px+
- 💻 Desktop : 1024px+
- 🖥️ Large : 1440px+

---

## 🎯 Prochaines Étapes

1. **Tester l'interface** : Explorez toutes les fonctionnalités
2. **Créer un compte** : Testez en tant qu'utilisateur
3. **Faire une demande** : Envoyez une demande à un coach
4. **Personnaliser** : Adaptez les couleurs et le contenu
5. **Ajouter des coaches** : Créez vos propres coaches

---

## 📚 Documentation Complète

- **Fonctionnalités détaillées** : `DEMANDE_COACHING_AMELIOREE.md`
- **Tests API** : `TEST_API_COACHES.md`
- **Architecture** : Consultez le code source

---

## 💡 Astuces

### Recherche Rapide
- Tapez juste quelques lettres pour filtrer
- La recherche fonctionne sur : nom, prénom, email, spécialité, bio

### Filtres Multiples
- Combinez plusieurs filtres pour affiner votre recherche
- Exemple : Yoga + 40-50€ + Note 4.5+

### Réinitialisation
- Cliquez sur "Réinitialiser" pour effacer tous les filtres d'un coup

### Demande Rapide
- Le formulaire se remplit automatiquement avec les infos du coach
- Tous les champs sont optionnels sauf le message

---

## 🎉 Félicitations !

Vous êtes prêt à utiliser le système de coaching amélioré !

Pour toute question, consultez la documentation ou contactez le support.

**Bon coaching ! 💪**
