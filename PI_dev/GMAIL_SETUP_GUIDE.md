# 📧 Guide de Configuration Gmail pour les Notifications

## 🚀 Configuration Rapide

### Étape 1: Créer un Mot de Passe d'Application Gmail

1. **Accédez à votre compte Google**
   - Allez sur: https://myaccount.google.com/security

2. **Activez la Vérification en 2 Étapes** (si ce n'est pas déjà fait)
   - Cliquez sur "Vérification en 2 étapes"
   - Suivez les instructions pour l'activer

3. **Créez un Mot de Passe d'Application**
   - Allez sur: https://myaccount.google.com/apppasswords
   - Sélectionnez "Mail" comme application
   - Sélectionnez votre appareil
   - Cliquez sur "Générer"
   - **Copiez le mot de passe de 16 caractères** (exemple: `abcd efgh ijkl mnop`)

### Étape 2: Configurer le Fichier .env.local

1. **Ouvrez le fichier** `PI_dev/.env.local`

2. **Remplacez la ligne MAILER_DSN** avec vos informations:

```env
# Remplacez YOUR_GMAIL et YOUR_16_CHAR_APP_PASSWORD
MAILER_DSN=gmail://votre.email@gmail.com:abcdefghijklmnop@default
```

**⚠️ IMPORTANT:**
- Utilisez votre adresse Gmail complète (avec @gmail.com)
- Utilisez le mot de passe d'application (PAS votre mot de passe Gmail normal)
- Retirez les espaces du mot de passe d'application
- Exemple: `abcd efgh ijkl mnop` devient `abcdefghijklmnop`

### Étape 3: Tester l'Envoi d'Emails

1. **Videz le cache Symfony**:
```bash
php bin/console cache:clear
```

2. **Créez une réclamation** en tant qu'utilisateur

3. **Répondez à la réclamation** en tant qu'admin

4. **Vérifiez votre boîte Gmail** - vous devriez recevoir l'email!

## 🔧 Configurations Alternatives

### Option 1: Gmail avec DSN complet (si l'option gmail:// ne fonctionne pas)

```env
MAILER_DSN=smtp://votre.email@gmail.com:abcdefghijklmnop@smtp.gmail.com:587
```

### Option 2: Utiliser un autre service SMTP

#### SendGrid (recommandé pour production)
```env
MAILER_DSN=sendgrid://YOUR_API_KEY@default
```

#### Mailgun
```env
MAILER_DSN=mailgun://YOUR_API_KEY:YOUR_DOMAIN@default
```

#### SMTP Personnalisé
```env
MAILER_DSN=smtp://username:password@smtp.example.com:587
```

## 🐛 Dépannage

### Problème: "Authentication failed"

**Solution 1**: Vérifiez que vous utilisez un mot de passe d'application
- ❌ N'utilisez PAS votre mot de passe Gmail normal
- ✅ Utilisez le mot de passe de 16 caractères généré

**Solution 2**: Vérifiez la vérification en 2 étapes
- Elle DOIT être activée pour créer des mots de passe d'application

### Problème: "Connection timeout"

**Solution**: Vérifiez votre pare-feu
```bash
# Testez la connexion SMTP
telnet smtp.gmail.com 587
```

### Problème: Les emails vont dans les spams

**Solutions**:
1. Ajoutez l'email à vos contacts
2. Marquez l'email comme "Non spam"
3. Pour la production, configurez SPF/DKIM pour votre domaine

### Problème: "Could not authenticate"

**Vérifiez**:
1. Le mot de passe d'application est correct (sans espaces)
2. L'email est correct (avec @gmail.com)
3. La vérification en 2 étapes est activée

## 📝 Exemple Complet de Configuration

### Fichier: `.env.local`
```env
###> symfony/mailer ###
# Configuration Gmail
MAILER_DSN=gmail://john.doe@gmail.com:abcdefghijklmnop@default
###< symfony/mailer ###
```

### Fichier: `config/services.yaml`
```yaml
parameters:
    # Changez l'email de l'expéditeur si nécessaire
    app.mailer.sender_email: 'noreply@buildify.com'
```

## 🧪 Test Manuel

Vous pouvez tester l'envoi d'emails avec cette commande:

```bash
php bin/console mailer:test votre.email@gmail.com
```

## 📊 Vérifier les Logs

Si les emails ne sont pas envoyés, vérifiez les logs:

```bash
# Logs de développement
tail -f var/log/dev.log

# Rechercher les erreurs d'email
grep "Failed to send" var/log/dev.log
```

## ✅ Checklist de Vérification

Avant de tester, assurez-vous que:

- [ ] La vérification en 2 étapes est activée sur Gmail
- [ ] Vous avez créé un mot de passe d'application
- [ ] Le mot de passe d'application est dans `.env.local` (sans espaces)
- [ ] Votre email Gmail est correct dans `.env.local`
- [ ] Le cache Symfony a été vidé
- [ ] Le serveur web est redémarré (si nécessaire)

## 🎯 Configuration Recommandée pour Production

Pour la production, utilisez un service d'emailing professionnel:

### SendGrid (Gratuit jusqu'à 100 emails/jour)
1. Créez un compte sur https://sendgrid.com
2. Générez une API Key
3. Configurez:
```env
MAILER_DSN=sendgrid://YOUR_API_KEY@default
```

### Avantages:
- ✅ Meilleure délivrabilité
- ✅ Statistiques d'envoi
- ✅ Pas de limite Gmail
- ✅ Support professionnel

## 🔐 Sécurité

**⚠️ IMPORTANT:**
- Ne commitez JAMAIS le fichier `.env.local` dans Git
- Ne partagez JAMAIS votre mot de passe d'application
- Utilisez des variables d'environnement en production
- Révoque les mots de passe d'application non utilisés

## 📞 Support

Si vous rencontrez des problèmes:

1. Vérifiez les logs: `var/log/dev.log`
2. Testez la connexion SMTP manuellement
3. Vérifiez que le port 587 n'est pas bloqué
4. Essayez avec un autre compte Gmail

## 🎉 Résultat Attendu

Une fois configuré correctement, vous recevrez:

1. **Email de confirmation** quand un utilisateur crée une réclamation
2. **Email de réponse** quand un admin répond
3. **Design professionnel** avec couleurs pastel
4. **Contenu HTML** formaté et responsive

**Bon courage! 🚀**
