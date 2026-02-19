<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DemoCoachController extends AbstractController
{
    #[Route('/demo/coaches', name: 'app_demo_coaches')]
    public function index(): Response
    {
        // Données statiques pour les coaches
        $coaches = [
            [
                'id' => 1,
                'firstName' => 'Sarah',
                'lastName' => 'Martin',
                'email' => 'sarah.martin@fitcoach.com',
                'speciality' => 'Fitness',
                'rating' => 4.8,
            ],
            [
                'id' => 2,
                'firstName' => 'Thomas',
                'lastName' => 'Dubois',
                'email' => 'thomas.dubois@fitcoach.com',
                'speciality' => 'Yoga',
                'rating' => 4.9,
            ],
            [
                'id' => 3,
                'firstName' => 'Marie',
                'lastName' => 'Laurent',
                'email' => 'marie.laurent@fitcoach.com',
                'speciality' => 'Musculation',
                'rating' => 4.7,
            ],
            [
                'id' => 4,
                'firstName' => 'Pierre',
                'lastName' => 'Moreau',
                'email' => 'pierre.moreau@fitcoach.com',
                'speciality' => 'Nutrition',
                'rating' => 5.0,
            ],
            [
                'id' => 5,
                'firstName' => 'Julie',
                'lastName' => 'Bernard',
                'email' => 'julie.bernard@fitcoach.com',
                'speciality' => 'Cardio',
                'rating' => 4.6,
            ],
            [
                'id' => 6,
                'firstName' => 'Lucas',
                'lastName' => 'Petit',
                'email' => 'lucas.petit@fitcoach.com',
                'speciality' => 'Fitness',
                'rating' => 4.5,
            ],
        ];

        // Données statiques pour les spécialités
        $specialities = ['Fitness', 'Yoga', 'Musculation', 'Nutrition', 'Cardio'];

        // Données statiques pour les demandes de l'utilisateur
        $myRequests = [
            [
                'id' => 1,
                'coach' => ['id' => 2, 'firstName' => 'Thomas', 'lastName' => 'Dubois'],
                'message' => 'Je souhaite améliorer ma flexibilité et réduire mon stress. J\'ai entendu parler des bienfaits du yoga et j\'aimerais commencer avec un programme adapté aux débutants.',
                'status' => 'pending',
                'createdAt' => new \DateTimeImmutable('2026-02-11 14:30:00'),
            ],
            [
                'id' => 2,
                'coach' => ['id' => 3, 'firstName' => 'Marie', 'lastName' => 'Laurent'],
                'message' => 'Mon objectif est de prendre de la masse musculaire. Je m\'entraîne déjà 3 fois par semaine mais j\'ai besoin de conseils pour optimiser mes séances et ma nutrition.',
                'status' => 'accepted',
                'createdAt' => new \DateTimeImmutable('2026-02-10 10:15:00'),
            ],
            [
                'id' => 3,
                'coach' => ['id' => 5, 'firstName' => 'Julie', 'lastName' => 'Bernard'],
                'message' => 'Je veux perdre du poids et améliorer mon endurance cardiovasculaire. Je cherche un programme de cardio progressif adapté à mon niveau.',
                'status' => 'declined',
                'createdAt' => new \DateTimeImmutable('2026-02-09 16:45:00'),
            ],
        ];

        return $this->render('demo/coaches.html.twig', [
            'coaches' => $coaches,
            'specialities' => $specialities,
            'selectedSpeciality' => null,
            'myRequests' => $myRequests,
        ]);
    }
}
