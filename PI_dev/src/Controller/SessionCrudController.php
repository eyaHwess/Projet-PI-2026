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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/sessions/manage', name: 'app_session_crud_')]
class SessionCrudController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private SessionRepository $sessionRepository,
        private CoachingRequestRepository $coachingRequestRepository
    ) {
    }

    #[Route('', name: 'index', methods: ['GET'])]
    public function index(): Response
    {
        $sessions = $this->sessionRepository->findAll();

        return $this->render('session_crud/index.html.twig', [
            'sessions' => $sessions,
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        // Récupérer une demande acceptée sans session
        $coachingRequest = $this->coachingRequestRepository->createQueryBuilder('cr')
            ->leftJoin('cr.session', 's')
            ->where('cr.status = :status')
            ->andWhere('s.id IS NULL')
            ->setParameter('status', CoachingRequest::STATUS_ACCEPTED)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

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

            $this->addFlash('success', 'Session créée avec succès!');
            return $this->redirectToRoute('app_session_crud_index');
        }

        return $this->render('session_crud/new.html.twig', [
            'form' => $form->createView(),
            'coachingRequest' => $coachingRequest,
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Session $session): Response
    {
        $form = $this->createForm(SessionType::class, $session);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $session->setUpdatedAt(new \DateTimeImmutable());
            $this->entityManager->flush();

            $this->addFlash('success', 'Session modifiée avec succès!');
            return $this->redirectToRoute('app_session_crud_index');
        }

        return $this->render('session_crud/edit.html.twig', [
            'form' => $form->createView(),
            'session' => $session,
        ]);
    }

    #[Route('/{id}', name: 'delete', methods: ['POST'])]
    public function delete(Request $request, Session $session): Response
    {
        if ($this->isCsrfTokenValid('delete' . $session->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($session);
            $this->entityManager->flush();

            $this->addFlash('success', 'Session supprimée avec succès!');
        }

        return $this->redirectToRoute('app_session_crud_index');
    }
}
