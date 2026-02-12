<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\RequestStack;

class DemoUserContext
{
    private const DEMO_EMAIL_SESSION_KEY = 'demo_user_email';

    public function __construct(private RequestStack $requestStack)
    {
    }

    /**
     * Set the current demo user email
     */
    public function setCurrentEmail(?string $email): void
    {
        $session = $this->requestStack->getSession();
        if ($email === null) {
            $session->remove(self::DEMO_EMAIL_SESSION_KEY);
        } else {
            $session->set(self::DEMO_EMAIL_SESSION_KEY, $email);
        }
    }

    /**
     * Get the current demo user email, or null if no demo user is set
     */
    public function getCurrentEmail(): ?string
    {
        $session = $this->requestStack->getSession();
        return $session->get(self::DEMO_EMAIL_SESSION_KEY);
    }

    /**
     * Check if a demo user is currently active
     */
    public function isDemoMode(): bool
    {
        return $this->getCurrentEmail() !== null;
    }
}
