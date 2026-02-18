<?php

namespace App\Repository;

use App\Entity\TimeSlot;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TimeSlot>
 */
class TimeSlotRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TimeSlot::class);
    }

    /**
     * Récupère les créneaux disponibles d'un coach pour une période donnée
     */
    public function findAvailableForCoach(User $coach, \DateTimeImmutable $start, \DateTimeImmutable $end): array
    {
        return $this->createQueryBuilder('ts')
            ->andWhere('ts.coach = :coach')
            ->andWhere('ts.isAvailable = :available')
            ->andWhere('ts.startTime >= :start')
            ->andWhere('ts.startTime < :end')
            ->setParameter('coach', $coach)
            ->setParameter('available', true)
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->orderBy('ts.startTime', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère tous les créneaux d'un coach pour une période donnée
     */
    public function findByCoachAndPeriod(User $coach, \DateTimeImmutable $start, \DateTimeImmutable $end): array
    {
        return $this->createQueryBuilder('ts')
            ->andWhere('ts.coach = :coach')
            ->andWhere('ts.startTime >= :start')
            ->andWhere('ts.startTime < :end')
            ->setParameter('coach', $coach)
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->orderBy('ts.startTime', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Vérifie si un coach a des créneaux disponibles aujourd'hui
     */
    public function hasAvailableToday(User $coach): bool
    {
        $today = new \DateTimeImmutable('today');
        $tomorrow = new \DateTimeImmutable('tomorrow');

        $count = $this->createQueryBuilder('ts')
            ->select('COUNT(ts.id)')
            ->andWhere('ts.coach = :coach')
            ->andWhere('ts.isAvailable = :available')
            ->andWhere('ts.startTime >= :start')
            ->andWhere('ts.startTime < :end')
            ->setParameter('coach', $coach)
            ->setParameter('available', true)
            ->setParameter('start', $today)
            ->setParameter('end', $tomorrow)
            ->getQuery()
            ->getSingleScalarResult();

        return $count > 0;
    }

    /**
     * Compte le nombre de créneaux disponibles pour un coach
     */
    public function countAvailableForCoach(User $coach): int
    {
        $now = new \DateTimeImmutable();

        return (int) $this->createQueryBuilder('ts')
            ->select('COUNT(ts.id)')
            ->andWhere('ts.coach = :coach')
            ->andWhere('ts.isAvailable = :available')
            ->andWhere('ts.startTime >= :now')
            ->setParameter('coach', $coach)
            ->setParameter('available', true)
            ->setParameter('now', $now)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Récupère les créneaux réservés par un utilisateur
     */
    public function findBookedByUser(User $user): array
    {
        return $this->createQueryBuilder('ts')
            ->andWhere('ts.bookedBy = :user')
            ->andWhere('ts.isAvailable = :available')
            ->setParameter('user', $user)
            ->setParameter('available', false)
            ->orderBy('ts.startTime', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
