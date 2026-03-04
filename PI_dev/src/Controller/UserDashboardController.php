<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\GoalRepository;
use App\Repository\RoutineRepository;
use App\Repository\SessionRepository;
use App\Service\LoginHistoryService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UserDashboardController extends AbstractController
{
    #[IsGranted('ROLE_USER')]
    #[Route('/user/dashboard', name: 'user_dashboard')]
    public function dashboard(
        GoalRepository $goalRepository,
        RoutineRepository $routineRepository,
        SessionRepository $sessionRepository,
        LoginHistoryService $loginHistoryService
    ) {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        // 📊 Stats
        $goalsCount = $goalRepository->count(['user' => $user]);
        $routinesCount = $routineRepository->countByUser($user);
        $sessions = $sessionRepository->findAllForUser($user);
        $sessionsCount = $sessionRepository->countAllForUser($user);
        $recentLogins = $loginHistoryService->getRecentLogins($user, 5);
        $suspiciousLoginsCount = $loginHistoryService->countSuspiciousLogins($user);

        // 📋 Derniers objectifs
        $recentGoals = $goalRepository->findBy(
            ['user' => $user],
            ['id' => 'DESC'],
            5
        );

        return $this->render('user/dashuser.html.twig', [
            'goalsCount' => $goalsCount,
            'routinesCount' => $routinesCount,
            'recentGoals' => $recentGoals,
            'sessions' => $sessions,
            'sessionsCount' => $sessionsCount,
            'recentLogins' => $recentLogins,
            'suspiciousLoginsCount' => $suspiciousLoginsCount,
        ]);
    }
}
