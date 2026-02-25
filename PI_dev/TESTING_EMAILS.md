# 📧 Comment tester les emails en développement

## Configuration actuelle

Votre `.env` est configuré avec `MAILER_DSN=null://null`

Cela signifie que les emails **ne sont PAS envoyés réellement**, mais sont **capturés dans le profiler Symfony**.

---

## 🔍 Comment voir les emails envoyés

### Méthode 1 : Profiler Symfony (Recommandé en dev)

1. **Effectuez l'action** (ex: demander reset password)
2. **Regardez en bas de la page** → Barre de debug Symfony
3. **Cliquez sur l'icône email** (📧) dans la barre
4. **Vous verrez** :
   - Nombre d'emails envoyés
   - Destinataire
   - Sujet
   - Contenu HTML complet

**Exemple :**
```
📧 1 email sent
```

Cliquez dessus pour voir le contenu complet de l'email !

---

## 🐳 Méthode 2 : MailHog (Interface web pour emails)

Si vous voulez une interface dédiée pour voir les emails :

### Installation avec Docker

```bash
docker run -d -p 1025:1025 -p 8025:8025 mailhog/mailhog
```

### Configuration

Modifiez `.env` :
```env
MAILER_DSN=smtp://localhost:1025
```

### Utilisation

1. Effectuez l'action (reset password, inscription, etc.)
2. Ouvrez votre navigateur : http://localhost:8025
3. Vous verrez tous les emails envoyés avec interface graphique

---

## 🧪 Test complet du système

### 1. Test Reset Password

1. Allez sur `/login`
2. Cliquez "Mot de passe oublié ?"
3. Entrez un email existant
4. Cliquez "Envoyer"
5. **Vérifiez le profiler** (barre debug en bas) → icône 📧
6. Vous devriez voir l'email avec le lien de reset

### 2. Test Inscription

1. Allez sur `/register`
2. Remplissez le formulaire
3. Soumettez
4. **Vérifiez le profiler** → email de bienvenue

### 3. Test Connexion suspecte

1. Connectez-vous depuis un nouvel appareil/IP
2. **Vérifiez le profiler** → email d'alerte

### 4. Test Changement mot de passe

1. Allez sur `/user/change-password`
2. Changez votre mot de passe
3. **Vérifiez le profiler** → email de confirmation

---

## 🚨 Dépannage

### "Je ne vois pas l'icône email dans le profiler"

**Vérifications :**

1. Le profiler est-il visible en bas de page ?
   - Si NON : Vérifiez que `APP_ENV=dev` dans `.env`

2. L'email a-t-il vraiment été envoyé ?
   - Vérifiez les logs : `var/log/dev.log`
   - Cherchez : "Email ... envoyé"

3. Y a-t-il des erreurs ?
   ```bash
   php bin/console debug:container EmailService
   ```

### "L'email n'apparaît pas"

**Causes possibles :**

1. **Exception silencieuse** - Vérifiez les logs :
   ```bash
   tail -f var/log/dev.log
   ```

2. **Service non injecté** - Vérifiez que EmailService est bien appelé

3. **Mailer DSN invalide** - Vérifiez `.env` :
   ```env
   MAILER_DSN=null://null
   ```

---

## 📝 Logs à vérifier

Les logs sont dans `var/log/dev.log`

**Recherchez :**
```
[info] Email de confirmation envoyé
[info] Email reset password envoyé
[warning] Email connexion suspecte envoyé
[error] Erreur envoi email
```

**Commande pour suivre en temps réel :**
```bash
# Windows PowerShell
Get-Content var/log/dev.log -Wait -Tail 50

# Ou avec Git Bash
tail -f var/log/dev.log
```

---

## 🎯 Test rapide maintenant

1. **Videz le cache** :
   ```bash
   php bin/console cache:clear
   ```

2. **Allez sur** : http://localhost:8000/reset-password

3. **Entrez votre email** et soumettez

4. **Regardez la barre de debug en bas** → Cliquez sur l'icône 📧

5. **Vous devriez voir** :
   - Subject: "Réinitialisation de votre mot de passe"
   - To: votre-email@example.com
   - Le contenu HTML complet

---

## 🚀 Pour la production

Quand vous passerez en production, modifiez `.env.prod` :

### Option 1 : Gmail
```env
MAILER_DSN=gmail+smtp://votre-email@gmail.com:mot-de-passe-app@default
```

**Note :** Utilisez un "mot de passe d'application" Gmail, pas votre mot de passe normal.

### Option 2 : SMTP générique
```env
MAILER_DSN=smtp://username:password@smtp.example.com:587
```

### Option 3 : Service tiers (recommandé)
- **SendGrid** : `MAILER_DSN=sendgrid://KEY@default`
- **Mailgun** : `MAILER_DSN=mailgun://KEY:DOMAIN@default`
- **Amazon SES** : `MAILER_DSN=ses+smtp://ACCESS_KEY:SECRET_KEY@default`

---

## ✅ Checklist de vérification

- [ ] `APP_ENV=dev` dans `.env`
- [ ] `MAILER_DSN=null://null` dans `.env`
- [ ] Cache vidé : `php bin/console cache:clear`
- [ ] Profiler visible en bas de page
- [ ] Action effectuée (reset password)
- [ ] Icône 📧 visible dans profiler
- [ ] Email visible en cliquant sur l'icône

Si tout est ✅, les emails fonctionnent parfaitement ! 🎉
