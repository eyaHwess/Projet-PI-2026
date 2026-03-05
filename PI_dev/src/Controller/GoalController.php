<?php

namespace App\Controller;

use App\Entity\Chatroom;
use App\Entity\Goal;
use App\Entity\GoalParticipation;
use App\Entity\Message;
use App\Entity\Suggestion;
use App\Entity\User;
use App\Form\GoalType;
use App\GoalHistoryBundle\Service\GoalHistoryLogger;
use App\Repository\ChatroomRepository;
use App\Repository\GoalRepository;
use App\Repository\SuggestionRepository;
use App\Repository\UserRepository;
use App\Service\AiAssistantService;
use App\Service\ChartService;
use App\Service\NotificationService;
use App\Service\StatusManager;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/goals', name: 'app_goal_')]
#[IsGranted('ROLE_USER')]
class GoalController extends AbstractController
{
    private function getCurrentUser(): User
    {
        $user = $this->getUser();
        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }
        return $user;
    }

    public function __construct(
        private ChatroomRepository $chatroomRepository,
        private EntityManagerInterface $entityManager,
        private GoalRepository $goalRepository,
        private SuggestionRepository $suggestionRepository,
        private UserRepository $userRepository,
        private UserPasswordHasherInterface $passwordHasher,
        private StatusManager $statusManager,
        private AiAssistantService $aiAssistantService,
        private ChartService $chartService,
        private GoalHistoryLogger $goalHistoryLogger,
        private NotificationService $notificationService,
    ) {
    }

    // ── Personal goals list (main branch) ──

    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(Request $request, PaginatorInterface $paginator): Response
    {
        $user = $this->getCurrentUser();
        $sortBy = $request->query->get('sort', 'createdAt');
        $sortOrder = $request->query->get('order', 'DESC');
        $filterStatus = $request->query->get('status', 'all');
        $searchQuery = $request->query->get('search', '');

        $queryBuilder = $this->goalRepository->getQueryBuilderForUserIndex($user, $searchQuery, $filterStatus);

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

        $this->goalRepository->hydrateRoutinesAndActivitiesForGoals($pagination);

        $countsByStatus = $this->goalRepository->countByUserAndStatus($user);
        $overviewChart = $this->chartService->createUserOverviewChartFromCounts($countsByStatus);
        $allActiveCount    = $countsByStatus['active'];
        $allCompletedCount = $countsByStatus['completed'];
        $allPausedCount    = $countsByStatus['paused'];
        $allFailedCount    = $countsByStatus['failed'];

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

    // ── Community goals list (mariem branch) ──

    #[Route('/community', name: 'community', methods: ['GET'])]
    public function community(GoalRepository $goalRepository): Response
    {
        return $this->render('goal/list.html.twig', [
            'goals' => $goalRepository->findGoalsWithParticipants(),
        ]);
    }

    // ── Create ──

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
            $user = $this->getCurrentUser();
            $goal->setUser($user);

            // Auto-create a chatroom for this goal
            $chatroom = new Chatroom();
            $chatroom->setCreatedAt(new \DateTime());
            $chatroom->setGoal($goal);

            // Creator becomes OWNER automatically
            $participation = new GoalParticipation();
            $participation->setGoal($goal);
            $participation->setUser($user);
            $participation->setRole(GoalParticipation::ROLE_OWNER);
            $participation->setStatus(GoalParticipation::STATUS_APPROVED);
            $participation->setCreatedAt(new \DateTime());

            $this->entityManager->persist($goal);
            $this->entityManager->persist($chatroom);
            $this->entityManager->persist($participation);
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

    // ── Show ──

    #[Route('/{id}', name: 'show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(Goal $goal): Response
    {
        if ($goal->getUser() !== $this->getCurrentUser()) {
            throw $this->createAccessDeniedException('Cet objectif ne vous appartient pas.');
        }

        // Auto-create chatroom + owner participation for old goals
        if (!$goal->getChatroom()) {
            $chatroom = new Chatroom();
            $chatroom->setCreatedAt(new \DateTime());
            $chatroom->setGoal($goal);
            $this->entityManager->persist($chatroom);

            $existingParticipation = $this->entityManager
                ->getRepository(GoalParticipation::class)
                ->findOneBy(['user' => $goal->getUser(), 'goal' => $goal]);

            if (!$existingParticipation) {
                $participation = new GoalParticipation();
                $participation->setGoal($goal);
                $participation->setUser($goal->getUser());
                $participation->setRole(GoalParticipation::ROLE_OWNER);
                $participation->setStatus(GoalParticipation::STATUS_APPROVED);
                $participation->setCreatedAt(new \DateTime());
                $this->entityManager->persist($participation);
            }
            $this->entityManager->flush();
        }

        $progressChart = $this->chartService->createGoalProgressChart($goal);
        $burndownChart = $this->chartService->createBurndownChart($goal);

        $goalHistory = $this->goalHistoryLogger->getHistoryForGoal($goal, 30);

        // Calculate raw chart data for direct Chart.js rendering (avoids UX bundle issues)
        $totalRoutines = $goal->getRoutines()->count();
        $completedRoutines = 0;
        foreach ($goal->getRoutines() as $routine) {
            $total = $routine->getActivities()->count();
            if ($total > 0) {
                $completed = 0;
                foreach ($routine->getActivities() as $act) {
                    if ($act->getStatus() === 'completed') { $completed++; }
                }
                if ($completed === $total) { $completedRoutines++; }
            }
        }
        $progressData = [
            'completed' => $completedRoutines,
            'remaining' => max(0, $totalRoutines - $completedRoutines),
        ];

        $totalActivities = 0;
        $completedActivities = 0;
        foreach ($goal->getRoutines() as $routine) {
            foreach ($routine->getActivities() as $act) {
                $totalActivities++;
                if ($act->getStatus() === 'completed') { $completedActivities++; }
            }
        }
        $burndownData = [
            'total' => $totalActivities,
            'completed' => $completedActivities,
            'remaining' => max(0, $totalActivities - $completedActivities),
        ];

        return $this->render('goal/show.html.twig', [
            'goal' => $goal,
            'progressChart' => $progressChart,
            'burndownChart' => $burndownChart,
            'progressData' => $progressData,
            'burndownData' => $burndownData,
            'goalHistory' => $goalHistory,
        ]);
    }

    // ── Edit ──

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function edit(Request $request, Goal $goal): JsonResponse|Response
    {
        if ($goal->getUser() !== $this->getCurrentUser() && !$goal->canUserModifyGoal($this->getCurrentUser())) {
            throw $this->createAccessDeniedException('Vous n\'avez pas la permission de modifier ce goal.');
        }

        // Sauvegarder les valeurs avant modification pour l'historique
        $oldStatus = $goal->getStatus();
        $oldPriority = $goal->getPriority();
        $oldTitle = $goal->getTitle();
        $oldDescription = $goal->getDescription();
        $oldStartDate = $goal->getStartDate()?->format('Y-m-d');
        $oldEndDate = $goal->getEndDate()?->format('Y-m-d');
        $oldDeadline = $goal->getDeadline()?->format('Y-m-d');

        $form = $this->createForm(GoalType::class, $goal);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $this->getCurrentUser();

            // Historique : statut modifié
            $newStatus = $goal->getStatus();
            if ($newStatus !== $oldStatus) {
                $this->goalHistoryLogger->logStatusChanged($goal, (string) $oldStatus, (string) $newStatus, $user);
            }

            // Historique : priorité modifiée
            $newPriority = $goal->getPriority();
            if ($newPriority !== $oldPriority) {
                $this->goalHistoryLogger->logPriorityChanged($goal, $oldPriority, $newPriority, $user);
            }

            // Historique : autres champs modifiés (titre, description, dates)
            $changedFields = [];
            if ($goal->getTitle() !== $oldTitle) {
                $changedFields['title'] = ['old' => $oldTitle, 'new' => $goal->getTitle()];
            }
            if ($goal->getDescription() !== $oldDescription) {
                $changedFields['description'] = ['old' => $oldDescription, 'new' => $goal->getDescription()];
            }
            $newStart = $goal->getStartDate()?->format('Y-m-d');
            $newEnd = $goal->getEndDate()?->format('Y-m-d');
            $newDeadline = $goal->getDeadline()?->format('Y-m-d');
            if ($newStart !== $oldStartDate) {
                $changedFields['startDate'] = ['old' => $oldStartDate, 'new' => $newStart];
            }
            if ($newEnd !== $oldEndDate) {
                $changedFields['endDate'] = ['old' => $oldEndDate, 'new' => $newEnd];
            }
            if ($newDeadline !== $oldDeadline) {
                $changedFields['deadline'] = ['old' => $oldDeadline, 'new' => $newDeadline];
            }
            if ($changedFields !== []) {
                $this->goalHistoryLogger->logUpdated($goal, $user, $changedFields);
            }

            $this->entityManager->flush();
            $this->addFlash('success', 'Objectif modifié avec succès !');
            return $this->redirectToRoute('app_goal_index');
        }

        return $this->render('goal/edit.html.twig', [
            'form' => $form,
            'goal' => $goal,
        ]);
    }

    // ── Duplicate ──

    #[Route('/{id}/duplicate', name: 'duplicate', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function duplicate(Request $request, Goal $goal): JsonResponse
    {
        if ($goal->getUser() !== $this->getCurrentUser()) {
            return new JsonResponse(['success' => false, 'message' => 'Accès refusé.'], 403);
        }
        if ($this->isCsrfTokenValid('duplicate' . $goal->getId(), $request->request->get('_token'))) {
            $dup = new Goal();
            $dup->setTitle($goal->getTitle() . ' (Copie)');
            $dup->setDescription($goal->getDescription());
            $dup->setStartDate(clone $goal->getStartDate());
            $dup->setEndDate(clone $goal->getEndDate());
            $dup->setStatus('active');
            $dup->setUser($this->getCurrentUser());
            $dup->setPriority($goal->getPriority());
            if ($goal->getDeadline()) {
                $dup->setDeadline(clone $goal->getDeadline());
            }
            $this->entityManager->persist($dup);
            $this->entityManager->flush();

            return new JsonResponse([
                'success' => true,
                'message' => 'Objectif dupliqué avec succès !',
                'goal' => ['id' => $dup->getId(), 'title' => $dup->getTitle()],
            ]);
        }
        return new JsonResponse(['success' => false, 'message' => 'Token CSRF invalide'], 403);
    }

    // ── Delete ──

    #[Route('/{id}', name: 'delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function delete(Request $request, Goal $goal): JsonResponse
    {
        if ($goal->getUser() !== $this->getCurrentUser() && !$goal->canUserDeleteGoal($this->getCurrentUser())) {
            return new JsonResponse(['success' => false, 'message' => 'Accès refusé.'], 403);
        }
        if ($this->isCsrfTokenValid('delete' . $goal->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($goal);
            $this->entityManager->flush();
            return new JsonResponse(['success' => true, 'message' => 'Objectif supprimé avec succès !']);
        }
        return new JsonResponse(['success' => false, 'message' => 'Token CSRF invalide'], 403);
    }

    // ── Join / Leave (mariem) ──

    #[Route('/{id}/join', name: 'join', requirements: ['id' => '\d+'])]
    public function join(Goal $goal): Response
    {
        $user = $this->getCurrentUser();

        $existing = $this->entityManager->getRepository(GoalParticipation::class)
            ->findOneBy(['goal' => $goal, 'user' => $user]);

        if ($existing) {
            if ($existing->isPending()) {
                $this->addFlash('warning', 'Votre demande est déjà en attente d\'approbation.');
            } elseif ($existing->isApproved()) {
                $this->addFlash('warning', 'Vous participez déjà à ce goal.');
            }
            return $this->redirectToRoute('app_goal_community');
        }

        $participation = new GoalParticipation();
        $participation->setGoal($goal);
        $participation->setUser($user);
        $participation->setCreatedAt(new \DateTime());
        $participation->setRole(GoalParticipation::ROLE_MEMBER);
        $participation->setStatus(GoalParticipation::STATUS_PENDING);

        $this->entityManager->persist($participation);
        $this->entityManager->flush();

        $this->addFlash('success', 'Demande d\'accès envoyée ! En attente d\'approbation.');
        return $this->redirectToRoute('app_goal_community');
    }

    #[Route('/{id}/leave', name: 'leave', requirements: ['id' => '\d+'])]
    public function leave(Goal $goal): Response
    {
        $user = $this->getCurrentUser();

        $participation = $this->entityManager->getRepository(GoalParticipation::class)
            ->findOneBy(['goal' => $goal, 'user' => $user]);

        if ($participation) {
            $this->entityManager->remove($participation);
            $this->entityManager->flush();
            $this->addFlash('success', 'Vous avez quitté le goal.');
        }

        return $this->redirectToRoute('app_goal_community');
    }

    // ── Member management (mariem) ──

    #[Route('/{goalId}/remove-member/{userId}', name: 'remove_member', methods: ['POST'], requirements: ['goalId' => '\d+', 'userId' => '\d+'])]
    public function removeMember(int $goalId, int $userId, Request $request): Response
    {
        $user = $this->getCurrentUser();
        $goal = $this->entityManager->getRepository(Goal::class)->find($goalId);

        if (!$goal) {
            return $this->jsonOrFlash($request, false, 'Goal introuvable', 404, 'goal_list');
        }
        if (!$goal->canUserRemoveMembers($user)) {
            return $this->jsonOrFlash($request, false, 'Permission refusée', 403, 'message_chatroom', ['goalId' => $goalId]);
        }

        $memberToRemove = $this->entityManager->getRepository(User::class)->find($userId);
        if (!$memberToRemove) {
            return $this->jsonOrFlash($request, false, 'Utilisateur introuvable', 404, 'message_chatroom', ['goalId' => $goalId]);
        }
        if ($memberToRemove->getId() === $user->getId()) {
            return $this->jsonOrFlash($request, false, 'Vous ne pouvez pas vous exclure vous-même', 400, 'message_chatroom', ['goalId' => $goalId]);
        }

        $participation = $this->entityManager->getRepository(GoalParticipation::class)
            ->findOneBy(['goal' => $goal, 'user' => $memberToRemove]);

        if (!$participation) {
            return $this->jsonOrFlash($request, false, 'Cet utilisateur n\'est pas membre', 404, 'message_chatroom', ['goalId' => $goalId]);
        }

        $currentParticipation = $goal->getUserParticipation($user);
        if ($currentParticipation && $currentParticipation->isAdmin() && $participation->isOwner()) {
            return $this->jsonOrFlash($request, false, 'Un admin ne peut pas exclure le propriétaire', 403, 'message_chatroom', ['goalId' => $goalId]);
        }

        $memberName = $memberToRemove->getFirstName() . ' ' . $memberToRemove->getLastName();
        $this->entityManager->remove($participation);
        $this->entityManager->flush();

        return $this->jsonOrFlash($request, true, "$memberName a été exclu du goal", 200, 'message_chatroom', ['goalId' => $goalId]);
    }

    #[Route('/{goalId}/promote-member/{userId}', name: 'promote_member', methods: ['POST'], requirements: ['goalId' => '\d+', 'userId' => '\d+'])]
    public function promoteMember(int $goalId, int $userId, Request $request): JsonResponse
    {
        $user = $this->getCurrentUser();
        $goal = $this->entityManager->getRepository(Goal::class)->find($goalId);

        if (!$goal) return new JsonResponse(['success' => false, 'error' => 'Goal introuvable'], 404);

        $currentParticipation = $goal->getUserParticipation($user);
        if (!$currentParticipation || !$currentParticipation->isOwner()) {
            return new JsonResponse(['success' => false, 'error' => 'Seul le propriétaire peut promouvoir des membres'], 403);
        }

        $memberToPromote = $this->entityManager->getRepository(User::class)->find($userId);
        if (!$memberToPromote) return new JsonResponse(['success' => false, 'error' => 'Utilisateur introuvable'], 404);

        $participation = $this->entityManager->getRepository(GoalParticipation::class)
            ->findOneBy(['goal' => $goal, 'user' => $memberToPromote]);

        if (!$participation) return new JsonResponse(['success' => false, 'error' => 'Cet utilisateur n\'est pas membre'], 404);

        $newRole = $request->request->get('role');
        if (!in_array($newRole, ['MEMBER', 'ADMIN', 'OWNER'])) {
            return new JsonResponse(['success' => false, 'error' => 'Rôle invalide'], 400);
        }

        $participation->setRole($newRole);
        $this->entityManager->flush();

        return new JsonResponse([
            'success' => true,
            'message' => $memberToPromote->getFirstName() . ' est maintenant ' . $newRole,
            'newRole' => $newRole,
        ]);
    }

    #[Route('/{goalId}/approve-request/{userId}', name: 'approve_request', methods: ['POST'], requirements: ['goalId' => '\d+', 'userId' => '\d+'])]
    public function approveRequest(int $goalId, int $userId, Request $request): Response
    {
        $user = $this->getCurrentUser();
        $goal = $this->entityManager->getRepository(Goal::class)->find($goalId);

        if (!$goal) return $this->jsonOrFlash($request, false, 'Goal introuvable', 404, 'app_goal_community');
        if (!$goal->canUserRemoveMembers($user)) {
            return $this->jsonOrFlash($request, false, 'Permission refusée', 403, 'message_chatroom', ['goalId' => $goalId]);
        }

        $requestUser = $this->entityManager->getRepository(User::class)->find($userId);
        if (!$requestUser) return $this->jsonOrFlash($request, false, 'Utilisateur introuvable', 404, 'message_chatroom', ['goalId' => $goalId]);

        $participation = $this->entityManager->getRepository(GoalParticipation::class)
            ->findOneBy(['goal' => $goal, 'user' => $requestUser]);

        if (!$participation || !$participation->isPending()) {
            return $this->jsonOrFlash($request, false, 'Aucune demande en attente', 404, 'message_chatroom', ['goalId' => $goalId]);
        }

        $participation->setStatus(GoalParticipation::STATUS_APPROVED);
        $this->entityManager->flush();

        $userName = $requestUser->getFirstName() . ' ' . $requestUser->getLastName();
        return $this->jsonOrFlash($request, true, "$userName a été accepté dans le goal", 200, 'message_chatroom', ['goalId' => $goalId]);
    }

    #[Route('/{goalId}/reject-request/{userId}', name: 'reject_request', methods: ['POST'], requirements: ['goalId' => '\d+', 'userId' => '\d+'])]
    public function rejectRequest(int $goalId, int $userId, Request $request): Response
    {
        $user = $this->getCurrentUser();
        $goal = $this->entityManager->getRepository(Goal::class)->find($goalId);

        if (!$goal) return $this->jsonOrFlash($request, false, 'Goal introuvable', 404, 'app_goal_community');
        if (!$goal->canUserRemoveMembers($user)) {
            return $this->jsonOrFlash($request, false, 'Permission refusée', 403, 'message_chatroom', ['goalId' => $goalId]);
        }

        $requestUser = $this->entityManager->getRepository(User::class)->find($userId);
        if (!$requestUser) return $this->jsonOrFlash($request, false, 'Utilisateur introuvable', 404, 'message_chatroom', ['goalId' => $goalId]);

        $participation = $this->entityManager->getRepository(GoalParticipation::class)
            ->findOneBy(['goal' => $goal, 'user' => $requestUser]);

        if (!$participation || !$participation->isPending()) {
            return $this->jsonOrFlash($request, false, 'Aucune demande en attente', 404, 'message_chatroom', ['goalId' => $goalId]);
        }

        $userName = $requestUser->getFirstName() . ' ' . $requestUser->getLastName();
        $this->entityManager->remove($participation);
        $this->entityManager->flush();

        return $this->jsonOrFlash($request, true, "Demande de $userName refusée", 200, 'message_chatroom', ['goalId' => $goalId]);
    }

    /** Inviter un utilisateur à rejoindre le goal (chatroom). Envoie une demande d’invitation. */
    #[Route('/{goalId}/invite/{userId}', name: 'invite', methods: ['POST'], requirements: ['goalId' => '\d+', 'userId' => '\d+'])]
    public function invite(int $goalId, int $userId, Request $request): Response
    {
        $user = $this->getCurrentUser();
        $goal = $this->entityManager->getRepository(Goal::class)->find($goalId);

        if (!$goal) {
            return $this->jsonOrFlash($request, false, 'Goal introuvable', 404, 'message_chatroom', ['goalId' => $goalId]);
        }
        if (!$goal->canUserRemoveMembers($user)) {
            return $this->jsonOrFlash($request, false, 'Permission refusée', 403, 'message_chatroom', ['goalId' => $goalId]);
        }

        $invitedUser = $this->entityManager->getRepository(User::class)->find($userId);
        if (!$invitedUser) {
            return $this->jsonOrFlash($request, false, 'Utilisateur introuvable', 404, 'message_chatroom', ['goalId' => $goalId]);
        }

        $existing = $this->entityManager->getRepository(GoalParticipation::class)
            ->findOneBy(['goal' => $goal, 'user' => $invitedUser]);

        if ($existing) {
            if ($existing->isApproved()) {
                return $this->jsonOrFlash($request, false, 'Cet utilisateur est déjà membre du goal', 400, 'message_chatroom', ['goalId' => $goalId]);
            }
            return $this->jsonOrFlash($request, false, 'Une invitation a déjà été envoyée à cet utilisateur', 400, 'message_chatroom', ['goalId' => $goalId]);
        }

        $participation = new GoalParticipation();
        $participation->setGoal($goal);
        $participation->setUser($invitedUser);
        $participation->setCreatedAt(new \DateTime());
        $participation->setRole(GoalParticipation::ROLE_MEMBER);
        $participation->setStatus(GoalParticipation::STATUS_PENDING);
        $this->entityManager->persist($participation);
        $this->entityManager->flush();

        $inviterName = $user->getFirstName() . ' ' . $user->getLastName();
        $goalTitle = $goal->getTitle();
        $this->notificationService->createAndPublish(
            $invitedUser,
            'goal_invitation',
            $inviterName . ' vous a invité à rejoindre le goal « ' . $goalTitle . ' ». Vous pouvez accepter ou refuser ci-dessous.',
            null,
            $goal->getId()
        );

        $invitedName = $invitedUser->getFirstName() . ' ' . $invitedUser->getLastName();
        return $this->jsonOrFlash($request, true, 'Invitation envoyée à ' . $invitedName, 200, 'message_chatroom', ['goalId' => $goalId]);
    }

    /** Accepter une invitation au goal (utilisateur invité). */
    #[Route('/{goalId}/accept-invitation', name: 'accept_invitation', methods: ['POST'], requirements: ['goalId' => '\d+'])]
    public function acceptInvitation(int $goalId, Request $request): Response
    {
        $user = $this->getCurrentUser();
        $goal = $this->entityManager->getRepository(Goal::class)->find($goalId);

        if (!$goal) {
            return $this->jsonOrFlash($request, false, 'Goal introuvable', 404, 'app_user_profile');
        }

        $participation = $this->entityManager->getRepository(GoalParticipation::class)
            ->findOneBy(['goal' => $goal, 'user' => $user]);

        if (!$participation || !$participation->isPending()) {
            return $this->jsonOrFlash($request, false, 'Aucune invitation en attente pour ce goal', 404, 'app_user_profile');
        }

        $participation->setStatus(GoalParticipation::STATUS_APPROVED);
        $this->entityManager->flush();

        return $this->jsonOrFlash($request, true, 'Vous avez rejoint le goal « ' . $goal->getTitle() . ' ».', 200, 'message_chatroom', ['goalId' => $goalId]);
    }

    /** Refuser une invitation au goal (utilisateur invité). */
    #[Route('/{goalId}/reject-invitation', name: 'reject_invitation', methods: ['POST'], requirements: ['goalId' => '\d+'])]
    public function rejectInvitation(int $goalId, Request $request): Response
    {
        $user = $this->getCurrentUser();
        $goal = $this->entityManager->getRepository(Goal::class)->find($goalId);

        if (!$goal) {
            return $this->jsonOrFlash($request, false, 'Goal introuvable', 404, 'app_user_profile');
        }

        $participation = $this->entityManager->getRepository(GoalParticipation::class)
            ->findOneBy(['goal' => $goal, 'user' => $user]);

        if (!$participation || !$participation->isPending()) {
            return $this->jsonOrFlash($request, false, 'Aucune invitation en attente pour ce goal', 404, 'app_user_profile');
        }

        $this->entityManager->remove($participation);
        $this->entityManager->flush();

        return $this->jsonOrFlash($request, true, 'Invitation refusée.', 200, 'app_user_profile');
    }

    // ── Redirect legacy chatroom route ──

    #[Route('/{id}/messages', name: 'messages_redirect', requirements: ['id' => '\d+'])]
    public function messagesRedirect(Goal $goal): Response
    {
        return $this->redirectToRoute('message_chatroom', ['goalId' => $goal->getId()], 301);
    }

    // ── Helpers ──

    private function getAiSuggestionsForNewGoal(Request $request): array
    {
        $user = $this->getCurrentUser();
        $allGoals = $this->goalRepository->findByUser($user, 100);
        $totalGoals = \count($allGoals);
        $completedGoals = \count(array_filter($allGoals, fn (Goal $g) => $g->getStatus() === 'completed'));
        $now = new \DateTimeImmutable('today');
        $overdueGoals = \count(array_filter($allGoals, function (Goal $g) use ($now) {
            if ($g->getStatus() !== 'active' && $g->getStatus() !== 'paused') return false;
            $d = $g->getDeadline() ?? $g->getEndDate();
            return $d && $d < $now;
        }));
        $completionRate = $totalGoals > 0 ? (int) round($completedGoals / $totalGoals * 100) : 0;
        $userGoalsForAi = array_map(fn (Goal $g) => [
            'title' => $g->getTitle(),
            'description' => $g->getDescription() ? mb_substr($g->getDescription(), 0, 200) : null,
        ], $allGoals);

        $userData = [
            'total_goals' => $totalGoals,
            'completed_goals' => $completedGoals,
            'overdue_goals' => $overdueGoals,
            'completion_rate' => $completionRate,
            'user_goals' => $userGoalsForAi,
        ];

        $locale = method_exists($user, 'getPreferredLanguage')
            ? ($user->getPreferredLanguage() ?? 'fr')
            : ($request->getLocale() ?: 'fr');

        $aiSuggestions = [];
        $cached = $this->suggestionRepository->findLatestForUser($user, 24);
        if ($cached && $cached->getContent()) {
            $decoded = json_decode($cached->getContent(), true);
            if (\is_array($decoded)) $aiSuggestions = $decoded;
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

    private function jsonOrFlash(
        Request $request,
        bool $success,
        string $message,
        int $status,
        string $route,
        array $routeParams = []
    ): JsonResponse|Response {
        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(
                $success ? ['success' => true, 'message' => $message] : ['success' => false, 'error' => $message],
                $status
            );
        }
        $this->addFlash($success ? 'success' : 'error', $message);
        return $this->redirectToRoute($route, $routeParams);
    }
}
