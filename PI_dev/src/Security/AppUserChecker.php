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

        // Compte sans mot de passe : bloquer le login email/mot de passe
        $hasNoPassword = $user->getPassword() === null || $user->getPassword() === '';
        if (!$hasNoPassword) {
            return;
        }
        if ($user->getGoogleId()) {
            throw new CustomUserMessageAuthenticationException(
                'Ce compte utilise la connexion Google. Utilisez le bouton « Se connecter avec Google » ci-dessous.'
            );
        }
        throw new CustomUserMessageAuthenticationException(
            'Aucun mot de passe n\'est défini pour ce compte. Utilisez « Mot de passe oublié » ou contactez l\'administrateur.'
        );
    }

    public function checkPostAuth(UserInterface $user): void
    {
        // Optionnel : vérifications après authentification (compte actif, etc.)
        if (!$user instanceof User) {
            return;
        }

        if ($user->getStatus() === 'BANNED' || $user->getStatus() === 'INACTIVE') {
            throw new CustomUserMessageAuthenticationException('Votre compte est désactivé ou banni.');
        }
    }
}
