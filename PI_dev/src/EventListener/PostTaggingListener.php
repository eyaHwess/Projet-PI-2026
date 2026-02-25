<?php

namespace App\EventListener;

use App\Entity\Post;
use App\Enum\PostStatus;
use App\Service\Tagging\TaggingManager;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Psr\Log\LoggerInterface;

/**
 * Automatically generates tags for posts when they are created or published.
 * 
 * This listener ensures tags are generated:
 * - When a post is created with status "published"
 * - When a post status changes to "published" (e.g., from draft or scheduled)
 * 
 * Tags are NOT regenerated on every edit to avoid overwriting manual tag changes.
 */
#[AsEntityListener(event: Events::postPersist, method: 'postPersist', entity: Post::class)]
#[AsEntityListener(event: Events::postUpdate, method: 'postUpdate', entity: Post::class)]
class PostTaggingListener
{
    public function __construct(
        private TaggingManager $taggingManager,
        private LoggerInterface $logger
    ) {
    }

    /**
     * Handle post creation - generate tags if post is published.
     */
    public function postPersist(Post $post, LifecycleEventArgs $event): void
    {
        if ($post->getStatus() === PostStatus::PUBLISHED->value) {
            $this->generateTagsIfNeeded($post);
        }
    }

    /**
     * Handle post updates - generate tags if status changed to published.
     */
    public function postUpdate(Post $post, LifecycleEventArgs $event): void
    {
        $unitOfWork = $event->getObjectManager()->getUnitOfWork();
        $changeSet = $unitOfWork->getEntityChangeSet($post);

        // Only generate tags if status changed to "published"
        if (isset($changeSet['status'])) {
            $oldStatus = $changeSet['status'][0];
            $newStatus = $changeSet['status'][1];

            // Generate tags if transitioning to published status
            if ($newStatus === PostStatus::PUBLISHED->value && $oldStatus !== PostStatus::PUBLISHED->value) {
                // Only generate if post doesn't already have tags (avoid overwriting)
                if ($post->getTags()->isEmpty()) {
                    $this->generateTagsIfNeeded($post);
                }
            }
        }
    }

    /**
     * Generate tags for a post if it doesn't already have tags.
     */
    private function generateTagsIfNeeded(Post $post): void
    {
        // Skip if post already has tags (avoid regenerating)
        if (!$post->getTags()->isEmpty()) {
            return;
        }

        try {
            $tags = $this->taggingManager->generateTagsForPost($post);
            
            if (!empty($tags)) {
                $this->logger->info('Auto-generated tags for post', [
                    'post_id' => $post->getId(),
                    'tag_count' => count($tags),
                    'tags' => array_map(fn($tag) => $tag->getName(), $tags),
                ]);
            }
        } catch (\Throwable $e) {
            // Log error but don't break post creation
            $this->logger->error('Failed to auto-generate tags for post', [
                'post_id' => $post->getId(),
                'error' => $e->getMessage(),
            ]);
        }
    }
}
