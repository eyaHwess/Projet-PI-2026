<?php

namespace App\Controller;

use App\Entity\User;
use App\Enum\PostStatus;
use App\Service\LoginHistoryService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ProfileController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/onboarding/reset', name: 'app_onboarding_reset', methods: ['GET'])]
    public function resetOnboarding(): Response
    {
        $user = $this->getUser();
        if ($user instanceof User) {
            $user->setIsOnboarded(false);
            $this->entityManager->flush();
            $this->addFlash('success', 'Vous pouvez refaire l\'onboarding pour régénérer votre profil IA.');
        }
        return $this->redirectToRoute('app_onboarding');
    }

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
            'hasOnboarded' => $user->isOnboarded(),
        ]);
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/user/ai-profile', name: 'app_user_ai_profile', methods: ['GET'])]
    public function aiProfile(): Response
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->redirectToRoute('app_login');
        }

        if (!$user->isOnboarded()) {
            $this->addFlash('info', 'Complétez l\'onboarding pour découvrir votre profil IA.');
            return $this->redirectToRoute('app_onboarding');
        }

        return $this->render('user/ai_profile.html.twig', [
            'user' => $user,
            'archetypeData' => $user->getArchetypeData() ?? [],
        ]);
    }
}
