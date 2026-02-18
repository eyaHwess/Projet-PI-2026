<?php

namespace App\Controller;

use App\Entity\Review;
use App\Repository\ReviewRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/reviews')]
class ReviewController extends AbstractController
{
    public function __construct(
        private ReviewRepository $reviewRepository,
        private UserRepository $userRepository
    ) {
    }

    #[Route('/coach/{id}', name: 'app_review_coach', methods: ['GET'])]
    public function getCoachReviews(int $id): Response
    {
        $coach = $this->userRepository->find($id);

        if (!$coach || !$coach->isCoach()) {
            return $this->json([
                'success' => false,
                'message' => 'Coach non trouvé'
            ], 404);
        }

        $reviews = $this->reviewRepository->findVisibleByCoach($coach);
        $stats = $this->reviewRepository->getRatingStatsForCoach($coach);

        return $this->json([
            'success' => true,
            'coach' => [
                'id' => $coach->getId(),
                'firstName' => $coach->getFirstName(),
                'lastName' => $coach->getLastName(),
            ],
            'stats' => $stats,
            'reviews' => array_map(function($review) {
                return [
                    'id' => $review->getId(),
                    'rating' => $review->getRating(),
                    'comment' => $review->getComment(),
                    'userName' => $review->getUser()->getFirstName() . ' ' . substr($review->getUser()->getLastName(), 0, 1) . '.',
                    'isVerified' => $review->isVerified(),
                    'createdAt' => $review->getCreatedAt()->format('d/m/Y'),
                    'createdAtRelative' => $this->getRelativeTime($review->getCreatedAt())
                ];
            }, $reviews)
        ]);
    }

    private function getRelativeTime(\DateTimeImmutable $date): string
    {
        $now = new \DateTimeImmutable();
        $diff = $now->diff($date);

        if ($diff->y > 0) {
            return $diff->y === 1 ? 'il y a 1 an' : "il y a {$diff->y} ans";
        }
        if ($diff->m > 0) {
            return $diff->m === 1 ? 'il y a 1 mois' : "il y a {$diff->m} mois";
        }
        if ($diff->d > 0) {
            return $diff->d === 1 ? 'il y a 1 jour' : "il y a {$diff->d} jours";
        }
        if ($diff->h > 0) {
            return $diff->h === 1 ? 'il y a 1 heure' : "il y a {$diff->h} heures";
        }
        
        return 'Aujourd\'hui';
    }
}
