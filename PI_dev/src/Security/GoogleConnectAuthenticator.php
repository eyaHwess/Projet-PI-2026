<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

/**
 * Authenticator utilisé uniquement après le callback Google (authenticateUser).
 * Permet à Symfony d'enregistrer correctement la session.
 */
class GoogleConnectAuthenticator extends AbstractAuthenticator
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator
    ) {
    }

    public function supports(Request $request): ?bool
    {
        // Ne jamais intercepter automatiquement : GoogleController appelle
        // authenticateUser() manuellement après avoir récupéré l'utilisateur.
        return false;
    }

    public function authenticate(Request $request): Passport
    {
        // Non utilisé : support() retourne toujours false.
        throw new \LogicException('authenticate() ne doit pas être appelé.');
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        $user = $token->getUser();
        if ($user instanceof User && !$user->isOnboarded()) {
            return new RedirectResponse($this->urlGenerator->generate('app_onboarding'));
        }
        return new RedirectResponse($this->urlGenerator->generate('user_dashboard'));
    }

    public function onAuthenticationFailure(Request $request, \Symfony\Component\Security\Core\Exception\AuthenticationException $exception): ?Response
    {
        return new RedirectResponse($this->urlGenerator->generate('app_login'));
    }
}
