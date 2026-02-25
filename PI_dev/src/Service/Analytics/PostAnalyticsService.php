<?php

namespace App\Service\Analytics;

use App\Entity\Post;
use App\Entity\Tag;
use Doctrine\ORM\EntityManagerInterface;

class PostAnalyticsService
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    /**
     * Calculate CTR (Click-Through Rate)
     */
    public function calculateCTR(Post $post): float
    {
        $views = $post->getViewCount();
        if ($views === 0) {
            return 0.0;
        }
        
        $clicks = $post->getClickCount();
        return ($clicks / $views) * 100;
    }

    /**
     * Get CTR Score (Low, Medium, High)
     */
    public function getCTRScore(Post $post): string
    {
        $ctr = $this->calculateCTR($post);
        
        if ($ctr >= 6.0) {
            return 'High';
        } elseif ($ctr >= 2.0) {
            return 'Medium';
        } else {
            return 'Low';
        }
    }

    /**
     * Calculate Engagement Rate
     */
    public function calculateEngagementRate(Post $post): float
    {
        $views = $post->getViewCount();
        if ($views === 0) {
            return 0.0;
        }
        
        $likes = count($post->getPostLikes());
        $comments = count($post->getComments());
        $totalEngagements = $likes + $comments;
        
        return ($totalEngagements / $views) * 100;
    }

    /**
     * Calculate Interaction Score (0-100)
     */
    public function calculateInteractionScore(Post $post): int
    {
        $views = $post->getViewCount();
        $clicks = $post->getClickCount();
        $likes = count($post->getPostLikes());
        $comments = count($post->getComments());
        
        // Weighted scoring
        $viewScore = min($views / 10, 20); // Max 20 points
        $clickScore = min($clicks / 5, 25); // Max 25 points
        $likeScore = min($likes / 3, 30); // Max 30 points
        $commentScore = min($comments / 2, 25); // Max 25 points
        
        return (int) round($viewScore + $clickScore + $likeScore + $commentScore);
    }

    /**
     * Get Tag Performance Stats
     */
    public function getTagPerformance(Tag $tag): array
    {
        // Get all tags ordered by usage count
        $allTags = $this->entityManager->getRepository(Tag::class)
            ->createQueryBuilder('t')
            ->orderBy('t.usageCount', 'DESC')
            ->getQuery()
            ->getResult();
        
        // Calculate total usage
        $totalUsage = array_sum(array_map(fn($t) => $t->getUsageCount(), $allTags));
        
        // Find rank
        $rank = 0;
        foreach ($allTags as $index => $t) {
            if ($t->getId() === $tag->getId()) {
                $rank = $index + 1;
                break;
            }
        }
        
        // Calculate popularity index
        $popularityIndex = $totalUsage > 0 
            ? ($tag->getUsageCount() / $totalUsage) * 100 
            : 0.0;
        
        return [
            'rank' => $rank,
            'popularityIndex' => round($popularityIndex, 1),
            'usageCount' => $tag->getUsageCount(),
        ];
    }

    /**
     * Get Trend Status
     */
    public function getTrendStatus(Post $post): array
    {
        $now = new \DateTimeImmutable();
        $oneDayAgo = $now->modify('-1 day');
        $sevenDaysAgo = $now->modify('-7 days');
        $fourteenDaysAgo = $now->modify('-14 days');
        
        // For now, we'll use a simplified calculation based on view count
        // In production, you'd track views with timestamps
        $views7d = $post->getViewCount(); // Simplified: total views
        $views14d = $post->getViewCount(); // In real implementation, query view logs
        
        // Calculate growth (simplified)
        $previousPeriodViews = max(1, (int)($views14d * 0.4)); // Estimate
        $currentPeriodViews = (int)($views7d * 0.6); // Estimate
        
        $growth = $previousPeriodViews > 0 
            ? (($currentPeriodViews - $previousPeriodViews) / $previousPeriodViews) * 100 
            : 0;
        
        // Determine status
        if ($growth > 25) {
            $status = '🔥 Trending';
            $statusClass = 'trending';
        } elseif ($growth >= 5) {
            $status = '⬆ Growing';
            $statusClass = 'growing';
        } elseif ($growth < 0) {
            $status = '⬇ Declining';
            $statusClass = 'declining';
        } else {
            $status = 'Stable';
            $statusClass = 'stable';
        }
        
        return [
            'views7d' => $views7d,
            'growth' => round($growth, 1),
            'status' => $status,
            'statusClass' => $statusClass,
        ];
    }

    /**
     * Get all available tags with usage count
     */
    public function getAllTagsWithStats(): array
    {
        return $this->entityManager->getRepository(Tag::class)
            ->createQueryBuilder('t')
            ->orderBy('t.usageCount', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
