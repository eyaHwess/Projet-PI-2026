<?php

namespace App\Controller;

use App\Entity\Goal;
use App\Entity\Routine;
use App\Entity\Activity;
use App\Repository\GoalRepository;
use App\Repository\RoutineRepository;
use App\Repository\ActivityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class FavoriteController extends AbstractController
{
    #[Route('/favorites', name: 'app_favorites')]
    #[IsGranted('ROLE_USER')]
    public function index(
        GoalRepository $goalRepository,
        RoutineRepository $routineRepository,
        ActivityRepository $activityRepository
    ): Response {
        $user = $this->getUser();

        $favoriteGoals = $goalRepository->createQueryBuilder('g')
            ->where('g.isFavorite = true')
            ->andWhere('g.user = :user')
            ->setParameter('user', $user)
            ->orderBy('g.createdAt', 'DESC')
            ->getQuery()
            ->getResult();

        $favoriteRoutines = $routineRepository->createQueryBuilder('r')
            ->join('r.goal', 'g')
            ->where('r.isFavorite = true')
            ->andWhere('g.user = :user')
            ->setParameter('user', $user)
            ->orderBy('r.createdAt', 'DESC')
            ->getQuery()
            ->getResult();

        $favoriteActivities = $activityRepository->createQueryBuilder('a')
            ->join('a.routine', 'r')
            ->join('r.goal', 'g')
            ->where('a.isFavorite = true')
            ->andWhere('g.user = :user')
            ->setParameter('user', $user)
            ->orderBy('a.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
        
        return $this->render('favorite/index.html.twig', [
            'favoriteGoals' => $favoriteGoals,
            'favoriteRoutines' => $favoriteRoutines,
            'favoriteActivities' => $favoriteActivities,
        ]);
    }
    
    #[Route('/goal/{id}/toggle-favorite', name: 'app_goal_toggle_favorite', methods: ['POST'])]
    public function toggleGoalFavorite(
        Goal $goal,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $goal->setIsFavorite(!$goal->isFavorite());
        $entityManager->flush();
        
        return new JsonResponse([
            'success' => true,
            'isFavorite' => $goal->isFavorite(),
            'message' => $goal->isFavorite() ? 'Ajouté aux favoris' : 'Retiré des favoris'
        ]);
    }
    
    #[Route('/routine/{id}/toggle-favorite', name: 'app_routine_toggle_favorite', methods: ['POST'])]
    public function toggleRoutineFavorite(
        Routine $routine,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $routine->setIsFavorite(!$routine->isFavorite());
        $entityManager->flush();
        
        return new JsonResponse([
            'success' => true,
            'isFavorite' => $routine->isFavorite(),
            'message' => $routine->isFavorite() ? 'Ajouté aux favoris' : 'Retiré des favoris'
        ]);
    }
    
    #[Route('/activity/{id}/toggle-favorite', name: 'app_activity_toggle_favorite', methods: ['POST'])]
    public function toggleActivityFavorite(
        Activity $activity,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $activity->setIsFavorite(!$activity->isFavorite());
        $entityManager->flush();
        
        return new JsonResponse([
            'success' => true,
            'isFavorite' => $activity->isFavorite(),
            'message' => $activity->isFavorite() ? 'Ajouté aux favoris' : 'Retiré des favoris'
        ]);
    }
}
