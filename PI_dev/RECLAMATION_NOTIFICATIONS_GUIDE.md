# 📧 Guide des Notifications - Système de Réclamations

## ✅ Fonctionnalités Implémentées

### 1. **Service de Notifications**
- `ReclamationNotificationService` créé dans `src/Service/`
- Gestion automatique des emails pour les réclamations et réponses
- Templates HTML professionnels avec design pastel

### 2. **Notifications Automatiques**

#### 📨 Quand un utilisateur crée une réclamation:
- ✉️ **Email de confirmation** envoyé à l'utilisateur
- 🔔 **Notification admin** pour informer l'équipe support
- 📋 **Réponse automatique** créée dans le système

#### 💬 Quand un admin répond à une réclamation:
- ✉️ **Email de réponse** envoyé à l'utilisateur
- 🔔 **Notification** avec le contenu de la réponse
- 📊 **Statut mis à jour** automatiquement (PENDING → ANSWERED)

### 3. **Templates d'Emails**

#### Email de Confirmation
- Design avec gradient pastel (rose → violet)
- Affiche les détails de la réclamation
- Badge coloré pour le type
- Message de réassurance

#### Email de Réponse
- Design cohérent avec le thème
- Boîte de réponse mise en évidence
- Rappel de la réclamation initiale
- Informations de l'équipe support

### 4. **Configuration**

#### Fichier: `config/packages/notifier.yaml`
```yaml
framework:
    notifier:
        admin_recipients:
            - { email: admin@buildify.com }
        channel_policy:
            high: ['email']
            medium: ['email']
            low: ['email']
```

#### Variables d'environnement (.env)
```env
# Mailer Configuration
MAILER_DSN=smtp://localhost:1025
# Pour production, utilisez:
# MAILER_DSN=smtp://user:pass@smtp.example.com:587
```

## 🚀 Utilisation

### Pour Tester en Développement

1. **Installer MailHog** (serveur SMTP de test):
```bash
# Windows: Télécharger depuis https://github.com/mailhog/MailHog/releases
# Ou utiliser Docker:
docker run -d -p 1025:1025 -p 8025:8025 mailhog/mailhog
```

2. **Accéder à l'interface MailHog**:
- URL: http://localhost:8025
- Tous les emails envoyés apparaîtront ici

3. **Créer une réclamation**:
- Connectez-vous en tant qu'utilisateur
- Cliquez sur le bouton flottant en bas à droite
- Remplissez le formulaire
- ✅ Vous recevrez un email de confirmation

4. **Répondre en tant qu'admin**:
- Connectez-vous en tant qu'admin
- Allez dans "Réclamations"
- Cliquez sur "Répondre"
- ✅ L'utilisateur recevra un email avec votre réponse

## 📝 Personnalisation

### Modifier l'Email de l'Expéditeur
Dans `ReclamationNotificationService.php`:
```php
->from('noreply@buildify.com')  // Changez ici
```

### Modifier l'Email Admin
Dans `config/packages/notifier.yaml`:
```yaml
admin_recipients:
    - { email: votre-email@example.com }
```

### Personnaliser les Templates
Les templates sont dans `ReclamationNotificationService.php`:
- `getConfirmationEmailTemplate()` - Email de confirmation
- `getResponseEmailTemplate()` - Email de réponse

## 🎨 Design des Emails

### Couleurs Utilisées
- **Gradient Header**: Rose (#fbb6ce) → Violet (#d8b4fe)
- **Background**: Gris clair (#f9fafb)
- **Réponse Box**: Bleu clair (#dbeafe) → Indigo (#e0e7ff)
- **Bordures**: Violet (#d8b4fe) et Bleu (#3b82f6)

### Éléments Visuels
- ✅ Icônes emoji pour les titres
- 🎨 Badges colorés pour les types
- 📦 Boîtes avec bordures colorées
- 💌 Design responsive et professionnel

## 🔧 Configuration Production

### 1. Configurer un vrai serveur SMTP
```env
# Gmail
MAILER_DSN=gmail://username:password@default

# SendGrid
MAILER_DSN=sendgrid://KEY@default

# SMTP personnalisé
MAILER_DSN=smtp://user:pass@smtp.example.com:587
```

### 2. Activer les notifications SMS (optionnel)
```yaml
# config/packages/notifier.yaml
framework:
    notifier:
        texter_transports:
            twilio: '%env(TWILIO_DSN)%'
```

### 3. Ajouter Slack (optionnel)
```yaml
framework:
    notifier:
        chatter_transports:
            slack: '%env(SLACK_DSN)%'
```

## 📊 Logs et Débogage

Les erreurs d'envoi sont loguées mais ne bloquent pas l'application:
```php
error_log('Failed to send notification: ' . $e->getMessage());
```

Vérifiez les logs dans:
- `var/log/dev.log` (développement)
- `var/log/prod.log` (production)

## ✨ Prochaines Étapes

Pour améliorer encore le système:

1. **Ajouter des notifications SMS** pour les réclamations urgentes
2. **Intégrer Slack** pour notifier l'équipe en temps réel
3. **Créer un dashboard** de statistiques des notifications
4. **Ajouter des templates** personnalisables par l'admin
5. **Implémenter des rappels** automatiques pour les réclamations non traitées

## 🎉 Résumé

Le système de notifications est maintenant **entièrement fonctionnel** avec:
- ✅ Emails automatiques pour les utilisateurs
- ✅ Notifications pour les admins
- ✅ Templates HTML professionnels
- ✅ Design cohérent avec le thème pastel
- ✅ Gestion d'erreurs robuste
- ✅ Configuration flexible

**Tout est prêt pour la production!** 🚀
