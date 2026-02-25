<?php

namespace App\Controller;
use App\Entity\Chatroom;
use App\Entity\Goal;
use App\Entity\GoalParticipation;
use App\Entity\Message;
use App\Entity\MessageReaction;
use App\Entity\MessageReadReceipt;
use App\Entity\User;
use App\Form\GoalType;
use App\Form\MessageType;
use App\Repository\ChatroomRepository;
use App\Repository\GoalRepository;
use App\Repository\MessageReactionRepository;
use App\Repository\MessageReadReceiptRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class GoalController extends AbstractController
{
    public function __construct(
        private ChatroomRepository $chatroomRepository,
        private EntityManagerInterface $entityManager,
        private GoalRepository $goalRepository,
        private UserRepository $userRepository,
        private UserPasswordHasherInterface $passwordHasher,
    ) {}

    #[Route('/goals', name: 'goal_list')]
    public function list(GoalRepository $goalRepository, MessageReadReceiptRepository $readReceiptRepo): Response
    {
        // Accessible sans authentification
        
        return $this->render('goal/list.html.twig', [
            'goals' => $goalRepository->findGoalsWithParticipants(),
            'readReceiptRepo' => $readReceiptRepo,
        ]);
    }
#[Route('/goal/new', name: 'goal_new', methods: ['GET', 'POST'])]
public function new(Request $request, EntityManagerInterface $em): JsonResponse|Response
{
    // Vérifier que l'utilisateur est connecté
    $user = $this->getUser();
    if (!$user) {
        $this->addFlash('error', 'Vous devez être connecté pour créer un goal.');
        return $this->redirectToRoute('app_login');
    }

    $goal = new Goal();
    $form = $this->createForm(GoalType::class, $goal);
    $form->handleRequest($request);

    $isAjax = $request->isXmlHttpRequest()
        || $request->headers->get('X-Requested-With') === 'XMLHttpRequest'
        || str_contains($request->headers->get('Accept', ''), 'application/json');

    if ($form->isSubmitted() && $form->isValid()) {

        // Définir l'utilisateur créateur
        $goal->setUser($user);

        // Create Chatroom automatically
        $chatroom = new Chatroom();
        $chatroom->setCreatedAt(new \DateTime());
        $chatroom->setGoal($goal);

        // Creator joins automatically as OWNER with APPROVED status
        $participation = new GoalParticipation();
        $participation->setGoal($goal);
        $participation->setUser($user);
        $participation->setRole(GoalParticipation::ROLE_OWNER);
        $participation->setStatus(GoalParticipation::STATUS_APPROVED);
        $participation->setCreatedAt(new \DateTime());
        $em->persist($participation);

        $em->persist($goal);
        $em->persist($chatroom);
        $em->flush();

        if ($isAjax) {
            return new JsonResponse([
                'success' => true,
                'message' => 'Goal created successfully!',
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

        $this->addFlash('success', 'Goal created successfully!');
        return $this->redirectToRoute('goal_list');
    }

    return $this->render('goal/new.html.twig', [
        'form' => $form->createView(),
    ]);
}


    #[Route('/goal/{id}/join', name: 'goal_join', requirements: ['id' => '\d+'])]
    public function join(Goal $goal, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        
        $user = $this->getUser();

        // Check if already has a participation
        $existingParticipation = $em->getRepository(GoalParticipation::class)
            ->findOneBy(['goal' => $goal, 'user' => $user]);
        
        if ($existingParticipation) {
            if ($existingParticipation->isPending()) {
                $this->addFlash('warning', 'Votre demande est déjà en attente d\'approbation.');
            } elseif ($existingParticipation->isApproved()) {
                $this->addFlash('warning', 'Vous participez déjà à ce goal.');
            }
            return $this->redirectToRoute('goal_list');
        }

        // Create new participation with PENDING status
        $participation = new GoalParticipation();
        $participation->setGoal($goal);
        $participation->setUser($user);
        $participation->setCreatedAt(new \DateTime());
        $participation->setRole(GoalParticipation::ROLE_MEMBER);
        $participation->setStatus(GoalParticipation::STATUS_PENDING);

        $em->persist($participation);
        $em->flush();

        $this->addFlash('success', 'Demande d\'accès envoyée! En attente d\'approbation par les administrateurs.');
        return $this->redirectToRoute('goal_list');
    }

    #[Route('/goal/{id}/leave', name: 'goal_leave', requirements: ['id' => '\d+'])]
    public function leave(Goal $goal, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        
        $user = $this->getUser();

        $participation = $em->getRepository(GoalParticipation::class)
            ->findOneBy([
                'goal' => $goal,
                'user' => $user
            ]);

        if ($participation) {
            $em->remove($participation);
            $em->flush();
            $this->addFlash('success', 'Vous avez quitté le goal.');
        }

        return $this->redirectToRoute('goal_list');
    }

    #[Route('/goal/{id}', name: 'goal_show', requirements: ['id' => '\d+'])]
    public function show(Goal $goal): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        
        return $this->render('goal/show.html.twig', [
            'goal' => $goal,
        ]);
    }

    /**
     * Redirect old chatroom route to new MessageController route
     */
    #[Route('/goal/{id}/messages', name: 'goal_messages', requirements: ['id' => '\d+'])]
    public function messagesRedirect(Goal $goal): Response
    {
        return $this->redirectToRoute('message_chatroom', ['goalId' => $goal->getId()], 301);
    }

    #[Route('/demo/setup', name: 'demo_setup')]
    public function setupDemo(EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher): Response
    {
        // Créer l'utilisateur Mariem
        $userRepo = $em->getRepository(User::class);
        
        // Créer le compte de Mariem
        $mariem = $userRepo->findOneBy(['email' => 'mariemayari@gmail.com']);
        if (!$mariem) {
            $mariem = new User();
            $mariem->setFirstName('Mariem');
            $mariem->setLastName('Ayari');
            $mariem->setEmail('mariemayari@gmail.com');
            $mariem->setPassword($passwordHasher->hashPassword($mariem, 'mariem'));
            $mariem->setRoles(['ROLE_USER']);
            $mariem->setStatus('active');
            $mariem->setCreatedAt(new \DateTimeImmutable());
            $em->persist($mariem);
            $em->flush();
        }

        $this->addFlash('success', 'Compte créé! Email: mariemayari@gmail.com / Password: mariem');
        return $this->redirectToRoute('goal_list');
    }




    #[Route('/goal/{id}/delete', name: 'goal_delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function deleteGoal(Goal $goal, EntityManagerInterface $em, Request $request): Response
    {
        $user = $this->getUser();
        
        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté');
            return $this->redirectToRoute('app_login');
        }

        // Check if user has permission to delete (OWNER only)
        if (!$goal->canUserDeleteGoal($user)) {
            $this->addFlash('error', 'Seul le propriétaire peut supprimer ce goal');
            return $this->redirectToRoute('goal_list');
        }

        $goalTitle = $goal->getTitle();
        $em->remove($goal);
        $em->flush();

        $this->addFlash('success', "Le goal \"$goalTitle\" a été supprimé avec succès");
        return $this->redirectToRoute('goal_list');
    }

    #[Route('/goal/{id}/edit', name: 'goal_edit', requirements: ['id' => '\d+'])]
    public function editGoal(Goal $goal, Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        
        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté');
            return $this->redirectToRoute('app_login');
        }

        // Check if user has permission to modify (ADMIN or OWNER)
        if (!$goal->canUserModifyGoal($user)) {
            $this->addFlash('error', 'Vous n\'avez pas la permission de modifier ce goal');
            return $this->redirectToRoute('goal_list');
        }

        $form = $this->createForm(GoalType::class, $goal);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Goal modifié avec succès!');
            return $this->redirectToRoute('goal_list');
        }

        return $this->render('goal/edit.html.twig', [
            'form' => $form->createView(),
            'goal' => $goal,
        ]);
    }

    #[Route('/goal/{goalId}/remove-member/{userId}', name: 'goal_remove_member', methods: ['POST'], requirements: ['goalId' => '\d+', 'userId' => '\d+'])]
    public function removeMember(int $goalId, int $userId, EntityManagerInterface $em, Request $request): Response
    {
        $user = $this->getUser();
        
        if (!$user) {
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse(['success' => false, 'error' => 'Vous devez être connecté'], 401);
            }
            $this->addFlash('error', 'Vous devez être connecté');
            return $this->redirectToRoute('app_login');
        }

        $goal = $em->getRepository(Goal::class)->find($goalId);
        if (!$goal) {
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse(['success' => false, 'error' => 'Goal introuvable'], 404);
            }
            $this->addFlash('error', 'Goal introuvable');
            return $this->redirectToRoute('goal_list');
        }

        // Check if user has permission to remove members (ADMIN or OWNER)
        if (!$goal->canUserRemoveMembers($user)) {
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse(['success' => false, 'error' => 'Vous n\'avez pas la permission d\'exclure des membres'], 403);
            }
            $this->addFlash('error', 'Vous n\'avez pas la permission d\'exclure des membres');
            return $this->redirectToRoute('message_chatroom', ['goalId' => $goalId]);
        }

        $memberToRemove = $em->getRepository(User::class)->find($userId);
        if (!$memberToRemove) {
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse(['success' => false, 'error' => 'Utilisateur introuvable'], 404);
            }
            $this->addFlash('error', 'Utilisateur introuvable');
            return $this->redirectToRoute('message_chatroom', ['goalId' => $goalId]);
        }

        // Cannot remove yourself
        if ($memberToRemove->getId() === $user->getId()) {
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse(['success' => false, 'error' => 'Vous ne pouvez pas vous exclure vous-même'], 400);
            }
            $this->addFlash('error', 'Vous ne pouvez pas vous exclure vous-même. Utilisez "Quitter le goal".');
            return $this->redirectToRoute('message_chatroom', ['goalId' => $goalId]);
        }

        $participation = $em->getRepository(GoalParticipation::class)->findOneBy([
            'goal' => $goal,
            'user' => $memberToRemove
        ]);

        if (!$participation) {
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse(['success' => false, 'error' => 'Cet utilisateur n\'est pas membre de ce goal'], 404);
            }
            $this->addFlash('error', 'Cet utilisateur n\'est pas membre de ce goal');
            return $this->redirectToRoute('message_chatroom', ['goalId' => $goalId]);
        }

        // ADMIN cannot remove OWNER
        $currentUserParticipation = $goal->getUserParticipation($user);
        if ($currentUserParticipation->isAdmin() && $participation->isOwner()) {
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse(['success' => false, 'error' => 'Un administrateur ne peut pas exclure le propriétaire'], 403);
            }
            $this->addFlash('error', 'Un administrateur ne peut pas exclure le propriétaire');
            return $this->redirectToRoute('message_chatroom', ['goalId' => $goalId]);
        }

        $memberName = $memberToRemove->getFirstName() . ' ' . $memberToRemove->getLastName();
        $em->remove($participation);
        $em->flush();

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse([
                'success' => true,
                'message' => "$memberName a été exclu du goal"
            ]);
        }

        $this->addFlash('success', "$memberName a été exclu du goal");
        return $this->redirectToRoute('message_chatroom', ['goalId' => $goalId]);
    }

    #[Route('/goal/{goalId}/promote-member/{userId}', name: 'goal_promote_member', methods: ['POST'], requirements: ['goalId' => '\d+', 'userId' => '\d+'])]
    public function promoteMember(int $goalId, int $userId, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $user = $this->getUser();
        
        if (!$user) {
            return new JsonResponse(['success' => false, 'error' => 'Vous devez être connecté'], 401);
        }

        $goal = $em->getRepository(Goal::class)->find($goalId);
        if (!$goal) {
            return new JsonResponse(['success' => false, 'error' => 'Goal introuvable'], 404);
        }

        // Only OWNER can promote members
        $currentUserParticipation = $goal->getUserParticipation($user);
        if (!$currentUserParticipation || !$currentUserParticipation->isOwner()) {
            return new JsonResponse(['success' => false, 'error' => 'Seul le propriétaire peut promouvoir des membres'], 403);
        }

        $memberToPromote = $em->getRepository(User::class)->find($userId);
        if (!$memberToPromote) {
            return new JsonResponse(['success' => false, 'error' => 'Utilisateur introuvable'], 404);
        }

        $participation = $em->getRepository(GoalParticipation::class)->findOneBy([
            'goal' => $goal,
            'user' => $memberToPromote
        ]);

        if (!$participation) {
            return new JsonResponse(['success' => false, 'error' => 'Cet utilisateur n\'est pas membre de ce goal'], 404);
        }

        $newRole = $request->request->get('role');
        if (!in_array($newRole, ['MEMBER', 'ADMIN', 'OWNER'])) {
            return new JsonResponse(['success' => false, 'error' => 'Rôle invalide'], 400);
        }

        $participation->setRole($newRole);
        $em->flush();

        return new JsonResponse([
            'success' => true,
            'message' => $memberToPromote->getFirstName() . ' est maintenant ' . $newRole,
            'newRole' => $newRole
        ]);
    }
    #[Route('/goal/{goalId}/approve-request/{userId}', name: 'goal_approve_request', methods: ['POST'], requirements: ['goalId' => '\d+', 'userId' => '\d+'])]
    public function approveRequest(
        int $goalId,
        int $userId,
        EntityManagerInterface $em,
        Request $request
    ): Response {
        $user = $this->getUser();

        if (!$user) {
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse(['success' => false, 'error' => 'Vous devez être connecté'], 401);
            }
            $this->addFlash('error', 'Vous devez être connecté');
            return $this->redirectToRoute('app_login');
        }

        $goal = $em->getRepository(Goal::class)->find($goalId);
        if (!$goal) {
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse(['success' => false, 'error' => 'Goal introuvable'], 404);
            }
            $this->addFlash('error', 'Goal introuvable');
            return $this->redirectToRoute('goal_list');
        }

        // Check permission (ADMIN or OWNER)
        if (!$goal->canUserRemoveMembers($user)) {
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse(['success' => false, 'error' => 'Permission refusée'], 403);
            }
            $this->addFlash('error', 'Vous n\'avez pas la permission d\'approuver des demandes');
            return $this->redirectToRoute('message_chatroom', ['goalId' => $goalId]);
        }

        $requestUser = $em->getRepository(User::class)->find($userId);
        if (!$requestUser) {
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse(['success' => false, 'error' => 'Utilisateur introuvable'], 404);
            }
            $this->addFlash('error', 'Utilisateur introuvable');
            return $this->redirectToRoute('message_chatroom', ['goalId' => $goalId]);
        }

        $participation = $em->getRepository(GoalParticipation::class)->findOneBy([
            'goal' => $goal,
            'user' => $requestUser
        ]);

        if (!$participation || !$participation->isPending()) {
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse(['success' => false, 'error' => 'Aucune demande en attente'], 404);
            }
            $this->addFlash('error', 'Aucune demande en attente pour cet utilisateur');
            return $this->redirectToRoute('message_chatroom', ['goalId' => $goalId]);
        }

        // Approve the request
        $participation->setStatus(GoalParticipation::STATUS_APPROVED);
        $em->flush();

        $userName = $requestUser->getFirstName() . ' ' . $requestUser->getLastName();

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse([
                'success' => true,
                'message' => "$userName a été accepté dans le goal"
            ]);
        }

        $this->addFlash('success', "$userName a été accepté dans le goal");
        return $this->redirectToRoute('message_chatroom', ['goalId' => $goalId]);
    }

    #[Route('/goal/{goalId}/reject-request/{userId}', name: 'goal_reject_request', methods: ['POST'], requirements: ['goalId' => '\d+', 'userId' => '\d+'])]
    public function rejectRequest(
        int $goalId,
        int $userId,
        EntityManagerInterface $em,
        Request $request
    ): Response {
        $user = $this->getUser();

        if (!$user) {
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse(['success' => false, 'error' => 'Vous devez être connecté'], 401);
            }
            $this->addFlash('error', 'Vous devez être connecté');
            return $this->redirectToRoute('app_login');
        }

        $goal = $em->getRepository(Goal::class)->find($goalId);
        if (!$goal) {
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse(['success' => false, 'error' => 'Goal introuvable'], 404);
            }
            $this->addFlash('error', 'Goal introuvable');
            return $this->redirectToRoute('goal_list');
        }

        // Check permission (ADMIN or OWNER)
        if (!$goal->canUserRemoveMembers($user)) {
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse(['success' => false, 'error' => 'Permission refusée'], 403);
            }
            $this->addFlash('error', 'Vous n\'avez pas la permission de refuser des demandes');
            return $this->redirectToRoute('message_chatroom', ['goalId' => $goalId]);
        }

        $requestUser = $em->getRepository(User::class)->find($userId);
        if (!$requestUser) {
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse(['success' => false, 'error' => 'Utilisateur introuvable'], 404);
            }
            $this->addFlash('error', 'Utilisateur introuvable');
            return $this->redirectToRoute('message_chatroom', ['goalId' => $goalId]);
        }

        $participation = $em->getRepository(GoalParticipation::class)->findOneBy([
            'goal' => $goal,
            'user' => $requestUser
        ]);

        if (!$participation || !$participation->isPending()) {
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse(['success' => false, 'error' => 'Aucune demande en attente'], 404);
            }
            $this->addFlash('error', 'Aucune demande en attente pour cet utilisateur');
            return $this->redirectToRoute('message_chatroom', ['goalId' => $goalId]);
        }

        // Reject the request (delete participation)
        $userName = $requestUser->getFirstName() . ' ' . $requestUser->getLastName();
        $em->remove($participation);
        $em->flush();

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse([
                'success' => true,
                'message' => "Demande de $userName refusée"
            ]);
        }

        $this->addFlash('success', "Demande de $userName refusée");
        return $this->redirectToRoute('message_chatroom', ['goalId' => $goalId]);
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

    // 👇 INDEX
    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $sortBy = $request->query->get('sort', 'createdAt'); // Default sort by creation date
        $sortOrder = $request->query->get('order', 'DESC'); // Default descending
        $filterStatus = $request->query->get('status', 'all'); // Default show all

        // Build query
        $queryBuilder = $this->goalRepository->createQueryBuilder('g');

        // Apply status filter
        if ($filterStatus !== 'all') {
            $queryBuilder->andWhere('g.status = :status')
                        ->setParameter('status', $filterStatus);
        }

        // Apply sorting
        $validSortFields = ['createdAt', 'startDate', 'endDate', 'title'];
        if (in_array($sortBy, $validSortFields)) {
            $queryBuilder->orderBy('g.' . $sortBy, $sortOrder);
        } else {
            $queryBuilder->orderBy('g.createdAt', 'DESC');
        }

        $goals = $queryBuilder->getQuery()->getResult();

        return $this->render('goal/index.html.twig', [
            'goals' => $goals,
            'currentSort' => $sortBy,
            'currentOrder' => $sortOrder,
            'currentStatus' => $filterStatus,
        ]);
    }


    // 👇 EDIT
    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function edit(Request $request, Goal $goal): JsonResponse|Response
    {
        $form = $this->createForm(GoalType::class, $goal);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $this->entityManager->flush();

            return new JsonResponse([
                'success' => true,
                'message' => 'Objectif modifié avec succès !',
                'goal' => [
                    'id' => $goal->getId(),
                    'title' => $goal->getTitle(),
                    'description' => $goal->getDescription(),
                    'status' => $goal->getStatus(),
                    'startDate' => $goal->getStartDate()?->format('Y-m-d'),
                    'endDate' => $goal->getEndDate()?->format('Y-m-d'),
                ]
            ]);
        }

        return $this->render('goal/_form.html.twig', [
            'form' => $form,
            'goal' => $goal,
        ]);
    }

    // 👇 DELETE
    #[Route('/{id}', name: 'delete', methods: ['POST'], requirements: ['id' => '\d+'])]
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
