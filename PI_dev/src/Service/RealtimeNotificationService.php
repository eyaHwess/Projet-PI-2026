<?php

namespace App\Service;

use App\Entity\Comment;
use App\Entity\Post;
use App\Entity\RealtimeNotification;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;

class RealtimeNotificationService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private HubInterface $hub,
        private LoggerInterface $logger
    ) {
    }

    public function topicForUser(User $user): string
    {
        return sprintf('user/%d/rt-notifications', (int) $user->getId());
    }

    public function notify(
        User $recipient,
        string $type,
        string $message,
        ?Post $relatedPost = null,
        ?Comment $relatedComment = null
    ): ?RealtimeNotification {
        if ((int) $recipient->getId() <= 0) {
            return null;
        }

        $notification = new RealtimeNotification();
        $notification->setRecipient($recipient);
        $notification->setType($type);
        $notification->setMessage($message);
        $notification->setRelatedPost($relatedPost);
        $notification->setRelatedComment($relatedComment);

        $this->entityManager->persist($notification);
        $this->entityManager->flush();

        $payload = [
            'id' => $notification->getId(),
            'type' => $type,
            'message' => $message,
            'createdAt' => $notification->getCreatedAt()?->format('c'),
            'postId' => $relatedPost?->getId(),
            'commentId' => $relatedComment?->getId(),
        ];

        try {
            $this->hub->publish(new Update(
                $this->topicForUser($recipient),
                json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            ));
        } catch (\Throwable $e) {
            // Notification is stored; real-time delivery failure shouldn't break the action.
            $this->logger->error('Failed to publish realtime notification', [
                'recipient_id' => $recipient->getId(),
                'error' => $e->getMessage(),
            ]);
        }

        return $notification;
    }
}

