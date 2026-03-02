<?php

namespace App\GoalHistoryBundle\Event;

use App\Entity\Goal;
use App\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Base class for all GoalHistory events.
 * Carries the Goal and the User who triggered the action.
 */
abstract class AbstractGoalEvent extends Event
{
    public function __construct(
        private readonly Goal  $goal,
        private readonly ?User $user = null,
    ) {
    }

    public function getGoal(): Goal
    {
        return $this->goal;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }
}
