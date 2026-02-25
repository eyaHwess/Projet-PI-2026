<?php

namespace App\Controller;

use App\Entity\Goal;
use App\Entity\Suggestion;
use App\Entity\User;
use App\Form\GoalType;
use App\Repository\GoalRepository;
use App\Repository\SuggestionRepository;
use App\Repository\UserRepository;
use App\Service\AiAssistantService;
use App\Service\ChartService;
use App\Service\StatusManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Knp\Component\Pager\PaginatorInterface;

#[Route('/goals', name: 'app_goal_')]
#[IsGranted('ROLE_USER')]
class GoalController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private GoalRepository $goalRepository,
        private SuggestionRepository $suggestionRepository,
        private UserRepository $userRepository,
        private UserPasswordHasherInterface $passwordHasher,
        private StatusManager $statusManager,
        private AiAssistantService $aiAssistantService,
        private ChartService $chartService,
    ) {
    }

    // 👇 INDEX — objectifs de l'utilisateur connecté uniquement
    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(Request $request, PaginatorInterface $paginator): Response
    {
        $user = $this->getUser();
        $sortBy = $request->query->get('sort', 'createdAt');
        $sortOrder = $request->query->get('order', 'DESC');
        $filterStatus = $request->query->get('status', 'all');
        $searchQuery = $request->query->get('search', '');

        $queryBuilder = $this->goalRepository->createQueryBuilder('g')
            ->andWhere('g.user = :user')
            ->setParameter('user', $user);

        if (!empty($searchQuery)) {
            $queryBuilder->andWhere('g.title LIKE :search OR g.description LIKE :search')
                ->setParameter('search', '%' . $searchQuery . '%');
        }

        if ($filterStatus !== 'all') {
            $queryBuilder->andWhere('g.status = :status')
                ->setParameter('status', $filterStatus);
        }

        $validSortFields = ['createdAt', 'startDate', 'endDate', 'title', 'priority', 'deadline'];
        if ($sortBy !== 'urgency' && in_array($sortBy, $validSortFields)) {
            $queryBuilder->orderBy('g.' . $sortBy, $sortOrder);
        } else {
            $queryBuilder->orderBy('g.createdAt', 'DESC');
        }

        $pagination = $paginator->paginate(
            $queryBuilder,
            $request->query->getInt('page', 1),
            4
        );

        foreach ($pagination as $goal) {
            $this->statusManager->updateGoalStatuses($goal);
        }

        $overviewChart = $this->chartService->createUserOverviewChart($this->getUser());

        // All goals of user (for accurate status counts, not just paginated page)
        $allUserGoals = $this->goalRepository->findBy(['user' => $this->getUser()]);
        $allActiveCount     = count(array_filter($allUserGoals, fn ($g) => $g->getStatus() === 'active'));
        $allCompletedCount  = count(array_filter($allUserGoals, fn ($g) => $g->getStatus() === 'completed'));
        $allPausedCount     = count(array_filter($allUserGoals, fn ($g) => $g->getStatus() === 'paused'));
        $allFailedCount     = count(array_filter($allUserGoals, fn ($g) => $g->getStatus() === 'failed'));

        return $this->render('goal/index_modern.html.twig', [
            'pagination' => $pagination,
            'currentSort' => $sortBy,
            'currentOrder' => $sortOrder,
            'currentStatus' => $filterStatus,
            'searchQuery' => $searchQuery,
            'overviewChart' => $overviewChart,
            'allActiveCount' => $allActiveCount,
            'allCompletedCount' => $allCompletedCount,
            'allPausedCount' => $allPausedCount,
            'allFailedCount' => $allFailedCount,
        ]);
    }

    // 👇 CREATE
    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request): JsonResponse|Response
    {
        $goal = new Goal();
        $form = $this->createForm(GoalType::class, $goal);
        $form->handleRequest($request);

        $isAjax = $request->isXmlHttpRequest()
            || $request->headers->get('X-Requested-With') === 'XMLHttpRequest'
            || str_contains($request->headers->get('Accept', ''), 'application/json');

        if ($form->isSubmitted() && $form->isValid()) {
            $goal->setUser($this->getUser());

            $this->entityManager->persist($goal);
            $this->entityManager->flush();

            if ($isAjax) {
                return new JsonResponse([
                    'success' => true,
                    'message' => 'Objectif créé avec succès !',
                    'goal' => [
                        'id' => $goal->getId(),
                        'title' => $goal->getTitle(),
                        'description' => $goal->getDescription(),
                        'status' => $goal->getStatus(),
                        'startDate' => $goal->getStartDate()?->format('Y-m-d'),
                        'endDate' => $goal->getEndDate()?->format('Y-m-d'),
                    ]
                ], 201);
            }

            return $this->redirectToRoute('app_goal_index');
        }

        $ai = $this->getAiSuggestionsForNewGoal($request);

        return $this->render('goal/new.html.twig', [
            'form' => $form,
            'goal' => $goal,
            'aiSuggestions' => $ai['aiSuggestions'],
            'aiUnavailable' => $ai['aiUnavailable'],
            'aiUserStats' => $ai['aiUserStats'],
        ]);
    }

    /**
     * Suggestions IA pour la page "Nouvel objectif" (objectifs similaires à ceux de l'utilisateur).
     */
    private function getAiSuggestionsForNewGoal(Request $request): array
    {
        $user = $this->getUser();
        $allGoals = $this->goalRepository->findBy(['user' => $user], ['createdAt' => 'DESC']);
        $totalGoals = \count($allGoals);
        $completedGoals = \count(array_filter($allGoals, fn (Goal $g) => $g->getStatus() === 'completed'));
        $now = new \DateTimeImmutable('today');
        $overdueGoals = \count(array_filter($allGoals, function (Goal $g) use ($now) {
            if ($g->getStatus() !== 'active' && $g->getStatus() !== 'paused') {
                return false;
            }
            $d = $g->getDeadline() ?? $g->getEndDate();
            return $d && $d < $now;
        }));
        $completionRate = $totalGoals > 0 ? (int) round($completedGoals / $totalGoals * 100) : 0;
        $userGoalsForAi = array_map(function (Goal $g) {
            return [
                'title' => $g->getTitle(),
                'description' => $g->getDescription() ? mb_substr($g->getDescription(), 0, 200) : null,
            ];
        }, $allGoals);
        $userData = [
            'total_goals' => $totalGoals,
            'completed_goals' => $completedGoals,
            'overdue_goals' => $overdueGoals,
            'completion_rate' => $completionRate,
            'user_goals' => $userGoalsForAi,
        ];
        $locale = $user instanceof User && method_exists($user, 'getPreferredLanguage') ? ($user->getPreferredLanguage() ?? 'fr') : ($request->getLocale() ?: 'fr');

        $aiSuggestions = [];
        $cached = $this->suggestionRepository->findLatestForUser($user, 24);
        if ($cached && $cached->getContent()) {
            $decoded = json_decode($cached->getContent(), true);
            if (\is_array($decoded)) {
                $aiSuggestions = $decoded;
            }
        }
        if ($aiSuggestions === []) {
            $json = $this->aiAssistantService->generateSuggestion($userData, $locale);
            if ($json !== null) {
                $decoded = json_decode($json, true);
                if (\is_array($decoded)) {
                    $aiSuggestions = $decoded;
                    $suggestion = new Suggestion();
                    $suggestion->setUser($user);
                    $suggestion->setContent($json);
                    $suggestion->setType('goal_coaching');
                    $suggestion->setCreatedAt(new \DateTime());
                    $this->entityManager->persist($suggestion);
                    $this->entityManager->flush();
                }
            }
        }

        return [
            'aiSuggestions' => $aiSuggestions,
            'aiUnavailable' => $aiSuggestions === [],
            'aiUserStats' => $userData,
        ];
    }

    // 👇 SHOW
    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Goal $goal): Response
    {
        if ($goal->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Cet objectif ne vous appartient pas.');
        }

        $progressChart = $this->chartService->createGoalProgressChart($goal);
        $burndownChart = $this->chartService->createBurndownChart($goal);

        return $this->render('goal/show.html.twig', [
            'goal' => $goal,
            'progressChart' => $progressChart,
            'burndownChart' => $burndownChart,
        ]);
    }

    // 👇 EDIT
    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Goal $goal): Response
    {
        if ($goal->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('Cet objectif ne vous appartient pas.');
        }
        $form = $this->createForm(GoalType::class, $goal);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            // Add flash message for success feedback
            $this->addFlash('success', 'Objectif modifié avec succès !');

            // Redirect to the goals index page
            return $this->redirectToRoute('app_goal_index');
        }

        return $this->render('goal/edit.html.twig', [
            'form' => $form,
            'goal' => $goal,
        ]);
    }

    // 👇 DUPLICATE
    #[Route('/{id}/duplicate', name: 'duplicate', methods: ['POST'])]
    public function duplicate(Request $request, Goal $goal): JsonResponse
    {
        if ($goal->getUser() !== $this->getUser()) {
            return new JsonResponse(['success' => false, 'message' => 'Accès refusé.'], 403);
        }
        if ($this->isCsrfTokenValid('duplicate' . $goal->getId(), $request->request->get('_token'))) {
            $duplicatedGoal = new Goal();
            $duplicatedGoal->setTitle($goal->getTitle() . ' (Copie)');
            $duplicatedGoal->setDescription($goal->getDescription());
            $duplicatedGoal->setStartDate(clone $goal->getStartDate());
            $duplicatedGoal->setEndDate(clone $goal->getEndDate());
            $duplicatedGoal->setStatus('active');
            $duplicatedGoal->setUser($this->getUser());
            $duplicatedGoal->setPriority($goal->getPriority());
            if ($goal->getDeadline()) {
                $duplicatedGoal->setDeadline(clone $goal->getDeadline());
            }

            $this->entityManager->persist($duplicatedGoal);
            $this->entityManager->flush();

            return new JsonResponse([
                'success' => true,
                'message' => 'Objectif dupliqué avec succès !',
                'goal' => [
                    'id' => $duplicatedGoal->getId(),
                    'title' => $duplicatedGoal->getTitle(),
                ]
            ]);
        }

        return new JsonResponse([
            'success' => false,
            'message' => 'Token CSRF invalide'
        ], 403);
    }

    // 👇 DELETE
    #[Route('/{id}', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, Goal $goal): JsonResponse
    {
        if ($goal->getUser() !== $this->getUser()) {
            return new JsonResponse(['success' => false, 'message' => 'Accès refusé.'], 403);
        }
        if ($this->isCsrfTokenValid('delete' . $goal->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($goal);
            $this->entityManager->flush();

            return new JsonResponse([
                'success' => true,
                'message' => 'Objectif supprimé avec succès !'
            ]);
        }

        return new JsonResponse([
            'success' => false,
            'message' => 'Token CSRF invalide'
        ], 403);
    }
    
    
}
