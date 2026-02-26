<?php

namespace App\Event\Post;

use App\Entity\Comment;
use App\Entity\User;

class CommentLikedEvent
{
    public function __construct(
        public readonly Comment $comment,
        public readonly User $liker,
        public readonly bool $liked
    ) {
    }
}

