<?php

namespace App\GoalHistoryBundle\Repository;

use App\Entity\Goal;
use App\Entity\User;
use App\GoalHistoryBundle\Entity\GoalHistory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<GoalHistory>
 */
class GoalHistoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GoalHistory::class);
    }

    /**
     * Full history for one goal, newest first.
     *
     * @return GoalHistory[]
     */
    public function findByGoal(Goal $goal, int $limit = 50): array
    {
        return $this->createQueryBuilder('gh')
            ->andWhere('gh.goal = :goal')
            ->setParameter('goal', $goal)
            ->orderBy('gh.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * All actions performed by a specific user, newest first.
     *
     * @return GoalHistory[]
     */
    public function findByUser(User $user, int $limit = 100): array
    {
        return $this->createQueryBuilder('gh')
            ->andWhere('gh.user = :user')
            ->setParameter('user', $user)
            ->orderBy('gh.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Filter by action type across all goals.
     *
     * @return GoalHistory[]
     */
    public function findByAction(string $action, int $limit = 100): array
    {
        return $this->createQueryBuilder('gh')
            ->andWhere('gh.action = :action')
            ->setParameter('action', $action)
            ->orderBy('gh.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Recent history entries for a goal filtered by action.
     *
     * @return GoalHistory[]
     */
    public function findByGoalAndAction(Goal $goal, string $action): array
    {
        return $this->createQueryBuilder('gh')
            ->andWhere('gh.goal = :goal')
            ->andWhere('gh.action = :action')
            ->setParameter('goal', $goal)
            ->setParameter('action', $action)
            ->orderBy('gh.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Count total history entries for a goal.
     */
    public function countByGoal(Goal $goal): int
    {
        return (int) $this->createQueryBuilder('gh')
            ->select('COUNT(gh.id)')
            ->andWhere('gh.goal = :goal')
            ->setParameter('goal', $goal)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Delete all history entries older than $days days.
     */
    public function purgeOlderThan(int $days): int
    {
        $cutoff = new \DateTimeImmutable("-{$days} days");

        return $this->createQueryBuilder('gh')
            ->delete()
            ->andWhere('gh.createdAt < :cutoff')
            ->setParameter('cutoff', $cutoff)
            ->getQuery()
            ->execute();
    }
}
