<?php

namespace App\Event\Moderation;

use App\Entity\User;

class ToxicPublishAttemptEvent
{
    public function __construct(
        public readonly User $user,
        public readonly string $entityType,
        public readonly string $contentPreview,
        public readonly float $highestScore
    ) {
    }
}

