<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserCheckerInterface;

class AppUserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }

        // Compte créé via Google sans mot de passe : bloquer le login email/mot de passe
        if ($user->getPassword() === null || $user->getPassword() === '') {
            throw new CustomUserMessageAuthenticationException(
                'Ce compte utilise la connexion Google. Utilisez le bouton « Se connecter avec Google » ci-dessous.'
            );
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
        // Optionnel : vérifications après authentification (compte actif, etc.)
        if (!$user instanceof User) {
            return;
        }

        if (!$user->isEnabled()) {
            throw new CustomUserMessageAuthenticationException('Votre compte est désactivé.');
        }
    }
}
