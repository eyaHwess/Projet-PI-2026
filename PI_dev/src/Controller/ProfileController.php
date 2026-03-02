<?php

namespace App\Controller;

use App\Entity\User;
use App\Enum\PostStatus;
use App\Repository\GoalParticipationRepository;
use App\Service\AiProfileGenerator;
use App\Service\LoginHistoryService;
use App\Service\Post\SavedPostService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ProfileController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private AiProfileGenerator $aiProfileGenerator,
        private SavedPostService $savedPostService,
        private GoalParticipationRepository $goalParticipationRepo,
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
    #[Route('/user/ai-profile/regenerate', name: 'app_ai_profile_regenerate', methods: ['GET'])]
    public function regenerateAiProfile(): Response
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            return $this->redirectToRoute('app_login');
        }

        $answers = $user->getOnboardingAnswers();

        if (empty($answers)) {
            $this->addFlash('info', 'Complétez d\'abord l\'onboarding pour générer votre profil IA.');
            return $this->redirectToRoute('app_onboarding');
        }

        $profile = $this->aiProfileGenerator->generateProfile($answers);

        if ($profile !== null) {
            $user->setArchetypeName($profile['archetypeName']);
            $user->setArchetypeDescription($profile['description']);
            $user->setArchetypeShortBio($profile['shortBio']);
            $user->setArchetypeData([
                'strengths' => $profile['strengths'],
                'growthAreas' => $profile['growthAreas'],
                'habitSuggestions' => $profile['habitSuggestions'],
            ]);
            $user->setIsOnboarded(true);
            $this->entityManager->flush();
            $this->addFlash('success', 'Votre profil IA a été généré avec succès !');
        } else {
            $this->addFlash('warning', 'Impossible de générer le profil IA pour le moment. Réessayez plus tard.');
        }

        return $this->redirectToRoute('app_user_ai_profile');
    }

    #[IsGranted('ROLE_USER')]
    #[Route('/user/profile', name: 'app_user_profile', methods: ['GET'])]
    public function profile(LoginHistoryService $loginHistoryService): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $allPosts = $user->getPosts();

        $publishedPosts = array_values($allPosts->filter(
            fn ($post) => $post->getStatus() === PostStatus::PUBLISHED->value
        )->toArray());

        $draftPosts = array_values($allPosts->filter(
            fn ($post) => $post->getStatus() === PostStatus::DRAFT->value
        )->toArray());

        $scheduledPosts = array_values($allPosts->filter(
            fn ($post) => $post->getStatus() === PostStatus::SCHEDULED->value
        )->toArray());

        $savedPostEntities = $this->savedPostService->getSavedPostsByUser($user);
        // getSavedPostsByUser returns SavedPost[] — extract the Post from each
        $savedPosts = array_map(fn ($sp) => $sp->getPost(), $savedPostEntities);

        $myChatrooms = $this->goalParticipationRepo->findApprovedByUser($user);

        $recentLogins = $loginHistoryService->getRecentLogins($user, 5);
        $suspiciousLoginsCount = $loginHistoryService->countSuspiciousLogins($user);

        return $this->render('user/profile.html.twig', [
            'user'                 => $user,
            'posts'                => $publishedPosts,
            'draftPosts'           => $draftPosts,
            'scheduledPosts'       => $scheduledPosts,
            'savedPosts'           => $savedPosts,
            'myChatrooms'          => $myChatrooms,
            'recentLogins'         => $recentLogins,
            'suspiciousLoginsCount' => $suspiciousLoginsCount,
            'hasOnboarded'         => $user->isOnboarded(),
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
