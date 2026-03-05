<?php

namespace App\Controller;

use App\Entity\Chatroom;
use App\Entity\GoalParticipation;
use App\Entity\Message;
use App\Entity\User;
use App\Form\MessageType;
use App\Repository\MessageReadReceiptRepository;
use App\Service\ModerationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ChatroomController extends AbstractController
{
    public function __construct(
        private ModerationService $moderationService,
        private MessageReadReceiptRepository $readReceiptRepo,
    ) {}

    #[Route('/chatroom/{id}/messages', name: 'chatroom_messages', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function messages(Chatroom $chatroom, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'Unauthorized'], 401);
        }

        $goal = $chatroom->getGoal();
        $participation = $em->getRepository(GoalParticipation::class)->findOneBy([
            'goal' => $goal,
            'user' => $user,
        ]);

        if (!$participation || !$participation->isApproved()) {
            return new JsonResponse(['error' => 'Forbidden'], 403);
        }

        return $this->render('chatroom/_messages.html.twig', [
            'chatroom'                => $chatroom,
            'goal'                    => $goal,
            'currentUserParticipation'=> $participation,
            'readReceiptRepo'         => $this->readReceiptRepo,
        ]);
    }

    #[Route('/chatroom/{id}', name: 'chatroom_show', requirements: ['id' => '\d+'])]
    public function show(
        Chatroom $chatroom,
        Request $request,
        EntityManagerInterface $em
    ): Response {

        // Vérifier que l'utilisateur est connecté
        $user = $this->getUser();
        if (!$user instanceof User) {
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
            return $this->redirectToRoute('app_goal_index');
        }

        // Vérifier que la participation est approuvée
        if (!$participation->isApproved()) {
            $this->addFlash('warning', 'Votre demande d\'accès est en attente d\'approbation.');
            return $this->redirectToRoute('app_goal_index');
        }

        $message = new Message();

        $form = $this->createForm(MessageType::class, $message);
        $form->handleRequest($request);

        $isAjax = $request->headers->get('X-Requested-With') === 'XMLHttpRequest';

        if ($form->isSubmitted() && ($form->isValid() || $isAjax)) {
            // Get content from form (or raw POST for AJAX)
            $content = $message->getContent() ?? $request->request->all('message')['content'] ?? null;

            // Modération du contenu
            if ($content && trim($content) !== '') {
                $moderationResult = $this->moderationService->analyzeMessage($content);

                $message->setIsToxic($moderationResult['isToxic']);
                $message->setIsSpam($moderationResult['isSpam']);
                $message->setToxicityScore($moderationResult['toxicityScore']);
                $message->setSpamScore($moderationResult['spamScore']);
                $message->setModerationStatus($moderationResult['moderationStatus']);
                $message->setModerationReason($moderationResult['moderationReason']);

                if ($moderationResult['moderationStatus'] === 'blocked') {
                    if ($isAjax) {
                        return new JsonResponse(['success' => false, 'error' => $moderationResult['moderationReason']], 400);
                    }
                    $this->addFlash('error', $moderationResult['moderationReason']);
                    return $this->redirectToRoute('chatroom_show', ['id' => $chatroom->getId()]);
                }
            }

            $message->setContent($content);
            $message->setAuthor($user);
            $message->setChatroom($chatroom);
            $message->setCreatedAt(new \DateTimeImmutable());

            // Handle file upload: try form field first, then raw request files
            $attachmentFile = $form->get('attachment')->getData();
            $debugInfo = 'form_attachment=' . ($attachmentFile ? $attachmentFile->getClientOriginalName() : 'null');
            $rawFiles = $request->files->get('message');
            $debugInfo .= ' raw_files_keys=' . json_encode($rawFiles ? array_keys((array)$rawFiles) : []);
            if (!$attachmentFile) {
                if (is_array($rawFiles) && isset($rawFiles['attachment'])) {
                    $attachmentFile = $rawFiles['attachment'];
                    $debugInfo .= ' fallback_used=' . $attachmentFile->getClientOriginalName();
                }
            }

            if ($attachmentFile instanceof \Symfony\Component\HttpFoundation\File\UploadedFile) {
                $mimeType     = $attachmentFile->getMimeType() ?? '';
                $fileSize     = $attachmentFile->getSize(); // read BEFORE move()
                $uploadDir    = $this->getParameter('kernel.project_dir') . '/public/uploads/messages';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0775, true);
                }
                $originalName = pathinfo($attachmentFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = preg_replace('/[^a-zA-Z0-9_-]/', '_', $originalName);
                $ext          = $attachmentFile->guessExtension() ?? $attachmentFile->getClientOriginalExtension();
                $newFilename  = $safeFilename . '_' . uniqid() . '.' . $ext;
                $attachmentFile->move($uploadDir, $newFilename);

                if (str_starts_with($mimeType, 'image/')) {
                    $message->setImageName($newFilename);
                    $message->setImageSize($fileSize);
                } else {
                    $message->setFileName($newFilename);
                    $message->setFileSize($fileSize);
                    $message->setFileType($mimeType);
                }
            }

            $em->persist($message);
            $em->flush();

            if ($isAjax) {
                $imageUrl = $message->getImageName() ? '/uploads/messages/' . $message->getImageName() : null;
                $fileUrl  = $message->getFileName()  ? '/uploads/messages/' . $message->getFileName()  : null;
                return new JsonResponse([
                    'debug' => $debugInfo,
                    'success' => true,
                    'message' => [
                        'id'        => $message->getId(),
                        'content'   => $message->getContent(),
                        'createdAt' => $message->getCreatedAt()->format('Y-m-d H:i:s'),
                        'author'    => [
                            'id'        => $user->getId(),
                            'firstName' => $user->getFirstName(),
                            'lastName'  => $user->getLastName(),
                            'photoUrl'  => $user->getPhotoUrl(),
                        ],
                        'moderationStatus' => $message->getModerationStatus(),
                        'imageUrl'  => $imageUrl,
                        'fileUrl'   => $fileUrl,
                        'fileName'  => $message->getFileName(),
                    ]
                ]);
            }

            return $this->redirectToRoute('chatroom_show', ['id' => $chatroom->getId()]);
        }

        return $this->render('chatroom/chatroom.html.twig', [
            'chatroom' => $chatroom,
            'goal' => $goal,
            'form' => $form->createView(),
            'currentUserParticipation' => $participation,
            'readReceiptRepo' => $this->readReceiptRepo,
        ]);
    }
}
