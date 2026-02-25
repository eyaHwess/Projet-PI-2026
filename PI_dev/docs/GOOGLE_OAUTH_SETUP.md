# Configuration Google OAuth 2.0 (DayFlow)

## 1. Google Cloud Console

1. Aller sur [Google Cloud Console](https://console.cloud.google.com/).
2. Créer un projet ou en sélectionner un.
3. **APIs & Services** → **Credentials** → **Create Credentials** → **OAuth client ID**.
4. Si demandé, configurer l’écran de consentement OAuth (type « Externe », nom de l’app, email de support, etc.).
5. Type d’application : **Application Web**.
6. Nom : par ex. « DayFlow Web ».
7. **Authorized redirect URIs** : ajouter l’URL de callback de votre app, par ex. :
   - En local : `http://localhost:8000/connect/google/check`
   - En prod : `https://votredomaine.com/connect/google/check`
8. Créer → copier le **Client ID** et le **Client Secret**.

## 2. Variables d’environnement

Dans `.env` ou `.env.local` (à ne pas commiter en prod) :

```env
GOOGLE_CLIENT_ID=votre_client_id.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=votre_client_secret
```

Puis :

```bash
php bin/console cache:clear
```

## 3. Routes

- **Démarrage du flux** : `GET /connect/google` → redirige vers Google (lien « Se connecter avec Google »).
- **Callback** : `GET /connect/google/check` → Google renvoie l’utilisateur ici ; l’app le connecte ou crée le compte.

L’URL exacte du callback doit être celle configurée dans la console Google (redirect URI).

## 4. Comportement

- Si l’utilisateur existe déjà (même `email` ou même `googleId`) : il est connecté.
- Si l’utilisateur n’existe pas : création d’un compte avec email, prénom, nom, photo (optionnel) et `googleId` ; mot de passe laissé à `null`.
- Un compte créé par inscription classique peut ensuite être « lié » à Google en se connectant une fois avec Google (on enregistre alors son `googleId`).

## 5. Sécurité

- Les routes `/connect/*` sont en `PUBLIC_ACCESS` pour permettre le flux OAuth.
- Aucun mot de passe n’est stocké pour les comptes créés uniquement via Google.
