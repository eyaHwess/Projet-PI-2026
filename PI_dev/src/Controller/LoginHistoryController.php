<?php

namespace App\Controller;

use App\Service\LoginHistoryService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class LoginHistoryController extends AbstractController
{
    #[IsGranted('ROLE_USER')]
    #[Route('/user/login-history', name: 'app_login_history')]
    public function index(LoginHistoryService $loginHistoryService): Response
    {
        $user = $this->getUser();
        $allLogins = $loginHistoryService->getRecentLogins($user, 50);
        $suspiciousLogins = $loginHistoryService->getSuspiciousLogins($user);

        return $this->render('user/login_history.html.twig', [
            'allLogins' => $allLogins,
            'suspiciousLogins' => $suspiciousLogins,
        ]);
    }
}
