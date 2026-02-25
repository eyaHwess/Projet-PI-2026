<?php
namespace App\Service\Post;

use App\Entity\Post;
use App\Entity\PostLike;
use App\Entity\User;
use App\Repository\PostLikeRepository;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PostLikeService
{
    public function __construct(
        private EntityManagerInterface $em,
        private PostLikeRepository $postLikeRepository,
        private PostRepository $postRepository
    ) {}

    public function toggleLike(int $postId, User $user): array
    {
        $post = $this->postRepository->find($postId);

        if (!$post) {
            throw new NotFoundHttpException('Post not found');
        }

        // Check if user already liked this post
        $existingLike = $this->postLikeRepository->findOneBy([
            'post' => $post,
            'Liker' => $user
        ]);

        if ($existingLike) {
            // Unlike: remove the like
            $this->em->remove($existingLike);
            $this->em->flush();
            
            return [
                'liked' => false,
                'likeCount' => $post->getPostLikes()->count()
            ];
        } else {
            // Like: add new like
            $postLike = new PostLike();
            $postLike->setPost($post);
            $postLike->setLiker($user);
            
            $this->em->persist($postLike);
            $this->em->flush();
            
            return [
                'liked' => true,
                'likeCount' => $post->getPostLikes()->count()
            ];
        }
    }

    public function hasUserLikedPost(Post $post, ?User $user): bool
    {
        if (!$user) {
            return false;
        }

        $like = $this->postLikeRepository->findOneBy([
            'post' => $post,
            'Liker' => $user
        ]);

        return $like !== null;
    }
}
