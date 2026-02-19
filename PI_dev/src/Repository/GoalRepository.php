<?php

namespace App\Repository;

use App\Entity\Goal;
use App\Entity\User;
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
     * Récupère les goals avec leurs participants
     */
    public function findGoalsWithParticipants(): array
    {
        return $this->createQueryBuilder('g')
            ->leftJoin('g.goalParticipations', 'gp')
            ->leftJoin('gp.user', 'u')
            ->addSelect('gp', 'u')
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère les goals auxquels un utilisateur participe
     */
    public function findByUser(User $user): array
    {
        return $this->createQueryBuilder('g')
            ->innerJoin('g.goalParticipations', 'gp')
            ->where('gp.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère les goals actifs
     */
    public function findActiveGoals(): array
    {
        return $this->createQueryBuilder('g')
            ->where('g.status = :status')
            ->setParameter('status', 'active')
            ->orderBy('g.startDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

//    /**
//     * @return Goal[] Returns an array of Goal objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('g')
//            ->andWhere('g.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('g.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Goal
//    {
//        return $this->createQueryBuilder('g')
//            ->andWhere('g.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
