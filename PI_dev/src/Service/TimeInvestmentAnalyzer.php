<?php

namespace App\Service;

use App\Entity\Goal;
use App\Entity\User;
use App\Repository\GoalRepository;
use App\Repository\ActivityRepository;
use Doctrine\ORM\EntityManagerInterface;

class TimeInvestmentAnalyzer
{
    private const WEEKLY_THRESHOLD_HOURS = 40;
    private const IMBALANCE_THRESHOLD_PERCENT = 60;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private GoalRepository $goalRepository,
        private ActivityRepository $activityRepository
    ) {}

    /**
     * Calculate total time invested in a goal (in minutes)
     */
    public function calculateGoalTotalTime(Goal $goal): int
    {
        $qb = $this->entityManager->createQueryBuilder();
        
        $result = $qb->select('SUM(a.actualDurationMinutes) as totalTime')
            ->from('App\Entity\Activity', 'a')
            ->join('a.routine', 'r')
            ->where('r.goal = :goal')
            ->andWhere('a.status = :status')
            ->andWhere('a.actualDurationMinutes IS NOT NULL')
            ->setParameter('goal', $goal)
            ->setParameter('status', 'completed')
            ->getQuery()
            ->getSingleScalarResult();

        return (int) ($result ?? 0);
    }

    /**
     * Calculate weekly time investment for a goal
     */
    public function calculateWeeklyTime(Goal $goal, ?\DateTimeInterface $weekStart = null): int
    {
        if (!$weekStart) {
            $weekStart = new \DateTime('monday this week');
        }
        
        $weekEnd = (clone $weekStart)->modify('+6 days')->setTime(23, 59, 59);

        $qb = $this->entityManager->createQueryBuilder();
        
        $result = $qb->select('SUM(a.actualDurationMinutes) as weeklyTime')
            ->from('App\Entity\Activity', 'a')
            ->join('a.routine', 'r')
            ->where('r.goal = :goal')
            ->andWhere('a.status = :status')
            ->andWhere('a.completedAt BETWEEN :start AND :end')
            ->andWhere('a.actualDurationMinutes IS NOT NULL')
            ->setParameter('goal', $goal)
            ->setParameter('status', 'completed')
            ->setParameter('start', $weekStart)
            ->setParameter('end', $weekEnd)
            ->getQuery()
            ->getSingleScalarResult();

        return (int) ($result ?? 0);
    }

    /**
     * Calculate monthly time investment for a goal
     */
    public function calculateMonthlyTime(Goal $goal, ?int $month = null, ?int $year = null): int
    {
        $month = $month ?? (int) date('m');
        $year = $year ?? (int) date('Y');
        
        $monthStart = new \DateTime("$year-$month-01 00:00:00");
        $monthEnd = (clone $monthStart)->modify('last day of this month')->setTime(23, 59, 59);

        $qb = $this->entityManager->createQueryBuilder();
        
        $result = $qb->select('SUM(a.actualDurationMinutes) as monthlyTime')
            ->from('App\Entity\Activity', 'a')
            ->join('a.routine', 'r')
            ->where('r.goal = :goal')
            ->andWhere('a.status = :status')
            ->andWhere('a.completedAt BETWEEN :start AND :end')
            ->andWhere('a.actualDurationMinutes IS NOT NULL')
            ->setParameter('goal', $goal)
            ->setParameter('status', 'completed')
            ->setParameter('start', $monthStart)
            ->setParameter('end', $monthEnd)
            ->getQuery()
            ->getSingleScalarResult();

        return (int) ($result ?? 0);
    }

    /**
     * Get time distribution across all goals for a user
     */
    public function getTimeDistribution(User $user): array
    {
        $goals = $this->goalRepository->findBy(['user' => $user]);
        $distribution = [];
        $totalTime = 0;

        foreach ($goals as $goal) {
            $goalTime = $this->calculateGoalTotalTime($goal);
            $distribution[] = [
                'goal' => $goal,
                'time' => $goalTime,
                'percentage' => 0 // Will be calculated after we know total
            ];
            $totalTime += $goalTime;
        }

        // Calculate percentages
        if ($totalTime > 0) {
            foreach ($distribution as &$item) {
                $item['percentage'] = round(($item['time'] / $totalTime) * 100, 2);
            }
        }

        // Sort by time descending
        usort($distribution, fn($a, $b) => $b['time'] <=> $a['time']);

        return $distribution;
    }

    /**
     * Get the most time-consuming goal
     */
    public function getMostTimeConsumingGoal(User $user): ?array
    {
        $distribution = $this->getTimeDistribution($user);
        return !empty($distribution) ? $distribution[0] : null;
    }

    /**
     * Calculate total weekly time for user across all goals
     */
    public function calculateUserWeeklyTime(User $user, ?\DateTimeInterface $weekStart = null): int
    {
        if (!$weekStart) {
            $weekStart = new \DateTime('monday this week');
        }
        
        $weekEnd = (clone $weekStart)->modify('+6 days')->setTime(23, 59, 59);

        $qb = $this->entityManager->createQueryBuilder();
        
        $result = $qb->select('SUM(a.actualDurationMinutes) as weeklyTime')
            ->from('App\Entity\Activity', 'a')
            ->join('a.routine', 'r')
            ->join('r.goal', 'g')
            ->where('g.user = :user')
            ->andWhere('a.status = :status')
            ->andWhere('a.completedAt BETWEEN :start AND :end')
            ->andWhere('a.actualDurationMinutes IS NOT NULL')
            ->setParameter('user', $user)
            ->setParameter('status', 'completed')
            ->setParameter('start', $weekStart)
            ->setParameter('end', $weekEnd)
            ->getQuery()
            ->getSingleScalarResult();

        return (int) ($result ?? 0);
    }

    /**
     * Check if weekly time exceeds threshold
     */
    public function hasWeeklyOverload(User $user, ?\DateTimeInterface $weekStart = null): bool
    {
        $weeklyMinutes = $this->calculateUserWeeklyTime($user, $weekStart);
        $weeklyHours = $weeklyMinutes / 60;
        
        return $weeklyHours > self::WEEKLY_THRESHOLD_HOURS;
    }

    /**
     * Detect time imbalance (one goal consuming >60% of time)
     */
    public function detectTimeImbalance(User $user): ?array
    {
        $distribution = $this->getTimeDistribution($user);
        
        foreach ($distribution as $item) {
            if ($item['percentage'] > self::IMBALANCE_THRESHOLD_PERCENT) {
                return [
                    'goal' => $item['goal'],
                    'percentage' => $item['percentage'],
                    'warning' => "Ce goal consomme {$item['percentage']}% de votre temps total"
                ];
            }
        }
        
        return null;
    }

    /**
     * Calculate Time Focus Index (which goal receives most attention)
     */
    public function calculateTimeFocusIndex(User $user): array
    {
        $distribution = $this->getTimeDistribution($user);
        $focusIndex = [];

        foreach ($distribution as $item) {
            $focusIndex[] = [
                'goal' => $item['goal'],
                'focusScore' => $item['percentage'],
                'totalHours' => round($item['time'] / 60, 2),
                'rank' => 0 // Will be set below
            ];
        }

        // Assign ranks
        foreach ($focusIndex as $index => &$item) {
            $item['rank'] = $index + 1;
        }

        return $focusIndex;
    }

    /**
     * Calculate average time efficiency across all completed activities for a goal
     */
    public function calculateGoalTimeEfficiency(Goal $goal): ?float
    {
        $qb = $this->entityManager->createQueryBuilder();
        
        $activities = $qb->select('a')
            ->from('App\Entity\Activity', 'a')
            ->join('a.routine', 'r')
            ->where('r.goal = :goal')
            ->andWhere('a.status = :status')
            ->andWhere('a.actualDurationMinutes IS NOT NULL')
            ->andWhere('a.plannedDurationMinutes IS NOT NULL')
            ->andWhere('a.plannedDurationMinutes > 0')
            ->setParameter('goal', $goal)
            ->setParameter('status', 'completed')
            ->getQuery()
            ->getResult();

        if (empty($activities)) {
            return null;
        }

        $totalEfficiency = 0;
        $count = 0;

        foreach ($activities as $activity) {
            $efficiency = $activity->getTimeEfficiency();
            if ($efficiency !== null) {
                $totalEfficiency += $efficiency;
                $count++;
            }
        }

        return $count > 0 ? round($totalEfficiency / $count, 2) : null;
    }

    /**
     * Get comprehensive analytics for a user
     */
    public function getComprehensiveAnalytics(User $user): array
    {
        $weeklyTime = $this->calculateUserWeeklyTime($user);
        $distribution = $this->getTimeDistribution($user);
        $mostTimeConsuming = $this->getMostTimeConsumingGoal($user);
        $hasOverload = $this->hasWeeklyOverload($user);
        $imbalance = $this->detectTimeImbalance($user);
        $focusIndex = $this->calculateTimeFocusIndex($user);

        return [
            'weeklyTime' => [
                'minutes' => $weeklyTime,
                'hours' => round($weeklyTime / 60, 2),
                'hasOverload' => $hasOverload,
                'threshold' => self::WEEKLY_THRESHOLD_HOURS
            ],
            'distribution' => $distribution,
            'mostTimeConsuming' => $mostTimeConsuming,
            'imbalance' => $imbalance,
            'focusIndex' => $focusIndex,
            'totalGoals' => count($distribution),
            'totalTime' => array_sum(array_column($distribution, 'time'))
        ];
    }

    /**
     * Get weekly time breakdown by day
     */
    public function getWeeklyBreakdown(User $user, ?\DateTimeInterface $weekStart = null): array
    {
        if (!$weekStart) {
            $weekStart = new \DateTime('monday this week');
        }

        $breakdown = [];
        $currentDay = clone $weekStart;

        for ($i = 0; $i < 7; $i++) {
            $dayStart = (clone $currentDay)->setTime(0, 0, 0);
            $dayEnd = (clone $currentDay)->setTime(23, 59, 59);

            $qb = $this->entityManager->createQueryBuilder();
            
            $result = $qb->select('SUM(a.actualDurationMinutes) as dailyTime')
                ->from('App\Entity\Activity', 'a')
                ->join('a.routine', 'r')
                ->join('r.goal', 'g')
                ->where('g.user = :user')
                ->andWhere('a.status = :status')
                ->andWhere('a.completedAt BETWEEN :start AND :end')
                ->andWhere('a.actualDurationMinutes IS NOT NULL')
                ->setParameter('user', $user)
                ->setParameter('status', 'completed')
                ->setParameter('start', $dayStart)
                ->setParameter('end', $dayEnd)
                ->getQuery()
                ->getSingleScalarResult();

            $breakdown[] = [
                'date' => clone $currentDay,
                'dayName' => $currentDay->format('l'),
                'minutes' => (int) ($result ?? 0),
                'hours' => round(($result ?? 0) / 60, 2)
            ];

            $currentDay->modify('+1 day');
        }

        return $breakdown;
    }

    /**
     * Convert minutes to human-readable format
     */
    public function formatDuration(int $minutes): string
    {
        if ($minutes < 60) {
            return $minutes . ' min';
        }

        $hours = floor($minutes / 60);
        $mins = $minutes % 60;

        if ($mins === 0) {
            return $hours . 'h';
        }

        return $hours . 'h ' . $mins . 'min';
    }
}
