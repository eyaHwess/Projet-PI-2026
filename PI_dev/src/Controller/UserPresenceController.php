<?php

namespace App\Controller;

use App\Repository\UserPresenceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/presence')]
class UserPresenceController extends AbstractController
{
    public function __construct(
        private UserPresenceRepository $presenceRepo,
        private EntityManagerInterface $entityManager
    ) {}

    /**
     * Update user activity (heartbeat)
     */
    #[Route('/heartbeat', name: 'presence_heartbeat', methods: ['POST'])]
    public function heartbeat(): JsonResponse
    {
        $user = $this->getUser();
        
        if (!$user) {
            return new JsonResponse(['error' => 'Non authentifié'], 401);
        }

        $this->presenceRepo->updateActivity($user);

        return new JsonResponse(['success' => true]);
    }

    /**
     * Set typing status
     */
    #[Route('/typing/{chatroomId}', name: 'presence_typing', methods: ['POST'], requirements: ['chatroomId' => '\d+'])]
    public function setTyping(int $chatroomId, Request $request): JsonResponse
    {
        $user = $this->getUser();
        
        if (!$user) {
            return new JsonResponse(['error' => 'Non authentifié'], 401);
        }

        $isTyping = $request->request->get('isTyping', false);
        $this->presenceRepo->setTyping($user, $chatroomId, (bool)$isTyping);

        return new JsonResponse(['success' => true]);
    }

    /**
     * Get users typing in chatroom
     */
    #[Route('/typing/{chatroomId}/users', name: 'presence_typing_users', methods: ['GET'], requirements: ['chatroomId' => '\d+'])]
    public function getTypingUsers(int $chatroomId): JsonResponse
    {
        $user = $this->getUser();
        
        if (!$user) {
            return new JsonResponse(['error' => 'Non authentifié'], 401);
        }

        // Clean up old typing indicators first
        $this->presenceRepo->cleanupOldTypingIndicators();

        $typingPresences = $this->presenceRepo->getUsersTypingInChatroom($chatroomId);
        
        $typingUsers = [];
        foreach ($typingPresences as $presence) {
            // Don't include current user
            if ($presence->getUser()->getId() === $user->getId()) {
                continue;
            }

            $typingUsers[] = [
                'id' => $presence->getUser()->getId(),
                'firstName' => $presence->getUser()->getFirstName(),
                'lastName' => $presence->getUser()->getLastName(),
            ];
        }

        return new JsonResponse([
            'typingUsers' => $typingUsers,
            'count' => count($typingUsers)
        ]);
    }

    /**
     * Get online users in chatroom
     */
    #[Route('/online/{chatroomId}', name: 'presence_online_users', methods: ['GET'], requirements: ['chatroomId' => '\d+'])]
    public function getOnlineUsers(int $chatroomId, \App\Repository\GoalRepository $goalRepo): JsonResponse
    {
        $user = $this->getUser();
        
        if (!$user) {
            return new JsonResponse(['error' => 'Non authentifié'], 401);
        }

        // Get chatroom and its participants
        $chatroom = $this->entityManager->getRepository(\App\Entity\Chatroom::class)->find($chatroomId);
        
        if (!$chatroom) {
            return new JsonResponse(['error' => 'Chatroom introuvable'], 404);
        }

        $goal = $chatroom->getGoal();
        $participants = $goal->getGoalParticipations();

        $onlineUsers = [];
        $awayUsers = [];
        $offlineUsers = [];

        foreach ($participants as $participation) {
            if (!$participation->isApproved()) {
                continue;
            }

            $participant = $participation->getUser();
            $presence = $this->presenceRepo->findOneBy(['user' => $participant]);

            $userData = [
                'id' => $participant->getId(),
                'firstName' => $participant->getFirstName(),
                'lastName' => $participant->getLastName(),
                'initials' => substr($participant->getFirstName(), 0, 1) . substr($participant->getLastName(), 0, 1),
                'role' => $participation->getRole(),
                'status' => $presence ? $presence->getOnlineStatus() : 'offline',
                'lastSeen' => $presence ? $presence->getLastSeenText() : 'Jamais vu',
            ];

            if ($presence && $presence->isOnline()) {
                $onlineUsers[] = $userData;
            } elseif ($presence && $presence->getOnlineStatus() === 'away') {
                $awayUsers[] = $userData;
            } else {
                $offlineUsers[] = $userData;
            }
        }

        return new JsonResponse([
            'online' => $onlineUsers,
            'away' => $awayUsers,
            'offline' => $offlineUsers,
            'counts' => [
                'online' => count($onlineUsers),
                'away' => count($awayUsers),
                'offline' => count($offlineUsers),
                'total' => count($onlineUsers) + count($awayUsers) + count($offlineUsers),
            ]
        ]);
    }

    /**
     * Get user presence status
     */
    #[Route('/status/{userId}', name: 'presence_user_status', methods: ['GET'], requirements: ['userId' => '\d+'])]
    public function getUserStatus(int $userId): JsonResponse
    {
        $targetUser = $this->entityManager->getRepository(\App\Entity\User::class)->find($userId);
        
        if (!$targetUser) {
            return new JsonResponse(['error' => 'Utilisateur introuvable'], 404);
        }

        $presence = $this->presenceRepo->findOneBy(['user' => $targetUser]);

        return new JsonResponse([
            'userId' => $userId,
            'status' => $presence ? $presence->getOnlineStatus() : 'offline',
            'isOnline' => $presence ? $presence->isOnline() : false,
            'lastSeen' => $presence ? $presence->getLastSeenText() : 'Jamais vu',
            'isTyping' => $presence ? $presence->isTyping() : false,
        ]);
    }
}
