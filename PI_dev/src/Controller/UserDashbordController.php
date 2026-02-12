<?php

namespace App\Controller;

use App\Repository\GoalRepository;
use App\Repository\RoutineRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UserDashboardController extends AbstractController
{
    #[IsGranted('ROLE_USER')]
    #[Route('/user/dashboard', name: 'user_dashboard')]
    public function dashboard(
        GoalRepository $goalRepository,
        RoutineRepository $routineRepository
    ) {
        $user = $this->getUser();

        // 📊 Stats
        $goalsCount = $goalRepository->count(['user' => $user]);
        $routinesCount = $routineRepository->count(['user' => $user]);

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
        ]);
    }
}
