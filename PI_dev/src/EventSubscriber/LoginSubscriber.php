<?php

namespace App\EventSubscriber;

use App\Entity\User;
use App\Service\LoginHistoryService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

class LoginSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private LoginHistoryService $loginHistoryService
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            LoginSuccessEvent::class => 'onLoginSuccess',
        ];
    }

    public function onLoginSuccess(LoginSuccessEvent $event): void
    {
        $user = $event->getUser();

        // Vérifier que c'est bien notre entité User
        if (!$user instanceof User) {
            return;
        }

        // Enregistrer la connexion
        $this->loginHistoryService->recordLogin($user);
    }
}
