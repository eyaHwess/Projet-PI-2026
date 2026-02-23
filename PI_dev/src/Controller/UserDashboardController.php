<?php

namespace App\Controller;

use App\Repository\GoalRepository;
use App\Repository\RoutineRepository;
use App\Repository\SessionRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\User;

class UserDashboardController extends AbstractController
{
    private UserRepository $userRepository;
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(
        UserRepository $userRepository,
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher
    ) {
        $this->userRepository = $userRepository;
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
    }

    private function getStaticUser(): User
    {
        $user = $this->userRepository->findOneBy(['email' => 'static@example.com']);

        if (!$user) {
            $user = new User();
            $user->setEmail('static@example.com');
            $user->setFirstName('Static');
            $user->setLastName('User');
            $user->setStatus('active');
            $user->setPassword(
                $this->passwordHasher->hashPassword($user, 'password123')
            );

            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }

        return $user;
    }

    #[Route('/user/dashboard', name: 'user_dashboard')]
    public function dashboard(
        GoalRepository $goalRepository,
        RoutineRepository $routineRepository,
        SessionRepository $sessionRepository
    ): Response {
        $user = $this->getStaticUser();

        // Count all goals for the user
        $goalsCount = $goalRepository->count(['user' => $user]);
        
        // Count all routines across all goals for the user
        $routinesCount = $routineRepository->countByUser($user);
        
        // Get recent goals
        $recentGoals = $goalRepository->findBy(
            ['user' => $user],
            ['id' => 'DESC'],
            5
        );

        // Get sessions
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
