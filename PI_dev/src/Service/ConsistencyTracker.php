<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\Activity;
use App\Repository\DailyActivityLogRepository;
use App\Repository\ActivityRepository;
use App\Repository\RoutineRepository;
use Doctrine\ORM\EntityManagerInterface;

class ConsistencyTracker
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private DailyActivityLogRepository $logRepository,
        private ActivityRepository $activityRepository,
        private RoutineRepository $routineRepository
    ) {}

    /**
     * Met à jour le log quotidien pour un utilisateur
     */
    public function updateDailyLog(User $user, ?\DateTimeInterface $date = null): void
    {
        $date = $date ?? new \DateTime();
        $log = $this->logRepository->findOrCreateForDate($user, $date);

        // Compter les activités du jour
        $activities = $this->activityRepository->createQueryBuilder('a')
            ->join('a.routine', 'r')
            ->join('r.goal', 'g')
            ->where('g.user = :user')
            ->andWhere('DATE(a.startTime) = :date')
            ->setParameter('user', $user)
            ->setParameter('date', $date->format('Y-m-d'))
            ->getQuery()
            ->getResult();

        $totalActivities = count($activities);
        $completedActivities = 0;

        foreach ($activities as $activity) {
            if ($activity->getStatus() === 'completed') {
                $completedActivities++;
            }
        }

        // Compter les routines actives du jour
        $routines = $this->routineRepository->createQueryBuilder('r')
            ->join('r.goal', 'g')
            ->where('g.user = :user')
            ->andWhere('r.status = :status')
            ->setParameter('user', $user)
            ->setParameter('status', 'active')
            ->getQuery()
            ->getResult();

        $totalRoutines = count($routines);
        $completedRoutines = 0;

        foreach ($routines as $routine) {
            if ($routine->getStatus() === 'completed') {
                $completedRoutines++;
            }
        }

        $log->setTotalActivities($totalActivities);
        $log->setCompletedActivities($completedActivities);
        $log->setTotalRoutines($totalRoutines);
        $log->setCompletedRoutines($completedRoutines);
        $log->calculateCompletionPercentage();

        $this->entityManager->persist($log);
        $this->entityManager->flush();
    }

    /**
     * Met à jour le log après modification d'une activité
     */
    public function updateLogAfterActivityChange(Activity $activity): void
    {
        $user = $activity->getRoutine()?->getGoal()?->getUser();
        
        if ($user && $activity->getStartTime()) {
            $this->updateDailyLog($user, $activity->getStartTime());
        }
    }

    /**
     * Génère les données pour le heatmap annuel
     */
    public function generateYearlyHeatmap(User $user, int $year): array
    {
        $logs = $this->logRepository->findByYear($user, $year);
        
        // Créer un tableau indexé par date
        $logsByDate = [];
        foreach ($logs as $log) {
            $dateKey = $log->getLogDate()->format('Y-m-d');
            $logsByDate[$dateKey] = $log;
        }

        // Générer la structure du heatmap (52 semaines x 7 jours)
        $heatmap = [];
        $startDate = new \DateTime("$year-01-01");
        
        // Commencer au premier lundi de l'année
        while ($startDate->format('N') != 1) {
            $startDate->modify('-1 day');
        }

        for ($week = 0; $week < 52; $week++) {
            $weekData = [];
            
            for ($day = 0; $day < 7; $day++) {
                $currentDate = clone $startDate;
                $currentDate->modify("+$week weeks +$day days");
                
                $dateKey = $currentDate->format('Y-m-d');
                $log = $logsByDate[$dateKey] ?? null;

                $weekData[] = [
                    'date' => $currentDate->format('Y-m-d'),
                    'dayOfWeek' => $currentDate->format('l'),
                    'color' => $log ? $log->getHeatmapColor() : '#f3f4f6',
                    'percentage' => $log ? (float) $log->getCompletionPercentage() : 0,
                    'completedActivities' => $log ? $log->getCompletedActivities() : 0,
                    'totalActivities' => $log ? $log->getTotalActivities() : 0,
                    'completedRoutines' => $log ? $log->getCompletedRoutines() : 0,
                    'totalRoutines' => $log ? $log->getTotalRoutines() : 0,
                ];
            }
            
            $heatmap[] = $weekData;
        }

        return $heatmap;
    }

    /**
     * Récupère les statistiques de consistance
     */
    public function getConsistencyStats(User $user): array
    {
        return [
            'consistencyScore' => $this->logRepository->calculateConsistencyScore($user),
            'longestStreak' => $this->logRepository->findLongestStreak($user),
            'mostProductiveDay' => $this->logRepository->findMostProductiveWeekday($user),
            'trend' => $this->logRepository->calculateTrend($user),
        ];
    }
    
    /**
     * Récupère les logs entre deux dates
     */
    public function getLogsBetweenDates(User $user, \DateTimeInterface $startDate, \DateTimeInterface $endDate): array
    {
        return $this->logRepository->createQueryBuilder('d')
            ->where('d.user = :user')
            ->andWhere('d.logDate BETWEEN :start AND :end')
            ->setParameter('user', $user)
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate)
            ->orderBy('d.logDate', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
