<?php

namespace App\Security;

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
        $roles = $token->getRoleNames();

        return match (true) {
            in_array('ROLE_ADMIN', $roles) => new RedirectResponse($this->router->generate('admin_dashboard')),
            in_array('ROLE_COACH', $roles) => new RedirectResponse($this->router->generate('coach_dashboard')),
            default => new RedirectResponse($this->router->generate('user_dashboard')),
        };
    }
}
