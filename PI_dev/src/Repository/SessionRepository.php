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

    /**
     * Sessions du coach (pour CRUD coach).
     *
     * @return Session[]
     */
    public function findForCoach(User $coach): array
    {
        return $this->createQueryBuilder('s')
            ->join('s.coachingRequest', 'cr')
            ->where('cr.coach = :coach')
            ->setParameter('coach', $coach)
            ->orderBy('s.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Nombre de sessions pour un coach (admin).
     */
    public function countByCoach(User $coach): int
    {
        return (int) $this->createQueryBuilder('s')
            ->select('COUNT(s.id)')
            ->join('s.coachingRequest', 'cr')
            ->where('cr.coach = :coach')
            ->setParameter('coach', $coach)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Nombre de sessions du coach prévues aujourd'hui (scheduledAt, proposedTimeByCoach ou proposedTimeByUser à la date du jour).
     */
    public function countSessionsTodayForCoach(User $coach): int
    {
        $todayStart = (new \DateTimeImmutable('today'))->setTime(0, 0, 0);
        $todayEnd = (new \DateTimeImmutable('today'))->setTime(23, 59, 59);

        return (int) $this->createQueryBuilder('s')
            ->select('COUNT(s.id)')
            ->join('s.coachingRequest', 'cr')
            ->where('cr.coach = :coach')
            ->andWhere(
                '(s.scheduledAt IS NOT NULL AND s.scheduledAt >= :todayStart AND s.scheduledAt <= :todayEnd)'
                . ' OR (s.scheduledAt IS NULL AND s.proposedTimeByCoach IS NOT NULL AND s.proposedTimeByCoach >= :todayStart AND s.proposedTimeByCoach <= :todayEnd)'
                . ' OR (s.scheduledAt IS NULL AND s.proposedTimeByCoach IS NULL AND s.proposedTimeByUser IS NOT NULL AND s.proposedTimeByUser >= :todayStart AND s.proposedTimeByUser <= :todayEnd)'
            )
            ->setParameter('coach', $coach)
            ->setParameter('todayStart', $todayStart)
            ->setParameter('todayEnd', $todayEnd)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
