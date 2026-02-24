<?php

namespace App\Controller;

use App\Entity\CoachingRequest;
use App\Entity\Session;
use App\Entity\User;
use App\Form\CoachingRequestType;
use App\Repository\CoachingRequestRepository;
use App\Repository\SessionRepository;
use App\Repository\TimeSlotRepository;
use App\Repository\UserRepository;
use App\AI\CompatibilityScoreEngine;
use App\Service\CoachRecommendationService;
use App\Service\DemoUserContext;
use App\Service\OpenAIService;
use App\Service\NotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/coaches', name: 'app_coach_')]
class CoachController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserRepository $userRepository,
        private CoachingRequestRepository $coachingRequestRepository,
        private SessionRepository $sessionRepository,
        private UserPasswordHasherInterface $passwordHasher,
        private ValidatorInterface $validator,
        private DemoUserContext $demoUserContext,
        private CoachRecommendationService $recommendationService,
        private NotificationService $notificationService,
        private TimeSlotRepository $timeSlotRepository,
        private OpenAIService $openAIService,
        private CompatibilityScoreEngine $compatibilityScoreEngine,
    ) {
    }

    /**
     * Retourne l'utilisateur connecté ou crée un utilisateur par défaut (mode demo)
     */
    private function getCurrentUser(): User
    {
        $user = $this->getUser();

        if ($user instanceof User) {
            return $user;
        }

        return $this->getOrCreateDefaultUser();
    }

    /**
     * Crée un utilisateur par défaut si aucun utilisateur connecté (mode demo)
     */
    private function getOrCreateDefaultUser(): User
    {
        $defaultUser = $this->userRepository->findOneBy(['email' => 'default@example.com']);

        if (!$defaultUser) {
            $defaultUser = new User();
            $defaultUser->setEmail('default@example.com');
            $defaultUser->setPassword(
                $this->passwordHasher->hashPassword($defaultUser, 'default123')
            );
            $defaultUser->setRoles(['ROLE_USER']);
            $defaultUser->setFirstName('Utilisateur');
            $defaultUser->setLastName('Par défaut');

            $this->entityManager->persist($defaultUser);
            $this->entityManager->flush();
        }

        return $defaultUser;
    }

    /**
     * Liste des coaches
     */
    #[Route('', name: 'index', methods: ['GET', 'POST'])]
    public function index(Request $request): Response
    {
        $currentUser = $this->getCurrentUser();

        // Récupération des filtres
        $speciality = $request->query->get('speciality');
        $minPrice = $request->query->get('minPrice');
        $maxPrice = $request->query->get('maxPrice');
        $minRating = $request->query->get('minRating');
        $availability = $request->query->get('availability');

        // Récupération de tous les coaches
        $coaches = $this->userRepository->findCoaches();
        
        // Application des filtres
        if ($speciality) {
            $coaches = array_filter($coaches, fn($coach) => $coach->getSpeciality() === $speciality);
        }
        if ($minPrice !== null && $minPrice !== '') {
            $coaches = array_filter($coaches, fn($coach) => $coach->getPricePerSession() >= (float)$minPrice);
        }
        if ($maxPrice !== null && $maxPrice !== '') {
            $coaches = array_filter($coaches, fn($coach) => $coach->getPricePerSession() <= (float)$maxPrice);
        }
        if ($minRating !== null && $minRating !== '') {
            $coaches = array_filter($coaches, fn($coach) => $coach->getRating() >= (float)$minRating);
        }
        if ($availability) {
            $coaches = array_filter($coaches, fn($coach) => $coach->getAvailability() === $availability);
        }
        
        $allCoachesForForm = $this->userRepository->findCoaches();

        $coachingRequest = new CoachingRequest();
        $coachingRequest->setUser($currentUser);
        $form = $this->createForm(CoachingRequestType::class, $coachingRequest, [
            'coaches' => $allCoachesForForm,
        ]);

        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $coach = $coachingRequest->getCoach();

            if ($currentUser->getId() === $coach->getId()) {
                $this->addFlash('error', 'Vous ne pouvez pas vous envoyer une demande à vous-même.');
                return $this->redirectToRoute('app_coach_index');
            }

            if ($this->coachingRequestRepository->hasPendingRequest($currentUser, $coach)) {
                $this->addFlash('warning', 'Vous avez déjà une demande en attente auprès de ce coach.');
                return $this->redirectToRoute('app_coach_index');
            }

            // Gérer le créneau sélectionné
            $timeSlotId = $request->request->get('timeSlotId');
            if ($timeSlotId) {
                $timeSlot = $this->timeSlotRepository->find($timeSlotId);
                if ($timeSlot && $timeSlot->isAvailable()) {
                    $coachingRequest->setTimeSlot($timeSlot);
                    $timeSlot->book($currentUser, $coachingRequest);
                }
            }

            $this->entityManager->persist($coachingRequest);
            $this->entityManager->flush();

            // Envoyer les notifications
            $this->notificationService->notifyCoachNewRequest($coachingRequest);
            $this->notificationService->notifyUserRequestSent($coachingRequest);

            $this->addFlash('success', 'Votre demande a été envoyée avec succès ! Le coach vous contactera bientôt.');
            return $this->redirectToRoute('app_coach_index');
        }

        // Spécialités et disponibilités pour les filtres
        $specialities = $this->userRepository->findAllCoachSpecialities();
        $allCoaches = $this->userRepository->findCoaches();
        $availabilities = array_values(array_unique(array_filter(array_map(fn($c) => $c->getAvailability(), $allCoaches))));

        return $this->render('coach/index.html.twig', [
            'coaches' => $coaches,
            'specialities' => $specialities,
            'availabilities' => $availabilities,
            'selectedSpeciality' => $speciality,
            'selectedMinPrice' => $minPrice,
            'selectedMaxPrice' => $maxPrice,
            'selectedMinRating' => $minRating,
            'selectedAvailability' => $availability,
            'myRequests' => $this->coachingRequestRepository->findByUser($currentUser),
            'currentUser' => $currentUser,
            'form' => $form->createView(),
            'isDemoSwitch' => (bool) $this->demoUserContext->getCurrentEmail(),
        ]);
    }

    /**
     * Liste des coaches - Version améliorée
     */
    #[Route('/enhanced', name: 'index_enhanced', methods: ['GET'])]
    public function indexEnhanced(Request $request): Response
    {
        $currentUser = $this->getCurrentUser();

        return $this->render('coach/index_enhanced.html.twig', [
            'currentUser' => $currentUser,
            'isDemoSwitch' => (bool) $this->demoUserContext->getCurrentEmail(),
        ]);
    }

    /**
     * Planning des sessions
     */
    #[Route('/schedule', name: 'schedule', methods: ['GET'])]
    public function schedule(): Response
    {
        $currentUser = $this->getCurrentUser();
        $acceptedWithSession = $this->coachingRequestRepository
            ->findAcceptedWithSessionForUser($currentUser);

        $sessionsToSchedule = [];

        foreach ($acceptedWithSession as $req) {
            $session = $req->getSession();

            if (
                $session &&
                !in_array($session->getStatus(), [
                    Session::STATUS_CONFIRMED,
                    Session::STATUS_COMPLETED,
                    Session::STATUS_CANCELLED,
                ])
            ) {
                $sessionsToSchedule[] = [
                    'coach' => $req->getCoach(),
                    'session' => $session,
                    'request' => $req,
                ];
            }
        }

        return $this->render('coach/schedule.html.twig', [
            'sessionsToSchedule' => $sessionsToSchedule,
            'currentUser' => $currentUser,
            'allCoaches' => $this->userRepository->findCoaches(),
            'myRequests' => $this->coachingRequestRepository->findByUser($currentUser),
        ]);
    }

    /**
     * Créer une demande de coaching (AJAX) avec validation serveur
     */
    #[Route('/request/create', name: 'request_create_ajax', methods: ['POST'])]
    public function requestCreateAjax(Request $request): JsonResponse
    {
        $currentUser = $this->getCurrentUser();

        $coachingRequest = new CoachingRequest();
        $coachingRequest->setUser($currentUser);
        $form = $this->createForm(CoachingRequestType::class, $coachingRequest, [
            'coaches' => $this->userRepository->findCoaches(),
        ]);
        $form->handleRequest($request);

        if (!$form->isSubmitted()) {
            return $this->json([
                'success' => false,
                'message' => 'Données invalides.',
                'errors' => ['form' => ['Le formulaire n\'a pas été soumis.']],
            ], 400);
        }

        if (!$form->isValid()) {
            $errors = [];
            foreach ($form->getErrors(true) as $error) {
                $origin = $error->getOrigin();
                $name = $origin ? $origin->getName() : 'form';
                if (!isset($errors[$name])) {
                    $errors[$name] = [];
                }
                $errors[$name][] = $error->getMessage();
            }
            return $this->json([
                'success' => false,
                'message' => 'Veuillez corriger les erreurs.',
                'errors' => $errors,
            ], 422);
        }

        $coach = $coachingRequest->getCoach();

        if ($currentUser->getId() === $coach->getId()) {
            return $this->json([
                'success' => false,
                'message' => 'Vous ne pouvez pas vous envoyer une demande à vous-même.',
                'errors' => ['coach' => ['Choix invalide.']],
            ], 400);
        }

        if (!$coach->isCoach()) {
            return $this->json([
                'success' => false,
                'message' => 'Cet utilisateur n\'est pas coach.',
                'errors' => ['coach' => ['Utilisateur invalide.']],
            ], 400);
        }

        if ($this->coachingRequestRepository->hasPendingRequest($currentUser, $coach)) {
            return $this->json([
                'success' => false,
                'message' => 'Vous avez déjà une demande en attente auprès de ce coach.',
                'errors' => ['coach' => ['Demande déjà en attente.']],
            ], 400);
        }

        $this->entityManager->persist($coachingRequest);
        $this->entityManager->flush();

        return $this->json([
            'success' => true,
            'message' => 'Votre demande a été envoyée avec succès ! Le coach vous contactera bientôt.',
        ]);
    }

    /**
     * Envoyer une demande de coaching (legacy, par ID coach)
     */
    #[Route('/{id}/request', name: 'request', methods: ['POST'])]
    public function request(User $coach, Request $request): JsonResponse
    {
        if (!$coach->isCoach()) {
            return $this->json([
                'success' => false,
                'message' => 'Cet utilisateur n\'est pas coach.',
            ], 400);
        }

        $user = $this->getCurrentUser();

        if ($user->getId() === $coach->getId()) {
            return $this->json([
                'success' => false,
                'message' => 'Vous ne pouvez pas vous envoyer une demande à vous-même.',
            ], 400);
        }

        if ($this->coachingRequestRepository->hasPendingRequest($user, $coach)) {
            return $this->json([
                'success' => false,
                'message' => 'Vous avez déjà une demande en attente auprès de ce coach.',
            ], 400);
        }

        if (
            !$this->isCsrfTokenValid(
                'coach-request' . $coach->getId(),
                $request->request->get('_token')
            )
        ) {
            return $this->json([
                'success' => false,
                'message' => 'Token CSRF invalide.',
            ], 403);
        }

        $coachingRequest = new CoachingRequest();
        $coachingRequest->setUser($user);
        $coachingRequest->setCoach($coach);

        $this->entityManager->persist($coachingRequest);
        $this->entityManager->flush();

        return $this->json([
            'success' => true,
            'message' => 'Demande envoyée avec succès ! Le coach vous contactera bientôt.',
        ]);
    }

    #[Route('/recommendations', name: 'recommendations', methods: ['POST'])]
    public function getRecommendations(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);
        $message = $data['message'] ?? '';

        if (\strlen($message) < 10) {
            return $this->json([
                'success' => false,
                'message' => 'Le message doit contenir au moins 10 caractères pour obtenir des recommandations.',
            ], 400);
        }

        $message = substr(trim($message), 0, 1000);

        $analysis = $this->openAIService->analyzeMessage($message);
        $categories = $analysis['categories'];
        $emotion = $analysis['emotion'];

        $coaches = $this->userRepository->findCoaches();

        $scored = [];
        foreach ($coaches as $coach) {
            $score = $this->compatibilityScoreEngine->calculate($coach, $categories, $emotion);
            $scored[] = ['coach' => $coach, 'score' => $score];
        }

        usort($scored, fn($a, $b) => $b['score'] <=> $a['score']);

        $maxScore = 0;
        foreach ($scored as $item) {
            if ($item['score'] > $maxScore) {
                $maxScore = $item['score'];
            }
        }

        $recommendations = [];
        foreach ($scored as $item) {
            $coach = $item['coach'];
            $rawScore = $item['score'];
            $percent = $maxScore > 0 ? (int) round(($rawScore / $maxScore) * 100) : 0;

            $reasons = $this->buildReasons($coach, $categories, $emotion, $percent);

            $recommendations[] = [
                'id' => $coach->getId(),
                'firstName' => $coach->getFirstName(),
                'lastName' => $coach->getLastName(),
                'email' => $coach->getEmail(),
                'speciality' => $coach->getSpeciality(),
                'rating' => $coach->getRating(),
                'pricePerSession' => $coach->getPricePerSession(),
                'availability' => $coach->getAvailability(),
                'score' => $percent,
                'reasons' => $reasons,
            ];
        }

        return $this->json([
            'success' => true,
            'recommendations' => array_slice($recommendations, 0, 15),
        ]);
    }

    private function buildReasons($coach, array $categories, ?string $emotion, int $percent): array
    {
        $reasons = [];
        $coachCategories = $this->compatibilityScoreEngine->getCoachCategories($coach);

        foreach ($categories as $cat) {
            if (\in_array($cat, $coachCategories, true)) {
                $reasons[] = 'Spécialiste en ' . $cat;
            }
        }
        if ($emotion === 'stress' && \in_array('mental', $coachCategories, true)) {
            $reasons[] = 'Idéal pour gérer le stress';
        }
        if ($coach->getRating() && $coach->getRating() >= 4) {
            $reasons[] = 'Bien noté (' . $coach->getRating() . '/5)';
        }
        if (empty($reasons)) {
            $reasons[] = 'Recommandé par notre IA (' . $percent . '%)';
        }
        return $reasons;
    }
}
