<?php

namespace App\Controller;

use App\Entity\CoachingRequest;
use App\Entity\Session;
use App\Entity\User;
use App\Form\SessionType;
use App\Repository\CoachingRequestRepository;
use App\Repository\SessionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Gestion des sessions par le coach uniquement (création, modification, suppression).
 * Réservé aux utilisateurs avec le rôle ROLE_COACH.
 */
#[Route('/sessions/manage', name: 'app_session_crud_')]
#[IsGranted('ROLE_COACH')]
class SessionCrudController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private SessionRepository $sessionRepository,
        private CoachingRequestRepository $coachingRequestRepository
    ) {
    }

    private function getCoach(): User
    {
        $user = $this->getUser();
        if (!$user instanceof User || !$user->isCoach()) {
            throw $this->createAccessDeniedException('Réservé aux coachs.');
        }
        return $user;
    }

    private function sessionBelongsToCoach(Session $session, User $coach): bool
    {
        $cr = $session->getCoachingRequest();
        return $cr && $cr->getCoach() && (int) $cr->getCoach()->getId() === (int) $coach->getId();
    }

    #[Route('', name: 'index', methods: ['GET'])]
    public function index(): Response
    {
        $coach = $this->getCoach();
        $sessions = $this->sessionRepository->findForCoach($coach);

        return $this->render('session_crud/index.html.twig', [
            'sessions' => $sessions,
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $coach = $this->getCoach();
        $requestId = $request->query->getInt('request');
        $coachingRequest = null;

        if ($requestId > 0) {
            $coachingRequest = $this->coachingRequestRepository->find($requestId);
            if (!$coachingRequest || $coachingRequest->getCoach()?->getId() !== $coach->getId()
                || $coachingRequest->getStatus() !== CoachingRequest::STATUS_ACCEPTED || $coachingRequest->getSession()) {
                $coachingRequest = null;
            }
        }
        if (!$coachingRequest) {
            $coachingRequest = $this->coachingRequestRepository->createQueryBuilder('cr')
                ->leftJoin('cr.session', 's')
                ->where('cr.status = :status')
                ->andWhere('cr.coach = :coach')
                ->andWhere('s.id IS NULL')
                ->setParameter('status', CoachingRequest::STATUS_ACCEPTED)
                ->setParameter('coach', $coach)
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult();
        }

        if (!$coachingRequest) {
            $this->addFlash('error', 'Aucune demande acceptée disponible pour créer une session.');
            return $this->redirectToRoute('app_session_crud_index');
        }

        $session = new Session();
        $session->setCoachingRequest($coachingRequest);
        $session->setStatus(Session::STATUS_SCHEDULING);

        $form = $this->createForm(SessionType::class, $session);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($session);
            $this->entityManager->flush();

            if ($request->isXmlHttpRequest() || $request->headers->get('X-Requested-With') === 'XMLHttpRequest') {
                return new JsonResponse([
                    'success' => true,
                    'message' => 'Session créée avec succès.',
                    'redirect' => $this->generateUrl('app_session_crud_index'),
                ]);
            }
            $this->addFlash('success', 'Session créée avec succès!');
            return $this->redirectToRoute('app_session_crud_index');
        }

        if (($request->isXmlHttpRequest() || $request->headers->get('X-Requested-With') === 'XMLHttpRequest') && $form->isSubmitted()) {
            $errors = [];
            foreach ($form->getErrors(true) as $error) {
                $origin = $error->getOrigin();
                $name = $origin ? $origin->getName() : 'form';
                if (!isset($errors[$name])) {
                    $errors[$name] = [];
                }
                $errors[$name][] = $error->getMessage();
            }
            return new JsonResponse([
                'success' => false,
                'message' => 'Veuillez corriger les erreurs.',
                'errors' => $errors,
            ], 422);
        }

        $acceptedWithoutSession = $this->coachingRequestRepository->findAcceptedWithoutSessionForCoach($coach);

        return $this->render('session_crud/new.html.twig', [
            'form' => $form->createView(),
            'coachingRequest' => $coachingRequest,
            'acceptedWithoutSession' => $acceptedWithoutSession,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Session $session): Response
    {
        $coach = $this->getCoach();
        if (!$this->sessionBelongsToCoach($session, $coach)) {
            throw $this->createAccessDeniedException('Session non autorisée.');
        }

        $form = $this->createForm(SessionType::class, $session);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $session->setUpdatedAt(new \DateTimeImmutable());
            $this->entityManager->flush();

            if ($request->isXmlHttpRequest() || $request->headers->get('X-Requested-With') === 'XMLHttpRequest') {
                return new JsonResponse([
                    'success' => true,
                    'message' => 'Session modifiée avec succès.',
                ]);
            }
            $this->addFlash('success', 'Session modifiée avec succès!');
            return $this->redirectToRoute('app_session_crud_index');
        }

        if (($request->isXmlHttpRequest() || $request->headers->get('X-Requested-With') === 'XMLHttpRequest') && $form->isSubmitted()) {
            $errors = [];
            foreach ($form->getErrors(true) as $error) {
                $origin = $error->getOrigin();
                $name = $origin ? $origin->getName() : 'form';
                if (!isset($errors[$name])) {
                    $errors[$name] = [];
                }
                $errors[$name][] = $error->getMessage();
            }
            return new JsonResponse([
                'success' => false,
                'message' => 'Veuillez corriger les erreurs.',
                'errors' => $errors,
            ], 422);
        }

        return $this->render('session_crud/edit.html.twig', [
            'form' => $form->createView(),
            'session' => $session,
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, Session $session): Response
    {
        $coach = $this->getCoach();
        if (!$this->sessionBelongsToCoach($session, $coach)) {
            throw $this->createAccessDeniedException('Session non autorisée.');
        }

        if (!$this->isCsrfTokenValid('delete' . $session->getId(), $request->request->get('_token'))) {
            if ($request->isXmlHttpRequest() || $request->headers->get('X-Requested-With') === 'XMLHttpRequest') {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Token CSRF invalide.',
                ], 403);
            }
            $this->addFlash('error', 'Token invalide.');
            return $this->redirectToRoute('app_session_crud_index');
        }

        $this->entityManager->remove($session);
        $this->entityManager->flush();

        if ($request->isXmlHttpRequest() || $request->headers->get('X-Requested-With') === 'XMLHttpRequest') {
            return new JsonResponse([
                'success' => true,
                'message' => 'Session supprimée avec succès.',
            ]);
        }
        $this->addFlash('success', 'Session supprimée avec succès!');
        return $this->redirectToRoute('app_session_crud_index');
    }
}
