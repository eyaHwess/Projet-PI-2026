<?php

namespace App\EventSubscriber;

use App\Event\Moderation\ToxicPublishAttemptEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;

class AdminViolationMercureSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private HubInterface $hub
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ToxicPublishAttemptEvent::class => 'onToxicPublishAttempt',
        ];
    }

    public function onToxicPublishAttempt(ToxicPublishAttemptEvent $event): void
    {
        $payload = [
            'type' => 'violation',
            'message' => sprintf('Toxic publish attempt by %s', $event->user->getEmail()),
            'userId' => $event->user->getId(),
            'userEmail' => $event->user->getEmail(),
            'contentPreview' => $event->contentPreview,
            'highestScore' => $event->highestScore,
            'timestamp' => (new \DateTimeImmutable())->format('c'),
            'url' => '/admin/moderation/logs',
        ];

        $this->hub->publish(new Update(
            'admin/violations',
            json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        ));
    }
}

