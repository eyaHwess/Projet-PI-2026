<?php

namespace App\Controller;

use App\Entity\Goal;
use App\Entity\User;
use App\Form\GoalType;
use App\Repository\GoalRepository;
use App\Repository\UserRepository;
use App\Service\StatusManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Knp\Component\Pager\PaginatorInterface;
use App\Service\AiAssistantService;
use App\Repository\SuggestionRepository;
use App\Entity\Suggestion;


#[Route('/goals', name: 'app_goal_')]
class GoalController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private GoalRepository $goalRepository,
        private UserRepository $userRepository,
        private UserPasswordHasherInterface $passwordHasher,
        private StatusManager $statusManager,
        private AiAssistantService $aiAssistantService,
        private SuggestionRepository $suggestionRepository
    ) {
        
    }

    // 👇 USER STATIQUE
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
#[Route('/test-ai', name: 'test_ai')]
public function testAi(): Response
{
    $userData = [
        'total_goals' => 5,
        'completed_goals' => 2,
        'overdue_goals' => 1,
        'completion_rate' => 40
    ];

    try {
        $response = $this->aiAssistantService->generateSuggestion($userData);
        return new Response('<pre>' . $response . '</pre>');
    } catch (\Exception $e) {
        return new Response('Error: ' . $e->getMessage());
    }
}
    // 👇 INDEX
#[Route('/', name: 'index', methods: ['GET'])]
public function index(Request $request, PaginatorInterface $paginator): Response
{
    $user = $this->getStaticUser();

    // ===== EXISTING PAGINATION LOGIC =====
    $sortBy = $request->query->get('sort', 'createdAt');
    $sortOrder = $request->query->get('order', 'DESC');
    $filterStatus = $request->query->get('status', 'all');
    $searchQuery = $request->query->get('search', '');

    $queryBuilder = $this->goalRepository->createQueryBuilder('g')
        ->where('g.user = :user')
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

    // ===== AI SECTION STARTS HERE =====

    $goals = $this->goalRepository->findBy(['user' => $user]);

    $totalGoals = count($goals);
    $completedGoals = 0;
    $overdueGoals = 0;

    foreach ($goals as $goal) {
        if ($goal->getStatus() === 'completed') {
            $completedGoals++;
        }

        if ($goal->getStatus() !== 'completed' && $goal->getDeadline() && $goal->getDeadline() < new \DateTime()) {
            $overdueGoals++;
        }
    }

    $completionRate = $totalGoals > 0
        ? round(($completedGoals / $totalGoals) * 100)
        : 0;

    $userData = [
        'total_goals' => $totalGoals,
        'completed_goals' => $completedGoals,
        'overdue_goals' => $overdueGoals,
        'completion_rate' => $completionRate
    ];

    // Check if suggestion exists today
    $latestSuggestion = $this->suggestionRepository->findOneBy(
        ['user' => $user],
        ['createdAt' => 'DESC']
    );

    // Only generate new suggestion if:
    // 1. No suggestion exists, OR
    // 2. Latest suggestion is older than 1 day
    $shouldGenerateNew = !$latestSuggestion || 
        $latestSuggestion->getCreatedAt() < new \DateTime('-1 day');

    $aiText = null;

    if ($shouldGenerateNew) {
        try {
            $aiText = $this->aiAssistantService->generateSuggestion($userData);

            // Only save if we got a valid suggestion (not null)
            if ($aiText !== null) {
                $suggestion = new Suggestion();
                $suggestion->setUser($user);
                $suggestion->setContent($aiText);
                $suggestion->setCreatedAt(new \DateTime());

                $this->entityManager->persist($suggestion);
                $this->entityManager->flush();
            } else {
                // AI generation failed (rate limit, network error, etc.)
                // Show a friendly fallback message without saving to DB
                $aiText = "🤖 **Coaching Temporairement Indisponible**\n\nNous avons atteint la limite de requêtes API. Votre suggestion personnalisée sera générée dans quelques instants.\n\n💡 **En attendant**: Continuez votre excellent travail ! Vous avez {$completedGoals} objectifs complétés sur {$totalGoals} ({$completionRate}%).\n\n✨ Revenez dans quelques minutes pour votre coaching personnalisé.";
            }

        } catch (\Exception $e) {
            // If AI generation throws an exception, use a fallback message
            $aiText = "🎯 **Continuez votre excellent travail !**\n\nVous avez {$completedGoals} objectifs complétés sur {$totalGoals}.\n\n💪 Restez concentré sur vos priorités et vous atteindrez vos objectifs !";
        }
    } else {
        $aiText = $latestSuggestion->getContent();
    }

    // Calculate statistics for all goals (not just current page)
    $allGoals = $this->goalRepository->findBy(['user' => $user]);
    $statsActiveCount = 0;
    $statsCompletedCount = 0;
    $statsPausedCount = 0;
    $statsFailedCount = 0;
    
    foreach ($allGoals as $goal) {
        switch ($goal->getStatus()) {
            case 'active':
                $statsActiveCount++;
                break;
            case 'completed':
                $statsCompletedCount++;
                break;
            case 'paused':
                $statsPausedCount++;
                break;
            case 'failed':
                $statsFailedCount++;
                break;
        }
    }

    // Decode JSON if it's a valid JSON string
    $aiSuggestions = null;
    if ($aiText) {
        $decoded = json_decode($aiText, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            $aiSuggestions = $decoded;
        }
    }

    return $this->render('goal/index_modern.html.twig', [
        'pagination' => $pagination,
        'currentSort' => $sortBy,
        'currentOrder' => $sortOrder,
        'currentStatus' => $filterStatus,
        'searchQuery' => $searchQuery,
        'aiSuggestion' => $aiText,
        'aiSuggestions' => $aiSuggestions,
        'statsActiveCount' => $statsActiveCount,
        'statsCompletedCount' => $statsCompletedCount,
        'statsPausedCount' => $statsPausedCount,
        'statsFailedCount' => $statsFailedCount,
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

            // Associer toujours le user statique
            $goal->setUser($this->getStaticUser());

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

        return $this->render('goal/new.html.twig', [
            'form' => $form,
            'goal' => $goal,
        ]);
    }
    #[Route('/new-from-suggestion', name: 'new_from_suggestion', methods: ['GET', 'POST'])]
    public function newFromSuggestion(Request $request): JsonResponse|Response
    {
        $goal = new Goal();

        // Pre-fill from suggestion data if provided
        if ($request->query->has('title')) {
            $goal->setTitle($request->query->get('title'));
        }
        if ($request->query->has('description')) {
            $goal->setDescription($request->query->get('description'));
        }
        if ($request->query->has('priority')) {
            $goal->setPriority($request->query->get('priority'));
        }
        if ($request->query->has('duration_days')) {
            $durationDays = (int) $request->query->get('duration_days');
            $startDate = new \DateTime();
            $endDate = (clone $startDate)->modify("+{$durationDays} days");
            $goal->setStartDate($startDate);
            $goal->setEndDate($endDate);
            $goal->setDeadline($endDate);
        }

        // Set default status
        $goal->setStatus('draft');

        $form = $this->createForm(GoalType::class, $goal);
        $form->handleRequest($request);

        $isAjax = $request->isXmlHttpRequest()
            || $request->headers->get('X-Requested-With') === 'XMLHttpRequest'
            || str_contains($request->headers->get('Accept', ''), 'application/json');

        if ($form->isSubmitted() && $form->isValid()) {
            // Associate with static user
            $goal->setUser($this->getStaticUser());

            $this->entityManager->persist($goal);
            $this->entityManager->flush();

            if ($isAjax) {
                return new JsonResponse([
                    'success' => true,
                    'message' => 'Objectif créé avec succès à partir de la suggestion IA !',
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

        return $this->render('goal/new.html.twig', [
            'form' => $form,
            'goal' => $goal,
            'fromSuggestion' => true,
        ]);
    }


    // 👇 SHOW
    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Goal $goal): Response
    {
        return $this->render('goal/show.html.twig', [
            'goal' => $goal,
        ]);
    }

    // 👇 EDIT
    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Goal $goal): Response
    {
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
        if ($this->isCsrfTokenValid('duplicate' . $goal->getId(), $request->request->get('_token'))) {
            // Create a new goal with the same properties
            $duplicatedGoal = new Goal();
            $duplicatedGoal->setTitle($goal->getTitle() . ' (Copie)');
            $duplicatedGoal->setDescription($goal->getDescription());
            $duplicatedGoal->setStartDate(clone $goal->getStartDate());
            $duplicatedGoal->setEndDate(clone $goal->getEndDate());
            $duplicatedGoal->setStatus('active'); // Reset to active
            $duplicatedGoal->setUser($goal->getUser());
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
