<?php

namespace App\Tests\Service;

use App\Entity\Post;
use App\Entity\User;
use App\Enum\PostStatus;
use App\Service\PostService;
use PHPUnit\Framework\TestCase;

final class PostServiceTest extends TestCase
{
    public function testCreateDraftRejectsBlankTitle(): void
    {
        $service = new PostService();
        $author = (new User())->setEmail('test@example.com');

        $this->expectException(\InvalidArgumentException::class);
        $service->createDraft($author, '   ', 'valid content', new \DateTimeImmutable('2026-01-01 10:00:00'));
    }

    public function testCreateDraftRejectsBlankContent(): void
    {
        $service = new PostService();
        $author = (new User())->setEmail('test@example.com');

        $this->expectException(\InvalidArgumentException::class);
        $service->createDraft($author, 'valid title', '   ', new \DateTimeImmutable('2026-01-01 10:00:00'));
    }

    public function testCreateDraftCreatesDraftPost(): void
    {
        $service = new PostService();
        $author = (new User())->setEmail('test@example.com');
        $now = new \DateTimeImmutable('2026-01-01 10:00:00');

        $post = $service->createDraft($author, '  My Title  ', '  My content  ', $now);

        self::assertSame('My Title', $post->getTitle());
        self::assertSame('My content', $post->getContent());
        self::assertSame($author, $post->getCreatedBy());
        self::assertSame($now, $post->getCreatedAt());
        self::assertNull($post->getScheduledAt());
        self::assertTrue($post->isDraft());
        self::assertSame(PostStatus::DRAFT, $post->getStatusEnum());
    }

    public function testScheduleRejectsPastDate(): void
    {
        $service = new PostService();
        $author = (new User())->setEmail('test@example.com');
        $now = new \DateTimeImmutable('2026-01-01 10:00:00');

        $post = $service->createDraft($author, 'Title', 'Content', $now);

        $this->expectException(\DomainException::class);
        $service->schedule($post, new \DateTimeImmutable('2026-01-01 09:59:59'), $now);
    }

    public function testScheduleSetsStatusAndScheduledAt(): void
    {
        $service = new PostService();
        $author = (new User())->setEmail('test@example.com');
        $now = new \DateTimeImmutable('2026-01-01 10:00:00');

        $post = $service->createDraft($author, 'Title', 'Content', $now);

        $scheduledAt = new \DateTimeImmutable('2026-01-01 11:00:00');
        $service->schedule($post, $scheduledAt, $now);

        self::assertSame($scheduledAt, $post->getScheduledAt());
        self::assertTrue($post->isScheduled());
        self::assertSame(PostStatus::SCHEDULED, $post->getStatusEnum());
    }

    public function testPublishNowClearsSchedulingAndSetsPublished(): void
    {
        $service = new PostService();
        $author = (new User())->setEmail('test@example.com');
        $now = new \DateTimeImmutable('2026-01-01 10:00:00');

        $post = $service->createDraft($author, 'Title', 'Content', $now);
        $service->schedule($post, new \DateTimeImmutable('2026-01-01 11:00:00'), $now);

        $service->publishNow($post);

        self::assertNull($post->getScheduledAt());
        self::assertTrue($post->isPublished());
        self::assertSame(PostStatus::PUBLISHED, $post->getStatusEnum());
    }
}

