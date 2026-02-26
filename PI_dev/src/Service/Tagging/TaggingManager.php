<?php

namespace App\Service\Tagging;

use App\Entity\Post;
use App\Entity\Tag;
use Psr\Log\LoggerInterface;

class TaggingManager
{
    public const STRATEGY_TFIDF = 'tfidf';
    public const STRATEGY_EMBEDDING = 'embedding';

    public function __construct(
        private TfIdfTaggingService $tfIdfTaggingService,
        private EmbeddingTaggingService $embeddingTaggingService,
        private LoggerInterface $logger,
        private string $defaultStrategy = self::STRATEGY_TFIDF
    ) {
    }

    /**
     * Generate tags for a post using the selected strategy.
     *
     * @return Tag[]
     */
    public function generateTagsForPost(Post $post, ?int $maxTags = null, ?string $strategy = null): array
    {
        $strategy = $strategy ?? $this->defaultStrategy;

        return match ($strategy) {
            self::STRATEGY_EMBEDDING => $this->embeddingTaggingService->generateTagsForPost($post, $maxTags),
            self::STRATEGY_TFIDF,
            $default => $this->tfIdfTaggingService->generateTagsForPost($post, $maxTags),
        };
    }

    /**
     * Regenerate tags for a post using the selected strategy.
     *
     * @return Tag[]
     */
    public function regenerateTagsForPost(Post $post, ?int $maxTags = null, ?string $strategy = null): array
    {
        $strategy = $strategy ?? $this->defaultStrategy;

        return match ($strategy) {
            self::STRATEGY_EMBEDDING => $this->embeddingTaggingService->regenerateTagsForPost($post, $maxTags),
            self::STRATEGY_TFIDF,
            $default => $this->tfIdfTaggingService->regenerateTagsForPost($post, $maxTags),
        };
    }
}

