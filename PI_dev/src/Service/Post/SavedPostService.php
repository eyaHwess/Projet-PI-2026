<?php
namespace App\Service\Post;

use App\Entity\SavedPost;
use App\Entity\User;
use App\Entity\Post;
use App\Repository\SavedPostRepository;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SavedPostService
{
    public function __construct(
        private EntityManagerInterface $em,
        private SavedPostRepository $savedPostRepository,
        private PostRepository $postRepository
    ) {}

    public function toggleSave(int $postId, User $user): array
    {
        $post = $this->postRepository->find($postId);

        if (!$post) {
            throw new NotFoundHttpException('Post not found');
        }

        $existingSave = $this->savedPostRepository->findByUserAndPost($user, $post);

        if ($existingSave) {
            // Unsave the post
            $this->em->remove($existingSave);
            $this->em->flush();

            return [
                'saved' => false,
                'message' => 'Post removed from saved'
            ];
        } else {
            // Save the post
            $savedPost = new SavedPost();
            $savedPost->setUser($user);
            $savedPost->setPost($post);
            $savedPost->setSavedAt(new \DateTimeImmutable());

            $this->em->persist($savedPost);
            $this->em->flush();

            return [
                'saved' => true,
                'message' => 'Post saved'
            ];
        }
    }

    public function hasUserSavedPost(Post $post, ?User $user): bool
    {
        if (!$user) {
            return false;
        }

        return $this->savedPostRepository->hasUserSavedPost($user, $post);
    }

    public function getSavedPostsByUser(User $user): array
    {
        return $this->savedPostRepository->findSavedPostsByUser($user);
    }
}
