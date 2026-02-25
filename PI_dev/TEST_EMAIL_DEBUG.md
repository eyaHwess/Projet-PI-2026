# 🔍 Debug Email Reset Password

## Étapes de test

1. **Videz le cache** (déjà fait) :
   ```bash
   php bin/console cache:clear
   ```

2. **Ouvrez un terminal pour suivre les logs en temps réel** :
   ```powershell
   Get-Content var/log/dev.log -Wait -Tail 50
   ```

3. **Dans votre navigateur** :
   - Allez sur : http://localhost:8000/reset-password
   - Entrez l'email : `abdellaeya@gmail.com` (vu dans les logs)
   - Cliquez "Envoyer"

4. **Regardez le terminal des logs** :
   Vous devriez voir :
   ```
   🔍 DEBUG: sendPasswordResetLink appelée
   ✅ Email reset password envoyé
   ```

5. **Regardez la barre de debug en bas de la page** :
   - Cherchez l'icône 📧 (email)
   - Elle devrait afficher "1"
   - Cliquez dessus pour voir l'email

## Si vous ne voyez toujours rien

### Vérification 1 : Le service est-il bien injecté ?

```bash
php bin/console debug:autowiring EmailService
```

### Vérification 2 : Test direct du service

Créez un fichier `test_email.php` à la racine :

```php
<?php

require_once __DIR__.'/vendor/autoload.php';

use App\Kernel;
use Symfony\Component\Dotenv\Dotenv;

(new Dotenv())->bootEnv(__DIR__.'/.env');

$kernel = new Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
$kernel->boot();
$container = $kernel->getContainer();

$emailService = $container->get('App\Service\EmailService');

echo "Test envoi email...\n";
$emailService->sendPasswordResetLink(
    'test@example.com',
    'Test',
    'fake-token-123'
);
echo "Email envoyé !\n";
```

Puis exécutez :
```bash
php test_email.php
```

### Vérification 3 : Le mailer fonctionne-t-il ?

```bash
php bin/console debug:config framework mailer
```

Vous devriez voir :
```
dsn: 'null://null'
```

## Problèmes possibles

### 1. EmailService n'est pas injecté dans ResetPasswordController

**Solution** : Vérifiez que le constructeur a bien :
```php
private EmailService $emailService,
```

### 2. Exception silencieuse

**Solution** : Les logs devraient maintenant montrer l'erreur avec le nouveau try-catch

### 3. Template email manquant

**Vérifiez** :
```bash
dir templates\emails\reset_password.html.twig
```

### 4. Mailer DSN invalide

**Vérifiez** `.env` :
```env
MAILER_DSN=null://null
```

## Ce que vous devriez voir

### Dans les logs (var/log/dev.log) :
```
[info] 🔍 DEBUG: sendPasswordResetLink appelée {"to":"abdellaeya@gmail.com","firstName":"...","token_length":40}
[info] ✅ Email reset password envoyé {"to":"abdellaeya@gmail.com"}
```

### Dans le profiler (barre debug) :
- Icône 📧 avec le chiffre "1"
- Cliquez dessus → voir l'email complet

### Si erreur :
```
[error] ❌ Erreur envoi email reset password {"to":"...","error":"..."}
```

## Test maintenant !

1. Terminal 1 : `Get-Content var/log/dev.log -Wait -Tail 50`
2. Navigateur : http://localhost:8000/reset-password
3. Entrez email et soumettez
4. Regardez les logs ET la barre de debug

**Dites-moi ce que vous voyez dans les logs !** 🔍
