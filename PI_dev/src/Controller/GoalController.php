<?php

namespace App\Controller;
use App\Entity\Chatroom;
use App\Entity\Goal;
use App\Entity\GoalParticipation;
use App\Entity\Message;
use App\Entity\MessageReaction;
use App\Entity\MessageReadReceipt;
use App\Entity\User;
use App\Form\GoalType;
use App\Form\MessageType;
use App\Repository\ChatroomRepository;
use App\Repository\GoalRepository;
use App\Repository\MessageReactionRepository;
use App\Repository\MessageReadReceiptRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class GoalController extends AbstractController
{
    public function __construct(
        private ChatroomRepository $chatroomRepository
    ) {}

    #[Route('/goals', name: 'goal_list')]
    public function list(GoalRepository $goalRepository, MessageReadReceiptRepository $readReceiptRepo): Response
    {
        // Accessible sans authentification
        
        return $this->render('goal/list.html.twig', [
            'goals' => $goalRepository->findGoalsWithParticipants(),
            'readReceiptRepo' => $readReceiptRepo,
        ]);
    }

    #[Route('/goal/new', name: 'goal_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        // Pas de restriction d'authentification pour voir le formulaire
        
        $goal = new Goal();
        $form = $this->createForm(GoalType::class, $goal);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Créer automatiquement le chatroom
            $chatroom = new Chatroom();
            $chatroom->setCreatedAt(new \DateTime());
            $chatroom->setGoal($goal);

            // Créer automatiquement la participation du créateur (si connecté)
            if ($this->getUser()) {
                $participation = new GoalParticipation();
                $participation->setGoal($goal);
                $participation->setUser($this->getUser());
                $participation->setCreatedAt(new \DateTime());
                $em->persist($participation);
            }

            $em->persist($goal);
            $em->persist($chatroom);

            $em->flush();

            $this->addFlash('success', 'Goal créé avec succès!');
            return $this->redirectToRoute('goal_list');
        }

        return $this->render('goal/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    #[Route('/goal/{id}/join', name: 'goal_join')]
    public function join(Goal $goal, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        
        $user = $this->getUser();

        // Check if already has a participation
        $existingParticipation = $em->getRepository(GoalParticipation::class)
            ->findOneBy(['goal' => $goal, 'user' => $user]);
        
        if ($existingParticipation) {
            if ($existingParticipation->isPending()) {
                $this->addFlash('warning', 'Votre demande est déjà en attente d\'approbation.');
            } elseif ($existingParticipation->isApproved()) {
                $this->addFlash('warning', 'Vous participez déjà à ce goal.');
            }
            return $this->redirectToRoute('goal_list');
        }

        // Create new participation with PENDING status
        $participation = new GoalParticipation();
        $participation->setGoal($goal);
        $participation->setUser($user);
        $participation->setCreatedAt(new \DateTime());
        $participation->setRole(GoalParticipation::ROLE_MEMBER);
        $participation->setStatus(GoalParticipation::STATUS_PENDING);

        $em->persist($participation);
        $em->flush();

        $this->addFlash('success', 'Demande d\'accès envoyée! En attente d\'approbation par les administrateurs.');
        return $this->redirectToRoute('goal_list');
    }

    #[Route('/goal/{id}/leave', name: 'goal_leave')]
    public function leave(Goal $goal, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        
        $user = $this->getUser();

        $participation = $em->getRepository(GoalParticipation::class)
            ->findOneBy([
                'goal' => $goal,
                'user' => $user
            ]);

        if ($participation) {
            $em->remove($participation);
            $em->flush();
            $this->addFlash('success', 'Vous avez quitté le goal.');
        }

        return $this->redirectToRoute('goal_list');
    }

    #[Route('/goal/{id}', name: 'goal_show')]
    public function show(Goal $goal): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        
        return $this->render('goal/show.html.twig', [
            'goal' => $goal,
        ]);
    }

    #[Route('/goal/{id}/messages', name: 'goal_messages')]
    public function messages(Goal $goal, Request $request, EntityManagerInterface $em, MessageReadReceiptRepository $readReceiptRepo): Response
    {
        $chatroom = $goal->getChatroom();
        
        if (!$chatroom) {
            if ($request->isXmlHttpRequest() || $request->headers->get('X-Requested-With') === 'XMLHttpRequest') {
                return new JsonResponse(['success' => false, 'error' => 'Ce goal n\'a pas de chatroom.'], 404);
            }
            $this->addFlash('error', 'Ce goal n\'a pas de chatroom.');
            return $this->redirectToRoute('goal_list');
        }

        $user = $this->getUser();
        
        // Check if user is a member of this goal
        $currentUserParticipation = null;
        $isMember = false;
        
        if ($user) {
            $currentUserParticipation = $em->getRepository(GoalParticipation::class)->findOneBy([
                'user' => $user,
                'goal' => $goal
            ]);
            // Only consider as member if participation is APPROVED
            $isMember = $currentUserParticipation !== null && $currentUserParticipation->isApproved();
        }
        
        // If not a member or pending, show read-only view
        if (!$isMember) {
            return $this->render('chatroom/chatroom.html.twig', [
                'chatroom' => $chatroom,
                'goal' => $goal,
                'form' => null,
                'readReceiptRepo' => $readReceiptRepo,
                'currentUserParticipation' => $currentUserParticipation,
                'isMember' => false,
            ]);
        }
        
        // Mark all messages as read when user opens chatroom
        if ($user) {
            foreach ($chatroom->getMessages() as $message) {
                if ($message->getAuthor()->getId() !== $user->getId()) {
                    if (!$readReceiptRepo->hasUserReadMessage($message, $user)) {
                        $receipt = new MessageReadReceipt();
                        $receipt->setMessage($message);
                        $receipt->setUser($user);
                        $receipt->setReadAt(new \DateTime());
                        $em->persist($receipt);
                    }
                }
            }
            $em->flush();
        }
        
        $message = new Message();

        $form = $this->createForm(MessageType::class, $message);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$user) {
                if ($request->isXmlHttpRequest() || $request->headers->get('X-Requested-With') === 'XMLHttpRequest') {
                    return new JsonResponse(['success' => false, 'error' => 'Vous devez être connecté pour envoyer un message.'], 401);
                }
                $this->addFlash('error', 'Vous devez être connecté pour envoyer un message.');
                return $this->redirectToRoute('app_login');
            }
            
            try {
                error_log('=== START FILE UPLOAD DEBUG ===');
                
                // Handle file upload
                $attachmentFile = $form->get('attachment')->getData();
                error_log('Attachment file: ' . ($attachmentFile ? 'YES' : 'NO'));
                
                if ($attachmentFile) {
                    error_log('File original name: ' . $attachmentFile->getClientOriginalName());
                    error_log('File size: ' . $attachmentFile->getSize());
                    error_log('File MIME type: ' . $attachmentFile->getMimeType());
                    
                    $originalFilename = pathinfo($attachmentFile->getClientOriginalName(), PATHINFO_FILENAME);
                    $safeFilename = transliterator_transliterate('Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()', $originalFilename);
                    
                    // Get extension - fallback to original if guessExtension fails
                    $extension = $attachmentFile->guessExtension();
                    if (!$extension) {
                        $extension = pathinfo($attachmentFile->getClientOriginalName(), PATHINFO_EXTENSION);
                    }
                    if (!$extension) {
                        $extension = 'bin'; // Fallback
                    }
                    
                    error_log('Extension: ' . $extension);
                    
                    $newFilename = $safeFilename.'-'.uniqid().'.'.$extension;
                    error_log('New filename: ' . $newFilename);

                    try {
                        // Ensure directory exists
                        $uploadDir = $this->getParameter('kernel.project_dir').'/public/uploads/messages';
                        error_log('Upload dir: ' . $uploadDir);
                        
                        if (!is_dir($uploadDir)) {
                            error_log('Creating directory...');
                            mkdir($uploadDir, 0777, true);
                        }
                        
                        error_log('Moving file...');
                        $attachmentFile->move($uploadDir, $newFilename);
                        error_log('File moved successfully!');

                        $message->setAttachmentPath('/uploads/messages/'.$newFilename);
                        $message->setAttachmentOriginalName($attachmentFile->getClientOriginalName());
                        
                        // Determine file type
                        $mimeType = $attachmentFile->getMimeType();
                        error_log('Determining file type from MIME: ' . $mimeType);
                        
                        if (str_starts_with($mimeType, 'image/')) {
                            $message->setAttachmentType('image');
                        } elseif (str_starts_with($mimeType, 'video/') || $mimeType === 'video/webm') {
                            $message->setAttachmentType('video');
                        } elseif (str_starts_with($mimeType, 'audio/') || $mimeType === 'audio/webm') {
                            $message->setAttachmentType('audio');
                        } elseif ($mimeType === 'application/pdf') {
                            $message->setAttachmentType('pdf');
                        } elseif (str_contains($mimeType, 'word')) {
                            $message->setAttachmentType('document');
                        } elseif (str_contains($mimeType, 'excel') || str_contains($mimeType, 'spreadsheet')) {
                            $message->setAttachmentType('excel');
                        } elseif (str_starts_with($mimeType, 'text/')) {
                            $message->setAttachmentType('text');
                        } else {
                            $message->setAttachmentType('file');
                        }
                        
                        error_log('File type set to: ' . $message->getAttachmentType());
                    } catch (\Exception $e) {
                        error_log('ERROR during file upload: ' . $e->getMessage());
                        error_log('Stack trace: ' . $e->getTraceAsString());
                        
                        if ($request->isXmlHttpRequest() || $request->headers->get('X-Requested-With') === 'XMLHttpRequest') {
                            return new JsonResponse([
                                'success' => false,
                                'error' => 'Erreur lors de l\'upload du fichier: ' . $e->getMessage()
                            ], 500);
                        }
                        $this->addFlash('error', 'Erreur lors de l\'upload du fichier.');
                        return $this->redirectToRoute('goal_messages', ['id' => $goal->getId()]);
                    }
                }

                // Content is optional if there's an attachment
                $contentValue = $message->getContent();
                error_log('Content value: ' . ($contentValue ? $contentValue : 'EMPTY'));
                
                // Check if there's an attachment (either regular attachment or VichUploader image)
                $hasAttachment = $attachmentFile || $message->getImageFile();
                
                if ((empty($contentValue) || trim($contentValue) === '') && !$hasAttachment) {
                    error_log('ERROR: No content and no attachment');
                    if ($request->isXmlHttpRequest() || $request->headers->get('X-Requested-With') === 'XMLHttpRequest') {
                        return new JsonResponse([
                            'success' => false,
                            'error' => 'Le message doit contenir du texte ou un fichier.'
                        ], 400);
                    }
                    $this->addFlash('error', 'Le message doit contenir du texte ou un fichier.');
                    return $this->redirectToRoute('goal_messages', ['id' => $goal->getId()]);
                }
                
                // If only attachment, set content to null
                if ((empty($contentValue) || trim($contentValue) === '') && $hasAttachment) {
                    $message->setContent(null);
                    error_log('Content set to null (attachment only)');
                }

                // Handle reply to another message
                $replyToId = $request->request->get('reply_to');
                if ($replyToId) {
                    $replyToMessage = $em->getRepository(Message::class)->find($replyToId);
                    if ($replyToMessage && $replyToMessage->getChatroom()->getId() === $chatroom->getId()) {
                        $message->setReplyTo($replyToMessage);
                        error_log('Reply to message: ' . $replyToId);
                    }
                }

                $message->setAuthor($user);
                $message->setChatroom($chatroom);
                $message->setCreatedAt(new \DateTime());

                error_log('Persisting message...');
                $em->persist($message);
                $em->flush();
                error_log('Message persisted successfully!');
                error_log('=== END FILE UPLOAD DEBUG ===');
                
                // For AJAX requests, return JSON
                if ($request->isXmlHttpRequest() || $request->headers->get('X-Requested-With') === 'XMLHttpRequest') {
                    return new JsonResponse([
                        'success' => true,
                        'message' => 'Message envoyé!',
                        'messageId' => $message->getId()
                    ]);
                }
                
                // For normal requests, redirect
                $this->addFlash('success', 'Message envoyé!');
                return $this->redirectToRoute('goal_messages', ['id' => $goal->getId()]);
                
            } catch (\Exception $e) {
                error_log('FATAL ERROR: ' . $e->getMessage());
                error_log('Stack trace: ' . $e->getTraceAsString());
                
                if ($request->isXmlHttpRequest() || $request->headers->get('X-Requested-With') === 'XMLHttpRequest') {
                    return new JsonResponse([
                        'success' => false,
                        'error' => 'Erreur: ' . $e->getMessage()
                    ], 500);
                }
                $this->addFlash('error', 'Erreur: ' . $e->getMessage());
                return $this->redirectToRoute('goal_messages', ['id' => $goal->getId()]);
            }
        }

        // For AJAX requests, return only the messages HTML
        if ($request->isXmlHttpRequest()) {
            return $this->render('chatroom/chatroom.html.twig', [
                'chatroom' => $chatroom,
                'goal' => $goal,
                'form' => $form->createView(),
                'readReceiptRepo' => $readReceiptRepo,
            ]);
        }

        return $this->render('chatroom/chatroom.html.twig', [
            'chatroom' => $chatroom,
            'goal' => $goal,
            'form' => $form->createView(),
            'readReceiptRepo' => $readReceiptRepo,
            'currentUserParticipation' => $currentUserParticipation,
            'isMember' => true,
        ]);
    }

    #[Route('/message/{id}/delete', name: 'message_delete', methods: ['POST'])]
    public function deleteMessage(Message $message, EntityManagerInterface $em, Request $request): Response
    {
        $user = $this->getUser();
        
        // Vérifier que l'utilisateur est connecté
        if (!$user) {
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse(['success' => false, 'error' => 'Vous devez être connecté'], 401);
            }
            $this->addFlash('error', 'Vous devez être connecté pour supprimer un message.');
            return $this->redirectToRoute('app_login');
        }

        // Vérifier que l'utilisateur est l'auteur du message
        if ($message->getAuthor()->getId() !== $user->getId()) {
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse(['success' => false, 'error' => 'Vous ne pouvez supprimer que vos propres messages'], 403);
            }
            $this->addFlash('error', 'Vous ne pouvez supprimer que vos propres messages.');
            return $this->redirectToRoute('goal_messages', ['id' => $message->getChatroom()->getGoal()->getId()]);
        }

        $goalId = $message->getChatroom()->getGoal()->getId();
        
        $em->remove($message);
        $em->flush();

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(['success' => true, 'message' => 'Message supprimé pour tout le monde']);
        }

        $this->addFlash('success', 'Message supprimé!');
        return $this->redirectToRoute('goal_messages', ['id' => $goalId]);
    }

    #[Route('/message/{id}/delete-for-me', name: 'message_delete_for_me', methods: ['POST'])]
    public function deleteMessageForMe(Message $message, EntityManagerInterface $em, Request $request): Response
    {
        $user = $this->getUser();
        
        // Vérifier que l'utilisateur est connecté
        if (!$user) {
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse(['success' => false, 'error' => 'Vous devez être connecté'], 401);
            }
            $this->addFlash('error', 'Vous devez être connecté.');
            return $this->redirectToRoute('app_login');
        }

        // Pour "Retirer pour vous", on pourrait:
        // 1. Créer une table "deleted_messages" pour tracker qui a supprimé quoi
        // 2. Ou simplement cacher le message côté client
        // Pour l'instant, on va créer une entité MessageDeletion
        
        // TODO: Implémenter la logique de suppression pour l'utilisateur uniquement
        // Pour l'instant, on retourne un succès et on cache le message côté client
        
        if ($request->isXmlHttpRequest()) {
            return new JsonResponse([
                'success' => true, 
                'message' => 'Message supprimé pour vous uniquement',
                'type' => 'for_me'
            ]);
        }

        $this->addFlash('success', 'Message supprimé pour vous!');
        return $this->redirectToRoute('goal_messages', ['id' => $message->getChatroom()->getGoal()->getId()]);
    }

    #[Route('/message/{id}/edit', name: 'message_edit', methods: ['POST'])]
    public function editMessage(Message $message, Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        
        // Vérifier que l'utilisateur est connecté
        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour modifier un message.');
            return $this->redirectToRoute('app_login');
        }

        // Vérifier que l'utilisateur est l'auteur du message
        if ($message->getAuthor()->getId() !== $user->getId()) {
            $this->addFlash('error', 'Vous ne pouvez modifier que vos propres messages.');
            return $this->redirectToRoute('goal_messages', ['id' => $message->getChatroom()->getGoal()->getId()]);
        }

        $newContent = $request->request->get('content');
        
        if (empty($newContent)) {
            $this->addFlash('error', 'Le message ne peut pas être vide.');
            return $this->redirectToRoute('goal_messages', ['id' => $message->getChatroom()->getGoal()->getId()]);
        }

        $message->setContent($newContent);
        $message->setIsEdited(true);
        $message->setEditedAt(new \DateTime());
        
        $em->flush();

        $this->addFlash('success', 'Message modifié!');
        return $this->redirectToRoute('goal_messages', ['id' => $message->getChatroom()->getGoal()->getId()]);
    }

    #[Route('/demo/setup', name: 'demo_setup')]
    public function setupDemo(EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher): Response
    {
        // Créer l'utilisateur Mariem
        $userRepo = $em->getRepository(User::class);
        
        // Créer le compte de Mariem
        $mariem = $userRepo->findOneBy(['email' => 'mariemayari@gmail.com']);
        if (!$mariem) {
            $mariem = new User();
            $mariem->setFirstName('Mariem');
            $mariem->setLastName('Ayari');
            $mariem->setEmail('mariemayari@gmail.com');
            $mariem->setPassword($passwordHasher->hashPassword($mariem, 'mariem'));
            $mariem->setRoles(['ROLE_USER']);
            $mariem->setStatus('active');
            $mariem->setCreatedAt(new \DateTimeImmutable());
            $em->persist($mariem);
            $em->flush();
        }

        $this->addFlash('success', 'Compte créé! Email: mariemayari@gmail.com / Password: mariem');
        return $this->redirectToRoute('goal_list');
    }

    #[Route('/message/{id}/react/{type}', name: 'message_react', methods: ['POST'])]
    public function reactToMessage(Message $message, string $type, EntityManagerInterface $em, MessageReactionRepository $reactionRepo): JsonResponse
    {
        $user = $this->getUser();
        
        if (!$user) {
            return new JsonResponse(['error' => 'Vous devez être connecté'], 401);
        }

        // Validate reaction type
        $validTypes = ['like', 'clap', 'fire', 'heart'];
        if (!in_array($type, $validTypes)) {
            return new JsonResponse(['error' => 'Type de réaction invalide'], 400);
        }

        // Check if reaction already exists
        $existingReaction = $reactionRepo->findOneBy([
            'message' => $message,
            'user' => $user,
            'reactionType' => $type
        ]);

        if ($existingReaction) {
            // Remove reaction (toggle off)
            $em->remove($existingReaction);
            $action = 'removed';
        } else {
            // Add reaction
            $reaction = new MessageReaction();
            $reaction->setMessage($message);
            $reaction->setUser($user);
            $reaction->setReactionType($type);
            $reaction->setCreatedAt(new \DateTime());
            $em->persist($reaction);
            $action = 'added';
        }

        $em->flush();

        // Return updated counts
        return new JsonResponse([
            'success' => true,
            'action' => $action,
            'counts' => [
                'like' => $message->getReactionCount('like'),
                'clap' => $message->getReactionCount('clap'),
                'fire' => $message->getReactionCount('fire'),
                'heart' => $message->getReactionCount('heart'),
            ]
        ]);
    }

    #[Route('/message/{id}/pin', name: 'message_pin', methods: ['POST'])]
    public function pinMessage(Message $message, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        
        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté');
            return $this->redirectToRoute('app_login');
        }

        $chatroom = $message->getChatroom();
        $goal = $chatroom->getGoal();

        // Check if user has permission (ADMIN or OWNER only)
        $participation = $em->getRepository(GoalParticipation::class)->findOneBy([
            'user' => $user,
            'goal' => $goal
        ]);

        if (!$participation || !$participation->canModerate()) {
            $this->addFlash('error', 'Vous n\'avez pas la permission d\'épingler des messages');
            return $this->redirectToRoute('goal_messages', ['id' => $goal->getId()]);
        }
        
        // Unpin any existing pinned message in this chatroom
        $existingPinned = $em->getRepository(Message::class)->findOneBy([
            'chatroom' => $chatroom,
            'isPinned' => true
        ]);

        if ($existingPinned) {
            $existingPinned->setIsPinned(false);
        }

        // Pin this message
        $message->setIsPinned(true);
        $em->flush();

        $this->addFlash('success', 'Message épinglé!');
        return $this->redirectToRoute('goal_messages', ['id' => $goal->getId()]);
    }

    #[Route('/message/{id}/unpin', name: 'message_unpin', methods: ['POST'])]
    public function unpinMessage(Message $message, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        
        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté');
            return $this->redirectToRoute('app_login');
        }

        $goal = $message->getChatroom()->getGoal();

        // Check if user has permission (ADMIN or OWNER only)
        $participation = $em->getRepository(GoalParticipation::class)->findOneBy([
            'user' => $user,
            'goal' => $goal
        ]);

        if (!$participation || !$participation->canModerate()) {
            $this->addFlash('error', 'Vous n\'avez pas la permission de désépingler des messages');
            return $this->redirectToRoute('goal_messages', ['id' => $goal->getId()]);
        }

        // Unpin message
        $message->setIsPinned(false);
        $em->flush();

        $this->addFlash('success', 'Message désépinglé!');
        return $this->redirectToRoute('goal_messages', ['id' => $goal->getId()]);
    }

    #[Route('/goal/{id}/messages/fetch', name: 'goal_messages_fetch', methods: ['GET'])]
    public function fetchMessages(Goal $goal, Request $request, MessageReadReceiptRepository $readReceiptRepo): JsonResponse
    {
        $chatroom = $goal->getChatroom();
        
        if (!$chatroom) {
            return new JsonResponse(['error' => 'Chatroom introuvable'], 404);
        }

        $lastMessageId = $request->query->get('lastMessageId', 0);
        $user = $this->getUser();

        // Get messages after lastMessageId
        $messages = $chatroom->getMessages()->filter(function($message) use ($lastMessageId) {
            return $message->getId() > $lastMessageId;
        });

        $messagesData = [];
        foreach ($messages as $message) {
            $messagesData[] = [
                'id' => $message->getId(),
                'content' => $message->getContent(),
                'authorFirstName' => $message->getAuthor()->getFirstName(),
                'authorLastName' => $message->getAuthor()->getLastName(),
                'authorInitials' => substr($message->getAuthor()->getFirstName(), 0, 1) . substr($message->getAuthor()->getLastName(), 0, 1),
                'createdAt' => $message->getCreatedAt()->format('g:i A'),
                'createdAtDate' => $message->getCreatedAt()->format('M d'),
                'isOwn' => $user && $message->getAuthor()->getId() === $user->getId(),
                'isEdited' => $message->getIsEdited(),
                'isPinned' => $message->getIsPinned(),
                'hasAttachment' => $message->hasAttachment(),
                'attachmentPath' => $message->getAttachmentPath(),
                'attachmentType' => $message->getAttachmentType(),
                'attachmentOriginalName' => $message->getAttachmentOriginalName(),
                'attachmentIcon' => $message->getAttachmentIcon(),
                'audioDuration' => $message->getFormattedDuration(),
                'isReply' => $message->isReply(),
                'replyTo' => $message->isReply() ? [
                    'authorFirstName' => $message->getReplyTo()->getAuthor()->getFirstName(),
                    'authorLastName' => $message->getReplyTo()->getAuthor()->getLastName(),
                    'content' => strlen($message->getReplyTo()->getContent()) > 50 
                        ? substr($message->getReplyTo()->getContent(), 0, 50) . '...' 
                        : $message->getReplyTo()->getContent()
                ] : null,
                'reactions' => [
                    'like' => $message->getReactionCount('like'),
                    'clap' => $message->getReactionCount('clap'),
                    'fire' => $message->getReactionCount('fire'),
                    'heart' => $message->getReactionCount('heart'),
                ],
                'userReactions' => $user ? [
                    'like' => $message->hasUserReacted($user, 'like'),
                    'clap' => $message->hasUserReacted($user, 'clap'),
                    'fire' => $message->hasUserReacted($user, 'fire'),
                    'heart' => $message->hasUserReacted($user, 'heart'),
                ] : null,
                'readCount' => $readReceiptRepo->getReadCount($message),
            ];
        }

        return new JsonResponse([
            'messages' => $messagesData,
            'count' => count($messagesData)
        ]);
    }

    #[Route('/goal/{id}/send-voice', name: 'goal_send_voice', methods: ['POST'])]
    public function sendVoiceMessage(Goal $goal, Request $request, EntityManagerInterface $em): JsonResponse
    {
        try {
            $user = $this->getUser();
            
            if (!$user) {
                return new JsonResponse(['error' => 'Vous devez être connecté'], 401);
            }

            $chatroom = $goal->getChatroom();
            
            if (!$chatroom) {
                return new JsonResponse(['error' => 'Chatroom introuvable'], 404);
            }

            // Get the voice file from request
            $voiceFile = $request->files->get('voice');
            $duration = $request->request->get('duration', 0);
            
            if (!$voiceFile) {
                return new JsonResponse(['error' => 'Fichier audio manquant'], 400);
            }

            // Use .webm extension for all voice messages
            $newFilename = 'voice-'.uniqid().'.webm';

            // Ensure directory exists
            $uploadDir = $this->getParameter('kernel.project_dir').'/public/uploads/voice';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            // Move file to voice uploads directory
            $voiceFile->move($uploadDir, $newFilename);

            // Create message
            $message = new Message();
            $message->setAuthor($user);
            $message->setChatroom($chatroom);
            $message->setCreatedAt(new \DateTime());
            $message->setContent(null);
            $message->setAttachmentPath('/uploads/voice/'.$newFilename);
            $message->setAttachmentType('audio');
            $message->setAttachmentOriginalName($newFilename);
            $message->setAudioDuration((int)$duration);

            $em->persist($message);
            $em->flush();

            return new JsonResponse([
                'success' => true,
                'message' => 'Message vocal envoyé!',
                'messageId' => $message->getId()
            ]);

        } catch (\Exception $e) {
            // Return only the error message, not the trace
            return new JsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    #[Route('/goal/{id}/delete', name: 'goal_delete', methods: ['POST'])]
    public function deleteGoal(Goal $goal, EntityManagerInterface $em, Request $request): Response
    {
        $user = $this->getUser();
        
        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté');
            return $this->redirectToRoute('app_login');
        }

        // Check if user has permission to delete (OWNER only)
        if (!$goal->canUserDeleteGoal($user)) {
            $this->addFlash('error', 'Seul le propriétaire peut supprimer ce goal');
            return $this->redirectToRoute('goal_list');
        }

        $goalTitle = $goal->getTitle();
        $em->remove($goal);
        $em->flush();

        $this->addFlash('success', "Le goal \"$goalTitle\" a été supprimé avec succès");
        return $this->redirectToRoute('goal_list');
    }

    #[Route('/goal/{id}/edit', name: 'goal_edit')]
    public function editGoal(Goal $goal, Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        
        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté');
            return $this->redirectToRoute('app_login');
        }

        // Check if user has permission to modify (ADMIN or OWNER)
        if (!$goal->canUserModifyGoal($user)) {
            $this->addFlash('error', 'Vous n\'avez pas la permission de modifier ce goal');
            return $this->redirectToRoute('goal_list');
        }

        $form = $this->createForm(GoalType::class, $goal);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Goal modifié avec succès!');
            return $this->redirectToRoute('goal_list');
        }

        return $this->render('goal/edit.html.twig', [
            'form' => $form->createView(),
            'goal' => $goal,
        ]);
    }

    #[Route('/goal/{goalId}/remove-member/{userId}', name: 'goal_remove_member', methods: ['POST'])]
    public function removeMember(int $goalId, int $userId, EntityManagerInterface $em, Request $request): Response
    {
        $user = $this->getUser();
        
        if (!$user) {
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse(['success' => false, 'error' => 'Vous devez être connecté'], 401);
            }
            $this->addFlash('error', 'Vous devez être connecté');
            return $this->redirectToRoute('app_login');
        }

        $goal = $em->getRepository(Goal::class)->find($goalId);
        if (!$goal) {
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse(['success' => false, 'error' => 'Goal introuvable'], 404);
            }
            $this->addFlash('error', 'Goal introuvable');
            return $this->redirectToRoute('goal_list');
        }

        // Check if user has permission to remove members (ADMIN or OWNER)
        if (!$goal->canUserRemoveMembers($user)) {
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse(['success' => false, 'error' => 'Vous n\'avez pas la permission d\'exclure des membres'], 403);
            }
            $this->addFlash('error', 'Vous n\'avez pas la permission d\'exclure des membres');
            return $this->redirectToRoute('goal_messages', ['id' => $goalId]);
        }

        $memberToRemove = $em->getRepository(User::class)->find($userId);
        if (!$memberToRemove) {
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse(['success' => false, 'error' => 'Utilisateur introuvable'], 404);
            }
            $this->addFlash('error', 'Utilisateur introuvable');
            return $this->redirectToRoute('goal_messages', ['id' => $goalId]);
        }

        // Cannot remove yourself
        if ($memberToRemove->getId() === $user->getId()) {
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse(['success' => false, 'error' => 'Vous ne pouvez pas vous exclure vous-même'], 400);
            }
            $this->addFlash('error', 'Vous ne pouvez pas vous exclure vous-même. Utilisez "Quitter le goal".');
            return $this->redirectToRoute('goal_messages', ['id' => $goalId]);
        }

        $participation = $em->getRepository(GoalParticipation::class)->findOneBy([
            'goal' => $goal,
            'user' => $memberToRemove
        ]);

        if (!$participation) {
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse(['success' => false, 'error' => 'Cet utilisateur n\'est pas membre de ce goal'], 404);
            }
            $this->addFlash('error', 'Cet utilisateur n\'est pas membre de ce goal');
            return $this->redirectToRoute('goal_messages', ['id' => $goalId]);
        }

        // ADMIN cannot remove OWNER
        $currentUserParticipation = $goal->getUserParticipation($user);
        if ($currentUserParticipation->isAdmin() && $participation->isOwner()) {
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse(['success' => false, 'error' => 'Un administrateur ne peut pas exclure le propriétaire'], 403);
            }
            $this->addFlash('error', 'Un administrateur ne peut pas exclure le propriétaire');
            return $this->redirectToRoute('goal_messages', ['id' => $goalId]);
        }

        $memberName = $memberToRemove->getFirstName() . ' ' . $memberToRemove->getLastName();
        $em->remove($participation);
        $em->flush();

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse([
                'success' => true,
                'message' => "$memberName a été exclu du goal"
            ]);
        }

        $this->addFlash('success', "$memberName a été exclu du goal");
        return $this->redirectToRoute('goal_messages', ['id' => $goalId]);
    }

    #[Route('/goal/{goalId}/promote-member/{userId}', name: 'goal_promote_member', methods: ['POST'])]
    public function promoteMember(int $goalId, int $userId, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $user = $this->getUser();
        
        if (!$user) {
            return new JsonResponse(['success' => false, 'error' => 'Vous devez être connecté'], 401);
        }

        $goal = $em->getRepository(Goal::class)->find($goalId);
        if (!$goal) {
            return new JsonResponse(['success' => false, 'error' => 'Goal introuvable'], 404);
        }

        // Only OWNER can promote members
        $currentUserParticipation = $goal->getUserParticipation($user);
        if (!$currentUserParticipation || !$currentUserParticipation->isOwner()) {
            return new JsonResponse(['success' => false, 'error' => 'Seul le propriétaire peut promouvoir des membres'], 403);
        }

        $memberToPromote = $em->getRepository(User::class)->find($userId);
        if (!$memberToPromote) {
            return new JsonResponse(['success' => false, 'error' => 'Utilisateur introuvable'], 404);
        }

        $participation = $em->getRepository(GoalParticipation::class)->findOneBy([
            'goal' => $goal,
            'user' => $memberToPromote
        ]);

        if (!$participation) {
            return new JsonResponse(['success' => false, 'error' => 'Cet utilisateur n\'est pas membre de ce goal'], 404);
        }

        $newRole = $request->request->get('role');
        if (!in_array($newRole, ['MEMBER', 'ADMIN', 'OWNER'])) {
            return new JsonResponse(['success' => false, 'error' => 'Rôle invalide'], 400);
        }

        $participation->setRole($newRole);
        $em->flush();

        return new JsonResponse([
            'success' => true,
            'message' => $memberToPromote->getFirstName() . ' est maintenant ' . $newRole,
            'newRole' => $newRole
        ]);
    }
    #[Route('/goal/{goalId}/approve-request/{userId}', name: 'goal_approve_request', methods: ['POST'])]
    public function approveRequest(
        int $goalId,
        int $userId,
        EntityManagerInterface $em,
        Request $request
    ): Response {
        $user = $this->getUser();

        if (!$user) {
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse(['success' => false, 'error' => 'Vous devez être connecté'], 401);
            }
            $this->addFlash('error', 'Vous devez être connecté');
            return $this->redirectToRoute('app_login');
        }

        $goal = $em->getRepository(Goal::class)->find($goalId);
        if (!$goal) {
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse(['success' => false, 'error' => 'Goal introuvable'], 404);
            }
            $this->addFlash('error', 'Goal introuvable');
            return $this->redirectToRoute('goal_list');
        }

        // Check permission (ADMIN or OWNER)
        if (!$goal->canUserRemoveMembers($user)) {
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse(['success' => false, 'error' => 'Permission refusée'], 403);
            }
            $this->addFlash('error', 'Vous n\'avez pas la permission d\'approuver des demandes');
            return $this->redirectToRoute('goal_messages', ['id' => $goalId]);
        }

        $requestUser = $em->getRepository(User::class)->find($userId);
        if (!$requestUser) {
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse(['success' => false, 'error' => 'Utilisateur introuvable'], 404);
            }
            $this->addFlash('error', 'Utilisateur introuvable');
            return $this->redirectToRoute('goal_messages', ['id' => $goalId]);
        }

        $participation = $em->getRepository(GoalParticipation::class)->findOneBy([
            'goal' => $goal,
            'user' => $requestUser
        ]);

        if (!$participation || !$participation->isPending()) {
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse(['success' => false, 'error' => 'Aucune demande en attente'], 404);
            }
            $this->addFlash('error', 'Aucune demande en attente pour cet utilisateur');
            return $this->redirectToRoute('goal_messages', ['id' => $goalId]);
        }

        // Approve the request
        $participation->setStatus(GoalParticipation::STATUS_APPROVED);
        $em->flush();

        $userName = $requestUser->getFirstName() . ' ' . $requestUser->getLastName();

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse([
                'success' => true,
                'message' => "$userName a été accepté dans le goal"
            ]);
        }

        $this->addFlash('success', "$userName a été accepté dans le goal");
        return $this->redirectToRoute('goal_messages', ['id' => $goalId]);
    }

    #[Route('/goal/{goalId}/reject-request/{userId}', name: 'goal_reject_request', methods: ['POST'])]
    public function rejectRequest(
        int $goalId,
        int $userId,
        EntityManagerInterface $em,
        Request $request
    ): Response {
        $user = $this->getUser();

        if (!$user) {
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse(['success' => false, 'error' => 'Vous devez être connecté'], 401);
            }
            $this->addFlash('error', 'Vous devez être connecté');
            return $this->redirectToRoute('app_login');
        }

        $goal = $em->getRepository(Goal::class)->find($goalId);
        if (!$goal) {
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse(['success' => false, 'error' => 'Goal introuvable'], 404);
            }
            $this->addFlash('error', 'Goal introuvable');
            return $this->redirectToRoute('goal_list');
        }

        // Check permission (ADMIN or OWNER)
        if (!$goal->canUserRemoveMembers($user)) {
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse(['success' => false, 'error' => 'Permission refusée'], 403);
            }
            $this->addFlash('error', 'Vous n\'avez pas la permission de refuser des demandes');
            return $this->redirectToRoute('goal_messages', ['id' => $goalId]);
        }

        $requestUser = $em->getRepository(User::class)->find($userId);
        if (!$requestUser) {
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse(['success' => false, 'error' => 'Utilisateur introuvable'], 404);
            }
            $this->addFlash('error', 'Utilisateur introuvable');
            return $this->redirectToRoute('goal_messages', ['id' => $goalId]);
        }

        $participation = $em->getRepository(GoalParticipation::class)->findOneBy([
            'goal' => $goal,
            'user' => $requestUser
        ]);

        if (!$participation || !$participation->isPending()) {
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse(['success' => false, 'error' => 'Aucune demande en attente'], 404);
            }
            $this->addFlash('error', 'Aucune demande en attente pour cet utilisateur');
            return $this->redirectToRoute('goal_messages', ['id' => $goalId]);
        }

        // Reject the request (delete participation)
        $userName = $requestUser->getFirstName() . ' ' . $requestUser->getLastName();
        $em->remove($participation);
        $em->flush();

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse([
                'success' => true,
                'message' => "Demande de $userName refusée"
            ]);
        }

        $this->addFlash('success', "Demande de $userName refusée");
        return $this->redirectToRoute('goal_messages', ['id' => $goalId]);
    }
}
