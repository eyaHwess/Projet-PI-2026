<?php

namespace App\Repository;

use App\Entity\Goal;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class GoalRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Goal::class);
    }

    /**
     * Total goals for user
     */
    public function countByUser(User $user): int
    {
        return (int) $this->createQueryBuilder('g')
            ->select('COUNT(g.id)')
            ->where('g.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Count goals by status
     */
    public function countByUserAndStatus(User $user, string $status): int
    {
        return (int) $this->createQueryBuilder('g')
            ->select('COUNT(g.id)')
            ->where('g.user = :user')
            ->andWhere('g.status = :status')
            ->setParameter('user', $user)
            ->setParameter('status', $status)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Count overdue goals
     */
    public function countOverdueByUser(User $user): int
    {
        return (int) $this->createQueryBuilder('g')
            ->select('COUNT(g.id)')
            ->where('g.user = :user')
            ->andWhere('g.status != :completed')
            ->andWhere('g.deadline IS NOT NULL')
            ->andWhere('g.deadline < :today')
            ->setParameter('user', $user)
            ->setParameter('completed', 'completed')
            ->setParameter('today', new \DateTime())
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Completion rate calculation helper
     */
    public function getCompletionRate(User $user): int
    {
        $total = $this->countByUser($user);

        if ($total === 0) {
            return 0;
        }

        $completed = $this->countByUserAndStatus($user, 'completed');

        return (int) round(($completed / $total) * 100);
    }

    /**
     * First goal (latest created)
     */
    public function findFirstByUser(User $user): ?Goal
    {
        return $this->createQueryBuilder('g')
            ->where('g.user = :user')
            ->setParameter('user', $user)
            ->orderBy('g.createdAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Find all goals for user (entity loading)
     * Use only when needed (NOT for counting)
     */
    public function findByUser(User $user): array
    {
        return $this->createQueryBuilder('g')
            ->where('g.user = :user')
            ->setParameter('user', $user)
            ->orderBy('g.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}