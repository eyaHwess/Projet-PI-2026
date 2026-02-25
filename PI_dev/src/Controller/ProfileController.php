<?php

namespace App\Controller;

use App\Enum\PostStatus;
use App\Service\LoginHistoryService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ProfileController extends AbstractController
{
    #[IsGranted('ROLE_USER')]
    #[Route('/user/profile', name: 'app_user_profile', methods: ['GET'])]
    public function profile(LoginHistoryService $loginHistoryService): Response
    {
        $user = $this->getUser();

        $posts = array_values($user->getPosts()->filter(
            fn ($post) => $post->getStatus() === PostStatus::PUBLISHED->value
        )->toArray());

        $recentLogins = $loginHistoryService->getRecentLogins($user, 5);
        $suspiciousLoginsCount = $loginHistoryService->countSuspiciousLogins($user);

        return $this->render('user/profile.html.twig', [
            'user' => $user,
            'posts' => $posts,
            'recentLogins' => $recentLogins,
            'suspiciousLoginsCount' => $suspiciousLoginsCount,
        ]);
    }
}
