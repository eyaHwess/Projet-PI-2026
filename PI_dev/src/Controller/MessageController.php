<?php

namespace App\Controller;

use App\Entity\Message;
use App\Entity\MessageReaction;
use App\Repository\MessageReactionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/message')]
final class MessageController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private \App\Service\ModerationService $moderationService
    ) {}

    /**
     * Delete a message
     */
    #[Route('/{id}/delete', name: 'message_delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function delete(Message $message, Request $request): Response
    {
        $user = $this->getUser();
        
        if (!$user) {
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse(['success' => false, 'error' => 'Vous devez être connecté'], 401);
            }
            $this->addFlash('error', 'Vous devez être connecté pour supprimer un message.');
            return $this->redirectToRoute('app_login');
        }

        // Check if user is the author OR has moderation rights
        $goal = $message->getChatroom()->getGoal();
        $userParticipation = $goal->getUserParticipation($user);
        
        $canDelete = ($message->getAuthor()->getId() === $user->getId()) || 
                     ($userParticipation && $userParticipation->canModerate());

        if (!$canDelete) {
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse(['success' => false, 'error' => 'Permission refusée'], 403);
            }
            $this->addFlash('error', 'Vous ne pouvez pas supprimer ce message.');
            return $this->redirectToRoute('message_chatroom', ['goalId' => $goal->getId()]);
        }

        $goalId = $goal->getId();
        
        $this->entityManager->remove($message);
        $this->entityManager->flush();

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse(['success' => true, 'message' => 'Message supprimé pour tout le monde']);
        }

        $this->addFlash('success', 'Message supprimé!');
        return $this->redirectToRoute('message_chatroom', ['goalId' => $goalId]);
    }
    /**
     * Translate a message (API traduction automatique)
     */
    #[Route('/{id}/translate', name: 'message_translate', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function translate(
        Message $message,
        Request $request,
        \App\Service\TranslationService $translator,
        \App\Repository\MessageTranslationRepository $translationRepo
    ): JsonResponse {
        $content = $message->getContent();
        if ($content === null || trim($content) === '') {
            return new JsonResponse([
                'error' => 'Ce message n\'a pas de texte à traduire.'
            ], 400);
        }

        $target = (string) $request->request->get('lang', 'en');
        $target = strtolower(strlen($target) >= 2 ? substr($target, 0, 2) : 'en');

        // Accepter toutes les langues, pas seulement celles dans la liste
        $supportedLanguages = $translator->getSupportedLanguages();
        $targetLanguageName = $supportedLanguages[$target] ?? strtoupper($target);

        // Vérifier si une traduction existe déjà en base de données
        $existingTranslation = $translationRepo->findExistingTranslation($message, $target);
        
        if ($existingTranslation) {
            // Incrémenter le compteur d'utilisation
            $existingTranslation->incrementUsageCount();
            $this->entityManager->flush();
            
            return new JsonResponse([
                'translation' => $existingTranslation->getTranslatedText(),
                'targetLanguage' => $targetLanguageName,
                'originalText' => $content,
                'cached' => true,
                'provider' => $existingTranslation->getProvider()
            ]);
        }

        // Sinon, appeler l'API de traduction
        try {
            $translated = $translator->translate($content, $target);
        } catch (\Throwable $e) {
            return new JsonResponse([
                'error' => 'Impossible de traduire pour le moment. Réessayez plus tard.'
            ], 200);
        }

        if (str_starts_with($translated, 'Erreur:')) {
            return new JsonResponse([
                'error' => 'Service de traduction indisponible. Réessayez plus tard.'
            ], 200);
        }

        // Enregistrer la traduction en base de données
        $messageTranslation = new \App\Entity\MessageTranslation();
        $messageTranslation->setMessage($message);
        $messageTranslation->setSourceLanguage('auto'); // Détection automatique
        $messageTranslation->setTargetLanguage($target);
        $messageTranslation->setTranslatedText($translated);
        $messageTranslation->setProvider($translator->getProvider());
        
        $this->entityManager->persist($messageTranslation);
        $this->entityManager->flush();

        return new JsonResponse([
            'translation' => $translated,
            'targetLanguage' => $targetLanguageName,
            'originalText' => $content,
            'cached' => false,
            'provider' => $translator->getProvider()
        ]);
    }



    /**
     * Delete message for current user only
     */
    #[Route('/{id}/delete-for-me', name: 'message_delete_for_me', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function deleteForMe(Message $message, Request $request): Response
    {
        $user = $this->getUser();
        
        if (!$user) {
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse(['success' => false, 'error' => 'Vous devez être connecté'], 401);
            }
            $this->addFlash('error', 'Vous devez être connecté.');
            return $this->redirectToRoute('app_login');
        }

        // TODO: Implement soft delete for user
        // For now, just return success and hide on client side
        
        if ($request->isXmlHttpRequest()) {
            return new JsonResponse([
                'success' => true, 
                'message' => 'Message supprimé pour vous uniquement',
                'type' => 'for_me'
            ]);
        }

        $this->addFlash('success', 'Message supprimé pour vous!');
        return $this->redirectToRoute('message_chatroom', ['goalId' => $message->getChatroom()->getGoal()->getId()]);
    }

    /**
     * Edit a message
     */
    #[Route('/{id}/edit', name: 'message_edit', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function edit(Message $message, Request $request): Response
    {
        $user = $this->getUser();
        
        if (!$user) {
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse(['success' => false, 'error' => 'Vous devez être connecté'], 401);
            }
            $this->addFlash('error', 'Vous devez être connecté pour modifier un message.');
            return $this->redirectToRoute('app_login');
        }

        // Only author can edit
        if ($message->getAuthor()->getId() !== $user->getId()) {
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse(['success' => false, 'error' => 'Vous ne pouvez modifier que vos propres messages'], 403);
            }
            $this->addFlash('error', 'Vous ne pouvez modifier que vos propres messages.');
            return $this->redirectToRoute('message_chatroom', ['goalId' => $message->getChatroom()->getGoal()->getId()]);
        }

        $newContent = $request->request->get('content');
        
        if (empty($newContent) || trim($newContent) === '') {
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse(['success' => false, 'error' => 'Le message ne peut pas être vide'], 400);
            }
            $this->addFlash('error', 'Le message ne peut pas être vide.');
            return $this->redirectToRoute('message_chatroom', ['goalId' => $message->getChatroom()->getGoal()->getId()]);
        }

        $message->setContent($newContent);
        $message->setIsEdited(true);
        $message->setEditedAt(new \DateTime());
        
        $this->entityManager->flush();

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse([
                'success' => true,
                'message' => 'Message modifié avec succès'
            ]);
        }

        $this->addFlash('success', 'Message modifié!');
        return $this->redirectToRoute('message_chatroom', ['goalId' => $message->getChatroom()->getGoal()->getId()]);
    }

    /**
     * React to a message
     */
    #[Route('/{id}/react/{type}', name: 'message_react', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function react(Message $message, string $type, MessageReactionRepository $reactionRepo): JsonResponse
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
            $this->entityManager->remove($existingReaction);
            $action = 'removed';
        } else {
            // Add reaction
            $reaction = new MessageReaction();
            $reaction->setMessage($message);
            $reaction->setUser($user);
            $reaction->setReactionType($type);
            $reaction->setCreatedAt(new \DateTime());
            $this->entityManager->persist($reaction);
            $action = 'added';
        }

        $this->entityManager->flush();

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

    /**
     * Pin a message
     */
    #[Route('/{id}/pin', name: 'message_pin', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function pin(Message $message): Response
    {
        $user = $this->getUser();
        
        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté');
            return $this->redirectToRoute('app_login');
        }

        $chatroom = $message->getChatroom();
        $goal = $chatroom->getGoal();

        // Check if user has permission (ADMIN or OWNER only)
        $participation = $goal->getUserParticipation($user);

        if (!$participation || !$participation->canModerate()) {
            $this->addFlash('error', 'Vous n\'avez pas la permission d\'épingler des messages');
            return $this->redirectToRoute('message_chatroom', ['goalId' => $goal->getId()]);
        }
        
        // Unpin any existing pinned message in this chatroom
        $existingPinned = $this->entityManager->getRepository(Message::class)->findOneBy([
            'chatroom' => $chatroom,
            'isPinned' => true
        ]);

        if ($existingPinned) {
            $existingPinned->setIsPinned(false);
        }

        // Pin this message
        $message->setIsPinned(true);
        $this->entityManager->flush();

        $this->addFlash('success', 'Message épinglé!');
        return $this->redirectToRoute('message_chatroom', ['goalId' => $goal->getId()]);
    }

    /**
     * Unpin a message
     */
    #[Route('/{id}/unpin', name: 'message_unpin', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function unpin(Message $message): Response
    {
        $user = $this->getUser();
        
        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté');
            return $this->redirectToRoute('app_login');
        }

        $goal = $message->getChatroom()->getGoal();

        // Check if user has permission (ADMIN or OWNER only)
        $participation = $goal->getUserParticipation($user);

        if (!$participation || !$participation->canModerate()) {
            $this->addFlash('error', 'Vous n\'avez pas la permission de désépingler des messages');
            return $this->redirectToRoute('message_chatroom', ['goalId' => $goal->getId()]);
        }

        // Unpin message
        $message->setIsPinned(false);
        $this->entityManager->flush();

        $this->addFlash('success', 'Message désépinglé!');
        return $this->redirectToRoute('message_chatroom', ['goalId' => $goal->getId()]);
    }

    /**
     * Report a message
     */
    #[Route('/{id}/report', name: 'message_report', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function report(
        Message $message,
        Request $request,
        \App\Repository\MessageReportRepository $reportRepo
    ): Response {
        $user = $this->getUser();

        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté');
            return $this->redirectToRoute('app_login');
        }

        $goal = $message->getChatroom()->getGoal();

        // Check if user has already reported this message
        if ($reportRepo->hasUserReported($message, $user)) {
            $this->addFlash('warning', 'Vous avez déjà signalé ce message');
            return $this->redirectToRoute('message_chatroom', ['goalId' => $goal->getId()]);
        }

        $report = new \App\Entity\MessageReport();
        $report->setMessage($message);
        $report->setReporter($user);

        $form = $this->createForm(\App\Form\MessageReportType::class, $report);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($report);
            $this->entityManager->flush();

            $this->addFlash('success', 'Message signalé. Notre équipe va examiner votre signalement.');
            return $this->redirectToRoute('message_chatroom', ['goalId' => $goal->getId()]);
        }

        return $this->render('message/report.html.twig', [
            'message' => $message,
            'goal' => $goal,
            'form' => $form->createView(),
        ]);
    }


    /**
     * Display chatroom and handle message sending
     */
    #[Route('/chatroom/{goalId}', name: 'message_chatroom', requirements: ['goalId' => '\d+'])]
    public function chatroom(
        int $goalId,
        Request $request,
        EntityManagerInterface $em,
        \App\Repository\MessageReadReceiptRepository $readReceiptRepo,
        \App\Repository\GoalRepository $goalRepository,
        \App\Service\TranslationService $translator
    ): Response {
        // Vérifier que l'utilisateur est connecté
        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour accéder au chatroom.');
            return $this->redirectToRoute('app_login');
        }

        $goal = $goalRepository->find($goalId);
        
        if (!$goal) {
            throw $this->createNotFoundException('Goal not found');
        }
        
        $chatroom = $goal->getChatroom();
        
        if (!$chatroom) {
            if ($request->isXmlHttpRequest() || $request->headers->get('X-Requested-With') === 'XMLHttpRequest') {
                return new JsonResponse(['success' => false, 'error' => 'Ce goal n\'a pas de chatroom.'], 404);
            }
            $this->addFlash('error', 'Ce goal n\'a pas de chatroom.');
            return $this->redirectToRoute('goal_list');
        }
        
        // Check if user is a member of this goal
        $currentUserParticipation = $em->getRepository(\App\Entity\GoalParticipation::class)->findOneBy([
            'user' => $user,
            'goal' => $goal
        ]);
        
        // Vérifier que l'utilisateur est membre
        if (!$currentUserParticipation) {
            $this->addFlash('error', 'Vous devez rejoindre ce goal pour accéder au chatroom.');
            return $this->redirectToRoute('goal_list');
        }
        
        // Vérifier que la participation est approuvée
        if (!$currentUserParticipation->isApproved()) {
            $this->addFlash('warning', 'Votre demande d\'accès est en attente d\'approbation.');
            return $this->redirectToRoute('goal_list');
        }

        // Vérifier l'état du chatroom
        if ($chatroom->getState() === 'deleted') {
            $this->addFlash('error', 'Ce chatroom a été supprimé.');
            return $this->redirectToRoute('goal_list');
        }
        
        // Use modern template
        $template = 'chatroom/chatroom_modern.html.twig';
        
        // Mark all messages as read when user opens chatroom
        foreach ($chatroom->getMessages() as $message) {
            if ($message->getAuthor()->getId() !== $user->getId()) {
                if (!$readReceiptRepo->hasUserReadMessage($message, $user)) {
                    $receipt = new \App\Entity\MessageReadReceipt();
                    $receipt->setMessage($message);
                    $receipt->setUser($user);
                    $receipt->setReadAt(new \DateTime());
                    $em->persist($receipt);
                }
            }
        }
        $em->flush();
        
        $message = new Message();

        $form = $this->createForm(\App\Form\MessageType::class, $message);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Vérifier que le chatroom n'est pas verrouillé ou archivé
            if ($chatroom->getState() === 'locked') {
                if ($request->isXmlHttpRequest() || $request->headers->get('X-Requested-With') === 'XMLHttpRequest') {
                    return new JsonResponse([
                        'success' => false,
                        'error' => 'Ce chatroom est verrouillé. Vous ne pouvez pas envoyer de messages.'
                    ], 403);
                }
                $this->addFlash('error', 'Ce chatroom est verrouillé. Vous ne pouvez pas envoyer de messages.');
                return $this->redirectToRoute('message_chatroom', ['goalId' => $goalId]);
            }

            if ($chatroom->getState() === 'archived') {
                if ($request->isXmlHttpRequest() || $request->headers->get('X-Requested-With') === 'XMLHttpRequest') {
                    return new JsonResponse([
                        'success' => false,
                        'error' => 'Ce chatroom est archivé (lecture seule).'
                    ], 403);
                }
                $this->addFlash('error', 'Ce chatroom est archivé (lecture seule).');
                return $this->redirectToRoute('message_chatroom', ['goalId' => $goalId]);
            }

            try {
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

                // Content is optional if there's an attachment (Vich or legacy)
                $contentValue = $message->getContent();
                $hasAttachment = $attachmentFile || $message->getImageFile() || $message->getFile()
                    || $message->getImageName() || $message->getFileName();
                
                if ((empty($contentValue) || trim($contentValue) === '') && !$hasAttachment) {
                    if ($request->isXmlHttpRequest() || $request->headers->get('X-Requested-With') === 'XMLHttpRequest') {
                        return new JsonResponse([
                            'success' => false,
                            'error' => 'Le message doit contenir du texte ou un fichier.'
                        ], 400);
                    }
                    $this->addFlash('error', 'Le message doit contenir du texte ou un fichier.');
                    return $this->redirectToRoute('message_chatroom', ['goalId' => $goalId]);
                }
                
                if ((empty($contentValue) || trim($contentValue) === '') && $hasAttachment) {
                    $message->setContent(null);
                }

                // Handle reply to another message
                $replyToId = $request->request->get('reply_to');
                if ($replyToId) {
                    $replyToMessage = $em->getRepository(Message::class)->find($replyToId);
                    if ($replyToMessage && $replyToMessage->getChatroom()->getId() === $chatroom->getId()) {
                        $message->setReplyTo($replyToMessage);
                    }
                }

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
                        if ($request->isXmlHttpRequest() || $request->headers->get('X-Requested-With') === 'XMLHttpRequest') {
                            return new JsonResponse([
                                'success' => false,
                                'error' => $moderationResult['moderationReason']
                            ], 403);
                        }
                        $this->addFlash('error', $moderationResult['moderationReason']);
                        return $this->redirectToRoute('message_chatroom', ['goalId' => $goalId]);
                    }

                    // Si le message est spam, afficher un avertissement
                    if ($moderationResult['moderationStatus'] === 'hidden') {
                        if ($request->isXmlHttpRequest() || $request->headers->get('X-Requested-With') === 'XMLHttpRequest') {
                            return new JsonResponse([
                                'success' => false,
                                'error' => 'Votre message a été marqué comme spam et sera masqué pour les autres utilisateurs.'
                            ], 403);
                        }
                        $this->addFlash('warning', 'Votre message a été marqué comme spam et sera masqué pour les autres utilisateurs.');
                    }
                }

                $message->setAuthor($user);
                $message->setChatroom($chatroom);
                $message->setCreatedAt(new \DateTime());

                $em->persist($message);
                $em->flush();
                
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
                return $this->redirectToRoute('message_chatroom', ['goalId' => $goalId]);
                
            } catch (\Exception $e) {
                if ($request->isXmlHttpRequest() || $request->headers->get('X-Requested-With') === 'XMLHttpRequest') {
                    return new JsonResponse([
                        'success' => false,
                        'error' => 'Erreur: ' . $e->getMessage()
                    ], 500);
                }
                $this->addFlash('error', 'Erreur: ' . $e->getMessage());
                return $this->redirectToRoute('message_chatroom', ['goalId' => $goalId]);
            }
        }

        // For AJAX requests, return only the messages HTML
        if ($request->isXmlHttpRequest()) {
            return $this->render($template, [
                'chatroom' => $chatroom,
                'goal' => $goal,
                'form' => $form->createView(),
                'readReceiptRepo' => $readReceiptRepo,
                'translationLanguages' => $translator->getSupportedLanguages(),
            ]);
        }

        return $this->render($template, [
            'chatroom' => $chatroom,
            'goal' => $goal,
            'form' => $form->createView(),
            'readReceiptRepo' => $readReceiptRepo,
            'currentUserParticipation' => $currentUserParticipation,
            'isMember' => true,
            'translationLanguages' => $translator->getSupportedLanguages(),
        ]);
    }

    /**
     * Fetch new messages (AJAX polling)
     */
    #[Route('/chatroom/{goalId}/fetch', name: 'message_fetch', methods: ['GET'], requirements: ['goalId' => '\d+'])]
    public function fetchMessages(
        int $goalId,
        Request $request,
        \App\Repository\MessageReadReceiptRepository $readReceiptRepo,
        \App\Repository\GoalRepository $goalRepository,
        EntityManagerInterface $em
    ): JsonResponse {
        // Vérifier que l'utilisateur est connecté
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'Vous devez être connecté'], 401);
        }

        $goal = $goalRepository->find($goalId);
        
        if (!$goal) {
            return new JsonResponse(['error' => 'Goal not found'], 404);
        }
        
        // Vérifier que l'utilisateur est membre approuvé
        $participation = $em->getRepository(\App\Entity\GoalParticipation::class)->findOneBy([
            'user' => $user,
            'goal' => $goal
        ]);
        
        if (!$participation || !$participation->isApproved()) {
            return new JsonResponse(['error' => 'Accès refusé'], 403);
        }
        
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
                'imageName' => $message->getImageName(),
                'imageUrl' => $message->getImageName() ? '/uploads/messages/'.$message->getImageName() : null,
                'fileName' => $message->getFileName(),
                'fileSize' => $message->getFileSize(),
                'fileType' => $message->getFileType(),
                'fileUrl' => $message->getFileName() ? '/uploads/messages/'.$message->getFileName() : null,
                'formattedGeneralFileSize' => $message->getFormattedGeneralFileSize(),
                'fileIcon' => $message->getFileIcon(),
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

    /**
     * Send voice message
     */
    #[Route('/chatroom/{goalId}/send-voice', name: 'message_send_voice', methods: ['POST'], requirements: ['goalId' => '\d+'])]
    public function sendVoiceMessage(
        int $goalId,
        Request $request,
        EntityManagerInterface $em,
        \App\Repository\GoalRepository $goalRepository
    ): JsonResponse {
        try {
            $user = $this->getUser();
            
            if (!$user) {
                return new JsonResponse(['error' => 'Vous devez être connecté'], 401);
            }

            $goal = $goalRepository->find($goalId);
            
            if (!$goal) {
                return new JsonResponse(['error' => 'Goal not found'], 404);
            }

            // Vérifier que l'utilisateur est membre approuvé
            $participation = $em->getRepository(\App\Entity\GoalParticipation::class)->findOneBy([
                'user' => $user,
                'goal' => $goal
            ]);
            
            if (!$participation || !$participation->isApproved()) {
                return new JsonResponse(['error' => 'Accès refusé'], 403);
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

    /**
     * List private chatrooms for a goal
     */
    #[Route('/private-chatrooms/{goalId}', name: 'message_private_chatrooms_list', methods: ['GET'], requirements: ['goalId' => '\d+'])]
    public function listPrivateChatrooms(
        int $goalId,
        \App\Repository\GoalRepository $goalRepository,
        \App\Repository\PrivateChatroomRepository $privateChatroomRepository
    ): Response {
        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté');
            return $this->redirectToRoute('app_login');
        }

        $goal = $goalRepository->find($goalId);
        if (!$goal) {
            throw $this->createNotFoundException('Goal not found');
        }

        // Vérifier que l'utilisateur est membre approuvé
        $participation = $this->entityManager->getRepository(\App\Entity\GoalParticipation::class)->findOneBy([
            'user' => $user,
            'goal' => $goal
        ]);

        if (!$participation || !$participation->isApproved()) {
            $this->addFlash('error', 'Vous devez être membre de ce goal');
            return $this->redirectToRoute('goal_list');
        }

        $privateChatrooms = $privateChatroomRepository->findByUserAndGoal($user, $goal);

        return $this->render('message/private_chatrooms_list.html.twig', [
            'goal' => $goal,
            'privateChatrooms' => $privateChatrooms,
        ]);
    }

    /**
     * Create a new private chatroom
     */
    #[Route('/private-chatroom/create/{goalId}', name: 'message_private_chatroom_create', methods: ['GET', 'POST'], requirements: ['goalId' => '\d+'])]
    public function createPrivateChatroom(
        int $goalId,
        Request $request,
        \App\Repository\GoalRepository $goalRepository
    ): Response {
        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté');
            return $this->redirectToRoute('app_login');
        }

        $goal = $goalRepository->find($goalId);
        if (!$goal) {
            throw $this->createNotFoundException('Goal not found');
        }

        // Vérifier que l'utilisateur est membre approuvé
        $participation = $this->entityManager->getRepository(\App\Entity\GoalParticipation::class)->findOneBy([
            'user' => $user,
            'goal' => $goal
        ]);

        if (!$participation || !$participation->isApproved()) {
            $this->addFlash('error', 'Vous devez être membre de ce goal');
            return $this->redirectToRoute('goal_list');
        }

        // Get all approved members of the goal except current user
        $availableMembers = [];
        foreach ($goal->getGoalParticipations() as $p) {
            if ($p->isApproved() && $p->getUser()->getId() !== $user->getId()) {
                $availableMembers[] = $p->getUser();
            }
        }

        $privateChatroom = new \App\Entity\PrivateChatroom();
        $form = $this->createForm(\App\Form\PrivateChatroomType::class, $privateChatroom, [
            'available_members' => $availableMembers
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $privateChatroom->setParentGoal($goal);
            $privateChatroom->setCreator($user);
            $privateChatroom->setCreatedAt(new \DateTime());

            $this->entityManager->persist($privateChatroom);
            $this->entityManager->flush();

            $this->addFlash('success', 'Sous-groupe privé créé avec succès!');
            return $this->redirectToRoute('message_private_chatroom_show', ['id' => $privateChatroom->getId()]);
        }

        return $this->render('message/private_chatroom_create.html.twig', [
            'goal' => $goal,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Show a private chatroom
     */
    #[Route('/private-chatroom/{id}', name: 'message_private_chatroom_show', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function showPrivateChatroom(
        int $id,
        Request $request,
        \App\Repository\PrivateChatroomRepository $privateChatroomRepository
    ): Response {
        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté');
            return $this->redirectToRoute('app_login');
        }

        $privateChatroom = $privateChatroomRepository->find($id);
        if (!$privateChatroom) {
            throw $this->createNotFoundException('Private chatroom not found');
        }

        // Vérifier que l'utilisateur est membre du sous-groupe
        if (!$privateChatroom->isMember($user)) {
            $this->addFlash('error', 'Vous n\'avez pas accès à ce sous-groupe');
            return $this->redirectToRoute('message_private_chatrooms_list', ['goalId' => $privateChatroom->getParentGoal()->getId()]);
        }

        // Handle message sending
        $message = new Message();
        $form = $this->createForm(\App\Form\MessageType::class, $message);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
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
            $message->setPrivateChatroom($privateChatroom);
            $message->setCreatedAt(new \DateTime());

            $this->entityManager->persist($message);
            $this->entityManager->flush();

            if ($request->isXmlHttpRequest()) {
                return new JsonResponse([
                    'success' => true,
                    'message' => 'Message envoyé!'
                ]);
            }

            return $this->redirectToRoute('message_private_chatroom_show', ['id' => $id]);
        }

        return $this->render('message/private_chatroom_show.html.twig', [
            'privateChatroom' => $privateChatroom,
            'goal' => $privateChatroom->getParentGoal(),
            'form' => $form->createView(),
        ]);
    }

    /**
     * Search messages in a chatroom
     */
    #[Route('/chatroom/{goalId}/search', name: 'message_search', methods: ['GET'], requirements: ['goalId' => '\d+'])]
    public function searchMessages(
        int $goalId,
        Request $request,
        \App\Repository\GoalRepository $goalRepository,
        EntityManagerInterface $em
    ): JsonResponse {
        $user = $this->getUser();

        if (!$user) {
            return new JsonResponse(['error' => 'Vous devez être connecté'], 401);
        }

        $goal = $goalRepository->find($goalId);

        if (!$goal) {
            return new JsonResponse(['error' => 'Goal not found'], 404);
        }

        // Vérifier que l'utilisateur est membre approuvé
        $participation = $em->getRepository(\App\Entity\GoalParticipation::class)->findOneBy([
            'user' => $user,
            'goal' => $goal
        ]);

        if (!$participation || !$participation->isApproved()) {
            return new JsonResponse(['error' => 'Accès refusé'], 403);
        }

        $chatroom = $goal->getChatroom();

        if (!$chatroom) {
            return new JsonResponse(['error' => 'Chatroom introuvable'], 404);
        }

        $query = $request->query->get('q', '');

        if (empty($query) || strlen($query) < 2) {
            return new JsonResponse(['results' => []]);
        }

        try {
            // Search in messages using DQL with simple LIKE
            $dql = "SELECT m FROM App\Entity\Message m 
                    WHERE m.chatroom = :chatroom 
                    AND m.content LIKE :query 
                    ORDER BY m.createdAt DESC";
            
            $queryObj = $em->createQuery($dql);
            $queryObj->setParameter('chatroom', $chatroom);
            $queryObj->setParameter('query', '%' . $query . '%');
            $queryObj->setMaxResults(50);
            
            $messages = $queryObj->getResult();
            
            $results = [];
            foreach ($messages as $message) {
                if ($message->getContent()) {
                    $results[] = [
                        'id' => $message->getId(),
                        'content' => $message->getContent(),
                        'authorFirstName' => $message->getAuthor()->getFirstName(),
                        'authorLastName' => $message->getAuthor()->getLastName(),
                        'createdAt' => $message->getCreatedAt()->format('d/m/Y H:i'),
                        'highlight' => $this->highlightText($message->getContent(), $query)
                    ];
                }
            }

            return new JsonResponse([
                'results' => $results,
                'count' => count($results),
                'query' => $query
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Erreur lors de la recherche: ' . $e->getMessage(),
                'results' => []
            ], 500);
        }
    }

    /**
     * Highlight search term in text
     */
    private function highlightText(string $text, string $query): string
    {
        if (empty($query)) {
            return $text;
        }

        // Escape special regex characters
        $query = preg_quote($query, '/');

        // Highlight with case-insensitive search
        return preg_replace('/(' . $query . ')/i', '<mark>$1</mark>', $text);
    }

    /**
     * Mark message as read
     */
    #[Route('/{id}/mark-read', name: 'message_mark_read', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function markAsRead(
        Message $message,
        \App\Repository\MessageReadReceiptRepository $readReceiptRepo
    ): JsonResponse {
        $user = $this->getUser();
        
        if (!$user) {
            return new JsonResponse(['error' => 'Non authentifié'], 401);
        }

        // Don't mark own messages as read
        if ($message->getAuthor()->getId() === $user->getId()) {
            return new JsonResponse(['success' => true, 'message' => 'Own message']);
        }

        // Check if already read
        if ($readReceiptRepo->hasUserReadMessage($message, $user)) {
            return new JsonResponse(['success' => true, 'message' => 'Already read']);
        }

        // Mark as read
        $readReceiptRepo->markAsRead($message, $user);

        return new JsonResponse([
            'success' => true,
            'readCount' => $readReceiptRepo->getReadCount($message)
        ]);
    }

}

