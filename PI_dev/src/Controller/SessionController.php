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
    public function index(): Response
    {
        $currentUser = $this->getCurrentUser();
        $sessions = $this->sessionRepository->findAllForUser($currentUser);

        return $this->render('session/index.html.twig', [
            'sessions' => $sessions,
            'currentUser' => $currentUser,
        ]);
    }

    #[Route('/{id}/schedule', name: 'schedule', methods: ['GET', 'POST'])]
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

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $session->setUpdatedAt(new \DateTimeImmutable());

            if ($isCoach) {
                $session->setStatus(Session::STATUS_PROPOSED_BY_COACH);
            } else {
                $session->setStatus(Session::STATUS_PROPOSED_BY_USER);
            }

            $this->entityManager->flush();

            $this->addFlash('success', 'Créneau proposé ! L\'autre partie peut confirmer ou proposer un autre horaire.');

            return $this->redirectToRoute('app_session_show', ['id' => $session->getId()]);
        }

        return $this->render('session/schedule.html.twig', [
            'session' => $session,
            'form' => $form,
            'isCoach' => $isCoach,
        ]);
    }

    #[Route('/{id}/confirm-time', name: 'confirm_time', methods: ['POST'])]
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

    #[Route('/{id}', name: 'show', methods: ['GET'])]
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

    #[Route('/{id}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(Session $session, Request $request): Response
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
            'isCoach' => $isCoach,
        ]);
    }

    #[Route('/{id}/delete', name: 'delete', methods: ['POST'])]
    public function delete(Session $session, Request $request): Response
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
