<?php

namespace App\Service;

use App\Entity\Goal;
use App\Entity\Routine;
use App\Entity\Activity;
use App\Entity\User;
use App\Repository\GoalRepository;
use App\Repository\RoutineRepository;
use App\Repository\ActivityRepository;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

class ChartService
{
    public function __construct(
        private ChartBuilderInterface $chartBuilder,
        private GoalRepository $goalRepository,
        private RoutineRepository $routineRepository,
        private ActivityRepository $activityRepository
    ) {
    }

    /**
     * Goal Progress Chart - Pie chart showing completed vs remaining tasks
     */
    public function createGoalProgressChart(Goal $goal): Chart
    {
        $totalRoutines = $goal->getRoutines()->count();
        $completedRoutines = 0;

        foreach ($goal->getRoutines() as $routine) {
            $completedActivities = 0;
            $totalActivities = $routine->getActivities()->count();

            if ($totalActivities > 0) {
                foreach ($routine->getActivities() as $activity) {
                    if ($activity->getStatus() === 'completed') {
                        $completedActivities++;
                    }
                }

                if ($completedActivities === $totalActivities) {
                    $completedRoutines++;
                }
            }
        }

        $remaining = $totalRoutines - $completedRoutines;

        // If no routines, show placeholder data
        if ($totalRoutines === 0) {
            $completedRoutines = 0;
            $remaining = 1; // Show "No routines" as remaining
        }

        $chart = $this->chartBuilder->createChart(Chart::TYPE_DOUGHNUT);
        $chart->setData([
            'labels' => ['Terminées', 'En cours'],
            'datasets' => [
                [
                    'label' => 'Routines',
                    'data' => [$completedRoutines, $remaining],
                    'backgroundColor' => [
                        'rgba(181, 234, 215, 0.8)', // pastel-green
                        'rgba(168, 216, 234, 0.8)', // pastel-blue
                    ],
                    'borderColor' => [
                        'rgba(181, 234, 215, 1)',
                        'rgba(168, 216, 234, 1)',
                    ],
                    'borderWidth' => 2,
                ],
            ],
        ]);

        $chart->setOptions([
            'responsive' => true,
            'maintainAspectRatio' => true,
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                    'labels' => [
                        'padding' => 20,
                        'font' => [
                            'size' => 14,
                            'family' => 'Inter',
                        ],
                    ],
                ],
                'title' => [
                    'display' => true,
                    'text' => 'Progression des Routines',
                    'font' => [
                        'size' => 18,
                        'weight' => 'bold',
                    ],
                    'padding' => 20,
                ],
            ],
        ]);

        return $chart;
    }

    /**
     * Weekly Activity Completion Chart - Bar chart
     */
    public function createWeeklyActivityChart(Routine $routine): Chart
    {
        $activities = $routine->getActivities();
        $weekData = $this->getWeeklyActivityData($activities);

        // If no activities, show placeholder data
        $hasData = array_sum($weekData['planned']) > 0;
        if (!$hasData) {
            $weekData['planned'] = [1, 0, 0, 0, 0, 0, 0]; // Show Monday as placeholder
            $weekData['completed'] = [0, 0, 0, 0, 0, 0, 0];
        }

        $chart = $this->chartBuilder->createChart(Chart::TYPE_BAR);
        $chart->setData([
            'labels' => ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'],
            'datasets' => [
                [
                    'label' => 'Activités Terminées',
                    'data' => $weekData['completed'],
                    'backgroundColor' => 'rgba(181, 234, 215, 0.8)',
                    'borderColor' => 'rgba(181, 234, 215, 1)',
                    'borderWidth' => 2,
                    'borderRadius' => 8,
                ],
                [
                    'label' => 'Activités Planifiées',
                    'data' => $weekData['planned'],
                    'backgroundColor' => 'rgba(168, 216, 234, 0.8)',
                    'borderColor' => 'rgba(168, 216, 234, 1)',
                    'borderWidth' => 2,
                    'borderRadius' => 8,
                ],
            ],
        ]);

        $chart->setOptions([
            'responsive' => true,
            'maintainAspectRatio' => true,
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'stepSize' => 1,
                    ],
                ],
            ],
            'plugins' => [
                'legend' => [
                    'position' => 'top',
                    'labels' => [
                        'padding' => 15,
                        'font' => [
                            'size' => 13,
                        ],
                    ],
                ],
                'title' => [
                    'display' => true,
                    'text' => 'Activités de la Semaine',
                    'font' => [
                        'size' => 18,
                        'weight' => 'bold',
                    ],
                    'padding' => 20,
                ],
            ],
        ]);

        return $chart;
    }

    /**
     * Time Investment Chart - Planned vs Actual
     */
    public function createTimeInvestmentChart(Routine $routine): Chart
    {
        $timeData = $this->calculateTimeInvestment($routine);

        // If no time data, show placeholder
        if ($timeData['planned'] === 0 && $timeData['actual'] === 0) {
            $timeData['planned'] = 1; // Show 1 hour as placeholder
            $timeData['actual'] = 0;
        }

        $chart = $this->chartBuilder->createChart(Chart::TYPE_BAR);
        $chart->setData([
            'labels' => ['Temps Planifié', 'Temps Réel'],
            'datasets' => [
                [
                    'label' => 'Heures',
                    'data' => [$timeData['planned'], $timeData['actual']],
                    'backgroundColor' => [
                        'rgba(255, 209, 163, 0.8)', // pastel-orange
                        'rgba(226, 194, 232, 0.8)', // pastel-purple
                    ],
                    'borderColor' => [
                        'rgba(255, 209, 163, 1)',
                        'rgba(226, 194, 232, 1)',
                    ],
                    'borderWidth' => 2,
                    'borderRadius' => 8,
                ],
            ],
        ]);

        $chart->setOptions([
            'responsive' => true,
            'maintainAspectRatio' => true,
            'indexAxis' => 'y',
            'scales' => [
                'x' => [
                    'beginAtZero' => true,
                    'title' => [
                        'display' => true,
                        'text' => 'Heures',
                    ],
                ],
            ],
            'plugins' => [
                'legend' => [
                    'display' => false,
                ],
                'title' => [
                    'display' => true,
                    'text' => 'Investissement Temps',
                    'font' => [
                        'size' => 18,
                        'weight' => 'bold',
                    ],
                    'padding' => 20,
                ],
            ],
        ]);

        return $chart;
    }

    /**
     * Goal Burn-down Chart - Line chart showing progress over time
     */
    public function createBurndownChart(Goal $goal): Chart
    {
        $burndownData = $this->calculateBurndownData($goal);

        // If no data, show placeholder
        if (empty($burndownData['labels'])) {
            $burndownData = [
                'labels' => ['Début', 'Milieu', 'Fin'],
                'remaining' => [10, 5, 0],
                'ideal' => [10, 5, 0],
            ];
        }

        $chart = $this->chartBuilder->createChart(Chart::TYPE_LINE);
        $chart->setData([
            'labels' => $burndownData['labels'],
            'datasets' => [
                [
                    'label' => 'Tâches Restantes',
                    'data' => $burndownData['remaining'],
                    'borderColor' => 'rgba(255, 179, 217, 1)', // pastel-pink
                    'backgroundColor' => 'rgba(255, 179, 217, 0.1)',
                    'borderWidth' => 3,
                    'tension' => 0.4,
                    'fill' => true,
                    'pointRadius' => 5,
                    'pointHoverRadius' => 7,
                    'pointBackgroundColor' => 'rgba(255, 179, 217, 1)',
                ],
                [
                    'label' => 'Idéal',
                    'data' => $burndownData['ideal'],
                    'borderColor' => 'rgba(181, 234, 215, 1)', // pastel-green
                    'backgroundColor' => 'transparent',
                    'borderWidth' => 2,
                    'borderDash' => [5, 5],
                    'tension' => 0,
                    'pointRadius' => 0,
                ],
            ],
        ]);

        $chart->setOptions([
            'responsive' => true,
            'maintainAspectRatio' => true,
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'title' => [
                        'display' => true,
                        'text' => 'Nombre de Tâches',
                    ],
                ],
                'x' => [
                    'title' => [
                        'display' => true,
                        'text' => 'Jours',
                    ],
                ],
            ],
            'plugins' => [
                'legend' => [
                    'position' => 'top',
                    'labels' => [
                        'padding' => 15,
                    ],
                ],
                'title' => [
                    'display' => true,
                    'text' => 'Burn-down Chart',
                    'font' => [
                        'size' => 18,
                        'weight' => 'bold',
                    ],
                    'padding' => 20,
                ],
            ],
        ]);

        return $chart;
    }

    /**
     * User Overview Dashboard Chart - Multiple goals status
     */
    public function createUserOverviewChart(User $user): Chart
    {
        // Get ALL goals regardless of user
        $goals = $this->goalRepository->findAll();

        $statusCounts = [
            'active' => 0,
            'completed' => 0,
            'paused' => 0,
            'failed' => 0,
        ];

        foreach ($goals as $goal) {
            $status = $goal->getStatus();
            if (isset($statusCounts[$status])) {
                $statusCounts[$status]++;
            }
        }

        $chart = $this->chartBuilder->createChart(Chart::TYPE_PIE);
        $chart->setData([
            'labels' => ['Actifs', 'Terminés', 'En Pause', 'Échoués'],
            'datasets' => [
                [
                    'data' => [
                        $statusCounts['active'],
                        $statusCounts['completed'],
                        $statusCounts['paused'],
                        $statusCounts['failed'],
                    ],
                    'backgroundColor' => [
                        'rgba(168, 216, 234, 0.8)', // blue
                        'rgba(181, 234, 215, 0.8)', // green
                        'rgba(226, 194, 232, 0.8)', // purple
                        'rgba(255, 179, 217, 0.8)', // pink
                    ],
                    'borderColor' => [
                        'rgba(168, 216, 234, 1)',
                        'rgba(181, 234, 215, 1)',
                        'rgba(226, 194, 232, 1)',
                        'rgba(255, 179, 217, 1)',
                    ],
                    'borderWidth' => 2,
                ],
            ],
        ]);

        $chart->setOptions([
            'responsive' => true,
            'maintainAspectRatio' => true,
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                    'labels' => [
                        'padding' => 20,
                        'font' => [
                            'size' => 14,
                        ],
                    ],
                ],
                'title' => [
                    'display' => true,
                    'text' => 'Vue d\'ensemble des Objectifs',
                    'font' => [
                        'size' => 18,
                        'weight' => 'bold',
                    ],
                    'padding' => 20,
                ],
            ],
        ]);

        return $chart;
    }

    // ===== PRIVATE HELPER METHODS =====

    private function getWeeklyActivityData($activities): array
    {
        $completed = [0, 0, 0, 0, 0, 0, 0];
        $planned = [0, 0, 0, 0, 0, 0, 0];

        foreach ($activities as $activity) {
            $startTime = $activity->getStartTime();
            if ($startTime) {
                $dayOfWeek = (int) $startTime->format('N') - 1; // 0 = Monday

                if ($dayOfWeek >= 0 && $dayOfWeek < 7) {
                    $planned[$dayOfWeek]++;

                    if ($activity->getStatus() === 'completed') {
                        $completed[$dayOfWeek]++;
                    }
                }
            }
        }

        return [
            'completed' => $completed,
            'planned' => $planned,
        ];
    }

    private function calculateTimeInvestment(Routine $routine): array
    {
        $plannedMinutes = 0;
        $actualMinutes = 0;

        foreach ($routine->getActivities() as $activity) {
            $duration = $activity->getDuration();
            if ($duration) {
                $hours = (int) $duration->format('H');
                $minutes = (int) $duration->format('i');
                $totalMinutes = ($hours * 60) + $minutes;

                $plannedMinutes += $totalMinutes;

                if ($activity->getStatus() === 'completed') {
                    $actualMinutes += $totalMinutes;
                }
            }
        }

        return [
            'planned' => round($plannedMinutes / 60, 1),
            'actual' => round($actualMinutes / 60, 1),
        ];
    }

    private function calculateBurndownData(Goal $goal): array
    {
        $startDate = $goal->getStartDate();
        $endDate = $goal->getEndDate();

        if (!$startDate || !$endDate) {
            return [
                'labels' => ['Début', 'Fin'],
                'remaining' => [0, 0],
                'ideal' => [0, 0],
            ];
        }

        $totalDays = $startDate->diff($endDate)->days;
        $totalTasks = $goal->getRoutines()->count();

        $labels = [];
        $remaining = [];
        $ideal = [];

        $interval = max(1, floor($totalDays / 10)); // Show max 10 points

        for ($i = 0; $i <= $totalDays; $i += $interval) {
            $currentDate = (clone $startDate)->modify("+{$i} days");
            $labels[] = $currentDate->format('d/m');

            // Calculate ideal burndown
            $idealRemaining = $totalTasks - ($totalTasks * ($i / $totalDays));
            $ideal[] = round($idealRemaining, 1);

            // Calculate actual remaining (simplified - in production, track completion dates)
            $completedCount = 0;
            foreach ($goal->getRoutines() as $routine) {
                $allCompleted = true;
                foreach ($routine->getActivities() as $activity) {
                    if ($activity->getStatus() !== 'completed') {
                        $allCompleted = false;
                        break;
                    }
                }
                if ($allCompleted && $routine->getActivities()->count() > 0) {
                    $completedCount++;
                }
            }

            $remaining[] = $totalTasks - $completedCount;
        }

        return [
            'labels' => $labels,
            'remaining' => $remaining,
            'ideal' => $ideal,
        ];
    }
}
