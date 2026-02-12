<?php

namespace App\Controller;

use App\Repository\GoalRepository;
use App\Repository\RoutineRepository;
use App\Repository\SessionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UserDashboardController extends AbstractController
{
    #[IsGranted('ROLE_USER')]
    #[Route('/user/dashboard', name: 'user_dashboard')]
    public function dashboard(
        GoalRepository $goalRepository,
        RoutineRepository $routineRepository,
        SessionRepository $sessionRepository
    ): Response {
        $user = $this->getUser();

        $goalsCount = $goalRepository->count(['user' => $user]);
        $routinesCount = $routineRepository->countByUser($user);
        $recentGoals = $goalRepository->findBy(
            ['user' => $user],
            ['id' => 'DESC'],
            5
        );

        $sessions = $sessionRepository->findAllForUser($user);
        $sessionsCount = \count($sessions);

        return $this->render('user/dashuser.html.twig', [
            'goalsCount' => $goalsCount,
            'routinesCount' => $routinesCount,
            'recentGoals' => $recentGoals,
            'sessions' => $sessions,
            'sessionsCount' => $sessionsCount,
        ]);
    }
}
