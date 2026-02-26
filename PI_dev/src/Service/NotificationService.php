<?php

namespace App\Service;

use App\Entity\Notification;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Twig\Environment;

class NotificationService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ?HubInterface $hub = null,
        private ?Environment $twig = null
    ) {}

    /**
     * Créer et publier une notification
     */
    public function createAndPublish(
        User $user,
        string $type,
        string $message,
        $relatedEntity = null
    ): Notification {
        // Créer la notification
        $notification = new Notification();
        $notification->setUser($user);
        $notification->setType($type);
        $notification->setMessage($message);
        
        if ($relatedEntity instanceof \App\Entity\CoachingRequest) {
            $notification->setCoachingRequest($relatedEntity);
        }
        
        $this->entityManager->persist($notification);
        $this->entityManager->flush();
        
        // Publier via Mercure (si disponible)
        $this->publishNotification($notification);
        
        return $notification;
    }

    /**
     * Publier une notification via Mercure
     */
    public function publishNotification(Notification $notification): void
    {
        if (!$this->hub || !$this->twig) {
            return; // Mercure non disponible, le polling prendra le relais
        }

        try {
            // Générer le HTML de la notification
            $html = $this->twig->render('notification/_notification_item.html.twig', [
                'notification' => $notification
            ]);

            // Créer l'update Mercure
            $update = new Update(
                'notification/user/' . $notification->getUser()->getId(),
                json_encode([
                    'id' => $notification->getId(),
                    'type' => $notification->getType(),
                    'message' => $notification->getMessage(),
                    'isRead' => $notification->isRead(),
                    'createdAt' => $notification->getCreatedAt()->format('c'),
                    'html' => $html
                ])
            );

            $this->hub->publish($update);
        } catch (\Exception $e) {
            // Log l'erreur mais ne pas bloquer l'application
            error_log('Mercure notification publish failed: ' . $e->getMessage());
        }
    }

    /**
     * Marquer une notification comme lue
     */
    public function markAsRead(Notification $notification): void
    {
        $notification->setIsRead(true);
        $this->entityManager->flush();
    }

    /**
     * Marquer toutes les notifications d'un utilisateur comme lues
     */
    public function markAllAsRead(User $user): void
    {
        $notifications = $this->entityManager
            ->getRepository(Notification::class)
            ->findBy(['user' => $user, 'isRead' => false]);

        foreach ($notifications as $notification) {
            $notification->setIsRead(true);
        }

        $this->entityManager->flush();
    }

    /**
     * Obtenir le nombre de notifications non lues
     */
    public function getUnreadCount(User $user): int
    {
        return $this->entityManager
            ->getRepository(Notification::class)
            ->count(['user' => $user, 'isRead' => false]);
    }

    /**
     * Obtenir les notifications récentes d'un utilisateur
     */
    public function getRecentNotifications(User $user, int $limit = 10): array
    {
        return $this->entityManager
            ->getRepository(Notification::class)
            ->findBy(
                ['user' => $user],
                ['createdAt' => 'DESC'],
                $limit
            );
    }

    /**
     * Notifier l'utilisateur que sa demande a été acceptée
     */
    public function notifyRequestAccepted(\App\Entity\CoachingRequest $coachingRequest): void
    {
        $coach = $coachingRequest->getCoach();
        $user = $coachingRequest->getUser();
        
        if (!$user) {
            return;
        }

        $message = sprintf(
            'Votre demande de coaching a été acceptée par %s %s',
            $coach->getFirstName(),
            $coach->getLastName()
        );

        $this->createAndPublish(
            $user,
            'coaching_accepted',
            $message,
            $coachingRequest
        );
    }

    /**
     * Notifier l'utilisateur que sa demande a été refusée
     */
    public function notifyRequestDeclined(\App\Entity\CoachingRequest $coachingRequest): void
    {
        $coach = $coachingRequest->getCoach();
        $user = $coachingRequest->getUser();
        
        if (!$user) {
            return;
        }

        $message = sprintf(
            'Votre demande de coaching a été refusée par %s %s',
            $coach->getFirstName(),
            $coach->getLastName()
        );

        $this->createAndPublish(
            $user,
            'coaching_rejected',
            $message,
            $coachingRequest
        );
    }

    /**
     * Notifier l'utilisateur que sa demande est en attente
     */
    public function notifyRequestPending(\App\Entity\CoachingRequest $coachingRequest): void
    {
        $coach = $coachingRequest->getCoach();
        $user = $coachingRequest->getUser();
        
        if (!$user) {
            return;
        }

        $message = sprintf(
            'Votre demande de coaching avec %s %s est de nouveau en attente',
            $coach->getFirstName(),
            $coach->getLastName()
        );

        $this->createAndPublish(
            $user,
            'coaching_request',
            $message,
            $coachingRequest
        );
    }

    /**
     * Notifier le coach qu'il a reçu une nouvelle demande
     */
    public function notifyCoachNewRequest(\App\Entity\CoachingRequest $coachingRequest): void
    {
        $coach = $coachingRequest->getCoach();
        $user = $coachingRequest->getUser();
        
        if (!$coach) {
            return;
        }

        $message = sprintf(
            'Nouvelle demande de coaching de %s %s',
            $user->getFirstName(),
            $user->getLastName()
        );

        $this->createAndPublish(
            $coach,
            'coaching_request',
            $message,
            $coachingRequest
        );
    }
}

