<?php

namespace App\Repository;

use App\Entity\Goal;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Goal>
 */
class GoalRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Goal::class);
    }

    /**
     * @return Goal[] Returns an array of Goal objects
     */
    public function findByUser($userId): array
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.user = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('g.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
