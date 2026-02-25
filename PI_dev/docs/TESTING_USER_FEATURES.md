# Vérification et tests – Mailer, Reset Password, Emails, Login History, Connexion suspecte

Ce document décrit l’état de chaque fonctionnalité et comment les tester manuellement.

---

## 1. Symfony Mailer + Notifier

### État
- **Mailer** : utilisé partout pour l’envoi d’emails (inscription, reset password, changement MDP, connexion suspecte, rappel routine).
- **Notifier** : installé et configuré (`config/packages/notifier.yaml`) mais **non utilisé** dans le code (pas de SMS, Slack, etc.). Seul le Mailer est utilisé pour les emails.

### Fichiers clés
- `config/packages/mailer.yaml` – DSN `MAILER_DSN`
- `src/Service/EmailService.php` – envoi de tous les emails
- `.env` – `MAILER_DSN`, `MAILER_FROM_EMAIL`, `MAILER_FROM_NAME`

### Comment tester le Mailer
1. Vérifier la config : `php bin/console debug:config framework mailer`
2. **Test d’envoi** : faire une inscription ou un « Mot de passe oublié » avec un email valide et vérifier la réception (ou les logs si DSN `null://null` en dev).
3. Vérifier les logs : `var/log/dev.log` après envoi (lignes avec "Email ... envoyé" ou "Erreur envoi email").

---

## 2. Symfony Reset Password Bundle (SymfonyCasts)

### État
- Bundle utilisé avec token en base, throttle, expiration 1 h.
- Routes : `/reset-password` (demande), `/reset-password/check-email`, `/reset-password/reset/{token}`.
- Email de reset envoyé via `EmailService::sendPasswordResetLink()`.

### Fichiers clés
- `config/packages/reset_password.yaml` – `lifetime`, `throttle_limit`
- `src/Controller/ResetPasswordController.php`
- `src/Entity/ResetPasswordRequest.php`, `src/Repository/ResetPasswordRequestRepository.php`
- Commande : `php bin/console app:reset-password:clear-requests <email>`

### Comment tester
1. Aller sur `/login` → clic « Mot de passe oublié » (ou aller sur `/reset-password`).
2. Saisir un email d’un utilisateur existant → Soumettre.
3. Vérifier la page « Vérifie ta boîte mail » puis la réception de l’email (lien de reset).
4. Cliquer sur le lien → page « Nouveau mot de passe » → saisir 2x le nouveau MDP → Soumettre.
5. Vérifier la redirection vers `/login` et le message de succès ; se connecter avec le nouveau mot de passe.
6. **Token expiré** : attendre 1 h ou modifier en base `reset_password_request.expires_at` dans le passé, puis rouvrir le lien → message d’erreur attendu.
7. **Throttle** : refaire une demande juste après → même page « check email » sans nouvel envoi (throttle 120 s). Pour débloquer :  
   `php bin/console app:reset-password:clear-requests ton@email.com`

---

## 3. Email confirmations

### État
- **Inscription** : `RegistrationController` appelle `EmailService::sendRegistrationConfirmation()` après `persist` + `flush`.
- **Changement de mot de passe** : `ChangePasswordController` et `ResetPasswordController` (après reset) appellent `EmailService::sendPasswordChanged()`.
- **Reset password** : `EmailService::sendPasswordResetLink()` (lien avec token).
- **Connexion suspecte** : voir section 5.

### Fichiers clés
- `src/Controller/RegistrationController.php` (ligne ~73)
- `src/Controller/ChangePasswordController.php` (ligne ~38)
- `src/Controller/ResetPasswordController.php` (ligne ~120)
- `src/Service/EmailService.php`
- `templates/emails/` : `registration_confirmation.html.twig`, `password_changed.html.twig`, `reset_password.html.twig`, `suspicious_login.html.twig`, `routine_reminder.html.twig`

### Comment tester
| Action | Email attendu | Où |
|--------|----------------|-----|
| Inscription (`/register`) | « Bienvenue sur DayFlow ! » | Boîte mail de l’email inscrit |
| Mot de passe oublié | « Réinitialisation de votre mot de passe » (lien) | Idem |
| Après reset MDP (lien cliqué) | « Votre mot de passe a été modifié » | Idem |
| Changement MDP (connecté, `/user/change-password`) | « Votre mot de passe a été modifié » | Idem |
| Connexion depuis une nouvelle IP | « Connexion suspecte détectée » | Idem |

Vérifier aussi `var/log/dev.log` pour les lignes "Email ... envoyé" ou "Erreur envoi email ...".

---

## 4. Login History Tracking

### État
- Chaque connexion réussie enregistre une entrée en base via `LoginHistoryService::recordLogin()`.
- Déclenchement : `LoginSubscriber` sur `LoginSuccessEvent`.
- Données enregistrées : user, IP, User-Agent, date, flag `isSuspicious` (nouvelle IP ou non).

### Fichiers clés
- `src/EventSubscriber/LoginSubscriber.php` – abonne à `LoginSuccessEvent`
- `src/Service/LoginHistoryService.php` – `recordLogin()`, `getRecentLogins()`, `getSuspiciousLogins()`, `countSuspiciousLogins()`
- `src/Repository/UserLoginHistoryRepository.php` – `hasIpBeenUsed()`, `findRecentByUser()`, `findSuspiciousByUser()`
- `src/Entity/UserLoginHistory.php`
- `src/Controller/LoginHistoryController.php` – route `/user/login-history`
- Dashboard user : `UserDashboardController` injecte `recentLogins` et `suspiciousLoginsCount` ; composant `templates/user/components/login_history.html.twig`

### Comment tester
1. Se connecter avec un compte utilisateur.
2. Ouvrir le tableau de bord utilisateur (`/user/dashboard`) : la section « Historique de connexion » doit afficher la dernière connexion (IP, navigateur, date).
3. Aller sur `/user/login-history` : liste des dernières connexions (jusqu’à 50) et bloc « Connexions suspectes » si applicable.
4. Se déconnecter, se reconnecter : une nouvelle ligne doit apparaître dans l’historique (même IP → non suspecte).
5. Vérifier en base : table `user_login_history`, colonnes `user_id`, `ip_address`, `user_agent`, `logged_at`, `is_suspicious`.

---

## 5. Détection de connexion suspecte

### État
- Une connexion est marquée **suspecte** si l’IP n’a **jamais** été utilisée auparavant pour cet utilisateur (`UserLoginHistoryRepository::hasIpBeenUsed()`).
- Si suspecte : enregistrement avec `isSuspicious = true` + envoi d’un email via `EmailService::sendSuspiciousLogin()`.

### Fichiers clés
- `src/Service/LoginHistoryService.php` – `recordLogin()` : `$isNewIp = !$this->loginHistoryRepository->hasIpBeenUsed($user, $ipAddress)` puis `sendSuspiciousLogin()` si `$isNewIp`.
- `templates/emails/suspicious_login.html.twig`
- Dashboard / page historique : affichage du nombre de connexions suspectes et lien « Changer mon mot de passe ».

### Comment tester
1. Avec un utilisateur qui ne s’est connecté qu’une fois (ou après nettoyage de `user_login_history` pour cet user), se connecter : première connexion = nouvelle IP → email « Connexion suspecte détectée » (si Mailer configuré).
2. Sur `/user/login-history`, la dernière connexion doit être marquée comme suspecte.
3. Se connecter depuis un autre réseau (autre IP, ou VPN) : nouvelle IP → nouvelle entrée suspecte + nouvel email.
4. Se reconnecter depuis la même IP qu’avant : entrée non suspecte, pas d’email.

---

## 6. Architecture (services + listeners)

### État
- **Services** : `EmailService`, `LoginHistoryService`, `NotificationService` (notifications in-app), etc.
- **Listeners / Subscribers** :
  - `LoginSubscriber` – `LoginSuccessEvent` → enregistrement de la connexion (historique + détection suspecte).
  - `UserActivityListener` – `KernelEvents::REQUEST` (activité utilisateur, ex. `last_activity_at`).
- Pas de logique métier lourde dans les contrôleurs : délégation aux services.

### Fichiers clés
- `src/Service/EmailService.php`, `src/Service/LoginHistoryService.php`
- `src/EventSubscriber/LoginSubscriber.php`
- `src/EventListener/UserActivityListener.php`
- `config/services.yaml` – injection `EmailService` (from email/name via env)

### Comment vérifier
1. Liste des services : `php bin/console debug:container --tag=event_subscriber`
2. Vérifier que `LoginSubscriber` est bien enregistré et abonné à `LoginSuccessEvent`.
3. En se connectant, vérifier dans les logs que la connexion est enregistrée (message du `LoginHistoryService`).

---

## Récap – commandes utiles

```bash
# Config Mailer
php bin/console debug:config framework mailer

# Débloquer un email pour reset password
php bin/console app:reset-password:clear-requests "email@example.com"

# Vider le cache après modification .env
php bin/console cache:clear

# Vérifier les subscribers
php bin/console debug:container --tag=event_subscriber
```

## Récap – scénarios de test rapides

| # | Scénario | Résultat attendu |
|---|----------|------------------|
| 1 | Inscription nouveau compte | Email « Bienvenue » reçu |
| 2 | Mot de passe oublié → clic lien → nouveau MDP | Email reset reçu, puis email « MDP modifié », connexion possible avec nouveau MDP |
| 3 | Connecté → Changer mot de passe | Email « MDP modifié » reçu |
| 4 | Connexion (même IP qu’avant) | Une ligne dans Historique de connexion, non suspecte |
| 5 | Connexion depuis une nouvelle IP | Une ligne suspecte + email « Connexion suspecte » |
| 6 | Dashboard user | Bloc « Dernières connexions » et éventuellement « X suspecte(s) » |

Si un de ces scénarios échoue, vérifier `var/log/dev.log` et la configuration `MAILER_DSN` / `MAILER_FROM_*` dans `.env`.
