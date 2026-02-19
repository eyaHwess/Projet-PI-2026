<?php

namespace App\Controller;

use App\Repository\CoachingRequestRepository;
use App\Repository\GoalRepository;
use App\Repository\RoutineRepository;
use App\Repository\SessionRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
final class AdminController extends AbstractController
{
    public function __construct(
        private UserRepository $userRepository,
        private CoachingRequestRepository $coachingRequestRepository,
        private SessionRepository $sessionRepository,
        private GoalRepository $goalRepository,
        private RoutineRepository $routineRepository
    ) {
    }

    #[Route('/admin', name: 'admin_dashboard')]
    public function index(): Response
    {
        // Statistiques générales
        $totalUsers = $this->userRepository->count([]);
        $totalCoaches = count($this->userRepository->findCoaches());
        $totalRequests = $this->coachingRequestRepository->count([]);
        $totalSessions = $this->sessionRepository->count([]);
        $totalGoals = $this->goalRepository->count([]);
        $totalRoutines = $this->routineRepository->count([]);

        // Demandes par statut
        $pendingRequests = $this->coachingRequestRepository->count(['status' => 'pending']);
        $acceptedRequests = $this->coachingRequestRepository->count(['status' => 'accepted']);
        $declinedRequests = $this->coachingRequestRepository->count(['status' => 'declined']);

        // Sessions par statut
        $schedulingSessions = $this->sessionRepository->count(['status' => 'scheduling']);
        $confirmedSessions = $this->sessionRepository->count(['status' => 'confirmed']);
        $completedSessions = $this->sessionRepository->count(['status' => 'completed']);
        $cancelledSessions = $this->sessionRepository->count(['status' => 'cancelled']);

        // Utilisateurs récents (7 derniers jours)
        $recentUsers = $this->userRepository->createQueryBuilder('u')
            ->where('u.createdAt >= :date')
            ->setParameter('date', new \DateTime('-7 days'))
            ->orderBy('u.createdAt', 'DESC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();

        // Demandes récentes
        $recentRequests = $this->coachingRequestRepository->createQueryBuilder('cr')
            ->orderBy('cr.createdAt', 'DESC')
            ->setMaxResults(5)
            ->getQuery()
            ->getResult();

        // Sessions récentes
        $recentSessions = $this->sessionRepository->createQueryBuilder('s')
            ->orderBy('s.createdAt', 'DESC')
            ->setMaxResults(5)
            ->getQuery()
            ->getResult();

        // Données pour graphiques (7 derniers jours)
        $chartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = new \DateTime("-{$i} days");
            $dateStart = clone $date;
            $dateStart->setTime(0, 0, 0);
            $dateEnd = clone $date;
            $dateEnd->setTime(23, 59, 59);

            $chartData['labels'][] = $date->format('d/m');
            $chartData['users'][] = $this->userRepository->createQueryBuilder('u')
                ->select('COUNT(u.id)')
                ->where('u.createdAt >= :start AND u.createdAt <= :end')
                ->setParameter('start', $dateStart)
                ->setParameter('end', $dateEnd)
                ->getQuery()
                ->getSingleScalarResult();
            $chartData['sessions'][] = $this->sessionRepository->createQueryBuilder('s')
                ->select('COUNT(s.id)')
                ->where('s.createdAt >= :start AND s.createdAt <= :end')
                ->setParameter('start', $dateStart)
                ->setParameter('end', $dateEnd)
                ->getQuery()
                ->getSingleScalarResult();
            $chartData['requests'][] = $this->coachingRequestRepository->createQueryBuilder('cr')
                ->select('COUNT(cr.id)')
                ->where('cr.createdAt >= :start AND cr.createdAt <= :end')
                ->setParameter('start', $dateStart)
                ->setParameter('end', $dateEnd)
                ->getQuery()
                ->getSingleScalarResult();
        }

        return $this->render('admin/dashboard/dashboard.html.twig', [
            'totalUsers' => $totalUsers,
            'totalCoaches' => $totalCoaches,
            'totalRequests' => $totalRequests,
            'totalSessions' => $totalSessions,
            'totalGoals' => $totalGoals,
            'totalRoutines' => $totalRoutines,
            'pendingRequests' => $pendingRequests,
            'acceptedRequests' => $acceptedRequests,
            'declinedRequests' => $declinedRequests,
            'schedulingSessions' => $schedulingSessions,
            'confirmedSessions' => $confirmedSessions,
            'completedSessions' => $completedSessions,
            'cancelledSessions' => $cancelledSessions,
            'recentUsers' => $recentUsers,
            'recentRequests' => $recentRequests,
            'recentSessions' => $recentSessions,
            'chartData' => $chartData,
        ]);
    }
    #[Route('/admin/users', name: 'admin_user_list')]
    public function userList(Request $request): Response
    {
        $name  = trim((string) $request->query->get('name', ''));
        $email = trim((string) $request->query->get('email', ''));

        $qb = $this->userRepository->createQueryBuilder('u')
            ->orderBy('u.lastName', 'ASC')
            ->addOrderBy('u.firstName', 'ASC');

        if ($name !== '') {
            $qb
                ->andWhere('LOWER(u.firstName) LIKE :name OR LOWER(u.lastName) LIKE :name')
                ->setParameter('name', '%' . mb_strtolower($name) . '%');
        }

        if ($email !== '') {
            $qb
                ->andWhere('LOWER(u.email) LIKE :email')
                ->setParameter('email', '%' . mb_strtolower($email) . '%');
        }

        $allUsers = $qb->getQuery()->getResult();
        $users = [];
        foreach ($allUsers as $u) {
            $roles = $u->getRoles();
            $role = in_array('ROLE_ADMIN', $roles, true) ? 'admin' : (in_array('ROLE_COACH', $roles, true) ? 'coach' : 'user');
            $users[] = [
                'name' => trim($u->getFirstName() . ' ' . $u->getLastName()) ?: $u->getEmail(),
                'role' => $role,
                'status' => 'Active',
                'streakDays' => '0',
                'routines' => $this->routineRepository->countByUser($u),
                'avatar' => null,
                'email' => $u->getEmail(),
            ];
            if ($role === 'admin') {
                $users[array_key_last($users)]['roleLevel'] = 5;
            }
            if ($role === 'coach') {
                $users[array_key_last($users)]['speciality'] = $u->getSpeciality() ?? '-';
                $users[array_key_last($users)]['totalSessions'] = $this->sessionRepository->countByCoach($u);
                $users[array_key_last($users)]['totalDemandes'] = $this->coachingRequestRepository->countByCoach($u);
                $users[array_key_last($users)]['rating'] = 4;
            }
        }
        return $this->render('admin/user_list.html.twig', [
            'users' => $users,
            'filter_name' => $name,
            'filter_email' => $email,
        ]);
    }

    #[Route('/admin/coaches', name: 'coaches_list')]
    public function coachList(Request $request): Response
    {
        $search = trim((string) $request->query->get('q', ''));

        $coaches = $this->userRepository->findCoaches();

        if ($search !== '') {
            $coaches = array_filter($coaches, static function ($coach) use ($search) {
                $fullName = trim($coach->getFirstName() . ' ' . $coach->getLastName());
                $haystack = mb_strtolower($fullName . ' ' . $coach->getEmail() . ' ' . (string) $coach->getSpeciality());
                return str_contains($haystack, mb_strtolower($search));
            });
        }

        $users = [];
        foreach ($coaches as $u) {
            $users[] = [
                'name' => trim($u->getFirstName() . ' ' . $u->getLastName()) ?: $u->getEmail(),
                'role' => 'coach',
                'speciality' => $u->getSpeciality() ?? '-',
                'totalSessions' => $this->sessionRepository->countByCoach($u),
                'totalDemandes' => $this->coachingRequestRepository->countByCoach($u),
                'rating' => 4,
                'avatar' => null,
            ];
        }
        return $this->render('admin/coaches_list.html.twig', [
            'users' => $users,
            'search' => $search,
        ]);
    }
    #[Route('/admin/manageUsers', name: 'admin_manage_accounts')]
    public function manageAccounts(Request $request): Response
    {
        $currentPage = max(1, $request->query->getInt('page', 1));
        $itemsPerPage = 10;
        $totalItems = $this->userRepository->count([]);
        $totalPages = $totalItems > 0 ? (int) ceil($totalItems / $itemsPerPage) : 1;
        $offset = ($currentPage - 1) * $itemsPerPage;

        $userEntities = $this->userRepository->createQueryBuilder('u')
            ->orderBy('u.lastName', 'ASC')
            ->addOrderBy('u.firstName', 'ASC')
            ->setFirstResult($offset)
            ->setMaxResults($itemsPerPage)
            ->getQuery()
            ->getResult();

        $users = [];
        foreach ($userEntities as $u) {
            $firstGoal = $this->goalRepository->findFirstByUser($u);
            $users[] = [
                'name' => trim($u->getFirstName() . ' ' . $u->getLastName()) ?: $u->getEmail(),
                'goal' => $firstGoal ? $firstGoal->getTitle() : 'Aucun objectif',
                'routines' => $this->routineRepository->countByUser($u),
                'streakDays' => '0',
                'avatar' => null,
                'id' => $u->getId(),
            ];
        }

        return $this->render('admin/manage_accounts.html.twig', [
            'users' => $users,
            'currentPage' => $currentPage,
            'totalPages' => $totalPages,
            'totalItems' => $totalItems,
            'itemsPerPage' => $itemsPerPage,
            'startItem' => $totalItems === 0 ? 0 : $offset + 1,
            'endItem' => min($offset + $itemsPerPage, $totalItems),
        ]);
    }
    #[Route('/admin/userDetail/{id}', name: 'admin_user_detail', requirements: ['id' => '\d+'])]
    public function detail(int $id): Response
    {
        $userEntity = $this->userRepository->find($id);
        if ($userEntity === null) {
            throw $this->createNotFoundException('Utilisateur introuvable.');
        }
        $roles = $userEntity->getRoles();
        $role = in_array('ROLE_ADMIN', $roles, true) ? 'admin' : (in_array('ROLE_COACH', $roles, true) ? 'coach' : 'user');
        $user = [
            'name' => trim($userEntity->getFirstName() . ' ' . $userEntity->getLastName()) ?: $userEntity->getEmail(),
            'role' => $role,
            'status' => 'Active',
            'email' => $userEntity->getEmail(),
            'createdAt' => $userEntity->getCreatedAt() ? $userEntity->getCreatedAt()->format('d M Y') : '-',
            'description' => 'Utilisateur de la plateforme.',
            'avatar' => null,
        ];
        if ($role === 'coach') {
            $user['speciality'] = $userEntity->getSpeciality() ?? '-';
        }
        return $this->render('admin/user_detail.html.twig', ['user' => $user]);
    }

    #[Route('/admin/coach/requests', name: 'admin_coach_requests')]
    public function coachRequests(Request $request): Response
    {
        $currentPage = max(1, $request->query->getInt('page', 1));
        $itemsPerPage = 10;
        $totalItems = $this->coachingRequestRepository->countAll();
        $totalPages = $totalItems > 0 ? (int) ceil($totalItems / $itemsPerPage) : 1;
        $offset = ($currentPage - 1) * $itemsPerPage;

        $coachRequests = $this->coachingRequestRepository->findAllOrdered($itemsPerPage, $offset);

        return $this->render('admin/components/Coach/coachRequests.html.twig', [
            'coachRequests' => $coachRequests,
            'currentPage' => $currentPage,
            'totalPages' => $totalPages,
            'totalItems' => $totalItems,
            'itemsPerPage' => $itemsPerPage,
            'startItem' => $totalItems === 0 ? 0 : $offset + 1,
            'endItem' => min($offset + $itemsPerPage, $totalItems),
        ]);
    }

    #[Route('/admin/coach/request/{id}', name: 'admin_coach_request_view', requirements: ['id' => '\d+'])]
    public function viewCoachRequest(int $id): Response
    {
        $requestEntity = $this->coachingRequestRepository->find($id);
        if ($requestEntity === null) {
            throw $this->createNotFoundException('Demande introuvable.');
        }
        return $this->render('admin/components/Coach/coach_request_detail.html.twig', [
            'request' => $requestEntity,
        ]);
    }

    #[Route('/admin/claims', name: 'admin_claims')]
    public function claims(Request $request): Response
    {
        // Pas d'entité Réclamation en BDD : liste vide (à brancher quand l'entité existera)
        $currentPage = 1;
        $totalItems = 0;
        $totalPages = 1;
        $itemsPerPage = 10;
        return $this->render('admin/components/reclamation/claims.html.twig', [
            'claims' => [],
            'currentPage' => $currentPage,
            'totalPages' => $totalPages,
            'totalItems' => $totalItems,
            'itemsPerPage' => $itemsPerPage,
            'startItem' => 0,
            'endItem' => 0,
        ]);
    }


    #[Route('/admin/statistics/users', name: 'admin_user_stats')]
    public function userStats(): Response
    {
        $totalUsers = $this->userRepository->count([]);
        $totalRoutines = $this->routineRepository->count([]);
        $coaches = $this->userRepository->findCoaches();

        $now = new \DateTimeImmutable();
        $weekAgo = $now->modify('-7 days');
        $twoWeeksAgo = $now->modify('-14 days');
        $monthAgo = $now->modify('-30 days');
        $twoMonthsAgo = $now->modify('-60 days');

        $newUsersWeek = (int) $this->userRepository->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->where('u.createdAt >= :since')
            ->setParameter('since', $weekAgo)
            ->getQuery()->getSingleScalarResult();
        $usersPreviousWeek = (int) $this->userRepository->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->where('u.createdAt >= :since')->andWhere('u.createdAt < :until')
            ->setParameter('since', $twoWeeksAgo)->setParameter('until', $weekAgo)
            ->getQuery()->getSingleScalarResult();
        $newUsersMonth = (int) $this->userRepository->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->where('u.createdAt >= :since')
            ->setParameter('since', $monthAgo)
            ->getQuery()->getSingleScalarResult();
        $usersPreviousMonth = (int) $this->userRepository->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->where('u.createdAt >= :since')->andWhere('u.createdAt < :until')
            ->setParameter('since', $twoMonthsAgo)->setParameter('until', $monthAgo)
            ->getQuery()->getSingleScalarResult();

        $weekGrowth = $usersPreviousWeek > 0 ? (int) round(($newUsersWeek - $usersPreviousWeek) / $usersPreviousWeek * 100) : ($newUsersWeek > 0 ? 100 : 0);
        $monthGrowth = $usersPreviousMonth > 0 ? (int) round(($newUsersMonth - $usersPreviousMonth) / $usersPreviousMonth * 100) : ($newUsersMonth > 0 ? 100 : 0);

        $coachNames = [];
        $coachSessions = [];
        foreach (array_slice($coaches, 0, 5) as $c) {
            $coachNames[] = trim($c->getFirstName() . ' ' . $c->getLastName()) ?: $c->getEmail();
            $coachSessions[] = $this->sessionRepository->countByCoach($c);
        }

        $engagementLabels = [];
        $engagementUsers = [];
        for ($i = 6; $i >= 0; $i--) {
            $d = $now->modify("-{$i} days");
            $start = $d->setTime(0, 0, 0);
            $end = $d->setTime(23, 59, 59);
            $engagementLabels[] = $start->format('D');
            $engagementUsers[] = (int) $this->userRepository->createQueryBuilder('u')
                ->select('COUNT(u.id)')
                ->where('u.createdAt >= :start AND u.createdAt <= :end')
                ->setParameter('start', $start)->setParameter('end', $end)
                ->getQuery()->getSingleScalarResult();
        }

        return $this->render('admin/statistics/user_stats.html.twig', [
            'totalUsers' => $totalUsers,
            'weekGrowth' => $weekGrowth,
            'monthGrowth' => $monthGrowth,
            'newUsersWeek' => $newUsersWeek,
            'totalRoutines' => $totalRoutines,
            'routinesByGoal' => [
                'fitness' => $totalRoutines,
                'education' => 0,
                'productivity' => 0,
                'wellness' => 0,
            ],
            'completionRate' => 0,
            'dailyCompleted' => 0,
            'activeStreaks' => 0,
            'engagementData' => [
                'dates' => $engagementLabels,
                'activeUsers' => $engagementUsers,
                'completions' => $engagementUsers,
                'streaks' => $engagementUsers,
            ],
            'coachData' => [
                'coaches' => $coachNames ?: ['-'],
                'sessions' => $coachSessions ?: [0],
                'ratings' => array_fill(0, count($coachNames) ?: 1, 4),
            ],
            'routinesData' => [
                'categories' => ['Routines'],
                'values' => [$totalRoutines],
            ],
        ]);
    }



    #[Route('/admin/statistics/global', name: 'admin_global_stats')]
    public function globalStats(): Response
    {
        $totalUsers = $this->userRepository->count([]);
        $totalCoaches = count($this->userRepository->findCoaches());
        $totalSessions = $this->sessionRepository->count([]);
        $totalRoutines = $this->routineRepository->count([]);

        $now = new \DateTimeImmutable();
        $monthAgo = $now->modify('-30 days');
        $twoMonthsAgo = $now->modify('-60 days');
        $yearAgo = $now->modify('-1 year');

        $newUsersMonth = (int) $this->userRepository->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->where('u.createdAt >= :since')
            ->setParameter('since', $monthAgo)
            ->getQuery()->getSingleScalarResult();
        $usersPreviousMonth = (int) $this->userRepository->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->where('u.createdAt >= :since')->andWhere('u.createdAt < :until')
            ->setParameter('since', $twoMonthsAgo)->setParameter('until', $monthAgo)
            ->getQuery()->getSingleScalarResult();
        $usersLastYear = (int) $this->userRepository->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->where('u.createdAt < :since')
            ->setParameter('since', $yearAgo)
            ->getQuery()->getSingleScalarResult();

        $monthGrowth = $usersPreviousMonth > 0 ? (int) round(($newUsersMonth - $usersPreviousMonth) / $usersPreviousMonth * 100) : ($newUsersMonth > 0 ? 100 : 0);
        $yearGrowth = $usersLastYear > 0 ? (int) round(($totalUsers - $usersLastYear) / $usersLastYear * 100) : ($totalUsers > 0 ? 100 : 0);

        $growthData = ['months' => [], 'users' => [], 'coaches' => [], 'routines' => []];
        $coaches = $this->userRepository->findCoaches();
        for ($i = 11; $i >= 0; $i--) {
            $monthStart = (clone $now)->modify("first day of -{$i} months")->setTime(0, 0, 0);
            $monthEnd = (clone $monthStart)->modify('last day of this month')->setTime(23, 59, 59);
            $growthData['months'][] = $monthStart->format('M');
            $growthData['users'][] = (int) $this->userRepository->createQueryBuilder('u')
                ->select('COUNT(u.id)')
                ->where('u.createdAt >= :start AND u.createdAt <= :end')
                ->setParameter('start', $monthStart)->setParameter('end', $monthEnd)
                ->getQuery()->getSingleScalarResult();
            $coachCount = 0;
            foreach ($coaches as $c) {
                $ca = $c->getCreatedAt();
                if ($ca && $ca >= $monthStart && $ca <= $monthEnd) {
                    $coachCount++;
                }
            }
            $growthData['coaches'][] = $coachCount;
            $growthData['routines'][] = (int) $this->routineRepository->createQueryBuilder('r')
                ->select('COUNT(r.id)')
                ->where('r.createdAt >= :start AND r.createdAt <= :end')
                ->setParameter('start', $monthStart)->setParameter('end', $monthEnd)
                ->getQuery()->getSingleScalarResult();
        }

        return $this->render('admin/statistics/global_stats.html.twig', [
            'totalUsers' => $totalUsers,
            'monthGrowth' => $monthGrowth,
            'yearGrowth' => $yearGrowth,
            'newUsersMonth' => $newUsersMonth,
            'totalCoaches' => $totalCoaches,
            'averageRating' => 4.7,
            'totalSessions' => $totalSessions,
            'totalRoutines' => $totalRoutines,
            'activeRoutines' => $totalRoutines,
            'completedRoutines' => 0,
            'newRoutinesWeek' => 0,
            'activeSessions' => $totalSessions,
            'scheduledToday' => 0,
            'completedToday' => 0,
            'contentStats' => [
                'posts' => 0,
                'comments' => 0,
                'chatrooms' => 0,
                'claims' => 0,
            ],
            'growthData' => $growthData,
            'forecastData' => [
                'labels' => array_slice($growthData['months'], -7),
                'historical' => array_slice($growthData['users'], -7),
                'predicted' => [],
                'predictedGrowth' => 0,
                'confidence' => 0,
                'expectedUsers' => $totalUsers,
                'currentMonthIndex' => 6,
            ],
            'churnData' => [
                'riskPercentage' => 0,
                'usersAtRisk' => 0,
                'factors' => [
                    'lowEngagement' => 0,
                    'inactiveRoutines' => 0,
                    'noCoachInteraction' => 0,
                ],
            ],
        ]);
    }
}