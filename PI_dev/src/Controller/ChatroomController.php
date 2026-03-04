<?php

namespace App\Controller;

use App\Entity\Chatroom;
use App\Entity\Message;
use App\Entity\GoalParticipation;
use App\Entity\User;
use App\Form\MessageType;
use App\Service\ModerationService;
use App\Repository\UserRepository;
use App\Repository\GoalParticipationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class ChatroomController extends AbstractController
{
    public function __construct(
        private ModerationService $moderationService
    ) {}

    #[Route('/chatroom/{id}', name: 'chatroom_show', requirements: ['id' => '\d+'])]
    public function show(
        Chatroom $chatroom,
        Request $request,
        EntityManagerInterface $em
    ): Response {

        // Vérifier que l'utilisateur est connecté
        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour accéder au chatroom.');
            return $this->redirectToRoute('app_login');
        }

        $goal = $chatroom->getGoal();

        // Vérifier que l'utilisateur est membre du goal
        $participation = $em->getRepository(\App\Entity\GoalParticipation::class)->findOneBy([
            'goal' => $goal,
            'user' => $user
        ]);

        if (!$participation) {
            $this->addFlash('error', 'Vous devez rejoindre ce goal pour accéder au chatroom.');
            return $this->redirectToRoute('goal_list');
        }

        // Vérifier que la participation est approuvée
        if (!$participation->isApproved()) {
            $this->addFlash('warning', 'Votre demande d\'accès est en attente d\'approbation.');
            return $this->redirectToRoute('goal_list');
        }

        $message = new Message();

        $form = $this->createForm(MessageType::class, $message);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Modération du contenu avant enregistrement
            $content = $message->getContent();
            if ($content && trim($content) !== '') {
                $moderationResult = $this->moderationService->analyzeMessage($content);
                
                // Appliquer les résultats de modération
                $message->setIsToxic($moderationResult['isToxic']);
                $message->setIsSpam($moderationResult['isSpam']);
                $message->setToxicityScore($moderationResult['toxicityScore']);
                $message->setSpamScore($moderationResult['spamScore']);
                $message->setModerationStatus($moderationResult['moderationStatus']);
                $message->setModerationReason($moderationResult['moderationReason']);

                // Si le message est bloqué, ne pas l'enregistrer et afficher un message
                if ($moderationResult['moderationStatus'] === 'blocked') {
                    $this->addFlash('error', $moderationResult['moderationReason']);
                    return $this->redirectToRoute('chatroom_show', [
                        'id' => $chatroom->getId()
                    ]);
                }

                // Si le message est spam, afficher un avertissement
                if ($moderationResult['moderationStatus'] === 'hidden') {
                    $this->addFlash('warning', 'Votre message a été marqué comme spam et sera masqué pour les autres utilisateurs.');
                }
            }

            // VichUploader: route form attachment to imageFile or file
            $attachmentFile = $form->get('attachment')->getData();
            if ($attachmentFile) {
                $mimeType = $attachmentFile->getMimeType();
                if (str_starts_with($mimeType ?? '', 'image/')) {
                    $message->setImageFile($attachmentFile);
                } else {
                    $message->setFile($attachmentFile);
                }
            }

            $message->setAuthor($user);
            $message->setChatroom($chatroom);
            $message->setCreatedAt(new \DateTime());

            $em->persist($message);
            $em->flush();

            return $this->redirectToRoute('chatroom_show', [
                'id' => $chatroom->getId()
            ]);
        }

        return $this->render('chatroom/chatroom_modern.html.twig', [
            'chatroom' => $chatroom,
            'goal' => $goal,
            'form' => $form->createView(),
            'userParticipation' => $participation,
        ]);
    }

    /**
     * Rechercher des utilisateurs pour les ajouter au chatroom
     */
    #[Route('/chatroom/{id}/search-users', name: 'chatroom_search_users', methods: ['GET'])]
    public function searchUsers(
        Chatroom $chatroom,
        Request $request,
        UserRepository $userRepo,
        GoalParticipationRepository $gpRepo
    ): JsonResponse {
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'Non authentifié'], 401);
        }

        $goal = $chatroom->getGoal();
        
        // Vérifier que l'utilisateur a le droit d'ajouter des membres
        $userParticipation = $gpRepo->findOneBy(['goal' => $goal, 'user' => $user]);
        if (!$userParticipation || !$userParticipation->canModerate()) {
            return new JsonResponse(['error' => 'Accès refusé'], 403);
        }

        $query = $request->query->get('q', '');
        
        if (strlen($query) < 2) {
            return new JsonResponse(['users' => []]);
        }

        // Rechercher les utilisateurs qui ne sont pas déjà membres
        $allUsers = $userRepo->createQueryBuilder('u')
            ->where('u.firstName LIKE :query OR u.lastName LIKE :query OR u.email LIKE :query')
            ->setParameter('query', '%' . $query . '%')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();

        // Filtrer ceux qui sont déjà membres
        $existingMemberIds = array_map(
            fn($p) => $p->getUser()->getId(),
            $gpRepo->findBy(['goal' => $goal])
        );

        $availableUsers = array_filter($allUsers, fn($u) => !in_array($u->getId(), $existingMemberIds));

        $results = array_map(function($u) {
            return [
                'id' => $u->getId(),
                'name' => $u->getFirstName() . ' ' . $u->getLastName(),
                'email' => $u->getEmail(),
                'hasProfilePicture' => $u->hasProfilePicture(),
                'initials' => substr($u->getFirstName(), 0, 1) . substr($u->getLastName(), 0, 1)
            ];
        }, $availableUsers);

        return new JsonResponse(['users' => array_values($results)]);
    }

    /**
     * Ajouter un membre au chatroom (via GoalParticipation)
     */
    #[Route('/chatroom/{id}/add-member/{userId}', name: 'chatroom_add_member', methods: ['POST'])]
    public function addMember(
        Chatroom $chatroom,
        int $userId,
        UserRepository $userRepo,
        GoalParticipationRepository $gpRepo,
        EntityManagerInterface $em
    ): JsonResponse {
        $currentUser = $this->getUser();
        if (!$currentUser) {
            return new JsonResponse(['error' => 'Non authentifié'], 401);
        }

        $goal = $chatroom->getGoal();
        
        // Vérifier que l'utilisateur actuel a le droit d'ajouter des membres
        $currentUserParticipation = $gpRepo->findOneBy(['goal' => $goal, 'user' => $currentUser]);
        if (!$currentUserParticipation || !$currentUserParticipation->canModerate()) {
            return new JsonResponse(['error' => 'Vous n\'avez pas la permission d\'ajouter des membres'], 403);
        }

        // Récupérer l'utilisateur à ajouter
        $userToAdd = $userRepo->find($userId);
        if (!$userToAdd) {
            return new JsonResponse(['error' => 'Utilisateur introuvable'], 404);
        }

        // Vérifier si l'utilisateur n'est pas déjà membre
        $existingParticipation = $gpRepo->findOneBy(['goal' => $goal, 'user' => $userToAdd]);
        if ($existingParticipation) {
            return new JsonResponse(['error' => 'Cet utilisateur est déjà membre'], 400);
        }

        // Créer la participation
        $participation = new GoalParticipation();
        $participation->setUser($userToAdd);
        $participation->setGoal($goal);
        $participation->setRole('MEMBER');
        $participation->setStatus('APPROVED'); // Approuvé directement par l'admin
        $participation->setCreatedAt(new \DateTime());

        $em->persist($participation);
        $em->flush();

        return new JsonResponse([
            'success' => true,
            'message' => $userToAdd->getFirstName() . ' ' . $userToAdd->getLastName() . ' a été ajouté au chatroom',
            'user' => [
                'id' => $userToAdd->getId(),
                'name' => $userToAdd->getFirstName() . ' ' . $userToAdd->getLastName(),
                'role' => 'MEMBER'
            ]
        ]);
    }
}
