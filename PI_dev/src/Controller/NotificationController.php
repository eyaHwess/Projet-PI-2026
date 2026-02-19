<?php

namespace App\Controller;

use App\Repository\NotificationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/notifications')]
class NotificationController extends AbstractController
{
    public function __construct(
        private NotificationRepository $notificationRepository,
        private EntityManagerInterface $entityManager
    ) {
    }

    /**
     * Liste toutes les notifications de l'utilisateur
     */
    #[Route('', name: 'app_notifications_index', methods: ['GET'])]
    public function index(): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $notifications = $this->notificationRepository->findByUser($user);

        return $this->render('notification/index.html.twig', [
            'notifications' => $notifications,
        ]);
    }

    /**
     * Récupère le nombre de notifications non lues (API)
     */
    #[Route('/unread-count', name: 'app_notifications_unread_count', methods: ['GET'])]
    public function unreadCount(): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['count' => 0]);
        }

        $count = $this->notificationRepository->countUnreadByUser($user);

        return new JsonResponse(['count' => $count]);
    }

    /**
     * Récupère les notifications non lues (API)
     */
    #[Route('/unread', name: 'app_notifications_unread', methods: ['GET'])]
    public function unread(): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['notifications' => []]);
        }

        $notifications = $this->notificationRepository->findUnreadByUser($user);

        $data = array_map(function ($notification) {
            $createdAt = $notification->getCreatedAt();
            $coachingRequest = $notification->getCoachingRequest();
            $sessionId = null;
            
            // Récupérer l'ID de la session si elle existe
            if ($coachingRequest && $coachingRequest->getSession()) {
                $sessionId = $coachingRequest->getSession()->getId();
            }
            
            return [
                'id' => $notification->getId(),
                'type' => $notification->getType(),
                'message' => $notification->getMessage(),
                'createdAt' => $createdAt ? $createdAt->format('Y-m-d H:i:s') : null,
                'isRead' => $notification->isRead(),
                'sessionId' => $sessionId,
            ];
        }, $notifications);

        return new JsonResponse(['notifications' => $data]);
    }

    /**
     * Marque une notification comme lue
     */
    #[Route('/{id}/mark-read', name: 'app_notifications_mark_read', methods: ['POST'])]
    public function markAsRead(int $id): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['success' => false, 'message' => 'Non authentifié'], 401);
        }

        $notification = $this->notificationRepository->find($id);

        if (!$notification || $notification->getUser() !== $user) {
            return new JsonResponse(['success' => false, 'message' => 'Notification non trouvée'], 404);
        }

        $notification->setIsRead(true);
        $this->entityManager->flush();

        return new JsonResponse(['success' => true]);
    }

    /**
     * Marque toutes les notifications comme lues
     */
    #[Route('/mark-all-read', name: 'app_notifications_mark_all_read', methods: ['POST'])]
    public function markAllAsRead(): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['success' => false, 'message' => 'Non authentifié'], 401);
        }

        $this->notificationRepository->markAllAsReadForUser($user);

        return new JsonResponse(['success' => true]);
    }
}
