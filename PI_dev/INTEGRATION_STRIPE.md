# Intégration Stripe - Paiement des sessions

## Installation

1. **Installer la dépendance Stripe**
   ```bash
   composer update stripe/stripe-php
   ```

2. **Configurer les clés** (déjà dans `.env.local`)
   - `STRIPE_SECRET_KEY` : clé secrète (sk_test_...)
   - `STRIPE_PUBLIC_KEY` : clé publique (pk_test_...)

3. **Exécuter la migration**
   ```bash
   php bin/console doctrine:migrations:migrate
   ```

## Flux utilisateur

1. L'utilisateur (client) ouvre une session de coaching
2. Clic sur **« Payer la session »**
3. Redirection vers Stripe Checkout
4. Paiement par carte (mode test : 4242 4242 4242 4242)
5. Redirection vers `/payment/success/{id}`
6. `paymentStatus` passe à `paid`

## Fichiers créés/modifiés

- `src/Service/StripeService.php` - Service Stripe Checkout
- `src/Controller/PaymentController.php` - Routes checkout + success
- `src/Entity/Session.php` - Ajout `price`, `paymentStatus`
- `config/packages/stripe.yaml` - Paramètres
- `templates/payment/success.html.twig` - Page succès
- `templates/session/show.html.twig` - Bouton Payer + affichage prix
- `src/Form/SessionType.php` - Champ prix pour le coach

## Mode test Stripe

Carte de test : `4242 4242 4242 4242`  
Date / CVC : toute valeur future valide
