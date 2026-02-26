<?php

namespace App\Controller;

use App\Repository\RealtimeNotificationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/rt-notifications', name: 'app_rt_notifications_')]
class RealtimeNotificationController extends AbstractController
{
    public function __construct(
        private RealtimeNotificationRepository $realtimeNotificationRepository,
        private EntityManagerInterface $entityManager
    ) {
    }

    #[Route('/unread-count', name: 'unread_count', methods: ['GET'])]
    public function unreadCount(): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['count' => 0]);
        }

        $count = $this->realtimeNotificationRepository->countUnreadByRecipient($user);
        return new JsonResponse(['count' => $count]);
    }

    #[Route('/recent', name: 'recent', methods: ['GET'])]
    public function recent(): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['notifications' => []]);
        }

        $items = $this->realtimeNotificationRepository->findRecentByRecipient($user, 10);
        $data = array_map(static function ($n) {
            return [
                'id' => $n->getId(),
                'type' => $n->getType(),
                'message' => $n->getMessage(),
                'createdAt' => $n->getCreatedAt()?->format('c'),
                'isRead' => $n->isRead(),
                'postId' => $n->getRelatedPost()?->getId(),
                'commentId' => $n->getRelatedComment()?->getId(),
            ];
        }, $items);

        return new JsonResponse(['notifications' => $data]);
    }

    #[Route('/{id}/mark-read', name: 'mark_read', methods: ['POST'])]
    public function markRead(int $id): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['success' => false], Response::HTTP_UNAUTHORIZED);
        }

        $n = $this->realtimeNotificationRepository->find($id);
        if (!$n || $n->getRecipient() !== $user) {
            return new JsonResponse(['success' => false], Response::HTTP_NOT_FOUND);
        }

        $n->setIsRead(true);
        $this->entityManager->flush();

        return new JsonResponse(['success' => true]);
    }

    #[Route('/mark-all-read', name: 'mark_all_read', methods: ['POST'])]
    public function markAllRead(): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['success' => false], Response::HTTP_UNAUTHORIZED);
        }

        $this->realtimeNotificationRepository->markAllAsRead($user);
        return new JsonResponse(['success' => true]);
    }
}

