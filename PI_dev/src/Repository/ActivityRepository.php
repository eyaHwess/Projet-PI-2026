<?php

namespace App\Repository;

use App\Entity\Activity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Activity>
 */
class ActivityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Activity::class);
    }

    /**
     * @return Activity[] Returns an array of Activity objects (limité pour éviter ORDER_BY_WITHOUT_LIMIT).
     */
    public function findByRoutine($routineId, int $maxResults = 500): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.routine = :routineId')
            ->setParameter('routineId', $routineId)
            ->orderBy('a.startTime', 'ASC')
            ->setMaxResults($maxResults)
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Activity[] Activities whose reminder is due (reminderAt <= $now).
     */
    public function findUpcomingReminders(\DateTimeInterface $date, int $maxResults = 200): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.hasReminder = :hasReminder')
            ->andWhere('a.reminderAt <= :date')
            ->andWhere('a.status != :completed')
            ->setParameter('hasReminder', true)
            ->setParameter('date', $date)
            ->setParameter('completed', 'completed')
            ->setMaxResults($maxResults)
            ->getQuery()
            ->getResult();
    }

    /**
     * Alias used by ActivityReminderService.
     *
     * @return Activity[]
     */
    public function findPendingReminders(\DateTimeInterface $now): array
    {
        return $this->findUpcomingReminders($now);
    }
}
