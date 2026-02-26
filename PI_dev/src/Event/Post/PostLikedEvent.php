<?php

namespace App\Event\Post;

use App\Entity\Post;
use App\Entity\User;

class PostLikedEvent
{
    public function __construct(
        public readonly Post $post,
        public readonly User $liker,
        public readonly bool $liked
    ) {
    }
}

