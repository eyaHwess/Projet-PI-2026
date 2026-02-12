<?php

namespace App\Repository;

use App\Entity\Routine;
use App\Entity\User;
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
     * Nombre de routines dont l'objectif (goal) appartient à l'utilisateur.
     */
    public function countByUser(User $user): int
    {
        return (int) $this->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->join('r.goal', 'g')
            ->where('g.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult();
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
