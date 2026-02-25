<?php

namespace App\Repository;

use App\Entity\RealtimeNotification;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<RealtimeNotification>
 */
class RealtimeNotificationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RealtimeNotification::class);
    }

    /**
     * @return RealtimeNotification[]
     */
    public function findRecentByRecipient(User $recipient, int $limit = 10): array
    {
        $limit = max(1, min(50, $limit));

        return $this->createQueryBuilder('n')
            ->where('n.recipient = :recipient')
            ->setParameter('recipient', $recipient)
            ->orderBy('n.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return RealtimeNotification[]
     */
    public function findUnreadByRecipient(User $recipient, int $limit = 10): array
    {
        $limit = max(1, min(50, $limit));

        return $this->createQueryBuilder('n')
            ->where('n.recipient = :recipient')
            ->andWhere('n.isRead = false')
            ->setParameter('recipient', $recipient)
            ->orderBy('n.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function countUnreadByRecipient(User $recipient): int
    {
        return (int) $this->createQueryBuilder('n')
            ->select('COUNT(n.id)')
            ->where('n.recipient = :recipient')
            ->andWhere('n.isRead = false')
            ->setParameter('recipient', $recipient)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function markAllAsRead(User $recipient): void
    {
        $this->createQueryBuilder('n')
            ->update()
            ->set('n.isRead', 'true')
            ->where('n.recipient = :recipient')
            ->andWhere('n.isRead = false')
            ->setParameter('recipient', $recipient)
            ->getQuery()
            ->execute();
    }
}

