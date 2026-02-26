<?php

namespace App\Controller;

use App\Entity\Chatroom;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Workflow\WorkflowInterface;

#[Route('/chatroom')]
class ChatroomStateController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    #[Route('/{id}/lock', name: 'chatroom_lock', methods: ['POST'])]
    public function lock(Chatroom $chatroom, WorkflowInterface $chatroomStateMachine): Response
    {
        $user = $this->getUser();
        
        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté');
            return $this->redirectToRoute('app_login');
        }

        $goal = $chatroom->getGoal();
        $participation = $goal->getUserParticipation($user);

        // Seuls les admins et le créateur peuvent verrouiller
        if (!$participation || !$participation->canModerate()) {
            $this->addFlash('error', 'Vous n\'avez pas la permission de verrouiller ce chatroom');
            return $this->redirectToRoute('message_chatroom', ['goalId' => $goal->getId()]);
        }

        // Vérifier si la transition est possible
        if (!$chatroomStateMachine->can($chatroom, 'lock')) {
            $this->addFlash('error', 'Impossible de verrouiller ce chatroom dans son état actuel');
            return $this->redirectToRoute('message_chatroom', ['goalId' => $goal->getId()]);
        }

        // Appliquer la transition
        $chatroomStateMachine->apply($chatroom, 'lock');
        $this->entityManager->flush();

        $this->addFlash('success', '🔒 Chatroom verrouillé. Les membres ne peuvent plus envoyer de messages.');
        return $this->redirectToRoute('message_chatroom', ['goalId' => $goal->getId()]);
    }

    #[Route('/{id}/unlock', name: 'chatroom_unlock', methods: ['POST'])]
    public function unlock(Chatroom $chatroom, WorkflowInterface $chatroomStateMachine): Response
    {
        $user = $this->getUser();
        
        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté');
            return $this->redirectToRoute('app_login');
        }

        $goal = $chatroom->getGoal();
        $participation = $goal->getUserParticipation($user);

        if (!$participation || !$participation->canModerate()) {
            $this->addFlash('error', 'Vous n\'avez pas la permission de déverrouiller ce chatroom');
            return $this->redirectToRoute('message_chatroom', ['goalId' => $goal->getId()]);
        }

        if (!$chatroomStateMachine->can($chatroom, 'unlock')) {
            $this->addFlash('error', 'Impossible de déverrouiller ce chatroom dans son état actuel');
            return $this->redirectToRoute('message_chatroom', ['goalId' => $goal->getId()]);
        }

        $chatroomStateMachine->apply($chatroom, 'unlock');
        $this->entityManager->flush();

        $this->addFlash('success', '🟢 Chatroom déverrouillé. Les membres peuvent à nouveau envoyer des messages.');
        return $this->redirectToRoute('message_chatroom', ['goalId' => $goal->getId()]);
    }

    #[Route('/{id}/archive', name: 'chatroom_archive', methods: ['POST'])]
    public function archive(Chatroom $chatroom, WorkflowInterface $chatroomStateMachine): Response
    {
        $user = $this->getUser();
        
        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté');
            return $this->redirectToRoute('app_login');
        }

        $goal = $chatroom->getGoal();
        $participation = $goal->getUserParticipation($user);

        if (!$participation || !$participation->canModerate()) {
            $this->addFlash('error', 'Vous n\'avez pas la permission d\'archiver ce chatroom');
            return $this->redirectToRoute('message_chatroom', ['goalId' => $goal->getId()]);
        }

        if (!$chatroomStateMachine->can($chatroom, 'archive')) {
            $this->addFlash('error', 'Impossible d\'archiver ce chatroom dans son état actuel');
            return $this->redirectToRoute('message_chatroom', ['goalId' => $goal->getId()]);
        }

        $chatroomStateMachine->apply($chatroom, 'archive');
        $this->entityManager->flush();

        $this->addFlash('success', '📦 Chatroom archivé. Le chatroom est maintenant en lecture seule.');
        return $this->redirectToRoute('message_chatroom', ['goalId' => $goal->getId()]);
    }

    #[Route('/{id}/delete', name: 'chatroom_delete', methods: ['POST'])]
    public function delete(Chatroom $chatroom, WorkflowInterface $chatroomStateMachine): Response
    {
        $user = $this->getUser();
        
        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté');
            return $this->redirectToRoute('app_login');
        }

        $goal = $chatroom->getGoal();
        $participation = $goal->getUserParticipation($user);

        // Seul le créateur du goal peut supprimer le chatroom
        if (!$participation || $participation->getRole() !== 'OWNER') {
            $this->addFlash('error', 'Seul le créateur du goal peut supprimer le chatroom');
            return $this->redirectToRoute('message_chatroom', ['goalId' => $goal->getId()]);
        }

        if (!$chatroomStateMachine->can($chatroom, 'delete')) {
            $this->addFlash('error', 'Impossible de supprimer ce chatroom dans son état actuel');
            return $this->redirectToRoute('message_chatroom', ['goalId' => $goal->getId()]);
        }

        $chatroomStateMachine->apply($chatroom, 'delete');
        $this->entityManager->flush();

        $this->addFlash('success', '🔴 Chatroom supprimé (soft delete). Le chatroom n\'est plus accessible.');
        return $this->redirectToRoute('goal_show', ['id' => $goal->getId()]);
    }

    #[Route('/{id}/restore', name: 'chatroom_restore', methods: ['POST'])]
    public function restore(Chatroom $chatroom, WorkflowInterface $chatroomStateMachine): Response
    {
        $user = $this->getUser();
        
        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté');
            return $this->redirectToRoute('app_login');
        }

        $goal = $chatroom->getGoal();
        $participation = $goal->getUserParticipation($user);

        if (!$participation || $participation->getRole() !== 'OWNER') {
            $this->addFlash('error', 'Seul le créateur du goal peut restaurer le chatroom');
            return $this->redirectToRoute('goal_show', ['id' => $goal->getId()]);
        }

        if (!$chatroomStateMachine->can($chatroom, 'restore')) {
            $this->addFlash('error', 'Impossible de restaurer ce chatroom');
            return $this->redirectToRoute('goal_show', ['id' => $goal->getId()]);
        }

        $chatroomStateMachine->apply($chatroom, 'restore');
        $this->entityManager->flush();

        $this->addFlash('success', '🟢 Chatroom restauré. Le chatroom est à nouveau actif.');
        return $this->redirectToRoute('message_chatroom', ['goalId' => $goal->getId()]);
    }
}
