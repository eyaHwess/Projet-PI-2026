<?php

namespace App\NotificationBundle\Service;

use App\Entity\CoachingRequest;
use App\Entity\User;
use App\NotificationBundle\Entity\Notification;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * NotificationManager - Service principal de gestion des notifications
 * 
 * Ce service centralise toute la logique de création et d'envoi de notifications.
 * Il fournit des méthodes simples pour notifier les utilisateurs et les coaches.
 */
class NotificationManager
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ?LoggerInterface $logger = null
    ) {
    }

    /**
     * Notifie un utilisateur avec un message personnalisé
     * 
     * @param User $user L'utilisateur à notifier
     * @param string $type Type de notification (ex: 'info', 'success', 'warning', 'error')
     * @param string $message Message de la notification
     * @param CoachingRequest|null $coachingRequest Demande de coaching associée (optionnel)
     * @return Notification La notification créée
     */
    public function notifyUser(
        User $user,
        string $type,
        string $message,
        ?CoachingRequest $coachingRequest = null
    ): Notification {
        $notification = $this->createNotification($user, $type, $message, $coachingRequest);
        
        $this->logger?->info('Notification sent to user', [
            'user_id' => $user->getId(),
            'type' => $type,
            'message' => $message
        ]);
        
        return $notification;
    }

    /**
     * Notifie un coach avec un message personnalisé
     * 
     * @param User $coach Le coach à notifier
     * @param string $type Type de notification
     * @param string $message Message de la notification
     * @param CoachingRequest|null $coachingRequest Demande de coaching associée (optionnel)
     * @return Notification La notification créée
     */
    public function notifyCoach(
        User $coach,
        string $type,
        string $message,
        ?CoachingRequest $coachingRequest = null
    ): Notification {
        $notification = $this->createNotification($coach, $type, $message, $coachingRequest);
        
        $this->logger?->info('Notification sent to coach', [
            'coach_id' => $coach->getId(),
            'type' => $type,
            'message' => $message
        ]);
        
        return $notification;
    }

    /**
     * Notifie l'utilisateur que sa demande a été acceptée
     */
    public function notifyRequestAccepted(CoachingRequest $request): Notification
    {
        $coach = $request->getCoach();
        $message = sprintf(
            'Bonne nouvelle ! %s %s a accepté votre demande de coaching.',
            $coach->getFirstName(),
            $coach->getLastName()
        );
        
        return $this->notifyUser($request->getUser(), 'request_accepted', $message, $request);
    }

    /**
     * Notifie l'utilisateur que sa demande a été refusée
     */
    public function notifyRequestDeclined(CoachingRequest $request): Notification
    {
        $coach = $request->getCoach();
        $message = sprintf(
            '%s %s a décliné votre demande de coaching. N\'hésitez pas à contacter un autre coach.',
            $coach->getFirstName(),
            $coach->getLastName()
        );
        
        return $this->notifyUser($request->getUser(), 'request_declined', $message, $request);
    }

    /**
     * Notifie l'utilisateur que sa demande est en attente
     */
    public function notifyRequestPending(CoachingRequest $request): Notification
    {
        $coach = $request->getCoach();
        $message = sprintf(
            '%s %s a mis votre demande en attente. Le coach reviendra vers vous prochainement.',
            $coach->getFirstName(),
            $coach->getLastName()
        );
        
        return $this->notifyUser($request->getUser(), 'request_pending', $message, $request);
    }

    /**
     * Notifie le coach qu'il a reçu une nouvelle demande
     */
    public function notifyCoachNewRequest(CoachingRequest $request): Notification
    {
        $user = $request->getUser();
        $isUrgent = $request->isUrgent();
        
        $message = sprintf(
            '%s%s %s vous a envoyé une %sdemande de coaching.',
            $isUrgent ? '🔴 URGENT : ' : '',
            $user->getFirstName(),
            $user->getLastName(),
            $isUrgent ? 'demande URGENTE' : 'nouvelle '
        );
        
        $type = $isUrgent ? 'new_request_urgent' : 'new_request';
        
        return $this->notifyCoach($request->getCoach(), $type, $message, $request);
    }

    /**
     * Notifie l'utilisateur que sa demande a été envoyée
     */
    public function notifyUserRequestSent(CoachingRequest $request): Notification
    {
        $coach = $request->getCoach();
        $message = sprintf(
            'Votre demande de coaching a été envoyée à %s %s. Vous recevrez une réponse prochainement.',
            $coach->getFirstName(),
            $coach->getLastName()
        );
        
        return $this->notifyUser($request->getUser(), 'request_sent', $message, $request);
    }

    /**
     * Notifie l'utilisateur d'une session à venir
     */
    public function notifyUpcomingSession(User $user, CoachingRequest $request, \DateTimeInterface $sessionDate): Notification
    {
        $message = sprintf(
            'Rappel : Vous avez une session de coaching prévue le %s.',
            $sessionDate->format('d/m/Y à H:i')
        );
        
        return $this->notifyUser($user, 'session_reminder', $message, $request);
    }

    /**
     * Notifie l'utilisateur d'une session annulée
     */
    public function notifySessionCancelled(User $user, CoachingRequest $request): Notification
    {
        $message = 'Votre session de coaching a été annulée. Veuillez contacter votre coach pour plus d\'informations.';
        
        return $this->notifyUser($user, 'session_cancelled', $message, $request);
    }

    /**
     * Crée et persiste une notification
     * 
     * @internal Méthode privée utilisée par les méthodes publiques
     */
    private function createNotification(
        User $user,
        string $type,
        string $message,
        ?CoachingRequest $coachingRequest = null
    ): Notification {
        $notification = new Notification();
        $notification->setUser($user);
        $notification->setType($type);
        $notification->setMessage($message);
        $notification->setCoachingRequest($coachingRequest);

        $this->entityManager->persist($notification);
        $this->entityManager->flush();

        return $notification;
    }

    /**
     * Marque une notification comme lue
     */
    public function markAsRead(Notification $notification): void
    {
        $notification->setIsRead(true);
        $this->entityManager->flush();
    }

    /**
     * Marque toutes les notifications d'un utilisateur comme lues
     */
    public function markAllAsReadForUser(User $user): void
    {
        $repository = $this->entityManager->getRepository(Notification::class);
        $repository->markAllAsReadForUser($user);
    }

    /**
     * Supprime une notification
     */
    public function deleteNotification(Notification $notification): void
    {
        $this->entityManager->remove($notification);
        $this->entityManager->flush();
    }

    /**
     * Supprime toutes les notifications d'un utilisateur
     */
    public function deleteAllForUser(User $user): void
    {
        $repository = $this->entityManager->getRepository(Notification::class);
        $notifications = $repository->findByUser($user);
        
        foreach ($notifications as $notification) {
            $this->entityManager->remove($notification);
        }
        
        $this->entityManager->flush();
    }
}
