<?php

namespace App\Controller;

use App\Entity\Activity;
use App\Entity\Routine;
use App\Form\ActivityType;
use App\Repository\ActivityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/routines/{routineId}/activities', name: 'app_activity_')]
class ActivityController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ActivityRepository $activityRepository
    ) {
    }

    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(int $routineId): Response
    {
        $routine = $this->entityManager->getRepository(Routine::class)->find($routineId);
        
        if (!$routine) {
            throw $this->createNotFoundException('Routine non trouvée');
        }

        $activities = $this->activityRepository->findByRoutine($routineId);

        return $this->render('activity/index.html.twig', [
            'routine' => $routine,
            'activities' => $activities,
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request, int $routineId): JsonResponse|Response
    {
        $routine = $this->entityManager->getRepository(Routine::class)->find($routineId);
        
        if (!$routine) {
            throw $this->createNotFoundException('Routine non trouvée');
        }

        $activity = new Activity();
        $activity->setRoutine($routine);
        $form = $this->createForm(ActivityType::class, $activity);
        $form->handleRequest($request);

        // Détecter les requêtes AJAX
        $isAjax = $request->isXmlHttpRequest() 
            || $request->headers->get('X-Requested-With') === 'XMLHttpRequest'
            || str_contains($request->headers->get('Accept', ''), 'application/json');
        
        if ($isAjax) {
            if ($form->isSubmitted()) {
                // Vérifier les erreurs CSRF avant la validation
                if (!$form->isValid() && $form->getErrors(true)->count() > 0) {
                    $csrfError = null;
                    foreach ($form->getErrors(true) as $error) {
                        if (str_contains($error->getMessage(), 'CSRF') || str_contains($error->getMessage(), 'token')) {
                            $csrfError = $error->getMessage();
                            break;
                        }
                    }
                    
                    if ($csrfError) {
                        return new JsonResponse([
                            'success' => false,
                            'message' => 'Erreur CSRF: ' . $csrfError . '. Veuillez recharger la page et réessayer.',
                            'errors' => ['Token CSRF invalide']
                        ], 400);
                    }
                }
                
                if ($form->isValid()) {
                    try {
                        $this->entityManager->persist($activity);
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
                        'message' => 'Activité créée avec succès !',
                        'activity' => [
                            'id' => $activity->getId(),
                            'title' => $activity->getTitle(),
                            'startTime' => $activity->getStartTime()?->format('Y-m-d H:i:s'),
                            'duration' => $activity->getDuration()?->format('H:i:s'),
                            'status' => $activity->getStatus(),
                            'hasReminder' => $activity->isHasReminder(),
                            'reminderAt' => $activity->getReminderAt()?->format('Y-m-d H:i:s'),
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
            return $this->render('activity/_form.html.twig', [
                'form' => $form,
                'activity' => $activity ?? new Activity(),
                'routine' => $routine,
            ]);
        }

        return $this->render('activity/_form.html.twig', [
            'form' => $form,
            'activity' => $activity,
            'routine' => $routine,
        ]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(int $routineId, Activity $activity): Response
    {
        return $this->render('activity/show.html.twig', [
            'activity' => $activity,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, int $routineId, Activity $activity): JsonResponse|Response
    {
        $routine = $this->entityManager->getRepository(Routine::class)->find($routineId);
        
        if (!$routine) {
            throw $this->createNotFoundException('Routine non trouvée');
        }

        $form = $this->createForm(ActivityType::class, $activity);
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
                    return $this->redirectToRoute('app_routine_show', ['goalId' => $routine->getGoal()->getId(), 'id' => $routineId]);
                }

                if ($isAjax) {
                    return new JsonResponse([
                        'success' => true,
                        'message' => 'Activité modifiée avec succès !',
                        'activity' => [
                            'id' => $activity->getId(),
                            'title' => $activity->getTitle(),
                            'startTime' => $activity->getStartTime()?->format('Y-m-d H:i:s'),
                            'duration' => $activity->getDuration()?->format('H:i:s'),
                            'status' => $activity->getStatus(),
                            'hasReminder' => $activity->isHasReminder(),
                            'reminderAt' => $activity->getReminderAt()?->format('Y-m-d H:i:s'),
                        ]
                    ]);
                }
                
                $this->addFlash('success', 'Activité modifiée avec succès !');
                return $this->redirectToRoute('app_routine_show', ['goalId' => $routine->getGoal()->getId(), 'id' => $routineId]);
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
        return $this->render('activity/_form.html.twig', [
            'form' => $form,
            'activity' => $activity,
            'routine' => $routine,
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE', 'POST'])]
    public function delete(Request $request, int $routineId, Activity $activity): JsonResponse
    {
        if ($this->isCsrfTokenValid('delete' . $activity->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($activity);
            $this->entityManager->flush();

            return new JsonResponse([
                'success' => true,
                'message' => 'Activité supprimée avec succès !'
            ]);
        }

        return new JsonResponse([
            'success' => false,
            'message' => 'Token CSRF invalide'
        ], 403);
    }

    #[Route('/{id}/status', name: 'update_status', methods: ['PATCH', 'POST'])]
    public function updateStatus(Request $request, int $routineId, Activity $activity): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $status = $data['status'] ?? null;

        if (!in_array($status, ['pending', 'in_progress', 'completed', 'skipped', 'cancelled'])) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Statut invalide'
            ], 400);
        }

        $activity->setStatus($status);
        $this->entityManager->flush();

        return new JsonResponse([
            'success' => true,
            'message' => 'Statut mis à jour avec succès !',
            'status' => $status
        ]);
    }
}
