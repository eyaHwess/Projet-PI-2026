<?php
namespace App\Service\Post;

use App\Entity\Comment;
use App\Entity\User;
use App\Event\Post\PostCommentCreatedEvent;
use App\Repository\CommentRepository;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CommentService
{
    public function __construct(
        private EntityManagerInterface $em,
        private CommentRepository $commentRepository,
        private PostRepository $postRepository,
        private EventDispatcherInterface $eventDispatcher
    ) {}

    public function createComment(int $postId, string $content, User $user, ?int $parentCommentId = null): Comment
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

        // If this is a reply, set the parent comment
        if ($parentCommentId) {
            $parentComment = $this->commentRepository->find($parentCommentId);
            if ($parentComment && !$parentComment->isReply()) {
                // Only allow replies to top-level comments
                $comment->setParentComment($parentComment);
            }
        }

        $this->em->persist($comment);
        $this->em->flush();

        $this->eventDispatcher->dispatch(new PostCommentCreatedEvent($comment));

        return $comment;
    }

    public function editComment(int $commentId, string $content, User $user): Comment
    {
        $comment = $this->commentRepository->find($commentId);

        if (!$comment) {
            throw new NotFoundHttpException('Comment not found');
        }

        if ($comment->getCommenter() !== $user) {
            throw new \Exception('You cannot edit this comment');
        }

        $comment->setContent($content);
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
