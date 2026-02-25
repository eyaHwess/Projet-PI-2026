<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DemoSessionController extends AbstractController
{
    #[Route('/demo/sessions', name: 'app_demo_sessions')]
    public function index(): Response
    {
        // Données statiques pour les sessions
        $sessions = [
            [
                'id' => 1,
                'scheduledAt' => new \DateTimeImmutable('2026-02-15 10:00:00'),
                'duration' => 60,
                'coach' => ['firstName' => 'Sarah', 'lastName' => 'Martin'],
                'user' => ['firstName' => 'Jean', 'lastName' => 'Dupont'],
                'status' => 'confirmed',
            ],
            [
                'id' => 2,
                'scheduledAt' => new \DateTimeImmutable('2026-02-16 14:30:00'),
                'duration' => 90,
                'coach' => ['firstName' => 'Thomas', 'lastName' => 'Dubois'],
                'user' => ['firstName' => 'Sophie', 'lastName' => 'Martin'],
                'status' => 'scheduling',
            ],
            [
                'id' => 3,
                'scheduledAt' => new \DateTimeImmutable('2026-02-17 09:00:00'),
                'duration' => 45,
                'coach' => ['firstName' => 'Marie', 'lastName' => 'Laurent'],
                'user' => ['firstName' => 'Claire', 'lastName' => 'Dubois'],
                'status' => 'completed',
            ],
            [
                'id' => 4,
                'scheduledAt' => new \DateTimeImmutable('2026-02-18 16:00:00'),
                'duration' => 120,
                'coach' => ['firstName' => 'Pierre', 'lastName' => 'Moreau'],
                'user' => ['firstName' => 'Marc', 'lastName' => 'Lefebvre'],
                'status' => 'proposed_by_coach',
            ],
            [
                'id' => 5,
                'scheduledAt' => null,
                'duration' => 60,
                'coach' => ['firstName' => 'Julie', 'lastName' => 'Bernard'],
                'user' => ['firstName' => 'Paul', 'lastName' => 'Bernard'],
                'status' => 'cancelled',
            ],
        ];

        return $this->render('demo/sessions.html.twig', [
            'sessions' => $sessions,
        ]);
    }
}
