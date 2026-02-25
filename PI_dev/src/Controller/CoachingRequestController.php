<?php

namespace App\Controller;

use App\Entity\CoachingRequest;
use App\Entity\Session;
use App\Entity\User;
use App\Repository\CoachingRequestRepository;
use App\Repository\UserRepository;
use App\Service\DemoUserContext;
use App\Service\NotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Espace coach : voir et gérer les demandes de coaching.
 * Réservé aux utilisateurs avec le rôle ROLE_COACH.
 */
#[Route('/coach', name: 'app_coaching_request_')]
#[IsGranted('ROLE_COACH')]
class CoachingRequestController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserRepository $userRepository,
        private CoachingRequestRepository $coachingRequestRepository,
        private UserPasswordHasherInterface $passwordHasher,
        private DemoUserContext $demoUserContext,
        private NotificationService $notificationService
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
        $authenticated = $this->getUser();
        if ($authenticated instanceof User) {
            $user = $this->demoUserContext->getCurrentUser($authenticated);
            return $user ?? $authenticated;
        }
        return $this->getOrCreateDefaultUser();
    }

    /**
     * Liste des demandes de coaching reçues par le coach connecté.
     */
    #[Route('/requests', name: 'index', methods: ['GET'])]
    public function index(): Response
    {
        $currentUser = $this->getCurrentUser();
        $pendingRequests = $this->coachingRequestRepository->findPendingForCoach($currentUser);
        $allRequests = $this->coachingRequestRepository->findAllForCoach($currentUser);

        // Calculer les statistiques
        $stats = [
            'total' => count($allRequests),
            'pending' => count($pendingRequests),
            'accepted' => $this->coachingRequestRepository->countByStatusForCoach($currentUser, CoachingRequest::STATUS_ACCEPTED),
            'declined' => $this->coachingRequestRepository->countByStatusForCoach($currentUser, CoachingRequest::STATUS_DECLINED),
            'urgent' => $this->coachingRequestRepository->countByPriorityForCoach($currentUser, CoachingRequest::PRIORITY_URGENT),
            'medium' => $this->coachingRequestRepository->countByPriorityForCoach($currentUser, CoachingRequest::PRIORITY_MEDIUM),
            'normal' => $this->coachingRequestRepository->countByPriorityForCoach($currentUser, CoachingRequest::PRIORITY_NORMAL),
        ];

        return $this->render('coaching_request/index.html.twig', [
            'pendingRequests' => $pendingRequests,
            'allRequests' => $allRequests,
            'currentUser' => $currentUser,
            'isDemoSwitch' => (bool) $this->demoUserContext->getCurrentEmail(),
            'stats' => $stats,
        ]);
    }

    #[Route('/requests/{id}/accept', name: 'accept', methods: ['POST'])]
    public function accept(CoachingRequest $coachingRequest, Request $request): JsonResponse|Response
    {
        $currentUser = $this->getCurrentUser();

        if (!$currentUser->isCoach() || $coachingRequest->getCoach()?->getId() !== $currentUser->getId()) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Non autorisé.',
            ], 403);
        }

        if ($coachingRequest->getStatus() !== CoachingRequest::STATUS_PENDING) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Cette demande a déjà été traitée.',
            ], 400);
        }

        if (!$this->isCsrfTokenValid('accept-request' . $coachingRequest->getId(), $request->request->get('_token'))) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Token CSRF invalide.',
            ], 403);
        }

        $coachingRequest->setStatus(CoachingRequest::STATUS_ACCEPTED);

        $session = new Session();
        $session->setCoachingRequest($coachingRequest);
        $coachingRequest->setSession($session);

        $this->entityManager->persist($session);
        $this->entityManager->flush();

        // Créer une notification pour l'utilisateur
        $this->notificationService->notifyRequestAccepted($coachingRequest);

        if ($request->isXmlHttpRequest() || $request->headers->get('X-Requested-With') === 'XMLHttpRequest') {
            return new JsonResponse([
                'success' => true,
                'message' => 'Demande acceptée ! Vous pouvez maintenant proposer un créneau pour la session.',
                'redirect' => $this->generateUrl('app_session_schedule', ['id' => $session->getId()]),
            ]);
        }

        $this->addFlash('success', 'Demande acceptée ! Proposez un créneau pour la session.');
        return $this->redirectToRoute('app_session_schedule', ['id' => $session->getId()]);
    }

    #[Route('/requests/{id}/decline', name: 'decline', methods: ['POST'])]
    public function decline(CoachingRequest $coachingRequest, Request $request): JsonResponse|Response
    {
        $currentUser = $this->getCurrentUser();

        if (!$currentUser->isCoach() || $coachingRequest->getCoach()?->getId() !== $currentUser->getId()) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Non autorisé.',
            ], 403);
        }

        if ($coachingRequest->getStatus() !== CoachingRequest::STATUS_PENDING) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Cette demande a déjà été traitée.',
            ], 400);
        }

        if (!$this->isCsrfTokenValid('decline-request' . $coachingRequest->getId(), $request->request->get('_token'))) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Token CSRF invalide.',
            ], 403);
        }

        $coachingRequest->setStatus(CoachingRequest::STATUS_DECLINED);
        $this->entityManager->flush();

        // Créer une notification pour l'utilisateur
        $this->notificationService->notifyRequestDeclined($coachingRequest);

        if ($request->isXmlHttpRequest() || $request->headers->get('X-Requested-With') === 'XMLHttpRequest') {
            return new JsonResponse([
                'success' => true,
                'message' => 'Demande refusée.',
            ]);
        }

        $this->addFlash('info', 'Demande refusée.');
        return $this->redirectToRoute('app_coaching_request_index');
    }

    #[Route('/requests/{id}/pending', name: 'pending', methods: ['POST'])]
    public function setPending(CoachingRequest $coachingRequest, Request $request): JsonResponse|Response
    {
        $currentUser = $this->getCurrentUser();

        if (!$currentUser->isCoach() || $coachingRequest->getCoach()?->getId() !== $currentUser->getId()) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Non autorisé.',
            ], 403);
        }

        if (!$this->isCsrfTokenValid('pending-request' . $coachingRequest->getId(), $request->request->get('_token'))) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Token CSRF invalide.',
            ], 403);
        }

        $coachingRequest->setStatus(CoachingRequest::STATUS_PENDING);
        // Réinitialiser la date de réponse
        $reflection = new \ReflectionClass($coachingRequest);
        $property = $reflection->getProperty('respondedAt');
        $property->setAccessible(true);
        $property->setValue($coachingRequest, null);
        
        $this->entityManager->flush();

        // Créer une notification pour l'utilisateur
        $this->notificationService->notifyRequestPending($coachingRequest);

        if ($request->isXmlHttpRequest() || $request->headers->get('X-Requested-With') === 'XMLHttpRequest') {
            return new JsonResponse([
                'success' => true,
                'message' => 'Demande remise en attente.',
            ]);
        }

        $this->addFlash('info', 'Demande remise en attente.');
        return $this->redirectToRoute('app_coaching_request_index');
    }
    /**
     * Créer une demande de coaching via AJAX (pour les utilisateurs)
     */
    #[Route('/create-ajax', name: 'create_ajax', methods: ['POST'])]
    public function createAjax(Request $request): JsonResponse
    {
        $currentUser = $this->getCurrentUser();

        // Vérifier que l'utilisateur est authentifié
        if (!$currentUser) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Vous devez être connecté pour faire une demande.',
            ], 401);
        }

        // Récupérer les données du formulaire
        $coachId = $request->request->get('coaching_request')['coach'] ?? null;
        $goal = $request->request->get('coaching_request')['goal'] ?? null;
        $level = $request->request->get('coaching_request')['level'] ?? null;
        $frequency = $request->request->get('coaching_request')['frequency'] ?? null;
        $budget = $request->request->get('coaching_request')['budget'] ?? null;
        $message = $request->request->get('coaching_request')['message'] ?? null;

        // Validation
        $errors = [];

        if (!$coachId) {
            $errors[] = 'Veuillez sélectionner un coach.';
        }

        if (!$message || strlen(trim($message)) < 10) {
            $errors[] = 'Le message doit contenir au moins 10 caractères.';
        }

        if ($message && strlen($message) > 1000) {
            $errors[] = 'Le message ne peut pas dépasser 1000 caractères.';
        }

        if (!empty($errors)) {
            return new JsonResponse([
                'success' => false,
                'message' => implode(' ', $errors),
                'errors' => $errors,
            ], 400);
        }

        // Récupérer le coach
        $coach = $this->userRepository->find($coachId);
        if (!$coach || !$coach->isCoach()) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Coach introuvable.',
            ], 404);
        }

        // Vérifier qu'on ne fait pas une demande à soi-même
        if ($coach->getId() === $currentUser->getId()) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Vous ne pouvez pas faire une demande à vous-même.',
            ], 400);
        }

        // Créer la demande
        $coachingRequest = new CoachingRequest();
        $coachingRequest->setUser($currentUser);
        $coachingRequest->setCoach($coach);
        $coachingRequest->setMessage(trim($message));

        if ($goal) {
            $coachingRequest->setGoal($goal);
        }
        if ($level) {
            $coachingRequest->setLevel($level);
        }
        if ($frequency) {
            $coachingRequest->setFrequency($frequency);
        }
        if ($budget) {
            $coachingRequest->setBudget((float) $budget);
        }

        // Détecter automatiquement la priorité basée sur le message
        $coachingRequest->detectAndSetPriority();

        try {
            $this->entityManager->persist($coachingRequest);
            $this->entityManager->flush();

            // Notifier le coach de la nouvelle demande
            $this->notificationService->notifyCoachNewRequest($coachingRequest);

            return new JsonResponse([
                'success' => true,
                'message' => 'Votre demande a été envoyée avec succès !',
                'requestId' => $coachingRequest->getId(),
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Une erreur est survenue lors de l\'envoi de votre demande.',
            ], 500);
        }
    }
}
