<?php

namespace App\Controller;

use App\Entity\Chatroom;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Workflow\WorkflowInterface;

#[Route('/chatroom')]
class ChatroomWorkflowController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    /**
     * Lock a chatroom
     */
    #[Route('/{id}/lock', name: 'chatroom_lock', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function lock(
        Chatroom $chatroom,
        Request $request,
        WorkflowInterface $chatroomStateMachine
    ): Response {
        $user = $this->getUser();
        
        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté');
            return $this->redirectToRoute('app_login');
        }

        // Vérifier les permissions (admin ou owner du goal)
        $goal = $chatroom->getGoal();
        $participation = $goal->getUserParticipation($user);
        
        if (!$participation || !$participation->canModerate()) {
            $this->addFlash('error', 'Vous n\'avez pas la permission de verrouiller ce chatroom');
            return $this->redirectToRoute('message_chatroom', ['goalId' => $goal->getId()]);
        }

        // Vérifier le token CSRF
        if (!$this->isCsrfTokenValid('lock-chatroom-' . $chatroom->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide');
            return $this->redirectToRoute('message_chatroom', ['goalId' => $goal->getId()]);
        }

        // Appliquer la transition
        if ($chatroomStateMachine->can($chatroom, 'lock')) {
            $chatroomStateMachine->apply($chatroom, 'lock');
            $this->entityManager->flush();
            
            $this->addFlash('success', 'Chatroom verrouillé avec succès');
        } else {
            $this->addFlash('error', 'Impossible de verrouiller ce chatroom');
        }

        return $this->redirectToRoute('message_chatroom', ['goalId' => $goal->getId()]);
    }

    /**
     * Unlock a chatroom
     */
    #[Route('/{id}/unlock', name: 'chatroom_unlock', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function unlock(
        Chatroom $chatroom,
        Request $request,
        WorkflowInterface $chatroomStateMachine
    ): Response {
        $user = $this->getUser();
        
        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté');
            return $this->redirectToRoute('app_login');
        }

        // Vérifier les permissions
        $goal = $chatroom->getGoal();
        $participation = $goal->getUserParticipation($user);
        
        if (!$participation || !$participation->canModerate()) {
            $this->addFlash('error', 'Vous n\'avez pas la permission de déverrouiller ce chatroom');
            return $this->redirectToRoute('message_chatroom', ['goalId' => $goal->getId()]);
        }

        // Vérifier le token CSRF
        if (!$this->isCsrfTokenValid('unlock-chatroom-' . $chatroom->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide');
            return $this->redirectToRoute('message_chatroom', ['goalId' => $goal->getId()]);
        }

        // Appliquer la transition
        if ($chatroomStateMachine->can($chatroom, 'unlock')) {
            $chatroomStateMachine->apply($chatroom, 'unlock');
            $this->entityManager->flush();
            
            $this->addFlash('success', 'Chatroom déverrouillé avec succès');
        } else {
            $this->addFlash('error', 'Impossible de déverrouiller ce chatroom');
        }

        return $this->redirectToRoute('message_chatroom', ['goalId' => $goal->getId()]);
    }

    /**
     * Archive a chatroom
     */
    #[Route('/{id}/archive', name: 'chatroom_archive', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function archive(
        Chatroom $chatroom,
        Request $request,
        WorkflowInterface $chatroomStateMachine
    ): Response {
        $user = $this->getUser();
        
        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté');
            return $this->redirectToRoute('app_login');
        }

        // Vérifier les permissions
        $goal = $chatroom->getGoal();
        $participation = $goal->getUserParticipation($user);
        
        if (!$participation || !$participation->canModerate()) {
            $this->addFlash('error', 'Vous n\'avez pas la permission d\'archiver ce chatroom');
            return $this->redirectToRoute('message_chatroom', ['goalId' => $goal->getId()]);
        }

        // Vérifier le token CSRF
        if (!$this->isCsrfTokenValid('archive-chatroom-' . $chatroom->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide');
            return $this->redirectToRoute('message_chatroom', ['goalId' => $goal->getId()]);
        }

        // Appliquer la transition
        if ($chatroomStateMachine->can($chatroom, 'archive')) {
            $chatroomStateMachine->apply($chatroom, 'archive');
            $this->entityManager->flush();
            
            $this->addFlash('success', 'Chatroom archivé avec succès (lecture seule)');
        } else {
            $this->addFlash('error', 'Impossible d\'archiver ce chatroom');
        }

        return $this->redirectToRoute('message_chatroom', ['goalId' => $goal->getId()]);
    }

    /**
     * Restore a chatroom from archive
     */
    #[Route('/{id}/restore', name: 'chatroom_restore', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function restore(
        Chatroom $chatroom,
        Request $request,
        WorkflowInterface $chatroomStateMachine
    ): Response {
        $user = $this->getUser();
        
        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté');
            return $this->redirectToRoute('app_login');
        }

        // Vérifier les permissions
        $goal = $chatroom->getGoal();
        $participation = $goal->getUserParticipation($user);
        
        if (!$participation || !$participation->canModerate()) {
            $this->addFlash('error', 'Vous n\'avez pas la permission de restaurer ce chatroom');
            return $this->redirectToRoute('message_chatroom', ['goalId' => $goal->getId()]);
        }

        // Vérifier le token CSRF
        if (!$this->isCsrfTokenValid('restore-chatroom-' . $chatroom->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide');
            return $this->redirectToRoute('message_chatroom', ['goalId' => $goal->getId()]);
        }

        // Appliquer la transition
        if ($chatroomStateMachine->can($chatroom, 'restore')) {
            $chatroomStateMachine->apply($chatroom, 'restore');
            $this->entityManager->flush();
            
            $this->addFlash('success', 'Chatroom restauré avec succès');
        } else {
            $this->addFlash('error', 'Impossible de restaurer ce chatroom');
        }

        return $this->redirectToRoute('message_chatroom', ['goalId' => $goal->getId()]);
    }

    /**
     * Delete a chatroom (soft delete)
     */
    #[Route('/{id}/delete', name: 'chatroom_delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function delete(
        Chatroom $chatroom,
        Request $request,
        WorkflowInterface $chatroomStateMachine
    ): Response {
        $user = $this->getUser();
        
        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté');
            return $this->redirectToRoute('app_login');
        }

        // Vérifier les permissions (owner uniquement)
        $goal = $chatroom->getGoal();
        $participation = $goal->getUserParticipation($user);
        
        if (!$participation || $participation->getRole() !== 'OWNER') {
            $this->addFlash('error', 'Seul le propriétaire peut supprimer ce chatroom');
            return $this->redirectToRoute('message_chatroom', ['goalId' => $goal->getId()]);
        }

        // Vérifier le token CSRF
        if (!$this->isCsrfTokenValid('delete-chatroom-' . $chatroom->getId(), $request->request->get('_token'))) {
            $this->addFlash('error', 'Token CSRF invalide');
            return $this->redirectToRoute('message_chatroom', ['goalId' => $goal->getId()]);
        }

        // Appliquer la transition
        if ($chatroomStateMachine->can($chatroom, 'delete')) {
            $chatroomStateMachine->apply($chatroom, 'delete');
            $this->entityManager->flush();
            
            $this->addFlash('success', 'Chatroom supprimé avec succès');
            return $this->redirectToRoute('goal_list');
        } else {
            $this->addFlash('error', 'Impossible de supprimer ce chatroom');
        }

        return $this->redirectToRoute('message_chatroom', ['goalId' => $goal->getId()]);
    }
}
