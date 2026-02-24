<?php

namespace App\NotificationBundle\Service;

use App\Entity\CoachingRequest;
use App\Entity\User;
use App\NotificationBundle\Entity\Notification;
use Doctrine\ORM\EntityManagerInterface;

/**
 * NotificationService - Service legacy pour compatibilité
 * 
 * @deprecated Utilisez NotificationManager à la place
 */
class NotificationService
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    /**
     * Crée une notification pour l'acceptation d'une demande de coaching
     */
    public function notifyRequestAccepted(CoachingRequest $request): void
    {
        $coach = $request->getCoach();
        $notification = new Notification();
        $notification->setUser($request->getUser());
        $notification->setType('request_accepted');
        $notification->setMessage(
            sprintf(
                'Bonne nouvelle ! %s %s a accepté votre demande de coaching.',
                $coach->getFirstName(),
                $coach->getLastName()
            )
        );
        $notification->setCoachingRequest($request);

        $this->entityManager->persist($notification);
        $this->entityManager->flush();
    }

    /**
     * Crée une notification pour le refus d'une demande de coaching
     */
    public function notifyRequestDeclined(CoachingRequest $request): void
    {
        $coach = $request->getCoach();
        $notification = new Notification();
        $notification->setUser($request->getUser());
        $notification->setType('request_declined');
        $notification->setMessage(
            sprintf(
                '%s %s a décliné votre demande de coaching. N\'hésitez pas à contacter un autre coach.',
                $coach->getFirstName(),
                $coach->getLastName()
            )
        );
        $notification->setCoachingRequest($request);

        $this->entityManager->persist($notification);
        $this->entityManager->flush();
    }

    /**
     * Crée une notification pour la mise en attente d'une demande de coaching
     */
    public function notifyRequestPending(CoachingRequest $request): void
    {
        $coach = $request->getCoach();
        $notification = new Notification();
        $notification->setUser($request->getUser());
        $notification->setType('request_pending');
        $notification->setMessage(
            sprintf(
                '%s %s a mis votre demande en attente. Le coach reviendra vers vous prochainement.',
                $coach->getFirstName(),
                $coach->getLastName()
            )
        );
        $notification->setCoachingRequest($request);

        $this->entityManager->persist($notification);
        $this->entityManager->flush();
    }

    /**
     * Crée une notification personnalisée
     */
    public function createNotification(User $user, string $type, string $message, ?CoachingRequest $request = null): void
    {
        $notification = new Notification();
        $notification->setUser($user);
        $notification->setType($type);
        $notification->setMessage($message);
        $notification->setCoachingRequest($request);

        $this->entityManager->persist($notification);
        $this->entityManager->flush();
    }

    /**
     * Notifie le coach qu'il a reçu une nouvelle demande
     */
    public function notifyCoachNewRequest(CoachingRequest $request): void
    {
        $user = $request->getUser();
        $isUrgent = $request->isUrgent();
        
        $notification = new Notification();
        $notification->setUser($request->getCoach());
        $notification->setType($isUrgent ? 'new_request_urgent' : 'new_request');
        $notification->setMessage(
            sprintf(
                '%s%s %s vous a envoyé une %sdemande de coaching.',
                $isUrgent ? '🔴 URGENT : ' : '',
                $user->getFirstName(),
                $user->getLastName(),
                $isUrgent ? 'demande URGENTE' : 'nouvelle '
            )
        );
        $notification->setCoachingRequest($request);

        $this->entityManager->persist($notification);
        $this->entityManager->flush();
    }

    /**
     * Notifie l'utilisateur que sa demande a été envoyée
     */
    public function notifyUserRequestSent(CoachingRequest $request): void
    {
        $coach = $request->getCoach();
        
        $notification = new Notification();
        $notification->setUser($request->getUser());
        $notification->setType('request_sent');
        $notification->setMessage(
            sprintf(
                'Votre demande de coaching a été envoyée à %s %s. Vous recevrez une réponse prochainement.',
                $coach->getFirstName(),
                $coach->getLastName()
            )
        );
        $notification->setCoachingRequest($request);

        $this->entityManager->persist($notification);
        $this->entityManager->flush();
    }
}
