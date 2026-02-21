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

#[Route('/goals', name: 'app_goal_')]
class GoalController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private GoalRepository $goalRepository,
        private UserRepository $userRepository,
        private UserPasswordHasherInterface $passwordHasher,
        private StatusManager $statusManager,
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

    // 👇 INDEX
#[Route('/', name: 'index', methods: ['GET'])]
public function index(Request $request, PaginatorInterface $paginator): Response
{
    $sortBy = $request->query->get('sort', 'createdAt');
    $sortOrder = $request->query->get('order', 'DESC');
    $filterStatus = $request->query->get('status', 'all');
    $searchQuery = $request->query->get('search', '');

    $queryBuilder = $this->goalRepository->createQueryBuilder('g');

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

    // Mise à jour statuts uniquement sur la page actuelle
    foreach ($pagination as $goal) {
        $this->statusManager->updateGoalStatuses($goal);
    }

    return $this->render('goal/index_modern.html.twig', [
        'pagination' => $pagination,
        'currentSort' => $sortBy,
        'currentOrder' => $sortOrder,
        'currentStatus' => $filterStatus,
        'searchQuery' => $searchQuery,
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
