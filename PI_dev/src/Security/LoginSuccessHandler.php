<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;

class LoginSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    public function __construct(private RouterInterface $router) {}

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): RedirectResponse
    {
        $user = $token->getUser();
        if ($user instanceof User && !$user->isOnboarded()) {
            return new RedirectResponse($this->router->generate('app_onboarding'));
        }

        $roles = $token->getRoleNames();

        if (in_array('ROLE_ADMIN', $roles, true)) {
            return new RedirectResponse($this->router->generate('admin_dashboard'));
        }

        if (in_array('ROLE_COACH', $roles, true)) {
            return new RedirectResponse($this->router->generate('app_coaching_request_index'));
        }

        if (in_array('ROLE_USER', $roles, true)) {
            return new RedirectResponse($this->router->generate('user_dashboard'));
        }

        return new RedirectResponse($this->router->generate('app_home'));
    }

}
