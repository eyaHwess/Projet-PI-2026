<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DemoCoachRequestController extends AbstractController
{
    #[Route('/demo/coach/requests', name: 'app_demo_coach_requests')]
    public function index(): Response
    {
        // Demandes en attente (pending)
        $pendingRequests = [
            [
                'id' => 1,
                'user' => [
                    'firstName' => 'Jean',
                    'lastName' => 'Dupont',
                    'email' => 'jean.dupont@email.com'
                ],
                'message' => 'Bonjour, je souhaite perdre 10kg et améliorer ma condition physique générale. Je n\'ai pas fait de sport depuis 2 ans et j\'aimerais reprendre progressivement avec un suivi personnalisé.',
                'createdAt' => new \DateTimeImmutable('2026-02-11 09:30:00'),
                'status' => 'pending'
            ],
            [
                'id' => 2,
                'user' => [
                    'firstName' => 'Sophie',
                    'lastName' => 'Martin',
                    'email' => 'sophie.martin@email.com'
                ],
                'message' => 'Je prépare un marathon dans 6 mois et j\'ai besoin d\'un programme d\'entraînement structuré. J\'ai déjà une bonne base en course à pied mais je veux optimiser ma préparation.',
                'createdAt' => new \DateTimeImmutable('2026-02-11 14:15:00'),
                'status' => 'pending'
            ],
            [
                'id' => 3,
                'user' => [
                    'firstName' => 'Marc',
                    'lastName' => 'Lefebvre',
                    'email' => 'marc.lefebvre@email.com'
                ],
                'message' => 'Je veux développer ma masse musculaire au niveau du haut du corps. Je m\'entraîne 4 fois par semaine mais je stagne depuis plusieurs mois. J\'ai besoin de conseils sur la technique et la nutrition.',
                'createdAt' => new \DateTimeImmutable('2026-02-11 16:45:00'),
                'status' => 'pending'
            ],
        ];

        // Toutes les demandes (historique)
        $allRequests = [
            [
                'id' => 1,
                'user' => [
                    'firstName' => 'Jean',
                    'lastName' => 'Dupont',
                    'email' => 'jean.dupont@email.com'
                ],
                'message' => 'Bonjour, je souhaite perdre 10kg et améliorer ma condition physique générale. Je n\'ai pas fait de sport depuis 2 ans et j\'aimerais reprendre progressivement avec un suivi personnalisé.',
                'createdAt' => new \DateTimeImmutable('2026-02-11 09:30:00'),
                'status' => 'pending'
            ],
            [
                'id' => 2,
                'user' => [
                    'firstName' => 'Sophie',
                    'lastName' => 'Martin',
                    'email' => 'sophie.martin@email.com'
                ],
                'message' => 'Je prépare un marathon dans 6 mois et j\'ai besoin d\'un programme d\'entraînement structuré. J\'ai déjà une bonne base en course à pied mais je veux optimiser ma préparation.',
                'createdAt' => new \DateTimeImmutable('2026-02-11 14:15:00'),
                'status' => 'pending'
            ],
            [
                'id' => 3,
                'user' => [
                    'firstName' => 'Marc',
                    'lastName' => 'Lefebvre',
                    'email' => 'marc.lefebvre@email.com'
                ],
                'message' => 'Je veux développer ma masse musculaire au niveau du haut du corps. Je m\'entraîne 4 fois par semaine mais je stagne depuis plusieurs mois. J\'ai besoin de conseils sur la technique et la nutrition.',
                'createdAt' => new \DateTimeImmutable('2026-02-11 16:45:00'),
                'status' => 'pending'
            ],
            [
                'id' => 4,
                'user' => [
                    'firstName' => 'Claire',
                    'lastName' => 'Dubois',
                    'email' => 'claire.dubois@email.com'
                ],
                'message' => 'Je cherche à améliorer ma souplesse et ma posture. Je travaille beaucoup devant un ordinateur et j\'ai des douleurs au dos.',
                'createdAt' => new \DateTimeImmutable('2026-02-10 11:20:00'),
                'status' => 'accepted'
            ],
            [
                'id' => 5,
                'user' => [
                    'firstName' => 'Paul',
                    'lastName' => 'Bernard',
                    'email' => 'paul.bernard@email.com'
                ],
                'message' => 'Je veux me remettre en forme après une blessure au genou. J\'ai besoin d\'exercices adaptés et progressifs.',
                'createdAt' => new \DateTimeImmutable('2026-02-09 15:30:00'),
                'status' => 'accepted'
            ],
            [
                'id' => 6,
                'user' => [
                    'firstName' => 'Emma',
                    'lastName' => 'Petit',
                    'email' => 'emma.petit@email.com'
                ],
                'message' => 'Je cherche un coach pour m\'aider à préparer une compétition de crossfit.',
                'createdAt' => new \DateTimeImmutable('2026-02-08 10:00:00'),
                'status' => 'declined'
            ],
        ];

        return $this->render('demo/coach_requests.html.twig', [
            'pendingRequests' => $pendingRequests,
            'allRequests' => $allRequests,
        ]);
    }
}
