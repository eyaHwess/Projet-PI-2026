<?php

namespace App\Repository;

use App\Entity\Review;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Review>
 */
class ReviewRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Review::class);
    }

    /**
     * Récupère tous les avis visibles pour un coach
     */
    public function findVisibleByCoach(User $coach): array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.coach = :coach')
            ->andWhere('r.isVisible = :visible')
            ->setParameter('coach', $coach)
            ->setParameter('visible', true)
            ->orderBy('r.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Calcule la moyenne des notes pour un coach
     */
    public function getAverageRatingForCoach(User $coach): ?float
    {
        $result = $this->createQueryBuilder('r')
            ->select('AVG(r.rating) as avgRating')
            ->andWhere('r.coach = :coach')
            ->andWhere('r.isVisible = :visible')
            ->setParameter('coach', $coach)
            ->setParameter('visible', true)
            ->getQuery()
            ->getSingleScalarResult();

        return $result ? round((float)$result, 1) : null;
    }

    /**
     * Compte le nombre d'avis pour un coach
     */
    public function countReviewsForCoach(User $coach): int
    {
        return $this->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->andWhere('r.coach = :coach')
            ->andWhere('r.isVisible = :visible')
            ->setParameter('coach', $coach)
            ->setParameter('visible', true)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Vérifie si un utilisateur a déjà laissé un avis pour un coach
     */
    public function hasUserReviewedCoach(User $user, User $coach): bool
    {
        $count = $this->createQueryBuilder('r')
            ->select('COUNT(r.id)')
            ->andWhere('r.user = :user')
            ->andWhere('r.coach = :coach')
            ->setParameter('user', $user)
            ->setParameter('coach', $coach)
            ->getQuery()
            ->getSingleScalarResult();

        return $count > 0;
    }

    /**
     * Récupère les statistiques de notes pour un coach
     */
    public function getRatingStatsForCoach(User $coach): array
    {
        $reviews = $this->findVisibleByCoach($coach);
        
        $stats = [
            'total' => count($reviews),
            'average' => 0,
            'distribution' => [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0]
        ];

        if (empty($reviews)) {
            return $stats;
        }

        $sum = 0;
        foreach ($reviews as $review) {
            $rating = (int)round($review->getRating());
            $stats['distribution'][$rating]++;
            $sum += $review->getRating();
        }

        $stats['average'] = round($sum / count($reviews), 1);

        return $stats;
    }
}
