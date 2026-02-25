<?php

namespace App\Controller;

use App\Service\ConsistencyTracker;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/consistency', name: 'app_consistency_')]
class ConsistencyController extends AbstractController
{
    public function __construct(
        private ConsistencyTracker $consistencyTracker,
        private UserRepository $userRepository
    ) {}

    #[Route('/heatmap', name: 'heatmap')]
    public function heatmap(Request $request): Response
    {
        // Utiliser l'utilisateur statique pour le moment
        $user = $this->userRepository->findOneBy(['email' => 'static@example.com']);
        
        if (!$user) {
            throw $this->createNotFoundException('User not found');
        }

        $year = (int) $request->query->get('year', date('Y'));
        
        $heatmapData = $this->consistencyTracker->generateYearlyHeatmap($user, $year);
        $stats = $this->consistencyTracker->getConsistencyStats($user);
        
        // Calculate monthly averages for comparison
        $monthlyAverages = $this->calculateMonthlyAverages($user, $year);

        return $this->render('consistency/heatmap.html.twig', [
            'heatmapData' => $heatmapData,
            'stats' => $stats,
            'currentYear' => $year,
            'monthlyAverages' => $monthlyAverages,
        ]);
    }
    
    private function calculateMonthlyAverages(object $user, int $year): array
    {
        $monthlyData = [];
        
        for ($month = 1; $month <= 12; $month++) {
            $startDate = new \DateTime("$year-$month-01");
            $endDate = (clone $startDate)->modify('last day of this month');
            
            $logs = $this->consistencyTracker->getLogsBetweenDates($user, $startDate, $endDate);
            
            $total = 0;
            $count = count($logs);
            
            foreach ($logs as $log) {
                $total += (float) $log->getCompletionPercentage();
            }
            
            $average = $count > 0 ? round($total / $count, 2) : 0;
            
            $monthlyData[] = [
                'month' => $startDate->format('M'),
                'average' => $average,
                'count' => $count
            ];
        }
        
        return $monthlyData;
    }

    #[Route('/update', name: 'update', methods: ['POST'])]
    public function updateLog(): Response
    {
        $user = $this->userRepository->findOneBy(['email' => 'static@example.com']);
        
        if (!$user) {
            throw $this->createNotFoundException('User not found');
        }

        $this->consistencyTracker->updateDailyLog($user);

        return $this->redirectToRoute('app_consistency_heatmap');
    }
}
