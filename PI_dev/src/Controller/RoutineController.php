<?php

namespace App\Controller;

use App\Entity\Goal;
use App\Entity\Routine;
use App\Form\RoutineType;
use App\Repository\RoutineRepository;
use App\Service\ChartService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/goals/{goalId}/routines', name: 'app_routine_')]
class RoutineController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private RoutineRepository $routineRepository,
        private ChartService $chartService,
    ) {
    }

    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(Request $request, int $goalId): Response
    {
        $goal = $this->entityManager->getRepository(Goal::class)->find($goalId);
        
        if (!$goal) {
            throw $this->createNotFoundException('Objectif non trouvé');
        }

        $sortBy = $request->query->get('sort', 'createdAt');
        $sortOrder = $request->query->get('order', 'DESC');
        $filterVisibility = $request->query->get('visibility', 'all');
        $searchQuery = $request->query->get('search', '');

        // Build query
        $queryBuilder = $this->routineRepository->createQueryBuilder('r')
            ->where('r.goal = :goal')
            ->setParameter('goal', $goal);

        // Apply search filter
        if (!empty($searchQuery)) {
            $queryBuilder->andWhere('r.title LIKE :search OR r.description LIKE :search')
                        ->setParameter('search', '%' . $searchQuery . '%');
        }

        // Apply visibility filter
        if ($filterVisibility !== 'all') {
            $queryBuilder->andWhere('r.visibility = :visibility')
                        ->setParameter('visibility', $filterVisibility);
        }

        // Apply sorting
        $validSortFields = ['createdAt', 'title'];
        if (in_array($sortBy, $validSortFields)) {
            $queryBuilder->orderBy('r.' . $sortBy, $sortOrder);
        } else {
            $queryBuilder->orderBy('r.createdAt', 'DESC');
        }

        $routines = $queryBuilder->getQuery()->getResult();

        return $this->render('routine/index.html.twig', [
            'goal' => $goal,
            'routines' => $routines,
            'currentSort' => $sortBy,
            'currentOrder' => $sortOrder,
            'currentVisibility' => $filterVisibility,
            'searchQuery' => $searchQuery,
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request, int $goalId): JsonResponse|Response
    {
        $goal = $this->entityManager->getRepository(Goal::class)->find($goalId);
        
        if (!$goal) {
            throw $this->createNotFoundException('Objectif non trouvé');
        }

        $routine = new Routine();
        $routine->setGoal($goal);
        $form = $this->createForm(RoutineType::class, $routine);
        $form->handleRequest($request);

        // Détecter les requêtes AJAX
        $isAjax = $request->isXmlHttpRequest() 
            || $request->headers->get('X-Requested-With') === 'XMLHttpRequest'
            || str_contains($request->headers->get('Accept', ''), 'application/json');
        
        if ($isAjax) {
            if ($form->isSubmitted()) {
                if ($form->isValid()) {
                    try {
                        $this->entityManager->persist($routine);
                        $this->entityManager->flush();
                    } catch (\Exception $e) {
                        return new JsonResponse([
                            'success' => false,
                            'message' => 'Erreur lors de l\'enregistrement: ' . $e->getMessage(),
                            'errors' => []
                        ], 500);
                    }

                    return new JsonResponse([
                        'success' => true,
                        'message' => 'Routine créée avec succès !',
                        'routine' => [
                            'id' => $routine->getId(),
                            'title' => $routine->getTitle(),
                            'description' => $routine->getDescription(),
                            'visibility' => $routine->getVisibility(),
                        ]
                    ], 201);
                } else {
                    // Formulaire soumis mais invalide
                    $errors = [];
                    foreach ($form->getErrors(true) as $error) {
                        $errors[] = $error->getMessage();
                    }
                    
                    // Ajouter les erreurs de validation des champs
                    foreach ($form->all() as $child) {
                        if (!$child->isValid()) {
                            foreach ($child->getErrors() as $error) {
                                $errors[] = $child->getName() . ': ' . $error->getMessage();
                            }
                        }
                    }

                    return new JsonResponse([
                        'success' => false,
                        'message' => 'Erreur de validation',
                        'errors' => $errors
                    ], 400);
                }
            }
            
            // Requête GET AJAX - retourner le formulaire en HTML
            return $this->render('routine/_form.html.twig', [
                'form' => $form,
                'routine' => $routine ?? new Routine(),
                'goal' => $goal,
            ]);
        }

        // Requête normale - gérer la soumission du formulaire
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->entityManager->persist($routine);
                $this->entityManager->flush();
                
                $this->addFlash('success', 'Routine créée avec succès !');
                return $this->redirectToRoute('app_goal_show', ['id' => $goalId]);
            } catch (\Exception $e) {
                $this->addFlash('error', 'Erreur lors de l\'enregistrement: ' . $e->getMessage());
            }
        }

        // Requête normale - retourner la page complète
        return $this->render('routine/new.html.twig', [
            'form' => $form,
            'routine' => $routine,
            'goal' => $goal,
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Request $request, int $goalId, Routine $routine): Response
    {
        $sortBy = $request->query->get('sort', 'startTime');
        $sortOrder = $request->query->get('order', 'ASC');
        $filterStatus = $request->query->get('status', 'all');
        $searchQuery = $request->query->get('search', '');

        // Build query for activities
        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->select('a')
            ->from('App\Entity\Activity', 'a')
            ->where('a.routine = :routine')
            ->setParameter('routine', $routine);

        // Apply search filter
        if (!empty($searchQuery)) {
            $queryBuilder->andWhere('a.title LIKE :search')
                        ->setParameter('search', '%' . $searchQuery . '%');
        }

        // Apply status filter
        if ($filterStatus !== 'all') {
            $queryBuilder->andWhere('a.status = :status')
                        ->setParameter('status', $filterStatus);
        }

        // Apply sorting
        $validSortFields = ['startTime', 'title', 'duration', 'status'];
        if (in_array($sortBy, $validSortFields)) {
            $queryBuilder->orderBy('a.' . $sortBy, $sortOrder);
        } else {
            $queryBuilder->orderBy('a.startTime', 'ASC');
        }

        $activities = $queryBuilder->getQuery()->getResult();

        $weeklyChart = $this->chartService->createWeeklyActivityChart($routine);
        $timeChart = $this->chartService->createTimeInvestmentChart($routine);

        return $this->render('routine/show.html.twig', [
            'routine' => $routine,
            'activities' => $activities,
            'currentSort' => $sortBy,
            'currentOrder' => $sortOrder,
            'currentStatus' => $filterStatus,
            'searchQuery' => $searchQuery,
            'weeklyChart' => $weeklyChart,
            'timeChart' => $timeChart,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, int $goalId, Routine $routine): JsonResponse|Response
    {
        $goal = $this->entityManager->getRepository(Goal::class)->find($goalId);
        
        if (!$goal) {
            throw $this->createNotFoundException('Objectif non trouvé');
        }

        $form = $this->createForm(RoutineType::class, $routine);
        $form->handleRequest($request);

        // Détecter les requêtes AJAX
        $isAjax = $request->isXmlHttpRequest() 
            || $request->headers->get('X-Requested-With') === 'XMLHttpRequest'
            || str_contains($request->headers->get('Accept', ''), 'application/json');

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                try {
                    $this->entityManager->flush();
                } catch (\Exception $e) {
                    if ($isAjax) {
                        return new JsonResponse([
                            'success' => false,
                            'message' => 'Erreur lors de l\'enregistrement: ' . $e->getMessage(),
                            'errors' => []
                        ], 500);
                    }
                    $this->addFlash('error', 'Erreur lors de l\'enregistrement: ' . $e->getMessage());
                    return $this->redirectToRoute('app_routine_show', ['goalId' => $goalId, 'id' => $routine->getId()]);
                }

                if ($isAjax) {
                    return new JsonResponse([
                        'success' => true,
                        'message' => 'Routine modifiée avec succès !',
                        'routine' => [
                            'id' => $routine->getId(),
                            'title' => $routine->getTitle(),
                            'description' => $routine->getDescription(),
                            'visibility' => $routine->getVisibility(),
                        ]
                    ]);
                }
                
                $this->addFlash('success', 'Routine modifiée avec succès !');
                return $this->redirectToRoute('app_routine_show', ['goalId' => $goalId, 'id' => $routine->getId()]);
            } else {
                // Formulaire soumis mais invalide
                $errors = [];
                foreach ($form->getErrors(true) as $error) {
                    $errors[] = $error->getMessage();
                }
                
                // Ajouter les erreurs de validation des champs
                foreach ($form->all() as $child) {
                    if (!$child->isValid()) {
                        foreach ($child->getErrors() as $error) {
                            $errors[] = $child->getName() . ': ' . $error->getMessage();
                        }
                    }
                }

                if ($isAjax) {
                    return new JsonResponse([
                        'success' => false,
                        'message' => 'Erreur de validation',
                        'errors' => $errors
                    ], 400);
                }
            }
        }
        
        // Retourner le formulaire (pour GET ou POST invalide)
        return $this->render('routine/_form.html.twig', [
            'form' => $form,
            'routine' => $routine,
            'goal' => $goal,
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE', 'POST'])]
    public function delete(Request $request, int $goalId, Routine $routine): JsonResponse
    {
        if ($this->isCsrfTokenValid('delete' . $routine->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($routine);
            $this->entityManager->flush();

            return new JsonResponse([
                'success' => true,
                'message' => 'Routine supprimée avec succès !'
            ]);
        }

        return new JsonResponse([
            'success' => false,
            'message' => 'Token CSRF invalide'
        ], 403);
    }

    #[Route('/{id}/duplicate', name: 'duplicate', methods: ['POST'])]
    public function duplicate(Request $request, int $goalId, Routine $routine): JsonResponse
    {
        if ($this->isCsrfTokenValid('duplicate' . $routine->getId(), $request->request->get('_token'))) {
            // Create a new routine with the same properties
            $duplicatedRoutine = new Routine();
            $duplicatedRoutine->setTitle($routine->getTitle() . ' (Copie)');
            $duplicatedRoutine->setDescription($routine->getDescription());
            $duplicatedRoutine->setVisibility($routine->getVisibility());
            $duplicatedRoutine->setGoal($routine->getGoal());
            $duplicatedRoutine->setPriority($routine->getPriority());
            if ($routine->getDeadline()) {
                $duplicatedRoutine->setDeadline(clone $routine->getDeadline());
            }

            // Optionally duplicate activities as well
            foreach ($routine->getActivities() as $activity) {
                $duplicatedActivity = new \App\Entity\Activity();
                $duplicatedActivity->setTitle($activity->getTitle());
                $duplicatedActivity->setStartTime(clone $activity->getStartTime());
                $duplicatedActivity->setDuration(clone $activity->getDuration());
                $duplicatedActivity->setStatus('pending'); // Reset to pending
                $duplicatedActivity->setHasReminder($activity->isHasReminder());
                if ($activity->getReminderAt()) {
                    $duplicatedActivity->setReminderAt(clone $activity->getReminderAt());
                }
                $duplicatedActivity->setPriority($activity->getPriority());
                if ($activity->getDeadline()) {
                    $duplicatedActivity->setDeadline(clone $activity->getDeadline());
                }
                $duplicatedActivity->setRoutine($duplicatedRoutine);
                $this->entityManager->persist($duplicatedActivity);
            }

            $this->entityManager->persist($duplicatedRoutine);
            $this->entityManager->flush();

            return new JsonResponse([
                'success' => true,
                'message' => 'Routine dupliquée avec succès (avec toutes ses activités) !',
                'routine' => [
                    'id' => $duplicatedRoutine->getId(),
                    'title' => $duplicatedRoutine->getTitle(),
                ]
            ]);
        }

        return new JsonResponse([
            'success' => false,
            'message' => 'Token CSRF invalide'
        ], 403);
    }
}
