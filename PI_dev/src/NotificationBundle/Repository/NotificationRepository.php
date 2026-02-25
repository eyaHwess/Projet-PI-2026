<?php

namespace App\NotificationBundle\Repository;

use App\Entity\User;
use App\NotificationBundle\Entity\Notification;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Notification>
 */
class NotificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Notification::class);
    }

    /**
     * Récupère toutes les notifications d'un utilisateur
     */
    public function findByUser(User $user): array
    {
        return $this->createQueryBuilder('n')
            ->where('n.user = :user')
            ->setParameter('user', $user)
            ->orderBy('n.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère les notifications non lues d'un utilisateur
     */
    public function findUnreadByUser(User $user): array
    {
        return $this->createQueryBuilder('n')
            ->where('n.user = :user')
            ->andWhere('n.isRead = false')
            ->setParameter('user', $user)
            ->orderBy('n.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Compte les notifications non lues d'un utilisateur
     */
    public function countUnreadByUser(User $user): int
    {
        return (int) $this->createQueryBuilder('n')
            ->select('COUNT(n.id)')
            ->where('n.user = :user')
            ->andWhere('n.isRead = false')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Marque toutes les notifications d'un utilisateur comme lues
     */
    public function markAllAsReadForUser(User $user): void
    {
        $this->createQueryBuilder('n')
            ->update()
            ->set('n.isRead', 'true')
            ->where('n.user = :user')
            ->andWhere('n.isRead = false')
            ->setParameter('user', $user)
            ->getQuery()
            ->execute();
    }

    /**
     * Récupère les notifications récentes (dernières 24h)
     */
    public function findRecentByUser(User $user, int $hours = 24): array
    {
        $since = new \DateTimeImmutable(sprintf('-%d hours', $hours));
        
        return $this->createQueryBuilder('n')
            ->where('n.user = :user')
            ->andWhere('n.createdAt >= :since')
            ->setParameter('user', $user)
            ->setParameter('since', $since)
            ->orderBy('n.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Supprime les anciennes notifications (plus de X jours)
     */
    public function deleteOldNotifications(int $days = 30): int
    {
        $before = new \DateTimeImmutable(sprintf('-%d days', $days));
        
        return $this->createQueryBuilder('n')
            ->delete()
            ->where('n.createdAt < :before')
            ->andWhere('n.isRead = true')
            ->setParameter('before', $before)
            ->getQuery()
            ->execute();
    }
}
