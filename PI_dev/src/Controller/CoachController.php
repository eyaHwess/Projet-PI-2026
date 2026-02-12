<?php

namespace App\Controller;

use App\Entity\CoachingRequest;
use App\Entity\Session;
use App\Entity\User;
use App\Form\CoachingRequestType;
use App\Repository\CoachingRequestRepository;
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
        private DemoUserContext $demoUserContext
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
        
        // Créer le formulaire de demande
        $coachingRequest = new CoachingRequest();
        $coachingRequest->setUser($currentUser);
        $form = $this->createForm(CoachingRequestType::class, $coachingRequest);
        
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $coach = $coachingRequest->getCoach();
            
            // Vérifier si l'utilisateur ne s'envoie pas une demande à lui-même
            if ($currentUser->getId() === $coach->getId()) {
                $this->addFlash('error', 'Vous ne pouvez pas vous envoyer une demande à vous-même.');
                return $this->redirectToRoute('app_coach_index');
            }
            
            // Vérifier s'il n'y a pas déjà une demande en attente
            if ($this->coachingRequestRepository->hasPendingRequest($currentUser, $coach)) {
                $this->addFlash('warning', 'Vous avez déjà une demande en attente auprès de ce coach.');
                return $this->redirectToRoute('app_coach_index');
            }
            
            $this->entityManager->persist($coachingRequest);
            $this->entityManager->flush();
            
            $this->addFlash('success', 'Votre demande a été envoyée avec succès ! Le coach vous contactera bientôt.');
            return $this->redirectToRoute('app_coach_index');
        }
        
        // Filtrer par spécialité si demandé
        $speciality = $request->query->get('speciality');
        $coaches = $speciality 
            ? $this->userRepository->findCoachesBySpeciality($speciality)
            : $this->userRepository->findCoaches();
        
        // Récupérer toutes les spécialités disponibles
        $specialities = $this->userRepository->findAllCoachSpecialities();

        return $this->render('coach/index.html.twig', [
            'coaches' => $coaches,
            'specialities' => $specialities,
            'selectedSpeciality' => $speciality,
            'myRequests' => $this->coachingRequestRepository->findByUser($currentUser),
            'currentUser' => $currentUser,
            'form' => $form->createView(),
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
     * Envoyer une demande de coaching
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
}
