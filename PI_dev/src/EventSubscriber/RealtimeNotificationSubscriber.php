<?php

namespace App\EventSubscriber;

use App\Event\Post\CommentLikedEvent;
use App\Event\Post\PostCommentCreatedEvent;
use App\Event\Post\PostLikedEvent;
use App\Service\RealtimeNotificationService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class RealtimeNotificationSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private RealtimeNotificationService $realtimeNotificationService
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PostLikedEvent::class => 'onPostLiked',
            PostCommentCreatedEvent::class => 'onCommentCreated',
            CommentLikedEvent::class => 'onCommentLiked',
        ];
    }

    public function onPostLiked(PostLikedEvent $event): void
    {
        if (!$event->liked) {
            return;
        }

        $post = $event->post;
        $owner = $post->getCreatedBy();
        $liker = $event->liker;
        if (!$owner || !$liker || $owner->getId() === $liker->getId()) {
            return;
        }

        $this->realtimeNotificationService->notify(
            $owner,
            'post_like',
            sprintf('%s %s liked your post', $liker->getFirstName(), $liker->getLastName()),
            $post,
            null
        );
    }

    public function onCommentCreated(PostCommentCreatedEvent $event): void
    {
        $comment = $event->comment;
        $post = $comment->getPost();
        if (!$post) {
            return;
        }

        $commenter = $comment->getCommenter();
        if (!$commenter) {
            return;
        }

        // Reply: notify original comment owner
        if ($comment->isReply() && $comment->getParentComment()) {
            $parent = $comment->getParentComment();
            $originalCommenter = $parent?->getCommenter();
            if ($originalCommenter && $originalCommenter->getId() !== $commenter->getId()) {
                $this->realtimeNotificationService->notify(
                    $originalCommenter,
                    'comment_reply',
                    sprintf('%s %s replied to your comment', $commenter->getFirstName(), $commenter->getLastName()),
                    $post,
                    $comment
                );
            }
            return;
        }

        // Top-level comment: notify post owner
        $owner = $post->getCreatedBy();
        if ($owner && $owner->getId() !== $commenter->getId()) {
            $this->realtimeNotificationService->notify(
                $owner,
                'post_comment',
                sprintf('%s %s commented on your post', $commenter->getFirstName(), $commenter->getLastName()),
                $post,
                $comment
            );
        }
    }

    public function onCommentLiked(CommentLikedEvent $event): void
    {
        if (!$event->liked) {
            return;
        }

        $comment = $event->comment;
        $owner = $comment->getCommenter();
        $liker = $event->liker;
        if (!$owner || !$liker || $owner->getId() === $liker->getId()) {
            return;
        }

        $this->realtimeNotificationService->notify(
            $owner,
            'comment_like',
            sprintf('%s %s liked your comment', $liker->getFirstName(), $liker->getLastName()),
            $comment->getPost(),
            $comment
        );
    }
}

