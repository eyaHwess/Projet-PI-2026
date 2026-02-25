<?php

namespace App\Controller;

use App\Entity\Notification;
use App\Service\NotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/notification')]
class NotificationController extends AbstractController
{
    public function __construct(
        private NotificationService $notificationService,
        private EntityManagerInterface $entityManager
    ) {}

    /**
     * Afficher toutes les notifications
     */
    #[Route('/', name: 'notification_list', methods: ['GET'])]
    public function list(): Response
    {
        $user = $this->getUser();
        
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $notifications = $this->notificationService->getRecentNotifications($user, 50);
        $unreadCount = $this->notificationService->getUnreadCount($user);

        return $this->render('notification/list.html.twig', [
            'notifications' => $notifications,
            'unreadCount' => $unreadCount
        ]);
    }

    /**
     * Récupérer les nouvelles notifications (AJAX)
     */
    #[Route('/fetch', name: 'notification_fetch', methods: ['GET'])]
    public function fetch(): JsonResponse
    {
        $user = $this->getUser();
        
        if (!$user) {
            return new JsonResponse(['error' => 'Non authentifié'], 401);
        }

        $notifications = $this->notificationService->getRecentNotifications($user, 10);
        $unreadCount = $this->notificationService->getUnreadCount($user);

        $notificationsData = [];
        foreach ($notifications as $notification) {
            $notificationsData[] = [
                'id' => $notification->getId(),
                'type' => $notification->getType(),
                'message' => $notification->getMessage(),
                'isRead' => $notification->isRead(),
                'createdAt' => $notification->getCreatedAt()->format('c'),
                'html' => $this->renderView('notification/_notification_item.html.twig', [
                    'notification' => $notification
                ])
            ];
        }

        return new JsonResponse([
            'notifications' => $notificationsData,
            'unreadCount' => $unreadCount
        ]);
    }

    /**
     * Marquer une notification comme lue
     */
    #[Route('/{id}/mark-read', name: 'notification_mark_read', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function markRead(Notification $notification): JsonResponse
    {
        $user = $this->getUser();
        
        if (!$user || $notification->getUser()->getId() !== $user->getId()) {
            return new JsonResponse(['error' => 'Non autorisé'], 403);
        }

        $this->notificationService->markAsRead($notification);

        return new JsonResponse([
            'success' => true,
            'unreadCount' => $this->notificationService->getUnreadCount($user)
        ]);
    }

    /**
     * Marquer toutes les notifications comme lues
     */
    #[Route('/mark-all-read', name: 'notification_mark_all_read', methods: ['POST'])]
    public function markAllRead(): JsonResponse
    {
        $user = $this->getUser();
        
        if (!$user) {
            return new JsonResponse(['error' => 'Non authentifié'], 401);
        }

        $this->notificationService->markAllAsRead($user);

        return new JsonResponse([
            'success' => true,
            'unreadCount' => 0
        ]);
    }

    /**
     * Supprimer une notification
     */
    #[Route('/{id}/delete', name: 'notification_delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function delete(Notification $notification): JsonResponse
    {
        $user = $this->getUser();
        
        if (!$user || $notification->getUser()->getId() !== $user->getId()) {
            return new JsonResponse(['error' => 'Non autorisé'], 403);
        }

        $this->entityManager->remove($notification);
        $this->entityManager->flush();

        return new JsonResponse([
            'success' => true,
            'unreadCount' => $this->notificationService->getUnreadCount($user)
        ]);
    }
}

