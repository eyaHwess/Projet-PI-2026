<?php

namespace App\Service\Post;

use App\Entity\Comment;
use App\Entity\CommentLike;
use App\Entity\User;
use App\Repository\CommentRepository;
use App\Repository\CommentLikeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CommentLikeService
{
    public function __construct(
        private EntityManagerInterface $em,
        private CommentRepository $commentRepository,
        private CommentLikeRepository $commentLikeRepository
    ) {}

    public function toggleLike(int $commentId, User $user): array
    {
        $comment = $this->commentRepository->find($commentId);

        if (!$comment) {
            throw new NotFoundHttpException('Comment not found');
        }

        // Check if user already liked this comment
        $existingLike = $this->commentLikeRepository->findOneBy([
            'comment' => $comment,
            'user' => $user
        ]);

        if ($existingLike) {
            // Unlike
            $this->em->remove($existingLike);
            $this->em->flush();

            return [
                'liked' => false,
                'likeCount' => count($comment->getCommentLikes())
            ];
        } else {
            // Like
            $like = new CommentLike();
            $like->setComment($comment);
            $like->setUser($user);
            $like->setCreatedAt(new \DateTimeImmutable());

            $this->em->persist($like);
            $this->em->flush();

            return [
                'liked' => true,
                'likeCount' => count($comment->getCommentLikes())
            ];
        }
    }

    public function hasUserLikedComment(Comment $comment, ?User $user): bool
    {
        if (!$user) {
            return false;
        }

        $like = $this->commentLikeRepository->findOneBy([
            'comment' => $comment,
            'user' => $user
        ]);

        return $like !== null;
    }
}
