<?php

namespace App\Controller;

use App\Repository\GoalRepository;
use App\Repository\UserRepository;
use App\Service\ChartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ChartTestController extends AbstractController
{
    public function __construct(
        private UserRepository $userRepository,
        private GoalRepository $goalRepository,
        private ChartService $chartService
    ) {
    }

    #[Route('/test-chart', name: 'test_chart')]
    public function testChart(): Response
    {
        $user = $this->getUser();
        if (!$user) {
            $user = $this->userRepository->findOneBy(['email' => 'static@example.com']);
        }
        if (!$user) {
            $user = $this->userRepository->findOneBy([], ['id' => 'ASC']);
        }
        if (!$user) {
            throw $this->createNotFoundException('Aucun utilisateur en base. Connectez-vous ou créez un utilisateur.');
        }

        $chart = $this->chartService->createUserOverviewChart($user);

        return $this->render('test_chart.html.twig', [
            'chart' => $chart,
            'user' => $user
        ]);
    }
}
