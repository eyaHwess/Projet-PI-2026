<?php

namespace App\Controller;

use App\Entity\Session;
use App\Entity\User;
use App\Form\SessionCoachScheduleType;
use App\Form\SessionScheduleType;
use App\Form\SessionType;
use App\Repository\SessionRepository;
use App\Repository\UserRepository;
use App\Service\DemoUserContext;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Knp\Component\Pager\PaginatorInterface;

#[Route('/sessions', name: 'app_session_')]
class SessionController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserRepository $userRepository,
        private SessionRepository $sessionRepository,
        private UserPasswordHasherInterface $passwordHasher,
        private DemoUserContext $demoUserContext
    ) {
    }

    private function getOrCreateDefaultUser(): User
    {
        $defaultUser = $this->userRepository->findOneBy(['email' => 'default@example.com']);

        if (!$defaultUser) {
            $defaultUser = new User();
            $defaultUser->setEmail('default@example.com');
            $defaultUser->setPassword($this->passwordHasher->hashPassword($defaultUser, 'default123'));
            $defaultUser->setRoles(['ROLE_USER']);
            $defaultUser->setFirstName('Utilisateur');
            $defaultUser->setLastName('Par défaut');
            $this->entityManager->persist($defaultUser);
            $this->entityManager->flush();
        }

        return $defaultUser;
    }

    private function getCurrentUser(): User
    {
        $user = $this->demoUserContext->getCurrentUser($this->getUser());
        return $user ?? $this->getOrCreateDefaultUser();
    }

   #[Route('', name: 'index', methods: ['GET'])]
public function index(Request $request, PaginatorInterface $paginator): Response
{
    $currentUser = $this->getCurrentUser();

    // On récupère la requête de tes sessions
    $query = $this->sessionRepository->createQueryBuilder('s')
        ->join('s.coachingRequest', 'cr')
        ->where('cr.user = :user OR cr.coach = :user')
        ->setParameter('user', $currentUser)
        ->orderBy('s.updatedAt', 'DESC')
        ->getQuery();

    // Pagination : 5 éléments par page
    $sessions = $paginator->paginate(
        $query,
        $request->query->getInt('page', 1),
        1
    );

    return $this->render('session/index.html.twig', [
        'sessions' => $sessions,
        'currentUser' => $currentUser,
    ]);
}

    #[Route('/{id}/schedule', name: 'schedule', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function schedule(Session $session, Request $request): Response
    {
        $currentUser = $this->getCurrentUser();
        $cr = $session->getCoachingRequest();

        if (!$cr) {
            throw $this->createNotFoundException('Session invalide.');
        }

        $isCoach = $currentUser->getId() === $cr->getCoach()?->getId();
        $isUser = $currentUser->getId() === $cr->getUser()?->getId();

        if (!$isCoach && !$isUser) {
            throw $this->createAccessDeniedException('Vous n\'avez pas accès à cette session.');
        }

        if (in_array($session->getStatus(), [Session::STATUS_CONFIRMED, Session::STATUS_COMPLETED, Session::STATUS_CANCELLED])) {
            return $this->redirectToRoute('app_session_show', ['id' => $session->getId()]);
        }

        if ($isCoach) {
            $form = $this->createForm(SessionCoachScheduleType::class, $session);
        } else {
            $form = $this->createForm(SessionScheduleType::class, $session);
        }

        // Vérifier si un créneau existe déjà (avant handleRequest qui modifie l'entité)
        $hadExistingSlot = ($isCoach && $session->getProposedTimeByCoach() !== null) 
            || (!$isCoach && $session->getProposedTimeByUser() !== null);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $session->setUpdatedAt(new \DateTimeImmutable());

            if ($isCoach) {
                $session->setStatus(Session::STATUS_PROPOSED_BY_COACH);
            } else {
                $session->setStatus(Session::STATUS_PROPOSED_BY_USER);
            }

            $this->entityManager->flush();

            if ($hadExistingSlot) {
                $this->addFlash('success', 'Créneau modifié avec succès ! L\'autre partie peut confirmer ou proposer un autre horaire.');
            } else {
                $this->addFlash('success', 'Créneau proposé ! L\'autre partie peut confirmer ou proposer un autre horaire.');
            }

            return $this->redirectToRoute('app_session_schedule', ['id' => $session->getId()]);
        }

        return $this->render('session/schedule.html.twig', [
            'session' => $session,
            'form' => $form,
            'isCoach' => $isCoach,
        ]);
    }

    #[Route('/{id}/confirm-time', name: 'confirm_time', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function confirmTime(Session $session, Request $request): JsonResponse|Response
    {
        $currentUser = $this->getCurrentUser();
        $cr = $session->getCoachingRequest();

        if (!$cr) {
            return new JsonResponse(['success' => false, 'message' => 'Session invalide.'], 404);
        }

        $isCoach = $currentUser->getId() === $cr->getCoach()?->getId();
        $isUser = $currentUser->getId() === $cr->getUser()?->getId();

        if (!$isCoach && !$isUser) {
            return new JsonResponse(['success' => false, 'message' => 'Non autorisé.'], 403);
        }

        $proposedTime = $isCoach ? $session->getProposedTimeByUser() : $session->getProposedTimeByCoach();
        if (!$proposedTime) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Aucun créneau à confirmer.',
            ], 400);
        }

        if (!$this->isCsrfTokenValid('confirm-session' . $session->getId(), $request->request->get('_token'))) {
            return new JsonResponse(['success' => false, 'message' => 'Token CSRF invalide.'], 403);
        }

        $session->setScheduledAt($proposedTime);
        $session->setStatus(Session::STATUS_CONFIRMED);
        $session->setUpdatedAt(new \DateTimeImmutable());
        $this->entityManager->flush();

        if ($request->isXmlHttpRequest() || $request->headers->get('X-Requested-With') === 'XMLHttpRequest') {
            return new JsonResponse([
                'success' => true,
                'message' => 'Session confirmée !',
            ]);
        }

        $this->addFlash('success', 'Session confirmée !');
        return $this->redirectToRoute('app_session_show', ['id' => $session->getId()]);
    }

    /**
     * Supprimer le créneau proposé (par le coach ou l'utilisateur) pour pouvoir en proposer un autre.
     */
    #[Route('/{id}/clear-slot', name: 'clear_slot', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function clearSlot(Session $session, Request $request): JsonResponse|Response
    {
        $currentUser = $this->getCurrentUser();
        $cr = $session->getCoachingRequest();

        if (!$cr) {
            throw $this->createNotFoundException('Session invalide.');
        }

        $isCoach = $currentUser->getId() === $cr->getCoach()?->getId();
        $isUser = $currentUser->getId() === $cr->getUser()?->getId();

        if (!$isCoach && !$isUser) {
            throw $this->createAccessDeniedException('Vous n\'avez pas accès à cette session.');
        }

        if (!in_array($session->getStatus(), [Session::STATUS_PROPOSED_BY_COACH, Session::STATUS_PROPOSED_BY_USER], true)) {
            $this->addFlash('info', 'Aucun créneau à supprimer.');
            return $this->redirectToRoute('app_session_show', ['id' => $session->getId()]);
        }

        $canClear = ($isCoach && $session->getStatus() === Session::STATUS_PROPOSED_BY_COACH)
            || ($isUser && $session->getStatus() === Session::STATUS_PROPOSED_BY_USER);
        if (!$canClear) {
            $this->addFlash('warning', 'Seul celui qui a proposé le créneau peut le supprimer.');
            return $this->redirectToRoute('app_session_show', ['id' => $session->getId()]);
        }

        if (!$this->isCsrfTokenValid('clear-slot' . $session->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Token de sécurité invalide.');
            return $this->redirectToRoute('app_session_show', ['id' => $session->getId()]);
        }

        if ($isCoach) {
            $session->setProposedTimeByCoach(null);
        } else {
            $session->setProposedTimeByUser(null);
        }
        $session->setStatus(Session::STATUS_SCHEDULING);
        $session->setUpdatedAt(new \DateTimeImmutable());
        $this->entityManager->flush();

        $this->addFlash('success', 'Créneau supprimé. Vous pouvez en proposer un autre.');
        return $this->redirectToRoute('app_session_schedule', ['id' => $session->getId()]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(Session $session): Response
    {
        $currentUser = $this->getCurrentUser();
        $cr = $session->getCoachingRequest();

        if (!$cr) {
            throw $this->createNotFoundException('Session invalide.');
        }

        $isCoach = $currentUser->getId() === $cr->getCoach()?->getId();
        $isUser = $currentUser->getId() === $cr->getUser()?->getId();

        if (!$isCoach && !$isUser) {
            throw $this->createAccessDeniedException('Vous n\'avez pas accès à cette session.');
        }

        return $this->render('session/show.html.twig', [
            'session' => $session,
            'isCoach' => $isCoach,
        ]);
    }

    /**
     * Édition d'une session. Réservée au coach qui gère cette session.
     */
    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function edit(Session $session, Request $request): Response
    {
        $currentUser = $this->getCurrentUser();
        $cr = $session->getCoachingRequest();

        if (!$cr) {
            throw $this->createNotFoundException('Session invalide.');
        }

        $isCoach = $currentUser->getId() === $cr->getCoach()?->getId();
        if (!$isCoach) {
            throw $this->createAccessDeniedException('Seul le coach peut modifier la session.');
        }

        $form = $this->createForm(SessionType::class, $session);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $session->setUpdatedAt(new \DateTimeImmutable());
            $this->entityManager->flush();

            $this->addFlash('success', 'Session mise à jour.');
            return $this->redirectToRoute('app_session_show', ['id' => $session->getId()]);
        }

        return $this->render('session/edit.html.twig', [
            'session' => $session,
            'form' => $form,
            'isCoach' => true,
        ]);
    }

    /**
     * Annulation/suppression d'une session. Réservée au coach qui gère cette session.
     */
    #[Route('/{id}/delete', name: 'delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function delete(Session $session, Request $request): Response
    {
        $currentUser = $this->getCurrentUser();
        $cr = $session->getCoachingRequest();

        if (!$cr) {
            throw $this->createNotFoundException('Session invalide.');
        }

        $isCoach = $currentUser->getId() === $cr->getCoach()?->getId();
        if (!$isCoach) {
            throw $this->createAccessDeniedException('Seul le coach peut annuler la session.');
        }

        if (!$this->isCsrfTokenValid('delete-session' . $session->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide.');
            return $this->redirectToRoute('app_session_index');
        }

        $session->setStatus(Session::STATUS_CANCELLED);
        $session->setUpdatedAt(new \DateTimeImmutable());
        $this->entityManager->flush();

        $this->addFlash('success', 'Session annulée.');
        return $this->redirectToRoute('app_session_index');
    }
}
