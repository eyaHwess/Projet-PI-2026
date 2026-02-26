# 🚀 Guide Rapide d'Utilisation

## Photos de Profil

### Comment ajouter une photo de profil?
Pour l'instant, les champs sont prêts dans la base de données. Il faut créer un formulaire d'édition de profil:

```php
// Dans un formulaire UserType.php
->add('profilePictureFile', VichFileType::class, [
    'required' => false,
    'allow_delete' => true,
    'download_uri' => false,
])
```

### Où apparaissent les photos?
- 💬 Messages du chatroom
- 👥 Liste des membres
- 📋 Barre latérale

## Gestion des États du Chatroom

### 🟢 Chatroom Actif
- État par défaut
- Tout le monde peut envoyer des messages
- Aucune restriction

### 🔒 Verrouiller un Chatroom
**Qui peut le faire?** Admins et modérateurs

**Comment?**
1. Ouvrir le chatroom
2. Cliquer sur le bouton "Verrouiller" dans l'en-tête
3. Confirmer

**Résultat:**
- Badge jaune 🔒 apparaît
- Zone de saisie désactivée
- Message: "Ce chatroom est verrouillé"
- Les membres ne peuvent plus envoyer de messages

**Pour déverrouiller:**
- Cliquer sur "Déverrouiller"

### 📦 Archiver un Chatroom
**Qui peut le faire?** Admins et modérateurs

**Comment?**
1. Ouvrir le chatroom
2. Cliquer sur "Archiver"
3. Confirmer

**Résultat:**
- Badge gris 📦 apparaît
- Lecture seule
- Historique conservé
- Aucun nouveau message possible

### 🔴 Supprimer un Chatroom
**Qui peut le faire?** Propriétaire uniquement

**Comment?**
1. Ouvrir le chatroom
2. Cliquer sur "Supprimer"
3. Confirmer

**Résultat:**
- Badge rouge 🔴 apparaît
- Chatroom inaccessible
- Données conservées (soft delete)

**Pour restaurer:**
- Cliquer sur "Restaurer" (propriétaire uniquement)

## Traduction DeepL

### État Actuel
⏳ En attente de confirmation email

### Actions à Faire
1. Vérifier votre email (compte DeepL)
2. Cliquer sur le lien de confirmation
3. Attendre 5-10 minutes
4. Tester:
   ```bash
   php bin/console app:test-translation "bonjour" en
   ```

### Utilisation dans le Chat
Une fois activé:
1. Survoler un message
2. Cliquer sur le bouton de traduction
3. Choisir la langue cible
4. La traduction s'affiche automatiquement

## Commandes Utiles

### Vider le cache
```bash
php bin/console cache:clear
```

### Voir les routes workflow
```bash
php bin/console debug:router | grep chatroom
```

### Tester la traduction
```bash
php bin/console app:test-translation "texte" langue_cible
```

## Permissions

| Action | Admin | Modérateur | Propriétaire | Membre |
|--------|-------|------------|--------------|--------|
| Verrouiller | ✅ | ✅ | ✅ | ❌ |
| Déverrouiller | ✅ | ✅ | ✅ | ❌ |
| Archiver | ✅ | ✅ | ✅ | ❌ |
| Supprimer | ❌ | ❌ | ✅ | ❌ |
| Restaurer | ❌ | ❌ | ✅ | ❌ |
| Voir les badges | ✅ | ✅ | ✅ | ✅ |

## Transitions d'État

```
┌─────────┐
│ ACTIVE  │ ◄──────────────────┐
└────┬────┘                    │
     │                         │
     │ verrouiller        restaurer
     ▼                         │
┌─────────┐                    │
│ LOCKED  │                    │
└────┬────┘                    │
     │                         │
     │ archiver                │
     ▼                         │
┌─────────┐                    │
│ARCHIVED │                    │
└────┬────┘                    │
     │                         │
     │ supprimer               │
     ▼                         │
┌─────────┐                    │
│ DELETED │────────────────────┘
└─────────┘
```

## Dépannage

### Le bouton de workflow n'apparaît pas
- Vérifier que vous êtes admin/modérateur/propriétaire
- Vider le cache: `php bin/console cache:clear`

### La photo de profil ne s'affiche pas
- Vérifier que le fichier existe dans `public/uploads/profiles/`
- Vérifier les permissions du dossier
- Vider le cache

### La traduction ne fonctionne pas
- Confirmer l'email DeepL
- Attendre 5-10 minutes après confirmation
- Vérifier la clé API dans `.env`
- Tester avec la commande console

## Support

Pour toute question:
1. Vérifier les fichiers de documentation
2. Consulter les logs: `var/log/dev.log`
3. Vider le cache
4. Vérifier les permissions

---

**Tout est prêt! Profitez de votre chatroom avancé! 🎉**
