<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/coaches', name: 'api_coaches_')]
class CoachSearchController extends AbstractController
{
    public function __construct(
        private UserRepository $userRepository
    ) {
    }

    #[Route('/search', name: 'search', methods: ['GET'])]
    public function search(Request $request): JsonResponse
    {
        $query = $request->query->get('q', '');
        $speciality = $request->query->get('speciality', '');
        $minPrice = $request->query->get('minPrice');
        $maxPrice = $request->query->get('maxPrice');
        $minRating = $request->query->get('minRating');
        $availability = $request->query->get('availability', '');
        $coachingType = $request->query->get('coachingType', '');
        $sortBy = $request->query->get('sortBy', 'rating');
        $sortOrder = $request->query->get('sortOrder', 'desc');

        $coaches = $this->userRepository->searchCoaches([
            'query' => $query,
            'speciality' => $speciality,
            'minPrice' => $minPrice ? (float) $minPrice : null,
            'maxPrice' => $maxPrice ? (float) $maxPrice : null,
            'minRating' => $minRating ? (float) $minRating : null,
            'availability' => $availability,
            'coachingType' => $coachingType,
            'sortBy' => $sortBy,
            'sortOrder' => $sortOrder,
        ]);

        $data = array_map(function ($coach) {
            return [
                'id' => $coach->getId(),
                'firstName' => $coach->getFirstName(),
                'lastName' => $coach->getLastName(),
                'email' => $coach->getEmail(),
                'speciality' => $coach->getSpeciality(),
                'rating' => $coach->getRating(),
                'reviewCount' => $coach->getReviewCount(),
                'pricePerSession' => $coach->getPricePerSession(),
                'availability' => $coach->getAvailability(),
                'bio' => $coach->getBio(),
                'photoUrl' => $coach->getPhotoUrl(),
                'badges' => $coach->getBadges(),
                'respondsQuickly' => $coach->getRespondsQuickly(),
                'totalSessions' => $coach->getTotalSessions(),
            ];
        }, $coaches);

        return $this->json([
            'success' => true,
            'coaches' => $data,
            'count' => count($data),
        ]);
    }

    #[Route('/filters', name: 'filters', methods: ['GET'])]
    public function getFilters(): JsonResponse
    {
        $specialities = $this->userRepository->findAllCoachSpecialities();
        $priceRange = $this->userRepository->getCoachPriceRange();
        $availabilities = $this->userRepository->getAvailabilities();

        return $this->json([
            'success' => true,
            'filters' => [
                'specialities' => $specialities,
                'priceRange' => $priceRange,
                'availabilities' => $availabilities,
                'coachingTypes' => ['En ligne', 'En présentiel', 'Hybride'],
            ],
        ]);
    }
}
