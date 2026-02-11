<?php

namespace App\Repository;

use App\Entity\Routine;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Routine>
 */
class RoutineRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Routine::class);
    }

    /**
     * @return Routine[] Returns an array of Routine objects
     */
    public function findByGoal($goalId): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.goal = :goalId')
            ->setParameter('goalId', $goalId)
            ->orderBy('r.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
