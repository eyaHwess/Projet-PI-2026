<?php

namespace App\Repository;

use App\Entity\Goal;
use App\Entity\Routine;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
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
     * Query builder pour la liste des goals utilisateur (sans fetch join pour éviter SETMAXRESULTS_WITH_COLLECTION_JOIN).
     * Appeler hydrateRoutinesAndActivitiesForGoals() sur la page courante après pagination.
     */
    public function getQueryBuilderForUserIndex(User $user, string $searchQuery = '', string $filterStatus = 'all'): \Doctrine\ORM\QueryBuilder
    {
        $qb = $this->createQueryBuilder('g')
            ->andWhere('g.user = :user')
            ->setParameter('user', $user);

        if ($searchQuery !== '') {
            $qb->andWhere('g.title LIKE :search OR g.description LIKE :search')
                ->setParameter('search', '%' . $searchQuery . '%');
        }

        if ($filterStatus !== 'all') {
            $qb->andWhere('g.status = :status')
                ->setParameter('status', $filterStatus);
        }

        return $qb;
    }

    /**
     * Hydrate routines puis activités en 2 requêtes (évite CARTESIAN_PRODUCT et SETMAXRESULTS_WITH_COLLECTION_JOIN).
     */
    public function hydrateRoutinesAndActivitiesForGoals(iterable $goals): void
    {
        $ids = [];
        foreach ($goals as $goal) {
            if ($goal instanceof Goal) {
                $ids[] = $goal->getId();
            }
        }
        if ($ids === []) {
            return;
        }

        // Étape 1 : charger les routines des goals (1 seul join collection)
        $this->createQueryBuilder('g')
            ->select('g', 'r')
            ->leftJoin('g.routines', 'r')
            ->where('g.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->getResult();

        // Étape 2 : collecter les ids des routines et charger leurs activités
        $routineIds = [];
        foreach ($goals as $goal) {
            if ($goal instanceof Goal) {
                foreach ($goal->getRoutines() as $routine) {
                    $routineIds[] = $routine->getId();
                }
            }
        }
        $routineIds = array_unique($routineIds);
        if ($routineIds === []) {
            return;
        }

        $this->getEntityManager()
            ->getRepository(Routine::class)
            ->createQueryBuilder('r')
            ->select('r', 'a')
            ->leftJoin('r.activities', 'a')
            ->where('r.id IN (:routineIds)')
            ->setParameter('routineIds', $routineIds)
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère les goals avec participants, créateur et chatroom (Paginator Doctrine pour éviter SETMAXRESULTS_WITH_COLLECTION_JOIN + N+1).
     */
    public function findGoalsWithParticipants(int $maxResults = 100): array
    {
        $query = $this->createQueryBuilder('g')
            ->leftJoin('g.user', 'owner')
            ->addSelect('owner')
            ->leftJoin('g.chatroom', 'c')
            ->addSelect('c')
            ->leftJoin('g.goalParticipations', 'gp')
            ->leftJoin('gp.user', 'u')
            ->addSelect('gp', 'u')
            ->orderBy('g.createdAt', 'DESC')
            ->getQuery();

        $query->setMaxResults($maxResults);
        $query->setFirstResult(0);
        $paginator = new Paginator($query, true);

        return iterator_to_array($paginator);
    }


    /**
     * Récupère les goals actifs (limité pour éviter ORDER_BY_WITHOUT_LIMIT).
     */
    public function findActiveGoals(int $maxResults = 200): array
    {
        return $this->createQueryBuilder('g')
            ->where('g.status = :status')
            ->setParameter('status', 'active')
            ->orderBy('g.startDate', 'DESC')
            ->setMaxResults($maxResults)
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
     * Compte les goals par statut pour un utilisateur (évite de charger toutes les entités en mémoire).
     */
    public function countByUserAndStatus(User $user): array
    {
        $qb = $this->createQueryBuilder('g')
            ->select('g.status', 'COUNT(g.id) as cnt')
            ->where('g.user = :user')
            ->setParameter('user', $user)
            ->groupBy('g.status');
        $rows = $qb->getQuery()->getScalarResult();
        $counts = ['active' => 0, 'completed' => 0, 'paused' => 0, 'failed' => 0];
        foreach ($rows as $row) {
            $counts[$row['status']] = (int) $row['cnt'];
        }
        return $counts;
    }

    /**
     * Premier objectif de l'utilisateur (pour affichage).
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
     * @return Goal[] Returns an array of Goal objects (limité pour éviter ORDER_BY_WITHOUT_LIMIT).
     */
    public function findByUser(User $user, int $maxResults = 500): array
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.user = :user')
            ->setParameter('user', $user)
            ->orderBy('g.createdAt', 'DESC')
            ->setMaxResults($maxResults)
            ->getQuery()
            ->getResult();
    }
}
