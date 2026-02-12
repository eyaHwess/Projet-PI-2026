<?php

namespace App\Repository;

use App\Entity\Session;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Session>
 */
class SessionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Session::class);
    }

    /**
     * @return Session[]
     */
    public function findActiveForUser(User $user): array
    {
        return $this->createQueryBuilder('s')
            ->join('s.coachingRequest', 'cr')
            ->where('cr.user = :user')
            ->andWhere('s.status IN (:statuses)')
            ->setParameter('user', $user)
            ->setParameter('statuses', [
                Session::STATUS_SCHEDULING,
                Session::STATUS_PROPOSED_BY_USER,
                Session::STATUS_PROPOSED_BY_COACH,
                Session::STATUS_CONFIRMED,
            ])
            ->orderBy('s.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Session[]
     */
    public function findActiveForCoach(User $coach): array
    {
        return $this->createQueryBuilder('s')
            ->join('s.coachingRequest', 'cr')
            ->where('cr.coach = :coach')
            ->andWhere('s.status IN (:statuses)')
            ->setParameter('coach', $coach)
            ->setParameter('statuses', [
                Session::STATUS_SCHEDULING,
                Session::STATUS_PROPOSED_BY_USER,
                Session::STATUS_PROPOSED_BY_COACH,
                Session::STATUS_CONFIRMED,
            ])
            ->orderBy('s.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * All sessions where user is either client or coach.
     *
     * @return Session[]
     */
    public function findAllForUser(User $user): array
    {
        return $this->createQueryBuilder('s')
            ->join('s.coachingRequest', 'cr')
            ->where('cr.user = :user')
            ->orWhere('cr.coach = :user')
            ->setParameter('user', $user)
            ->orderBy('s.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
