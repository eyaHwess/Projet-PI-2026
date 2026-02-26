<?php

namespace App\Service\Tagging;

use App\Entity\Post;
use App\Entity\Tag;

interface TaggingInterface
{
    /**
     * Generate and assign tags to a post.
     *
     * @return Tag[]
     */
    public function generateTagsForPost(Post $post, ?int $maxTags = null): array;

    /**
     * Regenerate tags for an existing post.
     *
     * @return Tag[]
     */
    public function regenerateTagsForPost(Post $post, ?int $maxTags = null): array;
}

