<?php

namespace App\Controller;

use App\Repository\TimeSlotRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/timeslots')]
class TimeSlotController extends AbstractController
{
    public function __construct(
        private TimeSlotRepository $timeSlotRepository,
        private UserRepository $userRepository
    ) {
    }

    /**
     * Récupère les créneaux disponibles d'un coach pour une période
     */
    #[Route('/coach/{id}', name: 'api_timeslots_coach', methods: ['GET'])]
    public function getCoachTimeSlots(int $id, Request $request): JsonResponse
    {
        $coach = $this->userRepository->find($id);

        if (!$coach || !$coach->isCoach()) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Coach non trouvé'
            ], 404);
        }

        $startDate = $request->query->get('start');
        $endDate = $request->query->get('end');

        if (!$startDate || !$endDate) {
            // Par défaut : 14 prochains jours
            $start = new \DateTimeImmutable('today');
            $end = new \DateTimeImmutable('+14 days');
        } else {
            $start = new \DateTimeImmutable($startDate);
            $end = new \DateTimeImmutable($endDate);
        }

        $timeSlots = $this->timeSlotRepository->findByCoachAndPeriod($coach, $start, $end);

        $data = array_map(function ($slot) {
            return [
                'id' => $slot->getId(),
                'start' => $slot->getStartTime()->format('Y-m-d\TH:i:s'),
                'end' => $slot->getEndTime()->format('Y-m-d\TH:i:s'),
                'title' => $slot->isAvailable() ? 'Disponible' : 'Réservé',
                'available' => $slot->isAvailable(),
                'duration' => $slot->getDurationInMinutes(),
                'backgroundColor' => $slot->isAvailable() ? '#10B981' : '#EF4444',
                'borderColor' => $slot->isAvailable() ? '#059669' : '#DC2626',
            ];
        }, $timeSlots);

        return new JsonResponse([
            'success' => true,
            'timeSlots' => $data,
            'hasAvailableToday' => $this->timeSlotRepository->hasAvailableToday($coach),
            'totalAvailable' => $this->timeSlotRepository->countAvailableForCoach($coach)
        ]);
    }

    /**
     * Récupère les détails d'un créneau
     */
    #[Route('/{id}', name: 'api_timeslot_details', methods: ['GET'])]
    public function getTimeSlotDetails(int $id): JsonResponse
    {
        $slot = $this->timeSlotRepository->find($id);

        if (!$slot) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Créneau non trouvé'
            ], 404);
        }

        return new JsonResponse([
            'success' => true,
            'timeSlot' => [
                'id' => $slot->getId(),
                'coachName' => $slot->getCoach()->getFirstName() . ' ' . $slot->getCoach()->getLastName(),
                'startTime' => $slot->getStartTime()->format('Y-m-d H:i'),
                'endTime' => $slot->getEndTime()->format('Y-m-d H:i'),
                'duration' => $slot->getDurationInMinutes(),
                'available' => $slot->isAvailable(),
                'date' => $slot->getStartTime()->format('d/m/Y'),
                'time' => $slot->getStartTime()->format('H:i') . ' - ' . $slot->getEndTime()->format('H:i'),
            ]
        ]);
    }
}
