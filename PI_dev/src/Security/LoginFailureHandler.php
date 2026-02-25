<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class LoginFailureHandler implements AuthenticationFailureHandlerInterface
{
    public function __construct(
        private RouterInterface $router,
        private RequestStack $requestStack,
        private TranslatorInterface $translator,
    ) {
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): RedirectResponse
    {
        $message = $this->translator->trans(
            $exception->getMessageKey(),
            $exception->getMessageData(),
            'security'
        );
        if ($message === '') {
            $message = 'Identifiants incorrects ou connexion refusée. Réessayez ou utilisez « Se connecter avec Google » si votre compte est lié à Google.';
        }
        $session = $this->requestStack->getSession();
        $session->getFlashBag()->add('login_error', $message);

        $url = $this->router->generate('app_login', ['login_failed' => 1]);
        return new RedirectResponse($url);
    }
}
