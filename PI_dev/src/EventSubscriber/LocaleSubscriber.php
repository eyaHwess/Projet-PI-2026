<?php

namespace App\EventSubscriber;

use App\Entity\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class LocaleSubscriber implements EventSubscriberInterface
{
    private const SUPPORTED_LOCALES = ['en', 'fr', 'ar'];

    public function __construct(
        private TokenStorageInterface $tokenStorage,
        private TranslatorInterface $translator,
        private string $defaultLocale = 'en'
    ) {
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        if (!$event->isMainRequest()) {
            return;
        }

        $session = $request->hasSession() ? $request->getSession() : null;

        $locale = null;

        $token = $this->tokenStorage->getToken();
        $user = $token?->getUser();
        if ($user instanceof User && $user->getPreferredLanguage()) {
            $locale = $user->getPreferredLanguage();
        }

        if (!$locale && $session && $session->has('_locale')) {
            $locale = $session->get('_locale');
        }

        if (!$locale) {
            $browserPreferred = $request->getPreferredLanguage(self::SUPPORTED_LOCALES);
            $locale = $browserPreferred ?: $this->defaultLocale;
        }

        if (!in_array($locale, self::SUPPORTED_LOCALES, true)) {
            $locale = $this->defaultLocale;
        }

        if ($session) {
            $session->set('_locale', $locale);
        }

        $request->setLocale($locale);
        $this->translator->setLocale($locale);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => [['onKernelRequest', 20]],
        ];
    }
}
