<?php

namespace App\Tests\Service;

use App\Entity\Post;
use App\Entity\User;
use App\Enum\PostStatus;
use App\Repository\PostRepository;
use App\Service\Post\PostService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class PostServiceTest extends TestCase
{
    private function makeService(?EntityManagerInterface $em = null, ?PostRepository $repo = null): PostService
    {
        $em ??= $this->createMock(EntityManagerInterface::class);
        $repo ??= $this->createMock(PostRepository::class);

        return new PostService($em, $repo);
    }

    private function makeUser(string $email = 'test@example.com'): User
    {
        return (new User())->setEmail($email);
    }

    public function testCreatePostDefaultsToPublishedWhenStatusIsNull(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $repo = $this->createMock(PostRepository::class);

        $em->expects(self::once())->method('persist')->with(self::isInstanceOf(Post::class));
        $em->expects(self::once())->method('flush');

        $service = $this->makeService($em, $repo);
        $user = $this->makeUser();

        $post = $service->createPost('Title', 'Content', $user, null);

        self::assertSame('Title', $post->getTitle());
        self::assertSame('Content', $post->getContent());
        self::assertSame($user, $post->getCreatedBy());
        self::assertSame(PostStatus::PUBLISHED->value, $post->getStatus());
    }

    public function testCreatePostUsesProvidedValidStatus(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $repo = $this->createMock(PostRepository::class);

        $em->expects(self::once())->method('persist')->with(self::isInstanceOf(Post::class));
        $em->expects(self::once())->method('flush');

        $service = $this->makeService($em, $repo);
        $user = $this->makeUser();

        $post = $service->createPost('Title', 'Content', $user, PostStatus::DRAFT->value);

        self::assertSame(PostStatus::DRAFT->value, $post->getStatus());
        self::assertTrue($post->isDraft());
    }

    public function testCreatePostIgnoresInvalidStatusAndDefaultsToPublished(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $repo = $this->createMock(PostRepository::class);

        $em->expects(self::once())->method('persist')->with(self::isInstanceOf(Post::class));
        $em->expects(self::once())->method('flush');

        $service = $this->makeService($em, $repo);
        $user = $this->makeUser();

        $post = $service->createPost('Title', 'Content', $user, 'invalid-status');

        self::assertSame(PostStatus::PUBLISHED->value, $post->getStatus());
    }

    public function testCreatePostSetsScheduledAtOnlyWhenStatusIsScheduled(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $repo = $this->createMock(PostRepository::class);

        $em->expects(self::exactly(2))->method('persist')->with(self::isInstanceOf(Post::class));
        $em->expects(self::exactly(2))->method('flush');

        $service = $this->makeService($em, $repo);
        $user = $this->makeUser();
        $scheduledAt = new \DateTimeImmutable('2026-01-01 11:00:00');

        $scheduled = $service->createPost('Title', 'Content', $user, PostStatus::SCHEDULED->value, [], $scheduledAt);
        self::assertSame(PostStatus::SCHEDULED->value, $scheduled->getStatus());
        self::assertSame($scheduledAt, $scheduled->getScheduledAt());

        $notScheduled = $service->createPost('Title', 'Content', $user, PostStatus::PUBLISHED->value, [], $scheduledAt);
        self::assertSame(PostStatus::PUBLISHED->value, $notScheduled->getStatus());
        self::assertNull($notScheduled->getScheduledAt());
    }

    public function testEditPostThrowsNotFoundWhenMissing(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $repo = $this->createMock(PostRepository::class);
        $repo->method('find')->with(123)->willReturn(null);

        $service = $this->makeService($em, $repo);
        $user = $this->makeUser();

        $this->expectException(NotFoundHttpException::class);
        $service->editPost(123, 'New title', 'New content', $user);
    }

    public function testEditPostThrowsAccessDeniedWhenNotOwner(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $repo = $this->createMock(PostRepository::class);

        $owner = $this->makeUser('owner@example.com');
        $other = $this->makeUser('other@example.com');
        $post = (new Post())->setTitle('Title')->setContent('Content')->setCreatedBy($owner);

        $repo->method('find')->with(10)->willReturn($post);

        $service = $this->makeService($em, $repo);

        $this->expectException(AccessDeniedHttpException::class);
        $service->editPost(10, 'New title', 'New content', $other);
    }

    public function testDeletePostSetsHiddenStatus(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $repo = $this->createMock(PostRepository::class);

        $owner = $this->makeUser('owner@example.com');
        $post = (new Post())
            ->setTitle('Title')
            ->setContent('Content')
            ->setCreatedBy($owner)
            ->setStatus(PostStatus::PUBLISHED->value);

        $repo->method('find')->with(77)->willReturn($post);
        $em->expects(self::once())->method('flush');

        $service = $this->makeService($em, $repo);
        $service->deletePost(77, $owner);

        self::assertSame(PostStatus::HIDDEN->value, $post->getStatus());
        self::assertTrue($post->isHidden());
    }

    public function testPublishPostSetsPublishedStatus(): void
    {
        $em = $this->createMock(EntityManagerInterface::class);
        $repo = $this->createMock(PostRepository::class);

        $owner = $this->makeUser('owner@example.com');
        $post = (new Post())
            ->setTitle('Title')
            ->setContent('Content')
            ->setCreatedBy($owner)
            ->setStatus(PostStatus::DRAFT->value);

        $repo->method('find')->with(88)->willReturn($post);
        $em->expects(self::once())->method('flush');

        $service = $this->makeService($em, $repo);
        $service->publishPost(88, $owner);

        self::assertSame(PostStatus::PUBLISHED->value, $post->getStatus());
        self::assertTrue($post->isPublished());
    }
}

