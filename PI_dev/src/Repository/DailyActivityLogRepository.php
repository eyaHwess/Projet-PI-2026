<?php

namespace App\Repository;

use App\Entity\DailyActivityLog;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class DailyActivityLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DailyActivityLog::class);
    }

    /**
     * Trouve ou crée un log pour une date donnée
     */
    public function findOrCreateForDate(User $user, \DateTimeInterface $date): DailyActivityLog
    {
        $log = $this->createQueryBuilder('d')
            ->where('d.user = :user')
            ->andWhere('d.logDate = :date')
            ->setParameter('user', $user)
            ->setParameter('date', $date->format('Y-m-d'))
            ->getQuery()
            ->getOneOrNullResult();

        if (!$log) {
            $log = new DailyActivityLog();
            $log->setUser($user);
            $log->setLogDate($date);
        }

        return $log;
    }

    /**
     * Récupère les logs pour une année donnée
     */
    public function findByYear(User $user, int $year): array
    {
        $startDate = new \DateTime("$year-01-01");
        $endDate = new \DateTime("$year-12-31");

        return $this->createQueryBuilder('d')
            ->where('d.user = :user')
            ->andWhere('d.logDate BETWEEN :start AND :end')
            ->setParameter('user', $user)
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate)
            ->orderBy('d.logDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère les logs des N derniers jours
     */
    public function findLastNDays(User $user, int $days): array
    {
        $startDate = new \DateTime("-$days days");

        return $this->createQueryBuilder('d')
            ->where('d.user = :user')
            ->andWhere('d.logDate >= :start')
            ->setParameter('user', $user)
            ->setParameter('start', $startDate)
            ->orderBy('d.logDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Calcule le score de consistance (moyenne sur 30 jours)
     */
    public function calculateConsistencyScore(User $user): float
    {
        $logs = $this->findLastNDays($user, 30);
        
        if (empty($logs)) {
            return 0.0;
        }

        $total = 0;
        foreach ($logs as $log) {
            $total += (float) $log->getCompletionPercentage();
        }

        return round($total / count($logs), 2);
    }

    /**
     * Trouve la plus longue série de jours avec completion > 0
     */
    public function findLongestStreak(User $user): int
    {
        $logs = $this->createQueryBuilder('d')
            ->where('d.user = :user')
            ->andWhere('d.completionPercentage > 0')
            ->setParameter('user', $user)
            ->orderBy('d.logDate', 'ASC')
            ->getQuery()
            ->getResult();

        if (empty($logs)) {
            return 0;
        }

        $maxStreak = 1;
        $currentStreak = 1;
        $previousDate = null;

        foreach ($logs as $log) {
            if ($previousDate) {
                $diff = $previousDate->diff($log->getLogDate())->days;
                
                if ($diff == 1) {
                    $currentStreak++;
                    $maxStreak = max($maxStreak, $currentStreak);
                } else {
                    $currentStreak = 1;
                }
            }
            
            $previousDate = $log->getLogDate();
        }

        return $maxStreak;
    }

    /**
     * Trouve le jour de la semaine le plus productif
     */
    public function findMostProductiveWeekday(User $user): ?string
    {
        $logs = $this->findLastNDays($user, 90); // 3 derniers mois
        
        if (empty($logs)) {
            return null;
        }

        $weekdayScores = [
            'Monday' => ['total' => 0, 'count' => 0],
            'Tuesday' => ['total' => 0, 'count' => 0],
            'Wednesday' => ['total' => 0, 'count' => 0],
            'Thursday' => ['total' => 0, 'count' => 0],
            'Friday' => ['total' => 0, 'count' => 0],
            'Saturday' => ['total' => 0, 'count' => 0],
            'Sunday' => ['total' => 0, 'count' => 0],
        ];

        foreach ($logs as $log) {
            $weekday = $log->getLogDate()->format('l');
            $weekdayScores[$weekday]['total'] += (float) $log->getCompletionPercentage();
            $weekdayScores[$weekday]['count']++;
        }

        $maxAverage = 0;
        $mostProductiveDay = null;

        foreach ($weekdayScores as $day => $data) {
            if ($data['count'] > 0) {
                $average = $data['total'] / $data['count'];
                if ($average > $maxAverage) {
                    $maxAverage = $average;
                    $mostProductiveDay = $day;
                }
            }
        }

        return $mostProductiveDay;
    }

    /**
     * Calcule la tendance (Improving / Stable / Decreasing)
     */
    public function calculateTrend(User $user): string
    {
        $recentLogs = $this->findLastNDays($user, 14); // 2 dernières semaines
        
        if (count($recentLogs) < 7) {
            return 'Stable';
        }

        // Diviser en 2 périodes
        $firstWeek = array_slice($recentLogs, 7, 7);
        $secondWeek = array_slice($recentLogs, 0, 7);

        $firstWeekAvg = 0;
        foreach ($firstWeek as $log) {
            $firstWeekAvg += (float) $log->getCompletionPercentage();
        }
        $firstWeekAvg = count($firstWeek) > 0 ? $firstWeekAvg / count($firstWeek) : 0;

        $secondWeekAvg = 0;
        foreach ($secondWeek as $log) {
            $secondWeekAvg += (float) $log->getCompletionPercentage();
        }
        $secondWeekAvg = count($secondWeek) > 0 ? $secondWeekAvg / count($secondWeek) : 0;

        $difference = $secondWeekAvg - $firstWeekAvg;

        if ($difference > 10) {
            return 'Improving';
        } elseif ($difference < -10) {
            return 'Decreasing';
        } else {
            return 'Stable';
        }
    }
}
