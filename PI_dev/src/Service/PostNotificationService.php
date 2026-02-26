<?php

namespace App\Service;

use App\Entity\Post;
use App\Entity\Comment;
use App\Entity\User;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;

class PostNotificationService
{
    public function __construct(
        private HubInterface $hub
    ) {
    }

    /**
     * Notify when someone likes a post
     */
    public function notifyPostLiked(Post $post, User $liker): void
    {
        $postOwner = $post->getCreatedBy();
        
        // Don't notify if user likes their own post
        if ($postOwner->getId() === $liker->getId()) {
            return;
        }

        $notification = [
            'type' => 'post_liked',
            'message' => sprintf('%s %s liked your post', $liker->getFirstName(), $liker->getLastName()),
            'postId' => $post->getId(),
            'postTitle' => $post->getTitle(),
            'userId' => $liker->getId(),
            'userName' => $liker->getFirstName() . ' ' . $liker->getLastName(),
            'timestamp' => (new \DateTimeImmutable())->format('c'),
        ];

        $this->publishToUser($postOwner->getId(), $notification);
    }

    /**
     * Notify when someone comments on a post
     */
    public function notifyPostCommented(Post $post, Comment $comment): void
    {
        $postOwner = $post->getCreatedBy();
        $commenter = $comment->getCommenter();
        
        // Don't notify if user comments on their own post
        if ($postOwner->getId() === $commenter->getId()) {
            return;
        }

        $notification = [
            'type' => 'post_commented',
            'message' => sprintf('%s %s commented on your post', $commenter->getFirstName(), $commenter->getLastName()),
            'postId' => $post->getId(),
            'postTitle' => $post->getTitle(),
            'commentId' => $comment->getId(),
            'commentContent' => substr($comment->getContent(), 0, 100),
            'userId' => $commenter->getId(),
            'userName' => $commenter->getFirstName() . ' ' . $commenter->getLastName(),
            'timestamp' => (new \DateTimeImmutable())->format('c'),
        ];

        $this->publishToUser($postOwner->getId(), $notification);
    }

    /**
     * Notify when someone replies to a comment
     */
    public function notifyCommentReplied(Comment $parentComment, Comment $reply): void
    {
        $originalCommenter = $parentComment->getCommenter();
        $replier = $reply->getCommenter();
        
        // Don't notify if user replies to their own comment
        if ($originalCommenter->getId() === $replier->getId()) {
            return;
        }

        $notification = [
            'type' => 'comment_replied',
            'message' => sprintf('%s %s replied to your comment', $replier->getFirstName(), $replier->getLastName()),
            'postId' => $parentComment->getPost()->getId(),
            'commentId' => $reply->getId(),
            'replyContent' => substr($reply->getContent(), 0, 100),
            'userId' => $replier->getId(),
            'userName' => $replier->getFirstName() . ' ' . $replier->getLastName(),
            'timestamp' => (new \DateTimeImmutable())->format('c'),
        ];

        $this->publishToUser($originalCommenter->getId(), $notification);
    }

    /**
     * Notify followers when a new post is created
     */
    public function notifyNewPost(Post $post): void
    {
        $author = $post->getCreatedBy();
        
        // Get all followers (you'll need to implement this based on your follow system)
        // For now, we'll broadcast to all users
        
        $notification = [
            'type' => 'new_post',
            'message' => sprintf('%s %s created a new post', $author->getFirstName(), $author->getLastName()),
            'postId' => $post->getId(),
            'postTitle' => $post->getTitle(),
            'userId' => $author->getId(),
            'userName' => $author->getFirstName() . ' ' . $author->getLastName(),
            'timestamp' => (new \DateTimeImmutable())->format('c'),
        ];

        // Broadcast to all users (you can filter by followers later)
        $this->publishToTopic('posts', $notification);
    }

    /**
     * Publish notification to a specific user
     */
    private function publishToUser(int $userId, array $data): void
    {
        $update = new Update(
            sprintf('user/%d/notifications', $userId),
            json_encode($data)
        );

        $this->hub->publish($update);
    }

    /**
     * Publish notification to a topic (for broadcasts)
     */
    private function publishToTopic(string $topic, array $data): void
    {
        $update = new Update(
            sprintf('topic/%s', $topic),
            json_encode($data)
        );

        $this->hub->publish($update);
    }
}
