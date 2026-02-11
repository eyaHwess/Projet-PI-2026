<?php

namespace App\Controller;

use App\Entity\Goal;
use App\Entity\User;
use App\Form\GoalType;
use App\Repository\GoalRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/goals', name: 'app_goal_')]
class GoalController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private GoalRepository $goalRepository,
        private UserRepository $userRepository,
        private UserPasswordHasherInterface $passwordHasher,
    ) {}

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

        return $this->render('goal/_form.html.twig', [
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
