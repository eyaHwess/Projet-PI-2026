<?php

namespace App\Controller;

use App\Entity\Chatroom;
use App\Entity\Message;
use App\Form\MessageType;
use App\Service\ModerationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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

        return $this->render('chatroom/chatroom.html.twig', [
            'chatroom' => $chatroom,
            'goal' => $goal,
            'form' => $form->createView(),
        ]);
    }
}
