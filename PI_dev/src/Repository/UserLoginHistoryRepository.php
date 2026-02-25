<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\UserLoginHistory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserLoginHistory>
 */
class UserLoginHistoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserLoginHistory::class);
    }

    /**
     * Récupère les N dernières connexions d'un utilisateur
     */
    public function findRecentByUser(User $user, int $limit = 5): array
    {
        return $this->createQueryBuilder('h')
            ->where('h.user = :user')
            ->setParameter('user', $user)
            ->orderBy('h.loggedAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Vérifie si une IP a déjà été utilisée par cet utilisateur
     */
    public function hasIpBeenUsed(User $user, string $ipAddress): bool
    {
        $count = $this->createQueryBuilder('h')
            ->select('COUNT(h.id)')
            ->where('h.user = :user')
            ->andWhere('h.ipAddress = :ip')
            ->setParameter('user', $user)
            ->setParameter('ip', $ipAddress)
            ->getQuery()
            ->getSingleScalarResult();

        return $count > 0;
    }

    /**
     * Récupère toutes les connexions suspectes d'un utilisateur
     */
    public function findSuspiciousByUser(User $user): array
    {
        return $this->createQueryBuilder('h')
            ->where('h.user = :user')
            ->andWhere('h.isSuspicious = true')
            ->setParameter('user', $user)
            ->orderBy('h.loggedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Compte le nombre de connexions suspectes non lues
     */
    public function countUnreadSuspicious(User $user): int
    {
        return $this->createQueryBuilder('h')
            ->select('COUNT(h.id)')
            ->where('h.user = :user')
            ->andWhere('h.isSuspicious = true')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Nettoie les anciennes entrées (plus de 90 jours)
     */
    public function cleanOldEntries(): int
    {
        $date = new \DateTimeImmutable('-90 days');
        
        return $this->createQueryBuilder('h')
            ->delete()
            ->where('h.loggedAt < :date')
            ->setParameter('date', $date)
            ->getQuery()
            ->execute();
    }
}
