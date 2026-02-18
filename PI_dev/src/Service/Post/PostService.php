<?php
namespace App\Service\Post;

use App\Entity\Post;
use App\Entity\User;
use App\Enum\PostStatus;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PostService
{
    public function __construct(
        private EntityManagerInterface $em,
        private PostRepository $postRepository
    ) {}

    
    public function createPost(string $title, string $content, User $user, string $status = null, array $images = []): Post
    {
        $post = new Post();
        $post->setTitle($title);
        $post->setContent($content);
        $post->setCreatedBy($user);
        $post->setCreatedAt(new \DateTimeImmutable());
        
        // Set status (default to published if not specified)
        if ($status && in_array($status, [PostStatus::DRAFT->value, PostStatus::PUBLISHED->value])) {
            $post->setStatus($status);
        } else {
            $post->setStatus(PostStatus::PUBLISHED->value);
        }

        // Set images if provided
        if (!empty($images)) {
            $post->setImages($images);
        }

        $this->em->persist($post);
        $this->em->flush();

        return $post;
    }

    public function editPost(int $postId, string $title, string $content, User $user): Post
    {
        $post = $this->postRepository->find($postId);

        if (!$post) {
            throw new NotFoundHttpException('Post not found');
        }

        if ($post->getCreatedBy() !== $user) {
            throw new AccessDeniedHttpException('You cannot edit this post');
        }

        $post->setTitle($title);
        $post->setContent($content);

        $this->em->flush();

        return $post;
    }

    public function deletePost(int $postId, User $user): void
    {
        $post = $this->postRepository->find($postId);

        if (!$post) {
            throw new NotFoundHttpException('Post not found');
        }

        if ($post->getCreatedBy() !== $user) {
            throw new AccessDeniedHttpException('You cannot delete this post');
        }

        // Instead of deleting, set status to hidden
        $post->setStatus(PostStatus::HIDDEN->value);
        $this->em->flush();
    }

    public function hidePost(int $postId, User $user): Post
    {
        $post = $this->postRepository->find($postId);

        if (!$post) {
            throw new NotFoundHttpException('Post not found');
        }

        if ($post->getCreatedBy() !== $user) {
            throw new AccessDeniedHttpException('You cannot hide this post');
        }

        $post->setStatus(PostStatus::HIDDEN->value);
        $this->em->flush();

        return $post;
    }

    public function publishPost(int $postId, User $user): Post
    {
        $post = $this->postRepository->find($postId);

        if (!$post) {
            throw new NotFoundHttpException('Post not found');
        }

        if ($post->getCreatedBy() !== $user) {
            throw new AccessDeniedHttpException('You cannot publish this post');
        }

        $post->setStatus(PostStatus::PUBLISHED->value);
        $this->em->flush();

        return $post;
    }
}
