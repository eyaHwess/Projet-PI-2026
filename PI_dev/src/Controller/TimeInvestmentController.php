<?php

namespace App\Controller;

use App\Service\TimeInvestmentAnalyzer;
use App\Repository\UserRepository;
use App\Repository\GoalRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/time-investment', name: 'app_time_investment_')]
class TimeInvestmentController extends AbstractController
{
    public function __construct(
        private TimeInvestmentAnalyzer $analyzer,
        private UserRepository $userRepository,
        private GoalRepository $goalRepository
    ) {}

    #[Route('/analytics', name: 'analytics')]
    public function analytics(Request $request): Response
    {
        // Use static user for now
        $user = $this->userRepository->findOneBy(['email' => 'static@example.com']);
        
        if (!$user) {
            throw $this->createNotFoundException('User not found');
        }

        // Get comprehensive analytics
        $analytics = $this->analyzer->getComprehensiveAnalytics($user);
        
        // Get weekly breakdown
        $weekStart = $request->query->get('week') 
            ? new \DateTime($request->query->get('week'))
            : new \DateTime('monday this week');
        
        $weeklyBreakdown = $this->analyzer->getWeeklyBreakdown($user, $weekStart);
        
        // Get month and year from query or use current
        $month = (int) ($request->query->get('month') ?? date('m'));
        $year = (int) ($request->query->get('year') ?? date('Y'));
        
        // Calculate monthly time for each goal
        $goals = $this->goalRepository->findBy(['user' => $user]);
        $monthlyData = [];
        
        foreach ($goals as $goal) {
            $monthlyTime = $this->analyzer->calculateMonthlyTime($goal, $month, $year);
            $efficiency = $this->analyzer->calculateGoalTimeEfficiency($goal);
            
            if ($monthlyTime > 0 || $efficiency !== null) {
                $monthlyData[] = [
                    'goal' => $goal,
                    'time' => $monthlyTime,
                    'hours' => round($monthlyTime / 60, 2),
                    'efficiency' => $efficiency
                ];
            }
        }
        
        // Sort by time descending
        usort($monthlyData, fn($a, $b) => $b['time'] <=> $a['time']);

        return $this->render('time_investment/analytics.html.twig', [
            'analytics' => $analytics,
            'weeklyBreakdown' => $weeklyBreakdown,
            'monthlyData' => $monthlyData,
            'currentWeek' => $weekStart,
            'currentMonth' => $month,
            'currentYear' => $year,
            'analyzer' => $this->analyzer
        ]);
    }

    #[Route('/goal/{id}', name: 'goal_details')]
    public function goalDetails(int $id): Response
    {
        $goal = $this->goalRepository->find($id);
        
        if (!$goal) {
            throw $this->createNotFoundException('Goal not found');
        }

        $totalTime = $this->analyzer->calculateGoalTotalTime($goal);
        $weeklyTime = $this->analyzer->calculateWeeklyTime($goal);
        $monthlyTime = $this->analyzer->calculateMonthlyTime($goal);
        $efficiency = $this->analyzer->calculateGoalTimeEfficiency($goal);

        return $this->render('time_investment/goal_details.html.twig', [
            'goal' => $goal,
            'totalTime' => $totalTime,
            'weeklyTime' => $weeklyTime,
            'monthlyTime' => $monthlyTime,
            'efficiency' => $efficiency,
            'analyzer' => $this->analyzer
        ]);
    }
}
