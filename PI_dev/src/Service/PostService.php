<?php

namespace App\Service;

use App\Entity\Post;
use App\Entity\User;
use App\Enum\PostStatus;

final class PostService
{
    public const TITLE_MAX_LENGTH = 255;

    /**
     * Business rule #1:
     * A post must have a non-empty title and content (after trimming).
     *
     * Business rule #2:
     * Draft creation should explicitly set the post status to DRAFT.
     */
    public function createDraft(User $author, string $title, string $content, ?\DateTimeImmutable $now = null): Post
    {
        $now ??= new \DateTimeImmutable();

        $title = $this->normalize($title);
        $content = $this->normalize($content);

        $this->assertValidTitle($title);
        $this->assertValidContent($content);

        $post = new Post();
        $post->setCreatedBy($author);
        $post->setCreatedAt($now);
        $post->setTitle($title);
        $post->setContent($content);
        $post->setScheduledAt(null);
        $post->setStatusEnum(PostStatus::DRAFT);

        return $post;
    }

    /**
     * Business rule #3:
     * A scheduled post must have a scheduled date strictly in the future.
     */
    public function schedule(Post $post, \DateTimeImmutable $scheduledAt, ?\DateTimeImmutable $now = null): Post
    {
        $now ??= new \DateTimeImmutable();

        if ($post->isHidden()) {
            throw new \DomainException('Hidden posts cannot be scheduled.');
        }

        $title = $this->normalize((string) $post->getTitle());
        $content = $this->normalize((string) $post->getContent());

        $this->assertValidTitle($title);
        $this->assertValidContent($content);

        if ($scheduledAt <= $now) {
            throw new \DomainException('A post cannot be scheduled in the past.');
        }

        $post->setScheduledAt($scheduledAt);
        $post->setStatusEnum(PostStatus::SCHEDULED);

        return $post;
    }

    /**
     * Business rule #4:
     * Publishing a post clears any scheduling metadata.
     */
    public function publishNow(Post $post): Post
    {
        if ($post->isHidden()) {
            throw new \DomainException('Hidden posts cannot be published.');
        }

        $title = $this->normalize((string) $post->getTitle());
        $content = $this->normalize((string) $post->getContent());

        $this->assertValidTitle($title);
        $this->assertValidContent($content);

        $post->setScheduledAt(null);
        $post->setStatusEnum(PostStatus::PUBLISHED);

        return $post;
    }

    private function normalize(string $value): string
    {
        return trim($value);
    }

    private function assertValidTitle(string $title): void
    {
        if ($title === '') {
            throw new \InvalidArgumentException('Title is required.');
        }
        if (mb_strlen($title) > self::TITLE_MAX_LENGTH) {
            throw new \InvalidArgumentException(sprintf('Title cannot exceed %d characters.', self::TITLE_MAX_LENGTH));
        }
    }

    private function assertValidContent(string $content): void
    {
        if ($content === '') {
            throw new \InvalidArgumentException('Content is required.');
        }
    }
}

