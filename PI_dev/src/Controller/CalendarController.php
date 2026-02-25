<?php

namespace App\Controller;

use App\Repository\GoalRepository;
use App\Repository\RoutineRepository;
use App\Repository\ActivityRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/calendar', name: 'app_calendar')]
class CalendarController extends AbstractController
{
    public function __construct(
        private GoalRepository $goalRepository,
        private RoutineRepository $routineRepository,
        private ActivityRepository $activityRepository
    ) {
    }

    #[Route('/', name: '', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('calendar/index.html.twig');
    }

    #[Route('/events', name: '_events', methods: ['GET'])]
    public function events(): JsonResponse
    {
        $events = [];

        // Récupérer tous les objectifs avec deadline
        $goals = $this->goalRepository->createQueryBuilder('g')
            ->where('g.deadline IS NOT NULL')
            ->getQuery()
            ->getResult();

        foreach ($goals as $goal) {
            $events[] = [
                'title' => $goal->getTitle(),
                'start' => $goal->getDeadline()->format('Y-m-d'),
                'allDay' => true,
                'className' => 'fc-event-goal',
                'extendedProps' => [
                    'type' => 'goal',
                    'typeName' => 'Objectif',
                    'description' => $goal->getDescription(),
                    'priority' => $goal->getPriority(),
                    'status' => $goal->getStatus(),
                    'url' => $this->generateUrl('app_goal_show', ['id' => $goal->getId()]),
                    'badgeClass' => 'bg-purple text-white',
                    'className' => 'fc-event-goal'
                ]
            ];
        }

        // Récupérer toutes les routines avec deadline
        $routines = $this->routineRepository->createQueryBuilder('r')
            ->where('r.deadline IS NOT NULL')
            ->getQuery()
            ->getResult();

        foreach ($routines as $routine) {
            $events[] = [
                'title' => $routine->getTitle(),
                'start' => $routine->getDeadline()->format('Y-m-d'),
                'allDay' => true,
                'className' => 'fc-event-routine',
                'extendedProps' => [
                    'type' => 'routine',
                    'typeName' => 'Routine',
                    'description' => $routine->getDescription(),
                    'priority' => $routine->getPriority(),
                    'url' => $this->generateUrl('app_routine_show', [
                        'goalId' => $routine->getGoal()->getId(),
                        'id' => $routine->getId()
                    ]),
                    'badgeClass' => 'bg-info text-white',
                    'className' => 'fc-event-routine'
                ]
            ];
        }

        // Récupérer toutes les activités
        $activities = $this->activityRepository->findAll();

        foreach ($activities as $activity) {
            // Ajouter l'activité avec son heure de début
            $events[] = [
                'title' => $activity->getTitle(),
                'start' => $activity->getStartTime()->format('Y-m-d\TH:i:s'),
                'allDay' => false,
                'className' => 'fc-event-activity',
                'extendedProps' => [
                    'type' => 'activity',
                    'typeName' => 'Activité',
                    'priority' => $activity->getPriority(),
                    'status' => $activity->getStatus(),
                    'url' => $this->generateUrl('app_routine_show', [
                        'goalId' => $activity->getRoutine()->getGoal()->getId(),
                        'id' => $activity->getRoutine()->getId()
                    ]),
                    'badgeClass' => 'bg-warning text-dark',
                    'className' => 'fc-event-activity'
                ]
            ];

            // Si l'activité a une deadline, l'ajouter aussi
            if ($activity->getDeadline()) {
                $events[] = [
                    'title' => $activity->getTitle() . ' (Deadline)',
                    'start' => $activity->getDeadline()->format('Y-m-d\TH:i:s'),
                    'allDay' => false,
                    'className' => 'fc-event-activity',
                    'extendedProps' => [
                        'type' => 'activity',
                        'typeName' => 'Activité - Deadline',
                        'priority' => $activity->getPriority(),
                        'status' => $activity->getStatus(),
                        'url' => $this->generateUrl('app_routine_show', [
                            'goalId' => $activity->getRoutine()->getGoal()->getId(),
                            'id' => $activity->getRoutine()->getId()
                        ]),
                        'badgeClass' => 'bg-danger text-white',
                        'className' => 'fc-event-activity'
                    ]
                ];
            }
        }

        return new JsonResponse($events);
    }
}
