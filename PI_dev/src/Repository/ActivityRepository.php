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
     * @return Activity[] Returns an array of Activity objects
     */
    public function findByRoutine($routineId): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.routine = :routineId')
            ->setParameter('routineId', $routineId)
            ->orderBy('a.startTime', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Activity[] Returns activities with reminders
     */
    public function findUpcomingReminders(\DateTimeInterface $date): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.hasReminder = :hasReminder')
            ->andWhere('a.reminderAt <= :date')
            ->andWhere('a.status != :completed')
            ->setParameter('hasReminder', true)
            ->setParameter('date', $date)
            ->setParameter('completed', 'completed')
            ->getQuery()
            ->getResult();
    }
}
