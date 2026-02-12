<?php
namespace App\Service\Post;

use App\Entity\Comment;
use App\Entity\User;
use App\Repository\CommentRepository;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CommentService
{
    public function __construct(
        private EntityManagerInterface $em,
        private CommentRepository $commentRepository,
        private PostRepository $postRepository
    ) {}

    public function createComment(int $postId, string $content, User $user): Comment
    {
        $post = $this->postRepository->find($postId);

        if (!$post) {
            throw new NotFoundHttpException('Post not found');
        }

        $comment = new Comment();
        $comment->setContent($content);
        $comment->setPost($post);
        $comment->setCommenter($user);
        $comment->setCreatedAt(new \DateTimeImmutable());

        $this->em->persist($comment);
        $this->em->flush();

        return $comment;
    }

    public function deleteComment(int $commentId, User $user): void
    {
        $comment = $this->commentRepository->find($commentId);

        if (!$comment) {
            throw new NotFoundHttpException('Comment not found');
        }

        if ($comment->getCommenter() !== $user) {
            throw new \Exception('You cannot delete this comment');
        }

        $this->em->remove($comment);
        $this->em->flush();
    }
}
