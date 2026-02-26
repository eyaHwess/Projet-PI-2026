<?php

namespace App\Event\Post;

use App\Entity\Comment;

class PostCommentCreatedEvent
{
    public function __construct(
        public readonly Comment $comment
    ) {
    }
}

