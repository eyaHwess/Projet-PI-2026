<?php

namespace App\EventSubscriber;

use App\Entity\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;

class UserLocaleOnLoginSubscriber implements EventSubscriberInterface
{
    private const SUPPORTED_LOCALES = ['en', 'fr', 'ar'];

    public function onInteractiveLogin(InteractiveLoginEvent $event): void
    {
        $user = $event->getAuthenticationToken()->getUser();
        if (!$user instanceof User) {
            return;
        }

        $preferred = $user->getPreferredLanguage();
        if (!$preferred || !in_array($preferred, self::SUPPORTED_LOCALES, true)) {
            return;
        }

        $request = $event->getRequest();
        if ($request->hasSession()) {
            $request->getSession()->set('_locale', $preferred);
        }

        $request->setLocale($preferred);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SecurityEvents::INTERACTIVE_LOGIN => 'onInteractiveLogin',
        ];
    }
}
