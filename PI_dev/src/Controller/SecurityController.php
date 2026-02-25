<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(Request $request, AuthenticationUtils $authenticationUtils): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        // Si on arrive avec login_failed=1 mais sans message flash (session perdue), afficher un message
        if ($request->query->getInt('login_failed') === 1) {
            $session = $request->getSession();
            $flashBag = $session->getFlashBag();
            $loginErrors = $flashBag->peek('login_error');
            if (empty($loginErrors)) {
                $flashBag->add('login_error', 'Identifiants incorrects ou compte inaccessible. Réessayez ou utilisez « Se connecter avec Google » si votre compte est lié à Google.');
            }
        }

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
