<?php

namespace App\EventListener;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

#[AsEventListener(event: KernelEvents::REQUEST, priority: 0)]
class UserActivityListener
{
    public function __construct(
        private Security $security,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function __invoke(RequestEvent $event): void
    {
        // Ne traiter que la requête principale
        if (!$event->isMainRequest()) {
            return;
        }

        $user = $this->security->getUser();

        // Si l'utilisateur est connecté
        if ($user instanceof User) {
            // Mettre à jour la dernière activité
            $user->updateLastActivity();
            $this->entityManager->flush();
        }
    }
}
